<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    private Client $client;
    private string $baseUrl = 'https://api.fonnte.com';

    public function __construct()
    {
        $this->client = new Client([
            'timeout'         => 15,
            'connect_timeout' => 10,
        ]);
    }

    /**
     * Kirim pesan WhatsApp melalui Fonnte API menggunakan token kelas tertentu.
     *
     * @param string $token  Token Fonnte milik kelas (sudah didekripsi)
     * @param string $target Nomor WA tujuan (format 62xxxxxxxxxx)
     * @param string $pesan  Isi pesan
     * @return array ['success' => bool, 'response' => string, 'status' => int]
     */
    public function kirim(string $token, string $target, string $pesan): array
    {
        try {
            $response = $this->client->post("{$this->baseUrl}/send", [
                'headers' => [
                    'Authorization' => $token,
                    'Accept'        => 'application/json',
                ],
                'form_params' => [
                    'target'  => $this->formatNomor($target),
                    'message' => $pesan,
                    'delay'   => '2~5', // Delay 2-5 detik antar pesan (fitur Fonnte)
                ],
            ]);

            $body   = (string) $response->getBody();
            $data   = json_decode($body, true);
            $status = $response->getStatusCode();

            $success = $status === 200 && isset($data['status']) && $data['status'] === true;

            Log::info('FonnteService::kirim', [
                'target'  => $target,
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

            Log::error('FonnteService::kirim - RequestException', [
                'target'  => $target,
                'error'   => $errorBody,
            ]);

            return [
                'success'  => false,
                'response' => $errorBody,
                'status'   => $e->hasResponse() ? $e->getResponse()->getStatusCode() : 0,
            ];

        } catch (\Exception $e) {
            Log::error('FonnteService::kirim - Exception', [
                'target' => $target,
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
     * Cek status/kesehatan device Fonnte berdasarkan token.
     *
     * Fonnte API GET /device response format:
     * {
     *   "status": true,          ← boolean, bukan string!
     *   "name": "nama device",
     *   "device": "08xxx",
     *   "connected": true,       ← kadang field ini yang dipakai
     *   "expired": false
     * }
     *
     * @return string 'aktif' | 'terputus' | 'nonaktif'
     */
    public function cekStatus(string $token): string
    {
        try {
            // ✅ Fonnte API SELALU POST, bukan GET!
            $response = $this->client->post("{$this->baseUrl}/device", [
                'headers' => [
                    'Authorization' => $token,
                    'Accept'        => 'application/json',
                ],
                // Body kosong tapi harus form_params agar Content-Type benar
                'form_params' => [],
            ]);

            $body = (string) $response->getBody();
            $data = json_decode($body, true);

            Log::info('FonnteService::cekStatus - raw response', [
                'body' => $body,
                'data' => $data,
            ]);

            if (!is_array($data)) {
                Log::warning('FonnteService::cekStatus - response bukan JSON valid', ['body' => $body]);
                return 'terputus';
            }

            // Fonnte bisa kembalikan array of devices atau single device
            // Jika array of devices, ambil yang pertama
            $device = $data;
            if (isset($data[0]) && is_array($data[0])) {
                $device = $data[0];
            }

            // Cek berbagai kemungkinan field status dari Fonnte
            // 1. Field 'connected' (boolean)
            if (isset($device['connected'])) {
                return $device['connected'] === true ? 'aktif' : 'terputus';
            }

            // 2. Field 'status' (bisa boolean atau string)
            if (isset($device['status'])) {
                $st = $device['status'];

                // Boolean true = connected
                if ($st === true) return 'aktif';
                if ($st === false) return 'terputus';

                // String
                $st = strtolower((string) $st);
                return match(true) {
                    in_array($st, ['connect', 'connected', 'active', 'aktif', '1']) => 'aktif',
                    in_array($st, ['disconnect', 'disconnected', 'inactive'])       => 'terputus',
                    default => 'nonaktif',
                };
            }

            // 3. Nested device.status (format lama)
            if (isset($device['device']['status'])) {
                $st = strtolower((string) $device['device']['status']);
                return match(true) {
                    in_array($st, ['connect', 'connected']) => 'aktif',
                    default                                  => 'terputus',
                };
            }

            Log::warning('FonnteService::cekStatus - tidak menemukan field status', ['data' => $data]);
            return 'nonaktif';

        } catch (\Exception $e) {
            Log::warning('FonnteService::cekStatus - gagal cek status', [
                'error' => $e->getMessage(),
                'token_prefix' => substr($token, 0, 8) . '...',
            ]);
            return 'terputus';
        }
    }

    /**
     * Format nomor WA ke format internasional 62xxx.
     */
    private function formatNomor(string $nomor): string
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
