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
use App\Mail\OTPEmail;
use App\Mail\ForgotPassword;
use Pusher\Pusher;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use App\Services\DisplayOrderService;
use Illuminate\Database\QueryException;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\SmsService;
use App\Services\FCMService;
use Razorpay\Api\Api;
use Illuminate\Support\Facades\Cache;
class ApiController extends Controller
{
/*	
	public function __construct()
	{		
	}	
*/
	protected $displayOrderService;
	protected $smsService;
    protected $razorpay;
	protected $appUrl;
	protected $fcm;
	
    public function __construct(DisplayOrderService $displayOrderService,SmsService $smsService)
    {
        $this->displayOrderService = $displayOrderService;
		$this->appUrl 		=	Config::get('app.url');
		$this->smsService 	=	$smsService;
		$this->razorpay 	=	new Api(env('RAZORPAY_KEY'), env('RAZORPAY_SECRET'));
		$this->fcm			=	new FCMService();
		ini_set('serialize_precision', -1);
		
    }
	

	private function buildMenuTree($menus, $parentId = null)
    {
        $branch = [];

        foreach ($menus as $menu) {
			
            if ($menu->parentid == $parentId) {
                $children = $this->buildMenuTree($menus, $menu->menuid);
                if ($children) {
                    $menu->children = $children;
                }
                $branch[] = $menu;
            }
        
		}
        return $branch;
    }
	private function CheckUser($userid,$accesstoken)
	{
		$user	=	DB::table('applicationusers')->where('userid','=',$userid)->where('accesstoken','=',$accesstoken)->where('isactive','=',1)->first();
		
		return $user;
	}
	/* NEED RELATED APIS */
    public function storeNeed(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
            'accesstoken' 	=> 'required',
			'categoryid' 	=> 'required',
			'need' 			=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'categoryid.required' 	=> __('validation.thisis.required'),
            'need.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$categoryid	=	intval($request->input('categoryid'));
			$need		=	strtoupper($request->input('need'));
			try
			{
				$displayorder = intval($this->displayOrderService->getLastDisplayOrder($categoryid,'need_tbl','displayorder','categoryid'));
				
				DB::insert('INSERT INTO need_tbl(categoryid,need,displayorder,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)',[$categoryid,$need,$displayorder,$userid,$result->name,$creationdate]);
				
				return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	
	/* NEED RELATED APIS CLOSED */
	/* CATEGORY RELATED APIS */
    public function storeCategory(Request $request)
	{        
        $rules = [
            'category' => 'required|max:50',
			'headingvalue' => 'nullable|max:300',
            'displayorder' => 'required',
			'categoryicon' => ($request->input('catid') == 0) ? 'required|max:512' : 'nullable|max:512',
			'categorypage' => 'nullable|file|mimes:jpg,png,gif|max:500',
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

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		$result		=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$categoryid   	= intval($request->input('categoryid'));
			$category   	= strtoupper($request->input('category'));
			$displayorder 	= intval($request->input('displayorder'));
			$categorystatus	= intval($request->input('categorystatus'));
			$description   	= (String) $request->input('description');
			$metakeywords  	= (String) $request->input('metakeywords');
			$metadescription= (String) $request->input('metadescription');
			
			$headingvalue	= (String) $request->input('headingvalue');

			$categoryicon   = (String) $request->file('categoryicon');		
			$categorypage   = (String) $request->file('categorypage');

			$icontype 	= 	"";
			$depthlevel	=	0;
			
			$catid			= intval($request->input('catid'));

			if($catid==0)
			{
				try
				{
					if($categoryicon!='')
					{
						$categoryicon= $request->file('categoryicon')->store('uploads/categoryicons', 'public');
					}

					if($categorypage!='')
					{
						$categorypage= $request->file('categorypage')->store('uploads/categorypage', 'public');
					}			
					if($categoryid!=0)
					{
						$cat	=	DB::table('category_tbl')->where('categoryid','=',$catid)->first();
						$depthlevel	=	intval($cat->depthlevel)+1;
					}
					
					DB::insert('INSERT INTO category_tbl (parentcategoryid,category,headingvalue,displayorder,categorystatus,description,metakeywords,metadescription,categoryicon,categorypage,icontype,pagetype,depthlevel,creationdate,createdby,createdbyname,activity) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)', [$categoryid,$category,$headingvalue,$displayorder,$categorystatus,$description,$metakeywords,$metadescription,$categoryicon,$categorypage,'IMAGE','IMAGE',$depthlevel,$creationdate,$userid,$result->name,'']);

					return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					if($categoryicon!='')
					Storage::disk('public')->delete($categoryicon);
					if($categorypage!='')
					Storage::disk('public')->delete($categorypage);
					
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
			else
			{
				try
				{
					$cont = DB::table('category_tbl')->where('categoryid',$catid)->first();
					if($cont)
					{
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
						if(!empty($description)) {
							$updateData['description'] = $description;
						}
						if(!empty($metakeywords)) {
							$updateData['metakeywords'] = $metakeywords;
						}
						if(!empty($metadescription)) {
							$updateData['metadescription'] = $metadescription;
						}
						$res = DB::table('category_tbl')->where('categoryid', $catid)->update($updateData);

						return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
					}
					else
					{
						return response()->json(['message' =>__('messages.notfound'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
					}
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}		
    }
    public function deleteCategory(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result		=	$this->CheckUser($userid,$accesstoken);
		if($result)
		{
			try
			{
				$categoryid		=	$request->input('categoryid');
				$content 		= DB::table('category_tbl')->where('categoryid','=',$categoryid)->first();
				if($content)
				{
					if($content->categoryicon!='')
					{
						Storage::disk('public')->delete($content->categoryicon);
					}
					if($content->categorypage!='')
					{
						Storage::disk('public')->delete($content->categorypage);
					}
					
					$res = DB::delete('DELETE FROM category_tbl WHERE categoryid=?', [$categoryid]);

					if($res)
					{
						return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
					}
					else
					{
						return response()->json(['message' =>__('messages.notfound'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
					}
				}
				else
				{
					return response()->json(['message' =>__('messages.notfound'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}

    }
	
	/* CATEGORY RELATED APIS CLOSED */
	/* TAX RELATED APIS */
    public function storeTax(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'taxname' 		=> 'required|max:30',
			'taxtype' 		=> 'required',
			'taxrate' 		=> ($request->input('taxtype') == 'SINGLE') ? 'required' : 'nullable',
			'taxrates' 		=> ($request->input('taxtype') == 'MULTIPLE') ? 'array|min:2' : 'nullable',
        ];

        $messages = [
            'userid.required' 		=> 'USER ID IS MANDATORY',
			'accesstoken.required' 	=> 'ACCESS TOKEN IS MANDATORY',
			'taxname.required' 		=> 'IT IS REQUIRED',
			'taxname.max' 			=> __('validation.thisis.max'),
			'taxtype.required' 		=> __('validation.thisis.required'),
			'taxrate.required' 		=> __('validation.thisis.required'),
			'taxrate.required' 		=> __('validation.thisis.required'),
			'taxrates.array' 		=> __('validation.thisis.arrayonly'),
			'taxrates.min' 			=> __('validation.thisis.min2tax'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		$result		=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$taxname  	= strtoupper($request->input('taxname'));
			$taxtype  	= strtoupper($request->input('taxtype'));
			$taxrate  	= doubleval($request->input('taxrate'));
			$taxrates  	= $request->input('taxrates');
			$taxnames  	= $request->input('taxnames');
			
		
			$taxid  	= intval($request->input('taxid'));
			if($taxid!=0)
			{
				$dt = DB::table('tax_tbl')->where('taxid','=',$taxid)->first();
				if($dt->taxtype!=$taxtype)
				{
					return response()->json(['message' =>__('messages.invalidtax'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
				}
			}
			
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
					return response()->json(['message' =>__('messages.multipletax'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
				}
			}

			if($taxid==0)
			{
				try 
				{
					DB::insert('insert into tax_tbl(taxname,taxtype,taxrate,createdby,createdbyname,creationdate,tempname) values (?,?,?,?,?,?,?)',[$taxname,$taxtype,$taxrate,$userid,$result->name,$creationdate,$taxname]);
					if($taxtype=='MULTIPLE')
					{
						$id = DB::getPdo()->lastInsertId();
						$totalrate	=	0;
						$displayname=	"";
						foreach($taxnames as $key=>$records)
						{
							DB::insert('insert into tax_multiple(taxid,taxname,taxrate,createdby,createdbyname,creationdate) values(?,?,?,?,?,?)',[$id,$taxnames[$key],$taxrates[$key],$userid,$result->name,$creationdate]);
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
					return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch (QueryException $e) 
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);					
				}
			}
			else
			{
				$data = DB::table('tax_tbl')->where('taxid',$taxid)->first();
				$updateData = [];
				if(!empty($taxname))
				{
					$updateData['taxname'] = $taxname;
				}
				if(!empty($taxname))
				{
					$updateData['tempname'] = $taxname;
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
					DB::table('tax_tbl')->where('taxid', $taxid)->update($updateData);
					$data = DB::table('tax_tbl')->where('taxid',$taxid)->first();
					if($taxtype=='MULTIPLE')
					{
						DB::delete('delete from tax_multiple where taxid=?',[$taxid]);
						$totalrate	=	0;
						$displayname=	"";
						foreach($taxnames as $key=>$records)
						{
							DB::insert('insert into tax_multiple(taxid,taxname,taxrate,createdby,createdbyname,creationdate) values(?,?,?,?,?,?)',[$taxid,$taxnames[$key],$taxrates[$key],$userid,$result->name,$creationdate]);
							$totalrate	=	$totalrate+$taxrates[$key];
							$displayname.= $taxnames[$key]." (".$taxrates[$key]."%)<br>";
						}
						$taxname	=	$data->tempname." (".$totalrate."%)";
						DB::update('update tax_tbl set taxname=?,displayname=?,taxrate=? where taxid=?',[$taxname,$displayname,$totalrate,$taxid]);
					}
					
					return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch (QueryException $e) 
				{
					return response()->json(['message' =>$e->getMessage(),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}				
    }
	public function taxList(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			try
			{
				$searchtext 	=	$request->input('searchtext');

				$taxlist = DB::table('tax_tbl')
						->select('taxid','taxname','displayname','taxtype','taxrate')
						->when($searchtext!='',function($query) use ($searchtext){
							return $query->where('taxname','like','%'.$searchtext.'%');
						})
						->get();
				
				return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'taxlist'=>$taxlist], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}		

	}
    public function deleteTax(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'taxid' 		=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'taxid.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$taxid	=	intval($request->input('taxid'));
			try
			{
				$res = DB::delete('delete from tax_tbl WHERE taxid=?',[$taxid]);
				if($res)
				{
					DB::delete('delete from tax_multiple WHERE taxid=?',[$taxid]);
					return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				else
				{
					return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	
	/* TAX RELATED APIS CLOSED */
	/* CITY BANNER RELATED APIS */
    public function storeCityBanner(Request $request)
	{        
        $rules = [
            'cityid' 		=> 'required',
			'isactive' 		=> 'required|numeric',
			'sliderimage' 	=> 'required|array|min:1',
			'sliderimage.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:500',
			'sliderimage' 	=> (intval($request->input('citysliderid')) == 0) ? 'required|array|min:1' : 'nullable',
			'sliderimage.*' => (intval($request->input('citysliderid')) == 0) ? 'image|mimes:jpeg,png,jpg,gif,svg|max:500' : 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:500',
        ];

        $messages = [
            'cityid.required' => __('validation.thisis.required'),
			'isactive.required' => __('validation.thisis.required'),
			'isactive.numeric' => __('validation.thisis.numeric'),
			'sliderimage.min' => __('validation.thisismin1.required'),
			'sliderimage.required' => __('validation.thisismin1.required'),
			'sliderimage.*.max' => __('validation.thisis500kb.max'),
			'sliderimage.*.image' => __('validation.thisisimage.required'),
			'sliderimage.*.mimes' => __('validation.thisismimes.required'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		$result		=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$cityid			=	intval($request->input('cityid'));
			$serviceid		=	intval($request->input('serviceid'));
			$isactive		=	intval($request->input('isactive'));
			$citysliderid	=	intval($request->input('citysliderid'));

			if($citysliderid==0)
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
							
							DB::insert('INSERT INTO cityslider_tbl(cityid,serviceid,sliderimage,displayorder,isactive,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?,?,?)',[$cityid,$serviceid,$path,$i,$isactive,$userid,$result->name,$creationdate]);
						}
					}

					return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
			else
			{
				try
				{
					$sliderimage    = (String) $request->file('sliderimage');	
					$cont = DB::table('cityslider_tbl')->where('citysliderid',$citysliderid)->first();
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

					DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->update($updateData);

					return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}		
    }

	public function cityBannerList(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			try
			{
				$cityid 		=	intval($request->input('cityid'));
				$searchtext 	=	$request->input('searchtext');

				$bannerlist		= 	DB::table('cityslider_tbl as a')
									->select('a.citysliderid','a.cityid','a.serviceid','a.sliderimage','a.displayorder','a.isactive','b.cityname','c.servicetitle')
									->leftjoin('city_tbl as b','b.cityid','=','a.cityid')
									->leftjoin('service_tbl as c','c.serviceid','=','a.serviceid')
									->when($cityid!=0,function($query) use ($cityid){
										return $query->where('a.cityid','=',$cityid);
									})
									->when($searchtext!=0,function($query) use ($searchtext){
										return $query->where('b.cityname','like','%'.$searchtext.'%')
													 ->where('c.servicetitle','like','%'.$searchtext.'%');
									})
									->orderBy('a.displayorder')
									->get();
				
				$appUrl = Config::get('app.url')."/storage/";
				return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'appurl'=>$appUrl,'bannerlist'=>$bannerlist], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}		

	}
    public function setBannerStatus(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		if($result)
		{
			try
			{
				$citysliderid 	=	$request->input('citysliderid');

				$currentStatus 	= DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->value('isactive');
				$newStatus 		= $currentStatus == 1 ? 0 : 1;				
				$updated 		= DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->update(['isactive' => $newStatus]);

				return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
		
		return response()->json(['success' => true,'fail'=>'']);

    }
    public function setBannerDisplayOrder(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		if($result)
		{
			try
			{		
				$citysliderid 	=	$request->input('citysliderid');
				$val 			=	$request->input('displayorder');

				DB::table('cityslider_tbl')->where('citysliderid', $citysliderid)->update(['displayorder' => $val]);
				return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
			
    }
    public function deleteBanner(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		if($result)
		{
			try
			{
				$citysliderid		=	$request->input('citysliderid');
				$content = DB::table('cityslider_tbl')->where('citysliderid','=',$citysliderid)->first();
				if($content->sliderimage!='')
				{
					Storage::disk('public')->delete($content->sliderimage);
				}
				
				$res = DB::delete('DELETE FROM cityslider_tbl WHERE citysliderid=?', [$citysliderid]);

				if($res)
				{
					return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				else
				{
					return response()->json(['message' =>__('messages.notfound'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}

    }
	
	/* CITY BANNER APIS CLOSED */
	/* CITY RELATED APIS */
    public function storeCity(Request $request)
	{        
        $rules = [
            'stateid' 	=> 'required',
			'cityname' 	=> 'required|max:50',
			'aliasname' => 'required|max:10',
			'tierid'  	=> 'required_if:operatingstatus,1'
        ];

        $messages = [
            'stateid.required' 		=> __('validation.thisis.required'),
            'cityname.required' 	=> __('validation.thisis.required'),
            'cityname.max' 			=> __('validation.thisis.max'),
            'aliasname.required'	=> __('validation.thisis.required'),
            'aliasname.max' 		=> __('validation.thisis.max'),
			'tierid.required_if' 	=> __('validation.tiername.required'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result		=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$stateid		=	intval($request->input('stateid'));
			$tierid			=	intval($request->input('tierid'));
			$cityname		=	strtoupper($request->input('cityname'));
			$aliasname		=	strtoupper($request->input('aliasname'));
			$cityid			=	intval($request->input('cityid'));

			if($cityid==0)
			{
				try
				{
					DB::insert('INSERT INTO city_tbl(stateid,tierid,cityname,aliasname,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?,?)',[$stateid,$tierid,$cityname,$aliasname,$userid,$result->name,$creationdate]);

					return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
			else
			{
				try
				{
					DB::update('update city_tbl set stateid=?,tierid=?,cityname=?,aliasname=? where cityid=?',[$stateid,$tierid,$cityname,$aliasname,$cityid]);


					return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
    public function cityList(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			try
			{
				$stateid 			=	intval($request->input('stateid'));
				$tierid 			=	intval($request->input('tierid'));
				$operatingstatus	=	$request->input('operatingstatus');
				$searchtext 		=	(String) $request->input('searchtext');

				$citylist = DB::table('city_tbl as a')
							->select('a.cityid','a.cityname','a.aliasname','a.isdefault','a.operatingstatus','a.createdbyname','a.creationdate','b.statename','c.tiername','a.tierid','a.stateid')
							->leftjoin('state_tbl as b','b.stateid','=','a.stateid')
							->leftjoin('tier_tbl as c','c.tierid','=','a.tierid')
							->when($stateid!=0,function($query) use ($stateid){
								return $query->where('a.stateid','=',$stateid);
							})
							->when($tierid!=0,function($query) use ($tierid){
								return $query->where('a.tierid','=',$tierid);
							})
							->when($operatingstatus!='',function($query) use ($operatingstatus){
								return $query->where('a.operatingstatus','=',$operatingstatus);
							})
							->when($searchtext!='',function($query) use ($searchtext){
								return $query->where('a.cityname','like','%'.$searchtext.'%')
											 ->orwhere('a.aliasname','like','%'.$searchtext.'%');
							})
							->orderBy('a.cityname')
							->get();

				return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'citylist'=>$citylist], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
    public function setDefaultCity(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'cityid' 		=> 'required|numeric',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'cityid.required' 	=> __('validation.thisis.required'),
			'cityid.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$cityid		=	$request->input('cityid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);

		if($result)
		{
			$isoperating	=	DB::table('city_tbl')->select('operatingstatus')->where('cityid','=',$cityid)->first();
			if($isoperating->operatingstatus==0)
			{
				return response()->json(['message' =>__('messages.notoperatingcity'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
			}
			$isslider		=	DB::table('cityslider_tbl')->where('cityid','=',$cityid)->get();
			
			if(count($isslider)>0)
			{
				$currentStatus 	= 	DB::table('city_tbl')->where('cityid', $cityid)->value('isdefault');
				$newStatus 		= 	$currentStatus == 1 ? 0 : 1;

				DB::table('city_tbl')->update(['isdefault' => 0]);
				DB::table('city_tbl')->where('cityid', $cityid)->update(['isdefault' => $newStatus]);

				return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			else
			{
				return response()->json(['message' =>__('messages.cityslidernot'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
		
    }
    public function setCityOperatingStatus(Request $request)
	{
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'cityid' 		=> 'required|numeric',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'cityid.required' 	=> __('validation.thisis.required'),
			'cityid.numeric' 	=> __('validation.thisis.numeric'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$cityid		=	$request->input('cityid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$checktier	=	DB::table('city_tbl')->where('cityid','=',$cityid)->first();
			if($checktier->tierid==0)
			{
				return response()->json(['message' =>__('messages.tiernotset'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}
			
			$isslider		=	DB::table('cityslider_tbl')->where('cityid','=',$cityid)->get();		
			if(count($isslider)>0)
			{
				$currentStatus 	= 	DB::table('city_tbl')->where('cityid', $cityid)->value('operatingstatus');
				$newStatus 		= 	$currentStatus == 1 ? 0 : 1;

				DB::table('city_tbl')->where('cityid', $cityid)->update(['operatingstatus' => $newStatus]);

				return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			else
			{
				return response()->json(['message' =>__('messages.cityslidernotforoperating'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
		
    }
	
    public function deleteCity(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'cityid' 		=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'cityid.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$cityid	=	intval($request->input('cityid'));
			try
			{
				DB::delete('delete from city_tbl where cityid=?',[$cityid]);
				return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	/* CITY RELATED APIS END */
	
	/* TIER RELATED APIS */
    public function storeTier(Request $request)
	{        
        $rules = [
            'tiername' 	=> 'required|max:50',
			'discount'=> 'required|numeric',
			'commission'=> 'required|numeric',
        ];

        $messages = [
            'tiername.required' 	=> __('validation.thisis.required'),
			'tiername.max' 			=> __('validation.thisis50.max'),
            'discount.required'	=> __('validation.thisis.required'),
			'discount.numeric'	=> __('validation.thisis.numeric'),
            'commission.required'	=> __('validation.thisis.required'),
			'commission.numeric'	=> __('validation.thisis.numeric'),
        ];

        $validatedData 	= 	$request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result		=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= 	now();
			$creationdate  	= 	$currentDateTime->format('Y-m-d H:i:s');

			$tiername	=	strtoupper($request->input('tiername'));
			$discount	=	doubleval($request->input('discount'));
			$commission	=	doubleval($request->input('commission'));
			$tierid		=	intval($request->input('tierid'));
			if($tierid==0)
			{
				try
				{
					DB::insert('INSERT INTO tier_tbl(tiername,discount,commission,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)',[$tiername,$discount,$commission,$userid,$result->name,$creationdate]);

					return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
			else
			{
				try
				{
					DB::update('update tier_tbl set tiername=?,discount=?,commission=? where tierid=?',[$tiername,$discount,$commission,$tierid]);

					return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
				}
				catch(QueryException $e)
				{
					return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
				}			
			}
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
    public function deleteTier(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'tierid' 		=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'tierid.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$tierid	=	intval($request->input('tierid'));
			try
			{
				DB::delete('delete from tier_tbl where tierid=?',[$tierid]);
				return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
    public function tierList(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			try
			{
				$searchtext=	$request->input('searchtext');
				if($searchtext=='')
				{
					$tierlist = DB::table('tier_tbl')->select('tierid','tiername','discount','commission')->orderby('tiername')->get();
					return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'tierlist'=>$tierlist], 201);
				}
				else
				{
				
					$tierlist = DB::table('tier_tbl')
						->select('tierid','tiername','discount','commission')
						->orderBy('tiername')
						->when($searchtext!=0,function($query) use ($searchtext){
							return $query->where('tiername','like','%'.$searchtext.'%');										 
						})
						->get();
					return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'tierlist'=>$tierlist], 201);
					
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	/* TIER RELATED APIS END */
	/* STATE RELATED APIS */
    public function storeState(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
            'statename' 	=> 'required|max:50',
			'aliasname' 	=> 'required|max:10',
			'accesstoken' 	=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'statename.required' 	=> __('validation.thisis.required'),
            'statename.max' 		=> __('validation.thisis50.max'),
            'aliasname.required' 	=> __('validation.thisis.required'),
            'aliasname.max' 		=> __('validation.thisis10.max'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$statename	=	strtoupper($request->input('statename'));
			$aliasname	=	strtoupper($request->input('aliasname'));
			try
			{
				DB::insert('INSERT INTO state_tbl(statename,aliasname,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?)', [$statename,$aliasname,$userid,$result->name,$creationdate]);
				return response()->json(['message' =>__('messages.stored'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }

    public function updateState(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
            'statename' 	=> 'required|max:50',
			'aliasname' 	=> 'required|max:10',
			'accesstoken' 	=> 'required',
			'stateid' 		=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'statename.required' 	=> __('validation.thisis.required'),
            'statename.max' 		=> __('validation.thisis50.max'),
            'aliasname.required' 	=> __('validation.thisis.required'),
            'aliasname.max' 		=> __('validation.thisis10.max'),
			'stateid.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$stateid	=	intval($request->input('stateid'));
			$statename	=	strtoupper($request->input('statename'));
			$aliasname	=	strtoupper($request->input('aliasname'));
			try
			{
				DB::update('update state_tbl set statename=?,aliasname=? where stateid=?',[$statename,$aliasname,$stateid]);
				return response()->json(['message' =>__('messages.updated'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.duplicate'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }

    public function deleteState(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'stateid' 		=> 'required',
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
			'stateid.required' 		=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			$stateid	=	intval($request->input('stateid'));
			try
			{
				DB::delete('delete from state_tbl where stateid=?',[$stateid]);
				return response()->json(['message' =>__('messages.deleted'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken], 201);
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	
    public function stateList(Request $request)
	{        
        $rules = [
			'userid' 		=> 'required',
			'accesstoken' 	=> 'required',
			'searchtext'	=> 'nullable'
        ];

        $messages = [
            'userid.required' 		=> __('validation.thisis.required'),
			'accesstoken.required' 	=> __('validation.thisis.required'),
        ];

        $validatedData = $request->validate($rules, $messages);

		$userid		=	$request->input('userid');
		$accesstoken=	$request->input('accesstoken');
		
		$result	=	$this->CheckUser($userid,$accesstoken);
		
		if($result)
		{
			$currentDateTime= now();
			$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

			try
			{
				$searchtext=	$request->input('searchtext');
				if($searchtext=='')
				{
					$statelist = DB::table('state_tbl')->select('stateid','statename','aliasname')->orderby('statename')->get();
					return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'statelist'=>$statelist], 201);
				}
				else
				{
					$statelist = DB::table('state_tbl')
						->orderBy('statename')
						->when($searchtext!=0,function($query) use ($searchtext){
							return $query->where('statename','like','%'.$searchtext.'%')
										 ->orwhere('aliasname','like','%'.$searchtext.'%');
						})
						->get();
					
					return response()->json(['message' =>__('messages.recordlist'),'status'=>201,'userid'=>$userid,'accesstoken'=>$accesstoken,'statelist'=>$statelist], 201);
				}
			}
			catch(QueryException $e)
			{
				return response()->json(['message' =>__('messages.notfound'),'status'=>400,'userid'=>$userid,'accesstoken'=>$accesstoken], 400);
			}			
		}
		else
		{
			return response()->json(['message' =>__('messages.unauthorized'),'status'=>401,'userid'=>$userid,'accesstoken'=>$accesstoken], 401);
		}
    }
	/* STATE RELATED APIS CLOSED */


	/* GENERAL APIS */
    public function generateOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobilenumber'  => 'required|regex:/^[1-9]\d{9}$/|digits:10',
        ]);

        $customMessages = [
            'mobilenumber.required' => __('validation.thisis.required'),
            'mobilenumber.regex' => __('validation.thisis.invalidmobile'),
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status'=>300], 422);
        }

        $mobilenumber   = $request->input('mobilenumber');
        try
        {
            $isexist = DB::table('applicationusers')
                        ->where('mobilenumber','=',$mobilenumber)
                        ->first();
            if($isexist)
            {
                $profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
                if($profilepic!='')
                {
                    $appUrl = Config::get('app.url');
                    $profilepic = $appUrl."".$profilepic;
                }

                if($isexist->isactive==0)
                {
                    return response()->json(['message' => __('messages.inactiveaccount'),'status'=>201,'mobilenumber'=>$mobilenumber], 201);
                }
                else
                {
					$otp	=	"555555";//rand(100000,999999);
					DB::update('update applicationusers set otp=? where userid=?',[$otp,$isexist->userid]);
					
					return response()->json(['message' => __('messages.otpgenerated'),'status'=>200,'mobilenumber'=>$mobilenumber], 200);
                }
            }
            else
            {                
                return response()->json(['message' => __('messages.invaliduser'),'status'=>300,'mobilenumber'=>$mobilenumber], 201);
            }

        }
        catch (QueryException $e) 
        {
            return back()->with('duplicate',$e->getMessage())->withInput();
        }
    }
    public function OtpVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobilenumber' 	=> 'required',
            'otp' 			=> 'required',
        ]);

        $customMessages = [
            'mobilenumber.required' => __('validation.thisis.required'),
            'otp.required' 			=> __('validation.thisis.required'),
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status'=>300], 422);
        }

        $mobilenumber   = $request->input('mobilenumber');
        $otp			= $request->input('otp');
        try
        {
            $isexist = DB::table('applicationusers')
                        ->where('mobilenumber','=',$mobilenumber)
                        ->where('otp','=',$otp)
                        ->first();
            if($isexist)
            {
                $profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
                if($profilepic!='')
                {
                    $appUrl = Config::get('app.url');
                    $profilepic = $appUrl."".$profilepic;
                }

                if($isexist->isactive==0)
                {
                    return response()->json(['message' =>__('messages.inactiveaccount'),'status'=>201,'userid'=>$isexist->userid], 201);
                }
                else
                {
				
                   return response()->json(['message' => __('messages.loginsuccess'),'status'=>200,'userid'=>$isexist->userid,'isactive'=>intval($isexist->isactive),'accesstoken'=>$isexist->accesstoken,'name'=>$isexist->name,'mobilenumber'=>$isexist->mobilenumber,'email'=>$isexist->email,'profilepic'=>$profilepic], 201);
                }
            }
            else
            {                
                return response()->json(['message' => __('messages.invalidmobileotp'),'status'=>300,'mobilenumber'=>$mobilenumber], 201);
            }

        }
        catch (QueryException $e) 
        {
            return back()->with('duplicate',$e->getMessage())->withInput();
        }
    }
	
    public function userLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required',
        ]);

        $customMessages = [
            'username.required' => __('validation.thisis.required'),
            'password.required' => __('validation.thisis.required'),
        ];

        $validator->setCustomMessages($customMessages);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors(),'status'=>300], 422);
        }

        $username   = $request->input('username');
        $password	= $request->input('password');
        try
        {
            $isexist = DB::table('applicationusers')
                        ->where('username','=',$username)
                        ->where('password','=',$password)
                        ->first();
            if($isexist)
            {
                $profilepic = $isexist->profilepic ? Storage::url($isexist->profilepic) : null;
                if($profilepic!='')
                {
                    $appUrl = Config::get('app.url');
                    $profilepic = $appUrl."".$profilepic;
                }

                if($isexist->isactive==0)
                {
                    return response()->json(['message' => 'YOUR ACCOUNT STATUS IS INACTIVE. PLEASE CONTACT SUPPORT TEAM OR YOUR BRANCH MANAGER.','status'=>201,'userid'=>$isexist->userid], 201);
                }
                else
                {
				
                   return response()->json(['message' => 'LOGGED IN SUCCESSFULLY','status'=>200,'userid'=>$isexist->userid,'isactive'=>intval($isexist->isactive),'accesstoken'=>$isexist->accesstoken,'name'=>$isexist->name,'mobilenumber'=>$isexist->mobilenumber,'email'=>$isexist->email,'profilepic'=>$profilepic,'hased'=>HASH::make($password)], 201);
                }
            }
            else
            {                
                return response()->json(['message' => 'INVALID USER NAME OR PASSWORD','status'=>300,'username'=>$username,'password'=>$password], 201);
            }

        }
        catch (QueryException $e) 
        {
            return back()->with('duplicate',$e->getMessage())->withInput();
        }
    }
	/* GENERAL APIS CLOSED */	


    public function getSlotTimes(Request $request)
	{
		$subcategoryid	=	intval($request->input('subcategoryid'));
		$requestdate	=	date('Y\-m\-d',strtotime($request->input('requestdate')));

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
				
				return response()->json(['message'=>'SLOT TIME','status'=>200,'slots'=>$slots,'subcategoryid'=>$subcategoryid,'requestdate'=>$requestdate], 200);
				
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

	public function handleDepositWebhook(Request $request)
	{
		$webhookSecret 	= 	env('DEPOSIT_HOOK_LIVE');
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

			$deposit		=	DB::table('security_deposit')->where('payment_link_id','=',$paymentLinkId)->first();
			if($deposit)
			{
				$vendor		=	DB::table('vendor_tbl')->where('vendorid','=',$deposit->vendorid)->first();
				
				$pdf 		=	PDF::loadView('pdf.depositreceipt',['vendor'=>$vendor,'receiptnumber'=>$deposit->receiptnumber,'receiptdate'=>$deposit->createdon,'amount'=>$deposit->amount]);
				$pdf->setPaper('A4','portrait');

				$res	=	DB::update('update security_deposit set paymentdate=?,razorpay_payment_id=?,razorpay_signature_id=?,paymentstatus=? where payment_link_id=?',[date('Y\-m\-d H:i:s'),$paymentId,$signature,'paid',$paymentLinkId]);
				if($res)
				{
					$pdf->save(storage_path('app/public/deposits/'.$deposit->receiptnumber.'.pdf'));
					
					$title	=	"Acknowledgment of Security Deposit Payment";
					$body 	=	"We acknowledge the receipt of the payment made towards the security deposit. Thank you for your prompt attention to this matter. Best regards, The Screw Driver";
					$this->fcm->sendNotification($vendor->fcmid,$title,$body);		
				}
				return response('Payment verified', 200);
			}
			
		}
		return response('Unhandled event', 200);
	}
	
	
	public function handelPanelCustomerOrder(Request $request)
	{
		$webhookSecret 	= 	env('PANEL_CUSTOMER_ORDER');
		$signature 		=	$request->header('X-Razorpay-Signature');
		$payload 		=	$request->getContent();

		if(!hash_equals(hash_hmac('sha256',$payload, $webhookSecret),$signature))
		{
			return response('Invalid signature', 400);
		}

		$data = json_decode($payload, true);

		if ($data['event']==='payment_link.paid')
		{
			$paymentLinkId	=	$data['payload']['payment_link']['entity']['id'];
			$paymentId 		=	$data['payload']['payment']['entity']['id'];
			$amount 		=	$data['payload']['payment']['entity']['amount'];
			
			$record		=	DB::table('customer_cart_order')->where('razorpay_paymentlink_id','=',$paymentLinkId)->first();
			if($record)
			{
				$cart 		= 	$this->getCartList($record->customerid);
				DB::transaction(function () use ($record,$cart,$paymentLinkId,$signature)
				{
					$now 			=	now();
					$creationDate 	= 	now()->format('Y-m-d H:i:s');
					$receiptid = DB::table('receipt_tbl')->insertGetId([
						'customerid'           => $record->customerid,
						'financialyear'        => $record->financialyear,
						'receiptnumber'        => $record->receiptnumber,
						'razorpay_payment_id'  => $paymentLinkId,
						'razorpay_signature'   => $signature,
						'netamount'            => $record->netpayable,
						'paymentstatus'        => 'paid',
						'paymentdatetime'      => $creationDate,
						'generateddate'        => $creationDate,
						'completeddate'        => $creationDate,
						'paymentmethod'		   => 'PAYMENTLINK',
						'paymentfile'		   => $record->paymentfile,
						'paymentremark'		   => $record->paymentremark,
					]);

		
					$orderid = DB::table('customer_order')->insertGetId([
						'customerid'     => $record->customerid,
						'servicedate'    => $record->servicedate,
						'slottime'       => $record->slottime,
						'addressid'      => $record->addressid,
						'servicecharge'  => (float)$cart->servicecharge,
						'discount'       => $record->discount,
						'visitingcharge' => $cart->visitingcharge,
						'visitingtax'    => $record->visitingtax,
						'totaltaxable'   => $cart->totaltaxable,
						'totaltaxvalue'  => $cart->totaltaxvalue,
						'netpayable'     => $cart->netpayable,
						'paid'           => $cart->netpayable,
						'couponcode'     => $cart->couponcode
					]);

					foreach ($cart->cartlist as $item) {
						DB::table('customer_order_detail')->insert([
							'orderid'         => $orderid,
							'customerid'      => $item->customerid,
							'categoryid'      => $item->categoryid,
							'serviceid'       => $item->serviceid,
							'optionid'        => $item->optionid,
							'servicecharge'   => $item->servicecharge,
							'discount'        => $record->discount,
							'visitingcharge'  => $record->visitingcharge,
							'taxable'         => $item->taxable,
							'taxvalue'        => $item->taxvalue,
							'payable'         => $item->payable,
							'quantity'        => $item->quantity,
							'addressid'       => $record->addressid,
							'slottime'        => $record->slottime,
							'paid'            => $item->payable,
							'servicedate'     => $record->servicedate,
							'paymentstatus'   => 1,
							'creationdate'    => $creationDate,
						]);
					}

					DB::table('receipt_tbl')
						->where('receiptid', $receiptid)
						->update(['orderid' => $orderid]);

					// Generate PDF
					$customer = DB::table('customer_tbl')->where('customerid', $record->customerid)->first();
					
					$services = DB::table('customer_order_detail as a')
						->select('a.quantity', 'a.payable', 'b.servicetitle','a.taxable','a.taxvalue')
						->leftJoin('services as b', function ($join) {
							$join->on('b.serviceid', '=', 'a.serviceid')
								 ->on('b.optionid', '=', 'a.optionid');
						})
						->where('orderid', $orderid)
						->get();

					$order	=	$record;
					$receipt=	DB::table('receipt_tbl')->where('receiptid', $receiptid)->first();
					$pdf 	=	PDF::loadView('pdf.customerreceipt',compact('customer', 'receipt', 'services','order'))->setPaper('A4', 'portrait');

					$filename = $record->receiptnumber . '.pdf';
					$pdf->save(storage_path('app/public/receipts/'.$filename));

					DB::table('receipt_tbl')->where('receiptid', $receiptid)->update([
						'receiptfile' => $filename
					]);

					DB::table('customer_cart')->where('customerid', $record->customerid)->delete();
					DB::table('customer_cart_order')->where('customerid', $record->customerid)->delete();

					$this->smsService->pushMessage($customer->mobilenumber, 0, 'BOOKING', 0, '');
				});
				
				return response('Payment verified', 200);
			}
			
		}
		return response('Unhandled event', 200);
		
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
	private function getDiscount()
	{
		$tierId 	= DB::table('city_tbl')->where('isdefault',1)->value('tierid');
		$cacheKey 	= "discount_tier_{$tierId}";
		$discount = Cache::remember($cacheKey, 60, function () use ($tierId) {
			return DB::table('tier_tbl')->where('tierid', $tierId)->value('discount');
		});		
		return $discount;
	}
	
}
