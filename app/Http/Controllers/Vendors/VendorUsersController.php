<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Vendors;
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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use App\Services\LogServices;
use App\Services\PasswordService;
use App\Mail\VendorUserPasswordMail;
use Hash;
use App\Services\FeatureService;

class VendorUsersController extends Controller
{
	protected $featureService;
	protected $passwordService;
    public function __construct(PasswordService $passwordService,FeatureService $featureService)
    {
        $this->passwordService = $passwordService;
		$this->featureService = $featureService;
    }

    public function editVendorUser($recordid)
	{
		$data = DB::table('users_tbl')->where('userid','=',Crypt::decrypt($recordid))->first();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/vendors/user_edit',compact('data','token'));
    }

    public function getVendorUserData(Request $request)
	{
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',10);
		$currentPage 	= 	$request->input('page',1);

        $vendors	=	DB::table('users_tbl as a')
						->select('a.userid','a.name','a.mobilenumber','a.email','a.pass_word')
						->when($pagesearch!='',function($query) use ($pagesearch){
							return $query->where('a.name','like','%'.$pagesearch.'%');
						})
						->where('a.isSubVendor','=',1)
						->where('a.parentVendorId',session('vendorId'))
						->orderBy('a.name')
						->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/vendors/ajaxpages/vendorusersTable',['data'=>$vendors]);
	}

    public function vendorUsersList(Request $request)
	{
		if($request->session()->get('subUserId'))
		{
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','vendorusers');
		Session::put('adminsubmenu','vendoruserlist');
		Session::put('menid',138);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action		=	DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=138 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();

			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

		$token		=	rand('100000','999999').''.time();
		
		Session::put('form_token',$token);
		
        return view('admin/vendors/users_list',compact('token'));
    }
	
    public function storeVendorUser(Request $request,$recordid=0)
	{		
        $rules = [
			'name' => 'required|max:150|regex:/^[a-zA-Z\s.&-]+$/',
			'mobilenumber'       => [
				'required',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'email'       => [
				'required',
				'email:rfc,dns'
			],
        ];
		if($recordid==0)
		{
			$rules['mobilenumber'][] = Rule::unique('users_tbl', 'mobilenumber');
			$rules['email'][] = Rule::unique('users_tbl', 'email');
		}
		else
		{
			$usrId	=	Crypt::decrypt($recordid);
			$rules['mobilenumber'][]=	Rule::unique('users_tbl','mobilenumber')->ignore($usrId,'userid');
			$rules['email'][] 		=	Rule::unique('users_tbl', 'email')->ignore($usrId,'userid');
		}
        $messages = [
			'name.required' 				=> 'Name is required',
			'name.max' 						=> 'Maximum character length is 150',
			'name.regex' 					=> 'Invalid name provided',
			'mobilenumber.required' 		=> 'Mobile number is required',
			'mobilenumber.regex' 			=> 'Invalid mobile number',
			'mobilenumber.digits' 			=> 'Invalid mobile number',
			'email.required' 				=> 'Email is required',
			'email.email' 					=> 'Invalid email id',
			'mobilenumber.unique' 			=> 'This mobile number is already registered.',
			'email.unique' 					=> 'This email is already in use.',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$userType   	=	$request->session()->get('userType');
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
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
				$plainPassword = $this->passwordService->generatePassword();

				$hashedPassword = Hash::make($plainPassword);
				DB::beginTransaction();
				$newuserId	=	DB::table('users_tbl')->insertGetId([
								'name' 			=> 	$request->input('name'),
								'mobilenumber' 	=> 	$request->input('mobilenumber'),
								'email' 		=> 	$request->input('email'),
								'password' 		=> 	$hashedPassword,
								'pass_word' 	=> 	$plainPassword,
								'usertype' 		=> 	'VENDOR',
								'created_at'	=>	now(),
								'isactive'		=>	1,
								'issuper'		=>	1,
								'isSubVendor'	=>	1,
								'parentVendorId'=>	session('vendorId'),
								'isvendor'		=>	1,
							]);
				
				$vendor	=	DB::table('vendor_tbl')->where('vendorid',session('vendorId'))->first();
				
				/*
				DB::table('vendor_tbl')
				->insert([
					'userid'		=>	$newuserId,
					'companyname'	=>	$vendor->companyname,
					'shortname'		=>	$vendor->shortname,
					'categoryid'	=>	$vendor->categoryid,
					'tierid'		=>	$vendor->tierid,
					'officelocation'=>	$vendor->officelocation,
					'creationdate'	=>	now(),
					'approvalstatus'=>	1,
					'remark'		=>	'Approved',
					'loinumber'		=>	'',
					'parentVendorId'=>	session('vendorId')
				]);
				*/
				if($this->featureService->isEmailEnabled())
				{
					Mail::to($validatedData['email'])->send(new VendorUserPasswordMail($plainPassword,$validatedData['email'],$validatedData['name']));
				}
				
				DB::commit();
				return back()->with('success','User detail saved successfully.');
			}
			catch(QueryException $e)
			{
				Log::error('Error: ' . $e->getMessage());
				DB::rollBack();
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				DB::update('update users_tbl set name=?,mobilenumber=?,email=? where userid=?',[$validatedData['name'],$validatedData['mobilenumber'],$validatedData['email'],$usrId]);

				return redirect('/master/add/vendoruser')->with('success','User detail updated successfully.');
			}
			catch(QueryException $e)
			{
				Log::error('Error: ' . $e->getMessage());
				return back()->with('duplicate','Something went wrong, please try again later.')->withInput();
			}			
		}
    }
	
    public function addVendorUser(Request $request)
	{
		if($request->session()->get('subUserId'))
		{
			return redirect('dashboard');
		}
        Session::put('adminmenu','vendorusers');
		Session::put('adminsubmenu','addvendoruser');

		Session::put('menid',137);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action		=	DB::table('menu_permission')
								->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=137 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/vendors/user_add',compact('token'));
    }
    
}
