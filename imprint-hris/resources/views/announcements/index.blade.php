@extends('layouts.app')

@section('title', 'Announcements | Imprint HRIS')
@section('heading', 'Announcements')

@section('content')
    <div class="page-head">
        <div>
            <h1>Announcements</h1>
            <p>Post company-wide memos. Everyone is notified.</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Posted</h2></div>
            <div class="card-body" style="padding:8px 24px 16px;">
                @forelse($announcements as $a)
                    <div style="padding:16px 0; border-bottom:1px solid var(--border);">
                        <div style="display:flex; justify-content:space-between; gap:12px; align-items:flex-start;">
                            <strong style="font-size:15px;">{{ $a->title }}</strong>
                            <form action="/announcements/{{ $a->id }}" method="POST" onsubmit="return confirm('Delete this announcement?')" style="margin:0;">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm" type="submit">Delete</button>
                            </form>
                        </div>
                        <p style="margin:6px 0 0; color:var(--muted); font-size:14px; line-height:1.6;">{{ $a->body }}</p>
                        <span style="display:block; margin-top:8px; color:var(--muted); font-size:12px;">{{ $a->author_name ?? 'HR' }} · {{ \Carbon\Carbon::parse($a->created_at)->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="muted">No announcements yet.</p>
                @endforelse
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>New Announcement</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/announcements" method="POST" style="display:grid; gap:16px;">
                    @csrf
                    <div class="field"><label>Title</label><input type="text" name="title" value="{{ old('title') }}" placeholder="e.g. Holiday schedule" required></div>
                    <div class="field"><label>Message</label><textarea name="body" rows="5" placeholder="Write your announcement...">{{ old('body') }}</textarea></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Post Announcement</button>
                </form>
            </div>
        </div>
    </div>
@endsection
