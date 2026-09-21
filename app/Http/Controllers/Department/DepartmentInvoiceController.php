<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Department;
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
use Illuminate\Validation\ValidationException;
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DepartmentInvoiceController extends Controller
{
    public function deptInvoiceList(Request $request)
	{
        Session::put('adminmenu','deptorderinvoices');
		Session::put('adminsubmenu','deptinvoices');
		
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;
		
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
		
		$firms	=	DB::table('eoi_work_order as a')
					->select('b.vendorid','b.companyname','b.shortname')
					->join('vendor_tbl as b','b.vendorid','=','a.vendorid')
					->distinct()
					->where('a.department_id',$departmentId)
					->orderBy('b.companyname')
					->get();

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
       return view('admin/department/invoice_history',compact('months','years','firms','token'));
		
    }

    public function getDeptInvoiceData(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$departmentId	= 	$request->session()->get('departmentId') ?? 0;

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
										  ->orwhere('c.project_name', 'like', '%' . $pagesearch . '%');
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
								->where('c.department_id', '=', $departmentId)
								->orderBy('a.request_date','DESC')
								->paginate($pagesize,['*'],'page',$currentPage);
				
		return view('admin/department/ajaxpages/invoicehistoryTable', ['data' => $data]);
    }

    public function markAsSeen(Request $request,$recordid=NULL)
	{
		DB::table('vendor_invoices')->where('recordid',Crypt::decrypt($request->input('recordid')))->update(['isMarked'=>1]);
		
		return response()->json(['status'=>200,'message'=>'Invoice marked as viewed.']);
		
    }
	
}
