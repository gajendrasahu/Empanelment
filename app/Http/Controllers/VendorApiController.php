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
use App\Http\Controllers\Master\ResourcesController;
use Illuminate\Support\Facades\Artisan;
use App\Jobs\CheckSingleOrderStatusJob;
use App\Jobs\ProcessNotificationJob;
use App\Services\RazorpayService;
use Razorpay\Api\Api;
use Barryvdh\DomPDF\Facade\Pdf;
//use PDF;
use App\Services\FCMService;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Exception;

class VendorApiController extends Controller
{
	protected $smsService;
    protected $razorpay;
	protected $resourceController;
	protected $appUrl;
	protected $fcm;
    protected $todays_datetime;
    protected $todays_date;	

	/*
	$ipAddress 		= request()->ip();
	$response 		= Http::get("http://ip-api.com/json/{$ipAddress}");

	if($response->successful())
	{
		$data = $response->json();

		$country 	= 	$data['country'];  // Country
		$region 	= 	$data['regionName'];  // Region (State)
		$city 		= 	$data['city'];  // City
		$latitude 	=	$data['lat'];  // Latitude
		$longitude 	=	$data['lon'];  // Longitude
	}
	*/
	
	public function __construct(SmsService $smsService,ResourcesController $resourceController)
	{
		$this->appUrl 				=	Config::get('app.url');
		$this->smsService 			=	$smsService;
		$this->razorpay 			=	new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
		$this->resourceController 	=	$resourceController;
		$this->fcm					=	new FCMService();
		ini_set('serialize_precision', -1);
		$this->todays_datetime		=	Carbon::now()->format('Y-m-d H:i:s');
        $this->todays_date			=	Carbon::now()->format('Y-m-d');		
	}

	public function sendPaymentLink($financialyear,$distinctCustomerId,$distinctOrderId,$vendorid,$amount,$detailids)
	{
		
		$order		=	DB::table('customer_order')->where('orderid','=',$distinctOrderId)->first();
		
		$customer	=	DB::table('customer_tbl')->where('customerid','=',$order->customerid)->first();
	
		$customername	=	$customer->name;
		if($customer->middlename!='')
		$customername	=	$customername.' '.$customer->middlename;
		if($customer->lastname!='')
		$customername	=	$customername.' '.$customer->lastname;

		$receipt		=	"RCPT".rand(100,999)."".strtotime(date('Y\-m\-d H:i:s'));		
		$lastId = DB::table('receipt_tbl')->insertGetId([
			'financialyear' => $financialyear,
			'receiptnumber'	=> $receipt,
			'customerid'    => $distinctCustomerId,
			'orderid'       => $distinctOrderId,
			'vendorid'      => $vendorid,
			'netamount'     => $amount,
			'paymentstatus'	=>'pending',
			'generateddate'=> date('Y\-m\-d H:i:s'),
			'detailids'     => is_array($detailids) ? implode(',', $detailids) : $detailids, // ensure string
		]);
	
		$response = $this->razorpay->paymentLink->create([
			'amount'          => $amount*100, // amount in paise (e.g., ₹500.00 = 50000)
			'currency'        => 'INR',
			'accept_partial'  => false,
			'description'     => 'UN PAID SERVICES PAYMENT',
			'customer'        => [
				'name'    => $customername,
				'contact' => $customer->mobilenumber,
				'email'   => $customer->email
			],
			'notify' => [
				'sms'  => true,
				'email'=> false
			],
			'callback_url'    => route('paymentlinkcallback'),
			'callback_method' => 'get',
		]);

		DB::update('update receipt_tbl set payment_link_id=? where receiptid=? and paymentstatus=?',[$response['id'],$lastId,'pending']);
		
		return response()->json(['payment_link' => $response['short_url']]);
	}	

	public function sendOTPMessage(Request $request)
	{
		$isSent 	= 	$this->smsService->pushMessage('9479020074',0,'BOOKING',0,'');
		return response()->json(['message' =>'SENT','status'=>200,'issent'=>$isSent], 200);
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

	private function pageCount($records,$pagelimit)
	{
		
		$count	=	intval($records/$pagelimit);
		if($count==0)
		$count++;
		return $count;
	}

    public function testNotification(Request $request)
    {
		
		exec("ps aux | grep 'artisan queue:work' | grep -v grep", $output);
		foreach ($output as $line) {
			preg_match('/\s+(\d+)\s+/', $line, $matches);
			if (isset($matches[1])) {
				$pid = $matches[1];
				exec("kill -9 $pid");
			}
		}
		

		/*
		$title 		=	"New Task";
        $fcmToken 	= 	$request->input('token'); // token from mobile
        $title 		=	"New Task";
        $body 		= 	"A new Category Name 1 job is available near you. Tap to view details and accept the task.";
		$channelid	=	"channel_509";
        $fcm		=	new FCMService();
		$response	=	$fcm->sendFcmNotification($fcmToken,$title,$body,'NEWTASK',$channelid);
		
		*/
        //return response()->json($response);
		return response()->json(['message'=>'SUCCESS']);
    }
	/*
    public function sendToDevice(Request $request, FCMService $fcm)
    {
        $token = $request->input('token');
        $title = $request->input('title', 'Test Notification');
        $body = $request->input('body', 'This is a message from Laravel.');

        $response = $fcm->sendNotification($token, $title, $body);

        return response()->json($response);
    }
	*/	
	private function CheckVendor($vendorid,$accesstoken)
	{
		$cacheKey = "vendor_{$vendorid}_{$accesstoken}";
		return Cache::remember($cacheKey,60,function () use ($vendorid, $accesstoken)
		{
				return DB::table('vendor_tbl')
						 ->where('vendorid', '=', $vendorid)
						 ->where('accesstoken', '=', $accesstoken)
						 ->first();
		});
	}
	private function getOTP()
	{
		//$otp	=	rand(100000,999999);
		$otp	=	"555555";
		return $otp;
	}

	private function TodaysDashboard($vendorid,$isemployee)
	{
		$vendorIds = [$vendorid];
		if($isemployee==0)
		{
			$vendorIds = DB::table('vendor_tbl')
				->where('vendorid', $vendorid)
				->orWhere('parentvendorid', $vendorid)
				->pluck('vendorid')
				->toArray();
		}		
		
		$allStatuses = [1, 2, 3, 4];
		$results = DB::table('customer_order_detail')
			->whereIn('vendorid', $vendorIds)
			->whereIn('orderstatus', $allStatuses)
			->select('orderstatus', DB::raw('COUNT(*) as count'))
			->groupBy('orderstatus')
			->get()
			->keyBy('orderstatus'); // Makes lookup faster		
		
		$finalResults = collect();
		foreach($allStatuses as $status)
		{
			$finalResults->push((object)[
				'orderstatus' => $status,
				'count' => isset($results[$status]) ? $results[$status]->count : 0
			]);
		}		
		$finalResults->totalCount = $finalResults->sum('count');
		return $finalResults->isEmpty() ? [] : $finalResults;
		
		
		
		/*		
		if($isemployee==0)
		{
			$vendorIds = DB::table('vendor_tbl')
							->select('vendorid')
							->where('vendorid', $vendorid)
							->orWhere('parentvendorid', $vendorid)
							->get()
							->pluck('vendorid')
							->implode(',');

			if($vendorIds!='')
			{
				$vendorIdsArray =	explode(',', $vendorIds);
				$results		=	DB::table('customer_order_detail')
									->whereIn('vendorid', $vendorIdsArray)
									->whereIn('orderstatus', [1,2,3])
									->select('orderstatus', DB::raw('COUNT(*) as count'))
									->groupBy('orderstatus')
									->get();  

				$onhold			=	DB::table('customer_order_detail')
									->whereIn('vendorid', $vendorIdsArray)
									->whereIn('orderstatus', [4])
									->select('orderstatus', DB::raw('COUNT(*) as count'))
									->groupBy('orderstatus')
									->get();  

				$combinedResults = $results->merge($onhold);
				
				$allStatuses	=	[1,2,3,4];
				$finalResults	=	collect();

				foreach($allStatuses as $status)
				{
					$statusData = $combinedResults->firstWhere('orderstatus', $status);

					if($statusData)
					{
						$finalResults->push($statusData);
					}
					else
					{
						$finalResults->push((object) [
							'orderstatus' => $status,
							'count' => 0
						]);
					}
				}
				$totalCount = $finalResults->filter(function ($item) {
					return in_array($item->orderstatus, [1,2,3,4]);  // Filter for orderstatus 1, 2, and 3
				})->sum('count');
				
				$finalResults->totalCount = $totalCount;
			}

			if($finalResults->isEmpty())
			{
				return [];
			}
			return $finalResults;
		}
		else
		{
			$results =	DB::table('customer_order_detail')
							->where('vendorid', $vendorid)
							->whereIn('orderstatus', [1,2,3])
							->select('orderstatus', DB::raw('COUNT(*) as count'))
							->groupBy('orderstatus')
							->get();  

			$onhold	=	DB::table('customer_order_detail')
							->where('vendorid', $vendorid)
							->whereIn('orderstatus', [4])
							->select('orderstatus', DB::raw('COUNT(*) as count'))
							->groupBy('orderstatus')
							->get();

			$combinedResults = $results->merge($onhold);
			
			$allStatuses	=	[1, 2, 3, 4];
			$finalResults	=	collect();

			foreach($allStatuses as $status)
			{
				$statusData = $combinedResults->firstWhere('orderstatus', $status);

				if($statusData)
				{
					$finalResults->push($statusData);
				}
				else
				{
					$finalResults->push((object) [
						'orderstatus' => $status,
						'count' => 0
					]);
				}
			}
			$totalCount = $finalResults->filter(function ($item) {
				return in_array($item->orderstatus, [1, 2, 3,4]);  // Filter for orderstatus 1, 2, and 3
			})->sum('count');
			
			$finalResults->totalCount = $totalCount;

			if($finalResults->isEmpty())
			{
				return [];
			}
			return $finalResults;
		}
		*/
	}
	private function getDiscount()
	{

		$tierId = DB::table('city_tbl')->where('isdefault', 1)->value('tierid');

		if (!$tierId) {
			return 0;
		}

		$cacheKey = "discount_value_{$tierId}";

		return Cache::remember($cacheKey, now()->addHours(1), function () use ($tierId) {
			return DB::table('tier_tbl')->where('tierid', $tierId)->value('discount') ?? 0;
		});		
	}

	private function AvailableJobs($vendorid,$isemployee)
	{
		if($isemployee==0)
		{
			$categoryIds = DB::table('vendor_tbl')
							->select('categoryids')
							->where('vendorid', $vendorid)
							->first();

			if($categoryIds->categoryids!='')
			{
				$categoryIdsArray	=	explode(',',$categoryIds->categoryids);
				$results 			=	DB::table('customer_order_detail as a')
											->select('a.detailid','a.orderid','a.categoryid','a.serviceid','a.optionid','a.quantity','a.servicecharge','a.taxable','a.taxvalue','a.payable','a.slottime','c.name','c.middlename','c.lastname',DB::raw("COALESCE(b.address, '') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude, '') as latitude"),DB::raw("COALESCE(b.longitude, '') as longitude"))
											->leftjoin('customer_address as b','b.addressid','=','a.addressid')
											->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
											->whereIn('a.categoryid',$categoryIdsArray)
											->whereIn('a.orderstatus',[0])
											->get();
				return $results;
			}
			else
			{
				$results	=	[];
				return $results;
			}
		}
		else
		{
			$results 	=	DB::table('customer_order_detail as a')
							->select('a.orderid','a.categoryid','a.serviceid','a.optionid','a.quantity','a.servicecharge','a.taxvalue','a.total','a.discount','a.netpayable','a.slottime',DB::raw("COALESCE(b.address, '') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude, '') as latitude"),DB::raw("COALESCE(b.longitude, '') as longitude"))
							->leftjoin('customer_address as b','b.addressid','=','a.addressid')
							->where('orderstatus','=',1)
							->where('vendorid','=',$vendorid)
							->whereIn('a.orderstatus',[0])
							->get();
			if(!$results)
			{
				$results	=	[];
			}
			return $results;			
		}
	}

	private function CheckAadhaar($aadhaarnumber)
	{
		$exist	=	DB::table('vendor_tbl')->where('aadhaarnumber','=',$aadhaarnumber)->first();
		return $exist;
	}
	private function CheckPanNumber($pannumber)
	{
		$exist	=	DB::table('vendor_tbl')
						->where('pannumber','=',$pannumber)
						->first();
		return $exist;
	}
	private function CheckDrivingLicence($drivinglicence)
	{
		$exist	=	DB::table('vendor_tbl')
						->where('drivinglicence','=',$drivinglicence)
						->first();
		return $exist;
	}
    public function getVendorCategory(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid'  		=> 'required',
			'accesstoken'  		=> 'required',
        ];

        $messages = [
            'vendorid.required' 	=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validated 		=	$request->validate($rules, $messages);
		$vendorid    	=	intval($request->input('vendorid'));
		$accesstoken 	=	(string) $request->input('accesstoken');		
		$cacheKey 		=	'vendor_category_'.$vendorid;
		
		try
		{
			// Cache the vendor check for 10 minutes
			$vendor = Cache::remember("vendor_auth_$vendorid", 20, function () use ($vendorid, $accesstoken) {
				return DB::table('vendor_tbl')
					->select('vendorid', 'name', 'mobilenumber', 'isactive', 'isverified', 'isemployee', 'categoryids')
					->where('vendorid', $vendorid)
					->where('accesstoken', $accesstoken)
					->first();
			});

			if (!$vendor) {
				return response()->json([
					'message' => __('messages.notfound'),
					'status'  => 400,
				], 400);
			}

			if ($vendor->isemployee != 0) {
				return response()->json([
					'message' => 'INVALID VENDOR',
					'status'  => 400,
				], 400);
			}

			// Use cache for category list
			$category = Cache::remember($cacheKey, 60, function () use ($vendor) {
				$catIds = array_filter(explode(',', $vendor->categoryids));

				$parentCategoryIds = DB::table('category_tbl')
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
			});

			return response()->json([
				'message' => __('messages.recordlist'),
				'status'  => 200,
				'data'    => $category,
			], 200);
		}
		catch (QueryException $e)
		{
			Log::error('Vendor Category Fetch Error: ' . $e->getMessage());

			return response()->json([
				'message' => __('messages.notfound'),
				'status'  => 400,
			], 400);
		}
    }
	
    public function masterCategoryList(Request $request)
	{

		try
		{
			$cacheKey = 'master_category_list';
			$category = Cache::remember($cacheKey, 60, function () {
				return DB::table('category_tbl')
					->select('categoryid', 'category')
					->where('parentcategoryid', '=', 0)
					->orderBy('displayorder')
					->get();
			});
			
			return response()->json([
				'message' => __('messages.recordlist'),
				'status'  => 200,
				'data'    => $category
			], 200);			
			
			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'data'=>$category], 200);
		}
		catch (QueryException $e)
		{
			Log::error('Master Category List Fetch Error: ' . $e->getMessage());

			return response()->json([
				'message' => __('messages.notfound'),
				'status'  => 400
			], 400);
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

		$categoryids = (string) $request->input('categoryids');
		$categoryIdsArray = array_filter(array_map('intval', explode(',', $categoryids)));
		$cacheKey = 'subcategory_list_' . md5(implode(',', $categoryIdsArray));

		try
		{
			$category = Cache::remember($cacheKey, 30, function () use ($categoryIdsArray)
			{
				return DB::table('category_tbl')
					->select('categoryid as subcategoryid', 'category as subcategory')
					->whereIn('parentcategoryid', $categoryIdsArray)
					->get();
			});

			return response()->json([
				'message' => __('messages.recordlist'),
				'status'  => 200,
				'data'    => $category
			], 200);

		}
		catch (QueryException $e)
		{
			Log::error('SubCategory List Fetch Error: ' . $e->getMessage());

			return response()->json([
				'message' => __('messages.notfound'),
				'status'  => 400
			], 400);
		}

    }

    public function categoryList(Request $request)
	{
        $rules = [
            'categoryid'	=>	'nullable|numeric',
        ];

        $messages = [
            'categoryid.required' 	=> __('validation.thisis.required'),
			'categoryid.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $categoryid =	intval($request->input('categoryid', 0));
        $searchtext =	trim($request->input('searchtext', ''));
		try
		{
			$cacheKey 	= 	'category_list_' . $categoryid . '_' . md5($searchtext);		
			
			$response = Cache::remember($cacheKey,30,function () use ($categoryid, $searchtext)
			{

				$parentname = '';
				if ($categoryid !== 0) {
					$parent = DB::table('category_tbl')->select('category')->where('categoryid', $categoryid)->first();
					$parentname = $parent->category ?? '';
				}

				$categories = DB::table('category_tbl')
					->select(
						'categoryid',
						'category',
						'headingvalue',
						'displayorder',
						'description',
						'metakeywords',
						'metadescription',
						'categoryicon',
						'categorypage'
					)
					->when($categoryid === 0, function ($query) {
						return $query->where('parentcategoryid', 0);
					})
					->when($categoryid !== 0, function ($query) use ($categoryid) {
						return $query->where('parentcategoryid', $categoryid);
					})
					->when(!empty($searchtext), function ($query) use ($searchtext) {
						return $query->where('category', 'like', '%' . $searchtext . '%');
					})
					->where('categorystatus', 1)
					->orderBy('displayorder')
					->get();

				$appUrl = Config::get('app.url');

				foreach ($categories as $cat) {
					// Add icon and page URLs
					$cat->categoryicon = $cat->categoryicon ? $appUrl . '/storage/' . $cat->categoryicon : '';
					$cat->categorypage = $cat->categorypage ? $appUrl . '/storage/' . $cat->categorypage : '';

					// Check for child categories
					$cat->haschild = DB::table('category_tbl')
						->where('parentcategoryid', $cat->categoryid)
						->exists() ? 1 : 0;

					// Attach related services
					$cat->services = DB::table('service_tbl')
						->select('serviceid', 'servicetitle', 'visitingcharge', 'mrp', 'requiredtime', 'description', 'hsncode')
						->where('categoryid', $cat->categoryid)
						->get();
				}

				return [
					'parentname' => $parentname,
					'data' => $categories
				];
			});

			return response()->json([
				'message' => 'RECORDS FETCHED SUCCESSFULLY.',
				'status' => 200,
				'parentname' => $response['parentname'],
				'data' => $response['data']
			], 200);

		}
		catch(\Exception $e)
		{
			Log::error('CATEGORY LIST FETCH ERROR: ' . $e->getMessage());

			return response()->json([
				'message' => 'UNABLE TO FETCH CATEGORIES. PLEASE TRY AGAIN LATER.',
				'status' => 400
			], 400);
		}
    }
	
    public function cityList(Request $request)
	{
        $rules = [
            'stateid'  		=> 'nullable|numeric',
			'searchtext'  	=> 'nullable',
        ];

        $messages = [
			'stateid.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validated 	= $request->validate($rules, $messages);
		
		try
		{
			$stateid    = 	intval($validated['stateid'] ?? 0);
			$searchtext = 	trim($validated['searchtext'] ?? '');			
			$cacheKey 	= 	'city_list_' . $stateid . '_' . md5($searchtext);
			
			$citylist = Cache::remember($cacheKey, 30, function () use ($stateid, $searchtext) {
				return DB::table('city_tbl')
					->select('cityid', 'cityname', 'aliasname', 'isdefault', 'operatingstatus', 'tierid')
					->when($stateid !== 0, function ($query) use ($stateid) {
						return $query->where('stateid', '=', $stateid);
					})
					->when(!empty($searchtext), function ($query) use ($searchtext) {
						return $query->where('cityname', 'like', '%' . $searchtext . '%');
					})
					->orderBy('cityname')
					->get();
			});			


			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'data'=>$citylist], 200);
		}
		catch(QueryException $e)
		{
			Log::error('CITY LIST FETCH ERROR: ' . $e->getMessage());
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

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'data'=>$arealist], 200);
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
			$cacheKey = 'state_list';
			$statelist = Cache::remember($cacheKey, 30, function () {
				return DB::table('state_tbl')
					->select('stateid', 'statename', 'aliasname')
					->where('stateid', '=', 1)
					->orderBy('statename')
					->get();
			});			

			return response()->json(['message' =>__('messages.recordlist'),'status'=>200,'data'=>$statelist], 200);
		}
		catch(QueryException $e)
		{
			Log::error('State List Fetch Error: ' . $e->getMessage());
			return response()->json(['message' =>'UNABLE TO FETCH STATES. PLEASE TRY AGAIN LATER.','status'=>400], 400);
		}			
    }
	
    public function generateVendorOtp(Request $request)
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

		$mobilenumber   = $request->input('mobilenumber');
		$name           = '';
		
		$otp			=	$this->getOTP();
		
		$creationdate   = now()->format('Y-m-d H:i:s');
		$isTestNumber   = $mobilenumber === '9876543211';		
		
        try
        {
			$vendor = DB::table('vendor_tbl')
						->select('vendorid')
						->where('mobilenumber', $mobilenumber)
						->first();			

			if($vendor)
			{
				// Existing vendor: update OTP
				if (!$isTestNumber) {
					$isSent = $this->smsService->pushMessage($mobilenumber,$otp,'OTP',0,'');
					if (!$isSent)
					{
						return response()->json([
							'message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
							'status' => 400,
							'mobilenumber' => $mobilenumber
						], 400);
					}
				}

				DB::update('UPDATE vendor_tbl SET otp = ? WHERE vendorid = ?', [$otp, $vendor->vendorid]);

			} 
			else
			{
				// New vendor: insert record with OTP
				if (!$isTestNumber) {
					$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');
					if (!$isSent) {
						return response()->json([
							'message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
							'status' => 400,
							'mobilenumber' => $mobilenumber
						], 400);
					}
				}

				DB::insert('INSERT INTO vendor_tbl (name, mobilenumber, otp, isactive, creationdate, ip_country, ip_countrycode, ip_region, ip_regionname, ip_city, ip_zip, ip_lat, ip_lon, ip_timezone, ip_isp, areaids, fcmid) 
							VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', 
							[$name, $mobilenumber, $otp, 0, $creationdate, '', '', '', '', '', '', '', '', '', '', '', '']);
			}			
			
			return response()->json([
				'message' => 'OTP SENT SUCCESSFULLY',
				'status' => 200,
				'mobilenumber' => $mobilenumber
			], 200);
        }
        catch (QueryException $e) 
        {
			Log::error('Get Employee Form Error: ' . $e->getMessage());
            return response()->json(['message'=>'We encountered a technical issue while processing your request. Please try again later.','status'=>400,'name'=>$name,'mobilenumber'=>$mobilenumber], 400);
        }
    }
    public function vendorOtpVerification(Request $request)
    {
        $rules = [
            'mobilenumber' 		=> 'required',
            'otp' 				=> 'required',
			'fcmid'				=> 'required',
        ];

        $messages = [
            'mobilenumber.required' 	=> 'MOBILE NUMBER IS REQUIRED',
            'otp.required' 				=> 'OTP IS REQUIRED',
			'fcmid.required'			=> 'FIREBASE CLOUD MESSAGING ID IS REQUIRED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);


		$mobilenumber	=	$request->input('mobilenumber');
		$otp 			= 	$request->input('otp');
		$fcmid 			= 	(string) $request->input('fcmid');
		$appUrl 		= 	Config::get('app.url');
		$accesstoken 	=	rand(10000000, 99999999);
		$channelid 		=	'';
		$creationdate 	=	now()->format('Y-m-d H:i:s');

        try
        {
			$vendor = DB::table('vendor_tbl')
				->where('mobilenumber', $mobilenumber)
				->where('otp', $otp)
				->first();			
			
			if (!$vendor) {
				return response()->json([
					'message' => 'INVALID OTP OR MOBILE NUMBER',
					'status' => 400,
					'mobilenumber' => $mobilenumber,
					'otp' => $otp,
				], 400);
			}			
			
			$channelid = "channel_" . $vendor->vendorid;
			DB::update('UPDATE vendor_tbl SET fcmid = ?, accesstoken = ?, channelid = ?, minimumbalance = ? WHERE vendorid = ?', [
				$fcmid, $accesstoken, $channelid, 1000, $vendor->vendorid
			]);
			
			if (!empty($vendor->profilepic)) {
				$vendor->profilepic = $appUrl . "/storage/" . $vendor->profilepic;
			}

			if($vendor->isemployee==0)
			{
				if ($vendor->isverified == 0) 
				{
					return response()->json([
						'message' => 'FILL BASIC DETAILS',
						'status' => 200,
						'authenticate' => [
							'vendorid' => $vendor->vendorid,
							'accesstoken' => $accesstoken,
							'goto' => 1
						],
						'vendorid' => $vendor->vendorid,
						'accesstoken' => $accesstoken,
						'goto' => 1
					], 200);
				}
				else
				{
					DB::update('UPDATE vendor_tbl SET isverified = 1 WHERE vendorid = ?', [$vendor->vendorid]);
				}
			}

			$vendor = DB::table('vendor_tbl')
				->select(
					'vendorid', 'accesstoken', 'name', 'email', 'middlename', 'lastname',
					'mobilenumber', 'profilepic', 'isverified', 'verificationstatus',
					'isprofilecompleted', 'isemployee', 'isapproved', 'walletamount', 'isavailable'
				)
				->where('vendorid', $vendor->vendorid)
				->first();
			
			if (!empty($vendor->profilepic)) {
				$vendor->profilepic = $appUrl . "/storage/" . $vendor->profilepic;
			}

			return response()->json([
				'message' => 'GO TO DASHBOARD',
				'status' => 200,
				'authenticate' => [
					'vendorid' => $vendor->vendorid,
					'accesstoken' => doubleval($vendor->accesstoken),
					'isverified' => $vendor->isverified,
					'verificationstatus' => $vendor->verificationstatus,
					'isprofilesubmitted' => $vendor->isprofilecompleted,
					'isemployee' => $vendor->isemployee,
					'isapproved' => $vendor->isapproved,
					'isavailable' => $vendor->isavailable,
					'goto' => 2
				],
				'vendorid' => $vendor->vendorid,
				'accesstoken' => $vendor->accesstoken,
				'data' => $vendor,
				'goto' => 2
			], 200);

		}
        catch(QueryException $e) 
        {
			return response()->json([
				'message' => 'We encountered a technical issue while verifying your OTP. Please try again.',
				'status' => 400,
				'mobilenumber' => $mobilenumber,
			], 400);
        }
    }
    public function updateBasicDetail(Request $request)
    {
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
        $rules = [
            'vendorid' 		=> 'required',
            'accesstoken' 	=> 'required',
			'name'  		=> 'required|regex:/^[a-zA-Z\s]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'lastname'  	=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'stateid' 		=> 'required',
			'cityid' 		=> 'required',
			'address' 		=> 'required',
			'pincode' 		=> 'required|numeric',
			'subcategoryids'=> 'required',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
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
			'subcategoryids.required'	=> 'PLEASE SELECT ATLEAST ONE CATEGORY NAME',
        ];

        $validatedData 	=	$request->validate($rules, $messages);


		$vendorid = intval($request->input('vendorid'));
		$accesstoken = $request->input('accesstoken');

		// Get input values
		$name 			= (string) $request->input('name');
		$middlename 	= (string) $request->input('middlename');
		$lastname 		= (string) $request->input('lastname');
		$stateid 		= intval($request->input('stateid'));
		$cityid 		= intval($request->input('cityid'));
		$address 		= (string) $request->input('address');
		$pincode 		= (string) $request->input('pincode');
		$subcategoryids = (string) $request->input('subcategoryids');



        try
        {
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if(!$vendor)
			{
				return response()->json([
					'message' => 'INVALID VENDOR DETAIL PROVIDED',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
				], 400);
			}

			DB::update(
				'UPDATE vendor_tbl SET name=?, middlename=?, lastname=?, stateid=?, cityid=?, completeaddress=?, postalcode=?, categoryids=?, isverified=? WHERE vendorid=?',
				[$name, $middlename, $lastname, $stateid, $cityid, $address, $pincode, $subcategoryids, 1, $vendorid]
			);

			$updatedVendor = DB::table('vendor_tbl')
				->select('vendorid', 'accesstoken', 'name', 'middlename', 'lastname', 'mobilenumber', 'profilepic', 'isverified', 'verificationstatus', 'isprofilecompleted', 'isemployee', 'isapproved', 'walletamount', 'isavailable')
				->where('vendorid', $vendorid)
				->first();

			if (!empty($updatedVendor->profilepic)) {
				$appUrl = Config::get('app.url');
				$updatedVendor->profilepic = $appUrl . "/storage/" . $updatedVendor->profilepic;
			}

			$authenticate = [
				'vendorid' 			=> $updatedVendor->vendorid,
				'accesstoken' 		=> doubleval($updatedVendor->accesstoken),
				'isverified' 		=> $updatedVendor->isverified,
				'verificationstatus'=> $updatedVendor->verificationstatus,
				'isprofilesubmitted'=> $updatedVendor->isprofilecompleted,
				'isemployee' 		=> $updatedVendor->isemployee,
				'isapproved' 		=> $updatedVendor->isapproved,
				'isavailable' 		=> $updatedVendor->isavailable,
				'goto' 				=> 2,
			];

			return response()->json([
				'message' => 'GO TO DASHBOARD',
				'status' => 200,
				'authenticate' => $authenticate,
				'data' => $updatedVendor,
				'goto' => 2,
				'isapproved' => 0
			], 200);

        }
        catch (QueryException $e) 
        {
			return response()->json([
				'message' => 'Something went wrong while updating your details. Please try again.',
				'status' => 400,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
			], 400);            
        }
    }
	
    public function goToDashboard(Request $request)
    {
		$request->merge([
			'accesstoken'	=>	$request->header('accesstoken'),
			'vendorid'		=>	$request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid' 		=>	'required',
            'accesstoken' 	=>	'required',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);
	
		$vendorid = intval($request->input('vendorid'));
		$accesstoken = $request->input('accesstoken');	



        try
        {
			$vendor = $this->CheckVendor($vendorid, $accesstoken);
			if (!$vendor) {
				return response()->json([
					'message' => 'INVALID VENDOR DETAIL PROVIDED',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
				], 400);
			}

			$data = DB::table('vendor_tbl')
				->where('vendorid', $vendorid)
				->first();

			if (!$data || $data->verificationstatus != 1) {
				return response()->json([
					'message' => 'INVALID VENDOR DETAIL PROVIDED',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
				], 400);
			}
			DB::update('UPDATE vendor_tbl SET isapproved = ? WHERE vendorid = ?', [1, $vendorid]);
			
			if (!empty($data->profilepic)) {
				$appUrl = Config::get('app.url');
				$data->profilepic = $appUrl . "/storage/" . $data->profilepic;
			}

			$authenticate = [
				'vendorid' => $data->vendorid,
				'accesstoken' => doubleval($data->accesstoken),
				'isverified' => $data->isverified,
				'verificationstatus' => $data->verificationstatus,
				'isprofilesubmitted' => $data->isprofilecompleted,
				'isemployee' => $data->isemployee,
				'isapproved' => 1, // Already updated
				'isavailable' => $data->isavailable,
			];
			$dashboard = $this->TodaysDashboard($vendorid, $data->isemployee);
			$availableJobs = $this->AvailableJobs($vendorid, $data->isemployee);

			return response()->json([
				'message' => 'GO TO DASHBOARD',
				'status' => 200,
				'authenticate' => $authenticate,
				'data' => $data,
				'dashboard' => $dashboard,
				'availablejobs' => $availableJobs
			], 200);
        }
        catch (QueryException $e) 
        {
			return response()->json([
				'message' => 'An error occurred while loading your dashboard. Please try again later.',
				'status' => 400,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
			], 400);            
        }
    }

    public function getAuthStatus(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid' 		=> 'required',
            'accesstoken' 	=> 'required',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $vendorid 		=	intval($request->header('vendorid'));
		$accesstoken 	=	$request->header('accesstoken');

        try
        {
            $isexist	=	$this->CheckVendor($vendorid,$accesstoken);
            if($isexist)
            {
				//$data	=	DB::table('vendor_tbl')->where('vendorid','=',$vendorid)->first();
				$authenticate	=	[
					'vendorid'			=>	$isexist->vendorid,
					'accesstoken'		=>	doubleval($isexist->accesstoken),
					'isverified'		=>	$isexist->isverified,
					'verificationstatus'=>	$isexist->verificationstatus,
					'isprofilesubmitted'=>	$isexist->isprofilecompleted,
					'isemployee'		=>	$isexist->isemployee,
					'isapproved'		=>	$isexist->isapproved,
					'isavailable'		=>	$isexist->isavailable,
				];
				return response()->json(['message' =>'Authentication Status','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'data'=>$authenticate], 200);
            }
            else
            {
				return response()->json(['message' => 'INVALID VENDOR DETAIL PROVIDED','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
            }
        }
        catch(QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }
    }


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
		
		$mobilenumber   = 	$request->input('mobilenumber');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			//$ipAddress 	=	request()->ip();
			//$ipdata		=	$this->getIpDetail($ipAddress);
			
            $isexist = DB::table('vendor_tbl')
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
					
					$otp			=	$this->getOTP();
					
					$accesstoken	=	rand(10000000,99999999);
					//pushMessage(string $mobile,string $otp, String $smstype,double $amount, String $paymentlink)
					
					if($mobilenumber!='9876543211')
					{
						
						$isSent 		= 	$this->smsService->pushMessage($mobilenumber,$otp,'OTP',0,'');
						DB::update('update vendor_tbl set otp=?,accesstoken=? where vendorid=?',[$otp,$accesstoken,$isexist->vendorid]);
						
						return response()->json(['message' => __('messages.otpgenerated'),'status'=>200,'mobilenumber'=>$mobilenumber,'accesstoken'=>$accesstoken], 200);
					}
					else
					{
						DB::update('update vendor_tbl set otp=?,accesstoken=? where vendorid=?',[$otp,$accesstoken,$isexist->vendorid]);
						
						return response()->json(['message' => __('messages.otpgenerated'),'status'=>200,'mobilenumber'=>$mobilenumber,'accesstoken'=>$accesstoken], 200);
						
					}
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
    public function vendorSignInVerification(Request $request)
    {
        $rules = [
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'otp'  			=> 'required|numeric',
			'fcmid'			=> 'required',
        ];

        $messages = [
			'mobilenumber.required' => 'PLEASE ENTER MOBILE NUMBER',
            'mobilenumber.regex' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'PLEASE PROVIDE VALID MOBILE NUMBER',
			'otp.required' 			=> 'PLEASE ENTER RECEIVED OTP',
			'otp.numeric' 			=> 'OTP SHOULD BE A NUMERIC VALUE',
			'fcmid.required' 		=> 'FIREBASE CLOUD MESSAGING IS REQUIRED',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);
		
		$mobilenumber   = 	$request->input('mobilenumber');
		$otp   			= 	$request->input('otp');
		$fcmid 			= 	$request->input('fcmid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
		
        try
        {
			//$ipAddress 	=	request()->ip();
			//$ipdata		=	$this->getIpDetail($ipAddress);
			
            $isexist = DB::table('vendor_tbl')
						->select('vendorid','name','email','mobilenumber','isactive','verificationstatus','isverified','isemployee','isselfemployeed','accesstoken','profilepic','isprofilecompleted','walletamount')
                        ->where('mobilenumber','=',$mobilenumber)
						->where('otp','=',$otp)
						->first();
            if($isexist)
            {
                if($isexist->isactive==0)
                {
                    return response()->json(['message' => __('messages.inactivevendor'),'status'=>201,'mobilenumber'=>$mobilenumber], 201);
                }
                else
                {
					$profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
					if($profilepic!='')
					{
						$appUrl = Config::get('app.url');
						$isexist->profilepic = $appUrl."/storage/".$profilepic;
					}
					DB::update('update vendor_tbl set fcmid=? where vendorid=?',[$fcmid,$isexist->vendorid]);
					return response()->json(['message' => 'LOGIN SUCCESSFUL!','status'=>200,'data'=>$isexist], 200);
                }
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

	public function getProfile(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validatedData 	= $request->validate($rules, $messages);

		try
		{
			$vendorid	=	$request->header('vendorid');
			$accesstoken=	$request->header('accesstoken');

			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if (!$isexist) {
				return response()->json([
					'message' => 'UNAUTHORIZED ACCESS',
					'status' => 401,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			$query = DB::table('vendor_tbl as a')
					->where('a.vendorid', $vendorid)
					->where('a.accesstoken', $accesstoken);

			if ($isexist->isemployee == 0)
			{
				$vendor = $query
					->leftJoin('state_tbl as c', 'c.stateid', '=', 'a.stateid')
					->leftJoin('city_tbl as d', 'd.cityid', '=', 'a.cityid')
					->select(
						'a.vendorid','a.name','a.middlename','a.lastname','a.mobilenumber','a.alternetnumber','a.email','a.gender','a.dob','a.stateid','c.statename','a.cityid','d.cityname','a.areaids','a.postalcode as pincode','a.completeaddress as address','a.aadhaarnumber','a.aadhaarfrontfile','a.aadhaarbackfile','a.pannumber','a.panfile','a.drivinglicence','a.licencefile','a.firmname','a.registrationnumber','a.firmcontact','a.firmemail','a.firmstateid','a.firmcityid','a.firmpostalcode','a.firmaddress','a.firmpannumber','a.firmpanfile','a.gstnumber','a.gstfile','a.bankname','a.accountnumber as bankaccount','a.bankfile','a.ifsccode','a.bankbranch','a.profilepic','a.categoryids','a.verificationstatus','isselfemployeed','a.isapproved','a.isverified','isprofilecompleted as isprofilesubmitted','a.walletamount','a.isavailable')
					->first();

				if(!$vendor) 
				{
					return response()->json(['message' => 'Vendor not found', 'status' => 404], 404);
				}

				$fieldsToPrefix = [
					'profilepic', 'aadhaarfrontfile', 'aadhaarbackfile',
					'panfile', 'bankfile', 'firmpanfile', 'gstfile', 'licencefile'
				];

				foreach ($fieldsToPrefix as $field) {
					if (!empty($vendor->$field)) {
						$vendor->$field = url("storage/{$vendor->$field}");
					}
				}

				$categoryIds = array_filter(explode(',', $vendor->categoryids));

				$parents = DB::table('category')
					->whereIn('categoryid', $categoryIds)
					->distinct()
					->pluck('parentcategoryid');

				$subcat = DB::table('category')
					->whereIn('categoryid', $categoryIds)
					->distinct()
					->pluck('categoryid');

				$vendor->categoryids = $parents;
				$vendor->subcategoryids = $subcat;

				$vendor->category = DB::table('category_tbl')
					->select('categoryid', 'category')
					->whereIn('categoryid', $parents)
					->get();

				$vendor->subcategory = DB::table('category_tbl')
					->select('categoryid as subcategoryid', 'category')
					->whereIn('categoryid', $categoryIds)
					->get();
			}
			else
			{
				$vendor = $query
					->leftJoin('category_tbl as b', DB::raw('FIND_IN_SET(b.categoryid, a.categoryids)'), '>', DB::raw('0'))
					->select(
						'a.vendorid','a.name','a.middlename','a.lastname','a.mobilenumber','a.alternetnumber','a.email','a.gender','a.dob','a.stateid','a.cityid','a.areaids','a.postalcode as pincode','a.completeaddress as address','a.aadhaarnumber','a.aadhaarfrontfile','a.aadhaarbackfile','a.pannumber','a.panfile','a.drivinglicence','a.licencefile','a.bankname','a.accountnumber as bankaccount','a.bankfile','a.ifsccode','a.bankbranch','a.profilepic','a.categoryids','a.verificationstatus','isselfemployeed','isprofilecompleted','a.isverified','a.isemployee','a.isapproved','a.isavailable',DB::raw('GROUP_CONCAT(DISTINCT b.parentcategoryid ORDER BY b.parentcategoryid) as categories')
					)
					->groupBy('a.vendorid')
					->first();

				if (!$vendor) {
					return response()->json(['message' => 'Employee not found', 'status' => 404], 404);
				}

				$fieldsToPrefix = [
					'profilepic', 'aadhaarfrontfile', 'aadhaarbackfile',
					'panfile', 'bankfile', 'licencefile'
				];

				foreach ($fieldsToPrefix as $field) {
					if (!empty($vendor->$field)) {
						$vendor->$field = url("storage/{$vendor->$field}");
					}
				}

				$vendor->isprofilesubmitted = $vendor->isprofilecompleted;
			}

			$authenticate = [
				'vendorid' => $vendor->vendorid,
				'accesstoken' => doubleval($accesstoken),
				'isverified' => $vendor->isverified,
				'verificationstatus' => $vendor->verificationstatus,
				'isprofilesubmitted' => $vendor->isprofilesubmitted,
				'isemployee' => $isexist->isemployee,
				'isapproved' => $vendor->isapproved,
				'isavailable' => $vendor->isavailable,
			];

			return response()->json([
				'message' => $isexist->isemployee ? 'EMPLOYEE DETAIL' : 'VENDOR DETAIL',
				'status' => 200,
				'authenticate' => $authenticate,
				'data' => $vendor,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'Something went wrong while processing your request. Please try again later.',
				'status' => 500,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken
			], 500);
		}		

	}

    public function updateVendorProfile(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
			'profilepic'		=> 'nullable|file|max:1024',
			'vendorid'			=> 'required',
			'accesstoken'		=> 'required',
            'name'  			=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'		=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'lastname'			=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'email'  			=> 'nullable|email',
			'gender' 			=> 'required',
			'stateid'			=> 'required',
			'cityid'			=> 'required',
			'pincode'			=> 'required|numeric',
			'address'			=> 'required',
			'aadhaarnumber'		=> 'required|numeric|digits:12',
			'aadhaarfrontfile'	=> 'nullable|file|max:1024',
			'aadhaarbackfile'	=> 'nullable|file|max:1024',
			'pannumber'			=> 'required',
			'panfile'			=> 'nullable|max:1024',
			'drivinglicence'	=> 'required',
			'licencefile'		=> 'nullable|max:1024',			
			'bankaccount'		=> 'nullable|numeric',
			'bankfile'			=> 'nullable|max:1024',			
			'subcategoryids'		=> 'required',
			'isselfemployeed'	=> 'required',
			'alternetnumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
			'latitude'			=> 'nullable|numeric',
			'longitude'			=> 'nullable|numeric',
        ];

        $messages = [
			'profilepic.required' 		=> 'PROFILE PICTURE REQUIRED',
			'profilepic.max' 			=> 'ONLY 1 MB SIZE IS ALLOWED',
			'profilepic.file' 			=> 'IT MUST BE A FILE',
			'vendorid.required' 		=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQUIRED',
			'name.required' 			=> 'NAME IS REQUIRED',
			'name.regex' 				=> 'INVALID NAME',
			'name.max' 					=> 'MAXIMUM LENGTH IS 50',
			'middlename.regex' 			=> 'INVALID MIDDLE NAME',
			'middlename.max' 			=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 			=> 'INVALID LAST NAME',
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
			'aadhaarnumber.numeric' 	=> 'INVALID AADHAAR NUMBER',
			'aadhaarnumber.digits' 		=> 'INVALID AADHAAR NUMBER',
			'pannumber.required'		=> 'PAN NUMBER IS REQUIRED',
			'aadhaarfrontfile.max'		=> 'AADHAAR FILE MAXIMUM SIZE IS 1 MB',
			'aadhaarfrontfile.file'		=> 'AADHAAR CARD MUST BE A FILE',
			'aadhaarbackfile.max'		=> 'AADHAAR FILE MAXIMUM SIZE IS 1 MB',
			'aadhaarbackfile.file'		=> 'AADHAAR CARD MUST BE A FILE',
			'panfile.max'				=> '1 MB IS ALLOWED',
			'drivinglicence.required'	=> 'DRIVING LICENCE IS REQUIRED',
			'licencefile.max'			=> 'LICENCE FILE MAXIMUM SIZE IS 1 MB',
			'bankfile.max'				=> 'BANK FILE MAXIMUM SIZE IS 1 MB',
			'subcategoryids.required'		=> 'CATEGORY NAME IS REQUIRED',
			'isselfemployeed.required'	=> 'IS SELF EMPLOYEED IS REQUIRED',
			'alternetnumber.regex'		=> 'INVALID ALTERNET NUMBER',
			'alternetnumber.digits'		=> 'INVALID ALTERNET NUMBER',
			'latitude.numeric'			=> 'INVALID LATITUDE VALUE',
			'longitude.numeric'			=> 'INVALID LONGITUDE VALUE',
        ];

	
        $validatedData 	= $request->validate($rules, $messages);

        $vendorid 		=	intval($request->header('vendorid'));
		$accesstoken 	=	$request->header('accesstoken');

		
		$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
		if(!$isexist || $isexist->isemployee != 0)
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
			], 401);
		}		
		$vendor = DB::table('vendor_tbl')->where('vendorid', $vendorid)->first();
		if (!$vendor)
		{
			return response()->json([
				'message' => 'VENDOR NOT FOUND',
				'status' => 404
			], 404);
		}
		DB::insert('insert into testing_tbl(particular,categoryids) values(?,?)',['CATEGORY IDS',$request->input('categoryids')]);
		DB::insert('insert into testing_tbl(particular,categoryids) values(?,?)',['SUB CATEGORY IDS',$request->input('subcategoryids')]);
		$fields = [
			'name', 'middlename', 'lastname', 'mobilenumber', 'alternetnumber', 'email', 'gender', 'dob',
			'pincode' => 'postalcode', 'address' => 'completeaddress', 'aadhaarnumber', 'pannumber',
			'drivinglicence', 'firmname', 'registrationnumber', 'firmcontact', 'firmemail',
			'firmpostalcode', 'firmaddress', 'firmpannumber', 'gstnumber', 'bankname',
			'bankaccount' => 'accountnumber', 'ifsccode', 'bankbranch',
			'areaids', 'latitude', 'longitude'
		];

		$updateData = [];
		foreach ($fields as $field) {
			if (!is_null($request->input($field))) {
				$updateData[$field] = $request->input($field);
			}
		}
		$updateData['stateid'] = $request->input('stateid');
		$updateData['categoryids'] = $request->input('subcategoryids');
		$updateData['cityid'] = $request->input('cityid');
		$updateData['firmstateid'] = $request->input('firmstateid');
		$updateData['firmcityid'] = $request->input('firmcityid');
		$updateData['isselfemployeed'] = $request->input('isselfemployeed');
		$updateData['isprofilecompleted'] = 1;
		$fileFields = [
			'profilepic' => 'uploads/vendorprofiles',
			'aadhaarfrontfile' => 'uploads/vendoraadharfiles',
			'aadhaarbackfile' => 'uploads/vendoraadharbackfiles',
			'panfile' => 'uploads/vendorpanfiles',
			'bankfile' => 'uploads/bankfiles',
			'firmpanfile' => 'uploads/firmpanfiles',
			'gstfile' => 'uploads/firmgstfiles',
			'licencefile' => 'uploads/licencefiles',
		];


		try
		{
			foreach($fileFields as $field => $folder) 
			{
				if($request->hasFile($field))
				{
					if(!empty($vendor->$field))
					{
						Storage::disk('public')->delete($vendor->$field);
					}
					$updateData[$field]	=	$request->file($field)->store($folder,'public');
				}
			}

			DB::table('vendor_tbl')
				->where('vendorid', $vendorid)
				->where('isemployee', 0)
				->update($updateData);

			$updatedVendor = $this->CheckVendor($vendorid, $accesstoken);

			return response()->json([
				'message' => __('messages.updated'),
				'status' => 200,
				'authenticate' => [
					'vendorid' => $updatedVendor->vendorid,
					'accesstoken' => doubleval($accesstoken),
					'isverified' => $updatedVendor->isverified,
					'verificationstatus' => $updatedVendor->verificationstatus,
					'isprofilesubmitted' => $updatedVendor->isprofilecompleted,
					'isemployee' => $updatedVendor->isemployee,
					'isapproved' => $updatedVendor->isapproved,
					'isavailable' => $updatedVendor->isavailable,
				]
			], 200);

		}
		catch (QueryException $e)
		{
			// Delete uploaded files in case of rollback
			foreach ($fileFields as $field => $folder) {
				if (isset($updateData[$field])) {
					Storage::disk('public')->delete($updateData[$field]);
				}
			}

			return response()->json([
				'message' => 'DUPLICATE DATA OR QUERY ERROR',
				'status' => 400,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
			], 400);
		}
    }


	public function getMyEmployeeList(Request $request)
	{
		$request->merge([
			'accesstoken'	=>	$request->header('accesstoken'),
			'vendorid'		=>	$request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'  	=> 'required',
			'accesstoken'  	=> 'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'PLEASE PROVIDE VENDOR DETAIL',
			'accesstoken.required' 	=> 'PLEASE PROVIDE ACCESSTOKEN TOKEN',
        ];
		
        $validated = $request->validate($rules, $messages);

		$vendorid 	= (int) $validated['vendorid'];
		$accesstoken= $validated['accesstoken'];
		$page 		= (int) $request->input('page', 1);
		$pagelimit 	= (int) $request->input('pagelimit', 10);		

		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if (!$vendor) {
				return response()->json([
					'message' => __('messages.unauthorized'),
					'status' => 401,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			$authenticate = [
				'vendorid' => $vendor->vendorid,
				'accesstoken' => $accesstoken,
				'isverified' => $vendor->isverified,
				'verificationstatus' => $vendor->verificationstatus,
				'isprofilesubmitted' => $vendor->isprofilecompleted,
				'isemployee' => $vendor->isemployee,
				'isapproved' => $vendor->isapproved,
				'isavailable' => $vendor->isavailable,
			];

			// If the vendor is an employee, they should not access employee list
			if ($vendor->isemployee == 1)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'status' => 401,
					'authenticate' => $authenticate,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			$employees = DB::table('vendor_tbl as a')
				->select(
					'a.vendorid as employeeid',
					'a.name',
					'a.middlename',
					'a.lastname',
					'a.mobilenumber',
					'a.alternetnumber',
					'a.email',
					'a.gender',
					'a.dob',
					'a.stateid',
					'a.cityid',
					'a.isactive',
					'a.postalcode as pincode',
					'a.completeaddress',
					'a.aadhaarnumber',
					'a.aadhaarfrontfile',
					'a.aadhaarbackfile',
					'a.pannumber',
					'a.panfile',
					'a.drivinglicence',
					'a.licencefile',
					'a.bankname',
					'a.accountnumber',
					'a.ifsccode',
					'a.profilepic',
					'a.employeecatids',
					'a.experience',
					'a.verificationstatus',
					'a.ratings',
					'a.reviews',
					'a.isavailable as isonline'
				)
				->where('a.parentvendorid', $vendorid)
				->where('a.isemployee', 1)
				->orderBy('a.vendorid', 'desc')
				->paginate($pagelimit, ['*'], 'page', $page);

			$urlPrefix = config('app.url') . '/storage/';

			foreach($employees as $employee)
			{
				$employee->isonline = (int) $employee->isonline;

				foreach(['aadhaarfrontfile', 'aadhaarbackfile', 'panfile', 'licencefile', 'profilepic'] as $fileField)
				{
					if(!empty($employee->$fileField))
					{
						$employee->$fileField = $urlPrefix . $employee->$fileField;
					}
				}

				// Convert category IDs into arrays
				$categoryIds = array_filter(explode(',', $employee->employeecatids));

				$parentCategories = DB::table('category')
					->whereIn('categoryid', $categoryIds)
					->pluck('parentcategoryid')
					->unique()
					->values();

				$employee->categories = $parentCategories;
				$employee->employeecatids = $categoryIds;
			}

			return response()->json([
				'message' => 'EMPLOYEE LIST PROVIDED',
				'status' => 200,
				'authenticate' => $authenticate,
				'totalpages' => $employees->lastPage(),
				'data' => $employees->items(),
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'Unable to fetch employee list at the moment. Please try again later.',
				'status' => 500
			], 500);
		}		
	}


    public function updateMyEmployee(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		

        $rules = [
			'vendorid'		=> 'required',
			'accesstoken'	=> 'required',
			'employeeid'	=> 'required',
            'name'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  	=> 'nullable|regex:/^[a-zA-Z\s]+$/|max:30',
			'lastname'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'gender' 		=> 'required',
			'dob'  				=> 'nullable|date',
			'categoryids'	=> 'required',
			'aadhaarnumber'	=> 'required|numeric|digits:12',
			'drivinglicence'=> 'nullable',
			'pannumber'		=> 'nullable',
			'stateid'		=> 'required',
			'cityid'		=> 'required',
			'pincode'		=> 'required|numeric',
			'alternatenumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'employeeid.required' 	=> 'EMPLOYEE ID IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'MAXIMUM LENGTH IS 50',
			'middlename.regex' 		=> 'INVALID MIDDLE NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30',
			'lastname.regex' 		=> 'INVALID LAST NAME',
			'lastname.max' 			=> 'MAXIMUM LENGTH IS 30',
			'mobilenumber.required' => 'MOBILE NUMBER IS REQUIRED',
			'mobilenumber.regex' 	=> 'INVALID MOBILE NUMBER',
			'mobilenumber.digits' 	=> 'INVALID MOBILE NUMBER',
			'gender.required' 		=> 'GENDER IS REQUIRED',
			'dob.date' 				=> 'MUST BE A DATE VALUE',
			'categoryids.required'	=> 'CATEGORY NAME IS REQUIRED',
			'aadhaarnumber.required'=> 'AADHAAR NUMBER IS REQUIRED',
			'aadhaarnumber.numeric'	=> 'AADHAAR SHOULD BE A NUMERIC VALUE ONLY',
			'aadhaarnumber.digits' 	=> 'INVALID AADHAAR NUMBER',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'alternatenumber.regex'	=> 'INVALID ALTERNET NUMBER',
			'alternatenumber.digits'	=> 'INVALID ALTERNET NUMBER',
        ];

        $validated 	= $request->validate($rules, $messages);

		$vendorid     	= (int) $validated['vendorid'];
		$employeeid   	= (int) $validated['employeeid'];
		$accesstoken  	= $validated['accesstoken'];
		$categoryids  	= $validated['categoryids'];
		
		$vendor			= $this->CheckVendor($vendorid, $accesstoken);
		if(!$vendor)
		{
			return response()->json(['message' => __('messages.unauthorized'), 'status' => 401], 401);
		}
		$employee = DB::table('vendor_tbl')->where('vendorid', $employeeid)->first();
		if(!$employee)
		{
			return response()->json(['message' => 'EMPLOYEE NOT FOUND', 'status' => 404], 404);
		}

		$authenticate = [
			'vendorid'           => $vendor->vendorid,
			'accesstoken'        => (float) $vendor->accesstoken,
			'isverified'         => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee'         => $vendor->isemployee,
			'isapproved'         => $vendor->isapproved,
			'isavailable'        => $vendor->isavailable,
		];
		if($employee->verificationstatus != 0)
		{
			return response()->json(['message' => 'PROFILE ALREADY VERIFIED. CANNOT UPDATE.', 'status' => 400, 'authenticate' => $authenticate], 400);
		}
		$inputCategories = explode(',', $categoryids);
		$vendorCategories = explode(',', $vendor->categoryids);
		if(array_diff($inputCategories, $vendorCategories))
		{
			return response()->json([
				'message' => 'CATEGORY IDS MISMATCH.',
				'status' => 400,
				'authenticate' => $authenticate,
			], 400);
		}
		$updateData = [
			'name'            => $validated['name'] ?? '',
			'middlename'      => $validated['middlename'] ?? '',
			'lastname'        => $validated['lastname'] ?? '',
			'mobilenumber'    => $validated['mobilenumber'] ?? '',
			'gender'          => $validated['gender'] ?? '',
			'dob'             => $validated['dob'] ?? '',
			'employeecatids'  => $categoryids ?? '',
			'experience'      => $request->input('experience', ''),
			'aadhaarnumber'   => $validated['aadhaarnumber'] ?? '',
			'drivinglicence'  => $request->input('drivinglicence', ''),
			'pannumber'       => $request->input('pannumber', ''),
			'bankname'        => $request->input('bankname', ''),
			'accountnumber'   => $request->input('bankaccount', ''),
			'ifsccode'        => $request->input('ifsccode', ''),
			'stateid'         => (int) $validated['stateid'],
			'cityid'          => (int) $validated['cityid'],
			'completeaddress' => $request->input('address', ''),
			'postalcode'      => $validated['pincode'],
			'alternetnumber'  => $request->input('alternatenumber', ''),
		];

		$fileFields = [
			'profilepic'       => 'uploads/employeeprofiles',
			'aadhaarfrontfile' => 'uploads/employeeaadharfiles',
			'aadhaarbackfile'  => 'uploads/employeeaadharbackfiles',
			'panfile'          => 'uploads/employeepanfiles',
			'bankfile'         => 'uploads/bankfiles',
			'licencefile'      => 'uploads/licencefiles',
		];

		$uploadedFiles = [];
		DB::beginTransaction();
		try
		{
			// Handle file uploads
			foreach($fileFields as $field => $path)
			{
				if($request->hasFile($field))
				{
					if(!empty($employee->$field))
					{
						Storage::disk('public')->delete($employee->$field);
					}
					$uploadedPath = $request->file($field)->store($path, 'public');
					$updateData[$field] = $uploadedPath;
					$uploadedFiles[] = $uploadedPath;
				}
			}

			DB::table('vendor_tbl')->where('vendorid', $employeeid)->update($updateData);

			DB::commit();

			return response()->json([
				'message' => __('messages.updated'),
				'status' => 200,
				'authenticate' => $authenticate
			], 200);
		}
		catch (\Exception $e)
		{
			DB::rollBack();

			// Rollback uploaded files
			foreach ($uploadedFiles as $file) {
				Storage::disk('public')->delete($file);
			}

			return response()->json([
				'message' => $e->getMessage(),
				'status' => 400,
				'authenticate' => $authenticate
			], 400);
		}
    }


    public function registerEmployee(Request $request)
	{
		$request->merge([
			'accesstoken'	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'			=> 'required',
			'accesstoken'		=> 'required',
			'profilepic'		=> 'nullable|max:1024',
            'name'  			=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:50',
			'middlename'  		=> 'nullable|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'lastname'  		=> 'required|regex:/^[a-zA-Z\s\.]+$/|max:30',
			'mobilenumber'  	=> 'required|regex:/^[1-9]\d{9}$/|digits:10',
			'gender' 			=> 'required',
			'dob'  				=> 'nullable|date',
			'categoryids'		=> 'required',
			'aadhaarnumber'		=> 'required|numeric|digits:12',
			'aadhaarfrontfile'	=> 'required|max:1024',
			'aadhaarbackfile'	=> 'required|max:1024',
			'pannumber'			=> 'nullable',
			'panfile'			=> 'nullable|max:1024',
			'drivinglicence'	=> 'nullable',
			'licencefile'		=> 'nullable|max:1024',
			'stateid'			=> 'required',
			'cityid'			=> 'required',
			'pincode'			=> 'required|numeric',
			'address'			=> 'required',
			'alternatenumber'	=> 'nullable|regex:/^[1-9]\d{9}$/|digits:10',
        ];

        $messages = [
            'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS REQUIRED',
			'profilepic.max' 		=> 'PROFILE PICTURE IS REQUIRED',
			'name.required' 		=> 'NAME IS REQUIRED',
			'name.regex' 			=> 'INVALID NAME',
			'name.max' 				=> 'MAXIMUM LENGTH IS 50',
			'middlename.regex' 		=> 'INVALID MIDDLE NAME',
			'middlename.max' 		=> 'MAXIMUM LENGTH IS 30',
			'lastname.required' 	=> 'LAST NAME IS REQUIRED',
			'lastname.regex' 		=> 'INVALID LAST NAME',
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
			'aadhaarfrontfile.required' 	=> 'AADHAAR FILE IS REQUIRED',
			'aadhaarfrontfile.max' 	=> 'MAXIMUM 1 MB IS ALLOWED',
			'aadhaarbackfile.required' 	=> 'AADHAAR FILE IS REQUIRED',
			'aadhaarbackfile.max' 	=> 'MAXIMUM 1 MB IS ALLOWED',
			'drivinglicence.required'=> 'AADHAAR NUMBER IS REQUIRED',
			'licencefile.max'		=> 'MAXIMUM 1 MB IS ALLOWED',
			'pannumber.required'	=> 'PAN NUMBER IS REQUIRED',
			'panfile.max'			=> 'MAXIMUM 1 MB IS ALLOWED',
			'bankname.required'		=> 'BANK NAME IS REQUIRED',
			'bankaccount.required'	=> 'BANK ACCOUNT IS REQUIRED',
			'bankaccount.numeric'	=> 'INVALID BANK ACCOUNT',
			'ifsccode.required'		=> 'IFSC IS REQUIRED',
			'stateid.required'		=> 'STATE NAME IS REQUIRED',
			'cityid.required'		=> 'CITY NAME IS REQUIRED',
			'pincode.required'		=> 'PIN CODE IS REQUIRED',
			'pincode.numeric'		=> 'INVALID PIN NUMBER',
			'address.required'		=> 'ADDRESS IS REQUIRED',
			'alternatenumber.regex'	=> 'INVALID ALTERNET NUMBER',
			'alternatenumber.digits'=> 'INVALID ALTERNET NUMBER',
        ];

        $validatedData 	= $request->validate($rules, $messages);

		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');

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
		$authenticate = [
			'vendorid' => $vendor->vendorid,
			'accesstoken' => $accesstoken,
			'isverified' => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee' => $vendor->isemployee,
			'isapproved' => $vendor->isapproved,
			'isavailable' => $vendor->isavailable,
		];

		$categoryIds = explode(',', $validatedData['categoryids']);
		$vendorCategories = explode(',', $vendor->categoryids ?? '');
		if(array_diff($categoryIds, $vendorCategories))
		{
			return response()->json([
				'message' => 'CATEGORY IDS MISMATCH FOR VENDOR ID. PLEASE CHECK AND TRY AGAIN.',
				'status' => 400,
				'authenticate' => $authenticate
			], 400);
		}
		$exists = DB::table('vendor_tbl')
					->where(function ($query) use ($validatedData) {
						$query->orWhere('aadhaarnumber', $validatedData['aadhaarnumber'])
							  ->orWhere('mobilenumber', $validatedData['mobilenumber']);

						if (!empty($validatedData['pannumber'])) {
							$query->orWhere('pannumber', $validatedData['pannumber']);
						}

						if (!empty($validatedData['drivinglicence'])) {
							$query->orWhere('drivinglicence', $validatedData['drivinglicence']);
						}
					})->exists();
		
		if($exists)
		{
			return response()->json([
				'message' => 'A DUPLICATE MOBILE NUMBER, AADHAAR NUMBER, PAN NUMBER, OR DRIVING LICENCE NUMBER WAS FOUND.',
				'status' => 409,
				'authenticate' => $authenticate
			], 409);
		}

		DB::beginTransaction();
		try
		{
			$fileFields = ['profilepic', 'aadhaarfrontfile', 'aadhaarbackfile', 'panfile', 'bankfile', 'licencefile'];
			$uploadedFiles = [];

			foreach ($fileFields as $field) {
				if ($request->hasFile($field)) {
					$uploadedFiles[$field] = $request->file($field)->store("uploads/$field", 'public');
				} else {
					$uploadedFiles[$field] = '';
				}
			}

			$employeeData = [
				$vendorid,
				$validatedData['name'],
				$validatedData['middlename'] ?? '',
				$validatedData['lastname'],
				$validatedData['mobilenumber'],
				$validatedData['alternatenumber'] ?? '',
				$validatedData['gender'],
				$validatedData['dob'],
				$validatedData['stateid'],
				$validatedData['cityid'],
				$request->input('areaid') ?? '',
				$validatedData['pincode'] ?? '',
				$validatedData['address'] ?? '',
				$validatedData['aadhaarnumber'] ?? '',
				$uploadedFiles['aadhaarfrontfile'],
				$uploadedFiles['aadhaarbackfile'],
				$validatedData['pannumber'] ?? '',
				$uploadedFiles['panfile'],
				$validatedData['drivinglicence'] ?? '',
				$uploadedFiles['licencefile'],
				$request->input('bankname') ?? '',
				$request->input('bankaccount') ?? '',
				$request->input('ifsccode') ?? '',
				1,
				$vendorid,
				'VENDOR',
				now()->format('Y-m-d H:i:s'),
				$request->input('experience'),
				$validatedData['categoryids'],
				rand(10000000, 99999999),
				rand(10000000, 99999999),
				1,
				1,
				'', '', '', '', '', '', '', '', '', '',
				$uploadedFiles['profilepic']
			];

			DB::insert('INSERT INTO vendor_tbl (parentvendorid, name, middlename, lastname, mobilenumber, alternetnumber, gender, dob, stateid, cityid, areaids, postalcode, completeaddress, aadhaarnumber, aadhaarfrontfile, aadhaarbackfile, pannumber, panfile, drivinglicence, licencefile, bankname, accountnumber, ifsccode, isactive, createdby, usertype, creationdate, experience, employeecatids, accesstoken, loginpassword, isemployee, isverified, ip_country, ip_countrycode, ip_region, ip_regionname, ip_city, ip_zip, ip_lat, ip_lon, ip_timezone, ip_isp, profilepic)
				VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)', $employeeData);

			DB::commit();
			return response()->json(['message' => __('messages.stored'), 'status' => 200, 'authenticate' => $authenticate], 200);

		}
		catch (\Exception $e)
		{
			DB::rollBack();

			// Delete uploaded files
			foreach ($uploadedFiles ?? [] as $file) {
				if (!empty($file)) {
					Storage::disk('public')->delete($file);
				}
			}

			//return response()->json(['message' => 'Something went wrong while processing your request. Please try again shortly or contact support if the issue persists.', 'status' => 500], 500);
			return response()->json(['message' => $e->getMessage(), 'status' => 500], 500);
		}
    }

    public function addOrderPrice(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'detailid'		=>	'required',
			'particular'	=>	'required',
			'amount'		=>	'required|numeric',
			'workpicture'	=> 	'nullable|max:1024',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'ORDER DETAIL IS REQUIRED',
			'particular.required' 	=> 'PARTICULAR IS REQUIRED',
			'amount.required' 		=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 		=> 'INVALID AMOUNT VALUE',
			'workpicture.max' 		=> 'MAX 1 MB IS ALLOWED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'=>$isexist->vendorid,
				'accesstoken'=>$accesstoken,
				'isverified'=>$isexist->isverified,
				'verificationstatus'=>$isexist->verificationstatus,
				'isprofilesubmitted'=>$isexist->isprofilecompleted,
				'isemployee'=>$isexist->isemployee,
				'isapproved'=>$isexist->isapproved,
				'isavailable'=>$isexist->isavailable,
			];
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
			
			$detailid   	= 	intval($request->input('detailid'));
			$particular    	= 	(String) $request->input('particular');
			$amount    		= 	doubleval($request->input('amount'));
			$workpicture    = 	(String) $request->file('workpicture');
		
			try
			{
				if($workpicture!='')
				{
					$workpicture= $request->file('workpicture')->store('uploads/workpictures', 'public');
				}
		
				DB::insert('insert into customer_order_price(detailid,particular,amount,taxvalue,vendorid,creationdate,workpicture) values(?,?,?,?,?,?,?)',[$detailid,$particular,$amount,0,$vendorid,$creationdate,$workpicture]);
					
				return response()->json(['message' =>'ADD ON PRICE UPDATED SUCCESSFULLY!','status'=>200,'authenticate'=>$authenticate], 200);
			}
			catch(QueryException $e)
			{
				return response()->json(['message'=>$e->getMessage(),'status'=>400], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>201,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 201);
		}
		
    }

    public function startWork(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'detailid'		=>	'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'ORDER DETAIL IS REQUIRED',
        ];
		
        $validated 	=	$request->validate($rules, $messages);
		
		$vendorid    = (int) $validated['vendorid'];
		$accesstoken = (string) $validated['accesstoken'];
		$detailid    = (int) $validated['detailid'];		

		$vendor = $this->CheckVendor($vendorid, $accesstoken);
		if(!$vendor)
		{
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken
			], 401);
		}
		$authenticate = [
			'vendorid'           => $vendor->vendorid,
			'accesstoken'        => (float) $vendor->accesstoken,
			'isverified'         => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee'         => $vendor->isemployee,
			'isapproved'         => $vendor->isapproved,
			'isavailable'        => $vendor->isavailable,
		];

		try
		{
			DB::beginTransaction();

			DB::table('customer_order_detail')
				->where('detailid', $detailid)
				->update([
					'workstarttime' => now()->format('Y-m-d H:i:s'),
					'orderstatus'   => 2,
				]);

			DB::commit();

			return response()->json([
				'message'      => 'START TIME UPDATED SUCCESSFULLY!',
				'status'       => 200,
				'authenticate' => $authenticate,
			], 200);
		}
		catch (\Exception $e)
		{
			DB::rollBack();

			return response()->json([
				'message' => 'Something went wrong while starting the work. Please try again later.',
				'status'  => 400
			], 400);
		}
    }


    public function walletRecharge(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'detailid'		=>	'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'ORDER DETAIL IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'=>$isexist->vendorid,
				'accesstoken'=>doubleval($isexist->accesstoken),
				'isverified'=>$isexist->isverified,
				'verificationstatus'=>$isexist->verificationstatus,
				'isprofilesubmitted'=>$isexist->isprofilecompleted,
				'isemployee'=>$isexist->isemployee,
				'isapproved'=>$isexist->isapproved,
				'isavailable'=>$isexist->isavailable,
			];
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
			
			$detailid   	= 	intval($request->input('detailid'));
	
			try
			{
				DB::update('update customer_order_detail set workstarttime=?,orderstatus=? where detailid=?',[$creationdate,2,$detailid]);
				return response()->json(['message' =>'START TIME UPDATED SUCCESSFULLY!','status'=>200,'authenticate'=>$authenticate], 200);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }


    public function generateRecharge(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'amount'		=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'amount.required' 		=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 		=> 'AMOUNT MUST BE NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid 		= (int) $validatedData['vendorid'];
		$accesstoken 	= (string) $validatedData['accesstoken'];
		$amount 		= (float) $validatedData['amount'];

		$vendor = $this->CheckVendor($vendorid, $accesstoken);
		if(!$vendor)
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401
			], 401);
		}
		$authenticate = [
			'vendorid' => $vendor->vendorid,
			'accesstoken' => $vendor->accesstoken,
			'isverified' => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee' => $vendor->isemployee,
			'isapproved' => $vendor->isapproved,
			'isavailable' => $vendor->isavailable,
		];
		$currentDateTime 	= now();
		$creationDateTime 	= $currentDateTime->format('Y-m-d H:i:s');
		$paymentDate 		= $currentDateTime->format('Y-m-d');
		$receipt 			= "RCPT" . mt_rand(100, 999) . time();
		try
		{
			// Create Razorpay order before starting transaction because it's an external API call
			$order = $this->razorpay->order->create([
				'amount' => (int)($amount * 100), // paisa
				'currency' => 'INR',
				'receipt' => $receipt,
				'payment_capture' => 1,
			]);

			$exitcode = $order->id;

			DB::transaction(function () use ($vendorid, $order, $amount, $receipt, $creationDateTime, $paymentDate, $exitcode)
			{
				$threeMinutesAgo = now()->subMinutes(3);

				$recentPayment = DB::table('receipt_tbl')
					->select('razorpay_order_id', 'cramount', 'paymentstatus')
					->where('vendorid', $vendorid)
					->where('paymentdatetime', '>=', $threeMinutesAgo)
					->where('paymentstatus', 'pending')
					->first();

				if ($recentPayment)
				{
					// Update existing pending payment
					DB::table('receipt_tbl')
						->where('vendorid', $vendorid)
						->where('razorpay_order_id', $recentPayment->razorpay_order_id)
						->update([
							'razorpay_order_id' => $order->id,
							'receiptnumber' => $receipt,
							'cramount' => $amount,
							'paymentstatus' => 'pending',
							'paymentdatetime' => $creationDateTime,
							'exitcode' => $order->id,
						]);
				}
				else
				{
					// Insert new receipt
					$financialYear = $this->resourceController->GetFinancialYear($paymentDate);

					DB::table('receipt_tbl')->insert([
						'financialyear' => $financialYear,
						'receiptnumber' => $receipt,
						'vendorid' => $vendorid,
						'razorpay_order_id' => $order->id,
						'cramount' => $amount,
						'paymentstatus' => 'pending',
						'paymentdatetime' => $creationDateTime,
						'generateddate' => $creationDateTime,
						'exitcode' => $order->id,
					]);
				}
			});

			return response()->json([
				'message' 		=> 'ORDER GENERATED SUCCESSFULLY',
				'status' 		=> 200,
				'authenticate' 	=> $authenticate,
				'orderid' 		=> $order->id,
				'amount' 		=> $amount,
				'exitcode' 		=> $exitcode,
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'Something went wrong while processing your request. Please try again later.',
				'status' => 500
			], 500);
		}
    }

    public function verifyRecharge(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'razorpay_signature'	=>	'required',
			'amount'				=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 			=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 			=> 'ACCESSTOKEN IS REQUIRED',
			'razorpay_order_id.required'	=> 'RAZORPAY ORDER ID IS REQUIRED',
			'razorpay_payment_id.required'	=> 'RAZORPAY PAYMENT ID IS REQUIRED',
			'razorpay_signature.required'	=> 'RAZORPAY SIGNATURE',
			'amount.required' 				=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 				=> 'AMOUNT MUST BE A NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);

		$vendorid 				= (int) $validatedData['vendorid'];
		$accesstoken 			= (string) $validatedData['accesstoken'];
		$razorpay_order_id 		= (string) $validatedData['razorpay_order_id'];
		$razorpay_payment_id 	= (string) $validatedData['razorpay_payment_id'];
		$razorpay_signature 	= (string) $validatedData['razorpay_signature'];
		$amount 				= (float) $validatedData['amount'];
		$vendor = $this->CheckVendor($vendorid, $accesstoken);
		if(!$vendor)
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401
			], 401);
		}
		$authenticate = [
			'vendorid' => $vendor->vendorid,
			'accesstoken' => $vendor->accesstoken,
			'isverified' => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee' => $vendor->isemployee,
			'isapproved' => $vendor->isapproved,
			'isavailable' => $vendor->isavailable,
		];
		try {
			$order = DB::table('receipt_tbl')
				->where('razorpay_order_id', $razorpay_order_id)
				->where('paymentstatus', 'pending')
				->where('vendorid', $vendorid)
				->first();

			if (!$order) {
				return response()->json([
					'message' => 'Transaction detail not found or already processed.',
					'status' => 404,
					'authenticate' => $authenticate
				], 404);
			}

			// Verify signature securely
			$generatedSignature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, env('RAZORPAY_SECRET'));

			if(!hash_equals($generatedSignature, $razorpay_signature))
			{
				return response()->json([
					'message' => 'Invalid payment signature.',
					'status' => 400,
					'authenticate' => $authenticate
				], 400);
			}

			DB::transaction(function () use ($razorpay_order_id, $razorpay_payment_id, $razorpay_signature, $vendorid, $amount, $authenticate,$vendor) {
				// Update payment status
				$updated = DB::table('receipt_tbl')
					->where('razorpay_order_id', $razorpay_order_id)
					->update([
						'paymentstatus' => 'paid',
						'razorpay_payment_id' => $razorpay_payment_id,
						'razorpay_signature' => $razorpay_signature,
					]);

				if (!$updated) {
					throw new \Exception('Failed to update payment status.');
				}

				// Increment wallet
				DB::table('vendor_tbl')->where('vendorid', $vendorid)->increment('walletamount', $amount);

				// Generate and save PDF receipt
				$receipt = DB::table('receipt_tbl')->where('razorpay_order_id', $razorpay_order_id)->first();

				if($receipt)
				{
					$pdf = PDF::loadView('pdf.rechargereceipt',['vendor' => $vendor, 'receipt' => $receipt]);
					$pdf->setPaper('A4', 'portrait');
					$filename = $receipt->receiptnumber . '.pdf';
					$pdf->save(storage_path('app/public/receipts/' . $filename));

					DB::table('receipt_tbl')
						->where('razorpay_order_id', $razorpay_order_id)
						->update(['receiptfile' => $filename]);
				}
			});

			return response()->json([
				'message' => 'Payment verified successfully!',
				'status' => 200,
				'authenticate' => $authenticate,
			], 200);

		}
		catch (\Exception $e) 
		{
			return response()->json([
				'message' => $e->getMessage(),
				'message1' => 'An error occurred while verifying your payment. Please try again later.',
				'status' => 500,
				'authenticate' => $authenticate,
			], 500);
		}
    }

    public function failedRecharge(Request $request)
	{
		$vendorid = $request->header('vendorid');
		$accesstoken = $request->header('accesstoken');
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'amount'				=>	'required|numeric',
			'errorcode'				=>	'required|max:100',
			'errordescription'		=>	'required|max:300',
        ];

        $messages = [
			'vendorid.required' 			=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 			=> 'ACCESSTOKEN IS REQUIRED',
			'razorpay_payment_id.required'	=> 'PAYMENT ID IS REQUIRED',
			'razorpay_order_id.required'	=> 'RAZORPAY ORDER ID IS REQUIRED',
			'amount.required' 				=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 				=> 'AMOUNT MUST BE A NUMERIC VALUE',
			'errorcode.required' 			=> 'ERROR CODE IS REQUIRED',
			'errorcode.max' 				=> 'MAX 300 CHARACTER ALLOWED',
			'errordescription.required' 	=> 'ERROR DESCRIPTION IS REQUIRED',
			'errordescription.max' 			=> 'MAX 1000 CHARACTER ALLOWED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendor = $this->CheckVendor($vendorid, $accesstoken);
		if(!$vendor)
		{
			return response()->json([
				'message' => __('messages.unauthorized'),
				'status' => 401,
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
			], 401);
		}
		$authenticate = [
			'vendorid' => $vendor->vendorid,
			'accesstoken' => $vendor->accesstoken,
			'isverified' => $vendor->isverified,
			'verificationstatus' => $vendor->verificationstatus,
			'isprofilesubmitted' => $vendor->isprofilecompleted,
			'isemployee' => $vendor->isemployee,
			'isapproved' => $vendor->isapproved,
			'isavailable' => $vendor->isavailable,
		];
		try
		{
			$razorpay_order_id 		= $validated['razorpay_order_id'];
			$razorpay_payment_id 	= $validated['razorpay_payment_id'];
			$amount 				= (float) $validated['amount'];
			$errorcode 				= $validated['errorcode'];
			$errordescription 		= $validated['errordescription'];

			$order = DB::table('receipt_tbl')
				->where('razorpay_order_id', $razorpay_order_id)
				->where('paymentstatus', 'pending')
				->where('vendorid', $vendorid)
				->first();

			if(!$order)
			{
				return response()->json([
					'message' => 'Transaction detail not found or already updated.',
					'status' => 404,
					'authenticate' => $authenticate
				], 404);
			}

			// Optional: clear fake/test orders if ID = payment ID (you can remove this block if not needed)
			if ($razorpay_order_id === $razorpay_payment_id)
			{
				$isvalid = DB::table('receipt_tbl')
					->where('exitcode', $razorpay_order_id)
					->first();

				if($isvalid)
				{
					DB::table('receipt_tbl')->where('exitcode', $razorpay_order_id)->delete();

					return response()->json([
						'message' => 'Payment record removed successfully.',
						'status' => 200,
						'authenticate' => $authenticate
					], 200);
				}
			}

			DB::transaction(function () use ($razorpay_order_id,$razorpay_payment_id,$vendorid,$errorcode,$errordescription)
			{
				DB::table('receipt_tbl')
					->where('razorpay_order_id', $razorpay_order_id)
					->where('vendorid', $vendorid)
					->update([
						'paymentstatus' 		=> 'failed',
						'razorpay_payment_id' 	=> $razorpay_payment_id,
						'errorcode' 			=> $errorcode,
						'errordescription' 		=> $errordescription,
					]);
			});

			return response()->json([
				'message' => 'Payment failure details recorded successfully.',
				'status' => 200,
				'authenticate' => $authenticate
			], 200);
		}
		catch (\Exception $e)
		{

			return response()->json([
				'message' => 'An error occurred while processing the failed payment. Please try again later.',
				'status' => 500,
				'authenticate' => $authenticate
			], 500);
		}
    }


    public function rechargeList(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'pagelimit'		=>	'nullable|numeric',
			'page'			=>	'nullable|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'pagelimit.numeric' 	=> 'PAGE LIMIT SHOULD BE NUMERIC',
			'page.numeric' 			=> 'PAGE NUMBER SHOULD BE NUMERIC',
        ];
		
        $validated = $request->validate($rules, $messages);
		
		$vendorid    = intval($request->vendorid);
		$accesstoken = (string) $request->accesstoken;
		$isexist = $this->CheckVendor($vendorid, $accesstoken);
		if
		(!$isexist)
		{
			return response()->json([
				'message'    => __('messages.unauthorized'),
				'status'     => 401,
				'vendorid'   => $vendorid,
				'accesstoken'=> $accesstoken
			], 401);
		}
		$authenticate = [
			'vendorid'           => $isexist->vendorid,
			'accesstoken'        => doubleval($isexist->accesstoken),
			'isverified'         => $isexist->isverified,
			'verificationstatus' => $isexist->verificationstatus,
			'isprofilesubmitted' => $isexist->isprofilecompleted,
			'isemployee'         => $isexist->isemployee,
			'isapproved'         => $isexist->isapproved,
			'isavailable'        => $isexist->isavailable,
		];
		$page      = max(intval($request->input('page', 1)),1);
		$pagelimit = max(intval($request->input('pagelimit',10)),1);
		$offset    = ($page - 1) * $pagelimit;

		try
		{
			return DB::transaction(function () use ($vendorid, $page, $pagelimit, $offset, $authenticate)
			{
				
				$firstTxnDatetime = DB::table('receipt_tbl')
					->where('vendorid', $vendorid)
					->whereIn('paymentstatus', ['paid', 'failed'])
					->orderBy('paymentdatetime')
					->offset($offset)
					->limit(1)
					->value('paymentdatetime');

				
				$openingBalance = 0;
				if($firstTxnDatetime)
				{
					$openingBalance = DB::table('receipt_tbl')
						->where('vendorid', $vendorid)
						->where('isunpaidservice', 0)
						->whereIn('paymentstatus', ['paid', 'failed'])
						->where('paymentdatetime', '<', $firstTxnDatetime)
						->sum(DB::raw('cramount - dramount'));
				}

				
				$transactions = DB::table('receipt_tbl as a')
					->select(
						'a.receiptid',
						'a.financialyear',
						'a.receiptnumber',
						'a.razorpay_order_id',
						'a.razorpay_payment_id',
						'a.cramount',
						'a.dramount',
						DB::raw('CASE WHEN a.cramount != 0 THEN a.cramount ELSE dramount END as amount'),
						DB::raw('CASE WHEN a.cramount != 0 THEN "CREDIT" ELSE "DEBIT" END as transaction'),
						'a.paymentstatus',
						'a.paymentdatetime',
						'a.errorcode',
						'a.errordescription',
						'a.errorreason',
						DB::raw("COALESCE(c.servicetitle, '') as servicetitle"),
						DB::raw("COALESCE(b.servicecharge, 0) as servicecharge"),
						DB::raw("COALESCE(b.taxable, 0) as taxable")
					)
					->leftJoin('customer_order_detail as b', 'b.detailid', '=', 'a.detailid')
					->leftJoin('services as c', function ($join) {
						$join->on('c.serviceid', '=', 'b.serviceid')
							 ->on('c.optionid', '=', 'b.optionid');
					})
					->where('a.vendorid', $vendorid)
					->where('a.isunpaidservice', 0)
					->whereIn('a.paymentstatus', ['paid', 'failed'])
					->orderBy('a.paymentdatetime')
					->paginate($pagelimit, ['*'], 'page', $page);

				
				$runningBalance = $openingBalance;
				$transactionsWithBalance = [];

				foreach ($transactions->items() as $txn) {
					if ($txn->paymentstatus === 'paid') {
						$runningBalance += floatval($txn->cramount) - floatval($txn->dramount);
						$txn->balance = number_format($runningBalance, 2, '.', '');
						$transactionsWithBalance[] = $txn;
					}
				}

				
				usort($transactionsWithBalance, function ($a, $b) {
					return strtotime($b->paymentdatetime) <=> strtotime($a->paymentdatetime);
				});

				return response()->json([
					'message'        => 'WALLET TRANSACTION LIST',
					'status'         => 200,
					'totalpages'     => $transactions->lastPage(),
					'authenticate'   => $authenticate,
					'openingbalance' => number_format($openingBalance, 2, '.', ''),
					'data'           => $transactionsWithBalance,
				]);
			});
		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'status'  => 400
			], 400);
		}
    }

    public function walletBalance(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
        ];

        $messages = [
			'vendorid.required' 		=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 		=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'=>$isexist->vendorid,
				'accesstoken'=>doubleval($isexist->accesstoken),
				'isverified'=>$isexist->isverified,
				'verificationstatus'=>$isexist->verificationstatus,
				'isprofilesubmitted'=>$isexist->isprofilecompleted,
				'isemployee'=>$isexist->isemployee,
				'isapproved'=>$isexist->isapproved,
				'isavailable'=>$isexist->isavailable,
			];
			$currentDateTime	= 	now();
			$creationdate  		= 	$currentDateTime->format('Y-m-d H:i:s');
			

			try
			{
				
				$securitydeposit = DB::table('vendor_tbl')->where('vendorid','=',$vendorid)->value('securitydeposit');
				$walletbalance = DB::table('vendor_tbl')->where('vendorid','=',$vendorid)->value('walletamount');
				
				if($walletbalance<2000)
				{
					$low		=	1;
					$message	=	"YOUR WALLET BALANCE IS LOW. PLEASE ADD FUNDS TO RECEIVING ORDERS.";
				}
				else
				{
					$low		=	0;
					$message	=	"WALLET BALANCE";
				}
				
				return response()->json(['message' =>$message,'status'=>200,'walletbalance'=>$walletbalance+$securitydeposit,'islow'=>$low,'authenticate'=>$authenticate],200);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }

	public function setJobRequest(Request $request)
	{
		/*
		$currenttime 	=	Carbon::now();
		$currentdate 	=	$currenttime->toDateString();
		$starttime 		=	$currenttime->toTimeString();
		//$endtime 		=	$currenttime->addMinutes(240)->toTimeString();
		$endtime 		=	$currenttime->addMinutes(800)->toTimeString();
		
		$orders 		= 	DB::table('customer_order_detail as a')
								->select('a.*','b.category')
								->leftjoin('category_tbl as b','b.categoryid','=','a.categoryid')
								->where('a.orderstatus','=',0)
								->where('a.iscancelled','=',0)
								->where('a.addondetailid','=',0)
								->whereDate('a.servicedate','>=',$currentdate)
								->whereBetween('a.slottime',[$starttime, $endtime])
								->get();		
		
		$radiusfrom		=	0;
		$radiusto		=	30;
		foreach($orders as $order)
		{
			$address	=	DB::table('customer_address')->where('addressid','=',$order->addressid)->first();

			$latitude	=	$address->latitude;
			$longitude	=	$address->longitude;
			
			$inradius 	=	DB::table('vendor_tbl')
								->select('vendorid','parentvendorid','name','latitude','longitude','isemployee','verificationstatus','isselfemployeed',DB::raw("
								(6371*acos(cos(radians($latitude))*cos(radians(latitude))*cos(radians(longitude)-radians($longitude))+sin(radians($latitude))*sin(radians(latitude)))) AS distance",[$latitude, $longitude, $latitude]))
								->havingRaw('distance<=?',[$radiusto])
								->where(function($query) {
									$query->where('verificationstatus','=',1)
										  ->where('isemployee','=',1);
								})
								->orWhere(function($query) {
									$query->where('isselfemployeed','=',1);
								})
								->orWhere(function($query) {
									$query->where('isselfemployeed','=',0)
									       ->where('isemployee','=',0);
								})
								->get();
			
					
			$inradiusIds=	$inradius->pluck('vendorid')->toArray();

			$available	=	DB::table('vendor_tbl')
								->select('vendorid')
								->where(function($query) use($inradiusIds,$order) {
									$query->whereIn('parentvendorid',$inradiusIds)
											->where('verificationstatus','=',1)
											->where('isemployee','=',0)
											->where('isavailable','=',1)
											->whereRaw("FIND_IN_SET(?, categoryids)", [$order->categoryid]);
								})						
								->orwhere(function($query) use($inradiusIds,$order) {
									$query->whereIn('vendorid',$inradiusIds)
											->where('verificationstatus','=',1)
											->where('isemployee','=',0)
											->where('isavailable','=',1)
											->whereRaw("FIND_IN_SET(?, categoryids)", [$order->categoryid]);
								})						
								->get();
			
			$vendorids	=	$available->pluck('vendorid')->toArray();
			
			$vendors	=	DB::table('vendor_tbl')
								->select('vendorid','parentvendorid','name','latitude','longitude','isemployee','verificationstatus','isselfemployeed',DB::raw("
								(6371*acos(cos(radians($latitude))*cos(radians(latitude))*cos(radians(longitude)-radians($longitude))+sin(radians($latitude))*sin(radians(latitude)))) AS distance",[$latitude, $longitude, $latitude]))
								->havingRaw('distance<=?',[$radiusto])
								->whereIn('vendorid',$vendorids)
								->where('isavailable','=',1)
								->get();

			
			foreach($vendors as $vendor)
			{
				$exist	=	DB::table('customer_order_notification')
								->where('vendorid','=',$vendor->vendorid)
								->where('detailid','=',$order->detailid)
								->exists();
				if(!$exist)
				{
					$channelid		=	"channel_".$vendor->vendorid;
					$categoryname	=	
					DB::insert('insert into customer_order_notification(vendorid,channelid,detailid,orderid,categoryid,categoryname,customerid,servicedate,slottime,distance,creationdatetime,currenttime) values(?,?,?,?,?,?,?,?,?,?,?,?)',[$vendor->vendorid,$channelid,$order->detailid,$order->orderid,$order->categoryid,$order->category,$order->customerid,$order->servicedate,$order->slottime,$vendor->distance,date('Y\-m\-d H:i:s'),now()]);
				}
			}
			
			//DB::table('customer_order_detail')->where('detailid',$order->detailid)->increment('requestcycle',1);
		}
		if($orders->count()==0)
		{
			$vendors	=	[];
		}
	
		*/
		/*
		WORKING WITH LOG FILE
		
		pclose(popen("start /B \"\" \"D:\\wamp64\\bin\\php\\php8.0.30\\php.exe\" \"D:\\wamp64\\www\\screwdriver\\artisan\" queue:work >> D:\\wamp64\\www\\screwdriver\\queue_log.txt 2>&1", "r"));
		*/

		/*
		WORKING FINE IN OFFLINE
		pclose(popen("start /B \"\" \"D:\\wamp64\\bin\\php\\php8.0.30\\php.exe\" \"D:\\wamp64\\www\\screwdriver\\artisan\" queue:work >NUL 2>NUL", "r")); 
		*/
		
		//exec('/opt/alt/php82/usr/bin/php /home/u151751738/domains/thescrewdriver.in/public_html/screw/artisan queue:work --timeout=3600 --tries=5 >> /home/u151751738/domains/thescrewdriver.in/public_html/screw/queue_log.txt 2>&1 &');
		
		//exec('/opt/alt/php82/usr/bin/php /home/u151751738/domains/thescrewdriver.in/public_html/screw/artisan queue:work --timeout=3600 --tries=10 >> /home/u151751738/domains/thescrewdriver.in/public_html/screw/queue_log.txt 2>&1 &');

		//exec('/opt/alt/php82/usr/bin/php /home/u151751738/domains/thescrewdriver.in/public_html/screw/artisan queue:work database --queue=' . $queueName . ' --timeout=3600 --tries=10 >> /home/u151751738/domains/thescrewdriver.in/public_html/screw/queue_log_'.$categoryid.'.txt 2>&1 &');
		
		
		
		//exec('/opt/cpanel/ea-php82/root/usr/bin/php /home1/creatfrp/test.thescrewdriver.in/screw/artisan queue:work database --queue=' . $queueName . ' --timeout=3600 --tries=10 --stop-when-empty >> /home1/creatfrp/test.thescrewdriver.in/screw/queue_log_'.$categoryid.'.txt 2>&1 &');

		/*
		$categoryIds	=	DB::table('customer_order_notification')
								->distinct()
								->pluck('categoryid')
								->toArray();
		foreach($categoryIds as $categoryid)
		{
			$queueName = 'category_'.$categoryid;

			$exists	=	DB::table('jobs')->where('queue','=',$queueName)->exists();
			if(!$exists)
			{
				dispatch(new CheckSingleOrderStatusJob($categoryid))->onQueue($queueName);
				
		
				
				exec('/opt/cpanel/ea-php82/root/usr/bin/php /home1/creatfrp/test.thescrewdriver.in/screw/artisan queue:work database --queue=' . $queueName . ' --timeout=3600 --tries=10 >> /home1/creatfrp/test.thescrewdriver.in/screw/queue_log_'.$categoryid.'.txt 2>&1 &');
			
				sleep(2);			
			}
		}
		*/
		return response()->json(['message'=>'THIS METHOD IS NO LONGER AVAILABLE','status'=>400],400);
	}
	
	
	public function availableVendorJob(Request $request)
	{
		$request->merge([
			'accesstoken' 	=>	$request->header('accesstoken'),
			'vendorid' 		=>	$request->header('vendorid'),
		]);		

        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'page'			=>	'nullable|numeric',
			'pagelimit'		=>	'nullable|numeric',
			'orderstatus'	=>	'nullable|numeric'
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'page.numeric' 			=> 'PAGE NUMBER SHOULD BE NUMERIC',
			'pagelimit.numeric' 	=> 'PAGE LIMIT SHOULD BE NUMERIC',
			'orderstatus.numeric' 	=> 'ORDER STATUS SHOULD BE NUMERIC',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		$isemployee   	= 	intval($request->input('isemployee'));

		$orderstatus	= 	intval($request->input('orderstatus',0));
		
		$page  			= 	intval($request->input('page',1));
		$pagelimit  	= 	intval($request->input('pagelimit',10));
		$totalpages		=	0;
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		$appUrl 		=	Config::get('app.url');		
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'=>$isexist->vendorid,
				'accesstoken'=>doubleval($isexist->accesstoken),
				'isverified'=>$isexist->isverified,
				'verificationstatus'=>$isexist->verificationstatus,
				'isprofilesubmitted'=>$isexist->isprofilecompleted,
				'isemployee'=>$isexist->isemployee,
				'isapproved'=>$isexist->isapproved,
				'isavailable'=>$isexist->isavailable,
			];
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');
			switch($orderstatus)
			{
				case 0:
					$results	=	$this->getAvailableVendorJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();
					return response()->json(['message' => 'AVAILABLE JOBS LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				case 1:
					$results	=	$this->getVendorAssignedJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();					
					return response()->json(['message' => 'ASSIGNED JOBS LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				case 2:
					$results	=	$this->getStartedJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();					
					return response()->json(['message' => 'STARTED JOBS LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				case 3:
					$results	=	$this->getCompletedJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();					
					return response()->json(['message' => 'COMPLETED JOB LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				case 4:
					$results	=	$this->getOnHoldJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();
					return response()->json(['message' => 'ON HOLD JOB LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				case -1:
					$results	=	$this->getCancelledJob($vendorid,$page,$pagelimit,$isexist->isemployee);
					$totalpages	=	$results->lastPage();					
					return response()->json(['message' => 'CANCELLED JOB LIST','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
				default:
					return response()->json(array_merge(['message' => 'INVALID ORDER STATUS PROVIDED','status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
			}				
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
	}
	// CANCELLED JOB FOR VENDORS OR SELF EMPLOYEED
	public function getCancelledJob($vendorid,$page,$pagelimit,$isemployee)
	{
		DB::beginTransaction();
		try
		{
			$appUrl = Config::get('app.url');
			
			$vendors = DB::table('vendor_tbl')
				->where(function ($query) use ($vendorid) {
					$query->where('vendorid', $vendorid)
						  ->orWhere('parentvendorid', $vendorid);
				})
				->where('verificationstatus', 1)
				->pluck('vendorid')
				->toArray();

			
			$results = DB::table('customer_order_detail as a')
				->select(
					'a.customerid', 'a.categoryid', 'a.detailid', 'a.orderid', 'a.orderstatus',
					'a.paid', 'a.creationdate as bookingdatetime', 'a.servicedate','a.slottime','a.quantity',
					'c.name', 'c.middlename', 'c.lastname', 'c.mobilenumber', 'c.profilepic',
					'c.email as customeremail', 'c.ratings as customerratings', 'c.reviews as customerreviews',
					'd.servicetitle', 'd.startingimages', 'd.finishingimages',
					'a.assignedtime', 'a.paymentstatus',
					DB::raw("COALESCE(a.workstarttime, '') as workstarttime"),
					DB::raw("COALESCE('') as workendtime"),
					DB::raw("COALESCE(b.address, '') as address"),
					DB::raw("COALESCE(b.postalcode, '') as postalcode"),
					DB::raw("COALESCE(b.latitude, '') as latitude"),
					DB::raw("COALESCE(b.longitude, '') as longitude"),
					'a.vendorid as employeeid',
					'e.name as employeename', 'e.middlename as employeemiddlename',
					'e.lastname as employeelastname', 'e.mobilenumber as employeecontact',
					'e.profilepic as employeeprofile', 'e.email as employeeemail',
					'e.completeaddress as employeeaddress', 'e.latitude as employeelatitude',
					'e.longitude as employeelongitude',
					'a.startpictures', 'a.finishpictures'
				)
				->leftJoin('customer_address as b', 'b.addressid', '=', 'a.addressid')
				->leftJoin('customer_tbl as c', 'c.customerid', '=', 'a.customerid')
				->leftJoin('services as d', function ($join) {
					$join->on('d.serviceid', '=', 'a.serviceid')
						 ->on('d.optionid', '=', 'a.optionid');
				})
				->leftJoin('vendor_tbl as e', 'e.vendorid', '=', 'a.vendorid')
				->whereIn('a.vendorid', $vendors)
				->where('a.orderstatus', -1)
				->orderByDesc('a.servicedate')
				->paginate($pagelimit, ['*'], 'page', $page);

			foreach ($results as $result)
			{
				$result->ordid = $result->orderid . "#" . $result->detailid;
				$result->slottime = date('h:i A', strtotime($result->slottime));

				if (!empty($result->profilepic)) {
					$result->profilepic = $appUrl . "/storage/" . ltrim($result->profilepic, '/');
				}

				if (!empty($result->employeeprofile)) {
					$result->employeeprofile = $appUrl . "/storage/" . ltrim($result->employeeprofile, '/');
				}

				$result->employeename = implode(' ', array_filter([
					$result->employeename,
					$result->employeemiddlename,
					$result->employeelastname
				]));

				$result->withinrange = ""; // required by mobile response format
			}

			DB::commit();
			return $results;
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Unable to fetch cancelled jobs.'], 500);
		}		
		/*
		$appUrl 			=	Config::get('app.url');		
		$self				=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('vendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		
		$employee			=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('parentvendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		$vendors			=	$employee->concat($self)->pluck('employeeid')->toArray();

		$results 			=	DB::table('customer_order_detail as a')
									->select('a.customerid','a.categoryid','a.detailid','a.orderid','a.orderstatus','a.paid','a.creationdate as bookingdatetime','a.servicedate','a.slottime','c.name','c.middlename','c.lastname','c.mobilenumber','c.profilepic','c.email as customeremail','c.ratings as customerratings','c.reviews as customerreviews','d.servicetitle','d.startingimages','d.finishingimages','a.assignedtime','a.paymentstatus',DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(b.address,'') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude,'') as latitude"),DB::raw("COALESCE(b.longitude,'') as longitude"),'a.vendorid as employeeid','e.name as employeename','e.middlename as employeemiddlename','e.lastname as employeelastname','e.mobilenumber as employeecontact','e.profilepic as employeeprofile','e.email as employeeemail','e.completeaddress as employeeaddress','e.latitude as employeelatitude','e.longitude as employeelongitude','a.startpictures','a.finishpictures')
									->leftjoin('customer_address as b','b.addressid','=','a.addressid')
									->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
									->leftjoin('services as d','d.serviceid','=','a.serviceid')
									->leftjoin('vendor_tbl as e','e.vendorid','=','a.vendorid')
									->whereIn('a.vendorid',$vendors)
									->whereIn('a.orderstatus',[-1])
									->paginate($pagelimit,['*'],'page',$page);
		
		
		foreach($results as $result)
		{
			$result->ordid		=	$result->orderid."#".$result->detailid;						
			$result->slottime	=	date('h:i A',strtotime($result->slottime));
			if($result->profilepic!='')
			{
				$result->profilepic	=	$appUrl."/storage/".$result->profilepic;
			}
			if($result->employeeprofile!='')
			{
				$result->employeeprofile	=	$appUrl."/storage/".$result->employeeprofile;
			}
			$result->withinrange	=	"";
			$result->employeename	=	$result->employeename;
			if($result->employeemiddlename!='')
			$result->employeename	=	$result->employeename." ".$result->employeemiddlename;
			if($result->employeelastname!='')
			$result->employeename	=	$result->employeename." ".$result->employeelastname;
			
		}
		
		return $results;
		*/
	}
	
	// ON HOLD JOB FOR VENDORS OR SELF EMPLOYEED
	public function getOnHoldJob($vendorid,$page,$pagelimit,$isemployee)
	{
		$results = [];

		DB::beginTransaction();
		try
		{
			$appUrl = Config::get('app.url');

			
			$vendors = DB::table('vendor_tbl')
				->where(function ($query) use ($vendorid) {
					$query->where('vendorid', $vendorid)
						  ->orWhere('parentvendorid', $vendorid);
				})
				->where('verificationstatus', 1)
				->pluck('vendorid')
				->toArray();

			
			$results = DB::table('customer_order_detail as a')
				->select(
					'a.customerid', 'a.categoryid', 'a.detailid', 'a.orderid', 'a.orderstatus',
					'a.paid', 'a.creationdate as bookingdatetime', 'a.servicedate', 'a.slottime','a.quantity',
					'c.name', 'c.middlename', 'c.lastname', 'c.mobilenumber', 'c.profilepic',
					'c.email as customeremail', 'c.ratings as customerratings', 'c.reviews as customerreviews',
					'd.servicetitle', 'd.startingimages', 'd.finishingimages',
					'a.assignedtime', 'a.paymentstatus', 'a.onholdreason',
					DB::raw("COALESCE(a.workstarttime, '') as workstarttime"),
					DB::raw("COALESCE('') as workendtime"),
					DB::raw("COALESCE(b.address, '') as address"),
					DB::raw("COALESCE(b.postalcode, '') as postalcode"),
					DB::raw("COALESCE(b.latitude, '') as latitude"),
					DB::raw("COALESCE(b.longitude, '') as longitude"),
					'a.vendorid as employeeid',
					'e.name as employeename', 'e.middlename as employeemiddlename',
					'e.lastname as employeelastname', 'e.mobilenumber as employeecontact',
					'e.profilepic as employeeprofile', 'e.email as employeeemail',
					'e.completeaddress as employeeaddress', 'e.latitude as employeelatitude',
					'e.longitude as employeelongitude', 'a.startpictures', 'a.finishpictures'
				)
				->leftJoin('customer_address as b', 'b.addressid', '=', 'a.addressid')
				->leftJoin('customer_tbl as c', 'c.customerid', '=', 'a.customerid')
				->leftJoin('services as d', function ($join) {
					$join->on('d.serviceid', '=', 'a.serviceid')
						 ->on('d.optionid', '=', 'a.optionid');
				})
				->leftJoin('vendor_tbl as e', 'e.vendorid', '=', 'a.vendorid')
				->whereIn('a.vendorid', $vendors)
				->where('a.orderstatus', 4)
				->where('a.ispinverified', 1)
				->orderBy('a.servicedate', 'desc')
				->paginate($pagelimit, ['*'], 'page', $page);

			// Format results
			foreach ($results as $result)
			{
				$result->ordid = $result->orderid . "#" . $result->detailid;
				$result->slottime = date('h:i A', strtotime($result->slottime));

				if (!empty($result->profilepic))
				{
					$result->profilepic = $appUrl . "/storage/" . ltrim($result->profilepic, '/');
				}

				if (!empty($result->employeeprofile))
				{
					$result->employeeprofile = $appUrl . "/storage/" . ltrim($result->employeeprofile, '/');
				}

				$nameParts = array_filter([
					$result->employeename,
					$result->employeemiddlename,
					$result->employeelastname
				]);
				$result->employeename = implode(' ', $nameParts);

				$result->withinrange = ""; // required by mobile response format
			}

			DB::commit();
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Unable to fetch on-hold jobs.'], 500);
		}

		return $results;		
		/*
		$appUrl 			=	Config::get('app.url');		
		$self				=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('vendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		
		$employee			=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('parentvendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		$vendors			=	$employee->concat($self)->pluck('employeeid')->toArray();

		$results 			=	DB::table('customer_order_detail as a')
									->select('a.customerid','a.categoryid','a.detailid','a.orderid','a.orderstatus','a.paid','a.creationdate as bookingdatetime','a.servicedate','a.slottime','c.name','c.middlename','c.lastname','c.mobilenumber','c.profilepic','c.email as customeremail','c.ratings as customerratings','c.reviews as customerreviews','d.servicetitle','d.startingimages','d.finishingimages','a.assignedtime','a.paymentstatus','a.onholdreason',DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(b.address,'') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude,'') as latitude"),DB::raw("COALESCE(b.longitude,'') as longitude"),'a.vendorid as employeeid','e.name as employeename','e.middlename as employeemiddlename','e.lastname as employeelastname','e.mobilenumber as employeecontact','e.profilepic as employeeprofile','e.email as employeeemail','e.completeaddress as employeeaddress','e.latitude as employeelatitude','e.longitude as employeelongitude','a.startpictures','a.finishpictures')
									->leftjoin('customer_address as b','b.addressid','=','a.addressid')
									->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
									->leftJoin('services as d', function ($join) {
										$join->on('d.serviceid', '=', 'a.serviceid')
											->on('d.optionid', '=', 'a.optionid');
									})
									->leftjoin('vendor_tbl as e','e.vendorid','=','a.vendorid')
									->whereIn('a.vendorid',$vendors)
									->whereIn('a.orderstatus',[4])
									->where('ispinverified','=',1)
									->paginate($pagelimit,['*'],'page',$page);
		
		
		foreach($results as $result)
		{
			$result->ordid		=	$result->orderid."#".$result->detailid;						
			$result->slottime	=	date('h:i A',strtotime($result->slottime));
			if($result->profilepic!='')
			{
				$result->profilepic	=	$appUrl."/storage/".$result->profilepic;
			}
			if($result->employeeprofile!='')
			{
				$result->employeeprofile	=	$appUrl."/storage/".$result->employeeprofile;
			}
			$result->withinrange	=	"";
			$result->employeename	=	$result->employeename;
			if($result->employeemiddlename!='')
			$result->employeename	=	$result->employeename." ".$result->employeemiddlename;
			if($result->employeelastname!='')
			$result->employeename	=	$result->employeename." ".$result->employeelastname;
			
		}
		
		return $results;
		*/
	}
	
	// COMPLETED JOB FOR VENDORS OR SELF EMPLOYEED
	public function getCompletedJob($vendorid,$page,$pagelimit,$isemployee)
	{
		$results = [];

		DB::beginTransaction();
		try {
			$appUrl = Config::get('app.url');

			
			$vendors = DB::table('vendor_tbl')
				->where(function ($query) use ($vendorid) {
					$query->where('vendorid', $vendorid)
						  ->orWhere('parentvendorid', $vendorid);
				})
				->where('verificationstatus', 1)
				->pluck('vendorid')
				->toArray();

			
			$results = DB::table('customer_order_detail as a')
				->select(
					'a.customerid', 'a.categoryid', 'a.detailid', 'a.orderid', 'a.orderstatus',
					'a.paid', 'a.creationdate as bookingdatetime', 'a.servicedate', 'a.slottime','a.quantity', 'c.name', 'c.middlename', 'c.lastname',
					'c.mobilenumber', 'c.profilepic', 'c.email as customeremail',
					'c.ratings as customerratings', 'c.reviews as customerreviews',
					'd.servicetitle', 'd.startingimages', 'd.finishingimages',
					'a.assignedtime', 'a.paymentstatus',
					DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),
					DB::raw("COALESCE('') as workendtime"),
					DB::raw("COALESCE(b.address,'') as address"),
					DB::raw("COALESCE(b.postalcode,'') as postalcode"),
					DB::raw("COALESCE(b.latitude,'') as latitude"),
					DB::raw("COALESCE(b.longitude,'') as longitude"),
					'a.vendorid as employeeid',
					'e.name as employeename', 'e.middlename as employeemiddlename',
					'e.lastname as employeelastname', 'e.mobilenumber as employeecontact',
					'e.profilepic as employeeprofile', 'e.email as employeeemail',
					'e.completeaddress as employeeaddress', 'e.latitude as employeelatitude',
					'e.longitude as employeelongitude', 'a.startpictures', 'a.finishpictures'
				)
				->leftJoin('customer_address as b', 'b.addressid', '=', 'a.addressid')
				->leftJoin('customer_tbl as c', 'c.customerid', '=', 'a.customerid')
				->leftJoin('services as d', function ($join) {
					$join->on('d.serviceid', '=', 'a.serviceid')
						 ->on('d.optionid', '=', 'a.optionid');
				})
				->leftJoin('vendor_tbl as e', 'e.vendorid', '=', 'a.vendorid')
				->whereIn('a.vendorid', $vendors)
				->where('a.orderstatus', 3)
				->where('a.ispinverified', 2)
				->orderBy('a.servicedate', 'desc')
				->paginate($pagelimit, ['*'], 'page', $page);

			
			foreach ($results as $result)
			{
				$result->ordid = $result->orderid . "#" . $result->detailid;
				$result->slottime = date('h:i A', strtotime($result->slottime));

				if (!empty($result->profilepic))
				{
					$result->profilepic = $appUrl . "/storage/" . ltrim($result->profilepic, '/');
				}

				if (!empty($result->employeeprofile))
				{
					$result->employeeprofile = $appUrl . "/storage/" . ltrim($result->employeeprofile, '/');
				}

				$nameParts = array_filter([
					$result->employeename,
					$result->employeemiddlename,
					$result->employeelastname
				]);
				$result->employeename = implode(' ', $nameParts);

				$result->withinrange = ""; // maintained per app requirement
			}

			DB::commit();
		}
		catch(\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Unable to fetch completed jobs.'], 500);
		}

		return $results;		
		/*
		$appUrl 			=	Config::get('app.url');		
		$self				=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('vendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		
		$employee			=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('parentvendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		$vendors			=	$employee->concat($self)->pluck('employeeid')->toArray();

		$results 			=	DB::table('customer_order_detail as a')
									->select('a.customerid','a.categoryid','a.detailid','a.orderid','a.orderstatus','a.paid','a.slottime','c.name','c.middlename','c.lastname','c.mobilenumber','c.profilepic','c.email as customeremail','c.ratings as customerratings','c.reviews as customerreviews','d.servicetitle','d.startingimages','d.finishingimages','a.assignedtime','a.paymentstatus',DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(b.address,'') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude,'') as latitude"),DB::raw("COALESCE(b.longitude,'') as longitude"),'a.vendorid as employeeid','e.name as employeename','e.middlename as employeemiddlename','e.lastname as employeelastname','e.mobilenumber as employeecontact','e.profilepic as employeeprofile','e.email as employeeemail','e.completeaddress as employeeaddress','e.latitude as employeelatitude','e.longitude as employeelongitude','a.startpictures','a.finishpictures')
									->leftjoin('customer_address as b','b.addressid','=','a.addressid')
									->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
									->leftJoin('services as d', function ($join) {
										$join->on('d.serviceid', '=', 'a.serviceid')
											->on('d.optionid', '=', 'a.optionid');
									})									
									->leftjoin('vendor_tbl as e','e.vendorid','=','a.vendorid')
									->whereIn('a.vendorid',$vendors)
									->whereIn('a.orderstatus',[3])
									->where('ispinverified','=',2)
									->paginate($pagelimit,['*'],'page',$page);
		
		
		foreach($results as $result)
		{
			$result->ordid		=	$result->orderid."#".$result->detailid;						
			$result->slottime	=	date('h:i A',strtotime($result->slottime));
			if($result->profilepic!='')
			{
				$result->profilepic	=	$appUrl."/storage/".$result->profilepic;
			}
			if($result->employeeprofile!='')
			{
				$result->employeeprofile	=	$appUrl."/storage/".$result->employeeprofile;
			}
			$result->withinrange	=	"";
			$result->employeename	=	$result->employeename;
			if($result->employeemiddlename!='')
			$result->employeename	=	$result->employeename." ".$result->employeemiddlename;
			if($result->employeelastname!='')
			$result->employeename	=	$result->employeename." ".$result->employeelastname;
			
		}
		return $results;
		*/
	}
	
	// STARTED JOB FOR VENDORS OR SELF EMPLOYEED
	public function getStartedJob($vendorid,$page,$pagelimit,$isemployee)
	{
		$results = [];

		DB::beginTransaction();
		try
		{
			$appUrl = Config::get('app.url');

			$vendors = DB::table('vendor_tbl')
				->where(function ($query) use ($vendorid) {
					$query->where('vendorid', $vendorid)
						  ->orWhere('parentvendorid', $vendorid);
				})
				->where('verificationstatus', 1)
				->pluck('vendorid')
				->toArray();

			$results = DB::table('customer_order_detail as a')
				->select(
					'a.customerid', 'a.categoryid', 'a.detailid', 'a.orderid', 'a.orderstatus',
					'a.paid', 'a.balanceamount', 'a.creationdate as bookingdatetime',
					'a.servicedate', 'a.slottime','a.quantity', 'c.name', 'c.middlename', 'c.lastname',
					'c.mobilenumber', 'c.profilepic', 'c.email as customeremail',
					'c.ratings as customerratings', 'c.reviews as customerreviews',
					'd.servicetitle', 'd.startingimages', 'd.finishingimages', 'a.assignedtime',
					'a.paymentstatus', DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),
					DB::raw("COALESCE('') as workendtime"),
					DB::raw("COALESCE(b.address,'') as address"),
					DB::raw("COALESCE(b.postalcode,'') as postalcode"),
					DB::raw("COALESCE(b.latitude,'') as latitude"),
					DB::raw("COALESCE(b.longitude,'') as longitude"),
					'a.vendorid as employeeid',
					'e.name as employeename', 'e.middlename as employeemiddlename',
					'e.lastname as employeelastname', 'e.mobilenumber as employeecontact',
					'e.profilepic as employeeprofile', 'e.email as employeeemail',
					'e.completeaddress as employeeaddress', 'e.latitude as employeelatitude',
					'e.longitude as employeelongitude', 'a.startpictures', 'a.finishpictures'
				)
				->leftJoin('customer_address as b', 'b.addressid', '=', 'a.addressid')
				->leftJoin('customer_tbl as c', 'c.customerid', '=', 'a.customerid')
				->leftJoin('services as d', function ($join) {
					$join->on('d.serviceid', '=', 'a.serviceid')
						 ->on('d.optionid', '=', 'a.optionid');
				})
				->leftJoin('vendor_tbl as e', 'e.vendorid', '=', 'a.vendorid')
				->whereIn('a.vendorid', $vendors)
				->where('a.orderstatus', 2)
				->where('a.ispinverified', 1)
				->orderBy('a.servicedate', 'asc')
				->paginate($pagelimit, ['*'], 'page', $page);

			foreach ($results as $result)
			{
				$result->ordid = $result->orderid . "#" . $result->detailid;
				$result->slottime = date('h:i A', strtotime($result->slottime));

				// Image URLs
				if (!empty($result->profilepic)) {
					$result->profilepic = $appUrl . "/storage/" . ltrim($result->profilepic, '/');
				}

				if (!empty($result->employeeprofile)) {
					$result->employeeprofile = $appUrl . "/storage/" . ltrim($result->employeeprofile, '/');
				}

				// Full employee name
				$nameParts = array_filter([
					$result->employeename,
					$result->employeemiddlename,
					$result->employeelastname
				]);
				$result->employeename = implode(' ', $nameParts);

				$result->withinrange = ""; // kept as per your original logic

				// If unpaid, show balanceamount as paid
				if ($result->paid == 0) {
					$result->paid = $result->balanceamount;
				}

				unset($result->balanceamount);
			}

			DB::commit();
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Unable to fetch started jobs.'], 500);
		}

		return $results;		
		/*
		$appUrl 			=	Config::get('app.url');		
		$self				=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('vendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		
		$employee			=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('parentvendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		$vendors			=	$employee->concat($self)->pluck('employeeid')->toArray();
		
		$results 			=	DB::table('customer_order_detail as a')
									->select('a.customerid','a.categoryid','a.detailid','a.orderid','a.orderstatus','a.paid','a.balanceamount','a.creationdate as bookingdatetime','a.servicedate','a.slottime','c.name','c.middlename','c.lastname','c.mobilenumber','c.profilepic','c.email as customeremail','c.ratings as customerratings','c.reviews as customerreviews','d.servicetitle','d.startingimages','d.finishingimages','a.assignedtime','a.paymentstatus',DB::raw("COALESCE(a.workstarttime,'') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(b.address,'') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude,'') as latitude"),DB::raw("COALESCE(b.longitude,'') as longitude"),'a.vendorid as employeeid','e.name as employeename','e.middlename as employeemiddlename','e.lastname as employeelastname','e.mobilenumber as employeecontact','e.profilepic as employeeprofile','e.email as employeeemail','e.completeaddress as employeeaddress','e.latitude as employeelatitude','e.longitude as employeelongitude','a.startpictures','a.finishpictures')
									->leftjoin('customer_address as b','b.addressid','=','a.addressid')
									->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
									->leftJoin('services as d', function ($join) {
										$join->on('d.serviceid', '=', 'a.serviceid')
											->on('d.optionid', '=', 'a.optionid');
									})									
									->leftjoin('vendor_tbl as e','e.vendorid','=','a.vendorid')
									->whereIn('a.vendorid',$vendors)
									->whereIn('a.orderstatus',[2])
									->where('ispinverified','=',1)
									->paginate($pagelimit,['*'],'page',$page);
		
		
		foreach($results as $result)
		{
			$result->ordid		=	$result->orderid."#".$result->detailid;						
			$result->slottime	=	date('h:i A',strtotime($result->slottime));
			if($result->profilepic!='')
			{
				$result->profilepic	=	$appUrl."/storage/".$result->profilepic;
			}
			if($result->employeeprofile!='')
			{
				$result->employeeprofile	=	$appUrl."/storage/".$result->employeeprofile;
			}
			$result->withinrange	=	"";
			$result->employeename	=	$result->employeename;
			if($result->employeemiddlename!='')
			$result->employeename	=	$result->employeename." ".$result->employeemiddlename;
			if($result->employeelastname!='')
			$result->employeename	=	$result->employeename." ".$result->employeelastname;
			
			if($result->paid==0)
			{
				$result->paid	=	$result->balanceamount;
			}
			unset($result->balanceamount);
		}
		
		return $results;
		*/
	}
	
	// ASSIGNED JOB FOR VENDORS OR SELF EMPLOYEED
	public function getVendorAssignedJob($vendorid,$page,$pagelimit,$isemployee)
	{
		$results = [];
		DB::beginTransaction();
		try
		{
			$appUrl = Config::get('app.url');

			// Fetch employee + self vendor IDs
			$vendors = DB::table('vendor_tbl')
				->where(function ($query) use ($vendorid) {
					$query->where('vendorid', $vendorid)
						  ->orWhere('parentvendorid', $vendorid);
				})
				->where('verificationstatus', 1)
				->pluck('vendorid')
				->toArray();

			// Query assigned jobs
			$results = DB::table('customer_order_detail as a')
				->select(
					'a.customerid', 'a.categoryid', 'a.detailid', 'a.orderid', 'a.orderstatus',
					'a.paid', 'a.creationdate as bookingdatetime', 'a.servicedate', 'a.slottime','a.quantity',
					'c.name', 'c.middlename', 'c.lastname', 'c.mobilenumber', 'c.profilepic',
					'c.email as customeremail', 'c.ratings as customerratings', 'c.reviews as customerreviews',
					'd.servicetitle', 'a.vendorid as employeeid',
					'e.name as employeename', 'e.middlename as employeemiddlename', 'e.lastname as employeelastname',
					'a.assignedtime', 'a.paymentstatus',
					'e.mobilenumber as employeecontact', 'e.profilepic as employeeprofile',
					'e.email as employeeemail', 'e.completeaddress as employeeaddress',
					'e.latitude as employeelatitude', 'e.longitude as employeelongitude',
					DB::raw("COALESCE('') as workstarttime"),
					DB::raw("COALESCE('') as workendtime"),
					DB::raw("COALESCE(b.address,'') as address"),
					DB::raw("COALESCE(b.postalcode,'') as postalcode"),
					DB::raw("COALESCE(b.latitude,'') as latitude"),
					DB::raw("COALESCE(b.longitude,'') as longitude")
				)
				->leftJoin('customer_address as b', 'b.addressid', '=', 'a.addressid')
				->leftJoin('customer_tbl as c', 'c.customerid', '=', 'a.customerid')
				->leftJoin('services as d', function ($join) {
					$join->on('d.serviceid', '=', 'a.serviceid')
						 ->on('d.optionid', '=', 'a.optionid');
				})
				->leftJoin('vendor_tbl as e', 'e.vendorid', '=', 'a.vendorid')
				->whereIn('a.vendorid', $vendors)
				->where('a.orderstatus', 1)
				->orderBy('a.servicedate', 'asc')
				->paginate($pagelimit, ['*'], 'page', $page);

			foreach ($results as $result)
			{
				$result->ordid = $result->orderid . "#" . $result->detailid;
				$result->slottime = date('h:i A', strtotime($result->slottime));

				// Set full image URL for customer and employee
				if (!empty($result->profilepic)) {
					$result->profilepic = $appUrl . "/storage/" . ltrim($result->profilepic, '/');
				}

				if (!empty($result->employeeprofile)) {
					$result->employeeprofile = $appUrl . "/storage/" . ltrim($result->employeeprofile, '/');
				}

				// Full name formatting
				$names = array_filter([$result->employeename, $result->employeemiddlename, $result->employeelastname]);
				$result->employeename = implode(' ', $names);

				$result->withinrange = ""; // Kept empty per original logic
			}

			DB::commit();
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Unable to fetch assigned jobs.'], 500);
		}

		return $results;		
		/*
		$appUrl 			=	Config::get('app.url');		

		$self				=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('vendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		
		$employee			=	DB::table('vendor_tbl')
								->select('vendorid as employeeid')
								->where('parentvendorid','=',$vendorid)
								->where('verificationstatus','=',1)
								->get();
		
		$vendors			=	$employee->concat($self)->pluck('employeeid')->toArray();
		$results 			=	DB::table('customer_order_detail as a')
									->select('a.customerid','a.categoryid','a.detailid','a.orderid','a.orderstatus','a.paid','a.creationdate as bookingdatetime','a.servicedate','a.slottime','c.name','c.middlename','c.lastname','c.mobilenumber','c.profilepic','c.email as customeremail','c.ratings as customerratings','c.reviews as customerreviews','d.servicetitle','a.vendorid as employeeid','e.name as employeename','e.middlename as employeemiddlename','e.lastname as employeelastname','a.assignedtime','a.paymentstatus','e.mobilenumber as employeecontact','e.profilepic as employeeprofile','e.email as employeeemail','e.completeaddress as employeeaddress','e.latitude as employeelatitude','e.longitude as employeelongitude',DB::raw("COALESCE('') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(b.address,'') as address"),DB::raw("COALESCE(b.postalcode, '') as postalcode"),DB::raw("COALESCE(b.latitude,'') as latitude"),DB::raw("COALESCE(b.longitude,'') as longitude"))
									->leftjoin('customer_address as b','b.addressid','=','a.addressid')
									->leftjoin('customer_tbl as c','c.customerid','=','a.customerid')
									->leftJoin('services as d', function ($join) {
										$join->on('d.serviceid', '=', 'a.serviceid')
											->on('d.optionid', '=', 'a.optionid');
									})
									->leftjoin('vendor_tbl as e','e.vendorid','=','a.vendorid')
									->whereIn('a.vendorid',$vendors)
									->whereIn('a.orderstatus',[1])
									->paginate($pagelimit, ['*'],'page',$page);
		
		
		foreach($results as $result)
		{
			$result->ordid		=	$result->orderid."#".$result->detailid;						
			$result->slottime	=	date('h:i A',strtotime($result->slottime));
			if($result->profilepic!='')
			{
				$result->profilepic = $appUrl."/storage/".$result->profilepic;
			}
			if($result->employeeprofile!='')
			{
				$result->employeeprofile = $appUrl."/storage/".$result->employeeprofile;
			}
			$result->withinrange	=	"";
			$result->employeename	=	$result->employeename;
			if($result->employeemiddlename!='')
			$result->employeename	=	$result->employeename." ".$result->employeemiddlename;
			if($result->employeelastname!='')
			$result->employeename	=	$result->employeename." ".$result->employeelastname;
		}
		return $results;
		*/
	}
	// AVAILABLE JOB FOR VENDOR TO ACCEPT
	public function getAvailableVendorJob($vendorid,$page,$pagelimit,$isemployee)
	{
		$results = [];
		DB::beginTransaction();
		try
		{
			$appUrl = Config::get('app.url');
			
			$categoryIds = DB::table('vendor_tbl')
							->select('categoryids')
							->where('vendorid', $vendorid)
							->first();

			if ($categoryIds && !empty($categoryIds->categoryids)) {
				$duration = DB::table('task_accept_duration')
							->select('acceptduration')
							->where('recordid', 1)
							->first();

				$currentTime = Carbon::now()->format('H:i:s');
				$serviceDate = Carbon::today()->toDateString();
				$categoryIdsArray = explode(',', $categoryIds->categoryids);

				$results = DB::table('customer_order_notification as a')
							->select(
								'a.requestcycle',
								'a.lastnotificationtime as notificationtime',
								'b.categoryid',
								'b.detailid',
								'b.orderid',
								'b.orderstatus',
								'b.paid',
								'b.slottime',
								'd.name',
								'd.middlename',
								'd.lastname',
								DB::raw("COALESCE('') as mobilenumber"),
								'd.profilepic',
								'e.servicetitle',
								DB::raw("COALESCE('') as employeeid"),
								DB::raw("COALESCE('') as employeename"),
								DB::raw("COALESCE('') as assignedtime"),
								DB::raw("COALESCE('') as workstarttime"),
								DB::raw("COALESCE('') as workendtime"),
								DB::raw("COALESCE(c.address,'') as address"),
								DB::raw("COALESCE(c.postalcode,'') as postalcode"),
								DB::raw("COALESCE('') as latitude"),
								DB::raw("COALESCE('') as longitude")
							)
							->leftJoin('customer_order_detail as b', 'b.detailid', '=', 'a.detailid')
							->leftJoin('customer_address as c', 'c.addressid', '=', 'b.addressid')
							->leftJoin('customer_tbl as d', 'd.customerid', '=', 'b.customerid')
							->leftJoin('services as e', 'e.serviceid', '=', 'b.serviceid')
							->whereIn('b.categoryid', $categoryIdsArray)
							->where('b.orderstatus', 0)
							->whereColumn('e.categoryid', 'b.categoryid')
							->whereColumn('e.optionid', 'b.optionid')
							->where('a.vendorid', $vendorid)
							->where('a.isnotified', '!=', 0)
							->where('a.ismissed', '=', 0)
							->orderBy('a.lastnotificationtime', 'asc') // Removed STR_TO_DATE for shared hosting safety
							->paginate($pagelimit, ['*'], 'page', $page);

				foreach ($results as $result) {
					$result->ordid = $result->orderid . "#" . $result->detailid;
					$result->slottime = date('h:i A', strtotime($result->slottime));

					if (!empty($result->profilepic)) {
						$result->profilepic = $appUrl . "/storage/" . $result->profilepic;
					}

					$within = $result->requestcycle * 2 + 2;
					$result->withinrange = "Within {$within} km of either you or your employee.";

					unset($result->requestcycle);

					$result->duration = ($duration->acceptduration ?? 5) * 60;
				}
			}

			DB::commit();
		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json(['error' => 'Something went wrong.'], 500);
		}

		return $results;		
		/*
		$appUrl 		=	Config::get('app.url');		
		$categoryIds	=	DB::table('vendor_tbl')
								->select('categoryids')
								->where('vendorid', $vendorid)
								->first();
		
		if($categoryIds->categoryids!='')
		{
			$duration			=	DB::table('task_accept_duration')->select('acceptduration')->where('recordid','=',1)->first();
			$current_time 		= 	Carbon::now()->format('H:i:s');
			$servicedate		=	date('Y\-m\-d');
			$categoryIdsArray	=	explode(',',$categoryIds->categoryids);
			
			$results 			=	DB::table('customer_order_notification as a')
										->select('a.requestcycle','a.lastnotificationtime as notificationtime','b.categoryid','b.detailid','b.orderid','b.orderstatus','b.paid','b.slottime','d.name','d.middlename','d.lastname',DB::raw("COALESCE('') as mobilenumber"),'d.profilepic','e.servicetitle',DB::raw("COALESCE('') as employeeid"),DB::raw("COALESCE('') as employeename"),DB::raw("COALESCE('') as assignedtime"),DB::raw("COALESCE('') as workstarttime"),DB::raw("COALESCE('') as workendtime"),DB::raw("COALESCE(c.address,'') as address"),DB::raw("COALESCE(c.postalcode,'') as postalcode"),DB::raw("COALESCE('') as latitude"),DB::raw("COALESCE('') as longitude"))
										->leftjoin('customer_order_detail as b','b.detailid','=','a.detailid')
										->leftjoin('customer_address as c','c.addressid','=','b.addressid')
										->leftjoin('customer_tbl as d','d.customerid','=','b.customerid')
										->leftjoin('services as e','e.serviceid','=','b.serviceid')
										->whereIn('b.categoryid',$categoryIdsArray)
										->whereIn('b.orderstatus',[0])
										->whereColumn('e.categoryid','b.categoryid')
										->whereColumn('e.optionid','b.optionid')
										->where('a.vendorid','=',$vendorid)
										->where('a.isnotified','!=',0)
										->where('a.ismissed','=',0)
										->orderByRaw("STR_TO_DATE(a.lastnotificationtime, '%Y-%m-%d %H:%i:%s') ASC")
										->paginate($pagelimit, ['*'],'page',$page);
			
										
			foreach($results as $result)
			{
				$result->ordid		=	$result->orderid."#".$result->detailid;						
				$result->slottime	=	date('h:i A',strtotime($result->slottime));
				if($result->profilepic!='')
				{
					$result->profilepic = $appUrl."/storage/".$result->profilepic;
				}
				$witin	=	$result->requestcycle*2+2;
				$result->withinrange	=	"Within ".$witin." km of either you or your employee.";
				unset($result->requestcycle);
				
				$result->duration	=	$duration->acceptduration*60;
			}

		}
		else
		{
			$results	=	[];
		}
		return $results;
		*/
	}
	
	public function getEmployee(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'categoryid'			=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'categoryid.required' 	=> 'CATEGORY ID IS REQUIRED',
			'categoryid.numeric' 	=> 'CATEGORY ID SHOULD BE NUMERIC',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid    = (int) $validatedData['vendorid'];
		$accesstoken = (string) $validatedData['accesstoken'];
		$categoryid  = (int) $validatedData['categoryid'];

		$vendor = $this->CheckVendor($vendorid, $accesstoken);

		if(!$vendor)
		{
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
			], 401);
		}
		$baseSelect = ['vendorid as employeeid', 'name', 'middlename', 'lastname', 'mobilenumber', 'profilepic'];	
		
		$self = DB::table('vendor_tbl')
			->select($baseSelect)
			->where([
				['vendorid', '=', $vendorid],
				['isassigned', '=', 0],
				['isavailable', '=', 1],
				['isselfemployeed', '=', 1],
				['verificationstatus', '=', 1],
			])
			->whereRaw("FIND_IN_SET(?, categoryids)", [$categoryid])
			->get();

		$employees = DB::table('vendor_tbl')
			->select($baseSelect)
			->where([
				['parentvendorid', '=', $vendorid],
				['isassigned', '=', 0],
				['isavailable', '=', 1],
				['verificationstatus', '=', 1],
			])
			->whereRaw("FIND_IN_SET(?, employeecatids)", [$categoryid])
			->get();

		$available = $employees->concat($self);

		foreach ($available as $emp)
		{
			if(!empty($emp->profilepic))
			{
				$emp->profilepic = $this->appUrl.'/storage/'.ltrim($emp->profilepic, '/');
			}
		}
		return response()->json([
			'message'     => 'AVAILABLE EMPLOYEES',
			'status'      => 200,
			'vendorid'    => $vendorid,
			'accesstoken' => $accesstoken,
			'data'        => $available,
		], 200);

	}
	public function acceptService(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'orderid'     	=> 'required|numeric',
			'employeeid'  	=> 'required|numeric',
		];

		$messages = [
			'vendorid.required'    => 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' => 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    => 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     => 'DETAIL ID SHOULD BE NUMERIC',
			'orderid.required'     => 'ORDER ID IS REQUIRED',
			'orderid.numeric'      => 'ORDER ID SHOULD BE NUMERIC',
			'employeeid.required'  => 'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric'   => 'EMPLOYEE ID SHOULD BE NUMERIC',
		];

		$validatedData 	= $request->validate($rules, $messages);
		$vendorid    	= (int) $request->header('vendorid');
		$accesstoken 	= (string) $request->header('accesstoken');
		$detailid    	= (int) $request->input('detailid');
		$orderid     	= (int) $request->input('orderid');
		$employeeid  	= (int) $request->input('employeeid');	
		$isexist 		= $this->CheckVendor($vendorid, $accesstoken);

		if(!$isexist)
		{
			return response()->json(['message' => __('messages.unauthorized'), 'status' => 401], 401);
		}		
		$authenticate = [
			'vendorid'           => $isexist->vendorid,
			'accesstoken'        => doubleval($isexist->accesstoken),
			'isverified'         => $isexist->isverified,
			'verificationstatus' => $isexist->verificationstatus,
			'isprofilesubmitted' => $isexist->isprofilecompleted,
			'isemployee'         => $isexist->isemployee,
			'isapproved'         => $isexist->isapproved,
			'isavailable'        => $isexist->isavailable,
		];		
		if($vendorid != $employeeid)
		{
			$parentid = DB::table('vendor_tbl')->where('vendorid', $employeeid)->value('parentvendorid');
			if ($vendorid != $parentid) {
				return response()->json([
					'message'     => 'INVALID VENDOR AND EMPLOYEE RELATION',
					'status'      => 400,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate
				], 400);
			}
		}
		else		
		{
			$parentid	=	$vendorid;
		}
		$isassigned = DB::table('vendor_tbl')->where('vendorid', $employeeid)->value('isassigned');
		if ($isassigned == 1)
		{
			return response()->json([
				'message'     => 'THIS VENDOR/EMPLOYEE IS CURRENTLY ASSIGNED TO AN INCOMPLETE JOB AND CANNOT BE REASSIGNED AT THIS TIME.',
				'status'      => 400,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate
			], 400);
		}		

		try
		{
			$result = DB::transaction(function () use ($vendorid, $accesstoken, $detailid, $orderid, $employeeid, $authenticate,$parentid)
			{
				$duration = DB::table('task_accept_duration')->where('recordid', 1)->first();

				$check = DB::table('customer_order_notification')
					->where('vendorid', $vendorid)
					->where('detailid', $detailid)
					->where('ismissed', 0)
					->where('isnotified', '>', 0)
					->whereRaw("NOW() BETWEEN STR_TO_DATE(notificationtime, '%Y-%m-%d %H:%i:%s') 
							AND DATE_ADD(STR_TO_DATE(notificationtime, '%Y-%m-%d %H:%i:%s'), INTERVAL ? MINUTE)", [$duration->acceptduration])
					->first();

				if(!$check) return 7;

				$order = DB::table('customer_order_detail')
					->where('detailid', $detailid)
					->where('orderid', $orderid)
					->lockForUpdate()
					->first();

				if(!$order) return 2;
				
				if($order->orderstatus!=0) return 1;

				$vendor_wallet = DB::table('vendor_tbl')
					->select('walletamount','minimumbalance',DB::raw('CASE WHEN walletamount >= minimumbalance THEN walletamount ELSE 0 END as wallet_balance'))
					->where('vendorid', $vendorid)
					->first();

				if(!$vendor_wallet) return 3;
				
				if ($vendor_wallet->wallet_balance == 0) return 4;

				$detail = DB::table('customer_order_detail')
					->where('detailid', $detailid)
					->where('orderid', $orderid)
					->first();

				$catid = $detail->categoryid;

				$employee = DB::table('vendor_tbl')
					->where('vendorid', $employeeid)
					->where('verificationstatus', 1)
					->where('isassigned', 0)
					->where('isavailable', 1)
					->where(function ($query) use ($catid) {
						$query->where(function ($q) use ($catid) {
							$q->where('isemployee', 1)->whereRaw("FIND_IN_SET(?, employeecatids)", [$catid]);
						})->orWhere(function ($q) use ($catid) {
							$q->where('isselfemployeed',1)->whereRaw("FIND_IN_SET(?, categoryids)", [$catid]);
						})->orWhere(function ($q) use ($catid) {
							$q->where('isemployee',0)->whereRaw("FIND_IN_SET(?, categoryids)",[$catid]);
						});
					})
					->first();

				if(!$employee) return 5;

				$tier = DB::table('vendor_tbl as a')
					->join('branch_tbl as b', 'b.branchid', '=', 'a.branchid')
					->join('city_tbl as c', 'c.cityid', '=', 'b.operatingcityid')
					->where('a.vendorid', $vendorid)
					->select('c.tierid')
					->first();

				$commission = DB::table('commission_tbl')
					->where('tierid', $tier->tierid)
					->where('categoryid', $catid)
					->value('commission') ?? 0;

				$deduct = $commission ? round(($detail->taxable*$commission)/100,2) : 0;

				$vendorname = trim("{$employee->name} {$employee->middlename} {$employee->lastname}");

				DB::table('customer_order_detail')
					->where('detailid',$detailid)
					->where('orderid',$orderid)
					->update([
						'orderstatus'  => 1,
						'commission'   => $commission,
						'vendorid'     => $employeeid,
						'vendorname'   => $vendorname,
						'assignedby'   => $vendorid,
						'assignedtime' => now(),
					]);
				
				/*
				$financialyear =	$this->resourceController->GetFinancialYear($detail->servicedate);
				$receipt       =	"RCPT" . rand(100, 999) . "" . strtotime(now());

				DB::table('receipt_tbl')->insert([
					'financialyear'   => $financialyear,
					'receiptnumber'   => $receipt,
					'vendorid'        => $vendorid,
					'dramount'        => $deduct,
					'paymentstatus'   => 'paid',
					'paymentdatetime' => now(),
					'orderid'         => $orderid,
					'detailid'        => $detailid,
				]);

				DB::table('vendor_tbl')->where('vendorid', $vendorid)->update([
					'walletamount' => DB::raw("ROUND(walletamount - {$deduct}, 2)")
				]);
				*/
				DB::table('vendor_tbl')->where('vendorid', $employeeid)->update(['isassigned' => 1]);

				DB::table('customer_order_notification')->where('detailid', $detailid)->delete();

				$vendor		=	DB::table('vendor_tbl')->where('vendorid','=',$parentid)->first();
				$vendorname = trim("{$vendor->name} {$vendor->middlename} {$vendor->lastname}");
				DB::table('customer_order_detail')
					->where('detailid','!=',$detailid)
					->where('iscancelled','=',0)
					->where('isfailed','=',0)
					->where('orderid',$orderid)
					->update([
						'orderstatus'  => 1,
						'commission'   => $commission,
						'vendorid'     => $parentid,
						'vendorname'   => $vendorname,
						'assignedby'   => $parentid,
						'assignedtime' => now(),
					]);

				DB::table('customer_order')
					->where('orderid',$orderid)
					->update([
						'isassigned'  => 1,
						'vendorid'     => $parentid,
					]);
				
				DB::table('vendor_tbl')->where('vendorid', $parentid)->update(['isassigned' => 1]);
				DB::table('customer_order_notification')->where('orderid', $orderid)->delete();
				
				return 0; // Success
			});

			switch($result)
			{
				case 1:
					return response()->json(['message'=>'ORDER ALREADY ACCEPTED.','status'=>422,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],422);
				case 2:
					return response()->json(['message' => 'INVALID DETAILS PROVIDED.', 'status' => 400, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 400);
				case 3:
					return response()->json(['message' => __('messages.unauthorized'), 'status' => 401, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 401);
				case 4:
					return response()->json(['message' => 'YOUR AVAILABLE BALANCE IS LESS THAN THE REQUIRED MAINTAINING BALANCE.', 'status' => 422, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 422);
				case 5:
					return response()->json(['message' => 'EMPLOYEE DOES NOT MEET CATEGORY OR AVAILABILITY REQUIREMENTS.', 'status' => 400, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 400);
				case 7:
					return response()->json(['message' => 'ORDER TIME EXPIRED OR NOT VALID ANYMORE.', 'status' => 400, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 400);
				case 0:
					return response()->json(['message'=>'ORDER ACCEPTED SUCCESSFULLY.','status'=>200,'vendorid'=>$vendorid,         'accesstoken'  => $accesstoken,'authenticate' => $authenticate], 200);
				default:
					return response()->json(['message' =>'UNKNOWN ERROR.', 'status' => 500, 'vendorid' => $vendorid, 'accesstoken' => $accesstoken, 'authenticate' => $authenticate], 500);
			}
		}
		catch (\Exception $e)
		{
			return response()->json(['message' => 'INTERNAL SERVER ERROR', 'error' => $e->getMessage(), 'status' => 500], 500);
		}

		
	}
	public function rejectService(Request $request)
	{
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'      => 'required',
			'accesstoken'   => 'required',
			'detailid'      => 'required|numeric',
		];

		$messages = [
			'vendorid.required'     => 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required'  => 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'     => 'DETAIL ID IS REQUIRED',
			'detailid.numeric'      => 'DETAIL ID SHOULD BE NUMERIC',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid    = intval($request->header('vendorid'));
		$accesstoken = (string) $request->header('accesstoken');
		$detailid    = intval($request->input('detailid'));

		try
		{
			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if($isexist)
			{
				$authenticate = [
					'vendorid'            => $isexist->vendorid,
					'accesstoken'         => doubleval($isexist->accesstoken),
					'isverified'          => $isexist->isverified,
					'verificationstatus'  => $isexist->verificationstatus,
					'isprofilesubmitted'  => $isexist->isprofilecompleted,
					'isemployee'          => $isexist->isemployee,
					'isapproved'          => $isexist->isapproved,
					'isavailable'         => $isexist->isavailable,
				];

				$orderid	=	DB::table('vendors_for_notification')
								->where('vendorid','=',$vendorid)
								->where('detailid','=',$detailid)
								->value('orderid');
				
				DB::transaction(function () use ($vendorid, $detailid,$orderid)
				{
					$currenttime=	Carbon::now();
					$currentdate=	$currenttime->toDateString();
					$duration	=	DB::table('task_accept_duration')->where('recordid','=',1)->first();

					$data		=	DB::table('customer_order_notification')
										->select('nextrequest','expiry')
										->where('vendorid','=',$vendorid)
										->where('detailid','=',$detailid)
										->where('orderid','=',$orderid)
										->where('isnotified','=',1)
										->orderby('nextrequest')
										->first();

					$expiryTime = Carbon::parse($data->expiry);
					$now 		= Carbon::now();
					$differenceInMinutes = $now->diffInMinutes($expiryTime,false);
					
					DB::delete('delete from vendors_for_notification where vendorid=? and detailid=?',[$vendorid,$detailid]);
					DB::delete('delete from customer_order_notification where vendorid=? and detailid=?',[$vendorid,$detailid]);
					
					$records	=	DB::table('customer_order_notification')
									->where('orderid','=',$orderid)
									->where('nextrequest','>',$data->nextrequest)
									->get();
					$expiry		=	Carbon::now()->second(0);
					foreach($records as $rec)
					{
						$nexttime	=	Carbon::parse($expiry)->second(0);
						$nextrequest=	$nexttime->toDateTimeString();
						$expirytime	=	Carbon::parse($nexttime)->second(0)->addMinutes($duration->acceptduration);
						$expiry		=	$expirytime->toDateTimeString();
						
						DB::update('update customer_order_notification set nextrequest=?,expiry=? where vendorid=? and detailid=? and orderid=? and nextrequest=? and expiry=?',[$nextrequest,$expiry,$rec->vendorid,$rec->detailid,$rec->orderid,$rec->nextrequest,$rec->expiry]);
					}
					
					
				});

				ProcessNotificationJob::dispatchSync($orderid);

				return response()->json([
					'message'     => 'TASK REJECTION SUBMITTED SUCCESSFULLY',
					'status'      => 200,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate
				], 200);
			}
			else
			{
				return response()->json([
					'message'     => __('messages.unauthorized'),
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'status'      => 401
				],401);
			}
		}
		catch(QueryException $e)
		{
			return response()->json(['message'     => $e->getMessage(),'vendorid'    => $vendorid,'accesstoken' => $accesstoken,'status'=> 400], 400);
		}
	}	

    public function startPin(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'startpin'  	=> 'nullable|numeric',
		];

		$messages = [
			'vendorid.required'    => 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' => 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    => 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     => 'DETAIL ID SHOULD BE NUMERIC',
			'startpin.numeric'     => 'START PIN SHOULD BE NUMERIC',
			'employeeid.numeric'   => 'EMPLOYEE ID SHOULD BE NUMERIC',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   = intval($request->header('vendorid'));
		$accesstoken= (string) $request->header('accesstoken');
		$detailid   = intval($request->input('detailid'));
		//$employeeid = intval($request->input('employeeid'));
		$startpin 	= (String) $request->input('startpin');



		try
		{
			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if (!$isexist) {
				return response()->json([
					'message'    => __('messages.unauthorized'),
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken,
					'status'     => 401
				], 401);
			}

			$authenticate = [
				'vendorid'           => $isexist->vendorid,
				'accesstoken'        => doubleval($isexist->accesstoken),
				'isverified'         => $isexist->isverified,
				'verificationstatus' => $isexist->verificationstatus,
				'isprofilesubmitted' => $isexist->isprofilecompleted,
				'isemployee'         => $isexist->isemployee,
				'isapproved'         => $isexist->isapproved,
				'isavailable'        => $isexist->isavailable,
			];

			$detail = DB::table('customer_order_detail')
						->where([
							['detailid', '=', $detailid],
							['vendorid', '=', $vendorid]
						])->first();

			if (!$detail) {
				return response()->json([
					'message'    => 'INVALID ATTEMPT',
					'status'     => 400,
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			// If no startpin provided, generate OTP
			if ($startpin == '')
			{
				$mobilenumber = DB::table('customer_tbl')
								  ->where('customerid', $detail->customerid)
								  ->value('mobilenumber');
				$otp = $this->getOTP();
				$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');

				if($isSent)
				{
					DB::beginTransaction();
					try {
						$res = DB::update(
							'UPDATE customer_order_detail SET workstartpin=? 
							 WHERE detailid=? AND orderstatus=? AND iscancelled=? AND ispinverified=?',
							[$otp, $detailid, 1, 0, 0]
						);
						DB::commit();

						return response()->json([
							'message' => 'THE START PIN HAS BEEN GENERATED SUCCESSFULLY. PLEASE ASK YOUR CLIENT/CUSTOMER TO SHARE THE START PIN WITH YOU IN ORDER TO BEGIN THE JOB.',
							'status' => 200,
							'vendorid' => $vendorid,
							'accesstoken' => $accesstoken,
							'authenticate' => $authenticate
						], 200);

					}
					catch (\Exception $e)
					{
						DB::rollBack();
						return response()->json([
							'message' => 'FAILED TO GENERATE START PIN. TRY AGAIN.',
							'status' => 500,
							'error' => $e->getMessage()
						], 500);
					}
				}
				else
				{
					return response()->json([
						'message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
						'status' => 400,
						'mobilenumber' => $mobilenumber
					], 400);
				}

			}
			else
			{
				// Verify entered startpin
				$check = DB::table('customer_order_detail')
							->where([
								['vendorid', '=', $vendorid],
								['detailid', '=', $detailid],
								['workstartpin', '=', $startpin],
								['ispinverified', '=', 0],
								['iscancelled', '=', 0],
								['orderstatus', '=', 1],
							])->first();

				if ($check) 
				{
					DB::beginTransaction();
					try 
					{
						DB::update(
							'UPDATE customer_order_detail 
							 SET ispinverified=?, orderstatus=?, workstarttime=? 
							 WHERE detailid=? AND orderstatus=? AND iscancelled=? AND workstartpin=?',
							[1, 2, now(), $detailid, 1, 0, $startpin]
						);
						DB::commit();

						return response()->json([
							'message' => 'START PIN MATCHED.',
							'status' => 200,
							'vendorid' => $vendorid,
							'accesstoken' => $accesstoken,
							'authenticate' => $authenticate
						], 200);
					}
					catch (\Exception $e)
					{
						DB::rollBack();
						return response()->json([
							'message' => 'FAILED TO START JOB. TRY AGAIN.',
							'status' => 500,
							'error' => $e->getMessage()
						], 500);
					}
				}
				else
				{
					return response()->json([
						'message' => 'INVALID START PIN PROVIDED OR THIS JOB MAY HAVE ALREADY BEEN STARTED. PLEASE CHECK THE JOB STATUS AND TRY AGAIN.',
						'status' => 400,
						'vendorid' => $vendorid,
						'accesstoken' => $accesstoken,
						'authenticate' => $authenticate
					], 400);
				}
			}

		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 400
			], 400);
		}
		
    }
	
    public function endPin(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'endpin'  		=> 'nullable|numeric',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     	=> 'DETAIL ID SHOULD BE NUMERIC',
			'endpin.numeric'     	=> 'END PIN SHOULD BE NUMERIC',
			'employeeid.numeric'   	=> 'EMPLOYEE ID SHOULD BE NUMERIC',
		];

		$validatedData 	= $request->validate($rules, $messages);

		$vendorid   	= intval($request->header('vendorid'));
		$accesstoken	= (string) $request->header('accesstoken');
		$detailid   	= intval($request->input('detailid'));
		//$employeeid 	= intval($request->input('employeeid'));
		$endpin 		= (String) $request->input('endpin');

		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);
			if (!$vendor) {
				return response()->json([
					'message' => __('messages.unauthorized'),
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'status' => 401
				], 401);
			}

			$authenticate = [
				'vendorid'           => $vendor->vendorid,
				'accesstoken'        => doubleval($vendor->accesstoken),
				'isverified'         => $vendor->isverified,
				'verificationstatus' => $vendor->verificationstatus,
				'isprofilesubmitted' => $vendor->isprofilecompleted,
				'isemployee'         => $vendor->isemployee,
				'isapproved'         => $vendor->isapproved,
				'isavailable'        => $vendor->isavailable,
			];

			$detail = DB::table('customer_order_detail')
				->where([
					['detailid', '=', $detailid],
					['vendorid', '=', $vendorid],
					['orderstatus', '=', 2],
				])
				->first();

			if (!$detail) 
			{
				return response()->json([
					'message' => 'INVALID ATTEMPT',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			// Check for required pictures
			if ($detail->startpictures == 0)
			{
				return response()->json([
					'message' => 'THIS SERVICE REQUIRES PICTURES BEFORE STARTING THE JOB. PLEASE TAKE THE PICTURES AND TRY AGAIN.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			if ($detail->finishpictures == 0) 
			{
				return response()->json([
					'message' => 'THIS SERVICE REQUIRES PICTURES AFTER FINISHING THE JOB. PLEASE TAKE THE PICTURES AND TRY AGAIN.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			if($endpin==='')
			{
				// Generate OTP and send it
				$mobilenumber = DB::table('customer_tbl')
					->where('customerid', $detail->customerid)
					->value('mobilenumber');

				$otp = $this->getOTP();
				$isSent = $this->smsService->pushMessage($mobilenumber, $otp, 'OTP', 0, '');

				if(!$isSent)
				{
					return response()->json([
						'message' => 'OTP COULD NOT BE SENT. PLEASE TRY AGAIN',
						'status' => 400,
						'mobilenumber' => $mobilenumber
					], 400);
				}

				DB::update(
					'UPDATE customer_order_detail SET workendpin=? WHERE detailid=? AND orderstatus=? AND iscancelled=? AND ispinverified=?',
					[$otp, $detailid, 2, 0, 1]
				);

				return response()->json([
					'message' => 'THE END PIN HAS BEEN GENERATED SUCCESSFULLY. PLEASE ASK YOUR CLIENT/CUSTOMER TO SHARE THE END PIN WITH YOU IN ORDER TO COMPLETE THE JOB.',
					'status' => 200,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 200);
			}

			// Validate endpin
			$check = DB::table('customer_order_detail')
				->where([
					['vendorid', '=', $vendorid],
					['detailid', '=', $detailid],
					['workendpin', '=', $endpin],
					['ispinverified', '=', 1],
					['orderstatus', '=', 2],
					['iscancelled', '=', 0]
				])
				->first();

			if(!$check)
			{
				return response()->json([
					'message' => 'INVALID END PIN PROVIDED OR ALREADY MARKED AS COMPLETED.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			DB::update('UPDATE customer_order_detail SET ispinverified=?, orderstatus=?, workendtime=? WHERE detailid=? AND orderstatus=? AND iscancelled=? AND workendpin=?',[2, 3, now(), $detailid, 2, 0, $endpin]);

			DB::update('UPDATE vendor_tbl SET isassigned=? WHERE vendorid=? AND isassigned=?',[0,$vendorid,1]);

			$this->checkAndSendInvoice($detailid);

			return response()->json(['message'=>'THE END PIN HAS BEEN MATCHED. THIS JOB IS NOW MARKED AS COMPLETED.','status'=>200,'vendorid' => $vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],200);

		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 400
			], 400);
		}

    }
	public function checkAndSendInvoice($detailid)
	{
		
		$detail		=	DB::table('customer_order_detail')->where('detailid','=',$detailid)->first();

		$count		=	DB::table('customer_order_detail')
							->where('orderid','=',$detail->orderid)
							->where('iscancelled','=',0)
							->where('orderstatus','!=',3)
							->count();
							
		if($count==0)
		{	
			$customer	=	DB::table('customer_tbl')->where('customerid','=',$detail->customerid)->first();
			
			$order		=	DB::table('customer_order as a')
								->select('a.*','b.address','b.postalcode','b.latitude','b.longitude')
								->leftjoin('customer_address as b','b.addressid','=','a.addressid')
								->where('a.orderid','=',$detail->orderid)
								->first();
								
			$details	=	DB::table('customer_order_detail as a')
								->leftJoin('services as b', function ($join) {
									$join->on('b.serviceid', '=', 'a.serviceid')
										->on('b.optionid', '=', 'a.optionid');
								})
								->where('a.orderid','=',$detail->orderid)
								->where('a.iscancelled','=',0)
								->get();
			
			$invoice		=	$detail->orderid."".time();
			$filename		=	$invoice.'.pdf';

			$financialyear 	= 	$this->resourceController->GetFinancialYear($this->todays_date);
			$invoicenumber	=	$financialyear."/".$detail->orderid;

								
			$pdf		=	PDF::loadView('pdf.invoice',['customer'=>$customer,'order'=>$order,'details'=>$details,'invoicenumber'=>$invoicenumber,'invoicedate'=>$this->todays_date]);

			$pdf->setPaper('A4','portrait'); // Set A4 size


			$pdf->save(storage_path('app/public/invoices/'.$filename));

			
			$exists	=	DB::table('receipt_tbl')
							->where('customerid','=',$customer->customerid)
							->where('orderid','=',$detail->orderid)
							->where('invoicenumber','=',$invoicenumber)
							->where('recordtype','=','CUSTOMER_INVOICE')
							->exists();
			
			if(!$exists)
			{
				DB::table('receipt_tbl')->insert([
					'customerid'   	=>	$customer->customerid,
					'orderid'       => 	$detail->orderid,
					'financialyear' => 	$financialyear,
					'netamount'     => 	$order->netpayable,
					'invoicenumber' => 	$invoicenumber,
					'invoicedate'   => 	$this->todays_datetime,
					'invoicefile'   => 	$filename,
					'recordtype'  	=> 	'CUSTOMER_INVOICE',
				]);
				
			}
			else
			{
				DB::table('receipt_tbl')
						->where('customerid','=',$customer->customerid)
						->where('orderid','=',$detail->orderid)
						->where('invoicenumber','=',$invoicenumber)
						->where('financialyear','=',$financialyear)
						->update([
							'invoicefile' => $filename,
						]);
			}
			
		}
			
		return null;
	}
    public function getPicture(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     	=> 'DETAIL ID SHOULD BE NUMERIC',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailid   	=	intval($request->input('detailid'));

		try
		{
			$isexist = $this->CheckVendor($vendorid, $accesstoken);

			if (!$isexist) {
				return response()->json([
					'message'     => __('messages.unauthorized'),
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'status'      => 401,
				], 401);
			}

			$authenticate = [
				'vendorid'            => $isexist->vendorid,
				'accesstoken'         => doubleval($isexist->accesstoken),
				'isverified'          => $isexist->isverified,
				'verificationstatus'  => $isexist->verificationstatus,
				'isprofilesubmitted'  => $isexist->isprofilecompleted,
				'isemployee'          => $isexist->isemployee,
				'isapproved'          => $isexist->isapproved,
				'isavailable'         => $isexist->isavailable,
			];

			$detail = DB::table('customer_order_detail as a')
				->select('a.*', 'b.startingimages', 'b.finishingimages')
				->leftJoin('services as b', 'b.serviceid', '=', 'a.serviceid')
				->where('a.detailid', $detailid)
				->where('a.ispinverified', '!=', 0)
				->where('a.orderstatus', 2)
				->first();

			if (!$detail) {
				return response()->json([
					'message'     => 'INVALID ATTEMPTT',
					'status'      => 400,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate,
				], 400);
			}

			$labels = array_filter(explode(',', $detail->startingimages));
			$appUrl = rtrim(config('app.url'), '/');

			$loadPictures = function ($picturetime) use ($labels, $detailid, $appUrl) {
				return DB::table('service_photo_labels as a')
					->leftJoin('service_photos as b', function ($join) use ($detailid, $picturetime) {
						$join->on('a.labelid', '=', 'b.labelid')
							 ->where('b.detailid', '=', $detailid)
							 ->where('b.picturetime', '=', $picturetime);
					})
					->whereIn('a.labelid', $labels)
					->select('a.labelid', 'a.labelvalue', DB::raw("COALESCE(b.pictureurl,'') as pictureurl"))
					->get()
					->map(function ($item) use ($appUrl) {
						$item->pictureurl = $item->pictureurl 
							? $appUrl . '/storage/' . ltrim($item->pictureurl, '/') 
							: '';
						return $item;
					});
			};

			$takestartimages  = $loadPictures('STARTING');
			$takefinishimages = $loadPictures('FINISHING');

			return response()->json([
				'message'     => 'PICTURES TAKEN',
				'status'      => 200,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate,
				'data'        => [
					'startpictures'    => $detail->startpictures ?? '',
					'finishpictures'   => $detail->finishpictures ?? '',
					'takestartimages'  => $takestartimages,
					'takefinishimages' => $takefinishimages,
				]
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message'     => $e->getMessage(),
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'status'      => 400,
			], 400);
		}
    }


    public function takePicture(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'picturetime' 	=> 'required|in:STARTING,FINISHING',
			'labelid'       => 'required|numeric',
			'photos'        => 'required|file|mimes:jpg,jpeg,png,gif|max:1024',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     	=> 'DETAIL ID SHOULD BE NUMERIC',
			'picturetime.required'  => 'PICTURE TIME IS REQUIRED',
			'picturetime.in' 		=> 'THE PICTURE TIME MUST BE EITHER STARTING OR FINISHING',
			'labelid.required' 		=> 'AT LEAST ONE LABEL ID IS REQUIRED',
			'labelid.numeric' 		=> 'THE LABEL ID MUST NUMERIC',
			'photos.required' 		=> 'PHOTO IS REQUIRED',
			'photos.file' 			=> 'EACH PHOTO MUST BE A VALID FILE',
			'photos.mimes' 			=> 'EACH PHOTO MUST BE AN IMAGE FILE',
			'photos.max' 			=> 'MAX PHOTO SIZE IS 1 MB',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailid   	=	intval($request->input('detailid'));
		$picturetime   	=	(string) $request->input('picturetime');
		$photo			=	$request->file('photos');
		$labelid		=	$request->input('labelid');


		try
		{
			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if(!$isexist)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'status' => 401
				], 401);
			}

			$authenticate = [
				'vendorid'           => $isexist->vendorid,
				'accesstoken'        => doubleval($isexist->accesstoken),
				'isverified'         => $isexist->isverified,
				'verificationstatus' => $isexist->verificationstatus,
				'isprofilesubmitted' => $isexist->isprofilecompleted,
				'isemployee'         => $isexist->isemployee,
				'isapproved'         => $isexist->isapproved,
				'isavailable'        => $isexist->isavailable,
			];

			$detail = DB::table('customer_order_detail')
						->where([
							['detailid', '=', $detailid],
							['vendorid', '=', $vendorid],
							['orderstatus', '=', 2],
						])
						->first();

			if (!$detail || $detail->orderstatus != 2)
			{
				return response()->json([
					'message' => 'INVALID ATTEMPT',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			if ($picturetime === 'STARTING' && $detail->finishpictures == 1)
			{
				return response()->json([
					'message' => 'START PHOTOS MUST BE TAKEN BEFORE FINISHING PHOTOS.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			if ($picturetime === 'FINISHING' && $detail->startpictures == 0) {
				return response()->json([
					'message' => 'KINDLY ENSURE THAT ALL STARTING PHOTOS ARE COMPLETED BEFORE PROCEEDING WITH FINISH PHOTOS.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			$imageColumn = $picturetime === 'STARTING' ? 'startingimages' : 'finishingimages';
			$labelString = DB::table('service_tbl')->where('serviceid', $detail->serviceid)->value($imageColumn);
			$expectedLabels = $labelString ? explode(',', $labelString) : [];

			if (!in_array($labelid, $expectedLabels)) {
				return response()->json([
					'message' => 'INVALID ATTEMPT',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate
				], 400);
			}

			$imagePath = $photo->store('uploads/servicephotos', 'public');

			$existing = DB::table('service_photos')
						->where('detailid', $detailid)
						->where('labelid', $labelid)
						->where('picturetime', $picturetime)
						->first();

			if ($existing && $existing->pictureurl) {
				Storage::disk('public')->delete($existing->pictureurl);
			}

			DB::table('service_photos')->updateOrInsert(
				[
					'detailid'    => $detailid,
					'labelid'     => $labelid,
					'picturetime' => $picturetime,
				],
				[
					'vendorid'     => $vendorid,
					'pictureurl'   => $imagePath,
					'creationdate' => now(),
				]
			);

			// Check if all required labels are uploaded
			$uploadedCount = DB::table('service_photos')
								->where('detailid', $detailid)
								->where('picturetime', $picturetime)
								->whereIn('labelid', $expectedLabels)
								->distinct()
								->count('labelid');

			if ($uploadedCount === count($expectedLabels))
			{
				$columnToUpdate = $picturetime === 'STARTING' ? 'startpictures' : 'finishpictures';
				DB::table('customer_order_detail')
					->where('detailid', $detailid)
					->where($columnToUpdate, 0)
					->update([$columnToUpdate => 1]);
			}

			return response()->json([
				'message' => 'PHOTO UPLOADED SUCCESSFULLY',
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 200
			], 200);

		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 400
			], 400);
		}		
    }
	

    public function getServices(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'categoryid'    => 'required|numeric',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     	=> 'DETAIL ID SHOULD BE NUMERIC',
			'categoryid.required'   => 'CATEGORY ID IS REQUIRED',
			'categoryid.numeric'    => 'CATEGORY ID SHOULD BE NUMERIC',
		];

		$validatedData 	=	$request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailid   	=	intval($request->input('detailid'));
		$categoryid   	=	intval($request->input('categoryid'));
		$page  			= 	intval($request->input('page',1));
		$pagelimit  	= 	intval($request->input('pagelimit',10));
		
		
		try
		{
			DB::beginTransaction();

			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if(!$isexist)
			{
				DB::rollBack();
				return response()->json([
					'message'     => __('messages.unauthorized'),
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'status'      => 401,
				], 401);
			}

			$authenticate = [
				'vendorid'           => $isexist->vendorid,
				'accesstoken'        => doubleval($isexist->accesstoken),
				'isverified'         => $isexist->isverified,
				'verificationstatus' => $isexist->verificationstatus,
				'isprofilesubmitted' => $isexist->isprofilecompleted,
				'isemployee'         => $isexist->isemployee,
				'isapproved'         => $isexist->isapproved,
				'isavailable'        => $isexist->isavailable,
			];

			$detail = DB::table('customer_order_detail')
				->where([
					['detailid', '=', $detailid],
					['vendorid', '=', $vendorid],
					['categoryid', '=', $categoryid],
					['orderstatus', '=', 2],
					['ispinverified', '=', 1],
				])
				->first();

			if(!$detail)
			{
				DB::rollBack();
				return response()->json([
					'message'     => 'INVALID ATTEMPT',
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'status'      => 400,
				], 400);
			}

			
			$discount = $this->getDiscount();
			

			$services = DB::table('services as a')
				->select(
					'a.categoryid', 'a.serviceid', 'a.optionid', 'a.servicetitle',
					'a.mrp as servicecharge', DB::raw('0 as taxable'), 'a.servicepic',
					'a.likes', 'a.ratings', 'a.reviews', 'a.requiredtime', 'a.description',
					'b.taxrate',
					DB::raw('(SELECT COUNT(serviceid) FROM services WHERE serviceid = a.serviceid AND optionid != 0) as options')
				)
				->leftJoin('tax_tbl as b', 'b.taxid', '=', 'a.taxid')
				->where('a.categoryid', $categoryid)
				->paginate($pagelimit, ['*'], 'page', $page);

			$excludeCombination = [$detail->serviceid, $detail->optionid];
			

			$filteredServices = collect($services->items())->filter(function ($item) use ($excludeCombination) {
				return !($item->serviceid == $excludeCombination[0] && $item->optionid == $excludeCombination[1]);
			})->values();

			foreach ($filteredServices as $ser)
			{
				if (!empty($ser->servicepic))
				{
					$ser->servicepic = $this->appUrl . "/storage/" . $ser->servicepic;
				}

				$ser->taxable = round($ser->servicecharge - (($ser->servicecharge * $discount) / 100), 2);
				$ser->netpayable = round($ser->taxable + (($ser->taxable * $ser->taxrate) / 100), 2);
			}

			DB::commit();

			return response()->json([
				'message'      => 'SERVICES LIST',
				'vendorid'     => $vendorid,
				'accesstoken'  => $accesstoken,
				'authenticate' => $authenticate,
				'status'       => 200,
				'totalpages'   => $services->lastPage(),
				'data'         => $filteredServices,
			], 200);

		}
		catch (\Exception $e)
		{
			DB::rollBack();
			return response()->json([
				'message'     => $e->getMessage(),
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'status'      => 400,
			], 400);
		}		
    }

    public function addOnService(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'      => 'required',
			'accesstoken'   => 'required',
			'detailid'    	=> 'required|numeric',
			'categoryid'    => 'required|numeric',
			'serviceid'     => 'required|array',
			'optionid'      => 'required|array',
			'quantity'      => 'required|array',
			'serviceid.*'   => 'integer',
			'optionid.*'    => 'integer',
			'quantity.*'    => 'integer',
		];

		$messages = [
			'vendorid.required'        	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required'     	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'     	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     		=> 'NUMERIC VALUES ONLY',
			'categoryid.required'     	=> 'CATEGORY ID IS REQUIRED',
			'categoryid.numeric'     	=> 'NUMERIC VALUES ONLY',
			'serviceid.required'       	=> 'SERVICE IDs ARE REQUIRED',
			'serviceid.array'          	=> 'SERVICE IDs MUST BE AN ARRAY',
			'optionid.required'        	=> 'OPTION IDs ARE REQUIRED',
			'optionid.array'           	=> 'OPTION IDs MUST BE AN ARRAY',
			'quantity.required'        	=> 'QUANTITY IS REQUIRED',
			'quantity.array'           	=> 'QUANTITY MUST BE AN ARRAY',
			'serviceid.*.integer'      	=> 'SERVICE IDs MUST BE INTEGERS',
			'optionid.*.integer'       	=> 'OPTION IDs MUST BE INTEGERS',
			'quantity.*.integer'       	=> 'QUANTITY MUST BE INTEGERS',
		];

		$validatedData 	=	$request->validate($rules, $messages);
		

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailid   	=	intval($request->input('detailid'));
		$categoryid   	=	intval($request->input('categoryid'));

		$serviceids   	=	$request->input('serviceid');
		$optionids   	=	$request->input('optionid');
		$quantities   	=	$request->input('quantity');

		if(count($serviceids) !== count($optionids) || count($serviceids) !== count($quantities))
		{
			return response()->json([
				'message'     => 'SERVICE ID, OPTION ID AND QUANTITY COUNTING SHOULD BE SAME',
				'status'      => 400,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
			], 400);
		}
		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);
			if (!$vendor)
			{
				return response()->json(['message' =>__('messages.unauthorized'), 'status' => 401], 401);
			}

			$detail = DB::table('customer_order_detail')
				->where([
					['detailid', '=', $detailid],
					['vendorid', '=', $vendorid],
					['categoryid', '=', $categoryid],
					['orderstatus', '=', 2],
					['ispinverified', '=', 1],
				])
				->first();

			if (!$detail)
			{
				return response()->json(['message' => 'DETAIL NOT FOUND', 'status' => 404], 404);
			}

			$discount = $this->getDiscount();
			$parentid = DB::table('vendor_tbl')->where('vendorid', $vendorid)->value('parentvendorid') ?? $vendorid;

			$tierid = DB::table('vendor_tbl as a')
				->join('branch_tbl as b', 'b.branchid', '=', 'a.branchid')
				->join('city_tbl as c', 'c.cityid', '=', 'b.operatingcityid')
				->where('a.vendorid', $parentid)
				->value('c.tierid');

			$commission = DB::table('commission_tbl')
				->where([
					['tierid', '=', $tierid],
					['categoryid', '=', $categoryid],
				])
				->value('commission') ?? 0;

			$financialyear = $this->resourceController->GetFinancialYear($detail->servicedate);

			foreach ($serviceids as $index => $serviceid)
			{
				$optionid = $optionids[$index];
				$quantity = $quantities[$index];

				$exists = DB::table('customer_order_detail')
					->where([
						['orderid', '=', $detail->orderid],
						['serviceid', '=', $serviceid],
						['optionid', '=', $optionid],
					])
					->exists();

				if ($exists) continue;

				$service = DB::table('services as a')
					->select(
						'a.categoryid', 'a.serviceid', 'a.optionid', 'a.servicetitle',
						'a.mrp as servicecharge', 'a.servicepic', 'a.likes', 'a.ratings',
						'a.reviews', 'a.requiredtime', 'a.description', 'b.taxrate',
						'a.startingimages', 'a.finishingimages'
					)
					->leftJoin('tax_tbl as b', 'b.taxid', '=', 'a.taxid')
					->where([
						['a.serviceid', '=', $serviceid],
						['a.optionid', '=', $optionid],
						['a.categoryid', '=', $categoryid],
					])
					->first();

				if (!$service) continue;

				// Prepare image flags
				$startpictures = $service->startingimages ? 0 : 1;
				$startpic      = $startpictures;
				$finishpictures = $service->finishingimages ? 0 : 1;
				$finishpic      = $service->finishingimages ? 1 : 1;

				// Tax and pricing
				$servicecharge = (float) $service->servicecharge;
				$taxable = round($servicecharge - (($servicecharge * $discount) / 100), 2);
				$taxable = round($taxable * $quantity, 2);
				$taxvalue = round(($taxable * $service->taxrate) / 100, 2);
				$netpayable = round($taxable + $taxvalue, 2);
				$deduct = $commission ? round(($taxable * $commission) / 100, 2) : 0;

				DB::table('customer_order_detail')->insert([
					'addondetailid'     => $detail->detailid,
					'customerid'        => $detail->customerid,
					'orderid'           => $detail->orderid,
					'categoryid'        => $detail->categoryid,
					'serviceid'         => $serviceid,
					'optionid'          => $optionid,
					'quantity'          => $quantity,
					'servicecharge'     => $servicecharge,
					'discount'          => $detail->discount,
					'visitingcharge'    => $detail->visitingcharge,
					'visitingdiscount'  => $detail->visitingdiscount,
					'visitingtax'       => $detail->visitingtax,
					'taxable'           => $taxable,
					'taxvalue'          => $taxvalue,
					'payable'           => $netpayable,
					'commission'        => $commission,
					'balanceamount'     => $netpayable,
					'orderstatus'       => $detail->orderstatus,
					'vendorid'          => $detail->vendorid,
					'vendorname'        => $detail->vendorname,
					'assignedby'        => $detail->assignedby,
					'assignedtime'      => now(),
					'workstartpin'      => $detail->workstartpin,
					'workstarttime'     => $detail->workstarttime,
					'ispinverified'     => $detail->ispinverified,
					'addressid'         => $detail->addressid,
					'servicedate'       => $detail->servicedate,
					'slottime'          => $detail->slottime,
					'isaddon'           => 1,
					'startpictures'     => $startpictures,
					'finishpictures'    => $finishpictures,
					'startpic'          => $startpic,
					'finishpic'         => $finishpic,
					'creationdate'      => now()
				]);

				DB::table('receipt_tbl')->insert([
					'financialyear'   => $financialyear,
					'receiptnumber'   => 'RCPT' . rand(100, 999) . strtotime(now()),
					'vendorid'        => $parentid,
					'dramount'        => $deduct,
					'paymentstatus'   => 'paid',
					'paymentdatetime' => now(),
					'orderid'         => $detail->orderid,
				]);
			}

			return response()->json([
				'message' => 'ADDON SERVICES SUCCESSFULLY ADDED',
				'status'  => 200,
			]);

		} catch (\Exception $e) {
			return response()->json([
				'message' => 'SOMETHING WENT WRONG',
				'error'   => $e->getMessage(),
				'status'  => 500,
			], 500);
		}

    }

    public function dailyAttendance(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
		];

		$validatedData 	=	$request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string)$request->header('accesstoken');
		$isavailable   	=	$request->input('isavailable');
        try
        {
			$isexist	=	DB::table('vendor_tbl')
								->where('vendorid', '=', $vendorid)
								->where('accesstoken', '=', $accesstoken)
								->first();
			if($isexist)
			{
				$authenticate = [
					'vendorid'            =>	$isexist->vendorid,
					'accesstoken'         =>	doubleval($isexist->accesstoken),
					'isverified'          =>	$isexist->isverified,
					'verificationstatus'  =>	$isexist->verificationstatus,
					'isprofilesubmitted'  =>	$isexist->isprofilecompleted,
					'isemployee'          =>	$isexist->isemployee,
					'isapproved'          =>	$isexist->isapproved,
					'isavailable'		  =>	$isexist->isavailable,
				];
				$today	=	date('Y\-m\-d');
				$currentstatus	=	$isexist->isavailable;
				if($isavailable=='')
				{
					return response()->json(['message'=>'STATUS ENQUIRY','vendorid'=>$vendorid,'accesstoken'=>doubleval($accesstoken),'authenticate'=>$authenticate,'data'=>['currentstatus'=>intval($currentstatus)],'status' => 200],200);
				}
				
				$exists	=	DB::table('availability_tbl')
								->where('vendorid','=',$vendorid)
								->where('attendancedate','=',$today)
								->exists();
				if($exists)
				{
					DB::update('update vendor_tbl set isavailable=? where vendorid=?',[$isavailable,$vendorid]);
					
					$authenticate = [
						'vendorid'            =>	$isexist->vendorid,
						'accesstoken'         =>	doubleval($isexist->accesstoken),
						'isverified'          =>	$isexist->isverified,
						'verificationstatus'  =>	$isexist->verificationstatus,
						'isprofilesubmitted'  =>	$isexist->isprofilecompleted,
						'isemployee'          =>	$isexist->isemployee,
						'isapproved'          =>	$isexist->isapproved,
						'isavailable'		  =>	$isavailable,
					];
					$isexist	=	$this->CheckVendor($vendorid, $accesstoken);
					
					return response()->json(['message'=>'AVAILABILITY UPDATED SUCCESSFULLY','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>['gotoform'=>0,'currentstatus'=>intval($isavailable)],'status' => 200],200);
				}
				else
				{
					if($isavailable==1)
					{
						$data	=	DB::table('login_options')->select('labelid','labelvalue','inputtype')->orderby('displayorder')->get();
						return response()->json(['message'=>'GO TO ATTENDANCE FORM','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>['gotoform'=>1,'currentstatus'=>intval($isexist->isavailable)],'status' => 200],200);
					}
					if($isavailable==0)
					{
						DB::update('update vendor_tbl set isavailable=? where vendorid=?',[$isavailable,$vendorid]);
						
						return response()->json(['message'=>'AVAILABILITY UPDATED SUCCESSFULLY','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>['gotoform'=>0,'currentstatus'=>intval($isavailable)],'status' => 200],200);
					}
					
					return response()->json(['message'=>'INVALID ATTEMPT','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'status' => 400],400);
				}
			}
			else
			{
				return response()->json(['message'=>__('messages.unauthorized'),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status' => 401], 401);
			}
        }
        catch(QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status'=>400], 400);
        }
    }

    public function getLoginOption(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
		];

		$validatedData 	=	$request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');

	
		
        try
        {
			$isexist	=	$this->CheckVendor($vendorid, $accesstoken);
			if($isexist)
			{
				$authenticate = [
					'vendorid'            => $isexist->vendorid,
					'accesstoken'         => doubleval($isexist->accesstoken),
					'isverified'          => $isexist->isverified,
					'verificationstatus'  => $isexist->verificationstatus,
					'isprofilesubmitted'  => $isexist->isprofilecompleted,
					'isemployee'          => $isexist->isemployee,
					'isapproved'          => $isexist->isapproved,
					'isavailable'=>$isexist->isavailable,
				];
				
				$type_input	=	DB::table('login_options')
								->select('labelid','labelvalue','inputtype')
								->where('inputtype','=','text')
								->orderby('displayorder')
								->get();

				$type_file	=	DB::table('login_options')
								->select('labelid','labelvalue','inputtype')
								->where('inputtype','=','file')
								->orderby('displayorder')
								->first();
				
				$keyname	=	"label_".$type_file->labelid;
				$labelname	=	$type_file->labelvalue;
				
				return response()->json(['message' => 'OPTION LISTS','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>['type_input'=>$type_input,'keyname'=>$keyname,'labelvalue'=>$labelname],'status' => 200],200);
			}
			else
			{
				return response()->json(['message' => __('messages.unauthorized'),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status' => 401], 401);
			}
        }
        catch(QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status'=>400], 400);
        }
		
    }

    public function setAvailability(Request $request)
    {
		$rules = [];
		$messages = [];
		
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required|numeric',
			'accesstoken' 	=> 'required',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
		];
		$loginOptions = Cache::remember('login_options', 120, function () {
			return DB::table('login_options')->select('labelid', 'inputtype')->get();
		});
		foreach ($loginOptions as $option) {
			$labelKey = "label_" . $option->labelid;

			if ($option->inputtype === 'text') {
				$rules[$labelKey] = 'required|in:1';
				$messages["{$labelKey}.required"] = "ALL VALUE IS REQUIRED";
				$messages["{$labelKey}.in"] = "VALUE SHOULD BE 1";
			}

			if ($option->inputtype === 'file') {
				$rules[$labelKey] = 'required|file|mimes:jpg,jpeg,png,gif|max:10240';
				$messages["{$labelKey}.required"] = "ALL FILE IS REQUIRED";
				$messages["{$labelKey}.file"] = "MUST BE A FILE";
				$messages["{$labelKey}.mimes"] = "MIME TYPE SHOULD BE IMAGE";
				$messages["{$labelKey}.max"] = "MAX FILE SIZE IS 1 MB";
			}
		}
		$validatedData = $request->validate($rules, $messages);

		$vendorid = (int) $request->vendorid;
		$accesstoken = (string) $request->accesstoken;

		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if (!$vendor)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'status' => 401
				], 401);
			}

			$authenticate = [
				'vendorid'            => $vendor->vendorid,
				'accesstoken'         => (float) $vendor->accesstoken,
				'isverified'          => $vendor->isverified,
				'verificationstatus'  => $vendor->verificationstatus,
				'isprofilesubmitted'  => $vendor->isprofilecompleted,
				'isemployee'          => $vendor->isemployee,
				'isapproved'          => $vendor->isapproved,
				'isavailable'         => $vendor->isavailable,
			];

			// Time Check
			if (Carbon::now()->gt(Carbon::createFromTime(18, 0, 0))) {
				return response()->json([
					'message' => 'ATTENDANCE CAN ONLY BE RECORDED BEFORE 6 P.M. PLEASE TRY AGAIN DURING THE ALLOWED TIME FRAME.',
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate,
					'status' => 200
				], 200);
			}

			// Attendance already marked
			if ($vendor->isavailable) {
				return response()->json([
					'message' => 'ATTENDANCE IS ALREADY MARKED',
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate,
					'status' => 400
				], 400);
			}

			// Get file labelid
			$fileOption = $loginOptions->firstWhere('inputtype', 'file');
			if (!$fileOption) {
				return response()->json([
					'message' => 'NO FILE INPUT OPTION FOUND.',
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'status' => 422
				], 422);
			}
			$labelKey = "label_" . $fileOption->labelid;
			$pictureFile = $request->file($labelKey);

			if (!$pictureFile)
			{
				return response()->json([
					'message' => 'PICTURE IS REQUIRED.',
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate,
					'status' => 422
				], 422);
			}

			// Store picture
			$picturePath = $pictureFile->store('uploads/attendance', 'public');

			// Mark availability
			DB::table('vendor_tbl')
				->where('vendorid', $vendorid)
				->where('isavailable', 0)
				->update(['isavailable' => 1]);

			// Insert availability log
			DB::table('availability_tbl')->insert([
				'vendorid'       => $vendorid,
				'labelid'        => $fileOption->labelid,
				'pictureurl'     => $picturePath,
				'attendancedate' => now()->format('Y-m-d'),
				'creationdate'   => now()
			]);

			// Recheck vendor
			$updatedVendor = DB::table('vendor_tbl')
								->where('vendorid', '=', $vendorid)
								->where('accesstoken', '=', $accesstoken)
								->first();
								
			$authenticate['isavailable'] = $updatedVendor->isavailable;

			return response()->json([
				'message' => 'ATTENDANCE MARKED SUCCESSFULLY',
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate' => $authenticate,
				'status' => 200
			], 200);
		}
		catch (QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 400
			], 400);
		}

    }


    public function updateLocation(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'latitude' 		=> 'required|numeric|not_in:0',
			'longitude' 	=> 'required|numeric|not_in:0',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'latitude.required' 	=> 'LATITUDE IS REQUIRED',
			'latitude.numeric' 		=> 'LATITUDE SHOULD BE NUMERIC VALUE',
			'longitude.required' 	=> 'LONGITUDE IS REQUIRED',
			'longitude.numeric' 	=> 'LONGITUDE SHOULD BE NUMERIC VALUE',
		];

		$validatedData 	=	$request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		
		$latitude	=	$request->input('latitude');
		$longitude	=	$request->input('longitude');
        try
        {
			$isexist	=	$this->CheckVendor($vendorid, $accesstoken);
			if($isexist)
			{
				$authenticate = [
					'vendorid'            => $isexist->vendorid,
					'accesstoken'         => doubleval($isexist->accesstoken),
					'isverified'          => $isexist->isverified,
					'verificationstatus'  => $isexist->verificationstatus,
					'isprofilesubmitted'  => $isexist->isprofilecompleted,
					'isemployee'          => $isexist->isemployee,
					'isapproved'          => $isexist->isapproved,
					'isavailable'=>$isexist->isavailable,
				];
				
				DB::update('update vendor_tbl set latitude=?,longitude=? where vendorid=?',[$latitude,$longitude,$vendorid]);
				
				DB::insert('insert into location_history(vendorid,latitude,longitude,creationdate) values(?,?,?,?)',[$vendorid,$latitude,$longitude,date('Y\-m\-d H:i:s')]);
				
				return response()->json(['message' => 'LATITUDE & LONGITUDE STORED SUCCESSFULLY','vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'status' => 200],200);
			}
			else
			{
				return response()->json(['message' => __('messages.unauthorized'),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status' => 401], 401);
			}
        }
        catch(QueryException $e) 
        {
            return response()->json(['message'=>$e->getMessage(),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status'=>400], 400);
        }
    }


    public function getDashBoard(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid' 		=> 'required',
            'accesstoken' 	=> 'required',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $vendorid 		=	intval($request->header('vendorid'));
		$accesstoken 	=	$request->header('accesstoken');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

        try
        {
            $isexist = $this->CheckVendor($vendorid,$accesstoken);
			
            if($isexist)
            {

				$authenticate	=	[
					'vendorid'=>$isexist->vendorid,
					'accesstoken'=>doubleval($isexist->accesstoken),
					'isverified'=>$isexist->isverified,
					'verificationstatus'=>$isexist->verificationstatus,
					'isprofilesubmitted'=>$isexist->isprofilecompleted,
					'isemployee'=>$isexist->isemployee,
					'isapproved'=>$isexist->isapproved,
					'isavailable'=>$isexist->isavailable,
				];

				$results	=	$this->TodaysDashboard($vendorid,$isexist->isemployee);
				
			
				return response()->json(['message' =>'GO TO DASHBOARD','status'=>200,'authenticate'=>$authenticate,'data'=>$results], 200);
            }
            else
            {
				return response()->json(['message' => 'UNAUTHORIZED ACCESS','status'=>401], 401);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }
    }

    public function sendToHold(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid' 		=> 'required',
            'accesstoken' 	=> 'required',
			'detailid' 		=> 'required|numeric',
			'onholdreason' 	=> 'required|max:500',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
			'detailid.required' 		=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 			=> 'DETAIL ID SHOULD BE NUMERIC',
			'onholdreason.required' 	=> 'ON HOLD REASON IS REQUIRED',
			'onholdreason.max' 			=> 'MAX 500 CHARACTERS ALLOWED',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $vendorid 		=	intval($request->header('vendorid'));
		$accesstoken 	=	$request->header('accesstoken');
		$detailid 		=	$request->input('detailid');
		$onholdreason 	=	(String) $request->input('onholdreason');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

        try
        {
            $isexist = $this->CheckVendor($vendorid,$accesstoken);
            if($isexist)
            {

				$authenticate	=	[
					'vendorid'=>$isexist->vendorid,
					'accesstoken'=>doubleval($isexist->accesstoken),
					'isverified'=>$isexist->isverified,
					'verificationstatus'=>$isexist->verificationstatus,
					'isprofilesubmitted'=>$isexist->isprofilecompleted,
					'isemployee'=>$isexist->isemployee,
					'isapproved'=>$isexist->isapproved,
					'isavailable'=>$isexist->isavailable,
				];

				$detail	=	DB::table('customer_order_detail')
								->where('detailid','=',$detailid)
								->where('vendorid','=',$vendorid)
								->where('orderstatus','=',2)
								->first();
				if($detail)
				{
					$res	=	DB::update('update customer_order_detail set orderstatus=?,onholdreason=? where detailid=? and orderstatus=?',[4,$onholdreason,$detail->detailid,2]);
					if($res)
					{
						DB::insert('insert into onhold_history(detailid,vendorid,onholdreason,creationdate) values(?,?,?,?)',[$detail->detailid,$detail->vendorid,$onholdreason,date('Y\-m\-d H:i:s')]);
						
						DB::update('update vendor_tbl set isassigned=? where vendorid=?',[0,$detail->vendorid]);
						
						return response()->json(['message'=>'ORDER STATUS CHANGED FROM STARTED TO ON HOLD SUCCESSFULLY','status'=>200,'authenticate'=>$authenticate], 200);
					}
					else
					{
						return response()->json(['message'=>'THIS RECORD MAY HAVE ALREADY BEEN MOVED TO ON HOLD STATUS.','status'=>200,'authenticate'=>$authenticate], 200);
					}
				}
				else
				{
					return response()->json(['message'=>'INVALID DATA PROVIDED','status'=>200,'authenticate'=>$authenticate], 200);
				}
			
				
            }
            else
            {
				return response()->json(['message' => 'UNAUTHORIZED ACCESS','status'=>401], 401);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }
    }

    public function backToWork(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid' => $request->header('vendorid')
		]);		
		
        $rules = [
            'vendorid' 		=> 'required',
            'accesstoken' 	=> 'required',
			'detailid' 		=> 'required|numeric',
        ];

        $messages = [
            'vendorid.required' 		=> 'VENDOR DETAIL IS REQURIED',
			'accesstoken.required' 		=> 'ACCESS TOKEN IS REQURIED',
			'detailid.required' 		=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 			=> 'DETAIL ID SHOULD BE NUMERIC',
        ];

        $validatedData 	=	$request->validate($rules, $messages);

        $vendorid 		=	intval($request->header('vendorid'));
		$accesstoken 	=	$request->header('accesstoken');
		$detailid 		=	$request->input('detailid');

		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

        try
        {
            $isexist = $this->CheckVendor($vendorid,$accesstoken);
            if($isexist)
            {

				$authenticate	=	[
					'vendorid'=>$isexist->vendorid,
					'accesstoken'=>doubleval($isexist->accesstoken),
					'isverified'=>$isexist->isverified,
					'verificationstatus'=>$isexist->verificationstatus,
					'isprofilesubmitted'=>$isexist->isprofilecompleted,
					'isemployee'=>$isexist->isemployee,
					'isapproved'=>$isexist->isapproved,
					'isavailable'=>$isexist->isavailable,
				];

				$detail	=	DB::table('customer_order_detail')
								->where('detailid','=',$detailid)
								->where('vendorid','=',$vendorid)
								->where('orderstatus','=',4)
								->first();
				if($detail)
				{
					$reason	=	"WORK AGAIN SENT TO ON GOING PROCESS ON ".date('d\-m\-Y h:i A');
					$res	=	DB::update('update customer_order_detail set orderstatus=?,onholdreason=? where detailid=? and orderstatus=?',[2,$reason,$detail->detailid,4]);
					if($res)
					{
						DB::insert('insert into onhold_history(detailid,vendorid,onholdreason,creationdate) values(?,?,?,?)',[$detail->detailid,$detail->vendorid,$reason,date('Y\-m\-d H:i:s')]);
						
						return response()->json(['message'=>'ORDER STATUS CHANGED FROM ON HOLD TO ON GOING PROCESS SUCCESSFULLY','status'=>200,'authenticate'=>$authenticate], 200);
					}
					else
					{
						return response()->json(['message'=>'THIS RECORD MAY HAVE ALREADY BEEN MOVED TO ON GOING PROCESS STATUS.','status'=>200,'authenticate'=>$authenticate], 200);
					}
				}
				else
				{
					return response()->json(['message'=>'INVALID DATA PROVIDED','status'=>200,'authenticate'=>$authenticate], 200);
				}
			
				
            }
            else
            {
				return response()->json(['message' => 'UNAUTHORIZED ACCESS','status'=>401], 401);
            }
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 400);
        }
    }


	public function interchangeEmployee(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'detailid'				=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		$detailid   	= 	intval($request->input('detailid'));
	
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
			];
			
			if($isexist->isemployee==1)
			{
				return response()->json(['message' => 'PLEASE ASK YOUR VENDOR TO INTERCHANGE THE WORKER.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],400);
			}
			
			$currentDateTime	= 	now();
			$creationdate  		= 	$currentDateTime->format('Y-m-d H:i:s');
			
			$detail	=	DB::table('customer_order_detail')
							->where('detailid','=',$detailid)
							->whereIn('orderstatus',[1,2,4])
							->first();
			if($detail)
			{
				$categoryid	=	$detail->categoryid;
				
				/* TEMPORORY UPDATE
				$self		=	DB::table('vendor_tbl')
									->select('vendorid as employeeid','name','middlename','lastname','mobilenumber','profilepic')
									->where('vendorid','=',$vendorid)
									->where('vendorid','!=',$detail->vendorid)
									->where('isavailable','=',1)
									->where('isselfemployeed','=',1)
									->where('verificationstatus','=',1)
									->whereRaw("FIND_IN_SET(?, categoryids)", [$categoryid])
									->get();

				$employee	=	DB::table('vendor_tbl')
									->select('vendorid as employeeid','name','middlename','lastname','mobilenumber','profilepic')
									->where('parentvendorid','=',$vendorid)
									->where('vendorid','!=',$detail->vendorid)
									->where('isavailable','=',1)
									->where('verificationstatus','=',1)
									->whereRaw("FIND_IN_SET(?,employeecatids)", [$categoryid])
									->get();
				*/
				$self		=	DB::table('vendor_tbl')
									->select('vendorid as employeeid','name','middlename','lastname','mobilenumber','profilepic')
									->where('vendorid','=',$vendorid)
									->where('isavailable','=',1)
									->where('isselfemployeed','=',1)
									->where('verificationstatus','=',1)
									->whereRaw("FIND_IN_SET(?, categoryids)", [$categoryid])
									->get();

				$employee	=	DB::table('vendor_tbl')
									->select('vendorid as employeeid','name','middlename','lastname','mobilenumber','profilepic')
									->where('parentvendorid','=',$vendorid)
									->where('isavailable','=',1)
									->where('verificationstatus','=',1)
									->whereRaw("FIND_IN_SET(?,employeecatids)", [$categoryid])
									->get();
				
				$available	=	$employee->concat($self);
				foreach($available as $avail)
				{
					$appUrl 		=	Config::get('app.url');		
					
					if($avail->profilepic)
					{
						$avail->profilepic = $appUrl."/storage/".$avail->profilepic;
					}				
				}
				
				return response()->json(['message' => 'VALID DATA.','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>$available],200);
			}
			else
			{
				return response()->json(['message' => 'INVALID DETAIL ID PROVIDED.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],400);
			}
			
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
	}

	public function confirmInterchange(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'detailid'				=>	'required|numeric',
			'employeeid'			=>	'required|numeric',
			'interchangereason'		=>	'required|max:500',
        ];

        $messages = [
			'vendorid.required' 		=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 		=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 		=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 			=> 'DETAIL ID SHOULD BE NUMERIC',
			'employeeid.required' 		=> 'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric' 		=> 'EMPLOYEE ID SHOULD BE NUMERIC',
			'interchangereason.required'=> 'INTERCHANGE REASON IS REQUIRED',
			'interchangereason.max'		=> 'MAXIMUM 500 CHARACTERS ALLOWED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   		= 	intval($request->header('vendorid'));
		$accesstoken   		= 	(String) $request->header('accesstoken');
		$detailid   		= 	intval($request->input('detailid'));
		$employeeid   		= 	intval($request->input('employeeid'));
		$interchangereason  = 	(String) $request->input('interchangereason');
	
		$isexist			=	$this->CheckVendor($vendorid,$accesstoken);
		
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
			];
			
			$currentDateTime	= 	now();
			$creationdate  		= 	$currentDateTime->format('Y-m-d H:i:s');
			
			/* TEMPRORY UPDATE
			$detail	=	DB::table('customer_order_detail')
							->where('detailid','=',$detailid)
							->where('vendorid','!=',$employeeid)
							->whereIn('orderstatus',[1,2,4])
							->first();
			*/
			$detail	=	DB::table('customer_order_detail')
							->where('detailid','=',$detailid)
							->whereIn('orderstatus',[1,2,4])
							->first();
			
			if($detail)
			{
				$vendorname	=	DB::table('vendor_tbl')->where('vendorid','=',$employeeid)->value('name');
				
				$res	=	DB::update('update customer_order_detail set vendorid=?,vendorname=?,interchangereason=?,interchangedate=?,isinterchanged=? where detailid=? and vendorid!=?',[$employeeid,$vendorname,$interchangereason,date('Y\-m\-d H:i:s'),1,$detailid,$employeeid]);
				
				if($res)
				{
					DB::insert('insert into interchange_history(detailid,fromvendorid,tovendorid,interchangereason,interchangedate) values(?,?,?,?,?)',[$detailid,$detail->vendorid,$employeeid,$interchangereason,date('Y\-m\-d H:i:s')]);
					
					return response()->json(['message' => 'INTERCHANGE CONFIRMED.','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],200);
				}
				else
				{
					return response()->json(['message' => 'THE INTERCHANGE FOR THIS RECORD HAS ALREADY BEEN COMPLETED. PLEASE CHECK AND TRY AGAIN!','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],400);
				}
			}
			else
			{
				return response()->json(['message' => 'INVALID DETAIL ID PROVIDED.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
	}

	public function setEmployeeStatus(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'employeeid'	=>	'required|numeric',
			'isactive'		=>	'required|boolean',
        ];

        $messages = [
			'vendorid.required' 	=>	'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=>	'ACCESSTOKEN IS REQUIRED',
			'employeeid.required' 	=>	'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric' 	=>	'EMPLOYEE ID SHOULD BE NUMERIC',
			'isactive.required'		=>	'IS ACTIVE IS REQUIRED',
			'isactive.boolean'		=>	'ISACTIVE FIELD MUST BE TRUE OR FALSE',
        ];
		
        $validated 	 =	$request->validate($rules,$messages);
		$vendorid    =	(int) $validated['vendorid'];
		$accesstoken =	(string) $validated['accesstoken'];
		$employeeid  =	(int) $validated['employeeid'];
		$isactive    =	(bool) $validated['isactive'];		
		
		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if (!$vendor) {
				return response()->json([
					'message'    => __('messages.unauthorized'),
					'status'     => 401,
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken
				], 401);
			}

			$authenticate = [
				'vendorid'            => $vendor->vendorid,
				'accesstoken'         => $accesstoken,
				'isverified'          => $vendor->isverified,
				'verificationstatus'  => $vendor->verificationstatus,
				'isprofilesubmitted'  => $vendor->isprofilecompleted,
				'isemployee'          => $vendor->isemployee,
				'isapproved'          => $vendor->isapproved,
			];

			$employee = DB::table('vendor_tbl')
				->where('parentvendorid', $vendorid)
				->where('vendorid', $employeeid)
				->where('isemployee', 1)
				->first();

			if (!$employee) {
				return response()->json([
					'message'     => 'INVALID EMPLOYEE DETAIL PROVIDED.',
					'status'      => 400,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate
				], 400);
			}

			DB::table('vendor_tbl')
				->where('vendorid', $employeeid)
				->update(['isactive' => $isactive]);

			return response()->json([
				'message'     => 'EMPLOYEE STATUS UPDATED SUCCESSFULLY.',
				'status'      => 200,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message' => 'We were unable to update the employee`s status at the moment. Please try again shortly or contact support if the issue continues.',
				'status'  => 500
			], 500);
		}
	}

	public function myNotifications(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$vendorid   = 	intval($request->header('vendorid'));
		$accesstoken= 	(String) $request->header('accesstoken');
		$page  		= 	intval($request->input('page',1));
		$pagelimit  = 	intval($request->input('pagelimit',10));

		$vendor = $this->CheckVendor($vendorid, $accesstoken);

		if(!$vendor)
		{
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken
			], 401);
		}
		$notifications = DB::table('vendor_notification as a')
			->select('a.title', 'a.notification', 'a.lastnotificationtime as notificationtime')
			->where('a.vendorid', $vendorid)
			->orderByDesc('a.lastnotificationtime')
			->paginate($pagelimit, ['*'], 'page', $page);
			
		$notifications->getCollection()->transform(function ($not) {
			$not->notificationtime = date('d-m-Y h:i A', strtotime($not->notificationtime));
			return $not;
		});
		
		return response()->json([
			'message'     => 'MY NOTIFICATION LIST',
			'status'      => 200,
			'vendorid'    => $vendorid,
			'accesstoken' => $accesstoken,
			'totalpages'  => $notifications->lastPage(),
			'data'        => $notifications->items(),
		], 200);

	}

	public function acceptDuration(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$vendorid   = 	intval($request->header('vendorid'));
		$accesstoken= 	(String) $request->header('accesstoken');

		$isexist = $this->CheckVendor($vendorid, $accesstoken);

		if(!$isexist)
		{
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
			], 401);
		}

		$duration = Cache::remember('task_accept_duration_record_1', 3600, function () {
			return DB::table('task_accept_duration')
				->select('acceptduration')
				->where('recordid', 1)
				->first(); // use ->first() instead of ->get() since we expect only 1 record
		});
		$durationInSeconds = $duration ? $duration->acceptduration * 60 : 0;

		return response()->json([
			'message'     => 'ACCEPT DURATION',
			'status'      => 200,
			'vendorid'    => $vendorid,
			'accesstoken' => $accesstoken,
			'data'        => ['acceptduration' => $durationInSeconds],
		], 200);

	}

	public function bookingHistory(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$vendorid    = (int) $request->header('vendorid');
		$accesstoken = (string) $request->header('accesstoken');
		$page        = (int) $request->input('page', 1);
		$pagelimit   = (int) $request->input('pagelimit', 10);
		
		$isexist = $this->CheckVendor($vendorid, $accesstoken);

		if(!$isexist)
		{
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken
			], 401);
		}		
		$authenticate = [
			'vendorid'            => $isexist->vendorid,
			'accesstoken'         => doubleval($isexist->accesstoken),
			'isverified'          => $isexist->isverified,
			'verificationstatus'  => $isexist->verificationstatus,
			'isprofilesubmitted'  => $isexist->isprofilecompleted,
			'isemployee'          => $isexist->isemployee,
			'isapproved'          => $isexist->isapproved,
		];
		$vendorIds = DB::table('vendor_tbl')
			->where('vendorid', $vendorid)
			->orWhere('parentvendorid', $vendorid)
			->pluck('vendorid')
			->toArray();

		$results = DB::table('customer_order_detail as a')
			->join('services as s', function ($join) {
				$join->on('a.serviceid', '=', 's.serviceid')
					 ->on('a.optionid', '=', 's.optionid');
			})
			->join('vendor_tbl as v', 'a.vendorid', '=', 'v.vendorid')
			->join('customer_tbl as cust', 'cust.customerid', '=', 'a.customerid')
			->join('customer_address as ad', 'ad.addressid', '=', 'a.addressid')
			->select(
				'a.vendorid as employeeid',
				'v.name as vendorname',
				'v.mobilenumber as vendorcontact',
				'v.ratings',
				'v.reviews',
				'v.profilepic as vendorprofilepic',
				'a.servicedate',
				'a.orderstatus',
				'a.orderid'
			)
			->whereIn('a.vendorid', $vendorIds)
			->where('a.orderstatus', '!=', 0)
			->groupBy('a.vendorid', 'a.orderid')
			->havingRaw("COUNT(*) = (
				SELECT COUNT(*) FROM customer_order_detail as b
				WHERE b.orderid = a.orderid AND b.vendorid = a.vendorid AND b.orderstatus != 0
			)")
			->paginate($pagelimit, ['*'], 'page', $page);
		
		$orderVendorPairs = [];
		foreach($results as $result)
		{
			if (!empty($result->vendorprofilepic))
			{
				$result->vendorprofilepic = $this->appUrl . "/storage/" . $result->vendorprofilepic;
			}
			$orderVendorPairs[] = ['orderid' => $result->orderid, 'vendorid' => $result->employeeid];
		}
		$services = DB::table('customer_order_detail as a')
			->select('a.detailid', 'b.servicetitle', 'a.orderstatus', 'a.orderid', 'a.vendorid')
			->leftJoin('services as b', function ($join) {
				$join->on('b.serviceid', '=', 'a.serviceid')
					 ->on('b.optionid', '=', 'a.optionid');
			})
			->where(function ($query) use ($orderVendorPairs) {
				foreach ($orderVendorPairs as $pair) {
					$query->orWhere(function ($q) use ($pair) {
						$q->where('a.orderid', $pair['orderid'])
						  ->where('a.vendorid', $pair['vendorid']);
					});
				}
			})
			->get()
			->groupBy(fn($item) => $item->orderid . '_' . $item->vendorid);
		
		foreach ($results as $result)
		{
			$key = $result->orderid . '_' . $result->employeeid;
			$result->services = $services[$key] ?? [];
			unset($result->orderstatus);
		}

		return response()->json([
			'message'     => 'BOOKING HISTORY',
			'status'      => 200,
			'vendorid'    => $vendorid,
			'accesstoken' => $accesstoken,
			'totalpages'  => $results->lastPage(),
			'data'        => $results->items(),
		], 200);


		/*
		$isexist	=	$this->CheckVendor($vendorid,$accesstoken);
		
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
			];
			$appUrl 	=	Config::get('app.url');
			$vendorIds	=	DB::table('vendor_tbl')
							->where('vendorid',$vendorid)
							->orWhere('parentvendorid',$vendorid)
							->pluck('vendorid')
							->toArray();
			
			$results = DB::table('customer_order_detail as a')
						->join('services as s', function ($join) {
							$join->on('a.serviceid','=','s.serviceid')
								 ->on('a.optionid','=','s.optionid');
						})
						->join('vendor_tbl as v','a.vendorid','=','v.vendorid')
						->join('customer_tbl as cust','cust.customerid','=','a.customerid')
						->join('customer_address as ad','ad.addressid','=','a.addressid')
						->select(
							'a.vendorid as employeeid',
							'v.name as vendorname',
							'v.mobilenumber as vendorcontact',
							'v.ratings',
							'v.reviews',
							'v.profilepic as vendorprofilepic',
							'a.servicedate',
							'a.orderstatus',
							'a.orderid',
						)
						->whereIn('a.vendorid',$vendorIds)
						->where('a.orderstatus','!=',0)
						->groupBy('a.vendorid','a.orderid')
						->havingRaw("COUNT(*) = (
							SELECT COUNT(*) FROM customer_order_detail as b
							WHERE b.orderid = a.orderid	AND b.vendorid = a.vendorid and b.orderstatus!=0)")
						->paginate($pagelimit,['*'],'page',$page);
			
			foreach($results as $result)
			{
				if($result->vendorprofilepic!='')
				{
					$result->vendorprofilepic = $appUrl."/storage/".$result->vendorprofilepic;					
				}
				$result->services	=	DB::table('customer_order_detail as a')
											->select('a.detailid','b.servicetitle','a.orderstatus')
											->leftJoin('services as b', function ($join) {
												$join->on('b.serviceid', '=', 'a.serviceid')
													->on('b.optionid', '=', 'a.optionid');
											})
											->where('a.orderid','=',$result->orderid)
											->where('a.vendorid','=',$result->employeeid)
											->get();
				unset($result->orderstatus);
			}
			
			$totalpages	=	$results->lastPage();
			
			return response()->json(['message'=>'BOOKING HISTORY','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'totalpages'=>$totalpages,'data'=>$results->items()], 200);
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
		*/
	}


	public function getBookingDetail(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'detailid'				=>	'required|numeric',
			'employeeid'			=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
			'employeeid.required' 	=> 'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric' 	=> 'EMPLOYEE ID SHOULD BE NUMERIC',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$vendorid   = (int) $request->vendorid;
		$accesstoken= (string) $request->accesstoken;
		$detailid   = (int) $request->detailid;
		$employeeid = (int) $request->employeeid;

		$isexist = $this->CheckVendor($vendorid, $accesstoken);

		if (!$isexist) {
			return response()->json([
				'message'     => __('messages.unauthorized'),
				'status'      => 401,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken
			], 401);
		}

		$authenticate = [
			'vendorid'           => $isexist->vendorid,
			'accesstoken'        => (float) $isexist->accesstoken,
			'isverified'         => $isexist->isverified,
			'verificationstatus' => $isexist->verificationstatus,
			'isprofilesubmitted' => $isexist->isprofilecompleted,
			'isemployee'         => $isexist->isemployee,
			'isapproved'         => $isexist->isapproved,
		];

		try
		{
			$employee = DB::table('vendor_tbl')->where('vendorid', $employeeid)->first();

			if (!$employee) {
				return response()->json([
					'message'     => 'INVALID EMPLOYEE ID',
					'status'      => 401,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			// Optional: Verify vendor-employee relationship
			if ($employee->isemployee == 1) {
				$parentid = $employee->parentvendorid ?? null;
				if ($vendorid !== (int) $parentid)
				{
					return response()->json([
						'message'     => 'INVALID VENDOR AND EMPLOYEE RELATION',
						'status'      => 400,
						'vendorid'    => $vendorid,
						'accesstoken' => $accesstoken,
						'authenticate'=> $authenticate
					], 400);

				}
			}

			$detail = DB::table('customer_order_detail as a')
				->select(
					'a.detailid','a.orderid','a.serviceid','a.optionid','a.quantity','a.servicecharge','a.taxable',
					'a.payable','a.paid','a.assignedtime','a.workstarttime','a.workendtime','a.servicedate','a.slottime',
					'a.creationdate','b.vendorid as employeeid','b.name as vendorfirstname','b.middlename as vendormiddlename',
					'b.lastname as vendorlastname','b.ratings','b.reviews','b.mobilenumber as vendorcontact','b.email as vendoremail',
					'b.completeaddress as vendoraddress','b.profilepic as vendorprofilepic','c.servicetitle','d.customerid',
					'd.name as customerfirstname','d.middlename as customermiddlename','d.lastname as customerlastname',
					'd.mobilenumber as customercontact','d.email as customeremail','d.profilepic as customerprofilepic',
					'e.address as customeraddress','e.latitude as customerlatitude','e.longitude as customerlongitude'
				)
				->leftJoin('vendor_tbl as b', 'b.vendorid', '=', 'a.vendorid')
				->leftJoin('services as c', function ($join) {
					$join->on('c.serviceid', '=', 'a.serviceid')->on('c.optionid', '=', 'a.optionid');
				})
				->join('customer_tbl as d', 'd.customerid', '=', 'a.customerid')
				->join('customer_address as e', 'e.addressid', '=', 'e.addressid')
				->where([
					['a.detailid', '=', $detailid],
					['a.vendorid', '=', $employeeid]
				])
				->first();

			if (!$detail) {
				return response()->json([
					'message'     => 'INVALID DATA',
					'status'      => 401,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			if (!empty($detail->vendorprofilepic)) {
				$detail->vendorprofilepic = $this->appUrl . '/storage/' . $detail->vendorprofilepic;
			}
			if (!empty($detail->customerprofilepic)) {
				$detail->customerprofilepic = $this->appUrl . '/storage/' . $detail->customerprofilepic;
			}

			return response()->json([
				'message'     => 'WORK DETAIL',
				'status'      => 200,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate,
				'data'        => $detail
			], 200);

		}
		catch (\Exception $e)
		{
			return response()->json([
				'message'     => $e->getMessage(),
				'status'      => 500,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken
			], 500);
		}
	}


	public function getJobHistory(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid'),
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'detailid'				=>	'required|numeric',
			'employeeid'			=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
			'employeeid.required' 	=> 'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric' 	=> 'EMPLOYEE ID SHOULD BE NUMERIC',
        ];
		
        $validatedData 		=	$request->validate($rules, $messages);
		
		$vendorid     = intval($request->vendorid);
		$accesstoken  = (string) $request->accesstoken;
		$detailid     = intval($request->detailid);
		$employeeid   = intval($request->employeeid);

		$isexist = $this->CheckVendor($vendorid, $accesstoken);

		if (!$isexist) {
			return response()->json([
				'message'    => __('messages.unauthorized'),
				'status'     => 401,
				'vendorid'   => $vendorid,
				'accesstoken'=> $accesstoken
			], 401);
		}

		$authenticate = [
			'vendorid'           => $isexist->vendorid,
			'accesstoken'        => doubleval($isexist->accesstoken),
			'isverified'         => $isexist->isverified,
			'verificationstatus' => $isexist->verificationstatus,
			'isprofilesubmitted' => $isexist->isprofilecompleted,
			'isemployee'         => $isexist->isemployee,
			'isapproved'         => $isexist->isapproved,
		];

		try
		{
			$employee = DB::table('vendor_tbl')
				->select('vendorid', 'isemployee', 'parentvendorid')
				->where('vendorid', $employeeid)
				->first();

			if (!$employee)
			{
				return response()->json([
					'message' => 'INVALID EMPLOYEE ID',
					'status'  => 401,
					'vendorid'=> $vendorid,
					'accesstoken' => $accesstoken
				], 401);
			}

			if ($employee->isemployee == 1 && $vendorid != $employee->parentvendorid) {
				return response()->json(array_merge(['message' => 'INVALID VENDOR AND EMPLOYEE RELATION', 'status' => 400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate]), 400);
			}

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
					['a.detailid', '=', $detailid],
					['a.vendorid', '=', $employeeid],
				])
				->first();

			if (!$detail)
			{
				return response()->json([
					'message'    => 'INVALID DATA',
					'status'     => 401,
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken
				], 401);
			}

			
			if (!empty($detail->onholdreason))
			{
				$detail->onholddate = DB::table('onhold_history')
					->where('detailid', $detailid)
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
					->where('a.detailid', $detailid)
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

			return response()->json(['message'=> 'WORK DETAIL','status'=> 200,'vendorid'=> $vendorid,'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate,'data'=> $detail], 200);
		}
		catch (QueryException $e)
		{
			return response()->json(['message'=> $e->getMessage(),'status'=> 500,'vendorid'=> $vendorid,'accesstoken' => $accesstoken
			],500);
		}

	}



    public function getBookingPicture(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'    	=> 'required|numeric',
			'employeeid'   	=> 'required|numeric',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required'    	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric'     	=> 'DETAIL ID SHOULD BE NUMERIC',
			'employeeid.required'    => 'EMPLOYEE ID IS REQUIRED',
			'employeeid.numeric'     => 'EMPLOYEE ID SHOULD BE NUMERIC',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailid   	=	intval($request->input('detailid'));
		$employeeid   	=	intval($request->input('employeeid'));

		try
		{
			$vendor = $this->CheckVendor($vendorid, $accesstoken);

			if(!$vendor)
			{
				return response()->json(['message'=> __('messages.unauthorized'),'vendorid'=> $vendorid,'accesstoken' => $accesstoken,'status'=> 401], 401);
			}

			$authenticate = [
				'vendorid'           => $vendor->vendorid,
				'accesstoken'        => (double) $vendor->accesstoken,
				'isverified'         => $vendor->isverified,
				'verificationstatus' => $vendor->verificationstatus,
				'isprofilesubmitted' => $vendor->isprofilecompleted,
				'isemployee'         => $vendor->isemployee,
				'isapproved'         => $vendor->isapproved,
				'isavailable'        => $vendor->isavailable,
			];

			$detail = DB::table('customer_order_detail as a')
				->select('a.*', 'b.startingimages', 'b.finishingimages')
				->leftJoin('services as b', 'b.serviceid', '=', 'a.serviceid')
				->where('a.detailid', $detailid)
				->where('a.vendorid', $employeeid)
				->where('a.ispinverified', '!=', 0)
				->first();

			if (!$detail) {
				return response()->json([
					'message'     => 'INVALID ATTEMPT',
					'status'      => 400,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate
				], 400);
			}

			$appUrl = Config::get('app.url');

			$getPictures = function ($imageString, $time) use ($detailid, $appUrl) {
				$labelIds = array_filter(explode(',', $imageString));
				if (empty($labelIds)) return [];

				return DB::table('service_photo_labels as a')
					->leftJoin('service_photos as b', function ($join) use ($detailid, $time) {
						$join->on('a.labelid', '=', 'b.labelid')
							 ->where('b.detailid', '=', $detailid)
							 ->where('b.picturetime', '=', $time);
					})
					->whereIn('a.labelid', $labelIds)
					->select('a.labelid', 'a.labelvalue', DB::raw("COALESCE(b.pictureurl,'') as pictureurl"))
					->get()
					->map(function ($item) use ($appUrl) {
						$item->pictureurl = $item->pictureurl ? "{$appUrl}/storage/{$item->pictureurl}" : '';
						return $item;
					});
			};

			$startingpictures  = $getPictures($detail->startingimages, 'STARTING');
			$finishingpictures = $getPictures($detail->finishingimages, 'FINISHING');

			return response()->json([
				'message'     => 'PICTURES TAKEN',
				'status'      => 200,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate,
				'data'        => [
					'startpictures'    => $detail->startpictures,
					'finishpictures'   => $detail->finishpictures,
					'takestartimages'  => $startingpictures,
					'takefinishimages' => $finishingpictures,
				]
			], 200);

		}
		catch(QueryException $e)
		{
			return response()->json([
				'message'     => $e->getMessage(),
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'status'      => 400
			], 400);
		}
    }

    public function earningHistory(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$page 			=	(int) $request->input('page',1);
		$pagelimit 		=	(int) $request->input('pagelimit',10);		
	
        try
        {
			$appUrl	=	Config::get('app.url');
			
			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if($isexist)
			{
				$authenticate = [
					'vendorid'            => $isexist->vendorid,
					'accesstoken'         => doubleval($isexist->accesstoken),
					'isverified'          => $isexist->isverified,
					'verificationstatus'  => $isexist->verificationstatus,
					'isprofilesubmitted'  => $isexist->isprofilecompleted,
					'isemployee'          => $isexist->isemployee,
					'isapproved'          => $isexist->isapproved,
					'isavailable'		  => $isexist->isavailable,
				];
				
				$vendorIds	=	DB::table('vendor_tbl')
									->where('vendorid', $vendorid)
									->orWhere('parentvendorid', $vendorid)
									->pluck('vendorid')
									->toArray();
				
				
				$jobs		=	DB::table('customer_order_detail as a')
										->select('a.detailid','a.orderid','a.orderstatus','e.servicetitle','a.servicecharge','a.taxable','a.commission','a.creationdate','b.name as customername','b.middlename as customermiddlename','b.lastname as customerlastname','b.email as customeremail','b.completeaddress as customeraddress','b.profilepic as customerprofilepic','d.name as employeename','d.middlename as employeemiddlename','d.lastname as employeelastname','d.profilepic as employeeprofilepic','c.receiptnumber','c.financialyear','c.receiptnumber','c.paymentdatetime','c.dramount')
										->leftjoin('customer_tbl as b','b.customerid','=','a.customerid')
										->join('receipt_tbl as c','c.detailid','=','a.detailid')
										->leftjoin('vendor_tbl as d','d.vendorid','=','a.vendorid')
										->leftJoin('services as e', function ($join) {
											$join->on('e.serviceid','=','a.serviceid')->on('e.optionid','=','a.optionid');
										})
										->whereIn('a.vendorid',$vendorIds)
										->where('c.dramount','!=',0)
										->paginate($pagelimit, ['*'], 'page', $page);
				foreach($jobs as $job)
				{
					if($job->customerprofilepic!='')
					{
						$job->customerprofilepic = $appUrl."/storage/".$job->customerprofilepic;
					}
					if($job->employeeprofilepic!='')
					{
						$job->employeeprofilepic = $appUrl."/storage/".$job->employeeprofilepic;
					}
					$job->earning	=	round($job->taxable-$job->dramount,2);
				}
				
				
				
				return response()->json(['message'=>'MY EARNINGS','status'=>200,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'data'=>$jobs->items(),'totalpages'=>$jobs->lastPage()],200);
			}
			else
			{
				return response()->json(['message' => __('messages.unauthorized'),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status' => 401], 401);
			}
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status'=>400], 400);
        }
    }


    public function getPendingPaymentServices(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'		=> 'required|numeric'
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.numeric' 		=> 'DETAIL ID SHOULD BE NUMERIC',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   =	intval($request->header('vendorid'));
		$accesstoken=	(string) $request->header('accesstoken');
		$detailid   =	intval($request->input('detailid'));


		try
		{
			$isexist = $this->CheckVendor($vendorid, $accesstoken);
			if(!$isexist)
			{
				return response()->json([
					'message'    => __('messages.unauthorized'),
					'vendorid'   => $vendorid,
					'accesstoken'=> $accesstoken,
					'status'     => 401
				], 401);
			}

			$authenticate = [
				'vendorid'           => $isexist->vendorid,
				'accesstoken'        => $isexist->accesstoken,
				'isverified'         => $isexist->isverified,
				'verificationstatus' => $isexist->verificationstatus,
				'isprofilesubmitted' => $isexist->isprofilecompleted,
				'isemployee'         => $isexist->isemployee,
				'isapproved'         => $isexist->isapproved,
				'isavailable'        => $isexist->isavailable,
			];

			$detail = DB::table('customer_order_detail as a')
						->where('a.detailid', $detailid)
						->where('a.vendorid', $vendorid)
						->whereIn('a.orderstatus', [2, 4])
						->first();

			if(!$detail)
			{
				return response()->json([
					'message'     => 'VENDOR ID IS NOT ASSOCIATED WITH THIS ORDER DETAIL ID.',
					'status'      => 400,
					'vendorid'    => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate'=> $authenticate
				], 400);
			}

			$pendings = DB::table('customer_order_detail as a')
						->select('a.detailid', 'b.servicetitle', 'a.payable')
						->leftJoin('services as b', function ($join) {
							$join->on('b.serviceid', '=', 'a.serviceid')
								 ->on('b.optionid', '=', 'a.optionid');
						})
						->where([
							['a.vendorid', '=', $vendorid],
							['a.customerid', '=', $detail->customerid],
							['a.paymentstatus', '=', 0],
							['a.paid', '=', 0],
						])
						->get();

			return response()->json([
				'message'     => 'UNPAID SERVICE LIST',
				'status'      => 200,
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'authenticate'=> $authenticate,
				'data'        => $pendings
			], 200);

		}
		catch (QueryException $e)
		{
			return response()->json([
				'message'     => 'Something went wrong while retrieving the pending payment services. Please try again later.',
				'vendorid'    => $vendorid,
				'accesstoken' => $accesstoken,
				'status'      => 400
			], 400);
		}
    }

    public function initiatePayment(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'isqrpayment' 	=> 'required',
			'detailid'		=> 'required|array|min:1'
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'isqrpayment.required' 	=> 'PLEASE PROVIDE PAYMENT METHOD',
			'isqrpayment.in' 		=> 'INVALID PAYMENT METHOD',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.array'        => 'DETAIL IDs MUST BE AN ARRAY',
			'detailid.min'        	=> 'MINIMUM 1 DETAIL ID IS REQUIRED',
		];

		$validatedData 	=	$request->validate($rules, $messages);

		$vendorid   	=	intval($request->header('vendorid'));
		$accesstoken	=	(string) $request->header('accesstoken');
		$detailids   	=	$request->input('detailid');
		$isqrpayment   	=	$request->input('isqrpayment');

		try
		{
			$isexist = $this->CheckVendor($vendorid,$accesstoken);

			if(!$isexist)
			{
				return response()->json([
					'message' => __('messages.unauthorized'),
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'status' => 401
				], 401);
			}

			$authenticate = [
				'vendorid'           => $isexist->vendorid,
				'accesstoken'        => doubleval($isexist->accesstoken),
				'isverified'         => $isexist->isverified,
				'verificationstatus' => $isexist->verificationstatus,
				'isprofilesubmitted' => $isexist->isprofilecompleted,
				'isemployee'         => $isexist->isemployee,
				'isapproved'         => $isexist->isapproved,
				'isavailable'        => $isexist->isavailable,
			];

			// Check vendorid for all detailids in one query
			$mismatched = DB::table('customer_order_detail')
				->whereIn('detailid', $detailids)
				->where('vendorid', '!=', $vendorid)
				->exists();

			if($mismatched)
			{
				return response()->json([
					'message' => 'INVALID ORDER AND VENDOR MATCHING.',
					'status' => 400,
					'vendorid' => $vendorid,
					'accesstoken' => $accesstoken,
					'authenticate' => $authenticate,
				], 400);
			}

			// Get distinct customer IDs count for these detailids
			$distinctCustomerIds = DB::table('customer_order_detail')
				->whereIn('detailid', $detailids)
				->distinct()
				->pluck('customerid');

			if($distinctCustomerIds->count()!==1)
			{
				return response()->json([
					'message' 		=> 'THE PROVIDED DETAIL IDS ARE ASSOCIATED WITH DIFFERENT CUSTOMERS. PLEASE ENSURE ALL DETAIL IDS BELONG TO THE SAME CUSTOMER AND TRY AGAIN.',
					'status' 		=> 400,
					'vendorid' 		=> $vendorid,
					'accesstoken' 	=> $accesstoken,
					'authenticate' 	=> $authenticate,
					'customerCount' => $distinctCustomerIds->count(),
				], 400);
			}

			// Check if any detailid is already paid or cancelled
			$exists = DB::table('customer_order_detail')
				->whereIn('detailid', $detailids)
				->where(function ($query) {
					$query->where('paymentstatus', 1)
						->orWhere('iscancelled', 1);
				})->exists();

			if($exists)
			{
				return response()->json([
					'message' => 'PAYMENT HAS ALREADY BEEN DONE OR ORDER CANCELLED FOR ONE OF THE DETAIL IDS. PLEASE CHECK AND TRY AGAIN!',
					'status' => 400,
					'authenticate' => $authenticate,
				], 400);
			}

			// Lock details inside a DB transaction
			DB::transaction(function () use ($detailids, $vendorid, $authenticate) {
				DB::table('customer_order_detail')
					->whereIn('detailid', $detailids)
					->update(['islocked' => 1]);
			});

			// Fetch distinct customerid and orderid once
			$distinctCustomerId = $distinctCustomerIds->first();
			$distinctOrderId = DB::table('customer_order_detail')
				->whereIn('detailid', $detailids)
				->distinct()
				->pluck('orderid')
				->first();

			// Fetch pending orders with service titles and payable amounts
			$pendings = DB::table('customer_order_detail as a')
				->select('a.detailid', 'a.orderid', 'b.servicetitle', 'a.servicecharge', 'a.taxable', 'a.taxvalue', 'a.payable')
				->leftJoin('services as b', function ($join) {
					$join->on('b.serviceid', '=', 'a.serviceid')
						->on('b.optionid', '=', 'a.optionid');
				})
				->whereIn('a.detailid', $detailids)
				->where('a.customerid', $distinctCustomerId)
				->where('a.orderid', $distinctOrderId)
				->where('a.paymentstatus', 0)
				->where('a.paid', 0)
				->get();

			// Calculate totals
			$totalservicecharge = 0;
			$totaltaxable = 0;
			$totaltaxvalue = 0;
			$netpayable = 0;

			foreach ($pendings as $pending) {
				$totalservicecharge += round($pending->servicecharge, 2);
				$totaltaxable += round($pending->taxable, 2);
				$totaltaxvalue += round($pending->taxvalue, 2);
				$netpayable += round($pending->payable, 2);
			}

			// Fetch customer data
			$customer = DB::table('customer_tbl')->where('customerid', $distinctCustomerId)->first();

			$detailidsStr = implode(',', $detailids);
			$receipt = "RCPT" . rand(100, 999) . now()->timestamp;

			$name = trim(implode(' ', array_filter([$customer->name, $customer->middlename, $customer->lastname])));
			//$netpayable=1;
			// Create payment link
			if($isqrpayment==1)
			{
				$paymentLink = $this->razorpay->paymentLink->create([
					'amount' => $netpayable * 100,
					'currency' => 'INR',
					'accept_partial' => false,
					'description' => 'UNPAID SERVICE PAYMENT',
					'customer' => [
						'name' => $name ?? '',
						'contact' => $customer->mobilenumber ?? '',
						'email' => $customer->email ?? '',
					],
					'notify' => [
						'sms' => false,
						'email' => false,
					],
				]);
			}
			else
			{
				$paymentLink = $this->razorpay->paymentLink->create([
					'amount' 		=> $netpayable * 100,
					'currency' 		=> 'INR',
					'accept_partial'=> false,
					'description' 	=> 'UNPAID SERVICE PAYMENT',
					'customer' => [
						'name' 		=> $name ?? '',
						'contact' 	=> $customer->mobilenumber ?? '',
						'email' 	=> $customer->email ?? '',
					],
					'notify'=>[
						'sms'=>true,
						'email'=>false,
					],
				]);
				
			}

			$paymentdate 	=	now()->format('Y-m-d');
			$financialyear 	=	$this->resourceController->GetFinancialYear($paymentdate);

			DB::transaction(function () use ($vendorid, $distinctCustomerId, $distinctOrderId,$receipt,$paymentLink,$netpayable, $detailidsStr,$financialyear) {
				DB::delete("DELETE FROM receipt_tbl WHERE vendorid = ? AND customerid = ? AND orderid = ? AND paymentstatus = ?", [
					$vendorid,
					$distinctCustomerId,
					$distinctOrderId,
					'pending',
				]);

				DB::table('receipt_tbl')->insert([
					'financialyear'   => $financialyear,
					'customerid'      => $distinctCustomerId,
					'orderid'         => $distinctOrderId,
					'receiptnumber'   => $receipt,
					'vendorid'        => $vendorid,
					'payment_link_id' => $paymentLink['id'],
					'netamount'       => $netpayable,
					'paymentstatus'   => 'pending',
					'paymentdatetime' => now()->format('Y-m-d H:i:s'),
					'generateddate'   => now()->format('Y-m-d H:i:s'),
					'detailids'       => $detailidsStr,
					'isunpaidservice' => 1,
				]);
			});

			// Uncomment if SMS notification needed:
			/*
			$servicetitles = DB::table('customer_order_detail as cod')
				->join('service_tbl as s', 'cod.serviceid', '=', 's.serviceid')
				->whereIn('cod.detailid', $detailids)
				->select(DB::raw("GROUP_CONCAT(s.servicetitle ORDER BY s.servicetitle SEPARATOR ', ') as servicetitles"))
				->value('servicetitles');

			$this->smsService->pushPaymentMessage($customer->mobilenumber, $paymentLink['short_url'], $netpayable, $servicetitles);
			*/
			
			if($isqrpayment==1)
			{
				return response()->json([
					'message' 		=> 'PAYMENT LINK & QR CODE GENERATED SUCCESSFULLY.',
					'status' 		=> 200,
					'vendorid' 		=> $vendorid,
					'accesstoken' 	=> $accesstoken,
					'authenticate'	=> $authenticate,
					'data' => [
						'paymentlinkid' => $paymentLink['id'],
						'qrcodeurl' 	=> $paymentLink['short_url'],
					],
				], 200);
			}
			else
			{
				return response()->json([
					'message' 		=> 'PAYMENT LINK SENT SUCCESSFULLY.',
					'status' 		=> 200,
					'vendorid' 		=> $vendorid,
					'accesstoken' 	=> $accesstoken,
					'authenticate' 	=> $authenticate,
					'data' 			=> [],
				], 200);				
			}
		}
		catch(QueryException $e)
		{
			return response()->json([
				'message' => $e->getMessage(),
				'vendorid' => $vendorid,
				'accesstoken' => $accesstoken,
				'status' => 400,
			], 400);
		}

    }


    public function createOrder(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'		=>	'required',
			'accesstoken'	=>	'required',
			'amount'		=>	'required|numeric',
			'detailid'		=> 'required|array|min:1'
        ];

        $messages = [
			'vendorid.required' 	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'amount.required' 		=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 		=> 'AMOUNT MUST BE NUMERIC VALUE',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.array'        => 'DETAIL IDs MUST BE AN ARRAY',
			'detailid.min'        	=> 'MINIMUM 1 DETAIL ID IS REQUIRED',
        ];
		
        $validatedData 	=	$request->validate($rules,$messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		$amount   		= 	doubleval($request->input('amount'));
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
				'isavailable'		=>	$isexist->isavailable,
			];
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$paymentdate	=	date('Y\-m\-d',strtotime($creationdate));
			
			$detailid		=	$request->input('detailid');
			$exists		=	DB::table('customer_order_detail')
								->whereIn('detailid',$detailid)
								->where('paymentstatus','=',1)
								->exists();
			if($exists)
			{
				return response()->json(['message' =>'PAYMENT HAS ALREADY BEEN DONE FOR ONE OF THE DETAIL IDS. PLEASE CHECK AND TRY AGAIN!','status'=>400,'authenticate'=>$authenticate], 400);
			}
			
			$sum	=	DB::table('customer_order_detail')->whereIn('detailid', $detailid)->sum('payable');
			$sum	= 	round($sum,2);
			if($sum!=$amount)
			{
				return response()->json(['message' =>'AS PER THE ORDER DETAIL SENT. TOTAL AMOUNT DOES NOT MATCH. PLEASE CHECK AND TRY AGAIN!','status'=>400,'authenticate'=>$authenticate], 400);
			}

			$distinctCustomerId = 	DB::table('customer_order_detail')
										->whereIn('detailid', $detailid)
										->distinct()
										->pluck('customerid')
										->first();											

			$distinctOrderId = 	DB::table('customer_order_detail')
										->whereIn('detailid', $detailid)
										->distinct()
										->pluck('orderid')
										->first();											
			
		
			$detailids   	= 	implode(",",$request->input('detailid'));

			$receipt		=	"RCPT".rand(100,999)."".strtotime(date('Y\-m\-d H:i:s'));

			try
			{
				$order = $this->razorpay->order->create([
					'amount' 			=> $amount * 100, // Convert to paisa
					'currency' 			=> 'INR',
					'receipt' 			=> "{$receipt}",
					'payment_capture'	=> 1
				]);
				$ordId = $order['id'];
				$paymentUrl = "https://api.razorpay.com/v1/checkout/embedded?order_id=" . $ordId;
				$fiveMinutesAgo	=	Carbon::now()->subMinutes(5);
				$recentPayment	=	DB::table('receipt_tbl')
										->select('razorpay_order_id','netamount','paymentstatus')
										->where('vendorid','=',$vendorid)
										->where('customerid','=',$distinctCustomerId)
										->where('orderid','=',$distinctOrderId)
										->where('isunpaidservice','=',1)
										->where('paymentdatetime','>=',$fiveMinutesAgo)
										->first();
				
				if($recentPayment)
				{
					if($recentPayment->paymentstatus=='pending') 
					{
						DB::table('receipt_tbl')
							->where('vendorid','=',$vendorid)
							->where('customerid','=',$distinctCustomerId)
							->where('orderid','=',$distinctOrderId)
							->where('isunpaidservice','=',1)
							->where('razorpay_order_id','=',$recentPayment->razorpay_order_id)
							->update([
								'razorpay_order_id' => $order->id,
								'receiptnumber' 	=> $receipt,
								'detailids' 		=> $detailids,
								'netamount' 		=> $amount,
								'paymentstatus' 	=> 'pending',
								'paymentdatetime' 	=> date('Y\-m\-d H:i:s')
							]);
						
						return response()->json(['message' =>'ORDER GENERATED SUCCESSFULLY','status'=>200,'authenticate'=>$authenticate,'orderid'=>$order->id,'amount'=>$amount], 200);
					}
				}

				$financialyear  =	$this->resourceController->GetFinancialYear($paymentdate);
				
				$timeThreshold = Carbon::now('Asia/Kolkata')->subMinutes(5)->toDateTimeString();
				
				DB::delete("DELETE FROM receipt_tbl WHERE vendorid = ? AND customerid =? AND orderid=? AND generateddate < ? AND paymentstatus=?",[$vendorid,$distinctCustomerId,$distinctOrderId,$timeThreshold,'pending']);
				
				DB::table('receipt_tbl')->insert([
					'financialyear'		=> $financialyear,
					'customerid'		=> $distinctCustomerId,
					'orderid'			=> $distinctOrderId,
					'receiptnumber'		=> $receipt,
					'vendorid' 			=> $vendorid,
					'razorpay_order_id' => $order->id,
					'netamount' 		=> $amount,
					'paymentstatus' 	=> 'pending',
					'paymentdatetime' 	=> date('Y\-m\-d H:i:s'),
					'generateddate'		=> date('Y\-m\-d H:i:s'),
					'detailids'			=> $detailids,
					'isunpaidservice'	=> 1
				]);
				
				
				return response()->json(['message' =>'ORDER GENERATED SUCCESSFULLY','status'=>200,'authenticate'=>$authenticate,'orderid'=>$order->id,'amount'=>$amount,'paymentUrl'=>$paymentUrl],200);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }


    public function makePayment(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'razorpay_signature'	=>	'required',
			'amount'				=>	'required|numeric',
        ];

        $messages = [
			'vendorid.required' 			=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 			=> 'ACCESSTOKEN IS REQUIRED',
			'razorpay_order_id.required'	=> 'RAZORPAY ORDER ID IS REQUIRED',
			'razorpay_payment_id.required'	=> 'RAZORPAY PAYMENT ID IS REQUIRED',
			'razorpay_signature.required'	=> 'RAZORPAY SIGNATURE',
			'amount.required' 				=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 				=> 'AMOUNT MUST BE A NUMERIC VALUE',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
				'isavailable'		=>	$isexist->isavailable,
			];
			$currentDateTime		= 	now();
			$creationdate  			= 	$currentDateTime->format('Y-m-d H:i:s');
			

			$razorpay_order_id		=	(String) $request->input('razorpay_order_id');
			$razorpay_payment_id	=	(String) $request->input('razorpay_payment_id');
			$razorpay_signature		=	(String) $request->input('razorpay_signature');
			$amount   				= 	doubleval($request->input('amount'));

			try
			{
				$order = DB::table('receipt_tbl')
							->where('razorpay_order_id', $razorpay_order_id)
							->where('paymentstatus','pending')
							->where('netamount','=',$amount)
							->where('vendorid','=',$vendorid)
							->first();
				if(!$order)
				{
					return response()->json(['message' =>'TRANSACTION DETAIL NOT FOUND.','status'=>404,'authenticate'=>$authenticate],404);					
				}
				// ABOVE LINE IS ONLY FOR TESTING REMOVE ONCE GETTING razorpay_signature
				$generatedSignature = hash_hmac('sha256', $razorpay_order_id . "|" . $razorpay_payment_id, env('RAZORPAY_SECRET'));

				if($generatedSignature===$razorpay_signature)
				{
					$updatedreceipt	=	DB::table('receipt_tbl')
										->where('razorpay_order_id',$razorpay_order_id)
										->update([
											'paymentstatus' 		=> 'paid',
											'completeddate' 		=> date('Y\-m\-d H:i:s'),
											'razorpay_payment_id' 	=> $razorpay_payment_id,
											'razorpay_signature' 	=> $razorpay_signature,
										]);
					if($updatedreceipt)
					{
						$dids	=	explode(",",$order->detailids);
						foreach($dids as $detailid)
						{
							DB::statement('UPDATE customer_order_detail SET paid = payable, paymentstatus = 1,balanceamount=? WHERE detailid = ? and paymentstatus=?', [0,$detailid,0]);
						}
					}
					return response()->json(['message' =>'PAYMENT VERIFIED SUCCESSFULLY!','status'=>200,'authenticate'=>$authenticate],200);
				}
				else
				{
					return response()->json(['message' =>'INVALID PAYMENT SIGNATURE!','status'=>400,'authenticate'=>$authenticate],400);
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>400,'authenticate'=>$authenticate], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }

    public function makePaymentFailed(Request $request)
	{
		$request->merge([
			'accesstoken' 	=> $request->header('accesstoken'),
			'vendorid' 		=> $request->header('vendorid')
		]);		
		
        $rules = [
			'vendorid'				=>	'required',
			'accesstoken'			=>	'required',
			'razorpay_order_id'		=>	'required',
			'razorpay_payment_id'	=>	'required',
			'amount'				=>	'required|numeric',
			'errorcode'				=>	'required|max:100',
			'errordescription'		=>	'required|max:300',
        ];

        $messages = [
			'vendorid.required' 			=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 			=> 'ACCESSTOKEN IS REQUIRED',
			'razorpay_payment_id.required'	=> 'PAYMENT ID IS REQUIRED',
			'razorpay_order_id.required'	=> 'RAZORPAY ORDER ID IS REQUIRED',
			'amount.required' 				=> 'AMOUNT IS REQUIRED',
			'amount.numeric' 				=> 'AMOUNT MUST BE A NUMERIC VALUE',
			'errorcode.required' 			=> 'ERROR CODE IS REQUIRED',
			'errorcode.max' 				=> 'MAX 300 CHARACTER ALLOWED',
			'errordescription.required' 	=> 'ERROR DESCRIPTION IS REQUIRED',
			'errordescription.max' 			=> 'MAX 1000 CHARACTER ALLOWED',
        ];
		
        $validatedData 	=	$request->validate($rules, $messages);
		
		$vendorid   	= 	intval($request->header('vendorid'));
		$accesstoken   	= 	(String) $request->header('accesstoken');
		
		$isexist		=	$this->CheckVendor($vendorid,$accesstoken);
		if($isexist)
		{
			$authenticate	=	[
				'vendorid'			=>	$isexist->vendorid,
				'accesstoken'		=>	doubleval($isexist->accesstoken),
				'isverified'		=>	$isexist->isverified,
				'verificationstatus'=>	$isexist->verificationstatus,
				'isprofilesubmitted'=>	$isexist->isprofilecompleted,
				'isemployee'		=>	$isexist->isemployee,
				'isapproved'		=>	$isexist->isapproved,
				'isavailable'		=>	$isexist->isavailable,
			];
			$currentDateTime	= 	now();
			$creationdate  		= 	$currentDateTime->format('Y-m-d H:i:s');
			

			$razorpay_order_id	=	(String) $request->input('razorpay_order_id');
			$razorpay_payment_id=	(String) $request->input('razorpay_payment_id');
			$amount   			= 	doubleval($request->input('amount'));
			$errorcode			=	(String) $request->input('errorcode');
			$errordescription	=	(String) $request->input('errordescription');

			try
			{
				$order = DB::table('receipt_tbl')
							->where('razorpay_order_id', $razorpay_order_id)
							->where('paymentstatus','pending')
							->where('netamount',$amount)
							->where('vendorid','=',$vendorid)
							->first();
				if(!$order)
				{
					return response()->json(['message' =>'TRANSACTION DETAIL NOT FOUND.','status'=>404,'authenticate'=>$authenticate],404);					
				}
				// ABOVE LINE IS ONLY FOR TESTING REMOVE ONCE GETTING razorpay_signature
				$updatedreceipt	=	DB::table('receipt_tbl')
									->where('razorpay_order_id',$razorpay_order_id)
									->where('vendorid',$vendorid)
									->update([
										'paymentstatus' 		=> 'failed',
										'errorcode'				=>	$errorcode,
										'errordescription'		=>	$errordescription,
										'razorpay_payment_id'	=>	$razorpay_payment_id
									]);						

				return response()->json(['message'=>'PAYMENT FAILURE STORED!','status'=>200,'authenticate'=>$authenticate],200);	
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
			}
		}
		else
		{
			return response()->json(['message' => __('messages.unauthorized'),'status'=>401,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken], 401);
		}
    }


    public function generatePaymentLink(Request $request)
    {
		$request->merge([
			'accesstoken' => $request->header('accesstoken'),
			'vendorid'    => $request->header('vendorid'),
		]);

		$rules = [
			'vendorid'		=> 'required',
			'accesstoken' 	=> 'required',
			'detailid'		=> 'required|array|min:1'
		];

		$messages = [
			'vendorid.required'    	=> 'VENDOR DETAIL IS REQUIRED',
			'accesstoken.required' 	=> 'ACCESSTOKEN IS REQUIRED',
			'detailid.required' 	=> 'DETAIL ID IS REQUIRED',
			'detailid.array'        => 'DETAIL IDs MUST BE AN ARRAY',
			'detailid.min'        => 'MINIMUM 1 DETAIL ID IS REQUIRED',
		];

		$validatedData = $request->validate($rules, $messages);

		$vendorid   =	intval($request->header('vendorid'));
		$accesstoken=	(string) $request->header('accesstoken');
		$detailids   =	$request->input('detailid');
	
        try
        {
			$appUrl	=	Config::get('app.url');
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$paymentdate	=	date('Y\-m\-d',strtotime($creationdate));
			
			$isexist=	$this->CheckVendor($vendorid, $accesstoken);
			if($isexist)
			{
				$authenticate = [
					'vendorid'            => $isexist->vendorid,
					'accesstoken'         => doubleval($isexist->accesstoken),
					'isverified'          => $isexist->isverified,
					'verificationstatus'  => $isexist->verificationstatus,
					'isprofilesubmitted'  => $isexist->isprofilecompleted,
					'isemployee'          => $isexist->isemployee,
					'isapproved'          => $isexist->isapproved,
					'isavailable'		  => $isexist->isavailable,
				];
				$flag=0;
				foreach($detailids as $detailid)
				{
					
					$vid	=	DB::table('customer_order_detail')->where('detailid','=',$detailid)->value('vendorid');
					if($vid!=$vendorid)
					{
						$flag++;
						break;
					}
				}
				if($flag>0)
				{
					return response()->json(['message'=>'INVALID ORDER AND VENDOR MATCHING.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate],400);
				}

				$customerCount	=	DB::table('customer_order_detail')
										->whereIn('detailid', $detailids)
										->count(DB::raw('DISTINCT customerid'));				
				if($customerCount!=1)
				{
					return response()->json(['message'=>'THE PROVIDED DETAIL IDS ARE ASSOCIATED WITH DIFFERENT CUSTOMERS. PLEASE ENSURE ALL DETAIL IDS BELONG TO THE SAME CUSTOMER AND TRY AGAIN.','status'=>400,'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'authenticate'=>$authenticate,'customerCount'=>$customerCount],400);
				}

				$distinctCustomerId = 	DB::table('customer_order_detail')
											->whereIn('detailid', $detailids)
											->distinct()
											->pluck('customerid')
											->first();											

				$distinctOrderId = 	DB::table('customer_order_detail')
											->whereIn('detailid', $detailids)
											->distinct()
											->pluck('orderid')
											->first();											

				$pendings		=	DB::table('customer_order_detail as a')
											->select('a.detailid','a.orderid','a.orderstatus','a.serviceid','a.optionid','b.servicetitle','a.servicecharge','a.quantity','a.taxable','a.taxvalue','a.payable')
											->leftJoin('services as b', function ($join) {
												$join->on('b.serviceid','=','a.serviceid')
													->on('b.optionid','=','a.optionid');
											})
											->whereIn('a.detailid',$detailids)
											->where('a.customerid',$distinctCustomerId)
											->where('a.orderid',$distinctOrderId)
											->where('a.paymentstatus','=',0)
											->where('a.paid','=',0)
											->get();

				if($pendings->isNotEmpty())
				{
					$totalservicecharge		=	0;
					$totaltaxable			=	0;
					$totaltaxvalue			=	0;
					$netpayable				=	0;
					foreach($pendings as $pending)
					{
						$totalservicecharge	=	round($totalservicecharge+$pending->servicecharge,2);
						$totaltaxable		=	round($totaltaxable+$pending->taxable,2);
						$totaltaxvalue		=	round($totaltaxvalue+$pending->taxvalue,2);
						$netpayable			=	round($netpayable+$pending->payable,2);
					}
				}
				else
				{
					return response()->json(['message' =>'A PAYMENT LINK HAS ALREADY BEEN GENERATED FOR ONE OF THE DETAIL IDS. PLEASE WAIT 5 MINUTES BEFORE ATTEMPTING TO REINITIATE THE PAYMENT','status'=>200,'authenticate'=>$authenticate], 200);
				}
				try
				{
					$flag++;
					$fiveMinutesAgo	=	Carbon::now()->subMinutes(5);

					$recentPayment	=	DB::table('receipt_tbl')
											->where('vendorid','=',$vendorid)
											->where('customerid','=',$distinctCustomerId)
											->where('orderid','=',$distinctOrderId)
											->where('paymentstatus','=','pending')
											->where('generateddate','>=',$fiveMinutesAgo)
											->first();						
					if($recentPayment)
					{
						return response()->json(['message' =>'A PAYMENT LINK HAS ALREADY BEEN GENERATED FOR ONE OF THE DETAIL IDS. PLEASE WAIT 5 MINUTES BEFORE ATTEMPTING TO REINITIATE THE PAYMENT.','status'=>200,'authenticate'=>$authenticate], 200);
					}

					$financialyear  =	$this->resourceController->GetFinancialYear($paymentdate);
					
					$timeThreshold = Carbon::now('Asia/Kolkata')->subMinutes(5)->toDateTimeString();
					
					DB::delete("DELETE FROM receipt_tbl WHERE vendorid = ? AND customerid =? AND orderid=? AND generateddate < ? AND paymentstatus=?",[$vendorid,$distinctCustomerId,$distinctOrderId,$timeThreshold,'pending']);
					
			
					$paymentlink	=	$this->sendPaymentLink($financialyear,$distinctCustomerId,$distinctOrderId,$vendorid,$netpayable,$detailids);
					
					/*
					DB::table('receipt_tbl')->insert([
						'financialyear'		=> $financialyear,
						'customerid'		=> $distinctCustomerId,
						'orderid'			=> $distinctOrderId,
						'vendorid' 			=> $vendorid,
						'netamount' 		=> $amount,
						'detailids'			=> $detailids,
					]);
					*/
					
					return response()->json(['message' =>'PAYMENT LINK HAS BEEN SUCCESSFULLY SENT TO THE CUSTOMER`S MOBILE NUMBER AND EMAIL ADDRESS.','status'=>200,'authenticate'=>$authenticate,'netpayable'=>$netpayable],200);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>$e->getMessage(),'status'=>400], 400);
				}
			}
			else
			{
				return response()->json(['message' => __('messages.unauthorized'),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status' => 401], 401);
			}
        }
        catch (QueryException $e) 
        {
            return response()->json(['message' =>$e->getMessage(),'vendorid'=>$vendorid,'accesstoken'=>$accesstoken,'status'=>400], 400);
        }
    }


	public function handlePaymentLinkCallback(Request $request)
	{
		$paymentLinkId = $request->input('razorpay_payment_link_id');
		$paymentId     = $request->input('razorpay_payment_id');
		$status        = $request->input('razorpay_payment_link_status');

		// Check if receipt exists
		$receipt = DB::table('receipt_tbl')
			->where('payment_link_id', $paymentLinkId)
			->first();

		if (!$receipt) {
			return response()->json(['message' =>'RECEIPT NOT FOUND','status'=>400], 400);
		}


		if($status==='paid')
		{
			// Update payment status using DB query
			$update	=	DB::table('receipt_tbl')
						->where('payment_link_id', $paymentLinkId)
						->update([
							'paymentstatus'      => $status === 'paid' ? 'paid' : $status,
							'razorpay_payment_id'=> $paymentId,
							'paymentdatetime'    => date('Y\-m\-d H:i:s'),
							'completeddate'      => date('Y\-m\-d H:i:s'),
						]);
			if($update)
			{
				$detailids	=	DB::table('receipt_tbl')->where('payment_link_id','=',$paymentLinkId)->value('detailids');
				$dids		=	explode(",",$detailids);
				foreach($dids as $detailid)
				{
					DB::statement('UPDATE customer_order_detail SET paid=payable,paymentstatus=?,balanceamount=? WHERE detailid = ? and paymentstatus=?',[1,0,$detailid,0]);
				}
				
				$receipt	=	DB::table('receipt_tbl')
									->select('receiptnumber','paymentdatetime','customerid','netamount')
									->where('payment_link_id', $paymentLinkId)
									->first();
				
				$customer	=	DB::table('customer_tbl')->where('customerid','=',$receipt->customerid)->first();
				
				$services	=	DB::table('customer_order_detail as a')
									->select('a.quantity','a.payable','b.servicetitle')
									->leftJoin('services as b', function ($join) {
										$join->on('b.serviceid', '=', 'a.serviceid')
											->on('b.optionid', '=', 'a.optionid');
									})									
									->whereIn('detailid',$dids)
									->get();

				$pdf = PDF::loadView('pdf.receipt', ['customer'=>$customer,'receipt'=>$receipt,'services'=>$services]);

				$pdf->setPaper('A4','portrait'); // Set A4 size

		//		return $pdf->download('invoice.pdf');
				$filename	=	$receipt->receiptnumber.'.pdf';
				//$pdf->save(storage_path('app/public/receipts/receipt.pdf'));
				$pdf->save(storage_path('app/public/receipts/'.$receipt->receiptnumber.'.pdf'));
				DB::update('update receipt_tbl set receiptfile=? where payment_link_id=?',[$filename,$paymentLinkId]);
				
				return redirect('/storage/receipts/'.$filename);
			}
			else
			{
				return response('Payment not completed.', 200);
			}
		}

		return response('Payment not completed.', 200);
	}

	public function testPDF(Request $request)
	{
		$pdf = PDF::loadView('pdf.testreceipt');

		$pdf->setPaper('A4','portrait'); // Set A4 size

//		return $pdf->download('invoice.pdf');
		$filename	=	'12345.pdf';
		$pdf->save(storage_path('app/public/receipts/'.$filename));
		
		return response('Payment successful! Thank you.',200);
	}


	public function handleWebhook(Request $request)
	{
		$webhookSecret 	= 	env('Web_Hook_Secret'); //'Web_Hook_Secret';
		$signature 		=	$request->header('X-Razorpay-Signature');
		$payload 		=	$request->getContent();

		if (!hash_equals(hash_hmac('sha256',$payload, $webhookSecret),$signature)) {
			return response('Invalid signature', 400);
		}

		$data = json_decode($payload, true);

		if ($data['event']==='payment_link.paid')
		{
			$paymentLinkId	=	$data['payload']['payment_link']['entity']['id'];
			$paymentId 		=	$data['payload']['payment']['entity']['id'];
			$amount 		=	$data['payload']['payment']['entity']['amount'];

			$receipt	=	DB::table('receipt_tbl')->where('payment_link_id','=',$paymentLinkId)->first();
			if($receipt)
			{
				$vendor		=	DB::table('vendor_tbl')->where('vendorid','=',$receipt->vendorid)->first();
				$customer	=	DB::table('customer_tbl')->where('customerid','=',$receipt->customerid)->first();
				
				$detailids	=	explode(",",$receipt->detailids);
				
				DB::table('customer_order_detail')
						->whereIn('detailid', $detailids)
						->where('paymentstatus',0)
						->update([
							'paid' => DB::raw('balanceamount'),
							'balanceamount' => 0,
							'paymentstatus' => 1,
							'islocked' => 0,
						]);				

	
				$services	=	DB::table('customer_order_detail as a')
									->select('a.quantity','a.payable','b.servicetitle')
									->leftJoin('services as b', function ($join) {
										$join->on('b.serviceid', '=', 'a.serviceid')
											->on('b.optionid', '=', 'a.optionid');
									})									
									->whereIn('detailid',$detailids)
									->get();

				$pdf 		=	PDF::loadView('pdf.receipt', ['customer'=>$customer,'receipt'=>$receipt,'services'=>$services]);
				$pdf->setPaper('A4','portrait');
				$filename	=	$receipt->receiptnumber.'.pdf';
				

				$res	=	DB::update('update receipt_tbl set razorpay_payment_id=?,paymentstatus=?,completeddate=?,razorpay_signature=?,receiptfile=? where payment_link_id=?',[$paymentId,'paid',date('Y\-m\-d H:i:s'),$signature,$filename,$paymentLinkId]);
				if($res)
				{
					$pdf->save(storage_path('app/public/receipts/'.$receipt->receiptnumber.'.pdf'));
					
					$title	=	"Payment Completed";
					$body 	=	"Payment received! The customer has paid your request. You can now start the job.";
					$this->fcm->sendNotification($vendor->fcmid,$title,$body);		
					DB::insert('insert into vendor_notification(vendorid,channelid,title,notification,creationdatetime,notificationtime) values(?,?,?,?,?,?)',[$vendor->vendorid,$vendor->channelid,$title,$body,date('Y\-m\-d H:i:s'),date('Y\-m\-d H:i:s')]);
				}
				return response('Payment verified', 200);
			}
			
		}

		return response('Unhandled event', 200);
	}

	public function handleCallback(Request $request)
	{
		$paymentId = $request->input('razorpay_payment_id');
		$linkId = $request->input('razorpay_payment_link_id');

		$order = DB::table('receipt_tbl')->where('payment_link_id', $linkId)->first();

		if (!$order) {
			return view('payments.unhandled', ['message' => 'Order not found']);
		}

		if ($order->paymentstatus==='paid') {
			return view('payments.success', ['order' => $order]);
		}

		return view('payments.pending', ['order' => $order]);
	}	
	public function contactDetail()
	{
		try
		{
			$data	=	DB::table('app_tbl')
						->select('androidappid', 'applestoreid', 'contactnumber','email','address','privacypolicy','message')
						->where('id','=',1)
						->first();
			return response()->json(['message' =>'CONTACT DETAIL','status'=>200,'data'=>['androidappid'=>$data->androidappid,'applestoreid'=>$data->applestoreid,'contactnumber'=>$data->contactnumber,'email'=>$data->email,'address'=>$data->address,'privacypolicy'=>$data->privacypolicy,'message'=>$data->message]], 200);
		}
		catch(QueryException $e)
		{
			return response()->json(['message' =>'UNABLE TO FETCH CONTACT DETAIL. PLEASE TRY AGAIN LATER.','status'=>400], 400);
		}			
		
	}
	
	/*
	public function copyData()
	{
		// Step 1: Read from database1 (default)
		$vendors = DB::table('vendor_tbl')->get();
		$tempConnection = 'temp_mysql';
		// Step 2: Connect to database2 dynamically

		Config::set("database.connections.$tempConnection", [
			'driver'    => 'mysql',
			'host'      => '127.0.0.1',
			'port'      => '3306',
			'database'  => 'creatfrp_live_screw',
			'username'  => 'creatfrp_live_screw',
			'password'  => 'Screw@driver25',
			'charset'   => 'utf8mb4',
			'collation' => 'utf8mb4_unicode_ci',
			'prefix'    => '',
		]);		
		DB::purge($tempConnection);
		DB::reconnect($tempConnection);
		// Step 3: Insert without listing columns, skip if mobile exists

		foreach ($vendors as $vendor) {
			$data = (array) $vendor;
			unset($data['vendorid']); // remove auto-increment field

			$exists = DB::connection($tempConnection)
				->table('vendor_tbl')
				->where('mobilenumber', $data['mobilenumber'])
				->exists();

			if (!$exists) {
				DB::connection($tempConnection)
					->table('vendor_tbl')
					->insert($data);
			}
		}

		return response()->json(['message' => 'Vendors migrated successfully (auto-increment IDs skipped).']);
	}
	*/
	

}
