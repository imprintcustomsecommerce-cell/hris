@extends('layouts.app')

@section('title', $applicant->name . ' | Recruitment')
@section('heading', 'Applicant')

@section('content')
    @php $sc = ['Applied'=>'gray','For Interview'=>'amber','For Requirements'=>'amber','Hired'=>'green','Rejected'=>'red']; @endphp

    <div class="page-head">
        <div>
            <h1>{{ $applicant->name }}</h1>
            <p>{{ $applicant->position_applied }} · <span class="badge {{ $sc[$applicant->status] ?? 'gray' }}">{{ $applicant->status }}</span></p>
        </div>
        <div class="head-actions"><a href="/applicants" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="grid-2">
        <div class="card">
            <div class="card-head"><h2>Details</h2></div>
            <div class="card-body">
                <div class="info-row"><span class="k">Email</span><span class="v">{{ $applicant->email ?? '—' }}</span></div>
                <div class="info-row"><span class="k">Phone</span><span class="v">{{ $applicant->phone ?? '—' }}</span></div>
                <div class="info-row"><span class="k">Position</span><span class="v">{{ $applicant->position_applied }}</span></div>
                <div class="info-row"><span class="k">Portal Login</span><span class="v">{{ $account ? $account->email : 'None' }}</span></div>
            </div>
            <div class="card-body" style="padding-top:0;">
                <form action="/applicants/{{ $applicant->id }}/status" method="POST" style="display:flex; gap:8px; align-items:center;">
                    @csrf @method('PATCH')
                    <select name="status" class="select">
                        @foreach(['Applied','For Interview','For Requirements','Hired','Rejected'] as $s)
                            <option value="{{ $s }}" {{ $applicant->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-ghost btn-sm" type="submit">Update Status</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Temporary Login</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/applicants/{{ $applicant->id }}/account" method="POST" style="display:grid; gap:14px;">
                    @csrf
                    <div class="field"><label>Login Email</label><input type="email" name="email" value="{{ old('email', $account->email ?? $applicant->email) }}" required></div>
                    <div class="field"><label>Temporary Password</label><input type="text" name="temp_password" value="apply123" minlength="6" required></div>
                    <button class="btn {{ $account ? 'btn-ghost' : 'btn-primary' }}" type="submit">{{ $account ? 'Reset Login' : 'Create Login' }}</button>
                </form>
            </div>
        </div>
    </div>

    <div class="grid-2" style="margin-top:18px;">
        <div class="card">
            <div class="card-head"><h2>Interviews</h2></div>
            <div class="card-body" style="padding:8px 24px 16px;">
                @forelse($interviews as $iv)
                    <div class="info-row">
                        <span class="k">{{ \Carbon\Carbon::parse($iv->scheduled_at)->format('M d, Y h:i A') }} · {{ $iv->mode }}{{ $iv->location ? ' · '.$iv->location : '' }}</span>
                        <span class="v">
                            <form action="/interviews/{{ $iv->id }}" method="POST" onsubmit="return confirm('Remove?')" style="margin:0;">@csrf @method('DELETE')<button class="btn btn-danger btn-sm" type="submit">Remove</button></form>
                        </span>
                    </div>
                @empty
                    <p class="muted">No interviews scheduled.</p>
                @endforelse

                <form action="/applicants/{{ $applicant->id }}/interviews" method="POST" style="display:grid; gap:12px; margin-top:14px;">
                    @csrf
                    <div class="field"><label>Date & Time</label><input type="datetime-local" name="scheduled_at" required></div>
                    <div class="form-grid">
                        <div class="field"><label>Mode</label><select name="mode"><option>Onsite</option><option>Online</option><option>Phone</option></select></div>
                        <div class="field"><label>Location / Link</label><input type="text" name="location" placeholder="Office or meeting link"></div>
                    </div>
                    <div class="field"><label>Interviewer</label><input type="text" name="interviewer"></div>
                    <button class="btn btn-primary" type="submit">Schedule Interview</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-head"><h2>Submitted Requirements</h2></div>
            <div class="card-body" style="padding:8px 24px 16px;">
                @forelse($documents as $doc)
                    <div class="info-row">
                        <span class="k">{{ $doc->name }}</span>
                        <span class="v"><a href="/applicant-documents/{{ $doc->id }}/download" class="btn btn-ghost btn-sm">Download</a></span>
                    </div>
                @empty
                    <p class="muted">No documents submitted yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    @if($applicant->status !== 'Hired')
        <div class="card" style="margin-top:18px;">
            <div class="card-head"><h2>Hire & Convert to Employee</h2></div>
            <div class="card-body" style="padding:20px 24px 24px;">
                <form action="/applicants/{{ $applicant->id }}/convert" method="POST" class="form-grid" style="align-items:end;">
                    @csrf
                    <div class="field"><label>Employee ID</label><input type="text" name="employee_id" placeholder="EMP-0002" required></div>
                    <div class="field"><label>Department</label><input type="text" name="department" value="{{ $applicant->position_applied }}" required></div>
                    <div class="field"><label>Position</label><input type="text" name="position" value="{{ $applicant->position_applied }}" required></div>
                    <div class="field"><label>Date Hired</label><input type="date" name="date_hired" value="{{ now()->toDateString() }}" required></div>
                    <div class="field">
                        <label>Employment Type</label>
                        <select name="employment_type">
                            <option>Probationary</option><option>Regular</option><option>Contractual</option>
                        </select>
                    </div>
                    <div class="field full"><button class="btn btn-primary" type="submit">Hire — Create Permanent Employee</button>
                        <small style="color:var(--muted);font-weight:500;margin-left:8px;">Their login becomes a regular employee account and documents move to the 201 file.</small>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection
