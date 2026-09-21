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
use App\Services\ResourceService;
class ReportingController extends Controller
{
	protected $resourceService;
	public function __construct(ResourceService $resourceService)
	{
		$this->resourceService	=	$resourceService;
	}
	
    public function getDepartmentWiseResourceData(Request $request)
	{
		$projectid 		=	intval($request->input('projectid'));
		$categoryid 	=	intval($request->input('categoryid'));
		$vendorid 		=	intval($request->input('vendorid'));
		$departmentid 	=	intval($request->input('departmentid'));
		$managerid 		=	intval($request->input('managerid'));
		$sectorid 		=	intval($request->input('sectorid'));
		$positionid 	=	intval($request->input('positionid'));
		$experiencelevel=	intval($request->input('experiencelevel'));
		$pagesearch		=	$request->input('pagesearch');
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		
		$format_id		=	intval($request->input('format_id'));

		if($format_id==3 && $categoryid==1)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/awdresourceTable',compact('data','categoryid','format_id'));
		}

		if($format_id==2 && $categoryid==1)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/awdresourceTable',compact('data','categoryid','format_id'));
		}

		if($format_id==1 && $categoryid==1)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/awdresourceTable',compact('data','categoryid','format_id'));
		}

		if($format_id==1 && $categoryid==2)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/departmentwiseresourceTable',compact('data','categoryid','format_id'));
		}
		if($format_id==2 && $categoryid==2)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/departmentwiseresourceTable',compact('data','categoryid','format_id'));
		}
		if($format_id==3 && $categoryid==2)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/departmentwiseresourceTable',compact('data','categoryid','format_id'));
		}
		if($format_id==4 && $categoryid==2)
		{
			$data	=	$this->resourceService->formatedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house,$projectid);
			return view('admin/reporting/ajaxpages/departmentwiseresourceTable',compact('data','categoryid','format_id'));
		}
	}
    public function departmentResources(Request $request)
	{
        Session::put('adminmenu','reporting');
		Session::put('adminsubmenu','deptwiseresources');

		Session::put('menid',153);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=153 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		
		$departments	=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname','a.ispm','a.isdepartment')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.isdepartment',1)
							->orderBy('b.departmentname')
							->get();

		$managers		=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname','a.ispm','a.isdepartment','a.name')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.ispm',1)
							->orderBy('b.departmentname')
							->get();
		
		$vendors	=	DB::table('vendor_tbl')->where('categoryid',2)->orderBy('companyname')->get();
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('positionid','DESC')->get();
		$levels		=	DB::table('remuneration_tbl')
						->select('experiencelevel')
						->where('experiencelevel','!=',0)
						->distinct('experiencelevel')
						->get();
						
		$projects	=	DB::table('project_tbl')->orderBy('project_name')->get();
		
        return view('admin/reporting/departmentwise_resource',compact('category','vendors','departments','managers','sectors','positions','levels','projects'));
    }
	
    public function summaryDetail(Request $request){
        Session::put('adminmenu','reporting');
		Session::put('adminsubmenu','eoisummary');

		Session::put('menid',147);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=147 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		
		$departments	=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.isdepartment',1)
							->orderBy('b.departmentname')
							->get();

		$managers		=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.ispm',1)
							->orderBy('b.departmentname')
							->get();

		$firms		=	DB::table('vendor_tbl')
							->select('vendorid','companyname','shortname')
							->where('categoryid',2)
							->orderBy('companyname')
							->get();

		$projects	=	DB::table('project_tbl')
							->select('projectid','project_name')
							->orderBy('project_name')
							->get();
							
		
        return view('admin/reporting/eoi_summary',compact('category','departments','managers','firms','projects'));
    }

    public function getSummaryData(Request $request)
	{
		$categoryid 	=	intval($request->input('categoryid'));
		$summarytype 	=	intval($request->input('summarytype'));
		if($summarytype==1)
		{
			$data = DB::table('eoi_work_order as wo')

				->leftJoin('eoi_request as er', 'er.requestid', '=', 'wo.requestid')

				/* =========================
				   RESOURCE SUMMARY
				==========================*/
				->leftJoin(
					DB::raw('
						(
							SELECT
								orderid,

								COUNT(deploymentid) as total_resources,

								SUM(CASE
									WHEN deployment_status IN ("Active","Released")
									THEN 1 ELSE 0 END
								) as deployed_resources,

								SUM(CASE
									WHEN deployment_status = "Active"
									THEN 1 ELSE 0 END
								) as active_resources,

								SUM(CASE
									WHEN deployment_status = "Released"
									THEN 1 ELSE 0 END
								) as released_resources,

								SUM(CASE
									WHEN deployment_status = "Pending"
									THEN 1 ELSE 0 END
								) as undeployed_resources

							FROM eoi_resource_deployment
							GROUP BY orderid
						) as dep
					'),
					'dep.orderid',
					'=',
					'wo.orderid'
				)

				/* =========================
				   INVOICE SUMMARY
				==========================*/
				->leftJoin(
					DB::raw('
						(
							SELECT
								orderid,
								SUM(invoice_value) as invoice_raised,
								SUM(paid_value) as paid_amount
							FROM vendor_invoices
							GROUP BY orderid
						) as inv
					'),
					'inv.orderid',
					'=',
					'wo.orderid'
				)

				/* =========================
				   FILTER (EOI category)
				==========================*/
				->where('er.categoryid', '=', $categoryid)

				/* =========================
				   SELECT
				==========================*/
				->select(

					// EOI Number
					'er.eoinumber',
					'er.projecttitle',

					// Display label (EOI or Direct WO)
					DB::raw('
						CASE
							WHEN wo.requestid = 0
							THEN CONCAT("DIRECT-WO-", wo.orderid)
							ELSE er.eoinumber
						END as display_number
					'),

					// Reference grouping key
					DB::raw('
						CASE
							WHEN wo.requestid = 0
							THEN wo.orderid
							ELSE wo.requestid
						END as reference_id
					'),

					// Total Work Orders
					DB::raw('COUNT(DISTINCT wo.orderid) as total_work_orders'),

					// Active Work Orders
					DB::raw('
						SUM(CASE
							WHEN wo.isActiveOrder = 1 and wo.workorderduedate>now()
							THEN 1 ELSE 0 END
						) as active_work_orders
					'),

					/* =========================
					   RESOURCE METRICS
					==========================*/
					DB::raw('SUM(COALESCE(dep.total_resources,0)) as total_resources'),
					DB::raw('SUM(COALESCE(dep.deployed_resources,0)) as deployed_resources'),
					DB::raw('SUM(COALESCE(dep.active_resources,0)) as active_resources'),
					DB::raw('SUM(COALESCE(dep.released_resources,0)) as released_resources'),
					DB::raw('SUM(COALESCE(dep.undeployed_resources,0)) as undeployed_resources'),

					/* =========================
					   FINANCIALS
					==========================*/
					DB::raw('SUM(COALESCE(wo.workorderamount,0)) as order_value'),
					DB::raw('SUM(COALESCE(inv.invoice_raised,0)) as invoice_raised'),
					DB::raw('SUM(COALESCE(inv.paid_amount,0)) as paid_amount'),

					/* =========================
					   EXPIRY STATUS
					==========================*/
					DB::raw('
						SUM(
							CASE
								WHEN wo.workorderduedate IS NOT NULL
								 AND wo.workorderduedate < CURDATE()
								THEN 1 ELSE 0
							END
						) as expired_work_orders
					'),

					DB::raw('
						MAX(wo.workorderduedate) as latest_due_date
					')
				)

				/* =========================
				   GROUPING
				==========================*/
				->groupBy(
					DB::raw('
						CASE
							WHEN wo.requestid = 0
							THEN wo.orderid
							ELSE wo.requestid
						END
					')
				)

				->orderBy('reference_id', 'DESC')

				->get();
			return view('admin/ajaxpages/reportsummaryTable', ['data' => $data]);
		}
    }
    public function getResourceList(Request $request)
	{
		$positionid		=	intval($request->input('positionid'));
		$format_id		=	intval($request->input('format_id'));
		$departmentid	=	intval($request->input('departmentid'));
		$vendorid		=	intval($request->input('vendorid'));
		$sectorid		=	intval($request->input('sectorid'));
		$orderid		=	intval($request->input('orderid'));
		$categoryid		=	intval($request->input('categoryid'));
		
		try
		{
			if($format_id==1 && $categoryid==2)
			{
				$html	=	$this->resourceService->getResourceReportList($positionid,$format_id,$departmentid,$vendorid,$sectorid,$orderid,$categoryid);
			}
			if($format_id==2 && $categoryid==2)
			{
				$html	=	$this->resourceService->getOrderWiseResourceList($format_id,$orderid,$departmentid,$categoryid,$sectorid,$positionid);
			}
			if($format_id==4 && $categoryid==2)
			{
				$html	=	$this->resourceService->getOrderWiseResourceList($format_id,$orderid,$departmentid,$categoryid,$sectorid,$positionid);
			}
			return response()->json(['status'=>200,'message'=>'Resource Detail.','html'=> $html]);
		}
		catch(Exception $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
		
		
	}
    public function getAwdResourceList(Request $request)
	{
		$format_id		=	intval($request->input('format_id'));
		$departmentid	=	intval($request->input('departmentid'));
		$vendorid		=	intval($request->input('vendorid'));
		$orderid		=	intval($request->input('orderid'));
		$categoryid		=	intval($request->input('categoryid'));
		$experiencelevel=	intval($request->input('experiencelevel'));
		
		try
		{
			if($format_id==3 && $categoryid==1)
			{
				$html	=	$this->resourceService->getAwdResourceReportList($format_id,$departmentid,$vendorid,$orderid,$categoryid,$experiencelevel);
			}
			if($format_id==2 && $categoryid==1)
			{
				$html	=	$this->resourceService->getAwdResourceReportList($format_id,$departmentid,$vendorid,$orderid,$categoryid,$experiencelevel);
			}
			if($format_id==1 && $categoryid==1)
			{
				$html	=	$this->resourceService->getAwdResourceReportList($format_id,$departmentid,$vendorid,$orderid,$categoryid,$experiencelevel);
			}
			if($format_id==2 && $categoryid==2)
			{
				$html	=	$this->resourceService->getOrderWiseResourceList($format_id,$orderid,$departmentid,$categoryid,$sectorid,$positionid);
			}
			if($format_id==4 && $categoryid==2)
			{
				$html	=	$this->resourceService->getOrderWiseResourceList($format_id,$orderid,$departmentid,$categoryid,$sectorid,$positionid);
			}
			return response()->json(['status'=>200,'message'=>'Resource Detail.','html'=> $html]);
		}
		catch(Exception $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
		
		
	}

	public function getVendorsByCategory(Request $request)
	{
		return DB::table('vendor_tbl')
				->where('categoryid', $request->categoryid)
				->orderBy('companyname')
				->get(['vendorid', 'companyname']);
	}

    public function undeployedResources(Request $request)
	{
        Session::put('adminmenu','reporting');
		Session::put('adminsubmenu','undeployedresource');

		Session::put('menid',154);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=154 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		
		$departments	=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname','a.ispm','a.isdepartment')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.isdepartment',1)
							->orderBy('b.departmentname')
							->get();

		$managers	=	DB::table('users_tbl as a')
							->select('b.departmentid','b.departmentname','b.shortname','a.ispm','a.isdepartment')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.ispm',1)
							->orderBy('b.departmentname')
							->get();
		
		$vendors	=	DB::table('vendor_tbl')->where('categoryid',2)->orderBy('companyname')->get();
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('positionid','DESC')->get();
		$levels		=	DB::table('remuneration_tbl')
						->select('experiencelevel')
						->where('experiencelevel','!=',0)
						->distinct('experiencelevel')
						->get();
		
        return view('admin/reporting/undeployed_resource',compact('category','vendors','departments','managers','sectors','positions','levels'));
    }

    public function getUndeployedResourceData(Request $request)
	{
		$categoryid 	=	intval($request->input('categoryid'));
		$vendorid 		=	intval($request->input('vendorid'));
		$departmentid 	=	intval($request->input('departmentid'));
		$managerid	 	=	intval($request->input('managerid'));
		$sectorid 		=	intval($request->input('sectorid'));
		$positionid 	=	intval($request->input('positionid'));
		$experiencelevel=	intval($request->input('experiencelevel'));
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$pagesearch		=	$request->input('pagesearch');
		
		$format_id		=	intval($request->input('format_id'));
		if($format_id==1 && $categoryid==1)
		{
			$data	=	$this->resourceService->undeployedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house);
			return view('admin/reporting/ajaxpages/undeployedawdresourceTable',compact('data','categoryid','format_id'));
		}

		if($format_id==1 && $categoryid==2)
		{
			$data	=	$this->resourceService->undeployedData($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$pagesearch,$format_id,$managerid,$in_house);
			return view('admin/reporting/ajaxpages/undeployedresourceTable',compact('data','categoryid','format_id'));
		}
	}

    public function getUndeployedResourceList(Request $request)
	{
		$positionid		=	intval($request->input('positionid'));
		$format_id		=	intval($request->input('format_id'));
		$departmentid	=	intval($request->input('departmentid'));
		$vendorid		=	intval($request->input('vendorid'));
		$sectorid		=	intval($request->input('sectorid'));
		$orderid		=	intval($request->input('orderid'));
		$categoryid		=	intval($request->input('categoryid'));
		
		try
		{
			if($format_id==1 && $categoryid==2)
			{
				$html	=	$this->resourceService->getUndeployedResourceList($format_id,$orderid,$departmentid,$categoryid,$sectorid,$positionid);
			}
			return response()->json(['status'=>200,'message'=>'Resource Detail.','html'=> $html]);
		}
		catch(Exception $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
		
		
	}
    public function getAwdUndeployedResourceList(Request $request)
	{
		$format_id		=	intval($request->input('format_id'));
		$departmentid	=	intval($request->input('departmentid'));
		$vendorid		=	intval($request->input('vendorid'));
		$orderid		=	intval($request->input('orderid'));
		$categoryid		=	intval($request->input('categoryid'));
		$experiencelevel=	intval($request->input('experiencelevel'));		

		
		try
		{
			if($format_id==1 && $categoryid==1)
			{
				$html	=	$this->resourceService->getAwdUndeployedResourceList($format_id,$departmentid,$vendorid,$orderid,$categoryid,$experiencelevel);
			}
			return response()->json(['status'=>200,'message'=>'Resource Detail.','html'=> $html]);
		}
		catch(Exception $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
		
		
	}
    public function getEoISummaryDetail(Request $request)
	{
		$projectid		=	intval($request->input('project_id'));
		$categoryid		=	intval($request->input('categoryid'));
		$vendorid		=	intval($request->input('vendorid'));
		$departmentid	=	intval($request->input('departmentid'));
		$managerid		=	intval($request->input('managerid'));
		$format_id		=	intval($request->input('format_id'));
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$pagesearch		=	$request->input('pagesearch');
		$record_type	=	$request->input('record_type');
		
		try
		{
			if($format_id==1 && ($categoryid==2 || $categoryid==1))
			{
				$html	=	$this->resourceService->getEoiSummaryDetail($categoryid,$vendorid,$departmentid,$managerid,$format_id,$in_house,$pagesearch,$record_type,$projectid);
			}
			return response()->json(['status'=>200,'message'=>'EoI Summary Detail.','html'=> $html]);
		}
		catch(Exception $e)
		{
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
		
		
	}

    public function eoiOrderValues(Request $request)
	{
        Session::put('adminmenu','reporting');
		Session::put('adminsubmenu','eoiordervalue');

		Session::put('menid',162);
		
		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		
		
		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		
		$departments	=	DB::table('users_tbl as a')
							->select('a.userid','b.departmentid','b.departmentname','b.shortname')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->where('a.isdepartment',1)
							->orderBy('b.departmentname')
							->get();

		$managers		=	DB::table('users_tbl as a')
							->select('a.userid','b.departmentid','b.departmentname','b.shortname')
							->leftJoin('department_tbl as b','b.userid','=','a.userid')
							->whereExists(function ($query) {
								$query->select(DB::raw(1))
									  ->from('eoi_request as e')
									  ->whereColumn('e.userid', 'a.userid')
									  ->where('e.categoryid', 2);
							})
							->where('a.ispm',1)
							->orderBy('b.departmentname')
							->get();

		$firms		=	DB::table('vendor_tbl')
							->select('vendorid','companyname','shortname')
							->where('categoryid',2)
							->orderBy('companyname')
							->orderBy('categoryid')
							->get();

		$projects	=	DB::table('project_tbl')
							->select('projectid','project_name')
							->orderBy('project_name')
							->get();

		
        return view('admin/reporting/eoiorder_value',compact('category','departments','managers','firms','projects'));
    }


    public function getEoiOrderValueData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('category_id') ?? NULL;
		$projectid 		=	$request->input('project_id') ?? NULL;
		$departmentid 	=	$request->input('department_id') ?? NULL;
		$managerid 		=	$request->input('manager_id') ?? NULL;
		$vendorid 		=	$request->input('vendor_id') ?? NULL;
		$pagesearch 	=	$request->input('pagesearch') ?? NULL;
		
		
		$data 			= 	DB::table('eoi_request as a')
							->select('a.*','b.project_name')
							->selectSub(function ($query) {
								$query->from('eoi_request_detail')
									  ->selectRaw('COUNT(*)')
									  ->whereColumn('eoi_request_detail.requestid', 'a.requestid');
							}, 'resourceCount')							
							->leftJoin('project_tbl as b','b.projectid','=','a.projectid')
							->where('a.categoryid',$categoryid)
							->where('a.eoi_resource_value','!=',0)
							->when($departmentid!=NULL,function($query) use($departmentid){
								return $query->where('a.userid',$departmentid);
							})
							->when($projectid!=NULL,function($query) use ($projectid){
								return $query->where('a.projectid','=',$projectid);
							})
							->when($projectid!=NULL,function($query) use ($projectid){
								return $query->where('a.projectid','=',$projectid);
							})
							->when($pagesearch!='',function($query) use ($pagesearch){
								return $query->where('a.eoinumber','like','%'.$pagesearch.'%');
							})							
							->orderBy('a.creationdate','DESC')
							->get();
		
		foreach ($data as $key => $record) {

			$record->orders	=	DB::table('eoi_work_order as a')
								->select(
									'a.orderid',
									'a.orderdate',
									'a.workorderduedate',
									'a.ordernumber',
									'a.order_resource_value',
									'a.order_tax_value',
									'a.order_value',
									'b.shortname'
								)
								->selectSub(function ($query) {
									$query->from('eoi_resource_deployment as rd')
										->selectRaw('COUNT(*)')
										->whereColumn('rd.orderid', 'a.orderid')
										->whereIn('rd.deployment_status',['Pending','Active','Extended']);
								}, 'resourceCount')
								->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
								->where('a.requestid', $record->requestid)
								->whereDate('a.orderdate','>=','2025-07-01')
								->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('eoi_resource_deployment as c')
										->whereColumn('c.orderid', 'a.orderid')
										->whereNull('c.startDate');
								})
								->when($vendorid != null, function ($query) use ($vendorid) {
									return $query->where('a.vendorid', $vendorid);
								})
								->orderBy('a.orderdate')
								->get();

			if ($record->orders->isEmpty()) {
				unset($data[$key]);
			}
		}

		$data = $data->values();

		$html = view('admin/reporting/ajaxpages/eoiordervalueTable',['data'=>$data])->render();
		
		return response()->json(['status'=>200,'message'=>'EoI Summary Detail.','html'=> $html]);
		
		//return view('admin/reporting/ajaxpages/eoiordervalueTable',['data' => $data]);
    }

    public function testFirmWiseEoiOrderValueData(Request $request)
	{
		$userId	=	$request->session()->get('userId');
	
		$categoryid 	=	$request->input('category_id') ?? NULL;
		$projectid 		=	$request->input('project_id') ?? NULL;
		$departmentid 	=	$request->input('department_id') ?? NULL;
		$managerid 		=	$request->input('manager_id') ?? NULL;
		$vendorid 		=	$request->input('vendor_id') ?? NULL;
		$record_type	=	$request->input('record_type') ?? NULL;

		$vendors	=	DB::table('vendor_tbl')
						->where('categoryid', 2)
						->when($vendorid != null, function ($query) use ($vendorid) {
							return $query->where('vendorid', $vendorid);
						})
						->orderBy('shortname')
						->get();

		foreach ($vendors as $key => $vendor)
		{
			$vendor->orders	=	DB::table('eoi_work_order as a')
								->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.order_resource_value','a.order_tax_value','a.order_value','a.isExtended','b.eoinumber','b.releasedate','b.projectduration','b.eoi_total_value','c.project_name')
								->selectSub(function ($query) {
									$query->from('eoi_request_detail as rd')
										  ->selectRaw('COUNT(*)')
										  ->whereColumn('rd.requestid', 'b.requestid');
								}, 'detail_count')
								->selectSub(function ($query) {
									$query->from('eoi_resource_deployment as erd')
										  ->selectRaw('COUNT(*)')
										  ->whereIn('erd.deployment_status',['Pending','Active','Extended','Released'])
										  ->whereColumn('erd.orderid', 'a.orderid');
								}, 'deployedCount')
								->leftJoin('eoi_request as b', 'b.requestid', '=', 'a.requestid')
								->leftJoin('project_tbl as c', 'c.projectid', '=', 'a.projectid')
								->where('a.order_resource_value','!=',0)
								->where('a.vendorid', $vendor->vendorid)
								->where('a.categoryid', $vendor->categoryid)
								->where(function ($q) use ($record_type) {
									$q->whereNull('b.requestid')
									  ->orWhere(function ($q1) use ($record_type) {
											$q1->whereExists(function ($query) {
											  $query->select(DB::raw(1))
													->from('eoi_request_detail as rd')
													->whereColumn('rd.requestid', 'b.requestid');
											})
											->when($record_type == 2, function ($query) {
												return $query->where('b.releasedate','<','2025-07-01')
															 ->where('b.iscancelled',0)
															 ->where('b.isClosed', 0);
											})
											->when($record_type == 3 || $record_type == 4 || $record_type == 6, function ($query) {
												return $query->where('b.releasedate','>=','2025-07-01')
															 ->where('b.iscancelled',0)
															 ->where('b.isClosed', 0);
											});
									});
								})
								->when($record_type == 1 || $record_type == 2 || $record_type == 4, function ($query) {
									return $query->where('a.workorderduedate', '>=', today())
												 ->where('a.isActiveOrder', 1)
												 ->where('a.isExtended', 0)
												 ->where('a.isCancelled', 0)
												 ->where('a.isClosed', 0);
								})
								->when($record_type == 5, function ($query) {
									return $query->where('a.isExtended',1)
												 ->where('a.isCancelled',0)
												 ->whereDate('a.orderdate','>','2025-07-01')
												 ->where('a.isClosed',0);
								})
								->when($record_type == 6, function ($query) {
									return $query->where('a.isExtended',0)
												 ->where('a.isCancelled',0)
												 ->where('a.isActiveOrder',1)
												 ->whereDate('a.orderdate','>','2025-07-01')
												 ->whereDate('a.workorderduedate','<',today())
												 ->where('a.isClosed',0);
								})
								->when($record_type == 7, function ($query) {
									return $query->where('a.order_resource_value','!=',0);
								})
								->when($projectid != null, function ($query) use ($projectid) {
									return $query->where('a.projectid', $projectid);
								})
								->when($departmentid != null, function ($query) use ($departmentid) {
									return $query->where('a.department_id', $departmentid);
								})
								->get();

			if($vendor->orders->isEmpty())
			{
				unset($vendors[$key]);
			}
		}

		$vendors = $vendors->values();		
	
		$html = view('admin/reporting/ajaxpages/testfirmwiseordervalueTable',['data'=>$vendors])->render();
		
		return response()->json(['status'=>200,'message'=>'Firm Wise Order Value.','html'=> $html]);
    }

    public function testFirmWiseEoiOrderValueExport(Request $request)
	{
		$userId	=	$request->session()->get('userId');
	
		$categoryid 	=	$request->input('category_id') ?? NULL;
		$projectid 		=	$request->input('project_id') ?? NULL;
		$departmentid 	=	$request->input('department_id') ?? NULL;
		$managerid 		=	$request->input('manager_id') ?? NULL;
		$vendorid 		=	$request->input('vendor_id') ?? NULL;
		$record_type	=	$request->input('record_type') ?? NULL;

		$fileName = 'vendor_work_order_data_' . date('Ymd_His') . '.csv';

		$headers = [
			"Content-Type" => "text/csv",
			"Content-Disposition" => "attachment; filename=$fileName",
		];
		
		try
		{
			$callback = function () use ($vendorid, $record_type, $projectid, $departmentid) {

				$handle = fopen('php://output', 'w');

				fputcsv($handle, [
					'S.No',
					'Project Name',
					'Vendor Name',
					'EOI Number',
					'Release Date',
					'Resource Count',
					'Project Duration',
					'EOI Total Value',
					'Order Number',
					'Order Date',
					'Work Order Due Date',
					'Resource Value',
					'Tax Value',
					'Order Value',
					'Deployed Count',
					'Start Date Available',
					'Department Name'
				]);

				$sno = 1;

				DB::table('vendor_tbl as v')
					->select(
						'c.project_name',
						'v.shortname',
						'b.eoinumber',
						'b.releasedate',
						'b.projectduration',
						'b.eoi_total_value',
						'a.ordernumber',
						'a.orderdate',
						'a.workorderduedate',
						'a.order_resource_value',
						'a.order_tax_value',
						'a.order_value',
						'd.departmentname'
					)
					->selectSub(function ($query) {
						$query->from('eoi_request_detail as rd')
							->selectRaw('COUNT(*)')
							->whereColumn('rd.requestid', 'b.requestid');
					}, 'detail_count')
					->selectSub(function ($query) {
						$query->from('eoi_resource_deployment as erd')
							->selectRaw('COUNT(*)')
							->whereIn('erd.deployment_status', ['Pending','Active','Extended','Released'])
							->whereColumn('erd.orderid', 'a.orderid');
					}, 'deployedCount')
					->selectSub(function ($query) {
						$query->from('eoi_resource_deployment as erd')
							->selectRaw('CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END')
							->whereColumn('erd.orderid', 'a.orderid')
							->whereNotNull('erd.startDate')
							->whereNotNull('erd.endDate');
					}, 'isDateAvailable')					
					->leftJoin('eoi_work_order as a', function ($join) {
						$join->on('a.vendorid', '=', 'v.vendorid')
							->on('a.categoryid', '=', 'v.categoryid');
					})
					->leftJoin('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->leftJoin('project_tbl as c', 'c.projectid', '=', 'a.projectid')
					->leftJoin('department_tbl as d', 'd.departmentid', '=', 'a.department_id')
					->where('v.categoryid', 2)
					->where('a.order_resource_value', '!=', 0)
					->when($vendorid != null, function ($query) use ($vendorid) {
						return $query->where('v.vendorid', $vendorid);
					})
					->when($projectid != null, function ($query) use ($projectid) {
						return $query->where('a.projectid', $projectid);
					})
					->when($departmentid != null, function ($query) use ($departmentid) {
						return $query->where('a.department_id', $departmentid);
					})

					->where(function ($q) use ($record_type) {

						$q->whereNull('b.requestid')

							->orWhere(function ($q1) use ($record_type) {

								$q1->whereExists(function ($query) {
									$query->select(DB::raw(1))
										->from('eoi_request_detail as rd')
										->whereColumn('rd.requestid', 'b.requestid');
								})

								->when($record_type == 2, function ($query) {
									return $query->where('b.releasedate', '<', '2025-07-01')
										->where('b.iscancelled', 0)
										->where('b.isClosed', 0);
								})

								->when(in_array($record_type, [3,4,6]), function ($query) {
									return $query->where('b.releasedate', '>=', '2025-07-01')
										->where('b.iscancelled', 0)
										->where('b.isClosed', 0);
								});

							});

					})

					->when(in_array($record_type, [1,2,4]), function ($query) {
						return $query->where('a.workorderduedate', '>=', today())
							->where('a.isActiveOrder', 1)
							->where('a.isExtended', 0)
							->where('a.isCancelled', 0)
							->where('a.isClosed', 0);
					})

					->when($record_type == 5, function ($query) {
						return $query->where('a.isExtended', 1)
							->where('a.isCancelled', 0)
							->whereDate('a.orderdate', '>', '2025-07-01')
							->where('a.isClosed', 0);
					})

					->when($record_type == 6, function ($query) {
						return $query->where('a.isExtended', 0)
							->where('a.isCancelled', 0)
							->where('a.isActiveOrder', 1)
							->whereDate('a.orderdate', '>', '2025-07-01')
							->whereDate('a.workorderduedate', '<', today())
							->where('a.isClosed', 0);
					})

					->when($record_type == 7, function ($query) {
						return $query->where('a.order_resource_value', '!=', 0);
					})

					->orderBy('v.shortname')
					->chunk(500, function ($rows) use ($handle, &$sno) {

						foreach ($rows as $row) {

							$row->orderdate = !empty($row->orderdate)
								? "\t" . Carbon::parse($row->orderdate)->format('d-m-Y')
								: '';

							$row->workorderduedate = !empty($row->workorderduedate)
								? "\t" . Carbon::parse($row->workorderduedate)->format('d-m-Y')
								: '';

							$row->releasedate = !empty($row->releasedate)
								? "\t" . Carbon::parse($row->releasedate)->format('d-m-Y')
								: '';

							if (!empty($row->projectduration)) {
								$row->projectduration .= ' Months';
							}

							fputcsv($handle, [
								$sno++,
								$row->project_name,
								$row->shortname,
								$row->eoinumber,
								$row->releasedate,
								$row->detail_count,
								$row->projectduration,
								$row->eoi_total_value,
								$row->ordernumber,
								$row->orderdate,
								$row->workorderduedate,
								$row->order_resource_value,
								$row->order_tax_value,
								$row->order_value,
								$row->deployedCount,
								$row->isDateAvailable,
								$row->departmentname,
							]);
						}

					});
				fclose($handle);
			};
			
			return response()->stream($callback, 200, $headers);
		}
		catch(\Exception $e)
		{
			Log::error('Export Error: '.$e->getMessage());

			return response()->json([
				'status' => 500,
				'message' => 'Export failed'
			]);
		}
    }


    public function getFirmWiseEoiOrderValueData(Request $request)
	{
		$userId	=	$request->session()->get('userId');

		$categoryid 	=	$request->input('category_id') ?? NULL;
		$projectid 		=	$request->input('project_id') ?? NULL;
		$departmentid 	=	$request->input('department_id') ?? NULL;
		$managerid 		=	$request->input('manager_id') ?? NULL;
		$vendorid 		=	$request->input('vendor_id') ?? NULL;
		$record_type	=	$request->input('record_type') ?? NULL;
		
		$vendors	=	DB::table('eoi_work_order as a')
						->select('a.vendorid','b.shortname','b.categoryid')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->whereDate('a.orderdate','>=','2025-07-01')
						->where('a.order_resource_value','!=',0)
						->distinct()
						->get();
		
		foreach($vendors as $vendor)
		{
			$vendor->eois	=	DB::table('eoi_work_order as a')
								->select('b.requestid','b.eoinumber','b.releasedate','b.eoi_resource_value','b.eoi_tax','b.eoi_total_value')
								->selectSub(function ($query) {
									$query->from('eoi_request_detail as d')
										->selectRaw('COUNT(*)')
										->whereColumn('d.requestid', 'b.requestid');
								}, 'eoiresourceCount')
								->join('eoi_request as b','b.requestid','=','a.requestid')
								->where('a.vendorid',$vendor->vendorid)
								->where('a.order_resource_value','!=',0)
								->whereDate('a.orderdate','>=','2025-07-01')
								->distinct()
								->get();
								
			foreach($vendor->eois as $eoi)
			{
				$eoi->orders	=	DB::table('eoi_work_order as a')
									->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.order_resource_value','a.order_tax_value','a.order_value','c.shortname','a.isExtended')
									->selectSub(function ($query) {
										$query->from('eoi_resource_deployment as r')
											->selectRaw('COUNT(*)')
											->whereIn('r.deployment_status',['Active','Pending','Extended'])
											->whereColumn('r.orderid', 'a.orderid');
									}, 'orderresourceCount')
									->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
									->where('a.order_resource_value','!=',0)
									->where('a.vendorid','=',$vendor->vendorid)
									->where('a.categoryid','=',$vendor->categoryid)
									->where('a.requestid','=',$eoi->requestid)
									->orderBy('a.orderdate')
									->get();
			}
		}
		$html = view('admin/reporting/ajaxpages/firmwiseordervalueTable',['data'=>$vendors])->render();
		
		return response()->json(['status'=>200,'message'=>'Firm Wise Order Value.','html'=> $html]);
		
		//return view('admin/reporting/ajaxpages/eoiordervalueTable',['data' => $data]);
    }


    public function getFirmWiseEoiOrderResourceData(Request $request)
	{
		$userId	=	$request->session()->get('userId');
		
		$vendors	=	DB::table('eoi_work_order as a')
						->select('a.vendorid','b.shortname','b.categoryid')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->whereDate('a.orderdate','>=','2025-07-01')
						->where('a.order_resource_value','!=',0)
						->distinct()
						->get();
		
		foreach($vendors as $vendor)
		{
			$vendor->eois	=	DB::table('eoi_work_order as a')
								->select('b.requestid','b.eoinumber','b.releasedate','b.eoi_resource_value','b.eoi_tax','b.eoi_total_value')
								->selectSub(function ($query) {
									$query->from('eoi_request_detail as d')
										->selectRaw('COUNT(*)')
										->whereColumn('d.requestid', 'b.requestid');
								}, 'eoiresourceCount')
								->join('eoi_request as b','b.requestid','=','a.requestid')
								->where('a.vendorid',$vendor->vendorid)
								->where('a.order_resource_value','!=',0)
								->whereDate('a.orderdate','>=','2025-07-01')
								->distinct()
								->get();
								
			foreach($vendor->eois as $eoi)
			{
				$eoi->orders	=	DB::table('eoi_work_order as a')
									->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.order_resource_value','a.order_tax_value','a.order_value','c.shortname')
									->selectSub(function ($query) {
										$query->from('eoi_resource_deployment as r')
											->selectRaw('COUNT(*)')
											->whereIn('r.deployment_status',['Active','Pending','Extended'])
											->whereColumn('r.orderid', 'a.orderid');
									}, 'orderresourceCount')
									->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
									->where('a.order_resource_value','!=',0)
									->where('a.vendorid','=',$vendor->vendorid)
									->where('a.categoryid','=',$vendor->categoryid)
									->where('a.requestid','=',$eoi->requestid)
									->orderBy('a.orderdate')
									->get();
				
				foreach($eoi->orders as $resource)
				{
					$resource->resources	=	DB::table('eoi_resource_deployment as a')
												->select('b.sectorname','c.consultantposition')
												->join('sector_tbl as b','b.sectorid','=','a.sectorid')
												->join('position_tbl as c','c.positionid','=','a.positionid')
												->where('a.orderid',$resource->orderid)
												->whereIn('a.deployment_status',['Active','Pending','Extended'])
												->get();
				}
			}
		}
		$html = view('admin/reporting/ajaxpages/firmwiseorderresourceTable',['data'=>$vendors])->render();
		
		return response()->json(['status'=>200,'message'=>'Firm Wise Order Value.','html'=> $html]);
		
		//return view('admin/reporting/ajaxpages/eoiordervalueTable',['data' => $data]);
    }
	
}
