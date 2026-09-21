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

class LoiBasedWorkOrderController extends Controller
{
    public function addLoIWorkOrder(Request $request,$categoryid=NULL)
	{
		
		$userId		= 	$request->session()->get('userId');
		
		$categoryid	=	Crypt::decrypt($categoryid);
		
		$token		=	rand('100000','999999').''.time();

		Session::put('form_token', $token);

		$vendors	=	DB::table('vendor_tbl')
						->select('vendorid','companyname')
						->where('categoryid',$categoryid)
						->orderby('companyname')
						->get();
						
		$departments=	DB::table('users_tbl as a')
						->select('b.departmentid','b.departmentname','b.shortname','a.ispm','a.isdepartment')
						->join('department_tbl as b','b.userid','=','a.userid')
						->when($categoryid==1,function($query) use ($categoryid){
							return $query->where('a.ispm','=',1);
						})
						->orderby('a.name')
						->get();

		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels		=	DB::table('remuneration_tbl')
						->select('experiencelevel')
						->where('experiencelevel','!=',0)
						->where('categoryid',$categoryid)
						->orderBy('experiencelevel')
						->distinct('experiencelevel')
						->get();

		$experiences=	DB::table('work_experience')
						->select('workexperience')
						->orderBy('experienceid')
						->get();
		
		$rateList	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->orderBy('rateid','DESC')->get();
		
        return view('admin/master/add_loiworkorder',compact('sectors','positions','levels','experiences','vendors','departments','token','categoryid','rateList'));
    }


    public function storeDirectWorkOrder(Request $request)
	{
		$rules = [
			'project_title' 	=> 'required|regex:/^[A-Za-z0-9@"$%&\(\)\[\],._\-\'\:\`\s]+$/',
			'vendorid'     		=>	'required',
			'rateid'     		=>	'required|numeric',
			'departmentid'     	=>	'required|numeric',
			'ordernumber'		=>	'required',
			'subject'			=>	'required',
			'signedby'			=>	'required|in:Chief Executive Officer (CEO),Jt. CEO (Project),Jt. CEO(Finance),Add. Chief Executive Officer,Chief Operating Officer',
			'notesheet'			=> 	'required|file|mimetypes:application/pdf|max:5120',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'order_date' 		=> 	'required|date_format:d-m-Y',
			'order_duedate' 	=> 	'required|date_format:d-m-Y',
		];

		
        $messages = [
			'project_title.required' 	=> 	'Project title is required.',
			'project_title.regex' 		=> 	'Project title may contain only letters, numbers, spaces, and these special characters: @ ( ) [ ] , \' : `',
			'rateid.required'			=>	'Rate list name is mandatory',
			'rateid.numeric'			=>	'Invalid rate list value provided',
			'departmentid.required'		=>	'Department / Project Manager name is mandatory',
			'departmentid.numeric'		=>	'Invalid department / project manager value provided',
			'vendorid.required'			=>	'Invalid vendorid',
			'ordernumber.required'		=>	'Order number is required',
			'subject.required'			=>	'Subject is required',
			'signedby.required'			=>	'Signed by name is required',
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
		
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$validatedData['vendorid'])->first();
		
		$orderDate 		= 	\Carbon\Carbon::createFromFormat('d-m-Y', $request->order_date);
		$orderDueDate 	= 	\Carbon\Carbon::createFromFormat('d-m-Y', $request->order_duedate);
		$days 			= 	$orderDate->diffInDays($orderDueDate);
		$maxDuration 	= 	round($days / 30, 1);

		if($vendor->categoryid==2)
		{
			foreach($request->sectorids as $i => $id)
			{
				$sectorRaw		= 	$request->sectorids[$i] ?? null;
				$positionRaw	= 	$request->positionids[$i] ?? null;
				$deploymentRaw	= 	$request->deploymenttypes[$i] ?? null;
				$startRaw 		= 	$request->startDate[$i] ?? null;
				$endRaw   		= 	$request->endDate[$i] ?? null;

				if($sectorRaw==null || $positionRaw==null || $deploymentRaw==null || $startRaw==null || $endRaw==null)
				{
					return response()->json([
						'status' => false,
						'errors' => [
							"sectorids.$i" => [
								"Please fill all fields OR remove the row."
							]
						]
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
				$existSector = DB::table('vendor_sector')
					->where('vendorid', $validatedData['vendorid'])
					->where('sectorid', $sectorRaw)
					->exists();

				if (!$existSector) {
					return response()->json([
						'status' => false,
						'errors' => [
							"sectorids.$i" => ["Selected sector is not mapped with the selected firm"]
						]
					], 422);
				}
				
			}
		}
		if($vendor->categoryid==1)
		{
			foreach($request->roles as $i => $id)
			{
				$roleRaw		= 	$request->roles[$i] ?? null;
				$nameRaw		= 	$request->resource_names[$i] ?? null;
				$experienceRaw	= 	$request->experiences[$i] ?? null;
				$levelRaw		= 	$request->exp_levels[$i] ?? null;
				$deploymentRaw	= 	$request->deploymenttypes[$i] ?? null;
				$startRaw 		= 	$request->startDate[$i] ?? null;
				$endRaw   		= 	$request->endDate[$i] ?? null;

				if($roleRaw==null || $nameRaw==null || $experienceRaw==null || $levelRaw==null || $deploymentRaw==null || $startRaw==null || $endRaw==null)
				{
					return response()->json([
						'status' => false,
						'errors' => [
							"roles.$i" => [
								"Please fill all fields OR remove the row."
							]
						]
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
	
		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		$notesheet		= 	$request->file('notesheet');
		$signedcopy		= 	$request->file('signedcopy');
		
		try
		{
			
			DB::beginTransaction();
			if($request->hasFile('notesheet'))
			{
				$notesheet	=	$request->file('notesheet')->store('uploads/notesheets','public');
			}
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	=	$request->file('signedcopy')->store('uploads/signedorder','public');
			}
			
	
			$pricing=	DB::table('pricing_tbl')
						->where('categoryid',$vendor->categoryid)
						->where('tierid',$vendor->tierid)
						->first();
			
			$copyto	=	$vendor->companyname.", ".$vendor->officelocation;			

			$department	=	DB::table('department_tbl')->where('departmentid',$validatedData['departmentid'])->first();
			
			$orderid=	DB::table('eoi_work_order')->insertGetId([
							'categoryid'		=>	$vendor->categoryid,
							'ordernumber'		=>	$validatedData['ordernumber'] ?? NULL,
							'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['order_date'])),
							'subject'			=>	$validatedData['subject'] ?? NULL,
							'vendorid'			=>	$vendor->vendorid,	
							'tierid'			=>	$vendor->tierid,	
							'creationdate'		=>	now(),
							'createdby'			=>	$userId,
							'operatingmargin'	=>	$pricing->operatingmargin,
							'tax'				=>	$pricing->tax,
							'admincharge'		=>	$pricing->admincharge,
							'notesheet'			=>	$notesheet,
							'signedcopy'		=>	$signedcopy,
							'signedby'			=>	$validatedData['signedby'],
							'loinumber'			=>	$vendor->loinumber ?? NULL,
							'copyto'			=>	$copyto ?? NULL,
							'workorderduedate'	=>	date('Y\-m\-d',strtotime($validatedData['order_duedate'])),
							'isfinalized'		=>	1,
							'finalizedby'		=>	$userName,
							'userid'			=>	$department->userid ?? NULL,
							'project_duration'	=>	$maxDuration,
							'department_id'		=>	$department->departmentid,
							'project_name'		=>	$validatedData['project_title'] ?? NULL
						]);
			

			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			if($vendor->categoryid==2)
			{
				$budget				=	0;
				$totalremuneration	=	0;
				$totalbudget		=	0;
				
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
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('rateid',$validatedData['rateid'])
									->first();

					$start 		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 		= 	$start->diffInDays($end);
					$duration 	= 	round($days / 30, 1);
					
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
						'sectorid'		=>	$sectorid,
						'positionid'	=>	$positionid,
						'remuneration'  => 	$price->remuneration,
						'operating'     => 	$pricing->operatingmargin,
						'tax'           => 	$pricing->tax,
						'admin'  	 	=> 	$pricing->admincharge,
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
						'deploymenttype'     => null,
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
						'deploymenttype'	 => $deployment ?? NULL
					];

					DB::table('eoi_resource_deployment')->insert($data);				

				}
			}
			if($vendor->categoryid==1)
			{
				$budget				=	0;
				$totalremuneration	=	0;
				$totalbudget		=	0;
				
				foreach(($request->roles ?? []) as $i => $id)
				{
					$role 		= 	$request->roles[$i];
					$name 		= 	$request->resource_names[$i];
					$experience = 	$request->experiences[$i];
					$explevel 	= 	$request->exp_levels[$i];
					$deployment = 	$request->deploymenttypes[$i];
					$startDate 	= 	$request->startDate[$i];
					$endDate 	= 	$request->endDate[$i];
					$price		=	DB::table('remuneration_tbl')
									->where('experiencelevel',$explevel)
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('rateid',$validatedData['rateid'])
									->first();

					$start 		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$startDate);
					$end   		= 	\Carbon\Carbon::createFromFormat('d-m-Y',$endDate);
					$days 		= 	$start->diffInDays($end);
					$duration 	= 	round($days / 30, 1);
					
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
						'name'			=>	$name ?? NULL,
						'mobilenumber'	=>	NULL,
						'email'			=>	NULL,
					]);
					DB::table('resource_tbl')->insert([
						'orderid'		=>	$orderid,
						'userid'		=>	$user_id ?? NULL,
						'categoryid'	=>	$vendor->categoryid,
						'tierid'		=>	$vendor->tierid,
						'experiencelevel'=>	$explevel,
						'remuneration'  => 	$price->remuneration,
						'operating'     => 	$pricing->operatingmargin,
						'tax'           => 	$pricing->tax,
						'admin'  	 	=> 	$pricing->admincharge,
						'rateid'		=> $validatedData['rateid'] ?? 0,
						'deployment_status'	=>	'Pending'
					]);

					$data = [
						'orderid'            => $orderid,
						'userid'			 => $user_id ?? 0,
						'recordid'           => 0,
						'deployment_status'  => 'Pending',
						'tierid'             => $vendor->tierid,
						'role'               => $role,
						'name'         		 => $name,
						'experience'         => $experience,
						'experiencelevel'    => $explevel,
						'duration'           => $duration,
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
						'deploymenttype'	 => $deployment ?? NULL
					];

					DB::table('eoi_resource_deployment')->insert($data);				

				}
			}
	
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
					'redirect_url'	=>	url('/master/directorderconfirmation/'.$orderid)
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

	public function confirmDirectOrder($orderid)
	{
		$order 	= 	DB::table('eoi_work_order as a')
					->select('a.ordernumber','a.orderdate','a.project_duration','b.companyname','b.vendorid','b.shortname','b.officelocation','a.signedcopy')
					->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->where('a.orderid', $orderid)
					->first();
	
		return view('admin/master/confirm_directorder', compact('order'));
	}
    
}
