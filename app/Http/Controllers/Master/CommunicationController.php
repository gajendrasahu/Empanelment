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

class CommunicationController extends Controller
{
    public function startMessage(Request $request,$requestid=NULL,$rectype=NULL,$vendor_id=NULL)
	{
		try
		{
			//$communications	=	DB::table('communication_tbl')->where('requestid',Crypt::decrypt($requestid))->orderBy('recordid')->get();
			$userId		=	$request->session()->get('userId');
			$userType	=	$request->session()->get('userType');
			
			$exists	=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($requestid))->first();
			if(in_array($userType,['DEPARTMENT', 'PROJECT MANAGER', 'VENDOR']))
			{
				if(!$exists)
				{
					return back()->with('success','Invalid access');
				}
			}
			

			return view('admin/master/communicate_message',compact('requestid','rectype','vendor_id'));
		}
		catch(Exception $e)
		{
			Log:error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);
		}
    }

    public function getCommunicationData(Request $request)
	{
		try
		{
			$userId		=	$request->session()->get('userId');
			$requestid	=	$request->input('requestid');
			$rectype	=	$request->input('rectype');
			$vendor_id	=	$request->input('vendor_id') ?? 0;
			
			
			$vendorId	=	$request->session()->get('vendorId') ?? 0;
			$departmentId=	$request->session()->get('departmentId') ?? 0;
			
			$userType	=	$request->session()->get('userType');
			

			$data	=	DB::table('communication_tbl as a')
						->select(
							'a.recordid',
							'a.requestid',
							'a.fromuserid',
							'a.message',
							'a.message_file',
							'a.creationdate',
							'a.isclosed',
							'a.closedby',
							'a.subuserid',
							'a.vendorids as vids',
							'a.departmentid',
							'b.name',
							'b.isdepartment',
							'b.ispm',
							'b.isvendor',
							'c.eoinumber',
							'c.engagementname',
							'c.vendorids',
							DB::raw("
								CASE 
									WHEN a.departmentid IS NOT NULL THEN d.shortname
									WHEN a.vendorids IS NOT NULL THEN (
										SELECT GROUP_CONCAT(v.shortname SEPARATOR ', ')
										FROM vendor_tbl v
										WHERE FIND_IN_SET(v.vendorid, a.vendorids)
									)
									ELSE ''
								END as sent_to
							")
						)
						->leftJoin('users_tbl as b', 'b.userid', '=', 'a.fromuserid')
						->leftJoin('eoi_request as c', 'c.requestid', '=', 'a.requestid')
						->leftJoin('department_tbl as d', 'd.departmentid', '=', 'a.departmentid')
						->when($vendorId != 0, function ($query) use ($vendorId) {
							$query->addSelect(DB::raw("
								CASE 
									WHEN EXISTS (
										SELECT 1 
										FROM communication_status cs
										WHERE cs.recordid = a.recordid
										AND cs.vendorid = {$vendorId}
										AND cs.seen_status IS NOT NULL
									) THEN 1
									ELSE 0
								END as seen_flag
							"));
						})
						->when($vendorId != 0, function ($query) use ($vendorId) {
							return $query->whereRaw("FIND_IN_SET(?, a.vendorids)", [$vendorId]);
						})
						->when($departmentId != 0, function ($query) use ($departmentId) {
							$query->addSelect(DB::raw("
								CASE 
									WHEN EXISTS (
										SELECT 1 
										FROM communication_status cs
										WHERE cs.recordid = a.recordid
										AND cs.departmentid = {$departmentId}
										AND cs.seen_status IS NOT NULL
									) THEN 1
									ELSE 0
								END as seen_flag
							"));
						})
						->when($departmentId!=0, function ($query) use ($departmentId) {
							return $query->where('a.departmentid', $departmentId);
						})
						->when(!in_array($userType,['DEPARTMENT','PROJECT MANAGER','VENDOR']), function ($query) use ($rectype) {
							if ($rectype == 'DEPT') {
								$query->whereNotNull('a.departmentid');
							} elseif ($rectype == 'FIRM') {
								$query->whereNotNull('a.vendorids');
							}
						})
						->when($vendor_id != 0, function ($query) use ($vendor_id) {
							return $query->where('a.vendorids', Crypt::decrypt($vendor_id));
						})
						->where('a.requestid', Crypt::decrypt($requestid))
						->orderBy('a.recordid', 'DESC')
						->get();		
			
			if(!$data)
			{
				$data	=	[];
			}
			
			if($data->count()!=0)
			{
				$vendorids	=	$data->first()->vendorids;
				$vendorids	=	explode(',',$vendorids);
			}
			else
			{
				$vendorids	=	explode(",",DB::table('eoi_request')->where('requestid',Crypt::decrypt($requestid))->value('vendorids'));
			}
		
			$department	=	DB::table('eoi_request as a')
							->select('b.departmentid as recordid','b.departmentname as label',DB::raw("'DEPT' as usrType"))
							->join('department_tbl as b','b.userid','=','a.userid')
							->where('a.requestid',Crypt::decrypt($requestid))
							->get();
			
			$vendors	=	DB::table('vendor_tbl')
							->select('vendorid as recordid','companyname as label',DB::raw("'VENDOR' as usrType"))
							->whereIn('vendorid',$vendorids)
							->get();			

			$combined = $department->merge($vendors);
			return view('admin/ajaxpages/communicationTable',['data'=>$data,'requestid'=>$requestid,'department'=>$department,'vendors'=>$vendors,'combined'=>$combined]);
		}
		catch(Exception $e)
		{
			Log:error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);
		}
    }

    public function sendConversation(Request $request)
	{
        $rules = [
            'request_id'	=> 'required',
			'message'		=> 'required',
			'message_file' 	=> 'nullable|file|mimes:pdf,doc,docx|max:2044',			
        ];

		$messages = [
			'request_id.required'=>	'The EoI details field is required.',

			'message.required'   =>	'The message field is required.',
			'message.regex'      =>	'The message may only contain letters, numbers, spaces, and the following characters: . , [ ] ( ) - / \ & @',

			'message_file.file'  =>	'The attachment must be a valid file.',
			'message_file.mimes' =>	'The attachment must be a file of type: PDF, DOC, or DOCX.',
			'message_file.max'   =>	'The attachment may not be greater than 2 MB.',
		];
		
        $validatedData = $request->validate($rules,$messages);
		try
		{
			$userId		=	$request->session()->get('userId');
			$userType	=	$request->session()->get('userType');
			$vendorId		=	$request->session()->get('vendorId') ?? 0;
			$departmentId	=	$request->session()->get('departmentId') ?? 0;

			if($userType=='DEPARTMENT' || $userType=='PROJECT MANAGER')
			{
				$exists	=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['request_id']))->where('userid',$userId)->exists();
				if(!$exists)
				{
					return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
				}
			}
			if($userType=='VENDOR')
			{
				$exists	=	DB::table('eoi_request_floated')->where('requestid',Crypt::decrypt($validatedData['request_id']))->where('userid',$userId)->exists();
				if(!$exists)
				{
					return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
				}
			}

			
			if($userType=='DEPARTMENT' || $userType=='PROJECT MANAGER' || $userType=='VENDOR')
			{
				$message_for	=	'CHiPS';
			}
			
		
			$message_file	=	"";
			if($request->hasFile('message_file'))
			{
				$message_file= $request->file('message_file')->store('uploads/message_files','public');
			}
			
			DB::beginTransaction();
			$recordid	=	DB::table('communication_tbl')->insertGetId([
								'requestid'		=>	Crypt::decrypt($validatedData['request_id']),
								'fromuserid'	=>	$userId,
								'message'		=>	$validatedData['message'],
								'message_file'	=>	$message_file ?? NULL,
								'creationdate'	=>	now(),
								'departmentid'	=>	$request->session()->get('departmentId') ?? NULL,
								'vendorids'		=>	$request->session()->get('vendorId') ?? NULL,
								'subuserid'		=>	session('subUserId') ?? NULL
							]);
							
			if($request->session()->get('departmentId'))
			{
				DB::table('communication_status')->insert([
					'recordid'		=>	$recordid,
					'message_for'	=>	'CHiPS'
				]);
			}
			if($request->session()->get('vendorId'))
			{
				DB::table('communication_status')->insert([
					'recordid'		=>	$recordid,
					'message_for'	=>	'CHiPS'
				]);
			}
			DB::table('communication_tbl')->where('requestid',Crypt::decrypt($validatedData['request_id']))->update(['isclosed'=>0]);
			DB::commit();
			
			return response()->json(['status'=>200,'message'=>'Message sent successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
		}
	}

    public function closeConversation(Request $request)
	{
        $rules = [
            'request_id'	=> 'required',
        ];

		$messages = [
			'request_id.required'=>	'The EoI details field is required.',
		];
		
        $validatedData = $request->validate($rules,$messages);
		try
		{
			$userId		=	$request->session()->get('userId');
			$userType	=	$request->session()->get('userType');

			if($userType=='DEPARTMENT' || $userType=='PROJECT MANAGER')
			{
				$exists	=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['request_id']))->where('userid',$userId)->exists();
				if(!$exists)
				{
					return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
				}
			}

			
			if($userType!='DEPARTMENT' && $userType!='VENDOR' && $userType!='PROJECT MANAGER')
			{
				$userType	=	'CHiPS';
			}
			
			DB::table('communication_tbl')->insert([
				'requestid'		=>	Crypt::decrypt($validatedData['request_id']),
				'fromuserid'	=>	$userId,
				'message'		=>	'This topic was closed by '.$userType.' on '.now()->format('d M Y, h:i A'),
				'usertype'		=>	$userType,
				'isclosed'		=>	1,
				'closedby'		=>	$userId,
				'creationdate'	=>	now(),
			]);
			
			DB::table('communication_tbl')->where('requestid',Crypt::decrypt($validatedData['request_id']))->update(['isclosed'=>1]);
			return response()->json(['status'=>200,'message'=>'The conversation topic has been closed successfully.']);
		}
		catch(QueryException $e)
		{
			Log:error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
		}
	}

    public function sendConversationAdmin(Request $request)
	{
        $rules = [
            'request_id'	=> 'required',
			'message'		=> 'required',
			'message_file'	=> 'nullable|file|mimes:pdf,doc,docx|max:2044',
			'department_id' => 'nullable|required_without:vendorids',
			'vendorids'     => 'nullable|array|required_without:department_id',
			'vendorids.*'   => 'exists:vendor_tbl,vendorid',
        ];

		if ($request->department_id && !empty($request->vendorids)) {
			return response()->json([
				'status' => 422,
				'message' => 'You can select either Department / Project Manager OR Firms, not both.'
			]);
		}

		$messages = [
			'request_id.required'=>	'The EoI details field is required.',

			'message.required'   =>	'The message field is required.',
			'message.regex'      =>	'The message may only contain letters, numbers, spaces, and the following characters: . , [ ] ( ) - / \ & @',

			'message_file.file'  =>	'The attachment must be a valid file.',
			'message_file.mimes' =>	'The attachment must be a file of type: PDF, DOC, or DOCX.',
			'message_file.max'   =>	'The attachment may not be greater than 2 MB.',

			'department_id.required_without' => 'Please select a Department / Project Manager OR at least one Firm name.',
			'vendorids.required_without'     => 'Please select at least one Firm name OR a Department / Project Manager.',
			'vendorids.*.exists'             => 'Selected Firm is invalid.',			
		];
		
        $validatedData = $request->validate($rules,$messages);
		try
		{
			if($request->vendorids)
			{
				$forwarded_to	=	'VENDOR';
				$vendorIds 		= 	$request->vendorids;
				$vendorIdsString= 	implode(',', $vendorIds);
			}
			else
			{
				$forwarded_to	=	'DEPARTMENT';
				$vendorIdsString=	NULL;
			}
			
			$userId			=	$request->session()->get('userId');
			$userType		=	$request->session()->get('userType');

			
			$message_file	=	"";
			if($request->hasFile('message_file'))
			{
				$message_file= $request->file('message_file')->store('uploads/message_files','public');
			}
			DB::beginTransaction();
			$recordid	=	DB::table('communication_tbl')->insertGetId([
								'requestid'		=>	Crypt::decrypt($validatedData['request_id']),
								'fromuserid'	=>	$userId,
								'message'		=>	$validatedData['message'],
								'message_file'	=>	$message_file ?? NULL,
								'creationdate'	=>	now(),
								'vendorids'		=>	$vendorIdsString,
								'departmentid'	=>	$validatedData['department_id'] ?? NULL,
							]);
			if($validatedData['department_id'])
			{
				DB::table('communication_status')->insert([
					'recordid'		=>	$recordid,
					'departmentid'	=>	$validatedData['department_id'] ?? NULL,
					'message_for'	=>	'DEPARTMENT'
				]);
			}
			if($vendorIdsString)
			{
				$vendors	=	explode(",",$vendorIdsString);
				foreach($vendors as $vendor)
				{
					DB::table('communication_status')->insert([
						'recordid'		=>	$recordid,
						'vendorid'		=>	$vendor,
						'message_for'	=>	'VENDOR'
					]);					
				}
			}
			DB::table('communication_tbl')->where('requestid',Crypt::decrypt($validatedData['request_id']))->update(['isclosed'=>0]);
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Message sent successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log:error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
		}
	}


    public function markAsRead(Request $request)
	{
		request()->merge([
			'recordid' => Crypt::decrypt(request('recordid'))
		]);
		
        $rules = [
            'recordid'	=> 'required|exists:communication_tbl,recordid',
        ];

		$messages = [
			'recordid.required'	=>	'Communication detail is required.',
			'recordid.exists'	=>	'Invalid communication detail provided.',
		];
		
        $validatedData = $request->validate($rules,$messages);
		try
		{
			$userId		=	$request->session()->get('userId');
			$userType	=	$request->session()->get('userType');

			if($userType=='DEPARTMENT' || $userType=='PROJECT MANAGER')
			{
				DB::table('communication_status')
				->where('recordid',$validatedData['recordid'])
				->where('departmentid',session('departmentId'))
				->whereIn('message_for',['DEPARTMENT','PROJECT MANAGER'])
				->update([
					'seen_status'	=>	1,
					'seen_at'		=>	now(),
					'seen_by'		=>	$userId
				]);
			}
			if($userType=='VENDOR')
			{
				DB::table('communication_status')
				->where('recordid',$validatedData['recordid'])
				->where('vendorid',session('vendorId'))
				->where('message_for','VENDOR')
				->update([
					'seen_status'	=>	1,
					'seen_at'		=>	now(),
					'seen_by'		=>	$userId
				]);
			}

			
			return response()->json(['status'=>200,'message'=>'The message was marked as read successfully.']);
		}
		catch(QueryException $e)
		{
			Log:error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong. Please wait and try again later.']);			
		}
	}
	
}
