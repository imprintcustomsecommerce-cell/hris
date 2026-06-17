@extends('layouts.app')

@section('title', 'Attendance | Imprint HRIS')
@section('heading', 'Attendance')

@section('content')
    <div class="page-head">
        <div>
            <h1>Attendance</h1>
            <p>Track daily time records and attendance logs.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <div class="head-actions">
                <a href="/attendance/create" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Attendance
                </a>
            </div>
        @endif
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Records Today</div><div class="stat-value">{{ $totalToday ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Present</div><div class="stat-value">{{ $presentToday ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Late</div><div class="stat-value">{{ $lateToday ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Absent</div><div class="stat-value">{{ $absentToday ?? 0 }}</div></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Attendance Log</h2>
            <form method="GET" action="/attendance" style="display:flex; gap:8px; flex-wrap:wrap;">
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search employee..." style="width:200px;">
                </div>
                <select name="status" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:11px;padding:10px 12px;font-size:14px;font-weight:600;font-family:inherit;">
                    <option value="">All statuses</option>
                    @foreach(['Present','Late','Absent','Half Day','On Leave'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th><th>ID</th><th>Department</th><th>Date</th><th>In</th><th>Out</th><th>Status</th><th>Remarks</th>
                        @if(in_array(auth()->user()->role, ['Admin', 'HR']))<th></th>@endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendanceRecords ?? [] as $attendance)
                        @php $sc = ['Present'=>'green','Late'=>'amber','Absent'=>'red','On Leave'=>'blue','Half Day'=>'gray'][$attendance->status] ?? 'gray'; @endphp
                        <tr>
                            <td>
                                <div class="cell-user">
                                    <div class="avatar">{{ strtoupper(substr($attendance->employee_name ?? 'E', 0, 1)) }}</div>
                                    <div><strong>{{ $attendance->employee_name }}</strong><span>{{ $attendance->position ?? 'No position' }}</span></div>
                                </div>
                            </td>
                            <td>{{ $attendance->employee_code }}</td>
                            <td>{{ $attendance->department }}</td>
                            <td>{{ date('M d, Y', strtotime($attendance->attendance_date)) }}</td>
                            <td>{{ $attendance->time_in ? date('h:i A', strtotime($attendance->time_in)) : '—' }}</td>
                            <td>{{ $attendance->time_out ? date('h:i A', strtotime($attendance->time_out)) : '—' }}</td>
                            <td><span class="badge {{ $sc }}">{{ $attendance->status }}</span></td>
                            <td>{{ $attendance->remarks ?? '—' }}</td>
                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <td>
                                    <div class="actions">
                                        <a href="/attendance/{{ $attendance->id }}/edit" class="btn btn-ghost btn-sm">Edit</a>
                                        <form action="/attendance/{{ $attendance->id }}" method="POST" onsubmit="return confirm('Delete this record?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="9">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                                <h3>No attendance records yet</h3>
                                <p>Start logging daily attendance.</p>
                                @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                    <a href="/attendance/create" class="btn btn-primary">Add Attendance</a>
                                @endif
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['p' => $attendanceRecords])
    </div>
@endsection
