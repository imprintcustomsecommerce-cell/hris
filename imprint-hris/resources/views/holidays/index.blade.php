@extends('layouts.app')

@section('title', 'Holidays | Imprint HRIS')
@section('heading', 'Holidays')

@section('content')
    <div class="page-head">
        <div>
            <h1>Company Holidays</h1>
            <p>Official non-working days for the organization.</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Holiday Calendar</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Date</th><th>Holiday</th><th>Type</th><th></th></tr></thead>
                    <tbody>
                        @forelse($holidays as $h)
                            <tr>
                                <td><strong style="color:var(--text);">{{ date('M d, Y', strtotime($h->date)) }}</strong><br><span style="color:var(--muted);font-size:12px;font-weight:600;">{{ date('l', strtotime($h->date)) }}</span></td>
                                <td>{{ $h->name }}</td>
                                <td><span class="badge {{ $h->type === 'Special' ? 'blue' : 'green' }}">{{ $h->type }}</span></td>
                                <td>
                                    <form action="/holidays/{{ $h->id }}" method="POST" onsubmit="return confirm('Remove this holiday?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4"><p class="muted">No holidays added yet.</p></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Add Holiday</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/holidays" method="POST" style="display:grid; gap:16px;">
                    @csrf
                    <div class="field"><label>Holiday Name</label><input type="text" name="name" value="{{ old('name') }}" placeholder="e.g. Christmas Day" required></div>
                    <div class="field"><label>Date</label><input type="date" name="date" value="{{ old('date') }}" required></div>
                    <div class="field">
                        <label>Type</label>
                        <select name="type" required>
                            <option value="Regular">Regular</option>
                            <option value="Special">Special</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Add Holiday</button>
                </form>
            </div>
        </div>
    </div>
@endsection
