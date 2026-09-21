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

class UnitController extends Controller
{
    public function addUnit(Request $request){        
        Session::put('adminmenu','settings');
		Session::put('adminsubmenu','addunit');

		Session::put('menid',27);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
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

        return view('admin/master/unit_add');
    }
    public function storeUnit(Request $request,$recordid){        
        
        $rules = [
			'unitname' => 'required|max:30',
        ];

        $messages = [
            'unitname.required' => 'UNIT NAME IS REQUIRED',
			'unitname.max' => 'MAXIMUM LENGTH REACHED',
        ];

        $validatedData = $request->validate($rules, $messages);

        $userId      	= $request->session()->get('loginId');
        $userName      	= $request->session()->get('userId');
        $currentDateTime= now();
        $creationdate  	= $currentDateTime->format('Y-m-d H:i:s');

		$unitname  	= strtoupper($request->input('unitname'));

		if($recordid==0)
		{
			try 
			{
				$res = DB::insert('insert into unit_tbl (unitname,creationdate,createdby,createdbyname) values (?,?,?,?)',[$unitname,$creationdate,$userId,$userName]);

				return back()->with('success','UNIT NAME STORED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'UNIT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
		}
		else
		{
			$data = DB::table('unit_tbl')->where('unitid',$recordid)->first();
			$updateData = [];
			if(!empty($unitname))
			{
				$updateData['unitname'] = $unitname;
			}
			try
			{
				$res = DB::table('unit_tbl')->where('unitid', $recordid)->update($updateData);
				return redirect('master/add/unit')->with('success','UNIT NAME UPDATED SUCCESSFULLY!');
			}
			catch (QueryException $e) 
			{
				if ($e->getCode() == 23000) 
				{ 
					$errorMessage = 'UNIT NAME IS ALREADY REGISTERED. PLEASE CHECK AND TRY AGAIN1.';
				}
				return back()->with('duplicate',$errorMessage)->withInput();
			}
			
		}
    }
    public function getUnitData(Request $request)
	{
		$pagesearch 	=	$request->input('pagesearch');
		$pagesize		=	$request->input('pagesize');
		$currentPage 	= $request->input('page', 1);
        $data = DB::table('unit_tbl')
				->select('*')
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('unitname','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);
	
		return view('/admin/ajaxpages/unitTable', compact('data'))->render();
    }
    public function editUnit($recordid)
	{
        $data = DB::table('unit_tbl')->where('unitid','=',$recordid)->first();
        return view('admin/master/unit_edit',compact('data'));
    }
    public function deleteUnit($recordid)
	{
		$res = DB::delete('delete from unit_tbl WHERE unitid=?',[$recordid]);
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
