<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
class FinanceDashboardController extends Controller
{
    public function index()
    {
        Session::put('adminmenu','adminfinance');
		Session::put('adminsubmenu','financedashboard');
		
        $data = [];

		$pendingVendorInvoices	=	DB::table('invoice_mpr')->where('finance_status', 'Pending')->count();
		$pendingVendorPayments	=	DB::table('invoice_mpr')->where('finance_status','Verified')->where('payment_status','!=','Paid')->count();

		$data['pendingVendorPayments']	=	$pendingVendorPayments;
		$data['pendingVendorInvoices']	=	$pendingVendorInvoices;        

        return view('finance.dashboard', compact('data'));
    }
}
?>