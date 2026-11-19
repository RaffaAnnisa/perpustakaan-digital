@extends('layouts.app')
@section('title', 'Ringkasan')

@section('content')
<div class="page-head">
    <div>
        <h1>Selamat datang, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p>Kondisi rak hari ini, {{ now()->format('d M Y') }}.</p>
    </div>
    <a class="btn" href="{{ route('loans.create') }}">+ Pinjamkan buku</a>
</div>

<div class="stats">
    <div class="stat"><b>{{ $summary['titles'] }}</b><span>Judul buku</span></div>
    <div class="stat"><b>{{ $summary['available'] }}<small class="muted" style="font-size:1rem"> / {{ $summary['copies'] }}</small></b><span>Eksemplar di rak</span></div>
    <div class="stat"><b>{{ $activeLoans }}</b><span>Sedang dipinjam</span></div>
    <div class="stat {{ $overdue->count() ? 'alarm' : '' }}"><b>{{ $overdue->count() }}</b><span>Terlambat</span></div>
    <div class="stat"><b>Rp {{ number_format($unpaidFines + $runningFines, 0, ',', '.') }}</b><span>Denda belum lunas/berjalan</span></div>
    <div class="stat"><b>{{ $memberCount['active'] }}</b><span>Anggota aktif</span></div>
</div>

<div class="grid2">
    <div class="panel">
        <h2>Perlu tindakan <a class="small" href="{{ route('loans.index', ['status' => 'overdue']) }}">lihat semua</a></h2>
        @forelse ($overdue as $loan)
            <div class="row" style="justify-content:space-between; padding:.6rem 0; border-bottom:1px solid var(--line)">
                <div>
                    <b>{{ $memberMap[$loan->member_id]['name'] ?? '(anggota dihapus)' }}</b>
                    <div class="small muted">
                        {{ $loan->items->map(fn ($i) => $bookMap[$i->book_id]['title'] ?? '(buku dihapus)')->join(', ') }}
                    </div>
                </div>
                <div class="row">
                    <span class="chip err">{{ $loan->daysLate() }} hari · Rp {{ number_format($loan->runningFine(), 0, ',', '.') }}</span>
                    <form method="POST" action="{{ route('loans.return', $loan) }}">@csrf <button class="btn sm">Kembalikan</button></form>
                </div>
            </div>
        @empty
            <div class="empty">Tidak ada keterlambatan. Rak aman.</div>
        @endforelse

        @if ($dueSoon->count())
            <h2 style="margin-top:1.4rem">Jatuh tempo 3 hari ke depan</h2>
            @foreach ($dueSoon as $loan)
                <div class="row" style="justify-content:space-between; padding:.45rem 0">
                    <span>{{ $memberMap[$loan->member_id]['name'] ?? '(anggota dihapus)' }}
                        <span class="muted small">· {{ $loan->items->count() }} buku</span></span>
                    <span class="chip warn">{{ $loan->due_date->isToday() ? 'hari ini' : $loan->due_date->format('d M') }}</span>
                </div>
            @endforeach
        @endif
    </div>

    <div style="display:grid; gap:1.25rem">
        <div class="panel">
            <h2>Paling sering dipinjam</h2>
            @forelse ($top as $row)
                @php($title = $bookMap[$row->book_id]['title'] ?? '(buku dihapus)')
                <div class="row" style="padding:.4rem 0">
                    <x-cover :title="$title" size="sm" />
                    <div style="flex:1">{{ $title }}</div>
                    <span class="chip">{{ $row->total }}×</span>
                </div>
            @empty
                <div class="empty">Belum ada data peminjaman.</div>
            @endforelse
        </div>

        <div class="panel">
            <h2>Stok menipis</h2>
            @foreach ($lowStock as $b)
                <div class="row" style="justify-content:space-between; padding:.3rem 0">
                    <span>{{ $b['title'] }}</span>
                    <span class="chip {{ $b['available_stock'] === 0 ? 'err' : 'warn' }}">{{ $b['available_stock'] }} / {{ $b['stock'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
