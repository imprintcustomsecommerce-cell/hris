@extends('layouts.app')

@section('title', 'Reports | Imprint HRIS')
@section('heading', 'HR Reports')

@section('content')
    <div class="page-head">
        <div>
            <h1>HR Reports</h1>
            <p>Overview of workforce, attendance, leave, and payroll.</p>
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
            <div class="card-body">
                @forelse($headcountByDept as $row)
                    <div class="info-row"><span class="k">{{ $row->department ?? 'Unassigned' }}</span><span class="v">{{ $row->total }}</span></div>
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
@endsection
