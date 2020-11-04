<?php

namespace Codificar\Chat\Middleware;

use Closure;
use Log;
use Provider;
use User;
use Admin;
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

            $isUser     =   User::isUserToken($userSystemId, $token);

            if($isUser)
            {
                $userData = User::getUserData($userSystemId, $token);
                if (is_token_active($userData->token_expiry))
                {
                    $request->route()->setParameter('user', $userData);
                    $request->route()->setParameter('userSystem', $userData);
                    $request->route()->setParameter('userType', 'user');
                    return $next($request);
                }
            }

            $isProvider =   Provider::isProviderToken($userSystemId, $token);

            if($isProvider)
            {
                $providerData = Provider::getProviderData($userSystemId, $token);
                if(is_token_active($providerData->token_expiry))
                {
                    $request->route()->setParameter('provider', $providerData);
                    $request->route()->setParameter('userSystem', $providerData);
                    $request->route()->setParameter('userType', 'provider');
                    return $next($request);
                }
            }

            $isAdmin    =   Admin::isAdminToken($userSystemId, $token);

            if($isAdmin)
            {
                $request->route()->setParameter('admin', $isAdmin);
                $request->route()->setParameter('userSystem', 'admin');
                return $next($request);
            }
        }

        $response = array('success' => false, 'error' => trans('userController.unauthorized_access'), 'error_code' => 406);
        return Response::json($response, 200);
    }
}