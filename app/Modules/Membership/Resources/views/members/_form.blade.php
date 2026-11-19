<label>Nama
    <input type="text" name="name" value="{{ old('name', $member->name ?? '') }}">
</label>
<label>Kode anggota
    <input type="text" name="member_code" value="{{ old('member_code', $member->member_code ?? '') }}">
</label>
<label>Email
    <input type="text" name="email" value="{{ old('email', $member->email ?? '') }}">
</label>
<label>Jenis keanggotaan
    <select name="membership_type_id">
        <option value="">-- pilih --</option>
        @foreach ($types as $t)
            <option value="{{ $t->id }}" @selected(old('membership_type_id', $member->membership_type_id ?? null) == $t->id)>
                {{ $t->name }} (maks {{ $t->max_loans }} buku, {{ $t->loan_days }} hari)
            </option>
        @endforeach
    </select>
</label>
<label>Status
    <select name="status">
        <option value="active" @selected(old('status', $member->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $member->status ?? '') === 'inactive')>Nonaktif</option>
    </select>
</label>
<button class="btn">Simpan</button>
<a href="{{ route('members.index') }}">Batal</a>
