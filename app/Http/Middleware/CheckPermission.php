<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Checks a named permission method on the User model.
 * Usage: ->middleware('permission:canViewInvoices')
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user) return redirect()->route('login');

        if (method_exists($user, $permission) && $user->$permission()) {
            return $next($request);
        }

        abort(403, 'You do not have permission to access this area.');
    }
}
