@extends('layouts.app')

@section('title', 'Loans | Imprint HRIS')
@section('heading', 'Loans & Cash Advances')

@section('content')
    <div class="page-head">
        <div>
            <h1>Loans & Cash Advances</h1>
            <p>Amortizations are auto-deducted from payroll and reduce the balance when paid.</p>
        </div>
    </div>

    <div class="stat-grid" style="grid-template-columns:repeat(2,1fr);">
        <div class="stat"><div class="stat-label">Active Loans</div><div class="stat-value">{{ $loans->where('status','Active')->count() }}</div></div>
        <div class="stat"><div class="stat-label">Outstanding Balance</div><div class="stat-value">₱{{ number_format($activeTotal, 0) }}</div></div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>All Loans</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Type</th><th>Amort.</th><th>Balance</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($loans as $loan)
                            <tr>
                                <td><strong style="color:var(--text);">{{ $loan->employee_name }}</strong><br><span style="color:var(--muted);font-size:12px;">{{ $loan->code }}</span></td>
                                <td>{{ $loan->type }}</td>
                                <td>₱{{ number_format($loan->monthly_amortization, 2) }}</td>
                                <td>₱{{ number_format($loan->balance, 2) }}</td>
                                <td><span class="badge {{ $loan->status === 'Paid' ? 'green' : 'amber' }}">{{ $loan->status }}</span></td>
                                <td>
                                    <form action="/loans/{{ $loan->id }}" method="POST" onsubmit="return confirm('Delete this loan?')" style="margin:0;">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><p class="muted">No loans recorded.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>New Loan / Advance</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/loans" method="POST" style="display:grid; gap:16px;">
                    @csrf
                    <div class="field">
                        <label>Employee</label>
                        <select name="employee_id" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Type</label>
                        <select name="type" required>
                            <option value="Loan">Loan</option>
                            <option value="Cash Advance">Cash Advance</option>
                        </select>
                    </div>
                    <div class="field"><label>Principal (₱)</label><input type="number" step="0.01" min="1" name="principal" required></div>
                    <div class="field"><label>Monthly Amortization (₱)</label><input type="number" step="0.01" min="1" name="monthly_amortization" required></div>
                    <div class="field"><label>Reason</label><input type="text" name="reason" placeholder="Optional"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Record</button>
                </form>
            </div>
        </div>
    </div>
@endsection
