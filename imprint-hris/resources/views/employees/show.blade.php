@extends('layouts.app')

@section('title', $employee->name . ' | Imprint HRIS')
@section('heading', 'Employee Profile')

@section('content')
    <div class="page-head">
        <div class="profile-hero">
            <div class="avatar">{{ strtoupper(substr($employee->name ?? 'E', 0, 1)) }}</div>
            <div>
                <h1>{{ $employee->name }}</h1>
                <p>{{ $employee->position }} · {{ $employee->department }}</p>
            </div>
        </div>
        <div class="head-actions">
            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                <a href="/employees/{{ $employee->id }}/edit" class="btn btn-primary">Edit</a>
            @endif
            <a href="/employees" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Personal Information</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Full Name</span><span class="v">{{ $employee->name }}</span></div>
                <div class="info-row"><span class="k">Email</span><span class="v">{{ $employee->email ?? '—' }}</span></div>
                <div class="info-row"><span class="k">Contact</span><span class="v">{{ $employee->contact_number ?? '—' }}</span></div>
                <div class="info-row"><span class="k">Birthdate</span><span class="v">{{ $employee->birthdate ? date('M d, Y', strtotime($employee->birthdate)) : '—' }}</span></div>
                <div class="info-row"><span class="k">Address</span><span class="v">{{ $employee->address ?? '—' }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Employment Details</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Employee ID</span><span class="v">{{ $employee->employee_id }}</span></div>
                <div class="info-row"><span class="k">Department</span><span class="v">{{ $employee->department }}</span></div>
                <div class="info-row"><span class="k">Position</span><span class="v">{{ $employee->position }}</span></div>
                <div class="info-row"><span class="k">Date Hired</span><span class="v">{{ date('M d, Y', strtotime($employee->date_hired)) }}</span></div>
                <div class="info-row"><span class="k">Type</span><span class="v">{{ $employee->employment_type ?? '—' }}</span></div>
                <div class="info-row"><span class="k">Status</span><span class="v">{{ $employee->status }}</span></div>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top:18px;">
        <div class="card-head"><h2>Recent Attendance</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date</th><th>Time In</th><th>Time Out</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($attendance as $a)
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

    <div class="grid-2" style="margin-top:18px;">
        <div class="card">
            <div class="card-head"><h2>Recent Leave</h2></div>
            <div class="card-body">
                @forelse($leaves as $l)
                    @php $sc = ['Pending'=>'amber','Approved'=>'green','Rejected'=>'red'][$l->status] ?? 'gray'; @endphp
                    <div class="info-row"><span class="k">{{ $l->leave_type }} ({{ $l->total_days }}d)</span><span class="v"><span class="badge {{ $sc }}">{{ $l->status }}</span></span></div>
                @empty
                    <p class="muted">No leave records.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Recent Payroll</h2></div>
            <div class="card-body">
                @forelse($payrolls as $p)
                    <div class="info-row"><span class="k">{{ $p->payroll_month }} {{ $p->payroll_year }}</span><span class="v">₱{{ number_format($p->net_pay, 2) }}</span></div>
                @empty
                    <p class="muted">No payroll records.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
        <div class="card" style="margin-top:18px;">
            <div class="card-head">
                <div>
                    <h2>Portal Login Account</h2>
                    <p>
                        @if($account)
                            Active — <strong>{{ $account->email }}</strong>
                            @if($account->must_change_password) · <span class="badge amber">temp password (pending change)</span> @endif
                        @else
                            No login account yet for this employee.
                        @endif
                    </p>
                </div>
            </div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/employees/{{ $employee->id }}/account" method="POST" class="form-grid" style="align-items:end;">
                    @csrf
                    <div class="field">
                        <label>Login Email</label>
                        <input type="email" name="email" value="{{ old('email', $account->email ?? $employee->email) }}" required>
                    </div>
                    <div class="field">
                        <label>Temporary Password</label>
                        <input type="text" name="temp_password" value="imprint123" minlength="6" required>
                    </div>
                    <div class="field full">
                        <button type="submit" class="btn {{ $account ? 'btn-ghost' : 'btn-primary' }}">
                            {{ $account ? 'Reset Login & Password' : 'Create Portal Login' }}
                        </button>
                        <small style="color:var(--muted); font-weight:500; margin-left:8px;">The employee will be required to set their own password on first sign-in.</small>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
