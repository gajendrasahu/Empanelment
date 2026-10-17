<?php

namespace App\Http\Controllers\Finance;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use App\Services\Finance\FinanceService;
use App\Helpers\FinanceHelper;
class FinanceReportingController extends Controller
{
	protected $financeService;
	
	public function __construct(FinanceService $financeService)
	{
		$this->financeService 		= 	$financeService;
	}

	public function departmentWiseFundBalance(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','deptwisefundbalance');

		$financialYears = FinanceHelper::financialYears();

		$departments = DB::table('department_tbl')->select('userid','departmentname','shortname')->where('isprojectmanager',0)->orderBy('departmentname')->get();

		return view('finance.reporting.department_wise_fund_balance',compact('financialYears','departments'));
	}


	public function getDepartmentWiseFundBalance(Request $request)
	{
		$financialYear	=	$request->financial_year;
		$departmentId 	= 	$request->departmentid;
		$fundType 		= 	$request->fund_type;
		$reportFormat 	=	$request->report_format ?? '1';

		$departmentsQuery = DB::table('department_tbl')->where('isprojectmanager',0);

		if($departmentId)
		{
			$departmentsQuery->where('userid',$departmentId);
		}

		$departments	=	$departmentsQuery->select('userid','departmentname','shortname')->orderBy('departmentname')->get();
		$financialYears = 	$financialYear ? collect([$financialYear]) : collect(FinanceHelper::financialYears());

		$financialYears = 	$financialYears->sortBy(function($year){
								return (int)substr($year,0,4);
							})->values();

		$reportData = collect();

		foreach($departments as $department)
		{
			$projects	=	DB::table('eoi_request as e')
							->leftJoin('users_tbl as u','u.userid','=','e.userid')
							->where('e.categoryid',2)
							->where('u.ispm',0)
							->where('e.iscancelled',0)
							->where('e.userid',$department->userid)
							->select('e.requestid','e.eoinumber','e.projecttitle')
							->get();

			$departmentOpeningVendor	= 	0;
			$departmentOpeningService 	= 	0;
			$departmentReceiptVendor 	= 	0;
			$departmentReceiptService 	= 	0;
			$departmentUtilizedVendor 	= 	0;
			$departmentUtilizedService 	= 	0;
			$departmentClosingVendor 	=	0;
			$departmentClosingService 	= 	0;

			foreach($projects as $project)
			{
				$vendorClosing = 0;
				$serviceClosing = 0;
				$firstYear = true;
				$projectOpeningVendor 	= 	0;
				$projectOpeningService 	= 	0;
				$projectReceiptVendor 	= 	0;
				$projectReceiptService 	= 	0;
				$projectUtilizedVendor 	= 	0;
				$projectUtilizedService	=	0;

				foreach($financialYears as $year)
				{
					$openingRecords = DB::table('finance_opening_balance')->where('request_id',$project->requestid)->where('financial_year',$year)->where('isactive',1)->get();

					$vendorOpeningRecords = $openingRecords->where('balance_type','VENDOR_FUNDING');
					$serviceOpeningRecords = $openingRecords->where('balance_type','SERVICE_CHARGE');

					$hasManualVendor = $vendorOpeningRecords->count() > 0;
					$hasManualService = $serviceOpeningRecords->count() > 0;

					$manualVendor = (float)$vendorOpeningRecords->sum('opening_balance');
					$manualService = (float)$serviceOpeningRecords->sum('opening_balance');

					$openingVendor = $hasManualVendor ? $manualVendor : $vendorClosing;
					$openingService = $hasManualService ? $manualService : $serviceClosing;

					$receiptVendor = (float)DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dp.request_id',$project->requestid)->where('dp.financial_year',$year)->where('dp.status','!=','Cancelled')->where('dn.status','!=','Cancelled')->where('dn.demand_note_type','VENDOR_FUNDING')->sum('dp.net_received_amount');

					$receiptService = (float)DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dp.request_id',$project->requestid)->where('dp.financial_year',$year)->where('dp.status','!=','Cancelled')->where('dn.status','!=','Cancelled')->where('dn.demand_note_type','SERVICE_CHARGE')->sum('dp.net_received_amount');

					$startDate = substr($year,0,4).'-04-01';
					$endDate = substr($year,5,4).'-03-31';

					$utilizedVendor = (float)DB::table('invoice_mpr as im')->where('im.request_id',$project->requestid)->where('im.finance_status','Verified')->whereBetween('im.invoice_date',[$startDate,$endDate])->sum('im.vendor_funding_utilized_amount');

					$utilizedService = (float)DB::table('invoice_mpr as im')->where('im.request_id',$project->requestid)->where('im.finance_status','Verified')->whereBetween('im.invoice_date',[$startDate,$endDate])->sum('im.service_charge_utilized_amount');

					$vendorClosing = $openingVendor + $receiptVendor - $utilizedVendor;
					$serviceClosing = $openingService + $receiptService - $utilizedService;

					if($firstYear)
					{
						$projectOpeningVendor = $openingVendor;
						$projectOpeningService = $openingService;
						$firstYear = false;
					}

					$projectReceiptVendor += $receiptVendor;
					$projectReceiptService += $receiptService;
					$projectUtilizedVendor += $utilizedVendor;
					$projectUtilizedService += $utilizedService;
				}

				$departmentOpeningVendor += $projectOpeningVendor;
				$departmentOpeningService += $projectOpeningService;
				$departmentReceiptVendor += $projectReceiptVendor;
				$departmentReceiptService += $projectReceiptService;
				$departmentUtilizedVendor += $projectUtilizedVendor;
				$departmentUtilizedService += $projectUtilizedService;
				$departmentClosingVendor += $vendorClosing;
				$departmentClosingService += $serviceClosing;
			}

			if($fundType == 'VENDOR_FUNDING')
			{
				$openingFund = $departmentOpeningVendor;
				$departmentReceipt = $departmentReceiptVendor;
				$vendorUtilized = $departmentUtilizedVendor;
				$serviceUtilized = 0;
				$closingBalance = $departmentClosingVendor;
			}
			elseif($fundType == 'SERVICE_CHARGE')
			{
				$openingFund = $departmentOpeningService;
				$departmentReceipt = $departmentReceiptService;
				$vendorUtilized = 0;
				$serviceUtilized = $departmentUtilizedService;
				$closingBalance = $departmentClosingService;
			}
			else
			{
				$openingFund = $departmentOpeningVendor + $departmentOpeningService;
				$departmentReceipt = $departmentReceiptVendor + $departmentReceiptService;
				$vendorUtilized = $departmentUtilizedVendor;
				$serviceUtilized = $departmentUtilizedService;
				$closingBalance = $departmentClosingVendor + $departmentClosingService;
			}

			$totalAvailable = $openingFund + $departmentReceipt;
			$totalUtilized = $vendorUtilized + $serviceUtilized;

			if($openingFund != 0 || $departmentReceipt != 0 || $totalUtilized != 0)
			{
				$reportData->push((object)[
					'departmentid' => $department->userid,
					'departmentname' => $department->departmentname,
					'shortname' => $department->shortname,
					'project_count' => $projects->count(),
					'opening_fund' => $openingFund,
					'department_receipt' => $departmentReceipt,
					'total_available' => $totalAvailable,
					'vendor_funding_utilized' => $vendorUtilized,
					'service_charge_utilized' => $serviceUtilized,
					'total_utilized' => $totalUtilized,
					'closing_balance' => $closingBalance
				]);
			}
		}

		return view('finance.reporting.ajaxpages.departmentWiseFundBalance',compact('reportData','reportFormat','financialYear','fundType'));
	}


	public function fundTypeUtilization(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','fundutilization');

		$financialYears = FinanceHelper::financialYears();

		$projects = DB::table('eoi_request as e')->leftJoin('users_tbl as u','u.userid','=','e.userid')->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0)->select('e.requestid','e.eoinumber','e.projecttitle')->orderBy('e.projecttitle')->get();

		return view('finance.reporting.fund_type_utilization',compact('financialYears','projects'));
	}

	public function getFundTypeUtilization(Request $request)
	{
		$financialYear = $request->financial_year;
		$requestId = $request->requestid;
		$fundType = $request->fund_type;
		$reportFormat = $request->report_format ?? '1';

		$projectsQuery = DB::table('eoi_request as e')->leftJoin('users_tbl as u','u.userid','=','e.userid')->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0);

		if($requestId)
		{
			$projectsQuery->where('e.requestid',$requestId);
		}

		$projects = $projectsQuery->select('e.requestid','e.eoinumber','e.projecttitle')->orderBy('e.projecttitle')->get();

		$financialYears = $financialYear ? collect([$financialYear]) : collect(FinanceHelper::financialYears());

		$financialYears = $financialYears->sortBy(function($year){
			return (int)substr($year,0,4);
		})->values();

		$reportData = collect();

		foreach($projects as $project)
		{
			$vendorClosing = 0;
			$serviceClosing = 0;

			$firstYear = true;

			$totalOpeningVendor = 0;
			$totalOpeningService = 0;
			$totalReceiptVendor = 0;
			$totalReceiptService = 0;
			$totalUtilizedVendor = 0;
			$totalUtilizedService = 0;

			foreach($financialYears as $year)
			{
				$openingRecords = DB::table('finance_opening_balance')->where('request_id',$project->requestid)->where('financial_year',$year)->where('isactive',1)->get();

				$vendorOpeningRecord = $openingRecords->where('balance_type','VENDOR_FUNDING');
				$serviceOpeningRecord = $openingRecords->where('balance_type','SERVICE_CHARGE');

				$hasManualVendor = $vendorOpeningRecord->count() > 0;
				$hasManualService = $serviceOpeningRecord->count() > 0;

				$manualVendor = (float)$vendorOpeningRecord->sum('opening_balance');
				$manualService = (float)$serviceOpeningRecord->sum('opening_balance');

				$openingVendor = $hasManualVendor ? $manualVendor : $vendorClosing;
				$openingService = $hasManualService ? $manualService : $serviceClosing;

				$receiptVendor = (float)DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dp.request_id',$project->requestid)->where('dp.financial_year',$year)->where('dp.status','!=','Cancelled')->where('dn.status','!=','Cancelled')->where('dn.demand_note_type','VENDOR_FUNDING')->sum('dp.net_received_amount');

				$receiptService = (float)DB::table('finance_department_payment as dp')->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->where('dp.request_id',$project->requestid)->where('dp.financial_year',$year)->where('dp.status','!=','Cancelled')->where('dp.status','!=','Cancelled')->where('dn.demand_note_type','SERVICE_CHARGE')->sum('dp.net_received_amount');

				$startDate = substr($year,0,4).'-04-01';
				$endDate = substr($year,5,4).'-03-31';

				$utilizedVendor = (float)DB::table('invoice_mpr as im')->where('im.request_id',$project->requestid)->where('im.finance_status','Verified')->whereBetween('im.invoice_date',[$startDate,$endDate])->sum('im.vendor_funding_utilized_amount');

				$utilizedService = (float)DB::table('invoice_mpr as im')->where('im.request_id',$project->requestid)->where('im.finance_status','Verified')->whereBetween('im.invoice_date',[$startDate,$endDate])->sum('im.service_charge_utilized_amount');

				$vendorClosing = $openingVendor + $receiptVendor - $utilizedVendor;
				$serviceClosing = $openingService + $receiptService - $utilizedService;

				if($firstYear)
				{
					$totalOpeningVendor = $openingVendor;
					$totalOpeningService = $openingService;
					$firstYear = false;
				}

				$totalReceiptVendor += $receiptVendor;
				$totalReceiptService += $receiptService;
				$totalUtilizedVendor += $utilizedVendor;
				$totalUtilizedService += $utilizedService;
			}

			if($fundType == 'VENDOR_FUNDING')
			{
				$openingFund = $totalOpeningVendor;
				$departmentReceipt = $totalReceiptVendor;
				$vendorUtilized = $totalUtilizedVendor;
				$serviceUtilized = 0;
				$closingBalance = $vendorClosing;
			}
			elseif($fundType == 'SERVICE_CHARGE')
			{
				$openingFund = $totalOpeningService;
				$departmentReceipt = $totalReceiptService;
				$vendorUtilized = 0;
				$serviceUtilized = $totalUtilizedService;
				$closingBalance = $serviceClosing;
			}
			else
			{
				$openingFund = $totalOpeningVendor + $totalOpeningService;
				$departmentReceipt = $totalReceiptVendor + $totalReceiptService;
				$vendorUtilized = $totalUtilizedVendor;
				$serviceUtilized = $totalUtilizedService;
				$closingBalance = $vendorClosing + $serviceClosing;
			}

			$totalAvailable = $openingFund + $departmentReceipt;
			$totalUtilized = $vendorUtilized + $serviceUtilized;

			if($openingFund != 0 || $departmentReceipt != 0 || $totalUtilized != 0)
			{
				$reportData->push((object)[
					'requestid' => $project->requestid,
					'eoinumber' => $project->eoinumber,
					'projecttitle' => $project->projecttitle,
					'opening_fund' => $openingFund,
					'department_receipt' => $departmentReceipt,
					'total_available' => $totalAvailable,
					'vendor_funding_utilized' => $vendorUtilized,
					'service_charge_utilized' => $serviceUtilized,
					'total_utilized' => $totalUtilized,
					'closing_balance' => $closingBalance
				]);
			}
		}

		return view('finance.reporting.ajaxpages.fundTypeUtilization',compact('reportData','reportFormat','financialYear','fundType'));
	}	

	public function fundTypeMovement(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','fundmovement');

		$financialYears = FinanceHelper::financialYears();

		return view('finance.reporting.fund_type_movement',compact('financialYears'));
	}

	public function financialYearFundMovement(Request $request)
	{
		$selectedFinancialYear = $request->input('financial_year');
		$report_format = $request->input('report_format',1);

		try
		{
			$financialYears = collect(FinanceHelper::financialYears())->sort()->values();
			$reportYears = $selectedFinancialYear ? collect([$selectedFinancialYear]) : $financialYears;

			$eois = DB::table('eoi_request as e')->leftJoin('users_tbl as u','u.userid','=','e.userid')->where('e.categoryid',2)->where('u.ispm',0)->where('e.iscancelled',0)->select('e.requestid')->get();

			$openingBalances = DB::table('finance_opening_balance')->select('request_id','financial_year','balance_type','opening_balance','isactive')->where('isactive',1)->get()->groupBy(function($row){ return $row->financial_year.'_'.$row->request_id; });

			$departmentPayments = DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id','dp.financial_year')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as vendor_funding_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as service_charge_received")->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id','dp.financial_year')->get()->keyBy(function($row){ return $row->financial_year.'_'.$row->request_id; });

			$invoiceUtilization = DB::table('invoice_mpr as im')->select('im.request_id')->selectRaw("DATE_FORMAT(im.invoice_date,'%Y-%m') as invoice_month")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_utilized_amount,0) ELSE 0 END) as vendor_funding_utilized")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.service_charge_utilized_amount,0) ELSE 0 END) as service_charge_utilized")->where('im.finance_status','Verified')->groupBy('im.request_id','invoice_month')->get();

			$utilizationByYear = collect();

			foreach($invoiceUtilization as $invoice)
			{
				$invoiceYear = (int)substr($invoice->invoice_month,0,4);
				$invoiceMonth = (int)substr($invoice->invoice_month,5,2);
				$invoiceFinancialYear = $invoiceMonth >= 4 ? $invoiceYear.'-'.substr($invoiceYear + 1,-2) : ($invoiceYear - 1).'-'.substr($invoiceYear,-2);
				$key = $invoiceFinancialYear.'_'.$invoice->request_id;
				$utilizationByYear[$key] = (object)[
					'vendor_funding_utilized' => round((float)($utilizationByYear[$key]->vendor_funding_utilized ?? 0) + (float)$invoice->vendor_funding_utilized,2),
					'service_charge_utilized' => round((float)($utilizationByYear[$key]->service_charge_utilized ?? 0) + (float)$invoice->service_charge_utilized,2)
				];
			}

			$allYears = $financialYears->merge($reportYears)->unique()->sort()->values();
			$yearBalances = collect();

			foreach($allYears as $financialYear)
			{
				$yearBalances[$financialYear] = collect();

				foreach($eois as $eoi)
				{
					$requestId = $eoi->requestid;
					$key = $financialYear.'_'.$requestId;
					$openingRows = $openingBalances->get($key);
					$receipt = $departmentPayments->get($key);
					$utilization = $utilizationByYear->get($key);

					$vendorOpening = null;
					$serviceOpening = null;

					if($openingRows)
					{
						$vendorRow = $openingRows->firstWhere('balance_type','VENDOR_FUNDING');
						$serviceRow = $openingRows->firstWhere('balance_type','SERVICE_CHARGE');

						if($vendorRow)
						{
							$vendorOpening = round((float)$vendorRow->opening_balance,2);
						}

						if($serviceRow)
						{
							$serviceOpening = round((float)$serviceRow->opening_balance,2);
						}
					}

					if($vendorOpening === null || $serviceOpening === null)
					{
						$previousYear = null;
						foreach($allYears as $checkYear)
						{
							if($checkYear >= $financialYear)
							{
								break;
							}

							$previousYear = $checkYear;
						}

						if($previousYear && isset($yearBalances[$previousYear][$requestId]))
						{
							$previousBalance = $yearBalances[$previousYear][$requestId];
							if($vendorOpening === null)
							{
								$vendorOpening = round((float)$previousBalance->vendor_funding_closing,2);
							}
							if($serviceOpening === null)
							{
								$serviceOpening = round((float)$previousBalance->service_charge_closing,2);
							}
						}
					}

					$vendorOpening = round((float)($vendorOpening ?? 0),2);
					$serviceOpening = round((float)($serviceOpening ?? 0),2);
					$vendorReceived = round((float)($receipt->vendor_funding_received ?? 0),2);
					$serviceReceived = round((float)($receipt->service_charge_received ?? 0),2);
					$vendorUtilized = round((float)($utilization->vendor_funding_utilized ?? 0),2);
					$serviceUtilized = round((float)($utilization->service_charge_utilized ?? 0),2);

					$yearBalances[$financialYear][$requestId] = (object)[
						'vendor_funding_closing' => round($vendorOpening + $vendorReceived - $vendorUtilized,2),
						'service_charge_closing' => round($serviceOpening + $serviceReceived - $serviceUtilized,2)
					];
				}
			}

			$report = collect();

			foreach($reportYears as $financialYear)
			{
				$openingBalance = 0;
				$departmentReceipt = 0;
				$utilized = 0;

				foreach($eois as $eoi)
				{
					$requestId = $eoi->requestid;
					$key = $financialYear.'_'.$requestId;
					$openingRows = $openingBalances->get($key);
					$receipt = $departmentPayments->get($key);
					$utilization = $utilizationByYear->get($key);

					if($openingRows)
					{
						$vendorRow = $openingRows->firstWhere('balance_type','VENDOR_FUNDING');
						$serviceRow = $openingRows->firstWhere('balance_type','SERVICE_CHARGE');
						$openingBalance += round((float)($vendorRow->opening_balance ?? 0),2);
						$openingBalance += round((float)($serviceRow->opening_balance ?? 0),2);
					}
					elseif(isset($yearBalances[$financialYear][$requestId]))
					{
						$currentBalance = $yearBalances[$financialYear][$requestId];
						$previousYears = $allYears->filter(function($year) use ($financialYear){ return $year < $financialYear; })->values();
						$previousYear = $previousYears->last();

						if($previousYear && isset($yearBalances[$previousYear][$requestId]))
						{
							$previousBalance = $yearBalances[$previousYear][$requestId];
							$openingBalance += round((float)$previousBalance->vendor_funding_closing,2);
							$openingBalance += round((float)$previousBalance->service_charge_closing,2);
						}
					}

					$departmentReceipt += round((float)($receipt->vendor_funding_received ?? 0),2);
					$departmentReceipt += round((float)($receipt->service_charge_received ?? 0),2);
					$utilized += round((float)($utilization->vendor_funding_utilized ?? 0),2);
					$utilized += round((float)($utilization->service_charge_utilized ?? 0),2);
				}

				$totalAvailable = round($openingBalance + $departmentReceipt,2);
				$closingBalance = round($totalAvailable - $utilized,2);

				$report->push((object)[
					'financial_year' => $financialYear,
					'opening_balance' => round($openingBalance,2),
					'department_receipt' => round($departmentReceipt,2),
					'total_available' => $totalAvailable,
					'utilized' => round($utilized,2),
					'closing_balance' => $closingBalance
				]);
			}

			$summary = [
				'opening_balance' => round($report->sum('opening_balance'),2),
				'department_receipt' => round($report->sum('department_receipt'),2),
				'total_available' => round($report->sum('total_available'),2),
				'utilized' => round($report->sum('utilized'),2),
				'closing_balance' => round($report->sum('closing_balance'),2)
			];

			return view('finance.reporting.ajaxpages.financialYearFundMovement',compact('report','summary','selectedFinancialYear','report_format'));
		}
		catch(Exception $e)
		{
			Log::error("Error ".$e->getMessage());
			return response()->json(['status'=>false,'message'=>'Unable to generate financial year fund movement report.'],500);
		}
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

	public function fundTypeWisePayment(Request $request)
	{
		Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','fundtypewisesummary');
		
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
		return view('finance.reporting.fundTypeWiseSummary',compact('departments','managers','financialYears'));
	}

	public function getFundTypeWiseSummary(Request $request)
	{
		$selectedFinancialYear 	= 	$request->input('financial_year');
		$report_format			=	$request->input('report_format',1);
		
		try
		{
			$selectedStartYear 		= 	(int)substr($selectedFinancialYear,0,4);
			$selectedEndYear 		= 	$selectedStartYear + 1;
			$previousFinancialYear 	= 	($selectedStartYear - 1).'-'.substr($selectedStartYear,-2);
			$fyStartDate 			= 	$selectedStartYear.'-04-01';
			$fyEndDate 				= 	$selectedEndYear.'-03-31';
			$previousFyStartDate 	= 	($selectedStartYear - 1).'-04-01';
			$previousFyEndDate 		= 	$selectedStartYear.'-03-31';

			$currentOpeningBalances	=	DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening")->where('financial_year',$selectedFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');

			$previousOpeningBalances= 	DB::table('finance_opening_balance')->select('request_id')->selectRaw("SUM(CASE WHEN balance_type = 'VENDOR_FUNDING' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as vendor_funding_opening")->selectRaw("SUM(CASE WHEN balance_type = 'SERVICE_CHARGE' AND isactive = 1 THEN COALESCE(opening_balance,0) ELSE 0 END) as service_charge_opening")->where('financial_year',$previousFinancialYear)->groupBy('request_id')->get()->keyBy('request_id');

			$previousDepartmentPayments	=	DB::table('finance_department_payment as dp')->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')->select('dp.request_id')->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as vendor_funding_received")->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as service_charge_received")->where('dp.financial_year',$previousFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

			$previousInvoiceUtilization	=	DB::table('invoice_mpr as im')->select('im.request_id')->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_utilized_amount,0) ELSE 0 END) as vendor_funding_utilized")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.service_charge_utilized_amount,0) ELSE 0 END) as service_charge_utilized")->whereBetween('im.invoice_date',[$previousFyStartDate,$previousFyEndDate])->groupBy('im.request_id')->get()->keyBy('request_id');

			$previousClosingBalances	=	collect();

			$allPreviousRequestIds	=	$previousOpeningBalances->keys()->merge($previousDepartmentPayments->keys())->merge($previousInvoiceUtilization->keys())->unique();

			foreach($allPreviousRequestIds as $requestId)
			{
				$previousOpening 		= 	$previousOpeningBalances->get($requestId);
				$previousReceipt 		= 	$previousDepartmentPayments->get($requestId);
				$previousUtilization 	= 	$previousInvoiceUtilization->get($requestId);

				$previousVendorFundingOpening 	= 	round((float)($previousOpening->vendor_funding_opening ?? 0),2);
				$previousServiceChargeOpening 	= 	round((float)($previousOpening->service_charge_opening ?? 0),2);
				$previousVendorFundingReceived 	= 	round((float)($previousReceipt->vendor_funding_received ?? 0),2);
				$previousServiceChargeReceived 	= 	round((float)($previousReceipt->service_charge_received ?? 0),2);
				$previousVendorFundingUtilized 	= 	round((float)($previousUtilization->vendor_funding_utilized ?? 0),2);
				$previousServiceChargeUtilized 	= 	round((float)($previousUtilization->service_charge_utilized ?? 0),2);

				$previousClosingBalances->put($requestId,(object)[
					'vendor_funding_closing' => round($previousVendorFundingOpening + $previousVendorFundingReceived - $previousVendorFundingUtilized,2),
					'service_charge_closing' => round($previousServiceChargeOpening + $previousServiceChargeReceived - $previousServiceChargeUtilized,2)
				]);
			}

			$departmentPayments	=	DB::table('finance_department_payment as dp')
									->leftJoin('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
									->select('dp.request_id')
									->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'VENDOR_FUNDING' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as vendor_funding_received")
									->selectRaw("SUM(CASE WHEN dn.demand_note_type = 'SERVICE_CHARGE' THEN COALESCE(dp.net_received_amount,0) ELSE 0 END) as service_charge_received")->where('dp.financial_year',$selectedFinancialYear)->where('dp.status','<>','Cancelled')->where(function($query){ $query->whereNull('dn.status')->orWhere('dn.status','<>','Cancelled'); })->groupBy('dp.request_id')->get()->keyBy('request_id');

			$invoiceUtilization	=	DB::table('invoice_mpr as im')->select('im.request_id')->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.vendor_funding_utilized_amount,0) ELSE 0 END) as vendor_funding_utilized")->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.service_charge_utilized_amount,0) ELSE 0 END) as service_charge_utilized")->whereBetween('im.invoice_date',[$fyStartDate,$fyEndDate])->groupBy('im.request_id')->get()->keyBy('request_id');

			$eois	=	DB::table('eoi_request as e')
						->leftJoin('users_tbl as u','u.userid','=','e.userid')
						->where('e.categoryid',2)
						->where('u.ispm',0)
						->where('e.iscancelled',0)
						->select('e.requestid')
						->get();

			$vendorFundingOpening 	= 	0;
			$vendorFundingReceived 	= 	0;
			$vendorFundingUtilized 	= 	0;
			$serviceChargeOpening 	= 	0;
			$serviceChargeReceived 	= 	0;
			$serviceChargeUtilized 	= 	0;

			foreach($eois as $eoi)
			{
				$currentOpening = $currentOpeningBalances->get($eoi->requestid);
				$previousClosing = $previousClosingBalances->get($eoi->requestid);
				$receipt = $departmentPayments->get($eoi->requestid);
				$utilization = $invoiceUtilization->get($eoi->requestid);

				$vendorFundingOpening += $currentOpening ? round((float)$currentOpening->vendor_funding_opening,2) : round((float)($previousClosing->vendor_funding_closing ?? 0),2);
				$serviceChargeOpening += $currentOpening ? round((float)$currentOpening->service_charge_opening,2) : round((float)($previousClosing->service_charge_closing ?? 0),2);

				$vendorFundingReceived += round((float)($receipt->vendor_funding_received ?? 0),2);
				$serviceChargeReceived += round((float)($receipt->service_charge_received ?? 0),2);

				$vendorFundingUtilized += round((float)($utilization->vendor_funding_utilized ?? 0),2);
				$serviceChargeUtilized += round((float)($utilization->service_charge_utilized ?? 0),2);
			}

			$report = collect([
				(object)[
					'fund_type' => 'VENDOR_FUNDING',
					'fund_name' => 'Consultancy',
					'opening_balance' => round($vendorFundingOpening,2),
					'department_receipt' => round($vendorFundingReceived,2),
					'total_available' => round($vendorFundingOpening + $vendorFundingReceived,2),
					'utilized' => round($vendorFundingUtilized,2),
					'available_balance' => round($vendorFundingOpening + $vendorFundingReceived - $vendorFundingUtilized,2)
				],
				(object)[
					'fund_type' => 'SERVICE_CHARGE',
					'fund_name' => 'Administrative Charge',
					'opening_balance' => round($serviceChargeOpening,2),
					'department_receipt' => round($serviceChargeReceived,2),
					'total_available' => round($serviceChargeOpening + $serviceChargeReceived,2),
					'utilized' => round($serviceChargeUtilized,2),
					'available_balance' => round($serviceChargeOpening + $serviceChargeReceived - $serviceChargeUtilized,2)
				]
			]);

			$summary = [
				'opening_balance' => round($report->sum('opening_balance'),2),
				'department_receipt' => round($report->sum('department_receipt'),2),
				'total_available' => round($report->sum('total_available'),2),
				'utilized' => round($report->sum('utilized'),2),
				'available_balance' => round($report->sum('available_balance'),2)
			];

			return view('finance.reporting.ajaxpages.fundTypeWiseSummary',compact('report','summary','selectedFinancialYear','report_format'));
		}
		catch(Exception $e)
		{
			Log::error("Error ".$e->getMessage());
			return response()->json(['status'=>false,'message'=>'Unable to generate fund type-wise summary.'],500);
		}
	}	
}
?>