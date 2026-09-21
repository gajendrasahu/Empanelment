<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

use Illuminate\Support\Facades\Hash;

class TierWiseDataService
{
    public function getCsfTempPrice($userid,$categoryid=NULL,$tierid=NULL,$rateid=NULL)
    {
		return DB::table('eoi_temp_requirement as a')
				->select(
					'a.projecttitle','a.projectduration','a.projectobjective','a.recordid',
					'a.qualification','a.requirednumber','a.duration','e.remuneration as budget','e.includingtax','a.role',
					'a.experience','a.remark','b.jobcategory','c.tiername','f.workexperience',
					'e.experiencelevel','g.sectorname','h.consultantposition','a.employmenttype'
				)
				->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
				->leftJoin('remuneration_tbl as e', function($join) use ($rateid) {
					$join->on('e.sectorid', '=', 'a.sectorid')
						 ->on('e.positionid', '=', 'a.positionid');

					if ($rateid !== NULL) {
						$join->on('e.rateid', '=', 'a.rateid');
					}
				})
				->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
				->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
				->leftJoin('position_tbl as h', 'h.positionid', '=', 'a.positionid')
				->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
				->where('a.userid', $userid)
				->where('a.categoryid', $categoryid)
				->where('e.tierid', $tierid)
				->when($rateid !== NULL, function ($query) use ($rateid) {
					return $query->where('a.rateid', $rateid);
				})
				->get();		
    }
	
    public function getCsfPrice($requestid,$categoryid=NULL,$tierid=NULL,$rateid=NULL)
    {
		return DB::table('eoi_request_detail as a')
				->select(
					'a.recordid','a.qualification','a.duration','e.remuneration as budget','e.includingtax','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition','a.employmenttype'
				)
				->leftJoin('remuneration_tbl as e', function($join) use ($rateid) {
					$join->on('e.sectorid','=','a.sectorid')
						 ->on('e.positionid','=','a.positionid');

					if ($rateid !== NULL) {
						$join->on('e.rateid', '=', 'a.rateid');
					}
				})
				->leftJoin('work_experience as f', 'f.experienceid', '=', 'e.experienceid')
				->leftJoin('sector_tbl as g', 'g.sectorid', '=', 'a.sectorid')
				->leftJoin('position_tbl as h', 'h.positionid','=','a.positionid')
				->leftJoin('tiermaster_tbl as c','c.tierid','=','e.tierid')
				->where('e.tierid', '=', $tierid)
				->where('a.requestid','=',$requestid)
				->where('e.categoryid','=',$categoryid)
				->when($rateid!==NULL, function ($query) use ($rateid) {
					return $query->where('a.rateid', $rateid);
				})
				->get();
    }
    public function getAwdTempPrice($userid,$categoryid=NULL,$tierid=NULL,$rateid=NULL)
    {
		return	DB::table('eoi_temp_requirement as a')
					->select(
						'a.projecttitle','a.projectduration','a.projectobjective','a.recordid',
						'a.qualification','a.requirednumber','a.duration','e.remuneration as budget','e.includingtax','a.role',
						'a.experience','a.remark','b.jobcategory','c.tiername',
						'e.experiencelevel','a.employmenttype'
					)
					->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
					->leftJoin('remuneration_tbl as e', function($join) use ($rateid){
						$join->on('e.categoryid', '=', 'a.categoryid');
						$join->on('e.experiencelevel', '=', 'a.experiencelevel');
						if ($rateid !== NULL) {
							$join->on('e.rateid', '=', 'a.rateid');
						}
					})
					->leftJoin('tiermaster_tbl as c', 'c.tierid', '=', 'e.tierid')
					->where('e.tierid','=',$tierid)
					->where('a.userid','=',$userid)
					->where('a.categoryid','=',$categoryid)
					->when($rateid !== NULL, function ($query) use ($rateid) {
						return $query->where('a.rateid', $rateid);
					})
					->get();
		
    }

    public function getAwdPrice($requestid,$categoryid=NULL,$tierid=NULL,$rateid=NULL)
    {
		return	DB::table('eoi_request_detail as a')
					->select(
						'a.recordid','a.qualification','a.duration','e.remuneration as budget','e.includingtax','a.admincharge','a.total','a.role','a.experience','a.remark','c.tiername','f.workexperience','e.experiencelevel','a.employmenttype'
					)
					->leftJoin('remuneration_tbl as e', function ($join) use ($categoryid,$tierid,$rateid) {
						$join->on('e.experiencelevel','=','a.experiencelevel')
							 ->where('e.categoryid','=',$categoryid)
							 ->when($rateid!=NULL, function ($query) use ($rateid) {
								return $query->where('e.rateid', $rateid);
							 })
							 ->where('e.tierid','=',$tierid);
					})
					->leftJoin('work_experience as f', 'f.experienceid','=','e.experienceid')
					->leftJoin('tiermaster_tbl as c','c.tierid','=','e.tierid')
					->where('a.requestid','=',$requestid)
					->when($rateid !== NULL, function ($query) use ($rateid) {
						return $query->where('a.rateid', $rateid);
					})
					->get();
				
    }

    public function getOrderTempData($userid)
    {
		return	DB::table('temp_work_order as a')
					->select('a.*','b.tiername','c.sectorname','d.consultantposition')
					->leftjoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
					->leftjoin('sector_tbl as c','c.sectorid','=','a.sectorid')
					->leftjoin('position_tbl as d','d.positionid','=','a.positionid')
					->where('a.userid','=',$userid)
					->get();
		
    }


    public function getCsfResourceSummary($requestid)
    {
		return DB::table('eoi_request_detail as a')
				->select(
					'a.sectorid',
					'a.positionid',
					DB::raw('COUNT(*) as resource_count'),
					'a.experience',
					'b.sectorname',
					'c.consultantposition',
					'a.duration'
				)
				->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
				->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
				->where('a.requestid',$requestid)
				->groupBy('a.sectorid', 'a.positionid')
				->get();
    }
    public function getAwdResourceSummary($requestid)
    {
		return DB::table('eoi_request_detail as a')
				->select(
					'a.role',
					'a.experiencelevel',
					DB::raw('COUNT(*) as resource_count'),
					'a.experience',
					'a.duration'
				)
				->where('a.requestid',$requestid)
				->groupBy('a.role','a.experiencelevel')
				->get();
    }

}