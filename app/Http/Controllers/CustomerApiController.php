<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use App\Services\SmsService;
use App\Helpers\JWTAuthHelper;
//use App\Events\OrderCreated;

class CustomerApiController extends Controller
{
	protected $smsService;
	protected $appUrl;
	public function __construct(SmsService $smsService)
	{
		$this->appUrl 		=	Config::get('app.url');
		$this->smsService 	=	$smsService;
	}
	private function getDiscount()
	{
		$tierId 	= DB::table('city_tbl')->where('isdefault',1)->value('tierid');
		$discount 	= DB::table('tier_tbl')->where('tierid','=',$tierId)->value('discount');
		return $discount;
	}
	
	private function getIpDetail($ipaddress)
	{
		$ipdata = [];
		$response 		= Http::get("http://ip-api.com/json/{$ipaddress}");
		if($response->successful())
		{
			$ipdata = $response->json();			
		}
		else
		{
			$ipdata['country']		=	"";
			$ipdata['countryCode']	=	"";
			$ipdata['region']		=	"";
			$ipdata['regionName']	=	"";
			$ipdata['city']			=	"";
			$ipdata['zip']			=	"";
			$ipdata['lat']			=	"";
			$ipdata['lon']			=	"";
			$ipdata['timezone']		=	"";
			$ipdata['isp']			=	"";			
			$ipdata['org']			=	"";			
		}
		return $ipdata;
	}

	private function getCartList($customerid)
	{
		ini_set('serialize_precision',-1);
		$subcategoryid	= 	DB::table('customer_cart')
									->where('customerid',$customerid)
									->distinct()
									->pluck('categoryid')
									->first();											

		
		$ordcart		=	DB::table('customer_cart_order')
							->select('customerid','visitingcharge','visitingtax')
							->where('customerid','=',$customerid)
							->first();
		
		$ordercart		=	DB::table('customer_cart_order')
							->select('customerid','servicecharge','visitingcharge','totaltaxable','totaltaxvalue','netpayable','couponcode','cartitems')
							->where('customerid','=',$customerid)
							->first();
		$flag			=	0;
		$coupondiscount	=	0;
		$couponPercent	=	0;
		$coupondiscamt	=	0;
		if($ordercart)
		{
			if($ordercart->couponcode!='')
			{
				$coupon	=	DB::table('coupon_tbl')
								->where('couponcode','=',$ordercart->couponcode)
								->whereRaw("STR_TO_DATE(expirydate,'%Y-%m-%d')>NOW()")
								->first();
								
				if(!is_null($coupon))
				{
					if($coupon->ordereligibility=='FIRST')
					{
						$count = DB::table('customer_order')->where('couponcode','=',$ordercart->couponcode)->where('customerid','=',$customerid)->count();
						if($count>0)
						{
							$flag++;
						}
					}
					if($coupon->totalusagelimit!=0 && $flag==0)
					{
						$count = DB::table('customer_order')->where('couponcode','=',$ordercart->couponcode)->count();
						if($count>$coupon->totalusagelimit)
						{
							$flag++;
						}
					}
					if(($ordercart->totaltaxable<$coupon->minordervalue) && $flag==0)
					{
						$flag++;
					}
					
					if(($coupon->discounttype=='PERCENTAGE') && $flag==0)
					{
						$coupondiscount	=	ceil(($ordercart->totaltaxable*$coupon->discountvalue)/100);
						if($coupondiscount>$coupon->maxdiscount)
						{
							$coupondiscount	=	$coupon->maxdiscount;
						}
					}
					else
					{
						if($flag==0)
						$coupondiscount	=	$coupon->discountvalue;
						else
						$coupondiscount=0;
					}

					$couponPercent	=	round(($coupondiscount * 100) /$ordercart->totaltaxable, 2);
					if($couponPercent>$coupon->discountvalue)
					{
						$couponPercent	=	$coupon->discountvalue;
					}
				}
				else
				{
					$code	=	"";
					DB::update('update customer_cart_order set couponcode=? where customerid=? and couponcode=?',[$code,$customerid,$couponcode]);
				}
			}
					
			$discount		=	$this->getDiscount();
			
			$cartlist		=	DB::table('customer_cart')
									->select('customerid','categoryid','serviceid','optionid','quantity','servicecharge','taxable','taxvalue','payable')
									->where('customerid','=',$customerid)
									->orderby('serviceid')
									->get();
			$totaltaxable	=	0;				
			$grandtotal		=	0;
			$totaltaxvalue	=	0;
			$catid			=	0;
			foreach($cartlist as $cart)
			{
				$services	=	DB::table('services as a')
									->select('a.categoryid','a.serviceid','a.optionid','a.servicetitle','a.mrp as servicecharge',DB::raw('0 as taxable'),'a.servicepic','a.likes','a.ratings','a.reviews','a.requiredtime','a.description','b.taxrate')
									->leftjoin('tax_tbl as b','b.taxid','=','a.taxid')
									->where('a.categoryid','=',$cart->categoryid)
									->where('a.serviceid','=',$cart->serviceid)
									->where('a.optionid','=',$cart->optionid)
									->first();
				
				$cart->servicepic 		= 	$this->appUrl."/storage/".$services->servicepic;

				$cart->requiredtime		=	$services->requiredtime;
				$cart->ratings			=	$services->ratings;
				$cart->likes			=	$services->likes;
				$services->taxable		=	$services->servicecharge-(($services->servicecharge*$discount)/100);
				if($couponPercent!=0)
				{
					$coupondiscamt		=	round($coupondiscamt+(($services->taxable*$cart->quantity*$couponPercent)/100));					
					$services->taxable	=	$services->taxable-(($services->taxable*$couponPercent)/100);
				}
				$cart->servicecharge	=	$services->servicecharge*$cart->quantity;
				$cart->taxable			=	number_format($services->taxable*$cart->quantity,'2','.','');
				$cart->taxable			=	doubleval($cart->taxable);
				$cart->servicetitle		=	$services->servicetitle;
				
				$totaltaxable			=	$totaltaxable+$cart->taxable;
				$cart->taxvalue			=	number_format(($cart->taxable*$services->taxrate)/100,'2','.','');
				$totaltaxvalue			=	$totaltaxvalue+$cart->taxvalue;
				$cart->payable			=	number_format($cart->taxable+$cart->taxvalue,'2','.','');
				
				$cart->taxvalue			=	doubleval($cart->taxvalue);
				$cart->payable			=	doubleval($cart->payable);
				
				$catid					=	$services->categoryid;
			}
			$totaltaxable				=	number_format($totaltaxable,'2','.','');
			$totaltaxvalue				=	number_format($totaltaxvalue,'2','.','');
			$grandtotal					=	number_format($totaltaxable+$totaltaxvalue,'2','.','');
			
			$ordercart->totaltaxable	=	number_format($totaltaxable,'2','.','');
			$ordercart->totaltaxable	=	doubleval($ordercart->totaltaxable);

			$ordercart->totaltaxvalue	=	doubleval(bcdiv($totaltaxvalue+$ordcart->visitingtax,1,2));
			$ordercart->netpayable		=	(float) round($ordercart->totaltaxable+$ordercart->totaltaxvalue+$ordercart->visitingcharge,2);
			
			
			$ordercart->couponpercent	=	$couponPercent;
			$ordercart->visitingcharge	=	$ordcart->visitingcharge;
			$ordercart->cartlist		=	$cartlist;
			
			$ordercart->parentcategoryid=	DB::table('category_tbl')->where('categoryid','=',$catid)->value('parentcategoryid');
			$ordercart->subcategoryid	=	$subcategoryid;
			
			json_encode($ordercart, JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE);
			
			if($coupondiscamt!=0)
			{
				DB::update('update customer_cart_order set coupondiscount=?,couponpercent=?,coupdisc=? where customerid=?',[$coupondiscamt,$couponPercent,$coupondiscount,$customerid]);
			}
		}
		else
		{
			$ordercart	=	[];
		}
		return $ordercart;
	}
	
	private function CheckCustomer($customerid,$accesstoken)
	{
		$customer	=	DB::table('customer_tbl')
						->where('customerid','=',$customerid)
						->where('accesstoken','=',$accesstoken)
						->first();
		return $customer;
	}
	function GetVendorsInRadius($orderid,$radius)
	{
		$order		=	DB::table('customer_order')->where('orderid','=',$orderid)->first();
		$address	=	DB::table('customer_address')->where('addressid','=',$order->addressid)->first();
		
		$latitude	=	$address->latitude;
		$longitude	=	$address->longitude;
		/*
		$vendors 	= DB::table('vendor_tbl')
						->select('vendorid', 'latitude', 'longitude', DB::raw('(6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance',[doubleval($address->latitude),doubleval($address->longitude),doubleval($address->latitude)]))
						->havingRaw('distance <= ?', [$radius])
						->orderBy('distance', 'asc')
						->get();
		
		*/
		
		$vendor = DB::table('vendor_tbl')
						->select('vendorid','parentvendorid','name','latitude','longitude','isemployee','verificationstatus','isselfemployeed',DB::raw("
						(6371*acos(cos(radians($latitude))*cos(radians(latitude))*cos(radians(longitude)-radians($longitude))+sin(radians($latitude))*sin(radians(latitude)))) AS distance",[$latitude, $longitude, $latitude]))
						->havingRaw('distance <= ?', [$radius])
						->where(function($query) {
							$query->where('verificationstatus', '=', 1)
								  ->where('isemployee', '=', 1);
						})
						->orWhere(function($query) {
							$query->where('isselfemployeed', '=', 1);
						})
						->get();
		
				
		$vendorIds 	= $vendor->pluck('vendorid')->toArray();
		
		$vendors	= DB::table('vendor_tbl')
						->select('vendorid','name','isemployee','verificationstatus','isselfemployeed')						
						->where(function($query) use($vendorIds) {
							$query->whereIn('parentvendorid',$vendorIds)
									->where('verificationstatus','=',1)
									->where('isemployee','=',0);
						})						
						->orwhere(function($query) use($vendorIds) {
							$query->whereIn('vendorid',$vendorIds)
									->where('verificationstatus','=',1)
									->where('isemployee','=',0);
						})						
						->get();
		
		if(!$vendors)
		{
			return [];
		}
		return $vendors;
	}
    public function masterCategoryList(Request $request)
	{
		try
		{
			$category = DB::table('category_tbl')
							->select('categoryid','category')
							->where('parentcategoryid','=',0)
							->orderby('displayorder')
							->get();

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'categotylist'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			
    }
    public function subCategoryList(Request $request)
	{
        $rules = [
            'categoryids'  		=> 'required',
        ];

        $messages = [
            'categoryids.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$categoryids		= (String) $request->input('categoryids');
		$categoryIdsArray 	= explode(',', $categoryids);
		try
		{
			$category = DB::table('category_tbl')
							->select('categoryid as subcategoryid','category as subcategory')
							->whereIn('parentcategoryid', $categoryIdsArray)
							->get();

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'subcategotylist'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			
    }

    public function categoryList(Request $request)
	{
        $rules = [
            'categoryid'  		=> 'nullable|numeric',
        ];

        $messages = [
            'categoryid.required' 	=> __('validation.thisis.required'),
			'categoryid.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$categoryid		=	intval($request->input('categoryid'));
		$searchtext		=	(String) $request->input('searchtext');
		
		
		try
		{
			$parentname	=	"";
			if($categoryid!='' && $categoryid!=0)
			{
				$parent		=	DB::table('category_tbl')->select('category')->where('categoryid','=',$categoryid)->first();
				if($parent)
				$parentname	=	$parent->category;
				else
				$categoryid=0;
			}
		
			$category = DB::table('category_tbl')
							->select('categoryid','category','headingvalue','displayorder','description','metakeywords','metadescription','categoryicon','categorypage')
							->when($categoryid==0,function($query) use ($categoryid){
								return $query->where('parentcategoryid','=',0);
							})
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('parentcategoryid','=',$categoryid);
							})
							->when($searchtext!='',function($query) use ($searchtext){
								return $query->where('category','like','%'.$searchtext.'%');
							})
							->where('categorystatus','=',1)
							->orderBy('displayorder')
							->get();
			
			foreach($category as $cat)
			{
				if($cat->categoryicon!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categoryicon = $appUrl."/storage/".$cat->categoryicon;
				}
				if($cat->categorypage!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categorypage = $appUrl."/storage/".$cat->categorypage;
				}

				$hasChild 		= DB::table('category_tbl')
									->where('parentcategoryid', $cat->categoryid)
									->exists();
				$cat->haschild 	= $hasChild ? 1 : 0;
				
				$service		=	DB::table('service_tbl')
									->select('serviceid','servicetitle','visitingcharge','mrp','requiredtime','description','hsncode')
									->where('categoryid','=',$cat->categoryid)
									->get();
									
				$cat->services	=	$service;
			}

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'parentname'=>$parentname,'categotylist'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			

    }
	
    public function cityList(Request $request)
	{
		
		try
		{
			$stateid		=	intval($request->input('stateid'));
			$searchtext		=	(String) $request->input('searchtext');
			
			$citylist 		= 	DB::table('city_tbl')
									->select('cityid','cityname')
									->when($stateid!=0,function($query) use ($stateid){
										return $query->where('stateid','=',$stateid);
									})
									->when($searchtext!='',function($query) use ($searchtext){
										return $query->where('cityname','like','%'.$searchtext.'%');
									})
									->orderBy('cityname')
									->get();

			return response()->json(['message' =>'CITY LIST','status'=>200,'data'=>$citylist], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>__('messages.notfound'),'status'=>400], 400);
		}			
    }

    public function areaList(Request $request)
	{
		
		try
		{
			$cityid		=	intval($request->input('cityid'));
			$searchtext	=	(String) $request->input('searchtext');
			
			$arealist = DB::table('area_tbl')
						->select('areaid','areaname')
						->when($cityid!=0,function($query) use ($cityid){
							return $query->where('cityid','=',$cityid);
						})
						->when($searchtext!='',function($query) use ($searchtext){
							return $query->where('areaname','like','%'.$searchtext.'%');
						})
						->orderBy('areaname')
						->get();

			return response()->json(['message' =>'AREA LIST','status'=>200,'data'=>$arealist], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>__('messages.notfound'),'status'=>400], 400);
		}			
    }

    public function stateList(Request $request)
	{        
	
		try
		{
			$statelist = DB::table('state_tbl')->select('stateid','statename','aliasname')->where('stateid','=',1)->orderby('statename')->get();

			return response()->json(['message' =>'STATE LIST','status'=>200,'data'=>$statelist], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>__('messages.notfound'),'status'=>400], 400);
		}			
    }
	
    public function generateCustomerOtp(Request $request)
    {
        $rules = [
			'mobilenumber'	=>	'required|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		//$ipAddress 		=	request()->ip();
		//$ipdata			=	$this->getIpDetail($ipAddress);
		
		$mobilenumber   = 	$request->input('mobilenumber');
		$name			=	"";
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
            $isexist = DB::table('customer_tbl')
						->select('customerid','name','mobilenumber','isverified')
                        ->where('mobilenumber','=',$mobilenumber)
                        ->first();
            if($isexist)
            {
				$otp			=	"555555";//rand(100000,999999);
				//$isSent 		= 	$this->smsService->sendOtp($mobilenumber,$otp);
				DB::update('update customer_tbl set otp=? where customerid=?',[$otp,$isexist->customerid]);
				
				return response()->json(['message' => 'OTP SENT SUCCESSFULLY','status'=>200,'mobilenumber'=>$mobilenumber], 200);
            }
            else
            {
				$otp		=	"555555";//rand(100000,999999);
				
				//$isSent 	= 	$this->smsService->sendOtp($mobilenumber,$otp);
				
				DB::insert('insert into customer_tbl(name,mobilenumber,otp,isactive,creationdate,fcmid) values(?,?,?,?,?,?)',[$name,$mobilenumber,$otp,1,$creationdate,'']);

				return response()->json(['message' => 'OTP SENT SUCCESSFULLY','status'=>200,'mobilenumber'=>$mobilenumber], 200);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'name'=>$name,'mobilenumber'=>$mobilenumber], 400);
        }
    }


    public function customerOtpVerification(Request $request)
    {
        $rules = [
            'mobilenumber' 	=> 'required',
            'otp' 			=> 'required',
			'fcmid'			=> 'required',
        ];

        $messages = [
            'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'otp.required' 			=> 'OTP IS REQUIRED',
			'fcmid.required'		=> 'FIREBASE CLOUD MESSAGING ID IS REQUIRED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $mobilenumber 	=	$request->input('mobilenumber');
        $otp			=	$request->input('otp');
		$fcmid			= 	(String) $request->input('fcmid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

        try
        {
            $isexist = DB::table('customer_tbl')
						->select('customerid','name','mobilenumber','access_token','profilepic','isverified')
                        ->where('mobilenumber','=',$mobilenumber)
                        ->where('otp','=',$otp)
                        ->first();
            if($isexist)
            {
				$profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
				if($profilepic!='')
				{
					$appUrl = Config::get('app.url');
					$isexist->profilepic = $appUrl."/storage/".$profilepic;
				}
				//$accesstoken	=	rand(10000000,99999999);
				$access_token	=	JWTAuthHelper::generateToken($isexist->customerid);
				DB::update('update customer_tbl set fcmid=?,access_token=? where customerid=?',[$fcmid,$access_token,$isexist->customerid]);
				
				if($isexist->isverified==0)
				{
					return response()->json(['message' =>'FILL BASIC DETAILS','status'=>200,'customerid'=>$isexist->customerid,'access_token'=>$access_token,'goto'=>1], 200);
				}
				else
				{
					$isexist=	DB::table('customer_tbl')
									->select('customerid','access_token','name','mobilenumber','profilepic')
									->where('customerid','=',$isexist->customerid)
									->first();

					
					//return response()->json(['message'=>'GO TO DASHBOARD','status'=>200,'customerid'=>$isexist->customerid,'access_token'=>$isexist->access_token,'goto'=>2,'data'=>$isexist], 200);
					
					return response()->json(['message'=>'GO TO DASHBOARD','status'=>200,'goto'=>2,'data'=>$isexist], 200);
				}
            }
            else
            {
				return response()->json(['message' => 'INVALID OTP OR MOBILE NUMBER','status'=>400,'mobilenumber'=>$mobilenumber,'otp'=>$otp], 400);
            }

        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
        }
    }
    public function updateBasicDetail(Request $request)
    {
        $rules = [
            'customerid' 	=> 'required',
			'name'  		=> 'required|regex:/^[a-zA-Z\s]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'lastname'  	=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'stateid' 		=> 'required',
			'cityid' 		=> 'required',
			'address' 		=> 'required',
			'pincode' 		=> 'required|numeric',
        ];

        $messages = [
            'customerid.required' 		=> 'CUSTOMER DETAIL IS REQURIED',
            'name.required' 			=> 'PLEASE ENTER NAME',
			'name.regex' 				=> 'INVALID NAME VALUE',
			'name.max' 					=> 'MAXIMUM LENGTH IS 50',
			'middlename.regex' 			=> 'INVALID MIDDLE NAME VALUE',
			'middlename.max' 			=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 			=> 'INVALID LAST NAME VALUE',
			'lastname.max' 				=> 'MAXIMUM LENGTH IS 30',
			'stateid.required' 			=> 'PLEASE SELECT STATE NAME',
			'cityid.required' 			=> 'PLEASE SELECT CITY NAME',
			'address.required' 			=> 'PLEASE ENTER ADDRESS DETAIL',
			'pincode.required' 			=> 'PLEASE ENTER PIN CODE',
			'pincode.numeric' 			=> 'PLEASE ENTER VALID PIN CODE',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $customerid 	=	intval($request->input('customerid'));
		$name   		= 	(String) $request->input('name');
		$middlename   	= 	(String) $request->input('middlename');
		$lastname   	= 	(String) $request->input('lastname');
		$stateid   		= 	intval($request->input('stateid'));
		$cityid   		= 	intval($request->input('cityid'));
		$completeaddress= 	(String) $request->input('address');
		$postalcode		= 	(String) $request->input('pincode');
		$subcategoryids	= 	(String) $request->input('subcategoryids');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

        try
        {
			$res	=	DB::update('update customer_tbl set name=?,middlename=?,lastname=?,stateid=?,cityid=?,completeaddress=?,postalcode=?,isverified=? where customerid=?',[$name,$middlename,$lastname,$stateid,$cityid,$completeaddress,$postalcode,1,$customerid]);
			
			$isexist = DB::table('customer_tbl')
						->select('customerid','name','middlename','lastname','mobilenumber','email','profilepic')
						->where('customerid','=',$customerid)
						->first();
			
			$cityId =	DB::table('city_tbl')->where('isdefault','=',1)->value('cityid');
			$sliders=	DB::table('cityslider_tbl')->select('sliderimage')->where('cityid','=',$cityId)->orderby('displayorder')->get();
			foreach($sliders as $slid)
			{
				if($slid->sliderimage!='')
				{
					$slid->sliderimage	=	Config::get('app.url')."/storage/".$slid->sliderimage;
					
				}
			}
			$cart	=	$this->getCartList($customerid);
			return response()->json(['message' =>'GO TO DASHBOARD','status'=>200,'goto'=>2,'data'=>['customerdata'=>$isexist,'sliders'=>$sliders,'cart'=>$cart]], 200);
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'name'=>$name,'mobilenumber'=>$mobilenumber], 400);
        }
    }


    public function customerSignInOtp(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
			'mobilenumber.required' => 'PLEASE PROVIDE MOBILE NUMBER',
            'mobilenumber.regex' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
        ];
		
        $validatedData 	= 	$request->validate($rules, $messages);
		
		$mobilenumber   = 	$request->input('mobilenumber');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);
			
            $isexist = DB::table('customer_tbl')
                        ->where('mobilenumber','=',$mobilenumber)
                        ->first();
            if($isexist)
            {
                if($isexist->isverified==0)
                {
                    return response()->json(['message' => 'YOUR MOBILE NUMBER IS NOT REGISTERED WITH THE SCREW DRIVER. PLEASE REGISTER YOUR ACCOUNT.','status'=>201,'mobilenumber'=>$mobilenumber], 201);
                }
                else
                {
					$otp		=	rand(100000,999999);
					$accesstoken=	rand(10000000,99999999);
					$isSent 	= 	$this->smsService->sendOtp($mobilenumber,$otp);
					DB::update('update customer_tbl set otp=?,accesstoken=? where customerid=?',[$otp,$accesstoken,$isexist->customerid]);
					
					return response()->json(['message' => __('messages.otpgenerated'),'status'=>200,'mobilenumber'=>$mobilenumber,'accesstoken'=>$accesstoken], 200);
                }
            }
            else
            {
				return response()->json(['message' => __('messages.invalidmobile'),'status'=>401,'mobilenumber'=>$mobilenumber], 200);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function customerSignInVerification(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'otp'  			=> 'required|numeric',
			'fcmid'  			=> 'required',
        ];

        $messages = [
			'mobilenumber.required' => 'PLEASE ENTER MOBILE NUMBER',
            'mobilenumber.regex' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'otp.required' 			=> 'PLEASE ENTER RECEIVED OTP',
			'otp.numeric' 			=> 'OTP SHOULD BE A NUMERIC VALUE',
			'fcmid.required' 		=> 'FIREBASE CLOUD MESSAGING ID IS REQUIRED',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$mobilenumber   = 	$request->input('mobilenumber');
		$otp   			= 	$request->input('otp');
		$fcmid   		= 	(String) $request->input('fcmid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);
			
            $isexist 	= 	DB::table('customer_tbl')
								->select('customerid','name','mobilenumber','isactive','isverified','accesstoken','profilepic')
								->where('mobilenumber','=',$mobilenumber)
								->where('otp','=',$otp)
								->first();
            if($isexist)
            {

				$profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
				if($profilepic!='')
				{
					$appUrl = Config::get('app.url');
					$isexist->profilepic = $appUrl."/storage/".$profilepic;
				}
				DB::update('update customer_tbl set fcmid=? where customerid=?',[$fcmid,$isexist->customerid]);				
				return response()->json(['message' => 'LOGIN SUCCESSFUL!','status'=>200,'customer'=>$isexist], 200);
            }
            else
            {
				return response()->json(['message' => 'THE MOBILE NUMBER OR OTP YOU ENTERED IS INVALID.','status'=>401,'mobilenumber'=>$mobilenumber], 200);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }

	public function getCustomerProfile(Request $request)
	{
        $rules = [
			'customerid'  	=> 'required',
        ];

        $messages = [
			'customerid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$customerid   	= 	$request->input('customerid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$customer	=	DB::table('customer_tbl as a')
							->select('a.customerid','a.name','a.middlename','a.lastname','a.mobilenumber','a.stateid','a.cityid','a.areaid','a.postalcode as pincode','a.completeaddress as address','isprofilecompleted','a.profilepic')
							->where('a.customerid','=',$customerid)
							->groupBy('a.customerid')
							->first();
			if($customer)
			{
				if($customer->profilepic!='')
				{
					$customer->profilepic	=	$appUrl = Config::get('app.url')."/storage/".$customer->profilepic;
				}
				return response()->json(['message' => 'CUSTOMER PROFILE DETAIL','status'=>200,'data'=>$customer], 200);
			}
			else
			{
				return response()->json(['message' =>'INVALID DATA PROVIDED','status'=>400], 400);
			}
        }
        catch (QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'status'=>400], 400);
        }		
	}

    public function updateCustomerProfile(Request $request)
	{
        $rules = [
			'profilepic'		=> 'nullable|max:1024',
            'name'  			=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'lastname'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'stateid'			=> 'required',
			'cityid'			=> 'required',
			'pincode'			=> 'required|numeric',
			'address'			=> 'required',
			'email'				=> 'nullable|email',
        ];

        $messages = [
			'profilepic.max' 		=> 'ONLY 512 KB SIZE IS ALLOWED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'INVALID NAME',
			'lastname.required' 	=> 'LAST NAME IS REQUIRED',
			'lastname.regex' 		=> 'INVALID LAST NAME',
			'lastname.max' 			=> 'INVALID LAST NAME',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'address.required'		=> 'ADDRESS IS REQUIRED',
			'email.email'			=> 'INVALID EMAIL PROVIDED',
        ];

        $validatedData 		=	$request->validate($rules, $messages);
		
		$customerid   		= 	intval($request->input('customerid'));
	
		$name   		=	(String) $request->input('name');
		$mobilenumber   =	(String) $request->input('mobilenumber');
		$middlename 	=	(String) $request->input('middlename');
		$lastname 		=	(String) $request->input('lastname');
		$email   		=	(String) $request->input('email');
		$stateid   		=	intval ($request->input('stateid'));
		$cityid   		=	intval ($request->input('cityid'));
		$areaid   		=	intval ($request->input('areaid',0));
		$pincode   		=	(String) $request->input('pincode');
		$address   		=	(String) $request->input('address');
		

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$profilepic    	= (String) $request->file('profilepic');
	
		
		try
		{
			$updateData = [];
			$updateData['name'] 			=	$name;
			$updateData['middlename'] 		=	$middlename;
			$updateData['lastname'] 		=	$lastname;
			$updateData['mobilenumber']		=	$mobilenumber;
			$updateData['email']			=	$email;
			$updateData['postalcode']		=	$pincode;
			$updateData['completeaddress']	=	$address;

			$updateData['stateid'] 			= $stateid;
			$updateData['cityid'] 			= $cityid;
			$updateData['areaid'] 			= $areaid;
			
			$customer 	= 	DB::table('customer_tbl')->where('customerid','=',$customerid)->first();
			
			if($profilepic!='')
			{
				Storage::disk('public')->delete($customer->profilepic);
				$profilepic					= $request->file('profilepic')->store('uploads/customerprofiles', 'public');
				$updateData['profilepic'] 	= $profilepic;
			}
			$res = DB::table('customer_tbl')->where('customerid',$customerid)->update($updateData);

			if($res)
			return response()->json(['message' =>'CUSTOMER DATA UPDATED','status'=>200], 200);
			else
			return response()->json(['message' =>'CUSTOMER DATA UPDATED','status'=>200], 200);
		}
		catch(QueryException $e)
		{
			if($profilepic!='')
			Storage::disk('public')->delete($profilepic);
						
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			
    }


    public function getCategoryList(Request $request)
	{
		try
		{
	
			$category = DB::table('category_tbl')
							->select('categoryid','category','categoryicon','categorypage')
							->where('parentcategoryid','=',0)
							->orderBy('displayorder')
							->get();
			
			foreach($category as $cat)
			{
				if($cat->categoryicon!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categoryicon = $appUrl."/storage/".$cat->categoryicon;
				}
				if($cat->categorypage!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categorypage = $appUrl."/storage/".$cat->categorypage;
				}
			}

			return response()->json(['message' =>'CATEGORY LIST','status'=>200,'data'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			

    }

    public function getSubCategoryList(Request $request)
	{
        $rules = [
			'categoryid'  	=> 'required',
        ];

        $messages = [
			'categoryid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$categoryid   	= 	$request->input('categoryid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
	
			$category = DB::table('category_tbl')
							->select('categoryid','category','displayorder','categoryicon','categorypage')
							->where('parentcategoryid','=',$categoryid)
							->orderBy('displayorder')
							->get();
			
			foreach($category as $cat)
			{
				if($cat->categoryicon!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categoryicon = $appUrl."/storage/".$cat->categoryicon;
				}
				if($cat->categorypage!='')
				{
					$appUrl = Config::get('app.url');
					$cat->categorypage = $appUrl."/storage/".$cat->categorypage;
				}
			}

			return response()->json(['message' =>'SUB CATEGORY LIST','status'=>200,'data'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			

    }

    public function getServiceList(Request $request)
	{
		/*
        $rules = [
			'subcategoryid'	=>	'required',
        ];

        $messages = [
			'subcategoryid.required' 	=> 'PLEASE PROVIDE CATEGORY DETAIL',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		*/
		
		$categoryid   	=	$request->input('subcategoryid');
		$searchtext   	=	(String) $request->input('searchtext');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$discount	=	$this->getDiscount();
			$services = DB::table('services as a')
							->select('a.categoryid','a.serviceid','a.optionid','a.servicetitle','a.mrp as servicecharge',DB::raw('0 as taxable'),'a.servicepic','a.likes','a.ratings','a.reviews','a.requiredtime','a.description','b.taxrate',DB::raw('(select count(serviceid) from services where serviceid = a.serviceid and optionid != 0) as options'))
							->leftjoin('tax_tbl as b','b.taxid','=','a.taxid')
							->when($categoryid!=0,function($query) use ($categoryid){
								return $query->where('a.categoryid','=',$categoryid);
							})							
							->when($searchtext!='',function($query) use ($searchtext){
								return $query->where('a.servicetitle','like','%'.$searchtext.'%');
							})
							->where('a.optionid','=',0)
							->get();
			
			foreach($services as $ser)
			{
				if($ser->servicepic!='')
				{
					$appUrl = Config::get('app.url');
					$ser->servicepic = $appUrl."/storage/".$ser->servicepic;
				}
				
				$needs = DB::table('service_tbl')
						->select(DB::raw("(SELECT GROUP_CONCAT('<li>',need ORDER BY displayorder SEPARATOR '</li>') FROM need_tbl WHERE FIND_IN_SET(needid,needids)) as needs"))
						->where('serviceid','=',$ser->serviceid)
						->get();
				$ser->needs	=	$needs;

				$includes = DB::table('service_tbl')
						->select(DB::raw("(SELECT GROUP_CONCAT('<li>',serviceinclude ORDER BY displayorder SEPARATOR '</li>') FROM include_tbl WHERE FIND_IN_SET(includeid,includeids)) as includes"))
						->where('serviceid','=',$ser->serviceid)
						->get();
				$ser->includes	=	$includes;

				$excludes = DB::table('service_tbl')
						->select(DB::raw("(SELECT GROUP_CONCAT('<li>',serviceexclude ORDER BY displayorder SEPARATOR '</li>') FROM exclude_tbl WHERE FIND_IN_SET(excludeid,excludeids)) as excludes"))
						->where('serviceid','=',$ser->serviceid)
						->get();
				$ser->excludes	=	$excludes;

				$ser->taxable	=	$ser->servicecharge-(($ser->servicecharge*$discount)/100);
				$ser->taxable	=	bcdiv($ser->taxable,1,2);

			}
			
			return response()->json(['message' =>'SERVICE LIST','status'=>200,'data'=>$services], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function getMyAddress(Request $request)
	{
		
        $rules = [
			'customerid'	=>	'required',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
	
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$addresslist	=	DB::table('customer_address')
									->select('addressid','stateid','cityid','postalcode','address','latitude','longitude')
									->where('customerid','=',$customerid)
									->get();
			
			return response()->json(['message' =>'ADDRESS LIST','status'=>200,'data'=>$addresslist], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }
    public function addServiceLocation(Request $request)
	{
		
        $rules = [
			'customerid'	=>	'required',
			'address'		=>	'required',
			'latitude'		=>	'required|numeric',
			'longitude'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'address.required' 		=> 'ADDRESS IS REQUIRED',
			'latitude.required' 	=> 'LATITUDE IS REQUIRED',
			'latitude.numeric' 		=> 'INVALID LATITUDE VALUE',
			'longitude.required' 	=> 'LONGITUDE IS REQUIRED',
			'longitude.numeric' 	=> 'LONGITUDE LATITUDE VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
	
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$isexist	=	DB::table('customer_tbl')->where('customerid','=',$customerid)->first();
			
			$address   	=	(String) $request->input('address');
			$latitude   =	doubleval($request->input('latitude'));
			$longitude  =	doubleval($request->input('longitude'));
			

			DB::insert('insert into customer_address(stateid,cityid,areaid,address,postalcode,latitude,longitude,customerid) values(?,?,?,?,?,?,?,?)',[$isexist->stateid,$isexist->cityid,0,$address,$isexist->postalcode,$latitude,$longitude,$customerid]);

			$lastid 		= 	DB::getPdo()->lastInsertId();

			$addresslist	=	DB::table('customer_address')
									->select('addressid','stateid','cityid','address','postalcode','latitude','longitude')
									->where('customerid','=',$customerid)
									->get();
			
			return response()->json(['message' =>'ADDRESS STORED SUCCESSFULLY','status'=>200,'data'=>$addresslist,'lastid'=>$lastid], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>'SERVICE LOCATION ALREADY EXIST','status'=>400], 400);
		}
    }

    public function myServiceList(Request $request)
	{
		
        $rules = [
			'customerid'	=>	'required',
			'accesstoken'	=>	'required',
			'orderstatus'	=>	'required'
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'orderstatus.required' 	=> 'ORDER STATUS IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
		$accesstoken   	=	(String) $request->input('accesstoken');
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$isexist		=	$this->CheckCustomer($customerid,$accesstoken);
			if($isexist)
			{
				$orderstatus	=	intval($request->input('orderstatus'));
				/*
				$servicelist	= 	DB::table('customer_order as a')
									->select('a.orderid','b.serviceid','a.optionid','a.categoryid','a.quantity','a.visitingcharge','a.mrp','a.taxid','a.orderstatus','a.areaid','b.servicetitle','b.requiredtime','b.description','b.servicepic')
									->leftJoin('services as b', function($join) {
										 $join->on('b.serviceid', '=', 'a.serviceid')
											->where('b.optionid', '=', 'a.optionid');
												
									 })
									->where('a.customerid','=',$customerid)			
									->where('a.orderstatus','=',$orderstatus)			
									->get();
				*/
				$servicelist	=	DB::table('customer_order as a')
									->join('services as b', function($join) {
										$join->on('a.serviceid', '=', 'b.serviceid')
											 ->on('a.optionid', '=', 'b.optionid');
									})
									->select('a.orderid','b.serviceid','a.optionid','a.categoryid','a.quantity','a.visitingcharge','a.mrp','a.taxid','a.orderstatus','a.areaid','b.servicetitle','b.requiredtime','b.description','b.servicepic')
									->where('a.customerid','=',$customerid)		
									->where('a.orderstatus','=',$orderstatus)				
									->get();				
				
				foreach($servicelist as $lst)
				{
					if($lst->servicepic!='')
					{
						$lst->servicepic	=	Config::get('app.url')."/storage/".$lst->servicepic;
					}
				}
				
				return response()->json(['message' =>'MY SERVICE LIST','status'=>200,'servicelist'=>$servicelist], 200);
			}
			else
			{
				return response()->json(['message' =>'INVALID USER DETAIL PROVIDED','status'=>200], 200);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function getSlotTime(Request $request)
	{
        $rules = [
			'todaysdate'	=>	'required|date',
			'subcategoryid'	=>	'required'
        ];

        $messages = [
			'todaysdate.required' 	=> 'DATE IS REQUIRED',
			'todaysdate.date' 		=> 'INVALID DATE VALUE PROVIDED',
			'subcategoryid.required'=> 'SUB CATEGORYID IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$subcategoryid	=	intval($request->input('subcategoryid'));
		$requestdate	=	date('Y\-m\-d',strtotime($request->input('todaysdate')));

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$times = [];

			$requestedDate 	= Carbon::parse($requestdate);
			$currentDate 	= $currentDateTime->format('Y-m-d');


			
			$requestedDateStartOfDay = $requestedDate->startOfDay();
			
			if(!$requestedDateStartOfDay->isBefore(Carbon::today()))
			{
				if($currentDate==$requestdate)
				{
					$now 	= Carbon::now()->addHours(2)->format('H:i:s');
					$slots	=	DB::table('slot_tbl')
									->select('slot','percentage')
									->selectRaw("IF(slot > ?, 1, 0) as isactive", [$now])
									->selectRaw("0 as vendors")
									->selectRaw("0 as takeonly")
									->where('isactive','=',1)
									->orderby('slot')
									->get();
									
					$carry	=	0;
					foreach($slots as $slot)
					{
						if($slot->isactive==0)
						{
							$vendors 	=	DB::table('vendors_subcategory')->where('subcategoryid',$subcategoryid)->value('vendors');
							$orders 	=	DB::table('todays_order')
												->where('categoryid','=',$subcategoryid)
												->where('slottime','<=',$slot->slot)
												->where('servicedate','=',$requestdate)
												->count();

							$completed 	=	DB::table('todays_order')
												->where('categoryid','=',$subcategoryid)
												->where('orderstatus','=',3)
												->where('slottime','<=',$slot->slot)
												->where('servicedate','=',$requestdate)
												->count();

							if(($vendors-$orders+$completed)>0)
							{
								$carry	=	$vendors-$orders+$completed;
							}
							//$percentage	=	intval(($vendors*$slot->percentage)/100);
						}
						else
						{
							$vendors 		=	DB::table('vendors_subcategory')->where('subcategoryid',$subcategoryid)->value('vendors');
							$percentage		=	intval(($vendors*$slot->percentage)/100);
							$percentage		=	$percentage+$carry;
							if($percentage>$vendors)
							{
								$slot->vendors	=	$vendors;
								$percentage		=	$vendors;
							}
							else
							{
								$slot->vendors	=	$percentage;
							}
							$percentage		=	intval(($percentage*$slot->percentage)/100);
							$slot->takeonly	=	$percentage;
							if($percentage==0)
							{
								$slot->isactive=0;
							}
							$carry=0;
						}
					}
				}
				else
				{
					$slots	=	DB::table('slot_tbl')
									->select('slot','percentage')
									->selectRaw("1 as isactive")
									->where('isactive','=',1)
									->selectRaw("0 as vendors")
									->selectRaw("0 as takeonly")
									->orderby('slot')
									->get();

					foreach($slots as $slot)
					{
						$vendors 		=	DB::table('vendors_subcategory')->where('subcategoryid',$subcategoryid)->value('vendors');
						$percentage		=	intval(($vendors*$slot->percentage)/100);
						$slot->vendors	=	$vendors;
						$slot->takeonly	=	$percentage;
						if($percentage==0)
						{
							$slot->isactive=0;
						}
					}
									
				}
				
				$filteredSlots = $slots->map(function ($slot) {
					return [
						'slot' 		=> Carbon::createFromFormat('H:i:s', $slot->slot)->format('h:i A'),
						'isactive' 	=> $slot->isactive,
					];
				});				

				return response()->json(['message'=>'SLOT TIME','status'=>200,'data'=>$filteredSlots], 200);
				
			}
			else
			{
				return response()->json(['message' =>'INVALID DATE PROVIDED','status'=>201,'data'=>""], 201);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function getVendors(Request $request)
	{
		
        $rules = [
			'orderid'	=>	'required',
			'radius'	=>	'required',
        ];

        $messages = [
			'orderid.required' 	=> 'DATE IS REQUIRED',
			'radius.required' 	=> 'RADIUS IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$orderid		=	intval($request->input('orderid'));
		$radius			=	doubleval($request->input('radius'));
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			
			$vendors	=	$this->GetVendorsInRadius($orderid,$radius);		
			
			return response()->json(['message' =>'VENDORS LIST','status'=>200,'vendors'=>$vendors],200);
			
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }


    public function getOptionList(Request $request)
	{
		
        $rules = [
			'categoryid'	=>	'required',
			'serviceid'		=>	'required',
        ];

        $messages = [
			'categoryid.required' 	=> 'PLEASE PROVIDE CATEGORY DETAIL',
			'serviceid.required' 	=> 'PLEASE PROVIDE SERVICE DETAIL',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$subcategoryid  =	intval($request->input('categoryid'));
		$serviceid		=	intval($request->input('serviceid'));

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$discount	=	$this->getDiscount();
			$services = DB::table('services as a')
							->select('a.categoryid','a.serviceid','a.optionid','a.servicetitle','a.mrp as servicecharge',DB::raw('0 as taxable'),'a.servicepic','a.likes','a.ratings','a.reviews','a.requiredtime','a.description','b.taxrate')
							->leftjoin('tax_tbl as b','b.taxid','=','a.taxid')
							->where('a.categoryid','=',$subcategoryid)
							->where('a.serviceid','=',$serviceid)
							->get();
			
			foreach($services as $ser)
			{
				if($ser->servicepic!='')
				{
					$appUrl = Config::get('app.url');
					$ser->servicepic = $appUrl."/storage/".$ser->servicepic;
				}
				
				$ser->taxable	=	$ser->servicecharge-(($ser->servicecharge*$discount)/100);
				$ser->taxable	=	round($ser->taxable);

			}
			
			return response()->json(['message' =>'SERVICE LIST','status'=>200,'data'=>$services], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }


    public function addToCart(Request $request)
	{
        $rules = [
			'categoryid'	=>	'required|numeric',
			'serviceid'		=>	'required|numeric',
			'quantity'		=>	'required|numeric',
        ];

        $messages = [
			'categoryid.required' 	=> 'PLEASE PROVIDE CATEGORY DETAIL',
			'categoryid.numeric' 	=> 'INVALID CATEGORY ID',
			'serviceid.required' 	=> 'PLEASE PROVIDE SERVICE DETAIL',
			'serviceid.numeric' 	=> 'INVALID SERVICE ID',
			'quantity.required' 	=> 'QUANTITY IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));

		$currentDateTime	= 	now();
		$creationdate  		= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$serviceid   	=	intval($request->input('serviceid'));
			$optionid   	=	intval($request->input('optionid'));
			$quantity   	=	intval($request->input('quantity'));
			$categoryid   	=	intval($request->input('categoryid'));
			
			if($categoryid==0 || $serviceid==0 || $quantity==0)
			{
				return response()->json(['message' =>'INVALID DETAILS','status'=>400], 400);
			}
			$isservice	=	DB::table('services')->where('serviceid','=',$serviceid)->where('optionid','=',$optionid)->exists();
			if(!$isservice)
			{
				return response()->json(['message' =>'INVALID SERVICE DETAILS','status'=>400], 400);
			}
			
			$islocked	=	DB::table('customer_cart')->where('customerid','=',$customerid)->where('islocked','=','1')->exists();
			if($islocked)
			{
				return response()->json(['message' =>'PAYMENT IS UNDER PROCESS. PLEASE WAIT FOR 5 MINUTES','status'=>423], 423);
			}
			$check		=	DB::table('customer_cart')
								->where('customerid','=',$customerid)
								->where('categoryid','=',$categoryid)
								->where('serviceid','=',$serviceid)
								->where('optionid','=',$optionid)
								->first();

			$discount	=	$this->getDiscount();
			if(!$check)
			{
				$services				=	DB::table('services as a')
											->select('a.categoryid','a.visitingcharge','a.serviceid','a.optionid','a.servicetitle','a.mrp as servicecharge',DB::raw('0 as taxable'),'a.servicepic','a.likes','a.ratings','a.reviews','a.requiredtime','a.description','b.taxrate')
											->leftjoin('tax_tbl as b','b.taxid','=','a.taxid')
											->where('a.categoryid','=',$categoryid)
											->where('a.serviceid','=',$serviceid)
											->where('a.optionid','=',$optionid)
											->first();
				
				$services->taxable		=	$services->servicecharge-(($services->servicecharge*$discount)/100);
				$services->taxable		=	bcdiv($services->taxable*$quantity,1,2);
				$taxvalue				=	bcdiv((($services->taxable*$services->taxrate)/100),1,2);
				$netpayable				=	bcdiv($services->taxable+$taxvalue,1,2);

				$totalservicecharge		=	$services->servicecharge*$quantity;

				DB::insert('insert into customer_cart(customerid,categoryid,serviceid,optionid,quantity,servicecharge,discount,visitingcharge,taxable,taxvalue,payable) values(?,?,?,?,?,?,?,?,?,?,?)',[$customerid,$categoryid,$serviceid,$optionid,$quantity,$totalservicecharge,$discount,$services->visitingcharge,$services->taxable,$taxvalue,$netpayable]);

			}
			else
			{
				$services				=	DB::table('services as a')
											->select('a.categoryid','a.visitingcharge','a.serviceid','a.optionid','a.servicetitle','a.mrp as servicecharge',DB::raw('0 as taxable'),'a.servicepic','a.likes','a.ratings','a.reviews','a.requiredtime','a.description','b.taxrate')
											->leftjoin('tax_tbl as b','b.taxid','=','a.taxid')
											->where('a.categoryid','=',$categoryid)
											->where('a.serviceid','=',$serviceid)
											->where('a.optionid','=',$optionid)
											->first();
				
				$services->servicecharge=	$services->servicecharge;
				$services->taxable		=	bcdiv(($services->servicecharge-(($services->servicecharge*$discount)/100)),1,2);
				$services->taxable		=	$services->taxable*$quantity;
				$taxvalue				=	bcdiv((($services->taxable*$services->taxrate)/100),1,2);
				$netpayable				=	bcdiv($services->taxable+$taxvalue,1,2);
				$totalservicecharge		=	$services->servicecharge*$quantity;
				DB::update('update customer_cart set quantity=?,servicecharge=?,discount=?,taxable=?,taxvalue=?,payable=?,visitingcharge=? where customerid=? and serviceid=? and optionid=?',[$quantity,$totalservicecharge,$discount,$services->taxable,$taxvalue,$netpayable,$services->visitingcharge,$customerid,$serviceid,$optionid]);
			}
			
			$cart	=	$this->getCartList($customerid);
			return response()->json(['message' =>'ITEM ADDED TO YOUR CART LIST','status'=>200,'data'=>$cart], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function getCart(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		$appUrl = Config::get('app.url');
	
		try
		{
			$cart	=	$this->getCartList($customerid);
			
			return response()->json(['message' =>'CART LIST','status'=>200,'data'=>$cart], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function removeFromCart(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'categoryid'	=>	'required|numeric',
			'serviceid'		=>	'required|numeric',
			'optionid'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'categoryid.required' 	=> 'CATEGORY DETAIL IS REQUIRED',
			'serviceid.required' 	=> 'SERVICE DETAIL IS REQUIRED',
			'optionid.required' 	=> 'OPTION ID IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$categoryid  	=	intval($request->input('categoryid'));
			$serviceid   	=	intval($request->input('serviceid'));
			$optionid   	=	intval($request->input('optionid'));

			DB::delete('delete from customer_cart where customerid=? and categoryid=? and serviceid=? and optionid=?',[$customerid,$categoryid,$serviceid,$optionid]);
			
			$cart	=	$this->getCartList($customerid);
			
			return response()->json(['message' =>'SERVICE REMOVED SUCCESSFULLY','status'=>200,'data'=>$cart], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function applyCoupon(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'couponcode'	=>	'required',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'couponcode.required' 	=> 'COUPON CODE IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
		$couponcode   	=	(String) $request->input('couponcode');
		
		try
		{				

			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
			$appUrl 		=	Config::get('app.url');
		
			$order			=	DB::table('customer_cart_order')->where('customerid','=',$customerid)->first();
			
			if($order)
			{
				$categoryid	=	DB::table('customer_cart')
									->where('customerid', $customerid)
									->orderBy('categoryid', 'asc')
									->value('categoryid');
									
				$coupon		=	DB::table('coupon_tbl')
									->where('couponcode','=',$couponcode)
									->whereRaw("FIND_IN_SET(?, applicablesubcategory)", [$categoryid])
									->whereRaw("STR_TO_DATE(expirydate,'%Y-%m-%d')>NOW()")
									->first();

				$coupondiscount	=	0;
				if(is_null($coupon))
				{
					return response()->json(['message' =>'INVALID COUPON CODE. THIS COUPON CODE MAY HAVE EXPIRED OR MAY NOT BE APPLICABLE FOR THE SELECTED SERVICE CATEGORY.','status'=>422],422);
				}
									
				if($coupon->ordereligibility=='FIRST')
				{
					$count = DB::table('customer_order')->where('couponcode','=',$couponcode)->where('customerid','=',$customerid)->count();
					if($count>0)
					{
						$message	=	"THIS COUPON HAS ALREADY BEEN USED BY THE USER";
						return response()->json(['message' =>$message,'status'=>409],409);						
					}
				}
				if($coupon->totalusagelimit!=0)
				{
					$count = DB::table('customer_order')->where('couponcode','=',$couponcode)->count();
					if($count>$coupon->totalusagelimit)
					{
						$message	=	"MAXIMUM NUMBER OF USAGE FOR THIS COUPON IS REACHED";
						return response()->json(['message' =>$message,'status'=>409],409);						
					}
				}
				if($order->totaltaxable<$coupon->minordervalue)
				{
					$message	=	"MINIMUM ORDER VALUE FOR THIS COUPON IS ".$coupon->minordervalue." INR";
					return response()->json(['message' =>$message,'status'=>400],400);
				}
				
				DB::update('update customer_cart_order set couponcode=? where customerid=?',[$couponcode,$customerid]);
				
				$cart	=	$this->getCartList($customerid);
				
				return response()->json(['message' =>'COUPON APPLIED SUCCESSFULLY','status'=>200,'data'=>$cart], 200);
				
			}
			else
			{
				return response()->json(['message' =>'ORDER DOES NOT EXIST','status'=>404], 404);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function generateOrder(Request $request)
	{
        $rules = [
			'customerid'	=>	'required',
			'netamount'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'netamount.required' 	=> 'AMOUNT IS REQUIRED',
			'netamount.numeric' 	=> 'AMOUNT MUST BE NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$customerid   	= 	intval($request->input('customerid'));
		$amount   		= 	doubleval($request->input('netamount'));


		$iscart			=	DB::table('customer_cart_order')->where('customerid','=',$customerid)->first();
		if(!$iscart)
		{
			return response()->json(['message' =>'YOUR CART IS EMPTY','status'=>400],400);			
		}
		if($iscart->couponcode=='')
		{
			if(round(($iscart->visitingcharge+$iscart->visitingtax+$iscart->netpayable),2)!=$amount)
			{
				return response()->json(['message' =>'INVALID REQUEST ATTEMPTED','status'=>400], 400);
			}
		}
		else
		{
			$totalcost	=	round($iscart->netpayable,2)-(round($iscart->netpayable*$iscart->couponpercent)/100);
			$totalcost	=	$totalcost+$iscart->visitingcharge+$iscart->visitingtax;
			if($totalcost!=$amount)
			{
				return response()->json(['message' =>'INVALID REQUEST ATTEMPTED','status'=>400], 400);
			}
		}

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		$paymentdate	=	date('Y\-m\-d',strtotime($creationdate));			
		$financialyear  =	$this->resourceController->GetFinancialYear($paymentdate);
		$receipt		=	"RCPT".rand(100,999)."".strtotime(date('Y\-m\-d H:i:s'));
		
		try
		{
			$order = $this->razorpay->order->create([
				'amount' => round($amount*100), // Convert to paisa
				'currency' => 'INR',
				'receipt' => "{$receipt}",
				'payment_capture' => 1
			]);
			
			$threeMinutesAgo=	Carbon::now()->subMinutes(3);
			$recentPayment	=	DB::table('customer_cart_order')
									->select('razorpay_order_id','netpayable','paymentstatus')
									->where('customerid','=',$customerid)
									->where('paymentdatetime','>=',$threeMinutesAgo)
									->first();
			
			if($recentPayment)
			{
				if($recentPayment->paymentstatus=='pending') 
				{
					/*
					DB::table('receipt_tbl')
						->where('customerid','=',$customerid)
						->where('razorpay_order_id','=',$recentPayment->razorpay_order_id)
						->where('paymentstatus','=','pending')
						->update([
							'razorpay_order_id' => $order->id,
							'receiptnumber' 	=> $receipt,
							'netamount' 		=> $amount,
							'paymentstatus' 	=> 'pending',
							'paymentdatetime' 	=> date('Y\-m\-d H:i:s')
						]);
					*/
					DB::table('customer_cart_order')
						->where('customerid','=',$customerid)
						->where('razorpay_order_id','=',$recentPayment->razorpay_order_id)
						->where('paymentstatus','=','pending')
						->update([
							'razorpay_order_id' => $order->id,
							'receiptnumber' 	=> $receipt,
							'netpayable' 		=> $iscart->netpayable,
							'paymentstatus' 	=> 'pending',
							'paymentdatetime' 	=> date('Y\-m\-d H:i:s')
						]);
					
					return response()->json(['message'=>'ORDER GENERATED SUCCESSFULLY','status'=>200,'orderid'=>$order->id,'netamount'=>$amount], 200);
				}
			}
			
			/*
			DB::table('receipt_tbl')->insert([
				'financialyear'		=> $financialyear,
				'receiptnumber'		=> $receipt,
				'customerid' 		=> $customerid,
				'razorpay_order_id' => $order->id,
				'netamount' 		=> $amount,
				'paymentstatus' 	=> 'pending',
				'paymentdatetime' 	=> date('Y\-m\-d H:i:s'),
				'generateddate'		=> date('Y\-m\-d H:i:s')
			]);
			*/
			DB::table('customer_cart_order')
				->where('customerid','=',$customerid)
				->update([
					'financialyear'		=> $financialyear,
					'razorpay_order_id' => $order->id,
					'receiptnumber' 	=> $receipt,
					'netpayable' 		=> $iscart->netpayable,
					'paymentstatus' 	=> 'pending',
					'paymentdatetime' 	=> date('Y\-m\-d H:i:s'),
					'islocked' 			=> 1,
				]);

			DB::table('customer_cart')
				->where('customerid','=',$customerid)
				->update([
					'islocked'		=> 1,
				]);
			
			return response()->json(['message' =>'ORDER GENERATED SUCCESSFULLY','status'=>200,'orderid'=>$order->id,'netamount'=>$amount],200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message'=>$e->getMessage(),'status'=>400],400);
		}
    }

    public function bookService(Request $request)
	{
        $rules = [
			'customerid'			=>	'required',
			'servicedate'			=>	'required|date',
			'slottime'				=>	'required|date_format:H:i:s',
			'addressid'				=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'razorpay_signature'	=>	'required',
			'netamount'				=>	'nullable|numeric',
        ];

        $messages = [
			'customerid.required' 		=> 'CUSTOMER DETAIL IS REQUIRED',
			'servicedate.required' 		=> 'SERVICE DATE IS REQUIRED',
			'slottime.required' 		=> 'SERVICE TIME IS REQUIRED',
			'slottime.time' 			=> 'INVALID TIME PROVIDED',
			'addressid.required' 		=> 'ADDRESS DETAIL IS REQUIRED',
			'razorpay_order_id.required'=> 'RAZORPAY ORDER ID IS REQUIRED',
			'razorpay_payment_id.required'=> 'RAZORPAY PAYMENT ID IS REQUIRED',
			'razorpay_signature.required'=> 'RAZORPAY SIGNATURE IS REQUIRED',
			'netamount.numeric' 			=> 'AMOUNT MUST BE A NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
	
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$razorpay_order_id	=	(String) $request->input('razorpay_order_id');

			$order = DB::table('customer_cart_order')
						->where('razorpay_order_id', $razorpay_order_id)
						->where('paymentstatus','pending')
						->where('customerid','=',$customerid)
						->first();
			if(!$order)
			{
				return response()->json(['message' =>'TRANSACTION DETAIL NOT FOUND.','status'=>404],404);					
			}
			$amount   			= 	doubleval($request->input('netamount'));
			$razorpay_payment_id=	(String) $request->input('razorpay_payment_id');
			$razorpay_signature	=	(String) $request->input('razorpay_signature');

			$servicedate		=	(String) $request->input('servicedate');
			$slottime			=	(String) $request->input('slottime');	
			$addressid			=	intval($request->input('addressid'));


			$generatedSignature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, env('RAZORPAY_SECRET'));
			if($generatedSignature===$razorpay_signature)
			{
				DB::beginTransaction();
				$cartorder	=	DB::table('customer_cart_order')
									->where('customerid','=',$customerid)
									->where('razorpay_order_id','=',$razorpay_order_id)
									->first();

				$receiptid	=	DB::table('receipt_tbl')->insertGetId([
								'customerid' 			=>	$customerid,
								'financialyear' 		=>	$cartorder->financialyear,
								'receiptnumber' 		=>	$cartorder->receiptnumber,
								'razorpay_order_id' 	=>	$cartorder->razorpay_order_id,
								'razorpay_payment_id'	=>	$razorpay_payment_id,
								'razorpay_signature' 	=>	$razorpay_signature,
								'netamount'				=>	$amount,
								'paymentstatus' 		=>	'paid',
								'paymentdatetime' 		=>	$cartorder->paymentdatetime,
								'generateddate' 		=>	$cartorder->paymentdatetime,
								'completeddate' 		=>	date('Y\-m\-d H:i:s'),
							]);

				if($receiptid)
				{
					$ordcart=	DB::table('customer_cart_order')
									->select('customerid','visitingcharge','visitingtax','discount')
									->where('customerid','=',$customerid)
									->where('razorpay_order_id','=',$razorpay_order_id)
									->first();

					$order	=	$this->getCartList($customerid);
					$carts	=	count($order->cartlist);
					

					$orderid	=	DB::table('customer_order')->insertGetId([
									'customerid' 	=> $customerid,
									'servicedate' 	=> $servicedate,
									'slottime' 		=> $slottime,
									'addressid' 	=> $addressid,
									'servicecharge' => doubleval($order->servicecharge),
									'discount' 		=> $ordcart->discount,
									'visitingcharge'=> $order->visitingcharge,
									'visitingtax' 	=> $ordcart->visitingtax,
									'totaltaxable' 	=> $order->totaltaxable,
									'totaltaxvalue' => $order->totaltaxvalue,
									'netpayable' 	=> $order->netpayable,
									'paid' 			=> $order->netpayable,
									'couponcode' 	=> $order->couponcode
								]);
					if($orderid)
					{
						foreach($order->cartlist as $service)
						{
							DB::table('customer_order_detail')->insert([
								'orderid' 			=>	$orderid,
								'customerid' 		=>	$service->customerid,
								'categoryid' 		=>	$service->categoryid,
								'serviceid' 		=>	$service->serviceid,
								'optionid' 			=>	$service->optionid,
								'servicecharge' 	=>	$service->servicecharge,
								'discount' 			=>	$ordcart->discount,
								'visitingcharge'	=>	$ordcart->visitingcharge,
								'taxable' 			=>	$service->taxable,
								'taxvalue' 			=>	$service->taxvalue,
								'payable' 			=>	$service->payable,
								'quantity' 			=>	$service->quantity,
								'addressid' 		=>	$addressid,
								'slottime'			=>	$slottime,
								'paid' 				=>	$service->payable,
								'servicedate'		=>	$servicedate
							]);								
						}
					}
				}
				DB::table('receipt_tbl')
						->where('receiptid','=',$receiptid)
						->where('customerid','=',$customerid)
						->where('razorpay_order_id','=',$razorpay_order_id)
						->update([
							'orderid' => $orderid,
						]);

				
				DB::delete('delete from customer_cart where customerid=?',[$customerid]);
				DB::delete('delete from customer_cart_order where customerid=?',[$customerid]);
				
				DB::commit();
				
				return response()->json(['message' =>'PAYMENT VERIFIED SUCCESSFULLY!','status'=>200],200);
			}
			else
			{
				return response()->json(['message' =>'INVALID PAYMENT SIGNATURE!','status'=>400],400);
			}
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['message' =>'DUPLICATE TRANSACTION FOUND','status'=>400], 400);
		}
    }

    public function bookingFailed(Request $request)
	{
        $rules = [
			'customerid'			=>	'required',
			'servicedate'			=>	'required|date',
			'slottime'				=>	'required|date_format:H:i:s',
			'addressid'				=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'errorcode'				=>	'required',
			'errordescription'		=>	'required',
			'netamount'				=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 			=> 'CUSTOMER DETAIL IS REQUIRED',
			'servicedate.required' 			=> 'SERVICE DATE IS REQUIRED',
			'slottime.required' 			=> 'SERVICE TIME IS REQUIRED',
			'slottime.time' 				=> 'INVALID TIME PROVIDED',
			'addressid.required' 			=> 'ADDRESS DETAIL IS REQUIRED',
			'razorpay_order_id.required'	=> 'RAZORPAY ORDER ID IS REQUIRED',
			'razorpay_payment_id.required'	=> 'RAZORPAY PAYMENT ID IS REQUIRED',
			'errorcode.required'			=> 'ERROR CODE IS REQUIRED',
			'errordescription.required'		=> 'ERROR DESCRIPTION IS REQUIRED',
			'netamount.numeric' 			=> 'AMOUNT MUST BE A NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   	=	intval($request->input('customerid'));
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$razorpay_order_id	=	(String) $request->input('razorpay_order_id');

			$order = DB::table('customer_cart_order')
						->where('razorpay_order_id', $razorpay_order_id)
						->where('paymentstatus','pending')
						->where('customerid','=',$customerid)
						->first();
			if(!$order)
			{
				return response()->json(['message' =>'TRANSACTION DETAIL NOT FOUND.','status'=>404],404);					
			}
			$amount   			= 	doubleval($request->input('netamount'));
			$razorpay_payment_id=	(String) $request->input('razorpay_payment_id');
			

			$servicedate		=	(String) $request->input('servicedate');
			$slottime			=	(String) $request->input('slottime');	
			$addressid			=	intval($request->input('addressid'));
			$errorcode			=	(String) $request->input('errorcode');	
			$errordescription	=	(String) $request->input('errordescription');	


			DB::beginTransaction();
			$cartorder	=	DB::table('customer_cart_order')
								->where('customerid','=',$customerid)
								->where('razorpay_order_id','=',$razorpay_order_id)
								->first();

			$receiptid	=	DB::table('receipt_tbl')->insertGetId([
							'customerid' 			=>	$customerid,
							'financialyear' 		=>	$cartorder->financialyear,
							'receiptnumber' 		=>	$cartorder->receiptnumber,
							'razorpay_order_id' 	=>	$cartorder->razorpay_order_id,
							'razorpay_payment_id'	=>	$razorpay_payment_id,
							'netamount'				=>	$amount,
							'paymentstatus' 		=>	'failed',
							'paymentdatetime' 		=>	$cartorder->paymentdatetime,
							'errorcode' 			=>	$errorcode,
							'errordescription' 		=>	$errordescription,
						]);

			if($receiptid)
			{
				$ordcart=	DB::table('customer_cart_order')
								->select('customerid','visitingcharge','visitingtax','discount')
								->where('customerid','=',$customerid)
								->where('razorpay_order_id','=',$razorpay_order_id)
								->first();

				$order	=	$this->getCartList($customerid);
				$carts	=	count($order->cartlist);
				

				$orderid	=	DB::table('customer_order')->insertGetId([
								'customerid' 	=> $customerid,
								'servicedate' 	=> $servicedate,
								'slottime' 		=> $slottime,
								'addressid' 	=> $addressid,
								'servicecharge' => doubleval($order->servicecharge),
								'discount' 		=> $ordcart->discount,
								'visitingcharge'=> $order->visitingcharge,
								'visitingtax' 	=> $ordcart->visitingtax,
								'totaltaxable' 	=> $order->totaltaxable,
								'totaltaxvalue' => $order->totaltaxvalue,
								'netpayable' 	=> $order->netpayable,
								'couponcode' 	=> $order->couponcode,
								'isfailed'		=>	1,
							]);
			}
			DB::table('receipt_tbl')
					->where('receiptid','=',$receiptid)
					->where('customerid','=',$customerid)
					->where('razorpay_order_id','=',$razorpay_order_id)
					->update([
						'orderid' => $orderid,
					]);

			
			DB::delete('delete from customer_cart where customerid=?',[$customerid]);
			DB::delete('delete from customer_cart_order where customerid=?',[$customerid]);
			
			DB::commit();
			
			return response()->json(['message' =>'PAYMENT FAILURE STORED SUCCESSFULLY!','status'=>200],200);
		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['message' =>'DUPLICATE TRANSACTION FOUND','status'=>400], 400);
		}
    }

}
