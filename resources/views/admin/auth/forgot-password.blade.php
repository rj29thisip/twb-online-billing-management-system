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
<body class="login-page">
  <div style="max-width:400px;margin:0 auto;">
    <div class="login-container">
      <div class="login-card">

        <div class="brand-header" style="display:flex;align-items:center;justify-content:center;gap:10px;margin-top:46px;margin-bottom:46px">

          <div class="brand-logo">
            <span class="material-icons" style="color:#fff;font-size:25px">water_drop</span>
          </div>

          <div class="brand-text-group" style="display:flex;flex-direction:column;align-items:flex-start;">
            <div class="brand-text">TWB ONLINE</div>
            <div class="brand-sub">Water Billing Management</div>
          </div>

        </div>

        {{-- <div style="max-width:480px;margin:0 auto;"> --}}

        <div class="card">
          <div class="table-card-header">
            <div class="card-header-float" style="background:var(--gradient-dark);">
              <div>
                <h3>Reset Password</h3>
              </div>

              <span class="material-icons" style="color:var(--accent-blue)">
                password
              </span>
            </div>
          </div>

          <div class="card-body" style="padding:28px;">
            <div style="margin-bottom:20px">
              <p style="color:var(--text-muted)">
                Enter your registered email address and we'll send you a password reset link
              </p>
            </div>

            @if(session('status'))
              <div class="alert alert-success">
                <span class="material-icons" style="font-size:16px">check_circle</span>
                {{ session('status') }}
              </div>
            @endif

            @if($errors->any())
              <div class="alert alert-error">
                <span class="material-icons" style="font-size:16px">error</span>
                {{ $errors->first() }}
              </div>
            @endif

            <form method="POST" action="{{ route('admin.password.email') }}">
              @csrf

              <div class="form-group">
                <label class="form-label" style="font-size:14px">
                  <span class="material-icons input-icon" style="font-size:14px;color:var(--accent-blue);">
                    email
                  </span>
                  Email Address
                </label>

                <div class="input-wrap">
                  <input
                    type="email"
                    name="email"
                    class="form-control"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="your@email.com">
                </div>
              </div>

              <button type="submit" class="btn btn-primary" style="width:100%;margin-top:8px;">
                Send Reset Link
              </button>
            </form>

            <div style="text-align:center;margin-top:16px;">
              <a href="{{ route('login') }}" style="font-size:13px;color:var(--accent-blue);">
                ← Back to Login
              </a>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>