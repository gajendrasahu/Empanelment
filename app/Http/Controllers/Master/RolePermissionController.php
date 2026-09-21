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
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Log;
use Hash;


class RolePermissionController extends Controller
{
	/*ROLE RELATED*/
    public function addRole(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addrole');
		Session::put('menid',38);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/master/role_add',compact('token'));
    }
    public function storeRole(Request $request,$recordid)
	{        
        $rules = [
            'role' 		=> 'required|max:100|regex:/^[a-zA-Z0-9\s.\-()]+$/',
        ];

        $messages = [
            'role.required'			=> 'ROLE IS REQUIRED',
            'role.max' 				=> 'MAXIMUM LENGTH IS 100',
			'role.regex' 				=> 'Special characters not allowed',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		if($recordid==0)
		{
			try
			{
				DB::table('role_tbl')->insert([
					'role'			=>	$validatedData['role'],
					'creationdate'	=>	now(),
					'createdby'		=>	$userId
				]);

				return back()->with('success','Record has been saved successfully.');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','Something went wrong. Please try again later.')->withInput();
			}			
		}
		else
		{
			try
			{
				DB::table('role_tbl')
				->where('roleid',$recordid)
				->update([
					'role'	=>	$validatedData['role']
				]);
				return redirect('/master/add/role')->with('success','Record has been updated successfully.');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','Something went wrong. Please try again later.')->withInput();
			}			
		}
    }
    public function editRole($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$data 		= 	DB::table('role_tbl')->where('roleid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/role_edit',compact('data','token'));
    }
	
    public function getRoleData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('role_tbl')
				->select('roleid','role','creationdate')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('role','like','%'.$pagesearch.'%');
                })
                ->orderBy('role')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/roleTable', ['data' => $data]);

    }
    public function deleteRole($recordid)
	{
		$res = true;//DB::delete('DELETE FROM role_tbl WHERE roleid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*ROLE RELATED CLOSED*/


	/*ROLE PERMISSION RELATED START */
    public function rolePermission(Request $request){        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','rolepermission');

		Session::put('menid',40);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=40 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action 	= 	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		$roles		= 	DB::table('role_tbl')->orderby('role')->get();
		$menus		=	DB::table('mainmenu_tbl')->where('hassubmenu','=',1)->where('isactive',1)->where('menufor','ADMIN')->orderby('displayorder')->get();
        return view('admin/master/role_permission',compact('roles','menus'));
    }
    public function getRolePermissionData(Request $request)
	{
		$roleid		=	intval($request->input('roleid'));
		$parentid	=	intval($request->input('parentid'));
	
		$records	=	DB::table('menu_mapping')->distinct('actionid')->count();
		$data 		=	DB::table('mainmenu_tbl')
							->when($parentid!=0,function($query) use ($parentid){
								return $query->where('menuid','=',$parentid);
							})			
							->when($parentid==0,function($query) use ($parentid){
								return $query->where('parentid','=',0);
							})
							->where('isactive',1)
							->where('ispermissible',1)
							->where('menufor','ADMIN')
							->orderby('displayorder')
							->get();
		foreach($data as $menu)
		{
			$menu->mapping	=	DB::table('menu_mapping as a')
								->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
								->leftjoin('menu_action as c','c.actionid','=','a.actionid')
								->where('a.menuid','=',$menu->menuid)
								->where('b.isactive','=',1)
								->where('b.menufor','ADMIN')
								->orderby('c.displayorder')
								->get();
			if($menu->mapping->count()==0)
			{
				$acids	=	DB::table('mainmenu_tbl')
								->select(DB::raw('GROUP_CONCAT(actionids) as ids'),DB::raw('GROUP_CONCAT(menuid) as mids'))
								->where('parentid','=',$menu->menuid)
								->where('isactive','=',1)
								->where('menufor','=','ADMIN')
								->first();

				$actionids		=	explode(",",$acids->ids);
				$actionids		=	array_unique($actionids);

				$mids			=	explode(",",$acids->mids);
				$mids			=	array_unique($mids);

				$menu->mapping	=	DB::table('menu_action')
										->wherein('actionid',$actionids)
										->orderby('displayorder')
										->get();
				
			}
			if($menu->menuurl!='' && $menu->parentid==0)
			{
				$menu->singlesubmenu = DB::table('mainmenu_tbl')
										->where('isactive',1)
										->where('menufor','ADMIN')
										->where('menuid','=',$menu->menuid)
										->orderby('displayorder')
										->get();
										
				foreach($menu->singlesubmenu as $singlesubmenu)
				{
					$singlesubmenu->mapping	=	DB::table('menu_mapping as a')
											->select('a.mappingid','c.actionid','c.actionname',DB::raw('IF(d.actionid IS NULL, 0, 1) as ispermitted'))
											->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
											->leftjoin('menu_action as c','c.actionid','=','a.actionid')
											->leftJoin('role_menu_permission as d', function($join) use ($roleid, $menu) {
												$join->on('d.actionid', '=', 'c.actionid')
													 ->on('d.menuid', '=', 'b.menuid')
													 ->where('d.roleid','=', $roleid)
													 ->where('d.ispermitted','=',1);
											})
											->where('a.menuid','=',$singlesubmenu->menuid)
											->where('b.isactive','=',1)
											->where('b.menufor','=','ADMIN')
											->orderby('c.displayorder')
											->get();
					//dd($submenu->mapping);
				}				
			}
			else
			{
				$menu->singlesubmenu	=	[];
			}
			
			$menu->submenu = DB::table('mainmenu_tbl')
							->where('parentid','=',$menu->menuid)
							->where('isactive',1)
							->where('menufor','ADMIN')
							->orderby('displayorder')
							->get();
							
			foreach($menu->submenu as $submenu)
			{
				$submenu->mapping	=	DB::table('menu_mapping as a')
										->select('a.mappingid','c.actionid','c.actionname',DB::raw('IF(d.actionid IS NULL, 0, 1) as ispermitted'))
										->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
										->leftjoin('menu_action as c','c.actionid','=','a.actionid')
										->leftJoin('role_menu_permission as d', function($join) use ($roleid, $menu) {
											$join->on('d.actionid', '=', 'c.actionid')
												 ->on('d.menuid', '=', 'b.menuid')
												 ->where('d.roleid','=', $roleid)
												 ->where('d.ispermitted','=',1);
										})
										->where('a.menuid','=',$submenu->menuid)
										->where('b.isactive','=',1)
										->where('b.menufor','=','ADMIN')
										->orderby('c.displayorder')
										->get();
				//dd($submenu->mapping);
			}
		}
		$role	=	DB::table('role_tbl')->where('roleid','=',$roleid)->first();
		$role=	$role->role;
		return view('admin/ajaxpages/rolepermissionTable',compact('data','roleid','role','records'));
    }
    public function storeRolePermission(Request $request,$recordid)
	{        
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');

		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$uid		=	$request->input('uid_'.$recordid);
		$mapping	=	DB::table('menu_mapping')->where('mappingid','=',$recordid)->first();
		
		
		$exists	=	DB::table('role_menu_permission')
					->where('roleid','=',$uid)
					->where('menuid','=',$mapping->menuid)
					->where('actionid','=',$mapping->actionid)
					->get();
		if($exists->count()==0)
		{
			$remark	=	"PERMISSION GRANTED BY ".$userName." ON ".date('d\-m\-Y h:i A')."<br>";
			DB::insert('insert into role_menu_permission(roleid,menuid,actionid,ispermitted,remark) values(?,?,?,?,?)',[$uid,$mapping->menuid,$mapping->actionid,1,$remark]);
			$check	=	DB::table('role_mainmenu')
						->where('menuid','=',$mapping->menuid)
						->where('roleid','=',$uid)
						->get();
			if($check->count()==0)
			{
				$menu	=	DB::table('mainmenu_tbl')
							->where('menuid','=',$mapping->menuid)
							->first();
				DB::insert('insert into role_mainmenu(menuid,parentid,mastermenu,icon,displayorder,menuurl,hassubmenu,activevalue,passuserid,actionids,roleid,ispermitted) values(?,?,?,?,?,?,?,?,?,?,?,?)',[$menu->menuid,$menu->parentid,$menu->mastermenu,$menu->icon,$menu->displayorder,$menu->menuurl,$menu->hassubmenu,$menu->activevalue,$menu->passuserid,$menu->actionids,$uid,1]);
				if($menu->parentid!=0)
				{
					$parent =	DB::table('role_mainmenu')
									->where('menuid','=',$menu->parentid)
									->where('roleid','=',$uid)
									->get();								
					if($parent->count()==0)
					{
						$mn = DB::table('mainmenu_tbl')
								->where('menuid','=',$menu->parentid)
								->first();
						DB::insert('insert into role_mainmenu(menuid,parentid,mastermenu,icon,displayorder,menuurl,hassubmenu,activevalue,passuserid,actionids,roleid,ispermitted) values(?,?,?,?,?,?,?,?,?,?,?,?)',[$mn->menuid,$mn->parentid,$mn->mastermenu,$mn->icon,$mn->displayorder,$mn->menuurl,$mn->hassubmenu,$mn->activevalue,$mn->passuserid,$mn->actionids,$uid,1]);
					}
				}
			}
			else
			{
				foreach($check as $data)
				{
					DB::update('update role_mainmenu set ispermitted=? where roleid=? and menuid=?',[1,$uid,$data->menuid]);
					if($data->parentid!=0)
					{
						$parent	=	DB::table('role_mainmenu')->where('menuid','=',$data->parentid)->get();
						if($parent->count()>0)
						{
							DB::update('update role_mainmenu set ispermitted=? where roleid=? and menuid=?',[1,$uid,$data->parentid]);
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
					DB::update('update role_menu_permission set ispermitted=?,remark=? where roleid=? and menuid=? and actionid=?',[0,$remark,$uid,$mapping->menuid,$mapping->actionid]);
				}
				else
				{
					$remark	=	$data->remark."PERMISSION GRANTED BY ".$userName." ON ".date('d\-m\-Y h:i A')."<br>";
					DB::update('update role_menu_permission set ispermitted=?,remark=? where roleid=? and menuid=? and actionid=?',[1,$remark,$uid,$mapping->menuid,$mapping->actionid]);
				}
			}
			$count	=	DB::table('role_menu_permission')
						->where('menuid','=',$mapping->menuid)
						->where('ispermitted','=',1)
						->where('roleid','=',$uid)
						->get();
						
			if($count->count()==0)
			{
				DB::update('update role_mainmenu set ispermitted=? where menuid=? and roleid=?',[0,$mapping->menuid,$uid]);
				
				$men	=	DB::table('mainmenu_tbl')
							->where('menuid','=',$mapping->menuid)
							->first();
							
							
				$mids	=	DB::table('mainmenu_tbl')
							->select(DB::raw('GROUP_CONCAT(menuid) as ids'))
							->where('parentid','=',$men->parentid)
							->first();
							
				$ids = $mids ? $mids->ids : null;
				if($mids->ids!='')
				{
					$menids			=	explode(",",$mids->ids);
					$isavailable	=	DB::table('role_mainmenu')->where('ispermitted','=',1)->whereIn('menuid',$menids)->get();
					if($isavailable->count()==0)
					{
						DB::update('update role_mainmenu set ispermitted=? where menuid=? and roleid=?',[0,$men->parentid,$uid]);
					}
				}
			}
			else
			{
				DB::update('update role_mainmenu set ispermitted=? where menuid=? and roleid=?',[1,$mapping->menuid,$uid]);
				$men	=	DB::table('mainmenu_tbl')->where('menuid','=',$mapping->menuid)->first();
				DB::update('update role_mainmenu set ispermitted=? where menuid=? and roleid=?',[1,$men->parentid,$uid]);
			}
		}
		
		return response()->noContent();
    }
	
	/*ROLE PERMISSION RELATED CLOSED */
    
}
