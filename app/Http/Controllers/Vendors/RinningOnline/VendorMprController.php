<?php

namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Vendors;
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
use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\LogServices;
use App\Services\TierWiseDataService;
use App\Services\DeploymentDataService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Hash;
use App\Services\PasswordService;

class VendorMprController extends Controller
{
	protected $smsService;
    protected $todays_datetime;
    protected $todays_date;	
	protected $logServices;
	protected $priceService;
	protected $deploymentService;
	protected $passwordService;
	public function __construct(SmsService $smsService,LogServices $logServices,TierWiseDataService $priceService,DeploymentDataService $deploymentService,PasswordService $passwordService)
	{
		$this->smsService 		=	$smsService;
		ini_set('serialize_precision', -1);
		$this->todays_datetime	=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date		=	Carbon::now()->format('Y-m-d');
		$this->logServices 		= 	$logServices;
		$this->priceService		=	$priceService;
		$this->deploymentService=	$deploymentService;
		$this->passwordService 	= 	$passwordService;
	}
    public function vendorMonthlyMpr(Request $request)
	{
		$userId			= 	$request->session()->get('userId');
		$vendorId		= 	$request->session()->get('vendorId') ?? 0;
		
        Session::put('adminmenu','vendorinvoices');
		Session::put('adminsubmenu','vendormonthlympr');
		Session::put('menid',160);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=160 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$projects	=	DB::table('eoi_work_order as a')
						->select('a.projectid','b.project_name')
						->join('project_tbl as b','b.projectid','=','a.projectid')
						->where('a.vendorid',$vendorId)
						->where('a.isActiveOrder',1)
						->where('a.isExtended',0)
						->where('a.workorderduedate','>=',today())
						->where('a.isClosed',0)
						->distinct('a.projectid')
						->get();

		$orders	=	DB::table('eoi_work_order as a')
						->select('a.orderid','a.ordernumber')
						->where('a.vendorid',$vendorId)
						//->where('a.isActiveOrder',1)
						->where('a.isExtended',0)
						//->where('a.workorderduedate','>=',today())
						->where('a.isClosed',0)
						->get();
		
        return view('admin/vendors/mpr/monthly_mpr',compact('projects','orders','months','years'));
    }

}
