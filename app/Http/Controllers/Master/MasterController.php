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
use App\Jobs\CheckOrderStatus;
//use App\Events\OrderCreated;
use Illuminate\Support\Facades\Log;
use Smalot\PdfParser\Parser;

class MasterController extends Controller
{
	protected $displayOrderService;
    public function __construct(DisplayOrderService $displayOrderService)
    {
        $this->displayOrderService = $displayOrderService;
		ini_set('serialize_precision', -1);
    }
	
    public function preBidFormat(Request $request)
	{
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','prebidformat');
		Session::put('menid',112);
		$userId	= $request->session()->get('userId');
		$issuper= $request->session()->get('issuper');
		$formats	=	DB::table('prebid_formats')->where('recordid',1)->first();
		$token		=	rand('100000','999999').''.time();
		
		Session::put('form_token',$token);
		
        return view('admin/master/prebid_format',compact('token','formats'));
    }

    public function storePreBidFormat(Request $request,$recordid)
	{    
        
        $rules = [
			'queryformat' 	=> 	'required|file|mimetypes:application/pdf|max:2048',
			'responseformat'=> 	'required|file|mimetypes:application/pdf|max:2048',
        ];

        $messages = [
            'queryformat.required' 		=>	'Query format is mandatory',
            'queryformat.max' 			=> 	'Maximum 2 MB size is allowed',
			'queryformat.mimetypes' 	=> 	'Invalid file format',
			'queryformat.file' 			=> 	'Invalid file format',
			'responseformat.required' 	=>	'Response format is mandatory',
			'responseformat.max' 		=> 	'Maximum 2 MB size is allowed',
			'responseformat.mimetypes' 	=> 	'Invalid file format',
			'responseformat.file' 		=> 	'Invalid file format',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$queryformat 	=	$request->file('queryformat');
		$responseformat = 	$request->file('responseformat');


		if($request->hasFile('queryformat'))
		{
			$queryformat = $request->file('queryformat')->store('uploads/prebidformat', 'public');
		}

		if($request->hasFile('responseformat'))
		{
			$responseformat = $request->file('responseformat')->store('uploads/prebidformat', 'public');
		}

		try 
		{
			$formats	=	DB::table('prebid_formats')->first();
			if(!$formats)
			{
				DB::table('prebid_formats')->insert([
					'queryformat'		=>	$queryformat,
					'responseformat'	=>	$responseformat,
				]);
			}
			else
			{
				$formats	=	DB::table('prebid_formats')->where('recordid',1)->first();
				
				Storage::disk('public')->delete($formats->queryformat);
				Storage::disk('public')->delete($formats->responseformat);

				DB::table('prebid_formats')->where('recordid',$formats->recordid)->update([
					'queryformat'		=>	$queryformat,
					'responseformat'	=>	$responseformat,
				]);
			}
			return back()->with('success','Pre bid formats stored successfully!');
		}
		catch(QueryException $e)
		{
			if($queryformat!='')
			Storage::disk('public')->delete($queryformat);
			if($responseformat!='')
			Storage::disk('public')->delete($responseformat);
			
			return back()->with('duplicate',$e->getMessage())->withInput();

		}

    }
	
	/* COMMITTEE MEMBER */
    public function addCommitteeMember(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addcommittee');
		Session::put('menid',101);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/master/committee_add',compact('token'));
    }
    public function storeCommitteeMember(Request $request,$recordid)
	{        
        $rules = [
            'name'			=> 'required|max:100|regex:/^[a-zA-Z\s.]+$/',
			'mobilenumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
			'designation'	=> 'required|max:100|regex:/^[a-zA-Z\s.]+$/',
			'department'	=> 'nullable|max:150|regex:/^[a-zA-Z\s.]+$/',
			'email'			=> 'nullable|email:rfc,dns',
			'remark' 		=> 'nullable|max:300|regex:/^[a-zA-Z\s\.,!?\-_]+$/',
        ];

        $messages = [
            'name.required'			=> 'Name is required',
			'name.max'				=> 'Invalid name',
			'name.regex'			=> 'Invalid name',
			'mobilenumber.regex' 	=> 'Invalid mobile number',
			'email.email' 			=> 'Invalid email address',
			'designation.required' 	=> 'Designation is required',
			'designation.max' 		=> 'Maximum length is 100',
			'designation.regex' 	=> 'Invalid designation name',
			'department.max' 		=> 'Maximum length is 150',
			'department.regex' 		=> 'Invalid department name',
			'remark.max' 			=> 'Maximum length is 300',
			'remark.regex' 			=> 'Remark can only contain letters, spaces, and . , ! ? - _ characters.',
        ];
		Log::info('Error: ');
        $validatedData 	= $request->validate($rules,$messages);
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		if($recordid==0)
		{
			DB::beginTransaction();
			try
			{
				DB::table('committee_member')->insert([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'],
					'designation'	=>	$validatedData['designation'] ?? '',
					'department'	=>	$validatedData['department'] ?? '',
					'email'			=>	$validatedData['email'] ?? NULL,
					'remark'		=>	$validatedData['remark'] ?? '',
					'userid'		=>	Session::get('userId'),
					'usertype'		=>	'CHIPS',
					'creationdate'	=>	now(),
				]);
				DB::commit();
				return back()->with('success','Committee member detail stored successfully!');
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				Log::error('Error: ' . $e->getMessage());
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			DB::beginTransaction();
			try
			{
				DB::table('committee_member')->where('memberid',Crypt::decrypt($recordid))->update([
					'name'			=>	$validatedData['name'],
					'mobilenumber'	=>	$validatedData['mobilenumber'],
					'email'			=>	$validatedData['email'] ?? NULL,
					'designation'	=>	$validatedData['designation'] ?? '',
					'department'	=>	$validatedData['department'] ?? '',
					'remark'		=>	$validatedData['remark'] ?? '',
				]);
				DB::commit();
				return redirect()->route('add.committeemember')->with('success', 'Committee member detail stored successfully!');
			}
			catch(QueryException $e)
			{
				DB::rollBack();
				Log::error('Error: ' . $e->getMessage());
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
    }
    public function getCommitteeMemberData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page', 1);
        $data = DB::table('committee_member as a')
				->select('a.*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.name','like','%'.$pagesearch.'%')
								 ->orwhere('a.designation','like','%'.$pagesearch.'%')
								 ->orwhere('a.mobilenumber','like','%'.$pagesearch.'%');;
                })
                ->where('a.usertype','CHIPS')
				->orderBy('a.name')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/committeememberTable', ['data' => $data]);

    }
    public function editCommitteeMember($recordid)
	{
		$data = DB::table('committee_member')->where('memberid','=',Crypt::decrypt($recordid))->first();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/master/committee_edit',compact('data','token'));
    }
	
	/*COMMITTEE MEMBER CLOSED*/
	/*POSITION RELATED*/
    public function addPosition(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addposition');
		Session::put('menid',91);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=91 and ispermitted=1 and userid=".$userId.") as actions"))
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
		Session::put('form_token', $token);
        return view('admin/master/position_add',compact('token'));
    }
    public function storePosition(Request $request,$recordid)
	{        
        $rules = [
            'consultantposition'=> 'required|max:150|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'consultantposition.required'	=> 'POSITION IS REQUIRED',
            'consultantposition.max' 		=> 'MAXIMUM LENGTH IS 100',
			'consultantposition.regex' 		=> 'INVALID CONSULTANT POSITION',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO position_tbl(consultantposition,createdby,creationdate) VALUES (?,?,?)',[$validatedData['consultantposition'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update position_tbl set consultantposition=? where positionid=?',[$validatedData['consultantposition'],$recordid]);
				return redirect('/master/add/position')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editPosition($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$data 		= 	DB::table('position_tbl')->where('positionid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/position_edit',compact('data','token'));
    }
	
    public function getPositionData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('position_tbl as a')
				->select('a.*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.consultantposition','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.consultantposition')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/positionTable', ['data' => $data]);

    }
    public function deletePosition($recordid)
	{
		$res = true;//DB::delete('DELETE FROM position_tbl WHERE positionid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*POSITION RELATED CLOSED*/

	/* SECTOR MASTER RELATED*/
    public function addSector(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addsector');
		Session::put('menid',90);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=90 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
        return view('admin/master/sector_add',compact('token'));
    }
    public function storeSector(Request $request,$recordid)
	{        
        $rules = [
            'sectorname' 		=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'sectorname.required' 	=> 'TIER NAME IS REQUIRED',
            'sectorname.max' 		=> 'MAXIMUM LENGTH IS 50',
			'sectorname.regex' 		=> 'INVALID SECTOR NAME',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO sector_tbl(sectorname,createdby,creationdate) VALUES (?,?,?)',[$validatedData['sectorname'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update sector_tbl set sectorname=? where sectorid=?',[$validatedData['sectorname'],$recordid]);
				return redirect('/master/add/sector')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editSector($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('sector_tbl')->where('sectorid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/sector_edit',compact('data','token'));
    }
	
    public function getSectorData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('sector_tbl')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('sectorname','like','%'.$pagesearch.'%');
                })
                ->orderBy('sectorname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/sectorTable', ['data' => $data]);

    }
    public function deleteSector($recordid)
	{
		$res = true;//DB::delete('DELETE FROM sector_tbl WHERE sectorid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*SECTOR MASTER RELATED CLOSED*/


	/*UALIFICATION RELATED*/
    public function addQualification(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addqualification');
		Session::put('menid',88);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=88 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$role	=	DB::table('role_tbl')->orderby('role')->get();
        return view('admin/master/qualification_add',compact('token','role'));
    }
    public function storeQualification(Request $request,$recordid)
	{        
        $rules = [
            'qualification' 		=> 'required|max:100|regex:/^[a-zA-Z0-9\s.]+$/',
			'roleid'=> 'required|numeric',
        ];

        $messages = [
            'qualification.required'			=> 'QUALIFICATION IS REQUIRED',
            'qualification.max' 				=> 'MAXIMUM LENGTH IS 100',
			'qualification.regex' 				=> 'Special characters not allowed',
            'roleid.required'	=> 'ROLE IS REQUIRED',
            'roleid.max' 		=> 'INVALID CATEGORY NAME',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO qualification_tbl(qualification,roleid,createdby,creationdate) VALUES (?,?,?,?)',[$validatedData['qualification'],$validatedData['roleid'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update qualification_tbl set roleid=?,qualification=? where qualificationid=?',[$validatedData['roleid'],$validatedData['qualification'],$recordid]);
				return redirect('/master/add/qualification')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editQualification($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$role	=	DB::table('role_tbl')->orderby('role')->get();
		$data 		= 	DB::table('qualification_tbl')->where('qualificationid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/qualification_edit',compact('data','token','category'));
    }
	
    public function getQualificationData(Request $request)
	{
		$roleid 	=	$request->input('roleid');
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= 	$request->input('page', 1);
		//$perPage 	= 	$request->input('perPage', 10);
        $data 		= 	DB::table('qualification_tbl as a')
							->select('a.*','b.role')
							->leftJoin('role_tbl as b','b.roleid','=','a.roleid')
							->when($pagesearch!='',function($query) use ($pagesearch){
								return $query->where('a.qualification','like','%'.$pagesearch.'%');
							})
							->when($roleid!=0,function($query) use ($roleid){
								return $query->where('a.roleid','=',$roleid);
							})
							->orderBy('a.qualification')
							->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/qualificationTable', ['data' => $data]);

    }
    public function deleteQualification($recordid)
	{
		$res = true;//DB::delete('DELETE FROM qualification_tbl WHERE roleid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*QUALIFICATION RELATED CLOSED*/
	

	
	
	/* TIER MASTER RELATED*/
    public function addTierMaster(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addtiermaster');
		Session::put('menid',87);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=87 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/tiermaster_add',compact('token'));
    }
    public function storeTierMaster(Request $request,$recordid)
	{        
        $rules = [
            'tiername' 		=> 'required|max:20|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'tiername.required' => 'TIER NAME IS REQUIRED',
            'tiername.max' 		=> 'MAXIMUM LENGTH IS 20',
			'tiername.regex' 	=> 'Special characters not allowed',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		return back()->with('fail', 'Creation of new tier is prohibited.');
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO tiermaster_tbl(tiername,createdby,creationdate) VALUES (?,?,?)',[$validatedData['tiername'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update tiermaster_tbl set tiername=? where tierid=?',[$validatedData['tiername'],$recordid]);
				return redirect('/master/add/tiermaster')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editTierMaster($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('tiermaster_tbl')->where('tierid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/tiermaster_edit',compact('data','token'));
    }
	
    public function getTierMasterData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('tiermaster_tbl')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('tiername','like','%'.$pagesearch.'%');
                })
                ->orderBy('tiername')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/tiermasterTable', ['data' => $data]);

    }
    public function deleteTierMaster($recordid)
	{
		$res = true;//DB::delete('DELETE FROM district_tbl WHERE districtid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*TIER MASTER RELATED CLOSED*/
	

	/*JOB CATEGORY RELATED*/
    public function addJobCategory(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addjobcategory');
		Session::put('menid',79);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=79 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/jobcategory_add',compact('token'));
    }
    public function storeJobCategory(Request $request,$recordid)
	{        
        $rules = [
            'jobcategory' 		=> 'required|max:100|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'jobcategory.required' 	=> 'CATEGORY NAME IS REQUIRED',
            'jobcategory.max' 		=> 'MAXIMUM LENGTH IS 100',
			'jobcategory.regex' 	=> 'INVALID CATEGORY NAME PROVIDED',
        ];

        $validatedData 	= $request->validate($rules,$messages);
		
		return back()->with('fail', 'Creation of new categories is prohibited.');
		
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$stateid		=	intval($request->input('stateid'));
		$districtname	=	strtoupper($request->input('districtname'));
		$districtcode	=	$request->input('districtcode');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO jobcategory_tbl(jobcategory,createdby,creationdate) VALUES (?,?,?)',[$validatedData['jobcategory'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FOUND SOME PROBLEM')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update jobcategory_tbl set jobcategory=? where categoryid=?',[$validatedData['jobcategory'],$recordid]);
				return redirect('/master/add/jobcategory')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editJobCategory($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('jobcategory_tbl')->where('categoryid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/jobcategory_edit',compact('data','token'));
    }
	
    public function getJobCategoryData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('jobcategory_tbl')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('jobcategory','like','%'.$pagesearch.'%');
                })
                ->orderBy('jobcategory')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/jobcategoryTable', ['data' => $data]);

    }
    public function deleteJobCategory($recordid)
	{
		$res = true;//DB::delete('DELETE FROM district_tbl WHERE districtid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*JOB CATEGORY RELATED CLOSED*/

	
	/*REMUNERATION RELATED*/
    public function addRemuneration(Request $request)
	{
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addremuneration');

		Session::put('menid',70);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=70 and ispermitted=1 and userid=".$userId.") as actions"))
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
		Session::put('form_token', $token);
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		$experience	=	DB::table('work_experience')->orderby('experienceid')->get();
		
		$levels 	= DB::table('remuneration_tbl')->select('experiencelevel')->orderby('experiencelevel')->distinct()->pluck('experiencelevel');
		
		$tierid			=	0;
		$experienceid	=	0;
		$sector			=	DB::table('sector_tbl')->orderby('sectorname')->get();
		$position		=	DB::table('position_tbl')->orderby('consultantposition')->get();
		$rates			=	DB::table('remuneration_rate_list')->orderBy('rateid','DESC')->get();
		
        return view('admin/master/remuneration_add',compact('token','category','experience','levels','tier','sector','position','rates'));
    }
	
    public function storeRemuneration(Request $request,$recordid)
	{
		
		$rules = [
			'categoryid'     	=> 'required|numeric',
			'tierid'         	=> 'required|numeric',
			'remuneration'   	=> 'required|numeric',
			'experienceid'   	=> 'nullable|numeric',
			'experiencelevel'	=> 'nullable|numeric',
			'sectorid'       	=> 'nullable|numeric',
			'positionid'     	=> 'nullable|numeric',
		];

		$messages = [
			'categoryid.required'    	=>	'Category name is required',
			'categoryid.numeric'     	=> 	'Invalid category name',
			'tierid.required'        	=> 	'Tier name is required',
			'tierid.numeric'         	=> 	'Invalid tier name',
			'experienceid.required'  	=> 	'It is required',
			'experienceid.numeric'   	=> 	'Invalid value',
			'experiencelevel.required'	=> 	'It is required',
			'experiencelevel.numeric'	=> 	'Invalid value',
			'remuneration.required'  	=> 	'It is required',
			'remuneration.numeric'   	=> 	'Invalid value',
			'sectorid.required'      	=> 	'It is required',
			'sectorid.numeric'       	=> 	'Invalid value',
			'positionid.required'    	=> 	'It is required',
			'positionid.numeric'     	=> 	'Invalid value',
		];

		$validator = Validator::make($request->all(), $rules, $messages);

		// Add conditional rules
		$validator->sometimes(['experienceid', 'experiencelevel'], 'required', function ($input) {
			return $input->categoryid == 1;
		});

		$validator->sometimes(['sectorid', 'positionid'], 'required', function ($input) {
			return $input->categoryid == 2;
		});

		if ($validator->fails()) {
			return response()->json(['errors' => $validator->errors()], 422);
		}
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		
		$categoryid		=	$request->input('categoryid') ?? 0;
		$tierid			=	$request->input('tierid') ?? 0;
		$remuneration	=	$request->input('remuneration') ?? 0;
		$experienceid	=	$request->input('experienceid') ?? 0;
		$experiencelevel=	$request->input('experiencelevel') ?? 0;
		$sectorid		=	$request->input('sectorid') ?? 0;
		$positionid		=	$request->input('positionid') ?? 0;
		
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		if($recordid==0)
		{
			$rateid	=	DB::table('remuneration_rate_list')->where('categoryid',$categoryid)->where('isactive',1)->value('rateid');
			try
			{
				$res = DB::insert('INSERT INTO remuneration_tbl(rateid,categoryid,tierid,experienceid,experiencelevel,sectorid,positionid,remuneration,createdby,creationdate) VALUES (?,?,?,?,?,?,?,?,?,?)',[$rateid,$categoryid,$tierid,$experienceid,$experiencelevel,$sectorid,$positionid,$remuneration,$userId,$creationdate]);
				
				return back()->with('success','Record stored successfully!');
			}
			catch(QueryException $e)
			{
				Log::error('Error '.$e->getMessage());
				return back()->with('duplicate','Duplicate data found.')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update remuneration_tbl set rateid=?,categoryid=?,tierid=?,experienceid=?,experiencelevel=?,sectorid=?,positionid=?,remuneration=? where remunerationid=?',[$rateid,$categoryid,$tierid,$experienceid,$experiencelevel,$sectorid,$positionid,$remuneration,$recordid]);
				return redirect('/master/add/remuneration')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editRemuneration($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$experience	=	DB::table('work_experience')->orderby('experienceid')->get();
		$category		=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$data = DB::table('remuneration_tbl')->where('remunerationid','=',Crypt::decrypt($recordid))->first();
		$sector	=	DB::table('sector_tbl')->orderby('sectorname')->get();
		$position	=	DB::table('position_tbl')->orderby('consultantposition')->get();
		
        return view('admin/master/remuneration_edit',compact('data','token','experience','category','sector','position'));
    }
    public function getRemunerationData(Request $request)
	{
		$rateid 		=	intval($request->input('rateid'));
		$categoryid 	=	intval($request->input('categoryid'));
		$tierid 		=	intval($request->input('tierid'));
		$experienceid 	=	intval($request->input('experienceid'));
		$experiencelevel=	intval($request->input('experiencelevel'));
		$sectorid		=	intval($request->input('sectorid'));
		$positionid		=	intval($request->input('positionid'));
		
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('remuneration_tbl as a')
				->select('a.*','b.tiername','c.workexperience','d.jobcategory','e.sectorname','f.consultantposition','g.ratelist_name')
				->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
				->leftJoin('work_experience as c','c.experienceid','=','a.experienceid')
				->leftJoin('jobcategory_tbl as d','d.categoryid','=','a.categoryid')
				->leftJoin('sector_tbl as e','e.sectorid','=','a.sectorid')
				->leftJoin('position_tbl as f','f.positionid','=','a.positionid')
				->leftJoin('remuneration_rate_list as g','g.rateid','=','a.rateid')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('b.tiername','like','%'.$pagesearch.'%')
								 ->orwhere('c.workexperience','like','%'.$pagesearch.'%');
                })
                ->when($tierid!=0,function($query) use ($tierid){
                    return $query->where('a.tierid','=',$tierid);
                })
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('a.categoryid','=',$categoryid);
                })
                ->when($experienceid!=0,function($query) use ($experienceid){
                    return $query->where('a.experienceid','=',$experienceid);
                })				
                ->when($experiencelevel!=0,function($query) use ($experiencelevel){
                    return $query->where('a.experiencelevel','=',$experiencelevel);
                })				
                ->when($sectorid!=0,function($query) use ($sectorid){
                    return $query->where('a.sectorid','=',$sectorid);
                })				
                ->when($positionid!=0,function($query) use ($positionid){
                    return $query->where('a.positionid','=',$positionid);
                })				
                ->when($rateid!=0,function($query) use ($rateid){
                    return $query->where('a.rateid','=',$rateid);
                })				
                ->orderBy('a.remuneration')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/remunerationTable', ['data' => $data]);
	}
	public function getRemunerationForm(Request $request)
	{
		$categoryid	=	$request->input('categoryid');
		$tierid		=	$request->input('tierid');
		$experience	=	DB::table('work_experience')->orderby('experienceid')->get();
		
		$levels 	= DB::table('remuneration_tbl')->select('experiencelevel')->distinct()->pluck('experiencelevel');
		
		$tierid			=	0;
		$experienceid	=	0;
		$sector	=	DB::table('sector_tbl')->orderby('sectorname')->get();
		$position	=	DB::table('position_tbl')->orderby('consultantposition')->get();
		
		return view('admin/ajaxpages/remunerationForm',compact('experience','levels','sector','position','categoryid','tierid'));
	}
    public function deleteRemuneration($recordid)
	{
		$res = true;//DB::delete('DELETE FROM remuneration_tbl WHERE experienceid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*REMUNERATION RELATED CLOSED*/
	/*CHARGES RELATED*/
    public function addCharges(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addcharges');

		Session::put('menid',69);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=69 and ispermitted=1 and userid=".$userId.") as actions"))
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
		Session::put('form_token', $token);
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
        return view('admin/master/charges_add',compact('token','category','tier'));
    }
    public function storeCharges(Request $request,$recordid)
	{        
        $rules = [
            'categoryid'		=> 'required|numeric',
			'tierid'			=> 'required|numeric',
			'operatingmargin'   => [
				Rule::requiredIf($request->input('categoryid') == 1),
				'nullable',
				'numeric',
				'max:100'
			],
			'tax'				=> 'required|numeric|max:100',
			'admincharge'		=> 'required|numeric|max:100',
        ];

        $messages = [
            'categoryid.required' 		=> 'CATEGORY NAME IS REQUIRED',
			'categoryid.numeric' 		=> 'INVALID CATEGORY',
			'tierid.required' 			=> 'TIER NAME IS REQUIRED',
            'tierid.max' 				=> 'INVALID TIER NAME',
            'operatingmargin.required' 	=> 'MARGIN IS REQUIRED',
            'operatingmargin.numeric' 	=> 'INVALID VALUE',
			'operatingmargin.max' 		=> 'INVALID VALUE',
            'tax.required' 				=> 'TAX IS REQUIRED',
            'tax.numeric' 				=> 'INVALID TAX VALUE',
			'tax.max' 					=> 'INVALID TAX VALUE',
            'admincharge.required' 		=> 'IT IS REQUIRED',
            'admincharge.numeric' 		=> 'INVALID VALUE',
			'admincharge.max' 			=> 'INVALID VALUE',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$operatingmargin	=	$request->input('operatingmargin') ?? 0;
		
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO pricing_tbl(categoryid,tierid,operatingmargin,tax,admincharge,createdby,creationdate) VALUES (?,?,?,?,?,?,?)',[$validatedData['categoryid'],$validatedData['tierid'],$operatingmargin,$validatedData['tax'],$validatedData['admincharge'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update pricing_tbl set categoryid=?,tierid=?,operatingmargin=?,tax=?,admincharge=? where priceid=?',[$validatedData['categoryid'],$validatedData['tierid'],$operatingmargin,$validatedData['tax'],$validatedData['admincharge'],$recordid]);
				return redirect('/master/add/charges')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editCharges($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		$category	=	DB::table('jobcategory_tbl')->orderby('jobcategory')->get();
		$tier		=	DB::table('tiermaster_tbl')->orderby('tiername')->get();
		$data 		= 	DB::table('pricing_tbl')->where('priceid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/charges_edit',compact('data','token','category','tier'));
    }
	
    public function getChargesData(Request $request)
	{
		$categoryid =	intval($request->input('categoryid'));
		$tierid 	=	intval($request->input('tierid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('pricing_tbl as a')
				->select('a.*','b.jobcategory','c.tiername')
				->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
				->leftJoin('tiermaster_tbl as c','c.tierid','=','a.tierid')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.tiername','like','%'.$pagesearch.'%')
								 ->orwhere('b.jobcategory','like','%'.$pagesearch.'%')
								 ->orwhere('c.tiername','like','%'.$pagesearch.'%');
                })
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('a.categoryid','=',$categoryid);
                })								
                ->when($tierid!=0,function($query) use ($tierid){
                    return $query->where('a.tierid','=',$tierid);
                })								
                ->orderBy('c.tiername')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/chargesTable', ['data' => $data]);

    }
    public function deleteCharges($recordid)
	{
		$res = true;//DB::delete('DELETE FROM pricing_tbl WHERE priceid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*CHARGES RELATED CLOSED*/
	/*WORK EXPERIENCE RELATED*/
    public function addWorkExp(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addworkexperience');

		Session::put('menid',68);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=68 and ispermitted=1 and userid=".$userId.") as actions"))
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
		Session::put('form_token', $token);
		
        return view('admin/master/workexperience_add',compact('token'));
    }
    public function storeWorkExp(Request $request,$recordid)
	{        
        $rules = [
            'workexperience'=> 'required|max:30|regex:/^[a-zA-Z0-9\s.\[\]\(\)\+\-_]+$/',
        ];

        $messages = [
            'workexperience.required' 	=> 'EXPERIENCE IS REQUIRED',
            'workexperience.max' 		=> 'MAXIMUM LENGTH IS 30',
			'workexperience.regex' 		=> 'SPECIAL CHARACTERS NOT ALLOWED',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO work_experience(workexperience,createdby,creationdate) VALUES (?,?,?)',[$validatedData['workexperience'],$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update work_experience set workexperience=?,updated_by=?,updated_on=? where experienceid=?',[$validatedData['workexperience'],session('userId'),now(),$recordid]);
				return redirect('/master/add/workexperience')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editWorkExp($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('work_experience')->where('experienceid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/workexperience_edit',compact('data','token'));
    }
	
    public function getWorkExpData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('work_experience as a')
				->select('a.experienceid','a.workexperience','a.createdby','a.creationdate')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.workexperience','like','%'.$pagesearch.'%');					             
                })
                ->orderBy('a.experienceid')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/workexperienceTable', ['data' => $data]);

    }
    public function deleteWorkExp($recordid)
	{
		$res = true;//DB::delete('DELETE FROM work_experience WHERE experienceid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*WORK EXPERIENCE RELATED CLOSED*/
	/*ITEM RELATED STARTED*/
    public function addItem(Request $request)
	{        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','additem');

		Session::put('menid',61);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action		=	DB::table('menu_permission')
								->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=61 and ispermitted=1 and userid=".$userId.") as actions"))
								->groupby('menuid')
								->first();

			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
        return view('admin/master/item_add');
    }
    public function storeItem(Request $request,$recordid)
	{        
        $rules = [
            'itemname' 	=> 'required|max:200|regex:/^[a-zA-Z0-9\s.]+$/',
			'price' 	=> 'required|numeric',
        ];

        $messages = [
            'itemname.required' => 'ITEM NAME IS REQUIRED',
			'itemname.max' 		=> 'MAXIMUM 200 CHARACTERS ALLOWED',
			'itemname.regex' 	=> 'SPECIAL CHARACTER NOT ALLOWED',
			'price.required' 	=> 'PRICE IS REQUIRED',
			'price.numeric' 	=> 'INVALID PRICE VALUE',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO item_tbl(itemname,price,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)',[$validatedData['itemname'],$validatedData['price'],$userId,$userName,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update item_tbl set itemname=?,price=? where itemid=?',[$validatedData['itemname'],$validatedData['price'],$recordid]);

				return redirect('/master/add/item')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function getItemData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('item_tbl')
                ->orderBy('itemname')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('itemname','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/itemTable', ['data' => $data]);

    }
    public function editItem($recordid)
	{
		$data = DB::table('item_tbl')->where('itemid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/item_edit',compact('data'));
    }
    public function deleteItem($recordid)
	{
		$res = DB::delete('DELETE FROM item_tbl WHERE itemid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/*ITEM RELATED CLOSED*/
	/* SLOT RELATED */
    public function addSlot(Request $request)
	{        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addslot');

		Session::put('menid',57);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=57 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
        return view('admin/master/slot_add');
    }
    public function storeSlot(Request $request,$recordid)
	{        
        $rules = [
            'slot' 			=> ['required', 'date_format:H:i'],
			'percentage' 	=> 'required|numeric',
        ];

        $messages = [
            'slot.required' 		=> 'SLOT TIME IS REQUIRED',
			'slot.date_format' 		=> 'IT MUST BE A TIME',
			'percentage.required' 	=> 'IT IS REQUIRED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$slot		=	(String) $request->input('slot');
		$percentage	=	doubleval($request->input('percentage'));
		$isactive	=	intval($request->input('isactive'));

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO slot_tbl(slot,percentage,isactive,createdby,creationdate) VALUES (?,?,?,?,?)',[$slot,$percentage,$isactive,$userId,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update slot_tbl set slot=?,percentage=?,isactive=? where slotid=?',[$slot,$percentage,$isactive,$recordid]);

				return redirect('/master/add/slot')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function getSlotData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('slot_tbl')
                ->orderBy('slot')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('slot','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/slotTable', ['data' => $data]);

    }
    public function editSlot($recordid)
	{
		$data = DB::table('slot_tbl')->where('slotid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/slot_edit',compact('data'));
    }
    public function deleteSlot($recordid)
	{
		$res = DB::delete('DELETE FROM slot_tbl WHERE slotid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/* SLOT RELATED CLOSED */
	
	/* PAYOUT STRUCTURE RELATED */
    public function addPayoutStructure(Request $request){        
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','payoutstructure');

		Session::put('menid',54);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=54 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
		$tiers		=	DB::table('tier_tbl')->orderby('tiername')->get();
		$category	=	DB::table('category_tbl')->where('parentcategoryid','=',0)->orderby('displayorder')->get();
		$objectives	=	DB::table('payoutobjective_tbl')->orderby('objective')->get();
		$payouttype	=	DB::table('payouttype_tbl')->orderby('payouttype')->get();
		
        return view('admin/master/payoutstructure_add',compact('tiers','category','objectives','payouttype'));
    }
    public function getObjectiveList(Request $request)
	{
        $payouttypeid = intval($request->input('payouttypeid'));
        $objectivelist =DB::table('payoutobjective_tbl')
                ->select('objectiveid as value','objective as label')
                ->where('payouttypeid','=',$payouttypeid)
                ->orderby('objective')
                ->get();        
        return response()->json($objectivelist);
    }
    public function getPayoutStructureList(Request $request)
	{
		$categoryid =	intval($request->input('categoryid'));
		$objectiveid=	intval($request->input('objectiveid'));
		$tierid		=	intval($request->input('tierid'));

        $data = DB::table('payout_structure as a')
						->select('a.*','b.category','c.objective')
						->leftjoin('category_tbl as b','b.categoryid','=','a.subcategoryid')
						->leftjoin('payoutobjective_tbl as c','c.objectiveid','=','a.objectiveid')
						->where('a.tierid','=',$tierid)
						->where('a.categoryid','=',$categoryid)
						->where('a.objectiveid','=',$objectiveid)
						->orderBy('c.objective')
						->get();
		
		return view('admin/ajaxpages/payoutstructureList', ['data' => $data]);

    }
    public function storePayoutStructure(Request $request)
	{        
		$tierid			=	intval($request->input('tierid'));
		$categoryid		=	intval($request->input('categoryid'));
		$payouttypeid	=	intval($request->input('payouttypeid'));
		$objectiveid	=	intval($request->input('objectiveid'));
		$subcategoryid	=	intval($request->input('subcategoryid'));
		$cramount		=	intval($request->input('cramount'));
		$dramount		=	intval($request->input('dramount'));
		$userId     	=	$request->session()->get('userId');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			DB::insert('INSERT INTO payout_structure(tierid,categoryid,subcategoryid,payouttypeid,objectiveid,cramount,dramount,createdby,creationdate) VALUES (?,?,?,?,?,?,?,?,?)',[$tierid,$categoryid,$subcategoryid,$payouttypeid,$objectiveid,$cramount,$dramount,$userId,$creationdate]);

			return response()->json(['success'=>'']);
		}
		catch(QueryException $e)
		{
			return response()->json(['error'=>'DUPLICATE ENTRY']);
		}			
    }
    public function removePayoutStructure(Request $request)
	{        
		$tierid			=	intval($request->input('tierid'));
		$categoryid		=	intval($request->input('categoryid'));
		$subcategoryid	=	intval($request->input('subcategoryid'));
		$payouttypeid	=	intval($request->input('payouttypeid'));
		$objectiveid	=	intval($request->input('objectiveid'));

		
		try
		{
			DB::delete('delete from payout_structure where tierid=? and categoryid=? and subcategoryid=? and payouttypeid=? and objectiveid=?',[$tierid,$categoryid,$subcategoryid,$payouttypeid,$objectiveid]);

			return response()->json(['success'=>'']);
		}
		catch(QueryException $e)
		{
			return response()->json(['error'=>'DUPLICATE ENTRY']);
		}			
    }
	
	/* PAYOUT STRUCTURE RELATED CLOSED */
	/* PAYOUT OBJECTIVE */
    public function addObjective(Request $request)
	{        
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','objective');

		Session::put('menid',56);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=56 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		$payouttype	=	DB::table('payouttype_tbl')->orderby('payouttype')->get();
        return view('admin/master/objective_add',compact('payouttype'));
    }
    public function storeObjective(Request $request,$recordid)
	{        
        $rules = [
            'objective' 	=> 'required|max:200',
			'payouttypeid' 	=> 'required',
        ];

        $messages = [
            'objective.required'	=> 'PAYOUT TYPE IS REQUIRED',
			'objective.max' 		=> 'MAXIMUM 100 CHARACTERS ALLOWED',
			'payouttypeid.required'	=> 'PAYOUT TYPE IS REQUIRED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$objective		=	strtoupper($request->input('objective'));
		$payouttypeid	=	intval($request->input('payouttypeid'));

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO payoutobjective_tbl(payouttypeid,objective,createdby,creationdate) VALUES (?,?,?,?)',[$payouttypeid,$objective,$userId,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update payoutobjective_tbl set payouttypeid=?,objective=? where objectiveid=?',[$payouttypeid,$objective,$recordid]);

				return redirect('/master/add/objective')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function getObjectiveData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('payoutobjective_tbl')
                ->orderBy('objective')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('objective','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/objectiveTable', ['data' => $data]);

    }
    public function editObjective($recordid)
	{
		$data = DB::table('payoutobjective_tbl')->where('objectiveid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/objective_edit',compact('data'));
    }
    public function deleteObjective($recordid)
	{
		$res = DB::delete('DELETE FROM payoutobjective_tbl WHERE objectiveid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/* PAYOUT OBJECTIVE CLOSED */
	/* PAYOUT TYPE RELATED */
    public function addPayoutType(Request $request)
	{        
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','payouttype');

		Session::put('menid',55);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=55 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
        return view('admin/master/payouttype_add');
    }
    public function storePayoutType(Request $request,$recordid)
	{        
        $rules = [
            'payouttype' 	=> 'required|max:50',
        ];

        $messages = [
            'payouttype.required' 	=> 'PAYOUT TYPE IS REQUIRED',
			'payouttype.max' 		=> 'MAXIMUM 50 CHARACTERS ALLOWED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$payouttype	=	strtoupper($request->input('payouttype'));

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO payouttype_tbl(payouttype,createdby,creationdate) VALUES (?,?,?)',[$payouttype,$userId,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update payouttype_tbl set payouttype=? where payouttypeid=?',[$payouttype,$recordid]);

				return redirect('/master/add/payouttype')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function getPayoutTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('payouttype_tbl')
                ->orderBy('payouttype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('payouttype','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/payouttypeTable', ['data' => $data]);

    }
    public function editPayoutType($recordid)
	{
		$data = DB::table('payouttype_tbl')->where('payouttypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/payouttype_edit',compact('data'));
    }
    public function deletePayoutType($recordid)
	{
		/*
		$exists 	= DB::table('city_tbl')->where('tierid','=',Crypt::decrypt($recordid))->exists();
		if(!$exists)
		{
			$res = DB::delete('DELETE FROM tier_tbl WHERE tierid=?', [Crypt::decrypt($recordid)]);

			if($res)
			{
				return response()->json(['success' => true,'fail'=>'']);
			}
			else
			{
				return back()->with('fail',__('messages.notfound'));
			}
		}
		else
		{
			return response()->json(['fail'=>'THIS TIER RECORD IS ASSOCIATED WITH SOME CITY. IT CAN NOT BE DELETED.']);
		}
		*/

    }
	
	/* PAYOUT TYPE RELATED CLOSED */

	/* TAX RELATED START */
    public function addTax(Request $request){        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','taxes');

		Session::put('menid',29);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=29 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/tax_add');
    }
	
    public function storeTax(Request $request,$recordid){        
        
        $rules = [
			'taxname' 	=> 'required|max:30|regex:/^[a-zA-Z0-9\s.]+$/',
			'taxtype' 	=> 'required|regex:/^[a-zA-Z0-9\s.]+$/',
			'taxrate' 	=> ($request->input('taxtype') == 'SINGLE') ? 'required' : 'nullable',
			'taxrates' 	=> ($request->input('taxtype') == 'MULTIPLE') ? 'array|min:2' : 'nullable',
        ];
        $messages = [
            'taxname.required' 	=> 'TAX NAME IS REQUIRED',
			'taxname.max' 		=> 'MAXIMUM LENGTH REACHED',
			'taxname.regex' 	=> 'SPECIAL CHARACTER NOT ALLOWED',
			'taxtype.required' 	=> 'TAX TYPE IS REQUIRED',
			'taxtype.regex' 	=> 'SPECIAL CHARACTERS NOT ALLOWED',
			'taxrate.required' 	=> 'TAX RATE IS REQUIRED',
			'taxrate.required' 	=> 'TAX RATE IS REQUIRED',
			'taxrates.min' 		=> 'MINIMUM 2 TAXES REQUIRED FOR TAX TYPE MULTIPLE',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$tempname  	= strtoupper($request->input('tempname'));
		$taxname  	= strtoupper($request->input('taxname'));
		$taxtype  	= strtoupper($request->input('taxtype'));
		$taxrate  	= doubleval($request->input('taxrate'));
		$taxrates  	= $request->input('taxrates');
		$taxnames  	= $request->input('taxnames');
		if($taxtype=='MULTIPLE')
		{
			$flag=0;
			foreach($taxrates as $key=>$records)
			{
				if($taxrates[$key]=='')
				{
					$flag++;
				}
			}
			if($flag>0)
			{
				return back()->with('taxrequired','FOR MULTIPLE TAX TYPE EACH VALUE OF TAX NAME AND TAX RATE IS MANDATORY')->withInput();
			}
		}

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into tax_tbl(taxname,taxtype,taxrate,createdby,createdbyname,creationdate,tempname) values (?,?,?,?,?,?,?)',[$taxname,$taxtype,$taxrate,$userId,$userName,$creationdate,$taxname]);
				if($taxtype=='MULTIPLE')
				{
					$id = DB::getPdo()->lastInsertId();
					$totalrate	=	0;
					$displayname=	"";
					foreach($taxnames as $key=>$records)
					{
						DB::insert('insert into tax_multiple(taxid,taxname,taxrate,createdby,createdbyname,creationdate) values(?,?,?,?,?,?)',[$id,$taxnames[$key],$taxrates[$key],$userId,$userName,$creationdate]);
						$totalrate	=	$totalrate+$taxrates[$key];
						$displayname.= $taxnames[$key]." (".$taxrates[$key]."%)<br>";
					}
					$taxname	=	$taxname." (".$totalrate."%)";
					DB::update('update tax_tbl set taxname=?,displayname=?,taxrate=? where taxid=?',[$taxname,$displayname,$totalrate,$id]);
				}
				else
				{
					$id = DB::getPdo()->lastInsertId();
					$taxname	=	$taxname." (".$taxrate."%)";
					DB::update('update tax_tbl set taxname=? where taxid=?',[$taxname,$id]);
				}
				return back()->with('success','TAX NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'TAX NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('tax_tbl')->where('taxid',$recordid)->first();
			$updateData = [];
			if(!empty($taxname))
			{
				$updateData['taxname'] = $taxname;
			}
			if(!empty($taxtype))
			{
				$updateData['taxtype'] = $taxtype;
			}
			if(!empty($taxrate))
			{
				$updateData['taxrate'] = $taxrate;
			}
			try
			{
				$res = DB::table('tax_tbl')->where('taxid', $recordid)->update($updateData);
				if($taxtype=='MULTIPLE')
				{
					DB::delete('delete from tax_multiple where taxid=?',[$recordid]);
					$totalrate	=	0;
					$displayname=	"";
					foreach($taxnames as $key=>$records)
					{
						DB::insert('insert into tax_multiple(taxid,taxname,taxrate,createdby,createdbyname,creationdate) values(?,?,?,?,?,?)',[$recordid,$taxnames[$key],$taxrates[$key],$userId,$userName,$creationdate]);
						$totalrate	=	$totalrate+$taxrates[$key];
						$displayname.= $taxnames[$key]." (".$taxrates[$key]."%)<br>";
					}
					$taxname	=	$data->tempname." (".$totalrate."%)";
					DB::update('update tax_tbl set taxname=?,displayname=?,taxrate=? where taxid=?',[$taxname,$displayname,$totalrate,$recordid]);
				}
				
				return redirect('master/add/tax')->with('success','TAX NAME UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'TAX NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getTaxData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('tax_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('taxname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/taxTable', compact('data'))->render();
    }
    public function editTax($recordid)
	{
        $data 	= DB::table('tax_tbl')->where('taxid','=',Crypt::decrypt($recordid))->first();
		$rates	= DB::table('tax_multiple')->where('taxid','=',Crypt::decrypt($recordid))->get();
        return view('admin/master/tax_edit',compact('data','rates'));
    }
    public function deleteTax($recordid)
	{
		$exists 	= DB::table('service_tbl')->where('taxid','=',Crypt::decrypt($recordid))->exists();
		if(!$exists)
		{
			$res = DB::delete('delete from tax_tbl WHERE taxid=?',[Crypt::decrypt($recordid)]);
			if($res)
			{
				DB::delete('delete from tax_multiple WHERE taxid=?',[Crypt::decrypt($recordid)]);
				return response()->json(['success' => true,'fail'=>'']);
			}
			else
			{
				return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
			}
		}
		else
		{
			return response()->json(['fail'=>'THIS TAX RECORD IS ASSOCIATED WITH SOME SERVICES. IT CAN NOT BE DELETED.']);
		}
    }
	/* TAX RELATED CLOSED */

	/* CITY SLIDER RELATED START */
    public function addCitySlider(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','cityslider');
		$city	=	DB::table('city_tbl')->where('operatingstatus','=',1)->orderby('cityname')->get();
		Session::put('menid',25);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=25 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$displayorder = $this->displayOrderService->getDisplayOrder('cityslider_tbl')+1;
        return view('admin/master/cityslider_add',compact('city','displayorder'));
    }
    public function storeCitySlider(Request $request,$recordid)
	{        
        $rules = [
            'cityid' 		=> 'required',
			'sliderimage' 	=> 'required|array|min:1',
			'sliderimage.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:500',
			'sliderimage' 	=> ($recordid == 0) ? 'required|array|min:1' : 'nullable',
			'sliderimage.*' => ($recordid == 0) ? 'image|mimes:jpeg,png,jpg,gif,svg|max:500' : 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:500',
        ];

        $messages = [
            'cityid.required' => __('validation.thisis.required'),
			'sliderimage.min' => __('validation.thisismin1.required'),
			'sliderimage.required' => __('validation.thisismin1.required'),
			'sliderimage.*.max' => __('validation.thisis500kb.max'),
			'sliderimage.*.image' => __('validation.thisisimage.required'),
			'sliderimage.*.mimes' => __('validation.thisismimes.required'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$cityid			=	intval($request->input('cityid'));
		$serviceid		=	intval($request->input('serviceid'));
		$isactive		=	intval($request->input('isactive'));


		if($recordid==0)
		{
			try
			{
				$displayorder = intval($this->displayOrderService->getLastDisplayOrder($cityid,'cityslider_tbl','displayorder','cityid'));
				if ($request->hasFile('sliderimage')) 
				{
					$i=$displayorder;
					foreach($request->file('sliderimage') as $file) 
					{
						$i++;
						$path = $file->store('uploads/citybanners', 'public');
						
						DB::insert('INSERT INTO cityslider_tbl(cityid,serviceid,sliderimage,displayorder,isactive,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?,?,?)',[$cityid,$serviceid,$path,$i,$isactive,$userId,$userName,$creationdate]);
					}
				}
				
				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$sliderimage    = (String) $request->file('sliderimage');	
				$cont = DB::table('cityslider_tbl')->where('citysliderid',$recordid)->first();
				if($sliderimage!='')
				{
					if ($cont->sliderimage!='') {
						Storage::disk('public')->delete($cont->sliderimage);
					}
					$sliderimage= $request->file('sliderimage')->store('uploads/citybanners', 'public');
				}

				$updateData = [];
				if(!empty($cityid)) {
					$updateData['cityid'] = $cityid;
				}
				if(!empty($serviceid)) {
					$updateData['serviceid'] = $serviceid;
				}
				$updateData['isactive'] = $isactive;
				if(!empty($sliderimage)) {
					$updateData['sliderimage'] = $sliderimage;
				}
				$res = DB::table('cityslider_tbl')->where('citysliderid', $recordid)->update($updateData);

				return redirect('/master/add/cityslider')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function editCitySlider($recordid)
	{
        $city		=	DB::table('city_tbl')->orderby('cityname')->get();
		$service	=	DB::table('service_tbl')->orderby('servicename')->get();
		
		$data 		= 	DB::table('cityslider_tbl')->where('citysliderid','=',Crypt::decrypt($recordid))->first();

        return view('admin/master/cityslider_edit',compact('data','city','service'));
    }
    public function getCitySliderData(Request $request)
	{
		$cityid 	=	intval($request->input('cityid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('cityslider_tbl as a')
				->select('a.*','b.cityname','c.servicetitle')
				->leftjoin('city_tbl as b','b.cityid','=','a.cityid')
				->leftjoin('service_tbl as c','c.serviceid','=','a.serviceid')
                ->when($cityid!=0,function($query) use ($cityid){
                    return $query->where('a.cityid','=',$cityid);
                })
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('b.cityname','like','%'.$pagesearch.'%')
								 ->where('c.servicetitle','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/citysliderTable', ['data' => $data]);

    }
    public function setActiveInactiveCitySlider(Request $request)
	{
		$citysliderid 	=	$request->input('citysliderid');
		$citysliderid	=	Crypt::decrypt($citysliderid);
		$currentStatus = DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->value('isactive');
		$newStatus = $currentStatus == 1 ? 0 : 1;
		
		$updated = DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->update(['isactive' => $newStatus]);
		return response()->json(['success' => true,'fail'=>'']);

    }
    public function setDisplayCitySlider(Request $request)
	{
		$citysliderid 	=	$request->input('citysliderid');
		$citysliderid	=	Crypt::decrypt($citysliderid);
		$val 			=	$request->input('val');

		DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->update(['displayorder' => $val]);
		return response()->json(['success' => true,'fail'=>'']);
    }

    public function deleteCitySlider($recordid)
	{
		$content = DB::table('cityslider_tbl')->where('citysliderid','=',Crypt::decrypt($recordid))->first();
		if($content->sliderimage!='')
		{
			Storage::disk('public')->delete($content->sliderimage);
		}
		
        $res = DB::delete('DELETE FROM cityslider_tbl WHERE citysliderid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail',__('messages.notfound'));
        }

    }
	/* CITY SLIDER RELATED CLOSED */

	
	/* CITY RELATED START */
    public function addCity(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addcity');
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
		Session::put('menid',7);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=7 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/city_add',compact('state','token'));
    }
    public function storeCity(Request $request,$recordid)
	{        
        $rules = [
            'stateid' 	=> 'required|numeric',
			'cityname' 	=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'aliasname' => 'required|max:10|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'stateid.required' 		=> __('validation.thisis.required'),
			'stateid.numeric' 		=> 'INVALID STATE NAME',
            'cityname.required' 	=> __('validation.thisis.required'),
            'cityname.max' 			=> __('validation.thisis.max'),
			'cityname.regex' 		=> 'INVALID CITY NAME',
            'aliasname.required'	=> __('validation.thisis.required'),
            'aliasname.max' 		=> __('validation.thisis.max'),
			'aliasname.regex' 		=> 'SPECIAL CHARACTERS NOT ALLOWED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$stateid		=	intval($request->input('stateid'));
		$cityname		=	strtoupper($request->input('cityname'));
		$aliasname		=	strtoupper($request->input('aliasname'));
		

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO city_tbl(stateid,cityname,citycode,createdby,creationdate) VALUES (?,?,?,?,?)',[$stateid,$cityname,$aliasname,$userId,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				DB::update('update city_tbl set stateid=?,cityname=?,citycode=? where cityid=?',[$stateid,$cityname,$aliasname,$recordid]);

				return redirect('/master/add/city')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function editCity($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        $state	=	DB::table('state_tbl')->orderby('statename')->get();
		$data = DB::table('city_tbl')->where('cityid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/city_edit',compact('data','state','token'));
    }
    public function getCityData(Request $request)
	{
		$stateid 	=	intval($request->input('stateid'));
		$operatingstatus	=	$request->input('operatingstatus');
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('city_tbl as a')
				->select('a.cityid','a.cityname','a.citycode','a.creationdate','b.statename')
				->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.cityname','like','%'.$pagesearch.'%')
								 ->orwhere('a.citycode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.cityname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/cityTable', ['data' => $data]);

    }
    public function setDefaultCity(Request $request)
	{
		$cityid 		=	$request->input('cityid');
		$cityid			=	Crypt::decrypt($cityid);
		
		$isoperating	=	DB::table('city_tbl')->select('operatingstatus')->where('cityid','=',$cityid)->first();
		if($isoperating->operatingstatus==0)
		{
			return response()->json(['error' => true,'fail'=>__('messages.notoperatingcity')]);
		}
		$isslider		=	DB::table('cityslider_tbl')->where('cityid','=',$cityid)->get();
		
		if(count($isslider)>0)
		{
			$currentStatus 	= 	DB::table('city_tbl')->where('cityid', $cityid)->value('isdefault');
			$newStatus 		= 	$currentStatus == 1 ? 0 : 1;

			DB::table('city_tbl')->update(['isdefault' => 0]);
			DB::table('city_tbl')->where('cityid', $cityid)->update(['isdefault' => $newStatus]);

			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return response()->json(['error' => true,'fail'=>__('messages.cityslidernot')]);
		}

    }
    public function setOperatingCityStatus(Request $request)
	{
		$cityid 		=	$request->input('cityid');
		$cityid			=	Crypt::decrypt($cityid);
		
		$checktier	=	DB::table('city_tbl')->where('cityid','=',$cityid)->first();
		if($checktier->tierid==0)
		{
			return response()->json(['error' => true,'fail'=>__('messages.tiernotset')]);
		}
		
		$isslider		=	DB::table('cityslider_tbl')->where('cityid','=',$cityid)->get();		
		if(count($isslider)>0)
		{
			$currentStatus 	= 	DB::table('city_tbl')->where('cityid', $cityid)->value('operatingstatus');
			$newStatus 		= 	$currentStatus == 1 ? 0 : 1;

			DB::table('city_tbl')->where('cityid', $cityid)->update(['operatingstatus' => $newStatus]);

			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return response()->json(['error' => true,'fail'=>__('messages.cityslidernotforoperating')]);
		}

    }
	
    public function deleteCity($recordid)
	{
		$res = true;//DB::delete('DELETE FROM city_tbl WHERE cityid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	/* CITY RELATED CLOSED */

	/* TIER RELATED START */
    public function addTier(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','tier');
		Session::put('menid',22);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=22 and ispermitted=1 and userid=".$userId.") as actions"))
						->groupby('menuid')
						->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);			
		}
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/tier_add',compact('token'));
    }
    public function storeTier(Request $request,$recordid)
	{        
        $rules = [
            'tiername' 	=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'discount'	=> 'required|numeric',
			'commission'=> 'required|numeric',
        ];

        $messages = [
            'tiername.required' 	=> __('validation.thisis.required'),
			'tiername.max' 			=> __('validation.thisis50.max'),
			'tiername.regex' 		=> 'SPECIAL CHARACTERS NOT ALLOWED',
            'discount.required'		=> __('validation.thisis.required'),
			'discount.numeric'		=> __('validation.thisis.numeric'),
            'commission.required'	=> __('validation.thisis.required'),
			'commission.numeric'	=> __('validation.thisis.numeric'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		
		$userId     	=	$request->session()->get('userId');
		$userName   	=	$request->session()->get('userName');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$tiername	=	strtoupper($request->input('tiername'));
		$discount	=	doubleval($request->input('discount'));
		$commission	=	doubleval($request->input('commission'));


		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO tier_tbl(tiername,discount,commission,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)',[$tiername,$discount,$commission,$userId,$userName,$creationdate]);

				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update tier_tbl set tiername=?,discount=?,commission=? where tierid=?',[$tiername,$discount,$commission,$recordid]);

				return redirect('/master/add/tier')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function editTier($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
		$data = DB::table('tier_tbl')->where('tierid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/tier_edit',compact('data','token'));
    }
    public function getTierData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('tier_tbl')
                ->orderBy('tiername')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('tiername','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/tierTable', ['data' => $data]);

    }
    public function deleteTier($recordid)
	{
		$exists 	= DB::table('city_tbl')->where('tierid','=',Crypt::decrypt($recordid))->exists();
		if(!$exists)
		{
			$res = DB::delete('DELETE FROM tier_tbl WHERE tierid=?', [Crypt::decrypt($recordid)]);

			if($res)
			{
				return response()->json(['success' => true,'fail'=>'']);
			}
			else
			{
				return back()->with('fail',__('messages.notfound'));
			}
		}
		else
		{
			return response()->json(['fail'=>'THIS TIER RECORD IS ASSOCIATED WITH SOME CITY. IT CAN NOT BE DELETED.']);
		}

    }
	/* TIER RELATED CLOSED */


	/* STATE RELATED START */
    public function addState(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addstate');
		Session::put('menid',6);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=6 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/state_add',compact('token'));
    }
    public function editState($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

        $data = DB::table('state_tbl')->where('stateid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/state_edit',compact('data','token'));
    }
    public function storeState(Request $request,$recordid)
	{        
        $rules = [
            'statename' => 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'aliasname' => 'required|max:10|regex:/^[a-zA-Z0-9\s.]+$/',
        ];

        $messages = [
            'statename.required' 	=> 	__('validation.thisis.required'),
            'statename.max' 		=> 	__('validation.thisis50.max'),
			'statename.regex' 		=> 	'Invalid state name',
            'aliasname.required' 	=> 	__('validation.thisis.required'),
            'aliasname.max' 		=>	__('validation.thisis10.max'),
			'aliasname.regex' 		=> 	'Invalid alias name',
        ];

        $validatedData = $request->validate($rules, $messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$statename	=	ucwords($request->input('statename'));
		$aliasname	=	ucwords($request->input('aliasname'));

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO state_tbl(statename,aliasname,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$statename,$aliasname,$userId,$userName,$creationdate]);
				return back()->with('success',__('messages.stored'));				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				DB::update('update state_tbl set statename=?,aliasname=? where stateid=?',[$statename,$aliasname,$recordid]);

				return redirect('/master/add/state')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.updated'))->withInput();
			}			
		}
    }
    public function getStateData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page', 1);
		//$perPage 		= 	$request->input('perPage', 10);
        $data = DB::table('state_tbl')
                ->orderBy('statename')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('statename','like','%'.$pagesearch.'%')
								 ->orwhere('aliasname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/stateTable', ['data' => $data]);
    }
    public function deleteState($recordid)
	{
		$res = true;//DB::delete('DELETE FROM state_tbl WHERE stateid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	/* STATE RELATED CLOSED */

	
	/* PAYMENT MODE RELATED START */
    public function addPaymentMode(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','paymentmode');
		Session::put('menid',76);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=76 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/paymentmode_add');
    }
    public function storePaymentMode(Request $request,$recordid)
	{        
        $rules = [
            'paymentmode' => 'required|max:50',
        ];

        $messages = [
            'paymentmode.required' => 'PAYMENT MODE IS REQUIRED',
            'paymentmode.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$paymentmode		=	strtoupper($request->input('paymentmode'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO paymentmode_tbl(paymentmode,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$paymentmode,$userId,$userName,$creationdate]);

				return back()->with('success','PAYMENT MODE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PAYMENT MODE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update paymentmode_tbl set paymentmode=? where modeid=?',[$paymentmode,$recordid]);

				return redirect('/master/add/paymentmode')->with('success','PAYMENT MODE STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PAYMENT MODE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editPaymentMode($recordid)
	{
        $data = DB::table('paymentmode_tbl')->where('modeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/paymentmode_edit',compact('data'));
    }
    public function getPaymentModeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('paymentmode_tbl')
                ->orderBy('paymentmode')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('paymentmode','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/paymentmodeTable', ['data' => $data]);
    }
    public function deletePaymentMode($recordid)
	{
        $res = DB::delete('DELETE FROM paymentmode_tbl WHERE modeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* PAYMENT MODE STATUS RELATED CLOSED */


	/* OWNER STATUS RELATED START */
    public function addOwnerStatus(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','ownerstatus');
		Session::put('menid',75);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=75 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/ownerstatus_add');
    }
    public function storeOwnerStatus(Request $request,$recordid)
	{        
        $rules = [
            'ownerstatus' => 'required|max:50',
        ];

        $messages = [
            'ownerstatus.required' => 'OWNER STATUS IS REQUIRED',
            'ownerstatus.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$ownerstatus		=	strtoupper($request->input('ownerstatus'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO ownerstatus_tbl(ownerstatus,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$ownerstatus,$userId,$userName,$creationdate]);

				return back()->with('success','OWNER STATUS TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','OWNER STATUS COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update ownerstatus_tbl set ownerstatus=? where ownerstatusid=?',[$ownerstatus,$recordid]);

				return redirect('/master/add/ownerstatus')->with('success','OWNER STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','OWNER STATUS COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editOwnerStatus($recordid)
	{
        $data = DB::table('ownerstatus_tbl')->where('ownerstatusid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/ownerstatus_edit',compact('data'));
    }
    public function getOwnerStatusData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('ownerstatus_tbl')
                ->orderBy('ownerstatus')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('ownerstatus','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/ownerstatusTable', ['data' => $data]);
    }
    public function deleteOwnerStatus($recordid)
	{
        $res = DB::delete('DELETE FROM ownerstatus_tbl WHERE ownerstatusid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* OWNER STATUS RELATED CLOSED */

	/* CALL STATUS RELATED START */
    public function addCallStatus(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','callstatus');
		Session::put('menid',74);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=74 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/callstatus_add');
    }
    public function storeCallStatus(Request $request,$recordid)
	{        
        $rules = [
            'callstatus' => 'required|max:50',
        ];

        $messages = [
            'callstatus.required' => 'CALL STATUS IS REQUIRED',
            'callstatus.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$callstatus		=	strtoupper($request->input('callstatus'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO callstatus_tbl(callstatus,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$callstatus,$userId,$userName,$creationdate]);

				return back()->with('success','CALL STATUS TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','CALL STATUS COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update callstatus_tbl set callstatus=? where callstatusid=?',[$callstatus,$recordid]);

				return redirect('/master/add/callstatus')->with('success','CALL STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','CALL STATUS COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editCallStatus($recordid)
	{
        $data = DB::table('callstatus_tbl')->where('callstatusid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/callstatus_edit',compact('data'));
    }
    public function getCallStatusData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('callstatus_tbl')
                ->orderBy('callstatus')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('callstatus','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/callstatusTable', ['data' => $data]);
    }
    public function deleteCallStatus($recordid)
	{
        $res = DB::delete('DELETE FROM callstatus_tbl WHERE callstatusid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* CALL STATUS RELATED CLOSED */

	
	/* LEAD STATUS RELATED START */
    public function addLeadStatus(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','leadstatus');
		Session::put('menid',73);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=73 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/leadstatus_add');
    }
    public function storeLeadStatus(Request $request,$recordid)
	{        
        $rules = [
            'leadstatus' => 'required|max:50',
        ];

        $messages = [
            'leadstatus.required' => 'LEAD STATUS IS REQUIRED',
            'leadstatus.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$leadstatus		=	strtoupper($request->input('leadstatus'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO leadstatus_tbl(leadstatus,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$leadstatus,$userId,$userName,$creationdate]);

				return back()->with('success','LEAD STATUS TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','LEAD STATUS COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update leadstatus_tbl set leadstatus=? where leadstatusid=?',[$leadstatus,$recordid]);

				return redirect('/master/add/leadstatus')->with('success','LEAD STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','LEAD STATUS COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editLeadStatus($recordid)
	{
        $data = DB::table('leadstatus_tbl')->where('leadstatusid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/leadstatus_edit',compact('data'));
    }
    public function getLeadStatusData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('leadstatus_tbl')
                ->orderBy('leadstatus')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('leadstatus','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/leadstatusTable', ['data' => $data]);
    }
    public function deleteLeadStatus($recordid)
	{
        $res = DB::delete('DELETE FROM leadstatus_tbl WHERE leadstatusid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* LEAD STATUS RELATED CLOSED */


	/* PRIORITY RELATED START */
    public function addPriority(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','priority');
		Session::put('menid',72);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=72 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/priority_add');
    }
    public function storePriority(Request $request,$recordid)
	{        
        $rules = [
            'priority' => 'required|max:50',
        ];

        $messages = [
            'priority.required' => 'PRIORITY IS REQUIRED',
            'priority.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$priority		=	strtoupper($request->input('priority'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO priority_tbl(priority,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$priority,$userId,$userName,$creationdate]);

				return back()->with('success','PRIORITY TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PRIORITY COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update priority_tbl set priority=? where priorityid=?',[$priority,$recordid]);

				return redirect('/master/add/priority')->with('success','PRIORITY UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PRIORITY COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editPriority($recordid)
	{
        $data = DB::table('priority_tbl')->where('priorityid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/priority_edit',compact('data'));
    }
    public function getPriorityData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('priority_tbl')
                ->orderBy('priority')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('priority','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/priorityTable', ['data' => $data]);
    }
    public function deletePriority($recordid)
	{
        $res = DB::delete('DELETE FROM priority_tbl WHERE priorityid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* PRIORITY RELATED CLOSED */


	/* QUOTA RELATED START */
    public function addQuota(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','quota');
		Session::put('menid',71);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=71 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/quota_add');
    }
    public function storeQuota(Request $request,$recordid)
	{        
        $rules = [
            'quota' => 'required|max:50',
        ];

        $messages = [
            'quota.required' => 'QUOTA IS REQUIRED',
            'quota.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$quota		=	strtoupper($request->input('quota'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO quota_tbl(quota,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$quota,$userId,$userName,$creationdate]);

				return back()->with('success','QUOTA TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','QUOTA COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update quota_tbl set quota=? where quotaid=?',[$quota,$recordid]);

				return redirect('/master/add/quota')->with('success','QUOTA UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','QUOTA COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editQuota($recordid)
	{
        $data = DB::table('quota_tbl')->where('quotaid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/quota_edit',compact('data'));
    }
    public function getQuotaData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('quota_tbl')
                ->orderBy('quota')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('quota','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/quotaTable', ['data' => $data]);
    }
    public function deleteQuota($recordid)
	{
        $res = DB::delete('DELETE FROM quota_tbl WHERE quotaid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* QUOTA RELATED CLOSED */

	/* STATUS COLOR RELATED START */
    public function addStatusColor(Request $request){        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','statuscolor');
		Session::put('menid',70);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=70 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/statuscolor_add');
    }
    public function storeStatusColor(Request $request,$recordid){        
        
        $rules = [
			'statuscolor' => 'required|max:30',
			'statusname' => 'required|max:50',
        ];

        $messages = [
            'statuscolor.required' => 'STATUS COLOR IS REQUIRED',
			'statuscolor.max' => 'MAXIMUM LENGTH REACHED',
            'statusname.required' => 'STATUS VALUE IS REQUIRED',
			'statusname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$statuscolor  	= strtoupper($request->input('statuscolor'));
		$statusname  	= strtoupper($request->input('statusname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into statuscolor_tbl (statusname,statuscolor,creationdate,createdby,createdbyname) values (?,?,?,?,?)',[$statusname,$statuscolor,$creationdate,$userId,$userName]);

				return back()->with('success','STATUS COLOR STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'STATUS COLOR IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('statuscolor_tbl')->where('statuscolorid',$recordid)->first();
			$updateData = [];
			if(!empty($statuscolor))
			{
				$updateData['statuscolor'] = $statuscolor;
			}
			if(!empty($statusname))
			{
				$updateData['statusname'] = $statusname;
			}
			try
			{
				$res = DB::table('statuscolor_tbl')->where('statuscolorid', $recordid)->update($updateData);
				return redirect('master/add/statuscolor')->with('success','STATUS COLOR UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'STATUS COLOR IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getStatusColorData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('statuscolor_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('statuscolor','like','%'.$pagesearch.'%')
								 ->orwhere('statusname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/statuscolorTable', compact('data'))->render();
    }
    public function editStatusColor($recordid)
	{
        $data = DB::table('statuscolor_tbl')->where('statuscolorid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/statuscolor_edit',compact('data'));
    }
    public function deleteStatusColor($recordid)
	{
		$res = DB::delete('delete from statuscolor_tbl WHERE statuscolorid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/* STATUS COLOR RELATED CLOSED */


	/* UNIT RELATED START */
    public function addUnit(Request $request){        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addunit');
		Session::put('menid',27);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=27 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/unit_add');
    }
    public function storeUnit(Request $request,$recordid){        
        
        $rules = [
			'unitname' => 'required|max:30',
        ];

        $messages = [
            'unitname.required' => 'UNIT NAME IS REQUIRED',
			'unitname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$unitname  	= strtoupper($request->input('unitname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into unit_tbl (unitname,creationdate,createdby,createdbyname) values (?,?,?,?)',[$unitname,$creationdate,$userId,$userName]);

				return back()->with('success','UNIT NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'UNIT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('unit_tbl')->where('unitid',$recordid)->first();
			$updateData = [];
			if(!empty($unitname))
			{
				$updateData['unitname'] = $unitname;
			}
			try
			{
				$res = DB::table('unit_tbl')->where('unitid', $recordid)->update($updateData);
				return redirect('master/add/unit')->with('success','UNIT NAME UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'UNIT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getUnitData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('unit_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('unitname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/unitTable', compact('data'))->render();
    }
    public function editUnit($recordid)
	{
        $data = DB::table('unit_tbl')->where('unitid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/unit_edit',compact('data'));
    }
    public function deleteUnit($recordid)
	{
		$res = DB::delete('delete from unit_tbl WHERE unitid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/* UNIT RELATED CLOSED */
	
	/* DEDUCTION TYPE RELATED START */
    public function addDeductionType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','deductiontype');
		Session::put('menid',68);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=68 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/deductiontype_add');
    }
    public function storeDeductionType(Request $request,$recordid)
	{        
        $rules = [
            'deductiontype' => 'required|max:50',
			'percentage' => 'required|numeric',
        ];

        $messages = [
            'deductiontype.required' => 'DEDUCTION TYPE IS REQUIRED',
            'deductiontype.max' => 'MAXIMUM LENGTH IS 50',
			'percentage.required' => 'IT IS REQUIRED',
			'percentage.numeric' => 'MUST BE NUMERIC',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$deductiontype		=	strtoupper($request->input('deductiontype'));
		$percentage		=	doubleval($request->input('percentage'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO deductiontype_tbl(deductiontype,percentage,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$deductiontype,$percentage,$userId,$userName,$creationdate]);

				return back()->with('success','DEDUCTION TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DEDUCTION TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update deductiontype_tbl set deductiontype=?,percentage=? where deductiontypeid=?',[$deductiontype,$percentage,$recordid]);

				return redirect('/master/add/deductiontype')->with('success','DEDUCTION TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DEDUCTION TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editDeductionType($recordid)
	{
        $data = DB::table('deductiontype_tbl')->where('deductiontypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/deductiontype_edit',compact('data'));
    }
    public function getDeductionTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('deductiontype_tbl')
                ->orderBy('deductiontype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('deductiontype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/deductiontypeTable', ['data' => $data]);
    }
    public function deleteDeductionType($recordid)
	{
        $res = DB::delete('DELETE FROM deductiontype_tbl WHERE deductiontypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* DEDUCTION TYPE RELATED CLOSED */
	
	/* ASSET TYPE RELATED START */
    public function addAssetType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','assettype');
		Session::put('menid',67);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=67 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		$displayorder = $this->displayOrderService->getDisplayOrder('assettype_tbl')+1;
        return view('admin/master/assettype_add',compact('displayorder'));
    }
    public function storeAssetType(Request $request,$recordid)
	{        
        $rules = [
            'assettype' => 'required|max:50',
			'displayorder' => 'required',
        ];

        $messages = [
            'assettype.required' => 'ASSET TYPE IS REQUIRED',
            'assettype.max' => 'MAXIMUM LENGTH IS 50',
			'displayorder.required' => 'IT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$assettype		=	strtoupper($request->input('assettype'));
		$displayorder	=	intval($request->input('displayorder'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO assettype_tbl(assettype,displayorder,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$assettype,$displayorder,$userId,$userName,$creationdate]);

				return back()->with('success','ASSET TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','ASSET TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update assettype_tbl set assettype=?,displayorder=? where assettypeid=?',[$assettype,$displayorder,$recordid]);

				return redirect('/master/add/assettype')->with('success','ASSET TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','ASSET TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editAssetType($recordid)
	{
        $data = DB::table('assettype_tbl')->where('assettypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/assettype_edit',compact('data'));
    }
    public function getAssetTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('assettype_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('assettype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/assettypeTable', ['data' => $data]);
    }
    public function deleteAssetType($recordid)
	{
        $res = DB::delete('DELETE FROM assettype_tbl WHERE assettypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* ASSET TYPE RELATED CLOSED */
	
	
	/* AMENITIES RELATED START */
    public function addAmenity(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','amenities');
		Session::put('menid',66);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=66 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/amenities_add');
    }
    public function storeAmenity(Request $request,$recordid)
	{        
        $rules = [
            'amenities' => 'required|max:50',
			'displayorder' => 'required',
        ];

        $messages = [
            'amenities.required' => 'AMINITIE IS REQUIRED',
            'amenities.max' => 'MAXIMUM LENGTH IS 50',
			'displayorder.required' => 'IT IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$amenities	=	strtoupper($request->input('amenities'));
		$displayorder	=	strtoupper($request->input('displayorder'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO amenities_tbl(amenities,displayorder,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$amenities,$displayorder,$userId,$userName,$creationdate]);

				return back()->with('success','AMENITY STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','AMENITY COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update amenities_tbl set amenities=?,displayorder=? where amenityid=?',[$amenities,$displayorder,$recordid]);

				return redirect('/master/add/amenities')->with('success','AMENITY UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','AMENITY COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editAmenity($recordid)
	{
        $data = DB::table('amenities_tbl')->where('amenityid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/amenities_edit',compact('data'));
    }
    public function getAmenityData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('amenities_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('amenities','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/amenitiesTable', ['data' => $data]);
    }
    public function deleteAmenity($recordid)
	{
        $res = DB::delete('DELETE FROM amenities_tbl WHERE amenityid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* AMENITIES RELATED CLOSED */

	
	/* PROJECT STATUS RELATED START */
    public function addProjectStatus(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','projectstatus');
		Session::put('menid',65);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=65 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/projectstatus_add');
    }
    public function storeProjectStatus(Request $request,$recordid)
	{        
        $rules = [
            'projectstatus' => 'required|max:50',
        ];

        $messages = [
            'projectstatus.required' => 'PROJECT STATUS NAME IS REQUIRED',
            'projectstatus.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$projectstatus	=	strtoupper($request->input('projectstatus'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO projectstatus_tbl(projectstatus,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$projectstatus,$userId,$userName,$creationdate]);

				return back()->with('success','PROJECT STATUS STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PROJET STATUS COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update projectstatus_tbl set projectstatus=? where projectstatusid=?',[$projectstatus,$recordid]);

				return redirect('/master/add/projectstatus')->with('success','PROJECT STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','PROJECT STATUS COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editProjectStatus($recordid)
	{
        $data = DB::table('projectstatus_tbl')->where('projectstatusid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/projectstatus_edit',compact('data'));
    }
    public function getProjectStatusData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('projectstatus_tbl')
                ->orderBy('projectstatus')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('projectstatus','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/projectstatusTable', ['data' => $data]);
    }
    public function deleteProjectStatus($recordid)
	{
        $res = DB::delete('DELETE FROM projectstatus_tbl WHERE projectstatusid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* PROJECT STATUS RELATED CLOSED */
	
	/* LEAD SOURCE RELATED START */
    public function addLeadSource(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','leadsource');
		Session::put('menid',64);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=64 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/leadsource_add');
    }
    public function storeLeadSource(Request $request,$recordid)
	{        
        $rules = [
            'leadsource' => 'required|max:50',
        ];

        $messages = [
            'leadsource.required' => 'LEAD SOURCE NAME IS REQUIRED',
            'leadsource.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$leadsource	=	strtoupper($request->input('leadsource'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO leadsource_tbl(leadsource,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$leadsource,$userId,$userName,$creationdate]);

				return back()->with('success','LEAD SOURCE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','LEAD SOURCE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update leadsource_tbl set leadsource=? where sourceid=?',[$leadsource,$recordid]);

				return redirect('/master/add/leadsource')->with('success','LEAD SOURCE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','LEAD SOURCE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editLeadSource($recordid)
	{
        $data = DB::table('leadsource_tbl')->where('sourceid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/leadsource_edit',compact('data'));
    }
    public function getLeadSourceData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('leadsource_tbl')
                ->orderBy('leadsource')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('leadsource','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/leadsourceTable', ['data' => $data]);
    }
    public function deleteLeadSource($recordid)
	{
        $res = DB::delete('DELETE FROM leadsource_tbl WHERE sourceid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* LEAD SOURCE RELATED CLOSED */


	/* ROWHOUSE TYPE RELATED START */
    public function addRowhouseType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','rowhousetype');
		Session::put('menid',63);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=63 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/rowhousetype_add');
    }
    public function storeRowhouseType(Request $request,$recordid)
	{        
        $rules = [
            'rowhousetype' => 'required|max:50',
        ];

        $messages = [
            'rowhousetype.required' => 'ROW HOUSE TYPE NAME IS REQUIRED',
            'rowhousetype.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$rowhousetype	=	strtoupper($request->input('rowhousetype'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO rowhousetype_tbl(rowhousetype,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$rowhousetype,$userId,$userName,$creationdate]);

				return back()->with('success','ROW HOUSE TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','ROW HOUSE TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update rowhousetype_tbl set rowhousetype=? where rowhousetypeid=?',[$rowhousetype,$recordid]);

				return redirect('/master/add/rowhousetype')->with('success','ROW HOUSE TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','ROW HOUSE TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editRowhouseType($recordid)
	{
        $data = DB::table('rowhousetype_tbl')->where('rowhousetypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/rowhousetype_edit',compact('data'));
    }
    public function getRowhouseTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('rowhousetype_tbl')
                ->orderBy('rowhousetype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('rowhousetype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/rowhousetypeTable', ['data' => $data]);
    }
    public function deleteRowhouseType($recordid)
	{
        $res = DB::delete('DELETE FROM rowhousetype_tbl WHERE rowhousetypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* ROWHOUSE TYPE RELATED CLOSED */
	
	
	/* FLAT TYPE RELATED START */
    public function addFlatType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','flattype');
		Session::put('menid',62);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=62 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/flattype_add');
    }
    public function storeFlatType(Request $request,$recordid)
	{        
        $rules = [
            'flattype' => 'required|max:50',
        ];

        $messages = [
            'flattype.required' => 'FLAT TYPE NAME IS REQUIRED',
            'flattype.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$flattype	=	strtoupper($request->input('flattype'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO flattype_tbl(flattype,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$flattype,$userId,$userName,$creationdate]);

				return back()->with('success','FLAT TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FLAT TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update flattype_tbl set flattype=? where flattypeid=?',[$flattype,$recordid]);

				return redirect('/master/add/flattype')->with('success','FLAT TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FLAT TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editFlatType($recordid)
	{
        $data = DB::table('flattype_tbl')->where('flattypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/flattype_edit',compact('data'));
    }
    public function getFlatTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('flattype_tbl')
                ->orderBy('flattype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('flattype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/flattypeTable', ['data' => $data]);
    }
    public function deleteFlatType($recordid)
	{
        $res = DB::delete('DELETE FROM flattype_tbl WHERE flattypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* FLAT TYPE RELATED CLOSED */

	/* FURNISHED TYPE RELATED START */
    public function addFurnishedType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','furnishedtype');
		Session::put('menid',76);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=76 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/furnishedtype_add');
    }
    public function storeFurnishedType(Request $request,$recordid)
	{        
        $rules = [
            'furnishedtype' => 'required|max:50',
        ];

        $messages = [
            'furnishedtype.required' => 'FURNISHED TYPE NAME IS REQUIRED',
            'furnishedtype.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$furnishedtype	=	strtoupper($request->input('furnishedtype'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO furnishedtype_tbl(furnishedtype,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$furnishedtype,$userId,$userName,$creationdate]);

				return back()->with('success','FURNISHED TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FURNISHED TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update furnishedtype_tbl set furnishedtype=? where furnishedtypeid=?',[$furnishedtype,$recordid]);

				return redirect('/master/add/furnishedtype')->with('success','FURNISHED TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FURNISHED TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editFurnishedType($recordid)
	{
        $data = DB::table('furnishedtype_tbl')->where('furnishedtypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/furnishedtype_edit',compact('data'));
    }
    public function getFurnishedTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('furnishedtype_tbl')
                ->orderBy('furnishedtype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('furnishedtype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/furnishedtypeTable', ['data' => $data]);
    }
    public function deleteFurnishedType($recordid)
	{
        $res = DB::delete('DELETE FROM furnishedtype_tbl WHERE furnishedtypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* FURNISHED TYPE RELATED CLOSED */

	
	/* DOCUMENT TYPE RELATED START */
    public function addDocumentType(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','documenttype');
		Session::put('menid',61);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=61 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/documenttype_add');
    }
    public function storeDocumentType(Request $request,$recordid)
	{        
        $rules = [
            'documenttype' => 'required|max:50',
        ];

        $messages = [
            'documenttype.required' => 'DOCUMENT TYPE NAME IS REQUIRED',
            'documenttype.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$documenttype	=	strtoupper($request->input('documenttype'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO documenttype_tbl(documenttype,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$documenttype,$userId,$userName,$creationdate]);

				return back()->with('success','DOCUMENT TYPE STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DOCUMENT TYPE COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update documenttype_tbl set documenttype=? where documenttypeid=?',[$documenttype,$recordid]);

				return redirect('/master/add/documenttype')->with('success','DOCUMENT TYPE UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DOCUMENT TYPE COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editDocumentType($recordid)
	{
        $data = DB::table('documenttype_tbl')->where('documenttypeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/documenttype_edit',compact('data'));
    }
    public function getDocumentTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('documenttype_tbl')
                ->orderBy('documenttype')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('documenttype','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/documenttypeTable', ['data' => $data]);
    }
    public function deleteDocumentType($recordid)
	{
        $res = DB::delete('DELETE FROM documenttype_tbl WHERE documenttypeid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* DOCUMENT TYPE RELATED CLOSED */
	
	/* INQUIRY STATUS RELATED START */
    public function addInquiryStatus(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','inquirystatus');
		Session::put('menid',60);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=60 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/inquirystatus_add');
    }
    public function storeInquiryStatus(Request $request,$recordid)
	{        
        $rules = [
            'inquirystatus' => 'required|max:50',
        ];

        $messages = [
            'inquirystatus.required' => 'INQUIRY STATUS NAME IS REQUIRED',
            'inquirystatus.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$inquirystatus	=	strtoupper($request->input('inquirystatus'));

		if($recordid==0)
		{
			try 
			{			
				$res = DB::insert('INSERT INTO inquirystatus_tbl(inquirystatus,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$inquirystatus,$userId,$userName,$creationdate]);

				return back()->with('success','INQUIRY STATUS STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','INQUIRY STATUS COULD NOT BE STORED (DUPLICATE). CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update inquirystatus_tbl set inquirystatus=? where statusid=?',[$inquirystatus,$recordid]);

				return redirect('/master/add/inquirystatus')->with('success','INQUIRY STATUS UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','INQUIRY STATUS COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
    }
    public function editInquiryStatus($recordid)
	{
        $data = DB::table('inquirystatus_tbl')->where('statusid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/inquirystatus_edit',compact('data'));
    }
    public function getInquiryStatusData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('inquirystatus_tbl')
                ->orderBy('inquirystatus')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('inquirystatus','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/inquirystatusTable', ['data' => $data]);
    }
    public function deleteInquiryStatus($recordid)
	{
        $res = DB::delete('DELETE FROM inquirystatus_tbl WHERE statusid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* INQUIRY STATUS RELATED CLOSED */
	
	
	/* FACING RELATED START */
    public function addFacing(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addfacing');
		Session::put('menid',59);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=59 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
        return view('admin/master/facing_add');
    }
    public function editFacing($recordid)
	{
        $data = DB::table('facing_tbl')->where('facingid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/facing_edit',compact('data'));
    }
    public function storeFacing(Request $request,$recordid)
	{        
        $rules = [
            'facingname' => 'required|max:50',
        ];

        $messages = [
            'facingname.required' => 'FACING NAME IS REQUIRED',
            'facingname.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$facingname	=	strtoupper($request->input('facingname'));

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO facing_tbl(facingname,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$facingname,$userId,$userName,$creationdate]);
				return back()->with('success','FACING NAME STORED SUCCESSFULLY!');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FACING NAME COULD NOT BE STORED (DUPLICATE). PLEASE CHECK AND TRY AGAIN!')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update facing_tbl set facingname=? where facingid=?',[$facingname,$recordid]);
				return redirect('/master/add/facing')->with('success','FACING NAME UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','FACING NAME COULD NOT BE UPDATED!')->withInput();
			}			
		}
    }
    public function getFacingData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('facing_tbl')
                ->orderBy('facingname')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('facingname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/facingTable', ['data' => $data]);
    }
    public function deleteFacing($recordid)
	{
        $res = DB::delete('DELETE FROM facing_tbl WHERE facingid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* FACING RELATED CLOSED */

	
	
	/* BANK RELATED START */
    public function addBank(Request $request){        
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addbank');

		Session::put('menid',26);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=26 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/bank_add');
    }
    public function storeBank(Request $request,$recordid){        
        
        $rules = [
			'bankname' => 'required|max:30',
        ];

        $messages = [
            'bankname.required' => 'BANK NAME IS REQUIRED',
			'bankname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$bankname  	= strtoupper($request->input('bankname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into bank_tbl (bankname,creationdate,createdby,createdbyname) values (?,?,?,?)',[$bankname,$creationdate,$userId,$userName]);

				return back()->with('success','BANK NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'BANK NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('bank_tbl')->where('bankid',$recordid)->first();
			$updateData = [];
			if(!empty($bankname))
			{
				$updateData['bankname'] = $bankname;
			}
			try
			{
				$res = DB::table('bank_tbl')->where('bankid', $recordid)->update($updateData);
				return redirect('master/add/bank')->with('success','BANK NAME UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'BANK NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getBankData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('bank_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('bankname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/bankTable', compact('data'))->render();
    }
    public function editBank($recordid)
	{
        $data = DB::table('bank_tbl')->where('bankid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/bank_edit',compact('data'));
    }
    public function deleteBank($recordid)
	{
		$res = DB::delete('delete from bank_tbl WHERE bankid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }	
	/* BANK RELATED CLOSED */
	/*BLOCK RELATED*/
    public function addBlock(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addblock');
		$tehsil	=	DB::table('tehsil_tbl')->orderby('tehsilname')->get();
		Session::put('menid',65);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=65 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$tehsilid=0;

		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/block_add',compact('tehsil','tehsilid','token'));
    }
    public function storeBlock(Request $request,$recordid)
	{        
        $rules = [
            'tehsilid' 		=> 'required|numeric',
			'blockname' 	=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'blockcode' 	=> 'required|max:5',
        ];

        $messages = [
            'tehsilid.required' 	=> 'TEHSIL NAME IS REQUIRED',
			'tehsilid.numeric' 		=> 'INVALID TEHSIL NAME',
            'blockname.required' 	=> 'BLOCK NAME IS REQUIRED',
            'blockname.max' 		=> 'MAXIMUM LENGTH IS 50',
			'blockname.regex' 		=> 'INVALID BLOCK NAME',
            'blockcode.required' 	=> 'BLOCK CODE IS REQUIRED',
            'blockcode.max' 		=> 'MAXIMUM LENGTH IS 5',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$tehsilid	=	intval($request->input('tehsilid'));
		$blockname	=	strtoupper($request->input('blockname'));
		$blockcode	=	$request->input('blockcode');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO block_tbl(tehsilid,blockname,blockcode,createdby,creationdate) VALUES (?,?,?,?,?)',[$tehsilid,$blockname,$blockcode,$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!')->with('tehsilid',$tehsilid);				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update block_tbl set tehsilid=?,blockname=?,blockcode=? where blockid=?',[$tehsilid,$blockname,$blockcode,$recordid]);
				return redirect('/master/add/block')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editBlock($recordid)
	{
		$token	=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        $tehsil	=	DB::table('tehsil_tbl')->orderby('tehsilname')->get();
		$data = DB::table('block_tbl')->where('blockid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/block_edit',compact('data','tehsil','token'));
    }
	
    public function getBlockData(Request $request)
	{
		$tehsilid 	=	intval($request->input('tehsilid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('block_tbl as a')
				->select('a.blockid','a.blockname','a.blockcode','a.createdby','a.creationdate','b.tehsilname')
				->leftjoin('tehsil_tbl as b','b.tehsilid','=','a.tehsilid')
                ->when($tehsilid!=0,function($query) use ($tehsilid){
                    return $query->where('a.tehsilid','=',$tehsilid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.blockname','like','%'.$pagesearch.'%')
					             ->orwhere('a.blockcode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.blockname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/blockTable', ['data' => $data]);

    }
    public function deleteBlock($recordid)
	{
		$res = true;//DB::delete('DELETE FROM block_tbl WHERE blockid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*BLOCK RELATED*/
	/*TEHSIL RELATED*/
    public function addTehsil(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addtehsil');
		$district	=	DB::table('district_tbl')->orderby('districtname')->get();
		Session::put('menid',64);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=64 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$districtid=0;
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/tehsil_add',compact('district','districtid','token'));
    }
    public function storeTehsil(Request $request,$recordid)
	{        
        $rules = [
            'districtid' 		=> 'required|numeric',
			'tehsilname' 	=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'tehsilcode' 	=> 'required|max:5',
        ];

        $messages = [
            'districtid.required' 	=> 'DISTRICT NAME IS REQUIRED',
			'districtid.numeric' 	=> 'INVALID DISTRICT NAME',
            'tehsilname.required' 	=> 'TEHSIL NAME IS REQUIRED',
            'tehsilname.max' 		=> 'MAXIMUM LENGTH IS 50',
			'tehsilname.regex' 		=> 'INVALID TEHSIL NAME',
            'tehsilcode.required' 	=> 'TEHSIL CODE IS REQUIRED',
            'tehsilcode.max' 		=> 'MAXIMUM LENGTH IS 5',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$districtid	=	intval($request->input('districtid'));
		$tehsilname	=	strtoupper($request->input('tehsilname'));
		$tehsilcode	=	$request->input('tehsilcode');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO tehsil_tbl(districtid,tehsilname,tehsilcode,createdby,creationdate) VALUES (?,?,?,?,?)',[$districtid,$tehsilname,$tehsilcode,$userId,$creationdate]);

				return back()->with('success','RECORD STORED SUCCESSFULLY!')->with('districtid',$districtid);				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update tehsil_tbl set districtid=?,tehsilname=?,tehsilcode=? where tehsilid=?',[$districtid,$tehsilname,$tehsilcode,$recordid]);
				return redirect('/master/add/tehsil')->with('success','RECORD UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editTehsil($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        $district	=	DB::table('district_tbl')->orderby('districtname')->get();
		$data = DB::table('tehsil_tbl')->where('tehsilid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/tehsil_edit',compact('data','district','token'));
    }
	
    public function getTehsilData(Request $request)
	{
		$districtid 	=	intval($request->input('districtid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('tehsil_tbl as a')
				->select('a.tehsilid','a.tehsilname','a.tehsilcode','a.createdby','a.creationdate','b.districtname')
				->leftjoin('district_tbl as b','b.districtid','=','a.districtid')
                ->when($districtid!=0,function($query) use ($districtid){
                    return $query->where('a.districtid','=',$districtid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.tehsilname','like','%'.$pagesearch.'%')
					             ->orwhere('a.tehsilcode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.tehsilname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/tehsilTable', ['data' => $data]);

    }
    public function deleteTehsil($recordid)
	{
		$res = true;//DB::delete('DELETE FROM tehsil_tbl WHERE tehsilid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*TEHSIL RELATED CLOSED*/
	/*DISTRICT RELATED*/
    public function addDistrict(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','adddistrict');
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
		Session::put('menid',63);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=63 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$stateid=0;
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/district_add',compact('state','stateid','token'));
    }
    public function storeDistrict(Request $request,$recordid)
	{        
        $rules = [
            'stateid' 		=> 'required|numeric',
			'districtname' 	=> 'required|max:50|regex:/^[a-zA-Z0-9\s.]+$/',
			'districtcode' 	=> 'required|max:5',
        ];

        $messages = [
            'stateid.required' 		=> 'STATE NAME IS REQUIRED',
			'stateid.numeric' 		=> 'INVALID STATE NAME',
            'districtname.required' => 'DISTRICT NAME IS REQUIRED',
            'districtname.max' 		=> 'MAXIMUM LENGTH IS 50',
			'districtname.regex' 	=> 'INVALID DISTRICT NAME',
            'districtcode.required' => 'DISTRICT CODE IS REQUIRED',
            'districtcode.max' 		=> 'MAXIMUM LENGTH IS 5',
        ];

        $validatedData 	= $request->validate($rules,$messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		
		
		$userId     	= $request->session()->get('userId');
		$userName   	= $request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$stateid		=	intval($request->input('stateid'));
		$districtname	=	strtoupper($request->input('districtname'));
		$districtcode	=	$request->input('districtcode');

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO district_tbl(stateid,districtname,districtcode,createdby,creationdate) VALUES (?,?,?,?,?)',[$stateid,$districtname,$districtcode,$userId,$creationdate]);

				return back()->with('success','DISTRICT NAME STORED SUCCESSFULLY!')->with('stateid',$stateid);				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update district_tbl set stateid=?,districtname=?,districtcode=? where districtid=?',[$stateid,$districtname,$districtcode,$recordid]);
				return redirect('/master/add/district')->with('success','DISTRICT UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate','DUPLICATE DATA FOUND.')->withInput();
			}			
		}
    }
    public function editDistrict($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        $state	=	DB::table('state_tbl')->orderby('statename')->get();
		$data = DB::table('district_tbl')->where('districtid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/district_edit',compact('data','state','token'));
    }
	
    public function getDistrictData(Request $request)
	{
		$stateid 	=	intval($request->input('stateid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('district_tbl as a')
				->select('a.districtid','a.districtname','a.districtcode','a.createdby','a.creationdate','b.statename')
				->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.districtname','like','%'.$pagesearch.'%')
					             ->orwhere('a.districtcode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.districtname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/districtTable', ['data' => $data]);

    }
    public function deleteDistrict($recordid)
	{
		$res = true;//DB::delete('DELETE FROM district_tbl WHERE districtid=?', [Crypt::decrypt($recordid)]);
		
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*DISTRICT RELATED CLOSED*/
	/* AREA RELATED START */
    public function addArea(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addarea');
		$city	=	DB::table('city_tbl')->orderby('cityname')->get();
		Session::put('menid',52);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=52 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$cityid=0;
        return view('admin/master/area_add',compact('city','cityid'));
    }
    public function storeArea(Request $request,$recordid)
	{        
        $rules = [
            'cityid' => 'required',
			'areaname' => 'required|max:50',
        ];

        $messages = [
            'cityid.required' => 'CITY NAME IS REQUIRED',
            'areaname.required' => 'AREA IS REQUIRED',
            'areaname.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$cityid	=	intval($request->input('cityid'));
		$stateid=	DB::table('city_tbl')->where('cityid','=',$cityid)->first();
		$areaname	=	strtoupper($request->input('areaname'));

		if($recordid==0)
		{
			try
			{
				$res = DB::insert('INSERT INTO area_tbl(cityid,areaname,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)',[$cityid,$areaname,$userId,$userName,$creationdate]);

				return back()->with('success','AREA NAME STORED SUCCESSFULLY!')->with('cityid',$cityid);				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				$res = DB::update('update area_tbl set cityid=?,areaname=? where areaid=?',[$cityid,$areaname,$recordid]);
				return redirect('/master/add/area')->with('success','AREA UPDATED SUCCESSFULLY!');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
    }
    public function editArea($recordid)
	{
        $city	=	DB::table('city_tbl')->orderby('cityname')->get();
		$data = DB::table('area_tbl')->where('areaid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/area_edit',compact('data','city'));
    }
    public function getAreaData(Request $request)
	{
		$cityid 	=	intval($request->input('cityid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('area_tbl as a')
				->select('a.areaid','a.areaname','a.createdbyname','a.creationdate','b.cityname')
				->leftjoin('city_tbl as b','b.cityid','=','a.cityid')
                ->when($cityid!=0,function($query) use ($cityid){
                    return $query->where('a.cityid','=',$cityid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.cityname','like','%'.$pagesearch.'%')
					             ->orwhere('a.pincode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.areaname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/areaTable', ['data' => $data]);

    }
    public function deleteArea($recordid)
	{
		$exists	=	DB::table('vendor_tbl')->whereRaw("FIND_IN_SET(?, areaids)>0",[Crypt::decrypt($recordid)])->exists();
		if(!$exists)
		{				
			$res = DB::delete('DELETE FROM area_tbl WHERE areaid=?', [Crypt::decrypt($recordid)]);
			
			if($res)
			{
				return response()->json(['success' => true,'fail'=>'']);
			}
			else
			{
				return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
			}
		}
		else
		{
			return response()->json(['fail'=>'THIS AREA IS ASSOCIATED WITH SOME VENDORS. IT CAN NOT BE DELETED.']);
		}
    }
	/* AREA RELATED CLOSED */

	
    public function addSlider(Request $request){        
		$displayorder = DB::table('slider_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');

		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addslider');
		Session::put('menid',9);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=9 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		$category = DB::table('category_tbl')->orderby('displayorder')->get();
        return view('admin/master/slider_add',compact('displayorder','category'));
    }
    public function addSubCategory(){        
		$displayorder = DB::table('subcategory_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','addsubcategory');
		$category	=	DB::table('category_tbl')->orderby('category')->get();
        return view('admin/master/subcategory_add',compact('category','displayorder'));
    }
	/* VEHICLE */
    public function addVehicle(Request $request){        
		$vehiclebrand	=	DB::table('vehiclebrand_tbl')->orderby('displayorder')->get();
		$displayorder = DB::table('vehicle_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','vehicle');

		Session::put('menid',12);
		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=12 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action = DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}

        return view('admin/master/vehicle_add',compact('displayorder','vehiclebrand'));
    }
    public function storeVehicle(Request $request,$recordid){        
        
        $rules = [
			'vehiclebrandid' => 'required',
			'vehicle' => 'required|max:30',
			'displayorder' => 'required|numeric',
			'icon' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
        ];

        $messages = [
			'vehiclebrandid.required' => 'IT IS REQUIRED',
			'vehicle.required' => 'VEHICLE IS REQUIRED',
			'vehicle.max' => 'MAXIMUM LENGTH REACHED',
			'displayorder.required' => 'IT IS REQUIRED',
			'displayorder.numeric' => 'NUMERIC ONLY',
			'icon.required' => 'IT IS REQUIRED',
			'icon.max' => 'MAX SIZE REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$vehiclebrandid =	intval($request->input('vehiclebrandid'));
		$vehicle  		= 	strtoupper($request->input('vehicle'));
		$displayorder 	=	intval($request->input('displayorder'));
		$icon    		= (String) $request->file('icon');

		if($recordid==0)
		{
			try 
			{
				if($icon!='')
				{
					$icon	=	$request->file('icon')->store('uploads/vehicles', 'public');
				}
				
				$res = DB::insert('insert into vehicle_tbl (vehiclebrandid,vehicle,displayorder,icon,creationdate,createdby,createdbyname) values (?,?,?,?,?,?,?)',[$vehiclebrandid,$vehicle,$displayorder,$icon,$creationdate,$userId,$userName]);

				return back()->with('success','VEHICLE NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				Storage::disk('public')->delete($icon);
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('vehicle_tbl')->where('vehicleid',$recordid)->first();
			if($icon!='')
			{
				if ($data->icon!='') {
					Storage::disk('public')->delete($data->icon);
				}
				$icon= $request->file('icon')->store('uploads/vehicles', 'public');
			}
			$updateData = [];
			if(!empty($vehiclebrandid))
			{
				$updateData['vehiclebrandid'] = $vehiclebrandid;
			}
			if(!empty($vehicle))
			{
				$updateData['vehicle'] = $vehicle;
			}
			if(!empty($displayorder))
			{
				$updateData['displayorder'] = $displayorder;
			}
			if(!empty($icon))
			{
				$updateData['icon'] = $icon;
			}
			try
			{
				$res = DB::table('vehicle_tbl')->where('vehicleid', $recordid)->update($updateData);
				return redirect('master/add/vehicle')->with('success','VEHICLE DETAIL UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if($icon!='')
				Storage::disk('public')->delete($icon);
				
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getVehicleData(Request $request)
	{
		$vehiclebrandid	=	intval($request->input('vehiclebrandid'));
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	=	$request->input('page',1);
        $data = DB::table('vehicle_tbl as a')
				->select('a.*','b.vehiclebrand')
				->leftjoin('vehiclebrand_tbl as b','b.vehiclebrandid','=','a.vehiclebrandid')
                ->when($vehiclebrandid!=0,function($query) use ($vehiclebrandid){
                    return $query->where('a.vehiclebrandid','=',$vehiclebrandid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.vehicle','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/vehicleTable', compact('data'))->render();
    }
    public function editVehicle($recordid)
	{
		$vehiclebrand	=	DB::table('vehiclebrand_tbl')->orderby('displayorder')->get();
        $data = DB::table('vehicle_tbl')->where('vehicleid','=',$recordid)->first();
        return view('admin/master/vehicle_edit',compact('data','vehiclebrand'));
    }
    public function deleteVehicle($recordid)
	{
		$content = DB::table('vehicle_tbl')->where('vehicleid','=',$recordid)->first();
		if($content->icon!='')
		{
			Storage::disk('public')->delete($content->icon);
		}
		$res = DB::delete('delete from vehicle_tbl WHERE vehicleid=?',[$recordid]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/* VEHICLE */


    public function storeSubCategory(Request $request,$recordid){        
        
        $rules = [
            'categoryid' => 'required',
			'subcategory' => 'required|max:50',
            'displayorder' => 'required',
			'subcategoryicon' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
			'subcategorypage' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',			
        ];

        $messages = [
            'categoryid.required' => 'CATEGORY IS REQUIRED',
			'subcategory.required' => 'SUB CATEGORY IS REQUIRED',
            'subcategory.max' => 'MAXIMUM LENGTH IS 50',
            'displayorder.required' => 'REQUIRED',
			'subcategoryicon.required' => 'SUB CATEGORY ICON IS REQUIRED',
			'subcategorypage.required' => 'SUB CATEGORY PAGE IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $categoryid   	= intval($request->input('categoryid'));
		$subcategory   	= strtoupper($request->input('subcategory'));
        $displayorder 	= intval($request->input('displayorder'));
        $userId      	= $request->session()->get('userId');
        $userName      	= $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$subcategoryicon    = (String) $request->file('subcategoryicon');		
		$subcategorypage    = (String) $request->file('subcategorypage');		
		$icontype = "";
		
		if($recordid==0)
		{
			if($subcategoryicon!='')
			{
				$subcategoryicon= $request->file('subcategoryicon')->store('uploads/subcategoryicons', 'public');
			}

			if($subcategorypage!='')
			{
				$subcategorypage= $request->file('subcategorypage')->store('uploads/subcategorypage', 'public');
			}
			
			$res = DB::insert('INSERT INTO subcategory_tbl (categoryid,subcategory,displayorder,subcategoryicon,subcategorypage,icontype,pagetype,creationdate,createdbyid,createdbyname) VALUES (?,?,?,?,?,?,?,?,?,?)', [$categoryid,$subcategory,$displayorder,$subcategoryicon,$subcategorypage,'IMAGE','IMAGE',$creationdate,$userId,$userName]);

			if($res)
			{
				return back()->with('success','SUB CATEGORY DETAIL STORED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','SUB CATEGORY DETAIL COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$cont = DB::table('subcategory_tbl')->where('subcategoryid',$recordid)->first();
			if($subcategoryicon!='')
			{
				if ($cont->subcategoryicon!='') {
					Storage::disk('public')->delete($cont->subcategoryicon);
				}
				$subcategoryicon= $request->file('subcategoryicon')->store('uploads/subcategoryicons', 'public');
			}
			if($subcategorypage!='')
			{
				if ($cont->subcategorypage!='') {
					Storage::disk('public')->delete($cont->subcategorypage);
				}
				$subcategorypage= $request->file('subcategorypage')->store('uploads/subcategorypage', 'public');
			}

			$updateData = [];
			if(!empty($categoryid)) {
				$updateData['categoryid'] = $categoryid;
			}
			if(!empty($subcategory)) {
				$updateData['subcategory'] = $subcategory;
			}
			if(!empty($displayorder)) {
				$updateData['displayorder'] = $displayorder;
			}
			if(!empty($subcategoryicon)) {
				$updateData['subcategoryicon'] = $subcategoryicon;
			}
			if(!empty($subcategorypage)) {
				$updateData['subcategorypage'] = $subcategorypage;
			}
			$res = DB::table('subcategory_tbl')->where('subcategoryid', $recordid)->update($updateData);
			if($res)
			{
				return redirect('master/add/subcategory')->with('success','SUB CATEGORY DETAIL UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','SUB CATEGORY DETAIL COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
    }
	
    public function storeSlider(Request $request,$recordid){        
        
        $rules = [
            'displayorder' => 'required',
            'sliderimage' => ($recordid == 0) ? 'required|max:1024' : 'nullable|max:1024',
        ];

        $messages = [
            'displayorder.required' => 'DISPLAY ORDER IS REQUIRED',
            'sliderimage.required' => 'SLIDER IMAGE IS REQUIRED',
            'sliderimage.max' => 'MAX 1 MB IS ALLOWED',
        ];

        $validatedData  = $request->validate($rules, $messages);

        $userId         = $request->session()->get('userId');
        $userName       = $request->session()->get('userName');
        $currentDateTime= now();
        $creationdate   = $currentDateTime->format('Y-m-d H:i:s');

        $displayorder   = intval($request->input('displayorder'));
        $sliderimage    = (String) $request->file('sliderimage');
        $contenttype    =   "";

		if($recordid==0)
		{
			if($sliderimage!='')
			{
				$sliderimage= $request->file('sliderimage')->store('uploads/sliderimages', 'public');
			}
			$res = DB::insert('INSERT INTO slider_tbl (displayorder,sliderimage,imagetype,createdbyid,createdby,creationdate) VALUES (?,?,?,?,?,?)', [$displayorder,$sliderimage,'IMAGE',$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','SLIDER IMAGE STORED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','SLIDER IMAGE COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			if($sliderimage!='')
			{
				$cont = DB::table('slider_tbl')->where('sliderid',$recordid)->first();
				if ($cont->sliderimage!='') {
					Storage::disk('public')->delete($cont->sliderimage);
				}
				$sliderimage= $request->file('sliderimage')->store('uploads/sliderimages', 'public');
				$res = DB::update('UPDATE slider_tbl SET displayorder=?,sliderimage=? where sliderid=?',[$displayorder,$sliderimage,$recordid]);			
			}
			else
			{
				$res = DB::update('UPDATE slider_tbl SET displayorder=? where sliderid=?',[$displayorder,$recordid]);			
			}
			if($res)
			{
				return redirect('master/add/slider')->with('success','SLIDER UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','SLIDER COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }

    public function getSliderData(Request $request){

		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('slider_tbl')
                ->orderBy('displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
        return view('/admin/ajaxpages/sliderTable', compact('data'))->render();
    }
    public function getSubCategoryData(Request $request)
	{
		$categoryid =	$request->input('categoryid');
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('subcategory_tbl as a')
				->select('a.*','b.category')
				->leftjoin('category_tbl as b','b.categoryid','=','a.categoryid')
                ->when($categoryid!='',function($query) use ($categoryid){
                    return $query->where('a.categoryid','=',$categoryid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.subcategory','like','%'.$pagesearch.'%');
                })
                ->orderBy('displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/subcategoryTable', compact('data'))->render();
    }

    public function editSlider($recordid){

        $data = DB::table('slider_tbl')->where('sliderid','=',$recordid)->first();
        return view('admin/master/slider_edit',compact('data'));
    }
    public function editSubCategory($recordid)
	{
		$category	=	DB::table('category_tbl')->orderby('category')->get();
        $data = DB::table('subcategory_tbl')->where('subcategoryid','=',$recordid)->first();
        return view('admin/master/subcategory_edit',compact('data','category'));
    }



    public function deleteSlider($recordid)
	{
		$content = DB::table('slider_tbl')->where('sliderid','=',$recordid)->first();
		if($content->sliderimage!='')
		{
			Storage::disk('public')->delete($content->sliderimage);
		}
		$res = DB::delete('DELETE FROM slider_tbl WHERE sliderid=?', [$recordid]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
    public function deleteSubCategory($recordid)
	{
		$content = DB::table('subcategory_tbl')->where('subcategoryid','=',$recordid)->first();
		if($content->subcategoryicon!='')
		{
			Storage::disk('public')->delete($content->subcategoryicon);
		}
		if($content->subcategorypage!='')
		{
			Storage::disk('public')->delete($content->subcategorypage);
		}
		
		$res = DB::delete('delete from subcategory_tbl WHERE subcategoryid=?',[$recordid]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }

    public function getTierList(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));
        $tierlist =DB::table('pricing_tbl as a')
				->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
                ->select('b.tierid as value','b.tiername as label')
                ->where('a.categoryid','=',$categoryid)
                ->orderby('b.tiername')
                ->get();        
        return response()->json($tierlist);
    }

    public function getTiersList(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));

        $tierlist =DB::table('pricing_tbl as a')
					->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
					->select('b.tierid as value','b.tiername as label')
					->where('a.categoryid','=',$categoryid)
					->orderby('b.tiername')
					->get();        

        $rolelist =DB::table('role_tbl as a')
					->select('a.roleid as value','a.role as label')
					->where('a.categoryid','=',$categoryid)
					->orderby('a.role')
					->get();
		if(!$rolelist)
		{
			$rolelist	=	[];
		}

        return response()->json([
			'tierlist'	=>	$tierlist,
			'rolelist'	=>	$rolelist,
		]);
    }

    public function getExperienceList(Request $request)
	{
        $tierid 	= intval($request->input('tierid'));
		$categoryid = intval($request->input('categoryid'));

		if($categoryid==1)
		{
			$experienceid = intval($request->input('experienceid'));
			$experiencelist =DB::table('remuneration_tbl as a')
						->leftJoin('work_experience as b','b.experienceid','=','a.experienceid')
						->select('a.remunerationid as value',DB::raw("CONCAT(b.workexperience, ' [Level ', a.experiencelevel, ']') as label"))
						->where('a.tierid','=',$tierid)
						->where('a.categoryid','=',$categoryid)
						->where('a.experienceid','=',$experienceid)
						->orderby('a.experiencelevel')
						->get();        
		}
		if($categoryid==2)
		{
			$sectorid 		= 	intval($request->input('sectorid'));
			$experiencelist =	DB::table('remuneration_tbl as a')
								->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
								->join('remuneration_rate_list as c','c.rateid','=','a.rateid')								
								->select('a.remunerationid as value','b.consultantposition as label')
								->where('a.tierid','=',$tierid)
								->where('c.isactive','=',1)
								->where('a.categoryid','=',$categoryid)
								->where('a.sectorid','=',$sectorid)
								->orderby('b.consultantposition')
								->get();        
			
		}
        return response()->json($experiencelist);
    }
    public function getPositionList(Request $request)
	{
        $tierid = intval($request->input('tierid'));
		$categoryid = intval($request->input('categoryid'));
		$sectorid = intval($request->input('sectorid'));

        $experiencelist =DB::table('remuneration_tbl as a')
					->leftJoin('position_tbl as b','b.positionid','=','a.positionid')
					->select('a.remunerationid as value','b.consultantposition as label')
					->where('a.tierid','=',$tierid)
					->where('a.categoryid','=',$categoryid)
					->where('a.sectorid','=',$sectorid)
					->orderby('a.consultantposition')
					->get();        

        return response()->json($experiencelist);
    }

    public function getRemunerationAmount(Request $request)
	{
        $remunerationid = intval($request->input('remunerationid'));

        $remuneration	=	DB::table('remuneration_tbl')->where('remunerationid','=',$remunerationid)->first();
		$pricing		=	DB::table('pricing_tbl')
								->where('categoryid','=',$remuneration->categoryid)
								->where('tierid','=',$remuneration->tierid)
								->first();
								
		$amount			=	$remuneration->remuneration;
		$amount			=	$amount+(($amount*$pricing->operatingmargin)/100);
		$budget			=	$amount+(($amount*$pricing->tax)/100);
		$experience		=	"";
		
		if($remuneration->categoryid==2)
		{
			$experience	=	DB::table('position_tbl')->where('positionid',$remuneration->positionid)->value('experience');
		}
        return response()->json(['budget'=>round($budget,2),'experience'=>$experience]);
    }
	
	/*
    public function getRemunerationAmount(Request $request)
	{
        $remunerationid = intval($request->input('remunerationid'));

        $remuneration	=	DB::table('remuneration_tbl')->where('remunerationid','=',$remunerationid)->first();
		$pricing		=	DB::table('pricing_tbl')
								->where('categoryid','=',$remuneration->categoryid)
								->where('tierid','=',$remuneration->tierid)
								->first();
		$amount			=	$remuneration->remuneration;
		$amount			=	$amount+(($amount*$pricing->operatingmargin)/100);
		$budget			=	$amount+(($amount*$pricing->tax)/100);
		$admincharge	=	(($budget*$pricing->admincharge)/100);
		$total			=	$budget+$admincharge;
		$experience		=	"";
		if($remuneration->categoryid==2)
		{
			$experience	=	DB::table('position_tbl')->where('positionid',$remuneration->positionid)->value('experience');
		}
        return response()->json(['budget'=>round($budget,2),'admincharge'=>round($admincharge,2),'total'=>round($total,2),'experience'=>$experience]);
    }
	*/
    public function getConsultantRemunerationAmount(Request $request)
	{
        $positionid 	= intval($request->input('positionid'));
		
		$categoryid 	= intval($request->input('categoryid'));
		$sectorid 		= intval($request->input('sectorid'));
		$tierid 		= intval($request->input('tierid'));

        $remuneration	=	DB::table('remuneration_tbl')->where('remunerationid','=',$remunerationid)->first();
		$pricing		=	DB::table('pricing_tbl')
								->where('categoryid','=',$remuneration->categoryid)
								->where('tierid','=',$remuneration->tierid)
								->first();
		$amount			=	$remuneration->remuneration;
		$amount			=	$amount+(($amount*$pricing->operatingmargin)/100);
		$amount			=	$amount+(($amount*$pricing->tax)/100);
		$amount			=	$amount+(($amount*$pricing->admincharge)/100);

        return response()->json(round($amount,2));
    }

    public function loadEoiData(Request $request)
	{
		$userId	=	$request->session()->get('userId');
		$data	=	DB::table('eoi_temp_requirement as a')
						->select('a.recordid','a.qualification','a.duration','a.budget','a.admincharge','a.total','a.grandtotal','a.role','a.experience','b.jobcategory','c.tiername','f.workexperience','e.experiencelevel','g.sectorname','h.consultantposition')
						->leftJoin('jobcategory_tbl as b','b.categoryid','=','a.categoryid')
						->leftJoin('tiermaster_tbl as c','c.tierid','=','a.tierid')
						->leftJoin('remuneration_tbl as e','e.remunerationid','=','a.remunerationid')
						->leftJoin('work_experience as f','f.experienceid','=','e.experienceid')
						->leftJoin('sector_tbl as g','g.sectorid','=','a.sectorid')
						->leftJoin('position_tbl as h','h.positionid','=','e.positionid')
						->where('a.userid','=',$userId)
						->get();
		
		return view('admin/department/ajaxpages/eoitempTable', ['data' => $data,'categoryid'=>session('categoryid')]);
    }



	/* PROJECT RELATED START */
    public function addProject(Request $request){
        Session::put('adminmenu','master');
		Session::put('adminsubmenu','addproject');
		Session::put('menid',133);

		$userId		= $request->session()->get('userId');
		$issuper	= $request->session()->get('issuper');
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		
        return view('admin/master/project_add',compact('token'));
    }
    public function editProject($recordid)
	{
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);

        $data = DB::table('project_tbl')->where('projectid','=',Crypt::decrypt($recordid))->first();

	
        return view('admin/master/project_edit',compact('data','token'));
    }
    public function storeProject(Request $request,$recordid)
	{        
        $rules = [
            'project_name' 	=> 'required|max:100|regex:/^[a-zA-Z0-9\s\.\,\[\]\(\)\*\&\@\#\!\-\=]+$/',
        ];

        $messages = [
            'project_name.required' => 	'Project name is required.',
            'project_name.max' 		=> 	'Project name must not exceed 100 characters.',
			'project_name.regex' 	=> 	'Project name may contain only letters, numbers, spaces, and these special characters: . , [ ] ( ) * & @ # ! - =',
        ];

        $validatedData = $request->validate($rules, $messages);

		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return back()->with('fail', 'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.');
		}		

		
		$userId     =	$request->session()->get('userId');
		$userName   =	$request->session()->get('userName');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			try
			{
				
				DB::table('project_tbl')
				->insert([
					'project_name'	=>	$validatedData['project_name'],
					'created_by'	=>	$userId,
					'creationdate'	=>	now()
				]);

				return back()->with('success','Project name saved successfully.');				
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}			
		}
		else
		{
			try
			{
				DB::table('project_tbl')
				->where('projectid',$recordid)
				->update([
					'project_name'	=>	$validatedData['project_name'],
				]);
				

				return redirect('/master/add/project')->with('success','Project name updated successfully.');
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.updated'))->withInput();
			}			
		}
    }
    public function getProjectData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= 	$request->input('page', 1);
		//$perPage 		= 	$request->input('perPage', 10);

        $data = DB::table('project_tbl as a')
				->select('a.*')
                ->orderBy('a.project_name')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('a.project_name','like','%'.$pagesearch.'%');
                })
				->where('a.isDeleted',0)
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/projectTable', ['data' => $data]);
    }
    public function deleteProject($recordid)
	{
		DB::table('project_tbl')
		->where('projectid',Crypt::decrypt($recordid))
		->update([
			'isDeleted'	=>	1
		]);

		return response()->json(['success' => true,'fail'=>'']);
    }
	/* PROJECT RELATED CLOSED */


}
