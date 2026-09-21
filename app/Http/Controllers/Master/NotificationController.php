<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    public function sendNotification(Request $request)
	{
        Session::put('adminmenu','eoimanagement');
		Session::put('adminsubmenu','sendnotification');

		$userId		= $request->session()->get('loginId');
		$issuper	= $request->session()->get('issuper');
		
		$users 		= 	DB::table('users_tbl as a')
						->select('b.departmentid','a.name','a.isdepartment','a.ispm')
						->join('department_tbl as b','b.userid','=','a.userid')
						->orderby('a.name')
						->get();
		
		$firms		=	DB::table('users_tbl as a')
						->select('b.vendorid','a.name','a.isvendor','b.categoryid')
						->join('vendor_tbl as b','b.userid','=','a.userid')
						->orderby('a.name')
						->get();
		
		$token		=	rand('100000','999999').''.time();
		Session::put('form_token', $token);
		return view('admin/master/notification_send',compact('users','token','firms'));
    }

	public function storeNotification(Request $request)
	{
		$request->merge([
			'notification_date'     => Carbon::parse($request->notification_date)->format('Y-m-d'),
		]);
		
		$rules = [
			'notification_date'   		=> 	'required|date',
			'notification_title'  		=> 	'required|regex:/^[a-zA-Z0-9\s\'\,\.\(\)\[\]"\`\@\#\$\%\&\*]+$/',
			'notification_content'		=> 	'required|regex:/^(?=.*<[^>]+>)[\s\S]*$/',

			'notification_attachment' 	=> 	'nullable|file|mimetypes:application/pdf|max:10240',
		];

		$messages = [
			'notification_date.required' => 'Notification date is required.',
			'notification_date.date' => 'Please provide a valid notification date.',

			'notification_title.required' => 'Notification title is required.',
			'notification_title.regex' => 'Notification title contains invalid characters.',

			'notification_content.required' => 'Notification content is required.',
			'notification_content.regex' => 'Notification content must include valid HTML content.',

			'notification_attachment.file' => 'Attachment must be a valid file.',
			'notification_attachment.mimetypes' => 'Only PDF files are allowed for attachment.',
			'notification_attachment.max' => 'Attachment size must not exceed 10 MB.',
		];
		
		$validatedData 	= $request->validate($rules, $messages);

		if(!($request->department_ids || $request->manager_ids || $request->vendor_ids))
		{
			return response()->json([
				'status'	=>	400,
				'message'	=>	'Please select at least one department, project manager, or vendor.'
			],200);				

		}
		
		$submittedtoken = $request->input('form_token');
		$sessiontoken 	= Session::pull('form_token');
		if($submittedtoken!==$sessiontoken)
		{
			return response()->json([
				'status'	=>	400,
				'message'	=>	'The form was submitted successfully. Additional submissions were detected and have been ignored to prevent duplication.'
			],200);				
		}		
	
        $userId      	= $request->session()->get('loginId');
		
		try
		{
			$notification_attachment	=	NULL;
			if($request->hasFile('notification_attachment'))
			{
				$file = $request->file('notification_attachment');
				$notification_attachment = $file->store('uploads/notification_files','public');
			}			
			
			$departmentIds 	= 	(array) $request->input('department_ids');
			$managerIds		=	(array) $request->input('manager_ids');
			$vendorIds		=	(array) $request->input('vendor_ids');
			
			$notificationId = DB::table('notification_tbl')->insertGetId([
				'notification_date'  		=> 	$validatedData['notification_date'],
				'notification_title'   		=> 	$validatedData['notification_title'],
				'notification_content'		=> 	$validatedData['notification_content'],
				'department_ids'			=>	implode(',',$departmentIds) ?? NULL,
				'manager_ids'				=>	implode(',',$managerIds) ?? NULL,
				'vendor_ids'				=>	implode(',',$vendorIds) ?? NULL,
				'notification_attachment'	=>	$notification_attachment,
				'created_on'				=>	now(),
				'created_by'				=>	$userId
			]);
			
			return response()->json([
				'status'=>200,
				'message'=>'Notification sent successfully.'
			],200);
			
		}
		catch(QueryException $e) 
		{
			Log::error("Error " . $e->getMessage());
			return response()->json(['status'=>400,'message'=>'Something went wrong, please try again after some time.']);
		}
	}
    public function getNotificationData(Request $request)
	{
		try
		{
			$frmdate 	=	date('Y-m-d', strtotime($request->input('frmdate')));
			$todate   	=	date('Y-m-d', strtotime($request->input('todate')));
			$frmdate	=	Carbon::parse($frmdate)->startOfDay();
			$todate   	=	Carbon::parse($todate)->endOfDay();
			
			$departmentid	=	$request->input('departmentid') ?? NULL;
			$pmid			=	$request->input('pmid') ?? NULL;
			$vendorid		=	$request->input('vendorid') ?? NULL;

			$pagesize		=	$request->input('pagesize');
			$currentPage	=	$request->input('page',1);

			$data	=	DB::table('notification_tbl as a')
						->select(
							'a.*',
							DB::raw("CONCAT('<ul><li>', GROUP_CONCAT(DISTINCT b.departmentname SEPARATOR '</li><li>'), '</li></ul>') as departments"),
							DB::raw("CONCAT('<ul><li>', GROUP_CONCAT(DISTINCT c.departmentname SEPARATOR '</li><li>'), '</li></ul>') as managers"),
							DB::raw("CONCAT('<ul><li>', GROUP_CONCAT(DISTINCT d.companyname SEPARATOR '</li><li>'), '</li></ul>') as vendors")
						)
						->leftJoin('department_tbl as b', DB::raw('FIND_IN_SET(b.departmentid,a.department_ids)'), '>', DB::raw('0'))
						->leftJoin('department_tbl as c', DB::raw('FIND_IN_SET(c.departmentid,a.manager_ids)'), '>', DB::raw('0'))
						->leftJoin('vendor_tbl as d', DB::raw('FIND_IN_SET(d.vendorid,a.vendor_ids)'), '>', DB::raw('0'))
						->when($departmentid!=NULL,function($query) use ($departmentid){
							return $query->whereRaw('FIND_IN_SET(?,a.department_ids',[$departmentid]);
						})
						->when($pmid!=NULL,function($query) use ($pmid){
							return $query->whereRaw('FIND_IN_SET(?,a.manager_ids',[$pmid]);
						})
						->when($vendorid!=NULL,function($query) use ($vendorid){
							return $query->whereRaw('FIND_IN_SET(?,a.vendor_ids',[$vendorid]);							
						})
						->when($frmdate && $todate, fn($q) =>
							$q->whereBetween('a.notification_date', [$frmdate, $todate])
						)
						->groupBy('a.notification_id')
						->orderBy('a.notification_date')
						->paginate($pagesize,['*'],'page',$currentPage);
			
			return view('admin/ajaxpages/notificationTable', ['data' => $data]);
		}
		catch(Exception $e)
		{
			Log::error('Error '.$e->getMessage());
		}

    }

    public function deleteNotification($recordid)
	{
		$res = DB::delete('DELETE FROM notification_tbl WHERE id=?', [Crypt::decrypt($recordid)]);

		if($res)
		{
			return response()->json(['success' => true,'fail'=>'']);
		}
		else
		{
			return back()->with('fail',__('messages.notfound'));
		}
    }
	/*FOR PROJECT MANAGERS, DEPARTMENT AND VENDORS*/
    public function viewNotifications(Request $request,$notificationid=NULL)
	{
        Session::put('adminmenu','viewnotifications');
		Session::put('adminsubmenu','viewnotifications');

		return view('admin/master/view_notifications',compact('notificationid'));
    }


    public function getNotificationsData(Request $request)
	{
		try
		{
			$departmentId		= 	$request->session()->get('departmentId') ?? null;
			$vendorId			=	$request->session()->get('vendorId') ?? null;
			$isProjectManager	=	$request->session()->get('isProjectManager') ?? NULL;
			
			$notificationid	=	$request->input('notificationid') ?? NULL;
			
			$pagesize	=	$request->input('pagesize');
			$currentPage=	$request->input('page',1);

			$data	=	DB::table('notification_tbl as a')
						->select('a.*')
						->when($departmentId!=null && $isProjectManager==NULL, function ($query) use ($departmentId) {
							return $query->where(function ($q) use ($departmentId) {
								$q->whereRaw('FIND_IN_SET(?, a.department_ids)', [$departmentId]);
							});
						})
						->when($departmentId!=null && $isProjectManager!=NULL, function ($query) use ($departmentId) {
							return $query->where(function ($q) use ($departmentId) {
								$q->whereRaw('FIND_IN_SET(?, a.manager_ids)', [$departmentId]);
							});
						})
						->when($vendorId!=null, function ($query) use ($vendorId) {
							return $query->whereRaw('FIND_IN_SET(?,a.vendor_ids)',[$vendorId]);
						})
						->when($notificationid!=null, function ($query) use ($notificationid) {
							return $query->where('a.notification_id','=',Crypt::decrypt($notificationid));
						})
						->groupBy('a.notification_id')
						->orderBy('a.notification_date', 'desc')
						->paginate($pagesize, ['*'], 'page', $currentPage);
			
			return view('admin/ajaxpages/notificationsTable', ['data' => $data]);
		}
		catch(Exception $e)
		{
			Log::error('Error '.$e->getMessage());
		}
    }
	
}
