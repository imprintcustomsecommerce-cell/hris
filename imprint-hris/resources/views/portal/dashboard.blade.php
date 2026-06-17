@extends('layouts.app')

@section('title', 'My Dashboard | Imprint HRIS')
@section('heading', 'My Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>Hi, {{ explode(' ', auth()->user()->name)[0] }} 👋</h1>
            <p>Welcome to your self-service portal.</p>
        </div>
    </div>

    @unless($me)
        <div class="card"><div class="card-body" style="padding:28px;">
            <p class="muted">Your account isn't linked to an employee record yet. Please contact HR to get set up.</p>
        </div></div>
    @else
        <div class="stat-grid">
            <a href="/portal/profile" class="stat" style="text-decoration:none;color:inherit;">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 7a4 4 0 1 1-8 0 4 4 0 0 1 8 0zM12 14a7 7 0 0 0-7 7h14a7 7 0 0 0-7-7z"/></svg></div>
                <div class="stat-label">Employee ID</div>
                <div class="stat-value" style="font-size:20px;">{{ $me->employee_id }}</div>
                <div class="stat-sub">{{ $me->position }}</div>
            </a>
            <a href="/portal/attendance" class="stat" style="text-decoration:none;color:inherit;">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                <div class="stat-label">Department</div>
                <div class="stat-value" style="font-size:20px;">{{ $me->department }}</div>
                <div class="stat-sub">{{ $me->employment_type ?? '—' }}</div>
            </a>
            <a href="/portal/leave" class="stat" style="text-decoration:none;color:inherit;">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg></div>
                <div class="stat-label">Pending Leave</div>
                <div class="stat-value">{{ $pendingLeaves }}</div>
                <div class="stat-sub">{{ $leaveCount }} total requests</div>
            </a>
            <a href="/portal/payslips" class="stat" style="text-decoration:none;color:inherit;">
                <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                <div class="stat-label">Latest Net Pay</div>
                <div class="stat-value">₱{{ number_format($lastPayslip->net_pay ?? 0, 0) }}</div>
                <div class="stat-sub">{{ $payslipCount }} payslips</div>
            </a>
        </div>

        <div class="grid-2 lean" style="margin-bottom:18px;">
            <div class="card">
                <div class="card-head"><div><h2>Time Clock</h2><p>{{ now()->format('l, F d, Y') }}</p></div></div>
                <div class="card-body" style="padding:22px 24px 24px;">
                    <div style="display:flex; gap:20px; flex-wrap:wrap; margin-bottom:18px;">
                        <div><div style="color:var(--muted);font-size:12px;font-weight:700;">Time In</div><div style="font-size:20px;font-weight:800;">{{ $today && $today->time_in ? date('h:i A', strtotime($today->time_in)) : '—' }}</div></div>
                        <div><div style="color:var(--muted);font-size:12px;font-weight:700;">Time Out</div><div style="font-size:20px;font-weight:800;">{{ $today && $today->time_out ? date('h:i A', strtotime($today->time_out)) : '—' }}</div></div>
                        <div><div style="color:var(--muted);font-size:12px;font-weight:700;">Status</div><div style="font-size:20px;font-weight:800;">{{ $today->status ?? 'Not clocked in' }}</div></div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <form action="/portal/clock-in" method="POST" style="flex:1;">@csrf<button class="btn btn-primary" style="width:100%;" {{ $today && $today->time_in ? 'disabled' : '' }}>Clock In</button></form>
                        <form action="/portal/clock-out" method="POST" style="flex:1;">@csrf<button class="btn btn-ghost" style="width:100%;" {{ !($today && $today->time_in) || ($today && $today->time_out) ? 'disabled' : '' }}>Clock Out</button></form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><div><h2>Leave Balances</h2><p>{{ now()->format('Y') }}</p></div></div>
                <div class="card-body">
                    @foreach($balances as $b)
                        <div class="info-row"><span class="k">{{ $b['type'] }}</span><span class="v">{{ $b['remaining'] }} / {{ $b['allotted'] }} days</span></div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid-2 lean">
            <div class="card">
                <div class="card-head">
                    <div><h2>Recent Attendance</h2><p>Your latest time records</p></div>
                    <a href="/portal/attendance" class="btn btn-ghost btn-sm">View all</a>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead><tr><th>Date</th><th>In</th><th>Out</th><th>Status</th></tr></thead>
                        <tbody>
                            @forelse($recentAttendance as $a)
                                @php $sc = ['Present'=>'green','Late'=>'amber','Absent'=>'red','On Leave'=>'blue','Half Day'=>'gray'][$a->status] ?? 'gray'; @endphp
                                <tr>
                                    <td>{{ date('M d, Y', strtotime($a->attendance_date)) }}</td>
                                    <td>{{ $a->time_in ? date('h:i A', strtotime($a->time_in)) : '—' }}</td>
                                    <td>{{ $a->time_out ? date('h:i A', strtotime($a->time_out)) : '—' }}</td>
                                    <td><span class="badge {{ $sc }}">{{ $a->status }}</span></td>
                                </tr>
                            @empty
                                <tr><td colspan="4"><p class="muted">No attendance records.</p></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><div><h2>Quick Actions</h2></div></div>
                <div class="card-body" style="padding:20px 24px 24px; display:grid; gap:10px;">
                    <a href="/portal/tasks" class="btn btn-primary" style="width:100%;">My Tasks @if($openTasks > 0)({{ $openTasks }} open)@endif</a>
                    <a href="/portal/leave" class="btn btn-ghost" style="width:100%;">Request Leave</a>
                    <a href="/portal/payslips" class="btn btn-ghost" style="width:100%;">View Payslips</a>
                    <a href="/portal/profile" class="btn btn-ghost" style="width:100%;">My Profile</a>
                </div>
            </div>
        </div>
    @endunless
@endsection
