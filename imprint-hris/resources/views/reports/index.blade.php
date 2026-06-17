@extends('layouts.app')

@section('title', 'Reports | Imprint HRIS')
@section('heading', 'HR Reports')

@section('content')
    <div class="page-head">
        <div>
            <h1>HR Reports</h1>
            <p>Overview of workforce, attendance, leave, and payroll.</p>
        </div>
        <div class="head-actions">
            <a href="/reports/export/employees" class="btn btn-ghost">⬇ Employees CSV</a>
            <a href="/reports/export/payroll" class="btn btn-primary">⬇ Payroll CSV</a>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Total Employees</div><div class="stat-value">{{ $totalEmployees }}</div></div>
        <div class="stat"><div class="stat-label">Departments</div><div class="stat-value">{{ count($headcountByDept) }}</div></div>
        <div class="stat"><div class="stat-label">Net Pay (Paid)</div><div class="stat-value">₱{{ number_format($totalPaid, 0) }}</div></div>
        <div class="stat"><div class="stat-label">Generated</div><div class="stat-value" style="font-size:18px;">{{ now()->format('M d, Y') }}</div></div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Headcount by Department</h2></div>
            <div class="card-body" style="padding-bottom:20px;">
                @php $maxHead = max(1, optional($headcountByDept->first())->total ?? 1); @endphp
                @forelse($headcountByDept as $row)
                    <div style="margin:12px 0;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:5px;"><span>{{ $row->department ?? 'Unassigned' }}</span><span>{{ $row->total }}</span></div>
                        <div style="height:8px;background:var(--accent-soft);border-radius:999px;overflow:hidden;"><div style="height:100%;width:{{ round($row->total / $maxHead * 100) }}%;background:var(--accent);border-radius:999px;"></div></div>
                    </div>
                @empty
                    <p class="muted">No data.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Attendance Summary</h2></div>
            <div class="card-body">
                @forelse($attendanceSummary as $row)
                    <div class="info-row"><span class="k">{{ $row->status }}</span><span class="v">{{ $row->total }}</span></div>
                @empty
                    <p class="muted">No data.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Leave Summary</h2></div>
            <div class="card-body">
                @forelse($leaveSummary as $row)
                    <div class="info-row"><span class="k">{{ $row->status }}</span><span class="v">{{ $row->total }}</span></div>
                @empty
                    <p class="muted">No data.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Payroll by Period</h2></div>
            <div class="card-body">
                @forelse($payrollByMonth as $row)
                    <div class="info-row"><span class="k">{{ $row->payroll_month }} {{ $row->payroll_year }} ({{ $row->records }})</span><span class="v">₱{{ number_format($row->total_net, 2) }}</span></div>
                @empty
                    <p class="muted">No data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head"><h2>13th Month Pay Estimate ({{ $reportYear }})</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Employee</th><th>Total Basic Earned</th><th>13th Month (÷12)</th></tr></thead>
                <tbody>
                    @forelse($thirteenthMonth as $row)
                        <tr>
                            <td>{{ $row->employee_name }}</td>
                            <td>₱{{ number_format($row->total_basic, 2) }}</td>
                            <td><strong style="color:var(--accent);">₱{{ number_format($row->thirteenth, 2) }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="3"><p class="muted">No payroll data for {{ $reportYear }} yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
