<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | Imprint HRIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth-wrap">
    <div class="auth-card">
        <div class="brand">
            <div class="brand-logo"><img src="{{ asset('logoic.png') }}" alt="Imprint"></div>
            <h1>Forgot password?</h1>
            <p class="sub">Enter your email and we'll send you a reset link.</p>
        </div>

        @if (session('success'))
            <div class="ok">{{ session('success') }}</div>
        @endif
        @if ($errors->any())
            <div class="err">{{ $errors->first() }}</div>
        @endif

        <form action="/forgot-password" method="POST">
            @csrf
            <div class="field">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@imprintcustoms.ph" required autofocus>
            </div>
            <button type="submit" class="submit">Send Reset Link</button>
        </form>

        <a class="back" href="/login">← Back to sign in</a>
    </div>
</div>

<style>
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', system-ui, Arial, sans-serif; color: #0a0a0a; background: #f4f4f5; }
    .auth-wrap { min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 32px; }
    .auth-card { width: 100%; max-width: 400px; background: #fff; border: 1px solid #e4e4e7; border-radius: 24px; padding: 36px; box-shadow: 0 30px 80px rgba(0,0,0,.10); }
    .brand { text-align: center; margin-bottom: 24px; }
    .brand-logo { width: 60px; height: 60px; margin: 0 auto 18px; border-radius: 16px; background: #fff; border: 1px solid #e4e4e7; display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .brand-logo img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }
    .brand h1 { margin: 0 0 6px; font-size: 24px; letter-spacing: -.02em; }
    .sub { margin: 0; color: #6b7280; font-size: 14px; }
    .ok { background: #f4f4f5; color: #0a0a0a; border: 1px solid #e4e4e7; border-left: 4px solid #facc15; padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 18px; }
    .err { background: #f4f4f5; color: #0a0a0a; border: 1px solid #e4e4e7; border-left: 4px solid #0a0a0a; padding: 12px 14px; border-radius: 12px; font-size: 13px; font-weight: 600; margin-bottom: 18px; }
    .field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 18px; }
    .field label { font-size: 13px; font-weight: 700; color: #3f3f46; }
    .field input { border: 1px solid #e4e4e7; background: #fff; padding: 13px 15px; border-radius: 12px; font-size: 14px; font-weight: 500; font-family: inherit; outline: none; }
    .field input:focus { border-color: #facc15; box-shadow: 0 0 0 3px #fef9c3; }
    .submit { width: 100%; border: none; background: #facc15; color: #1a1a1a; padding: 14px; border-radius: 12px; font-size: 15px; font-weight: 800; cursor: pointer; font-family: inherit; box-shadow: 0 10px 24px rgba(250,204,21,.35); }
    .submit:hover { background: #eab308; }
    .back { display: block; text-align: center; margin-top: 20px; color: #6b7280; font-size: 13px; font-weight: 600; text-decoration: none; }
    .back:hover { color: #0a0a0a; }
</style>

</body>
</html>
