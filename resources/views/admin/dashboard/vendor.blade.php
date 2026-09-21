@php
	$subUserId	=	Session('subUserId') ?? NULL;
	$floated = DB::table('eoi_request_floated as a')
				->select('a.floatid')
				->join('eoi_request as b','b.requestid','=','a.requestid')
				->where('a.userid', Session::get('userId'))
				->when($subUserId!=NULL,function($query) use ($subUserId){
					return $query->where('a.subuserid',$subUserId);
				})
				->where('b.iscancelled',0)
				->count();

	$participated = DB::table('eoi_request_participation as a')
					->join('eoi_request_floated as b','b.floatid','=','a.floatid')
					->join('eoi_request as c','c.requestid','=','a.requestid')
					->where('a.userid','=',Session::get('userId'))
					->where('a.vendorid','=',Session::get('vendorId'))
					->when($subUserId!=NULL,function($query) use ($subUserId){
						return $query->where('b.subuserid',$subUserId);
					})
					->whereNotNull('b.signedcopyofeoi')
					->where('c.iscancelled',0)
					->count();

	$queries=	DB::table('eoi_request_prebid_broadcast as a')
				->select(
					'b.eoinumber',
					'a.broadcastedon',
					'a.broadcastid',
					'a.attachment',
					DB::raw("
						CASE 
							WHEN DATEDIFF(CURDATE(), a.broadcastedon) <= 7 
								THEN 'New'
							ELSE CONCAT(DATEDIFF(CURDATE(), a.broadcastedon), ' days ago')
						END as daysago
					")
				)
				->join('eoi_request as b','b.requestid', '=', 'a.requestid')
				->leftJoin('eoi_request_floated as c', 'c.requestid', '=', 'a.requestid')
				->whereRaw("FIND_IN_SET(?,a.vendorids)", [Session('vendorId')])
				->where('b.isordered', 0)
				->when($subUserId != NULL, function($query) use ($subUserId) {
					return $query->where('c.subuserid', $subUserId);
				})
				->where('b.deadlinedate','>',now())
				->where('c.vendorid',Session('vendorId'))
				->whereRaw("a.broadcastedon>=DATE_SUB(CURDATE(),INTERVAL 30 DAY)")
				->orderby('a.broadcastedon', 'DESC')
				->get();	
	
	$requests	=	DB::table('eoi_request_floated as erf')
					->where('erf.vendorid', Session('vendorId'))
					->whereNotExists(function ($query) {
						$query->select(DB::raw(1))
							->from('eoi_request as er')
							->whereColumn('er.requestid', 'erf.requestid')
							->where('er.eoistatus', '<', 3);
					})
					->select('erf.requestid')
					->distinct()
					->get();
	
	$published = DB::table('eoi_request as a')
		->select('a.requestid', 'a.projecttitle', 'a.eoinumber', 'a.releasedate', 'a.prebidlastdate', 'a.deadlinedate', 'a.interviewdate', 'b.floatid')
		->leftjoin('eoi_request_floated as b', 'b.requestid', '=', 'a.requestid')
		->whereIn('a.requestid', $requests->pluck('requestid'))
		->where('b.vendorid', Session('vendorId'))
		->orderBy('a.floatdate', 'desc')
		->get();

	$joined = DB::table('eoi_request as a')
		->select('a.requestid', 'a.projecttitle', 'a.eoinumber', 'a.releasedate', 'a.prebidlastdate', 'a.deadlinedate', 'a.interviewdate', 'b.participationdate', 'b.floatid')
		->leftjoin('eoi_request_participation as b', 'b.requestid', '=', 'a.requestid')
		->whereIn('a.requestid', $requests->pluck('requestid'))
		->where('b.vendorid', Session('vendorId'))
		->orderBy('b.participationdate', 'desc')
		->get();


	$orders	=	DB::table('eoi_work_order')
				->where('vendorid', Session('vendorId'))
				->when($subUserId!=NULL,function($query) use ($subUserId){
					return $query->where('subuserid',$subUserId);
				})
				->whereDate('workorderduedate','>=',today())
				->where('isActiveOrder',1)
				->count();

@endphp

<div class="main-content" style="background-color:#fdfdfd!important;">
	<div class="main-content-inner">

		<div class="col-sm-12 height-space"></div>
		<div class="col-sm-12">
			<div class="dashboard-header">
				<h1 class="welcome-title">Welcome, {{ucwords(strtolower(Session('userName')))}}</h1>
				<p class="subtitle">Stay updated with daily activities, reports, and announcements from CHiPS and
					departments.</p>
			</div>
		</div>

		<div class="col-sm-12" style="padding:0px 18px!important;">
			<div class="col-sm-3 animated flipInX delay-01">
				<div class="horizontal-card">
					<div class="card-header">
						<i class="fa fa-folder-o card-icon"></i>
						<h4 class="card-title">Published EoI</h4>
					</div>
					<div class="card-count">{{$floated}}</div>
					<div class="card-footer">
						@if($floated != 0)
							<a href="{{route('floated.eois')}}"><button class="view-all-btn">View All</button></a>
						@else
							<button class="view-all-btn" disabled>View All</button>
						@endif
					</div>
				</div>
			</div>

			<div class="col-sm-3 animated flipInX delay-02">
				<div class="horizontal-card">
					<div class="card-header">
						<i class="fa fa-folder-o card-icon"></i>
						<h4 class="card-title">Participated EoI</h4>
					</div>
					<div class="card-count">{{$participated}}</div>
					<div class="card-footer">
						@if($participated != 0)
							<a href="{{route('participated.eois')}}"><button class="view-all-btn">View All</button></a>
						@else
							<button class="view-all-btn" disabled>View All</button>
						@endif
					</div>
				</div>
			</div>
			@if($orders != 0)
				<div class="col-sm-3 animated flipInX delay-03">
					<div class="horizontal-card">
						<div class="card-header">
							<i class="fa fa-folder-o card-icon"></i>
							<h4 class="card-title">Work Orders</h4>
						</div>
						<div class="card-count">{{$orders}}</div>
						<div class="card-footer">
							<a href="{{route('vendor.orderslist')}}"><button class="view-all-btn">View All</button></a>
						</div>
					</div>
				</div>
			@endif
			<div class="col-sm-12">&nbsp;</div>
			@if($queries->count() != 0)
				<div class="col-sm-12 animated flipInX delay-04">
					<div class="horizontal-card">
						<div class="card-header">
							<i class="fa fa-folder-o card-icon"></i>
							<h4 class="card-title">Responses to Pre-bid Queries</h4>
						</div>
						<ul class="notification-list">
							@foreach($queries as $query)
								<li style="list-style:none; line-height:25px!important;">
<a href="{{route('view.prebidresponse', Crypt::encrypt($query->broadcastid))}}" class="form-label font-12">
	<span style="width:80px; text-align:center; margin-left:-15px;" class="label @if($query->daysago=='New') label-warning @else label-success @endif arrowed-right">
	{{$query->daysago}}
	</span>
	&nbsp;&nbsp;&nbsp;{{$query->eoinumber}} <i style="padding:0px 10px;">Date : {{date('d\-m\-Y, h:i A', strtotime($query->broadcastedon))}}</i>
</a>

@if($query->attachment)
<a href="{{route('view.uploadedfile',Crypt::encrypt($query->attachment))}}" target="_blank">
	<span style="width:130px; text-align:center; outline:none; border-radius:4px;" class="label label-success">
		<i class="fa fa-file-pdf-o"></i> View Attachment
	</span>
</a>
@endif


								</li>
							@endforeach
						</ul>

					</div>
				</div>
			@endif
			<div class="col-sm-12">&nbsp;</div>
			@php
				$wos	=	DB::table('eoi_work_order')
							->where('markedbyvendor',0)
							->where('vendorid', Session('vendorId'))
							->whereRaw('CURDATE() <= DATE_ADD(orderdate, INTERVAL 15 DAY)')
							->get();
			@endphp
			@if($wos->count() > 0)
				<div class="col-sm-12 animated flipInX delay-05">
					<div class="vendor-horizontal-card">
						<div class="vendor-card-header">
							<i class="fa fa-calendar card-icon"></i>
							<h4 class="vendor-card-title">New Work Order</h4>
						</div>
						<div class="vendor-card-body">
							@php
								//$wos = DB::table('eoi_work_order')->where('markedbyvendor', 0)->where('vendorid', Session('vendorId'))->get();
							@endphp
							@foreach($wos as $wo)
								<div class="vendor-cards">
									<div class="vendor-cards-title">
										<span class="material-icons-outlined">topic</span>
										<span class="form-label">{{$wo->ordernumber}}</span>
									</div>
									<div class="vendor-cards-content">
										<table>
											<tr>
												<td>Order Date</td>
												<td>: {{date('d\-m\-Y', strtotime($wo->orderdate))}}</td>
											</tr>
											<!--
											<tr>
												<td>Cost of Resources (including tax)</td>
												<td>: <span class="cost-value">{{$wo->totalbudget}}</span></td>
											</tr>
											<tr>
												<td>CHiPS Admin Charge</td>
												<td>: <span class="admin-value">{{$wo->totaladmincharge}}</span></td>
											</tr>
											-->
											<tr>
												<td>Order Value</td>
												<td>: <span class="grand-value">{{$wo->workorderamount}}</span></td>
											</tr>
											<tr>
												<td colspan="2">
													<a href="{{ route('show.vendorworkorder', Crypt::encrypt($wo->orderid)) }}"
														class="action-a">
														<button class="view-all-btn animated flipInX delay-07">
															<span class="fa fa-folder-o"></span> View Details
														</button>
													</a>
												</td>
											</tr>
										</table>
									</div>
								</div>
							@endforeach
						</div>
					</div>
				</div>
			@endif
			<!--
			<div class="col-sm-12 animated flipInX delay-06">
				<div class="vendor-horizontal-card">
					<div class="vendor-card-header">
						<i class="fa fa-calendar card-icon"></i>
						<h4 class="vendor-card-title">Published EoI</h4>
					</div>
					<div class="vendor-card-body">
						@foreach($published as $publish)
							<div class="vendor-cards">
								<div class="vendor-cards-title">
									<span class="material-icons-outlined">topic</span>
									<span class="form-label">{{substr($publish->projecttitle, 0, 30)}}..<br>
										<p>{{$publish->eoinumber}}</p>
									</span>
								</div>
								<div class="vendor-cards-content">
									<table>
										<tr>
											<td>Release Date</td>
											<td>: {{date('d\-m\-Y', strtotime($publish->releasedate))}}</td>
										</tr>
										<tr>
											<td>Pre-bid Query Date</td>
											<td>: {{date('d\-m\-Y', strtotime($publish->prebidlastdate))}}</td>
										</tr>
										<tr>
											<td>Dead Line Date</td>
											<td>: {{date('d\-m\-Y', strtotime($publish->deadlinedate))}}</td>
										</tr>
										<tr>
											<td>Interview Date</td>
											<td>: {{date('d\-m\-Y', strtotime($publish->interviewdate))}}</td>
										</tr>
										<tr>
											<td colspan="2">
												<a href="{{ route('floated.view', Crypt::encrypt($publish->floatid))}}"
													class="action-a">
													<button class="view-all-btn animated flipInX delay-07">
														<span class="fa fa-folder-o"></span> View Details
													</button>
												</a>
											</td>
										</tr>
									</table>
								</div>
							</div>
						@endforeach
						@if($published->count() == 0)
							No Record Found
						@endif
					</div>
				</div>
			</div>
			-->
			<!--
			<div class="col-sm-12 animated flipInX delay-07">
				<div class="vendor-horizontal-card">
					<div class="vendor-card-header">
						<i class="fa fa-calendar card-icon"></i>
						<h4 class="vendor-card-title">Participated EoI</h4>
					</div>
					<div class="vendor-card-body">
						@foreach($joined as $publish)
							<div class="vendor-cards">
								<div class="vendor-cards-title">
									<span class="material-icons-outlined">topic</span>
									<span class="form-label">{{substr($publish->projecttitle, 0, 35)}}<br>
										<p>{{$publish->eoinumber}}</p>
									</span>
								</div>
								<div class="vendor-cards-content">
									<table>
										<tr>
											<td>Release Date</td>
											<td>: {{date('d\-m\-Y h:i A', strtotime($publish->releasedate))}}</td>
										</tr>
										<tr>
											<td>Pre-bid Query Date</td>
											<td>: {{date('d\-m\-Y h:i A', strtotime($publish->prebidlastdate))}}</td>
										</tr>
										<tr>
											<td>Dead Line Date</td>
											<td>: {{date('d\-m\-Y h:i A', strtotime($publish->deadlinedate))}}</td>
										</tr>
										<tr>
											<td>Interview Date</td>
											<td>: {{date('d\-m\-Y h:i A', strtotime($publish->interviewdate))}}</td>
										</tr>
										<tr>
											<td colspan="2">
												<a href="{{ route('fillform.view', Crypt::encrypt($publish->floatid))}}"
													class="action-a">
													<button class="view-all-btn animated flipInX delay-07">
														<span class="fa fa-folder-o"></span> View Details
													</button>
												</a>
											</td>
										</tr>
									</table>
								</div>
							</div>
						@endforeach
						@if($joined->count() == 0)
							No Record Found
						@endif

					</div>
				</div>
			</div>
			-->
			<div class="col-sm-12">&nbsp;</div>


		</div>



	</div>
</div>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
	// Collect all elements
	document.querySelectorAll('.vendor-cards').forEach(card => {
		let cost = parseFloat(card.querySelector('.cost-value').textContent);
		let admin = parseFloat(card.querySelector('.admin-value').textContent);
		let grand = parseFloat(card.querySelector('.grand-value').textContent);

		card.querySelector('.cost-value').textContent = formatIndianNumber(cost);
		card.querySelector('.admin-value').textContent = formatIndianNumber(admin);
		card.querySelector('.grand-value').textContent = formatIndianNumber(grand);
	});
</script>