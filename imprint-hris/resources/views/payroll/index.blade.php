@extends('layouts.app')

@section('title', 'Payroll | Imprint HRIS')
@section('heading', 'Payroll')

@section('content')
    <div class="page-head">
        <div>
            <h1>Payroll</h1>
            <p>Manage salary records and payslips.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <div class="head-actions">
                <a href="/payroll/create" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Payroll
                </a>
            </div>
        @endif
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Total Records</div><div class="stat-value">{{ $totalPayrolls ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingPayrolls ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Paid</div><div class="stat-value">{{ $paidPayrolls ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Total Net Pay</div><div class="stat-value">₱{{ number_format($totalNetPay ?? 0, 0) }}</div></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>Payroll Records</h2>
            <form method="GET" action="/payroll" style="display:flex; gap:8px; flex-wrap:wrap;">
                <div class="search">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                    <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search..." style="width:180px;">
                </div>
                <select name="status" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:11px;padding:10px 12px;font-size:14px;font-weight:600;font-family:inherit;">
                    <option value="">All statuses</option>
                    @foreach(['Pending','Paid'] as $s)
                        <option value="{{ $s }}" {{ ($status ?? '') === $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><th>Employee</th><th>Period</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th></th></tr>
                </thead>
                <tbody>
                    @forelse($payrollRecords ?? [] as $payroll)
                        @php $sc = $payroll->status === 'Paid' ? 'green' : 'amber'; @endphp
                        <tr>
                            <td>
                                <div class="cell-user">
                                    <div class="avatar">{{ strtoupper(substr($payroll->employee_name ?? 'E', 0, 1)) }}</div>
                                    <div><strong>{{ $payroll->employee_name }}</strong><span>{{ $payroll->department }}</span></div>
                                </div>
                            </td>
                            <td>{{ $payroll->payroll_month }} {{ $payroll->payroll_year }}</td>
                            <td>₱{{ number_format($payroll->gross_pay, 2) }}</td>
                            <td>₱{{ number_format($payroll->deductions, 2) }}</td>
                            <td><strong style="color:var(--text);">₱{{ number_format($payroll->net_pay, 2) }}</strong></td>
                            <td><span class="badge {{ $sc }}">{{ $payroll->status }}</span></td>
                            <td>
                                <div class="actions">
                                    <a href="/payroll/{{ $payroll->id }}" class="btn btn-ghost btn-sm">View</a>
                                    <a href="/payroll/{{ $payroll->id }}/payslip" class="btn btn-ghost btn-sm" target="_blank">Payslip</a>
                                    <a href="/payroll/{{ $payroll->id }}/payslip/pdf" class="btn btn-ghost btn-sm">PDF</a>
                                    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                        @if($payroll->status === 'Pending')
                                            <form action="/payroll/{{ $payroll->id }}/paid" method="POST">@csrf @method('PATCH')<button type="submit" class="btn btn-success btn-sm">Mark Paid</button></form>
                                        @endif
                                        <form action="/payroll/{{ $payroll->id }}" method="POST" onsubmit="return confirm('Delete this payroll record?')">@csrf @method('DELETE')<button type="submit" class="btn btn-danger btn-sm">Delete</button></form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                                <h3>No payroll records yet</h3>
                                <p>Create payroll records to generate payslips.</p>
                                @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                    <a href="/payroll/create" class="btn btn-primary">Add Payroll</a>
                                @endif
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @include('partials.pager', ['p' => $payrollRecords])
    </div>
@endsection
