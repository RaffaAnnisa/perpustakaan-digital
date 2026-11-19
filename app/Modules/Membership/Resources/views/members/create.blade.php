@extends('layouts.app')
@section('title', 'Tambah Anggota')

@section('content')
<div class="page-head"><h1>Tambah Anggota</h1></div>
<form method="POST" action="{{ route('members.store') }}" class="form">
    @csrf
    @include('membership::members._form')
</form>
@endsection
