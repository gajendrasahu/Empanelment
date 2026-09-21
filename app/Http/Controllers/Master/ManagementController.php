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
use Illuminate\Support\Facades\Mail;
use App\Mail\LoginCredentialMail;

class ManagementController extends Controller
{
	protected $passwordService;
    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }
	
	/* USER RELATED START */
    public function addUser(Request $request)
	{
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','adduser');

		$userId		= $request->session()->get('userId');
		$userName	= $request->session()->get('userName');
		$issuper	= $request->session()->get('issuper');
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$roles	=	DB::table('role_tbl')->orderBy('role')->get();
        return view('admin/master/user_add',compact('roles','token'));
    }

    public function storeUser(Request $request,$recordid)
	{
        
        $rules = [
			'name' => 'required|max:150|regex:/^[a-zA-Z0-9\s.\(\)\[\]]+$/',
			'mobilenumber'       => [
				'nullable',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'email'       => [
				'required',
				'email:rfc,dns'
			],
        ];
		if((int) $recordid===0)
		{
			$rules['mobilenumber'][] = Rule::unique('users_tbl', 'mobilenumber');
		}
        $messages = [
			'name.required' 		=> 	'The name field is required.',
			'name.max' 				=> 	'A maximum of 100 characters is allowed.',
			'name.regex' 			=> 	'Special characters are not permitted in the name.',
			'mobilenumber.required'	=> 	'The mobile number is required.',
			'mobilenumber.regex'  	=> 	'The mobile number format is invalid.',
			'mobilenumber.digits' 	=> 	'The mobile number must contain the required number of digits.',
			'mobilenumber.unique' 	=> 	'This mobile number is already registered.',
			'email.required'      	=> 	'The email address is required.',
			'email.email'         	=> 	'The email address format is invalid.',
			'email.unique'        	=> 	'This email address is already registered.',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		
		if($recordid==0)
		{
			try 
			{
				$plainPassword	= 	$this->passwordService->generatePassword();
				$hashedPassword =	Hash::make($plainPassword); // Encrypt password
				
				DB::table('users_tbl')->insert([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'],
					'email'			=>	$validatedData['email'],
					'isadmin'		=>	1,
					'createdby'		=>	$userId,
					'created_at'	=>	now(),
					'usertype'		=>	'ADMIN',
					'password' 		=> 	$hashedPassword,
					'pass_word' 	=> 	$plainPassword,
					'issuper'		=>	1,
					'isuser'		=>	1,
				]);
				$weblink	=	"https://empl.cgstate.gov.in";
				
				/*
				Mail::to($validatedData['email'])->send(new LoginCredentialMail($validatedData['name'],$validatedData['email'],$plainPassword,$weblink));
				*/
				
				return back()->with('success','The user details have been stored successfully.');
			}
			catch (QueryException $e) 
			{
				Log::error('Error'.$e->getMessage());
				return back()->with('duplicate','Something went wrong. Please try again.')->withInput();
			}
		}
		else
		{
			try
			{
				DB::table('users_tbl')
				->where('userid',$recordid)
				->update([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'],
					'email'			=>	$validatedData['email'],
					'roleid'		=>	$validatedData['roleid'],
				]);
				return redirect('master/add/user')->with('success','The user details have been updated successfully.');
			}
			catch (QueryException $e) 
			{			
				return back()->with('duplicate','Something went wrong. Please try again.')->withInput();
			}
			
		}
    }
    public function getUserData(Request $request)
	{
		$roleid			=	intval($request->input('roleid'));
		$pagesearch 	=	$request->input('pagesearch');

		$pagesize		=	$request->input('pagesize');
		$currentPage	=	$request->input('page', 1);
        
		try
		{
			$data = DB::table('users_tbl as a')
					->select('a.*')
					->when($pagesearch!='', function($query) use ($pagesearch){
						return $query->where(function($q) use ($pagesearch){
							$q->where('a.name','like','%'.$pagesearch.'%')
							  ->orWhere('a.mobilenumber','like','%'.$pagesearch.'%')
							  ->orWhere('a.email','like','%'.$pagesearch.'%');
						});
					})
					->whereNull('a.isSubVendor')
					->where('a.isdepartment',0)
					->where('a.isvendor',0)
					->where('a.ispm',0)
					->where('a.name','!=','')
					->whereNotNull('a.name')
					->whereNotNull('a.email')
					->orderBy('a.name')
					->paginate($pagesize,['*'],'page',$currentPage);	
			
		
			return view('/admin/ajaxpages/usersTable', compact('data'))->render();
			
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
		}
    }
    public function editUser(Request $request,$recordid)
	{
		$issuper	= 	$request->session()->get('issuper');
		
		$userId		= 	$request->session()->get('userId');
		
		$data 		= 	DB::table('users_tbl')
						->where('userid','=',Crypt::decrypt($recordid))
						->first();
		
		$roles		=	DB::table('role_tbl')->orderby('role')->get();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

        return view('admin/master/user_edit',compact('data','roles','token'));
    }
    public function deleteUser($recordid)
	{
		$content = DB::table('applicationusers')->where('userid','=',Crypt::decrypt($recordid))->first();
		if($content->profilepic!='')
		{
			Storage::disk('public')->delete($content->profilepic);
		}	
		$res = DB::delete('delete from applicationusers WHERE userid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	/* USER RELATED CLOSED */
	
	/* PERMISSION RELATED */
    public function addPermission(Request $request)
	{
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','addpermission');
		
		$users = DB::table('users_tbl')->where('isadmin','=',1)->where('issuper','!=',1)->get();
		
		$menus	=	DB::table('mainmenu_tbl')
					->where('hassubmenu','=',1)
					->where('isactive',1)
					->where('menufor','ADMIN')
					->orderby('displayorder')
					->get();
        
		return view('admin/master/permission_add',compact('users','menus'));
    }
    public function getPermissionData(Request $request)
	{
		$userid		=	intval($request->input('userid'));
		$parentid	=	intval($request->input('parentid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page',1);
		
		$records=	DB::table('menu_mapping')->distinct('actionid')->count();
		
		$data 	=	DB::table('mainmenu_tbl')
						->when($parentid!=0,function($query) use ($parentid){
							return $query->where('menuid','=',$parentid);
						})			
						->when($parentid==0,function($query) use ($parentid){
							return $query->where('parentid','=',0);
						})
						->where('isactive',1)
						->where('menufor','ADMIN')
						->where('ispermissible',1)
						->orderby('displayorder')
						->get();

		foreach($data as $menu)
		{
			$menu->mapping	=	DB::table('menu_mapping as a')
								->leftjoin('mainmenu_tbl as b','b.menuid','=','a.menuid')
								->leftjoin('menu_action as c','c.actionid','=','a.actionid')
								->where('a.menuid','=',$menu->menuid)
								->where('b.isactive','=',1)
								->where('b.menufor','=','ADMIN')
								->where('b.ispermissible',1)
								->orderby('c.displayorder')
								->get();
			if($menu->mapping->count()==0)
			{
				$acids	=	DB::table('mainmenu_tbl')
								->select(DB::raw('GROUP_CONCAT(actionids) as ids'),DB::raw('GROUP_CONCAT(menuid) as mids'))
								->where('parentid','=',$menu->menuid)
								->where('isactive','=',1)
								->where('menufor','=','ADMIN')
								->where('ispermissible',1)
								->first();

				$actionids		=	explode(",",$acids->ids);
				$actionids		=	array_unique($actionids);

				$mids		=	explode(",",$acids->mids);
				$mids		=	array_unique($mids);

				$menu->mapping	=	DB::table('menu_action')
										->whereIn('actionid',$actionids)
										->orderby('displayorder')
										->get();
				
			}
			if($menu->menuurl!='' && $menu->parentid==0)
			{
				$menu->singlesubmenu = DB::table('mainmenu_tbl')
										->where('menuid','=',$menu->menuid)
										->where('isactive',1)
										->where('menufor','ADMIN')
										->where('ispermissible',1)
										->orderby('displayorder')
										->get();
										
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
											->where('b.isactive','=',1)
											->where('b.menufor','=','ADMIN')
											->where('b.ispermissible',1)
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
							->where('isactive','=',1)
							->where('menufor','=','ADMIN')
							->where('ispermissible',1)
							->orderby('displayorder')
							->get();
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
										->where('b.isactive','=',1)
										->where('b.menufor','=','ADMIN')
										->where('b.ispermissible',1)
										->orderby('c.displayorder')
										->get();
				//dd($submenu->mapping);
			}
		}
		
		return view('admin/ajaxpages/permissionTable',compact('data','userid','records'));
    }
    public function storePermission(Request $request,$recordid)
	{        
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');

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
	
    public function getDepartmentsList(Request $request)
	{
		
		$branchIds = explode(',', $request->input('branchids'));
		$allDepartments = [];
		
		foreach($branchIds as $branchId) 
		{
			
			$branch = DB::table('branch_tbl')->where('branchid','=',$branchId)->first();
			
			if ($branch) {
				$departments = explode(',', $branch->departmentids);
				$allDepartments = array_merge($allDepartments, $departments);
			}
		}
		$combinedDepartments = array_unique($allDepartments);
		$departments = DB::table('department_tbl')->select('departmentid as value','departmentname as label')->whereIn('departmentid',$combinedDepartments)->get();
        return response()->json($departments);
    }
	
	/* USER RELATED CLOSED */

	/*BRANCH RELATED START */
    public function addBranch(Request $request){        
        Session::put('adminmenu','management');
		Session::put('adminsubmenu','addbranch');

		Session::put('menid',39);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=39 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
		$state			=	DB::table('state_tbl')->orderby('statename')->get();
		$city			=	DB::table('city_tbl')->orderby('cityname')->get();
		$operatingcity	=	DB::table('city_tbl')->where('operatingstatus','=',1)->orderby('cityname')->get();
		
		$addstate	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',6)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		$addcity	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',7)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		if ($addstate === null) {
			$addstate = new \stdClass();
			$addstate->ispermitted = 0;
		}
		if ($addcity === null) {
			$addcity = new \stdClass();
			$addcity->ispermitted = 0;
		}
		if($issuper==1)
		{
			$addstate->ispermitted= 1;
			$addcity->ispermitted = 1;
		}
		
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$categories	= 	DB::table('category_tbl')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->orderBy('displayorder')
							->get();
		
        return view('admin/master/branch_add',compact('state','city','addstate','addcity','operatingcity','departments','categories'));
    }
    public function storeBranch(Request $request,$recordid){        
        
        $rules = [
            'branchname' 		=> 'required|max:50',
			'branchcode' 		=> 'required|max:10',
			'contact1' 			=> 'required|numeric|digits_between:10,12',
			'contact2'			=> 'nullable|numeric|max:12',
			'branchemail' 		=> 'required|email',
			'stateid' 			=> 'required',
			'cityid' 			=> 'required',
			'district' 			=> 'required',
			'postalcode' 		=> 'required|numeric',
			'completeaddress' 	=> 'required',
			'operatingcityid' 	=> 'required',
			'departmentids'    	=> 'required|regex:/^(?!,)(?:\d+,)*\d+$/',
			'categoryids'    	=> 'required|regex:/^(?!,)(?:\d+,)*\d+$/',			
        ];

        $messages = [
            'branchname.required' 		=> __('validation.thisis.required'),
			'branchname.max' 			=> __('validation.thisis50.max'),
            'branchcode.required' 		=> __('validation.thisis.required'),
			'branchcode.max' 			=> __('validation.thisis10.max'),
            'contact1.required' 		=> __('validation.thisis.required'),
			'contact1.max' 				=> __('validation.thisis12.max'),
			'contact1.numeric' 			=> __('validation.thisis.numeric'),
            'branchemail.required' 		=> __('validation.thisis.required'),
			'branchemail.email' 		=> __('validation.thisis.email'),
			'stateid.required' 			=> __('validation.thisis.required'),
			'cityid.required' 			=> __('validation.thisis.required'),
			'district.required' 		=> __('validation.thisis.required'),
			'postalcode.required' 		=> __('validation.thisis.required'),
			'postalcode.numeric' 		=> __('validation.thisis.numeric'),
			'completeaddress.required' 	=> __('validation.thisis.required'),
			'operatingcityid.required' 	=> __('validation.thisis.required'),
			'departmentids.required' 	=> __('validation.thisis.required'),
			'departmentids.regex' 		=> __('validation.thisis.invalidformat'),
			'categoryids.required' 		=> __('validation.thisis.required'),
			'categoryids.regex' 		=> __('validation.thisis.invalidformat'),			
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$branchname		= (String) ($request->input('branchname'));
		$branchcode		= (String) ($request->input('branchcode'));
		$contact1		= (String) ($request->input('contact1'));
		$contact2		= (String) ($request->input('contact2'));
		$branchemail	= (String) $request->input('branchemail');
		$branchpannumber= (String) $request->input('branchpannumber');
		$branchgstnumber= (String) $request->input('branchgstnumber');
		$stateid		= intval($request->input('stateid'));
		$cityid			= intval($request->input('cityid'));
		$district		= (String) $request->input('district');
		$postalcode		= (String) $request->input('postalcode');
		$completeaddress= (String) $request->input('completeaddress');
		$bankname		= (String) $request->input('bankname');
		$bankaccount	= (String) $request->input('bankaccount');
		$ifsccode		= (String) $request->input('ifsccode');
		$bankbranch		= (String) $request->input('bankbranch');		
		$operatingcityid= intval($request->input('operatingcityid'));
		$departmentids	= (String) $request->input('departmentids');
		$categoryids	= (String) $request->input('categoryids');
		
		if($recordid==0)
		{
			try 
			{
				DB::insert('insert into branch_tbl (branchname,branchcode,contact1,contact2,branchemail,branchpannumber,branchgstnumber,stateid,cityid,district,postalcode,completeaddress,bankname,bankaccount,ifsccode,bankbranch,operatingcityid,departmentids,categoryids,createdby,createdbyname,creationdate) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$branchname,$branchcode,$contact1,$contact2,$branchemail,$branchpannumber,$branchgstnumber,$stateid,$cityid,$district,$postalcode,$completeaddress,$bankname,$bankaccount,$ifsccode,$bankbranch,$operatingcityid,$departmentids,$categoryids,$userId,$userName,$creationdate]);
				
				return back()->with('success',__('messages.stored'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			
			//$cont = DB::table('service_tbl')->where('serviceid',$recordid)->first();
			$updateData = [];
			if(!empty($branchname))
			{
				$updateData['branchname'] = $branchname;
			}
			if(!empty($branchcode))
			{
				$updateData['branchcode'] = $branchcode;
			}
			if(!empty($contact1))
			{
				$updateData['contact1'] = $contact1;
			}
			if(!empty($contact2))
			{
				$updateData['contact2'] = $contact2;
			}
			if(!empty($branchemail))
			{
				$updateData['branchemail'] = $branchemail;
			}
			if(!empty($branchpannumber))
			{
				$updateData['branchpannumber'] = $branchpannumber;
			}
			if(!empty($branchgstnumber))
			{
				$updateData['branchgstnumber'] = $branchgstnumber;
			}
			if(!empty($stateid))
			{
				$updateData['stateid'] = $stateid;
			}
			if(!empty($cityid))
			{
				$updateData['cityid'] = $cityid;
			}
			if(!empty($district))
			{
				$updateData['district'] = $district;
			}
			if(!empty($postalcode))
			{
				$updateData['postalcode'] = $postalcode;
			}
			if(!empty($completeaddress))
			{
				$updateData['completeaddress'] = $completeaddress;
			}
			if(!empty($bankname))
			{
				$updateData['bankname'] = $bankname;
			}
			if(!empty($bankaccount))
			{
				$updateData['bankaccount'] = $bankaccount;
			}
			if(!empty($ifsccode))
			{
				$updateData['ifsccode'] = $ifsccode;
			}
			if(!empty($bankbranch))
			{
				$updateData['bankbranch'] = $bankbranch;
			}
			if(!empty($operatingcityid))
			{
				$updateData['operatingcityid'] = $operatingcityid;
			}
			if(!empty($departmentids))
			{
				$updateData['departmentids'] = $departmentids;
			}
			if(!empty($categoryids))
			{
				$updateData['categoryids'] = $categoryids;
			}
			try
			{
				DB::table('branch_tbl')->where('branchid', $recordid)->update($updateData);
				return redirect('master/add/branch')->with('success',__('messages.updated'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
			
		}
    }
    public function getBranchData(Request $request)
	{
		$stateid 			=	intval($request->input('stateid'));
		$cityid				=	intval($request->input('cityid'));
		$operatingcityid	=	intval($request->input('operatingcityid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage=	$request->input('page', 1);
        
		$data = DB::table('branch_tbl as a')
				->select('a.*','b.statename','c.cityname','d.cityname as operatingcityname',DB::raw("(SELECT GROUP_CONCAT('<li>',departmentname ORDER BY departmentname SEPARATOR '</li>') FROM department_tbl WHERE FIND_IN_SET(departmentid, a.departmentids)) as departments"),DB::raw("(SELECT GROUP_CONCAT('<li>',category ORDER BY displayorder SEPARATOR '</li>') FROM category_tbl WHERE FIND_IN_SET(categoryid, a.categoryids)) as categories"))
				->leftJoin('state_tbl as b', 'b.stateid', '=', 'a.stateid')
				->leftJoin('city_tbl as c', 'c.cityid', '=', 'a.cityid')
				->leftJoin('city_tbl as d', 'd.cityid', '=', 'a.operatingcityid')
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($cityid!=0,function($query) use ($cityid){
                    return $query->where('a.cityid','=',$cityid);
                })
                ->when($operatingcityid!=0,function($query) use ($operatingcityid){
                    return $query->where('a.operatingcityid','=',$operatingcityid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.branchname','like','%'.$pagesearch.'%')
								 ->orwhere('a.branchcode','like','%'.$pagesearch.'%')
								 ->orwhere('a.branchemail','like','%'.$pagesearch.'%')
								 ->orwhere('a.branchgstnumber','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.creationdate')
				->paginate($pagesize,['*'],'page',$currentPage);	
		
		
		return view('/admin/ajaxpages/branchTable', compact('data'))->render();
    }
    public function editBranch(Request $request,$recordid)
	{
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
		$issuper		= $request->session()->get('issuper');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		
		$state			=	DB::table('state_tbl')->orderby('statename')->get();
		
		$operatingcity	=	DB::table('city_tbl')->where('operatingstatus','=',1)->orderby('cityname')->get();
		
		$addstate	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',6)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		$addcity	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',7)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		if ($addstate === null) {
			$addstate = new \stdClass();
			$addstate->ispermitted = 0;
		}
		if ($addcity === null) {
			$addcity = new \stdClass();
			$addcity->ispermitted = 0;
		}
		if($issuper==1)
		{
			$addstate->ispermitted= 1;
			$addcity->ispermitted = 1;
		}
		
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$categories	= 	DB::table('category_tbl')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->orderBy('displayorder')
							->get();

        $data = DB::table('branch_tbl')->where('branchid','=',Crypt::decrypt($recordid))->first();
		
		$city			=	DB::table('city_tbl')->where('stateid','=',$data->stateid)->orderby('cityname')->get();
        return view('admin/master/branch_edit',compact('data','state','city','addstate','addcity','operatingcity','departments','categories'));
    }
	
	public function deleteBranch($recordid)
	{
		$res = DB::delete('DELETE FROM branch_tbl WHERE branchid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
	}
	
	/*BRANCH RELATED CLOSED */


	/*DESIGNATION RELATED START */
    public function addDesignation(Request $request){        
        Session::put('adminmenu','management');
		Session::put('adminsubmenu','adddesignation');

		Session::put('menid',38);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=38 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/designation_add');
    }
    public function storeDesignation(Request $request,$recordid){        
        
        $rules = [
			'designationname' => 'required|max:150',
			'aliasname' => 'required|max:10',
        ];

        $messages = [
            'designationname.required' => 'DESGINATION NAME IS REQUIRED',
			'designationname.max' => 'MAXIMUM LENGTH REACHED',
            'aliasname.required' => 'ALIAS NAME IS REQUIRED',
			'aliasname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$designationname= strtoupper($request->input('designationname'));
		$aliasname  	= strtoupper($request->input('aliasname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into designation_tbl (designationname,aliasname,creationdate,createdby,createdbyname) values (?,?,?,?,?)',[$designationname,$aliasname,$creationdate,$userId,$userName]);

				return back()->with('success',__('messages.stored'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('designation_tbl')->where('designationid',$recordid)->first();
			$updateData = [];
			if(!empty($designationname))
			{
				$updateData['designationname'] = $designationname;
				$updateData['aliasname'] = $aliasname;
			}
			try
			{
				$res = DB::table('designation_tbl')->where('designationid', $recordid)->update($updateData);
				return redirect('master/add/designation')->with('success',__('messages.updated'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
			
		}
    }
    public function getDesignationData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('designation_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('designationname','like','%'.$pagesearch.'%')
					             ->orwhere('aliasname','like','%'.$pagesearch.'%');
                })
				->orderby('designationname')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/designationTable', compact('data'))->render();
    }
    public function editDesignation($recordid)
	{
        $data = DB::table('designation_tbl')->where('designationid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/designation_edit',compact('data'));
    }
    public function deleteDesignation($recordid)
	{
		$res = DB::delete('delete from designation_tbl WHERE designationid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/*DESIGNATION RELATED CLOSED*/
	
	
	/*DEPARTMENT RELATED*/
    public function addDepartment(Request $request){        
        Session::put('adminmenu','management');
		Session::put('adminsubmenu','adddepartment');

		Session::put('menid',37);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=37 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/department_add');
    }
    public function storeDepartment(Request $request,$recordid){        
        
        $rules = [
			'departmentname' => 'required|max:30',
        ];

        $messages = [
            'departmentname.required' => 'DEPARTMENT NAME IS REQUIRED',
			'departmentname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$departmentname  	= strtoupper($request->input('departmentname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into department_tbl (departmentname,creationdate,createdby,createdbyname) values (?,?,?,?)',[$departmentname,$creationdate,$userId,$userName]);

				return back()->with('success',__('messages.stored'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('department_tbl')->where('departmentid',$recordid)->first();
			$updateData = [];
			if(!empty($departmentname))
			{
				$updateData['departmentname'] = $departmentname;
			}
			try
			{
				$res = DB::table('department_tbl')->where('departmentid', $recordid)->update($updateData);
				return redirect('master/add/department')->with('success',__('messages.updated'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
			
		}
    }
    public function getDepartmentData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('department_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('departmentname','like','%'.$pagesearch.'%');
                })
				->orderby('departmentname')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/departmentTable', compact('data'))->render();
    }
    public function editDepartment($recordid)
	{
        $data = DB::table('department_tbl')->where('departmentid','=',$recordid)->first();
        return view('admin/master/department_edit',compact('data'));
    }
    public function deleteDepartment($recordid)
	{
		$res = DB::delete('delete from department_tbl WHERE departmentid=?',[$recordid]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/*DEPARTMENT RELATED CLOSED*/

    public function setUserPermission(Request $request,$userid)
	{
		
		$user_Id= 	$request->session()->get('userId');
		$userid	=	Crypt::decrypt($userid);
		$user	=	DB::table('users_tbl')->where('userid',$userid)->first();
		
		$roles	=	DB::table('role_tbl as a')
					->leftJoin('users_roles as b', function ($join) use ($userid) {
					$join->on('b.roleid', '=', 'a.roleid')
						 ->where('b.userid', '=',$userid)
						 ->where('b.isactive', '=',1);
					})
					->select(
					'a.*',
					DB::raw('CASE WHEN b.roleid IS NULL THEN 0 ELSE 1 END as isAvailable')
					)
					->orderBy('a.role')
					->get();
					
		$parents	=	DB::table('permissions_tbl as a')
						->leftJoin('mainmenu_tbl as b','b.menuid','=','a.parentid')
						->select('a.*','b.mastermenu')
						->groupBy('a.parentid')
						->orderBy('a.parentid')
						->get();

        return view('admin/permissionpages/user_permission',compact('roles','user','parents'));
    }

    public function getUserPermissionData(Request $request)
	{
		$roleid 	=	$request->input('roleids') ?? NULL;
		$roleids	=	explode(",",$roleid);
		$menuid 	=	intval($request->input('menuid')) ?? 0;
		$userid 	=	intval($request->input('userid')) ?? 0;
	
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
									->leftJoin('users_permissions as b', function ($join) use ($userid) {
										$join->on('a.permissionid','=','b.permission_id')
											 ->where('b.user_id',$userid)
											 ->where('b.permission_status','=','ALLOW');
									})
									->select(
										'a.*',
										DB::raw('CASE WHEN b.permission_id IS NULL THEN 0 ELSE 1 END as isAvailable')
									)
									->where('a.parentid',$parent->parentid)
									->where('a.permission_type','MENU')
									->get();

			
			foreach($parent->submenus as $submenu)
			{
				$submenu->actions	=	DB::table('permissions_tbl as a')
										->leftJoin('users_permissions as b', function ($join) use ($userid) {
											$join->on('a.permissionid', '=', 'b.permission_id')
												 ->where('b.user_id',$userid)
												 ->where('b.permission_status', '=', 'ALLOW');
										})
										->select(
											'a.*',
											DB::raw('CASE WHEN b.permission_id IS NULL THEN 0 ELSE 1 END as isAvailable')
										)
										->whereNotIn('a.permission_type', ['MENU', 'LINK'])
										->where('a.menuid', $submenu->menuid)
										->orderBy('a.display_order')
										->get();
			}
		}
		
		return view('admin/permissionpages/ajaxpages/userpermissionTable',['data'=>$parents,'userid'=>$userid]);
    }

	public function saveUserPermission(Request $request)
	{
		$userid       	= 	intval($request->userid);
		$permissionid 	= 	intval($request->permissionid);
		$isactive 		= 	intval($request->isactive) == 1 ? 'ALLOW' : 'DENY';
		
	
		$exists	=	DB::table('users_permissions')
					->where('user_id',$userid)
					->where('permission_id',$permissionid)
					->first();

		if($exists)
		{
			DB::table('users_permissions')
			->where('user_id', $userid)
			->where('permission_id',$permissionid)
			->update([
				'permission_status'	=>	$isactive
			]);
		}
		else
		{
			DB::table('users_permissions')
				->insert([
					'user_id'       	=> 	$userid,
					'permission_id' 	=>	$permissionid,
					'permission_status' => 	'ALLOW',
				]);
			
		}
		
		$permission	=	DB::table('permissions_tbl')->where('permissionid',$permissionid)->first();
		if($permission->permission_type=='MENU' && $isactive=='DENY')
		{
			if($permission->parentid!=0)
			{
				$permissionIds	=	DB::table('permissions_tbl')
									->where('parentid',$permission->parentid)
									->where('menuid',$permission->menuid)
									->pluck('permissionid');
									
				DB::table('users_permissions')
				->where('user_id', $userid)
				->whereIn('permission_id',$permissionIds)
				->update([
					'permission_status'	=>	'DENY'
				]);				
			}
		}

		return response()->json([
			'status' => true
		]);
	}

	public function saveUserRolePermission(Request $request)
	{
		$userid		=	intval($request->userid);
		$roleid 	= 	intval($request->roleid);
		$isactive	= 	intval($request->isactive) ?? 0;
		
	
		$exists	=	DB::table('users_roles')
					->where('userid',$userid)
					->where('roleid',$roleid)
					->first();

		if($exists)
		{
			DB::table('users_roles')
			->where('userid', $userid)
			->where('roleid',$roleid)
			->update([
				'isactive'		=>	$isactive,
				'updated_by'	=>	session('userId'),
				'updated_on'	=>	now()
			]);
		}
		else
		{
			DB::table('users_roles')
			->insert([
				'userid'		=>	$userid,
				'roleid' 		=>	$roleid,
				'isactive' 		=> 	1,
				'createdby'		=>	session('userId'),
				'creationdate'	=>	now()
			]);			
		}
		
		$rolePermissions	=	DB::table('role_permissions')->where('roleid',$roleid)->where('isActive',1)->get();
		foreach($rolePermissions as $permission)
		{
			DB::table('users_permissions')
			->where('user_id',$userid)
			->where('permission_id',$permission->permissionid)
			->update([
				'permission_status'	=>	'DENY'
			]);
		}
		
		$userRoles	=	DB::table('users_roles')
						->where('userid',$userid)
						->where('isactive',1)
						->get();

		foreach($userRoles as $role)
		{
			$permissions	=	DB::table('role_permissions')
								->where('roleid',$role->roleid)
								->where('isActive',1)
								->get();

			foreach($permissions as $permission)
			{
				DB::table('users_permissions')
				->where('user_id',$userid)
				->where('permission_id',$permission->permissionid)
				->update([
					'permission_status'	=>	'ALLOW'
				]);
				
				$isExists	=	DB::table('users_permissions')
								->where('user_id',$userid)
								->where('permission_id',$permission->permissionid)
								->exists();
				if($isExists)
				{
					DB::table('users_permissions')
					->where('user_id',$userid)
					->where('permission_id',$permission->permissionid)
					->update([
						'permission_status'	=>	'ALLOW'
					]);					
				}
				else
				{
					DB::table('users_permissions')
					->insert([
						'user_id'			=>	$userid,
						'permission_id'		=>	$permission->permissionid,
						'permission_status'	=>	'ALLOW'
					]);
				}

			}
		}
		if($userRoles->count()==0)
		{
			DB::table('users_permissions')
			->where('user_id',$userid)
			->update([
				'permission_status'	=>	'DENY'
			]);			
		}
		return response()->json(['status' => true,]);
	}	

}
