<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
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

class RemunerationController extends Controller
{
    public function manageRemuneration(Request $request)
	{
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addratelist');
		Session::put('menid',140);
		$userId	=	$request->session()->get('userId');
		$issuper=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=140 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$rates	=	DB::table('remuneration_rate_list as a')
					->select('a.*','b.jobcategory')
					->join('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
					->where('a.isactive',1)
					->get();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/manage_remuneration',compact('category','token','rates'));
    }

    public function createRateList(Request $request,$rateid=NULL)
	{
		$rate		=	DB::table('remuneration_rate_list')->where('rateid',Crypt::decrypt($rateid))->first();
		
		if(!$rate)
		{
			return back()->with('fail','We couldn’t find a rate list with the given ID.')->withInput();
		}
		$category	=	DB::table('jobcategory_tbl')->where('categoryid',$rate->categoryid)->orderby('jobcategory')->get();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$startDate 	= 	Carbon::parse($rate->startDate)->addYear();
		$endDate 	= 	Carbon::parse($rate->endDate)->addYear();
		$rateId		=	Crypt::encrypt($rate->rateid);
        return view('admin/master/create_remuneration',compact('category','token','startDate','endDate','rateId'));
    }
	
	/*
    public function storeRateList()
    {
		$rules = [
			'ratelist_name'	=>	'required||regex:/^[A-Za-z0-9,\'.\-\(\)\[\]&\s]+$/',
			'categoryid'	=>	'required|numeric|exists:jobcategory_tbl,caegoryid',
			'incremented_by'=>	'required|numeric',
		];
		
		$messages = [
			'ratelist_name.required' => 'The rate list name field is required.',
			'ratelist_name.regex'    => 'The rate list name may only contain letters, numbers, spaces, and the following characters: , \' - ( ) [ ] . &.',

			'categoryid.required'    => 'The category field is required.',
			'categoryid.numeric'     => 'The category must be a valid number.',
			'categoryid.exists'      => 'The selected category is invalid.',

			'incremented_by.required'=> 'The incremented by field is required.',
			'incremented_by.numeric' => 'The incremented by field must be a number.',
		];
		
        $validatedData 	= $request->validate($rules,$messages);

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
			DB::beginTransaction();
			$rateid	=	DB::table('remuneration_rate_list')->insertGetId([
							'ratelist_name'		=>	$validatedData['ratelist_name'] ?? NULL,
							'incremented_by'	=>	$validatedData['incremented_by'] ?? NULL,
							'categoryid'		=>	$validatedData['categoryid'] ?? NULL,
							'isactive'			=>	1,
							'created_on'		=>	now(),
							'applied_on'		=>	now()
						]);
			
			$activeRate		=	DB::table('remuneration_rate_list')
								->select('rateid')
								->where('categoryid',$validatedData['categoryid'])
								->where('isactive',1)
								->first();
			
			if(!$activeRate)
			{
				return back()->with('fail', 'No active rate list found for the selected category. Please select a different category or contact support.')->withInput();
			}
			
			$remunerations	=	DB::table('remuneration_tbl as a')
								->select('a*')
								->where('categoryid',$validatedData['categoryid'])
								->where('rateid',$activeRate->rateid)
								->get();
								
			$percentage		=	$validatedData['incremented_by'] ?? 0;
			foreach($remunerations as $remuneration)
			{
				$updated 	= 	round($remuneration->remuneration + (($remuneration->remuneration * $percentage) / 100),0);
				
				DB::table('remuneration_tbl')->insert([
					'rateid'			=>	$rateid,
					'categoryid'		=>	$remuneration->categoryid,
					'tierid'			=>	$remuneration->tierid,
					'experienceid'		=>	$remuneration->experienceid,
					'experiencelevel'	=>	$remuneration->experiencelevel,
					'sectorid'			=>	$remuneration->sectorid,
					'positionid'		=>	$remuneration->positionid,
					'remuneration'		=>	$updated,
					'oldremuneration'	=>	$remuneration->remuneration,
					'createdby'			=>	$userId,
					'creationdate'		=>	now()
				]);
			}
			DB::table('remuneration_rate_list')
			->where('rateid',$activeRate->rateid)
			->update(['isactive',0]);
			
			DB::commit();
			
			return back()->with('success','The rate list has been successfully created and activated. All new EoIs will use the updated price list.');
		}
		catch(Exception $e)
		{
			Log::error('Error '+$e->getMessage());
			return back()->with('duplicate','Something went wrong. Please check the data and try again.')->withInput();	
		}
    }	
	*/

	public function storeRateList(Request $request)
	{
		request()->merge([
			'rateid' 	=>	Crypt::decrypt(request('rateid')),
			'startDate'	=> 	Carbon::parse($request->startDate)->format('Y-m-d'),
			'endDate'  	=> 	Carbon::parse($request->endDate)->format('Y-m-d'),
		]);
		
		$rules = [
			'rateid'		=>	'required|exists:remuneration_rate_list,rateid',
			'categoryid'	=>	'required|numeric|exists:jobcategory_tbl,categoryid',
			'incremented_by'=>	'required|numeric',
			'startDate'		=>	'required|date',
			'endDate'		=>	'required|date',
		];
		
		$messages = [
			'rateid.exists' 		=> 'Invalid rate list detail provided',
			
			'categoryid.required'    => 'The category field is required.',
			'categoryid.numeric'     => 'The category must be a valid number.',
			'categoryid.exists'      => 'The selected category is invalid.',

			'incremented_by.required'=> 'The incremented by field is required.',
			'incremented_by.numeric' => 'The incremented by field must be a number.',

			'startDate.required' => 'The start date field is required.',
			'startDate.date'     => 'The start date must be a valid date.',

			'endDate.required'   => 'The end date field is required.',
			'endDate.date'       => 'The end date must be a valid date.',
			
		];
		
        $validatedData 	= $request->validate($rules,$messages);

		// Prevent duplicate submission
		if ($request->input('form_token') !== session()->pull('form_token')) {
			return response()->json([
				'status' => 400,
				'message'=> 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.'
			], 200);
		}

		try
		{
			return DB::transaction(function () use ($validatedData)
			{

				$now = now();
				$percentage = $validatedData['incremented_by'];

				$activeRate = DB::table('remuneration_rate_list')
					->where('categoryid', $validatedData['categoryid'])
					->where('isactive', 1)
					->value('rateid');

				if (!$activeRate) {
					return back()->with('fail',
						'No active rate list found for the selected category.'
					)->withInput();
				}
				
				$category	=	DB::table('jobcategory_tbl')
								->where('categoryid',$validatedData['categoryid'])
								->first();
				
				$rateLitName	=	date('d\-m\-Y',strtotime($validatedData['startDate'])).' To '.date('d\-m\-Y',strtotime($validatedData['endDate'])).' ['.$category->shortname.']';
				
				$rateId = DB::table('remuneration_rate_list')->insertGetId([
					'startDate' 	=> $validatedData['startDate'] ?? NULL,
					'endDate' 		=> $validatedData['endDate'],
					'ratelist_name' => $rateLitName,
					'incremented_by'=> $percentage,
					'categoryid'    => $validatedData['categoryid'],
					'isactive'      => 1,
					'created_on'    => $now,
					'applied_on'    => $now,
					'created_by'	=>	session('userId')
				]);

				$remunerations = DB::table('remuneration_tbl')
					->where('categoryid', $validatedData['categoryid'])
					->where('rateid', $activeRate)
					->get();

				$insertData = [];

				foreach ($remunerations as $r)
				{
					$updated = round($r->remuneration * (1 + $percentage / 100), 0);

					$insertData[] = [
						'rateid'            => $rateId,
						'categoryid'        => $r->categoryid,
						'tierid'            => $r->tierid,
						'experienceid'      => $r->experienceid,
						'experiencelevel'   => $r->experiencelevel,
						'sectorid'          => $r->sectorid,
						'positionid'        => $r->positionid,
						'remuneration'      => $updated,
						'oldremuneration'   => $r->remuneration,
						'createdby'         => auth()->id(),
						'creationdate'      => $now
					];
				}

				if(!empty($insertData)) {
					DB::table('remuneration_tbl')->insert($insertData);
				}


				DB::table('remuneration_rate_list')
					->where('rateid', $activeRate)
					->update(['isactive' => 0]);
				
				return redirect('master/add/ratelist')->with(['success' => 'Rate list created and activated successfully.']);
				/*
				return back()->with('success',
					'Rate list created and activated successfully.'
				);
				*/
			});

		}
		catch (\Exception $e)
		{
			Log::error('Error: ' . $e->getMessage());
			return back()->with('error','Something went wrong. Please try again.')->withInput();
		}
	}	

}
