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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Config;
use App\Http\Controllers\Master\ResourcesController;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Log;
use Hash;


class VendorController extends Controller
{	
	protected $appUrl;
	protected $passwordService;
	public function __construct(PasswordService $passwordService)
	{
		$this->appUrl	=	Config::get('app.url');
		ini_set('serialize_precision',-1);
        $this->passwordService = $passwordService;
	}
	
    public function vendorList(Request $request)
	{
        Session::put('adminmenu','departments');
		Session::put('adminsubmenu','vendorlist');

		$userId	= $request->session()->get('userId');

		$issuper= $request->session()->get('issuper');

		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		$category	=	DB::table('jobcategory_tbl')->get();
		$vendor		=	DB::table('users_tbl as a')
							->select('a.userid','a.name','b.vendorid')
							->leftjoin('vendor_tbl as b','b.userid','=','a.userid')
							->where('a.isvendor',1)
							->whereNull('a.isSubVendor')
							->orderby('a.name')
							->get();

		$token		=	rand('100000','999999').''.time();
		
		Session::put('form_token', $token);
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
        return view('admin/master/vendor_list',compact('tier','vendor','category','token','sectors'));
    }
	

    public function getVendorData(Request $request)
	{
		$tierid 		=	intval($request->input('tierid'));
		$categoryid 	=	intval($request->input('categoryid'));
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',10);
		$currentPage 	= 	$request->input('page',1);

        $vendors = DB::table('users_tbl as a')
				->select('a.userid','a.name','a.mobilenumber','a.email','a.pass_word','c.tiername','b.vendorid')
				->leftJoin('vendor_tbl as b','b.userid','=','a.userid')
				->leftJoin('tiermaster_tbl as c','c.tierid','=','b.tierid')
                ->when($tierid!=0,function($query) use ($tierid){
                    return $query->where('b.tierid','=',$tierid);
                })
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('b.categoryid','=',$categoryid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.name','like','%'.$pagesearch.'%');
                })
				->whereIn('b.categoryid',[1,2])
				->where('a.isvendor','=',1)
				->whereNull('a.isSubVendor')
                ->orderBy('b.tierid')
				->orderBy('b.categoryid')
				->get();
		
		foreach($vendors as $vendor)
		{
			$vendor->sectors	=	DB::table('vendor_sector as a')
										->select('b.sectorname')
										->leftjoin('sector_tbl as b','b.sectorid','=','a.sectorid')
										->where('a.vendorid','=',$vendor->vendorid)
										->get();
			$vendor->emails	=	DB::table('vendors_email as a')
										->select('a.email')
										->leftjoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
										->where('a.vendorid','=',$vendor->vendorid)
										->get();
		}

		return view('admin/ajaxpages/vendorsTable',['data'=>$vendors]);
	}

    public function storeEmail(Request $request)
	{

		$rules = [
			'vendors_id'	=>	'required',
			'vendors_email' 	=>	'required|email',
		];
        $messages = [
            'vendors_id.required'	=> 'Vendor name is required',
			'vendors_email.required'	=> 'Email address is required',
			'vendors_email.email'		=> 'Invalid email address provided',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			DB::table('vendors_email')->insert([
				'vendorid'	=>	$validatedData['vendors_id'],
				'email'		=>	$validatedData['vendors_email'],
				'createdby'	=>	$request->session()->get('userId'),
			]);
			return response()->json(['status'=>200,'message'=>'Email stored successfully!']);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>'Duplicate email found for same vendors.']);
		}			
		

    }

    public function storeFirm(Request $request,$recordid)
	{        
        $rules = [
			'mobilenumber'	=>	[
				'nullable',
				'regex:/^(?!1234|0000|1111|2222|3333|4444|5555|6666|7777|8888|9999)[6-9][0-9]{9}$/',
				'digits:10'
			],
			'email'       => [
				'required',
				'email:rfc,dns'
			],
			'companyname' 		=> 'required|max:150|regex:/^[a-zA-Z0-9\s.]+$/',
			'shortname' 		=> 'required|max:30|regex:/^[a-zA-Z0-9.]+$/',
			'tierid' 			=> 'required|numeric',
			'categoryid'		=> 'required|numeric',
			'address' 			=> 'required|max:200',
			'loinumber' 		=> 'required|max:100',
			'sector'        	=> 'required_if:categoryid,2|array|min:1',
        ];
		if($recordid==0) {
			$rules['email'][] = Rule::unique('users_tbl', 'email');
		}
        $messages = [
			'companyname.required' 			=> 'Firm name is required',
			'companyname.max' 				=> 'Maximum 100 characters allowed',
			'companyname.regex' 			=> 'Special characters not allowed in address',
			'shortname.required' 			=> 'Short name is required',
			'tierid.required' 				=> 'Please select firms tier',
			'tierid.numeric' 				=> 'Invalid tier value',
			'categoryid.required' 			=> 'Please select category',
			'categoryid.numeric' 			=> 'Invalid category value',
			'shortname.max' 				=> 'Maximum 20 characters allowed',
			'shortname.regex' 				=> 'Special characters not allowed in short name',
			'mobilenumber.required' 		=> 'Mobile number is required',
			'mobilenumber.regex' 			=> 'Invalid mobile number',
			'mobilenumber.digits' 			=> 'Invalid mobile number',
			'email.required' 				=> 'Email is required',
			'email.email' 					=> 'Invalid email id',
			'mobilenumber.unique' 			=> 'This mobile number is already registered.',
			'email.unique' 					=> 'This email is already registered.',
			'address.required' 				=> 'Address is required',
			'address.max' 					=> 'Maximum character length is 200',
			'loinumber.required' 			=> 'LOI Number is required',
			'loinumber.max' 				=> 'Maximum character length is 100',
			'sector.required_if' 			=> 'In case of consultancy, please select at least one sector.',
			'sector.array'       			=> 'Invalid sector selection.',
			'sector.min'         			=> 'Please select at least one sector.',			
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$userType   	=	$request->session()->get('userType');
		
		$sectors		=	$request->input('sectors', []);
		
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
			DB::beginTransaction();
			try
			{
				$plainPassword = $this->passwordService->generatePassword();
				$hashedPassword = Hash::make($plainPassword); // Encrypt password
				
				$userId		=	DB::table('users_tbl')->insertGetId([
									'name' 			=> 	$request->input('companyname'),
									'mobilenumber' 	=> 	$request->input('mobilenumber'),
									'email' 		=> 	$request->input('email'),
									'password' 		=> 	$hashedPassword,
									'pass_word' 	=> 	$plainPassword,
									'usertype' 		=> 	'VENDOR',
									'otp' 			=> 	rand(100001,999999),
									'created_at'	=>	now(),
									'isactive'		=>	1,
									'issuper'		=>	1,
									'isvendor'		=>	1,
								]);

				$vendorId	=	DB::table('vendor_tbl')->insertGetId([
									'userid'		=>	$userId,
									'companyname'	=>	$request->input('companyname'),
									'shortname'		=>	$request->input('shortname'),
									'categoryid'	=>	$request->input('categoryid'),
									'tierid'		=>	$request->input('tierid'),
									'creationdate'	=>	now(),
									'approvalstatus'=>	1,
									'actiondatetime'=>	now(),
									'actiontakenby'	=>	$request->session()->get('userId'),
									'officelocation'=>	$request->input('address'),
									'remark'		=>	'Approved',
									'loinumber'		=>	$validatedData['loinumber'],
								]);

				foreach($request->input('sector', []) as $sectorId) {
					DB::table('vendor_sector')->insert([
						'vendorid' => $vendorId,
						'sectorid' => $sectorId
					]);
				}

				DB::commit();
				return back()->with('success','Firm detail stored successfully!');				
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			DB::beginTransaction();
			try
			{
				$data = DB::table('vendor_tbl')->where('vendorid','=',Crypt::decrypt($recordid))->first();

				DB::update('update vendor_tbl set companyname=?,shortname=?,officelocation=?,categoryid=?,tierid=?,loinumber=? where vendorid=?',[$validatedData['companyname'],$validatedData['shortname'],$validatedData['address'],$validatedData['categoryid'],$validatedData['tierid'],$validatedData['loinumber'],Crypt::decrypt($recordid)]);

				DB::update('update users_tbl set name=?,mobilenumber=?,email=? where userid=?',[$validatedData['companyname'],$validatedData['mobilenumber'],$validatedData['email'],$data->userid]);
				
				DB::table('vendor_sector')->where('vendorid',Crypt::decrypt($recordid))->delete();
				
				foreach($request->input('sector', []) as $sectorId) {
					DB::table('vendor_sector')->insert([
						'vendorid' => Crypt::decrypt($recordid),
						'sectorid' => $sectorId
					]);
				}
				
				DB::commit();
				return redirect('/master/vendor/list')->with('success','Detail updated successfully!');
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				return back()->with('duplicate','Found some problem')->withInput();
			}			
		}
    }
	
    public function editVendor($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		
		$data = DB::table('vendor_tbl as a')
				->select('a.*','b.email','b.mobilenumber')
				->join('users_tbl as b','b.userid','=','a.userid')
				->where('a.vendorid','=',Crypt::decrypt($recordid))
				->first();
		$user = DB::table('users_tbl')->where('userid','=',$data->userid)->first();
		
		$sectors = DB::table('sector_tbl')->orderBy('sectorname')->get();
		$vendorSectors = DB::table('vendor_sector')->where('vendorid',Crypt::decrypt($recordid))->pluck('sectorid')->toArray();

		$sectors = $sectors->map(function ($sector) use ($vendorSectors) {
			$sector->isAvailable = in_array($sector->sectorid, $vendorSectors) ? 1 : 0;
			return $sector;
		});
		
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		$category	=	DB::table('jobcategory_tbl')->get();
		
        return view('admin/master/vendor_edit',compact('data','user','token','sectors','tier','category','vendorSectors'));
    }

    public function sendMessageVendor(Request $request,$vendorid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$userId		=	$request->session()->get('userId');
		$vendorId	=	Crypt::decrypt($vendorid);
		
		$data	=	DB::table('vendor_tbl as a')
					->select('a.*','b.email','b.mobilenumber')
					->join('users_tbl as b','b.userid','=','a.userid')
					->where('a.vendorid','=',$vendorId)
					->first();
		
		$uid	=	$data->userid;
		
		$messages	=	DB::table('chips_vendor_communication')
						->where(function($query) use($uid){
							$query->where('fromuserid',$uid)
								  ->orWhere('touserid',$uid);
						})
						->orderBy('sent_on','DESC')
						->get();
		
		if(session('userType')!='VENDOR')
		{
			DB::table('chips_vendor_communication')
			->where('sentby','VENDOR')
			->where('fromuserid',$uid)
			->whereNull('isviewed')
			->update([
				'isviewed'	=>	1,
				'viewedby'	=>	$userId,
				'viewedon'	=>	now()
			]);
		}
		else
		{
			DB::table('chips_vendor_communication')
			->where('sentby','CHiPS')
			->where('touserid',$uid)
			->whereNull('isviewed')
			->update([
				'isviewed'	=>	1,
				'viewedby'	=>	$userId,
				'viewedon'	=>	now()
			]);
			
		}
			
        return view('admin/master/chips_vendor_communication',compact('token','data','messages'));
    }

    public function storeMessageVendor(Request $request)
	{

		$rules = [
			'userid'	=>	'required',
			'message'	=> [
				'required',
				function ($attribute, $value, $fail) {
					$lower = strtolower($value);
					$decoded = html_entity_decode($lower);
					$patterns = [
						'/<\s*script\b/i',
						'/javascript\s*:/i',
						'/on\w+\s*=/i',
					];
					foreach ($patterns as $pattern) {
						if (preg_match($pattern, $decoded)) {
							$fail("The $attribute contains forbidden scripting code.");
							return;
						}
					}
				}
			],
			'attachment' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
		];
		
        $messages = [
			'userid.required' 	=> 'The user detail is invalid.',
			'message.required'	=> 'Pre-bid query message is required',
			'attachment.file' 	=> 'Invalid file type',
			'attachment.mimes' 	=> 'Invalid file type',
			'attachment.max' 	=> 'Maximum file size allowed is 5 MB.',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$attachment	=	NULL;

		if($request->hasFile('attachment'))
		{
			$attachment	=	$request->file('attachment')->store('uploads/communication', 'public');
		}
		
		try
		{
			$usrid		=	Crypt::decrypt($validatedData['userid']);
			$userId		= 	$request->session()->get('userId');
			$userName   = 	$request->session()->get('userName');
			
			if(session('userType')!='VENDOR')
			{
				DB::table('chips_vendor_communication')
				->insert([
					'fromuserid'	=>	$userId,
					'touserid'		=>	$usrid,
					'message'		=>	$validatedData['message'] ?? NULL,
					'attachment'	=>	$attachment ?? NULL,
					'sent_on'		=>	now(),
					'sentby'		=>	'CHiPS'
				]);
			}
			else
			{
				$usrid	=	DB::table('chips_vendor_communication')->where('touserid',$userId)->value('fromuserid');
				if($usrid=== null){
					$usrid	=	1;
				}				
				DB::table('chips_vendor_communication')
				->insert([
					'fromuserid'	=>	$userId,
					'touserid'		=>	$usrid,
					'message'		=>	$validatedData['message'] ?? NULL,
					'attachment'	=>	$attachment ?? NULL,
					'sent_on'		=>	now(),
					'sentby'		=>	'VENDOR'
				]);				
			}
			return response()->json(['status'=>200,'message'=>'Message sent successfully!']);
		}
		catch(QueryException $e)
		{
			if($attachment!='')
			Storage::disk('public')->delete($attachment);
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}			
		

    }
	
}
