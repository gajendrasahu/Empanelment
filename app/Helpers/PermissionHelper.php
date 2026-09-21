<?php

use Illuminate\Support\Facades\Session;

if (!function_exists('hasPermission'))
{
    function hasPermission($routeName)
    {
        $permissions = Session::get('permissions', []);

        return isset($permissions[$routeName]);
    }
}
?>