<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;


class DashboardController extends Controller
{
    public function getAdminDashboard(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		
		$firm_type 	=	$request->input('firm_type') ?? 0;
		
		
		$eoi		=	DB::table('eoi_request')
						->when($firm_type!=0,function($query) use ($firm_type){
							return $query->where('categoryid','=',$firm_type);
						})
						->where('iscancelled',0)
						->where('isClosed',0)
						->count();

		$approved	=	DB::table('eoi_request')
						->where('eoistatus','>=',2)
						->when($firm_type!=0,function($query) use ($firm_type){
							return $query->where('categoryid','=',$firm_type);
						})
						->where('iscancelled',0)
						->where('isClosed',0)
						->count();

		$floated	=	DB::table('eoi_request')
						->where('eoistatus','>=',3)
						->when($firm_type!=0,function($query) use ($firm_type){
							return $query->where('categoryid','=',$firm_type);
						})
						->where('iscancelled',0)
						->where('isClosed',0)
						->count();

		$wos		=	DB::table('eoi_work_order')
						->where('isExtended',0)
						->where('isActiveOrder',1)
						->where('isCancelled',0)
						->where('isClosed',0)
						->where('workorderduedate','>',now())
						->when($firm_type!=0,function($query) use ($firm_type){
							return $query->where('categoryid','=',$firm_type);
						})
						->count();

		$today	=	Carbon::today()->toDateString();


		$presentation	=	DB::table('eoi_request')
							->where('isordered',0)
							->when($firm_type!=0,function($query) use ($firm_type){
								return $query->where('categoryid','=',$firm_type);
							})
							->where('iscancelled',0)
							->whereNotNull('interviewdate')
							->where('interviewdate','!=','0000-00-00')
							->whereDate('interviewdate', '>=',date('Y\-m\-d'))
							->count();

						
						
		$progress	=	DB::table('eoi_request')
						->where('isordered',0)
						->when($firm_type!=0,function($query) use ($firm_type){
							return $query->where('categoryid','=',$firm_type);
						})
						->where('iscancelled',0)
						->where('isClosed',0)
						->count();

		$pending	=	0;

		$departments=	DB::table('users_tbl')->where('isdepartment',1)->count();

/*
		$resources	=	DB::table('eoi_resource_deployment as a')
						->whereNotNull('a.deployed_date')
						->join('eoi_work_order as b', function ($join) use($firm_type) {
							$join->on('b.orderid', '=', 'a.orderid');
								if($firm_type!=0)
								{
								 $join->where('b.categoryid','=',$firm_type);
								}
						})
						->count();
*/
		$resources	=	DB::table('eoi_resource_deployment as a')
						->where('a.deployment_status','Active')
						->join('eoi_work_order as b', function ($join) use($firm_type) {
							$join->on('b.orderid', '=', 'a.orderid');
								if($firm_type!=0)
								{
								 $join->where('b.categoryid','=',$firm_type);
								}
						})
						->count();

		
		
		$data	=	DB::table('vendor_resource_counting as a')
					->select('a.vendorid','b.companyname','b.shortname','a.total_count','c.tiername')
					->leftjoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->leftjoin('tiermaster_tbl as c','c.tierid','=','b.tierid')
					->when($firm_type!=0,function($query) use ($firm_type){
						return $query->where('b.categoryid','=',$firm_type);
					})					
					->orderBy('b.tierid')
					->orderBy('a.total_count','DESC')
					->get();
		
		foreach($data as $dt)
		{
			$dt->total_project	=	DB::table('eoi_work_order as a')
									->join('eoi_request as b','b.requestid','=','a.requestid')
									->where('a.vendorid',$dt->vendorid)
									->where('a.isActiveOrder',1)
									->whereDate('a.workorderduedate','>=',today())
									->distinct()
									->count('b.projecttitle') ?? 0;
			
			$dt->order_value	=	DB::table('eoi_work_order')->where('vendorid',$dt->vendorid)->where('isExtended',0)->sum('workorderamount') ?? 0;
			
		}
		

		$awd_staff		=	DB::table('resource_counting')->where('categoryid',1)->value('total_count');
		$csf_staff		=	DB::table('resource_counting')->where('categoryid',2)->value('total_count');

		$total_resource	=	$awd_staff+$csf_staff;

		$awd_percentage =	$total_resource > 0 ? ($awd_staff / $total_resource) * 100 : 0;
		$csf_percentage =	$total_resource > 0 ? ($csf_staff / $total_resource) * 100 : 0;

		$awd_percentage	=	number_format($awd_percentage,2,'.','');
		$csf_percentage	=	number_format($csf_percentage,2,'.','');

		if($firm_type==0)
		{
			
			$vendors	=	DB::table('vendor_resource_counting as a')
							->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
							->select(
								'b.shortname',
								'b.categoryid',
								'a.total_count'
							)
							->whereIn('b.categoryid', [1, 2])
							->orderBy('b.categoryid', 'ASC')
							->orderBy('a.total_count','DESC')
							->get();
			
		}
		else
		{
			$vendors	=	DB::table('vendor_resource_counting as a')
							->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
							->select(
								'b.shortname',
								'b.categoryid',
								'a.total_count'
							)
							->where('b.categoryid',$firm_type)
							->orderBy('b.categoryid', 'ASC')
							->orderBy('a.total_count','DESC')
							->get();
			
		}
		$vendor_resource_name  = $vendors->pluck('shortname');
		$vendor_resource_count = $vendors->pluck('total_count');
		$vendor_category       = $vendors->pluck('categoryid');
		
	
		return view('admin/dashboard/adminContent',[
			'data'					=>	$data,
			'eoi'					=>	$eoi,
			'approved'				=>	$approved,
			'floated'				=>	$floated,
			'wos'					=>	$wos,
			'presentation'			=>	$presentation,
			'progress'				=>	$progress,
			'pending'				=>	$pending,
			'departments'			=>	$departments,
			'resources'				=>	$resources,
			'awd_staff'				=>	$awd_staff,
			'csf_staff'				=>	$csf_staff,
			'awd_percentage'		=>	$awd_percentage,
			'csf_percentage'		=>	$csf_percentage,
			'vendor_resource_name'	=>	$vendor_resource_name,
			'vendor_resource_count'	=>	$vendor_resource_count,
			'vendor_category'		=>	$vendor_category,
			'firm_type'				=>	$firm_type,
		]);

    }


    public function getAdminNoticeBoard(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		
		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		//$departments	=	DB::table('department_tbl')->orderby('departmentname')->get();
		
		$departments	=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->orderby('b.name')
							->get();
		
		
		return view('admin/noticeboards/admin_noticeboard',compact('category','departments'));
    }
    
	public function getAdminNoticeBoardData(Request $request)
	{
		$eois	=	DB::table('eoi_request as a')
					->select('a.requestid','a.creationdate','a.projecttitle','b.name',
						DB::raw("
							CASE
								WHEN DATE(a.creationdate) = CURDATE() THEN 1
								WHEN DATE(a.creationdate) = CURDATE() - INTERVAL 1 DAY THEN 2
								WHEN DATEDIFF(CURDATE(), a.creationdate) <= 7 THEN 3
								ELSE 4
							END as duration
						")		
					)
					->join('users_tbl as b','b.userid','=','a.userid')
					->orderBy('a.creationdate','desc')
					->limit(20)
					->get();

		$approved	=	DB::table('eoi_request as a')
						->select('a.requestid','a.acceptancedatetime','a.projecttitle','b.name',
							DB::raw("
								CASE
									WHEN DATE(a.acceptancedatetime) = CURDATE() THEN 1
									WHEN DATE(a.acceptancedatetime) = CURDATE() - INTERVAL 1 DAY THEN 2
									WHEN DATEDIFF(CURDATE(), a.acceptancedatetime) <= 7 THEN 3
									ELSE 4
								END as duration
							")		
						)
						->join('users_tbl as b','b.userid','=','a.userid')
						->whereNotNull('a.acceptancedatetime')
						->orderBy('a.acceptancedatetime','desc')
						->limit(20)
						->get();

		$prebids	=	DB::table('eoi_request_prebid as a')
						->select('a.requestid','a.raisedon','b.projecttitle','d.companyname',
							DB::raw("
								CASE
									WHEN DATE(a.raisedon) = CURDATE() THEN 1
									WHEN DATE(a.raisedon) = CURDATE() - INTERVAL 1 DAY THEN 2
									WHEN DATEDIFF(CURDATE(), a.raisedon) <= 7 THEN 3
									ELSE 4
								END as duration
							")		
						)
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','b.userid')
						->join('vendor_tbl as d','d.vendorid','=','a.vendorid')
						->orderBy('a.raisedon','desc')
						->limit(20)
						->get();

		$responses	=	DB::table('eoi_request_prebid_forwarded as a')
						->select('a.requestid','a.creationdate','b.projecttitle','c.name as fromname',
							DB::raw("
								CASE
									WHEN DATE(a.creationdate) = CURDATE() THEN 1
									WHEN DATE(a.creationdate) = CURDATE() - INTERVAL 1 DAY THEN 2
									WHEN DATEDIFF(CURDATE(), a.creationdate) <= 7 THEN 3
									ELSE 4
								END as duration
							")		
						)
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('users_tbl as c','c.userid','=','a.fromuserid')
						->whereIn('a.postedby',['DEPARTMENT','PROJECT MANAGER'])
						->orderBy('a.creationdate','desc')
						->limit(20)
						->get();

		$participations	=	DB::table('eoi_request_floated as a')
							->select('a.requestid','a.userid','a.vendorid','a.uploaded_at','b.projecttitle','c.name','d.companyname',
								DB::raw("
									CASE
										WHEN DATE(a.uploaded_at) = CURDATE() THEN 1
										WHEN DATE(a.uploaded_at) = CURDATE() - INTERVAL 1 DAY THEN 2
										WHEN DATEDIFF(CURDATE(),a.uploaded_at) <= 7 THEN 3
										ELSE 4
									END as duration
								")		
							)
							->join('eoi_request as b','b.requestid','=','a.requestid')
							->join('users_tbl as c','c.userid','=','a.userid')
							->join('vendor_tbl as d','d.vendorid','=','a.vendorid')
							->whereNotNull('a.uploaded_at')
							->orderBy('a.uploaded_at','desc')
							->limit(20)
							->get();


		$dates	=	DB::select("
							SELECT requestid, projecttitle, notice_type, notice_date,
							DATEDIFF(notice_date, CURDATE()) as remainingdays
							FROM
							(
								SELECT requestid, projecttitle, releasedate as notice_date, 'Release Date' as notice_type
								FROM eoi_request
								WHERE releasedate IS NOT NULL

								UNION ALL

								SELECT requestid, projecttitle, prebidlastdate as notice_date, 'Pre Bid Last Date' as notice_type
								FROM eoi_request
								WHERE prebidlastdate IS NOT NULL

								UNION ALL

								SELECT requestid, projecttitle, deadlinedate as notice_date, 'Submission Date' as notice_type
								FROM eoi_request
								WHERE deadlinedate IS NOT NULL

								UNION ALL

								SELECT requestid, projecttitle, DATE(interviewdate) as notice_date, 'Interview Date' as notice_type
								FROM eoi_request
								WHERE interviewdate IS NOT NULL
							) as notices
							WHERE notice_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 5 DAY)
							ORDER BY notice_date ASC
						");
	
		return view('admin/noticeboards/ajaxpages/adminnoticeTable',['eois' => $eois,'approved'=>$approved,'prebids'=>$prebids,'responses'=>$responses,'participations'=>$participations,'dates'=>$dates]);

    }




    public function getPmDashboard(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		

		$today	=	Carbon::today()->toDateString();


$awd_staff = DB::table('eoi_work_order as a')
    ->join('eoi_resource_deployment as b', 'b.orderid', '=', 'a.orderid')
    ->leftJoin('eoi_request as c', 'c.requestid', '=', 'a.requestid')
    ->where('a.department_id', session('departmentId'))
    ->whereNotNull('b.deployment_date')
    ->whereNotNull('b.name')
    ->where('a.isActiveOrder', 1)
    ->whereDate('a.workorderduedate', '>=', today())
    ->where('b.deployment_status', 'Active')
    ->where('a.categoryid', 1)
    ->where(function ($q) {
        $q->where('a.requestid', 0)
          ->orWhere(function ($q) {
              $q->where('a.requestid', '!=', 0)
                ->where('c.isClosed', 0)
                ->where('c.iscancelled', 0);
          });
    })
    ->count('b.deploymentid');

		
		$csf_staff		=	DB::table('eoi_work_order as a')
							->join('eoi_resource_deployment as b', 'b.orderid', '=', 'a.orderid')
							->where('a.department_id',session('departmentId'))
							->whereNotNull('b.deployment_date')
							->whereNotNull('b.name')
							->where('a.isActiveOrder',1)
							->whereDate('a.workorderduedate','>=',today())
							->where('b.deployment_status','Active')
							->where('a.categoryid',2)
							->count('b.deploymentid');

		$total_resource	=	$awd_staff+$csf_staff;

		$awd_percentage =	$total_resource > 0 ? ($awd_staff / $total_resource) * 100 : 0;
		$csf_percentage =	$total_resource > 0 ? ($csf_staff / $total_resource) * 100 : 0;

		$awd_percentage	=	number_format($awd_percentage,2,'.','');
		$csf_percentage	=	number_format($csf_percentage,2,'.','');

		
		$orders		=	DB::table('eoi_work_order as a')
						->select('a.ordernumber','b.companyname','b.shortname',DB::raw("(select count(*) from eoi_resource_deployment where deployment_status='Active' and orderid=a.orderid) as total_resource"))
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.department_id',session('departmentId'))
						->where('a.isActiveOrder',1)
						->whereDate('a.workorderduedate','>=',today())
						->get();
		
		$labels 		=	$orders->pluck('shortname');
		$totals 		=	$orders->pluck('total_resource');
		$orderNumbers 	=	$orders->pluck('ordernumber');


	
		$data	=	DB::table('eoi_work_order as a')
					->select(
						'a.vendorid',
						'b.companyname',
						'b.shortname',
						'c.tiername'
					)
					->selectRaw('COUNT(r.deploymentid) as total_count')
					->leftJoin('eoi_resource_deployment as r', function ($join) {
						$join->on('r.orderid', '=', 'a.orderid')
							 ->whereNotNull('r.name')
							 ->whereNotNull('r.deployment_date')
							 ->where('r.deployment_status','Active');
					})
					->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
					->join('tiermaster_tbl as c', 'c.tierid', '=', 'b.tierid')
					->where('a.department_id', session('departmentId'))
					->where('a.isActiveOrder',1)
					->where('a.workorderduedate','>=',today())					
					->groupBy(
						'a.vendorid',
						'b.companyname',
						'b.shortname',
						'c.tiername'
					)
					->orderBy('b.tierid')
					->having('total_count', '>', 0)
					->get();
		
		foreach($data as $dt)
		{
			$dt->total_project	=	DB::table('eoi_work_order as a')
									->where('a.vendorid',$dt->vendorid)
									->where('a.department_id',session('departmentId'))
									->where('a.isActiveOrder',1)
									->where('a.workorderduedate','>=',today())
									->distinct()
									->count('a.orderid') ?? 0;
			
			$dt->order_value	=	DB::table('eoi_work_order')
									->where('vendorid',$dt->vendorid)
									->where('department_id',session('departmentId'))
									->sum('workorderamount') ?? 0;
			
		}
		return view('admin/dashboard/projectmanagerContent',[
			'data'					=>	$data,
			'awd_staff'				=>	$awd_staff,
			'csf_staff'				=>	$csf_staff,
			'awd_percentage'		=>	$awd_percentage,
			'csf_percentage'		=>	$csf_percentage,
			'labels'				=>	$labels,
			'totals'				=>	$totals,
			'orderNumbers'			=>	$orderNumbers,
		]);

    }

    public function getDeptDashboard(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		

		$today	=	Carbon::today()->toDateString();



		$orders		=	DB::table('eoi_work_order as a')
						->select('a.ordernumber','b.companyname','b.shortname',DB::raw("(select count(*) from eoi_resource_deployment where deployment_status='Active' and orderid=a.orderid) as total_resource"))
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.department_id',session('departmentId'))
						->where('a.isActiveOrder',1)
						->where('a.workorderduedate','>=',today())
						->having('total_resource', '>', 0)
						->get();
		
		$labels 		=	$orders->pluck('shortname');
		$totals 		=	$orders->pluck('total_resource');
		$orderNumbers 	=	$orders->pluck('ordernumber');


	
		$data	=	DB::table('eoi_work_order as a')
					->select(
						'a.vendorid',
						'b.companyname',
						'b.shortname',
						'c.tiername'
					)
					->selectRaw('COUNT(r.deploymentid) as total_count')
					->leftJoin('eoi_resource_deployment as r', function ($join) {
						$join->on('r.orderid', '=', 'a.orderid')
							 ->whereNotNull('r.name')
							 ->whereNotNull('r.deployed_date')
							 ->where('r.deployment_status','Active');
					})
					->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
					->join('tiermaster_tbl as c', 'c.tierid', '=', 'b.tierid')
					->where('a.department_id', session('departmentId'))
					->where('a.isActiveOrder',1)
					->where('a.workorderduedate','>=',today())
					->groupBy(
						'a.vendorid',
						'b.companyname',
						'b.shortname',
						'c.tiername'
					)
					->havingRaw('COUNT(r.deploymentid) > 0')
					->orderBy('b.tierid')
					->get();
		
		foreach($data as $dt)
		{
			$dt->total_project	=	DB::table('eoi_work_order as a')
									->join('eoi_request as b','b.requestid','=','a.requestid')
									->where('a.vendorid',$dt->vendorid)
									->where('a.department_id',session('departmentId'))
									->where('b.isClosed',0)
									->where('b.iscancelled',0)
									->distinct()
									->count('b.projecttitle') ?? 0;
			
			$dt->order_value	=	DB::table('eoi_work_order')
									->where('vendorid',$dt->vendorid)
									->where('department_id',session('departmentId'))
									->sum('workorderamount') ?? 0;
			
		}
	
		return view('admin/dashboard/departmentContent',[
			'data'					=>	$data,
			'labels'				=>	$labels,
			'totals'				=>	$totals,
			'orderNumbers'			=>	$orderNumbers,
		]);

    }
	
}
