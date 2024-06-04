<?php

namespace App\Http\Middleware;

use App\Exceptions\CustomJsonResponse;
use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;

class JWT
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
    
        try {
            JWTAuth::parseToken()->authenticate();
        return $next($request);
        } catch (\Exception $e) {
            return CustomJsonResponse::response(
                Response::HTTP_NOT_FOUND,
                null,
                $e->getMessage(),
                1
            );
        }
    }
}
