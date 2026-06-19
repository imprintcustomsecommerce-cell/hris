@extends('layouts.app')

@section('title', 'My Leave | Imprint HRIS')
@section('heading', 'My Leave')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Leave</h1>
            <p>Request time off and track your leave requests.</p>
        </div>
    </div>

    @if($me)
        <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);">
            @foreach($balances as $b)
                <div class="stat">
                    <div class="stat-label">{{ $b['type'] }} Remaining</div>
                    <div class="stat-value">{{ $b['remaining'] }} <span style="font-size:15px;color:var(--muted);">/ {{ $b['allotted'] }} days</span></div>
                    <div class="stat-sub">{{ $b['used'] }} used this year</div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>My Requests</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Type</th><th>Dates</th><th>Days</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($leaves as $leave)
                            @php $sc = ['Pending'=>'amber','Approved'=>'green','Rejected'=>'red','Cancelled'=>'gray'][$leave->status] ?? 'gray'; @endphp
                            <tr>
                                <td>{{ $leave->leave_type }}</td>
                                <td>{{ date('M d', strtotime($leave->start_date)) }} – {{ date('M d, Y', strtotime($leave->end_date)) }}</td>
                                <td>{{ $leave->total_days }}</td>
                                <td><span class="badge {{ $sc }}">{{ $leave->status }}</span></td>
                                <td>
                                    @if($leave->status === 'Pending')
                                        <form action="/portal/leave/{{ $leave->id }}/cancel" method="POST" onsubmit="return confirm('Cancel this leave request?')" style="margin:0;">
                                            @csrf @method('PATCH')
                                            <button type="submit" class="btn btn-danger btn-sm">Cancel</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><p class="muted">No leave requests yet.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Request Leave</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                @if($me)
                    <form action="/portal/leave" method="POST" style="display:grid; gap:16px;">
                        @csrf
                        <div class="field">
                            <label>Leave Type</label>
                            <select name="leave_type" required>
                                <option value="">Select type</option>
                                @foreach(['Vacation Leave','Sick Leave','Emergency Leave','Maternity Leave','Paternity Leave','Unpaid Leave'] as $lt)
                                    <option value="{{ $lt }}" {{ old('leave_type') === $lt ? 'selected' : '' }}>{{ $lt }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label>Start Date</label><input type="date" name="start_date" value="{{ old('start_date') }}" required></div>
                        <div class="field"><label>End Date</label><input type="date" name="end_date" value="{{ old('end_date') }}" required></div>
                        <div class="field"><label>Reason</label><textarea name="reason" rows="3" placeholder="Reason for leave">{{ old('reason') }}</textarea></div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">Submit Request</button>
                    </form>
                @else
                    <p class="muted">Your account isn't linked to an employee record. Please contact HR.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
