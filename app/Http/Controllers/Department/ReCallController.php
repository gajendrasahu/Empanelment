<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Department;
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
use Illuminate\Validation\ValidationException;
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use App\Services\SmsService;
use App\Services\TierWiseDataService;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use App\Services\DeploymentDataService;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOTPMail;

class ReCallController extends Controller
{
	protected $smsService;
	protected $priceService;
    protected $todays_datetime;
    protected $todays_date;	
	protected $deploymentService;
	public function __construct(SmsService $smsService,TierWiseDataService $priceService,DeploymentDataService $deploymentService)
	{
		$this->smsService 			=	$smsService;
		$this->priceService 		=	$priceService;
		ini_set('serialize_precision', -1);
		$this->todays_datetime		=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date			=	Carbon::now()->format('Y-m-d');
		$this->deploymentService	=	$deploymentService;
	}
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if(strpos($number, '.') !== false)
		{
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree = substr($number, -3);
		$rest = substr($number, 0, -3);

		if($rest != '')
		{
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}
    public function addReCall(Request $request,$requestid)
	{
        Session::put('adminmenu','depteoilist');
		Session::put('adminsubmenu','depteoilist');
		Session::put('menid',85);
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$requestid	=	Crypt::decrypt($requestid);
		
		$data	=	DB::table('eoi_request as a')
					->select('a.*','b.departmentname')
					->leftJoin('department_tbl as b','b.userid','=','a.userid')
					->where('a.requestid',$requestid)
					->first();

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

        return view('admin/department/add_recalleoi',compact('data','token'));
		
	}
	public function storeReCall(Request $request,$requestid)
	{
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		
		$request_id	=	Crypt::decrypt($requestid);
		
		try
		{
			DB::beginTransaction();
			
			$eoi	=	DB::table('eoi_request')->where('requestid',$request_id)->first();
			
			$requestId	=	DB::table('eoi_request')->insertGetId([
								'userid'				=>	$eoi->userid,
								'eoinumber'				=>	$eoi->eoinumber,
								'projecttitle'			=>	$eoi->projecttitle,
								'projectduration'		=>	$eoi->projectduration,
								'projectobjective'		=>	$eoi->projectobjective,
								'categoryid'			=>	$eoi->categoryid,
								'createdby'				=>	$eoi->createdby,
								'creationdate'			=>	now(),
								'totalbudget'			=>	$eoi->totalbudget,
								'totaladmincharge'		=>	$eoi->totaladmincharge,
								'totalamount'			=>	$eoi->totalamount,
								'operatingmargin'		=>	$eoi->operatingmargin,
								'tax'					=>	$eoi->tax,
								'admincharge'			=>	$eoi->admincharge,
								'page_indexing'			=>	$eoi->page_indexing,
								'scopeofwork'			=>	$eoi->scopeofwork,
								'anyother'				=>	$eoi->anyother,
								'termsandcondition'		=>	$eoi->termsandcondition,
								'evaluationprocess'		=>	$eoi->evaluationprocess,
								'criticalinformation'	=>	$eoi->criticalinformation,
								'documentrequired'		=>	$eoi->documentrequired,
								'replywithremark'		=>	$eoi->replywithremark,
								'eoistatus'				=>	0,
								'authletter'			=>	$eoi->authletter,
								'authorizationletter'	=>	$eoi->authorizationletter,
								'tier_choice'			=>	$eoi->tier_choice,
								'project_type'			=>	$eoi->project_type,
								'vendorids'				=>	$eoi->vendorids,
								'eoi_file'				=>	$eoi->eoi_file,
								'isSecondCall'			=>	1,
								'oldRequestId'			=>	$request_id
							]);
			
			$records	=	DB::table('eoi_request_detail')->where('requestid',$request_id)->get();
			foreach($records as $record)
			{
				DB::table('eoi_request_detail')->insert([
					'requestid'				=>	$requestId,
					'tierid'				=>	$record->tierid,
					'sectorid'				=>	$record->sectorid,
					'positionid'			=>	$record->positionid,
					'role'					=>	$record->role,
					'experience'			=>	$record->experience,
					'remunerationid'		=>	$record->remunerationid,
					'qualification'			=>	$record->qualification,
					'experiencelevel'		=>	$record->experiencelevel,
					'temp_experiencelevel'	=>	$record->temp_experiencelevel,
					'temp_name'				=>	$record->temp_name,
					'duration'				=>	$record->duration,
					'employmenttype'		=>	$record->employmenttype,
					'budget'				=>	$record->budget,
					'total'					=>	$record->total,
					'creationdate'			=>	now(),
					'name'					=>	$record->name,
					'mobilenumber'			=>	$record->mobilenumber,
					'remark'				=>	$record->remark,
					'remuneration'			=>	$record->remuneration,
					'operating'				=>	$record->operating,
					'operatingvalue'		=>	$record->operatingvalue,
					'tax'					=>	$record->tax,
					'taxvalue'				=>	$record->taxvalue,
					'admin'					=>	$record->admin,
					'admincharge'			=>	$record->admincharge,
					'grandtotal'			=>	$record->grandtotal,
				]);
			}
			
			$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$request_id)->get();
			foreach($attachments as $attachment)
			{
				DB::table('eoi_request_attachment')->insert([
					'eoirequestid'		=>	$requestId,
					'attachmenttitle'	=>	$attachment->attachmenttitle,
					'attachmentfile'	=>	$attachment->attachmentfile,
					'created_by'		=>	$userId,
					'creationdate'		=>	now(),
				]);
			}

			$committees	=	DB::table('eoi_request_committee')->where('requestid',$request_id)->get();
			foreach($committees as $committee)
			{
				DB::table('eoi_request_committee')->insert([
					'requestid'			=>	$requestId,
					'committeeid'		=>	$committee->committeeid,
					'createdby'			=>	$userId,
					'creationdate'		=>	now(),
				]);
			}
			
			DB::table('eoi_request')
			->where('requestid',$request_id)
			->update([
				'iscancelled'	=>	1
			]);
			DB::commit();
			
			return redirect()->route('update.eoidetail', ['recordid' => Crypt::encrypt($requestId)])->with('success', 'Second call submitted successfully. You can now edit the EoI details if any updates are required.');
			//return back()->with('success','Second call submitted successfully!');
			/*
			return redirect('master/dept/eoilist')->with([
				'success'	=>	'EoI floated successfully!'
			]);
			*/
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
	}
}
