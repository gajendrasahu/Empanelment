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
class OtherController extends Controller
{
	/* TAG SERVICES RELATED */
    public function addTagServices(Request $request)
	{
        Session::put('adminmenu','hometag');
		Session::put('adminsubmenu','tagservices');
		$tags	=	DB::table('tag_tbl')->orderby('displayorder')->get();
		$category	=	DB::table('category_tbl')->orderby('displayorder')->get();

		Session::put('menid',20);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=20 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/tagservices_add',compact('tags','category'));
    }
    public function storeTagServices(Request $request,$recordid)
	{        
        $rules = [
            'tagline' => 'required|max:50',
			'displayorder' => 'required',
			'isactive' => 'required',
        ];

        $messages = [
            'tagline.required' => 'TAG LINE IS REQUIRED',
            'displayorder.required' => 'IT IS REQUIRED',
            'isactive.required' => 'REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$tagline		=	strtoupper($request->input('tagline'));
		$displayorder	=	intval($request->input('displayorder'));
		$isactive		=	intval($request->input('isactive'));
		
		if($recordid==0)
		{
			$res = DB::insert('INSERT INTO tag_tbl(tagline,displayorder,isactive,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)', [$tagline,$displayorder,$isactive,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','TAG LINE STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','TAG LINE COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$newDisplayOrder = intval($request->input('displayorder'));
			$tag	=	DB::table('tag_tbl')->where('tagid','=',$recordid)->first();
			$oldDisplayOrder = $tag->displayorder;
			if($newDisplayOrder!=$oldDisplayOrder)
			{
				DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder, $recordid) {
				if($newDisplayOrder>$oldDisplayOrder)
				{
					DB::table('tag_tbl')
						->where('displayorder', '>', $oldDisplayOrder)
						->where('displayorder', '<=', $newDisplayOrder)
						->where('tagid', '!=', $recordid)
						->decrement('displayorder');
				}
				else
				{
					DB::table('tag_tbl')
						->where('displayorder', '>=', $newDisplayOrder)
						->where('displayorder', '<', $oldDisplayOrder)
						->where('tagid', '!=', $recordid)
						->increment('displayorder');
				}
				});				
			}
			
			$res = DB::update('update tag_tbl set tagline=?,displayorder=?,isactive=? where tagid=?',[$tagline,$displayorder,$isactive,$recordid]);
			
			if($res)
			{
				return redirect('/master/add/tag')->with('success','TAG NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','TAG NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }
    public function getTagServicesData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('tag_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('tagline','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);		
		return view('admin/ajaxpages/tagTable', ['data' => $data]);
    }
    public function loadServices(Request $request)
	{
		$tagid 		=	intval($request->input('tagid'));
		$categoryid =	intval($request->input('categoryid'));
		$pagesearch =	$request->input('pagesearch');
        $data = DB::table('service_tbl')
				->whereNotIn('serviceid', function($query) {
					$query->select('serviceid')
					->from('taglinking_tbl');
				})
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('categoryid','=',$categoryid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('servicetitle','like','%'.$pagesearch.'%');
                })
                ->orderBy('requiredtime')
				->get();		
		return view('admin/ajaxpages/loadserviceTable',compact('data','categoryid','tagid'));
    }
    public function mapServices(Request $request,$recordid)
	{        
        $rules = [
            'serviceid' => 'required',
			'tagid' => ['required', 'not_in:0'],
        ];

        $messages = [
            'serviceid.required' => 'IT IS REQUIRED',
            'tagid.required' => 'IT IS REQUIRED',
			'tagid.not_in' => 'TAG NAME IS REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');

		$tagid 		=	intval($request->input('tagid'));
		$serviceid	=	intval($request->input('serviceid'));
		$catid		=	intval($request->input('categoryid'));
		$category 	=	DB::table('service_tbl')->select('categoryid')->where('serviceid','=',$serviceid)->first();
		
		try
		{		
			$res = DB::insert('INSERT INTO taglinking_tbl(tagid,categoryid,serviceid,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)', [$tagid,$category->categoryid,$recordid,$userId,$userName,$creationdate]);
			return back()->with('success','TAG AND SERVICE MAPPED SUCCESSFULLY!')->with('tagid',$tagid)->with('categoryid',$catid);
		}
		catch (QueryException $e) 
		{
			if ($e->getCode() == 23000) 
			{ 
				$errorMessage = 'TAG AND SERVICE ALREADY MAPPED. PLEASE CHECK AND TRY AGAIN1.';
			}
			return back()->with('duplicate',$errorMessage)->withInput();
		}
    }
    public function underTagServices(Request $request)
	{
		$tagid 		=	intval($request->input('tagid'));
		$categoryid =	intval($request->input('categoryid'));
		$pagesearch =	$request->input('pagesearch');
        $data = DB::table('taglinking_tbl as a')
				->select('a.linkid','b.*')
				->leftjoin('service_tbl as b','b.serviceid','=','a.serviceid')
                ->when($tagid!=0,function($query) use ($tagid){
                    return $query->where('a.tagid','=',$tagid);
                })
                ->when($categoryid!=0,function($query) use ($categoryid){
                    return $query->where('a.categoryid','=',$categoryid);
                })
                ->when($pagesearch!='',function($query) use ($pagesearch){
                    return $query->where('b.servicetitle','like','%'.$pagesearch.'%');
                })
                ->orderBy('a.linkid')
				->get();		
		return view('admin/ajaxpages/undertagTable',compact('data','categoryid','tagid'));
    }
    public function editTagServices($recordid)
	{
        $data = DB::table('tag_tbl')->where('tagid','=',$recordid)->first();
        return view('admin/master/tag_edit',compact('data'));
    }
	
    public function deleteMapping($recordid)
	{
        $res = DB::delete('DELETE FROM taglinking_tbl WHERE linkid=?', [$recordid]);
        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* TAG SERVICES RELATED */
	
	/* TAG RELATED */
    public function setMenu(Request $request)
	{
	
        Session::put('adminmenu','setmenu');
        return view('admin/master/set_menu');
    }
    public function addTag(Request $request)
	{
		$displayorder = DB::table('tag_tbl')
                    ->orderBy('displayorder', 'desc')
                    ->value('displayorder');
		$displayorder	=	intval($displayorder)+1;
		
        Session::put('adminmenu','hometag');
		Session::put('adminsubmenu','addtag');

		Session::put('menid',19);
		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		if($issuper==0)
		{
			$action	= DB::table('menu_permission')
							->select(DB::raw("(SELECT GROUP_CONCAT(actionid) FROM menu_permission where menuid=19 and ispermitted=1 and userid=".$userId.") as actions"))
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

        return view('admin/master/tag_add',compact('displayorder'));
    }
    public function storeTag(Request $request,$recordid)
	{        
        $rules = [
            'tagline' => 'required|max:50',
			'displayorder' => 'required',
			'isactive' => 'required',
        ];

        $messages = [
            'tagline.required' => 'TAG LINE IS REQUIRED',
            'displayorder.required' => 'IT IS REQUIRED',
            'isactive.required' => 'REQUIRED',
        ];

        $validatedData = $request->validate($rules, $messages);
		$userId     =	$request->session()->get('loginId');
		$userName   =	$request->session()->get('userId');
		$currentDateTime= now();
		$creationdate  = $currentDateTime->format('Y-m-d H:i:s');
		
		$tagline		=	strtoupper($request->input('tagline'));
		$displayorder	=	intval($request->input('displayorder'));
		$isactive		=	intval($request->input('isactive'));
		
		if($recordid==0)
		{
			$res = DB::insert('INSERT INTO tag_tbl(tagline,displayorder,isactive,createdby,createdbyname,creationdate) VALUES (?,?,?,?,?,?)', [$tagline,$displayorder,$isactive,$userId,$userName,$creationdate]);

			if($res)
			{
				return back()->with('success','TAG LINE STORED SUCCESSFULLY!');				
			}
			else
			{
				return back()->with('fail','TAG LINE COULD NOT BE ADDED. PLEASEE CHECK AND TRY AGAIN!');
			}
		}
		else
		{
			$newDisplayOrder = intval($request->input('displayorder'));
			$tag	=	DB::table('tag_tbl')->where('tagid','=',$recordid)->first();
			$oldDisplayOrder = $tag->displayorder;
			if($newDisplayOrder!=$oldDisplayOrder)
			{
				DB::transaction(function () use ($oldDisplayOrder, $newDisplayOrder, $recordid) {
				if($newDisplayOrder>$oldDisplayOrder)
				{
					DB::table('tag_tbl')
						->where('displayorder', '>', $oldDisplayOrder)
						->where('displayorder', '<=', $newDisplayOrder)
						->where('tagid', '!=', $recordid)
						->decrement('displayorder');
				}
				else
				{
					DB::table('tag_tbl')
						->where('displayorder', '>=', $newDisplayOrder)
						->where('displayorder', '<', $oldDisplayOrder)
						->where('tagid', '!=', $recordid)
						->increment('displayorder');
				}
				});				
			}
			
			$res = DB::update('update tag_tbl set tagline=?,displayorder=?,isactive=? where tagid=?',[$tagline,$displayorder,$isactive,$recordid]);
			
			if($res)
			{
				return redirect('/master/add/tag')->with('success','TAG NAME UPDATED SUCCESSFULLY!');
			}
			else
			{
				return back()->with('fail','TAG NAME COULD NOT BE UPDATED. PLEASEE CHECK AND TRY AGAIN!');
			}			
		}
    }
    public function getTagData(Request $request)
	{
		$pagesearch =	$request->input('pagesearch');
		$pagesize	=	$request->input('pagesize');
		$currentPage = $request->input('page', 1);
        $data = DB::table('tag_tbl')
                ->orderBy('displayorder')
                ->when($pagesearch!=0,function($query) use ($pagesearch){
                    return $query->where('tagline','like','%'.$pagesearch.'%');
                })
				->paginate($pagesize,['*'],'page',$currentPage);		
		return view('admin/ajaxpages/tagTable', ['data' => $data]);
    }
    public function editTag($recordid)
	{
        $data = DB::table('tag_tbl')->where('tagid','=',$recordid)->first();
        return view('admin/master/tag_edit',compact('data'));
    }
	
    public function deleteTag($recordid)
	{
        $res = DB::delete('DELETE FROM tag_tbl WHERE tagid=?', [$recordid]);
        if($res)
        {
            return response()->json(['success' => true,'fail'=>'']);
        }
        else
        {
            return back()->with('fail','RECORD ID DOES NOT EXIST OR FOUND SOME PROBLEM!');
        }

    }
	/* TAG RELATED */
}
