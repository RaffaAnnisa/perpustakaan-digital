@extends('layouts.app')
@section('title', 'Pinjamkan Buku')

@section('content')
@php
    $memberData = collect($members)->mapWithKeys(fn ($m) => [$m['id'] => $m + ['active' => $activeCounts[$m['id']] ?? 0]]);
@endphp
<div class="page-head"><div><h1>Pinjamkan buku</h1><p>Pilih anggota dulu; aturan, kuota, dan jatuh tempo muncul otomatis.</p></div></div>

<form method="POST" action="{{ route('loans.store') }}" style="max-width:720px">
    @csrf
    <label class="f">Anggota
        <select name="member_id" id="member">
            <option value="">-- pilih anggota --</option>
            @foreach ($members as $m)
                <option value="{{ $m['id'] }}" @selected(old('member_id') == $m['id'])>{{ $m['member_code'] }} · {{ $m['name'] }} ({{ $m['type'] }})</option>
            @endforeach
        </select>
    </label>

    <div class="rules" id="rules"></div>

    <label class="f">Buku
        <input type="search" id="bsearch" placeholder="Cari judul…" autocomplete="off">
    </label>
    <div class="picker">
        @foreach ($books as $b)
            <label class="pick" data-title="{{ strtolower($b['title']) }}">
                <input type="checkbox" name="book_ids[]" value="{{ $b['id'] }}" @checked(in_array($b['id'], old('book_ids', [])))>
                <x-cover :title="$b['title']" size="sm" />
                <span>{{ $b['title'] }}<small>Tersedia {{ $b['available_stock'] }} eksemplar</small></span>
            </label>
        @endforeach
    </div>

    <p class="muted small" id="summary" style="margin:.8rem 0"></p>
    <div class="row">
        <button class="btn" id="submit" disabled>Proses peminjaman</button>
        <a href="{{ route('loans.index') }}">Batal</a>
    </div>
</form>

<script>
    // Pratinjau saja. Validasi sebenarnya tetap di server (Form Request + LoanService).
    const MEMBERS = @json($memberData);
    const sel = document.getElementById('member'), box = document.getElementById('rules'),
          checks = [...document.querySelectorAll('.pick input')], search = document.getElementById('bsearch'),
          sum = document.getElementById('summary'), submit = document.getElementById('submit');
    const rp = n => 'Rp ' + Number(n).toLocaleString('id-ID');
    const esc = s => String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

    function update() {
        const m = MEMBERS[sel.value], picked = checks.filter(c => c.checked).length;
        if (!m) {
            box.innerHTML = '<span class="muted">Belum ada anggota dipilih.</span>';
            checks.forEach(c => c.disabled = false);
            submit.disabled = true; sum.textContent = ''; return;
        }
        const left = Math.max(0, m.max_loans - m.active);
        const due = new Date(); due.setDate(due.getDate() + m.loan_days);
        box.innerHTML = '<dl>'
            + '<dt>Jenis</dt><dd>' + esc(m.type) + '</dd>'
            + '<dt>Kuota tersisa</dt><dd>' + left + ' dari ' + m.max_loans + ' buku (sedang pinjam ' + m.active + ')</dd>'
            + '<dt>Jatuh tempo</dt><dd>' + due.toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'}) + ' (' + m.loan_days + ' hari)</dd>'
            + '<dt>Denda</dt><dd>' + rp(m.fine_per_day) + ' / hari &nbsp;·&nbsp; telat 7 hari = ' + rp(m.fine_per_day * 7) + '</dd></dl>';
        checks.forEach(c => { c.disabled = !c.checked && picked >= left; c.closest('.pick').classList.toggle('off', c.disabled); });
        submit.disabled = picked === 0 || picked > left;
        sum.textContent = left === 0 ? 'Kuota anggota ini sudah penuh.' : picked + ' buku dipilih.';
    }
    sel.addEventListener('change', update);
    checks.forEach(c => c.addEventListener('change', update));
    search.addEventListener('input', () => document.querySelectorAll('.pick').forEach(p =>
        p.style.display = p.dataset.title.includes(search.value.trim().toLowerCase()) ? '' : 'none'));
    update();
</script>
@endsection
