<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payslip · {{ $payroll->employee_name }} | Imprint HRIS</title>
</head>

<body>

<div class="toolbar no-print">
    <a href="/payroll" class="tb-btn">← Back</a>
    <div style="display:flex; gap:10px;">
        <a href="{{ url()->current() }}/pdf" class="tb-btn">⬇ Download PDF</a>
        <button onclick="window.print()" class="tb-btn primary">🖨 Print Payslip</button>
    </div>
</div>

<main class="payslip">
    <header class="ps-head">
        <div class="ps-brand">
            <img src="{{ asset('logoic.png') }}" alt="Imprint Customs">
            <div>
                <h1>Imprint Customs</h1>
                <span>Payslip</span>
            </div>
        </div>
        <div class="ps-period">
            <span>Pay Period</span>
            <strong>{{ $payroll->payroll_month }} {{ $payroll->payroll_year }}</strong>
        </div>
    </header>

    <section class="ps-emp">
        <div><span>Employee</span><strong>{{ $payroll->employee_name }}</strong></div>
        <div><span>Employee ID</span><strong>{{ $payroll->employee_code }}</strong></div>
        <div><span>Department</span><strong>{{ $payroll->department }}</strong></div>
        <div><span>Position</span><strong>{{ $payroll->position }}</strong></div>
        <div><span>Status</span><strong>{{ $payroll->status }}</strong></div>
        <div><span>Payment Date</span><strong>{{ $payroll->payment_date ? date('M d, Y', strtotime($payroll->payment_date)) : 'Not yet paid' }}</strong></div>
    </section>

    <section class="ps-table">
        <div class="ps-col">
            <h3>Earnings</h3>
            <div class="ps-row"><span>Basic Salary</span><strong>₱{{ number_format($payroll->basic_salary, 2) }}</strong></div>
            <div class="ps-row"><span>Allowances</span><strong>₱{{ number_format($payroll->allowances, 2) }}</strong></div>
            <div class="ps-row"><span>Overtime Pay</span><strong>₱{{ number_format($payroll->overtime_pay, 2) }}</strong></div>
            <div class="ps-row total"><span>Gross Pay</span><strong>₱{{ number_format($payroll->gross_pay, 2) }}</strong></div>
        </div>
        <div class="ps-col">
            <h3>Deductions</h3>
            <div class="ps-row"><span>SSS</span><strong>₱{{ number_format($payroll->sss, 2) }}</strong></div>
            <div class="ps-row"><span>PhilHealth</span><strong>₱{{ number_format($payroll->philhealth, 2) }}</strong></div>
            <div class="ps-row"><span>Pag-IBIG</span><strong>₱{{ number_format($payroll->pagibig, 2) }}</strong></div>
            <div class="ps-row"><span>Withholding Tax</span><strong>₱{{ number_format($payroll->tax, 2) }}</strong></div>
            <div class="ps-row"><span>Other</span><strong>₱{{ number_format($payroll->other_deductions, 2) }}</strong></div>
            <div class="ps-row total"><span>Total Deductions</span><strong>₱{{ number_format($payroll->deductions, 2) }}</strong></div>
        </div>
    </section>

    <section class="ps-net">
        <span>Net Pay</span>
        <strong>₱{{ number_format($payroll->net_pay, 2) }}</strong>
    </section>

    @if($payroll->remarks)
        <p class="ps-remarks"><strong>Remarks:</strong> {{ $payroll->remarks }}</p>
    @endif

    <footer class="ps-foot">
        <div class="sig"><span></span>Employee Signature</div>
        <div class="sig"><span></span>Authorized Signature</div>
    </footer>

    <p class="ps-generated">Generated on {{ now()->format('F d, Y h:i A') }} · This is a system-generated payslip.</p>
</main>

<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: Inter, Arial, sans-serif; background: #f6f7fb; color: #111827; }

    .toolbar {
        max-width: 800px;
        margin: 24px auto 0;
        padding: 0 24px;
        display: flex;
        justify-content: space-between;
    }

    .tb-btn {
        text-decoration: none;
        border: 1px solid #e5e7eb;
        background: white;
        color: #111827;
        padding: 12px 18px;
        border-radius: 12px;
        font-weight: 800;
        font-size: 14px;
        cursor: pointer;
        font-family: inherit;
    }

    .tb-btn.primary { background: #111827; color: white; border-color: #111827; }

    .payslip {
        max-width: 800px;
        margin: 20px auto 60px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .ps-head {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        border-bottom: 2px solid #111827;
        padding-bottom: 24px;
        margin-bottom: 24px;
    }

    .ps-brand { display: flex; gap: 14px; align-items: center; }
    .ps-brand img { width: 56px; height: 56px; object-fit: contain; }
    .ps-brand h1 { margin: 0; font-size: 24px; letter-spacing: -0.02em; }
    .ps-brand span { color: #6b7280; font-weight: 700; font-size: 14px; }

    .ps-period { text-align: right; }
    .ps-period span { display: block; color: #6b7280; font-size: 12px; font-weight: 700; }
    .ps-period strong { font-size: 16px; }

    .ps-emp {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    .ps-emp span { display: block; color: #6b7280; font-size: 12px; font-weight: 700; margin-bottom: 4px; }
    .ps-emp strong { font-size: 14px; }

    .ps-table {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 24px;
    }

    .ps-col h3 {
        margin: 0 0 12px;
        font-size: 14px;
        text-transform: uppercase;
        letter-spacing: .06em;
        color: #6b7280;
    }

    .ps-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f3f4f6;
        font-size: 14px;
        font-weight: 600;
    }

    .ps-row.total {
        border-bottom: none;
        border-top: 2px solid #e5e7eb;
        margin-top: 6px;
        font-weight: 900;
    }

    .ps-net {
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #111827;
        color: white;
        padding: 22px 28px;
        border-radius: 18px;
    }

    .ps-net span { font-size: 16px; font-weight: 700; }
    .ps-net strong { font-size: 28px; }

    .ps-remarks { margin: 20px 0 0; color: #4b5563; font-size: 13px; }

    .ps-foot {
        display: flex;
        justify-content: space-between;
        gap: 40px;
        margin-top: 60px;
    }

    .sig { flex: 1; text-align: center; font-size: 13px; color: #6b7280; font-weight: 700; }
    .sig span { display: block; border-top: 1px solid #111827; margin-bottom: 8px; }

    .ps-generated { text-align: center; color: #9ca3af; font-size: 11px; margin-top: 30px; }

    @media print {
        body { background: white; }
        .no-print { display: none; }
        .payslip { box-shadow: none; border: none; margin: 0; max-width: 100%; border-radius: 0; }
    }

    @media (max-width: 600px) {
        .ps-emp { grid-template-columns: 1fr 1fr; }
        .ps-table { grid-template-columns: 1fr; }
        .payslip { padding: 24px; }
    }
</style>

</body>
</html>
