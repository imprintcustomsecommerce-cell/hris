<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="attendance-page">

    <section class="page-header">
        <div>
            <span class="badge">Daily Time Record</span>
            <h1>Attendance</h1>
            <p>
                Monitor employee attendance, time in, time out, and daily status records.
            </p>
        </div>

        <a href="/attendance/create" class="add-btn">+ Add Attendance</a>
    </section>

    @if(session('success'))
        <div class="success-alert">
            {{ session('success') }}
        </div>
    @endif

    <section class="summary-grid">
        <div class="summary-card">
            <span>Total Today</span>
            <strong>{{ $totalToday ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Present</span>
            <strong>{{ $presentToday ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Late</span>
            <strong>{{ $lateToday ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Absent</span>
            <strong>{{ $absentToday ?? 0 }}</strong>
        </div>
    </section>

    <section class="attendance-panel">
        <div class="panel-top">
            <div>
                <h2>Attendance Records</h2>
                <p>View daily employee attendance logs.</p>
            </div>

            <div class="search-box">
                <input type="text" placeholder="Search attendance...">
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Date</th>
                        <th>Time In</th>
                        <th>Time Out</th>
                        <th>Status</th>
                        <th>Remarks</th>
                        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @forelse($attendanceRecords ?? [] as $attendance)
                        @php
                            $statusClass = strtolower(str_replace(' ', '-', $attendance->status));
                        @endphp

                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="avatar">
                                        {{ strtoupper(substr($attendance->employee_name ?? 'E', 0, 1)) }}
                                    </div>

                                    <div>
                                        <strong>{{ $attendance->employee_name }}</strong>
                                        <span>{{ $attendance->position ?? 'No position' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $attendance->employee_code }}</td>
                            <td>{{ $attendance->department }}</td>

                            <td>
                                {{ date('M d, Y', strtotime($attendance->attendance_date)) }}
                            </td>

                            <td>
                                {{ $attendance->time_in ? date('h:i A', strtotime($attendance->time_in)) : '—' }}
                            </td>

                            <td>
                                {{ $attendance->time_out ? date('h:i A', strtotime($attendance->time_out)) : '—' }}
                            </td>

                            <td>
                                <span class="status {{ $statusClass }}">
                                    {{ $attendance->status }}
                                </span>
                            </td>

                            <td>{{ $attendance->remarks ?? '—' }}</td>

                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <td>
                                    <div class="actions">
                                        <a href="/attendance/{{ $attendance->id }}/edit">Edit</a>
                                        <form action="/attendance/{{ $attendance->id }}" method="POST" onsubmit="return confirm('Delete this attendance record?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="delete-btn">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9">
                                <div class="empty-state">
                                    <div class="empty-icon">🕒</div>
                                    <h3>No attendance records yet</h3>
                                    <p>Start adding daily attendance records.</p>
                                    <a href="/attendance/create">Add Attendance</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
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

    .attendance-page {
        min-height: calc(100vh - 78px);
        padding: 50px 32px;
        background:
            radial-gradient(circle at top left, rgba(17, 24, 39, 0.08), transparent 35%),
            #f6f7fb;
    }

    .page-header {
        max-width: 1280px;
        margin: 0 auto 28px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
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

    .page-header h1 {
        font-size: clamp(36px, 5vw, 56px);
        margin: 0 0 12px;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 17px;
        line-height: 1.6;
    }

    .add-btn {
        background: #111827;
        color: white;
        text-decoration: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-weight: 800;
        white-space: nowrap;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .18);
    }

    .success-alert {
        max-width: 1280px;
        margin: 0 auto 20px;
        background: #dcfce7;
        color: #166534;
        border: 1px solid #bbf7d0;
        padding: 16px 20px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 800;
    }

    .summary-grid {
        max-width: 1280px;
        margin: 0 auto 24px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .07);
    }

    .summary-card span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .summary-card strong {
        font-size: 34px;
        color: #111827;
    }

    .attendance-panel {
        max-width: 1280px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 28px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .panel-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .panel-top h2 {
        margin: 0 0 6px;
        font-size: 24px;
    }

    .panel-top p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .search-box input {
        width: 280px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 13px 16px;
        border-radius: 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
    }

    .search-box input:focus {
        border-color: #111827;
        background: white;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 980px;
    }

    th {
        text-align: left;
        padding: 14px 16px;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 18px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .employee-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .avatar {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: #111827;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        flex-shrink: 0;
    }

    .employee-info strong {
        display: block;
        color: #111827;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .employee-info span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
        white-space: nowrap;
    }

    .status.present {
        background: #dcfce7;
        color: #166534;
    }

    .status.late {
        background: #fef3c7;
        color: #92400e;
    }

    .status.absent {
        background: #fee2e2;
        color: #991b1b;
    }

    .status.half-day {
        background: #dbeafe;
        color: #1e40af;
    }

    .status.on-leave {
        background: #ede9fe;
        color: #5b21b6;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        font-size: 42px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        font-size: 22px;
        color: #111827;
    }

    .empty-state p {
        margin: 0 0 22px;
        color: #6b7280;
    }

    .empty-state a {
        display: inline-block;
        text-decoration: none;
        background: #111827;
        color: white;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 800;
    }

    @media (max-width: 1000px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .panel-top {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box,
        .search-box input {
            width: 100%;
        }
    }

    @media (max-width: 600px) {
        .attendance-page {
            padding: 36px 20px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .attendance-panel {
            padding: 22px;
            border-radius: 24px;
        }

        .add-btn {
            width: 100%;
            text-align: center;
        }
    }

    .actions { display: flex; gap: 8px; align-items: center; }
    .actions a, .actions button {
        text-decoration: none;
        color: #111827;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
        border: none;
        cursor: pointer;
        font-family: inherit;
    }
    .actions a:hover { background: #111827; color: white; }
    .actions form { margin: 0; }
    .delete-btn:hover { background: #991b1b; color: white; }
</style>

</body>
</html>