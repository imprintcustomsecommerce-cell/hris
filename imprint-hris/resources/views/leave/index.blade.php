@extends('layouts.app')

@section('title', 'Leave | Imprint HRIS')
@section('heading', 'Leave Requests')

@section('content')
    <div class="page-head">
        <div>
            <h1>Leave Requests</h1>
            <p>Review, approve, and manage employee leaves.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <div class="head-actions">
                <a href="/leave/create" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Leave Request
                </a>
            </div>
        @endif
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Total Requests</div><div class="stat-value">{{ $totalLeaves ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingLeaves ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Approved</div><div class="stat-value">{{ $approvedLeaves ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Rejected</div><div class="stat-value">{{ $rejectedLeaves ?? 0 }}</div></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Leave Requests</h2>
            <form method="GET" action="/leave" style="display:flex; gap:8px; flex-wrap:wrap;">
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search..." style="width:180px;">
                </div>
                <select name="status" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:11px;padding:10px 12px;font-size:14px;font-weight:600;font-family:inherit;">
                    <option value="">All statuses</option>
                    @foreach(['Pending','Approved','Rejected'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Employee</th><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($leaveRequests ?? [] as $leave)
                        @php $sc = ['Pending'=>'amber','Approved'=>'green','Rejected'=>'red'][$leave->status] ?? 'gray'; @endphp
                        <tr>
                            <td>
                                <div class="cell-user">
                                    <div class="avatar">{{ strtoupper(substr($leave->employee_name ?? 'E', 0, 1)) }}</div>
                                    <div><strong>{{ $leave->employee_name }}</strong><span>{{ $leave->department }}</span></div>
                                </div>
                            </td>
                            <td>{{ $leave->leave_type }}</td>
                            <td>{{ date('M d', strtotime($leave->start_date)) }} – {{ date('M d, Y', strtotime($leave->end_date)) }}</td>
                            <td>{{ $leave->total_days }}</td>
                            <td><span class="badge {{ $sc }}">{{ $leave->status }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="/leave/{{ $leave->id }}" class="btn btn-ghost btn-sm">View</a>
                                    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                        @if($leave->status === 'Pending')
                                            <form action="/leave/{{ $leave->id }}/approve" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-success btn-sm">Approve</button></form>
                                            <form action="/leave/{{ $leave->id }}/reject" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-danger btn-sm">Reject</button></form>
                                        @endif
                                        <form action="/leave/{{ $leave->id }}" method="POST" onsubmit="return confirm('Delete this leave request?')">@csrf @method('DELETE')<button type="submit" class="btn btn-ghost btn-sm">Delete</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5.586a1 1 0 0 1 .707.293l5.414 5.414a1 1 0 0 1 .293.707V19a2 2 0 0 1-2 2z"/></svg></div>
                                <h3>No leave requests yet</h3>
                                <p>Leave requests will appear here.</p>
                                @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                    <a href="/leave/create" class="btn btn-primary">Add Leave Request</a>
                                @endif
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['p' => $leaveRequests])
    </div>
@endsection
