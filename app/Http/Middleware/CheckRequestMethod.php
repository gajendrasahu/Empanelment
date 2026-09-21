<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRequestMethod
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return \Illuminate\Http\Response
     */
	 
	public function handle(Request $request, Closure $next)
	{

		$allowedMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'];

		if (!in_array($request->method(), $allowedMethods)) {
			return response()->json(['message' => 'Method not allowed.'], 405);
		}

		return $next($request);
	}
}