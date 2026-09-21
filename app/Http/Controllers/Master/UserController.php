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
use Illuminate\Database\QueryException;

class UserController extends Controller
{

	/*DEPARTMENT RELATED*/
    public function addDepartment(Request $request){        
        Session::put('adminmenu','users');
		Session::put('adminsubmenu','adddepartment');

		Session::put('menid',28);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=28 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/department_add');
    }
    public function storeDepartment(Request $request,$recordid){        
        
        $rules = [
			'departmentname' => 'required|max:30',
        ];

        $messages = [
            'departmentname.required' => 'DEPARTMENT NAME IS REQUIRED',
			'departmentname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$departmentname  	= strtoupper($request->input('departmentname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into department_tbl (departmentname,creationdate,createdby,createdbyname) values (?,?,?,?)',[$departmentname,$creationdate,$userId,$userName]);

				return back()->with('success','DEPARTMENT NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'DEPARTMENT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('department_tbl')->where('departmentid',$recordid)->first();
			$updateData = [];
			if(!empty($departmentname))
			{
				$updateData['departmentname'] = $departmentname;
			}
			try
			{
				$res = DB::table('department_tbl')->where('departmentid', $recordid)->update($updateData);
				return redirect('master/add/department')->with('success','DEPARTMENT NAME UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'DEPARTMENT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getDepartmentData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('department_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('departmentname','like','%'.$pagesearch.'%');
                })
				->orderby('departmentname')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/departmentTable', compact('data'))->render();
    }
    public function editDepartment($recordid)
	{
        $data = DB::table('department_tbl')->where('departmentid','=',$recordid)->first();
        return view('admin/master/department_edit',compact('data'));
    }
    public function deleteDepartment($recordid)
	{
		$res = DB::delete('delete from department_tbl WHERE departmentid=?',[$recordid]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	
	/*DEPARTMENT RELATED CLOSED*/
	/*APP USER RELATED*/
    public function addAppUser(Request $request)
	{
        Session::put('adminmenu','users');
		Session::put('adminsubmenu','appuser');
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
		$city	=	DB::table('city_tbl')->orderby('cityname')->get();
		$area	=	DB::table('area_tbl')->orderby('areaname')->get();
		$department	=	DB::table('department_tbl')->orderby('departmentname')->get();
		$designation	=	DB::table('designation_tbl')->orderby('designationname')->get();
		$bank	=	DB::table('bank_tbl')->orderby('bankname')->get();

		Session::put('menid',17);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=17 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$addstate	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',6)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		$addcity	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',7)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		$addarea	=	DB::table('menu_permission')
						->select('ispermitted')
						->where('menuid','=',8)
						->where('userid','=',$userId)
						->where('actionid','=',1)
						->first();
		if ($addstate === null) {
			$addstate = new \stdClass();
			$addstate->ispermitted = 0;
		}
		if ($addcity === null) {
			$addcity = new \stdClass();
			$addcity->ispermitted = 0;
		}
		if ($addarea === null) {
			$addarea = new \stdClass();
			$addarea->ispermitted = 0;
		}
		if($issuper==1)
		{
			$addstate->ispermitted= 1;
			$addcity->ispermitted = 1;
			$addarea->ispermitted = 1;
		}
        return view('admin/master/appuser_add',compact('state','city','area','addstate','addcity','addarea','department','designation','bank'));
    }

    public function storeAppUser(Request $request,$recordid){        
        
        $rules = [
            'name' => 'required',
			'mobilenumber'   => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email' => 'required|email',
			'gender' => 'required',
			'fathername' => 'nullable|max:50',
			'dob' => 'nullable|date',
			'departmentid' => 'required',
			'designationid' => 'required',
			'basicsalary' => 'required|regex:/^\d+(\.\d{1,2})?$/',
			'ta' => 'nullable|numeric',
			'da' => 'nullable|numeric',
			'hra' => 'nullable|numeric',
			'ma' => 'nullable|numeric',
            'address' => 'required',
			'pincode' => 'nullable|numeric',
			'checkintime' => 'required|date_format:H:i',
			'checkouttime' => 'required|date_format:H:i',
			'accountnumber' => 'nullable|numeric',
			'username' => 'required',
			'password' => 'required',
			'profilepic' => 'nullable|max:512',
        ];

        $messages = [
            'name.required' => 'NAME IS REQUIRED',
			'mobilenumber.required' => 'NAME IS REQUIRED',
			'mobilenumber.regex' => 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' => 'ONLY DIGITS ALLOWED',
			'email.required' => 'EMAIL IS REQUIRED',
			'email.email' => 'INVALID EMAIL',
			'gender.required' => 'REQUIRED',
			'fathername.max' => 'MAX LENGTH REACHED',
			'dob.date' => 'MUST BE DATE',
			'departmentid.required' => 'DEPARTMENT IS REQUIRED',
			'designationid.required' => 'DESIGNATION IS REQUIRED',
			'basicsalary.required' => 'SALARY IS REQUIRED',
			'basicsalary.regex' => 'MUST BE VALID VALUE',
			'ta.numeric' => 'MUST BE VALID VALUE',
			'da.numeric' => 'MUST BE VALID VALUE',
			'hra.numeric' => 'MUST BE VALID VALUE',
			'ma.numeric' => 'MUST BE VALID VALUE',
            'address.required' => 'ADDRESS IS REQUIRED',
			'pincode.numeric' => 'ENTER VALID PINCODE',
			'checkintime.required' => 'IT IS REQUIRED',
			'checkintime.date_format' => 'MUST BE A TIME',
			'checkouttime.required' => 'IT IS REQUIRED',
			'checkouttime.date_format' => 'MUST BE A TIME',
			'accountnumber.numeric' => 'NUMBERS ONLY',
			'username.required' => 'IT IS REQUIRED',
			'password.required' => 'IT IS REQUIRED',
			'profilepic.max' => 'MAX SIZE REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $name   		= strtoupper($request->input('name'));
        $mobilenumber	= (String) $request->input('mobilenumber');
		$email 			= (String) ($request->input('email'));
		$gender 		= (String) ($request->input('gender'));
		$fathername 	= (String) ($request->input('fathername'));
		$dob 			= (String) ($request->input('dob'));
		$departmentid	= intval($request->input('departmentid'));
		$designationid	= intval($request->input('designationid'));
		$basicsalary	= doubleval($request->input('basicsalary'));
		$ta				= doubleval($request->input('ta'));
		$da				= doubleval($request->input('da'));	
		$hra			= doubleval($request->input('hra'));
		$ma				= doubleval($request->input('ma'));
		$address		= (String) $request->input('address');
		$stateid		= intval($request->input('stateid'));
		$cityid			= intval($request->input('cityid'));
		$areaid			= intval($request->input('areaid'));
		$pincode		= (String) $request->input('pincode');
		$checkintime	= date('H:i',strtotime($request->input('checkintime')));
		$checkouttime	= date('H:i',strtotime($request->input('checkouttime')));		
		$remark			= (String) $request->input('remark');
		$bankid			= intval($request->input('bankid'));
		$accountnumber	= (String) $request->input('accountnumber');
		$ifsccode		= (String) $request->input('ifsccode');
		$branchname		= (String) $request->input('branchname');
		
		$username		= (String) $request->input('username');
		$password		= (String) $request->input('password');

		$profilepic    	= (String) $request->file('profilepic');		
		$accesstoken	= rand(10000000,99999999);
		
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		
		if($recordid==0)
		{
			if($profilepic!='')
			{
				$profilepic= $request->file('profilepic')->store('uploads/profilepic', 'public');
			}
			try 
			{
				$res = DB::insert('INSERT INTO applicationusers (name,mobilenumber,email,gender,fathername,dob,departmentid,designationid,basicsalary,ta,da,hra,ma,checkintime,checkouttime,bankid,accountnumber,ifsccode,branchname,username,password,stateid,cityid,areaid,address,pincode,remark,profilepic,accesstoken,creationdate,createdby,createdbyname,fcmid,macid) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$name,$mobilenumber,$email,$gender,$fathername,$dob,$departmentid,$designationid,$basicsalary,$ta,$da,$hra,$ma,$checkintime,$checkouttime,$bankid,$accountnumber,$ifsccode,$branchname,$username,$password,$stateid,$cityid,$areaid,$address,$pincode,$remark,$profilepic,$accesstoken,$creationdate,$userId,$userName,'','']);

				return back()->with('success','APPLICATION USER DETAIL STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				Storage::disk('public')->delete($profilepic);
				
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'MOBILE NUMBER, EMAIL OR LOGIN USER NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$cont = DB::table('applicationusers')->where('userid',$recordid)->first();
			if($profilepic!='')
			{
				if ($cont->profilepic!='') {
					Storage::disk('public')->delete($cont->profilepic);
				}
				$profilepic= $request->file('profilepic')->store('uploads/profilepic', 'public');
			}

			$updateData = [];
			if(!empty($name)) {
				$updateData['name'] = $name;
			}
			if(!empty($mobilenumber)) {
				$updateData['mobilenumber'] = $mobilenumber;
			}
			if(!empty($email)) {
				$updateData['email'] = $email;
			}
			if(!empty($gender)) {
				$updateData['gender'] = $gender;
			}
			if(!empty($fathername)) {
				$updateData['fathername'] = $fathername;
			}
			if(!empty($dob)) {
				$updateData['dob'] = $dob;
			}
			if(!empty($departmentid)) {
				$updateData['departmentid'] = $departmentid;
			}
			if(!empty($designationid)) {
				$updateData['designationid'] = $designationid;
			}
			if(!empty($basicsalary)) {
				$updateData['basicsalary'] = $basicsalary;
			}
			if(!empty($ta)) {
				$updateData['ta'] = $ta;
			}
			if(!empty($da)) {
				$updateData['da'] = $da;
			}
			if(!empty($hra)) {
				$updateData['hra'] = $hra;
			}
			if(!empty($ma)) {
				$updateData['ma'] = $ma;
			}
			if(!empty($checkintime)) {
				$updateData['checkintime'] = $checkintime;
			}
			if(!empty($checkouttime)) {
				$updateData['checkouttime'] = $checkouttime;
			}
			if(!empty($bankid)) {
				$updateData['bankid'] = $bankid;
			}
			if(!empty($accountnumber)) {
				$updateData['accountnumber'] = $accountnumber;
			}
			if(!empty($ifsccode)) {
				$updateData['ifsccode'] = $ifsccode;
			}
			if(!empty($branchname)) {
				$updateData['branchname'] = $branchname;
			}
			if(!empty($username)) {
				$updateData['username'] = $username;
			}
			if(!empty($password)) {
				$updateData['password'] = $password;
			}
			if(!empty($stateid)) {
				$updateData['stateid'] = $stateid;
			}
			if(!empty($cityid)) {
				$updateData['cityid'] = $cityid;
			}
			if(!empty($areaid)) {
				$updateData['areaid'] = $areaid;
			}
			if(!empty($address)) {
				$updateData['address'] = $address;
			}
			if(!empty($pincode)) {
				$updateData['pincode'] = $pincode;
			}
			if(!empty($remark)) {
				$updateData['remark'] = $remark;
			}
			if(!empty($profilepic)) {
				$updateData['profilepic'] = $profilepic;
			}
			$res = DB::table('applicationusers')->where('userid', $recordid)->update($updateData);
			if($res)
			{
				return redirect('master/add/appuser')->with('success','USER DETAIL UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','USER DETAIL COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
    }
    public function getAppUserData(Request $request)
	{
		$departmentid 	=	intval($request->input('departmentid'));
		$designationid 	=	intval($request->input('designationid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= $request->input('page', 1);
        
		$data = DB::table('applicationusers as a')
				->select('a.*','b.statename','c.cityname','d.areaname','e.departmentname','f.designationname','g.bankname')
				->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
				->leftjoin('city_tbl as c','c.cityid','=','a.cityid')
				->leftjoin('area_tbl as d','d.areaid','=','a.areaid')
				->leftjoin('department_tbl as e','e.departmentid','=','a.departmentid')
				->leftjoin('designation_tbl as f','f.designationid','=','a.designationid')
				->leftjoin('bank_tbl as g','g.bankid','=','a.bankid')
                ->when($departmentid!=0,function($query) use ($departmentid){
                    return $query->where('a.departmentid','=',$departmentid);
                })
                ->when($departmentid!=0,function($query) use ($departmentid){
                    return $query->where('a.departmentid','=',$departmentid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.name','like','%'.$pagesearch.'%')
								->orwhere('a.mobilenumber','like','%'.$pagesearch.'%')
								->orwhere('a.email','like','%'.$pagesearch.'%')
								->orwhere('a.address','like','%'.$pagesearch.'%')
								->orwhere('a.username','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.name')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/appuserTable', compact('data'))->render();
    }
    public function editAppUser($recordid)
	{		
		$data = DB::table('applicationusers')->where('userid','=',$recordid)->first();
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
		$department	=	DB::table('department_tbl')->orderby('departmentname')->get();
		$designation	=	DB::table('designation_tbl')->orderby('designationname')->get();
		$bank	=	DB::table('bank_tbl')->orderby('bankname')->get();
		if($data->stateid!=0)
		{
			$city	=	DB::table('city_tbl')->where('stateid','=',$data->stateid)->orderby('cityname')->get();
		}
		else
		{
			$city	=	[];
		}
		if($data->cityid!=0)
		{
			$area	=	DB::table('area_tbl')->where('cityid','=',$data->cityid)->orderby('areaname')->get();
		}
		else
		{
			$area	=	[];
		}
        return view('admin/master/appuser_edit',compact('data','state','city','area','department','designation','bank'));
    }
    public function deleteAppUser($recordid)
	{
		$content = DB::table('applicationusers')->where('userid','=',$recordid)->first();
		if($content->profilepic!='')
		{
			Storage::disk('public')->delete($content->profilepic);
		}	
		$res = DB::delete('delete from applicationusers WHERE userid=?',[$recordid]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/*APP USER RELATED CLOSED*/
}
