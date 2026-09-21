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

class DepartmentPaymentController extends Controller
{
	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financedepartmentreceiptindex');

		$departments	=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->where('b.isdepartment',1)
							->orderby('b.name')
							->get();

		$managers		=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->Join('eoi_request as c','c.userid','=','b.userid')
							->where('b.ispm',1)
							->where('c.categoryid',2)
							->orderby('b.name')
							->groupBy('b.name')
							->get();

		$projects		=	DB::table('project_tbl')
							->select('projectid','project_name')
							->orderby('project_name')
							->get();
		
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token',$token);

		return view('finance.departmentreceipt.index', compact('departments','managers','projects','token'));
	}
	public function getDemandNoteList(Request $request)
	{
		$projectid		=	$request->input('projectid');
		$departmentid	=	$request->input('departmentid');
		$managerid		=	$request->input('managerid');
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

		$data	=	DB::table('finance_demand_note as dn')
							->leftJoin('department_tbl as d','d.departmentid','=','dn.department_id')
							->leftJoin('eoi_work_order as wo','wo.orderid','=','dn.order_id')
							->leftJoin('project_tbl as p','p.projectid','=','dn.project_id')
							->leftJoin('vendor_tbl as v','v.vendorid','=','dn.vendor_id')
							->select(
								'dn.*',
								'd.departmentname',
								'p.project_name',
								'v.companyname',
								'v.shortname',
								'wo.ordernumber'
							)
							->selectSub(function($q){
								$q->from('finance_department_payment')
									->selectRaw('IFNULL(SUM(gross_received_amount),0)')
									->whereColumn('demand_note_id','dn.demand_note_id')
									->whereIn('status',['Active','Invoiced']);
							},'total_received')
							->selectRaw('
								(
									dn.total_amount -
									(
										SELECT IFNULL(SUM(gross_received_amount),0)
										FROM finance_department_payment
										WHERE finance_department_payment.demand_note_id = dn.demand_note_id
										AND (status="Active" || status="Invoiced")
									)
								) AS balance_amount
							')
							->when($projectid!=0,function($query) use ($projectid){
								return $query->where('dn.project_id','=',$projectid);
							})
							->when($departmentid!=0,function($query) use ($departmentid){
								return $query->where('dn.department_id','=',$departmentid);
							})
							->when($managerid!=0,function($query) use ($managerid){
								return $query->where('dn.department_id','=',$managerid);
							})
							->when($pagesearch != '', function($query) use ($pagesearch) {
								return $query->where(function($q) use ($pagesearch) {
									$q->where('dn.demand_note_no', 'like', '%' . $pagesearch . '%')
									  ->orWhere('dn.particular', 'like', '%' . $pagesearch . '%')
									  ->orWhere('dn.remarks', 'like', '%' . $pagesearch . '%');
								});
							})
							->orderByDesc('dn.demand_note_id')
							->paginate($pagesize,['*'],'page',$currentPage);
		
						
		return view('finance/departmentreceipt/ajaxpages/demandnoteTable',['data' => $data]);
	}

	public function create(Request $request,$id)
	{
		$id = Crypt::decrypt($id);

		$record	=	DB::table('finance_demand_note as dn')
					->leftJoin('eoi_request as e','e.requestid','=','dn.request_id')
					->leftJoin('project_tbl as p','p.projectid','=','dn.project_id')
					->leftJoin('department_tbl as d','d.departmentid','=','dn.department_id')
					->select(
						'dn.*',
						'e.eoinumber',
						'e.engagementname',
						'p.project_name',
						'd.departmentname',
					)
					->selectSub(function($q){
						$q->from('finance_department_payment')
							->selectRaw('IFNULL(SUM(gross_received_amount),0)')
							->whereColumn('demand_note_id','dn.demand_note_id')
							->whereIn('status',['Active','Invoiced']);
					},'total_received')
					->where('dn.demand_note_id',$id)
					->first();

		if(!$record)
		{
			abort(404);
		}

		$record->balance_amount = $record->total_amount - $record->total_received;
		$record->gst_rate = 0;
		if($record->igst_rate > 0)
		{
			$record->gst_rate = $record->igst_rate;
		}
		else
		{
			$record->gst_rate = $record->cgst_rate + $record->sgst_rate;
		}


		if($record->balance_amount<=0)
		{
			return redirect()->route('finance.departmentreceipt.index')->with('error','This Demand Note has already been fully received.');
		}

		$receiptNo			=	FinanceHelper::generateDocumentNumber('Department Receipt');
		$financialYears 	= 	FinanceHelper::financialYears();
		
		$paymentModes 		= 	DB::table('finance_payment_mode_master')
								->where('is_active',1)
								->orderBy('sort_order')
								->get();
							
		$bankAccounts 		= 	DB::table('finance_bank_account')
								->where('is_active',1)
								->orderBy('bank_name')
								->get();
		
		
		$mode				=	'create';

		$departmentReceipt 	= 	FinanceHelper::departmentReceiptObject();

		$tdsTaxes			= 	DB::table('finance_tax_master')
								->where('tax_type','TDS')
								->where('is_active',1)
								->get();

						
		$gstTdsTaxes		=	DB::table('finance_tax_master')
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->get();
		
		return view(
			'finance.departmentreceipt.create',
			compact(
				'mode',
				'record',
				'departmentReceipt',
				'receiptNo',
				'financialYears',
				'bankAccounts',
				'paymentModes',
				'tdsTaxes',
				'gstTdsTaxes'
			)
		);
	}


	public function store(Request $request)
	{
		if($request->receipt_date)
		{
			$request->merge([
				'receipt_date' => Carbon::parse($request->receipt_date)->format('Y-m-d'),
			]);
		}

		if($request->voucher_date)
		{
			$request->merge([
				'voucher_date' => Carbon::parse($request->voucher_date)->format('Y-m-d'),
			]);
		}

		$rules = [
			'demand_note_id'		=> 	'required|integer',
			'department_id'			=> 	'required|integer',
			'financial_year'		=> 	'required',
			'receipt_date'			=> 	'required|date|before_or_equal:today',
			'payment_mode_id'		=> 	'required|integer',
			'bank_account_id'		=> 	'required|integer',
			'gross_received_amount'	=> 	'required|numeric|min:0.01',
			'tds_tax_id'			=> 	'nullable|integer',
			'gst_tds_tax_id'		=> 	'nullable|integer',
			'other_deduction'		=> 	'nullable|numeric|min:0',
			'voucher_no'			=> 	'nullable|max:100',
			'voucher_date'			=> 	'nullable|date|before_or_equal:today',
			'transaction_no'		=> 	'nullable|max:100',
			'remarks'				=> 	'nullable|max:1000',
			'attachment'			=> 	'nullable|mimes:pdf,jpg,jpeg,png|max:5120'

		];

		$messages = [
			'demand_note_id.required'		=>	'Demand Note is required.',
			'department_id.required'		=> 	'Department is required.',
			'financial_year.required'		=> 	'Financial year is required.',
			'receipt_date.required'			=> 	'Receipt Date is required.',
			'receipt_date.date'				=> 	'Please enter a valid Receipt Date.',
			'receipt_date.before_or_equal'	=> 	'Receipt Date cannot be greater than today.',
			'payment_mode_id.required'		=> 	'Payment Mode is required.',
			'bank_account_id.required'		=> 	'Bank Account is required.',
			'gross_received_amount.required'=> 	'Gross Receipt Amount is required.',
			'gross_received_amount.numeric'	=> 	'Gross Receipt Amount must be numeric.',
			'gross_received_amount.min'		=> 	'Gross Receipt Amount should be greater than zero.',
			'tds_tax_id.integer'			=> 	'Invalid TDS selected.',
			'gst_tds_tax_id.integer'		=> 	'Invalid GST-TDS selected.',
			'other_deduction.numeric'		=>  'Other Deduction must be numeric.',
			'voucher_date.date'				=>  'Please enter a valid Voucher Date.',
			'voucher_date.before_or_equal'	=>  'Voucher Date cannot be greater than today.',
			'remarks.max'					=>  'Remarks cannot exceed 1000 characters.',
			'attachment.mimes'				=>  'Attachment must be PDF, JPG, JPEG or PNG.',
			'attachment.max'				=>  'Attachment size should not exceed 5 MB.'
		];

		$validatedData = $request->validate($rules,$messages);

		DB::beginTransaction();

		$attachment = '';

		try
		{

			$demandNote	= 	DB::table('finance_demand_note')
							->where('demand_note_id',$request->demand_note_id)
							->where('status','Active')
							->first();

			if(!$demandNote)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 	0,
					'message' => 	'Demand Note not found or has been cancelled.'
				],422);
			}

			$gstRate = 0;

			if((float)$demandNote->igst_rate > 0)
			{
				$gstRate = (float)$demandNote->igst_rate;
			}
			else
			{
				$gstRate = (float)$demandNote->cgst_rate + (float)$demandNote->sgst_rate;
			}

			if(!empty($request->tds_tax_id))
			{
				$tdsTax	= 	DB::table('finance_tax_master')
							->where('tax_id',$request->tds_tax_id)
							->where('tax_type','TDS')
							->where('is_active',1)
							->first();

				if(!$tdsTax)
				{
					DB::rollBack();
					return response()->json([
						'status'  =>	0,
						'message' => 	'Invalid TDS selected.'
					],422);
				}
			}

			if(!empty($request->gst_tds_tax_id))
			{
				$gstTdsTax	=	DB::table('finance_tax_master')
								->where('tax_id',$request->gst_tds_tax_id)
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->first();

				if(!$gstTdsTax)
				{
					DB::rollBack();
					return response()->json([
						'status'	=> 	0,
						'message'	=> 	'Invalid GST-TDS selected.'
					],422);
				}
			}

			$totalGrossReceived	=	DB::table('finance_department_payment')
									->where('demand_note_id',$request->demand_note_id)
									->where('status','Active')
									->sum('gross_received_amount');

			$balanceAmount = round((float)$demandNote->total_amount- (float)$totalGrossReceived,2);
			
			if($balanceAmount<=0)
			{
				DB::rollBack();
				return response()->json([
					'status'	=> 	0,
					'message'	=>	'Payment has already been received against this Demand Note.'
				],422);
			}

			$grossReceivedAmount	=	round((float)$request->gross_received_amount,2);

			if($grossReceivedAmount>$balanceAmount)
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'Gross Receipt Amount cannot be greater than Balance Amount of ₹' .number_format($balanceAmount,2)
				],422);
			}

			$receiptCalculation = FinanceHelper::calculateDepartmentReceipt(
										$grossReceivedAmount,
										$gstRate,
										$request->tds_tax_id,
										$request->gst_tds_tax_id,
										$request->other_deduction ?: 0
									);

			$netReceivedAmount = $receiptCalculation['net_received_amount'];

			if($netReceivedAmount<=0)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' => 'Net Receipt Amount should be greater than zero.'
				],422);
			}

			$receiptNo = FinanceHelper::generateDocumentNumber('Department Receipt');

			if($request->hasFile('attachment'))
			{
				$file = $request->file('attachment');
				$attachment = $file->store('uploads/department_receipt','public');
			}

			$id	=	DB::table('finance_department_payment')
					->insertGetId([
						'demand_note_id'		=>	$request->demand_note_id,
						'request_id'			=> 	$demandNote->request_id ?: NULL,
						'order_id'				=> 	$demandNote->order_id ?: NULL,
						'department_id'			=> 	$demandNote->department_id,
						'project_id'			=> 	$demandNote->project_id ?: NULL,
						'vendor_id'				=> 	$demandNote->vendor_id ?: NULL,
						'receipt_no'			=> 	$receiptNo,
						'receipt_date'			=> 	date('Y-m-d',strtotime($request->receipt_date)),
						'financial_year'		=> 	$request->financial_year,
						'voucher_no'			=> 	$request->voucher_no ?: NULL,
						'voucher_date'			=> 	$request->voucher_date ? date('Y-m-d',strtotime($request->voucher_date)): NULL,
						'payment_mode_id'		=> 	$request->payment_mode_id,
						'bank_account_id'		=> 	$request->bank_account_id,
						'transaction_no'		=> 	$request->transaction_no ?: NULL,
						'gross_received_amount'	=> 	$receiptCalculation['gross_received_amount'],
						'taxable_amount'		=> 	$receiptCalculation['taxable_amount'],
						'gst_amount'			=> 	$receiptCalculation['gst_amount'],
						'tds_tax_id'			=> 	$request->tds_tax_id ?: NULL,
						'tds_amount'			=> 	$receiptCalculation['tds_amount'],
						'gst_tds_tax_id'		=> 	$request->gst_tds_tax_id ?: NULL,
						'gst_tds_amount'		=> 	$receiptCalculation['gst_tds_amount'],
						'other_deduction'		=> 	$receiptCalculation['other_deduction'],
						'net_received_amount'	=> 	$receiptCalculation['net_received_amount'],
						'remarks'				=> 	$request->remarks,
						'attachment'			=> 	$attachment,
						'status'				=> 	'Active',
						'created_by'			=> 	session('userId'),
						'created_at'			=> 	now()
					]);

			DB::table('finance_ledger')
				->insert([
					'order_id'				=>	$demandNote->order_id ?: 0,
					'request_id'			=>	$demandNote->request_id ?: NULL,
					'demand_note_id'		=>	$demandNote->demand_note_id,
					'department_payment_id'	=>	$id,
					'department_invoice_id'	=>	NULL,
					'vendor_invoice_id'		=>	NULL,
					'vendor_payment_id'		=>	NULL,
					'reference_type'		=>	'DEPARTMENT_PAYMENT',
					'reference_id'			=>	$id,
					'transaction_date'		=>	date('Y-m-d',strtotime($request->receipt_date)),
					'financial_year'		=>	$request->financial_year,
					'ledger_type'			=>	'Receipt',
					'dr_amount'				=>	$receiptCalculation['net_received_amount'],
					'cr_amount'				=>	0,
					'remarks'				=>	'Department Payment Receipt '.$receiptNo,
					'narration'				=>	'Department payment received against Demand Note '.$demandNote->demand_note_no,
					'created_by'			=>	session('userId'),
					'created_at'			=>	now()
				]);

			FinanceHelper::updateDemandNotePaymentStatus(
				$request->demand_note_id
			);

			DB::commit();

			session()->flash('success','Department Receipt created successfully.');

			return response()->json([
				'status'	=> 	1,
				'message'	=> 	'Department Receipt created successfully.',
				'redirect'	=> 	route('finance.departmentreceipt.index'),
				'id'		=> 	$id
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
				'status'	=> 	0,
				'message'	=> 	$e->getMessage()
			],500);
		}
	}

	public function getDepartmentReceiptList(Request $request)
	{
		$projectid		=	$request->input('projectid');
		$departmentid	=	$request->input('departmentid');
		$managerid		=	$request->input('managerid');
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	=	$request->input('page',1);

		$data	=	DB::table('finance_department_payment as fp')
					->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
					->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','fp.payment_mode_id')
					->leftJoin('finance_bank_account as ba','ba.bank_account_id','=','fp.bank_account_id')
					->leftJoin('department_tbl as dept','dept.departmentid','=','fp.department_id')
					->select(
						'fp.*',
						'dn.demand_note_no',
						'dn.demand_note_date',
						'dn.total_amount as demand_note_amount',
						'pm.payment_mode',
						'ba.bank_name',
						'ba.account_name',
						'dept.departmentname'
					)
					->selectSub(function($q)
					{
						$q->from('finance_department_invoice_payment as dip')
							->join('finance_department_invoice as di','di.department_invoice_id','=','dip.department_invoice_id')
							->selectRaw('IFNULL(SUM(dip.allocated_amount),0)')
							->whereColumn(
								'dip.department_payment_id',
								'fp.department_payment_id'
							)
							->whereIn('di.status',['Draft','Posted','Paid']);
					},'total_invoiced')
					->selectRaw("
						(
							fp.gross_received_amount
							-
							IFNULL(
								(
									SELECT SUM(dip.allocated_amount)
									FROM finance_department_invoice_payment as dip
									INNER JOIN finance_department_invoice as di
										ON di.department_invoice_id = dip.department_invoice_id
									WHERE dip.department_payment_id = fp.department_payment_id
									AND di.status IN ('Draft','Posted','Paid')
								),
								0
							)
						) as invoice_balance
					")
					->when($projectid!=0,function($query) use ($projectid)
					{
						return $query->where('fp.project_id','=',$projectid);
					})
					->when($departmentid!=0,function($query) use ($departmentid)
					{
						return $query->where('fp.department_id','=',$departmentid);
					})
					->when($pagesearch!='',function($query) use ($pagesearch)
					{
						return $query->where(function($q) use ($pagesearch)
						{
							$q->where('dn.demand_note_no','like','%'.$pagesearch.'%')
							->orWhere('fp.voucher_no','like','%'.$pagesearch.'%')
							->orWhere('fp.receipt_no','like','%'.$pagesearch.'%')
							->orWhere('fp.remarks','like','%'.$pagesearch.'%');
						});
					})
					->orderBy('fp.department_payment_id','desc')
					->paginate($pagesize,['*'],'page',$currentPage);

		return view('finance/departmentreceipt/ajaxpages/departmentreceiptTable',['data' => $data]);
	}

	public function edit($id)
	{
		try
		{
			$id	=	Crypt::decrypt($id);

			$payment	=	DB::table('finance_department_payment as fp')
							->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
							->leftJoin('department_tbl as dept','dept.departmentid','=','fp.department_id')
							->leftJoin('eoi_request as e','e.requestid','=','fp.request_id')
							->leftJoin('project_tbl as p','p.projectid','=','fp.project_id')
							->where('fp.department_payment_id',$id)
							->select(
								'fp.*',
								'dn.demand_note_no',
								'dn.demand_note_date',
								'dn.total_amount',
								'dn.cgst_rate',
								'dn.sgst_rate',
								'dn.igst_rate',
								'dn.from_date',
								'dn.to_date',
								'dept.departmentname',
								'e.eoinumber',
								'e.engagementname',
								'p.project_name',
							)
							->first();

			if(!$payment)
			{
				return redirect()->route('finance.departmentreceipt.index')->with('error','Department Payment not found.');
			}

			if($payment->status != 'Active')
			{
				return redirect()->route('finance.departmentreceipt.index')->with('error','Cancelled or Invoiced Department Payment cannot be edited.');
			}

			if(FinanceHelper::hasDepartmentInvoiceGenerated($id))
			{
				return redirect()->route('finance.departmentreceipt.index')->with('error','Invoiced Department Payment cannot be edited.');
			}
			
		
			if(!FinanceHelper::isLatestDepartmentPayment($id))
			{
				return redirect()
					->route('finance.departmentreceipt.index')
					->with(
						'error',
						'This Department Payment cannot be edited because subsequent receipt transactions have already been recorded.'
					);
			}

			if(FinanceHelper::hasDepartmentInvoice($id))
			{
				return redirect()
					->route('finance.departmentreceipt.index')
					->with(
						'error',
						'This Department Payment cannot be edited because an invoice has already been generated against it.'
					);
			}

			$totalReceived = DB::table('finance_department_payment')
								->where('demand_note_id',$payment->demand_note_id)
								->where('department_payment_id','!=',$id)
								->where('status','Active')
								->sum('gross_received_amount');

			$balanceAmount = round((float)$payment->total_amount - (float)$totalReceived,2);
			
			$payment->total_received = round((float)$totalReceived,2);
			$payment->balance_amount = $balanceAmount;
			
			$payment->gst_rate = 0;

			if((float)$payment->igst_rate > 0)
			{
				$payment->gst_rate = (float)$payment->igst_rate;
			}
			else
			{
				$payment->gst_rate	=	(float)$payment->cgst_rate	+	(float)$payment->sgst_rate;
			}

			$financialYears	=	FinanceHelper::financialYears();
			$bankAccounts 	= 	DB::table('finance_bank_account')
								->where('is_active',1)
								->orderBy('bank_name')
								->get();

			$paymentModes 	= 	DB::table('finance_payment_mode_master')
								->where('is_active',1)
								->orderBy('payment_mode')
								->get();

			$tdsTaxes 		=	DB::table('finance_tax_master')
								->where('tax_type','TDS')
								->where('is_active',1)
								->orderBy('rate')
								->get();

			$gstTdsTaxes 	= 	DB::table('finance_tax_master')
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->orderBy('rate')
								->get();

			$mode	=	'edit';
			$record = 	$payment;

			$departmentReceipt	=	$payment;
			$receiptNo 			= 	$payment->receipt_no;


			return view(
				'finance.departmentreceipt.create',
				compact(
					'mode',
					'record',
					'departmentReceipt',
					'receiptNo',
					'financialYears',
					'bankAccounts',
					'paymentModes',
					'tdsTaxes',
					'gstTdsTaxes'
				)		
			);
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.departmentreceipt.index')->with('error',$e->getMessage());
		}
	}


	public function update(Request $request, $id)
	{

		try
		{
			$paymentId = Crypt::decrypt($id);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'status'  => 0,
				'message' => 'Invalid Department Payment.'
			],422);
		}

		if($request->receipt_date)
		{
			$request->merge([
				'receipt_date' => Carbon::parse($request->receipt_date)->format('Y-m-d H:i:s')
			]);
		}

		if($request->voucher_date)
		{
			$request->merge([
				'voucher_date' => Carbon::parse($request->voucher_date)->format('Y-m-d H:i:s')
			]);
		}

		$rules = [
			'department_id'			=> 'required|integer',
			'financial_year'		=> 'required',
			'receipt_date'			=> 'required|date|before_or_equal:today',
			'payment_mode_id'		=> 'required|integer',
			'bank_account_id'		=> 'required|integer',
			'gross_received_amount'	=> 'required|numeric|min:0.01',
			'tds_tax_id'			=> 'nullable|integer',
			'gst_tds_tax_id'		=> 'nullable|integer',
			'other_deduction'		=> 'nullable|numeric|min:0',
			'voucher_no'			=> 'nullable|max:100',
			'voucher_date'			=> 'nullable|date|before_or_equal:today',
			'transaction_no'		=> 'nullable|max:100',
			'remarks'				=> 'nullable|max:1000',
			'attachment'			=> 'nullable|mimes:pdf,jpg,jpeg,png|max:5120'
		];


		$messages = [
			'department_id.required'		=> 	'Department is required.',
			'financial_year.required'		=> 	'Financial year is required.',
			'receipt_date.required'			=> 	'Receipt Date is required.',
			'receipt_date.date'				=> 	'Please enter a valid Receipt Date.',
			'receipt_date.before_or_equal'	=> 	'Receipt Date cannot be greater than today.',
			'payment_mode_id.required'		=> 	'Payment Mode is required.',
			'bank_account_id.required'		=> 	'Bank Account is required.',
			'gross_received_amount.required'=> 	'Gross Receipt Amount is required.',
			'gross_received_amount.numeric'	=> 	'Gross Receipt Amount must be numeric.',
			'gross_received_amount.min'		=> 	'Gross Receipt Amount should be greater than zero.',
			'tds_tax_id.integer'			=> 	'Invalid TDS selected.',
			'gst_tds_tax_id.integer'		=> 	'Invalid GST-TDS selected.',
			'other_deduction.numeric'		=> 	'Other Deduction must be numeric.',
			'voucher_date.date'				=> 	'Please enter a valid Voucher Date.',
			'voucher_date.before_or_equal'	=> 	'Voucher Date cannot be greater than today.',
			'remarks.max'					=> 	'Remarks cannot exceed 1000 characters.',
			'attachment.mimes'				=> 	'Attachment must be PDF, JPG, JPEG or PNG.',
			'attachment.max'				=> 	'Attachment size should not exceed 5 MB.'
		];


		$validatedData	=	$request->validate($rules,$messages);


		DB::beginTransaction();

		$oldAttachment = '';

		$newAttachment = '';

		try
		{

			$payment	=	DB::table('finance_department_payment')
							->where('department_payment_id',$paymentId)
							->first();

			if(!$payment)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' => 'Department Payment not found.'
				],404);
			}

			if($payment->status != 'Active')
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' => 'Cancelled or Invoiced Department Payment cannot be edited.'
				],422);
			}

			if(!FinanceHelper::isLatestDepartmentPayment($paymentId))
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' =>
						'This Department Payment cannot be edited because subsequent receipt transactions have already been recorded.'
				],422);
			}

			if(FinanceHelper::hasDepartmentInvoice($paymentId))
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' =>
						'This Department Payment cannot be edited because an invoice has already been generated against it.'
				],422);
			}

			$demandNote	=	DB::table('finance_demand_note')
							->where('demand_note_id',$payment->demand_note_id)
							->where('status','Active')
							->first();

			if(!$demandNote)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' => 'Demand Note not found or has been cancelled.'
				],422);
			}

			$gstRate = 0;

			if((float)$demandNote->igst_rate > 0)
			{
				$gstRate = (float)$demandNote->igst_rate;
			}
			else
			{
				$gstRate =(float)$demandNote->cgst_rate + (float)$demandNote->sgst_rate;
			}


			if(!empty($request->tds_tax_id))
			{
				$tdsTax	=	DB::table('finance_tax_master')
							->where('tax_id',$request->tds_tax_id)
							->where('tax_type','TDS')
							->where('is_active',1)
							->first();

				if(!$tdsTax)
				{
					DB::rollBack();
					return response()->json([
						'status'  => 0,
						'message' => 'Invalid TDS selected.'
					],422);
				}
			}

			if(!empty($request->gst_tds_tax_id))
			{
				$gstTdsTax = DB::table('finance_tax_master')
								->where('tax_id',$request->gst_tds_tax_id)
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->first();

				if(!$gstTdsTax)
				{
					DB::rollBack();
					return response()->json([
						'status'  => 0,
						'message' => 'Invalid GST-TDS selected.'
					],422);
				}
			}

			$otherGrossReceived = DB::table('finance_department_payment')
								->where('demand_note_id',$payment->demand_note_id)
								->where('department_payment_id','!=',$paymentId)
								->where('status','Active')
								->sum('gross_received_amount');

			$balanceAmount = round((float)$demandNote->total_amount - (float)$otherGrossReceived,2);


			if($balanceAmount <= 0)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 	0,
					'message' =>	'No balance is available against this Demand Note.'
				],422);
			}

			$grossReceivedAmount = round((float)$request->gross_received_amount,2);

			if($grossReceivedAmount > $balanceAmount)
			{
				DB::rollBack();
				return response()->json([
					'status'  =>	0,
					'message' =>	'Gross Receipt Amount cannot be greater than Balance Amount of ₹ '.number_format($balanceAmount,2)
				],422);
			}

			$receiptCalculation =	FinanceHelper::calculateDepartmentReceipt(
										$grossReceivedAmount,
										$gstRate,
										$request->tds_tax_id,
										$request->gst_tds_tax_id,
										$request->other_deduction ?: 0
									);

			$netReceivedAmount	=	$receiptCalculation['net_received_amount'];


			if($netReceivedAmount <= 0)
			{
				DB::rollBack();
				return response()->json([
					'status'  => 0,
					'message' =>
						'Net Receipt Amount should be greater than zero.'
				],422);
			}

			$oldAttachment = $payment->attachment;
			$newAttachment = $oldAttachment;

			if($request->hasFile('attachment'))
			{
				$newAttachment	=	$request->file('attachment')->store('uploads/department_receipt','public');
			}

			$oldData = [
				'gross_received_amount' => 	$payment->gross_received_amount,
				'taxable_amount'		=> 	$payment->taxable_amount,
				'gst_amount'			=> 	$payment->gst_amount,
				'tds_tax_id'			=> 	$payment->tds_tax_id,
				'tds_amount'			=> 	$payment->tds_amount,
				'gst_tds_tax_id'		=> 	$payment->gst_tds_tax_id,
				'gst_tds_amount'		=> 	$payment->gst_tds_amount,
				'other_deduction'		=> 	$payment->other_deduction,
				'net_received_amount'	=> 	$payment->net_received_amount,
				'receipt_date'			=> 	$payment->receipt_date,
				'voucher_no'			=> 	$payment->voucher_no,
				'voucher_date'			=> 	$payment->voucher_date,
				'payment_mode_id'		=> 	$payment->payment_mode_id,
				'bank_account_id'		=> 	$payment->bank_account_id,
				'transaction_no'		=> 	$payment->transaction_no,
				'remarks'				=> 	$payment->remarks
			];

			DB::table('finance_department_payment')
			->where('department_payment_id',$paymentId)
			->update([
				'financial_year'		=> 	$request->financial_year,
				'receipt_date'			=> 	date('Y-m-d',strtotime($request->receipt_date)),
				'voucher_no'			=> 	$request->voucher_no ?: NULL,
				'voucher_date'			=> 	$request->voucher_date ? date('Y-m-d',strtotime($request->voucher_date)) : NULL,
				'payment_mode_id'		=> 	$request->payment_mode_id,
				'bank_account_id'		=> 	$request->bank_account_id,
				'transaction_no'		=> 	$request->transaction_no ?: NULL,
				'gross_received_amount'	=> 	$receiptCalculation['gross_received_amount'],
				'taxable_amount'		=> 	$receiptCalculation['taxable_amount'],
				'gst_amount'			=> 	$receiptCalculation['gst_amount'],
				'tds_tax_id'			=> 	$request->tds_tax_id ?: NULL,
				'tds_amount'			=> 	$receiptCalculation['tds_amount'],
				'gst_tds_tax_id'		=> 	$request->gst_tds_tax_id ?: NULL,
				'gst_tds_amount'		=> 	$receiptCalculation['gst_tds_amount'],
				'other_deduction'		=> 	$receiptCalculation['other_deduction'],
				'net_received_amount'	=> 	$receiptCalculation['net_received_amount'],
				'remarks'				=> 	$request->remarks,
				'attachment'			=> 	$newAttachment,
				'updated_at'			=> 	now()
			]);

			$ledgerUpdated	=	DB::table('finance_ledger')
								->where('reference_type','DEPARTMENT_PAYMENT')
								->where('reference_id',$paymentId)
								->update([
									'order_id'				=>	$payment->order_id ?: 0,
									'request_id'			=>	$payment->request_id ?: NULL,
									'demand_note_id'		=>	$payment->demand_note_id,
									'department_payment_id'	=>	$paymentId,
									'transaction_date'		=>	date('Y-m-d',strtotime($request->receipt_date)),
									'financial_year'		=>	$request->financial_year,
									'ledger_type'			=>	'Receipt',
									'dr_amount'				=>	$receiptCalculation['net_received_amount'],
									'cr_amount'				=>	0,
									'remarks'				=>	'Department Payment Receipt '.$payment->receipt_no,
									'narration'				=>	'Department payment received against Demand Note '.$demandNote->demand_note_no
								]);
								
			if($ledgerUpdated == 0)
			{
				DB::table('finance_ledger')
				->insert([
					'order_id'				=>	$payment->order_id ?: 0,
					'request_id'			=>	$payment->request_id ?: NULL,
					'demand_note_id'		=>	$payment->demand_note_id,
					'department_payment_id'	=>	$paymentId,
					'department_invoice_id'	=>	NULL,
					'vendor_invoice_id'		=>	NULL,
					'vendor_payment_id'		=>	NULL,
					'reference_type'		=>	'DEPARTMENT_PAYMENT',
					'reference_id'			=>	$paymentId,
					'transaction_date'		=>	date('Y-m-d',strtotime($request->receipt_date)),
					'financial_year'		=>	$request->financial_year,
					'ledger_type'			=>	'Receipt',
					'dr_amount'				=>	$receiptCalculation['net_received_amount'],
					'cr_amount'				=>	0,
					'remarks'				=>	'Department Payment Receipt '.$payment->receipt_no,
					'narration'				=>	'Department payment received against Demand Note '.$demandNote->demand_note_no,
					'created_by'			=>	session('userId'),
					'created_at'			=>	now()
				]);
			}
			FinanceHelper::updateDemandNotePaymentStatus(
				$payment->demand_note_id
			);

			$newData = [
				'gross_received_amount'	=> 	$receiptCalculation['gross_received_amount'],
				'taxable_amount'		=> 	$receiptCalculation['taxable_amount'],
				'gst_amount'			=> 	$receiptCalculation['gst_amount'],
				'tds_tax_id'			=> 	$request->tds_tax_id ?: NULL,
				'tds_amount'			=> 	$receiptCalculation['tds_amount'],
				'gst_tds_tax_id'		=> 	$request->gst_tds_tax_id ?: NULL,
				'gst_tds_amount'		=> 	$receiptCalculation['gst_tds_amount'],
				'other_deduction'		=> 	$receiptCalculation['other_deduction'],
				'net_received_amount'	=> 	$receiptCalculation['net_received_amount'],
				'receipt_date'			=> 	date('Y-m-d',strtotime($request->receipt_date)),
				'voucher_no'			=> 	$request->voucher_no ?: NULL,
				'voucher_date'			=> 	$request->voucher_date ? date('Y-m-d',strtotime($request->voucher_date)) : NULL,
				'payment_mode_id'		=> 	$request->payment_mode_id,
				'bank_account_id'		=> 	$request->bank_account_id,
				'transaction_no'		=> 	$request->transaction_no,
				'remarks'				=> 	$request->remarks
			];

			FinanceHelper::logChanges(
				'Department Payment',
				$paymentId,
				$oldData,
				$newData,
				'UPDATE',
				'Department Payment updated successfully.'
			);

			DB::commit();

			if($request->hasFile('attachment') && !empty($oldAttachment) && $oldAttachment != $newAttachment)
			{
				Storage::disk('public')->delete($oldAttachment);
			}

			session()->flash('success','Department Payment updated successfully.');


			return response()->json([
				'status'	=>	1,
				'message'	=> 	'Department Payment updated successfully.',
				'redirect'	=> 	route('finance.departmentreceipt.index'),
				'id'		=> 	$paymentId
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();
			
			if(!empty($newAttachment) && $newAttachment != $oldAttachment)
			{
				Storage::disk('public')->delete($newAttachment);
			}
			return response()->json([
				'status'	=> 0,
				'message'	=> $e->getMessage()
			],500);
		}
	}

	public function cancel($id)
	{
		try
		{
			$paymentId = Crypt::decrypt($id);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'status'	=>	0,
				'message'	=>	'Invalid Department Payment.'
			],422);
		}

		DB::beginTransaction();

		try
		{
			$payment = DB::table('finance_department_payment')->where('department_payment_id',$paymentId)->first();

			if(!$payment)
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'Department Payment not found.'
				],404);
			}

			if($payment->status != 'Active')
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'Department Payment is already cancelled.'
				],422);
			}

			if(!FinanceHelper::isLatestDepartmentPayment($paymentId))
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'This Department Payment cannot be cancelled because subsequent receipt transactions have already been recorded.'
				],422);
			}

			if(FinanceHelper::hasDepartmentInvoice($paymentId))
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'This Department Payment cannot be cancelled because an invoice has already been generated against it.'
				],422);
			}
			if(FinanceHelper::hasDepartmentInvoiceGenerated($paymentId))
			{
				DB::rollBack();
				return response()->json([
					'status'	=>	0,
					'message'	=>	'This Department Payment cannot be cancelled because an invoice has already been generated against it.'
				],422);
			}

			$oldData = [
				'status'	=>	$payment->status
			];

			DB::table('finance_department_payment')
			->where('department_payment_id',$paymentId)
			->update([
				'status'		=>	'Cancelled',
				'updated_at'	=>	now()
			]);

			DB::table('finance_ledger')
			->insert([
				'order_id'				=>	$payment->order_id ?: 0,
				'request_id'			=>	$payment->request_id ?: NULL,
				'demand_note_id'		=>	$payment->demand_note_id ?: NULL,
				'department_payment_id'	=>	$paymentId,
				'department_invoice_id'	=>	NULL,
				'vendor_invoice_id'		=>	NULL,
				'vendor_payment_id'		=>	NULL,
				'reference_type'		=>	'DEPARTMENT_PAYMENT',
				'reference_id'			=>	$paymentId,
				'transaction_date'		=>	date('Y-m-d'),
				'financial_year'		=>	$payment->financial_year,
				'ledger_type'			=>	'Journal',
				'dr_amount'				=>	0,
				'cr_amount'				=>	$payment->net_received_amount,
				'remarks'				=>	'Cancellation of Department Payment '.$payment->receipt_no,
				'narration'				=>	'Reversal of Department Payment Receipt '.$payment->receipt_no,
				'created_by'				=>	session('userId'),
				'created_at'				=>	now()
			]);

			$newData = [
				'status'	=>	'Cancelled'
			];

			FinanceHelper::logChanges(
				'Department Payment',
				$paymentId,
				$oldData,
				$newData,
				'CANCEL',
				'Department Payment '.$payment->receipt_no.' cancelled successfully.'
			);

			FinanceHelper::updateDemandNotePaymentStatus(
				$payment->demand_note_id
			);

			DB::commit();

			return response()->json([
				'status'	=>	1,
				'message'	=>	'Department Payment cancelled successfully.',
				'redirect'	=>	route('finance.departmentreceipt.index')
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

	public function view($id)
	{
		try
		{
			$id = Crypt::decrypt($id);

			$payment = DB::table('finance_department_payment as fp')
						->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
						->leftJoin('department_tbl as dept','dept.departmentid','=','fp.department_id')
						->leftJoin('eoi_request as e','e.requestid','=','fp.request_id')
						->leftJoin('project_tbl as p','p.projectid','=','fp.project_id')
						->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','fp.payment_mode_id')
						->leftJoin('finance_bank_account as ba','ba.bank_account_id','=','fp.bank_account_id')
						->where('fp.department_payment_id',$id)
						->select(
							'fp.*',
							'dn.demand_note_no',
							'dn.demand_note_date',
							'dn.total_amount as demand_note_amount',
							'dn.cgst_rate',
							'dn.sgst_rate',
							'dn.igst_rate',
							'dept.departmentname',
							'e.eoinumber',
							'e.engagementname',
							'p.project_name',
							'pm.payment_mode',
							'ba.bank_name',
							'ba.account_name',
							'ba.account_number'
						)
						->first();

			if(!$payment)
			{
				return redirect()
					->route('finance.departmentreceipt.index')
					->with('error','Department Payment not found.');
			}

			$payment->gst_rate = 0;

			if((float)$payment->igst_rate > 0)
			{
				$payment->gst_rate = (float)$payment->igst_rate;
			}
			else
			{
				$payment->gst_rate = (float)$payment->cgst_rate + (float)$payment->sgst_rate;
			}

			$tdsTax = NULL;

			if(!empty($payment->tds_tax_id))
			{
				$tdsTax	=	DB::table('finance_tax_master')
							->where('tax_id',$payment->tds_tax_id)
							->first();
			}

			$gstTdsTax = NULL;

			if(!empty($payment->gst_tds_tax_id))
			{
				$gstTdsTax	=	DB::table('finance_tax_master')
								->where('tax_id',$payment->gst_tds_tax_id)
								->first();
			}

			$ledger	=	DB::table('finance_ledger')
						->where('reference_type','DEPARTMENT_PAYMENT')
						->where('reference_id',$id)
						->orderBy('ledger_id','asc')
						->get();

			$activityLogs	=	DB::table('finance_activity_log')
								->where('module_name','Department Payment')
								->where('record_id',$id)
								->orderBy('action_date','desc')
								->get();


			return view(
				'finance.departmentreceipt.view',
				compact(
					'payment',
					'tdsTax',
					'gstTdsTax',
					'ledger',
					'activityLogs'
				)
			);
		}
		catch(\Exception $e)
		{
			return redirect()
				->route('finance.departmentreceipt.index')
				->with('error',$e->getMessage());
		}
	}

	public function departmentReceipts(Request $request)
	{
		$departmentid	=	$request->input('departmentid');
		$pagesize		=	$request->input('pagesize',100);
		$pagesearch		=	$request->input('pagesearch');
		$currentPage	=	$request->input('page',1);

		try
		{
			$allocatedPayments = DB::table('finance_department_invoice_payment as dip')
				->join('finance_department_invoice as di','di.department_invoice_id','=','dip.department_invoice_id')
				->select('dip.department_payment_id',DB::raw('COALESCE(SUM(dip.allocated_amount),0) as total_invoiced')
				)
				->whereIn('di.status',['Draft','Posted','Paid'])
				->groupBy('dip.department_payment_id');

			$data = DB::table('finance_department_payment as fp')
				->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
				->leftJoinSub($allocatedPayments,'dip',
					function($join)
					{
						$join->on(
							'dip.department_payment_id',
							'=',
							'fp.department_payment_id'
						);
					}
				)
				->select(
					'fp.department_payment_id',
					'fp.receipt_no',
					'fp.receipt_date',
					'dn.demand_note_no',
					'fp.gross_received_amount',
					DB::raw('COALESCE(dip.total_invoiced,0) as total_invoiced'),
					DB::raw(
						'ROUND(
							fp.gross_received_amount -
							COALESCE(dip.total_invoiced,0),
							2
						) as balance'
					)
				)
				->where('fp.department_id',$request->departmentid)
				->where('fp.status','Active')
				->whereRaw('fp.gross_received_amount > COALESCE(dip.total_invoiced,0)')
				->when($pagesearch != '',function($query) use ($pagesearch)
				{
					return $query->where(function($q) use ($pagesearch)
					{
						$q->where('dn.demand_note_no','like','%'.$pagesearch.'%')
						->orWhere('fp.receipt_no','like','%'.$pagesearch.'%');
					});
				})
				->orderBy('fp.receipt_date','asc')
				->orderBy('fp.department_payment_id','asc')
				->paginate($pagesize,['*'],'page',$currentPage);

			return view('finance/departmentreceipt/ajaxpages/departmentreceiptsTable',['data'=>$data]);
		}
		catch(\Exception $e)
		{
			Log::error('Error: '.$e->getMessage());
			return response()->json([
				'success'	=>	false,
				'message'	=>	'Unable to fetch department receipts.'
			],500);
		}
	}
	
}
