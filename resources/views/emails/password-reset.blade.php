<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Reset Your Password</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;}
.wrap{max-width:580px;margin:40px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.1);}
.hdr{background:linear-gradient(135deg,#0f2d4a,#1a5276);padding:28px 36px;text-align:center;}
.hdr h1{color:#fff;margin:8px 0 0;font-size:20px;}
.hdr p{color:rgba(255,255,255,.65);margin:4px 0 0;font-size:12px;}
.accent{height:4px;background:linear-gradient(90deg,#3498db,#1abc9c);}
.body{padding:32px 36px;color:#333;line-height:1.7;}
.btn{display:inline-block;margin:20px 0;padding:13px 30px;background:#1a73e8;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:14px;}
.note{font-size:12px;color:#888;margin-top:20px;padding-top:14px;border-top:1px solid #eee;}
.footer{background:#f9f9f9;padding:16px 36px;text-align:center;font-size:11px;color:#aaa;}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <h1>TWB Water Billing</h1>
    <p>Tonga Water Board</p>
  </div>
  <div class="accent"></div>
  <div class="body">
    <p>Hello, <strong>{{ $userName }}</strong>!</p>
    <p>We received a request to reset your password. Click the button below to choose a new one:</p>
    <div style="text-align:center;">
      <a href="{{ $resetUrl }}" class="btn">Reset My Password</a>
    </div>
    <p>This link will expire in <strong>{{ $expiryMinutes }} minutes</strong>.</p>
    <p>If you didn't request this, you can safely ignore this email — your password will not change.</p>
    <div class="note">
      <p>If the button doesn't work, copy and paste this link into your browser:</p>
      <p style="word-break:break-all;color:#1a73e8;">{{ $resetUrl }}</p>
    </div>
  </div>
  <div class="footer">&copy; {{ date('Y') }} Tonga Water Board. All rights reserved.</div>
</div>
</body>
</html>
