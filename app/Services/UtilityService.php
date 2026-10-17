<?php

namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Exception;

class UtilityService
{
    public function getDepartments()
    {
		return DB::table('users_tbl as a')
				->select('a.userid','a.name','b.departmentid','b.departmentname','b.shortname')
				->Join('department_tbl as b','b.userid','=','a.userid')
				->where('isdepartment',1)
				->get();
    }
    public function getProjectManagers()
    {
		return DB::table('users_tbl as a')
				->select('a.userid','a.name','b.departmentid','b.departmentname','b.shortname')
				->Join('department_tbl as b','b.userid','=','a.userid')
				->where('ispm',1)
				->get();
    }

    public function getCategories()
    {
		return DB::table('jobcategory_tbl')->select('categoryid','jobcategory')->orderBy('jobcategory')->get();
    }
	
}
?>