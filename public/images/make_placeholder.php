<?php
/**
 * Script satu kali — buat gambar placeholder default-siswa.png
 * Jalankan: php public/images/make_placeholder.php
 * (Hanya perlu dijalankan sekali saat setup)
 */

$dir = __DIR__;
$file = $dir . '/default-siswa.png';

if (extension_loaded('gd')) {
    $w = 200; $h = 200;
    $img = imagecreatetruecolor($w, $h);
    $bg  = imagecolorallocate($img, 30, 35, 50);
    $fg  = imagecolorallocate($img, 99, 102, 241);
    $txt = imagecolorallocate($img, 180, 185, 210);
    imagefill($img, 0, 0, $bg);
    imagefilledellipse($img, 100, 80, 80, 80, $fg);
    imagefilledellipse($img, 100, 175, 130, 60, $fg);
    imagepng($img, $file);
    imagedestroy($img);
    echo "OK: $file\n";
} else {
    // Fallback: buat 1x1 pixel transparan
    file_put_contents($file, base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg=='
    ));
    echo "Placeholder 1x1 dibuat (GD tidak tersedia): $file\n";
}
