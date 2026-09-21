<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Master;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

class MenuController extends Controller
{
	/* PERMISSION RELATED */
    public function addPermission(Request $request)
	{
        Session::put('adminmenu','users');
		Session::put('adminsubmenu','addpermission');
		$users = DB::table('applicationusers')->where('issuper','!=',1)->get();
		$menus	=	DB::table('mainmenu_tbl')->where('hassubmenu','=',1)->orderby('displayorder')->get();
        return view('admin/master/permission_add',compact('users','menus'));
    }
    public function getPermissionData(Request $request)
	{
		$userid		=	intval($request->input('userid'));
		$parentid	=	intval($request->input('parentid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page',1);
		/*
		$data = DB::table('mainmenu_tbl as a')
			->select('a.menuid', 'a.mastermenu')
			->distinct()
			->leftJoin('menu_mapping as b', 'b.menuid', '=', 'a.menuid')
			->leftJoin('menu_action as c', 'c.actionid', '=', 'b.actionid')
			->when($parentid!=0,function($query) use ($parentid){
				return $query->where('a.parentid','=',$parentid);
			})			
			->when($parentid==0,function($query) use ($parentid){
				return $query->where('a.parentid','!=',0)
							->orWhere('a.menuurl', '!=', '');				
			})						
			->orderBy('a.menuid')
			->get();		

		$data->map(function ($menu) use ($userid) {
			$menu->actions = DB::table('menu_mapping as b')
				->select('b.mappingid','c.actionid', 'c.actionname',
						 DB::raw('IF(p.actionid IS NULL, 0, 1) as is_permitted'),'p.ispermitted')
				->leftJoin('menu_action as c', 'c.actionid', '=', 'b.actionid')
				->leftJoin('menu_permission as p', function($join) use ($userid, $menu) {
					$join->on('p.actionid', '=', 'c.actionid')
						 ->on('p.menuid', '=', 'b.menuid')
						 ->where('p.userid','=', $userid)
						 ->where('p.ispermitted','=',1);
				})
				->where('b.menuid', $menu->menuid)
				->orderBy('c.displayorder')
				->get();
			return $menu;
		});
		*/
		
		$records=	DB::table('menu_mapping')->distinct('actionid')->count();
		$data 	=	DB::table('mainmenu_tbl')
						->when($parentid!=0,function($query) use ($parentid){
							return $query->where('menuid','=',$parentid);
						})			
						->when($parentid==0,function($query) use ($parentid){
							return $query->where('parentid','=',0);
						})						
						->orderby('displayorder')
						->get();
		foreach($data as $menu)
		{
			$menu->mapping	=	DB::table('menu_mapping as a')
								->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
								->leftjoin('menu_action as c','c.actionid','=','a.actionid')
								->where('a.menuid','=',$menu->menuid)
								->orderby('c.displayorder')
								->get();
			if($menu->mapping->count()==0)
			{
				$acids	=	DB::table('mainmenu_tbl')
								->select(DB::raw('GROUP_CONCAT(actionids) as ids'),DB::raw('GROUP_CONCAT(menuid) as mids'))
								->where('parentid','=',$menu->menuid)
								->first();

				$actionids		=	explode(",",$acids->ids);
				$actionids		=	array_unique($actionids);

				$mids		=	explode(",",$acids->mids);
				$mids		=	array_unique($mids);

				$menu->mapping	=	DB::table('menu_action')
										->wherein('actionid',$actionids)
										->orderby('displayorder')
										->get();
				
			}
			if($menu->menuurl!='' && $menu->parentid==0)
			{
				$menu->singlesubmenu = DB::table('mainmenu_tbl')->where('menuid','=',$menu->menuid)->orderby('displayorder')->get();
				foreach($menu->singlesubmenu as $singlesubmenu)
				{
					$singlesubmenu->mapping	=	DB::table('menu_mapping as a')
											->select('a.mappingid','c.actionid','c.actionname',DB::raw('IF(d.actionid IS NULL, 0, 1) as ispermitted'))
											->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
											->leftjoin('menu_action as c','c.actionid','=','a.actionid')
											->leftJoin('menu_permission as d', function($join) use ($userid, $menu) {
												$join->on('d.actionid', '=', 'c.actionid')
													 ->on('d.menuid', '=', 'b.menuid')
													 ->where('d.userid','=', $userid)
													 ->where('d.ispermitted','=',1);
											})
											->where('a.menuid','=',$singlesubmenu->menuid)
											->orderby('c.displayorder')
											->get();
					//dd($submenu->mapping);
				}				
			}
			else
			{
				$menu->singlesubmenu	=	[];
			}
			
			$menu->submenu = DB::table('mainmenu_tbl')->where('parentid','=',$menu->menuid)->orderby('displayorder')->get();
			foreach($menu->submenu as $submenu)
			{
				$submenu->mapping	=	DB::table('menu_mapping as a')
										->select('a.mappingid','c.actionid','c.actionname',DB::raw('IF(d.actionid IS NULL, 0, 1) as ispermitted'))
										->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
										->leftjoin('menu_action as c','c.actionid','=','a.actionid')
										->leftJoin('menu_permission as d', function($join) use ($userid, $menu) {
											$join->on('d.actionid', '=', 'c.actionid')
												 ->on('d.menuid', '=', 'b.menuid')
												 ->where('d.userid','=', $userid)
												 ->where('d.ispermitted','=',1);
										})
										->where('a.menuid','=',$submenu->menuid)
										->orderby('c.displayorder')
										->get();
				//dd($submenu->mapping);
			}
		}
		
		return view('admin/ajaxpages/permissionTable',compact('data','userid','records'));
    }
    public function storePermission(Request $request,$recordid)
	{        
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');

		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$uid		=	$request->input('uid_'.$recordid);
		$mapping	=	DB::table('menu_mapping')->where('mappingid','=',$recordid)->first();
		
		$exists	=	DB::table('menu_permission')
					->where('userid','=',$uid)
					->where('menuid','=',$mapping->menuid)
					->where('actionid','=',$mapping->actionid)
					->get();
		if($exists->count()==0)
		{
			$remark	=	"PERMISSION GRANTED BY ".$userName." ON ".date('d\-m\-Y h:i A')."<br>";
			DB::insert('insert into menu_permission(userid,menuid,actionid,ispermitted,remark) values(?,?,?,?,?)',[$uid,$mapping->menuid,$mapping->actionid,1,$remark]);
			$check	=	DB::table('mainmenu_users')
						->where('menuid','=',$mapping->menuid)
						->where('userid','=',$uid)
						->get();
			if($check->count()==0)
			{
				$menu	=	DB::table('mainmenu_tbl')
							->where('menuid','=',$mapping->menuid)
							->first();
				DB::insert('insert into mainmenu_users(menuid,parentid,mastermenu,icon,displayorder,menuurl,hassubmenu,activevalue,passuserid,actionids,userid,ispermitted) values(?,?,?,?,?,?,?,?,?,?,?,?)',[$menu->menuid,$menu->parentid,$menu->mastermenu,$menu->icon,$menu->displayorder,$menu->menuurl,$menu->hassubmenu,$menu->activevalue,$menu->passuserid,$menu->actionids,$uid,1]);
				if($menu->parentid!=0)
				{
					$parent =	DB::table('mainmenu_users')
									->where('menuid','=',$menu->parentid)
									->where('userid','=',$uid)
									->get();								
					if($parent->count()==0)
					{
						$mn = DB::table('mainmenu_tbl')
								->where('menuid','=',$menu->parentid)
								->first();
						DB::insert('insert into mainmenu_users(menuid,parentid,mastermenu,icon,displayorder,menuurl,hassubmenu,activevalue,passuserid,actionids,userid,ispermitted) values(?,?,?,?,?,?,?,?,?,?,?,?)',[$mn->menuid,$mn->parentid,$mn->mastermenu,$mn->icon,$mn->displayorder,$mn->menuurl,$mn->hassubmenu,$mn->activevalue,$mn->passuserid,$mn->actionids,$uid,1]);
					}
				}
			}
			else
			{
				foreach($check as $data)
				{
					DB::update('update mainmenu_users set ispermitted=? where userid=? and menuid=?',[1,$uid,$data->menuid]);
					if($data->parentid!=0)
					{
						$parent	=	DB::table('mainmenu_users')->where('menuid','=',$data->parentid)->get();
						if($parent->count()>0)
						{
							DB::update('update mainmenu_users set ispermitted=? where userid=? and menuid=?',[1,$uid,$data->parentid]);
						}
					}
				}
			}
		}
		else
		{
			foreach($exists as $data)
			{
				if($data->ispermitted==1)
				{
					$remark	=	$data->remark."PERMISSION DENIED BY ".$userName." ON ".date('d\-m\-Y h:i A')."<br>";
					DB::update('update menu_permission set ispermitted=?,remark=? where userid=? and menuid=? and actionid=?',[0,$remark,$uid,$mapping->menuid,$mapping->actionid]);
				}
				else
				{
					$remark	=	$data->remark."PERMISSION GRANTED BY ".$userName." ON ".date('d\-m\-Y h:i A')."<br>";
					DB::update('update menu_permission set ispermitted=?,remark=? where userid=? and menuid=? and actionid=?',[1,$remark,$uid,$mapping->menuid,$mapping->actionid]);
				}
			}
			$count	=	DB::table('menu_permission')
						->where('menuid','=',$mapping->menuid)
						->where('ispermitted','=',1)
						->where('userid','=',$uid)
						->get();
			if($count->count()==0)
			{
				DB::update('update mainmenu_users set ispermitted=? where menuid=? and userid=?',[0,$mapping->menuid,$uid]);
				$men	=	DB::table('mainmenu_tbl')->where('menuid','=',$mapping->menuid)->first();
				$mids	=	DB::table('mainmenu_tbl')->select(DB::raw('GROUP_CONCAT(menuid) as ids'))->where('parentid','=',$men->parentid)->first();
				DB::insert('insert into menu_test(test) values(?)',[$mids->ids]);
				$ids = $mids ? $mids->ids : null;
				if($mids->ids!='')
				{
					$menids	=	explode(",",$mids->ids);
					$isavailable	=	DB::table('mainmenu_users')->where('ispermitted','=',1)->whereIn('menuid',$menids)->get();
					if($isavailable->count()==0)
					{
						DB::update('update mainmenu_users set ispermitted=? where menuid=? and userid=?',[0,$men->parentid,$uid]);
					}
				}
			}
			else
			{
				DB::update('update mainmenu_users set ispermitted=? where menuid=? and userid=?',[1,$mapping->menuid,$uid]);
				$men	=	DB::table('mainmenu_tbl')->where('menuid','=',$mapping->menuid)->first();
				DB::update('update mainmenu_users set ispermitted=? where menuid=? and userid=?',[1,$men->parentid,$uid]);
			}
		}
		return response()->noContent();
    }
	/* PERMISSION RELATED CLOSED */

	/* MENU RELATED */
    public function addMenu(Request $request)
	{
		$usertype     =	$request->session()->get('userType');
        Session::put('adminmenu','menus');
		Session::put('adminsubmenu','addmenu');
		$displayorder = DB::table('mainmenu_tbl')
					->where('hassubmenu','=',1)
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;	
		
		$menus	=	DB::table('mainmenu_tbl')->where('hassubmenu','=',1)->where('menufor','=',$usertype)->orderby('displayorder')->get();
		$actions=	DB::table('menu_action')->orderby('displayorder')->get();
        return view('admin/master/menu_add',compact('menus','actions','displayorder'));
    }
    public function storeMenu(Request $request,$recordid)
	{        
        $rules = [
            'mastermenu' => 'required|max:50',
			'displayorder' => 'required',
			'activevalue' => 'required',
        ];

        $messages = [
            'mastermenu.required' => 'TAG LINE IS REQUIRED',
            'displayorder.required' => 'IT IS REQUIRED',
            'activevalue.required' => 'REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$usertype   =	$request->session()->get('userType');

		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$menuid			=	intval($request->input('menuid'));
		$mastermenu		=	strtolower($request->input('mastermenu'));
		$icon			=	strtolower($request->input('icon'));
		$displayorder	=	intval($request->input('displayorder'));
		$menuurl		=	(String) $request->input('menuurl');
		$hassubmenu		=	intval($request->input('hassubmenu'));
		$passuserid		=	intval($request->input('passuserid'));
		$activevalue	=	(String) $request->input('activevalue');
		$actionids		=	(String) $request->input('actionids');
		
		if($recordid==0)
		{
			$res = DB::insert('INSERT INTO mainmenu_tbl(parentid,mastermenu,icon,displayorder,menuurl,hassubmenu,activevalue,actionids,passuserid,createdby,createdbyname,creationdate,menufor) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)', [$menuid,$mastermenu,$icon,$displayorder,$menuurl,$hassubmenu,$activevalue,$actionids,$passuserid,$userId,$userName,$creationdate,$usertype]);
			if($res)
			{
				$menuid = DB::getPdo()->lastInsertId();
				if($actionids!='')
				{
					$actions	=	explode(",",$actionids);
					foreach($actions as $rec)
					{
						DB::insert('insert into menu_mapping(menuid,actionid) values(?,?)',[$menuid,$rec]);
					}
				}
				return back()->with('success','MENU DETAIL STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','MENU DETAIL COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$newDisplayOrder = intval($request->input('displayorder'));
			$menu	=	DB::table('mainmenu_tbl')->where('menuid','=',$recordid)->first();
			$oldDisplayOrder = $menu->displayorder;
			if($newDisplayOrder!=$oldDisplayOrder)
			{
				DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder,$menuid, $recordid) {
				if($newDisplayOrder>$oldDisplayOrder)
				{
					DB::table('mainmenu_tbl')
						->when($menuid!=0,function($query) use ($menuid){
							return $query->where('parentid','=',$menuid);
						})
						->when($menuid==0,function($query) use ($menuid){
							return $query->where('parentid','=',0);
						})
						->where('displayorder', '>', $oldDisplayOrder)
						->where('displayorder', '<=', $newDisplayOrder)
						->where('menuid','!=',$recordid)
						->decrement('displayorder');
				}
				else
				{
					DB::table('mainmenu_tbl')
						->when($menuid!=0,function($query) use ($menuid){
							return $query->where('parentid','=',$menuid);
						})
						->when($menuid==0,function($query) use ($menuid){
							return $query->where('parentid','=',0);
						})
						->where('displayorder', '>=', $newDisplayOrder)
						->where('displayorder', '<', $oldDisplayOrder)
						->where('menuid','!=', $recordid)
						->increment('displayorder');
				}
				});				
			}
			
			if($menuid==0)
			$res = DB::update('update mainmenu_tbl set mastermenu=?,icon=?,displayorder=?,menuurl=?,hassubmenu=?,activevalue=?,passuserid=?,actionids=? where menuid=?',[$mastermenu,$icon,$displayorder,$menuurl,$hassubmenu,$activevalue,$passuserid,$actionids,$recordid]);
			else
			$res = DB::update('update mainmenu_tbl set parentid=?,mastermenu=?,icon=?,displayorder=?,menuurl=?,hassubmenu=?,activevalue=?,passuserid=?,actionids=? where menuid=?',[$menuid,$mastermenu,$icon,$displayorder,$menuurl,$hassubmenu,$activevalue,$passuserid,$actionids,$recordid]);
			
			if($res)
			{
				DB::delete('DELETE FROM menu_mapping WHERE menuid=?', [$recordid]);
				if($actionids!='')
				{
					$actions = explode(",",$actionids);
					foreach($actions as $rec)
					{
						DB::insert('insert into menu_mapping(menuid,actionid) values(?,?)',[$recordid,$rec]);
					}				
				}
				return redirect('/master/add/menu')->with('success','MENU NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','MENU NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }
	
    public function getMenuData(Request $request)
	{
		$usertype   =	$request->session()->get('userType');
		$parentid 	=	intval($request->input('parentid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= $request->input('page',1);

		$data 	= DB::table('mainmenu_tbl as a')
				->select('a.menuid','a.mastermenu','a.icon','a.displayorder','a.menuurl','a.hassubmenu','a.activevalue','a.passuserid','a.actionids','b.mastermenu as parentmenu',DB::raw("(SELECT GROUP_CONCAT(actionname) FROM menu_action WHERE FIND_IN_SET(actionid,a.actionids)) as actions"))
				->leftjoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
                ->when($parentid!=0,function($query) use ($parentid){
                    return $query->where('a.parentid','=',$parentid);
                })
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('a.mastermenu','like','%'.$pagesearch.'%');
                })
				->where('a.menufor','=',$usertype)
                ->orderBy('a.menuid')                
				->orderBy('a.parentid')
				->orderBy('a.displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);		

		return view('admin/ajaxpages/menuTable',['data'=>$data]);
    }
    public function editMenu($recordid)
	{
		$menus	=	DB::table('mainmenu_tbl')->where('hassubmenu','=',1)->orderby('displayorder')->get();
        $data = DB::table('mainmenu_tbl')->where('menuid','=',$recordid)->first();
		$actions=	DB::table('menu_action')->orderby('displayorder')->get();		
        return view('admin/master/menu_edit',compact('data','menus','actions'));
    }
    public function deleteMenu($recordid)
	{
        $res = DB::delete('DELETE FROM mainmenu_tbl WHERE menuid=?', [$recordid]);
        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* MENU RELATED CLOSED */
	/* ACTION RELATED */
    public function addAction(Request $request)
	{
        Session::put('adminmenu','menus');
		Session::put('adminsubmenu','addaction');
		$displayorder = DB::table('menu_action')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;	
        return view('admin/master/action_add',compact('displayorder'));
    }
    public function storeAction(Request $request,$recordid)
	{        
        $rules = [
            'actionname' => 'required|max:50',
			'displayorder' => 'required',
        ];

        $messages = [
            'actionname.required' => 'TAG LINE IS REQUIRED',
            'displayorder.required' => 'IT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');

		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$actionname		=	strtoupper($request->input('actionname'));
		$displayorder	=	intval($request->input('displayorder'));
		
		if($recordid==0)
		{
			$res = DB::insert('INSERT INTO menu_action(actionname,displayorder,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$actionname,$displayorder,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','ACTION NAME STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','ACTION NAME COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$newDisplayOrder = intval($request->input('displayorder'));
			$action	=	DB::table('menu_action')->where('actionid','=',$recordid)->first();
			$oldDisplayOrder = $action->displayorder;
			if($newDisplayOrder!=$oldDisplayOrder)
			{
				DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder, $recordid) {
				if($newDisplayOrder>$oldDisplayOrder)
				{
					DB::table('menu_action')
						->where('displayorder', '>', $oldDisplayOrder)
						->where('displayorder', '<=', $newDisplayOrder)
						->where('actionid','!=',$recordid)
						->decrement('displayorder');
				}
				else
				{
					DB::table('menu_action')
						->where('displayorder', '>=', $newDisplayOrder)
						->where('displayorder', '<', $oldDisplayOrder)
						->where('actionid','!=', $recordid)
						->increment('displayorder');
				}
				});				
			}
			
			$res = DB::update('update menu_action set actionname=?,displayorder=? where actionid=?',[$actionname,$displayorder,$recordid]);
			
			if($res)
			{
				return redirect('/master/add/action')->with('success','ACTION NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','ACTION NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }
	
    public function getActionData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= $request->input('page',1);

		$data 	= DB::table('menu_action')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('actionname','like','%'.$pagesearch.'%');
                })
				->orderBy('displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);		

		return view('admin/ajaxpages/actionTable',['data'=>$data]);
    }
    public function editAction($recordid)
	{
        $data = DB::table('menu_action')->where('actionid','=',$recordid)->first();
        return view('admin/master/action_edit',compact('data'));
    }
    public function deleteAction($recordid)
	{
        $res = DB::delete('DELETE FROM menu_action WHERE actionid=?', [$recordid]);
        if($res)
        {
			DB::delete('DELETE FROM menu_mapping WHERE actionid=?', [$recordid]);
			DB::delete('DELETE FROM menu_permission WHERE actionid=?', [$recordid]);
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* ACTION RELATED CLOSED */

    public function syncMenuPermission(Request $request)
	{
		$usertype     =	$request->session()->get('userType');
        Session::put('adminmenu','menus');
		Session::put('adminsubmenu','addmenu');
		
		try
		{
			$mainmenus	=	DB::table('mainmenu_tbl')
							->where('menufor','ADMIN')
							->where('isactive',1)
							->where('parentid',0)
							->where('menuurl','!=','')
							->where('hassubmenu','=',0)
							->orderBy('displayorder')
							->get();
			foreach($mainmenus as $menu)
			{
				$exists = DB::table('permissions_tbl')
							->where('route_name',$menu->menuurl)
							->exists();

				if(!$exists)
				{
					DB::table('permissions_tbl')->insert([
						'parentid'			=>	$menu->parentid,
						'menuid'			=>	$menu->menuid,
						'permission_name'	=>	__($menu->mastermenu),
						'route_name'		=>	$menu->menuurl,
						'permission_type'	=>	'MENU',
						'display_order'		=>	$menu->displayorder,
						'createdby'			=>	session('userid'),
						'creationdate'		=>	now()
					]);
				}
			}
			
			$mainmenus	=	DB::table('mainmenu_tbl')
							->where('menufor','ADMIN')
							->where('isactive',1)
							->where('parentid',0)
							->orderBy('displayorder')
							->get();
							
			foreach($mainmenus as $men)
			{
				$menus	=	DB::table('mainmenu_tbl')
							->where('parentid',$men->menuid)
							->where('menuurl','!=','')
							->where('hassubmenu','=',0)
							->where('menufor','=','ADMIN')
							->where('isactive',1)
							->orderBy('displayorder')
							->get();
				if($menus)
				{
					foreach($menus as $menu)
					{
						$exists = DB::table('permissions_tbl')
									->where('route_name',$menu->menuurl)
									->exists();

						if(!$exists)
						{
							DB::table('permissions_tbl')->insert([
								'parentid'			=>	$men->menuid,
								'menuid'			=>	$menu->menuid,
								'permission_name'	=>	__($menu->mastermenu),
								'route_name'		=>	$menu->menuurl,
								'permission_type'	=>	'MENU',
								'display_order'		=>	$menu->displayorder,
								'createdby'			=>	session('userid'),
								'creationdate'		=>	now()
							]);
						}
					}
				}
			}

			return redirect()->route('dashboard');
		}
		catch(Exception $e)
		{
			Log::error('Error '.$e->getMessage());
			
		}
    }

    public function addActionPermission(Request $request)
	{
		$usertype	=	$request->session()->get('userType');
        Session::put('adminmenu','menus');
		Session::put('adminsubmenu','addmenu');
		
		$menus	=	DB::table('permissions_tbl as a')
					->select('a.*','b.mastermenu')
					->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
					->where('permission_type','MENU')
					->orderBy('a.permissionid')
					->get();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/permissionpages/action_permission',compact('menus','token'));
    }
	
    public function storeActionPermission(Request $request,$recordid)
	{        
		$rules = [
			'permissionid'      =>	'required|exists:permissions_tbl,permissionid',
			'permission_name'   =>	'required|max:100',
			'route_name'        => 	'required|max:100',
			'permission_type'   => 	'required|in:ACTION,AJAX,LINK',
			'display_order' 	=> 	'required|numeric',
		];

		$messages = [
			'permissionid.required'     => 'Permission ID is required.',
			'permissionid.exists'       => 'Invalid permission details provided.',
			
			'permission_name.required'  => 'Permission name is required.',
			'permission_name.max'       => 'Permission name cannot exceed 100 characters.',
			
			'route_name.required'       => 'Route name is required.',
			'route_name.max'            => 'Route name cannot exceed 100 characters.',
			
			'permission_type.required'  => 'Permission type is required.',
			'permission_type.in'        => 'Invalid permission type. Allowed values are ACTION and AJAX.',

			'display_order.required'    => 'Display order is required.',
			'display_order.numeric'     => 'Display order must be a numeric value.',
		];
		
        $validatedData = $request->validate($rules, $messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return redirect('/master/add/actionpermission')->with('success','The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		$userId     =	$request->session()->get('userId');

		$permission	=	DB::table('permissions_tbl')->where('permissionid',$validatedData['permissionid'])->first();
		
		if($recordid==0)
		{

			
			DB::table('permissions_tbl')
			->insert([
				'parentid'			=>	$permission->parentid,
				'menuid'			=>	$permission->menuid,
				'permission_name'	=>	$validatedData['permission_name'] ?? NULL,
				'route_name'		=>	$validatedData['route_name'] ?? NULL,
				'permission_type'	=>	$validatedData['permission_type'] ?? NULL,
				'display_order'		=>	$validatedData['display_order'] ?? NULL,
				'isactive'			=>	1,
				'createdby'			=>	$userId,
				'creationdate'		=>	now()
			]);
			
			return back()->with('success','Action permission has been added successfully.');				
		}
		else
		{
			DB::table('permissions_tbl')
			->where('permissionid',$recordid)
			->update([
				'parentid'			=>	$permission->parentid,
				'menuid'			=>	$permission->menuid,
				'permission_name'	=>	$validatedData['permission_name'] ?? NULL,
				'route_name'		=>	$validatedData['route_name'] ?? NULL,
				'permission_type'	=>	$validatedData['permission_type'] ?? NULL,
				'display_order'		=>	$validatedData['display_order'] ?? NULL,
			]);
			
			return redirect('/master/add/actionpermission')->with('success','Action permission has been updated successfully.');
		}
    }

    public function getActionPermissionData(Request $request)
	{
		$menuid 	=	intval($request->input('menuid')) ?? 0;
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page',1);

		$data	=	DB::table('permissions_tbl as a')
					->select('a.*','b.mastermenu')
					->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
					->whereIn('permission_type',['ACTION','AJAX','LINK'])
					->when($menuid!=0,function($query) use ($menuid){
						return $query->where('a.menuid','=',$menuid);
					})
					->when($pagesearch!='', function ($query) use ($pagesearch) {
						$query->where(function ($q) use ($pagesearch) {
							$q->where('a.permission_name','like',"%$pagesearch%")
							  ->orWhere('a.route_name','like',"%$pagesearch%");
						});
					})
					->orderBy('a.display_order')
					->paginate($pagesize,['*'],'page',$currentPage);		


		return view('admin/permissionpages/ajaxpages/actionpermissionTable',['data'=>$data]);
    }

    public function editActionPermission($recordid)
	{
		$menus	=	DB::table('permissions_tbl as a')
					->select('a.*','b.mastermenu')
					->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
					->where('permission_type','MENU')
					->orderBy('a.permissionid')
					->get();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$data	=	DB::table('permissions_tbl as a')
					->select('a.*','b.mastermenu')
					->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
					->whereIn('a.permission_type',['ACTION','AJAX','LINK'])
					->where('a.permissionid',$recordid)
					->orderBy('a.permissionid')
					->first();

        return view('admin/permissionpages/edit_action_permission',compact('data','menus','token'));
    }
    public function deleteActionPermission($recordid)
	{
		DB::table('permissions_tbl')->where('permissionid',$recordid)->delete();
        return response()->json(['success' => true,'fail'=>'']);
    }

    public function addRolePermission(Request $request)
	{
		$usertype	=	$request->session()->get('userType');
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','maprolepermission');
		
		$roles	=	DB::table('role_tbl')->select('*')->orderBy('role')->get();

		$parents	=	DB::table('permissions_tbl as a')
						->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
						->select('a.*','b.mastermenu')
						->groupBy('a.parentid')
						->orderBy('a.parentid')
						->get();

		$token	=	rand('100000','999999').''.time();
		
		Session::put('form_token',$token);

        return view('admin/permissionpages/role_permission',compact('roles','token','parents'));
    }


    public function getRolePermissionData(Request $request)
	{
		$roleid 	=	intval($request->input('roleid')) ?? 0;
		$menuid 	=	intval($request->input('menuid')) ?? 0;
	
		$parents	=	DB::table('permissions_tbl as a')
						->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
						->select('a.*','b.mastermenu')
						->when($menuid!=0,function($query) use ($menuid){
							return $query->where('a.menuid','=',$menuid);
						})
						->groupBy('a.parentid')
						->orderBy('a.parentid')
						->get();
		
		foreach($parents as $parent)
		{
			$parent->submenus	=	DB::table('permissions_tbl as a')
									->leftJoin('role_permissions as b', function ($join) use ($roleid) {
										$join->on('a.permissionid','=','b.permissionid')
											 ->where('b.roleid','=',$roleid)
											 ->where('b.isactive','=',1);
									})
									->select(
										'a.*',
										DB::raw('CASE WHEN b.permissionid IS NULL THEN 0 ELSE 1 END as isAvailable')
									)
									->where('a.parentid',$parent->parentid)
									->where('a.permission_type','MENU')
									->get();

			
			foreach($parent->submenus as $submenu)
			{
				$submenu->actions	=	DB::table('permissions_tbl as a')
										->leftJoin('role_permissions as b', function ($join) use ($roleid) {
											$join->on('a.permissionid', '=', 'b.permissionid')
												 ->where('b.roleid', '=', $roleid)
												 ->where('b.isactive', '=', 1);
										})
										->select(
											'a.*',
											DB::raw('CASE WHEN b.permissionid IS NULL THEN 0 ELSE 1 END as isAvailable')
										)
										->whereNotIn('a.permission_type', ['MENU', 'LINK'])
										->where('a.menuid', $submenu->menuid)
										->orderBy('a.display_order')
										->get();
			}
		}
		
		return view('admin/permissionpages/ajaxpages/rolepermissionTable',['data'=>$parents,'roleid'=>$roleid]);
    }

	public function saveRolePermission(Request $request)
	{
		$roleid       	= 	intval($request->roleid);
		$permissionid 	= 	intval($request->permissionid);
		$isactive     	= 	intval($request->isactive) ?? 0;
		$apply_to		=	$request->apply_to ?? NULL;
		
		$exists = DB::table('role_permissions')
					->where('roleid', $roleid)
					->where('permissionid', $permissionid)
					->first();

		if($exists)
		{
			DB::table('role_permissions')
			->where('roleid', $roleid)
			->where('permissionid',$permissionid)
			->update([
				'isActive'	=>	$isactive
			]);

			if($apply_to=='ROEU')
			{
				$allow	=	NULL;
				if($isactive==1)
				{
					$allow	=	'ALLOW';
				}
				else
				{
					$allow	=	'DENY';
				}
				$users		=	DB::table('users_roles')->where('roleid',$roleid)->where('isactive',1)->get();
				foreach($users as $user)
				{
					$available	=	DB::table('users_permissions')
									->where('user_id',$user->userid)
									->where('permission_id',$permissionid)
									->first();
					if($available)
					{
						DB::table('users_permissions')
						->where('user_id',$user->userid)
						->where('permission_id',$permissionid)
						->update([
							'permission_status'	=>	$allow
						]);
					}
					else
					{
						DB::table('users_permissions')
						->insert([
							'user_id'			=>	$user->userid,
							'permission_id'		=>	$permissionid,
							'permission_status'	=>	'ALLOW'
						]);
						
					}
				}
			}
		}
		else
		{
			DB::table('role_permissions')
				->insert([
					'roleid'       	=> 	$roleid,
					'permissionid' 	=>	$permissionid,
					'isActive'     	=> 	1,
					'creationdate'	=>	now()
				]);
			
			if($apply_to=='ROEU')
			{
				$users		=	DB::table('users_roles')->where('roleid',$roleid)->where('isactive',1)->get();
				foreach($users as $user)
				{
					DB::table('users_permissions')
					->insert([
						'user_id'			=>	$user->userid,
						'permission_id'		=>	$permissionid,
						'permission_status'	=>	'ALLOW'
					]);
				}
			}
		}
		
		$permission	=	DB::table('permissions_tbl')->where('permissionid',$permissionid)->first();
		if($permission->permission_type=='MENU' && $isactive==0)
		{
			if($permission->parentid!=0)
			{
				$permissionIds	=	DB::table('permissions_tbl')
									->where('parentid',$permission->parentid)
									->where('menuid',$permission->menuid)
									->pluck('permissionid');
				DB::table('role_permissions')
				->where('roleid', $roleid)
				->whereIn('permissionid',$permissionIds)
				->update([
					'isActive'	=>	0
				]);
				
				if($apply_to=='ROEU')
				{
					$users		=	DB::table('users_roles')->where('roleid',$roleid)->where('isactive',1)->get();
					foreach($users as $user)
					{
						DB::table('users_permissions')
						->where('user_id',$user->userid)
						->whereIn('permission_id',$permissionIds)
						->update([
							'permission_status'	=>	'DENY'
						]);
					}
				}
			}
		}
		
		return response()->json([
			'status' => true
		]);
	}	
}
