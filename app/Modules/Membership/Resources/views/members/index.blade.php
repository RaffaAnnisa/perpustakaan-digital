@extends('layouts.app')
@section('title', 'Anggota')

@section('content')
<div class="page-head">
    <div><h1>Anggota</h1><p>Khusus admin. Aturan pinjam mengikuti jenis keanggotaan.</p></div>
    <a class="btn" href="{{ route('members.create') }}">+ Tambah anggota</a>
</div>

<div class="panel" style="padding:.4rem .6rem">
<table>
    <thead><tr><th>Anggota</th><th>Jenis & aturan</th><th>Status</th><th></th></tr></thead>
    <tbody>
    @forelse ($members as $m)
        <tr>
            <td><b>{{ $m->name }}</b><div class="small muted">{{ $m->member_code }}{{ $m->email ? ' · '.$m->email : '' }}</div></td>
            <td>{{ $m->type->name }}<div class="small muted">maks {{ $m->type->max_loans }} buku · {{ $m->type->loan_days }} hari · denda Rp {{ number_format($m->type->fine_per_day, 0, ',', '.') }}/hari</div></td>
            <td><span class="chip {{ $m->status === 'active' ? 'ok' : '' }}">{{ $m->status === 'active' ? 'Aktif' : 'Nonaktif' }}</span></td>
            <td>
                <div class="row small">
                    <a href="{{ route('members.card', $m) }}">Kartu</a>
                    <a href="{{ route('members.edit', $m) }}">Ubah</a>
                    <form method="POST" action="{{ route('members.destroy', $m) }}" onsubmit="return confirm('Hapus anggota ini?')">
                        @csrf @method('DELETE')
                        <button class="link danger">Hapus</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="4" class="empty">Belum ada anggota.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
@endsection
