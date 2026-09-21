<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\PermissionService;

class PermissionMiddleware
{
    protected $permissionService;

    public function __construct(PermissionService $permissionService)
    {
        $this->permissionService = $permissionService;
    }

    public function handle(Request $request, Closure $next)
    {
        // Only ADMIN users will be checked
        if(session('userType') != 'ADMIN' && session('isMaster')!=1)
        {
            return $next($request);
        }

        // Super Admin bypass
        if(session('isMaster') == 1)
        {
            return $next($request);
        }

        $routeName = $request->route()->getName();

        if(!$this->permissionService->hasPermission($routeName))
        {
			Log::info('You are not authorized to access this page.');
            return redirect()->back()->with('permission_error','You are not authorized to access this page.');
        }

        return $next($request);
    }
}
?>