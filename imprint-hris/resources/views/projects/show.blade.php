@extends('layouts.app')

@section('title', $project->title . ' | Projects')
@section('heading', 'Project')

@section('content')
    @php
        $sc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green'];
        $total = $tasks->count();
        $pct = $total > 0 ? round($doneCount / $total * 100) : 0;
        $allDone = $total > 0 && $doneCount === $total;
    @endphp

    <div class="page-head">
        <div>
            <h1>{{ $project->title }}</h1>
            <p>
                Manager: {{ $project->manager_name ?? '—' }} ·
                Deadline: {{ $project->deadline ? date('M d, Y', strtotime($project->deadline)) : 'None' }} ·
                <span class="badge {{ $sc[$project->status] ?? 'gray' }}">{{ $project->status }}</span>
            </p>
        </div>
        <div class="head-actions">
            @if($canManage && $project->status !== 'Completed')
                <form action="/projects/{{ $project->id }}/complete" method="POST" style="margin:0;">
                    @csrf @method('PATCH')
                    <button type="submit" class="btn btn-primary" {{ $allDone ? '' : 'disabled title=All tasks must be completed first' }}>Mark Completed</button>
                </form>
            @endif
            <a href="/projects" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    @if($project->description)
        <div class="card" style="margin-bottom:18px;"><div class="card-body" style="padding:20px 24px;"><p style="margin:0; color:var(--text-soft); line-height:1.7;">{{ $project->description }}</p></div></div>
    @endif

    <div class="card" style="margin-bottom:18px;">
        <div class="card-body" style="padding:20px 24px;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="flex:1; height:10px; background:var(--accent-soft); border-radius:999px; overflow:hidden;"><div style="height:100%; width:{{ $pct }}%; background:var(--accent);"></div></div>
                <strong style="white-space:nowrap;">{{ $doneCount }}/{{ $total }} done ({{ $pct }}%)</strong>
            </div>
            @unless($allDone)
                <p class="muted" style="margin:12px 0 0;">The project can be completed once all tasks are done.</p>
            @endunless
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Tasks</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Task</th><th>Assignee</th><th>Due</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($tasks as $t)
                            @php
                                $tsc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green'][$t->status] ?? 'gray';
                                $od = $t->due_date && $t->status !== 'Completed' && strtotime($t->due_date) < strtotime(date('Y-m-d'));
                            @endphp
                            <tr>
                                <td><strong style="color:var(--text);">{{ $t->title }}</strong></td>
                                <td>{{ $t->employee_name }}</td>
                                <td>{{ $t->due_date ? date('M d, Y', strtotime($t->due_date)) : '—' }} @if($od)<br><span class="badge red">overdue</span>@endif</td>
                                <td><span class="badge {{ $tsc }}">{{ $t->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="muted">No tasks yet. Add the first one.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($canManage && $project->status !== 'Completed')
            <div class="card">
                <div class="card-head"><h2>Add Task</h2></div>
                <div class="card-body" style="padding:20px 24px 24px;">
                    <form action="/projects/{{ $project->id }}/tasks" method="POST" style="display:grid; gap:14px;">
                        @csrf
                        <div class="field">
                            <label>Assign To</label>
                            <select name="assigned_to" required>
                                <option value="">Select team member</option>
                                @forelse($team as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->position }})</option>
                                @empty
                                    <option value="" disabled>No team members</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="field"><label>Title</label><input type="text" name="title" required></div>
                        <div class="field"><label>Description</label><textarea name="description" rows="2"></textarea></div>
                        <div class="form-grid">
                            <div class="field"><label>Priority</label><select name="priority"><option>Low</option><option selected>Medium</option><option>High</option></select></div>
                            <div class="field"><label>Deadline</label><input type="date" name="due_date"></div>
                        </div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">Add Task</button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endsection
