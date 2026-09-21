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
//use App\Events\OrderCreated;
class StateCityController extends Controller
{
    public function storeIncludeValue(Request $request)
	{        
        $rules = [
			'includevalue' => 'required|max:50',
        ];

        $messages = [
            'includevalue.required' => 'STATE NAME IS REQUIRED',
            'includevalue.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$includevalue	=	strtoupper($request->input('includevalue'));
		try
		{
			$insertedId = DB::table('include_tbl')->insertGetId([
				'serviceinclude' => $includevalue,
				'createdby' => $userId,
				'createdbyname' => $userName,
				'creationdate' => $creationdate,
			]);
			return response()->json(['success' => 'SERVICE INCLUDE POINT ADDEDD SUCCESSFULLY','lastid'=>$insertedId]);
		}
		catch (QueryException $e) 
		{
			if($e->getCode() == 23000)
			{ 
				$errorMessage = 'DUPLICATE VALUE.';
			}
			return response()->json(['error' => 'DUPLICATE VALUE']);
		}		
    }
	
    public function storeState(Request $request)
	{        
        $rules = [
			'statename' => 'required|max:50',
        ];

        $messages = [
            'statename.required' => 'STATE NAME IS REQUIRED',
            'statename.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$statename	=	strtoupper($request->input('statename'));
		try
		{
			$insertedId = DB::table('state_tbl')->insertGetId([
				'statename' => $statename,
				'createdby' => $userId,
				'createdbyname' => $userName,
				'creationdate' => $creationdate,
			]);
			//event(new OrderCreated(1));
			return response()->json(['success' => 'STATE NAME ADDEDD SUCCESSFULLY','lastid'=>$insertedId]);
		}
		catch (QueryException $e) 
		{
			if($e->getCode() == 23000)
			{ 
				$errorMessage = 'DUPLICATE STATE NAME.';
			}
			return response()->json(['error' => 'DUPLICATE STATE NAME']);
		}		
    }

    public function storeCity(Request $request)
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

		$stateid	=	intval($request->input('stateid'));
		$cityname	=	strtoupper($request->input('cityname'));
		try
		{
			//DB::insert('INSERT INTO state_tbl(statename,createdby,createdbyname,creationdate) VALUES (?,?,?,?)',[$statename,$userId,$userName,$creationdate]);
			$insertedId = DB::table('city_tbl')->insertGetId([
				'stateid' => $stateid,
				'cityname' => $cityname,
				'createdby' => $userId,
				'createdbyname' => $userName,
				'creationdate' => $creationdate,
			]);
			return response()->json(['success' => 'CITY NAME ADDEDD SUCCESSFULLY','lastid'=>$insertedId]);
		}
		catch (QueryException $e) 
		{
			if($e->getCode() == 23000)
			{ 
				$errorMessage = 'DUPLICATE CITY NAME.';
			}
			return response()->json(['error' => 'DUPLICATE CITY NAME']);
		}		
    }
    public function storeArea(Request $request)
	{        
        $rules = [
            'cityid' => 'required',
			'areaname' => 'required|max:50',
        ];

        $messages = [
            'cityid.required' => 'CITY NAME IS REQUIRED',
            'areaname.required' => 'AREA NAME IS REQUIRED',
            'areaname.unique' => 'DUPLICATE AREA NAME FOUND',
            'areaname.max' => 'MAXIMUM LENGTH IS 50',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$cityid	=	intval($request->input('cityid'));
		$areaname	=	strtoupper($request->input('areaname'));
		$stateid	=	DB::table('city_tbl')->select('stateid')->where('cityid','=',$cityid)->first();
		try
		{
			//DB::insert('INSERT INTO state_tbl(statename,createdby,createdbyname,creationdate) VALUES (?,?,?,?)',[$statename,$userId,$userName,$creationdate]);
			$insertedId = DB::table('area_tbl')->insertGetId([
				'cityid' => $cityid,
				'areaname' => $areaname,
				'createdby' => $userId,
				'createdbyname' => $userName,
				'creationdate' => $creationdate,
			]);
			return response()->json(['success' => 'AREA NAME ADDEDD SUCCESSFULLY','lastid'=>$insertedId]);
		}
		catch(QueryException $e)
		{
			if($e->getCode()==23000)
			{ 
				$errorMessage = 'DUPLICATE AREA NAME.';
			}
			return response()->json(['error' => 'DUPLICATE AREA NAME']);
		}		

    }

    public function getStateList(Request $request)
	{
        $statelist =DB::table('state_tbl')
					->select('stateid as value','statename as label')
					->orderby('statename')
					->get();        
        return response()->json($statelist);
    }

    public function getIncludeList(Request $request)
	{
        $includelist =DB::table('include_tbl')
					->select('includeid as value','serviceinclude as label')
					->orderby('serviceinclude')
					->get();        
        return response()->json($includelist);
    }

    public function getCityList(Request $request)
	{
        $stateid = intval($request->input('stateid'));
        $citylist =DB::table('city_tbl')
                ->select('cityid as value','cityname as label')
                ->where('stateid','=',$stateid)
                ->orderby('cityname')
                ->get();        
        return response()->json($citylist);
    }
    public function getSubCategoryList(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));
        $subcategorylist =DB::table('category_tbl')
                ->select('categoryid as value','category as label')
                ->where('parentcategoryid','=',$categoryid)
                ->orderby('category')
                ->get();        
        return response()->json($subcategorylist);
    }

    public function getProdList(Request $request)
	{
        $categoryid = intval($request->input('categoryid'));
        $prodlist =DB::table('service_tbl')
                ->select('serviceid as value','servicetitle as label')
                ->where('categoryid','=',$categoryid)
                ->orderby('servicetitle')
                ->get();        
        return response()->json($prodlist);
    }
    public function getAreaList(Request $request)
	{
        $cityid = intval($request->input('cityid'));
        $arealist =DB::table('area_tbl')
                ->select('areaid as value','areaname as label')
                ->where('cityid','=',$cityid)
                ->orderby('areaname')
                ->get();        
        return response()->json($arealist);
    }
    public function getAreaNameList(Request $request)
	{
        $cityid 	=	intval($request->input('cityid'));
        $data 	=	DB::table('area_tbl')
						->select('areaid','areaname')
						->where('cityid','=',$cityid)
						->orderby('areaname')
						->get();        
        return view('/admin/ajaxpages/areanameTable', compact('data'))->render();
    }

    public function getAreaNamesList(Request $request)
	{
        $cityid 	=	intval($request->input('cityid'));
        $arealist 	=	DB::table('area_tbl')
						->select('areaid as value','areaname as label')
						->where('cityid','=',$cityid)
						->orderby('areaname')
						->get();        
        return response()->json($arealist);
    }

    public function getSubHeadList(Request $request)
	{
        $headid = intval($request->input('headid'));
        $subheadlist =DB::table('expense_sub_head')
                ->select('subheadid as value','subhead as label')
                ->where('headid','=',$headid)
                ->orderby('subhead')
                ->get();        
        return response()->json($subheadlist);
    }
    
}
