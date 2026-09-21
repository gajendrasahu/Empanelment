<?php

namespace App\Http\Controllers\Master;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Hash;
use App\Services\OrderValueService;
class UtilityController extends Controller
{
	protected $passwordService;
	protected $valueService;
	public function __construct(PasswordService $passwordService,OrderValueService $valueService)
	{
		$this->passwordService 	= 	$passwordService;
		$this->valueService = $valueService;
	}

	public function showLogs()
	{
		$logFile = storage_path('logs/laravel-2026-07-20.log');

		if (!file_exists($logFile)) {
			abort(404, 'Log file not found.');
		}

		return response()->file($logFile);
	}
	public function deleteLogs()
	{
		$logFile = storage_path('logs/laravel-2026-07-20.log');

		if (file_exists($logFile)) {
			file_put_contents($logFile, ''); // Clears the log
		}
		return 'Laravel log cleared successfully.';
	}	
	
    public function getUpdates(Request $request,$fieldname,$requestid)
	{
		$userId		= 	$request->session()->get('userId');
		$userType	= 	$request->session()->get('userType');
		$fieldName	=	Crypt::decrypt($fieldname);
		$requestId	=	Crypt::decrypt($requestid);
		
		$user	=	DB::table('users_tbl')->where('userid',$userId)->first();
		
		if($userType!=$user->usertype)
		{
			return redirect()->route('logout');
		}
		
		
		$records	=	DB::table('log_eoi_request_updated')
							->where('field_name',$fieldName)
							->where('requestid',$requestId)
							->where(function ($query) {
								$query->whereNull('old_value')
									  ->orWhere('old_value', 'NOT LIKE', '%01-01-1970%');
							})							
							->get();
							
		$html = view('admin.ajaxpages.dateupdatesRecord',['data'=>$records,'field_name'=>$fieldName])->render();
		
		return response()->json(['status'=>200,'message'=>'Committee record added successfully.','formhtml' => $html]);
	}

	public function updateResourceTracking(Request $request)
	{
		while ($resource = DB::table('eoi_resource_deployment')->where('userid', 0)->orderBy('deploymentid', 'DESC')->first())
		{
			DB::beginTransaction();

			try
			{

				$plainPassword = $this->passwordService->generatePassword();

				$user_id = DB::table('users_tbl')->insertGetId([
					'name'          => $resource->name,
					'mobilenumber'  => $resource->mobilenumber,
					'email'         => $resource->email,
					'password'      => Hash::make($plainPassword),
					'pass_word'		=> $plainPassword,
					'isactive'      => 1,
					'issuper'       => 1,
					'isresource'    => 1
				]);

				$categoryid = DB::table('eoi_work_order')
					->where('orderid', $resource->orderid)
					->value('categoryid');

				DB::table('resource_tbl')->insert([
					'orderid'           => $resource->orderid,
					'userid'            => $user_id,
					'employee_code'     => $resource->employee_code,
					'categoryid'        => $categoryid,
					'tierid'            => $resource->tierid,
					'sectorid'          => $resource->sectorid,
					'positionid'        => $resource->positionid,
					'experiencelevel'   => $resource->experiencelevel,
					'role'              => $resource->role,
					'remuneration'      => $resource->remuneration,
					'operating'         => $resource->operating,
					'operatingvalue'    => $resource->operatingvalue,
					'tax'               => $resource->tax,
					'taxvalue'          => $resource->taxvalue,
					'admin'             => $resource->admincharge,
					'admincharge'       => $resource->adminvalue,
					'deployment_date'   => $resource->deployment_date,
					'deployed_date'     => $resource->deployed_date,
					'joining_date'      => $resource->deployed_date,
					'deployment_status' => $resource->deployment_status,
					'is_verified'       => $resource->is_verified,
					'lastdate'          => $resource->lastdate,
					'rateid'            => $resource->rateid,
				]);

				DB::table('eoi_resource_deployment')
					->where('deploymentid', $resource->deploymentid)
					->update([
						'userid' => $user_id
					]);

				$parentid = $resource->parentdeploymentid;

				while ($parentid != 0)
				{
					DB::table('eoi_resource_deployment')
						->where('deploymentid', $parentid)
						->update([
							'userid'        => $user_id,
							'name'          => $resource->name,
							'mobilenumber'  => $resource->mobilenumber,
							'email'         => $resource->email,
							'employee_code' => $resource->employee_code,
						]);

					$parentid = DB::table('eoi_resource_deployment')
						->where('deploymentid', $parentid)
						->value('parentdeploymentid');
				}

				DB::commit();

			}
			catch (\Illuminate\Database\QueryException $e)
			{
				DB::rollBack();

				// Duplicate entry error code
				if ($e->errorInfo[1] == 1062)
				{
					\Log::warning('Duplicate entry skipped: '.$e->getMessage());

					// Mark record processed or skip it
					DB::table('eoi_resource_deployment')
						->where('deploymentid', $resource->deploymentid)
						->update([
							'userid' => -1
						]);

					continue;
				}

				\Log::error($e->getMessage());

				continue;
			}
			catch (\Exception $e)
			{
				DB::rollBack();

				\Log::error($e->getMessage());

				continue;
			}		
		}

		return redirect('dashboard')->with('success', 'Resource detail updated successfully.');
	}
	
    public function tierWisePriceComparison(Request $request,$isTemp=NULL,$requestid=0)
    {
		$userId		= 	$request->session()->get('userId');
		$isTemp		=	Crypt::decrypt($isTemp);
		$requestid	=	Crypt::decrypt($requestid);

		$tiers		=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();

		if($isTemp=='Yes' && $requestid==0)
		{
			$records		=	DB::table('eoi_temp_requirement')->where('userid',$userId)->first();
			if($records)
			{
				$categoryid		=	$records->categoryid;
				$projectduration=	$records->projectduration;
				$html = [];
				if($categoryid==2)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_temp_requirement as a')
										->select('g.sectorname','h.consultantposition','e.remuneration as base_price','a.duration')
										->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.sectorid', '=', 'a.sectorid')
												 ->on('e.positionid', '=', 'a.positionid')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
										->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.userid', $userId)
										->where('a.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();
						
						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',date('Y\-m\-d'))
										->whereDate('endDate','>=',date('Y\-m\-d'))
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();
						
						$data 	= 	$this->valueService->calculateOrder(date('Y\-m\-d'),$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					return view('admin/pricingpages/pricecomparison',compact('html'));
				}
			}
		}
		
		if($isTemp=='No' && $requestid!=0)
		{
			$eoi		=	DB::table('eoi_request')->where('requestid',$requestid)->first();
			if($eoi)
			{
				$categoryid		=	$eoi->categoryid;
				$projectduration=	$eoi->projectduration;
				$html = [];
				if($categoryid==2)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_request_detail as a')
										->select('g.sectorname','h.consultantposition','e.remuneration as base_price','a.duration')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.sectorid', '=', 'a.sectorid')
												 ->on('e.positionid', '=', 'a.positionid')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
										->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.requestid', $requestid)
										->where('e.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();

						if(!$eoi->releasedate)
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->creationdate));
						}
						else
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->releasedate));
						}
						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',$startDate)
										->whereDate('endDate','>=',$startDate)
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();

						$data 	= 	$this->valueService->calculateOrder($startDate,$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'startDate'=>$startDate,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					return view('admin/pricingpages/pricecomparison',compact('html'));
				}
				if($categoryid==1)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_request_detail as a')
										->select('e.remuneration as base_price','a.duration','a.experience','a.experiencelevel','a.role')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.experiencelevel', '=', 'a.experiencelevel')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.requestid', $requestid)
										->where('e.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();

						if(!$eoi->releasedate)
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->creationdate));
						}
						else
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->releasedate));
						}
						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',$startDate)
										->whereDate('endDate','>=',$startDate)
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();

						$data 	= 	$this->valueService->calculateOrder($startDate,$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'startDate'=>$startDate,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					return view('admin/pricingpages/pricecomparison',compact('html'));
				}
				
			}
		}
    }


    public function showTierWisePriceComparison(Request $request,$isTemp=NULL,$requestid=0)
    {
		$userId		= 	$request->session()->get('userId');
		$isTemp		=	Crypt::decrypt($isTemp);
		$requestid	=	Crypt::decrypt($requestid);

		$tiers		=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();

		if($isTemp=='Yes' && $requestid==0)
		{
			$records	=	DB::table('eoi_temp_requirement')->where('userid',$userId)->first();
			if($records)
			{
				$categoryid		=	$records->categoryid;
				$projectduration=	$records->projectduration;
				$html			=	[];
				if($categoryid==2)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_temp_requirement as a')
										->select('g.sectorname','h.consultantposition','e.remuneration as base_price','a.duration')
										->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.sectorid', '=', 'a.sectorid')
												 ->on('e.positionid', '=', 'a.positionid')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
										->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.userid', $userId)
										->where('a.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();

						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',date('Y\-m\-d'))
										->whereDate('endDate','>=',date('Y\-m\-d'))
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();

						$data 	= 	$this->valueService->calculateOrder(date('Y\-m\-d'),$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					$view = view('admin.pricingpages.pricecomparison', compact('html'))->render();

					return response()->json([
						'status'   => 200,
						'formhtml' => $view
					]);
				}
				if($categoryid==1)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_temp_requirement as a')
										->select('e.remuneration as base_price','a.duration','a.experiencelevel','a.experience','a.role')
										->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.experiencelevel', '=', 'a.experiencelevel')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.userid', $userId)
										->where('a.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();

						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',date('Y\-m\-d'))
										->whereDate('endDate','>=',date('Y\-m\-d'))
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();

						$data 	= 	$this->valueService->calculateOrder(date('Y\-m\-d'),$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					$view = view('admin.pricingpages.pricecomparison', compact('html'))->render();

					return response()->json([
						'status'   => 200,
						'formhtml' => $view
					]);
				}
			}
		}

		if ($isTemp=='No' && $requestid!=0)
		{

			$eoi	= 	DB::table('eoi_request')->where('requestid', $requestid)->first();

			if($eoi)
			{
				$categoryid       = $eoi->categoryid;
				$projectduration  = $eoi->projectduration;
				$html = [];

				if($categoryid == 2)
				{

					foreach($tiers as $tier)
					{
						$resources = DB::table('eoi_request_detail as a')
							->select(
								'g.sectorname',
								'h.consultantposition',
								'e.remuneration as base_price',
								'a.duration'
							)
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->on('e.positionid', '=', 'a.positionid')
									 ->on('e.rateid', '=', 'a.rateid');
							})
							->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
							->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
							->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
							->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
							->where('a.requestid', $requestid)
							->where('e.categoryid', $categoryid)
							->where('e.tierid', $tier->tierid)
							->get();
						
						if(!$eoi->releasedate)
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->creationdate));
						}
						else
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->releasedate));
						}

						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',$startDate)
										->whereDate('endDate','>=',$startDate)
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();
						
						$data = $this->valueService->calculateOrder($startDate,$projectduration,$resources,$pricing);

						$html[] = view('admin.pricingpages.pricecalculation',compact('data','tier','startDate','categoryid','pricing'))->render();
					}

					$view = view('admin.pricingpages.pricecomparison', compact('html'))->render();

					return response()->json([
						'status'   => 200,
						'formhtml' => $view
					]);
				}
				if($categoryid==1)
				{
					foreach($tiers as $tier)
					{
						$resources	=	DB::table('eoi_request_detail as a')
										->select('e.remuneration as base_price','a.duration','a.experiencelevel','a.experience','a.role')
										->leftJoin('remuneration_tbl as e', function($join) {
											$join->on('e.experiencelevel', '=', 'a.experiencelevel')
												 ->on('e.rateid', '=', 'a.rateid');
										})
										->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
										->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
										->where('a.requestid', $requestid)
										->where('e.categoryid', $categoryid)
										->where('e.tierid', $tier->tierid)
										->get();

						if(!$eoi->releasedate)
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->creationdate));
						}
						else
						{
							$startDate	=	date('Y\-m\-d',strtotime($eoi->releasedate));
						}

						$pricing	=	DB::table('pricing_tbl')
										->whereDate('startDate','<=',$startDate)
										->whereDate('endDate','>=',$startDate)
										->where('categoryid',$categoryid)
										->where('tierid',$tier->tierid)
										->first();

						$data 	= 	$this->valueService->calculateOrder($startDate,$projectduration,$resources,$pricing);
						$html[]	=	view('admin.pricingpages.pricecalculation',['data'=>$data,'tier'=>$tier,'categoryid'=>$categoryid,'pricing'=>$pricing])->render();
					}
					$view = view('admin.pricingpages.pricecomparison', compact('html'))->render();

					return response()->json([
						'status'   => 200,
						'formhtml' => $view
					]);
				}
				
			}

			return response()->json([
				'status' => 404,
				'message' => 'Data not found.'
			]);
		}
    }


	public function calculateOldEoIValue(Request $request)
	{
		$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
		
		//$data	= 	DB::table('eoi_request as a')->select('a.*')->orderBy('a.creationdate','DESC')->get();

		$data	=	DB::table('eoi_request as a')
					->selectRaw("
						a.*,
						c.tierid,
						CASE
							WHEN DATE_ADD(a.releasedate, INTERVAL a.projectduration MONTH) > '2025-06-01'
							THEN '2025-06-01'
							ELSE a.releasedate
						END AS releasedate,

						CASE
							WHEN DATE_ADD(a.releasedate, INTERVAL a.projectduration MONTH) > '2025-06-01'
							THEN DATE_ADD(a.releasedate, INTERVAL a.projectduration MONTH)
							ELSE NULL
						END AS enddate
					")
					->join(
						DB::raw('(SELECT requestid, MIN(vendorid) AS vendorid FROM eoi_work_order WHERE isCancelled = 0 GROUP BY requestid) b'),
						'b.requestid',
						'=',
						'a.requestid'
					)
					->join('vendor_tbl as c', 'c.vendorid', '=', 'b.vendorid')
					->whereDate('a.releasedate', '<', '2025-06-01')
					->orderBy('a.releasedate', 'DESC')
					->get();

		foreach($data as $eoi)
		{
			$rateId 	= 	2;
			
			if($eoi->categoryid==2)
			{
				$resources	=	DB::table('eoi_request_detail as a')
								->select('g.sectorname','h.consultantposition','e.remuneration as base_price')
								->leftJoin('remuneration_tbl as e', function($join) {
									$join->on('e.sectorid', '=', 'a.sectorid')
										 ->on('e.positionid', '=', 'a.positionid');
								})
								->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'e.categoryid')									
								->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
								->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
								->leftJoin('position_tbl as h','h.positionid','=','a.positionid')
								->leftJoin('tiermaster_tbl as c','c.tierid','=','e.tierid')
								->where('a.requestid',$eoi->requestid)
								->where('e.categoryid',$eoi->categoryid)
								->where('e.tierid',$eoi->tierid)
								->where('e.rateid',2)
								->get();
				
				$valueData=	$this->valueService->calculateOlderSummary($eoi->releasedate,$eoi->enddate,$resources);
				
				DB::table('eoi_request')
				->where('requestid',$eoi->requestid)
				->update([
					'eoi_resource_value'	=>	$valueData['salary'] ?? 0,
					'eoi_tax'				=>	$valueData['gst'] ?? 0,
					'eoi_total_value'		=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
				]);
			}
		}
		
	}
	
	public function calculateEoIValue(Request $request)
	{
		$tiers	=	DB::table('tiermaster_tbl')->orderBy('tierid')->get();
		
		//$data	= 	DB::table('eoi_request as a')->select('a.*')->orderBy('a.creationdate','DESC')->get();

		$data	=	DB::table('eoi_request as a')
					->select('a.*','c.tierid')
					->join(
						DB::raw('(SELECT requestid, MIN(vendorid) AS vendorid FROM eoi_work_order where isCancelled=0 GROUP BY requestid) b'),
						'b.requestid',
						'=',
						'a.requestid'
					)
					->join('vendor_tbl as c','c.vendorid','=','b.vendorid')
					->whereDate('releasedate','>=','2025-06-01')
					->orderBy('a.releasedate','DESC')
					->get();		

		foreach($data as $eoi)
		{
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate','<=',$eoi->releasedate)
							->where('endDate','>=',$eoi->releasedate)
							->where('categoryid',2)
							->value('rateid');
			
			if($eoi->categoryid==2)
			{
				$resources	=	DB::table('eoi_request_detail as a')
								->select('g.sectorname','h.consultantposition','e.remuneration as base_price')
								->leftJoin('remuneration_tbl as e', function($join) {
									$join->on('e.sectorid', '=', 'a.sectorid')
										 ->on('e.positionid', '=', 'a.positionid')
										 ->on('e.rateid', '=', 'a.rateid');
								})
								->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'e.categoryid')									
								->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
								->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
								->leftJoin('position_tbl as h','h.positionid','=','a.positionid')
								->leftJoin('tiermaster_tbl as c','c.tierid','=','e.tierid')
								->where('a.requestid',$eoi->requestid)
								->where('e.categoryid',$eoi->categoryid)
								->where('e.tierid',$eoi->tierid)
								->get();
				
				$valueData=	$this->valueService->calculateSummary($eoi->releasedate,$eoi->projectduration,$resources);
				
				DB::table('eoi_request')
				->where('requestid',$eoi->requestid)
				->update([
					'eoi_resource_value'	=>	$valueData['salary'] ?? 0,
					'eoi_tax'				=>	$valueData['gst'] ?? 0,
					'eoi_total_value'		=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
				]);
			}
		}
	}

	public function calculateOrderValue(Request $request)
	{
		// FOR FIRST ORDER WITH START DATE NULL
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->join('eoi_request as b','b.requestid','=','a.requestid')
					->where('a.categoryid',2)
					->where('a.isCancelled','=',0)
					->whereDate('b.releasedate','>=','2025-07-01')
					->where('a.parentorderid','=',0)
					->where('a.pbgCalculated','=',0)
					->whereDate('a.orderdate','>=','2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->get();					
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid', '=', 'a.sectorid')
										 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->get();							
				
				$valueData=	$this->valueService->calculateOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	1,
					'pbgCalculated'			=>	1,
				]);
			}
		}

		// FOR EXTENDED ORDER WITH START DATE NULL
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->join('eoi_request as b','b.requestid','=','a.requestid')
					->where('a.categoryid',2)
					->where('a.isCancelled','=',0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate','>=','2025-07-01')
					->where('a.parentorderid','!=',0)
					->whereDate('a.orderdate','>=','2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->get();					
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->get();							
				
				$valueData=	$this->valueService->calculateOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	2,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// FOR FIRST ORDER WITH START DATE VALUE
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->join('eoi_request as b','b.requestid','=','a.requestid')
					->where('a.categoryid',2)
					->where('a.isCancelled','=',0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate','>=','2025-07-01')
					->whereDate('a.orderdate','>=','2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate','<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate','a.deploymentid','a.sectorid','a.positionid','a.orderid')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualOrderSummary($order->orderdate,$order->workorderduedate,$resources,$vendor);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// DIRECT WORK ORDERS WITH START DATE VALUE NULL
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->where('a.categoryid',2)
					->where('a.isCancelled','=',0)
					->where('a.pbgCalculated','=',0)
					->whereDate('a.orderdate','>=','2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->where('a.isCancelled',0)
					->where('a.requestid',0)
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData	=	$this->valueService->calculateOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// DIRECT WORK ORDERS WITH START DATE VALUE
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->where('a.categoryid',2)
					->where('a.pbgCalculated','=',0)
					->whereDate('a.orderdate','>=','2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->where('a.isCancelled',0)
					->where('a.requestid',0)					
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate','a.deploymentid','a.sectorid','a.positionid','a.orderid')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualOrderSummary($order->orderdate,$order->workorderduedate,$resources,$vendor);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// DIRECT WORK ORDERS In Between WITH START DATE VALUE
		$data	=	DB::table('eoi_work_order as a')
					->selectRaw("
						a.*,
						'2025-07-01' AS order_date
					")
					->where('a.categoryid',2)
					->where('a.pbgCalculated','=',0)
					->whereDate('a.orderdate', '<', '2025-07-01')
					->whereDate('a.workorderduedate', '>', '2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->where('a.isCancelled',0)
					->where('a.requestid',0)					
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate','<=',$order->order_date)
							->where('endDate','>=',$order->order_date)
							->where('categoryid',2)
							->value('rateid');
			if(!$rateId)
			{
				$rateId	=	2;
			}
			
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate','a.deploymentid','a.sectorid','a.positionid','a.orderid')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualOrderSummary($order->orderdate,$order->workorderduedate,$resources,$vendor);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}
		

		// FOR ORDER WITH START DATE NULL OLDER ORDER BEFORE 01-06-2025 EXPIRY AFTER 01-06-2025
		$data	=	DB::table('eoi_work_order as a')
					->selectRaw("
						a.*,
						'2025-07-01' AS order_date
					")
					->join('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate', '<', '2025-07-01')
					->whereDate('a.orderdate', '<', '2025-07-01')
					->whereDate('a.workorderduedate', '>', '2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->get();
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->order_date)
							->where('endDate', '>=', $order->order_date)
							->where('categoryid',2)
							->value('rateid');
			if(!$rateId)
			{
				$rateId	=	2;
			}
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid', '=', 'a.sectorid')
										 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->get();							
				
				$valueData=	$this->valueService->calculateOrderSummary($order->order_date,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	1,
					'inBetween'				=>	1,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// FOR ORDER WITH START DATE VALUE OLDER ORDER BEFORE 01-06-2025 EXPIRY AFTER 01-06-2025
		$data	=	DB::table('eoi_work_order as a')
					->selectRaw("
						a.*,
						'2025-07-01' AS order_date
					")
					->join('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate', '<', '2025-07-01')
					->whereDate('a.orderdate', '<', '2025-07-01')
					->whereDate('a.workorderduedate', '>', '2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->order_date)
							->where('endDate', '>=', $order->order_date)
							->where('categoryid',2)
							->value('rateid');
			if(!$rateId)
			{
				$rateId	=	2;
			}

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate','a.deploymentid','a.sectorid','a.positionid','a.orderid')
								->join('remuneration_tbl as b', function ($join) {
								$join->on('b.sectorid', '=', 'a.sectorid')
									 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualOrderSummary($order->order_date,$order->workorderduedate,$resources,$vendor);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}


		// FOR ORDER WITH START DATE NULL OLDER ORDER AFTER 01-06-2025
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->join('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate', '<', '2025-07-01')
					->whereDate('a.orderdate', '>', '2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->get();
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid', '=', 'a.sectorid')
										 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->get();							
				
				$valueData=	$this->valueService->calculateOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	1,
					'pbgCalculated'			=>	1,
				]);
			}
		}

		// FOR ORDER WITH START DATE VALUE OLDER ORDER AFTER 01-06-2025
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*')
					->join('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.pbgCalculated','=',0)
					->whereDate('b.releasedate', '<', '2025-07-01')
					->whereDate('a.orderdate', '>', '2025-07-01')
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->get();		
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');

			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate','a.deploymentid','a.sectorid','a.positionid','a.orderid')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid','=','a.sectorid')
										 ->on('b.positionid','=','a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->whereIn('a.deployment_status',['Pending','Active','Extended','Released'])
								->where('b.rateid', $rateId)
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualOrderSummary($order->orderdate,$order->workorderduedate,$resources,$vendor);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	3,
					'pbgCalculated'			=>	1,
				]);
			}
		}

		/*
		// AS CONDITION FOR ORDERS WITH START DATE NULL
		$data	=	DB::table('eoi_work_order as a')
					->selectRaw("
						a.*,
						CASE
							WHEN a.orderdate < '2025-06-01'
							THEN '2025-06-01'
							ELSE a.orderdate
						END AS orderdate,
						CURDATE() AS workorderduedate
					")
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.isActiveOrder', 1)
					->whereDate('a.workorderduedate', '<', today())
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNull('c.startDate');
					})
					->get();
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid', '=', 'a.sectorid')
										 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended'])
								->get();							
				
				$valueData=	$this->valueService->calculateOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	1
				]);
			}
		}

		// AS CONDITION FOR ORDERS WITH START DATE NULL
		$data	=	DB::table('eoi_work_order as a')
					->selectRaw("
						a.*,
						CASE
							WHEN a.orderdate < '2025-06-01'
							THEN '2025-06-01'
							ELSE a.orderdate
						END AS orderdate,
						CURDATE() AS workorderduedate
					")
					->where('a.categoryid', 2)
					->where('a.isCancelled', 0)
					->where('a.isActiveOrder', 1)
					->whereDate('a.workorderduedate', '<', today())
					->whereExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_resource_deployment as c')
							->whereColumn('c.orderid', 'a.orderid')
							->whereNotNull('c.startDate');
					})
					->get();
					
		foreach($data as $order)
		{
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			$rateId 	= 	DB::table('remuneration_rate_list')
							->where('startDate', '<=', $order->orderdate)
							->where('endDate', '>=', $order->orderdate)
							->where('categoryid',2)
							->value('rateid');
			if($rateId)
			{
				$resources 	= 	DB::table('eoi_resource_deployment as a')
								->select('b.remuneration as base_price','a.startDate','a.endDate')
								->join('remuneration_tbl as b', function ($join) {
									$join->on('b.sectorid', '=', 'a.sectorid')
										 ->on('b.positionid', '=', 'a.positionid');
								})
								->where('a.orderid', $order->orderid)
								->where('b.categoryid', $vendor->categoryid)
								->where('b.tierid', $vendor->tierid)
								->where('b.rateid', $rateId)
								->whereIn('a.deployment_status',['Pending','Active','Extended'])
								->get();							
				
				$valueData=	$this->valueService->calculateIndividualAsOrderSummary($order->orderdate,$order->workorderduedate,$resources);
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'order_resource_value'	=>	$valueData['salary'] ?? 0,
					'order_tax_value'		=>	$valueData['gst'] ?? 0,
					'order_value'			=>	$valueData['total'] ?? 0,
					'isCalculated'			=>	1,
					'isFirst'				=>	1
				]);
			}
		}
		*/
	}
	
	public function showWorkOrderValue(Request $request,$orderid=0)
	{
		$request->merge([
			'requestid' => Crypt::decrypt($request->requestid),
		]);
		
		$rules = [
			'requestid'		=>	'required|exists:eoi_request,requestid',
			'vendorid'		=>	'required',
			'rateid'    	=>	'required|numeric',
			'order_date'	=> 	'required|date_format:d-m-Y',
			'order_duedate' => 	'required|date_format:d-m-Y',
		];

        $messages = [
			'requestid.required'		=>	'Invalid eoi detail',
			'requestid.exists'			=>	'No EOI details found.',
			'rateid.required'			=>	'Rate list name is mandatory',
			'rateid.numeric'			=>	'Invalid rate list value provided',
			'vendorid.required'			=>	'Invalid vendorid',
			'order_date.required'       => 	'Please select an order date.',
			'order_date.date_format'    => 	'Please enter a valid order date.',
			'order_duedate.required'    => 	'Please select an order due date.',
			'order_duedate.date_format' => 	'Please enter a valid order due date.',
        ];

        $validatedData	=	$request->validate($rules,$messages);
		
		$eoi	=	DB::table('eoi_request')
					->select('requestid','categoryid')
					->where('requestid',$validatedData['requestid'])
					->first();
		
		$vendor	=	DB::table('vendor_tbl')
					->select('vendorid','categoryid','tierid','companyname','shortname')
					->where('vendorid',$validatedData['vendorid'])
					->first();
		
		$resources = [];
		
		foreach($request->recordids as $i => $id)
		{
			if($vendor->categoryid==2)
			{
				$startRaw = $request->startDate[$i] ?? null;
				$endRaw   = $request->endDate[$i] ?? null;

				if(!$id || !$startRaw || !$endRaw)
				{
					continue;
				}
				$resource	=	DB::table('eoi_request_detail')->where('recordid',$request->recordids[$i])->first();

				$resources[] = [
					'role'    			=>	$resource['role'],
					'sectorid'    		=>	$resource['sectorid'],
					'positionid'		=> 	$resource['positionid'],
					'experiencelevel'  	=> 	$resource['experiencelevel'],
					'experience'  		=> 	$resource['experience'],
					'startDate'			=>	$request->startDate[$i],
					'endDate'			=>	$request->endDate[$i],
				];

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

				if($end->lte($start))
				{
					return response()->json([
						'status' => false,
						'errors' => [
							"endDate.$i" => ["End date must be greater than start date"]
						]
					], 422);
				}
			}
			if($vendor->categoryid==1)
			{
				$levelRaw = trim($request->exp_level[$i] ?? '');
				$startRaw = trim($request->startDate[$i] ?? '');
				$endRaw   = trim($request->endDate[$i] ?? '');

				// Check if any one field is filled
				$hasAnyValue = $levelRaw || $startRaw || $endRaw;

				// If any field is entered, then all fields are required
				if($hasAnyValue)
				{
					$errors = [];

					if(!$id)
					{
						$errors["id.$i"] = ["ID is required"];
					}

					if(!$levelRaw)
					{
						$errors["exp_level.$i"] = ["Experience level is required for the selected record"];
					}

					if(!$startRaw)
					{
						$errors["startDate.$i"] = ["Start date is required for the selected record"];
					}

					if(!$endRaw)
					{
						$errors["endDate.$i"] = ["End date is required for the selected record"];
					}

					// Return validation errors
					if(!empty($errors))
					{
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

					if($end->lte($start))
					{
						return response()->json([
							'status' => false,
							'errors' => [
								"endDate.$i" => ["End date must be greater than start date"]
							]
						], 422);
					}
					$resource	=	DB::table('eoi_request_detail')->where('recordid',$request->recordids[$i])->first();

					$resources[] = [
						'role'    			=>	$resource['role'],
						'sectorid'    		=>	$resource['sectorid'],
						'positionid'		=> 	$resource['positionid'],
						'experiencelevel'  	=> 	$request->exp_level[$i],
						'experience'  		=> 	$resource['experience'],
						'startDate'			=>	$request->startDate[$i],
						'endDate'			=>	$request->endDate[$i],
					];
					
				}
			}
		}
		foreach(($request->sectorids ?? []) as $i => $id)
		{
			$resources[] = [
				'role'    			=>	'',
				'sectorid'    		=>	$request->sectorids[$i],
				'positionid'		=> 	$request->positionids[$i],
				'experiencelevel'  	=> 	0,
				'experience'  		=> 	'',
				'startDate'			=>	$request->startDate[$i],
				'endDate'			=>	$request->endDate[$i],
			];
			
		}
		foreach(($request->new_roles ?? []) as $i => $role)
		{
			$resources[] = [
				'role'    			=>	$role,
				'sectorid'    		=>	0,
				'positionid'		=> 	0,
				'experiencelevel'  	=> 	$request->new_exp_level[$i],
				'experience'  		=> 	$request->new_experience[$i],
				'startDate'			=>	$request->new_startDate[$i],
				'endDate'			=>	$request->new_endDate[$i],
			];			
		}
		$pricing	=	DB::table('pricing_tbl')
						->whereDate('startDate','<=',date('Y\-m-d',strtotime($validatedData['order_date'])))
						->whereDate('endDate','>=',date('Y\-m-d',strtotime($validatedData['order_duedate'])))
						->where('categoryid',$vendor->categoryid)
						->where('tierid',$vendor->tierid)
						->where('isActive',1)
						->first();
		
		$data 	= 	$this->valueService->getCalculatedOrderValue($vendor,$resources);
		
		$view	=	view('admin.pricingpages.orderpricecalculation',['data'=>$data,'pricing'=>$pricing,'vendor'=>$vendor])->render();

		return response()->json(['status'   => 200,'formhtml' => $view]);
		
	}


}
