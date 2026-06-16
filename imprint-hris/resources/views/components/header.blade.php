<header class="header">
    <div class="header-inner">
        <a href="/" class="brand">
            <div class="brand-icon">
                <img src="{{ asset('logoic.png') }}" alt="Imprint HRIS">
            </div>

            <div class="brand-text">
                <div class="brand-name">Imprint HRIS</div>
                <div class="brand-sub">Employee Management System</div>
            </div>
        </a>

        <nav class="nav">
    <a href="/" class="{{ request()->is('/') ? 'active' : '' }}">Home</a>

    <a href="/employees" class="{{ request()->is('employees*') ? 'active' : '' }}">Employees</a>

    <a href="/departments" class="{{ request()->is('departments*') ? 'active' : '' }}">Departments</a>

    <a href="/attendance" class="{{ request()->is('attendance*') ? 'active' : '' }}">Attendance</a>

    <a href="/leave" class="{{ request()->is('leave*') ? 'active' : '' }}">Leave</a>

    <a href="/payroll" class="{{ request()->is('payroll*') ? 'active' : '' }}">Payroll</a>

    <a href="/reports" class="{{ request()->is('reports*') ? 'active' : '' }}">Reports</a>
</nav>

        @auth
        <div class="user-box">
            <div class="user-meta">
                <span class="user-name">{{ auth()->user()->name }}</span>
                <span class="user-role">{{ auth()->user()->role }}</span>
            </div>
            <form action="/logout" method="POST" style="margin:0;">
                @csrf
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
        @endauth
    </div>
</header>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Inter, Arial, sans-serif;
        background: #f6f7fb;
        color: #111827;
    }

    .header {
        position: sticky;
        top: 0;
        z-index: 50;
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid rgba(229, 231, 235, 0.9);
    }

    .header-inner {
        max-width: 1280px;
        width: 100%;
        margin: 0 auto;
        height: 78px;
        padding: 0 32px;

        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
    }

    .brand {
        display: flex;
        align-items: center;
        gap: 14px;

        text-decoration: none;
        color: #111827;
        min-width: 0;
    }

    .brand-icon {
        width: 56px;
        height: 56px;
        flex: 0 0 56px;

        border-radius: 16px;
        background: white;

        display: flex;
        align-items: center;
        justify-content: center;

        overflow: hidden;
        border: 1px solid #e5e7eb;
        box-shadow: 0 10px 30px rgba(0, 0, 0, .08);
    }

    .brand-icon img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 8px;
        display: block;
    }

    .brand-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.2;
    }

    .brand-name {
        font-size: 18px;
        font-weight: 800;
        letter-spacing: -0.02em;
    }

    .brand-sub {
        margin-top: 3px;
        font-size: 12px;
        color: #6b7280;
        white-space: nowrap;
    }

    .nav {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;

        padding: 6px;
        background: #f3f4f6;
        border-radius: 16px;
        flex-shrink: 0;
    }

    .nav a {
        display: flex;
        align-items: center;
        justify-content: center;

        min-height: 40px;
        padding: 10px 14px;
        border-radius: 12px;

        text-decoration: none;
        color: #4b5563;
        font-size: 14px;
        font-weight: 700;

        transition: .2s ease;
    }

    .nav a:hover,
    .nav a.active {
        background: white;
        color: #111827;
        box-shadow: 0 8px 20px rgba(17, 24, 39, 0.08);
    }

    .user-box {
        display: flex;
        align-items: center;
        gap: 14px;
        flex-shrink: 0;
    }

    .user-meta {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        line-height: 1.2;
    }

    .user-name {
        font-size: 14px;
        font-weight: 800;
        color: #111827;
    }

    .user-role {
        font-size: 11px;
        font-weight: 700;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: .04em;
    }

    .logout-btn {
        border: 1px solid #e5e7eb;
        background: #f3f4f6;
        color: #111827;
        padding: 10px 14px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
    }

    .logout-btn:hover {
        background: #111827;
        color: white;
    }

    @media (max-width: 900px) {
        .nav {
            display: none;
        }

        .brand-sub {
            display: none;
        }

        .header-inner {
            padding: 0 20px;
            height: 72px;
        }

        .brand-icon {
            width: 50px;
            height: 50px;
            flex-basis: 50px;
        }
    }
</style>