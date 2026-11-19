@extends('layouts.app')
@section('title', 'Slip Peminjaman #'.$loan->id)

@section('content')
<div class="page-head no-print">
    <div><h1>Slip peminjaman</h1><p>Cetak atau berikan ke anggota sebagai pengingat jatuh tempo.</p></div>
    <div class="row"><button class="btn" onclick="window.print()">Cetak</button><a class="btn ghost" href="{{ route('loans.index') }}">Kembali</a></div>
</div>

<div class="slip">
    <h2>Rak Digital</h2>
    <div style="text-align:center" class="muted">Slip peminjaman #{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</div>
    <hr>
    <div class="li"><span>Anggota</span><b>{{ $member['name'] ?? '(anggota dihapus)' }}</b></div>
    <div class="li"><span>Kode</span><span>{{ $member['member_code'] ?? '-' }}</span></div>
    <div class="li"><span>Dipinjam</span><span>{{ $loan->loan_date->format('d M Y') }}</span></div>
    <div class="li"><span>Jatuh tempo</span><b>{{ $loan->due_date->format('d M Y') }}</b></div>
    <hr>
    @foreach ($loan->items as $item)
        <div class="li"><span>{{ $bookMap[$item->book_id]['title'] ?? '(buku dihapus)' }}</span><span>{{ $item->returned_at ? 'kembali' : '' }}</span></div>
    @endforeach
    <hr>
    <div class="li"><span>Denda/hari</span><span>Rp {{ number_format($loan->fine_per_day, 0, ',', '.') }}</span></div>
    @if ($loan->fine)
        <div class="li"><span>Denda ({{ $loan->fine->days_late }} hari)</span><b>Rp {{ number_format($loan->fine->amount, 0, ',', '.') }}</b></div>
    @elseif ($loan->isOverdue())
        <div class="li"><span>Denda berjalan</span><b>Rp {{ number_format($loan->runningFine(), 0, ',', '.') }}</b></div>
    @endif
    <hr>
    <div class="muted small">Dilayani oleh {{ $staff->name ?? '-' }}</div>
</div>
@endsection
