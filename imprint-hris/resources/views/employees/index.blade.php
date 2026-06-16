<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="employees-page">
    @if(session('success'))
    <div class="success-alert">
        {{ session('success') }}
    </div>
@endif

    <section class="page-header">
        <div>
            <span class="badge">Employee Management</span>
            <h1>Employees</h1>
            <p>
                View, organize, and manage all employee records in one place.
            </p>
        </div>

        <a href="/employees/create" class="add-btn">+ Add Employee</a>
    </section>

    <section class="summary-grid">
        <div class="summary-card">
            <span>Total Employees</span>
            <strong>{{ $totalEmployees ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Active</span>
            <strong>{{ $activeEmployees ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>On Leave</span>
            <strong>{{ $onLeaveEmployees ?? 0 }}</strong>
        </div>

        <div class="summary-card">
            <span>Departments</span>
            <strong>{{ $departments ?? 0 }}</strong>
        </div>
    </section>

    <section class="employee-panel">
        <div class="panel-top">
            <div>
                <h2>Employee List</h2>
                <p>Manage employee information, status, and department records.</p>
            </div>

            <div class="search-box">
                <input type="text" placeholder="Search employee...">
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Employee ID</th>
                        <th>Department</th>
                        <th>Position</th>
                        <th>Status</th>
                        <th>Date Hired</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($employees ?? [] as $employee)
                        <tr>
                            <td>
                                <div class="employee-info">
                                    <div class="avatar">
                                        {{ strtoupper(substr($employee->name ?? 'E', 0, 1)) }}
                                    </div>

                                    <div>
                                        <strong>{{ $employee->name }}</strong>
                                        <span>{{ $employee->email ?? 'No email added' }}</span>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                            <td>{{ $employee->department ?? 'N/A' }}</td>
                            <td>{{ $employee->position ?? 'N/A' }}</td>

                            <td>
                                <span class="status active">
                                    {{ $employee->status ?? 'Active' }}
                                </span>
                            </td>

                            <td>
                                {{ isset($employee->date_hired) ? date('M d, Y', strtotime($employee->date_hired)) : 'N/A' }}
                            </td>

                            <td>
                                <div class="actions">
                                    <a href="/employees/{{ $employee->id }}">View</a>
                                    <a href="/employees/{{ $employee->id }}/edit">Edit</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <div class="empty-icon">👥</div>
                                    <h3>No employees yet</h3>
                                    <p>Start adding employee records to your HRIS.</p>
                                    <a href="/employees/create">Add First Employee</a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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

    .employees-page {
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

    .add-btn {
        background: #111827;
        color: white;
        text-decoration: none;
        padding: 14px 20px;
        border-radius: 14px;
        font-weight: 800;
        white-space: nowrap;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .18);
    }

    .summary-grid {
        max-width: 1280px;
        margin: 0 auto 24px;
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 18px;
    }

    .summary-card {
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 24px;
        padding: 24px;
        box-shadow: 0 20px 50px rgba(17, 24, 39, .07);
    }

    .summary-card span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 700;
        margin-bottom: 10px;
    }

    .summary-card strong {
        font-size: 34px;
        color: #111827;
    }

    .employee-panel {
        max-width: 1280px;
        margin: 0 auto;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 28px;
        box-shadow: 0 25px 70px rgba(17, 24, 39, .08);
    }

    .panel-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        margin-bottom: 24px;
    }

    .panel-top h2 {
        margin: 0 0 6px;
        font-size: 24px;
    }

    .panel-top p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .search-box input {
        width: 280px;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 13px 16px;
        border-radius: 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
    }

    .search-box input:focus {
        border-color: #111827;
        background: white;
    }

    .table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }

    th {
        text-align: left;
        padding: 14px 16px;
        color: #6b7280;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .06em;
        border-bottom: 1px solid #e5e7eb;
    }

    td {
        padding: 18px 16px;
        border-bottom: 1px solid #f3f4f6;
        color: #374151;
        font-size: 14px;
        font-weight: 600;
    }

    tr:last-child td {
        border-bottom: none;
    }

    .employee-info {
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .avatar {
        width: 46px;
        height: 46px;
        border-radius: 16px;
        background: #111827;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        flex-shrink: 0;
    }

    .employee-info strong {
        display: block;
        color: #111827;
        font-size: 15px;
        margin-bottom: 4px;
    }

    .employee-info span {
        display: block;
        color: #6b7280;
        font-size: 13px;
        font-weight: 500;
    }

    .status {
        display: inline-flex;
        align-items: center;
        padding: 7px 12px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 800;
    }

    .status.active {
        background: #dcfce7;
        color: #166534;
    }

    .actions {
        display: flex;
        gap: 8px;
    }

    .actions a {
        text-decoration: none;
        color: #111827;
        background: #f3f4f6;
        padding: 8px 12px;
        border-radius: 10px;
        font-size: 12px;
        font-weight: 800;
    }

    .actions a:hover {
        background: #111827;
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
    }

    .empty-icon {
        font-size: 42px;
        margin-bottom: 16px;
    }

    .empty-state h3 {
        margin: 0 0 8px;
        font-size: 22px;
        color: #111827;
    }

    .empty-state p {
        margin: 0 0 22px;
        color: #6b7280;
    }

    .empty-state a {
        display: inline-block;
        text-decoration: none;
        background: #111827;
        color: white;
        padding: 12px 18px;
        border-radius: 14px;
        font-weight: 800;
    }

    @media (max-width: 1000px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .summary-grid {
            grid-template-columns: repeat(2, 1fr);
        }

        .panel-top {
            flex-direction: column;
            align-items: flex-start;
        }

        .search-box,
        .search-box input {
            width: 100%;
        }
    }

    @media (max-width: 600px) {
        .employees-page {
            padding: 36px 20px;
        }

        .summary-grid {
            grid-template-columns: 1fr;
        }

        .employee-panel {
            padding: 22px;
            border-radius: 24px;
        }

        .add-btn {
            width: 100%;
            text-align: center;
        }
    }

    .success-alert {
    max-width: 1280px;
    margin: 0 auto 20px;
    background: #dcfce7;
    color: #166534;
    border: 1px solid #bbf7d0;
    padding: 16px 20px;
    border-radius: 16px;
    font-size: 14px;
    font-weight: 800;
}
</style>

</body>
</html>