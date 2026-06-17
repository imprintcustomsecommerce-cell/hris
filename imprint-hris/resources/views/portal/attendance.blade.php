@extends('layouts.app')

@section('title', 'My Attendance | Imprint HRIS')
@section('heading', 'My Attendance')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Attendance</h1>
            <p>Your personal daily time records.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Attendance History</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th><th>Late</th><th>OT</th><th>Remarks</th></tr></thead>
                <tbody>
                    @forelse($records as $a)
                        @php $sc = ['Present'=>'green','Late'=>'amber','Absent'=>'red','On Leave'=>'blue','Half Day'=>'gray'][$a->status] ?? 'gray'; @endphp
                        <tr>
                            <td>{{ date('M d, Y', strtotime($a->attendance_date)) }}</td>
                            <td>{{ $a->time_in ? date('h:i A', strtotime($a->time_in)) : '—' }}</td>
                            <td>{{ $a->time_out ? date('h:i A', strtotime($a->time_out)) : '—' }}</td>
                            <td><span class="badge {{ $sc }}">{{ $a->status }}</span></td>
                            <td>{{ $a->late_minutes ? $a->late_minutes.'m' : '—' }}</td>
                            <td>{{ $a->overtime_minutes ? $a->overtime_minutes.'m' : '—' }}</td>
                            <td>{{ $a->remarks ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                                <h3>No attendance records</h3>
                                <p>Your time records will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
