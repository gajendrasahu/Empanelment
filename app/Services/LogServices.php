<?php
namespace App\Services;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use Exception;

class LogServices
{
		
	public function logChanges($requestId, $oldData, $newData)
	{
		$changes = [];
		foreach ($newData as $field => $newValue)
		{
			if(isset($oldData[$field]) && $oldData[$field]!=$newValue)
			{
				if($field=='prebidlastdate')
				{
					if($oldData[$field]!='01-01-1970' || $oldData[$field]!='01-01-1970 05:30:00')
					{
						if(date('Y\-m\-d',strtotime($oldData[$field]))!=$newValue)
						{
							$changes[] = [
								'requestid'    		=> $requestId,
								'field_name'   		=> $field,
								'old_value'    		=> date('Y\-m\-d',strtotime($oldData[$field])),
								'new_value'    		=> $newValue,
								'updated_by'   		=> Session::get('userId'),
								'updated_by_name'   => Session::get('userName'),
								'updated_at'   		=> now(),
							];
						}
					}
				}
				else if($field=='deadlinedate' || $field=='interviewdate')
				{
					if($oldData[$field]!='01-01-1970' || $oldData[$field]!='01-01-1970 05:30:00')
					{
						if(date('Y\-m\-d H:i:s',strtotime($oldData[$field]))!=$newValue)
						{
							$changes[] = [
								'requestid'    		=> $requestId,
								'field_name'   		=> $field,
								'old_value'    		=> date('Y\-m\-d H:i:s',strtotime($oldData[$field])),
								'new_value'    		=> $newValue,
								'updated_by'   		=> Session::get('userId'),
								'updated_by_name'   => Session::get('userName'),
								'updated_at'   		=> now(),
							];
						}
					}					
				}
				else
				{
					if($oldData[$field]!='01-01-1970' || $oldData[$field]!='01-01-1970 05:30:00')
					{
						$changes[] = [
							'requestid'    		=> $requestId,
							'field_name'   		=> $field,
							'old_value'    		=> $oldData[$field],
							'new_value'    		=> $newValue,
							'updated_by'   		=> Session::get('userId'),
							'updated_by_name'   => Session::get('userName'),
							'updated_at'   		=> now(),
						];
					}					
					
				}
			}
		}

		if(!empty($changes))
		{
			DB::table('log_eoi_request_updated')->insert($changes);
		}
	}

	public function getMprLog($mprid=0)
	{
		$mpr = DB::table('mpr_tbl')->where('mpr_id', $mprid)->first();

		if(!$mpr)
		{
			return null;
		}

		$mpr->deployments	=	DB::table('mpr_attendance_logs')
								->select('deployment_id')
								->where('mpr_id',$mpr->mpr_id)
								->distinct()
								->get();

		foreach($mpr->deployments as $deployment)
		{
			$deployment->attendance	=	DB::table('mpr_attendance_logs')
										->where('mpr_id',$mpr->mpr_id)
										->where('deployment_id',$deployment->deployment_id)
										->orderBy('changed_at','desc')
										->get();
		}

		return $mpr;
	}
}
