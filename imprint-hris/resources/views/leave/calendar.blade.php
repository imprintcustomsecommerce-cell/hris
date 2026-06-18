@extends('layouts.app')

@section('title', 'Leave Calendar | Imprint HRIS')
@section('heading', 'Leave Calendar')

@section('content')
    <div class="page-head">
        <div>
            <h1>Leave Calendar</h1>
            <p>Approved leaves and company holidays for {{ $title }}.</p>
        </div>
        <div class="head-actions">
            <a href="/leave-calendar?month={{ $prev }}" class="btn btn-ghost">← Prev</a>
            <a href="/leave-calendar" class="btn btn-ghost">Today</a>
            <a href="/leave-calendar?month={{ $next }}" class="btn btn-ghost">Next →</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:20px;">
            <div class="cal-grid cal-head">
                @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $d)
                    <div class="cal-dow">{{ $d }}</div>
                @endforeach
            </div>
            <div class="cal-grid">
                @foreach($days as $day)
                    <div class="cal-cell {{ $day['inMonth'] ? '' : 'muted-cell' }} {{ $day['isToday'] ? 'today' : '' }}">
                        <div class="cal-date">{{ $day['date']->format('j') }}</div>
                        @if($day['holiday'])
                            <div class="cal-holiday">🏖 {{ $day['holiday'] }}</div>
                        @endif
                        @foreach($day['leaves'] as $lv)
                            <div class="cal-leave" title="{{ $lv->leave_type }}">{{ $lv->employee_name }}</div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <style>
        .cal-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; }
        .cal-head { margin-bottom: 6px; }
        .cal-dow { text-align: center; font-size: 12px; font-weight: 800; color: var(--muted); text-transform: uppercase; letter-spacing: .04em; padding: 6px 0; }
        .cal-cell { min-height: 96px; border: 1px solid var(--border); border-radius: 12px; padding: 8px; background: var(--card); display: flex; flex-direction: column; gap: 4px; }
        .cal-cell.muted-cell { opacity: .45; }
        .cal-cell.today { border-color: var(--btn); box-shadow: 0 0 0 2px var(--btn); }
        .cal-date { font-size: 13px; font-weight: 800; }
        .cal-holiday { font-size: 11px; font-weight: 700; color: #92600a; background: #fef9c3; border-radius: 6px; padding: 2px 6px; }
        [data-theme="dark"] .cal-holiday { color: #fde68a; background: #3a3413; }
        .cal-leave { font-size: 11px; font-weight: 700; color: var(--text); background: var(--accent-soft); border-radius: 6px; padding: 2px 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        @media (max-width: 700px) { .cal-cell { min-height: 70px; } .cal-leave, .cal-holiday { font-size: 10px; } }
    </style>
@endsection
