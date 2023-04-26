<?php

namespace App\Http\Middleware;

use Closure;

class AuthenticationToken
{
    public function handle($request, Closure $next)
    {
        $bearerToken = 'SkFabTZibXE1aE14ckpQUUxHc2dnQ2RzdlFRTTM2NFE2cGI4d3RQNjZmdEFITmdBQkE=';
        $authHeader = $request->header('Authorization');
        if (!$authHeader || $authHeader !== "Bearer $bearerToken") {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }
        return $next($request);
    }
}
