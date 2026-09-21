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

class FinanceDepartmentFundController extends Controller
{
	public function index()
	{
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','departmentbalance');

		$departments	=	DB::table('department_tbl as a')
							->select('a.*','b.isdepartment','b.ispm')
							->Join('users_tbl as b','b.userid','=','a.userid')
							->where('b.isdepartment',1)
							->orderby('b.name')
							->get();
		$projects	=	DB::table('project_tbl')
						->select('projectid','project_name')
						->orderby('project_name')
						->get();

		$token	=	rand('100000','999999').''.time();
		
		Session::put('form_token',$token);

		return view('finance.departmentfund.index',compact('departments','projects','token'));
	}
	public function getDepartmentAvailableFund(Request $request)
	{
		$pagesearch		=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize',100);
		$currentPage	=	$request->input('page',1);
		$projectid		=	$request->input('projectid') ?? NULL;
		$departmentid	=	$request->input('departmentid') ?? NULL;

		$data = DB::table('eoi_request as e')

			// EOI -> User
			->join('users_tbl as u','u.userid','=','e.userid')

			// EOI -> Project
			->leftJoin('project_tbl as pn','pn.projectid','=','e.projectid')

			// Opening Balance
			->leftJoinSub(
				DB::table('finance_opening_balance')
					->select(
						'request_id',
						DB::raw("
							SUM(
								CASE
									WHEN balance_type = 'VENDOR_FUNDING'
										 AND isactive = 1
									THEN opening_balance
									ELSE 0
								END
							) AS vendor_funding_opening
						"),
						DB::raw("
							SUM(
								CASE
									WHEN balance_type = 'SERVICE_CHARGE'
										 AND isactive = 1
									THEN opening_balance
									ELSE 0
								END
							) AS service_charge_opening
						")
					)
					->groupBy('request_id'),

				'ob',
				'ob.request_id',
				'=',
				'e.requestid'
			)

			// Department Payments
			->leftJoinSub(
				DB::table('finance_department_payment as dp')
					->join('finance_demand_note as dn','dn.demand_note_id','=','dp.demand_note_id')
					->select(
						'dp.request_id',
						DB::raw("
							SUM(
								CASE
									WHEN dn.demand_note_type = 'VENDOR_FUNDING'
										 AND dp.status = 'Active'
									THEN dp.net_received_amount
									ELSE 0
								END
							) AS vendor_funding_received
						"),

						DB::raw("
							SUM(
								CASE
									WHEN dn.demand_note_type = 'SERVICE_CHARGE'
										 AND dp.status = 'Active'
									THEN dp.net_received_amount
									ELSE 0
								END
							) AS service_charge_received
						")
					)
					->whereNotNull('dp.request_id')
					->groupBy('dp.request_id'),

				'dp',
				'dp.request_id',
				'=',
				'e.requestid'
			)

			// Invoice / Utilization
			->leftJoinSub(
				DB::table('invoice_mpr as i')
					->select(
						'i.request_id',
						// Vendor Funding Utilized
						DB::raw("
							SUM(
								CASE
									WHEN i.finance_status = 'Verified'
									THEN ROUND(
										COALESCE(i.net_invoice_value, 0)

										- (
											COALESCE(i.taxable_amount, 0)
											* COALESCE(i.vendor_funding_tds_rate, 0)
											/ 100
										)

										- (
											COALESCE(i.taxable_amount, 0)
											* COALESCE(i.vendor_funding_gst_tds_rate, 0)
											/ 100
										),

										2
									)
									ELSE 0
								END
							) AS vendor_funding_utilized
						"),

						// Service Charge Utilized
						DB::raw("
							SUM(
								CASE
									WHEN i.finance_status = 'Verified'
									THEN ROUND(

										(
											COALESCE(i.taxable_amount, 0)
											* COALESCE(i.admin_charge_percent, 0)
											/ 100
										)

										+

										(
											(
												COALESCE(i.net_invoice_value, 0)
												- COALESCE(i.taxable_amount, 0)
											)
											* COALESCE(i.admin_charge_percent, 0)
											/ 100
										)

										-

										(
											(
												COALESCE(i.taxable_amount, 0)
												* COALESCE(i.admin_charge_percent, 0)
												/ 100
											)
											* COALESCE(i.service_charge_tds_rate, 0)
											/ 100
										)

										-

										(
											(
												COALESCE(i.taxable_amount, 0)
												* COALESCE(i.admin_charge_percent, 0)
												/ 100
											)
											* COALESCE(i.service_charge_gst_tds_rate, 0)
											/ 100
										),

										2
									)
									ELSE 0
								END
							) AS service_charge_utilized
						")
					)
					->whereNotNull('i.request_id')
					->groupBy('i.request_id'),

				'iu',
				'iu.request_id',
				'=',
				'e.requestid'
			)

			// Basic Filters
			->where('e.categoryid', 2)
			->where('u.ispm', 0)
			->where('e.iscancelled', 0)

			// Project Filter
			->when(
				$projectid != null,
				function ($query) use ($projectid) {
					return $query->where(
						'e.projectid',
						$projectid
					);
				}
			)

			// Department Filter
			->when(
				$departmentid != null,
				function ($query) use ($departmentid) {
					return $query->where(
						'e.userid',
						$departmentid
					);
				}
			)

			// Search Filter
			->when(
				$pagesearch != '',
				function ($query) use ($pagesearch) {
					return $query->where(function ($q) use ($pagesearch) {
						$q->where(
							'e.eoinumber',
							'like',
							'%' . $pagesearch . '%'
						);
					});
				}
			)

			// Selected Fields
			->select(
				'u.name',
				'pn.project_name',
				'e.requestid',
				'e.eoinumber',
				'e.engagementname as projecttitle',

				// Opening Balance
				DB::raw("
					COALESCE(
						ob.vendor_funding_opening,
						0
					) AS vendor_funding_opening
				"),

				DB::raw("
					COALESCE(
						ob.service_charge_opening,
						0
					) AS service_charge_opening
				"),

				// Department Received
				DB::raw("
					COALESCE(
						dp.vendor_funding_received,
						0
					) AS vendor_funding_received
				"),

				DB::raw("
					COALESCE(
						dp.service_charge_received,
						0
					) AS service_charge_received
				"),

				// Utilized
				DB::raw("
					COALESCE(
						iu.vendor_funding_utilized,
						0
					) AS vendor_funding_utilized
				"),

				DB::raw("
					COALESCE(
						iu.service_charge_utilized,
						0
					) AS service_charge_utilized
				"),

				// Vendor Funding Balance
				DB::raw("
					(
						COALESCE(ob.vendor_funding_opening, 0)
						+ COALESCE(dp.vendor_funding_received, 0)
						- COALESCE(iu.vendor_funding_utilized, 0)
					) AS vendor_funding_balance
				"),

				// Service Charge Balance
				DB::raw("
					(
						COALESCE(ob.service_charge_opening, 0)
						+ COALESCE(dp.service_charge_received, 0)
						- COALESCE(iu.service_charge_utilized, 0)
					) AS service_charge_balance
				")
			)

			// Latest EOI First
			->orderBy(
				'e.requestid',
				'desc'
			)

			->get();	

		return view('finance.departmentfund.ajaxpages.availablefundTable',['data'=>$data]);
		
	}

}
