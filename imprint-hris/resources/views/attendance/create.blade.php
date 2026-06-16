@extends('layouts.app')

@section('title', 'Add Attendance | Imprint HRIS')
@section('heading', 'Add Attendance')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Attendance</h1>
            <p>Record a daily time entry.</p>
        </div>
        <div class="head-actions"><a href="/attendance" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/attendance" method="POST">
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
                    <div class="field"><label>Date</label><input type="date" name="attendance_date" value="{{ old('attendance_date', now()->toDateString()) }}" required></div>
                    <div class="field">
                        <label>Status</label>
                        <select name="status" required>
                            @foreach(['Present', 'Late', 'Absent', 'Half Day', 'On Leave'] as $st)
                                <option value="{{ $st }}" {{ old('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Time In</label><input type="time" name="time_in" value="{{ old('time_in') }}"></div>
                    <div class="field"><label>Time Out</label><input type="time" name="time_out" value="{{ old('time_out') }}"></div>
                    <div class="field full"><label>Remarks</label><textarea name="remarks" rows="2">{{ old('remarks') }}</textarea></div>
                </div>
                <div class="form-actions">
                    <a href="/attendance" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            </form>
        </div>
    </div>
@endsection
