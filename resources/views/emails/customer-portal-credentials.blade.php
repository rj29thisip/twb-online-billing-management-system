<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Your TWB Portal Access</title>
<style>
body{font-family:Arial,sans-serif;background:#f4f4f4;margin:0;padding:0;}
.wrap{max-width:580px;margin:40px auto;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.1);}
.hdr{background:linear-gradient(135deg,#0f2d4a,#1a5276);padding:28px 36px;text-align:center;}
.hdr h1{color:#fff;margin:8px 0 0;font-size:20px;} .hdr p{color:rgba(255,255,255,.65);margin:4px 0 0;font-size:12px;}
.accent{height:4px;background:linear-gradient(90deg,#3498db,#1abc9c);}
.body{padding:32px 36px;color:#333;line-height:1.7;}
.creds{background:#f0f7ff;border:1px solid #c2daf7;border-radius:8px;padding:16px 20px;margin:16px 0;}
.creds p{margin:5px 0;font-size:14px;} .creds strong{display:inline-block;width:110px;color:#555;}
.creds .val{font-family:monospace;font-size:14px;font-weight:bold;color:#1a3a5c;}
.btn{display:inline-block;margin:20px 0;padding:13px 30px;background:#1a73e8;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:14px;}
.note{font-size:12px;color:#888;margin-top:20px;padding-top:14px;border-top:1px solid #eee;}
.footer{background:#f9f9f9;padding:16px 36px;text-align:center;font-size:11px;color:#aaa;}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr"><h1>TWB Customer Portal</h1><p>Tonga Water Board</p></div>
  <div class="accent"></div>
  <div class="body">
    <p>Hello, <strong>{{ $customerName }}</strong>!</p>
    <p>Your customer portal account has been created. You can now log in to view your bills, meter readings, and payment history.</p>
    <div class="creds">
      <p><strong>Email:</strong> <span class="val">{{ $email }}</span></p>
      <p><strong>Password:</strong> <span class="val">{{ $temporaryPassword }}</span></p>
    </div>
    <p>For security, you will be asked to <strong>change your password</strong> when you first log in.</p>
    <div style="text-align:center;"><a href="{{ $loginUrl }}" class="btn">Log In to Portal</a></div>
    <div class="note">
      <p>If the button doesn't work: <span style="word-break:break-all;color:#1a73e8;">{{ $loginUrl }}</span></p>
      <p>If you did not expect this email, please contact us immediately.</p>
    </div>
  </div>
  <div class="footer">&copy; {{ date('Y') }} Tonga Water Board. All rights reserved.</div>
</div>
</body>
</html>
