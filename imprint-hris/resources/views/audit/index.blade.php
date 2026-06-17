@extends('layouts.app')

@section('title', 'Audit Log | Imprint HRIS')
@section('heading', 'Audit Log')

@section('content')
    <div class="page-head">
        <div>
            <h1>Audit Log</h1>
            <p>Recent actions performed across the system.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Activity (last 200)</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>When</th><th>User</th><th>Action</th><th>Details</th></tr></thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('M d, Y h:i A') }}</td>
                            <td>{{ $log->actor_name ?? '—' }}</td>
                            <td><span class="badge gray plain">{{ $log->action }}</span></td>
                            <td>{{ $log->description }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><p class="muted">No activity recorded yet.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
