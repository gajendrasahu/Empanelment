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
class DepartmentController extends Controller
{
	protected $passwordService;
    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }
	
	/*REQUIREMENT RELATED*/
    public function addRequirement(Request $request,$recordid)
	{        
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','departmentrequest');

		Session::put('menid',67);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action		=	DB::table('menu_permission')
								->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=67 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$data		=	DB::table('department_request')->where('requestid','=',Crypt::decrypt($recordid))->first();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$experience	=	DB::table('work_experience')->orderby('experienceid')->get();
		$eois		=	DB::table('eoi_request')->where('requestid','=',Crypt::decrypt($recordid))->get();
        return view('admin/master/requirement_add',compact('token','data','recordid','category','experience','eois'));
    }
	/*REQUIREMENT RELATED CLOSED*/
    public function addDeptRequest(Request $request)
	{        
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','departmentrequest');

		$userId	= 	$request->session()->get('userId');
		$issuper= 	$request->session()->get('issuper');
		
		$token	=	rand('100000','999999').''.time();
		
		Session::put('form_token', $token);
		
        return view('admin/master/department_request',compact('token'));
    }
    public function storeDeptRequest(Request $request,$recordid)
	{        
        $rules = [
			'mobilenumber'       => [
				'nullable',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'email'       => [
				'required',
				'email:rfc,dns'
			],
			'officialemail'       => [
				'nullable',
				'email:rfc,dns'
			],
			'departmentname' 		=> 'required|max:150|regex:/^[a-zA-Z0-9\s.]+$/',
			'shortname' 			=> 'required|max:20|regex:/^[a-zA-Z0-9.]+$/',
			'address' 				=> 'required|max:200',
        ];
		if((int) $recordid===0) {
			$rules['mobilenumber'][] = Rule::unique('users_tbl', 'mobilenumber');
			$rules['email'][] = Rule::unique('users_tbl', 'email');
		}
        $messages = [
			'departmentname.required' 	=> 	'Department name is required',
			'departmentname.max' 		=> 	'Maximum 100 characters allowed',
			'departmentname.regex' 		=> 	'Special characters are not allowed in address',
			'shortname.required' 		=> 	'Short name is required',
			'shortname.max' 			=> 	'Maximum 20 characters allowed',
			'shortname.regex' 			=> 	'Special characters not allowed in short name',
			'mobilenumber.required' 	=> 	'Mobile number is required',
			'mobilenumber.regex' 		=> 	'Invalid mobile number',
			'mobilenumber.digits' 		=> 	'Invalid mobile number',
			'email.required' 			=> 	'Email is required',
			'email.email' 				=> 	'Invalid email id',
			'mobilenumber.unique' 		=> 	'This mobile number is already registered.',
			'email.unique' 				=> 	'This email is already registered.',
			'officialemail.email' 		=> 	'Invalid email id',
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
				$hashedPassword = Hash::make($plainPassword); // Encrypt password
				
				$userId	=	DB::table('users_tbl')->insertGetId([
								'name' 			=> $request->input('departmentname'),
								'mobilenumber' 	=> $request->input('mobilenumber'),
								'email' 		=> $request->input('email'),
								'password' 		=> $hashedPassword,
								'pass_word' 	=> $plainPassword,
								'usertype' 		=> 'DEPARTMENT',
								'otp' 			=> rand(100001,999999),
								'created_at'	=>	now(),
								'isactive'		=>	1,
								'issuper'		=>	1,
								'isdepartment'	=>	1,
							]);

				DB::table('department_tbl')->insert([
					'userid'			=>	$userId,
					'departmenttype'	=>	'OUTSIDE',
					'departmentname'	=>	$request->input('departmentname'),
					'shortname'			=>	$request->input('shortname'),
					'projectname'		=>	NULL,
					'designation'		=>	NULL,
					'officialemail'		=>	$validatedData['officialemail'] ?? NULL,
					'creationdate'		=>	now(),
					'requestedby'		=>	$request->session()->get('userId'),
					'approvalstatus'	=>	1,
					'actiondatetime'	=>	now(),
					'actiontakenby'		=>	$request->session()->get('userId'),
					'address'			=>	$request->input('address')
				]);

				return back()->with('success','Department record stored successfully.');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				$data = DB::table('department_tbl')->where('departmentid','=',$recordid)->first();
				
				$officialemail	=	$validatedData['officialemail'] ?? NULL;
				
				DB::update('update department_tbl set departmentname=?,shortname=?,address=?,officialemail=? where departmentid=?',[$validatedData['departmentname'],$validatedData['shortname'],$validatedData['address'],$officialemail,$recordid]);
				
				
				
				DB::update('update users_tbl set name=?,mobilenumber=?,email=? where userid=?',[$validatedData['departmentname'],$validatedData['mobilenumber'],$validatedData['email'],$data->userid]);

				return redirect('/master/department/request')->with('success','Department record updated successfully.');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','Something went wrong. Please try again.')->withInput();
			}			
		}
    }

    public function getDeptRequestData(Request $request)
	{
		$approvalstatus	=	$request->input('approvalstatus');
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page', 1);

		$data	=	DB::table('department_tbl as a')
					->select(
						'a.departmentid',
						'a.userid',
						'a.departmentname',
						'a.shortname',
						'a.creationdate',
						'a.requestedby',
						'a.approvalstatus',
						'a.actiondatetime',
						'a.remark',
						'b.name',
						'b.mobilenumber',
						'b.email',
						'a.officialemail',
						'b.isactive',
						'a.address',
						'b.pass_word'
					)
					->leftJoin('users_tbl as b', 'b.userid', '=', 'a.userid')
					->where('b.isdepartment','=',1)  // Ensure isprojectmanager = 1
					->when($pagesearch != '', function ($query) use ($pagesearch) {
						return $query->where(function ($query) use ($pagesearch) {
							$query->where('b.name', 'like', '%' . $pagesearch . '%')
								->orWhere('b.mobilenumber', 'like', '%' . $pagesearch . '%')
								->orWhere('b.email', 'like', '%' . $pagesearch . '%');
						});
					})
					->orderBy('a.creationdate')
					->paginate($pagesize, ['*'], 'page', $currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/departmentrequestTable', ['data' => $data]);

    }
    public function editDeptRequest($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('department_tbl')->where('departmentid','=',Crypt::decrypt($recordid))->first();
		$user = DB::table('users_tbl')->where('userid','=',$data->userid)->first();
        return view('admin/master/department_request_update',compact('data','user','token'));
    }
    public function detailDeptRequest($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data 	=	DB::table('department_tbl')->where('departmentid','=',Crypt::decrypt($recordid))->first();
		$user	=	DB::table('users_tbl')->where('userid','=',$data->userid)->first();
        return view('admin/master/department_request_detail',compact('data','token','user'));
    }
	
    public function approvalDeptRequest(Request $request,$recordid)
	{        
        $rules = [
			'name' 					=> 'required|max:150|regex:/^[a-zA-Z0-9\s.]+$/',
			'mobilenumber'       => [
				'required',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'email'       => [
				'required',
				'email'
			],
			'departmentname' 		=> 'required|max:150|regex:/^[a-zA-Z0-9\s.]+$/',
			'shortname' 			=> 'required|max:20|regex:/^[a-zA-Z0-9.]+$/',
			'address' 				=> 'required|max:200|regex:/^[a-zA-Z0-9.]+$/',
			'remark' 				=> 'required|max:150|regex:/^[a-zA-Z0-9.]+$/',
			'action_type'			=> 'required|in:1,-1',
        ];
		if((int) $recordid===0) {
			$rules['mobilenumber'][] = Rule::unique('users_tbl', 'mobilenumber');
			$rules['email'][] = Rule::unique('users_tbl', 'email');
			$rules['loginpassword'] = 'required|min:8|max:15';
			$rules['confirmpassword'] = 'required|same:loginpassword|max:15';			
		}
        $messages = [
			'name.required' 				=> 'Name is required',
			'name.max' 						=> 'Maximum character length is 150',
			'name.regex' 					=> 'Invalid name provided',
			'departmentname.required' 		=> 'Department name is required',
			'departmentname.max' 			=> 'Maximum 100 characters allowed',
			'departmentname.regex' 			=> 'Special characters not allowed in address',
			'shortname.required' 			=> 'Short name is required',
			'shortname.max' 				=> 'Maximum 20 characters allowed',
			'shortname.regex' 				=> 'Special characters not allowed in short name',
			'mobilenumber.required' 		=> 'Mobile number is required',
			'mobilenumber.regex' 			=> 'Invalid mobile number',
			'mobilenumber.digits' 			=> 'Invalid mobile number',
			'email.required' 				=> 'Email is required',
			'email.email' 					=> 'Invalid email id',
			'loginpassword.required' 		=> 'Create password is mandatory',
			'loginpassword.min' 			=> 'Minimum password length is 8 characters',
			'loginpassword.max' 			=> 'Maximum password length is 15',
			'confirmpassword.required' 		=> 'Confirm password is required',
			'confirmpassword.same' 			=> 'Both password must be same',
			'confirmpassword.max' 			=> 'Maximum password length is 15 characters',
			'mobilenumber.unique' 			=> 'This mobile number is already registered.',
			'email.unique' 					=> 'This email is already registered.',
			'remark.required' 				=> 'APPROVAL REJECTION REMARK IS REQUIRED',
			'remark.max' 					=> 'MAXIMUM 150 CHARACTERS ALLOWED',
			'action_type.required' 			=> 'ACTION TYPE IS REQUIRED',
			'action_type.in' 				=> 'INVALID ACTION TYPE VALUE',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		try
		{
			$activestatus	=	0;
			if($validatedData['action_type']==1)
			$activestatus	=	1;
		
			DB::update('update department_tbl set approvalstatus=?,actiondatetime=?,remark=?,actiontakenby=? where departmentid=?',[$validatedData['action_type'],now(),$validatedData['remark'],$userId,$recordid]);
			
			$data = DB::table('department_tbl')->where('departmentid','=',$recordid)->first();
			
			DB::update('update users_tbl set isactive=? where userid=?',[$activestatus,$data->userid]);

			return redirect('/master/department/request')->with('success','APPROVAL / REJECTION ACTION STORED SUCCESSFULLY!');
		}
		catch(QueryException $e)
		{
			return back()->with('duplicate',$e->getMessage())->withInput();
		}			
    }


    public function addProjectManager(Request $request)
	{        
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','projectmanager');

		Session::put('menid',109);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/projectmanager_add',compact('token'));
    }

    public function storeProjectManager(Request $request,$recordid)
	{        
        $rules = [
			'name' => 'required|max:150|regex:/^[a-zA-Z\s.&-]+$/',
			'mobilenumber'       => [
				'required',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'officialemail'	=> [
				'nullable',
				'email:rfc,dns'
			],
			'email'       => [
				'required',
				'email:rfc,dns'
			],
			'address' 				=> 'required|max:200',
        ];
		if((int) $recordid===0) {
			$rules['mobilenumber'][] = Rule::unique('users_tbl', 'mobilenumber');
			$rules['email'][] = Rule::unique('users_tbl', 'email');
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
			'email.unique' 					=> 'This email is already registered.',
			'address.required' 				=> 'Address is required',
			'address.max' 					=> 'Maximum character length is 200',
			'officialemail.email' 			=> 'Invalid email id',
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
				$hashedPassword = Hash::make($plainPassword); // Encrypt password
				
				$userId	=	DB::table('users_tbl')->insertGetId([
								'name' 			=> 	$request->input('name'),
								'mobilenumber' 	=> 	$request->input('mobilenumber'),
								'email' 		=> 	$request->input('email'),
								'password' 		=> 	$hashedPassword,
								'pass_word' 	=> 	$plainPassword,
								'usertype' 		=> 	'PROJECT MANAGER',
								'otp' 			=> 	rand(100001,999999),
								'created_at'	=>	now(),
								'isactive'		=>	1,
								'issuper'		=>	1,
								'ispm'			=>	1,
								'categoryids'	=>	'1,2'
							]);

				DB::table('department_tbl')->insert([
					'userid'			=>	$userId,
					'departmenttype'	=>	'INSIDE',
					'departmentname'	=>	$request->input('name'),
					'officialemail'		=>	$validatedData['officialemail'] ?? NULL,
					'shortname'			=>	'PM',
					'projectname'		=>	NULL,
					'designation'		=>	NULL,
					'creationdate'		=>	now(),
					'requestedby'		=>	$request->session()->get('userId'),
					'approvalstatus'	=>	1,
					'actiondatetime'	=>	now(),
					'actiontakenby'		=>	$request->session()->get('userId'),
					'address'			=>	$request->input('address'),
					'isprojectmanager'	=>	1
				]);

				return back()->with('success','Project manager detail saved successfully.');
			}
			catch(QueryException $e)
			{
				Log::error('Error: ' . $e->getMessage());
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				$data = DB::table('department_tbl')->where('departmentid','=',$recordid)->first();
				
				$officialemail	=	$validatedData['officialemail'] ?? NULL;
				
				DB::update('update department_tbl set departmentname=?,address=?,officialemail=? where departmentid=?',[$validatedData['name'],$validatedData['address'],$officialemail,$recordid]);

				DB::update('update users_tbl set name=?,mobilenumber=?,email=? where userid=?',[$validatedData['name'],$validatedData['mobilenumber'],$validatedData['email'],$data->userid]);

				return redirect('/master/add/projectmanager')->with('success','Project manager detail saved successfully.');
			}
			catch(QueryException $e)
			{
				Log::error('Error: ' . $e->getMessage());
				return back()->with('duplicate','Something went wrong, please try again later.')->withInput();
			}			
		}
    }

    public function getProjectManagerData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page', 1);

		$data = DB::table('department_tbl as a')
				->select(
					'a.departmentid',
					'a.userid',
					'a.departmentname',
					'a.officialemail',
					'a.shortname',
					'a.creationdate',
					'a.requestedby',
					'a.approvalstatus',
					'a.actiondatetime',
					'a.remark',
					'b.name',
					'b.mobilenumber',
					'b.email',
					'b.isactive',
					'a.address',
					'b.pass_word'
				)
				->leftJoin('users_tbl as b', 'b.userid', '=', 'a.userid')
				->where('a.isprojectmanager', '=', 1)  // Ensure isprojectmanager = 1
				->where('b.ispm', '=', 1)              // Ensure b.ispm = 1
				->when($pagesearch != '', function ($query) use ($pagesearch) {
					return $query->where(function ($query) use ($pagesearch) {
						$query->where('b.name', 'like', '%' . $pagesearch . '%')
							->orWhere('b.mobilenumber', 'like', '%' . $pagesearch . '%')
							->orWhere('b.email', 'like', '%' . $pagesearch . '%');
					});
				})
				->orderBy('a.creationdate')
				->paginate($pagesize, ['*'], 'page', $currentPage);		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/projectmanagerTable', ['data' => $data]);

    }

    public function editProjectManager($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('department_tbl')->where('departmentid','=',Crypt::decrypt($recordid))->first();
		$user = DB::table('users_tbl')->where('userid','=',$data->userid)->first();
        return view('admin/master/projectmanager_edit',compact('data','user','token'));
    }


    
}
