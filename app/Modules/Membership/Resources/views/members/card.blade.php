@extends('layouts.app')
@section('title', 'Kartu '.$member->member_code)

@section('content')
<div class="page-head no-print">
    <div><h1>Kartu anggota</h1><p>Cetak lalu laminasi, atau tampilkan di layar saat meminjam.</p></div>
    <div class="row"><button class="btn" onclick="window.print()">Cetak</button><a class="btn ghost" href="{{ route('members.index') }}">Kembali</a></div>
</div>

<div class="idcard">
    <small>Rak Digital · {{ $member->type->name }}</small>
    <h2>{{ $member->name }}</h2>
    <div style="opacity:.8">{{ $member->member_code }}</div>
    <div class="small" style="opacity:.75; margin-top:.6rem">
        Maks {{ $member->type->max_loans }} buku · {{ $member->type->loan_days }} hari pinjam
    </div>
    {{-- pola garis dibentuk dari kode anggota, hanya hiasan (bukan barcode yang bisa dipindai) --}}
    <div class="barcode" aria-hidden="true">
        @foreach (str_split($member->member_code) as $ch)
            <i style="width: {{ 1 + (ord($ch) % 4) }}px"></i><i style="width:2px; background:#fff"></i>
        @endforeach
    </div>
</div>
@endsection
