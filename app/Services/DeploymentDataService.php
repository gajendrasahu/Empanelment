<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeploymentDataService
{
    public function getCsfDeploymentData($orderid)
    {
			$detail		=	DB::table('eoi_resource_deployment as a')
							->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
							->leftJoin('remuneration_tbl as e', function ($join) {
								$join->on('e.sectorid', '=', 'a.sectorid')
									 ->whereColumn('e.positionid', '=', 'a.positionid')
									 ->whereColumn('e.tierid', '=', 'a.tierid')
									 ->whereColumn('e.rateid', '=', 'a.rateid');
							})
							->where('a.orderid', $orderid)
							->get();
			return $detail;	
    }

    public function getAwdDeploymentData($orderid)
    {
		$detail		=	DB::table('eoi_resource_deployment as a')
						->select('a.*','d.tiername','e.remuneration')
						->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
						->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
						->where('a.orderid', $orderid)
						->get();
		return $detail;	
		
		/*
		$detail		=	DB::table('eoi_request_detail as a')
						->select('a.*','b.tiername','e.remuneration','f.name','f.mobilenumber','f.email','f.deployment_date','f.deployed_date','f.deployment_status')
						->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
						->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
						->leftJoin('eoi_resource_deployment as f','f.recordid','=','a.recordid')
						->where('a.requestid','=',$requestid)
						->get();
				
		return $detail;
		*/
    }
	
	public function getOrderWiseResource(Request $request,$categoryid=0,$vendorid=0,$departmentid=0,$sectorid=0,$positionid=0,$experiencelevel=0,$order_type=NULL,$pagesearch=NULL)
	{
		$vendId	=	$request->session()->get('vendorId');
		$deptId	=	$request->session()->get('departmentId');

		
		$data	=	DB::table('eoi_work_order as a')
					->select('a.*','c.companyname','g.isdepartment','g.ispm','p.project_name')
					->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
					->leftJoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
					->leftJoin('eoi_request as e', 'e.requestid', '=', 'a.requestid')
					->leftJoin('department_tbl as f', 'f.departmentid', '=', 'a.department_id')
					->leftJoin('users_tbl as g', 'g.userid', '=', 'f.userid')
					->leftJoin('project_tbl as p', 'p.projectid', '=', 'a.projectid')
					->when($pagesearch!=NULL, function ($query) use ($pagesearch) {
						$query->where(function ($q) use ($pagesearch) {
							$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
							  ->orWhere('a.refrence', 'like', '%' . $pagesearch . '%')
							  ->orWhere('a.project_name', 'like', '%' . $pagesearch . '%')
							  ->orWhere('f.departmentname', 'like', '%' . $pagesearch . '%')
							  ->orWhere('c.companyname', 'like', '%' . $pagesearch . '%');
						});
					})
					->when($categoryid!=0,function($query) use ($categoryid){
						return $query->where('a.categoryid','=',$categoryid);
					})
					->when($vendorid!=0,function($query) use ($vendorid){
						return $query->where('a.vendorid','=',$vendorid);
					})
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('a.department_id','=',$departmentid);
					})
					->when($order_type==1,function($query) {
						return $query->where('a.isActiveOrder','=',1)
									 ->where('a.workorderduedate','>',now())
									 ->where('a.isExtended','=',0);
					})								
					->when($order_type==2,function($query) {
						return $query->where('a.isExtended','=',1)
									 ->where('a.workorderduedate','>',now());
					})								
					->when($order_type==3,function($query) {
						return $query->where('a.workorderduedate','<',date('Y\-m\-d'));
					})								
					->when($order_type == 4, function ($query) {
						return $query->whereBetween('a.workorderduedate', [
							date('Y-m-d'), 
							date('Y-m-d', strtotime('+60 days'))
						]);
					})
					->where('a.isCancelled',0)
					->orderBy('orderdate','DESC')
					->get();
		
		foreach($data as $key => $rec)
		{
			if($rec->categoryid==2)
			{
				$rec->resources	= 	DB::table('eoi_resource_deployment as a')
									->select('a.*','b.sectorname','c.consultantposition')
									->join('sector_tbl as b','b.sectorid','=','a.sectorid')
									->join('position_tbl as c','c.positionid','=','a.positionid')
									->where('a.orderid',$rec->orderid)
									->when($sectorid!=NULL,function($query) use($sectorid) {
										return $query->where('a.sectorid','=',$sectorid);
									})                              
									->when($positionid!=NULL,function($query) use($positionid) {
										return $query->where('a.positionid','=',$positionid);
									})                              
									->when($experiencelevel!=NULL,function($query) use($experiencelevel) {
										return $query->where('a.experiencelevel','=',$experiencelevel);
									})                              
									->get();
			}

			if($rec->categoryid==1)
			{
				$rec->resources	=	DB::table('eoi_resource_deployment as a')
									->select('a.*')
									->where('a.orderid',$rec->orderid)
									->when($experiencelevel!=NULL,function($query) use($experiencelevel) {
										return $query->where('a.experiencelevel','=',$experiencelevel);
									})                              
									->get();
			}

			if($rec->resources->count()==0)
			{
				unset($data[$key]);
			}
		}

		return $data->values();
	}
}