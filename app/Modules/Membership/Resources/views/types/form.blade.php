@extends('layouts.app')
@section('title', isset($type) ? 'Ubah Jenis Keanggotaan' : 'Tambah Jenis Keanggotaan')

@section('content')
<h1>{{ isset($type) ? 'Ubah Jenis Keanggotaan' : 'Tambah Jenis Keanggotaan' }}</h1>
<form method="POST" class="form"
      action="{{ isset($type) ? route('membership-types.update', $type) : route('membership-types.store') }}">
    @csrf
    @isset($type) @method('PUT') @endisset

    <label>Nama
        <input type="text" name="name" value="{{ old('name', $type->name ?? '') }}" autofocus>
    </label>
    <label>Maksimal buku dipinjam bersamaan
        <input type="number" name="max_loans" min="1" value="{{ old('max_loans', $type->max_loans ?? 3) }}">
    </label>
    <label>Lama peminjaman (hari)
        <input type="number" name="loan_days" min="1" value="{{ old('loan_days', $type->loan_days ?? 7) }}">
    </label>
    <label>Denda per hari terlambat (Rp)
        <input type="number" name="fine_per_day" min="0" value="{{ old('fine_per_day', $type->fine_per_day ?? 1000) }}">
    </label>
    <button class="btn">Simpan</button>
    <a href="{{ route('membership-types.index') }}">Batal</a>
</form>
@endsection
