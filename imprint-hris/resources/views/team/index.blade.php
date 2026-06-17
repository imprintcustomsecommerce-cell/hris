@extends('layouts.app')

@section('title', 'My Team | Imprint HRIS')
@section('heading', 'My Team')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Team</h1>
            <p>The employees reporting to you.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Team Members</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Employee</th><th>ID</th><th>Department</th><th>Position</th><th>Status</th><th></th></tr></thead>
                <tbody>
                    @forelse($members as $m)
                        @php $sc = ['Active'=>'green','On Leave'=>'amber','Inactive'=>'gray'][$m->status ?? ''] ?? 'gray'; @endphp
                        <tr>
                            <td>
                                <div class="cell-user">
                                    <div class="avatar">{{ strtoupper(substr($m->name ?? 'E', 0, 1)) }}</div>
                                    <div><strong>{{ $m->name }}</strong><span>{{ $m->email ?? '—' }}</span></div>
                                </div>
                            </td>
                            <td>{{ $m->employee_id }}</td>
                            <td>{{ $m->department }}</td>
                            <td>{{ $m->position }}</td>
                            <td><span class="badge {{ $sc }}">{{ $m->status }}</span></td>
                            <td><a href="/tasks" class="btn btn-ghost btn-sm">Assign Task</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/></svg></div>
                                <h3>No team members</h3>
                                <p>Employees assigned to you will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
