<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="add-employee-page">

    <section class="page-header">
        <div>
            <span class="badge">Employee Management</span>
            <h1>Add Employee</h1>
            <p>
                Create a new employee record for the Imprint Customs HRIS.
            </p>
        </div>

        <a href="/employees" class="back-btn">← Back to Employees</a>
    </section>

    <section class="form-panel">
        <form action="/employees" method="POST" class="employee-form">
            @csrf

            <div class="form-section">
                <h2>Personal Information</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Full Name</label>
                        <input type="text" name="name" placeholder="Enter full name" required>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" placeholder="Enter email address">
                    </div>

                    <div class="form-group">
                        <label>Contact Number</label>
                        <input type="text" name="contact_number" placeholder="Enter contact number">
                    </div>

                    <div class="form-group">
                        <label>Birthdate</label>
                        <input type="date" name="birthdate">
                    </div>

                    <div class="form-group full">
                        <label>Address</label>
                        <textarea name="address" rows="3" placeholder="Enter complete address"></textarea>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h2>Employment Details</h2>

                <div class="form-grid">
                    <div class="form-group">
                        <label>Employee ID</label>
                        <input type="text" name="employee_id" placeholder="EMP-0001" required>
                    </div>

                    <div class="form-group">
                        <label>Department</label>
                        <select name="department" required>
                            <option value="">Select department</option>
                            @foreach(($departments ?? []) as $dept)
                                <option value="{{ $dept->name }}" {{ old('department') === $dept->name ? 'selected' : '' }}>{{ $dept->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Position</label>
                        <input type="text" name="position" placeholder="Enter position" required>
                    </div>

                    <div class="form-group">
                        <label>Date Hired</label>
                        <input type="date" name="date_hired" required>
                    </div>

                    <div class="form-group">
                        <label>Employment Type</label>
                        <select name="employment_type">
                            <option value="">Select type</option>
                            <option value="Full-time">Full-time</option>
                            <option value="Part-time">Part-time</option>
                            <option value="Probationary">Probationary</option>
                            <option value="Regular">Regular</option>
                            <option value="OJT">OJT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status</label>
                        <select name="status" required>
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                            <option value="On Leave">On Leave</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <a href="/employees" class="cancel-btn">Cancel</a>
                <button type="submit" class="save-btn">Save Employee</button>
            </div>
        </form>
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

    .add-employee-page {
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

    .employee-form {
        display: grid;
        gap: 34px;
    }

    .form-section {
        border-bottom: 1px solid #f3f4f6;
        padding-bottom: 30px;
    }

    .form-section:last-of-type {
        border-bottom: none;
        padding-bottom: 0;
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
        padding-top: 4px;
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
        .add-employee-page {
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
</style>

</body>
</html>