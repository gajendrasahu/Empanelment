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
use Illuminate\Support\Facades\Crypt;
use App\Services\SmsService;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

use App\Mail\WorkOrderExpiryEmail;

class ProfileController extends Controller
{
	protected $smsService;
	public function __construct(SmsService $smsService)
	{
		$this->smsService 			=	$smsService;
	}
	
    public function sendOtpMessage(Request $request)
	{
		$mobilenumber	=	"9479020075";
		$otp			=	"555555";
		
		$isSent = $this->smsService->pushTestingMessage($mobilenumber,$otp,'OTP',0,'');
		
		if($isSent)
		{
			return response()->json([
				'message'	=> 	'OTP Generated Successfully',
				'status' 	=>	200,
			], 200);		
		}
		else
		{
			return response()->json([
				'message'	=>	'OTP could not be sent. Please try again.',
				'status' 	=> 	422,
			], 422);			
		}
    }

	
	
    public function editData($recordid){

        $data = DB::table('applicationusers')->where('userid','=',$recordid)->first();

        return view('admin/master/profile_edit',compact('data'));

    }

    public function addData(){

        return view('admin/master/add_form');

    }

    public function editPassword(Request $request,$recordid)
	{
        Session::put('adminmenu','changepassword');
		Session::put('menid',21);
        $data = DB::table('applicationusers')->where('userid','=',Crypt::decrypt($recordid))->first();
		$userId		= $request->session()->get('loginId');
		$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=21 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
		$actions	=	explode(",",$action->actions);
		Session::put('actions', $actions);
        return view('admin/master/password_edit',compact('data'));
    }

    public function editAbout(){

        $data = DB::table('aboutus')->where('Id','=',1)->first();

        return view('admin/master/about_edit',compact('data'));

    }

    public function editTerm(){

        $data = DB::table('termsandcondition')->where('Id','=',1)->first();

        return view('admin/master/term_edit',compact('data'));

    }
    public function editPrivacy(){

        $data = DB::table('privacypolicy')->where('Id','=',1)->first();

        return view('admin/master/privacy_edit',compact('data'));

    }

    public function updateData(Request $request,$recordid){
        
        $rules = [
            'name' => 'required|max:100',
            'mobilenumber'   => 'required|regex:/^[1-9]\d{9}$/|digits:10',
            'email' => 'required|email|max:100',
            'address' => 'required',
            'profilepic' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        $messages = [
            'name.required' => 'PROFILE NAME IS REQUIRED',
            'name.max' => 'MAXIMUM LENGTH IS 100',
            'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' => 'INVALID MOBILE NUMBER',
            'mobilenumber.digits' => 'INVALID MOBILE NUMBER',
            'email.required' => 'EMAIL IS REQUIRED',
            'email.email' => 'INVALID EMAIL',
            'email.max' => 'MAXIMUM LENGTH IS 100',
            'address.required' => 'ADDRESS IS REQUIRED',
            'profilepic.image' => 'NOT AN IMAGE',
            'profilepic.mimes' => 'INVALID IMAGE TYPE',
            'profilepic.max' => 'MAXIMUM FILE SIZE SHOULD BE 2 MB ONLY',
        ];

        $validatedData = $request->validate($rules, $messages);
        

        $userId      = $request->session()->get('loginId');

        $name        = strtoupper($request->input('name'));
        $mobilenumber= $request->input('mobilenumber');
        $email       = strtolower($request->input('email'));
        $address     = $request->input('address');
        $profilepic  = $request->file('profilepic');

        DB::beginTransaction();

        try {
            // Retrieve the authenticated user's information from the users table
            $user = DB::table('applicationusers')->where('userid', $userId)->first();

            // Delete the old image if it exists
            if ($user->profilepic && $profilepic) {
                // Delete the old image using a DB query
                DB::table('applicationusers')
                    ->where('userid', $userid)
                    ->update(['profilepic' => '']);

                // Delete the old image file from storage
                Storage::disk('public')->delete($user->profilepic);
            }

            if($profilepic)
            {
                // Store the new uploaded image
                $imagePath = $request->file('profilepic')->store('uploads/images', 'public');

                // Update the image information in the users table
                DB::table('applicationusers')
                ->where('userid', $userId)
                ->update(['profilepic' => $imagePath]);
            }

            DB::table('applicationusers')
            ->where('userid', $userId)
            ->update([
                'name'=>$name,
                'mobilenumber'=>$mobilenumber,
                'email'=>$email,
                'address'=>$address
            ]);

            // Commit the transaction
            DB::commit();

            // Redirect back with a success message
            return redirect('edit/profile/'.$userId)->with('success','PROFILE DETAIL UPDATED SUCCESSFULLY!');
        }
        catch (QueryException $e) 
        {
            DB::rollback();
            
            if ($e->getCode() == 23000) 
            { 
                $errorMessage = 'PROFILE DETAIL COULD NOT BE UPDATED.';
            }
            return back()->with('duplicate',$e->getMessage())->withInput();
        }

    }


    public function updatePassword(Request $request,$recordid){
        
        $rules = [
            'oldpassword'   => 'required',
            'password'   => 'required',
            'confirmpassword'   => 'required',
        ];

        $messages = [
            'oldpassword.required' => 'OLD PASSWORD IS REQUIRED',
            'password.required' => 'PASSWORD IS REQUIRED',
            'confirmpassword.required' => 'PASSWORD IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
        

        $userId      = $request->session()->get('loginId');
        $password    = $request->input('password');
        $oldpassword = (String) $request->input('oldpassword');

        

        try {
            DB::beginTransaction();
            // Retrieve the authenticated user's information from the users table
            $user = DB::table('applicationusers')->where('userid','=',$userId)->first();

            if($oldpassword==$user->password)
            {
                DB::table('applicationusers')
                ->where('userid', $userId)
                ->update([
                    'password'=>$password,
                ]);
                DB::commit();
                return redirect('edit/password/'.Crypt::encrypt($userId))->with('success','PASSWORD DETAIL UPDATED SUCCESSFULLY!');
            }
            else
            {
                return back()->with('duplicate','OLD PASSWORD DOES NOT MATCHED.')->withInput();
            }
            // Commit the transaction
        }
        catch (QueryException $e) 
        {
            DB::rollback();
            
            if ($e->getCode() == 23000) 
            { 
                $errorMessage = 'PROFILE DETAIL COULD NOT BE UPDATED.';
            }
            return back()->with('duplicate',$e->getMessage())->withInput();
        }

    }

    public function updateAbout(Request $request,$recordid){
        
        $rules = [
            'aboutus' => 'required',
        ];

        $messages = [
            'aboutus.required' => 'ABOUT US CONTENT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
        

        $userId      = $request->session()->get('loginId');

        $aboutus = $request->input('aboutus');
        $currentDateTime= now();
        $entrydatetime  = $currentDateTime->format('Y-m-d H:i:s');

        try {
                DB::beginTransaction();
                DB::table('aboutus')
                ->where('Id', $recordid)
                ->update([
                    'AboutUs'=>$aboutus,
                    'UpdatedOn'=>$entrydatetime,
                    'UpdatedBy'=>$userId
                ]);
                DB::commit();
                return redirect('edit/aboutus')->with('success','ABOUT US DETAIL UPDATED SUCCESSFULLY!');
        }
        catch (QueryException $e) 
        {
            DB::rollback();
            
            if ($e->getCode() == 23000) 
            { 
                $errorMessage = 'ABOUT US DETAIL COULD NOT BE UPDATED.';
            }
            return back()->with('duplicate',$e->getMessage())->withInput();
        }

    }


    public function updateTerm(Request $request,$recordid){
        
        $rules = [
            'terms' => 'required',
        ];

        $messages = [
            'terms.required' => 'TERMS AND CONDITIONS CONTENT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
        

        $userId      = $request->session()->get('loginId');

        $terms = $request->input('terms');
        $currentDateTime= now();
        $entrydatetime  = $currentDateTime->format('Y-m-d H:i:s');

        try {
                DB::beginTransaction();
                DB::table('termsandcondition')
                ->where('Id', $recordid)
                ->update([
                    'Terms'=>$terms,
                    'UpdatedOn'=>$entrydatetime,
                    'UpdatedBy'=>$userId
                ]);
                DB::commit();
                return redirect('edit/terms')->with('success','TERMS AND CONDITIONS DETAIL UPDATED SUCCESSFULLY!');
        }
        catch (QueryException $e) 
        {
            DB::rollback();
            
            if ($e->getCode() == 23000) 
            { 
                $errorMessage = 'TERMS AND CONDITIONS DETAIL COULD NOT BE UPDATED.';
            }
            return back()->with('duplicate',$e->getMessage())->withInput();
        }

    }

    public function updatePrivacy(Request $request,$recordid){
        
        $rules = [
            'privacy' => 'required',
        ];

        $messages = [
            'privacy.required' => 'PRIVACY POLICY CONTENT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
        

        $userId      = $request->session()->get('loginId');

        $privacy = $request->input('privacy');
        $currentDateTime= now();
        $entrydatetime  = $currentDateTime->format('Y-m-d H:i:s');

        try {
                DB::beginTransaction();
                DB::table('privacypolicy')
                ->where('Id', $recordid)
                ->update([
                    'Privacy'=>$privacy,
                    'UpdatedOn'=>$entrydatetime,
                    'UpdatedBy'=>$userId
                ]);
                DB::commit();
                return redirect('edit/privacy')->with('success','PRIVACY POLICY DETAIL UPDATED SUCCESSFULLY!');
        }
        catch (QueryException $e) 
        {
            DB::rollback();
            
            if ($e->getCode() == 23000) 
            { 
                $errorMessage = 'PRIVACY POLICY DETAIL COULD NOT BE UPDATED.';
            }
            return back()->with('duplicate',$e->getMessage())->withInput();
        }

    }

    public function applicationSettings(Request $request)
	{
		$features	=	DB::table('setting_tbl')->orderBy('featureid')->get();
        return view('admin/master/application_settings',compact('features'));
    }

    public function updateApplicationSettings(Request $request)
	{
		$selected = $request->featureid ?? [];
		DB::table('setting_tbl')->update([
			'feature_status' => 0
		]);

		if(!empty($selected))
		{
			DB::table('setting_tbl')
			->whereIn('featureid',$selected)
			->update([
				'feature_status' => 1
			]);
		}
		cache()->forget('email_feature_status');
		cache()->forget('sms_feature_status');
		return back()->with('success', 'Feature status updated successfully');
    }

	public function sendWorkOrderExpiryNotification(Request $request)
	{
		try 
		{	
			$reminderDays	= 	[60,45,30,15,7,3,1];
			
			$today 			=	Carbon::today();
			
			$workOrders		= 	DB::table('eoi_work_order as wo')
								->join('department_tbl as d', 'd.departmentid','=','wo.department_id')
								->leftJoin('eoi_request as eoi','eoi.requestid','=','wo.requestid')
								->whereNotNull('wo.workorderduedate')
								->whereDate('wo.workorderduedate','>=',$today)
								->where('wo.isActiveOrder',1)
								->where('wo.sendDueReminder',1)
								->select('wo.*','d.departmentname','d.officialemail','eoi.projecttitle','eoi.eoinumber','eoi.engagementname')
								->get();

			$sentCount		= 	0;

			foreach ($workOrders as $workOrder)
			{
				$dueDate 		=	Carbon::parse($workOrder->workorderduedate);
				$remainingDays 	= 	$today->diffInDays($dueDate);
				
				if(!in_array($remainingDays,$reminderDays))
				{
					continue;
				}
				
				if(empty($workOrder->officialemail))
				{
					continue;
				}
				
				/*
				Mail::to($workOrder->officialemail)
				->cc([
					'ceo@cgchips.in',
					'jceop@cgchips.in',
					'jceo.finance@cgchips.in',
					'singh.ranjeet@cgchips.in',
					'divya_tiwari@cgchips.in',
					'empl.chips@cgchips.in',
					'empl_finance@cgchips.in',
					'empl_hr@cgchips.in',
				])
				->send(new WorkOrderExpiryEmail($workOrder,$remainingDays));
				*/
				//Mail::to('gajendrasahu09@gmail.com')->send(new WorkOrderExpiryEmail($workOrder,$remainingDays));
				//Mail::to('gajendrasahu09@gmail.com')->send(new WorkOrderExpiryEmail($workOrder,$remainingDays));
				
				$sentCount++;
			}

			return response()->json([
				'status'	=>	'success',
				'message'	=>	$sentCount . ' work order reminder(s) processed successfully.'
			]);
		}
		catch (\Exception $e)
		{

			Log::error('Work order expiry notification failed',['error' => $e->getMessage()]);
			
			return response()->json([
				'status'	=>	'error',
				'message'	=>	'Failed to process work order expiry notifications.'
			],500);
		}
	}

}
