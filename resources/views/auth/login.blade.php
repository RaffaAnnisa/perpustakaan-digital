@extends('layouts.app')
@section('title', 'Masuk')

@section('content')
<h1>Masuk</h1>
<form method="POST" action="{{ url('/login') }}" class="form">
    @csrf
    <label>Email
        <input type="text" name="email" value="{{ old('email') }}" autofocus>
    </label>
    <label>Password
        <input type="password" name="password">
    </label>
    <button class="btn">Masuk</button>
    <a href="{{ route('register') }}">Buat akun</a>
</form>
@endsection
