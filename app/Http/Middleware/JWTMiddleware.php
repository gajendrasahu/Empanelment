<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\JWTAuthHelper;
use Illuminate\Support\Facades\DB;

class JWTMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['message' =>'PLEASE PROVIDE ACCESS TOKEN.','status'=>400], 400);
        }

        $token = str_replace('Bearer ','',$token);

        $customerId = JWTAuthHelper::validateToken($token);

        if (!$customerId) {
            return response()->json(['message' => 'UNAUTHORIZED ACCESS TOKEN.','status'=>401], 401);
        }

        $request->merge(['customerid' => $customerId]);

        return $next($request);
    }
}

?>