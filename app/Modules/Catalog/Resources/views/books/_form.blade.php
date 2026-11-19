@php($selected = old('author_ids', isset($book) ? $book->authors->pluck('id')->all() : []))

<label>Judul
    <input type="text" name="title" value="{{ old('title', $book->title ?? '') }}">
</label>
<label>ISBN
    <input type="text" name="isbn" value="{{ old('isbn', $book->isbn ?? '') }}">
</label>
<label>Kategori
    <select name="category_id">
        <option value="">-- pilih --</option>
        @foreach ($categories as $c)
            <option value="{{ $c->id }}" @selected(old('category_id', $book->category_id ?? null) == $c->id)>{{ $c->name }}</option>
        @endforeach
    </select>
</label>
<label>Total stok
    <input type="number" name="stock" min="0" value="{{ old('stock', $book->stock ?? 0) }}">
</label>
<label>Penulis (tahan Ctrl/Cmd untuk memilih lebih dari satu)
    <select name="author_ids[]" multiple size="6">
        @foreach ($authors as $a)
            <option value="{{ $a->id }}" @selected(in_array($a->id, $selected))>{{ $a->name }}</option>
        @endforeach
    </select>
</label>
<button class="btn">Simpan</button>
<a href="{{ route('books.index') }}">Batal</a>
