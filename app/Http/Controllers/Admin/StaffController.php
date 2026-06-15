<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\AuditLogger;
use App\Models\District;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * StaffController — handles admin user/staff management.
 *
 * KEY CHANGES from previous version:
 *  - Role list expanded to 6 roles
 *  - district_id added to store/update
 *  - must_change_password set to true on create (staff must set own password on first login)
 */
class StaffController extends Controller
{
    public function index()
    {
        $staff = User::with('district')->orderBy('name')->paginate(15);
        return view('admin.staff.index', compact('staff'));
    }

    public function create()
    {
        $districts = District::where('is_active', true)->orderBy('name')->get();
        $roles     = User::$roles;
        return view('admin.staff.create', compact('districts', 'roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email',
            'role'        => 'required|in:' . implode(',', array_keys(User::$roles)),
            'district_id' => 'nullable|exists:districts,id',
            'is_active'   => 'boolean',
        ]);

        // Generate a temporary password; staff must change on first login
        $temporaryPassword = Str::random(12);

        $user = User::create([
            ...$data,
            'password'             => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        AuditLogger::log('staff_action', 'User', null, [], [], 'Staff action');

        // Optionally email the temp password (if email config is active)
        // app(EmailService::class)->sendStaffWelcome($user->email, $user->name, $temporaryPassword);

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff '{$user->name}' created. Temporary password: <code>{$temporaryPassword}</code> (save this now — it won't be shown again).")
            ->with('show_temp_password', true);
    }

    public function edit(User $staff)
    {
        $districts = District::where('is_active', true)->orderBy('name')->get();
        $roles     = User::$roles;
        return view('admin.staff.edit', compact('staff', 'districts', 'roles'));
    }

    public function update(Request $request, User $staff)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users,email,' . $staff->id,
            'role'        => 'required|in:' . implode(',', array_keys(User::$roles)),
            'district_id' => 'nullable|exists:districts,id',
            'is_active'   => 'boolean',
        ]);

        $old = $staff->only(['role', 'district_id']);
        $staff->update($data);

        AuditLogger::log('staff_action', 'User', null, [], [], 'Staff action');

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff '{$staff->name}' updated successfully.");
    }

    public function destroy(Request $request, User $staff)
    {
        // Prevent deleting yourself
        if ($staff->id === auth()->id()) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $name = $staff->name;
        $staff->delete();

        AuditLogger::log('staff_action', 'User', null, [], [], 'Staff action');

        return redirect()->route('admin.staff.index')
            ->with('success', "Staff '{$name}' deleted.");
    }

    /**
     * Reset staff password and force change on next login.
     */
    public function resetPassword(Request $request, User $staff)
    {
        $temporaryPassword = Str::random(12);
        $staff->update([
            'password'             => Hash::make($temporaryPassword),
            'must_change_password' => true,
        ]);

        AuditLogger::log('staff_action', 'User', null, [], [], 'Staff action');

        return back()->with('success', "Password reset for {$staff->name}. New temporary password: <code>{$temporaryPassword}</code>")
            ->with('show_temp_password', true);
    }
}
