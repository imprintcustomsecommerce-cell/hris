@extends('layouts.app')

@section('title', 'Settings | Imprint HRIS')
@section('heading', 'Settings')

@section('content')
    <div class="page-head">
        <div>
            <h1>System Settings</h1>
            <p>Company info, work schedule and default leave policy.</p>
        </div>
    </div>

    <div class="card" style="max-width:720px;">
        <div class="card-body" style="padding:28px;">
            <form action="/settings" method="POST">
                @csrf

                <div class="form-section">
                    <h3>Company</h3>
                    <div class="form-grid">
                        <div class="field full"><label>Company Name</label><input type="text" name="company_name" value="{{ old('company_name', $settings['company_name'] ?? '') }}" required></div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Work Schedule</h3>
                    <div class="form-grid">
                        <div class="field"><label>Shift Start</label><input type="time" name="shift_start" value="{{ old('shift_start', $settings['shift_start'] ?? '08:00') }}" required></div>
                        <div class="field"><label>Shift End</label><input type="time" name="shift_end" value="{{ old('shift_end', $settings['shift_end'] ?? '17:00') }}" required></div>
                        <div class="field"><label>Grace Period (minutes)</label><input type="number" name="grace_minutes" min="0" max="240" value="{{ old('grace_minutes', $settings['grace_minutes'] ?? '15') }}" required></div>
                    </div>
                    <p class="muted" style="margin-top:12px;">Clock-ins after start + grace are marked Late; minutes of late/undertime/overtime are computed automatically.</p>
                </div>

                <div class="form-section">
                    <h3>Default Leave Credits</h3>
                    <div class="form-grid">
                        <div class="field"><label>Vacation days / year</label><input type="number" name="default_vacation_credits" min="0" max="365" value="{{ old('default_vacation_credits', $settings['default_vacation_credits'] ?? '15') }}" required></div>
                        <div class="field"><label>Sick days / year</label><input type="number" name="default_sick_credits" min="0" max="365" value="{{ old('default_sick_credits', $settings['default_sick_credits'] ?? '15') }}" required></div>
                    </div>
                    <p class="muted" style="margin-top:12px;">Used to pre-fill new employee records.</p>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
@endsection
