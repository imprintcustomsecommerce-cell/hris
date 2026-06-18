@extends('layouts.app')

@section('title', 'Remittances | Imprint HRIS')
@section('heading', 'Government Remittances')

@section('content')
    <div class="page-head">
        <div>
            <h1>Government Remittances</h1>
            <p>SSS, PhilHealth, Pag-IBIG and withholding tax per payroll period.</p>
        </div>
        <form method="GET" action="/remittances" class="head-actions">
            <select name="month" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:11px;padding:10px 12px;font-weight:600;font-family:inherit;">
                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m)
                    <option value="{{ $m }}" {{ $month === $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <select name="year" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:11px;padding:10px 12px;font-weight:600;font-family:inherit;">
                @foreach($years as $y)
                    <option value="{{ $y }}" {{ (int) $year === (int) $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">SSS Total</div><div class="stat-value">₱{{ number_format($totals['sss'], 2) }}</div></div>
        <div class="stat"><div class="stat-label">PhilHealth Total</div><div class="stat-value">₱{{ number_format($totals['philhealth'], 2) }}</div></div>
        <div class="stat"><div class="stat-label">Pag-IBIG Total</div><div class="stat-value">₱{{ number_format($totals['pagibig'], 2) }}</div></div>
        <div class="stat"><div class="stat-label">Withholding Tax</div><div class="stat-value">₱{{ number_format($totals['tax'], 2) }}</div></div>
    </div>

    <div class="card">
        <div class="card-head">
            <h2>{{ $month }} {{ $year }}</h2>
            <div class="head-actions">
                @foreach(['sss'=>'SSS','philhealth'=>'PhilHealth','pagibig'=>'Pag-IBIG','tax'=>'BIR'] as $a => $label)
                    <a href="/remittances/export?month={{ urlencode($month) }}&year={{ $year }}&agency={{ $a }}" class="btn btn-ghost btn-sm">⬇ {{ $label }}</a>
                @endforeach
            </div>
        </div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Employee</th><th>Basic</th><th>SSS</th><th>PhilHealth</th><th>Pag-IBIG</th><th>Tax</th></tr></thead>
                <tbody>
                    @forelse($rows as $r)
                        <tr>
                            <td><strong style="color:var(--text);">{{ $r->employee_name }}</strong><br><span style="color:var(--muted);font-size:12px;">{{ $r->code }}</span></td>
                            <td>₱{{ number_format($r->basic_salary, 2) }}</td>
                            <td>₱{{ number_format($r->sss, 2) }}</td>
                            <td>₱{{ number_format($r->philhealth, 2) }}</td>
                            <td>₱{{ number_format($r->pagibig, 2) }}</td>
                            <td>₱{{ number_format($r->tax, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
                                <h3>No payroll for {{ $month }} {{ $year }}</h3>
                                <p>Create payroll records for this period to see remittances.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
                @if($rows->count())
                    <tfoot>
                        <tr style="font-weight:800;">
                            <td>TOTAL</td><td></td>
                            <td>₱{{ number_format($totals['sss'], 2) }}</td>
                            <td>₱{{ number_format($totals['philhealth'], 2) }}</td>
                            <td>₱{{ number_format($totals['pagibig'], 2) }}</td>
                            <td>₱{{ number_format($totals['tax'], 2) }}</td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endsection
