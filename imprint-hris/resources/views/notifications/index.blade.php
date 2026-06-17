@extends('layouts.app')

@section('title', 'Notifications | Imprint HRIS')
@section('heading', 'Notifications')

@section('content')
    <div class="page-head">
        <div>
            <h1>Notifications</h1>
            <p>Your latest updates and alerts.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:8px 24px;">
            @forelse($notifications as $n)
                <a href="{{ $n->url ?? '#' }}" class="list-row">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <span style="width:38px;height:38px;border-radius:11px;background:var(--accent-soft);color:var(--accent);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:18px;height:18px;"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                        </span>
                        <div>
                            <span class="lr-title">{{ $n->message }}</span>
                            <span class="lr-sub">{{ \Carbon\Carbon::parse($n->created_at)->diffForHumans() }}</span>
                        </div>
                    </div>
                    @if(is_null($n->read_at))<span class="badge amber">new</span>@endif
                </a>
            @empty
                <div class="empty">
                    <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg></div>
                    <h3>No notifications</h3>
                    <p>You're all caught up.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection
