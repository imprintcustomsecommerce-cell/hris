@extends('layouts.app')

@section('title', 'Projects | Imprint HRIS')
@section('heading', 'Projects')

@section('content')
    <div class="page-head">
        <div>
            <h1>Projects</h1>
            <p>{{ $isExec ? 'Assign projects to managers and track delivery.' : 'Projects assigned to you. Break them into tasks for your team.' }}</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>{{ $isExec ? 'All Projects' : 'My Projects' }}</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Project</th><th>Manager</th><th>Deadline</th><th>Progress</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @php $sc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green']; @endphp
                        @forelse($projects as $p)
                            @php
                                $pr = $progress[$p->id] ?? null;
                                $total = $pr->total ?? 0; $done = $pr->done ?? 0;
                                $pct = $total > 0 ? round($done / $total * 100) : 0;
                                $overdue = $p->deadline && $p->status !== 'Completed' && strtotime($p->deadline) < strtotime(date('Y-m-d'));
                            @endphp
                            <tr>
                                <td><strong style="color:var(--text);">{{ $p->title }}</strong></td>
                                <td>{{ $p->manager_name ?? '—' }}</td>
                                <td>{{ $p->deadline ? date('M d, Y', strtotime($p->deadline)) : '—' }} @if($overdue)<br><span class="badge red">overdue</span>@endif</td>
                                <td>
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <div style="flex:1; min-width:60px; height:7px; background:var(--accent-soft); border-radius:999px; overflow:hidden;"><div style="height:100%; width:{{ $pct }}%; background:var(--accent);"></div></div>
                                        <span style="font-size:12px; font-weight:700; color:var(--muted);">{{ $done }}/{{ $total }}</span>
                                    </div>
                                </td>
                                <td><span class="badge {{ $sc[$p->status] ?? 'gray' }}">{{ $p->status }}</span></td>
                                <td><a href="/projects/{{ $p->id }}" class="btn btn-ghost btn-sm">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6">
                                <div class="empty">
                                    <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
                                    <h3>No projects yet</h3>
                                    <p>{{ $isExec ? 'Create a project to get started.' : 'Projects assigned to you will appear here.' }}</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($isExec)
            <div class="card">
                <div class="card-head"><h2>New Project</h2></div>
                <div class="card-body" style="padding:20px 24px 24px;">
                    <form action="/projects" method="POST" style="display:grid; gap:14px;">
                        @csrf
                        <div class="field"><label>Title</label><input type="text" name="title" value="{{ old('title') }}" required></div>
                        <div class="field"><label>Description</label><textarea name="description" rows="3">{{ old('description') }}</textarea></div>
                        <div class="field">
                            <label>Assign to Manager</label>
                            <select name="manager_id" required>
                                <option value="">Select manager</option>
                                @forelse($managers as $m)
                                    <option value="{{ $m->id }}" {{ (int) old('manager_id') === (int) $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                                @empty
                                    <option value="" disabled>No managers found</option>
                                @endforelse
                            </select>
                        </div>
                        <div class="field"><label>Deadline</label><input type="date" name="deadline" value="{{ old('deadline') }}"></div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">Create & Assign</button>
                    </form>
                </div>
            </div>
        @else
            <div class="card">
                <div class="card-head"><h2>How it works</h2></div>
                <div class="card-body" style="padding:20px 24px 24px; color:var(--muted); font-size:14px; line-height:1.7;">
                    Open a project, add tasks for your team with deadlines, and the progress bar fills as they finish.
                    A project can only be marked <strong>Completed</strong> once every task is done.
                </div>
            </div>
        @endif
    </div>
@endsection
