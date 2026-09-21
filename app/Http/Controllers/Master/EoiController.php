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
use App\Services\LogServices;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use ZipArchive;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Services\TierWiseDataService;
use App\Services\PdfServices;
use App\Mail\FloatEoiMail;
use App\Mail\PublishMail;
use App\Mail\PublishNotificationMail;
use App\Mail\ApprovalOTPMail;
use App\Mail\InterviewLinkMail;
//use App\Mail\PublishPreBidMail;
use App\Mail\DeptEoiExtensionMail;
use App\Mail\FirmEoiExtensionMail;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Shared\Html;
use App\Services\FeatureService;

use App\Services\DateAvailabilityService;

class EoiController extends Controller
{
	protected $logService;
	protected $priceService;
	protected $pdfService;
	protected $usr;
	protected $featureService;
	protected $dateService;
	public function __construct(LogServices $logService,TierWiseDataService $priceService,PdfServices $pdfService,FeatureService $featureService,DateAvailabilityService $dateService,Request $request)
	{
		$this->dateService 		= 	$dateService;
		$this->featureService 	= 	$featureService;
		$this->logService 		=	$logService;
		$this->priceService		=	$priceService;
		$this->pdfService		=	$pdfService;
		
		$this->middleware(function ($request, $next) {
			$this->usr = DB::table('users_tbl')
				->where('userid', $request->session()->get('userId'))
				->first();

			return $next($request);
		});		
	}
	
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if (strpos($number, '.') !== false) {
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree = substr($number, -3);
		$rest = substr($number, 0, -3);

		if ($rest != '') {
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}	
    public function eoiRequestList(Request $request,$firm_type=NULL,$record_type=NULL){

		if(Session('userType')!='ADMIN') {
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','eoirequestlist');
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		//$departments	=	DB::table('department_tbl')->orderby('departmentname')->get();
		
		$departments	=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->where('b.isdepartment',1)
							->orderby('b.name')
							->get();

		$managers		=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->where('b.ispm',1)
							->orderby('b.name')
							->get();

		$projects		=	DB::table('project_tbl')
							->select('projectid','project_name')
							->orderby('project_name')
							->get();


        return view('admin/master/eoirequest_list',compact('category','departments','managers','firm_type','record_type','projects'));
    }
	
    public function getEoiRequestData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		$categoryid 	=	$request->input('categoryid');
		$projectid 		=	$request->input('projectid') ?? 0;
		$deptid 		=	$request->input('departmentid');
		$eoi_status 	=	$request->input('eoi_status') ?? '';
		$olddata 		=	$request->input('olddata');

		$managerid 		=	$request->input('managerid');
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$is_cancelled	= 	($request->input('is_cancelled')==='true') ? 1 : 0;
		$is_closed		= 	($request->input('is_closed')==='true') ? 1 : 0;
		
		$firm_type 		=	$request->input('firm_type');
		$record_type	=	$request->input('record_type');

		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize',100);
		$currentPage= 	$request->input('page', 1);
		$data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','b.ispm','c.departmentname','c.shortname','d.jobcategory','e.cancellation_remark','e.cancellation_attachment','e.canReqOn','f.project_name')
							->selectRaw("
								CASE 
									WHEN EXISTS (
										SELECT 1 
										FROM eoi_request_floated p 
										WHERE p.requestid = a.requestid and p.signedcopyofeoi IS NOT NULL
									) 
									THEN 1 ELSE 0 
								END as isParticipated
							")
							->selectRaw("
								(
									SELECT u.name 
									FROM users_tbl u
									WHERE u.userid = (
										SELECT ea.closedby 
										FROM eoi_approval ea 
										WHERE ea.requestid = a.requestid 
										  AND ea.closedby IS NOT NULL
										LIMIT 1
									)
								) as approvedBy
							")
							->selectRaw("
								CASE
									WHEN a.isordered = 1
									 AND EXISTS (
										SELECT 1
										FROM eoi_resource_deployment dr
										WHERE dr.orderid = (
											SELECT ew.orderid
											FROM eoi_work_order ew
											WHERE ew.requestid = a.requestid
											ORDER BY ew.orderid ASC
											LIMIT 1
										)
										AND dr.deployment_status IN ('Active', 'Extended')
									 )
									THEN 1
									ELSE 0
								END as isdeployed
							")							
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->when($in_house == 1, function ($query) {
								$query->join('users_tbl as u', function ($join) {
									$join->on('u.userid','=','c.userid')
										 ->where('u.ispm','=',1)
										 ->where('a.categoryid',2);
								});
							})
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->leftJoin('eoi_cancellation as e','e.requestid','=','a.requestid')
							->leftJoin('project_tbl as f','f.projectid','=','a.projectid')							
							->when($pagesearch != '', function($query) use ($pagesearch) {
								return $query->where(function($q) use ($pagesearch) {
									$q->where('b.name', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.eoinumber', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.projecttitle', 'like', '%' . $pagesearch . '%');
								})->where('a.iscancelled', 0);
							})
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('a.categoryid','=',$categoryid);
							})
							->when($eoi_status==0 || $eoi_status==1 || $eoi_status==2 || $eoi_status==3,function($query) use ($eoi_status){
								return $query->where('a.eoistatus','=',$eoi_status);
							})
							->when($eoi_status == 34, function ($query) {
								return $query->where('a.eoistatus', 4)
											 ->where('a.isordered', 0);
							})
							->when($eoi_status == 43, function ($query) {
								return $query->where('a.eoistatus', 3)
											 ->where('a.deadlinedate', '<', now())
											 ->whereNotExists(function ($subquery) {
												 $subquery->select(DB::raw(1))
														  ->from('eoi_request_participation as p')
														  ->whereRaw('p.requestid = a.requestid');
											 });
							})		
							->when($eoi_status == 4, function ($query) {
								return $query->where('a.eoistatus', 4)
											 ->where('a.isordered', '=',1);
							})							
							->when($eoi_status == 5, function ($query) {
								return $query->where('a.eoistatus', 4)
											 ->where('a.isordered', '=',0)
											 ->whereExists(function ($subquery) {
												 $subquery->select(DB::raw(1))
														  ->from('eoi_request_participation as p')
														  ->whereColumn('p.requestid', 'a.requestid')
														  ->where('p.interviewlink','!=','');
											 });
							})							
							->when($eoi_status==6, function ($query)
							{
								return $query->where('a.eoistatus',3)
											 ->where('a.isinprebid',1)
											 ->whereDate('a.deadlinedate','>=',date('Y\-m\-d'));
							})
							->when($departmentId!=0,function($query) use ($departmentId,$userId){
								return $query->where('a.userid','=',$userId);
							})
							->when($deptid!=0,function($query) use ($deptid){
								return $query->where('a.userid','=',$deptid);
							})
							->when($managerid!=0,function($query) use ($managerid){
								return $query->where('a.userid','=',$managerid);
							})
							->when($projectid!=0,function($query) use ($projectid){
								return $query->where('a.projectid','=',$projectid);
							})
							->where('a.iscancelled',$is_cancelled)
							->where('a.isClosed',$is_closed)
							->orderBy('a.creationdate','DESC')
							->paginate($pagesize,['*'],'page',$currentPage);
	
	
		foreach($data as $da)
		{
			$da->totalamount	=	$this->formatIndianCurrency($da->totalamount);
			if($olddata==1)
			{
				$da->creationdate	=	'';
				$da->senton			=	'';
				$da->confirmedon	=	'';
				$da->floatdate		=	'';				
			}
		}
		
		if($record_type)
		{
			$record_type	=	Crypt::decrypt($record_type);
			if($record_type=='102')
			{
				$data->getCollection()->transform(function($item) {
					return $item->eoistatus >= 2 ? $item : null;
				});

				$data->setCollection($data->getCollection()->filter());				
			}
			if($record_type=='103')
			{
				$data->getCollection()->transform(function($item) {
					return $item->eoistatus >= 3 ? $item : null;
				});

				$data->setCollection($data->getCollection()->filter());				
			}
			if($record_type=='104')
			{
				$data->setCollection(
					$data->getCollection()->filter(function($item) {
						return $item->eoistatus <= 4 && $item->isordered == 0;
					})
				);				
			}
			if($record_type=='105')
			{
				$data->setCollection(
					$data->getCollection()->filter(function($item) {
						if ($item->isordered != 0) {
							return false;
						}
						if (is_null($item->interviewdate) || $item->interviewdate == '0000-00-00') {
							return false;
						}

						$interviewDate = Carbon::parse($item->interviewdate);
						$today = Carbon::today();

						return $interviewDate->gte($today);
					})
				);
			}			
		}
		return view('admin/ajaxpages/eoirequestTable',['data' => $data]);

    }

    public function viewEoiRequest(Request $request)
	{
		$requestid 	=	$request->input('requestid');
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory','e.tiername')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->leftJoin('tiermaster_tbl as e','e.tierid','=','a.tierid')
							->where('requestid','=',$requestid)
							->first();
		
		$detail		=	DB::table('eoi_request_detail as a')
							->select('a.recordid','a.qualification','a.requirednumber','a.duration','a.worklocation','a.startdate','a.enddate','a.urgency','a.budgetamount','a.total','a.role','a.engagement','a.experience','a.employmenttype','a.remark','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition')
							->leftJoin('tiermaster_tbl as c','c.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
							->leftJoin('work_experience as f','f.experienceid','=','e.experienceid')
							->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as h','h.positionid','=','e.positionid')
							->where('a.requestid','=',$requestid)
							->get();

		
		return view('admin/ajaxpages/vieweoirequestTable',['data'=>$data,'detail'=>$detail]);

    }
    public function prepareDraft(Request $request,$recordid)
	{
		$userId    	= 	$request->session()->get('userId');
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
						->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory','b.ispm','b.isdepartment')
						->leftJoin('users_tbl as b','b.userid','=','a.userid')
						->leftJoin('department_tbl as c','c.userid','=','a.userid')
						->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
						->where('requestid','=',$requestid)
						->first();
		
		if($data->tier_choice==3)
		{
			$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
		}
		else
		{
			$tiers	=	DB::table('tiermaster_tbl')->where('tierid',$data->tier_choice)->orderBy('tierid')->get();
		}
		$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
		if($data->categoryid==2)
		{
			$html = [];
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
				$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
				
				$html[] = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail,'tierid'=>$tier->tierid])->render();
			}
		}
		if($data->categoryid==1)
		{
			$html = [];
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getAwdPrice($requestid,$data->categoryid,$tier->tierid,$rateid);

				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
					$budget			=	$budget+(($budget*$pricing->tax)/100);
					$rec->budget	=	$budget*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				
				$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
				$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
				
				$html[] = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail,'tierid'=>$tier->tierid])->render();
			}
			
		}
		$old_letter	=	DB::table('eoi_files_update')->where('requestid',$requestid)->get();
		
		$attachments=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
		
		$committee=	DB::table('eoi_request_committee as a')
						->select('b.*')
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();
	
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$updates	=	DB::table('log_eoi_updates')
							->where('requestid',$requestid)
							->orderby('updatedon','desc')
							->get();
		
		$whois	=	DB::table('users_tbl')->where('userid',$data->userid)->first();
		if($whois->isdepartment)
		{
			$updatedBy	=	"Updated by the Department";
		}
		if($whois->ispm)
		{
			$updatedBy	=	"Updated by the Project Manager";
		}
		
		return view('admin/master/eoirequest_draft',compact('data','token','attachments','old_letter','html','committee','updates','updatedBy'));
		
    }

    public function saveDraft(Request $request)
	{

		$rules = [
			'requestid'        		=> 	'required',
			'termsandcondition' 	=> 	'nullable|not_regex:/<script\b[^>]*>(.*?)<\/script>/is',
			'evaluation_index' 		=> 	'nullable',
			'chips_objective' 		=> 	'required',
			'projectobjective' 		=> 	'required',
			'scopeofwork' 			=> 	'required',
			'evaluationprocess' 	=> 	'nullable|not_regex:/<script\b[^>]*>(.*?)<\/script>/is',
			'criticalinformation' 	=> 	'nullable|not_regex:/<script\b[^>]*>(.*?)<\/script>/is',
			'documentrequired' 		=> 	'nullable|not_regex:/<script\b[^>]*>(.*?)<\/script>/is',
			'page_indexing' 		=> 	'nullable',
			'issuername'       		=> 	'nullable',
			'engagementname'		=>	'nullable',
			'releasedate'			=>	'nullable',
			'deadlinedate'			=>	'nullable',
			'prebidlastdate'		=>	'nullable',
			'interviewdate'			=>	'nullable',
			'communicationaddress'	=>	'nullable',
			'project_name'			=>	'nullable',
		];
        $messages = [
            'requestid.required'			=> 'Invalid detail provided',
			'termsandcondition.required'	=> 'Terms and conditions is mandatory',
			'termsandcondition.not_regex'	=> 'Terms and Conditions contains invalid content.',
			'evaluationprocess.required'	=> 'Evaluation process is required',
			'evaluationprocess.not_regex'	=> 'Evaluation process contains invalid content.',
			'criticalinformation.required'	=> 'Critical information is required',
			'criticalinformation.not_regex'	=> 'Critical information contains invalid content.',
			'documentrequired.required'		=> 'Document is required',
			'documentrequired.not_regex'	=> 'Document required contains invalid content.',
			'page_indexing.required'		=> 'Index of pages is required',
			'issuername.required'			=> 'Issuer name is required',
			'engagementname.required'		=> 'Engagement name is required',
			'releasedate.required'			=> 'Release date is required',
			'deadlinedate.required'			=> 'Deadline date is required',
			'prebidlastdate.required'		=> 'Pre-bid last date is required',
			'communicationaddress.required'	=> 'Communication address is required',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		

		$fieldsToCheck = [
			'releasedate',
			'deadlinedate',
			'prebidlastdate',
			'interviewdate',
		];

		$validator = Validator::make($validatedData, []);

		$service = $this->dateService;

		$validator->after(function ($validator) use ($validatedData, $fieldsToCheck, $service) {

			foreach ($fieldsToCheck as $field) {

				if (!empty($validatedData[$field])) {

					$result = $service->isDateAvailable($validatedData[$field]);

					if ($result[0] === false) {
						$validator->errors()->add($field, $result[1]);
					}
				}
			}
		});
		$validator->validate();


		
		$eoi	=	DB::table('eoi_request')->where('requestid', '=', Crypt::decrypt($validatedData['requestid']))->value('eoistatus');
		if($eoi>=2)
		{
			return response()->json(['status'=>300,'message'=>'This EoI detail has already been approved by the department. You cannot make any changes.'],200);
		}
		
		$oldData = DB::table('eoi_request')
					->select('issuername','engagementname','releasedate','deadlinedate','prebidlastdate','communicationaddress','termsandcondition')
					->where('requestid', '=', Crypt::decrypt($validatedData['requestid']))
					->first();

		$oldData->releasedate	=	date('d-m-Y',strtotime($oldData->releasedate));
		$oldData->prebidlastdate=	date('d-m-Y',strtotime($oldData->prebidlastdate));
		$oldData->deadlinedate	=	date('d-m-Y H:i:s',strtotime($oldData->deadlinedate));

		try
		{
			$updateData = [
				'eoinumber'            => $validatedData['project_name'] ?? null,
				'issuername'            => $validatedData['issuername'] ?? null,
				'engagementname'        => $validatedData['engagementname'] ?? null,
				'releasedate'           => isset($validatedData['releasedate'])
											? date('Y-m-d', strtotime($validatedData['releasedate']))
											: null,
				'prebidlastdate'        => isset($validatedData['prebidlastdate'])
											? date('Y-m-d', strtotime($validatedData['prebidlastdate']))
											: null,
				'communicationaddress'  => $validatedData['communicationaddress'] ?? null,
				'termsandcondition'     => $validatedData['termsandcondition'] ?? null,
				'evaluationprocess'     => $validatedData['evaluationprocess'] ?? null,
				'criticalinformation'   => $validatedData['criticalinformation'] ?? null,
				'documentrequired'      => $validatedData['documentrequired'] ?? null,
				'page_indexing'         => $validatedData['page_indexing'] ?? null,
				'evaluation_index'     	=> $validatedData['evaluation_index'] ?? null,
				'chips_objective'     	=> $validatedData['chips_objective'] ?? null,
				'projectobjective'     	=> $validatedData['projectobjective'] ?? null,
				'scopeofwork'     		=> $validatedData['scopeofwork'] ?? null,
			];

			if(!empty($validatedData['deadlinedate']))
			{
				$deadlinedate = strtoupper(trim($validatedData['deadlinedate']));

				if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $deadlinedate)) {
					$deadlinedate = Carbon::createFromFormat('d-m-Y', $deadlinedate)->startOfDay();
				} else {
					$deadlinedate = Carbon::createFromFormat('d-m-Y g:i A', $deadlinedate);
				}

				$updateData['deadlinedate'] = $deadlinedate->format('Y-m-d H:i:s');
			}

			if(!empty($validatedData['interviewdate']))
			{
				$interviewdate = strtoupper(trim($validatedData['interviewdate']));

				if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $interviewdate)) {
					$interviewdate = Carbon::createFromFormat('d-m-Y', $interviewdate)->startOfDay();
				} else {
					$interviewdate = Carbon::createFromFormat('d-m-Y g:i A', $interviewdate);
				}

				$updateData['interviewdate'] = $interviewdate->format('Y-m-d H:i:s');
			}

			DB::table('eoi_request')
				->where('requestid', Crypt::decrypt($validatedData['requestid']))
				->update($updateData);			
				
			$this->logService->logChanges(Crypt::decrypt($validatedData['requestid']), (array)$oldData, $validatedData);
			
			return response()->json(['status'=>200,'message'=>'The record has been updated successfully.']);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}
    }

    public function updateRemarkEoi(Request $request)
	{

		$rules = [
			'requestid'        		=> 	'required',
			'replywithremark' 	=> 	'required',
		];
        $messages = [
            'requestid.required'			=> 'Invalid detail provided',
			'replywithremark.required'		=> 'Remark is required',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			DB::table('eoi_request')->where('requestid','=',Crypt::decrypt($validatedData['requestid']))->update([
				'replywithremark'	=>	$validatedData['replywithremark'],
				'isUpdateRequired'	=>	1
			]);
			
			return response()->json(['status'=>200,'message'=>'Update remark added successfully!']);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}
    }

	
    public function updateDraft(Request $request,$recordid)
	{

		$oldData = DB::table('eoi_request')
					->select('issuername','engagementname','releasedate','deadlinedate','interviewdate','prebidlastdate','communicationaddress','termsandcondition','eoinumber','isUpdateRequired','isgenerated','anyother')
					->where('requestid','=',Crypt::decrypt($recordid))
					->first();


		$rules = [
			'requestid' 	=> 	['required'],
			'project_name'	=>	['required'],
			'evaluation_index'	=>	['nullable'],
			'termsandcondition' => [
				'required',
				'regex:/^(?!.*<\s*script).*$/is'
			],
			'issuername' => [
				'required',
				'not_regex:/<[^>]*>/i',
				'not_regex:/(script|javascript:)/i',
				'regex:/^[A-Za-z0-9\s()]+$/'
			],
			'engagementname' => [
				'required',
				'not_regex:/<[^>]*>/i',
				'not_regex:/(script|javascript:)/i',
				'regex:/^[A-Za-z0-9\s\.&,\-\(\)\[\]]+$/'
			],
			'releasedate' => ['nullable', 'date_format:d-m-Y'],
			'prebidlastdate' => ['nullable', 'date_format:d-m-Y'],
			'deadlinedate' => ['nullable', 'date_format:d-m-Y g:i A'],
			'interviewdate' => ['nullable','date_format:d-m-Y g:i A'],
			'communicationaddress' => [
				'required',
				'not_regex:/<[^>]*>/i',
				'not_regex:/(script|javascript:)/i',
				'regex:/^[A-Za-z0-9\s\.,()\-:@]+$/'
			],
			'page_indexing' 		=> ['nullable', 'regex:/^(?!.*<\s*script).*$/is'],
			'chips_objective' 		=> ['nullable', 'regex:/^(?!.*<\s*script).*$/is'],
			'evaluationprocess' 	=> ['required', 'regex:/^(?!.*<\s*script).*$/is'],
			'criticalinformation' 	=> ['required', 'regex:/^(?!.*<\s*script).*$/is'],
			'documentrequired' 		=> ['required', 'regex:/^(?!.*<\s*script).*$/is'],
			'anyother' 				=> ['nullable', 'regex:/^(?!.*<\s*script).*$/is'],
			'interview_place'		=> ['nullable', 'max:100', 'regex:/^(?!.*<\s*script).*$/is'],
			'selection_method'		=> ['nullable', 'max:100', 'regex:/^(?!.*<\s*script).*$/is'],
		];


		if($oldData && $oldData->isgenerated == 0)
		{
			$rules['project_name'] = 'required|max:100';
		}

		$messages = [
			'requestid.required'             => 'Invalid details provided.',
			'termsandcondition.required'     => 'Terms and conditions are required.',
			'termsandcondition.regex'        => 'Invalid terms and conditions content.',
			'issuername.required'            => 'Issuer name is required.',
			'issuername.regex'               => 'Invalid issuer name.',
			'engagementname.required'        => 'Engagement name is required.',
			'engagementname.regex'           => 'Invalid engagement name.',
			'releasedate.required'           => 'Release date is required.',
			'releasedate.date_format'        => 'Invalid release date format.',
			'prebidlastdate.required'        => 'Pre-bid date is required.',
			'prebidlastdate.date_format'     => 'Invalid pre-bid last date format.',
			'deadlinedate.required'          => 'Deadline date is required.',
			'deadlinedate.date_format'       => 'Invalid deadline date format.',
			'interviewdate.date_format'      => 'Invalid presentation and interview date format.',
			'communicationaddress.required'  => 'Communication address is required.',
			'communicationaddress.regex'     => 'Invalid communication address.',
			'evaluationprocess.required'     => 'Evaluation process is required.',
			'evaluationprocess.regex'        => 'Invalid evaluation process content.',
			'page_indexing.required'         => 'Table of contents is required.',
			'page_indexing.regex'            => 'Invalid table of contents.',
			'chips_objective.regex'          => 'Invalid data.',
			'criticalinformation.required'   => 'Critical information is required.',
			'criticalinformation.regex'      => 'Invalid critical information content.',
			'documentrequired.required'      => 'Document is required.',
			'documentrequired.regex'         => 'Invalid document content.',
			'anyother.regex'                 => 'Invalid special conditions content.',
			'project_name.required'          => 'EOI number is required.',
			'project_name.max'               => 'Maximum 10 characters are allowed for the project short name.',
			'interview_place.regex'          => 'Invalid place for presentations and interviews.',
			'interview_place.max'            => 'Maximum 100 characters are allowed for interview place.',
			'selection_method.regex'         => 'Invalid selection method.',
			'selection_method.max'           => 'Maximum 100 characters are allowed for selection method.',
		];
		
		$validator = Validator::make($request->all(), $rules, $messages);

		$fieldsToCheck = [
			'releasedate',
			'deadlinedate',
			'prebidlastdate',
			'interviewdate',
		];

		$service = $this->dateService;

		$validator->after(function ($validator) use ($request, $fieldsToCheck, $service) {

			foreach ($fieldsToCheck as $field) {

				if (!empty($request->$field)) {

					$result = $service->isDateAvailable($request->$field);

					if ($result[0] === false) {
						$validator->errors()->add($field, $result[1]);
					}
				}
			}
		});

		$validatedData = $validator->validate();

		
		
		// Chronological validation
		if($request->releasedate)
		{
			$validator->after(function ($validator) use ($request) {
				$releasedate    = 	Carbon::createFromFormat('d-m-Y', $request->releasedate);
				$prebidlastdate =	Carbon::createFromFormat('d-m-Y', $request->prebidlastdate);
				$deadlinedate   =	Carbon::createFromFormat('d-m-Y h:i A', $request->deadlinedate);
				if($request->interviewdate!='')
				{
					$interviewdate  = Carbon::createFromFormat('d-m-Y h:i A', $request->interviewdate);
				}

				if ($releasedate->gt($prebidlastdate)) {
					$validator->errors()->add('prebidlastdate', 'Pre-bid date must be after or equal to release date.');
				}

				if ($prebidlastdate->gt($deadlinedate)) {
					$validator->errors()->add('deadlinedate', 'Deadline date must be after or equal to pre-bid date.');
				}
				if($request->interviewdate!='')
				{
					if ($deadlinedate->gt($interviewdate)) {
						$validator->errors()->add('interviewdate', 'Interview date must be after or equal to deadline date.');
					}
				}
			});


			if ($validator->fails()) {

				if ($request->expectsJson()) {
					return response()->json([
						'message' => 'Validation failed',
						'errors'  => $validator->errors()
					], 422);
				}

				return redirect()
					->back()
					->withErrors($validator)
					->withInput();
			}
		}
		$validatedData = $validator->validated();		

		if($validatedData['project_name']!='')
		{
			$exists	=	DB::table('eoi_request')
						->where('eoinumber',$validatedData['project_name'])
						->where('requestid','!=',Crypt::decrypt($recordid))
						->exists();
			if($exists)
			{
				return back()->with('duplicate','The EoI number already exists. Please verify the number and try again with a different EoI number.')->withInput();	
			}
		}
		
        //$validatedData 	= $request->validate($rules,$messages);

		
		if($oldData->isUpdateRequired>0)
		{
			return back()->withInput()->withErrors([
				'update' => 'Cannot proceed: Department has not updated the EoI yet.'
			]);		
		}
		
		$oldData->releasedate	=	date('d-m-Y',strtotime($oldData->releasedate));
		$oldData->prebidlastdate=	date('d-m-Y',strtotime($oldData->prebidlastdate));
		$oldData->deadlinedate	=	date('d-m-Y H:i:s',strtotime($oldData->deadlinedate));
		$oldData->interviewdate	=	date('d-m-Y H:i:s',strtotime($oldData->interviewdate));

		try
		{
			if(!empty($validatedData['releasedate']) && strtotime($validatedData['releasedate']))
			{
				$validatedData['releasedate'] = date('Y-m-d', strtotime($validatedData['releasedate']));
			}
			else
			{
				$validatedData['releasedate'] = null;
			}
			if(!empty($validatedData['prebidlastdate']) && strtotime($validatedData['prebidlastdate']))
			{
				$validatedData['prebidlastdate'] = date('Y-m-d', strtotime($validatedData['prebidlastdate']));
			}
			else
			{
				$validatedData['prebidlastdate'] = null;
			}

			if(!empty($validatedData['deadlinedate']) && strtotime($validatedData['deadlinedate']))
			{
				$validatedData['deadlinedate'] = date('Y-m-d H:i:s', strtotime($validatedData['deadlinedate']));
			}
			else
			{
				$validatedData['deadlinedate'] = null;
			}			
			if(!empty($validatedData['interviewdate']) && strtotime($validatedData['interviewdate']))
			{
				$validatedData['interviewdate'] = date('Y-m-d H:i:s', strtotime($validatedData['interviewdate']));
			}
			else
			{
				$validatedData['interviewdate'] = null;
			}			
			if($oldData->isgenerated==0)
			{
				$eoi		=	explode("/",$oldData->eoinumber);
				
				$eoinumber	=	$validatedData['project_name'];
								
				DB::table('eoi_request')->where('requestid','=',Crypt::decrypt($recordid))->update([
					'issuername'			=>	$validatedData['issuername'],
					'engagementname'		=>	$validatedData['engagementname'],
					'releasedate'			=>	$validatedData['releasedate'],
					'prebidlastdate'		=>	$validatedData['prebidlastdate'],
					'deadlinedate'			=>	$validatedData['deadlinedate'],
					'interviewdate'			=>	$validatedData['interviewdate'],
					'communicationaddress'	=>	$validatedData['communicationaddress'],
					'termsandcondition'		=>	$validatedData['termsandcondition'],
					'evaluationprocess'		=>	$validatedData['evaluationprocess'],
					'page_indexing'			=>	$validatedData['page_indexing'] ?? NULL,
					'chips_objective'		=>	$validatedData['chips_objective'] ?? NULL,
					'criticalinformation'	=>	$validatedData['criticalinformation'],
					'documentrequired'		=>	$validatedData['documentrequired'],
					'anyother'				=>	$validatedData['anyother'] ?? NULL,
					'eoistatus'				=>	1,
					'sentby'				=>	$request->session()->get('userId'),
					'senton'				=>	now(),
					'eoiexpirydate'			=>	now()->addDays(225),
					'eoinumber'				=>	$eoinumber,
					'isgenerated'			=>	1,
					'interview_place'		=>	$validatedData['interview_place'] ?? NULL,
					'selection_method'		=>	$validatedData['selection_method'] ?? NULL,
					'evaluation_index'		=>	$validatedData['evaluation_index'] ?? NULL,
					
				]);
			}
			else
			{
				DB::table('eoi_request')->where('requestid','=',Crypt::decrypt($recordid))->update([
					'issuername'			=>	$validatedData['issuername'],
					'engagementname'		=>	$validatedData['engagementname'],
					'releasedate'			=>	$validatedData['releasedate'],
					'prebidlastdate'		=>	$validatedData['prebidlastdate'],
					'deadlinedate'			=>	$validatedData['deadlinedate'],
					'interviewdate'			=>	$validatedData['interviewdate'],
					'communicationaddress'	=>	$validatedData['communicationaddress'],
					'termsandcondition'		=>	$validatedData['termsandcondition'],
					'evaluationprocess'		=>	$validatedData['evaluationprocess'],
					'page_indexing'			=>	$validatedData['page_indexing'] ?? NULL,
					'chips_objective'		=>	$validatedData['chips_objective'] ?? NULL,
					'criticalinformation'	=>	$validatedData['criticalinformation'],
					'documentrequired'		=>	$validatedData['documentrequired'],
					'anyother'				=>	$validatedData['anyother'] ?? NULL,
					'eoistatus'				=>	1,
					'sentby'				=>	$request->session()->get('userId'),
					'senton'				=>	now(),
					'eoiexpirydate'			=>	now()->addDays(225),
					'evaluation_index'		=>	$validatedData['evaluation_index'] ?? NULL,
				]);				
			}
			$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
			
			$this->logService->logChanges(Crypt::decrypt($validatedData['requestid']), (array)$oldData, $validatedData);
			
			return redirect('master/eoi/requestlist')->with([
				'success' => 'Draft detail submitted successfully!',
				'category' => $category,
			]);
		}
		catch(QueryException $e)
		{
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate','Please check the data, there should not be any scripting tags.')->withInput();	
		}
    }

    public function prepareToFloat(Request $request,$recordid)
	{
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=71 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$data	=	DB::table('eoi_request as a')
						->select('a.requestid','a.eoinumber','a.engagementname','a.projecttitle','a.projectduration','a.projectobjective','a.page_indexing','a.chips_objective','a.criticalinformation','a.releasedate','a.prebidlastdate','a.deadlinedate','a.interviewdate','a.interview_place','a.selection_method','a.creationdate','a.termsandcondition','b.departmentname','a.admincharge','a.tax','a.tier_choice','a.categoryid','a.isupdated')
						->leftjoin('department_tbl as b','b.userid','=','a.userid')
						->where('a.requestid',Crypt::decrypt($recordid))
						->where('a.approvalClosed',1)
						->first();
		
		if(!$data)
		{
			return redirect('dashboard')->with('success','Invalid Request.');
		}
		
		$admincharge	=	$data->admincharge;
		$tax			=	$data->tax;
		$grandtotal		=	0;
		
		
		$tier_choice	=	$data->tier_choice;
		
		if($tier_choice==1)
		{
			$tier	=	DB::table('tiermaster_tbl')->where('tierid',1)->orderby('tiername')->get();
		}
		else if($tier_choice>=2)
		{
			if($data->categoryid==2)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
				$detail		=	$this->priceService->getCsfPrice(Crypt::decrypt($recordid),$data->categoryid,2);
				
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
			}
			if($data->categoryid==1)
			{
				$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
				$detail	=	DB::table('eoi_request_detail as a')
							->select(
								'a.recordid','a.qualification','a.duration','e.remuneration as budget','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype'
							)
							->leftJoin('remuneration_tbl as e', function ($join) use ($data) {
								$join->on('e.experiencelevel', '=', 'a.experiencelevel')
									 ->where('e.categoryid', '=', $data->categoryid)
									 ->where('e.tierid', '=', 2);
							})
							->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
							->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
							->where('a.requestid', '=', $data->requestid)
							->get();
							
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
					$rec->budget	=	($budget+(($budget*$pricing->tax)/100))*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;				
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
			}
			if($grandtotal>50000000)
			{
				$tier	=	DB::table('tiermaster_tbl')->where('tierid',1)->orderBy('tiername')->get();
			}
			else
			{
				$tier	=	DB::table('tiermaster_tbl')->whereIn('tierid',[1,2])->orderBy('tiername')->get();
			}
		}
		Session::put('requestid',$recordid);
        return view('admin/master/prepare_float',compact('tier','data','grandtotal'));
    }
	
    public function floatEoi(Request $request)
	{
		$request->merge([
			'releasedate'     => Carbon::parse($request->releasedate)->format('Y-m-d'),
			'prebidlastdate'  => Carbon::parse($request->prebidlastdate)->format('Y-m-d'),
			'deadlinedate'    => Carbon::parse($request->deadlinedate)->format('Y-m-d H:i:s'),
		]);
		
		if($request->interviewdate)
		{
			$request->merge([
				'interviewdate'   => Carbon::parse($request->interviewdate)->format('Y-m-d H:i:s'),
			]);			
		}

	
		$rules = [
			'releasedate' 		=> 	'required|date|after_or_equal:today',
			'prebidlastdate'	=> 	'required|date|after_or_equal:today',
			'deadlinedate' 		=> 	'required|date|after_or_equal:today',
			'interviewdate' 	=>	'nullable|date|after_or_equal:today',
			'eoi_file' 			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'notesheet_file' 	=> 	'nullable|file|mimetypes:application/pdf|max:5120',
			'interview_place' 	=> 	"nullable|max:100|regex:/^[A-Za-z0-9\s\.\,\(\)\[\]\-'%&@*]+$/",
			'selection_method' 	=> 	"nullable|max:100|regex:/^[A-Za-z0-9\s\.\,\(\)\[\]\-'%&@*]+$/",
			'page_indexing' 	=> 	"required|not_regex:/<\s*script\b/i|not_regex:/on\w+\s*=/i",
			'chips_objective' 	=> 	"required|not_regex:/<\s*script\b/i|not_regex:/on\w+\s*=/i",
			'termsandcondition' => 	"required|not_regex:/<\s*script\b/i|not_regex:/on\w+\s*=/i",
			'criticalinformation'=>	"required|not_regex:/<\s*script\b/i|not_regex:/on\w+\s*=/i",
		];
		
        $messages = [
			'releasedate.required'			=> 	'Release date is required',
			'releasedate.date'				=> 	'Invalid release date',
			'releasedate.after_or_equal'	=> 	'Invalid release date',
			'prebidlastdate.required'		=> 	'Pre-bid last date is required',
			'prebidlastdate.date'			=> 	'Invalid pre bid last date',
			'prebidlastdate.after_or_equal'	=> 	'Invalid pre bid date',
			'deadlinedate.required'			=> 	'Submission date is required',
			'deadlinedate.date'				=> 	'Invalid submission date',
			'deadlinedate.after_or_equal'	=> 	'Invalid submission date',
			'interviewdate.required'		=> 	'Interview date is required',
			'interviewdate.date'			=> 	'Invalid interview date',
			'interviewdate.after_or_equal'	=> 	'Invalid interview date',

			'eoi_file.required' 			=> 	'EoI file is required',
			'eoi_file.file' 				=> 	'Invalid EoI file',
			'eoi_file.mimetypes' 			=> 	'Invalid EoI file',
			'eoi_file.max' 					=> 	'Invalid EoI file',

			'notesheet_file.file' 			=> 	'Invalid Notesheet file',
			'notesheet_file.mimetypes' 		=> 	'Invalid Notesheet file',
			'notesheet_file.max' 			=> 	'Invalid Notesheet file',

			'interview_place.max' 			=> 	'Interview place must not exceed 100 characters.',			
			'interview_place.regex' 		=> 	'Interview place may only contain letters, numbers, spaces, and . , ( ) [ ] - \' characters.',			
			'selection_method.max' 			=> 	'Selection method must not exceed 100 characters.',			
			'selection_method.regex' 		=> 	'Selection method may only contain letters, numbers, spaces, and . , ( ) [ ] - \' characters.',

			'page_indexing.required' 		=> 	'Page indexing field is required.',
			'page_indexing.not_regex' 		=> 	'Page indexing must not contain script tags or inline event handlers.',

			'chips_objective.required' 		=> 	'Chips objective field is required.',
			'chips_objective.not_regex' 	=> 	'Chips objective must not contain script tags or inline event handlers.',

			'termsandcondition.required' 	=> 	'Terms and condition field is required.',
			'termsandcondition.not_regex' 	=> 	'Terms and condition must not contain script tags or inline event handlers.',

			'criticalinformation.required' 	=> 	'Critical information field is required.',
			'criticalinformation.not_regex' => 	'Critical information must not contain script tags or inline event handlers.',			
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$release      = Carbon::parse($validatedData['releasedate']);
		$prebid       = Carbon::parse($validatedData['prebidlastdate']);
		$deadline     = Carbon::parse($validatedData['deadlinedate']);
		$interview    = Carbon::parse($validatedData['interviewdate']);

		$errors = [];

		if (!$prebid->gt($release)) {
			$errors['prebidlastdate'] = 'Pre-bid date must be strictly after the Release date.';
		}

		if (!$deadline->gt($prebid)) {
			$errors['deadlinedate'] = 'Submission deadline must be strictly after the Pre-bid date.';
		}
		
		if($request->interviewdate)
		{
			if (!$interview->gt($deadline)) {
				$errors['interviewdate'] = 'Interview date must be strictly after the Submission deadline.';
			}
		}
		
		if (!empty($errors)) {
			return back()->withErrors($errors)->withInput();
		}
		
		try
		{
			$eoi_file = null;
			$notesheet_file = null;

			if($request->hasFile('eoi_file'))
			{
				$file = $request->file('eoi_file');
				$eoi_file = $file->store('uploads/floated_eois','public');
			}			
			if($request->hasFile('notesheet_file'))
			{
				$file = $request->file('notesheet_file');
				$notesheet_file = $file->store('uploads/notesheet_files','public');
			}			
			DB::beginTransaction();
			
			$eoi	=	DB::table('eoi_request')->where('requestid',Crypt::decrypt(Session::get('requestid')))->first();
			
			if($eoi_file!='' && $eoi_file!=NULL)
			{
				DB::table('eoi_request')
				->where('requestid',$eoi->requestid)
				->update([
					'eoi_file'			=>	$eoi_file,
				]);
			}

			if($notesheet_file!='' && $notesheet_file!=NULL)
			{
				DB::table('eoi_request')
				->where('requestid',$eoi->requestid)
				->update([
					'notesheet_file'	=>	$notesheet_file,
				]);
			}
			
			if($eoi->eoistatus!=2)
			{
				return redirect('master/eoi/requestlist')->with(['fail' => 'Invalid Attempt!']);
			}
			if($eoi->isupdated!=0)
			{
				return redirect('master/eoi/requestlist')->with(['fail' => 'Invalid Attempt!']);
			}
			if($eoi->isinprebid==1)
			{
				return redirect('master/eoi/requestlist')->with(['fail' => 'Invalid Attempt!']);
			}


			$vids = $eoi && $eoi->vendorids ? explode(',', $eoi->vendorids) : [];
			
			$vendors	=	DB::table('vendor_tbl')
							->where('approvalstatus', 1)
							->when(!empty($vids), function ($q) use ($vids) {
								return $q->whereIn('vendorid', $vids);
							})
							->get();			

			if(!$vendors)
			{
				return back()->with('duplicate','At least one vendor name must be selected.')->withInput();	
			}

			$validatedData['chips_objective'] = str_replace(
				'dd-mm-YYYY',
				date('d-m-Y', strtotime($validatedData['releasedate'])),
				$validatedData['chips_objective']
			);

			
			$validatedData['termsandcondition'] = str_replace(
				'Please provide project name',
				$eoi->engagementname,
				$validatedData['termsandcondition']
			);			
			
			$validatedData['criticalinformation'] = str_replace(
				'dd/mm/YYYY',
				date('d-m-Y, h:i A', strtotime($validatedData['deadlinedate'])),
				$validatedData['criticalinformation']
			);
			
			DB::table('eoi_request')
				->where('requestid','=',Crypt::decrypt(Session::get('requestid')))
				->where('eoistatus','=',2)
				->update([
					'eoistatus'			=>	3,
					'floatedby'			=>	$request->session()->get('userId'),
					'eoifloatdate'		=>	now(),
					'floatdate'			=>	now()->toDateString(),
					'releasedate'		=>	$validatedData['releasedate'],
					'prebidlastdate'	=>	$validatedData['prebidlastdate'],
					'deadlinedate'		=>	$validatedData['deadlinedate'],
					'interviewdate'		=>	$validatedData['interviewdate'],
					'interview_place'	=>	$validatedData['interview_place'] ?? NULL,
					'selection_method'	=>	$validatedData['selection_method'] ?? NULL,
					'chips_objective'	=>	$validatedData['chips_objective'] ?? NULL,
					'termsandcondition'	=>	$validatedData['termsandcondition'] ?? NULL,
					'criticalinformation'=>	$validatedData['criticalinformation'] ?? NULL,
				]);

			foreach($vendors as $vendor)
			{
				
				DB::table('eoi_request_floated')->insert([
					'requestid'		=>	Crypt::decrypt(Session::get('requestid')),
					'userid'		=>	$vendor->userid,
					'vendorid'		=>	$vendor->vendorid,
					'floateddate'	=>	now(),
					'floatedby'		=>	Session::get('userId'),
					'floatdate'		=>	now()->toDateString(),
					'expirydate'	=>	$validatedData['deadlinedate'],
				]);
				
				
			}
			$userName		= $request->session()->get('userName');
			if($eoi->releasedate!=$validatedData['releasedate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	Crypt::decrypt(Session::get('requestid')),
					'field_name'		=>	'releasedate',
					'old_value'			=>	$eoi->releasedate,
					'new_value'			=>	$validatedData['releasedate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	Session::get('userId'),
					'marked'			=>	1,
				]);
			}
			if($eoi->prebidlastdate!=$validatedData['prebidlastdate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	Crypt::decrypt(Session::get('requestid')),
					'field_name'		=>	'prebidlastdate',
					'old_value'			=>	$eoi->prebidlastdate,
					'new_value'			=>	$validatedData['prebidlastdate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	Session::get('userId'),
					'marked'			=>	1,
				]);
			}
			if($eoi->deadlinedate!=$validatedData['deadlinedate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	Crypt::decrypt(Session::get('requestid')),
					'field_name'		=>	'deadlinedate',
					'old_value'			=>	$eoi->deadlinedate,
					'new_value'			=>	$validatedData['deadlinedate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	Session::get('userId'),
					'marked'			=>	1,
				]);
			}
			if($eoi->interviewdate!=$validatedData['interviewdate'])
			{
				DB::table('log_eoi_request_updated')->insert([
					'requestid'			=>	Crypt::decrypt(Session::get('requestid')),
					'field_name'		=>	'interviewdate',
					'old_value'			=>	$eoi->interviewdate,
					'new_value'			=>	$validatedData['interviewdate'],
					'updated_at'		=>	now(),
					'updated_by_name'	=>	$userName,
					'updated_by'		=>	Session::get('userId'),
					'marked'			=>	1,
				]);
			}
			
			$eoi	=	DB::table('eoi_request')->where('requestid',Crypt::decrypt(Session::get('requestid')))->first();
			
			$officialemail	=	"";
			
			$usrType	=	DB::table('users_tbl as a')->where('userid',$eoi->userid)->first();
			if($usrType->isdepartment==1)
			{
				$officialemail	=	DB::table('department_tbl')->where('userid',$usrType->userid)->value('officialemail');
			}
			if($usrType->ispm==1)
			{
				$officialemail	=	DB::table('department_tbl')->where('userid',$usrType->userid)->value('officialemail');
			}

			if($this->featureService->isEmailEnabled())
			{
				if($request->input('emailids')!='')
				{
				
					$recordids	=	explode(",",$request->input('emailids'));
					
					
					$emails = DB::table('vendors_email')
							->select('email')->whereIn('recordid', $recordids)
							->pluck('email')
							->map(fn($email) => trim($email))
							->filter(fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))
							->toArray();
					
					if($officialemail=='')
					{
						Mail::to($emails)->send(new PublishMail($eoi));
					}
					else
					{
						Mail::to($emails)->cc($officialemail)->send(new PublishMail($eoi));
					}
					
				}
			}
			DB::commit();

			$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();

			return redirect('master/eoi/requestlist')->with([
				'success' => 'EoI floated successfully!',
				'category' => $category,
			]);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			if($eoi_file!='')
			Storage::disk('public')->delete($eoi_file);

			if($notesheet_file!='')
			Storage::disk('public')->delete($notesheet_file);
			
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
    }

    public function prepareCommittee(Request $request,$recordid)
	{
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','eoirequestlist');
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();
		
		$committee	=	DB::table('eoi_request_committee as a')
							->select('a.memberid','a.requestid','a.committeeid','a.createdby','a.creationdate','b.department','b.usertype','b.name','b.mobilenumber','b.designation','b.remark','b.email')
							->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
							->where('a.requestid',$requestid)
							->get();
		
		$members	=	DB::table('committee_member')
							->where('usertype','CHIPS')
							->whereNotIn('memberid', function($query) use ($requestid) {
								$query->select('committeeid')
									  ->from('eoi_request_committee')
									  ->where('requestid', $requestid);
							})
							->orderby('name')
							->get();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/prepare_committee',compact('data','token','committee','members'));

    }

	public function uploadCommittee(Request $request, $encryptedId)
	{
		$rules = [
			'memberid'	=>	'required',
		];

		$messages = [
			'memberid.required'	=> 'Name is required.',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'errors' => $validator->errors()
			], 422);
		}

		$memberid	=	$request->input('memberid');
		$requestid	=	Crypt::decrypt($encryptedId);

		try
		{
			DB::beginTransaction();

			DB::table('eoi_request_committee')->insert([
				'requestid'   	=> $requestid,
				'committeeid'  	=> $memberid,
				'createdby'  	=> Session('userId'),
				'creationdate'  => now()
			]);

			DB::table('eoi_request')->where('requestid',$requestid)->where('iscommittee',0)->update([
				'iscommittee'	=>	1
			]);
//			DB::commit();
//			return response()->json(['message' => 'Committee record added successfully.','status'=>200]);

			$committee	=	DB::table('eoi_request_committee as a')
								->select('a.memberid','a.requestid','a.committeeid','a.createdby','a.creationdate','b.usertype','b.name','b.mobilenumber','b.designation','b.remark')
								->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
								->where('a.requestid',$requestid)
								->get();

			$html = view('admin.ajaxpages.eoicommitteeTable',['data'=>$committee])->render();
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Committee record added successfully.','formhtml' => $html]);

			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json([
				'errors' => [
					'table_number' => ['<b style="color:red;">Duplicate data found for same EoI.</b>'],
					]
				], 422);			
		}
	}	

    public function viewPptResumes(Request $request,$recordid)
	{
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','eoirequestlist');
		
		$requestid 	=	Crypt::decrypt($recordid);

        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory',DB::raw('CASE WHEN a.deadlinedate < NOW() THEN 1 ELSE 0 END as isDeadlinePassed'))
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();

		$today 		= 	Carbon::today()->toDateString();

		$vendors	=	DB::table('eoi_request_participation as a')
						->select(
							'a.participationid',
							'a.participationdate',
							'b.vendorid',
							'b.companyname',
							'b.contactperson',
							'b.designation',
							'b.officialemail',
							'b.officelocation',
							'c.tiername',
							'a.reviewstatus',
							'a.interviewdate',
							'a.isenabled',
							'a.interviewlink',
							'a.contact_person',
							'a.contact_number',
							'a.contact_email',
							'a.contact_person_2',
							'a.contact_number_2',
							'a.contact_email_2',
							'd.uploaded_at as participation_date_time',
							DB::raw("
								CASE 
								WHEN CAST(a.interviewdate AS CHAR) = '' 
									 OR a.interviewdate IS NULL 
									 OR a.interviewdate = '0000-00-00' THEN 0
								WHEN a.interviewdate > '$today' THEN 0
								ELSE 1
								END as ispassed
							")
						)
						->join('eoi_request_floated as d','d.floatid','=','a.floatid')
						->leftJoin('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
						->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'b.tierid')
						->where('a.requestid', $requestid)
						->whereNotNull('d.signedcopyofeoi')
						->get();
						
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token',$token);
		
		$interviews	=	DB::table('eoi_interview_date as a')
						->select('a.requested_on','a.updated_on','a.isUpdated','a.updatedValue','a.remark','c.name as requestedby','d.name as updatedby','b.eoinumber')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.requested_by')
						->leftjoin('users_tbl as d','d.userid','=','a.updated_by')
						->where('a.requestid',$requestid)
						->orderBy('a.recordid')
						->get();
		
		$members	=	DB::table('eoi_request_committee as a')
						->select('b.memberid','b.name','b.mobilenumber','b.email','b.designation','b.department','b.usertype')
						->join('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();
		
		return view('admin/master/uploaded_pptresumes',compact('data','token','vendors','interviews','members'));
    }

    public function viewParticipations(Request $request,$recordid)
	{
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','eoirequestlist');
		
		$participationid	=	Crypt::decrypt($recordid);
		$participation		=	DB::table('eoi_request_participation')->where('participationid',$participationid)->first();

		$floated			=	DB::table('eoi_request_floated')->where('floatid',$participation->floatid)->first();

		$vendor	=	DB::table('vendor_tbl as a')
							->select('a.*','b.name','b.mobilenumber','b.email')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->where('a.vendorid',$participation->vendorid)
							->first();
		
		$eoi		=	DB::table('eoi_request as a')
						->select('a.*','b.departmentname')
						->leftjoin('department_tbl as b','b.userid','a.userid')
						->where('a.requestid',$participation->requestid)
						->first();
		$categoryid	=	$eoi->categoryid;

		if($categoryid==1)
		{
			$requestdetail	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.tierid','a.role','a.qualification','a.duration','b.tiername','a.experiencelevel','d.workexperience','a.experience','a.remark','a.isAdditional')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->leftjoin('remuneration_tbl as c','c.remunerationid','a.remunerationid')
									->leftjoin('work_experience as d','d.experienceid','c.experienceid')
									->where('requestid',$participation->requestid)
									->orderby('a.recordid')
									->get();
			foreach($requestdetail as $req)
			{
				$req->resumes	=	DB::table('eoi_request_detail_resume as a')
										->select('a.resumeid','a.name','a.uploaddate','a.resume','a.reviewstatus','a.remark','a.isresumeviewed')
										->where('a.recordid',$req->recordid)
										->where('a.isordered',0)
										->where('a.vendorid',$participation->vendorid)
										->get();
			}
		}
		
		if($categoryid==2)
		{
			$requestdetail	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.tierid','a.experience','a.employmenttype','a.remark','a.duration','a.qualification','a.isAdditional','b.tiername','d.sectorname','e.consultantposition')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->leftjoin('remuneration_tbl as c','c.rateid','a.rateid')
									->leftjoin('sector_tbl as d','d.sectorid','a.sectorid')
									->leftjoin('position_tbl as e','e.positionid','a.positionid')
									->where('a.requestid',$participation->requestid)
									->distinct()
									->orderby('a.recordid')
									->get();
			foreach($requestdetail as $req)
			{
				$req->resumes	=	DB::table('eoi_request_detail_resume as a')
										->select('a.resumeid','a.name','a.uploaddate','a.resume','a.reviewstatus','a.remark','a.isresumeviewed')
										->where('a.recordid',$req->recordid)
										->where('a.isordered',0)
										->where('a.vendorid',$participation->vendorid)
										->get();
				
			}			
		}
		
		
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/vendor_resumesppt',compact('eoi','vendor','token','participation','categoryid','requestdetail','floated'));

    }

    public function updateResumeRemark(Request $request)
	{
		$rules = [
			'resumeid'      => 'required',
			'remark'        => 'required|max:200',
		];
        $messages = [
            'resumeid.required'		=> 'Invalid resume detail',
			'remark.required'		=> 'Remark is required',
			'remark.max'			=> 'Maximum 200 characters allowed',
        ];

        $validatedData 	= 	$request->validate($rules,$messages);
		$userId			= 	$request->session()->get('userId');
		
		DB::table('eoi_request_detail_resume')
		->where('resumeid',$validatedData['resumeid'])
		->update([
			'remark'		=>	$validatedData['remark'],
			'updated_at'	=>	now(),
			'updatedby'		=>	$userId
		]);			
		return response()->json(['message'=>'Remark updated successfully.','status'=>200]);

    }


    public function updateInterviewDate(Request $request)
	{
		$rules = [
			'requestid'		=>	'required',
			'interviewdate' =>	'required|date_format:d-m-Y g:i A',
		];
        $messages = [
            'requestid.required'		=>	'EoI detail is required',
			'interviewdate.required'	=>	'Interview date is required',
			'interviewdate.date_format'	=>	'Invalid interview date',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId			= $request->session()->get('userId');
		
		DB::table('eoi_request_participation')->where('requestid',Crypt::decrypt($validatedData['requestid']))->update([
			'interviewdate'		=>	date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate'])),
			'updated_at'		=>	now(),
			'updatedby'			=>	$userId,
			'interviewsetby'	=>	$userId,
			'interviewlink'		=>	NULL
		]);
		
		DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->update(['isInterviewSet'=>1]);
		
		return response()->json(['message' => 'Interview date updated successfully.','status'=>200]);
    }

    public function updateInterview(Request $request)
	{
		$rules = [
			'participationid'	=>	'required',
			'interviewlink'     =>	'nullable|regex:/^https?:\/\/[\w\-]+(\.[\w\-]+)+/',
			'interviewdate' 	=>	'required|date_format:d-m-Y g:i A',
		];
        $messages = [
            'participationid.required'	=>	'Invalid participation detail',
			'interviewlink.required'	=>	'Interview link is required',
			'interviewlink.regex'		=>	'Invalid interview link provided',
			'interviewdate.required'	=>	'Interview date is required',
			'interviewdate.date_format'	=>	'Invalid interview date',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

		if($validator->fails())
		{
			return response()->json([
				'status' => 422,
				'errors' => $validator->errors()
			], 422); // Important: set HTTP status to 422 for validation errors
		}

		$validatedData	=	$validator->validated();

		$userId	=	$request->session()->get('userId');

		try
		{
			DB::table('eoi_request_participation')
			->where('participationid',$validatedData['participationid'])
			->update([
				'updated_at'		=>	now(),
				'updatedby'			=>	$userId,
				'interviewsetby'	=>	$userId,
				'interviewlink'		=>	$validatedData['interviewlink'] ?? NULL,
				'interviewdate'		=>	date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate'])),
			]);
			
			$participation	=	DB::table('eoi_request_participation')
								->where('participationid',$validatedData['participationid'])
								->first();
			

			
			$eoi	=	DB::table('eoi_request')
						->select('requestid','eoinumber','releasedate','engagementname','userid','vendorids')
						->where('requestid',$participation->requestid)
						->first();

			$committeeEmails=	DB::table('eoi_request_committee as a')
									->select('email')
									->leftJoin('committee_member as b','b.memberid','=','a.committeeid')
									->where('a.requestid',$participation->requestid)
									->distinct()
									->pluck('b.email')
									->map(fn($email)=>trim($email))
									->filter(fn($email)=>filter_var($email, FILTER_VALIDATE_EMAIL))
									->toArray();

			$vendorEmails 	=	DB::table('vendors_email')
								->where('vendorid',$participation->vendorid)
								->pluck('email')
								->toArray();
			
			$emails			=	array_unique(array_merge($committeeEmails,$vendorEmails));
			
			$officialemail	=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('officialemail') ?? NULL;
			
			if(!empty($officialemail) && filter_var($officialemail, FILTER_VALIDATE_EMAIL))
			{
				$emails[] = trim($officialemail);
			}
			
			$emails = 	array_unique($emails);
			$vendor	=	DB::table('vendor_tbl')
						->select('vendorid','companyname','shortname')
						->where('vendorid',$participation->vendorid)
						->first();
			
			if($this->featureService->isEmailEnabled())
			{
				if(!empty($emails))
				{
					if(!empty($officialemail) && filter_var($officialemail, FILTER_VALIDATE_EMAIL))
					{
						Mail::to($emails)->cc($officialemail)->send(new InterviewLinkMail($eoi,$vendor,$participation));
					}
					else
					{
						Mail::to($emails)->send(new InterviewLinkMail($eoi,$vendor,$participation));
					}
				}						
			}
			return response()->json(['message' => 'Interview date updated successfully.','status'=>200]);
		}
		catch(\Illuminate\Database\QueryException $e)
		{
			Log::error('Error: '.$e->getMessage());
			
			return response()->json([
				'message' => 'Something went wrong.',
				'error'   => $e->getMessage(),
				'status'  => 500
			]);
		}
		

    }

    public function updateParticipationRemark(Request $request)
	{
		$rules = [
			'participationid'	=>	'required',
			'remark'     =>	'required',
		];
        $messages = [
            'participationid.required'	=>	'Invalid participation detail',
			'remark.required'			=>	'Remark is required',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId			= $request->session()->get('userId');
		
		DB::table('eoi_request_participation')->where('participationid',$validatedData['participationid'])->update([
			'remark'			=>	$validatedData['remark'],
			'remark_updated_at'	=>	now(),
			'remark_updated_by'	=>	$userId,
		]);
		
		return response()->json(['message' => 'Remark detail updated successfully.','status'=>200]);

    }

    public function createWorkOrder(Request $request)
	{
		$rules = [
			'requestid'     	=>	'required',
			'project_type'     	=>	'required|in:0,1',
			'vendorid'     		=>	'required',
			'loinumber'			=>	'required',
			'orderno'			=>	'required|regex:/^[A-Za-z0-9.,()]+$/|max:50',
			'ordernumber'		=>	'required',
			'refrence'			=>	'required',
			'subject'			=>	'required',
			'agreementdate'		=>	'required|date',
			'tendernumber'		=>	'required',
			'termsandcondition'	=>	'required',
			'signedby'			=>	'required|in:Chief Executive Officer (CEO),Jt. CEO (Project),Jt. CEO(Finance),Add. Chief Executive Officer,Chief Operating Officer',
			'momfile' 			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'markingsheet'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'notesheet'			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'orderdate' 		=> 	'required|date_format:d-m-Y|before_or_equal:today',
		];

		
        $messages = [
			'requestid.required'		=>	'Invalid eoi detail',
			'vendorid.required'			=>	'Invalid vendorid',
			'project_type.required'		=>	'Project type is required',
			'project_type.in'			=>	'Invalid project type value',
			'loinumber.required'		=>	'LOI number is required',
			'orderno.required'			=>	'Order prefix number is required',
			'orderno.regex'				=>	'Invalid order prefix number',
			'orderno.max'				=>	'Maximum 10 characters allowed',
			'ordernumber.required'		=>	'Order number is required',
			'refrence.required'			=>	'Refrence number is required',
			'subject.required'			=>	'Subject is required',
			'agreementdate.required'	=>	'Agreement date is required',
			'agreementdate.date'		=>	'Invalid agreement date',
			'tendernumber.required'		=>	'Tender number is required',
			'termsandcondition.required'=>	'Terms & conditions is required',
			'signedby.required'			=>	'Signed by name is required',
			'momfile.required'			=>	'MoM File is required',
			'momfile.file'				=>	'MoM must be a file',
			'momfile.mimetypes'			=>	'Invalid MoM file attached',
			'momfile.max'				=>	'Maximum file size is 2 MB',
			'markingsheet.required'		=>	'Marking sheet is required',
			'markingsheet.file'			=>	'Marking sheet must be a file',
			'markingsheet.mimetypes'	=>	'Invalid marking sheet file attached',
			'markingsheet.max'			=>	'Maximum file size is 2 MB',
			'notesheet.required'		=>	'Note sheet is required',
			'notesheet.file'			=>	'Note sheet must be a file',
			'notesheet.mimetypes'		=>	'Invalid note sheet file attached',
			'notesheet.max'				=>	'Maximum file size is 2 MB',
			'signedcopy.required'		=>	'Order signed copy is required',
			'signedcopy.file'			=>	'Order signed copy must be a file',
			'signedcopy.mimetypes'		=>	'Invalid Order signed copy file attached',
			'signedcopy.max'			=>	'Maximum file size is 2 MB',
			'orderdate.required'		=>	'Order date is required',
			'orderdate.date_format'		=>	'Invalid order date provided',
			'orderdate.before_or_equal'	=>	'Invalid order date provided',
        ];

        $validatedData 	=	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		$requestid		=	Crypt::decrypt($request->input('requestid'));
		$vendorid		=	Crypt::decrypt($request->input('vendorid'));
		
		$eoi			=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		
		if(!$eoi)
		{
			return response()->json([
				'errors' => [
					'eoi' => ['Invalid EoI detail provided.'],
					]
				], 422);			
		}
		if($eoi->categoryid==1)
		{
			$names      = $request->input('name',[]);
			$checkboxes = $request->input('records',[]);
			$expLevels 	= $request->input('exp_level',[]);

			if(empty($checkboxes))
			{
				return view('errors.workorder_error', [
					'message' => 'Please select at least one resource.',
					'requestid' => Crypt::encrypt($eoi->requestid)
				]);
			}

			foreach($checkboxes as $recordId)
			{
				$name = $names[$recordId] ?? '';

				if(trim($name)==='')
				{
					return view('errors.workorder_error', [
						'message' 	=> 	'Please enter a name for all selected resources.',
						'requestid' => 	Crypt::encrypt($eoi->requestid)
					]);
				}

				if(!preg_match('/^[a-zA-Z ]+(\.[a-zA-Z ]+)?$/',$name))
				{
					return view('errors.workorder_error', [
						'message' 	=> 	'Names can only contain letters and at most one dot.',
						'requestid' => 	Crypt::encrypt($eoi->requestid)
					]);
				}
			}
			
		}
		$momfile		= 	$request->file('momfile');		
		$markingsheet	= 	$request->file('markingsheet');
		$notesheet		= 	$request->file('notesheet');
		$signedcopy		= 	$request->file('signedcopy');

		try
		{
			
			DB::beginTransaction();
			if($eoi->categoryid==1)
			{
				foreach ($checkboxes as $recordId)
				{
					$tempName     = $names[$recordId] ?? null;
					$tempExpLevel = $expLevels[$recordId] ?? null;

					if($recordId)
					{
						DB::table('eoi_request_detail')
						->where('recordid',$recordId)
						->update([
							'temp_name'            =>	$tempName,
							'temp_experiencelevel' =>	$tempExpLevel,
						]);
					}
				}			
			}
			if($request->hasFile('momfile'))
			{
				$momfile= $request->file('momfile')->store('uploads/momfiles','public');
			}
			if($request->hasFile('markingsheet'))
			{
				$markingsheet= $request->file('markingsheet')->store('uploads/markingsheets','public');
			}
			if($request->hasFile('notesheet'))
			{
				$notesheet	= $request->file('notesheet')->store('uploads/notesheets','public');
			}
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	= $request->file('signedcopy')->store('uploads/signedorder','public');
			}
			
			$eoi	=	DB::table('eoi_request')->where('requestid',Session::get('eoi_requestid'))->first();

			$vendor	=	DB::table('vendor_tbl')->where('vendorid',Session::get('eoi_vendorid'))->first();
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$eoi->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
			
			$copyto	=	$vendor->companyname.", ".$vendor->officelocation;
		
			
			if($eoi->categoryid==2)
			{
				$records		=	DB::table('eoi_request_detail as a')
									->select(
										'a.recordid','a.qualification','a.duration','e.remunerationid','e.remuneration','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition','a.employmenttype')
									->leftJoin('remuneration_tbl as e', function($join){
										$join->on('e.sectorid', '=', 'a.sectorid');
										$join->on('e.positionid', '=', 'a.positionid');
									})
									->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
									->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
									->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
									->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
									->where('e.tierid', '=', $vendor->tierid)
									->where('a.requestid','=',Session::get('eoi_requestid'))
									->get();
			}
			if($eoi->categoryid==1)
			{
				$records		=	DB::table('eoi_request_detail as a')
									->select(
										'a.recordid','a.qualification','a.duration','e.remunerationid','e.remuneration','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype')
									->leftJoin('remuneration_tbl as e', function ($join) use ($eoi,$vendor) {
										$join->on('e.experiencelevel','=','a.experiencelevel')
											 ->where('e.categoryid','=',$eoi->categoryid)
											 ->where('e.tierid','=',$vendor->tierid);
									})
									->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
									->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
									->where('a.requestid', '=',Session::get('eoi_requestid'))
									->get();

				$orderRecords	=	DB::table('eoi_request_detail as a')
									->select(
										'a.recordid','a.qualification','a.duration','e.remunerationid','e.remuneration','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype','a.temp_name','a.temp_experiencelevel')
									->leftJoin('remuneration_tbl as e', function ($join) use ($eoi,$vendor) {
										$join->on('e.experiencelevel','=','a.temp_experiencelevel')
											 ->where('e.categoryid','=',$eoi->categoryid)
											 ->where('e.tierid','=',$vendor->tierid);
									})
									->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
									->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
									->where('a.requestid','=',Session::get('eoi_requestid'))
									->where('a.temp_experiencelevel','!=',0)
									->get();
				
			}

			$workOrderDuedate 	= 	Carbon::parse($validatedData['orderdate'])->addMonths($eoi->projectduration)->format('Y-m-d');
			$nextReminderDate 	= 	Carbon::parse($validatedData['orderdate'])->addMonths($eoi->projectduration-2)->format('Y-m-d');
			$department_id		=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('departmentid');
			
			$orderid=	DB::table('eoi_work_order')->insertGetId([
							'requestid'			=>	$eoi->requestid,
							'categoryid'		=>	$eoi->categoryid,
							'orderno'			=>	$validatedData['orderno'],
							'ordernumber'		=>	$validatedData['orderno']."".$validatedData['ordernumber'],
							'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['orderdate'])),
							'refrence'			=>	$validatedData['refrence'],
							'subject'			=>	$validatedData['subject'],
							'termsandcondition'	=>	$validatedData['termsandcondition'],
							'vendorid'			=>	$vendor->vendorid,	
							'creationdate'		=>	now(),
							'createdby'			=>	$userId,
							'operatingmargin'	=>	$pricing->operatingmargin,
							'tax'				=>	$pricing->tax,
							'admincharge'		=>	$pricing->admincharge,
							'momfile'			=>	$momfile,
							'markingsheet'		=>	$markingsheet,
							'notesheet'			=>	$notesheet,
							'signedcopy'		=>	$signedcopy,
							'signedby'			=>	$validatedData['signedby'],
							'loinumber'			=>	$validatedData['loinumber'],
							'copyto'			=>	$copyto,
							'workorderduedate'	=>	$workOrderDuedate,
							'nextreminderdate'	=>	$nextReminderDate,
							'isfinalized'		=>	1,
							'finalizedby'		=>	$userName,
							'project_type'		=>	$validatedData['project_type'],
							'userid'			=>	$eoi->userid,
							'project_duration'	=>	$eoi->projectduration,
							'project_name'		=>	$eoi->projecttitle,
							'department_id'		=>	$department_id,
						]);
			
			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			foreach($records as $record)
			{
				$r++;
				$budget				=	0;
				$remuneration		=	round($record->remuneration*$record->duration,2);
				$totalremuneration	=	$totalremuneration+$remuneration;
				$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
				$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
				$budget				=	$remuneration+$operatingvalue+$taxvalue;
				$totalbudget		=	$totalbudget+$budget;
				$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
				$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);

				DB::table('eoi_request_detail')
				->where('recordid',$record->recordid)
				->update([
					'remunerationid'	=>	$record->remunerationid,
					'remuneration'		=>	$record->remuneration,
					'budget'			=>	$budget,
					'operating'			=>	$pricing->operatingmargin,
					'operatingvalue'	=>	$operatingvalue,
					'tax'				=>	$pricing->tax,
					'taxvalue'			=>	$taxvalue,
					'admin'				=>	$pricing->admincharge,
					'admincharge'		=>	$admincharge,
					'grandtotal'		=>	$grandtotal,
					'tierid'			=>	$vendor->tierid
				]);
				
				if($eoi->categoryid==2)
				{
					$rates	=	DB::table('remuneration_tbl')->where('remunerationid',$record->remunerationid)->first();
					$data = [
						'orderid'            => $orderid,
						'recordid'           => $record->recordid,
						'deployment_status'  => 'Pending',
						'tierid'             => $vendor->tierid,
						'sectorid'           => $rates->sectorid,
						'positionid'         => $rates->positionid,
						'role'               => $record->role ?? null,
						'experience'         => $record->experience ?? null,
						'experiencelevel'    => $record->experiencelevel,
						'qualification'      => $record->qualification ?? null,
						'deploymenttype'     => $record->employmenttype ?? null,
						'duration'           => $record->duration,
						'remark'             => $record->remark ?? null,
						'remunerationid'     => $record->remunerationid,
						'remuneration'       => $record->remuneration,
						'baseprice'          => $record->remuneration,
						'operating'          => $pricing->operatingmargin,
						'operatingvalue'     => $operatingvalue,
						'tax'                => $pricing->tax,
						'taxvalue'           => $taxvalue,
						'admincharge'        => $pricing->admincharge,
						'adminvalue'         => $admincharge,
						'grandtotal'         => $grandtotal,
						'deployment_date'    => date('Y-m-d', strtotime($validatedData['orderdate'])),
					];

					DB::table('eoi_resource_deployment')->insert($data);				
				}
			}
			if($eoi->categoryid==1)
			{
				$r=0;
				$totalremuneration	=	0;
				$totalbudget		=	0;
				foreach($orderRecords as $record)
				{
					$r++;
					$budget				=	0;
					$remuneration		=	round($record->remuneration*$record->duration,2);
					$totalremuneration	=	$totalremuneration+$remuneration;
					$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
					$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
					$budget				=	$remuneration+$operatingvalue+$taxvalue;
					$totalbudget		=	$totalbudget+$budget;
					$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
					$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);
					$rates	=	DB::table('remuneration_tbl')->where('remunerationid',$record->remunerationid)->first();
					$data = [
						'orderid'           => $orderid,
						'recordid'          => $record->recordid,
						'deployment_status' => 'Pending',
						'tierid'            => $vendor->tierid,
						'sectorid'          => $rates->sectorid,
						'positionid'        => $rates->positionid,
						'role'              => $record->role ?? null,
						'experience'        => $record->experience ?? null,
						'experiencelevel'   => $record->experiencelevel,
						'qualification'     => $record->qualification ?? null,
						'deploymenttype'    => $record->employmenttype ?? null,
						'duration'          => $record->duration,
						'remark'            => $record->remark ?? null,
						'remunerationid'    => $record->remunerationid,
						'remuneration'      => $record->remuneration,
						'baseprice'         => $record->remuneration,
						'operating'         => $pricing->operatingmargin,
						'operatingvalue'    => $operatingvalue,
						'tax'               => $pricing->tax,
						'taxvalue'          => $taxvalue,
						'admincharge'       => $pricing->admincharge,
						'adminvalue'        => $admincharge,
						'grandtotal'        => $grandtotal,
						'deployment_date'   => date('Y-m-d', strtotime($validatedData['orderdate'])),
						'name'         		=> $record->temp_name,
					];
					DB::table('eoi_resource_deployment')->insert($data);				
				}
			}
			DB::table('eoi_request')
			->where('requestid',$eoi->requestid)
			->update([
				'isordered'			=>	1,
				'project_type'		=>	$validatedData['project_type'],
				'operatingmargin'	=>	$pricing->operatingmargin,
				'tax'				=>	$pricing->tax,
				'admincharge'		=>	$pricing->admincharge
			]);
			
			$totaladmincharge	=	round((($totalbudget*$pricing->admincharge)/100),2);
			$workorderamount	=	$totalbudget+$totaladmincharge;
			DB::table('eoi_work_order')
				->where('orderid',$orderid)
				->update([
					'totalremuneration'		=>	$totalremuneration,
					'totalbudget'			=>	$totalbudget,
					'totaladmincharge'		=>	$totaladmincharge,
					'workorderamount'		=>	$workorderamount,
				]);
			
			DB::commit();
		
			if($orderid)
			{
				return redirect()->route('confirmed.workorder',['orderid'=>$orderid])->with('success', 'Work order created successfully.');
			}
			else
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();	
			}
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }

	public function showOrder($orderid)
	{
		$order 	= 	DB::table('eoi_work_order')->where('orderid', $orderid)->first();
		$eoi	=	DB::table('eoi_request as a')
					->select('a.*','b.address','b.departmentname')
					->leftjoin('department_tbl as b','b.userid','a.userid')
					->where('a.requestid',$order->requestid)
					->first();
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
		$detail = 	[];
		
		if($eoi->categoryid==2)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->whereColumn('e.positionid', '=', 'a.positionid')
									 ->whereColumn('e.tierid', '=', 'a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
		}
		if($eoi->categoryid==1)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.tiername','c.remuneration')
							->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as c', function ($join){
								$join->on('c.remunerationid', '=', 'a.remunerationid')
									 ->whereColumn('c.tierid','=','a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
			
		}
		$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
		$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
		$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
		$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		
		return view('admin/master/work_order', compact('order', 'detail','vendor','eoi'));
	}

	public function confirmOrder($orderid)
	{
		$order 	= 	DB::table('eoi_work_order')->where('orderid', $orderid)->first();
		$eoi	=	DB::table('eoi_request as a')
					->select('a.*','b.address','b.departmentname')
					->leftjoin('department_tbl as b','b.userid','a.userid')
					->where('a.requestid',$order->requestid)
					->first();
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
		$detail = 	[];
		
		if($eoi->categoryid==2)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->whereColumn('e.positionid', '=', 'a.positionid')
									 ->whereColumn('e.tierid', '=', 'a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
		}
		if($eoi->categoryid==1)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.tiername','c.remuneration')
							->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as c', function ($join){
								$join->on('c.remunerationid', '=', 'a.remunerationid')
									 ->whereColumn('c.tierid','=','a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
			
		}
		$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
		$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
		$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
		$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		
		return view('admin/master/confirm_order', compact('order','detail','vendor','eoi'));
	}

	public function printWorkOrder($orderid)
	{
		$order 			= 	DB::table('eoi_work_order')->where('orderid', $orderid)->first();

		$eoi	=	DB::table('eoi_request as a')
					->select('a.*','b.address','b.departmentname')
					->leftjoin('department_tbl as b','b.userid','a.userid')
					->where('a.requestid',$order->requestid)
					->first();
		

		$vendor			=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

		$detail 		= 	[];
		
		if($eoi->categoryid==2)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->whereColumn('e.positionid', '=', 'a.positionid')
									 ->whereColumn('e.tierid', '=', 'a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
		}
		if($eoi->categoryid==1)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.tiername','c.remuneration')
							->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as c', function ($join){
								$join->on('c.remunerationid', '=', 'a.remunerationid')
									 ->whereColumn('c.tierid','=','a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
			
		}
		$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
		$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
		$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
		$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		
		return view('admin/printing/printworkorder', compact('order', 'detail','vendor','eoi'));
	}

    public function getFloatVendorData(Request $request)
	{

		$requestid	=	$request->input('requestid') ?? 0;

		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		
		$admincharge	=	$eoi->admincharge;
		$tax			=	$eoi->tax;
		$grandtotal		=	0;
		
		
		$tier_choice	=	$eoi->tier_choice;
		
		if($tier_choice==1)
		{
			$tierIds	=	[1];
		}
		else if($tier_choice>=2)
		{
			if($eoi->categoryid==2)
			{
				$detail	=	DB::table('eoi_request_detail as a')
							->select('a.duration','e.remuneration as budget')
							->leftJoin('remuneration_tbl as e', function($join){
								$join->on('e.sectorid', '=', 'a.sectorid');
								$join->on('e.positionid', '=', 'a.positionid');
							})
							->leftJoin('work_experience as f','f.experienceid','=','e.experienceid')
							->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as h','h.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as c','c.tierid','=','e.tierid')
							->where('e.tierid','=',2)
							->where('a.requestid','=',$requestid)
							->get();
				
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				foreach($detail as $rec)
				{
					$rec->budget	=	($rec->budget+(($rec->budget*$tax)/100))*$rec->duration;
					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
			}
			if($eoi->categoryid==1)
			{
				$pricing=	DB::table('pricing_tbl')->where('categoryid',$eoi->categoryid)->where('tierid',2)->first();
				$detail	=	DB::table('eoi_request_detail as a')
							->select(
								'a.recordid','a.qualification','a.duration','e.remuneration as budget','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype'
							)
							->leftJoin('remuneration_tbl as e', function ($join) use ($eoi) {
								$join->on('e.experiencelevel', '=', 'a.experiencelevel')
									 ->where('e.categoryid', '=', $eoi->categoryid)
									 ->where('e.tierid', '=', 2);
							})
							->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
							->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
							->where('a.requestid', '=', $eoi->requestid)
							->get();
							
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
					$rec->budget	=	($budget+(($budget*$pricing->tax)/100))*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;				
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
			}
			if($grandtotal>50000000)
			{
				$tierIds	=	[1];
			}
			else
			{
				$tierIds	=	[1,2];
			}
		}

		
		$tierid 		=	$request->input('tierid') ?? 0;

	
		$sectorIds		=	DB::table('eoi_request_detail')
								->where('requestid',$requestid)
								->distinct()
								->pluck('sectorid')
								->toArray();
		
		
		if($eoi->categoryid==2)
		{
			$data	=	DB::table('vendor_tbl as v')
						->join('vendor_sector as vs', 'vs.vendorid', '=', 'v.vendorid')
						->leftJoin('tiermaster_tbl as b', 'b.tierid', '=', 'v.tierid')
						->where('v.categoryid', $eoi->categoryid)
						->whereIn('v.tierid', $tierIds)
						->when($tierid != 3, function ($query) use ($tierid) {
							return $query->where('v.tierid', '=', $tierid);
						})
						->whereIn('vs.sectorid', $sectorIds)
						->whereNull('v.parentVendorId')
						->groupBy('v.vendorid')
						->havingRaw('COUNT(DISTINCT vs.sectorid) = ?', [count($sectorIds)])
						->select('v.*', 'b.tiername')
						->get();
		}
		if($eoi->categoryid==1)
		{
			$data	=	DB::table('vendor_tbl as v')
						->leftJoin('tiermaster_tbl as b', 'b.tierid', '=', 'v.tierid')
						->where('v.categoryid',$eoi->categoryid)
						->whereIn('v.tierid', $tierIds)
						->when($tierid != 3, function ($query) use ($tierid) {
							return $query->where('v.tierid', '=', $tierid);
						})
						->whereNull('v.parentVendorId')
						->select('v.*', 'b.tiername')
						->get();
		}
		
		$vids	=	"";
		foreach($data as $vendor)
		{
			$vendor->emails		=	DB::table('vendors_email')->select('recordid','email')->where('vendorid',$vendor->vendorid)->get();
			$vendor->sectors	=	DB::table('vendor_sector as a')
									->select('b.sectorname')
									->leftjoin('sector_tbl as b','b.sectorid','a.sectorid')
									->where('a.vendorid',$vendor->vendorid)
									->get();
			if($vids=='')
			$vids	=	$vendor->vendorid;
			else
			$vids	=	$vids.",".$vendor->vendorid;
		}
		
		DB::table('eoi_request')->where('requestid',$requestid)->update(['vendorids'=>$vids]);
		
		return view('admin/ajaxpages/floatvendorsTable',['data'=>$data]);
	}

    public function preBidEnquiryList(Request $request,$recordid)
	{
		$requestid	=	Crypt::decrypt($recordid);
		
							
        return view('admin/master/prebidenquiry_list',compact('recordid'));
    }

    public function preBidEnquiryData(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		
		$requestid	=	Crypt::decrypt($request->input('requestid'));
		$pagesearch =	$request->input('pagesearch');


		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		
		$data		=	DB::table('eoi_request_prebid as a')
							->select('a.*','b.eoinumber','b.projecttitle','b.projectduration','c.companyname')
							->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
							->leftjoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
							->when($pagesearch!='',function($query) use ($pagesearch){
								return $query->where('b.eoinumber','like','%'.$pagesearch.'%')
											 ->orwhere('b.projecttitle','like','%'.$pagesearch.'%');
							})
							->where('a.requestid',$requestid)
							->where('isprebid',1)
							->get();
		
		$broadcasted	=	DB::table('eoi_request_prebid_broadcast')
							->where('requestid',$requestid)
							->where('isprebid',1)
							->orderBy('broadcastid','desc')
							->get();

		$prebid	=	DB::table('eoi_request_prebid')
					->select('prebidstatus')
					->where('requestid',$requestid)
					->where('isprebid',1)
					->first();

		return view('admin/ajaxpages/prebidenquiryTable', ['data' => $data,'eoi'=>$eoi,'broadcasted'=>$broadcasted,'prebid'=>$prebid]);

    }

    public function preBidForward(Request $request,$recordid)
	{
		$requestid	=	Crypt::decrypt($recordid);
		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$exists		=	DB::table('eoi_request_prebid_forwarded')->where('requestid',$requestid)->exists();
		
		return view('admin/master/prebid_forward',compact('eoi','token','exists'));
    }

    public function preBidQueryData(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		$requestid	=	Crypt::decrypt($request->input('requestid')) ?? 0;
		
		$eoi		=	DB::table('eoi_request as a')
							->select('a.*','b.departmentname')
							->leftjoin('department_tbl as b','b.userid','a.userid')
							->where('a.requestid',$requestid)
							->first();
		
		
		$data		=	DB::table('eoi_request_prebid_forwarded')
							->where('requestid',$requestid)
							->orderby('creationdate')
							->get();

		return view('admin/ajaxpages/prebidqueriesData', ['eoi' => $eoi,'data'=>$data]);

    }

    public function storeForward(Request $request)
	{
		$request->merge([
			'deadlinedate'    => Carbon::parse($request->deadlinedate)->format('Y-m-d H:i:s'),
		]);
		
		if($request->interviewdate)
		{
			$request->merge([
				'interviewdate'   => Carbon::parse($request->interviewdate)->format('Y-m-d H:i:s'),
			]);			
		}
		     
        $rules = [
            'requestid'		=> 'required',
			'prebidenquiry'	=> 'nullable',
			'attachment' 	=> 'required|file|mimes:pdf,doc,docx|max:5120',
			'deadlinedate' 	=> 	'required|date|after_or_equal:today',
			'interviewdate' =>	'nullable|date|after_or_equal:today',
			
        ];

        $messages = [
            'requestid.required' 			=> 'EoI detail is required',
			'attachment.required' 			=> 'Attachment is required',
			'attachment.file' 				=> 'Invalid file type',
			'attachment.mimes' 				=> 'Invalid file type',
			'attachment.max' 				=> 'Maximum file size allowed is 1 MB.',
			'deadlinedate.required'			=> 	'Submission date is required',
			'deadlinedate.date'				=> 	'Invalid submission date',
			'deadlinedate.after_or_equal'	=> 	'Invalid submission date',
			'interviewdate.date'			=> 	'Invalid interview date',
			'interviewdate.after_or_equal'	=> 	'Invalid interview date',
        ];

        $validatedData = $request->validate($rules,$messages);

		if($request->interviewdate)
		{
			$deadline     = Carbon::parse($validatedData['deadlinedate']);
			$interview    = Carbon::parse($validatedData['interviewdate']);

			$errors = [];

			if (!$interview->gt($deadline)) {
				$errors['interviewdate'] = 'Interview date must be strictly after the Submission deadline.';
			}

			if (!empty($errors)) {
				return back()->withErrors($errors)->withInput();
			}
		}


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
				return back()->with('fail','Invalid data provided.')->withInput();
			}
			$attachment    = (String) $request->file('attachment');

			if($attachment!='')
			{
				$attachment= $request->file('attachment')->store('uploads/prebidenquiry', 'public');
			}		

			$declarationChecked = $request->has('declaration');
			if(!$declarationChecked)
			{
				$department	=	DB::table('eoi_request')->where('requestid',$eoi->requestid)->first();
				DB::table('eoi_request_prebid_forwarded')->insert([
					'requestid'		=>	$eoi->requestid,
					'fromuserid'	=>	$userId,
					'touserid'		=>	$department->userid,
					'creationdate'	=>	now(),				
					'message'		=>	$validatedData['prebidenquiry'],
					'attachment'	=>	$attachment ?? '',
					'postedby'		=>	'CHiPS'
				]);
				//DB::table('eoi_request')->where('requestid',$query->requestid)->update(['isinprebid'=>1]);
				DB::table('eoi_request_prebid')->where('requestid',$eoi->requestid)->update(['prebidstatus'=>'FORWARDED']);
				DB::commit();
				return back()->with('success','Pre-bid message/enquiry stored successfully!');
			}
			else
			{
				
				$department	=	DB::table('eoi_request')->where('requestid',$eoi->requestid)->first();
				
				$result	=	DB::table('eoi_request_floated')
								->selectRaw('GROUP_CONCAT(DISTINCT vendorid) AS vendorids, GROUP_CONCAT(DISTINCT userid) AS userids')
								->where('requestid', $department->requestid)
								->first();
				
				DB::table('eoi_request_prebid_broadcast')->insert([
					'requestid'			=>	$eoi->requestid,
					'userid'			=>	$userId,
					'broadcastedon'		=>	now(),				
					'broadcastmessage'	=>	$validatedData['prebidenquiry'],
					'attachment'		=>	$attachment ?? '',
					'vendorids'			=>	$result->vendorids,
					'userids'			=>	$result->userids,
				]);
				DB::table('eoi_request_prebid')->where('requestid',$eoi->requestid)->update(['prebidstatus'=>'BRODCASTED']);

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
					DB::table('eoi_request')->where('requestid',$eoi->requestid)->update([
						'interviewdate'	=>	$validatedData['interviewdate']
					]);
					
				}
				
				/*
				$usrType	=	DB::table('users_tbl as a')->where('userid',$eoi->userid)->first();
				if($usrType->isdepartment==1)
				{
					$officialemail	=	DB::table('department_tbl')->where('userid',$usrType->userid)->value('officialemail');
					if($officialemail!='')
					{
						Mail::to($officialemail)->send(new PublishPreBidMail($eoi,$usrType));
					}
				}
				if($usrType->ispm==1)
				{
					$officialemail	=	DB::table('department_tbl')->where('userid',$usrType->userid)->value('officialemail');
					if($officialemail!='')
					{
						Mail::to($officialemail)->send(new PublishPreBidMail($eoi,$usrType));
					}
				}
				*/
				DB::commit();
				return back()->with('success','Responses to the pre-bid queries have been successfully shared with all vendors.');
			}
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			if($attachment!='')
			Storage::disk('public')->delete($attachment);
			
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }
	
	/*
    public function downloadAllFiles(Request $request,$recordid)
	{
		$userId		= 	$request->session()->get('userId');
		$requestid	=	Crypt::decrypt($recordid) ?? 0;
		
		$records		=	DB::table('eoi_request_prebid')->where('requestid',$requestid)->whereNotNull('attachment')->get();
		$zipFileName 	= 	'prebid_attachments_' . date('Ymd_His') . '.zip';
		$zipFullPath 	= 	storage_path('app/public/' . $zipFileName);
		$zip 			= 	new ZipArchive;

	   if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE)
	   {
			foreach ($records as $record)
			{
				$relativePath = $record->attachment;  // e.g. 'uploads/prebidenquiry/MyFile.docx'
				$fullPath = storage_path('app/public/' . $relativePath);

				if (file_exists($fullPath))
				{
					$zip->addFile($fullPath, basename($relativePath));  // Add with filename only
				}
			}

			$zip->close();
			return response()->download($zipFullPath)->deleteFileAfterSend(true);
		}
		else
		{
			return response()->json(['error' => 'Failed to create ZIP file'], 500);
		}
    }
	*/
public function downloadAllFiles(Request $request, $recordid)
{
    $userId     = $request->session()->get('userId');
    $requestid  = Crypt::decrypt($recordid) ?? 0;

    // Fetch all records with attachments for this request
    $records = DB::table('eoi_request_prebid')
                ->where('requestid', $requestid)
                ->whereNotNull('attachment')
                ->get();

    // ZIP file name and full path
    $zipFileName = 'prebid_attachments_' . date('Ymd_His') . '.zip';
    $zipFullPath = storage_path('app/public/' . $zipFileName);
    
    $zip = new ZipArchive;

    if ($zip->open($zipFullPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {

        foreach ($records as $record) {

            $relativePath = $record->attachment;        // e.g. 'uploads/prebidenquiry/file.docx'
            $fullPath     = storage_path('app/public/' . $relativePath);

            if (file_exists($fullPath)) {

                // Create unique filename inside ZIP (prevents overwriting)
                $fileNameInZip = $record->vendorid . '_' . $record->queryid . '_' . basename($relativePath);

                // Add file to ZIP
                $zip->addFile($fullPath, $fileNameInZip);
            }
        }

        $zip->close();

        return response()->download($zipFullPath)->deleteFileAfterSend(true);
    } 
    else {
        return response()->json(['error' => 'Failed to create ZIP file'], 500);
    }
}	
    public function setResumeStatus(Request $request)
	{
        $rules = [
            'resumeid'	=>	'required',
        ];

        $messages = [
            'resumeid.required'		=>	'Resume detail is required.',
        ];

        $validatedData 	=	$request->validate($rules,$messages);
		try
		{
			DB::table('eoi_request_detail_resume')
			->where('resumeid',Crypt::decrypt($validatedData['resumeid']))
			->update([
				'isresumeviewed'	=>	1
			]);
			return response()->json(['status'=>200,'message'=>'Resume status changed successfully']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error '.$e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}

    }

    public function prepareWorkOrder(Request $request)
	{
        $rules = [
			'requestid'	=>	'required',
        ];

        $messages = [
			'requestid.required'	=>	'EoI number is required.',
        ];

        $validatedData 	=	$request->validate($rules,$messages);

        $userId		= 	$request->session()->get('userId');
        $userName   = 	$request->session()->get('userName');

		$eoiExists			=	DB::table('eoi_request')
							->where('requestid',Crypt::decrypt($validatedData['requestid']))
							->exists();
		if(!$eoiExists)
		{
			return response()->json([
				'errors' => [
					'requestid'	=>	['Invalid EoI detail.'],
				]
			],422);
		}


		$exists	=	DB::table('eoi_request_detail_resume')
						->where('requestid',Crypt::decrypt($validatedData['requestid']))
						->where('isresumeviewed',0)
						->exists();
		if($exists)
		{
			return response()->json([
				'errors' => [
					'resume'	=>	['Some resumes have not been reviewed yet. Please review all before proceeding.'],
				]
			], 422);			
			
		}

		
        $currentDateTime= 	now();
        $creationdate  	=	$currentDateTime->format('Y-m-d H:i:s');
		try 
		{
		
			return response()->json([
				'status' => 200,
				'redirect_url' => url('/master/generate/workorder/' . $validatedData['requestid'])
			]);			
			
			//return response()->json(['status'=>200,'vendorid'=>$validatedData['vendorid']]);	
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error '.$e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }
	
    public function generateWorkOrder(Request $request,$recordid)
	{
        $userId     = 	$request->session()->get('userId');
        $userName   = 	$request->session()->get('userName');
		
		$exists		=	DB::table('eoi_request')
						->where('requestid',Crypt::decrypt($recordid))
						->exists();

		if(!$exists)
		{
			return response()->json([
				'errors' => [
					'requestid'	=>	['Invalid EoI detail.'],
				]
			],422);
		}

        $currentDateTime= 	now();
        $creationdate  	=	$currentDateTime->format('Y-m-d H:i:s');
		try 
		{
			$requestid	=	Crypt::decrypt($recordid);
			
			$previousOrders	=	DB::table('eoi_work_order as a')
								->select('a.orderid','a.ordernumber','a.project_duration','a.orderdate','a.workorderduedate','a.signedcopy','b.companyname')
								->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
								->where('a.requestid',$requestid)
								->orderBy('a.orderdate')
								->get();
			
			$eoi		=	DB::table('eoi_request')
							->select('requestid','eoinumber','categoryid','tier_choice','vendorids')
							->where('requestid',$requestid)
							->first();


			$vendorIds 	= 	explode(',', $eoi->vendorids);

			
			$vendors	=	DB::table('vendor_tbl')
							->select('vendorid', 'companyname')
							->when($eoi->tier_choice == 3, function ($query) {
								return $query->whereIn('tierid', [1, 2]);
							}, function ($query) use ($eoi) {
								return $query->where('tierid', $eoi->tier_choice);
							})
							->where('categoryid', $eoi->categoryid)
							->whereIn('vendorid', $vendorIds)
							->orderBy('companyname')
							->get();

			
			$rateid		=	DB::table('eoi_request_detail')
							->where('requestid',Crypt::decrypt($recordid))
							->value('rateid');


			$rateList	=	DB::table('remuneration_rate_list')
							->where('categoryid',$eoi->categoryid)
							->where('rateid','>=',$rateid)
							->get();
			
			if($eoi->categoryid==2)
			{
				$resumes	=	DB::table('eoi_request_detail as a')
								->select(
									'a.recordid','a.qualification','a.duration','a.experience','a.remark','g.sectorname','h.consultantposition','a.employmenttype'
								)
								->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
								->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
								->where('a.requestid','=',Crypt::decrypt($recordid))
								->where('rateid',$rateid)
								->where('a.isordered',0)
								->get();				
			}
			if($eoi->categoryid==1)
			{
				$resumes	=	DB::table('eoi_request_detail as a')
								->select(
									'a.recordid','a.qualification','a.duration','a.role','a.experience','a.remark','a.employmenttype','a.experiencelevel'
								)
								->where('a.requestid', '=',Crypt::decrypt($recordid))
								->where('a.rateid',$rateid)
								->where('a.isordered',0)
								->get();
							
			}
			$token	=	rand('100000','999999').''.time();
			
			Session::put('form_token',$token);
			//Session::put('eoi_requestid',$eoi->requestid);
			//Session::put('eoi_vendorid',$vendor->vendorid);
			
			$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
			$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
			
			$levels	=	DB::table('remuneration_tbl')
						->select('experiencelevel')
						->where('experiencelevel','!=',0)
						->where('rateid',$rateid)
						->where('categoryid',$eoi->categoryid)
						->orderBy('experiencelevel')
						->distinct('experiencelevel')
						->get();

			$experiences=	DB::table('work_experience')
							->select('workexperience')
							->orderBy('experienceid')
							->get();
			
			return view('admin/master/prepare_workorder',compact('eoi','token','resumes','rateList','rateid','vendors','previousOrders','sectors','positions','levels','experiences'));
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error '.$e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }

    public function generateWoNumber(Request $request)
	{
        $requestid 	=	Crypt::decrypt($request->input('requestid'));
		$signedby 	=	$request->input('signedby');
		$wonumber	=	"";
		
		$eoinumber	=	DB::table('eoi_request')->where('requestid',$requestid)->value('eoinumber');
		$explode	=	explode("/",$eoinumber);
		
		if($signedby=='Chief Executive Officer (CEO)')
		{
			$wonumber	=	"/CEO/".$explode[0]."/".$explode[1]."/".$explode[2]."/".$explode[3]."/".$explode[4]."/".date('Y');
		}
		else if($signedby=='Jt. CEO (Project)')
		{
			$wonumber	=	"/Jt.CEO(P)/".$explode[0]."/".$explode[1]."/".$explode[2]."/".$explode[3]."/".$explode[4]."/".date('Y');
		}
		else if($signedby=='Jt. CEO(Finance)')
		{
			$wonumber	=	"/Jt.CEO(F)/".$explode[0]."/".$explode[1]."/".$explode[2]."/".$explode[3]."/".$explode[4]."/".date('Y');
		}
		else if($signedby=='Add. Chief Executive Officer')
		{
			$wonumber	=	"/ACEO/".$explode[0]."/".$explode[1]."/".$explode[2]."/".$explode[3]."/".$explode[4]."/".date('Y');
		}
		else if($signedby=='Chief Operating Officer')
		{
			$wonumber	=	"/COO/".$explode[0]."/".$explode[1]."/".$explode[2]."/".$explode[3]."/".$explode[4]."/".date('Y');
		}

        return response()->json(['wonumber'=>$wonumber]);
    }

    public function viewDepartmentUpdate(Request $request,$recordid)
	{
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();
		
		$data->totalbudget		=	$this->formatIndianCurrency($data->totalbudget);
		$data->totaladmincharge	=	$this->formatIndianCurrency($data->totaladmincharge);
		$data->totalamount		=	$this->formatIndianCurrency($data->totalamount);
		
		$detail		=	DB::table('eoi_request_detail as a')
							->select('a.recordid','a.qualification','a.duration','a.budget','a.admincharge','a.total','a.role','a.experience','a.employmenttype','a.remark','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition')
							->leftJoin('tiermaster_tbl as c','c.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
							->leftJoin('work_experience as f','f.experienceid','=','e.experienceid')
							->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as h','h.positionid','=','e.positionid')
							->where('a.requestid','=',$requestid)
							->get();
		
		$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
	
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$updates	=	DB::table('log_eoi_updates')
							->where('requestid',$requestid)
							->orderby('updatedon','desc')
							->get();

		return view('admin/master/view_departmentupdate',compact('data','detail','token','attachments','updates'));

    }

    public function markAsRead(Request $request)
	{
		DB::beginTransaction();
		try
		{
			$recordid	=	Crypt::decrypt($request->input('recordid'));
			$requestid	=	DB::table('log_eoi_updates')->where('recordid',$recordid)->value('requestid');
			
			DB::table('log_eoi_updates')
				->where('recordid',$recordid)
				->update([
					'isviewed'	=>	1,
					'viewedon'	=>	now(),
					'viewedby'	=>	session('userId')
				]);
			
			$count	=	DB::table('log_eoi_updates')->where('requestid',$requestid)->where('isviewed',0)->count();
			if($count==0)
			{
				DB::table('eoi_request')->where('requestid',$requestid)->update(['isupdated'=>0]);
			}
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Marked as read']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
		
			return back()->with('duplicate',$e->getMessage())->withInput();
		}

    }

    public function showEoiContent(Request $request,$recordid)
	{
		
		$requestid 	=	Crypt::decrypt($recordid);
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();
		
		if($data->categoryid==2)
		{
			/* FOR TIER 1 DATA START*/
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
			$detail		=	$this->priceService->getCsfPrice($data->requestid,$data->categoryid,1);
			
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			$data->totalmanmonth	=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal		=	$this->formatIndianCurrency($grandtotal);
			
			$tier1_html = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 1 DATA END*/


			/* FOR TIER 2 DATA START*/
			
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
			$detail		=	$this->priceService->getCsfPrice($data->requestid,$data->categoryid,2);
			
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier2_html = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END*/
		}
		if($data->categoryid==1)
		{
			/* FOR TIER 1 DATA START*/
			$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
			$detail	=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,1);
						
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
				$budget			=	$budget+(($budget*$pricing->tax)/100);
				$rec->budget	=	$budget*$rec->duration;

				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier1_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 1 DATA END*/

			/* FOR TIER 2 DATA START*/
			$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
			$detail	=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,2);
						
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
				$budget			=	$budget+(($budget*$pricing->tax)/100);
				$rec->budget	=	$budget*$rec->duration;

				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier2_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END*/
			
		}
		$old_letter	=	DB::table('eoi_files_update')->where('requestid',$requestid)->get();
		
		$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();

		$committee=	DB::table('eoi_request_committee as a')
						->select()
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();


		$html = view('admin.ajaxpages.showeoiContent',['data'=>$data,'attachments'=>$attachments,'tier1_html'=>$tier1_html,'tier2_html'=>$tier2_html,'old_letter'=>$old_letter,'committee'=>$committee])->render();
		
		
		return response()->json(['status'=>200,'message'=>'EoI Content.','formhtml' => $html]);

		
    }
    public function showFactSheet(Request $request,$recordid)
	{
		$releasedate	=	$request->input('releasedate');
		$prebidlastdate	=	$request->input('prebidlastdate');
		$deadlinedate	=	$request->input('deadlinedate');
		$interviewdate	=	$request->input('interviewdate');
		
		$requestid 	=	Crypt::decrypt($recordid);
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();
		

		
		$data->releasedate		=	$releasedate;
		$data->prebidlastdate	=	$prebidlastdate;
		$data->deadlinedate		=	$deadlinedate;
		$data->interviewdate	=	$interviewdate;
		
		$html = view('admin.ajaxpages.showFactSheetContent',['data'=>$data])->render();
		
		
		return response()->json(['status'=>200,'message'=>'EoI Content.','formhtml' => $html]);

		
    }

    public function showPreviewEoiContent(Request $request,$recordid)
	{
		$releasedate		=	$request->input('releasedate');
		$prebidlastdate		=	$request->input('prebidlastdate');
		$deadlinedate		=	$request->input('deadlinedate');
		$interviewdate		=	$request->input('interviewdate');
		$interview_place	=	$request->input('interview_place');
		$selection_method	=	$request->input('selection_method');
		$page_indexing		=	$request->input('page_indexing');
		$chips_objective	=	$request->input('chips_objective');
		$termsandcondition	=	$request->input('termsandcondition');
		$criticalinformation=	$request->input('criticalinformation');
		$isPreview			=	$request->input('isPreview');
		
		
		$requestid 	=	Crypt::decrypt($recordid);
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->first();
		$team_composition	=	"";
		if($data->categoryid==2)
		{
			$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
			
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
			$html 	= 	[];
			
			foreach($tiers as $tier)
			{
				$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				break;
			}
			$tier_html = view('admin.viewpages.csf_tier_html_without_price_withindex',['data'=>$data,'detail'=>$detail])->render();

			$resource_summary	=	$this->priceService->getCsfResourceSummary($requestid);
			$team_composition 	= 	view('admin.viewpages.team_composition',['resource_summary'=>$resource_summary])->render();
			
		}
		if($data->categoryid==1)
		{
			$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
			
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
			$html = [];
			foreach($tiers as $tier)
			{
				$detail		=	$this->priceService->getAwdPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				break;
			}
			$tier_html = view('admin.viewpages.awd_tier_html_without_price_withindex',['data'=>$data,'detail'=>$detail])->render();			
			$resource_summary	=	$this->priceService->getAwdResourceSummary($requestid);
			$team_composition 	= 	view('admin.viewpages.team_composition',['resource_summary'=>$resource_summary])->render();

		}
		$old_letter	=	DB::table('eoi_files_update')->where('requestid',$requestid)->get();
		
		$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();

		$committee=	DB::table('eoi_request_committee as a')
						->select()
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();

		
		$data->releasedate			=	$releasedate;
		$data->prebidlastdate		=	$prebidlastdate;
		$data->deadlinedate			=	$deadlinedate;
		$data->interviewdate		=	$interviewdate;
		$data->interview_place		=	$interview_place;
		$data->selection_method		=	$selection_method;
		$data->page_indexing		=	$page_indexing;
		$data->chips_objective		=	$chips_objective;
		$data->termsandcondition	=	$termsandcondition;
		$data->criticalinformation	=	$criticalinformation;
		

		
		if($isPreview==1)
		{
			$html = view('admin.ajaxpages.previeweoi',['data'=>$data,'attachments'=>$attachments,'tier_html'=>$tier_html,'old_letter'=>$old_letter,'committee'=>$committee,'team_composition'=>$team_composition])->render();


			
			return response()->json(['status'=>200,'message'=>'EoI Content.','formhtml' => $html]);
		}
		else
		{
			/*
			$html	=	PDF::loadView('admin.ajaxpages.downloadeoi',compact('data','attachments','tier_html','old_letter','committee'))->setPaper('A4','portrait');
			*/
			$html = PDF::loadView('admin.ajaxpages.downloadeoi',compact('data','attachments','tier_html','old_letter','committee','team_composition'))
			->setPaper('A4', 'portrait')
			->setOptions([
				'isPhpEnabled' => true,
				'isHtml5ParserEnabled' => true,
				'isRemoteEnabled' => true,
			]);
			
			$filename	=	uniqid()."_eoi_preview.pdf";
			
			return $html->download($filename);
		}
	
		
    }

    public function eoiCancelRequest(Request $request,$recordid=NULL)
	{
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','eoicancel');
		Session::put('menid',115);
		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=115 and ispermitted=1 and userid=".$userId.") as actions"))
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
		if($recordid!='')
		{
			DB::table('eoi_cancellation')
			->where('requestid',Crypt::decrypt($recordid))
			->update([
				'isViewed'	=>	1,
				'viewedBy'	=>	$userId,
				'viewedOn'	=>	now()
			]);
		}
		$departments	=	DB::table('department_tbl')->orderby('departmentname')->get();
		
        return view('admin/master/cancellation_list',compact('departments','recordid'));
    }

    public function getCancelledEoiData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

		$deptid 		=	$request->input('departmentid');

		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize',100);
		$currentPage= 	$request->input('page', 1);
		$data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','d.jobcategory','e.cancellation_remark','e.cancellation_attachment','e.canReqOn','e.cancelledOn')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->join('eoi_cancellation as e','e.requestid','=','a.requestid')
							->when($pagesearch!='',function($query) use ($pagesearch){
								return $query->where('b.name','like','%'.$pagesearch.'%');
							})
							->when($departmentId!=0,function($query) use ($departmentId,$userId){
								return $query->where('a.userid','=',$userId);
							})
							->when($deptid!=0,function($query) use ($deptid){
								return $query->where('a.userid','=',$deptid);
							})
							->orderBy('a.creationdate','DESC')
							->paginate($pagesize,['*'],'page',$currentPage);
	
	
		return view('admin/ajaxpages/eoicancelTable',['data' => $data]);

    }


	public function eoiPreviewPrintable(Request $request,$recordid)
	{
		$requestid		=	Crypt::decrypt($recordid);
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		
		$data	=	DB::table('eoi_request as a')
						->select('a.*','b.jobcategory')
						->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
						->where('a.requestid','=',$requestid)
						->first();
						
		$department	=	DB::table('department_tbl')->where('userid',$data->userid)->first();
		$user		=	DB::table('users_tbl')->where('userid',$userId)->first();

		$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
		$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');

		/* FOR TIER 1 DATA START*/
		if($data->categoryid==2)
		{		
	
			$html = [];
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				$html[] 	= 	view('admin.viewpages.csf_tier_html_without_price',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
				break;
			}
			
			return view('admin/ajaxpages/eoiPreviewPrintable', [
				'data'				=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'categoryid'		=>	$data->categoryid,
				'html'				=>	$html,
			]);
		}
		if($data->categoryid==1)
		{
			$html = [];
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getAwdPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				$html[] 	= 	view('admin.viewpages.awd_tier_html_without_price',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
				break;
			}
			
			return view('admin/ajaxpages/eoiPreviewPrintable', [
				'data'				=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'categoryid'		=>	$data->categoryid,
				'html'				=>	$html,
			]);
		}
	}

    public function showDraftPreview(Request $request,$requestid)
	{
		try
		{
			$requestid 	=	Crypt::decrypt($requestid);
			$data		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			
			$releasedate	=	$request->input('releasedate');
			$prebidlastdate	=	$request->input('prebidlastdate');
			$deadlinedate	=	$request->input('deadlinedate');
			$interviewdate	=	$request->input('interviewdate');

			$eoinumber				=	$request->input('eoinumber');
			$tableofcontent			=	$request->input('tableofcontent'); 	
			$chips_objective		=	$request->input('chips_objective'); 	
			$projectobjective		=	$request->input('projectobjective');
			$scopeofwork			=	$request->input('scopeofwork');
			$issuername				=	$request->input('issuername'); 
			$engagementname			=	$request->input('engagementname'); 
			$evaluation_index		=	$request->input('evaluation_index'); 
			$communicationaddress	=	$request->input('communicationaddress');
			$anyother				=	$request->input('anyother');
			$evaluationprocess		=	$request->input('evaluationprocess');
			$termsandcondition		=	$request->input('termsandcondition');
			$criticalinformation	=	$request->input('criticalinformation');
			$documentrequired		=	$request->input('documentrequired');
			
			$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();		
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
			$team_composition	=	"";
			if($data->categoryid==2)
			{
				$html = [];
				foreach($tiers as $tier)
				{
					$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
					break;
				}
				$tier_html = view('admin.viewpages.csf_tier_html_without_price_withindex',['data'=>$data,'detail'=>$detail])->render();
				
				$resource_summary	=	$this->priceService->getCsfResourceSummary($requestid);
				$team_composition 	= 	view('admin.viewpages.team_composition',['resource_summary'=>$resource_summary])->render();
				
			}
			if($data->categoryid==1)
			{
				$html = [];
				foreach($tiers as $tier)
				{
					$detail		=	$this->priceService->getAwdPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
					break;
				}
				$tier_html = view('admin.viewpages.awd_tier_html_without_price_withindex',['data'=>$data,'detail'=>$detail])->render();

				$resource_summary	=	$this->priceService->getAwdResourceSummary($requestid);
				$team_composition 	= 	view('admin.viewpages.team_awd_composition',['resource_summary'=>$resource_summary])->render();
			}
			
			
			$html = view('admin.ajaxpages.showDraftPreviewContent',[
				'data'					=>	$data,
				'releasedate'			=>	$releasedate,
				'prebidlastdate'		=>	$prebidlastdate,
				'deadlinedate'			=>	$deadlinedate,
				'interviewdate'			=>	$interviewdate,
				'eoinumber'				=>	$eoinumber,
				'tableofcontent'		=>	$tableofcontent,
				'chips_objective'		=>	$chips_objective,
				'issuername'			=>	$issuername,
				'engagementname'		=>	$engagementname,
				'evaluation_index'		=>	$evaluation_index,
				'communicationaddress'	=>	$communicationaddress,
				'anyother'				=>	$anyother,
				'evaluationprocess'		=>	$evaluationprocess,
				'termsandcondition'		=>	$termsandcondition,
				'criticalinformation'	=>	$criticalinformation,
				'documentrequired'		=>	$documentrequired,
				'tier1_html'			=>	$tier_html,
				'projectobjective'		=>	$projectobjective,
				'scopeofwork'			=>	$scopeofwork,
				'team_composition'		=>	$team_composition
			])->render();
			
			
			return response()->json(['status'=>200,'message'=>'EoI Content.','formhtml' => $html]);
		}
		catch(QueryException $e)
		{
			Log::error('Error '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		catch (\Exception $e)
		{
			Log::error('Error '.$e->getMessage());

			return response()->json([
				'status' => 500,
				'message' => $e->getMessage()
			], 500);
		}		
    }

	public function publishedFirms(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid'))
		]);
		
        $rules = [
			'requestid'	=>	'required|exists:eoi_request,requestid',
        ];

        $messages = [
			'requestid.required'=> 'Please provide request detail',
			'requestid.exists' 	=> 'Invalid request detail provided',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		
		try
		{
			$requestid	=	$request->input('requestid');
			
			$eoi	=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json([
					'message'	=> 	'Record not found. Please check and try again.',
					'status'	=> 	400,
				], 400);				
				
			}
			
			$data	=	DB::table('eoi_request_floated as a')
						->select('b.companyname','b.shortname','b.officelocation','a.floateddate','c.tiername')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('tiermaster_tbl as c','c.tierid','=','b.tierid')
						->where('a.requestid',$requestid)
						->orderBy('b.tierid')
						->orderBy('b.companyname')
						->get();
			
			$html	=	view('admin.ajaxpages.publishedfirmTable',['data'=>$data,'eoi'=>$eoi])->render();
			
			return response()->json([
				'message' 	=> 	'Firm List',
				'status' 	=> 	200,
				'formhtml'	=>	$html,
			],200);
		}
		catch(QueryException $e)
		{
			
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

    public function showApprovalForm(Request $request,$requestid)
	{
		$userId	=	$request->session()->get('userId');
		$requestid 	=	Crypt::decrypt($requestid);
		
		$requests	=	DB::table('eoi_approval as a')
						->select('a.*','b.projecttitle','c.name as fromname','d.name as toname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.fromuserid')
						->leftjoin('users_tbl as d','d.userid','=','a.touserid')
						->where('a.requestid',$requestid)
						->orderBy('a.recordid')
						->get();
		$lastRequest	=	DB::table('eoi_approval')
							->where('requestid', $requestid)
							->where('fromuserid',$userId)
							->latest('recordid')
							->first();

		$approvalUsers	=	DB::table('users_tbl')
							->where('isuser',1)
							->where('forapproval',1)
							->where('approvallevel','>',0)
							->where('userid','!=',$userId)
							->orderby('approvallevel','DESC')
							->orderBy('name')
							->get();
							
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$html = view('admin.ajaxpages.approvalform',[
			'requests'		=>	$requests,
			'requestid'		=>	$requestid,
			'approvalUsers'	=>	$approvalUsers,
			'lastRequest'	=>	$lastRequest,
			'token'			=>	$token
		])->render();
		
		
		return response()->json(['status'=>200,'message'=>'EoI Content.','approvalform' => $html]);
		
    }

    public function sendToApproval(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('request_id'))
		]);
		
        $rules = [
			'requestid'			=>	'required|exists:eoi_request,requestid',
			'user_id'			=>	'required|exists:users_tbl,userid',
			'approval_remark' 	=> 	'nullable|max:500|regex:/^[A-Za-z0-9\s\.\,\:\-\&\(\)\[\]\/\\\\]+$/',
        ];

        $messages = [
			'requestid.required'	=> 'Request details are required',
			'requestid.exists'		=> 'The provided request details are invalid.',
			'user_id.required' 		=> 'User selection is required.',
			'user_id.exists' 		=> 'The selected user is invalid.',
			'approval_remark.max'	=> 'Approval remark cannot exceed 500 characters.',
			'approval_remark.regex'	=> 'Approval remark may contain only letters, numbers, spaces, and the following characters: . ( ) [ ] / \\',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return response()->json([
				'status'	=>	400,
				'message'	=>	'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.'
			],200);				

		}		
		
		try
		{
			$userId	=	$request->session()->get('userId');
			
			$lastApproval	=	DB::table('eoi_approval')
								->where('requestid', $validatedData['requestid'])
								->latest('recordid')
								->first();
			if($lastApproval)
			{
				if($lastApproval->fromuserid==$userId)
				{
					return response()->json([
						'status'	=>	400,
						'message'	=>	'An approval request for this EOI is already in process. A new request cannot be initiated until the current approval cycle is completed.'
					],200);				
				}
			}
			if(!$lastApproval || $lastApproval->fromuserid != $userId)
			{

				DB::table('eoi_approval')->insert([
					'requestid'     => $validatedData['requestid'],
					'fromuserid'    => $userId,
					'touserid'      => $validatedData['user_id'],
					'remark'        => $validatedData['approval_remark'],
					'requestdate'   => now()
				]);

				return response()->json([
					'status'=>200,
					'message'=>'The action has been confirmed successfully.'
				],200);

			}
			else
			{
				return response()->json([
					'status'=>400,
					'message'=>'You have already submitted the latest approval request. Please await approval.'
				],200);
			}			
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }


    public function eoiApproval(Request $request,$recordid)
	{
		$userId    	= 	$request->session()->get('userId');
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
						->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
						->leftJoin('users_tbl as b','b.userid','=','a.userid')
						->leftJoin('department_tbl as c','c.userid','=','a.userid')
						->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
						->where('requestid','=',$requestid)
						->first();
		
		if($data->categoryid==2)
		{
			/* FOR TIER 1 DATA START */
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
			$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,1);
			
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier1_html = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 1 DATA END*/


			/* FOR TIER 2 DATA START*/
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();

			$detail	=	$this->priceService->getCsfPrice($requestid,$data->categoryid,2);

			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier2_html = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			/* FOR TIER 2 DATA END*/
		}
		if($data->categoryid==1)
		{
			/* FOR TIER 1 DATA START*/
			$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
			$detail	=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,1);
						
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
				$budget			=	$budget+(($budget*$pricing->tax)/100);
				$rec->budget	=	$budget*$rec->duration;

				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier1_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 1 DATA END*/

			/* FOR TIER 2 DATA START*/
			$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
			$detail	=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,2);
						
			$totalmanmonth		=	0;
			$adminchargetotal	=	0;
			$grandtotal			=	0;
			foreach($detail as $rec)
			{
				$rec->baseprice	=	$rec->budget;
				$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
				$budget			=	$budget+(($budget*$pricing->tax)/100);
				$rec->budget	=	$budget*$rec->duration;

				$totalmanmonth	=	$totalmanmonth+$rec->budget;
			}

			$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
			$grandtotal			=	$totalmanmonth+$adminchargetotal;
			
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier2_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END */
			
		}
		$old_letter	=	DB::table('eoi_files_update')->where('requestid',$requestid)->get();
		
		$attachments=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
		
		$committee=	DB::table('eoi_request_committee as a')
						->select()
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();
	
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$requests	=	DB::table('eoi_approval as a')
						->select('a.*','b.projecttitle','c.name as fromname','d.name as toname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.fromuserid')
						->leftjoin('users_tbl as d','d.userid','=','a.touserid')
						->where('a.requestid',$requestid)
						->orderBy('a.recordid')
						->get();

		$lastRecord	=	DB::table('eoi_approval as a')
						->select('a.*','b.projecttitle','c.name as fromname','d.name as toname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.fromuserid')
						->join('users_tbl as d','d.userid','=','a.touserid')
						->where('a.requestid',$requestid)
						->orderBy('a.requestdate','desc')
						->first(); 

		$approvalUsers	=	DB::table('users_tbl')
							->where('forapproval',1)
							->where('approvallevel','>',0)
							->where('userid','!=',$userId)
							->orderby('approvallevel','DESC')
							->orderBy('name')
							->get();

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/eoi_approval',compact('data','token','attachments','old_letter','tier1_html','tier2_html','committee','approvalUsers','requests','lastRecord','token'));
		
    }


    public function sendToApprove(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('req_id'))
		]);
		
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'approval_otp' 	=> 	'required|digits:6',
        ];

        $messages = [
			'requestid.required'      => 'Request details are required to proceed.',
			'requestid.exists'        => 'The selected request details are invalid or do not exist.',
			'approval_otp.required'   => 'Please enter the One-Time Password (OTP).',
			'approval_otp.digits'     => 'The OTP must be a valid 6-digit number.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId	=	$request->session()->get('userId');
		try
		{
			
			$isValid	=	DB::table('eoi_request')
							->where('requestid',$validatedData['requestid'])
							->where('approvalOtp',$validatedData['approval_otp'])
							->first();
			if(!$isValid)
			{
				return response()->json(['status'=>300,'message'=>'The entered One-Time Password (OTP) is invalid. Please try again.']);
			}
			
			$lastRecord	=	DB::table('eoi_approval')
							->where('requestid',$validatedData['requestid'])
							->where('touserid',$userId)
							->orderByDesc('recordid')
							->first();

			if($lastRecord)
			{
				DB::table('eoi_approval')
				->insert([
					'requestid'		=>	$lastRecord->requestid,
					'fromuserid'	=>	$lastRecord->touserid,
					'remark'		=>	'Approved',
					'closingdate'	=>	now(),
					'closedby'		=>	$userId
				]);
				/*
				DB::table('eoi_approval')
				->where('recordid', $lastRecord->recordid)
				->update([
					'closingdate' => now(),
					'closedby'    => $userId
				]);
				*/
			}
			
			DB::table('eoi_request')
			->where('requestid',$validatedData['requestid'])
			->update([
				'approvalClosed'	=>	1
			]);

			return response()->json(['status'=>200,'message'=>'The EoI details have been reviewed and approved successfully.']);
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function sendApprovalOTP(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('request_id'))
		]);
		
        $rules = [
			'requestid'			=>	'required|exists:eoi_request,requestid',
        ];

        $messages = [
			'requestid.required'	=> 'Request details are required',
			'requestid.exists'		=> 'The provided request details are invalid.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId	=	$request->session()->get('userId');
		try
		{
			$email	=	DB::table('users_tbl')->where('userid',$userId)->value('email');
			if($this->featureService->isEmailEnabled())
			{
				if($email!='')
				{
					$otp	=	rand('100000','999999');
					
					Mail::to($email)->send(new ApprovalOTPMail($otp));
					
					DB::table('eoi_request')
					->where('requestid',$validatedData['requestid'])
					->update([
						'approvalOtp'	=>	$otp
					]);
				}
			}
			return response()->json(['status'=>200,'message'=>'']);
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function extendEoiDate(Request $request,$recordid)
	{
		$userId    	= 	$request->session()->get('userId');
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
						->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
						->leftJoin('users_tbl as b','b.userid','=','a.userid')
						->leftJoin('department_tbl as c','c.userid','=','a.userid')
						->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
						->where('requestid','=',$requestid)
						->first();
		

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		
		return view('admin/master/extend_eoi',compact('data','token'));
		
    }
	public function storeEoiExtension(Request $request,$recordid)
	{
		$oldData = DB::table('eoi_request')
					->select('deadlinedate','interviewdate','prebidlastdate')
					->where('requestid','=',Crypt::decrypt($recordid))
					->first();
		
		if(!$oldData)
		{
			return response()->json([
				'message' => 'nvalid EOI details provided. Please check and try again.',
				'errors'  => $validator->errors()
			], 422);
			
		}

		$rules = [
			'prebidlastdate' => ['required', 'date_format:d-m-Y'],
			'deadlinedate' => ['required', 'date_format:d-m-Y g:i A'],
			'interviewdate' => ['required','date_format:d-m-Y g:i A'],
		];


		$messages = [
			'prebidlastdate.required'        => 'Pre-bid date is required',
			'prebidlastdate.date_format'     => 'Invalid pre-bid last date',
			'deadlinedate.required'          => 'Deadline date is required',
			'deadlinedate.date_format'       => 'Invalid dead line date',
			'interviewdate.date_format'      => 'Invalid Presentation And Interview date',
		];

		$validator = Validator::make($request->all(), $rules, $messages);
		$userId    	= 	$request->session()->get('userId');
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('duplicate','The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.')->withInput();
		}		
		
		// Chronological validation
		if($request->prebidlastdate)
		{
			$validator->after(function ($validator) use ($request) {
				$prebidlastdate = Carbon::createFromFormat('d-m-Y', $request->prebidlastdate);
				$deadlinedate   = Carbon::createFromFormat('d-m-Y h:i A', $request->deadlinedate);
				if($request->interviewdate!='')
				{
					$interviewdate  = Carbon::createFromFormat('d-m-Y h:i A', $request->interviewdate);
				}

				if ($prebidlastdate->gt($deadlinedate)) {
					$validator->errors()->add('deadlinedate', 'Deadline date must be after or equal to pre-bid date.');
				}
				if($request->interviewdate!='')
				{
					if ($deadlinedate->gt($interviewdate)) {
						$validator->errors()->add('interviewdate', 'Interview date must be after or equal to deadline date.');
					}
				}
			});


			if ($validator->fails()) {

				if ($request->expectsJson()) {
					return response()->json([
						'message' => 'Validation failed',
						'errors'  => $validator->errors()
					], 422);
				}

				return redirect()
					->back()
					->withErrors($validator)
					->withInput();
			}
		}
		$validatedData = $validator->validated();
		
		$oldData->prebidlastdate=	date('d-m-Y',strtotime($oldData->prebidlastdate));
		$oldData->deadlinedate	=	date('d-m-Y H:i:s',strtotime($oldData->deadlinedate));
		$oldData->interviewdate	=	date('d-m-Y H:i:s',strtotime($oldData->interviewdate));

		try
		{
			if(!empty($validatedData['prebidlastdate']) && strtotime($validatedData['prebidlastdate']))
			{
				$validatedData['prebidlastdate'] = date('Y-m-d', strtotime($validatedData['prebidlastdate']));
			}
			else
			{
				$validatedData['prebidlastdate'] = null;
			}

			if(!empty($validatedData['deadlinedate']) && strtotime($validatedData['deadlinedate']))
			{
				$validatedData['deadlinedate'] = date('Y-m-d H:i:s', strtotime($validatedData['deadlinedate']));
			}
			else
			{
				$validatedData['deadlinedate'] = null;
			}			
			if(!empty($validatedData['interviewdate']) && strtotime($validatedData['interviewdate']))
			{
				$validatedData['interviewdate'] = date('Y-m-d H:i:s', strtotime($validatedData['interviewdate']));
			}
			else
			{
				$validatedData['interviewdate'] = null;
			}			
			
			DB::beginTransaction();
						
			DB::table('eoi_request')->where('requestid','=',Crypt::decrypt($recordid))->update([
				'prebidlastdate'		=>	$validatedData['prebidlastdate'],
				'deadlinedate'			=>	$validatedData['deadlinedate'],
				'interviewdate'			=>	$validatedData['interviewdate'],
				'isExtended'			=>	1
			]);
			
			$this->logService->logChanges(Crypt::decrypt($recordid), (array)$oldData, $validatedData);
			
			DB::table('eoi_date_extension')->insert([
				'requestid'			=>	Crypt::decrypt($recordid),
				'old_prebidlastdate'=>	date('Y\-m\-d',strtotime($oldData->prebidlastdate)) ?? NULL,
				'new_prebidlastdate'=>	$validatedData['prebidlastdate'],
				'old_deadlinedate'	=>	date('Y\-m\-d H:i:s',strtotime($oldData->deadlinedate)) ?? NULL,
				'new_deadlinedate'	=>	$validatedData['deadlinedate'],
				'old_interviewdate'	=>	date('Y\-m\-d H:i:s',strtotime($oldData->interviewdate)) ?? NULL,
				'new_interviewdate'	=>	$validatedData['interviewdate'] ?? NULL,
				'extendedon'		=>	now(),
				'extendedby'		=>	$userId
			]);
			
			$eoi	=	DB::table('eoi_request')
						->select('requestid','userid','vendorids','releasedate','prebidlastdate','deadlinedate','interviewdate','eoinumber','projecttitle')
						->where('requestid',Crypt::decrypt($recordid))
						->first();
			
			if($this->featureService->isEmailEnabled())
			{
				$officialemail	=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('officialemail') ?? NULL;
				if($eoi->vendorids!='')
				{
					$vendorIds = explode(',', $eoi->vendorids);
					$emails	=	DB::table('vendors_email')
								->whereIn('vendorid', $vendorIds)
								->pluck('email')
								->toArray();
					$departmentName	=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('departmentname') ?? NULL;
					
					if($officialemail=='')
					{
						Mail::to($emails)->send(new FirmEoiExtensionMail($eoi,$departmentName));
					}
					else
					{
						Mail::to($emails)->cc($officialemail)->send(new FirmEoiExtensionMail($eoi,$departmentName));
					}
				}
			}
			DB::commit();
			return back()->with('success','The extension of the deadline has been processed successfully. All eligible firms and the concerned department have been informed via email.');	
			
			/*
			return redirect('master/eoi/requestlist')->with([
				'success' => 'Draft detail submitted successfully!',
				'category' => $category,
			]);
			*/
		}
		catch(QueryException $e)
		{
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate','Something went wrong. Please check the data and try again.')->withInput();	
		}		
	}

    public function updateInterviewDateRequest(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid'))
		]);
		
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'remark'		=>	'required|regex:/^[A-Za-z0-9\s\.,\-_\(\)\/@&]+$/',
        ];

        $messages = [
			'requestid.required'      => 'Request details are required to proceed.',
			'requestid.exists'        => 'The selected request details are invalid or do not exist.',

			'remark.required'    => 'Remark is required.',
			'remark.regex'       => 'Remark contains invalid characters. Only letters, numbers, spaces, and @ & . , - _ ( ) / are allowed.',			
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId	=	$request->session()->get('userId');
		try
		{			
			$exists	=	DB::table('eoi_interview_date')
						->where('requestid',$validatedData['requestid'])
						->whereNull('isUpdated')
						->exists();
			if($exists)
			{
				return response()->json(['status'=>400,'message'=>'You already have a pending request. Please wait for it to be processed.']);
			}
			DB::table('eoi_interview_date')
				->insert([
					'requestid'		=>	$validatedData['requestid'],
					'remark'		=>	$validatedData['remark'] ?? NULL,
					'requested_by'	=>	$userId,
					'requested_on'	=>	now(),
				]);
			return response()->json(['status'=>200,'message'=>'Request to update the interview date and time has been sent successfully.']);
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }


    public function getEoIDates(Request $request,$requestid)
	{
		$userId    	= 	$request->session()->get('userId');
		$requestid 	=	Crypt::decrypt($requestid);
		
        $data 		= 	DB::table('eoi_request as a')
						->select('a.requestid','a.eoinumber','a.projecttitle','a.releasedate','a.prebidlastdate','a.deadlinedate','a.interviewdate','a.isordered','a.creationdate','a.projectduration','a.eoistatus','a.engagementname','b.name')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('a.requestid','=',$requestid)
						->first();
		

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		
		return view('admin/master/update_eoidates',compact('data','token'));
		
    }

    public function updateEoiDates(Request $request)
	{
		$request->merge([
			'requestid' => Crypt::decrypt($request->requestid),
		]);
		
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'releasedate'	=>	'required|date',
			'prebidlastdate'=>	'required|date',
			'deadlinedate'	=>	'required|date',
			'interviewdate'	=>	'nullable|date',
        ];

		$messages = [
			'requestid.required'	=>	'EoI detail is required.',
			'requestid.exists'      =>	'The selected EoI detail is invalid.',

			'releasedate.required'  => 	'Pre-bid last date is required.',
			'releasedate.date'		=> 	'Pre-bid last date must be in Y-m-d format.',

			'prebidlastdate.required'   => 'Pre-bid last date is required.',
			'prebidlastdate.date'		=> 'Pre-bid last date must be in Y-m-d format.',

			'deadlinedate.required'     => 'Deadline date is required.',
			'deadlinedate.date'  		=> 'Deadline date must be in Y-m-d H:i:s format.',

			'interviewdate.date' 		=> 'Interview date must be in Y-m-d H:i:s format.',
		];
	
		$validatedData 	= 	$request->validate($rules,$messages);


		$fieldsToCheck = [
			'releasedate',
			'deadlinedate',
			'prebidlastdate',
			'interviewdate',
		];

		$validator = Validator::make($validatedData, []);

		$service = $this->dateService;

		$validator->after(function ($validator) use ($validatedData, $fieldsToCheck, $service) {

			foreach ($fieldsToCheck as $field) {

				if (!empty($validatedData[$field])) {

					$result = $service->isDateAvailable($validatedData[$field]);

					if ($result[0] === false) {
						$validator->errors()->add($field, $result[1]);
					}
				}
			}
		});
		$validator->validate();


	
		$userId	=	$request->session()->get('userId');
		try
		{
			$oldData	=	DB::table('eoi_request')
							->select('prebidlastdate','deadlinedate','interviewdate')
							->where('requestid',$validatedData['requestid'])
							->first();


			$release = Carbon::parse($validatedData['releasedate']);
			
			$prebid = Carbon::parse($validatedData['prebidlastdate']);

			$deadline = Carbon::parse($validatedData['deadlinedate']);

			$interview = !empty($validatedData['interviewdate']) ? Carbon::parse($validatedData['interviewdate']) : null;

			$validatedData['releasedate']	= 	$release->format('Y-m-d');
			$validatedData['prebidlastdate']= 	$prebid->format('Y-m-d');
			$validatedData['deadlinedate']  = 	$deadline->format('Y-m-d H:i:s');
			$validatedData['interviewdate'] =	$interview ? $interview->format('Y-m-d H:i:s') : null;			
			

			if($release->gte($prebid))
			{
				return back()->withErrors([
					'releasedate'	=>	'Release date must be before the pre-bid last date.'
				])->withInput();
			}

			if($prebid->gte($deadline))
			{
				return back()->withErrors([
					'prebidlastdate' => 'Pre-bid last date must be before the deadline date.'
				])->withInput();
			}

			if($interview && $deadline->gte($interview))
			{
				return back()->withErrors([
					'interviewdate' => 'Interview date must be after the deadline date.'
				])->withInput();
			}

			$this->logService->logChanges($validatedData['requestid'], (array)$oldData, $validatedData);
			
			DB::table('eoi_request')
			->where('requestid',$validatedData['requestid'])
			->update([
				'releasedate'	=>	date('Y\-m\-d',strtotime($validatedData['releasedate'])),
				'prebidlastdate'=>	date('Y\-m\-d',strtotime($validatedData['prebidlastdate'])),
				'deadlinedate'	=>	date('Y\-m\-d H:i:s',strtotime($validatedData['deadlinedate'])),
				'interviewdate'	=>	!empty($validatedData['interviewdate']) ? date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate'])) : null,
			]);
	
			return back()->with('success','Fact sheet dates have been updated successfully.');
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return back()->with('duplicate','Something went wrong, please try again after some time.')->withInput();
		}
    }

    public function exportEoiData(Request $request)
	{
		$categoryid 	=	$request->input('categoryid');
		$projectid 		=	$request->input('projectid');
		$deptid 		=	$request->input('departmentid');
		$eoi_status 	=	$request->input('eoi_status') ?? '';
		
		$managerid 		=	$request->input('managerid');

		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$is_cancelled	= 	($request->input('is_cancelled')==='true') ? 1 : 0;
		$is_closed		= 	($request->input('is_closed')==='true') ? 1 : 0;
		
		$record_type	=	$request->input('record_type');

		$pagesearch =	$request->input('pagesearch');
		if($categoryid==1)
		{
			$fileName = 'awd_eoi_data_' . date('Ymd_His') . '.csv';
		}
		else if($categoryid==2)
		{
			$fileName = 'csf_eoi_data_' . date('Ymd_His') . '.csv';
		}
		else
		{
			$fileName = 'eoi_data_' . date('Ymd_His') . '.csv';
		}
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

		$callback = function () use ($deptid,$eoi_status,$record_type,$pagesearch,$categoryid,$in_house,$managerid,$is_cancelled,$is_closed,$projectid) {

			$handle = fopen('php://output', 'w');

			fputcsv($handle, [
				'S.No','Project Name',
				'Department / Project Manager Name','Project Title','EoI','Release Date','Last Date of Pre-bid query','Last Date of Submission Date','Tentative Interview Date','EoI Status'
			]);

			$sno = 1;
			
			DB::table('eoi_request as a')
				->select(
					'c.project_name','b.departmentname','a.projecttitle','a.eoinumber','a.releasedate',
					'a.prebidlastdate','a.deadlinedate','a.interviewdate','a.eoistatus',
					'a.isinprebid','a.isordered','a.iscancelled','a.isClosed'
				)
				->leftJoin('department_tbl as b','b.userid','=','a.userid')
				->leftJoin('project_tbl as c','c.projectid','=','a.projectid')
				->when($in_house==1, function ($query) {
					$query->join('users_tbl as u', function ($join) {
						$join->on('u.userid','=','b.userid')
							 ->where('u.ispm','=',1)
							 ->where('a.categoryid',2);
					});
				})
				->when($pagesearch!='', function ($query) use ($pagesearch) {
					$query->where(function ($q) use ($pagesearch) {
						$q->where('a.projecttitle','like',"%$pagesearch%")
						  ->orWhere('a.eoinumber','like',"%$pagesearch%");
					});
				})
				->where('a.iscancelled',$is_cancelled)
				->where('a.isClosed',$is_closed)
				->when($categoryid != 0, fn($q) => $q->where('a.categoryid',$categoryid))
				->when($deptid != 0, fn($q) => $q->where('b.userid',$deptid))
				->when($managerid != 0, fn($q) => $q->where('b.userid',$managerid))
				->when($eoi_status==0 || $eoi_status==1 || $eoi_status==2 || $eoi_status==3,function($query) use ($eoi_status){
					return $query->where('a.eoistatus','=',$eoi_status);
				})
				->when($eoi_status == 34, function ($query) {
					return $query->where('a.eoistatus', 4)
								 ->where('a.isordered', 0);
				})
				->when($eoi_status == 43, function ($query) {
					return $query->where('a.eoistatus', 3)
								 ->where('a.deadlinedate', '<', now())
								 ->whereNotExists(function ($subquery) {
									 $subquery->select(DB::raw(1))
											  ->from('eoi_request_participation as p')
											  ->whereRaw('p.requestid = a.requestid');
								 });
				})		
				->when($eoi_status == 4, function ($query) {
					return $query->where('a.eoistatus', 4)
								 ->where('a.isordered', '=',1);
				})							
				->when($eoi_status == 5, function ($query) {
					return $query->where('a.eoistatus', 4)
								 ->where('a.isordered', '=',0)
								 ->whereExists(function ($subquery) {
									 $subquery->select(DB::raw(1))
											  ->from('eoi_request_participation as p')
											  ->whereColumn('p.requestid', 'a.requestid')
											  ->where('p.interviewlink','!=','');
								 });
				})							
				->when($eoi_status==6, function ($query)
				{
					return $query->where('a.eoistatus',3)
								 ->where('a.isinprebid',1)
								 ->whereDate('a.deadlinedate','>=',date('Y\-m\-d'));
				})
				->when($projectid>0,function($query) use ($projectid){
					return $query->where('a.projectid','=',$projectid);
				})
				->orderBy('a.requestid')
				->chunk(500, function ($rows) use ($handle, &$sno) {
					foreach($rows as $row)
					{
						if($row->eoistatus==0)
						{
							$row->eoistatus='New Request';
						}
						elseif($row->eoistatus==1)
						{
							$row->eoistatus='Drafted';
						}
						elseif($row->eoistatus== 2)
						{
							$row->eoistatus='Approved';
						}
						elseif($row->eoistatus==3 && Carbon::parse($row->prebidlastdate)->lte(Carbon::now()))
						{
							$row->eoistatus = 'Published';
							if($row->isinprebid==1)
							{
								$row->eoistatus = 'In Pre-bid';
							}
						}
						elseif($row->eoistatus==3 && Carbon::parse($row->prebidlastdate)->gt(Carbon::now()))
						{
							$row->eoistatus = 'Published';
							if($row->isinprebid==1)
							{
								$row->eoistatus = 'Pre-bid query period ended';
							}
						}
						elseif($row->eoistatus==3 && Carbon::parse($row->deadlinedate)->gt(Carbon::now()))
						{
							$row->eoistatus = 'No-Participation';
						}
						elseif($row->eoistatus==4 && Carbon::parse($row->deadlinedate)->gt(Carbon::now()))
						{
							$row->eoistatus = 'Participated';
						}
						elseif($row->eoistatus==4 && Carbon::parse($row->deadlinedate)->lt(Carbon::now()) && $row->isordered==0)
						{
							$row->eoistatus = 'Submission date passed';
						}
						elseif($row->eoistatus==4 && $row->isordered==1)
						{
							$row->eoistatus = 'Work Order Released';
						}
						else
						{
							$row->eoistatus = '';
						}
						if($row->iscancelled==1)
						{
							$row->eoistatus = 'EoI Cancelled';
						}
						if($row->isClosed==1)
						{
							$row->eoistatus = 'EoI Closed';
						}
						unset($row->iscancelled);
						unset($row->isClosed);
						unset($row->isinprebid);
						unset($row->isordered);
						fputcsv($handle, array_merge([$sno++], (array) $row));
					}
				});

			fclose($handle);
		};
        return response()->stream($callback, 200, $headers);
    }


	public function createFinalWorkOrder(Request $request)
	{
		$rules = [
			'requestid'     	=>	'required',
			'vendorid'     		=>	'required',
			'rateid'     		=>	'required|numeric',
			'ordernumber'		=>	'required',
			'refrence'			=>	'required',
			'subject'			=>	'required',
			'signedby'			=>	'required|in:Chief Executive Officer (CEO),Jt. CEO (Project),Jt. CEO(Finance),Add. Chief Executive Officer,Chief Operating Officer',
			'momfile' 			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'markingsheet'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'notesheet'			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'order_date' 		=> 	'required|date_format:d-m-Y',
			'order_duedate' 	=> 	'required|date_format:d-m-Y',
		];

		
        $messages = [
			'requestid.required'		=>	'Invalid eoi detail',
			'rateid.required'			=>	'Rate list name is mandatory',
			'rateid.numeric'			=>	'Invalid rate list value provided',
			'vendorid.required'			=>	'Invalid vendorid',
			'ordernumber.required'		=>	'Order number is required',
			'refrence.required'			=>	'Refrence number is required',
			'subject.required'			=>	'Subject is required',
			'signedby.required'			=>	'Signed by name is required',
			'momfile.required'			=>	'MoM File is required',
			'momfile.file'				=>	'MoM must be a file',
			'momfile.mimetypes'			=>	'Invalid MoM file attached',
			'momfile.max'				=>	'Maximum file size is 2 MB',
			'markingsheet.required'		=>	'Marking sheet is required',
			'markingsheet.file'			=>	'Marking sheet must be a file',
			'markingsheet.mimetypes'	=>	'Invalid marking sheet file attached',
			'markingsheet.max'			=>	'Maximum file size is 2 MB',
			'notesheet.required'		=>	'Note sheet is required',
			'notesheet.file'			=>	'Note sheet must be a file',
			'notesheet.mimetypes'		=>	'Invalid note sheet file attached',
			'notesheet.max'				=>	'Maximum file size is 2 MB',
			'signedcopy.required'		=>	'Order signed copy is required',
			'signedcopy.file'			=>	'Order signed copy must be a file',
			'signedcopy.mimetypes'		=>	'Invalid Order signed copy file attached',
			'signedcopy.max'			=>	'Maximum file size is 2 MB',
			'order_date.required'		=>	'Order date is required',
			'order_date.date_format'	=>	'Invalid order date provided',
			'order_date.before_or_equal'=>	'Invalid order date provided',

			'order_duedate.required'		=>	'Order due date is required',
			'order_duedate.date_format'		=>	'Invalid order due date provided',
			'order_duedate.before_or_equal'	=>	'Invalid order due date provided',
        ];

        $validatedData 	=	$request->validate($rules,$messages);
		$requestid		=	Crypt::decrypt($validatedData['requestid']);
		$eoi			=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		$orderDate 		= 	\Carbon\Carbon::createFromFormat('d-m-Y', $request->order_date);
		$orderDueDate 	= 	\Carbon\Carbon::createFromFormat('d-m-Y', $request->order_duedate);
		$days 			= 	$orderDate->diffInDays($orderDueDate);
		$maxDuration 	= 	round($days / 30, 1);

		foreach($request->recordids as $i => $id)
		{
			if($eoi->categoryid==2)
			{
				$startRaw = $request->startDate[$i] ?? null;
				$endRaw   = $request->endDate[$i] ?? null;

				if (!$id || !$startRaw || !$endRaw) {
					continue;
				}

				try
				{
					$start = \Carbon\Carbon::createFromFormat('d-m-Y', $startRaw);
					$end   = \Carbon\Carbon::createFromFormat('d-m-Y', $endRaw);
				}
				catch (\Exception $e)
				{
					return response()->json([
						'status' => false,
						'errors' => [
							"endDate.$i" => ["Invalid date format"]
						]
					], 422);
				}

				if ($end->lte($start)) {
					return response()->json([
						'status' => false,
						'errors' => [
							"endDate.$i" => ["End date must be greater than start date"]
						]
					], 422);
				}
			}
			if($eoi->categoryid == 1)
			{
				$nameRaw  = trim($request->resource_name[$i] ?? '');
				$levelRaw = trim($request->exp_level[$i] ?? '');
				$startRaw = trim($request->startDate[$i] ?? '');
				$endRaw   = trim($request->endDate[$i] ?? '');

				// Check if any one field is filled
				$hasAnyValue = $nameRaw || $levelRaw || $startRaw || $endRaw;

				// If any field is entered, then all fields are required
				if ($hasAnyValue) {

					$errors = [];

					if (!$id) {
						$errors["id.$i"] = ["ID is required"];
					}

					if (!$nameRaw) {
						$errors["resource_name.$i"] = ["Resource name is required for the selected record."];
					}

					if (!$levelRaw) {
						$errors["exp_level.$i"] = ["Experience level is required for the selected record"];
					}

					if (!$startRaw) {
						$errors["startDate.$i"] = ["Start date is required for the selected record"];
					}

					if (!$endRaw) {
						$errors["endDate.$i"] = ["End date is required for the selected record"];
					}

					// Return validation errors
					if (!empty($errors)) {
						return response()->json([
							'status' => false,
							'errors' => $errors
						], 422);
					}

					try
					{
						$start = \Carbon\Carbon::createFromFormat('d-m-Y', $startRaw);
						$end   = \Carbon\Carbon::createFromFormat('d-m-Y', $endRaw);
					}
					catch (\Exception $e)
					{
						return response()->json([
							'status' => false,
							'errors' => [
								"endDate.$i" => ["Invalid date format"]
							]
						], 422);
					}

					if ($end->lte($start)) {
						return response()->json([
							'status' => false,
							'errors' => [
								"endDate.$i" => ["End date must be greater than start date"]
							]
						], 422);
					}
				}
			}
		}
		
	
		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		
		
		
		
		if(!$eoi)
		{
			return response()->json([
				'errors' => [
					'eoi' => ['Invalid EoI detail provided.'],
					]
				], 422);			
		}
		if($eoi->categoryid==2)
		{
			$rows = collect($request->sectorids)
				->map(function ($sector, $i) use ($request) {

					return [
						'sector'   	=> trim((string)$sector),
						'position' 	=> trim((string)($request->positionids[$i] ?? '')),
						'deployment'=> trim((string)($request->deploymenttypes[$i] ?? '')),
						'start'    	=> trim((string)($request->startDate[$i] ?? '')),
						'end'      	=> trim((string)($request->endDate[$i] ?? '')),
						'index'    	=> $i
					];
				})
				->filter(function ($row) {
					return $row['sector'] !== '' ||
						   $row['position'] !== '' ||
						   $row['deployment'] !== '' ||
						   $row['start'] !== '' ||
						   $row['end'] !== '';
				})
				->values();			

			foreach ($rows as $row) 
			{

				$i	= $row['index'];

				$id          	= 	$row['sector'];
				$positionRaw 	= 	$row['position'];
				$deploymentRaw	= 	$row['deployment'];
				$startRaw    	= 	$row['start'];
				$endRaw      	= 	$row['end'];

				if ($id === '' || $positionRaw === '' || $deploymentRaw === '' || $startRaw === '' || $endRaw === '') {
					return response()->json([
						'status' => false,
						'errors' => [
							"sectorids.$i" => [
								"Please fill all fields OR remove the row."
							]
						]
					], 422);
				}

				try {
					$start = \Carbon\Carbon::createFromFormat('d-m-Y', $startRaw);
					$end   = \Carbon\Carbon::createFromFormat('d-m-Y', $endRaw);
				} catch (\Exception $e) {
					return response()->json([
						'status' => false,
						'errors' => [
							"endDate.$i" => ["Invalid date format"]
						]
					], 422);
				}

				if ($end->lte($start)) {
					return response()->json([
						'status' => false,
						'errors' => [
							"endDate.$i" => ["End date must be greater than start date"]
						]
					], 422);
				}

				$existSector = DB::table('vendor_sector')
					->where('vendorid', $validatedData['vendorid'])
					->where('sectorid', $id)
					->exists();

				if (!$existSector) {
					return response()->json([
						'status' => false,
						'errors' => [
							"sectorids.$i" => ["Selected sector is not mapped with the selected vendor"]
						]
					], 422);
				}
			}
		}
		if($eoi->categoryid==1)
		{
			$rows = collect($request->new_roles ?? [])
				->map(function ($role, $i) use ($request) {

					return [
						'role' => trim((string)$role),
						'experience' => trim((string)($request->new_experience[$i] ?? '')),
						'resource_name' => trim((string)($request->new_resource_name[$i] ?? '')),
						'experiencelevel' => trim((string)($request->new_exp_level[$i] ?? '')),
						'deployment' => trim((string)($request->new_deploymenttype[$i] ?? '')),
						'start' => trim((string)($request->new_startDate[$i] ?? '')),
						'end' => trim((string)($request->new_endDate[$i] ?? '')),
						'index' => $i
					];
				})
				->filter(function ($row) {
					return
						filled($row['role']) ||
						filled($row['experience']) ||
						filled($row['resource_name']) ||
						filled($row['experiencelevel']) ||
						filled($row['deployment']) ||
						filled($row['start']) ||
						filled($row['end']);
				})
				->values();

			foreach ($rows as $row)
			{
				$i = $row['index'];
				if(blank($row['role']) || blank($row['experience']) || blank($row['resource_name']) || blank($row['experiencelevel']) || blank($row['deployment']) || blank($row['start']) || blank($row['end']))
				{

					return response()->json([
						'status' => false,
						'errors' => [
							"new_roles.$i" => [
								"Please fill all fields OR remove the row."
							]
						]
					], 422);
				}
			}
		}
		
		
		$momfile		= 	$request->file('momfile');		
		$markingsheet	= 	$request->file('markingsheet');
		$notesheet		= 	$request->file('notesheet');
		$signedcopy		= 	$request->file('signedcopy');
		
		if($eoi->categoryid==2)
		{
			$selectedIds 	= 	$request->recordids ?? [];
			$startDates 	= 	$request->startDate ?? [];
			$endDates   	= 	$request->endDate ?? [];

			$dataMap  		= 	[];

			foreach($selectedIds as $i=>$id)
			{
				$dataMap[$id] = [
					'startDate'	=> 	$startDates[$i] ?? null,
					'endDate'	=> 	$endDates[$i] ?? null,
				];
			}
		}
		if($eoi->categoryid==1)
		{
			$selectedIds 	= 	$request->recordids ?? [];
			$startDates 	= 	$request->startDate ?? [];
			$endDates   	= 	$request->endDate ?? [];
			$resourceNames 	= 	$request->resource_name ?? [];
			$expLevels 		= 	$request->exp_level ?? [];

			$dataMap  		= 	[];

			foreach($selectedIds as $i=>$id)
			{
				$dataMap[$id] = [
					'startDate'		=> 	$startDates[$i] ?? null,
					'endDate'		=> 	$endDates[$i] ?? null,
					'resource_name'	=> 	$resourceNames[$i] ?? null,
					'exp_level'		=> 	$expLevels[$i] ?? null,
				];
			}
			
		}
		try
		{
			
			DB::beginTransaction();
			if($request->hasFile('momfile'))
			{
				$momfile= $request->file('momfile')->store('uploads/momfiles','public');
			}
			if($request->hasFile('markingsheet'))
			{
				$markingsheet= $request->file('markingsheet')->store('uploads/markingsheets','public');
			}
			if($request->hasFile('notesheet'))
			{
				$notesheet	= $request->file('notesheet')->store('uploads/notesheets','public');
			}
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	= $request->file('signedcopy')->store('uploads/signedorder','public');
			}
			

			$vendor	=	DB::table('vendor_tbl')->where('vendorid',$validatedData['vendorid'])->first();
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$eoi->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
			
			$copyto	=	$vendor->companyname.", ".$vendor->officelocation;
		
			
			if($eoi->categoryid==2)
			{
				$records		=	DB::table('eoi_request_detail as a')
									->select(
										'a.recordid','a.qualification','a.duration','e.remunerationid','e.remuneration','a.admincharge','a.total','a.role','a.experience','a.remark','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition','a.employmenttype')
									->leftJoin('remuneration_tbl as e', function ($join) {
										$join->on('e.sectorid', '=', 'a.sectorid')
											 ->on('e.positionid', '=', 'a.positionid');
									})
									->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
									->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
									->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
									->where('e.tierid', $vendor->tierid)
									->where('e.rateid', $validatedData['rateid'])
									->where('a.requestid','=',$requestid)
									->where('a.isordered',0)
									->get();
			}
			if($eoi->categoryid==1)
			{
				$orderRecords	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.qualification','a.duration','e.remunerationid','e.remuneration','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype')
									->leftJoin('remuneration_tbl as e', function ($join) use ($eoi,$vendor) {
										$join->on('e.experiencelevel','=','a.experiencelevel')
											 ->where('e.categoryid','=',$eoi->categoryid)
											 ->where('e.tierid','=',$vendor->tierid);
									})
									->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
									->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
									->where('a.requestid','=',$requestid)
									->where('e.rateid',$validatedData['rateid'])
									->where('a.isordered',0)
									->get();
			}

			$nextReminderDate 	= 	Carbon::parse($validatedData['order_date'])->addMonths($maxDuration-2)->format('Y-m-d');
			$department_id		=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('departmentid');
			
			$orderid=	DB::table('eoi_work_order')->insertGetId([
							'requestid'			=>	$eoi->requestid,
							'categoryid'		=>	$vendor->categoryid,
							'ordernumber'		=>	$validatedData['ordernumber'] ?? NULL,
							'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['order_date'])),
							'refrence'			=>	$eoi->eoinumber ?? NULL,
							'subject'			=>	$validatedData['subject'] ?? NULL,
							'vendorid'			=>	$vendor->vendorid,	
							'tierid'			=>	$vendor->tierid,	
							'creationdate'		=>	now(),
							'createdby'			=>	$userId,
							'operatingmargin'	=>	$pricing->operatingmargin,
							'tax'				=>	$pricing->tax,
							'admincharge'		=>	$pricing->admincharge,
							'momfile'			=>	$momfile,
							'markingsheet'		=>	$markingsheet,
							'notesheet'			=>	$notesheet,
							'signedcopy'		=>	$signedcopy,
							'signedby'			=>	$validatedData['signedby'],
							'loinumber'			=>	$vendor->loinumber ?? NULL,
							'copyto'			=>	$copyto ?? NULL,
							'workorderduedate'	=>	date('Y\-m\-d',strtotime($validatedData['order_duedate'])),
							'nextreminderdate'	=>	$nextReminderDate,
							'isfinalized'		=>	1,
							'finalizedby'		=>	$userName,
							'userid'			=>	$eoi->userid,
							'project_duration'	=>	$maxDuration,
							'project_name'		=>	$eoi->projecttitle,
							'department_id'		=>	$department_id,
						]);
			

			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			if($eoi->categoryid==2)
			{
				foreach($records as $record)
				{
					if(!isset($dataMap[$record->recordid]))
					{
						continue;
					}

					$input 		=	$dataMap[$record->recordid];
					$startDate 	=	$input['startDate'];
					$endDate   	=	$input['endDate'];				

					$start 		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 		= 	$start->diffInDays($end);
					$duration 	= 	round($days / 30, 1);
					//$duration 	= 	$start->diffInMonths($end);
					
					$r++;
					$budget				=	0;
					$remuneration		=	round($record->remuneration*$duration,2);
					$totalremuneration	=	$totalremuneration+$remuneration;
					$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
					$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
					$budget				=	$remuneration+$operatingvalue+$taxvalue;
					$totalbudget		=	$totalbudget+$budget;
					$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
					$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);

					DB::table('eoi_request_detail')
					->where('recordid',$record->recordid)
					->update([
						'remunerationid'	=>	$record->remunerationid,
						'remuneration'		=>	$record->remuneration,
						'budget'			=>	$budget,
						'operating'			=>	$pricing->operatingmargin,
						'operatingvalue'	=>	$operatingvalue,
						'tax'				=>	$pricing->tax,
						'taxvalue'			=>	$taxvalue,
						'admin'				=>	$pricing->admincharge,
						'admincharge'		=>	$admincharge,
						'grandtotal'		=>	$grandtotal,
						'tierid'			=>	$vendor->tierid,
						'isordered'			=>	1
					]);
					
					if($eoi->categoryid==2)
					{
						$rates	=	DB::table('remuneration_tbl')
									->where('remunerationid',$record->remunerationid)
									->where('rateid',$validatedData['rateid'])
									->first();

						$user_id	=	DB::table('users_tbl')->insertGetId([
							'name'			=>	NULL,
							'mobilenumber'	=>	NULL,
							'email'			=>	NULL,
						]);
						DB::table('resource_tbl')->insert([
							'orderid'		=>	$orderid,
							'userid'		=>	$user_id ?? NULL,
							'categoryid'	=>	$vendor->categoryid,
							'tierid'		=>	$vendor->tierid,
							'sectorid'		=>	$rates->sectorid,
							'positionid'	=>	$rates->positionid,
							'remuneration'  => 	$record->remuneration,
							'operating'     => 	$pricing->operatingmargin,
							'operatingvalue'=> 	$operatingvalue,
							'tax'           => 	$pricing->tax,
							'taxvalue'      => 	$taxvalue,
							'admin'  	 	=> 	$pricing->admincharge,
							'admincharge'   => 	$admincharge,
							'rateid'			 => $validatedData['rateid'] ?? 0,
							'deployment_status'	=>	'Pending'
						]);
									
						$data = [
							'orderid'            => $orderid,
							'userid'			 => $user_id ?? 0,
							'recordid'           => $record->recordid,
							'deployment_status'  => 'Pending',
							'tierid'             => $vendor->tierid,
							'sectorid'           => $rates->sectorid,
							'positionid'         => $rates->positionid,
							'role'               => $record->role ?? null,
							'experience'         => $record->experience ?? null,
							'experiencelevel'    => $record->experiencelevel,
							'qualification'      => $record->qualification ?? null,
							'deploymenttype'     => $record->employmenttype ?? null,
							'duration'           => $duration,
							'remark'             => $record->remark ?? null,
							'remunerationid'     => $record->remunerationid,
							'remuneration'       => $record->remuneration,
							'baseprice'          => $record->remuneration,
							'operating'          => $pricing->operatingmargin,
							'operatingvalue'     => $operatingvalue,
							'tax'                => $pricing->tax,
							'taxvalue'           => $taxvalue,
							'admincharge'        => $pricing->admincharge,
							'adminvalue'         => $admincharge,
							'grandtotal'         => $grandtotal,
							'deployment_date'    => date('Y-m-d', strtotime($validatedData['order_date'])),
							'rateid'			 => $validatedData['rateid'] ?? 0,
							'startDate'			 => date('Y\-m\-d',strtotime($startDate)) ?? NULL,
							'endDate'			 => date('Y\-m\-d',strtotime($endDate)) ?? NULL,
						];

						DB::table('eoi_resource_deployment')->insert($data);				
					}
				}
			}
			if($eoi->categoryid==2)
			{
				foreach(($request->sectorids ?? []) as $i => $id)
				{
					$sectorid 	= 	$request->sectorids[$i];
					$positionid = 	$request->positionids[$i];
					$deployment = 	$request->deploymenttypes[$i];
					$startDate 	= 	$request->startDate[$i];
					$endDate 	= 	$request->endDate[$i];
					$price		=	DB::table('remuneration_tbl')
									->where('sectorid',$sectorid)
									->where('positionid',$positionid)
									->where('categoryid',$eoi->categoryid)
									->where('tierid',$vendor->tierid)
									->where('rateid',$validatedData['rateid'])
									->first();

					$start 		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 		= 	$start->diffInDays($end);
					$duration 	= 	round($days / 30, 1);

					//$duration 	= 	$start->diffInMonths($end);
					
					$r++;
					$remuneration		=	round($price->remuneration*$duration,2);
					$totalremuneration	=	$totalremuneration+$remuneration;
					$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
					$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
					$budget				=	$remuneration+$operatingvalue+$taxvalue;
					$totalbudget		=	$totalbudget+$budget;
					$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
					$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);

					$user_id	=	DB::table('users_tbl')->insertGetId([
						'name'			=>	NULL,
						'mobilenumber'	=>	NULL,
						'email'			=>	NULL,
					]);
					DB::table('resource_tbl')->insert([
						'orderid'		=>	$orderid,
						'userid'		=>	$user_id ?? NULL,
						'categoryid'	=>	$vendor->categoryid,
						'tierid'		=>	$vendor->tierid,
						'sectorid'		=>	$price->sectorid,
						'positionid'	=>	$price->positionid,
						'remuneration'  => 	$price->remuneration,
						'operating'     => 	$pricing->operatingmargin,
						'operatingvalue'=> 	$operatingvalue,
						'tax'           => 	$pricing->tax,
						'taxvalue'      => 	$taxvalue,
						'admin'  	 	=> 	$pricing->admincharge,
						'admincharge'   => 	$admincharge,
						'rateid'		=> $validatedData['rateid'] ?? 0,
						'deployment_status'	=>	'Pending'
					]);

					$data = [
						'orderid'            => $orderid,
						'userid'			 => $user_id ?? 0,
						'recordid'           => 0,
						'deployment_status'  => 'Pending',
						'tierid'             => $vendor->tierid,
						'sectorid'           => $sectorid,
						'positionid'         => $positionid,
						'role'               => null,
						'experience'         => null,
						'experiencelevel'    => 0,
						'qualification'      => null,
						'deploymenttype'     => $deployment ?? NULL,
						'duration'           => $duration,
						'remark'             => null,
						'remunerationid'     => $price->remunerationid,
						'remuneration'       => $price->remuneration,
						'baseprice'          => $price->remuneration,
						'operating'          => $pricing->operatingmargin,
						'operatingvalue'     => $operatingvalue,
						'tax'                => $pricing->tax,
						'taxvalue'           => $taxvalue,
						'admincharge'        => $pricing->admincharge,
						'adminvalue'         => $admincharge,
						'grandtotal'         => $grandtotal,
						'deployment_date'    => date('Y-m-d', strtotime($validatedData['order_date'])),
						'rateid'			 => $validatedData['rateid'] ?? 0,
						'startDate'			 => date('Y\-m\-d',strtotime($startDate)) ?? NULL,
						'endDate'			 => date('Y\-m\-d',strtotime($endDate)) ?? NULL,
					];

					DB::table('eoi_resource_deployment')->insert($data);				

				}
			}
			if($eoi->categoryid==1)
			{
				$r=0;
				$totalremuneration	=	0;
				$totalbudget		=	0;
				foreach($orderRecords as $record)
				{
					if(!isset($dataMap[$record->recordid]))
					{
						continue;
					}

					$input 			=	$dataMap[$record->recordid];
					$startDate 		=	$input['startDate'];
					$endDate   		=	$input['endDate'];
					$resourceName	=	$input['resource_name'];
					$expLevel		=	$input['exp_level'];

					$start 			= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   			= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 			= 	$start->diffInDays($end);
					$duration 		= 	round($days / 30, 1);
					//$duration 		= 	$start->diffInMonths($end);

					$rates				=	DB::table('remuneration_tbl')
											->where('categoryid',$eoi->categoryid)
											->where('tierid',$vendor->tierid)
											->where('rateid',$validatedData['rateid'])
											->where('experiencelevel',$expLevel)
											->first();

					
					$r++;
					$budget				=	0;
					$remuneration		=	round($rates->remuneration*$duration,2);
					$totalremuneration	=	$totalremuneration+$remuneration;
					$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
					$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
					$budget				=	$remuneration+$operatingvalue+$taxvalue;
					$totalbudget		=	$totalbudget+$budget;
					$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
					$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);


					DB::table('eoi_request_detail')
					->where('recordid',$record->recordid)
					->update([
						'remunerationid'	=>	$rates->remunerationid,
						'remuneration'		=>	$rates->remuneration,
						'budget'			=>	$budget,
						'operating'			=>	$pricing->operatingmargin,
						'operatingvalue'	=>	$operatingvalue,
						'tax'				=>	$pricing->tax,
						'taxvalue'			=>	$taxvalue,
						'admin'				=>	$pricing->admincharge,
						'admincharge'		=>	$admincharge,
						'grandtotal'		=>	$grandtotal,
						'tierid'			=>	$vendor->tierid,
						'isordered'			=>	1
					]);

					$user_id	=	DB::table('users_tbl')->insertGetId([
						'name'			=>	NULL,
						'mobilenumber'	=>	NULL,
						'email'			=>	NULL,
					]);
					DB::table('resource_tbl')->insert([
						'orderid'		=>	$orderid,
						'userid'		=>	$user_id ?? NULL,
						'categoryid'	=>	$vendor->categoryid,
						'tierid'		=>	$vendor->tierid,
						'experiencelevel'=>	$expLevel,
						'remuneration'  => 	$rates->remuneration,
						'operating'     => 	$pricing->operatingmargin,
						'operatingvalue'=> 	$operatingvalue,
						'tax'           => 	$pricing->tax,
						'taxvalue'      => 	$taxvalue,
						'admin'  	 	=> 	$pricing->admincharge,
						'admincharge'   => 	$admincharge,
						'rateid'		=> 	$validatedData['rateid'] ?? 0,
						'deployment_status'	=>	'Pending'
					]);
					
					$data = [
						'orderid'           => 	$orderid,
						'userid'           => 	$user_id ?? 0,
						'recordid'          => 	$record->recordid,
						'deployment_status' => 	'Pending',
						'tierid'            => 	$vendor->tierid,
						'sectorid'          => 	$rates->sectorid,
						'positionid'        => 	$rates->positionid,
						'role'              => 	$record->role ?? null,
						'experience'        => 	$record->experience ?? null,
						'experiencelevel'   => 	$expLevel,
						'qualification'     => 	$record->qualification ?? null,
						'deploymenttype'    => 	$record->employmenttype ?? null,
						'duration'          => 	$duration,
						'remark'            => 	$record->remark ?? null,
						'remunerationid'    => 	$rates->remunerationid,
						'remuneration'      => 	$rates->remuneration,
						'baseprice'         => 	$rates->remuneration,
						'operating'         => 	$pricing->operatingmargin,
						'operatingvalue'    => 	$operatingvalue,
						'tax'               => 	$pricing->tax,
						'taxvalue'          => 	$taxvalue,
						'admincharge'       => 	$pricing->admincharge,
						'adminvalue'        => 	$admincharge,
						'grandtotal'        => 	$grandtotal,
						'deployment_date'   => 	date('Y-m-d', strtotime($validatedData['order_date'])),
						'name'         		=> 	$resourceName ?? NULL,
						'rateid'			=> 	$validatedData['rateid'] ?? 0,
						'startDate'			=> 	date('Y\-m\-d',strtotime($startDate)) ?? NULL,
						'endDate'			=> 	date('Y\-m\-d',strtotime($endDate)) ?? NULL,
						
					];
					//DB::table('eoi_resource_deployment')->insert($data);
					
					DB::table('eoi_resource_deployment')->insertGetId($data);
				}
				foreach(($request->new_roles ?? []) as $i => $role)
				{
					$role 			= 	$request->new_roles[$i] ?? NULL;
					$resourceName 	= 	$request->new_resource_name[$i] ?? null;
					$experience   	= 	$request->new_experience[$i] ?? null;
					$expLevel     	= 	$request->new_exp_level[$i] ?? null;
					$deployment    	= 	$request->new_deploymenttype[$i] ?? null;
					$startDate    	= 	$request->new_startDate[$i] ?? null;
					$endDate      	= 	$request->new_endDate[$i] ?? null;
					$price			=	DB::table('remuneration_tbl')
										->where('experiencelevel',$expLevel)
										->where('categoryid',$eoi->categoryid)
										->where('tierid',$vendor->tierid)
										->where('rateid',$validatedData['rateid'])
										->first();

					$start 		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 		= 	$start->diffInDays($end);
					$duration 	= 	round($days / 30, 1);
					//$duration 	= 	$start->diffInMonths($end);
					
					$r++;
					$remuneration		=	round($price->remuneration*$duration,2);
					$totalremuneration	=	$totalremuneration+$remuneration;
					$operatingvalue		=	round(($remuneration*$pricing->operatingmargin)/100,2);
					$taxvalue			=	round((($remuneration+$operatingvalue)*$pricing->tax)/100,2);
					$budget				=	$remuneration+$operatingvalue+$taxvalue;
					$totalbudget		=	$totalbudget+$budget;
					$admincharge		=	round((($remuneration+$operatingvalue+$taxvalue)*$pricing->admincharge)/100,2);
					$grandtotal			=	round($remuneration+$operatingvalue+$taxvalue+$admincharge,2);

					$user_id	=	DB::table('users_tbl')->insertGetId([
						'name'			=>	NULL,
						'mobilenumber'	=>	NULL,
						'email'			=>	NULL,
					]);
					DB::table('resource_tbl')->insert([
						'orderid'		=>	$orderid,
						'userid'		=>	$user_id ?? NULL,
						'categoryid'	=>	$vendor->categoryid,
						'tierid'		=>	$vendor->tierid,
						'experiencelevel'=>	$expLevel,
						'remuneration'  => 	$price->remuneration,
						'operating'     => 	$pricing->operatingmargin,
						'operatingvalue'=> 	$operatingvalue,
						'tax'           => 	$pricing->tax,
						'taxvalue'      => 	$taxvalue,
						'admin'  	 	=> 	$pricing->admincharge,
						'admincharge'   => 	$admincharge,
						'rateid'		=> 	$validatedData['rateid'] ?? 0,
						'deployment_status'	=>	'Pending'
					]);


					$data = [
						'orderid'            => $orderid,
						'userid'			 => $user_id ?? 0,
						'recordid'           => 0,
						'deployment_status'  => 'Pending',
						'tierid'             => $vendor->tierid,
						'role'               => $role ?? NULL,
						'experience'         => $experience ?? NULL,
						'experiencelevel'    => $expLevel,
						'qualification'      => null,
						'deploymenttype'     => $deployment ?? NULL,
						'duration'           => $duration,
						'remark'             => null,
						'remunerationid'     => $price->remunerationid,
						'remuneration'       => $price->remuneration,
						'baseprice'          => $price->remuneration,
						'operating'          => $pricing->operatingmargin,
						'operatingvalue'     => $operatingvalue,
						'tax'                => $pricing->tax,
						'taxvalue'           => $taxvalue,
						'admincharge'        => $pricing->admincharge,
						'adminvalue'         => $admincharge,
						'grandtotal'         => $grandtotal,
						'deployment_date'    => date('Y-m-d', strtotime($validatedData['order_date'])),
						'name'         		 => $resourceName ?? NULL,
						'rateid'			 => $validatedData['rateid'] ?? 0,
						'startDate'			 => date('Y\-m\-d',strtotime($startDate)) ?? NULL,
						'endDate'			 => date('Y\-m\-d',strtotime($endDate)) ?? NULL,
					];

					DB::table('eoi_resource_deployment')->insert($data);				

				}
				
			}
			DB::table('eoi_request')
			->where('requestid',$eoi->requestid)
			->update([
				'isordered'			=>	1,
				'operatingmargin'	=>	$pricing->operatingmargin,
				'tax'				=>	$pricing->tax,
				'admincharge'		=>	$pricing->admincharge,
				'eoistatus'			=>	4
			]);
			
			$totaladmincharge	=	round((($totalbudget*$pricing->admincharge)/100),2);
			$workorderamount	=	$totalbudget+$totaladmincharge;
			DB::table('eoi_work_order')
				->where('orderid',$orderid)
				->update([
					'totalremuneration'		=>	$totalremuneration,
					'totalbudget'			=>	$totalbudget,
					'totaladmincharge'		=>	$totaladmincharge,
					'workorderamount'		=>	$workorderamount,
				]);
			
			DB::commit();
			
			if($orderid)
			{
				return response()->json([
					'status'		=>	200,
					'redirect_url'	=>	url('/master/orderconfirmation/'.$orderid)
				]);			
				
			}
			else
			{
				return response()->json([
					'errors'	=>	[
						'error'	=>	['Order could not be generated. Please try again.'],
						]
					], 422);			
				
			}
			
		}
		catch(QueryException $e)
		{		
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());

			if($momfile!='')
			Storage::disk('public')->delete($momfile);
			if($markingsheet!='')
			Storage::disk('public')->delete($markingsheet);
			if($notesheet!='')
			Storage::disk('public')->delete($notesheet);
			if($signedcopy!='')
			Storage::disk('public')->delete($signedcopy);
			
			return response()->json([
				'errors'	=>	[
					'error'	=>	[''.$e->getMessage()],
					]
				], 422);			
			
		}
    }

	public function confirmFinalOrder($orderid)
	{
		$order 	= 	DB::table('eoi_work_order')->where('orderid', $orderid)->first();
		$eoi	=	DB::table('eoi_request as a')
					->select('a.*','b.address','b.departmentname')
					->leftjoin('department_tbl as b','b.userid','a.userid')
					->where('a.requestid',$order->requestid)
					->first();
		$rateid	=	DB::table('eoi_request_detail')->where('requestid',$order->requestid)->value('rateid');
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
		$detail = 	[];
		
		if($eoi->categoryid==2)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->whereColumn('e.positionid', '=', 'a.positionid')
									 ->whereColumn('e.tierid', '=', 'a.tierid');
							})
							->where('e.rateid',$rateid)
							->where('a.requestid', $order->requestid)
							->get();
		}
		if($eoi->categoryid==1)
		{
			$detail	=	DB::table('eoi_request_detail as a')
							->select('a.*','b.tiername','c.remuneration')
							->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as c', function ($join){
								$join->on('c.remunerationid', '=', 'a.remunerationid')
									 ->whereColumn('c.tierid','=','a.tierid');
							})
							->where('a.requestid', $order->requestid)
							->get();
			
		}
		$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
		$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
		$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
		$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		
		return view('admin/master/confirm_order', compact('order','detail','vendor','eoi'));
	}


    public function viewMoreEoIData(Request $request)
	{
	
		$requestid 	=	Crypt::decrypt($request->input('requestid'));

		$data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','d.jobcategory')
							->selectRaw("
								CASE 
									WHEN EXISTS (
										SELECT 1 
										FROM eoi_request_participation p 
										WHERE p.requestid = a.requestid
									) 
									THEN 1 ELSE 0 
								END as isParticipated
							")
							->selectRaw("
								(
									SELECT u.name 
									FROM users_tbl u
									WHERE u.userid = (
										SELECT ea.closedby 
										FROM eoi_approval ea 
										WHERE ea.requestid = a.requestid 
										  AND ea.closedby IS NOT NULL
										LIMIT 1
									)
								) as approvedBy
							")
							->selectRaw("
								(
									SELECT ew.orderdate
									FROM eoi_work_order ew
									WHERE ew.requestid = a.requestid
									ORDER BY ew.orderid ASC
									LIMIT 1
								) as orderdate
							")
							->selectRaw("
								CASE
									WHEN a.isordered = 1
									 AND EXISTS (
										SELECT 1
										FROM eoi_resource_deployment dr
										WHERE dr.orderid = (
											SELECT ew.orderid
											FROM eoi_work_order ew
											WHERE ew.requestid = a.requestid
											ORDER BY ew.orderid ASC
											LIMIT 1
										)
										AND dr.deployment_status IN ('Active', 'Extended')
									 )
									THEN 1
									ELSE 0
								END as isdeployed
							")							
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('a.requestid',$requestid)
							->first();
			
		$resources	=	DB::table('eoi_request_detail as a')
						->select('a.*','b.sectorname','c.consultantposition')
						->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
						->leftJoin('remuneration_tbl as d', function($join) use ($data) {
							$join->on('d.rateid', '=', 'a.rateid')
								 ->on('d.sectorid', '=', 'a.sectorid')
								 ->on('d.positionid', '=', 'a.positionid')
								 ->on('d.experiencelevel', '=', 'a.experiencelevel')
								 ->where('d.categoryid', $data->categoryid)
								 ->where(function($q) {
									 $q->whereColumn('d.tierid','a.tierid')
									   ->orWhere('d.tierid',0);
								 });
						})
						->where('a.requestid', $data->requestid)
						->get();	


		$old_letter	=	DB::table('eoi_files_update')->where('requestid',$requestid)->get();
		$attachments=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
		$committee	=	DB::table('eoi_request_committee as a')
						->select('b.*')
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->get();
					
		$data->totalamount	=	$this->formatIndianCurrency($data->totalamount);

		$whois	=	DB::table('users_tbl as a')
					->select('a.name','a.ispm','a.isdepartment')
					->leftJoin('department_tbl as b','b.userid','=','a.userid')
					->where('a.userid',$data->userid)
					->first();
		if(!$whois)
		{
			$whois	=	[];
		}
		
		$published	=	DB::table('eoi_request_floated as a')
					->select('b.companyname','b.shortname')
					->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->where('a.requestid',$requestid)
					->get();

		$participated=	DB::table('eoi_request_floated as a')
					->select('b.companyname','b.shortname')
					->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->where('a.requestid',$requestid)
					->whereNotNull('a.signedcopyofeoi')
					->get();
		
		$projects	=	DB::table('project_tbl')->orderBy('project_name')->get();
		
		$html	=	view('admin.ajaxpages.viewMoreEoITable',[
						'data'			=>	$data,
						'resources'		=>	$resources,
						'old_letter'	=>	$old_letter,
						'attachments'	=>	$attachments,
						'committee'		=>	$committee,
						'whois'			=>	$whois,
						'published'		=>	$published,
						'participated'	=>	$participated,
						'projects'		=>	$projects
					])->render();
		
		return response()->json(['status'=>200,'message'=>'EoI Content.','data'=>$html]);
    }


    public function closeProject(Request $request,$requestid)
	{
		$userId    	= 	$request->session()->get('userId');
		$requestid 	=	Crypt::decrypt($requestid);
		
        $data 		= 	DB::table('eoi_request as a')
						->select('a.requestid','a.eoinumber','a.projecttitle','a.releasedate','a.prebidlastdate','a.deadlinedate','a.interviewdate','a.isordered','a.creationdate','a.projectduration','a.eoistatus','a.engagementname','b.name','a.isClosed')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('a.requestid','=',$requestid)
						->first();
		

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		
		return view('admin/master/close_project',compact('data','token'));
		
    }

    public function closeConfirmation(Request $request)
	{
		$request->merge([
			'requestid' => Crypt::decrypt($request->requestid),
		]);
		
        $rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'closedOn' 		=> 	['nullable', 'date_format:d-m-Y'],
			'closure_file' 	=> 	'nullable|file|mimes:pdf|max:5120',
        ];

		$messages = [
			'requestid.required'	=>	'EoI detail is required.',
			'requestid.exists'      =>	'The selected EoI detail is invalid.',

			'closedOn.required'		=>	'Project closing date is mandatory.',
			'closedOn.date_format'  =>	'Closing date is not valid.',

			'closure_file.required'	=> 	'Project closure file is required',
			'closure_file.file'		=> 	'Invalid closure file',
			'closure_file.mimes'	=> 	'Invalid closure file',
			'closure_file.max'		=> 	'Maximum 5 MB file is allowed',
		];
	
		$validatedData 	= $request->validate($rules,$messages);

		$userId	=	$request->session()->get('userId');
		try
		{
			$eoi	=	DB::table('eoi_request')
						->where('requestid',$validatedData['requestid'])
						->first();

			/*
			$havingActiveOrder	=	DB::table('eoi_work_order')
									->where('requestid',$validatedData['requestid'])
									->whereDate('workorderduedate','>=',today())
									->exists();

			if($havingActiveOrder)
			{
				return back()->withErrors([
					'activeorder'	=>	'This EOI already has an active work order. Please check and try again.'
				])->withInput();
				
			}
			*/
			if($validatedData['closedOn'])
			{
				$validatedData['closedOn']	=	date('Y\-m\-d',strtotime($validatedData['closedOn']));
			}
			$closure_file	=	(String) $request->file('closure_file');

			if($closure_file!='')
			{
				$closure_file= $request->file('closure_file')->store('uploads/closure_file', 'public');
			}		
			
			DB::transaction(function () use ($validatedData,$closure_file) {

				$orderIds = DB::table('eoi_work_order')
					->where('requestid', $validatedData['requestid'])
					->pluck('orderid')
					->toArray();

				DB::table('eoi_work_order')
					->where('requestid', $validatedData['requestid'])
					->update([
						'isActiveOrder' => 	0,
						'isClosed'		=>	1,
						'closedOn'		=>	$validatedData['closedOn'] ?? NULL,
						'closing_file'	=>	$closure_file ?? NULL
					]);

				DB::table('eoi_resource_deployment')
					->whereIn('orderid', $orderIds)
					->update([
						'isClosed' => 1
					]);
			});

			DB::table('eoi_request')
			->where('requestid',$validatedData['requestid'])
			->update([
				'isClosed'		=>	1,
				'closedOn'		=>	$validatedData['closedOn'] ?? NULL,
				'closure_file'	=>	$closure_file ?? NULL
			]);
			
			$message	=	"EoI Number ".$eoi->eoinumber." has been successfully closed along with all associated work orders.";
			return back()->with('success',$message);
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return back()->with('duplicate','Something went wrong, please try again after some time.')->withInput();
		}
    }


    public function updateProjectName(Request $request)
	{
		$request->merge([
			'requestid'	=>	Crypt::decrypt($request->requestid),
			'projectid'	=>	$request->projectid,
		]);
		
        $rules = [
			'requestid'	=>	'required|exists:eoi_request,requestid',
			'projectid'	=>	'required|exists:project_tbl,projectid',
        ];

		$messages = [
			'requestid.required'	=>	'EoI detail is required.',
			'requestid.exists'      =>	'The selected EoI detail is invalid.',

			'projectid.required'	=>	'Project name is required.',
			'projectid.exists'      =>	'The selected project name is invalid.',
		];
	
		$validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			DB::table('eoi_request')
			->where('requestid',$validatedData['requestid'])
			->update([
				'projectid'	=>	$validatedData['projectid']
			]);
			
			$exists	=	DB::table('eoi_work_order')->where('requestid',$validatedData['requestid'])->exists();
			
			if($exists)
			{
				DB::table('eoi_work_order')
				->where('requestid',$validatedData['requestid'])
				->update([
					'projectid'	=>	$validatedData['projectid']
				]);
			}
			return response()->json(['status'=>200,'message'=>'The project name has been mapped successfully with the EoI.']);
		}
		catch(Exception $e)
		{
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
    }

    public function allowAddition(Request $request,$requestid=NULL,$allowtype=NULL)
	{
		
		$request->merge([
			'requestid'	=>	Crypt::decrypt($request->requestid),
		]);
		
        $rules = [
			'requestid'	=>	'required|exists:eoi_request,requestid',
        ];

		$messages = [
			'requestid.required'	=>	'EoI detail is required.',
			'requestid.exists'      =>	'The selected EoI detail is invalid.',
		];
	
		$validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$eoi	=	DB::table('eoi_request')->select('eoinumber')->where('requestid',$validatedData['requestid'])->first();
			
			if($allowtype=='Enable')
			{
				DB::table('eoi_request')
				->where('requestid',$validatedData['requestid'])
				->update([
					'allowResourceAdd'	=>	1
				]);
				
				DB::table('eoi_addtional_resource_log')
				->insert([
					'requestid'		=>	$validatedData['requestid'],
					'enabledBy'		=>	session('userId'),
					'datetime'		=>	now()
				]);
				
				return redirect()->route('eoi.requestlist')->with("success","Additional resource permission has been allowed for this EoI {$eoi->eoinumber}");
			}
			else
			{
				DB::table('eoi_request')
				->where('requestid',$validatedData['requestid'])
				->update([
					'allowResourceAdd'	=>	0
				]);
				
				DB::table('eoi_addtional_resource_log')
				->insert([
					'requestid'		=>	$validatedData['requestid'],
					'disabledBy'	=>	session('userId'),
					'datetime'		=>	now()
				]);
				return redirect()->route('eoi.requestlist')->with("success","Permission for additional resources has been denied for this EoI {$eoi->eoinumber}.");
			}
			
		}
		catch(Exception $e)
		{
			return redirect()->route('eoi.requestlist')->with('fail', 'The process could not be completed. Please try again.');
		}
    }
	
}

