@extends('layouts.app')
@section('title', 'Koleksi Buku')

@section('content')
<div class="page-head">
    <div><h1>Koleksi buku</h1><p>{{ $books->count() }} judul{{ $q !== '' || $categoryId ? ' ditemukan' : ' di rak' }}.</p></div>
    <a class="btn" href="{{ route('books.create') }}">+ Tambah buku</a>
</div>

<form method="GET" class="filters">
    <input type="search" name="q" value="{{ $q }}" placeholder="Cari judul, penulis, atau ISBN…">
    <select name="category" onchange="this.form.submit()">
        <option value="">Semua kategori</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected($categoryId == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
    <button class="btn ghost">Cari</button>
    @if ($q !== '' || $categoryId) <a class="btn sm ghost" href="{{ route('books.index') }}" style="align-self:center">Reset filter</a> @endif
</form>

@if ($books->isEmpty())
    <div class="panel empty">Tidak ada buku yang cocok.</div>
@else
<div class="shelf">
    @foreach ($books as $book)
        @php($ratio = $book->stock > 0 ? round($book->available_stock / $book->stock * 100) : 0)
        <article class="book">
            <div class="plank"><x-cover :title="$book->title" /></div>
            <div>
                <div style="margin-bottom:.3rem"><span class="chip">{{ $book->category->name }}</span></div>
                <h3>{{ $book->title }}</h3>
                <div class="meta">{{ $book->authors->pluck('name')->join(', ') }}</div>
                <div class="meta">ISBN: {{ $book->isbn }}</div>
            </div>
            <div>
                <div class="bar {{ $book->available_stock === 0 ? 'zero' : ($ratio <= 34 ? 'low' : '') }}"><i style="width: {{ $ratio }}%"></i></div>
                <div class="small muted" style="margin-top:.3rem">
                    @if ($book->available_stock === 0)
                        <span class="chip err">Habis dipinjam</span>
                    @elseif ($ratio <= 34)
                        <span class="chip warn">Sisa {{ $book->available_stock }} dari {{ $book->stock }}</span>
                    @else
                        <span class="chip ok">{{ $book->available_stock }} dari {{ $book->stock }} tersedia</span>
                    @endif
                </div>
            </div>
            <div class="row small">
                @if ($book->available_stock > 0)
                    <a href="{{ route('loans.create') }}">Pinjam</a>
                @endif
                <a href="{{ route('books.edit', $book) }}">Ubah</a>
                @if (auth()->user()->role === 'admin')
                    <form method="POST" action="{{ route('books.destroy', $book) }}" onsubmit="return confirm('Hapus buku ini?')">
                        @csrf @method('DELETE')
                        <button class="link danger">Hapus</button>
                    </form>
                @endif
            </div>
        </article>
    @endforeach
</div>
@endif
@endsection
