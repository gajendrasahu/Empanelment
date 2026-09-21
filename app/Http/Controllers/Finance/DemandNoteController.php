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
use App\Services\OrderValueService;
use Barryvdh\DomPDF\Facade\Pdf;
class DemandNoteController extends Controller
{
	protected $valueService;
	public function __construct(OrderValueService $valueService)
	{
		$this->valueService = $valueService;
	}

	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financedemandnoteindex');

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

		return view('finance.demandnote.index', compact('departments','managers','projects','token'));
	}
	
	public function getEoIList(Request $request)
	{
		$projectid		=	$request->input('projectid');
		$departmentid	=	$request->input('departmentid');
		$managerid		=	$request->input('managerid');
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

		
		$data	=	DB::table('eoi_request as e')
						->leftJoin('project_tbl as p','p.projectid','=','e.projectid')
						->Join('department_tbl as d','d.userid','=','e.userid')
						->Join('users_tbl as pm','pm.userid','=','e.userid')
						->select(
							'e.requestid',
							'e.eoinumber',
							'e.releasedate',
							'p.project_name',
							'pm.name as name',
						)
						->selectSub(function ($q) {

							$q->from('finance_demand_note')
								->selectRaw('COUNT(*)')
								->whereColumn('request_id', 'e.requestid');

						}, 'demand_note_count')
						->selectSub(function ($q) {

							$q->from('finance_demand_note')
								->selectRaw('IFNULL(SUM(total_amount),0)')
								->whereColumn('request_id', 'e.requestid');

						}, 'total_demand_note_value')			
						->selectSub(function ($q) {

							$q->from('finance_department_payment')
								->selectRaw('IFNULL(SUM(net_received_amount),0)')
								->whereColumn('request_id', 'e.requestid');

						}, 'total_received_amount')						
						->where('e.isClosed',0)
						->where('e.iscancelled',0)
						->where('e.categoryid',2)
						->where('pm.ispm',0)
						->when($projectid!=0,function($query) use ($projectid){
							return $query->where('e.projectid','=',$projectid);
						})
						->when($departmentid!=0,function($query) use ($departmentid){
							return $query->where('e.userid','=',$departmentid);
						})
						->when($managerid!=0,function($query) use ($managerid){
							return $query->where('e.userid','=',$managerid);
						})
						->when($pagesearch != '', function($query) use ($pagesearch) {
							return $query->where(function($q) use ($pagesearch) {
								$q->where('e.eoinumber', 'like', '%' . $pagesearch . '%')
								  ->orWhere('e.projecttitle', 'like', '%' . $pagesearch . '%');
							})->where('e.iscancelled', 0);
						})
						->orderBy('e.requestid','DESC')
						->paginate($pagesize,['*'],'page',$currentPage);
						
		return view('finance/demandnote/ajaxpages/financeeoiTable',['data' => $data]);
	}

	public function create($requestid)
	{
		$requestid	=	Crypt::decrypt($requestid);
		
		$record		=	DB::table('eoi_request as e')
						->leftJoin('project_tbl as p','p.projectid','=','e.projectid')
						->leftJoin('department_tbl as d','d.userid','=','e.userid')
						->select(
							'e.*',
							'p.project_name',
							'd.departmentid',
							'd.departmentname',
						)
						->where('e.requestid', $requestid)
						->first();
		
		$demandNoteNo 	= 	FinanceHelper::generateDocumentNumber('Demand Note');
		$financialYears = 	FinanceHelper::financialYears();

		$gstTaxes	=	DB::table('finance_tax_master')
						->where('tax_type','GST')
						->where('is_active', 1)
						->whereDate('effective_from', '<=', date('Y-m-d'))
						->where(function($q){
							$q->whereNull('effective_to')
							  ->orWhere('effective_to', '>=', date('Y-m-d'));
						})
						->orderBy('rate')
						->get();

		$categoryid	=	DB::table('eoi_request')->where('requestid',$record->requestid)->value('categoryid');
		
		$adminChargePercentage	=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('isActive',1)->value('admincharge');

		$record->adminChargePercentage	=	$adminChargePercentage ?? 0;
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token',$token);
		
		$mode	=	'create';
		
		return view('finance.demandnote.create', compact('mode','record','token','demandNoteNo','financialYears','gstTaxes'));
	}


	public function store(Request $request)
	{
		if($request->demand_note_date)
		{
			$request->merge([
				'demand_note_date' => Carbon::parse($request->demand_note_date)->format('Y-m-d'),
			]);
		}

		if($request->from_date)
		{
			$request->merge([
				'from_date' => Carbon::parse($request->from_date)->format('Y-m-d'),
			]);
		}

		if($request->to_date)
		{
			$request->merge([
				'to_date' => Carbon::parse($request->to_date)->format('Y-m-d'),
			]);
		}

		$rules = [
			'request_id'                 => 'nullable|integer',
			'order_id'                   => 'nullable|integer',
			'vendor_id'                  => 'nullable|integer',
			'department_id'              => 'required|integer',
			'project_id'                 => 'nullable|integer',
			'financial_year'             => 'required',
			'reference_type'             => 'required',
			'demand_note_date'           => 'required|date|before_or_equal:today',
			'from_date'                  => 'nullable|date',
			'to_date'                    => 'nullable|date|after:from_date',

			'vendor_funding_particular'  => 'required',
			'service_charge_particular'  => 'required',

			'advance_amount'              => 'required|numeric|min:0.01',
			'advance_gst_tax_id'          => 'required|integer',

			'service_charge_gst_tax_id'   => 'required|integer',

			'remarks'                    => 'nullable|max:1000',
			'attachment'                 => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120'
		];

		$messages = [
			'department_id.required'             => 'Department is required.',
			'financial_year.required'            => 'Financial year is required.',
			'reference_type.required'            => 'Reference type is required.',
			'demand_note_date.required'          => 'Demand Note date is required.',
			'demand_note_date.date'              => 'Please enter a valid Demand Note date.',

			'vendor_funding_particular.required' => 'Vendor Payment Advance Particular is required.',
			'service_charge_particular.required' => 'CHiPS Service Charge Particular is required.',

			'advance_amount.required'            => 'Advance amount is required.',
			'advance_amount.numeric'             => 'Advance amount must be numeric.',
			'advance_amount.min'                 => 'Advance amount should be greater than zero.',

			'advance_gst_tax_id.required'        => 'GST for Vendor Payment Advance is required.',
			'advance_gst_tax_id.integer'         => 'Invalid GST selected for Vendor Payment Advance.',

			'service_charge_gst_tax_id.required' => 'GST for CHiPS Service Charge is required.',
			'service_charge_gst_tax_id.integer'  => 'Invalid GST selected for CHiPS Service Charge.',

			'remarks.max'                        => 'Remarks cannot exceed 1000 characters.',
			'attachment.mimes'                   => 'Attachment must be PDF, JPG, JPEG or PNG.',
			'attachment.max'                    => 'Attachment size should not exceed 5 MB.',
			'from_date.date'                    => 'Please enter a valid from date.',
			'to_date.date'                      => 'Please enter a valid to date.'
		];

		$validatedData = $request->validate($rules,$messages);

		if(empty($request->request_id) && empty($request->order_id))
		{
			return response()->json([
				'status'  => 0,
				'message' => 'Either EOI or Work Order must be selected.'
			],422);
		}

		DB::beginTransaction();

		$attachment = NULL;

		try
		{
			$eoi	=	DB::table('eoi_request')->select('requestid','categoryid')->where('requestid',$request->request_id)->first();
			
			$demandNoteGroupUuid		= 	(string)\Illuminate\Support\Str::uuid();
			$advanceAmount 				= 	round((float)$request->advance_amount,2);
			
			$advanceTax 				= 	FinanceHelper::calculateGST($advanceAmount,$request->advance_gst_tax_id);
			$advanceAmountInWords 		= 	FinanceHelper::amountInWords(round($advanceTax['grand_total']));

			$adminChargePercentage		=	DB::table('pricing_tbl')->where('categoryid',$eoi->categoryid)->value('admincharge');

			$serviceChargeAmount		= 	round($advanceAmount*$adminChargePercentage/100,2);
			$serviceChargeTax			=	FinanceHelper::calculateGST($serviceChargeAmount,$request->service_charge_gst_tax_id);
			$serviceChargeAmountInWords = 	FinanceHelper::amountInWords(round($serviceChargeTax['grand_total']));

			if($request->hasFile('attachment'))
			{
				$file 					= 	$request->file('attachment');
				$attachment 			= 	$file->store('uploads/demand_note','public');
			}

			$vendorFundingDemandNoteNo	=	FinanceHelper::generateDocumentNumber('Demand Note');

			$vendorFundingDemandNoteId =
				DB::table('finance_demand_note')->insertGetId([
					'reference_type'       		=> $request->reference_type,
					'demand_note_type'     		=> 'VENDOR_FUNDING',
					'demand_note_group_uuid' 	=> $demandNoteGroupUuid,

					'request_id'           		=> $request->request_id ?: NULL,
					'order_id'             		=> $request->order_id ?: NULL,

					'project_type'         		=> 'CSF',
					'department_id'       		=> $request->department_id,
					'project_id'           		=> $request->project_id ?: NULL,
					'vendor_id'            		=> $request->vendor_id ?: NULL,

					'demand_note_no'       		=> $vendorFundingDemandNoteNo,
					'demand_note_date'     		=> $request->demand_note_date,
					'financial_year'       		=> $request->financial_year,

					'tax_id'               		=> $request->advance_gst_tax_id,
					'gstin'                		=> $request->gstin ?? NULL,

					'from_date'            		=> $request->from_date ?? NULL,
					'to_date'              		=> $request->to_date ?? NULL,

					'particular'           		=> $request->vendor_funding_particular,

					'taxable_amount'       		=> $advanceAmount,

					'cgst_rate'            		=> $advanceTax['cgst_rate'],
					'cgst_amount'          		=> $advanceTax['cgst_amount'],

					'sgst_rate'            		=> $advanceTax['sgst_rate'],
					'sgst_amount'          		=> $advanceTax['sgst_amount'],

					'igst_rate'            		=> $advanceTax['igst_rate'],
					'igst_amount'          		=> $advanceTax['igst_amount'],

					'total_amount'         		=> round($advanceTax['grand_total']),
					'amount_in_words'      		=> $advanceAmountInWords,

					'payment_status'       		=> 'Pending',
					'status'               		=> 'Active',

					'remarks'              		=> $request->remarks,
					'attachment'          		=> $attachment,

					'created_by'           		=> session('userId'),
					'created_at'           		=> now()
				]);


			$serviceChargeDemandNoteNo	=	FinanceHelper::generateDocumentNumber('Demand Note');

			$serviceChargeDemandNoteId =
				DB::table('finance_demand_note')->insertGetId([
					'reference_type'       	=> 	$request->reference_type,
					'demand_note_type'     	=> 	'SERVICE_CHARGE',
					'demand_note_group_uuid'=> 	$demandNoteGroupUuid,

					'request_id'           	=> 	$request->request_id ?: NULL,
					'order_id'             	=> 	$request->order_id ?: NULL,

					'project_type'         	=> 	'CSF',
					'department_id'       	=> 	$request->department_id,
					'project_id'           	=> 	$request->project_id ?: NULL,
					'vendor_id'            	=> 	$request->vendor_id ?: NULL,

					'demand_note_no'       	=> 	$serviceChargeDemandNoteNo,
					'demand_note_date'     	=> 	$request->demand_note_date,
					'financial_year'       	=> 	$request->financial_year,

					'tax_id'               	=> 	$request->service_charge_gst_tax_id,
					'gstin'                	=> 	$request->gstin ?? NULL,

					'from_date'            	=> 	$request->from_date ?? NULL,
					'to_date'              	=> 	$request->to_date ?? NULL,

					'particular'           	=> 	$request->service_charge_particular,

					'taxable_amount'       	=> 	$serviceChargeAmount,

					'cgst_rate'            	=> 	$serviceChargeTax['cgst_rate'],
					'cgst_amount'          	=> 	$serviceChargeTax['cgst_amount'],

					'sgst_rate'            	=> 	$serviceChargeTax['sgst_rate'],
					'sgst_amount'          	=> 	$serviceChargeTax['sgst_amount'],

					'igst_rate'            	=> 	$serviceChargeTax['igst_rate'],
					'igst_amount'          	=> 	$serviceChargeTax['igst_amount'],

					'total_amount'         	=> 	round($serviceChargeTax['grand_total']),
					'amount_in_words'      	=> 	$serviceChargeAmountInWords,

					'payment_status'       	=> 	'Pending',
					'status'               	=> 	'Active',

					'remarks'              	=> 	$request->remarks,
					'attachment'          	=> 	$attachment,

					'created_by'           	=> 	session('userId'),
					'created_at'           	=>	now()
				]);


			DB::commit();

			session()->flash('success','Both Demand Notes created successfully.');

			return response()->json([
				'status'	=> 	1,
				'message'  	=> 	'Both Demand Notes created successfully.',
				'redirect' 	=> 	route('finance.demandnote.index'),
				'id'       	=> 	$vendorFundingDemandNoteId,
				'group_uuid'=> 	$demandNoteGroupUuid
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
				'status'  => 0,
				'message' => $e->getMessage()
			],500);
		}
	}

	public function getDemandNoteList(Request $request)
	{
		$projectid		=	$request->input('projectid');
		$departmentid	=	$request->input('departmentid') ?? 0;
		$managerid		=	$request->input('managerid');
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

		$data	=	DB::table('finance_demand_note as dn')
							->leftJoin('department_tbl as d','d.departmentid','=','dn.department_id')
							->leftJoin('project_tbl as p','p.projectid','=','dn.project_id')
							->select(
								'dn.*',
								'd.departmentname',
								'p.project_name',
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
		
						
		return view('finance/demandnote/ajaxpages/demandnoteTable',['data' => $data]);
	}

	public function edit($id)
	{
		$id = Crypt::decrypt($id);

		$selectedRecord	=	DB::table('finance_demand_note')
							->where('demand_note_id',$id)
							->where('status','Active')
							->first();

		if(!$selectedRecord)
		{
			abort(404);
		}

		$demandNoteGroupUuid = $selectedRecord->demand_note_group_uuid;
		
		$categoryid	=	DB::table('eoi_request')->where('requestid',$selectedRecord->request_id)->value('categoryid');
		
		$adminChargePercentage	=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('isActive',1)->value('admincharge');

		$records = DB::table('finance_demand_note as dn')
					->leftJoin('department_tbl as d','d.departmentid','=','dn.department_id')
					->leftJoin('eoi_request as e','e.requestid','=','dn.request_id')
					->leftJoin('project_tbl as p','p.projectid','=','dn.project_id')
					->select(
						'dn.*',
						'dn.request_id as requestid',
						'dn.order_id as orderid',
						'dn.project_id as projectid',
						'dn.vendor_id as vendorid',
						'dn.department_id as departmentid',
						'e.eoinumber',
						'e.releasedate',
						'e.engagementname',
						'e.projecttitle',
						'p.project_name',
						'd.departmentname',
					)
					->where('dn.demand_note_group_uuid',$demandNoteGroupUuid)
					->where('dn.status','Active')
					->orderByRaw("FIELD(dn.demand_note_type,'VENDOR_FUNDING','SERVICE_CHARGE')")
					->get();

		if($records->count() != 2)
		{
			abort(404);
		}

		$record = $records->first(function($item)
		{
			return $item->demand_note_type == 'VENDOR_FUNDING';
		});

		$serviceChargeRecord = $records->first(function($item)
		{
			return $item->demand_note_type == 'SERVICE_CHARGE';
		});
		
		$serviceChargeRecord->adminChargePercentage	=	$adminChargePercentage ?? 0;

		$financialYears = FinanceHelper::financialYears();

		$gstTaxes = DB::table('finance_tax_master')
						->where('tax_type','GST')
						->where('is_active',1)
						->get();

		$mode = 'edit';

		return view('finance.demandnote.create',compact(
			'mode',
			'record',
			'serviceChargeRecord',
			'financialYears',
			'gstTaxes'
		));
	}	


	public function update(Request $request, $id)
	{
		if($request->demand_note_date)
		{
			$request->merge([
				'demand_note_date' => Carbon::parse($request->demand_note_date)->format('Y-m-d'),
			]);
		}

		if($request->from_date)
		{
			$request->merge([
				'from_date' => Carbon::parse($request->from_date)->format('Y-m-d'),
			]);
		}

		if($request->to_date)
		{
			$request->merge([
				'to_date' => Carbon::parse($request->to_date)->format('Y-m-d'),
			]);
		}

		$rules = [
			'request_id'                 => 'nullable|integer',
			'order_id'                   => 'nullable|integer',
			'vendor_id'                  => 'nullable|integer',
			'department_id'              => 'required|integer',
			'project_id'                 => 'nullable|integer',
			'financial_year'             => 'required',
			'reference_type'             => 'required',
			'demand_note_date'           => 'required|date|before_or_equal:today',
			'from_date'                  => 'nullable|date',
			'to_date'                    => 'nullable|date|after:from_date',

			'vendor_funding_particular'  => 'required',
			'service_charge_particular'  => 'required',

			'advance_amount'             => 'required|numeric|min:0.01',
			'advance_gst_tax_id'         => 'required|integer',
			'service_charge_gst_tax_id'  => 'required|integer',

			'remarks'                    => 'nullable|max:1000',
			'attachment'                 => 'nullable|mimes:pdf,jpg,jpeg,png|max:5120'
		];

		$messages = [
			'department_id.required'             => 'Department is required.',
			'financial_year.required'            => 'Financial year is required.',
			'reference_type.required'            => 'Reference type is required.',
			'demand_note_date.required'          => 'Demand Note date is required.',
			'demand_note_date.date'              => 'Please enter a valid Demand Note date.',

			'vendor_funding_particular.required' => 'Vendor Payment Advance Particular is required.',
			'service_charge_particular.required' => 'CHiPS Service Charge Particular is required.',

			'advance_amount.required'            => 'Advance amount is required.',
			'advance_amount.numeric'             => 'Advance amount must be numeric.',
			'advance_amount.min'                 => 'Advance amount should be greater than zero.',

			'advance_gst_tax_id.required'        => 'GST for Vendor Payment Advance is required.',
			'advance_gst_tax_id.integer'         => 'Invalid GST selected for Vendor Payment Advance.',

			'service_charge_gst_tax_id.required' => 'GST for CHiPS Service Charge is required.',
			'service_charge_gst_tax_id.integer'  => 'Invalid GST selected for CHiPS Service Charge.',

			'remarks.max'                        => 'Remarks cannot exceed 1000 characters.',
			'attachment.mimes'                   => 'Attachment must be PDF, JPG, JPEG or PNG.',
			'attachment.max'                    => 'Attachment size should not exceed 5 MB.',
			'from_date.date'                    => 'Please enter a valid from date.',
			'to_date.date'                      => 'Please enter a valid to date.'
		];

		$validatedData = $request->validate($rules,$messages);

		if(empty($request->request_id) && empty($request->order_id))
		{
			return response()->json([
				'status'  => 0,
				'message' => 'Either EOI or Work Order must be selected.'
			],422);
		}

		$id = Crypt::decrypt($id);

		DB::beginTransaction();

		$oldAttachment = NULL;
		$newAttachment = NULL;

		try
		{
			$selectedDemandNote = 	DB::table('finance_demand_note')
									->where('demand_note_id',$id)
									->lockForUpdate()
									->first();

			if(!$selectedDemandNote)
			{
				throw new \Exception('Demand Note not found.');
			}

			$groupUuid = $selectedDemandNote->demand_note_group_uuid;

			if(empty($groupUuid))
			{
				throw new \Exception('Demand Note group information is missing.');
			}

			$categoryid				=	DB::table('eoi_request')->where('requestid',$selectedDemandNote->request_id)->value('categoryid');
			$adminChargePercentage	=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('isActive',1)->value('admincharge') ?? 0;

			$demandNotes	=	DB::table('finance_demand_note')
								->where('demand_note_group_uuid',$groupUuid)
								->where('status','Active')
								->lockForUpdate()
								->get();

			$vendorFunding	= 	$demandNotes
								->first(function($note)
								{
									return $note->demand_note_type == 'VENDOR_FUNDING';
								});

			$serviceCharge	=	$demandNotes
								->first(function($note)
								{
									return $note->demand_note_type == 'SERVICE_CHARGE';
								});

			if(!$vendorFunding || !$serviceCharge)
			{
				throw new \Exception(
					'Both Vendor Funding and Service Charge Demand Notes must exist.'
				);
			}

			$paymentExists	=	DB::table('finance_department_payment')
								->whereIn('demand_note_id',[$vendorFunding->demand_note_id,$serviceCharge->demand_note_id])
								->exists();

			if($paymentExists)
			{
				throw new \Exception('Demand Notes cannot be edited because payment has already been received against one or both Demand Notes.');
			}

			$advanceAmount	=	round((float)$request->advance_amount,2);
			$advanceTax		=	FinanceHelper::calculateGST($advanceAmount,$request->advance_gst_tax_id);

			$advanceAmountInWords = FinanceHelper::amountInWords($advanceTax['grand_total']);

			$serviceChargeAmount = round($advanceAmount * $adminChargePercentage / 100,2);

			$serviceChargeTax	=	FinanceHelper::calculateGST($serviceChargeAmount,$request->service_charge_gst_tax_id);

			$serviceChargeAmountInWords = FinanceHelper::amountInWords($serviceChargeTax['grand_total']);

			$attachment = $vendorFunding->attachment ?: $serviceCharge->attachment;

			$oldAttachment = $attachment;

			if($request->hasFile('attachment'))
			{
				$newAttachment	=	$request->file('attachment')->store('uploads/demand_note','public');
				$attachment 	= 	$newAttachment;
			}

			$oldVendorFunding	=	clone $vendorFunding;
			$oldServiceCharge	= 	clone $serviceCharge;

			$vendorFundingUpdate = [
				'financial_year'  => $request->financial_year,

				'tax_id'          => $request->advance_gst_tax_id,
				'gstin'           => $request->gstin ?? NULL,

				'from_date'       => $request->from_date ?? NULL,
				'to_date'         => $request->to_date ?? NULL,

				'demand_note_date' => $request->demand_note_date,

				'particular'      => $request->vendor_funding_particular,

				'taxable_amount'  => $advanceAmount,

				'cgst_rate'       => $advanceTax['cgst_rate'],
				'cgst_amount'     => $advanceTax['cgst_amount'],

				'sgst_rate'       => $advanceTax['sgst_rate'],
				'sgst_amount'     => $advanceTax['sgst_amount'],

				'igst_rate'       => $advanceTax['igst_rate'],
				'igst_amount'     => $advanceTax['igst_amount'],

				'total_amount'    => $advanceTax['grand_total'],

				'amount_in_words' => $advanceAmountInWords,

				'remarks'         => $request->remarks,

				'attachment'      => $attachment,

				'updated_at'      => now()
			];

			DB::table('finance_demand_note')
			->where('demand_note_id',$vendorFunding->demand_note_id)
			->update($vendorFundingUpdate);


			$serviceChargeUpdate = [
				'financial_year'  => $request->financial_year,

				'tax_id'          => $request->service_charge_gst_tax_id,
				'gstin'           => $request->gstin ?? NULL,

				'from_date'       => $request->from_date ?? NULL,
				'to_date'         => $request->to_date ?? NULL,

				'demand_note_date' => $request->demand_note_date,

				'particular'      => $request->service_charge_particular,

				'taxable_amount'  => $serviceChargeAmount,

				'cgst_rate'       => $serviceChargeTax['cgst_rate'],
				'cgst_amount'     => $serviceChargeTax['cgst_amount'],

				'sgst_rate'       => $serviceChargeTax['sgst_rate'],
				'sgst_amount'     => $serviceChargeTax['sgst_amount'],

				'igst_rate'       => $serviceChargeTax['igst_rate'],
				'igst_amount'     => $serviceChargeTax['igst_amount'],

				'total_amount'    => $serviceChargeTax['grand_total'],

				'amount_in_words' => $serviceChargeAmountInWords,

				'remarks'         => $request->remarks,

				'attachment'      => $attachment,

				'updated_at'      => now()
			];

			DB::table('finance_demand_note')
			->where('demand_note_id',$serviceCharge->demand_note_id)
			->update($serviceChargeUpdate);


			FinanceHelper::logChanges(
				'Demand Note',
				$vendorFunding->demand_note_id,
				$oldVendorFunding,
				$vendorFundingUpdate,
				'UPDATE',
				'Vendor Funding Demand Note updated.'
			);

			FinanceHelper::logChanges(
				'Demand Note',
				$serviceCharge->demand_note_id,
				$oldServiceCharge,
				$serviceChargeUpdate,
				'UPDATE',
				'CHiPS Service Charge Demand Note updated.'
			);

			if($newAttachment && $oldAttachment && $oldAttachment != $newAttachment)
			{
				Storage::disk('public')->delete($oldAttachment);
			}


			DB::commit();

			session()->flash('success','Both Demand Notes updated successfully.');

			return response()->json([
				'status'	=> 	1,
				'message'  	=> 	'Both Demand Notes updated successfully.',
				'redirect' 	=> 	route('finance.demandnote.index')
			]);
		}
		catch(\Exception $e)
		{
			DB::rollBack();

			if($newAttachment)
			{
				Storage::disk('public')->delete($newAttachment);
			}

			return response()->json([
				'status'	=> 	0,
				'message' 	=> 	$e->getMessage()
			],500);
		}
	}

	public function cancel(Request $request,$id)
	{
		$id = Crypt::decrypt($id);

		$request->validate(
		[
			'reason'=>'required|max:500'
		],
		[
			'reason.required'=>'Cancellation reason is required.',
			'reason.max'=>'Cancellation reason cannot exceed 500 characters.'
		]);

		DB::beginTransaction();

		try
		{

			$demandNote	=	DB::table('finance_demand_note')
							->where('demand_note_id',$id)
							->first();

			if(!$demandNote)
			{
				return response()->json([
					'status'	=>	0,
					'message'	=>	'Demand Note not found.'
				]);
			}

			if($demandNote->status=='Cancelled')
			{
				return response()->json([
					'status'=>0,
					'message'=>'Demand Note is already cancelled.'
				]);
			}

			$paymentExists	=	DB::table('finance_department_payment')
								->where('demand_note_id',$id)
								->exists();

			if($paymentExists)
			{
				return response()->json([
					'status'=>0,
					'message'=>'Demand Note cannot be cancelled because department receipt has already been recorded.'
				]);
			}

			DB::table('finance_demand_note')
			->where('demand_note_id',$id)
			->update([
					'status'				=>	'Cancelled',
					'cancelled_by'			=>	session('userId'),
					'cancelled_at'			=>	now(),
					'cancellation_reason'	=>	$request->reason,
					'updated_at'			=>	now()
				]);

			FinanceHelper::logChanges(
				'Demand Note',
				$id,
				$demandNote,
				[
					'status'				=>	'Cancelled',
					'cancellation_reason'	=>	$request->reason
				],
				'CANCEL',
				'Demand Note cancelled.'
			);

			DB::commit();

			session()->flash('success','Demand Note cancelled successfully.');

			return response()->json([
				'status'=>1,
				'message'=>'Demand Note cancelled successfully.'
			]);

		}
		catch(\Exception $e)
		{
			DB::rollBack();

			return response()->json([
				'status'=>0,
				'message'=>$e->getMessage()
			],500);
		}

	}
	

	public function view($id)
	{
		try
		{
			$id	=	Crypt::decrypt($id);

			$selectedDemandNote	=	DB::table('finance_demand_note')
									->where('demand_note_id',$id)
									->where('status','Active')
									->first();

			if(!$selectedDemandNote)
			{
				return redirect()->route('finance.demandnote.index')->with('error','Demand Note not found.');
			}

			$groupUuid	=	$selectedDemandNote->demand_note_group_uuid;

			if(empty($groupUuid))
			{
				return redirect()->route('finance.demandnote.index')->with('error','Demand Note group information not found.');
			}

			$categoryid	=	DB::table('eoi_request')->where('requestid',$selectedDemandNote->request_id)->value('categoryid');
			
			$adminChargePercentage	=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('isActive',1)->value('admincharge');

			$demandNotes	=	DB::table('finance_demand_note as dn')
								->leftJoin('department_tbl as dept','dept.departmentid','=','dn.department_id')
								->leftJoin('finance_tax_master as tm','tm.tax_id','=','dn.tax_id')
								->leftJoin('eoi_request as e','e.requestid','=','dn.request_id')
								->select(
									'dn.*',
									'dept.departmentname',
									'tm.tax_name',
									'tm.hsn_sac_code',
									'e.eoinumber'
								)
								->where('dn.demand_note_group_uuid',$groupUuid)
								->where('dn.status','Active')
								->orderByRaw("FIELD(dn.demand_note_type,'VENDOR_FUNDING','SERVICE_CHARGE')")
								->get();


			if($demandNotes->count() != 2)
			{
				return redirect()
					->route('finance.demandnote.index')
					->with('error','Both Demand Notes belonging to this group could not be found.');
			}

			$vendorFunding = $demandNotes->first(function($item)
			{
				return $item->demand_note_type == 'VENDOR_FUNDING';
			});


			$serviceCharge = $demandNotes->first(function($item)
			{
				return $item->demand_note_type == 'SERVICE_CHARGE';
			});
			
			$serviceCharge->adminChargePercentage	=	$adminChargePercentage ?? 0;

			if(!$vendorFunding || !$serviceCharge)
			{
				return redirect()
					->route('finance.demandnote.index')
					->with('error','Vendor Funding or Service Charge Demand Note is missing.');
			}

			$vendorFundingReceipts	=	DB::table('finance_department_payment as fp')
										->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','fp.payment_mode_id')
										->leftJoin('finance_bank_account as ba','ba.bank_account_id','=','fp.bank_account_id')
										->where('fp.demand_note_id',$vendorFunding->demand_note_id)
										->select(
											'fp.*',
											'pm.payment_mode',
											'ba.bank_name',
											'ba.account_name'
										)
										->orderBy('fp.department_payment_id')
										->get();

			$serviceChargeReceipts	=	DB::table('finance_department_payment as fp')
										->leftJoin('finance_payment_mode_master as pm','pm.payment_mode_id','=','fp.payment_mode_id')
										->leftJoin('finance_bank_account as ba','ba.bank_account_id','=','fp.bank_account_id')
										->where('fp.demand_note_id',$serviceCharge->demand_note_id)
										->select(
											'fp.*',
											'pm.payment_mode',
											'ba.bank_name',
											'ba.account_name'
										)
										->orderBy('fp.department_payment_id')
										->get();


			$vendorFundingTotalReceived	=	$vendorFundingReceipts->sum('gross_received_amount');


			$vendorFundingTotalInvoiced	=	DB::table('finance_department_invoice_payment as dip')
											->join('finance_department_payment as fp','fp.department_payment_id','=','dip.department_payment_id')
											->where('fp.demand_note_id',$vendorFunding->demand_note_id)
											->sum('dip.allocated_amount');


			$vendorFundingRemainingBalance = round(
				(float)$vendorFundingTotalReceived -
				(float)$vendorFundingTotalInvoiced,
				2
			);

			$serviceChargeTotalReceived	=	$serviceChargeReceipts->sum('gross_received_amount');


			$serviceChargeTotalInvoiced	=	DB::table('finance_department_invoice_payment as dip')
											->join('finance_department_payment as fp','fp.department_payment_id','=','dip.department_payment_id')
											->where('fp.demand_note_id',$serviceCharge->demand_note_id)
											->sum('dip.allocated_amount');


			$serviceChargeRemainingBalance	=	round((float)$serviceChargeTotalReceived - (float)$serviceChargeTotalInvoiced,2);


			return view(
				'finance.demandnote.view',
				compact(
					'demandNotes',
					'vendorFunding',
					'serviceCharge',
					'vendorFundingReceipts',
					'serviceChargeReceipts',
					'vendorFundingTotalReceived',
					'vendorFundingTotalInvoiced',
					'vendorFundingRemainingBalance',
					'serviceChargeTotalReceived',
					'serviceChargeTotalInvoiced',
					'serviceChargeRemainingBalance'
				)
			);
		}
		catch(\Exception $e)
		{
			return redirect()->route('finance.demandnote.index')->with('error',$e->getMessage());
		}
	}	


	public function getDemandNoteResources(Request $request)
	{
		if($request->from_date)
		{
			$request->merge(['from_date' => Carbon::parse($request->from_date)->format('Y-m-d')]);
		}
		if($request->to_date)
		{
			$request->merge(['to_date' => Carbon::parse($request->to_date)->format('Y-m-d')]);
		}
		
		$request->validate([
			'from_date'	=> 	'required|date',
			'to_date'	=> 	'required|date',
			'requestid'	=> 	'required',
		]);
		
		$requestid	=	$request->requestid;
		$fromDate	=	$request->from_date;
		$toDate		=	$request->to_date;
		
		try
		{
			$eoi	=	DB::table('eoi_request')
						->where('requestid', $requestid)
						->select('*')
						->selectRaw("CASE 
							WHEN tier_choice = 1 THEN 1
							WHEN tier_choice = 2 THEN 2
							WHEN tier_choice = 3 THEN 1
							ELSE NULL
						END AS tierid")
						->first();
			
			if(!$eoi)
			{
				throw new \Exception('EoI data not found.');
			}
			$resources	=	[];
			$orders		=	[];
			
			
			$orders	=	DB::table('eoi_work_order as wo')
						->select('wo.orderid','wo.ordernumber','wo.categoryid','v.tierid','wo.orderdate','wo.workorderduedate')
						->leftJoin('vendor_tbl as v','v.vendorid','=','wo.vendorid')
						->where('wo.requestid',$requestid)
						->where('wo.isActiveOrder',1)
						->where('wo.isExtended',0)
						->get();
						
			if($orders->isNotEmpty())
			{
				foreach($orders as $order)
				{
					$order->resources	=	DB::table('eoi_resource_deployment as a')
											->join('sector_tbl as b','b.sectorid','=','a.sectorid')
											->join('position_tbl as c','c.positionid','=','a.positionid')
											->where('a.orderid',$order->orderid)
											->whereIn('a.deployment_status',['Pending','Active'])
											->get();
				}
			}
			else
			{
				$resources	=	DB::table('eoi_request_detail as a')
								->select('a.recordid','a.requestid','a.duration','b.sectorid','c.positionid','b.sectorname','c.consultantposition')
								->join('sector_tbl as b','b.sectorid','=','a.sectorid')
								->join('position_tbl as c','c.positionid','=','a.positionid')
								->where('a.requestid',$eoi->requestid)
								->get();
			}
			// Your calculation logic here
			
			$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
			$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
			
			$html	=	view('finance.demandnote.ajaxpages.resourcesTable', compact('eoi','orders','resources','sectors','positions','fromDate','toDate'))->render();

			return response()->json([
				'status'	=>	true,
				'html'		=>	$html
			]);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'status'	=>	false,
				'message' 	=> 	$e->getMessage()
			],500);
		}
	}
	

	public function getDemandNoteValue(Request $request)
	{
		try
		{
			$request->validate([
				'requestid' 				=> 	'required|integer',
				'resources' 				=> 	'required|array|min:1',
				'resources.*.deploymentid' 	=> 	'required|integer|min:1',
				'resources.*.orderid' 		=> 	'required|integer|min:1',
				'resources.*.sectorid' 		=> 	'required|integer|min:1',
				'resources.*.positionid' 	=> 	'required|integer|min:1',
				'resources.*.categoryid' 	=> 	'required|integer|min:1',
				'resources.*.tierid' 		=> 	'required|integer|min:1',
				'resources.*.fromDate' 		=> 	'required|date_format:d-m-Y',
				'resources.*.toDate' 		=> 	'required|date_format:d-m-Y',
			]);

			$resources = [];

			foreach($request->resources as $resource)
			{
				$fromDate 	= 	Carbon::createFromFormat('d-m-Y', $resource['fromDate'])->startOfDay();
				$toDate 	= 	Carbon::createFromFormat('d-m-Y', $resource['toDate'])->startOfDay();

				if($toDate->lt($fromDate))
				{
					throw new \Exception('To Date cannot be less than From Date.');
				}

				$resources[] = (object)[
					'deploymentid' 	=> 	$resource['deploymentid'],
					'orderid' 		=> 	$resource['orderid'],
					'sectorid' 		=> 	$resource['sectorid'],
					'positionid' 	=> 	$resource['positionid'],
					'categoryid' 	=> 	$resource['categoryid'],
					'tierid' 		=> 	$resource['tierid'],
					'startDate' 	=> 	$fromDate->format('Y-m-d'),
					'endDate' 		=> 	$toDate->format('Y-m-d'),
				];
			}

			$result = $this->valueService->getCalculatedDemandNoteValue($resources);

			return response()->json([
				'status' 		=> 	true,
				'resources' 	=> 	$result['resources'],
				'grand_total' 	=> 	$result['grand_total']
			]);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'status' => false,
				'message' => $e->getMessage()
			], 422);
		}
	}	

	public function getCalculatedFile(Request $request)
	{
		try
		{
			$request->validate([
				'requestid' 				=> 	'required|integer',
				'resources' 				=> 	'required|array|min:1',
				'resources.*.deploymentid' 	=> 	'required|integer|min:1',
				'resources.*.orderid' 		=> 	'required|integer|min:1',
				'resources.*.sectorid' 		=> 	'required|integer|min:1',
				'resources.*.positionid' 	=> 	'required|integer|min:1',
				'resources.*.categoryid' 	=> 	'required|integer|min:1',
				'resources.*.tierid' 		=> 	'required|integer|min:1',
				'resources.*.fromDate' 		=> 	'required|date_format:d-m-Y',
				'resources.*.toDate' 		=> 	'required|date_format:d-m-Y',
			]);

			$resources = [];

			foreach($request->resources as $resource)
			{
				$fromDate 	= 	Carbon::createFromFormat('d-m-Y', $resource['fromDate'])->startOfDay();
				$toDate 	= 	Carbon::createFromFormat('d-m-Y', $resource['toDate'])->startOfDay();

				if($toDate->lt($fromDate))
				{
					throw new \Exception('To Date cannot be less than From Date.');
				}

				$resources[] = (object)[
					'deploymentid' 	=> 	$resource['deploymentid'],
					'orderid' 		=> 	$resource['orderid'],
					'sectorid' 		=> 	$resource['sectorid'],
					'positionid' 	=> 	$resource['positionid'],
					'categoryid' 	=> 	$resource['categoryid'],
					'tierid' 		=> 	$resource['tierid'],
					'startDate' 	=> 	$fromDate->format('Y-m-d'),
					'endDate' 		=> 	$toDate->format('Y-m-d'),
				];
			}

			return response()->json([
				'status' => true
			]);			
			
		}
		catch(\Exception $e)
		{
			Log::error('Error '.$e->getMessage());
			return response()->json([
				'status' => false,
				'message' => $e->getMessage()
			], 422);
		}
	}	

	public function downloadCalculatedFile(Request $request)
	{
        $request->merge([
            'resources' => json_decode($request->resources, true)
        ]);
		
		try
		{
			$request->validate([
				'requestid'                  => 'required|integer',
				'resources'                  => 'required|array|min:1',
				'resources.*.deploymentid'  => 'required|integer|min:1',
				'resources.*.orderid'       => 'required|integer|min:1',
				'resources.*.sectorid'      => 'required|integer|min:1',
				'resources.*.positionid'    => 'required|integer|min:1',
				'resources.*.categoryid'    => 'required|integer|min:1',
				'resources.*.tierid'        => 'required|integer|min:1',
				'resources.*.fromDate'      => 'required|date_format:d-m-Y',
				'resources.*.toDate'        => 'required|date_format:d-m-Y',
			]);

			$resources = [];

			foreach($request->resources as $resource)
			{
				$fromDate = Carbon::createFromFormat('d-m-Y',$resource['fromDate'])->startOfDay();

				$toDate = Carbon::createFromFormat('d-m-Y',$resource['toDate'])->startOfDay();

				if($toDate->lt($fromDate))
				{
					throw new \Exception('To Date cannot be less than From Date.');
				}

				$resources[] = (object)[
					'deploymentid' => $resource['deploymentid'],
					'orderid'      => $resource['orderid'],
					'sectorid'     => $resource['sectorid'],
					'positionid'   => $resource['positionid'],
					'categoryid'   => $resource['categoryid'],
					'tierid'       => $resource['tierid'],
					'startDate'    => $fromDate->format('Y-m-d'),
					'endDate'      => $toDate->format('Y-m-d'),
				];
			}

			$result = $this->valueService->getCalculatedDemandNoteValue($resources);

			$html	=	view(
							'finance.demandnote.ajaxpages.calculationsheetTable',
							[
								'data' => $result
							]
						)->render();

			$pdf = Pdf::loadHTML($html)->setPaper('a4', 'landscape');

			return $pdf->download('pricing-calculation.pdf');
		}
		catch(\Exception $e)
		{
			Log::error('Error '.$e->getMessage());

			return response()->json([
				'status'  => false,
				'message' => $e->getMessage()
			], 422);
		}
	}	
}
