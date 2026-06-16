<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Usage in routes:
 *   ->middleware('role:admin,cashier')          → check $user->role
 *   ->middleware('can_permission:viewInvoices')  → check $user->can{Method}()
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');
        if (empty($roles) || in_array($user->role, $roles)) return $next($request);
        abort(403, 'You do not have permission to access this area.');
    }
}
