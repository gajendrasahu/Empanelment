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
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use App\Mail\OTPEmail;
use App\Mail\ForgotPassword;
use Pusher\Pusher;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use App\Services\SmsService;
use App\Http\Controllers\Master\ResourcesController;
use App\Services\RazorpayService;
use Razorpay\Api\Api;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class WebApiController extends Controller
{
	protected $smsService;
	protected $appUrl;
	protected $razorpay;
	protected $resourceController;
	/*
	$ipAddress 		= request()->ip();
	$response 		= Http::get("http://ip-api.com/json/{$ipAddress}");

	if ($response->successful()) {
		$data = $response->json();

		$country = $data['country'];  // Country
		$region = $data['regionName'];  // Region (State)
		$city = $data['city'];  // City
		$latitude = $data['lat'];  // Latitude
		$longitude = $data['lon'];  // Longitude
	}
	*/

	private function getOTP()
	{
		$otp	=	rand(100000,999999);
		//$otp	=	"555555";
		return $otp;
	}
	
	public function __construct(SmsService $smsService,ResourcesController $resourceController)
	{
		$this->appUrl = Config::get('app.url');
		$this->smsService = $smsService;
		$this->resourceController = $resourceController;
		$this->razorpay = new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
		ini_set('serialize_precision', -1);
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
	
	private function CheckVendor($vendorid,$accesstoken)
	{
		$vendor	=	DB::table('vendor_tbl')
						->where('vendorid','=',$vendorid)
						->where('accesstoken','=',$accesstoken)
						->first();
		return $vendor;
	}
	private function getDiscount()
	{
		$tierId 	= DB::table('city_tbl')->where('isdefault',1)->value('tierid');
		$cacheKey 	= "discount_tier_{$tierId}";
		$discount = Cache::remember($cacheKey, 60, function () use ($tierId) {
			return DB::table('tier_tbl')->where('tierid', $tierId)->value('discount');
		});		
		return $discount;
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

	private function States()
	{
		$states	=	DB::table('state_tbl')->orderby('statename')->get();
		return $states;
	}

	private function CheckCustomer($customerid,$accesstoken)
	{
		$customer	=	DB::table('customer_tbl')
						->where('customerid','=',$customerid)
						->where('accesstoken','=',$accesstoken)
						->first();
		return $customer;
	}

    public function myOrders(Request $request)
	{
        $rules = [
            'customerid'  		=> 'required|numeric',
			'accesstoken'  		=> 'required|numeric',
			'page'				=>	'nullable|numeric',
			'pagelimit'			=>	'nullable|numeric',
        ];

        $messages = [
            'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'customerid.numeric' 	=> 'INVALID CUSTOMER DETAIL',
            'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'accesstoken.numeric' 	=> 'INVALID ACCESS TOKEN',
			'page.numeric' 			=> 'PAGE NUMBER SHOULD BE NUMERIC',
			'pagelimit.numeric' 	=> 'PAGE LIMIT SHOULD BE NUMERIC',			
        ];

        $validatedData 	=	$request->validate($rules, $messages);

		$customerid		=	intval($request->input('customerid'));
		$accesstoken	=	(String) $request->input('accesstoken');

		
		
		$result			=	$this->CheckCustomer($customerid,$accesstoken);
		
		try
		{
			if($result)
			{
				$orderstatus	=	intval($request->input('orderstatus',0));				
				$page  			= 	intval($request->input('page',1));
				$pagelimit  	= 	intval($request->input('pagelimit',10));
				$totalpages		=	0;
				
				$appUrl	=	Config::get('app.url');		
				$results=	DB::table('customer_order_detail as a')
									->select('a.detailid','a.categoryid as subcategoryid','a.addondetailid','a.orderid','a.payable','a.servicedate','a.slottime','b.servicetitle','b.servicepic','a.orderstatus','a.vendorname','a.assignedtime as workerassignedtime','a.workstartpin','a.workstarttime','a.workendpin','a.workendtime','a.onholdreason','a.isrescheduled','a.customerid')
									->leftJoin('services as b', function($join) {
										$join->on('b.serviceid', '=', 'a.serviceid')
											 ->whereColumn('b.optionid', '=', 'a.optionid')
											 ->whereColumn('b.categoryid', '=', 'a.categoryid');
									})
									->where('a.customerid','=',$customerid)
									->when($orderstatus!=0,function($query) use ($orderstatus){
										return $query->where('a.orderstatus','=',$orderstatus);
									})
									->when($orderstatus==0,function($query) use ($orderstatus){
										return $query->where('a.orderstatus','=',$orderstatus);
									})
									->where('a.orderstatus','=',$orderstatus)
									->where('a.paymentstatus','=',1)
									->orderby('a.detailid','desc')
									->paginate($pagelimit, ['*'],'page',$page);
									
				foreach($results as $result)
				{
					$result->servicedate=	date('d\-m\-Y',strtotime($result->servicedate));
					$result->slottime	=	date('h:i A',strtotime($result->slottime));
					if($result->servicepic!='')
					{
						$result->servicepic = $appUrl."/storage/".$result->servicepic;
					}
					if($result->orderstatus==0)
					$result->orderstatus	=	'PENDING';
					else if($result->orderstatus==1)
					$result->orderstatus	=	'ASSIGNED';
					else if($result->orderstatus==2)
					$result->orderstatus	=	'STARTED';
					else if($result->orderstatus==3)
					$result->orderstatus	=	'COMPLETED';
					else if($result->orderstatus==4)
					$result->orderstatus	=	'ON HOLD';
					else if($result->orderstatus==-1)
					$result->orderstatus	=	'CANCELLED';
					else
					$result->orderstatus	=	'UNKNOWN';
					
					$result->receipts		=	DB::table('receipt_tbl')
												->select('receiptfile')
												->where('customerid','=',$result->customerid)
												->where('orderid','=',$result->orderid)
												->get();
					if($result->receipts)
					{
						foreach($result->receipts as $fls)
						{
							$fls->receiptfile	= asset('storage/receipts/'.$fls->receiptfile);
						}
					}
				}

				//return $results;
				/*
				switch($orderstatus)
				{
					case 0:
						$results	=	$this->getUnAssignedJob($customerid,$page,$pagelimit);
						$totalpages	=	$results->lastPage();					
						return response()->json(['message' => 'UN ASSIGNED JOBS','status'=>200,'customerid'=>$customerid,'accesstoken'=>$accesstoken,'totalpages'=>$totalpages,'data'=>$results->items()],200);
					case 1:
						$results	=	$this->getAssignedJob($customerid,$page,$pagelimit);
						$totalpages	=	$results->lastPage();					
						return response()->json(['message' =>'ASSIGNED JOBS','status'=>200,'customerid'=>$customerid,'accesstoken'=>$accesstoken,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
					case 2:
						$results	=	$this->getStartedJob($customerid,$page,$pagelimit);
						$totalpages	=	$results->lastPage();					
						return response()->json(['message' =>'STARTED JOBS LIST','status'=>200,'customerid'=>$customerid,'accesstoken'=>$accesstoken,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
					case 3:
						return response()->json(array_merge(['message' => 'ORDER STATUS NOT FOUND', 'status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
					case 4:
						return response()->json(array_merge(['message' => 'ORDER STATUS NOT FOUND', 'status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
					case -1:
						return response()->json(array_merge(['message' => 'ORDER STATUS NOT FOUND', 'status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
					default:
						return response()->json(array_merge(['message' => 'INVALID ORDER STATUS PROVIDED','status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
				}				
				*/
				return response()->json(['message' =>'ORDER LIST','status'=>200,'customerid'=>$customerid,'accesstoken'=>$accesstoken,'data'=>$results->items()], 200);
			}
			else
			{
				return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 401);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message'=>$e->getMessage(),'status'=>400],400);
		}
    }
	
	
    public function storeBooking(Request $request)
	{
        $rules = [
            'customerid'  	=> 'required|numeric',
			'accesstoken'  	=> 'required|numeric',
			'serviceid'  	=> 'required|numeric',
        ];

        $messages = [
            'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'customerid.numeric' 	=> 'INVALID CUSTOMER DETAIL',
            'serviceid.required' 	=> __('validation.thisis.required'),
			'serviceid.numeric' 	=> __('validation.thisis.numeric'),
            'accesstoken.required' 	=> __('validation.thisis.required'),
			'accesstoken.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validatedData = $request->validate($rules, $messages);
		
		
        $serviceid   	= 	intval($request->input('serviceid'));
		$customerid   	= 	intval($request->input('customerid'));
		$accesstoken   	= 	$request->input('accesstoken');

	
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		$recorddate		= 	$currentDateTime->format('Y-m-d');
		
		
		try
		{
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$service	=	DB::table('service_tbl')->where('serviceid','=',$serviceid)->first();

			$isexist	=	DB::table('customer_tbl')
								->select('customerid','name','mobilenumber','stateid','cityid','completeaddress','postalcode','profilepic')
								->where('customerid','=',$customerid)
								->where('accesstoken','=',$accesstoken)
								->first();
			if($isexist)
			{
				if($isexist->profilepic!='')
				{
					$appUrl = Config::get('app.url');
					$isexist->profilepic = $appUrl."/storage/".$isexist->profilepic;				
				}
				$vendorname=	"";
				DB::insert('insert into customer_order(customerid,categoryid,serviceid,visitingcharge,mrp,taxid,orderdate,vendorname,ip_country,ip_countrycode,ip_region,ip_regionname,ip_city,ip_zip,ip_lat,ip_lon,ip_timezone,ip_isp,recorddate,cancellationremark) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$customerid,$service->categoryid,$serviceid,$service->visitingcharge,$service->mrp,$service->taxid,$creationdate,$vendorname,$ipdata['country'],$ipdata['countryCode'],$ipdata['region'],$ipdata['regionName'],$ipdata['city'],$ipdata['zip'],$ipdata['lat'],$ipdata['lon'],$ipdata['timezone'],$ipdata['isp'],$recorddate,'']);
				
				$orderid = DB::getPdo()->lastInsertId();
			
				return response()->json(['message' =>__('messages.orderstored'),'status'=>200,'orderid'=>$orderid,'customer'=>$isexist], 200);
			}
			else
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>401,'customerid'=>$customerid], 401);
			}
			
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>__('messages.duplicateorder'),'status'=>400], 400);
		}			

    }
    public function cancelOrder(Request $request)
	{
        $rules = [
            'customerid'  		=> 'required|numeric',
			'accesstoken'  		=> 'required|numeric',
			'orderid'  			=> 'required|numeric',
			'detailid'  		=> 'required|numeric',
			'cancellationremark'=> 'required',
        ];

        $messages = [
            'customerid.required' 			=> __('validation.thisis.required'),
			'customerid.numeric' 			=> __('validation.thisis.numeric'),
            'orderid.required' 				=> __('validation.thisis.required'),
			'orderid.numeric' 				=> __('validation.thisis.numeric'),
            'detailid.required' 			=> __('validation.thisis.required'),
			'detailid.numeric' 				=> __('validation.thisis.numeric'),
            'accesstoken.required' 			=> __('validation.thisis.required'),
			'accesstoken.numeric' 			=> __('validation.thisis.numeric'),
            'cancellationremark.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData 		= 	$request->validate($rules,$messages);

		$customerid         = (int) $validatedData['customerid'];
		$accesstoken        = $validatedData['accesstoken'];
		$orderid            = (int) $validatedData['orderid'];
		$detailid           = (int) $validatedData['detailid'];
		$cancellationremark = $validatedData['cancellationremark'];
		$creationdate       = now()->format('Y-m-d H:i:s');

		try 
		{
			// Check if customer exists and token is valid
			$customer = DB::table('customer_tbl')
				->select('customerid')
				->where('customerid', $customerid)
				->where('accesstoken', $accesstoken)
				->first();

			if (!$customer) {
				return response()->json([
					'message'    => __('messages.unauthorized'),
					'status'     => 401,
					'customerid' => $customerid,
					'accesstoken'=> $accesstoken
				], 401);
			}

			// Update order detail if not locked and belongs to user
			$affectedRows = DB::table('customer_order_detail')
				->where('orderid', $orderid)
				->where('detailid', $detailid)
				->where('islocked', 0)
				->update([
					'orderstatus'        => -1,
					'cancellationremark' => $cancellationremark,
					'iscancelled'        => 1,
					'canceldatetime'     => $creationdate,
				]);

			if($affectedRows > 0)
			{
				return response()->json([
					'message'    => __('messages.cancelled'),
					'status'     => 200,
					'orderid'    => $orderid,
					'detailid'   => $detailid,
					'customerid' => $customerid,
					'accesstoken'=> $accesstoken,
				], 200);
			}
			else
			{
				return response()->json([
					'message'    => 'RECORD COULD NOT BE UPDATED',
					'status'     => 400,
					'orderid'    => $orderid,
					'detailid'   => $detailid,
					'customerid' => $customerid,
					'accesstoken'=> $accesstoken,
				], 400);
			}
		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => 'WE ENCOUNTERED AN ISSUE WHILE PROCESSING YOUR REQUEST. PLEASE TRY AGAIN LATER OR CONTACT SUPPORT.',
				'status' => 400
			], 400);
		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'SOMETHING WENT WRONG ON OUR END. PLEASE TRY AGAIN IN A MOMENT OR CONTACT SUPPORT.',
				'error' => $e->getMessage(),
				'status' => 500
			], 500);
		}
    }
	
    public function signUpVerification(Request $request)
    {
        $rules = [
            'name'  		=> 'required',
			'lastname'  	=> 'required',
			'stateid'  		=> 'nullable',
			'cityid'  		=> 'nullable',
			'address'  		=> 'nullable',
			'pincode'  		=> 'nullable',
			'email'  		=> 'nullable|email',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
            'otp' 			=> 'required',
        ];

        $messages = [
			'name.required' 		=> 'NAME IS REQUIRED',
			'lastname.required' 	=> 'LAST NAME IS REQUIRED',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
            'otp.required' 			=> 'PLEASE ENTER RECEIVED OTP',
			'email.email' 			=> 'INVALID EMAIL ADDRESS',
        ];

        $validatedData = $request->validate($rules, $messages);

        try
        {
			$mobilenumber 	= 	$request->input('mobilenumber');
			$otp 			= 	$request->input('otp');
			$customer 		= 	DB::table('customer_tbl')
								->where('mobilenumber', $mobilenumber)
								->where('otp', $otp)
								->first();			
			if (!$customer)
            {
				return response()->json(['message' =>__('messages.invalidmobileotp'),'status' => 400,'mobilenumber' => $mobilenumber,'otp' => $otp,], 400);
			}			
			
			if ($customer->isactive == 0)
			{
				return response()->json(['message' => __('messages.inactivecustomeraccount'),'status' => 201,'mobilenumber' => $mobilenumber,], 201);
			}			

			DB::transaction(function () use ($request, $customer) {
				// Update customer profile
				DB::table('customer_tbl')
					->where('customerid', $customer->customerid)
					->update([
						'name'             => $request->input('name') ?? '',
						'middlename'       => $request->input('middlename') ?? '',
						'lastname'         => $request->input('lastname') ?? '',
						'stateid'          => $request->input('stateid') ?? 0,
						'cityid'           => $request->input('cityid') ?? 0,
						'completeaddress'  => $request->input('address') ?? '',
						'postalcode'       => $request->input('pincode') ?? '',
						'email'            => $request->input('email') ?? '',
						'isverified'       => 1,
					]);

              
				// Insert address only if not already inserted
				$addressExists = DB::table('customer_address')
					->where('customerid', $customer->customerid)
					->where('ismaster',1)
					->exists();

				if (!$addressExists) {
					DB::table('customer_address')->insert([
						'stateid'    => $request->input('stateid') ?? 0,
						'cityid'     => $request->input('cityid') ?? 0,
						'postalcode' => $request->input('pincode') ?? '',
						'address'    => $request->input('address') ?? '',
						'customerid' => $customer->customerid,
						'ismaster'   => 1,
					]);
				}
			});				

			$updatedCustomer =	DB::table('customer_tbl')
								->select('customerid', 'name', 'accesstoken', 'profilepic', 'isactive', 'mobilenumber')
								->where('customerid', $customer->customerid)
								->first();			
			
			
			return response()->json(['message' =>__('messages.signupsuccess'),'status'=>200,'customer'=>$updatedCustomer,],200);			
        }
        catch(QueryException $e) 
        {
			Log::error('Signup Verification Failed: ' . $e->getMessage());
            return response()->json(['message' =>'We are unable to process your request at the moment or mobile number is already registered. Please try again shortly.','status'=>400], 400);
        }
    }
    public function signInOtp(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
        ];
		
        $validatedData 	= 	$request->validate($rules, $messages);
		
		$mobilenumber = $request->input('mobilenumber');
		
        try
        {

			$customer = DB::table('customer_tbl')
						->select('customerid', 'mobilenumber')
						->where('mobilenumber', $mobilenumber)
						->where('isverified', 1)
						->first();
			if (!$customer)
			{
				return response()->json(['message' => 'YOUR MOBILE NUMBER IS NOT REGISTERED. PLEASE USE SIGN UP LINK.','status' => 200,'mobilenumber' => $mobilenumber], 200);
			}
			$otp = $this->getOTP();
			$accesstoken = rand(10000000, 99999999);

			$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');
			
			if ($isSent) {
				DB::table('customer_tbl')
					->where('customerid', $customer->customerid)
					->update([
						'otp' => $otp,
						'accesstoken' => $accesstoken
					]);

				return response()->json([
					'message' => __('messages.otpgenerated'),
					'status' => 200,
					'mobilenumber' => $mobilenumber
				], 200);
			}
			else
			{
				return response()->json([
					'message' => 'We couldn’t send the OTP. Please try again shortly.',
					'status' => 400,
					'mobilenumber' => $mobilenumber
				], 400);
			}			
        }
        catch (QueryException $e) 
        {
			Log::error('SignIn OTP Error: ' . $e->getMessage());
            return response()->json(['message' =>'Unable to process request at the moment. Please try again.','status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function signInVerification(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'otp'  			=> 'required|numeric',
        ];

        $messages = [
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'otp.required' 			=> 'OTP IS REQUIRED',
			'otp.numeric' 			=> 'INVALID OTP PROVIDED',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$mobilenumber = $request->input('mobilenumber');
		$otp          = $request->input('otp');
		
        try
        {
			$customer = DB::table('customer_tbl')
						->select('customerid', 'name', 'accesstoken', 'profilepic', 'isactive', 'mobilenumber')
						->where('mobilenumber', $mobilenumber)
						->where('otp', $otp)
						->first();

			if (!$customer) {
				return response()->json([
					'message'      => __('messages.invalidmobotp'),
					'status'       => 400,
					'mobilenumber' => $mobilenumber
				], 400);
			}			
			if ($customer->isactive == 0) {
				return response()->json([
					'message'      => __('messages.inactivecustomer'),
					'status'       => 201,
					'mobilenumber' => $mobilenumber
				], 201);
			}

			// Format profile picture if exists
			if (!empty($customer->profilepic)) {
				$customer->profilepic = Config::get('app.url') . Storage::url($customer->profilepic);
			}

			return response()->json([
				'message'  => __('messages.loginsuccess'),
				'status'   => 200,
				'customer' => $customer
			], 200);
			
        }
        catch (QueryException $e) 
        {
			Log::error('SignInVerification Error: ' . $e->getMessage());
            return response()->json(['message' =>'Unable to verify login at the moment. Please try again shortly.','status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
	
    public function signUp(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$mobilenumber = $request->input('mobilenumber');
		$currentDateTime = now();
		$creationdate = $currentDateTime->format('Y-m-d H:i:s');		

        try
        {
			$existingUser = DB::table('customer_tbl')
				->select('customerid', 'name', 'mobilenumber','isactive', 'isverified')
				->where('mobilenumber', $mobilenumber)
				->first();			
			if ($existingUser) {

				if ($existingUser->isactive == 0) {
					return response()->json(['message' => __('messages.inactivevendoraccount'), 'status' => 201, 'mobilenumber' => $mobilenumber], 201);
				}

				$otp = $this->getOTP();
				$accesstoken = rand(10000000, 99999999);

				$otpCacheKey = 'otp_' . $mobilenumber;
				if (Cache::has($otpCacheKey)) {
					//return response()->json(['message' => 'OTP already sent recently. Please wait for 2 minutes.', 'status' => 400], 400);
				}

				// Send OTP via SMS
				$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');
				if ($isSent) {

					DB::table('customer_tbl')
						->where('customerid', $existingUser->customerid)
						->update(['otp' => $otp, 'accesstoken' => $accesstoken]);

					Cache::put($otpCacheKey, $otp, 120);  // Store for 5 minutes

					// Check if the user is verified
					if ($existingUser->isverified == 0) {
						return response()->json(['message' => __('messages.otpgenerated'), 'status' => 200, 'mobilenumber' => $mobilenumber, 'goto' => true], 200);
					}

					return response()->json(['message' => __('messages.otpgenerated'), 'status' => 200, 'mobilenumber' => $mobilenumber, 'goto' => false], 200);
				}

				return response()->json(['message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN', 'status' => 400, 'mobilenumber' => $mobilenumber], 400);
			}			
			
			// IF NEW USER COMING
			$otp 			= $this->getOTP();
			$accesstoken 	= rand(10000000, 99999999);
			$password 		= rand(100000, 999999);			
			
			$otpCacheKey = 'otp_' . $mobilenumber;
			if (Cache::has($otpCacheKey)) {
				return response()->json(['message' => 'OTP already sent recently. Please try again later.', 'status' => 400], 400);
			}			
			
			$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');

			if($isSent)
			{
				// Store new user data in the database inside a transaction to ensure data integrity
				DB::transaction(function () use ($mobilenumber, $password, $otp, $accesstoken, $creationdate) {
					DB::table('customer_tbl')->insert([
						'mobilenumber' => $mobilenumber,
						'loginpassword' => $password,
						'otp' => $otp,
						'accesstoken' => $accesstoken,
						'fcmid' => '',
						'macid' => '',
						'deviceid' => '',
						'isactive' => 1,
						'creationdate' => $creationdate,
						'ip_country' => '',
						'ip_countrycode' => '',
						'ip_region' => '',
						'ip_regionname' => '',
						'ip_city' => '',
						'ip_zip' => '',
						'ip_lat' => '',
						'ip_lon' => '',
						'ip_timezone' => '',
						'ip_isp' => '',
					]);
				});
				// Cache the OTP for a short time to prevent frequent requests
				Cache::put($otpCacheKey, $otp, 120);  // Store for 5 minutes

				return response()->json(['message' => __('messages.otpgenerated'), 'status' => 200, 'mobilenumber' => $mobilenumber, 'goto' => true], 200);
			}

			return response()->json(['message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN', 'status' => 400, 'mobilenumber' => $mobilenumber], 400);
			
        }
        catch (QueryException $e) 
        {
			Log::error('Error during sign up: ' . $e->getMessage());
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function masterCategoryList(Request $request)
	{
		try
		{
			// Cache the top-level categories for 5 minutes (300 seconds)
			$category = Cache::remember('master_category_list', 300, function () {
				return DB::table('category_tbl')
					->select('categoryid', 'category')
					->where('parentcategoryid', 0)
					->orderBy('displayorder')
					->get();
			});
			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'categotylist'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			
    }
    public function getVendorCategory(Request $request)
	{
        $rules = [
            'vendorid'  		=> 'required',
			'accesstoken'  		=> 'required',
        ];

        $messages = [
            'vendorid.required' 	=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData 	= $request->validate($rules, $messages);
		
		try
		{
			$vendorid		= intval($request->input('vendorid'));
			$accesstoken		= (String) $request->input('accesstoken');
			
			$cacheKey = 'vendor_category_' . $vendorid;
			
			$categoryData = Cache::remember($cacheKey, 300, function () use ($vendorid, $accesstoken)
			{
				$vendor = DB::table('vendor_tbl')
					->select('vendorid', 'name', 'mobilenumber', 'isactive', 'isverified', 'isemployee', 'categoryids')
					->where('vendorid', $vendorid)
					->where('accesstoken', $accesstoken)
					->first();

				if(!$vendor)
				{
					return null;
				}

				if($vendor->isemployee==0)
				{
					
					$catIds = explode(',', $vendor->categoryids);

					
					$parentCategoryIds = DB::table('category_tbl')
						->select('parentcategoryid')
						->whereIn('categoryid', $catIds)
						->distinct()
						->pluck('parentcategoryid')
						->toArray();

					
					return DB::table('category_tbl')
						->select('categoryid', 'category')
						->where('parentcategoryid', 0)
						->whereIn('categoryid', $parentCategoryIds)
						->orderBy('displayorder')
						->get();
				}

				return null;
			});			
						
			if(!$categoryData)
			{
				return response()->json(['message' => __('messages.notfound'), 'status' => 400], 400);
			}			
			
			return response()->json(['message' => __('messages.recordlist'), 'status' => 200, 'categotylist' => $categoryData], 200);

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
			'searchtext'  		=> 'nullable',
        ];

        $messages = [
            'categoryids.required' 	=> 'CATEGORY ID IS REQUIRED',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$categoryids      = trim($request->input('categoryids'));
		$searchtext       = trim((string) $request->input('searchtext'));
		$categoryIdsArray = array_filter(explode(',', $categoryids));

		try
		{
			$cacheKey = 'sub_category_list_' . md5($categoryids . '_' . $searchtext);

			$category = Cache::remember($cacheKey, 300, function () use ($categoryIdsArray, $searchtext) {
				return DB::table('category_tbl')
					->select('categoryid as subcategoryid', 'category as subcategory')
					->where('parentcategoryid', '!=', 0)
					->when(!empty($categoryIdsArray), function ($query) use ($categoryIdsArray) {
						return $query->whereIn('parentcategoryid', $categoryIdsArray);
					})
					->when($searchtext !== '', function ($query) use ($searchtext) {
						return $query->where('category', 'like', '%' . $searchtext . '%');
					})
					->get();
			});

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'subcategotylist'=>$category], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			
    }

    public function searchService(Request $request)
	{
        $rules = [
            'searchtext'  		=> 'required',
        ];

        $messages = [
            'searchtext.required' 	=> 'SEARCH VALUE IS REQUIRED',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$searchtext = trim((string) $request->input('searchtext'));
		
		try
		{
			$cacheKey = 'search_service_' . md5($searchtext);

			$services = Cache::remember($cacheKey, 300, function () use ($searchtext) {
				return DB::table('service_tbl as a')
					->select(
						'b.parentcategoryid as categoryid',
						'a.categoryid as subcategoryid',
						'a.serviceid',
						'a.servicetitle'
					)
					->join('category_tbl as b', 'b.categoryid', '=', 'a.categoryid')
					->where('a.isactive',1)
					->when($searchtext!=='', function ($query) use ($searchtext) {
						return $query->where('a.servicetitle', 'like', '%' . $searchtext . '%');
					})
					->get();
			});


			
			
			return response()->json(['message' =>'SEARCH RESULT','status'=>200,'services'=>$services], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400],400);
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

		$categoryid = intval($request->input('categoryid'));
		$searchtext = (string) $request->input('searchtext');
		$appUrl = Config::get('app.url');
		$discount = $this->getDiscount();		

		try
		{			
			$parentname	=	"";

			if ($categoryid !== 0) {
				$parent = Cache::remember("category_parent_{$categoryid}", 300, function () use ($categoryid) {
					return DB::table('category_tbl')
						->select('category')
						->where('categoryid', $categoryid)
						->first();
				});

				if ($parent) {
					$parentname = $parent->category;
				} else {
					$categoryid = 0;
				}
			}
			
			$categoryCacheKey = 'category_list_' . $categoryid . '_' . md5($searchtext);

			$categoryList = Cache::remember($categoryCacheKey, 300, function () use ($categoryid, $searchtext) {
				return DB::table('category_tbl')
					->select(
						'categoryid', 'category', 'headingvalue', 'displayorder', 'description',
						'metakeywords', 'metadescription', 'categoryicon', 'categorypage'
					)
					->where('categorystatus', 1)
					->when($categoryid === 0, fn($q) => $q->where('parentcategoryid', 0))
					->when($categoryid !== 0, fn($q) => $q->where('parentcategoryid', $categoryid))
					->when($searchtext !== '', fn($q) => $q->where('category', 'like', '%' . $searchtext . '%'))
					->orderBy('displayorder')
					->get();
			});
			$categoryIds = $categoryList->pluck('categoryid')->toArray();
			$childCategoryMap = Cache::remember('category_child_map_' . implode('_', $categoryIds), 300, function () use ($categoryIds) {
				return DB::table('category_tbl')
					->select('parentcategoryid')
					->whereIn('parentcategoryid', $categoryIds)
					->distinct()
					->pluck('parentcategoryid')
					->flip();
			});			
			$allServices = [];
			foreach ($categoryIds as $cid) {
				$allServices[$cid] = Cache::remember("services_list_{$cid}", 300, function () use ($cid) {
					return DB::table('services as a')
						->select(
							'a.categoryid', 'a.serviceid', 'a.optionid', 'a.servicetitle',
							'a.mrp as servicecharge', DB::raw('0 as taxable'), 'a.servicepic',
							'a.likes', 'a.ratings', 'a.reviews', 'a.requiredtime', 'a.description',
							'b.taxrate', 'a.needs', 'a.includes', 'a.excludes',
							DB::raw('(select count(serviceid) from services where serviceid = a.serviceid and optionid != 0) as options')
						)
						->leftJoin('tax_tbl as b', 'b.taxid', '=', 'a.taxid')
						->where('a.categoryid', $cid)
						->where('a.optionid', 0)
						->where('a.isactive', 1)
						->orderby('a.displayorder')
						->get();
				});
			}

			foreach ($categoryList as $cat) {
				$cat->categoryicon = $cat->categoryicon ? $appUrl . "/storage/" . $cat->categoryicon : "";
				$cat->categorypage = $cat->categorypage ? $appUrl . "/storage/" . $cat->categorypage : "";
				$cat->haschild = isset($childCategoryMap[$cat->categoryid]) ? 1 : 0;

				$services = $allServices[$cat->categoryid] ?? collect();

				foreach ($services as $ser) {
					$ser->servicepic = $ser->servicepic ? $appUrl . "/storage/" . $ser->servicepic : "";
					$ser->taxable = bcdiv($ser->servicecharge - (($ser->servicecharge * $discount) / 100), 1, 2);
				}

				$cat->services = $services->values(); // reindex
			}

			


			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'parentname'=>$parentname,'categotylist'=>$categoryList], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400],400);
		}			

    }
	

    public function subscribeCategoryList(Request $request)
	{
		
		try
		{
			$categories = DB::table('category_tbl')
				->select('categoryid', 'category')
				->whereNotIn('categoryid', function ($query) {
					$query->select('parentcategoryid')
						  ->from('category_tbl')
						  ->whereNotNull('parentcategoryid'); // Optimization: skip nulls
				})
				->whereIn('categoryid', function ($query) {
					$query->select('categoryid')
						  ->from('service_tbl')
						  ->where('isactive', 1); // Optional: filter only active services
				})
				->orderBy('displayorder') // Added ordering if required
				->get();

			return response()->json(['message'=> __('messages.recordlist'),'status'=>200,'categotylist' => $categories], 200);
		}
		catch(QueryException $e)
		{
			Log::error('Subscribe Category List Error: ' . $e->getMessage());
			return response()->json(['message' =>'Unable to fetch category list. Please try again later.','status'=>400], 400);
		}			

    }

    public function cityList(Request $request)
	{
		
		try
		{
			$stateid		=	intval($request->input('stateid'));
			$searchtext		=	(String) $request->input('searchtext');
			
			$citylist = DB::table('city_tbl')
						->select('cityid','cityname','aliasname','isdefault','operatingstatus','tierid')
						->when($stateid!=0,function($query) use ($stateid){
							return $query->where('stateid','=',$stateid);
						})
						->when($searchtext!='',function($query) use ($searchtext){
							return $query->where('cityname','like','%'.$searchtext.'%');
						})
						->orderBy('cityname')
						->get();

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'citylist'=>$citylist], 200);
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

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'arealist'=>$arealist], 200);
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
			Artisan::call('cache:clear');
			$statelist = DB::table('state_tbl')->select('stateid','statename','aliasname')->where('stateid','=',1)->orderby('statename')->get();

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'statelist'=>$statelist,'hased'=>HASH::make('admin')], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>__('messages.notfound'),'status'=>400], 400);
		}			
    }

	
	/* VENDOR REGISTRATION API */
    public function generateVendorOtp(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
            'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
        ];

        $validatedData = $request->validate($rules, $messages);

		$mobilenumber    = $request->input('mobilenumber');
		$name            = "";
		$middlename      = "";
		$lastname        = "";
		$creationdate    = now()->format('Y-m-d H:i:s');		

        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);
			$vendor 	= 	DB::table('vendor_tbl')
								->select('vendorid', 'name', 'mobilenumber', 'isactive', 'isverified')
								->where('mobilenumber', '=', $mobilenumber)
								->first();
			$otp        =	$this->getOTP();
			$accesstoken=	rand(10000000, 99999999);

			$isSent 	=	$this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');
			if(!$isSent)
			{
				return response()->json([
					'message'      => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
					'status'       => 400,
					'mobilenumber' => $mobilenumber
				], 400);
			}

			if($vendor)
			{
				if ($vendor->isverified == 1)
				{
					return response()->json([
						'message'      => 'ALREADY REGISTERED',
						'status'       => 400,
						'mobilenumber' => $mobilenumber
					], 400);
				}

				// Update existing vendor
				DB::table('vendor_tbl')
					->where('vendorid', $vendor->vendorid)
					->update([
						'otp'         => $otp,
						'accesstoken' => $accesstoken
					]);

				return response()->json([
					'message'      => __('messages.otpgenerated'),
					'status'       => 200,
					'mobilenumber' => $mobilenumber
				], 200);
			}
			$categories	=	DB::table('category_tbl')
							->select('categoryid', 'category')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->orderBy('displayorder')
							->get();

			DB::table('vendor_tbl')->insert([
				'name'           => $name,
				'middlename'     => $middlename,
				'lastname'       => $lastname,
				'mobilenumber'   => $mobilenumber,
				'otp'            => $otp,
				'accesstoken'    => $accesstoken,
				'isactive'       => 1,
				'creationdate'   => $creationdate,
				'ip_country'     => $ipdata['country'] ?? '',
				'ip_countrycode' => $ipdata['countryCode'] ?? '',
				'ip_region'      => $ipdata['region'] ?? '',
				'ip_regionname'  => $ipdata['regionName'] ?? '',
				'ip_city'        => $ipdata['city'] ?? '',
				'ip_zip'         => $ipdata['zip'] ?? '',
				'ip_lat'         => $ipdata['lat'] ?? '',
				'ip_lon'         => $ipdata['lon'] ?? '',
				'ip_timezone'    => $ipdata['timezone'] ?? '',
				'ip_isp'         => $ipdata['isp'] ?? '',
				'areaids'        => '',
				'fcmid'          => '',
			]);

			return response()->json([
				'message'      => __('messages.otpgenerated'),
				'status'       => 200,
				'mobilenumber' => $mobilenumber,
				'category'     => $categories
			], 200);
        }
        catch (QueryException $e) 
        {
			Log::error('Generate Vendor OTP Error: ' . $e->getMessage());
            return response()->json(['message' =>'We are facing a temporary issue while sending the OTP. Please try again in a moment.','status'=>400,'name'=>$name,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function vendorOtpVerification(Request $request)
    {
        $rules = [
            'mobilenumber' 		=> 'required',
            'otp' 				=> 'required',
			'name'  			=> 'required|regex:/^[a-zA-Z\s]+$/|max:50',
			'middlename'  		=> 'nullable|max:30',
			'lastname'  		=> 'nullable|max:30',
			'stateid' 			=> 'required',
			'cityid' 			=> 'required',
			'address' 			=> 'required',
			'pincode' 			=> 'required|numeric',
			'subcategoryids'	=> 'required',
        ];

        $messages = [
            'mobilenumber.required' 	=> 'MOBILE NUMBER IS REQUIRED',
            'otp.required' 				=> 'OTP IS REQUIRED',
            'name.required' 			=> 'PLEASE ENTER NAME',
			'name.regex' 				=> 'INVALID NAME VALUE',
			'name.max' 					=> 'MAXIMUM LENGTH IS 50',
			'middlename.max' 			=> 'MAXIMUM LENGTH IS 30',
			'lastname.max' 				=> 'MAXIMUM LENGTH IS 30',
			'stateid.required' 			=> 'PLEASE SELECT STATE NAME',
			'cityid.required' 			=> 'PLEASE SELECT CITY NAME',
			'address.required' 			=> 'PLEASE ENTER ADDRESS DETAIL',
			'pincode.required' 			=> 'PLEASE ENTER PIN CODE',
			'pincode.numeric' 			=> 'PLEASE ENTER VALID PIN CODE',
			'subcategoryids.required'	=> 'PLEASE SELECT ATLEAST ONE CATEGORY NAME',
        ];

        $validated 	=	$request->validate($rules, $messages);

		try
        {
			$vendor = DB::table('vendor_tbl')
                    ->select('vendorid', 'profilepic', 'accesstoken')
                    ->where('mobilenumber', $validated['mobilenumber'])
                    ->where('otp', $validated['otp'])
                    ->first();			
			if (!$vendor) {
				return response()->json([
					'message' => 'Invalid OTP or mobile number.',
					'status' => 401,
				]);
			}

			DB::table('vendor_tbl')
				->where('vendorid', $vendor->vendorid)
				->update([
					'name'             => $validated['name'],
					'middlename'       => $validated['middlename'],
					'lastname'         => $validated['lastname'],
					'stateid'          => $validated['stateid'],
					'cityid'           => $validated['cityid'],
					'completeaddress'  => $validated['address'],
					'postalcode'       => $validated['pincode'],
					'categoryids'      => $validated['subcategoryids'],
					'isverified'       => 1,
				]);
			$updatedVendor	=	DB::table('vendor_tbl')
									->select(
										'vendorid', 'name', 'middlename', 'lastname', 'mobilenumber', 'isactive',
										'verificationstatus', 'isverified', 'isemployee', 'isselfemployeed',
										'accesstoken', 'profilepic'
									)
									->where('vendorid', $vendor->vendorid)
									->first();
			if (!empty($updatedVendor->profilepic)) {
				$updatedVendor->profilepic = url('storage/' . $updatedVendor->profilepic);
			}

			return response()->json([
				'message' => __('messages.stored'),
				'status' => 200,
				'vendor' => $updatedVendor,
			]);
        }
        catch(QueryException $e) 
        {
			Log::error('Vendor OTP Verification DB Error: ' . $e->getMessage());
            return response()->json(['message' =>'We are currently facing a technical issue. Kindly try again shortly.','status'=>400,'name'=>$name,'mobilenumber'=>$mobilenumber], 400);
        }
    }


    public function contactForm(Request $request)
	{
        $rules = [
            'name'  		=> 'required|regex:/^[a-zA-Z\s]+$/|max:50',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email' 		=> 'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/|max:100',
			'message'  		=> 'required',
        ];

        $messages = [
            'name.required' 		=> 'PLEASE ENTER NAME',
			'name.regex' 			=> 'PLEASE ENTER VALID NAME',
			'name.max' 				=> 'PLEASE ENTER VALID NAME',
			'mobilenumber.required' => 'PLEASE ENTER MOBILE NUMBER',
			'mobilenumber.regex' 	=> 'PLEASE ENTER VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
            'email.required' 		=> 'PLEASE ENTER EMAIL ADDRESS',
			'email.regex' 			=> 'PLEASE PROVIDE VALID EMAIL',
			'message.required' 		=> 'PLEASE ENTER YOUR MESSAGE',
        ];

        $validatedData 	= $request->validate($rules, $messages);
		
        $name   		= 	(String) $request->input('name');
		$mobilenumber   = 	(String) $request->input('mobilenumber');
		$email   		= 	(String) $request->input('email');
		$message   		= 	(String) $request->input('message');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		
		try
		{
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			DB::insert('insert into contact_tbl(name,mobilenumber,email,message,creationdate,ip_country,ip_countrycode,ip_region,ip_regionname,ip_city,ip_zip,ip_lat,ip_lon,ip_timezone,ip_isp) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$name,$mobilenumber,$email,$message,$creationdate,$ipdata['country'],$ipdata['countryCode'],$ipdata['region'],$ipdata['regionName'],$ipdata['city'],$ipdata['zip'],$ipdata['lat'],$ipdata['lon'],$ipdata['timezone'],$ipdata['isp']]);
			
			return response()->json(['message' =>__('messages.stored'),'status'=>200], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			

    }
	public function sendOTPMessage(Request $request)
	{
		$otp 			= "555555";//rand(100000,999999);
		$mobilenumber 	= "9479020075";  // Ensure this is correct and formatted with country code
		//$body 		= "Hi {#var#}, your booking is confirmed! ID: $otp, you can track it here from {#var#}. Regards, Nexify World";
		//$body 			= "Hi User, your booking is confirmed! ID: $otp, you can track it here from here. Regards, Nexify World";

		try 
		{
			//$isSent 		= 	$this->smsService->sendOtp($mobilenumber,$otp);
			if($isSent)
			{
				return response()->json(['message' =>'Message Sent Successfully','status'=>200], 200);
			}
			else
			{
				return response()->json(['message' =>__('messages.smsfailed'),'status'=>400], 400);
			}
		} 
		catch (QueryException $e) 
		{
			return response()->json(['message' => $e->getMessage(), 'status' => 400], 400);
		}
	}	
	
	/*VENDOR SIGN IN*/
    public function vendorSignInOtp(Request $request)
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
		
		$mobilenumber = $request->input('mobilenumber');
		$creationdate = now()->format('Y-m-d H:i:s');		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$vendor	=	DB::table('vendor_tbl')
						->where('mobilenumber', $mobilenumber)
						->first();
			if($vendor)
			{
				if ($vendor->isverified == 0) {
					return response()->json([
						'message' => __('messages.inactivevendor'),
						'status'  => 201,
						'mobilenumber' => $mobilenumber
					], 201);
				}
				$otp = $this->getOTP();
				$accesstoken = rand(10000000, 99999999);

				$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');

				if (!$isSent) {
					return response()->json([
						'message' => 'Unable to send OTP at the moment. Please try again.',
						'status'  => 400,
						'mobilenumber' => $mobilenumber
					], 400);
				}

				DB::update('UPDATE vendor_tbl SET otp = ?, accesstoken = ? WHERE vendorid = ?', [
					$otp, $accesstoken, $vendor->vendorid
				]);

				return response()->json([
					'message'      => __('messages.otpgenerated'),
					'status'       => 200,
					'mobilenumber' => $mobilenumber,
					'accesstoken'  => $accesstoken
				], 200);
			}
			else
			{
				return response()->json([
					'message'      => 'This mobile number is not registered. Please sign up first.',
					'status'       => 401,
					'mobilenumber' => $mobilenumber
				], 200);
			}
        }
        catch (QueryException $e) 
        {
			Log::error('Vendor SignIn OTP Error: ' . $e->getMessage());
            return response()->json(['message' =>'We are facing a temporary issue. Please try again shortly.','status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function vendorSignInVerification(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'otp'  			=> 'required|numeric',
        ];

        $messages = [
			'mobilenumber.required' => 'PLEASE ENTER MOBILE NUMBER',
            'mobilenumber.regex' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'otp.required' 			=> 'PLEASE ENTER RECEIVED OTP',
			'otp.numeric' 			=> 'OTP SHOULD BE A NUMERIC VALUE',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$mobilenumber = $request->input('mobilenumber');
		$otp          = $request->input('otp');		

        try
        {
			$vendor	=	DB::table('vendor_tbl')
							->select(
								'vendorid',
								'name',
								'email',
								'mobilenumber',
								'isactive',
								'verificationstatus',
								'isverified',
								'isemployee',
								'isselfemployeed',
								'accesstoken',
								'profilepic'
							)
							->where('mobilenumber', $mobilenumber)
							->where('otp', $otp)
							->first();

			if($vendor)
			{
				if($vendor->isactive==0)
				{
					return response()->json([
						'message' => __('messages.inactivevendor'),
						'status' => 201,
						'mobilenumber' => $mobilenumber
					], 201);
				}

				if(!empty($vendor->profilepic))
				{
					$appUrl = Config::get('app.url');
					$vendor->profilepic = $appUrl . Storage::url($vendor->profilepic);
				}

				return response()->json([
					'message' => __('messages.loginsuccess'),
					'status' => 200,
					'vendor' => $vendor
				], 200);
			}
			else
			{
				return response()->json([
					'message' => __('messages.invalidmobotp'),
					'status' => 400,
					'mobilenumber' => $mobilenumber
				], 400);
			}
        }
        catch(QueryException $e)
        {
			Log::error('Vendor Sign-In Verification Error: ' . $e->getMessage());
            return response()->json(['message' =>'We could not complete the request. Please try again in a moment.','status'=>400,'mobilenumber'=>$mobilenumber], 400);
        }
    }
	
	public function getEmployeeForm(Request $request)
	{
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
			'employeeid'  	=> 'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
			'employeeid.required' 	=> 'PLEASE PROVIDE EMPLOYEE DETAIL',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$vendorid     = $request->input('vendorid');
		$accesstoken  = $request->input('accesstoken');
		$employeeid   = $request->input('employeeid');
		
        try
        {
			$vendor = $this->CheckVendor($vendorid, $accesstoken);
			if(!$vendor)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'status' => 401,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}			
			if($vendor->isemployee==1)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'status' => 401,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}			
			$employee	=	DB::table('vendor_tbl')
								->select([
									'vendorid', 'name', 'middlename', 'lastname', 'mobilenumber',
									'alternetnumber', 'email', 'gender', 'dob', 'stateid', 'cityid',
									'areaids', 'postalcode', 'completeaddress', 'aadhaarnumber',
									'aadhaarfrontfile', 'aadhaarbackfile', 'pannumber', 'panfile',
									'drivinglicence', 'licencefile', 'bankname', 'accountnumber',
									'bankfile', 'ifsccode', 'bankbranch', 'profilepic', 'categoryids',
									'latitude', 'longitude', 'accesstoken', 'isemployee',
									'verificationstatus', 'employeecatids', 'experience',
									'nomineename', 'relation', 'nomineedob', 'documentfile'
								])
								->where('vendorid', $employeeid)
								->where('parentvendorid', $vendorid)
								->first();			

			if(!$employee)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'status' => 401,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'employeeid' => $employeeid
				], 401);
			}			
			$fileFields = [
				'profilepic', 'aadhaarfrontfile', 'aadhaarbackfile','panfile', 'licencefile', 'bankfile', 'documentfile'
			];			
			foreach ($fileFields as $field) {
				if (!empty($employee->$field)) {
					$employee->$field = Config::get('app.url') . Storage::url($employee->$field);
				}
			}

			$statelist 	= 	$this->States();
			$categories	=	DB::table('category_tbl')
								->select('categoryid', 'category')
								->whereIn('categoryid', explode(',', $vendor->categoryids))
								->get();

			return response()->json([
				'message' => __('messages.validaccess'),
				'status' => 200,
				'statelist' => $statelist,
				'categorylist' => $categories,
				'employeedetail' => $employee
			], 200);
        }
        catch(QueryException $e) 
        {
			Log::error('Get Employee Form Error: ' . $e->getMessage());
            return response()->json(['message'=>'We were unable to fetch the information. Please try again shortly.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }		
	}
    public function registerEmployee(Request $request)
	{
        $rules = [
			'vendorid'		=> 'required',
			'accesstoken'	=> 'required',
			'profilepic'	=> 'required',
            'name'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'gender' 		=> 'required',
			'dob'  			=> 'nullable|date',
			'categoryids'	=> 'required',
			'aadhaarnumber'	=> 'required|numeric|digits:12',
			'pannumber'		=> 'nullable',
			'drivinglicence'=> 'nullable',
			'stateid'		=> 'required',
			'cityid'		=> 'required',
			'pincode'		=> 'required|numeric',
			'address'		=> 'required',
			'alternetnumber'=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'profilepic.required' 	=> 'PROFILE PICTURE IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'INVALID NAME',
			'middlename.regex' 		=> 'INVALID NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 		=> 'INVALID NAME',
			'lastname.max' 			=> 'MAXIMUM LENGTH IS 30',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'gender.required' 		=> 'GENDER IS REQUIRED',
			'dob.required' 			=> 'DATE OF BIRTH IS REQUIRED',
			'dob.date' 				=> 'MUST BE A DATE VALUE',
			'categoryids.required'	=> 'CATEGORY NAME IS REQUIRED',
			'aadhaarnumber.required'=> 'AADHAAR NUMBER IS REQUIRED',
			'aadhaarnumber.numeric'	=> 'AADHAAR SHOULD BE A NUMERIC VALUE ONLY',
			'aadhaarnumber.digits' 	=> 'INVALID AADHAAR NUMBER',
			'drivinglicence.required'=> 'DRIVING LICENCE NUMBER IS REQUIRED',
			'pannumber.required'	=> 'PAN NUMBER IS REQUIRED',
			'bankname.required'		=> 'BANK NAME IS REQUIRED',
			'bankaccount.required'	=> 'BANK ACCOUNT IS REQUIRED',
			'bankaccount.numeric'	=> 'INVALID BANK ACCOUNT',
			'ifsccode.required'		=> 'IFSC IS REQUIRED',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'address.required'		=> 'ADDRESS IS REQUIRED',
			'alternetnumber.regex'	=> 'INVALID ALTERNATE NUMBER',
			'alternetnumber.digits'	=> 'INVALID ALTERNATE NUMBER',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$vendorid    = $request->input('vendorid');
		$accesstoken = $request->input('accesstoken');		
		$vendor = $this->CheckVendor($vendorid, $accesstoken);
		if (!$vendor) {
			return response()->json(['message' => __('messages.unauthorized'), 'status' => 401], 401);
		}
		$input = $request->only([
			'name', 'middlename', 'lastname', 'mobilenumber', 'gender', 'dob',
			'categoryids', 'experience', 'aadhaarnumber', 'drivinglicence',
			'pannumber', 'bankname', 'bankaccount', 'ifsccode', 'stateid', 'cityid',
			'areaid', 'address', 'pincode', 'alternetnumber', 'nomineename',
			'relation', 'nomineedob'
		]);
		
		$input['middlename']      	= 	$input['middlename'] ?? '';
		$input['lastname']        	= 	$input['lastname'] ?? '';
		$input['dob']             	= 	$input['dob'] ?? '';
		$input['pannumber']       	= 	$input['pannumber'] ?? '';
		$input['drivinglicence']  	= 	$input['drivinglicence'] ?? '';
		$input['alternetnumber']  	= 	$input['alternetnumber'] ?? '';
		$input['experience']      	= 	$input['experience'] ?? '';
		$input['areaid']         	= 	$input['areaid'] ?? '';
		$input['nomineename']     	=	$input['nomineename'] ?? '';
		$input['relation']        	=	$input['relation'] ?? '';
		$input['nomineedob']      	=	$input['nomineedob'] ?? '';

		$exists = DB::table('vendor_tbl')
			->when($input['aadhaarnumber'], fn($q) => $q->orWhere('aadhaarnumber', $input['aadhaarnumber']))
			->when($input['mobilenumber'], fn($q) => $q->orWhere('mobilenumber', $input['mobilenumber']))
			->when($input['pannumber'], fn($q) => $q->orWhere('pannumber', $input['pannumber']))
			->when($input['drivinglicence'], fn($q) => $q->orWhere('drivinglicence', $input['drivinglicence']))
			->exists();
		if($exists)
		{
			return response()->json([
				'message' => 'A DUPLICATE MOBILE NUMBER, AADHAAR NUMBER, PAN NUMBER, OR DRIVING LICENCE NUMBER WAS FOUND. PLEASE VERIFY YOUR INFORMATION.',
				'status' => 409
			], 409);
		}

		try
		{
			DB::beginTransaction();

			$files = [];
			$storeFile = fn($name, $path) =>
				$request->hasFile($name) ? $request->file($name)->store($path, 'public') : '';

			$files['profilepic']        = $storeFile('profilepic', 'uploads/employeeprofiles');
			$files['aadhaarfrontfile']  = $storeFile('aadhaarfrontfile', 'uploads/employeeaadharfiles');
			$files['aadhaarbackfile']   = $storeFile('aadhaarbackfile', 'uploads/employeeaadharbackfiles');
			$files['panfile']           = $storeFile('panfile', 'uploads/employeepanfiles');
			$files['bankfile']          = $storeFile('bankfile', 'uploads/bankfiles');
			$files['licencefile']       = $storeFile('licencefile', 'uploads/licencefiles');
			$files['documentfile']      = $storeFile('documentfile', 'uploads/nomineefiles');

			$ipAddress = request()->ip();
			$ipdata    = $this->getIpDetail($ipAddress);
			$now       = now()->format('Y-m-d H:i:s');
			$token     = rand(10000000, 99999999);
			$password  = rand(10000000, 99999999);

			DB::table('vendor_tbl')->insert([
				'parentvendorid'   => $vendorid,
				'name'             => $input['name'],
				'middlename'       => $input['middlename'],
				'lastname'         => $input['lastname'],
				'mobilenumber'     => $input['mobilenumber'],
				'alternetnumber'   => $input['alternetnumber'],
				'gender'           => $input['gender'],
				'dob'              => $input['dob'],
				'stateid'          => $input['stateid'],
				'cityid'           => $input['cityid'],
				'areaids'          => $input['areaid'],
				'postalcode'       => $input['pincode'],
				'completeaddress'  => $input['address'],
				'aadhaarnumber'    => $input['aadhaarnumber'],
				'aadhaarfrontfile' => $files['aadhaarfrontfile'],
				'aadhaarbackfile'  => $files['aadhaarbackfile'],
				'pannumber'        => $input['pannumber'],
				'panfile'          => $files['panfile'],
				'drivinglicence'   => $input['drivinglicence'],
				'licencefile'      => $files['licencefile'],
				'bankname'         => $input['bankname'],
				'accountnumber'    => $input['bankaccount'],
				'ifsccode'         => $input['ifsccode'],
				'isactive'         => 1,
				'createdby'        => $vendorid,
				'usertype'         => 'VENDOR',
				'creationdate'     => $now,
				'experience'       => $input['experience'],
				'employeecatids'   => $input['categoryids'],
				'accesstoken'      => $token,
				'loginpassword'    => $password,
				'isemployee'       => 1,
				'isverified'       => 1,
				'ip_country'       => $ipdata['country'] ?? '',
				'ip_countrycode'   => $ipdata['countryCode'] ?? '',
				'ip_region'        => $ipdata['region'] ?? '',
				'ip_regionname'    => $ipdata['regionName'] ?? '',
				'ip_city'          => $ipdata['city'] ?? '',
				'ip_zip'           => $ipdata['zip'] ?? '',
				'ip_lat'           => $ipdata['lat'] ?? '',
				'ip_lon'           => $ipdata['lon'] ?? '',
				'ip_timezone'      => $ipdata['timezone'] ?? '',
				'ip_isp'           => $ipdata['isp'] ?? '',
				'profilepic'       => $files['profilepic'],
				'bankfile'         => $files['bankfile'],
				'nomineename'      => $input['nomineename'],
				'relation'         => $input['relation'],
				'nomineedob'       => $input['nomineedob'],
				'documentfile'     => $files['documentfile']
			]);

			DB::commit();
			return response()->json(['message' => __('messages.stored'), 'status' => 200], 200);
		}
		catch(QueryException $e)
		{
			DB::rollBack();

			// Delete uploaded files to avoid storage clutter
			foreach ($files as $file) {
				if ($file) {
					Storage::disk('public')->delete($file);
				}
			}
			Log::error('Get Employee Form Error: ' . $e->getMessage());
			return response()->json(['message' => 'FAILED TO REGISTER EMPLOYEE. PLEASE TRY AGAIN.', 'status' => 400], 400);
		}
    }
	public function employeeList(Request $request)
	{
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validated 	=	$request->validate($rules, $messages);
		$vendorid   =	$validated['vendorid'];
		$accesstoken=	$validated['accesstoken'];


		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if (!$vendor) {
				return response()->json([
					'message'    => 'UNAUTHORIZED ACCESS.',
					'status'     => 401,
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken
				], 401);
			}

			if ($vendor->isemployee == 1) {
				return response()->json([
					'message'    => 'UNAUTHORIZED ACCESS.',
					'status'     => 401,
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken
				], 401);
			}

			$baseUrl = Config::get('app.url') . "/storage/";

			$employees = DB::table('vendor_tbl as a')
				->select(
					'a.vendorid as employeeid', 'a.name', 'a.middlename', 'a.lastname',
					'a.mobilenumber', 'a.alternetnumber', 'a.email', 'a.gender', 'a.dob',
					'a.stateid', 'a.cityid', 'a.isactive', 'a.postalcode as pincode',
					'a.completeaddress', 'a.aadhaarnumber', 'a.aadhaarfrontfile',
					'a.aadhaarbackfile', 'a.pannumber', 'a.panfile', 'a.drivinglicence',
					'a.licencefile', 'a.bankname', 'a.accountnumber', 'a.ifsccode',
					'a.profilepic', 'a.employeecatids', 'a.experience', 'a.verificationstatus',
					'a.nomineename', 'a.relation', 'a.nomineedob', 'a.documentfile',
					DB::raw('GROUP_CONCAT(DISTINCT b.parentcategoryid ORDER BY b.parentcategoryid) as categories')
				)
				->leftJoin('category_tbl as b', DB::raw('FIND_IN_SET(b.categoryid, a.employeecatids)'), '>', DB::raw('0'))
				->where('a.parentvendorid', $vendorid)
				->groupBy('a.vendorid')
				->get();

			foreach ($employees as $emp) {
				$emp->aadhaarfrontfile = $emp->aadhaarfrontfile ? $baseUrl . $emp->aadhaarfrontfile : '';
				$emp->aadhaarbackfile  = $emp->aadhaarbackfile  ? $baseUrl . $emp->aadhaarbackfile  : '';
				$emp->panfile          = $emp->panfile          ? $baseUrl . $emp->panfile          : '';
				$emp->licencefile      = $emp->licencefile      ? $baseUrl . $emp->licencefile      : '';
				$emp->profilepic       = $emp->profilepic       ? $baseUrl . $emp->profilepic       : '';
				$emp->documentfile     = $emp->documentfile     ? $baseUrl . $emp->documentfile     : '';
			}

			return response()->json([
				'message'  => 'ACCESS GRANTED',
				'status'   => 200,
				'employees'=> $employees
			], 200);

		}
		catch (QueryException $e) 
		{
			return response()->json([
				'message'    => 'WE ENCOUNTERED AN ISSUE WHILE FETCHING EMPLOYEE DATA. PLEASE TRY AGAIN LATER.',
				'status'     => 500,
				'vendorid'   => $vendorid,
				'accesstoken'=> $accesstoken
			], 500);
		}
		catch(\Exception $e)
		{
			return response()->json([
				'message'    => 'INTERNAL SERVER ERROR. PLEASE TRY AGAIN LATER.',
				'status'     => 500,
				'vendorid'   => $vendorid,
				'accesstoken'=> $accesstoken
			], 500);
		}

	}
    public function updateEmployee(Request $request)
	{
        $rules = [
			'vendorid'		=> 'required',
			'accesstoken'	=> 'required',
			'employeeid'	=> 'required',
            'name'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'gender' 		=> 'required',
			'dob'  			=> 'required|date',
			'categoryids'	=> 'required',
			'aadhaarnumber'	=> 'required|numeric|digits:12',
			'drivinglicence'	=> 'nullable',
			'pannumber'		=> 'nullable',
			'bankname'		=> 'nullable',
			'bankaccount'	=> 'nullable|numeric',
			'ifsccode'		=> 'nullable',
			'stateid'		=> 'required',
			'cityid'		=> 'required',
			'pincode'		=> 'required|numeric',
			'alternetnumber'=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'employeeid.required' 	=> 'EMPLOYEE ID IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'INVALID NAME',
			'middlename.regex' 		=> 'INVALID NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 		=> 'INVALID NAME',
			'lastname.max' 			=> 'MAXIMUM LENGTH IS 30',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'gender.required' 		=> 'GENDER IS REQUIRED',
			'dob.required' 			=> 'DATE OF BIRTH IS REQUIRED',
			'dob.date' 				=> 'MUST BE A DATE VALUE',
			'categoryids.required'	=> 'CATEGORY NAME IS REQUIRED',
			'aadhaarnumber.required'=> 'AADHAAR NUMBER IS REQUIRED',
			'drivinglicence.required'=> 'DRIVING LICENCE IS REQUIRED',
			'aadhaarnumber.numeric'	=> 'AADHAAR SHOULD BE A NUMERIC VALUE ONLY',
			'aadhaarnumber.digits' 	=> 'INVALID AADHAAR NUMBER',
			'pannumber.required'	=> 'PAN NUMBER IS REQUIRED',
			'bankname.required'		=> 'BANK NAME IS REQUIRED',
			'bankaccount.required'	=> 'BANK ACCOUNT IS REQUIRED',
			'bankaccount.numeric'	=> 'INVALID BANK ACCOUNT',
			'ifsccode.required'		=> 'IFSC IS REQUIRED',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'alternetnumber.regex'	=> 'INVALID ALTERNATE NUMBER',
			'alternetnumber.digits'	=> 'INVALID ALTERNATE NUMBER',
        ];

        $validated = $request->validate($rules, $messages);

		$vendorid = (int) $validated['vendorid'];
		$accesstoken = $validated['accesstoken'];

		if (!$this->CheckVendor($vendorid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status'  => 401
			], 401);
		}
		$employeeid = (int) $validated['employeeid'];
		$employee = DB::table('vendor_tbl')->where('vendorid', $employeeid)->first();

		if (!$employee)
		{
			return response()->json([
				'message' => 'EMPLOYEE NOT FOUND',
				'status'  => 404
			], 404);
		}

		$updateData = array_filter([
			'name'              => $validated['name'] ?? null,
			'middlename'        => $validated['middlename'] ?? null,
			'lastname'          => $validated['lastname'] ?? null,
			'mobilenumber'      => $validated['mobilenumber'],
			'gender'            => $validated['gender'],
			'dob'               => $validated['dob'],
			'employeecatids'    => $validated['categoryids'],
			'experience'        => $request->input('experience'),
			'aadhaarnumber'     => $validated['aadhaarnumber'],
			'drivinglicence'    => $validated['drivinglicence'],
			'pannumber'         => $validated['pannumber'],
			'bankname'          => $validated['bankname'],
			'accountnumber'     => $validated['bankaccount'],
			'ifsccode'          => $validated['ifsccode'],
			'completeaddress'   => $request->input('address'),
			'postalcode'        => $validated['pincode'],
			'alternetnumber'    => $validated['alternetnumber'],
			'nomineename'       => $request->input('nomineename'),
			'relation'          => $request->input('relation'),
			'nomineedob'        => $request->input('nomineedob'),
			'stateid'           => $validated['stateid'],
			'cityid'            => $validated['cityid'],
		], fn ($val) => !is_null($val)); // Remove nulls

		DB::beginTransaction();

		try {
			// File Upload Handling
			$fileFields = [
				'profilepic'        => 'uploads/employeeprofiles',
				'aadhaarfrontfile'  => 'uploads/employeeaadharfiles',
				'aadhaarbackfile'   => 'uploads/employeeaadharbackfiles',
				'panfile'           => 'uploads/employeepanfiles',
				'bankfile'          => 'uploads/bankfiles',
				'licencefile'       => 'uploads/licencefiles',
				'documentfile'      => 'uploads/nomineefiles',
			];

			foreach ($fileFields as $field => $path)
			{
				if ($request->hasFile($field)) {
					if (!empty($employee->$field)) {
						Storage::disk('public')->delete($employee->$field);
					}
					$updateData[$field] = $request->file($field)->store($path, 'public');
				}
			}

			DB::table('vendor_tbl')->where('vendorid', $employeeid)->update($updateData);

			DB::commit();

			return response()->json(['message' => __('messages.updated'), 'status' => 200], 200);
		}
		catch (\Exception $e)
		{
			DB::rollBack();

			// Clean uploaded files if something failed after upload
			foreach ($fileFields as $field => $path) {
				if (isset($updateData[$field])) {
					Storage::disk('public')->delete($updateData[$field]);
				}
			}

			return response()->json(['message' => $e->getMessage(), 'status' => 400], 400);
		}

	}
    public function updateEmployeeProfile(Request $request)
	{
        $rules = [
			'vendorid'		=> 'required',
			'accesstoken'	=> 'required',
            'name'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  	=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'gender' 		=> 'required',
			'dob'  			=> 'nullable|date',
			'categoryids'	=> 'required',
			'aadhaarnumber'	=> 'required|numeric|digits:12',
			'pannumber'		=> 'nullable',
			'drivinglicence'=> 'nullable',
			'bankname'		=> 'nullable',
			'bankaccount'	=> 'nullable|numeric',
			'ifsccode'		=> 'nullable',
			'stateid'		=> 'required',
			'cityid'		=> 'required',
			'pincode'		=> 'required|numeric',
			'alternetnumber'=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'INVALID NAME',
			'middlename.regex' 		=> 'INVALID NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 		=> 'INVALID NAME',
			'lastname.max' 			=> 'MAXIMUM LENGTH IS 30',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'gender.required' 		=> 'GENDER IS REQUIRED',
			'dob.required' 			=> 'DATE OF BIRTH IS REQUIRED',
			'dob.date' 				=> 'MUST BE A DATE VALUE',
			'categoryids.required'	=> 'CATEGORY NAME IS REQUIRED',
			'aadhaarnumber.required'=> 'AADHAAR NUMBER IS REQUIRED',
			'aadhaarnumber.numeric'	=> 'AADHAAR SHOULD BE A NUMERIC VALUE ONLY',
			'aadhaarnumber.digits' 	=> 'INVALID AADHAAR NUMBER',
			'pannumber.required'	=> 'PAN NUMBER IS REQUIRED',
			'drivinglicence.required'=> 'DRIVING LICENCE NUMBER IS REQUIRED',
			'bankname.required'		=> 'BANK NAME IS REQUIRED',
			'bankaccount.required'	=> 'BANK ACCOUNT IS REQUIRED',
			'bankaccount.numeric'	=> 'INVALID BANK ACCOUNT',
			'ifsccode.required'		=> 'IFSC IS REQUIRED',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'alternetnumber.regex'	=> 'INVALID ALTERNATE NUMBER',
			'alternetnumber.digits'	=> 'INVALID ALTERNATE NUMBER',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$vendorid   	= 	intval($request->input('vendorid'));
		$accesstoken   	= 	(String) $request->input('accesstoken');

		
		$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$name   		= 	(String) $request->input('name');
			$middlename   	= 	(String) $request->input('middlename');
			$lastname   	= 	(String) $request->input('lastname');
			$mobilenumber   = 	(String) $request->input('mobilenumber');
			$gender   		= 	(String) $request->input('gender');
			$dob			= 	(String) $request->input('dob');
			$categoryids   	= 	(String) ($request->input('categoryids'));
			$experience		=	(String) $request->input('experience');
			$aadhaarnumber  = 	(String) $request->input('aadhaarnumber');
			$pannumber   	= 	(String) $request->input('pannumber');
			$drivinglicence   	= 	(String) $request->input('drivinglicence');
			$bankname   	= 	(String) $request->input('bankname');
			$bankaccount   	= 	(String) $request->input('bankaccount');
			$ifsccode  		= 	(String) $request->input('ifsccode');
			$stateid   		= 	intval($request->input('stateid'));
			$cityid   		= 	intval($request->input('cityid'));
			$address   		= 	(String) $request->input('address');
			$pincode   		= 	(String) $request->input('pincode');
			$alternetnumber = 	(String) $request->input('alternetnumber');

			$nomineename   		= 	(String) $request->input('nomineename');
			$relation   		= 	(String) $request->input('relation');
			$nomineedob = 	(String) $request->input('nomineedob');

			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$profilepic    	=	(String) $request->file('profilepic');
			$aadhaarfrontfile=	(String) $request->file('aadhaarfrontfile');
			$aadhaarbackfile =	(String) $request->file('aadhaarbackfile');
			$panfile    	=	(String) $request->file('panfile');
			$bankfile    	=	(String) $request->file('bankfile');
			
			$documentfile   =	(String) $request->file('documentfile');
			
			
			try
			{
				$updateData = [];
				if(!empty($name))
				{
					$updateData['name'] = $name;
				}
				if(!empty($middlename))
				{
					$updateData['middlename'] = $middlename;
				}
				if(!empty($lastname))
				{
					$updateData['lastname'] = $lastname;
				}
				if(!empty($mobilenumber))
				{
					$updateData['mobilenumber'] = $mobilenumber;
				}
				if(!empty($gender))
				{
					$updateData['gender'] = $gender;
				}
				if(!empty($dob))
				{
					$updateData['dob'] = $dob;
				}
				if(!empty($categoryids))
				{
					$updateData['employeecatids'] = $categoryids;
				}
				if(!empty($experience))
				{
					$updateData['experience'] = $experience;
				}
				if(!empty($aadhaarnumber))
				{
					$updateData['aadhaarnumber'] = $aadhaarnumber;
				}
				if(!empty($drivinglicence))
				{
					$updateData['drivinglicence'] = $drivinglicence;
				}
				if(!empty($pannumber))
				{
					$updateData['pannumber'] = $pannumber;
				}
				if(!empty($bankname))
				{
					$updateData['bankname'] = $bankname;
				}
				if(!empty($bankaccount))
				{
					$updateData['accountnumber'] = $bankaccount;
				}
				if(!empty($ifsccode))
				{
					$updateData['ifsccode'] = $ifsccode;
				}
				if(!empty($address))
				{
					$updateData['completeaddress'] = $address;
				}
				if(!empty($pincode))
				{
					$updateData['postalcode'] = $pincode;
				}
				if(!empty($alternetnumber))
				{
					$updateData['alternetnumber'] = $alternetnumber;
				}
				if(!empty($nomineename))
				{
					$updateData['nomineename'] = $nomineename;
				}
				if(!empty($relation))
				{
					$updateData['relation'] = $relation;
				}
				if(!empty($nomineedob))
				{
					$updateData['nomineedob'] = $nomineedob;
				}
				$updateData['stateid'] 	= $stateid;
				$updateData['cityid'] 	= $cityid;
				
				$employee 	= 	DB::table('vendor_tbl')->where('vendorid','=',$vendorid)->first();
				
				if($profilepic!='')
				{
					Storage::disk('public')->delete($employee->profilepic);
					$profilepic					= $request->file('profilepic')->store('uploads/employeeprofiles', 'public');
					$updateData['profilepic'] 	= $profilepic;
				}
				if($aadhaarfrontfile!='')
				{
					Storage::disk('public')->delete($employee->aadhaarfrontfile);
					$aadhaarfrontfile					= $request->file('aadhaarfrontfile')->store('uploads/employeeaadharfiles', 'public');
					$updateData['aadhaarfrontfile'] 	= $aadhaarfrontfile;
				}
				if($aadhaarbackfile!='')
				{
					Storage::disk('public')->delete($employee->aadhaarbackfile);
					$aadhaarbackfile					= $request->file('aadhaarbackfile')->store('uploads/employeeaadharbackfiles', 'public');
					$updateData['aadhaarbackfile'] 	= $aadhaarbackfile;
				}
				if($panfile!='')
				{
					Storage::disk('public')->delete($employee->panfile);
					$panfile				= $request->file('panfile')->store('uploads/employeepanfiles', 'public');
					$updateData['panfile'] 	= $panfile;
				}
				if($bankfile!='')
				{
					Storage::disk('public')->delete($employee->bankfile);
					$bankfile				= $request->file('bankfile')->store('uploads/bankfiles', 'public');
					$updateData['bankfile'] = $bankfile;
				}
				if($licencefile!='')
				{
					Storage::disk('public')->delete($employee->licencefile);
					$licencefile				= $request->file('licencefile')->store('uploads/licencefiles', 'public');
					$updateData['licencefile'] = $licencefile;
				}
				if($documentfile!='')
				{
					Storage::disk('public')->delete($employee->documentfile);
					$documentfile				= $request->file('documentfile')->store('uploads/nomineefiles', 'public');
					$updateData['documentfile'] = $documentfile;
				}
				DB::table('vendor_tbl')->where('vendorid',$vendorid)->where('isemployee','=',1)->update($updateData);
				
				return response()->json(['message' =>__('messages.updated'),'status'=>200], 200);
			}
			catch(QueryException $e)
			{
				if($profilepic!='')
				Storage::disk('public')->delete($profilepic);
			
				if($aadhaarfrontfile!='')
				Storage::disk('public')->delete($aadhaarfrontfile);

				if($aadhaarbackfile!='')
				Storage::disk('public')->delete($aadhaarbackfile);
			
				if($panfile!='')
				Storage::disk('public')->delete($panfile);

				if($bankfile!='')
				Storage::disk('public')->delete($bankfile);

				if($licencefile!='')
				Storage::disk('public')->delete($licencefile);

				if($documentfile!='')
				Storage::disk('public')->delete($documentfile);
				
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}			
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }

	public function getVendorProfile(Request $request)
	{
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$vendorid   	= 	$request->input('vendorid');
		$accesstoken   	= 	$request->input('accesstoken');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
			
            if($isexist)
            {
				$vendor	=	DB::table('vendor_tbl as a')
								->select('a.vendorid','a.name','a.middlename','a.lastname','a.mobilenumber','a.alternetnumber','a.email','a.gender','a.dob','a.stateid','a.cityid','a.areaids','a.postalcode as pincode','a.completeaddress as address','a.aadhaarnumber','a.aadhaarfrontfile','a.aadhaarbackfile','a.pannumber','a.panfile','a.firmname','a.registrationnumber','a.firmcontact','a.firmemail','a.firmstateid','c.statename as firmstate','a.firmcityid','d.cityname as firmcity','a.firmpostalcode','a.firmaddress','a.firmpannumber','a.firmpanfile','a.gstnumber','a.gstfile','a.bankname','a.accountnumber as bankaccount','a.bankfile','a.ifsccode','a.bankbranch','a.profilepic','a.categoryids','a.verificationstatus','a.isselfemployeed','a.isprofilecompleted','a.drivinglicence','a.licencefile','a.latitude','a.longitude','a.nomineename','a.relation','a.nomineedob','a.documentfile',DB::raw('GROUP_CONCAT(DISTINCT b.parentcategoryid ORDER BY b.parentcategoryid) as categories'))
								->leftJoin('category_tbl as b', DB::raw('FIND_IN_SET(b.categoryid, a.categoryids)'), '>', DB::raw('0'))
								->leftJoin('state_tbl as c','c.stateid','=','a.firmstateid')
								->leftJoin('city_tbl as d','d.cityid','=','a.firmcityid')
								->where('a.vendorid','=',$vendorid)
								->where('a.accesstoken','=',$accesstoken)
								->groupBy('a.vendorid')
								->first();
				if($vendor->categories!='')
				{
					$categoryIds = explode(',', $vendor->categories);
					$maincategories = DB::table('category_tbl')
									->select(DB::raw('GROUP_CONCAT(category SEPARATOR ",") AS categories'))
									->whereIn('categoryid',$categoryIds)
									->pluck('categories')
									->first();
					$vendor->maincategory	=	$maincategories;
				}
				if($vendor->categoryids!='')
				{
					$categoryIds = explode(',', $vendor->categoryids);
					$subcategories = DB::table('category_tbl')
									->select(DB::raw('GROUP_CONCAT(category SEPARATOR ",") AS categories'))
									->whereIn('categoryid',$categoryIds)
									->pluck('categories')
									->first();
					$vendor->subcategories	=	$subcategories;
				}
				if($vendor->areaids!='')
				{
					$areaIds = explode(',', $vendor->areaids);
					$areanames = DB::table('area_tbl')
									->select(DB::raw('GROUP_CONCAT(areaname SEPARATOR ",") AS areas'))
									->whereIn('areaid',$areaIds)
									->pluck('areas')
									->first();
					$vendor->areanames	=	$areanames;
				}
				$state = DB::table('state_tbl')->where('stateid', $vendor->stateid)->first();
				$vendor->statename	=	$state->statename;
				$city = DB::table('city_tbl')->where('cityid', $vendor->cityid)->first();
				$vendor->cityname	=	$city->cityname;
				
				if($isexist->isemployee==0)
				{
					if($vendor->profilepic!='')
					{
						$vendor->profilepic	=	Config::get('app.url')."/storage/".$vendor->profilepic;
					}
					if($vendor->aadhaarfrontfile!='')
					{
						$vendor->aadhaarfrontfile	=	Config::get('app.url')."/storage/".$vendor->aadhaarfrontfile;
					}
					if($vendor->aadhaarbackfile!='')
					{
						$vendor->aadhaarbackfile	=	Config::get('app.url')."/storage/".$vendor->aadhaarbackfile;
					}
					if($vendor->panfile!='')
					{
						$vendor->panfile	=	Config::get('app.url')."/storage/".$vendor->panfile;
					}
					if($vendor->bankfile!='')
					{
						$vendor->bankfile	=	Config::get('app.url')."/storage/".$vendor->bankfile;
					}
					if($vendor->firmpanfile!='')
					{
						$vendor->firmpanfile	=	Config::get('app.url')."/storage/".$vendor->firmpanfile;
					}
					if($vendor->gstfile!='')
					{
						$vendor->gstfile	=	Config::get('app.url')."/storage/".$vendor->gstfile;
					}
					if($vendor->licencefile!='')
					{
						$vendor->licencefile	=	Config::get('app.url')."/storage/".$vendor->licencefile;
					}
					if($vendor->documentfile!='')
					{
						$vendor->documentfile	=	Config::get('app.url')."/storage/".$vendor->documentfile;
					}
					return response()->json(['message' => 'VENDOR DETAIL','status'=>200,'employeedetail'=>$vendor,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 200);
				}
				else
				{
					return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
				}
            }
            else
            {
				return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message'=>'DUPLICATE DATA FOUND','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }		
	}

	public function getEmployeeProfile(Request $request)
	{
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$vendorid   	= 	$request->input('vendorid');
		$accesstoken   	= 	$request->input('accesstoken');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
			
            if($isexist)
            {
				$vendor	=	DB::table('vendor_tbl')
								->select('vendorid','name','middlename','lastname','mobilenumber','alternetnumber','email','gender','dob','stateid','cityid','postalcode','completeaddress as address','aadhaarnumber','aadhaarfrontfile','aadhaarbackfile','pannumber','panfile','bankname','accountnumber','bankfile','ifsccode','bankbranch','profilepic','categoryids','verificationstatus','drivinglicence','licencefile','nomineename','relation','nomineedob','documentfile')
								->where('vendorid','=',$vendorid)
								->where('accesstoken','=',$accesstoken)
								->first();
				
				if($isexist->isemployee==1)
				{
					if($vendor->profilepic!='')
					{
						$vendor->profilepic	=	Config::get('app.url')."/storage/".$vendor->profilepic;
					}
					if($vendor->aadhaarfrontfile!='')
					{
						$vendor->aadhaarfrontfile	=	Config::get('app.url')."/storage/".$vendor->aadhaarfrontfile;
					}
					if($vendor->aadhaarbackfile!='')
					{
						$vendor->aadhaarbackfile	=	Config::get('app.url')."/storage/".$vendor->aadhaarbackfile;
					}
					if($vendor->panfile!='')
					{
						$vendor->panfile	=	Config::get('app.url')."/storage/".$vendor->panfile;
					}
					if($vendor->bankfile!='')
					{
						$vendor->bankfile	=	Config::get('app.url')."/storage/".$vendor->bankfile;
					}
					if($vendor->licencefile!='')
					{
						$vendor->licencefile	=	Config::get('app.url')."/storage/".$vendor->licencefile;
					}
					if($vendor->documentfile!='')
					{
						$vendor->documentfile	=	Config::get('app.url')."/storage/".$vendor->documentfile;
					}
					return response()->json(['message' => 'EMPLOYEE DETAIL','status'=>200,'employeedetail'=>$vendor,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 200);
				}
				else
				{
					return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 201);
				}
            }
            else
            {
				return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 201);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }		
	}

    public function updateVendorProfile(Request $request)
	{
        $rules = [
			'profilepic'		=> 'required|max:10240',
			'vendorid'			=> 'required',
			'accesstoken'		=> 'required',
            'name'  			=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  		=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  		=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email'  			=> 'nullable|email',
			'gender' 			=> 'required',
			'stateid'			=> 'required',
			'cityid'			=> 'required',
			'pincode'			=> 'required|numeric',
			'address'			=> 'required',
			'aadhaarnumber'		=> 'required|numeric|digits:12',
			'drivinglicence'	=> 'required',
			'aadhaarfrontfile'	=> 'nullable|max:10240',
			'aadhaarbackfile'	=> 'nullable|max:10240',
			'licencefile'		=> 'nullable|max:10240',
			'pannumber'			=> 'required',
			'panfile'			=> 'nullable|max:10240',
			'bankaccount'		=> 'nullable|max:10240',
			'bankfile'			=> 'nullable|max:10240',
			'categoryids'		=> 'required',
			'isselfemployeed'	=> 'required',
			'alternetnumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
			'firmcontact'  		=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'profilepic.required' 		=> 'PROFILE PICTURE IS REQUIRED',
			'profilepic.max' 			=> 'ONLY 512 KB SIZE IS ALLOWED',
			'vendorid.required' 		=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQUIRED',
			'name.required' 			=> 'NAME IS REQUIRED',
			'name.regex' 				=> 'INVALID NAME',
			'name.max' 					=> 'MAXIMUM LENGTH IS 50',
			'middlename.regex' 			=> 'INVALID NAME',
			'middlename.max' 			=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 			=> 'INVALID NAME',
			'lastname.max' 				=> 'MAXIMUM LENGTH IS 30',
			'mobilenumber.required' 	=> 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 		=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 		=> 'INVALID MOBILE NUMBER',
			'email.email' 				=> 'INVALID EMAIL ADDRESS',
			'gender.required' 			=> 'GENDER IS REQUIRED',
			'stateid.required'			=> 'STATE NAME IS REQUIRED',
			'cityid.required'			=> 'CITY NAME IS REQUIRED',
			'pincode.required'			=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'			=> 'INVALID PIN NUMBER',
			'address.required'			=> 'ADDRESS IS REQUIRED',
			'aadhaarnumber.required'	=> 'AADHAAR NUMBER IS REQUIRED',
			'drivinglicence.required'	=> 'DRIVING LICENCE NUMBER IS REQUIRED',
			'aadhaarnumber.numeric' 	=> 'INVALID AADHAAR NUMBER',
			'aadhaarnumber.digits' 		=> 'INVALID AADHAAR NUMBER',
			'pannumber.required'		=> 'PAN NUMBER IS REQUIRED',
			'aadhaarfrontfile.max'		=> 'AADHAAR FILE MAXIMUM SIZE IS 512 KB',
			'aadhaarbackfile.max'		=> 'AADHAAR FILE MAXIMUM SIZE IS 512 KB',
			'licencefile.max'			=> 'AADHAAR FILE MAXIMUM SIZE IS 512 KB',
			'panfile.max'				=> '512 KB IS ALLOWED',
			'bankfile.max'				=> 'BANK FILE MAXIMUM SIZE IS 512 KB',
			'categoryids.required'		=> 'CATEGORY NAME IS REQUIRED',
			'isselfemployeed.required'	=> 'IS SELF EMPLOYEED IS REQUIRED',
			'alternetnumber.regex'		=> 'INVALID ALTERNATE NUMBER',
			'alternetnumber.digits'		=> 'INVALID ALTERNATE NUMBER',
			'firmcontact.regex'			=> 'INVALID FIRM CONTACT',
			'firmcontact.digits'		=> 'INVALID FIRM CONTACT',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$vendorid   	= 	intval($request->input('vendorid'));
		$accesstoken   	= 	(String) $request->input('accesstoken');

		
		$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			if($isexist->isemployee==0)
			{
				$name   		= 	(String) $request->input('name');
				$middlename   	= 	(String) $request->input('middlename');
				$lastname   	= 	(String) $request->input('lastname');
				$mobilenumber   = 	(String) $request->input('mobilenumber');
				$alternetnumber = 	(String) $request->input('alternetnumber');
				$email 			= 	(String) $request->input('email');
				$gender   		= 	(String) $request->input('gender');
				$dob			= 	(String) $request->input('dob');
				$stateid   		= 	intval($request->input('stateid'));
				$cityid   		= 	intval($request->input('cityid'));
				$areaids   		= 	(String) $request->input('areaids');
				$pincode   		= 	(String) $request->input('pincode');
				$address   		= 	(String) $request->input('address');
				$aadhaarnumber  = 	(String) $request->input('aadhaarnumber');
				$drivinglicence  = 	(String) $request->input('drivinglicence');
				$pannumber   	= 	(String) $request->input('pannumber');
				$firmname   	= 	(String) $request->input('firmname');
				$registrationnumber   	= 	(String) $request->input('registrationnumber');
				$firmcontact   	= 	(String) $request->input('firmcontact');
				$firmemail   	= 	(String) $request->input('firmemail');
				$firmstateid   	= 	(String) $request->input('firmstateid');
				$firmcityid   	= 	(String) $request->input('firmcityid');
				$firmpostalcode = 	(String) $request->input('firmpostalcode');
				$firmaddress   	= 	(String) $request->input('firmaddress');
				$firmpannumber  = 	(String) $request->input('firmpannumber');
				$gstnumber   	= 	(String) $request->input('gstnumber');
				$bankname   	= 	(String) $request->input('bankname');
				$bankaccount   	= 	(String) $request->input('bankaccount');
				$ifsccode   	= 	(String) $request->input('ifsccode');
				$bankbranch   	= 	(String) $request->input('bankbranch');
				$categoryids   	= 	(String) $request->input('categoryids');
				$isselfemployeed= 	(String) $request->input('isselfemployeed');
				$latitude		= 	doubleval($request->input('latitude'));
				$longitude		= 	doubleval($request->input('longitude'));
				
				$nomineename	= 	(String) $request->input('nomineename');
				$relation		= 	(String) $request->input('relation');
				$nomineedob		= 	(String) $request->input('nomineedob');

				$currentDateTime= 	now();
				$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

				$profilepic    	= (String) $request->file('profilepic');
				$aadhaarfrontfile    = (String) $request->file('aadhaarfrontfile');
				$aadhaarbackfile    = (String) $request->file('aadhaarbackfile');
				$panfile    	= (String) $request->file('panfile');
				$bankfile    	= (String) $request->file('bankfile');
				$firmpanfile    = (String) $request->file('firmpanfile');
				$gstfile    	= (String) $request->file('gstfile');
				$licencefile    	= (String) $request->file('licencefile');
				
				$documentfile    	= (String) $request->file('documentfile');
				
				
				try
				{
					$updateData = [];
					if(!empty($name))
					{
						$updateData['name'] = $name;
					}
					if(!empty($middlename))
					{
						$updateData['middlename'] = $middlename;
					}
					if(!empty($lastname))
					{
						$updateData['lastname'] = $lastname;
					}
					if(!empty($mobilenumber))
					{
						$updateData['mobilenumber'] = $mobilenumber;
					}
					if(!empty($alternetnumber))
					{
						$updateData['alternetnumber'] = $alternetnumber;
					}
					if(!empty($email))
					{
						$updateData['email'] = $email;
					}
					if(!empty($gender))
					{
						$updateData['gender'] = $gender;
					}
					if(!empty($dob))
					{
						$updateData['dob'] = $dob;
					}
					if(!empty($pincode))
					{
						$updateData['postalcode'] = $pincode;
					}
					if(!empty($address))
					{
						$updateData['completeaddress'] = $address;
					}
					if(!empty($aadhaarnumber))
					{
						$updateData['aadhaarnumber'] = $aadhaarnumber;
					}
					if(!empty($drivinglicence))
					{
						$updateData['drivinglicence'] = $drivinglicence;
					}
					if(!empty($pannumber))
					{
						$updateData['pannumber'] = $pannumber;
					}
					if(!empty($firmname))
					{
						$updateData['firmname'] = $firmname;
					}
					if(!empty($registrationnumber))
					{
						$updateData['registrationnumber'] = $registrationnumber;
					}
					if(!empty($firmcontact))
					{
						$updateData['firmcontact'] = $firmcontact;
					}
					if(!empty($firmemail))
					{
						$updateData['firmemail'] = $firmemail;
					}
					if(!empty($firmemail))
					{
						$updateData['firmemail'] = $firmemail;
					}
					if(!empty($firmpostalcode))
					{
						$updateData['firmpostalcode'] = $firmpostalcode;
					}
					if(!empty($firmaddress))
					{
						$updateData['firmaddress'] = $firmaddress;
					}
					if(!empty($firmpannumber))
					{
						$updateData['firmpannumber'] = $firmpannumber;
					}
					if(!empty($gstnumber))
					{
						$updateData['gstnumber'] = $gstnumber;
					}
					if(!empty($bankname))
					{
						$updateData['bankname'] = $bankname;
					}
					if(!empty($bankaccount))
					{
						$updateData['accountnumber'] = $bankaccount;
					}
					if(!empty($ifsccode))
					{
						$updateData['ifsccode'] = $ifsccode;
					}
					if(!empty($bankbranch))
					{
						$updateData['bankbranch'] = $bankbranch;
					}
					if(!empty($categoryids))
					{
						$updateData['categoryids'] = $categoryids;
					}
					if(!empty($nomineename))
					{
						$updateData['nomineename'] = $nomineename;
					}
					if(!empty($relation))
					{
						$updateData['relation'] = $relation;
					}
					if(!empty($nomineedob))
					{
						$updateData['nomineedob'] = $nomineedob;
					}
					if(!empty($areaids))
					{
						$updateData['areaids'] = $areaids;
					}
					if(!empty($latitude))
					{
						$updateData['latitude'] = $latitude;
					}
					if(!empty($longitude))
					{
						$updateData['longitude'] = $longitude;
					}

					$updateData['stateid'] 			= $stateid;
					$updateData['cityid'] 			= $cityid;
					$updateData['firmstateid'] 		= $firmstateid;
					$updateData['firmcityid'] 		= $firmcityid;
					$updateData['isselfemployeed'] 	= $isselfemployeed;
					
					$vendor 	= 	DB::table('vendor_tbl')->where('vendorid','=',$vendorid)->first();
					
					if($profilepic!='')
					{
						Storage::disk('public')->delete($vendor->profilepic);
						$profilepic					= $request->file('profilepic')->store('uploads/vendorprofiles', 'public');
						$updateData['profilepic'] 	= $profilepic;
					}
					if($aadhaarfrontfile!='')
					{
						Storage::disk('public')->delete($vendor->aadhaarfrontfile);
						$aadhaarfrontfile					= $request->file('aadhaarfrontfile')->store('uploads/vendoraadharfiles', 'public');
						$updateData['aadhaarfrontfile'] 	= $aadhaarfrontfile;
					}
					if($aadhaarbackfile!='')
					{
						Storage::disk('public')->delete($vendor->aadhaarbackfile);
						$aadhaarbackfile					= $request->file('aadhaarbackfile')->store('uploads/vendoraadharbackfiles', 'public');
						$updateData['aadhaarbackfile'] 	= $aadhaarbackfile;
					}
					if($panfile!='')
					{
						Storage::disk('public')->delete($vendor->panfile);
						$panfile				= $request->file('panfile')->store('uploads/vendorpanfiles', 'public');
						$updateData['panfile'] 	= $panfile;
					}
					if($bankfile!='')
					{
						Storage::disk('public')->delete($vendor->bankfile);
						$bankfile				= $request->file('bankfile')->store('uploads/bankfiles', 'public');
						$updateData['bankfile'] 	= $bankfile;
					}
					if($firmpanfile!='')
					{
						Storage::disk('public')->delete($vendor->firmpanfile);
						$firmpanfile				= $request->file('firmpanfile')->store('uploads/firmpanfiles', 'public');
						$updateData['firmpanfile'] 	= $firmpanfile;
					}
					if($gstfile!='')
					{
						Storage::disk('public')->delete($vendor->gstfile);
						$gstfile				= $request->file('gstfile')->store('uploads/firmgstfiles', 'public');
						$updateData['gstfile'] 	= $gstfile;
					}
					if($licencefile!='')
					{
						Storage::disk('public')->delete($vendor->licencefile);
						$licencefile				= $request->file('licencefile')->store('uploads/licencefiles', 'public');
						$updateData['licencefile'] 	= $licencefile;
					}
					if($documentfile!='')
					{
						Storage::disk('public')->delete($vendor->documentfile);
						$documentfile				= $request->file('documentfile')->store('uploads/nomineefiles', 'public');
						$updateData['documentfile'] 	= $documentfile;
					}
					DB::table('vendor_tbl')->where('vendorid',$vendorid)->where('isemployee','=',0)->update($updateData);
					
					return response()->json(['message' =>__('messages.updated'),'status'=>200], 200);
				}
				catch(QueryException $e)
				{
					if($profilepic!='')
					Storage::disk('public')->delete($profilepic);
				
					if($aadhaarfrontfile!='')
					Storage::disk('public')->delete($aadhaarfrontfile);

					if($aadhaarbackfile!='')
					Storage::disk('public')->delete($aadhaarbackfile);
				
					if($panfile!='')
					Storage::disk('public')->delete($panfile);

					if($bankfile!='')
					Storage::disk('public')->delete($bankfile);

					if($firmpanfile!='')
					Storage::disk('public')->delete($firmpanfile);

					if($gstfile!='')
					Storage::disk('public')->delete($gstfile);

					if($licencefile!='')
					Storage::disk('public')->delete($licencefile);

					if($documentfile!='')
					Storage::disk('public')->delete($documentfile);
					
					return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
				}			
			}
			else
			{
				return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 201);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 201);
		}
    }



	public function setEmployeeStatus(Request $request)
	{
        $rules = [
			'vendorid'  	=> 'required',
			'employeeid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'employeeid.required' 	=> 'PLEASE PROVIDE EMPLOYEE DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$vendorid   	= 	$request->input('vendorid');
		$employeeid   	= 	$request->input('employeeid');
		$accesstoken   	= 	$request->input('accesstoken');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
			
            if($isexist)
            {
				$employee	=	DB::table('vendor_tbl')->where('vendorid','=',$employeeid)->first();
				if($employee->isemployee==1)
				{
					if($employee->lastupdated!='')
					{
						$now = Carbon::now();
						$lastUpdated = Carbon::parse($employee->lastupdated);					
						if($lastUpdated->diffInSeconds($now)>=15)
						{
							if($employee->isactive==1)
							DB::update('update vendor_tbl set isactive=?,lastupdated=? where vendorid=?',[0,$creationdate,$employeeid]);
							else
							DB::update('update vendor_tbl set isactive=?,lastupdated=? where vendorid=?',[1,$creationdate,$employeeid]);
							
							return response()->json(['message' =>__('messages.updated'),'status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'employeeid'=>$employeeid], 200);
						}
						else
						{
							return response()->json(['message' =>__('messages.updated'),'status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'employeeid'=>$employeeid], 200);
						}
					}
					else
					{
							if($employee->isactive==1)
							DB::update('update vendor_tbl set isactive=?,lastupdated=? where vendorid=?',[0,$creationdate,$employeeid]);
							else
							DB::update('update vendor_tbl set isactive=?,lastupdated=? where vendorid=?',[1,$creationdate,$employeeid]);
							
							return response()->json(['message' =>__('messages.updated'),'status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'employeeid'=>$employeeid], 200);
						
					}
				}
				else
				{
					return response()->json(['message' => 'INVALID EMPLOYEE DETAIL','status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'employeeid'=>$employeeid], 401);
				}
            }
            else
            {
				return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'employeeid'=>$employeeid], 201);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }		
	}

	/* VENDOR SIGN IN COMPLETED */
	/*
    public function getSlotTime(Request $request)
	{
		
        $rules = [
			'todaysdate'	=>	'required|date',
        ];

        $messages = [
			'todaysdate.required' 	=> 'DATE IS REQUIRED',
			'todaysdate.date' 		=> 'INVALID DATE VALUE PROVIDED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$customerid   	=	intval($request->input('customerid'));
		$accesstoken   	=	(String) $request->input('accesstoken');
		$todaysdate   	=	$request->input('todaysdate');
		
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		try
		{
			$times = [];

			$requestedDate 	= Carbon::parse($todaysdate);
			$currentDate 	= $currentDateTime->format('Y-m-d');
			
			$requestedDateStartOfDay = $requestedDate->startOfDay();
			
			if(!$requestedDateStartOfDay->isBefore(Carbon::today()))
			{
				if($currentDate==$todaysdate)
				{
					$now 	= Carbon::now()->addHours(2)->format('H:i:s');
					$slots	=	DB::table('slot_tbl')
									->select('slot')
									->selectRaw("IF(slot > ?, 1, 0) as isactive", [$now])
									->where('isactive','=',1)
									->orderby('slot')
									->get();
				}
				else
				{
					$slots	=	DB::table('slot_tbl')
									->select('slot')
									->selectRaw("1 as isactive")
									->where('isactive','=',1)
									->orderby('slot')
									->get();
				}
				foreach ($slots as $slot) {
					$slot->slot = Carbon::createFromFormat('H:i:s', $slot->slot)->format('h:i A');
				}
				
				return response()->json(['message' =>'SLOT TIME','status'=>200,'slots'=>$slots], 200);
			}
			else
			{
				return response()->json(['message' =>'INVALID DATE PROVIDED','status'=>201,'slots'=>""], 201);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }
	*/
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
					//$now 	= Carbon::now()->addHours(2)->format('H:i:s');
					$now = Carbon::now()->addHours(2);
					$slots	=	DB::table('slot_tbl')
									->select('slot','percentage')
									->selectRaw("IF(TIMESTAMP(?, slot) > ?, 1, 0) as isactive", [
										$requestdate, // e.g., '2025-06-18'
										$now->format('Y-m-d H:i:s')
									])
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
						$vendors 	=	DB::table('vendors_subcategory')->where('subcategoryid',$subcategoryid)->value('vendors');
						$percentage	=	($vendors*$slot->percentage)/100;
						
						$percentage =	($percentage - floor($percentage)) < 0.5 ? floor($percentage) : ceil($percentage);
						
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

				return response()->json(['message'=>'SLOT TIME','status'=>200,'slots'=>$filteredSlots], 200);
				
			}
			else
			{
				return response()->json(['message' =>'INVALID DATE PROVIDED','status'=>201,'slots'=>""], 201);
			}
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
			'accesstoken'	=>	'required',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
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
				$addresslist	=	DB::table('customer_address')
										->select('addressid','stateid','cityid','postalcode','address','latitude','longitude')
										->where('customerid','=',$customerid)
										->where('ismaster','=',0)
										->get();
				
				return response()->json(['message' =>'ADDRESS LIST','status'=>200,'addresslist'=>$addresslist], 200);
			}
			else
			{
				
			}
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
			'accesstoken'	=>	'required',
			'address'		=>	'required',
			'latitude'		=>	'required|numeric',
			'longitude'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'address.required' 		=> 'ADDRESS IS REQUIRED',
			'latitude.required' 	=> 'LATITUDE IS REQUIRED',
			'latitude.numeric' 		=> 'INVALID LATITUDE VALUE',
			'longitude.required' 	=> 'LONGITUDE IS REQUIRED',
			'longitude.numeric' 	=> 'LONGITUDE LATITUDE VALUE',
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
				$areaid   	=	intval($request->input('areaid'));
				$address   	=	(String) $request->input('address');
				$latitude   =	doubleval($request->input('latitude'));
				$longitude  =	doubleval($request->input('longitude'));
				
				if($latitude==0 || $longitude==0)
				{
					return response()->json(['message' =>'PLEASE PROVIDE LATIRUDE AND LONGITUDE VALUE','status'=>400], 400);
				}

				DB::insert('insert into customer_address(stateid,cityid,areaid,address,postalcode,latitude,longitude,customerid) values(?,?,?,?,?,?,?,?)',[$isexist->stateid,$isexist->cityid,$areaid,$address,$isexist->postalcode,$latitude,$longitude,$customerid]);

				$lastid = DB::getPdo()->lastInsertId();

				$addresslist	=	DB::table('customer_address')
										->select('addressid','stateid','cityid','areaid','address','postalcode','latitude','longitude')
										->where('customerid','=',$customerid)
										->get();
				
				return response()->json(['message' =>'ADDRESS STORED SUCCESSFULLY','status'=>200,'addresslist'=>$addresslist,'lastid'=>$lastid], 200);
			}
			else
			{
				return response()->json(['message' =>'CUSTOMER DOES NOT EXIST. PLEASE CHECK AND TRY AGAIN!','status'=>401], 401);
			}
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


	public function getCustomerProfile(Request $request)
	{
        $rules = [
			'customerid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$customerid   	= 	$request->input('customerid');
		$accesstoken   	= 	$request->input('accesstoken');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			$ipAddress 	=	request()->ip();
			$ipdata		=	$this->getIpDetail($ipAddress);

			$isexist	=	$this->CheckCustomer($customerid,$accesstoken);
			
            if($isexist)
            {
				$customer	=	DB::table('customer_tbl as a')
								->select('a.customerid','a.accesstoken','a.name','a.middlename','a.lastname','a.mobilenumber','a.stateid','b.statename','a.cityid','c.cityname','a.areaid','a.postalcode as pincode','a.completeaddress as address','isprofilecompleted','a.profilepic','a.latitude','a.longitude','a.email')
								->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
								->leftjoin('city_tbl as c','c.cityid','=','a.cityid')
								->where('a.customerid','=',$customerid)
								->where('a.accesstoken','=',$accesstoken)
								->groupBy('a.customerid')
								->first();
				
				if($customer->profilepic!='')
				{
					$customer->profilepic	=	$appUrl = Config::get('app.url')."/storage/".$customer->profilepic;
				}
				return response()->json(['message' => 'CUSTOMER PROFILE DETAIL','status'=>200,'customerprofile'=>$customer,'customer'=>$customerid,'accesstoken'=>$accesstoken], 200);
            }
            else
            {
				return response()->json(['message' =>'UNAUTHORIZED ACCESS.','status'=>401,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 401);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'status'=>400,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 400);
        }		
	}

    public function updateCustomerProfile(Request $request)
	{
        $rules = [
			'profilepic'		=> 'nullable|max:512',
			'customerid'		=> 'required',
			'accesstoken'		=> 'required',
            'name'  			=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  		=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'stateid'			=> 'required',
			'cityid'			=> 'required',
			'pincode'			=> 'required|numeric',
			'address'			=> 'required',
        ];

        $messages = [
			'profilepic.max' 		=> 'ONLY 512 KB SIZE IS ALLOWED',
			'customerid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'MAXIMUM LENGTH IS 50 CHARACTERS ONLY',
			'middlename.regex' 		=> 'INVALID NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30 CHARACTERS ONLY',
			'lastname.required' 	=> 'NAME IS REQUIRED',
			'lastname.regex' 		=> 'INVALID NAME',
			'lastname.max' 			=> 'MAXIMUM LENGTH IS 30 CHARACTERS ONLY',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'address.required'		=> 'ADDRESS IS REQUIRED',
        ];

        $validatedData 		=	$request->validate($rules, $messages);
		
		$customerid   		= 	intval($request->input('customerid'));
		$accesstoken   		= 	(String) $request->input('accesstoken');
		
		$isexist			=	$this->CheckCustomer($customerid,$accesstoken);
		if($isexist)
		{
			$name   		=	(String) $request->input('name');
			$middlename   	=	(String) $request->input('middlename');
			$lastname   	=	(String) $request->input('lastname');
			$mobilenumber   =	(String) $request->input('mobilenumber');
			$email   		=	(String) $request->input('email');
			$stateid   		=	intval ($request->input('stateid'));
			$cityid   		=	intval ($request->input('cityid'));
			$areaid   		=	intval ($request->input('areaid'));
			$pincode   		=	(String) $request->input('pincode');
			$address   		=	(String) $request->input('address');
			$latitude   	=	(String) $request->input('latitude');
			$longitude   	=	(String) $request->input('longitude');
			

			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$profilepic    	= (String) $request->file('profilepic');
		
			try
			{
				$updateData = [];
				if(!empty($name))
				{
					$updateData['name'] =	$name;
				}
				if(!empty($middlename))
				{
					$updateData['middlename'] =	$middlename;
				}
				if(!empty($lastname))
				{
					$updateData['lastname'] =	$lastname;
				}
				if(!empty($email))
				{
					$updateData['email'] =	$email;
				}
				if(!empty($mobilenumber))
				{
					$updateData['mobilenumber']	=	$mobilenumber;
				}
				if(!empty($pincode))
				{
					$updateData['postalcode']	=	$pincode;
				}
				if(!empty($address))
				{
					$updateData['completeaddress']	=	$address;
				}
				if(!empty($latitude))
				{
					$updateData['latitude']	=	$latitude;
				}
				if(!empty($longitude))
				{
					$updateData['longitude']	=	$longitude;
				}
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
				DB::table('customer_tbl')->where('customerid',$customerid)->update($updateData);
				
				DB::update('update customer_address set stateid=?,cityid=?,areaid=?,postalcode=?,address=?,latitude=?,longitude=? where customerid=? and ismaster=?',[$stateid,$cityid,$areaid,$pincode,$address,doubleval($latitude),doubleval($longitude),$customerid,1]);
				
				return response()->json(['message' =>'CUSTOMER DATA UPDATED','status'=>200], 200);
			}
			catch(QueryException $e)
			{
				if($profilepic!='')
				Storage::disk('public')->delete($profilepic);
							
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}			
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 401);
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
			
			return response()->json(['message' =>'SERVICE LIST','status'=>200,'servicelist'=>$services], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function addToCart(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'accesstoken'	=>	'required|numeric',
			'categoryid'	=>	'required|numeric',
			'serviceid'		=>	'required|numeric',
			'quantity'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'customerid.numeric' 	=> 'INVALID CUSTOMER ID',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'categoryid.required' 	=> 'PLEASE PROVIDE CATEGORY DETAIL',
			'categoryid.numeric' 	=> 'INVALID CATEGORY ID',
			'serviceid.required' 	=> 'PLEASE PROVIDE SERVICE DETAIL',
			'serviceid.numeric' 	=> 'INVALID SERVICE ID',
			'quantity.required' 	=> 'QUANTITY IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		
		$customerid   = (int) $request->input('customerid');
		$accesstoken  = (string) $request->input('accesstoken');
		$categoryid   = (int) $request->input('categoryid');
		$serviceid    = (int) $request->input('serviceid');
		$optionid     = (int) $request->input('optionid');
		$quantity     = (int) $request->input('quantity');


		if(!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		if($categoryid==0 || $serviceid==0 || $quantity==0)
		{
			return response()->json(['message' => 'INVALID DETAILS', 'status' => 400], 400);
		}

		$serviceCacheKey = "service_{$categoryid}_{$serviceid}_{$optionid}";
		$service = Cache::remember($serviceCacheKey, 10, function () use ($categoryid, $serviceid, $optionid) {
			return DB::table('services as a')
				->select('a.categoryid', 'a.visitingcharge', 'a.serviceid', 'a.optionid', 'a.servicetitle',
						 'a.mrp as servicecharge', 'a.servicepic', 'a.likes', 'a.ratings', 'a.reviews',
						 'a.requiredtime', 'a.description', 'b.taxrate')
				->leftJoin('tax_tbl as b', 'b.taxid', '=', 'a.taxid')
				->where([
					['a.categoryid', '=', $categoryid],
					['a.serviceid', '=', $serviceid],
					['a.optionid', '=', $optionid]
				])
				->first();
		});
		if(!$service)
		{
			return response()->json(['message' => 'INVALID SERVICE DETAILS', 'status' => 400], 400);
		}

		$isLocked = Cache::remember("customer_cart_locked_{$customerid}",5, function () use ($customerid) {
			return DB::table('customer_cart')->where('customerid', $customerid)->where('islocked', 1)->exists();
		});	

		if ($isLocked) {
			return response()->json([
				'message' => 'PAYMENT IS UNDER PROCESS. PLEASE WAIT FOR 5 MINUTES',
				'status' => 423
			], 423);
		}
		$existingCategory = DB::table('customer_cart')
			->where('customerid', $customerid)
			->value('categoryid');

		if (!is_null($existingCategory) && $existingCategory != $categoryid) {
			return response()->json([
				'message' => 'ONLY ONE CATEGORY OF SERVICE CAN BE BOOKED AT A TIME. PLEASE COMPLETE OR REMOVE THE CURRENT SELECTION BEFORE ADDING SERVICES FROM A DIFFERENT CATEGORY. THIS ENSURES WE ASSIGN THE RIGHT SPECIALIST FOR YOUR NEEDS.',
				'status' => 200
			], 200);
		}
		$existingItem = DB::table('customer_cart')
			->where([
				['customerid', '=', $customerid],
				['categoryid', '=', $categoryid],
				['serviceid', '=', $serviceid],
				['optionid', '=', $optionid]
			])
			->first();

		$discount = $this->getDiscount();
		

		$baseCharge = $service->servicecharge;
		$taxable = round(($baseCharge - ($baseCharge * $discount / 100)) * $quantity, 2);
		$taxValue = round(($taxable * $service->taxrate) / 100, 2);
		$netPayable = round($taxable + $taxValue, 2);
		$totalServiceCharge = $baseCharge * $quantity;

		DB::beginTransaction();

		try
		{
			if($existingItem)
			{
				DB::table('customer_cart')
					->where('customerid', $customerid)
					->where('serviceid', $serviceid)
					->where('optionid', $optionid)
					->update([
						'quantity' => $quantity,
						'servicecharge' => $totalServiceCharge,
						'discount' => $discount,
						'taxable' => $taxable,
						'taxvalue' => $taxValue,
						'payable' => $netPayable,
						'visitingcharge' => $service->visitingcharge
					]);
			}
			else
			{
				DB::table('customer_cart')->insert([
					'customerid' => $customerid,
					'categoryid' => $categoryid,
					'serviceid' => $serviceid,
					'optionid' => $optionid,
					'quantity' => $quantity,
					'servicecharge' => $totalServiceCharge,
					'discount' => $discount,
					'visitingcharge' => $service->visitingcharge,
					'taxable' => $taxable,
					'taxvalue' => $taxValue,
					'payable' => $netPayable
				]);
			}

			DB::commit();

			$cart = $this->getCartList($customerid);

			return response()->json([
				'message' => 'ITEM ADDED TO YOUR CART LIST',
				'status' => 200,
				'cart' => $cart
			], 200);

		}
		catch(QueryException $e)
		{
			DB::rollBack();
			Log::error('Add to Cart Error: ' . $e->getMessage());
			return response()->json([
				'message' => $e->getMessage(),
				'status' => 400
			], 400);
		}
    }

    public function getCart(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'accesstoken'	=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);

		$customerid  = (int) $request->input('customerid');
		$accesstoken = (string) $request->input('accesstoken');		
		if (!$this->CheckCustomer($customerid, $accesstoken)) {
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}
		try
		{

			$cart = $this->getCartList($customerid);
			

			return response()->json([
				'message' => 'CART LIST',
				'status' => 200,
				'cart' => $cart
			], 200);

		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'status' => 400
			], 400);
		}

		
    }


    public function removeFromCart(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'accesstoken'	=>	'required|numeric',
			'categoryid'	=>	'required|numeric',
			'serviceid'		=>	'required|numeric',
			'optionid'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'categoryid.required' 	=> 'CATEGORY DETAIL IS REQUIRED',
			'serviceid.required' 	=> 'SERVICE DETAIL IS REQUIRED',
			'optionid.required' 	=> 'OPTION ID IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$customerid   = (int) $request->input('customerid');
		$accesstoken  = (string) $request->input('accesstoken');

		if (!$this->CheckCustomer($customerid, $accesstoken)) {
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		$categoryid = (int) $request->input('categoryid');
		$serviceid  = (int) $request->input('serviceid');
		$optionid   = (int) $request->input('optionid');

		try
		{
			DB::beginTransaction();

			DB::delete('delete from customer_cart where customerid=? and categoryid=? and serviceid=? and optionid=? and islocked=?',
				[$customerid, $categoryid, $serviceid, $optionid,0]);

			$cart = $this->getCartList($customerid);

			DB::commit();

			return response()->json([
				'message' => 'SERVICE REMOVED SUCCESSFULLY',
				'status' => 200,
				'cart' => $cart
			], 200);

		}
		catch (QueryException $e)
		{
			DB::rollBack();

			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function applyCoupon(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'accesstoken'	=>	'required|numeric',
			'couponcode'	=>	'required',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'couponcode.required' 	=> 'COUPON CODE IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);

		$customerid   = (int) $request->input('customerid');
		$accesstoken  = (string) $request->input('accesstoken');
		$couponcode   = (string) $request->input('couponcode');

		if(!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		try
		{
			$order = DB::table('customer_cart_order')->where('customerid', $customerid)->first();

			if(!$order)
			{
				return response()->json([
					'message' => 'ORDER DOES NOT EXIST',
					'status' => 404
				], 404);
			}

			$categoryid = DB::table('customer_cart')
							->where('customerid', $customerid)
							->orderBy('categoryid', 'asc')
							->value('categoryid');

			$coupon	=	DB::table('coupon_tbl')
							->where('couponcode', $couponcode)
							->whereRaw("FIND_IN_SET(?, applicablesubcategory)", [$categoryid])
							->whereRaw("STR_TO_DATE(expirydate,'%Y-%m-%d') > NOW()")
							->first();

			if(is_null($coupon))
			{
				return response()->json([
					'message' => 'INVALID COUPON CODE. THIS COUPON CODE MAY HAVE EXPIRED OR MAY NOT BE APPLICABLE FOR THE SELECTED SERVICE CATEGORY.',
					'status' => 422
				], 422);
			}

			if ($coupon->ordereligibility === 'FIRST')
			{
				$usedBefore	=	DB::table('customer_order')
									->where('couponcode', $couponcode)
									->where('customerid', $customerid)
									->exists();

				if($usedBefore)
				{
					return response()->json([
						'message' => 'THIS COUPON HAS ALREADY BEEN USED BY THE USER',
						'status' => 409
					], 409);
				}
			}

			if($coupon->totalusagelimit!=0)
			{
				$globalusage	=	DB::table('customer_order')
										->where('couponcode', $couponcode)
										->count();

				if($globalusage>=$coupon->totalusagelimit)
				{
					return response()->json([
						'message' => 'MAXIMUM NUMBER OF USAGE FOR THIS COUPON IS REACHED',
						'status' => 409
					], 409);
				}
			}

			if ($order->totaltaxable < $coupon->minordervalue) {
				return response()->json([
					'message' => 'MINIMUM ORDER VALUE FOR THIS COUPON IS ' . $coupon->minordervalue . ' INR',
					'status' => 400
				], 400);
			}

			DB::beginTransaction();

			DB::update(
				'UPDATE customer_cart_order SET couponcode = ? WHERE customerid = ?',
				[$couponcode, $customerid]
			);

			$cart = $this->getCartList($customerid);

			DB::commit();

			return response()->json(['message'=>'COUPON APPLIED SUCCESSFULLY','status'=>200,'cart'=>$cart], 200);

		}
		catch(QueryException $e)
		{
			DB::rollBack();
			return response()->json(['message'=>$e->getMessage(),'status'=>400], 400);
		}
    }

    public function removeCoupon(Request $request)
	{
        $rules = [
			'customerid'	=>	'required|numeric',
			'accesstoken'	=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$customerid   = (int) $request->input('customerid');
		$accesstoken  = (string) $request->input('accesstoken');

		if (!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		try
		{
			$order = DB::table('customer_cart_order')->where('customerid', $customerid)->first();

			if(!$order)
			{
				return response()->json([
					'message' => 'YOUR SHOPPING CART IS CURRENTLY EMPTY.',
					'status' => 404
				], 404);
			}

			DB::beginTransaction();

			DB::update('UPDATE customer_cart_order SET couponcode = ?, coupondiscount = ?, couponpercent = ?, coupdisc = ? WHERE customerid = ?',['', 0, 0, 0, $customerid]);

			$cart = $this->getCartList($customerid);

			DB::commit();

			return response()->json([
				'message' => 'COUPON CODE REMOVED SUCCESSFULLY',
				'status' => 200,
				'cart' => $cart
			], 200);

		}
		catch(QueryException $e)
		{
			DB::rollBack();

			return response()->json([
				'message' => $e->getMessage(),
				'status' => 400
			], 400);
		}
    }


    public function bookService(Request $request)
	{
        $rules = [
			'customerid'			=>	'required',
			'accesstoken'			=>	'required',
			'servicedate'			=>	'required|date|after_or_equal:today',
			'slottime'				=>	'required|date_format:H:i:s',
			'addressid'				=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'razorpay_signature'	=>	'required',
			'netamount'				=>	'nullable|numeric',
        ];

        $messages = [
			'customerid.required' 		=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQUIRED',
			'servicedate.required' 		=> 'SERVICE DATE IS REQUIRED',
			'servicedate.after_or_equal'=> 'INVALID SERVICE DATE',
			'slottime.required' 		=> 'SERVICE TIME IS REQUIRED',
			'slottime.time' 			=> 'INVALID TIME PROVIDED',
			'addressid.required' 		=> 'ADDRESS DETAIL IS REQUIRED',
			'razorpay_order_id.required'=> 'RAZORPAY ORDER ID IS REQUIRED',
			'razorpay_payment_id.required'=> 'RAZORPAY PAYMENT ID IS REQUIRED',
			'razorpay_signature.required'=> 'RAZORPAY SIGNATURE IS REQUIRED',
			'netamount.numeric' 			=> 'AMOUNT MUST BE A NUMERIC VALUE',
        ];
		
        $validated  	=	$request->validate($rules,$messages);
		$customerid 	= 	(int)$validated['customerid'];
		$accesstoken 	= 	$validated['accesstoken'];

		if(!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		$order	=	DB::table('customer_cart_order')
						->where('razorpay_order_id', $validated['razorpay_order_id'])
						->where('paymentstatus', 'pending')
						->where('customerid', $customerid)
						->first();
						
		if(!$order)
		{
			return response()->json([
				'message' => 'TRANSACTION DETAIL NOT FOUND.',
				'status' => 404,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 404);
		}
      
		$expectedSignature = hash_hmac(
			'sha256',
			$validated['razorpay_order_id'] . "|" . $validated['razorpay_payment_id'],
			env('RAZORPAY_SECRET')
		);
		
		
		if($expectedSignature !== $validated['razorpay_signature'])
		{
			return response()->json([
				'message' => 'INVALID PAYMENT SIGNATURE!',
				'status' => 400,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 400);
		}
		try
		{
			DB::transaction(function () use ($customerid, $validated, $order)
			{
				$amount = (float)($validated['netamount'] ?? 0);
				$creationDate = now()->format('Y-m-d H:i:s');

				$receiptid = DB::table('receipt_tbl')->insertGetId([
					'customerid'           => $customerid,
					'financialyear'        => $order->financialyear,
					'receiptnumber'        => $order->receiptnumber,
					'razorpay_order_id'    => $order->razorpay_order_id,
					'razorpay_payment_id'  => $validated['razorpay_payment_id'],
					'razorpay_signature'   => $validated['razorpay_signature'],
					'netamount'            => $amount,
					'paymentstatus'        => 'paid',
					'paymentdatetime'      => $order->paymentdatetime,
					'generateddate'        => $order->paymentdatetime,
					'completeddate'        => $creationDate,
				]);

				$ordCart = DB::table('customer_cart_order')
					->select('visitingcharge', 'visitingtax', 'discount')
					->where('customerid', $customerid)
					->where('razorpay_order_id', $validated['razorpay_order_id'])
					->first();

				$cartList = $this->getCartList($customerid);
				
				$orderid = DB::table('customer_order')->insertGetId([
					'customerid'     => $customerid,
					'servicedate'    => $validated['servicedate'],
					'slottime'       => $validated['slottime'],
					'addressid'      => $validated['addressid'],
					'servicecharge'  => (float)$cartList->servicecharge,
					'discount'       => $ordCart->discount,
					'visitingcharge' => $cartList->visitingcharge,
					'visitingtax'    => $ordCart->visitingtax,
					'totaltaxable'   => $cartList->totaltaxable,
					'totaltaxvalue'  => $cartList->totaltaxvalue,
					'netpayable'     => $cartList->netpayable,
					'paid'           => $cartList->netpayable,
					'couponcode'     => $cartList->couponcode
				]);

				foreach ($cartList->cartlist as $item) {
					DB::table('customer_order_detail')->insert([
						'orderid'         => $orderid,
						'customerid'      => $item->customerid,
						'categoryid'      => $item->categoryid,
						'serviceid'       => $item->serviceid,
						'optionid'        => $item->optionid,
						'servicecharge'   => $item->servicecharge,
						'discount'        => $ordCart->discount,
						'visitingcharge'  => $ordCart->visitingcharge,
						'taxable'         => $item->taxable,
						'taxvalue'        => $item->taxvalue,
						'payable'         => $item->payable,
						'quantity'        => $item->quantity,
						'addressid'       => $validated['addressid'],
						'slottime'        => $validated['slottime'],
						'paid'            => $item->payable,
						'servicedate'     => $validated['servicedate'],
						'paymentstatus'   => 1,
						'creationdate'    => $creationDate,
					]);
				}

				DB::table('receipt_tbl')
					->where('receiptid', $receiptid)
					->update(['orderid' => $orderid]);

				// Generate PDF
				$customer = DB::table('customer_tbl')->where('customerid', $customerid)->first();
				
				$services = DB::table('customer_order_detail as a')
					->select('a.quantity', 'a.payable', 'b.servicetitle','a.taxable','a.taxvalue')
					->leftJoin('services as b', function ($join) {
						$join->on('b.serviceid', '=', 'a.serviceid')
							 ->on('b.optionid', '=', 'a.optionid');
					})
					->where('orderid', $orderid)
					->get();

				$receipt=	DB::table('receipt_tbl')->where('receiptid', $receiptid)->first();
				$pdf 	=	PDF::loadView('pdf.customerreceipt',compact('customer', 'receipt', 'services','order'))->setPaper('A4', 'portrait');

				$filename = $order->receiptnumber . '.pdf';
				$pdf->save(storage_path('app/public/receipts/'.$filename));

				DB::table('receipt_tbl')->where('receiptid', $receiptid)->update([
					'receiptfile' => $filename
				]);

				DB::table('customer_cart')->where('customerid', $customerid)->delete();
				DB::table('customer_cart_order')->where('customerid', $customerid)->delete();

				$this->smsService->pushMessage($customer->mobilenumber, 0, 'BOOKING', 0, '');
			});

			return response()->json([
				'message' => 'PAYMENT VERIFIED SUCCESSFULLY!',
				'status' => 200,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 200);

		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'status' => 400,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 400);
		}

    }

    public function generateOrder(Request $request)
	{
        $rules = [
			'customerid'	=>	'required',
			'accesstoken'	=>	'required',
			'netamount'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'netamount.required' 	=> 'AMOUNT IS REQUIRED',
			'netamount.numeric' 	=> 'AMOUNT MUST BE NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$customerid = (int) $request->input('customerid');
		$accesstoken= $request->input('accesstoken');
		$amount 	= round($request->input('netamount'),2);
		
		$cart 		= DB::table('customer_cart_order')->where('customerid', $customerid)->first();
		if(!$cart)
		{
			return response()->json([
				'message' => 'YOUR CART IS EMPTY',
				'status' => 400,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 400);
		}
		$expectedAmount = $cart->visitingcharge + $cart->visitingtax + $cart->netpayable;
		if(!empty($cart->couponcode))
		{
			$discount		=	round($cart->netpayable * $cart->couponpercent / 100, 2);
			$expectedAmount	= 	$cart->visitingcharge + $cart->visitingtax + ($cart->netpayable - $discount);
		}
		$expectedAmount = round($expectedAmount, 2);
		
		if($amount!==$expectedAmount)
		{
			return response()->json([
				'message' => 'INVALID REQUEST ATTEMPTED',
				'status' => 400,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken,
				'expected' => $expectedAmount
			], 400);
		}

		if (!$this->CheckCustomer($customerid, $accesstoken)) {
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}

		$now 			=	now();
		$paymentdate 	=	$now->format('Y-m-d');
		$financialyear 	= 	$this->resourceController->GetFinancialYear($paymentdate);
		$receipt 		= 	'RCPT' . rand(100, 999) . strtotime($now);
		$amount 		= (int) round($amount * 100);
		try {
			$order = $this->razorpay->order->create([
				'amount' => $amount, // convert to paisa
				'currency' => 'INR',
				'receipt' => $receipt,
				'payment_capture' => 1
			]);

			$recent = DB::table('customer_cart_order')
				->where('customerid', $customerid)
				->where('paymentdatetime', '>=', Carbon::now()->subMinutes(3))
				->where('paymentstatus', 'pending')
				->first();

			if ($recent) {
				// Update only if there's a pending payment
				DB::table('customer_cart_order')
					->where('customerid', $customerid)
					->where('razorpay_order_id', $recent->razorpay_order_id)
					->update([
						'razorpay_order_id' => $order->id,
						'receiptnumber' => $receipt,
						'netpayable' => $cart->netpayable,
						'paymentstatus' => 'pending',
						'paymentdatetime' => $now,
					]);

				return response()->json([
					'message' => 'ORDER GENERATED SUCCESSFULLY',
					'status' => 200,
					'orderid' => $order->id,
					'netamount' => $amount,
					'customerid' => $customerid,
					'accesstoken' => $accesstoken
				], 200);
			}

			// Create new order
			DB::table('customer_cart_order')
				->where('customerid', $customerid)
				->update([
					'financialyear' => $financialyear,
					'razorpay_order_id' => $order->id,
					'receiptnumber' => $receipt,
					'netpayable' => $cart->netpayable,
					'paymentstatus' => 'pending',
					'paymentdatetime' => $now,
					'islocked' => 1,
				]);

			DB::table('customer_cart')->where('customerid', $customerid)->update(['islocked' => 1,]);

			return response()->json([
				'message' => 'ORDER GENERATED SUCCESSFULLY',
				'status' => 200,
				'orderid' => $order->id,
				'netamount' => $amount,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 200);
		}
		catch(QueryException $e) 
		{
			return response()->json([
				'message' => 'ORDER GENERATION FAILED',
				'error' => $e->getMessage(),
				'status' => 400
			], 400);
		}
    }

    public function bookingFailed(Request $request)
	{
        $rules = [
			'customerid'			=>	'required',
			'accesstoken'			=>	'required',
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
			'accesstoken.required' 			=> 'ACCESS TOKEN IS REQUIRED',
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
		
        $validated 	=	$request->validate($rules, $messages);
		$customerid = (int) $validated['customerid'];
		$accesstoken = $validated['accesstoken'];

		if(!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}
		
		$razorpay_order_id 	=	$validated['razorpay_order_id'];
		$cartOrder 			=	DB::table('customer_cart_order')
								->where('razorpay_order_id', $razorpay_order_id)
								->where('paymentstatus', 'pending')
								->where('customerid', $customerid)
								->first();

		if(!$cartOrder)
		{
			return response()->json([
				'message' => 'TRANSACTION DETAIL NOT FOUND.',
				'status' => 404,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 404);
		}

		try
		{
			DB::transaction(function () use ($validated, $customerid, $cartOrder)
			{
				$receiptid = DB::table('receipt_tbl')->insertGetId([
					'customerid'          => $customerid,
					'financialyear'       => $cartOrder->financialyear,
					'receiptnumber'       => $cartOrder->receiptnumber,
					'razorpay_order_id'   => $cartOrder->razorpay_order_id,
					'razorpay_payment_id' => $validated['razorpay_payment_id'],
					'netamount'           => (float) $validated['netamount'],
					'paymentstatus'       => 'failed',
					'paymentdatetime'     => $cartOrder->paymentdatetime,
					'errorcode'           => $validated['errorcode'],
					'errordescription'    => $validated['errordescription'],
				]);

				$ordCart = DB::table('customer_cart_order')
					->select('visitingcharge', 'visitingtax', 'discount')
					->where('customerid', $customerid)
					->where('razorpay_order_id', $validated['razorpay_order_id'])
					->first();

				$cartList = $this->getCartList($customerid);

				$orderid = DB::table('customer_order')->insertGetId([
					'customerid'      => $customerid,
					'servicedate'     => $validated['servicedate'],
					'slottime'        => $validated['slottime'],
					'addressid'       => $validated['addressid'],
					'servicecharge'   => (float) $cartList->servicecharge,
					'discount'        => $ordCart->discount,
					'visitingcharge'  => $cartList->visitingcharge,
					'visitingtax'     => $ordCart->visitingtax,
					'totaltaxable'    => $cartList->totaltaxable,
					'totaltaxvalue'   => $cartList->totaltaxvalue,
					'netpayable'      => $cartList->netpayable,
					'couponcode'      => $cartList->couponcode,
					'isfailed'        => 1,
				]);

				DB::table('receipt_tbl')
					->where('receiptid', $receiptid)
					->update(['orderid' => $orderid]);

				DB::table('customer_cart')->where('customerid', $customerid)->delete();
				DB::table('customer_cart_order')->where('customerid', $customerid)->delete();
			});

			return response()->json([
				'message' => 'PAYMENT FAILURE STORED SUCCESSFULLY!',
				'status' => 200,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 200);

		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => 'DUPLICATE TRANSACTION FOUND',
				'status' => 400,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 400);
		}
		catch(Exception $e)
		{
			return response()->json([
				'message' => 'INTERNAL SERVER ERROR',
				'status' => 500,
				'error' => $e->getMessage(),
				'customerid' => $customerid
			], 500);
		}

    }

    public function rescheduleOrder(Request $request)
	{
		$today 			= Carbon::today();

        $rules = [
            'customerid'  		=> 'required|numeric',
			'accesstoken'		=>	'required',
			'detailid'  		=> 'required|numeric',
			'scheduledate' 		=> 'required|date',
			'scheduledate' 		=> 'required|date|after_or_equal:'.$today->toDateString(),
			'slottime' 			=> 'required',
			'categoryid' 		=> 'required|numeric',
			'reschedulereason' 	=> 'required|max:500',
        ];

        $messages = [
            'customerid.required' 		=> 'CUSTOMER ID IS REQUIRED',
			'customerid.numeric' 		=> 'INVALID CUSTOMER ID',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQUIRED',
            'detailid.required' 		=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 			=> 'INVALID DETAIL ID',
            'scheduledate.required' 	=> 'SCHEDULE DATE',
			'scheduledate.date' 		=> 'INVALID SCHEDULE DATE',
			'scheduledate.after_or_equal' => 'INVALID SCHEDULE DATE',
			'slottime.required' 		=> 'SLOT TIME IS REQUIRED',
			'categoryid.numeric' 		=> 'CATEGORY ID IS REQUIRED',
			'reschedulereason.required' => 'REASON IS REQUIRED',
			'reschedulereason.max' 		=> 'MAX 500 CHARACTERS ALLOWED',
        ];

        $validated 		= $request->validate($rules, $messages);

		$customerid        = (int) $validated['customerid'];
		$accesstoken       = $validated['accesstoken'];
		$detailid          = (int) $validated['detailid'];
		$scheduledate      = $validated['scheduledate'];
		$slottime          = $validated['slottime'];
		$categoryid        = (int) $validated['categoryid'];
		$reschedulereason  = $validated['reschedulereason'];
		if(!$this->CheckCustomer($customerid, $accesstoken))
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'customerid' => $customerid,
				'accesstoken' => $accesstoken
			], 401);
		}
		$detail = DB::table('customer_order_detail')
					->where('detailid', $detailid)
					->where('categoryid', $categoryid)
					->first();

		if(!$detail)
		{
			return response()->json(['message' => 'INVALID DETAIL PROVIDED', 'status' => 400], 400);
		}

		if($detail->vendorid != 0)
		{
			return response()->json([
				'message' => 'THIS TASK CANNOT BE RESCHEDULED BECAUSE IT HAS ALREADY BEEN ASSIGNED TO A VENDOR.',
				'status' => 400
			], 400);
		}					
		if ($detail->servicedate === $scheduledate && $detail->slottime === $slottime) {
			return response()->json([
				'message' => 'YOUR SERVICE HAS BEEN SUCCESSFULLY RESCHEDULED. THE DATE AND TIME REMAIN UNCHANGED.',
				'status' => 200
			], 200);
		}

		try
		{
			DB::transaction(function () use ($detail, $scheduledate, $slottime, $reschedulereason)
			{
				DB::table('reschedule_tbl')->insert([
					'detailid'         => $detail->detailid,
					'reschedulereason' => $reschedulereason,
					'fromdate'         => $detail->servicedate,
					'fromtime'         => $detail->slottime,
					'todate'           => $scheduledate,
					'totime'           => $slottime,
					'requestdatetime'  => now()
				]);

				DB::table('customer_order_detail')
					->where('detailid', $detail->detailid)
					->update([
						'servicedate'    => $scheduledate,
						'slottime'       => $slottime,
						'isrescheduled'  => 1
					]);
			});

			return response()->json([
				'message' => 'THE SERVICE REQUEST HAS BEEN SUCCESSFULLY RESCHEDULED TO ' .
							 Carbon::parse($scheduledate)->format('d-m-Y') . ' ' .
							 Carbon::parse($slottime)->format('h:i A'),
				'status' => 200
			], 200);

		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => 'WE ENCOUNTERED AN ISSUE WHILE PROCESSING YOUR REQUEST. PLEASE TRY AGAIN LATER OR CONTACT SUPPORT.',
				'status' => 400
			], 400);
		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'SOMETHING WENT WRONG ON OUR END. PLEASE TRY AGAIN IN A MOMENT OR CONTACT SUPPORT.',
				'error' => $e->getMessage(),
				'status' => 500
			], 500);
		}
    }



	public function myInvoices(Request $request)
	{
        $rules = [
			'customerid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
			'page'			=>	'nullable|numeric',
			'pagelimit'		=>	'nullable|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
			'pagelimit.numeric' 	=> 'PAGE LIMIT SHOULD BE NUMERIC',			
        ];
		
        $validated 	= $request->validate($rules, $messages);
		
		$customerid   = $validated['customerid'];
		$accesstoken  = $validated['accesstoken'];
		$page         = (int) ($validated['page'] ?? 1);
		$pagelimit    = (int) ($validated['pagelimit'] ?? 10);		


		try
		{
			$isValid = $this->CheckCustomer($customerid, $accesstoken);

			if (!$isValid)
			{
				return response()->json([
					'message'     => 'UNAUTHORIZED ACCESS.',
					'status'      => 401,
					'customerid'  => $customerid,
					'accesstoken' => $accesstoken
				], 401);
			}

			$paginator = DB::table('receipt_tbl')
							->select('customerid', 'financialyear', 'netamount', 'invoicenumber', 'invoicedate', 'invoicefile', 'recordtype', 'receiptfile', 'receiptnumber', 'completeddate')
							->where('customerid', $customerid)
							->orderByDesc('receiptid')
							->paginate($pagelimit, ['*'], 'page', $page);

			$invoices = $paginator->getCollection()->transform(function ($item)
			{
				$item->recordlabel = $item->recordtype === 'CUSTOMER_INVOICE' ? 'INVOICE' : 'RECEIPT';

				// Set file paths
				if ($item->invoicefile) {
					$item->invoicefile = $this->appUrl . "/storage/invoices/" . $item->invoicefile;
				} elseif ($item->receiptfile) {
					$item->invoicefile = $this->appUrl . "/storage/receipts/" . $item->receiptfile;
				}

				// Fallback values
				$item->invoicenumber = $item->invoicenumber ?: $item->receiptnumber;
				$item->invoicedate   = $item->invoicedate ?: $item->completeddate;

				// Only return relevant fields
				return [
					'customerid'    => $item->customerid,
					'financialyear' => $item->financialyear,
					'netamount'     => $item->netamount,
					'invoicenumber' => $item->invoicenumber,
					'invoicedate'   => $item->invoicedate,
					'invoicefile'   => $item->invoicefile,
					'type'          => $item->recordlabel,
				];
			});

			return response()->json([
				'message'     => 'INVOICE LIST',
				'status'      => 200,
				'customerid'  => $customerid,
				'accesstoken' => $accesstoken,
				'data'        => $invoices,
				'totalpages'  => $paginator->lastPage(),
			], 200);

		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => 'WE COULDN’T COMPLETE YOUR REQUEST RIGHT NOW. PLEASE TRY AGAIN SHORTLY.',
				'status'  => 500
			], 500);
		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'UNABLE TO PROCESS YOUR REQUEST RIGHT NOW. PLEASE TRY AGAIN LATER OR CONTACT SUPPORT.',
				'status'  => 500
			], 500);
		}
	}


	public function getReview(Request $request)
	{
        $rules = [
			'customerid'			=>	'required',
			'accesstoken'			=>	'required',
			'detailid'				=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$customerid  = 	intval($request->input('customerid'));
		$accesstoken= 	(String) $request->input('accesstoken');
		$detailid  	= 	intval($request->input('detailid'));

		$isexist	=	$this->CheckCustomer($customerid,$accesstoken);
		
		if($isexist)
		{
			
			
			try
			{
				/*
				$detail	=	DB::table('customer_order_detail as a')
								->select('a.detailid','a.serviceid','b.vendorid as vendorid','b.name as vendorname','b.middlename as vendormiddlename','b.lastname as vendorlastname','b.ratings','b.reviews','b.mobilenumber','b.email','b.completeaddress as vendoraddress','b.profilepic as vendorprofilepic')
								->leftjoin('vendor_tbl as b','b.vendorid','=','a.vendorid')
								->where('a.detailid','=',$detailid)
								->where('a.customerid','=',$customerid)
								->where('a.orderstatus','=',3)
								->first();
				*/
				$detail	=	DB::table('customer_order_detail as a')
								->select('a.detailid','a.serviceid')
								->where('a.detailid','=',$detailid)
								->where('a.customerid','=',$customerid)
								->where('a.orderstatus','=',3)
								->first();
				if($detail)
				{
					$detail->reviewquestions	=	DB::table('service_question as a')
														->select('a.questionid','a.question',DB::raw("IFNULL(b.answer, '') as answer"))
														->leftJoin('vendor_review_question as b', function($join) use ($detail) {
															$join->on('b.questionid', '=', 'a.questionid')
																 ->where('b.detailid', '=', $detail->detailid);
														})
														->where('a.serviceid','=',$detail->serviceid)
														->get();
					$ratings		=	0;
					$customerreview	=	"";
					$review	=	DB::table('vendor_review')->where('detailid','=',$detailid)->first();
					if(!is_null($review))
					{
						$ratings		=	$review->ratings;
						$customerreview	=	$review->reviewdetail;
					}
					unset($detail->detailid);
					if($ratings!=0)
					{
						$detail->reviewquestions->transform(function ($item)
						{
							$item->answer = (int) $item->answer;
							return $item;
						});
					}
					return response()->json(array_merge(['message' =>'SERVICE DETAIL AND REVIEW','status' => 200,'ratings'=>$ratings,'customerreview'=>$customerreview,'data'=>$detail]), 200);
				}
				else
				{
					return response()->json(['message' => 'INVALID DATA','status'=>401,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 401);
				}
			}
			catch(QueryException $e) 
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>500,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 500);
			}
			
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'customerid'=>$customerid,'accesstoken'=>$accesstoken], 401);
		}
	}

	public function storeReview(Request $request)
	{
		
		$rules = [
			'customerid'    => 'required',
			'accesstoken'   => 'required',
			'detailid'      => 'required|numeric',
			'ratings'       => 'required|numeric|min:1|max:5',
			'customerreview'=> 'nullable|string|max:1000',
		];

		$messages = [
			'customerid.required'    => 'CUSTOMER ID IS REQUIRED',
			'accesstoken.required'   => 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'      => 'DETAIL ID IS REQUIRED',
			'detailid.numeric'       => 'DETAIL ID SHOULD BE NUMERIC',
			'ratings.required'       => 'RATING IS REQUIRED',
			'ratings.numeric'        => 'RATING SHOULD BE A NUMBER',
			'ratings.min'            => 'RATING MUST BE AT LEAST 1',
			'ratings.max'            => 'RATING MAY NOT BE GREATER THAN 5',
		];		
		
		$detailid		=	intval($request->input('detailid'));
		
		$detail			=	DB::table('customer_order_detail')->where('detailid','=',$detailid)->first();
		
		$serviceid		=	$detail->serviceid;
		$vendorid		=	$detail->vendorid;
		
		$havequestion	=	DB::table('service_question')->where('serviceid','=',$serviceid)->first();
		if(!empty($havequestion))
		{

			$questionids	=	DB::table('service_question')
									->where('serviceid',$serviceid)
									->pluck('questionid');

			foreach($questionids as $qid)
			{
				$key 			= "question_$qid";
				$rules[$key] 	= 'required|in:0,1';
				$messages["$key.required"] 	= "ANSWER OF EACH QUESTION IS REQUIRED.";
				$messages["$key.in"] 		= "ANSWER SHOULD BE 0 AND 1 ONLY.";
			}
		}

        $validatedData = $request->validate($rules, $messages);
		
		$customerid  	= 	$request->input('customerid');
		$accesstoken	= 	$request->input('accesstoken') ?? '';
		$ratings 		= 	$request->input('ratings');
		$customerreview = 	$request->input('customerreview') ?? '';
		
		
		
		$isexist	=	$this->CheckCustomer($customerid,$accesstoken);
		
		if($isexist)
		{
			try
			{
				DB::insert('insert into vendor_review(customerid,vendorid,reviewdetail,reviewdate,ratings,serviceid,detailid) values(?,?,?,?,?,?,?)',[$customerid,$vendorid,$customerreview,date('Y\-m\-d H:i:s'),$ratings,$serviceid,$detailid]);
				
				if(!empty($havequestion))
				{
					$inserts = [];

					foreach($questionids as $qid)
					{
						$key	=	"question_$qid";
						if(isset($validatedData[$key]))
						{
							$inserts[] = [
								'customerid' => $customerid,
								'detailid'   => $detailid,
								'questionid' => $qid,
								'vendorid'   => $vendorid,
								'answer'     => $validatedData[$key],
								'reviewdate' => now(),
							];
						}
					}
					if(!empty($inserts))
					{
						DB::table('vendor_review_question')->insert($inserts);
					}
				}
				$review	=	DB::table('vendor_review')
								->selectRaw('COUNT(*) as review_count, AVG(ratings) as average_rating')
								->where('vendorid', $vendorid)
								->first();
				
				//DB::update('update vendor_tbl set reviews=?,ratings=? where vendorid=?',[$review->review_count,$review->average_rating,$vendorid]);
				
				return response()->json(array_merge(['message' =>'REVIEW STORED SUCCESSFULL','status' => 200]), 200);
			}
			catch (QueryException $e) 
			{
				return response()->json(['message' =>'A REVIEW HAS ALREADY BEEN SUBMITTED FOR THIS SERVICE.','status'=>400],400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401], 401);
		}
	}


	public function servicePhotos(Request $request)
	{
        $rules = [
			'customerid'	=>	'required',
			'accesstoken'	=>	'required',
			'detailid'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
        ];
		
        $validatedData	=	$request->validate($rules, $messages);
		
		$isexist		=	$this->CheckCustomer($validatedData['customerid'],$validatedData['accesstoken']);
		
		if($isexist)
		{
			try
			{
				$detail	=	DB::table('customer_order_detail as a')
								->select('a.detailid')
								->where('a.detailid','=',$validatedData['detailid'])
								->where('a.customerid','=',$validatedData['customerid'])
								->where('a.orderstatus','=',3)
								->first();
				if($detail)
				{
					$startphotos	=	DB::table('service_photos as a')
											->select(
												'a.pictureurl',
												'a.detailid',
												'b.labelvalue'
											)
											->leftJoin('service_photo_labels as b','b.labelid','=','a.labelid')
											->where('a.detailid','=',$validatedData['detailid'])
											->where('a.picturetime','=','STARTING')
											->get();
					
					if($startphotos)
					{
						foreach($startphotos as $start)
						{
							$start->pictureurl	=	$this->appUrl."/storage/".$start->pictureurl;
						}
					}
					$finishphotos	=	DB::table('service_photos as a')
											->select(
												'a.pictureurl',
												'a.detailid',
												'b.labelvalue'
											)
											->leftJoin('service_photo_labels as b','b.labelid','=','a.labelid')
											->where('a.detailid','=',$validatedData['detailid'])
											->where('a.picturetime','=','FINISHING')
											->get();
					if($finishphotos)
					{
						foreach($finishphotos as $finish)
						{
							$finish->pictureurl	=	$this->appUrl."/storage/".$finish->pictureurl;
						}
					}
					
					return response()->json(array_merge(['message'=>'SERVICE DETAIL AND REVIEW','status' => 200,'startphotos'=>$startphotos,'finishphotos'=>$finishphotos]),200);
				}
				else
				{
					return response()->json(['message'=>'INVALID DATA','status'=>401],401);
				}
			}
			catch(QueryException $e) 
			{
				return response()->json(['message'=>$e->getMessage(),'status'=>500],500);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401], 401);
		}
	}


	public function getJobHistory(Request $request)
	{
	
        $rules = [
			'customerid'	=>	'required',
			'accesstoken'	=>	'required',
			'detailid'		=>	'required|numeric',
        ];

        $messages = [
			'customerid.required' 	=> 'CUSTOMER DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
        ];
		
        $validatedData	=	$request->validate($rules, $messages);
		
		$isexist	=	$this->CheckCustomer($validatedData['customerid'], $validatedData['accesstoken']);

		if (!$isexist) {
			return response()->json([
				'message'    => __('messages.unauthorized'),
				'status'     => 401,
				'vendorid'   => $validatedData['customerid'],
				'accesstoken'=> $validatedData['accesstoken']
			], 401);
		}

		try
		{
			$detail = DB::table('customer_order_detail as a')
				->select(
					'a.creationdate as orderdatetime',
					'a.assignedtime',
					'a.vendorname',
					'a.workstarttime',
					'a.workendtime',
					'a.servicedate',
					'a.slottime',
					'a.isinterchanged',
					'a.onholdreason'
				)
				->where([
					['a.detailid', '=', $validatedData['detailid']],
					['a.customerid', '=', $validatedData['customerid']],
				])
				->first();

			if (!$detail)
			{
				return response()->json([
					'message'    => 'INVALID DATA',
					'status'     => 401,
					'customerid' => $validatedData['customerid'],
					'accesstoken'=> $validatedData['accesstoken']
				], 401);
			}

			
			if (!empty($detail->onholdreason))
			{
				$detail->onholddate = DB::table('onhold_history')
					->where('detailid', $validatedData['detailid'])
					->value('creationdate');
			}

			if ($detail->isinterchanged == 1)
			{
				$interchanges = DB::table('interchange_history as a')
					->select(
						'b.name as fromvendor', 'b.middlename as fromvendormiddlename', 'b.lastname as fromvendorlastname',
						'c.name as tovendor',   'c.middlename as tovendormiddlename',   'c.lastname as tovendorlastname',
						'a.interchangedate', 'a.interchangereason'
					)
					->leftJoin('vendor_tbl as b', 'b.vendorid', '=', 'a.fromvendorid')
					->leftJoin('vendor_tbl as c', 'c.vendorid', '=', 'a.tovendorid')
					->where('a.detailid', $validatedData['detailid'])
					->get();

				foreach ($interchanges as $interchange)
				{
					$interchange->fromvendor = trim(
						$interchange->fromvendor . ' ' .
						($interchange->fromvendormiddlename ?? '') . ' ' .
						($interchange->fromvendorlastname ?? '')
					);

					$interchange->tovendor = trim(
						$interchange->tovendor . ' ' .
						($interchange->tovendormiddlename ?? '') . ' ' .
						($interchange->tovendorlastname ?? '')
					);

					unset(
						$interchange->fromvendormiddlename,
						$interchange->fromvendorlastname,
						$interchange->tovendormiddlename,
						$interchange->tovendorlastname
					);
				}

				$detail->interchange = $interchanges;
			}
			else
			{
				$detail->interchange = [];
			}

			unset($detail->isinterchanged);

			return response()->json(['message'=> 'JOB HISTORY DETAIL','status'=> 200,'data'=> $detail], 200);
		}
		catch (QueryException $e)
		{
			return response()->json(['message'=> $e->getMessage(),'status'=> 500],500);
		}

	}


    public function storeCareerData(Request $request)
	{
        $rules = [
            'name'  		=> 'required|regex:/^[a-zA-Z\s.]+$/|max:50',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email' 		=> 'required|regex:/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/|max:100',
			'message'  		=> 'required',
			'resume' 		=> 'required|file|mimes:pdf,doc,docx|max:1024',
        ];

        $messages = [
            'name.required' 		=> 'PLEASE ENTER NAME',
			'name.regex' 			=> 'PLEASE ENTER VALID NAME',
			'name.max' 				=> 'PLEASE ENTER VALID NAME',
			'mobilenumber.required' => 'PLEASE ENTER MOBILE NUMBER',
			'mobilenumber.regex' 	=> 'PLEASE ENTER VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
            'email.required' 		=> 'PLEASE ENTER EMAIL ADDRESS',
			'email.regex' 			=> 'PLEASE PROVIDE VALID EMAIL',
			'message.required' 		=> 'PLEASE ENTER YOUR MESSAGE',
			'resume.required' 		=> 'PLEASE UPLOAD YOUR RESUME',
			'resume.max' 			=> 'ONLY 1 MB SIZE IS ALLOWED',
			'resume.file' 			=> 'RESUME MUST BE A FILE',
			'resume.mimes'          => 'PLEASE UPLOAD VALID FILE',
			
        ];

        $validatedData 	= $request->validate($rules, $messages);
		
        $name   		= 	(String) $request->input('name');
		$mobilenumber   = 	(String) $request->input('mobilenumber');
		$email   		= 	$request->input('email');
		$message   		= 	$request->input('message') ?? '';
		$resume   		= 	$request->file('resume') ?? '';

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
		
		try
		{
			if($resume!='')
			{
				$resume	=	$request->file('resume')->store('uploads/resumes','public');
			}
			
			$result	=	DB::insert('insert into resume_tbl(name,mobilenumber,email,message,resumefile,creationdate) values(?,?,?,?,?,?)',[$name,$mobilenumber,$email,$message,$resume,$creationdate]);
			
			return response()->json(['message' =>'RESUME UPLOADED SUCCESSFULLY','status'=>200], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
		}			

    }

}