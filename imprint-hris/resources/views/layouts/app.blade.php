<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Imprint HRIS')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script>
        // Apply saved theme before paint to avoid flash.
        if (localStorage.getItem('theme') === 'dark') document.documentElement.setAttribute('data-theme', 'dark');
        function toggleTheme() {
            const el = document.documentElement;
            const dark = el.getAttribute('data-theme') === 'dark';
            if (dark) { el.removeAttribute('data-theme'); localStorage.setItem('theme', 'light'); }
            else { el.setAttribute('data-theme', 'dark'); localStorage.setItem('theme', 'dark'); }
        }
    </script>
</head>
<body>

@php
    $role = auth()->user()->role ?? 'Employee';
    $isManager = in_array($role, ['Admin', 'HR']);

    $taskNav = ['/tasks', 'Tasks', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'];

    if ($isManager) {
        $nav = [
            ['/dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['/employees', 'Employees', 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z'],
            ['/org-chart', 'Org Chart', 'M16 4h4v4h-4zM4 16h4v4H4zM16 16h4v4h-4zM10 4h4v4h-4zM12 8v4M6 16v-2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2'],
            ['/applicants', 'Recruitment', 'M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8zM19 8v6M22 11h-6'],
            ['/departments', 'Departments', 'M3 21h18M5 21V7l7-4 7 4v14M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01'],
            ['/attendance', 'Attendance', 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['/leave', 'Leave', 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z'],
            ['/leave-calendar', 'Leave Calendar', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zM8 14h.01M12 14h.01M16 14h.01'],
            ['/payroll', 'Payroll', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['/loans', 'Loans', 'M3 10h18M7 15h2m4 0h4M5 6h14a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2z'],
            $taskNav,
            ['/reviews', 'Reviews', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.05 10.8c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z'],
            ['/reports', 'Reports', 'M9 19v-6a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2zm0 0V9a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v10m-6 0a2 2 0 0 0 2 2h2a2 2 0 0 0 2-2m0 0V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-2a2 2 0 0 1-2-2z'],
            ['/remittances', 'Remittances', 'M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6'],
            ['/announcements', 'Announcements', 'M3 11l18-5v12L3 14v-3zM11.6 16.8a3 3 0 1 1-5.8-1.6'],
            ['/holidays', 'Holidays', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z'],
        ];
        if ($role === 'Admin') {
            array_splice($nav, 1, 0, [['/projects', 'Projects', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11']]);
            $nav[] = ['/settings', 'Settings', 'M12 15a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z'];
            $nav[] = ['/audit', 'Audit Log', 'M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'];
        }
        $home = '/dashboard';
    } elseif ($role === 'CEO') {
        $nav = [
            ['/exec-dashboard', 'Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['/projects', 'Projects', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
        ];
        $home = '/exec-dashboard';
    } elseif ($role === 'Manager') {
        $nav = [
            ['/projects', 'Projects', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
            $taskNav,
            ['/reviews', 'Reviews', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.05 10.8c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z'],
            ['/team', 'My Team', 'M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z'],
        ];
        $home = '/tasks';
    } elseif ($role === 'Applicant') {
        $nav = [
            ['/apply', 'Overview', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['/apply/documents', 'My Requirements', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 2v6h6M9 13h6M9 17h6'],
            ['/apply/interviews', 'My Interviews', 'M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z'],
        ];
        $home = '/apply';
    } else {
        $nav = [
            ['/portal', 'My Dashboard', 'M3 12l9-9 9 9M5 10v10h5v-6h4v6h5V10'],
            ['/portal/profile', 'My Profile', 'M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z'],
            ['/portal/tasks', 'My Tasks', 'M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11'],
            ['/portal/attendance', 'My Attendance', 'M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['/portal/leave', 'My Leave', 'M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z'],
            ['/portal/payslips', 'My Payslips', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z'],
            ['/portal/reviews', 'My Reviews', 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.05 10.8c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z'],
            ['/portal/documents', 'My Documents', 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 2v6h6M9 13h6M9 17h6'],
        ];
        $home = '/portal';
    }

    $currentPath = '/' . trim(request()->path(), '/');
    $unreadCount = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('user_id', auth()->id())->whereNull('read_at')->count();
@endphp

<input type="checkbox" id="nav-toggle" class="nav-toggle-cb" hidden>

<div class="shell">
    <aside class="sidebar">
        <a href="{{ $home }}" class="brand">
            <div class="brand-icon"><img src="{{ asset('logoic.png') }}" alt="Imprint HRIS"></div>
            <div class="brand-text">
                <span class="brand-name">Imprint HRIS</span>
                <span class="brand-sub">HR Management</span>
            </div>
        </a>

        <nav class="nav">
            @foreach($nav as [$href, $label, $icon])
                @php
                    $active = in_array($href, ['/dashboard', '/portal'])
                        ? $currentPath === $href
                        : ($currentPath === $href || str_starts_with($currentPath, $href . '/'));
                @endphp
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
            <button type="button" class="bell" id="theme-toggle" aria-label="Toggle theme" onclick="toggleTheme()">
                <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
                <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
            <a href="/notifications" class="bell" aria-label="Notifications">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                @if($unreadCount > 0)<span class="bell-dot">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>@endif
            </a>
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
        --bg: #f4f4f5;
        --card: #ffffff;
        --sidebar: #0a0a0a;
        --sidebar-2: #1f1f1f;
        --accent: #0a0a0a;          /* structural accent = black */
        --accent-dark: #000000;
        --accent-soft: #f0f0f0;     /* light gray fills */
        --btn: #facc15;             /* yellow — primary actions only */
        --btn-hover: #eab308;
        --btn-text: #1a1a1a;
        --avatar-bg: #0a0a0a;
        --text: #0a0a0a;
        --text-soft: #334155;        /* body copy inside cards */
        --muted: #6b7280;
        --border: #e4e4e7;
        --border-soft: #f1f5f9;      /* hairlines between rows */
        --border-strong: #cbd5e1;    /* hover borders */
        --surface-2: #f8fafc;        /* inputs, table headers */
        --surface-3: #ffffff;        /* focused input */
        --row-hover: #fafbff;
        --ring: rgba(10,10,10,.12);

        /* Semantic status colors — text on a soft tinted fill. */
        --ok: #15803d;      --ok-soft: #dcfce7;      --ok-line: #86efac;
        --warn: #b45309;    --warn-soft: #fef3c7;    --warn-line: #fcd34d;
        --bad: #b91c1c;     --bad-soft: #fee2e2;     --bad-line: #fca5a5;
        --info: #1d4ed8;    --info-soft: #dbeafe;    --info-line: #93c5fd;
        --neutral: #52525b; --neutral-soft: #f4f4f5; --neutral-line: #d4d4d8;

        /* Accent hues for stat-card icons, cycled across the grid. */
        --c1: #4f46e5; --c1-soft: #e0e7ff;
        --c2: #0891b2; --c2-soft: #cffafe;
        --c3: #ea580c; --c3-soft: #ffedd5;
        --c4: #db2777; --c4-soft: #fce7f3;
        --c5: #16a34a; --c5-soft: #dcfce7;

        --radius: 16px;
        --shadow: 0 1px 3px rgba(0,0,0,.05), 0 12px 32px rgba(0,0,0,.06);
        --sidebar-w: 264px;
    }

    [data-theme="dark"] {
        --bg: #0a0a0a;
        --card: #161616;
        --sidebar: #000000;
        --sidebar-2: #1f1f1f;
        --accent: #f5f5f5;          /* structural accent = near-white */
        --accent-dark: #ffffff;
        --accent-soft: #1f1f1f;
        --btn: #facc15;
        --btn-hover: #eab308;
        --btn-text: #1a1a1a;
        --avatar-bg: #2a2a2a;
        --text: #f5f5f5;
        --text-soft: #d4d4d8;
        --muted: #a1a1aa;
        --border: #2a2a2a;
        --border-soft: #232323;
        --border-strong: #3f3f46;
        --surface-2: #1c1c1c;
        --surface-3: #202020;
        --row-hover: #1c1c1c;
        --ring: rgba(245,245,245,.16);

        /* Dark theme: lift the text, sink the fill, so contrast holds. */
        --ok: #4ade80;      --ok-soft: #0f2a1a;      --ok-line: #166534;
        --warn: #fbbf24;    --warn-soft: #2c2109;    --warn-line: #854d0e;
        --bad: #f87171;     --bad-soft: #2c1214;     --bad-line: #991b1b;
        --info: #60a5fa;    --info-soft: #10203c;    --info-line: #1e40af;
        --neutral: #a1a1aa; --neutral-soft: #1f1f1f; --neutral-line: #3f3f46;

        --c1: #a5b4fc; --c1-soft: #1e1b4b;
        --c2: #67e8f9; --c2-soft: #083344;
        --c3: #fdba74; --c3-soft: #431407;
        --c4: #f9a8d4; --c4-soft: #500724;
        --c5: #86efac; --c5-soft: #052e16;

        --shadow: 0 1px 3px rgba(0,0,0,.4), 0 12px 32px rgba(0,0,0,.5);
    }

    /* Theme toggle icon visibility */
    .icon-moon { display: none; }
    [data-theme="dark"] .icon-sun { display: none; }
    [data-theme="dark"] .icon-moon { display: block; }

    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: 'Inter', system-ui, Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
        -webkit-font-smoothing: antialiased;
    }

    a { color: inherit; }

    /* Visible keyboard focus everywhere; mouse clicks stay clean. */
    :focus-visible { outline: 2px solid var(--btn); outline-offset: 2px; border-radius: 8px; }
    .sidebar :focus-visible { outline-color: var(--btn); }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after { transition-duration: .01ms !important; animation-duration: .01ms !important; }
    }

    /* ---------- Layout ---------- */
    .shell { display: flex; min-height: 100vh; }

    .sidebar {
        position: fixed;
        inset: 0 auto 0 0;
        width: var(--sidebar-w);
        background: var(--sidebar);
        color: var(--border-strong);
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

    /* flex:1 + min-height:0 lets long nav lists (Admin has 18) scroll
       instead of pushing the user card off-screen on short viewports. */
    .nav {
        display: flex; flex-direction: column; gap: 4px;
        flex: 1; min-height: 0; overflow-y: auto;
        scrollbar-width: thin; scrollbar-color: var(--sidebar-2) transparent;
    }
    .nav::-webkit-scrollbar { width: 6px; }
    .nav::-webkit-scrollbar-thumb { background: var(--sidebar-2); border-radius: 999px; }
    .nav-link {
        display: flex; align-items: center; gap: 12px;
        padding: 11px 12px; border-radius: 11px;
        text-decoration: none; color: #94a3b8;
        font-size: 14px; font-weight: 600; transition: .15s ease;
    }
    .nav-link svg { width: 19px; height: 19px; flex-shrink: 0; }
    .nav-link:hover { background: var(--sidebar-2); color: #fff; }
    .nav-link.active { background: #ffffff; color: #0a0a0a; }

    .sidebar-foot { border-top: 1px solid var(--sidebar-2); padding-top: 16px; }
    .user-card { display: flex; align-items: center; gap: 11px; padding: 4px 8px 14px; }
    .user-avatar {
        width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
        background: var(--btn); color: #1a1a1a; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
    }
    .user-meta { display: flex; flex-direction: column; line-height: 1.3; min-width: 0; }
    .user-name { color: var(--border-soft); font-weight: 700; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .user-role { color: #64748b; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }

    .logout-btn {
        width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px;
        background: var(--sidebar-2); color: var(--border-strong); border: none;
        padding: 11px; border-radius: 11px; font-size: 13px; font-weight: 700;
        cursor: pointer; font-family: inherit; transition: .15s ease;
    }
    .logout-btn svg { width: 16px; height: 16px; }
    .logout-btn:hover { background: #ffffff; color: #0a0a0a; }

    .main { flex: 1; margin-left: var(--sidebar-w); min-width: 0; display: flex; flex-direction: column; }

    .topbar {
        position: sticky; top: 0; z-index: 40;
        height: 70px; display: flex; align-items: center; gap: 16px;
        padding: 0 40px;
        background: var(--card); backdrop-filter: blur(12px);
        border-bottom: 1px solid var(--border);
    }
    .topbar-title { font-size: 18px; font-weight: 800; letter-spacing: -.02em; }
    .topbar-date { margin-left: auto; color: var(--muted); font-size: 13px; font-weight: 600; }
    .menu-btn { display: none; cursor: pointer; color: var(--text); }
    .menu-btn svg { width: 24px; height: 24px; display: block; }

    .bell {
        position: relative; display: flex; align-items: center; justify-content: center;
        width: 42px; height: 42px; margin-left: 18px; border-radius: 12px;
        background: var(--card); border: 1px solid var(--border); color: var(--text); flex-shrink: 0;
    }
    .bell:hover { border-color: var(--border-strong); }
    .bell svg { width: 20px; height: 20px; }
    .bell-dot {
        position: absolute; top: -6px; right: -6px; min-width: 18px; height: 18px; padding: 0 4px;
        background: var(--btn); color: var(--btn-text); border-radius: 999px; font-size: 11px; font-weight: 800;
        display: flex; align-items: center; justify-content: center; border: 2px solid var(--card);
    }

    .content { padding: 32px 40px 60px; max-width: 1320px; width: 100%; }

    .scrim { display: none; }

    /* ---------- Alerts ---------- */
    .alert {
        display: flex; align-items: flex-start; gap: 10px;
        padding: 14px 18px; border-radius: var(--radius); font-size: 14px; font-weight: 600;
        margin-bottom: 22px;
    }
    .alert svg { width: 18px; height: 18px; flex-shrink: 0; margin-top: 1px; }
    .alert-success { background: var(--ok-soft); color: var(--ok); border: 1px solid var(--ok-line); border-left: 4px solid var(--ok); }
    .alert-error { background: var(--bad-soft); color: var(--bad); border: 1px solid var(--bad-line); border-left: 4px solid var(--bad); display: block; }
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
    .btn-primary { background: var(--btn); color: var(--btn-text); box-shadow: 0 8px 20px rgba(250,204,21,.3); }
    .btn-primary:hover { background: var(--btn-hover); }
    .btn-ghost { background: var(--card); color: var(--text); border: 1px solid var(--border); }
    .btn-ghost:hover { border-color: var(--border-strong); background: var(--surface-2); }
    /* Destructive and confirming actions carry their meaning in colour. */
    .btn-danger { background: var(--bad-soft); color: var(--bad); border: 1px solid var(--bad-line); }
    .btn-danger:hover { background: var(--bad); color: var(--card); border-color: var(--bad); }
    .btn-sm { padding: 8px 13px; font-size: 13px; border-radius: 10px; }
    .btn-success { background: var(--ok); color: var(--card); }
    .btn-success:hover { filter: brightness(.92); }

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

    /* Cycle accent hues across the stat row so cards read as distinct tiles.
       Colour is decorative here — the label and value carry the meaning. */
    .stat-grid .stat:nth-child(5n+1) .stat-icon { background: var(--c1-soft); color: var(--c1); }
    .stat-grid .stat:nth-child(5n+2) .stat-icon { background: var(--c2-soft); color: var(--c2); }
    .stat-grid .stat:nth-child(5n+3) .stat-icon { background: var(--c3-soft); color: var(--c3); }
    .stat-grid .stat:nth-child(5n+4) .stat-icon { background: var(--c4-soft); color: var(--c4); }
    .stat-grid .stat:nth-child(5n+5) .stat-icon { background: var(--c5-soft); color: var(--c5); }

    /* Thin colour bar keys each card to its icon. */
    .stat::after { content: ''; position: absolute; inset: 0 0 auto 0; height: 3px; }
    .stat-grid .stat:nth-child(5n+1)::after { background: var(--c1); }
    .stat-grid .stat:nth-child(5n+2)::after { background: var(--c2); }
    .stat-grid .stat:nth-child(5n+3)::after { background: var(--c3); }
    .stat-grid .stat:nth-child(5n+4)::after { background: var(--c4); }
    .stat-grid .stat:nth-child(5n+5)::after { background: var(--c5); }
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
        border: 1px solid var(--border); background: var(--surface-2); border-radius: 11px;
        padding: 10px 14px 10px 40px; font-size: 14px; font-weight: 500; font-family: inherit;
        outline: none; width: 260px; color: var(--text);
    }
    .search input:focus { border-color: var(--accent); background: var(--surface-3); box-shadow: 0 0 0 3px var(--ring); }

    /* Standalone filter dropdown (toolbars above tables) */
    .select {
        border: 1px solid var(--border); background: var(--surface-2); color: var(--text);
        border-radius: 11px; padding: 10px 12px; font-size: 14px; font-weight: 600;
        font-family: inherit; outline: none; cursor: pointer; transition: .15s ease;
    }
    .select:hover { border-color: var(--border-strong); }
    .select:focus { border-color: var(--accent); background: var(--surface-3); box-shadow: 0 0 0 3px var(--ring); }
    .select.sm { padding: 8px 10px; font-size: 13px; border-radius: 10px; }

    /* ---------- Table ---------- */
    .table-wrap { width: 100%; overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; min-width: 720px; }
    thead th {
        text-align: left; padding: 13px 24px; color: var(--muted);
        font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em;
        background: var(--surface-2); border-bottom: 1px solid var(--border);
    }
    tbody td { padding: 16px 24px; border-bottom: 1px solid var(--border-soft); font-size: 14px; color: var(--text-soft); font-weight: 500; }
    tbody tr:last-child td { border-bottom: none; }
    tbody tr:hover { background: var(--row-hover); }

    .cell-user { display: flex; align-items: center; gap: 12px; }
    .avatar {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        background: var(--avatar-bg); color: #fff;
        font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 15px;
    }
    .cell-user strong { display: block; color: var(--text); font-weight: 700; font-size: 14px; }
    .cell-user span { display: block; color: var(--muted); font-size: 12.5px; }

    /* ---------- Badges ---------- */
    /* Semantic status colors. Each carries a tinted fill, a matching border and
       a dot, so status is never signalled by hue alone. */
    .badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 11px;
        border: 1px solid var(--neutral-line); border-radius: 999px; font-size: 12px; font-weight: 700;
        background: var(--neutral-soft); color: var(--neutral);
    }
    .badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
    .badge.green { background: var(--ok-soft);   color: var(--ok);   border-color: var(--ok-line); }
    .badge.amber { background: var(--warn-soft); color: var(--warn); border-color: var(--warn-line); }
    .badge.red   { background: var(--bad-soft);  color: var(--bad);  border-color: var(--bad-line); }
    .badge.blue  { background: var(--info-soft); color: var(--info); border-color: var(--info-line); }
    .badge.gray  { background: var(--neutral-soft); color: var(--neutral); border-color: var(--neutral-line); }
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
    .field label { font-size: 13px; font-weight: 700; color: var(--text-soft); }
    .field input, .field select, .field textarea {
        width: 100%; border: 1px solid var(--border); background: var(--surface-2);
        padding: 12px 14px; border-radius: 12px; font-size: 14px; font-weight: 500;
        color: var(--text); font-family: inherit; outline: none; transition: .15s ease;
    }
    .field textarea { resize: vertical; }
    .field input:focus, .field select:focus, .field textarea:focus {
        border-color: var(--accent); background: var(--surface-3); box-shadow: 0 0 0 3px var(--ring);
    }
    .form-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 28px; padding-top: 24px; border-top: 1px solid var(--border); }

    /* ---------- Detail rows ---------- */
    .info-row { display: flex; justify-content: space-between; gap: 16px; padding: 13px 0; border-bottom: 1px solid var(--border-soft); font-size: 14px; }
    .info-row:last-child { border-bottom: none; }
    .info-row .k { color: var(--muted); font-weight: 600; }
    .info-row .v { font-weight: 700; text-align: right; }

    /* ---------- List rows (dashboard) ---------- */
    .list-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 13px 0; border-bottom: 1px solid var(--border-soft); text-decoration: none; }
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
