<?php
namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Vendors;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

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
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\LogServices;
use App\Services\TierWiseDataService;
use App\Services\DeploymentDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use App\Services\PasswordService;
use App\Mail\SendParticipationOTPMail;
class VendorManagementController extends Controller
{
	protected $smsService;
    protected $todays_datetime;
    protected $todays_date;	
	protected $logServices;
	protected $priceService;
	protected $deploymentService;
	protected $passwordService;
	public function __construct(SmsService $smsService,LogServices $logServices,TierWiseDataService $priceService,DeploymentDataService $deploymentService,PasswordService $passwordService)
	{
		$this->smsService 		=	$smsService;
		ini_set('serialize_precision', -1);
		$this->todays_datetime	=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date		=	Carbon::now()->format('Y-m-d');
		$this->logServices 		= 	$logServices;
		$this->priceService		=	$priceService;
		$this->deploymentService=	$deploymentService;
		$this->passwordService 	= 	$passwordService;
	}
	function formatIndianCurrency($number) 
	{
		$decimal = '';
		if(strpos($number,'.')!==false)
		{
			$parts = explode('.', $number);
			$number = $parts[0];
			$decimal = '.' . substr($parts[1], 0, 2); // Keep 2 decimal places
		}

		$lastThree = substr($number, -3);
		$rest = substr($number, 0, -3);

		if($rest!='')
		{
			$lastThree = ',' . $lastThree;
		}

		$rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);
		return $rest . $lastThree . $decimal;
	}
    public function storeInvoiceVoucher(Request $request)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
        $rules = [
			'invoice_number'	=>	'required|max:50',
			'invoice_date' 		=>	'required|date_format:d-m-Y',
			'invoice_file'		=>	'required|file|mimes:pdf|max:10240',
			'invoice_remark'	=>	'nullable|max:1000',
        ];

        $messages = [
			'invoice_number.required'	=>	'Invoice number is mandatory',
			'invoice_number.max'		=>	'Maximum 50 characters allowed',
			'invoice_date.required'		=>	'Invoice date is mandatory',
			'invoice_date.date_format'	=>	'Invalid invoice date provided',
			'invoice_file.required' 	=> 	'Invoice file is mandatory',
			'invoice_file.file' 		=> 	'Invalid invoice file provided',
			'invoice_file.mimes' 		=> 	'Invalid invoice file provided',
			'invoice_file.max' 			=> 	'Only 10 MB file size is allowed',
			'invoice_remark.max'		=>	'Maximum 1000 characters allowed',
        ];
		$validatedData	=	$request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		
		try
		{
			$validatedData['invoice_date']	=	Carbon::createFromFormat('d-m-Y',$validatedData['invoice_date'])->format('Y-m-d');
			
			$orderid		=	Crypt::decrypt(Session('invoice_order_id'));
			$mpr_ids		=	explode(',',Session('invoice_mpr_ids'));
			
			$order	=	DB::table('eoi_work_order as a')
						->select('a.*','b.departmentname')
						->leftjoin('department_tbl as b','b.userid','=','a.userid')
						->where('a.orderid',$orderid)
						->first();
			
			if($order->requestid!=0)
			{
				$order	=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
			}
			
			
			$mprs	=	DB::table('mpr_tbl as a')
						->select('a.*')
						->where('a.vendor_id',Session::get('vendorId'))
						->where('a.is_verified','=',1)
						->where('a.is_invoiced','=',0)
						->where('a.order_id','=',$orderid)
						->whereIn('a.mpr_id',$mpr_ids)
						->orderby('a.submission_date')
						->get();
			
			if(!$mprs)
			{
				return response()->json([
				'errors' => [
					'mpr_ids' => ['Invalid mpr detail provided.'],
					]
				], 422);			
				
			}
			
			$total=0;
			foreach($mprs as $mpr)
			{
				$total	=	$total+$mpr->mpr_approved_value;
			}
			
			$gst		=	round(($total*$order->tax)/100,2);
			$net_amount	=	round(($total+$gst),2);
			$invoicefile	=	$request->file('invoice_file') ?? '';

			if($invoicefile!='')
			{
				$invoicefile	=	$request->file('invoice_file')->store('uploads/invoicefiles','public');
			}
			$voucher_number	=	'INV/'.strtoupper(Session('shortName')).'/'.date('Y').'/'.date('m').'/'.date('d').'/'.rand(100,999);
			
			DB::table('invoice_mpr')->insert([
				'voucher_number'	=>	$voucher_number,
				'voucher_date'		=>	now(),
				'invoice_number'	=>	$validatedData['invoice_number'],
				'invoice_date'		=>	$validatedData['invoice_date'],
				'invoice_status'	=>	'Submitted',
				'invoice_remark'	=>	$validatedData['invoice_remark'],
				'mpr_ids'			=>	Session('invoice_mpr_ids'),
				'invoice_amount'	=>	$total,
				'tax'				=>	$order->tax,
				'tax_value'			=>	$gst,
				'net_invoice_value'	=>	$net_amount,
				'invoice_file'		=>	$invoicefile,
				'order_id'			=>	$order->orderid,
				'request_id'		=>	$order->requestid,
				'vendor_id'			=>	Session('vendorId') ?? 0,
				'user_id'			=>	Session('userId') ?? 0,
				'subUserId'			=>	Session('subUserId') ?? 0,
			]);
			
			DB::table('mpr_tbl')->whereIn('mpr_id',$mpr_ids)->update([
				'is_invoiced'	=>	1
			]);
			$url = route('manage.vendor',Crypt::encrypt($orderid));
			Session::forget('invoice_order_id');
			Session::forget('invoice_mpr_ids');			
			return redirect($url)->with('success', 'The invoice has been submitted successfully.');;
		}
		catch(QueryException $e)
		{
			Log::error('Error: '.$e->getMessage());
			if($invoicefile!='')
			Storage::disk('public')->delete($invoicefile);
			
			$url = route('manage.vendor',Crypt::encrypt($orderid));
			return redirect($url)->with('duplicate','Duplicate  invoice number found. Please check and try again');
		}
		
    }
	
    public function prepareVendorInvoice(Request $request)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		if($request->isMethod('post'))
		{
			$rules = [
				'orderid' 		=> 'required',
				'mpr_ids' 		=> 'required',
			];

			$messages = [
				'orderid.required' 		=> 'Order detail is required',
				'mpr_ids.required' 		=> 'At least one MPR must be selected.',
			];
			
			$validatedData	=	$request->validate($rules, $messages);

			$encryptedOrderId	=	$request->input('orderid');
			$mpr_ids = explode(',',$request->input('mpr_ids'));

			Session::put('invoice_order_id',$encryptedOrderId);
			Session::put('invoice_mpr_ids',$request->input('mpr_ids'));
		}
		else
		{
			$encryptedOrderId = Session::get('invoice_order_id');
			$mprIdsString = Session::get('invoice_mpr_ids');

			if (empty($encryptedOrderId) || empty($mprIdsString)) {
				return redirect('dashboard')->with('error', 'Invoice data not found.');
			}

			$mpr_ids = explode(',', $mprIdsString);			
		}
		$orderid = Crypt::decrypt($encryptedOrderId);
		try
		{

			$data = DB::table('eoi_work_order')
				->where('orderid', $orderid)
				->first();

			$order = DB::table('eoi_work_order as a')
					->select('a.*', 'b.departmentname','c.ispm','d.project_name')
					->leftJoin('department_tbl as b', 'b.userid', '=', 'a.userid')
					->leftJoin('users_tbl as c', 'c.userid', '=', 'a.userid')
					->leftJoin('project_tbl as d', 'd.projectid', '=', 'a.projectid')
					->where('a.orderid', $orderid)
					->first();


			$mprs = DB::table('mpr_tbl as a')
				->select('a.*')
				->join('vendor_tbl as b', 'b.vendorid', '=', 'a.vendor_id')
				->where('a.vendor_id', Session::get('vendorId'))
				->where('a.is_verified', 1)
				->where('a.is_invoiced', 0)
				->where('a.order_id', $orderid)
				->whereIn('a.mpr_id', $mpr_ids)
				->orderBy('a.submission_date')
				->get();

			if ($mprs->count() == 0) {
				$url = route('manage.vendor', Crypt::encrypt($orderid));
				return redirect($url);
			}
			
			$operating	=	DB::table('mpr_resource')->where('mpr_id',$mpr_ids[0])->value('operating') ?? 0;
			$token 		= 	rand(100000,999999).time();

			Session::put('form_token', $token);

			return view('admin/vendors/prepare_invoice', [
				'order'   	=> 	$order,
				'mprs'    	=> 	$mprs,
				'token'   	=> 	$token,
				'operating'	=>	$operating,
				'mpr_ids' => implode(',', $mpr_ids),
			]);

		}
		catch (QueryException $e)
		{
			return back()->with('error', 'Something went wrong. Please try again!');
		}		
    }
	
    public function makeVendorInvoice(Request $request)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
        $rules = [
			'orderid' 		=> 'required',
			'mpr_ids' 		=> 'required',
        ];

        $messages = [
			'orderid.required' 		=> 'Order detail is required',
			'mpr_ids.required' 		=> 'At least one MPR must be selected.',
        ];
		$validator = \Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'status' => 422,
				'errors' => $validator->errors()
			], 422);
		}		
		
		$orderid	=	Crypt::decrypt($request->input('orderid'));
		$mpr_ids	=	explode(",",$request->input('mpr_ids'));
		
		try
		{
			$order	=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
			
			$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			
			$mprs	=	DB::table('mpr_tbl as a')
						->select('a.*')
						->join('vendor_tbl as b','b.vendorid','=','a.vendor_id')
						->where('a.vendor_id',Session::get('vendorId'))
						->where('a.is_verified','=',1)
						->where('a.is_invoiced','=',0)
						->where('a.order_id','=',$orderid)
						->whereIn('a.mpr_id',$mpr_ids)
						->orderby('a.submission_date')
						->get();
			if(!$mprs)
			{
				return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
			}
			return response()->json(['status'=>200,'message'=>'Prepare Invoice.']);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
		}
    }

    public function pendingInvoices(Request $request)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		$orderid	=	Crypt::decrypt($request->input('orderid'));
		$order		=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','b.project_name')
						->leftJoin('project_tbl as b','b.projectid','=','a.projectid')
						->where('a.orderid',$orderid)
						->first();
		try
		{
			$mprs	=	DB::table('mpr_tbl as a')
						->select('a.*',DB::raw("
							CASE
								WHEN EXISTS (
									SELECT 1
									FROM regularisation r
									WHERE r.mpr_id = a.mpr_id
								)
								THEN 1
								ELSE 0
							END AS is_regularised
						"))
						->join('eoi_work_order as b','b.orderid','=','a.order_id')
						->where('a.vendor_id',Session::get('vendorId'))
						->where('a.is_verified','=',1)
						->where('a.is_invoiced','=',0)
						->where('a.order_id','=',$orderid)
						->orderby('a.submission_date')
						->get();
			if(!$mprs)
			{
				return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
			}
			$html	=	view('admin.vendors.ajaxpages.loadPendingMpr',['mprs'=>$mprs,'order'=>$order,'orderid'=>$request->input('orderid')])->render();
			return response()->json(['status'=>200,'data'=>$html]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
		}
		
    }

    public function uploadMprDocumentForm(Request $request)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		$mprid	=	Crypt::decrypt($request->input('mprid'));
		try
		{
			$mprs	=	DB::table('mpr_tbl as a')
						->select('a.*','b.ordernumber','c.project_name','b.categoryid')
						->join('eoi_work_order as b','b.orderid','=','a.order_id')
						->leftJoin('project_tbl as c','c.projectid','=','b.projectid')
						->where('a.vendor_id',Session::get('vendorId'))
						->where('a.is_verified','=',1)
						->where('a.is_invoiced','=',0)
						->where('a.mpr_id','=',$mprid)
						->orderby('a.submission_date')
						->first();

			if(!$mprs)
			{
				return response()->json(['status'=>500,'message'=>'The provided MPR details are invalid.']);
			}

			$mprs->summary	=	DB::table('mpr_attendance_summary as a')
								->select('a.*','b.name','b.mobilenumber','b.email','b.deployed_date','b.lastdate','c.sectorname','d.consultantposition','b.role','b.experience','b.experiencelevel')
								->join('eoi_resource_deployment as b','b.deploymentid','=','a.deployment_id')
								->leftJoin('sector_tbl as c','c.sectorid','=','b.sectorid')
								->leftJoin('position_tbl as d','d.positionid','=','b.positionid')
								->where('a.mpr_id',$mprs->mpr_id)
								->get();

			$mprs->mprid	=	Crypt::encrypt($mprs->mpr_id);								

			$html	=	view('admin.vendors.ajaxpages.loadUpdateMprForm',['mprs'=>$mprs,'mprid'=>$request->input('mprid'),'orderid'=>$mprs->order_id])->render();

			return response()->json(['status'=>200,'data'=>$html]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
		}
    }
	
    public function submitMpr(Request $request)
	{
        $rules = [
			'orderid' 			=> 'required',
			'mprid' 			=> 'required|numeric|min:0',
			'month' 			=> 'required|numeric',
			'year' 				=> 'required|numeric',
			
			'remark_mpr' 		=> 	['nullable','max:1000', 'regex:/^[a-zA-Z0-9!`,@\$#%&*\(\)\{\}\[\]:\'"\., ]*$/'],
			'attendance_file'	=> 	'nullable|file|mimes:pdf|max:10240',
			'mpr_file' 			=> 	'nullable|file|mimes:pdf|max:10240',
			'supporting_file'	=> 	'nullable|file|mimes:pdf|max:10240',
        ];

        $messages = [
			'orderid.required' 			=> 	'Order detail is required',
			'mprid.required' 			=> 	'MPR detail is required',
			'mprid.numeric' 			=> 	'Invalid MPR detail',
			'mprid.min' 				=> 	'Invalid MPR detail',
			'month.required' 			=> 	'Month value is required',
			'month.numeric' 			=> 	'Invalid month value',
			'year.required' 			=> 	'Year value is required',
			'year.numeric' 				=> 	'Invalid year value',

			'remark_mpr.string'  		=> 	'Remark must be a valid text.',
			'remark_mpr.max'     		=> 	'Remark cannot exceed 1000 characters.',
			'remark_mpr.regex'   		=> 	'Remark contains invalid characters.',

			'attendance_file.required'	=> 	'Attendance file is required',
			'attendance_file.file' 		=> 	'Invalid Attendance file selected',
			'attendance_file.mimes' 	=> 	'Invalid Attendance file selected',
			'attendance_file.max' 		=> 	'Maximum file size of 10 MB is allowed',

			'mpr_file.required' 		=> 	'MPR file is required',
			'mpr_file.file' 			=> 	'Invalid MPR file selected',
			'mpr_file.mimes' 			=> 	'Invalid MPR file selected',
			'mpr_file.max' 				=> 	'Maximum file size of 10 MB is allowed',

			'supporting_file.required'	=> 	'Supporting file is required',
			'supporting_file.file' 		=> 	'Invalid Supporting file selected',
			'supporting_file.mimes' 	=> 	'Invalid Supporting file selected',
			'supporting_file.max' 		=> 	'Maximum file size of 10 MB is allowed',
        ];
		$validatedData	=	$request->validate($rules, $messages);
		
		
		DB::beginTransaction();
		try
		{
			$pendingFiles	=	DB::table('mpr_attendance_summary')
								->where(function ($query) {
									$query->whereNull('mpr_file')
										  ->orWhereNull('attendance_file');
								})
								->exists();						
			if($pendingFiles)
			{
				if(!$request->file('attendance_file') || !$request->file('mpr_file'))
				{
					return response()->json(['status'=>500,'message'=>'<i class="fa fa-angle-double-right"></i> One or more of the required documents (MPR, Attendance, or Supporting Document) are missing for one or more resources.<br><br><i class="fa fa-angle-double-right"></i>In such cases, you must merge the MPR, Attendance, and Supporting Documents for all the resources and upload the merged files in their respective fields.<br><br><i class="fa fa-angle-double-right"></i> Alternatively, you can upload the respective MPR, Attendance, and Supporting Documents in the View section of each resource. Once all required documents for all resources have been submitted, the system will no longer require you to upload merged files.']);
				}
			}
			
			$order	=	DB::table('eoi_work_order')
						->select('orderid','vendorid')
						->where('orderid',Crypt::decrypt($validatedData['orderid']))
						->first();
			if(!$order)
			{
				return response()->json(['status'=>500,'message'=>'The provided order details are invalid']);
			}
		
			$exists	=	DB::table('mpr_tbl')
						->where('order_id',$order->orderid)
						->where('vendor_id',$order->vendorid)
						->where('mpr_month',$validatedData['month'])
						->where('mpr_year',$validatedData['year'])
						->where('mpr_id',$validatedData['mprid'])
						->first();
			if($exists)
			{
				$attendance_file=	$request->file('attendance_file') ?? NULL;
				$mpr_file		=	$request->file('mpr_file') ?? NULL;
				$supporting_file=	$request->file('supporting_file') ?? NULL;
				$engagement		=	$request->input('engagement') ?? NULL;
				$remark_mpr		=	$request->input('remark_mpr') ?? NULL;

				if($attendance_file!='')
				{
					$attendance_file	=	$request->file('attendance_file')->store('uploads/mprattendancefiles','public');
				}
				if($mpr_file!='')
				{
					$mpr_file	=	$request->file('mpr_file')->store('uploads/mprfiles','public');
				}
				if($supporting_file!='')
				{
					$supporting_file	=	$request->file('supporting_file')->store('uploads/mprsupportingfiles','public');
				}
				
				DB::table('mpr_tbl')
				->where('order_id',$order->orderid)
				->where('vendor_id',$order->vendorid)
				->where('mpr_month',$validatedData['month'])
				->where('mpr_year',$validatedData['year'])
				->where('mpr_id',$validatedData['mprid'])
				->update([
					'mpr_status'		=>	'Completed',
					'submission_date'	=>	date('Y\-m\-d'),
					'attendance_file'	=>	$attendance_file,
					'mpr_file'			=>	$mpr_file,
					'supporting_file'	=>	$supporting_file,
					'remark_mpr'		=>	$remark_mpr
				]);

				DB::table('mpr_resource')
				->where('mpr_month',$validatedData['month'])
				->where('mpr_year',$validatedData['year'])
				->where('mpr_id',$validatedData['mprid'])
				->update([
					'status'	=>	'Submitted',
				]);

				DB::commit();
				return response()->json(['status' => 200]);
			}
			else
			{
				DB::commit();
				return response()->json(['status'=>400,'message'=>'The provided order or MPR details are invalid.']);
			}
			
			
		}
		catch(Exception $e)
		{
			DB::rollBack();
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
	}
	
    public function storeMprAttendance(Request $request)
	{
		$request->merge([
			'deploymentid'	=> 	Crypt::decrypt($request->deploymentid),
			'absents'		=>	$request->absents ?? 0,
			'leaves'		=>	$request->leaves ?? 0,
		]);
		
        $rules = [
			'orderid' 		=> 	'required',
			'mprid' 		=> 	'required|numeric|min:0',
			'month' 		=> 	'required|numeric',
			'year' 			=> 	'required|numeric',
			'deploymentid' 	=> 	'required|exists:eoi_resource_deployment,deploymentid',
			'total_days' 	=> 	'required|numeric',
			'count_days' 	=> 	'required|numeric',
			'per_day_salary'=> 	'required|numeric',
			'presents' 		=> 	'required|numeric',
			'absents' 		=> 	'required|numeric',
			'leaves' 		=> 	'required|numeric',
			'holidays' 		=> 	'required|numeric',
			'engagement'	=> 	'required|numeric|between:0,100',
			'mpr_remark' 	=> 	['nullable','max:1000', 'regex:/^[a-zA-Z0-9!`,@\$#%&*\(\)\{\}\[\]:\'"\., ]*$/'],
        ];
		$exists	=	DB::table('mpr_tbl')->where('mpr_id',$request->mprid)->exists();
					
		if(!$exists)
		{
			$rules['attendancefile']	= 	'required|file|mimes:pdf|max:5120';
			$rules['mprfile'] 			= 	'required|file|mimes:pdf|max:5120';
			$rules['supportingfile']	=	'nullable|file|mimes:pdf|max:5120';
		}
		else
		{
			$rules['attendancefile'] =	'nullable|file|mimes:pdf|max:5120';
			$rules['mprfile'] 		 = 	'nullable|file|mimes:pdf|max:5120';
			$rules['supportingfile'] = 	'nullable|file|mimes:pdf|max:5120';
		}
		
        $messages = [
			'orderid.required' 			=> 'Order detail is required',
			'mprid.required' 			=> 'MPR detail is required',
			'mprid.numeric' 			=> 'Invalid MPR detail',
			'mprid.min' 				=> 'Invalid MPR detail',
			'month.required' 			=> 'Month value is required',
			'month.numeric' 			=> 'Invalid month value',
			'year.required' 			=> 'Year value is required',
			'year.numeric' 				=> 'Invalid year value',
			'deploymentid.required' 	=> 'Deployment detail is required',
			'total_days.required' 		=> 'Total days value is required',
			'total_days.numeric' 		=> 'Invalid total days value',
			'presents.required' 		=> 'Present value is required',
			'presents.numeric' 			=> 'Invalid present value',
			'absents.required' 			=> 'Absent value is required',
			'absents.numeric' 			=> 'Invalid absent value',
			'leaves.required' 			=> 'Leave value is required',
			'leaves.numeric' 			=> 'Invalid leave value',
			'holidays.required' 		=> 'Holidy value is required',
			'holidays.numeric' 			=> 'Invalid holiday value',

			'engagement.required' 		=> 'Engagement is required.',
			'engagement.numeric'  		=> 'Engagement must be a number.',
			'engagement.between' 		=> 'Engagement must be between 0% and 100%.',
			
			'mpr_remark.string'  		=> 'Remark must be a valid text.',
			'mpr_remark.max'     		=> 'Remark cannot exceed 1000 characters.',
			'mpr_remark.regex'   		=> 'Remark contains invalid characters.',

			'attendancefile.required'	=> 'Attendance file is required',
			'attendancefile.file' 		=> 'Invalid Attendance file selected',
			'attendancefile.mimes' 		=> 'Invalid Attendance file selected',
			'attendancefile.max' 		=> 'Maximum file size of 5 MB is allowed',

			'mprfile.required' 			=> 'MPR file is required',
			'mprfile.file' 				=> 'Invalid MPR file selected',
			'mprfile.mimes' 			=> 'Invalid MPR file selected',
			'mprfile.max' 				=> 'Maximum file size of 5 MB is allowed',

			'supportingfile.required'	=> 'Supporting file is required',
			'supportingfile.file' 		=> 'Invalid Supporting file selected',
			'supportingfile.mimes' 		=> 'Invalid Supporting file selected',
			'supportingfile.max' 		=> 'Maximum file size of 5 MB is allowed',
        ];
		$validatedData					=	$request->validate($rules, $messages);

		DB::beginTransaction();
		try
		{
			$start			= 	Carbon::create($validatedData['year'], $validatedData['month'], 1);			
			$joiningDate	=	DB::table('eoi_resource_deployment')
								->where('deploymentid',$validatedData['deploymentid'])
								->value('deployment_date');
			$joiningDate 	= 	Carbon::parse($joiningDate);

			
			$start 			= 	$joiningDate->greaterThanOrEqualTo($start) ? $joiningDate : $start;
			
			$releasedate	=	DB::table('eoi_resource_deployment')
								->where('deploymentid',$validatedData['deploymentid'])
								->value('lastdate');
			
			$monthEnd	=	$start->copy()->endOfMonth();
			$end 		= 	$releasedate ? $monthEnd->min(\Carbon\Carbon::parse($releasedate)) : $monthEnd;
			
			$weekendCount 	= 	0;
			$countableDays	= 	$start->diffInDays($end) + 1;

			for($date = $start; $date->lte($end); $date->addDay())
			{
				if($date->isSaturday() || $date->isSunday())
				{
					$weekendCount++;
				}
			}
			if($weekendCount>$validatedData['holidays'])
			{
				return response()->json(['status'=>400,'message'=>"Entered holidays cannot be less than {$weekendCount}, because Saturdays and Sundays are already considered holidays in this month."]);
			}
			$orderid=	DB::table('eoi_resource_deployment')->where('deploymentid',$validatedData['deploymentid'])->value('orderid');
			
			$order	=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
			
			if(!$order)
			{
				return response()->json(['status'=>400,'message'=>'Invalid order detail provided. Please check and try again!']);
			}
			$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			
			$exists	=	DB::table('mpr_tbl')
						->where('order_id',$order->orderid)
						->where('vendor_id',$order->vendorid)
						->where('mpr_month',$validatedData['month'])
						->where('mpr_year',$validatedData['year'])
						->first();
			if(!$exists)
			{
				$ord			=	explode("/",$order->ordernumber);
				
				$mprnumber		=	'MPR/'.$ord[0].'/'.$validatedData['month'].'/'.$validatedData['year'];
				$report_month 	= 	Carbon::create($validatedData['year'],$validatedData['month'])->endOfMonth()->toDateString();
				$mprid			=	DB::table('mpr_tbl')->insertGetId([
										'mpr_number'	=>	$mprnumber,
										'vendor_id'		=>	$order->vendorid,
										'order_id'		=>	$order->orderid,
										'request_id'	=>	$order->requestid,
										'report_month'	=>	$report_month,
										'mpr_month'		=>	$validatedData['month'],
										'mpr_year'		=>	$validatedData['year'],
										'created_at'	=>	now(),
										'subUserId'		=>	session('subUserId') ?? 0
									]);
				$validatedData['mprid']	=	$mprid;
			}
			else
			{
				$validatedData['mprid']	=	$exists->mpr_id;
			}
			$exists	=	DB::table('mpr_resource')
						->where('deployment_id',$validatedData['deploymentid'])
						->where('mpr_month',$validatedData['month'])
						->where('mpr_year',$validatedData['year'])
						->first();
			$date 		= 	Carbon::createFromDate($validatedData['year'],$validatedData['month'],1)->toDateString();

			$rateid		=	DB::table('remuneration_rate_list')
							->whereDate('startDate','<=',$date)
							->where('endDate','>=',$date)
							->where('categoryid',$vendor->categoryid)
							->value('rateid');
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
			
			$resource	=	DB::table('eoi_resource_deployment as a')
							->select('a.deploymentid','a.orderid','d.remuneration','d.includingtax','a.deploymenttype')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('remuneration_tbl as d', function ($join) use ($vendor,$rateid) {
								$join->where('d.categoryid', $vendor->categoryid)
									 ->where('d.tierid', $vendor->tierid)
									 ->where('d.rateid', $rateid);

								if ($vendor->categoryid == 2) {
									$join->on('d.sectorid', '=', 'a.sectorid')
										 ->on('d.positionid', '=', 'a.positionid');
								} elseif ($vendor->categoryid == 1) {
									$join->on('d.experiencelevel', '=', 'a.experiencelevel');
								}
							})
							->where('a.deploymentid',$validatedData['deploymentid'])
							->first();

			$taxable					=	$resource->remuneration;
			$resource->operating_value	=	(($taxable*$pricing->operatingmargin)/100);
			$taxable					=	$taxable+$resource->operating_value;
			$resource->tax_value		=	(($taxable*$pricing->tax)/100);
			$resource->total_amount		=	$taxable+$resource->tax_value;

			if(!$exists)
			{				
				DB::table('mpr_resource')->insert([
					'deployment_id'		=>	$validatedData['deploymentid'],
					'mpr_id'			=>	$validatedData['mprid'],
					'mpr_month'			=>	$validatedData['month'],
					'mpr_year'			=>	$validatedData['year'],
					'remuneration'		=>	$resource->remuneration,
					'operating'			=>	$pricing->operatingmargin,
					'operating_value'	=>	$resource->operating_value,
					'tax'				=>	$pricing->tax,
					'tax_value'			=>	$resource->tax_value,
					'total_amount'		=>	$resource->total_amount,
					'status'			=>	'Completed'
				]);
			}
			$total_days = 	Carbon::create($validatedData['year'], $validatedData['month'])->daysInMonth;
			$presents	=	0;
			$absents	=	0;
			$leaves		=	0;
			$holidays	=	0;
			$actualDays	=	0;

			$presents	=	$request->input('presents') ?? 0;
			$absents	=	$request->input('absents') ?? 0;
			$leaves		=	$request->input('leaves') ?? 0;
			$holidays	=	$request->input('holidays') ?? 0;
			$actualDays	=	$request->input('count_days') ?? 0;
			if($countableDays!=($presents+$absents+$leaves+$holidays) || $countableDays>$total_days)
			{
				$message	=	"Total available days for the selected month is ".$countableDays.". Please ensure that the sum of P, A, L, and H equals ".$countableDays;
				return response()->json(['status' => 500,'message'=>$message]);
			}
			
			for($i=1;$i<=31;$i++)
			{
				$days[$i]		=	$request->input('day_'.$i);
				$remarks[$i]	=	$request->input('remark_'.$i);
				
				if($days[$i]!='' && in_array($days[$i],['A','P','L','H']))
				{
					$actualDays++;

					$exists		=	DB::table('mpr_attendance')
									->where('mpr_id',$validatedData['mprid'])
									->where('deployment_id',$validatedData['deploymentid'])
									->where('work_day',$i)
									->where('work_month',$validatedData['month'])
									->where('work_year',$validatedData['year'])
									->exists();
					if(!$exists)
					{
						DB::table('mpr_attendance')->insert([
							'mpr_id'		=>	$validatedData['mprid'],
							'deployment_id'	=>	$validatedData['deploymentid'],
							'work_date'		=>	$validatedData['year'].'-'.$validatedData['month'].'-'.$i,
							'work_day'		=>	$i,
							'work_month'	=>	$validatedData['month'],
							'work_year'		=>	$validatedData['year'],
							'status'		=>	$days[$i],
							'created_at'	=>	now(),
							'mpr_detail'	=>	$remarks[$i],
						]);
					}
					if($days[$i]=='P')
					{
						$presents	=	$presents+1;
					}
					if($days[$i]=='A')
					{
						$absents	=	$absents+1;
					}
					if($days[$i]=='L')
					{
						$leaves	=	$leaves+1;
					}
					if($days[$i]=='H')
					{
						$holidays	=	$holidays+1;
					}
				}
			}
			$exists	=	DB::table('mpr_attendance_summary')
						->where('mpr_id',$validatedData['mprid'])
						->where('deployment_id',$validatedData['deploymentid'])
						->exists();
						
			$attendancefile	=	$request->file('attendancefile') ?? NULL;
			$mprfile		=	$request->file('mprfile') ?? NULL;
			$supportingfile	=	$request->file('supportingfile') ?? NULL;
			$engagement		=	$request->input('engagement') ?? NULL;
			$mpr_remark		=	$request->input('mpr_remark') ?? NULL;

			if($attendancefile!='')
			{
				$attendancefile	=	$request->file('attendancefile')->store('uploads/mprattendancefiles','public');
			}
			if($mprfile!='')
			{
				$mprfile	=	$request->file('mprfile')->store('uploads/mprfiles','public');
			}
			if($supportingfile!='')
			{
				$supportingfile	=	$request->file('supportingfile')->store('uploads/mprsupportingfiles','public');
			}
			
			
			$countable			=	$presents+$leaves+$holidays;
			$per_day_salary		=	$resource->remuneration/$total_days;
			$actual_salary		=	$actualDays*$per_day_salary;
			$calculated_salary	=	$countable*$per_day_salary;
			if($resource->deploymenttype!='FULL TIME')
			{
				$per_day_salary		=	($per_day_salary*$validatedData['engagement'])/100;
				$actual_salary		=	$actualDays*$per_day_salary;
				$calculated_salary	=	$countable*$per_day_salary;
			}
			

			if(!$exists)
			{				
				DB::table('mpr_attendance_summary')->insert([
					'mpr_id'			=>	$validatedData['mprid'],
					'deployment_id'		=>	$validatedData['deploymentid'],
					'total_working_days'=>	$total_days,
					'count_days'		=>	$validatedData['count_days'] ?? 0,
					'total_present'		=>	$presents,
					'total_absent'		=>	$absents,
					'total_leave'		=>	$leaves,
					'total_holiday'		=>	$holidays,
					'actual_salary'		=>	round($actual_salary,2),
					'calculated_salary'	=>	round($calculated_salary,2),
					'net_salary'		=>	round($calculated_salary,2),
					'per_day_cost'		=>	round($per_day_salary,2),
					'mpr_file'			=>	$mprfile,
					'attendance_file'	=>	$attendancefile,
					'supporting_file'	=>	$supportingfile,
					'engagement'		=>	$validatedData['engagement'] ?? 0,
					'mpr_remark'		=>	$validatedData['mpr_remark'] ?? NULL,
					'subUserId'			=>	session('subUserId') ?? 0
				]);
			}
			else
			{
				DB::table('mpr_attendance_summary')
				->where('mpr_id',$validatedData['mprid'])
				->where('deployment_id',$validatedData['deploymentid'])
				->update([
					'total_working_days'=>	$total_days,
					'count_days'		=>	$validatedData['count_days'] ?? 0,
					'total_present'		=>	$presents,
					'total_absent'		=>	$absents,
					'total_leave'		=>	$leaves,
					'total_holiday'		=>	$holidays,
					'actual_salary'		=>	round($actualDays*$per_day_salary,2),
					'calculated_salary'	=>	round($calculated_salary,2),
					'net_salary'		=>	round($calculated_salary,2),
					'per_day_cost'		=>	round($per_day_salary,2),
					'engagement'		=>	$validatedData['engagement'] ?? 0,
					'mpr_remark'		=>	$validatedData['mpr_remark'] ?? NULL					
				]);
				$files = [];
				if($request->hasFile('attendancefile'))
				{
					$files['attendance_file'] = $request->file('attendancefile')->store('uploads/mprattendancefiles', 'public');
				}

				if($request->hasFile('mprfile'))
				{
					$files['mpr_file'] = $request->file('mprfile')->store('uploads/mprfiles', 'public');
				}

				if($request->hasFile('supportingfile'))
				{
					$files['supporting_file'] = $request->file('supportingfile')->store('uploads/mprsupportingfiles', 'public');
				}

				if(!empty($files))
				{
					DB::table('mpr_attendance_summary')
					->where('mpr_id', $validatedData['mprid'])
					->where('deployment_id', $validatedData['deploymentid'])
					->update($files);
				}				
			}
			DB::commit();
			return response()->json(['status' => 200,'mprid'=>$validatedData['mprid']]);
		}
		catch(Exception $e)
		{
			DB::rollBack();
			return response()->json(['status'=>500,'message'=>$e->getMessage()]);
		}
	}
    public function loadResourceMpr(Request $request)
	{	
		$orderid		=	Crypt::decrypt($request->input('orderid'));
		$mprid			=	$request->input('mprid') ?? 0;
		$month			=	$request->input('month') ?? 0;
		$year			=	$request->input('year') ?? 0;
		$deploymentid	=	$request->input('deploymentid') ?? 0;
		
		$order 			= 	DB::table('eoi_work_order')
							->select('orderid','project_name as projecttitle','categoryid','vendorid','workorderduedate')
							->where('orderid',$orderid)
							->where('vendorid',Session('vendorId'))
							->first();
		if(!$order)
		{
			return response()->json(['status'=>400,'message'=>'Invalid order detail provided. Please check and try again!']);
		}
		$vendor		=	DB::table('vendor_tbl')
						->select('vendorid','categoryid','tierid')
						->where('vendorid',$order->vendorid)
						->first();
						
		$date 	= 	Carbon::createFromDate($year,$month,1)->toDateString();

		$rateid	=	DB::table('remuneration_rate_list')
					->whereDate('startDate','<=',$date)
					->where('endDate','>=',$date)
					->where('categoryid',$vendor->categoryid)
					->value('rateid');
						
		$resource	=	DB::table('eoi_resource_deployment as a')
						->select('a.deploymentid','a.orderid','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.role','a.experience as awdexperience','a.experiencelevel','a.qualification','a.deploymenttype','a.duration','a.operating','a.tax','a.admincharge as admin','a.lastdate','d.sectorname','e.consultantposition','e.experience','f.remuneration','mas.total_present','mas.total_absent','mas.total_leave','mas.total_holiday','mas.mpr_remark','mas.attendance_file','mas.supporting_file','mas.mpr_file')
						->leftJoin('mpr_resource as b', function($join) use ($mprid) {
							$join->on('b.deployment_id', '=', 'a.deploymentid');
							if($mprid>0){
								$join->where('b.mpr_id', '=', $mprid);
							}
						})
						->leftjoin('sector_tbl as d','d.sectorid','=','a.sectorid')
						->leftjoin('position_tbl as e','e.positionid','=','a.positionid')
						->leftJoin('remuneration_tbl as f', function ($join) use ($vendor,$rateid) {
							$join->where('f.categoryid', $vendor->categoryid)
								 ->where('f.tierid', $vendor->tierid)
								 ->where('f.rateid', $rateid);

							if ($vendor->categoryid == 2) {
								$join->on('f.sectorid', '=', 'a.sectorid')
									 ->on('f.positionid', '=', 'a.positionid');
							} elseif ($vendor->categoryid == 1) {
								$join->on('f.experiencelevel', '=', 'a.experiencelevel');
							}
						})
						->when(($vendor->categoryid == 2 || $vendor->categoryid == 1), function ($query) use ($mprid) {
							$query->leftJoin('mpr_attendance_summary as mas', function ($join) use ($mprid) {
								$join->on('mas.deployment_id', '=', 'a.deploymentid')
									 ->where('mas.mpr_id', '=', $mprid);
							});
						})
						->where('a.orderid',$orderid)
						->where('a.deploymentid',$deploymentid)
						->first();
			
		$resource->taxvalue		=	(($resource->remuneration*$resource->tax)/100);
		$resource->grandtotal	=	$this->formatIndianCurrency($resource->remuneration+$resource->taxvalue);
		$resource->remuneration	=	$this->formatIndianCurrency($resource->remuneration);
		$resource->taxvalue		=	$this->formatIndianCurrency($resource->taxvalue);		

		$joining_date			=	$resource->deployed_date;

		$result = DB::select("
			SELECT 
				DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY) AS work_date,
				DAY(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)) AS work_day,
				MONTH(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)) AS work_month,
				YEAR(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)) AS work_year,
				DATE_FORMAT(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY), '%a') AS day_name,
				CASE 
					WHEN DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY) < ? THEN 'NA'
					ELSE COALESCE(
						a.status,
						CASE WHEN DAYOFWEEK(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)) IN (1,7)
							THEN 'H'
							ELSE ''
						END
					)
				END AS status,
				a.remarks,
				a.mpr_id,
				a.deployment_id,
				a.mpr_detail
			FROM (
				SELECT @rownum := @rownum + 1 AS n
				FROM
					(SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
					 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d1,
					(SELECT 0 UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4
					 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) d2,
					(SELECT @rownum := -1) r
			) n
			LEFT JOIN mpr_attendance a
				ON a.work_date = DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)
				AND a.deployment_id = ?
				AND a.work_month = ?
				AND a.work_year = ?
			WHERE MONTH(DATE_ADD(DATE(CONCAT(?, '-', LPAD(?,2,'0'), '-01')), INTERVAL n.n DAY)) = ?
			ORDER BY work_date
		", [
			$year, $month,
			$year, $month,
			$year, $month,
			$year, $month,
			$year, $month,
			$year, $month,
			$joining_date,
			$year, $month,
			$year, $month,
			$deploymentid, $month, $year,
			$year, $month, $month
		]);
		
		$attendance = collect($result)->map(function($item) {
			return $item;
		});
		
		
		$resource->total_days	=	$attendance->count();
		$html	=	view('admin.vendors.ajaxpages.loadResourceMpr',['resource'=>$resource,'order'=>$order,'vendor'=>$vendor,'mprid'=>$mprid,'month'=>$month,'year'=>$year,'attendance'=>$attendance])->render();
		
		return response()->json(['status'=>200,'data'=>$html]);
    }
    public function loadMprResources(Request $request)
	{
		$vendorId = session('vendorId');
	
		$orderid	=	Crypt::decrypt($request->input('orderid'));

		$mprid		=	$request->input('mprid') ?? 0;
		$month		=	$request->input('month') ?? 0;
		$year		=	$request->input('year') ?? 0;
		$lastDate 	= 	Carbon::createFromDate($year, $month, 1)->endOfMonth()->toDateString();

		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.orderid','a.project_name as projecttitle','a.categoryid','a.vendorid','b.ispm')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('a.orderid',$orderid)
						->where('a.vendorid',Session('vendorId'))
						->first();

		if(!$order)
		{
			return response()->json(['status'=>400,'message'=>'Invalid order detail provided. Please check and try again!']);
		}
		/*
		$resources	=	DB::table('eoi_resource_deployment as a')
						->select('a.*','b.status','c.mpr_number','e.sectorname','f.consultantposition')
						->leftJoin('mpr_resource as b', function($join) use ($month, $year) {
							$join->on('b.deployment_id', '=', 'a.deploymentid')
								 ->where('b.mpr_month', '=', $month)
								 ->where('b.mpr_year', '=', $year);
						})
						->leftjoin('mpr_tbl as c','c.mpr_id','=','b.mpr_id')
						->leftjoin('sector_tbl as e','e.sectorid','=','a.sectorid')
						->leftjoin('position_tbl as f','f.positionid','=','a.positionid')
						->where('a.orderid',$orderid)
						->whereIn('a.deployment_status',['Active','Released'])
						->where('a.deployed_date','<=',$lastDate)
						->get();
		*/
		try
		{
			$vendor	=	DB::table('vendor_tbl')
						->select('vendorid','categoryid','tierid')
						->where('vendorid',$order->vendorid)
						->first();
						
						
			$date 	= 	Carbon::createFromDate($year,$month,1)->toDateString();

			$rateid	=	DB::table('remuneration_rate_list')
						->whereDate('startDate','<=',$date)
						->where('endDate','>=',$date)
						->where('categoryid',$vendor->categoryid)
						->value('rateid');
			
			$resources = DB::table('eoi_resource_deployment as a')
						->select('a.*','b.status','c.mpr_number','e.sectorname','f.consultantposition','g.remuneration','g.includingtax')
						->leftJoin('mpr_resource as b', function($join) use ($month, $year) {
							$join->on('b.deployment_id', '=', 'a.deploymentid')
								 ->where('b.mpr_month', '=', $month)
								 ->where('b.mpr_year', '=', $year);
						})
						->leftJoin('mpr_tbl as c','c.mpr_id','=','b.mpr_id')
						->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
						->leftJoin('remuneration_tbl as g', function ($join) use ($vendor,$rateid) {
							$join->where('g.categoryid', $vendor->categoryid)
								 ->where('g.tierid', $vendor->tierid)
								 ->where('g.rateid', $rateid);

							if ($vendor->categoryid == 2) {
								$join->on('g.sectorid', '=', 'a.sectorid')
									 ->on('g.positionid', '=', 'a.positionid');
							} elseif ($vendor->categoryid == 1) {
								$join->on('g.experiencelevel', '=', 'a.experiencelevel');
							}
						})
						->where('a.orderid', $orderid)
						->whereIn('a.deployment_status',['Active','Released','Extended'])
						->where('a.deployed_date', '<=', $lastDate)
						->where(function ($query) use ($month, $year) {
							$query->whereNull('a.lastdate')
								  ->orWhere(function ($q) use ($month, $year) {
									  $q->whereYear('a.lastdate', '<', $year)
										->orWhere(function ($qq) use ($month, $year) {
											$qq->whereYear('a.lastdate', '=', $year)
											   ->whereMonth('a.lastdate', '>=', $month);
										});
								  });
						})
						->get();		
							
			$html 		= 	view('admin.vendors.ajaxpages.loadMprResources',['resources'=>$resources,'order'=>$order,'mprid'=>$mprid,'month'=>$month,'year'=>$year])->render();
			
			return response()->json(['status'=>200,'data'=>$html]);
		}
		catch(Exception $e)
		{
			Log::error('Error : '.$e->getMessage());
		}
		
    }
	
    public function loadMprMonths(Request $request)
	{
	
		$orderid	=	Crypt::decrypt($request->input('orderid'));
		
		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.project_name')
						->leftJoin('project_tbl as b','b.projectid','=','a.projectid')
						->where('a.orderid',$orderid)
						->where('a.vendorid',Session('vendorId'))
						->first();
		
		if(!$order)
		{
			return response()->json(['status'=>400,'message'=>'Invalid order detail provided. Please check and try again!']);
		}
		
		try
		{
			/*
			$deployment	=	DB::table('eoi_resource_deployment')
								->where('orderid',$orderid)
								->where('deployed_date','>=',$order->orderdate)
								->orderby('deployed_date')
								->limit(1)
								->first();
			*/
			$deployment	=	DB::table('eoi_resource_deployment')
								->where('orderid',$orderid)
								->whereNotNull('deployed_date')
								->orderby('deployed_date')
								->limit(1)
								->first();
			
			if(!$deployment)
			{
				return response()->json(['status'=>500,'message'=>'Either the resource is not deployed or not verified by the department.']);
			}
			$startDate 	= 	Carbon::parse($deployment->deployed_date);

			$default	=	Carbon::create(2026, 1, 1, 0, 0, 0);
			$minDate    = 	Carbon::createFromFormat('Y-m-d', $order->orderdate);
			$minDate 	= 	$minDate->greaterThan($default) ? $minDate : $default;
			
			$current    = 	$startDate->greaterThan($minDate) ? $startDate->copy()->startOfMonth() : $minDate->copy()->startOfMonth();
			
			$defaultEnd	=	now();
			$endDate   	= 	Carbon::createFromFormat('Y-m-d', $order->workorderduedate);
			$endDate	=	$endDate->greaterThan($defaultEnd) ? $defaultEnd : $endDate;
			
			$months 	= 	collect();
			//$current 	=	$startDate->copy()->startOfMonth();
			
			while($current<=$endDate)
			{
				$month 	= 	$current->format('m');
				$year  	= 	$current->format('Y');
				if($month<date('m') && $year<=date('Y'))
				{
					$record	=	DB::table('mpr_tbl as a')
								->leftJoin('mpr_attendance_summary as b','b.mpr_id','=','a.mpr_id')
								->where('a.order_id','=',$deployment->orderid)
								->where('a.mpr_month', $month)
								->where('a.mpr_year', $year)
								->first();
					
					$label	= 	$record ? $record->mpr_status : 'Generate';
					$mprid	=	$record ? $record->mpr_id : 0;
					
					if($label=='Generate' || $label=='Pending')
					{
						$months->push([
							'month' 	=> 	$month,
							'month_name'=> 	$current->format('F'),
							'year' 		=> 	$year,
							'label' 	=>	$label,
							'mprid'		=>	$mprid
						]);
					}
				}
					$current->addMonth();
				
			}		
			$html = view('admin.vendors.ajaxpages.loadMprMonths',['months'=>$months,'order'=>$order,'orderid'=>$request->input('orderid')])->render();
			return response()->json(['status'=>200,'data'=>$html]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>500,'message'=>'Eigther resource not deployed or not verified by the department.']);
		}
		
    }
	
    public function manageVendorOrder(Request $request,$orderid)
	{
		
		$orderid	=	Crypt::decrypt($orderid);
		
		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.workorderamount','a.orderdate','a.refrence','a.project_name','a.project_duration','a.subuserid','c.departmentname',DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment WHERE orderid = a.orderid AND deployment_status = "Pending") as pending_count'),DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment WHERE orderid = a.orderid AND deployment_status = "Active") as active_count'),DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment WHERE orderid = a.orderid AND deployment_status = "Released") as released_count'))
						->leftJoin('eoi_request as b', 'b.requestid','=','a.requestid')
						->leftJoin('department_tbl as c', function ($join) {
							$join->on('c.userid', '=', DB::raw('CASE WHEN a.requestid = 0 THEN a.userid ELSE b.userid END'));
						})
						->where('a.orderid',$orderid)
						->where('a.vendorid',Session('vendorId'))
						->first();
		
		$order->workorderamount	=	$this->formatIndianCurrency(number_format($order->workorderamount,'2','.',''));
		
		$invoiced		=	DB::table('invoice_mpr')->where('order_id',$orderid)->sum('net_invoice_value');
		$order->invoiced=	$this->formatIndianCurrency(number_format($invoiced,'2','.',''));
		
		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
		
		$regularise	=	DB::table('regularisation')
						->where('order_id',$orderid)
						->get();
		
		return view('admin/vendors/manage_vendor', compact('order','subusers','regularise'));
    }

    public function updateVendorDeployment(Request $request,$orderid)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		
		$order	=	DB::table('eoi_work_order')->where('orderid',Crypt::decrypt($orderid))->first();
		$orderDate	=	date('d\-m\-Y',strtotime($order->orderdate));
		/*
		$rules = [
			'candidatename'   => 'array',
			'mobilenumber'    => 'array',
			'email'           => 'array',
			'deployeddate'    => 'array',
			
			'candidatename.*' => 'nullable|required_with:deployeddate.*|regex:/^[a-zA-Z.\s]+$/',
			
			'mobilenumber.*'  => 'nullable|digits:10',
			'email.*'         => 'nullable|email',
			'deployeddate.*'  => 'nullable|date|date_format:d-m-Y|after_or_equal:'.$orderDate,
		];
		*/
		$rules = [
			'candidatename'   => 'array',
			'mobilenumber'    => 'array',
			'email'           => 'array',
			'deployeddate'    => 'array',
			
			'candidatename.*' => 'nullable|required_with:deployeddate.*|regex:/^[a-zA-Z.\s]+$/',
			'mobilenumber.*'  => 'nullable|digits:10',
			'email.*'         => 'nullable|email:rfc,dns',
			'deployeddate.*'  => 'nullable|date|date_format:d-m-Y',
		];
		$messages = [
			'candidatename.array'           => 	'Candidate names must be an array',
			'candidatename.*.required_with' => 	'Candidate name is required when deployed date is provided.',
			'candidatename.*.regex'         =>	'Invalid candidate name',

			'mobilenumber.array'            => 	'Mobile numbers must be an array',
			'mobilenumber.*.required'      	=> 	'Mobile number is required',
			'mobilenumber.*.digits'         => 	'Invalid mobile number',

			'email.array'                   => 	'Emails must be an array',
			'email.*.required'            	=> 	'Email is required',
			'email.*.email'                 => 	'Invalid email address',

			'deployeddate.array'            => 	'Deployment dates must be an array',
			'deployeddate.*.date'           => 	'Invalid deployment date',
			'deployeddate.*.date_format'    => 	'Deployment date must be in d-m-Y format',
			'deployeddate.*.after_or_equal' => 	'Deployment date must be after or equal order date',
		];
		
		$validatedData 	= $request->validate($rules,$messages);
		
		DB::beginTransaction();
		try
		{
			$orderid	=	Crypt::decrypt($orderid);
			foreach($request->candidatename as $index => $name)
			{
				$deploymentid   = 	Crypt::decrypt($request->deploymentid[$index]) ?? null;
				$employee_code  = 	$request->employee_code[$index] ?? null;
				$mobilenumber   = 	$request->mobilenumber[$index] ?? null;
				$email          = 	$request->email[$index] ?? null;
				$deployeddate	=	$request->deployeddate[$index] ?? null;
				
				if($deployeddate)
				{
					$deployeddate	=	date('Y\-m\-d',strtotime($deployeddate));
				}
				
				$data = [
					'name'              => $name,
					'employee_code'     => $employee_code,
					'mobilenumber'      => $mobilenumber,
					'email'             => $email,
					'deployed_date'   	=> $deployeddate,
				];


				$exists	=	DB::table('eoi_resource_deployment')
							->where('orderid',$orderid)
							->where('deploymentid',$deploymentid)
							->first();

				if($exists)
				{
					if($exists->is_verified == 1)
					{
						// Only employee_code can be updated
						$data = [
							'name'          => $name,
							'employee_code' => $employee_code,
							'mobilenumber'  => $mobilenumber,
							'email'         => $email,
							'deployed_date' => $deployeddate,
						];
						if($exists->userid!=0)
						{
							DB::table('users_tbl')->where('userid',$exists->userid)->update([
								'name'			=>	$name ?? NULL,
								'mobilenumber'	=>	$mobilenumber ?? NULL,
								'email'			=>	$email ?? NULL,
							]);
							DB::table('resource_tbl')->where('userid',$exists->userid)->update([
								'employee_code'			=>	$employee_code ?? NULL,
								'joining_date'			=>	$deployeddate,
								'deployed_date'			=>	$deployeddate
							]);
						}
					}
					else
					{
						// Update all fields
						$data = [
							'name'          => $name,
							'employee_code' => $employee_code,
							'mobilenumber'  => $mobilenumber,
							'email'         => $email,
							'deployed_date' => $deployeddate,
						];
						if($exists->userid!=0)
						{
							DB::table('users_tbl')->where('userid',$exists->userid)->update([
								'name'			=>	$name ?? NULL,
								'mobilenumber'	=>	$mobilenumber ?? NULL,
								'email'			=>	$email ?? NULL,
							]);
							DB::table('resource_tbl')->where('userid',$exists->userid)->update([
								'employee_code'			=>	$employee_code ?? NULL,
								'joining_date'			=>	$deployeddate,
								'deployed_date'			=>	$deployeddate
							]);
						}
						
					}					
					
					$existingData 	= (array) $exists;
					$changedFields 	= [];
					foreach($data as $field	=>	$newValue)
					{
						$oldValue = $existingData[$field] ?? null;
						if($oldValue!=$newValue)
						{
							$changedFields[$field] = [
								'old' => $oldValue,
								'new' => $newValue,
							];
						}
					}
					if(!empty($changedFields))
					{
						DB::table('eoi_resource_deployment_logs')->insert([
							'orderid'        => $orderid,
							'deploymentid'   => $deploymentid,
							'changed_fields' => json_encode($changedFields),
							'updated_by'     => Session('userId'),
						]);
					}					
					DB::table('eoi_resource_deployment')
						->where('orderid', $orderid)
						->where('deploymentid',$deploymentid)
						->update($data);
				}
				else
				{
					$data['orderid']  =	$orderid;
				}
			}			
			DB::commit();
			return back()->with('success','Deployment details submitted successfully. Please note: once the deployment is confirmed by the department or project manager, further updates will not be allowed.');
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
		
    }
	
    public function viewDeployment(Request $request,$orderid)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		
		$orderid	=	Crypt::decrypt($orderid);

		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.departmentname','c.eoinumber')
						->leftjoin('department_tbl as b','b.userid','a.userid')
						->leftjoin('eoi_request as c','c.requestid','a.requestid')							
						->where('a.orderid',$orderid)
						->where('a.vendorid',Session('vendorId'))
						->first();

		$order 		= 	DB::table('eoi_work_order as a')
							->select('a.*','b.departmentname','c.eoinumber')
							->leftjoin('department_tbl as b','b.userid','a.userid')
							->leftjoin('eoi_request as c','c.requestid','a.requestid')							
							->where('a.orderid',$orderid)
							->first();


		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

		$detail 	= 	[];
		if($order->categoryid==2)
		{
			//$detail	=	$this->deploymentService->getCsfDeploymentData($order->requestid);
			$detail						=	$this->deploymentService->getCsfDeploymentData($order->orderid);
			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		}
		if($order->categoryid==1)
		{
			$detail	=	$this->deploymentService->getAwdDeploymentData($order->orderid);
			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
			
		}
		return view('admin/vendors/deployment_detail',compact('order','detail','vendor'));
    }
	
	public function showVendorOrder($orderid)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		$orderid=	Crypt::decrypt($orderid);

        Session::put('adminmenu','vendorworkorder');
		Session::put('adminsubmenu','vendorworkorder');
		
		$order 	= 	DB::table('eoi_work_order')->where('orderid', $orderid)->where('vendorid',Session('vendorId'))->first();
		$eoi 	= 	null;
		if($order && $order->requestid!=0)
		{
			$eoi = DB::table('eoi_request as a')
				->select('a.*', 'b.departmentname', 'b.address')
				->leftJoin('department_tbl as b', 'b.userid', '=', 'a.userid')
				->where('a.requestid', $order->requestid)
				->first();
		}
		$eoi 	= 	$eoi ?? (object) [];
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
		$detail = 	[];

		$detail	=	DB::table('eoi_request_detail as a')
						->select('a.*','b.sectorname','c.consultantposition','d.tiername','e.remuneration')
						->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
						->leftJoin('tiermaster_tbl as d','d.tierid','=','a.tierid')
						->leftJoin('remuneration_tbl as e', function ($join) {
							$join->on('e.sectorid', '=', 'a.sectorid')
								 ->whereColumn('e.positionid', '=', 'a.positionid')
								 ->whereColumn('e.tierid', '=', 'a.tierid');
						})
						->where('a.requestid', $order->requestid)
						->get();
		
		$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
		$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
		$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
		$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		
		DB::table('eoi_work_order')->where('orderid',$orderid)->where('vendorid',Session('vendorId'))->update([
			'markedbyvendor'	=>	Session('vendorId'),
			'markedby'			=>	Session('userName'),
			'markedon'			=>	now()
		]);
		
		return view('admin/vendors/work_order', compact('order', 'detail','vendor','eoi'));
	}
	
    public function getVendorWorkOrderData(Request $request)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;
		$deptid		 	=	$request->input('departmentid');
		$subuser_id		=	$request->input('subuser_id');
		$is_expired		=	$request->input('is_expired');
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
		
		$is_cancelled 	= 	($request->input('is_cancelled')==='true') ? 1 : 0;
		
		$subUserId		=	session('subUserId') ?? NULL;
		
        $data 			= 	DB::table('eoi_work_order as a')
								->select('a.*','b.jobcategory','c.companyname','c.contactperson','e.departmentname','e.shortname',DB::raw('CASE WHEN pd.orderid IS NULL THEN 1 ELSE 0 END as isDeployed'),'u.name',DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.name IS NOT NULL AND erd.deployment_date IS NOT NULL) as resourcedeployed'),DB::raw('(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid) as totalresources'))
								->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
								->leftJoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
								->leftJoin('eoi_request as d','d.requestid','=','a.requestid')
								->leftJoin('pending_deployment as pd', 'pd.orderid', '=', 'a.orderid')
								->leftJoin('users_tbl as u', 'u.userid', '=', 'a.subuserid')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.refrence', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.orderno', 'like', '%' . $pagesearch . '%')
										  ->orWhere('e.departmentname', 'like', '%' . $pagesearch . '%')
										  ->orWhere('e.shortname', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($vendorId!=0,function($query) use ($vendorId){
									return $query->where('a.vendorid','=',$vendorId);
								})
								->when($deptid!=0,function($query) use ($deptid){
									return $query->where('d.userid','=',$deptid);
								})
								->leftJoin('department_tbl as e', function ($join) {
									$join->on('e.userid', '=', DB::raw('CASE WHEN a.requestid = 0 THEN a.userid ELSE d.userid END'));
								})
								->when($subUserId!=NULL,function($query) use ($subUserId){
									return $query->where('a.subuserid',$subUserId);
								})
								->when($subuser_id!=NULL,function($query) use ($subuser_id){
									return $query->where('a.subuserid',$subuser_id);
								})
								->when($is_expired==3,function($query){
									return $query->whereDate('a.workorderduedate','>=',today())
									             ->where('a.isActiveOrder',1)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==1,function($query){
									return $query->whereDate('a.workorderduedate','<',today())
									             ->where('a.isActiveOrder',1)
												 ->where('a.isExtended',0)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==2,function($query){
									return $query->whereBetween('a.workorderduedate', [date('Y-m-d'),date('Y-m-d', strtotime('+60 days'))])
									             ->where('a.isActiveOrder',1)
												 ->where('a.isExtended',0)
												 ->where('a.isClosed',0);
								})
								->when($is_expired==4,function($query){
									return $query->where('a.isExtended',1);
								})
								->where('a.isCancelled',$is_cancelled)
								->orderBy('a.orderdate','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		foreach($data as $dt)
		{
			$dt->workorderamount	=	$this->formatIndianCurrency($dt->workorderamount);
			$dt->expiring 		= 	Carbon::parse($dt->workorderduedate)->format('d-m-Y');
			$dt->days_to_expire = 	Carbon::now()->diffInDays(Carbon::parse($dt->expiring), false);
		}
		
		return view('admin/vendors/ajaxpages/workorderTable', ['data' => $data]);

    }

    public function vendorWorkOrderList(Request $request)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','vendororders');
		Session::put('adminsubmenu','vendorworkorder');
		Session::put('menid',97);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=97 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$departments	=	DB::table('eoi_work_order as a')
							->select('c.userid','c.departmentid','c.departmentname','c.shortname')
							->join('department_tbl as c','c.departmentid','=','a.department_id')
							->distinct()
							->whereNotNull('a.orderdate')
							->where('a.vendorid',Session('vendorId'))
							->get();
		
		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
        return view('admin/vendors/workorder_list',compact('departments','subusers'));
    }
	
    public function floatedEoiList(Request $request)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','vendoreois');
		Session::put('adminsubmenu','vendoreoifloated');
		Session::put('menid',94);
		$userId		= $request->session()->get('userId');
		$vendorId	= $request->session()->get('vendorId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=94 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();


		$departments	=	DB::table('eoi_request_floated as a')
							->select('c.userid','c.departmentid','c.departmentname','c.shortname')
							->join('eoi_request as b','b.requestid','=','a.requestid')
							->join('department_tbl as c','c.userid','=','b.userid')
							->distinct()
							->where('a.vendorid',Session('vendorId'))
							->get();
		
		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
		
		
        return view('admin/vendors/floated_eoilist',compact('departments','subusers'));
    }

    public function floatedEoiData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;
		$departmentid 	=	$request->input('departmentid');
		$subuser_id 	=	$request->input('subuser_id') ?? NULL;
		/*
		$frmdate 	= 	date('Y-m-d', strtotime($request->input('frmdate')));
		$todate   	= 	date('Y-m-d', strtotime($request->input('todate')));
		$frmdate 	= 	Carbon::parse($frmdate)->startOfDay();
		$todate   	= 	Carbon::parse($todate)->endOfDay();
		*/
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize',100);
		$currentPage= 	$request->input('page', 1);
		
		$subUserId	=	Session('subUserId') ?? NULL;
		
		$data 		= 	DB::table('eoi_request_floated as a')
							->select('a.floatid','a.vendorid','a.floatdate','a.expirydate','a.presentationfile','a.signedcopyofeoi','e.*','b.name','b.mobilenumber','b.email','c.departmentname','d.jobcategory',DB::raw('CASE WHEN p.requestid IS NULL THEN 0 ELSE 1 END as isparticipated'),DB::raw('CASE WHEN q.vendorid IS NULL THEN 0 ELSE 1 END as inprebid'))
							->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
							->leftJoin('users_tbl as b','b.userid','=','e.userid')
							->leftJoin('department_tbl as c','c.userid','=','e.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','e.categoryid')
							->leftJoin('eoi_request_participation as p', function ($join) use ($vendorId) {
								$join->on('p.requestid', '=', 'e.requestid')
									 ->where('p.vendorid', '=', $vendorId);
							})
							->leftJoin('eoi_request_prebid as q', function ($join) use ($vendorId) {
								$join->on('q.requestid', '=', 'e.requestid')
									 ->where('q.vendorid', '=', $vendorId);
							})
							->when($pagesearch != '', function($query) use ($pagesearch) {
								return $query->where(function($q) use ($pagesearch) {
									$q->where('b.name', 'like', '%' . $pagesearch . '%')
									  ->orWhere('e.eoinumber', 'like', '%' . $pagesearch . '%')
									  ->orWhere('e.projecttitle', 'like', '%' . $pagesearch . '%');
								})->where('e.iscancelled', 0);
							})							
							->when($departmentid!=0,function($query) use ($departmentid){
								return $query->where('e.userid','=',$departmentid);
							})
							->when($subUserId!=NULL,function($query) use ($subUserId){
								return $query->where('a.subuserid',$subUserId);
							})
							->when($subuser_id!=NULL,function($query) use ($subuser_id){
								return $query->where('a.subuserid',$subuser_id);
							})
							->where('e.iscancelled','=',0)
							->where('e.eoistatus','>=',3)
							->where('a.vendorid','=',$vendorId)
							->where('a.floatdate','<=',now()->toDateString())
							->orderBy('e.creationdate','desc')
							->paginate($pagesize,['*'],'page',$currentPage);
		
		foreach($data as $da)
		{
			$da->totalamount	=	$this->formatIndianCurrency($da->totalamount);
		}
		
		return view('admin/vendors/ajaxpages/floatedeoiTable', ['data' => $data]);

    }
    public function floatedView(Request $request,$recordid)
	{
	
		$userId		= $request->session()->get('userId');
        Session::put('adminmenu','vendoreoifloated');
		Session::put('adminsubmenu','vendoreoifloated');
		$floatid 	=	Crypt::decrypt($recordid);
		
		$floated	=	DB::table('eoi_request_floated')->where('floatid','=',$floatid)->first();
		
		$requestid	=	$floated->requestid; //DB::table('eoi_request_floated')->where('floatid','=',$floatid)->value('requestid');
		
		$vendorId	=	$floated->vendorid; //DB::table('eoi_request_floated')->where('floatid','=',$floatid)->value('vendorid');

		$tierId		=	DB::table('vendor_tbl')->where('vendorid','=',$vendorId)->value('tierid');
			
		
        $data 		= 	DB::table('eoi_request_floated as a')
							->select('a.floatid','a.vendorid','a.floatdate','a.expirydate','a.presentationfile','a.isresumeuploaded','a.subuserid','f.tier_choice','f.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory')
							->leftJoin('eoi_request as f','f.requestid','=','a.requestid')
							->leftJoin('users_tbl as b','b.userid','=','f.userid')
							->leftJoin('department_tbl as c','c.userid','=','f.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','f.categoryid')
							->where('f.requestid','=',$requestid)
							->where('a.floatid','=',$floatid)
							->first();
		
		$admincharge	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->value('admincharge');

		$data->totalbudget		=	$this->formatIndianCurrency($data->totalbudget);
		$data->totaladmincharge	=	$this->formatIndianCurrency($data->totaladmincharge);
		$data->totalamount		=	$this->formatIndianCurrency($data->totalamount);
		
		$hasParticipated=	DB::table('eoi_request_participation')
									->where('requestid', $requestid)
									->where('userid', $userId)
									->exists();

		$hasPreBid	=	DB::table('eoi_request_prebid')
									->where('requestid', $requestid)
									->where('vendorid', $data->vendorid)
									->exists();

		$hasBroadcasted	=	DB::table('eoi_request_prebid_broadcast')
									->where('requestid', $requestid)
									->exists();
		
		
		DB::table('eoi_request_floated')->where('floatid',$floatid)->update([
			'isviewed'	=>	1
		]);
		
		$tiers	=	DB::table('tiermaster_tbl')->where('tierid',$tierId)->orderBy('tierid')->get();
		$rateid	=	DB::table('eoi_request_detail')->where('requestid',$requestid)->value('rateid');

		if($data->categoryid==2)
		{
			
			$tier_html = "";
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getCsfPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$rec->budget	=	($rec->budget+(($rec->budget*$pricing->tax)/100))*$rec->duration;
					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
				$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
				
				$tier_html = view('admin.viewpages.csf_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail,'tierid'=>$tier->tierid])->render();
				break;
			}
			
		}
		if($data->categoryid==1)
		{
			$tier_html = "";
			foreach($tiers as $tier)
			{
				$pricing	=	DB::table('pricing_tbl')->where('categoryid',$data->categoryid)->where('tierid',$tier->tierid)->first();
				$detail		=	$this->priceService->getAwdPrice($requestid,$data->categoryid,$tier->tierid,$rateid);
				$totalmanmonth		=	0;
				$adminchargetotal	=	0;
				$grandtotal			=	0;
				foreach($detail as $rec)
				{
					$rec->baseprice	=	$rec->budget;
					$budget			=	$rec->budget+(($rec->budget*$pricing->operatingmargin)/100);
					$budget			=	$budget+(($budget*$pricing->tax)/100);
					$rec->budget	=	$budget*$rec->duration;

					$totalmanmonth	=	$totalmanmonth+$rec->budget;
				}

				$adminchargetotal	=	round(($totalmanmonth*$pricing->admincharge)/100,2);
				$grandtotal			=	$totalmanmonth+$adminchargetotal;
				
				
				$data->totalmanmonth		=	$this->formatIndianCurrency($totalmanmonth);
				$data->adminchargetotal		=	$this->formatIndianCurrency($adminchargetotal);
				$data->grandtotal			=	$this->formatIndianCurrency($grandtotal);
				
				$tier_html = view('admin.viewpages.awd_tier_html',['pricing'=>$pricing,'data'=>$data,'detail'=>$detail,'tierid'=>$tier->tierid])->render();
				break;
			}
					
		}
		$attachments=	DB::table('eoi_request_attachment')->where('eoirequestid',$requestid)->get();
		
		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
		
		return view('admin/vendors/floatedeoi_view',compact('data','tier_html','hasParticipated','hasPreBid','hasBroadcasted','attachments','subusers'));

    }

    public function participateEoi(Request $request,$recordid)
	{    
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
        
        $rules = [
			'floatid' 		=> 'required',
        ];

        $messages = [
			'floatid.required' 	=> 'EoI DETAIL IS REQUIRED',
        ];
		$validatedData	=	$request->validate($rules, $messages);
		
		$requestid	=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($validatedData['floatid']))->value('requestid');
		
		$eoi		= 	DB::table('eoi_request')
						->where('requestid',$requestid)
						->where('deadlinedate','>=',now())
						->first();		
		if(!$eoi)
		{
			return back()
				->withInput()
				->withErrors([
					'deadlinedate' => 'The deadline for this request has passed.',
				]);
		}
		try
		{
			DB::beginTransaction();
			
			$floated	=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($validatedData['floatid']))->first();
			
			DB::table('eoi_request_participation')->insert([
				'userid'            =>	$request->session()->get('userId'),
				'participationdate' =>	now(),
				'requestid' 		=>	$floated->requestid,
				'floatid' 		 	=>	$floated->floatid,
				'vendorid' 		 	=>	$floated->vendorid,
				'subuserid'			=>	session('subUserId') ?? NULL
			]);
			
			DB::commit();

			Session::put('adminmenu','participatedeois');
			Session::put('adminsubmenu','participatedeois');
			Session::put('menid',94);
			$userId		= $request->session()->get('userId');
			$vendorId		= $request->session()->get('vendorId');
			$issuper	= $request->session()->get('issuper');
			if($issuper==0)
			{
				$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=94 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
				$actions	=	explode(",",$action->actions);
				Session::put('actions', $actions);
			}
			else
			{
				$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
				$actions=	explode(",",$action->actions);
				Session::put('actions', $actions);			
			}
			
			return redirect()->route('fillform.view', ['recordid' => $validatedData['floatid']])
							->with('success', '<i class="fa fa-hand-o-right"></i> Please proceed to upload the required resumes and/or presentation (PPT) at your earliest convenience.')
							->header('Cache-Control','no-cache,no-store, must-revalidate')
							->header('Pragma','no-cache')
							->header('Expires','0');
		}
        catch (QueryException $e) 
        {
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());
			return response()
				->view('admin/vendors/participated',['success'=>'YOUR PARTICIPATION REQUEST HAS BEEN ACCEPTED SUCCESSFULLY. NOW PLEASE GO TO <b>PARTICIPATED EoI</b> SECTION TO FILL CANDIDATE / EMPLOYEE DETAILS!'])
				->header('Cache-Control','no-cache, no-store, must-revalidate')
				->header('Pragma','no-cache')
				->header('Expires','0');			
        }
		
	}

    public function participatedEoiList(Request $request)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		
        Session::put('adminmenu','vendoreois');
		Session::put('adminsubmenu','participatedeois');
		Session::put('menid',94);
		
		$userId		=	$request->session()->get('userId');
		$vendorId	=	$request->session()->get('vendorId');
		$issuper	=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=94 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();

		$departments	=	DB::table('eoi_request_participation as a')
							->select('c.userid','c.departmentid','c.departmentname','c.shortname')
							->join('eoi_request as b','b.requestid','=','a.requestid')
							->join('department_tbl as c','c.userid','=','b.userid')
							->distinct()
							->where('a.vendorid',Session('vendorId'))
							->get();

		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
        return view('admin/vendors/participated_eoilist',compact('departments','subusers'));
    }

    public function participatedEoiData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;
		$departmentid 	=	$request->input('departmentid');
		$subuser_id 	=	$request->input('subuser_id') ?? NULL;
		$frmdate 		= 	date('Y-m-d', strtotime($request->input('frmdate')));
		$todate   		= 	date('Y-m-d', strtotime($request->input('todate')));
		$frmdate 		= 	Carbon::parse($frmdate)->startOfDay();
		$todate   		= 	Carbon::parse($todate)->endOfDay();

		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
		
		$subUserId		=	Session('subUserId') ?? NULL;
		
        $data 			= 	DB::table('eoi_request_floated as a')
							->select('a.floatid','a.vendorid','a.presentationfile','a.signedcopyofeoi','a.isresumeuploaded','a.floatdate','a.expirydate','a.isenabled','e.*','b.name','b.mobilenumber','b.email','c.departmentname','d.jobcategory',DB::raw('CASE WHEN q.vendorid IS NULL THEN 0 ELSE 1 END as inprebid'))
							->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
							->leftJoin('users_tbl as b','b.userid','=','e.userid')
							->leftJoin('department_tbl as c','c.userid','=','e.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','e.categoryid')
							->leftJoin('eoi_request_participation as p', function($join) use ($userId,$vendorId) {
								$join->on('p.floatid', '=','a.floatid')
									 ->on('p.vendorid', '=','a.vendorid');
							})						
							->leftJoin('eoi_request_prebid as q', function ($join) use ($vendorId) {
								$join->on('q.requestid', '=', 'e.requestid')
									 ->where('q.vendorid', '=', $vendorId);
							})
							->when($pagesearch != '', function($query) use ($pagesearch) {
								return $query->where(function($q) use ($pagesearch) {
									$q->where('b.name', 'like', '%' . $pagesearch . '%')
									  ->orWhere('e.eoinumber', 'like', '%' . $pagesearch . '%')
									  ->orWhere('e.projecttitle', 'like', '%' . $pagesearch . '%');
								})->where('e.iscancelled', 0);
							})							
							->when($departmentid!=0,function($query) use ($departmentid){
								return $query->where('e.userid','=',$departmentid);
							})
							->whereIn('a.requestid', function ($subquery) use($userId,$vendorId) {
								$subquery->select('requestid')->from('eoi_request_participation')->where('userid',$userId)->where('vendorid',$vendorId);
							})
							->when($subUserId!=NULL,function($query) use ($subUserId){
								return $query->where('a.subuserid',$subUserId);
							})
							->when($subuser_id!=NULL,function($query) use ($subuser_id){
								return $query->where('a.subuserid',$subuser_id);
							})
							->whereNotNull('a.signedcopyofeoi')
							->where('e.iscancelled',0)
							->where('a.vendorid',$vendorId)
							->orderBy('a.floateddate','DESC')
							->paginate($pagesize,['*'],'page',$currentPage);

		foreach($data as $da)
		{
			$da->totalamount	=	$this->formatIndianCurrency($da->totalamount);
		}
		
		return view('admin/vendors/ajaxpages/participatedeoiTable', ['data' => $data]);
    }

    public function candidateForm(Request $request,$recordid)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		$userId		= 	$request->session()->get('userId');
		$vendorId	= 	$request->session()->get('vendorId') ?? 0;
		
		$floated	=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($recordid))->first();
		
        $data 		= 	DB::table('eoi_request_floated as a')
							->select('a.floatid','a.vendorid','a.presentationfile','a.signedcopyofeoi','a.isresumeuploaded','a.otp_verified_on','e.*','b.name','b.mobilenumber','b.email','c.departmentname','c.designation','c.actiondatetime','d.jobcategory','d.categoryid as catid')
							->leftJoin('eoi_request as e','e.requestid','=','a.requestid')
							->leftJoin('users_tbl as b','b.userid','=','e.userid')
							->leftJoin('department_tbl as c','c.userid','=','e.userid')
							->leftJoin('jobcategory_tbl as d','d.categoryid','=','e.categoryid')
							->where('a.floatid','=',Crypt::decrypt($recordid))
							->first();

		
		$participation	=	DB::table('eoi_request_participation')
								->where('requestid','=',$floated->requestid)
								->where('userid','=',$request->session()->get('userId'))
								->where('vendorid','=',$vendorId)
								->first();
		
		if($data->categoryid==1)
		{
			$eoidata	=	DB::table('eoi_request_detail as a')
								->select('a.recordid','a.requestid','a.role','a.experience','a.qualification','a.duration','a.resumes','b.tiername','a.experiencelevel','d.workexperience','a.remark')
								->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
								->leftJoin('remuneration_tbl as c','c.remunerationid','=','a.remunerationid')
								->leftJoin('work_experience as d','d.experienceid','=','c.experienceid')
								->where('a.requestid','=',$participation->requestid)
								->get();
		}

		if($data->categoryid==2)
		{
			$eoidata		=	DB::table('eoi_request_detail as a')
									->select('a.recordid','a.requestid','a.role','a.experience','a.qualification','a.duration','a.resumes','a.remark','b.tiername','c.sectorname','d.consultantposition')
									->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
									->leftJoin('sector_tbl as c','c.sectorid','=','a.sectorid')
									->leftJoin('position_tbl as d','d.positionid','=','a.positionid')
									->where('a.requestid','=',$participation->requestid)
									->get();
		}
		foreach($eoidata as $resumes)
		{
			$resumes->resumes	=	DB::table('eoi_request_detail_resume')
										->where('recordid','=',$resumes->recordid)
										->where('userid','=',$floated->userid)
										->where('vendorid','=',$floated->vendorid)
										->get();
		}
		$prebidPublished	=	DB::table('eoi_request_prebid_broadcast')->where('requestid',$data->requestid)->count();
        return view('admin/vendors/uploadresume_form',compact('data','participation','eoidata','prebidPublished'));
    }

	public function uploadPpt(Request $request)
	{
		$floatid		=	Crypt::decrypt($request->input('floatid'));
		$floated		=	DB::table('eoi_request_floated')->where('floatid',$floatid)->first();

		if(!$floated->otp_verified_on)
		{
			return back()
				->withInput()
				->withErrors([
					'verification'	=>	'OTP verification for the proposal submission is pending.',
				]);
		}
		
		$categoryId		=	$request->input('categoryid');
		$categoryExists	=	DB::table('jobcategory_tbl')->where('categoryid', $categoryId)->exists();
		$requestExists	=	DB::table('eoi_request_floated')->where('floatid',$floated->floatid)->first();
		
		$rules = [
			'categoryid' => [
				'required',
				Rule::exists('jobcategory_tbl', 'categoryid'),
			],
			'ppt' => [
				($requestExists && $requestExists->presentationfile == '' && $categoryExists && $categoryId == 2) 
					? 'required' 
					: 'nullable',
				'file',
				'mimes:pdf,doc,docx,ppt,pptx',
				'max:51200', // max size in KB (50MB)
			],
			'signedcopyofeoi' => [
				($requestExists && $requestExists->signedcopyofeoi == '' && $categoryExists && $categoryId == 2) 
					? 'required' 
					: 'nullable',
				'file',
				'mimes:pdf,doc,docx',
				'max:15360', // max size in KB (15MB)
			],
			'contact_name' => [
				'required',
				'regex:/^[A-Za-z\s\.]+$/',
			],
			'contact_number' => [
				'required',
				'regex:/^[6-9]\d{9}$/',
			],
			'contact_email' => [
				'required',
				'email:rfc,dns',
			],
			'contact_name_2' => [
				'nullable',
				'regex:/^[A-Za-z\s\.]+$/',
			],
			'contact_number_2' => [
				'nullable',
				'regex:/^[6-9]\d{9}$/',
			],
			'contact_email_2' => [
				'nullable',
				'email:rfc,dns',
			],
			
		];

		$messages = [
			'categoryid.required' 		=> 'Category is required.',
			'categoryid.exists'   		=> 'Selected category does not exist.',
			'ppt.required'        		=> 'PPT is required for this category.',
			'ppt.mimes'           		=> 'PPT must be a PDF, DOC, or DOCX file.',
			'ppt.max'             		=> 'PPT must not be larger than 5MB.',
			'signedcopyofeoi.required'	=> 'Signed copy of EoI file is required.',
			'signedcopyofeoi.mimes'     => 'Signed copy of EoI must be a PDF, DOC, or DOCX file.',
			'signedcopyofeoi.max'       => 'Signed copy of EoI must not be larger than 5MB.',
			'contact_name.required' 	=> 'Contact person name is required.',
			'contact_name.regex' 		=> 'Contact person name may only contain letters, spaces, and dots (e.g., A.K. Sharma).',

			'contact_name_2.regex' 		=> 'Secondary contact person name may only contain letters, spaces, and dots (e.g., A.K. Sharma).',

			'contact_number.required' 	=> 'Contact number is required.',
			'contact_number.regex' 		=> 'Contact number must be a valid 10-digit mobile number.',			
			
			'contact_number_.regex' 	=> 'Secondary contact number must be a valid 10-digit mobile number.',			

			'contact_email.required' 	=> 'Email address is required.',
			'contact_email.regex' 		=> 'Email address must be a valid email address.',
			
			'contact_email_2.regex' 	=> 'Secondary email address must be a valid email address.',
		];

		$validatedData = $request->validate($rules, $messages);
		
		$eoi	=	DB::table('eoi_request')->where('requestid',$floated->requestid)->where('deadlinedate','>=',now())->first();
		
		if(!$eoi)
		{
			return back()
				->withInput()
				->withErrors([
					'deadlinedate'	=>	'The deadline for this request has passed.',
				]);
		}

		/*
		if($categoryId==1)
		{
		*/
			if($eoi->categoryid==2)
			{
				$invalidRecordIds	=	DB::table('eoi_request_detail as d')
											->leftJoin('eoi_request_detail_resume as r', function ($join) use($floated) {
												$join->on('d.recordid', '=', 'r.recordid')
													 ->where('r.vendorid', '=', $floated->vendorid);
											})
											->select('d.recordid',DB::raw('COUNT(r.resumeid) as resume_count'))
											->where('d.requestid',$floated->requestid)
											->groupBy('d.recordid')
											->havingRaw('resume_count<1')
											->pluck('d.recordid');
			}
			else
			{
				$invalidRecordIds	=	DB::table('eoi_request_detail as d')
											->leftJoin('eoi_request_detail_resume as r', function ($join) use($floated) {
												$join->on('d.recordid', '=', 'r.recordid')
													 ->where('r.vendorid', '=', $floated->vendorid);
											})
											->select('d.recordid',DB::raw('COUNT(r.resumeid) as resume_count'))
											->where('d.requestid',$floated->requestid)
											->groupBy('d.recordid')
											->havingRaw('resume_count<3')
											->pluck('d.recordid');
				
			}
			if(!$invalidRecordIds->isEmpty())
			{
				if($eoi->categoryid==2)
				return back()->with('fail','Resumes for each role/position is required.');
				else
				return back()->with('fail','At least 3 resumes are required for each resource.');
			}							
		/* } */
		$presentationfile	=	$request->file('ppt') ?? '';
		$signedcopyofeoi	=	$request->file('signedcopyofeoi') ?? '';
		if($categoryId==2)
		{
			if($presentationfile=='')
			{
				return back()->with('fail', 'For consultant presentation file is required.');
			}
			if($signedcopyofeoi=='')
			{
				return back()->with('fail', 'For consultant signed EoI file is required.');
			}
		}
		if($presentationfile!='')
		{
			$presentationfile= $request->file('ppt')->store('uploads/presentationfiles','public');
			DB::table('eoi_request_floated')
			->where('floatid',$floatid)
			->update([
				'presentationfile'	=>	$presentationfile
			]);
		}
		if($signedcopyofeoi!='')
		{
			$signedcopyofeoi= $request->file('signedcopyofeoi')->store('uploads/signedcopyofeoi','public');
			
			DB::table('eoi_request_floated')
			->where('floatid',$floatid)
			->update([
				'signedcopyofeoi'	=>	$signedcopyofeoi,
				'uploaded_at'		=>	now()
			]);
		}
		DB::table('eoi_request_floated')->where('floatid',$floatid)->update([
			'isresumeuploaded'	=>	1
		]);
		
		DB::table('eoi_request')->where('requestid',$floated->requestid)->update([
			'eoistatus'			=>	4,
			'isInterviewSet'	=>	1
		]);
		
		
		DB::table('eoi_request_participation')
		->where('requestid',$floated->requestid)
		->where('vendorid',$floated->vendorid)
		->update([
			'interviewdate'		=>	$eoi->interviewdate.' 10:00:00',
			'contact_person'	=>	$validatedData['contact_name'],
			'contact_number'	=>	$validatedData['contact_number'],
			'contact_email'		=>	$validatedData['contact_email'],
			'contact_person_2'	=>	$validatedData['contact_name_2'],
			'contact_number_2'	=>	$validatedData['contact_number_2'],
			'contact_email_2'	=>	$validatedData['contact_email_2'],
		]);
		
        return back()->with('success', 'You have successfully participated in the selected EoI.');
	}	


	public function uploadResumes(Request $request, $encryptedId)
	{
		$rules = [
			'name.*'   => 'required|max:50',
			'resume.*' => 'required|file|mimes:pdf|max:15360',
		];

		$messages = [
			'name.*.required'  => 'Name is required.',
			'resume.*.required'=> 'Resume file is required.',
			'resume.*.file'    => 'Invalid file.',
			'resume.*.mimes'   => 'Invalid file format. Allowed: pdf, doc, docx.',
			'resume.*.max'     => 'Max 15 MB is allowed.',
			'resume.*.uploaded'=> 'Resume could not be uploaded. File may be too large or corrupted.',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		if ($validator->fails()) {
			return response()->json([
				'errors' => $validator->errors()
			], 422);
		}
		
		try
		{
			$newFilesCount = 	count($request->file('resume') ?? []);
			if($newFilesCount==0)
			{
				return response()->json([
					'errors' => ['resume'=>["Please select resume file."]]
				], 422);				
			}
			$floatid	=	Crypt::decrypt($encryptedId);
			$floated	=	DB::table('eoi_request_floated')->where('floatid',$floatid)->first();
			$requestId	=	$floated->requestid;
			
			$eoi		=	DB::table('eoi_request')->where('requestid',$requestId)->first();
			
			if($eoi->categoryid==2)
			$required	=	1;
			else
			$required	=	3;
			
			if($eoi->requestid==145)
			{
				$required	=	6;
			}

			if($eoi->requestid==177)
			{
				$required	=	2;
			}
			
			$existingCount	=	DB::table('eoi_request_detail_resume')
								->where('recordid',Crypt::decrypt($request->input('recid')))
								->where('requestid',$floated->requestid)
								->where('vendorid',$floated->vendorid)
								->where('userid',$floated->userid)
								->count();



			$total = $existingCount + $newFilesCount;

			if($total<$required)
			{
				$need	=	$required-$total;
				return response()->json([
					'errors' => ['resume'=>["At least {$required} resumes are required for each resource."]]
				], 422);
			}

			if($eoi->requestid==177)
			{
				if($total>$required)
				{
					return response()->json([
						'errors' => ['resume'=>["You cannot upload more than {$required} resumes for each resource."]]
					], 422);
				}
			}

			
			if($eoi->categoryid==2)
			{
				if($eoi->requestid!=145 && $eoi->requestid!=177)
				{
					if($total>$required)
					{
						$previousDetail	=	DB::table('eoi_request_detail_resume')
											->where('recordid',Crypt::decrypt($request->input('recid')))
											->where('requestid',$floated->requestid)
											->where('vendorid',$floated->vendorid)
											->first();

						if($request->hasFile('resume'))
						{
							foreach ($request->file('resume') as $index => $file)
							{
								$path = $file->store('uploads/eoiresumes', 'public');

								DB::table('eoi_request_detail_resume')
								->where('recordid',Crypt::decrypt($request->input('recid')))
								->where('requestid',$floated->requestid)
								->where('userid',$floated->userid)
								->where('vendorid',$floated->vendorid)
								->update([
									'name' 			=> 	$request->input('name')[$index],
									'resume' 		=> 	$path,
									'uploaddate' 	=> 	now(),
									'uploadedby' 	=>	Session('userId'),
								]);
							}
						}
						DB::table('resumes_log')
						->insert([
							'resumeid'		=>	$previousDetail->resumeid,
							'recordid'		=>	$previousDetail->resumeid,
							'requestid'		=>	$previousDetail->requestid,
							'userid'		=>	$previousDetail->userid,
							'vendorid'		=>	$previousDetail->vendorid,
							'name'			=>	$previousDetail->name,
							'resume'		=>	$previousDetail->resume,
							'uploaddate'	=>	$previousDetail->uploaddate,
							'uploadedby'	=>	$previousDetail->uploadedby,
							'remark'		=>	$previousDetail->remark,
							'reviewstatus'	=>	$previousDetail->reviewstatus,
							'updated_at'	=>	$previousDetail->updated_at,
							'updatedby'		=>	$previousDetail->updatedby,
							'isordered'		=>	$previousDetail->isordered,
							'isresumeviewed'=>	$previousDetail->isresumeviewed,
						]);
						return response()->json(['message' => 'Resumes uploaded successfully','status'=>200]);					
					}
				}
			}

			// Insert uploaded files info into DB
			if($request->hasFile('resume'))
			{
				foreach ($request->file('resume') as $index => $file)
				{
					$path = $file->store('uploads/eoiresumes', 'public');

					DB::table('eoi_request_detail_resume')->insert([
						'recordid' => Crypt::decrypt($request->input('recid')),
						'requestid' => $floated->requestid,
						'userid' => $floated->userid,
						'vendorid' => $floated->vendorid,
						'name' => $request->input('name')[$index],
						'resume' => $path,
						'uploaddate' => now(),
						'uploadedby' => Session('userId'),
					]);
				}
			}

			return response()->json(['message' => 'Resumes uploaded successfully','status'=>200]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}
	}	


    public function prebidEnquiry(Request $request,$recordid,$questionid=NULL)
	{
		if(!session('vendorId'))
		{
			return redirect('dashboard');
		}
		$userId		= 	$request->session()->get('userId');
		$vendorId	= 	$request->session()->get('vendorId') ?? 0;
		
	
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$floated	=	DB::table('eoi_request_floated as a')
						->select('a.requestid','a.vendorid','b.prebidlastdate')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->where('a.floatid',Crypt::decrypt($recordid))
						->first();
		
		
		$queries=	DB::table('eoi_request_prebid')
							->where('vendorid',$vendorId)
							->where('requestid',$floated->requestid)
							->orderby('raisedon','DESC')
							->first();
		
		$hasPreBid	=	DB::table('eoi_request_prebid')
									->where('requestid',$floated->requestid)
									->where('vendorid',$floated->vendorid)
									->exists();

		$contact	=	DB::table('eoi_request_prebid')
									->where('requestid',$floated->requestid)
									->where('vendorid',$floated->vendorid)
									->first();
		
		$formats=	DB::table('prebid_formats')->where('recordid',1)->first();
		
		Session::put('float_id',$recordid);
		
		$query	=	DB::table('eoi_request_prebid_query')
					->where('requestid',$floated->requestid)
					->where('vendorid',$vendorId)
					->get();
		
		if($questionid)
		{
			Session::put('question_id',$questionid);
			$question	=	DB::table('eoi_request_prebid_query')->where('questionid',Crypt::decrypt($questionid))->first();
		}
		else
		{
			$question	=	NULL;
		}
		
        return view('admin/vendors/prebid_enquiry',compact('recordid','token','hasPreBid','contact','queries','formats','query','floated','question'));
    }

    public function storePreBidEnquiry(Request $request,$recordid)
	{    
        
        $rules = [
			'attachment' 	=> 'required|file|mimes:pdf,doc,docx|max:10240',
        ];

        $messages = [
			'attachment.file' 			=> 'Invalid file type',
			'attachment.mimes' 			=> 'Invalid file type',
			'attachment.max' 			=> 'Maximum file size allowed is 10 MB.',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		


        $currentDateTime= now();
        $creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		$floated		=	DB::table('eoi_request_floated')->where('floatid',Crypt::decrypt($recordid))->first();
		if(!$floated)
		{
			return back()->with('fail','Invalid data provided.')->withInput();
		}
		//$attachment    	= 	$request->file('attachment');
		
		$attachment    	= 	$request->file('attachment');
		$extension		=	$attachment->getClientOriginalExtension();
		$companyName 	= 	DB::table('vendor_tbl')->where('userid',Session('userId'))->value('companyname');
		$filename 		= 	preg_replace('/[^A-Za-z0-9 ]/', '', $companyName);
		$filename 		= 	str_replace(' ', '_', $filename).rand(1000,9999);
		$filename .= '.' . $extension;
		$path 			= 	$attachment->storeAs('uploads/prebidenquiry', $filename, 'public');
		$attachment 	= 	$path;
		
		/*
		if($attachment!='')
		{
			$attachment= $request->file('attachment')->store('uploads/prebidenquiry','public');
		}
		*/
		try 
		{
			DB::table('eoi_request_prebid')->insert([
				'requestid'		=>	$floated->requestid,
				'vendorid'		=>	$floated->vendorid,
				'prebidquery'	=>	'',
				'attachment'	=>	$attachment ?? '',
				'raisedon'		=>	now(),
				'prebidstatus'	=>	'PENDING',
				'isprebid'		=>	1
			]);
			DB::table('eoi_request')->where('requestid',$floated->requestid)->update(['isinprebid'=>1]);
			return back()->with('success','Pre-bid enquiry stored successfully!');
		}
		catch(QueryException $e)
		{
			if($attachment!='')
			Storage::disk('public')->delete($attachment);
			
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }

    public function viewPreBidResponse(Request $request,$recordid)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		$userId		= $request->session()->get('userId');
		$vendorId	= $request->session()->get('vendorId') ?? 0;
		
		$broadcastid=	Crypt::decrypt($recordid);
		
		$exists	=	DB::table('eoi_request_prebid_broadcast')->where('broadcastid',$broadcastid)->first();
		
		if(!$exists)
		{
			return back()->with('duplicate','Invalid detail provided')->withInput();
		}
		$requestId	=	$exists->requestid;
		$data		=	DB::table('eoi_request_prebid_broadcast')->where('requestid',$requestId)->orderBy('broadcastid','desc')->get();

		$eoi		=	DB::table('eoi_request')->select('requestid','projecttitle','eoinumber')->where('requestid',$requestId)->first();
		
		$queries	=	DB::table('eoi_request_prebid_query')
						->where('requestid',$requestId)
						->orderBy('questionid')
						->get();
		
		return view('admin/vendors/viewprebid_response',compact('data','eoi','queries'));
    }

    public function removeResume(Request $request)
	{
		DB::beginTransaction();
		try
		{
			$resumeid	=	Crypt::decrypt($request->input('resumeid'));
			$resume		=	DB::table('eoi_request_detail_resume')->where('resumeid',$resumeid)->first();

			$deadlinedate	=	DB::table('eoi_request')->where('requestid',$resume->requestid)->value('deadlinedate');
			if(Carbon::now()->gte(Carbon::parse($deadlinedate)))
			{
				return response()->json(['status'=>400,'message'=>'The proposal submission deadline has passed. You can no longer delete resumes.']);
			}

			$interviewdate	=	DB::table('eoi_request')->where('requestid',$resume->requestid)->value('interviewdate');
			if(!empty($interviewdate))
			{
				$interview = Carbon::parse($interviewdate);
				if(Carbon::today()->gte($interview))
				{
					return response()->json(['status'=>400,'message'=>'The interview date has passed. You can no longer delete resumes, but you can still add more resumes.']);
				}
			}
			if($resume->resume!='')
			{
				Storage::disk('public')->delete($resume->resume);
			}
			DB::table('eoi_request_detail_resume')->where('resumeid',$resumeid)->delete();
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Resume removed successfully']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
		
			return back()->with('duplicate',$e->getMessage())->withInput();
		}

    }

    public function showMprLog($mprid)
    {
        $logs = $this->logServices->getMprLog($mprid);

        return response()->json($logs);
    }	

    public function showInterest(Request $request,$recordid)
	{
		$userId		= 	$request->session()->get('userId');
		
		$vendorId	= 	$request->session()->get('vendorId') ?? 0;
		
		$floatid	=	Crypt::decrypt($recordid);
		
		try
		{
			$float	=	DB::table('eoi_request_floated')->where('floatid',$floatid)->first();
			if(!$float)
			{
				return response()->json([
					'status'  => 422,
					'message' => 'Invalid detail provided'
				], 422);
				
			}
			$exists	=	DB::table('interested_vendor_eoi')->where('vendorid',$vendorId)->where('requestid',$float->requestid)->exists();
			if(!$exists)
			{
				DB::table('interested_vendor_eoi')->insert([
					'requestid'		=>	$float->requestid,
					'vendorid'		=>	$vendorId,
					'markedon'		=>	now(),
					'subuserid'		=>	session('subUserId') ?? NULL
				]);
				return response()->json(['status'=>200,'message'=>'Thank you! You have successfully indicated your interest.<br><br>Please note this does not confirm participation.']);
			}
			else
			{
				return response()->json([
					'status'  => 500,
					'message' => 'You`ve already shown interest in this EoI. <b>Thank you!</b>'
				], 500);
				
			}
		}
		catch(Exception $e)
		{
			return response()->json([
				'status'  => 500,
				'message' => 'You`ve already shown interest in this EoI. Thank you!'
			], 500);
		}
		

    }


    public function assignEoIToUser(Request $request)
	{
		$rules = [
			'floatid' 		=> 	'required',
			'subuserid'		=>	'required|numeric',
		];
		
		$messages = [
			'floatid.required'    => 'EOI details are required.',
			'subuserid.required'  => 'Please select a user.',
			'subuserid.numeric'   => 'Please select a valid user.',
		];		
        $validatedData = $request->validate($rules,$messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
		
		$floatid	=	Crypt::decrypt($validatedData['floatid']);
		
		DB::beginTransaction();
		try 
		{
			$float	=	DB::table('eoi_request_floated')->where('floatid',$floatid)->first();
			
			if($float->vendorid!=session('vendorId'))
			{
				return response()->json(['status'=>400,'message'=>'The provided EoI details are invalid.']);
			}
			
			if(!$float->subuserid)
			{
				DB::table('eoi_request_floated')->where('floatid',$floatid)->update([
					'subuserid'	=>	$validatedData['subuserid']
				]);
			}
			else
			{
				if($float->subuserid==$validatedData['subuserid'])
				{
					return response()->json(['status'=>400,'message'=>'Please select another user to assign the EOI.']);
				}
				else
				{
					if($float->activity)
					{
						$fromUser = DB::table('users_tbl')->select('name')->where('userid', $float->subuserid)->first();

						$toUser = DB::table('users_tbl')->select('name')->where('userid', $validatedData['subuserid'])->first();

						$fromName	=	$fromUser ? $fromUser->name : 'N/A';
						$toName		= 	$toUser ? $toUser->name : 'N/A';

						$activity = $float->activity . "<br>Moved by "
							. session('userName')
							. " on "
							. date('d-m-Y, h:i A')
							. " from "
							. $fromName
							. " to "
							. $toName;
					}
					else
					{
						$activity = "Assigned by "
							. session('userName')
							. " on "
							. date('d-m-Y, h:i A');
					}
					
					DB::table('eoi_request_floated')->where('floatid',$floatid)->update([
						'subuserid'	=>	$validatedData['subuserid'],
						'activity'	=>	$activity
					]);
					
				}
			}
			
			DB::commit();
			return response()->json(['status'=>200,'message'=>'The EOI has been assigned to the selected user.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function assignOrderToUser(Request $request)
	{
		$rules = [
			'orderid' 		=> 	'required',
			'subuserid'		=>	'required|numeric',
		];
		
		$messages = [
			'orderid.required'    => 'Order details are required.',
			'subuserid.required'  => 'Please select a user.',
			'subuserid.numeric'   => 'Please select a valid user.',
		];		
        $validatedData = $request->validate($rules,$messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
		
		$orderid	=	Crypt::decrypt($validatedData['orderid']);
		
		DB::beginTransaction();
		try 
		{
			$order	=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
			
			if($order->vendorid!=session('vendorId'))
			{
				return response()->json(['status'=>400,'message'=>'The provided Order details are invalid.']);
			}
			
			if(!$order->subuserid)
			{
				$activity = "Assigned by ". session('userName'). " on ". date('d-m-Y, h:i A');
				
				DB::table('eoi_work_order')->where('orderid',$orderid)->update([
					'subuserid'	=>	$validatedData['subuserid'],
					'activity'	=>	$activity
				]);
			}
			else
			{
				if($order->subuserid==$validatedData['subuserid'])
				{
					return response()->json(['status'=>400,'message'=>'Please select another user to assign the EOI.']);
				}
				else
				{
					if($order->activity)
					{
						$fromUser = DB::table('users_tbl')->select('name')->where('userid', $order->subuserid)->first();

						$toUser = DB::table('users_tbl')->select('name')->where('userid', $validatedData['subuserid'])->first();

						$fromName	=	$fromUser ? $fromUser->name : 'N/A';
						$toName		= 	$toUser ? $toUser->name : 'N/A';

						$activity = $order->activity . "<br>Moved by "
							. session('userName')
							. " on "
							. date('d-m-Y, h:i A')
							. " from "
							. $fromName
							. " to "
							. $toName;
					}
					else
					{
						$activity = "Assigned by "
							. session('userName')
							. " on "
							. date('d-m-Y, h:i A');
					}
					
					DB::table('eoi_work_order')->where('orderid',$orderid)->update([
						'subuserid'	=>	$validatedData['subuserid'],
						'activity'	=>	$activity
					]);
					
				}
			}
			
			DB::commit();
			return response()->json(['status'=>200,'message'=>'The order has been assigned to the selected user.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }


	public function replacePresentationFile(Request $request)
	{
		$request->merge([
			'floatid'     => Crypt::decrypt($request->floatid)
		]);
		
		$rules = [
			'floatid'	=>	'required|exists:eoi_request_floated,floatid',
			'ppt' 		=>	'required|file|mimes:pdf|max:15360',
		];

		$messages = [
			'floatid.required' => 'Invalid firm detail provided.',
			'floatid.exists'   => 'The selected firm does not exist.',

			'ppt.required' =>	'Please upload the presentation file.',
			'ppt.file'     => 	'The uploaded file must be a valid file.',
			'ppt.mimes'    => 	'Only PDF files are allowed.',
			'ppt.max'      => 	'The file size must not exceed 15MB.',
		];
		
		$validatedData 	= 	$request->validate($rules,$messages);
		
		try
		{
			
			if($request->hasFile('ppt'))
			{
				$float	=	DB::table('eoi_request_floated')->where('floatid',$validatedData['floatid'])->first();
				
				Storage::disk('public')->delete($float->presentationfile);
				
				$file 	=	$request->file('ppt');
				$ppt	= 	$file->store('uploads/presentationfiles','public');
				
				DB::table('eoi_request_floated')
				->where('floatid',$validatedData['floatid'])
				->update([
					'presentationfile'	=>	$ppt ?? NULL
				]);
			}			

			return response()->json(['message' => 'Presentation file replaced successfully','status'=>200]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
	}	

	public function replaceSignedCopyOfEoi(Request $request)
	{
		$request->merge([
			'floatid'     => Crypt::decrypt($request->floatid)
		]);
		
		$rules = [
			'floatid'			=>	'required|exists:eoi_request_floated,floatid',
			'signedcopyofeoi' 	=>	'required|file|mimes:pdf|max:15360',
		];

		$messages = [
			'floatid.required' 			=>	'Invalid firm detail provided.',
			'floatid.exists'   			=> 	'The selected firm does not exist.',

			'signedcopyofeoi.required' 	=>	'Please upload the presentation file.',
			'signedcopyofeoi.file'     	=> 	'The uploaded file must be a valid file.',
			'signedcopyofeoi.mimes'    	=> 	'Only PDF files are allowed.',
			'signedcopyofeoi.max'      	=> 	'The file size must not exceed 15MB.',
		];
		
		$validatedData 	= 	$request->validate($rules,$messages);
		
		try
		{
			
			if($request->hasFile('signedcopyofeoi'))
			{
				$float	=	DB::table('eoi_request_floated')->where('floatid',$validatedData['floatid'])->first();
				
				Storage::disk('public')->delete($float->signedcopyofeoi);
				
				$file 			=	$request->file('signedcopyofeoi');
				$signedcopyofeoi= 	$file->store('uploads/signedcopyofeoi','public');
				
				DB::table('eoi_request_floated')
				->where('floatid',$validatedData['floatid'])
				->update([
					'signedcopyofeoi'	=>	$signedcopyofeoi ?? NULL
				]);
			}			

			return response()->json(['message' => 'Signed EoI file replaced successfully','status'=>200]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
	}	

    public function downloadPreBidResponse(Request $request,$requestid)
	{
		try
		{
			$userId	=	$request->session()->get('userId');
			$vendorId	=	$request->session()->get('vendorId');
			
			$requestid	=	Crypt::decrypt($requestid);

			$exists	=	DB::table('eoi_request_floated')->where('requestid',$requestid)->where('userid',$userId)->where('vendorid',$vendorId)->exists();
			if(!$exists)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}

			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate','projecttitle')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}

			
			$ispassed = 0;
			$today = Carbon::today();
			$prebidDate = Carbon::parse($eoi->prebidlastdate);

			if($prebidDate->lt($today))
			{
				$ispassed = 1;
			}
			else
			{
				$ispassed = 0;
			}
			
			$data	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$requestid)
									->where('isprebid',1)
									->orderBy('questionid')
									->get();
							

			$isForwarded	=	DB::table('eoi_request_prebid_forwarded')
								->where('requestid',$requestid)
								->where('postedby','CHiPS')
								->where('isprebid',1)
								->first();

			$replied	=	DB::table('eoi_request_prebid_forwarded')
							->where('requestid', $requestid)
							->where('isprebid',1)
							->where(function ($query) {
								$query->where('postedby', 'DEPARTMENT')
									  ->orWhere('postedby', 'PROJECT MANAGER');
							})
							->first();


			$brodcasted	=	DB::table('eoi_request_prebid_broadcast')
								->where('requestid',$requestid)
								->where('isprebid',1)
								->first();

			
			
			$html	=	PDF::loadView('admin.vendors.ajaxpages.downloadprebidresponse',compact('data','eoi'))->setPaper('A4','landscape');
			$filename	=	$eoi->eoinumber."_prebid.pdf";
			
			return $html->download($filename);
		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function downloadVendorPreBid(Request $request,$requestid)
	{
		try
		{
			$userId		=	$request->session()->get('userId');
			$vendorId	=	$request->session()->get('vendorId');
			
			$requestid	=	Crypt::decrypt($requestid);

			$exists	=	DB::table('eoi_request_floated')->where('requestid',$requestid)->where('userid',$userId)->where('vendorid',$vendorId)->exists();
			if(!$exists)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}

			
			$eoi	=	DB::table('eoi_request')->select('requestid','eoinumber','prebidlastdate','deadlinedate','interviewdate','projecttitle')->where('requestid',$requestid)->first();
			if(!$eoi)
			{
				return response()->json(['status'=>400,'message'=>'Invalid EoI details provided.']);
			}

			
			$ispassed = 0;
			$today = Carbon::today();
			$prebidDate = Carbon::parse($eoi->prebidlastdate);

			if($prebidDate->lt($today))
			{
				$ispassed = 1;
			}
			else
			{
				$ispassed = 0;
			}
			
			$data	=	DB::table('eoi_request_prebid as a')
							->select('a.requestid','a.vendorid','a.attachment','a.raisedon','b.companyname','a.prebidstatus')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->where('a.requestid',$eoi->requestid)
							->where('a.vendorid',$vendorId)
							->where('a.isprebid',1)
							->orderBy('a.queryid')
							->get();
							

			foreach($data as $query)
			{
				$query->queries	=	DB::table('eoi_request_prebid_query')
									->select('clause_reference','clause_detail','queries_with_justification','answer')
									->where('requestid',$query->requestid)
									->where('vendorid',$query->vendorid)
									->where('isprebid',1)
									->orderBy('questionid')
									->get();
			}
							

			$html	=	PDF::loadView('admin.vendors.ajaxpages.downloadvendorprebid',compact('data','eoi'))->setPaper('A4','landscape');
			$filename	=	$eoi->eoinumber."_query.pdf";
			
			return $html->download($filename);
		}
		catch(QueryException $e)
		{
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
    }

    public function orderWiseResource(Request $request)
	{
        Session::put('adminmenu','vendororders');
		Session::put('adminsubmenu','vendororderresources');
		Session::put('menid',152);
		$userId		= $request->session()->get('userId');
		$vendorId		= $request->session()->get('vendorId');
		$issuper	= $request->session()->get('issuper');

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$vendor	=	DB::table('vendor_tbl')->select('vendorid','categoryid','companyname')->where('vendorid',$vendorId)->first();
		
		$departmentids	=	DB::table('eoi_work_order')->where('vendorid',$vendorId)->pluck('department_id');
		
		$departments=	DB::table('users_tbl as a')
						->select('a.isdepartment','a.ispm','b.departmentid','b.departmentname','b.shortname')
						->join('department_tbl as b','b.userid','=','a.userid')
						->whereIn('b.departmentid',$departmentids)
						->orderby('a.name')
						->get();

	
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')->select('experiencelevel')->where('experiencelevel','!=',0)->distinct('experiencelevel')->orderBy('experiencelevel')->get();
		
		return view('admin/vendors/orderwise_resource',compact('vendor','departments','sectors','positions','levels','token'));
    }

    public function getOrderWiseResource(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$vendorId			= 	$request->session()->get('vendorId');
		$vendor	=	DB::table('vendor_tbl')->select('vendorid','categoryid','companyname')->where('vendorid',$vendorId)->first();
		
		$departmentid	=	$request->input('departmentid');
		$sectorid		=	$request->input('sectorid') ?? NULL;
		$positionid		=	$request->input('positionid') ?? NULL;
		$experiencelevel=	$request->input('experiencelevel') ?? NULL;
		$order_type		=	$request->input('order_type');
		$pagesearch		=	$request->input('pagesearch');
		
		$data	=	$this->deploymentService->getOrderWiseResource($request,$vendor->categoryid,$vendor->vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$order_type,$pagesearch);

		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')->select('experiencelevel')->where('experiencelevel','!=',0)->distinct('experiencelevel')->orderBy('experiencelevel')->get();
		
		return view('admin/vendors/ajaxpages/orderwiseresourceTable', ['data' => $data,'sectors'=>$sectors,'positions'=>$positions,'levels'=>$levels]);

    }


    public function replaceResourceInOrder(Request $request,$deploymentid=NULL)
	{
        Session::put('adminmenu','vendororders');
		Session::put('adminsubmenu','vendororderresources');
		Session::put('menid',152);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$deploymentid	=	Crypt::decrypt($deploymentid);
		$deployment		=	DB::table('eoi_resource_deployment as a')
							->select('a.*','b.sectorname','c.consultantposition')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->where('a.deploymentid',$deploymentid)
							->first();
		
		$order		=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.categoryid','a.project_name','b.eoinumber','b.projectduration','c.departmentname','d.companyname','d.officialemail','e.ispm')
						->leftJoin('eoi_request as b','b.requestid','=','a.requestid')
						->leftJoin('department_tbl as c','c.departmentid','=','a.department_id')
						->leftJoin('vendor_tbl as d','d.vendorid','=','a.vendorid')
						->leftJoin('users_tbl as e','e.userid','=','c.userid')
						->where('a.orderid','=',$deployment->orderid)
						->first();
		if(!$order)
		{
			return back()->with('fail', 'Order details not found. Please check the order details and try again.');
		}

		
		$sectors	=	DB::table('sector_tbl')->select('sectorid','sectorname')->orderBy('sectorname')->get();
		
		$positions	=	DB::table('position_tbl')->select('positionid','consultantposition')->orderBy('consultantposition')->get();
		
		$levels		=	DB::table('remuneration_tbl')
						->select('experiencelevel')
						->where('experiencelevel','!=',0)
						->distinct('experiencelevel')
						->orderBy('experiencelevel')
						->get();
		
		return view('admin/vendors/resource_replacement',compact('sectors','positions','levels','token','deployment','order'));
    }

	public function storeResourceReplacement(Request $request)
	{
		$rules = [
			'release_date'  	=> 	'nullable|date_format:d-m-Y',
			'deploymentid' 		=> 	'required|exists:eoi_resource_deployment,deploymentid',
			'employee_code' 	=> 	'nullable|max:100',
			'name'          	=> 	'nullable|max:150',
			'mobilenumber'  	=> 	'nullable|digits:10',
			'email' 			=> 	'nullable|email|max:150',
			'joining_date' 		=> 	'nullable|date_format:d-m-Y',
			'experiencelevel'   => 	'nullable|numeric',
			'role'   			=> 	'nullable',
			'sectorid'      	=> 	'nullable|numeric',
			'positionid'    	=> 	'nullable|numeric',
		];

		$messages = [
			'release_date.date_format'	=> 	'Release date must be in dd-mm-YYYY format.',

			'deploymentid.required'    	=> 	'Deployment ID is required.',
			'deploymentid.exists'      	=> 	'Invalid deployment selected.',

			'employee_code.required'   	=> 	'Employee code is required.',
			'employee_code.max'        	=> 	'Maximum 100 characters allowed in employee code.',

			'name.required'            	=> 	'Employee name is required.',
			'name.max'                 	=> 	'Maximum 150 characters allowed in name.',

			'mobilenumber.required'    	=> 	'Mobile number is required.',
			'mobilenumber.digits'      	=> 	'Mobile number must be 10 digits.',

			'email.required'           	=> 	'Email address is required.',
			'email.email'              	=> 	'Invalid email address provided.',
			'email.max'                	=> 	'Maximum 150 characters allowed in email.',

			'joining_date.date_format' 	=> 	'Joining date must be in dd-mm-YYYY format.',

			'experiencelevel.numeric'  	=> 	'Invalid sector selected.',
			'sectorid.numeric'         	=> 	'Invalid sector selected.',
			'positionid.numeric'       	=> 	'Invalid position selected.',
		];

		$validatedData = $request->validate($rules, $messages);
		if($validatedData['joining_date']!='')
		{
			$validatedData['joining_date']	=	date('Y\-m\-d',strtotime($validatedData['joining_date']));
		}
		try
		{
			$plainPassword	= 	$this->passwordService->generatePassword();
			DB::beginTransaction();
			$supporting_document	=	"";
			if($request->hasFile('supporting_document'))
			{
				$supporting_document= $request->file('supporting_document')->store('uploads/supporting_files','public');
			}

			$deployment		=	DB::table('eoi_resource_deployment')->where('deploymentid',$validatedData['deploymentid'])->first();

			$order			=	DB::table('eoi_work_order')->where('orderid',$deployment->orderid)->first();

			$user_id		=	DB::table('users_tbl')->insertGetId([
									'name'			=>	$validatedData['name'] ?? NULL,
									'mobilenumber'	=>	$validatedData['mobilenumber'] ?? NULL,
									'email'			=>	$validatedData['email'] ?? NULL,
									'password'		=>	Hash::make($plainPassword),
									'pass_word'		=>	$plainPassword,
								]);
			
			DB::table('resource_tbl')->insert([
				'orderid'			=>	$deployment->orderid,
				'userid'			=>	$user_id,
				'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
				'categoryid'		=>	$order->categoryid,
				'tierid'			=>	$deployment->tierid,
				'sectorid'			=>	$validatedData['sectorid'] ?? 0,
				'positionid'		=>	$validatedData['positionid'] ?? 0,
				'role'				=>	$deployment->role,
				'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
				'remuneration'		=>	$deployment->remuneration,
				'operating'			=>	$deployment->operating,
				'operatingvalue'	=>	$deployment->operatingvalue,
				'tax'				=>	$deployment->tax,
				'taxvalue'			=>	$deployment->taxvalue,
				'admin'				=>	$deployment->admincharge,
				'admincharge'		=>	$deployment->adminvalue,
				'deployed_date'		=>	$validatedData['joining_date'] ?? NULL,
				'joining_date'		=>	$validatedData['joining_date'] ?? NULL,
				'deploymenttype'	=>	$deployment->deploymenttype,
				'rateid'			=>	$deployment->rateid
			]);
			
			$new_deployment_id	=	DB::table('eoi_resource_deployment')
									->insertGetId([
										'orderid'			=>	$deployment->orderid,
										'userid'			=>	$user_id,
										'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
										'name'				=>	$validatedData['name']	?? NULL,
										'mobilenumber'		=>	$validatedData['mobilenumber']	?? NULL,
										'email'				=>	$validatedData['email']	?? NULL,
										'deployed_date'		=>	$validatedData['joining_date'] ?? NULL,
										'deployment_status'	=>	'Pending',
										'tierid'			=>	$deployment->tierid,
										'sectorid'			=>	$validatedData['sectorid'] ?? 0,
										'positionid'		=>	$validatedData['positionid'] ?? 0,
										'role'				=>	$validatedData['role'] ?? 0,
										'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
										'deploymenttype'	=>	$deployment->deploymenttype,
										'duration'			=>	$deployment->duration,
										'remuneration'		=>	$deployment->remuneration,
										'operating'			=>	$deployment->operating,
										'operatingvalue'	=>	$deployment->operatingvalue,
										'tax'				=>	$deployment->tax,
										'taxvalue'			=>	$deployment->taxvalue,
										'admincharge'		=>	$deployment->admincharge,
										'adminvalue'		=>	$deployment->adminvalue,
										'rateid'			=>	$deployment->rateid,
									]);
			
			DB::table('eoi_resource_deployment')
			->where('deploymentid',$validatedData['deploymentid'])
			->update([
				'deployment_status'			=>	'Released',
				'lastdate'					=>	date('Y\-m\-d',strtotime($validatedData['release_date'])),
				'supporting_document'		=>	$supporting_document ?? NULL,
				'replaced_by_deployment_id'	=>	$new_deployment_id
			]);
			DB::table('resource_tbl')
			->where('userid',$deployment->userid)
			->update([
				'deployment_status'	=>	'Released',
				'lastdate'			=>	date('Y\-m\-d',strtotime($validatedData['release_date']))
			]);
			DB::commit();
			return redirect()->route('vendor.orderresources')->with('success', 'Resource replacement details saved successfully.');
		}
		catch(Exception $e)
		{
			DB::rollBack();
			Log::error('Error '.$e->getMessage());
			if($supporting_document!='')
			Storage::disk('public')->delete($supporting_document);
			
			return back()->with('duplicate',$e->getMessage())->withInput();
			
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
							->select('a.deploymentid','a.orderid','a.employee_code','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.sectorid','a.positionid','a.lastdate','a.replaced_by_deployment_id','a.supporting_document')
							->where('deploymentid',$deploymentid)
							->first();
			
			$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
			$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
			
			$updates	=	DB::table('eoi_resource_deployment_updates as a')
							->select('a.employee_code','a.name','a.mobilenumber','a.email','a.deployed_date','a.deployment_status','a.lastdate','a.updatedOn','b.sectorname','c.consultantposition','d.shortname as updatedBy','e.name as subUserName')
							->leftJoin('sector_tbl as b','b.sectorid','=','a.sectorid')
							->leftJoin('position_tbl as c','c.positionid','=','a.positionid')
							->leftJoin('vendor_tbl as d','d.vendorid','=','a.vendorid')
							->leftJoin('users_tbl as e','e.userid','=','a.subuserid')
							->where('a.deploymentid',$deploymentid)
							->orderBy('a.updatedOn','DESC')
							->get();
			
			$htmlData 	= 	view('admin.vendors.ajaxpages.resourceDetail',[
								'resource'	=>	$resource,
								'sectors'	=>	$sectors,
								'positions'	=>	$positions,
								'order'		=>	$order,
								'updates'	=>	$updates,
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
							->select('a.deploymentid','a.orderid','a.employee_code','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.experiencelevel','a.experience','a.lastdate','a.replaced_by_deployment_id','a.supporting_document','a.role')
							->where('deploymentid',$deploymentid)
							->first();
			
			$order		=	DB::table('eoi_work_order')->where('orderid',$resource->orderid)->first();
			
		
			$updates	=	DB::table('eoi_resource_deployment_updates as a')
							->select('a.employee_code','a.name','a.mobilenumber','a.email','a.deployed_date','a.deployment_status','a.lastdate','a.updatedOn','b.shortname as updatedBy','c.name as subUserName','a.experience','a.experiencelevel')
							->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->leftJoin('users_tbl as c','c.userid','=','a.subuserid')
							->where('a.deploymentid',$deploymentid)
							->orderBy('a.updatedOn','DESC')
							->get();
			
			$htmlData 	= 	view('admin.vendors.ajaxpages.resourceDetail',[
								'resource'	=>	$resource,
								'experiences'=>	$experiences,
								'levels'	=>	$levels,
								'order'		=>	$order,
								'updates'	=>	$updates,
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
			
			$order		=	DB::table('eoi_work_order')
							->where('orderid',$resource->orderid)
							->where('isActiveOrder',1)
							->first();
			if(!$order)
			{
				return response()->json(['status'=>500,'message'=>'This detail cannot be updated because the order has been extended or has expired.']);
			}
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
			
			$deployment	=	DB::table('eoi_resource_deployment')
							->where('deploymentid',$validatedData['deploymentid'])
							->first();
			
			if(!$deployment)
			{
				return response()->json(['status'=>500,'message'=>'Please check the deployment details.']);
			}
			
			

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

    public function viewMprUpdates(Request $request,$summaryid)
	{
		if(!session('vendorId')) {
			return redirect('dashboard');
		}
		$summaryid	=	Crypt::decrypt($summaryid);
		$ind		=	$request->input('ind') ?? 0;
		try
		{
			$summary	=	DB::table('mpr_attendance_summary')->where('summary_id',$summaryid)->first();
			$summaryLog	=	DB::table('mpr_attendance_summary_log')->where('summary_id',$summaryid)->first();
			
			if(!$summary)
			{
				return response()->json(['status'=>500,'message'=>'The provided summary detail does not exist.']);
			}
			
			$html	=	view('admin.vendors.ajaxpages.viewMprUpdates',['summary'=>$summary,'summaryLog'=>$summaryLog,'ind'=>$ind])->render();
			return response()->json(['status'=>200,'data'=>$html]);
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>500,'message'=>'There are no pending MPRs for invoicing.']);
		}
    }

	public function uploadSignedDocuments(Request $request)
	{
		$mpr_id	=	Crypt::decrypt($request->input('mpr_id'));

		if($request->upload_type == 'mpr')
		{
			$request->validate([
				'signed_mpr' => 'required|mimes:pdf|max:10240',
			], [
				'signed_mpr.required' => 'Please select the signed MPR PDF file to upload.',
				'signed_mpr.mimes'    => 'Only PDF files are allowed.',
				'signed_mpr.max'      => 'The file size must not exceed 10 MB.',
			]);

		}
		elseif ($request->upload_type == 'attendance')
		{
			$request->validate([
				'signed_attendance' => 'required|mimes:pdf|max:10240',
			], [
				'signed_attendance.required' => 'Please select the signed Attendance PDF file to upload.',
				'signed_attendance.mimes'    => 'Only PDF files are allowed.',
				'signed_attendance.max'      => 'The file size must not exceed 10 MB.',
			]);

		}
		elseif ($request->upload_type == 'supporting')
		{
			$request->validate([
				'signed_supporting' => 'required|mimes:pdf|max:10240',
			], [
				'signed_supporting.required' => 'Please select the signed Supporting Documents PDF file to upload.',
				'signed_supporting.mimes'    => 'Only PDF files are allowed.',
				'signed_supporting.max'      => 'The file size must not exceed 10 MB.',
			]);
		}		

		$path 	= null;
		$data	=	[];
		if($request->upload_type == 'mpr')
		{
			$file = $request->file('signed_mpr');
			$path = $file->store('uploads/singed_mpr', 'public');
			$data['signed_mpr']	=	$path;
		}
		else if($request->upload_type == 'attendance')
		{
			$file = $request->file('signed_attendance');
			$path = $file->store('uploads/signed_attendance', 'public');
			$data['signed_attendance']	=	$path;
		}
		else if($request->upload_type == 'supporting')
		{
			$file = $request->file('signed_supporting');
			$path = $file->store('uploads/signed_supporting', 'public');
			$data['signed_supporting']	=	$path;
		}		
		
		DB::table('mpr_tbl')
		->where('mpr_id',$mpr_id)
		->update($data);
		
		return response()->json([
			'status' 	=> 	true,
			'mpr_id'	=>	$request->input('mpr_id'),
			'message' 	=> 	'File uploaded successfully.'
		]);
	}


    public function generateParticipationOtp(Request $request)
    {
        $request->validate([
            'email' 	=> 	['required', 'email'],
			'floatid' 	=> 	['required'],
        ]);

        $email 		= 	$request->email;
		$floatid 	= 	Crypt::decrypt($request->floatid);
		
		try
		{
			$float	=	DB::table('eoi_request_floated')->where('floatid',$floatid)->whereNull('otp_verified_on')->exists();
			if(!$float)
			{
				return response()->json([
					'success' => false,
					'message' => 'This proposal has already been verified.'
				], 422);
			}
			$otp = rand(100000, 999999);

			Cache::put('email_otp_' . $email,$otp,now()->addMinutes(5));

			DB::table('eoi_request_floated')
			->where('floatid',$floatid)
			->update([
				'otp_email'	=>	$email
			]);

			Mail::to($email)->send(new SendParticipationOTPMail($otp));

			return response()->json([
				'success' => true,
				'message' => 'OTP has been sent to your email.'
			]);
		}
		catch(Exception $e)
		{
			Log::error('Error '.$e->getMessage());
		}
    }


    public function verifyParticipationOtp(Request $request)
    {
        $request->validate([
            'email'		=> 	['required','email'],
			'floatid' 	=> 	['required'],
            'otp'   	=> 	['required', 'digits:6'],
        ]);

        $email		=	$request->email;
		$floatid	=	$request->floatid;
        $otp		=	$request->otp;

        $storedOtp = Cache::get('email_otp_' . $email);

        if(!$storedOtp)
		{
            return response()->json([
                'success' => false,
                'message' => 'OTP has expired. Please request a new OTP.'
            ], 422);
        }

        // Compare OTP
        if((string) $storedOtp!==(string) $otp)
		{
            return response()->json([
                'success'	=>	false,
                'message'	=>	'Invalid OTP. Please try again.'
            ], 422);
        }

        Cache::forget('email_otp_'.$email);
		
		DB::table('eoi_request_floated')
		->where('floatid',Crypt::decrypt($floatid))
		->where('otp_email',$email)
		->update([
			'otp_verified_on'	=>	now()
		]);
		
		
        return response()->json([
            'success' => true,
            'message' => 'Email verified successfully.'
        ]);
    }	
	
}
