<?php

namespace App\Http\Middleware;

use JWTAuth;
use Closure;
use Illuminate\Http\Request;
use App\Models\UserPremieum;

class UserPaid
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
        $prem = UserPremieum::where("id_user","=",$user->id)->select("isPaid")->first();

        if(!$prem){
            return response('Vous avez pas payé pour avoir une compte premieum',400);
        };
        return $next($request);
    }
}