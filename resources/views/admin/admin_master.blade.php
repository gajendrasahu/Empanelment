@php
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
$eoi	=	DB::table('eoi_request')->where('userid',session('userId'))->where('eoistatus','=',0)->count();
@endphp

<!DOCTYPE html>
<html lang="en">
	@include('admin.body.header')
	@inject('detect', 'Detection\MobileDetect')
	<body class="no-skin">
		 @if($detect->isMobile())
		<div id="navbar" class="navbar navbar-default ace-save-state">
			<button type="button" class="navbar-toggle menu-toggler pull-right" id="menu-toggler" data-target="#sidebar">
				<span class="sr-only">Toggle sidebar</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<div>
				<div style="display: inline-block; vertical-align: middle;">
					<img src="{{ asset('panel/assets/images/basic/logo.png') }}" alt="Login Image" style="height: 45px; vertical-align: middle;">
				</div>
				<div style="display: inline-block; vertical-align: middle; margin-left: 10px;">
					<span id="banner-text" style="color:white; font-size:8px;">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</span><br>
					<h2 class="primary-color" style="margin: 0; display: inline-block; color:white!important; font-size:16px;">CHiPS Empanelment Portal</h2>
				</div>			
				
			</div>
			
			<div class="navbar-container ace-save-state" id="navbar-container" @if($detect->isMobile()) style="float:right;" @endif>
				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">

					

					</ul>
				</div>
			</div>
		</div>
		@else
		<div id="navbar" class="navbar navbar-default ace-save-state" style="padding:10px; height:70px; background-color:#025964!important; opacity:1;">
			<button type="button" class="navbar-toggle menu-toggler pull-right" id="menu-toggler" data-target="#sidebar">
				<span class="sr-only">Toggle sidebar</span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
				<span class="icon-bar"></span>
			</button>
			<div class="navbar-header pull-left">
				<div style="display: inline-block; vertical-align: middle;">
					<img src="{{ asset('panel/assets/images/basic/logo.png') }}" alt="Login Image" style="height: 45px; vertical-align: middle;">
				</div>
				<div style="display: inline-block; vertical-align: middle; margin-left: 10px;">
					<span id="banner-text" style="color:white; font-size:8px;">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</span><br>
					<h2 class="primary-color" style="margin: 0; display: inline-block; color:white!important; font-size:16px;">CHiPS Empanelment Portal</h2>
				</div>			
				
			</div>
			<div class="navbar-container ace-save-state" id="navbar-container" @if($detect->isMobile()) style="float:right;" @endif>
				<div class="navbar-buttons navbar-header pull-right" role="navigation">
					<ul class="nav ace-nav">
						@if(Session('userType')=='ADMIN')
						@php

						$messages	=	DB::table('communication_tbl as a')
										->join('eoi_request as b','b.requestid','=','a.requestid')
										->where('a.isclosed',0)
										->groupBy('a.requestid', 'b.eoinumber')
										->select('a.requestid','b.eoinumber')
										->get();
						
					
			
						$approvals	=	DB::table('eoi_approval as a')
										->select('a.*','b.eoinumber')
										->join('eoi_request as b','b.requestid','=','a.requestid')
										->where('a.touserid',session('userId'))
										->whereRaw('a.recordid = (
											SELECT MAX(recordid) 
											FROM eoi_approval 
											WHERE requestid = a.requestid
										)')
										->whereNull('a.closedby')
										->get();					

						$eoiupdates		=	DB::table('log_eoi_updates as a')
												->select('a.requestid','b.eoinumber','b.projecttitle')
												->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
												->where('a.isviewed',0)
												->orderby('a.recordid')
												->get();
						// RAISED PREBID
						$preBids	=	DB::table('eoi_request as eoi')
											->join('eoi_request_prebid as prebid', 'eoi.requestid', '=', 'prebid.requestid')
											->where('prebid.prebidstatus', 'PENDING')
											->select('eoi.requestid', 'eoi.eoinumber')
											->distinct()
											->get();


						$preBidReply=	DB::table('eoi_request as eoi')
											->join('eoi_request_prebid as prebid', 'eoi.requestid', '=', 'prebid.requestid')
											->where('prebid.prebidstatus','REPLIED')
											->select('eoi.requestid', 'eoi.eoinumber')
											->distinct()
											->get();
						
						$orderdates = 	DB::table('eoi_work_order')
											->where(function($query) {
												$query->where('orderdate', '0000-00-00')
													  ->orWhereNull('orderdate')
													  ->orWhere('orderdate', '');
											})
											->get();
						
						$pendingDeployment	=	DB::table('pending_deployment')->get();
						$neweoi	=	DB::table('eoi_request')->where('eoistatus',0)->get();
						
						$float	=	DB::table('eoi_request')->where('eoistatus',2)->where('approvalClosed',1)->get();
						//$float	=	DB::table('eoi_request')->where('eoistatus',2)->get();
						
						$woexpiring	=	DB::table('eoi_work_order')->whereBetween('workorderduedate',[
											Carbon::now(),
											Carbon::now()->addMonths(2)
										])
										->orderBy('workorderduedate')
										->get();
						
						$invoices	=	DB::table('invoice_mpr')->select('voucher_id','voucher_number')->where('is_paid',0)->get();
						
						$cancellation	=	DB::table('eoi_cancellation as a')
											->select('a.requestid','b.eoinumber')
											->join('eoi_request as b','b.requestid','a.requestid')
											->where('a.isViewed',0)
											->get();
						@endphp

						@if($approvals->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								EoI Approval
								<span class="badge badge-important ordercounter">{{$approvals->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-check"></i> EoI Approval Request
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="overflow-x:scroll;">
										@foreach($approvals as $ids)
										<li>
											<a href="{{route('eoi.approval',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						@if($messages->count()>0)
						<li class="dropdown-modal primary-bgcolor">							
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Message
								<span class="badge badge-important ordercounter">{{$messages->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="overflow-x:scroll;">
										@foreach($messages as $ids)
										<li>
											<a href="{{route('communication.message',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif


						@if($cancellation->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-warning icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$cancellation->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-warning"></i> EoI Cancellation Request
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="overflow-x:scroll;">
										@foreach($cancellation as $ids)
										<li>
											<a href="{{route('eoi.eoicancel',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						
						@if($invoices->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-file-pdf-o icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$invoices->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-file-pdf-o"></i> Pending Invoice Payment
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($invoices as $ids)
										<li>
											<a href="" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->voucher_number}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						@if($woexpiring->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-hourglass-half icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$woexpiring->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-hourglass-half"></i> Orders Expiring Within 2 Months
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($woexpiring as $ids)
										<li>
											<a href="{{ route('workorders.list',$ids->orderid) }}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($float->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<span class="material-icons icon-animated-bell" style="vertical-align:middle;">outbox</span>
								<span class="badge badge-important ordercounter">{{$float->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-hand-o-right"></i> Ready To Publish EoI
									<!--<span class="material-icons" style="vertical-align:middle;">outbox</span> Ready To Publish EoI-->
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar">
										@foreach($float as $ids)
										<li>
											<a href="{{ route('prepare.float',Crypt::encrypt($ids->requestid)) }}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						
						@if($neweoi->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-tasks icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$neweoi->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-tasks"></i> New Request Letter
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar">
										@foreach($neweoi as $ids)
										<li>
											<a href="{{ route('prepare.draft',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix" style="width:300px;">
													<span class="pull-left form-label">
														 {{$ids->projecttitle}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($pendingDeployment->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-calendar icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$pendingDeployment->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-warning fa fa-calendar"></i> Pending Deployment
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($pendingDeployment as $ids)
										<li>
											<a href="{{route('set.deploymentdate',Crypt::encrypt($ids->orderid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left form-label">
														 {{$ids->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($preBidReply->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$preBidReply->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question"></i> Pre-bid Replies
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($preBidReply as $ids)
										<li>
											<a href="{{route('prebidenquiry.list',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						@if($orderdates->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-list-alt icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$orderdates->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-list-alt"></i> Pending Work Order Approvals
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($orderdates as $ids)
										<li>
											<a href="{{ route('set.orderdate',Crypt::encrypt($ids->orderid)) }}" class="action-a">
												<div class="clearfix">
													<span class="pull-left">
														 {{$ids->refrence}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($preBids->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$preBids->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question"></i> Pre-bid Queries
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($preBids as $ids)
										<li>
											<a href="{{ route('prebidenquiry.list',Crypt::encrypt($ids->requestid))}}" class="action-a">
												<div class="clearfix">
													<span class="pull-left">
														 {{$ids->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						@if($eoiupdates->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-bell icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$eoiupdates->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-bullhorn"></i> EoI update alert
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($eoiupdates as $updates)
										<li>
											<a href="{{ route('prepare.draft',Crypt::encrypt($updates->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$updates->projecttitle}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						<li class="dropdown-modal primary-bgcolor">
							<a class="dropdown-toggle" href="{{route('admin.noticeboard')}}">
								<i class="fa fa-bell"></i>&nbsp; Activity
							</a>
						</li>
						
						@elseif(Session('userType')=='DEPARTMENT')
						@php
						$usrId		=	session('userId');

						$updateRequests	=	DB::table('eoi_interview_date as a')
											->select('a.requestid','b.eoinumber')
											->join('eoi_request as b','b.requestid','=','a.requestid')
											->where('b.userid',session('userId'))
											->whereNull('a.isUpdated')
											->orderBy('a.recordid')
											->get();

						$messages	=	DB::table('communication_status as a')
										->join('communication_tbl as b', 'b.recordid', '=', 'a.recordid')
										->join('eoi_request as c', 'c.requestid', '=', 'b.requestid')
										->where('a.message_for', 'DEPARTMENT')
										->where('a.departmentid',session('departmentId'))
										->select(
											'c.requestid',
											'c.eoinumber',
											DB::raw('SUM(a.seen_status IS NULL) as unseen_count')
										)
										->groupBy('c.requestid', 'c.eoinumber')
										->havingRaw('SUM(a.seen_status IS NULL) > 0')
										->orderByRaw('MAX(a.recordid) DESC')
										->get();

					
						$prebids	=	DB::table('eoi_request_prebid as a')
										->select('a.requestid','b.eoinumber')
										->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
										->where('b.userid','=',Session('userId'))
										->where('a.prebidstatus','=','FORWARD')
										->groupBy('a.requestid', 'b.eoinumber')
										->get();
						$approvedEoi	=	DB::table('eoi_request')->where('eoistatus',1)->where('userid',Session('userId'))->count();
						
						$wos	=	DB::table('eoi_work_order as a')
										->select('a.orderid','b.requestid','a.ordernumber','a.orderdate','a.orderno')
										->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
										->where('b.userid',Session('userId'))
										->where('a.departmentview',0)
										->whereNotNull('a.orderdate')
										->get();

						$pendingDeployment	=	DB::table('pending_deployment_verification')
												->select('orderid','ordernumber')
												->where('userid',Session::get('userId'))
												->distinct()
												->get();
						$mpr_view	=	DB::table('mpr_status')->select('mpr_id','order_id','mpr_number')->where('userid',Session('userId'))->get();
						
						$update_required	=	DB::table('eoi_request')
												->select('requestid','eoinumber')
												->where('userid',Session('userId'))
												->where('isUpdateRequired',1)
												->get();

						$preBids	= 	DB::table('eoi_request_prebid_forwarded as a')
										->join('eoi_request as b','b.requestid','=','a.requestid')
										->select('b.requestid', 'b.eoinumber')
										->where('a.touserid', Session('userId'))
										->whereNotIn('b.requestid', function($query) {
											$query->select('requestid')
												  ->from('eoi_request_prebid_broadcast');
										})
										->distinct()
										->get();		
										
						$newNotifications=	DB::table('notification_tbl')
										->select('notification_id','notification_title')
										->whereRaw('FIND_IN_SET(?, department_ids)', [Session('departmentId')])
										->whereDate('notification_date', '>=', Carbon::now()->subDays(5))
										->orderBy('notification_id','DESC')
										->get();

						$newInvoices	=	DB::table('vendor_invoices as a')
											->join('eoi_work_order as b', 'b.orderid', '=', 'a.orderid')
											->join('vendor_tbl as c', 'c.vendorid', '=', 'b.vendorid')
											->where('b.department_id', Session('departmentId'))
											->whereNull('a.isMarked')
											->select(
												'c.vendorid',
												'c.companyname',
												DB::raw('COUNT(a.recordid) as invoice_count')
											)
											->groupBy('c.vendorid', 'c.companyname')
											->get();										
						@endphp

						@if($newInvoices->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-bell icon-animated-bell"></i>&nbsp;New Invoice
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-content" style="width:300px;">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($newInvoices as $pd)
										<li>
											<a href="{{ route('dept.invoices')}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{ $pd->companyname }}<br>[{{$pd->invoice_count}} Invoices]
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($newNotifications->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Notification
								<span class="badge badge-important ordercounter">{{$newNotifications->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:250px;">
										@foreach($newNotifications as $pd)
										<li>
											<a href="{{ route('view.notifications',Crypt::encrypt($pd->notification_id))}}">
												<div class="clearfix">
													<span class="pull-left">
														 <i class="fa fa-info-circle"></i> {{ $pd->notification_title }}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($updateRequests->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Confirm Interview Date
								<span class="badge badge-important ordercounter">{{$updateRequests->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Reference
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($updateRequests as $pd)
										<li>
											<a href="{{ route('view.deptpptresumes', Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						
						@if($messages->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Messages
								<span class="badge badge-important ordercounter">{{$messages->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Reference
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($messages as $pd)
										<li>
											<a href="{{ route('communication.message', Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}} [{{$pd->unseen_count}}]
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						
						@if($preBids->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$preBids->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question"></i> Pre-bid Query
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($preBids as $pd)
										<li>
											<a href="{{ route('prebid.reply',Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($update_required->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-refresh icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$update_required->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-refresh"></i> Update required in EoI
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($update_required as $pd)
										<li>
											<a href="{{ route('deptview.draft',Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($mpr_view->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-calendar icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$mpr_view->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-calendar"></i> MPRs For Approval
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($mpr_view as $pd)
										<li>
											<a href="{{ route('department.mprlist',Crypt::encrypt($pd->mpr_id)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->mpr_number}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($pendingDeployment->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-user icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$pendingDeployment->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-user"></i> Pending Deployment Approval
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($pendingDeployment as $pd)
										<li>
											<a href="{{ route('view.deployment',Crypt::encrypt($pd->orderid)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($wos->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-reorder icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$wos->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-reorder"></i> Work Order Issued
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($wos as $wo)
										@php
										$temp	=	explode("/",$wo->ordernumber);
										$ordNo	=	$temp[0];
										@endphp
										<li>
											<a href="{{ route('departmentworkorders.list',$ordNo) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$wo->ordernumber}} {{date('d\-m\-Y',strtotime($wo->orderdate))}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($approvedEoi>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-clock-o icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$approvedEoi}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<a href="{{route('dept.eoilist')}}" class="action-a">
										<div class="clearfix">
											<span class="pull-left form-label">
												 <i class="fa fa-clock-o"></i> Waiting For Approval
											</span>
										</div>
									</a>

								</li>
							</ul>
						</li>
						@endif

						@if($prebids->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question-circle icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$prebids->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question-circle"></i> Pre-bid Query
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($prebids as $prebid)
										<li>
											<a href="{{ route('prebid.reply',Crypt::encrypt($prebid->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$prebid->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						@elseif(Session('userType')=='VENDOR')
						@php

						$messages	=	DB::table('communication_status as a')
										->join('communication_tbl as b', 'b.recordid', '=', 'a.recordid')
										->join('eoi_request as c', 'c.requestid', '=', 'b.requestid')
										->where('a.message_for', 'VENDOR')
										->where('a.vendorid', session('vendorId'))
										->select(
											'c.requestid',
											'c.eoinumber',
											DB::raw('SUM(a.seen_status IS NULL) as unseen_count')
										)
										->groupBy('c.requestid', 'c.eoinumber')
										->havingRaw('SUM(a.seen_status IS NULL) > 0')
										->orderByRaw('MAX(a.recordid) DESC')
										->get();
							
						$wos	=	DB::table('eoi_work_order')->where('markedbyvendor',0)->where('vendorid',Session('vendorId'))->get();
						$wos	=	DB::table('eoi_work_order')->where('markedbyvendor',0)->where('vendorid',Session('vendorId'))->get();
						
						$published	=	DB::table('eoi_request_floated as a')
										->select('a.floatid','b.requestid','b.eoinumber')
										->leftjoin('eoi_request as b','b.requestid','a.requestid')
										->where('a.isviewed',0)
										->where('a.vendorid',Session('vendorId'))
										->get();
						
						
						$pendingDeployment	=	DB::table('eoi_resource_deployment as a')
												->select('b.orderid','b.ordernumber')
												->join('eoi_work_order as b','b.orderid','=','a.orderid')
												->where('b.vendorid',Session::get('vendorId'))
												->whereNull('a.deployed_date')
												->distinct()
												->get();
						$pendingInvoice	=	DB::table('mpr_tbl as a')
												->select('a.mpr_id','a.mpr_number')
												->where('a.vendor_id',Session('vendorId'))
												->where('a.is_verified',1)
												->where('a.is_invoiced',0)
												->get();
						$subUserId	=	Session('subUserId') ?? NULL;
						$pendingResumes	=	DB::table('pending_resumes')
											->where('vendorid',Session('vendorId'))
											->when($subUserId!=NULL,function($query) use ($subUserId){
												return $query->where('subuserid',$subUserId);
											})
											->get();

						$newNotifications=	DB::table('notification_tbl')
										->select('notification_id','notification_title')
										->whereRaw('FIND_IN_SET(?, vendor_ids)', [Session('vendorId')])
										->whereDate('notification_date', '>=', Carbon::now()->subDays(5))
										->orderBy('notification_id','DESC')
										->get();
											
						@endphp
						@if($newNotifications->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Notification
								<span class="badge badge-important ordercounter">{{$newNotifications->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:250px;">
										@foreach($newNotifications as $pd)
										<li>
											<a href="{{ route('view.notifications',Crypt::encrypt($pd->notification_id))}}">
												<div class="clearfix">
													<span class="pull-left">
														 <i class="fa fa-info-circle"></i> {{ $pd->notification_title }}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						@if($messages->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Messages
								<span class="badge badge-important ordercounter">{{$messages->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Reference
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($messages as $pd)
										<li>
											<a href="{{ route('communication.message', Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}} [{{$pd->unseen_count}}]
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						@if($pendingResumes->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-warning"></i>&nbsp;Upload Document
								<span class="badge badge-important ordercounter">{{$pendingResumes->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									Pending Participation
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($pendingResumes as $pd)
										<li>
											<a href="{{ route('fillform.view',Crypt::encrypt($pd->floatid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($pendingInvoice->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-inr icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$pendingInvoice->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-inr"></i> Pending MPRs for Invoicing
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($pendingInvoice as $pd)
										<li>
											<a>
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->mpr_number}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						@if($pendingDeployment->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-list-alt icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$pendingDeployment->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-list-alt"></i> Pending Deployment
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($pendingDeployment as $pd)
										<li>
											<a href="{{ route('view.deploymentdate',Crypt::encrypt($pd->orderid)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($published->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-list-alt icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$published->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-list-alt"></i> New EoI Floated
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($published as $pb)
										<li>
											<a href="{{ route('floated.view',Crypt::encrypt($pb->floatid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pb->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($wos->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-list-alt icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$wos->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-list-alt"></i> New Work Order
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($wos as $wo)
										<li>
											<a href="{{ route('show.vendorworkorder',Crypt::encrypt($wo->orderid)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$wo->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						@elseif(Session('userType')=='PROJECT MANAGER')
						@php
						$usrId	=	session('userId');

						$updateRequests	=	DB::table('eoi_interview_date as a')
											->select('a.requestid','a.remark','b.eoinumber')
											->join('eoi_request as b','b.requestid','=','a.requestid')
											->where('b.userid',session('userId'))
											->whereNull('a.isUpdated')
											->orderBy('a.recordid')
											->get();


						$messages	=	DB::table('communication_status as a')
										->join('communication_tbl as b', 'b.recordid', '=', 'a.recordid')
										->join('eoi_request as c', 'c.requestid', '=', 'b.requestid')
										->where('a.message_for', 'PROJECT MANAGER')
										->where('a.departmentid',session('departmentId'))
										->select(
											'c.requestid',
											'c.eoinumber',
											DB::raw('SUM(a.seen_status IS NULL) as unseen_count')
										)
										->groupBy('c.requestid', 'c.eoinumber')
										->havingRaw('SUM(a.seen_status IS NULL) > 0')
										->orderByRaw('MAX(a.recordid) DESC')
										->get();


						$prebids	=	DB::table('eoi_request_prebid as a')
										->select('a.requestid','b.eoinumber')
										->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
										->where('b.userid','=',Session('userId'))
										->where('a.prebidstatus','=','FORWARD')
										->groupBy('a.requestid', 'b.eoinumber')
										->get();
						$approvedEoi	=	DB::table('eoi_request')->where('eoistatus',1)->where('userid',Session('userId'))->get();
						
						$wos	=	DB::table('eoi_work_order as a')
										->select('a.orderid','b.requestid','a.ordernumber','a.orderdate','a.orderno')
										->leftjoin('eoi_request as b','b.requestid','=','a.requestid')
										->where('b.userid',Session('userId'))
										->where('a.departmentview',0)
										->whereNotNull('a.orderdate')
										->get();

						$pendingDeployment	=	DB::table('pending_deployment_verification')
												->select('orderid','ordernumber')
												->where('userid',Session::get('userId'))
												->distinct()
												->get();
						$mpr_view	=	DB::table('mpr_status')->select('mpr_id','order_id','mpr_number')->where('userid',Session('userId'))->get();
						
						$update_required	=	DB::table('eoi_request')
												->select('requestid','eoinumber')
												->where('userid',Session('userId'))
												->where('isUpdateRequired',1)
												->get();

						$preBids	= 	DB::table('eoi_request_prebid_forwarded as a')
										->join('eoi_request as b','b.requestid','=','a.requestid')
										->select('b.requestid', 'b.eoinumber')
										->where('a.touserid', Session('userId'))
										->whereNotIn('b.requestid', function($query) {
											$query->select('requestid')
												  ->from('eoi_request_prebid_broadcast');
										})
										->distinct()
										->get();												

					$newInvoices	=	DB::table('vendor_invoices as a')
										->join('eoi_work_order as b', 'b.orderid', '=', 'a.orderid')
										->join('vendor_tbl as c', 'c.vendorid', '=', 'b.vendorid')
										->where('b.department_id', Session('departmentId'))
										->whereNull('a.isMarked')
										->select(
											'c.vendorid',
											'c.companyname',
											DB::raw('COUNT(a.recordid) as invoice_count')
										)
										->groupBy('c.vendorid', 'c.companyname')
										->get();										


						$newNotifications=	DB::table('notification_tbl')
										->select('notification_id','notification_title')
										->whereRaw('FIND_IN_SET(?, manager_ids)', [Session('departmentId')])
										->whereDate('notification_date', '>=', Carbon::now()->subDays(5))
										->orderBy('notification_id','DESC')
										->get();

						@endphp
						@if($newNotifications->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Notification
								<span class="badge badge-important ordercounter">{{$newNotifications->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:250px;">
										@foreach($newNotifications as $pd)
										<li>
											<a href="{{ route('view.notifications',Crypt::encrypt($pd->notification_id))}}">
												<div class="clearfix">
													<span class="pull-left">
														 <i class="fa fa-info-circle"></i> {{ $pd->notification_title }}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						@if($newInvoices->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="fa fa-bell icon-animated-bell"></i>&nbsp;New Invoice
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Invoices
								</li>
								<li class="dropdown-content" style="width:300px;">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($newInvoices as $pd)
										<li>
											<a href="{{ route('pm.invoices')}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{ $pd->companyname }}<br>[{{$pd->invoice_count}} Invoices]
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif


						@if($updateRequests->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Confirm Interview Schedule
								<span class="badge badge-important ordercounter">{{$updateRequests->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Reference
								</li>
								<li class="dropdown-content" style="width:300px;">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($updateRequests as $pd)
										<li>
											<a href="{{ route('view.pmpptresumes', Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{ \Illuminate\Support\Str::limit($pd->remark, 50) }}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif

						
						@if($messages->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								Messages
								<span class="badge badge-important ordercounter">{{$messages->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="fa fa-list"></i> Reference
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($messages as $pd)
										<li>
											<a href="{{ route('communication.message', Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}} [{{$pd->unseen_count}}]
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($preBids->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$preBids->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question"></i> Pre-bid Query
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($preBids as $pd)
										<li>
											<a href="{{ route('prebid.pmreply',Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($update_required->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-refresh icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$update_required->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-refresh"></i> Update required in EoI
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($update_required as $pd)
										<li>
											<a href="{{ route('pmview.draft',Crypt::encrypt($pd->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($mpr_view->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-calendar icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$mpr_view->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-calendar"></i> MPRs For Approval
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($mpr_view as $pd)
										<li>
											<a href="{{ route('pm.mprlist',Crypt::encrypt($pd->mpr_id)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->mpr_number}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($pendingDeployment->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-user icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$pendingDeployment->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-user"></i> Pending Deployment Approval
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($pendingDeployment as $pd)
										<li>
											<a href="{{ route('view.pmdeployment',Crypt::encrypt($pd->orderid)) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$pd->ordernumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@if($wos->count()!=0)
							<!--
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-reorder icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$wos->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-reorder"></i> Work Order Issued
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap style="max-width:100%; overflow-x:scroll;">
										@foreach($wos as $wo)
										<li>
											<a href="{{ route('departmentworkorders.list',$wo->orderno) }}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$wo->ordernumber}} {{date('d\-m\-Y',strtotime($wo->orderdate))}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						-->
						@endif
						
						@if($approvedEoi->count()>0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-clock-o icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$approvedEoi->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
							@foreach($approvedEoi as $approved)
								<li class="dropdown-header">
									<a href="{{ route('pmview.draft',Crypt::encrypt($approved->requestid))}}" class="action-a">
										<div class="clearfix">
											<span class="pull-left form-label">
												 <i class="fa fa-clock-o"></i> Waiting For Approval
											</span>
										</div>
									</a>

								</li>
							@endforeach
							</ul>
						</li>
						@endif

						@if($prebids->count()!=0)
						<li class="dropdown-modal primary-bgcolor">
							<a data-toggle="dropdown" class="dropdown-toggle" href="#">
								<i class="ace-icon fa fa-question-circle icon-animated-bell"></i>
								<span class="badge badge-important ordercounter">{{$prebids->count()}}</span>
							</a>

							<ul class="dropdown-menu-right dropdown-navbar dropdown-menu dropdown-caret dropdown-close">
								<li class="dropdown-header">
									<i class="btn btn-xs no-hover btn-pink fa fa-question-circle"></i> Pre-bid Query
								</li>
								<li class="dropdown-content">
									<ul class="dropdown-menu dropdown-navbar" nowrap>
										@foreach($prebids as $prebid)
										<li>
											<a href="{{ route('prebid.pmreply',Crypt::encrypt($prebid->requestid))}}">
												<div class="clearfix">
													<span class="pull-left">
														 {{$prebid->eoinumber}}
													</span>
												</div>
											</a>
										</li>
										@endforeach
									</ul>
								</li>
							</ul>
						</li>
						@endif
						
						@endif
						<li class="dropdown-modal primary-bgcolor nav-profile">
							<a data-toggle="dropdown" href="#">
								<img class="nav-user-photo" src="{{ asset('panel/assets/images/basic/sarkar.png') }}" alt="" />
									@if(Session::get('userType')=='ADMIN')
									{{Session::get('userType')}}
									@endif
									
									@if(Session::get('userType')=='PROJECT MANAGER')
									{{Session::get('userName')}} @if(Session::get('shortName')) ({{Session::get('shortName')}}) @endif
									@endif
									
									@if(Session::get('userType')=='DEPARTMENT')
									@if(Session::get('shortName')) {{Session::get('shortName')}} (Dept.) @endif 
									@endif

									@if(Session::get('userType')=='VENDOR')
									{{Session::get('shortName')}} (FIRM)
									@endif
								<i class="ace-icon fa fa-caret-down"></i><br>
								
							</a>

							<ul class="user-menu dropdown-menu-right dropdown-menu dropdown-caret dropdown-close">
								@if(Session::get('userType')!='PROJECT MANAGER')
								<li>
								<a>
									<i class="ace-icon fa fa-building-o"></i>
									{{Session::get('userName')}}
								</a>
								</li>
								
								@endif
								<!--
								<li>
								<a>
									<i class="ace-icon fa fa-clock-o"></i>
									Last Login : {{date('d\-m\-Y, h:i A',strtotime(Session('lastlogin')))}}
								</a>
								</li>
								-->
								@if(Session::get('userType')=='ADMIN')
								<li>
								<a href="{{route('application.settings')}}">
									<i class="ace-icon fa fa-cog"></i>
									Settings
								</a>
								</li>
								
								@endif
								<li>
									<a href="{{route('change.password')}}">
										<i class="ace-icon fa fa-lock"></i>
										Change Password
									</a>
								</li>


								<li class="divider"></li>
								<li>
									<a href=" {{ route('logout') }}">
										<i class="ace-icon fa fa-power-off"></i>
										Logout
									</a>
								</li>
							</ul>
						</li>
					</ul>
				</div>
			</div>
			
		</div>			
		@endif
		<div class="main-container ace-save-state" id="main-container" style="background-color:#F9FAF9!important;">

			@include('admin.body.adminmenu')

			@yield('admin')
			
			@include('admin.body.footer')

			<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
				<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
			</a>
		</div><!-- /.main-container -->

<div class="modal fade" id="updatedModal">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Update Detail</h5>
        <!-- Proper close button for BS3 -->
        <button type="button" class="close" style="float:right;" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body" id="updatedContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


		<!-- basic scripts -->

<link rel="stylesheet" href="{{ asset('panel/assets/css/flatpickr.min.css') }}">
<script src="{{ asset('panel/assets/js/flatpickr.js') }}"></script>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.go(1);
    };
	//setTimeout(function(){ location.reload(); },450000);
</script>

<script>
	const currentDate = new Date();
	const lastMonthDate = new Date(currentDate);
	lastMonthDate.setMonth(lastMonthDate.getMonth());	

	const firstDayOfMonth = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
	const lastDayOfMonth  = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);

function addDays(date, days) {
	const result = new Date(date);
	result.setDate(result.getDate() + days);
	return result;
}

function addDaysSkippingWeekend(date, daysToAdd) {
	const result = new Date(date);
	result.setDate(result.getDate() + daysToAdd);

	// If the resulting date is Saturday (6), add 2 days to move to Monday
	if (result.getDay() === 6) {
		result.setDate(result.getDate() + 2);
	}
	// If it's Sunday (0), add 1 day to move to Monday
	else if (result.getDay() === 0) {
		result.setDate(result.getDate() + 1);
	}

	return result;
}
// Initialize prebidlastdate, deadlinedate, and interviewdate pickers
const prebidlastdatePicker = flatpickr("#prebidlastdate", {
	dateFormat: "d-m-Y",
	minDate:"today",
	disable: [
		date => date.getDay() === 0 || date.getDay() === 6
	]
});

const deadlinedatePicker = flatpickr("#deadlinedate", {
	dateFormat: "d-m-Y h:i K",
	enableTime: true,
	time_24hr: false,
	minDate:"today",
	disable: [
		date => date.getDay() === 0 || date.getDay() === 6
	]
});

const interviewdatePicker = flatpickr("#interviewdate", {
	dateFormat: "d-m-Y h:i K",
	enableTime: true,
	time_24hr: false,
	minDate:"today",
	disable: [
		date => date.getDay() === 0 || date.getDay() === 6
	]
});

// Main releasedate picker
flatpickr("#releasedate", {
	dateFormat: "d-m-Y",
	minDate:"today",
	disable: [
		function(date) {
			return date.getDay() === 0 || date.getDay() === 6; // Disable weekends
		}
	],
	onChange: function(selectedDates) {
		if (selectedDates.length === 0) return;

		const releasedate = selectedDates[0];

		// Add 5 days to release date, skipping weekends
		const prebidDate = addDaysSkippingWeekend(releasedate, 5);
		//prebidlastdatePicker.setDate(prebidDate, true);

		// Add 16 days to prebid date
		const deadlineDate = addDaysSkippingWeekend(prebidDate, 16);
		//deadlinedatePicker.setDate(deadlineDate, true);

		// Add 7 days to deadline date
		const interviewDate = addDaysSkippingWeekend(deadlineDate, 7);
		//interviewdatePicker.setDate(interviewDate, true);
	}
});


  flatpickr("#totime", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: new Date().fp_incr(7),
    minDate: "today",
  });

  flatpickr("#appointfrmtime", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today"    
  });
  flatpickr("#setorderdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    minDate: "today",
  });

  flatpickr(".order_date", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    maxDate: "today",
	allowInput: true
  });

  flatpickr(".deploymentdate", {
    dateFormat: "d-m-Y",
	//minDate: "today",
  });

  flatpickr("#deliverydate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today",
  });
  flatpickr("#porderdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today"
  });
  flatpickr("#voucherdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today"
  });
  flatpickr("#pinvdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today"
  });
  flatpickr(".curdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today"
  });
  flatpickr(".blankdate", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
  });

  flatpickr(".dttm", {
  	enableTime: true,
    dateFormat: "d-m-Y h:i K",
    defaultDate: new Date(),
  });

  flatpickr(".interview_date", {
  	enableTime: true,
    dateFormat: "d-m-Y h:i K",
    defaultDate: "",
	allowInput: false
  });

  flatpickr(".frm_date", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: firstDayOfMonth,
	allowInput: true
  });
  flatpickr(".to_date", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: lastDayOfMonth,
	allowInput: true
  });
  flatpickr(".todays_dt", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today",
	allowInput: true
  });

  flatpickr(".todays_dat",{
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "today",
	allowInput: false
  });

  
  flatpickr(".todays_dt_blank", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "",
	allowInput: true
  });

  flatpickr(".todays_dt_hidden", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "",
	allowInput: false
  });

  flatpickr(".form_dates", {
    dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
    defaultDate: "",
	allowInput: false,
	maxDate: "today",
    onReady: function(selectedDates, dateStr, instance)
	{
        const clearBtn = document.createElement("button");
        clearBtn.type = "button";
        clearBtn.textContent = "Clear";
        clearBtn.className = "flatpickr-clear";
        clearBtn.addEventListener("click", function() {
            instance.clear(); // clear the date
        });
        instance.calendarContainer.appendChild(clearBtn);
    }
	
  });

</script>

<script>
jQuery(function($) {

	$('.chosen-select').chosen({allow_single_deselect:true}); 	

	$('.chosen-container').on('keydown', function(e) {
      if (e.which === 13) { 
        e.preventDefault();
        return false;
      }
	});

/*
	$('.select2').select2({				
        allowClear:true,
        width:'200px',
    });
*/

		function updateSelect2State($el) {
			var hasValue = $el.val() && $el.val().toString().length > 0;
			var $container = $el.next(".select2-container");
			$container.toggleClass("select2-has-value", !!hasValue);
		}

		$(".select2").css('width','200px').select2({
			allowClear: true
		}).on("select2:unselecting", function (e) {
			$(this).data('unselecting', true);
		}).on("select2:opening", function (e) {
		if ($(this).data('unselecting')) {
			$(this).removeData('unselecting');
			e.preventDefault();
			}
		}).on("change select2:select select2:unselect select2:clear", function () {
			updateSelect2State($(this));
		});
		$(".select2").css('margin-top','0px');
		$(".select2").each(function () { updateSelect2State($(this)); });

$('#creditaccountid').on('select2:open', function() {
        $('.select2-dropdown--below').css('width', '300px'); // Adjust width as needed
    });

$(".numbers").on("keypress", function(event) {
    var inputValue = event.key;
    var currentValue = $(this).val();
    
    // Allow "0" as a valid input unless it's already part of a larger number like "00"
    if ((currentValue === "" || currentValue === "0") && inputValue === "0") {
        event.preventDefault();
        return;
    }

    // Ensure only valid numeric input and allow a single decimal point
    var isValid = /^\d$/.test(inputValue) || (inputValue === '.' && currentValue.indexOf('.') === -1);
    
    if (!isValid) {
        event.preventDefault();
    }
});

$(".firstname, .middlename, .lastname").on("keypress", function(event) {
    var inputChar = event.key;

    var isAlpha = /^[a-zA-Z.]$/.test(inputChar);

    if (!isAlpha) {
        event.preventDefault();
    }
});

$(".completenumber").on("keypress", function(event) {
    var inputValue = event.key;
		var currentValue = $(this).val();
    if ((currentValue === "" || currentValue === "0") && inputValue === "0") {
        event.preventDefault();
        return;
    }
    var isValid = /^\d$/.test(inputValue);
    if (!isValid) {
        event.preventDefault();
    }
});

function getStickyScrollContainer() {
    var $main = $('.main-content');
    return $main.length ? $main : $(window);
}

function getRelativeTop($el, $container) {
    if ($container[0] === window) {
        return $el.offset().top;
    }
    return $el.offset().top - $container.offset().top + $container.scrollTop();
}

function getRowColspan($row, fallbackCount) {
    var colspan = 0;
    $row.children('th, td').each(function () {
        colspan += parseInt($(this).attr('colspan') || 1, 10);
    });
    if (!colspan && fallbackCount) {
        colspan = fallbackCount;
    }
    return colspan || 1;
}

function getWidthSourceRow(meta) {
    if (!meta || !meta.table) {
        return $();
    }

    var expectedCols = 0;
    if (meta.headerRow && meta.headerRow.length) {
        expectedCols = getRowColspan(meta.headerRow, meta.headerRow.children().length);
    }

    var $fallback = $();
    var fallbackCols = 0;

    meta.table.find('tbody tr:visible').each(function () {
        var $row = $(this);
        var hasColspan = false;
        var count = 0;
        $row.children('th, td').each(function () {
            var span = parseInt($(this).attr('colspan') || 1, 10);
            if (span > 1) {
                hasColspan = true;
            }
            count += span;
        });

        if (!hasColspan && expectedCols && count === expectedCols) {
            $fallback = $row;
            fallbackCols = count;
            return false;
        }

        if (!hasColspan && count > fallbackCols) {
            $fallback = $row;
            fallbackCols = count;
        }
    });

    if ($fallback.length && fallbackCols > 1) {
        return $fallback;
    }

    if (meta.headerRow && meta.headerRow.length) {
        return meta.headerRow;
    }

    return $();
}

function getColumnWidths(meta) {
    var widths = [];
    var $row = getWidthSourceRow(meta);
    if (!$row.length) {
        return widths;
    }
    $row.children('th, td').each(function () {
        var $cell = $(this);
        if ($cell.hasClass('hidden-column') || !$cell.is(':visible')) {
            return;
        }
        var colspan = parseInt($cell.attr('colspan') || 1, 10);
        var width = $cell.outerWidth() || $cell[0].getBoundingClientRect().width || 0;
        var perCol = colspan > 1 ? width / colspan : width;
        for (var i = 0; i < colspan; i++) {
            widths.push(perCol);
        }
    });
    return widths;
}

function getVisibleColumnCount(meta) {
    if (!meta || !meta.headerRow || !meta.headerRow.length) {
        return meta && meta.filterColspan ? meta.filterColspan : 1;
    }
    var count = 0;
    meta.headerRow.children('th, td').each(function () {
        var $cell = $(this);
        if ($cell.hasClass('hidden-column') || !$cell.is(':visible')) {
            return;
        }
        var span = parseInt($cell.attr('colspan') || 1, 10);
        count += span;
    });
    return count || meta.headerRow.children().length;
}

function applyColGroup(meta, widths) {
    if (!meta || !meta.cloneTable) {
        return;
    }
    var $origColgroup = meta.table ? meta.table.children('colgroup') : $();
    if ($origColgroup.length) {
        meta.cloneTable.children('colgroup').remove();
        meta.cloneTable.prepend($origColgroup.clone());
        return;
    }
    if (!widths || !widths.length) {
        return;
    }
    var $colgroup = meta.cloneTable.children('colgroup');
    if (!$colgroup.length) {
        $colgroup = $('<colgroup></colgroup>');
        meta.cloneTable.prepend($colgroup);
    }
    $colgroup.empty();
    widths.forEach(function (w) {
        $colgroup.append('<col style="width:' + w + 'px">');
    });
}

function syncStickyClone(meta) {
    if (!meta || !meta.cloneTable) {
        return;
    }

    var $scrollX = meta.scrollX && meta.scrollX.length ? meta.scrollX : meta.table.parent();
    var scrollLeft = $scrollX.length ? $scrollX.scrollLeft() : 0;
    var viewportWidth = $scrollX.length ? $scrollX.innerWidth() : meta.table.outerWidth();
    var scrollEl = $scrollX.length ? $scrollX[0] : null;
    var tableEl = meta.table && meta.table[0] ? meta.table[0] : null;
    var widths = getColumnWidths(meta);
    var hasColgroup = meta.table && meta.table.children('colgroup').length;
    var widthsTotal = 0;
    if (widths && widths.length) {
        widths.forEach(function (w) {
            widthsTotal += w;
        });
    }
    var scrollWidth = scrollEl ? scrollEl.scrollWidth : 0;
    var tableWidth = Math.max(viewportWidth, widthsTotal, scrollWidth, tableEl ? (tableEl.scrollWidth || 0) : 0, tableEl ? (tableEl.offsetWidth || 0) : 0, meta.table.outerWidth());

    meta.sticky.css('width', viewportWidth);
    meta.cloneTable.width(tableWidth);
    meta.cloneTable.css('transform', 'translateX(' + (-scrollLeft) + 'px)');

    applyColGroup(meta, widths);

    if (meta.headerRow && meta.headerRow.length && !hasColgroup) {
        var $cloneHeader = meta.cloneHead.find('tr.sticky-header-clone');
        if ($cloneHeader.length && widths.length) {
            var widthIndex = 0;
            $cloneHeader.children().each(function () {
                var $cell = $(this);
                if ($cell.hasClass('hidden-column') || !$cell.is(':visible')) {
                    return;
                }
                if (widths[widthIndex]) {
                    $cell.css('width', widths[widthIndex]);
                }
                widthIndex++;
            });
        }
    }
}

function showSticky(meta) {
    if (!meta || meta.isStuck) {
        syncStickyClone(meta);
        return;
    }

    meta.isStuck = true;
    meta.cloneHead.empty();

    if (meta.filterRow && meta.filterRow.length) {
        var visibleColspan = getVisibleColumnCount(meta);
        if (!meta.filterPlaceholder) {
            var placeholder = $('<tr class="sticky-filter-placeholder"><td colspan="' + visibleColspan + '"></td></tr>');
            placeholder.find('td').height(meta.filterRow.outerHeight());
            meta.filterPlaceholder = placeholder;
        } else {
            meta.filterPlaceholder.find('td, th').attr('colspan', visibleColspan);
        }
        var $filterCell = meta.filterRow.children('td, th').first();
        if ($filterCell.length) {
            if (!$filterCell.data('orig-colspan')) {
                $filterCell.data('orig-colspan', $filterCell.attr('colspan') || 1);
            }
            $filterCell.attr('colspan', visibleColspan);
        }
        meta.filterRow.before(meta.filterPlaceholder);
        meta.cloneHead.append(meta.filterRow);
    }

    if (meta.headerRow && meta.headerRow.length) {
        var headerClone = meta.headerRow.clone();
        headerClone.find('.hidden-column').remove();
        headerClone.removeClass('sticky-original-hidden');
        headerClone.addClass('sticky-header-clone');
        meta.headerRow.addClass('sticky-original-hidden');
        meta.cloneHead.append(headerClone);
    }

    meta.sticky.show();
    syncStickyClone(meta);
}

function hideSticky(meta) {
    if (!meta || !meta.isStuck) {
        return;
    }

    meta.isStuck = false;
    meta.sticky.hide();
    meta.cloneHead.find('.sticky-header-clone').remove();

    if (meta.filterRow && meta.filterRow.length && meta.filterPlaceholder) {
        meta.filterPlaceholder.before(meta.filterRow);
        meta.filterPlaceholder.remove();
        meta.filterPlaceholder = null;
        var $filterCell = meta.filterRow.children('td, th').first();
        if ($filterCell.length && $filterCell.data('orig-colspan')) {
            $filterCell.attr('colspan', $filterCell.data('orig-colspan'));
        }
    }

    if (meta.headerRow && meta.headerRow.length) {
        meta.headerRow.removeClass('sticky-original-hidden');
    }
}

function updateStickyPositions() {
    var $scrollY = getStickyScrollContainer();
    var scrollTop = $scrollY.scrollTop();

    $('.page-content table.sticky-table').each(function () {
        var meta = $(this).data('stickyMeta');
        if (!meta || !meta.table.is(':visible')) {
            return;
        }

        var tableTop = getRelativeTop(meta.table, $scrollY);
        var tableBottom = tableTop + meta.table.outerHeight();
        var stickyHeight = meta.sticky.outerHeight() || 0;

        if (scrollTop > tableTop && scrollTop < (tableBottom - stickyHeight)) {
            showSticky(meta);
        } else {
            hideSticky(meta);
        }
    });
}

function setupStickyTables() {
    $('.page-content table').each(function (idx) {
        var $table = $(this);
        if ($table.closest('.modal').length) {
            return;
        }

        var $thead = $table.find('thead');
        if (!$thead.length) {
            return;
        }

        $table.addClass('sticky-table');

        var $responsive = $table.closest('.table-responsive');
        if ($responsive.length && !$table.parent().hasClass('sticky-table-scroll')) {
            $table.wrap('<div class="sticky-table-scroll"></div>');
        }

        var $scrollX = $table.closest('.sticky-table-scroll');
        if (!$scrollX.length && $responsive.length) {
            $scrollX = $responsive;
        }

        var $rows = $thead.find('tr');
        var $filterRow = $();
        $rows.each(function () {
            var $row = $(this);
            if ($row.find('.table-filters-row').length ||
                $row.find('input, select, .select2, .nav-search-input').length) {
                $filterRow = $row;
                return false;
            }
        });

        var $headerRow = $rows.last();
        if ($filterRow.length && $headerRow.is($filterRow)) {
            $headerRow = $();
        }

        var tableId = $table.attr('data-sticky-id');
        if (!tableId) {
            tableId = 'sticky-table-' + idx;
            $table.attr('data-sticky-id', tableId);
        }

        var $sticky = $('.sticky-table-floating[data-sticky-for="' + tableId + '"]');
        if (!$sticky.length) {
            $sticky = $('<div class="sticky-table-floating" data-sticky-for="' + tableId + '"><div class="sticky-table-floating-inner"><table class="' + $table.attr('class') + ' sticky-clone-table"><thead></thead></table></div></div>');
            ($responsive.length ? $responsive : $table).before($sticky);
        }

        var meta = $table.data('stickyMeta') || {};
        meta.table = $table;
        meta.thead = $thead;
        if (!$filterRow.length && meta.filterRow && meta.filterRow.length) {
            $filterRow = meta.filterRow;
        }
        if (!$headerRow.length && meta.headerRow && meta.headerRow.length) {
            $headerRow = meta.headerRow;
        }
        meta.filterRow = $filterRow;
        meta.headerRow = $headerRow;
        meta.sticky = $sticky;
        meta.cloneTable = $sticky.find('table');
        meta.cloneHead = meta.cloneTable.find('thead');
        meta.scrollX = $scrollX;
        meta.filterColspan = $filterRow.length ? getRowColspan($filterRow, $headerRow.length ? $headerRow.children().length : $filterRow.children().length) : 1;
        meta.isStuck = meta.isStuck || false;
        $table.data('stickyMeta', meta);

        if ($scrollX.length && !$scrollX.data('stickyBound')) {
            $scrollX.on('scroll.stickyTable', function () {
                $(this).find('table.sticky-table').each(function () {
                    var m = $(this).data('stickyMeta');
                    if (m) {
                        syncStickyClone(m);
                    }
                });
            });
            $scrollX.data('stickyBound', true);
        }
    });

    updateStickyPositions();
}

setupStickyTables();
$(window).on('resize', function () {
    clearTimeout(window.__stickyTableTimer);
    window.__stickyTableTimer = setTimeout(function () {
        setupStickyTables();
        updateStickyPositions();
    }, 150);
});

var $stickyScrollContainer = getStickyScrollContainer();
$stickyScrollContainer.off('scroll.stickyTables').on('scroll.stickyTables', function () {
    updateStickyPositions();
});

$(document).off('ajaxComplete.stickyTables').on('ajaxComplete.stickyTables', function () {
    clearTimeout(window.__stickyTableAjaxTimer);
    window.__stickyTableAjaxTimer = setTimeout(function () {
        setupStickyTables();
        updateStickyPositions();
    }, 80);
});

window.refreshStickyTables = function (force) {
    if (force) {
        $('.page-content table.sticky-table').each(function () {
            var meta = $(this).data('stickyMeta');
            if (meta) {
                hideSticky(meta);
                meta.isStuck = false;
            }
        });
    }
    setupStickyTables();
    updateStickyPositions();
};



});


function UpdateCounter(mobile,orderid)
{
	var count = parseInt(document.querySelector('.ordercounter').textContent,10);
	$(".ordercounter").html(count-1);
	$("#searchorder").val(orderid);
	$("#pagesearch").val(mobile).trigger('change');
	$("#custOrd"+orderid).css("display","none");
	
}

</script>
<script src="{{ asset('panel/assets/js/select22.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/chosen.jquery.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script src="{{ asset('panel/assets/js/bootbox.js') }}"></script>




</body>
</html>
