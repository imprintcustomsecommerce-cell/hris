@php
    $avg = round(($review->rating_quality + $review->rating_productivity + $review->rating_teamwork + $review->rating_punctuality) / 4, 1);
    $rows = ['Quality of Work'=>$review->rating_quality,'Productivity'=>$review->rating_productivity,'Teamwork'=>$review->rating_teamwork,'Punctuality'=>$review->rating_punctuality];
@endphp

<div class="grid-2">
    <div class="card">
        <div class="card-head"><h2>Ratings</h2></div>
        <div class="card-body">
            @foreach($rows as $label => $val)
                <div class="info-row">
                    <span class="k">{{ $label }}</span>
                    <span class="v">
                        @for($i=1;$i<=5;$i++)<span style="color:{{ $i <= $val ? 'var(--btn)' : 'var(--border)' }};">★</span>@endfor
                        <span style="color:var(--muted); font-weight:600; margin-left:6px;">{{ $val }}/5</span>
                    </span>
                </div>
            @endforeach
            <div class="info-row"><span class="k">Overall</span><span class="v" style="font-size:18px;">{{ $avg }} / 5</span></div>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Notes</h2></div>
        <div class="card-body" style="padding-bottom:20px;">
            <div style="margin-bottom:14px;"><div style="font-weight:700; font-size:13px; margin-bottom:4px;">Strengths</div><div style="color:var(--text-soft); font-size:14px; line-height:1.6;">{{ $review->strengths ?: '—' }}</div></div>
            <div style="margin-bottom:14px;"><div style="font-weight:700; font-size:13px; margin-bottom:4px;">Areas to Improve</div><div style="color:var(--text-soft); font-size:14px; line-height:1.6;">{{ $review->improvements ?: '—' }}</div></div>
            <div><div style="font-weight:700; font-size:13px; margin-bottom:4px;">Comments</div><div style="color:var(--text-soft); font-size:14px; line-height:1.6;">{{ $review->comments ?: '—' }}</div></div>
        </div>
    </div>
</div>
