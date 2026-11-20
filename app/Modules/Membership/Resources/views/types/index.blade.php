@extends('layouts.app')
@section('title', 'Jenis Keanggotaan')

@section('content')
<div class="head">
    <h1>Jenis Keanggotaan</h1>
    <a class="btn" href="{{ route('membership-types.create') }}">Tambah jenis</a>
</div>

<table>
    <thead>
        <tr><th>Nama</th><th>Maks. buku</th><th>Lama pinjam</th><th>Denda / hari</th><th>Anggota</th><th></th></tr>
    </thead>
    <tbody>
    @forelse ($types as $type)
        <tr>
            <td>{{ $type->name }}</td>
            <td>{{ $type->max_loans }}</td>
            <td>{{ $type->loan_days }} hari</td>
            <td>Rp {{ number_format($type->fine_per_day, 0, ',', '.') }}</td>
            <td>{{ $type->members_count }}</td>
            <td class="actions">
                <a href="{{ route('membership-types.edit', $type) }}">Ubah</a>
                <form method="POST" action="{{ route('membership-types.destroy', $type) }}"
                      onsubmit="return confirm('Hapus jenis keanggotaan ini?')">
                    @csrf @method('DELETE')
                    <button class="link danger">Hapus</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6">Belum ada jenis keanggotaan.</td></tr>
    @endforelse
    </tbody>
</table>
@endsection
