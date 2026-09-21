<?php

namespace App\Http\Controllers\Finance;

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
use App\Helpers\FinanceHelper;
class FinanceVendorPaymentController extends Controller
{
	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financevendorpaymentindex');

		$departments=	DB::table('department_tbl as a')
						->select('a.*','b.isdepartment','b.ispm')
						->Join('users_tbl as b','b.userid','=','a.userid')
						->where('b.isdepartment',1)
						->orderby('b.name')
						->get();

		$managers	=	DB::table('department_tbl as a')
						->select('a.*','b.isdepartment','b.ispm')
						->Join('users_tbl as b','b.userid','=','a.userid')
						->Join('eoi_request as c','c.userid','=','b.userid')
						->where('b.ispm',1)
						->where('c.categoryid',2)
						->orderby('b.name')
						->groupBy('b.name')
						->get();


		$projects	=	DB::table('project_tbl')
						->select('projectid','project_name')
						->orderby('project_name')
						->get();
		
		$vendors	=	DB::table('vendor_tbl')->select('vendorid','shortname','companyname','categoryid')->orderBy('categoryid')->get();
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token',$token);

		return view('finance.vendorpayment.index',compact('departments','managers','projects','token','vendors'));
	}


	public function getVerifiedInvoiceList(Request $request)
	{
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	=	$request->input('page',1);
		$projectid		=	$request->input('projectid') ?? NULL;
		$departmentid	=	$request->input('departmentid') ?? NULL;
		$managerid		=	$request->input('managerid') ?? NULL;
		$vendorid		=	$request->input('vendorid') ?? NULL;

		$paidPayments	=	DB::table('finance_vendor_payment as vp')
							->select(
								'vp.voucher_id',
								DB::raw('COALESCE(SUM(vp.gross_amount),0) as total_paid')
							)
							->groupBy('vp.voucher_id');

		$data	=	DB::table('invoice_mpr as inv')
					->leftJoin('vendor_tbl as v','v.vendorid','=','inv.vendor_id')
					->leftJoin('eoi_work_order as wo','wo.orderid','=','inv.order_id')
					->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
					->leftJoinSub(
						$paidPayments,
						'pp',
						function($join)
						{
							$join->on('pp.voucher_id','=','inv.voucher_id');
						}
					)
					->select(
						'inv.voucher_id',
						'inv.invoice_number',
						'inv.invoice_date',
						'inv.voucher_number',
						'inv.order_id',
						'inv.request_id',
						'inv.vendor_id',
						'inv.net_invoice_value',
						'inv.finance_status',
						'inv.invoice_status',
						'v.companyname',
						'e.eoinumber',
						'wo.ordernumber',
						DB::raw("
							CASE
								WHEN v.categoryid = 2 THEN 'CSF'
								WHEN v.categoryid = 1 THEN 'AWD'
								ELSE 'UNKNOWN'
							END as case_type
						"),
						DB::raw('COALESCE(pp.total_paid,0) as total_paid'),
						DB::raw('ROUND(inv.net_invoice_value - COALESCE(pp.total_paid,0),2) as balance'),
						DB::raw("
							CASE
								WHEN v.categoryid = 2 THEN
									(
										SELECT COALESCE(SUM(di.invoice_amount),0)
										FROM finance_department_invoice as di
										WHERE di.order_id = inv.order_id
										AND di.status IN ('Posted','Paid')
									)
									-
									(
										SELECT COALESCE(SUM(vi.net_paid_value),0)
										FROM invoice_mpr as vi
										WHERE vi.order_id = inv.order_id
										AND vi.finance_status = 'Verified'
									)
								ELSE NULL
							END as csf_available_balance
						")						
					)
					->where('inv.finance_status','Verified')
					->whereIn('inv.payment_status',['Unpaid','Partially Paid'])
					->when($departmentid !=NULL,function($query) use ($departmentid){
						return $query->where('wo.department_id',$departmentid);
					})
					->when($managerid !=NULL,function($query) use ($managerid){
						return $query->where('wo.department_id',$managerid);
					})
					->when($vendorid!=NULL,function($query) use ($vendorid){
						return $query->where('v.vendorid',$vendorid);
					})
					->when($projectid !=NULL,function($query) use ($projectid){
						return $query->where('inv.finance_project_id',$projectid);
					})
					->when($pagesearch != '',function($query) use ($pagesearch)
					{
						return $query->where(function($q) use ($pagesearch)
						{
							$q->where('inv.invoice_number','like','%'.$pagesearch.'%')
								->orWhere('inv.voucher_number','like','%'.$pagesearch.'%')
								->orWhere('v.companyname','like','%'.$pagesearch.'%')
								->orWhere('wo.ordernumber','like','%'.$pagesearch.'%');
						});
					})
					->having('balance','>',0)
					->orderBy('inv.invoice_date','asc')
					->orderBy('inv.voucher_id','asc')
					->paginate($pagesize,['*'],'page',$currentPage);

		return view('finance.vendorpayment.ajaxpages.verifiedinvoiceTable',['data'	=>	$data]);
	}

/*
	public function makeVendorPayment($id)
	{
		try
		{
			$voucherId = Crypt::decrypt($id);

			$invoice = DB::table('invoice_mpr as inv')
							->leftJoin('vendor_tbl as v','v.vendorid','=','inv.vendor_id')
							->leftJoin('eoi_work_order as wo','wo.orderid','=','inv.order_id')
							->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
							->leftJoin('users_tbl as f','f.userid','=','e.userid')
							->select('inv.*','v.companyname','v.categoryid','v.gstnumber','v.tierid','wo.ordernumber','wo.requestid as wo_request_id','e.eoinumber','f.ispm')
							->where('inv.voucher_id',$voucherId)
							->where('inv.finance_status','Verified')
							->whereIn('inv.payment_status',['Unpaid','Partially Paid'])
							->first();

			if(!$invoice)
			{
				return redirect()->route('finance.vendorpayment.index')->with('error','Vendor Invoice not found or is not available for payment.');
			}

			$totalPaid = DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount');

			$invoiceBalance = round((float)$invoice->net_invoice_value - (float)$totalPaid,2);

			if($invoiceBalance <= 0)
			{
				return redirect()->route('finance.vendorpayment.index')->with('error','There is no payment balance available for this Vendor Invoice.');
			}

			$paymentModes = DB::table('finance_payment_mode_master')->where('is_active',1)->orderBy('payment_mode')->get();

			$bankAccounts = DB::table('finance_bank_account')->where('is_active',1)->orderBy('bank_name')->get();

			$tdsTaxes = DB::table('finance_tax_master')
							->where('tax_type','TDS')
							->where('is_active',1)
							->where('effective_from','<=',$invoice->invoice_date)
							->where(function($q) use ($invoice)
							{
								$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
							})
							->orderBy('effective_from','desc')
							->orderBy('tax_name')
							->get();

			$gstTdsTaxes = DB::table('finance_tax_master')
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->where('effective_from','<=',$invoice->invoice_date)
								->where(function($q) use ($invoice)
								{
									$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
								})
								->orderBy('effective_from','desc')
								->orderBy('tax_name')
								->get();


			$vendorFundingReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','VENDOR_FUNDING')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$vendorFundingReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$vendorFundingReceipt = $vendorFundingReceiptQuery
										->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
										->orderBy('dp.department_payment_id','asc')
										->first();


			$serviceChargeReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','SERVICE_CHARGE')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$serviceChargeReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$serviceChargeReceipt = $serviceChargeReceiptQuery
										->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
										->orderBy('dp.department_payment_id','asc')
										->first();


			$departmentGstRate = 0;

			if($vendorFundingReceipt && (float)$vendorFundingReceipt->taxable_amount > 0 && (float)$vendorFundingReceipt->gst_amount > 0)
			{
				$departmentGstRate = round(((float)$vendorFundingReceipt->gst_amount / (float)$vendorFundingReceipt->taxable_amount) * 100,2);
			}

			$departmentFundingReceipt = $vendorFundingReceipt;

			$vendorFundingTdsTaxId = $vendorFundingReceipt->tds_tax_id ?? null;
			$vendorFundingGstTdsTaxId = $vendorFundingReceipt->gst_tds_tax_id ?? null;

			$vendorFundingTdsRate = 0;
			$vendorFundingGstTdsRate = 0;

			if($vendorFundingTdsTaxId)
			{
				$vendorFundingTds = DB::table('finance_tax_master')
										->where('tax_id',$vendorFundingTdsTaxId)
										->where('tax_type','TDS')
										->first();

				if($vendorFundingTds)
				{
					$vendorFundingTdsRate = round((float)$vendorFundingTds->rate,2);
				}
			}

			if($vendorFundingGstTdsTaxId)
			{
				$vendorFundingGstTds = DB::table('finance_tax_master')
											->where('tax_id',$vendorFundingGstTdsTaxId)
											->where('tax_type','GST_TDS')
											->first();

				if($vendorFundingGstTds)
				{
					$vendorFundingGstTdsRate = round((float)$vendorFundingGstTds->rate,2);
				}
			}

			$serviceChargeTdsTaxId = $serviceChargeReceipt->tds_tax_id ?? null;
			$serviceChargeGstTdsTaxId = $serviceChargeReceipt->gst_tds_tax_id ?? null;

			$serviceChargeTdsRate = 0;
			$serviceChargeGstTdsRate = 0;

			if($serviceChargeTdsTaxId)
			{
				$serviceChargeTds = DB::table('finance_tax_master')
										->where('tax_id',$serviceChargeTdsTaxId)
										->where('tax_type','TDS')
										->first();

				if($serviceChargeTds)
				{
					$serviceChargeTdsRate = round((float)$serviceChargeTds->rate,2);
				}
			}

			if($serviceChargeGstTdsTaxId)
			{
				$serviceChargeGstTds = DB::table('finance_tax_master')
											->where('tax_id',$serviceChargeGstTdsTaxId)
											->where('tax_type','GST_TDS')
											->first();

				if($serviceChargeGstTds)
				{
					$serviceChargeGstTdsRate = round((float)$serviceChargeGstTds->rate,2);
				}
			}

			$defaultTdsTaxId = $invoice->vendor_tds_tax_id;
			$defaultTdsRate = round((float)$invoice->vendor_tds_rate,2);

			$defaultGstTdsTaxId = $invoice->vendor_gst_tds_tax_id;
			$defaultGstTdsRate = round((float)$invoice->vendor_gst_tds_rate,2);

			return view('finance.vendorpayment.form',compact(
					'invoice',
					'totalPaid',
					'invoiceBalance',
					'paymentModes',
					'bankAccounts',
					'tdsTaxes',
					'gstTdsTaxes',
					'defaultTdsTaxId',
					'defaultGstTdsTaxId',
					'defaultTdsRate',
					'defaultGstTdsRate',
					'departmentGstRate',
					'departmentFundingReceipt',
					'vendorFundingReceipt',
					'serviceChargeReceipt',
					'vendorFundingTdsTaxId',
					'vendorFundingTdsRate',
					'vendorFundingGstTdsTaxId',
					'vendorFundingGstTdsRate',
					'serviceChargeTdsTaxId',
					'serviceChargeTdsRate',
					'serviceChargeGstTdsTaxId',
					'serviceChargeGstTdsRate'
			));
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.vendorpayment.index')->with('error',$e->getMessage());
		}
	}
*/

public function makeVendorPayment($id)
{
	try
	{
		$voucherId = Crypt::decrypt($id);

		$invoice = DB::table('invoice_mpr as inv')
						->leftJoin('vendor_tbl as v','v.vendorid','=','inv.vendor_id')
						->leftJoin('eoi_work_order as wo','wo.orderid','=','inv.order_id')
						->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
						->leftJoin('users_tbl as f','f.userid','=','e.userid')
						->select('inv.*','v.companyname','v.categoryid','v.gstnumber','v.tierid','wo.ordernumber','wo.requestid as wo_request_id','e.eoinumber','f.ispm')
						->where('inv.voucher_id',$voucherId)
						->where('inv.finance_status','Verified')
						->whereIn('inv.payment_status',['Unpaid','Partially Paid'])
						->first();

		if(!$invoice)
		{
			return redirect()->route('finance.vendorpayment.index')->with('error','Vendor Invoice not found or is not available for payment.');
		}

		/*
		|--------------------------------------------------------------------------
		| CASE TYPE
		|--------------------------------------------------------------------------
		| CSF = categoryid 2 AND ispm 0
		| AWD = all other cases
		*/

		if($invoice->categoryid == 2 && $invoice->ispm == 0)
		{
			$caseType = 'CSF';
		}
		else
		{
			$caseType = 'AWD';
		}

		$totalPaid = DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount');

		$invoiceBalance = round((float)$invoice->net_invoice_value - (float)$totalPaid,2);

		if($invoiceBalance <= 0)
		{
			return redirect()->route('finance.vendorpayment.index')->with('error','There is no payment balance available for this Vendor Invoice.');
		}

		$paymentModes = DB::table('finance_payment_mode_master')->where('is_active',1)->orderBy('payment_mode')->get();

		$bankAccounts = DB::table('finance_bank_account')->where('is_active',1)->orderBy('bank_name')->get();

		/*
		|--------------------------------------------------------------------------
		| VENDOR PAYMENT TAX MASTER
		|--------------------------------------------------------------------------
		| Required for both CSF and AWD.
		*/

		$tdsTaxes = DB::table('finance_tax_master')
						->where('tax_type','TDS')
						->where('is_active',1)
						->where('effective_from','<=',$invoice->invoice_date)
						->where(function($q) use ($invoice)
						{
							$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
						})
						->orderBy('effective_from','desc')
						->orderBy('rate')
						->get();

		$gstTdsTaxes = DB::table('finance_tax_master')
							->where('tax_type','GST_TDS')
							->where('is_active',1)
							->where('effective_from','<=',$invoice->invoice_date)
							->where(function($q) use ($invoice)
							{
								$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
							})
							->orderBy('effective_from','desc')
							->orderBy('rate')
							->get();

		/*
		|--------------------------------------------------------------------------
		| DEFAULT VALUES
		|--------------------------------------------------------------------------
		| These are required for both CSF and AWD.
		*/

		$departmentGstRate = 0;
		$departmentFundingReceipt = null;
		$vendorFundingReceipt = null;
		$serviceChargeReceipt = null;

		$vendorFundingTdsTaxId = null;
		$vendorFundingTdsRate = 0;
		$vendorFundingGstTdsTaxId = null;
		$vendorFundingGstTdsRate = 0;

		$serviceChargeTdsTaxId = null;
		$serviceChargeTdsRate = 0;
		$serviceChargeGstTdsTaxId = null;
		$serviceChargeGstTdsRate = 0;

		/*
		|--------------------------------------------------------------------------
		| CSF ONLY - DEPARTMENT FUNDING / FUND AVAILABILITY
		|--------------------------------------------------------------------------
		| AWD does not use Department Payment or Fund Availability.
		*/

		if($caseType == 'CSF')
		{
			/*
			|
			| VENDOR FUNDING DEPARTMENT PAYMENT
			|
			| Used for:
			| - Vendor Invoice TDS
			| - Vendor Invoice GST-TDS
			| - Department GST rate
			| - Vendor Funding available fund
			*/

			$vendorFundingReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','VENDOR_FUNDING')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$vendorFundingReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$vendorFundingReceipt = $vendorFundingReceiptQuery
										->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
										->orderBy('dp.department_payment_id','asc')
										->first();

			/*
			|
			| SERVICE CHARGE DEPARTMENT PAYMENT
			|
			| Used for:
			| - CHiPS Administrative Charges TDS
			| - CHiPS Administrative Charges GST-TDS
			*/

			$serviceChargeReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','SERVICE_CHARGE')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$serviceChargeReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$serviceChargeReceipt = $serviceChargeReceiptQuery
										->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
										->orderBy('dp.department_payment_id','asc')
										->first();

			/*
			|
			| DEPARTMENT GST RATE
			|
			| GST rate is taken from VENDOR_FUNDING Department Payment.
			*/

			if($vendorFundingReceipt && (float)$vendorFundingReceipt->taxable_amount > 0 && (float)$vendorFundingReceipt->gst_amount > 0)
			{
				$departmentGstRate = round(((float)$vendorFundingReceipt->gst_amount / (float)$vendorFundingReceipt->taxable_amount) * 100,2);
			}

			$departmentFundingReceipt = $vendorFundingReceipt;

			/*
			|
			| VENDOR FUNDING TAX RATES
			|
			*/

			$vendorFundingTdsTaxId = $vendorFundingReceipt->tds_tax_id ?? null;
			$vendorFundingGstTdsTaxId = $vendorFundingReceipt->gst_tds_tax_id ?? null;

			if($vendorFundingTdsTaxId)
			{
				$vendorFundingTds = DB::table('finance_tax_master')
										->where('tax_id',$vendorFundingTdsTaxId)
										->where('tax_type','TDS')
										->first();

				if($vendorFundingTds)
				{
					$vendorFundingTdsRate = round((float)$vendorFundingTds->rate,2);
				}
			}

			if($vendorFundingGstTdsTaxId)
			{
				$vendorFundingGstTds = DB::table('finance_tax_master')
											->where('tax_id',$vendorFundingGstTdsTaxId)
											->where('tax_type','GST_TDS')
											->first();

				if($vendorFundingGstTds)
				{
					$vendorFundingGstTdsRate = round((float)$vendorFundingGstTds->rate,2);
				}
			}

			/*
			|
			| SERVICE CHARGE TAX RATES
			|
			*/

			$serviceChargeTdsTaxId = $serviceChargeReceipt->tds_tax_id ?? null;
			$serviceChargeGstTdsTaxId = $serviceChargeReceipt->gst_tds_tax_id ?? null;

			if($serviceChargeTdsTaxId)
			{
				$serviceChargeTds = DB::table('finance_tax_master')
										->where('tax_id',$serviceChargeTdsTaxId)
										->where('tax_type','TDS')
										->first();

				if($serviceChargeTds)
				{
					$serviceChargeTdsRate = round((float)$serviceChargeTds->rate,2);
				}
			}

			if($serviceChargeGstTdsTaxId)
			{
				$serviceChargeGstTds = DB::table('finance_tax_master')
											->where('tax_id',$serviceChargeGstTdsTaxId)
											->where('tax_type','GST_TDS')
											->first();

				if($serviceChargeGstTds)
				{
					$serviceChargeGstTdsRate = round((float)$serviceChargeGstTds->rate,2);
				}
			}
		}

		/*
		|--------------------------------------------------------------------------
		| VENDOR PAYMENT TAX SNAPSHOT
		|--------------------------------------------------------------------------
		| Required for both CSF and AWD.
		|
		| These values were selected during Finance Invoice Verification.
		*/

		$defaultTdsTaxId = $invoice->vendor_tds_tax_id;
		$defaultTdsRate = round((float)$invoice->vendor_tds_rate,2);

		$defaultGstTdsTaxId = $invoice->vendor_gst_tds_tax_id;
		$defaultGstTdsRate = round((float)$invoice->vendor_gst_tds_rate,2);

		return view('finance.vendorpayment.form',compact(
				'invoice',
				'caseType',
				'totalPaid',
				'invoiceBalance',
				'paymentModes',
				'bankAccounts',
				'tdsTaxes',
				'gstTdsTaxes',
				'defaultTdsTaxId',
				'defaultGstTdsTaxId',
				'defaultTdsRate',
				'defaultGstTdsRate',
				'departmentGstRate',
				'departmentFundingReceipt',
				'vendorFundingReceipt',
				'serviceChargeReceipt',
				'vendorFundingTdsTaxId',
				'vendorFundingTdsRate',
				'vendorFundingGstTdsTaxId',
				'vendorFundingGstTdsRate',
				'serviceChargeTdsTaxId',
				'serviceChargeTdsRate',
				'serviceChargeGstTdsTaxId',
				'serviceChargeGstTdsRate'
		));
	}
	catch(\Exception $e)
	{
		return redirect()->route('finance.vendorpayment.index')->with('error',$e->getMessage());
	}
}

/* public function storeVendorPayment(Request $request)
{
	if($request->payment_date)
	{
		$request->merge(['payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d')]);
	}

	$rules = [
		'voucher_id' 		=> 	'required|integer',
		'payment_date' 		=> 	'required|date|before_or_equal:today',
		'payment_mode_id' 	=> 	'required|integer',
		'bank_account_id' 	=> 	'required|integer',
		'gross_amount' 		=> 	'required|numeric|min:0.01',
		'tds_tax_id' 		=> 	'required|numeric',
		'gst_tds_tax_id' 	=> 	'required|numeric',
		'voucher_number' 	=> 	'nullable|max:50',
		'transaction_no' 	=> 	'nullable|max:100',
		'remarks' 			=> 	'nullable|max:1000',
		'attachment' 		=> 	'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
	];

	$messages = [
		'voucher_id.required' 			=> 	'Vendor Invoice is required.',
		'payment_date.required' 		=> 	'Payment Date is required.',
		'payment_date.date' 			=> 	'Please enter a valid Payment Date.',
		'payment_date.before_or_equal' 	=> 	'Payment Date cannot be greater than today.',
		'payment_mode_id.required' 		=> 	'Payment Mode is required.',
		'bank_account_id.required' 		=> 	'Bank Account is required.',
		'gross_amount.required' 		=> 	'Gross Payment Amount is required.',
		'gross_amount.numeric' 			=> 	'Gross Payment Amount must be numeric.',
		'gross_amount.min' 				=> 	'Gross Payment Amount should be greater than zero.',
		'tds_tax_id.required' 			=> 	'Please select TDS rate.',
		'gst_tds_tax_id.required' 		=> 	'Please select GST-TDS rate.',
		'tds_tax_id.numeric' 			=> 	'Invalid TDS selected.',
		'gst_tds_tax_id.numeric' 		=> 	'Invalid GST-TDS selected.',
		'voucher_number.max' 			=> 	'Voucher Number cannot exceed 50 characters.',
		'transaction_no.max' 			=> 	'Transaction Number cannot exceed 100 characters.',
		'remarks.max' 					=> 	'Remarks cannot exceed 1000 characters.',
		'attachment.mimes' 				=> 	'Attachment must be PDF, JPG, JPEG or PNG.',
		'attachment.max' 				=> 	'Attachment size should not exceed 5 MB.'
	];

	$validatedData = $request->validate($rules,$messages);
	
	$validatedData['tds_tax_id']	=	DB::table('finance_tax_master')
										->where('rate',$validatedData['tds_tax_id'])
										->where('is_active',1)
										->where('tax_type','TDS')
										->value('tax_id');

	$validatedData['gst_tds_tax_id']=	DB::table('finance_tax_master')
										->where('rate',$validatedData['gst_tds_tax_id'])
										->where('is_active',1)
										->where('tax_type','GST_TDS')
										->value('tax_id');

	$request->tds_tax_id		=	$validatedData['tds_tax_id'];
	$request->gst_tds_tax_id	=	$validatedData['gst_tds_tax_id'];
	
	DB::beginTransaction();

	$attachment = '';

	try
	{

		$invoice = DB::table('invoice_mpr as inv')
						->leftJoin('eoi_work_order as wo','wo.orderid','=','inv.order_id')
						->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
						->leftJoin('users_tbl as f','f.userid','=','e.userid')
						->select('inv.*','wo.requestid as wo_request_id','f.ispm','e.userid','e.categoryid')
						->where('inv.voucher_id',$request->voucher_id)
						->where('inv.finance_status','Verified')
						->whereIn('inv.payment_status',['Unpaid','Partially Paid'])
						->lockForUpdate()
						->first();

		if(!$invoice)
		{
			throw new \Exception('Vendor Invoice not found or is not available for payment.');
		}

		if($invoice->categoryid == 2 && $invoice->ispm == 0)
		{
			$caseType = 'CSF';
		}
		else
		{
			$caseType = 'AWD';
		}

		$invoiceGrossAmount = round((float)$invoice->net_invoice_value,2);

		if($invoiceGrossAmount <= 0)
		{
			throw new \Exception('Gross Invoice Value is not available.');
		}

		$totalPreviousPayments = round((float)DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount'),2);

		$remainingInvoiceAmount = round($invoiceGrossAmount - $totalPreviousPayments,2);

		if($remainingInvoiceAmount <= 0)
		{
			throw new \Exception('There is no payment balance available against this Vendor Invoice.');
		}

		$grossAmount = round((float)$request->gross_amount,2);

		if($grossAmount <= 0)
		{
			throw new \Exception('Gross Payment Amount should be greater than zero.');
		}

		if($grossAmount > $remainingInvoiceAmount)
		{
			throw new \Exception('Gross Payment Amount cannot be greater than remaining Invoice Amount of ₹'.number_format($remainingInvoiceAmount,2));
		}

		$invoiceBasicAmount = round((float)$invoice->taxable_amount,2);

		if($invoiceBasicAmount <= 0)
		{
			throw new \Exception('Vendor Invoice Basic / Taxable Amount is not available.');
		}

		$availableFund = 0;
		$adminChargePercent = 0;
		$adminChargeAmount = 0;
		$approvalAmount = 0;
		$basicAmount = 0;

		if($caseType == 'CSF')
		{
			$vendorFundingReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','VENDOR_FUNDING')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$vendorFundingReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$vendorFundingReceipt = $vendorFundingReceiptQuery->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')->orderBy('dp.department_payment_id','asc')->first();

			if(!$vendorFundingReceipt)
			{
				throw new \Exception('VENDOR_FUNDING Department Receipt not found for this Vendor Invoice.');
			}

			$serviceChargeReceiptQuery = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','SERVICE_CHARGE')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled');

			if(!empty($invoice->request_id))
			{
				$serviceChargeReceiptQuery->where('dn.request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeReceiptQuery->where('dn.order_id',$invoice->order_id);
			}

			$serviceChargeReceipt = $serviceChargeReceiptQuery->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')->orderBy('dp.department_payment_id','asc')->first();

			if(!$serviceChargeReceipt)
			{
				throw new \Exception('SERVICE_CHARGE Department Receipt not found for this Vendor Invoice.');
			}

			$departmentGstRate = 0;

			if((float)$vendorFundingReceipt->taxable_amount > 0 && (float)$vendorFundingReceipt->gst_amount > 0)
			{
				$departmentGstRate = round(((float)$vendorFundingReceipt->gst_amount / (float)$vendorFundingReceipt->taxable_amount) * 100,2);
			}

			$vendorFundingTdsRate = 0;

			if(!empty($vendorFundingReceipt->tds_tax_id))
			{
				$vendorFundingTdsTax	=	DB::table('finance_tax_master')
											->where('tax_id',$vendorFundingReceipt->tds_tax_id)
											->where('tax_type','TDS')->where('is_active',1)
											->where('effective_from','<=',$invoice->invoice_date)
											->where(function($q) use ($invoice){
												$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
											})
											->first();

				if($vendorFundingTdsTax)
				{
					$vendorFundingTdsRate = round((float)$vendorFundingTdsTax->rate,2);
				}
			}

			$vendorFundingGstTdsRate = 0;

			if(!empty($vendorFundingReceipt->gst_tds_tax_id))
			{
				$vendorFundingGstTdsTax	=	DB::table('finance_tax_master')
											->where('tax_id',$vendorFundingReceipt->gst_tds_tax_id)
											->where('tax_type','GST_TDS')
											->where('is_active',1)
											->where('effective_from','<=',$invoice->invoice_date)
											->where(function($q) use ($invoice){
												$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
											})
											->first();

				if($vendorFundingGstTdsTax)
				{
					$vendorFundingGstTdsRate = round((float)$vendorFundingGstTdsTax->rate,2);
				}
			}

			$serviceChargeTdsRate = 0;

			if(!empty($serviceChargeReceipt->tds_tax_id))
			{
				$serviceChargeTdsTax	=	DB::table('finance_tax_master')
											->where('tax_id',$serviceChargeReceipt->tds_tax_id)
											->where('tax_type','TDS')
											->where('is_active',1)
											->where('effective_from','<=',$invoice->invoice_date)
											->where(function($q) use ($invoice){
												$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
											})
											->first();

				if($serviceChargeTdsTax)
				{
					$serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
				}
			}

			$serviceChargeGstTdsRate = 0;

			if(!empty($serviceChargeReceipt->gst_tds_tax_id))
			{
				$serviceChargeGstTdsTax	=	DB::table('finance_tax_master')
											->where('tax_id',$serviceChargeReceipt->gst_tds_tax_id)
											->where('tax_type','GST_TDS')
											->where('is_active',1)
											->where('effective_from','<=',$invoice->invoice_date)
											->where(function($q) use ($invoice){
												$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
											})
											->first();

				if($serviceChargeGstTdsTax)
				{
					$serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
				}
			}

			$fullVendorFundingTdsAmount 	= 	round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);
			$fullVendorFundingGstTdsAmount 	= 	round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);
			$fullVendorNetReceived 			= 	round($invoiceGrossAmount - $fullVendorFundingTdsAmount - $fullVendorFundingGstTdsAmount,2);
			$adminChargePercent 			= 	round((float)$invoice->admin_charge_percent,2);
			$fullAdminBasicAmount 			= 	round($invoiceBasicAmount * $adminChargePercent / 100,2);
			$fullAdminGstAmount 			= 	round($fullAdminBasicAmount * $departmentGstRate / 100,2);
			$fullAdminGrossAmount 			= 	round($fullAdminBasicAmount + $fullAdminGstAmount,2);
			$fullServiceChargeTdsAmount 	= 	round($fullAdminBasicAmount * $serviceChargeTdsRate / 100,2);
			$fullServiceChargeGstTdsAmount 	= 	round($fullAdminBasicAmount * $serviceChargeGstTdsRate / 100,2);
			$fullAdminChargeAmount 			= 	round($fullAdminGrossAmount - $fullServiceChargeTdsAmount - $fullServiceChargeGstTdsAmount,2);
			$fullAvailableFund 				= 	round($fullVendorNetReceived + $fullAdminChargeAmount,2);
			$paymentRatio 					= 	round($grossAmount / $invoiceGrossAmount,10);
			$availableFund 					= 	round($fullAvailableFund * $paymentRatio,2);
			$adminChargeAmount 				= 	round($fullAdminChargeAmount * $paymentRatio,2);
			$approvalAmount 				= 	round($availableFund - $adminChargeAmount,2);

			if($approvalAmount < 0)
			{
				$approvalAmount = 0;
			}

			$basicAmount = round($invoiceBasicAmount * $paymentRatio,2);
		}

		if($caseType == 'AWD')
		{
			$availableFund = $grossAmount;

			$adminChargePercent = 0;

			$adminChargeAmount = 0;

			$approvalAmount = $grossAmount;

			$paymentRatio = round($grossAmount / $invoiceGrossAmount,10);

			$basicAmount = round($invoiceBasicAmount * $paymentRatio,2);
		}

		$tdsTaxId = NULL;
		$tdsRate = 0;
		$tdsAmount = 0;

		if(!empty($request->tds_tax_id))
		{
			$tdsTax = DB::table('finance_tax_master')->where('tax_id',$request->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

			if(!$tdsTax)
			{
				throw new \Exception('Selected TDS rate is invalid or inactive.');
			}

			$tdsTaxId = $tdsTax->tax_id;
			$tdsRate = round((float)$tdsTax->rate,2);
			$tdsAmount = round($basicAmount * $tdsRate / 100,2);
		}

		$gstTdsTaxId = NULL;
		$gstTdsRate = 0;
		$gstTdsAmount = 0;

		if(!empty($request->gst_tds_tax_id))
		{
			$gstTdsTax	=	DB::table('finance_tax_master')
							->where('tax_id',$request->gst_tds_tax_id)
							->where('tax_type','GST_TDS')
							->where('is_active',1)
							->where('effective_from','<=',$invoice->invoice_date)
							->where(function($q) use ($invoice){
									$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
							})
							->first();

			if(!$gstTdsTax)
			{
				throw new \Exception('Selected GST-TDS rate is invalid or inactive.');
			}

			$gstTdsTaxId = $gstTdsTax->tax_id;
			$gstTdsRate = round((float)$gstTdsTax->rate,2);
			$gstTdsAmount = round($basicAmount * $gstTdsRate / 100,2);
		}

		$netPaidAmount = round($approvalAmount - $tdsAmount - $gstTdsAmount,2);

		if($netPaidAmount < 0)
		{
			throw new \Exception('TDS and GST-TDS deductions cannot be greater than the Approval Amount.');
		}

		$paymentMode = DB::table('finance_payment_mode_master')->where('payment_mode_id',$request->payment_mode_id)->where('is_active',1)->first();

		if(!$paymentMode)
		{
			throw new \Exception('Invalid Payment Mode selected.');
		}

		$bankAccount = DB::table('finance_bank_account')->where('bank_account_id',$request->bank_account_id)->where('is_active',1)->first();

		if(!$bankAccount)
		{
			throw new \Exception('Invalid Bank Account selected.');
		}


		if($request->hasFile('attachment'))
		{
			$file = $request->file('attachment');
			$attachment = $file->store('uploads/vendor_payments','public');
		}


		$vendorPaymentId = DB::table('finance_vendor_payment')->insertGetId([
			'order_id' => $invoice->order_id,
			'request_id' => $invoice->request_id ?: NULL,
			'voucher_id' => $invoice->voucher_id,
			'vendor_id' => $invoice->vendor_id,
			'payment_date' => $request->payment_date,
			'payment_mode_id' => $request->payment_mode_id,
			'voucher_number' => $request->voucher_number ?: NULL,
			'transaction_no' => $request->transaction_no ?: NULL,
			'bank_id' => $bankAccount->bank_account_id ?? NULL,
			'bank_name' => $bankAccount->bank_name ?? NULL,
			'gross_amount' => $grossAmount,
			'available_fund' => $availableFund,
			'admin_charge_percent' => $adminChargePercent,
			'admin_charge_amount' => $adminChargeAmount,
			'approval_amount' => $approvalAmount,
			'basic_amount' => $basicAmount,
			'tds_tax_id' => $tdsTaxId,
			'tds_rate' => $tdsRate,
			'tds_amount' => $tdsAmount,
			'gst_tds_tax_id' => $gstTdsTaxId,
			'gst_tds_rate' => $gstTdsRate,
			'gst_tds_amount' => $gstTdsAmount,
			'net_paid_amount' => $netPaidAmount,
			'remarks' => $request->remarks ?: NULL,
			'attachment' => $attachment ?: NULL,
			'created_by' => session('userId'),
			'created_at' => now()
		]);


		$newPaidAmount = round($totalPreviousPayments + $grossAmount,2);

		$newBalance = round($invoiceGrossAmount - $newPaidAmount,2);

		if($newBalance < 0)
		{
			$newBalance = 0;
		}

		$paymentStatus = $newBalance <= 0 ? 'Paid' : 'Partially Paid';

		DB::table('invoice_mpr')->where('voucher_id',$invoice->voucher_id)->update([
			'net_paid_value' => $newPaidAmount,
			'net_balance_value' => $newBalance,
			'payment_status' => $paymentStatus,
			'is_paid' => $paymentStatus == 'Paid' ? 1 : 0
		]);


		DB::table('finance_ledger')->insert([
			'order_id' => $invoice->order_id ?: 0,
			'request_id' => $invoice->request_id ?: NULL,
			'demand_note_id' => NULL,
			'department_payment_id' => NULL,
			'department_invoice_id' => NULL,
			'vendor_invoice_id' => $invoice->voucher_id,
			'vendor_payment_id' => $vendorPaymentId,
			'reference_type' => 'VENDOR_PAYMENT',
			'reference_id' => $vendorPaymentId,
			'transaction_date' => $request->payment_date,
			'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
			'ledger_type' => 'Payment',
			'dr_amount' => $approvalAmount,
			'cr_amount' => 0,
			'remarks' => 'Vendor Payment',
			'narration' => 'Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
			'created_by' => session('userId'),
			'created_at' => now()
		]);


		DB::table('finance_ledger')->insert([
			'order_id' => $invoice->order_id ?: 0,
			'request_id' => $invoice->request_id ?: NULL,
			'demand_note_id' => NULL,
			'department_payment_id' => NULL,
			'department_invoice_id' => NULL,
			'vendor_invoice_id' => $invoice->voucher_id,
			'vendor_payment_id' => $vendorPaymentId,
			'reference_type' => 'VENDOR_PAYMENT_BANK',
			'reference_id' => $vendorPaymentId,
			'transaction_date' => $request->payment_date,
			'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
			'ledger_type' => 'Payment',
			'dr_amount' => 0,
			'cr_amount' => $netPaidAmount,
			'remarks' => 'Vendor Payment - Bank',
			'narration' => 'Net payment to vendor against Vendor Invoice '.$invoice->invoice_number,
			'created_by' => session('userId'),
			'created_at' => now()
		]);


		if($tdsAmount > 0)
		{
			DB::table('finance_ledger')->insert([
				'order_id' => $invoice->order_id ?: 0,
				'request_id' => $invoice->request_id ?: NULL,
				'demand_note_id' => NULL,
				'department_payment_id' => NULL,
				'department_invoice_id' => $invoice->voucher_id,
				'vendor_invoice_id' => $invoice->voucher_id,
				'vendor_payment_id' => $vendorPaymentId,
				'reference_type' => 'VENDOR_PAYMENT_TDS',
				'reference_id' => $vendorPaymentId,
				'transaction_date' => $request->payment_date,
				'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
				'ledger_type' => 'Payment',
				'dr_amount' => 0,
				'cr_amount' => $tdsAmount,
				'remarks' => 'Vendor Payment - TDS',
				'narration' => 'TDS deducted from Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
				'created_by' => session('userId'),
				'created_at' => now()
			]);
		}


		if($gstTdsAmount > 0)
		{
			DB::table('finance_ledger')->insert([
				'order_id' => $invoice->order_id ?: 0,
				'request_id' => $invoice->request_id ?: NULL,
				'demand_note_id' => NULL,
				'department_payment_id' => NULL,
				'department_invoice_id' => $invoice->voucher_id,
				'vendor_invoice_id' => $invoice->voucher_id,
				'vendor_payment_id' => $vendorPaymentId,
				'reference_type' => 'VENDOR_PAYMENT_GST_TDS',
				'reference_id' => $vendorPaymentId,
				'transaction_date' => $request->payment_date,
				'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
				'ledger_type' => 'Payment',
				'dr_amount' => 0,
				'cr_amount' => $gstTdsAmount,
				'remarks' => 'Vendor Payment - GST-TDS',
				'narration' => 'GST-TDS deducted from Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
				'created_by' => session('userId'),
				'created_at' => now()
			]);
		}

		DB::table('finance_activity_log')->insert([
			'transaction_uuid' => (string)\Illuminate\Support\Str::uuid(),
			'module_name' => 'Vendor Payment',
			'record_id' => $vendorPaymentId,
			'action' => 'CREATE',
			'action_description' => 'Vendor Payment Created',
			'remarks' => 'Vendor Payment of ₹'.number_format($grossAmount,2).' created against Vendor Invoice '.$invoice->invoice_number.'.',
			'action_by' => session('userId'),
			'action_date' => now(),
			'ip_address' => $request->ip()
		]);

		DB::commit();

		session()->flash('success','Vendor Payment created successfully.');

		return response()->json([
			'status' => 1,
			'message' => 'Vendor Payment created successfully.',
			'redirect' => route('finance.vendorpayment.index')
		]);
	}
	catch(\Exception $e)
	{
		DB::rollBack();

		if(!empty($attachment))
		{
			Storage::disk('public')->delete($attachment);
		}

		return response()->json([
			'status' => 0,
			'message' => $e->getMessage()
		],500);
	}
} */

public function storeVendorPayment(Request $request)
{
    if($request->payment_date)
    {
        $request->merge(['payment_date' => Carbon::parse($request->payment_date)->format('Y-m-d')]);
    }

    $rules = [
        'voucher_id'         =>     'required|integer',
        'payment_date'         =>     'required|date|before_or_equal:today',
        'payment_mode_id'     =>     'required|integer',
        'bank_account_id'     =>     'required|integer',
        'gross_amount'         =>     'required|numeric|min:0.01',
        'tds_tax_id'         =>     'required|numeric',
        'gst_tds_tax_id'     =>     'required|numeric',
        'voucher_number'     =>     'nullable|max:50',
        'transaction_no'     =>     'nullable|max:100',
        'remarks'             =>     'nullable|max:1000',
        'attachment'         =>     'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120'
    ];

    $messages = [
        'voucher_id.required'             =>     'Vendor Invoice is required.',
        'payment_date.required'         =>     'Payment Date is required.',
        'payment_date.date'             =>     'Please enter a valid Payment Date.',
        'payment_date.before_or_equal'     =>     'Payment Date cannot be greater than today.',
        'payment_mode_id.required'         =>     'Payment Mode is required.',
        'bank_account_id.required'         =>     'Bank Account is required.',
        'gross_amount.required'         =>     'Gross Payment Amount is required.',
        'gross_amount.numeric'             =>     'Gross Payment Amount must be numeric.',
        'gross_amount.min'                 =>     'Gross Payment Amount should be greater than zero.',
        'tds_tax_id.required'             =>     'Please select TDS rate.',
        'gst_tds_tax_id.required'         =>     'Please select GST-TDS rate.',
        'tds_tax_id.numeric'             =>     'Invalid TDS selected.',
        'gst_tds_tax_id.numeric'         =>     'Invalid GST-TDS selected.',
        'voucher_number.max'             =>     'Voucher Number cannot exceed 50 characters.',
        'transaction_no.max'             =>     'Transaction Number cannot exceed 100 characters.',
        'remarks.max'                     =>     'Remarks cannot exceed 1000 characters.',
        'attachment.mimes'                 =>     'Attachment must be PDF, JPG, JPEG or PNG.',
        'attachment.max'                 =>     'Attachment size should not exceed 5 MB.'
    ];

    $validatedData = $request->validate($rules,$messages);
    
    $validatedData['tds_tax_id']    =    DB::table('finance_tax_master')
                                        ->where('rate',$validatedData['tds_tax_id'])
                                        ->where('is_active',1)
                                        ->where('tax_type','TDS')
                                        ->value('tax_id');

    $validatedData['gst_tds_tax_id']=    DB::table('finance_tax_master')
                                        ->where('rate',$validatedData['gst_tds_tax_id'])
                                        ->where('is_active',1)
                                        ->where('tax_type','GST_TDS')
                                        ->value('tax_id');

    $request->tds_tax_id        =    $validatedData['tds_tax_id'];
    $request->gst_tds_tax_id    =    $validatedData['gst_tds_tax_id'];
    
    DB::beginTransaction();

    $attachment = '';

    try
    {
        /*
        |--------------------------------------------------------------------------
        | GET VERIFIED INVOICE
        |--------------------------------------------------------------------------
        | categoryid = 2 AND ispm = 0 => CSF
        | Otherwise => AWD
        */

        $invoice = DB::table('invoice_mpr as inv')
                        ->leftJoin('eoi_work_order as wo','wo.orderid','=','inv.order_id')
                        ->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
                        ->leftJoin('users_tbl as f','f.userid','=','e.userid')
                        ->select('inv.*','wo.requestid as wo_request_id','f.ispm','e.userid','e.categoryid')
                        ->where('inv.voucher_id',$request->voucher_id)
                        ->where('inv.finance_status','Verified')
                        ->whereIn('inv.payment_status',['Unpaid','Partially Paid'])
                        ->lockForUpdate()
                        ->first();

        if(!$invoice)
        {
            throw new \Exception('Vendor Invoice not found or is not available for payment.');
        }

        if($invoice->categoryid == 2 && $invoice->ispm == 0)
        {
            $caseType = 'CSF';
        }
        else
        {
            $caseType = 'AWD';
        }

        /*
        |--------------------------------------------------------------------------
        | INVOICE AMOUNTS
        |--------------------------------------------------------------------------
        */

        $invoiceGrossAmount = round((float)$invoice->net_invoice_value,2);

        if($invoiceGrossAmount <= 0)
        {
            throw new \Exception('Gross Invoice Value is not available.');
        }

        $totalPreviousPayments = round((float)DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount'),2);

        $remainingInvoiceAmount = round($invoiceGrossAmount - $totalPreviousPayments,2);

        if($remainingInvoiceAmount <= 0)
        {
            throw new \Exception('There is no payment balance available against this Vendor Invoice.');
        }

        $grossAmount = round((float)$request->gross_amount,2);

        if($grossAmount <= 0)
        {
            throw new \Exception('Gross Payment Amount should be greater than zero.');
        }

        if($grossAmount > $remainingInvoiceAmount)
        {
            throw new \Exception('Gross Payment Amount cannot be greater than remaining Invoice Amount of ₹'.number_format($remainingInvoiceAmount,2));
        }

        $invoiceBasicAmount = round((float)$invoice->taxable_amount,2);

        if($invoiceBasicAmount <= 0)
        {
            throw new \Exception('Vendor Invoice Basic / Taxable Amount is not available.');
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT CALCULATION VALUES
        |--------------------------------------------------------------------------
        */

        $availableFund = 0;
        $adminChargePercent = 0;
        $adminChargeAmount = 0;
        $approvalAmount = 0;
        $basicAmount = 0;

        /*
        |--------------------------------------------------------------------------
        | CSF - FUND AVAILABILITY CALCULATION
        |--------------------------------------------------------------------------
        */

        if($caseType == 'CSF')
        {
            /*
            |
            | OPENING BALANCE TAX CONFIGURATION
            |
            | If Department Demand Note / Receipt does not exist,
            | use TDS and GST-TDS rates from finance_opening_balance.
            | Department GST defaults to 18% when no receipt exists.
            */

            $vendorFundingOpeningBalance = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','VENDOR_FUNDING')->where('isactive',1)->sum('opening_balance');

            $vendorFundingOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','VENDOR_FUNDING')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

            $serviceChargeOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','SERVICE_CHARGE')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

            /*
            |
            | VENDOR FUNDING DEPARTMENT PAYMENT
            |
            */

            $vendorFundingReceiptQuery = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled');

            if(!empty($invoice->request_id))
            {
                $vendorFundingReceiptQuery->where('dn.request_id',$invoice->request_id);
            }
            else
            {
                $vendorFundingReceiptQuery->where('dn.order_id',$invoice->order_id);
            }

            $vendorFundingReceipt = $vendorFundingReceiptQuery->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')->orderBy('dp.department_payment_id','asc')->first();

            /*
            |
            | SERVICE CHARGE DEPARTMENT PAYMENT
            |
            */

            $serviceChargeReceiptQuery = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','SERVICE_CHARGE')->where('dn.status','Active')->where('dp.status','!=','Cancelled');

            if(!empty($invoice->request_id))
            {
                $serviceChargeReceiptQuery->where('dn.request_id',$invoice->request_id);
            }
            else
            {
                $serviceChargeReceiptQuery->where('dn.order_id',$invoice->order_id);
            }

            $serviceChargeReceipt = $serviceChargeReceiptQuery->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')->orderBy('dp.department_payment_id','asc')->first();

            /*
            |
            | DEPARTMENT GST RATE
            |
            | Receipt GST rate has priority.
            | If no receipt exists, default to 18%.
            |
            */

            $departmentGstRate = 18;

            if($vendorFundingReceipt && (float)$vendorFundingReceipt->taxable_amount > 0 && (float)$vendorFundingReceipt->gst_amount > 0)
            {
                $departmentGstRate = round(((float)$vendorFundingReceipt->gst_amount / (float)$vendorFundingReceipt->taxable_amount) * 100,2);
            }

            /*
            |
            | VENDOR FUNDING TDS RATE
            |
            | Receipt tax rate has priority.
            | Otherwise use Opening Balance tax rate.
            |
            */

            $vendorFundingTdsRate = 0;

            if($vendorFundingReceipt && !empty($vendorFundingReceipt->tds_tax_id))
            {
                $vendorFundingTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingReceipt->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

                if($vendorFundingTdsTax)
            {
                    $vendorFundingTdsRate = round((float)$vendorFundingTdsTax->rate,2);
                }
            }
            else if($vendorFundingOpeningTax)
            {
                $vendorFundingTdsRate = round((float)$vendorFundingOpeningTax->tds_rate,2);
            }

            /*
            |
            | VENDOR FUNDING GST-TDS RATE
            |
            | Receipt tax rate has priority.
            | Otherwise use Opening Balance tax rate.
            |
            */

            $vendorFundingGstTdsRate = 0;

            if($vendorFundingReceipt && !empty($vendorFundingReceipt->gst_tds_tax_id))
            {
                $vendorFundingGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingReceipt->gst_tds_tax_id)->where('tax_type','GST_TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

                if($vendorFundingGstTdsTax)
            {
                    $vendorFundingGstTdsRate = round((float)$vendorFundingGstTdsTax->rate,2);
                }
            }
            else if($vendorFundingOpeningTax)
            {
                $vendorFundingGstTdsRate = round((float)$vendorFundingOpeningTax->gst_tds_rate,2);
            }

            /*
            |
            | SERVICE CHARGE TDS RATE
            |
            | Receipt tax rate has priority.
            | Otherwise use Opening Balance tax rate.
            |
            */

            $serviceChargeTdsRate = 0;

            if($serviceChargeReceipt && !empty($serviceChargeReceipt->tds_tax_id))
            {
                $serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeReceipt->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

                if($serviceChargeTdsTax)
            {
                    $serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
                }
            }
            else if($serviceChargeOpeningTax)
            {
                $serviceChargeTdsRate = round((float)$serviceChargeOpeningTax->tds_rate,2);
            }

            /*
            |
            | SERVICE CHARGE GST-TDS RATE
            |
            | Receipt tax rate has priority.
            | Otherwise use Opening Balance tax rate.
            |
            */

            $serviceChargeGstTdsRate = 0;

            if($serviceChargeReceipt && !empty($serviceChargeReceipt->gst_tds_tax_id))
            {
                $serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeReceipt->gst_tds_tax_id)->where('tax_type','GST_TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

                if($serviceChargeGstTdsTax)
            {
                    $serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
                }
            }
            else if($serviceChargeOpeningTax)
            {
                $serviceChargeGstTdsRate = round((float)$serviceChargeOpeningTax->gst_tds_rate,2);
            }

            /*
            |
            | FUND AVAILABILITY
            |
            | Department Receipt is used when available.
            | If no receipt exists, Vendor Funding Opening Balance is used.
            |
            */

            $fullVendorFundingTdsAmount = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);
            $fullVendorFundingGstTdsAmount = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);
            $fullVendorNetReceived = round($invoiceGrossAmount - $fullVendorFundingTdsAmount - $fullVendorFundingGstTdsAmount,2);
            $adminChargePercent = round((float)$invoice->admin_charge_percent,2);
            $fullAdminBasicAmount = round($invoiceBasicAmount * $adminChargePercent / 100,2);
            $fullAdminGstAmount = round($fullAdminBasicAmount * $departmentGstRate / 100,2);
            $fullAdminGrossAmount = round($fullAdminBasicAmount + $fullAdminGstAmount,2);
            $fullServiceChargeTdsAmount = round($fullAdminBasicAmount * $serviceChargeTdsRate / 100,2);
            $fullServiceChargeGstTdsAmount = round($fullAdminBasicAmount * $serviceChargeGstTdsRate / 100,2);
            $fullAdminChargeAmount = round($fullAdminGrossAmount - $fullServiceChargeTdsAmount - $fullServiceChargeGstTdsAmount,2);
            $fullAvailableFund = round($fullVendorNetReceived + $fullAdminChargeAmount,2);
            $paymentRatio = round($grossAmount / $invoiceGrossAmount,10);
            $availableFund = round($fullAvailableFund * $paymentRatio,2);
            $adminChargeAmount = round($fullAdminChargeAmount * $paymentRatio,2);
            $approvalAmount = round($availableFund - $adminChargeAmount,2);

            if(!$vendorFundingReceipt)
            {
                $fullAvailableFund = round($vendorFundingOpeningBalance + $fullAdminChargeAmount,2);
                $availableFund = round($fullAvailableFund * $paymentRatio,2);
                $approvalAmount = round($availableFund - $adminChargeAmount,2);
            }

            if($approvalAmount < 0)
            {
                $approvalAmount = 0;
            }

            $basicAmount = round($invoiceBasicAmount * $paymentRatio,2);
        }

        /*
        |--------------------------------------------------------------------------
        | AWD - DIRECT VENDOR PAYMENT
        |--------------------------------------------------------------------------
        | No Department Payment
        | No Fund Availability
        | No Administrative Charge
        | No Department TDS/GST-TDS
        */

        if($caseType == 'AWD')
        {
            $availableFund = $grossAmount;

            $adminChargePercent = 0;

            $adminChargeAmount = 0;

            $approvalAmount = $grossAmount;

            $paymentRatio = round($grossAmount / $invoiceGrossAmount,10);

            $basicAmount = round($invoiceBasicAmount * $paymentRatio,2);
        }

        /*
        |--------------------------------------------------------------------------
        | VENDOR PAYMENT TDS
        |--------------------------------------------------------------------------
        */

        $tdsTaxId = NULL;
        $tdsRate = 0;
        $tdsAmount = 0;

        if(!empty($request->tds_tax_id))
        {
            $tdsTax = DB::table('finance_tax_master')->where('tax_id',$request->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->where('effective_from','<=',$invoice->invoice_date)->where(function($q) use ($invoice){$q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);})->first();

            if(!$tdsTax)
            {
                throw new \Exception('Selected TDS rate is invalid or inactive.');
            }

            $tdsTaxId = $tdsTax->tax_id;
            $tdsRate = round((float)$tdsTax->rate,2);
            $tdsAmount = round($basicAmount * $tdsRate / 100,2);
        }

        /*
        |--------------------------------------------------------------------------
        | VENDOR PAYMENT GST-TDS
        |--------------------------------------------------------------------------
        */

        $gstTdsTaxId = NULL;
        $gstTdsRate = 0;
        $gstTdsAmount = 0;

        if(!empty($request->gst_tds_tax_id))
        {
            $gstTdsTax    =    DB::table('finance_tax_master')
                            ->where('tax_id',$request->gst_tds_tax_id)
                            ->where('tax_type','GST_TDS')
                            ->where('is_active',1)
                            ->where('effective_from','<=',$invoice->invoice_date)
                            ->where(function($q) use ($invoice){
                                    $q->whereNull('effective_to')->orWhere('effective_to','>=',$invoice->invoice_date);
                            })
                            ->first();

            if(!$gstTdsTax)
            {
                throw new \Exception('Selected GST-TDS rate is invalid or inactive.');
            }

            $gstTdsTaxId = $gstTdsTax->tax_id;
            $gstTdsRate = round((float)$gstTdsTax->rate,2);
            $gstTdsAmount = round($basicAmount * $gstTdsRate / 100,2);
        }

        /*
        |--------------------------------------------------------------------------
        | NET PAYMENT
        |--------------------------------------------------------------------------
        */

        $netPaidAmount = round($approvalAmount - $tdsAmount - $gstTdsAmount,2);

        if($netPaidAmount < 0)
        {
            throw new \Exception('TDS and GST-TDS deductions cannot be greater than the Approval Amount.');
        }

        /*
        |--------------------------------------------------------------------------
        | PAYMENT MODE
        |--------------------------------------------------------------------------
        */

        $paymentMode = DB::table('finance_payment_mode_master')->where('payment_mode_id',$request->payment_mode_id)->where('is_active',1)->first();

        if(!$paymentMode)
        {
            throw new \Exception('Invalid Payment Mode selected.');
        }

        /*
        |--------------------------------------------------------------------------
        | BANK ACCOUNT
        |--------------------------------------------------------------------------
        */

        $bankAccount = DB::table('finance_bank_account')->where('bank_account_id',$request->bank_account_id)->where('is_active',1)->first();

        if(!$bankAccount)
        {
            throw new \Exception('Invalid Bank Account selected.');
        }

        /*
        |--------------------------------------------------------------------------
        | ATTACHMENT
        |--------------------------------------------------------------------------
        */

        if($request->hasFile('attachment'))
        {
            $file = $request->file('attachment');
            $attachment = $file->store('uploads/vendor_payments','public');
        }

        /*
        |--------------------------------------------------------------------------
        | INSERT VENDOR PAYMENT
        |--------------------------------------------------------------------------
        */

        $vendorPaymentId = DB::table('finance_vendor_payment')->insertGetId([
            'order_id' => $invoice->order_id,
            'request_id' => $invoice->request_id ?: NULL,
            'voucher_id' => $invoice->voucher_id,
            'vendor_id' => $invoice->vendor_id,
            'payment_date' => $request->payment_date,
            'payment_mode_id' => $request->payment_mode_id,
            'voucher_number' => $request->voucher_number ?: NULL,
            'transaction_no' => $request->transaction_no ?: NULL,
            'bank_id' => $bankAccount->bank_account_id ?? NULL,
            'bank_name' => $bankAccount->bank_name ?? NULL,
            'gross_amount' => $grossAmount,
            'available_fund' => $availableFund,
            'admin_charge_percent' => $adminChargePercent,
            'admin_charge_amount' => $adminChargeAmount,
            'approval_amount' => $approvalAmount,
            'basic_amount' => $basicAmount,
            'tds_tax_id' => $tdsTaxId,
            'tds_rate' => $tdsRate,
            'tds_amount' => $tdsAmount,
            'gst_tds_tax_id' => $gstTdsTaxId,
            'gst_tds_rate' => $gstTdsRate,
            'gst_tds_amount' => $gstTdsAmount,
            'net_paid_amount' => $netPaidAmount,
            'remarks' => $request->remarks ?: NULL,
            'attachment' => $attachment ?: NULL,
            'created_by' => session('userId'),
            'created_at' => now()
        ]);

        /*
        |--------------------------------------------------------------------------
        | UPDATE INVOICE PAYMENT STATUS
        |--------------------------------------------------------------------------
        */

        $newPaidAmount = round($totalPreviousPayments + $grossAmount,2);

        $newBalance = round($invoiceGrossAmount - $newPaidAmount,2);

        if($newBalance < 0)
        {
            $newBalance = 0;
        }

        $paymentStatus = $newBalance <= 0 ? 'Paid' : 'Partially Paid';

        DB::table('invoice_mpr')->where('voucher_id',$invoice->voucher_id)->update([
            'net_paid_value' => $newPaidAmount,
            'net_balance_value' => $newBalance,
            'payment_status' => $paymentStatus,
            'is_paid' => $paymentStatus == 'Paid' ? 1 : 0
        ]);

        /*
        |--------------------------------------------------------------------------
        | LEDGER - VENDOR PAYMENT
        |--------------------------------------------------------------------------
        */

        DB::table('finance_ledger')->insert([
            'order_id' => $invoice->order_id ?: 0,
            'request_id' => $invoice->request_id ?: NULL,
            'demand_note_id' => NULL,
            'department_payment_id' => NULL,
            'department_invoice_id' => NULL,
            'vendor_invoice_id' => $invoice->voucher_id,
            'vendor_payment_id' => $vendorPaymentId,
            'reference_type' => 'VENDOR_PAYMENT',
            'reference_id' => $vendorPaymentId,
            'transaction_date' => $request->payment_date,
            'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
            'ledger_type' => 'Payment',
            'dr_amount' => $approvalAmount,
            'cr_amount' => 0,
            'remarks' => 'Vendor Payment',
            'narration' => 'Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
            'created_by' => session('userId'),
            'created_at' => now()
        ]);

        /*
        |--------------------------------------------------------------------------
        | LEDGER - BANK
        |--------------------------------------------------------------------------
        */

        DB::table('finance_ledger')->insert([
            'order_id' => $invoice->order_id ?: 0,
            'request_id' => $invoice->request_id ?: NULL,
            'demand_note_id' => NULL,
            'department_payment_id' => NULL,
            'department_invoice_id' => NULL,
            'vendor_invoice_id' => $invoice->voucher_id,
            'vendor_payment_id' => $vendorPaymentId,
            'reference_type' => 'VENDOR_PAYMENT_BANK',
            'reference_id' => $vendorPaymentId,
            'transaction_date' => $request->payment_date,
            'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
            'ledger_type' => 'Payment',
            'dr_amount' => 0,
            'cr_amount' => $netPaidAmount,
            'remarks' => 'Vendor Payment - Bank',
            'narration' => 'Net payment to vendor against Vendor Invoice '.$invoice->invoice_number,
            'created_by' => session('userId'),
            'created_at' => now()
        ]);

        /*
        |--------------------------------------------------------------------------
        | LEDGER - TDS
        |--------------------------------------------------------------------------
        */

        if($tdsAmount > 0)
        {
            DB::table('finance_ledger')->insert([
                'order_id' => $invoice->order_id ?: 0,
                'request_id' => $invoice->request_id ?: NULL,
                'demand_note_id' => NULL,
                'department_payment_id' => NULL,
                'department_invoice_id' => $invoice->voucher_id,
                'vendor_invoice_id' => $invoice->voucher_id,
                'vendor_payment_id' => $vendorPaymentId,
                'reference_type' => 'VENDOR_PAYMENT_TDS',
                'reference_id' => $vendorPaymentId,
                'transaction_date' => $request->payment_date,
                'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
                'ledger_type' => 'Payment',
                'dr_amount' => 0,
                'cr_amount' => $tdsAmount,
                'remarks' => 'Vendor Payment - TDS',
                'narration' => 'TDS deducted from Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
                'created_by' => session('userId'),
                'created_at' => now()
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | LEDGER - GST TDS
        |--------------------------------------------------------------------------
        */

        if($gstTdsAmount > 0)
        {
            DB::table('finance_ledger')->insert([
                'order_id' => $invoice->order_id ?: 0,
                'request_id' => $invoice->request_id ?: NULL,
                'demand_note_id' => NULL,
                'department_payment_id' => NULL,
                'department_invoice_id' => $invoice->voucher_id,
                'vendor_invoice_id' => $invoice->voucher_id,
                'vendor_payment_id' => $vendorPaymentId,
                'reference_type' => 'VENDOR_PAYMENT_GST_TDS',
                'reference_id' => $vendorPaymentId,
                'transaction_date' => $request->payment_date,
                'financial_year' => FinanceHelper::financialYearFromDate($request->payment_date),
                'ledger_type' => 'Payment',
                'dr_amount' => 0,
                'cr_amount' => $gstTdsAmount,
                'remarks' => 'Vendor Payment - GST-TDS',
                'narration' => 'GST-TDS deducted from Vendor Payment against Vendor Invoice '.$invoice->invoice_number,
                'created_by' => session('userId'),
                'created_at' => now()
            ]);
        }

        /*
        |--------------------------------------------------------------------------
        | ACTIVITY LOG
        |--------------------------------------------------------------------------
        */

        DB::table('finance_activity_log')->insert([
            'transaction_uuid' => (string)\Illuminate\Support\Str::uuid(),
            'module_name' => 'Vendor Payment',
            'record_id' => $vendorPaymentId,
            'action' => 'CREATE',
            'action_description' => 'Vendor Payment Created',
            'remarks' => 'Vendor Payment of ₹'.number_format($grossAmount,2).' created against Vendor Invoice '.$invoice->invoice_number.'.',
            'action_by' => session('userId'),
            'action_date' => now(),
            'ip_address' => $request->ip()
        ]);

        DB::commit();

        session()->flash('success','Vendor Payment created successfully.');

        return response()->json([
            'status' => 1,
            'message' => 'Vendor Payment created successfully.',
            'redirect' => route('finance.vendorpayment.index')
        ]);
    }
    catch(\Exception $e)
    {
        DB::rollBack();

        if(!empty($attachment))
        {
            Storage::disk('public')->delete($attachment);
        }

        return response()->json([
            'status' => 0,
            'message' => $e->getMessage()
        ],500);
    }
}



	public function financeVendorPaymentList(Request $request)
	{
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	=	$request->input('page',1);
		$projectid		=	$request->input('projectid') ?? NULL;
		$departmentid	=	$request->input('departmentid') ?? NULL;
		$managerid		=	$request->input('managerid') ?? NULL;
		$vendorid		=	$request->input('vendorid') ?? NULL;

		$data	=	DB::table('finance_vendor_payment as vp')
					->leftJoin('invoice_mpr as inv','inv.voucher_id','=','vp.voucher_id')
					->leftJoin('vendor_tbl as v','v.vendorid','=','vp.vendor_id')
					->leftJoin('eoi_work_order as wo','wo.orderid','=','vp.order_id')
					->leftJoin('eoi_request as e','e.requestid','=','vp.request_id')
					->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','vp.payment_mode_id')
					->select(
						'vp.vendor_payment_id',
						'vp.order_id',
						'vp.request_id',
						'vp.voucher_id',
						'vp.vendor_id',
						'vp.payment_date',
						'vp.payment_mode_id',
						'vp.voucher_number',
						'vp.transaction_no',
						'vp.bank_id',
						'vp.bank_name',
						'vp.gross_amount',
						'vp.available_fund',
						'vp.admin_charge_percent',
						'vp.admin_charge_amount',
						'vp.approval_amount',
						'vp.basic_amount',
						'vp.tds_tax_id',
						'vp.tds_rate',
						'vp.tds_amount',
						'vp.gst_tds_tax_id',
						'vp.gst_tds_rate',
						'vp.gst_tds_amount',
						'vp.net_paid_amount',
						'vp.remarks',
						'vp.attachment',
						'inv.invoice_number',
						'inv.invoice_date',
						'inv.net_invoice_value',
						'v.companyname',
						'e.eoinumber',
						'wo.ordernumber',
						'pm.payment_mode',
						DB::raw("CASE WHEN v.categoryid = 2 THEN 'CSF' WHEN v.categoryid = 1 THEN 'AWD' ELSE 'UNKNOWN' END as case_type")
					)
					->when($departmentid != NULL,function($query) use ($departmentid){ return $query->where('wo.department_id',$departmentid); })
					->when($managerid != NULL,function($query) use ($managerid){ return $query->where('wo.department_id',$managerid); })
					->when($vendorid != NULL,function($query) use ($vendorid){ return $query->where('v.vendorid',$vendorid); })
					->when($projectid != NULL,function($query) use ($projectid){ return $query->where('inv.finance_project_id',$projectid); })
					->when($pagesearch != '',function($query) use ($pagesearch)
					{
						return $query->where(function($q) use ($pagesearch)
						{
							$q->where('vp.voucher_number','like','%'.$pagesearch.'%')
								->orWhere('vp.transaction_no','like','%'.$pagesearch.'%')
								->orWhere('inv.invoice_number','like','%'.$pagesearch.'%')
								->orWhere('v.companyname','like','%'.$pagesearch.'%')
								->orWhere('wo.ordernumber','like','%'.$pagesearch.'%')
								->orWhere('e.eoinumber','like','%'.$pagesearch.'%');
						});
					})
					->orderBy('vp.payment_date','desc')
					->orderBy('vp.vendor_payment_id','desc')
					->paginate($pagesize,['*'],'page',$currentPage);

		return view('finance.vendorpayment.ajaxpages.vendorpaymentTable',['data' => $data]);
	}

	/*
	public function view($id)
	{
		try
		{
			$vendorPaymentId	=	Crypt::decrypt($id);

			$payment	=	DB::table('finance_vendor_payment as vp')
							->leftJoin('invoice_mpr as inv','inv.voucher_id','=','vp.voucher_id')
							->leftJoin('vendor_tbl as v','v.vendorid','=','vp.vendor_id')
							->leftJoin('eoi_work_order as wo','wo.orderid','=','vp.order_id')
							->leftJoin('eoi_request as e','e.requestid','=','vp.request_id')
							->leftJoin('users_tbl as f','f.userid','=','e.userid')
							->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','vp.payment_mode_id')
							->select(
								'vp.*',
								'inv.invoice_number',
								'inv.invoice_date',
								'inv.voucher_number as invoice_voucher_number',
								'inv.net_invoice_value',
								'inv.taxable_amount as invoice_taxable_amount',
								'v.companyname',
								'v.gstnumber',
								'v.categoryid',
								'wo.ordernumber',
								'e.eoinumber',
								'f.ispm',
								'pm.payment_mode'
							)
							->where('vp.vendor_payment_id',$vendorPaymentId)
							->first();

			if(!$payment)
			{
				return redirect()->route('finance.vendorpayment.index')->with('error','Vendor Payment not found.');
			}

			$ledger	=	DB::table('finance_ledger')
						->where('vendor_payment_id',$payment->vendor_payment_id)
						->orderBy('ledger_id','asc')
						->get();

			return view('finance.vendorpayment.view',[
				'payment'	=>	$payment,
				'ledger'	=> 	$ledger
			]);
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.vendorpayment.index')->with('error',$e->getMessage());
		}
	}
	*/

public function view($id)
{
	try
	{
		$vendorPaymentId = Crypt::decrypt($id);

		$payment = DB::table('finance_vendor_payment as vp')
					->leftJoin('invoice_mpr as inv','inv.voucher_id','=','vp.voucher_id')
					->leftJoin('vendor_tbl as v','v.vendorid','=','vp.vendor_id')
					->leftJoin('eoi_work_order as wo','wo.orderid','=','vp.order_id')
					->leftJoin('eoi_request as e','e.requestid','=','vp.request_id')
					->leftJoin('users_tbl as f','f.userid','=','e.userid')
					->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','vp.payment_mode_id')
					->select('vp.*','inv.invoice_number','inv.invoice_date','inv.voucher_number as invoice_voucher_number','inv.net_invoice_value','inv.taxable_amount as invoice_taxable_amount','v.companyname','v.gstnumber','v.categoryid','wo.ordernumber','e.eoinumber','f.ispm','pm.payment_mode')
					->where('vp.vendor_payment_id',$vendorPaymentId)
					->first();

		if(!$payment)
		{
			return redirect()->route('finance.vendorpayment.index')->with('error','Vendor Payment not found.');
		}

		if($payment->categoryid == 2 && $payment->ispm == 0)
		{
			$caseType = 'CSF';
		}
		else
		{
			$caseType = 'AWD';
		}

		$fundingPayment = null;
		$serviceChargePayment = null;

		if($caseType == 'CSF')
		{
			$fundingPayment = DB::table('finance_department_payment as dp')
								->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
								->where('dp.order_id',$payment->order_id)
								->where('dp.status','Active')
								->where('dn.demand_note_type','VENDOR_FUNDING')
								->orderBy('dp.department_payment_id','asc')
								->first();

			$serviceChargePayment = DB::table('finance_department_payment as dp')
									->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
									->where('dp.order_id',$payment->order_id)
									->where('dp.status','Active')
									->where('dn.demand_note_type','SERVICE_CHARGE')
									->orderBy('dp.department_payment_id','asc')
									->first();
		}

		$ledger = DB::table('finance_ledger')
					->where('vendor_payment_id',$payment->vendor_payment_id)
					->orderBy('ledger_id','asc')
					->get();

		return view('finance.vendorpayment.view',[
			'payment' => $payment,
			'ledger' => $ledger,
			'caseType' => $caseType,
			'fundingPayment' => $fundingPayment,
			'serviceChargePayment' => $serviceChargePayment
		]);
	}
	catch(\Exception $e)
	{
		return redirect()->route('finance.vendorpayment.index')->with('error',$e->getMessage());
	}
}	
}
