<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
//use App\Models\User;
use Illuminate\Support\Str;
use Hash;
use Session;
use Illuminate\Support\Facades\Cookie;

use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Services\FeatureService;
class CustomAuthController extends Controller
{
	protected $featureService;
	public function __construct(FeatureService $featureService,Request $request)
	{
		$this->featureService = $featureService;
	}
	
	public function updatePassword(Request $request)
	{
		$request->validate([
			'email' 		=> 'required|email:rfc,dns',
			'token' 		=> 'required',
			'user_password' => 'required|confirmed',
		]);

		$reset = DB::table('password_resets')
			->where('email', $request->email)
			->where('token', $request->token)
			->first();

		if (!$reset) {
			return back()->with('fail', 'Invalid or expired reset token.');
		}

		DB::table('users_tbl')
			->where('email', $request->email)
			->update([
				'password'	=> 	Hash::make($request->user_password),
				'pass_word' =>	$request->user_password,
			]);

		DB::table('password_resets')->where('email', $request->email)->delete();

		return redirect()->route('loginpanel')->with('success','Password updated successfully.');
	}	

    public function showResetForm(Request $request,$token)
    {
		$email = $request->query('email');

		if (!$token || !$email) {
			return redirect()->route('forgotpassword')
				->with('fail', 'Invalid or expired reset link.');
		}

		return view('auth.resetform', [
			'token' => $token,
			'email' => $email,
		]);
    }
    public function sendPasswordLink(Request $request)
	{

		$rules = [
			'email'			=>	'required|email:rfc,dns',
		];
        $messages = [
            'email.required'	=> 'The user must enter an email.',
			'email.email'		=> 'Invalid email provided',
        ];

        $validatedData 	= $request->validate($rules,$messages);

        $user = DB::table('users_tbl')->where('email',$validatedData['email'])->first();
		if(!$user)
		{
			return back()->with('success','If an account with the email exists, we’ve sent password reset instructions.');
		}
		$token = Str::random(64);

        DB::table('password_resets')->updateOrInsert(
            ['email' => $request->email],
            [
                'token' => $token,
                'created_at' => now()
            ]
        );
		
		// Make sure this route points to the reset form, NOT forgotPassword
		$resetLink = route('userpassword.reset', [
			'token' => $token,
			'email' => $request->email,
		]);		
		
		if($this->featureService->isEmailEnabled())
		{
			Mail::to($request->email)->send(new ResetPasswordMail($resetLink));
		}
		
		return back()->with('success', 'Reset link sent to your email.');
    }

    public function forgotPassword()
	{
		return response()
			->view('auth.forgot')
			->header('Cache-Control', 'no-cache, no-store, must-revalidate')
			->header('Pragma', 'no-cache')
			->header('Expires', '0');		

    }
	
    public function login(){

		if(session()->has('userId')) {
			return redirect('application/admin/dashboard');
		}

		return response()
			->view('auth.login')
			->header('Cache-Control', 'no-cache, no-store, must-revalidate')
			->header('Pragma', 'no-cache')
			->header('Expires', '0');		
		
    }

    public function registration()
	{
        return view('auth.registration');
    }

    public function registerUser(Request $request)
    {

        $this->validate($request, [
            'UserName' =>  'required|unique:users',
            'Password' => 'required|min:6|max:12',
            'EmailID' => 'required|email',
            'EmailPW' => 'required',
        ]);        

        $UserName   = $request->input('UserName');
        $Password   = $request->input('Password');
        $EmailID    = $request->input('EmailID');
        $EmailPW    = $request->input('EmailPW');
        
        $res = DB::insert('INSERT INTO users (username,password,emailid,emailpw) VALUES (?, ?, ?, ?)', [$UserName,$Password,$EmailID,$EmailPW]);

        if($res)
        {
            return back()->with('success','You have registered successfully');
        }
        else
        {
            return back()->with('fail','Something wrong');
        }
    }


    public function loginUser(Request $request)
	{
		
		$rules = [
			'username'		=>	['required', 'regex:/^\S+$/'],
			'userpassword' 	=>	['required', 'regex:/^\S+$/'],
			'usertype' 		=>	'required',
		];
        $messages = [
            'username.required'		=> 'User name is required',
			'username.regex'		=> 'Invalid user name',
			'userpassword.required'	=> 'Password is required',
			'userpassword.regex'	=> 'Invalid password',
			'usertype.required'		=> 'User type is required',
        ];

        $validatedData 	= $request->validate($rules,$messages);

        //$user = User::where('UserName','=',$request->UserName)->first();

		$user	=	DB::table('users_tbl')
						->where(function ($query) use ($request) {
							$query->where('mobilenumber','=',$request->username)
								  ->orWhere('email','=',$request->username);
						})
						->first();

        if(!$user || !Hash::check($request->userpassword,$user->password))
		{
            return back()->withInput()->with('fail', 'Invalid Username or Password');
        }

        if($user->isactive != 1)
		{
            return back()->with('fail','Activation is pending. Please contact administrator.');
        }
		
		$request->session()->regenerate();

		if($user->usertype=='ADMIN' || $user->usertype=='RESOURCE')
		{
			if($user->usertype=='RESOURCE')
			{
				$user->usertype	=	'ADMIN';
			}
			$username	=	$user->name;
			$request->session()->put([
				'userId' 		=> $user->userid,
				'userName' 		=> $user->name,
				'userType' 		=> $user->usertype,
				'shortName' 	=> '',
				'issuper' 		=> $user->issuper,
				'departmentId' 	=> 0,
			]);

			DB::table('users_tbl')->where('userid',$user->userid)->update([
				'lastlogin'	=>	now()
			]);
			
			$lastLogin	=	DB::table('login_activity')
								->where('userid',$user->userid)
								->orderByDesc('logintime')
								->value('logintime');
								
			if(!is_null($lastLogin))
			{
				$request->session()->put([
					'lastlogin'	=> $lastLogin,
				]);
				
			}
			DB::table('login_activity')->insert([
				'userid'	=>	$user->userid,
				'logintime'	=>	now(),
				'logouttime'=>	'',
				'usertype'	=>	'USER'
			]);
			
			if($user->isMaster==0)
			$permissions	=	$this->loadAdminPermissions($user->userid);
			else
			$permissions	=	[];//$this->loadAdminPermissions($user->userid);
			
			$request->session()->put([
				'adminmenu'		=>	'dashboard',
				'adminsubmenu'	=> 	'',
				'isMaster'		=>	$user->isMaster,
				'permissions'	=> 	$permissions,
			]);
			
			
			return redirect('dashboard')->with('success','You are logged in successfully.');
		}
		if($user->usertype=='DEPARTMENT')
		{
			$department	=	DB::table('department_tbl')->where('userid','=',$user->userid)->first();
			if($department->approvalstatus==0)
			{
				return back()->with('fail','Approval is pending. Please contact CHiPS administrator.');
			}
			$username	=	$user->name;
			$request->session()->put([
				'userId' 			=> 	$user->userid,
				'userName' 			=> 	$user->name,
				'userType' 			=> 	$user->usertype,
				'shortName' 		=> 	$department->shortname,
				'issuper' 			=> 	$user->issuper,
				'departmentId' 		=> 	$department->departmentid,
				'isProjectManager' 	=> 	$user->ispm,
				'isMaster'			=>	$user->isMaster,
			]);
			
			DB::table('users_tbl')->where('userid',$user->userid)->update([
				'lastlogin'	=>	now()
			]);

			$lastLogin	=	DB::table('login_activity')
								->where('userid',$user->userid)
								->orderByDesc('logintime')
								->value('logintime');
			if(!is_null($lastLogin))
			{
				$request->session()->put([
					'lastlogin'	=> $lastLogin,
				]);
				
			}
			DB::table('login_activity')->insert([
				'userid'	=>	$user->userid,
				'logintime'	=>	now(),
				'logouttime'=>	'',
				'usertype'	=>	'DEPARTMENT'
			]);
			
			$request->session()->put([
				'adminmenu'		=> 'dashboard',
				'adminsubmenu'	=> '',
			]);
			
			return redirect('dashboard')->with('success','You are logged in successfully.');						
		}
		if($user->usertype=='VENDOR')
		{

			$isSubVendor = ($user->isSubVendor!=0);

			if($isSubVendor)
			{
				$parentVendorId	=	$user->parentVendorId;
				$vendor			= 	DB::table('vendor_tbl')->where('vendorid', $parentVendorId)->first();

				$sessionUserId	=	$vendor->userid;
				$subUserId		= 	$user->userid;
			}
			else
			{
				$vendor	=	DB::table('vendor_tbl')->where('userid', $user->userid)->first();

				if($vendor->approvalstatus==0)
				{
					return back()->with('fail', 'Approval is pending. Please contact CHiPS administrator.');
				}

				$sessionUserId 	=	$user->userid;
				$subUserId 		= 	null;
			}

			DB::table('users_tbl')->where('userid', $user->userid)->update(['lastlogin' => now()]);

			$lastLogin = DB::table('login_activity')->where('userid', $user->userid)->latest('logintime')->value('logintime');

			DB::table('login_activity')->insert([
				'userid'     => $user->userid,
				'logintime'  => now(),
				'logouttime' => '',
				'usertype'   => 'VENDOR'
			]);

			$request->session()->put([
				'userId'      => $sessionUserId,
				'vendorId'    => $vendor->vendorid,
				'userName'    => $user->name,
				'shortName'   => $vendor->shortname,
				'userType'    => $user->usertype,
				'issuper'     => $user->issuper,
				'subUserId'   => $subUserId,
				'adminmenu'   => 'dashboard',
				'adminsubmenu'=> '',
				'isMaster'	  => $user->isMaster,
			]);

			if(!is_null($lastLogin))
			{
				$request->session()->put('lastlogin', $lastLogin);
			}

			return redirect('dashboard')->with('success','You are logged in successfully.');
			
		}
		if($user->usertype=='PROJECT MANAGER')
		{
			$department	=	DB::table('department_tbl')
							->where('userid','=',$user->userid)
							->where('isprojectmanager',1)
							->first();
							
			if($department->approvalstatus==0)
			{
				return back()->with('fail','Approval is pending. Please contact CHiPS administrator.');
			}
			$username	=	$user->name;
			$request->session()->put([
				'userId' 			=> $user->userid,
				'departmentId' 		=> $department->departmentid,
				'isProjectManager' 	=> $user->ispm,
				'userName' 			=> $user->name,
				'shortName' 		=> $department->shortname,
				'userType' 			=> $user->usertype,
				'issuper' 			=> $user->issuper,
				'isMaster'			=> $user->isMaster,
			]);

			
			DB::table('users_tbl')->where('userid',$user->userid)->update([
				'lastlogin'	=>	now()
			]);

			$lastLogin	=	DB::table('login_activity')
								->where('userid',$user->userid)
								->orderByDesc('logintime')
								->value('logintime');
			if(!is_null($lastLogin))
			{
				$request->session()->put([
					'lastlogin'	=> $lastLogin,
				]);
				
			}
			DB::table('login_activity')->insert([
				'userid'	=>	$user->userid,
				'logintime'	=>	now(),
				'logouttime'=>	'',
				'usertype'	=>	'PROJECT MANAGER'
			]);
			
			$request->session()->put([
				'adminmenu'		=> 'dashboard',
				'adminsubmenu'	=> '',
			]);
			
			return redirect('dashboard')->with('success','You are logged in successfully.');						
		}
		/*
		if($user->usertype=='RESOURCE')
		{
			
			$resource	=	DB::table('resource_tbl')
							->where('userid','=',$user->userid)
							->first();
							
			$username	=	$user->name;
			$request->session()->put([
				'userId' 			=> $user->userid,
				'resourceId' 		=> $resource->resourceid,
				'isResource' 		=> $user->isresource,
				'userName' 			=> $user->name,
				'shortName' 		=> '',
				'userType' 			=> $user->usertype,
				'issuper' 			=> $user->issuper,
			]);

			
			DB::table('users_tbl')->where('userid',$user->userid)->update([
				'lastlogin'	=>	now()
			]);

			$lastLogin	=	DB::table('login_activity')
								->where('userid',$user->userid)
								->orderByDesc('logintime')
								->value('logintime');
			if(!is_null($lastLogin))
			{
				$request->session()->put([
					'lastlogin'	=> $lastLogin,
				]);
				
			}
			DB::table('login_activity')->insert([
				'userid'	=>	$user->userid,
				'logintime'	=>	now(),
				'logouttime'=>	'',
				'usertype'	=>	'RESOURCE'
			]);
			
			$request->session()->put([
				'adminmenu'		=> 'dashboard',
				'adminsubmenu'	=> '',
			]);
			
			return redirect('dashboard')->with('success','You are logged in successfully.');						
		}
		*/
		
    }

    public function dashboard()
    {
		$userId  	= Session::get('userId');
		$userType  	= Session::get('userType');
		$isSuper 	= Session::get('issuper');		

		if(!$userId)
		{
			return redirect()->route('login')->with('error', 'Session expired. Please login again.');
		}

		Session::put('adminmenu', 'dashboard');
		Session::put('adminsubmenu', '');
		$user = DB::table('users_tbl')
				  ->where('userid', $userId)
				  ->first();

		if($isSuper==0)
		{
			$actionIds = DB::table('menu_permission')
				->where('menuid', 1)
				->where('ispermitted',1)
				->where('userid', $userId)
				->pluck('actionid')
				->toArray();
		}
		else
		{
			$actionIds = DB::table('menu_action')
				->pluck('actionid')
				->toArray();
		}
		Session::put('actions', $actionIds);

		return view('admin/dashboard');
    }
	
	public function logout(Request $request)
	{
		$userId = Session::get('userId');
		
		if($userId)
		{
			DB::table('login_activity')
				->where('userid', Session::get('userId'))
				->where('logouttime', '0000-00-00 00:00:00')
				->orderByDesc('logintime')
				->limit(1)
				->update([
					'logouttime' => now()
				]);

			$isSuper			=	Session::get('issuper');
			$departmentid 		= 	Session::get('departmentId') ?? 0;
			$vendorid 			= 	Session::get('vendorId') ?? 0;
			$isProjectManager	= 	Session::get('isProjectManager') ?? 0;
			$isResource			= 	Session::get('isResource') ?? 0;
			$resourceid 		= 	Session::get('resourceId') ?? 0;
			
			
			if($departmentid!=0)
			{
				Cache::forget('menu_tree_super_department');
			}
			else if($vendorid!=0)
			{
				Cache::forget('menu_tree_user_vendor');
			}
			else if($isProjectManager!=0)
			{
				Cache::forget('menu_tree_user_project_manager');
			}
			else if($isResource!=0)
			{
				Cache::forget('menu_tree_super_resource');
			}
			else
			{
				Cache::forget('menu_tree_admin_'.$userId);
			}

			Session::flush();
			Session::invalidate();

			Session::regenerate();
			Session::regenerateToken();

			Cookie::queue(Cookie::forget('laravel_session'));
			Cookie::queue(Cookie::forget('XSRF-TOKEN'));
		}

		return redirect('loginpanel')
			->header('Cache-Control', 'no-cache, no-store, must-revalidate')
			->header('Pragma', 'no-cache')
			->header('Expires', '0');
		
	}
	
	public function setLocale($locale)
	{
		if (in_array($locale, ['en', 'mr', 'hn'])) {
			Session::put('locale', $locale);
		}

		return redirect()->back();
	}		


	private function loadAdminPermissions($userId)
	{
		$permissions	=	DB::table('users_permissions as up')
							->join('permissions_tbl as p','p.permissionid','=','up.permission_id')
							->where('up.user_id',$userId)
							->where('up.permission_status','ALLOW')
							->where('p.isactive',1)
							->select('p.permissionid','p.route_name','p.permission_type','p.menuid')
							->get();

		$linkPermissions=	DB::table('permissions_tbl')
							->where('permission_type','LINK')
							->where('isactive',1)
							->select('permissionid','route_name','permission_type','menuid')
							->get();
							
		$permissionArray = [];
		
		foreach($permissions as $permission)
		{
			$permissionArray[$permission->route_name] = [
				'type'  =>	$permission->permission_type,
				'menuid'=>	$permission->menuid,
			];
		}

		foreach($linkPermissions as $permission)
		{
			$permissionArray[$permission->route_name] = [
				'type'   => $permission->permission_type,
				'menuid' => $permission->menuid,
			];
		}
		
		return $permissionArray;
	}	
}
