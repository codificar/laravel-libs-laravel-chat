<?php

namespace Codificar\Chat\Middleware;

use Closure;
use Log;
use Provider;
use User;
use Admin;
use App\Models\Institution;
use Response;

class CheckUserSystem
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
        if($request->input('id') && $request->input('token'))
        {
            $userSystemId = $request->input('id');
            $token = $request->input('token');

            $isUser = User::where('id', $userSystemId)
                ->where('token', $token)
                ->first();

            if($isUser) {
                
                $request->route()->setParameter('user', $isUser);
                $request->route()->setParameter('userSystem', $isUser);
                $request->route()->setParameter('userType', 'user');
                return $next($request);
            }

            $isProvider = Provider::where('id', $userSystemId)
                ->where('token', $token)
                ->first();

            if($isProvider) {
                
                $request->route()->setParameter('provider', $isProvider);
                $request->route()->setParameter('userSystem', $isProvider);
                $request->route()->setParameter('userType', 'provider');
                return $next($request);
            }

            $isInstitution = Institution::where('id', $userSystemId)
                ->where('api_key', $token)
                ->first();

            if ($isInstitution) {
                $user = User::find($isInstitution->default_user_id);

                $request->route()->setParameter('user', $user);
                $request->route()->setParameter('userSystem', $user);
                $request->route()->setParameter('userType', 'corp');
                return $next($request);
            }

            $isAdmin = Admin::where('id', $userSystemId)
                ->first();

            if($isAdmin) {
                
                $request->route()->setParameter('userSystem', $isAdmin);
                $request->route()->setParameter('userType', 'admin');
                return $next($request);
            }
        }

        $response = array('success' => false, 'error' => trans('userController.unauthorized_access'), 'error_code' => 406);
        return Response::json($response, 200);
    }
}