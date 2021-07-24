<?php

namespace App\Http\Middleware;

use JWTAuth;
use Closure;
use Illuminate\Http\Request;

class UserPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next,$role)
    {
        
        $user=JWTAuth::user();
        if(($role=="SupAdmin" && $user->typeuser!="SupAdmin") || ($role=="Admin" && !in_array($user->typeuser,['SupAdmin','Admin'])) || ($role=="UserPremieume" && !in_array($user->typeuser,['SupAdmin','Admin','UserPremieume'])) || ($role=="Gestionnaire" && !in_array($user->typeuser,['SupAdmin','Admin','Gestionnaire'])) || ($role=="UserSimple" && !in_array($user->typeuser,['SupAdmin','Admin','UserSimple','UserPremieume']))){
            return response('Premission denied',401);
        };
        return $next($request);
    }
}