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

class ExtensionController extends Controller
{
	public function extendWorkOrder(Request $request, $orderid = NULL)
	{
		$rules = [
			'vendorid'         => 'required',
			'rateid'           => 'required|numeric',
			'departmentid'     => 'required|numeric',
			'ordernumber'      => 'required',
			'subject'          => 'required',
			'signedby'         => 'required|in:Chief Executive Officer (CEO),Jt. CEO (Project),Jt. CEO(Finance),Add. Chief Executive Officer,Chief Operating Officer',
			'notesheet'        => 'required|file|mimetypes:application/pdf|max:5120',
			'signedcopy'       => 'required|file|mimetypes:application/pdf|max:5120',
			'order_date'       => 'required|date_format:d-m-Y',
			'order_duedate'    => 'required|date_format:d-m-Y',
		];

		$messages = [
			'rateid.required'              => 'Rate list name is mandatory',
			'rateid.numeric'               => 'Invalid rate list value provided',

			'departmentid.required'        => 'Department / Project Manager name is mandatory',
			'departmentid.numeric'         => 'Invalid department / project manager value provided',

			'vendorid.required'            => 'Invalid vendor',

			'ordernumber.required'         => 'Order number is required',

			'subject.required'             => 'Work order subject is required',

			'signedby.required'            => 'Signed by name is required',

			'notesheet.required'           => 'Note sheet is required',
			'notesheet.file'               => 'Note sheet must be a file',
			'notesheet.mimetypes'          => 'Invalid note sheet file attached',
			'notesheet.max'                => 'Maximum file size is 5 MB',

			'signedcopy.required'          => 'Work order signed copy is required',
			'signedcopy.file'              => 'Work order signed copy must be a file',
			'signedcopy.mimetypes'         => 'Invalid signed copy file attached',
			'signedcopy.max'               => 'Maximum file size is 5 MB',

			'order_date.required'          => 'Order date is required',
			'order_date.date_format'       => 'Invalid order date provided',

			'order_duedate.required'       => 'Order due date is required',
			'order_duedate.date_format'    => 'Invalid order due date provided',
		];

		$validatedData = $request->validate($rules, $messages);

		$orderid = 	Crypt::decrypt($orderid);
		$orderIdsArray 	= 	[];
		$orderIdsArray[]= 	$orderid;
		$notesheetPath	= 	'';
		$signedcopyPath =	'';

		try
		{
			$mappedSectors = DB::table('vendor_sector')->where('vendorid', $validatedData['vendorid'])->pluck('sectorid')->toArray();
			foreach($request->sectorids ?? [] as $i => $sectorid)
			{
				if(!in_array($sectorid, $mappedSectors))
				{
					return response()->json([
						'status' => false,
						'errors' => [
							"sectorids.$i" => [
								'Selected sector is not mapped with the selected firm'
							]
						]
					], 422);
				}
			}
			DB::beginTransaction();
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$validatedData['vendorid'])->first();
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
							
			$oldOrder	=	DB::table('eoi_work_order')->where('orderid', $orderid)->first();

			if(!$oldOrder)
			{
				return response()->json([
					'errors' => [
						'error' => ['Order not found']
					]
				], 422);
			}

			/* Upload Files */

			$notesheetPath = $request->file('notesheet')->store('workorders', 'public');

			$signedcopyPath = $request->file('signedcopy')->store('workorders', 'public');

			/* Create New Work Order */

			$newOrderId	=	DB::table('eoi_work_order')->insertGetId([

				'parentorderid' 	=>	$oldOrder->orderid,
				'rootorderid'   	=> 	$oldOrder->rootorderid ? $oldOrder->rootorderid : $oldOrder->orderid,
				'requestid'     	=> 	$oldOrder->requestid,
				'categoryid'    	=> 	$oldOrder->categoryid,
				'subject'       	=> 	$validatedData['subject'],
				'ordernumber'   	=> 	$validatedData['ordernumber'],
				'orderdate'     	=> 	date('Y-m-d',strtotime($validatedData['order_date'])),
				'refrence'			=> 	$oldOrder->refrence,
				'agreementdate'		=>	$oldOrder->agreementdate,
				'tendernumber'		=>	$oldOrder->tendernumber,
				'participationid'	=>	$oldOrder->participationid,
				'floatid'			=>	$oldOrder->floatid,
				'vendorid'      	=> 	$vendor->vendorid,
				'tierid'        	=> 	$vendor->tierid,
				'creationdate'  	=> 	now(),
				'createdby'     	=> 	session('userId'),
				'loinumber'			=>	$oldOrder->loinumber,
				'project_name'		=>	$oldOrder->project_name,
				'subuserid'			=>	$oldOrder->subuserid,
				'department_id' 	=> 	$validatedData['departmentid'],
				'workorderduedate' 	=> 	date('Y-m-d',strtotime($validatedData['order_duedate'])),
				'signedby'         	=> 	$validatedData['signedby'],
				'notesheet'        	=> 	$notesheetPath,
				'signedcopy'       	=> 	$signedcopyPath,
				'isActiveOrder'    	=> 	1,
				'operatingmargin'	=>	$pricing->operatingmargin ?? 0,
				'tax'				=>	$pricing->tax ?? 0,
				'admincharge'		=>	$pricing->admincharge ?? 0,
				'userid'			=>	$oldOrder->userid,
			]);

			/* Existing Resources */

			$existingResources = $request->existing ?? [];

			foreach($existingResources as $resource)
			{
				$deploymentId = $resource['deployment_id'] ?? 0;
				if(!$deploymentId)
				{
					continue;
				}

				/* Release Resource */

				if(!empty($resource['release']))
				{
					DB::table('eoi_resource_deployment')
						->where('deploymentid', $deploymentId)
						->update([
							'deployment_status' => 'Released',
							'isExtended'        => 0
						]);

					continue;
				}

				/* Extend Resource */

				if(empty($resource['extend']))
				{
					continue;
				}

				$oldDeployment	=	DB::table('eoi_resource_deployment')->where('deploymentid', $deploymentId)->first();

				if (!$oldDeployment)
				{
					continue;
				}

				$startDate = !empty($resource['start_date'])
					? date('Y-m-d', strtotime($resource['start_date']))
					: date('Y-m-d', strtotime($validatedData['order_date']));

				$endDate = !empty($resource['end_date'])
					? date('Y-m-d', strtotime($resource['end_date']))
					: date('Y-m-d', strtotime($validatedData['order_duedate']));

				/* Update Old Deployment */

				DB::table('eoi_resource_deployment')
					->where('deploymentid', $deploymentId)
					->update([
						'deployment_status' => 'Extended',
						'isExtended'        => 1
					]);

				/* Insert New Extended Deployment */
				$remuneration	=	DB::table('remuneration_tbl')
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('rateid',$validatedData['rateid'])
									->when($oldDeployment->sectorid!='0',function($query) use($oldDeployment){
										return $query->where('sectorid',$oldDeployment->sectorid);
									})
									->when($oldDeployment->positionid!='0',function($query) use($oldDeployment){
										return $query->where('positionid',$oldDeployment->positionid);
									})
									->when($oldDeployment->experiencelevel!='0',function($query) use($resource){
										return $query->where('experiencelevel',$resource['experience_level']);
									})
									->first();
									
				DB::table('eoi_resource_deployment')->insert([
					'orderid'           => 	$newOrderId,
					'parentdeploymentid'=> 	$oldDeployment->deploymentid,
					'recordid'          => 	$oldDeployment->recordid,
					'userid'            => 	$oldDeployment->userid,
					'name'              => 	$oldDeployment->name,
					'sectorid'          => 	$oldDeployment->sectorid,
					'positionid'        => 	$oldDeployment->positionid,
					'role'              => 	$oldDeployment->role,
					'experience'        => 	$oldDeployment->experience,
					'experiencelevel'   => 	$resource['experience_level'] ?? $oldDeployment->experiencelevel,
					'deploymenttype'    => 	$resource['deploymenttype'] ?? $oldDeployment->deploymenttype,
					'remuneration'      => 	$oldDeployment->remuneration,
					'rateid'            => 	$validatedData['rateid'],
					'deployment_date'   => 	$startDate,
					'deployed_date'     => 	$startDate,
					'startDate'         => 	$startDate,
					'endDate'           => 	$endDate,
					'deployment_status' => 	'Active',
					'isExtended'        => 	0,
					'employee_code'		=>	$oldDeployment->employee_code,
					'mobilenumber'		=>	$oldDeployment->mobilenumber,
					'email'				=>	$oldDeployment->email,
					'is_verified'		=>	$oldDeployment->is_verified,
					'verified_by'		=>	$oldDeployment->verified_by,
					'verified_on'		=>	$oldDeployment->verified_on,
					'tierid'			=>	$vendor->tierid,
					'qualification'		=>	$oldDeployment->qualification,
					'remunerationid'	=>	$remuneration->remunerationid,
					'remuneration'		=>	$remuneration->remuneration,
					'baseprice'			=>	$remuneration->remuneration,
					'operating'			=>	$pricing->operatingmargin ?? 0,
					'tax'				=>	$pricing->tax ?? 0,
					'admincharge'		=>	$pricing->admincharge ?? 0
					
				]);
			}

			/* Merge Resources */

			$mergeResources = $request->merge ?? [];

			foreach($mergeResources as $resource)
			{
				if(empty($resource['selected']))
				{
					continue;
				}

				$deploymentId = $resource['deployment_id'] ?? 0;

				if(!$deploymentId)
				{
					continue;
				}

				$oldDeployment = DB::table('eoi_resource_deployment')->where('deploymentid', $deploymentId)->first();

				if(!$oldDeployment)
				{
					continue;
				}
				$orderIdsArray[] = $oldDeployment->orderid;
				DB::table('eoi_resource_deployment')
					->where('deploymentid', $deploymentId)
					->update([
						'deployment_status' => 'Extended',
						'isExtended'        => 1
					]);


				DB::table('eoi_resource_deployment')->insert([
					'orderid'              	=> 	$newOrderId,
					'parentdeploymentid'	=> 	$oldDeployment->deploymentid,
					'recordid'             	=> 	$oldDeployment->recordid,
					'userid'               	=> 	$oldDeployment->userid,
					'name'                 	=> 	$oldDeployment->name,
					'sectorid'             	=> 	$oldDeployment->sectorid,
					'positionid'           	=> 	$oldDeployment->positionid,
					'role'                 	=> 	$oldDeployment->role,
					'experience'           	=> 	$oldDeployment->experience,
					'experiencelevel'      	=> 	$resource['experiencelevel'] ?? $oldDeployment->experiencelevel,
					'deploymenttype'       	=> 	$resource['deploymenttype'] ?? $oldDeployment->deploymenttype,
					'startDate'            	=> 	!empty($resource['start_date']) ? date('Y-m-d', strtotime($resource['start_date'])) : null,
					'endDate'              	=> 	!empty($resource['end_date']) ? date('Y-m-d', strtotime($resource['end_date'])) : null,
					'deployment_status'    	=> 	'Active',
					'isExtended'           	=> 	0,
					'rateid'			   	=> 	$validatedData['rateid'],
					'deployment_date'   	=> 	!empty($resource['start_date']) ? date('Y-m-d', strtotime($resource['start_date'])) : null,
					'deployed_date'     	=> 	!empty($resource['start_date']) ? date('Y-m-d', strtotime($resource['start_date'])) : null,
					'employee_code'			=>	$oldDeployment->employee_code,
					'mobilenumber'			=>	$oldDeployment->mobilenumber,
					'email'					=>	$oldDeployment->email,
					'is_verified'			=>	$oldDeployment->is_verified,
					'verified_by'			=>	$oldDeployment->verified_by,
					'verified_on'			=>	$oldDeployment->verified_on,
					'tierid'				=>	$oldDeployment->tierid,
					'qualification'			=>	$oldDeployment->qualification,
					'remunerationid'		=>	$oldDeployment->remunerationid,
					'remuneration'			=>	$oldDeployment->remuneration,
					'baseprice'				=>	$oldDeployment->remuneration,
					'operating'				=>	$pricing->operatingmargin ?? 0,
					'tax'					=>	$pricing->tax ?? 0,
					'admincharge'			=>	$pricing->admincharge ?? 0
				]);
			}

			/* Additional Awd Resources (Category 1) */

			foreach($request->roles ?? [] as $i => $role)
			{
				if(empty($role) && empty($request->resources[$i]))
				{
					continue;
				}
				$remuneration	=	DB::table('remuneration_tbl')
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('experiencelevel',$request->levels[$i])
									->where('rateid',$validatedData['rateid'])
									->first();
				
				DB::table('eoi_resource_deployment')->insert([
					'orderid'           => 	$newOrderId,
					'role'              =>	$role,
					'name'              => 	$request->resources[$i] ?? null,
					'experience'        => 	$request->experiences[$i] ?? null,
					'experiencelevel'   => 	$request->levels[$i] ?? null,
					'deploymenttype'    => 	$request->deploymenttypes[$i] ?? null,
					'deployment_date'   => 	!empty($request->startDate[$i]) ? date('Y-m-d', strtotime($request->startDate[$i])) : null,
					'startDate'         => 	!empty($request->startDate[$i]) ? date('Y-m-d', strtotime($request->startDate[$i])) : null,
					'endDate'           => 	!empty($request->endDate[$i]) ? date('Y-m-d', strtotime($request->endDate[$i])) : null,
					'deployment_status'	=> 	'Pending',
					'isExtended'        => 	0,
					'rateid'			=> 	$validatedData['rateid'],
					'tierid'			=>	$vendor->tierid,
					'remunerationid'	=>	$remuneration->remunerationid,
					'remuneration'		=>	$remuneration->remuneration,
					'baseprice'			=>	$remuneration->remuneration,
					'operating'			=>	$pricing->operatingmargin ?? 0,
					'tax'				=>	$pricing->tax ?? 0,
					'admincharge'		=>	$pricing->admincharge ?? 0
				]);
			}

			/* Additional Non-Awd Resources (Category 2) */

			foreach ($request->sectorids ?? [] as $i => $sectorid)
			{
				if(empty($sectorid) && empty($request->positionids[$i]))
				{
					continue;
				}
				$remuneration	=	DB::table('remuneration_tbl')
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('sectorid',$sectorid)
									->where('positionid',$request->positionids[$i])
									->where('rateid',$validatedData['rateid'])
									->first();

				DB::table('eoi_resource_deployment')->insert([
					'orderid'           => 	$newOrderId,
					'sectorid'          => 	$sectorid,
					'positionid'        => 	$request->positionids[$i] ?? null,
					'deploymenttype'    => 	$request->deploymenttypes[$i] ?? null,
					'deployment_date'   => 	!empty($request->startDate[$i]) ? date('Y-m-d', strtotime($request->startDate[$i])) : null,
					'startDate'         => 	!empty($request->startDate[$i]) ? date('Y-m-d', strtotime($request->startDate[$i])) : null,
					'endDate'           => 	!empty($request->endDate[$i]) ? date('Y-m-d', strtotime($request->endDate[$i])) : null,
					'deployment_status'	=> 	'Pending',
					'isExtended'        => 	0,
					'rateid'			=> 	$validatedData['rateid'],
					'tierid'			=>	$vendor->tierid,
					'remunerationid'	=>	$remuneration->remunerationid,
					'remuneration'		=>	$remuneration->remuneration,
					'baseprice'			=>	$remuneration->remuneration,
					'operating'			=>	$pricing->operatingmargin ?? 0,
					'tax'				=>	$pricing->tax ?? 0,
					'admincharge'		=>	$pricing->admincharge ?? 0
				]);
			}

			/* Disable Old Work Order */

			DB::table('eoi_work_order')
				->where('orderid', $orderid)
				->update([
					'isActiveOrder' => 0,
					'isExtended'    => 1
				]);
			
			/* Update New Order Merged Orderids if available */
			$orderIdsArray = array_filter(array_unique($orderIdsArray));
			if (!empty($orderIdsArray))
			{
				$orderids = implode(',', $orderIdsArray);
				
				DB::table('eoi_work_order')
				->where('orderid', $newOrderId)
				->update([
					'mergedOrderNumbers' => $orderids,
				]);
				
				DB::table('eoi_work_order')
				->whereIn('orderid',$orderIdsArray)
				->update([
					'isActiveOrder'		=>	0,
					'isExtended'    	=> 	1					
				]);
			}
			DB::commit();

			return response()->json([
				'status'  => 200,
				'message' => 'Work order extended successfully'
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();
			Log::error('Extend Work Order Error: '.$e->getMessage());

			if(!empty($notesheetPath))
			{
				Storage::disk('public')->delete($notesheetPath);
			}

			if(!empty($signedcopyPath))
			{
				Storage::disk('public')->delete($signedcopyPath);
			}
			
			return response()->json([
				'errors' => [
					'error' => [$e->getMessage()]
				]
			], 422);
		}
	}
	
    public function showExtendForm(Request $request,$orderid=NULL)
	{
		$orderid	=	Crypt::decrypt($orderid);
		$order		=	DB::table('eoi_work_order as a')
						->select('a.*','b.companyname','b.shortname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.orderid',$orderid)
						->first();
		
		$vendors	=	DB::table('vendor_tbl as a')
						->select('a.vendorid','a.companyname','a.shortname')
						->where('a.categoryid',$order->categoryid)
						->orderBy('a.companyname')
						->get();

		$rate	=	DB::table('remuneration_rate_list')->where('categoryid',$order->categoryid)->where('isactive',1)->first();

		if($order->categoryid==2)
		{
			$resources	=	DB::table('eoi_resource_deployment as a')
							->select(
								'a.deploymentid','a.name','a.qualification','a.experience','a.remark','a.deploymenttype','b.sectorname','c.consultantposition'
							)
							->leftJoin('sector_tbl as b', 'b.sectorid', '=', 'a.sectorid')
							->leftJoin('position_tbl as c', 'c.positionid', '=', 'a.positionid')
							->where('a.orderid','=',$orderid)
							->where('a.deployment_status','Active')
							->get();
		}
		if($order->categoryid==1)
		{
			$resources	=	DB::table('eoi_resource_deployment as a')
							->select(
								'a.deploymentid',
								'a.name',
								'a.qualification',
								'a.experience',
								'a.role',
								'a.remark',
								'a.deploymenttype',
								'a.experiencelevel'
							)
							->where('a.orderid', '=', $orderid)
							->where('a.deployment_status', 'Active')
							->get();
		}
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')
					->select('experiencelevel')
					->where('experiencelevel','!=',0)
					->where('rateid',$rate->rateid)
					->where('categoryid',$order->categoryid)
					->orderBy('experiencelevel')
					->distinct('experiencelevel')
					->get();

		$experiences=	DB::table('work_experience')
						->select('workexperience')
						->orderBy('experienceid')
						->get();
		
		$requestid	=	$order->requestid;

		if($requestid!=0)
		{
			$orders =	DB::table('eoi_work_order as a')
						->where('a.requestid', $order->requestid)
						->where('a.orderid', '!=', $order->orderid)
						->where('a.isActiveOrder', 1)
						->whereExists(function ($q) {
						$q->select(DB::raw(1))
						  ->from('eoi_resource_deployment as b')
						  ->whereColumn('b.orderid', 'a.orderid')
						  ->where('b.deployment_status','Active');
						})
						->orderBy('a.orderdate')
						->get();

			foreach($orders as $ord)
			{
				$query	=	DB::table('eoi_resource_deployment as a')
							->where('a.orderid', $ord->orderid)
							->where('a.deployment_status', 'Active');

				if($order->categoryid == 1)
				{
					$query->select(
						'a.deploymentid',
						'a.name',
						'a.qualification',
						'a.experience',
						'a.role',
						'a.remark',
						'a.deploymenttype',
						'a.experiencelevel'
					);

				}
				elseif($order->categoryid == 2)
				{
					$query->select(
						'a.deploymentid',
						'a.name',
						'a.qualification',
						'a.experience',
						'a.remark',
						'a.deploymenttype',
						'b.sectorname',
						'c.consultantposition'
					)
					->leftJoin('sector_tbl as b', 'b.sectorid', '=', 'a.sectorid')
					->leftJoin('position_tbl as c', 'c.positionid', '=', 'a.positionid');
				}

				$ord->orderResources = $query->get();
			}
		}
		else
		{
			$orders		=	[];
		}
		
		$chain = [];
		while ($orderid != 0) {

			$row = DB::table('eoi_work_order as wo')
				->leftJoin('vendor_tbl as v', 'v.vendorid', '=', 'wo.vendorid')
				->select(
					'wo.orderid',
					'wo.parentorderid',
					'wo.ordernumber',
					'v.companyname',
					'wo.project_duration',
					'wo.orderdate',
					'wo.workorderduedate',
					'wo.signedcopy'
				)
				->where('wo.orderid', $orderid)
				->first();

			if (!$row) {
				break;
			}

			$chain[] = $row;

			$orderid = $row->parentorderid;
		}
		
		$previousExtensions = collect($chain)->sortBy('orderdate')->values();
		$previousExtensions = collect($chain)->sortBy('orderid')->values();
		
		return view('admin/master/extension_form',compact('requestid','order','resources','vendors','rate','sectors','positions','levels','experiences','orders','previousExtensions'));
	}
}
