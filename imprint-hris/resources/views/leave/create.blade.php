<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Leave Request | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="add-leave-page">

    <section class="page-header">
        <div>
            <span class="badge">Leave Management</span>
            <h1>Add Leave Request</h1>
            <p>
                Create a leave request for an employee.
            </p>
        </div>

        <a href="/leave" class="back-btn">← Back to Leave</a>
    </section>

    <section class="form-panel">
        <form action="/leave" method="POST" class="leave-form">
            @csrf

            @if ($errors->any())
                <div class="error-alert">
                    <strong>Please check the form.</strong>

                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-section">
                <h2>Leave Details</h2>

                <div class="form-grid">
                    <div class="form-group full">
                        <label>Employee</label>
                        <select name="employee_id" required>
                            <option value="">Select employee</option>

                            @foreach($employees ?? [] as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->name }} — {{ $employee->employee_id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Leave Type</label>
                        <select name="leave_type" required>
                            <option value="">Select leave type</option>
                            <option value="Vacation Leave" {{ old('leave_type') == 'Vacation Leave' ? 'selected' : '' }}>Vacation Leave</option>
                            <option value="Sick Leave" {{ old('leave_type') == 'Sick Leave' ? 'selected' : '' }}>Sick Leave</option>
                            <option value="Emergency Leave" {{ old('leave_type') == 'Emergency Leave' ? 'selected' : '' }}>Emergency Leave</option>
                            <option value="Maternity Leave" {{ old('leave_type') == 'Maternity Leave' ? 'selected' : '' }}>Maternity Leave</option>
                            <option value="Paternity Leave" {{ old('leave_type') == 'Paternity Leave' ? 'selected' : '' }}>Paternity Leave</option>
                            <option value="Unpaid Leave" {{ old('leave_type') == 'Unpaid Leave' ? 'selected' : '' }}>Unpaid Leave</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <input type="text" value="Pending" disabled>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}" required>
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}" required>
                    </div>

                    <div class="form-group full">
                        <label>Reason</label>
                        <textarea name="reason" rows="5" placeholder="Enter leave reason">{{ old('reason') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="/leave" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Save Leave Request</button>
            </div>
        </form>
    </section>

</main>

<style>
    * {
        box-sizing: border-box;
    }

    body {
        margin: 0;
        font-family: Inter, Arial, sans-serif;
        background: #f6f7fb;
        color: #111827;
    }

    .add-leave-page {
        min-height: calc(100vh - 78px);
        padding: 50px 32px;
        background:
            radial-gradient(circle at top left, rgba(17, 24, 39, 0.08), transparent 35%),
            #f6f7fb;
    }

    .page-header {
        max-width: 1280px;
        margin: 0 auto 28px;
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        gap: 24px;
    }

    .badge {
        display: inline-block;
        background: #111827;
        color: white;
        padding: 10px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 800;
        margin-bottom: 18px;
    }

    .page-header h1 {
        font-size: clamp(36px, 5vw, 56px);
        margin: 0 0 12px;
        letter-spacing: -0.04em;
        line-height: 1;
    }

    .page-header p {
        margin: 0;
        color: #6b7280;
        font-size: 17px;
        line-height: 1.6;
    }

    .back-btn {
        background: white;
        color: #111827;
        text-decoration: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-weight: 800;
        border: 1px solid #e5e7eb;
        white-space: nowrap;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .08);
    }

    .form-panel {
        max-width: 1280px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 34px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .leave-form {
        display: grid;
        gap: 34px;
    }

    .error-alert {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 16px 20px;
        border-radius: 16px;
        font-size: 14px;
        font-weight: 700;
    }

    .error-alert strong {
        display: block;
        margin-bottom: 8px;
    }

    .error-alert ul {
        margin: 0;
        padding-left: 20px;
    }

    .form-section h2 {
        margin: 0 0 24px;
        font-size: 24px;
        color: #111827;
    }

    .form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group.full {
        grid-column: span 2;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 800;
        color: #374151;
    }

    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 14px 16px;
        border-radius: 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
        color: #111827;
        font-family: inherit;
    }

    .form-group input:disabled {
        color: #6b7280;
        cursor: not-allowed;
    }

    .form-group textarea {
        resize: vertical;
    }

    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
        border-color: #111827;
        background: white;
        box-shadow: 0 0 0 4px rgba(17, 24, 39, .06);
    }

    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .cancel-btn,
    .save-btn {
        border: none;
        text-decoration: none;
        padding: 14px 22px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
    }

    .cancel-btn {
        background: #f3f4f6;
        color: #111827;
    }

    .save-btn {
        background: #111827;
        color: white;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .18);
    }

    .cancel-btn:hover {
        background: #e5e7eb;
    }

    .save-btn:hover {
        background: #030712;
    }

    @media (max-width: 900px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-group.full {
            grid-column: span 1;
        }

        .back-btn {
            width: 100%;
            text-align: center;
        }
    }

    @media (max-width: 600px) {
        .add-leave-page {
            padding: 36px 20px;
        }

        .form-panel {
            padding: 24px;
            border-radius: 24px;
        }

        .form-actions {
            flex-direction: column;
        }

        .cancel-btn,
        .save-btn {
            width: 100%;
            text-align: center;
        }
    }
</style>

</body>
</html>