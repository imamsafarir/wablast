<?php

namespace App\Jobs;

use App\Models\ActivityLog;
use App\Models\WaBlacklist;
use App\Models\WaBlastCampaign;
use App\Models\WaBlastLog;
use App\Models\WaBlastRecipient;
use App\Services\EvolutionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessWaBlastCampaignJob implements ShouldQueue
{
    use Queueable;

    /**
     * Jumlah percobaan jika job gagal total
     */
    public int $tries = 1;

    /**
     * Timeout job dalam detik (misal 24 jam untuk pengiriman aman dengan jeda manusia)
     */
    public int $timeout = 86400;

    /**
     * Create a new job instance.
     */
    public function __construct(public int $campaignId) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $campaign = WaBlastCampaign::with('user')->find($this->campaignId);
        if (! $campaign || in_array($campaign->status, ['cancelled', 'completed', 'paused'], true)) {
            return;
        }

        $userInstance = $campaign->user ? $campaign->user->getWaInstanceName() : null;
        $evoService = new EvolutionService(instanceName: $userInstance);

        $campaign->update([
            'status' => 'processing',
        ]);

        $delayMin = max(1, (int) ($campaign->delay_min ?: 30));
        $delayMax = max($delayMin, (int) ($campaign->delay_max ?: 60));
        $batchSize = max(5, (int) ($campaign->batch_size ?: 20));
        $batchCooldown = max(10, (int) ($campaign->batch_cooldown ?: 180));
        $enableSpintax = (bool) ($campaign->enable_spintax ?? true);
        $enableZeroWidthHash = (bool) ($campaign->enable_zero_width_hash ?? true);
        $enableAntiReport = (bool) ($campaign->enable_anti_report ?? false);

        ActivityLog::record(
            action: 'blast_processing',
            description: "Memulai pengiriman background blast (#{$campaign->id}) ke {$campaign->total_target} target dengan Mode {$campaign->speed_mode} (Jeda {$delayMin}-{$delayMax}s, Istirahat tiap {$batchSize} pesan).",
            subject: $campaign,
            properties: [
                'total_target' => $campaign->total_target,
                'speed_mode' => $campaign->speed_mode,
                'delay_min' => $delayMin,
                'delay_max' => $delayMax,
                'batch_size' => $batchSize,
                'batch_cooldown' => $batchCooldown,
                'wa_instance' => $userInstance,
            ],
            userId: $campaign->user_id
        );

        $recipients = WaBlastRecipient::where('wa_blast_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->get();

        $mediaBase64 = null;
        if ($campaign->media_path && Storage::disk('public')->exists($campaign->media_path)) {
            $fileContent = Storage::disk('public')->get($campaign->media_path);
            $mediaBase64 = base64_encode($fileContent);
        }

        // Cache daftar blacklist untuk user ini agar tidak dikirimi
        $blacklistedNumbers = WaBlacklist::where(function ($q) use ($campaign) {
            $q->whereNull('user_id')->orWhere('user_id', $campaign->user_id);
        })->pluck('nomor')->map(fn($n) => EvolutionService::normalizePhoneNumber($n))->flip()->toArray();

        $consecutiveFailures = 0;
        $sentInBatchCount = 0;

        foreach ($recipients as $recipient) {
            // Cek jika status kampanye diubah (misal: di-pause atau di-cancel oleh user)
            $campaign->refresh();
            if (in_array($campaign->status, ['cancelled', 'paused'], true)) {
                break;
            }

            $cleanNumber = EvolutionService::normalizePhoneNumber($recipient->nomor);

            // Cek apakah nomor ada di daftar Blacklist / meminta opt-out
            if (isset($blacklistedNumbers[$cleanNumber])) {
                $recipient->update([
                    'status' => 'failed',
                    'sent_at' => now(),
                    'error_message' => 'Nomor ada di daftar Blacklist / meminta berhenti berlangganan (Dilewati).',
                ]);
                $campaign->increment('failed_count');

                continue;
            }

            // Atomic claim
            $claimed = WaBlastRecipient::where('id', $recipient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'sending',
                    'updated_at' => now(),
                ]);

            if (! $claimed) {
                continue;
            }

            // ================= 1. TRANSFORMASI KONTEN (ANTI-SPAM FINGERPRINT) =================
            $personalMessage = $recipient->pesan_personal ?: $campaign->pesan;

            // Spintax: {Halo|Hai|Selamat pagi}
            if ($enableSpintax) {
                $personalMessage = EvolutionService::parseSpintax($personalMessage);
            }

            // Friendly Opt-out Footer
            if ($enableAntiReport) {
                $personalMessage = EvolutionService::appendOptOutFooter($personalMessage);
            }

            // Invisible Zero-Width Hash Injection
            if ($enableZeroWidthHash) {
                $personalMessage = EvolutionService::injectZeroWidthHash($personalMessage);
            }

            // Randomize Media Binary Checksum jika ada media
            $currentMediaBase64 = $mediaBase64;
            if ($mediaBase64 && $enableZeroWidthHash) {
                $currentMediaBase64 = EvolutionService::randomizeMediaChecksum($mediaBase64);
            }

            // ================= 2. PENGIRIMAN PESAN LANGSUNG =================
            try {
                if ($currentMediaBase64) {
                    $response = $evoService->sendMedia($cleanNumber, $personalMessage, $currentMediaBase64);
                } else {
                    $response = $evoService->sendMessage($cleanNumber, $personalMessage);
                }

                if ($response['is_success']) {
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);
                    $campaign->increment('success_count');
                    $sentInBatchCount++;
                    $consecutiveFailures = 0; // Reset rem darurat
                } else {
                    $errorDetails = EvolutionService::diagnoseAndFormatError($response['data'] ?? null, $cleanNumber, $evoService);

                    $recipient->update([
                        'status' => 'failed',
                        'sent_at' => now(),
                        'error_message' => $errorDetails,
                    ]);
                    $campaign->increment('failed_count');
                    $consecutiveFailures++;
                }
            } catch (\Throwable $e) {
                Log::error("Error sending WA to {$cleanNumber}: " . $e->getMessage());
                $errorDetails = EvolutionService::diagnoseAndFormatError($e->getMessage(), $cleanNumber, $evoService);

                $recipient->update([
                    'status' => 'failed',
                    'sent_at' => now(),
                    'error_message' => $errorDetails,
                ]);
                $campaign->increment('failed_count');
                $consecutiveFailures++;
            }

            // ================= 3. SMART CIRCUIT BREAKER (REM DARURAT) =================
            // Jika 4 kegagalan beruntun, otomatis PAUSE agar nomor tidak diblokir permanen
            if ($consecutiveFailures >= 4) {
                $campaign->update(['status' => 'paused']);

                ActivityLog::record(
                    action: 'blast_circuit_breaker',
                    description: "Smart Circuit Breaker aktif! Kampanye #{$campaign->id} otomatis di-PAUSE karena terjadi {$consecutiveFailures} kegagalan beruntun untuk melindungi nomor WhatsApp Anda dari pemblokiran.",
                    subject: $campaign,
                    properties: [
                        'consecutive_failures' => $consecutiveFailures,
                        'last_error' => $errorDetails ?? 'Unknown error',
                    ],
                    userId: $campaign->user_id
                );

                break;
            }

            // Cek status kampanye jika dipause/dicancel pengguna saat pengiriman
            $campaign->refresh();
            if (in_array($campaign->status, ['cancelled', 'paused'], true)) {
                break;
            }

            // Cek apakah masih ada penerima berikutnya yang pending
            $hasRemainingPending = WaBlastRecipient::where('wa_blast_campaign_id', $campaign->id)
                ->where('status', 'pending')
                ->exists();

            if (! $hasRemainingPending) {
                break;
            }

            // ================= 4. JEDA & BATCH COOLDOWN SETELAH PENGIRIMAN =================
            if ($sentInBatchCount >= $batchSize) {
                ActivityLog::record(
                    action: 'blast_cooldown',
                    description: "Sesi istirahat anti-bot: Kampanye #{$campaign->id} beristirahat selama {$batchCooldown} detik setelah mengirim {$sentInBatchCount} pesan.",
                    subject: $campaign,
                    userId: $campaign->user_id
                );

                sleep($batchCooldown);
                $sentInBatchCount = 0;
            } else {
                $randomDelay = random_int($delayMin, $delayMax);
                sleep($randomDelay);
            }

            // Cek kembali status kampanye setelah jeda tidur
            $campaign->refresh();
            if (in_array($campaign->status, ['cancelled', 'paused'], true)) {
                break;
            }
        }

        $campaign->refresh();

        // Jika kampanye berstatus 'paused' atau 'cancelled', jangan tandai completed
        if (in_array($campaign->status, ['paused', 'cancelled'], true)) {
            return;
        }

        // Cek apakah masih ada sisa penerima pending
        $remainingPending = WaBlastRecipient::where('wa_blast_campaign_id', $campaign->id)
            ->whereIn('status', ['pending', 'sending'])
            ->count();

        if ($remainingPending === 0) {
            $updated = WaBlastCampaign::where('id', $campaign->id)
                ->where('status', 'processing')
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            if ($updated > 0) {
                $campaign->refresh();
                WaBlastLog::create([
                    'user_id' => $campaign->user_id,
                    'wa_instance' => $userInstance,
                    'pesan' => $campaign->pesan,
                    'total_target' => $campaign->total_target,
                    'success_count' => $campaign->success_count,
                    'failed_count' => $campaign->failed_count,
                ]);

                ActivityLog::record(
                    action: 'blast_completed',
                    description: "Pengiriman background blast (#{$campaign->id}) selesai. Sukses: {$campaign->success_count}, Gagal: {$campaign->failed_count}.",
                    subject: $campaign,
                    properties: [
                        'total_target' => $campaign->total_target,
                        'success_count' => $campaign->success_count,
                        'failed_count' => $campaign->failed_count,
                        'wa_instance' => $userInstance,
                    ],
                    userId: $campaign->user_id
                );
            }
        }
    }
}
