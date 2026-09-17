<?php

namespace App\Services;

use App\Models\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EvolutionService
{
    protected string $baseUrl;

    protected string $apiKey;

    protected string $instanceName;

    public function __construct(?string $baseUrl = null, ?string $apiKey = null, ?string $instanceName = null)
    {
        $this->baseUrl = rtrim($baseUrl ?? (string) AppSetting::get('evolution_api_url', (string) env('EVOLUTION_API_URL', 'http://localhost:8080')), '/');
        $this->apiKey = $apiKey ?? (string) AppSetting::get('evolution_api_apikey', (string) env('EVOLUTION_API_APIKEY', ''));
        $this->instanceName = $instanceName ?? (string) AppSetting::get('wa_instance_name', (string) env('WA_INSTANCE_NAME', 'wablast'));
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getApiKey(): string
    {
        return $this->apiKey;
    }

    public function getInstanceName(): string
    {
        return $this->instanceName;
    }

    /**
     * Format dan bersihkan nomor telepon WhatsApp ke format internasional (62xxxx)
     */
    public static function normalizePhoneNumber(?string $phone): string
    {
        if (! $phone) {
            return '';
        }

        $phone = trim($phone, " \t\n\r\0\x0B\"'");

        if (str_contains($phone, '@g.us')) {
            if (preg_match('/([0-9\-]+@g\.us)/i', $phone, $m)) {
                return $m[1];
            }

            return $phone;
        }

        // Tangani notasi ilmiah Excel (misal: 8.95351E+11 atau 6.28954E+13)
        if (preg_match('/^[+\-]?\d+[\.,]\d+[eE][+\-]?\d+$/i', $phone)) {
            $normalizedFloat = str_replace(',', '.', $phone);
            $phone = sprintf('%.0f', (float) $normalizedFloat);
        }

        $clean = (string) preg_replace('/\D/', '', $phone);

        if (empty($clean)) {
            return '';
        }

        // 1. Tangani awalan 620
        if (str_starts_with($clean, '620')) {
            $clean = '62' . substr($clean, 3);
        }

        // 2. Perbaikan otomatis nomor Tri/Three (089x) yang hilang digit 8:
        // 095xxx -> 62895xxx, 6295xxx -> 62895xxx, 95xxx -> 62895xxx (juga 96, 97, 98, 99)
        if (preg_match('/^09([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289' . $m[1];
        }
        if (preg_match('/^629([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289' . $m[1];
        }
        if (preg_match('/^9([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289' . $m[1];
        }

        // 3. Standar nomor Indonesia
        if (str_starts_with($clean, '0')) {
            $clean = '62' . substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62' . $clean;
        }

        return $clean;
    }

    /**
     * Test connection to Evolution API
     */
    public static function testConnection(string $url, string $apiKey, string $instanceName): array
    {
        $url = rtrim($url, '/');

        try {
            $response = Http::withHeaders([
                'apikey' => $apiKey,
            ])->timeout(8)->get("{$url}/instance/connectionState/{$instanceName}");

            if ($response->successful()) {
                $state = $response->json()['instance']['state'] ?? 'close';

                return [
                    'success' => true,
                    'state' => $state,
                    'message' => "Koneksi berhasil! Status instance '{$instanceName}': " . strtoupper((string) $state),
                ];
            }

            if ($response->status() === 404) {
                $listResponse = Http::withHeaders([
                    'apikey' => $apiKey,
                ])->timeout(8)->get("{$url}/instance/fetchInstances");

                if ($listResponse->successful()) {
                    return [
                        'success' => true,
                        'state' => 'not_found',
                        'message' => "Server Evolution terhubung! Namun instance '{$instanceName}' belum dibuat.",
                    ];
                }
            }

            if ($response->status() === 401 || $response->status() === 403) {
                return [
                    'success' => false,
                    'message' => 'Gagal: API Key tidak valid atau tidak memiliki izin akses (HTTP ' . $response->status() . ').',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Evolution API: HTTP ' . $response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Evolution API: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Get WhatsApp profile info of connected instance
     */
    public function getConnectedProfile(): ?array
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->timeout(8)->get("{$this->baseUrl}/instance/fetchInstances");

            if ($response->successful()) {
                $instances = $response->json();
                if (is_array($instances)) {
                    foreach ($instances as $item) {
                        $name = $item['name'] ?? ($item['instance']['instanceName'] ?? null);
                        if ($name === $this->instanceName) {
                            $instanceData = $item['instance'] ?? $item;
                            $ownerJid = $instanceData['ownerJid'] ?? ($item['ownerJid'] ?? null);
                            $phone = null;
                            if ($ownerJid) {
                                $phone = explode('@', (string) $ownerJid)[0];
                            }
                            $profileName = $instanceData['profileName'] ?? ($item['profileName'] ?? null);
                            $profilePictureUrl = $instanceData['profilePictureUrl'] ?? ($item['profilePictureUrl'] ?? ($instanceData['profilePicUrl'] ?? ($item['profilePicUrl'] ?? null)));

                            return [
                                'name' => $name,
                                'phone' => $phone,
                                'profile_name' => $profileName,
                                'profile_picture_url' => $profilePictureUrl,
                                'connection_status' => $instanceData['connectionStatus'] ?? ($item['connectionStatus'] ?? 'open'),
                            ];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning("Failed to fetch WhatsApp connected profile: {$e->getMessage()}");
        }

        return null;
    }

    // Mengecek status koneksi (open, close, connecting)
    public function getConnectionState()
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->get("{$this->baseUrl}/instance/connectionState/{$this->instanceName}");

        if ($response->successful()) {
            return $response->json()['instance']['state'] ?? 'close';
        }

        // Jika API mereturn error (misal 404), berarti instance belum terbuat
        return 'not_found';
    }

    // Mengambil QR Code yang aman dari error 'already exists'
    public function getQrCode()
    {
        // 1. Coba panggil QR untuk instance yang sudah ada
        $connectResponse = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->get("{$this->baseUrl}/instance/connect/{$this->instanceName}");

        if ($connectResponse->successful()) {
            $data = $connectResponse->json();
            if (isset($data['base64'])) {
                return $data['base64'];
            }
        }

        // 2. Jika koneksi di atas gagal (biasanya karena instance memang belum dibuat), buat baru
        $createResponse = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->post("{$this->baseUrl}/instance/create", [
            'instanceName' => $this->instanceName,
            'qrcode' => true,
            'integration' => 'WHATSAPP-BAILEYS',
        ]);

        if ($createResponse->successful()) {
            $data = $createResponse->json();

            return $data['qrcode']['base64'] ?? $data['base64'] ?? null;
        }

        return null;
    }

    // Memutus koneksi WA
    public function logoutInstance()
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
        ])->delete("{$this->baseUrl}/instance/logout/{$this->instanceName}");

        return $response->successful();
    }

    // Mengirim Teks Biasa (Tetap ada)
    public function sendMessage($number, $text)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/message/sendText/{$this->instanceName}", [
            'number' => (string) $number,
            'text' => $text,
        ]);

        return [
            'is_success' => $response->successful(),
            'data' => $response->json(),
        ];
    }

    // FUNGSI BARU: Mengirim Media (Gambar + Caption)
    public function sendMedia($number, $caption, $base64)
    {
        $response = Http::withHeaders([
            'apikey' => $this->apiKey,
            'Content-Type' => 'application/json',
        ])->post("{$this->baseUrl}/message/sendMedia/{$this->instanceName}", [
            'number' => (string) $number,
            'mediatype' => 'image',
            'media' => $base64,
            'caption' => $caption,
        ]);

        return [
            'is_success' => $response->successful(),
            'data' => $response->json(),
        ];
    }

    /**
     * Mengambil daftar grup WhatsApp yang diikuti oleh instance nomor ini
     */
    public function fetchWaGroups(bool $refresh = false): array
    {
        if (empty($this->instanceName)) {
            return [
                'success' => false,
                'groups' => [],
                'instance' => '',
                'connected' => false,
                'state' => 'not_found',
                'message' => 'Nama WhatsApp instance belum diatur.',
            ];
        }

        $state = $this->getConnectionState();
        if ($state !== 'open') {
            return [
                'success' => true,
                'groups' => [],
                'instance' => $this->instanceName,
                'connected' => false,
                'state' => $state,
                'message' => "WhatsApp instance '{$this->instanceName}' belum terhubung (Status: {$state}). Silakan hubungkan WhatsApp terlebih dahulu di menu Pengaturan.",
            ];
        }

        $cacheKey = "wa_active_groups_{$this->instanceName}";
        if ($refresh) {
            Cache::forget($cacheKey);
        } elseif (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
        }

        try {
            $groupsMap = [];

            // 1. Ambil seluruh obrolan bertipe grup (@g.us) dari POST /chat/findChats/{instance}
            try {
                $response = Http::withHeaders([
                    'apikey' => $this->apiKey,
                    'Content-Type' => 'application/json',
                ])->timeout(15)->post("{$this->baseUrl}/chat/findChats/{$this->instanceName}", []);

                if ($response->successful()) {
                    $chats = $response->json();
                    if (is_array($chats)) {
                        foreach ($chats as $c) {
                            $jid = $c['remoteJid'] ?? ($c['id'] ?? ($c['jid'] ?? ''));
                            if (str_contains($jid, '@g.us')) {
                                if (isset($c['isGroup']) && $c['isGroup'] === false) {
                                    continue;
                                }
                                $subject = trim((string) ($c['pushName'] ?? ($c['subject'] ?? ($c['name'] ?? ''))));
                                $groupsMap[$jid] = [
                                    'id' => $jid,
                                    'subject' => $subject ?: 'Grup WhatsApp',
                                    'updatedAt' => $c['updatedAt'] ?? null,
                                    'size' => null,
                                ];
                            }
                        }
                    }
                }
            } catch (\Throwable $eFindChats) {
                Log::warning("findChats error for instance {$this->instanceName}: {$eFindChats->getMessage()}");
            }

            // 2. Coba gabungkan dengan fetchAllGroups (jika ada grup baru yang belum sempat ada riwayat chat)
            try {
                $resAll = Http::withHeaders(['apikey' => $this->apiKey])
                    ->timeout(3)
                    ->get("{$this->baseUrl}/group/fetchAllGroups/{$this->instanceName}?getParticipants=false");

                if ($resAll->successful()) {
                    $allGroups = $resAll->json();
                    if (is_array($allGroups)) {
                        foreach ($allGroups as $g) {
                            $jid = $g['id'] ?? ($g['jid'] ?? '');
                            if (str_contains($jid, '@g.us')) {
                                $subject = trim((string) ($g['subject'] ?? ($g['name'] ?? '')));
                                if (! isset($groupsMap[$jid])) {
                                    $groupsMap[$jid] = [
                                        'id' => $jid,
                                        'subject' => $subject ?: 'Grup WhatsApp',
                                        'updatedAt' => null,
                                        'size' => isset($g['size']) ? (int) $g['size'] : (isset($g['participants']) ? count($g['participants']) : null),
                                    ];
                                } elseif (! empty($subject) && $groupsMap[$jid]['subject'] === 'Grup WhatsApp') {
                                    $groupsMap[$jid]['subject'] = $subject;
                                }
                            }
                        }
                    }
                }
            } catch (\Throwable $eAll) {
                // Timeout di fetchAllGroups wajar untuk instance dengan >100 grup, abaikan
            }

            $result = array_values($groupsMap);

            if (! empty($result)) {
                // Urutkan grup: yang paling baru aktif/diupdate di atas, lalu alfabetis
                usort($result, function ($a, $b) {
                    $timeA = ! empty($a['updatedAt']) ? strtotime($a['updatedAt']) : 0;
                    $timeB = ! empty($b['updatedAt']) ? strtotime($b['updatedAt']) : 0;
                    if ($timeA !== $timeB) {
                        return $timeB <=> $timeA;
                    }

                    return strcasecmp($a['subject'], $b['subject']);
                });

                $data = [
                    'success' => true,
                    'groups' => $result,
                    'instance' => $this->instanceName,
                    'connected' => true,
                    'state' => 'open',
                    'total' => count($result),
                ];

                Cache::put($cacheKey, $data, 300);

                return $data;
            }

            $emptyData = [
                'success' => true,
                'groups' => [],
                'instance' => $this->instanceName,
                'connected' => true,
                'state' => 'open',
                'total' => 0,
                'message' => "Tidak ada grup WhatsApp aktif yang diikuti oleh instance '{$this->instanceName}'.",
            ];

            Cache::put($cacheKey, $emptyData, 60);

            return $emptyData;
        } catch (\Throwable $e) {
            Log::error("Failed to fetch WA groups for instance {$this->instanceName}: " . $e->getMessage());

            return [
                'success' => false,
                'groups' => [],
                'instance' => $this->instanceName,
                'connected' => false,
                'state' => 'error',
                'message' => 'Gagal menghubungi server WhatsApp Gateway: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Mengambil peserta / nomor kontak anggota dari satu grup WhatsApp
     */
    public function fetchGroupParticipants(string $groupJid): array
    {
        $groupJid = self::normalizePhoneNumber($groupJid);

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->timeout(15)->get("{$this->baseUrl}/group/findGroupInfos/{$this->instanceName}", [
                'groupJid' => $groupJid,
            ]);

            if (! $response->successful()) {
                $status = $response->status();
                if ($status === 404 || $status === 403) {
                    return [
                        'success' => false,
                        'message' => 'Grup WhatsApp tidak ditemukan atau nomor Anda bukan anggota aktif grup ini (Forbidden).',
                        'participants' => [],
                    ];
                }

                return [
                    'success' => false,
                    'message' => "Gagal mengambil data peserta grup WhatsApp (HTTP {$status}).",
                    'participants' => [],
                ];
            }

            $data = $response->json();
            $rawParticipants = $data['participants'] ?? [];
            $contacts = [];

            foreach ($rawParticipants as $p) {
                $phoneRaw = $p['phoneNumber'] ?? ($p['id'] ?? '');
                // Bersihkan domain WhatsApp (@s.whatsapp.net, @lid, dll)
                $phone = preg_replace('/@.*$/', '', (string) $phoneRaw);
                $phone = preg_replace('/\D/', '', $phone);

                if (str_starts_with($phone, '62') || str_starts_with($phone, '0') || str_starts_with($phone, '8') || str_starts_with($phone, '9')) {
                    $normalized = self::normalizePhoneNumber($phone);
                    if (! empty($normalized) && ! in_array($normalized, $contacts, true)) {
                        $contacts[] = $normalized;
                    }
                }
            }

            return [
                'success' => true,
                'group_jid' => $groupJid,
                'subject' => $data['subject'] ?? 'Grup WhatsApp',
                'announce' => (bool) ($data['announce'] ?? false),
                'total' => count($contacts),
                'participants' => $contacts,
            ];
        } catch (\Throwable $e) {
            Log::error("fetchGroupParticipants error for {$groupJid}: " . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Evolution API: ' . $e->getMessage(),
                'participants' => [],
            ];
        }
    }

    /**
     * Mendiagnosa dan memformat pesan kegagalan pengiriman agar jelas dan informatif bagi pengguna
     */
    public static function diagnoseAndFormatError(mixed $data, string $targetNumber, ?self $service = null): string
    {
        $rawString = is_array($data) ? json_encode($data) : (string) $data;
        $isGroup = str_contains($targetNumber, '@g.us');

        if ($isGroup) {
            if ($service) {
                try {
                    $groupInfo = $service->fetchGroupParticipants($targetNumber);
                    if (! $groupInfo['success']) {
                        return 'Gagal kirim ke grup: Akun WhatsApp Anda bukan anggota aktif grup ini atau telah keluar (Forbidden).';
                    }
                    if (! empty($groupInfo['announce'])) {
                        return 'Gagal kirim ke grup: Pengaturan grup dibatasi hanya untuk Admin (Hanya Admin yang dapat mengirim pesan).';
                    }
                } catch (\Throwable) {
                    // Lanjut ke fallback string matching jika pengecekan gagal
                }
            }

            if (str_contains($rawString, '[object Object]') || str_contains($rawString, '400') || str_contains($rawString, 'Bad Request')) {
                return 'Gagal kirim ke grup: Izin pengiriman ditolak WhatsApp. Kemungkinan grup hanya untuk Admin atau nomor Anda bukan anggota aktif grup.';
            }

            if (str_contains($rawString, 'forbidden') || str_contains($rawString, '403') || str_contains($rawString, '404')) {
                return 'Gagal kirim ke grup: Akun WhatsApp Anda bukan anggota aktif grup ini atau akses ditolak (Forbidden).';
            }

            return 'Gagal kirim ke grup WhatsApp: ' . (is_string($data) ? $data : 'Akses pengiriman ditolak oleh WhatsApp Gateway.');
        }

        // Penanganan nomor pribadi
        if (str_contains($rawString, 'exists') && (str_contains($rawString, 'false') || str_contains($rawString, 'not exists'))) {
            return 'Nomor telepon tujuan tidak terdaftar di WhatsApp.';
        }

        if (str_contains($rawString, 'rate-overlimit') || str_contains($rawString, 'rate limit')) {
            return 'Batas frekuensi pengiriman pesan WhatsApp tercapai (Rate limit). Silakan coba lagi beberapa saat lagi.';
        }

        if (str_contains($rawString, '[object Object]')) {
            return 'Gagal dikirim oleh WhatsApp Gateway (Format pesan atau koneksi nomor bermasalah).';
        }

        if (is_array($data)) {
            $msg = $data['response']['message'] ?? ($data['message'] ?? null);
            if (is_string($msg) && ! empty($msg) && ! str_contains($msg, '[object Object]')) {
                return "Gagal: {$msg}";
            }
            if (is_array($msg)) {
                $filtered = array_filter($msg, fn($m) => is_string($m) && ! str_contains($m, '[object Object]'));
                if (! empty($filtered)) {
                    return 'Gagal: ' . implode(', ', $filtered);
                }
            }
        }

        return ! empty($rawString) && ! str_contains($rawString, '[object Object]')
            ? $rawString
            : 'Gagal dikirim oleh WhatsApp Gateway.';
    }
}
