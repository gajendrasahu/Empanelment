<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateReferer
{
    public function handle(Request $request, Closure $next): Response
    {
        $referer = $request->headers->get('referer');
        $allowedHosts = config('referrers.allowed_hosts');
        $allowEmpty = config('referrers.allow_empty');


        if(!$referer)
		{
            if($allowEmpty)
			{
                return $next($request);
            }

            return redirect()->route('invalid-referer');
        }

        $host = parse_url($referer, PHP_URL_HOST);

        if(!$host || !in_array($host,$allowedHosts, true))
		{
            return redirect()->route('invalid-referer');
        }

        return $next($request);
    }
}