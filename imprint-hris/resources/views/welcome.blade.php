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
            <h3>Employees</h3>
            <p>View and manage employee records.</p>
        </a>

        <a href="/attendance" class="dashboard-card">
            <div class="card-icon">🕒</div>
            <h3>Attendance</h3>
            <p>Track daily time records and attendance logs.</p>
        </a>

        <a href="/leave" class="dashboard-card">
            <div class="card-icon">📄</div>
            <h3>Leave Requests</h3>
            <p>Review, approve, and manage employee leaves.</p>
        </a>

        <a href="/payroll" class="dashboard-card">
            <div class="card-icon">💰</div>
            <h3>Payroll</h3>
            <p>Manage salary records and payroll reports.</p>
        </a>
    </section>

    <section class="dashboard-panel">
        <div class="panel-card">
            <h2>HRIS Overview</h2>

            <div class="stats">
                <div>
                    <strong>HR</strong>
                    <span>Employee Records</span>
                </div>

                <div>
                    <strong>DTR</strong>
                    <span>Attendance Logs</span>
                </div>

                <div>
                    <strong>PAY</strong>
                    <span>Payroll System</span>
                </div>
            </div>
        </div>

        <div class="panel-card">
            <h2>System Status</h2>

            <ul class="status-list">
                <li>
                    <span></span>
                    Employee database is active
                </li>
                <li>
                    <span></span>
                    Attendance monitoring is ready
                </li>
                <li>
                    <span></span>
                    Payroll module is available
                </li>
            </ul>
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