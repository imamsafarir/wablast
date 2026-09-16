<?php

namespace App\Jobs;

use App\Models\ActivityLog;
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
     * Timeout job dalam detik (misal 1 jam untuk ribuan kontak)
     */
    public int $timeout = 3600;

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
        if (! $campaign || $campaign->status === 'cancelled') {
            return;
        }

        $userInstance = $campaign->user ? $campaign->user->getWaInstanceName() : null;
        $evoService = new EvolutionService(instanceName: $userInstance);

        $campaign->update([
            'status' => 'processing',
        ]);

        ActivityLog::record(
            action: 'blast_processing',
            description: "Memulai pengiriman background blast (#{$campaign->id}) ke {$campaign->total_target} target.",
            subject: $campaign,
            properties: [
                'total_target' => $campaign->total_target,
                'anti_bot' => $campaign->anti_bot,
                'wa_instance' => $userInstance,
            ],
            userId: $campaign->user_id
        );

        $recipients = WaBlastRecipient::where('wa_blast_campaign_id', $campaign->id)
            ->where('status', 'pending')
            ->get();

        $mediaBase64 = null;
        if ($campaign->media_path && Storage::disk('public')->exists($campaign->media_path)) {
            $fileContent = Storage::disk('public')->get($campaign->media_path);
            $mediaBase64 = base64_encode($fileContent);
        }

        $isFirst = true;

        foreach ($recipients as $recipient) {
            // Cek jika kampanye dibatalkan di tengah jalan
            $campaign->refresh();
            if ($campaign->status === 'cancelled') {
                break;
            }

            // Atomic claim: pastikan tidak diproses ganda oleh request polling / proses lain
            $claimed = WaBlastRecipient::where('id', $recipient->id)
                ->where('status', 'pending')
                ->update([
                    'status' => 'sending',
                    'updated_at' => now(),
                ]);

            if (! $claimed) {
                continue;
            }

            // Jeda Anti-Bot (acak antara 2 sampai 5 detik)
            if ($campaign->anti_bot && ! $isFirst) {
                sleep(random_int(2, 5));
            }
            $isFirst = false;

            $cleanNumber = EvolutionService::normalizePhoneNumber($recipient->nomor);

            try {
                if ($mediaBase64) {
                    $response = $evoService->sendMedia($cleanNumber, $recipient->pesan_personal, $mediaBase64);
                } else {
                    $response = $evoService->sendMessage($cleanNumber, $recipient->pesan_personal);
                }

                if ($response['is_success']) {
                    $recipient->update([
                        'status' => 'sent',
                        'sent_at' => now(),
                        'error_message' => null,
                    ]);
                    $campaign->increment('success_count');
                } else {
                    $errorDetails = is_array($response['data'] ?? null)
                        ? json_encode($response['data'])
                        : (string) ($response['data'] ?? 'Gagal dikirim oleh WhatsApp Gateway');

                    $recipient->update([
                        'status' => 'failed',
                        'sent_at' => now(),
                        'error_message' => $errorDetails,
                    ]);
                    $campaign->increment('failed_count');
                }
            } catch (\Throwable $e) {
                Log::error("Error sending WA to {$cleanNumber}: ".$e->getMessage());
                $recipient->update([
                    'status' => 'failed',
                    'sent_at' => now(),
                    'error_message' => $e->getMessage(),
                ]);
                $campaign->increment('failed_count');
            }
        }

        $campaign->refresh();

        $finalStatus = ($campaign->status === 'cancelled') ? 'cancelled' : 'completed';

        // Update status secara atomik untuk mencegah duplikasi pencatatan log oleh request polling HTTP
        $updated = WaBlastCampaign::where('id', $campaign->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->update([
                'status' => $finalStatus,
                'completed_at' => now(),
            ]);

        if ($updated > 0) {
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
