<?php

namespace Codificar\Chat\Middleware;

use \Illuminate\Http\Request;
use Closure;

class CheckCorpLogged
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = \Auth::guard('web_corp')->user();
        if (!$user || !$user->AdminInstitution) {
            return \Redirect::to("/corp/login");
        }
        $request->user = $user;
        $request->adminInstitution = $user->AdminInstitution;
        return $next($request);   
    }
}