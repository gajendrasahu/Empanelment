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
class WorkOrderController extends Controller
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
	
    public function storeDemandNote(Request $request,$orderid)
	{
		$rules = [
			'demandnotefile'      => 'required|mimes:pdf|max:2048',
			'demandnotenumber'    => 'required|string',
			'demandnoteamount'    => 'required|numeric',

			'amountreceived'      => 'nullable|numeric',
			'transactiondetail'   => 'nullable|string|max:150|required_with:amountreceived,paymentfile,paymentperiod,payment_date',
			'paymentfile'         => 'nullable|file|mimes:pdf|max:5120|required_with:amountreceived,transactiondetail,paymentperiod,payment_date',
			'paymentperiod'       => 'nullable|numeric|required_with:amountreceived,transactiondetail,paymentfile,payment_date',
			'payment_date'        => ['nullable', 'date_format:d-m-Y', 'required_with:amountreceived,transactiondetail,paymentfile,paymentperiod'],
			'payment_remark'      => 'nullable|string|max:255',
		];
		$messages = [
			'transactiondetail.required_with'	=> 'If any payment detail is entered, all payment fields are required.',
			'paymentfile.required_with'       	=> 'If any payment detail is entered, all payment fields are required.',
			'paymentperiod.required_with'     	=> 'If any payment detail is entered, all payment fields are required.',
			'payment_date.required_with'      	=> 'If any payment detail is entered, all payment fields are required.',

			'amountreceived.numeric'          	=> 'Invalid payment amount value.',
			'transactiondetail.max'           	=> 'Payment transaction maximum length is 150 characters.',
			'paymentfile.file'                	=> 'Invalid transaction file.',
			'paymentfile.mimes'               	=> 'The payment file must be a PDF document.',
			'paymentfile.max'                 	=> 'Maximum 5 MB file size is allowed.',
			'paymentperiod.numeric'           	=> 'Invalid payment period value.',
			'payment_date.date_format'        	=> 'Invalid payment date format (use dd-mm-YYYY).',
			'payment_remark.max'        		=> 'Only 255 characters allowed for payment remark',
		];

		$validatedData = $request->validate($rules, $messages);
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
	
		DB::beginTransaction();
		try
		{
			$orderid	=	Crypt::decrypt($orderid);
			$order		=	DB::table('eoi_work_order as a')
							->select('a.orderid','a.requestid','a.ordernumber','a.orderdate')
							->where('a.orderid',$orderid)
							->first();
			if(!$order)
			{
				return back()->with('fail', 'Invalid order detail provided. Please check and try again!')->withInput();
			}
			$paymentfile=	$request->file('paymentfile') ?? '';
			if($paymentfile!='')
			{
				$paymentfile= $request->file('paymentfile')->store('uploads/workorderpayment','public');
			}
			$demandnotefile=	$request->file('demandnotefile') ?? '';
			if($demandnotefile!='')
			{
				$demandnotefile= $request->file('demandnotefile')->store('uploads/demandnotefiles','public');
			}

			if($validatedData['payment_date']!='')
			{
				$payment_date = Carbon::createFromFormat('d-m-Y', $validatedData['payment_date']);
			}
			else
			{
				$payment_date	=	NULL;
			}

			$nextPaymentDue = NULL;
			$nextReminder = NULL;
			
			$paymentperiod		=	$request->input('paymentperiod') ?? 0;
			if($payment_date && $paymentperiod > 0)
			{
				$nextPaymentDueDate	=	$payment_date->copy()->addMonths($paymentperiod);
				$nextReminderDate 	=	$payment_date->copy()->addMonths($paymentperiod - 2);

				$nextPaymentDue	= 	$nextPaymentDueDate->format('Y-m-d');
				$nextReminder 	= 	$nextReminderDate->format('Y-m-d');
			}
			
			$payment_date = Carbon::parse($validatedData['payment_date'])->format('Y-m-d');
			
			DB::table('eoi_work_order_payment_received')
			->insert([
				'orderid'					=>	$order->orderid,
				'requestid'					=>	$order->requestid,
				'transactiondetail'			=>	$validatedData['transactiondetail'] ?? NULL,
				'paymentfile'				=>	$paymentfile,
				'demandnotenumber'			=>	$validatedData['demandnotenumber'] ?? NULL,
				'demandnotefile'			=>	$demandnotefile,
				'demandnoteamount'			=>	$validatedData['demandnoteamount'] ?? 0,
				'amountreceived'			=>	$validatedData['amountreceived'] ?? 0,
				'paymentperiod'				=>	$validatedData['paymentperiod'] ?? 0,
				'created_by'				=>	Session::get('userId'),
				'payment_date'				=>	$payment_date,
				'creationdate'				=>	now(),
				'payment_remark'			=>	$validatedData['payment_remark'] ?? NULL,
				'next_payment_due_date'		=>	$nextPaymentDue,
				'next_payment_reminder_date'=>	$nextReminder
			]);

			DB::commit();
			//return back()->with('success','Payment detail stored successfully!');
			return redirect()->route('viewmore.order', ['orderid'=>Crypt::encrypt($order->orderid)])->with('success','Payment detail stored successfully!');			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
		
    }
	
    public function addDemandNote(Request $request,$orderid)
	{
		$orderid	=	Crypt::decrypt($orderid);
		
		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.eoinumber','b.projecttitle','b.projectduration','d.departmentname','b.creationdate','c.companyname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->join('department_tbl as d','d.userid','b.userid')
						->where('a.orderid',$orderid)
						->first();

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/add_demandnote', compact('order','token'));
    }
	
    public function demandNotePaymentHistory(Request $request)
	{
		if(Session('userType')!='ADMIN')
		{
			return redirect('dashboard');
		}
		
		$orderid	=	Crypt::decrypt($request->input('orderid'));
		$paymentid	=	$request->input('paymentid') ?? 0;

		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.eoinumber','b.projecttitle','b.projectduration','d.departmentname','b.creationdate','c.companyname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->join('department_tbl as d','d.userid','b.userid')
						->where('a.orderid',$orderid)
						->first();
		
		$notes	=	DB::table('eoi_work_order_payment_received')
					->select('*')
					->where('orderid',$orderid)
					->where('parentpaymentid',0)
					->when($paymentid!=0,function($query) use ($paymentid){
						return $query->where('paymentid','=',$paymentid);
					})
					->distinct()
					->get();
		
		foreach($notes as $dn)
		{
			$dn->payments	=	DB::table('eoi_work_order_payment_received')
								->where('parentpaymentid',$dn->paymentid)
								->get();
		}
		
		$html = view('admin.ajaxpages.demandnotepaymentsTable',['order'=>$order,'notes'=>$notes])->render();
		//return view('admin/ajaxpages/demandnotepaymentsTable',compact('order','notes'));
		return response()->json(['status'=>200,'message'=>'Committee record added successfully.','data' => $html,'notes'=>$notes]);
    }
	
    public function receiveDemandNotePayment(Request $request,$paymentid)
	{
		$rules = [
			'paying'			=> 	'required|numeric',
			'transactiondetail'	=> 	'required|max:150',
			'paymentfile' 		=> 	'required|file|mimes:pdf|max:5120',
			'paymentperiod'		=> 	'required|numeric',
			'payment_date' 		=> 	['required', 'date_format:d-m-Y'],
			'payment_remark'	=> 	'nullable',

		];
        $messages = [
            'paying.required'			=>	'Paying amount is required',
			'paying.numeric'			=>	'Invalid payment amount value',
            'transactiondetail.required'=>	'Payment transaction detail is required',
			'transactiondetail.max'		=>	'Payment transaction maximum length is 150 characters',
			'paymentfile.required'		=> 	'Payment transaction file is required',
			'paymentfile.file'			=> 	'Invalid transaction file',
			'paymentfile.mimes'			=> 	'Invalid transaction file',
			'paymentfile.max'			=> 	'Maximum 5 MB file size is allowed',
            'paymentperiod.required'	=>	'Payment period is required',
			'paymentperiod.numeric'		=>	'Invalid payment period value',
			'payment_date.required'		=> 	'Payment date is required',
			'payment_date.date_format'	=> 	'Invalid payment date',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
	
		DB::beginTransaction();
		try
		{
			$paymentid	=	Crypt::decrypt($paymentid);
			$payment	=	DB::table('eoi_work_order_payment_received as a')
							->select('a.*','b.ordernumber')
							->join('eoi_work_order as b','b.orderid','=','a.orderid')
							->where('a.paymentid',$paymentid)
							->first();
			if(!$payment)
			{
				return back()->with('fail', 'Invalid payment detail provided. Please check and try again!')->withInput();
			}
			$payment->total_received=	DB::table('eoi_work_order_payment_received')
										->where('parentpaymentid',0)
										->where('orderid',$payment->orderid)
										->sum('amountreceived');
			
			$balance				=	$payment->demandnoteamount-$payment->total_received;
			if($validatedData['paying']>$balance)
			{
				return back()->with('fail', 'The payment amount is greater than the balance amount. Please check and try again!')->withInput();
			}
			$paymentfile=	$request->file('paymentfile') ?? '';
			if($paymentfile!='')
			{
				$paymentfile= $request->file('paymentfile')->store('uploads/workorderpayment','public');
			}

			if($validatedData['payment_date']!='')
			{
				$payment_date = Carbon::createFromFormat('d-m-Y', $validatedData['payment_date']);
			}
			else
			{
				$payment_date	=	NULL;
			}

			$nextPaymentDue = NULL;
			$nextReminder = NULL;
			
			$paymentperiod		=	$request->input('paymentperiod') ?? 0;
			if($payment_date && $paymentperiod > 0)
			{
				$nextPaymentDueDate	=	$payment_date->copy()->addMonths($paymentperiod);
				$nextReminderDate 	=	$payment_date->copy()->addMonths($paymentperiod - 2);

				$nextPaymentDue	= 	$nextPaymentDueDate->format('Y-m-d');
				$nextReminder 	= 	$nextReminderDate->format('Y-m-d');
			}
			
			$payment_date = Carbon::parse($validatedData['payment_date'])->format('Y-m-d');
			
			DB::table('eoi_work_order_payment_received')
			->insert([
				'parentpaymentid'			=>	$paymentid,
				'orderid'					=>	$payment->orderid,
				'requestid'					=>	$payment->requestid,
				'transactiondetail'			=>	$validatedData['transactiondetail'] ?? NULL,
				'paymentfile'				=>	$paymentfile,
				'amountreceived'			=>	$validatedData['paying'] ?? 0,
				'paymentperiod'				=>	$validatedData['paymentperiod'] ?? 0,
				'created_by'				=>	Session::get('userId'),
				'payment_date'				=>	$payment_date,
				'creationdate'				=>	now(),
				'payment_remark'			=>	$validatedData['payment_remark'] ?? NULL,
				'next_payment_due_date'		=>	$nextPaymentDue,
				'next_payment_reminder_date'=>	$nextReminder
			]);
			DB::table('eoi_work_order_payment_received')
			->where('paymentid',$paymentid)
			->update([
				'next_payment_due_date'		=>	$nextPaymentDue,
				'next_payment_reminder_date'=>	$nextReminder
			]);

			DB::commit();
			//return back()->with('success','Payment detail stored successfully!');
			return redirect()->route('viewmore.order', ['orderid'=>Crypt::encrypt($payment->orderid)])->with('success','Payment detail stored successfully!');			
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
    }
	
    public function payDemandNote(Request $request,$paymentid)
	{
		$paymentid	=	Crypt::decrypt($paymentid);
		
		$payment	=	DB::table('eoi_work_order_payment_received as a')
						->select('a.*','b.ordernumber')
						->join('eoi_work_order as b','b.orderid','=','a.orderid')
						->where('a.paymentid',$paymentid)
						->first();
		

		$payment->total_received 	= 	DB::table('eoi_work_order_payment_received')
										->where('orderid',$payment->orderid)
										->where(function ($query) use ($payment) {
											$query->where('paymentid', $payment->paymentid)
												  ->orWhere('parentpaymentid', $payment->paymentid);
										})
										->sum('amountreceived');

		
		$payment->balance	=	$payment->demandnoteamount-$payment->total_received;

		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.eoinumber','b.projecttitle','b.projectduration','d.departmentname','b.creationdate','c.companyname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->join('department_tbl as d','d.userid','b.userid')
						->where('a.orderid',$payment->orderid)
						->first();

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/receive_payment', compact('order','payment','token','paymentid'));
    }
	
    public function viewMoreOrderDetail(Request $request,$orderid)
	{
		if(Session('userType')!='ADMIN') {
			return redirect('dashboard');
		}
		
		$orderid	=	Crypt::decrypt($orderid);

		$order 		= 	DB::table('eoi_work_order as a')
						->select('a.*','b.eoinumber','b.projecttitle','b.projectduration','d.departmentname','b.creationdate','c.companyname')
						->join('eoi_request as b','b.requestid','=','a.requestid')
						->join('vendor_tbl as c','c.vendorid','=','a.vendorid')
						->join('department_tbl as d','d.userid','b.userid')
						->where('a.orderid',$orderid)
						->first();

		$demand_notes=	DB::table('eoi_work_order_payment_received as a')
							->select('a.*','b.demandnotefile')
							->join('eoi_work_order as b','b.orderid','=','a.orderid')
							->where('a.orderid',$orderid)
							->where('a.parentpaymentid','=',0)
							->get();
							
		foreach($demand_notes as $dn)
		{
			$dn->amountreceived 	= 	DB::table('eoi_work_order_payment_received')
										->where(function ($query) use ($dn) {
											$query->where('paymentid', $dn->paymentid)
												  ->orWhere('parentpaymentid', $dn->paymentid);
										})
										->sum('amountreceived');
										
			$dn->balance			=	$dn->demandnoteamount-$dn->amountreceived;
			$dn->demandnoteamount	=	$this->formatIndianCurrency($dn->demandnoteamount);
			$dn->amountreceived		=	$this->formatIndianCurrency($dn->amountreceived);
			$dn->balance			=	$this->formatIndianCurrency($dn->balance);
			$dn->expiring 			= 	Carbon::parse($dn->next_payment_due_date)->format('d-m-Y');
			$dn->days_to_expire 	= 	Carbon::now()->diffInDays(Carbon::parse($dn->expiring), false);
		}

	

		
		return view('admin/master/viewmore_order', compact('order','demand_notes'));
    }
	
    public function updateDeploymentDate(Request $request,$orderid)
	{
		$order	=	DB::table('eoi_work_order')->where('orderid',Crypt::decrypt($orderid))->first();
		$orderDate	=	date('d\-m\-Y',strtotime($order->orderdate));
		$rules = [
			'employee_code'     => 	'array',
			'employee_code.*' 	=> 	'nullable|regex:/^[a-zA-Z0-9\s\-.]+$/',
			'candidatename'     => 	'array',
			'candidatename.*'   => 	'nullable|regex:/^[a-zA-Z\s\-.]+$/',
			'mobilenumber'      => 	'array',
			'mobilenumber.*'    => 	'nullable|digits:10',
			'email'             => 	'array',
			'email.*'           => 	'nullable|email',
			'deployed_date'     => 	'array',
			'deployed_date.*'   => 	'nullable',
		];
		
		$messages = [
			'employee_code.array'             => 	'Candidate names must be an array',
			'employee_code.*.regex'           =>	'Invalid candidate name',

			'candidatename.array'             => 	'Candidate names must be an array',
			'candidatename.*.regex'           =>	'Invalid candidate name',

			'mobilenumber.array'              => 	'Mobile numbers must be an array',
			'mobilenumber.*.digits'           => 	'Invalid mobile number',

			'email.array'                     => 	'Emails must be an array',
			'email.*.email'                   => 	'Invalid email address',

		];
		
		$validatedData 	= $request->validate($rules,$messages);
		
		DB::beginTransaction();
		try
		{
			$updates	=	0;
			$orderid	=	Crypt::decrypt($orderid);
			foreach($request->candidatename as $index => $name)
			{
				$deploymentid   	= 	Crypt::decrypt($request->deploymentid[$index]) ?? null;
				$employee_code  	= 	$request->employee_code[$index] ?? null;
				$mobilenumber   	= 	$request->mobilenumber[$index] ?? null;
				$email          	= 	$request->email[$index] ?? null;
				$deployed_date   	= 	$request->deployed_date[$index] ?? null;
				if($deployed_date)
				{
					$deployed_date	=	date('Y\-m\-d',strtotime($deployed_date));
				}
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
							'deployed_date'	=> $deployed_date
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
								'joining_date'			=>	$deployed_date,
								'deployed_date'			=>	$deployed_date
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
							'deployed_date'	=> $deployed_date
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
								'joining_date'			=>	$deployed_date,
								'deployed_date'			=>	$deployed_date
							]);
						}
					}					
					/*
					$updateCount	=	DB::table('eoi_resource_deployment')
										->where('orderid', $orderid)
										->where('deploymentid',$deploymentid)
										->where('is_verified',0)
										->update($data);
					*/
					$updateCount	=	DB::table('eoi_resource_deployment')
										->where('orderid',$orderid)
										->where('deploymentid',$deploymentid)
										->update($data);
					if($updateCount>0)
					{
						$updates		= 1;
						$existingData 	= (array) $exists;
						$changedFields 	= [];
						foreach ($data as $field => $newValue)
						{
							$oldValue = $existingData[$field] ?? null;
							if ($oldValue != $newValue)
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
					}
				}
				else
				{
					$data['orderid']  =	$orderid;
					//DB::table('eoi_resource_deployment')->insert($data);
				}
			}			
			DB::commit();
			if($updates>0)
			{
				return back()->with('success', 'Deployment details have been updated successfully.');
			}
			else
			{
				return back()->with('fail', 'No changes were detected to update.');
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return back()->with('duplicate',$e->getMessage())->withInput();	
		}
		
    }
	
    public function setDeploymentDate(Request $request,$orderid)
	{
		if(Session('userType')!='ADMIN') {
			return redirect('dashboard');
		}
		
		$orderid	=	Crypt::decrypt($orderid);

		$order 		= 	DB::table('eoi_work_order as a')
							->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.project_duration','a.project_name','a.categoryid','a.isExtended','a.isActiveOrder','a.isCancelled','a.vendorid','a.totalremuneration','a.totalbudget','a.totaladmincharge','a.workorderamount','a.signedcopy','b.departmentname','c.eoinumber','c.releasedate','d.ispm')
							->Join('department_tbl as b','b.departmentid','a.department_id')
							->leftjoin('eoi_request as c','c.requestid','a.requestid')
							->Join('users_tbl as d','d.userid','b.userid')
							->where('a.orderid',$orderid)
							->first();


		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

		$detail 	= 	[];
		
		if($order->categoryid==2)
		{
			$detail						=	$this->deploymentService->getCsfDeploymentData($order->orderid);
			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
		}
		if($order->categoryid==1)
		{
			$detail						=	$this->deploymentService->getAwdDeploymentData($order->orderid);

			
			$order->totalremuneration	=	$this->formatIndianCurrency($order->totalremuneration);
			$order->totalbudget			=	$this->formatIndianCurrency($order->totalbudget);
			$order->totaladmincharge	=	$this->formatIndianCurrency($order->totaladmincharge);
			$order->workorderamount		=	$this->formatIndianCurrency($order->workorderamount);
			
		}
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		return view('admin/master/deployment_detail', compact('order', 'detail','vendor','sectors','positions'));
    }

    public function updateWorkOrderDate(Request $request,$orderid)
	{
		$rules = [
			'orderno'		=> 	'required',
			'setorderdate' 	=> 	['required', 'date_format:d-m-Y'],
			'signedcopy' 	=> 	'required|file|mimes:pdf|max:5120',
		];
        $messages = [
            'orderno.required'			=>	'Order number is required',
			'setorderdate.required'		=> 	'Order date is required',
			'setorderdate.date_format'	=> 	'Invalid order date',
			'signedcopy.required'		=> 	'Signed copy of order is required',
			'signedcopy.file'			=> 	'Invalid signed copy of order',
			'signedcopy.mimes'			=> 	'Invalid signed copy of order',
			'signedcopy.max'			=> 	'Maximum 1 MB file is allowed',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		
		$order	=	DB::table('eoi_work_order')->where('orderid',Crypt::decrypt($orderid))->first();
		if(!$order)
		{
			return back()->with('duplicate','Invalid order detail provided.')->withInput();
		}
		
		$projectduration	=	DB::table('eoi_request')->where('requestid',$order->requestid)->value('projectduration');

		$userId		= 	$request->session()->get('userId');
		$userName	= 	$request->session()->get('userName');
		$issuper	= 	$request->session()->get('issuper');

		$signedcopy	=	(String) $request->file('signedcopy');

		if($signedcopy!='')
		{
			$signedcopy= $request->file('signedcopy')->store('uploads/signedorder', 'public');
		}		
		
		try
		{
			$workOrderDuedate = Carbon::parse($validatedData['setorderdate'])->addMonths($projectduration)->format('Y-m-d');
			$nextReminderDate = Carbon::parse($validatedData['setorderdate'])->addMonths($projectduration-2)->format('Y-m-d');
			DB::table('eoi_work_order')
			->where('orderid',Crypt::decrypt($orderid))
			->update([
				'orderno'			=>	$validatedData['orderno'],
				'ordernumber'		=>	$validatedData['orderno']."".$order->ordernumber,
				'orderdate'			=>	date('Y\-m-d',strtotime($validatedData['setorderdate'])),
				'workorderduedate'	=>	$workOrderDuedate,
				'nextreminderdate'	=>	$nextReminderDate,
				'isfinalized'		=>	1,
				'finalizedby'		=>	$userName,
				'signedcopy'		=>	$signedcopy
			]);
			
			return redirect()->route('show.workorder',['orderid'=>Crypt::decrypt($orderid)])->with('success', 'Work order number and date updated successfully.');
		}
		catch(QueryException $e)
		{
			if($signedcopy!='')
			Storage::disk('public')->delete($signedcopy);
			
			return back()->with('duplicate','The order number you entered already exists. Kindly enter a new order number.')->withInput();
		}
		
	}
    public function setOrderDate(Request $request,$orderid)
	{
		$orderid	=	Crypt::decrypt($orderid);

		$order 		= 	DB::table('eoi_work_order')->where('orderid', $orderid)->first();

		$eoi		=	DB::table('eoi_request')->where('requestid',$order->requestid)->first();

		$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();

		$detail 	= 	[];

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
		
		return view('admin/master/workorder_detail', compact('order', 'detail','vendor','eoi'));
    }
	
    public function workOrderList(Request $request,$firm_type=NULL)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','workorderlist');
		Session::put('menid',96);

		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');

		if($issuper==0)
		{
			$action	= 	DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=96 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderby('companyname')->get();
		
		//$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$departments=	DB::table('department_tbl as a')
							->select('a.*')
							->join('users_tbl as b','b.userid','=','a.userid')
							->where('b.isdepartment',1)
							->orderby('departmentname')
							->get();

		
		$managers	=	DB::table('department_tbl as a')
						->select('a.*')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('b.ispm',1)
						->orderby('departmentname')
						->get();

		$projects	=	DB::table('project_tbl')
						->orderby('project_name')
						->get();
		
		$search		=	"";
        return view('admin/master/workorder_list',compact('category','vendors','search','departments','managers','firm_type','projects'));
    }

    public function workOrdersList(Request $request,$search=NULL)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','workorderlist');
		Session::put('menid',96);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=96 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$vendors	=	DB::table('vendor_tbl')->whereNull('parentVendorId')->orderby('companyname')->get();
		//$departments=	DB::table('department_tbl')->orderby('departmentname')->get();
		$departments	=	DB::table('department_tbl as a')
						->select('a.*')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('b.isdepartment',1)
						->orderby('departmentname')
						->get();

		
		$managers	=	DB::table('department_tbl as a')
						->select('a.*')
						->join('users_tbl as b','b.userid','=','a.userid')
						->where('b.ispm',1)
						->orderby('departmentname')
						->get();

		$projects	=	DB::table('project_tbl')
						->orderby('project_name')
						->get();

		$firm_type	=	'';
		if($search!=NULL)
		{
			$search	=	DB::table('eoi_work_order')->where('orderid',$search)->value('ordernumber');
		}
        return view('admin/master/workorder_list',compact('category','vendors','search','departments','managers','firm_type','projects'));
    }

    public function getWorkOrderData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;
		$categoryid 	=	$request->input('categoryid');
		$projectid 		=	$request->input('projectid');
		$vendid 		=	$request->input('vendorid');
		$deptid 		=	$request->input('departmentid');
		$is_expired		=	$request->input('is_expired') ?? 0;
		$is_cancelled 	= 	($request->input('is_cancelled')==='true') ? 1 : 0;
		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$managerid 		=	$request->input('managerid');		
		
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);
        $data 			= 	DB::table('eoi_work_order as a')
								->select('a.*','b.jobcategory','c.companyname','c.contactperson','f.departmentname','g.project_name',DB::raw('CASE WHEN pd.orderid IS NULL THEN 1 ELSE 0 END as isDeployed'),DB::raw("(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.name IS NOT NULL AND erd.deployment_status!='Released') as resourcedeployed"),DB::raw("(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.deployment_status!='Released') as totalresources"))
								->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
								->leftJoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
								->leftJoin('pending_deployment as pd', 'pd.orderid', '=', 'a.orderid')
								->leftJoin('eoi_request as e', 'e.requestid', '=', 'a.requestid')
								->leftJoin('department_tbl as f', 'f.departmentid', '=', 'a.department_id')
								->leftJoin('project_tbl as g','g.projectid','=','a.projectid')
								->when($in_house == 1, function ($query) {
									$query->join('users_tbl as u', function ($join) {
										$join->on('u.userid','=','f.userid')
											 ->where('u.ispm','=',1)
											 ->where('a.categoryid',2);
									});
								})
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.refrence', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.project_name', 'like', '%' . $pagesearch . '%')
										  ->orWhere('f.departmentname', 'like', '%' . $pagesearch . '%')
										  ->orWhere('c.companyname', 'like', '%' . $pagesearch . '%');
									});
								})
								->when($categoryid!=0,function($query) use ($categoryid){
									return $query->where('a.categoryid','=',$categoryid);
								})
								->when($projectid!=0,function($query) use ($projectid){
									return $query->where('a.projectid','=',$projectid);
								})
								->when($vendorId!=0,function($query) use ($vendorId){
									return $query->where('a.vendorid','=',$vendorId);
								})
								->when($vendid!=0,function($query) use ($vendid){
									return $query->where('a.vendorid','=',$vendid);
								})
								->when($deptid!=0,function($query) use ($deptid){
									return $query->where('f.departmentid','=',$deptid);
								})
								->when($managerid!=0,function($query) use ($managerid){
									return $query->where('f.departmentid','=',$managerid);
								})
								->when($is_expired==1,function($query) {
									return $query->where('a.workorderduedate','<',date('Y\-m\-d'))
												 ->where('a.isExtended','=',0);
								})								
								->when($is_expired == 2, function ($query) {
									return $query->whereBetween('a.workorderduedate', [
										date('Y-m-d'), 
										date('Y-m-d', strtotime('+60 days'))
									])
									->where('a.isExtended','=',0);
								})
								->when($is_expired==3,function($query) {
									return $query->where('a.isActiveOrder','=',1)
												 ->where('a.workorderduedate','>',now());
								})								
								->when($is_expired==4,function($query) {
									return $query->where('a.workorderduedate','<',date('Y\-m\-d'))
												 ->where('a.isExtended','=',1);
								})								
								->when($is_expired == 5, function ($query) {
									return $query->whereBetween('a.workorderduedate', [
										date('Y-m-d'), 
										date('Y-m-d', strtotime('+45 days'))
									])
									->where('a.isExtended','=',0);
								})
								->when($is_expired == 6, function ($query) {
									return $query->whereBetween('a.workorderduedate', [
										date('Y-m-d'), 
										date('Y-m-d', strtotime('+30 days'))
									])
									->where('a.isExtended','=',0);
								})
								->where('a.isCancelled',$is_cancelled)
								->orderBy('a.orderdate','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		foreach($data as $dt)
		{
			$dt->workorderamount	=	$this->formatIndianCurrency($dt->workorderamount);
			$dt->expiring 			= 	Carbon::parse($dt->workorderduedate)->format('d-m-Y');
			$dt->days_to_expire 	= 	Carbon::now()->diffInDays(Carbon::parse($dt->expiring), false);

		}
		return view('admin/ajaxpages/workorderTable', ['data' => $data]);

    }
    public function exportOrderData(Request $request)
	{
		$categoryid 	=	$request->input('categoryid');
		$projectid 		=	$request->input('projectid');
		$vendid 		=	$request->input('vendorid');
		$deptid 		=	$request->input('departmentid');
		$is_expired		=	$request->input('is_expired') ?? 0;
		$is_cancelled 	= 	($request->input('is_cancelled')==='true') ? 1 : 0;

		$in_house 		= 	($request->input('in_house')==='true') ? 1 : 0;
		$managerid 		=	$request->input('managerid');		
		
		$pagesearch 	=	$request->input('pagesearch');
		$fileName		=	'work_order_data_' . date('Ymd_His') . '.csv';
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];
		
		
		$callback = function () use ($categoryid,$vendid,$deptid,$is_expired,$pagesearch,$is_cancelled,$in_house,$managerid,$projectid) {

			$handle = fopen('php://output','w');

			fputcsv($handle, [
				'S.No','Project Name',
				'EoI Number','Work Order Number','Firm Name','Department / Project Manager','Total Resource','Deployed','To be deployed','Order Date','Order Expiry Date','Order Amount','Duration As Per EoI','Expiry Status'
			]);

			$sno = 1;
			
			DB::table('eoi_work_order as a')
				->select(
					'g.project_name',
					'e.eoinumber',
					'a.ordernumber',
					'a.isCancelled',
					'a.isExtended',
					'c.companyname',
					'f.departmentname',

					DB::raw("
						(
							SELECT COUNT(*)
							FROM eoi_resource_deployment erd
							WHERE erd.orderid = a.orderid
							AND erd.deployment_status IN ('Active','Pending')
						) AS totalresources
					"),

					DB::raw("
						(
							SELECT COUNT(*)
							FROM eoi_resource_deployment erd
							WHERE erd.orderid = a.orderid
							AND erd.deployment_status = 'Active'
						) AS resourcedeployed
					"),

					DB::raw("'' AS tobedeployed"),
					'a.orderdate',
					'a.workorderduedate',
					'a.workorderamount',
					'e.projectduration',
					DB::raw("'' AS expirystatus")
				)

				->leftJoin('jobcategory_tbl as b', 'b.categoryid', '=', 'a.categoryid')
				->leftJoin('vendor_tbl as c', 'c.vendorid', '=', 'a.vendorid')
				->leftJoin('pending_deployment as pd', 'pd.orderid', '=', 'a.orderid')
				->leftJoin('eoi_request as e', 'e.requestid', '=', 'a.requestid')
				->leftJoin('department_tbl as f', 'f.departmentid', '=', 'a.department_id')
				->leftJoin('project_tbl as g', 'g.projectid', '=', 'a.projectid')

				->when($in_house == 1, function ($query) {
					$query->join('users_tbl as u', function ($join) {
						$join->on('u.userid', '=', 'f.userid')
							 ->where('u.ispm', 1);
					})->where('a.categoryid', 2);
				})

				->when($pagesearch != '', function ($query) use ($pagesearch) {
					$query->where(function ($q) use ($pagesearch) {
						$q->where('a.ordernumber', 'like', "%{$pagesearch}%")
						  ->orWhere('a.refrence', 'like', "%{$pagesearch}%")
						  ->orWhere('g.project_name', 'like', "%{$pagesearch}%")
						  ->orWhere('a.orderno', 'like', "%{$pagesearch}%")
						  ->orWhere('f.departmentname', 'like', "%{$pagesearch}%")
						  ->orWhere('c.companyname', 'like', "%{$pagesearch}%");
					});
				})

				->when($categoryid != 0, function ($query) use ($categoryid) {
					$query->where('a.categoryid', $categoryid);
				})

				->when($projectid != 0, function ($query) use ($projectid) {
					$query->where('a.projectid', $projectid);
				})

				->when($vendid != 0, function ($query) use ($vendid) {
					$query->where('a.vendorid', $vendid);
				})

				->when($deptid != 0, function ($query) use ($deptid) {
					$query->where('f.departmentid', $deptid);
				})

				// If managerid is actually userid, change f.departmentid to u.userid
				->when($managerid != 0, function ($query) use ($managerid) {
					$query->where('f.departmentid', $managerid);
				})

				->when($is_expired == 1, function ($query) {
					$query->where('a.workorderduedate', '<', date('Y-m-d'))
						  ->where('a.isExtended', 0);
				})

				->when($is_expired == 2, function ($query) {
					$query->whereBetween('a.workorderduedate', [
						date('Y-m-d'),
						date('Y-m-d', strtotime('+60 days'))
					])
					->where('a.isExtended','=',0);
				})

				->when($is_expired == 3, function ($query) {
					$query->where('a.isActiveOrder', 1)
						  ->where('a.workorderduedate', '>', now());
				})

				->when($is_expired == 4, function ($query) {
					$query->where('a.workorderduedate', '<', date('Y-m-d'))
						  ->where('a.isExtended', 1);
				})
				->when($is_expired == 5, function ($query) {
					return $query->whereBetween('a.workorderduedate', [
						date('Y-m-d'), 
						date('Y-m-d', strtotime('+45 days'))
					])
					->where('a.isExtended','=',0);
				})
				->when($is_expired == 6, function ($query) {
					return $query->whereBetween('a.workorderduedate', [
						date('Y-m-d'), 
						date('Y-m-d', strtotime('+30 days'))
					])
					->where('a.isExtended','=',0);
				})
				->where('a.isCancelled', $is_cancelled)
				->orderBy('a.workorderduedate')

				->chunk(500, function ($rows) use ($handle, &$sno) {

					foreach ($rows as $row) {

						$row->tobedeployed = $row->totalresources - $row->resourcedeployed;

						$dueDate = Carbon::parse($row->workorderduedate);

						$row->expiring = $dueDate->format('d-m-Y');
						$row->days_to_expire = Carbon::now()->diffInDays($dueDate, false);

						$days = $row->days_to_expire;
						$months = intdiv(abs($days), 30);
						$remainingDays = abs($days) % 30;

						$row->expirystatus = '';

						if ($row->isCancelled == 0) {

							if ($days >= 0) {

								if ($months > 0) {
									$row->expirystatus = $months . ' month' . ($months > 1 ? 's' : '');

									if ($remainingDays > 0) {
										$row->expirystatus .= ' ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
									}
								} else {
									$row->expirystatus = $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
								}

								$row->expirystatus .= ' left';

							} else {

								$row->expirystatus = 'Expired ';

								if ($months > 0) {
									$row->expirystatus .= $months . ' month' . ($months > 1 ? 's' : '');

									if ($remainingDays > 0) {
										$row->expirystatus .= ' ' . $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
									}
								} else {
									$row->expirystatus .= $remainingDays . ' day' . ($remainingDays > 1 ? 's' : '');
								}

								$row->expirystatus .= ' ago';
							}

						} else {
							$row->expirystatus = 'Cancelled';
						}

						if ($row->isExtended == 1) {
							$row->expirystatus = 'Extended';
						}

						$row->orderdate = "\t" . Carbon::parse($row->orderdate)->format('d-m-Y');
						$row->workorderduedate = "\t" . Carbon::parse($row->workorderduedate)->format('d-m-Y');

						if (!empty($row->projectduration)) {
							$row->projectduration .= ' Months';
						}

						unset(
							$row->isCancelled,
							$row->isExtended,
							$row->expiring,
							$row->days_to_expire
						);

						fputcsv($handle, array_merge([$sno++], (array) $row));
					}
				});

			fclose($handle);
		};
		return response()->stream($callback, 200, $headers);
    }


    public function addWorkOrder(Request $request)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','addworkorder');
		Session::put('menid',119);
		$userId	= 	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=119 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$vendors	=	DB::table('vendor_tbl')->select('vendorid','companyname')->orderby('companyname')->get();
		
		$departments=	DB::table('users_tbl as a')
						->select('a.userid','a.name','b.shortname')
						->join('department_tbl as b','b.userid','=','a.userid')
						->orderby('a.name')
						->get();
						
		$category	=	DB::table('jobcategory_tbl')->orderBy('categoryid')->get();
		/*
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')
					->select('experiencelevel')
					->where('experiencelevel','!=',0)
					->orderBy('experiencelevel')
					->distinct('experiencelevel')
					->get();

		$experiences=	DB::table('work_experience')
						->select('workexperience')
						->orderBy('experienceid')
						->get();
		
		$rateList	=	DB::table('remuneration_rate_list')->orderBy('rateid','DESC')->get();
		*/
		
        //return view('admin/master/workorder_add',compact('sectors','positions','levels','experiences','vendors','departments','token','rateList'));
		return view('admin/master/workorder_add',compact('category'));
    }

	public function loadWorkOrderForm(Request $request)
	{
		$tierid			=	$request->input('tierid') ?? 0;
		$categoryid		=	$request->input('categoryid') ?? 0;
		$projectduration=	$request->input('projectduration') ?? 0;
		if($tierid==0 || $categoryid==0 || $projectduration==0)
		{
			return response()->json([
				'errors' => [
					'err_msg' => ['Empanelment type, tier and project duration is required.'],
					]
				], 422);			
		}
		$sectorid	=	$request->input('sectorid') ?? 0;
		if($categoryid==2)
		{
			$sectorid 		= 	intval($request->input('sectorid'));
			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->select('a.remunerationid as value','b.consultantposition as label')
								->where('a.tierid','=',$tierid)
								->where('a.categoryid','=',$categoryid)
								->where('a.sectorid','=',$sectorid)
								->orderby('b.consultantposition')
								->get();

								
			$sectors	=	DB::table('sector_tbl')->where('categoryid',$categoryid)->orderby('sectorname')->get();
			$admincharge=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('tierid',$tierid)->value('admincharge');
			
			$pricing	=	DB::table('pricing_tbl')->where('categoryid',$categoryid)->where('tierid',$tierid)->first();

			$userId		=	$request->session()->get('userId');
			
			//TIER 1 DATA
			$data	=	$this->priceService->getOrderTempData($userId);
			foreach($data as $dt)
			{
				$dt->withoperating	=	$dt->totalremuneration+$dt->operatingvalue;
				$dt->withtax		=	$dt->withoperating+$dt->taxvalue;
				$dt->withadmin		=	$dt->withtax+$dt->adminvalue;
			}
			
			$formhtml	=	view('/admin/ajaxpages/csforderformTable',compact('experiencelist','sectors','pricing','data','categoryid','tierid'))->render();

			return response()->json(['success'=>true,'formhtml'=>$formhtml]);
		}
	}

	public function addWorkOrderRecord(Request $request)
	{
		$userId    		= 	$request->session()->get('userId');
		
		$category_id= 	request('categoryid') ?? Session::get('category_id');
		$tier_id 	= 	request('categoryid') ?? Session::get('tier_id');
		
		$exists	=	DB::table('temp_work_order')->where('userid',$userId)->first();
		if($exists)
		{
			if($exists->categoryid!=$category_id)
			{
				return response()->json([
					'errors' => [
						'err_msg' => ['Invalid attempt'],
						]
					], 422);			
				
			}
		}
		if(Session::has('category_id'))
		{
			if(Session::get('category_id')==0)
			{
				Session::put('category_id',request('categoryid'));
			}
			if(Session::get('tier_id')==0)
			{
				Session::put('tier_id',request('tierid'));
			}
		}
		
		$rules = [
			'tierid'        	=> 	'required',
			'experience'        => 	'nullable|max:150',
			'remunerationid'    => 	'required',
			'duration'          => 	'required|numeric',
			'qualification'     => 	'required',
			'employmenttype'	=>	'required',
			'remark'			=>	'nullable',
			'projecttitle'		=>	'nullable',			
		];
        $messages = [
            'sectorid.required'			=> 'SECTOR NAME IS REQUIRED',
			'experience.max'			=> 'MAXIMUM 150CHARACTERS ALLOWED FOR EXPERIENCE',
			'categoryid.required'		=> 'CATEGORY NAME IS REQUIRED',
            'tierid.required' 			=> 'TIER NAME IS REQUIRED',
            'role.required'				=> 'ROLE IS REQUIRED',
            'remunerationid.required' 	=> 'EXPERIENCE IS REQUIRED',
			'qualification.required' 	=> 'QUALIFICATION IS REQUIRED',
			'duration.required' 		=> 'DURATION IS REQUIRED',
			'duration.numeric' 			=> 'INVALID DURATION VALUE',
			'projecttitle.required' 	=> 'PROJECT NAME IS REQUIRED',
			'projectduration.required' 	=> 'PROJECT DURATION IS REQUIRED',
			'employmenttype.required' 	=> 'EMPLOYMENT TYPE IS REQUIRED',
        ];

		// Base rules for categoryid, subrequirementtype, and tierid
		if(!Session::has('category_id'))
		{
			$rules['categoryid'] = 'required';
		}
		if(!Session::has('projecttitle'))
		{
			$rules['projecttitle'] = 'required';
		}
		if(!Session::has('projectduration'))
		{
			$rules['projectduration'] = 'required';
		}

		// Conditional rules based on categoryid
		if($category_id == 1)
		{
			$rules['role'] = 'required';
			$rules['sectorid'] = 'nullable';
		}
		if ($category_id == 2) {
			$rules['sectorid'] = 'required';
			$rules['role'] = 'nullable';
		}		


        $validatedData 	= $request->validate($rules,$messages);
		
		if(!Session::has('category_id'))
		{
			Session::put('category_id',$validatedData['categoryid']);
			Session::put('projecttitle',$validatedData['projecttitle']);
			Session::put('projectduration',$validatedData['projectduration']);
		}
		Session::put('tier_id',$validatedData['tierid']);
		DB::beginTransaction();
		try
		{
			
			$userName  		= 	$request->session()->get('userName');
			$userType  		= 	$request->session()->get('userType');
			
			$pricing		=	DB::table('pricing_tbl')->where('categoryid',$category_id)->where('tierid',$tier_id)->first();
			
			$remuneration	=	DB::table('remuneration_tbl')->where('remunerationid',$validatedData['remunerationid'])->first();
			
			$totalremuneration	=	$remuneration->remuneration*$validatedData['duration'];
			$operatingvalue		=	round((($totalremuneration*($pricing->operatingmargin ?? 0))/100),2);
			$withoperating		=	$totalremuneration+$operatingvalue;
			$taxvalue			=	round((($withoperating*($pricing->tax ?? 0))/100),2);
			$withtax			=	$withoperating+$taxvalue;
			$adminvalue			=	round((($withtax*($pricing->admincharge ?? 0))/100),2);
			$grandtotal			=	round(($withtax+$adminvalue),2);
			
			DB::table('temp_work_order')->where('userid',$userId)->where('categoryid',0)->delete();
			DB::table('temp_work_order')->insert([
				'projecttitle'		=>	session('projecttitle'),
				'projectduration'	=>	session('projectduration'),
				'categoryid'		=>	session('category_id'),
				'tierid'			=>	session('tier_id'),
				'sectorid'			=>	$remuneration->sectorid,
				'positionid'		=>	$remuneration->positionid,
				'role'				=>	$validatedData['role'] ?? '',
				'experience'		=>	$validatedData['experience'] ?? '',
				'employmenttype'	=>	$validatedData['employmenttype'],
				'remark'			=>	$validatedData['remark'] ?? '',
				'remunerationid'	=>	$validatedData['remunerationid'],
				'experiencelevel'	=>	$remuneration->experiencelevel,
				'qualification'		=>	$validatedData['qualification'],
				'remuneration'		=>	$remuneration->remuneration,
				'totalremuneration'	=>	$totalremuneration,
				'duration'			=>	$validatedData['duration'],
				'operating'			=>	$pricing->operatingmargin ?? 0,
				'operatingvalue'	=>	$operatingvalue,
				'tax'				=>	$pricing->tax ?? 0,
				'taxvalue'			=>	$taxvalue,
				'admincharge'		=>	$pricing->admincharge ?? 0,
				'adminvalue'		=>	$adminvalue,
				'grandtotal'		=>	$grandtotal,
				'userid'			=>	$userId,
				'creationdate'		=>	now(),
			]);
			
			
			$sectorid 		= 	$validatedData['sectorid'] ?? 0;
			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->select('a.remunerationid as value','b.consultantposition as label')
								->where('a.tierid','=',$tier_id)
								->where('a.categoryid','=',$category_id)
								->where('a.sectorid','=',$sectorid)
								->orderby('b.consultantposition')
								->get();

			$sectors		=	DB::table('sector_tbl')->where('categoryid',$category_id)->orderby('sectorname')->get();
			
			Session::put('duration_month',$validatedData['duration']);

			// TIER 1 DATA
			$data	=	$this->priceService->getOrderTempData($userId);
			
			foreach($data as $dt)
			{
				$dt->withoperating	=	$dt->totalremuneration+$dt->operatingvalue;
				$dt->withtax		=	$dt->withoperating+$dt->taxvalue;
				$dt->withadmin		=	$dt->withtax+$dt->adminvalue;
			}
			
			$html 	=	view('admin.ajaxpages.orderresourcedataTable',['data'=>$data,'categoryid'=>$category_id])->render();
			
			DB::commit();
			return response()->json(['status'=>200,'message'=>'RECORD SAVED SUCCESSFULLY!','formhtml'=>$html]);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['status'=>400,'message'=>$e->getMessage()]);
		}			
		
	}

    public function getVendorsList(Request $request)
	{
        $tierid 	= 	intval($request->input('tierid'));
		$categoryid = 	intval($request->input('categoryid'));
		$vendors	=	DB::table('users_tbl as a')
							->select('b.vendorid as value','b.companyname as label')
							->leftjoin('vendor_tbl as b','b.userid','=','a.userid')
							->where('a.isvendor',1)
							->whereNull('a.isSubVendor')
							->where('b.categoryid',$categoryid)
							->where('b.tierid',$tierid)
							->get();
        return response()->json($vendors);
    }

    public function storeDirectWorkOrder(Request $request)
	{
		$rules = [
			'categoryid'     	=>	'required|numeric',
			'tierid'     		=>	'required|numeric',
			'projecttitle'     	=>	'required|string',
			'projectduration'   =>	'required|numeric',
			'vendor_id'     	=>	'required|numeric',
			'department_id'     =>	'required|numeric',
			'orderno'			=>	'required|regex:/^[A-Za-z0-9.,()]+$/|max:100',
			'ordernumber'		=>	'required',
			'momfile' 			=> 	'nullable|file|mimetypes:application/pdf|max:2048',
			'markingsheet'		=> 	'nullable|file|mimetypes:application/pdf|max:2048',
			'notesheet'			=> 	'nullable|file|mimetypes:application/pdf|max:2048',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:2048',
			'orderdate' 		=> 	'required|date_format:d-m-Y|before_or_equal:today',
		];
		
        $messages = [
			'categoryid.required'		=>	'Category name is required.',
			'categoryid.numeric'		=>	'Invalid category name.',
			'tierid.required'			=>	'Tier name is required.',
			'tierid.numeric'			=>	'Invalid tier name.',
			'projecttitle.required'		=>	'Project name is required.',
			'projecttitle.string'		=>	'Invalid project name.',
			'projectduration.required'	=>	'Project duration is required.',
			'projectduration.numeric'	=>	'Invalid project duration.',
			'vendor_id.required'		=>	'Vendor name is required',
			'vendor_id.numeric'			=>	'Invalid vendorid',
			'department_id.required'	=>	'Department name is required',
			'department_id.numeric'		=>	'Invalid department name',
			'orderno.required'			=>	'Order prefix number is required',
			'orderno.regex'				=>	'Invalid order prefix number',
			'orderno.max'				=>	'Maximum 10 characters allowed',
			'ordernumber.required'		=>	'Order number is required',
			
			'momfile.file'				=>	'MoM must be a file',
			'momfile.mimetypes'			=>	'Invalid MoM file attached',
			'momfile.max'				=>	'Maximum file size is 2 MB',
			'markingsheet.file'			=>	'Marking sheet must be a file',
			'markingsheet.mimetypes'	=>	'Invalid marking sheet file attached',
			'markingsheet.max'			=>	'Maximum file size is 2 MB',
			'notesheet.file'			=>	'Note sheet must be a file',
			'notesheet.mimetypes'		=>	'Invalid note sheet file attached',
			'notesheet.max'				=>	'Maximum file size is 2 MB',
			'signedcopy.file'			=>	'Order signed copy must be a file',
			'signedcopy.mimetypes'		=>	'Invalid Order signed copy file attached',
			'signedcopy.max'			=>	'Maximum file size is 2 MB',
			'orderdate.required'		=>	'Order date is required',
			'orderdate.date_format'		=>	'Invalid order date provided',
			'orderdate.before_or_equal'	=>	'Invalid order date provided',
        ];

        $validatedData 	=	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		
		$exists	=	DB::table('temp_work_order')
						->where('categoryid',$validatedData['categoryid'])
						->where('tierid',$validatedData['tierid'])
						->first();
		if(!$exists)
		{
			return response()->json([
				'errors' => [
					'err_msg' => ['Some thing went wrong, please try again!.'],
					]
				], 422);			
			
		}

		$momfile		= 	$request->file('momfile');		
		$markingsheet	= 	$request->file('markingsheet');
		$notesheet		= 	$request->file('notesheet');
		$signedcopy		= 	$request->file('signedcopy');

		try
		{
			DB::beginTransaction();
			if($request->hasFile('momfile'))
			{
				$momfile= $request->file('momfile')->store('uploads/momfiles','public');
			}
			if($request->hasFile('markingsheet'))
			{
				$markingsheet= $request->file('markingsheet')->store('uploads/markingsheets','public');
			}
			if($request->hasFile('notesheet'))
			{
				$notesheet	= $request->file('notesheet')->store('uploads/notesheets','public');
			}
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	= $request->file('signedcopy')->store('uploads/signedorder','public');
			}
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$validatedData['categoryid'])
							->where('tierid',$validatedData['tierid'])
							->first();

			$department	=	DB::table('department_tbl')->where('userid',$validatedData['department_id'])->first();
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$validatedData['vendor_id'])->first();
			
			
			$copyto	=	$vendor->companyname.", ".$vendor->officelocation;
		
			

			$workOrderDuedate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['projectduration'])->format('Y-m-d');
			$nextReminderDate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['projectduration']-2)->format('Y-m-d');
			
			$orderid=	DB::table('eoi_work_order')->insertGetId([
							'categoryid'		=>	$validatedData['categoryid'],
							'orderno'			=>	$validatedData['orderno'],
							'ordernumber'		=>	$validatedData['orderno']."".$validatedData['ordernumber'],
							'vendorid'			=>	$vendor->vendorid,	
							'creationdate'		=>	now(),
							'createdby'			=>	$userId,
							'operatingmargin'	=>	$pricing->operatingmargin,
							'tax'				=>	$pricing->tax,
							'admincharge'		=>	$pricing->admincharge,
							'momfile'			=>	$momfile ?? NULL,
							'markingsheet'		=>	$markingsheet ?? NULL,
							'notesheet'			=>	$notesheet ?? NULL,
							'signedcopy'		=>	$signedcopy,
							'loinumber'			=>	$vendor->loinumber,
							'copyto'			=>	$copyto,
							'workorderduedate'	=>	$workOrderDuedate,
							'nextreminderdate'	=>	$nextReminderDate,
							'isfinalized'		=>	1,
							'finalizedby'		=>	$userName,
							'project_type'		=>	1,
							'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['orderdate'])),
							'userid'			=>	$department->userid,
							'project_duration'	=>	$validatedData['projectduration'],
							'project_name'		=>	$validatedData['projecttitle'],
							'department_id'		=>	$department->departmentid,
						]);
			
			$records	=	DB::table('temp_work_order')->where('userid',$userId)->get();
			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			$totaladmincharge	=	0;
			foreach($records as $record)
			{
				DB::table('eoi_resource_deployment')->insert([
					'orderid'			=>	$orderid,
					'deployment_status'	=>	'Pending',
					'remarks'			=>	$record->remark,
					'tierid'			=>	$record->tierid,
					'sectorid'			=>	$record->sectorid,
					'positionid'		=>	$record->positionid,
					'experience'		=>	$record->experience,
					'qualification'		=>	$record->qualification,
					'deploymenttype'	=>	$record->employmenttype,
					'duration'			=>	$record->duration,
					'remuneration'		=>	$record->remuneration,
					'baseprice'			=>	$record->remuneration,
					'operating'			=>	$record->operating,
					'operatingvalue'	=>	$record->operatingvalue,
					'tax'				=>	$record->tax,
					'taxvalue'			=>	$record->taxvalue,
					'admincharge'		=>	$record->admincharge,
					'adminvalue'		=>	$record->adminvalue,
					'grandtotal'		=>	$record->grandtotal,
					'remunerationid'	=>	$record->remunerationid
				]);
				
				$totalremuneration	=	$totalremuneration+($record->remuneration*$record->duration);
			}
			$totalbudget		=	round($totalremuneration+(($totalremuneration*$pricing->tax)/100),2);
			$totaladmincharge	=	round($totalbudget+(($totalbudget*$pricing->admincharge)/100),2);
			$workorderamount	=	$totalbudget+$totaladmincharge;

			DB::table('eoi_work_order')->where('orderid',$orderid)->update([
				'totalremuneration'	=>	$totalremuneration,
				'totalbudget'		=>	$totalbudget,
				'totaladmincharge'	=>	$totaladmincharge,
				'workorderamount'	=>	$workorderamount
			]);
			
			
			
			DB::commit();

			if($orderid)
			{
				session::pull('tier_id');
				Session::pull('category_id');
				session::pull('projecttitle');
				session::pull('projectduration');
				
				DB::table('temp_work_order')->where('userid',$userId)->delete();
				return back()->with('success','Order created successfully')->withInput();
			}
			else
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();	
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }

    public function addNewOrder(Request $request,$requestid)
	{
		$requestid	=	Crypt::decrypt($requestid);
		
		$eoi 		= 	DB::table('eoi_request as a')
						->select('a.*','b.departmentname')
						->join('department_tbl as b','b.userid','=','a.userid')
						->where('a.requestid',$requestid)
						->first();
		
		$vendor		=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber','a.orderdate','b.companyname','b.officelocation','c.tiername')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->join('tiermaster_tbl as c','c.tierid','=','b.tierid')
						->where('a.requestid','=',$eoi->requestid)
						->where('a.parentorderid','=',0)
						->first();
		
		
		$orders		=	DB::table('eoi_work_order as a')
						->select('a.*','b.companyname')
						->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
						->where('a.requestid','=',$eoi->requestid)
						->get();
		
		$resources	=	DB::table('eoi_resource_deployment as a')
						->select('a.deploymentid','a.name','a.mobilenumber','a.email','a.deployment_date','a.deployed_date','a.deployment_status','a.is_verified','a.role','a.experience','a.qualification','a.experiencelevel','a.remuneration','b.sectorname','c.consultantposition')
						->leftjoin('sector_tbl as b','b.sectorid','=','a.sectorid')
						->leftjoin('position_tbl as c','c.positionid','=','a.positionid')
						->where('a.orderid','=',$vendor->orderid)
						->where('a.deployment_status','=','Pending')
						->get();

		$isavailable=	DB::table('eoi_resource_deployment')
						->where('orderid',$vendor->orderid)
						->where('deployment_status','=','Active')
						->count();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/add_neworder', compact('eoi','token','vendor','orders','isavailable','resources'));
    }

	public function viewOrderResource(Request $request)
	{
		$orderid	=	Crypt::decrypt($request->input('orderid'));
		
		$order		=	DB::table('eoi_work_order')->where('orderid',$orderid)->first();
		
		$categoryid	=	$order->categoryid;
		
		if($order->categoryid==1)
		$resources	=	$this->deploymentService->getAwdDeploymentData($orderid);
		else
		$resources	=	$this->deploymentService->getCsfDeploymentData($orderid);
		
		$formhtml	=	view('/admin/ajaxpages/orderresourceTable',compact('resources','categoryid'))->render();

		return response()->json(['success'=>true,'formhtml'=>$formhtml]);

	}

    public function storeNewOrder(Request $request,$requestid)
	{
		$rules = [
			'notesheet'			=> 	'nullable|file|mimetypes:application/pdf|max:5120',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:5120',
			'orderno'			=>	'required|regex:/^[A-Za-z0-9.,()]+$/|max:100',
			'ordernumber'		=>	'required',
			'orderdate' 		=> 	'required|date_format:d-m-Y|before_or_equal:today',
			'project_duration'  =>	'required|numeric',
			'deploymentid' 		=> 	'required|array|min:1',
		];
		
        $messages = [
			'notesheet.file'			=>	'Note sheet must be a file',
			'notesheet.mimetypes'		=>	'Invalid note sheet file attached',
			'notesheet.max'				=>	'Maximum file size is 2 MB',
			'signedcopy.file'			=>	'Order signed copy must be a file',
			'signedcopy.mimetypes'		=>	'Invalid Order signed copy file attached',
			'signedcopy.max'			=>	'Maximum file size is 2 MB',
			'orderno.required'			=>	'Order prefix number is required',
			'orderno.regex'				=>	'Invalid order prefix number',
			'orderno.max'				=>	'Maximum 10 characters allowed',
			'ordernumber.required'		=>	'Order number is required',
			'orderdate.required'		=>	'Order date is required',
			'orderdate.date_format'		=>	'Invalid order date provided',
			'orderdate.before_or_equal'	=>	'Invalid order date provided',
			'project_duration.required'	=>	'Project duration is required.',
			'project_duration.numeric'	=>	'Invalid project duration.',
			'deploymentid.required'		=>	'At least one resource must be selected to create the order.',
			'deploymentid.array'		=>	'Invalid resource detail.',
			'deploymentid.min'			=>	'At least one resource must be selected to create the order.',
        ];

		$validatedData 	=	$request->validate($rules,$messages);

		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		$requestid		=	Crypt::decrypt($requestid);
		
		$exists	=	DB::table('eoi_request')
						->where('requestid',$requestid)
						->where('isordered',1)
						->first();
		if(!$exists)
		{
			return response()->json([
				'errors' => [
					'err_msg' => ['Some thing went wrong, please try again!.'],
					]
				], 422);			
			
		}

		$notesheet		= 	$request->file('notesheet');
		$signedcopy		= 	$request->file('signedcopy');

		try
		{
			DB::beginTransaction();
			if($request->hasFile('notesheet'))
			{
				$notesheet	= $request->file('notesheet')->store('uploads/notesheets','public');
			}
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	= $request->file('signedcopy')->store('uploads/signedorder','public');
			}
			$deploymentIds = $request->input('deploymentid');
			

			$department	=	DB::table('department_tbl')->where('userid',$exists->userid)->first();

			$vendor		=	DB::table('eoi_work_order as a')
							->select('a.orderid','a.ordernumber','a.orderdate','a.project_type','a.project_name','b.vendorid','b.companyname','b.officelocation','c.tiername','c.tierid','b.loinumber')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->join('tiermaster_tbl as c','c.tierid','=','b.tierid')
							->where('a.requestid','=',$requestid)
							->where('a.parentorderid','=',0)
							->first();

			
			
			$copyto		=	$vendor->companyname.", ".$vendor->officelocation;

			$workOrderDuedate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['project_duration'])->format('Y-m-d');
			$nextReminderDate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['project_duration']-2)->format('Y-m-d');
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$exists->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
			
			$orderid	=	DB::table('eoi_work_order')->insertGetId([
								'requestid'			=>	$exists->requestid,
								'parentorderid'		=>	$vendor->orderid,
								'categoryid'		=>	$exists->categoryid,
								'orderno'			=>	$validatedData['orderno'],
								'ordernumber'		=>	$validatedData['orderno']."/".strtoupper($validatedData['ordernumber']),
								'refrence'			=>	$exists->eoinumber,
								'vendorid'			=>	$vendor->vendorid,	
								'creationdate'		=>	now(),
								'createdby'			=>	$userId,
								'operatingmargin'	=>	$pricing->operatingmargin,
								'tax'				=>	$pricing->tax,
								'admincharge'		=>	$pricing->admincharge,
								'notesheet'			=>	$notesheet ?? NULL,
								'signedcopy'		=>	$signedcopy,
								'loinumber'			=>	$vendor->loinumber,
								'copyto'			=>	$copyto,
								'workorderduedate'	=>	$workOrderDuedate,
								'nextreminderdate'	=>	$nextReminderDate,
								'isfinalized'		=>	1,
								'finalizedby'		=>	$userName,
								'project_type'		=>	$vendor->project_type,
								'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['orderdate'])),
								'userid'			=>	$department->userid,
								'project_duration'	=>	$validatedData['project_duration'],
								'project_name'		=>	$vendor->project_name,
								'department_id'		=>	$department->departmentid,
							]);
			
			$records	=	DB::table('eoi_resource_deployment')
							->whereIn('deploymentid',$deploymentIds)
							->get();
			
			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			$totaladmincharge	=	0;
			foreach($records as $record)
			{
				DB::table('eoi_resource_deployment')
				->where('deploymentid',$record->deploymentid)
				->update([
					'orderid'	=>	$orderid,
					'duration'	=>	$validatedData['project_duration'],
				]);
				
				$totalremuneration	=	$totalremuneration+($record->remuneration*$validatedData['project_duration']);
			}
			$totalbudget		=	round($totalremuneration+(($totalremuneration*$pricing->tax)/100),2);
			$totaladmincharge	=	round($totalbudget+(($totalbudget*$pricing->admincharge)/100),2);
			$workorderamount	=	$totalbudget+$totaladmincharge;

			DB::table('eoi_work_order')->where('orderid',$orderid)->update([
				'totalremuneration'	=>	$totalremuneration,
				'totalbudget'		=>	$totalbudget,
				'totaladmincharge'	=>	$totaladmincharge,
				'workorderamount'	=>	$workorderamount
			]);
			
			
			
			DB::commit();

			if($orderid)
			{			
				return back()->with('success','Order created successfully');
			}
			else
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();	
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }

	public function extendOrder(Request $request,$orderid)
	{
		$orderid	=	Crypt::decrypt($orderid);
		
		$order		=	DB::table('eoi_work_order as a')
						->select('a.*','b.departmentname')
						->leftJoin('department_tbl as b','b.departmentid','=','a.department_id')
						->where('a.orderid',$orderid)
						->where('a.isExtended',0)
						->first();
		if(!$order)
		{
			return view('errors.extended_workorder_error', [
				'message' => 'The order you are looking for either does not exist or has already been extended. Please check and try again.',
			]);
		}
		$categoryid	=	$order->categoryid;
		
		if($order->categoryid==1)
		$resources	=	$this->deploymentService->getAwdDeploymentData($orderid);
		else
		$resources	=	$this->deploymentService->getCsfDeploymentData($orderid);
		
		if($order->requestid!=0)
		{
			$eoi	=	DB::table('eoi_request')->where('requestid',$order->requestid)->first();
		}
		else
		{
			$eoi	=	null;
		}
		
		$vendor	=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
		
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		return view('admin/master/extend_order', compact('eoi','token','vendor','order','resources'));
	}

    public function storeExtendOrder(Request $request,$orderid)
	{
		$rules = [
			'project_duration'  =>	'required|numeric',		
			'orderdate' 		=> 	'required|date_format:d-m-Y|before_or_equal:today',
			'subject' 			=> 	'required|regex:/^[A-Za-z0-9 .,()]+$/',
			'ordernumber' 		=> 'required|regex:/^[A-Za-z0-9.,()_\/-]+$/|max:100',
			'signedby' 			=> 'required|in:Chief Executive Officer (CEO),Jt. CEO (Project),Jt. CEO(Finance),Add. Chief Executive Officer,Chief Operating Officer',
			'signedcopy'		=> 	'required|file|mimetypes:application/pdf|max:5120',
		];
		
        $messages = [
			'project_duration.required'	=>	'Project duration is required.',
			'project_duration.numeric'	=>	'Invalid project duration.',
			'orderdate.required'		=>	'Order date is required',
			'orderdate.date_format'		=>	'Invalid order date provided',
			'orderdate.before_or_equal'	=>	'Invalid order date provided',
			'subject.required'			=>	'Please provide order subject.',
			'subject.regex'				=>	'Please check the content of subject only (.,()) provide order subject.',
			'ordernumber.required'		=>	'Order number is mandatory.',
			'ordernumber.regex'			=>	'Invalid order number provided.',
			'signedby.required'			=>	'Signed by is mandatory.',
			'signedby.in'				=>	'Invalid signed by name provided.',
			'signedcopy.file'			=>	'Order signed copy must be a file',
			'signedcopy.mimetypes'		=>	'Invalid Order signed copy file attached',
			'signedcopy.max'			=>	'Maximum file size is 2 MB',
        ];

		$validatedData 	=	$request->validate($rules,$messages);

		$resources	=	$request->input('resources',[]);
		$durations	= 	$request->input('duration',[]);

		if(empty($resources))
		{
			return response()->json([
				'errors' => [
					'err_msg' => ['Please select at least one resource!.'],
					]
				], 422);			
		}


		$userId			=	$request->session()->get('userId');
		$userName		= 	$request->session()->get('userName');
		
		$orderid		=	Crypt::decrypt($orderid);
		
		$order	=	DB::table('eoi_work_order')
						->where('orderid',$orderid)
						->first();
		if(!$order)
		{
			return response()->json([
				'errors' => [
					'err_msg' => ['No order details found. Please verify and try again.'],
					]
				], 422);			
			
		}
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		$signedcopy		= 	$request->file('signedcopy');

		try
		{
			DB::beginTransaction();
			
			$vendor	=	DB::table('')->first();
			
			$ord	=	explode("/",$validatedData['ordernumber']);
			$orderid	=	DB::table('eoi_work_order')->insertGetId([
								'requestid'			=>	$order->requestid,
								'parentorderid'		=>	$order->orderid,
								'categoryid'		=>	$order->categoryid,
								'orderno'			=>	$ord[0],
								'ordernumber'		=>	$validatedData['ordernumber'],
								'refrence'			=>	$order->refrence,
								'vendorid'			=>	$order->vendorid,	
								'creationdate'		=>	now(),
								'createdby'			=>	$userId,
								'operatingmargin'	=>	$pricing->operatingmargin,
								'tax'				=>	$pricing->tax,
								'admincharge'		=>	$pricing->admincharge,
								'notesheet'			=>	$notesheet ?? NULL,
								'signedcopy'		=>	$signedcopy,
								'loinumber'			=>	$vendor->loinumber,
								'copyto'			=>	$copyto,
								'workorderduedate'	=>	$workOrderDuedate,
								'nextreminderdate'	=>	$nextReminderDate,
								'isfinalized'		=>	1,
								'finalizedby'		=>	$userName,
								'project_type'		=>	$vendor->project_type,
								'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['orderdate'])),
								'userid'			=>	$department->userid,
								'project_duration'	=>	$validatedData['project_duration'],
								'project_name'		=>	$vendor->project_name,
								'department_id'		=>	$department->departmentid,
							]);

			
			if($request->hasFile('signedcopy'))
			{
				$signedcopy	= $request->file('signedcopy')->store('uploads/signedorder','public');
			}
			
			
			$deployments=	DB::table('eoi_resource_deployment')
							->where('orderid',$orderid)
							->where('deployment_status','Active')
							->where('isExtended',0)
							->get();
			
			$department	=	DB::table('department_tbl')->where('userid',$exists->userid)->first();

			$vendor		=	DB::table('eoi_work_order as a')
							->select('a.orderid','a.ordernumber','a.orderdate','a.project_type','a.project_name','b.vendorid','b.companyname','b.officelocation','c.tiername','c.tierid','b.loinumber')
							->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
							->join('tiermaster_tbl as c','c.tierid','=','b.tierid')
							->where('a.requestid','=',$requestid)
							->where('a.parentorderid','=',0)
							->first();

			
			
			$copyto		=	$vendor->companyname.", ".$vendor->officelocation;

			$workOrderDuedate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['project_duration'])->format('Y-m-d');
			$nextReminderDate = Carbon::parse($validatedData['orderdate'])->addMonths($validatedData['project_duration']-2)->format('Y-m-d');
			
			$pricing	=	DB::table('pricing_tbl')
							->where('categoryid',$exists->categoryid)
							->where('tierid',$vendor->tierid)
							->first();
			
			$orderid	=	DB::table('eoi_work_order')->insertGetId([
								'requestid'			=>	$exists->requestid,
								'parentorderid'		=>	$vendor->orderid,
								'categoryid'		=>	$exists->categoryid,
								'orderno'			=>	$validatedData['orderno'],
								'ordernumber'		=>	$validatedData['orderno']."/".strtoupper($validatedData['ordernumber']),
								'refrence'			=>	$exists->eoinumber,
								'vendorid'			=>	$vendor->vendorid,	
								'creationdate'		=>	now(),
								'createdby'			=>	$userId,
								'operatingmargin'	=>	$pricing->operatingmargin,
								'tax'				=>	$pricing->tax,
								'admincharge'		=>	$pricing->admincharge,
								'notesheet'			=>	$notesheet ?? NULL,
								'signedcopy'		=>	$signedcopy,
								'loinumber'			=>	$vendor->loinumber,
								'copyto'			=>	$copyto,
								'workorderduedate'	=>	$workOrderDuedate,
								'nextreminderdate'	=>	$nextReminderDate,
								'isfinalized'		=>	1,
								'finalizedby'		=>	$userName,
								'project_type'		=>	$vendor->project_type,
								'orderdate'			=>	date('Y\-m\-d',strtotime($validatedData['orderdate'])),
								'userid'			=>	$department->userid,
								'project_duration'	=>	$validatedData['project_duration'],
								'project_name'		=>	$vendor->project_name,
								'department_id'		=>	$department->departmentid,
							]);
			
			$records	=	DB::table('eoi_resource_deployment')
							->whereIn('deploymentid',$deploymentIds)
							->get();
			
			$r=0;
			$totalremuneration	=	0;
			$totalbudget		=	0;
			$totaladmincharge	=	0;
			foreach($records as $record)
			{
				DB::table('eoi_resource_deployment')
				->where('deploymentid',$record->deploymentid)
				->update([
					'orderid'	=>	$orderid,
					'duration'	=>	$validatedData['project_duration'],
				]);
				
				$totalremuneration	=	$totalremuneration+($record->remuneration*$validatedData['project_duration']);
			}
			$totalbudget		=	round($totalremuneration+(($totalremuneration*$pricing->tax)/100),2);
			$totaladmincharge	=	round($totalbudget+(($totalbudget*$pricing->admincharge)/100),2);
			$workorderamount	=	$totalbudget+$totaladmincharge;

			DB::table('eoi_work_order')->where('orderid',$orderid)->update([
				'totalremuneration'	=>	$totalremuneration,
				'totalbudget'		=>	$totalbudget,
				'totaladmincharge'	=>	$totaladmincharge,
				'workorderamount'	=>	$workorderamount
			]);
			
			
			
			DB::commit();

			if($orderid)
			{			
				return back()->with('success','Order created successfully');
			}
			else
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();	
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }


	public function workOrderConfirmation(Request $request, $requestid)
	{
		$userId	=	$request->session()->get('userId');

		try
		{
			$requestId	=	Crypt::decrypt($requestid);

			$key	= 	$request->key;
			$value	=	$request->value ? 1 : 0;

			$allowed = ['isInterviewDone', 'isMomReceived', 'isOrderFileReceived'];
			$labels = [
				'isInterviewDone' 		=> 	'Interview done confirmation given',
				'isMomReceived' 		=> 	'Evaluation sheet received confirmation given',
				'isOrderFileReceived' 	=> 	'Order file received confirmation given',
			];
			if(!in_array($key, $allowed))
			{
				return response()->json(['message' => 'Invalid key'], 400);
			}

			DB::beginTransaction();

			$updated = DB::table('eoi_request')
				->where('requestid', $requestId)
				->update([
					$key => $value,
				]);

			if(!$updated)
			{
				DB::rollBack();
				return response()->json(['message' => 'Record not found'], 404);
			}

			DB::table('workorder_confirmation')->insert([
				'requestid'   	=> 	$requestId,
				'particular'	=> 	$labels[$key] ?? 'Work order confirmation updated',
				'creationdate'	=> 	now(),
				'created_by'  	=> 	$userId
			]);
			DB::commit();
			return response()->json(['success' => true], 200);
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json([
				'message' => $e->getMessage()
			], 400);
		}
	}


    public function orderWiseResource(Request $request)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','orderwiseresource');
		
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');

		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$vendors	=	DB::table('vendor_tbl')->select('vendorid','companyname')->orderby('companyname')->get();
		$departments=	DB::table('users_tbl as a')
						->select('a.isdepartment','a.ispm','b.departmentid','b.departmentname','b.shortname')
						->join('department_tbl as b','b.userid','=','a.userid')
						->orderby('a.name')
						->get();

		$category	=	DB::table('jobcategory_tbl')->orderBy('categoryid')->get();
		
		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')->select('experiencelevel')->where('experiencelevel','!=',0)->distinct('experiencelevel')->orderBy('experiencelevel')->get();
		
		return view('admin/master/orderwise_resource',compact('category','vendors','departments','sectors','positions','levels','token'));
    }
    public function getOrderWiseResource(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('categoryid');
		$vendorid 		=	$request->input('vendorid');
		$departmentid	=	$request->input('departmentid');
		$sectorid		=	$request->input('sectorid') ?? NULL;
		$positionid		=	$request->input('positionid') ?? NULL;
		$experiencelevel=	$request->input('experiencelevel') ?? NULL;
		$order_type		=	$request->input('order_type');
		$pagesearch		=	$request->input('pagesearch');
		
		$data	=	$this->deploymentService->getOrderWiseResource($request,$categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$order_type,$pagesearch);

		$sectors	=	DB::table('sector_tbl')->orderBy('sectorname')->get();
		
		$positions	=	DB::table('position_tbl')->orderBy('consultantposition')->get();
		
		$levels	=	DB::table('remuneration_tbl')->select('experiencelevel')->where('experiencelevel','!=',0)->distinct('experiencelevel')->orderBy('experiencelevel')->get();
		
		return view('admin/ajaxpages/orderwiseresourceTable', ['data' => $data,'sectors'=>$sectors,'positions'=>$positions,'levels'=>$levels]);

    }

	public function updateResourceDetail(Request $request)
	{
		$rules = [
		
			'orderid'     	=> 	'required|exists:eoi_work_order,orderid',
			'vendorid'     	=> 	'required|exists:vendor_tbl,vendorid',
			'deploymentid'  => 	'required|exists:eoi_resource_deployment,deploymentid',
			'employee_code' => 	'nullable|max:100',
			'name'          => 	'nullable|max:150',
			'mobilenumber'  => 	'nullable|digits:10',
			'email' 		=> 	'nullable|email|max:150',

			'deployed_date' => 	'nullable|date_format:d-m-Y',

			'levels'        => 	'nullable',
			'sectorid'      => 	'nullable|numeric',
			'positionid'    => 	'nullable|numeric',
		];

		$messages = [
			'orderid.required'    	   => 'Order ID is required.',
			'orderid.exists'      	   => 'Invalid order detail provided.',

			'vendorid.required'    	   => 'Firm ID is required.',
			'vendorid.exists'      	   => 'Invalid firm detail provided.',

			'deploymentid.required'    => 'Deployment ID is required.',
			'deploymentid.exists'      => 'Invalid deployment selected.',

			'employee_code.required'   => 'Employee code is required.',
			'employee_code.max'        => 'Maximum 100 characters allowed in employee code.',

			'name.required'            => 'Employee name is required.',
			'name.max'                 => 'Maximum 150 characters allowed in name.',

			'mobilenumber.required'    => 'Mobile number is required.',
			'mobilenumber.digits'      => 'Mobile number must be 10 digits.',

			'email.required'           => 'Email address is required.',
			'email.email'              => 'Invalid email address provided.',
			'email.max'                => 'Maximum 150 characters allowed in email.',

			'deployed_date.date_format'=> 'Deployment date must be in dd-mm-YYYY format.',

			'sectorid.numeric'         => 'Invalid sector selected.',
			'positionid.numeric'       => 'Invalid position selected.',
		];

		$validatedData = $request->validate($rules, $messages);		

		try 
		{
			$order		=	DB::table('eoi_work_order')->where('orderid',$validatedData['orderid'])->first();
			$deployment	=	DB::table('eoi_resource_deployment')->where('deploymentid',$validatedData['deploymentid'])->first();

			if($validatedData['deployed_date'])
			{
				$validatedData['deployed_date']	=	date('Y\-m\-d',strtotime($validatedData['deployed_date']));
			}
			else
			{
				$validatedData['deployed_date']	=	NULL;
			}
			
			$data = [
				'employee_code'		=> 	$validatedData['employee_code'] ?? NULL,
				'name'				=> 	$validatedData['name'] ?? NULL,
				'mobilenumber'		=> 	$validatedData['mobilenumber'] ?? NULL,
				'email'				=> 	$validatedData['email'] ?? NULL,
				'deployed_date'		=>	$validatedData['deployed_date'] ?? NULL,
			];
			
			$plainPassword	= 	$this->passwordService->generatePassword();
			$hashedPassword = 	Hash::make($plainPassword);
			
			$resource	=	DB::table('eoi_resource_deployment')->where('deploymentid',$deployment->deploymentid)->first();
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
					'categoryid'		=>	$categoryid,
					'tierid'			=>	$resource->tierid,
					'sectorid'			=>	$resource->sectorid,
					'positionid'		=>	$resource->positionid,
					'role'				=>	$resource->role,
					'experiencelevel'	=>	$resource->experiencelevel,
					'remuneration'		=>	$resource->remuneration,
					'operating'			=>	$resource->operating,
					'operatingvalue'	=>	$resource->operatingvalue,
					'operatingvalue'	=>	$resource->operatingvalue,
					'tax'				=>	$resource->tax,
					'taxvalue'			=>	$resource->taxvalue,
					'admin'				=>	$resource->admincharge,
					'admincharge'		=>	$resource->adminvalue,
					'deployed_date'		=>	$validatedData['deployed_date'] ?? NULL,
					'joining_date'		=>	$validatedData['deployed_date'] ?? NULL,
					'deploymenttype'	=>	$resource->deploymenttype,
					'duration'			=>	$resource->duration,
					'rateid'			=>	$resource->rateid,					
				]);
				
				DB::table('eoi_resource_deployment')
				->where('deploymentid',$deployment->deploymentid)
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
					'orderid'			=>	$deployment->orderid,
					'employee_code'		=>	$validatedData['employee_code'] ?? NULL,
					'categoryid'		=>	$order->categoryid,
					'tierid'			=>	$deployment->tierid,
					'sectorid'			=>	$deployment->sectorid,
					'positionid'		=>	$deployment->positionid,
					'role'				=>	$deployment->role,
					'experiencelevel'	=>	$deployment->experiencelevel,
					'remuneration'		=>	$deployment->remuneration,
					'operating'			=>	$deployment->operating,
					'operatingvalue'	=>	$deployment->operatingvalue,
					'tax'				=>	$deployment->tax,
					'taxvalue'			=>	$deployment->taxvalue,
					'admin'				=>	$deployment->admincharge,
					'admincharge'		=>	$deployment->adminvalue,
					'deployment_date'	=>	$deployment->deployment_date,
					'deployed_date'		=>	$deployment->deployed_date,
					'joining_date'		=>	$deployment->deployment_date,
					'rateid'			=>	$deployment->rateid,
				]);

			}
				
		
			DB::table('eoi_resource_deployment')
			->where('orderid',$validatedData['orderid'])
			->where('deploymentid',$validatedData['deploymentid'])
			->update($data);
					
			DB::commit();
			
			return response()->json([
				'status'	=>	200,
				'message' 	=>	'Resource updated successfully',
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


    public function replaceResourceInOrder(Request $request,$deploymentid=NULL)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','workorderlist');
		Session::put('menid',96);
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
						->select('a.orderid','a.ordernumber','a.orderdate','a.workorderduedate','a.categoryid','a.project_name','b.eoinumber','b.projectduration','c.departmentname','d.companyname','d.officialemail')
						->leftJoin('eoi_request as b','b.requestid','=','a.requestid')
						->leftJoin('department_tbl as c','c.departmentid','=','a.department_id')
						->leftJoin('vendor_tbl as d','d.vendorid','=','a.vendorid')
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
		
		return view('admin/master/resource_replacement',compact('sectors','positions','levels','token','deployment','order'));
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
			'role'   			=> 	'nullable',
			'experiencelevel'   => 	'nullable|numeric',
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
			$plainPassword 	= 	$this->passwordService->generatePassword();
			DB::beginTransaction();
			$supporting_document	=	"";
			if($request->hasFile('supporting_document'))
			{
				$supporting_document= $request->file('supporting_document')->store('uploads/supporting_files','public');
			}

			$deployment		=	DB::table('eoi_resource_deployment')->where('deploymentid',$validatedData['deploymentid'])->first();

			$order			=	DB::table('eoi_work_order')->where('orderid',$deployment->orderid)->first();
			$vendor		=	DB::table('vendor_tbl')->where('vendorid',$order->vendorid)->first();
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
							->where('rateid',$rateid)
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->where('sectorid',$validatedData['sectorid'])
							->where('positionid',$validatedData['positionid'])
							->first();
			}
			if($order->categoryid==1)
			{
				$price	=	DB::table('remuneration_tbl')
							->where('rateid',$rateid)
							->where('categoryid',$vendor->categoryid)
							->where('tierid',$vendor->tierid)
							->where('experiencelevel',$validatedData['experiencelevel'])
							->first();
			}


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
				'remuneration'		=>	$price->remuneration,
				'operating'			=>	$pricing->operatingmargin,
				'tax'				=>	$pricing->tax,
				'admin'				=>	$pricing->admincharge,
				'deployed_date'		=>	$validatedData['joining_date'] ?? NULL,
				'joining_date'		=>	$validatedData['joining_date'] ?? NULL,
				'deploymenttype'	=>	$deployment->deploymenttype,
				'rateid'			=>	$rateid
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
										'role'				=>	$validatedData['role'] ?? NULL,
										'experiencelevel'	=>	$validatedData['experiencelevel'] ?? 0,
										'deploymenttype'	=>	$deployment->deploymenttype,
										'duration'			=>	$deployment->duration,
										'remuneration'		=>	$price->remuneration,
										'operating'			=>	$pricing->operatingmargin,
										'tax'				=>	$pricing->tax,
										'admincharge'		=>	$pricing->admincharge,
										'rateid'			=>	$rateid,
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
			return redirect()->route('order.resource')->with('success', 'Resource replacement details saved successfully.');
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


    public function cancelWorkOrder(Request $request,$orderid)
	{
		$userId    	= 	$request->session()->get('userId');
		$orderid 	=	Crypt::decrypt($orderid);
		
        $data 		= 	DB::table('eoi_work_order as a')
						->select('a.orderid','a.orderdate','a.workorderduedate','a.subject','a.ordernumber','a.refrence','b.departmentname','c.ispm','a.isClosed','a.isCancelled')
						->join('department_tbl as b','b.departmentid','=','a.department_id')
						->join('users_tbl as c','c.userid','=','b.userid')
						->where('a.orderid','=',$orderid)
						->first();
		

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		
		return view('admin/master/cancel_workorder',compact('data','token'));
		
    }

    public function cancelConfirmation(Request $request)
	{
		$request->merge([
			'orderid' => Crypt::decrypt($request->orderid),
		]);
		
        $rules = [
			'orderid'		=>	'required|exists:eoi_work_order,orderid',
			'cancelled_on'	=> 	['nullable', 'date_format:d-m-Y'],
			'cancellation_file' 	=> 	'nullable|file|mimes:pdf|max:5120',
        ];

		$messages = [
			'orderid.required'		=>	'Work order detail is required.',
			'orderid.exists'      	=>	'The selected work order detail is invalid.',

			'cancelled_on.required'		=>	'Cancel date is mandatory.',
			'cancelled_on.date_format'  =>	'Closing date is not valid.',

			'cancellation_file.required'	=> 	'Project closure file is required',
			'cancellation_file.file'		=> 	'Invalid closure file',
			'cancellation_file.mimes'	=> 	'Invalid closure file',
			'cancellation_file.max'		=> 	'Maximum 5 MB file is allowed',
		];
	
		$validatedData 	= $request->validate($rules,$messages);

		$userId	=	$request->session()->get('userId');
		try
		{
			$order	=	DB::table('eoi_work_order')
						->where('orderid',$validatedData['orderid'])
						->first();

			/*
			$havingActiveOrder	=	DB::table('eoi_work_order')
									->where('requestid',$validatedData['requestid'])
									->whereDate('workorderduedate','>=',today())
									->exists();

			if($havingActiveOrder)
			{
				return back()->withErrors([
					'activeorder'	=>	'This EOI already has an active work order. Please check and try again.'
				])->withInput();
				
			}
			*/
			if($validatedData['cancelled_on'])
			{
				$validatedData['cancelled_on']	=	date('Y\-m\-d',strtotime($validatedData['cancelled_on']));
			}
			$cancellation_file	=	(String) $request->file('cancellation_file');

			if($cancellation_file!='')
			{
				$cancellation_file= $request->file('cancellation_file')->store('uploads/cancellation_file', 'public');
			}		
			
			DB::transaction(function () use ($validatedData,$cancellation_file) {

				DB::table('eoi_work_order')
					->where('orderid', $validatedData['orderid'])
					->update([
						'isActiveOrder' 	=> 	0,
						'isCancelled'		=>	1,
						'cancelled_on'		=>	$validatedData['cancelled_on'] ?? NULL,
						'cancellation_file'	=>	$cancellation_file ?? NULL
					]);

				DB::table('eoi_resource_deployment')
					->where('orderid',$validatedData['orderid'])
					->update([
						'deployment_status' => 'Cancelled'
					]);
			});

		
			$message	=	"Work order number ".$order->ordernumber." has been successfully cancelled along with all associated resources.";
			return back()->with('success',$message);
		}
		catch(QueryException $e)
		{
			Log::error('Error'.$e->getMessage());
			return back()->with('duplicate','Something went wrong, please try again after some time.')->withInput();
		}
    }

	public function addResourceWorkorder(Request $request)
	{
		$request->merge([
			'category_id' 	=> 	Crypt::decrypt($request->category_id),
			'order_id' 		=> 	Crypt::decrypt($request->order_id),
			'vendor_id' 	=> 	Crypt::decrypt($request->vendor_id),
		]);
		
		$rules = [
			'vendor_id'  			=> 	'required|exists:vendor_tbl,vendorid',
			'category_id'  			=> 	'required|exists:jobcategory_tbl,categoryid',
			'order_id'  			=> 	'required|exists:vendor_tbl,vendorid',
			'resource_start_date'  	=> 	'nullable|date_format:d-m-Y',
			'resource_end_date'  	=> 	'nullable|date_format:d-m-Y',
			'resource_code' 		=> 	'nullable|max:20',
			'resource_name'         => 	'nullable|max:150|regex:/^[A-Za-z\s]+$/',
			'resource_mobile'  		=> 	'nullable|digits:10',
			'resource_email' 		=> 	'nullable|email:rfc,dns|max:150',
			'resource_deployed_date'=> 	'nullable|date_format:d-m-Y',
			'experience_level'   	=> 	'nullable|numeric',
			'sector_id'      		=> 	'nullable|numeric',
			'position_id'    		=> 	'nullable|numeric',
			'deployment_type' 		=> 	'required|in:FULL TIME,PART TIME',
		];

		$messages = [
			'vendor_id.required'      			=> 'Invalid firm detail provided.',
			'vendor_id.exists'      			=> 'Invalid firm detail detail provided.',

			'category_id.required'      		=> 'Invalid detail provided.',
			'category_id.exists'      			=> 'Invalid detail provided.',

			'order_id.required'      			=> 'Invalid order detail provided.',
			'order_id.exists'      				=> 'Invalid order detail provided.',
			
			'resource_start_date.date_format'   => 'Start date must be in dd-mm-YYYY format.',
			'resource_end_date.date_format'     => 'End date must be in dd-mm-YYYY format.',

			'resource_code.required'            => 'Resource code is required.',
			'resource_code.max'                 => 'Maximum 20 characters are allowed for the resource code.',

			'resource_name.required'            => 'Resource name is required.',
			'resource_name.max'                 => 'Maximum 150 characters are allowed for the resource name.',
			'resource_name.regex'               => 'Resource name may contain only letters, spaces, and dots.',

			'resource_mobile.required'          => 'Mobile number is required.',
			'resource_mobile.digits'            => 'Mobile number must be exactly 10 digits.',

			'resource_email.required'           => 'Email address is required.',
			'resource_email.email'              => 'Please provide a valid email address.',
			'resource_email.max'                => 'Maximum 150 characters are allowed for the email address.',

			'resource_deployed_date.date_format'=> 'Joining date must be in dd-mm-YYYY format.',

			'experience_level.numeric'          => 'Invalid experience level selected.',
			'sector_id.numeric'                 => 'Invalid sector selected.',
			'position_id.numeric'               => 'Invalid position selected.',

			'deployment_type.required'          => 'Deployment type is required.',
			'deployment_type.in'                => 'Deployment type must be either FULL TIME or PART TIME.',
		];
		
		$validatedData = $request->validate($rules, $messages);
		
		if($validatedData['resource_start_date']!='')
		{
			$validatedData['resource_start_date']	=	date('Y\-m\-d',strtotime($validatedData['resource_start_date']));
		}
		if($validatedData['resource_end_date']!='')
		{
			$validatedData['resource_end_date']	=	date('Y\-m\-d',strtotime($validatedData['resource_end_date']));
		}
		if($validatedData['resource_deployed_date']!='')
		{
			$validatedData['resource_deployed_date']	=	date('Y\-m\-d',strtotime($validatedData['resource_deployed_date']));
		}
		try
		{
			if($validatedData['sector_id'] && $validatedData['position_id'])
			{
				$exists	=	DB::table('vendor_sector')->where('vendorid',$validatedData['sector_id'])->exists();
				if(!$exists)
				{
					return response()->json([
						'errors' => [
							'err_msg' => ['The selected sector and position combination is not valid for the selected vendor.'],
							]
						], 422);					
				}
			}
			$plainPassword 	= 	$this->passwordService->generatePassword();
			DB::beginTransaction();


			$order			=	DB::table('eoi_work_order')->where('orderid',$validatedData['order_id'])->first();
			$vendor			=	DB::table('vendor_tbl')->where('vendorid',$validatedData['vendor_id'])->first();
			
			if($vendor->categoryid==2)
			{
				$rateid			=	DB::table('remuneration_rate_list')
									->where('categoryid',$vendor->categoryid)
									->where('isactive',1)
									->value('rateid');
									
				$remuneration	=	DB::table('remuneration_tbl')
									->where('rateid',$rateid)
									->where('categoryid',$vendor->categoryid)
									->where('tierid',$vendor->tierid)
									->where('sectorid',$validatedData['sector_id'])
									->where('positionid',$validatedData['position_id'])
									->get();
				
				$price			=	DB::table('pricing_tbl')->where('categoryid',$vendor->categoryid)->where('tierid',$vendor->tierid)-first();
			}

			$user_id		=	DB::table('users_tbl')->insertGetId([
									'name'			=>	$validatedData['resource_name'] ?? NULL,
									'mobilenumber'	=>	$validatedData['resource_mobile'] ?? NULL,
									'email'			=>	$validatedData['resource_email'] ?? NULL,
									'password'		=>	Hash::make($plainPassword),
									'pass_word'		=>	$plainPassword,
								]);
			
			DB::table('resource_tbl')->insert([
				'orderid'			=>	$validatedData['order_id'],
				'userid'			=>	$user_id,
				'employee_code'		=>	$validatedData['resource_code'] ?? NULL,
				'categoryid'		=>	$vendor->categoryid,
				'tierid'			=>	$vendor->tierid,
				'sectorid'			=>	$validatedData['sector_id'] ?? 0,
				'positionid'		=>	$validatedData['position_id'] ?? 0,
				'role'				=>	$validatedData['resource_role'] ?? 0,
				'experiencelevel'	=>	$validatedData['experience_level'] ?? 0,
				'remuneration'		=>	$remuneration->remuneration,
				'operating'			=>	$price->operatingmargin,
				'tax'				=>	$price->tax,
				'admin'				=>	$price->admincharge,
				'deployed_date'		=>	$validatedData['resource_deployed_date'] ?? NULL,
				'joining_date'		=>	$validatedData['resource_deployed_date'] ?? NULL,
				'deploymenttype'	=>	$validatedData[''],
				'rateid'			=>	$deployment->rateid
			]);
			
			$new_deployment_id	=	DB::table('eoi_resource_deployment')
									->insertGetId([
										'orderid'			=>	$validatedData['order_id'],
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
			return redirect()->route('workorder.list')->with('success', 'Resource replacement details saved successfully.');
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

	
    public function exportOrderWiseResourceData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');

		$categoryid 	=	$request->input('categoryid');
		$vendorid 		=	$request->input('vendorid');
		$departmentid	=	$request->input('departmentid');
		$sectorid		=	$request->input('sectorid') ?? NULL;
		$positionid		=	$request->input('positionid') ?? NULL;
		$experiencelevel=	$request->input('experiencelevel') ?? NULL;
		$order_type		=	$request->input('order_type');
		$pagesearch		=	$request->input('pagesearch');
		


		$fileName		=	'order_wise_resource_data_' . date('Ymd_His') . '.csv';
        $headers = [
            "Content-Type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];


		$callback = function () use ($categoryid,$vendorid,$departmentid,$sectorid,$positionid,$experiencelevel,$order_type,$pagesearch) {

			$handle = fopen('php://output','w');
			
			fwrite($handle, "\xEF\xBB\xBF");
			
			if($categoryid==1)
			{
				fputcsv($handle, [
					'S.No','Project Name','Project Manager','Firm Name','Work Order Number','Order Date','Order Due Date','Code','Name','Mobile Number','Email','Role','Experience Level','Joining Date','Status','Remark / Role'
				]);
			}
			if($categoryid==2)
			{
				fputcsv($handle, [
					'S.No','Project Name','Department Name','Firm Name','Work Order Number','Order Date','Order Due Date','Code','Name','Mobile Number','Email','Sector','Position','Joining Date','Status','Remark / Role'
				]);
			}
			$sno = 1;
			$select = [
				'g.project_name',
				'e.departmentname',
				'f.companyname',
				'd.ordernumber',
				'd.orderdate',
				'd.workorderduedate',
				'a.employee_code',
				'a.name',
				'a.mobilenumber',
				'a.email',
			];

			if ($categoryid == 1) {
				$select[] = 'a.role';
				$select[] = 'a.experiencelevel';
			} elseif ($categoryid == 2) {
				$select[] = 'b.sectorname';
				$select[] = 'c.consultantposition';
			}
			$select[] = 'a.deployed_date';
			$select[] = 'a.deployment_status';
			$select[] = 'a.remark';
			DB::table('eoi_resource_deployment as a')
				->select($select)
				->join('sector_tbl as b','b.sectorid','=','a.sectorid')
				->join('position_tbl as c','c.positionid','=','a.positionid')
				->join('eoi_work_order as d','d.orderid','=','a.orderid')
				->join('department_tbl as e','e.departmentid','=','d.department_id')
				->join('vendor_tbl as f','f.vendorid','=','d.vendorid')
				->leftJoin('project_tbl as g','g.projectid','=','d.projectid')
				->when($sectorid!=NULL,function($query) use($sectorid) {
					return $query->where('a.sectorid','=',$sectorid);
				})                              
				->when($positionid!=NULL,function($query) use($positionid) {
					return $query->where('a.positionid','=',$positionid);
				})                              
				->when($experiencelevel!=NULL,function($query) use($experiencelevel) {
					return $query->where('a.experiencelevel','=',$experiencelevel);
				})                              
				->when($categoryid!=NULL,function($query) use($categoryid) {
					return $query->where('d.categoryid','=',$categoryid);
				})                              
				->when($vendorid!=NULL,function($query) use($vendorid) {
					return $query->where('d.vendorid','=',$vendorid);
				})                              
				->when($departmentid!=NULL,function($query) use($departmentid) {
					return $query->where('d.department_id','=',$departmentid);
				})                              
				->when($order_type==1,function($query) {
					return $query->where('d.isActiveOrder','=',1)
								 ->where('d.workorderduedate','>',now())
								 ->where('d.isExtended','=',0);
				})								
				->when($order_type==2,function($query) {
					return $query->where('d.isExtended','=',1)
								 ->where('d.workorderduedate','>',now());
				})								
				->when($order_type==3,function($query) {
					return $query->where('d.workorderduedate','<',date('Y\-m\-d'));
				})								
				->when($order_type == 4, function ($query) {
					return $query->whereBetween('d.workorderduedate', [
						date('Y-m-d'), 
						date('Y-m-d', strtotime('+60 days'))
					]);
				})
				->when($pagesearch != '', function ($query) use ($pagesearch) {
					$query->where(function ($q) use ($pagesearch) {
						$q->where('a.name', 'like', "%{$pagesearch}%")
						  ->orWhere('d.ordernumber', 'like', "%{$pagesearch}%")
						  ->orWhere('f.companyname', 'like', "%{$pagesearch}%");
					});
				})
				->orderBy('d.workorderduedate')
				->chunk(500, function ($rows) use ($handle, &$sno) {

					foreach($rows as $row)
					{
						$row->orderdate = "\t" . Carbon::parse($row->orderdate)->format('d-m-Y');
						$row->workorderduedate = "\t" . Carbon::parse($row->workorderduedate)->format('d-m-Y');
						if($row->deployed_date)
						{
							$row->deployed_date = "\t" . Carbon::parse($row->deployed_date)->format('d-m-Y');
						}
						if(!empty($row->remark))
						{
							$row->remark = html_entity_decode(
								strip_tags(
									str_replace(
										['</p>', '</div>', '</li>', '<br>', '<br/>', '<br />'],
										["\n", "\n", "\n", "\n", "\n", "\n"],
										$row->remark
									)
								),
								ENT_QUOTES | ENT_HTML5,
								'UTF-8'
							);
							$row->remark = preg_replace("/\n{3,}/", "\n\n", trim($row->remark));
						}
						fputcsv($handle, array_merge([$sno++], (array) $row));
					}
				});

			fclose($handle);
		};
		return response()->stream($callback,200,$headers);
    }


    public function updateDueDate(Request $request)
	{
        Session::put('adminmenu','adminworkorder');
		Session::put('adminsubmenu','updateduedate');
		
		return view('admin/master/update_duedate');
    }

    public function getOrderList(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	= 	$request->input('page', 1);

        $data 			= 	DB::table('eoi_work_order as a')
								->select('a.*','b.jobcategory','c.companyname','c.contactperson','f.departmentname','g.project_name',DB::raw('CASE WHEN pd.orderid IS NULL THEN 1 ELSE 0 END as isDeployed'),DB::raw("(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.name IS NOT NULL AND erd.deployment_status!='Released') as resourcedeployed"),DB::raw("(SELECT COUNT(*) FROM eoi_resource_deployment erd WHERE erd.orderid = a.orderid AND erd.deployment_status!='Released') as totalresources"))
								->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
								->leftJoin('vendor_tbl as c','c.vendorid','=','a.vendorid')
								->leftJoin('pending_deployment as pd', 'pd.orderid', '=', 'a.orderid')
								->leftJoin('eoi_request as e', 'e.requestid', '=', 'a.requestid')
								->leftJoin('department_tbl as f', 'f.departmentid', '=', 'a.department_id')
								->leftJoin('project_tbl as g','g.projectid','=','a.projectid')
								->when($pagesearch != '', function ($query) use ($pagesearch) {
									$query->where(function ($q) use ($pagesearch) {
										$q->where('a.ordernumber', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.refrence', 'like', '%' . $pagesearch . '%')
										  ->orWhere('a.project_name', 'like', '%' . $pagesearch . '%')
										  ->orWhere('f.departmentname', 'like', '%' . $pagesearch . '%')
										  ->orWhere('c.companyname', 'like', '%' . $pagesearch . '%');
									});
								})
								->orderBy('a.orderdate','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		foreach($data as $dt)
		{
			$dt->expiring 			= 	Carbon::parse($dt->workorderduedate)->format('d-m-Y');
			$dt->days_to_expire 	= 	Carbon::now()->diffInDays(Carbon::parse($dt->expiring), false);

		}
		return view('admin/ajaxpages/orderlistTable', ['data' => $data]);
    }

	public function updateWorkOrderDueDate(Request $request)
	{
		$request->merge([
			'workorderduedate' => Carbon::parse($request->workorderduedate)->format('Y-m-d')
		]);
		
		$request->validate([
			'orderid' 			=> 	'required',
			'workorderduedate' 	=>	'required|date',
		]);

		DB::table('eoi_work_order')
			->where('orderid', $request->orderid)
			->update([
				'workorderduedate' => date('Y-m-d', strtotime($request->workorderduedate)),
			]);

		return response()->json([
			'status' 	=> 	true,
			'message' 	=> 	'Work order due date updated successfully.'
		]);
	}	
}
