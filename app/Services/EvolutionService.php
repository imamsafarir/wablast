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
    public function getConnectionState(): string
    {
        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->timeout(5)->get("{$this->baseUrl}/instance/connectionState/{$this->instanceName}");

            if ($response->successful()) {
                $state = $response->json()['instance']['state'] ?? 'close';
                if ($state === 'open') {
                    return 'open';
                }
            }

            // Fallback: periksa status dari fetchInstances
            $fetchRes = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->timeout(5)->get("{$this->baseUrl}/instance/fetchInstances");

            if ($fetchRes->successful()) {
                $instances = $fetchRes->json();
                if (is_array($instances)) {
                    foreach ($instances as $item) {
                        $name = $item['name'] ?? ($item['instance']['instanceName'] ?? null);
                        if ($name === $this->instanceName) {
                            $status = $item['connectionStatus'] ?? ($item['instance']['connectionStatus'] ?? null);
                            if ($status === 'open') {
                                return 'open';
                            }

                            return $status ?? 'close';
                        }
                    }
                }
            }

            if ($response->status() === 404) {
                return 'not_found';
            }

            return $response->successful() ? ($response->json()['instance']['state'] ?? 'close') : 'close';
        } catch (\Throwable $e) {
            return 'error';
        }
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

        $cacheKey = "wa_active_groups_{$this->instanceName}";
        if ($refresh) {
            Cache::forget($cacheKey);
        } elseif (Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if (is_array($cached)) {
                return $cached;
            }
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

            $candidates = array_values($groupsMap);

            // 2. Filter & Eliminasi Grup Ghost / Residu Sesi Lama:
            // Mengeliminasi grup dari nomor sesi lama di database Evolution API
            // tanpa melakukan request paralel massal yang menyebabkan socket Baileys timeout/400.
            $excludedMap = array_flip($this->getExcludedGroupJids($this->instanceName));
            $validGroups = [];

            foreach ($candidates as $item) {
                $jid = $item['id'];

                // Abaikan jika termasuk residu ghost JID yang diketahui dari instance lain
                if (isset($excludedMap[$jid])) {
                    continue;
                }

                // Abaikan jika pernah terkonfirmasi forbidden di cache
                $statusKey = "wa_grp_status_{$this->instanceName}_{$jid}";
                if (Cache::get($statusKey) === 'forbidden') {
                    continue;
                }

                $validGroups[] = $item;
            }

            if (! empty($validGroups)) {
                // Urutkan grup: yang paling baru aktif di atas, lalu alfabetis
                usort($validGroups, function ($a, $b) {
                    $timeA = ! empty($a['updatedAt']) ? strtotime($a['updatedAt']) : 0;
                    $timeB = ! empty($b['updatedAt']) ? strtotime($b['updatedAt']) : 0;
                    if ($timeA !== $timeB) {
                        return $timeB <=> $timeA;
                    }

                    return strcasecmp($a['subject'], $b['subject']);
                });

                $data = [
                    'success' => true,
                    'groups' => $validGroups,
                    'instance' => $this->instanceName,
                    'connected' => true,
                    'state' => 'open',
                    'total' => count($validGroups),
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
                    Cache::put("wa_grp_status_{$this->instanceName}_{$groupJid}", 'forbidden', 86400 * 7);

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

    /**
     * Known ghost / leaked group JIDs from other historical sessions in Evolution API DB
     * that must be excluded from specific instances (e.g. admin_bpvp_pangkep).
     *
     * @return array<string>
     */
    public function getExcludedGroupJids(string $instanceName): array
    {
        if ($instanceName === 'admin_bpvp_pangkep') {
            return [
                '120363410898413604@g.us',
                '120363428871201042@g.us',
                '6285394798991-1524191383@g.us',
                '120363303764532328@g.us',
                '120363409953532990@g.us',
                '120363169457510980@g.us',
                '120363409779151222@g.us',
                '120363222649201158@g.us',
                '120363387144085168@g.us',
                '6285212989694-1579827342@g.us',
                '120363163896209999@g.us',
                '120363042885842204@g.us',
                '6281915528044-1624254754@g.us',
                '120363244043402425@g.us',
                '120363409833057155@g.us',
                '120363409743341759@g.us',
                '6285299189995-1580434924@g.us',
                '120363163932830072@g.us',
                '120363416246605170@g.us',
                '6285239545469-1463562635@g.us',
                '6285238546460-1615030394@g.us',
                '120363321001982536@g.us',
                '120363402755204623@g.us',
                '120363418552450261@g.us',
                '120363159802663137@g.us',
                '120363409625757415@g.us',
                '6282187737233-1556284655@g.us', // LAMBE ESSE✨
                '6285934536335-1601005081@g.us',
                '120363411798402715@g.us',
                '120363410331429900@g.us',
                '120363405068285405@g.us',
                '120363403841459920@g.us',
                '120363025651372652@g.us',
                '6281317176165-1570676161@g.us',
                '120363422212171945@g.us',
                '6282188468863-1510225549@g.us',
                '6289695622193-1608734420@g.us', // KEPALA KOTAK
                '120363412524774200@g.us',
                '120363363144386421@g.us',
                '6282347273939-1561972765@g.us',
                '120363196354824639@g.us',
                '120363425192065756@g.us',
                '120363178466587788@g.us',
                '120363405664891003@g.us',
                '6285342218362-1537141101@g.us',
                '120363418386235437@g.us',
                '120363327186211950@g.us',
                '120363419092090683@g.us',
                '6285255829867-1533886875@g.us',
                '120363426491943594@g.us',
                '120363405940370816@g.us',
                '120363241001172096@g.us',
                '6282188222812-1526294873@g.us',
                '6285255636989-1567517357@g.us',
                '120363167935160025@g.us',
                '120363403449549135@g.us',
                '120363420663493812@g.us',
                '6282187737233-1635599895@g.us', // Sudiang racing team
                '6281317176165-1570425722@g.us',
                '120363250577793858@g.us',
                '120363231144288320@g.us',
                '120363401473841629@g.us',
                '120363241920731305@g.us',
                '120363200366237580@g.us',
                '62895806384064-1554605811@g.us',
                '120363045618438919@g.us',
                '120363419474389571@g.us',
                '120363329026994823@g.us',
                '6285336054144-1621414168@g.us',
                '120363043866524510@g.us',
                '120363325180667500@g.us',
                '120363341207552808@g.us',
                '120363334202107020@g.us',
                '120363332248436910@g.us',
                '120363242125834716@g.us',
            ];
        }

        return [];
    }
}
