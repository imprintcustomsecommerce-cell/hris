@extends('layouts.app')

@section('title', 'Application | Imprint HRIS')
@section('heading', 'My Application')

@section('content')
    <div class="page-head">
        <div>
            <h1>Welcome{{ $me ? ', ' . explode(' ', $me->name)[0] : '' }} 👋</h1>
            <p>Track your application, submit requirements, and view interview schedules.</p>
        </div>
    </div>

    @unless($me)
        <div class="card"><div class="card-body" style="padding:28px;"><p class="muted">Your application record isn't linked yet. Please contact HR.</p></div></div>
    @else
        @php $sc = ['Applied'=>'gray','For Interview'=>'amber','For Requirements'=>'amber','Hired'=>'green','Rejected'=>'red']; @endphp
        <div class="stat-grid" style="grid-template-columns:repeat(3,1fr);">
            <div class="stat"><div class="stat-label">Application Status</div><div class="stat-value" style="font-size:20px;"><span class="badge {{ $sc[$me->status] ?? 'gray' }}">{{ $me->status }}</span></div><div class="stat-sub">{{ $me->position_applied }}</div></div>
            <a href="/apply/documents" class="stat" style="text-decoration:none;color:inherit;"><div class="stat-label">Requirements Submitted</div><div class="stat-value">{{ $docCount }}</div><div class="stat-sub">Tap to upload more</div></a>
            <a href="/apply/interviews" class="stat" style="text-decoration:none;color:inherit;">
                <div class="stat-label">Next Interview</div>
                <div class="stat-value" style="font-size:18px;">{{ $nextInterview ? \Carbon\Carbon::parse($nextInterview->scheduled_at)->format('M d, h:i A') : '—' }}</div>
                <div class="stat-sub">{{ $nextInterview->mode ?? 'Not scheduled' }}</div>
            </a>
        </div>

        <div class="grid-2 lean">
            <div class="card">
                <div class="card-head"><div><h2>Next Steps</h2></div></div>
                <div class="card-body" style="padding:20px 24px 24px; display:grid; gap:10px;">
                    <a href="/apply/documents" class="btn btn-primary" style="width:100%;">Submit Requirements</a>
                    <a href="/apply/interviews" class="btn btn-ghost" style="width:100%;">View Interviews</a>
                    <a href="/account/password" class="btn btn-ghost" style="width:100%;">Change Password</a>
                </div>
            </div>
            <div class="card">
                <div class="card-head"><div><h2>Upcoming Interview</h2></div></div>
                <div class="card-body">
                    @if($nextInterview)
                        <div class="info-row"><span class="k">Date</span><span class="v">{{ \Carbon\Carbon::parse($nextInterview->scheduled_at)->format('M d, Y h:i A') }}</span></div>
                        <div class="info-row"><span class="k">Mode</span><span class="v">{{ $nextInterview->mode }}</span></div>
                        <div class="info-row"><span class="k">Where</span><span class="v">{{ $nextInterview->location ?? '—' }}</span></div>
                    @else
                        <p class="muted">No interview scheduled yet.</p>
                    @endif
                </div>
            </div>
        </div>
    @endunless
@endsection
