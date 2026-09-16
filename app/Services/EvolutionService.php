<?php

namespace App\Services;

use App\Models\AppSetting;
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
            $clean = '62'.substr($clean, 3);
        }

        // 2. Perbaikan otomatis nomor Tri/Three (089x) yang hilang digit 8:
        // 095xxx -> 62895xxx, 6295xxx -> 62895xxx, 95xxx -> 62895xxx (juga 96, 97, 98, 99)
        if (preg_match('/^09([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289'.$m[1];
        }
        if (preg_match('/^629([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289'.$m[1];
        }
        if (preg_match('/^9([5-9]\d{7,10})$/', $clean, $m)) {
            return '6289'.$m[1];
        }

        // 3. Standar nomor Indonesia
        if (str_starts_with($clean, '0')) {
            $clean = '62'.substr($clean, 1);
        } elseif (str_starts_with($clean, '8')) {
            $clean = '62'.$clean;
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
                    'message' => "Koneksi berhasil! Status instance '{$instanceName}': ".strtoupper((string) $state),
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
                    'message' => 'Gagal: API Key tidak valid atau tidak memiliki izin akses (HTTP '.$response->status().').',
                ];
            }

            return [
                'success' => false,
                'message' => 'Gagal terhubung ke Evolution API: HTTP '.$response->status(),
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal menghubungi server Evolution API: '.$e->getMessage(),
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
}
