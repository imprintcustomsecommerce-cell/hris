<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Request | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    @php
        $statusClass = ['Pending' => 'amber', 'Approved' => 'green', 'Rejected' => 'red'][$leave->status] ?? 'gray';
    @endphp

    <section class="page-header">
        <div>
            <span class="badge">Leave Request</span>
            <h1>{{ $leave->leave_type }}</h1>
            <p>{{ $leave->employee_name }} · {{ $leave->department }}</p>
        </div>
        <a href="/leave" class="back-btn">← Back to Leave</a>
    </section>

    <section class="detail-grid">
        <div class="detail-card">
            <h2>Request Details</h2>
            <div class="detail-row"><span>Employee</span><strong>{{ $leave->employee_name }}</strong></div>
            <div class="detail-row"><span>Employee ID</span><strong>{{ $leave->employee_code }}</strong></div>
            <div class="detail-row"><span>Department</span><strong>{{ $leave->department }}</strong></div>
            <div class="detail-row"><span>Leave Type</span><strong>{{ $leave->leave_type }}</strong></div>
            <div class="detail-row"><span>Status</span><strong><span class="status {{ $statusClass }}">{{ $leave->status }}</span></strong></div>
        </div>

        <div class="detail-card">
            <h2>Duration</h2>
            <div class="detail-row"><span>Start Date</span><strong>{{ date('M d, Y', strtotime($leave->start_date)) }}</strong></div>
            <div class="detail-row"><span>End Date</span><strong>{{ date('M d, Y', strtotime($leave->end_date)) }}</strong></div>
            <div class="detail-row"><span>Total Days</span><strong>{{ $leave->total_days }}</strong></div>
            <div class="detail-row"><span>Remarks</span><strong>{{ $leave->remarks ?? '—' }}</strong></div>
        </div>

        <div class="detail-card full">
            <h2>Reason</h2>
            <p style="margin:0; color:#374151; line-height:1.7;">{{ $leave->reason ?? 'No reason provided.' }}</p>

            @if(in_array(auth()->user()->role, ['Admin', 'HR']) && $leave->status === 'Pending')
                <div class="form-actions" style="justify-content:flex-start;">
                    <form action="/leave/{{ $leave->id }}/approve" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="save-btn">Approve</button>
                    </form>
                    <form action="/leave/{{ $leave->id }}/reject" method="POST">
                        @csrf @method('PATCH')
                        <button type="submit" class="cancel-btn">Reject</button>
                    </form>
                </div>
            @endif
        </div>
    </section>
</main>

@include('components.page-style')

</body>
</html>
