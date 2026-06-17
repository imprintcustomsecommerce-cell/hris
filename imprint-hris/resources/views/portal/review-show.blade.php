@extends('layouts.app')

@section('title', 'Review | Imprint HRIS')
@section('heading', 'My Review')

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $review->period }}</h1>
            <p>Reviewed by {{ $review->reviewer_name ?? '—' }}</p>
        </div>
        <div class="head-actions"><a href="/portal/reviews" class="btn btn-ghost">← Back</a></div>
    </div>

    @include('partials.review-body', ['review' => $review])
@endsection
