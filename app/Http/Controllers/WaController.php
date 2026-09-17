<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessWaBlastCampaignJob;
use App\Models\ActivityLog;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WaBlastCampaign;
use App\Models\WaBlastLog;
use App\Models\WaBlastRecipient;
use App\Models\WaContactGroup;
use App\Models\WaTemplate;
use App\Services\EvolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class WaController extends Controller
{
    public function __construct(protected EvolutionService $evoService) {}

    protected function getEvoService(?User $user = null): EvolutionService
    {
        $targetUser = $user ?? Auth::user();
        $instanceName = $targetUser ? $targetUser->getWaInstanceName() : null;

        return new EvolutionService(instanceName: $instanceName);
    }

    public function dashboard(): View
    {
        $user = Auth::user();
        $evoService = $this->getEvoService($user);

        $state = $evoService->getConnectionState();
        $isConnected = ($state === 'open');
        $profile = $isConnected ? $evoService->getConnectedProfile() : null;

        $isSuperAdmin = $user->isSuperAdmin();

        $totalCampaigns = $isSuperAdmin
            ? WaBlastCampaign::count()
            : WaBlastCampaign::where('user_id', $user->id)->count();

        $activeCampaigns = WaBlastCampaign::with('user')
            ->whereIn('status', ['pending', 'processing'])
            ->when(! $isSuperAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->get();

        $totalRecipients = $isSuperAdmin
            ? WaBlastRecipient::count()
            : WaBlastRecipient::whereHas('campaign', fn($q) => $q->where('user_id', $user->id))->count();

        $successRecipients = $isSuperAdmin
            ? WaBlastRecipient::where('status', 'sent')->count()
            : WaBlastRecipient::where('status', 'sent')->whereHas('campaign', fn($q) => $q->where('user_id', $user->id))->count();

        $failedRecipients = $isSuperAdmin
            ? WaBlastRecipient::where('status', 'failed')->count()
            : WaBlastRecipient::where('status', 'failed')->whereHas('campaign', fn($q) => $q->where('user_id', $user->id))->count();

        $pendingRecipients = $isSuperAdmin
            ? WaBlastRecipient::whereIn('status', ['pending', 'sending'])->count()
            : WaBlastRecipient::whereIn('status', ['pending', 'sending'])->whereHas('campaign', fn($q) => $q->where('user_id', $user->id))->count();

        $totalGroups = $isSuperAdmin
            ? WaContactGroup::count()
            : WaContactGroup::where(fn($q) => $q->where('user_id', $user->id)->orWhereNull('user_id'))->count();

        $totalTemplates = $isSuperAdmin
            ? WaTemplate::count()
            : WaTemplate::where(fn($q) => $q->where('user_id', $user->id)->orWhereNull('user_id'))->count();

        $recentCampaigns = WaBlastCampaign::with('user')
            ->when(! $isSuperAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(6)
            ->get();

        $recentLogs = ActivityLog::with('user')
            ->when(! $isSuperAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'isConnected',
            'state',
            'profile',
            'totalCampaigns',
            'activeCampaigns',
            'totalRecipients',
            'successRecipients',
            'failedRecipients',
            'pendingRecipients',
            'totalGroups',
            'totalTemplates',
            'recentCampaigns',
            'recentLogs'
        ));
    }

    public function setting(): View
    {
        $user = Auth::user();
        $userInstance = $user ? $user->getWaInstanceName() : AppSetting::get('wa_instance_name', 'wablast');
        $evoService = $this->getEvoService($user);

        $state = $evoService->getConnectionState();
        $isConnected = ($state === 'open');
        $qrCode = ! $isConnected ? $evoService->getQrCode() : null;
        $profile = $isConnected ? $evoService->getConnectedProfile() : null;

        $siteName = AppSetting::getSiteName();
        $logoUrl = AppSetting::getLogoUrl();
        $faviconUrl = AppSetting::getFaviconUrl();

        $evoUrl = AppSetting::get('evolution_api_url', (string) env('EVOLUTION_API_URL', 'http://localhost:8080'));
        $evoApiKey = AppSetting::get('evolution_api_apikey', (string) env('EVOLUTION_API_APIKEY', ''));
        $evoInstance = $userInstance;

        return view('wa.setting', compact(
            'isConnected',
            'state',
            'qrCode',
            'profile',
            'siteName',
            'logoUrl',
            'faviconUrl',
            'evoUrl',
            'evoApiKey',
            'evoInstance',
            'userInstance'
        ));
    }

    public function updateUserInstance(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'wa_instance_name' => ['required', 'string', 'alpha_dash', 'max:50'],
        ]);

        $name = strtolower(trim($validated['wa_instance_name']));

        if (! $user->isSuperAdmin() && $user->isInstanceTakenByOther($name)) {
            return back()->withErrors([
                'wa_instance_name' => "Nama WhatsApp Instance '{$name}' sudah digunakan oleh akun pengguna lain. Silakan gunakan nama instance yang unik.",
            ]);
        }

        $oldInstance = $user->wa_instance_name;
        $user->update(['wa_instance_name' => $name]);

        ActivityLog::record(
            action: 'instance_update',
            description: "Memperbarui nama WhatsApp Instance akun '{$user->name}' dari '{$oldInstance}' menjadi '{$name}'.",
            subject: $user,
            properties: [
                'old_instance' => $oldInstance,
                'new_instance' => $name,
            ],
            userId: $user->id
        );

        return back()->with('success', "WhatsApp Instance akun Anda berhasil diubah menjadi '{$name}'.");
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $request->validate([
            'site_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon' => 'nullable|file|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
        ]);

        AppSetting::set('site_name', $request->site_name);

        if ($request->hasFile('logo')) {
            $oldLogo = AppSetting::get('logo_path');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            $logoPath = $request->file('logo')->store('settings', 'public');
            AppSetting::set('logo_path', $logoPath);
        }

        if ($request->hasFile('favicon')) {
            $oldFavicon = AppSetting::get('favicon_path');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            $faviconPath = $request->file('favicon')->store('settings', 'public');
            AppSetting::set('favicon_path', $faviconPath);
        }

        ActivityLog::record(
            action: 'branding_update',
            description: "Memperbarui identitas dan branding website: '{$request->site_name}'."
        );

        return back()->with('success', 'Pengaturan branding website (Nama, Logo, Favicon) berhasil disimpan!');
    }

    public function updateEvolutionConfig(Request $request): RedirectResponse
    {
        if (! Auth::user()->isAdmin()) {
            abort(403, 'Akses terbatas untuk Administrator.');
        }

        $validated = $request->validate([
            'evolution_api_url' => ['required', 'url'],
            'evolution_api_apikey' => ['required', 'string'],
            'wa_instance_name' => ['required', 'string', 'alpha_dash'],
        ]);

        $url = rtrim($validated['evolution_api_url'], '/');
        AppSetting::set('evolution_api_url', $url);
        AppSetting::set('evolution_api_apikey', $validated['evolution_api_apikey']);

        $this->updateEnvFile([
            'EVOLUTION_API_URL' => $url,
            'EVOLUTION_API_APIKEY' => $validated['evolution_api_apikey'],
        ]);

        ActivityLog::record(
            action: 'setting_evolution_update',
            description: 'Memperbarui konfigurasi Server Evolution API Gateway.',
            subject: null,
            properties: [
                'evolution_api_url' => $url,
            ]
        );

        return redirect()->route('wa.setting')->with('success', 'Konfigurasi Server Evolution API Gateway berhasil disimpan!');
    }

    public function testEvolution(Request $request): JsonResponse
    {
        $request->validate([
            'evolution_api_url' => ['required', 'url'],
            'evolution_api_apikey' => ['required', 'string'],
            'wa_instance_name' => ['required', 'string'],
        ]);

        $result = EvolutionService::testConnection(
            $request->string('evolution_api_url')->value(),
            $request->string('evolution_api_apikey')->value(),
            $request->string('wa_instance_name')->value()
        );

        return response()->json($result);
    }

    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        if (! file_exists($envPath) || ! is_writable($envPath)) {
            return;
        }

        $envContent = (string) file_get_contents($envPath);
        foreach ($values as $key => $value) {
            $value = preg_replace('/\s+/', '', (string) $value);
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = (string) preg_replace("/^{$key}=.*/m", "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }
        file_put_contents($envPath, $envContent);
    }

    public function disconnect(): RedirectResponse
    {
        $user = Auth::user();
        $evoService = $this->getEvoService($user);
        $evoService->logoutInstance();

        ActivityLog::record(
            action: 'wa_disconnect',
            description: "Koneksi WhatsApp akun '{$user->name}' ({$user->getWaInstanceName()}) diputus (logout instance).",
            userId: $user->id
        );

        return redirect()->route('wa.setting')->with('success', 'WhatsApp berhasil di-disconnect.');
    }

    public function blast(): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->isSuperAdmin();

        $templates = WaTemplate::when(! $isSuperAdmin, fn($q) => $q->where(fn($sub) => $sub->where('user_id', $user->id)->orWhereNull('user_id')))
            ->latest()
            ->get();

        $contactGroups = WaContactGroup::when(! $isSuperAdmin, fn($q) => $q->where(fn($sub) => $sub->where('user_id', $user->id)->orWhereNull('user_id')))
            ->latest()
            ->get();

        $campaigns = WaBlastCampaign::when(! $isSuperAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(10)
            ->get();

        $logs = WaBlastLog::when(! $isSuperAdmin, fn($q) => $q->where('user_id', $user->id))
            ->latest()
            ->take(10)
            ->get();

        return view('wa.blast', compact('templates', 'contactGroups', 'campaigns', 'logs'));
    }

    /**
     * Memulai pengiriman Blast di antrean latar belakang (Queue Worker).
     * Pengiriman akan tetap berjalan meski browser/laptop ditutup.
     */
    public function startBlast(Request $request): JsonResponse
    {
        $request->validate([
            'targets' => 'required',
            'pesan' => 'required|string',
            'anti_bot' => 'nullable',
        ]);

        $targetsRaw = $request->targets;
        if (is_string($targetsRaw)) {
            $lines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $targetsRaw))));
        } elseif (is_array($targetsRaw)) {
            $lines = array_filter(array_map('trim', $targetsRaw));
        } else {
            $lines = [];
        }

        if (empty($lines)) {
            return response()->json([
                'success' => false,
                'message' => 'Daftar nomor target tidak boleh kosong.',
            ], 422);
        }

        // Simpan gambar jika ada
        $mediaPath = null;
        if ($request->hasFile('gambar')) {
            $mediaPath = $request->file('gambar')->store('wa_blasts', 'public');
        } elseif ($request->filled('gambar_base64')) {
            $base64Image = $request->gambar_base64;
            $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
            $decoded = base64_decode($base64Data);
            if ($decoded !== false) {
                $fileName = 'wa_blasts/' . uniqid('blast_', true) . '.jpg';
                Storage::disk('public')->put($fileName, $decoded);
                $mediaPath = $fileName;
            }
        }

        $pesanTemplate = $request->pesan;
        $antiBot = $request->boolean('anti_bot');

        // Buat Kampanye Blast
        $campaign = WaBlastCampaign::create([
            'user_id' => Auth::id(),
            'judul' => 'Blast - ' . now()->translatedFormat('d M Y H:i'),
            'pesan' => $pesanTemplate,
            'media_path' => $mediaPath,
            'total_target' => count($lines),
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'pending',
            'anti_bot' => $antiBot,
        ]);

        // Buat detail penerima untuk kampanye ini
        $recipientsData = [];
        $now = now();

        foreach ($lines as $line) {
            $parsed = $this->parseContactLine($line);
            $nama = $parsed['nama'];
            $nomor = $parsed['nomor'];

            if (empty($nomor)) {
                continue;
            }

            // Personalisasi pesan per nomor (mendukung {nama}, {Nama}, {name}, dll)
            $pesanPersonal = preg_replace('/\{(?:nama|name)\}/iu', ! empty($nama) ? $nama : 'Bapak/Ibu', $pesanTemplate);
            $pesanPersonal = $this->parseSpintax($pesanPersonal);

            $recipientsData[] = [
                'wa_blast_campaign_id' => $campaign->id,
                'nomor' => $nomor,
                'nama' => $nama ?: null,
                'pesan_personal' => $pesanPersonal,
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (empty($recipientsData)) {
            $campaign->delete();

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada nomor target yang valid.',
            ], 422);
        }

        // Update total target sesuai nomor valid
        $campaign->update(['total_target' => count($recipientsData)]);

        // Chunk insert data penerima untuk performa tinggi
        foreach (array_chunk($recipientsData, 500) as $chunk) {
            WaBlastRecipient::insert($chunk);
        }

        // Catat Log Aktivitas
        ActivityLog::record(
            action: 'blast_created',
            description: "Membuat antrean kampanye blast baru (#{$campaign->id}) untuk {$campaign->total_target} nomor.",
            subject: $campaign,
            properties: [
                'total_target' => $campaign->total_target,
                'anti_bot' => $antiBot,
                'has_media' => (bool) $mediaPath,
            ]
        );

        // Dispatch background job ke antrean
        ProcessWaBlastCampaignJob::dispatch($campaign->id);
        self::ensureQueueWorkerRunning();

        return response()->json([
            'success' => true,
            'campaign_id' => $campaign->id,
            'total_target' => $campaign->total_target,
            'message' => 'Blast berhasil masuk antrean server dan sedang diproses.',
        ]);
    }

    /**
     * Memeriksa apakah ada kampanye blast yang sedang aktif berjalan
     */
    public function getActiveBlast(): JsonResponse
    {
        self::ensureQueueWorkerRunning();

        $campaign = WaBlastCampaign::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'processing'])
            ->latest()
            ->first();

        if (! $campaign) {
            return response()->json(['active' => false]);
        }

        $this->processNextRecipientForCampaign($campaign);
        $campaign->refresh();

        $processed = $campaign->success_count + $campaign->failed_count;
        $progress = $campaign->total_target > 0
            ? (int) round(($processed / $campaign->total_target) * 100)
            : 0;

        return response()->json([
            'active' => true,
            'campaign' => [
                'id' => $campaign->id,
                'status' => $campaign->status,
                'total_target' => $campaign->total_target,
                'success_count' => $campaign->success_count,
                'failed_count' => $campaign->failed_count,
                'processed' => $processed,
                'progress' => $progress,
            ],
        ]);
    }

    /**
     * Mengambil progres real-time kampanye tertentu untuk polling frontend
     */
    public function getBlastProgress(int $id): JsonResponse
    {
        self::ensureQueueWorkerRunning();

        $campaign = WaBlastCampaign::findOrFail($id);

        if (in_array($campaign->status, ['pending', 'processing'], true)) {
            $this->processNextRecipientForCampaign($campaign);
        }

        $campaign->refresh();

        $processed = $campaign->success_count + $campaign->failed_count;
        $progress = $campaign->total_target > 0
            ? (int) round(($processed / $campaign->total_target) * 100)
            : 0;

        return response()->json([
            'id' => $campaign->id,
            'status' => $campaign->status,
            'total_target' => $campaign->total_target,
            'success_count' => $campaign->success_count,
            'failed_count' => $campaign->failed_count,
            'processed' => $processed,
            'progress' => $progress,
            'completed' => in_array($campaign->status, ['completed', 'failed', 'cancelled'], true),
        ]);
    }

    /**
     * Memproses penerima berikutnya secara langsung jika queue worker tidak aktif (Inline Fail-safe Processor)
     */
    public function processNextRecipientForCampaign(WaBlastCampaign $campaign): ?WaBlastRecipient
    {
        if (in_array($campaign->status, ['completed', 'failed', 'cancelled'], true)) {
            return null;
        }

        // Reset penerima yang macet di status 'sending' lebih dari 2 menit
        $campaign->recipients()
            ->where('status', 'sending')
            ->where('updated_at', '<', now()->subMinutes(2))
            ->update(['status' => 'pending']);

        $recipient = $campaign->recipients()
            ->where('status', 'pending')
            ->orderBy('id', 'asc')
            ->first();

        if (! $recipient) {
            $pendingCount = $campaign->recipients()->whereIn('status', ['pending', 'sending'])->count();
            if ($pendingCount === 0) {
                $wasUpdated = WaBlastCampaign::where('id', $campaign->id)
                    ->whereNotIn('status', ['completed', 'cancelled'])
                    ->update([
                        'status' => 'completed',
                        'completed_at' => now(),
                    ]);

                if ($wasUpdated > 0) {
                    $campaign->refresh();
                    WaBlastLog::create([
                        'user_id' => $campaign->user_id,
                        'wa_instance' => $campaign->user?->getWaInstanceName(),
                        'pesan' => $campaign->pesan,
                        'total_target' => $campaign->total_target,
                        'success_count' => $campaign->success_count,
                        'failed_count' => $campaign->failed_count,
                    ]);

                    ActivityLog::record(
                        action: 'blast_completed',
                        description: "Pengiriman blast (#{$campaign->id}) selesai. Sukses: {$campaign->success_count}, Gagal: {$campaign->failed_count}.",
                        subject: $campaign,
                        properties: [
                            'total_target' => $campaign->total_target,
                            'success_count' => $campaign->success_count,
                            'failed_count' => $campaign->failed_count,
                            'wa_instance' => $campaign->user?->getWaInstanceName(),
                        ],
                        userId: $campaign->user_id
                    );
                }
            }

            return null;
        }

        // Kunci status secara atomik agar tidak diproses ganda oleh queue worker / polling lain
        $claimed = WaBlastRecipient::where('id', $recipient->id)
            ->where('status', 'pending')
            ->update([
                'status' => 'sending',
                'updated_at' => now(),
            ]);

        if (! $claimed) {
            return null;
        }

        if ($campaign->status === 'pending') {
            $campaign->update(['status' => 'processing']);
        }

        $mediaBase64 = null;
        if ($campaign->media_path && Storage::disk('public')->exists($campaign->media_path)) {
            $fileContent = Storage::disk('public')->get($campaign->media_path);
            $mediaBase64 = base64_encode($fileContent);
        }

        $cleanNumber = EvolutionService::normalizePhoneNumber($recipient->nomor);
        $evoService = $this->getEvoService($campaign->user);

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
            Log::error("Error sending WA to {$cleanNumber}: " . $e->getMessage());
            $recipient->update([
                'status' => 'failed',
                'sent_at' => now(),
                'error_message' => $e->getMessage(),
            ]);
            $campaign->increment('failed_count');
        }

        $campaign->refresh();
        $processed = $campaign->success_count + $campaign->failed_count;
        if ($processed >= $campaign->total_target) {
            $wasUpdated = WaBlastCampaign::where('id', $campaign->id)
                ->whereNotIn('status', ['completed', 'cancelled'])
                ->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

            if ($wasUpdated > 0) {
                $campaign->refresh();
                WaBlastLog::create([
                    'user_id' => $campaign->user_id,
                    'wa_instance' => $campaign->user?->getWaInstanceName(),
                    'pesan' => $campaign->pesan,
                    'total_target' => $campaign->total_target,
                    'success_count' => $campaign->success_count,
                    'failed_count' => $campaign->failed_count,
                ]);

                ActivityLog::record(
                    action: 'blast_completed',
                    description: "Pengiriman blast (#{$campaign->id}) selesai. Sukses: {$campaign->success_count}, Gagal: {$campaign->failed_count}.",
                    subject: $campaign,
                    properties: [
                        'total_target' => $campaign->total_target,
                        'success_count' => $campaign->success_count,
                        'failed_count' => $campaign->failed_count,
                        'wa_instance' => $campaign->user?->getWaInstanceName(),
                    ],
                    userId: $campaign->user_id
                );
            }
        }

        return $recipient;
    }

    /**
     * Menampilkan detail rincian status per nomor pada suatu kampanye
     */
    public function showCampaign(Request $request, int $id): View|JsonResponse
    {
        $campaign = WaBlastCampaign::with('user')->findOrFail($id);

        $statusFilter = $request->query('status');
        $search = $request->query('search');

        $recipientsQuery = $campaign->recipients();

        if ($statusFilter && in_array($statusFilter, ['pending', 'sent', 'failed'])) {
            $recipientsQuery->where('status', $statusFilter);
        }

        if ($search) {
            $recipientsQuery->where(function ($q) use ($search) {
                $q->where('nomor', 'like', "%{$search}%")
                    ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $recipients = $recipientsQuery->latest()->paginate(50)->withQueryString();

        if ($request->wantsJson()) {
            return response()->json([
                'campaign' => $campaign,
                'recipients' => $recipients,
            ]);
        }

        return view('wa.campaign-detail', compact('campaign', 'recipients', 'statusFilter', 'search'));
    }

    /**
     * Mendapatkan daftar penerima yang gagal untuk dimasukkan kembali ke form input blast
     */
    public function getFailedRecipients(int $id): JsonResponse
    {
        $campaign = WaBlastCampaign::findOrFail($id);
        $failedRecipients = $campaign->recipients()
            ->where('status', 'failed')
            ->select('nama', 'nomor', 'error_message')
            ->get()
            ->map(function ($r) {
                return [
                    'nama' => $r->nama,
                    'nomor' => EvolutionService::normalizePhoneNumber($r->nomor),
                    'error_message' => $r->error_message,
                ];
            });

        return response()->json([
            'success' => true,
            'campaign' => [
                'id' => $campaign->id,
                'pesan' => $campaign->pesan,
            ],
            'failed_recipients' => $failedRecipients,
        ]);
    }

    /**
     * Mengirim ulang nomor-nomor yang gagal pada kampanye tertentu
     */
    public function retryFailedCampaign(int $id): JsonResponse|RedirectResponse
    {
        $originalCampaign = WaBlastCampaign::findOrFail($id);
        $failedRecipients = $originalCampaign->recipients()->where('status', 'failed')->get();

        if ($failedRecipients->isEmpty()) {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tidak ada nomor yang gagal pada kampanye ini.']);
            }

            return back()->with('error', 'Tidak ada nomor yang gagal pada kampanye ini.');
        }

        $newCampaign = WaBlastCampaign::create([
            'user_id' => Auth::id(),
            'judul' => 'Retry Blast #' . $originalCampaign->id . ' - ' . now()->translatedFormat('d M H:i'),
            'pesan' => $originalCampaign->pesan,
            'media_path' => $originalCampaign->media_path,
            'total_target' => $failedRecipients->count(),
            'success_count' => 0,
            'failed_count' => 0,
            'status' => 'pending',
            'anti_bot' => $originalCampaign->anti_bot,
        ]);

        $recipientsData = [];
        $now = now();
        foreach ($failedRecipients as $failed) {
            $cleanNomor = EvolutionService::normalizePhoneNumber($failed->nomor);
            $pesanPersonal = preg_replace('/\{(?:nama|name)\}/iu', ! empty($failed->nama) ? $failed->nama : 'Bapak/Ibu', $originalCampaign->pesan);
            $pesanPersonal = $this->parseSpintax($pesanPersonal);

            $recipientsData[] = [
                'wa_blast_campaign_id' => $newCampaign->id,
                'nomor' => $cleanNomor,
                'nama' => $failed->nama,
                'pesan_personal' => $pesanPersonal,
                'status' => 'pending',
                'error_message' => null,
                'sent_at' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        WaBlastRecipient::insert($recipientsData);

        ActivityLog::record(
            action: 'blast_retry',
            description: "Membuat kampanye retry (#{$newCampaign->id}) untuk {$newCampaign->total_target} nomor gagal dari blast #{$originalCampaign->id}.",
            subject: $newCampaign
        );

        ProcessWaBlastCampaignJob::dispatch($newCampaign->id);
        self::ensureQueueWorkerRunning();

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'campaign_id' => $newCampaign->id,
                'total_target' => $newCampaign->total_target,
                'message' => 'Pengiriman ulang nomor gagal telah dijadwalkan.',
            ]);
        }

        return redirect()->route('wa.blast')->with('success', "Pengiriman ulang ({$newCampaign->total_target} nomor) sedang diproses!");
    }

    /**
     * Memastikan worker queue berjalan otomatis di server latar belakang
     */
    public static function ensureQueueWorkerRunning(): void
    {
        try {
            $hasPendingJobs = DB::table('jobs')->exists()
                || WaBlastCampaign::whereIn('status', ['pending', 'processing'])->exists();

            if ($hasPendingJobs) {
                $artisan = base_path('artisan');
                $php = PHP_BINARY ?: 'php';
                if (str_contains(PHP_OS_FAMILY, 'Windows')) {
                    $cmd = sprintf('start "" /B "%s" "%s" queue:work --stop-when-empty --tries=1 > NUL 2>&1', $php, $artisan);
                    pclose(popen($cmd, 'r'));
                } else {
                    $cmd = sprintf('"%s" "%s" queue:work --stop-when-empty --tries=1 > /dev/null 2>&1 &', $php, $artisan);
                    exec($cmd);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Auto-queue worker trigger notice: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint lama sendSingle (tetap dipertahankan untuk kompatibilitas jika dipanggil)
     */
    public function sendSingle(Request $request): JsonResponse
    {
        $cleanNumber = EvolutionService::normalizePhoneNumber($request->nomor);
        $pesan = $request->pesan;
        $base64Image = $request->gambar_base64;

        if ($base64Image) {
            $base64Data = preg_replace('#^data:image/\w+;base64,#i', '', $base64Image);
            $response = $this->evoService->sendMedia($cleanNumber, $pesan, $base64Data);
        } else {
            $response = $this->evoService->sendMessage($cleanNumber, $pesan);
        }

        return response()->json([
            'success' => $response['is_success'],
            'data' => $response['data'] ?? [],
            'nomor_asli' => $request->nomor_asli,
        ]);
    }

    // ================= FITUR TEMPLATE =================
    public function storeTemplate(Request $request): RedirectResponse
    {
        $validated = $request->validate(['judul' => 'required|string|max:255', 'pesan' => 'required|string']);
        $validated['user_id'] = Auth::id();
        $template = WaTemplate::create($validated);

        ActivityLog::record(
            action: 'template_create',
            description: "Membuat template pesan baru: '{$template->judul}'.",
            subject: $template
        );

        return back()->with('success', 'Template berhasil disimpan!');
    }

    public function updateTemplate(Request $request, int $id): RedirectResponse
    {
        $template = WaTemplate::findOrFail($id);
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $template->user_id !== null && $template->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah template ini.');
        }

        $validated = $request->validate(['judul' => 'required|string|max:255', 'pesan' => 'required|string']);
        $template->update($validated);

        ActivityLog::record(
            action: 'template_update',
            description: "Memperbarui template pesan: '{$template->judul}'.",
            subject: $template
        );

        return back()->with('success', 'Template berhasil diperbarui!');
    }

    public function deleteTemplate(int $id): RedirectResponse
    {
        $template = WaTemplate::findOrFail($id);
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $template->user_id !== null && $template->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus template ini.');
        }

        $judul = $template->judul;
        $template->delete();

        ActivityLog::record(
            action: 'template_delete',
            description: "Menghapus template pesan: '{$judul}'."
        );

        return back()->with('success', 'Template dihapus!');
    }

    // ================= FITUR GRUP KONTAK =================
    public function storeContactGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_grup' => 'required|string|max:255',
            'nomor' => 'required|string',
        ]);
        $validated['user_id'] = Auth::id();

        $rawLines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['nomor']))));
        $normalizedLines = [];
        foreach ($rawLines as $line) {
            $parsed = $this->parseContactLine($line);
            if (! empty($parsed['nomor'])) {
                $normalizedLines[] = ! empty($parsed['nama']) ? "{$parsed['nama']} - {$parsed['nomor']}" : $parsed['nomor'];
            }
        }
        $validated['nomor'] = implode("\n", array_unique($normalizedLines));

        $group = WaContactGroup::create($validated);

        $lineCount = count(array_filter(explode("\n", str_replace("\r", '', $group->nomor))));

        ActivityLog::record(
            action: 'contact_group_create',
            description: "Membuat grup kontak '{$group->nama_grup}' berisi {$lineCount} kontak.",
            subject: $group,
            properties: ['total_nomor' => $lineCount]
        );

        return back()->with('success', 'Grup Kontak berhasil disimpan!');
    }

    public function updateContactGroup(Request $request, int $id): RedirectResponse
    {
        $group = WaContactGroup::findOrFail($id);
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $group->user_id !== null && $group->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengubah grup kontak ini.');
        }

        $validated = $request->validate([
            'nama_grup' => 'required|string|max:255',
            'nomor' => 'required|string',
        ]);

        $rawLines = array_filter(array_map('trim', explode("\n", str_replace("\r", '', $validated['nomor']))));
        $normalizedLines = [];
        foreach ($rawLines as $line) {
            $parsed = $this->parseContactLine($line);
            if (! empty($parsed['nomor'])) {
                $normalizedLines[] = ! empty($parsed['nama']) ? "{$parsed['nama']} - {$parsed['nomor']}" : $parsed['nomor'];
            }
        }
        $validated['nomor'] = implode("\n", array_unique($normalizedLines));

        $group->update($validated);
        $lineCount = count(array_filter(explode("\n", str_replace("\r", '', $group->nomor))));

        ActivityLog::record(
            action: 'contact_group_update',
            description: "Memperbarui grup kontak '{$group->nama_grup}' (sekarang {$lineCount} kontak).",
            subject: $group,
            properties: ['total_nomor' => $lineCount]
        );

        return back()->with('success', 'Grup Kontak berhasil diperbarui!');
    }

    public function deleteContactGroup(int $id): RedirectResponse
    {
        $group = WaContactGroup::findOrFail($id);
        $user = Auth::user();
        if (! $user->isSuperAdmin() && $group->user_id !== null && $group->user_id !== $user->id) {
            abort(403, 'Anda tidak memiliki hak akses untuk menghapus grup kontak ini.');
        }

        $nama = $group->nama_grup;
        $group->delete();

        ActivityLog::record(
            action: 'contact_group_delete',
            description: "Menghapus grup kontak: '{$nama}'."
        );

        return back()->with('success', 'Grup Kontak dihapus!');
    }

    public function storeLog(Request $request): JsonResponse
    {
        $user = Auth::user();

        WaBlastLog::create([
            'user_id' => $user?->id,
            'wa_instance' => $user?->getWaInstanceName(),
            'pesan' => $request->pesan,
            'total_target' => $request->total_target,
            'success_count' => $request->success_count,
            'failed_count' => $request->failed_count,
        ]);

        return response()->json(['status' => 'success']);
    }

    // ================= FITUR AUDIT LOG =================
    public function logs(Request $request): View
    {
        $user = Auth::user();
        $query = ActivityLog::with('user')->latest();

        if (! $user->isSuperAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        $logs = $query->paginate(25)->withQueryString();

        // Action options untuk filter dropdown
        $actionTypes = [
            'auth_login' => 'User Login',
            'auth_logout' => 'User Logout',
            'auth_failed' => 'Login Gagal',
            'user_create' => 'Tambah User',
            'user_update' => 'Edit User',
            'user_delete' => 'Hapus User',
            'profile_update' => 'Update Profil',
            'password_update' => 'Ubah Password',
            'branding_update' => 'Ubah Branding',
            'setting_evolution_update' => 'Ubah Setting Evolution',
            'instance_update' => 'Ubah Instance WA',
            'contact_group_create' => 'Buat Grup Kontak',
            'contact_group_update' => 'Edit Grup Kontak',
            'contact_group_delete' => 'Hapus Grup Kontak',
            'template_create' => 'Buat Template',
            'template_update' => 'Edit Template',
            'template_delete' => 'Hapus Template',
            'wa_disconnect' => 'Putus Koneksi WA',
            'blast_created' => 'Buat Antrean Blast',
            'blast_processing' => 'Blast Diproses',
            'blast_completed' => 'Blast Selesai',
            'blast_retry' => 'Kirim Ulang Blast',
        ];

        return view('wa.logs', compact('logs', 'actionTypes'));
    }

    /**
     * Parser Spintax helper
     */
    protected function parseSpintax(string $text): string
    {
        return preg_replace_callback('/\[(.*?)\]/', function ($matches) {
            $choices = explode('/', $matches[1]);

            return $choices[array_rand($choices)];
        }, $text);
    }

    /**
     * Parsing baris kontak target secara cerdas (mendukung berbagai format: Nama - Nomor, Nomor - Nama, Tab Excel, CSV, dll)
     */
    protected function parseContactLine(string $line): array
    {
        $trimmed = trim($line, " \t\n\r\0\x0B\"'");
        if ($trimmed === '') {
            return ['nama' => '', 'nomor' => ''];
        }

        // Abaikan baris header Excel / CSV jika tidak memuat nomor telepon
        if (
            preg_match('/^(no|nomor|name|nama|kontak|contact|phone|hp|telepon)[\s\t,;:\-\|]/i', $trimmed)
            && ! preg_match('/\d{8,}/', $trimmed)
        ) {
            return ['nama' => '', 'nomor' => ''];
        }

        $nama = '';
        $rawPhone = '';

        // 1. Cek pemisah tabular (Excel copy-paste), titik koma, pipe, atau koma
        $delimiter = null;
        if (str_contains($trimmed, "\t")) {
            $delimiter = "\t";
        } elseif (str_contains($trimmed, ';')) {
            $delimiter = ';';
        } elseif (str_contains($trimmed, '|')) {
            $delimiter = '|';
        } elseif (substr_count($trimmed, ',') === 1 && preg_match('/\d{8,}/', $trimmed)) {
            $delimiter = ',';
        }

        if ($delimiter !== null) {
            $cells = array_values(array_filter(array_map(function ($c) {
                return trim($c, " \t\n\r\0\x0B\"'");
            }, explode($delimiter, $trimmed)), fn($c) => $c !== ''));

            $phoneIndex = -1;
            foreach ($cells as $idx => $cell) {
                $digits = preg_replace('/\D/', '', $cell);
                if (strlen($digits) >= 8 && strlen($digits) <= 16) {
                    $rawPhone = $cell;
                    $phoneIndex = $idx;
                    break;
                }
            }

            if ($phoneIndex !== -1) {
                // Prioritaskan cell yang memuat huruf dan bukan nomor urut baris (misal '1', '2')
                foreach ($cells as $idx => $cell) {
                    if ($idx === $phoneIndex) {
                        continue;
                    }
                    if (preg_match('/^\d{1,4}$/', $cell)) {
                        continue;
                    }
                    if (preg_match('/[a-zA-Z]/', $cell)) {
                        $nama = $cell;
                        break;
                    }
                }

                if (empty($nama)) {
                    foreach ($cells as $idx => $cell) {
                        if ($idx === $phoneIndex) {
                            continue;
                        }
                        if (! preg_match('/^\d{1,4}$/', $cell)) {
                            $nama = $cell;
                            break;
                        }
                    }
                }
            }
        }

        // 2. Jika tanpa delimiter tabel atau belum ditemukan nomor HP, gunakan regex cerdas
        if (empty($rawPhone)) {
            if (preg_match('/^(.*?)\(([\+?\d\s\-\.]{8,20})\)(.*?)$/', $trimmed, $m)) {
                $rawPhone = trim($m[2]);
                $nama = trim($m[1] . ' ' . $m[3]);
            } elseif (preg_match('/^([^\:\-]+)[\:\-]\s*([\+?\d\s\-\.]{8,20})$/', $trimmed, $m)) {
                $rawPhone = trim($m[2]);
                $nama = trim($m[1]);
            } elseif (preg_match('/^([\+?\d\s\-\.]{8,20})\s*[\:\-]\s*([^\:\-]+)$/', $trimmed, $m)) {
                $rawPhone = trim($m[1]);
                $nama = trim($m[2]);
            } elseif (preg_match('/^(.*?)((?:\+?62|0|8|9)\d[\d\s\-\.]{6,16}\d)(.*?)$/', $trimmed, $m)) {
                $rawPhone = trim($m[2]);
                $nama = trim($m[1] . ' ' . $m[3]);
            }
        }

        // Bersihkan nama dari simbol pemisah
        if (! empty($nama)) {
            $nama = trim(preg_replace('/^[\s\-\:\,\;\|\(\)\[\]\/]+|[\s\-\:\,\;\|\(\)\[\]\/]+$/u', '', $nama));
            $nama = preg_replace('/\s{2,}/', ' ', $nama);
        }

        $cleanPhone = EvolutionService::normalizePhoneNumber($rawPhone ?: $trimmed);

        return [
            'nama' => $nama,
            'nomor' => $cleanPhone,
        ];
    }
}
