@extends('layouts.app')

@section('title', 'My Interviews | Imprint HRIS')
@section('heading', 'My Interviews')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Interviews</h1>
            <p>Scheduled interviews for your application.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Schedule</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Date & Time</th><th>Mode</th><th>Where</th><th>Interviewer</th><th>Status</th></tr></thead>
                <tbody>
                    @forelse($interviews as $iv)
                        @php $sc = ['Scheduled'=>'amber','Completed'=>'green','Cancelled'=>'red'][$iv->status] ?? 'gray'; @endphp
                        <tr>
                            <td><strong style="color:var(--text);">{{ \Carbon\Carbon::parse($iv->scheduled_at)->format('M d, Y') }}</strong><br><span style="color:var(--muted);font-size:12px;">{{ \Carbon\Carbon::parse($iv->scheduled_at)->format('h:i A') }}</span></td>
                            <td>{{ $iv->mode }}</td>
                            <td>{{ $iv->location ?? '—' }}</td>
                            <td>{{ $iv->interviewer ?? '—' }}</td>
                            <td><span class="badge {{ $sc }}">{{ $iv->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4M16 2v4M3 10h18M5 4h14a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z"/></svg></div>
                                <h3>No interviews scheduled</h3>
                                <p>HR will schedule your interview here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
