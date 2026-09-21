<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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

class CommitteeMember extends Controller
{
    public function viewEoiCommitteeMember(Request $request,$requestid=NULL)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid'))
		]);
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
        ];

        $messages = [
			'requestid.required'      => 'Request details are required to proceed.',
			'requestid.exists'        => 'The selected request details are invalid or do not exist.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$userType		=	$request->session()->get('userType');
		$departmentId	=	$request->session()->get('departmentId');
		
		try
		{
			if($userType=='DEPARTMENT' || $userType=='PROJECT MANAGER')
			{
				$exists	=	DB::table('eoi_request')->where('requestid',$validatedData['requestid'])->where('userid',$userId)->exists();
				if(!$exists)
				{
					return response()->json([
						'errors' => [
							'resume'	=>	['<b style="color:red;">The Expression of Interest (EoI) details do not match the currently logged-in user.</b>'],
						]
					], 422);			
					
				}
			}
			$eoi		=	DB::table('eoi_request')->select('requestid','eoinumber','projecttitle','iscancelled')->where('requestid',$validatedData['requestid'])->first();
			
			$activeMembers	=	DB::table('committee_member')
								->select('memberid','name','mobilenumber','email','designation','department')
								->where('userid',$userId)
								->where('isdeleted',0)
								->get();
			
			$members	=	DB::table('eoi_request_committee as a')
							->select('a.memberid as recordid','b.memberid','b.name','b.mobilenumber','b.email','b.designation','b.department')
							->join('committee_member as b','b.memberid','=','a.committeeid')
							->where('a.requestid',$validatedData['requestid'])
							->where('b.userid',$userId)
							->get();
			
			$moreMember = $activeMembers->whereNotIn('memberid',$members->pluck('memberid'));
			
			$html	=	view('admin.ajaxpages.eoicommitteeMember',['data'=>$members,'eoi'=>$eoi,'activeMembers'=>$activeMembers,'moreMember'=>$moreMember])->render();
			
			return response()->json(['status'=>200,'message'=>'EoI Committee Member.','html' => $html]);
		}
		catch(Exception $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function updateEoICommitteeMember(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid')),
			'recordid' 	=> Crypt::decrypt(request('recordid')),
		]);
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'recordid'		=>	'required|exists:eoi_request_committee,memberid',
			'memberid'		=>	'required|exists:committee_member,memberid',
        ];

        $messages = [
			'requestid.required'=> 'Request details are required to proceed.',
			'requestid.exists'  => 'The selected request details are invalid or do not exist.',
			'recordid.required'	=> 'Request details are required to proceed.',
			'recordid.exists'   => 'The selected request details are invalid or do not exist.',
			'memberid.required'	=> 'Request details are required to proceed.',
			'memberid.exists'   => 'The selected request details are invalid or do not exist.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$userType		=	$request->session()->get('userType');
		$departmentId	=	$request->session()->get('departmentId');
		
		try
		{
			DB::table('eoi_request_committee')
			->where('memberid',$validatedData['recordid'])
			->where('requestid',$validatedData['requestid'])
			->update([
				'committeeid'	=>	$validatedData['memberid']
			]);
			return response()->json(['status'=>200,'message'=>'EoI Committee Member Updated Successfully.','requestid' => Crypt::encrypt($validatedData['requestid'])]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>'The selected committee member already exists for this EoI. Please check and try again.']);
		}
    }

    public function removeEoICommitteeMember(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid')),
			'recordid' 	=> Crypt::decrypt(request('recordid')),
		]);
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'recordid'		=>	'required|exists:eoi_request_committee,memberid',
        ];

        $messages = [
			'requestid.required'=> 'Request details are required to proceed.',
			'requestid.exists'  => 'The selected request details are invalid or do not exist.',
			'recordid.required'	=> 'Request details are required to proceed.',
			'recordid.exists'   => 'The selected request details are invalid or do not exist.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$userType		=	$request->session()->get('userType');
		$departmentId	=	$request->session()->get('departmentId');
		
		try
		{
			$count	=	DB::table('eoi_request_committee')->where('requestid',$validatedData['requestid'])->where('createdby',$userId)->count();
			if($count>3)
			{
				DB::table('eoi_request_committee')
				->where('memberid',$validatedData['recordid'])
				->where('requestid',$validatedData['requestid'])
				->delete();
				
				return response()->json(['status'=>200,'message'=>'EoI committee member removed successfully.','requestid' => Crypt::encrypt($validatedData['requestid'])]);
			}
			else
			{
				return response()->json(['status'=>400,'message'=>'The committee member list must contain at least 3 members.']);
			}
		}
		catch(Exception $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function addEoICommitteeMember(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid')),
		]);
        $rules = [
			'requestid'	=>	'required|exists:eoi_request,requestid',
			'memid'		=>	'required|exists:committee_member,memberid',
        ];

        $messages = [
			'requestid.required'=> 'Request details are required to proceed.',
			'requestid.exists'  => 'The selected request details are invalid or do not exist.',
			'memid.required'	=> 'Please provide committee member name.',
			'memid.exists'  	=> 'The committee member detail is invalid or do not exist.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$userType		=	$request->session()->get('userType');
		$departmentId	=	$request->session()->get('departmentId');
		
		try
		{
			DB::table('eoi_request_committee')->insert([
				'requestid'		=>	$validatedData['requestid'],
				'committeeid'	=>	$validatedData['memid'],
				'createdby'		=>	$userId,
				'creationdate'	=>	now()
			]);
			return response()->json(['status'=>200,'message'=>'EoI committee member added successfully.','requestid' => Crypt::encrypt($validatedData['requestid'])]);
		}
		catch(Exception $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }
    
}
