@extends('layouts.app')

@section('title', 'Org Chart | Imprint HRIS')
@section('heading', 'Org Chart')

@section('content')
    <div class="page-head">
        <div>
            <h1>Organization Chart</h1>
            <p>Reporting structure built from each employee's manager. {{ $total }} employees.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="padding:28px; overflow-x:auto;">
            @if($roots->count())
                <ul class="org-tree">
                    @foreach($roots as $root)
                        @include('partials.org-node', ['node' => $root, 'children' => $children])
                    @endforeach
                </ul>
            @else
                <p class="muted">No employees to chart yet. Assign managers on employee records to build the tree.</p>
            @endif
        </div>
    </div>

    <style>
        .org-tree, .org-tree ul { list-style: none; margin: 0; padding: 0; }
        .org-tree { display: flex; gap: 28px; }
        .org-tree ul { display: flex; gap: 20px; padding-top: 26px; position: relative; }
        .org-tree li { position: relative; display: flex; flex-direction: column; align-items: center; }

        /* connectors */
        .org-tree li::before {
            content: ''; position: absolute; top: -26px; left: 50%; width: 2px; height: 26px; background: var(--border);
        }
        .org-tree > li::before { display: none; }
        .org-tree ul::before {
            content: ''; position: absolute; top: 0; left: 50%; width: 2px; height: 26px; background: var(--border); transform: translateX(-50%);
        }

        .org-card {
            display: flex; flex-direction: column; align-items: center; text-align: center;
            width: 160px; padding: 16px 14px; gap: 4px;
            background: var(--card); border: 1px solid var(--border); border-radius: 16px;
            text-decoration: none; color: var(--text); box-shadow: var(--shadow); transition: .15s ease;
        }
        .org-card:hover { border-color: var(--btn); transform: translateY(-2px); }
        .org-photo { width: 52px; height: 52px; border-radius: 14px; object-fit: cover; margin-bottom: 6px; }
        .org-initial { background: var(--avatar-bg); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 20px; }
        .org-name { font-weight: 800; font-size: 14px; }
        .org-pos { font-size: 12px; color: var(--muted); }
        .org-dept { font-size: 11px; color: var(--muted); background: var(--accent-soft); padding: 2px 8px; border-radius: 999px; margin-top: 4px; }
    </style>
@endsection
