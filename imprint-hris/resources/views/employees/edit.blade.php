@extends('layouts.app')

@section('title', 'Edit Employee | Imprint HRIS')
@section('heading', 'Edit Employee')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Employee</h1>
            <p>Update the record for {{ $employee->name }}.</p>
        </div>
        <div class="head-actions">
            <a href="/employees/{{ $employee->id }}" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/employees/{{ $employee->id }}" method="POST">
                @csrf
                @method('PUT')

                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="field"><label>Full Name</label><input type="text" name="name" value="{{ old('name', $employee->name) }}" required></div>
                        <div class="field"><label>Email Address</label><input type="email" name="email" value="{{ old('email', $employee->email) }}"></div>
                        <div class="field"><label>Contact Number</label><input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}"></div>
                        <div class="field"><label>Birthdate</label><input type="date" name="birthdate" value="{{ old('birthdate', $employee->birthdate) }}"></div>
                        <div class="field full"><label>Address</label><textarea name="address" rows="3">{{ old('address', $employee->address) }}</textarea></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Employment Details</h3>
                    <div class="form-grid">
                        <div class="field"><label>Employee ID</label><input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required></div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department" required>
                                <option value="">Select department</option>
                                @foreach(($departments ?? []) as $dept)
                                    <option value="{{ $dept->name }}" {{ old('department', $employee->department) === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label>Position</label><input type="text" name="position" value="{{ old('position', $employee->position) }}" required></div>
                        <div class="field"><label>Date Hired</label><input type="date" name="date_hired" value="{{ old('date_hired', $employee->date_hired) }}" required></div>
                        <div class="field">
                            <label>Employment Type</label>
                            <select name="employment_type">
                                @foreach(['', 'Full-time', 'Part-time', 'Probationary', 'Regular', 'OJT'] as $type)
                                    <option value="{{ $type }}" {{ old('employment_type', $employee->employment_type) === $type ? 'selected' : '' }}>{{ $type ?: 'Select type' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select name="status" required>
                                @foreach(['Active', 'Inactive', 'On Leave'] as $st)
                                    <option value="{{ $st }}" {{ old('status', $employee->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/employees" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Employee</button>
                </div>
            </form>
        </div>
    </div>
@endsection
