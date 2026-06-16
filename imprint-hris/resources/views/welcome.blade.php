<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Imprint HRIS Dashboard</title>
</head>

<body>

<x-header />

<main class="dashboard">
    <section class="dashboard-header">
        <div>
            <span class="badge">Imprint Customs HR System</span>

            <h1>Welcome back, {{ auth()->user()?->name ?? 'Imprint Team' }}</h1>

            <p>
                Here is your HRIS dashboard. Manage employees, attendance,
                leave requests, payroll, and HR reports in one organized system.
            </p>
        </div>

        <div class="dashboard-date">
            <span>Today</span>
            <strong>{{ now()->format('F d, Y') }}</strong>
        </div>
    </section>

    <section class="dashboard-grid">
        <a href="/employees" class="dashboard-card">
            <div class="card-icon">👥</div>
            <h3>{{ $stats['totalEmployees'] ?? 0 }}</h3>
            <p>Total employees · {{ $stats['activeEmployees'] ?? 0 }} active</p>
        </a>

        <a href="/attendance" class="dashboard-card">
            <div class="card-icon">🕒</div>
            <h3>{{ $stats['presentToday'] ?? 0 }}</h3>
            <p>Present today · {{ $stats['lateToday'] ?? 0 }} late</p>
        </a>

        <a href="/leave" class="dashboard-card">
            <div class="card-icon">📄</div>
            <h3>{{ $stats['pendingLeaves'] ?? 0 }}</h3>
            <p>Pending leave requests</p>
        </a>

        <a href="/payroll" class="dashboard-card">
            <div class="card-icon">💰</div>
            <h3>₱{{ number_format($stats['totalNetPay'] ?? 0, 2) }}</h3>
            <p>Total net pay disbursed</p>
        </a>
    </section>

    <section class="dashboard-panel">
        <div class="panel-card">
            <h2>Pending Leave Requests</h2>

            @forelse($pendingLeaveList ?? [] as $leave)
                <a href="/leave/{{ $leave->id }}" class="list-row">
                    <div>
                        <strong>{{ $leave->employee_name }}</strong>
                        <span>{{ $leave->leave_type }} · {{ $leave->total_days }} day(s)</span>
                    </div>
                    <span class="pill">{{ date('M d', strtotime($leave->start_date)) }}</span>
                </a>
            @empty
                <p class="muted">No pending leave requests. 🎉</p>
            @endforelse
        </div>

        <div class="panel-card">
            <h2>Recent Employees</h2>

            @forelse($recentEmployees ?? [] as $employee)
                <a href="/employees/{{ $employee->id }}" class="list-row">
                    <div>
                        <strong>{{ $employee->name }}</strong>
                        <span>{{ $employee->position }} · {{ $employee->department }}</span>
                    </div>
                    <span class="pill">{{ $employee->employee_id }}</span>
                </a>
            @empty
                <p class="muted">No employees yet.</p>
            @endforelse
        </div>
    </section>
</main>

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

    .dashboard {
        min-height: calc(100vh - 78px);
        background:
            radial-gradient(circle at top left, rgba(17, 24, 39, 0.08), transparent 35%),
            #f6f7fb;
        padding: 50px 32px;
    }

    .dashboard-header {
        max-width: 1280px;
        margin: 0 auto 34px;
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 30px;
    }

    .badge {
        display: inline-block;
        background: #111827;
        color: white;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .dashboard-header h1 {
        font-size: clamp(34px, 5vw, 56px);
        line-height: 1;
        margin: 0 0 16px;
        letter-spacing: -0.04em;
        color: #111827;
    }

    .dashboard-header p {
        font-size: 17px;
        line-height: 1.7;
        color: #6b7280;
        max-width: 700px;
        margin: 0;
    }

    .dashboard-date {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 22px;
        padding: 20px 24px;
        min-width: 220px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .08);
    }

    .dashboard-date span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .dashboard-date strong {
        font-size: 18px;
        color: #111827;
    }

    .dashboard-grid {
        max-width: 1280px;
        margin: 0 auto;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
    }

    .dashboard-card {
        background: rgba(255,255,255,.9);
        border: 1px solid #e5e7eb;
        border-radius: 28px;
        padding: 28px;
        text-decoration: none;
        color: #111827;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .08);
        transition: .2s ease;
    }

    .dashboard-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 30px 70px rgba(17, 24, 39, .12);
    }

    .card-icon {
        width: 54px;
        height: 54px;
        background: #f3f4f6;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 25px;
        margin-bottom: 22px;
    }

    .dashboard-card h3 {
        font-size: 22px;
        margin: 0 0 10px;
    }

    .dashboard-card p {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: #6b7280;
    }

    .dashboard-panel {
        max-width: 1280px;
        margin: 24px auto 0;
        display: grid;
        grid-template-columns: 1.2fr .8fr;
        gap: 20px;
    }

    .panel-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 28px;
        padding: 30px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .08);
    }

    .panel-card h2 {
        margin: 0 0 24px;
        font-size: 24px;
        color: #111827;
    }

    .list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 14px 0;
        border-bottom: 1px solid #f3f4f6;
        text-decoration: none;
        color: #111827;
    }

    .list-row:last-child { border-bottom: none; }

    .list-row strong {
        display: block;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .list-row span {
        font-size: 13px;
        color: #6b7280;
        font-weight: 500;
    }

    .pill {
        background: #f3f4f6;
        color: #374151;
        padding: 6px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .muted {
        color: #6b7280;
        font-size: 14px;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
    }

    .stats div {
        background: #f3f4f6;
        border-radius: 20px;
        padding: 20px 14px;
    }

    .stats strong {
        display: block;
        font-size: 22px;
        color: #111827;
        margin-bottom: 6px;
    }

    .stats span {
        font-size: 13px;
        color: #6b7280;
    }

    .status-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: grid;
        gap: 16px;
    }

    .status-list li {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #4b5563;
        font-size: 15px;
        font-weight: 600;
    }

    .status-list span {
        width: 10px;
        height: 10px;
        background: #22c55e;
        border-radius: 999px;
        flex-shrink: 0;
    }

    @media (max-width: 1000px) {
        .dashboard-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .dashboard-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .dashboard-panel {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 600px) {
        .dashboard {
            padding: 36px 20px;
        }

        .dashboard-grid {
            grid-template-columns: 1fr;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        .dashboard-date {
            width: 100%;
        }
    }
</style>

</body>
</html>