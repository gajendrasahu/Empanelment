<?php
namespace App\Http\Controllers;
use Illuminate\Pagination\LengthAwarePaginator;
namespace App\Http\Controllers\Resources;
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
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Crypt;
use App\Services\SmsService;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendOTPMail;

class ResourcesController extends Controller
{
	protected $smsService;
    protected $todays_datetime;
    protected $todays_date;	
	protected $usr;
	public function __construct(SmsService $smsService,Request $request)
	{
		$this->smsService 		=	$smsService;
		$this->todays_datetime	=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date		=	Carbon::now()->format('Y-m-d');

		$this->middleware(function ($request, $next) {
			$this->usr = DB::table('users_tbl')
				->where('userid', $request->session()->get('userId'))
				->first();

			return $next($request);
		});		
	}

    public function monthlyAttendance(Request $request)
	{
        Session::put('adminmenu','resourcemprs');
		Session::put('adminsubmenu','monthlyattendance');
		Session::put('menid',130);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=130 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
						
			$actions	=	explode(",",$action->actions);
			Session::put('actions',$actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}

		$token		=	rand('100000','999999').''.time();		
		Session::put('form_token',$token);

		$totals = ['P'=>0,'A'=>0,'L'=>0,'H'=>0];		
		
		$deployment	=	DB::table('resource_tbl')->where('userid',$this->usr->userid)->first();
		
		$deploymentDate = Carbon::parse($deployment->deployed_date);

		$currentMonth = $request->get('month') ? Carbon::parse($request->get('month'))->startOfMonth() : now()->startOfMonth();
		
		$todayMonth = now()->startOfMonth();

		if($currentMonth < $deploymentDate)
		{
			$currentMonth = $deploymentDate;
		}

		if($currentMonth > $todayMonth)
		{
			$currentMonth = $todayMonth;
		}
		
		$startOfMonth = $currentMonth->copy()->startOfMonth();
		$endOfMonth   = $currentMonth->copy()->endOfMonth();		

		if($currentMonth < $deploymentDate->copy()->startOfMonth())
		{
			$currentMonth = $deploymentDate->copy()->startOfMonth();
		}

		$attendanceRecords	=	DB::table('resource_attendance')->where('user_id', $this->usr->userid)
								->whereBetween('work_date', [$startOfMonth, $endOfMonth])
								->get()
								->keyBy(function ($item) {
									return Carbon::parse($item->work_date)->format('Y-m-d');
								});

		$calendarDays	=	[];
		$date			= 	$startOfMonth->copy();

		while($date <= $endOfMonth)
		{

			$formattedDate = $date->format('Y-m-d');
			if($date < Carbon::parse($deploymentDate))
			{
				$calendarDays[] = [
					'date' 		=>	$formattedDate,
					'day' 		=>	$date->day,
					'day_name' 	=>	$date->format('D'),
					'status' 	=>	null,
					'in_time' 	=>	null,
					'out_time' 	=>	null,
				];

			}
			else
			{
				$record = $attendanceRecords[$formattedDate] ?? null;
				$calendarDays[] = [
					'date' 		=> $formattedDate,
					'day' 		=> $date->day,
					'day_name' 	=> $date->format('D'),
					'status' 	=> $record->status ?? null,
					'in_time' 	=> $record->in_time ?? null,
					'out_time' 	=> $record->out_time ?? null,
				];
			}
			$date->addDay();
		}
		
		$previousMonth	=	$currentMonth->copy()->subMonth()->format('Y-m');
		$nextMonth		=	$currentMonth->copy()->addMonth()->format('Y-m');
		$userId			=	$this->usr->userid;

		foreach($attendanceRecords as $record)
		{
			if(isset($totals[$record->status]))
			{
				$totals[$record->status]++;
			}
		}		

        return view('admin/resources/monthly_attendance',compact('token','calendarDays','currentMonth','previousMonth','nextMonth','userId','totals'));
		
    }

    public function mprMonths(Request $request)
	{
        Session::put('adminmenu','resourcemprs');
		Session::put('adminsubmenu','monthlympr');
		Session::put('menid',131);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=131 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
						
			$actions	=	explode(",",$action->actions);
			Session::put('actions',$actions);
		}
		else
		{
			$action =	DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}

		$token		=	rand('100000','999999').''.time();		
		Session::put('form_token',$token);
		
		$resource	=	DB::table('resource_tbl')->where('userid',$userId)->first();
		
		if($resource && $resource->deployed_date)
		{
			$deployedDate = Carbon::parse($resource->deployed_date);
			$startLimit   = Carbon::create(2026, 1, 1);

			$startDate = $deployedDate->lt($startLimit) ? $startLimit : $deployedDate;

			$startDate   = $startDate->startOfMonth();
			$currentDate = Carbon::now()->startOfMonth();

			$months = [];
			$years = [];
			while($startDate->lte($currentDate))
			{
				$months[] = [
					'month'			=>	$startDate->format('F'),
					'year'			=>	$startDate->format('Y'),
					'month_number' 	=>	$startDate->format('m')
				];
				$startDate->addMonth();
			}
		}
		$years = array_unique(array_column($months, 'year'));
        return view('admin/resources/mpr_months',compact('token','months','years'));
		
    }

    public function monthlyProgressReport(Request $request)
	{
        $rules = [
            'mpr_month'	=>	'required|numeric',
			'mpr_year'	=>	'required|numeric',
        ];

		$messages = [
			'mpr_month.required' => 'The month field is required.',
			'mpr_month.numeric'  => 'The month must be a valid numeric value.',
			'mpr_month.between'  => 'The month must be between 1 and 12.',

			'mpr_year.required'  => 'The year field is required.',
			'mpr_year.numeric'   => 'The year must be a valid numeric value.',
			'mpr_year.digits'    => 'The year must be a valid 4-digit year.',
		];

        $validatedData 	=	$request->validate($rules,$messages);

        $userId      	= 	$request->session()->get('userId');
        $userName      	= 	$request->session()->get('userName');
		
		$resource	=	DB::table('eoi_resource_deployment')
						->where('userid',$userId)
						->where('old_deployment_id',0)
						->first();
		if(!$resource)
		{
            return response()->json([
                'status'  => 400,
                'message' => $e->getMessage()
            ], 500);
			
		}
		DB::beginTransaction();

        try
		{
            $startDate	=	Carbon::createFromDate($validatedData['mpr_year'], $validatedData['mpr_month'],1);

			$deployedDate	=	Carbon::parse($resource->deployed_date);

			if($startDate->lt($deployedDate))
			{
				$startDate = $deployedDate->copy();
			}			

            $endDate=	$startDate->copy()->endOfMonth();
			
			$today	=	Carbon::today();
			
			if($endDate->gt($today))
			{
				$endDate = $today->copy();
			}
			
            while($startDate<=$endDate)
			{
                $workDate	=	$startDate->toDateString();
                $exists		= 	DB::table('resource_attendance')
								->where('user_id', $userId)
								->whereDate('work_date', $workDate)
								->exists();

                if(!$exists)
				{
                    $status = ($startDate->isSaturday() || $startDate->isSunday()) ? 'H' : 'A';

                    DB::table('resource_attendance')->insert([
                        'order_id'      => $resource->orderid,
                        'deployment_id' => $resource->deploymentid,
                        'user_id'       => $userId,
                        'work_date'     => $workDate,
                        'work_day'      => $startDate->day,
                        'work_month'    => $startDate->month,
                        'work_year'     => $startDate->year,
                        'status'        => $status,
                        'created_at'    => now(),
                    ]);
                }

                $startDate->addDay();
            }

            DB::commit();
			$data	=	DB::table('resource_attendance')
						->where('user_id',$userId)
						->where('work_month',$validatedData['mpr_month'])
						->where('work_year',$validatedData['mpr_year'])
						->orderBy('work_date')
						->get();
			
			$html = view('admin/resources/ajaxpages/mprTable', ['data' => $data])->render();
			
			return response()->json(['status'=>200,'message'=>'Attendance Data.','html' => $html]);
        }
		catch(QueryException $e)
		{
            DB::rollBack();
			Log::error('Error '.$e->getMessage());
            return response()->json(['status'  => 400,'message' => $e->getMessage()], 500);
        }
    }
    
}
