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

class StateController extends Controller
{
    public function addState(Request $request){
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addstate');
		Session::put('menid',6);

		$userId		= $request->session()->get('loginId');
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
        return view('admin/master/state_add');
    }
    public function addCity(Request $request){
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addcity');
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
		Session::put('menid',7);
		$userId		= $request->session()->get('loginId');
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
        return view('admin/master/city_add',compact('state'));
    }
    public function addArea(Request $request){
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addarea');
		Session::put('menid',8);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=8 and ispermitted=1 and userid=".$userId.") as actions"))
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

		$city	=	DB::table('city_tbl')->orderby('cityname')->get();
		$state	=	DB::table('state_tbl')->orderby('statename')->get();
        return view('admin/master/area_add',compact('city','state'));
    }
    public function addSlider(Request $request){        
		$displayorder = DB::table('slider_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addslider');
		Session::put('menid',9);
		$userId		= $request->session()->get('loginId');
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
        return view('admin/master/slider_add',compact('displayorder'));
    }
    public function addCategory(Request $request){        
		$category = DB::table('category_tbl')
					->where('parentcategoryid','=',0)
                    ->orderBy('displayorder')
                    ->get();
		$displayorder = DB::table('category_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','addcategory');

		Session::put('menid',13);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=13 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/category_add',compact('displayorder','category'));
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
		$userId		= $request->session()->get('loginId');
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

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
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

	/* VEHICLE BRAND */
    public function addVehicleBrand(Request $request){        
		$vehicletype	=	DB::table('vehicletype_tbl')->orderby('displayorder')->get();
		$displayorder = DB::table('vehiclebrand_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','vehiclebrand');

		Session::put('menid',11);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=11 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/vehiclebrand_add',compact('displayorder','vehicletype'));
    }
    public function storeVehicleBrand(Request $request,$recordid){        
        
        $rules = [
            'vehicletypeid' => 'required',
			'vehiclebrand' => 'required|max:30',
			'displayorder' => 'required|numeric',
			'icon' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
        ];

        $messages = [
            'vehicletypeid.required' => 'VEHICLE TYPE IS REQUIRED',
			'vehiclebrand.required' => 'VEHICLE BRAND IS REQUIRED',
			'vehiclebrand.max' => 'MAXIMUM LENGTH REACHED',
			'displayorder.required' => 'IT IS REQUIRED',
			'displayorder.numeric' => 'NUMERIC ONLY',
			'icon.required' => 'ICON IS MANDATORY',
			'icon.max' => 'MAX SIZE REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

        $vehicletypeid  = intval($request->input('vehicletypeid'));
		$vehiclebrand  	= strtoupper($request->input('vehiclebrand'));
		$displayorder 	=	intval($request->input('displayorder'));
		$icon    		= (String) $request->file('icon');

		if($recordid==0)
		{
			if($icon!='')
			{
				$icon	=	$request->file('icon')->store('uploads/vehiclebrands', 'public');
			}
			try 
			{
				$res = DB::insert('insert into vehiclebrand_tbl (vehicletypeid,vehiclebrand,displayorder,icon,creationdate,createdby,createdbyname) values (?,?,?,?,?,?,?)',[$vehicletypeid,$vehiclebrand,$displayorder,$icon,$creationdate,$userId,$userName]);

				return back()->with('success','VEHICLE BRAND STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				Storage::disk('public')->delete($icon);
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE BRAND IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('vehiclebrand_tbl')->where('vehiclebrandid',$recordid)->first();
			if($icon!='')
			{
				if ($data->icon!='') {
					Storage::disk('public')->delete($data->icon);
				}
				$icon= $request->file('icon')->store('uploads/vehiclebrands', 'public');
			}
			$updateData = [];
			if(!empty($vehicletypeid))
			{
				$updateData['vehicletypeid'] = $vehicletypeid;
			}
			if(!empty($vehiclebrand))
			{
				$updateData['vehiclebrand'] = $vehiclebrand;
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
				$newDisplayOrder = intval($request->input('displayorder'));
				$oldDisplayOrder = $data->displayorder;

				if($newDisplayOrder!=$oldDisplayOrder)
				{
					DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder, $recordid) {
					if($newDisplayOrder>$oldDisplayOrder)
					{
						DB::table('vehiclebrand_tbl')
							->where('displayorder', '>', $oldDisplayOrder)
							->where('displayorder', '<=', $newDisplayOrder)
							->where('vehiclebrandid', '!=', $recordid)
							->decrement('displayorder');
					}
					else
					{
						DB::table('vehiclebrand_tbl')
							->where('displayorder', '>=', $newDisplayOrder)
							->where('displayorder', '<', $oldDisplayOrder)
							->where('vehiclebrandid', '!=', $recordid)
							->increment('displayorder');
					}
					});				
				}
				
				$res = DB::table('vehiclebrand_tbl')->where('vehiclebrandid', $recordid)->update($updateData);
				return redirect('master/add/vehiclebrand')->with('success','VEHICLE BRAND DETAIL UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if($icon!='')
				Storage::disk('public')->delete($icon);

				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE BRAND IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getVehicleBrandData(Request $request)
	{
		$vehicletypeid 	=	intval($request->input('vehicletypeid'));
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('vehiclebrand_tbl as a')
				->select('a.*','b.vehicletype')
				->leftjoin('vehicletype_tbl as b','b.vehicletypeid','=','a.vehicletypeid')
                ->when($vehicletypeid!=0,function($query) use ($vehicletypeid){
                    return $query->where('a.vehicletypeid','=',$vehicletypeid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.vehiclebrand','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/vehiclebrandTable', compact('data'))->render();
    }
    public function editVehicleBrand($recordid)
	{
		$vehicletype	=	DB::table('vehicletype_tbl')->orderby('displayorder')->get();
        $data = DB::table('vehiclebrand_tbl')->where('vehiclebrandid','=',$recordid)->first();
        return view('admin/master/vehiclebrand_edit',compact('data','vehicletype'));
    }
    public function deleteVehicleBrand($recordid)
	{
		$content = DB::table('vehiclebrand_tbl')->where('vehiclebrandid','=',$recordid)->first();
		if($content->icon!='')
		{
			Storage::disk('public')->delete($content->icon);
		}
		$res = DB::delete('delete from vehiclebrand_tbl WHERE vehiclebrandid=?',[$recordid]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/* VEHICLE BRAND */

	/* VEHICLE TYPE */
    public function addVehicleType(Request $request){        
		$displayorder = DB::table('vehicletype_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','vehicletype');
		Session::put('menid',10);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=10 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/vehicletype_add',compact('displayorder'));
    }
    public function storeVehicleType(Request $request,$recordid){        
        
        $rules = [
            'vehicletype' => 'required|max:30',
			'displayorder' => 'required|numeric',
			'icon' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
        ];

        $messages = [
            'vehicletype.required' => 'VEHICLE TYPE IS REQUIRED',
			'vehicletype.max' => 'MAXIMUM LENGTH REACHED',
			'displayorder.required' => 'IT IS REQUIRED',
			'displayorder.numeric' => 'NUMERIC ONLY',
			'icon.required' => 'ICON IS MANDATORY',
			'icon.max' => 'MAX SIZE REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

        $vehicletype  = strtoupper($request->input('vehicletype'));
		$displayorder =	intval($request->input('displayorder'));
		$icon    	= (String) $request->file('icon');

		if($recordid==0)
		{
			if($icon!='')
			{
				$icon	=	$request->file('icon')->store('uploads/vehicletypes', 'public');
			}
			try 
			{
				$res = DB::insert('insert into vehicletype_tbl (vehicletype,displayorder,icon,creationdate,createdby,createdbyname) values (?,?,?,?,?,?)',[$vehicletype,$displayorder,$icon,$creationdate,$userId,$userName]);

				return back()->with('success','VEHICLE TYPE STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				Storage::disk('public')->delete($icon);
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE TYPE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('vehicletype_tbl')->where('vehicletypeid',$recordid)->first();
			if($icon!='')
			{
				if ($data->icon!='') {
					Storage::disk('public')->delete($data->icon);
				}
				$icon= $request->file('icon')->store('uploads/vehicletypes', 'public');
			}
			$updateData = [];
			if(!empty($vehicletype))
			{
				$updateData['vehicletype'] = $vehicletype;
			}
			if(!empty($displcyorder))
			{
				$updateData['displcyorder'] = $displcyorder;
			}
			if(!empty($icon))
			{
				$updateData['icon'] = $icon;
			}
			try
			{
				$newDisplayOrder = intval($request->input('displayorder'));
				$oldDisplayOrder = $data->displayorder;

				if($newDisplayOrder!=$oldDisplayOrder)
				{
					DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder, $recordid) {
					if($newDisplayOrder>$oldDisplayOrder)
					{
						DB::table('vehicletype_tbl')
							->where('displayorder', '>', $oldDisplayOrder)
							->where('displayorder', '<=', $newDisplayOrder)
							->where('vehicletypeid', '!=', $recordid)
							->decrement('displayorder');
					}
					else
					{
						DB::table('vehicletype_tbl')
							->where('displayorder', '>=', $newDisplayOrder)
							->where('displayorder', '<', $oldDisplayOrder)
							->where('vehicletypeid', '!=', $recordid)
							->increment('displayorder');
					}
					});				
				}
				
				$res = DB::table('vehicletype_tbl')->where('vehicletypeid', $recordid)->update($updateData);
				return redirect('master/add/vehicletype')->with('success','VEHICLE TYPE DETAIL UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if($icon!='')
				Storage::disk('public')->delete($icon);

				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE TYPE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getVehicleTypeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('vehicletype_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('vehicletype','like','%'.$pagesearch.'%');
                })
                ->orderBy('displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/vehicletypeTable', compact('data'))->render();
    }
    public function editVehicleType($recordid)
	{
        $data = DB::table('vehicletype_tbl')->where('vehicletypeid','=',$recordid)->first();
        return view('admin/master/vehicletype_edit',compact('data'));
    }
    public function deleteVehicleType($recordid)
	{
		$content = DB::table('vehicletype_tbl')->where('vehicletypeid','=',$recordid)->first();
		if($content->icon!='')
		{
			Storage::disk('public')->delete($content->icon);
		}
		$res = DB::delete('delete from vehicletype_tbl WHERE vehicletypeid=?',[$recordid]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
		}
    }
	/* VEHICLE TYPE */
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
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
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
    public function storeCategory(Request $request,$recordid){        
        
        $rules = [
            'category' => 'required|max:50',
            'displayorder' => 'required',
			'categoryicon' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
			'categorypage' => ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',			
        ];

        $messages = [
            'category.required' => 'CATEGORY NAME IS REQUIRED',
            'category.max' => 'MAXIMUM LENGTH IS 50',
            'displayorder.required' => 'REQUIRED',
			'categoryicon.required' => 'CATEGORY ICON IS REQUIRED',
			'categorypage.required' => 'CATEGORY PAGE IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $categoryid   	= intval($request->input('categoryid'));
		$category   	= strtoupper($request->input('category'));
        $displayorder 	= intval($request->input('displayorder'));
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$categoryicon    = (String) $request->file('categoryicon');		
		$categorypage    = (String) $request->file('categorypage');		
		$icontype = "";
		
		if($recordid==0)
		{
			if($categoryicon!='')
			{
				$categoryicon= $request->file('categoryicon')->store('uploads/categoryicons', 'public');
			}

			if($categorypage!='')
			{
				$categorypage= $request->file('categorypage')->store('uploads/categorypage', 'public');
			}
			
			$res = DB::insert('INSERT INTO category_tbl (parentcategoryid,category,displayorder,categoryicon,categorypage,icontype,pagetype,creationdate,createdbyid,createdbyname) VALUES (?,?,?,?,?,?,?,?,?,?)', [$categoryid,$category,$displayorder,$categoryicon,$categorypage,'IMAGE','IMAGE',$creationdate,$userId,$userName]);

			if($res)
			{
				return back()->with('success','CATEGORY DETAIL STORED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','CATEGORY DETAIL COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$cont = DB::table('category_tbl')->where('categoryid',$recordid)->first();
			if($categoryicon!='')
			{
				if ($cont->categoryicon!='') {
					Storage::disk('public')->delete($cont->categoryicon);
				}
				$categoryicon= $request->file('categoryicon')->store('uploads/categoryicons', 'public');
			}
			if($categorypage!='')
			{
				if ($cont->categorypage!='') {
					Storage::disk('public')->delete($cont->categorypage);
				}
				$categorypage= $request->file('categorypage')->store('uploads/categorypage', 'public');
			}

			$updateData = [];
			if(!empty($categoryid)) {
				$updateData['parentcategoryid'] = $categoryid;
			}
			if(!empty($category)) {
				$updateData['category'] = $category;
			}
			if(!empty($displayorder)) {
				$updateData['displayorder'] = $displayorder;
			}
			if(!empty($categoryicon)) {
				$updateData['categoryicon'] = $categoryicon;
			}
			if(!empty($categorypage)) {
				$updateData['categorypage'] = $categorypage;
			}
			$res = DB::table('category_tbl')->where('categoryid', $recordid)->update($updateData);
			if($res)
			{
				return redirect('master/add/category')->with('success','CATEGORY DETAIL UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','CATEGORY DETAIL COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
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

        $userId         = $request->session()->get('loginId');
        $userName       = $request->session()->get('userId');
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

    public function storeArea(Request $request,$recordid)
	{        
        $rules = [
            'cityid' => 'required',
			'areaname' => 'required|max:50',
			'pincode' => 'required|max:10',
        ];

        $messages = [
            'cityid.required' => 'STATE NAME IS REQUIRED',
            'areaname.required' => 'AREA NAME IS REQUIRED',
            'areaname.unique' => 'DUPLICATE AREA NAME FOUND',
            'areaname.max' => 'MAXIMUM LENGTH IS 50',
			'pincode.required' => 'PIN CODE IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			$cityid		=	strtoupper($request->input('cityid'));
			$stateid 	= DB::table('city_tbl')->where('cityid',$cityid)->value('stateid');
			$areaname	=	strtoupper($request->input('areaname'));
			$pincode	=	(String) $request->input('pincode');
			$res = DB::insert('INSERT INTO area_tbl(stateid,cityid,areaname,pincode,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?,?)',[$stateid,$cityid,$areaname,$pincode,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','AREA NAME STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','AREA NAME COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$cityid		=	strtoupper($request->input('cityid'));
			$stateid 	= 	DB::table('city_tbl')->where('cityid',$cityid)->value('stateid');
			$areaname	=	strtoupper($request->input('areaname'));
			$pincode	=	(String) $request->input('pincode');
			$res = DB::update('update area_tbl set stateid=?,cityid=?,areaname=?,pincode=? where areaid=?',[$stateid,$cityid,$areaname,$pincode,$recordid]);

			if($res)
			{
				//return back()->with('success','STATE NAME UPDATED SUCCESSFULLY!');
				return redirect('/master/add/area')->with('success','AREA NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','AREA NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }

    public function storeCity(Request $request,$recordid)
	{        
        $rules = [
            'stateid' => 'required',
			'cityname' => 'required|max:50',
        ];

        $messages = [
            'stateid.required' => 'STATE NAME IS REQUIRED',
            'cityname.required' => 'CITY NAME IS REQUIRED',
            'cityname.unique' => 'DUPLICATE CITY NAME FOUND',
            'cityname.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			$stateid	=	strtoupper($request->input('stateid'));
			$cityname	=	strtoupper($request->input('cityname'));
			$res = DB::insert('INSERT INTO city_tbl(stateid,cityname,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)',[$stateid,$cityname,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','CITY NAME STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','CITY NAME COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$stateid	=	strtoupper($request->input('stateid'));
			$cityname	=	strtoupper($request->input('cityname'));
			$res = DB::update('update city_tbl set stateid=?,cityname=? where cityid=?',[$stateid,$cityname,$recordid]);

			if($res)
			{
				//return back()->with('success','STATE NAME UPDATED SUCCESSFULLY!');
				return redirect('/master/add/city')->with('success','CITY NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','CITY NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }

    public function storeState(Request $request,$recordid)
	{        
        $rules = [
            'statename' => 'required|max:50',
        ];

        $messages = [
            'statename.required' => 'STATE NAME IS REQUIRED',
            'statename.unique' => 'DUPLICATE STATE NAME FOUND',
            'statename.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		if($recordid==0)
		{
			$statename	=	strtoupper($request->input('statename'));
			$res = DB::insert('INSERT INTO state_tbl(statename,createdby,createdbyname,creationdate) VALUES (?,?,?,?)', [$statename,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','STATE NAME STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','STATE NAME COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$statename	=	strtoupper($request->input('statename'));
			$res = DB::update('update state_tbl set statename=? where stateid=?',[$statename,$recordid]);

			if($res)
			{
				//return back()->with('success','STATE NAME UPDATED SUCCESSFULLY!');
				return redirect('/master/add/state')->with('success','STATE NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','STATE NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }
    public function getStateData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('state_tbl')
                ->orderBy('statename')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('statename','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/stateTable', ['data' => $data]);

    }
    public function getCityData(Request $request)
	{
		$stateid 	=	intval($request->input('stateid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('city_tbl as a')
				->select('a.cityid','a.cityname','a.createdbyname','a.creationdate','b.statename')
				->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.cityname','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.cityname')
				->paginate($pagesize,['*'],'page',$currentPage);
		
        //return view('admin/ajaxpages/stateTable', compact('data','pagesize'))->render();
		return view('admin/ajaxpages/cityTable', ['data' => $data]);

    }
    public function getAreaData(Request $request)
	{
		$cityid 	=	intval($request->input('cityid'));
		$stateid 	=	intval($request->input('stateid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('area_tbl as a')
				->select('a.areaid','a.areaname','a.pincode','a.createdbyname','a.creationdate','b.cityname')
				->leftjoin('city_tbl as b','b.cityid','=','a.cityid')
                ->when($cityid!=0,function($query) use ($cityid){
                    return $query->where('a.cityid','=',$cityid);
                })
                ->when($stateid!=0,function($query) use ($stateid){
                    return $query->where('a.stateid','=',$stateid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.areaname','like','%'.$pagesearch.'%')
									->orwhere('a.pincode','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.areaname')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('admin/ajaxpages/areaTable', ['data' => $data]);
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
    public function getCategoryData(Request $request)
	{
		$parentcategoryid =	intval($request->input('parentcategoryid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('category_tbl as a')
				->select('a.*','b.category as parentcategory')
				->leftjoin('category_tbl as b','b.categoryid','=','a.parentcategoryid')
                ->when($parentcategoryid!=0,function($query) use ($parentcategoryid){
                    return $query->where('a.parentcategoryid','=',$parentcategoryid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('a.category','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.displayorder')
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/categoryTable', compact('data'))->render();
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

    public function editState($recordid)
	{
        $data = DB::table('state_tbl')->where('stateid','=',$recordid)->first();
        return view('admin/master/state_edit',compact('data'));
    }
    public function editCity($recordid)
	{
        $state	=	DB::table('state_tbl')->orderby('statename')->get();
		$data = DB::table('city_tbl')->where('cityid','=',$recordid)->first();
        return view('admin/master/city_edit',compact('data','state'));
    }
    public function editArea($recordid)
	{
        $city	=	DB::table('city_tbl')->orderby('cityname')->get();
		$data = DB::table('area_tbl')->where('areaid','=',$recordid)->first();
        return view('admin/master/area_edit',compact('data','city'));
    }
    public function editSlider($recordid){

        $data = DB::table('slider_tbl')->where('sliderid','=',$recordid)->first();
        return view('admin/master/slider_edit',compact('data'));
    }
    public function editCategory($recordid)
	{
		$category = DB::table('category_tbl')
					->where('parentcategoryid','=',0)
                    ->orderBy('displayorder')
                    ->get();
		
        $data = DB::table('category_tbl')->where('categoryid','=',$recordid)->first();
        return view('admin/master/category_edit',compact('data','category'));

    }
    public function editSubCategory($recordid)
	{
		$category	=	DB::table('category_tbl')->orderby('category')->get();
        $data = DB::table('subcategory_tbl')->where('subcategoryid','=',$recordid)->first();
        return view('admin/master/subcategory_edit',compact('data','category'));
    }

    public function deleteState($recordid)
	{
        $res = DB::delete('DELETE FROM state_tbl WHERE stateid=?', [$recordid]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }

    public function deleteCity($recordid)
	{
        $res = DB::delete('DELETE FROM city_tbl WHERE cityid=?', [$recordid]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
    public function deleteArea($recordid)
	{
        $res = DB::delete('DELETE FROM area_tbl WHERE areaid=?', [$recordid]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

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
    public function deleteCategory($recordid)
	{
		$content = DB::table('category_tbl')->where('categoryid','=',$recordid)->first();
		if($content->categoryicon!='')
		{
			Storage::disk('public')->delete($content->categoryicon);
		}
		if($content->categorypage!='')
		{
			Storage::disk('public')->delete($content->categorypage);
		}
		
		$res = DB::delete('delete from category_tbl WHERE categoryid=?',[$recordid]);

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
}
