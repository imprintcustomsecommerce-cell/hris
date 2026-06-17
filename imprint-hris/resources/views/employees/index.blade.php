@extends('layouts.app')

@section('title', 'Employees | Imprint HRIS')
@section('heading', 'Employees')

@section('content')
    <div class="page-head">
        <div>
            <h1>Employees</h1>
            <p>Manage all employee records in one place.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <div class="head-actions">
                <a href="/reports/export/employees" class="btn btn-ghost">⬇ Export CSV</a>
                <a href="/employees/create" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Employee
                </a>
            </div>
        @endif
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Total Employees</div><div class="stat-value">{{ $totalEmployees ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Active</div><div class="stat-value">{{ $activeEmployees ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">On Leave</div><div class="stat-value">{{ $onLeaveEmployees ?? 0 }}</div></div>
        <div class="stat"><div class="stat-label">Departments</div><div class="stat-value">{{ $departments ?? 0 }}</div></div>
    </div>

    <div class="card">
        <div class="card-head">
            <div><h2>Employee Directory</h2></div>
            <form class="search" method="GET" action="/employees">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4-4"/></svg>
                <input type="text" name="q" value="{{ $q ?? '' }}" placeholder="Search name, ID, dept..." onchange="this.form.submit()">
            </form>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Employee</th><th>ID</th><th>Department</th><th>Position</th><th>Status</th><th>Hired</th><th></th>
                    </tr>
                </thead>
                <tbody id="emp-rows">
                    @forelse($employees ?? [] as $employee)
                        @php $sc = ['Active'=>'green','On Leave'=>'amber','Inactive'=>'gray'][$employee->status ?? ''] ?? 'gray'; @endphp
                        <tr>
                            <td>
                                <div class="cell-user">
                                    @if($employee->photo)
                                        <img class="avatar" src="{{ asset('storage/' . $employee->photo) }}" alt="" style="object-fit:cover;">
                                    @else
                                        <div class="avatar">{{ strtoupper(substr($employee->name ?? 'E', 0, 1)) }}</div>
                                    @endif
                                    <div>
                                        <strong>{{ $employee->name }}</strong>
                                        <span>{{ $employee->email ?? 'No email' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td>{{ $employee->employee_id ?? 'N/A' }}</td>
                            <td>{{ $employee->department ?? 'N/A' }}</td>
                            <td>{{ $employee->position ?? 'N/A' }}</td>
                            <td><span class="badge {{ $sc }}">{{ $employee->status ?? 'Active' }}</span></td>
                            <td>{{ isset($employee->date_hired) ? date('M d, Y', strtotime($employee->date_hired)) : 'N/A' }}</td>
                            <td>
                                <div class="actions">
                                    <a href="/employees/{{ $employee->id }}" class="btn btn-ghost btn-sm">View</a>
                                    @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                        <a href="/employees/{{ $employee->id }}/edit" class="btn btn-ghost btn-sm">Edit</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/></svg></div>
                                <h3>No employees yet</h3>
                                <p>Start building your team directory.</p>
                                @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                    <a href="/employees/create" class="btn btn-primary">Add First Employee</a>
                                @endif
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection
