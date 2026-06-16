@extends('layouts.app')

@section('title', 'Leave Request | Imprint HRIS')
@section('heading', 'Leave Request')

@section('content')
    @php $sc = ['Pending'=>'amber','Approved'=>'green','Rejected'=>'red'][$leave->status] ?? 'gray'; @endphp

    <div class="page-head">
        <div>
            <h1>{{ $leave->leave_type }}</h1>
            <p>{{ $leave->employee_name }} · {{ $leave->department }}</p>
        </div>
        <div class="head-actions"><a href="/leave" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Request Details</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Employee</span><span class="v">{{ $leave->employee_name }}</span></div>
                <div class="info-row"><span class="k">Employee ID</span><span class="v">{{ $leave->employee_code }}</span></div>
                <div class="info-row"><span class="k">Department</span><span class="v">{{ $leave->department }}</span></div>
                <div class="info-row"><span class="k">Leave Type</span><span class="v">{{ $leave->leave_type }}</span></div>
                <div class="info-row"><span class="k">Status</span><span class="v"><span class="badge {{ $sc }}">{{ $leave->status }}</span></span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Duration</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Start Date</span><span class="v">{{ date('M d, Y', strtotime($leave->start_date)) }}</span></div>
                <div class="info-row"><span class="k">End Date</span><span class="v">{{ date('M d, Y', strtotime($leave->end_date)) }}</span></div>
                <div class="info-row"><span class="k">Total Days</span><span class="v">{{ $leave->total_days }}</span></div>
                <div class="info-row"><span class="k">Remarks</span><span class="v">{{ $leave->remarks ?? '—' }}</span></div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head"><h2>Reason</h2></div>
        <div class="card-body" style="padding-bottom:24px;">
            <p style="margin:8px 0 0; color:#334155; line-height:1.7;">{{ $leave->reason ?? 'No reason provided.' }}</p>

            @if(in_array(auth()->user()->role, ['Admin', 'HR']) && $leave->status === 'Pending')
                <div class="actions" style="margin-top:22px;">
                    <form action="/leave/{{ $leave->id }}/approve" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-success">Approve Request</button></form>
                    <form action="/leave/{{ $leave->id }}/reject" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-danger">Reject Request</button></form>
                </div>
            @endif
        </div>
    </div>
@endsection
