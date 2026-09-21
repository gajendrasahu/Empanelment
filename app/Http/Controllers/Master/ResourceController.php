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
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Services\DeploymentDataService;
use App\Services\TierWiseDataService;
use App\Services\PasswordService;
use Illuminate\Support\Facades\Hash;
class ResourceController extends Controller
{
	protected $deploymentService;
	protected $priceService;
	protected $passwordService;
	public function __construct(DeploymentDataService $deploymentService,TierWiseDataService $priceService,PasswordService $passwordService)
	{
		$this->deploymentService=	$deploymentService;
		$this->priceService		=	$priceService;
		$this->passwordService 	= 	$passwordService;
	}
	
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if (strpos($number, '.') !== false) {
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree = substr($number, -3);
		$rest = substr($number, 0, -3);

		if ($rest != '') {
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}

    public function exportResourceData(Request $request)
	{
        $pagesearch   =	$request->pagesearch ?? '';
        $categoryid   = $request->categoryid ?? 0;
        $vendorid     = $request->vendorid ?? 0;
        $departmentid = $request->departmentid ?? 0;

        $fileName	=	'resource_export_'.date('Ymd_His').'.csv';

        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

		$callback = function () use ($pagesearch, $categoryid, $vendorid, $departmentid) {

			$handle = fopen('php://output','w');

			fputcsv($handle,[
				'S.No','Resource Name','Firm Name','Department / Project Manager','Project Name','EoI Number','Order Date','Order Number','Order Value','Duration (In months)','Position','Sector','Role','Deployed Date','Base Rate','Total Cost'
			]);

			$sno = 1;

			DB::table('eoi_resource_deployment as a')
				->select(
					'a.name','d.companyname','c.departmentname','b.project_name','b.refrence as eoinumber','b.orderdate',
					'b.ordernumber','b.workorderamount','a.duration','f.consultantposition',
					'e.sectorname','a.role','a.deployed_date','a.remuneration',
					DB::raw('(a.remuneration * a.duration) as total_cost')
				)
				->leftJoin('eoi_work_order as b','b.orderid','=','a.orderid')
				->leftJoin('department_tbl as c','c.departmentid','=','b.department_id')
				->leftJoin('vendor_tbl as d','d.vendorid','=','b.vendorid')
				->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
				->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
				->leftJoin('eoi_request_detail as g','g.recordid','=','a.recordid')
				->when($pagesearch != '', function ($query) use ($pagesearch) {
					$query->where(function ($q) use ($pagesearch) {
						$q->where('a.name', 'like', '%' . $pagesearch . '%')
						  ->orWhere('a.mobilenumber', 'like', '%' . $pagesearch . '%')
						  ->orWhere('a.email', 'like', '%' . $pagesearch . '%')
						  ->orWhere('a.role', 'like', '%' . $pagesearch . '%')
						  ->orWhere('b.project_name', 'like', '%' . $pagesearch . '%');
					});
				})				
				->when($categoryid != 0, fn($q) => $q->where('b.categoryid',$categoryid))
				->when($departmentid != 0, fn($q) => $q->where('b.department_id',$departmentid))
				->when($vendorid != 0, fn($q) => $q->where('b.vendorid',$vendorid))
				->where('a.deployment_status','Active')
				->where('a.isExtended',0)
				->whereNotNull('a.name')
				->orderBy('a.deployment_date')
				->chunk(500, function ($rows) use ($handle, &$sno) {
					foreach ($rows as $row) {
						$row->orderdate 	= "\t" . Carbon::parse($row->orderdate)->format('d-m-Y');
						$row->deployed_date	= "\t" . Carbon::parse($row->deployed_date)->format('d-m-Y');
						fputcsv($handle, array_merge([$sno++], (array) $row));
					}
				});

			fclose($handle);
		};
        return response()->stream($callback, 200, $headers);
    }

    public function resourceList(Request $request)
	{
        Session::put('adminmenu','resources');
		Session::put('adminsubmenu','resourceslist');
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderBy('categoryid')->orderBy('companyname')->get();
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$search	=	"";
        return view('admin/master/resource_list',compact('category','vendors','search','departments'));
    }

    public function getResourcesData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('categoryid') ?? 0;
		$departmentid 	=	$request->input('departmentid') ?? 0;
		$vendorid 		=	$request->input('vendorid') ?? 0;

		$pagesearch =	$request->input('pagesearch');
		
		$pagesize	=	$request->input('pagesize',300);
		$currentPage= 	$request->input('page', 1);
		/*
		$data 		= 	DB::table('eoi_resource_deployment as a')
							->select('a.userid as resourceid','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.remarks','a.is_verified','a.role','a.experience','a.experiencelevel','a.qualification','a.deploymenttype','a.remuneration','a.duration','a.baseprice','a.operating','a.tax','a.admincharge','b.ordernumber','b.orderdate','b.refrence as eoinumber','b.vendorid','b.workorderamount','b.userid','b.project_duration','b.project_name','b.department_id','c.departmentname','d.companyname','e.sectorname','f.consultantposition','g.remuneration as baseprice','g.duration as temp_duration')
							->leftJoin('eoi_work_order as b','b.orderid','=','a.orderid')
							->leftJoin('department_tbl as c','c.departmentid','=','b.department_id')
							->leftJoin('vendor_tbl as d','d.vendorid','=','b.vendorid')
							->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
							->leftJoin('eoi_request_detail as g','g.recordid','=','a.recordid')
							->when($pagesearch != '', function ($query) use ($pagesearch) {
								$query->where(function ($q) use ($pagesearch) {
									$q->where('a.name', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.mobilenumber', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.email', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.role', 'like', '%' . $pagesearch . '%')
									  ->orWhere('b.project_name', 'like', '%' . $pagesearch . '%');
								});
							})
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('b.categoryid','=',$categoryid);
							})
							->when($departmentid!=0,function($query) use ($departmentid){
								return $query->where('b.department_id','=',$departmentid);
							})
							->when($vendorid!=0,function($query) use ($vendorid){
								return $query->where('b.vendorid','=',$vendorid);
							})
							->whereNotNull('a.deployment_date')
							->whereNotNull('a.name')
							->where('deployment_status','Active')
							->orderBy('a.deployment_date')
							->paginate($pagesize,['*'],'page',$currentPage);
		*/
		$data 		= 	DB::table('eoi_resource_deployment as a')
							->select('a.userid as resourceid','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.remarks','a.is_verified','a.role','a.experience','a.experiencelevel','a.qualification','a.deploymenttype','a.remuneration','a.duration','a.baseprice','a.operating','a.tax','a.admincharge','b.ordernumber','b.orderdate','b.refrence as eoinumber','b.vendorid','b.workorderamount','b.userid','b.project_duration','b.project_name','b.department_id','c.departmentname','d.companyname','e.sectorname','f.consultantposition','g.remuneration as baseprice','g.duration as temp_duration')
							->leftJoin('eoi_work_order as b','b.orderid','=','a.orderid')
							->leftJoin('department_tbl as c','c.departmentid','=','b.department_id')
							->leftJoin('vendor_tbl as d','d.vendorid','=','b.vendorid')
							->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
							->leftJoin('eoi_request_detail as g','g.recordid','=','a.recordid')
							->when($pagesearch != '', function ($query) use ($pagesearch) {
								$query->where(function ($q) use ($pagesearch) {
									$q->where('a.name', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.mobilenumber', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.email', 'like', '%' . $pagesearch . '%')
									  ->orWhere('a.role', 'like', '%' . $pagesearch . '%')
									  ->orWhere('b.project_name', 'like', '%' . $pagesearch . '%');
								});
							})
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('b.categoryid','=',$categoryid);
							})
							->when($departmentid!=0,function($query) use ($departmentid){
								return $query->where('b.department_id','=',$departmentid);
							})
							->when($vendorid!=0,function($query) use ($vendorid){
								return $query->where('b.vendorid','=',$vendorid);
							})
							->where('a.deployment_status','Active')
							->where('a.isExtended',0)
							->whereNotNull('a.name')
							->orderBy('a.deployment_date')
							->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/resourcedetailTable',['data' => $data]);

    }


    public function addNewResource(Request $request,$requestid)
	{
		$request->merge([
			'requestid' => Crypt::decrypt($request->requestid),
		]);
		
        $rules = [
            'requestid'		=>	'required|numeric|exists:eoi_request,requestid',
        ];

        $messages = [
            'requestid.required'	=> 'EoI data is required',
			'requestid.numeric'		=> 'Invalid EoI data',
			'requestid.exists'		=> 'EoI detail does not exists',
        ];

        $validatedData 	=	$request->validate($rules, $messages);
		
		$requestid	=	$request->input('requestid');
		
		$eoi 		= 	DB::table('eoi_request as a')
						->select('a.*','b.departmentname')
						->join('department_tbl as b','b.userid','=','a.userid')
						->where('a.requestid',$requestid)
						->first();
		
		$vendor		=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','b.companyname','b.officelocation','c.tiername','c.tierid')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('tiermaster_tbl as c','c.tierid','=','b.tierid')
						->where('a.requestid','=',$eoi->requestid)
						->where('a.parentorderid','=',0)
						->first();
		
		
		$orders		=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','b.companyname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.requestid','=',$eoi->requestid)
						->get();
		
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/add_newresource', compact('eoi','token','vendor','orders','sectors'));
    }


    public function releaseResourceForm(Request $request,$userid=NULL)
	{
		//$userId	=	$request->session()->get('userId');
		$userid 	=	Crypt::decrypt($userid);
		
		$data	=	DB::table('users_tbl as a')
						->select('a.userid','a.name','a.mobilenumber','a.email','b.employee_code','b.resource_pic','b.role','b.experiencelevel','b.remuneration','b.deployed_date','b.deployment_status','b.deploymenttype','b.duration','b.address','c.sectorname','d.consultantposition')
						->join('resource_tbl as b','b.userid','=','a.userid')
						->leftjoin('sector_tbl as c','c.sectorid','=','b.sectorid')
						->leftjoin('position_tbl as d','d.positionid','=','b.positionid')
						->where('a.userid',$userid)
						->first();
						
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$html = view('admin.forms.releaseform',[
			'data'		=>	$data,
			'token'		=>	$token,
		])->render();
		
		
		return response()->json(['status'=>200,'message'=>'Resource Detail.','releaseform' => $html]);
		
    }

    public function storeRelease(Request $request)
	{
		request()->merge([
			'userid' => Crypt::decrypt(request('user_id'))
		]);
		
        $rules = [
			'userid'			=>	'required|exists:users_tbl,userid',
			'release_file' 		=> 	'nullable|file|mimetypes:application/pdf|max:2048',
			'release_remark' 	=> 	'nullable|max:250|regex:/^[A-Za-z0-9\s\.\,\:\-\&\(\)\[\]\/\\\\]+$/',
			'lastdate'			=>	'required|date',
        ];

		$messages = [
			'userid.required'        => 'User details are required.',
			'userid.exists'          => 'The selected user does not exist.',

			'release_file.file'      => 'The uploaded file is invalid.',
			'release_file.mimetypes' => 'Only PDF files are allowed.',
			'release_file.max'       => 'The file size must not exceed 2 MB.',

			'release_remark.max'     => 'Release remark must not exceed 250 characters.',
			'release_remark.regex'   => 'Release remark contains invalid characters.',

			'lastdate.required'       => 'Last working date is required.',
			'lastdate.date'           => 'Please provide a valid last working date.',
		];

	
		$validatedData 	= 	$request->validate($rules,$messages);
		
		$userId	=	$request->session()->get('userId');
		try
		{
			$release_file=	$request->file('release_file');
			if($request->hasFile('release_file'))
			{
				$release_file= $request->file('release_file')->store('uploads/release_files','public');
			}
			
			$exists	=	DB::table('release_request')->where('userid',$validatedData['userid'])->where('isReleased',0)->exists();
			if($exists)
			{
				return response()->json(['status'=>300,'message'=>'A release request has already been submitted for this resource.']);
			}
			DB::beginTransaction();
			DB::table('release_request')->insert([
				'userid'			=>	$validatedData['userid'],
				'release_file'		=>	$release_file ?? NULL,
				'release_remark'	=>	$validatedData['release_remark'] ?? NULL,
				'lastdate'			=>	date('Y\-m\-d',strtotime($validatedData['lastdate'])) ?? NULL,
				'creationdate'		=>	now(),
				'entered_by'		=>	$userId
			]);
			
			DB::table('resource_tbl')
			->where('userid',$validatedData['userid'])
			->update(['lastdate'=>date('Y\-m\-d',strtotime($validatedData['lastdate']))]);
	
			DB::table('eoi_resource_deployment')
			->where('userid',$validatedData['userid'])
			->where('old_deployment_id',0)
			->update(['lastdate'=>date('Y\-m\-d',strtotime($validatedData['lastdate']))]);
	
			DB::commit();

			return response()->json(['status'=>200,'message'=>'The resource release details have been stored successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error'.$e->getMessage());
			if($release_file!='')
			{
				Storage::disk('public')->delete($release_file);
			}
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function releasingResource(Request $request)
	{
        Session::put('adminmenu','resources');
		Session::put('adminsubmenu','releaserequest');
		
		$userId		=	$request->session()->get('userId');
		$issuper	= 	$request->session()->get('issuper');

		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderBy('categoryid')->orderBy('companyname')->get();
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$search		=	"";

        return view('admin/master/releasing_list',compact('category','vendors','search','departments'));
    }

    public function getReleasingData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('categoryid') ?? 0;
		$departmentid 	=	$request->input('departmentid') ?? 0;
		$vendorid 		=	$request->input('vendorid') ?? 0;

		$pagesearch =	$request->input('pagesearch');
		
		$pagesize	=	$request->input('pagesize',300);
		$currentPage= 	$request->input('page', 1);
		
		$data	=	DB::table('release_request as a')
					->select('a.recordid','a.userid','a.release_file','a.release_remark','a.lastdate','a.isReleased','a.creationdate','b.name','b.mobilenumber','b.email','c.employee_code','c.role','d.sectorname','e.consultantposition','g.companyname')
					->join('users_tbl as b','b.userid','=','a.userid')
					->join('resource_tbl as c','c.userid','=','b.userid')
					->leftjoin('sector_tbl as d','d.sectorid','=','c.sectorid')
					->leftjoin('position_tbl as e','e.positionid','=','c.positionid')
					->leftjoin('eoi_work_order as f','f.orderid','=','c.orderid')
					->leftjoin('vendor_tbl as g','g.vendorid','=','f.vendorid')
					->when($pagesearch!='',function($query) use ($pagesearch){
						return $query->where('b.name','like','%'.$pagesearch.'%')
									 ->orwhere('b.email','like','%'.$pagesearch.'%');
					})
					->when($categoryid!=0,function($query) use ($categoryid){
						return $query->where('f.categoryid','=',$categoryid);
					})
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('f.department_id','=',$departmentid);
					})
					->when($vendorid!=0,function($query) use ($vendorid){
						return $query->where('f.vendorid','=',$vendorid);
					})
					->paginate($pagesize,['*'],'page',$currentPage);
		
		
		return view('admin/ajaxpages/releasingTable',['data' => $data]);

    }


    public function processRelease(Request $request,$recordid)
	{
		$recordid	=	Crypt::decrypt($recordid);
		
		$userId		=	$request->session()->get('userId');
		try
		{
			$todaysdate = Carbon::today()->format('Y-m-d');
			
			DB::beginTransaction();
			$release	=	DB::table('release_request')->where('recordid',$recordid)->first();
			
			if($release->lastdate!=$todaysdate)
			{
				DB::table('eoi_resource_deployment')
				->where('userid',$release->userid)
				->where('old_deployment_id',0)
				->update([
					'deployment_status'	=>	'Released'
				]);

				DB::table('resource_tbl')
				->where('userid',$release->userid)
				->update([
					'deployment_status'	=>	'Released'
				]);

				
				DB::table('release_request')
				->where('recordid',$recordid)
				->update([
					'isReleased'	=>	1,
					'processed_by'	=>	$userId,
					'processed_date'=>	now()
				]);

				
				DB::commit();
				return response()->json(['status'=>200,'message'=>'The resource release process completed successfully.']);
			}
			else
			{
				DB::commit();
				return response()->json(['status'=>400,'message'=>'The resource release process cannot be completed because the last working date '.date('d-m-Y', strtotime($release->lastdate)).' does not match the expected todays date.']);
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error'.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }


	public function viewResourceDetail(Request $request,$deploymentid=0)
	{
		$deploymentid	=	Crypt::decrypt($deploymentid);
		$exists			=	DB::table('eoi_resource_deployment')->where('deploymentid',$deploymentid)->exists();
		
		if(!$exists)
		{
			return response()->json(['status'=>500,'message'=>'Please check the deployment details.']);
		}
		$orderid	=	DB::table('eoi_resource_deployment')->where('deploymentid',$deploymentid)->value('orderid');
		
		$order		=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
		
		if($order->categoryid==2)
		{
			$resource	=	DB::table('eoi_resource_deployment as a')
							->select('a.deploymentid','a.orderid','a.employee_code','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.sectorid','a.positionid','a.lastdate','a.replaced_by_deployment_id','a.supporting_document','a.replaced_by_deployment_id','a.remark')
							->where('deploymentid',$deploymentid)
							->first();
			
			$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
			$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
			
			$updates	=	DB::table('eoi_resource_deployment_updates as a')
							->select('a.employee_code','a.name','a.mobilenumber','a.email','a.deployed_date','a.deployment_status','a.lastdate','a.updatedOn','b.sectorname','c.consultantposition','d.shortname as updatedBy','e.name as subUserName',DB::raw("
								CASE 
									WHEN a.vendorid = 0 AND a.subuserid = 0 
										THEN u.name
									ELSE d.shortname
								END as updatedBy
							"),
							'e.name as subUserName')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('vendor_tbl as d','d.vendorid','=','a.vendorid')
							->leftJoin('users_tbl as e', 'e.userid', '=', 'a.subuserid')
							->leftJoin('users_tbl as u', function ($join) {
								$join->on('u.userid', '=', 'a.updatedBy');
							})
							->where('a.deploymentid',$deploymentid)
							->orderBy('a.updatedOn','DESC')
							->get();
			
			
			$replacedBy	=	DB::table('eoi_resource_deployment')->where('deploymentid',$resource->replaced_by_deployment_id)->value('name');
			
			$replaces	=	DB::table('eoi_resource_deployment')->where('replaced_by_deployment_id',$resource->deploymentid)->value('name');
			
			$htmlData 	= 	view('admin.ajaxpages.resourceDetail',[
								'resource'	=>	$resource,
								'sectors'	=>	$sectors,
								'positions'	=>	$positions,
								'order'		=>	$order,
								'updates'	=>	$updates,
								'replacedBy'=>	$replacedBy,
								'replaces'	=>	$replaces
							])->render();
			
			return response()->json(['status'=>200,'message'=>'Resource Detail.','htmlData'=>$htmlData]);
		}
		if($order->categoryid==1)
		{
			$levels		=	DB::table('remuneration_tbl')
							->select('experiencelevel')
							->where('experiencelevel','!=',0)
							->distinct('experiencelevel')
							->orderBy('experiencelevel')
							->get();
			
			$experiences=	DB::table('work_experience')
							->select('workexperience')
							->orderBy('experienceid')
							->get();
			
			$resource	=	DB::table('eoi_resource_deployment as a')
							->select('a.deploymentid','a.orderid','a.employee_code','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.experiencelevel','a.experience','a.lastdate','a.replaced_by_deployment_id','a.supporting_document','a.role','a.replaced_by_deployment_id','a.remark')
							->where('deploymentid',$deploymentid)
							->first();
			
			$order		=	DB::table('eoi_work_order')->where('orderid',$resource->orderid)->first();
			
		
			$updates	=	DB::table('eoi_resource_deployment_updates as a')
							->select('a.employee_code','a.name','a.mobilenumber','a.email','a.deployed_date','a.deployment_status','a.lastdate','a.updatedOn','b.shortname as updatedBy','c.name as subUserName','a.experience','a.experiencelevel',DB::raw("
								CASE 
									WHEN a.vendorid = 0 AND a.subuserid = 0 
										THEN u.name
									ELSE b.shortname
								END as updatedBy
							"),
							'c.name as subUserName')
							->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->leftJoin('users_tbl as c','c.userid','=','a.subuserid')
							->leftJoin('users_tbl as u', function ($join) {
								$join->on('u.userid', '=', 'a.updatedBy');
							})
							->where('a.deploymentid',$deploymentid)
							->orderBy('a.updatedOn','DESC')
							->get();

			$replacedBy	=	DB::table('eoi_resource_deployment')->where('deploymentid',$resource->replaced_by_deployment_id)->value('name');

			$replaces	=	DB::table('eoi_resource_deployment')->where('replaced_by_deployment_id',$resource->deploymentid)->value('name');

			$htmlData 	= 	view('admin.ajaxpages.resourceDetail',[
								'resource'	=>	$resource,
								'experiences'=>	$experiences,
								'levels'	=>	$levels,
								'order'		=>	$order,
								'updates'	=>	$updates,
								'replacedBy'=>	$replacedBy,
								'replaces'	=>	$replaces
							])->render();
			
			return response()->json(['status'=>200,'message'=>'Resource Detail.','htmlData'=>$htmlData]);
		}
	}

	public function updateResourceDetail(Request $request,$deploymentid=0)
	{
		$request->merge([
			'deploymentid'     => Crypt::decrypt($request->deploymentid)
		]);
		
		$rules = [
		
			'deploymentid'  	=> 	'required|exists:eoi_resource_deployment,deploymentid',
			'employee_code' 	=> 	'nullable|max:100',
			'name'          	=> 	'nullable|max:150',
			'mobilenumber'  	=> 	'nullable|digits:10',
			'email' 			=> 	'nullable|email:rfc,dns|max:150',
			'deployment_status'	=> 	'nullable|in:Pending,Active,Extended,Cancelled,Released|max:10',
			'deployed_date' 	=> 	'nullable|date_format:d-m-Y',
			'released_date' 	=> 	'nullable|date_format:d-m-Y',
			'role'				=>	'nullable|max:150',
			'experiencelevel'   => 	'nullable|numeric',
			'experience' 		=> 	['nullable', 'regex:/^[a-zA-Z0-9+.\(\)\{\} ]+$/'],
			'sectorid'      	=> 	'nullable|numeric',
			'positionid'    	=> 	'nullable|numeric',
		];

		$messages = [
			'deploymentid.required'    => 'Deployment ID is required.',
			'deploymentid.exists'      => 'Invalid deployment selected.',

			'employee_code.required'   => 'Employee code is required.',
			'employee_code.max'        => 'Maximum 100 characters allowed in employee code.',

			'name.required'            => 'Employee name is required.',
			'name.max'                 => 'Maximum 150 characters allowed in name.',

			'mobilenumber.required'    => 'Mobile number is required.',
			'mobilenumber.digits'      => 'Mobile number must be 10 digits.',

			'email.email' 				=> 'Please enter a valid email address.',
			'email.max'   				=> 'Email address must not exceed 150 characters.',

			'deployment_status.in'		=>	'Please select a valid deployment status.',
			'deployment_status.max' 	=> 	'Deployment status must not exceed 10 characters.',

			'deployed_date.date_format'	=> 	'Deployment date must be in dd-mm-YYYY format.',
			'released_date.date_format'	=> 	'Released date must be in dd-mm-YYYY format.',
			
			'role.max'                 	=> 	'Maximum 150 characters allowed in role.',

			'experiencelevel.numeric'  	=> 	'Invalid experience level selected.',
			'sectorid.numeric'         	=> 	'Invalid sector selected.',
			'positionid.numeric'       	=> 	'Invalid position selected.',
			'experience.regex'  		=> 	'The experience field may only contain letters, numbers, spaces, and the characters + . ( ) { }.',
		];

		$validatedData = $request->validate($rules, $messages);		
		
		if($validatedData['deployment_status']=='Released' && $validatedData['released_date']=='')
		{
			return response()->json(['status'=>500,'message'=>'Release Date is required when Deployment Status is Released.']);
		}
		if($validatedData['deployment_status']!='Released' && $validatedData['released_date']!='')
		{
			return response()->json(['status'=>500,'message'=>'If the release date is set, then the deployment status must be Released.']);
		}
		
		try 
		{
			$resource	=	DB::table('eoi_resource_deployment')->where('deploymentid',$validatedData['deploymentid'])->first();
			if(!$resource)
			{
				return response()->json(['status'=>500,'message'=>'Please check the deployment details.']);
			}
			
			$order		=	DB::table('eoi_work_order')
							->where('orderid',$resource->orderid)
							->where('isActiveOrder',1)
							->first();
			if(!$order)
			{
				return response()->json(['status'=>500,'message'=>'This detail cannot be updated because the order has been extended or has expired.']);
			}
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			
			
			
			

			if($validatedData['deployed_date'])
			{
				$validatedData['deployed_date']	=	date('Y\-m\-d',strtotime($validatedData['deployed_date']));
			}
			else
			{
				$validatedData['deployed_date']	=	NULL;
			}

			if($validatedData['released_date'])
			{
				$validatedData['released_date']	=	date('Y\-m\-d',strtotime($validatedData['released_date']));
			}
			else
			{
				$validatedData['released_date']	=	NULL;
			}
			
			
			$plainPassword	= 	$this->passwordService->generatePassword();
			$hashedPassword = 	Hash::make($plainPassword);
			
			$rateid	=	DB::table('remuneration_rate_list')
						->where('categoryid',$order->categoryid)
						->where('isactive',1)
						->value('rateid');
						
			$pricing=	DB::table('pricing_tbl')
						->where('categoryid',$vendor->categoryid)
						->where('tierid',$vendor->tierid)
						->first();


			if($order->categoryid==2)
			{
				$price	=	DB::table('remuneration_tbl')
							->where('rateid',$resource->rateid)
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->where('sectorid',$validatedData['sectorid'])
							->where('positionid',$validatedData['positionid'])
							->first();
			}
			if($order->categoryid==1)
			{
				$price	=	DB::table('remuneration_tbl')
							->where('rateid',$resource->rateid)
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->where('experiencelevel',$validatedData['experiencelevel'])
							->first();
			}
			$data = [
				'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
				'name'				=>	$validatedData['name'] ?? NULL,
				'mobilenumber'		=>	$validatedData['mobilenumber'] ?? NULL,
				'email'				=>	$validatedData['email'] ?? NULL,
				'sectorid'			=>	$validatedData['sectorid'] ?? NULL,
				'positionid'		=>	$validatedData['positionid'] ?? NULL,
				'deployed_date'		=>	$validatedData['deployed_date'] ?? NULL,
				'deployment_status'	=>	$validatedData['deployment_status'] ?? NULL,
				'lastdate'			=>	$validatedData['released_date'] ?? NULL,
				'experience'		=>	$validatedData['experience'] ?? NULL,
				'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
				'role'				=>	$validatedData['role'] ?? NULL,
				'remunerationid'	=>	$price->remunerationid ?? 0,
				'remuneration'		=>	$price->remuneration ?? 0,
				'baseprice'			=>	$price->remuneration ?? 0,
				'operating'			=>	$pricing->operatingmargin ?? 0,
				'tax'				=>	$pricing->tax ?? 0,
				'admincharge'		=>	$pricing->admincharge ?? 0,
			];
			
			if($resource->userid==0)
			{
				if(!empty($validatedData['email']))
				{
					$emailExists	=	DB::table('users_tbl')
										->where('email',$validatedData['email'])
										->exists();

					if($emailExists)
					{
						return response()->json([
							'status' 	=> 	422,
							'message'	=> 	'Email already exists.'
						]);
					}				
				}				
				$categoryid	=	DB::table('eoi_work_order')->where('orderid',$resource->orderid)->value('categoryid');
				$user_id	=	DB::table('users_tbl')->insertGetId([
									'name'			=>	$validatedData['name'] ?? NULL,
									'mobilenumber'	=>	$validatedData['mobilenumber'] ?? NULL,
									'email'			=>	$validatedData['email'] ?? NULL,
									'password'		=>	$hashedPassword,
									'pass_word'		=>	$plainPassword,
									'isresource'	=>	1,
									'issuper'		=>	1,
									'isactive'		=>	1,
									'usertype'		=>	'RESOURCE'
								]);
				
				DB::table('resource_tbl')->insert([
					'orderid'			=>	$resource->orderid,
					'userid'			=>	$user_id,
					'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
					'categoryid'		=>	$order->categoryid,
					'tierid'			=>	$vendor->tierid,
					'sectorid'			=>	$validatedData['sectorid'] ?? 0,
					'positionid'		=>	$validatedData['positionid'] ?? 0,
					'role'				=>	$validatedData['role'] ?? NULL,
					'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
					'remuneration'		=>	$price->remuneration,
					'operating'			=>	$pricing->operatingmargin,
					'tax'				=>	$pricing->tax,
					'admin'				=>	$pricing->admincharge,
					'deployed_date'		=>	$validatedData['deployed_date'] ?? NULL,
					'joining_date'		=>	$validatedData['deployed_date'] ?? NULL,
					'deploymenttype'	=>	$resource->deploymenttype,
					'duration'			=>	$resource->duration,
					'rateid'			=>	$resource->rateid,					
				]);
				
				DB::table('eoi_resource_deployment')
				->where('deploymentid',$resource->deploymentid)
				->update([
					'userid'	=>	$user_id
				]);
			}
			else
			{
				DB::table('users_tbl')
				->where('userid',$resource->userid)
				->update([
					'name'			=>	$validatedData['name'] ?? NULL,
					'mobilenumber'	=>	$validatedData['mobilenumber'] ?? NULL,
					'email'			=>	$validatedData['email'] ?? NULL,
				]);
				
				DB::table('resource_tbl')
				->where('userid',$resource->userid)
				->update([
					'orderid'			=>	$order->orderid,
					'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
					'categoryid'		=>	$order->categoryid,
					'tierid'			=>	$vendor->tierid,
					'sectorid'			=>	$resource->sectorid,
					'positionid'		=>	$resource->positionid,
					'role'				=>	$resource->role,
					'experiencelevel'	=>	$resource->experiencelevel,
					'remuneration'		=>	$price->remuneration,
					'operating'			=>	$pricing->operatingmargin,
					'tax'				=>	$pricing->tax,
					'admin'				=>	$pricing->admincharge,
					'deployment_date'	=>	$resource->deployment_date,
					'deployed_date'		=>	$resource->deployed_date,
					'joining_date'		=>	$resource->deployment_date,
					'rateid'			=>	$rateid,
				]);

			}
				
		
			DB::table('eoi_resource_deployment')
			->where('orderid',$order->orderid)
			->where('deploymentid',$validatedData['deploymentid'])
			->update($data);

			$userId			= 	$request->session()->get('userId');
			$vendorId		= 	$request->session()->get('vendorId') ?? 0;
			$subUserId		=	$request->session()->get('SubUserId');

			$history = [
				'deploymentid'		=>	$validatedData['deploymentid'],
				'updatedBy'    		=> 	$userId,
				'updatedOn'    		=> 	now(),
				'subuserid'    		=> 	$subUserId ?? 0,
				'vendorid'     		=> 	$vendorId ?? 0,
			];

			$fields = [
				'employee_code',
				'name',
				'mobilenumber',
				'email',
				'deployed_date',
				'deployment_status',
				'lastdate',
				'role',
				'experiencelevel',
				'experience',
				'sectorid',
				'positionid'
			];
			
			$hasChanges = false;

			foreach($fields as $field)
			{
				$oldValue = $resource->$field ?? null;
				$newValue = $data[$field] ?? null;

				if(in_array($field, ['deployed_date', 'lastdate']))
				{
					$oldValue = $oldValue ? date('Y-m-d', strtotime($oldValue)) : null;
					$newValue = $newValue ? date('Y-m-d', strtotime($newValue)) : null;
				}

				if($oldValue != $newValue)
				{
					$history[$field] = $oldValue;
					$hasChanges = true;
				}
				else
				{
					$history[$field] = null;
				}
			}

			if($hasChanges)
			{
				DB::table('eoi_resource_deployment_updates')->insert($history);
			}			
			
			DB::commit();
			
			return response()->json([
				'status'	=>	200,
				'message' 	=>	'Resource detail updated successfully',
			]);

		}
		catch(\Exception $e)
		{
			Log::error('Error '.$e->getMessage());
			
			return response()->json([
				'status' 	=> 	500,
				'message'	=> 	$e->getMessage()
			]);
		}
	}

    public function resourceResumes(Request $request)
	{
        Session::put('adminmenu','resources');
		Session::put('adminsubmenu','resourceresumes');
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderBy('categoryid')->orderBy('companyname')->get();
		$departments=	DB::table('department_tbl')->where('isprojectmanager',0)->orderby('departmentname')->get();
		$managers	=	DB::table('department_tbl')->where('isprojectmanager',1)->orderby('departmentname')->get();
		$search	=	"";
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();


		$experiencelevels	=	DB::table('remuneration_tbl')
								->select('experiencelevel')
								->where('experiencelevel', '!=', 0)
								->distinct()
								->orderBy('experiencelevel')
								->get();

		
        return view('admin/master/resource_resumes',compact('category','vendors','search','departments','managers','sectors','positions','experiencelevels'));
    }


    public function getEoIResumes(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('categoryid') ?? 0;
		$departmentid 	=	$request->input('departmentid') ?? 0;
		$managerid 		=	$request->input('managerid') ?? 0;
		$vendorid 		=	$request->input('vendorid') ?? 0;
		
		$eoinumber 		=	$request->input('eoi_number') ?? NULL;
		
		$pagesearch =	$request->input('pagesearch');
		
		$pagesize	=	$request->input('pagesize',300);
		$currentPage= 	$request->input('page', 1);
		

		$data	=	DB::table('eoi_request')
					->select('requestid','eoinumber','releasedate','categoryid')
					->when($categoryid!=0,function($query) use ($categoryid){
						return $query->where('categoryid','=',$categoryid);
					})
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('userid','=',$departmentid);
					})
					->when($managerid!=0,function($query) use ($managerid){
						return $query->where('userid','=',$managerid);
					})
					->when($eoinumber!=NULL,function($query) use ($eoinumber){
						return $query->where('eoinumber','=',$eoinumber);
					})
					->when($pagesearch!='',function($query) use ($pagesearch){
						return $query->whereExists(function($q) use ($pagesearch){
							$q->select(DB::raw(1))
							  ->from('eoi_request_detail_resume as erdr')
							  ->whereColumn('erdr.requestid','eoi_request.requestid')
							  ->where('erdr.name','like','%'.$pagesearch.'%');
						});
					})
					->whereExists(function($query){
						$query->select(DB::raw(1))
							  ->from('eoi_request_detail_resume')
							  ->whereColumn(
								  'eoi_request_detail_resume.requestid',
								  'eoi_request.requestid'
							  );
					})
					->orderBy('releasedate','DESC')
					->paginate($pagesize,['*'],'page',$currentPage);		
		

		foreach($data as $eoi)
		{
			$vendors = DB::table('eoi_request_detail_resume as erdr')
							->leftJoin('vendor_tbl as v','v.vendorid','=','erdr.vendorid')
							->where('erdr.requestid',$eoi->requestid)
							->select('erdr.vendorid','v.shortname','v.tierid','v.categoryid')
							->distinct()
							->get();

			$filteredVendors = collect();

			foreach($vendors as $vendor)
			{
				if($eoi->categoryid==2)
				{
					$vendor->records = DB::table('eoi_request_detail_resume as a')
											->select('a.name','c.sectorname','d.consultantposition','a.resume')
											->join('eoi_request_detail as b','b.recordid','=','a.recordid')
											->join('sector_tbl as c','c.sectorid','=','b.sectorid')
											->join('position_tbl as d','d.positionid','=','b.positionid')
											->where('a.requestid',$eoi->requestid)
											->where('a.vendorid',$vendor->vendorid)
											->when($pagesearch!='',function($query) use ($pagesearch){
												return $query->where('a.name','like','%'.$pagesearch.'%');
											})
											->get();
				}

				if($eoi->categoryid==1)
				{
					$vendor->records = DB::table('eoi_request_detail_resume as a')
											->select('a.name','c.experiencelevel','a.resume')
											->join('eoi_request_detail as b','b.recordid','=','a.recordid')
											->join('remuneration_tbl as c','c.experiencelevel','=','b.experiencelevel')
											->where('a.requestid',$eoi->requestid)
											->where('a.vendorid',$vendor->vendorid)
											->where('c.tierid',$vendor->tierid)
											->where('c.categoryid',$vendor->categoryid)
											->when($pagesearch!='',function($query) use ($pagesearch){
												return $query->where('a.name','like','%'.$pagesearch.'%');
											})
											->get();
				}

				if(isset($vendor->records) && $vendor->records->count() > 0)
				{
					$filteredVendors->push($vendor);
				}
			}

			$eoi->vendors = $filteredVendors;
		}

		$data->setCollection(
			$data->getCollection()
				->filter(function($eoi){
					return $eoi->vendors->count() > 0;
				})
				->values()
		);
		
		return view('admin/ajaxpages/resourceresumeTable',['data' => $data]);
    }


    public function buildnxtResources(Request $request)
	{
        Session::put('adminmenu','resources');
		Session::put('adminsubmenu','buildnxtresources');
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderBy('categoryid')->orderBy('companyname')->get();
		$departments=	DB::table('department_tbl')->where('isprojectmanager',0)->orderby('departmentname')->get();
		$managers	=	DB::table('department_tbl')->where('isprojectmanager',1)->orderby('departmentname')->get();
		$search		=	"";
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();


		$experiencelevels	=	DB::table('remuneration_tbl')
								->select('experiencelevel')
								->where('experiencelevel', '!=', 0)
								->distinct()
								->orderBy('experiencelevel')
								->get();

		
        return view('admin/master/buildnxt_resource',compact('category','vendors','search','departments','managers','sectors','positions','experiencelevels'));
    }

}
