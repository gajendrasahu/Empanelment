<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;

class ValidateHostHeader
{
    public function handle($request, Closure $next)
    {
		if ($request->route() && $request->route()->getName() === 'invalid-host') {
			return $next($request);
		}		

        $allowedHosts = [
            '127.0.0.1',
			'localhost',
			'::1',
            'empl.cgstate.gov.in',
			'www.empl.cgstate.gov.in',
			'empl.vaarnikaenterprises.com',
			'www.empl.vaarnikaenterprises.com'
        ];

        $host = $request->getHost();

        if (!in_array($host, $allowedHosts, true)) {
            //throw new SuspiciousOperationException("Invalid Host header: $host");
			
			return response()->view('errors.invalid-host', [], 400);
        }

        return $next($request);
    }
}