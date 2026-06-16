@extends('layouts.app')

@section('title', 'Departments | Imprint HRIS')
@section('heading', 'Departments')

@section('content')
    <div class="page-head">
        <div>
            <h1>Departments</h1>
            <p>Organize your company's teams and structure.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'HR']))
            <div class="head-actions">
                <a href="/departments/create" class="btn btn-primary">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><path d="M12 5v14M5 12h14"/></svg>
                    Add Department
                </a>
            </div>
        @endif
    </div>

    <div class="card">
        <div class="card-head"><h2>All Departments</h2></div>
        <div class="table-wrap">
            <table>
                <thead><tr><th>Department</th><th>Description</th><th>Employees</th>@if(in_array(auth()->user()->role, ['Admin', 'HR']))<th></th>@endif</tr></thead>
                <tbody>
                    @forelse($departments ?? [] as $dept)
                        <tr>
                            <td><strong style="color:var(--text);">{{ $dept->name }}</strong></td>
                            <td>{{ $dept->description ?? '—' }}</td>
                            <td><span class="badge blue">{{ $dept->employee_count }}</span></td>
                            @if(in_array(auth()->user()->role, ['Admin', 'HR']))
                                <td>
                                    <div class="actions">
                                        <a href="/departments/{{ $dept->id }}/edit" class="btn btn-ghost btn-sm">Edit</a>
                                        <form action="/departments/{{ $dept->id }}" method="POST" onsubmit="return confirm('Delete this department?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            @endif
                        </tr>
                    @empty
                        <tr><td colspan="4">
                            <div class="empty">
                                <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14"/></svg></div>
                                <h3>No departments yet</h3>
                                <p>Add your first department.</p>
                            </div>
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
