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

class CouponsController extends Controller
{
    public function addCoupon(Request $request)
	{        
        Session::put('adminmenu','coupons');
		Session::put('adminsubmenu','addcoupon');

		Session::put('menid',48);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=48 and ispermitted=1 and userid=".$userId.") as actions"))
							->groupby('menuid')
							->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		else
		{
			$action 	= DB::table('menu_action')->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_action) as actions"))->first();
			$actions	=	explode(",",$action->actions);
			Session::put('actions', $actions);
		}
		
		$category	=	DB::table('category_tbl')
							->select('categoryid','category')
							->where('parentcategoryid','=',0)
							->orderby('displayorder')
							->get();
		
		foreach($category as $cat)
		{
			$cat->subcategory	=	DB::table('category_tbl')
										->where('parentcategoryid','=',$cat->categoryid)
										->orderby('displayorder')
										->get();
		}
							
		
        return view('admin/master/coupon_add',compact('category'));
    }
    public function storeCoupon(Request $request,$recordid){        
        
        $rules = [
			'couponcode'			=> 'required',
            'discounttype' 			=> 'required',
			'discountvalue'  		=> 'required|numeric',
			'minordervalue' 		=> 'nullable|numeric',
			'maxdiscount' 			=> 'required|numeric',
			'startdate'				=> 'required|date',
			'expirydate'			=> 'required|date',
			'usagelimitperuser'		=> 'nullable|numeric',
			'totalusagelimit'		=> 'nullable|numeric',
			'ordereligibility'		=> 'nullable',
			'applicablesubcategory'	=> 'nullable',
        ];

        $messages = [
			'couponcode.required'		=> 'COUPON CODE IS REQUIRED',
			'discounttype.required'		=> 'TYPE IS REQUIRED',
			'discountvalue.required'	=> 'VALUE IS REQUIRED',
			'minordervalue.numeric'		=> 'INVALID VALUE',
			'maxdiscount.numeric'		=> 'INVALID VALUE',
			'startdate.required'		=> 'START DATE IS REQUIRED',
			'startdate.date'			=> 'INVALID START DATE',
			'expirydate.required'		=> 'EXPIRY IS REQUIRED',
			'expirydate.date'			=> 'INVALID EXPIRY DATE',
			'usagelimitperuser.numeric'	=> 'INVALID VALUE',
			'totalusagelimit.numeric'	=> 'INVALID VALUE',
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      		= $request->session()->get('loginId');
        $userName      		= $request->session()->get('userId');
        $currentDateTime	= now();
        $creationdate  		= $currentDateTime->format('Y-m-d H:i:s');

		$couponcode				= (String) ($request->input('couponcode'));
		$discounttype			= (String) ($request->input('discounttype'));
		$discountvalue			= doubleval($request->input('discountvalue'));
		$minordervalue			= doubleval($request->input('minordervalue'));
		$maxdiscount			= doubleval($request->input('maxdiscount'));
		$startdate				= (String) ($request->input('startdate'));
		$expirydate				= (String) ($request->input('expirydate'));
		$usagelimitperuser		= intval($request->input('usagelimitperuser'));
		$totalusagelimit		= intval($request->input('totalusagelimit'));
		$ordereligibility		= (String) ($request->input('ordereligibility'));
		$applicablesubcategory	= $request->input('applicablesubcategory');
		$subcategories			=	"";
		if($request->has('applicablesubcategory'))
		{
			$applicablesubcategory = $request->input('applicablesubcategory', []);
			$subcategories	=	implode(",",$applicablesubcategory);
		}
		
		if($recordid==0)
		{
			try 
			{
				DB::insert('insert into coupon_tbl (couponcode,discounttype,discountvalue,minordervalue,maxdiscount,startdate,expirydate,usagelimitperuser,totalusagelimit,ordereligibility,applicablesubcategory,createdby,creationdate) values (?,?,?,?,?,?,?,?,?,?,?,?,?)',[$couponcode,$discounttype,$discountvalue,$minordervalue,$maxdiscount,$startdate,$expirydate,$usagelimitperuser,$totalusagelimit,$ordereligibility,$subcategories,$userId,$creationdate]);
				
				return back()->with('success','COUPON CODE STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{		
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{			
			$updateData = [];
			if(!empty($couponcode))
			{
				$updateData['couponcode'] = $couponcode;
			}
			if(!empty($discounttype))
			{
				$updateData['discounttype'] = $discounttype;
			}
			if(!empty($discountvalue))
			{
				$updateData['discountvalue'] = $discountvalue;
			}
			if(!empty($minordervalue))
			{
				$updateData['minordervalue'] = $minordervalue;
			}
			if(!empty($maxdiscount))
			{
				$updateData['maxdiscount'] = $maxdiscount;
			}
			if(!empty($startdate))
			{
				$updateData['startdate'] = $startdate;
			}
			if(!empty($expirydate))
			{
				$updateData['expirydate'] = $expirydate;
			}
			if(!empty($usagelimitperuser))
			{
				$updateData['usagelimitperuser'] = $usagelimitperuser;
			}
			if(!empty($totalusagelimit))
			{
				$updateData['totalusagelimit'] = $totalusagelimit;
			}
			if(!empty($ordereligibility))
			{
				$updateData['ordereligibility'] = $ordereligibility;
			}
			if(!empty($subcategories))
			{
				$updateData['applicablesubcategory'] = $subcategories;
			}
			try
			{		
				DB::table('coupon_tbl')->where('couponid', $recordid)->update($updateData);
				return redirect('master/add/coupon')->with('success','COUPON DETAIL UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
			
		}
    }
    public function getCouponData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage= $request->input('page', 1);
		$disctype	=	$request->input('disctype');
		$eligibility=	$request->input('eligibility');

        $data = DB::table('coupon_tbl as a')
				->select('a.*',DB::raw("(SELECT GROUP_CONCAT('<li>',category ORDER BY category SEPARATOR '</li>') FROM category_tbl WHERE FIND_IN_SET(categoryid, a.applicablesubcategory)) as subcats"))
                ->when($disctype!=0,function($query) use ($disctype){
                    return $query->where('a.discounttype','=',$disctype);								 
                })
                ->when($eligibility!=0,function($query) use ($eligibility){
                    return $query->where('a.ordereligibility','=',$eligibility);								 
                })
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('a.couponcode','like','%'.$pagesearch.'%');								 
                })
				->orderBy('a.startdate')
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/couponTable', ['data' => $data]);

    }
    public function editCoupon($recordid)
	{
		$category	=	DB::table('category_tbl')
							->select('categoryid','category')
							->where('parentcategoryid','=',0)
							->orderby('displayorder')
							->get();
		
		foreach($category as $cat)
		{
			$cat->subcategory	=	DB::table('category_tbl')
										->where('parentcategoryid','=',$cat->categoryid)
										->orderby('displayorder')
										->get();
		}
		
		$data = DB::table('coupon_tbl')->where('couponid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/coupon_edit',compact('data','category'));
    }
	
}

