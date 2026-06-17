@extends('layouts.app')

@section('title', 'My Reviews | Imprint HRIS')
@section('heading', 'My Reviews')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Performance Reviews</h1>
            <p>Appraisals shared with you by your manager or HR.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Reviews</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Period</th><th>Overall</th><th>Reviewer</th><th></th></tr></thead>
                <tbody>
                    @forelse($reviews as $r)
                        @php $avg = round(($r->rating_quality + $r->rating_productivity + $r->rating_teamwork + $r->rating_punctuality) / 4, 1); @endphp
                        <tr>
                            <td><strong style="color:var(--text);">{{ $r->period }}</strong></td>
                            <td><strong>{{ $avg }}</strong> <span style="color:var(--muted);font-size:12px;">/ 5</span></td>
                            <td>{{ $r->reviewer_name ?? '—' }}</td>
                            <td><a href="/portal/reviews/{{ $r->id }}" class="btn btn-ghost btn-sm">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.05 10.8c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z"/></svg></div>
                                <h3>No reviews yet</h3>
                                <p>Your finalized appraisals will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
