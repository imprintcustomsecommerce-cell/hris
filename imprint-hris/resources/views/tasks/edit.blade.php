@extends('layouts.app')

@section('title', 'Edit Task | Imprint HRIS')
@section('heading', 'Edit Task')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit Task</h1>
            <p>Update task details, assignment, or status.</p>
        </div>
        <div class="head-actions"><a href="/tasks" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card" style="max-width:640px;">
        <div class="card-body" style="padding:28px;">
            <form action="/tasks/{{ $task->id }}" method="POST" style="display:grid; gap:16px;">
                @csrf @method('PUT')
                <div class="field">
                    <label>Assign To</label>
                    <select name="assigned_to" required>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (int) old('assigned_to', $task->assigned_to) === (int) $emp->id ? 'selected' : '' }}>{{ $emp->name }} ({{ $emp->position }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="field"><label>Title</label><input type="text" name="title" value="{{ old('title', $task->title) }}" required></div>
                <div class="field"><label>Description</label><textarea name="description" rows="3">{{ old('description', $task->description) }}</textarea></div>
                <div class="form-grid">
                    <div class="field">
                        <label>Priority</label>
                        <select name="priority" required>
                            @foreach(['Low','Medium','High'] as $p)
                                <option value="{{ $p }}" {{ old('priority', $task->priority) === $p ? 'selected' : '' }}>{{ $p }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Status</label>
                        <select name="status" required>
                            @foreach(['Pending','In Progress','Completed'] as $s)
                                <option value="{{ $s }}" {{ old('status', $task->status) === $s ? 'selected' : '' }}>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="field"><label>Due Date</label><input type="date" name="due_date" value="{{ old('due_date', $task->due_date) }}"></div>
                <div class="form-actions">
                    <a href="/tasks" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Update Task</button>
                </div>
            </form>
        </div>
    </div>
@endsection
