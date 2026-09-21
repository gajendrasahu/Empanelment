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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminPreBidQuery extends Controller
{
	public function viewAllPreBidQuery(Request $request)
	{
		$rules = [
			'requestid'	=>	'required',
		];
		
		$messages = [
			'requestid.required'   => 	'Please provide EoI detail.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId	=	$request->session()->get('userId');
			
			$requestid	=	Crypt::decrypt($validatedData['requestid']);
			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}
			
			$ispassed = 0;
			$today = Carbon::now();
			$prebidDate = Carbon::parse($eoi->prebidlastdate)->setTime(17, 30, 0);

			if($prebidDate->lt($today))
			{
				$ispassed = 1;
			}
			else
			{
				$ispassed = 0;
			}
			
			$data	=	DB::table('eoi_request_prebid as a')
							->select('a.requestid','a.vendorid','a.attachment','a.raisedon','b.companyname','a.prebidstatus')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->where('a.requestid',$eoi->requestid)
							->where('a.isprebid',1)
							->orderBy('a.queryid')
							->get();
							

			foreach($data as $query)
			{
				$query->queries	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$query->requestid)
									->where('vendorid',$query->vendorid)
									->where('isprebid',1)
									->orderBy('questionid')
									->get();
			}
			
	
			$isForwarded	=	DB::table('eoi_request_prebid_forwarded')
								->where('requestid',$requestid)
								->where('postedby','CHiPS')
								->where('isprebid',1)
								->first();

			$replied	=	DB::table('eoi_request_prebid_forwarded')
							->where('requestid', $requestid)
							->where('isprebid',1)
							->where(function ($query) {
								$query->where('postedby', 'DEPARTMENT')
									  ->orWhere('postedby', 'PROJECT MANAGER');
							})
							->first();
			
			$html	=	view('admin.ajaxpages.showAllQueries',['data'=>$data,'eoi'=>$eoi,'ispassed'=>$ispassed,'isForwarded'=>$isForwarded,'replied'=>$replied])->render();
			
			
			return response()->json(['status'=>200,'message'=>'All pre-bid query list.','formhtml' => $html]);

		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

    public function forwardToDepartment(Request $request)
	{	     
		$rules = [
			'requestid' => 'required',
			'prebidenquiry' => [
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
			'requestid.required' 	=> 'EoI detail is required',
			'prebidenquiry.required'=> 'Pre-bid query message is required',
			'attachment.file' 		=> 'Invalid file type',
			'attachment.mimes' 		=> 'Invalid file type',
			'attachment.max' 		=> 'Maximum file size allowed is 5 MB.',
		];
        $validatedData = $request->validate($rules,$messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
		

        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		DB::beginTransaction();
		try 
		{

			$eoi		=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'The provided EoI details are invalid.']);
			}
			$attachment    = $request->file('attachment') ?? NULL;

			if($attachment!='')
			{
				$attachment= $request->file('attachment')->store('uploads/prebidenquiry', 'public');
			}		

			$department	=	DB::table('eoi_request')->where('requestid',$eoi->requestid)->first();
			
			DB::table('eoi_request_prebid_forwarded')->insert([
				'requestid'		=>	$eoi->requestid,
				'fromuserid'	=>	$userId,
				'touserid'		=>	$department->userid,
				'creationdate'	=>	now(),				
				'message'		=>	$validatedData['prebidenquiry'],
				'attachment'	=>	$attachment ?? NULL,
				'postedby'		=>	'CHiPS',
				'isprebid'		=>	1
			]);
			DB::table('eoi_request_prebid')
			->where('requestid',$eoi->requestid)
			->where('isprebid',1)
			->update([
				'prebidstatus'	=>	'FORWARDED',
				'forwardedon'	=>	now()
			]);
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Pre-bid query has been forwarded to the department successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			if($attachment!='')
			Storage::disk('public')->delete($attachment);
			
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function publishToFirms(Request $request)
	{
		if($request->deadlinedate)
		{
			$request->merge([
				'deadlinedate'   => Carbon::parse($request->deadlinedate)->format('Y-m-d H:i:s'),
			]);			
		}
		
		if($request->interviewdate)
		{
			$request->merge([
				'interviewdate'   => Carbon::parse($request->interviewdate)->format('Y-m-d H:i:s'),
			]);			
		}
		
		$rules = [
			'requestid' => 'required',
			'publish_message' => [
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
			'publish_attachment' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
			'deadlinedate' =>	'required|date|after_or_equal:today',
			'interviewdate'=>	'nullable|date|after_or_equal:today',
		];
		
		$messages = [
			'requestid.required' 			=> 'EoI detail is required',
			'publish_message.required'		=> 'Response message is required',
			'publish_attachment.file' 		=> 'Invalid file type',
			'publish_attachment.mimes' 		=> 'Invalid file type',
			'publish_attachment.max' 		=> 'Maximum file size allowed is 5 MB.',
			'deadlinedate.required'			=> 'Last date of submission is required',
			'deadlinedate.date'				=> 'Invalid last date of submission provided',
			'deadlinedate.after_or_equal'	=> 'Invalid last date of submission provided',
			'interviewdate.date'			=> 'Invalid interview date provided',
			'interviewdate.after_or_equal'	=> 'Invalid interview date provided',
		];
        $validatedData = $request->validate($rules,$messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');

		if($request->interviewdate)
		{
			$deadline     = Carbon::parse($validatedData['deadlinedate']);
			$interview    = Carbon::parse($validatedData['interviewdate']);

			if(!$interview->gt($deadline))
			{
				return response()->json(['status'=>400,'message'=>'Interview date must be strictly after the Submission deadline.']);
			}
		}
		

        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		DB::beginTransaction();
		try 
		{

			$eoi	=	DB::table('eoi_request')
						->select('requestid','deadlinedate','interviewdate')
						->where('requestid',Crypt::decrypt($validatedData['requestid']))
						->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'The provided EoI details are invalid.']);
			}

			$publish_attachment	=	NULL;

			if($request->hasFile('publish_attachment'))
			{
				$publish_attachment	=	$request->file('publish_attachment')->store('uploads/prebidenquiry', 'public');
			}


			$result	=	DB::table('eoi_request_floated')
							->selectRaw('GROUP_CONCAT(DISTINCT vendorid) AS vendorids, GROUP_CONCAT(DISTINCT userid) AS userids')
							->where('requestid', $eoi->requestid)
							->first();
			
			DB::table('eoi_request_prebid_broadcast')->insert([
				'requestid'			=>	$eoi->requestid,
				'userid'			=>	$userId,
				'broadcastedon'		=>	now(),				
				'broadcastmessage'	=>	$validatedData['publish_message'],
				'attachment'		=>	$publish_attachment ?? '',
				'vendorids'			=>	$result->vendorids,
				'userids'			=>	$result->userids,
				'isprebid'			=>	1
			]);
			
			DB::table('eoi_request_prebid')
			->where('requestid',$eoi->requestid)
			->where('isprebid',1)
			->update(['prebidstatus'=>'BRODCASTED']);

			if($eoi->deadlinedate!=$validatedData['deadlinedate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	$eoi->requestid,
					'field_name'		=>	'deadlinedate',
					'old_value'			=>	$eoi->deadlinedate,
					'new_value'			=>	$validatedData['deadlinedate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	$userId,
					'marked'			=>	1,
				]);
				DB::table('eoi_request')->where('requestid',$eoi->requestid)->update([
					'deadlinedate'	=>	$validatedData['deadlinedate']
				]);
			}
			
			if($eoi->interviewdate!=$validatedData['interviewdate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	$eoi->requestid,
					'field_name'		=>	'interviewdate',
					'old_value'			=>	$eoi->interviewdate,
					'new_value'			=>	$validatedData['interviewdate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	$userId,
					'marked'			=>	1,
				]);
				
				DB::table('eoi_request')
				->where('requestid',$eoi->requestid)
				->update([
					'interviewdate'	=>	$validatedData['interviewdate']
				]);
			}

			DB::commit();
			return response()->json(['status'=>200,'message'=>'Responses to the pre-bid queries have been successfully shared with all vendors.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			if($publish_attachment!='')
			Storage::disk('public')->delete($publish_attachment);
			
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }


	public function viewBrodcastedPreBidQuery(Request $request)
	{
		$rules = [
			'requestid'	=>	'required',
		];
		
		$messages = [
			'requestid.required'   => 	'Please provide EoI detail.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId	=	$request->session()->get('userId');
			
			$requestid	=	Crypt::decrypt($validatedData['requestid']);
			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}
			
			$ispassed = 0;
			$today = Carbon::today();
			$prebidDate = Carbon::parse($eoi->prebidlastdate);

			if($prebidDate->lt($today))
			{
				$ispassed = 1;
			}
			else
			{
				$ispassed = 0;
			}
			
			$data	=	DB::table('eoi_request_prebid as a')
							->select('a.requestid','a.vendorid','a.attachment','a.raisedon','b.companyname','a.prebidstatus')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->where('a.requestid',$eoi->requestid)
							->orderBy('a.queryid')
							->get();
							

			foreach($data as $query)
			{
				$query->queries	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$query->requestid)
									->where('vendorid',$query->vendorid)
									->orderBy('questionid')
									->get();
			}
			
	
			$isForwarded	=	DB::table('eoi_request_prebid_forwarded')
								->where('requestid',$requestid)
								->where('postedby','CHiPS')
								->first();

			$replied	=	DB::table('eoi_request_prebid_forwarded')
							->where('requestid', $requestid)
							->where(function ($query) {
								$query->where('postedby', 'DEPARTMENT')
									  ->orWhere('postedby', 'PROJECT MANAGER');
							})
							->first();


			$brodcasted	=	DB::table('eoi_request_prebid_broadcast')
								->where('requestid',$requestid)
								->first();

			
			$html	=	view('admin.ajaxpages.showAllQueries',['data'=>$data,'eoi'=>$eoi,'ispassed'=>$ispassed,'isForwarded'=>$isForwarded,'replied'=>$replied,'brodcasted'=>$brodcasted])->render();
			
			
			return response()->json(['status'=>200,'message'=>'All pre-bid query list.','formhtml' => $html]);

		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}


    public function downloadPreBidQuery(Request $request,$requestid)
	{
	
		try
		{
			$userId	=	$request->session()->get('userId');
			
			$requestid	=	Crypt::decrypt($requestid);
			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate','projecttitle')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}
			
			$ispassed = 0;
			$today = Carbon::today();
			$prebidDate = Carbon::parse($eoi->prebidlastdate);

			if($prebidDate->lt($today))
			{
				$ispassed = 1;
			}
			else
			{
				$ispassed = 0;
			}
			
			$data	=	DB::table('eoi_request_prebid as a')
							->select('a.requestid','a.vendorid','a.attachment','a.raisedon','b.companyname','a.prebidstatus')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->where('a.requestid',$eoi->requestid)
							->where('a.isprebid',1)
							->orderBy('a.queryid')
							->get();
							

			foreach($data as $query)
			{
				$query->queries	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$query->requestid)
									->where('vendorid',$query->vendorid)
									->where('isprebid',1)
									->orderBy('questionid')
									->get();
			}
			
	
			$isForwarded	=	DB::table('eoi_request_prebid_forwarded')
								->where('requestid',$requestid)
								->where('postedby','CHiPS')
								->where('isprebid',1)
								->first();

			$replied	=	DB::table('eoi_request_prebid_forwarded')
							->where('requestid', $requestid)
							->where('isprebid',1)
							->where(function ($query) {
								$query->where('postedby', 'DEPARTMENT')
									  ->orWhere('postedby', 'PROJECT MANAGER');
							})
							->first();


			$brodcasted	=	DB::table('eoi_request_prebid_broadcast')
								->where('requestid',$requestid)
								->where('isprebid',1)
								->first();

			
			
			$html	=	PDF::loadView('admin.ajaxpages.downloadprebidquery',compact('data','eoi'))->setPaper('A4','landscape');
			
			$filename	=	$eoi->eoinumber."_prebid.pdf";
			
			return $html->download($filename);


		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function downloadDeptPmPreBid(Request $request,$requestid)
	{
		try
		{
			$userId		=	$request->session()->get('userId');
			$requestid	=	Crypt::decrypt($requestid);

			$exists	=	DB::table('eoi_request')->where('requestid',$requestid)->where('userid',$userId)->exists();
			if(!$exists)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}

			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate','projecttitle')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}
			
			$data	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$requestid)
									->where('isprebid',1)
									->orderBy('questionid')
									->get();			
	
			$html	=	PDF::loadView('admin.ajaxpages.downloaddeptpm',compact('data','eoi'))->setPaper('A4','landscape');
			$filename	=	$eoi->eoinumber."_query.pdf";
			
			return $html->download($filename);
		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

}
