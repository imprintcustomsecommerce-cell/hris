<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Imprint HRIS</title>
</head>

<body>

<main class="login-page">
    <div class="login-card">
        <div class="login-brand">
            <div class="login-logo">
                <img src="{{ asset('logoic.png') }}" alt="Imprint HRIS">
            </div>
            <h1>Imprint HRIS</h1>
            <p>Sign in to access the HR management system.</p>
        </div>

        @if ($errors->any())
            <div class="login-error">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="/login" method="POST" class="login-form">
            @csrf

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="you@imprintcustoms.ph" required autofocus>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" placeholder="Enter your password" required>
            </div>

            <label class="remember">
                <input type="checkbox" name="remember"> Remember me
            </label>

            <button type="submit" class="login-btn">Sign In</button>
        </form>

        <div class="login-hint">
            <strong>Demo accounts</strong> (password: <code>password</code>)<br>
            admin@imprintcustoms.ph · hr@imprintcustoms.ph · employee@imprintcustoms.ph
        </div>
    </div>
</main>

<style>
    * { box-sizing: border-box; }

    body {
        margin: 0;
        font-family: Inter, Arial, sans-serif;
        color: #111827;
    }

    .login-page {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px;
        background:
            radial-gradient(circle at top left, rgba(17, 24, 39, 0.12), transparent 40%),
            radial-gradient(circle at bottom right, rgba(17, 24, 39, 0.08), transparent 40%),
            #f6f7fb;
    }

    .login-card {
        width: 100%;
        max-width: 440px;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 30px;
        padding: 40px;
        box-shadow: 0 30px 80px rgba(17, 24, 39, .12);
    }

    .login-brand {
        text-align: center;
        margin-bottom: 28px;
    }

    .login-logo {
        width: 72px;
        height: 72px;
        margin: 0 auto 18px;
        border-radius: 20px;
        background: white;
        border: 1px solid #e5e7eb;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0,0,0,.08);
    }

    .login-logo img {
        width: 100%;
        height: 100%;
        object-fit: contain;
        padding: 10px;
    }

    .login-brand h1 {
        margin: 0 0 8px;
        font-size: 28px;
        letter-spacing: -0.03em;
    }

    .login-brand p {
        margin: 0;
        color: #6b7280;
        font-size: 14px;
    }

    .login-error {
        background: #fee2e2;
        color: #991b1b;
        border: 1px solid #fecaca;
        padding: 14px 16px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 20px;
    }

    .login-form {
        display: grid;
        gap: 18px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .form-group label {
        font-size: 13px;
        font-weight: 800;
        color: #374151;
    }

    .form-group input {
        width: 100%;
        border: 1px solid #e5e7eb;
        background: #f9fafb;
        padding: 14px 16px;
        border-radius: 14px;
        outline: none;
        font-size: 14px;
        font-weight: 600;
        font-family: inherit;
    }

    .form-group input:focus {
        border-color: #111827;
        background: white;
        box-shadow: 0 0 0 4px rgba(17, 24, 39, .06);
    }

    .remember {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 600;
        color: #4b5563;
    }

    .login-btn {
        border: none;
        background: #111827;
        color: white;
        padding: 15px;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        font-family: inherit;
        box-shadow: 0 16px 35px rgba(17, 24, 39, .18);
    }

    .login-btn:hover { background: #030712; }

    .login-hint {
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #f3f4f6;
        font-size: 12px;
        line-height: 1.7;
        color: #6b7280;
        text-align: center;
    }

    .login-hint code {
        background: #f3f4f6;
        padding: 2px 6px;
        border-radius: 6px;
        font-weight: 700;
    }
</style>

</body>
</html>
