@extends('layouts.app')
@section('title', 'Ubah Anggota')

@section('content')
<div class="page-head"><h1>Ubah Anggota</h1></div>
<form method="POST" action="{{ route('members.update', $member) }}" class="form">
    @csrf @method('PUT')
    @include('membership::members._form')
</form>
@endsection
