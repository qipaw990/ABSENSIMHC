@props([
    'nama'  => '',
    'foto'  => null,
    'size'  => '40px',
    'class' => '',
    'style' => '',
])

@php
    use App\Helpers\AvatarHelper;
    use Illuminate\Support\Facades\Storage;

    if ($foto && Storage::disk('public')->exists($foto)) {
        $src = asset('storage/' . $foto);
    } else {
        // Ukuran dalam pixel untuk SVG (ambil angka dari string CSS seperti "40px", "2rem")
        $px = is_numeric($size) ? (int)$size : 80;
        $src = AvatarHelper::svgDataUri($nama ?: 'User', $px);
    }
@endphp

<img
    src="{{ $src }}"
    alt="{{ $nama }}"
    class="{{ $class }}"
    style="width:{{ $size }};height:{{ $size }};border-radius:50%;object-fit:cover;{{ $style }}"
>
