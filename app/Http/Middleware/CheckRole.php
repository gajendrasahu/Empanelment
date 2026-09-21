<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Session;
class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $userType = session('userType');  // or however you store it

        if (! $userType || ! in_array($userType, $roles))
		{

			if(Session::has('userId'))
			{
				$userId  		= 	Session::get('userId');
				$isSuper 		= 	Session::get('issuper');
				$departmentid	=	Session::get('departmentId') ?? 0;
				$vendorid		=	Session::get('vendorId') ?? 0;

				// Clear cached menu
				if ($isSuper == 1) {
					Cache::forget('menu_tree_super'.'_'.$departmentid.'_'.$vendorid);
				} else {
					Cache::forget('menu_tree_user_' . $userId.'_'.$departmentid.'_'.$vendorid);
				}

				// Clear session data
				Session::pull('userId');
				Session::pull('userName');
				Session::pull('shortName');
				Session::pull('userType');
				Session::pull('issuper');
				Session::pull('departmentId');
				Session::pull('vendorId');

				Session::pull('categoryid');
				Session::pull('projecttitle');
				Session::pull('projectduration');
				Session::pull('projectobjective');
				
				
				return redirect('loginpanel')->with('fail','Unauthorized Access');

			}
        }

        return $next($request);
    }
}