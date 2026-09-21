@php
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
				<p class="subtitle">Stay updated with daily activities, reports, and announcements from CHiPS.</p>
			</div>
		</div>
		<div class="col-sm-12">
			<!--
			<div class="col-sm-12 dashboard-cards-row five-cards-row">
				<div class="col-sm-3 animated flipInX delay-01">
					<a class="dashboard-card-link" href="{{route('dept.eoilist',0)}}">
						<div class="dashboard-horizontal-card role-dashboard-card bg-yellow">
							<span class="dashboard-icon"><i class="fa fa-folder-o"></i></span>
							<div class="role-card-body">
								<div class="dashboard-card-count"></div>
								<p class="dashboard-card-title">Request Letter to CHiPS</p>
							</div>
						</div>
					</a>
				</div>
			</div>
			-->
			<div class="col-sm-12">&nbsp;</div>
			<div class="col-sm-12">&nbsp;</div>
		</div>



	</div>
</div>
