@extends('layouts.app')

@section('title', 'My Tasks | Imprint HRIS')
@section('heading', 'My Tasks')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Tasks</h1>
            <p>Tasks assigned to you. Update your progress as you go.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Assigned Tasks</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Task</th><th>From</th><th>Priority</th><th>Due</th><th>Status</th><th>Update</th></tr></thead>
                <tbody>
                    @forelse($tasks as $task)
                        @php
                            $sc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green'][$task->status] ?? 'gray';
                            $pc = ['Low'=>'blue','Medium'=>'amber','High'=>'red'][$task->priority] ?? 'gray';
                        @endphp
                        <tr>
                            <td>
                                <strong style="color:var(--text);">{{ $task->title }}</strong>
                                @if($task->description)<br><span style="color:var(--muted);font-weight:500;font-size:13px;">{{ $task->description }}</span>@endif
                            </td>
                            <td>{{ $task->assigner_name ?? '—' }}</td>
                            <td><span class="badge {{ $pc }}">{{ $task->priority }}</span></td>
                            <td>{{ $task->due_date ? date('M d, Y', strtotime($task->due_date)) : '—' }}</td>
                            <td><span class="badge {{ $sc }}">{{ $task->status }}</span></td>
                            <td>
                                <form action="/portal/tasks/{{ $task->id }}/status" method="POST" class="actions">
                                    @csrf @method('PATCH')
                                    <select name="status" onchange="this.form.submit()" style="border:1px solid var(--border);background:#f8fafc;border-radius:10px;padding:8px 10px;font-size:13px;font-weight:600;font-family:inherit;">
                                        @foreach(['Pending','In Progress','Completed'] as $st)
                                            <option value="{{ $st }}" {{ $task->status === $st ? 'selected' : '' }}>{{ $st }}</option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                                <h3>No tasks assigned</h3>
                                <p>Tasks from your manager or HR will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
