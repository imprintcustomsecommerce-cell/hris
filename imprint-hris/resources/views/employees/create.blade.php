@extends('layouts.app')

@section('title', 'Add Employee | Imprint HRIS')
@section('heading', 'Add Employee')

@section('content')
    <div class="page-head">
        <div>
            <h1>Add Employee</h1>
            <p>Create a new employee record.</p>
        </div>
        <div class="head-actions">
            <a href="/employees" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px;">
            <form action="/employees" method="POST">
                @csrf

                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="field"><label>Full Name</label><input type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" required></div>
                        <div class="field"><label>Email Address</label><input type="email" name="email" value="{{ old('email') }}" placeholder="name@email.com"></div>
                        <div class="field"><label>Contact Number</label><input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="09xx xxx xxxx"></div>
                        <div class="field"><label>Birthdate</label><input type="date" name="birthdate" value="{{ old('birthdate') }}"></div>
                        <div class="field full"><label>Address</label><textarea name="address" rows="3" placeholder="Complete address">{{ old('address') }}</textarea></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Employment Details</h3>
                    <div class="form-grid">
                        <div class="field"><label>Employee ID</label><input type="text" name="employee_id" value="{{ old('employee_id') }}" placeholder="EMP-0001" required></div>
                        <div class="field">
                            <label>Department</label>
                            <select name="department" required>
                                <option value="">Select department</option>
                                @foreach(($departments ?? []) as $dept)
                                    <option value="{{ $dept->name }}" {{ old('department') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field"><label>Position</label><input type="text" name="position" value="{{ old('position') }}" placeholder="Enter position" required></div>
                        <div class="field"><label>Date Hired</label><input type="date" name="date_hired" value="{{ old('date_hired') }}" required></div>
                        <div class="field">
                            <label>Employment Type</label>
                            <select name="employment_type">
                                @foreach(['', 'Full-time', 'Part-time', 'Probationary', 'Regular', 'OJT'] as $type)
                                    <option value="{{ $type }}" {{ old('employment_type') === $type ? 'selected' : '' }}>{{ $type ?: 'Select type' }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="field">
                            <label>Status</label>
                            <select name="status" required>
                                @foreach(['Active', 'Inactive', 'On Leave'] as $st)
                                    <option value="{{ $st }}" {{ old('status') === $st ? 'selected' : '' }}>{{ $st }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/employees" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>
        </div>
    </div>
@endsection
