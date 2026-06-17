@extends('layouts.app')

@section('title', 'Change Password | Imprint HRIS')
@section('heading', 'Change Password')

@section('content')
    <div class="page-head">
        <div>
            <h1>Change Password</h1>
            <p>Keep your account secure with a password only you know.</p>
        </div>
    </div>

    @if(auth()->user()->must_change_password)
        <div class="alert alert-error" style="background:#fef3c7;color:#92400e;border-color:#fde68a;">
            For your security, please change your temporary password before continuing.
        </div>
    @endif

    <div class="card" style="max-width:560px;">
        <div class="card-body" style="padding:28px;">
            <form action="/portal/password" method="POST" style="display:grid; gap:18px;">
                @csrf
                <div class="field">
                    <label>Current Password</label>
                    <input type="password" name="current_password" placeholder="Enter current / temporary password" required autofocus>
                </div>
                <div class="field">
                    <label>New Password</label>
                    <input type="password" name="password" placeholder="At least 8 characters" required>
                </div>
                <div class="field">
                    <label>Confirm New Password</label>
                    <input type="password" name="password_confirmation" placeholder="Re-enter new password" required>
                </div>
                <div class="form-actions" style="margin-top:6px;">
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </div>
            </form>
        </div>
    </div>
@endsection
