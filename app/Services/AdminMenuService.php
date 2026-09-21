<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
class AdminMenuService
{
	public function getMenuItems()
    {
		$userid 			= Session::get('userId');
		$usertype 			= Session::get('userType');
		$issuper			= Session::get('issuper');
	
		$cacheKey = 'menu_tree_admin_'.$userid;
		
		return Cache::remember($cacheKey,now()->addMinute(60),function () use ($issuper,$userid,$usertype)
		{
			$menus	=	DB::table('mainmenu_tbl as m')
						->join('permissions_tbl as p','m.menuid','=','p.menuid')
						->join('users_permissions as up','p.permissionid','=','up.permission_id')
						->where('up.user_id',$userid)
						->where('up.permission_status','ALLOW')
						->where('p.permission_type','MENU')
						->where('m.isactive',1)
						->where('m.menufor','ADMIN')
						->orderBy('m.displayorder')
						->select('m.*')
						->distinct()
						->get();
			
			
			$menuIds = $menus->pluck('menuid')->toArray();

			$parentIds	=	DB::table('mainmenu_tbl')
							->whereIn('menuid', $menuIds)
							->whereNotNull('parentid')
							->where('parentid', '!=', 0)
							->pluck('parentid')
							->unique()
							->toArray();

			$parents	=	DB::table('mainmenu_tbl')
							->whereIn('menuid', $parentIds)
							->where('isactive', 1)
							->get();

			$menus	=	$menus->merge($parents)->unique('menuid');
			
			return $this->buildMenuTree($menus);
		});
	}
	
	private function buildMenuTree($menus,$parentId=null)
    {
        $branch = [];

        foreach($menus as $menu)
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