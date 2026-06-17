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
            <form action="/employees" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="form-section">
                    <h3>Personal Information</h3>
                    <div class="form-grid">
                        <div class="field"><label>Full Name</label><input type="text" name="name" value="{{ old('name') }}" placeholder="Enter full name" required></div>
                        <div class="field"><label>Email Address</label><input type="email" name="email" value="{{ old('email') }}" placeholder="name@email.com"></div>
                        <div class="field"><label>Contact Number</label><input type="text" name="contact_number" value="{{ old('contact_number') }}" placeholder="09xx xxx xxxx"></div>
                        <div class="field"><label>Birthdate</label><input type="date" name="birthdate" value="{{ old('birthdate') }}"></div>
                        <div class="field full"><label>Address</label><textarea name="address" rows="3" placeholder="Complete address">{{ old('address') }}</textarea></div>
                        <div class="field full"><label>Profile Photo</label><input type="file" name="photo" accept="image/*"></div>
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
                        <div class="field">
                            <label>Reports To (Manager)</label>
                            <select name="manager_id">
                                <option value="">— None —</option>
                                @foreach(($managers ?? []) as $mgr)
                                    <option value="{{ $mgr->id }}" {{ (int) old('manager_id') === (int) $mgr->id ? 'selected' : '' }}>{{ $mgr->name }} ({{ $mgr->position }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Leave Credits (annual)</h3>
                    <div class="form-grid">
                        <div class="field"><label>Vacation Leave days</label><input type="number" name="vacation_credits" value="{{ old('vacation_credits', 15) }}" min="0" max="365"></div>
                        <div class="field"><label>Sick Leave days</label><input type="number" name="sick_credits" value="{{ old('sick_credits', 15) }}" min="0" max="365"></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Portal Login (optional)</h3>
                    <label class="acct-toggle">
                        <input type="checkbox" name="create_account" value="1" id="create_account" {{ old('create_account') ? 'checked' : '' }}>
                        <span>Create a self-service portal login for this employee</span>
                    </label>
                    <div class="form-grid" id="acct-fields" style="margin-top:18px;">
                        <div class="field">
                            <label>Temporary Password</label>
                            <input type="text" name="temp_password" value="{{ old('temp_password', 'imprint123') }}" minlength="6">
                            <small style="color:var(--muted); font-weight:500;">The employee must change this on first sign-in. Login uses the email address above.</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="/employees" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Employee</button>
                </div>
            </form>

            <script>
                (function () {
                    const cb = document.getElementById('create_account');
                    const fields = document.getElementById('acct-fields');
                    const sync = () => fields.style.display = cb.checked ? '' : 'none';
                    cb.addEventListener('change', sync); sync();
                })();
            </script>

            <style>
                .acct-toggle { display:flex; align-items:center; gap:10px; font-size:14px; font-weight:600; color:#334155; cursor:pointer; }
                .acct-toggle input { width:18px; height:18px; }
            </style>
        </div>
    </div>
@endsection
