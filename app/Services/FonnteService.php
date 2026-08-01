<?php

namespace App\Services;

/**
 * Class FonnteService
 *
 * @deprecated Digantikan oleh WaGatewayService.
 * Wrapper ini dipertahankan untuk backward compatibility.
 */
class FonnteService
{
    private WaGatewayService $waGatewayService;

    public function __construct(?WaGatewayService $waGatewayService = null)
    {
        $this->waGatewayService = $waGatewayService ?? app(WaGatewayService::class);
    }

    /**
     * Backward-compatible kirim method.
     */
    public function kirim(string $token, string $target, string $pesan): array
    {
        return $this->waGatewayService->kirim($token, $target, $pesan);
    }

    /**
     * Backward-compatible cekStatus method.
     */
    public function cekStatus(string $token): string
    {
        return $this->waGatewayService->cekStatus($token);
    }

    /**
     * Format nomor WA.
     */
    public function formatNomor(string $nomor): string
    {
        return $this->waGatewayService->formatNomor($nomor);
    }
}
