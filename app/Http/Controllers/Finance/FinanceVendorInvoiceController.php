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

class FinanceVendorInvoiceController extends Controller
{
	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financevendorinvoiceindex');

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

		return view('finance.vendorinvoice.index',compact('departments','managers','projects','token','vendors'));
	}

	public function getVendorInvoiceList(Request $request)
	{
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	=	$request->input('page',1);
		$projectid		=	$request->input('projectid') ?? NULL;
		$departmentid	=	$request->input('departmentid') ?? NULL;
		$managerid		=	$request->input('managerid') ?? NULL;
		$vendorid		=	$request->input('vendor_id') ?? NULL;
		$financeStatus	=	$request->input('financeStatus') ?? NULL;
		$invoiceStatus	=	$request->input('invoiceStatus') ?? NULL;
		$data	=	DB::table('invoice_mpr as vi')
					->leftJoin('vendor_tbl as v','v.vendorid','=','vi.vendor_id')
					->leftJoin('eoi_work_order as wo','wo.orderid','=','vi.order_id')
					->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
					->select(
						'vi.voucher_id',
						'vi.invoice_number',
						'vi.invoice_date',
						'vi.order_id',
						'vi.request_id',
						'vi.mpr_ids',
						'vi.net_invoice_value',
						'vi.finance_status',
						'vi.payment_status',
						'vi.net_balance_value',
						'vi.vendor_id',
						'v.companyname',
						'v.categoryid',
						'wo.department_id',
						'e.eoinumber',
						'wo.ordernumber'
					)
					->selectSub(function($q){
						$q->from('finance_vendor_payment as vp')
							->selectRaw('IFNULL(SUM(vp.net_paid_amount),0)')
							->whereColumn('vp.voucher_id','vi.voucher_id');
					},'total_paid')
					->selectRaw('(vi.net_invoice_value - IFNULL((SELECT SUM(vp.gross_amount) FROM finance_vendor_payment as vp WHERE vp.voucher_id = vi.voucher_id),0)) as calculated_balance')
					->when($projectid !=NULL,function($query) use ($projectid){
						return $query->where('vi.finance_project_id',$projectid);
					})
					->when($departmentid !=NULL,function($query) use ($departmentid){
						return $query->where('wo.department_id',$departmentid);
					})
					->when($managerid !=NULL,function($query) use ($managerid){
						return $query->where('wo.department_id',$managerid);
					})
					->when($vendorid !=NULL,function($query) use ($vendorid){
						return $query->where('vi.vendor_id',$vendorid);
					})
					->when($financeStatus !='',function($query) use ($financeStatus){
						return $query->where('vi.finance_status',$financeStatus);
					})
					->when($invoiceStatus !='',function($query) use ($invoiceStatus){
						return $query->where('vi.invoice_status',$invoiceStatus);
					})
					->when($pagesearch != '',function($query) use ($pagesearch){
						return $query->where(function($q) use ($pagesearch){
							$q->where('vi.invoice_number','like','%'.$pagesearch.'%')
							  ->orWhere('vi.voucher_number','like','%'.$pagesearch.'%')
							  ->orWhere('v.companyname','like','%'.$pagesearch.'%')
							  ->orWhere('e.eoinumber','like','%'.$pagesearch.'%')
							  ->orWhere('e.projecttitle','like','%'.$pagesearch.'%')
							  ->orWhere('vi.order_id','like','%'.$pagesearch.'%');
						});
					})
					->orderBy('vi.voucher_id','desc')
					->paginate($pagesize,['*'],'page',$currentPage);

		return view('finance.vendorinvoice.ajaxpages.vendorinvoiceTable',['data'=>$data]);
	}
	
	/*
	public function view($id)
	{
		try
		{
			$voucherId = Crypt::decrypt($id);

			$invoice = DB::table('invoice_mpr as vi')
							->leftJoin('vendor_tbl as v','v.vendorid','=','vi.vendor_id')
							->leftJoin('eoi_work_order as wo','wo.orderid','=','vi.order_id')
							->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
							->leftJoin('users_tbl as u','u.userid','=','vi.user_id')
							->leftJoin('users_tbl as su','su.userid','=','vi.finance_verified_by')
							->leftJoin('users_tbl as req','req.userid','=','e.userid')
							->select('vi.*','v.companyname','v.gstnumber as vendor_gstin','v.pannumber as vendor_pan','v.officialemail as vendor_email','v.contactperson','v.categoryid','v.tierid','u.name as submitted_by','req.ispm','e.eoinumber','wo.ordernumber','su.name as finance_verified_by_name')
							->where('vi.voucher_id',$voucherId)
							->first();

			if(!$invoice)
			{
				return redirect()->route('finance.vendorinvoice.index')->with('error','Vendor Invoice not found.');
			}

			$totalPaid = DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount');

			$currentBalance = round((float)$invoice->net_invoice_value - (float)$totalPaid,2);

			if($currentBalance < 0)
			{
				$currentBalance = 0;
			}

			$payments = DB::table('finance_vendor_payment as vp')
							->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','vp.payment_mode_id')
							->select('vp.*','pm.payment_mode')
							->where('vp.voucher_id',$invoice->voucher_id)
							->orderBy('vp.vendor_payment_id','desc')
							->get();

			$caseType = null;

			if((int)$invoice->categoryid == 1)
			{
				$caseType = 'AWD';
			}
			else if((int)$invoice->categoryid == 2 && $invoice->ispm==0)
			{
				$caseType = 'CSF';
			}
			else if((int)$invoice->categoryid == 2 && $invoice->ispm==1)
			{
				$caseType = 'AWD';
			}


			$departmentGrossReceived = 0;
			$departmentTaxableAmount = 0;
			$departmentGstAmount = 0;
			$departmentTdsAmount = 0;
			$departmentGstTdsAmount = 0;
			$departmentOtherDeduction = 0;
			$departmentReceived = 0;
			$utilizedAmount = 0;

			$currentInvoiceAmount = round((float)$invoice->net_invoice_value,2);

			$availableBalance 	= 	0;
			$shortfall 			= 	0;
			$hasCsfShortfall 	= 	false;

			$invoiceBasicAmount = 	round((float)$invoice->taxable_amount,2);
			$invoiceGrossAmount = 	round((float)$invoice->net_invoice_value,2);
			$invoiceGstAmount 	= 	round($invoiceGrossAmount - $invoiceBasicAmount);


			$vendorFundingTdsTaxId 		= 	null;
			$vendorFundingGstTdsTaxId 	= 	null;
			$vendorFundingTdsRate 		= 	0;
			$vendorFundingGstTdsRate 	= 	0;

			$serviceChargeTdsTaxId 		= 	null;
			$serviceChargeGstTdsTaxId 	= 	null;
			$serviceChargeTdsRate 		= 	0;
			$serviceChargeGstTdsRate 	= 	0;

			$vendorTdsTaxId 	= 	null;
			$vendorGstTdsTaxId 	= 	null;
			$vendorTdsRate 		= 	0;
			$vendorGstTdsRate 	= 	0;

			$departmentGstRate = 0;

			$fullDepartmentTdsAmount = 0;
			$fullDepartmentGstTdsAmount = 0;
			$fullVendorNetReceived = 0;

			$adminChargePercent = round((float)$invoice->admin_charge_percent,2);
			$fullAdminChargeAmount = 0;
			$fullAvailableFund = 0;

			$fundAdminBasic = 0;
			$fundAdminGst = 0;
			$fundAdminTotal = 0;

			$fundInvoiceTds = 0;
			$fundInvoiceGstTds = 0;
			$fundAdminTds = 0;
			$fundAdminGstTds = 0;

			$fundInvoiceNet = 0;
			$fundAdminNet = 0;
			$fundTotalInvoice = 0;

			$vendorInvoiceAvailableFund = 0;
			$adminChargeAmount = 0;
			$vendorInvoiceApprovalAmount = 0;

			$vendorTdsAmount = 0;
			$vendorGstTdsAmount = 0;
			$netPaymentToVendor = 0;

			$vendorFundingDemandNotes = collect();
			$serviceChargeDemandNotes = collect();

			$departmentFundingReceipt 		= null;
			$departmentServiceChargeReceipt = null;
			$balanceBeforeCurrentInvoice	=	0;

			$openingBalance	=	DB::table('finance_opening_balance')
								->where('request_id',$invoice->request_id)
								->where('balance_type','VENDOR_FUNDING')
								->sum('opening_balance') ?? 0;

			if($caseType == 'CSF')
			{

				$vendorFundingDemandNotesQuery = DB::table('finance_demand_note')
													->where('demand_note_type','VENDOR_FUNDING')
													->where('status','Active');

				if(!empty($invoice->request_id))
				{
					$vendorFundingDemandNotesQuery->where('request_id',$invoice->request_id);
				}
				else
				{
					$vendorFundingDemandNotesQuery->where('order_id',$invoice->order_id);
				}

				$vendorFundingDemandNotes = $vendorFundingDemandNotesQuery->orderBy('demand_note_id','asc')->get();

				$serviceChargeDemandNotesQuery = DB::table('finance_demand_note')
													->where('demand_note_type','SERVICE_CHARGE')
													->where('status','Active');

				if(!empty($invoice->request_id))
				{
					$serviceChargeDemandNotesQuery->where('request_id',$invoice->request_id);
				}
				else
				{
					$serviceChargeDemandNotesQuery->where('order_id',$invoice->order_id);
				}

				$serviceChargeDemandNotes = $serviceChargeDemandNotesQuery->orderBy('demand_note_id','asc')->get();

				if($vendorFundingDemandNotes->count() > 0)
				{
					$vendorFundingDemandNoteIds = $vendorFundingDemandNotes->pluck('demand_note_id')->toArray();

					$departmentFundingReceipt = DB::table('finance_department_payment as dp')
													->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
													->where('dn.demand_note_type','VENDOR_FUNDING')
													->where('dn.status','Active')
													->where('dp.status','!=','Cancelled')
													->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
													->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
													->orderBy('dp.department_payment_id','asc')
													->first();

					if($departmentFundingReceipt)
					{

						$departmentGrossReceived = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.gross_received_amount');

						$departmentTaxableAmount = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.taxable_amount');

						$departmentGstAmount = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.gst_amount');

						$departmentTdsAmount = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.tds_amount');

						$departmentGstTdsAmount = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.gst_tds_amount');

						$departmentOtherDeduction = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.other_deduction');

						$departmentReceived = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','VENDOR_FUNDING')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
														->sum('dp.net_received_amount');

						$departmentGrossReceived = round((float)$departmentGrossReceived,2);
						$departmentTaxableAmount = round((float)$departmentTaxableAmount,2);
						$departmentGstAmount = round((float)$departmentGstAmount,2);
						$departmentTdsAmount = round((float)$departmentTdsAmount,2);
						$departmentGstTdsAmount = round((float)$departmentGstTdsAmount,2);
						$departmentOtherDeduction = round((float)$departmentOtherDeduction,2);


						
						$departmentReceived = round((float)$departmentReceived,2)+$openingBalance;

						$receiptTaxableAmount = round((float)$departmentFundingReceipt->taxable_amount,2);
						$receiptGstAmount = round((float)$departmentFundingReceipt->gst_amount,2);

						if($receiptTaxableAmount > 0 && $receiptGstAmount > 0)
						{
							$departmentGstRate = round(($receiptGstAmount / $receiptTaxableAmount) * 100,2);
						}

						if(!empty($departmentFundingReceipt->tds_tax_id))
						{
							$departmentTdsTax = DB::table('finance_tax_master')
													->where('tax_id',$departmentFundingReceipt->tds_tax_id)
													->where('tax_type','TDS')
													->where('is_active',1)
													->first();

							if($departmentTdsTax)
							{
								$vendorFundingTdsTaxId = $departmentFundingReceipt->tds_tax_id;
								$vendorFundingTdsRate = round((float)$departmentTdsTax->rate,2);
							}
						}

						if(!empty($departmentFundingReceipt->gst_tds_tax_id))
						{
							$departmentGstTdsTax = DB::table('finance_tax_master')
													->where('tax_id',$departmentFundingReceipt->gst_tds_tax_id)
													->where('tax_type','GST_TDS')
													->where('is_active',1)
													->first();

							if($departmentGstTdsTax)
							{
								$vendorFundingGstTdsTaxId = $departmentFundingReceipt->gst_tds_tax_id;
								$vendorFundingGstTdsRate = round((float)$departmentGstTdsTax->rate,2);
							}
						}
					}
					else
					{
						$departmentReceived = round((float)$openingBalance,2);
					}
				}
				else
				{
					$departmentReceived = round((float)$openingBalance,2);
				}

				if($serviceChargeDemandNotes->count() > 0)
				{
					$serviceChargeDemandNoteIds = $serviceChargeDemandNotes->pluck('demand_note_id')->toArray();

					$departmentServiceChargeReceipt = DB::table('finance_department_payment as dp')
														->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
														->where('dn.demand_note_type','SERVICE_CHARGE')
														->where('dn.status','Active')
														->where('dp.status','!=','Cancelled')
														->whereIn('dp.demand_note_id',$serviceChargeDemandNoteIds)
														->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
														->orderBy('dp.department_payment_id','asc')
														->first();

					if($departmentServiceChargeReceipt)
					{

						if(!empty($departmentServiceChargeReceipt->tds_tax_id))
						{
							$serviceChargeTdsTax	=	DB::table('finance_tax_master')
														->where('tax_id',$departmentServiceChargeReceipt->tds_tax_id)
														->where('tax_type','TDS')
														->where('is_active',1)
														->first();

							if($serviceChargeTdsTax)
							{
								$serviceChargeTdsTaxId = $departmentServiceChargeReceipt->tds_tax_id;
								$serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
							}
						}

						if(!empty($departmentServiceChargeReceipt->gst_tds_tax_id))
						{
							$serviceChargeGstTdsTax = DB::table('finance_tax_master')
															->where('tax_id',$departmentServiceChargeReceipt->gst_tds_tax_id)
															->where('tax_type','GST_TDS')
															->where('is_active',1)
															->first();

							if($serviceChargeGstTdsTax)
							{
								$serviceChargeGstTdsTaxId = $departmentServiceChargeReceipt->gst_tds_tax_id;
								$serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
							}
						}
					}
				}

				$fullDepartmentTdsAmount = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);

				$fullDepartmentGstTdsAmount = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

				$fullVendorNetReceived = round($invoiceGrossAmount - $fullDepartmentTdsAmount - $fullDepartmentGstTdsAmount,2);

				$fullAdminChargeAmount = round($fullVendorNetReceived * $adminChargePercent / 100,2);

				$fullAvailableFund = round($fullVendorNetReceived + $fullAdminChargeAmount,2);

				$fundAdminBasic = round($invoiceBasicAmount * $adminChargePercent / 100,2);

				$fundAdminGst = round($invoiceGstAmount * $adminChargePercent / 100,2);

				$fundAdminTotal = round($fundAdminBasic + $fundAdminGst,2);

				$fundInvoiceTds = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);

				$fundInvoiceGstTds = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

				$fundAdminTds = round($fundAdminBasic * $serviceChargeTdsRate / 100,2);

				$fundAdminGstTds = round($fundAdminBasic * $serviceChargeGstTdsRate / 100,2);

				$fundInvoiceNet = round($invoiceGrossAmount - $fundInvoiceTds - $fundInvoiceGstTds);

				$fundAdminNet = round($fundAdminTotal - $fundAdminTds - $fundAdminGstTds);

				$fundTotalInvoice = round($fundInvoiceNet + $fundAdminNet);

				$vendorInvoiceAvailableFund = round($fullAvailableFund);

				$adminChargeAmount = round($fullAdminChargeAmount);

				$vendorInvoiceApprovalAmount = round($vendorInvoiceAvailableFund - $adminChargeAmount);

				if($vendorInvoiceApprovalAmount < 0)
				{
					$vendorInvoiceApprovalAmount = 0;
				}

				$previousInvoicesQuery = DB::table('invoice_mpr')
										->where('finance_status','Verified')
										->where('voucher_id','!=',$invoice->voucher_id);

				if(!empty($invoice->request_id))
				{
					$previousInvoicesQuery->where('request_id',$invoice->request_id);
				}
				else
				{
					$previousInvoicesQuery->where('order_id',$invoice->order_id);
				}

				$previousInvoices = $previousInvoicesQuery->select('voucher_id','fund_utilized_amount')->get();

				foreach($previousInvoices as $previousInvoice)
				{
					$utilizedAmount += round((float)$previousInvoice->fund_utilized_amount,2);
				}

				$utilizedAmount = round($utilizedAmount,2);
								
				$balanceBeforeCurrentInvoice= 	round($departmentReceived - $utilizedAmount,2);

				$currentInvoiceAmount = round($invoiceGrossAmount,2);

				$availableBalance = round($balanceBeforeCurrentInvoice - $vendorInvoiceAvailableFund,2);

				if($balanceBeforeCurrentInvoice < $vendorInvoiceAvailableFund)
				{
					$shortfall = round($vendorInvoiceAvailableFund - $balanceBeforeCurrentInvoice,2);
					$hasCsfShortfall = true;
				}
			}
			else
			{
				$shortfall = 0;
				$hasCsfShortfall = false;
			}

			$ledger = DB::table('finance_ledger')
						->where('vendor_invoice_id',$invoice->voucher_id)
						->orderBy('transaction_date','asc')
						->orderBy('ledger_id','asc')
						->get();

			$mprIds = [];

			if(!empty($invoice->mpr_ids))
			{
				$mprIds = array_values(array_filter(array_map('intval',explode(',',$invoice->mpr_ids))));
			}

			$mprs = collect();

			if(count($mprIds) > 0)
			{
				$mprs = DB::table('mpr_tbl as mpr')
							->select('mpr.*','b.name')
							->leftJoin('users_tbl as b','b.userid','=','mpr.verified_by')
							->whereIn('mpr.mpr_id',$mprIds)
							->orderBy('mpr.submission_date','asc')
							->orderBy('mpr.mpr_id','asc')
							->get();

				foreach($mprs as $mpr)
				{
					$mpr->resources = DB::table('mpr_attendance_summary as mas')
										->select('mas.*','erd.name')
										->leftJoin('eoi_resource_deployment as erd','erd.deploymentid','=','mas.deployment_id')
										->where('mas.mpr_id',$mpr->mpr_id)
										->orderBy('mas.deployment_id','asc')
										->get();
				}
			}

			$tdsTaxes = DB::table('finance_tax_master')->where('tax_type','TDS')->where('is_active',1)->orderBy('rate','asc')->get();

			$gstTdsTaxes = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->where('is_active',1)->orderBy('rate','asc')->get();

			return view('finance.vendorinvoice.view',compact(
					'invoice',
					'totalPaid',
					'currentBalance',
					'payments',
					'caseType',
					'vendorFundingDemandNotes',
					'serviceChargeDemandNotes',
					'departmentFundingReceipt',
					'departmentServiceChargeReceipt',
					'departmentGrossReceived',
					'departmentTaxableAmount',
					'departmentGstAmount',
					'departmentTdsAmount',
					'departmentGstTdsAmount',
					'departmentOtherDeduction',
					'departmentReceived',
					'departmentGstRate',
					'vendorFundingTdsTaxId',
					'vendorFundingGstTdsTaxId',
					'vendorFundingTdsRate',
					'vendorFundingGstTdsRate',
					'serviceChargeTdsTaxId',
					'serviceChargeGstTdsTaxId',
					'serviceChargeTdsRate',
					'serviceChargeGstTdsRate',
					'vendorTdsTaxId',
					'vendorGstTdsTaxId',
					'vendorTdsRate',
					'vendorGstTdsRate',
					'fullDepartmentTdsAmount',
					'fullDepartmentGstTdsAmount',
					'fullVendorNetReceived',
					'invoiceGrossAmount',
					'invoiceBasicAmount',
					'invoiceGstAmount',
					'adminChargePercent',
					'fullAdminChargeAmount',
					'fullAvailableFund',
					'fundAdminBasic',
					'fundAdminGst',
					'fundAdminTotal',
					'fundInvoiceTds',
					'fundInvoiceGstTds',
					'fundAdminTds',
					'fundAdminGstTds',
					'fundInvoiceNet',
					'fundAdminNet',
					'fundTotalInvoice',
					'vendorInvoiceAvailableFund',
					'adminChargeAmount',
					'vendorInvoiceApprovalAmount',
					'utilizedAmount',
					'currentInvoiceAmount',
					'balanceBeforeCurrentInvoice',
					'availableBalance',
					'shortfall',
					'hasCsfShortfall',
					'ledger',
					'mprIds',
					'mprs',
					'tdsTaxes',
					'gstTdsTaxes'
			));
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.vendorinvoice.index')->with('error',$e->getMessage());
		}
	}
	*/

public function view($id)
{
	try
	{
		$voucherId = Crypt::decrypt($id);

		$invoice = DB::table('invoice_mpr as vi')
						->leftJoin('vendor_tbl as v','v.vendorid','=','vi.vendor_id')
						->leftJoin('eoi_work_order as wo','wo.orderid','=','vi.order_id')
						->leftJoin('eoi_request as e','e.requestid','=','wo.requestid')
						->leftJoin('users_tbl as u','u.userid','=','vi.user_id')
						->leftJoin('users_tbl as su','su.userid','=','vi.finance_verified_by')
						->leftJoin('users_tbl as req','req.userid','=','e.userid')
						->select('vi.*','v.companyname','v.gstnumber as vendor_gstin','v.pannumber as vendor_pan','v.officialemail as vendor_email','v.contactperson','v.categoryid','v.tierid','u.name as submitted_by','req.ispm','e.eoinumber','wo.ordernumber','su.name as finance_verified_by_name')
						->where('vi.voucher_id',$voucherId)
						->first();

		if(!$invoice)
		{
			return redirect()->route('finance.vendorinvoice.index')->with('error','Vendor Invoice not found.');
		}

		/*
		 * Existing Vendor Payment Summary
		 */

		$totalPaid = DB::table('finance_vendor_payment')->where('voucher_id',$invoice->voucher_id)->sum('gross_amount');

		$currentBalance = round((float)$invoice->net_invoice_value - (float)$totalPaid,2);

		if($currentBalance < 0)
		{
			$currentBalance = 0;
		}

		$payments = DB::table('finance_vendor_payment as vp')
						->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','vp.payment_mode_id')
						->select('vp.*','pm.payment_mode')
						->where('vp.voucher_id',$invoice->voucher_id)
						->orderBy('vp.vendor_payment_id','desc')
						->get();

		/*
		 * Case Type
		 */

		$caseType = null;

		if((int)$invoice->categoryid == 1)
		{
			$caseType = 'AWD';
		}
		else if((int)$invoice->categoryid == 2 && $invoice->ispm==0)
		{
			$caseType = 'CSF';
		}
		else if((int)$invoice->categoryid == 2 && $invoice->ispm==1)
		{
			$caseType = 'AWD';
		}

		/*
		 * Default Calculation Values
		 */

		$departmentGrossReceived = 0;
		$departmentTaxableAmount = 0;
		$departmentGstAmount = 0;
		$departmentTdsAmount = 0;
		$departmentGstTdsAmount = 0;
		$departmentOtherDeduction = 0;
		$departmentReceived = 0;
		$utilizedAmount = 0;

		$currentInvoiceAmount = round((float)$invoice->net_invoice_value,2);

		$availableBalance = 0;
		$shortfall = 0;
		$hasCsfShortfall = false;

		$invoiceBasicAmount = round((float)$invoice->taxable_amount,2);
		$invoiceGrossAmount = round((float)$invoice->net_invoice_value,2);
		$invoiceGstAmount = round($invoiceGrossAmount - $invoiceBasicAmount,2);

		/*
		 * Department Vendor Funding Tax
		 */

		$vendorFundingTdsTaxId = null;
		$vendorFundingGstTdsTaxId = null;
		$vendorFundingTdsRate = 0;
		$vendorFundingGstTdsRate = 0;

		/*
		 * Department Service Charge Tax
		 */

		$serviceChargeTdsTaxId = null;
		$serviceChargeGstTdsTaxId = null;
		$serviceChargeTdsRate = 0;
		$serviceChargeGstTdsRate = 0;

		/*
		 * Vendor Payment Tax
		 * Finance will select these manually.
		 */

		$vendorTdsTaxId = null;
		$vendorGstTdsTaxId = null;
		$vendorTdsRate = 0;
		$vendorGstTdsRate = 0;

		/*
		 * Default Department GST Rate
		 * If there is no Department Receipt, use 18%.
		 */

		$departmentGstRate = 18;

		$fullDepartmentTdsAmount = 0;
		$fullDepartmentGstTdsAmount = 0;
		$fullVendorNetReceived = 0;

		$adminChargePercent = round((float)$invoice->admin_charge_percent,2);
		$fullAdminChargeAmount = 0;
		$fullAvailableFund = 0;

		/*
		 * Fund Availability Table
		 */

		$fundAdminBasic = 0;
		$fundAdminGst = 0;
		$fundAdminTotal = 0;

		$fundInvoiceTds = 0;
		$fundInvoiceGstTds = 0;
		$fundAdminTds = 0;
		$fundAdminGstTds = 0;

		$fundInvoiceNet = 0;
		$fundAdminNet = 0;
		$fundTotalInvoice = 0;

		/*
		 * Vendor Payment Calculation
		 */

		$vendorInvoiceAvailableFund = 0;
		$adminChargeAmount = 0;
		$vendorInvoiceApprovalAmount = 0;

		$vendorTdsAmount = 0;
		$vendorGstTdsAmount = 0;
		$netPaymentToVendor = 0;

		/*
		 * Demand Notes
		 */

		$vendorFundingDemandNotes = collect();
		$serviceChargeDemandNotes = collect();

		$departmentFundingReceipt = null;
		$departmentServiceChargeReceipt = null;
		$balanceBeforeCurrentInvoice = 0;

		/*
		 * CSF Calculation
		 */

		$openingBalance = DB::table('finance_opening_balance')
							->where('request_id',$invoice->request_id)
							->whereIn('balance_type',['VENDOR_FUNDING','SERVICE_CHARGE'])
							->where('isactive',1)
							->sum('opening_balance') ?? 0;

		/*
		 * Opening Balance Tax Configuration
		 */

		$vendorFundingOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','VENDOR_FUNDING')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

		$serviceChargeOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','SERVICE_CHARGE')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

		if($caseType == 'CSF')
		{
			/*
			 * VENDOR_FUNDING Demand Notes
			 */

			$vendorFundingDemandNotesQuery = DB::table('finance_demand_note')->where('demand_note_type','VENDOR_FUNDING')->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$vendorFundingDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$vendorFundingDemandNotes = $vendorFundingDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			/*
			 * SERVICE_CHARGE Demand Notes
			 */

			$serviceChargeDemandNotesQuery = DB::table('finance_demand_note')->where('demand_note_type','SERVICE_CHARGE')->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$serviceChargeDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$serviceChargeDemandNotes = $serviceChargeDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			/*
			 * VENDOR_FUNDING Department Payment
			 */

			if($vendorFundingDemandNotes->count() > 0)
			{
				$vendorFundingDemandNoteIds = $vendorFundingDemandNotes->pluck('demand_note_id')->toArray();

				$departmentFundingReceipt = DB::table('finance_department_payment as dp')
											->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
											->where('dn.demand_note_type','VENDOR_FUNDING')
											->where('dn.status','Active')
											->where('dp.status','!=','Cancelled')
											->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)
											->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
											->orderBy('dp.department_payment_id','asc')
											->first();

				if($departmentFundingReceipt)
				{
					/*
					 * Total VENDOR_FUNDING Department Receipts
					 */

					$departmentGrossReceived = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.gross_received_amount');

					$departmentTaxableAmount = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.taxable_amount');

					$departmentGstAmount = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.gst_amount');

					$departmentTdsAmount = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.tds_amount');

					$departmentGstTdsAmount = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.gst_tds_amount');

					$departmentOtherDeduction = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.other_deduction');

					$departmentReceived = DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dn.demand_note_type','VENDOR_FUNDING')->where('dn.status','Active')->where('dp.status','!=','Cancelled')->whereIn('dp.demand_note_id',$vendorFundingDemandNoteIds)->sum('dp.net_received_amount');

					$departmentGrossReceived = round((float)$departmentGrossReceived,2);
					$departmentTaxableAmount = round((float)$departmentTaxableAmount,2);
					$departmentGstAmount = round((float)$departmentGstAmount,2);
					$departmentTdsAmount = round((float)$departmentTdsAmount,2);
					$departmentGstTdsAmount = round((float)$departmentGstTdsAmount,2);
					$departmentOtherDeduction = round((float)$departmentOtherDeduction,2);

					$departmentReceived = round((float)$departmentReceived,2) + round((float)$openingBalance,2);

					/*
					 * Vendor Funding GST Rate
					 */

					$receiptTaxableAmount = round((float)$departmentFundingReceipt->taxable_amount,2);
					$receiptGstAmount = round((float)$departmentFundingReceipt->gst_amount,2);

					if($receiptTaxableAmount > 0 && $receiptGstAmount > 0)
					{
						$departmentGstRate = round(($receiptGstAmount / $receiptTaxableAmount) * 100,2);
					}
					else
					{
						$departmentGstRate = 18;
					}

					/*
					 * Vendor Funding TDS
					 */

					if(!empty($departmentFundingReceipt->tds_tax_id))
					{
						$departmentTdsTax = DB::table('finance_tax_master')->where('tax_id',$departmentFundingReceipt->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->first();

						if($departmentTdsTax)
						{
							$vendorFundingTdsTaxId = $departmentFundingReceipt->tds_tax_id;
							$vendorFundingTdsRate = round((float)$departmentTdsTax->rate,2);
						}
					}

					/*
					 * Vendor Funding GST-TDS
					 */

					if(!empty($departmentFundingReceipt->gst_tds_tax_id))
					{
						$departmentGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$departmentFundingReceipt->gst_tds_tax_id)->where('tax_type','GST_TDS')->where('is_active',1)->first();

						if($departmentGstTdsTax)
						{
							$vendorFundingGstTdsTaxId = $departmentFundingReceipt->gst_tds_tax_id;
							$vendorFundingGstTdsRate = round((float)$departmentGstTdsTax->rate,2);
						}
					}
				}
			}

			/*
			 * VENDOR_FUNDING Tax Fallback
			 * No Department Receipt -> Opening Balance Tax Configuration
			 */

			if(!$departmentFundingReceipt && $vendorFundingOpeningTax)
			{
				$vendorFundingTdsRate = round((float)$vendorFundingOpeningTax->tds_rate,2);
				$vendorFundingGstTdsRate = round((float)$vendorFundingOpeningTax->gst_tds_rate,2);

				if($vendorFundingTdsRate > 0)
				{
					$vendorFundingTdsTax = DB::table('finance_tax_master')->where('tax_type','TDS')->whereRaw('ROUND(rate,2)=?',[$vendorFundingTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

					if($vendorFundingTdsTax)
					{
						$vendorFundingTdsTaxId = $vendorFundingTdsTax->tax_id;
					}
				}

				if($vendorFundingGstTdsRate > 0)
				{
					$vendorFundingGstTdsTax = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->whereRaw('ROUND(rate,2)=?',[$vendorFundingGstTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

					if($vendorFundingGstTdsTax)
					{
						$vendorFundingGstTdsTaxId = $vendorFundingGstTdsTax->tax_id;
					}
				}
			}

			/*
			 * If no Vendor Funding Receipt exists,
			 * Department Received remains Opening Balance
			 * and GST Rate remains 18%.
			 */

			if(!$departmentFundingReceipt)
			{
				$departmentReceived = round((float)$openingBalance,2);
				$departmentGstRate = 18;
			}

			/*
			 * SERVICE_CHARGE Department Payment
			 */

			if($serviceChargeDemandNotes->count() > 0)
			{
				$serviceChargeDemandNoteIds = $serviceChargeDemandNotes->pluck('demand_note_id')->toArray();

				$departmentServiceChargeReceipt = DB::table('finance_department_payment as dp')
												->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
												->where('dn.demand_note_type','SERVICE_CHARGE')
												->where('dn.status','Active')
												->where('dp.status','!=','Cancelled')
												->whereIn('dp.demand_note_id',$serviceChargeDemandNoteIds)
												->select('dp.department_payment_id','dp.gross_received_amount','dp.taxable_amount','dp.gst_amount','dp.tds_tax_id','dp.tds_amount','dp.gst_tds_tax_id','dp.gst_tds_amount','dp.other_deduction','dp.net_received_amount')
												->orderBy('dp.department_payment_id','asc')
												->first();

				if($departmentServiceChargeReceipt)
				{
					/*
					 * Service Charge TDS
					 */

					if(!empty($departmentServiceChargeReceipt->tds_tax_id))
					{
						$serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_id',$departmentServiceChargeReceipt->tds_tax_id)->where('tax_type','TDS')->where('is_active',1)->first();

						if($serviceChargeTdsTax)
						{
							$serviceChargeTdsTaxId = $departmentServiceChargeReceipt->tds_tax_id;
							$serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
						}
					}

					/*
					 * Service Charge GST-TDS
					 */

					if(!empty($departmentServiceChargeReceipt->gst_tds_tax_id))
					{
						$serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$departmentServiceChargeReceipt->gst_tds_tax_id)->where('tax_type','GST_TDS')->where('is_active',1)->first();

						if($serviceChargeGstTdsTax)
						{
							$serviceChargeGstTdsTaxId = $departmentServiceChargeReceipt->gst_tds_tax_id;
							$serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
						}
					}
				}
			}

			/*
			 * SERVICE_CHARGE Tax Fallback
			 * No Department Receipt -> Opening Balance Tax Configuration
			 */

			if(!$departmentServiceChargeReceipt && $serviceChargeOpeningTax)
			{
				$serviceChargeTdsRate = round((float)$serviceChargeOpeningTax->tds_rate,2);
				$serviceChargeGstTdsRate = round((float)$serviceChargeOpeningTax->gst_tds_rate,2);

				if($serviceChargeTdsRate > 0)
				{
					$serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_type','TDS')->whereRaw('ROUND(rate,2)=?',[$serviceChargeTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

					if($serviceChargeTdsTax)
					{
						$serviceChargeTdsTaxId = $serviceChargeTdsTax->tax_id;
					}
				}

				if($serviceChargeGstTdsRate > 0)
				{
					$serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->whereRaw('ROUND(rate,2)=?',[$serviceChargeGstTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

					if($serviceChargeGstTdsTax)
					{
						$serviceChargeGstTdsTaxId = $serviceChargeGstTdsTax->tax_id;
					}
				}
			}

			/*
			 * Full Vendor Funding Calculation
			 */

			$fullDepartmentTdsAmount = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);

			$fullDepartmentGstTdsAmount = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

			$fullVendorNetReceived = round($invoiceGrossAmount - $fullDepartmentTdsAmount - $fullDepartmentGstTdsAmount,2);

			$fullAdminChargeAmount = round($fullVendorNetReceived * $adminChargePercent / 100,2);

			$fullAvailableFund = round($fullVendorNetReceived + $fullAdminChargeAmount,2);

			/*
			 * Fund Availability Table
			 */

			$fundAdminBasic = round($invoiceBasicAmount * $adminChargePercent / 100,2);

			$fundAdminGst = round($invoiceGstAmount * $adminChargePercent / 100,2);

			$fundAdminTotal = round($fundAdminBasic + $fundAdminGst,2);

			/*
			 * Vendor Invoice Department Deductions
			 */

			$fundInvoiceTds = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);

			$fundInvoiceGstTds = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

			/*
			 * CHiPS Administrative Charge Deductions
			 * IMPORTANT: These use SERVICE_CHARGE rates.
			 */

			$fundAdminTds = round($fundAdminBasic * $serviceChargeTdsRate / 100,2);

			$fundAdminGstTds = round($fundAdminBasic * $serviceChargeGstTdsRate / 100,2);

			$fundInvoiceNet = round($invoiceGrossAmount - $fundInvoiceTds - $fundInvoiceGstTds,2);

			$fundAdminNet = round($fundAdminTotal - $fundAdminTds - $fundAdminGstTds,2);

			$fundTotalInvoice = round($fundInvoiceNet + $fundAdminNet,2);

			/*
			 * Vendor Payment Calculation
			 */

			$vendorInvoiceAvailableFund = round($fullAvailableFund);

			$adminChargeAmount = round($fullAdminChargeAmount);

			$vendorInvoiceApprovalAmount = round($vendorInvoiceAvailableFund - $adminChargeAmount);

			if($vendorInvoiceApprovalAmount < 0)
			{
				$vendorInvoiceApprovalAmount = 0;
			}

			/*
			 * Previous Verified Vendor Invoices
			 */

			$previousInvoicesQuery = DB::table('invoice_mpr')
											->where('finance_status','Verified')
											->where('voucher_id','!=',$invoice->voucher_id);

			if(!empty($invoice->request_id))
			{
				$previousInvoicesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$previousInvoicesQuery->where('order_id',$invoice->order_id);
			}

			$previousInvoices = $previousInvoicesQuery->select('voucher_id','fund_utilized_amount')->get();

			foreach($previousInvoices as $previousInvoice)
			{
				$utilizedAmount += round((float)$previousInvoice->fund_utilized_amount,2);
			}

			$utilizedAmount = round($utilizedAmount,2);

			/*
			 * Department Fund Available Before Current Invoice
			 */

			$balanceBeforeCurrentInvoice = round($departmentReceived - $utilizedAmount,2);

			/*
			 * Current Invoice Requirement
			 */

			$currentInvoiceAmount = round($invoiceGrossAmount,2);

			$availableBalance = round($balanceBeforeCurrentInvoice - $vendorInvoiceAvailableFund,2);

			if($balanceBeforeCurrentInvoice < $vendorInvoiceAvailableFund)
			{
				$shortfall = round($vendorInvoiceAvailableFund - $balanceBeforeCurrentInvoice,2);
				$hasCsfShortfall = true;
			}
		}
		else
		{
			$shortfall = 0;
			$hasCsfShortfall = false;
		}

		/*
		 * Ledger
		 */

		$ledger = DB::table('finance_ledger')->where('vendor_invoice_id',$invoice->voucher_id)->orderBy('transaction_date','asc')->orderBy('ledger_id','asc')->get();

		/*
		 * MPR Details
		 */

		$mprIds = [];

		if(!empty($invoice->mpr_ids))
		{
			$mprIds = array_values(array_filter(array_map('intval',explode(',',$invoice->mpr_ids))));
		}

		$mprs = collect();

		if(count($mprIds) > 0)
		{
			$mprs = DB::table('mpr_tbl as mpr')
						->select('mpr.*','b.name')
						->leftJoin('users_tbl as b','b.userid','=','mpr.verified_by')
						->whereIn('mpr.mpr_id',$mprIds)
						->orderBy('mpr.submission_date','asc')
						->orderBy('mpr.mpr_id','asc')
						->get();

			foreach($mprs as $mpr)
			{
				$mpr->resources = DB::table('mpr_attendance_summary as mas')
									->select('mas.*','erd.name')
									->leftJoin('eoi_resource_deployment as erd','erd.deploymentid','=','mas.deployment_id')
									->where('mas.mpr_id',$mpr->mpr_id)
									->orderBy('mas.deployment_id','asc')
									->get();
			}
		}

		/*
		 * Tax Master Lists For Vendor Payment
		 */

		$tdsTaxes = DB::table('finance_tax_master')->where('tax_type','TDS')->where('is_active',1)->orderBy('rate','asc')->get();

		$gstTdsTaxes = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->where('is_active',1)->orderBy('rate','asc')->get();

		return view('finance.vendorinvoice.view',compact(
			'invoice',
			'totalPaid',
			'currentBalance',
			'payments',
			'caseType',
			'vendorFundingDemandNotes',
			'serviceChargeDemandNotes',
			'departmentFundingReceipt',
			'departmentServiceChargeReceipt',
			'departmentGrossReceived',
			'departmentTaxableAmount',
			'departmentGstAmount',
			'departmentTdsAmount',
			'departmentGstTdsAmount',
			'departmentOtherDeduction',
			'departmentReceived',
			'departmentGstRate',
			'vendorFundingTdsTaxId',
			'vendorFundingGstTdsTaxId',
			'vendorFundingTdsRate',
			'vendorFundingGstTdsRate',
			'serviceChargeTdsTaxId',
			'serviceChargeGstTdsTaxId',
			'serviceChargeTdsRate',
			'serviceChargeGstTdsRate',
			'vendorTdsTaxId',
			'vendorGstTdsTaxId',
			'vendorTdsRate',
			'vendorGstTdsRate',
			'fullDepartmentTdsAmount',
			'fullDepartmentGstTdsAmount',
			'fullVendorNetReceived',
			'invoiceGrossAmount',
			'invoiceBasicAmount',
			'invoiceGstAmount',
			'adminChargePercent',
			'fullAdminChargeAmount',
			'fullAvailableFund',
			'fundAdminBasic',
			'fundAdminGst',
			'fundAdminTotal',
			'fundInvoiceTds',
			'fundInvoiceGstTds',
			'fundAdminTds',
			'fundAdminGstTds',
			'fundInvoiceNet',
			'fundAdminNet',
			'fundTotalInvoice',
			'vendorInvoiceAvailableFund',
			'adminChargeAmount',
			'vendorInvoiceApprovalAmount',
			'utilizedAmount',
			'currentInvoiceAmount',
			'balanceBeforeCurrentInvoice',
			'availableBalance',
			'shortfall',
			'hasCsfShortfall',
			'ledger',
			'mprIds',
			'mprs',
			'tdsTaxes',
			'gstTdsTaxes'
		));
	}
	catch(\Exception $e)
	{
		return redirect()->route('finance.vendorinvoice.index')->with('error',$e->getMessage());
	}
}
	
	public function verifyMpr(Request $request)
	{
		DB::beginTransaction();
		try
		{
			$rules = [
				'voucher_id'	=>	'required|integer',
				'mpr_id'		=>	'required|integer',
			];

			$validatedData	=	$request->validate($rules);

			$invoice	=	DB::table('invoice_mpr')
							->where('voucher_id',$request->voucher_id)
							->where('finance_status','Pending')
							->first();

			if(!$invoice)
			{
				throw new \Exception('Vendor Invoice not found or Finance verification is already completed.');
			}

			$mprIds = array_values(
				array_filter(
					array_map(
						'intval',
						explode(',',$invoice->mpr_ids)
					)
				)
			);

			if(!in_array((int)$request->mpr_id,$mprIds))
			{
				throw new \Exception('Selected MPR does not belong to this Vendor Invoice.');
			}

			$mpr	=	DB::table('mpr_tbl')
						->where('mpr_id',$request->mpr_id)
						->where('vendor_id',$invoice->vendor_id)
						->where('order_id',$invoice->order_id)
						->first();

			if(!$mpr)
			{
				throw new \Exception('MPR not found.');
			}

			if((int)$mpr->is_verified!=1)
			{
				throw new \Exception('MPR '.$mpr->mpr_number.' has not been verified by the Department/Project Manager.');
			}

			if((int)$mpr->finance_verified==1)
			{
				throw new \Exception('MPR '.$mpr->mpr_number.' is already Finance Verified.');
			}

			DB::table('mpr_tbl')
				->where('mpr_id',$request->mpr_id)
				->update([
					'finance_verified'		=>	1,
					'finance_verified_by'	=>	session('userId'),
					'finance_verified_on'	=>	now()
				]);

			DB::commit();
			return response()->json([
				'status'	=>	1,
				'message'	=>	'MPR '.$mpr->mpr_number.' Finance Verified successfully.'
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();
			return response()->json([
				'status'	=>	0,
				'message'	=>	$e->getMessage()
			],500);
		}
	}

/*
public function verifyVendorInvoice(Request $request)
{
	DB::beginTransaction();

	try
	{
		$rules = [
			'voucher_id' => 'required|integer',
			'finance_remarks' => ['nullable','string','not_regex:/<[^>]*>/'],
		];
		

		$validatedData = $request->validate($rules);

		$vendorTdsTaxId = $validatedData['vendor_tds_tax_id'] ?? 0;
		$vendorGstTdsTaxId = $validatedData['vendor_gst_tds_tax_id'] ?? 0;

		$invoice = DB::table('invoice_mpr')->where('voucher_id',$request->voucher_id)->where('finance_status','Pending')->first();

		if(!$invoice) throw new \Exception('Vendor Invoice not found or it has already been processed.');

		$mprIds = array_values(array_filter(array_map('intval',explode(',',$invoice->mpr_ids))));

		if(count($mprIds) == 0) throw new \Exception('No MPRs are linked with this Vendor Invoice.');

		$mprs = DB::table('mpr_tbl')->whereIn('mpr_id',$mprIds)->where('vendor_id',$invoice->vendor_id)->where('order_id',$invoice->order_id)->get();

		if($mprs->count() != count($mprIds)) throw new \Exception('One or more MPRs linked with this Vendor Invoice could not be found.');

		$pendingMprs = $mprs->filter(function($mpr){ return (int)$mpr->finance_verified != 1; })->pluck('mpr_number');

		if($pendingMprs->count() > 0) throw new \Exception('All MPRs must be Finance Verified before Vendor Invoice verification.<br><br>Pending MPRs: '.$pendingMprs->implode(', '));

		$vendor = DB::table('vendor_tbl')->where('vendorid',$invoice->vendor_id)->first();
		
		$isProjectManager	=	DB::table('eoi_request as a')
								->join('users_tbl as b','b.userid','=','a.userid')
								->where('a.requestid',$invoice->request_id)
								->value('b.ispm') ?? 0;
		
		if(!$vendor) throw new \Exception('Vendor not found.');

		$caseType = ((int)$vendor->categoryid == 2 && $isProjectManager==0) ? 'CSF' : 'AWD';
		$vendorTdsRate = 0;//round((float)$vendorTdsTax->rate,2);
		$vendorGstTdsRate = 0;//round((float)$vendorGstTdsTax->rate,2);

		$fundUtilizedAmount = 0;

		$vendorFundingTdsTaxId = null;
		$vendorFundingTdsRate = 0;
		$vendorFundingGstTdsTaxId = null;
		$vendorFundingGstTdsRate = 0;
		$serviceChargeTdsTaxId = null;
		$serviceChargeTdsRate = 0;
		$serviceChargeGstTdsTaxId = null;
		$serviceChargeGstTdsRate = 0;

		if($caseType == 'CSF')
		{
			$vendorFundingDemandNotesQuery = DB::table('finance_demand_note')
											->where('demand_note_type','VENDOR_FUNDING')
											->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$vendorFundingDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$vendorFundingDemandNotes = $vendorFundingDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			if($vendorFundingDemandNotes->count() == 0)
			{
				throw new \Exception('Vendor Invoice cannot be Finance Verified because no active Vendor Funding Demand Note was found for this EOI / Work Order.');
			}

			$vendorFundingDemandNoteIds = $vendorFundingDemandNotes->pluck('demand_note_id')->toArray();

			$serviceChargeDemandNotesQuery = DB::table('finance_demand_note')->where('demand_note_type','SERVICE_CHARGE')->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$serviceChargeDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$serviceChargeDemandNotes = $serviceChargeDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			$serviceChargeDemandNoteIds = $serviceChargeDemandNotes->pluck('demand_note_id')->toArray();

			$vendorFundingPayment = DB::table('finance_department_payment')->whereIn('demand_note_id',$vendorFundingDemandNoteIds)->where('status','!=','Cancelled')->orderBy('department_payment_id','asc')->first();

			if(!$vendorFundingPayment)
			{
				throw new \Exception('Vendor Invoice cannot be Finance Verified because no Department payment has been received against the Vendor Funding Demand Note.');
			}

			$serviceChargePayment = null;

			if(count($serviceChargeDemandNoteIds) > 0)
			{
				$serviceChargePayment = DB::table('finance_department_payment')->whereIn('demand_note_id',$serviceChargeDemandNoteIds)->where('status','!=','Cancelled')->orderBy('department_payment_id','asc')->first();
			}

			$vendorFundingTdsTaxId = !empty($vendorFundingPayment->tds_tax_id) ? $vendorFundingPayment->tds_tax_id : null;
			$vendorFundingGstTdsTaxId = !empty($vendorFundingPayment->gst_tds_tax_id) ? $vendorFundingPayment->gst_tds_tax_id : null;

			$serviceChargeTdsTaxId = $serviceChargePayment && !empty($serviceChargePayment->tds_tax_id) ? $serviceChargePayment->tds_tax_id : null;
			$serviceChargeGstTdsTaxId = $serviceChargePayment && !empty($serviceChargePayment->gst_tds_tax_id) ? $serviceChargePayment->gst_tds_tax_id : null;

			if(!empty($vendorFundingTdsTaxId))
			{
				$vendorFundingTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingTdsTaxId)->where('tax_type','TDS')->where('is_active',1)->first();

				if($vendorFundingTdsTax)
				{
					$vendorFundingTdsRate = round((float)$vendorFundingTdsTax->rate,2);
				}
			}

			if(!empty($vendorFundingGstTdsTaxId))
			{
				$vendorFundingGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingGstTdsTaxId)->where('tax_type','GST_TDS')->where('is_active',1)->first();

				if($vendorFundingGstTdsTax)
				{
					$vendorFundingGstTdsRate = round((float)$vendorFundingGstTdsTax->rate,2);
				}
			}

			if(!empty($serviceChargeTdsTaxId))
			{
				$serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeTdsTaxId)->where('tax_type','TDS')->where('is_active',1)->first();

				if($serviceChargeTdsTax)
				{
					$serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
				}
			}

			if(!empty($serviceChargeGstTdsTaxId))
			{
				$serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeGstTdsTaxId)->where('tax_type','GST_TDS')->where('is_active',1)->first();

				if($serviceChargeGstTdsTax)
				{
					$serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
				}
			}

			$invoiceGrossAmount = round((float)$invoice->net_invoice_value,2);
			$invoiceBasicAmount = round((float)$invoice->taxable_amount,2);
			$invoiceGstAmount = round($invoiceGrossAmount - $invoiceBasicAmount,2);

			$adminChargePercent = round((float)$invoice->admin_charge_percent,2);

			$fundInvoiceTds = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);
			$fundInvoiceGstTds = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

			$fundInvoiceNet = round($invoiceGrossAmount - $fundInvoiceTds - $fundInvoiceGstTds);

			$fundAdminBasic = round($invoiceBasicAmount * $adminChargePercent / 100,2);
			$fundAdminGst = round($invoiceGstAmount * $adminChargePercent / 100,2);
			$fundAdminTotal = round($fundAdminBasic + $fundAdminGst,2);

			$fundAdminTds = round($fundAdminBasic * $serviceChargeTdsRate / 100,2);
			$fundAdminGstTds = round($fundAdminBasic * $serviceChargeGstTdsRate / 100,2);

			$fundAdminNet = round($fundAdminTotal - $fundAdminTds - $fundAdminGstTds);

			$currentInvoiceFundRequirement = round($fundInvoiceNet + $fundAdminNet);

			$departmentPayments = DB::table('finance_department_payment')->whereIn('demand_note_id',$vendorFundingDemandNoteIds)->where('status','!=','Cancelled')->get();

			if($departmentPayments->count() == 0)
			{
				throw new \Exception('Vendor Invoice cannot be Finance Verified because no Department payment has been received against the Vendor Funding Demand Note.');
			}

			$departmentReceived = round((float)$departmentPayments->sum('net_received_amount'),2);

			$previousInvoicesQuery = DB::table('invoice_mpr')->where('finance_status','Verified')->where('voucher_id','!=',$invoice->voucher_id);

			if(!empty($invoice->request_id))
			{
				$previousInvoicesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$previousInvoicesQuery->where('order_id',$invoice->order_id);
			}

			$previousInvoices = $previousInvoicesQuery->select('voucher_id','fund_utilized_amount')->get();

			$utilizedAmount = 0;

			foreach($previousInvoices as $previousInvoice)
			{
				$utilizedAmount += round((float)$previousInvoice->fund_utilized_amount,2);
			}

			$utilizedAmount = round($utilizedAmount,2);

			$balanceBeforeCurrentInvoice = round($departmentReceived - $utilizedAmount,2);

			$availableBalance = round($balanceBeforeCurrentInvoice - $currentInvoiceFundRequirement,2);

			if($balanceBeforeCurrentInvoice < $currentInvoiceFundRequirement)
			{
				$shortfall = round($currentInvoiceFundRequirement - $balanceBeforeCurrentInvoice,2);

				throw new \Exception('Vendor Invoice cannot be Finance Verified because the Department-paid Vendor Funding balance is insufficient.<br><br>Available Fund Before Current Invoice: ₹'.number_format($balanceBeforeCurrentInvoice,2).'<br>Current Invoice Approval Amount: ₹'.number_format($currentInvoiceFundRequirement,2).'<br>Shortfall: ₹'.number_format($shortfall,2));
			}

			$fundUtilizedAmount = round($currentInvoiceFundRequirement,2);
		}

		DB::table('invoice_mpr')->where('voucher_id',$invoice->voucher_id)->update([
			'finance_status' => 'Verified',
			'finance_verified_by' => session('userId'),
			'finance_verified_on' => now(),
			'finance_remarks' => $request->finance_remarks,
			'fund_utilized_amount' => $fundUtilizedAmount,
			'vendor_funding_tds_tax_id' => $vendorFundingTdsTaxId,
			'vendor_funding_tds_rate' => $vendorFundingTdsRate,
			'vendor_funding_gst_tds_tax_id' => $vendorFundingGstTdsTaxId,
			'vendor_funding_gst_tds_rate' => $vendorFundingGstTdsRate,
			'service_charge_tds_tax_id' => $serviceChargeTdsTaxId,
			'service_charge_tds_rate' => $serviceChargeTdsRate,
			'service_charge_gst_tds_tax_id' => $serviceChargeGstTdsTaxId,
			'service_charge_gst_tds_rate' => $serviceChargeGstTdsRate,
			'vendor_tds_tax_id' => $vendorTdsTaxId,
			'vendor_tds_rate' => $vendorTdsRate,
			'vendor_gst_tds_tax_id' => $vendorGstTdsTaxId,
			'vendor_gst_tds_rate' => $vendorGstTdsRate
		]);

		DB::table('finance_activity_log')->insert([
			'transaction_uuid' => (string)\Illuminate\Support\Str::uuid(),
			'module_name' => 'Vendor Invoice',
			'record_id' => $invoice->voucher_id,
			'action' => 'APPROVE',
			'action_description' => 'Vendor Invoice Finance Verified',
			'remarks' => 'Vendor Invoice '.$invoice->invoice_number.' Finance Verified.',
			'action_by' => session('userId'),
			'action_date' => now(),
			'ip_address' => $request->ip()
		]);

		DB::table('finance_ledger')->insert([
			'order_id' => $invoice->order_id ?: 0,
			'request_id' => $invoice->request_id ?: NULL,
			'demand_note_id' => NULL,
			'department_payment_id' => NULL,
			'department_invoice_id' => NULL,
			'vendor_invoice_id' => $invoice->voucher_id,
			'vendor_payment_id' => NULL,
			'reference_type' => 'VENDOR_INVOICE',
			'reference_id' => $invoice->voucher_id,
			'transaction_date' => $invoice->invoice_date,
			'financial_year' => FinanceHelper::financialYearFromDate($invoice->invoice_date),
			'ledger_type' => 'Journal',
			'dr_amount' => 0,
			'cr_amount' => $invoice->net_invoice_value,
			'remarks' => 'Vendor Invoice Finance Verified',
			'narration' => 'Vendor Invoice '.$invoice->invoice_number.' Finance Verified.',
			'created_by' => session('userId'),
			'created_at' => now()
		]);

		DB::commit();

		return response()->json(['status'=>1,'message'=>'Vendor Invoice Finance Verified successfully.']);
	}
	catch(\Exception $e)
	{
		DB::rollBack();

		return response()->json(['status'=>0,'message'=>$e->getMessage()],500);
	}
}
*/


public function verifyVendorInvoice(Request $request)
{
	DB::beginTransaction();

	try
	{
		/*
		 * Validation
		 */

		$rules = [
			'voucher_id' => 'required|integer',
			'finance_remarks' => ['nullable','string','not_regex:/<[^>]*>/'],
		];

		$validatedData = $request->validate($rules);

		$vendorTdsTaxId = $validatedData['vendor_tds_tax_id'] ?? 0;
		$vendorGstTdsTaxId = $validatedData['vendor_gst_tds_tax_id'] ?? 0;

		$invoice = DB::table('invoice_mpr')->where('voucher_id',$request->voucher_id)->where('finance_status','Pending')->first();

		if(!$invoice) throw new \Exception('Vendor Invoice not found or it has already been processed.');

		$mprIds = array_values(array_filter(array_map('intval',explode(',',$invoice->mpr_ids))));

		if(count($mprIds) == 0) throw new \Exception('No MPRs are linked with this Vendor Invoice.');

		$mprs = DB::table('mpr_tbl')->whereIn('mpr_id',$mprIds)->where('vendor_id',$invoice->vendor_id)->where('order_id',$invoice->order_id)->get();

		if($mprs->count() != count($mprIds)) throw new \Exception('One or more MPRs linked with this Vendor Invoice could not be found.');

		$pendingMprs = $mprs->filter(function($mpr){ return (int)$mpr->finance_verified != 1; })->pluck('mpr_number');

		if($pendingMprs->count() > 0) throw new \Exception('All MPRs must be Finance Verified before Vendor Invoice verification.<br><br>Pending MPRs: '.$pendingMprs->implode(', '));

		$vendor = DB::table('vendor_tbl')->where('vendorid',$invoice->vendor_id)->first();

		$isProjectManager = DB::table('eoi_request as a')->join('users_tbl as b','b.userid','=','a.userid')->where('a.requestid',$invoice->request_id)->value('b.ispm') ?? 0;

		if(!$vendor) throw new \Exception('Vendor not found.');

		$caseType = ((int)$vendor->categoryid == 2 && $isProjectManager==0) ? 'CSF' : 'AWD';

		/*
		 * Vendor Payment Tax Configuration
		 * These are selected manually by Finance.
		 */

		$vendorTdsRate = 0;
		$vendorGstTdsRate = 0;

		$fundUtilizedAmount = 0;

		/*
		 * Department Vendor Funding Tax Configuration
		 */

		$vendorFundingTdsTaxId = null;
		$vendorFundingTdsRate = 0;
		$vendorFundingGstTdsTaxId = null;
		$vendorFundingGstTdsRate = 0;

		/*
		 * Department Service Charge Tax Configuration
		 */

		$serviceChargeTdsTaxId = null;
		$serviceChargeTdsRate = 0;
		$serviceChargeGstTdsTaxId = null;
		$serviceChargeGstTdsRate = 0;

		/*
		 * Department GST Rate
		 * Default GST rate is 18%.
		 */

		$departmentGstRate = 18;

		if($caseType == 'CSF')
		{
			/*
			 * Opening Balance
			 */

			$vendorFundingOpeningBalance = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','VENDOR_FUNDING')->where('isactive',1)->sum('opening_balance');

			/*
			 * Opening Balance Tax Configuration - Vendor Funding
			 */

			$vendorFundingOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','VENDOR_FUNDING')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

			/*
			 * Opening Balance Tax Configuration - Service Charge
			 */

			$serviceChargeOpeningTax = DB::table('finance_opening_balance')->where('request_id',$invoice->request_id)->where('balance_type','SERVICE_CHARGE')->where('isactive',1)->orderBy('opening_balance_id','asc')->first();

			/*
			 * VENDOR_FUNDING Demand Notes
			 */

			$vendorFundingDemandNotesQuery = DB::table('finance_demand_note')->where('demand_note_type','VENDOR_FUNDING')->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$vendorFundingDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$vendorFundingDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$vendorFundingDemandNotes = $vendorFundingDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			$vendorFundingDemandNoteIds = $vendorFundingDemandNotes->pluck('demand_note_id')->toArray();

			/*
			 * SERVICE_CHARGE Demand Notes
			 */

			$serviceChargeDemandNotesQuery = DB::table('finance_demand_note')->where('demand_note_type','SERVICE_CHARGE')->where('status','Active');

			if(!empty($invoice->request_id))
			{
				$serviceChargeDemandNotesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$serviceChargeDemandNotesQuery->where('order_id',$invoice->order_id);
			}

			$serviceChargeDemandNotes = $serviceChargeDemandNotesQuery->orderBy('demand_note_id','asc')->get();

			$serviceChargeDemandNoteIds = $serviceChargeDemandNotes->pluck('demand_note_id')->toArray();

			/*
			 * VENDOR_FUNDING Department Payment
			 */

			$vendorFundingPayment = null;

			if(count($vendorFundingDemandNoteIds) > 0)
			{
				$vendorFundingPayment = DB::table('finance_department_payment')->whereIn('demand_note_id',$vendorFundingDemandNoteIds)->where('status','!=','Cancelled')->orderBy('department_payment_id','asc')->first();
			}

			/*
			 * SERVICE_CHARGE Department Payment
			 */

			$serviceChargePayment = null;

			if(count($serviceChargeDemandNoteIds) > 0)
			{
				$serviceChargePayment = DB::table('finance_department_payment')->whereIn('demand_note_id',$serviceChargeDemandNoteIds)->where('status','!=','Cancelled')->orderBy('department_payment_id','asc')->first();
			}

			/*
			 * VENDOR_FUNDING Tax Configuration
			 *
			 * Priority:
			 * 1. Department Receipt
			 * 2. Opening Balance
			 */

			if($vendorFundingPayment)
			{
				$vendorFundingTdsTaxId = !empty($vendorFundingPayment->tds_tax_id) ? $vendorFundingPayment->tds_tax_id : null;

				$vendorFundingGstTdsTaxId = !empty($vendorFundingPayment->gst_tds_tax_id) ? $vendorFundingPayment->gst_tds_tax_id : null;

				if(!empty($vendorFundingTdsTaxId))
				{
					$vendorFundingTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingTdsTaxId)->where('tax_type','TDS')->where('is_active',1)->first();

					if($vendorFundingTdsTax)
					{
						$vendorFundingTdsRate = round((float)$vendorFundingTdsTax->rate,2);
					}
				}

				if(!empty($vendorFundingGstTdsTaxId))
				{
					$vendorFundingGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$vendorFundingGstTdsTaxId)->where('tax_type','GST_TDS')->where('is_active',1)->first();

					if($vendorFundingGstTdsTax)
					{
						$vendorFundingGstTdsRate = round((float)$vendorFundingGstTdsTax->rate,2);
					}
				}

				/*
				 * Department Receipt GST Rate
				 */

				$receiptTaxableAmount = round((float)$vendorFundingPayment->taxable_amount,2);

				$receiptGstAmount = round((float)$vendorFundingPayment->gst_amount,2);

				if($receiptTaxableAmount > 0 && $receiptGstAmount > 0)
				{
					$departmentGstRate = round(($receiptGstAmount / $receiptTaxableAmount) * 100,2);
				}
				else
				{
					$departmentGstRate = 18;
				}
			}
			else
			{
				/*
				 * No Department Receipt
				 * Get TDS/GST-TDS from Opening Balance.
				 */

				if($vendorFundingOpeningTax)
				{
					$vendorFundingTdsRate = round((float)$vendorFundingOpeningTax->tds_rate,2);

					$vendorFundingGstTdsRate = round((float)$vendorFundingOpeningTax->gst_tds_rate,2);

					if($vendorFundingTdsRate > 0)
					{
						$vendorFundingTdsTax = DB::table('finance_tax_master')->where('tax_type','TDS')->whereRaw('ROUND(rate,2)=?',[$vendorFundingTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

						if($vendorFundingTdsTax)
						{
							$vendorFundingTdsTaxId = $vendorFundingTdsTax->tax_id;
						}
					}

					if($vendorFundingGstTdsRate > 0)
					{
						$vendorFundingGstTdsTax = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->whereRaw('ROUND(rate,2)=?',[$vendorFundingGstTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

						if($vendorFundingGstTdsTax)
						{
							$vendorFundingGstTdsTaxId = $vendorFundingGstTdsTax->tax_id;
						}
					}
				}

				/*
				 * No Receipt -> GST 18%
				 */

				$departmentGstRate = 18;
			}

			/*
			 * SERVICE_CHARGE Tax Configuration
			 *
			 * Priority:
			 * 1. Department Receipt
			 * 2. Opening Balance
			 */

			if($serviceChargePayment)
			{
				$serviceChargeTdsTaxId = !empty($serviceChargePayment->tds_tax_id) ? $serviceChargePayment->tds_tax_id : null;

				$serviceChargeGstTdsTaxId = !empty($serviceChargePayment->gst_tds_tax_id) ? $serviceChargePayment->gst_tds_tax_id : null;

				if(!empty($serviceChargeTdsTaxId))
				{
					$serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeTdsTaxId)->where('tax_type','TDS')->where('is_active',1)->first();

					if($serviceChargeTdsTax)
					{
						$serviceChargeTdsRate = round((float)$serviceChargeTdsTax->rate,2);
					}
				}

				if(!empty($serviceChargeGstTdsTaxId))
				{
					$serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_id',$serviceChargeGstTdsTaxId)->where('tax_type','GST_TDS')->where('is_active',1)->first();

					if($serviceChargeGstTdsTax)
					{
						$serviceChargeGstTdsRate = round((float)$serviceChargeGstTdsTax->rate,2);
					}
				}
			}
			else
			{
				/*
				 * No Department Receipt
				 * Get TDS/GST-TDS from Opening Balance.
				 */

				if($serviceChargeOpeningTax)
				{
					$serviceChargeTdsRate = round((float)$serviceChargeOpeningTax->tds_rate,2);

					$serviceChargeGstTdsRate = round((float)$serviceChargeOpeningTax->gst_tds_rate,2);

					if($serviceChargeTdsRate > 0)
					{
						$serviceChargeTdsTax = DB::table('finance_tax_master')->where('tax_type','TDS')->whereRaw('ROUND(rate,2)=?',[$serviceChargeTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

						if($serviceChargeTdsTax)
						{
							$serviceChargeTdsTaxId = $serviceChargeTdsTax->tax_id;
						}
					}

					if($serviceChargeGstTdsRate > 0)
					{
						$serviceChargeGstTdsTax = DB::table('finance_tax_master')->where('tax_type','GST_TDS')->whereRaw('ROUND(rate,2)=?',[$serviceChargeGstTdsRate])->where('is_active',1)->orderBy('tax_id','asc')->first();

						if($serviceChargeGstTdsTax)
						{
							$serviceChargeGstTdsTaxId = $serviceChargeGstTdsTax->tax_id;
						}
					}
				}
			}

			/*
			 * Invoice Amounts
			 */

			$invoiceGrossAmount = round((float)$invoice->net_invoice_value,2);

			$invoiceBasicAmount = round((float)$invoice->taxable_amount,2);

			$invoiceGstAmount = round($invoiceGrossAmount - $invoiceBasicAmount,2);

			$adminChargePercent = round((float)$invoice->admin_charge_percent,2);

			/*
			 * Vendor Funding TDS / GST-TDS
			 */

			$fundInvoiceTds = round($invoiceBasicAmount * $vendorFundingTdsRate / 100,2);

			$fundInvoiceGstTds = round($invoiceBasicAmount * $vendorFundingGstTdsRate / 100,2);

			$fundInvoiceNet = round($invoiceGrossAmount - $fundInvoiceTds - $fundInvoiceGstTds,2);

			/*
			 * CHiPS Administrative Charges
			 */

			$fundAdminBasic = round($invoiceBasicAmount * $adminChargePercent / 100,2);

			$fundAdminGst = round($invoiceGstAmount * $adminChargePercent / 100,2);

			$fundAdminTotal = round($fundAdminBasic + $fundAdminGst,2);

			/*
			 * Service Charge TDS / GST-TDS
			 */

			$fundAdminTds = round($fundAdminBasic * $serviceChargeTdsRate / 100,2);

			$fundAdminGstTds = round($fundAdminBasic * $serviceChargeGstTdsRate / 100,2);

			$fundAdminNet = round($fundAdminTotal - $fundAdminTds - $fundAdminGstTds,2);

			/*
			 * Current Invoice Fund Requirement
			 */

			$currentInvoiceFundRequirement = round($fundInvoiceNet + $fundAdminNet,2);

			/*
			 * Department Payments
			 *
			 * If Department Payment exists, use actual net received.
			 * If no Department Payment exists, use Opening Balance.
			 */

			$departmentPayments = collect();

			if(count($vendorFundingDemandNoteIds) > 0)
			{
				$departmentPayments = DB::table('finance_department_payment')->whereIn('demand_note_id',$vendorFundingDemandNoteIds)->where('status','!=','Cancelled')->get();
			}

			if($departmentPayments->count() > 0)
			{
				$departmentReceived = round((float)$departmentPayments->sum('net_received_amount'),2);
			}
			else
			{
				$departmentReceived = round((float)$vendorFundingOpeningBalance,2);
			}

			/*
			 * Previous Verified Vendor Invoices
			 */

			$previousInvoicesQuery = DB::table('invoice_mpr')->where('finance_status','Verified')->where('voucher_id','!=',$invoice->voucher_id);

			if(!empty($invoice->request_id))
			{
				$previousInvoicesQuery->where('request_id',$invoice->request_id);
			}
			else
			{
				$previousInvoicesQuery->where('order_id',$invoice->order_id);
			}

			$previousInvoices = $previousInvoicesQuery->select('voucher_id','fund_utilized_amount')->get();

			$utilizedAmount = 0;

			foreach($previousInvoices as $previousInvoice)
			{
				$utilizedAmount += round((float)$previousInvoice->fund_utilized_amount,2);
			}

			$utilizedAmount = round($utilizedAmount,2);

			/*
			 * Available Fund Before Current Invoice
			 */

			$balanceBeforeCurrentInvoice = round($departmentReceived - $utilizedAmount,2);

			/*
			 * Current Invoice Fund Availability
			 */

			$availableBalance = round($balanceBeforeCurrentInvoice - $currentInvoiceFundRequirement,2);

			if($balanceBeforeCurrentInvoice < $currentInvoiceFundRequirement)
			{
				$shortfall = round($currentInvoiceFundRequirement - $balanceBeforeCurrentInvoice,2);

				throw new \Exception('Vendor Invoice cannot be Finance Verified because the Department-paid Vendor Funding balance is insufficient.<br><br>Available Fund Before Current Invoice: ₹'.number_format($balanceBeforeCurrentInvoice,2).'<br>Current Invoice Approval Amount: ₹'.number_format($currentInvoiceFundRequirement,2).'<br>Shortfall: ₹'.number_format($shortfall,2));
			}

			$fundUtilizedAmount = round($currentInvoiceFundRequirement,2);
		}

		/*
		 * Update Vendor Invoice
		 */

		DB::table('invoice_mpr')->where('voucher_id',$invoice->voucher_id)->update([
			'finance_status' => 'Verified',
			'finance_verified_by' => session('userId'),
			'finance_verified_on' => now(),
			'finance_remarks' => $request->finance_remarks,
			'fund_utilized_amount' => $fundUtilizedAmount,
			'vendor_funding_tds_tax_id' => $vendorFundingTdsTaxId,
			'vendor_funding_tds_rate' => $vendorFundingTdsRate,
			'vendor_funding_gst_tds_tax_id' => $vendorFundingGstTdsTaxId,
			'vendor_funding_gst_tds_rate' => $vendorFundingGstTdsRate,
			'service_charge_tds_tax_id' => $serviceChargeTdsTaxId,
			'service_charge_tds_rate' => $serviceChargeTdsRate,
			'service_charge_gst_tds_tax_id' => $serviceChargeGstTdsTaxId,
			'service_charge_gst_tds_rate' => $serviceChargeGstTdsRate,
			'vendor_tds_tax_id' => $vendorTdsTaxId,
			'vendor_tds_rate' => $vendorTdsRate,
			'vendor_gst_tds_tax_id' => $vendorGstTdsTaxId,
			'vendor_gst_tds_rate' => $vendorGstTdsRate
		]);

		/*
		 * Activity Log
		 */

		DB::table('finance_activity_log')->insert([
			'transaction_uuid' => (string)\Illuminate\Support\Str::uuid(),
			'module_name' => 'Vendor Invoice',
			'record_id' => $invoice->voucher_id,
			'action' => 'APPROVE',
			'action_description' => 'Vendor Invoice Finance Verified',
			'remarks' => 'Vendor Invoice '.$invoice->invoice_number.' Finance Verified.',
			'action_by' => session('userId'),
			'action_date' => now(),
			'ip_address' => $request->ip()
		]);

		/*
		 * Vendor Invoice Ledger
		 */

		DB::table('finance_ledger')->insert([
			'order_id' => $invoice->order_id ?: 0,
			'request_id' => $invoice->request_id ?: NULL,
			'demand_note_id' => NULL,
			'department_payment_id' => NULL,
			'department_invoice_id' => NULL,
			'vendor_invoice_id' => $invoice->voucher_id,
			'vendor_payment_id' => NULL,
			'reference_type' => 'VENDOR_INVOICE',
			'reference_id' => $invoice->voucher_id,
			'transaction_date' => $invoice->invoice_date,
			'financial_year' => FinanceHelper::financialYearFromDate($invoice->invoice_date),
			'ledger_type' => 'Journal',
			'dr_amount' => 0,
			'cr_amount' => $invoice->net_invoice_value,
			'remarks' => 'Vendor Invoice Finance Verified',
			'narration' => 'Vendor Invoice '.$invoice->invoice_number.' Finance Verified.',
			'created_by' => session('userId'),
			'created_at' => now()
		]);

		DB::commit();

		return response()->json(['status'=>1,'message'=>'Vendor Invoice Finance Verified successfully.']);
	}
	catch(\Exception $e)
	{
		DB::rollBack();

		return response()->json(['status'=>0,'message'=>$e->getMessage()],500);
	}
}
	
}
