<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Vendors;
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
use Illuminate\Support\Str;
use App\Services\LogServices;


class InvoiceController extends Controller
{
	protected $logService;
	protected $usr;
	public function __construct(LogServices $logService,Request $request)
	{
		$this->logService 	=	$logService;
		$this->middleware(function ($request, $next) {
			$this->usr = DB::table('users_tbl')
				->where('userid', $request->session()->get('userId'))
				->first();

			return $next($request);
		});		
	}

    public function submitInvoice(Request $request)
	{
        Session::put('adminmenu','vendorinvoices');
		Session::put('adminsubmenu','submitinvoice');
		Session::put('menid',125);
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= 	DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=125 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();

			$actions=	explode(",",$action->actions);
			Session::put('actions',$actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}

		$months = [
					['label' => 'January', 'value' => 1],
					['label' => 'February', 'value' => 2],
					['label' => 'March', 'value' => 3],
					['label' => 'April', 'value' => 4],
					['label' => 'May', 'value' => 5],
					['label' => 'June', 'value' => 6],
					['label' => 'July', 'value' => 7],
					['label' => 'August', 'value' => 8],
					['label' => 'September', 'value' => 9],
					['label' => 'October', 'value' => 10],
					['label' => 'November', 'value' => 11],
					['label' => 'December', 'value' => 12],
				];
		
		$currentYear = now()->year;
		$years	=	collect(range(2025, $currentYear))
					->sortDesc()
					->map(function ($year) {
						return [
							'label' => $year,
							'value' => $year,
						];
					})
					->values();
					
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		if(session('subUserId'))
		{
			$orders	=	DB::table('eoi_work_order')
						->select('orderid','project_name','ordernumber')
						->where('vendorid',Session('vendorId'))
						->where('subuserid',session('subUserId'))
						->whereDate('workorderduedate', '>=', now()->subMonths(2)->startOfMonth())
						->get();
		}
		else
		{
			$orders	=	DB::table('eoi_work_order')
						->select('orderid','project_name','ordernumber')
						->where('vendorid',Session('vendorId'))
						->whereDate('workorderduedate', '>=', now()->subMonths(2)->startOfMonth())
						->get();
			
		}
		
        return view('admin/vendors/submit_invoice',compact('months','years','token','orders'));
    }

    public function storeInvoice(Request $request)
	{
		$request->merge([
			'invoice_date'  => 	Carbon::parse($request->invoice_date)->format('Y-m-d'),
		]);

		$rules = [
			'order_id' => [
				'required',
				'numeric',
				Rule::exists('eoi_work_order', 'orderid')
					->where(function ($query) {
						$query->where('vendorid', session('vendorId'));
					}),
			],
			'mpr_month' 		=> 	'required|numeric',
			'mpr_year' 			=> 	'required|numeric',
			'invoice_date' 		=> 	'required|date|before_or_equal:today',
			'invoice_number' 	=> 	'required|max:30|not_regex:/<script\b[^>]*>(.*?)<\/script>/is|regex:/^[a-zA-Z0-9.\/\\\\\-\(\)\[\]]+$/',
			'amount_value' 		=> 	'required|numeric',
			'tax_value'       	=> 	'required|numeric',
			'invoice_value'		=>	'required|numeric',
			'mpr_file' 			=> 	'required|file|mimes:pdf,zip,rar',
			'attendance_file'	=>	'required|file|mimes:pdf,zip,rar',
			'supporting_file'	=>	'required|file|mimes:pdf,zip,rar',
			'invoice_file'		=>	'required|file|mimes:pdf,zip,rar',
		];

		$messages = [
			
			'order_id.required'           => 'Order ID is required.',
			'order_id.numeric'            => 'Order ID must be a valid number.',
			'order_id.exists'   		  => 'The selected Order ID is invalid or does not belong to you.',
			
			'mpr_month.required'          => 'The MPR month field is required.',
			'mpr_month.numeric'           => 'The MPR month must be a numeric value.',
			
			'mpr_year.required'           => 'The MPR year field is required.',
			'mpr_year.numeric'            => 'The MPR year must be a numeric value.',
			
			'invoice_date.required'       => 'The invoice date field is required.',
			'invoice_date.date'           => 'The invoice date must be a valid date.',
			'invoice_date.before_or_equal'=> 'The invoice date must be today or a previous date.',
			
			'invoice_number.required'     	=> 'The invoice number field is required.',
			'invoice_number.not_regex'    	=> 'The invoice number contains invalid characters.',
			'invoice_number.regex'        	=> 'The invoice number format is invalid.',
			'invoice_number.max'			=> 'The invoice number may not be greater than 30 characters.',
			
			'amount_value.required'       => 'The amount value field is required.',
			'amount_value.numeric'        => 'The amount value must be a numeric value.',
			
			'tax_value.required'          => 'The tax value field is required.',
			'tax_value.numeric'           => 'The tax value must be a numeric value.',
			
			'invoice_value.required'      => 'The invoice value field is required.',
			'invoice_value.numeric'    => 'The invoice value must be a numeric value.',
			
			'mpr_file.required'        => 'The MPR file is required.',
			'mpr_file.file'            => 'The MPR file must be a valid file.',
			'mpr_file.mimes'           => 'The MPR file must be a file of type: zip, rar.',
			
			'attendance_file.required' => 'The attendance file is required.',
			'attendance_file.file'     => 'The attendance file must be a valid file.',
			'attendance_file.mimes'    => 'The attendance file must be a file of type:  zip, rar.',
			
			'supporting_file.required' => 'The supporting document is required.',
			'supporting_file.file'     => 'The supporting document must be a valid file.',
			'supporting_file.mimes'    => 'The supporting document must be a file of type: zip,rar.',

			'invoice_file.required' => 'The invoice file is required.',
			'invoice_file.file'     => 'The invoice file must be a valid file.',
			'invoice_file.mimes'    => 'The invoice file must be a file of type: zip,rar.',
		];
		
        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		try
		{
			$mpr_file		= 	$request->file('mpr_file');		
			$attendance_file= 	$request->file('attendance_file');
			$supporting_file= 	$request->file('supporting_file');
			
			if($request->hasFile('mpr_file'))
			{
				$mpr_file= $request->file('mpr_file')->store('uploads/mpr_files','public');
			}
			if($request->hasFile('attendance_file'))
			{
				$attendance_file= $request->file('attendance_file')->store('uploads/attendance_files','public');
			}
			if($request->hasFile('supporting_file'))
			{
				$supporting_file= $request->file('supporting_file')->store('uploads/supporting_files','public');
			}
			if($request->hasFile('invoice_file'))
			{
				$invoice_file= $request->file('invoice_file')->store('uploads/invoice_files','public');
			}
			
			DB::table('vendor_invoices')->insert([
				'request_date'		=>	date('Y\-m\-d'),
				'mpr_month'			=>	$validatedData['mpr_month'],
				'mpr_year'			=>	$validatedData['mpr_year'],
				'invoice_date'		=>	$validatedData['invoice_date'],
				'invoice_number'	=>	$validatedData['invoice_number'],
				'amount_value'		=>	$validatedData['amount_value'],
				'tax_value'			=>	$validatedData['tax_value'],
				'invoice_value'		=>	$validatedData['invoice_value'],
				'balance_value'		=>	$validatedData['invoice_value'],
				'mpr_file'			=>	$mpr_file,
				'attendance_file'	=>	$attendance_file,
				'supporting_file'	=>	$supporting_file,
				'invoice_file'		=>	$invoice_file,
				'userid'			=>	$this->usr->userid,
				'vendorid'			=>	DB::table('vendor_tbl')->where('userid',$this->usr->userid)->value('vendorid'),
				'orderid'			=>	$validatedData['order_id'] ?? NULL,
				'subuserid'			=>	session('subUserId') ?? NULL
			]);
			return back()->with('success','Invoice details have been successfully saved.');
		}
		catch(QueryException $e)
		{
			Log::error('Error '+$e->getMessage());
			
			if($mpr_file!='')
			Storage::disk('public')->delete($mpr_file);

			if($attendance_file!='')
			Storage::disk('public')->delete($attendance_file);

			if($supporting_file!='')
			Storage::disk('public')->delete($supporting_file);

			if($invoice_file!='')
			Storage::disk('public')->delete($invoice_file);
			
			return back()->with('duplicate','Something went wrong. Please try again later.')->withInput();	
		}
	}

    public function invoiceHistory(Request $request)
	{		
        Session::put('adminmenu','vendorinvoices');
		Session::put('adminsubmenu','invoicehistory');
		Session::put('menid',126);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=126 and ispermitted=1 and userid=".$userId.") as actions"))
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

		$months = [
					['label' => 'January', 'value' => 1],
					['label' => 'February', 'value' => 2],
					['label' => 'March', 'value' => 3],
					['label' => 'April', 'value' => 4],
					['label' => 'May', 'value' => 5],
					['label' => 'June', 'value' => 6],
					['label' => 'July', 'value' => 7],
					['label' => 'August', 'value' => 8],
					['label' => 'September', 'value' => 9],
					['label' => 'October', 'value' => 10],
					['label' => 'November', 'value' => 11],
					['label' => 'December', 'value' => 12],
				];
		
		$currentYear = now()->year;
		$years	=	collect(range(2025, $currentYear))
					->sortDesc()
					->map(function ($year) {
						return [
							'label' => $year,
							'value' => $year,
						];
					})
					->values();
		$subusers	=	DB::table('users_tbl')->where('isSubVendor',1)->where('parentVendorId',Session('vendorId'))->orderBy('name')->get();
		
		if(session('subUserId'))
		{
			$orders	=	DB::table('eoi_work_order')
						->select('orderid','project_name')
						->where('vendorid',Session('vendorId'))
						->where('isExtended',0)
						->where('subuserid',session('subUserId'))
						->get();
		}
		else
		{
			$orders	=	DB::table('eoi_work_order')
						->select('orderid','project_name')
						->where('vendorid',Session('vendorId'))
						->where('isExtended',0)
						->get();
			
		}
		
		
        return view('admin/vendors/invoice_history',compact('months','years','subusers','orders'));
    }

    public function getInvoiceData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;

		$mpr_month		=	$request->input('mpr_month');
		$mpr_year		=	$request->input('mpr_year');
		$subuser_id		=	$request->input('subuser_id');
		$order_id		=	$request->input('order_id') ?? NULL;
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
		
		$subUserId		=	session('subUserId') ?? NULL;
		
        $data 			= 	DB::table('vendor_invoices as a')
								->select('a.*','c.project_name','d.name')
								->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
								->leftJoin('eoi_work_order as c','c.orderid','=','a.orderid')
								->leftJoin('users_tbl as d','d.userid','=','a.subuserid')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.invoice_number', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($mpr_month!=0,function($query) use ($mpr_month){
									return $query->where('a.mpr_month','=',$mpr_month);
								})
								->when($mpr_year!=0,function($query) use ($mpr_year){
									return $query->where('a.mpr_year','=',$mpr_year);
								})
								->when($subUserId!=NULL,function($query) use ($subUserId){
									return $query->where('c.subuserid',$subUserId);
								})
								->when($subuser_id!=NULL,function($query) use ($subuser_id){
									return $query->where('c.subuserid',$subuser_id);
								})
								->when($order_id!=NULL,function($query) use ($order_id){
									return $query->where('a.orderid',$order_id);
								})
								->where('a.userid',$userId)
								->orderBy('a.request_date','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
				
		return view('admin/vendors/ajaxpages/invoicehistoryTable', ['data' => $data]);

    }
	

}
