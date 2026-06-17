@extends('layouts.app')

@section('title', 'Review | Imprint HRIS')
@section('heading', 'Performance Review')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $review->employee_name }}</h1>
            <p>{{ $review->position }} · {{ $review->department }} · {{ $review->period }} · <span class="badge {{ $review->status === 'Finalized' ? 'green' : 'amber' }}">{{ $review->status }}</span></p>
        </div>
        <div class="head-actions">
            @if($review->status !== 'Finalized')
                <form action="/reviews/{{ $review->id }}/finalize" method="POST" style="margin:0;">@csrf @method('PATCH')<button class="btn btn-primary" type="submit">Finalize & Share</button></form>
            @endif
            <form action="/reviews/{{ $review->id }}" method="POST" onsubmit="return confirm('Delete this review?')" style="margin:0;">@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Delete</button></form>
            <a href="/reviews" class="btn btn-ghost">← Back</a>
        </div>
    </div>

    @include('partials.review-body', ['review' => $review])

    <p class="muted" style="margin-top:16px;">Reviewed by {{ $review->reviewer_name ?? '—' }}. @if($review->status !== 'Finalized') The employee can't see this until you finalize it. @endif</p>
@endsection
