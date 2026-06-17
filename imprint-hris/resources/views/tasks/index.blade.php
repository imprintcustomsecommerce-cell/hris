@extends('layouts.app')

@section('title', 'Tasks | Imprint HRIS')
@section('heading', 'Tasks')

@section('content')
    <div class="page-head">
        <div>
            <h1>Tasks</h1>
            <p>Assign tasks to employees and track their progress.</p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat"><div class="stat-label">Total Tasks</div><div class="stat-value">{{ $totalTasks }}</div></div>
        <div class="stat"><div class="stat-label">Pending</div><div class="stat-value">{{ $pendingTasks }}</div></div>
        <div class="stat"><div class="stat-label">In Progress</div><div class="stat-value">{{ $inProgressTasks }}</div></div>
        <div class="stat"><div class="stat-label">Completed</div><div class="stat-value">{{ $completedTasks }}</div></div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>All Tasks</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Task</th><th>Assigned To</th><th>Priority</th><th>Due</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($tasks as $task)
                            @php
                                $sc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green'][$task->status] ?? 'gray';
                                $pc = ['Low'=>'blue','Medium'=>'amber','High'=>'red'][$task->priority] ?? 'gray';
                            @endphp
                            <tr>
                                <td>
                                    <strong style="color:var(--text);">{{ $task->title }}</strong>
                                    @if($task->description)<br><span style="color:var(--muted);font-weight:500;font-size:13px;">{{ Str::limit($task->description, 60) }}</span>@endif
                                </td>
                                <td>{{ $task->employee_name }}</td>
                                <td><span class="badge {{ $pc }}">{{ $task->priority }}</span></td>
                                @php $overdue = $task->due_date && $task->status !== 'Completed' && strtotime($task->due_date) < strtotime(date('Y-m-d')); @endphp
                                <td>
                                    {{ $task->due_date ? date('M d, Y', strtotime($task->due_date)) : '—' }}
                                    @if($overdue)<br><span class="badge red">overdue</span>@endif
                                </td>
                                <td><span class="badge {{ $sc }}">{{ $task->status }}</span></td>
                                <td>
                                    <div class="actions">
                                        <a href="/tasks/{{ $task->id }}/edit" class="btn btn-ghost btn-sm">Edit</a>
                                        <form action="/tasks/{{ $task->id }}" method="POST" onsubmit="return confirm('Delete this task?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6">
                                <div class="empty">
                                    <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                                    <h3>No tasks yet</h3>
                                    <p>Assign your first task using the form.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Assign a Task</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/tasks" method="POST" style="display:grid; gap:16px;">
                    @csrf
                    <div class="field">
                        <label>Assign To</label>
                        <select name="assigned_to" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}" {{ (int) old('assigned_to') === (int) $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->position }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Title</label><input type="text" name="title" value="{{ old('title') }}" placeholder="Task title" required></div>
                    <div class="field"><label>Description</label><textarea name="description" rows="3" placeholder="Details / instructions">{{ old('description') }}</textarea></div>
                    <div class="field">
                        <label>Priority</label>
                        <select name="priority" required>
                            @foreach(['Low','Medium','High'] as $p)
                                <option value="{{ $p }}" {{ old('priority','Medium') === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date') }}"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Assign Task</button>
                </form>
            </div>
        </div>
    </div>
@endsection
