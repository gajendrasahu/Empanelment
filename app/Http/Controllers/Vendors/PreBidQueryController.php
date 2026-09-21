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
use Illuminate\Support\Str;
use App\Services\LogServices;

class PreBidQueryController extends Controller
{
	protected $logService;
	protected $usr;
	public function __construct(LogServices $logService,Request $request)
	{
		$this->logService 	=	$logService;
		$this->middleware(function ($request, $next) {
			$this->usr = DB::table('users_tbl')
				->where('userid', $request->session()->get('userId'))
				->first();

			return $next($request);
		});		
	}


	public function storeQuery(Request $request)
	{
		$rules = [
			'contact_name'			=>	'required|regex:/^[A-Za-z]+(?:[ .][A-Za-z]+)*$/',
			'contact_number'		=>	'required|regex:/^[6-9][0-9]{9}$/',
			'contact_designation'	=>	'required|required|regex:/^[A-Za-z]+(?:[ .][A-Za-z]+)*\.?$/',
			'float_id'				=>	'required',
			'references'			=>	'required',
			'details'				=>	'required',
			'queries'  				=> 	'required',
		];
		
		$messages = [
			'float_id.required'			=> 	'EoI detail is required.',
			'references.required'   	=> 	'Please enter clause reference and page number.',

			'details.required'      	=> 	'Please enter clause details.',
			'queries.required'      	=> 	'Please enter queries with justification.',

			'contact_name.required' 	=> 	'Raised by name is required.',
			'contact_name.regex' 		=> 	'Raised by name may contain only alphabets, spaces, and one dot.',

			'contact_number.required' 	=> 	'Contact number is required.',
			'contact_number.regex' 		=> 	'Contact number must be a valid 10 digit mobile number starting with 6, 7, 8, or 9.',

			'contact_number.required' 	=> 	'Contact number is required.',
			'contact_number.regex' 		=> 	'Contact number must be a valid 10 digit mobile number starting with 6, 7, 8, or 9.',

			'contact_designation.required' => 'Designation is required.',
			'contact_designation.regex' => 'The designation may only contain alphabets, spaces.'
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId		= 	$request->session()->get('userId');
			$floatid	=	Crypt::decrypt(Session::get('float_id'));
			
			$float	=	DB::table('eoi_request_floated')->where('floatid',$floatid)->first();
			$prebid	=	DB::table('eoi_request_prebid')
						->where('requestid',$float->requestid)
						->where('vendorid',$float->vendorid)
						->where('isprebid',1)
						->first();
			if(!$prebid)
			{
				$queryId	=	DB::table('eoi_request_prebid')
								->insertGetId([
									'requestid'				=>	$float->requestid,
									'vendorid'				=>	$float->vendorid,
									'raisedon'				=>	now(),
									'subuserid'				=>	session('subUserId') ?? NULL,
									'isprebid'				=>	1,
									'contact_name'			=>	$validatedData['contact_name'],
									'contact_number'		=>	$validatedData['contact_number'],
									'contact_designation'	=>	$validatedData['contact_designation'],
								]);
			}
			else
			{
				$queryId	=	DB::table('eoi_request_prebid')
								->where('requestid',$float->requestid)
								->where('vendorid',$float->vendorid)
								->where('isprebid',1)
								->value('queryid');
				
				DB::table('eoi_request_prebid')
				->where('requestid',$float->requestid)
				->where('vendorid',$float->vendorid)
				->where('queryid',$queryId)
				->update([
					'contact_name'		 =>	$validatedData['contact_name'],
					'contact_number'	 =>	$validatedData['contact_number'],
					'contact_designation'=>	$validatedData['contact_designation'],					
				]);
			}
			
			DB::table('eoi_request_prebid_query')
			->insert([
				'queryid'					=>	$queryId,
				'requestid'					=>	$float->requestid,
				'vendorid'					=>	$float->vendorid,
				'clause_reference'			=>	$validatedData['references'] ?? NULL,
				'clause_detail'				=>	$validatedData['details'] ?? NULL,
				'queries_with_justification'=>	$validatedData['queries'] ?? NULL,
				'creationdate'				=>	now(),
				'iseditable'				=>	1,
				'subuserid'					=>	session('subUserId') ?? NULL,
				'isprebid'					=>	1
			]);
			
			DB::beginTransaction();
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Pre-Bid Query added successfully.<br><br>If you have more queries, please add them. <br><br>Otherwise, attach the cover letter and click ‘Forward to CHiPS’ to submit the Pre-Bid Query to CHiPS.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

	public function updatePreBidQuery(Request $request)
	{
		$rules = [
			'references'	=>	'required',
			'details'		=>	'required',
			'queries'  		=> 	'required',
		];
		
		$messages = [
			'references.required'   => 	'Please enter clause reference and page number.',
			'details.required'      => 	'Please enter clause details.',
			'queries.required'      => 	'Please enter queries with justification.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId		= 	$request->session()->get('userId');
			$floatid	=	Crypt::decrypt(Session::get('float_id'));
			DB::beginTransaction();
			DB::table('eoi_request_prebid_query')
			->where('questionid',Crypt::decrypt(Session::get('question_id')))
			->update([
				'clause_reference'			=>	$validatedData['references'] ?? NULL,
				'clause_detail'				=>	$validatedData['details'] ?? NULL,
				'queries_with_justification'=>	$validatedData['queries'] ?? NULL,
			]);
			
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Pre-Bid Query updated successfully.','redirect_url' => route('prebid.enquiry',Session::get('float_id'))]);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

	public function uploadPreBidFile(Request $request)
	{
		$rules = [
			'float_id'		=>	'required',
			'attachment' 	=> 	'required|file|mimes:pdf,doc,docx|max:10240',
		];
		
		$messages = [
			'float_id.required' =>	'Invalid EoI detail.',
			'attachment.file'	=> 	'Invalid file type',
			'attachment.mimes' 	=> 	'Invalid file type',
			'attachment.max' 	=> 	'Maximum file size allowed is 10 MB.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId	= 	$request->session()->get('userId');
			
			$floated=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($validatedData['float_id']))->first();
			
			$attachment	=	$request->file('attachment');
			
			if($attachment!='')
			{
				$attachment	=	$request->file('attachment')->store('uploads/prebidenquiry','public');
			}
			$prebid	=	DB::table('eoi_request_prebid')
						->where('requestid',$floated->requestid)
						->where('vendorid',$floated->vendorid)
						->first();
						
			DB::table('eoi_request_prebid')
			->where('requestid',$floated->requestid)
			->where('vendorid',$floated->vendorid)
			->update([
				'attachment'	=>	$attachment
			]);
			if($prebid->attachment!=NULL)
			{
				Storage::disk('public')->delete($prebid->attachment);
			}
			return response()->json(['status'=>200,'message'=>'Pre-Bid Query file uploaded successfully.','redirect_url' => route('prebid.enquiry',$validatedData['float_id'])]);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

	public function forwardPreBidQuery(Request $request)
	{
		$rules = [
			'float_id'	=>	'required',
		];
		
		$messages = [
			'float_id.required' =>	'Invalid EoI detail.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId	= 	$request->session()->get('userId');
			
			$floated=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($validatedData['float_id']))->first();
			
			DB::table('eoi_request')->where('requestid',$floated->requestid)->update(['isinprebid'=>1]);
			
			DB::table('eoi_request_prebid')
			->where('requestid',$floated->requestid)
			->where('vendorid',$floated->vendorid)
			->where('isprebid',1)
			->update([
				'forwardedon'	=>	now()
			]);
			
			return response()->json(['status'=>200,'message'=>'Pre-Bid Query forwarded successfully.','redirect_url' => route('prebid.enquiry',$validatedData['float_id'])]);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

}


