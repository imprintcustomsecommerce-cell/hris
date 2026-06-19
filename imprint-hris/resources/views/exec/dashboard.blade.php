@extends('layouts.app')

@section('title', 'Executive Dashboard | Imprint HRIS')
@section('heading', 'Executive Dashboard')

@section('content')
    <div class="page-head">
        <div>
            <h1>Executive Overview</h1>
            <p>Company-wide snapshot for {{ now()->format('F d, Y') }}.</p>
        </div>
    </div>

    <div class="stat-grid">
        <div class="stat">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 20h5v-2a4 4 0 0 0-3-3.87M9 20H4v-2a4 4 0 0 1 3-3.87m6-1.13a4 4 0 1 0-4-4 4 4 0 0 0 4 4z"/></svg></div>
            <div class="stat-label">Workforce</div>
            <div class="stat-value">{{ $stats['employees'] }}</div>
            <div class="stat-sub">{{ $stats['active'] }} active · {{ $stats['departments'] }} departments</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8V7m0 9v1M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"/></svg></div>
            <div class="stat-label">Net Pay Disbursed</div>
            <div class="stat-value">₱{{ number_format($stats['netPaid'], 0) }}</div>
            <div class="stat-sub">{{ $stats['pendingLeaves'] }} leave(s) pending</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
            <div class="stat-label">Active Projects</div>
            <div class="stat-value">{{ $stats['projInProgress'] }}</div>
            <div class="stat-sub">{{ $stats['projCompleted'] }} completed · {{ $stats['projTotal'] }} total</div>
        </div>
        <div class="stat">
            <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4m0 4h.01M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg></div>
            <div class="stat-label">Overdue Projects</div>
            <div class="stat-value">{{ $stats['projOverdue'] }}</div>
            <div class="stat-sub">past deadline, not completed</div>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><div><h2>Recent Projects</h2></div><a href="/projects" class="btn btn-ghost btn-sm">All projects</a></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Project</th><th>Manager</th><th>Progress</th><th>Status</th></tr></thead>
                    <tbody>
                        @php $sc = ['Pending'=>'gray','In Progress'=>'amber','Completed'=>'green']; @endphp
                        @forelse($recentProjects as $p)
                            @php $pr = $progress[$p->id] ?? null; $t = $pr->total ?? 0; $d = $pr->done ?? 0; $pct = $t ? round($d/$t*100) : 0; @endphp
                            <tr>
                                <td><a href="/projects/{{ $p->id }}" style="text-decoration:none;color:var(--text);font-weight:700;">{{ $p->title }}</a></td>
                                <td>{{ $p->manager_name ?? '—' }}</td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px;">
                                        <div style="flex:1;min-width:50px;height:6px;background:var(--accent-soft);border-radius:999px;overflow:hidden;"><div style="height:100%;width:{{ $pct }}%;background:var(--accent);"></div></div>
                                        <span style="font-size:12px;font-weight:700;color:var(--muted);">{{ $d }}/{{ $t }}</span>
                                    </div>
                                </td>
                                <td><span class="badge {{ $sc[$p->status] ?? 'gray' }}">{{ $p->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="muted">No projects yet.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><div><h2>Headcount by Department</h2></div></div>
            <div class="card-body" style="padding-bottom:20px;">
                @php $maxHead = max(1, optional($headcountByDept->first())->total ?? 1); @endphp
                @forelse($headcountByDept as $row)
                    <div style="margin:12px 0;">
                        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:600;margin-bottom:5px;"><span>{{ $row->department ?? 'Unassigned' }}</span><span>{{ $row->total }}</span></div>
                        <div style="height:8px;background:var(--accent-soft);border-radius:999px;overflow:hidden;"><div style="height:100%;width:{{ round($row->total / $maxHead * 100) }}%;background:var(--accent);border-radius:999px;"></div></div>
                    </div>
                @empty
                    <p class="muted">No data.</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
