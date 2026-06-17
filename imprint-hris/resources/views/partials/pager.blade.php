@if($p->hasPages())
    <div style="display:flex; align-items:center; justify-content:space-between; gap:12px; padding:16px 24px; border-top:1px solid var(--border); flex-wrap:wrap;">
        <span style="color:var(--muted); font-size:13px; font-weight:600;">
            Showing {{ $p->firstItem() }}–{{ $p->lastItem() }} of {{ $p->total() }}
        </span>
        <div style="display:flex; gap:8px;">
            @if($p->onFirstPage())
                <span class="btn btn-ghost btn-sm" style="opacity:.5; pointer-events:none;">← Prev</span>
            @else
                <a href="{{ $p->previousPageUrl() }}" class="btn btn-ghost btn-sm">← Prev</a>
            @endif
            <span class="btn btn-ghost btn-sm" style="pointer-events:none;">Page {{ $p->currentPage() }} of {{ $p->lastPage() }}</span>
            @if($p->hasMorePages())
                <a href="{{ $p->nextPageUrl() }}" class="btn btn-ghost btn-sm">Next →</a>
            @else
                <span class="btn btn-ghost btn-sm" style="opacity:.5; pointer-events:none;">Next →</span>
            @endif
        </div>
    </div>
@endif
