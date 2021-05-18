<?php

namespace App\Http\Middleware;
use Closure;
use JWTAuth;
use Exception;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
        } catch (Exception $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException){
                return response(['status' => 'Token is Invalid'],400);
            }else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException){
                return response(['status' => 'Token is Expired'],400);
            }else{
                return response(['status' => 'Authorization Token not found'],400);
            }
        }
        return $next($request);
    }
}