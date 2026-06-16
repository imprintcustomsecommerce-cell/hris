<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendance | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    <section class="page-header">
        <div>
            <span class="badge">Attendance</span>
            <h1>Edit Attendance</h1>
            <p>Update this daily time record.</p>
        </div>
        <a href="/attendance" class="back-btn">← Back to Attendance</a>
    </section>

    <section class="form-panel">
        <form action="/attendance/{{ $record->id }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-grid">
                <div class="form-group full">
                    <label>Employee</label>
                    <select name="employee_id" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (int) old('employee_id', $record->employee_id) === (int) $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->employee_id }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Date</label>
                    <input type="date" name="attendance_date" value="{{ old('attendance_date', $record->attendance_date) }}" required>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status" required>
                        @foreach(['Present', 'Late', 'Absent', 'Half Day', 'On Leave'] as $st)
                            <option value="{{ $st }}" {{ old('status', $record->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label>Time In</label>
                    <input type="time" name="time_in" value="{{ old('time_in', $record->time_in ? substr($record->time_in, 0, 5) : '') }}">
                </div>
                <div class="form-group">
                    <label>Time Out</label>
                    <input type="time" name="time_out" value="{{ old('time_out', $record->time_out ? substr($record->time_out, 0, 5) : '') }}">
                </div>
                <div class="form-group full">
                    <label>Remarks</label>
                    <textarea name="remarks" rows="2">{{ old('remarks', $record->remarks) }}</textarea>
                </div>
            </div>
            <div class="form-actions">
                <a href="/attendance" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Update Record</button>
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
