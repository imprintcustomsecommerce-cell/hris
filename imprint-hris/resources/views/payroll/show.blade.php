<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Record | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    @php $statusClass = $payroll->status === 'Paid' ? 'green' : 'amber'; @endphp

    <section class="page-header">
        <div>
            <span class="badge">Payroll</span>
            <h1>{{ $payroll->payroll_month }} {{ $payroll->payroll_year }}</h1>
            <p>{{ $payroll->employee_name }} · {{ $payroll->department }}</p>
        </div>
        <div style="display:flex; gap:10px;">
            <a href="/payroll/{{ $payroll->id }}/payslip" class="add-btn" target="_blank">View Payslip</a>
            <a href="/payroll" class="back-btn">← Back</a>
        </div>
    </section>

    <section class="detail-grid">
        <div class="detail-card">
            <h2>Employee</h2>
            <div class="detail-row"><span>Name</span><strong>{{ $payroll->employee_name }}</strong></div>
            <div class="detail-row"><span>Employee ID</span><strong>{{ $payroll->employee_code }}</strong></div>
            <div class="detail-row"><span>Department</span><strong>{{ $payroll->department }}</strong></div>
            <div class="detail-row"><span>Position</span><strong>{{ $payroll->position }}</strong></div>
            <div class="detail-row"><span>Status</span><strong><span class="status {{ $statusClass }}">{{ $payroll->status }}</span></strong></div>
            <div class="detail-row"><span>Payment Date</span><strong>{{ $payroll->payment_date ? date('M d, Y', strtotime($payroll->payment_date)) : '—' }}</strong></div>
        </div>

        <div class="detail-card">
            <h2>Earnings & Deductions</h2>
            <div class="detail-row"><span>Basic Salary</span><strong>₱{{ number_format($payroll->basic_salary, 2) }}</strong></div>
            <div class="detail-row"><span>Allowances</span><strong>₱{{ number_format($payroll->allowances, 2) }}</strong></div>
            <div class="detail-row"><span>Overtime Pay</span><strong>₱{{ number_format($payroll->overtime_pay, 2) }}</strong></div>
            <div class="detail-row"><span>Gross Pay</span><strong>₱{{ number_format($payroll->gross_pay, 2) }}</strong></div>
            <div class="detail-row"><span>Deductions</span><strong>− ₱{{ number_format($payroll->deductions, 2) }}</strong></div>
            <div class="detail-row"><span>Net Pay</span><strong style="font-size:18px;">₱{{ number_format($payroll->net_pay, 2) }}</strong></div>
        </div>
    </section>
</main>

@include('components.page-style')

</body>
</html>
