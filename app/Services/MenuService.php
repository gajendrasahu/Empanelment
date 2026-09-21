<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
class MenuService
{
	public function getMenuItems()
    {
		$userid 			= Session::get('userId');
		$usertype 			= Session::get('userType');
		$issuper			= Session::get('issuper');
		$departmentid		= Session::get('departmentId') ?? 0;
		$vendorid			= Session::get('vendorId') ?? 0;
		$isProjectManager	= Session::get('isProjectManager') ?? 0;
		$isResource			= Session::get('isResource') ?? 0;
		
		if($departmentid!=0 && $isProjectManager==0)
		{
			$cacheKey = 'menu_tree_super_department';
		}
		else if($departmentid!=0 && $isProjectManager!=0)
		{
			$cacheKey = 'menu_tree_super_project_manager';
		}
		else if($vendorid!=0)
		{
			$cacheKey = 'menu_tree_user_vendor';
		}
		else if($isProjectManager!=0)
		{
			$cacheKey = 'menu_tree_super_project_manager';
		}
		else if($isResource!=0)
		{
			$cacheKey = 'menu_tree_super_resource';
		}
		else
		{
			$cacheKey = 'menu_tree_admin_'.$userid;
		}
		
		return Cache::remember($cacheKey, 60, function () use ($issuper, $userid,$usertype,$departmentid,$vendorid,$isProjectManager,$isResource)
		{
			if($issuper===1 && $departmentid===0 && $vendorid===0 && $isResource===0)
			{
				$menus = DB::table('mainmenu_tbl')
						->where('isactive','=',1)
						->where('menufor','=','ADMIN')
						->orderBy('displayorder')
						->get();
			}
			else if($issuper===1 && $departmentid===0 && $vendorid===0 && $isResource===1)
			{
				$menus	=	DB::table('mainmenu_tbl')
							->where('isactive','=',1)
							->where('menufor','=','RESOURCE')
							->orderBy('displayorder')
							->get();
			}
			else if($issuper===1 && $departmentid!=0 && $vendorid===0 && $isResource===0)
			{
				if($isProjectManager===0)
				{
					$menus = DB::table('mainmenu_tbl')
							->where('isactive', '=', 1)
							->where('menufor', '=','DEPARTMENT')
							->orderBy('displayorder')
							->get();
				}
				else
				{
					$menus = DB::table('mainmenu_tbl')
							->where('isactive', '=', 1)
							->where('menufor', '=','PROJECT MANAGER')
							->orderBy('displayorder')
							->get();
					
				}
			}
			else if($issuper===1 && $departmentid===0 && $vendorid!=0 && $isResource===0)
			{
				$query	=	DB::table('mainmenu_tbl')
							->where('isactive', 1)
							->where('menufor', 'VENDOR');

							if(!session()->has('subUserId'))
							{
								$menus = $query->orderBy('displayorder')->get();
							}
							else
							{
								$menus = $query
									->whereNotIn('menuid', ['136'])
									->orderBy('displayorder')
									->get();
							}
			}
			else
			{
				$menus = DB::table('mainmenu_users')
						->where('userid', '=', $userid)
						->where('ispermitted', '=', 1)
						->where('menufor', '=', $usertype)
						->orderBy('displayorder')
						->get();			
			}
			return $this->buildMenuTree($menus);
		});
	}
	
	private function buildMenuTree($menus, $parentId = null)
    {
        $branch = [];

        foreach ($menus as $menu)
		{
            if ($menu->parentid == $parentId)
			{
                $children = $this->buildMenuTree($menus, $menu->menuid);
                if ($children) {
                    $menu->children = $children;
                }
                $branch[] = $menu;
            }
		}

        return $branch;
    }
	

}