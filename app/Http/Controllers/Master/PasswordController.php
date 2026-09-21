<?php

namespace App\Http\Controllers\Master;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
class PasswordController extends Controller
{
    protected $passwordService;

    public function __construct(PasswordService $passwordService)
    {
        $this->passwordService = $passwordService;
    }
	public function storePassword(Request $request)
	{
        $rules = [
			'old_password'	=>	'required',
			'user_password' => 'required|min:8|max:25|confirmed|regex:/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*#?&=_])[A-Za-z\d@$!%*#?&=_]+$/',
        ];

        $messages = [
			'old_password.required'		=> 	'Please provide old password',
			'user_password.required'	=> 	'Please provide password',
			'user_password.confirmed'	=> 	'Both password must be same',
			'user_password.min' 		=> 	'Password must be at least 8 characters long.',
			'user_password.max' 		=> 	'Maximum length is 25',
			'user_password.regex'		=>	'Password must start with a letter and include at least one number and one special character.',
        ];

		$validatedData	=	$request->validate($rules, $messages);
		try
		{
			$userId	=	$request->session()->get('userId');
			
			if(!session('subUserId'))
			{
				DB::table('users_tbl')
				->where('userid',$userId)
				->update([
					'password'	=>	Hash::make($validatedData['user_password']),
					'pass_word'	=>	$validatedData['user_password']
				]);
				return back()->with('success','Password updated successfully.');
			}
			else
			{
				DB::table('users_tbl')
				->where('userid',session('subUserId'))
				->update([
					'password'	=>	Hash::make($validatedData['user_password']),
					'pass_word'	=>	$validatedData['user_password']
				]);
				return back()->with('success','Password updated successfully.');				
			}
		}
		catch(Exception $e)
		{
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
		
		
	}
    public function changePassword(Request $request)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token',$token);
		
		return view('admin/master/change_password',compact('token'));
    }
	
    public function generatePassword()
    {
        //$users = DB::table('users_tbl')->get();
		
		$users = [];//DB::table('users_tbl')->where('userid','=',56)->where('isvendor',1)->get();

        foreach ($users as $user)
		{
            $plainPassword = $this->passwordService->generatePassword();
            $hashedPassword = Hash::make($plainPassword); // Encrypt password
            
			
			DB::table('users_tbl')->where('userid',$user->userid)->update([
				'pass_word'		=>	$plainPassword,
				'password'		=>	$hashedPassword
			]);

        }

        return redirect('dashboard')->with('success','Passwords updated successfully.');
    }	
    public function resetDeployment()
    {
		$totalremuneration	=	0;
		$totalbudget		=	0;
		$totaladmincharge	=	0;
		$totaloperatingcost	=	0;
		$totaltaxcost		=	0;
		$workorderamount	=	0;
		$eois	=	DB::table('eoi_request')->where('isordered',1)->get();
		foreach($eois as $eoi)
		{	
			$order	=	DB::table('eoi_work_order')->where('requestid',$eoi->requestid)->first();
			if($order)
			{
				$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
				if($vendor)
				{
					$pricing	=	DB::table('pricing_tbl')->where('categoryid',$vendor->categoryid)->where('tierid',$vendor->tierid)->first();
					
					$records	=	DB::table('eoi_request_detail as a')
									->select('a.*','b.remuneration')
									->leftjoin('remuneration_tbl as b','b.remunerationid','=','a.remunerationid')
									->where('a.requestid',$eoi->requestid)
									->get();
					
					foreach($records as $record)
					{
						$remuneration		=	$record->remuneration;
						$total				=	$record->remuneration*$record->duration;
						$operatingvalue		=	round(($total*$pricing->operatingmargin)/100,2);

						$total				=	$total+$operatingvalue;
						$taxvalue			=	round(($total*$pricing->tax)/100,2);
						$total				=	$total+$taxvalue;
						$adminvalue			=	round(($total*$pricing->admincharge)/100,2);
						$grandtotal			=	$record->remuneration*$record->duration+$operatingvalue+$taxvalue+$adminvalue;
						
						$totaloperatingcost	=	$totaloperatingcost+$operatingvalue;
						$totaltaxcost		=	$totaltaxcost+$taxvalue;
						$totaladmincharge	=	$totaladmincharge+$adminvalue;
						
						$totalremuneration	=	$totalremuneration+$record->remuneration*$record->duration;
						$exists				=	DB::table('eoi_resource_deployment')
												->where('recordid',$record->recordid)
												->first();
						if($exists)
						{
							DB::table('eoi_resource_deployment')
							->where('recordid',$record->recordid)
							->update([
								'orderid'			=>	$order->orderid,
								'tierid'			=>	$record->tierid,
								'sectorid'			=>	$record->sectorid,
								'positionid'		=>	$record->positionid,
								'role'				=>	$record->role,
								'experience'		=>	$record->experience,
								'experiencelevel'	=>	$record->experiencelevel,
								'qualification'		=>	$record->qualification,
								'deploymenttype'	=>	$record->employmenttype,
								'duration'			=>	$record->duration,
								'remark'			=>	$record->remark,
								'remunerationid'	=>	$record->remunerationid,
								'remuneration'		=>	$record->remuneration,
								'baseprice'			=>	$record->remuneration,
								'operating'			=>	$pricing->operatingmargin,
								'operatingvalue'	=>	$operatingvalue,
								'tax'				=>	$pricing->tax,
								'taxvalue'			=>	$taxvalue,
								'admincharge'		=>	$pricing->admincharge,
								'adminvalue'		=>	$adminvalue,
								'grandtotal'		=>	$grandtotal
							]);
						}
						else
						{
							DB::table('eoi_resource_deployment')
							->insert([
								'orderid'			=>	$order->orderid,
								'recordid'			=>	$record->recordid,
								'tierid'			=>	$record->tierid,
								'sectorid'			=>	$record->sectorid,
								'positionid'		=>	$record->positionid,
								'role'				=>	$record->role,
								'experience'		=>	$record->experience,
								'experiencelevel'	=>	$record->experiencelevel,
								'qualification'		=>	$record->qualification,
								'deploymenttype'	=>	$record->employmenttype,
								'duration'			=>	$record->duration,
								'remark'			=>	$record->remark,
								'remunerationid'	=>	$record->remunerationid,
								'remuneration'		=>	$record->remuneration,
								'baseprice'			=>	$record->remuneration,
								'operating'			=>	$pricing->operatingmargin,
								'operatingvalue'	=>	$operatingvalue,
								'tax'				=>	$pricing->tax,
								'taxvalue'			=>	$taxvalue,
								'admincharge'		=>	$pricing->admincharge,
								'adminvalue'		=>	$adminvalue,
								'grandtotal'		=>	$grandtotal
							]);							
						}
						
					}
					
					$totalbudget	=	$totalremuneration+$totaloperatingcost+$totaltaxcost+$totaladmincharge;
					
					$department_id	=	DB::table('department_tbl')->where('userid',$eoi->userid)->value('departmentid');
					
					DB::table('eoi_work_order')
					->where('orderid',$order->orderid)
					->update([
						'totalremuneration'		=>	$totalremuneration,
						'totalbudget'			=>	$totalbudget,
						'totaladmincharge'		=>	$totaladmincharge,
						'totaloperatingcost'	=>	$totaloperatingcost,
						'workorderamount'		=>	$totalbudget,
						'userid'				=>	$eoi->userid,
						'project_type'			=>	$eoi->project_type,
						'project_duration'		=>	$eoi->projectduration,
						'project_name'			=>	$eoi->projecttitle,
						'department_id'			=>	$department_id
					]);
				}
			}
			
		}
        return redirect('dashboard')->with('success','Resource detail updated successfully.');
    }	

    public function approveDeployment()
    {
		
		$deployments = DB::table('eoi_resource_deployment')
						->whereNotNull('deployment_date')
						->where('is_verified',0)
						->where('deployment_status','Pending')
						->get();

        foreach($deployments as $deployment)
		{
			DB::table('eoi_resource_deployment')
			->where('deploymentid',$deployment->deploymentid)
			->update([
				'deployed_date' 	=> 	$deployment->deployment_date,
				'deployment_status'	=>	'Active',
				'is_verified'		=>	1,
				'verified_on'		=>	now()
				
			]);
        }

        return redirect('dashboard')->with('success','Deployment date updated successfully.');
    }	

    public function setOrderIssuanceDate()
    {
		
		$orders	=	DB::table('eoi_work_order')->get();

        foreach($orders as $order)
		{
			DB::table('eoi_resource_deployment')
			->where('orderid',$order->orderid)
			->update([
				'deployment_date'	=>	$order->orderdate,
			]);
        }

        return redirect('dashboard')->with('success','Deployment date updated successfully.');
    }	


    public function setNewOrderValue()
    {
		$orders	=	DB::table('eoi_work_order as a')
					->select('a.*','b.tierid')
					->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->get();
		foreach($orders as $order)
		{
			
			if($order->categoryid==1)
			{
				$totalremuneration		=	0;
				$operatingcost			=	0;
				$withoperating			=	0;
				$taxvalue				=	0;
				$workorderamount		=	0;
				$resources				=	DB::table('eoi_resource_deployment as a')
											->select('b.remuneration')
											->join('remuneration_tbl as b', function ($join) {
												$join->on('b.experiencelevel','=','a.experiencelevel')
													 ->on('b.tierid','=','a.tierid');
											})
											->where('a.orderid', $order->orderid)
											->where('b.categoryid',$order->categoryid)
											->get();
										
				foreach($resources as $resource)
				{
					$totalremuneration	=	$totalremuneration+$resource->remuneration;
				}
				$totalremuneration	=	$totalremuneration*$order->project_duration;
				
				$operatingcost		=	(($totalremuneration*$order->operatingmargin)/100);
				
				$withoperating		=	$totalremuneration+$operatingcost;
				$taxvalue			=	(($withoperating*$order->tax)/100);
				
				$workorderamount	=	$withoperating+$taxvalue;
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'totalremuneration'		=>	$totalremuneration,
					'totalbudget'			=>	$workorderamount,
					'totaladmincharge'		=>	0,
					'totaloperatingcost'	=>	$operatingcost,
					'workorderamount'		=>	$workorderamount
				]);
			}
			
			if($order->categoryid==2)
			{
				$totalremuneration		=	0;
				$includingtax			=	0;
				$totalbudget			=	0;
				$totaladmincharge		=	0;
				$workorderamount		=	0;
				$resources				=	DB::table('eoi_resource_deployment as a')
											->select('b.remuneration')
											->join('remuneration_tbl as b', function ($join) {
												$join->on('b.sectorid','=','a.sectorid')
													 ->on('b.positionid','=','a.positionid')
													 ->on('b.tierid','=','a.tierid');
											})
											->where('a.orderid', $order->orderid)
											->where('b.categoryid',$order->categoryid)
											->get();
										
				foreach($resources as $resource)
				{
					$totalremuneration	=	$totalremuneration+$resource->remuneration;
				}
				$totalremuneration	=	$totalremuneration*$order->project_duration;
				$includingtax		=	$totalremuneration+(($totalremuneration*$order->tax)/100);
				$totaladmincharge	=	(($includingtax*$order->admincharge)/100);
				$workorderamount	=	$includingtax+$totaladmincharge;
				
				DB::table('eoi_work_order')
				->where('orderid',$order->orderid)
				->update([
					'totalremuneration'		=>	$totalremuneration,
					'totalbudget'			=>	$workorderamount,
					'totaladmincharge'		=>	$totaladmincharge,
					'workorderamount'		=>	$workorderamount
				]);
			}
		}

        return redirect('dashboard')->with('success','Order Value Updated Successfully.');
    }	
	

    public function addResources()
    {
		DB::beginTransaction();
		$resources	=	DB::table('eoi_resource_deployment')->where('userid',0)->whereNotNull('deployed_date')->get();
		$categoryid	=	0;
		$i=0;
		foreach($resources as $resource)
		{
			$i++;
			if($resource->sectorid!=0)
			$categoryid	=	2;
			else
			$categoryid	=	1;
		
            $plainPassword	= 	$this->passwordService->generatePassword();
            $hashedPassword =	Hash::make($plainPassword);
			
			$userid	=	DB::table('users_tbl')->insertGetId([
							'name'			=>	$resource->name ?? '',
							'mobilenumber'	=>	$resource->mobilenumber ?? NULL,
							'email'			=>	'empl_portal'.$i.'@gmail.com',
							'password'		=>	$hashedPassword,
							'pass_word'		=>	$plainPassword,
							'isactive'		=>	1,
							'issuper'		=>	1,
							'isresource'	=>	1,
							'usertype'		=>	'RESOURCE',
							'created_at'	=>	now()
						]);
						
			$taxvalue		=	0;
			$operatingvalue	=	0;
			$admincharge	=	0;
			if($categoryid==1)
			{
				$taxvalue	=	($resource->remuneration*$resource->tax)/100;
				$admincharge=	(($resource->remuneration+$taxvalue)*$resource->admincharge)/100;
				$totalvalue	=	$resource->remuneration+$taxvalue+$admincharge;
			}
			if($categoryid==2)
			{
				$operatingvalue	=	($resource->remuneration*$resource->operating)/100;
				$taxvalue		=	(($resource->remuneration+$operatingvalue)*$resource->tax)/100;
				$totalvalue		=	$resource->remuneration+$operatingvalue+$taxvalue;
			}
			
			DB::table('resource_tbl')->insert([
				'orderid'			=>	$resource->orderid,
				'userid'			=>	$userid,
				'categoryid'		=>	$categoryid,
				'tierid'			=>	$resource->tierid,
				'sectorid'			=>	$resource->sectorid,
				'positionid'		=>	$resource->positionid,
				'role'				=>	$resource->role,
				'experiencelevel'	=>	$resource->experiencelevel,
				'remuneration'		=>	$resource->remuneration,
				'operating'			=>	$resource->operating,
				'operatingvalue'	=>	$operatingvalue,
				'tax'				=>	$resource->tax,
				'taxvalue'			=>	$taxvalue,
				'admin'				=>	$resource->admincharge,
				'admincharge'		=>	$admincharge,
				'totalvalue'		=>	$totalvalue,
				'deployment_date'	=>	$resource->deployment_date,
				'deployed_date'		=>	$resource->deployed_date,
				'deployment_status'	=>	$resource->deployment_status,
				'deploymenttype'	=>	$resource->deploymenttype,
				'duration'			=>	$resource->duration,
				'is_verified'		=>	$resource->is_verified,
				'verified_by'		=>	$resource->verified_by,
				'verified_on'		=>	$resource->verified_on,
				'lastdate'			=>	$resource->endDate,
			]);
			
			DB::table('eoi_resource_deployment')
			->where('deploymentid',$resource->deploymentid)
			->update([
				'userid'	=>	$userid
			]);
		}
		DB::commit();
        return redirect('dashboard')->with('success','Resource details created successfully.');
    }	
	

    public function UpdateRemunerationPrice()
    {
		DB::beginTransaction();
		$categoryid		=	1;
		$remunerations	=	DB::table('remuneration_tbl')->where('categoryid',$categoryid)->get();
		$percentage		=	0;
		$i=0;
		foreach($remunerations as $remuneration)
		{
			$i++;
			if($remuneration->categoryid==2)
			$percentage	=	5;
			else
			$percentage	=	8;
			
			$updated	=	round($remuneration->remuneration+(($remuneration->remuneration*$percentage)/100),2);
			
			DB::table('remuneration_tbl')
			->where('remunerationid',$remuneration->remunerationid)
			->update([
				'remuneration'		=>	$updated,
				'oldremuneration'	=>	$remuneration->remuneration
			]);
		}
		DB::commit();
        return redirect('dashboard')->with('success','Resource details created successfully.');
    }	

    public function UpdateResourcePrice()
    {
		DB::beginTransaction();
		
		$orders		=	DB::table('eoi_work_order')
						->whereDate('workorderduedate','>','2026-05-31')
						->where('isActiveOrder',1)
						->where('isExtended',0)
						->where('isCancelled',0)
						->where('isClosed',0)
						->get();
		
		foreach($orders as $order)
		{
			$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

			$rateid	=	DB::table('remuneration_rate_list')
						->where('categoryid',$vendor->categoryid)
						->where('isactive',1)
						->value('rateid');
			
			
			$pricing=	DB::table('pricing_tbl')
						->where('categoryid',$vendor->categoryid)
						->where('tierid',$vendor->tierid)
						->first();
			
			
			$order->resources	=	DB::table('eoi_resource_deployment')
									->whereNotIn('deployment_status',['Released','Extended','Cancelled'])
									->where('orderid',$order->orderid)
									->where('isClosed',0)
									->where('isExtended',0)
									->whereNull('lastdate')
									->get();
			
			foreach($order->resources as $resource)
			{
				if($vendor->categoryid==2)
				{
					$price	=	DB::table('remuneration_tbl')
								->where('rateid',$rateid)
								->where('categoryid',$vendor->categoryid)
								->where('tierid',$vendor->tierid)
								->where('sectorid',$resource->sectorid)
								->where('positionid',$resource->positionid)
								->first();
				}
				if($vendor->categoryid==1)
				{
					$price	=	DB::table('remuneration_tbl')
								->where('rateid',$rateid)
								->where('categoryid',$vendor->categoryid)
								->where('tierid',$vendor->tierid)
								->where('experiencelevel',$resource->experiencelevel)
								->first();
				}
				
				DB::table('eoi_resource_deployment')
				->where('deploymentid',$resource->deploymentid)
				->update([
					'remunerationid'	=>	$price->remunerationid,
					'remuneration'		=>	$price->remuneration,
					'baseprice'			=>	$price->remuneration,
					'operating'			=>	$pricing->operatingmargin,
					'tax'				=>	$pricing->tax,
					'admincharge'		=>	$pricing->admincharge,
				]);
				
				if($resource->userid!=NULL && $resource->userid>0)
				{
					DB::table('resource_tbl')
					->where('userid',$resource->userid)
					->update([
						'sectorid'			=>	$resource->sectorid,
						'positionid'		=>	$resource->positionid,
						'role'				=>	$resource->role ?? NULL,
						'experiencelevel'	=>	$resource->experiencelevel,
						'remuneration'		=>	$price->remuneration,
						'operating'			=>	$pricing->operatingmargin,
						'tax'				=>	$pricing->tax,
						'admin'				=>	$pricing->admincharge,
					]);
				}
			}
		}
		
		DB::commit();
        return redirect('dashboard')->with('success','Resource details created successfully.');
    }	

}
