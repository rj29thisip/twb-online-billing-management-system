{{--
    LOGIN VIEW ADDITION
    ───────────────────
    Add the "Forgot Password?" link to your existing admin login form.

    Find the password field block in resources/views/admin/auth/login.blade.php
    and add the forgot-password link below the password input, like this:
--}}

{{-- BEFORE (existing): --}}
{{--
<div class="form-group">
    <label for="password">Password</label>
    <input type="password" id="password" name="password" class="form-control" required>
</div>
--}}

{{-- AFTER (with forgot password link): --}}
<div class="form-group">
    <div class="d-flex justify-content-between align-items-center">
        <label for="password">Password</label>
        <a href="{{ route('admin.password.request') }}"
           class="small text-muted"
           style="font-size: 0.82rem;">
            Forgot password?
        </a>
    </div>
    <input
        type="password"
        id="password"
        name="password"
        class="form-control @error('password') is-invalid @enderror"
        required
        autocomplete="current-password"
    >
    @error('password')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
