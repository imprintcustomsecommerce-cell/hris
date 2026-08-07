@extends('layouts.app')

@section('title', 'Edit Profile | Imprint HRIS')
@section('heading', 'Edit Profile')

@section('content')
    <div class="page-head">
        <div>
            <h1>Edit My Profile</h1>
            <p>Update your contact details and photo. For other changes, contact HR.</p>
        </div>
        <div class="head-actions"><a href="/portal/profile" class="btn btn-ghost">← Back</a></div>
    </div>

    <div class="card" style="max-width:680px;">
        <div class="card-body" style="padding:28px;">
            <form action="/portal/profile" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div style="display:flex; align-items:center; gap:18px; margin-bottom:24px;">
                    @if($me->photo)
                        <img src="{{ \App\Support\Blob::url($me->photo) }}" alt="" style="width:72px; height:72px; border-radius:20px; object-fit:cover;">
                    @else
                        <div class="avatar" style="width:72px; height:72px; border-radius:20px; font-size:28px;">{{ strtoupper(substr($me->name ?? 'E', 0, 1)) }}</div>
                    @endif
                    <div>
                        <div style="font-weight:800; font-size:16px;">{{ $me->name }}</div>
                        <div style="color:var(--muted); font-size:13px;">{{ $me->position }} · {{ $me->department }}</div>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="field full"><label>Profile Photo</label><input type="file" name="photo" accept="image/*"></div>
                    <div class="field"><label>Contact Number</label><input type="text" name="contact_number" value="{{ old('contact_number', $me->contact_number) }}" placeholder="09xx xxx xxxx"></div>
                    <div class="field"><label>Birthdate</label><input type="date" name="birthdate" value="{{ old('birthdate', $me->birthdate) }}"></div>
                    <div class="field full"><label>Address</label><textarea name="address" rows="3" placeholder="Complete address">{{ old('address', $me->address) }}</textarea></div>
                </div>

                <p class="muted" style="margin-top:16px;">Name, employee ID, department, position and salary are managed by HR.</p>

                <div class="form-actions">
                    <a href="/portal/profile" class="btn btn-ghost">Cancel</a>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
@endsection
