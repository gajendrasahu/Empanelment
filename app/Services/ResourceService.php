<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

use Illuminate\Support\Facades\Hash;

class ResourceService
{
	public function formatedData($categoryid=0,$vendorid=0,$departmentid=0,$sectorid=0,$positionid=0,$experiencelevel=0,$pagesearch=NULL,$format_id=0,$managerid=0,$in_house=0,$projectid=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		/* AWD SECTION */
		if($format_id==3 && $categoryid==1)
		{
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->join('vendor_tbl as e', 'e.vendorid', '=', 'wo.vendorid')
						->where('rd.deployment_status', 'Active')
						->where('rd.isExtended', 0)
						->where('wo.isCancelled', 0)
						->where('wo.isActiveOrder', 1)
						->whereDate('wo.workorderduedate', '>=', today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('rd.experiencelevel', $experiencelevel);
						})
						->select(
							'e.vendorid',
							'e.companyname',
							'e.shortname',
							DB::raw('COUNT(DISTINCT wo.orderid) as total_orders'),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 1 THEN 1 ELSE 0 END) as l1"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 2 THEN 1 ELSE 0 END) as l2"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 3 THEN 1 ELSE 0 END) as l3"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 4 THEN 1 ELSE 0 END) as l4"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 5 THEN 1 ELSE 0 END) as l5"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 6 THEN 1 ELSE 0 END) as l6"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 7 THEN 1 ELSE 0 END) as l7"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 8 THEN 1 ELSE 0 END) as l8"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 9 THEN 1 ELSE 0 END) as l9"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 10 THEN 1 ELSE 0 END) as l10"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 11 THEN 1 ELSE 0 END) as l11"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 12 THEN 1 ELSE 0 END) as l12"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 13 THEN 1 ELSE 0 END) as l13"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 14 THEN 1 ELSE 0 END) as l14"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 15 THEN 1 ELSE 0 END) as l15"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 16 THEN 1 ELSE 0 END) as l16"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 17 THEN 1 ELSE 0 END) as l17"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 18 THEN 1 ELSE 0 END) as l18"),
							DB::raw('COUNT(rd.deploymentid) as total_resources')
						)
						->groupBy(
							'e.vendorid',
							'e.companyname'
						)

						->orderBy('e.companyname')
						->get();			
			return $data;
		}
		
		if($format_id==2 && $categoryid==1)
		{
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->where('rd.deployment_status', 'Active')
						->where('rd.isExtended', 0)
						->where('wo.isCancelled', 0)
						->where('wo.isActiveOrder', 1)
						->whereDate('wo.workorderduedate', '>=', today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('rd.experiencelevel', $experiencelevel);
						})
						->select(
							'd.departmentid',
							'd.departmentname',
							DB::raw('COUNT(DISTINCT wo.orderid) as total_orders'),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 1 THEN 1 ELSE 0 END) as l1"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 2 THEN 1 ELSE 0 END) as l2"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 3 THEN 1 ELSE 0 END) as l3"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 4 THEN 1 ELSE 0 END) as l4"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 5 THEN 1 ELSE 0 END) as l5"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 6 THEN 1 ELSE 0 END) as l6"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 7 THEN 1 ELSE 0 END) as l7"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 8 THEN 1 ELSE 0 END) as l8"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 9 THEN 1 ELSE 0 END) as l9"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 10 THEN 1 ELSE 0 END) as l10"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 11 THEN 1 ELSE 0 END) as l11"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 12 THEN 1 ELSE 0 END) as l12"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 13 THEN 1 ELSE 0 END) as l13"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 14 THEN 1 ELSE 0 END) as l14"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 15 THEN 1 ELSE 0 END) as l15"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 16 THEN 1 ELSE 0 END) as l16"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 17 THEN 1 ELSE 0 END) as l17"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 18 THEN 1 ELSE 0 END) as l18"),
							DB::raw('COUNT(rd.deploymentid) as total_resources')
						)
						->groupBy(
							'd.departmentid',
							'd.departmentname'
						)

						->orderBy('d.departmentname')
						->get();			
			return $data;
		}
		
		if($format_id==1 && $categoryid==1)
		{
		
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('vendor_tbl as v', 'v.vendorid', '=', 'wo.vendorid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->where('rd.deployment_status','Active')
						->where('rd.isExtended',0)
						->where('wo.isCancelled', 0)
						->where('wo.isActiveOrder', 1)
						->whereDate('wo.workorderduedate','>=',today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid',$projectid);
						})
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($vendorid > 0, function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('rd.experiencelevel', $experiencelevel);
						})

						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						
						->when($pagesearch != '', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})

						->select(
							'd.departmentid as departmentid',
							'd.departmentname as department_name',
							'd.shortname as department',
							'v.shortname as vendor',
							'v.vendorid as vendorid',
							'v.companyname as vendor_name',
							'wo.ordernumber as work_order_no',
							'wo.orderid as orderid',
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 1 THEN 1 ELSE 0 END) as l1"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 2 THEN 1 ELSE 0 END) as l2"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 3 THEN 1 ELSE 0 END) as l3"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 4 THEN 1 ELSE 0 END) as l4"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 5 THEN 1 ELSE 0 END) as l5"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 6 THEN 1 ELSE 0 END) as l6"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 7 THEN 1 ELSE 0 END) as l7"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 8 THEN 1 ELSE 0 END) as l8"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 9 THEN 1 ELSE 0 END) as l9"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 10 THEN 1 ELSE 0 END) as l10"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 11 THEN 1 ELSE 0 END) as l11"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 12 THEN 1 ELSE 0 END) as l12"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 13 THEN 1 ELSE 0 END) as l13"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 14 THEN 1 ELSE 0 END) as l14"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 15 THEN 1 ELSE 0 END) as l15"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 16 THEN 1 ELSE 0 END) as l16"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 17 THEN 1 ELSE 0 END) as l17"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 18 THEN 1 ELSE 0 END) as l18"),

							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.department_id',
							'wo.vendorid',
							'wo.orderid',
						)
						->orderBy('d.departmentname')
						->orderBy('v.companyname')
						->get();
			
			return $data;
		}
		
		/* AWD SECTION CLOSED */
		/*CONSULTANCY SECTION*/
		if($format_id==1 && $categoryid==2)
		{
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('vendor_tbl as v', 'v.vendorid', '=', 'wo.vendorid')
						->join('sector_tbl as s', 's.sectorid', '=', 'rd.sectorid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->when($in_house==1, function ($query) {
							$query->join('users_tbl as u', function ($join) {
								$join->on('u.userid','=','d.userid')
									 ->where('u.ispm','=',1)
									 ->where('wo.categoryid',2);
							});
						})
						->where('rd.deployment_status','Active')
						->where('rd.isExtended',0)
						->where('wo.isCancelled',0)
						->where('wo.isActiveOrder',1)
						->whereDate('wo.workorderduedate','>=',today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($managerid>0, function ($q) use ($managerid) {
							$q->where('d.departmentid', $managerid);
						})
						->when($vendorid > 0, function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($sectorid > 0, function ($q) use ($sectorid) {
							$q->where('rd.sectorid', $sectorid);
						})
						->when($positionid > 0, function ($q) use ($positionid) {
							$q->where('rd.positionid', $positionid);
						})
						->when($pagesearch != '', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})

						->select(
							'd.departmentid as departmentid',
							'd.departmentname as department_name',
							'd.shortname as department',
							'v.shortname as vendor',
							'v.vendorid as vendorid',
							'v.companyname as vendor_name',
							'wo.ordernumber as work_order_no',
							'wo.orderid as orderid',
							's.sectorname as sector',
							's.sectorid as sectorid',
							DB::raw("SUM(CASE WHEN rd.positionid = 1 THEN 1 ELSE 0 END) as managing_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 2 THEN 1 ELSE 0 END) as principal_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 3 THEN 1 ELSE 0 END) as senior_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 4 THEN 1 ELSE 0 END) as consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 5 THEN 1 ELSE 0 END) as associate_consultant"),

							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.department_id',
							'wo.vendorid',
							'wo.orderid',
							'rd.sectorid',
							'd.shortname',
							'v.shortname',
							'wo.ordernumber',
							's.sectorname'
						)
						->orderBy('d.shortname')
						->orderBy('v.shortname')
						->orderBy('wo.ordernumber')
						->orderBy('s.sectorname')
						->get();
			
			return $data;
		}
		if($format_id==2 && $categoryid==2)
		{
			$engSectorId	=	4;
			$finSectorId	=	2;
			$govSectorId  	= 	3;
			$healSectorId  	= 	5;
			$infoSectorId  	= 	1;
			$socSectorId  	= 	6;

			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo','wo.orderid', '=', 'rd.orderid')
						->join('department_tbl as d','d.departmentid', '=', 'wo.department_id')
						->when($in_house==1, function ($query) {
							$query->join('users_tbl as u', function ($join) {
								$join->on('u.userid','=','d.userid')
									 ->where('u.ispm','=',1)
									 ->where('wo.categoryid',2);
							});
						})
						->where('rd.deployment_status','Active')
						->where('wo.isCancelled',0)
						->where('wo.isActiveOrder',1)
						->where('wo.isExtended',0)
						->where('rd.isExtended',0)
						->whereDate('wo.workorderduedate','>=',today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid>0,function ($q) use ($categoryid){
							$q->where('wo.categoryid',$categoryid);
						})
						->when($managerid>0, function ($q) use ($managerid) {
							$q->where('d.departmentid', $managerid);
						})
						->when($vendorid>0,function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($departmentid>0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($sectorid>0, function ($q) use ($sectorid) {
							$q->where('rd.sectorid', $sectorid);
						})
						->when($positionid>0, function ($q) use ($positionid) {
							$q->where('rd.positionid', $positionid);
						})
						->when($pagesearch!='', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})
						->select(
							'd.departmentname as department','d.shortname as deptname','d.departmentid as departmentid',
							DB::raw('COUNT(DISTINCT wo.orderid) as total_orders'),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$engSectorId} THEN 1 ELSE 0 END) as engineering"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$finSectorId} THEN 1 ELSE 0 END) as finance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$govSectorId} THEN 1 ELSE 0 END) as governance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$healSectorId} THEN 1 ELSE 0 END) as health"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$infoSectorId} THEN 1 ELSE 0 END) as information"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$socSectorId} THEN 1 ELSE 0 END) as social"),
							DB::raw("SUM(CASE WHEN rd.positionid = 5 THEN 1 ELSE 0 END) as associate_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 4 THEN 1 ELSE 0 END) as consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 3 THEN 1 ELSE 0 END) as senior_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 2 THEN 1 ELSE 0 END) as principal_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 1 THEN 1 ELSE 0 END) as managing_consultant"),
							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.department_id',
							'd.departmentname',
						)
						->orderBy('d.departmentname')
						->get();
						
			return $data;
		}
		if($format_id==3 && $categoryid==2)
		{
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('vendor_tbl as v', 'v.vendorid', '=', 'wo.vendorid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->when($in_house==1, function ($query) {
							$query->join('users_tbl as uu', function ($join) {
								$join->on('uu.userid','=','d.userid')
									 ->where('uu.ispm','=',1)
									 ->where('wo.categoryid',2);
							});
						})
						->leftJoin('sector_tbl as s', 's.sectorid', '=', 'rd.sectorid')
						->leftJoin('position_tbl as p', 'p.positionid', '=', 'rd.positionid')
						->whereDate('wo.workorderduedate','>=',today())
						->where('wo.isCancelled', 0)
						->where('wo.isActiveOrder', 1)
						->where('rd.isExtended',0)
						->whereNotNull('rd.name')
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($vendorid > 0, function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})

						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})

						->when($sectorid > 0, function ($q) use ($sectorid) {
							$q->where('rd.sectorid', $sectorid);
						})

						->when($positionid > 0, function ($q) use ($positionid) {
							$q->where('rd.positionid', $positionid);
						})
						->when($pagesearch != '', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('rd.name', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})

						->select(
							'd.departmentid as departmentid',
							'd.shortname as deptname',
							'd.departmentname as department',
							'v.vendorid as vendorid',
							'v.companyname as vendorname',
							'v.shortname as vendor',
							'wo.ordernumber as work_order_no',
							's.sectorid as sectorid',
							'p.positionid as positionid',
							DB::raw("
								CASE
									WHEN rd.name IS NOT NULL AND rd.name <> ''
									THEN rd.name
									ELSE CONCAT(IFNULL(u.name,''),'')
								END as resource_name
							"),

							's.sectorname as sector',
							'p.consultantposition as position',

							'rd.deployment_date',
							'rd.deployment_status as status'
						)
						->leftJoin('users_tbl as u','u.userid','=','rd.userid')
					
						->orderBy('d.departmentname')
						->orderBy('v.companyname')
						->orderBy('wo.ordernumber')
						->orderBy('rd.deployment_date')

						->get();
			return $data;
		}

		if($format_id==4 && $categoryid==2)
		{
			$engSectorId	=	4;
			$finSectorId	=	2;
			$govSectorId  	= 	3;
			$healSectorId  	= 	5;
			$infoSectorId  	= 	1;
			$socSectorId  	= 	6;

			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo','wo.orderid', '=', 'rd.orderid')
						->join('department_tbl as d','d.departmentid', '=', 'wo.department_id')
						->when($in_house==1, function ($query) {
							$query->join('users_tbl as uu', function ($join) {
								$join->on('uu.userid','=','d.userid')
									 ->where('uu.ispm','=',1)
									 ->where('wo.categoryid',2);
							});
						})
						->join('vendor_tbl as v','v.vendorid','=','wo.vendorid')
						->where('rd.deployment_status','Active')
						->where('wo.isCancelled',0)
						->where('wo.isActiveOrder',1)
						->where('wo.isExtended',0)
						->where('rd.isExtended',0)
						->whereDate('wo.workorderduedate','>=',today())
						->when($projectid > 0, function ($q) use ($projectid) {
							$q->where('wo.projectid', $projectid);
						})
						->when($categoryid>0,function ($q) use ($categoryid){
							$q->where('wo.categoryid',$categoryid);
						})
						->when($vendorid>0,function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($departmentid>0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						
						->when($sectorid>0, function ($q) use ($sectorid) {
							$q->where('rd.sectorid', $sectorid);
						})
						->when($positionid>0, function ($q) use ($positionid) {
							$q->where('rd.positionid', $positionid);
						})
						->when($pagesearch!='', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})
						->select(
							'wo.orderid','wo.ordernumber','wo.department_id as departmentid','v.companyname','d.departmentname',
							DB::raw("SUM(CASE WHEN rd.sectorid = {$engSectorId} THEN 1 ELSE 0 END) as engineering"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$finSectorId} THEN 1 ELSE 0 END) as finance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$govSectorId} THEN 1 ELSE 0 END) as governance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$healSectorId} THEN 1 ELSE 0 END) as health"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$infoSectorId} THEN 1 ELSE 0 END) as information"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$socSectorId} THEN 1 ELSE 0 END) as social"),
							DB::raw("SUM(CASE WHEN rd.positionid = 5 THEN 1 ELSE 0 END) as associate_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 4 THEN 1 ELSE 0 END) as consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 3 THEN 1 ELSE 0 END) as senior_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 2 THEN 1 ELSE 0 END) as principal_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 1 THEN 1 ELSE 0 END) as managing_consultant"),
							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.orderid'
						)
						->orderBy('wo.orderdate')
						->get();
						
			return $data;
		}
		/*CONSULTANCY SECTION COMPLETED*/
	}
	
	public function getResourceReportList($positionid=0,$format_id=0,$departmentid=0,$vendorid=0,$sectorid=0,$orderid=0,$categoryid=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		if($positionid!=0)
		{
			if($format_id==1 && $categoryid==2)
			{
				$data	=	DB::table('eoi_work_order as a')
							->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->join('department_tbl as c','c.departmentid','=','a.department_id')
							->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
							->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
							->where('a.orderid','=',$orderid)
							->where('a.categoryid','=',$categoryid)
							->where('a.vendorid','=',$vendorid)
							->where('a.department_id','=',$departmentid)
							->first();
				
				$data->resources	=	DB::table('eoi_resource_deployment as a')
										->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration','b.sectorname','c.consultantposition')
										->join('sector_tbl as b','b.sectorid','=','a.sectorid')
										->join('position_tbl as c','c.positionid','=','a.positionid')
										->where('a.sectorid','=',$sectorid)
										->where('a.positionid','=',$positionid)
										->where('a.orderid','=',$data->orderid)
										->where('a.deployment_status','Active')
										->get();
				
				
				$sector			=	DB::table('sector_tbl')->where('sectorid',$sectorid)->value('sectorname');
				$position		=	DB::table('position_tbl')->where('positionid',$positionid)->value('consultantposition');
				$remuneration	=	DB::table('remuneration_tbl')
									->where('categoryid',$categoryid)
									->where('sectorid',$sectorid)
									->where('positionid',$positionid)
									->where('tierid',$data->tierid)
									->where('rateid',$activeRateId)
									->value('remuneration');
				$html 		= 	view('admin.reporting.ajaxpages.resourceDataTable',['data'=>$data,'position'=>$position,'sector'=>$sector,'remuneration'=>$remuneration])->render();
				return $html;
			}
		}
		else
		{
			if($format_id==1 && $categoryid==2)
			{
				$data	=	DB::table('eoi_work_order as a')
							->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->join('department_tbl as c','c.departmentid','=','a.department_id')
							->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
							->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
							->where('a.orderid','=',$orderid)
							->where('a.categoryid','=',$categoryid)
							->where('a.vendorid','=',$vendorid)
							->where('a.department_id','=',$departmentid)
							->first();

				$positions	=	DB::table('eoi_resource_deployment as a')
								->select('a.positionid','b.consultantposition')
								->join('position_tbl as b','b.positionid','=','a.positionid')
								->where('a.sectorid',$sectorid)
								->where('a.orderid',$data->orderid)
								->where('a.deployment_status','Active')
								->orderBy('a.positionid','DESC')
								->distinct()
								->get();
				
				foreach($positions as $position)
				{
					$position->remuneration	=	DB::table('remuneration_tbl')
												->where('categoryid',$categoryid)
												->where('sectorid',$sectorid)
												->where('positionid',$position->positionid)
												->where('tierid',$data->tierid)
												->where('rateid',$activeRateId)
												->value('remuneration');

					$position->resources	=	DB::table('eoi_resource_deployment as a')
												->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration')
												->where('a.orderid','=',$data->orderid)
												->where('a.sectorid','=',$sectorid)
												->where('a.positionid','=',$position->positionid)
												->where('a.deployment_status','Active')
												->get();
				}
				
				
				$sectorname	=	DB::table('sector_tbl')->where('sectorid',$sectorid)->value('sectorname');
				
				$html = view('admin.reporting.ajaxpages.resourceTotalDataTable',['data'=>$data,'sectorname'=>$sectorname,'positions'=>$positions])->render();
				return $html;
			}
			
		}
	}
	public function getOrderWiseResourceList($format_id=0,$orderid=0,$departmentid=0,$categoryid=0,$sectorid=0,$positionid=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		
		if(($format_id==2 || $format_id==4) && $categoryid==2)
		{
			$departmentName	=	DB::table('department_tbl')->where('departmentid',$departmentid)->value('departmentname');
			
			$orders	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname as vendor','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.department_id','=',$departmentid)
						->where('a.isActiveOrder',1)
						->where('a.isExtended',0)
						->whereDate('a.workorderduedate','>=',today())
						->when($orderid > 0, function ($q) use ($orderid) {
							$q->where('a.orderid', $orderid);
						})						
						->get();
			
			foreach ($orders as $order)
			{
				$order->groups	=	DB::table('eoi_resource_deployment as a')
									->select(
										'a.sectorid',
										'b.sectorname',
										'a.positionid',
										'c.consultantposition',
										DB::raw("
										(
											SELECT remuneration
											FROM remuneration_tbl r
											WHERE r.sectorid = a.sectorid
											  AND r.positionid = a.positionid
											  AND r.categoryid = $categoryid
											  AND r.rateid = $activeRateId
											  AND r.tierid = $order->tierid
											LIMIT 1
										) AS remuneration")
									)
									->join('sector_tbl as b', 'b.sectorid', '=', 'a.sectorid')
									->join('position_tbl as c', 'c.positionid', '=', 'a.positionid')
									->where('a.orderid', $order->orderid)
									->where('a.deployment_status', 'Active')
									->when($sectorid > 0, function ($q) use ($sectorid) {
										$q->where('a.sectorid', $sectorid);
									})
									->when($positionid > 0, function ($q) use ($positionid) {
										$q->where('a.positionid', $positionid);
									})
									->when($orderid > 0, function ($q) use ($orderid) {
										$q->where('a.orderid', $orderid);
									})						
									->groupBy(
										'a.sectorid',
										'b.sectorname',
										'a.positionid',
										'c.consultantposition'
									)
									->orderBy('b.sectorname')
									->get();

				foreach($order->groups as $group)
				{
					$group->resources	=	DB::table('eoi_resource_deployment as a')
											->select(
												'a.employee_code',
												'a.name',
												'a.mobilenumber',
												'a.email',
												'a.deployed_date',
												'a.deployment_status'
											)
											->where('a.orderid', $order->orderid)
											->where('a.sectorid', $group->sectorid)
											->where('a.positionid', $group->positionid)
											->where('a.deployment_status', 'Active')
											->get();
				}
			}
			$orders = $orders->filter(function ($order) {
				return $order->groups->count() > 0;
			})->values();			
			
			$html = view('admin.reporting.ajaxpages.orderWiseResourceDataTable',['orders'=>$orders,'departmentName'=>$departmentName])->render();
			
			return $html;

		}
	}

	public function undeployedData($categoryid=0,$vendorid=0,$departmentid=0,$sectorid=0,$positionid=0,$experiencelevel=0,$pagesearch=NULL,$format_id=0,$managerid=0,$in_house=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		/* AWD SECTION */
		if($format_id==1 && $categoryid==1)
		{
			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo', 'wo.orderid', '=', 'rd.orderid')
						->join('vendor_tbl as v', 'v.vendorid', '=', 'wo.vendorid')
						->join('department_tbl as d', 'd.departmentid', '=', 'wo.department_id')
						->where('rd.deployment_status','Pending')
						->where('rd.isExtended',0)
						->where('wo.isCancelled', 0)
						->where('wo.isActiveOrder', 1)
						->whereDate('wo.workorderduedate','>=',today())
						->when($categoryid > 0, function ($q) use ($categoryid) {
							$q->where('wo.categoryid', $categoryid);
						})
						->when($vendorid > 0, function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('rd.experiencelevel', $experiencelevel);
						})

						->when($departmentid > 0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid > 0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						->when($pagesearch != '', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})

						->select(
							'd.departmentid as departmentid',
							'd.departmentname as department_name',
							'd.shortname as department',
							'v.shortname as vendor',
							'v.vendorid as vendorid',
							'v.companyname as vendor_name',
							'wo.ordernumber as work_order_no',
							'wo.orderid as orderid',
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 1 THEN 1 ELSE 0 END) as l1"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 2 THEN 1 ELSE 0 END) as l2"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 3 THEN 1 ELSE 0 END) as l3"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 4 THEN 1 ELSE 0 END) as l4"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 5 THEN 1 ELSE 0 END) as l5"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 6 THEN 1 ELSE 0 END) as l6"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 7 THEN 1 ELSE 0 END) as l7"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 8 THEN 1 ELSE 0 END) as l8"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 9 THEN 1 ELSE 0 END) as l9"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 10 THEN 1 ELSE 0 END) as l10"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 11 THEN 1 ELSE 0 END) as l11"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 12 THEN 1 ELSE 0 END) as l12"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 13 THEN 1 ELSE 0 END) as l13"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 14 THEN 1 ELSE 0 END) as l14"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 15 THEN 1 ELSE 0 END) as l15"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 16 THEN 1 ELSE 0 END) as l16"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 17 THEN 1 ELSE 0 END) as l17"),
							DB::raw("SUM(CASE WHEN rd.experiencelevel = 18 THEN 1 ELSE 0 END) as l18"),

							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.orderid',
						)
						->orderBy('wo.orderdate')
						->get();
			
			return $data;
		}
		
		/* AWD SECTION CLOSED */
		/*CONSULTANCY SECTION*/
		if($format_id==1 && $categoryid==2)
		{
			$engSectorId	=	4;
			$finSectorId	=	2;
			$govSectorId  	= 	3;
			$healSectorId  	= 	5;
			$infoSectorId  	= 	1;
			$socSectorId  	= 	6;

			$data	=	DB::table('eoi_resource_deployment as rd')
						->join('eoi_work_order as wo','wo.orderid', '=', 'rd.orderid')
						->join('department_tbl as d','d.departmentid', '=', 'wo.department_id')
						->when($in_house==1, function ($query) {
							$query->join('users_tbl as u', function ($join) {
								$join->on('u.userid','=','d.userid')
									 ->where('u.ispm','=',1)
									 ->where('wo.categoryid',2);
							});
						})
						->where('rd.deployment_status','Pending')
						->where('wo.isCancelled',0)
						->where('wo.isActiveOrder',1)
						->where('wo.isExtended',0)
						->where('rd.isExtended',0)
						->whereDate('wo.workorderduedate','>=',today())
						->when($categoryid>0,function ($q) use ($categoryid){
							$q->where('wo.categoryid',$categoryid);
						})
						->when($vendorid>0,function ($q) use ($vendorid) {
							$q->where('wo.vendorid', $vendorid);
						})
						->when($departmentid>0, function ($q) use ($departmentid) {
							$q->where('wo.department_id', $departmentid);
						})
						->when($managerid>0, function ($q) use ($managerid) {
							$q->where('wo.department_id', $managerid);
						})
						->when($sectorid>0, function ($q) use ($sectorid) {
							$q->where('rd.sectorid', $sectorid);
						})
						->when($positionid>0, function ($q) use ($positionid) {
							$q->where('rd.positionid', $positionid);
						})
						->when($pagesearch!='', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('wo.ordernumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.refrence', 'like', '%' . $pagesearch . '%')
								  ->orWhere('wo.project_name', 'like', '%' . $pagesearch . '%');
							});
						})
						->select(
							'wo.orderid','wo.ordernumber','wo.department_id as departmentid',
							DB::raw("SUM(CASE WHEN rd.sectorid = {$engSectorId} THEN 1 ELSE 0 END) as engineering"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$finSectorId} THEN 1 ELSE 0 END) as finance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$govSectorId} THEN 1 ELSE 0 END) as governance"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$healSectorId} THEN 1 ELSE 0 END) as health"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$infoSectorId} THEN 1 ELSE 0 END) as information"),
							DB::raw("SUM(CASE WHEN rd.sectorid = {$socSectorId} THEN 1 ELSE 0 END) as social"),
							DB::raw("SUM(CASE WHEN rd.positionid = 5 THEN 1 ELSE 0 END) as associate_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 4 THEN 1 ELSE 0 END) as consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 3 THEN 1 ELSE 0 END) as senior_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 2 THEN 1 ELSE 0 END) as principal_consultant"),
							DB::raw("SUM(CASE WHEN rd.positionid = 1 THEN 1 ELSE 0 END) as managing_consultant"),
							DB::raw("COUNT(rd.deploymentid) as total_resources")
						)
						->groupBy(
							'wo.orderid'
						)
						->orderBy('wo.orderdate')
						->get();
						
			return $data;
		}
		/*CONSULTANCY SECTION COMPLETED*/
	}

	public function getUndeployedResourceList($format_id=0,$orderid=0,$departmentid=0,$categoryid=0,$sectorid=0,$positionid=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		
		if($format_id==1 && $categoryid==2)
		{
			$departmentName	=	DB::table('department_tbl')->where('departmentid',$departmentid)->value('departmentname');
			
			$orders	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname as vendor','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.department_id','=',$departmentid)
						->where('a.isActiveOrder',1)
						->where('a.isExtended',0)
						->whereDate('a.workorderduedate','>=',today())
						->when($orderid > 0, function ($q) use ($orderid) {
							$q->where('a.orderid', $orderid);
						})						
						->get();
			
			foreach ($orders as $order)
			{
				$order->groups	=	DB::table('eoi_resource_deployment as a')
									->select(
										'a.sectorid',
										'b.sectorname',
										'a.positionid',
										'c.consultantposition',
										DB::raw("
										(
											SELECT remuneration
											FROM remuneration_tbl r
											WHERE r.sectorid = a.sectorid
											  AND r.positionid = a.positionid
											  AND r.categoryid = $categoryid
											  AND r.rateid = $activeRateId
											  AND r.tierid = $order->tierid
											LIMIT 1
										) AS remuneration")
									)
									->join('sector_tbl as b', 'b.sectorid', '=', 'a.sectorid')
									->join('position_tbl as c', 'c.positionid', '=', 'a.positionid')
									->where('a.orderid', $order->orderid)
									->where('a.deployment_status', 'Pending')
									->when($sectorid > 0, function ($q) use ($sectorid) {
										$q->where('a.sectorid', $sectorid);
									})
									->when($positionid > 0, function ($q) use ($positionid) {
										$q->where('a.positionid', $positionid);
									})
									->when($orderid > 0, function ($q) use ($orderid) {
										$q->where('a.orderid', $orderid);
									})						
									->groupBy(
										'a.sectorid',
										'b.sectorname',
										'a.positionid',
										'c.consultantposition'
									)
									->orderBy('b.sectorname')
									->get();

				foreach($order->groups as $group)
				{
					$group->resources	=	DB::table('eoi_resource_deployment as a')
											->select(
												'a.employee_code',
												'a.name',
												'a.mobilenumber',
												'a.email',
												'a.deployed_date',
												'a.deployment_status'
											)
											->where('a.orderid', $order->orderid)
											->where('a.sectorid', $group->sectorid)
											->where('a.positionid', $group->positionid)
											->where('a.deployment_status', 'Pending')
											->get();
				}
			}
			$orders = $orders->filter(function ($order) {
				return $order->groups->count() > 0;
			})->values();			
			
			$html = view('admin.reporting.ajaxpages.orderWiseResourceDataTable',['orders'=>$orders,'departmentName'=>$departmentName])->render();
			
			return $html;

		}
	}
	public function getAwdUndeployedResourceList($format_id=0,$departmentid=0,$vendorid=0,$orderid=0,$categoryid=0,$experiencelevel=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		
		if($format_id==1 && $categoryid==1)
		{
			$data	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.vendorid','=',$vendorid)
						->where('a.orderid','=',$orderid)
						->where('a.department_id','=',$departmentid)
						->whereDate('a.workorderduedate','>=',today())
						->first();
			
			$levels	=	DB::table('eoi_resource_deployment')
						->select('experiencelevel')
						->where('orderid',$data->orderid)
						->where('tierid',$data->tierid)
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('experiencelevel', $experiencelevel);
						})
						->distinct()
						->orderBy('experiencelevel')
						->get();
			
			foreach($levels as $lvl)
			{
				$lvl->remuneration=	DB::table('remuneration_tbl')
									->where('categoryid',$categoryid)
									->where('experiencelevel',$lvl->experiencelevel)
									->where('tierid',$data->tierid)
									->where('rateid',$activeRateId)
									->value('remuneration');
			
				$lvl->resources	=	DB::table('eoi_resource_deployment as a')
										->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration')
										->where('a.experiencelevel','=',$lvl->experiencelevel)
										->where('a.orderid','=',$data->orderid)
										->where('a.deployment_status','Pending')
										->get();
			}			
			foreach ($levels as $key => $lvl)
			{
				$lvl->resources = DB::table('eoi_resource_deployment as a')
					->where('a.experiencelevel', $lvl->experiencelevel)
					->where('a.orderid', $data->orderid)
					->where('a.deployment_status', 'Pending')
					->get();

				if ($lvl->resources->count() == 0) {
					$levels->forget($key);   // Laravel Collection way
				}
			}

			$levels = $levels->values();			
			$html	= 	view('admin.reporting.ajaxpages.awdresourceDataTable',['data'=>$data,'levels'=>$levels])->render();
			
			return $html;
		}
	}


	public function getAwdResourceReportList($format_id=0,$departmentid=0,$vendorid=0,$orderid=0,$categoryid=0,$experiencelevel=0)
	{
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		if($format_id==3 && $categoryid==1)
		{
			$firmName	=	DB::table('vendor_tbl')->where('vendorid',$vendorid)->value('companyname');
			$data	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.vendorid','=',$vendorid)
						->whereDate('a.workorderduedate','>=',today())
						->get();

			foreach ($data as $order)
			{
				$order->levels = DB::table('eoi_resource_deployment')
								->select('experiencelevel')
								->where('orderid', $order->orderid)
								->where('tierid', $order->tierid)
								->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
									$q->where('experiencelevel', $experiencelevel);
								})
								->distinct()
								->orderBy('experiencelevel')
								->get();

				foreach ($order->levels as $lvl)
				{
					$lvl->remuneration = DB::table('remuneration_tbl')
										->where('categoryid', $categoryid)
										->where('experiencelevel', $lvl->experiencelevel)
										->where('tierid', $order->tierid)
										->where('rateid', $activeRateId)
										->value('remuneration');

					$lvl->resources = DB::table('eoi_resource_deployment as a')
										->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration')
										->where('a.experiencelevel', $lvl->experiencelevel)
										->where('a.orderid', $order->orderid)
										->where('a.deployment_status', 'Active')
										->get();
				}

				$order->levels = $order->levels->filter(function ($lvl) {
					return $lvl->resources->isNotEmpty();
				})->values();
			}

			$data = $data->filter(function ($order) {
				return $order->levels->isNotEmpty();
			})->values();

			$html	= 	view('admin.reporting.ajaxpages.awdFirmDataTable',['data'=>$data,'firmname'=>$firmName])->render();
			
			return $html;
		}

		if($format_id==2 && $categoryid==1)
		{
			$managerName	=	DB::table('department_tbl')->where('departmentid',$departmentid)->value('departmentname');
			$data	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.department_id','=',$departmentid)
						->whereDate('a.workorderduedate','>=',today())
						->get();

			foreach ($data as $order)
			{
				$order->levels = DB::table('eoi_resource_deployment')
								->select('experiencelevel')
								->where('orderid', $order->orderid)
								->where('tierid', $order->tierid)
								->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
									$q->where('experiencelevel', $experiencelevel);
								})
								->distinct()
								->orderBy('experiencelevel')
								->get();

				foreach ($order->levels as $lvl)
				{
					$lvl->remuneration = DB::table('remuneration_tbl')
										->where('categoryid', $categoryid)
										->where('experiencelevel', $lvl->experiencelevel)
										->where('tierid', $order->tierid)
										->where('rateid', $activeRateId)
										->value('remuneration');

					$lvl->resources = DB::table('eoi_resource_deployment as a')
										->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration')
										->where('a.experiencelevel', $lvl->experiencelevel)
										->where('a.orderid', $order->orderid)
										->where('a.deployment_status', 'Active')
										->get();
				}

				$order->levels = $order->levels->filter(function ($lvl) {
					return $lvl->resources->isNotEmpty();
				})->values();
			}

			$data = $data->filter(function ($order) {
				return $order->levels->isNotEmpty();
			})->values();

			$html	= 	view('admin.reporting.ajaxpages.awdManagerDataTable',['data'=>$data,'managername'=>$managerName])->render();
			
			return $html;
		}

		
		if($format_id==1 && $categoryid==1)
		{
			$data	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.companyname as vendorname','b.shortname','c.departmentname','c.shortname as deptname','d.tierid','d.tiername','e.engagementname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('department_tbl as c','c.departmentid','=','a.department_id')
						->join('tiermaster_tbl as d','d.tierid','=','b.tierid')
						->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
						->where('a.categoryid','=',$categoryid)
						->where('a.vendorid','=',$vendorid)
						->where('a.orderid','=',$orderid)
						->where('a.department_id','=',$departmentid)
						->whereDate('a.workorderduedate','>=',today())
						->first();
			
			$levels	=	DB::table('eoi_resource_deployment')
						->select('experiencelevel')
						->where('orderid',$data->orderid)
						->where('tierid',$data->tierid)
						->when($experiencelevel > 0, function ($q) use ($experiencelevel) {
							$q->where('experiencelevel', $experiencelevel);
						})
						->distinct()
						->orderBy('experiencelevel')
						->get();
			
			foreach($levels as $lvl)
			{
				$lvl->remuneration=	DB::table('remuneration_tbl')
									->where('categoryid',$categoryid)
									->where('experiencelevel',$lvl->experiencelevel)
									->where('tierid',$data->tierid)
									->where('rateid',$activeRateId)
									->value('remuneration');
			
				$lvl->resources	=	DB::table('eoi_resource_deployment as a')
										->select('a.name','a.mobilenumber','a.email','a.employee_code','a.deployed_date','a.deployment_status','a.remuneration')
										->where('a.experiencelevel','=',$lvl->experiencelevel)
										->where('a.orderid','=',$data->orderid)
										->where('a.deployment_status','Active')
										->get();
			}			
			foreach ($levels as $key => $lvl)
			{
				$lvl->resources = DB::table('eoi_resource_deployment as a')
					->where('a.experiencelevel', $lvl->experiencelevel)
					->where('a.orderid', $data->orderid)
					->where('a.deployment_status', 'Active')
					->get();

				if ($lvl->resources->count() == 0) {
					$levels->forget($key);   // Laravel Collection way
				}
			}

			$levels = $levels->values();			
			$html	= 	view('admin.reporting.ajaxpages.awdresourceDataTable',['data'=>$data,'levels'=>$levels])->render();
			
			return $html;
		}
	}

	public function getEoiSummaryDetail($categoryid=0,$vendorid=0,$departmentid=0,$managerid=0,$format_id=0,$in_house=0,$pagesearch=NULL,$record_type=0,$projectid=0)
	{
		$record_type	=	intval($record_type);
		$activeRateId	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
		if($format_id==1 && ($categoryid==2 || $categoryid==1))
		{
			$data	=	DB::table('eoi_request as a')
						->select('a.requestid','a.eoinumber','a.engagementname','b.departmentname','a.isClosed','c.projectid','c.project_name')
						->join('department_tbl as b','b.userid','=','a.userid')
						->leftJoin('project_tbl as c','c.projectid','=','a.projectid')
						->when($in_house==1,function ($query) {
							$query->join('users_tbl as u', function ($join) {
								$join->on('u.userid','=','b.userid')
									 ->where('u.ispm','=',1)
									 ->where('a.categoryid',2);
							});
						})
						->where('a.isordered',1)
						->where('a.iscancelled',0)
						->when($categoryid>0, function ($q) use ($categoryid) {
							$q->where('a.categoryid', $categoryid);
						})
						->when($departmentid>0, function ($q) use ($departmentid) {
							$q->where('b.departmentid', $departmentid);
						})
						->when($managerid>0, function ($q) use ($managerid) {
							$q->where('b.departmentid', $managerid);
						})
						->when($record_type==1, function ($q) use ($record_type) {
							$q->where('a.isClosed',0);
						})
						->when($record_type==3, function ($q) use ($record_type) {
							$q->where('a.isClosed',1);
						})
						->when($projectid>0, function ($q) use ($projectid) {
							$q->where('a.projectid', $projectid);
						})
						->when($pagesearch!='', function ($query) use ($pagesearch) {
							$query->where(function ($q) use ($pagesearch) {
								$q->where('a.projecttitle', 'like', '%' . $pagesearch . '%')
								  ->orwhere('a.eoinumber', 'like', '%' . $pagesearch . '%')
								  ->orwhere('a.engagementname', 'like', '%' . $pagesearch . '%');
							});
						})
						->orderBy('c.project_name')
						->get();

			foreach($data as $key => $eoi)
			{
				$eoi->orders	=	DB::table('eoi_work_order as a')
									->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.shortname',
										DB::raw('(SELECT COUNT(*) 
												  FROM eoi_resource_deployment rd 
												  WHERE rd.orderid = a.orderid) as total_resource'),

										DB::raw("(SELECT COUNT(*) 
												  FROM eoi_resource_deployment rd 
												  WHERE rd.orderid = a.orderid 
												  AND rd.deployment_status = 'Active') as active_total"),

										DB::raw("(SELECT COUNT(*) 
												  FROM eoi_resource_deployment rd 
												  WHERE rd.orderid = a.orderid 
												  AND rd.deployment_status = 'Released') as released_total"),

										DB::raw("(SELECT COUNT(*) 
												  FROM eoi_resource_deployment rd 
												  WHERE rd.orderid = a.orderid 
												  AND rd.deployment_status = 'Pending') as pending_total"),
										
										DB::raw('(
												SELECT COALESCE(SUM(vi.invoice_value), 0)
												FROM vendor_invoices vi
												WHERE vi.orderid = a.orderid) as invoice_submitted'),
										DB::raw('(
												SELECT COALESCE(SUM(vi.paid_value), 0)
												FROM vendor_invoices vi
												WHERE vi.orderid = a.orderid) as invoice_paid')
									)
									->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
									->where('a.requestid',$eoi->requestid)
									->where('a.isExtended',0)
									->where('a.isCancelled',0)
									->when($vendorid>0,function ($q) use ($vendorid) {
										$q->where('a.vendorid',$vendorid);
									})
									->when($record_type===1,function ($q){
										$q->whereDate('a.workorderduedate','>=',today())
										  ->where('a.isClosed',0)
										  ->where('a.isActiveOrder',1);
									})
									->when($record_type===3,function ($q) use ($record_type) {
										$q->where('a.isClosed',1)
										  ->where('a.isActiveOrder',0);
									})
									->when($eoi->projectid>0,function ($q) use ($eoi) {
										$q->where('a.projectid',$eoi->projectid);
									})
									->orderBy('a.orderdate')
									->get();

				if($eoi->orders->isEmpty())
				{
					unset($data[$key]);
				}
			}
			$data 	= 	collect($data)->values();
			$html	= 	view('admin.reporting.ajaxpages.eoiSummaryData',['data'=>$data])->render();
			
			return $html;
		}
	}
}