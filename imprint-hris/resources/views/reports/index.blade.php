<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    <section class="page-header">
        <div>
            <span class="badge">Analytics</span>
            <h1>HR Reports</h1>
            <p>Overview of workforce, attendance, leave, and payroll data.</p>
        </div>
    </section>

    <section class="summary-grid">
        <div class="summary-card"><span>Total Employees</span><strong>{{ $totalEmployees }}</strong></div>
        <div class="summary-card"><span>Departments</span><strong>{{ count($headcountByDept) }}</strong></div>
        <div class="summary-card"><span>Total Net Pay (Paid)</span><strong>₱{{ number_format($totalPaid, 0) }}</strong></div>
        <div class="summary-card"><span>Generated</span><strong style="font-size:18px;">{{ now()->format('M d, Y') }}</strong></div>
    </section>

    <section class="detail-grid">
        <div class="detail-card">
            <h2>Headcount by Department</h2>
            @forelse($headcountByDept as $row)
                <div class="detail-row"><span>{{ $row->department ?? 'Unassigned' }}</span><strong>{{ $row->total }}</strong></div>
            @empty
                <p style="color:#6b7280;">No data.</p>
            @endforelse
        </div>

        <div class="detail-card">
            <h2>Attendance Summary</h2>
            @forelse($attendanceSummary as $row)
                <div class="detail-row"><span>{{ $row->status }}</span><strong>{{ $row->total }}</strong></div>
            @empty
                <p style="color:#6b7280;">No data.</p>
            @endforelse
        </div>

        <div class="detail-card">
            <h2>Leave Summary</h2>
            @forelse($leaveSummary as $row)
                <div class="detail-row"><span>{{ $row->status }}</span><strong>{{ $row->total }}</strong></div>
            @empty
                <p style="color:#6b7280;">No data.</p>
            @endforelse
        </div>

        <div class="detail-card">
            <h2>Payroll by Period</h2>
            @forelse($payrollByMonth as $row)
                <div class="detail-row"><span>{{ $row->payroll_month }} {{ $row->payroll_year }} ({{ $row->records }})</span><strong>₱{{ number_format($row->total_net, 2) }}</strong></div>
            @empty
                <p style="color:#6b7280;">No data.</p>
            @endforelse
        </div>
    </section>
</main>

@include('components.page-style')

</body>
</html>
