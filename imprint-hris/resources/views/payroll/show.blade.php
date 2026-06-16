@extends('layouts.app')

@section('title', 'Payroll Record | Imprint HRIS')
@section('heading', 'Payroll Record')

@section('content')
    @php $sc = $payroll->status === 'Paid' ? 'green' : 'amber'; @endphp

    <div class="page-head">
        <div>
            <h1>{{ $payroll->payroll_month }} {{ $payroll->payroll_year }}</h1>
            <p>{{ $payroll->employee_name }} · {{ $payroll->department }}</p>
        </div>
        <div class="head-actions">
            <a href="/payroll/{{ $payroll->id }}/payslip" class="btn btn-primary" target="_blank">View Payslip</a>
            <a href="/payroll" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Employee</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Name</span><span class="v">{{ $payroll->employee_name }}</span></div>
                <div class="info-row"><span class="k">Employee ID</span><span class="v">{{ $payroll->employee_code }}</span></div>
                <div class="info-row"><span class="k">Department</span><span class="v">{{ $payroll->department }}</span></div>
                <div class="info-row"><span class="k">Position</span><span class="v">{{ $payroll->position }}</span></div>
                <div class="info-row"><span class="k">Status</span><span class="v"><span class="badge {{ $sc }}">{{ $payroll->status }}</span></span></div>
                <div class="info-row"><span class="k">Payment Date</span><span class="v">{{ $payroll->payment_date ? date('M d, Y', strtotime($payroll->payment_date)) : '—' }}</span></div>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Earnings & Deductions</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Basic Salary</span><span class="v">₱{{ number_format($payroll->basic_salary, 2) }}</span></div>
                <div class="info-row"><span class="k">Allowances</span><span class="v">₱{{ number_format($payroll->allowances, 2) }}</span></div>
                <div class="info-row"><span class="k">Overtime Pay</span><span class="v">₱{{ number_format($payroll->overtime_pay, 2) }}</span></div>
                <div class="info-row"><span class="k">Gross Pay</span><span class="v">₱{{ number_format($payroll->gross_pay, 2) }}</span></div>
                <div class="info-row"><span class="k">Deductions</span><span class="v">− ₱{{ number_format($payroll->deductions, 2) }}</span></div>
                <div class="info-row"><span class="k">Net Pay</span><span class="v" style="font-size:18px; color:var(--accent);">₱{{ number_format($payroll->net_pay, 2) }}</span></div>
            </div>
        </div>
    </div>
@endsection
