<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Imprint HRIS</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
</head>
<body>

<div class="auth">
    <div class="auth-aside">
        <div class="aside-inner">
            <div class="aside-logo"><img src="{{ asset('logoic.png') }}" alt="Imprint"></div>
            <h2>Imprint Customs<br>HR Information System</h2>
            <p>Employees, attendance, leave, payroll and reports — all in one place.</p>
            <ul class="aside-list">
                <li>Centralized employee records</li>
                <li>Attendance &amp; leave tracking</li>
                <li>Automated payroll &amp; payslips</li>
            </ul>
        </div>
        <span class="orb orb-1"></span>
        <span class="orb orb-2"></span>
    </div>

    <div class="auth-main">
        <div class="auth-card">
            <h1>Welcome back</h1>
            <p class="sub">Sign in to your HRIS account.</p>

            @if ($errors->any())
                <div class="auth-error">{{ $errors->first() }}</div>
            @endif

            <form action="/login" method="POST">
                @csrf
                <div class="field">
                    <label>Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="you@imprintcustoms.ph" required autofocus>
                </div>
                <div class="field">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
                <button type="submit" class="submit">Sign In</button>
            </form>

            <div class="hint">
                <strong>Demo accounts</strong> · password <code>password</code><br>
                admin@imprintcustoms.ph · hr@imprintcustoms.ph · employee@imprintcustoms.ph
            </div>
        </div>
    </div>
</div>

<style>
    :root { --accent: #4f46e5; --accent-dark: #4338ca; --accent-soft: #eef2ff; --text: #0f172a; --muted: #64748b; --border: #e2e8f0; }
    * { box-sizing: border-box; }
    body { margin: 0; font-family: 'Inter', system-ui, Arial, sans-serif; color: var(--text); -webkit-font-smoothing: antialiased; }

    .auth { display: grid; grid-template-columns: 1.05fr 1fr; min-height: 100vh; }

    .auth-aside {
        position: relative; overflow: hidden; color: #fff;
        background: linear-gradient(150deg, #1e1b4b, #4f46e5 70%, #6d28d9);
        padding: 56px; display: flex; align-items: center;
    }
    .aside-inner { position: relative; z-index: 2; max-width: 420px; }
    .aside-logo { width: 60px; height: 60px; border-radius: 16px; background: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; margin-bottom: 30px; }
    .aside-logo img { width: 100%; height: 100%; object-fit: contain; padding: 8px; }
    .auth-aside h2 { font-size: 34px; line-height: 1.15; letter-spacing: -.03em; margin: 0 0 16px; }
    .auth-aside p { color: rgba(255,255,255,.8); font-size: 16px; line-height: 1.6; margin: 0 0 28px; }
    .aside-list { list-style: none; padding: 0; margin: 0; display: grid; gap: 14px; }
    .aside-list li { display: flex; align-items: center; gap: 11px; font-weight: 600; font-size: 15px; color: rgba(255,255,255,.92); }
    .aside-list li::before { content: '✓'; width: 24px; height: 24px; flex-shrink: 0; background: rgba(255,255,255,.2); border-radius: 7px; display: flex; align-items: center; justify-content: center; font-size: 13px; }
    .orb { position: absolute; border-radius: 50%; filter: blur(8px); opacity: .35; }
    .orb-1 { width: 340px; height: 340px; background: #a78bfa; top: -90px; right: -90px; }
    .orb-2 { width: 260px; height: 260px; background: #818cf8; bottom: -80px; left: -60px; }

    .auth-main { display: flex; align-items: center; justify-content: center; padding: 40px; background: #f1f5f9; }
    .auth-card { width: 100%; max-width: 400px; }
    .auth-card h1 { margin: 0 0 6px; font-size: 30px; letter-spacing: -.03em; }
    .sub { margin: 0 0 28px; color: var(--muted); font-size: 15px; }

    .auth-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; padding: 13px 16px; border-radius: 12px; font-size: 14px; font-weight: 600; margin-bottom: 20px; }

    .field { display: flex; flex-direction: column; gap: 7px; margin-bottom: 18px; }
    .field label { font-size: 13px; font-weight: 700; color: #334155; }
    .field input {
        border: 1px solid var(--border); background: #fff; padding: 13px 15px; border-radius: 12px;
        font-size: 14px; font-weight: 500; font-family: inherit; outline: none; transition: .15s ease;
    }
    .field input:focus { border-color: var(--accent); box-shadow: 0 0 0 3px var(--accent-soft); }

    .remember { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: var(--muted); margin-bottom: 22px; }

    .submit {
        width: 100%; border: none; background: var(--accent); color: #fff; padding: 14px; border-radius: 12px;
        font-size: 15px; font-weight: 700; cursor: pointer; font-family: inherit;
        box-shadow: 0 10px 24px rgba(79,70,229,.3); transition: .15s ease;
    }
    .submit:hover { background: var(--accent-dark); }

    .hint { margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--border); font-size: 12px; line-height: 1.7; color: var(--muted); text-align: center; }
    .hint code { background: #e2e8f0; padding: 2px 6px; border-radius: 6px; font-weight: 700; }

    @media (max-width: 860px) {
        .auth { grid-template-columns: 1fr; }
        .auth-aside { display: none; }
    }
</style>

</body>
</html>
