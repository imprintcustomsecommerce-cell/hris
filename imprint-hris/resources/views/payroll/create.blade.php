@extends('layouts.app')

@section('title', 'Add Payroll | Imprint HRIS')
@section('heading', 'Add Payroll')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Payroll</h1>
            <p>Create a new payroll record. Gross and net pay are computed automatically.</p>
        </div>
        <div class="head-actions"><a href="/payroll" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/payroll" method="POST">
                @csrf

                <div class="form-section">
                    <h3>Pay Period</h3>
                    <div class="form-grid">
                        <div class="field full">
                            <label>Employee</label>
                            <select name="employee_id" required>
                                <option value="">Select employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}" {{ (int) old('employee_id') === (int) $employee->id ? 'selected' : '' }}>{{ $employee->name }} ({{ $employee->employee_id }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Month</label>
                            <select name="payroll_month" required>
                                <option value="">Select month</option>
                                @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $month)
                                    <option value="{{ $month }}" {{ old('payroll_month') === $month ? 'selected' : '' }}>{{ $month }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label>Year</label><input type="number" name="payroll_year" value="{{ old('payroll_year', now()->year) }}" required></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Earnings & Deductions</h3>
                    <div class="form-grid">
                        <div class="field"><label>Basic Salary (₱)</label><input type="number" step="0.01" min="0" name="basic_salary" value="{{ old('basic_salary') }}" placeholder="0.00" required></div>
                        <div class="field"><label>Allowances (₱)</label><input type="number" step="0.01" min="0" name="allowances" value="{{ old('allowances', 0) }}" placeholder="0.00"></div>
                        <div class="field"><label>Overtime Pay (₱)</label><input type="number" step="0.01" min="0" name="overtime_pay" value="{{ old('overtime_pay', 0) }}" placeholder="0.00"></div>
                        <div class="field"><label>Deductions (₱)</label><input type="number" step="0.01" min="0" name="deductions" value="{{ old('deductions', 0) }}" placeholder="0.00"></div>
                        <div class="field full"><label>Remarks</label><textarea name="remarks" rows="3" placeholder="Optional remarks">{{ old('remarks') }}</textarea></div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/payroll" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Payroll</button>
                </div>
            </form>
        </div>
    </div>
@endsection
