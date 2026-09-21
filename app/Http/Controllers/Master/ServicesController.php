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
use Illuminate\Support\Facades\Artisan;
class ServicesController extends Controller
{
	protected $displayOrderService;
    public function __construct(DisplayOrderService $displayOrderService)
    {
        $this->displayOrderService = $displayOrderService;
    }
	/* START END PHOTOS START */
    public function addStartEndPhoto(Request $request){
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','startendphoto');

		Session::put('menid',58);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=58 and ispermitted=1 and userid=".$userId.") as actions"))
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
		
		
        return view('admin/master/startendphoto_add');
    }
    public function storeStartEndPhoto(Request $request,$recordid)
	{        
        $rules = [
            'labelvalue' 	=> 'required|max:50',
        ];

        $messages = [
            'labelvalue.required' 	=> 'PHOTO LABEL IS REQUIRED',
			'labelvalue.max' 		=> 'MAX 50 CHARACTERS ALLOWED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('loginId');
		$userName   	=	$request->session()->get('userId');
		$currentDateTime= 	now();
		$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

		$labelvalue	=	(String) $request->input('labelvalue');

		if($recordid==0)
		{
			try
			{
				DB::insert('INSERT INTO service_photo_labels(labelvalue,createdby,creationdate) VALUES (?,?,?)',[ucwords($labelvalue),$userId,$creationdate]);

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
				$res = DB::update('update service_photo_labels set labelvalue=? where labelid=?',[$labelvalue,$recordid]);

				return redirect('/master/add/startendphoto')->with('success',__('messages.updated'));
			}
			catch(QueryException $e)
			{
				return back()->with('duplicate',__('messages.duplicate'))->withInput();
			}			
		}
    }
    public function getStartEndPhotoData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);

        $data = DB::table('service_photo_labels')
                ->orderBy('labelvalue')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('labelvalue','like','%'.$pagesearch.'%');								 
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/startendphotoTable', ['data' => $data]);

    }
    public function editStartEndPhoto($recordid)
	{
		$data = DB::table('service_photo_labels')->where('labelid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/startendphoto_edit',compact('data'));
    }
    public function deleteStartEndPhoto($recordid)
	{
		$res = DB::delete('DELETE FROM service_photo_labels WHERE labelid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	
	/* START END PHOTOS END */
	/* COMMISSION RELATED START */
    public function addCommission(Request $request){
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','addcommission');

		Session::put('menid',41);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=41 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$categories = 	DB::table('category_tbl')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->orderBy('displayorder')
							->get();

		$combinations = [];
		foreach ($tiers as $tier) {
			foreach ($categories as $category) {
				
				$commission = DB::table('commission_tbl')
									->where('tierid', $tier->tierid)
									->where('categoryid', $category->categoryid)
									->first();
				$combinations[] = [
					'tiername'      => $tier->tiername,
					'categoryname'  => $category->category,
					'tierid'        => $tier->tierid,
					'categoryid'    => $category->categoryid,
					'commission'    => $commission ? $commission->commission : null,
				];
				/*
				$combinations[] = [
					'tiername' 		=> $tier->tiername,
					'categoryname' 	=> $category->category,
					'tierid' 		=> $tier->tierid,
					'categoryid' 	=> $category->categoryid
				];
				*/
			}
		}
		
        return view('admin/master/commission_add',compact('combinations','tiers','categories'));
    }
    public function getCommissionData(Request $request)
	{
		$categoryid =	intval($request->input('categoryid'));
		$tierid		=	intval($request->input('tierid'));
		
		if($categoryid==0)
		{
			$categories = 	DB::table('category_tbl')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->orderBy('displayorder')
							->get();
		}
		else
		{
			$categories = 	DB::table('category_tbl')
							->whereNotIn('categoryid', function ($query) {
								$query->select('parentcategoryid')->from('category_tbl');
							})
							->where('categoryid','=',$categoryid)
							->get();
			
		}
		if($tierid==0)
		{
			$tiers		=	DB::table('tier_tbl')->orderby('tiername')->get();
		}
		else
		{
			$tiers		=	DB::table('tier_tbl')->where('tierid','=',$tierid)->get();
		}
		

		$combinations = [];
		foreach ($tiers as $tier) {
			foreach ($categories as $category) {
				
				$commission = DB::table('commission_tbl')
									->where('tierid', $tier->tierid)
									->where('categoryid', $category->categoryid)
									->first();
				$combinations[] = [
					'tiername'      => $tier->tiername,
					'categoryname'  => $category->category,
					'tierid'        => $tier->tierid,
					'categoryid'    => $category->categoryid,
					'commission'    => $commission ? $commission->commission : null,
				];
			}
		}
		
		return view('/admin/ajaxpages/commissionTable', compact('combinations'))->render();
    }
    public function updateCommission(Request $request)
	{
        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');
		
		$tierid 		=	intval($request->input('tierid'));
		$categoryid		=	intval($request->input('categoryid'));
		$commission		=	doubleval($request->input('commission'));
		if($commission>99)
		{
			return response()->json(['error' => true,'fail'=>'','message'=>'INVALID COMMISSION VALUE']);
		}
		
		$isavailable	=	DB::table('commission_tbl')->where('tierid','=',$tierid)->where('categoryid','=',$categoryid)->first();

		if($isavailable)
		{
			$activity="";
			if($commission!=$isavailable->commission)
			{
				$activity	=	"COMMISSION VALUE CHANGED PRE VALUE : ".$isavailable->commission." NEW VALUE : ".$commission." UPDATED BY ".strtoupper($userName)." ON ".date('d\-m\-Y, h:i A'); 
			}
			if($activity!='')
			{
				if($isavailable->activity!='')
				{
					$activity	=	$activity."<br>".$isavailable->activity;
				}
				DB::update('update commission_tbl set commission=?,activity=? where tierid=? and categoryid=?',[$commission,$activity,$tierid,$categoryid]);
			}
			else
			{
				DB::update('update commission_tbl set commission=? where tierid=? and categoryid=?',[$commission,$tierid,$categoryid]);
			}
			return response()->json(['success' => true,'fail'=>'','message'=>'COMMISSION UPDATED SUCCESSFULLY']);
				
		}
		else
		{
			DB::insert('insert into commission_tbl(tierid,categoryid,commission,activity,createdby,createdbyname,creationdate) values(?,?,?,?,?,?,?)',[$tierid,$categoryid,$commission,'',$userId,$userName,$creationdate]);
			return response()->json(['success' => true,'fail'=>'','message'=>'COMMISSION STORED SUCCESSFULLY']);
		}
		

    }
	
	/* COMMISSION RELATED CLOSED */

	/* SERVICE / PRODUCT RELATED START */
    public function addService(Request $request){

		Session::put([
			'adminmenu' => 'services',
			'adminsubmenu' => 'services',
			'menid' => 26,
		]);

		$userId = Session::get('loginId');
		$isSuper = Session::get('issuper');
		
		$actions = [];

		if($isSuper == 1) {
			$action = DB::table('menu_action')
						->selectRaw("GROUP_CONCAT(actionid) as actions")
						->first();
		}
		else
		{
			$action = DB::table('menu_permission')
						->selectRaw("GROUP_CONCAT(actionid) as actions")
						->where([
							['menuid', '=', 26],
							['ispermitted', '=', 1],
							['userid', '=', $userId],
						])
						->groupBy('menuid')
						->first();
		}

		if ($action && $action->actions) {
			$actions = explode(',', $action->actions);
		}

		Session::put('actions', $actions);

		// Load dropdown/select data
		$category = DB::table('category')
						->whereNotIn('categoryid', function ($query) {
							$query->select('parentcategoryid')->from('category_tbl');
						})
						->where('parentcategoryid', '!=', 0)
						->orderBy('displayname')
						->get();

		$needs     = DB::table('need_tbl')->orderBy('displayorder')->get();
		$includes  = DB::table('include_tbl')->orderBy('displayorder')->get();
		$excludes  = DB::table('exclude_tbl')->orderBy('displayorder')->get();
		$taxes     = DB::table('tax_tbl')->orderBy('taxname')->get();
		$photos    = DB::table('service_photo_labels')->orderBy('labelvalue')->get();

		// Helper closure to check permission
		$checkPermission = function ($menuId) use ($userId) {
			return optional(
				DB::table('menu_permission')
					->select('ispermitted')
					->where([
						['menuid', '=', $menuId],
						['userid', '=', $userId],
						['actionid', '=', 1],
					])
					->first()
			)->ispermitted ?? 0;
		};

		$addneed    = $isSuper ? 1 : $checkPermission(27);
		$addinclude = $isSuper ? 1 : $checkPermission(28);
		$addexclude = $isSuper ? 1 : $checkPermission(53);

		return view('admin/master/service_add', compact('category','needs','includes','excludes','taxes','addneed','addinclude','addexclude','photos'));

    }
    public function storeService(Request $request,$recordid)
	{
		$isNew = ($recordid == 0);
		
		$rules = [
			'categoryid'     => 'required',
			'servicetitle'   => 'required|max:250',
			'mrp'            => 'nullable|numeric',
			'visitingcharge' => 'nullable|numeric',
			'taxid'          => 'required|numeric',
			'minqty'         => 'required|numeric',
			'hsncode'        => 'nullable|max:25',
			'isactive'       => 'required|numeric',
			'firstpic'       => $isNew ? 'required|max:512' : 'nullable|max:512',
		];

		$messages = [
			'required' => __('validation.thisis.required'),
			'numeric'  => __('validation.thisis.numeric'),
			'firstpic.max' => __('validation.thisis500kb.max'),
			'hsncode.max'  => __('validation.thisis25.max'),
		];

		$request->validate($rules, $messages);

		$userId      = Session::get('loginId');
		$userName    = Session::get('userId');
		$now         = now()->format('Y-m-d H:i:s');

		$inputs = $request->only([
			'categoryid', 'servicetitle', 'mrp', 'visitingcharge', 'taxid', 'requiredtime',
			'minqty', 'maxqty', 'isactive', 'optionrequired', 'hsncode', 'description',
			'needs', 'includes', 'excludes',
		]);
		
		$optionalFields = ['hsncode', 'description', 'needs', 'includes', 'excludes', 'requiredtime'];
		foreach ($optionalFields as $field) {
			$inputs[$field] = $inputs[$field] ?? '';
		}

		$startingImages  = $this->parseLabelFlags($request->input('labelid', []), $request->input('atstart', []));
		$finishingImages = $this->parseLabelFlags($request->input('labelid', []), $request->input('atend', []));
		
		$imagePaths = [];
		
		
		if ($isNew) {
			DB::beginTransaction();
			try {
				$serviceId = DB::table('service_tbl')->insertGetId([
					'categoryid'      => intval($inputs['categoryid']),
					'servicetitle'    => $inputs['servicetitle'],
					'mrp'             => doubleval($inputs['mrp']),
					'visitingcharge'  => doubleval($inputs['visitingcharge']),
					'taxid'           => intval($inputs['taxid']),
					'requiredtime'    => $inputs['requiredtime'],
					'minqty'          => intval($inputs['minqty']),
					'maxqty'          => intval($inputs['maxqty']),
					'isactive'        => intval($inputs['isactive']),
					'optionrequired'  => intval($inputs['optionrequired']),
					'hsncode'         => $inputs['hsncode'],
					'description'     => $inputs['description'],
					'createdby'       => $userId,
					'createdbyname'   => $userName,
					'creationdate'    => $now,
					'startingimages'  => $startingImages,
					'finishingimages' => $finishingImages,
					'needs'           => $inputs['needs'],
					'includes'        => $inputs['includes'],
					'excludes'        => $inputs['excludes'],
				]);

				$imageFields = ['firstpic', 'secondpic', 'thirdpic', 'fourthpic'];

				foreach ($imageFields as $field) {
					if ($request->hasFile($field)) {
						$storedPath = $request->file($field)->store('uploads/serviceimages', 'public');
						$imagePaths[] = $storedPath;

						DB::table('service_images')->insert([
							'serviceid'    => $serviceId,
							'servicepic'   => $storedPath,
							'creationdate' => $now,
						]);
					}
				}

				DB::commit();
				Artisan::call('cache:clear');
				return back()->with('success', __('messages.stored'));

			} catch (\Exception $e) {
				DB::rollBack();
				// Clean up any uploaded files
				foreach ($imagePaths as $path) {
					Storage::disk('public')->delete($path);
				}
				return back()->with('duplicate', $e->getMessage())->withInput();
			}

		} else {
			// Update existing
			$updateData = [
				'categoryid'      => intval($inputs['categoryid']),
				'servicetitle'    => $inputs['servicetitle'],
				'mrp'             => doubleval($inputs['mrp']),
				'taxid'           => intval($inputs['taxid']),
				'requiredtime'    => $inputs['requiredtime'],
				'minqty'          => intval($inputs['minqty']),
				'maxqty'          => intval($inputs['maxqty']),
				'isactive'        => intval($inputs['isactive']),
				'optionrequired'  => intval($inputs['optionrequired']),
				'hsncode'         => $inputs['hsncode'] ?? '',
				'description'     => $inputs['description'] ?? '',
				'visitingcharge'  => doubleval($inputs['visitingcharge']),
				'startingimages'  => $startingImages,
				'finishingimages' => $finishingImages,
				'needs'           => $inputs['needs'] ?? '',
				'includes'        => $inputs['includes'] ?? '',
				'excludes'        => $inputs['excludes'] ?? '',
			];
			DB::beginTransaction();
			try
			{
				DB::table('service_tbl')->where('serviceid', $recordid)->update($updateData);
				DB::commit();
				Artisan::call('cache:clear');
				return redirect('master/add/services')->with('success', __('messages.updated'));
			}
			catch (\Exception $e)
			{
				DB::rollBack();
				return back()->with('duplicate', $e->getMessage())->withInput();
			}
		}
    }
	private function parseLabelFlags(array $labelIds, array $flags): string
	{
		$selected = [];

		foreach ($labelIds as $index => $id) {
			if (isset($flags[$index]) && $flags[$index] == 1) {
				$selected[] = $id;
			}
		}

		return implode(',', $selected);
	}	
    public function getServiceData(Request $request)
	{

		$filters = $request->only(['categoryid', 'taxid', 'isactive', 'pagesearch']);
		$pageSize = intval($request->input('pagesize', 10));
		$currentPage = intval($request->input('page', 1));		

        
		$query = DB::table('service_tbl as a')
					->select('a.*', 'b.category', 'd.taxname')
					->leftJoin('category_tbl as b', 'b.categoryid', '=', 'a.categoryid')
					->leftJoin('tax_tbl as d', 'd.taxid', '=', 'a.taxid')
					->when(!empty($filters['categoryid']), fn($q) => $q->where('a.categoryid', $filters['categoryid']))
					->when(!empty($filters['taxid']), fn($q) => $q->where('a.taxid', $filters['taxid']))
					->when(isset($filters['isactive']) && $filters['isactive'] !== '', fn($q) => $q->where('a.isactive', $filters['isactive']))
					->when(!empty($filters['pagesearch']), function ($q) use ($filters) {
						$search = '%' . $filters['pagesearch'] . '%';
						return $q->where(function ($sub) use ($search) {
							$sub->where('a.servicetitle', 'like', $search)
								->orWhere('a.hsncode', 'like', $search);
						});
					})
					->orderBy('a.creationdate','desc');
		$data = $query->paginate($pageSize, ['*'], 'page', $currentPage);
		$serviceIds = $data->pluck('serviceid')->toArray();
		$images = DB::table('service_images')
			->select('serviceid', 'servicepic')
			->whereIn('serviceid', $serviceIds)
			->orderBy('recordid')
			->get()
			->groupBy('serviceid');		

		foreach($data as $dt)
		{
			$dt->servicepic	=	$images[$dt->serviceid][0]->servicepic ?? null;
		}		
		return view('admin.ajaxpages.servicesTable', compact('data'))->render();
    }
    public function getServiceListData(Request $request)
	{

		$filters = $request->only(['categoryid', 'pagesearch']);
		$pageSize = intval($request->input('pagesize', 10));
		$currentPage = intval($request->input('page', 1));		

        
		$query = DB::table('service_tbl as a')
					->select('a.*', 'b.category', 'd.taxname')
					->leftJoin('category_tbl as b', 'b.categoryid', '=', 'a.categoryid')
					->leftJoin('tax_tbl as d', 'd.taxid', '=', 'a.taxid')
					->when(!empty($filters['categoryid']), fn($q) => $q->where('a.categoryid', $filters['categoryid']))
					->when(!empty($filters['pagesearch']), function ($q) use ($filters) {
						$search = '%' . $filters['pagesearch'] . '%';
						return $q->where(function ($sub) use ($search) {
							$sub->where('a.servicetitle', 'like', $search)
								->orWhere('a.hsncode', 'like', $search);
						});
					})
					->orderBy('a.displayorder');
		$data = $query->paginate($pageSize, ['*'], 'page', $currentPage);
		$serviceIds = $data->pluck('serviceid')->toArray();
		$images = DB::table('service_images')
			->select('serviceid', 'servicepic')
			->whereIn('serviceid', $serviceIds)
			->orderBy('recordid')
			->get()
			->groupBy('serviceid');		

		foreach($data as $dt)
		{
			$dt->servicepic	=	$images[$dt->serviceid][0]->servicepic ?? null;
		}		
		return view('admin.ajaxpages.servicelistsTable', compact('data'))->render();
    }

    public function updateServiceOrder(Request $request)
	{
		$serviceid	=	$request->input("serviceid");
		$val		=	$request->input("val");
		DB::update('update service_tbl set displayorder=? where serviceid=?',[$val,$serviceid]);
		return response()->json(1);
	}
    public function editService($recordid)
	{
		$serviceId = Crypt::decrypt($recordid);

        $category = DB::table('category_tbl')
            ->whereNotIn('categoryid', function ($query) {
                $query->select('parentcategoryid')->from('category_tbl');
            })
			->where('parentcategoryid','!=',0)
            ->orderBy('displayorder')
            ->get();

        $taxes = DB::table('tax_tbl')->orderBy('taxname')->get();

        // Get service details
        $data = DB::table('service_tbl')->where('serviceid','=',$serviceId)->first();

        // Fetch photo labels
        $photos = DB::table('service_photo_labels')->orderBy('labelvalue')->get();

        return view('admin/master/service_edit',compact('data','category','taxes','photos'));
    }
	
    public function setActiveInactiveService(Request $request)
	{
		try
		{
			$decryptedId = Crypt::decrypt($request->input('serviceid'));

			$newStatus = DB::table('service_tbl')
				->where('serviceid', $decryptedId)
				->update([
					'isactive' => DB::raw('1 - isactive') // Toggle logic in DB directly
				]);

			return response()->json(['success' => true]);
		}
		catch (\Exception $e)
		{
			return response()->json([
				'success' => false,
				'error' => 'Failed to update status',
				'message' => $e->getMessage()
			], 500);
		}
		return response()->json(['success' => true,'fail'=>'']);

    }
    public function getServicePhotos(Request $request)
	{
		try {
			$encryptedServiceId = $request->input('serviceid');
			$serviceId = Crypt::decrypt($encryptedServiceId);

			// Use Eloquent if you have models; otherwise, clean query builder
			$data = DB::table('service_images')
						->where('serviceid', $serviceId)
						->get();

			$service = DB::table('service_tbl')
						->where('serviceid', $serviceId)
						->first();

			return view('admin.ajaxpages.servicesPhotos', compact('data', 'service'))->render();

		} catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
			// Handle decryption failure
			return response()->json(['error' => 'Invalid service ID.'], 400);
		} catch (\Exception $e) {
			// Handle general errors
			return response()->json(['error' => 'An error occurred while fetching service photos.'], 500);
		}

		
    }
    public function copyServiceData(Request $request)
	{
		$serviceid 	=	$request->input('serviceid');
		
		$serviceid	=	Crypt::decrypt($serviceid);
        
		$services	=	DB::table('service_tbl')->where('isactive','=',1)->orderby('servicetitle')->get();
			
		return view('/admin/ajaxpages/servicesList', compact('services','serviceid'))->render();
    }

    public function storeServicePhotos(Request $request,$recordid){        
        
        $rules = [
            'gallerypic' => 'required|max:500',
        ];

        $messages = [
            'gallerypic.required' 	=> __('validation.thisis.required'),
			'gallerypic.max' 		=> __('validation.thisis500.max'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $creationDate  = now()->format('Y-m-d H:i:s');

		$galleryPath = null;
		try 
		{
			$galleryPath = $request->file('gallerypic')->store('uploads/serviceimages', 'public');


			DB::table('service_images')->insert([
				'serviceid'     => $recordid,
				'servicepic'    => $galleryPath,
				'creationdate'  => $creationDate,
			]);

			return response()->json([
				'success'   => __('messages.stored'),
				'serviceid' => Crypt::encrypt($recordid),
			]);			
			
		}
		catch (\Illuminate\Database\QueryException $e) {
			// If DB insert fails, delete uploaded image
			if ($galleryPath) {
				Storage::disk('public')->delete($galleryPath);
			}

			return response()->json([
				'error'   => 'Database error: ' . $e->getMessage(),
			], 500);
		}
		catch (\Exception $e)
		{
			// Catch other errors (e.g., file issues)
			if ($galleryPath) {
				Storage::disk('public')->delete($galleryPath);
			}

			return response()->json([
				'error'   => 'An unexpected error occurred: ' . $e->getMessage(),
			], 500);
		}
	}
    public function deleteServicePhotos($recordid)
	{
		$content = DB::table('service_images')->where('recordid','=',Crypt::decrypt($recordid))->first();
		if($content->servicepic!='')
		{
			Storage::disk('public')->delete($content->servicepic);
		}
		
        $res = DB::delete('DELETE FROM service_images WHERE recordid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail',__('messages.notfound'));
        }

    }
    public function addServiceOption(Request $request)
	{
		$serviceid 	=	$request->input('serviceid');
		$serviceid	=	Crypt::decrypt($serviceid);        
		$data		=	DB::table('service_tbl')->where('serviceid','=',$serviceid)->first();
		$option		=	DB::table('service_options')->where('serviceid','=',$serviceid)->get();
		return view('/admin/ajaxpages/addserviceOptions', compact('data','option'))->render();
    }
    public function storeServiceOption(Request $request,$recordid){        
        
        $rules = [
            'servicename' => 'required|max:250',
			'servicemrp' => 'required|numeric',
			'servicepic' => 'nullable|max:500',
        ];

        $messages = [
            'servicename.required' 	=> __('validation.thisis.required'),
			'servicename.max' 		=> __('validation.thisis250.max'),
            'servicemrp.required' 	=> __('validation.thisis.required'),
			'servicemrp.numeric' 	=> __('validation.thisis.numeric'),
			'servicepic.max' 		=> __('validation.thisis500.max'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$servicepic    	= 	(String) $request->file('servicepic');
		$servicename	=	$request->input('servicename');
		$description	=	(String) $request->input('shortdescription');
		$mrp			=	doubleval($request->input('servicemrp'));
		$requiredtime	=	doubleval($request->input('timerequired'));
		$activities		=	"";

		try 
		{
			if($servicepic!='')
			{
				$servicepic	=	$request->file('servicepic')->store('uploads/serviceimages','public');
			}
			DB::insert('insert into service_options(serviceid,servicetitle,mrp,displayimage,description,requiredtime,activities,createdby,createdbyname,creationdate) values(?,?,?,?,?,?,?,?,?,?)',[$recordid,$servicename,$mrp,$servicepic,$description,$requiredtime,$activities,$userId,$userName,$creationdate]);
			
			
			
			return response()->json(['success' => __('messages.stored'),'serviceid'=>Crypt::encrypt($recordid)]);
		}
		catch (QueryException $e) 
		{
			if($servicepic!='')
			{
				Storage::disk('public')->delete($servicepic);
			}
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }
    public function updateServiceOption(Request $request,$recordid){        
        
        $rules = [
            'servicename' 	=> 'required|max:250',
			'servicemrp' 	=> 'required|numeric',
			'timerequired' 	=> 'required|numeric',
			'servicepic' 	=> 'nullable|max:500',
        ];

        $messages = [
            'servicename.required' 	=> __('validation.thisis.required'),
			'servicename.max' 		=> __('validation.thisis250.max'),
            'servicemrp.required' 	=> __('validation.thisis.required'),
			'servicemrp.numeric' 	=> __('validation.thisis.numeric'),
            'timerequired.required' => __('validation.thisis.required'),
			'timerequired.numeric' 	=> __('validation.thisis.numeric'),
			'servicepic.max' 		=> __('validation.thisis500.max'),
        ];

        $validatedData 	= $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$servicename	=	$request->input('servicename');
		$description	=	$request->input('shortdescription');
		$mrp			=	doubleval($request->input('servicemrp'));
		$timerequired	=	doubleval($request->input('timerequired'));
		$servicepic    	= 	(String) $request->file('servicepic');
		$activities		=	"";
		
		try 
		{
			if($servicepic!='')
			{
				$content	=	DB::table('service_options')->where('optionid','=',$recordid)->first();
				if($content->displayimage!='')
				{
					Storage::disk('public')->delete($content->displayimage);
				}
				
				$servicepic	=	$request->file('servicepic')->store('uploads/serviceimages','public');
			}
			$updateData = [];
			if(!empty($servicename))
			{
				$updateData['servicetitle'] = $servicename;
			}
			if(!empty($description))
			{
				$updateData['description'] = $description;
			}
			if(!empty($mrp))
			{
				$updateData['mrp'] = $mrp;
			}
			if(!empty($timerequired))
			{
				$updateData['requiredtime'] = $timerequired;
			}
			if(!empty($servicepic))
			{
				$updateData['displayimage'] = $servicepic;
			}
			DB::table('service_options')->where('optionid', $recordid)->update($updateData);
			return redirect('master/add/services')->with('success',__('messages.optionupdated'));
		}
		catch (QueryException $e) 
		{
			if($servicepic!='')
			{
				Storage::disk('public')->delete($servicepic);
			}
			return back()->with('duplicate',$e->getMessage())->withInput();
		}
    }
    public function editServiceOption($recordid)
	{
		$data = DB::table('service_options')->where('optionid','=',Crypt::decrypt($recordid))->first();
		
		
        return view('admin/master/serviceoption_edit',compact('data'));
    }
	
    public function deleteServiceOption($recordid)
	{
		$content = DB::table('service_options')->where('optionid','=',Crypt::decrypt($recordid))->first();
		if($content->displayimage!='')
		{
			Storage::disk('public')->delete($content->displayimage);
		}
		
        $res = DB::delete('DELETE FROM service_options WHERE optionid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail',__('messages.notfound'));
        }

    }
    public function deleteService($recordid)
	{
		$content = DB::table('service_images')->where('serviceid','=',Crypt::decrypt($recordid))->first();
		if($content->servicepic!='')
		{
			Storage::disk('public')->delete($content->servicepic);
		}
		
        $res = DB::delete('DELETE FROM service_tbl WHERE serviceid=?', [Crypt::decrypt($recordid)]);

        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail',__('messages.notfound'));
        }

    }
	
	/* SERVICE / PRODUCT RELATED CLOSED */


	/* EXCLUDE RELATED */	
    public function addExclude(Request $request){
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','serviceexclude');
		$displayorder = DB::table('exclude_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;	

		Session::put('menid',53);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=53 and ispermitted=1 and userid=".$userId.") as actions"))
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


        return view('admin/master/exclude_add',compact('displayorder'));
    }
    public function storeExclude(Request $request,$recordid)
	{        
        $rules = [
            'serviceexclude' => 'required|max:500',
        ];

        $messages = [
            'serviceexclude.required' => 'SERVICE EXCLUDE IS REQUIRED',
			'serviceexclude.max' => 'ONLY 150 CHARACTERS ALLOWED',
        ];

        $validatedData 	= 	$request->validate($rules, $messages);
		$userId     	=	$request->session()->get('loginId');
		$userName   	=	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$serviceexclude	=	ucfirst($request->input('serviceexclude'));
		$displayorder 	=	intval($this->displayOrderService->getDisplayOrder('exclude_tbl'))+1;

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into exclude_tbl (serviceexclude,displayorder,creationdate,createdby,createdbyname) values (?,?,?,?,?)',[$serviceexclude,$displayorder,$creationdate,$userId,$userName]);
				return back()->with('success','SERVICE EXCLUDE STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'SERVICE EXCLUDE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('exclude_tbl')->where('excludeid',$recordid)->first();
			$updateData = [];
			if(!empty($serviceexclude))
			{
				$updateData['serviceexclude'] = $serviceexclude;
			}
			if(!empty($displayorder))
			{
				$updateData['displayorder'] = $displayorder;
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
						DB::table('exclude_tbl')
							->where('displayorder', '>', $oldDisplayOrder)
							->where('displayorder', '<=', $newDisplayOrder)
							->where('excludeid', '!=', $recordid)
							->decrement('displayorder');
					}
					else
					{
						DB::table('exclude_tbl')
							->where('displayorder', '>=', $newDisplayOrder)
							->where('displayorder', '<', $oldDisplayOrder)
							->where('excludeid', '!=', $recordid)
							->increment('displayorder');
					}
					});				
				}				
				$res = DB::table('exclude_tbl')->where('excludeid', $recordid)->update($updateData);
				return redirect('master/add/serviceexclude')->with('success','SERVICE INCLUDE UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'SERVICE EXCLUDE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
    }
    public function getExcludeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('exclude_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('serviceexclude','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		return view('admin/ajaxpages/excludeTable', ['data' => $data]);
    }
    public function editExclude($recordid)
	{
        $data = DB::table('exclude_tbl')->where('excludeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/exclude_edit',compact('data'));
    }
    public function deleteExclude($recordid)
	{
        $res = DB::delete('delete from exclude_tbl WHERE excludeid=?',[Crypt::decrypt($recordid)]);
        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }
    }
    public function updateExclude(Request $request)
	{
		$excludeid 	=	$request->input('excludeid');		
		$excludeid	=	Crypt::decrypt($excludeid);
		$exclude 	=	(String) $request->input('exclude');
		$displayorder=	intval($request->input('displayorder'));

	
		$updated = DB::table('exclude_tbl')->where('excludeid', $excludeid)->update(['serviceexclude' => $exclude,'displayorder'=>$displayorder]);
		return response()->json(['success' => true,'fail'=>'']);

    }
	/* EXCLUDE RELATED */


	
	/* INCLUDE RELATED */	
    public function addInclude(Request $request){
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','serviceinclude');

		$isact	=	DB::table('mainmenu_tbl')->where('menuid','=',28)->value('isactive');
		if($isact==0)
		{
			return redirect('application/admin/dashboard');
		}

		$displayorder = DB::table('include_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;	

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


        return view('admin/master/include_add',compact('displayorder'));
    }
    public function storeInclude(Request $request,$recordid)
	{        
        $rules = [
            'serviceinclude' => 'required|max:500',
        ];

        $messages = [
            'serviceinclude.required' => 'SERVICE INCLUDE IS REQUIRED',
			'serviceinclude.max' => 'ONLY 150 CHARACTERS ALLOWED',
        ];

        $validatedData 	= $request->validate($rules, $messages);
		$userId     	=	$request->session()->get('loginId');
		$userName   	=	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$serviceinclude	=	ucfirst($request->input('serviceinclude'));
		$displayorder = intval($this->displayOrderService->getDisplayOrder('include_tbl'))+1;

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into include_tbl (serviceinclude,displayorder,creationdate,createdby,createdbyname) values (?,?,?,?,?)',[$serviceinclude,$displayorder,$creationdate,$userId,$userName]);
				return back()->with('success','SERVICE INCLUDE STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'SERVICE INCLUDE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('include_tbl')->where('includeid',$recordid)->first();
			$updateData = [];
			if(!empty($serviceinclude))
			{
				$updateData['serviceinclude'] = $serviceinclude;
			}
			if(!empty($displayorder))
			{
				$updateData['displayorder'] = $displayorder;
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
						DB::table('include_tbl')
							->where('displayorder', '>', $oldDisplayOrder)
							->where('displayorder', '<=', $newDisplayOrder)
							->where('includeid', '!=', $recordid)
							->decrement('displayorder');
					}
					else
					{
						DB::table('include_tbl')
							->where('displayorder', '>=', $newDisplayOrder)
							->where('displayorder', '<', $oldDisplayOrder)
							->where('includeid', '!=', $recordid)
							->increment('displayorder');
					}
					});				
				}				
				$res = DB::table('include_tbl')->where('includeid', $recordid)->update($updateData);
				return redirect('master/add/serviceinclude')->with('success','SERVICE INCLUDE UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'SERVICE INCLUDE IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
    }
    public function getIncludeData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
        $data = DB::table('include_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('serviceinclude','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
		return view('admin/ajaxpages/includeTable', ['data' => $data]);
    }
    public function editInclude($recordid)
	{
        $data = DB::table('include_tbl')->where('includeid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/include_edit',compact('data'));
    }
    public function deleteInclude($recordid)
	{
        $res = DB::delete('delete from include_tbl WHERE includeid=?',[Crypt::decrypt($recordid)]);
        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }
    }
    public function updateInclude(Request $request)
	{
		$includeid 	=	$request->input('includeid');		
		$includeid	=	Crypt::decrypt($includeid);
		$include 	=	(String) $request->input('include');
		$displayorder=	intval($request->input('displayorder'));

	
		$updated = DB::table('include_tbl')->where('includeid', $includeid)->update(['serviceinclude' => $include,'displayorder'=>$displayorder]);
		return response()->json(['success' => true,'fail'=>'']);

    }
	/* INCLUDE RELATED */
	
	/* NEED RELATED */	
	public function addNeed(Request $request){
		Session::put('adminmenu','services');
		Session::put('adminsubmenu','need');
		
		$isact	=	DB::table('mainmenu_tbl')->where('menuid','=',27)->value('isactive');
		if($isact==0)
		{
			return redirect('application/admin/dashboard');
		}
		
		$displayorder = DB::table('need_tbl')
					->orderBy('displayorder', 'desc')
					->value('displayorder');
		$displayorder	=	intval($displayorder)+1;	

		Session::put('menid',27);
		$userId		=	$request->session()->get('loginId');
		$issuper	=	$request->session()->get('issuper');
		if($issuper==0)
		{
			$action		=	DB::table('menu_permission')
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
		$category = DB::table('category_tbl')
						->whereNotIn('categoryid', function ($query) {
							$query->select('parentcategoryid')->from('category_tbl');
						})
						->orderBy('displayorder')
						->get();
		return view('admin/master/need_add',compact('displayorder','category'));
	}
	public function storeNeed(Request $request,$recordid)
	{        
		$rules = [
			'categoryid' => 'required',
			'need' => 'required|array',
			'need.*' => 'nullable|string|max:250',
			
		];

		$messages = [
			'categoryid.required' => __('validation.thisis.required'),
			'need.required' => __('validation.thisis.required'),
			'need.min' => __('validation.thisismin1.required'),
			'need.*.max' => __('validation.thisis250.max'),
		];

		$validatedData 	= $request->validate($rules, $messages);

		$needs = $request->input('need');
		if (empty(array_filter($needs))) {  // Check if all values are blank
			return back()->withErrors([
				'need' => __('validation.thisismin1need.required'),
			])->withInput();
		}
		
		$userId     	=	$request->session()->get('loginId');
		$userName   	=	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$categoryid	=	intval($request->input('categoryid'));

		if($recordid==0)
		{
			try 
			{
				$displayorder = intval($this->displayOrderService->getLastDisplayOrder($categoryid,'need_tbl','displayorder','categoryid'));
				$i=$displayorder;
				foreach ($request->input('need') as $inp) 
				{
					if($inp!='')
					{
						$i++;
					
						DB::insert('INSERT INTO need_tbl(categoryid,need,displayorder,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)',[$categoryid,$inp,$i,$userId,$userName,$creationdate]);
					}
				}
				
				return back()->with('success',__('messages.stored'));
			}
			catch (QueryException $e) 
			{
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
		else
		{
			$data = DB::table('need_tbl')->where('needid',$recordid)->first();
			$updateData = [];
			if(!empty($need))
			{
				$updateData['need'] = $need;
			}
			if(!empty($displayorder))
			{
				$updateData['displayorder'] = $displayorder;
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
						DB::table('need_tbl')
							->where('displayorder', '>', $oldDisplayOrder)
							->where('displayorder', '<=', $newDisplayOrder)
							->where('needid', '!=', $recordid)
							->decrement('displayorder');
					}
					else
					{
						DB::table('need_tbl')
							->where('displayorder', '>=', $newDisplayOrder)
							->where('displayorder', '<', $oldDisplayOrder)
							->where('needid', '!=', $recordid)
							->increment('displayorder');
					}
					});				
				}
				
				$res = DB::table('need_tbl')->where('needid', $recordid)->update($updateData);
				return redirect('master/add/needin')->with('success','NEED UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'VEHICLE BRAND IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$e->getMessage())->withInput();
			}
		}
	}
	public function getNeedData(Request $request)
	{
		$categoryid	=	intval($request->input('categoryid'));
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
		//$perPage = $request->input('perPage', 10);
		$data = DB::table('need_tbl as a')
				->select('a.*','b.category')
				->leftjoin('category_tbl as b','b.categoryid','=','a.categoryid')
				->orderBy('a.displayorder')
				->when($categoryid!=0,function($query) use ($categoryid){
					return $query->where('a.categoryid','=',$categoryid);
				})
				->when($pagesearch!=0,function($query) use ($pagesearch){
					return $query->where('a.need','like','%'.$pagesearch.'%');
				})
				->paginate($pagesize,['*'],'page',$currentPage);
		
		return view('admin/ajaxpages/needTable', ['data' => $data]);

	}
	public function editNeed($recordid)
	{
		$data = DB::table('need_tbl')->where('needid','=',Crypt::decrypt($recordid))->first();
		return view('admin/master/need_edit',compact('data'));
	}
	public function deleteNeed($recordid)
	{
		$res = DB::delete('DELETE FROM need_tbl WHERE needid=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
	}
    public function updateNeed(Request $request)
	{
		$needid 		=	$request->input('needid');		
		$needid			=	Crypt::decrypt($needid);
		$need 			=	(String) $request->input('need');
		$displayorder 	=	intval($request->input('displayorder'));

	
		$updated = DB::table('need_tbl')->where('needid', $needid)->update(['need' => $need,'displayorder'=>$displayorder]);
		return response()->json(['success' => true,'fail'=>'']);

    }
	
	/* NEED RELATED */

	/* CATEGORY RELATED START */
    public function addCategory(Request $request){
        Session::put('adminmenu','services');
		Session::put('adminsubmenu','addcategory');
		Session::put('menid',24);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
						->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=24 and ispermitted=1 and userid=".$userId.") as actions"))
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
		$category 		= DB::table('category_tbl')					
							->select('*')
							->where('parentcategoryid','=',0)
							->orderBy('displayorder')
							->get();
		$displayorder 	= DB::table('category_tbl')
							->orderBy('displayorder', 'desc')
							->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
		
        return view('admin/master/category_add',compact('category','displayorder'));
    }
    public function storeCategory(Request $request,$recordid)
	{    
        
        $rules = [
            'category' 		=> 'required|max:50',
			'headingvalue' 	=> 'nullable|max:300',
            'displayorder' 	=> 'required',
			'categoryicon' 	=> ($recordid == 0) ? 'required|max:512' : 'nullable|max:512',
			'categorypage' 	=> 'nullable|file|mimes:jpg,png,gif|max:500',
        ];

        $messages = [
            'category.required' => __('validation.thisis.required'),
            'category.max' => __('validation.thisis50.max'),
			'headingvalue.max' => __('validation.thisis300.max'),
            'displayorder.required' => __('validation.thisis.required'),
			'categoryicon.required' => __('validation.thisis.required'),
			'categorypage.max' => __('validation.thisis500kb.max'),
        ];

        $validatedData = $request->validate($rules, $messages);

        $categoryid   	= intval($request->input('categoryid'));
		$category   	= strtoupper($request->input('category'));
        $displayorder 	= intval($request->input('displayorder'));
		$categorystatus	= intval($request->input('categorystatus'));
		$description   	= (String) $request->input('description');
		$metakeywords  	= (String) $request->input('metakeywords');
		$metadescription= (String) $request->input('metadescription');
		
		$headingvalue	= (String) $request->input('headingvalue');

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');

        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$categoryicon    = (String) $request->file('categoryicon');		
		$categorypage    = (String) $request->file('categorypage');

		$icontype = "";
		$depthlevel	=	0;
		
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
			try 
			{
				if($categoryid!=0)
				{
					$cat	=	DB::table('category_tbl')->where('categoryid','=',$categoryid)->first();
					$depthlevel	=	intval($cat->depthlevel)+1;
				}
				$res = DB::insert('INSERT INTO category_tbl (parentcategoryid,category,headingvalue,displayorder,categorystatus,description,metakeywords,metadescription,categoryicon,categorypage,icontype,pagetype,depthlevel,creationdate,createdby,createdbyname,activity) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$categoryid,$category,$headingvalue,$displayorder,$categorystatus,$description,$metakeywords,$metadescription,$categoryicon,$categorypage,'IMAGE','IMAGE',$depthlevel,$creationdate,$userId,$userName,'']);
				Artisan::call('cache:clear');
				return back()->with('success',__('messages.stored'));
			}
			catch(QueryException $e)
			{
				if($categoryicon!='')
				Storage::disk('public')->delete($categoryicon);
				if($categorypage!='')
				Storage::disk('public')->delete($categorypage);
				
				return back()->with('duplicate',$e->getMessage())->withInput();

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
			if(!empty($headingvalue)) {
				$updateData['headingvalue'] = $headingvalue;
			}
			if(!empty($displayorder)) {
				$updateData['displayorder'] = $displayorder;
			}
			if(!empty($categorystatus)) {
				$updateData['categorystatus'] = $categorystatus;
			}
			if(!empty($categoryicon)) {
				$updateData['categoryicon'] = $categoryicon;
			}
			if(!empty($categorypage)) {
				$updateData['categorypage'] = $categorypage;
			}
			$updateData['description'] = $description;
			$updateData['metadescription'] = $metadescription;
			$updateData['metakeywords'] = $metakeywords;
			$res = DB::table('category_tbl')->where('categoryid', $recordid)->update($updateData);
			if($res)
			{
				Artisan::call('cache:clear');
				return redirect('master/add/category')->with('success',__('messages.updated'));
			}
			else
			{
				return back()->with('fail',__('messages.duplicate'));
			}
		}
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
    public function editCategory($recordid)
	{
		$category = DB::table('category')
					->where('parentcategoryid',0)
					->where('categoryid','!=',Crypt::decrypt($recordid))
                    ->orderBy('displayorder')
                    ->get();
        $data = DB::table('category_tbl')->where('categoryid','=',Crypt::decrypt($recordid))->first();
        return view('admin/master/category_edit',compact('data','category'));

    }
    public function deleteCategory($recordid)
	{
		$content 	= DB::table('category_tbl')->where('categoryid','=',Crypt::decrypt($recordid))->first();		
		$hasChild	= DB::table('category_tbl')->where('parentcategoryid','=',Crypt::decrypt($recordid))->exists();		
		if($hasChild)
		{
			return response()->json(['fail'=>'SUB CATEGORY AVAILABLE. IT CAN NOT BE DELETED.']);
		}
		else
		{
			$hasServices	= DB::table('service_tbl')->where('categoryid','=',Crypt::decrypt($recordid))->exists();
			if($hasServices)
			{
				return response()->json(['fail'=>'SERVICES AVAILABLE UNDER THIS SUB CATEGORY. IT CAN NOT BE DELETED.']);
			}
		}
		if($content->categoryicon!='')
		{
			Storage::disk('public')->delete($content->categoryicon);
		}
		if($content->categorypage!='')
		{
			Storage::disk('public')->delete($content->categorypage);
		}
		
		$res = DB::delete('delete from category_tbl WHERE categoryid=?',[Crypt::decrypt($recordid)]);

		if($res)
		{
			Artisan::call('cache:clear');
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }

    public function getSubCategoryList(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));
		$objectiveid = intval($request->input('objectiveid'));
		
		$subIds 	= DB::table('payout_structure')
						->where('categoryid', $categoryid)
						->where('objectiveid', $objectiveid)
						->pluck('subcategoryid')
						->toArray();
		
        $data 	=DB::table('category_tbl')
                ->select('categoryid','category')
                ->when(count($subIds)!=0,function($query) use ($subIds){
                    return $query->whereNotIn('categoryid',$subIds);
                })
                ->where('parentcategoryid','=',$categoryid)				
                ->orderby('category')
                ->get();        
        return view('/admin/ajaxpages/subcategorylistTable', compact('data'));
    }

    public function getSubCategoryOption(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));
	
	
        $data 	=DB::table('category_tbl')
                ->select('categoryid as value','category as label')
                ->where('parentcategoryid','=',$categoryid)				
                ->orderby('category')
                ->get();        
        return response()->json($data);
    }
	
	/* CATEGORY RELATED CLOSED */

}
