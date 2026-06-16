<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnforcePasswordChange
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && $user->must_change_password) {
            $exempt = ['admin.password.change.form', 'admin.password.change.update', 'logout'];
            if (!in_array($request->route()?->getName(), $exempt)) {
                return redirect()->route('admin.password.change.form')
                    ->with('warning', 'You must change your password before continuing.');
            }
        }
        return $next($request);
    }
}
