@extends('layouts.app')
@section('title', 'Riwayat Denda')

@section('content')
<div class="page-head">
    <div><h1>Riwayat denda</h1><p>Denda keterlambatan yang tercatat saat buku dikembalikan.</p></div>
    <a class="btn ghost" href="{{ route('loans.index', ['status' => 'overdue']) }}">Lihat yang masih terlambat</a>
</div>

<div class="stats">
    <div class="stat"><b>Rp {{ number_format($totals['all'], 0, ',', '.') }}</b><span>Total denda tercatat</span></div>
    <div class="stat {{ $totals['unpaid'] > 0 ? 'alarm' : '' }}"><b>Rp {{ number_format($totals['unpaid'], 0, ',', '.') }}</b><span>Belum lunas</span></div>
    <div class="stat"><b>Rp {{ number_format($totals['paid'], 0, ',', '.') }}</b><span>Sudah lunas</span></div>
</div>

<div class="tabs">
    @foreach (['all' => 'Semua', 'unpaid' => 'Belum lunas', 'paid' => 'Lunas'] as $key => $label)
        <a class="{{ $filter === $key ? 'on' : '' }}" href="{{ route('fines.index', $key === 'all' ? [] : ['status' => $key]) }}">{{ $label }}<i>{{ $counts[$key] }}</i></a>
    @endforeach
</div>

<div class="panel" style="padding:.4rem .6rem">
<table>
    <thead><tr><th>Pinjaman</th><th>Anggota</th><th>Buku</th><th>Dikembalikan</th><th>Terlambat</th><th>Denda</th><th>Status</th></tr></thead>
    <tbody>
    @forelse ($fines as $fine)
        @php($loan = $fine->loan)
        <tr>
            <td>@if ($loan)<a href="{{ route('loans.show', $loan) }}">#{{ $loan->id }}</a>@else - @endif</td>
            <td>{{ $loan ? ($memberMap[$loan->member_id]['name'] ?? '(anggota dihapus)') : '-' }}</td>
            <td>
                @if ($loan)
                    @foreach ($loan->items as $item)
                        <div>{{ $bookMap[$item->book_id]['title'] ?? '(buku dihapus)' }}</div>
                    @endforeach
                @endif
            </td>
            <td>{{ $loan?->returned_at?->format('d M Y') ?? '-' }}</td>
            <td>{{ $fine->days_late }} hari</td>
            <td><b>Rp {{ number_format($fine->amount, 0, ',', '.') }}</b></td>
            <td>
                @if ($fine->isPaid())
                    <span class="chip ok">Lunas</span>
                    <div class="small muted">{{ $fine->paid_at->format('d M Y') }}</div>
                @else
                    <span class="chip err">Belum lunas</span>
                    <form method="POST" action="{{ route('fines.pay', $fine) }}" style="margin-top:.35rem">@csrf <button class="btn sm ghost">Tandai lunas</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="empty">Belum ada denda pada filter ini.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
