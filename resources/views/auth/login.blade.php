{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — TWB Water Billing</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --bg-main: #1a2035; --bg-card: #202940;
      --gradient-blue: linear-gradient(195deg, #42a5f5, #1976d2);
      --gradient-dark: linear-gradient(195deg, #42424a, #191919);
      --gradient-info: linear-gradient(195deg, #26c6da, #00838f);
      --accent-blue: #1a73e8;
      --text-primary: rgba(255,255,255,0.87);
      --text-secondary: rgba(255,255,255,0.60);
      --text-muted: rgba(255,255,255,0.38);
      --border: rgba(255,255,255,0.09);
    }
    body {
      font-family: 'Roboto', sans-serif;
      background: var(--bg-main);
      color: var(--text-primary);
      min-height: 100vh;
      display: grid;
      grid-template-columns: 1fr 1fr;
      font-size: 14px;
    }

    /* LEFT PANEL */
    .signin-left {
      background: var(--gradient-dark);
      position: relative;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      justify-content: flex-end;
      padding: 60px;
    }
    .signin-left::before {
      content: '';
      position: absolute; inset: 0;
      background:
        radial-gradient(ellipse at 20% 50%, rgba(26,115,232,0.18) 0%, transparent 60%),
        radial-gradient(ellipse at 80% 20%, rgba(38,198,218,0.12) 0%, transparent 60%);
    }

    /* Water drop animation */
    .water-drops { position: absolute; top: 40px; left: 50%; transform: translateX(-50%); }
    .drop {
      width: 80px; height: 80px;
      border-radius: 50% 50% 50% 50% / 60% 60% 40% 40%;
      background: var(--gradient-info);
      opacity: 0.6;
      position: relative;
      animation: float 3s ease-in-out infinite;
      box-shadow: 0 20px 60px rgba(38,198,218,0.3);
    }
    .drop::after {
      content: '';
      position: absolute;
      bottom: -30px; left: 50%; transform: translateX(-50%);
      width: 60px; height: 60px;
      border-radius: 50%;
      background: rgba(38,198,218,0.08);
      filter: blur(10px);
    }
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-12px); }
    }

    .signin-quote { position: relative; z-index: 1; }
    .quote-icon  { font-size: 52px; color: rgba(255,255,255,0.2); margin-bottom: 16px; line-height: 1; }
    .quote-text  { font-size: 22px; font-weight: 300; line-height: 1.6; margin-bottom: 20px; color: rgba(255,255,255,0.8); }
    .quote-sub   { font-size: 14px; color: var(--text-muted); }

    /* RIGHT PANEL */
    .signin-right {
      display: flex; align-items: center; justify-content: center;
      padding: 60px 80px;
      background: var(--bg-main);
    }

    .auth-wrap { width: 100%; max-width: 380px; }

    .form-header { text-align: center; margin-bottom: 36px; }
    .form-logo {
      width: 56px; height: 56px;
      background: var(--gradient-blue);
      border-radius: 14px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 8px 24px rgba(26,115,232,0.4);
    }
    .form-logo .material-icons { font-size: 28px; color: #fff; }
    .form-title { font-size: 24px; font-weight: 700; margin-bottom: 8px; }
    .form-sub   { font-size: 14px; color: var(--text-muted); line-height: 1.5; }

    /* Form Elements */
    .form-group { margin-bottom: 20px; }
    .form-label {
      display: block;
      font-size: 14px; font-weight: 600; letter-spacing: 0.5px;
      color: var(--text-secondary);
      margin-bottom: 8px; text-transform: uppercase;
    }
    .input-wrap { position: relative; }
    .input-icon {
      position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
      color: var(--text-muted);
    }
    .input-icon .material-icons { font-size: 18px; }
    .form-control {
      width: 100%;
      background: rgba(255,255,255,0.06);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 11px 14px 11px 40px;
      color: var(--text-primary);
      font-family: 'Roboto', sans-serif;
      font-size: 14px;
      transition: border-color 0.2s, background 0.2s;
      outline: none;
    }
    .form-control:focus {
      border-color: var(--accent-blue);
      background: rgba(26,115,232,0.06);
    }
    .form-control::placeholder { color: var(--text-muted); }
    .form-control.is-invalid { border-color: #e91e63; }

    .form-error { font-size: 12px; color: #ec407a; margin-top: 4px; }

    .remember-row {
      display: flex; align-items: center; justify-content: space-between;
      margin-bottom: 24px;
    }
    .checkbox-label {
      display: flex; align-items: center; gap: 8px;
      font-size: 13px; color: var(--text-secondary); cursor: pointer;
    }
    .forgot-link { font-size: 13px; color: var(--accent-blue); }

    .btn-login {
      width: 100%;
      padding: 13px;
      background: var(--gradient-blue);
      color: #fff;
      border: none;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 600;
      font-family: 'Roboto', sans-serif;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow: 0 6px 20px rgba(26,115,232,0.35);
      display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .btn-login:hover { filter: brightness(1.1); transform: translateY(-1px); }
    .btn-login .material-icons { font-size: 18px; }

    .divider {
      text-align: center;
      margin: 20px 0;
      position: relative;
      font-size: 12px;
      color: var(--text-muted);
    }
    .divider::before, .divider::after {
      content: '';
      position: absolute; top: 50%;
      width: 40%; height: 1px;
      background: var(--border);
    }
    .divider::before { left: 0; }
    .divider::after  { right: 0; }

    .error-banner {
      background: rgba(233,30,99,0.12);
      border: 1px solid rgba(233,30,99,0.25);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 13px;
      color: #ec407a;
      display: flex; align-items: center; gap: 10px;
      margin-bottom: 24px;
    }
    .error-banner .material-icons { font-size: 18px; }

    @media (max-width: 768px) {
      body { grid-template-columns: 1fr; }
      .signin-left { display: none; }
      .signin-right { padding: 40px 24px; }
    }
  </style>
</head>
<body>

{{-- LEFT: Decorative --}}
<div class="signin-left">
  <div class="water-drops">
    <div class="drop"></div>
  </div>

  <div class="signin-quote">
    <div class="quote-icon">"</div>
    <p class="quote-text">Access to clean water is a human right. Efficient billing management ensures it reaches every household.</p>
    <p class="quote-sub">TWB ONLINE — Managing water, sustaining life..</p>    
  </div>
</div>

{{-- RIGHT: Login Form --}}
<div class="signin-right">
  <div class="auth-wrap">

    <div class="form-header">
      <div>
        <img src="../../twblogo3_transparent.png"></img>
      </div>
      <p class="form-sub">Sign in to your TWB ONLINE account</p>
    </div>

    @if($errors->any())
      <div class="error-banner">
        <span class="material-icons">error</span>
        {{ $errors->first() }}
      </div>
    @endif

    <form action="{{ route('login.post') }}" method="POST" novalidate>
      @csrf

      <div class="form-group">
        <label class="form-label" for="email">Email Address</label>
        <div class="input-wrap">
          <div class="input-icon"><span class="material-icons">email</span></div>
          <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email') }}"
            class="form-control @error('email') is-invalid @enderror"
            placeholder="you@example.com"
            required
            autocomplete="email"
          >
        </div>
        @error('email')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Password</label>
        <div class="input-wrap">
          <div class="input-icon"><span class="material-icons">lock</span></div>
          <input
            type="password"
            id="password"
            name="password"
            class="form-control @error('password') is-invalid @enderror"
            placeholder="Your password"
            required
            autocomplete="current-password"
          >
        </div>
        @error('password')
          <div class="form-error">{{ $message }}</div>
        @enderror
      </div>

      <div class="remember-row">
        <label class="checkbox-label">
          <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
          Remember me
        </label>
        <a href="#" class="forgot-link">Forgot password?</a>
      </div>

      <button type="submit" class="btn-login">
        <span class="material-icons">login</span>
        Sign In
      </button>
    </form>

    <div class="divider">or</div>
    <p style="text-align:center;font-size:14px;color:var(--text-muted);">
      Contact your administrator to create an account.
    </p>

  </div>
</div>

</body>
</html>
