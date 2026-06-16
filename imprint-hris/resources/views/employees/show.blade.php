<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $employee->name }} | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    <section class="page-header">
        <div>
            <span class="badge">Employee Profile</span>
            <h1>{{ $employee->name }}</h1>
            <p>{{ $employee->position }} · {{ $employee->department }}</p>
        </div>
        <div style="display:flex; gap:10px;">
            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                <a href="/employees/{{ $employee->id }}/edit" class="add-btn">Edit</a>
            @endif
            <a href="/employees" class="back-btn">← Back</a>
        </div>
    </section>

    <section class="detail-grid">
        <div class="detail-card">
            <h2>Personal Information</h2>
            <div class="detail-row"><span>Full Name</span><strong>{{ $employee->name }}</strong></div>
            <div class="detail-row"><span>Email</span><strong>{{ $employee->email ?? '—' }}</strong></div>
            <div class="detail-row"><span>Contact</span><strong>{{ $employee->contact_number ?? '—' }}</strong></div>
            <div class="detail-row"><span>Birthdate</span><strong>{{ $employee->birthdate ? date('M d, Y', strtotime($employee->birthdate)) : '—' }}</strong></div>
            <div class="detail-row"><span>Address</span><strong>{{ $employee->address ?? '—' }}</strong></div>
        </div>

        <div class="detail-card">
            <h2>Employment Details</h2>
            <div class="detail-row"><span>Employee ID</span><strong>{{ $employee->employee_id }}</strong></div>
            <div class="detail-row"><span>Department</span><strong>{{ $employee->department }}</strong></div>
            <div class="detail-row"><span>Position</span><strong>{{ $employee->position }}</strong></div>
            <div class="detail-row"><span>Date Hired</span><strong>{{ date('M d, Y', strtotime($employee->date_hired)) }}</strong></div>
            <div class="detail-row"><span>Employment Type</span><strong>{{ $employee->employment_type ?? '—' }}</strong></div>
            <div class="detail-row"><span>Status</span><strong>{{ $employee->status }}</strong></div>
        </div>

        <div class="detail-card full">
            <h2>Recent Attendance</h2>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($attendance as $a)
                            <tr>
                                <td>{{ date('M d, Y', strtotime($a->attendance_date)) }}</td>
                                <td>{{ $a->time_in ?? '—' }}</td>
                                <td>{{ $a->time_out ?? '—' }}</td>
                                <td>{{ $a->status }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4">No attendance records.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="detail-card">
            <h2>Recent Leave</h2>
            @forelse($leaves as $l)
                <div class="detail-row"><span>{{ $l->leave_type }} ({{ $l->total_days }}d)</span><strong>{{ $l->status }}</strong></div>
            @empty
                <p class="muted" style="color:#6b7280;">No leave records.</p>
            @endforelse
        </div>

        <div class="detail-card">
            <h2>Recent Payroll</h2>
            @forelse($payrolls as $p)
                <div class="detail-row"><span>{{ $p->payroll_month }} {{ $p->payroll_year }}</span><strong>₱{{ number_format($p->net_pay, 2) }}</strong></div>
            @empty
                <p class="muted" style="color:#6b7280;">No payroll records.</p>
            @endforelse
        </div>
    </section>
</main>

@include('components.page-style')

</body>
</html>
