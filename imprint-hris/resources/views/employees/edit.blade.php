<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    <section class="page-header">
        <div>
            <span class="badge">Employee Management</span>
            <h1>Edit Employee</h1>
            <p>Update the record for {{ $employee->name }}.</p>
        </div>
        <a href="/employees" class="back-btn">← Back to Employees</a>
    </section>

    <section class="form-panel">
        <form action="/employees/{{ $employee->id }}" method="POST">
            @csrf
            @method('PUT')

            <h2 style="margin:0 0 20px;">Personal Information</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="{{ old('name', $employee->name) }}" required>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email', $employee->email) }}">
                </div>
                <div class="form-group">
                    <label>Contact Number</label>
                    <input type="text" name="contact_number" value="{{ old('contact_number', $employee->contact_number) }}">
                </div>
                <div class="form-group">
                    <label>Birthdate</label>
                    <input type="date" name="birthdate" value="{{ old('birthdate', $employee->birthdate) }}">
                </div>
                <div class="form-group full">
                    <label>Address</label>
                    <textarea name="address" rows="3">{{ old('address', $employee->address) }}</textarea>
                </div>
            </div>

            <h2 style="margin:30px 0 20px;">Employment Details</h2>
            <div class="form-grid">
                <div class="form-group">
                    <label>Employee ID</label>
                    <input type="text" name="employee_id" value="{{ old('employee_id', $employee->employee_id) }}" required>
                </div>
                <div class="form-group">
                    <label>Department</label>
                    <select name="department" required>
                        <option value="">Select department</option>
                        @foreach(($departments ?? []) as $dept)
                            <option value="{{ $dept->name }}" {{ old('department', $employee->department) === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Position</label>
                    <input type="text" name="position" value="{{ old('position', $employee->position) }}" required>
                </div>
                <div class="form-group">
                    <label>Date Hired</label>
                    <input type="date" name="date_hired" value="{{ old('date_hired', $employee->date_hired) }}" required>
                </div>
                <div class="form-group">
                    <label>Employment Type</label>
                    <select name="employment_type">
                        @foreach(['', 'Full-time', 'Part-time', 'Probationary', 'Regular', 'OJT'] as $type)
                            <option value="{{ $type }}" {{ old('employment_type', $employee->employment_type) === $type ? 'selected' : '' }}>{{ $type ?: 'Select type' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        @foreach(['Active', 'Inactive', 'On Leave'] as $st)
                            <option value="{{ $st }}" {{ old('status', $employee->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-actions">
                <a href="/employees" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Update Employee</button>
            </div>
        </form>

        @if ($errors->any())
            <div class="error-alert">
                <strong>Please check the form.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
    </section>
</main>

@include('components.page-style')

</body>
</html>
