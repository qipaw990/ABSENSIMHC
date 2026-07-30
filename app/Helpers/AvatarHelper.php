<?php

namespace App\Helpers;

class AvatarHelper
{
    /**
     * Warna avatar berdasarkan karakter nama (konsisten per orang).
     */
    private static array $colors = [
        ['bg' => '#4f46e5', 'fg' => '#e0e7ff'], // indigo
        ['bg' => '#0891b2', 'fg' => '#cffafe'], // cyan
        ['bg' => '#059669', 'fg' => '#d1fae5'], // emerald
        ['bg' => '#d97706', 'fg' => '#fef3c7'], // amber
        ['bg' => '#dc2626', 'fg' => '#fee2e2'], // red
        ['bg' => '#7c3aed', 'fg' => '#ede9fe'], // violet
        ['bg' => '#db2777', 'fg' => '#fce7f3'], // pink
        ['bg' => '#0284c7', 'fg' => '#e0f2fe'], // sky
        ['bg' => '#16a34a', 'fg' => '#dcfce7'], // green
        ['bg' => '#9333ea', 'fg' => '#f3e8ff'], // purple
    ];

    /**
     * Ambil 1-2 inisial dari nama lengkap.
     */
    public static function initials(string $nama): string
    {
        $words = preg_split('/\s+/', trim($nama));
        if (count($words) >= 2) {
            return strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
        }
        return strtoupper(mb_substr($nama, 0, 2));
    }

    /**
     * Pilih warna konsisten berdasarkan nama.
     */
    public static function color(string $nama): array
    {
        $idx = array_sum(array_map('ord', str_split($nama))) % count(self::$colors);
        return self::$colors[$idx];
    }

    /**
     * Generate SVG avatar data URI dari nama.
     * Kompatibel dengan <img src="..."> dan DomPDF.
     *
     * @param string $nama   Nama lengkap
     * @param int    $size   Ukuran SVG dalam pixel
     */
    public static function svgDataUri(string $nama, int $size = 200): string
    {
        $initials = self::initials($nama);
        $color    = self::color($nama);
        $fontSize = intval($size * 0.38);

        $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="{$size}" height="{$size}" viewBox="0 0 {$size} {$size}">
  <rect width="{$size}" height="{$size}" rx="0" fill="{$color['bg']}"/>
  <text
    x="50%" y="50%"
    dominant-baseline="central"
    text-anchor="middle"
    font-family="Arial, sans-serif"
    font-size="{$fontSize}"
    font-weight="bold"
    fill="{$color['fg']}"
  >{$initials}</text>
</svg>
SVG;

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
