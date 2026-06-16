<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Departments | Imprint HRIS</title>
</head>

<body>

<x-header />

<main class="page">
    @if(session('success'))
        <div class="success-alert">{{ session('success') }}</div>
    @endif

    <section class="page-header">
        <div>
            <span class="badge">Organization</span>
            <h1>Departments</h1>
            <p>Manage your company's departments and team structure.</p>
        </div>

        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <a href="/departments/create" class="add-btn">+ Add Department</a>
        @endif
    </section>

    <section class="panel">
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Description</th>
                        <th>Employees</th>
                        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                            <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($departments ?? [] as $dept)
                        <tr>
                            <td><strong>{{ $dept->name }}</strong></td>
                            <td>{{ $dept->description ?? '—' }}</td>
                            <td><span class="pill">{{ $dept->employee_count }}</span></td>
                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <td>
                                    <div class="actions">
                                        <a href="/departments/{{ $dept->id }}/edit">Edit</a>
                                        <form action="/departments/{{ $dept->id }}" method="POST" onsubmit="return confirm('Delete this department?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="del">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    <div class="empty-icon">🏢</div>
                                    <h3>No departments yet</h3>
                                    <p>Add your first department to organize employees.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</main>

@include('components.page-style')

</body>
</html>
