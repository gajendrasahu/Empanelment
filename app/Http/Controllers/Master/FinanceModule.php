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

class FinanceModule extends Controller
{
	
    public function getPendingInvoiceData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',50);
		$currentPage	= 	$request->input('page', 1);
		
		$vendorid		=	$request->input('vendorid');
		$departmentid	=	$request->input('departmentid');
		$orderid		=	$request->input('orderid');
		
		$data	=	DB::table('invoice_mpr as a')
					->select('a.voucher_id','a.voucher_number','a.invoice_date','a.invoice_number','a.invoice_remark','a.invoice_amount','a.tax','a.tax_value','a.net_invoice_value','a.invoice_file','b.workorderamount','b.ordernumber','c.companyname','d.departmentname')
					->join('eoi_work_order as b','b.orderid','=','a.order_id')
					->join('vendor_tbl as c','c.vendorid','=','b.vendorid')
					->join('department_tbl as d','d.departmentid','=','b.department_id')
					->when($pagesearch!='',function ($query) use ($pagesearch) {
						$query->where(function ($q) use ($pagesearch) {
							$q->where('b.ordernumber','like','%'.$pagesearch.'%')
							  ->orWhere('b.orderno','like','%'.$pagesearch.'%')
							  ->orWhere('b.departmentname','like','%'.$pagesearch.'%')
							  ->orWhere('a.voucher_number','like','%'.$pagesearch.'%')
							  ->orWhere('a.invoice_number','like','%'.$pagesearch.'%')
							  ->orWhere('b.companyname','like','%'.$pagesearch.'%');
						});
					})
					->when($vendorid!=0,function($query) use ($vendorid){
						return $query->where('a.vendor_id','=',$vendorid);
					})					
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('b.userid','=',$departmentid);
					})					
					->when($orderid!=0,function($query) use ($orderid){
						return $query->where('a.order_id','=',$orderid);
					})					
					->where('a.is_paid','=',0)
					->orderby('a.invoice_date')
					->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/pendinginvoiceTable', ['data' => $data]);

    }
	
    public function getInvoiceList(Request $request,$search=NULL)
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','admininvoicelist');
		Session::put('menid',107);
		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=107 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$vendors	=	DB::table('vendor_tbl')->orderby('companyname')->get();
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$orders		=	DB::table('order_eoi')->select('orderid','ordernumber')->get();
		
        return view('admin/master/invoices_list',compact('vendors','search','orders','departments'));
    }

    public function getWorkOrderList(Request $request,$search=NULL)
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','paymentrequest');
		Session::put('menid',108);
		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=108 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$vendors	=	DB::table('vendor_tbl')->orderby('companyname')->get();
		$orders		=	DB::table('order_eoi')->select('orderid','ordernumber')->get();
		
        return view('admin/master/paymentrequest_list',compact('departments','search','orders','vendors'));
    }

    public function getOrdersData(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize',50);
		$currentPage= 	$request->input('page', 1);
		
		$vendorid		=	$request->input('vendorid');
		$departmentid	=	$request->input('departmentid');
		$orderid		=	$request->input('orderid');
		
		$data	=	DB::table('order_eoi_department_vendor as a')
					->select('a.*')
					->when($pagesearch != '', function ($query) use ($pagesearch) {
						$query->where(function ($q) use ($pagesearch) {
							$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
							  ->orWhere('a.orderno', 'like', '%' . $pagesearch . '%')
							  ->orWhere('a.departmentname', 'like', '%' . $pagesearch . '%')
							  ->orWhere('a.companyname', 'like', '%' . $pagesearch . '%');
						});
					})
					->when($vendorid!=0,function($query) use ($vendorid){
						return $query->where('a.vendorid','=',$vendorid);
					})					
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('a.userid','=',$departmentid);
					})					
					->when($orderid!=0,function($query) use ($orderid){
						return $query->where('a.orderid','=',$orderid);
					})					
					->orderby('a.orderdate')
					->paginate($pagesize,['*'],'page',$currentPage);
		
		foreach($data as $dt)
		{
			$dt->paid				=	0;
			$dt->requested			=	0;
			$dt->balance			=	$dt->workorderamount-$dt->paid;
			$dt->workorderamount	=	$this->formatIndianCurrency(number_format($dt->workorderamount,'2','.',''));
			$dt->requested			=	$this->formatIndianCurrency(0);
			$dt->paid				=	$this->formatIndianCurrency(0);
			$dt->balance			=	$this->formatIndianCurrency(number_format($dt->balance,'2','.',''));

		}
		
		
		return view('admin/ajaxpages/requestordersTable', ['data' => $data]);

    }

    public function getFirmWiseProjectData(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		
		$firm_type 	=	$request->input('firm_type') ?? 0;
		
		$data	=	DB::table('vendor_resource_counting as a')
					->select('a.vendorid','b.companyname','b.shortname','a.total_count','c.tiername')
					->leftjoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->leftjoin('tiermaster_tbl as c','c.tierid','=','b.tierid')
					->when($firm_type!=0,function($query) use ($firm_type){
						return $query->where('b.categoryid','=',$firm_type);
					})					
					->orderBy('b.companyname')
					->get();
		
		foreach($data as $dt)
		{
			$dt->total_project	=	DB::table('eoi_work_order as a')
									->join('eoi_request as b','b.requestid','=','a.requestid')
									->where('a.vendorid',$dt->vendorid)
									->distinct()
									->count('b.projecttitle') ?? 0;
			
			$dt->order_value	=	DB::table('eoi_work_order')->where('vendorid',$dt->vendorid)->sum('workorderamount') ?? 0;
			
		}
		
		
		
		return view('admin/ajaxpages/firmwiseprojectTable', ['data' => $data]);

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


    public function invoiceHistory(Request $request)
	{		
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','invoicehistory');

		$userId		=	$request->session()->get('userId');
		$issuper	=	$request->session()->get('issuper');

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
		
		$firms	=	DB::table('vendor_tbl as a')
					->select('a.vendorid','a.companyname','a.shortname')
					->join('vendor_invoices as b','b.vendorid','=','a.vendorid')
					->distinct()
					->whereNull('a.parentVendorId')
					->orderBy('a.companyname')
					->get();

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/invoice_history',compact('months','years','firms','token'));
    }

    public function getInvoiceData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;

		$mpr_month		=	$request->input('mpr_month');
		$mpr_year		=	$request->input('mpr_year');
		$vendor_id		=	$request->input('vendor_id');
		$payment_status	=	$request->input('payment_status');
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

        $data 			= 	DB::table('vendor_invoices as a')
								->select('a.*','b.companyname','b.shortname','c.project_name','c.ordernumber','d.name')
								->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
								->leftJoin('eoi_work_order as c','c.orderid','=','a.orderid')
								->leftJoin('users_tbl as d','d.userid','=','a.subuserid')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.invoice_number', 'like', '%' . $pagesearch . '%')
										  ->orwhere('b.companyname', 'like', '%' . $pagesearch . '%')
										  ->orwhere('c.project_name', 'like', '%' . $pagesearch . '%')
										  ->orwhere('c.ordernumber', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($mpr_month!=0,function($query) use ($mpr_month){
									return $query->where('a.mpr_month','=',$mpr_month);
								})
								->when($mpr_year!=0,function($query) use ($mpr_year){
									return $query->where('a.mpr_year','=',$mpr_year);
								})
								->when($vendor_id!=0,function($query) use ($vendor_id){
									return $query->where('a.vendorid','=',$vendor_id);
								})
								->when($payment_status!='',function($query) use ($payment_status){
									if($payment_status=='Paid')
									{
										return $query->where('a.balance_value','=',0);
									}
									if($payment_status=='Partially Paid')
									{
										return $query->where('a.balance_value','!=',0)
													 ->where('a.paid_value','!=',0);
									}
									if($payment_status=='Unpaid')
									{
										return $query->where('a.paid_value','=',NULL);
									}
								})
								->orderBy('a.request_date','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
				
		return view('admin/ajaxpages/invoicehistoryTable', ['data' => $data]);

    }

    public function addInvoicePayment(Request $request)
	{

		$rules = [
			'record_ids'	=>	'required|regex:/^\d+(,\d+)*$/',
		];
        $messages = [
            'record_ids.required'	=> 'Invalid detail provided',
			'record_ids.regex'		=> 'Record IDs must be comma separated numeric values.',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$recordIds	=	explode(',', $request->record_ids);

			$first	=	DB::table('vendor_invoices as a')
							->select('a.*','b.companyname')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->whereIn('a.recordid', $recordIds)
							->first();

			$invoices	=	DB::table('vendor_invoices as a')
							->select('a.*','b.companyname')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->whereIn('a.recordid', $recordIds)
							->get();

			if($invoices->count()!==count($recordIds))
			{
				return back()->with('error', 'Some selected records are invalid.')->withInput();
			}

			if($invoices->pluck('vendorid')->unique()->count()!==1)
			{
				return back()->with('duplicate', 'Selected records must belong to the same firm.')->withInput();
			}
			$token		=	rand('100000','999999').''.time();
			Session::put('form_token', $token);

			$recordIds	=	$request->record_ids;
			Session::put('record_ids', $recordIds);
			Session::put('vendor_id', $first->vendorid);
			return view('admin/master/payment_entry',compact('invoices','token','first'));
		}
		catch(QueryException $e)
		{
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}
    }
	
	public function savePaymentData(Request $request)
	{
		$rules = [
			'payment_date'			=>	'required|date',
			'total_balance'			=>	'required|numeric',
			'paying_amount' 		=> 	'required|numeric|min:0.01|lte:total_balance',
			'payment_method'		=>	'required|in:Bank Transfer,Cheque,Demand Draft',
			'transaction_number'  	=> 	'required|string|not_regex:/<[^>]*>/',
			'invoice_reference'    	=> 	'required|string|max:2000|not_regex:/<[^>]*>/',
			'payment_remark'      	=> 	'nullable|string|not_regex:/<[^>]*>/',
			'bank_id' 				=> 	'required_if:payment_method,Bank Transfer|nullable|integer',
			'cheque_date' 			=> 	'required_if:payment_method,Cheque|nullable|date',
			'demand_date' 			=> 	'required_if:payment_method,Demand Draft|nullable|date',
		];
		
		$messages = [
			'payment_date.required'        	=> 	'The payment date field is required.',
			'payment_date.date'            	=> 	'Please enter a valid payment date.',
			'total_balance.required'       	=> 	'The total balance field is required.',
			'total_balance.numeric'        	=> 	'The total balance must be a number.',
			'paying_amount.required'       	=> 	'The paying amount field is required.',
			'paying_amount.numeric'        	=> 	'The paying amount must be a number.',
			'paying_amount.min'        		=> 	'The paying amount must be at least 0.01.',
			'paying_amount.lte'        		=> 	'The paying amount must be less than or equal to the total balance.',
			'payment_method.required'      	=> 	'Please select a payment method.',
			'payment_method.in'            	=> 	'The selected payment method is invalid.',
			'transaction_number.required'  	=> 	'The transaction number field is required.',
			'transaction_number.not_regex' 	=> 	'The transaction number must not contain HTML or script tags.',
			'invoice_reference.required'   	=> 	'The invoice reference field is required.',
			'invoice_reference.max' 		=> 	'The invoice reference may not be greater than 2000 characters.',
			'invoice_reference.not_regex'  	=> 	'The invoice reference must not contain HTML or script tags.',
			'payment_remark.not_regex'     	=> 	'The payment remark must not contain HTML or script tags.',
			'bank_id.required_if'          	=> 	'Please select a bank when payment method is Bank Transfer.',
			'cheque_date.required_if'      	=> 	'The cheque date is required when payment method is Cheque.',
			'cheque_date.date'             	=> 	'Please enter a valid cheque date.',
			'demand_date.required_if'      	=> 	'The demand draft date is required when payment method is Demand Draft.',
			'demand_date.date'             	=> 	'Please enter a valid demand draft date.',
		];

        $validatedData 	= $request->validate($rules,$messages);
		
		try
		{
			$userId		= 	$request->session()->get('userId');
			$recordIds	=	explode(',',session('record_ids'));
			$vendorId 	=	session('vendor_id');
			
			$count	=	DB::table('vendor_invoices')
						->whereIn('recordid', $recordIds)
						->where('vendorid',$vendorId)
						->count();

			if ($count!=count($recordIds))
			{
				return response()->json(['status'=>400,'message'=>'Invalid invoice records for this vendor.']);
			}
			DB::beginTransaction();
			$recordIds			=	array_map('intval', explode(',', session('record_ids')));
			$vendorId 			= 	session('vendor_id');
			$remainingAmount 	= 	$validatedData['paying_amount'];

			$invoices	=	DB::table('vendor_invoices')
							->whereIn('recordid',$recordIds)
							->where('vendorid', $vendorId)
							->orderBy('recordid')
							->get();

			$isChequeCleared	=	1;
			if($validatedData['cheque_date'])
			{
				$isChequeCleared	=	0;
			}
			
			$chequeDate	=	NULL;
			$demandDate	=	NULL;
			if($validatedData['cheque_date'])
			{
				$chequeDate	=	date('Y\-m\-d',strtotime($validatedData['cheque_date']));
			}
			if($validatedData['demand_date'])
			{
				$demandDate	=	date('Y\-m\-d',strtotime($validatedData['demand_date']));
			}

			$paymentid	=	DB::table('vendor_invoice_bulk_payment')
							->insertGetId([
								'vendorid'				=>	$vendorId,
								'record_ids'			=>	session('record_ids'),
								'payment_date'			=>	date('Y\-m\-d',strtotime($validatedData['payment_date'])),
								'paying_amount'			=>	$validatedData['paying_amount'],
								'payment_method'		=>	$validatedData['payment_method'],
								'bank_id'				=>	$validatedData['bank_id'] ?? 0,
								'cheque_date'			=>	$chequeDate,
								'demand_date'			=>	$demandDate,
								'transaction_number'	=>	$validatedData['transaction_number'] ?? NULL,
								'invoice_refrence'		=>	$validatedData['invoice_refrence'] ?? NULL,
								'payment_remark'		=>	$validatedData['payment_remark'] ?? NULL,
								'creationdate'			=>	now(),
								'created_by'			=>	$userId,
								'isChequeCleared'		=>	$isChequeCleared
							]);

			foreach($invoices as $invoice)
			{

				if($remainingAmount<=0)
				{
					break;
				}

				$balance	=	$invoice->balance_value;

				if($remainingAmount>=$balance)
				{
					$paidAmount		=	$balance;
					$remainingAmount-= 	$balance;
					$newBalance 	= 	0;

				}
				else
				{
					$paidAmount		= 	$remainingAmount;
					$newBalance		=	$balance-$remainingAmount;
					$remainingAmount= 	0;
				}

				DB::table('vendor_invoices')
					->where('recordid',$invoice->recordid)
					->update([
						'paid_value'	=>	DB::raw("COALESCE(paid_value,0) + $paidAmount"),
						'balance_value' => 	$newBalance
					]);
				
				DB::table('vendor_invoice_payment')
				->insert([
					'paymentid'				=>	$paymentid,
					'vendorid'				=>	$vendorId,
					'recordid'				=>	$invoice->recordid,
					'payment_date'			=>	date('Y\-m\-d',strtotime($validatedData['payment_date'])),
					'paying_amount'			=>	$paidAmount,
					'payment_method'		=>	$validatedData['payment_method'],
					'bank_id'				=>	$validatedData['bank_id'] ?? 0,
					'cheque_date'			=>	$chequeDate,
					'demand_date'			=>	$demandDate,
					'transaction_number'	=>	$validatedData['transaction_number'] ?? NULL,
					'payment_remark'		=>	$validatedData['payment_remark'] ?? NULL,
					'creationdate'			=>	now(),
					'created_by'			=>	$userId,
					'isChequeCleared'		=>	$isChequeCleared
				]);				
			}
			DB::commit();
			return response()->json(['status'=>200,'message'=>'Payment applied to selected invoices successfully.']);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error is '.$e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
		
	}

    public function invoicePaymentHistory(Request $request)
	{		
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','adminvendorpayments');
		Session::put('menid',134);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=134 and ispermitted=1 and userid=".$userId.") as actions"))
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

		
		$firms	=	DB::table('vendor_tbl as a')
					->select('a.vendorid','a.companyname','a.shortname')
					->join('vendor_invoices as b','b.vendorid','=','a.vendorid')
					->distinct()
					->orderBy('a.companyname')
					->get();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/payment_history',compact('firms','token'));
    }

    public function getInvoicePaymentData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;

		$vendor_id		=	$request->input('vendor_id');
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

        $data 			= 	DB::table('vendor_invoice_bulk_payment as a')
								->select('a.*','b.companyname','b.shortname')
								->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.invoice_refrence', 'like', '%' . $pagesearch . '%')
										  ->orwhere('b.companyname', 'like', '%' . $pagesearch . '%')
										  ->orwhere('a.transaction_number', 'like', '%' . $pagesearch . '%')
										  ->orwhere('a.payment_remark', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($vendor_id!=0,function($query) use ($vendor_id){
									return $query->where('a.vendorid','=',$vendor_id);
								})
								->orderBy('a.payment_date')
								->paginate($pagesize,['*'],'page',$currentPage);
				
		return view('admin/ajaxpages/paymenthistoryTable', ['data' => $data]);

    }

    public function paymentSummary(Request $request)
	{		
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','adminpaymentsummary');
		Session::put('menid',135);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=135 and ispermitted=1 and userid=".$userId.") as actions"))
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

	
		$firms	=	DB::table('vendor_tbl as a')
					->select('a.vendorid','a.companyname','a.shortname')
					->join('vendor_invoices as b','b.vendorid','=','a.vendorid')
					->distinct()
					->orderBy('a.companyname')
					->get();
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/payment_summary',compact('firms','token'));
    }

    public function getPaymentSummaryData(Request $request)
	{
	
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;

		$vendor_id		=	$request->input('vendor_id');
		
		$summary_type	=	$request->input('summary_type') ?? 'FIRM';
		
		$pagesearch 	=	$request->input('pagesearch');
	
		if($summary_type==='FIRM')
		{
		
			$vendors	=	DB::table('vendor_tbl as v')
							->leftJoin('vendor_invoices as vi', 'v.vendorid', '=', 'vi.vendorid')
							->leftJoin(
								DB::raw('(SELECT vendorid, SUM(paying_amount) as total_paid 
										  FROM vendor_invoice_bulk_payment 
										  GROUP BY vendorid) as vp'),
								'v.vendorid',
								'=',
								'vp.vendorid'
							)
							->select(
								'v.vendorid',
								'v.companyname as firm_name',
								DB::raw('COUNT(vi.recordid) as total_invoices'),
								DB::raw('COALESCE(SUM(vi.invoice_value),0) as invoice_value'),
								DB::raw('COALESCE(vp.total_paid,0) as paid_amount'),
								DB::raw('(COALESCE(SUM(vi.invoice_value),0) - COALESCE(vp.total_paid,0)) as balance'),
								DB::raw('
									CASE 
										WHEN COALESCE(SUM(vi.invoice_value),0) = 0 THEN 0
										ELSE ROUND((COALESCE(vp.total_paid,0) / SUM(vi.invoice_value)) * 100,2)
									END as payment_percent
								'),
								DB::raw("
									CASE 
										WHEN COALESCE(vp.total_paid,0) = 0 THEN 'Pending'
										WHEN COALESCE(vp.total_paid,0) >= SUM(vi.invoice_value) THEN 'Paid'
										ELSE 'Partial'
									END as status
								")
							)
							->when($vendor_id!=0,function($query) use ($vendor_id){
								return $query->where('vi.vendorid','=',$vendor_id);
							})
							->when($pagesearch != '', function ($query) use ($pagesearch) {
								$query->where(function ($q) use ($pagesearch) {
									$q->where('v.companyname', 'like', '%' . $pagesearch . '%')
									  ->orwhere('vi.invoice_number', 'like', '%' . $pagesearch . '%');
								});
							})							
							->groupBy('v.vendorid','v.companyname','vp.total_paid')
							->get();
					
			return view('admin/ajaxpages/paymentsummaryTable', ['data' => $vendors,'summary_type'=>$summary_type]);
		}
		
		if($summary_type==='PROJECT')
		{
			$vendors	=	DB::table('vendor_invoices as vi')
							->join('vendor_tbl as v', 'vi.vendorid', '=', 'v.vendorid')
							->join('eoi_work_order as wo', 'vi.orderid', '=', 'wo.orderid')
							->whereNotNull('vi.orderid')
							->select(
								'vi.vendorid',
								'v.companyname',
								'vi.orderid',
								'wo.project_name',
								DB::raw('SUM(vi.amount_value) as total_amount'),
								DB::raw('SUM(vi.tax_value) as total_tax'),
								DB::raw('SUM(vi.invoice_value) as total_invoice'),
								DB::raw('SUM(vi.paid_value) as total_paid'),
								DB::raw('SUM(vi.balance_value) as total_balance')
							)
							->when($vendor_id!=0,function($query) use ($vendor_id){
								return $query->where('vi.vendorid','=',$vendor_id);
							})
							->groupBy('vi.vendorid', 'v.companyname', 'vi.orderid', 'wo.project_name')
							->get()
							->groupBy('vendorid');


			return view('admin/ajaxpages/paymentsummaryTable', ['data' => $vendors,'summary_type'=>$summary_type]);
		}
    }


    public function viewVendorInvoices(Request $request)
	{
	
		$userId		= 	$request->session()->get('userId');
		$vendorId	= 	$request->input('vendorid');
		$rectype	= 	$request->input('rectype');
		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$vendorId)->first();
		
		if($rectype=='Invoices')
		{
			$data	= 	DB::table('vendor_invoices as a')
						->select('a.*','b.companyname','b.shortname')
						->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.vendorid',$vendorId)
						->orderBy('a.request_date')
						->get();
			
			$html = view('admin.invoicepages.invoicesTable',['data'=>$data,'vendor'=>$vendor])->render();
			
			return response()->json(['status'=>200,'message'=>'Invoice List.','formhtml' => $html]);
		}
		if($rectype=='Paid')
		{
			$data	= 	DB::table('vendor_invoice_bulk_payment as a')
						->select('a.*','b.companyname','b.shortname')
						->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.vendorid',$vendorId)
						->orderBy('a.payment_date')
						->get();
			
			$html = view('admin.invoicepages.paidinvoicesTable',['data'=>$data,'vendor'=>$vendor])->render();
			
			return response()->json(['status'=>200,'message'=>'Invoice List.','formhtml' => $html]);			
		}
		if($rectype=='Balance')
		{
			$data	= 	DB::table('vendor_invoices as a')
						->select('a.*','b.companyname','b.shortname')
						->leftJoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.vendorid',$vendorId)
						->where('a.balance_value','!=',0)
						->whereNotNull('a.balance_value')
						->orderBy('a.request_date')
						->get();
			
			$html = view('admin.invoicepages.invoicesTable',['data'=>$data,'vendor'=>$vendor])->render();
			
			return response()->json(['status'=>200,'message'=>'Invoice List.','formhtml' => $html]);
		}
    }
    
}
