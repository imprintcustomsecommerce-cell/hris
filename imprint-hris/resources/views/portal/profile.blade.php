@extends('layouts.app')

@section('title', 'My Profile | Imprint HRIS')
@section('heading', 'My Profile')

@section('content')
    @unless($me)
        <div class="card"><div class="card-body" style="padding:28px;">
            <p class="muted">Your account isn't linked to an employee record yet. Please contact HR.</p>
        </div></div>
    @else
        <div class="page-head">
            <div class="profile-hero">
                @if($me->photo)
                    <img class="avatar" src="{{ \App\Support\Blob::url($me->photo) }}" alt="" style="object-fit:cover;">
                @else
                    <div class="avatar">{{ strtoupper(substr($me->name ?? 'E', 0, 1)) }}</div>
                @endif
                <div>
                    <h1>{{ $me->name }}</h1>
                    <p>{{ $me->position }} · {{ $me->department }}</p>
                </div>
            </div>
            <div class="head-actions">
                <a href="/portal/profile/edit" class="btn btn-primary">Edit Profile</a>
                <a href="/account/password" class="btn btn-ghost">🔒 Change Password</a>
            </div>
        </div>

        <div class="grid-2">
            <div class="card">
                <div class="card-head"><h2>Personal Information</h2></div>
                <div class="card-body">
                    <div class="info-row"><span class="k">Full Name</span><span class="v">{{ $me->name }}</span></div>
                    <div class="info-row"><span class="k">Email</span><span class="v">{{ $me->email ?? '—' }}</span></div>
                    <div class="info-row"><span class="k">Contact</span><span class="v">{{ $me->contact_number ?? '—' }}</span></div>
                    <div class="info-row"><span class="k">Birthdate</span><span class="v">{{ $me->birthdate ? date('M d, Y', strtotime($me->birthdate)) : '—' }}</span></div>
                    <div class="info-row"><span class="k">Address</span><span class="v">{{ $me->address ?? '—' }}</span></div>
                </div>
            </div>

            <div class="card">
                <div class="card-head"><h2>Employment Details</h2></div>
                <div class="card-body">
                    <div class="info-row"><span class="k">Employee ID</span><span class="v">{{ $me->employee_id }}</span></div>
                    <div class="info-row"><span class="k">Department</span><span class="v">{{ $me->department }}</span></div>
                    <div class="info-row"><span class="k">Position</span><span class="v">{{ $me->position }}</span></div>
                    <div class="info-row"><span class="k">Date Hired</span><span class="v">{{ date('M d, Y', strtotime($me->date_hired)) }}</span></div>
                    <div class="info-row"><span class="k">Type</span><span class="v">{{ $me->employment_type ?? '—' }}</span></div>
                    <div class="info-row"><span class="k">Status</span><span class="v">{{ $me->status }}</span></div>
                </div>
            </div>
        </div>

        <p class="muted" style="margin-top:18px;">Need to update your details? Please contact HR.</p>
    @endunless
@endsection
