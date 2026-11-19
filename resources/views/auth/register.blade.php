@extends('layouts.app')
@section('title', 'Daftar')

@section('content')
<h1>Buat akun</h1>
<form method="POST" action="{{ url('/register') }}" class="form">
    @csrf
    <label>Nama
        <input type="text" name="name" value="{{ old('name') }}">
    </label>
    <label>Email
        <input type="text" name="email" value="{{ old('email') }}">
    </label>
    <label>Password
        <input type="password" name="password">
    </label>
    <label>Ulangi password
        <input type="password" name="password_confirmation">
    </label>
    <button class="btn">Daftar</button>
    <a href="{{ route('login') }}">Sudah punya akun</a>
</form>
@endsection
