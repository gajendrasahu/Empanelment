<?php
namespace App\Services\Finance;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
class FinanceService
{
	public function getProjectSummary($categoryid=0,$requestid=0,$managerid=0,$departmentid=0,$vendorid=0,$orderid=0)
	{
		
	}

	public function getVendorPaymentChartData($vendorId = null)
	{
		$query	=	DB::table('invoice_mpr as im')
					->leftJoin('vendor_tbl as u','u.vendorid','=','im.vendor_id')
					->select('im.vendor_id','u.shortname as vendor_name')
					->selectRaw('SUM(COALESCE(im.net_invoice_value,0)) as invoice_raised')
					->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN COALESCE(im.net_invoice_value,0) ELSE 0 END) as finance_verified")
					->selectRaw('SUM(COALESCE(im.net_paid_value,0)) as paid')
					->selectRaw("SUM(CASE WHEN im.finance_status = 'Verified' THEN GREATEST(COALESCE(im.net_balance_value,0),0) ELSE 0 END) as balance")
					->groupBy('im.vendor_id','u.shortname')
					->orderByDesc('invoice_raised');

		if($vendorId !== null)
		{
			$query->where('im.vendor_id',$vendorId);
		}

		return $query->get();
	}	
}