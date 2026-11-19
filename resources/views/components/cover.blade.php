@props(['title', 'size' => 'md'])
{{-- Sampul buku otomatis: warna ditentukan dari judul, jadi tiap buku punya "wajah" sendiri tanpa upload gambar. --}}
<div {{ $attributes->class(['cover', 'cover-'.$size]) }} style="--h: {{ crc32($title) % 360 }}" aria-hidden="true">
    <span>{{ $title }}</span>
</div>
