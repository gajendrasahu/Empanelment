<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Manager;
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
use Illuminate\Support\Str;
use App\Services\TierWiseDataService;
use Illuminate\Support\Facades\Log;
use App\Services\DeploymentDataService;
use App\Services\FeatureService;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOTPMail;
use App\Services\PasswordService;
use Hash;

class ProjectManagerController extends Controller
{
	protected $smsService;
    protected $todays_datetime;
    protected $todays_date;	
	protected $priceService;
	protected $deploymentService;
	protected $featureService;
	protected $passwordService;
	
	public function __construct(SmsService $smsService,TierWiseDataService $priceService,DeploymentDataService $deploymentService,FeatureService $featureService,PasswordService $passwordService)
	{
		$this->smsService 			=	$smsService;
		$this->priceService 		=	$priceService;
		ini_set('serialize_precision', -1);
		$this->todays_datetime		=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date			=	Carbon::now()->format('Y-m-d');
		$this->deploymentService	=	$deploymentService;
		$this->featureService 		= 	$featureService;
		$this->passwordService 		= 	$passwordService;
	}
	function cleanHtml($content)
	{
		// Remove script/style blocks completely
		$content = preg_replace('/<(script|style).*?<\/\1>/is', '', $content);

		$allowedTags = '<ul><li><b><strong><p><div><h1><h2><h3><h4><h5><h6><br><table><thead><tbody><tfoot><tr><td><th>';

		$content = strip_tags($content, $allowedTags);

		$content = preg_replace('/\s*on\w+="[^"]*"/i', '', $content);
		$content = preg_replace("/\s*on\w+='[^']*'/i", '', $content);
		$content = preg_replace('/javascript:/i', '', $content);

		return $content;
	}
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if (strpos($number, '.') !== false) {
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree 	= 	substr($number, -3);
		$rest 		=	substr($number, 0, -3);

		if($rest != '')
		{
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}

    public function storePmHiring(Request $request,$recordid)
	{    
       
        $rules = [
			'projectobjective' 		=> 	'required',
			'scopeofwork' 			=> 	'required',
			'anyother' 				=> 	'nullable',
			'authorizationletter' 	=> 	'required|file|mimetypes:application/pdf|max:5120',
			'attachmentfile.*' 		=>	'nullable|file|mimetypes:application/pdf|max:5120',
			'tier_choice' 			=> 	'required|numeric',
        ];

        $messages = [
			'projectobjective.required' 	=>	'Project objective is required',
			'scopeofwork.required' 			=> 	'Scope of work is required',
			'authorizationletter.required' 	=> 	'Authorization letter is required',
			'authorizationletter.file' 		=> 	'Invalid authorization file',
			'authorizationletter.mimetypes' => 	'Invalid authorization file',
			'authorizationletter.max' 		=> 	'Invalid authorization file',
			'attachmentfile.*.file'    		=> 	'Invalid file.',
			'attachmentfile.*.mimetypes'	=> 	'Invalid file format. Allowed: pdf, doc, docx.',
			'attachmentfile.*.max'     		=> 	'Max 5 MB is allowed.',
			'tier_choice.required' 			=> 	'Please select the Tier of Vendors to float the EoI.',
			'tier_choice.numeric' 			=> 	'Invalid tier value for vendors.',
        ];
		
		$validator = Validator::make($request->all(), $rules, $messages);
		
		if (!session()->has('projecttitle')) {
			$validator->errors()->add('projecttitle', 'Project name is mandatory.');
		}
		if (!session()->has('projectduration')) {
			$validator->errors()->add('projectduration', 'Project duration is mandatory.');
		}

		if (!session()->has('categoryid')) {
			$validator->errors()->add('categoryid', 'Category name is mandatory.');
		}

		if ($validator->fails()) {
			throw new ValidationException($validator);
		}
        
		$validatedData	=	$request->validate($rules, $messages);

		$userId      	=	$request->session()->get('userId');
        $userName      	=	$request->session()->get('userName');

		$attachmenttitles= $request->input('attachmenttitle');
		$attachmentfiles = $request->file('attachmentfile');
		
		$authorizationletter=	$request->file('authorizationletter');
	
		if($recordid==0)
		{
			try 
			{
				if($request->hasFile('authorizationletter'))
				{
					$authorizationletter= $request->file('authorizationletter')->store('uploads/authorizationletters','public');
				}
			
				DB::transaction(function () use ($request, $userId,$validatedData,$attachmenttitles,$attachmentfiles,$authorizationletter)
				{
					// Optional safety check
					$categoryId 		=	session('categoryid');
					$projecttitle		=	session('projecttitle');
					$projectduration	=	session('projectduration');
			

					if (!$categoryId || !$projecttitle || !$projectduration) {
						throw ValidationException::withMessages([
							'session' => ['SESSION EXPIRED OR INCOMPLETE. PLEASE RESTART THE PROCESS.']
						]);
					}

				
					// Insert into eoi_request
					$approvalRequired	=	1;
					if($categoryId==1)
					{
						$approvalRequired	=	1;
					}
					
					$requestId = DB::table('eoi_request')->insertGetId([
						'userid'             => $userId,
						'projecttitle'		 => $projecttitle,
						'projectduration'	 => $projectduration,
						'projectobjective'	 => $validatedData['projectobjective'],
						'categoryid'         => $categoryId,
						'createdby'          => $userId,
						'creationdate'       => now(),
						'scopeofwork'		 => $validatedData['scopeofwork'],
						'anyother'	 		 => $validatedData['anyother'],
						'authorizationletter'=>	$authorizationletter,
						'tier_choice'		 => $validatedData['tier_choice'],
						'approvalClosed'	 => $approvalRequired
					]);

					// Move records from temp to request_detail
					$records = DB::table('eoi_temp_requirement')
								->where('userid', $userId)
								->get();
					$totalbudget		=	0;
					$totaladmincharge	=	0;
					$totalamount		=	0;
					foreach ($records as $rec)
					{
						DB::table('eoi_request_detail')->insert([
							'requestid'         => 	$requestId,
							'sectorid'          => 	$rec->sectorid,
							'positionid'        => 	$rec->positionid,
							'experience'        => 	$rec->experience,
							'employmenttype'    => 	$rec->employmenttype,
							'role'            	=> 	$rec->role,
							'qualification'     => 	$rec->qualification,
							'duration'          => 	$rec->duration,
							'creationdate'      => 	$rec->creationdate,
							'remark'      		=> 	$rec->remark,
							'experiencelevel'	=>	$rec->experiencelevel,
							'rateid'			=>	$rec->rateid,
						]);						
					}
					$year 		= 	date('Y');
					$month 		= 	date('m');
					$day 		= 	date('d');
					
					$eoinumber	=	"";
					DB::table('eoi_request')->where('requestid',$requestId)->update([
						'eoinumber'			=>	$eoinumber ?? NULL,
					]);
					// Clean up temp data
					DB::table('eoi_temp_requirement')
						->where('userid', $userId)
						->delete();

					// Optionally clear session vars after successful submission
					foreach ($attachmenttitles as $index => $attachmenttitle) {
						$file         = $attachmentfiles[$index] ?? null;
						$filePath     = "";

						if ($file) {
							$filePath = $file->store('uploads/eoiattachments','public');
						}
						if($attachmenttitle!='' && $filePath!='')
						{
							DB::table('eoi_request_attachment')->insert([
								'eoirequestid'   	=> $requestId,
								'attachmenttitle'   => $attachmenttitle,
								'attachmentfile'  	=> $filePath,					
								'created_by'  		=> Session('userId'),
								'creationdate'  	=> now()
							]);
						}
					}
					
					Session::forget(['categoryid','subrequirementtype','duration_month']);
				});
				
				return redirect()->route('add.pmhiring')->with('success','Record saved successfully.');
			}
			catch(QueryException $e)
			{
			
				return back()->with('duplicate',$e->getMessage())->withInput();

			}
		}
		else
		{
		}
    }
	
	public function addPmEoiRecord(Request $request)
	{	
		$categoryId = request('categoryid') ?? Session::get('categoryid');
		
		if(Session::has('categoryid'))
		{
			if(Session::get('categoryid')==0)
			{
				Session::put('categoryid',request('categoryid'));
			}
		}
		
		$rules = [
			'experience'        => 	'nullable|max:150',
			'qualification'    	=> 	'required',
			'duration'          => 	'required|numeric',
			'employmenttype'	=>	'required',
			'remark'			=>	'nullable',
			'projecttitle'		=>	'nullable',
			'projectobjective'	=>	'nullable',
			'aboutproject'		=>	'nullable',
			'scopeofwork'		=>	'nullable',
			'anyother'			=>	'nullable',
			'resource_qty'		=>	'required'
			
		];
        $messages = [
            'sectorid.required'			=> 'Sector name is required',
			'sectorid.numeric'			=> 'Invalid sector value',
            'experiencelevel.required'	=> 'Experience level is required',
			'experiencelevel.numeric'	=> 'Invalid experience level provided',
			'sectorid.numeric'			=> 'Invalid sector value',
			'experience.max'			=> 'Maximum 150 characters allowed for experience',
			'categoryid.required'		=> 'Category name is required',
            'role.required'				=> 'Role is required',
            'positionid.required' 		=> 'Position name is required',
			'positionid.numeric' 		=> 'Invalid position name',
			'qualification.required' 	=> 'Qualification is required',
			'duration.required' 		=> 'Duration is required',
			'duration.numeric' 			=> 'Invalid duration value',
			'projecttitle.required' 	=> 'Project name is required',
			'projectduration.required' 	=> 'Project duration is required',
			'paymentterms.required' 	=> 'Payment terms is required',
			'employmenttype.required' 	=> 'Employment type is required',
			'resource_qty.required' 	=> 'Resource quantity is required',
        ];

		// Base rules for categoryid, subrequirementtype, and tierid
		if(!Session::has('categoryid'))
		{
			$rules['categoryid'] = 'required';
		}
		if(!Session::has('projecttitle'))
		{
			$rules['projecttitle'] = 'required';
		}
		if(!Session::has('projectduration'))
		{
			$rules['projectduration'] = 'required';
		}

		// Conditional rules based on categoryid
		if($categoryId==1)
		{
			$rules['role'] 				= 	'required';
			$rules['experiencelevel'] 	= 	'required|numeric';
			$rules['sectorid'] 			= 	'nullable';
			$rules['positionid']		=	'nullable';
		}
		if($categoryId==2)
		{
			$rules['sectorid'] 	=	'required|numeric';
			$rules['positionid']=	'required|numeric';
			$rules['role'] 		=	'nullable';
		}		


        //$validatedData	=	$request->validate($rules,$messages);
		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status' => false,
				'errors' => $validator->errors()
			], 422);
		}

		$validatedData = $validator->validated();		
		
		if(!Session::has('categoryid'))
		{
			Session::put('categoryid',$validatedData['categoryid']);
			Session::put('projecttitle',$validatedData['projecttitle']);
			Session::put('projectduration',$validatedData['projectduration']);
		}
		
		$isexists	=	DB::table('eoi_temp_requirement')->where('userid',session('userId'))->first();
		if($isexists)
		{
			if($isexists->categoryid!=0)
			{
				if(($isexists->categoryid!=$categoryId))
				{
					return response()->json(['status'=>400,'message'=>'The empanelment type or tier must be the same for all resources.']);
				}
			}
		}
		
		DB::beginTransaction();
		try
		{
			$userId    		= 	$request->session()->get('userId');
			$userName  		= 	$request->session()->get('userName');
			$userType  		= 	$request->session()->get('userType');
			$html	=	"";

			$rateid	=	DB::table('remuneration_rate_list')
						->where('categoryid',$categoryId)
						->where('isactive',1)
						->value('rateid');
			
			if($categoryId==2)
			{
				$experience	=	DB::table('position_tbl')->where('positionid',$validatedData['positionid'])->value('experience');
				
				
				DB::table('eoi_temp_requirement')->where('userid',$userId)->where('categoryid',0)->delete();

				$i=1;
				while($i<=intval($validatedData['resource_qty']))
				{				
					DB::table('eoi_temp_requirement')->insert([
						'sectorid'			=>	$validatedData['sectorid'] ?? 0,
						'positionid'		=>	$validatedData['positionid'] ?? 0,
						'experience'		=>	$experience ?? '',
						'role'				=>	$validatedData['role'] ?? '',
						'employmenttype'	=>	$validatedData['employmenttype'] ?? NULL,
						'qualification'		=>	$validatedData['qualification'] ?? '',
						'duration'			=>	$validatedData['duration'],
						'userid'			=>	$userId,
						'creationdate'		=>	now(),
						'remark'			=>	$validatedData['remark'] ?? '',
						'categoryid'		=>	session('categoryid'),
						'projecttitle'		=>	session('projecttitle'),
						'projectduration'	=>	session('projectduration'),
						'projectobjective'	=>	$validatedData['projectobjective'] ?? '',
						'aboutproject'		=>	$validatedData['aboutproject'] ?? '',
						'scopeofwork'		=>	$validatedData['scopeofwork'] ?? '',
						'anyother'			=>	$validatedData['anyother'] ?? '',
						'rateid'			=>	$rateid
					]);
					$i++;
				}

				$sectorid = $validatedData['sectorid'] ?? 0;
				$experiencelist =	DB::table('remuneration_tbl as a')
									->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
									->select('a.remunerationid as value','b.consultantposition as label')
									->where('a.tierid','=',1)
									->where('a.categoryid','=',session('categoryid'))
									->where('a.sectorid','=',$sectorid)
									->orderby('b.consultantposition')
									->get();

				$sectors		=	DB::table('sector_tbl')->where('categoryid',session('categoryid'))->orderby('sectorname')->get();

				// TIER DATA
				$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->limit(1)->get();
				$i = 0;

				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryId)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getCsfTempPrice($userId,$categoryId,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);
					
					$tier2 = ($i == 1) ? 0 : 1;

					$html[]	=	view('admin.manager.resourceTierData.csfTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'isTemp'			=>	'Yes',
									'req_id'			=>	0
								])->render();
				}

				$html = !empty($html) ? implode('', $html) : '';
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Record saved successfully.','tableData'=>$html]);
			}
			if($categoryId==1)
			{
				$experiencelist	=	DB::table('work_experience')->orderby('experienceid')->get();
				
				
				DB::table('eoi_temp_requirement')->where('userid',$userId)->where('categoryid',0)->delete();
				
				$experience	=	DB::table('work_experience')->where('experienceid',$request->input('experienceid'))->value('workexperience');
				
				$i=1;
				while($i<=intval($validatedData['resource_qty']))
				{
					DB::table('eoi_temp_requirement')->insert([
						'role'				=>	$validatedData['role'] ?? '',
						'experience'		=>	$experience ?? NULL,
						'experiencelevel'	=>	$validatedData['experiencelevel'],
						'employmenttype'	=>	$validatedData['employmenttype'] ?? NULL,
						'qualification'		=>	$validatedData['qualification'] ?? '',
						'duration'			=>	$validatedData['duration'],
						'userid'			=>	$userId,
						'creationdate'		=>	now(),
						'remark'			=>	$validatedData['remark'] ?? '',
						'categoryid'		=>	session('categoryid'),
						'projecttitle'		=>	session('projecttitle'),
						'projectduration'	=>	session('projectduration'),
						'projectobjective'	=>	$validatedData['projectobjective'] ?? '',
						'aboutproject'		=>	$validatedData['aboutproject'] ?? '',
						'scopeofwork'		=>	$validatedData['scopeofwork'] ?? '',
						'anyother'			=>	$validatedData['anyother'] ?? '',
						'rateid'			=>	$rateid
					]);
					$i++;
				}

				//TIER 1 DATA
				$tiers	=	DB::table('tiermaster_tbl')->limit(1)->orderBy('tierid')->get();
				$i = 0;

				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryId)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getAwdTempPrice($userId,$categoryId,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
						$budget			=	$budget+(($budget*$pricing->tax)/100);
						$rec->budget	=	$budget*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);

					$tier2 	= ($i == 1) ? 0 : 1;
					$tier2	=	1;
					$html[]	=	view('admin.manager.resourceTierData.awdTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'isTemp'			=>	'Yes',
									'req_id'			=>	0
								])->render();
				}				
				
				$html = !empty($html) ? implode('', $html) : '';
			
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Record saved successfully.','tableData'=>$html]);
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}
    public function getPmExperienceList(Request $request)
	{
		if(!session('departmentId') || !session('isProjectManager'))
		{
			return redirect('dashboard');
		}
		
		$categoryid = intval($request->input('categoryid'));

		if($categoryid==1)
		{
			$experienceid = intval($request->input('experienceid'));
			$experiencelist =DB::table('remuneration_tbl as a')
						->leftJoin('work_experience as b','b.experienceid','=','a.experienceid')
						->select('a.experiencelevel as value',DB::raw("CONCAT('Level ', a.experiencelevel, '') as label"))
						->where('a.categoryid','=',$categoryid)
						->groupBy('a.experiencelevel')
						->orderby('a.experiencelevel')
						->get();        
		}
		if($categoryid==2)
		{
			$sectorid 		= 	intval($request->input('sectorid'));
			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->select('a.positionid as value','b.consultantposition as label')
								->where('a.categoryid','=',$categoryid)
								->where('a.sectorid','=',$sectorid)
								->groupBy('a.positionid')
								->orderby('b.consultantposition')
								->get();
		}
        return response()->json($experiencelist);
    }
	
	public function loadFormData(Request $request)
	{
		
		$categoryid	=	$request->input('categoryid') ?? 0;
		$sectorid	=	$request->input('sectorid') ?? 0;

		$tiers		=	DB::table('tiermaster_tbl')->orderBy('tierid')->limit(1)->get();
		$rateid		=	DB::table('remuneration_rate_list')
						->where('categoryid',$categoryid)
						->where('isactive',1)
						->value('rateid');

		
		$userId		=	$request->session()->get('userId');
		
		if($categoryid==1)
		{
			$experiencelist		=	DB::table('work_experience')->orderby('experienceid')->get();
			$experiencelevel	= 	intval($request->input('experiencelevel'));
			
			try
			{
				$i = 0;

				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryid)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getAwdTempPrice($userId,$categoryid,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
						$budget			=	$budget+(($budget*$pricing->tax)/100);
						$rec->budget	=	$budget*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);

					$tier2 	= ($i == 1) ? 0 : 1;
					$tier2	=	1;
					$html[]	=	view('admin.manager.resourceTierData.awdTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'isTemp'			=>	'Yes',
									'req_id'			=>	0
								])->render();
				}
				$modalForm	=	view('admin.manager.resourceTierData.awdModalForm', [
									'categoryid' 		=> 	$categoryid,
									'experiencelist'	=> 	$experiencelist,
								])->render();

				$formhtml	=	view('/admin/manager/ajaxpages/awdformTable', compact('modalForm','html'))->render();
				return response()->json(['success'=>true,'formhtml'=>$formhtml]);
			}
			catch(Exception $e)
			{
				Log::error('Error '.$e->getMessage());
				return response()->json(['status'=>500,'message'=>'Something went wrong. Please try again later.']);
			}
			catch(QueryException $e)
			{
				Log::error('Error '.$e->getMessage());
				return response()->json(['status'=>500,'message'=>'Something went wrong. Please try again later.']);
			}			
		}
		if($categoryid==2)
		{
			$tierid			=	0;
			$sectorid 		= 	intval($request->input('sectorid'));

			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->select('a.positionid as value','b.consultantposition as label')
								->where('a.categoryid','=',$categoryid)
								->where('a.sectorid','=',$sectorid)
								->groupBy('a.positionid')
								->orderby('b.consultantposition')
								->get();
								
			$sectors	=	DB::table('sector_tbl')->where('categoryid',$categoryid)->orderby('sectorname')->get();
			


			
			try
			{
				$i = 0;

				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryid)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getCsfTempPrice($userId,$categoryid,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);
					
					$tier2 = ($i == 1) ? 0 : 1;
					$tier2 = 1;
					$html[]	=	view('admin.manager.resourceTierData.csfTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'isTemp'			=>	'Yes',
									'req_id'			=>	0
								])->render();
				}
				$modalForm	=	view('admin.manager.resourceTierData.csfModalForm', [
									'categoryid' 		=> 	$categoryid,
									'experiencelist'	=> 	$experiencelist,
									'sectors' 			=> 	$sectors,
								])->render();

				$formhtml	=	view('/admin/manager/ajaxpages/consultantformTable', compact('modalForm','html'))->render();
				return response()->json(['success'=>true,'formhtml'=>$formhtml]);
			}
			catch(Exception $e)
			{
				Log::error('Error '.$e->getMessage());
				return response()->json(['status'=>500,'message'=>'Something went wrong. Please try again later.']);
			}
			catch(QueryException $e)
			{
				Log::error('Error '.$e->getMessage());
				return response()->json(['status'=>500,'message'=>'Something went wrong. Please try again later.']);
			}
		}		
	}
    public function getPmRemunerationAmount(Request $request)
	{
		$categoryid 	= 	intval($request->input('categoryid'));
		$sectorid 		= 	intval($request->input('sectorid'));
        $positionid 	= 	intval($request->input('positionid'));

		$budget			=	0;
		$experience		=	"";
		
		if($categoryid==2)
		{
			$experience	=	DB::table('position_tbl')->where('positionid',$positionid)->value('experience');
		}
        return response()->json(['budget'=>round($budget,2),'experience'=>$experience]);
    }
	
    public function addPmHiring(Request $request)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','pmeois');
		Session::put('adminsubmenu','addpmhiring');
		Session::put('menid',111);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=111 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		
		$exists	=	DB::table('eoi_temp_requirement')->where('userid','=',$userId)->first();
		if($exists)
		{
			Session::put('projecttitle',$exists->projecttitle);
			Session::put('projectduration',$exists->projectduration);
			Session::put('categoryid',$exists->categoryid);
			Session::put('subrequirementtype',$exists->subrequirementtype);
			Session::put('tierid',$exists->tierid);			
			$scope				=	$exists->scopeofwork;
			$aboutproject		=	$exists->aboutproject;
			$anyother			=	$exists->anyother;
			$projectobjective	=	$exists->projectobjective;
		}
		else
		{
			Session::forget('projecttitle');
			Session::forget('projectduration');
			Session::forget('categoryid');
			Session::forget('tierid');
			$scope				=	"";
			$aboutproject		=	"";
			$anyother			=	"";
			$projectobjective	=	"";
		}
		$sector	=	DB::table('sector_tbl')->orderby('sectorname')->get();
		
        return view('admin/manager/hiring_add',compact('token','category','tier','sector','scope','aboutproject','anyother','projectobjective'));
    }
	
    public function pmSelectionProcess(Request $request)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		/*
		$exists	=	DB::table('eoi_temp_requirement')->where('userid',$request->session()->get('userId'))->exists();
		if($exists)
		{
			return redirect()->route('add.pmhiring');
		}
		*/
        Session::put('adminmenu','pmeois');
		Session::put('adminsubmenu','pmguideline');
		Session::put('menid',111);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=111 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$sectors		=	DB::table('sector_tbl')->where('categoryid',2)->orderBy('sectorid')->get();
		$tier1_vendors 	= 	DB::table('vendor_tbl')->select('vendorid','companyname')->where('categoryid',2)->where('tierid',1)->orderby('companyname')->get();
		$tier2_vendors 	= 	DB::table('vendor_tbl')->select('vendorid','companyname')->where('categoryid',2)->where('tierid',2)->orderby('companyname')->get();
		
		$vendorSectors 	= 	DB::table('vendor_sector')->select('vendorid', 'sectorid')->get()->groupBy('vendorid');
		
		$rateid		=	DB::table('remuneration_rate_list')->where('categoryid',2)->where('isactive',1)->value('rateid');
		$t1_price	=	DB::table('pricing_tbl')->where('categoryid',2)->where('tierid',1)->first();
		$t2_price	=	DB::table('pricing_tbl')->where('categoryid',2)->where('tierid',2)->first();
		
		$tier1_sectors		=	DB::table('sector_tbl')->where('categoryid',2)->orderBy('sectorid')->get();
		foreach($tier1_sectors as $sector)
		{
			$sector->positions	=	DB::table('position_tbl')->orderby('consultantposition')->get();
			foreach($sector->positions as $position)
			{
				$baseprice		=	0;
				$includingtax	=	0;
				$includingadmin	=	0;
				$remu	=	DB::table('remuneration_tbl')
												->where('sectorid',$sector->sectorid)
												->where('positionid',$position->positionid)
												->where('categoryid',$t1_price->categoryid)
												->where('tierid',$t1_price->tierid)
												->where('rateid',$rateid)
												->first();
				$baseprice		=	$remu->remuneration;
				$includingtax	=	$baseprice+(($baseprice*$t1_price->tax)/100);
				$includingadmin	=	$includingtax+(($includingtax*$t1_price->admincharge)/100);
				$position->baseprice	=	$baseprice;
				$position->rate			=	$includingtax;
				$position->admincharge	=	($includingtax*$t1_price->admincharge)/100;
				$position->total		=	round($includingadmin,2);
				
				$position->baseprice	=	$this->formatIndianCurrency($position->baseprice);
				$position->rate			=	$this->formatIndianCurrency($position->rate);
				$position->admincharge	=	$this->formatIndianCurrency($position->admincharge);
				$position->total		=	$this->formatIndianCurrency($position->total);
				
			}
			
		}
		
		$tier2_sectors	=	DB::table('sector_tbl')->where('categoryid',2)->orderBy('sectorid')->get();
		foreach($tier2_sectors as $sector)
		{
			$sector->positions	=	DB::table('position_tbl')->orderby('consultantposition')->get();
			foreach($sector->positions as $position)
			{
				$remuneration	=	DB::table('remuneration_tbl')
												->where('sectorid',$sector->sectorid)
												->where('positionid',$position->positionid)
												->where('categoryid',$t2_price->categoryid)
												->where('tierid',$t2_price->tierid)
												->where('rateid',$rateid)
												->first();
				$position->baseprice	=	$remuneration->remuneration;
				$position->rate			=	$remuneration->remuneration+((($remuneration->remuneration)*$t2_price->tax)/100);
				$position->admincharge	=	($position->rate*$t2_price->admincharge)/100;
				$position->total		=	round(($position->rate+$position->admincharge),2);
				
				$position->baseprice	=	$this->formatIndianCurrency($position->baseprice);
				$position->rate			=	$this->formatIndianCurrency($position->rate);
				$position->admincharge	=	$this->formatIndianCurrency($position->admincharge);
				$position->total		=	$this->formatIndianCurrency($position->total);
				
			}
		}
		
		
		$awd_tier1_vendors 	= 	DB::table('vendor_tbl')->select('vendorid','companyname')->where('categoryid',1)->where('tierid',1)->orderby('companyname')->get();
		
		$awd_tier2_vendors 	= 	DB::table('vendor_tbl')->select('vendorid','companyname')->where('categoryid',1)->where('tierid',2)->orderby('companyname')->get();

		$pricing1	=	DB::table('pricing_tbl')->where('categoryid',1)->where('tierid',1)->first();
		$pricing2	=	DB::table('pricing_tbl')->where('categoryid',1)->where('tierid',2)->first();
		$rateid		=	DB::table('remuneration_rate_list')->where('categoryid',1)->where('isactive',1)->value('rateid');
		$tier1_rate	=	DB::table('remuneration_tbl')
							->select('remuneration','experiencelevel')
							->where('categoryid',1)
							->where('tierid',1)
							->where('experiencelevel','!=',0)
							->where('rateid',$rateid)
							->get();
							
		$operatingvalue	=	0;
		$tax			=	0;
		$admincharge	=	0;
		
		foreach($tier1_rate as $tr1)
		{
			$operatingvalue		=	round((($tr1->remuneration*$pricing1->operatingmargin)/100),2);
			$tr1->operatingvalue=	round($operatingvalue,2);
			$tr1->withoperating	=	round($tr1->remuneration+$operatingvalue,2);
			$tr1->taxvalue		=	round((($tr1->withoperating*$pricing1->tax)/100),2);
			$tr1->withtax		=	round($tr1->withoperating+$tr1->taxvalue,2);
			$tr1->admincharge	=	round((($tr1->withtax*$pricing1->admincharge)/100),2);
			$tr1->withadmin		=	round($tr1->withtax+$tr1->admincharge,2);
			
			$tr1->remuneration	=	$this->formatIndianCurrency($tr1->remuneration);
			$tr1->operatingvalue=	$this->formatIndianCurrency($tr1->operatingvalue);
			$tr1->withoperating	=	$this->formatIndianCurrency($tr1->withoperating);
			$tr1->taxvalue		=	$this->formatIndianCurrency($tr1->taxvalue);
			$tr1->withtax		=	$this->formatIndianCurrency($tr1->withtax);
			$tr1->admincharge	=	$this->formatIndianCurrency($tr1->admincharge);
			$tr1->withadmin		=	$this->formatIndianCurrency($tr1->withadmin);
		}

		$tier2_rate	=	DB::table('remuneration_tbl')
							->select('remuneration','experiencelevel')
							->where('categoryid',1)
							->where('tierid',2)
							->where('experiencelevel','!=',0)
							->where('rateid',$rateid)
							->get();
							
		$operatingvalue	=	0;
		$tax			=	0;
		$admincharge	=	0;
		
		foreach($tier2_rate as $tr1)
		{
			$operatingvalue		=	round((($tr1->remuneration*$pricing2->operatingmargin)/100),2);
			$tr1->operatingvalue=	round($operatingvalue,2);
			$tr1->withoperating	=	round($tr1->remuneration+$operatingvalue,2);
			$tr1->taxvalue		=	round((($tr1->withoperating*$pricing2->tax)/100),2);
			$tr1->withtax		=	round($tr1->withoperating+$tr1->taxvalue,2);
			$tr1->admincharge	=	round((($tr1->withtax*$pricing2->admincharge)/100),2);
			$tr1->withadmin		=	round($tr1->withtax+$tr1->admincharge,2);
			
			$tr1->remuneration	=	$this->formatIndianCurrency($tr1->remuneration);
			$tr1->operatingvalue=	$this->formatIndianCurrency($tr1->operatingvalue);
			$tr1->withoperating	=	$this->formatIndianCurrency($tr1->withoperating);
			$tr1->taxvalue		=	$this->formatIndianCurrency($tr1->taxvalue);
			$tr1->withtax		=	$this->formatIndianCurrency($tr1->withtax);
			$tr1->admincharge	=	$this->formatIndianCurrency($tr1->admincharge);
			$tr1->withadmin		=	$this->formatIndianCurrency($tr1->withadmin);
		}
		
		
        return view('admin/manager/selection_process',compact('sectors','tier1_vendors','tier2_vendors','vendorSectors','tier1_sectors','tier2_sectors','t1_price','t2_price','awd_tier1_vendors','awd_tier2_vendors','pricing1','pricing2','tier1_rate','tier2_rate'));
		
    }



    public function updateMprAttendance(Request $request)
	{
        $rules = [
			'mprid' 		=> 'required',
			'deploymentid' 	=> 'required',
        ];

        $messages = [
			'mprid.required' 		=> 'MPR detail is required',
			'mprid.numeric' 		=> 'Invalid MPR detail',
			'mprid.min' 			=> 'Invalid MPR detail',
			'deploymentid.required' => 'Deployment detail is required',
        ];
		$validatedData	=	$request->validate($rules, $messages);

		$validatedData['deploymentid']	=	Crypt::decrypt($validatedData['deploymentid']);
		$validatedData['mprid']			=	Crypt::decrypt($validatedData['mprid']);
		DB::beginTransaction();
		try
		{
			$mpr	=	DB::table('mpr_attendance_summary as a')
						->select('b.*')
						->join('mpr_tbl as b','b.mpr_id','=','a.mpr_id')
						->join('eoi_request as c','c.requestid','=','b.request_id')
						->where('a.mpr_id',$validatedData['mprid'])
						->where('a.deployment_id',$validatedData['deploymentid'])
						->where('c.userid',Session('userId'))
						->first();
			if(!$mpr)
			{
				return response()->json(['status' => 400,'message'=>'Invalid data provided.']);
			}
			$total_days = 	Carbon::create($mpr->mpr_year, $mpr->mpr_month)->daysInMonth;
			$presents	=	0;
			$absents	=	0;
			$leaves		=	0;
			$holidays	=	0;

			$attendance	=	DB::table('mpr_attendance')
							->where('mpr_id',$validatedData['mprid'])
							->where('deployment_id',$validatedData['deploymentid'])
							->get();
							
			foreach($attendance as $att)
			{
				$status		=	$request->input('day_'.$att->attendance_id);
				$updates	=	$request->input('update_'.$att->attendance_id);

				$newStatus = $status;
				$newDetail = $updates ?? NULL;
				
				DB::table('mpr_attendance')
				->where('attendance_id',$att->attendance_id)
				->update([
					'status' 	=> $newStatus,
					'remarks'	=> $newDetail,
				]);

				if($att->status!=$newStatus)
				{
					DB::table('mpr_attendance_logs')->insert([
						'attendance_id' => $att->attendance_id,
						'mpr_id' 		=> $validatedData['mprid'],
						'deployment_id' => $validatedData['deploymentid'],
						'old_status'    => $att->status,
						'new_status'    => $newStatus,
						'new_remark'    => $newDetail,
						'changed_at'    => now(),
						'changed_by'    => Session('userId'),
					]);
				}

				if($status=='P') $presents++;
				if($status=='A') $absents++;
				if($status=='L') $leaves++;
				if($status=='H') $holidays++;
				
			}
			$resource				=	DB::table('mpr_resource')
										->where('mpr_id',$validatedData['mprid'])
										->where('deployment_id',$validatedData['deploymentid'])
										->first();
										
			$per_day_salary			=	$resource->remuneration/$total_days;
			$countable				=	$presents+$leaves+$holidays;
			$calculated_salary		=	round($countable*$per_day_salary);
			
			DB::table('mpr_attendance_summary')
			->where('mpr_id',$validatedData['mprid'])
			->where('deployment_id',$validatedData['deploymentid'])
			->update([
				'total_present'		=>	$presents,
				'total_absent'		=>	$absents,
				'total_leave'		=>	$leaves,
				'total_holiday'		=>	$holidays,
				'approved_salary'	=>	$calculated_salary
			]);
			
			DB::commit();
			return response()->json(['status' => 200,'mprid'=>$validatedData['mprid']]);
		}
		catch(Exception $e)
		{
			DB::rollBack();
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
	}


    public function approveResourceMpr(Request $request)
	{	
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

		$mprid 			=	Crypt::decrypt($request->input('mprid'));
		$deploymentid 	=	Crypt::decrypt($request->input('deploymentid'));
		$deduction_value=	$request->input('deduction_value') ?? 0;
		$count_days		=	$request->input('count_days') ?? 0;
		$remark			=	$request->input('particular') ?? NULL;
		
		$summary		=	DB::table('mpr_attendance_summary')
							->where('mpr_id',$mprid)
							->where('deployment_id',$deploymentid)
							->first();


		/* UPDATE */
		$mpr		=	DB::table('mpr_tbl')->select('mpr_year','mpr_month','vendor_id')->where('mpr_id',$mprid)->first();
		$vendor		=	DB::table('vendor_tbl')->select('vendorid','categoryid')->where('vendorid',$mpr->vendor_id)->first();
		$total_days = 	Carbon::create($mpr->mpr_year, $mpr->mpr_month)->daysInMonth;
		$presents	=	0;
		$absents	=	0;
		$leaves		=	0;
		$holidays	=	0;
		$summaryData=	[];		

		$deduction_file			=	$request->file('deduction_file') ?? NULL;
		if($deduction_file)
		{
			$deduction_file	=	$request->file('deduction_file')->store('uploads/deductionfiles','public');
		}
		
		DB::beginTransaction();

		$presents	=	$request->input('presents') ?? 0;
		$absents	=	$request->input('absents') ?? 0;
		$leaves		=	$request->input('leaves') ?? 0;
		$holidays	=	$request->input('holidays') ?? 0;
		if($count_days!=($presents+$leaves+$holidays+$absents))
		{
			return response()->json(['status'=>400,'message'=>'Total available days for the selected month is '.$count_days.'. Please ensure that the sum of P, A, L, and H equals '.$count_days]);
		}
		$data	=	[];
		if($summary->total_present!=$presents)
		{
			$data['total_present'] = $presents;
		}
		if($summary->total_absent!=$absents)
		{
			$data['total_absent'] = $absents;
		}
		if($summary->total_leave!=$leaves)
		{
			$data['total_leave'] = $leaves;
		}
		if($summary->total_holiday!=$holidays)
		{
			$data['total_holiday'] = $holidays;
		}
		if($summary->count_days!=$count_days)
		{
			$data['count_days'] = $count_days;
		}
		if($deduction_value)
		{
			$data['deduction'] = $deduction_value;
		}
		
		
		
		if(!empty($data))
		{
			DB::table('mpr_attendance_summary')
			->where('summary_id',$summary->summary_id)
			->update($data);
			
			DB::table('mpr_attendance_summary_log')
			->insert([
				'summary_id'	=>	$summary->summary_id,
				'count_days'	=>	$summary->count_days,
				'total_present'	=>	$summary->total_present,
				'total_absent'	=>	$summary->total_absent,
				'total_leave'	=>	$summary->total_leave,
				'total_holiday'	=>	$summary->total_holiday,
				'updated_by'	=>	session('userId'),
				'updated_on'	=>	now(),
				'deduction_file'=>	$deduction_file,
				'remark'		=>	$remark,
			]);
			
			$summaryData['isFlaged']	=	1;
		}

									
		$per_day_salary			=	$summary->per_day_cost;
		$countable				=	$presents+$leaves+$holidays;
		$calculated_salary		=	round(($countable*$per_day_salary)-$deduction_value);
		

		$summaryData['total_present']	=	$presents;
		$summaryData['total_absent']	=	$absents;
		$summaryData['total_leave']		=	$leaves;
		$summaryData['total_holiday']	=	$holidays;
		$summaryData['approved_salary']	=	$calculated_salary;
		$summaryData['deduction']		=	$deduction_value;
		$summaryData['deduction_file']	=	$deduction_file;
		$summaryData['remarks'] 		= $remark;
		
		
		
		DB::table('mpr_attendance_summary')
		->where('mpr_id',$mprid)
		->where('deployment_id',$deploymentid)
		->update($summaryData);

		/* UPDATE CLOSED */

		DB::table('mpr_resource')
		->where('mpr_id','=',$mprid)
		->where('deployment_id','=',$deploymentid)
		->update([
			'status'		=>	'Approved',
			'approved_by'	=>	Session('userId'),
			'approved_on'	=>	now()
		]);
		
		$is_approved=	DB::table('mpr_resource')
						->selectRaw("
							CASE 
								WHEN COUNT(CASE WHEN status <> 'Approved' THEN 1 END) = 0 
								THEN 1 
								ELSE 0 
							END AS is_all_approved
						")
						->where('mpr_id',$mprid)
						->value('is_all_approved');		

		if($is_approved==1)
		{
			DB::table('mpr_tbl')->where('mpr_id',$mprid)->where('is_verified',0)->update([
				'is_verified'	=>	1,
				'verified_by'	=>	Session('userId'),
				'verified_date'	=>	now(),
				'mpr_status'	=>	'Verified'
			]);
			
			$approvedmprvalue	=	DB::table('mpr_attendance_summary')
									->where('mpr_id',$mprid)
									->sum('approved_salary');
									
			DB::table('mpr_tbl')->where('mpr_id','=',$mprid)->update(['mpr_approved_value'=>$approvedmprvalue]);
		}
		DB::commit();
		return response()->json(['status'=>200,'mprid'=>$request->input('mprid')]);
		
    }

	
    public function viewAttendanceMprManager(Request $request)
	{	
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

		$mprid 			=	Crypt::decrypt($request->input('mprid'));
		$deploymentid 	=	Crypt::decrypt($request->input('deploymentid'));
		
		$mpr		=	DB::table('mpr_tbl')->where('mpr_id',$mprid)->first();
		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$mpr->vendor_id)->first();

		$resource	= 	DB::table('eoi_resource_deployment as a')
						->select('a.deploymentid', 'a.name', 'b.remuneration')
						->leftJoin('mpr_resource as b', function ($join) use ($mprid) {
							$join->on('b.deployment_id', '=', 'a.deploymentid')
								 ->where('b.mpr_id', '=', $mprid);
						})
						->where('a.deploymentid', $deploymentid)
						->first();

		$summary	=	DB::table('mpr_attendance_summary as a')
						->select('a.*','b.status')
						->join('mpr_resource as b','b.deployment_id','=','a.deployment_id')
						->where('a.mpr_id',$mprid)
						->where('b.mpr_id',$mprid)
						->where('a.deployment_id',$deploymentid)
						->first();
		
		$attendance	=	DB::table('mpr_attendance as a')
						->where('a.mpr_id','=',$mprid)
						->where('a.deployment_id','=',$deploymentid)
						->get();
		
		$html 		= 	view('admin.manager.ajaxpages.loadAttendanceMprData',['resource'=>$resource,'attendance'=>$attendance,'mpr'=>$mpr,'summary'=>$summary,'vendor'=>$vendor])->render();
		
		return response()->json(['status'=>200,'data'=>$html]);
		
    }
	

    public function viewMprManager(Request $request,$mprid=NULL)
	{
		
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

		$mprid 		=	Crypt::decrypt($request->input('mprid'));
		
		$mpr	=	DB::table('mpr_tbl as a')
					->select('a.mpr_number','a.report_month','a.mpr_month','a.mpr_year','a.submission_date','a.mpr_status','a.created_at','a.mpr_value','b.companyname','a.is_watched','a.request_id','a.order_id')
					->join('vendor_tbl as b','b.vendorid','=','a.vendor_id')
					->where('a.mpr_id','=',$mprid)
					->first();
						
		if($mpr->is_watched==0)
		{
			DB::table('mpr_tbl')->where('mpr_id','=',$mprid)->update([
				'is_watched'	=>	1,
				'watched_on'	=>	now(),
				'watched_by'	=>	Session('userId')
			]);
		}
		
		$order	=	DB::table('eoi_work_order as a')
					->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.project_name','a.categoryid')
					->leftJoin('project_tbl as b','b.projectid','=','a.projectid')
					->where('a.orderid',$mpr->order_id)
					->first();
		
		$data	=	DB::table('mpr_resource as a')
					->select('a.mpr_id','a.deployment_id','a.remuneration','a.total_amount','a.status','b.name','b.mobilenumber','b.email','b.deployed_date','d.sectorname','e.consultantposition','f.total_working_days','f.total_present','f.total_absent','f.total_leave','f.total_holiday','f.calculated_salary','f.net_salary','f.approved_salary','f.mpr_file','f.attendance_file','f.supporting_file','b.role','b.experience','b.experiencelevel')
					->join('eoi_resource_deployment as b','b.deploymentid','=','a.deployment_id')
					->leftJoin('sector_tbl as d','d.sectorid','=','b.sectorid')
					->leftJoin('position_tbl as e','e.positionid','=','b.positionid')
					->join('mpr_attendance_summary as f',function($join){
						$join->on('f.mpr_id','=','a.mpr_id')
							 ->on('f.deployment_id','=','a.deployment_id');
					})
					->where('a.mpr_id','=',$mprid)
					->get();

		$html	=	view('admin.manager.ajaxpages.loadMprData',['data'=>$data,'mpr'=>$mpr,'order'=>$order])->render();
		
		return response()->json(['status'=>200,'data'=>$html]);
		
    }
	
	
    public function getPmMprData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

		$vendorid 		=	$request->input('vendorid');
		$orderid 		=	$request->input('orderid');
		
		$month 			=	$request->input('month',0);
		$year 			=	$request->input('year',0);
		$approval		=	$request->input('approval');
		
		
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
		
		$data			=	DB::table('mpr_tbl as a')
								->select('a.mpr_id','a.mpr_number','a.vendor_id','a.order_id','a.request_id','a.report_month','a.mpr_month','a.mpr_year','a.submission_date','a.is_verified','a.verified_by','a.verified_date','a.mpr_status','a.remarks','a.created_at','a.is_watched','a.watched_on','a.watched_by','a.mpr_value','a.mpr_approved_value','c.ordernumber','d.companyname','c.orderdate','a.attendance_file','a.mpr_file','a.supporting_file','e.project_name')
								->leftJoin('eoi_request as b', function ($join) {
									$join->on('b.requestid','=','a.request_id')
										 ->where('a.request_id','!=',0);
								})
								->join('eoi_work_order as c','c.orderid','=','a.order_id')
								->join('vendor_tbl as d','d.vendorid','=','c.vendorid')
								->leftJoin('project_tbl as e','e.projectid','=','c.projectid')
								->where(function ($q) {
									$q->where(function ($q1) {
										$q1->where('a.request_id', '!=', 0)
										   ->where('b.userid', Session('userId'));
									})
									->orWhere(function ($q2) {
										$q2->where('a.request_id', 0)
										   ->where('c.userid', Session('userId'));
									});
								})
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.mpr_number', 'like', '%' . $pagesearch . '%')
										  ->orWhere('c.ordernumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('b.eoinumber', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($vendorid!=0,function($query) use ($vendorid){
									return $query->where('a.vendor_id','=',$vendorid);
								})
								->when($orderid!=0,function($query) use ($orderid){
									return $query->where('a.order_id','=',$orderid);
								})
								->when($month!=0,function($query) use ($month){
									return $query->where('a.mpr_month','=',$month);
								})
								->when($year!=0,function($query) use ($year){
									return $query->where('a.mpr_year','=',$year);
								})
								->whereNotNull('a.submission_date')
								->where('a.is_verified','=',$approval)
								->orderBy('a.mpr_month')
								->paginate($pagesize,['*'],'page',$currentPage);
		
	
		return view('admin/manager/ajaxpages/mprTable',['data'=>$data]);

    }
	
    public function managerMprList(Request $request,$search=NULL)
	{
	
        Session::put('adminmenu','pmorderinvoices');
		Session::put('adminsubmenu','pmmprlist');
		Session::put('menid',118);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=118 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$vendors	=	DB::table('eoi_work_order as a')
						->join('eoi_request as b', 'b.requestid', '=', 'a.requestid')
						->join('vendor_tbl as c', 'c.vendorid', '=', 'a.vendorid')
						->where('b.userid', Session('userId'))
						->groupBy('c.vendorid', 'c.companyname')
						->orderBy('c.companyname')
						->get(['c.vendorid', 'c.companyname']);

		$orders		=	DB::table('eoi_work_order as a')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->where('b.userid',Session('userId'))
						->groupBy('a.orderid', 'a.ordernumber')
						->orderBy('a.ordernumber')
						->get(['a.orderid', 'a.ordernumber']);

		if($search!='')
		{
			DB::table('mpr_tbl')
			->where('mpr_id','=',Crypt::decrypt($search))
			->update([
				'is_watched'	=>	1,
				'watched_on'	=>	now(),
				'watched_by'	=>	Session('userId')
			]);
			$search	=	DB::table('mpr_tbl')->where('mpr_id',Crypt::decrypt($search))->value('mpr_number');
		}
        return view('admin/manager/mpr_list',compact('search','vendors','orders'));
    }
	
    public function updatePmDeploymentVerification(Request $request,$orderid)
	{
		if(!session('departmentId'))
		{
			return redirect('dashboard');
		}
		
		$rules = [
			'verification'   => 'required|array|min:1',
		];

		$messages = [
			'verification.required' => 'Please select at least one checkbox.',
			'verification.array'    => 'Invalid selection.',
			'verification.min'      => 'Please select at least one checkbox.',
		];

		$validatedData = $request->validate($rules, $messages);		

		DB::beginTransaction();
		try
		{
			$orderid	=	Crypt::decrypt($orderid);
			
			foreach($request->verification as $encryptedDeploymentId => $value)
			{
				$deploymentid = Crypt::decrypt($encryptedDeploymentId);
				if($value)
				{
					$deploymentStatus = 'Active';
				}
				else
				{
					$deploymentStatus = 'Pending';
				}

				if($deploymentStatus=='Active')
				{
					$data = [
						'deployment_status'	=> $deploymentStatus,
						'is_verified'		=>	1,
						'verified_by'		=>	Session('userId'),
						'verified_on'		=>	now(),
					];

					$plainPassword	= 	$this->passwordService->generatePassword();
					$hashedPassword = 	Hash::make($plainPassword); // Encrypt password
					
					$resource	=	DB::table('eoi_resource_deployment')->where('deploymentid',$deploymentid)->first();
					if($resource->userid==0)
					{
						$categoryid	=	DB::table('eoi_work_order')->where('orderid',$resource->orderid)->value('categoryid');
						$user_id	=	DB::table('users_tbl')->insertGetId([
											'name'			=>	$resource->name ?? NULL,
											'mobilenumber'	=>	$resource->mobilenumber ?? NULL,
											'email'			=>	$resource->email ?? NULL,
											'password'		=>	$hashedPassword,
											'pass_word'		=>	$plainPassword,
											'isresource'	=>	1,
											'issuper'		=>	1,
											'isactive'		=>	1,
											'usertype'		=>	'RESOURCE'
										]);
						
						DB::table('resource_tbl')->insert([
							'orderid'			=>	$resource->orderid,
							'userid'			=>	$user_id,
							'employee_code'		=>	$resource->employee_code,
							'categoryid'		=>	$categoryid,
							'tierid'			=>	$resource->tierid,
							'sectorid'			=>	$resource->sectorid,
							'positionid'		=>	$resource->positionid,
							'role'				=>	$resource->role,
							'experiencelevel'	=>	$resource->experiencelevel,
							'remuneration'		=>	$resource->remuneration,
							'operating'			=>	$resource->operating,
							'operatingvalue'	=>	$resource->operatingvalue,
							'operatingvalue'	=>	$resource->operatingvalue,
							'tax'				=>	$resource->tax,
							'taxvalue'			=>	$resource->taxvalue,
							'admin'				=>	$resource->admincharge,
							'admincharge'		=>	$resource->adminvalue,
							'deployed_date'		=>	$resource->deployed_date,
							'joining_date'		=>	$resource->deployed_date,
							'deployment_status'	=>	'Active',
							'deploymenttype'	=>	$resource->deploymenttype,
							'duration'			=>	$resource->duration,
							'is_verified'		=>	1,
							'verified_by'		=>	session('userId'),
							'verified_on'		=>	now(),
							'rateid'			=>	$resource->rateid,
							
						]);
						
						DB::table('eoi_resource_deployment')
						->where('deploymentid',$deploymentid)
						->update([
							'userid'	=>	$user_id
						]);
					}
					else
					{
						DB::table('users_tbl')
						->where('userid',$resource->userid)
						->update([
							'name'			=>	$resource->name ?? NULL,
							'mobilenumber'	=>	$resource->mobilenumber ?? NULL,
							'email'			=>	$resource->email ?? NULL,
							'password'		=>	$hashedPassword,
							'pass_word'		=>	$plainPassword,
							'isresource'	=>	1,
							'issuper'		=>	1,
							'isactive'		=>	1,
							'usertype'		=>	'RESOURCE'
						]);

						DB::table('resource_tbl')
						->where('userid',$resource->userid)
						->update([
							'orderid'			=>	$resource->orderid,
							'employee_code'		=>	$resource->employee_code,
							'categoryid'		=>	$categoryid,
							'tierid'			=>	$resource->tierid,
							'sectorid'			=>	$resource->sectorid,
							'positionid'		=>	$resource->positionid,
							'role'				=>	$resource->role,
							'experiencelevel'	=>	$resource->experiencelevel,
							'remuneration'		=>	$resource->remuneration,
							'operating'			=>	$resource->operating,
							'operatingvalue'	=>	$resource->operatingvalue,
							'operatingvalue'	=>	$resource->operatingvalue,
							'tax'				=>	$resource->tax,
							'taxvalue'			=>	$resource->taxvalue,
							'admin'				=>	$resource->admincharge,
							'admincharge'		=>	$resource->adminvalue,
							'deployed_date'		=>	$resource->deployed_date,
							'joining_date'		=>	$resource->deployed_date,
							'deployment_status'	=>	'Active',
							'deploymenttype'	=>	$resource->deploymenttype,
							'duration'			=>	$resource->duration,
							'is_verified'		=>	1,
							'verified_by'		=>	session('userId'),
							'verified_on'		=>	now(),
							'rateid'			=>	$resource->rateid,
							
						]);
					}
				}
				else
				{
					$data = [
						'deployment_status'	=> $deploymentStatus,
					];					
				}

				$exists	=	DB::table('eoi_resource_deployment')
							->where('orderid',$orderid)
							->where('deploymentid',$deploymentid)
							->first();

				$existingData 	= (array) $exists;
				$changedFields 	= [];
				foreach ($data as $field => $newValue)
				{
					$oldValue = $existingData[$field] ?? null;
					if ($oldValue != $newValue)
					{
						$changedFields[$field] = [
							'old' => $oldValue,
							'new' => $newValue,
						];
					}
				}
				if(!empty($changedFields))
				{
					DB::table('eoi_resource_deployment_logs')->insert([
						'orderid'        => $orderid,
						'deploymentid'   => $deploymentid,
						'changed_fields' => json_encode($changedFields),
						'updated_by'     => Session('userId'),
					]);
				}					
				
				DB::table('eoi_resource_deployment')
					->where('orderid', $orderid)
					->where('deploymentid', $deploymentid)
					->update($data);
			}
			
			
			DB::commit();
			return back()->with('success','Deployment veification submitted successfully!');
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
		
    }
	
    public function viewPmDeployment(Request $request,$orderid)
	{
	
		$orderid	=	Crypt::decrypt($orderid);


		$order 		= 	DB::table('eoi_work_order as a')
							->select('a.*','b.departmentname','c.eoinumber')
							->leftjoin('department_tbl as b','b.userid','a.userid')
							->leftjoin('eoi_request as c','c.requestid','a.requestid')							
							->where('a.orderid',$orderid)
							->first();


		if(!$order)
		{
			return back()->with('fail', 'Invalid user detail provided. Please check and try again!')->withInput();
		}
		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

		$detail 	= 	[];
		
		if($order->categoryid==2)
		{
			$detail		=	$this->deploymentService->getCsfDeploymentData($order->orderid);
			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		}
		if($order->categoryid==1)
		{
			$detail		=	$this->deploymentService->getAwdDeploymentData($order->orderid);

			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
			
		}
		return view('admin/manager/deployment_detail', compact('order', 'detail','vendor'));
    }

    public function getPmWorkOrderData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		$vendid 		=	$request->input('vendorid') ?? 0;
		$categoryid 	=	$request->input('categoryid') ?? 0;
		$request_id 	=	$request->input('request_id') ?? 0;
		$order_type 	=	$request->input('order_type') ?? NULL;

		$is_expired		=	$request->input('is_expired');


		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
        $data 			= 	DB::table('eoi_work_order as a')
								->select('a.*','b.jobcategory','c.companyname','c.contactperson','f.departmentname',DB::raw('CASE WHEN pd.orderid IS NULL THEN 1 ELSE 0 END as isDeployed'),DB::raw("(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.deployment_status='Active') as resourcedeployed"),DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid) as totalresources'))
								->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
								->leftJoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
								->leftJoin('pending_deployment as pd', 'pd.orderid', '=', 'a.orderid')
								->leftJoin('eoi_request as e', 'e.requestid', '=', 'a.requestid')
								->leftJoin('department_tbl as f', 'f.departmentid', '=', 'a.department_id')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.refrence', 'like', '%' . $pagesearch . '%')
										  ->orWhere('e.eoinumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.orderno', 'like', '%' . $pagesearch . '%')
										  ->orWhere('f.departmentname', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($vendid!=0,function($query) use ($vendid){
									return $query->where('a.vendorid','=',$vendid);
								})
								->when($categoryid!=0,function($query) use ($categoryid){
									return $query->where('a.categoryid','=',$categoryid);
								})
								->when($request_id!=0,function($query) use ($request_id){
									return $query->where('a.requestid','=',$request_id);
								})
								->when($order_type==1,function($query) use ($order_type){
									return $query->where('a.requestid','!=',0);
								})
								->when($order_type==2,function($query) use ($order_type){
									return $query->where('a.requestid','=',0);
								})
								->where('a.department_id','=',session('departmentId'))
								->when($is_expired==3,function($query){
									return $query->whereDate('a.workorderduedate','>=',today())
									             ->where('a.isActiveOrder',1)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==1,function($query){
									return $query->whereDate('a.workorderduedate','<',today())
									             ->where('a.isActiveOrder',1)
												 ->where('a.isExtended',0)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==2,function($query){
									return $query->whereBetween('a.workorderduedate', [date('Y-m-d'),date('Y-m-d', strtotime('+60 days'))])
									             ->where('a.isActiveOrder',1)
												 ->where('a.isExtended',0)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==4,function($query){
									return $query->where('a.isExtended',1);
								})
								->when($is_expired==5,function($query){
									return $query->where('a.isCancelled',1);
								})
								->orderBy('a.workorderduedate')
								->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		foreach($data as $dt)
		{
			$dt->workorderamount=	$this->formatIndianCurrency($dt->workorderamount);
			$dt->expiring 		= 	Carbon::parse($dt->workorderduedate)->format('d-m-Y');
			$dt->days_to_expire = 	Carbon::now()->diffInDays(Carbon::parse($dt->expiring), false);
		}
		return view('admin/manager/ajaxpages/workorderTable', ['data' => $data]);

    }

    public function pmWorkOrderList(Request $request)
	{
		
        Session::put('adminmenu','pmorderinvoices');
		Session::put('adminsubmenu','pmworkorder');
		Session::put('menid',117);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=117 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$vendors	=	DB::table('eoi_work_order as a')
						->select('c.vendorid','c.companyname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->where('b.userid',Session('userId'))
						->orderby('c.companyname')
						->distinct()
						->get();
		
		$eoiOrders		=	DB::table('eoi_request as a')
						->select('a.requestid','a.engagementname')
						->join('eoi_work_order as b','b.requestid','=','a.requestid')
						->where('b.department_id',session('departmentId'))
						->where('a.iscancelled',0)
						->where('a.isClosed',0)
						->where('b.isActiveOrder',1)
						->where('b.workorderduedate','>=',today())
						->get();
		
		
		$directOrders	=	DB::table('eoi_work_order')
							->select('orderid','project_name')
							->where('department_id',session('departmentId'))
							->where('requestid',0)
							->get();
		
		$search		=	"";
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
        return view('admin/manager/workorder_list',compact('search','vendors','category','eoiOrders','directOrders'));
    }

    public function departmentWorkOrdersList(Request $request,$search)
	{
	
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=103 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$vendors	=	DB::table('vendor_tbl')->orderby('companyname')->get();
		
		DB::table('eoi_work_order as a')
		->whereIn('a.requestid', function($query) use ($userId) {
		$query->select('b.requestid')
			  ->from('eoi_request as b')
			  ->where('b.userid', $userId);
		})
		->where('a.orderno',$search)
		->update([
		'departmentview' => 1,
		'department_marked' => now()
		]);		
		
        return view('admin/department/workorder_list',compact('search','vendors'));
    }

    public function addCommitteeMember(Request $request){
        Session::put('adminmenu','addpmcommittee');
		Session::put('adminsubmenu','addpmcommittee');
		Session::put('menid',116);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=116 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions',$actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/manager/committee_add',compact('token'));
    }
    public function storeCommitteeMember(Request $request,$recordid)
	{        
        $rules = [
            'name'			=> 'required|max:100|regex:/^[a-zA-Z\s.]+$/',
			'mobilenumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
			'designation'	=> 'required|max:100|regex:/^[a-zA-Z\s.]+$/',
			'department'	=> 'nullable|max:150|regex:/^[a-zA-Z\s.]+$/',
			'remark' 		=> 'nullable|max:300|regex:/^[a-zA-Z\s\.,!?\-_]+$/',
			'email'			=> 'required|email:rc,dns',
        ];

        $messages = [
            'name.required'			=> 'Name is required',
			'name.max'				=> 'Invalid name',
			'name.regex'			=> 'Invalid name',
			'mobilenumber.regex' 	=> 'Invalid mobile number',
			'email.email' 			=> 'Invalid email address',
			'designation.required' 	=> 'Designation is required',
			'designation.max' 		=> 'Maximum length is 100',
			'designation.regex' 	=> 'Invalid designation name',
			'department.max' 		=> 'Maximum length is 150',
			'department.regex' 		=> 'Invalid department name',
			'remark.max' 			=> 'Maximum length is 300',
			'remark.regex' 			=> 'Remark can only contain letters, spaces, and . , ! ? - _ characters.',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

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
				DB::table('committee_member')->insert([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'] ?? NULL,
					'designation'	=>	$validatedData['designation'] ?? NULL,
					'department'	=>	$validatedData['department'] ?? NULL,
					'remark'		=>	$validatedData['remark'] ?? NULL,
					'userid'		=>	Session::get('userId'),
					'usertype'		=>	'PROJECT MANAGER',
					'creationdate'	=>	now(),
					'email'			=>	$validatedData['email']
				]);
				DB::commit();
				return back()->with('success','Committee member detail stored successfully!');
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			DB::beginTransaction();
			try
			{
				DB::table('committee_member')->where('memberid',Crypt::decrypt($recordid))->update([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'] ?? NULL,
					'designation'	=>	$validatedData['designation'] ?? NULL,
					'department'	=>	$validatedData['department'] ?? NULL,
					'remark'		=>	$validatedData['remark'] ?? NULL,
					'email'			=>	$validatedData['email'] ?? NULL,
				]);
				DB::commit();

				$token	= rand('100000','999999').''.time();
				Session::put('form_token', $token);
				return redirect()->route('add.pmcommittee')->with('success', 'Committee member updated successfully.');
				//return view('admin/manager/committee_add',compact('token'));
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
    }
    public function getCommitteeMemberData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page', 1);
        $data = DB::table('committee_member as a')
				->select('a.*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.name','like','%'.$pagesearch.'%')
								 ->orwhere('a.designation','like','%'.$pagesearch.'%')
								 ->orwhere('a.mobilenumber','like','%'.$pagesearch.'%')
								 ->orwhere('a.email','like','%'.$pagesearch.'%');
                })
                ->where('a.usertype','PROJECT MANAGER')
				->where('a.isdeleted',0)
				->where('a.userid',Session('userId'))
				->orderBy('a.name')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/manager/ajaxpages/committeememberTable', ['data' => $data]);

    }
    public function editCommitteeMember($recordid)
	{
		$data = DB::table('committee_member')->where('memberid','=',Crypt::decrypt($recordid))->first();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/manager/committee_edit',compact('data','token'));
    }
    public function deleteCommitteeMember($recordid)
	{
	
        $res = DB::table('committee_member')
				->where('memberid',Crypt::decrypt($recordid))
				->update([
					'isdeleted'	=>	1
				]);

        return response()->json(['success' => true,'fail'=>'']);
	}
	
    public function pmEoiList(Request $request,$eoi_status=null)
	{
		
        Session::put('adminmenu','pmeois');
		Session::put('adminsubmenu','pmeoilist');
		Session::put('menid',113);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=113 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$eoi_status	=	$eoi_status;
        return view('admin/manager/pmeoi_list',compact('category','eoi_status'));
    }
	
	
    public function deletePmEois($recordid)
	{
	
		$res = DB::delete('delete from eoi_temp_requirement WHERE recordid=?',[Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
    public function deletePmRequestEois(Request $request,$recordid)
	{
		$requestid	=	DB::table('eoi_request_detail')->where('recordid',Crypt::decrypt($recordid))->value('requestid');
		
		$categoryid	=	DB::table('eoi_request')->where('requestid',$requestid)->value('categoryid');
		
		if($categoryid==2)
		{
			$record		=	DB::table('eoi_request_detail as a')
							->select('a.requestid','b.sectorname','c.consultantposition')
							->leftjoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftjoin('position_tbl as c','c.positionid','=','a.positionid')
							->where('recordid',Crypt::decrypt($recordid))
							->first();
			
			
			$counts	=	DB::table('eoi_request_detail')->where('requestid',$record->requestid)->count();
			
			if($counts>1)
			{
				$res = DB::delete('delete from eoi_request_detail WHERE recordid=?',[Crypt::decrypt($recordid)]);

				if($res)
				{
					$userId	=	$request->session()->get('userId');
					DB::table('log_eoi_updates')->insert([
						'requestid'		=>	$record->requestid,
						'particular'	=>	'Resource deleted for the position '.$record->consultantposition.' in the '.$record->sectorname.' sector on '.date('d\-m\-Y, h:i A'),
						'updatedon'		=>	now(),
						'updatedby'		=>	$userId,
					]);

					DB::table('eoi_request')
					->where('requestid',$record->requestid)
					->update([
						'isupdated'	=>	1
					]);
					
					return response()->json(['success' => true,'fail'=>'']);
				}
				else
				{
					return back()->with('fail','Record id does not exists!');
				}
			}
			else
			{
				return response()->json(['success' => true,'fail'=>'An EoI must contain at least one resource. The last resource detail cannot be deleted.']);
			}
		}
		if($categoryid==1)
		{
			$record	=	DB::table('eoi_request_detail as a')
							->where('recordid',Crypt::decrypt($recordid))
							->first();
			
			
			$counts	=	DB::table('eoi_request_detail')->where('requestid',$record->requestid)->count();
			
			if($counts>1)
			{
				$res = DB::delete('delete from eoi_request_detail WHERE recordid=?',[Crypt::decrypt($recordid)]);

				if($res)
				{
					$userId	=	$request->session()->get('userId');
					
					DB::table('log_eoi_updates')->insert([
						'requestid'	=>	$record->requestid,
						'particular'=>	'Resource deleted for the position '.$record->role.' on '.date('d\-m\-Y, h:i A'),
						'updatedon'	=>	now(),
						'updatedby'	=>	$userId,
					]);

					DB::table('eoi_request')
					->where('requestid',$record->requestid)
					->update([
						'isupdated'	=>	1
					]);
					
					return response()->json(['success' => true,'fail'=>'']);
				}
				else
				{
					return back()->with('fail','Record id does not exists!');
				}
			}
			else
			{
				return response()->json(['success' => true,'fail'=>'An EoI must contain at least one resource. The last resource detail cannot be deleted.']);
			}
		}
    }
	
    public function clearPmEois(Request $request)
	{
		if(!session('departmentId')) {
			return redirect('dashboard');
		}
		
		$userId	= $request->session()->get('userId');
		DB::delete('delete from eoi_temp_requirement WHERE userid=?',[$userId]);

		return response()->json(['status' => 200,'message'=>'RECORD CLEARED SUCCESSFULLY!']);
    }

    public function getPmEoiRequestData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		$categoryid 	=	$request->input('categoryid') ?? 0;
		$eoi_status 	=	$request->input('eoi_status');
		
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize',100);
		$currentPage= 	$request->input('page', 1);
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','d.jobcategory')
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
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->when($pagesearch!='',function($query) use ($pagesearch){
								return $query->where('a.projecttitle','like','%'.$pagesearch.'%')
											 ->orwhere('a.eoinumber','like','%'.$pagesearch.'%');
							})
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('a.categoryid','=',$categoryid);
							})
							->when($departmentId!=0,function($query) use ($departmentId,$userId){
								return $query->where('a.userid','=',$userId);
							})
							->when($eoi_status!='',function($query) use ($eoi_status){
								$status = (int)$eoi_status;
								if ($status===1)
								{
									return $query->where('a.eoistatus','=',$status);
								}
								elseif($status===2)
								{
									return $query->where('a.eoistatus','=',$status);
								}
								elseif($status===3)
								{
									return $query->where('a.eoistatus','=',$status);
								}
								else
								{
									return $query->where('a.eoistatus','=',$status);
								}
							})
							
							->orderBy('a.creationdate','DESC')
							->paginate($pagesize,['*'],'page',$currentPage);
		
		foreach($data as $rec)
		{
			$rec->istrue	=	Carbon::parse($rec->deadlinedate)->gte(Carbon::today());
		}
		//$isFutureOrToday = Carbon::parse($item->deadlinedate)->gte(Carbon::today());
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/manager/ajaxpages/pmeoirequestTable', ['data' => $data]);

    }
	public function eoiPreviewPrintable(Request $request,$recordid)
	{

		
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');

		
		$data	=	DB::table('eoi_request as a')
						->select('a.*','b.jobcategory')
						->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
						->where('a.userid','=',$userId)
						->where('a.requestid','=',Crypt::decrypt($recordid))
						->first();
		
		
		
		

		$department	=	DB::table('department_tbl')->where('departmentid',$departmentId)->first();
		$user		=	DB::table('users_tbl')->where('userid',$userId)->first();


		if($data->categoryid==2)
		{
			$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
			
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',Crypt::decrypt($recordid))->value('rateid');
			$html = [];
			foreach($tiers as $tier)
			{
				$detail		=	$this->priceService->getCsfPrice(Crypt::decrypt($recordid),$data->categoryid,$tier->tierid,$rateid);
				break;
			}
			$tier_html = view('admin.viewpages.csf_tier_html_without_price',['data'=>$data,'detail'=>$detail])->render();
			
		
			return view('admin/manager/ajaxpages/eoiPreviewPrintable', [
				'data'				=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'categoryid'		=>	$data->categoryid,
				'tier1_html'		=>	$tier_html,
			]);
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
			
			//$tier1_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			$tier1_html = view('admin.viewpages.awd_tier_html_without_price',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
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
			
			//$tier2_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			$tier2_html = view('admin.viewpages.awd_tier_html_without_price',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END*/
			return view('admin/manager/ajaxpages/eoiPreviewPrintable', [
				'data'				=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'tier1_html'		=>	$tier1_html,
				'tier2_html'		=>	$tier2_html
			]);
			
		}
	}
	
	public function printPreviewPmEOI(Request $request,$recordid)
	{
		
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');

		
		$data	=	DB::table('eoi_request as a')
						->select('a.*','b.jobcategory')
						->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
						->where('a.userid','=',$userId)
						->where('a.requestid','=',Crypt::decrypt($recordid))
						->first();
		
	
		$admincharge	=	DB::table('pricing_tbl')
								->where('categoryid',$data->categoryid)
								->value('admincharge');

		$department	=	DB::table('department_tbl')->where('departmentid',$departmentId)->first();
		$user		=	DB::table('users_tbl')->where('userid',$userId)->first();


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
			
			$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
			$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
			$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
			
			$tier1_html = view('admin.viewpages.csf_tier_table',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
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
			
			$tier2_html = view('admin.viewpages.csf_tier_table',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END*/

			return view('admin/manager/ajaxpages/eoiPrintingPreview', [
				'eoidata'			=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'categoryid'		=>	$data->categoryid,
				'tier1_html'		=>	$tier1_html,
				'tier2_html'		=>	$tier2_html
			]);
		}
		if($data->categoryid==1)
		{
			/* FOR TIER 1 DATA START*/
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',1)->first();
			$detail		=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,1);
						
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
			
			$tier1_html = view('admin.viewpages.awd_tier_table',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			/* FOR TIER 1 DATA END*/

			/* FOR TIER 2 DATA START*/
			$pricing=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',2)->first();
			$detail		=	$this->priceService->getAwdPrice($data->requestid,$data->categoryid,2);
						
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
			
			$tier2_html = view('admin.viewpages.awd_tier_table',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail])->render();
			
			/* FOR TIER 2 DATA END*/

			return view('admin/manager/ajaxpages/eoiPrintingPreview', [
				'eoidata'			=>	$data,
				'department'		=>	$department,
				'user'				=>	$user,
				'categoryid'		=>	$data->categoryid,
				'tier1_html'		=>	$tier1_html,
				'tier2_html'		=>	$tier2_html,
			]);
			
		}
		
	}

	public function printEOI(Request $request)
	{
		if(!session('departmentId')) {
			return redirect('dashboard');
		}
		
        $rules = [
			'projectobjective' 	=> 'nullable',
			'aboutproject' 		=> 'nullable',
			'scope' 			=> 'nullable',
			'anyother' 			=> 'nullable',
        ];

        $messages = [
        ];
		$validatedData 	= $request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		$admincharge	=	DB::table('pricing_tbl')
								->where('categoryid',session('categoryid'))
								->where('tierid',session('tierid'))
								->value('admincharge');
		
		$data	=	DB::table('eoi_temp_requirement as a')
						->select('a.recordid','a.qualification','a.duration','a.budget','a.admincharge','a.total','a.role','a.experience','a.remark','b.jobcategory','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition')
						->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
						->leftJoin('tiermaster_tbl as c','c.tierid','=','a.tierid')
						->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
						->leftJoin('work_experience as f','f.experienceid','=','e.experienceid')
						->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as h','h.positionid','=','e.positionid')
						->where('a.userid','=',$userId)
						->get();

		$exists 		= 	DB::table('eoi_temp_requirement')->where('userid', $userId)->exists();
		if($exists)
		{
			DB::table('eoi_temp_requirement')->where('userid',$userId)->update([
				'scopeofwork'		=>	$validatedData['scope'],
				'aboutproject'		=>	$validatedData['aboutproject'] ?? '',
				'anyother'			=>	$validatedData['anyother'] ?? '',
				'projectobjective'	=>	$validatedData['projectobjective'] ?? '',
			]);
		} 
		else
		{
			\Log::warning("No eoi_temp_requirement row found for userid: $userId");
		}		
		$department	=	DB::table('department_tbl')->where('departmentid',$departmentId)->first();
		$user		=	DB::table('users_tbl')->where('userid',$userId)->first();
		
		return view('admin/department/ajaxpages/eoiPrintPreview', [
			'scope'				=>	$validatedData['scope'] ?? '',
			'aboutproject'		=>	$validatedData['aboutproject'] ?? '',
			'projectobjective'	=>	$validatedData['projectobjective'] ?? '',
			'anyother'			=>	$validatedData['anyother'] ?? '',
			'eoidata'			=>	$data,
			'department'		=>	$department,
			'user'				=>	$user,
			'categoryid'		=>	session('categoryid'),
			'admincharge'		=>	$admincharge
		]);
	}
	public function saveOnlyEOI(Request $request)
	{
		if(!session('departmentId') || !session('isProjectManager'))
		{
			return redirect('dashboard');
		}
		
        $rules = [
			'projecttitle' 		=> ['required', 'regex:/^[A-Za-z0-9 .&]+$/'],
			'projectduration'	=> 'required|numeric',
			'projectobjective'	=> 'required',
			'scopeofwork' 		=> 'required',
			'anyother' 			=> 'nullable',

			
        ];

        $messages = [
            'projecttitle.required'			=> 'Project title is required',
			'projecttitle.regex'			=> 'Invalid project title provided',
			'projectduration.required'		=> 'Project duration is required',
			'projectduration.numeric'		=> 'Invalid project duration',
			'scopeofwork.required'		=> 	'Scope of work is required',
			'projectobjective.required'	=> 	'Project objective is required',
        ];
		$validatedData 	= $request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		
		
		$data	=	DB::table('eoi_temp_requirement')->where('userid','=',$userId)->get();

		$exists 		= 	DB::table('eoi_temp_requirement')->where('userid', $userId)->exists();
		if($exists)
		{
			DB::table('eoi_temp_requirement')->where('userid',$userId)->update([
				'projecttitle'		=>	$validatedData['projecttitle'],
				'projectduration'	=>	$validatedData['projectduration'],
				'projectobjective'	=>	$validatedData['projectobjective'],
				'scopeofwork'		=>	$validatedData['scopeofwork'],
				'anyother'			=>	$validatedData['anyother'] ?? '',
			]);

			return response()->json([
				'message' => 'Record saved successfully',
				'status' => 200,
			], 200);
		} 
		else
		{
			DB::table('eoi_temp_requirement')->insert([
				'projecttitle'		=>	$validatedData['projecttitle'],
				'projectduration'	=>	$validatedData['projectduration'],
				'projectobjective'	=>	$validatedData['projectobjective'],
				'scopeofwork'		=>	$validatedData['scopeofwork'],
				'anyother'			=>	$validatedData['anyother'] ?? '',
				'userid'			=>	$userId
			]);
			return response()->json([
				'message' => 'Record saved successfully',
				'status' => 200,
			], 200);
			
		}		
		
	}

    public function pmViewDraft(Request $request,$recordid)
	{
		$userId			=	$request->session()->get('userId');
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->where('a.requestid','=',$requestid)
							->where('a.userid','=',$userId)
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
		
		$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
		$token			=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$members 	= 	DB::table('committee_member')
						->whereNotIn('memberid', function ($query) use ($requestid) {
							$query->select('committeeid')
								  ->from('eoi_request_committee')
								  ->where('requestid', $requestid);
						})
						->where('isdeleted',0)
						->where('userid',Session::get('userId'))
						->where('usertype','PROJECT MANAGER')
						->get();
		
		
		$committee	=	DB::table('eoi_request_committee as a')
						->select('b.memberid','b.name','b.mobilenumber','b.designation','b.remark','b.usertype','b.email','b.department')
						->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
						->where('a.requestid',$requestid)
						->orderby('b.name')
						->get();
		
		return view('admin/manager/pm_viewdraft',compact('data','token','attachments','members','committee','html'));

    }

	public function addPmEoiCommitteeMember(Request $request)
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
		$requestid	=	Crypt::decrypt($request->input('requestid'));

		try
		{
			DB::beginTransaction();

			DB::table('eoi_request_committee')->insert([
				'requestid'   	=> $requestid,
				'committeeid'  	=> $memberid,
				'createdby'  	=> Session('userId'),
				'creationdate'  => now()
			]);


			$committee	=	DB::table('eoi_request_committee as a')
								->select('a.memberid','a.requestid','a.committeeid','a.createdby','a.creationdate','b.usertype','b.name','b.mobilenumber','b.designation','b.remark')
								->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
								->where('a.requestid',$requestid)
								->get();
			
			$count	=	DB::table('eoi_request_committee')->where('requestid',$requestid)->count();
			if($count>=5)
			{
				DB::table('eoi_request')->where('requestid',$requestid)->where('iscommittee',0)->update([
					'iscommittee'	=>	1
				]);
			}
			
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

    public function generatePmConfirmationOtp(Request $request)
	{
        $rules = [
            'requestid'	=>	'required',
        ];

        $messages = [
            'requestid.required'	=>	'REQUEST ID IS REQUIRED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		
		try
		{
			$otp	=	rand(100001,999999);
			
			$userId			=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->value('userid');
			$mobilenumber	=	DB::table('users_tbl')->where('userid',$userId)->value('mobilenumber');
			$officialemail	=	DB::table('department_tbl')->where('userid',$userId)->value('officialemail');
			/*
			if(!is_null($mobilenumber))
			{
			*/
				if($officialemail!='')
				{
					if($this->featureService->isEmailEnabled())
					{
						Mail::to($officialemail)->send(new SendOTPMail($otp));
					}
				}
				else
				{
					$otp	=	"555555";
				}
				
				$isSent = $this->smsService->pushMessage($mobilenumber,$otp,'OTP',0,'');
				if(!$isSent)
				{
					return response()->json([
						'message' => 'OTP could not be sent. Please try again',
						'status' => 400,
					], 400);
				}
				DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->update([
					'acceptanceotp'	=>	$otp
				]);
				return response()->json([
					'message' => 'OTP Generated Successfully',
					'status' => 200,
				], 200);
			/*
			}
			else
			{
				return response()->json([
					'message' => 'OTP could not be generated. Please try again!',
					'status' => 400,
				], 400);								
			}
			*/
		}
        catch (QueryException $e) 
        {
			Log::error('Error: ' . $e->getMessage());
            return response()->json(['message'=>'We encountered a technical issue while processing your request. Please try again later.','status'=>400], 400);
        }
		
	}

    public function verifyPmConfirmationOtp(Request $request)
	{
		$request->merge([
			'otp' => $request->first . $request->second . $request->third . $request->fourth . $request->fifth . $request->sixth,
		]);	
        $rules = [
            'reqid' => 'required',
			'otp'    	=> 'required|min:6',
        ];
		
        $messages = [
            'reqid.required'	=> 'REQUEST ID IS REQUIRED',
			'otp.required' 			=> 'OTP IS REQUIRED',
			'otp.min' 				=> 'INVALID OTP PROVIDED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		
		try
		{
		
			$eoi	=	DB::table('eoi_request')
							->where('requestid',Crypt::decrypt($validatedData['reqid']))
							->where('acceptanceotp',$validatedData['otp'])
							->first();
			if($eoi)
			{
				$rows	=	DB::table('eoi_request')
								->where('requestid',Crypt::decrypt($validatedData['reqid']))
								->where('acceptanceotp',$validatedData['otp'])
								->where('eoistatus',1)
								->update([
									'acceptancedatetime'	=>	now(),
									'eoistatus'				=>	2,
									'confirmedby'			=>	session('userId'),
									'confirmedon'			=>	now(),
								]);
				if($rows>0)
				{
					$url = route('pm.eoilist');
					return response()->json(['redirect_url' => $url,'success'=>'EoI DRAFT CONFIRMATION HAS BEEN DONE!']);
				}
				else
				{
					return response()->json(['status'=>400,'message'=>'VERIFICATION FAILD!']);
				}
			}
			else
			{
				return response()->json(['status'=>400,'message'=>'Invalid OTP']);
			}
		}
        catch (QueryException $e) 
        {
			Log::error('Error: ' . $e->getMessage());
            return response()->json(['message'=>'We encountered a technical issue while processing your request. Please try again later.','status'=>400], 400);
        }
		
	}
    public function preBidPmReplyData(Request $request,$recordid)
	{
		
		$userId		= 	$request->session()->get('userId');
		$requestid	=	Crypt::decrypt($recordid) ?? 0;
		
		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		
	
		/* OLD QUERIES
		$data	=	DB::table('eoi_request_prebid_forwarded')
						->where('requestid', $requestid)
						->where(function($q) use ($userId) {
							$q->where('fromuserid', $userId)
							  ->orWhere('touserid', $userId);
						})
						->orderBy('creationdate')
						->get();		
		
		$exists	=	DB::table('eoi_request_prebid_broadcast')->where('requestid',$requestid)->exists();
		
		$formats=	DB::table('prebid_formats')->where('recordid',1)->first();
		OLD QUERIES CLOSED */
		
		$prebid		=	DB::table('eoi_request_prebid')
						->where('requestid',$eoi->requestid)
						->where('isprebid',1)
						->first();
		
		$forward	=	DB::table('eoi_request_prebid_forwarded as a')
						->select('a.*')
						->join('eoi_request as b','b.userid','=','a.touserid')
						->where('a.requestid',$eoi->requestid)
						->where('a.touserid',$userId)
						->where('a.isprebid',1)
						->first();
		
		$data		=	DB::table('eoi_request_prebid_query')
							->select('questionid','clause_reference','clause_detail','queries_with_justification','answer')
							->where('requestid',$requestid)
							->where('isprebid',1)
							->orderBy('questionid')
							->get();
						
		$exists		=	DB::table('eoi_request_prebid_broadcast')
						->where('requestid',$requestid)
						->where('isprebid',1)
						->exists();

		$replied	=	DB::table('eoi_request_prebid_forwarded as a')
						->select('a.*')
						->join('eoi_request as b','b.userid','=','a.fromuserid')
						->where('a.requestid',$eoi->requestid)
						->where('a.fromuserid',$userId)
						->where('a.isprebid',1)
						->first();

		
		$formats=	DB::table('prebid_formats')->where('recordid',1)->first();
		
		//return view('admin/manager/prebidreply_form',['eoi'=>$eoi,'data'=>$data,'exists'=>$exists,'formats'=>$formats]);
		return view('admin/manager/prebidreply_form',[
			'eoi'		=>	$eoi,
			'prebid'	=>	$prebid,
			'forward'	=>	$forward,
			'replied'	=>	$replied,
			'data'		=>	$data,
			'exists'	=>	$exists,
			'formats'	=>	$formats
		]);

    }

    public function deptRateList(Request $request){
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','deptratelist');
		Session::put('adminsubmenu','deptratelist');
		Session::put('menid',84);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=84 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$category	=	DB::table('jobcategory_tbl')->where('categoryid',2)->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		
		$sector	=	DB::table('sector_tbl')->orderby('sectorname')->get();
		$position	=	DB::table('position_tbl')->orderby('consultantposition')->get();
        return view('admin/department/dept_ratelist',compact('category','tier','sector','position'));
		
    }

    public function getDeptRatelistData(Request $request)
	{
		$tierid 		=	intval($request->input('tierid'));
		$sectorid		=	intval($request->input('sectorid'));
		$positionid		=	intval($request->input('positionid'));
		
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page',1);

        $data = DB::table('remuneration_tbl as a')
				->select('a.*','b.tiername','c.workexperience','d.jobcategory','e.sectorname','f.consultantposition','g.operatingmargin','g.tax','g.admincharge')
				->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
				->leftJoin('work_experience as c','c.experienceid','=','a.experienceid')
				->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
				->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
				->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
				->leftJoin('pricing_tbl as g', function($join) {
					$join->on('g.categoryid', '=', 'a.categoryid')
						 ->on('g.tierid', '=', 'a.tierid');
				})
                ->when($tierid!=0,function($query) use ($tierid){
                    return $query->where('a.tierid','=',$tierid);
                })
                ->when($sectorid!=0,function($query) use ($sectorid){
                    return $query->where('a.sectorid','=',$sectorid);
                })				
                ->when($positionid!=0,function($query) use ($positionid){
                    return $query->where('a.positionid','=',$positionid);
                })				
				->where('a.categoryid','=',2)
                ->orderBy('a.remuneration')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/department/ajaxpages/deptratelistTable',['data'=>$data]);
	}


    public function pmempanelVendors(Request $request)
	{	
        Session::put('adminmenu','empanelvendors');
		Session::put('adminsubmenu','empanelvendors');
		Session::put('menid',114);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=114 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
        return view('admin/manager/vendor_list',compact('tier','category'));
		
    }
    public function getPmVendorData(Request $request)
	{
		$tierid 		=	intval($request->input('tierid')) ?? 0;
		$categoryid		=	intval($request->input('categoryid')) ?? 0;
	
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page',1);

        $data = DB::table('users_tbl as a')
				->select('a.userid','a.name','a.mobilenumber','a.email','c.tiername')
				->leftJoin('vendor_tbl as b','b.userid','=','a.userid')
				->leftJoin('tiermaster_tbl as c','c.tierid','=','b.tierid')
                ->when($tierid!=0,function($query) use ($tierid){
                    return $query->where('b.tierid','=',$tierid);
                })
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('b.categoryid','=',$categoryid);
                })
				->whereNull('a.isSubVendor')
				->where('a.isvendor','=',1)
                ->orderBy('b.tierid')
				->orderBy('a.name')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/manager/ajaxpages/pmvendorsTable',['data'=>$data]);
	}

    public function deptViewResumes(Request $request,$recordid)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		
		$userId			=	$request->session()->get('userId');
        Session::put('adminmenu','depteoilist');
		Session::put('adminsubmenu','depteoilist');
		
		$requestid 	=	Crypt::decrypt($recordid);

        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory','e.tiername')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->leftJoin('department_tbl as c','c.userid','=','a.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
							->leftJoin('tiermaster_tbl as e','e.tierid','=','a.tierid')
							->where('requestid','=',$requestid)
							->first();

		$today 		= 	Carbon::today()->toDateString();

		$vendors	=	DB::table('eoi_request_participation as a')
						->select(
							'a.participationid',
							'a.participationdate',
							'b.companyname',
							'b.contactperson',
							'b.designation',
							'b.officialemail',
							'b.officelocation',
							'a.reviewstatus',
							'a.interviewdate',
							'a.isenabled',
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
						->leftJoin('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
						->where('a.requestid', $requestid)
						->get();

				$token		=	rand('100000','999999').''.time();
				Session::put('form_token', $token);
				
				return view('admin/department/uploaded_resumes',compact('data','token','vendors'));
	}

    public function deptViewParticipations(Request $request,$recordid)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		
		$participationid	=	Crypt::decrypt($recordid);
		$participation		=	DB::table('eoi_request_participation')->where('participationid',$participationid)->first();

		$floated			=	DB::table('eoi_request_floated')->where('floatid',$participation->floatid)->first();

		$vendor	=	DB::table('vendor_tbl as a')
							->select('a.*','b.name','b.mobilenumber','b.email')
							->leftJoin('users_tbl as b','b.userid','=','a.userid')
							->where('a.vendorid',$participation->vendorid)
							->first();
		
		$categoryid	=	DB::table('eoi_request')->where('requestid',$participation->requestid)->value('categoryid');
		
		if($categoryid==1)
		{
			$requestdetail	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.tierid','a.role','a.qualification','a.requirednumber','a.duration','b.tiername','c.experiencelevel','d.workexperience')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->leftjoin('remuneration_tbl as c','c.remunerationid','a.remunerationid')
									->leftjoin('work_experience as d','d.experienceid','c.experienceid')
									->where('a.requestid',$participation->requestid)
									->orderby('a.recordid')
									->get();
			foreach($requestdetail as $req)
			{
				$req->resumes	=	DB::table('eoi_request_detail_resume as a')
										->select('a.resumeid','a.name','a.uploaddate','a.resume','a.reviewstatus','a.remark')
										->where('a.recordid',$req->recordid)
										->where('a.vendorid',$participation->vendorid)
										->get();
			}
		}

		if($categoryid==1)
		{
			$requestdetail	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.tierid','a.role','a.qualification','a.requirednumber','a.duration','b.tiername','c.experiencelevel','d.workexperience')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->leftjoin('remuneration_tbl as c','c.remunerationid','a.remunerationid')
									->leftjoin('work_experience as d','d.experienceid','c.experienceid')
									->where('requestid',$participation->requestid)
									->orderby('a.recordid')
									->get();
			foreach($requestdetail as $req)
			{
				$req->resumes	=	DB::table('eoi_request_detail_resume as a')
										->select('a.resumeid','a.name','a.uploaddate','a.resume','a.reviewstatus','a.remark')
										->where('a.recordid',$req->recordid)
										->where('a.isordered',0)
										->where('a.vendorid',$participation->vendorid)
										->get();
			}
		}
		
		if($categoryid==2)
		{
			$requestdetail	=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.tierid','a.experience','a.requirednumber','a.duration','b.tiername','d.sectorname','e.consultantposition','a.remark')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->leftjoin('remuneration_tbl as c','c.remunerationid','a.remunerationid')
									->leftjoin('sector_tbl as d','d.sectorid','c.sectorid')
									->leftjoin('position_tbl as e','e.positionid','c.positionid')
									->where('a.requestid',$participation->requestid)
									->orderby('a.recordid')
									->get();
			foreach($requestdetail as $req)
			{
				$req->resumes	=	DB::table('eoi_request_detail_resume as a')
										->select('a.resumeid','a.name','a.uploaddate','a.resume','a.reviewstatus','a.remark')
										->where('a.recordid',$req->recordid)
										->where('a.isordered',0)
										->where('a.vendorid',$participation->vendorid)
										->get();
			}			
		}
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/department/dept_viewresumes',compact('vendor','token','participation','categoryid','requestdetail','floated'));

    }

    public function preBidForwardList(Request $request,$recordid)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}		
		
		$requestid	=	Crypt::decrypt($recordid);

		$results	= 	DB::table('eoi_request_prebid as a')
							->select('a.*','c.eoinumber','c.projecttitle')
							->leftjoin('eoi_request as c','c.requestid','=','a.requestid')
							->where('a.requestid',$requestid)
							->whereExists(function ($query) {
								$query->select(DB::raw(1))
									  ->from('eoi_request_prebid_forwarded as b')
									  ->whereRaw('b.queryid = a.queryid');
							})
							->get();

		$userId			=	$request->session()->get('userId');
		
		//$query		=	DB::table('eoi_request_prebid_forwarded')->where('userid',$queryid)->first();
		
		return view('admin/department/prebidforward_list',compact('results'));
    }

    public function preBidReplyData(Request $request,$recordid)
	{
		
		$userId		= 	$request->session()->get('userId');
		$requestid	=	Crypt::decrypt($recordid) ?? 0;
		
		$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
		
	

		$data	=	DB::table('eoi_request_prebid_forwarded')
						->where('requestid', $requestid)
						->where(function($q) use ($userId) {
							$q->where('fromuserid', $userId)
							  ->orWhere('touserid', $userId);
						})
						->orderBy('creationdate')
						->get();		
		
		$exists	=	DB::table('eoi_request_prebid_broadcast')->where('requestid',$requestid)->exists();
		
		return view('admin/department/prebidreply_form',['eoi'=>$eoi,'data'=>$data,'exists'=>$exists]);

    }

    public function storePmReply(Request $request)
	{           
        $rules = [
            'requestid'		=> 'nullable',
			'prebidreply'	=> 'nullable',
			'attachment' 	=> 'required|file|mimes:pdf,doc,docx|max:1024',
        ];

        $messages = [
            'requestid.required' 		=> 'EoI detail is required',
			'attachment.required' 		=> 'Attachment is mandatory',
			'attachment.file' 			=> 'Invalid file type',
			'attachment.mimes' 			=> 'Invalid file type',
			'attachment.max' 			=> 'Maximum file size allowed is 1 MB.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');


        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		
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
		try 
		{
			$lastFromUserId	=	DB::table('eoi_request_prebid_forwarded')
									->where('requestid', $eoi->requestid)
									->where('postedby','CHiPS')
									->orderByDesc('forwardid')
									->value('fromuserid');			

			DB::table('eoi_request_prebid_forwarded')->insert([
				'requestid'		=>	$eoi->requestid,
				'fromuserid'	=>	$userId,
				'touserid'		=>	$lastFromUserId,
				'creationdate'	=>	now(),				
				'message'		=>	$validatedData['prebidreply'] ?? '',
				'attachment'	=>	$attachment ?? '',
				'postedby'		=>	'PROJECT MANAGER',
				'isprebid'		=>	1
			]);
			
			DB::table('eoi_request_prebid')->where('requestid',$eoi->requestid)->update(['prebidstatus'=>'REPLIED']);
			
			return back()->with('success','Your reply has been stored successfully!');
		}
		catch(QueryException $e)
		{
			if($attachment!='')
			Storage::disk('public')->delete($attachment);
			
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }
	
    public function showPreBidResponse(Request $request,$recordid)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		$userId		= $request->session()->get('userId');
		$vendorId	= $request->session()->get('vendorId') ?? 0;
		
		$broadcastid=	Crypt::decrypt($recordid);

		$data	=	DB::table('eoi_request_prebid_broadcast')->where('broadcastid',$broadcastid)->first();

		$eoi		=	DB::table('eoi_request')->where('requestid',$data->requestid)->first();
		
		return view('admin/manager/viewprebid_response',compact('data','eoi'));
    }

    public function updatePmEoiDetail(Request $request,$recordid)
	{
		$userId	=	$request->session()->get('userId');
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $eoi 		= 	DB::table('eoi_request as a')
							->select('a.*','b.jobcategory')
							->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->where('userid','=',$userId)
							->first();
		if(!$eoi)
		{
			return back()->with('fail','This EoI record is not associated with the current user or department.')->withInput();
		}
		
		$category	=	DB::table('jobcategory_tbl')->orderby('categoryid')->get();
		
		$attachments=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		session::put('categoryid', $eoi->categoryid);
		session::put('projecttitle', $eoi->projecttitle);
		session::put('projectduration', $eoi->projectduration);
		session::put('requestid', $requestid);
		return view('admin/manager/update_eoidetail',compact('eoi','category','token','attachments'));
	}

	public function loadUpdateFormData(Request $request)
	{
		$requestid	=	Crypt::decrypt($request->input('requestid'));
		$eoi		=	DB::table('eoi_request')->select('tier_choice')->where('requestid',$requestid)->first();
		$tierid		=	$request->input('tierid') ?? 0;
		$categoryid	=	$request->input('categoryid') ?? 0;
		$sectorid	=	$request->input('sectorid') ?? 0;

		$tiers	=	DB::table('tiermaster_tbl')->limit(1)->orderBy('tierid')->get();
		
		$rateid	=	DB::table('eoi_request_detail')
					->where('requestid',$requestid)
					->value('rateid');

		$userId	=	$request->session()->get('userId');
		if($categoryid==2)
		{
			$sectorid 		= 	intval($request->input('sectorid'));
			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->select('a.remunerationid as value','b.consultantposition as label')
								->where('a.tierid','=',$tierid)
								->where('a.categoryid','=',$categoryid)
								->where('a.sectorid','=',$sectorid)
								->orderby('b.consultantposition')
								->get();

								
			$sectors	=	DB::table('sector_tbl')->where('categoryid',$categoryid)->orderby('sectorname')->get();

			$i = 0;
			$html = [];
			foreach($tiers as $tier)
			{
				$i++;
				$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryid)->where('tierid','=',$tier->tierid)->first();
				$data	=	$this->priceService->getCsfPrice($requestid,$categoryid,$tier->tierid,$rateid);

				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($data as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
				$grandtotal			=	$this->formatIndianCurrency($grandtotal);
				
				$tier2 = ($i == 1) ? 0 : 1;
				$tier2	=	1;
				$html[]	=	view('admin.manager.resourceTierData.csfTierData', [
								'data' 				=> 	$data,
								'pricing' 			=> 	$pricing,
								'totalmanmonth' 	=> 	$totalmanmonth,
								'adminchargetotal' 	=> 	$adminchargetotal,
								'grandtotal' 		=> 	$grandtotal,
								'tiername'			=>	$tier->tiername,
								'tierid'			=>	$tier->tierid,
								'tier2' 			=>	$tier2,
								'selectedTier'		=>	$eoi->tier_choice,
								'isTemp'			=>	'No',
								'req_id'			=>	$requestid
							])->render();
			}

			$modalForm	=	view('admin.manager.resourceTierData.csfModalForm', [
								'categoryid' 		=> 	$categoryid,
								'experiencelist'	=> 	$experiencelist,
								'sectors' 			=> 	$sectors,
							])->render();

			$formhtml	=	view('/admin/manager/ajaxpages/updateconsultantformTable', compact('modalForm','html'))->render();
			return response()->json(['success'=>true,'formhtml'=>$formhtml]);
		}
		if($categoryid==1)
		{
			$experiencelist =	DB::table('work_experience')->orderBy('experienceid')->get();

			$i = 0;
			$html = [];
			foreach($tiers as $tier)
			{
				$i++;
				$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$categoryid)->where('tierid','=',$tier->tierid)->first();
				$data	=	$this->priceService->getAwdPrice($requestid,$categoryid,$tier->tierid,$rateid);

				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($data as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
				$grandtotal			=	$this->formatIndianCurrency($grandtotal);

				$tier2 = ($i == 1) ? 0 : 1;
				$tier2	=	1;
				$html[]	=	view('admin.manager.resourceTierData.awdTierData', [
								'data' 				=> 	$data,
								'pricing' 			=> 	$pricing,
								'totalmanmonth' 	=> 	$totalmanmonth,
								'adminchargetotal' 	=> 	$adminchargetotal,
								'grandtotal' 		=> 	$grandtotal,
								'tiername'			=>	$tier->tiername,
								'tierid'			=>	$tier->tierid,
								'tier2' 			=>	$tier2,
								'selectedTier'		=>	$eoi->tier_choice,
								'isTemp'			=>	'No',
								'req_id'			=>	$requestid
							])->render();

			}
			$modalForm	=	view('admin.manager.resourceTierData.awdModalForm', [
								'categoryid' 		=> 	$categoryid,
								'experiencelist'	=> 	$experiencelist,
							])->render();

			$formhtml	=	view('/admin/manager/ajaxpages/updateawdformTable', compact('modalForm','html'))->render();
			return response()->json(['success'=>true,'formhtml'=>$formhtml]);
		}
	}

	public function updateEoiRecord(Request $request)
	{

		request()->merge([
			'categoryid' => request('categoryid') ?? Session::get('categoryid')
		]);
		
		$rules = [
			'requestid'        	=> 	'required',
			'categoryid'        => 	'required',
			'tierid'        	=> 	'required',
			'qualification'		=>	'required',
			'employmenttype'	=>	'required',
			'duration'			=>	'required|numeric',
			'remark'			=>	'nullable',
			'resource_qty'		=>	'required|numeric'
		];
		
		if($request->categoryid==2)
		{
			$rules['sectorid'] 		= 	'required';
			$rules['experience'] 		= 	'required';
			$rules['positionid'] 	=	'required';
		}
		if($request->categoryid==1)
		{
			$rules['role'] 				=	'required';
			$rules['experienceid'] 		= 	'required';
			$rules['experiencelevel'] 	= 	'required';
		}
		
        $messages = [
            'requestid.required'		=> 'Eoi detail is required',
			'sectorid.required'			=> 'Sector is required',
			'positionid.required'		=> 'Position is required',
			'role.required'				=> 'Position is required',
			'experienceid.required'		=> 'Experience is required',
			'experiencelevel.required'	=> 'Experience level is required',
			'categoryid.required'		=> 'Category is required',
            'tierid.required' 			=> 'Tier is required',
            'experience.required' 		=> 'Experience is required',
			'qualification.required' 	=> 'Qualification is required',
			'employmenttype.required' 	=> 'Employment type is required',
			'duration.required' 		=> 'Resource duration is required',
			'duration.numeric' 			=> 'Resource duration must be a valid number',
			'resource_qty.required' 	=> 'The number of resources to be added is required.',
			'resource_qty.numeric' 		=> 'The number of resources must be a valid number',
        ];
		$validatedData = $request->validate($rules, $messages);
		DB::beginTransaction();
		try
		{
			$requestid		=	Crypt::decrypt($validatedData['requestid']);
			
			$eoi			=	DB::table('eoi_request')
								->select('projectduration','tier_choice','categoryid')
								->where('requestid',$requestid)
								->first();
			
			$userId    		= 	$request->session()->get('userId');
			$userName  		= 	$request->session()->get('userName');
			$userType  		= 	$request->session()->get('userType');
			
			$tiers	=	DB::table('tiermaster_tbl')->limit(1)->orderBy('tierid')->get();
			
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
			
			if($eoi->categoryid==2)
			{
				/*
				$remuneration	=	DB::table('remuneration_tbl as a')
									->select('a.positionid','b.sectorname','c.consultantposition')
									->leftjoin('sector_tbl as b','b.sectorid','=','a.sectorid')
									->leftjoin('position_tbl as c','c.positionid','=','a.positionid')
									->where('a.remunerationid',$validatedData['remunerationid'])
									->first();
				*/
				$sector		=	DB::table('sector_tbl')->where('sectorid',$validatedData['sectorid'])->first();
				$position	=	DB::table('position_tbl')->where('positionid',$validatedData['positionid'])->first();
				$i=1;
				while($i<=intval($validatedData['resource_qty']))
				{
					DB::table('eoi_request_detail')->insertGetId([
						'requestid'			=>	$requestid,
						'sectorid'			=>	$validatedData['sectorid'] ?? 0,
						'positionid'		=>	$validatedData['positionid'] ?? 0,
						'experience'		=>	$position->experience ?? '',
						'employmenttype'	=>	$validatedData['employmenttype'],
						'qualification'		=>	$validatedData['qualification'] ?? '',
						'duration'			=>	$validatedData['duration'],
						'creationdate'		=>	now(),
						'remark'			=>	$validatedData['remark'] ?? '',
						'rateid'			=>	$rateid
					]);
					$i++;
				}
				
				DB::table('eoi_request')
				->where('requestid',$requestid)
				->update([
					'isupdated'	=>	1
				]);
				
				DB::table('log_eoi_updates')->insert([
					'requestid'		=>	$requestid,
					'particular'	=>	$validatedData['resource_qty'].' resource(s) added for the position '.$position->consultantposition.' in '.$sector->sectorname.' sector on '.date('d\-m\-Y, h:i A'),
					'updatedon'		=>	now(),
					'updatedby'		=>	$userId,
				]);


				$i = 0;
				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$eoi->categoryid)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getCsfPrice($requestid,$eoi->categoryid,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);
					
					$tier2 = ($i == 1) ? 0 : 1;
					$tier2	=	1;
					$html[]	=	view('admin.manager.resourceTierData.csfTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'selectedTier'		=>	$eoi->tier_choice,
									'isTemp'			=>	'No',
									'req_id'			=>	$requestid
								])->render();
				}
				
				$html = !empty($html) ? implode('', $html) : '';
				
		
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Record saved successfully.','tableData'=>$html]);
			}
			if($eoi->categoryid==1)
			{
				$experience	=	DB::table('work_experience')->where('experienceid',$validatedData['experienceid'])->first();
				$i=1;
				while($i<=intval($validatedData['resource_qty']))
				{
					DB::table('eoi_request_detail')->insertGetId([
						'requestid'			=>	$requestid,
						'role'				=>	$validatedData['role'],
						'experience'		=>	$experience->workexperience ?? null,
						'qualification'		=>	$validatedData['qualification'] ?? '',
						'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
						'duration'			=>	$validatedData['duration'],
						'remark'			=>	$validatedData['remark'] ?? '',
						'employmenttype'	=>	$validatedData['employmenttype'],
						'creationdate'		=>	now(),
						'rateid'			=>	$rateid
					]);
					$i++;
				}
				
				DB::table('eoi_request')
				->where('requestid',$requestid)
				->update([
					'isupdated'	=>	1
				]);
				
				DB::table('log_eoi_updates')->insert([
					'requestid'		=>	$requestid,
					'particular'	=>	$validatedData['resource_qty'].' resource(s) added for the position '.$validatedData['role'].' on '.date('d\-m\-Y, h:i A'),
					'updatedon'		=>	now(),
					'updatedby'		=>	$userId,
				]);

				$i = 0;
				$html = [];
				foreach($tiers as $tier)
				{
					$i++;
					$pricing=	DB::table('pricing_tbl')->where('categoryid','=',$eoi->categoryid)->where('tierid','=',$tier->tierid)->first();
					$data	=	$this->priceService->getAwdPrice($requestid,$eoi->categoryid,$tier->tierid,$rateid);

					$totalmanmonth		=	0;
					$adminchargetotal	=	0;
					$grandtotal			=	0;
					foreach($data as $rec)
					{
						$rec->baseprice	=	$rec->budget;
						$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;

						$totalmanmonth	=	$totalmanmonth+$rec->budget;
					}

					$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
					$grandtotal			=	$totalmanmonth+$adminchargetotal;
					
					$totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
					$adminchargetotal	=	$this->formatIndianCurrency($adminchargetotal);
					$grandtotal			=	$this->formatIndianCurrency($grandtotal);

					$tier2 = ($i == 1) ? 0 : 1;

					$html[]	=	view('admin.manager.resourceTierData.awdTierData', [
									'data' 				=> 	$data,
									'pricing' 			=> 	$pricing,
									'totalmanmonth' 	=> 	$totalmanmonth,
									'adminchargetotal' 	=> 	$adminchargetotal,
									'grandtotal' 		=> 	$grandtotal,
									'tiername'			=>	$tier->tiername,
									'tierid'			=>	$tier->tierid,
									'tier2' 			=>	$tier2,
									'selectedTier'		=>	$eoi->tier_choice
								])->render();

				}
				$html = !empty($html) ? implode('', $html) : '';
				
		
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Record saved successfully.','tableData'=>$html]);
			}
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

    public function deleteEoiDetail($recordid)
	{
		if(!session('departmentId') || !session('isProjectManager'))
		{
			return redirect('dashboard');
		}
		
		$userId    		= 	session('userId');
		$eoi	=	DB::table('eoi_request')->where('requestid',session('requestid'))->first();
		$res = DB::delete('delete from eoi_request_detail WHERE recordid=?',[Crypt::decrypt($recordid)]);

		if($res)
		{
			$data	=	DB::table('eoi_request_detail as a')
								->select('a.*','c.sectorname','d.consultantposition','d.experience')
								->leftjoin('remuneration_tbl as b','b.remunerationid','=','a.remunerationid')
								->leftjoin('sector_tbl as c','c.sectorid','=','a.sectorid')
								->leftjoin('position_tbl as d','d.positionid','=','a.positionid')
								->where('a.requestid',session('requestid'))
								->get();
			$admincharge	=	DB::table('pricing_tbl')->where('categoryid',$eoi->categoryid)->value('admincharge');

			$totalbudget		=	0;
			$totaladmincharge	=	0;
			$totalamount		=	0;
			foreach($data as $record)
			{
				$totalbudget	=	$totalbudget+$record->budget;
			}
			$totaladmincharge	=	($totalbudget*$admincharge)/100;
			$totalamount		=	$totalbudget+$totaladmincharge;
			
			DB::table('eoi_request')
				->where('requestid',session('requestid'))
				->update([
					'isupdated'			=>	1,
					'totalbudget'		=>	$totalbudget,
					'totaladmincharge'	=>	$totaladmincharge,
					'totalamount'		=>	$totalamount,
				]);
			
			DB::table('log_eoi_updates')->insert([
				'requestid'		=>	session('requestid'),
				'particular'	=>	'One resource removed',
				'updatedon'		=>	now(),
				'updatedby'		=>	$userId
			]);
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }

	public function updateOnlyPmEOI(Request $request)
	{	
        $rules = [
			'scopeofwork' 		=> ['required', 'regex:/^(?!.*<\s*script).*$/is'],
			'anyother' 			=> ['nullable', 'regex:/^(?!.*<\s*script).*$/is'],
			'projecttitle' 		=> ['required','string','not_regex:/<[^>]*>/i','not_regex:/(script|javascript:|onerror|onload)/i',],
			'projectobjective'	=> ['required', 'regex:/^(?!.*<\s*script).*$/is'],
        ];
        $messages = [
			'scopeofwork.required'		=> 'Scope of work is required',
			'scopeofwork.regex'			=> 'Invalid content in scope of work',
			'anyother.regex'			=> 'Invalid content in special condition',
			'projecttitle.required'		=> 'Project name is required',
			'projecttitle.not_regex'	=> 'Invalid content in project name',
            'projectobjective.required'	=> 'Project objective is required',
			'projectobjective.regex'	=> 'Invalid content in project objective',
        ];
		
		$validatedData 	= $request->validate($rules,$messages);
		
		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		
		$eoi	=	DB::table('eoi_request')->where('requestid',session('requestid'))->first();
		$particular	=	"";
		
		if($validatedData['projecttitle']!=$eoi->projecttitle)
		{
			$particular.=	"Project name changed from ".$eoi->projecttitle." to ".$validatedData['projecttitle']."<br>";
		}
		if($validatedData['projectobjective']!=$eoi->projectobjective)
		{
			$particular.=	"Project objective changed from ".$eoi->projectobjective." to ".$validatedData['projectobjective']."<br>";
		}
		if($validatedData['scopeofwork']!=$eoi->scopeofwork)
		{
			$particular.=	"Scope of work changed from ".$eoi->scopeofwork." to ".$validatedData['scopeofwork']."<br>";
		}
		if($validatedData['anyother']!=$eoi->anyother)
		{
			$particular.=	"Special condition changed from ".$eoi->anyother." to ".$validatedData['anyother']."<br>";
		}
		DB::beginTransaction();
		try
		{
			DB::table('eoi_request')->where('requestid',session('requestid'))->update([
				'projecttitle'		=>	$validatedData['projecttitle'],
				'projectobjective'	=>	$validatedData['projectobjective'],
				'scopeofwork'		=>	$validatedData['scopeofwork'],
				'anyother'			=>	$validatedData['anyother'],
			]);
			if($particular!='')
			{
				DB::table('log_eoi_updates')->insert([
					'requestid'		=>	session('requestid'),
					'particular'	=>	$particular,
					'updatedon'		=>	now(),
					'updatedby'		=>	$userId
				]);
				
				DB::table('eoi_request')->where('requestid',session('requestid'))->update(['isupdated'=>1]);
			}
			DB::commit();
			return response()->json([
				'message' => 'Record saved successfully',
				'status' => 200,
			], 200);				
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}


	public function addDeptEoiCommitteeMember(Request $request)
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
		$requestid	=	Crypt::decrypt($request->input('requestid'));

		try
		{
			DB::beginTransaction();

			DB::table('eoi_request_committee')->insert([
				'requestid'   	=> $requestid,
				'committeeid'  	=> $memberid,
				'createdby'  	=> Session('userId'),
				'creationdate'  => now()
			]);


			$committee	=	DB::table('eoi_request_committee as a')
								->select('a.memberid','a.requestid','a.committeeid','a.createdby','a.creationdate','b.usertype','b.name','b.mobilenumber','b.designation','b.remark')
								->leftjoin('committee_member as b','b.memberid','=','a.committeeid')
								->where('a.requestid',$requestid)
								->get();
			
			$count	=	DB::table('eoi_request_committee')->where('requestid',$requestid)->count();
			if($count>=5)
			{
				DB::table('eoi_request')->where('requestid',$requestid)->where('iscommittee',0)->update([
					'iscommittee'	=>	1
				]);
			}
			
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


	public function updatePmAttachmentTitle(Request $request)
	{
	
        $rules = [
			'recordid' 			=>	'required',
			'requestid' 		=>	'required',
			'attachmenttitle' 	=>	'required',
        ];
        $messages = [
            'recordid.required'			=> 'Record id is required',
			'requestid.required'		=> 'EoI number is required',
			'attachmenttitle.required'	=> 'Attachment title is required',
        ];
		
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		
		$eoi		=	DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->first();
		$attachment	=	DB::table('eoi_request_attachment')->where('recordid',Crypt::decrypt($validatedData['recordid']))->first();

		$particular	=	"";
		
		if($validatedData['attachmenttitle']!=$attachment->attachmenttitle)
		{
			$particular.=	"Attachment title name changed from ".$attachment->attachmenttitle." to ".$validatedData['attachmenttitle']."<br>";
		}
		DB::beginTransaction();
		try
		{
			DB::table('eoi_request_attachment')->where('recordid',Crypt::decrypt($validatedData['recordid']))->update([
				'attachmenttitle'		=>	$validatedData['attachmenttitle'],
			]);
			if($particular!='')
			{
				DB::table('log_eoi_updates')->insert([
					'requestid'		=>	Crypt::decrypt($validatedData['requestid']),
					'particular'	=>	$particular,
					'updatedon'		=>	now(),
					'updatedby'		=>	$userId
				]);
				
				DB::table('eoi_request')->where('requestid',Crypt::decrypt($validatedData['requestid']))->update(['isupdated'=>1]);
			}
			DB::commit();
			return response()->json([
				'message' => 'Record saved successfully',
				'status' => 200,
			], 200);				
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}


	public function removeAttachment(Request $request)
	{
		if(!session('departmentId') || !session('isProjectManager')) {
			return redirect('dashboard');
		}
		
        $rules = [
			'recordid' 			=>	'required',
			'requestid' 		=>	'required',
        ];
        $messages = [
            'recordid.required'			=> 'Record id is required',
			'requestid.required'		=> 'EoI number is required',
			'attachmenttitle.required'	=> 'Attachment title is required',
        ];
		
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		$requestid		=	Crypt::decrypt($validatedData['requestid']);
		$recordid		=	Crypt::decrypt($validatedData['recordid']);
		DB::beginTransaction();
		try
		{
			$eoi	=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			if($eoi->eoistatus>2)
			{
				return response()->json([
					'message' 	=> 	'Files cannot be removed, as this EoI has been published.',
					'status' 	=> 	400,
				], 400);				
				
			}
			$attch = DB::table('eoi_request_attachment')
						->where('recordid',$recordid)
						->where('eoirequestid',$requestid)
						->first();

			if ($attch->attachmentfile!='') {
				Storage::disk('public')->delete($attch->attachmentfile);
			}
			
			DB::table('eoi_request_attachment')
				->where('recordid',$recordid)
				->where('eoirequestid',$requestid)
				->delete();
			
			DB::table('log_eoi_updates')->insert([
				'requestid'		=>	$requestid,
				'particular'	=>	'One attachment has been deleted by the department. Please review the details.',
				'updatedon'		=>	now(),
				'updatedby'		=>	$userId
			]);
			
			$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
			
			$html = view('admin.department.ajaxpages.fileattachmentTable',['attachments'=>$attachments,'requestid'=>$validatedData['requestid']])->render();
			DB::commit();
			return response()->json([
				'message' 	=> 	'File removed successfully',
				'status' 	=> 	200,
				'formhtml'	=>	$html
			], 200);				
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

	public function uploadPmAttachments(Request $request)
	{
	
        $rules = [
			'requestid' 		=>	'required',
			'attachmenttitles.*'=> 	'required|max:150',
			'attachmentfiles.*' => 	'required|file|mimetypes:application/pdf|max:2024',
        ];

        $messages = [
			'requestid.required' 			=> 'EoI detail is required',
			'attachmenttitles.*.required' 	=> 'Attachment title is required',
			'attachmenttitles.*.max' 		=> 'A maximum of 150 characters is allowed for the attachment title',
			'attachmentfiles.*.file'    	=> 'Invalid file.',
			'attachmentfiles.*.mimetypes'   => 'Invalid file format. Allowed: pdf, doc, docx.',
			'attachmentfiles.*.max'     	=> 'Max 1 MB is allowed.',			
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		$requestid		=	Crypt::decrypt($validatedData['requestid']);
		
		$attachmenttitles= $request->input('attachmenttitles');
		$attachmentfiles = $request->file('attachmentfiles');
		
		DB::beginTransaction();
		try
		{
			$eoi	=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			if($eoi->eoistatus>2)
			{
				return response()->json([
					'message' 	=> 	'Files cannot be added, as this EoI has been published.',
					'status' 	=> 	400,
				], 400);				
				
			}
			
			DB::transaction(function () use ($request,$userId,$validatedData,$attachmenttitles,$attachmentfiles,$requestid)
			{
				foreach ($attachmenttitles as $index => $attachmenttitle) {
					$file         = $attachmentfiles[$index] ?? null;
					$filePath     = "";

					if ($file) {
						$filePath = $file->store('uploads/eoiattachments','public');
					}
					if($attachmenttitle!='' && $filePath!='')
					{
						DB::table('eoi_request_attachment')->insert([
							'eoirequestid'   	=> $requestid,
							'attachmenttitle'   => $attachmenttitle,
							'attachmentfile'  	=> $filePath,					
							'created_by'  		=> $userId,
							'creationdate'  	=> now()
						]);
					}
				}				
			});

			
			DB::table('log_eoi_updates')->insert([
				'requestid'		=>	$requestid,
				'particular'	=>	'The project manager has uploaded new files. Kindly review the details.',
				'updatedon'		=>	now(),
				'updatedby'		=>	$userId
			]);
			
			$attachments	=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
			Session::put('uploaded','Files uploaded successfully');
			$html	=	view('admin.manager.ajaxpages.fileattachmentTable',['attachments'=>$attachments,'requestid'=>$validatedData['requestid']])->render();
			DB::commit();
			return response()->json([
				'message' 	=> 	'File attached successfully',
				'status' 	=> 	200,
				'formhtml'	=>	$html
			],200);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}


	public function updatePmEoiFiles(Request $request)
	{	
        $rules = [
			'authorizationletter' 	=> 	'nullable|file|mimetypes:application/pdf|max:5120',
			'draftedeoi' 			=> 	'nullable|file|mimetypes:application/pdf|max:5120',
			'requestid' 			=>	'required',
        ];
        $messages = [
			'requestid.required'					=> 'EoI detail is required',
			'authorizationletter.file'				=> 'Must be a file',
			'authorizationletter.mimetypes'			=> 'Invalid file',
			'authorizationletter.max'				=> 'Maximum 5 MB file size is allowed',
			'draftedeoi.file'						=> 'Must be a file',
			'draftedeoi.mimetypes'					=> 'Invalid file',
			'draftedeoi.max'						=> 'Maximum 5 MB file size is allowed',
			
        ];
		
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$departmentId	=	$request->session()->get('departmentId');
		$requestid		=	Crypt::decrypt($validatedData['requestid']);
		DB::beginTransaction();
		try
		{
			$eoi	=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			if($eoi->eoistatus>2)
			{
				return response()->json([
					'message' 	=> 	'Files cannot be updated, as this EoI has been published.',
					'status' 	=> 	400,
				], 400);				
				
			}

			$filePath = null;
			$updateData = [
				'authorizationletter' => null,
				'draftedeoi' => null,
			];
			$flag=0;
			if($request->hasFile('authorizationletter'))
			{
				$flag++;
				$filePath = $request->file('authorizationletter')->store('uploads/authorizationletters', 'public');				
				$updateData['authorizationletter'] = $filePath;
				if($eoi->eoistatus>0)
				{
					DB::table('log_eoi_updates')->insert([
						'requestid'		=>	$requestid,
						'particular'	=>	'Authorization letter file updated successfully',
						'updatedon'		=>	now(),
						'updatedby'		=>	$userId
					]);
				}
			}
			elseif($request->hasFile('draftedeoi'))
			{
				$flag++;
				$filePath = $request->file('draftedeoi')->store('uploads/draftedeois', 'public');
				$updateData['draftedeoi'] = $filePath;
				if($eoi->eoistatus>0)
				{
					DB::table('log_eoi_updates')->insert([
						'requestid'		=>	$requestid,
						'particular'	=>	'EoI file updated successfully',
						'updatedon'		=>	now(),
						'updatedby'		=>	$userId
					]);
				}
			}
			if($flag>0)
			{
				$old	=	DB::table('eoi_request')->where('requestid', $requestid)->first();
				DB::table('eoi_files_update')
					->insert([
						'requestid'			=>	$requestid,
						'auth_letter'		=>	$old->authorizationletter,
						'updated_on'		=>	now(),
						'updated_by'		=>	Session('userId')
					]);
				DB::table('eoi_request')
					->where('requestid', $requestid)
					->update($updateData);			
			}
			
			$eoifiles	=	DB::table('eoi_request')
								->select('requestid','authorizationletter','draftedeoi')
								->where('requestid',$requestid)
								->first();
			
			$html	=	view('admin.manager.ajaxpages.eoifilesTable',['eoifiles'=>$eoifiles,'requestid'=>$requestid])->render();
			Session::put('uploaded','File uploaded successfully');
			DB::commit();
			return response()->json([
				'message' 	=> 	'File Updated successfully',
				'status' 	=> 	200,
				'formhtml'	=>	$html
			], 200);				
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}


    public function updatePmConfirmation(Request $request,$recordid)
	{
	
		$userId	=	$request->session()->get('userId');
		
		$requestid 	=	Crypt::decrypt($recordid);
		
        $eoi 		= 	DB::table('eoi_request as a')
							->select('a.*','b.jobcategory')
							->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
							->where('requestid','=',$requestid)
							->where('userid','=',$userId)
							->first();
		if(!$eoi)
		{
			return back()->with('fail','This EoI record is not associated with the current user or department.')->withInput();
		}
		
		DB::table('eoi_request')->where('userid',$userId)->where('requestid','=',$requestid)->update([
			'isUpdateRequired'	=>	0
		]);
		
		DB::table('eoi_update_confirmation')->insert([
			'requestid'		=>	$requestid,
			'respondedby'	=>	$userId,
			'respondedon'	=>	now()
		]);
		
		return redirect()->route('pmview.draft', ['recordid' => Crypt::encrypt($requestid)])
                     ->with('success', 'EoI update confirmed successfully.');
	}

	public function updateResourceRecord(Request $request)
	{
		request()->merge([
			'recordid' => Crypt::decrypt(request('recordid'))
		]);
		
        $rules = [
			'recordid'	=>	'required|exists:eoi_request_detail,recordid',
        ];

        $messages = [
			'recordid.required'	=> 'Please provide resource detail',
			'recordid.exists' 	=> 'Invalid resource detail provided',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		DB::beginTransaction();
		try
		{
			$record	=	DB::table('eoi_request_detail')->where('recordid',$validatedData['recordid'])->first();
			
			$eoi	=	DB::table('eoi_request')->where('requestid',$record->requestid)->first();
			if($eoi->eoistatus>2)
			{
				return response()->json([
					'message'	=> 	'Record can not be edited, as this EoI has been published.',
					'status'	=> 	400,
				], 400);				
				
			}
			$token	=	rand('100000','999999').''.time();
			Session::put('form_token', $token);
			
			if($eoi->categoryid==2)
			{
				
				$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
				$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
				
				$record->recordid	=	Crypt::encrypt($record->recordid);
				
				$html	=	view('admin.manager.ajaxpages.updates.updateresourceTable',['record'=>$record,'sectors'=>$sectors,'positions'=>$positions,'token'=>$token,'eoi'=>$eoi])->render();
				DB::commit();
				return response()->json([
					'message' 	=> 	'Editable Form',
					'status' 	=> 	200,
					'formhtml'	=>	$html,
				],200);
			}
			if($eoi->categoryid==1)
			{
				
				$experience	=	DB::table('work_experience')->orderBy('experienceid')->get();
				$levels	=	DB::table('remuneration_tbl')->select('experiencelevel')->orderBy('experiencelevel')->distinct()->get();
				
				$record->recordid	=	Crypt::encrypt($record->recordid);
				
				$html	=	view('admin.manager.ajaxpages.updates.updateresourceawdTable',['record'=>$record,'experiences'=>$experience,'levels'=>$levels,'token'=>$token,'eoi'=>$eoi])->render();
				DB::commit();
				return response()->json([
					'message' 	=> 	'Editable Form',
					'status' 	=> 	200,
					'formhtml'	=>	$html,
				],200);
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

	public function storeResourceUpdate(Request $request)
	{
		$request->merge([
			'recordid' => Crypt::decrypt($request->input('recordid'))
		]);
		$record	=	DB::table('eoi_request_detail as a')
					->select('a.*','b.sectorname','c.consultantposition')
					->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
					->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
					->where('recordid',$request->input('recordid'))
					->first();

		
		$eoi	=	DB::table('eoi_request')->where('requestid',$record->requestid)->first();

		
        $rules = [
			'recordid'				=>	'required|exists:eoi_request_detail,recordid',
			'resource_experience'   => 	'required|string|regex:/^(?!.*<script>).*$/i',
			'employment_type'		=> 	'required|in:FULL TIME,PART TIME',
			'resource_duration'		=>	'required|numeric',
			'qualification' 		=> 	'required|regex:/^(?!.*<script>).*$/is',
			'remark'        		=> 	'nullable|regex:/^(?!.*<script>).*$/is',
        ];
		
		if($eoi->categoryid==2)
		{
			$rules['sector_id']	=	'required|numeric';
			$rules['position_id']=	'required|numeric';
		}
		if($eoi->categoryid==1)
		{
			$rules['position_role']		=	'required|regex:/^(?!.*<script>).*$/is';
			$rules['experience_level']	=	'required|numeric';
		}
		
		
        $messages = [
			'recordid.required'			=> 'Please provide resource detail',
			'recordid.exists' 			=> 'Invalid resource detail provided',
			'resource_experience.required'		=> 'Experience is required',
			'resource_experience.string'			=> 'Invalid experience value found',
			'resource_experience.regex'			=> 'Invalid experience value found',
			'employment_type.required'	=> 'Deployment type is required',
			'employment_type.in'			=> 'In valid deployment value provided',
			'resource_duration.required'			=> 'Duration value is required',
			'resource_duration.numeric'			=> 'Invalid duration value provided',
			'qualification.required'	=> 'Qualification is required',
			'qualification.string'		=> 'Invalid qualification value found',
			'qualification.regex'		=> 'Invalid qualification value found',
			'remark.required'			=> 'Remark is required',
			'remark.string'				=> 'Invalid remark value found',
			'remark.regex'				=> 'Invalid remark value found',
			'sector_id.required'			=> 'Sector name is required',
			'sector_id.numeric'			=> 'Invalid sector name provided',
			'position_id.required'		=> 'Position name is required',
			'position_id.numeric'		=> 'Invalid position name provided',
			'position_role.required'	=> 'Position is required',
			'position_role.string'		=> 'Invalid position value',
			'position_role.regex'		=> 'Invalid position value',
			'experience_level.required'	=> 'Experience level is required',
			'experience_level.string'	=> 'Invalid experience level value',
			
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		DB::beginTransaction();
		try
		{
			if($eoi->eoistatus>2)
			{
				return response()->json([
					'message'	=> 	'Record can not be edited, as this EoI has been published.',
					'status'	=> 	400,
				], 400);				
				
			}
			
			if($eoi->categoryid==2)
			{
				$particular	=	"";
				if($record->sectorid!=$validatedData['sector_id'])
				{
					$sectorname	=	DB::table('sector_tbl')->where('sectorid',$validatedData['sector_id'])->value('sectorname');
					$particular	=	"Sector changed from ".$record->sectorname." to ".$sectorname.".<br>";
				}
				if($record->positionid!=$validatedData['position_id'])
				{
					$positionname	=	DB::table('position_tbl')->where('positionid',$validatedData['position_id'])->value('consultantposition');
					$particular.="Position changed from ".$record->consultantposition." to ".$positionname.".<br>";
				}
				if($record->employmenttype!=$validatedData['employment_type'])
				{
					$particular.="Deployment type changed from ".$record->employmenttype." to ".$validatedData['employment_type'].".<br>";
				}
				if($record->duration!=$validatedData['resource_duration'])
				{
					$particular.="Resource duration changed from ".$record->duration." to ".$validatedData['resource_duration'].".<br>";
				}
				if($record->qualification!=$validatedData['qualification'])
				{
					$particular.="Qualification changed from ".$record->qualification." to ".$validatedData['qualification'].".<br>";
				}
				if($record->remark!=$validatedData['remark'])
				{
					$particular.="Remark changed from ".$record->remark." to ".$validatedData['remark'].".<br>";
				}
				if($particular!='')
				{
					DB::table('log_eoi_updates')->insert([
						'requestid'		=>	$record->requestid,
						'particular'	=>	'The following details were updated for the resource <br>' . $particular . 'on ' . date('d-m-Y, h:i A'),
						'updatedon'		=>	now(),
						'updatedby'		=>	$userId,
					]);
					
				}
				DB::table('eoi_request')
				->where('requestid',$record->requestid)
				->update([
					'isupdated'	=>	1
				]);
				$experience	=	DB::table('position_tbl')->where('positionid',$validatedData['position_id'])->value('experience');
				DB::table('eoi_request_detail')
				->where('recordid',$validatedData['recordid'])
				->update([
					'sectorid'		=>	$validatedData['sector_id'],
					'positionid'	=>	$validatedData['position_id'],
					'duration'		=>	$validatedData['resource_duration'],
					'employmenttype'=>	$validatedData['employment_type'],
					'qualification'	=>	$validatedData['qualification'],
					'experience'	=>	$experience ?? '',
					'remark'		=>	$validatedData['remark'],
				]);
				
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Resource detail updated successfully.']);
			}
			if($eoi->categoryid==1)
			{
				$particular	=	"";
				if($record->role!=$validatedData['position_role'])
				{
					$particular	=	"Position changed from ".$record->role." to ".$validatedData['position_role'].".<br>";
				}
				if($record->experience!=$validatedData['resource_experience'])
				{
					$particular.="Experience changed from ".$record->experience." to ".$validatedData['resource_experience'].".<br>";
				}
				if($record->experiencelevel!=$validatedData['experience_level'])
				{
					$particular.="Experience level changed from ".$record->experiencelevel." to ".$validatedData['experience_level'].".<br>";
				}
				if($record->employmenttype!=$validatedData['employment_type'])
				{
					$particular.="Deployment type changed from ".$record->employmenttype." to ".$validatedData['employment_type'].".<br>";
				}
				if($record->duration!=$validatedData['resource_duration'])
				{
					$particular.="Resource duration changed from ".$record->duration." to ".$validatedData['resource_duration'].".<br>";
				}
				if($record->qualification!=$validatedData['qualification'])
				{
					$particular.="Qualification changed from ".$record->qualification." to ".$validatedData['qualification'].".<br>";
				}
				if($record->remark!=$validatedData['remark'])
				{
					$particular.="Remark changed from ".$record->remark." to ".$validatedData['remark'].".<br>";
				}
				if($particular!='')
				{
					DB::table('log_eoi_updates')->insert([
						'requestid'		=>	$record->requestid,
						'particular'	=>	'The following details were updated for the resource <br>' . $particular . 'on ' . date('d-m-Y, h:i A'),
						'updatedon'		=>	now(),
						'updatedby'		=>	$userId,
					]);
					
				}
				DB::table('eoi_request')
				->where('requestid',$record->requestid)
				->update([
					'isupdated'	=>	1
				]);
				
				DB::table('eoi_request_detail')
				->where('recordid',$validatedData['recordid'])
				->update([
					'role'				=>	$validatedData['position_role'],
					'experience'		=>	$validatedData['resource_experience'],
					'experiencelevel'	=>	$validatedData['experience_level'],					
					'employmenttype'	=>	$validatedData['employment_type'],
					'duration'			=>	$validatedData['resource_duration'],
					'qualification'		=>	$validatedData['qualification'],
					'remark'			=>	$validatedData['remark'],					
				]);
				
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Resource detail updated successfully.','qualification'=>$validatedData['qualification']]);
			}
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

	public function updateEoiResourceRecord(Request $request)
	{
		request()->merge([
			'recordid' => Crypt::decrypt(request('recordid'))
		]);
		
        $rules = [
			'recordid'	=>	'required|exists:eoi_temp_requirement,recordid',
        ];

        $messages = [
			'recordid.required'	=> 'Please provide resource detail',
			'recordid.exists' 	=> 'Invalid resource detail provided',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		DB::beginTransaction();
		try
		{
			$record	=	DB::table('eoi_temp_requirement')->where('recordid',$validatedData['recordid'])->first();
			
		
			if($record->categoryid==2)
			{
				
				$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
				$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
				
				$record->recordid	=	Crypt::encrypt($record->recordid);
				
				$html	=	view('admin.manager.ajaxpages.updates.resourceTable',['record'=>$record,'sectors'=>$sectors,'positions'=>$positions])->render();
				DB::commit();
				return response()->json([
					'message' 	=> 	'Editable Form',
					'status' 	=> 	200,
					'formhtml'	=>	$html,
				],200);
			}
			if($record->categoryid==1)
			{
				
				$experience	=	DB::table('work_experience')->orderBy('experienceid')->get();
				$levels		=	DB::table('remuneration_tbl')->select('experiencelevel')->orderBy('experiencelevel')->distinct()->get();
				
				$record->recordid	=	Crypt::encrypt($record->recordid);
				
				$html	=	view('admin.manager.ajaxpages.updates.resourceawdTable',['record'=>$record,'experiences'=>$experience,'levels'=>$levels])->render();
				DB::commit();
				return response()->json([
					'message' 	=> 	'Editable Form',
					'status' 	=> 	200,
					'formhtml'	=>	$html,
				],200);
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

	public function storeEoiResourceUpdate(Request $request)
	{
		$request->merge([
			'recordid' => Crypt::decrypt($request->input('recordid'))
		]);
		$record	=	DB::table('eoi_temp_requirement as a')
					->select('a.*','b.sectorname','c.consultantposition')
					->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
					->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
					->where('recordid',$request->input('recordid'))
					->first();

		
        $rules = [
			'recordid'				=>	'required|exists:eoi_temp_requirement,recordid',
			'resource_experience'   => 	'required|string|regex:/^(?!.*<script>).*$/i',
			'employment_type'		=> 	'required|in:FULL TIME,PART TIME',
			'resource_duration'		=>	'required|numeric',
			'qualification' 		=> 	'required|regex:/^(?!.*<script>).*$/is',
			'remark'        		=> 	'nullable|regex:/^(?!.*<script>).*$/is',
        ];
		
		if($record->categoryid==2)
		{
			$rules['sector_id']	=	'required|numeric';
			$rules['position_id']=	'required|numeric';
		}
		if($record->categoryid==1)
		{
			$rules['position_role']		=	'required|regex:/^(?!.*<script>).*$/is';
			$rules['experience_level']	=	'required|numeric';
		}
		
		
        $messages = [
			'recordid.required'			=> 'Please provide resource detail',
			'recordid.exists' 			=> 'Invalid resource detail provided',
			'resource_experience.required'		=> 'Experience is required',
			'resource_experience.string'			=> 'Invalid experience value found',
			'resource_experience.regex'			=> 'Invalid experience value found',
			'employment_type.required'	=> 'Deployment type is required',
			'employment_type.in'			=> 'In valid deployment value provided',
			'resource_duration.required'			=> 'Duration value is required',
			'resource_duration.numeric'			=> 'Invalid duration value provided',
			'qualification.required'	=> 'Qualification is required',
			'qualification.string'		=> 'Invalid qualification value found',
			'qualification.regex'		=> 'Invalid qualification value found',
			'remark.required'			=> 'Remark is required',
			'remark.string'				=> 'Invalid remark value found',
			'remark.regex'				=> 'Invalid remark value found',
			'sector_id.required'			=> 'Sector name is required',
			'sector_id.numeric'			=> 'Invalid sector name provided',
			'position_id.required'		=> 'Position name is required',
			'position_id.numeric'		=> 'Invalid position name provided',
			'position_role.required'	=> 'Position is required',
			'position_role.string'		=> 'Invalid position value',
			'position_role.regex'		=> 'Invalid position value',
			'experience_level.required'	=> 'Experience level is required',
			'experience_level.string'	=> 'Invalid experience level value',
			
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		DB::beginTransaction();
		try
		{
		
			if($record->categoryid==2)
			{
				$experience	=	DB::table('position_tbl')->where('positionid',$validatedData['position_id'])->value('experience');
				DB::table('eoi_temp_requirement')
				->where('recordid',$validatedData['recordid'])
				->update([
					'sectorid'		=>	$validatedData['sector_id'],
					'positionid'	=>	$validatedData['position_id'],
					'duration'		=>	$validatedData['resource_duration'],
					'employmenttype'=>	$validatedData['employment_type'],
					'qualification'	=>	$validatedData['qualification'],
					'experience'	=>	$experience ?? '',
					'remark'		=>	$validatedData['remark'],
				]);
				
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Resource detail updated successfully.']);
			}
			if($record->categoryid==1)
			{		
				DB::table('eoi_temp_requirement')
				->where('recordid',$validatedData['recordid'])
				->update([
					'role'				=>	$validatedData['position_role'],
					'experience'		=>	$validatedData['resource_experience'],
					'experiencelevel'	=>	$validatedData['experience_level'],					
					'employmenttype'	=>	$validatedData['employment_type'],
					'duration'			=>	$validatedData['resource_duration'],
					'qualification'		=>	$validatedData['qualification'],
					'remark'			=>	$validatedData['remark'],					
				]);
				
				DB::commit();
				return response()->json(['status'=>200,'message'=>'Resource detail updated successfully.']);
			}
			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

    public function viewPptResumes(Request $request,$recordid)
	{
		$requestid 	=	Crypt::decrypt($recordid);

        $data 		= 	DB::table('eoi_request as a')
							->select('a.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
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
						->leftJoin('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
						->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'b.tierid')
						->where('a.requestid', $requestid)
						->get();
						
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token',$token);

		$interviews	=	DB::table('eoi_interview_date as a')
						->select('a.requested_on','a.updated_on','a.isUpdated','a.updatedValue','a.remark','c.name as requestedby','b.eoinumber')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.requested_by')
						->where('a.requestid',$requestid)
						->where('b.userid',session('userId'))
						->orderBy('a.recordid')
						->get();
		
		return view('admin/manager/uploaded_pptresumes',compact('data','token','vendors','interviews'));
    }

    public function viewPmParticipations(Request $request,$recordid)
	{
		
		$participationid=	Crypt::decrypt($recordid);
		$participation	=	DB::table('eoi_request_participation')->where('participationid',$participationid)->first();
		$floated		=	DB::table('eoi_request_floated')->where('floatid',$participation->floatid)->first();

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
									->select('a.recordid','a.tierid','a.experience','a.employmenttype','a.qualification','a.remark','a.duration','a.isAdditional','b.tiername','d.sectorname','e.consultantposition')
									->leftjoin('tiermaster_tbl as b','b.tierid','a.tierid')
									->join('remuneration_tbl as c','c.rateid','a.rateid')
									->join('sector_tbl as d','d.sectorid','a.sectorid')
									->join('position_tbl as e','e.positionid','a.positionid')
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
		
		return view('admin/manager/vendor_resumesppt',compact('eoi','vendor','token','participation','categoryid','requestdetail','floated'));

    }

    public function updatePmInterviewDate(Request $request)
	{
		request()->merge([
			'requestid' => Crypt::decrypt(request('requestid'))
		]);
		
		$rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'interviewdate' =>	'required|date_format:d-m-Y g:i A',
		];
        $messages = [
            'requestid.required'		=>	'EoI detail is required',
			'interviewdate.required'	=>	'Interview date is required',
			'interviewdate.date_format'	=>	'Invalid interview date',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId			= $request->session()->get('userId');
		
		try
		{
			DB::beginTransaction();
			DB::table('eoi_interview_date')
			->where('requestid',$validatedData['requestid'])
			->whereNull('isUpdated')
			->update([
				'updated_on'	=>	now(),
				'updated_by'	=>	$userId,
				'isUpdated'		=>	1,
				'updatedValue'	=>	date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate']))
			]);
			
			DB::table('eoi_request')
			->where('requestid',$validatedData['requestid'])
			->update([
				'isInterviewSet'	=>	1,
				'interviewdate'		=>	date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate']))
			]);
			
			DB::table('eoi_request_participation')
			->where('requestid',$validatedData['requestid'])
			->update([
				'interviewdate'	=>	date('Y\-m\-d H:i:s',strtotime($validatedData['interviewdate']))
			]);
			
			DB::commit();
			return response()->json(['message' => 'Interview date and time updated successfully.','status'=>200]);
		}
		catch(QueryException $e)
		{
			rollBack();
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);			
		}
    }


	public function updatePreBidPmResponse(Request $request,$recordid=NULL)
	{
		$request->merge([
			'requestid'		=>	Crypt::decrypt($recordid),
			'questionid'	=>	Crypt::decrypt($request->input('question_id'))
		]);
		
		
        $rules = [
			'requestid'			=>	'required|exists:eoi_request,requestid',
			'questionid'   		=> 	'required|exists:eoi_request_prebid_query,questionid',
			'prebid_response'	=> 	'required|regex:/^(?!.*<script>).*$/is',
        ];		
		
        $messages = [
			'requestid.required'		=>	'EoI request ID is required.',
			'requestid.exists' 			=> 	'The selected EoI request is invalid.',
			'questionid.required'		=> 	'Pre-bid query ID is required.',
			'questionid.exists' 		=> 	'The selected pre-bid query is invalid.',
			'prebid_response.required'	=> 	'Response is required.',
			'prebid_response.regex'		=> 	'Response contains invalid content.',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
	
		DB::beginTransaction();
		try
		{
			
			DB::table('eoi_request_prebid_query')
			->where('questionid',$validatedData['questionid'])
			->where('isprebid',1)
			->update([
				'answer'		=>	$validatedData['prebid_response'] ?? NULL,
				'answeredon'	=>	now()
			]);
				
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Response updated successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}
	}


	public function submitPreBidPmResponse(Request $request,$recordid=NULL)
	{
		$request->merge([
			'requestid'		=>	Crypt::decrypt($recordid),
		]);
		
		if($request->deadlinedate)
		{
			$request->merge([
				'deadlinedate'   => Carbon::parse($request->deadlinedate)->format('Y-m-d H:i:s'),
			]);			
		}
		
        $rules = [
			'requestid'			=>	'required|exists:eoi_request,requestid',
			'prebid_reply'		=> 	'required|regex:/^(?!.*<script>).*$/is',
			'reply_attachment' 	=> 	'nullable|file|mimetypes:application/pdf|max:10240',
			'deadlinedate' 		=>	'nullable|date|after_or_equal:today',
        ];		
		
        $messages = [
			'requestid.required'		=>	'EoI request ID is required.',
			'requestid.exists' 			=> 	'The selected EoI request is invalid.',
			'prebid_reply.required'		=> 	'Reply is required.',
			'prebid_reply.regex'		=> 	'Reply contains invalid content.',
			'reply_attachment.max' 		=> 	'Maximum 2 MB size is allowed',
			'reply_attachment.mimetypes'=> 	'Invalid file format',
			'reply_attachment.file' 	=> 	'Invalid file format',
			'deadlinedate.date'			=> 	'Invalid submission last date',
			'deadlinedate.after_or_equal'=> 'Invalid submission last date',
        ];

	
		$validatedData 	= 	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		
		if($request->hasFile('reply_attachment'))
		{
			$reply_attachment = $request->file('reply_attachment')->store('uploads/prebidenquiry', 'public');
		}
		
		$hasNullAnswer	=	DB::table('eoi_request_prebid_query')
							->where('requestid',$validatedData['requestid'])
							->whereNull('answer')
							->where('isprebid',1)
							->exists();
		
		if($hasNullAnswer)
		{
			return response()->json(['status'=>400,'message'=>'<b style="color:red;">All pre-bid queries for this EoI request must be answered before proceeding.</b>']);
		}
		DB::beginTransaction();
		try
		{
			$lastFromUserId	=	DB::table('eoi_request_prebid_forwarded')
									->where('requestid', $validatedData['requestid'])
									->where('postedby','CHiPS')
									->orderByDesc('forwardid')
									->where('isprebid',1)
									->value('fromuserid');			
			
			DB::table('eoi_request_prebid_forwarded')->insert([
				'requestid'		=>	$validatedData['requestid'],
				'fromuserid'	=>	$userId,
				'touserid'		=>	$lastFromUserId,
				'creationdate'	=>	now(),				
				'message'		=>	$validatedData['prebid_reply'] ?? NULL,
				'attachment'	=>	$reply_attachment ?? NULL,
				'postedby'		=>	'DEPARTMENT',
				'deadlinedate'	=>	$validatedData['deadlinedate'] ?? NULL,
				'isprebid'		=>	1
			]);

			DB::table('eoi_request_prebid')
			->where('requestid',$validatedData['requestid'])
			->where('isprebid',1)
			->update(['prebidstatus'=>'REPLIED']);
				
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Response updated successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}


    public function addEoIResource(Request $request,$requestid)
	{
	
		$requestid	=	Crypt::decrypt($requestid);


		$eoi 		= 	DB::table('eoi_request as a')
						->select('a.requestid','a.categoryid','a.eoinumber','a.projecttitle','a.projectduration','a.releasedate','a.prebidlastdate','a.deadlinedate','a.addition_resource_letter','b.departmentname')
						->leftjoin('department_tbl as b','b.userid','=','a.userid')
						->where('a.requestid',$requestid)
						->first();


		if(!$eoi)
		{
			return back()->with('fail', 'Invalid user detail provided. Please check and try again!')->withInput();
		}
		
		if($eoi->categoryid==2)
		{
			$eoi->resources	=	DB::table('eoi_request_detail as a')
								->select('a.*','b.sectorname','c.consultantposition')
								->join('sector_tbl as b','b.sectorid','=','a.sectorid')
								->join('position_tbl as c','c.positionid','=','a.positionid')
								->where('isAdditional',1)
								->where('requestid',$requestid)
								->get();
		}
		if($eoi->categoryid==1)
		{
			$eoi->resources	=	DB::table('eoi_request_detail')
								->select('*')
								->where('requestid',$requestid)
								->where('isAdditional',1)
								->get();
		}
		$sectors		=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		$positions		=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$experiences	=	DB::table('work_experience')
							->select('workexperience')
							->orderBy('experienceid')
							->get();
		
		$experiencelevel=	DB::table('remuneration_tbl')
							->where('experiencelevel','!=',0)
							->orderBy('experiencelevel')
							->get();
		
		return view('admin/manager/additional_resources', compact('eoi','sectors','positions','experiences','experiencelevel'));
    }


	public function uploadAdditionalResourceLetter(Request $request)
	{
		try
		{
			$request->validate([
				'requestid' => 'required',
				'addition_resource_letter' => 'required|file|mimes:pdf|max:10240', // 10 MB
			], [
				'addition_resource_letter.max' => 'File size must not exceed 10 MB.',
				'addition_resource_letter.mimes' => 'Only PDF, JPG, JPEG, and PNG files are allowed.',
			]);

			try
			{
				$requestid = Crypt::decrypt($request->requestid);
			}
			catch (\Exception $e) 
			{
				return response()->json([
					'status' => 0,
					'message' => 'Invalid EoI details provided. Please check and try again.'
				], 400);
			}


			$eoi = DB::table('eoi_request')->where('requestid', $requestid)->first();

			if(!$eoi) 
			{
				return response()->json([
					'status' => 0,
					'message' => 'EoI record not found.'
				], 404);
			}


			if ($request->hasFile('addition_resource_letter'))
			{

				$file		=	$request->file('addition_resource_letter');
				$fileName	= 	time() . '_' . $file->getClientOriginalName();
				$path		=	$file->storeAs('uploads/additional_resource_letters',$fileName,'public');

				DB::table('eoi_request')
				->where('requestid', $requestid)
				->update([
					'addition_resource_letter'	=> 	$path,
					'allowResourceAdd' 			=> 	1,
					'upload_time' 				=> 	now()
				]);


				return response()->json([
					'status' => 200,
					'message' => 'Additional resource requirement letter uploaded successfully.',
					'redirect' => route('add.eoiresource', [
						'requestid' => Crypt::encrypt($requestid)
					])
				]);
			}


			return response()->json([
				'status' => 0,
				'message' => 'Please select a file to upload.'
			], 400);


		}
		catch (\Exception $e)
		{
			return response()->json([
				'status'	=>	0,
				'message' 	=> 	'Something went wrong. Please try again.',
				'error' 	=> 	$e->getMessage()
			], 500);
		}
	}


	public function storeAdditionalEoiResource(Request $request)
	{
		$requestid	=	Crypt::decrypt($request->requestid);
		$eoi		=	DB::table('eoi_request')->where('requestid', $requestid)->first();
		
		if($eoi->categoryid==2)
		{
			$request->merge([
				'qualification' => $this->cleanHtml($request->qualification),
				'remark'        => $this->cleanHtml($request->remark),
			]);			

			$request->validate([
				'sectorid' 		=> 	'required',
				'positionid' 	=> 	'required',
				'experience' 	=> 	'required',
				'employmenttype'=> 	'required',
				'duration' 		=> 	'required',
				'qualification' => 	'required',
			], [
				'sectorid.required' 	=> 	'Please select sector.',
				'positionid.required' 	=> 	'Please select position.',
				'experience.required' 	=> 	'Experience is required.',
				'duration.required' 	=> 	'Please select duration.',
				'qualification.required'=> 	'Qualification is required.',
			]);
		}

		try
		{
			if(!$eoi)
			{
				return response()->json([
					'status' => 0,
					'message' => 'Invalid EoI details provided.'
				]);
			}
			$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');
			
			$rate	=	DB::table('remuneration_rate_list')->where('rateid',$rateid)->first();
			$order	=	DB::table('eoi_work_order')->where('requestid',$requestid)->first();
			if(!$order)
			{
				return response()->json([
					'status' => 0,
					'message' => 'Order has not been placed yet.'
				]);
			}
			$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			
			$validSector	=	DB::table('vendor_sector')->where('vendorid',$vendor->vendorid)->where('sectorid',$request->sectorid)->exists();
			if(!$validSector)
			{
				return response()->json([
					'status' => 0,
					'message' => 'The selected vendor is not associated with the selected sector. Please check and try again.'
				]);				
			}
			
			$remuneration	=	DB::table('remuneration_tbl')
								->where('categoryid',$eoi->categoryid)
								->where('tierid',$vendor->tierid)
								->where('sectorid',$request->sectorid)
								->where('positionid',$request->positionid)
								->where('rateid',$rate->rateid)
								->first();
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$eoi->categoryid)
							->where('tierid',$vendor->tierid)
							->where('tierid',$vendor->tierid)
							->where('isActive',1)
							->first();
			
			DB::table('eoi_request_detail')
			->insert([
				'requestid'			=>	$requestid,
				'tierid'			=>	$vendor->tierid ?? 0,
				'sectorid' 			=> 	$request->sectorid ?? 0,
				'positionid' 		=> 	$request->positionid ?? 0,
				'role' 				=> 	$request->role ?? NULL,
				'experience' 		=> 	$request->experience ?? NULL,
				'rateid'			=>	$rate->rateid ?? 0,
				'remunerationid'	=>	$remuneration->remunerationid ?? 0,
				'qualification' 	=> 	$request->qualification ?? NULL,
				'experiencelevel'	=>	$request->experiencelevel ?? 0,
				'duration' 			=> 	$request->duration,
				'remark' 			=> 	$request->remark ?? NULL,
				'employmenttype' 	=> 	$request->employmenttype ?? NULL,
				'creationdate'		=>	now(),
				'remuneration'		=>	$remuneration->remuneration ?? 0,
				'operating'			=>	$pricing->operatingmargin ?? 0,
				'tax'				=>	$pricing->tax ?? 0,
				'admin'				=>	$pricing->admincharge ?? 0,
				'isAdditional' 		=> 	1
			]);


			return response()->json([
				'status' => 200,
				'message' => 'Additional resource added successfully.'
			]);
		}
		catch (\Exception $e)
		{
			return response()->json([
				'status' => 0,
				'message' => 'Something went wrong. Please try again.'
			], 500);
		}
	}	
}


