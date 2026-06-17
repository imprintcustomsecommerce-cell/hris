<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Imprint HRIS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

@php
    $role = auth()->user()->role ?? 'Employee';
    $isManager = in_array($role, ['Admin', 'HR']);
    $nav = [
        ['/dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
        ['/employees', 'Employees', 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z'],
        ['/departments', 'Departments', 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01'],
        ['/attendance', 'Attendance', 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['/leave', 'Leave', 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z'],
        ['/payroll', 'Payroll', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
        ['/reports', 'Reports', 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z'],
    ];
@endphp

<input type="checkbox" id="nav-toggle" class="nav-toggle-cb" hidden>

<div class="shell">
    <aside class="sidebar">
        <a href="/dashboard" class="brand">
            <div class="brand-icon"><img src="{{ asset('logoic.png') }}" alt="Imprint HRIS"></div>
            <div class="brand-text">
                <span class="brand-name">Imprint HRIS</span>
                <span class="brand-sub">HR Management</span>
            </div>
        </a>

        <nav class="nav">
            @foreach($nav as [$href, $label, $icon])
                @php $active = $href === '/' ? request()->is('/') : request()->is(ltrim($href, '/') . '*'); @endphp
                <a href="{{ $href }}" class="nav-link {{ $active ? 'active' : '' }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $icon }}"></path></svg>
                    <span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="sidebar-foot">
            <div class="user-card">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
                <div class="user-meta">
                    <span class="user-name">{{ auth()->user()->name ?? 'User' }}</span>
                    <span class="user-role">{{ $role }}</span>
                </div>
            </div>
            <form action="/logout" method="POST">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1"></path></svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <label for="nav-toggle" class="scrim"></label>

    <div class="main">
        <header class="topbar">
            <label for="nav-toggle" class="menu-btn" aria-label="Toggle menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"></path></svg>
            </label>
            <div class="topbar-title">@yield('heading', 'Dashboard')</div>
            <div class="topbar-date">{{ now()->format('l, F d, Y') }}</div>
        </header>

        <main class="content">
            @if(session('success'))
                <div class="alert alert-success">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"></path></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-error">
                    <strong>Please review the form:</strong>
                    <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>

<style>
    :root {
        --bg: #f1f5f9;
        --card: #ffffff;
        --sidebar: #0f172a;
        --sidebar-2: #1e293b;
        --accent: #4f46e5;
        --accent-dark: #4338ca;
        --accent-soft: #eef2ff;
        --text: #0f172a;
        --muted: #64748b;
        --border: #e2e8f0;
        --radius: 16px;
        --shadow: 0 1px 3px rgba(15,23,42,.06), 0 12px 32px rgba(15,23,42,.06);
        --sidebar-w: 264px;
    }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: 'Inter', system-ui, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
        -webkit-font-smoothing: antialiased;
    }

    a { color: inherit; }

    /* ---------- Layout ---------- */
    .shell { display: flex; min-height: 100vh; }

    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: var(--sidebar-w);
        background: var(--sidebar);
        color: #cbd5e1;
        display: flex;
        flex-direction: column;
        padding: 22px 16px;
        z-index: 60;
    }

    .brand { display: flex; align-items: center; gap: 12px; text-decoration: none; padding: 6px 8px 22px; }
    .brand-icon {
        width: 44px; height: 44px; border-radius: 12px; background: #fff;
        display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0;
    }
    .brand-icon img { width: 100%; height: 100%; object-fit: contain; padding: 6px; }
    .brand-text { display: flex; flex-direction: column; line-height: 1.25; }
    .brand-name { color: #fff; font-weight: 800; font-size: 16px; letter-spacing: -.01em; }
    .brand-sub { color: #64748b; font-size: 12px; font-weight: 600; }

    .nav { display: flex; flex-direction: column; gap: 4px; flex: 1; }
    .nav-link {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 12px; border-radius: 11px;
        text-decoration: none; color: #94a3b8;
        font-size: 14px; font-weight: 600; transition: .15s ease;
    }
    .nav-link svg { width: 19px; height: 19px; flex-shrink: 0; }
    .nav-link:hover { background: var(--sidebar-2); color: #fff; }
    .nav-link.active { background: var(--accent); color: #fff; box-shadow: 0 8px 20px rgba(79,70,229,.4); }

    .sidebar-foot { border-top: 1px solid var(--sidebar-2); padding-top: 16px; }
    .user-card { display: flex; align-items: center; gap: 11px; padding: 4px 8px 14px; }
    .user-avatar {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), #7c3aed);
        color: #fff; font-weight: 800; display: flex; align-items: center; justify-content: center;
    }
    .user-meta { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
    .user-name { color: #f1f5f9; font-weight: 700; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-role { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }

    .logout-btn {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        background: var(--sidebar-2); color: #cbd5e1; border: none;
        padding: 11px; border-radius: 11px; font-size: 13px; font-weight: 700;
        cursor: pointer; font-family: inherit; transition: .15s ease;
    }
    .logout-btn svg { width: 16px; height: 16px; }
    .logout-btn:hover { background: #ef4444; color: #fff; }

    .main { flex: 1; margin-left: var(--sidebar-w); min-width: 0; display: flex; flex-direction: column; }

    .topbar {
        position: sticky; top: 0; z-index: 40;
        height: 70px; display: flex; align-items: center; gap: 16px;
        padding: 0 40px;
        background: rgba(241,245,249,.8); backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border);
    }
    .topbar-title { font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
    .topbar-date { margin-left: auto; color: var(--muted); font-size: 13px; font-weight: 600; }
    .menu-btn { display: none; cursor: pointer; color: var(--text); }
    .menu-btn svg { width: 24px; height: 24px; display: block; }

    .content { padding: 32px 40px 60px; max-width: 1320px; width: 100%; }

    .scrim { display: none; }

    /* ---------- Alerts ---------- */
    .alert {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 14px 18px; border-radius: var(--radius); font-size: 14px; font-weight: 600;
        margin-bottom: 22px;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; display: block; }
    .alert-error strong { display: block; margin-bottom: 6px; }
    .alert-error ul { margin: 0; padding-left: 18px; font-weight: 500; }

    /* ---------- Page header ---------- */
    .page-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 26px; }
    .page-head h1 { margin: 0 0 6px; font-size: 30px; font-weight: 800; letter-spacing: -.03em; }
    .page-head p { margin: 0; color: var(--muted); font-size: 15px; }
    .head-actions { display: flex; gap: 10px; flex-shrink: 0; }

    /* ---------- Buttons ---------- */
    .btn {
        display: inline-flex; align-items: center; gap: 8px; justify-content: center;
        text-decoration: none; border: none; cursor: pointer; font-family: inherit;
        padding: 11px 18px; border-radius: 12px; font-size: 14px; font-weight: 700; transition: .15s ease;
        white-space: nowrap;
    }
    .btn svg { width: 16px; height: 16px; }
    .btn-primary { background: var(--accent); color: #fff; box-shadow: 0 8px 20px rgba(79,70,229,.25); }
    .btn-primary:hover { background: var(--accent-dark); }
    .btn-ghost { background: var(--card); color: var(--text); border: 1px solid var(--border); }
    .btn-ghost:hover { border-color: #cbd5e1; background: #f8fafc; }
    .btn-danger { background: #fef2f2; color: #b91c1c; }
    .btn-danger:hover { background: #ef4444; color: #fff; }
    .btn-sm { padding: 8px 13px; font-size: 13px; border-radius: 10px; }
    .btn-success { background: #ecfdf5; color: #047857; }
    .btn-success:hover { background: #10b981; color: #fff; }

    /* ---------- Stat cards ---------- */
    .stat-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 18px; margin-bottom: 26px; }
    .stat {
        background: var(--card); border: 1px solid var(--border); border-radius: var(--radius);
        padding: 22px; box-shadow: var(--shadow); position: relative; overflow: hidden;
    }
    .stat-icon {
        width: 42px; height: 42px; border-radius: 12px; background: var(--accent-soft); color: var(--accent);
        display: flex; align-items: center; justify-content: center; margin-bottom: 16px;
    }
    .stat-icon svg { width: 21px; height: 21px; }
    .stat-label { color: var(--muted); font-size: 13px; font-weight: 600; margin-bottom: 6px; }
    .stat-value { font-size: 28px; font-weight: 800; letter-spacing: -.02em; }
    .stat-sub { color: var(--muted); font-size: 12px; font-weight: 600; margin-top: 4px; }

    /* ---------- Card / panel ---------- */
    .card {
        background: var(--card); border: 1px solid var(--border); border-radius: 20px;
        box-shadow: var(--shadow); overflow: hidden;
    }
    .card-head { padding: 22px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .card-head h2 { margin: 0; font-size: 17px; font-weight: 800; }
    .card-head p { margin: 3px 0 0; color: var(--muted); font-size: 13px; }
    .card-body { padding: 8px 24px 16px; }

    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    .grid-2.lean { grid-template-columns: 1.3fr .7fr; }

    /* ---------- Search ---------- */
    .search {
        position: relative; display: flex; align-items: center;
    }
    .search svg { position: absolute; left: 14px; width: 17px; height: 17px; color: var(--muted); pointer-events: none; }
    .search input {
        border: 1px solid var(--border); background: #f8fafc; border-radius: 11px;
        padding: 10px 14px 10px 40px; font-size: 14px; font-weight: 500; font-family: inherit;
        outline: none; width: 260px; color: var(--text);
    }
    .search input:focus { border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-soft); }

    /* ---------- Table ---------- */
    .table-wrap { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 720px; }
    thead th {
        text-align: left; padding: 13px 24px; color: var(--muted);
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        background: #f8fafc; border-bottom: 1px solid var(--border);
    }
    tbody td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; color: #334155; font-weight: 500; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: #fafbff; }

    .cell-user { display: flex; align-items: center; gap: 12px; }
    .avatar {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        background: linear-gradient(135deg, var(--accent), #7c3aed); color: #fff;
        font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 15px;
    }
    .cell-user strong { display: block; color: var(--text); font-weight: 700; font-size: 14px; }
    .cell-user span { display: block; color: var(--muted); font-size: 12.5px; }

    /* ---------- Badges ---------- */
    .badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px;
        border-radius: 999px; font-size: 12px; font-weight: 700;
        background: #f1f5f9; color: #475569;
    }
    .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .badge.green { background: #dcfce7; color: #15803d; }
    .badge.amber { background: #fef3c7; color: #b45309; }
    .badge.red   { background: #fee2e2; color: #b91c1c; }
    .badge.blue  { background: #dbeafe; color: #1d4ed8; }
    .badge.gray  { background: #f1f5f9; color: #475569; }
    .badge.plain::before { display: none; }

    /* ---------- Row actions ---------- */
    .actions { display: flex; gap: 7px; align-items: center; flex-wrap: wrap; }
    .actions form { margin: 0; }

    /* ---------- Empty state ---------- */
    .empty { text-align: center; padding: 60px 20px; }
    .empty-icon {
        width: 64px; height: 64px; margin: 0 auto 18px; border-radius: 18px;
        background: var(--accent-soft); color: var(--accent);
        display: flex; align-items: center; justify-content: center;
    }
    .empty-icon svg { width: 30px; height: 30px; }
    .empty h3 { margin: 0 0 6px; font-size: 18px; }
    .empty p { margin: 0 0 20px; color: var(--muted); }

    /* ---------- Forms ---------- */
    .form-section + .form-section { margin-top: 30px; padding-top: 30px; border-top: 1px solid var(--border); }
    .form-section h3 { margin: 0 0 18px; font-size: 15px; font-weight: 800; }
    .form-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 18px; }
    .field { display: flex; flex-direction: column; gap: 7px; }
    .field.full { grid-column: span 2; }
    .field label { font-size: 13px; font-weight: 700; color: #334155; }
    .field input, .field select, .field textarea {
        width: 100%; border: 1px solid var(--border); background: #f8fafc;
        padding: 12px 14px; border-radius: 12px; font-size: 14px; font-weight: 500;
        color: var(--text); font-family: inherit; outline: none; transition: .15s ease;
    }
    .field textarea { resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus {
        border-color: var(--accent); background: #fff; box-shadow: 0 0 0 3px var(--accent-soft);
    }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border); }

    /* ---------- Detail rows ---------- */
    .info-row { display: flex; justify-content: space-between; gap: 16px; padding: 13px 0; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row .k { color: var(--muted); font-weight: 600; }
    .info-row .v { font-weight: 700; text-align: right; }

    /* ---------- List rows (dashboard) ---------- */
    .list-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 13px 0; border-bottom: 1px solid #f1f5f9; text-decoration: none; }
    .list-row:last-child { border-bottom: none; }
    .list-row:hover .lr-title { color: var(--accent); }
    .lr-title { display: block; font-weight: 700; font-size: 14px; color: var(--text); margin-bottom: 2px; }
    .lr-sub { display: block; color: var(--muted); font-size: 12.5px; }
    .muted { color: var(--muted); font-size: 14px; padding: 8px 0; }

    /* ---------- Profile header ---------- */
    .profile-hero { display: flex; align-items: center; gap: 20px; }
    .profile-hero .avatar { width: 72px; height: 72px; border-radius: 20px; font-size: 28px; }
    .profile-hero h1 { margin: 0 0 4px; font-size: 26px; }
    .profile-hero p { margin: 0; color: var(--muted); }

    /* ---------- Responsive ---------- */
    @media (max-width: 1100px) { .stat-grid { grid-template-columns: repeat(2, 1fr); } }
    @media (max-width: 900px) {
        .sidebar { transform: translateX(-100%); transition: transform .25s ease; }
        .nav-toggle-cb:checked ~ .shell .sidebar { transform: translateX(0); }
        .nav-toggle-cb:checked ~ .shell .scrim { display: block; position: fixed; inset: 0; background: rgba(15,23,42,.5); z-index: 55; }
        .main { margin-left: 0; }
        .menu-btn { display: block; }
        .topbar { padding: 0 20px; }
        .content { padding: 24px 20px 50px; }
        .grid-2, .grid-2.lean { grid-template-columns: 1fr; }
        .form-grid { grid-template-columns: 1fr; }
        .field.full { grid-column: span 1; }
        .topbar-date { display: none; }
    }
    @media (max-width: 560px) {
        .stat-grid { grid-template-columns: 1fr; }
        .page-head { flex-direction: column; align-items: stretch; }
        .head-actions .btn { flex: 1; }
        .profile-hero { flex-direction: column; text-align: center; }
    }
</style>

</body>
</html>
