@extends('layouts.app')

@section('title', 'My Documents | Imprint HRIS')
@section('heading', 'My Documents')

@section('content')
    <div class="page-head">
        <div>
            <h1>My Documents</h1>
            <p>Files shared with you by HR.</p>
        </div>
    </div>

    <div class="card">
        <div class="card-head"><h2>Documents</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Name</th><th>Category</th><th>Uploaded</th><th></th></tr></thead>
                <tbody>
                    @forelse($documents as $doc)
                        <tr>
                            <td><strong style="color:var(--text);">{{ $doc->name }}</strong></td>
                            <td><span class="badge gray">{{ $doc->category }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($doc->created_at)->format('M d, Y') }}</td>
                            <td><a href="/portal/documents/{{ $doc->id }}/download" class="btn btn-ghost btn-sm">Download</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6zM14 2v6h6"/></svg></div>
                                <h3>No documents</h3>
                                <p>Documents shared by HR will appear here.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
