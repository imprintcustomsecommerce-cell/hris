@extends('layouts.app')

@section('title', 'Recruitment | Imprint HRIS')
@section('heading', 'Recruitment')

@section('content')
    <div class="page-head">
        <div>
            <h1>Applicants</h1>
            <p>Track applicants, schedule interviews, collect requirements, then hire.</p>
        </div>
    </div>

    <div class="grid-2 lean">
        <div class="card">
            <div class="card-head"><h2>Applicant Pipeline</h2></div>
            <div class="table-wrap">
                <table>
                    <thead><tr><th>Name</th><th>Position</th><th>Status</th><th></th></tr></thead>
                    <tbody>
                        @php $sc = ['Applied'=>'gray','For Interview'=>'amber','For Requirements'=>'amber','Hired'=>'green','Rejected'=>'red']; @endphp
                        @forelse($applicants as $a)
                            <tr>
                                <td><strong style="color:var(--text);">{{ $a->name }}</strong><br><span style="color:var(--muted);font-size:12px;">{{ $a->email ?? $a->phone ?? '—' }}</span></td>
                                <td>{{ $a->position_applied }}</td>
                                <td><span class="badge {{ $sc[$a->status] ?? 'gray' }}">{{ $a->status }}</span></td>
                                <td><a href="/applicants/{{ $a->id }}" class="btn btn-ghost btn-sm">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="4">
                                <div class="empty">
                                    <div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2M9 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8z"/></svg></div>
                                    <h3>No applicants yet</h3>
                                    <p>Add an applicant to start the hiring pipeline.</p>
                                </div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Add Applicant</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/applicants" method="POST" style="display:grid; gap:14px;">
                    @csrf
                    <div class="field"><label>Full Name</label><input type="text" name="name" value="{{ old('name') }}" required></div>
                    <div class="field"><label>Position Applied</label><input type="text" name="position_applied" value="{{ old('position_applied') }}" required></div>
                    <div class="field"><label>Email</label><input type="email" name="email" value="{{ old('email') }}"></div>
                    <div class="field"><label>Phone</label><input type="text" name="phone" value="{{ old('phone') }}"></div>
                    <label class="acct-toggle" style="display:flex; align-items:center; gap:10px; font-size:14px; font-weight:600; color:#334155; cursor:pointer;">
                        <input type="checkbox" name="create_account" value="1" id="acct" style="width:18px;height:18px;" {{ old('create_account') ? 'checked' : '' }}> Create a temporary portal login
                    </label>
                    <div class="field" id="acctpw" style="display:none;">
                        <label>Temporary Password</label>
                        <input type="text" name="temp_password" value="{{ old('temp_password', 'apply123') }}" minlength="6">
                        <small style="color:var(--muted);font-weight:500;">Login uses the email above. They must change it on first sign-in.</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Add Applicant</button>
                </form>
                <script>
                    (function(){ const c=document.getElementById('acct'), f=document.getElementById('acctpw');
                    const s=()=>f.style.display=c.checked?'':'none'; c.addEventListener('change',s); s(); })();
                </script>
            </div>
        </div>
    </div>
@endsection
