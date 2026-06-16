<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ForcePasswordChangeController extends Controller
{
    public function showChangeForm()
    {
        return view('admin.auth.force-change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'password' => 'required|min:8|confirmed',
        ]);

        $user = $request->user();

        // Prevent setting the same password
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Your new password must be different from your current password.']);
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLogger::log('password_changed_first_login', 'User', auth()->id(), [], [], 'Password changed on first login');

        return redirect()->route('admin.dashboard')
            ->with('success', 'Password updated successfully. Welcome to TWB Water Billing!');
    }
}
