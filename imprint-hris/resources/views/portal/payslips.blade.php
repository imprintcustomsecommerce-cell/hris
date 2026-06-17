@extends('layouts.app')

@section('title', 'My Payslips | Imprint HRIS')
@section('heading', 'My Payslips')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Payslips</h1>
            <p>View and print your salary records.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Payslip History</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Period</th><th>Gross</th><th>Deductions</th><th>Net Pay</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($payrolls as $p)
                        @php $sc = $p->status === 'Paid' ? 'green' : 'amber'; @endphp
                        <tr>
                            <td>{{ $p->payroll_month }} {{ $p->payroll_year }}</td>
                            <td>₱{{ number_format($p->gross_pay, 2) }}</td>
                            <td>₱{{ number_format($p->deductions, 2) }}</td>
                            <td><strong style="color:var(--text);">₱{{ number_format($p->net_pay, 2) }}</strong></td>
                            <td><span class="badge {{ $sc }}">{{ $p->status }}</span></td>
                            <td><a href="/portal/payslips/{{ $p->id }}" class="btn btn-ghost btn-sm" target="_blank">View Payslip</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
                                <h3>No payslips yet</h3>
                                <p>Your payslips will appear here once payroll is processed.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
