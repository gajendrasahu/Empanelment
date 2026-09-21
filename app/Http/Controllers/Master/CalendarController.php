<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
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
use Illuminate\Support\Facades\Mail;

class CalendarController extends Controller
{
    public function createHoliday(Request $request)
	{

        Session::put('adminmenu','master');
		Session::put('adminsubmenu','createholiday');
		Session::put('menid',139);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$months = [
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December',
		];

        return view('admin/master/create_holiday',compact('token','months'));
    }
	
	public function storeHoliday(Request $request,$recordid=NULL)
	{
        $rules = [
            'holiday_title' => 'required|max:255|regex:/^[a-zA-Z0-9\s,\.\(\)\[\]@%\'&\*-]+$/',
			'start_date' => [
				'required',
				'date',
				Rule::unique('holiday_tbl', 'start_date')->ignore($recordid),
			],			
			'end_date' 		=> 'required|date|after_or_equal:start_date',
			'is_recurring' 	=> 'nullable|boolean',
			'recurring_type'=> 'nullable|required_if:is_recurring,1|in:yearly,monthly',
        ];

		$messages = [
			'holiday_title.required' =>	'Holiday title is required.',
			'holiday_title.regex'    =>	'Holiday title may only contain letters, numbers, spaces, and , \' & * ( ) [ ] @ % characters.',
			'holiday_title.max'      =>	'Holiday title must not exceed 255 characters.',

			'start_date.required'    =>	'Start date is required.',
			'start_date.date'        =>	'Start date must be a valid date.',

			'end_date.required'      =>	'End date is required.',
			'end_date.date'          =>	'End date must be a valid date.',
			'end_date.after_or_equal'=>	'End date must be after of equal to start date.',
			
			'is_recurring.boolean'		=>	'Recurring flag must be true or false.',
			'recurring_type.required_if'=>	'Recurring type is required when recurring is enabled.',
			'recurring_type.in'         =>	'Recurring type must be either yearly or monthly.',
		];

        $validatedData = $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return response()->json([
				'status'	=>	400,
				'message'	=>	'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.'
			],200);				

		}		
		try
		{
			if($recordid==NULL)
			{
				DB::table('holiday_tbl')
				->insert([
					'title'				=>	$validatedData['holiday_title'],
					'start_date'		=>	date('Y\-m\-d',strtotime($validatedData['start_date'])),
					'end_date'			=>	date('Y\-m\-d',strtotime($validatedData['end_date'])),
					'created_at' 		=> 	now(),
					'updated_at' 		=> 	now(),
					'is_recurring' 		=> 	$request->is_recurring ? 1 : 0,
					'recurring_type' 	=> 	$request->is_recurring ? $request->recurring_type : null,
				]);
				
				return back()->with('success','The holiday details have been stored successfully.');	
			}
			else
			{
				DB::table('holiday_tbl')
				->where('id',Crypt::decrypt($recordid))
				->update([
					'title'				=>	$validatedData['holiday_title'],
					'start_date'		=>	date('Y\-m\-d',strtotime($validatedData['start_date'])),
					'end_date'			=>	date('Y\-m\-d',strtotime($validatedData['end_date'])),
					'updated_at' 		=> 	now(),
					'is_recurring' 		=> 	$request->is_recurring ? 1 : 0,
					'recurring_type' 	=> 	$request->is_recurring ? $request->recurring_type : null,
				]);
				return redirect()->route('create.holiday')->with('success', 'The holiday details have been updated successfully.');
			}
		}
		catch(QueryException $e)
		{
			Log::error('Error: ' . $e->getMessage());
			return back()->with('duplicate','Something went wrong. Please check the data and try again.')->withInput();				
		}
	}

    public function holidayData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage	= 	$request->input('page', 1);
		$holiday_month	=	$request->input('holiday_month') ?? NULL;
		$is_recurring	=	$request->input('is_recurring') ?? NULL;
		
        $data	=	DB::table('holiday_tbl as a')
					->when($is_recurring==1,function($query) use ($is_recurring){
						return $query->where('is_recurring',$is_recurring);
					})
					->when($holiday_month,function($query) use ($holiday_month){
						return $query->whereMonth('start_date',$holiday_month);
					})
					->when($pagesearch!='',function($query) use ($pagesearch){
						return $query->where('title','like','%'.$pagesearch.'%');
					})
					->orderBy('start_date')
					->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/holidayTable', ['data' => $data]);

    }

	public function calendarView(Request $request)
	{
		$month = $request->month ? Carbon::parse($request->month) : Carbon::now();

		$prevMonth = $month->copy()->subMonth()->format('Y-m-01');
		$nextMonth = $month->copy()->addMonth()->format('Y-m-01');

		$startOfMonth = $month->copy()->startOfMonth();
		$endOfMonth   = $month->copy()->endOfMonth();

		$rangeHolidays = DB::table('holiday_tbl')
			->where(function ($q) use ($startOfMonth, $endOfMonth) {
				$q->whereBetween('start_date', [$startOfMonth, $endOfMonth])
				  ->orWhereBetween('end_date', [$startOfMonth, $endOfMonth])
				  ->orWhere(function ($q2) use ($startOfMonth, $endOfMonth) {
					  $q2->where('start_date', '<=', $startOfMonth)
						 ->where('end_date', '>=', $endOfMonth);
				  });
			})
			->get();

		$recurringHolidays = DB::table('holiday_tbl')
			->where('is_recurring', 1)
			->get();

		$holidays = $rangeHolidays->merge($recurringHolidays);

		$weeklyOffs = DB::table('holiday_rules')
			->where('type', 'weekly')
			->pluck('value')
			->map(fn ($v) => (int) $v)
			->toArray();

		$daysInMonth = $month->daysInMonth;
		$firstDayOfWeek = $startOfMonth->dayOfWeek;

		$calendar = [];
		$week = [];

		// pad first week
		for ($i = 0; $i < $firstDayOfWeek; $i++) {
			$week[] = null;
		}

		for ($day = 1; $day <= $daysInMonth; $day++) {

			$date = Carbon::create($month->year, $month->month, $day);
			$dow  = $date->dayOfWeek;

			$isWeeklyOff = in_array($dow, $weeklyOffs);

			$holidayTitles = [];

			foreach ($holidays as $h) {

				$start = Carbon::parse($h->start_date);
				$end   = !empty($h->end_date) ? Carbon::parse($h->end_date) : null;

				if ($end && $date->between($start, $end)) {
					$holidayTitles[] = $h->title;
					continue;
				}

				if (!empty($h->is_recurring)) {
					if ($date->format('m-d') === $start->format('m-d')) {
						$holidayTitles[] = $h->title;
					}
				}
			}

			$isHoliday = !empty($holidayTitles);

			$week[] = [
				'date' => $date->format('Y-m-d'),
				'day'  => $day,
				'is_weekly_off' => $isWeeklyOff,
				'is_holiday'    => $isHoliday,
				'holiday_titles' => $holidayTitles,
			];

			if (count($week) == 7) {
				$calendar[] = $week;
				$week = [];
			}
		}

		// fill last week
		if (!empty($week)) {
			while (count($week) < 7) {
				$week[] = null;
			}
			$calendar[] = $week;
		}
		
		$monthHolidays	=	DB::table('holiday_tbl')
							->whereBetween('start_date', [$startOfMonth, $endOfMonth])
							->orderBy('start_date')
							->get();
		
		return view('admin/ajaxpages/calendarView', compact(
			'calendar',
			'month',
			'prevMonth',
			'nextMonth',
			'monthHolidays'
		));
	}

    public function editHoliday($recordid)
	{
		$data = DB::table('holiday_tbl')->where('id','=',Crypt::decrypt($recordid))->first();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

		$months = [
			1 => 'January',
			2 => 'February',
			3 => 'March',
			4 => 'April',
			5 => 'May',
			6 => 'June',
			7 => 'July',
			8 => 'August',
			9 => 'September',
			10 => 'October',
			11 => 'November',
			12 => 'December',
		];
		
        return view('admin/master/holiday_edit',compact('data','token','months'));
    }
	
}
