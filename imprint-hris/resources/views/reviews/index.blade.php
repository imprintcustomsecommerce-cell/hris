@extends('layouts.app')

@section('title', 'Performance Reviews | Imprint HRIS')
@section('heading', 'Performance Reviews')

@section('content')
    <div class="page-head">
        <div>
            <h1>Performance Reviews</h1>
            <p>Appraise employees per period. Finalize to share with the employee.</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Reviews</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Employee</th><th>Period</th><th>Overall</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @forelse($reviews as $r)
                            @php $avg = round(($r->rating_quality + $r->rating_productivity + $r->rating_teamwork + $r->rating_punctuality) / 4, 1); @endphp
                            <tr>
                                <td><strong style="color:var(--text);">{{ $r->employee_name }}</strong><br><span style="color:var(--muted);font-size:12px;">{{ $r->position }}</span></td>
                                <td>{{ $r->period }}</td>
                                <td><strong>{{ $avg }}</strong> <span style="color:var(--muted);font-size:12px;">/ 5</span></td>
                                <td><span class="badge {{ $r->status === 'Finalized' ? 'green' : 'amber' }}">{{ $r->status }}</span></td>
                                <td><a href="/reviews/{{ $r->id }}" class="btn btn-ghost btn-sm">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty">
                                    <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 0 0 .95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 0 0-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 0 0-1.176 0l-3.976 2.888c-.783.57-1.838-.196-1.538-1.118l1.518-4.674a1 1 0 0 0-.363-1.118L2.05 10.8c-.783-.57-.38-1.81.588-1.81h4.914a1 1 0 0 0 .951-.69z"/></svg></div>
                                    <h3>No reviews yet</h3>
                                    <p>Create your first appraisal.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>New Review</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/reviews" method="POST" style="display:grid; gap:14px;">
                    @csrf
                    <div class="field">
                        <label>Employee</label>
                        <select name="employee_id" required>
                            <option value="">Select employee</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->position }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field"><label>Period</label><input type="text" name="period" placeholder="e.g. 2026 H1" required></div>
                    @foreach(['rating_quality'=>'Quality of Work','rating_productivity'=>'Productivity','rating_teamwork'=>'Teamwork','rating_punctuality'=>'Punctuality'] as $key => $label)
                        <div class="field">
                            <label>{{ $label }}</label>
                            <select name="{{ $key }}" required>
                                @for($i=5;$i>=1;$i--)<option value="{{ $i }}" {{ $i===3?'selected':'' }}>{{ $i }} — {{ ['','Poor','Fair','Good','Very Good','Excellent'][$i] }}</option>@endfor
                            </select>
                        </div>
                    @endforeach
                    <div class="field"><label>Strengths</label><textarea name="strengths" rows="2"></textarea></div>
                    <div class="field"><label>Areas to Improve</label><textarea name="improvements" rows="2"></textarea></div>
                    <div class="field"><label>Comments</label><textarea name="comments" rows="2"></textarea></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Save Draft</button>
                </form>
            </div>
        </div>
    </div>
@endsection
