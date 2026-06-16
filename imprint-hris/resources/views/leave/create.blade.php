@extends('layouts.app')

@section('title', 'Add Leave Request | Imprint HRIS')
@section('heading', 'Add Leave Request')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Leave Request</h1>
            <p>File a new leave request.</p>
        </div>
        <div class="head-actions"><a href="/leave" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/leave" method="POST">
                @csrf
                <div class="form-grid">
                    <div class="field full">
                        <label>Employee</label>
                        <select name="employee_id" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (int) old('employee_id') === (int) $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->employee_id }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field full">
                        <label>Leave Type</label>
                        <select name="leave_type" required>
                            <option value="">Select type</option>
                            @foreach(['Vacation Leave','Sick Leave','Emergency Leave','Maternity Leave','Paternity Leave','Unpaid Leave'] as $lt)
                                <option value="{{ $lt }}" {{ old('leave_type') === $lt ? 'selected' : '' }}>{{ $lt }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Start Date</label><input type="date" name="start_date" value="{{ old('start_date') }}" required></div>
                    <div class="field"><label>End Date</label><input type="date" name="end_date" value="{{ old('end_date') }}" required></div>
                    <div class="field full"><label>Reason</label><textarea name="reason" rows="4" placeholder="Enter leave reason">{{ old('reason') }}</textarea></div>
                </div>
                <div class="form-actions">
                    <a href="/leave" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
@endsection
