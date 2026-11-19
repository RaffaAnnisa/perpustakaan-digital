@extends('layouts.app')
@section('title', 'Ubah Buku')

@section('content')
<div class="page-head"><h1>Ubah Buku</h1></div>
<form method="POST" action="{{ route('books.update', $book) }}" class="form">
    @csrf @method('PUT')
    @include('catalog::books._form')
</form>
@endsection
