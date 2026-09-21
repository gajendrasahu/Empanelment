<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        // Only apply these headers in production (not local or development environments)
        if (env('APP_ENV') === 'local') {

            // Add X-XSS-Protection header
            $response->headers->set('X-XSS-Protection', '1; mode=block');
            
            // Add HTTP Strict Transport Security (HSTS) header with max-age=60000 (16.6 hours)
            $response->headers->set('Strict-Transport-Security', 'max-age=60000; includeSubDomains');
            
            // Add Content Security Policy (CSP) header in a single line
            $response->headers->set('Content-Security-Policy', 
                "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; object-src 'none'; style-src 'self' 'unsafe-inline' https://fonts.googleapis.com; font-src 'self' https://fonts.gstatic.com; img-src 'self' https://vaarnikaenterprises.com;"
            );
        }

        return $response;
    }
}