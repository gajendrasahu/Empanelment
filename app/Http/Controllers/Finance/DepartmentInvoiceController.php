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
use Illuminate\Support\Str;
class DepartmentInvoiceController extends Controller
{

	public function prepareDepartmentInvoice(Request $request)
	{
		try
		{
			$paymentIds = $request->department_payment_ids;

			if(!is_array($paymentIds) || count($paymentIds) == 0)
			{
				return response()->json([
					'status'	=>	0,
					'message'	=>	'Please select at least one Department Receipt.'
				],422);
			}

			$paymentIds	=	array_values(array_unique(array_map('intval',$paymentIds)));

			$receipts	= 	DB::table('finance_department_payment')
							->whereIn('department_payment_id',$paymentIds)
							->where('status','Active')
							->get();

			if($receipts->count() != count($paymentIds))
			{
				return response()->json([
					'status'	=>	0,
					'message'	=>	'One or more selected Department Receipts are invalid or cancelled.'
				],422);
			}

			$departmentIds	=	$receipts
								->pluck('department_id')
								->unique()
								->values();

			if($departmentIds->count() != 1)
			{
				return response()->json([
					'status'	=>	0,
					'message'	=>	'All selected Department Receipts must belong to the same Department.'
				],422);
			}

			foreach($receipts as $receipt)
			{
				$totalInvoiced	= 	DB::table('finance_department_invoice_payment as dip')
									->join('finance_department_invoice as di','di.department_invoice_id','=','dip.department_invoice_id')
									->where('dip.department_payment_id',$receipt->department_payment_id)
									->whereIn('di.status',['Draft','Posted','Paid'])
									->sum('dip.allocated_amount');

				$balance		= 	round((float)$receipt->gross_received_amount - (float)$totalInvoiced,2);

				if($balance<=0)
				{
					return response()->json([
						'status'	=>	0,
						'message'	=>	'Receipt '.$receipt->receipt_no.' has no available invoice balance.'
					],422);
				}
			}

			$token	=	Crypt::encrypt(implode(',',$paymentIds));

			return response()->json([
				'status'	=>	1,
				'redirect'	=>	route('finance.departmentinvoice.form',$token)
			]);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'status'	=>	0,
				'message'	=>	$e->getMessage()
			],500);
		}
	}


	public function prepareDepartmentInvoiceForm($ids)
	{
		try
		{
			$paymentIds	=	Crypt::decrypt($ids);
			$paymentIds	= 	array_values(array_unique(array_filter(array_map('intval',explode(',',$paymentIds)))));

			if(count($paymentIds)==0)
			{
				return redirect()->route('finance.departmentinvoice.create')->with('error','No Department Receipts selected.');
			}

			$receipts	=	DB::table('finance_department_payment as fp')
							->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
							->leftJoin('department_tbl as dept','dept.departmentid','=','fp.department_id')
							->select(
								'fp.*',
								'dn.demand_note_no',
								'dn.demand_note_date',
								'dn.from_date',
								'dn.to_date',
								'dn.total_amount as demand_note_amount',
								'dept.departmentname',
								'dn.cgst_rate',
								'dn.sgst_rate',
								'dn.igst_rate'								
							)
							->selectSub(function($q){
								$q->from('finance_department_invoice_payment as dip')
									->join(
										'finance_department_invoice as di',
										'di.department_invoice_id',
										'=',
										'dip.department_invoice_id'
									)
									->selectRaw('IFNULL(SUM(dip.allocated_amount),0)')
									->whereColumn('dip.department_payment_id','fp.department_payment_id')
									->whereIn('di.status',['Draft','Posted','Paid']);
							},'total_invoiced')
							->whereIn('fp.department_payment_id',$paymentIds)
							->where('fp.status','Active')
							->get();

			if($receipts->count()!=count($paymentIds))
			{
				return redirect()
						->route('finance.departmentinvoice.create')
						->with('error','One or more selected Department Receipts are invalid or cancelled.');
			}

			$departmentIds	=	$receipts->pluck('department_id')->unique()->values();
			if($departmentIds->count() != 1)
			{
				return redirect()->route('finance.departmentinvoice.create')->with('error','All selected Department Receipts must belong to the same Department.');
			}
			foreach($receipts as $receipt)
			{
				$receipt->invoice_balance	=	round((float)$receipt->gross_received_amount - (float)$receipt->total_invoiced,2);
				if($receipt->invoice_balance <= 0)
				{
					return redirect()
							->route('finance.departmentinvoice.create')
							->with('error','Receipt '.$receipt->receipt_no.' has no available invoice balance.');
				}
			}

			$departmentId	=	$receipts->first()->department_id;
			$department 	= 	DB::table('department_tbl')
								->where('departmentid',$departmentId)
								->first();

			$selectedReceiptBalance	=	round($receipts->sum('invoice_balance'),2);
			$financialYears 		= 	FinanceHelper::financialYears();
			
			$gstRates = $receipts->map(function($receipt)
			{
				$cgst = (float)$receipt->cgst_rate;
				$sgst = (float)$receipt->sgst_rate;
				$igst = (float)$receipt->igst_rate;

				if($igst > 0)
				{
					return 'IGST:'.$igst;
				}

				return 'CGST:'.$cgst.'|SGST:'.$sgst;
			})->unique()->values();			

			if($gstRates->count() > 1)
			{
				return redirect()
					->route('finance.departmentinvoice.create')
					->with('error','Selected receipts have different GST rates/tax treatments and cannot be combined in one invoice.');
			}

			$firstReceipt = $receipts->first();

			$cgstRate	= 	(float)$firstReceipt->cgst_rate;
			$sgstRate	= 	(float)$firstReceipt->sgst_rate;
			$igstRate	=	(float)$firstReceipt->igst_rate;

			$gstRate 	= 	$igstRate > 0 ? $igstRate : $cgstRate + $sgstRate;
			
			$mode		= 	'create';
			
			$from_date	=	date('d\-m\-Y',strtotime($receipts->min('from_date')));
			$to_date 	= 	date('d\-m\-Y',strtotime($receipts->min('to_date')));
			
			return view(
				'finance.departmentinvoice.form',
				compact(
					'mode',
					'receipts',
					'department',
					'departmentId',
					'selectedReceiptBalance',
					'financialYears',
					'cgstRate',
					'sgstRate',
					'igstRate',
					'gstRate',
					'from_date',
					'to_date'
				)
			);
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.departmentinvoice.create')->with('error',$e->getMessage());
		}
	}


	public function storeDepartmentInvoice(Request $request)
	{
		DB::beginTransaction();

		try
		{
			$rules = [
				'department_id'      => 'required|integer',
				'financial_year'     => 'required|string|max:15',
				'invoice_no'         => 'required|string|max:50',
				'invoice_date'       => 'required|date',
				'gstin'              => 'nullable|string|max:20',
				'period_from'        => 'nullable|date',
				'period_to'          => 'nullable|date',
				'particulars'        => 'nullable|string',
				'remarks'            => 'nullable|string',
				'allocation_amount'  => 'required|array',
			];

			$messages = [
				'department_id.required'     => 'Department is required.',
				'department_id.integer'      => 'Invalid department.',

				'financial_year.required'    => 'Financial Year is required.',
				'financial_year.string'      => 'Financial Year must be a valid string.',
				'financial_year.max'         => 'Financial Year may not be greater than 15 characters.',

				'invoice_no.required'        => 'Invoice No. is required.',
				'invoice_no.string'          => 'Invoice No. must be a valid string.',
				'invoice_no.max'             => 'Invoice No. may not be greater than 50 characters.',

				'invoice_date.required'      => 'Invoice Date is required.',
				'invoice_date.date'          => 'Invoice Date must be a valid date.',

				'gstin.string'               => 'GSTIN must be a valid string.',
				'gstin.max'                  => 'GSTIN may not be greater than 20 characters.',

				'period_from.date'           => 'Period From must be a valid date.',
				'period_to.date'             => 'Period To must be a valid date.',

				'particulars.string'         => 'Particulars must be a valid string.',
				'remarks.string'             => 'Remarks must be a valid string.',

				'allocation_amount.required' => 'Allocation Amount is required.',
				'allocation_amount.array'    => 'Allocation Amount must be a valid list.',
			];

			$validatedData = $request->validate($rules, $messages);

			$allocations = $request->allocation_amount;

			$allocations = array_filter($allocations,function($amount)
			{
				return (float)$amount > 0;
			});

			if(count($allocations) == 0)
			{
				throw new \Exception('Please allocate an amount against at least one receipt.');
			}

			$paymentIds	=	array_map('intval',array_keys($allocations));

			$receipts	=	DB::table('finance_department_payment as fp')
							->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
							->whereIn('fp.department_payment_id',$paymentIds)
							->where('fp.status','Active')
							->select(
								'fp.*',
								'dn.cgst_rate',
								'dn.sgst_rate',
								'dn.igst_rate'
							)
							->lockForUpdate()
							->get();

			if($receipts->count() != count($paymentIds))
			{
				throw new \Exception('One or more selected Department Receipts are invalid or cancelled.');
			}

			foreach($receipts as $receipt)
			{
				if((int)$receipt->department_id != (int)$request->department_id)
				{
					throw new \Exception('All selected Department Receipts must belong to the selected Department.');
				}
			}

			$firstReceipt = $receipts->first();

			foreach($receipts as $receipt)
			{
				if(
					(int)$receipt->order_id != (int)$firstReceipt->order_id
				)
				{
					throw new \Exception('All selected Department Receipts must belong to the same Work Order.');
				}

				if(
					(int)$receipt->request_id != (int)$firstReceipt->request_id
				)
				{
					throw new \Exception('All selected Department Receipts must belong to the same EOI Request.');
				}
			}

			$cgstRate = (float)$firstReceipt->cgst_rate;
			$sgstRate = (float)$firstReceipt->sgst_rate;
			$igstRate = (float)$firstReceipt->igst_rate;

			foreach($receipts as $receipt)
			{
				if((float)$receipt->cgst_rate != $cgstRate || (float)$receipt->sgst_rate != $sgstRate || (float)$receipt->igst_rate != $igstRate)
				{
					throw new \Exception('Selected receipts have different GST rates or tax treatment and cannot be combined into one invoice.');
				}
			}

			$invoiceDate	=	date('Y-m-d',strtotime($request->invoice_date));

			$gstRate		= 	$igstRate > 0 ? $igstRate : ($cgstRate + $sgstRate);

			$tax	=	DB::table('finance_tax_master')
						->where('tax_type','GST')
						->where('rate',$gstRate)
						->where('effective_from','<=',$invoiceDate)
						->where(function($q) use ($invoiceDate)
						{
							$q->whereNull('effective_to')
								->orWhere('effective_to','>=',$invoiceDate);
						})
						->where('is_active',1)
						->orderByDesc('effective_from')
						->first();

			if($gstRate > 0 && !$tax)
			{
				throw new \Exception('Applicable GST Tax Master record was not found for the selected GST rates.');
			}

			$taxId	=	$tax ? $tax->tax_id : 0;

			$invoiceNoExists	=	DB::table('finance_department_invoice')
									->where('invoice_no',$request->invoice_no)
									->exists();

			if($invoiceNoExists)
			{
				throw new \Exception('Invoice number already exists.');
			}

			$totalAllocation	= 	0;
			$allocationRows 	= 	[];

			foreach($receipts as $receipt)
			{
				$allocationAmount = round((float)($allocations[$receipt->department_payment_id] ?? 0),2);

				if($allocationAmount <= 0)
				{
					continue;
				}

				$alreadyInvoiced	=	DB::table('finance_department_invoice_payment as dip')
										->join('finance_department_invoice as di','di.department_invoice_id','=','dip.department_invoice_id')
										->where('dip.department_payment_id',$receipt->department_payment_id)
										->whereIn('di.status',['Draft','Posted','Paid'])
										->sum('dip.allocated_amount');

				$availableBalance	=	round((float)$receipt->gross_received_amount - (float)$alreadyInvoiced,2);

				if($availableBalance<0)
				{
					$availableBalance	=	0;
				}

				if($allocationAmount > $availableBalance)
				{
					throw new \Exception(
						'Allocation amount for Receipt '.$receipt->receipt_no.' cannot be greater than available balance of '.number_format($availableBalance,2,'.','').'.'
					);
				}

				$totalAllocation	=	round($totalAllocation + $allocationAmount,2);

				$allocationRows[]	=	[
					'department_payment_id'	=>	$receipt->department_payment_id,
					'allocated_amount'		=>	$allocationAmount
				];
			}

			if($totalAllocation <= 0)
			{
				throw new \Exception('Total invoice allocation must be greater than zero.');
			}

			if($gstRate > 0)
			{
				$taxableAmount = round($totalAllocation / (1 + ($gstRate / 100)),2);

				if($igstRate > 0)
				{
					$igstAmount	= 	round($totalAllocation - $taxableAmount,2);
					$cgstAmount = 	0;
					$sgstAmount = 	0;
				}
				else
				{
					$cgstAmount	=	round($taxableAmount * $cgstRate / 100,2);
					$sgstAmount	= 	round($taxableAmount * $sgstRate / 100,2);
					$igstAmount = 	0;

					$difference = round($totalAllocation - $taxableAmount - $cgstAmount - $sgstAmount,2);

					if($difference != 0)
					{
						$sgstAmount = round($sgstAmount + $difference,2);
					}
				}
			}
			else
			{
				$taxableAmount 	= 	$totalAllocation;
				$cgstAmount 	= 	0;
				$sgstAmount 	= 	0;
				$igstAmount 	= 	0;
			}

			$invoiceData = [
				'request_id'			=>	$firstReceipt->request_id ?: NULL,
				'demand_note_id'		=>	NULL,
				'order_id'				=>	$firstReceipt->order_id ?: NULL,
				'department_payment_id'	=>	NULL,
				'invoice_no'			=>	$request->invoice_no,
				'invoice_date'			=>	$invoiceDate,
				'financial_year'		=>	$request->financial_year,
				'tax_id'				=>	$taxId,
				'gstin'					=>	$request->gstin ?: NULL,
				'period_from'			=>	$request->period_from ? date('Y-m-d',strtotime($request->period_from)) : NULL,
				'period_to'				=>	$request->period_to ? date('Y-m-d',strtotime($request->period_to)) : NULL,
				'particulars'			=>	$request->particulars ?: NULL,
				'taxable_amount'		=>	$taxableAmount,
				'cgst_percent'			=>	$cgstRate,
				'cgst_amount'			=>	$cgstAmount,
				'sgst_percent'			=>	$sgstRate,
				'sgst_amount'			=>	$sgstAmount,
				'igst_percent'			=>	$igstRate,
				'igst_amount'			=>	$igstAmount,
				'invoice_amount'		=>	$totalAllocation,
				'invoice_file'			=>	NULL,
				'remarks'				=>	$request->remarks ?: NULL,
				'created_by'			=>	session('userId'),
				'created_at'			=>	now(),
				'department_id' 		=> 	$request->department_id,
			];

			$departmentInvoiceId	=	DB::table('finance_department_invoice')->insertGetId($invoiceData);

			foreach($allocationRows as $allocation)
			{
				DB::table('finance_department_invoice_payment')
				->insert([
					'department_invoice_id'	=>	$departmentInvoiceId,
					'department_payment_id'	=>	$allocation['department_payment_id'],
					'allocated_amount'		=>	$allocation['allocated_amount'],
					'created_by'			=>	session('userId'),
					'created_at'			=>	now()
				]);
			}

			DB::table('finance_ledger')
			->insert([
				'order_id'				=>	$firstReceipt->order_id ?: 0,
				'request_id'			=>	$firstReceipt->request_id ?: NULL,
				'demand_note_id'		=>	NULL,
				'department_payment_id'	=>	NULL,
				'department_invoice_id'	=>	$departmentInvoiceId,
				'vendor_invoice_id'		=>	NULL,
				'vendor_payment_id'		=>	NULL,
				'reference_type'		=>	'DEPARTMENT_INVOICE',
				'reference_id'			=>	$departmentInvoiceId,
				'transaction_date'		=>	$invoiceDate,
				'financial_year'		=>	$request->financial_year,
				'ledger_type'			=>	'Receipt',
				'dr_amount'				=>	$totalAllocation,
				'cr_amount'				=>	0,
				'remarks'				=>	'Department Invoice',
				'narration'				=>	'Department Invoice '.$request->invoice_no,
				'created_by'			=>	session('userId'),
				'created_at'			=>	now()
			]);

			DB::commit();
			session()->flash('success','Department Invoice created successfully.');
			return response()->json([
				'status'	=>	1,
				'message'	=>	'Department Invoice created successfully.',
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

	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financedepartmentinvoiceindex');

		$departments	=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->where('b.isdepartment',1)
							->orderby('b.name')
							->get();

		$projects		=	DB::table('project_tbl')
							->select('projectid','project_name')
							->orderby('project_name')
							->get();
		
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token',$token);

		return view('finance.departmentinvoice.index', compact('departments','projects','token'));
	}

	public function getDepartmentInvoiceList(Request $request)
	{
		$projectid		=	$request->input('projectid');
		$departmentid	=	$request->input('departmentid');
		$managerid		=	$request->input('managerid');
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

		$data	=	DB::table('finance_department_invoice as di')
					->leftJoin('department_tbl as dept','dept.departmentid','=','di.department_id')
					->leftJoin('finance_tax_master as tm','tm.tax_id','=','di.tax_id')
					->leftJoin('eoi_request as e','e.requestid','=','di.request_id')
					->leftJoin('project_tbl as pn','pn.projectid','=','e.projectid')
					->select(
						'di.*',
						'dept.departmentname',
						'tm.tax_name',
						'pn.project_name'
					)
					->selectSub(function($q){
						$q->from('finance_department_invoice_payment as dip')
							->selectRaw('COUNT(DISTINCT dip.department_payment_id)')
							->whereColumn('dip.department_invoice_id','di.department_invoice_id');
					},'receipt_count')
					->selectSub(function($q){
						$q->from('finance_department_invoice_payment as dip')
							->selectRaw('IFNULL(SUM(dip.allocated_amount),0)')
							->whereColumn('dip.department_invoice_id','di.department_invoice_id');
					},'allocated_amount')
					->when($projectid != 0,function($query) use ($projectid){
						return $query->where('pn.projectid','=',$projectid);
					})
					->when($departmentid != 0,function($query) use ($departmentid){
						return $query->where('di.department_id','=',$departmentid);
					})
					->when($pagesearch != '',function($query) use ($pagesearch){
						return $query->where(function($q) use ($pagesearch){
							$q->where('di.invoice_no','like','%'.$pagesearch.'%')
								->orWhere('di.gstin','like','%'.$pagesearch.'%')
								->orWhere('di.particulars','like','%'.$pagesearch.'%')
								->orWhere('di.remarks','like','%'.$pagesearch.'%')
								->orWhere('dept.departmentname','like','%'.$pagesearch.'%');
						});
					})
					->orderBy('di.department_invoice_id','desc')
					->paginate($pagesize,['*'],'page',$currentPage);
					
		return view('finance/departmentinvoice/ajaxpages/departmentinvoicesTable',['data' => $data]);
	}

	public function view($id)
	{
		try
		{
			$id = Crypt::decrypt($id);

			$invoice = DB::table('finance_department_invoice as di')
						->leftJoin('department_tbl as dept','dept.departmentid','=','di.department_id')
						->leftJoin('finance_tax_master as tm','tm.tax_id','=','di.tax_id')
						->select(
							'di.*',
							'dept.departmentname',
							'tm.tax_name',
							'tm.hsn_sac_code'
						)
						->where('di.department_invoice_id',$id)
						->first();

			if(!$invoice)
			{
				return redirect()
					->route('finance.departmentinvoice.index')
					->with('error','Department Invoice not found.');
			}

			$allocations = DB::table('finance_department_invoice_payment as dip')
							->join('finance_department_payment as fp','fp.department_payment_id','=','dip.department_payment_id')
							->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
							->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','fp.payment_mode_id')
							->select(
								'dip.invoice_payment_id',
								'dip.department_invoice_id',
								'dip.department_payment_id',
								'dip.allocated_amount',
								'fp.receipt_no',
								'fp.receipt_date',
								'fp.gross_received_amount',
								'fp.taxable_amount as receipt_taxable_amount',
								'fp.gst_amount as receipt_gst_amount',
								'fp.net_received_amount',
								'fp.voucher_no',
								'fp.transaction_no',
								'pm.payment_mode',
								'dn.demand_note_no',
								'dn.demand_note_date'
							)
							->where('dip.department_invoice_id',$id)
							->orderBy('fp.department_payment_id')
							->get();

			$totalAllocated = $allocations->sum('allocated_amount');

			$ledger = DB::table('finance_ledger')
						->where('department_invoice_id',$id)
						->where('reference_type','DEPARTMENT_INVOICE')
						->where('reference_id',$id)
						->first();

			return view(
				'finance.departmentinvoice.view',
				compact(
					'invoice',
					'allocations',
					'totalAllocated',
					'ledger'
				)
			);
		}
		catch(\Exception $e)
		{
			return redirect()
				->route('finance.departmentinvoice.index')
				->with('error',$e->getMessage());
		}
	}


	public function edit($id)
	{
		try
		{
			$id 	= 	Crypt::decrypt($id);
			$invoice= 	DB::table('finance_department_invoice as di')
						->leftJoin('department_tbl as dept','dept.departmentid','=','di.department_id')
						->leftJoin('finance_tax_master as tm','tm.tax_id','=','di.tax_id')
						->where('di.department_invoice_id',$id)
						->select(
							'di.*',
							'dept.departmentname',
							'tm.tax_name',
							'tm.hsn_sac_code'
						)
						->first();

			if(!$invoice)
			{
				return redirect()
					->route('finance.departmentinvoice.index')
					->with('error','Department Invoice not found.');
			}

			$allocations = DB::table('finance_department_invoice_payment')
							->where('department_invoice_id',$id)
							->get();

			if($allocations->count() == 0)
			{
				return redirect()
					->route('finance.departmentinvoice.index')
					->with('error','No receipt allocation found against this invoice.');
			}

			$allocationMap = [];

			foreach($allocations as $allocation)
			{
				$allocationMap[$allocation->department_payment_id] = (float)$allocation->allocated_amount;
			}

			$receipts = DB::table('finance_department_payment as fp')
							->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','fp.demand_note_id')
							->where('fp.department_id',$invoice->department_id)
							->where('fp.status','Active')
							->select(
								'fp.*',
								'dn.demand_note_no',
								'dn.demand_note_date',
								'dn.cgst_rate',
								'dn.sgst_rate',
								'dn.igst_rate'
							)
							->orderBy('fp.department_payment_id')
							->get();

			foreach($receipts as $receipt)
			{
				$currentAllocation = (float)($allocationMap[$receipt->department_payment_id] ?? 0);

				$previousInvoiced = DB::table('finance_department_invoice_payment')
										->where('department_payment_id',$receipt->department_payment_id)
										->where('department_invoice_id','!=',$id)
										->sum('allocated_amount');

				$availableBalance = round(
					(float)$receipt->gross_received_amount -
					(float)$previousInvoiced,
					2
				);

				if($availableBalance < 0)
				{
					$availableBalance = 0;
				}

				$receipt->current_allocation = round($currentAllocation,2);
				$receipt->previous_invoiced = round((float)$previousInvoiced,2);
				$receipt->invoice_balance = $availableBalance;
				$receipt->selected_for_invoice = $currentAllocation > 0 ? 1 : 0;

				$receipt->gst_compatible = (
					(float)$receipt->cgst_rate == (float)$invoice->cgst_percent &&
					(float)$receipt->sgst_rate == (float)$invoice->sgst_percent &&
					(float)$receipt->igst_rate == (float)$invoice->igst_percent
				) ? 1 : 0;
			}

			$selectedReceiptBalance = 0;

			foreach($receipts as $receipt)
			{
				if($receipt->selected_for_invoice == 1)
				{
					$selectedReceiptBalance += (float)$receipt->invoice_balance;
				}
			}

			$financialYears = FinanceHelper::financialYears();

			$bankAccounts = DB::table('finance_bank_account')
								->where('is_active',1)
								->orderBy('bank_name')
								->get();

			$paymentModes = DB::table('finance_payment_mode_master')
								->where('is_active',1)
								->orderBy('payment_mode')
								->get();

			$tdsTaxes = DB::table('finance_tax_master')
							->where('tax_type','TDS')
							->where('is_active',1)
							->orderBy('rate')
							->get();

			$gstTdsTaxes = DB::table('finance_tax_master')
								->where('tax_type','GST_TDS')
								->where('is_active',1)
								->orderBy('rate')
								->get();

			$mode = 'edit';

			$record = $invoice;

			$departmentInvoice = $invoice;

			$departmentId = $invoice->department_id;
			$department = DB::table('department_tbl')
							->where('departmentid',$invoice->department_id)
							->first();
			$invoiceAmount = (float)$invoice->invoice_amount;

			return view(
				'finance.departmentinvoice.edit',
				compact(
					'invoice',
					'mode',
					'record',
					'department',
					'departmentId',
					'departmentInvoice',
					'receipts',
					'allocations',
					'allocationMap',
					'selectedReceiptBalance',
					'financialYears',
					'bankAccounts',
					'paymentModes',
					'tdsTaxes',
					'gstTdsTaxes',
					'invoiceAmount'
				)
			);
		}
		catch(\Exception $e)
		{
			return redirect()
				->route('finance.departmentinvoice.index')
				->with('error',$e->getMessage());
		}
	}

	public function updateDepartmentInvoice(Request $request)
	{
		$rules = [
			'department_invoice_id' => 'required|integer',
			'department_id'         => 'required|integer',
			'financial_year'        => 'required|string|max:15',
			'invoice_no'            => 'required|string|max:50',
			'invoice_date'          => 'required|date_format:d-m-Y',
			'gstin'                 => 'nullable|string|max:20',
			'period_from'           => 'nullable|date_format:d-m-Y',
			'period_to'             => 'nullable|date_format:d-m-Y',
			'particulars'           => 'nullable|string',
			'remarks'               => 'nullable|string',
			'allocation_amount'     => 'required|array',
		];

		$messages = [
			'department_invoice_id.required' => 'Department Invoice is required.',
			'department_invoice_id.integer'  => 'Invalid Department Invoice.',

			'department_id.required'         => 'Department is required.',
			'department_id.integer'          => 'Invalid Department.',

			'financial_year.required'        => 'Financial Year is required.',
			'financial_year.string'          => 'Financial Year must be a valid string.',
			'financial_year.max'             => 'Financial Year may not be greater than 15 characters.',

			'invoice_no.required'            => 'Invoice Number is required.',
			'invoice_no.string'              => 'Invoice Number must be a valid string.',
			'invoice_no.max'                 => 'Invoice Number may not be greater than 50 characters.',

			'invoice_date.required'          => 'Invoice Date is required.',
			'invoice_date.date_format'       => 'Invoice Date must be in the format DD-MM-YYYY.',

			'gstin.string'                   => 'GSTIN must be a valid string.',
			'gstin.max'                      => 'GSTIN may not be greater than 20 characters.',

			'period_from.date_format'        => 'Period From must be in the format DD-MM-YYYY.',
			'period_to.date_format'          => 'Period To must be in the format DD-MM-YYYY.',

			'particulars.string'             => 'Particulars must be a valid string.',
			'remarks.string'                 => 'Remarks must be a valid string.',

			'allocation_amount.required'     => 'Allocation Amount is required.',
			'allocation_amount.array'        => 'Allocation Amount must be a valid list.',
		];

		$validatedData = $request->validate($rules, $messages);

		DB::beginTransaction();

		try
		{
			$invoiceId 		= 	(int)$request->department_invoice_id;
			$departmentId 	= 	(int)$request->department_id;

			$invoiceDate	=	Carbon::createFromFormat('d-m-Y',$request->invoice_date)->format('Y-m-d');

			$periodFrom	=	!empty($request->period_from) ? Carbon::createFromFormat('d-m-Y',$request->period_from)->format('Y-m-d') : null;

			$periodTo = !empty($request->period_to) ? Carbon::createFromFormat('d-m-Y',$request->period_to)->format('Y-m-d') : null;

			$invoice	= 	DB::table('finance_department_invoice')
							->where('department_invoice_id',$invoiceId)
							->lockForUpdate()
							->first();

			if(!$invoice)
			{
				throw new \Exception('Department Invoice not found.');
			}

			if(isset($invoice->status))
			{
				if(in_array($invoice->status,['Posted','Paid','Cancelled']))
				{
					throw new \Exception(
						'This invoice cannot be edited because its current status is '.$invoice->status.'.'
					);
				}
			}

			if((int)$invoice->department_id != $departmentId)
			{
				throw new \Exception(
					'Invalid Department selected for this invoice.'
				);
			}

			$duplicateInvoice	=	DB::table('finance_department_invoice')
									->where('invoice_no',$request->invoice_no)
									->where('department_invoice_id','!=',$invoiceId)
									->exists();

			if($duplicateInvoice)
			{
				throw new \Exception('Invoice number already exists.');
			}

			$oldAllocations	=	DB::table('finance_department_invoice_payment')
								->where('department_invoice_id',$invoiceId)
								->get();

			$oldAllocationMap = [];

			foreach($oldAllocations as $oldAllocation)
			{
				$oldAllocationMap[$oldAllocation->department_payment_id] = (float)$oldAllocation->allocated_amount;
			}

			$allocationInput	= 	$request->allocation_amount;
			$selectedAllocations= 	[];

			foreach($allocationInput as $paymentId => $amount)
			{
				$amount = round((float)$amount,2);
				if($amount <= 0)
				{
					continue;
				}
				$selectedAllocations[(int)$paymentId] = $amount;
			}

			if(count($selectedAllocations) == 0)
			{
				throw new \Exception('Please allocate amount to at least one receipt.');
			}

			$paymentIds	= 	array_keys($selectedAllocations);

			$receipts 	= 	DB::table('finance_department_payment as fp')
							->leftJoin(
								'finance_demand_note as dn',
								'dn.demand_note_id',
								'=',
								'fp.demand_note_id'
							)
							->whereIn(
								'fp.department_payment_id',
								$paymentIds
							)
							->where('fp.status','Active')
							->select(
								'fp.*',
								'dn.cgst_rate',
								'dn.sgst_rate',
								'dn.igst_rate'
							)
							->lockForUpdate()
							->get()
							->keyBy('department_payment_id');


			if($receipts->count() != count($paymentIds))
			{
				throw new \Exception('One or more selected Department Receipts are invalid or cancelled.');
			}

			foreach($receipts as $receipt)
			{
				if((int)$receipt->department_id != $departmentId)
				{
					throw new \Exception('All selected receipts must belong to the same Department.');
				}
			}

			$cgstRate	= 	null;
			$sgstRate 	= 	null;
			$igstRate 	= 	null;

			foreach($receipts as $receipt)
			{
				$receiptCgst = (float)$receipt->cgst_rate;
				$receiptSgst = (float)$receipt->sgst_rate;
				$receiptIgst = (float)$receipt->igst_rate;

				if($cgstRate === null)
				{
					$cgstRate = $receiptCgst;
					$sgstRate = $receiptSgst;
					$igstRate = $receiptIgst;
				}
				else
				{
					if($receiptCgst != $cgstRate || $receiptSgst != $sgstRate || $receiptIgst != $igstRate)
					{
						throw new \Exception('Selected receipts have different GST rates. Please select receipts with the same GST treatment.');
					}
				}
			}

			$totalAllocation = 0;

			foreach($selectedAllocations as $paymentId => $allocationAmount)
			{
				$receipt	=	$receipts[$paymentId];

				$previousInvoiced	=	DB::table('finance_department_invoice_payment as dip')
										->join('finance_department_invoice as di','di.department_invoice_id','=','dip.department_invoice_id')
										->where('dip.department_payment_id',$paymentId)
										->where('dip.department_invoice_id','!=',$invoiceId)
										->whereIn('di.status',['Draft','Posted','Paid'])
										->sum('dip.allocated_amount');
										
				$availableBalance = round((float)$receipt->gross_received_amount - (float)$previousInvoiced,2);

				if($availableBalance < 0)
				{
					$availableBalance = 0;
				}

				if($allocationAmount > $availableBalance)
				{
					throw new \Exception('Allocation amount for Receipt '.$receipt->receipt_no.' cannot be greater than its available balance of '.
						number_format($availableBalance,2,'.',''));
				}
				$totalAllocation += $allocationAmount;
			}

			$totalAllocation = round($totalAllocation,2);
			if($totalAllocation <= 0)
			{
				throw new \Exception('Total allocation must be greater than zero.');
			}

			$gstRate = $igstRate > 0 ? $igstRate : ($cgstRate + $sgstRate);

			if($gstRate > 0)
			{
				$taxableAmount = round($totalAllocation / (1 + ($gstRate / 100)),2);

				if($igstRate > 0)
				{
					$igstAmount	=	round($totalAllocation - $taxableAmount,2);
					$cgstAmount = 	0;
					$sgstAmount = 	0;
				}
				else
				{
					$cgstAmount	= 	round($taxableAmount * ($cgstRate / 100),2);
					$sgstAmount	= 	round($taxableAmount * ($sgstRate / 100),2);
					$igstAmount = 	0;
				}
			}
			else
			{
				$taxableAmount 	= 	$totalAllocation;
				$cgstAmount 	= 	0;
				$sgstAmount 	= 	0;
				$igstAmount 	= 	0;
			}

			$invoiceAmount = $totalAllocation;


			$tax	= 	DB::table('finance_tax_master')
						->where('tax_type','GST')
						->where('rate',$gstRate)
						->where(
							'effective_from',
							'<=',
							$invoiceDate
						)
						->where(function($q) use ($invoiceDate)
						{
							$q->whereNull('effective_to')
								->orWhere(
									'effective_to',
									'>=',
									$invoiceDate
								);
						})
						->where('is_active',1)
						->orderByDesc('effective_from')
						->first();

			if(!$tax)
			{
				throw new \Exception(
					'Applicable GST Tax Master record was not found for the selected GST rates.'
				);
			}


			DB::table('finance_department_invoice')
			->where('department_invoice_id',$invoiceId)
			->update([
				'department_id'		=>	$departmentId,
				'financial_year'	=>	$request->financial_year,
				'invoice_no'		=>	$request->invoice_no,
				'invoice_date'		=>	$invoiceDate,
				'tax_id'			=>	$tax->tax_id,
				'gstin'				=>	$request->gstin,
				'period_from'		=>	$periodFrom,
				'period_to'			=>	$periodTo,
				'particulars'		=>	$request->particulars,
				'taxable_amount'	=>	$taxableAmount,
				'cgst_percent'		=>	$cgstRate,
				'cgst_amount'		=>	$cgstAmount,
				'sgst_percent'		=>	$sgstRate,
				'sgst_amount'		=>	$sgstAmount,
				'igst_percent'		=>	$igstRate,
				'igst_amount'		=>	$igstAmount,
				'invoice_amount'	=>	$invoiceAmount,
				'remarks'			=>	$request->remarks,
				'updated_at'		=>	now()
			]);

			DB::table('finance_department_invoice_payment')
			->where('department_invoice_id',$invoiceId)
			->delete();


			foreach($selectedAllocations as $paymentId => $allocationAmount)
			{
				DB::table('finance_department_invoice_payment')
				->insert([
					'department_invoice_id'	=>	$invoiceId,
					'department_payment_id'	=>	$paymentId,
					'allocated_amount'		=>	$allocationAmount,
					'created_by'			=>	auth()->id() ?? 0,
					'created_at'			=>	now()
				]);
			}

			$ledger	=	DB::table('finance_ledger')
						->where('reference_type','DEPARTMENT_INVOICE')
						->where('reference_id',$invoiceId)
						->lockForUpdate()
						->first();

			if($ledger)
			{
				DB::table('finance_ledger')
				->where('ledger_id',$ledger->ledger_id)
				->update([
					'order_id'				=>	$invoice->order_id,
					'request_id'			=>	$invoice->request_id,
					'demand_note_id'		=>	null,
					'department_payment_id'	=>	null,
					'department_invoice_id'	=>	$invoiceId,
					'reference_type'		=>	'DEPARTMENT_INVOICE',
					'reference_id'			=>	$invoiceId,
					'transaction_date'		=>	$invoiceDate,
					'financial_year'		=>	$request->financial_year,
					'ledger_type'			=>	'Receipt',
					'dr_amount'				=>	$invoiceAmount,
					'cr_amount'				=>	0,
					'remarks'				=>	'Department Invoice updated',
					'narration'				=>	'Department Invoice '.$request->invoice_no.' updated against receipt allocation.',
					'created_by'			=>	auth()->id() ?? 0
				]);
			}
			else
			{
				DB::table('finance_ledger')
				->insert([
					'order_id'				=>	$invoice->order_id,
					'request_id'			=>	$invoice->request_id,
					'demand_note_id'		=>	null,
					'department_payment_id'=>	null,
					'department_invoice_id'=>	$invoiceId,
					'reference_type'		=>	'DEPARTMENT_INVOICE',
					'reference_id'			=>	$invoiceId,
					'transaction_date'		=>	$invoiceDate,
					'financial_year'		=>	$request->financial_year,
					'ledger_type'			=>	'Receipt',
					'dr_amount'				=>	$invoiceAmount,
					'cr_amount'				=>	0,
					'remarks'				=>	'Department Invoice ledger created during update',
					'narration'				=>	'Department Invoice '.$request->invoice_no.' ledger entry created.',
					'created_by'			=>	auth()->id() ?? 0
				]);
			}

			$oldInvoice	=	(array)$invoice;

			$newInvoice = [
				'department_id'		=>	$departmentId,
				'financial_year'	=>	$request->financial_year,
				'invoice_no'		=>	$request->invoice_no,
				'invoice_date'		=>	$invoiceDate,
				'tax_id'			=>	$tax->tax_id,
				'gstin'				=>	$request->gstin,
				'period_from'		=>	$periodFrom,
				'period_to'			=>	$periodTo,
				'particulars'		=>	$request->particulars,
				'taxable_amount'	=>	$taxableAmount,
				'cgst_percent'		=>	$cgstRate,
				'cgst_amount'		=>	$cgstAmount,
				'sgst_percent'		=>	$sgstRate,
				'sgst_amount'		=>	$sgstAmount,
				'igst_percent'		=>	$igstRate,
				'igst_amount'		=>	$igstAmount,
				'invoice_amount'	=>	$invoiceAmount,
				'remarks'			=>	$request->remarks
			];

			foreach($newInvoice as $field => $newValue)
			{
				$oldValue = $oldInvoice[$field] ?? null;

				if((string)$oldValue !== (string)$newValue)
				{
					DB::table('finance_activity_log')
					->insert([
						'module_name'	=>	'DEPARTMENT_INVOICE',
						'record_id'		=>	$invoiceId,
						'action'		=>	'UPDATE',
						'field_name'	=>	$field,
						'old_value'		=>	$oldValue,
						'new_value'		=>	$newValue,
						'remarks'		=>	'Department Invoice updated.',
						'action_by'		=>	auth()->id() ?? 0,
						'action_date'	=>	now(),
						'ip_address'	=>	$request->ip()
					]);
				}
			}

			$allPaymentIds = array_unique(array_merge(
				array_keys($oldAllocationMap),
				array_keys($selectedAllocations)
			));

			foreach($allPaymentIds as $paymentId)
			{
				$oldAmount = (float)($oldAllocationMap[$paymentId] ?? 0);

				$newAmount = (float)($selectedAllocations[$paymentId] ?? 0);

				if(round($oldAmount,2) !=round($newAmount,2))
				{
					DB::table('finance_activity_log')
					->insert([
						'module_name'	=>	'DEPARTMENT_INVOICE',
						'record_id'		=>	$invoiceId,
						'action'		=>	'UPDATE',
						'field_name'	=>	'receipt_allocation_'.$paymentId,
						'old_value'		=>	number_format($oldAmount,2,'.',''),
						'new_value'		=>	number_format($newAmount,2,'.',''),
						'remarks'		=>	'Department receipt allocation changed.',
						'action_by'		=>	auth()->id() ?? 0,
						'action_date'	=>	now(),
						'ip_address'	=>	$request->ip()
					]);
				}
			}
			DB::commit();
			
			session()->flash('success','Department Invoice updated successfully.');
			return response()->json([
				'status'	=>	1,
				'message'	=>	'Department Invoice updated successfully.',
				'redirect'	=>	route('finance.departmentinvoice.index')
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();

			return response()->json([
				'status'	=>	0,
				'message'	=>	$e->getMessage()
			],422);
		}
	}


	public function post(Request $request)
	{
		$validator = Validator::make($request->all(),[
			'department_invoice_id' => 'required|integer'
		]);

		if($validator->fails())
		{
			return response()->json([
				'status' => 0,
				'message' => 'Invalid Department Invoice.'
			],422);
		}

		DB::beginTransaction();

		try
		{
			$invoiceId	= 	(int)$request->department_invoice_id;

			$invoice 	= 	DB::table('finance_department_invoice')
							->where('department_invoice_id',$invoiceId)
							->lockForUpdate()
							->first();

			if(!$invoice)
			{
				throw new \Exception('Department Invoice not found.');
			}

			if($invoice->status != 'Draft')
			{
				throw new \Exception('Only Draft invoices can be posted.');
			}

			$totalAllocated	=	DB::table('finance_department_invoice_payment')
								->where('department_invoice_id',$invoiceId)
								->sum('allocated_amount');

			$totalAllocated = round((float)$totalAllocated,2);

			if($totalAllocated <= 0)
			{
				throw new \Exception('Invoice cannot be posted because no receipt allocation exists.');
			}

			if(round((float)$invoice->invoice_amount,2) != $totalAllocated)
			{
				throw new \Exception('Invoice amount does not match the total receipt allocation.');
			}

			$userId	=	session('userId');
			
			
			DB::table('finance_department_invoice')
			->where('department_invoice_id',$invoiceId)
			->update([
				'status'	=>	'Posted',
				'posted_by'	=>	$userId,
				'posted_at'	=>	now(),
				'updated_at'=>	now()
			]);

			DB::table('finance_activity_log')
			->insert([
				'module_name'	=>	'DEPARTMENT_INVOICE',
				'record_id'		=>	$invoiceId,
				'action'		=>	'POST',
				'field_name'	=>	'status',
				'old_value'		=>	'Draft',
				'new_value'		=>	'Posted',
				'remarks'		=>	'Department Invoice posted.',
				'action_by'		=>	$userId,
				'action_date'	=>	now(),
				'ip_address'	=>	$request->ip()
			]);
			

			DB::commit();
			session()->flash('success','Department Invoice posted successfully.');
			return response()->json([
				'status'	=>	1,
				'message'	=>	'Department Invoice posted successfully.',
				'redirect'	=>	route(
					'finance.departmentinvoice.view',
					Crypt::encrypt($invoiceId)
				)
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();

			return response()->json([
				'status'	=>	0,
				'message'	=>	$e->getMessage()
			],422);
		}
	}	

	public function cancelDepartmentInvoice(Request $request)
	{
		$rules = [
			'department_invoice_id'	=>	'required|integer',
			'cancellation_remarks'	=>	'required|string|max:1000'
		];

		$messages = [
			'department_invoice_id.required'	=>	'Department Invoice is required.',
			'department_invoice_id.integer'		=>	'Invalid Department Invoice.',
			'cancellation_remarks.required'		=>	'Cancellation remarks are required.',
			'cancellation_remarks.string'		=>	'Cancellation remarks must be a valid string.',
			'cancellation_remarks.max'			=>	'Cancellation remarks cannot exceed 1000 characters.'
		];

		$validatedData = $request->validate($rules,$messages);

		DB::beginTransaction();

		try
		{
			$invoiceId	=	(int)$request->department_invoice_id;
			$userId		=	session('userId') ?: (auth()->id() ?? 0);

			$invoice	=	DB::table('finance_department_invoice')
							->where('department_invoice_id',$invoiceId)
							->lockForUpdate()
							->first();

			if(!$invoice)
			{
				throw new \Exception('Department Invoice not found.');
			}

			if($invoice->status != 'Posted')
			{
				throw new \Exception('Only Posted Department Invoices can be cancelled. Current status is '.$invoice->status.'.');
			}

			$ledger	=	DB::table('finance_ledger')
						->where('reference_type','DEPARTMENT_INVOICE')
						->where('reference_id',$invoiceId)
						->where('ledger_type','Receipt')
						->lockForUpdate()
						->first();

			if(!$ledger)
			{
				throw new \Exception('Original Department Invoice ledger entry was not found.');
			}

			$ledgerAmount	=	round((float)$ledger->dr_amount - (float)$ledger->cr_amount,2);

			if($ledgerAmount <= 0)
			{
				throw new \Exception('Invalid Department Invoice ledger amount. Cancellation cannot be processed.');
			}

			$existingReversal	=	DB::table('finance_ledger')
									->where('reference_type','DEPARTMENT_INVOICE')
									->where('reference_id',$invoiceId)
									->where('ledger_type','Journal')
									->where('cr_amount',$ledgerAmount)
									->exists();

			if($existingReversal)
			{
				throw new \Exception('Cancellation reversal ledger entry already exists for this invoice.');
			}

			DB::table('finance_ledger')
			->insert([
				'order_id'				=>	$invoice->order_id ?: 0,
				'request_id'			=>	$invoice->request_id ?: NULL,
				'demand_note_id'		=>	NULL,
				'department_payment_id'	=>	NULL,
				'department_invoice_id'	=>	$invoiceId,
				'vendor_invoice_id'		=>	NULL,
				'vendor_payment_id'		=>	NULL,
				'reference_type'		=>	'DEPARTMENT_INVOICE',
				'reference_id'			=>	$invoiceId,
				'transaction_date'		=>	date('Y-m-d'),
				'financial_year'		=>	$invoice->financial_year,
				'ledger_type'			=>	'Journal',
				'dr_amount'				=>	0,
				'cr_amount'				=>	$ledgerAmount,
				'remarks'				=>	'Department Invoice cancellation reversal',
				'narration'				=>	'Cancellation reversal for Department Invoice '.$invoice->invoice_no.'. Reason: '.$request->cancellation_remarks,
				'created_by'			=>	$userId,
				'created_at'			=>	now()
			]);

			DB::table('finance_department_invoice')
			->where('department_invoice_id',$invoiceId)
			->update([
				'status'				=>	'Cancelled',
				'cancelled_by'			=>	$userId,
				'cancelled_at'			=>	now(),
				'cancellation_remarks'	=>	$request->cancellation_remarks,
				'updated_at'			=>	now()
			]);

			DB::table('finance_activity_log')
			->insert([
				'transaction_uuid'		=>	(string)Str::uuid(),
				'module_name'			=>	'DEPARTMENT_INVOICE',
				'record_id'				=>	$invoiceId,
				'action'				=>	'CANCEL',
				'action_description'	=>	'Department Invoice cancelled.',
				'field_name'			=>	'status',
				'changed_fields'		=>	1,
				'old_value'				=>	'Posted',
				'new_value'				=>	'Cancelled',
				'remarks'				=>	$request->cancellation_remarks,
				'action_by'				=>	$userId,
				'action_date'			=>	now(),
				'ip_address'			=>	$request->ip()
			]);

			DB::commit();

			session()->flash('success','Department Invoice cancelled successfully.');

			return response()->json([
				'status'	=>	1,
				'message'	=>	'Department Invoice cancelled successfully.',
				'redirect'	=>	route(
					'finance.departmentinvoice.view',
					Crypt::encrypt($invoiceId)
				)
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();

			return response()->json([
				'status'	=>	0,
				'message'	=>	$e->getMessage()
			],422);
		}
	}

}
