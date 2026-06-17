@extends('layouts.app')

@section('title', 'My Requirements | Imprint HRIS')
@section('heading', 'My Requirements')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Requirements</h1>
            <p>Upload the documents HR asked for (IDs, resume, clearances, etc.).</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Submitted</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Submitted</th><th></th></tr></thead>
                    <tbody>
                        @forelse($documents as $doc)
                            <tr>
                                <td><strong style="color:var(--text);">{{ $doc->name }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($doc->created_at)->format('M d, Y') }}</td>
                                <td><a href="/apply/documents/{{ $doc->id }}/download" class="btn btn-ghost btn-sm">View</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="3"><p class="muted">Nothing submitted yet.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Submit a Document</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                @if($me)
                    <form action="/apply/documents" method="POST" enctype="multipart/form-data" style="display:grid; gap:16px;">
                        @csrf
                        <div class="field"><label>Document Name</label><input type="text" name="name" placeholder="e.g. Resume, NBI Clearance" required></div>
                        <div class="field"><label>File</label><input type="file" name="document" required></div>
                        <button type="submit" class="btn btn-primary" style="width:100%;">Upload</button>
                    </form>
                @else
                    <p class="muted">Your application isn't linked. Contact HR.</p>
                @endif
            </div>
        </div>
    </div>
@endsection
