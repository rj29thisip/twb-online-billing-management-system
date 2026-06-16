<!DOCTYPE html>
<html lang="en" id="htmlRoot">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Forgot Password — TWB Water Billing</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/app.css') }}">
  <script>(function(){var s=localStorage.getItem('twb_theme')||'dark';if(s==='light')document.documentElement.classList.add('light-mode');})();</script>
</head>
<body class="login-page">
<div class="login-container">
  <div class="login-card">
    <div class="login-logo">
      <div class="login-logo-icon"><span class="material-icons">water_drop</span></div>
      <div class="login-logo-text">TWB Billing</div>
    </div>
    <h2 class="login-title">Reset Password</h2>
    <p style="font-size:13px;color:var(--text-secondary);margin-bottom:20px;text-align:center;">
      Enter your email address and we'll send you a reset link.
    </p>

    @if(session('status'))
      <div class="alert alert-success"><span class="material-icons" style="font-size:16px">check_circle</span> {{ session('status') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-error"><span class="material-icons" style="font-size:16px">error</span> {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.password.email') }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Email Address</label>
        <div class="input-wrap">
          <span class="material-icons input-icon">email</span>
          <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="your@email.com">
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">Send Reset Link</button>
    </form>

    <div style="text-align:center;margin-top:16px;">
      <a href="{{ route('login') }}" style="font-size:13px;color:var(--accent-blue);">← Back to Login</a>
    </div>
  </div>
</div>
</body>
</html>
