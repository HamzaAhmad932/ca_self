<?php

namespace App\Http\Middleware;

use Closure;
use App\RoleAndPermissions;
use \Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

       $rd = Role::where('guard_name', 'admin')->get();

       if($request->user() === null){
           return redirect(route('login'))->with('message', 'Please Login again!');
        }

        if($request->user()->hasAnyRole($rd)){
            auth()->user()->guard_name = 'admin';
           return $next($request);
       }
        return redirect('/client/v2/dashboard');
    }
}
