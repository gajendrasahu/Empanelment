@php
use Carbon\Carbon;
	$eoi = DB::table('eoi_request')->where('userid', session('userId'))->where('eoistatus', '=', 0)->count();
	$drafted = DB::table('eoi_request')->where('userid', session('userId'))->where('eoistatus', '=', 1)->count();
	$approved = DB::table('eoi_request')->where('userid', session('userId'))->where('eoistatus', '=', 2)->count();
	$published = DB::table('eoi_request')->where('userid', session('userId'))->where('eoistatus','>=',3)->where('isordered',0)->where('iscancelled',0)->count();

	$ordered	=	DB::table('eoi_work_order')
					->where('department_id', session('departmentId'))
					->where('isActiveOrder',1)
					->whereDate('workorderduedate','>=',today())
					->count();

	$requestids	=	DB::table('eoi_request')
					->where('userid', Session('userId'))
					->distinct()
					->pluck('requestid')
					->toArray();

	$responses	=	DB::table('eoi_request_prebid_broadcast as a')
					->select('a.broadcastid', 'a.requestid', 'b.eoinumber', 'a.broadcastedon')
					->leftjoin('eoi_request as b', 'b.requestid', '=', 'a.requestid')
					->whereIn('a.requestid', $requestids)
					->get();
					
	$interviews	=	DB::table('eoi_request as a')
					->join('eoi_request_participation as b', 'a.requestid', '=', 'b.requestid')
					->select('a.requestid', 'a.eoinumber', 'b.interviewdate')
					->where('a.userid', session('userId'))
					->whereNotNull('b.interviewdate')
					->whereBetween('b.interviewdate', [
						Carbon::now()->startOfDay(),
						Carbon::now()->addDays(30)->endOfDay()
					])
					->distinct()
					->get();

@endphp
<div class="main-content">
	<div class="main-content-inner">

		<div class="col-sm-12 height-space"></div>
		@if(Session::has('success'))
			<!--
						<div class="col-sm-12 padding-0-35">
							<br>
							<div class="alert alert-success" style="background-color:white;"><i class="fa fa-smile-o"></i> {{ strtoupper(Session::get('success')); }} {{ strtoupper(Session::get('userName')); }} !</div>
						</div>
						-->
		@endif

		<div class="col-sm-12">
			<div class="dashboard-header">
				<h1 class="welcome-title">Welcome, {{Session('userName')}}</h1>
				<p class="subtitle">Stay updated with daily activities, reports, and announcements from CHiPS and
					empanelled vendors.</p>
			</div>
		</div>
		<div class="col-sm-12">
			<div class="col-sm-12">
				<div class="col-sm-3 animated flipInX delay-01">
					<a class="dashboard-card-link" href="{{route('pm.eoilist', 0)}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-yellow">
							<span class="dashboard-icon"><i class="fa fa-folder-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count">{{$eoi}}</div>
								<p class="dashboard-card-title">Ongoing Eoi Request(s)</p>
							</div>
						</div>
					</a>
				</div>

				<div class="col-sm-3 animated flipInX delay-02">
					<a class="dashboard-card-link" href="{{route('pm.eoilist', 1)}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-orange">
							<span class="dashboard-icon"><i class="fa fa-folder-open-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count">{{$drafted}}</div>
								<p class="dashboard-card-title">Ongoing Draft EoI</p>
							</div>
						</div>
					</a>
				</div>

				<div class="col-sm-3 animated flipInX delay-03">
					<a class="dashboard-card-link" href="{{route('pm.eoilist', 2)}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-blue">
							<span class="dashboard-icon"><i class="fa fa-folder-open-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count">{{$approved}}</div>
								<p class="dashboard-card-title">EoI Approved</p>
							</div>
						</div>
					</a>
				</div>

				<div class="col-sm-3 animated flipInX delay-04">
					<a class="dashboard-card-link" href="{{route('pm.eoilist', 3)}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-purple">
							<span class="dashboard-icon"><i class="fa fa-folder-open-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count">{{$published}}</div>
								<p class="dashboard-card-title">EoI Published</p>
							</div>
						</div>
					</a>
				</div>

				<div class="col-sm-3 animated flipInX delay-05 mt-20">
					<a class="dashboard-card-link" href="{{route('pm.orderslist')}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-green">
							<span class="dashboard-icon"><i class="fa fa-folder-open-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count">{{$ordered}}</div>
								<p class="dashboard-card-title">Work Order Issued</p>
							</div>
						</div>
					</a>
				</div>
				
				
				<div class="col-sm-9 animated flipInX delay-06 mt-20">
					<div class="bg-green" style="background-color:#fff; padding:18px 20px; border-radius:12px;">
						<div style="display:flex!important; flex-direction: row; align-items:center; gap:8px;">
							<span class="dashboard-icon"><i class="fa fa-calendar"></i></span> <span>Upcoming Tentative Interview Dates (Next 30 Days)</span>
						</div>
						@if($interviews->count()!=0)
						<ul class="interview-list mt-10">
							@foreach($interviews as $interview)
							<li><a href="{{route('view.pmpptresumes', Crypt::encrypt($interview->requestid))}}" style="font-weight:bold; font-size:12px; text-decoration:none;" class="font-10">{{$interview->eoinumber}} [{{date('d\-m\-Y',strtotime($interview->interviewdate))}}]</a></li>
							@endforeach
						</ul>
						@else
						<ul class="interview-list mt-10">
						<span style="font-weight:bold; font-size:12px; text-decoration:none; line-height:80px;">No data found</span>
						</ul>
						@endif
					</div>					
				</div>
				
			</div>

			<div class="col-sm-12">&nbsp;</div>


			@if($responses->count() != 0)
				<div class="col-sm-12 animated flipInX delay-04">
					<div class="horizontal-card">
						<div class="card-header">
							<i class="fa fa-folder-o card-icon"></i>
							<h4 class="card-title">Published Pre-bid Response</h4>
						</div>
						<ul class="notification-list">
							@foreach($responses as $response)
								<li style="list-style:none;">
									<a href="{{route('show.pmprebidanswer', Crypt::encrypt($response->broadcastid))}}"
										class="form-label font-12">
										<i class="fa fa-hand-o-right"></i> {{$response->eoinumber}} <i
											style="padding:0px 10px;">Date :
											{{date('d\-m\-Y, h:i A', strtotime($response->broadcastedon))}}</i>
									</a>
								</li>
							@endforeach
						</ul>

					</div>
				</div>
			@endif
			
			<div class="col-sm-12 dashboardContent"></div>
		</div>



	</div>
</div>
<script>
	setTimeout(function () { loadData('{{ route('pmdashboard.html') }}'); }, 1000);

	function loadData(r1) {

		$.get("" + r1,
			{
			},
			function (data, status) {
				$(".dashboardContent").html(data);
			});
	}

</script>
