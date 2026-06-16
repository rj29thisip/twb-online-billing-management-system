<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ChangePasswordController extends Controller
{
    public function show()
    {
        return view('admin.account.change-password');
    }

    public function update(Request $request)
    {
        $request->validate([
            'current_password'          => 'required',
            'password'                  => 'required|min:8|confirmed|different:current_password',
            'password_confirmation'     => 'required',
        ], [
            'password.different' => 'New password must be different from your current password.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Current password is incorrect.']);
        }

        $user->update([
            'password'             => Hash::make($request->password),
            'must_change_password' => false,
        ]);

        AuditLogger::log('password_changed', 'User', $user->id, [], ['email' => $user->email],
            "Staff {$user->email} changed their password.");

        return back()->with('success', 'Password changed successfully.');
    }
}
