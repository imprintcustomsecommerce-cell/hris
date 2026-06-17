@extends('layouts.app')

@section('title', 'Dashboard | Imprint HRIS')
@section('heading', 'Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>Welcome back, {{ explode(' ', auth()->user()->name ?? 'Team')[0] }} 👋</h1>
            <p>Here's what's happening across Imprint Customs today.</p>
        </div>
    </div>

    <div class="stat-grid">
        <a href="/employees" class="stat" style="text-decoration:none; color:inherit;">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/></svg></div>
            <div class="stat-label">Total Employees</div>
            <div class="stat-value">{{ $stats['totalEmployees'] ?? 0 }}</div>
            <div class="stat-sub">{{ $stats['activeEmployees'] ?? 0 }} active · {{ $stats['onLeaveEmployees'] ?? 0 }} on leave</div>
        </a>

        <a href="/attendance" class="stat" style="text-decoration:none; color:inherit;">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
            <div class="stat-label">Present Today</div>
            <div class="stat-value">{{ $stats['presentToday'] ?? 0 }}</div>
            <div class="stat-sub">{{ $stats['lateToday'] ?? 0 }} late arrivals</div>
        </a>

        <a href="/leave" class="stat" style="text-decoration:none; color:inherit;">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg></div>
            <div class="stat-label">Pending Leave</div>
            <div class="stat-value">{{ $stats['pendingLeaves'] ?? 0 }}</div>
            <div class="stat-sub">awaiting approval</div>
        </a>

        <a href="/payroll" class="stat" style="text-decoration:none; color:inherit;">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
            <div class="stat-label">Net Pay Disbursed</div>
            <div class="stat-value">₱{{ number_format($stats['totalNetPay'] ?? 0, 0) }}</div>
            <div class="stat-sub">{{ $stats['pendingPayrolls'] ?? 0 }} payrolls pending</div>
        </a>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head">
                <div><h2>Pending Leave Requests</h2><p>Requests waiting for your decision</p></div>
                <a href="/leave" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="card-body">
                @forelse($pendingLeaveList ?? [] as $leave)
                    <a href="/leave/{{ $leave->id }}" class="list-row">
                        <div>
                            <span class="lr-title">{{ $leave->employee_name }}</span>
                            <span class="lr-sub">{{ $leave->leave_type }} · {{ $leave->total_days }} day(s)</span>
                        </div>
                        <span class="badge amber">{{ date('M d', strtotime($leave->start_date)) }}</span>
                    </a>
                @empty
                    <p class="muted">No pending leave requests. 🎉</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head">
                <div><h2>Recent Employees</h2><p>Newest hires</p></div>
                <a href="/employees" class="btn btn-ghost btn-sm">View all</a>
            </div>
            <div class="card-body">
                @forelse($recentEmployees ?? [] as $employee)
                    <a href="/employees/{{ $employee->id }}" class="list-row">
                        <div>
                            <span class="lr-title">{{ $employee->name }}</span>
                            <span class="lr-sub">{{ $employee->position }} · {{ $employee->department }}</span>
                        </div>
                        <span class="badge gray plain">{{ $employee->employee_id }}</span>
                    </a>
                @empty
                    <p class="muted">No employees yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="grid-2 lean" style="margin-top:18px;">
        <div class="card">
            <div class="card-head"><div><h2>Headcount by Department</h2></div><a href="/reports" class="btn btn-ghost btn-sm">Reports</a></div>
            <div class="card-body" style="padding-bottom:20px;">
                @php $maxHead = max(1, optional(($headcountByDept ?? collect())->first())->total ?? 1); @endphp
                @forelse($headcountByDept ?? [] as $row)
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
            <div class="card-head"><div><h2>Upcoming Holidays</h2></div><a href="/holidays" class="btn btn-ghost btn-sm">All</a></div>
            <div class="card-body">
                @forelse($upcomingHolidays ?? [] as $h)
                    <div class="list-row">
                        <div>
                            <span class="lr-title">{{ $h->name }}</span>
                            <span class="lr-sub">{{ date('l, M d, Y', strtotime($h->date)) }}</span>
                        </div>
                        <span class="badge {{ $h->type === 'Special' ? 'blue' : 'green' }}">{{ $h->type }}</span>
                    </div>
                @empty
                    <p class="muted">No upcoming holidays.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head"><div><h2>Announcements</h2></div><a href="/announcements" class="btn btn-ghost btn-sm">Manage</a></div>
        <div class="card-body" style="padding:8px 24px 16px;">
            @forelse($announcements ?? [] as $a)
                <div style="padding:14px 0; border-bottom:1px solid var(--border);">
                    <strong style="font-size:15px;">{{ $a->title }}</strong>
                    <p style="margin:5px 0 0; color:var(--muted); font-size:14px; line-height:1.6;">{{ \Illuminate\Support\Str::limit($a->body, 160) }}</p>
                    <span style="display:block; margin-top:6px; color:var(--muted); font-size:12px;">{{ $a->author_name ?? 'HR' }} · {{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
                </div>
            @empty
                <p class="muted">No announcements yet.</p>
            @endforelse
        </div>
    </div>
@endsection
