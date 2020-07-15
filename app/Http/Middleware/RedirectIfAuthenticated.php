<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use App\RoleAndPermissions;
use \Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $rd = Role::where('guard_name', 'admin')->get();

        if (Auth::guard($guard)->check()) {
            if(!$request->user()->hasAnyRole($rd)) {
                return redirect('/client/v2/dashboard');
            } else {
                return redirect('/admin/user-accounts');
            }
        }

        return $next($request);
    }
}
