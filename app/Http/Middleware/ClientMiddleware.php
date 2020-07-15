<?php

namespace App\Http\Middleware;

use Closure;
use App\RoleAndPermissions;
use \Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\URL;
use \Illuminate\Support\Facades\Auth;


class ClientMiddleware
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

       $rd = Role::where('guard_name', 'client')->get();

       if($request->user() === null){
           return redirect(route('login'))->with('message', 'Please Login again!');
       }

       if($request->user()->hasAnyRole($rd)){

           if ($request->user()->email_verified_at==null) {

               $id = $request->user()->id;
//               $url = URL::temporarySignedRoute('activeuser', now()->addMinutes(30), ['user' => $id]);
               $url = URL::signedRoute('activate-user', ['user' => $id]);

               return redirect($url); // You can add a route of your choice

           }  

           return $next($request);
       }
        return redirect('/admin/user-accounts');
    }

   
}
