<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Rak') · Perpustakaan Digital</title>
    <style>
        :root {
            --paper:#f4efe5; --card:#fffdf8; --ink:#23201b; --muted:#797164; --line:#e4dccb;
            --accent:#b4492d; --accent-soft:#f6e3dc; --green:#2f5d50; --green-soft:#dfeae4;
            --amber:#9a6412; --amber-soft:#f8ecd2; --err:#b42318; --err-soft:#fbe4e1;
            --serif: Georgia, "Iowan Old Style", "Palatino Linotype", serif;
            --sans: system-ui, -apple-system, "Segoe UI", sans-serif;
            --shadow: 0 1px 0 rgba(35,32,27,.05), 0 10px 24px -14px rgba(35,32,27,.25);
        }
        * { box-sizing:border-box; }
        body { margin:0; font:15px/1.5 var(--sans); color:var(--ink); background:var(--paper); }
        a { color:var(--accent); }
        h1,h2,h3 { font-family:var(--serif); font-weight:700; letter-spacing:-.01em; margin:0; }
        h1 { font-size:1.9rem; } h2 { font-size:1.15rem; }
        .muted { color:var(--muted); } .small { font-size:.85rem; }

        /* kerangka */
        .shell { display:grid; grid-template-columns:230px 1fr; min-height:100vh; }
        .side { background:#26231e; color:#e9e2d3; padding:1.5rem 1rem; position:sticky; top:0; height:100vh; display:flex; flex-direction:column; gap:.25rem; }
        .brand { font-family:var(--serif); font-size:1.35rem; color:#fff; padding:0 .6rem 1.25rem; line-height:1.1; }
        .brand small { display:block; font:600 .68rem var(--sans); letter-spacing:.14em; text-transform:uppercase; color:#d9a48f; margin-bottom:.35rem; }
        .side a.nav { color:#e9e2d3; text-decoration:none; padding:.55rem .7rem; border-radius:8px; display:flex; justify-content:space-between; }
        .side a.nav:hover { background:#38342d; }
        .side a.nav.on { background:var(--accent); color:#fff; }
        .side .who { margin-top:auto; padding:.8rem .7rem; border-top:1px solid #3d3931; font-size:.85rem; }
        .side .who b { display:block; color:#fff; }
        .role { display:inline-block; margin:.3rem 0 .6rem; padding:.05rem .5rem; border-radius:99px; background:#3d3931; font-size:.72rem; letter-spacing:.06em; text-transform:uppercase; }
        .side .logout { background:none; border:1px solid #4a463d; color:#e9e2d3; border-radius:8px; padding:.35rem .7rem; cursor:pointer; width:100%; }
        main { padding:2rem clamp(1rem,3vw,2.5rem); max-width:1180px; width:100%; }
        .guest { max-width:420px; margin:6vh auto; padding:0 1rem; }
        .guest .brand { color:var(--ink); padding-left:0; } .guest .brand small { color:var(--accent); }

        /* komponen */
        .page-head { display:flex; justify-content:space-between; align-items:flex-end; gap:1rem; flex-wrap:wrap; margin-bottom:1.5rem; }
        .page-head p { margin:.25rem 0 0; color:var(--muted); }
        .btn { display:inline-block; background:var(--accent); color:#fff; border:1px solid var(--accent); padding:.55rem 1.05rem; border-radius:8px; cursor:pointer; text-decoration:none; font:600 .92rem var(--sans); }
        .btn:hover { filter:brightness(1.07); } .btn[disabled] { opacity:.45; cursor:not-allowed; }
        .btn.ghost { background:transparent; color:var(--ink); border-color:#cfc6b2; }
        .btn.sm { padding:.28rem .65rem; font-size:.82rem; }
        .link { background:none; border:0; color:var(--accent); cursor:pointer; padding:0; font:inherit; } .link.danger { color:var(--err); }
        .row { display:flex; gap:.7rem; align-items:center; flex-wrap:wrap; } .row form { margin:0; }
        .panel { background:var(--card); border:1px solid var(--line); border-radius:12px; box-shadow:var(--shadow); padding:1.15rem 1.25rem; }
        .panel > h2 { margin-bottom:.8rem; display:flex; justify-content:space-between; align-items:baseline; }
        .grid2 { display:grid; grid-template-columns:3fr 2fr; gap:1.25rem; align-items:start; }
        .chip { display:inline-block; padding:.08rem .6rem; border-radius:99px; font-size:.76rem; font-weight:600; background:#ece6d7; color:#5d5649; }
        .chip.ok { background:var(--green-soft); color:var(--green); } .chip.warn { background:var(--amber-soft); color:var(--amber); } .chip.err { background:var(--err-soft); color:var(--err); }
        .alert { padding:.8rem 1rem; border-radius:10px; margin-bottom:1.25rem; border:1px solid; }
        .alert.error { background:var(--err-soft); color:var(--err); border-color:#f3b9b3; } .alert.success { background:var(--green-soft); color:var(--green); border-color:#b5d0c3; }
        .alert ul { margin:.3rem 0 0; padding-left:1.1rem; }
        .empty { text-align:center; padding:2.5rem 1rem; color:var(--muted); }

        /* statistik */
        .stats { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:1rem; margin-bottom:1.5rem; }
        .stat { background:var(--card); border:1px solid var(--line); border-radius:12px; padding:1rem 1.1rem; box-shadow:var(--shadow); }
        .stat b { display:block; font:700 1.9rem/1.1 var(--serif); } .stat span { color:var(--muted); font-size:.82rem; }
        .stat.alarm { background:var(--err-soft); border-color:#f3b9b3; } .stat.alarm b { color:var(--err); }

        /* tabel & tab */
        table { width:100%; border-collapse:collapse; } th,td { text-align:left; padding:.7rem .8rem; border-bottom:1px solid var(--line); vertical-align:top; }
        th { font:600 .72rem var(--sans); letter-spacing:.08em; text-transform:uppercase; color:var(--muted); }
        tr.late td { background:#fdf3f1; } tr:last-child td { border-bottom:0; }
        .tabs { display:flex; gap:.4rem; margin-bottom:1rem; flex-wrap:wrap; }
        .tabs a { text-decoration:none; color:var(--ink); padding:.35rem .85rem; border-radius:99px; border:1px solid var(--line); background:var(--card); font-size:.88rem; }
        .tabs a.on { background:var(--ink); color:#fff; border-color:var(--ink); } .tabs a i { font-style:normal; opacity:.6; margin-left:.3rem; }

        /* sampul & rak */
        .cover { position:relative; aspect-ratio:2/3; border-radius:3px 7px 7px 3px; padding:.8rem .7rem .8rem 1rem; display:flex; align-items:flex-end; color:#fff; overflow:hidden;
                 background:linear-gradient(155deg, hsl(var(--h) 42% 34%), hsl(calc(var(--h) + 35) 48% 20%)); box-shadow:inset 7px 0 0 rgba(0,0,0,.22), 3px 6px 12px -4px rgba(0,0,0,.45); }
        .cover::after { content:""; position:absolute; inset:0 auto 0 0; width:2px; left:7px; background:rgba(255,255,255,.18); }
        .cover span { font:700 .95rem/1.15 var(--serif); text-shadow:0 1px 2px rgba(0,0,0,.4); }
        .cover-sm { width:42px; } .cover-sm span { display:none; } .cover-sm { aspect-ratio:2/3; padding:0; }
        .cover-lg span { font-size:1.2rem; }
        .shelf { display:grid; grid-template-columns:repeat(auto-fill,minmax(190px,1fr)); gap:1.6rem 1.25rem; }
        .book { display:flex; flex-direction:column; gap:.55rem; }
        .book .plank { padding-bottom:.55rem; border-bottom:6px solid #cdbb96; border-radius:0 0 3px 3px; }
        .book h3 { font-size:1rem; line-height:1.25; } .book .meta { font-size:.82rem; color:var(--muted); }
        .bar { height:6px; background:#e7dfcd; border-radius:9px; overflow:hidden; } .bar i { display:block; height:100%; background:var(--green); } .bar.low i { background:var(--amber); } .bar.zero i { background:var(--err); }
        .filters { display:flex; gap:.6rem; margin-bottom:1.5rem; flex-wrap:wrap; }

        /* formulir */
        .form { background:var(--card); border:1px solid var(--line); padding:1.4rem; max-width:560px; border-radius:12px; box-shadow:var(--shadow); }
        .form label, label.f { display:block; margin-bottom:1rem; font-weight:600; font-size:.88rem; }
        input[type=text],input[type=password],input[type=number],input[type=search],select { display:block; width:100%; margin-top:.3rem; padding:.55rem .7rem; border:1px solid #cfc6b2; border-radius:8px; font:inherit; background:#fff; color:var(--ink); }
        input:focus,select:focus { outline:2px solid var(--accent); outline-offset:1px; }
        .filters input,.filters select { margin:0; width:auto; min-width:200px; }

        /* peminjaman */
        .picker { max-height:340px; overflow:auto; border:1px solid var(--line); border-radius:10px; background:#fff; }
        .pick { display:flex; gap:.8rem; align-items:center; padding:.55rem .8rem; border-bottom:1px solid var(--line); cursor:pointer; font-weight:400; margin:0 !important; }
        .pick:last-child { border-bottom:0; } .pick:hover { background:#faf6ec; } .pick input { width:auto; margin:0; }
        .pick.off { opacity:.4; cursor:not-allowed; } .pick small { color:var(--muted); display:block; }
        .rules { background:var(--accent-soft); border-radius:10px; padding:.9rem 1rem; font-size:.9rem; margin-bottom:1rem; }
        .rules dl { display:grid; grid-template-columns:auto 1fr; gap:.15rem 1rem; margin:0; } .rules dt { color:var(--muted); } .rules dd { margin:0; font-weight:600; }

        /* slip & kartu */
        .slip { background:#fff; border:1px dashed #b9ae95; max-width:460px; padding:1.6rem; font-family:"Courier New",monospace; font-size:.9rem; }
        .slip h2 { font-family:var(--serif); text-align:center; margin-bottom:.2rem; } .slip hr { border:0; border-top:1px dashed #b9ae95; margin:.9rem 0; }
        .slip .li { display:flex; justify-content:space-between; gap:1rem; }
        .idcard { width:340px; border-radius:16px; overflow:hidden; background:linear-gradient(135deg,#2f5d50,#1d3b32); color:#fff; padding:1.3rem; box-shadow:var(--shadow); }
        .idcard small { letter-spacing:.14em; text-transform:uppercase; opacity:.7; font-size:.68rem; } .idcard h2 { color:#fff; font-size:1.45rem; margin:.5rem 0 .1rem; }
        .barcode { display:flex; align-items:stretch; height:44px; gap:2px; background:#fff; padding:6px 8px; border-radius:6px; margin-top:1.1rem; } .barcode i { display:block; background:#111; }
        @media print { .side, .no-print { display:none !important; } .shell { display:block; } body { background:#fff; } main { padding:0; } }
        @media (max-width:860px) { .shell { grid-template-columns:1fr; } .side { position:static; height:auto; flex-direction:row; flex-wrap:wrap; align-items:center; padding:.8rem; } .brand { padding:0 1rem 0 0; } .side .who { margin:0 0 0 auto; border:0; padding:0; } .grid2 { grid-template-columns:1fr; } }
    </style>
</head>
<body>
@php($isAdmin = auth()->check() && auth()->user()->role === 'admin')
@auth
<div class="shell">
    <aside class="side no-print">
        <div class="brand"><small>Perpustakaan</small>Rak Digital</div>
        <a class="nav {{ request()->routeIs('dashboard') ? 'on' : '' }}" href="{{ route('dashboard') }}">Ringkasan</a>
        <a class="nav {{ request()->routeIs('books.*') ? 'on' : '' }}" href="{{ route('books.index') }}">Koleksi buku</a>
        <a class="nav {{ request()->routeIs('loans.*') ? 'on' : '' }}" href="{{ route('loans.index') }}">Peminjaman</a>
        @if ($isAdmin)
            <a class="nav {{ request()->routeIs('members.*') ? 'on' : '' }}" href="{{ route('members.index') }}">Anggota</a>
        <a href="{{ route('membership-types.index') }}">Jenis Anggota</a>
        @endif
        <div class="who">
            <b>{{ auth()->user()->name }}</b>
            <span class="role">{{ auth()->user()->role }}</span>
            <form method="POST" action="{{ route('logout') }}">@csrf <button class="logout">Keluar</button></form>
        </div>
    </aside>
    <main>
@else
    <div class="guest">
        <div class="brand"><small>Perpustakaan</small>Rak Digital</div>
@endauth
        @if (session('success')) <div class="alert success">{{ session('success') }}</div> @endif
        @if (session('error')) <div class="alert error">{{ session('error') }}</div> @endif
        @if ($errors->any())
            <div class="alert error">
                <strong>Data belum bisa disimpan:</strong>
                <ul>@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
            </div>
        @endif

        @yield('content')
@auth
    </main>
</div>
@else
    </div>
@endauth
</body>
</html>
