<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { margin: 0; color: #111; font-size: 12px; }
        .wrap { padding: 28px 32px; }
        .head { width: 100%; border-bottom: 2px solid #111; padding-bottom: 14px; margin-bottom: 18px; }
        .head td { vertical-align: middle; }
        .company { font-size: 20px; font-weight: bold; }
        .doc { font-size: 13px; color: #555; }
        .period { text-align: right; font-size: 12px; }
        .period strong { font-size: 14px; }
        .meta { width: 100%; margin-bottom: 18px; }
        .meta td { padding: 4px 0; font-size: 12px; }
        .meta .label { color: #666; width: 130px; }
        h3 { font-size: 12px; text-transform: uppercase; letter-spacing: .04em; color: #666; margin: 0 0 6px; }
        table.lines { width: 100%; border-collapse: collapse; }
        table.lines td { padding: 6px 0; border-bottom: 1px solid #eee; }
        table.lines td.amt { text-align: right; }
        table.lines tr.total td { border-top: 2px solid #ccc; border-bottom: none; font-weight: bold; }
        .cols { width: 100%; }
        .cols td { vertical-align: top; width: 50%; }
        .cols td.left { padding-right: 16px; }
        .cols td.right { padding-left: 16px; }
        .net { background: #111; color: #fff; padding: 14px 18px; margin-top: 18px; }
        .net td { color: #fff; }
        .net .lbl { font-size: 13px; }
        .net .val { text-align: right; font-size: 20px; font-weight: bold; }
        .sign { width: 100%; margin-top: 50px; }
        .sign td { width: 50%; text-align: center; font-size: 11px; color: #666; padding-top: 30px; }
        .sign .line { border-top: 1px solid #111; padding-top: 6px; }
        .foot { text-align: center; color: #999; font-size: 10px; margin-top: 26px; }
    </style>
</head>
<body>
<div class="wrap">
    <table class="head">
        <tr>
            <td><div class="company">{{ \App\Support\Setting::get('company_name', 'Imprint Customs') }}</div><div class="doc">Payslip</div></td>
            <td class="period"><div>Pay Period</div><strong>{{ $payroll->payroll_month }} {{ $payroll->payroll_year }}</strong></td>
        </tr>
    </table>

    <table class="meta">
        <tr>
            <td class="label">Employee</td><td>{{ $payroll->employee_name }}</td>
            <td class="label">Employee ID</td><td>{{ $payroll->employee_code }}</td>
        </tr>
        <tr>
            <td class="label">Department</td><td>{{ $payroll->department }}</td>
            <td class="label">Position</td><td>{{ $payroll->position }}</td>
        </tr>
        <tr>
            <td class="label">Status</td><td>{{ $payroll->status }}</td>
            <td class="label">Payment Date</td><td>{{ $payroll->payment_date ? date('M d, Y', strtotime($payroll->payment_date)) : 'Not yet paid' }}</td>
        </tr>
    </table>

    <table class="cols">
        <tr>
            <td class="left">
                <h3>Earnings</h3>
                <table class="lines">
                    <tr><td>Basic Salary</td><td class="amt">{{ number_format($payroll->basic_salary, 2) }}</td></tr>
                    <tr><td>Allowances</td><td class="amt">{{ number_format($payroll->allowances, 2) }}</td></tr>
                    <tr><td>Overtime Pay</td><td class="amt">{{ number_format($payroll->overtime_pay, 2) }}</td></tr>
                    <tr class="total"><td>Gross Pay</td><td class="amt">{{ number_format($payroll->gross_pay, 2) }}</td></tr>
                </table>
            </td>
            <td class="right">
                <h3>Deductions</h3>
                <table class="lines">
                    <tr><td>SSS</td><td class="amt">{{ number_format($payroll->sss, 2) }}</td></tr>
                    <tr><td>PhilHealth</td><td class="amt">{{ number_format($payroll->philhealth, 2) }}</td></tr>
                    <tr><td>Pag-IBIG</td><td class="amt">{{ number_format($payroll->pagibig, 2) }}</td></tr>
                    <tr><td>Withholding Tax</td><td class="amt">{{ number_format($payroll->tax, 2) }}</td></tr>
                    <tr><td>Loan / Cash Advance</td><td class="amt">{{ number_format($payroll->loan_deduction, 2) }}</td></tr>
                    <tr><td>Other</td><td class="amt">{{ number_format($payroll->other_deductions, 2) }}</td></tr>
                    <tr class="total"><td>Total Deductions</td><td class="amt">{{ number_format($payroll->deductions, 2) }}</td></tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="net">
        <tr><td class="lbl">NET PAY</td><td class="val">PHP {{ number_format($payroll->net_pay, 2) }}</td></tr>
    </table>

    @if($payroll->remarks)
        <p style="margin-top:16px; font-size:11px; color:#555;"><strong>Remarks:</strong> {{ $payroll->remarks }}</p>
    @endif

    <table class="sign">
        <tr>
            <td><div class="line">Employee Signature</div></td>
            <td><div class="line">Authorized Signature</div></td>
        </tr>
    </table>

    <div class="foot">Generated on {{ now()->format('F d, Y h:i A') }} · System-generated payslip.</div>
</div>
</body>
</html>
