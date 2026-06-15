<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogger;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::with(['customer', 'district'])
            ->where('role', '!=', 'customer')  // Staff only — customers are managed in Customer module
            ->when($request->search, fn($q) => $q->where(fn($q) =>
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('email', 'like', '%'.$request->search.'%')
            ))
            ->when($request->role, fn($q) => $q->where('role', $request->role))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|string|min:8',
            'role'        => 'required|in:admin,cashier,account_employee,ceo,accountant,manager',
            'district_id' => 'nullable|exists:districts,id',
        ]);

        $user = User::create([
            'name'                 => $validated['name'],
            'email'                => $validated['email'],
            'password'             => bcrypt($validated['password']),
            'role'                 => $validated['role'],
            'district_id'         => $validated['district_id'] ?? null,
            'is_active'            => true,
            'must_change_password' => true,
        ]);

        AuditLogger::log('staff_created', 'User', $user->id, [], [
            'name'        => $user->name,
            'email'       => $user->email,
            'role'        => $user->role,
            'district'    => $user->district?->name ?? 'None',
        ], "Staff user created: {$user->name}");

        return back()->with('success', "Staff user '{$user->name}' created. They will be prompted to change their password on first login.");
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'role'        => 'required|in:admin,cashier,account_employee,ceo,accountant,manager,customer',
            'district_id' => 'nullable|exists:districts,id',
            'is_active'   => 'nullable|boolean',
        ]);

        $old = $user->only(['name', 'role', 'district_id', 'is_active']);
        $validated['is_active']   = $request->boolean('is_active');
        $validated['district_id'] = $validated['district_id'] ?? null;
        $user->update($validated);

        AuditLogger::log('staff_updated', 'User', $user->id, $old, [
            'name'      => $user->name,
            'role'      => $user->role,
            'district'  => $user->district?->name ?? 'None',
            'is_active' => $user->is_active,
        ], "Staff user updated: {$user->name}");

        return back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'Cannot deactivate yourself.');
        $user->update(['is_active' => false]);

        AuditLogger::log('staff_deactivated', 'User', $user->id, ['is_active' => true], ['is_active' => false],
            "Deactivated staff: {$user->name} ({$user->email})");

        return back()->with('success', 'User deactivated.');
    }
}
