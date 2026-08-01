<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class WaGatewayService
{
    private Client $client;
    private string $baseUrl;
    private string $defaultApiKey;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('services.whatsapp.base_url', 'https://api-gateway.smkmuthiaharapanclk.com'), '/');
        $this->defaultApiKey = config('services.whatsapp.api_key', 'wag_admin_key_changeme_12345678');

        $this->client = new Client([
            'timeout'         => 15,
            'connect_timeout' => 10,
            'verify'          => false, // Hindari masalah SSL jika cert self-signed
        ]);
    }

    /**
     * Kirim pesan WhatsApp melalui Custom WhatsApp Gateway API.
     *
     * @param string|null $apiKey   API Key khusus device/kelas (opsional, jika null/kosong memakai default)
     * @param string      $target   Nomor WA tujuan (08xx, 628xx, atau +628xx)
     * @param string      $pesan    Isi pesan
     * @param string|null $mediaUrl URL media lampiran (opsional)
     * @param string|null $mediaType Tipe media (image, pdf, document, video, audio)
     * @return array ['success' => bool, 'response' => string, 'status' => int]
     */
    public function kirim(?string $apiKey, string $target, string $pesan, ?string $mediaUrl = null, ?string $mediaType = null): array
    {
        $key = !empty($apiKey) ? $apiKey : $this->defaultApiKey;
        $formattedPhone = $this->formatNomor($target);

        $payload = [
            'phone'   => $formattedPhone,
            'message' => $pesan,
        ];

        if (!empty($mediaUrl)) {
            $payload['media_url']  = $mediaUrl;
            $payload['media_type'] = $mediaType ?? 'image';
        }

        try {
            $response = $this->client->post("{$this->baseUrl}/api/messages/send", [
                'headers' => [
                    'X-API-KEY'    => $key,
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                ],
                'json' => $payload,
            ]);

            $body   = (string) $response->getBody();
            $data   = json_decode($body, true);
            $status = $response->getStatusCode();

            $success = ($status === 200 || $status === 201) && isset($data['success']) && $data['success'] === true;

            Log::info('WaGatewayService::kirim', [
                'target'  => $formattedPhone,
                'status'  => $status,
                'success' => $success,
                'response' => $body,
            ]);

            return [
                'success'  => $success,
                'response' => $body,
                'status'   => $status,
            ];

        } catch (RequestException $e) {
            $errorBody = $e->hasResponse()
                ? (string) $e->getResponse()->getBody()
                : $e->getMessage();

            Log::error('WaGatewayService::kirim - RequestException', [
                'target' => $formattedPhone,
                'error'  => $errorBody,
            ]);

            return [
                'success'  => false,
                'response' => $errorBody,
                'status'   => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0,
            ];

        } catch (\Exception $e) {
            Log::error('WaGatewayService::kirim - Exception', [
                'target' => $formattedPhone,
                'error'  => $e->getMessage(),
            ]);

            return [
                'success'  => false,
                'response' => $e->getMessage(),
                'status'   => 0,
            ];
        }
    }

    /**
     * Cek status/kesehatan WhatsApp Gateway server dan device.
     * Endpoint: GET /health
     * Response:
     * {
     *   "success": true,
     *   "status": "running",
     *   "connected_devices": ["device_android_01"]
     * }
     *
     * @param string|null $apiKey (Opsional) API Key untuk otentikasi tambahan jika diperlukan
     * @return string 'aktif' | 'terputus' | 'nonaktif'
     */
    public function cekStatus(?string $apiKey = null): string
    {
        $key = !empty($apiKey) ? $apiKey : $this->defaultApiKey;

        try {
            $response = $this->client->get("{$this->baseUrl}/health", [
                'headers' => [
                    'X-API-KEY' => $key,
                    'Accept'    => 'application/json',
                ],
                'timeout' => 5,
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            Log::info('WaGatewayService::cekStatus - response', [
                'body' => $body,
                'data' => $data,
            ]);

            if (is_array($data) && isset($data['success']) && $data['success'] === true) {
                if (isset($data['status']) && strtolower((string)$data['status']) === 'running') {
                    return 'aktif';
                }
                return 'aktif';
            }

            return 'terputus';

        } catch (\Exception $e) {
            Log::warning('WaGatewayService::cekStatus - gagal koneksi', [
                'error' => $e->getMessage(),
            ]);
            return 'terputus';
        }
    }

    /**
     * Format nomor WA ke format internasional 62xxx.
     */
    public function formatNomor(string $nomor): string
    {
        $nomor = preg_replace('/\D/', '', $nomor); // Hapus karakter non-angka
        if (str_starts_with($nomor, '0')) {
            $nomor = '62' . substr($nomor, 1);
        } elseif (!str_starts_with($nomor, '62')) {
            $nomor = '62' . $nomor;
        }
        return $nomor;
    }
}
