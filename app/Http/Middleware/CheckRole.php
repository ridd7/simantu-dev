<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check() || !in_array($request->user()->level_user, $roles)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }

    private function checkUserRole($user, $roles)
    {
        if (in_array('admin', $roles) && $user->level_user === 'admin') {
            return true;
        }

        if (in_array('koordinator', $roles) && $user->koordinator_akses === 'Y') {
            return true;
        }

        if (in_array('user', $roles) && $user->level_user !== 'admin' && $user->koordinator_akses !== 'Y') {
            return true;
        }

        return false;
    }

    
}
