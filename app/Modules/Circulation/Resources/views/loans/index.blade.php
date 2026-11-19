@extends('layouts.app')
@section('title', 'Peminjaman')

@section('content')
<div class="page-head">
    <div><h1>Peminjaman</h1><p>Catatan sirkulasi, jatuh tempo, dan denda.</p></div>
    <a class="btn" href="{{ route('loans.create') }}">+ Pinjamkan buku</a>
</div>

<div class="tabs">
    @foreach (['all' => 'Semua', 'borrowed' => 'Dipinjam', 'overdue' => 'Terlambat', 'returned' => 'Dikembalikan'] as $key => $label)
        <a class="{{ $filter === $key ? 'on' : '' }}" href="{{ route('loans.index', $key === 'all' ? [] : ['status' => $key]) }}">{{ $label }}<i>{{ $counts[$key] }}</i></a>
    @endforeach
</div>

<div class="panel" style="padding:.4rem .6rem">
<table>
    <thead><tr><th>#</th><th>Anggota</th><th>Buku</th><th>Jatuh tempo</th><th>Status</th><th>Denda</th><th></th></tr></thead>
    <tbody>
    @forelse ($loans as $loan)
        <tr class="{{ $loan->isOverdue() ? 'late' : '' }}">
            <td><a href="{{ route('loans.show', $loan) }}">{{ $loan->id }}</a></td>
            <td>{{ $memberMap[$loan->member_id]['name'] ?? '(anggota dihapus)' }}</td>
            <td>
                @foreach ($loan->items as $item)
                    <div>{{ $bookMap[$item->book_id]['title'] ?? '(buku dihapus)' }}</div>
                @endforeach
            </td>
            <td>{{ $loan->due_date->format('d M Y') }}</td>
            <td>
                @if ($loan->status === 'returned') <span class="chip ok">Dikembalikan</span>
                @elseif ($loan->isOverdue()) <span class="chip err">Terlambat {{ $loan->daysLate() }} hari</span>
                @else <span class="chip warn">Dipinjam</span> @endif
            </td>
            <td>
                @if ($loan->fine)
                    Rp {{ number_format($loan->fine->amount, 0, ',', '.') }}
                    @if ($loan->fine->paid_at) <span class="chip ok">lunas</span>
                    @else
                        <form method="POST" action="{{ route('fines.pay', $loan->fine) }}" style="margin-top:.25rem">@csrf <button class="btn sm ghost">Tandai lunas</button></form>
                    @endif
                @elseif ($loan->isOverdue())
                    <span class="muted">≈ Rp {{ number_format($loan->runningFine(), 0, ',', '.') }}</span>
                @else - @endif
            </td>
            <td>
                @if ($loan->status === 'borrowed')
                    <form method="POST" action="{{ route('loans.return', $loan) }}">@csrf <button class="btn sm">Kembalikan</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="empty">Tidak ada peminjaman pada filter ini.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
