<?php

namespace App\Http\Controllers\Finance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\Finance\FinanceService;
use App\Helpers\FinanceHelper;
class FinanceDashboardController extends Controller
{
	protected $financeService;
	
	public function __construct(FinanceService $financeService)
	{
		$this->financeService 		= 	$financeService;
	}


	public function index()
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financedashboard');

		$data = [];

		$data['pendingVendorInvoices'] = DB::table('invoice_mpr')->where('finance_status','Pending')->count();

		$data['pendingVendorPayments'] = DB::table('invoice_mpr')->where('finance_status','Verified')->whereIn('payment_status',['Unpaid','Partially Paid'])->count();

		$data['totalDepartmentReceipt'] = round((float)DB::table('finance_department_payment')->where('status','<>','Cancelled')->sum('net_received_amount'),2);

		$data['totalVendorInvoices'] = DB::table('invoice_mpr')->count();

		$data['totalVendorInvoiceAmount'] = round((float)DB::table('invoice_mpr')->sum('net_invoice_value'),2);

		$data['totalVendorPayments'] = DB::table('finance_vendor_payment')->count();

		$data['totalVendorPaymentAmount'] = round((float)DB::table('finance_vendor_payment')->sum('gross_amount'),2);

		$data['pendingPaymentAmount'] = round((float)DB::table('invoice_mpr')->where('finance_status','Verified')->whereIn('payment_status',['Unpaid','Partially Paid'])->sum('net_balance_value'),2);


		$csfOpeningBalance = round((float)DB::table('finance_opening_balance as ob')->join('eoi_request as e','e.requestid','=','ob.request_id')->join('users_tbl as u','u.userid','=','e.userid')->where('ob.isactive',1)->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0)->sum('ob.opening_balance'),2);

		$csfDepartmentReceipt = round((float)DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->join('eoi_request as e','e.requestid','=','dp.request_id')->join('users_tbl as u','u.userid','=','e.userid')->where('dp.status','<>','Cancelled')->where('dn.status','<>','Cancelled')->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0)->sum('dp.net_received_amount'),2);

		$csfReceived = round($csfOpeningBalance + $csfDepartmentReceipt,2);

		$csfUtilized = round((float)DB::table('invoice_mpr as im')->join('eoi_request as e','e.requestid','=','im.request_id')->join('users_tbl as u','u.userid','=','e.userid')->where('im.finance_status','Verified')->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0)->sum('im.fund_utilized_amount'),2);

		$csfAvailable = round($csfReceived - $csfUtilized,2);
		
		$data['availableFundBalance'] = $csfAvailable;

		$data['receiptUtilization'] = [
			[
				'type'=>'CSF Projects',
				'received'=>$csfReceived,
				'utilized'=>$csfUtilized,
				'available'=>$csfAvailable
			],
			[
				'type'=>'Total',
				'received'=>round($csfReceived,2),
				'utilized'=>round($csfUtilized,2),
				'available'=>round($csfAvailable,2)
			]
		];

		$data['recentInvoices']	=	DB::table('invoice_mpr as im')
									->leftJoin('vendor_tbl as u','u.vendorid','=','im.vendor_id')
									->select('im.voucher_id','im.invoice_number','im.invoice_date','im.net_invoice_value','im.payment_status','im.finance_status','u.companyname as vendor_name')
									->orderByDesc('im.voucher_id')
									->limit(5)
									->get();


		$data['recentPayments']	=	DB::table('finance_vendor_payment as vp')
									->leftJoin('vendor_tbl as u','u.vendorid','=','vp.vendor_id')
									->select('vp.*','u.companyname as vendor_name')
									->orderByDesc('vp.vendor_payment_id')
									->limit(5)
									->get();

		$data['csfFundPosition'] = [
			'received'=>$csfReceived,
			'utilized'=>$csfUtilized,
			'available'=>$csfAvailable
		];
		
		$data['vendorPaymentChart']	=	$this->financeService->getVendorPaymentChartData();

		return view('finance.dashboard',compact('data'));
	}	


	public function financePaymentCycle(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','paymentcycle');

		$currentYear	= 	date('Y');
		$currentMonth 	=	date('n');
		
		$currentFinancialYear = $currentMonth >= 4 ? $currentYear.'-'.substr($currentYear + 1,-2) : ($currentYear - 1).'-'.substr($currentYear,-2);

		$financialYears = collect();

		$openingFinancialYears = DB::table('finance_opening_balance')->whereNotNull('financial_year')->distinct()->pluck('financial_year');

		$receiptFinancialYears = DB::table('finance_department_payment')->whereNotNull('financial_year')->distinct()->pluck('financial_year');

		$invoiceFinancialYears = DB::table('invoice_mpr')->whereNotNull('invoice_date')->selectRaw("DISTINCT CONCAT(YEAR(invoice_date) - (MONTH(invoice_date) < 4), '-', RIGHT(YEAR(invoice_date) - (MONTH(invoice_date) < 4) + 1,2)) as financial_year")->pluck('financial_year');

		$financialYears	=	$financialYears->merge($openingFinancialYears)->merge($receiptFinancialYears)->merge($invoiceFinancialYears)->push($currentFinancialYear)->filter()->unique()->sort()->reverse()->values()->toArray();

		$selectedFinancialYear = $request->get('financial_year',$currentFinancialYear);

		if(!preg_match('/^\d{4}-\d{2}$/',$selectedFinancialYear))
		{
			$selectedFinancialYear = $currentFinancialYear;
		}

		$selectedStartYear		= 	(int)substr($selectedFinancialYear,0,4);
		$selectedEndYear 		=	$selectedStartYear + 1;
		$previousFinancialYear	= 	($selectedStartYear - 1).'-'.substr($selectedStartYear,-2);

		$fyStartDate		=	$selectedStartYear.'-04-01';
		$fyEndDate 			= 	$selectedEndYear.'-03-31';
		$previousFyStartDate= 	($selectedStartYear - 1).'-04-01';
		$previousFyEndDate 	= 	$selectedStartYear.'-03-31';

		/*
		|--------------------------------------------------------------------------
		| CURRENT FY OPENING BALANCE
		|--------------------------------------------------------------------------
		| Opening balance for the selected FY comes from the previous FY closing.
		|
		| For the first/available FY, the opening balance stored against that
		| financial year is used.
		|
		| For subsequent FYs:
		|
		| Previous FY Opening
		| + Previous FY Department Receipts
		| - Previous FY Verified Fund Utilization
		| = Current FY Opening
		|
		| Vendor Funding and Service Charge are maintained separately.
		|--------------------------------------------------------------------------
		*/

		$currentOpeningBalances	=	DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening_balance")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening_balance")->where('financial_year',$selectedFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY OPENING BALANCE
		|--------------------------------------------------------------------------
		*/

		$previousOpeningBalances	=	DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening_balance")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening_balance")->where('financial_year',$previousFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY DEPARTMENT RECEIPTS
		|--------------------------------------------------------------------------
		*/

		$previousDepartmentPayments = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as vendor_funding_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as service_charge_received")->where('dp.financial_year',$previousFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY VERIFIED FUND UTILIZATION
		|--------------------------------------------------------------------------
		| Fund utilization is already split in invoice_mpr:
		| vendor_funding_utilized_amount
		| service_charge_utilized_amount
		|
		| Therefore vendor payment is NOT subtracted again.
		|--------------------------------------------------------------------------
		*/

		$previousInvoiceUtilization = DB::table('invoice_mpr as im')->select('im.request_id')->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_utilized_amount,0) ELSE 0 END) as vendor_funding_utilized")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.service_charge_utilized_amount,0) ELSE 0 END) as service_charge_utilized")->whereBetween('im.invoice_date',[$previousFyStartDate,$previousFyEndDate])->groupBy('im.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| CALCULATE PREVIOUS FY CLOSING
		|--------------------------------------------------------------------------
		*/

		$previousClosingBalances = collect();

		$allPreviousRequestIds = $previousOpeningBalances->keys()->merge($previousDepartmentPayments->keys())->merge($previousInvoiceUtilization->keys())->unique();

		foreach($allPreviousRequestIds as $requestId)
		{
			$previousOpening = $previousOpeningBalances->get($requestId);
			$previousReceipt = $previousDepartmentPayments->get($requestId);
			$previousUtilization = $previousInvoiceUtilization->get($requestId);

			$previousVendorFundingOpening = round((float)($previousOpening->vendor_funding_opening_balance ?? 0),2);
			$previousServiceChargeOpening = round((float)($previousOpening->service_charge_opening_balance ?? 0),2);

			$previousVendorFundingReceived = round((float)($previousReceipt->vendor_funding_received ?? 0),2);
			$previousServiceChargeReceived = round((float)($previousReceipt->service_charge_received ?? 0),2);

			$previousVendorFundingUtilized = round((float)($previousUtilization->vendor_funding_utilized ?? 0),2);
			$previousServiceChargeUtilized = round((float)($previousUtilization->service_charge_utilized ?? 0),2);

			$vendorFundingClosing = round($previousVendorFundingOpening + $previousVendorFundingReceived - $previousVendorFundingUtilized,2);
			$serviceChargeClosing = round($previousServiceChargeOpening + $previousServiceChargeReceived - $previousServiceChargeUtilized,2);

			$previousClosingBalances->put($requestId,(object)[
				'vendor_funding_closing' => $vendorFundingClosing,
				'service_charge_closing' => $serviceChargeClosing
			]);
		}

		/*
		|--------------------------------------------------------------------------
		| CURRENT FY DEPARTMENT RECEIPTS
		|--------------------------------------------------------------------------
		*/

		$departmentPayments = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gross_received_amount,0) ELSE 0 END) as consultancy_receipt")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.taxable_amount,0) ELSE 0 END) as consultancy_taxable_amount")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as consultancy_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as consultancy_gst_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.gross_received_amount,0) ELSE 0 END) as admin_receipt")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.taxable_amount,0) ELSE 0 END) as admin_taxable_amount")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as admin_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as admin_gst_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as consultancy_net_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as admin_net_received")->where('dp.financial_year',$selectedFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS REFUNDABLE
		|--------------------------------------------------------------------------
		*/

		$previousRefundable = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as previous_consultancy_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as previous_consultancy_gst_tds")->where('dp.financial_year','<',$selectedFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| CURRENT FY INVOICE DATA
		|--------------------------------------------------------------------------
		*/

		$invoiceData	=	DB::table('invoice_mpr as im')
							->select('im.request_id')
							->selectRaw('SUM(COALESCE(im.net_invoice_value,0)) as invoice_raised')
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_invoice_value,0) ELSE 0 END) as finance_verified")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_net_amount,0) ELSE 0 END) as consultancy_fee")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.admin_charge_total_amount,0) ELSE 0 END) as admin_charge")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_paid_value,0) ELSE 0 END) as paid_to_vendor")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_balance_value,0) ELSE 0 END) as vendor_balance")
							->whereBetween('im.invoice_date',[$fyStartDate,$fyEndDate])
							->groupBy('im.request_id')
							->get()
							->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| EOI / PROJECT LIST
		|--------------------------------------------------------------------------
		*/

		$eois	=	DB::table('eoi_request as e')
					->leftJoin('department_tbl as u','u.userid','=','e.userid')
					->select('e.requestid','e.eoinumber','e.projecttitle','e.categoryid','e.userid','u.isprojectmanager','u.departmentname as department_name','u.shortname as short_name')
					->where('e.iscancelled',0)
					->orderBy('e.requestid','desc')
					->get();

		$report	=	collect();

		foreach($eois as $eoi)
		{
			$currentOpening		=	$currentOpeningBalances->get($eoi->requestid);
			$previousClosing 	= 	$previousClosingBalances->get($eoi->requestid);
			$receipt 			= 	$departmentPayments->get($eoi->requestid);
			$previousRefund 	= 	$previousRefundable->get($eoi->requestid);
			$invoice 			= 	$invoiceData->get($eoi->requestid);

			$vendorFundingOpeningBalance=	$currentOpening ? round((float)$currentOpening->vendor_funding_opening_balance,2) : round((float)($previousClosing->vendor_funding_closing ?? 0),2);

			$serviceChargeOpeningBalance= 	$currentOpening ? round((float)$currentOpening->service_charge_opening_balance,2) : round((float)($previousClosing->service_charge_closing ?? 0),2);

			$consultancyReceipt			=	round((float)($receipt->consultancy_receipt ?? 0),2);
			$consultancyTaxableAmount 	= 	round((float)($receipt->consultancy_taxable_amount ?? 0),2);
			$consultancyTds 			= 	round((float)($receipt->consultancy_tds ?? 0),2);
			$consultancyGstTds 			= 	round((float)($receipt->consultancy_gst_tds ?? 0),2);
			$consultancyNetReceived 	= 	round((float)($receipt->consultancy_net_received ?? 0),2);

			$adminReceipt 				=	round((float)($receipt->admin_receipt ?? 0),2);
			$adminTaxableAmount 		= 	round((float)($receipt->admin_taxable_amount ?? 0),2);
			$adminTds 					= 	round((float)($receipt->admin_tds ?? 0),2);
			$adminGstTds 				= 	round((float)($receipt->admin_gst_tds ?? 0),2);
			$adminNetReceived 			= 	round((float)($receipt->admin_net_received ?? 0),2);

			$vendorFundingTotalAvailable= 	round($vendorFundingOpeningBalance + $consultancyReceipt,2);
			$serviceChargeTotalAvailable= 	round($serviceChargeOpeningBalance + $adminReceipt,2);
			$totalOpeningBalance 		=	round($vendorFundingOpeningBalance + $serviceChargeOpeningBalance,2);
			$totalDepartmentReceipt 	= 	round($consultancyReceipt + $adminReceipt,2);
			$totalFundAvailable 		= 	round($vendorFundingTotalAvailable + $serviceChargeTotalAvailable,2);

			$totalDepartmentTds			= 	round($consultancyTds + $adminTds,2);
			$totalDepartmentGstTds 		= 	round($consultancyGstTds + $adminGstTds,2);
			$totalDepartmentDeductions 	= 	round($totalDepartmentTds + $totalDepartmentGstTds,2);
			$netReceivedByChips 		= 	round($consultancyNetReceived + $adminNetReceived,2);

			$invoiceRaised 				= 	round((float)($invoice->invoice_raised ?? 0),2);
			$financeVerified 			= 	round((float)($invoice->finance_verified ?? 0),2);
			$consultancyFee 			= 	round((float)($invoice->consultancy_fee ?? 0),2);
			$adminCharge 				= 	round((float)($invoice->admin_charge ?? 0),2);
			$paidToVendor 				= 	round((float)($invoice->paid_to_vendor ?? 0),2);
			$vendorBalance 				= 	round((float)($invoice->vendor_balance ?? 0),2);

			$previousConsultancyTds 	= 	round((float)($previousRefund->previous_consultancy_tds ?? 0),2);
			$previousConsultancyGstTds 	= 	round((float)($previousRefund->previous_consultancy_gst_tds ?? 0),2);
			$previousRefundableToVendor = 	round($previousConsultancyTds + $previousConsultancyGstTds,2);

			$currentRefundableToVendor 	= 	round($consultancyTds + $consultancyGstTds,2);
			$totalRefundableToVendor 	= 	round($previousRefundableToVendor + $currentRefundableToVendor,2);

			$report->push((object)[
				'request_id' 						=> 	$eoi->requestid,
				'ispm'								=>	$eoi->isprojectmanager,
				'short_name'						=>	$eoi->short_name,
				'eoinumber' 						=> 	$eoi->eoinumber,
				'projecttitle' 						=> 	$eoi->projecttitle,
				'department_name' 					=> 	$eoi->department_name,

				'vendor_funding_opening_balance' 	=> 	$vendorFundingOpeningBalance,
				'vendor_funding_department_receipt' => 	$consultancyReceipt,
				'vendor_funding_total_available' 	=> 	$vendorFundingTotalAvailable,

				'service_charge_opening_balance' 	=> 	$serviceChargeOpeningBalance,
				'service_charge_department_receipt' => 	$adminReceipt,
				'service_charge_total_available' 	=> 	$serviceChargeTotalAvailable,

				'total_opening_balance' 			=> 	$totalOpeningBalance,
				'total_department_receipt' 			=> 	$totalDepartmentReceipt,
				'total_fund_available' 				=> 	$totalFundAvailable,

				'consultancy_receipt' 				=> 	$consultancyReceipt,
				'consultancy_taxable_amount' 		=> 	$consultancyTaxableAmount,
				'consultancy_tds' 					=> 	$consultancyTds,
				'consultancy_gst_tds' 				=> 	$consultancyGstTds,
				'consultancy_total_deduction' 		=> 	round($consultancyTds + $consultancyGstTds,2),
				'consultancy_net_received' 			=> 	$consultancyNetReceived,

				'admin_receipt' 					=> 	$adminReceipt,
				'admin_taxable_amount' 				=> 	$adminTaxableAmount,
				'admin_tds' 						=> 	$adminTds,
				'admin_gst_tds' 					=> 	$adminGstTds,
				'admin_total_deduction' 			=> 	round($adminTds + $adminGstTds,2),
				'admin_net_received' 				=> 	$adminNetReceived,

				'total_department_tds' 				=> 	$totalDepartmentTds,
				'total_department_gst_tds' 			=> 	$totalDepartmentGstTds,
				'total_department_deductions' 		=> 	$totalDepartmentDeductions,
				'net_received_by_chips' 			=> 	$netReceivedByChips,

				'invoice_raised' 					=> 	$invoiceRaised,
				'finance_verified' 					=> 	$financeVerified,
				'consultancy_fee' 					=> 	$consultancyFee,
				'admin_charge' 						=> 	$adminCharge,
				'paid_to_vendor' 					=> 	$paidToVendor,
				'vendor_balance' 					=> 	$vendorBalance,

				'previous_consultancy_tds' 			=> 	$previousConsultancyTds,
				'previous_consultancy_gst_tds' 		=> 	$previousConsultancyGstTds,
				'previous_refundable_to_vendor' 	=> 	$previousRefundableToVendor,
				'current_refundable_to_vendor' 		=> 	$currentRefundableToVendor,
				'refundable_to_vendor' 				=> 	$totalRefundableToVendor
			]);
		}

		$summary = [
			'opening_balance' 						=> 	round($report->sum('total_opening_balance'),2),
			'vendor_funding_opening_balance' 		=> 	round($report->sum('vendor_funding_opening_balance'),2),
			'service_charge_opening_balance' 		=> 	round($report->sum('service_charge_opening_balance'),2),

			'department_receipt' 					=> 	round($report->sum('total_department_receipt'),2),
			'department_consultancy_receipt' 		=> 	round($report->sum('consultancy_receipt'),2),
			'department_consultancy_taxable_amount' => 	round($report->sum('consultancy_taxable_amount'),2),
			'department_consultancy_tds' 			=> 	round($report->sum('consultancy_tds'),2),
			'department_consultancy_gst_tds' 		=> 	round($report->sum('consultancy_gst_tds'),2),
			'department_consultancy_total_deduction'=> 	round($report->sum('consultancy_total_deduction'),2),
			'department_consultancy_net_received' 	=> 	round($report->sum('consultancy_net_received'),2),

			'department_admin_receipt' 				=> 	round($report->sum('admin_receipt'),2),
			'department_admin_taxable_amount' 		=> 	round($report->sum('admin_taxable_amount'),2),
			'department_admin_tds' 					=> 	round($report->sum('admin_tds'),2),
			'department_admin_gst_tds' 				=> 	round($report->sum('admin_gst_tds'),2),
			'department_admin_total_deduction' 		=> 	round($report->sum('admin_total_deduction'),2),
			'department_admin_net_received' 		=> 	round($report->sum('admin_net_received'),2),

			'vendor_funding_department_receipt' 	=> 	round($report->sum('vendor_funding_department_receipt'),2),
			'vendor_funding_total_available' 		=> 	round($report->sum('vendor_funding_total_available'),2),
			'service_charge_department_receipt' 	=> 	round($report->sum('service_charge_department_receipt'),2),
			'service_charge_total_available' 		=> 	round($report->sum('service_charge_total_available'),2),
			'total_fund_available' 					=> 	round($report->sum('total_fund_available'),2),

			'department_total_tds' 					=> 	round($report->sum('total_department_tds'),2),
			'department_total_gst_tds' 				=> 	round($report->sum('total_department_gst_tds'),2),
			'department_total_deductions' 			=> 	round($report->sum('total_department_deductions'),2),
			'net_received_by_chips' 				=> 	round($report->sum('net_received_by_chips'),2),

			'invoice_raised' 						=> 	round($report->sum('invoice_raised'),2),
			'finance_verified' 						=> 	round($report->sum('finance_verified'),2),
			'consultancy_fee' 						=> 	round($report->sum('consultancy_fee'),2),
			'admin_charge' 							=> 	round($report->sum('admin_charge'),2),
			'paid_to_vendor' 						=> 	round($report->sum('paid_to_vendor'),2),
			'vendor_balance' 						=> 	round($report->sum('vendor_balance'),2),

			'previous_refundable_to_vendor' 		=> 	round($report->sum('previous_refundable_to_vendor'),2),
			'current_refundable_to_vendor' 			=> 	round($report->sum('current_refundable_to_vendor'),2),
			'refundable_to_vendor' 					=> 	round($report->sum('refundable_to_vendor'),2)
		];

		$summary['fund_utilization_percent']	= 	$summary['total_fund_available'] > 0 ? round(($summary['finance_verified'] / $summary['total_fund_available']) * 100,2) : 0;

		$summary['payment_completion_percent'] 	= 	$summary['finance_verified'] > 0 ? round(($summary['paid_to_vendor'] / $summary['finance_verified']) * 100,2) : 0;

		return view('finance.payment_cycle',compact('report','summary','financialYears','selectedFinancialYear'));
	}

	public function projectWisePayment(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','projectwisefinance');
		
		$departments	=	DB::table('department_tbl')
							->select('userid','departmentname','shortname')
							->where('isprojectmanager',0)
							->orderBy('departmentname')
							->get();

		$managers		=	DB::table('department_tbl')
							->select('userid','departmentname','shortname')
							->where('isprojectmanager',1)
							->orderBy('departmentname')
							->get();
		
		$financialYears 		= 	FinanceHelper::financialYears();
		return view('finance.project_wise_payment',compact('departments','managers','financialYears'));
	}
	
	public function getProjectWisePaymentData(Request $request)
	{
		$userId		= 	$request->session()->get('userId');
		$userType	= 	$request->session()->get('userType');
		
		$categoryid		=	$request->input('categoryid');
		$managerid		=	$request->input('managerid');
		$departmentid	=	$request->input('departmentid');
		$pagesearch		=	$request->input('pagesearch');
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$report_format	=	$request->input('report_format');
		$selectedFinancialYear	=	$request->input('financial_year');

		try
		{
		$selectedStartYear = (int)substr($selectedFinancialYear,0,4);
		$selectedEndYear = $selectedStartYear + 1;
		$previousFinancialYear = ($selectedStartYear - 1).'-'.substr($selectedStartYear,-2);

		$fyStartDate = $selectedStartYear.'-04-01';
		$fyEndDate = $selectedEndYear.'-03-31';
		$previousFyStartDate = ($selectedStartYear - 1).'-04-01';
		$previousFyEndDate = $selectedStartYear.'-03-31';
		
		/*
		|--------------------------------------------------------------------------
		| CURRENT FY OPENING BALANCE
		|--------------------------------------------------------------------------
		| Opening balance for the selected FY comes from the previous FY closing.
		|
		| For the first/available FY, the opening balance stored against that
		| financial year is used.
		|
		| For subsequent FYs:
		|
		| Previous FY Opening
		| + Previous FY Department Receipts
		| - Previous FY Verified Fund Utilization
		| = Current FY Opening
		|
		| Vendor Funding and Service Charge are maintained separately.
		|--------------------------------------------------------------------------
		*/

		$currentOpeningBalances = DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening_balance")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening_balance")->where('financial_year',$selectedFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');
		
		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY OPENING BALANCE
		|--------------------------------------------------------------------------
		*/

		$previousOpeningBalances = DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening_balance")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening_balance")->where('financial_year',$previousFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');


		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY DEPARTMENT RECEIPTS
		|--------------------------------------------------------------------------
		*/

		$previousDepartmentPayments = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as vendor_funding_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as service_charge_received")->where('dp.financial_year',$previousFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS FY VERIFIED FUND UTILIZATION
		|--------------------------------------------------------------------------
		| Fund utilization is already split in invoice_mpr:
		| vendor_funding_utilized_amount
		| service_charge_utilized_amount
		|
		| Therefore vendor payment is NOT subtracted again.
		|--------------------------------------------------------------------------
		*/

		$previousInvoiceUtilization = DB::table('invoice_mpr as im')->select('im.request_id')->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_utilized_amount,0) ELSE 0 END) as vendor_funding_utilized")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.service_charge_utilized_amount,0) ELSE 0 END) as service_charge_utilized")->whereBetween('im.invoice_date',[$previousFyStartDate,$previousFyEndDate])->groupBy('im.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| CALCULATE PREVIOUS FY CLOSING
		|--------------------------------------------------------------------------
		*/

		$previousClosingBalances = collect();

		$allPreviousRequestIds = $previousOpeningBalances->keys()->merge($previousDepartmentPayments->keys())->merge($previousInvoiceUtilization->keys())->unique();

		foreach($allPreviousRequestIds as $requestId)
		{
			$previousOpening = $previousOpeningBalances->get($requestId);
			$previousReceipt = $previousDepartmentPayments->get($requestId);
			$previousUtilization = $previousInvoiceUtilization->get($requestId);

			$previousVendorFundingOpening = round((float)($previousOpening->vendor_funding_opening_balance ?? 0),2);
			$previousServiceChargeOpening = round((float)($previousOpening->service_charge_opening_balance ?? 0),2);

			$previousVendorFundingReceived = round((float)($previousReceipt->vendor_funding_received ?? 0),2);
			$previousServiceChargeReceived = round((float)($previousReceipt->service_charge_received ?? 0),2);

			$previousVendorFundingUtilized = round((float)($previousUtilization->vendor_funding_utilized ?? 0),2);
			$previousServiceChargeUtilized = round((float)($previousUtilization->service_charge_utilized ?? 0),2);

			$vendorFundingClosing = round($previousVendorFundingOpening + $previousVendorFundingReceived - $previousVendorFundingUtilized,2);
			$serviceChargeClosing = round($previousServiceChargeOpening + $previousServiceChargeReceived - $previousServiceChargeUtilized,2);

			$previousClosingBalances->put($requestId,(object)[
				'vendor_funding_closing' => $vendorFundingClosing,
				'service_charge_closing' => $serviceChargeClosing
			]);
		}

		/*
		|--------------------------------------------------------------------------
		| CURRENT FY DEPARTMENT RECEIPTS
		|--------------------------------------------------------------------------
		*/

		$departmentPayments = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gross_received_amount,0) ELSE 0 END) as consultancy_receipt")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.taxable_amount,0) ELSE 0 END) as consultancy_taxable_amount")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as consultancy_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as consultancy_gst_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.gross_received_amount,0) ELSE 0 END) as admin_receipt")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.taxable_amount,0) ELSE 0 END) as admin_taxable_amount")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as admin_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as admin_gst_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as consultancy_net_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as admin_net_received")->where('dp.financial_year',$selectedFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

		/*
		|--------------------------------------------------------------------------
		| PREVIOUS REFUNDABLE
		|--------------------------------------------------------------------------
		*/
		
		$previousRefundable = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.tds_amount,0) ELSE 0 END) as previous_consultancy_tds")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.gst_tds_amount,0) ELSE 0 END) as previous_consultancy_gst_tds")->where('dp.financial_year','<',$selectedFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');
		
		/*
		|--------------------------------------------------------------------------
		| CURRENT FY INVOICE DATA
		|--------------------------------------------------------------------------
		*/

		$invoiceData	=	DB::table('invoice_mpr as im')
							->select('im.request_id')
							->selectRaw('SUM(COALESCE(im.net_invoice_value,0)) as invoice_raised')
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_invoice_value,0) ELSE 0 END) as finance_verified")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_net_amount,0) ELSE 0 END) as consultancy_fee")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.admin_charge_total_amount,0) ELSE 0 END) as admin_charge")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_paid_value,0) ELSE 0 END) as paid_to_vendor")
							->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_balance_value,0) ELSE 0 END) as vendor_balance")
							->whereBetween('im.invoice_date',[$fyStartDate,$fyEndDate])
							->groupBy('im.request_id')
							->get()
							->keyBy('request_id');
		

		$eois	=	DB::table('eoi_request as e')
					->leftJoin('department_tbl as dept','dept.userid','=','e.userid')
					->select('e.requestid','e.eoinumber','e.projecttitle','e.categoryid','e.userid','dept.isprojectmanager','dept.departmentname as department_name','dept.shortname as short_name')
					->where('e.iscancelled',0)
					->when($in_house==1, function ($query) {
						$query->join('users_tbl as u', function ($join) {
							$join->on('u.userid','=','e.userid')
								->where('u.ispm','=',1)
								->where('e.categoryid',2);
						});
					})
					->when($categoryid!=0,function($query) use ($categoryid){
						return $query->where('e.categoryid','=',$categoryid);
					})		
					->when($managerid!=0,function($query) use ($managerid){
						return $query->where('e.userid','=',$managerid);
					})		
					->when($departmentid!=0,function($query) use ($departmentid){
						return $query->where('e.userid','=',$departmentid);
					})		
					->when($pagesearch != '', function($query) use ($pagesearch) {
						return $query->where(function($q) use ($pagesearch) {
							$q->where('e.eoinumber','like', '%' . $pagesearch . '%')
								->orWhere('e.projecttitle', 'like', '%' . $pagesearch . '%');
						})->where('e.iscancelled', 0);
					})
					->orderBy('e.requestid','desc')
					->get();

		$report	=	collect();

		foreach($eois as $eoi)
		{
			$currentOpening		=	$currentOpeningBalances->get($eoi->requestid);
			$previousClosing 	= 	$previousClosingBalances->get($eoi->requestid);
			$receipt 			= 	$departmentPayments->get($eoi->requestid);
			$previousRefund 	= 	$previousRefundable->get($eoi->requestid);
			$invoice 			= 	$invoiceData->get($eoi->requestid);

			$vendorFundingOpeningBalance=	$currentOpening ? round((float)$currentOpening->vendor_funding_opening_balance,2) : round((float)($previousClosing->vendor_funding_closing ?? 0),2);

			$serviceChargeOpeningBalance= 	$currentOpening ? round((float)$currentOpening->service_charge_opening_balance,2) : round((float)($previousClosing->service_charge_closing ?? 0),2);

			$consultancyReceipt			=	round((float)($receipt->consultancy_receipt ?? 0),2);
			$consultancyTaxableAmount 	= 	round((float)($receipt->consultancy_taxable_amount ?? 0),2);
			$consultancyTds 			= 	round((float)($receipt->consultancy_tds ?? 0),2);
			$consultancyGstTds 			= 	round((float)($receipt->consultancy_gst_tds ?? 0),2);
			$consultancyNetReceived 	= 	round((float)($receipt->consultancy_net_received ?? 0),2);

			$adminReceipt 				=	round((float)($receipt->admin_receipt ?? 0),2);
			$adminTaxableAmount 		= 	round((float)($receipt->admin_taxable_amount ?? 0),2);
			$adminTds 					= 	round((float)($receipt->admin_tds ?? 0),2);
			$adminGstTds 				= 	round((float)($receipt->admin_gst_tds ?? 0),2);
			$adminNetReceived 			= 	round((float)($receipt->admin_net_received ?? 0),2);

			$vendorFundingTotalAvailable= 	round($vendorFundingOpeningBalance + $consultancyReceipt,2);
			$serviceChargeTotalAvailable= 	round($serviceChargeOpeningBalance + $adminReceipt,2);
			$totalOpeningBalance 		=	round($vendorFundingOpeningBalance + $serviceChargeOpeningBalance,2);
			$totalDepartmentReceipt 	= 	round($consultancyReceipt + $adminReceipt,2);
			$totalFundAvailable 		= 	round($vendorFundingTotalAvailable + $serviceChargeTotalAvailable,2);

			$totalDepartmentTds			= 	round($consultancyTds + $adminTds,2);
			$totalDepartmentGstTds 		= 	round($consultancyGstTds + $adminGstTds,2);
			$totalDepartmentDeductions 	= 	round($totalDepartmentTds + $totalDepartmentGstTds,2);
			$netReceivedByChips 		= 	round($consultancyNetReceived + $adminNetReceived,2);

			$invoiceRaised 				= 	round((float)($invoice->invoice_raised ?? 0),2);
			$financeVerified 			= 	round((float)($invoice->finance_verified ?? 0),2);
			$consultancyFee 			= 	round((float)($invoice->consultancy_fee ?? 0),2);
			$adminCharge 				= 	round((float)($invoice->admin_charge ?? 0),2);
			$paidToVendor 				= 	round((float)($invoice->paid_to_vendor ?? 0),2);
			$vendorBalance 				= 	round((float)($invoice->vendor_balance ?? 0),2);

			$previousConsultancyTds 	= 	round((float)($previousRefund->previous_consultancy_tds ?? 0),2);
			$previousConsultancyGstTds 	= 	round((float)($previousRefund->previous_consultancy_gst_tds ?? 0),2);
			$previousRefundableToVendor = 	round($previousConsultancyTds + $previousConsultancyGstTds,2);

			$currentRefundableToVendor 	= 	round($consultancyTds + $consultancyGstTds,2);
			$totalRefundableToVendor 	= 	round($previousRefundableToVendor + $currentRefundableToVendor,2);

			$report->push((object)[
				'request_id' 						=> 	$eoi->requestid,
				'ispm'								=>	$eoi->isprojectmanager,
				'short_name'						=>	$eoi->short_name,
				'eoinumber' 						=> 	$eoi->eoinumber,
				'projecttitle' 						=> 	$eoi->projecttitle,
				'department_name' 					=> 	$eoi->department_name,

				'vendor_funding_opening_balance' 	=> 	$vendorFundingOpeningBalance,
				'vendor_funding_department_receipt' => 	$consultancyReceipt,
				'vendor_funding_total_available' 	=> 	$vendorFundingTotalAvailable,

				'service_charge_opening_balance' 	=> 	$serviceChargeOpeningBalance,
				'service_charge_department_receipt' => 	$adminReceipt,
				'service_charge_total_available' 	=> 	$serviceChargeTotalAvailable,

				'total_opening_balance' 			=> 	$totalOpeningBalance,
				'total_department_receipt' 			=> 	$totalDepartmentReceipt,
				'total_fund_available' 				=> 	$totalFundAvailable,

				'consultancy_receipt' 				=> 	$consultancyReceipt,
				'consultancy_taxable_amount' 		=> 	$consultancyTaxableAmount,
				'consultancy_tds' 					=> 	$consultancyTds,
				'consultancy_gst_tds' 				=> 	$consultancyGstTds,
				'consultancy_total_deduction' 		=> 	round($consultancyTds + $consultancyGstTds,2),
				'consultancy_net_received' 			=> 	$consultancyNetReceived,

				'admin_receipt' 					=> 	$adminReceipt,
				'admin_taxable_amount' 				=> 	$adminTaxableAmount,
				'admin_tds' 						=> 	$adminTds,
				'admin_gst_tds' 					=> 	$adminGstTds,
				'admin_total_deduction' 			=> 	round($adminTds + $adminGstTds,2),
				'admin_net_received' 				=> 	$adminNetReceived,

				'total_department_tds' 				=> 	$totalDepartmentTds,
				'total_department_gst_tds' 			=> 	$totalDepartmentGstTds,
				'total_department_deductions' 		=> 	$totalDepartmentDeductions,
				'net_received_by_chips' 			=> 	$netReceivedByChips,

				'invoice_raised' 					=> 	$invoiceRaised,
				'finance_verified' 					=> 	$financeVerified,
				'consultancy_fee' 					=> 	$consultancyFee,
				'admin_charge' 						=> 	$adminCharge,
				'paid_to_vendor' 					=> 	$paidToVendor,
				'vendor_balance' 					=> 	$vendorBalance,

				'previous_consultancy_tds' 			=> 	$previousConsultancyTds,
				'previous_consultancy_gst_tds' 		=> 	$previousConsultancyGstTds,
				'previous_refundable_to_vendor' 	=> 	$previousRefundableToVendor,
				'current_refundable_to_vendor' 		=> 	$currentRefundableToVendor,
				'refundable_to_vendor' 				=> 	$totalRefundableToVendor
			]);
		}

		$summary = [
			'opening_balance' 						=> 	round($report->sum('total_opening_balance'),2),
			'vendor_funding_opening_balance' 		=> 	round($report->sum('vendor_funding_opening_balance'),2),
			'service_charge_opening_balance' 		=> 	round($report->sum('service_charge_opening_balance'),2),

			'department_receipt' 					=> 	round($report->sum('total_department_receipt'),2),
			'department_consultancy_receipt' 		=> 	round($report->sum('consultancy_receipt'),2),
			'department_consultancy_taxable_amount' => 	round($report->sum('consultancy_taxable_amount'),2),
			'department_consultancy_tds' 			=> 	round($report->sum('consultancy_tds'),2),
			'department_consultancy_gst_tds' 		=> 	round($report->sum('consultancy_gst_tds'),2),
			'department_consultancy_total_deduction'=> 	round($report->sum('consultancy_total_deduction'),2),
			'department_consultancy_net_received' 	=> 	round($report->sum('consultancy_net_received'),2),

			'department_admin_receipt' 				=> 	round($report->sum('admin_receipt'),2),
			'department_admin_taxable_amount' 		=> 	round($report->sum('admin_taxable_amount'),2),
			'department_admin_tds' 					=> 	round($report->sum('admin_tds'),2),
			'department_admin_gst_tds' 				=> 	round($report->sum('admin_gst_tds'),2),
			'department_admin_total_deduction' 		=> 	round($report->sum('admin_total_deduction'),2),
			'department_admin_net_received' 		=> 	round($report->sum('admin_net_received'),2),

			'vendor_funding_department_receipt' 	=> 	round($report->sum('vendor_funding_department_receipt'),2),
			'vendor_funding_total_available' 		=> 	round($report->sum('vendor_funding_total_available'),2),
			'service_charge_department_receipt' 	=> 	round($report->sum('service_charge_department_receipt'),2),
			'service_charge_total_available' 		=> 	round($report->sum('service_charge_total_available'),2),
			'total_fund_available' 					=> 	round($report->sum('total_fund_available'),2),

			'department_total_tds' 					=> 	round($report->sum('total_department_tds'),2),
			'department_total_gst_tds' 				=> 	round($report->sum('total_department_gst_tds'),2),
			'department_total_deductions' 			=> 	round($report->sum('total_department_deductions'),2),
			'net_received_by_chips' 				=> 	round($report->sum('net_received_by_chips'),2),

			'invoice_raised' 						=> 	round($report->sum('invoice_raised'),2),
			'finance_verified' 						=> 	round($report->sum('finance_verified'),2),
			'consultancy_fee' 						=> 	round($report->sum('consultancy_fee'),2),
			'admin_charge' 							=> 	round($report->sum('admin_charge'),2),
			'paid_to_vendor' 						=> 	round($report->sum('paid_to_vendor'),2),
			'vendor_balance' 						=> 	round($report->sum('vendor_balance'),2),

			'previous_refundable_to_vendor' 		=> 	round($report->sum('previous_refundable_to_vendor'),2),
			'current_refundable_to_vendor' 			=> 	round($report->sum('current_refundable_to_vendor'),2),
			'refundable_to_vendor' 					=> 	round($report->sum('refundable_to_vendor'),2)
		];

		$summary['fund_utilization_percent']	= 	$summary['total_fund_available'] > 0 ? round(($summary['finance_verified'] / $summary['total_fund_available']) * 100,2) : 0;

		$summary['payment_completion_percent'] 	= 	$summary['finance_verified'] > 0 ? round(($summary['paid_to_vendor'] / $summary['finance_verified']) * 100,2) : 0;
		
		return view('finance.ajaxpages.projectWisePaymentData',compact('report','summary','report_format','categoryid'));		
		}
		catch(Exception $e)
		{
			Log::error("Error ".$e->getMessage());
		}
	}
}
?>