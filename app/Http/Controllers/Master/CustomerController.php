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

class CustomerController extends Controller
{
    /* CUSTOMER RELATED START */
    public function addCustomer(Request $request)
	{
        Session::put('adminmenu','customers');
		Session::put('adminsubmenu','addcustomer');

		Session::put('menid',46);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=46 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		$state			=	DB::table('state_tbl')->orderby('statename')->get();
		$city			=	DB::table('city_tbl')->orderby('cityname')->get();
		$area			=	DB::table('area_tbl')->orderby('areaname')->get();
		
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
						->where('menuid','=',52)
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
		
		return view('admin/master/customer_add',compact('state','city','addstate','addcity','addarea'));
    }
    public function storeCustomer(Request $request,$recordid){        
        
        $rules = [
			'profilepic'		=> 'nullable|max:512',
            'name' 				=> 'required|max:50',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email' 			=> 'nullable|email',
			'postalcode' 		=> 'nullable|max:20',
			'completeaddress'	=> 'nullable|max:250',
			'loginpassword'		=> 'required|max:50',
        ];

        $messages = [
            'profilepic.max' 			=> __('validation.thisis512.max'),
			'name.required'				=> __('validation.thisis.required'),
			'name.max'					=> __('validation.thisis50.max'),
			'mobilenumber.required'		=> __('validation.thisis.required'),
			'mobilenumber.regex'		=> __('validation.thisis.invalid'),
			'email.email'				=> __('validation.thisis.email'),
			'postalcode.max'			=> __('validation.thisis20.max'),
			'completeaddress.max'		=> __('validation.thisis250.max'),
			'loginpassword.required'	=> __('validation.thisis.required'),
			'loginpassword.max'			=> __('validation.thisis50.max'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      		= $request->session()->get('loginId');
        $userName      		= $request->session()->get('userId');
        $currentDateTime	= now();
        $creationdate  		= $currentDateTime->format('Y-m-d H:i:s');

		$name				= (String) ($request->input('name'));
		$mobilenumber		= (String) ($request->input('mobilenumber'));
		$email				= (String) $request->input('email');
		$stateid			= intval($request->input('stateid'));
		$cityid				= intval($request->input('cityid'));
		$areaid				= intval($request->input('areaid'));
		$postalcode			= (String) $request->input('postalcode');
		$completeaddress	= (String) $request->input('completeaddress');
		$loginpassword		= (String) $request->input('loginpassword');

		$locationaddress	= (String) $request->input('locationaddress');
		$latitude			= (String) $request->input('latitude');
		$longitude			= (String) $request->input('longitude');

		
		$profilepic    		= (String) $request->file('profilepic');

	
		if($recordid==0)
		{
			if($profilepic!='')
			{
				$profilepic= $request->file('profilepic')->store('uploads/customerprofiles', 'public');
			}
		
			try 
			{
				DB::insert('insert into customer_tbl (name,mobilenumber,email,stateid,cityid,areaid,postalcode,completeaddress,loginpassword,profilepic,createdby,creationdate,latitude,longitude,locationaddress,fcmid,macid,deviceid) values (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$name,$mobilenumber,$email,$stateid,$cityid,$areaid,$postalcode,$completeaddress,$loginpassword,$profilepic,$userId,$creationdate,$latitude,$longitude,$locationaddress,'','','']);
				
				return back()->with('success',__('messages.stored'));
			}
			catch (QueryException $e) 
			{	
				if($profilepic!='')
				Storage::disk('public')->delete($profilepic);
			
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{			
			$updateData = [];
			if(!empty($name))
			{
				$updateData['name'] = $name;
			}
			if(!empty($mobilenumber))
			{
				$updateData['mobilenumber'] = $mobilenumber;
			}
			if(!empty($email))
			{
				$updateData['email'] = $email;
			}
			if(!empty($postalcode))
			{
				$updateData['postalcode'] = $postalcode;
			}
			if(!empty($completeaddress))
			{
				$updateData['completeaddress'] = $completeaddress;
			}
			if(!empty($loginpassword))
			{
				$updateData['loginpassword'] = $loginpassword;
			}
			if(!empty($latitude))
			{
				$updateData['latitude'] = $latitude;
			}
			if(!empty($longitude))
			{
				$updateData['longitude'] = $longitude;
			}
			if(!empty($locationaddress))
			{
				$updateData['locationaddress'] = $locationaddress;
			}
			$updateData['stateid'] = $stateid;
			$updateData['cityid'] = $cityid;
			$updateData['areaid'] = $areaid;
			try
			{
				$customer = DB::table('customer_tbl')->where('customerid','=',$recordid)->first();
				if($profilepic!='')
				{
					Storage::disk('public')->delete($customer->profilepic);
					$profilepic= $request->file('profilepic')->store('uploads/customerprofiles', 'public');
					$updateData['profilepic'] = $profilepic;
				}
				
				DB::table('customer_tbl')->where('customerid', $recordid)->update($updateData);
				return redirect('master/add/customer')->with('success',__('messages.updated'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
			
		}
    }
    public function getCustomerData(Request $request)
	{
		$stateid 		=	intval($request->input('stateid'));
		$cityid			=	intval($request->input('cityid'));
		
		$pagesearch 	=	$request->input('pagesearch');

		$pagesize			=	$request->input('pagesize');
		$currentPage		=	$request->input('page', 1);
        
		$data = DB::table('customer_tbl as a')
				->select('a.*','b.statename','c.cityname')
				->leftJoin('state_tbl as b', 'b.stateid', '=', 'a.stateid')
				->leftJoin('city_tbl as c', 'c.cityid', '=', 'a.cityid')
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($cityid!=0,function($query) use ($cityid){
                    return $query->where('a.cityid','=',$cityid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.name','like','%'.$pagesearch.'%')
								 ->orwhere('a.mobilenumber','like','%'.$pagesearch.'%')
								 ->orwhere('a.email','like','%'.$pagesearch.'%')
								 ->orwhere('a.completeaddress','like','%'.$pagesearch.'%')
								 ->orwhere('a.loginid','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.creationdate')
				->paginate($pagesize,['*'],'page',$currentPage);	
		
		
		return view('/admin/ajaxpages/customerTable', compact('data'))->render();
    }
    public function editCustomer(Request $request,$recordid)
	{
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
		$issuper		= $request->session()->get('issuper');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		
		$state			=	DB::table('state_tbl')->orderby('statename')->get();
		$city			=	DB::table('city_tbl')->orderby('cityname')->get();
		$area			=	DB::table('area_tbl')->orderby('areaname')->get();
		
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
						->where('menuid','=',52)
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
		
	
		$data	=	DB::table('customer_tbl')->where('customerid','=',Crypt::decrypt($recordid))->first();
		
        return view('admin/master/customer_edit',compact('data','state','city','addstate','addcity','addarea'));
    }
    public function deleteCustomer($recordid)
	{
		$customer =	DB::table('customer_tbl')->where('customerid','=',Crypt::decrypt($recordid))->first();
		if($customer->profilepic!='')
		{
			Storage::disk('public')->delete($customer->profilepic);
		}	
		$res =	DB::delete('delete from customer_tbl WHERE customerid=?',[Crypt::decrypt($recordid)]);
		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/* CUSTOMER RELATED CLOSED */
}
