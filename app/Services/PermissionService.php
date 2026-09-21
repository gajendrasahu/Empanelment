<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
class PermissionService
{
    /**
     * Check whether the user has a permission.
     */
    public function hasPermission($routeName)
    {
		// Super Admin always has access
		if(session('isMaster')==1)
		{
			return true;
		}

		if(empty($routeName))
		{
			return false;
		}

		$permissions = session('permissions', []);

		return isset($permissions[$routeName]);
    }

    /**
     * Return all permissions from session.
     */
    public function getPermissions()
    {
        return Session::get('permissions', []);
    }
}