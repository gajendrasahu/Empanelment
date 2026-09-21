<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
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
use App\Services\SmsService;
use Hash;
use Illuminate\Support\Facades\Log;
class RegistrationController extends Controller
{
	protected $smsService;
    protected $todays_datetime;
    protected $todays_date;	
	
	public function __construct(SmsService $smsService)
	{
		$this->smsService 			=	$smsService;
		ini_set('serialize_precision', -1);
		$this->todays_datetime		=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date			=	Carbon::now()->format('Y-m-d');		
	}
	private function getOTP()
	{
		//$otp	=	rand(100000,999999);
		$otp	=	"555555";
		return $otp;
	}
	
	public function userRegistration(Request $request)
	{
		$usertype = $request->input('usertype');
		$token = rand(100000, 999999) . '' . time();
		Session::put('form_token', $token);
		Session::put('usertype',$usertype);

		if ($usertype == 'DEPARTMENT') {
			$url = route('department.registration.view', ['token' => $token]);
		} else if ($usertype == 'IMPELLENT') {
			$url = route('impellent.registration.view', ['token' => $token]);
		} else if ($usertype == 'VENDOR') {
			$url = route('vendor.registration.view', ['token' => $token]);
		} else {
			return response()->json(['error' => 'Invalid user type'], 400);
		}

		return response()->json(['redirect_url' => $url]);
	}
    public function verifyDepartmentOtp(Request $request)
	{        
		$request->merge([
			'otp' => $request->first . $request->second . $request->third . $request->fourth . $request->fifth . $request->sixth,
		]);	
        $rules = [
            'departmenttype' 		=> 'required|in:INSIDE,OUTSIDE',
			'departmentname' 		=> 'required|max:150',
			'projectname' 			=> 'nullable|max:150',
			'personname' 			=> 'required|max:150',
			'designation' 			=> 'required|max:50',
			'mobilenumber'       => [
				'required',
				'regex:/^[1-9]\d{9}$/',
				'digits:10',
				function ($attribute, $value, $fail) {
					if ($value !== session('mobilenumber')) {
						$fail('MOBILE NUMBER DOES NOT MATCH.');
					}
				}
			],
			'email' 				=> 'required|email',
			'loginpassword'			=> 'required|min:8|max:15',
			'confirmpassword'    	=> 'required|same:loginpassword|max:15',
			'first'    				=> 'required',
			'second'    			=> 'required',
			'third'    				=> 'required',
			'fourth'    			=> 'required',
			'fifth'    				=> 'required',
			'sixth'    				=> 'required',
			'otp'                => ['required', function ($attribute, $value, $fail) {
				if ($value !== session('validotp')) {
					$fail('INVALID OTP PROVIDED');
				}
			}],			
        ];
		
        $messages = [
            'departmenttype.required' 		=> 'DEPARTMENT TYPE IS REQUIRED',
			'departmenttype.in' 			=> 'INVALID DEPARTMENT TYPE',
			'departmentname.required' 		=> 'DEPARTMENT NAME IS REQUIRED',
			'departmentname.max' 			=> 'MAXIMUM 150 CHARACTERS ALLOWED',
			'projectname.required' 			=> 'PROJECT NAME IS REQUIRED',
			'projectname.max' 				=> 'MAXIMUM 150 CHARACTERS ALLOWED',
			'personname.required' 			=> 'PERSON NAME IS REQUIRED',
			'personname.max' 				=> 'MAXIMUM 150 CHARACTERS ALLOWED',
			'designation.required' 			=> 'DESIGNATION IS REQUIRED',
			'designation.max' 				=> 'MAXIMUM 150 CHARACTERS ALLOWED',
			'mobilenumber.required' 		=> 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 			=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 			=> 'INVALID MOBILE NUMBER',
			'email.required' 				=> 'EMAIL IS REQUIRED',
			'email.email' 					=> 'INVALID EMAIL ID',
			'loginpassword.required' 		=> 'CREATE PASSWORD IS MANDATORY',
			'loginpassword.min' 			=> 'MINIMUM PASSWORD LENGTH IS 8 CHARACTERS',
			'loginpassword.max' 			=> 'MAXIMUM PASSWORD LENGTH IS 15',
			'confirmpassword.required' 		=> 'CONFIRM PASSWORD IS MANDATORY',
			'confirmpassword.same' 			=> 'CREATE AND CONFIRM PASSWORD MUST BE SAME',
			'confirmpassword.max' 			=> 'MAXIMUM PASSWORD LENGTH IS 15',
			'first.required' 				=> 'OTP IS REQUIRED',
			'second.required' 				=> 'OTP IS REQUIRED',
			'third.required' 				=> 'OTP IS REQUIRED',
			'fourth.required' 				=> 'OTP IS REQUIRED',
			'fifth.required' 				=> 'OTP IS REQUIRED',
			'sixth.required' 				=> 'OTP IS REQUIRED',
        ];

		$validator = Validator::make($request->all(), $rules);		
		if ($validator->fails()) {
			// Return JSON for AJAX
			return response()->json([
				'status' => 'error',
				'errors' => $validator->errors()
			], 422);
		}

		try
		{
			
			$userId	=	DB::table('users_tbl')->insertGetId([
							'name' 			=> $request->input('personname'),
							'mobilenumber' 	=> $request->input('mobilenumber'),
							'email' 		=> $request->input('email'),
							'password' 		=> HASH::make($request->input('loginpassword')),
							'usertype' 		=> 'DEPARTMENT',
							'otp' 			=> Session::pull('validotp'),
							'created_at'	=>	now(),
							'isactive'		=>	0,
							'issuper'		=>	1,
							'isdepartment'	=>	1,
						]);

			DB::table('department_tbl')->insert([
				'userid'			=>	$userId,
				'departmenttype'	=>	$request->input('departmenttype'),
				'departmentname'	=>	$request->input('departmentname'),
				'projectname'		=>	$request->input('projectname') ?? '',
				'designation'		=>	$request->input('designation'),
				'creationdate'		=>	now(),
			]);
			
			$url = route('registered.view');
			return response()->json(['redirect_url' => $url]);

		}
        catch (QueryException $e) 
        {
			Log::error('Error: ' . $e->getMessage());
            return response()->json(['message'=>'We encountered a technical issue while processing your request. Please try again later.','status'=>400], 400);
        }
		
	}
	
    public function generateOtp(Request $request)
	{        
        $rules = [
            'departmenttype' 		=> 'required|in:INSIDE,OUTSIDE',
			'departmentname' 		=> 'required|max:150',
			'projectname' 			=> 'nullable|max:150',
			'personname' 			=> 'required|max:150',
			'designation' 			=> 'required|max:150',
			'mobilenumber'       => [
				'required',
				'regex:/^[1-9]\d{9}$/',
				'digits:10',
				Rule::unique('users_tbl', 'mobilenumber')
			],
			'email'       => [
				'required',
				'email',
				Rule::unique('users_tbl', 'email')
			],
			'loginpassword'			=> 'required|min:8|max:15',
			'confirmpassword'    	=> 'required|same:loginpassword|max:15',
        ];

        $messages = [
            'departmenttype.required' 		=> 'DEPARTMENT TYPE IS REQUIRED',
			'departmenttype.in' 			=> 'INVALID DEPARTMENT TYPE',
			'departmentname.required' 		=> 'DEPARTMENT NAME IS REQUIRED',
			'departmentname.max' 			=> 'MAXIMUM 100 CHARACTERS ALLOWED',
			'projectname.required' 			=> 'PROJECT NAME IS REQUIRED',
			'projectname.max' 				=> 'MAXIMUM 50 CHARACTERS ALLOWED',
			'personname.required' 			=> 'PERSON NAME IS REQUIRED',
			'personname.max' 				=> 'MAXIMUM 50 CHARACTERS ALLOWED',
			'designation.required' 			=> 'DESIGNATION IS REQUIRED',
			'designation.max' 				=> 'MAXIMUM 50 CHARACTERS ALLOWED',
			'mobilenumber.required' 		=> 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 			=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 			=> 'INVALID MOBILE NUMBER',
			'email.required' 				=> 'EMAIL IS REQUIRED',
			'email.email' 					=> 'INVALID EMAIL ID',
			'loginpassword.required' 		=> 'CREATE PASSWORD IS MANDATORY',
			'loginpassword.min' 			=> 'MINIMUM PASSWORD LENGTH IS 8 CHARACTERS',
			'loginpassword.max' 			=> 'MAXIMUM PASSWORD LENGTH IS 15',
			'confirmpassword.required' 		=> 'CONFIRM PASSWORD IS MANDATORY',
			'confirmpassword.same' 			=> 'CREATE AND CONFIRM PASSWORD MUST BE SAME',
			'confirmpassword.max' 			=> 'MAXIMUM PASSWORD LENGTH IS 15',
			'mobilenumber.unique' 			=> 'THIS MOBILE NUMBER IS ALREADY REGISTERED.',
			'email.unique' 					=> 'THIS EMAIL IS ALREADY REGISTERED.',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		try
		{
			$otp			=	$this->getOTP();
			$isSent = $this->smsService->pushMessage($validatedData['mobilenumber'], $otp, 'OTP', 0, '');
			if (!$isSent) {
				return response()->json([
					'message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
					'status' => 400,
				], 400);
			}
			Session::put('mobilenumber',$validatedData['mobilenumber']);
			Session::put('validotp',$otp);

			return response()->json([
				'message' => 'OTP GENERATED SUCCESSFULLY',
				'status' => 200,
			], 200);

		}
        catch (QueryException $e) 
        {
			Log::error('Get Employee Form Error: ' . $e->getMessage());
            return response()->json(['message'=>'We encountered a technical issue while processing your request. Please try again later.','status'=>400], 400);
        }
		
	}
    public function registerDepartment(Request $request)
	{        
        $rules = [
            'depttype' 			=> 'required|in:INSIDE,OUTSIDE',
			'departmentname' 	=> 'required|',
        ];

        $messages = [
			'mobilenumber.required'		=> __('validation.thisis.required'),
			'mobilenumber.regex'		=> __('validation.thisis.invalid'),
        ];

        $validatedData 	= 	$request->validate($rules,$messages);

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO jobcategory_tbl(jobcategory,createdby,creationdate) VALUES (?,?,?)',[$validatedData['jobcategory'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update jobcategory_tbl set jobcategory=? where categoryid=?',[$validatedData['jobcategory'],$recordid]);
				return redirect('/master/add/jobcategory')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }

}
