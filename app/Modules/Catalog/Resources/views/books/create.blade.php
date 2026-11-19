@extends('layouts.app')
@section('title', 'Tambah Buku')

@section('content')
<div class="page-head"><h1>Tambah buku</h1></div>
<form method="POST" action="{{ route('books.store') }}" class="form">
    @csrf
    @include('catalog::books._form')
</form>
@endsection
