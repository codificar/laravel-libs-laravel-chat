<?php

namespace Codificar\Chat\Middleware;

use \Illuminate\Http\Request;
use Closure;

class CheckAdminLogged
{
    private const CORP = 6;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = \Auth::guard('web')->user($id = null);
        if (!$user) {
            return \Redirect::to("/admin/login");
        }

        $profileCorp = \Profile::CORP 
            ? \Profile::CORP 
            : self::CORP;

        if ($user->profile_id == $profileCorp){
            abort(404);
        }

        $request->id = $id;
        $request->user = $user;
        return $next($request);   
    }
}