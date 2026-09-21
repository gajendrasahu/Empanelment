<div id="sidebar" class="sidebar responsive ace-save-state">
	<ul class="nav nav-list">
		<li @if(session('vendormenu') == 'dashboard') class="active" @endif>
			<a href="{{route('vendorpanel/dashboard')}}">
				<i class="menu-icon fa fa-tachometer"></i>
				<span class="menu-text"> DASHBOARD </span>
			</a>
			<b class="arrow"></b>
		</li>
		<li @if(session('vendormenu') == 'profiledetail') class="active" @endif>
			@if($logindata->isemployee == 0)
				<a href="{{route('vendor.profile')}}">
					<i class="menu-icon fa fa-user"></i>
					<span class="menu-text"> PROFILE DETAIL </span>
				</a>
			@else
				<a href="{{route('employee.profile')}}">
					<i class="menu-icon fa fa-user"></i>
					<span class="menu-text"> PROFILE DETAIL </span>
				</a>

			@endif
			<b class="arrow"></b>
		</li>
		@if($logindata->isemployee == 0)
			<li @if(session('vendormenu') == 'openorderlist') class="active" @endif>
				<a href="{{route('openorder.list')}}">
					<i class="menu-icon fa fa-spinner fa-spin"></i>
					<span class="menu-text"> OPEN ORDERS </span>
				</a>
				<b class="arrow"></b>
			</li>
		@endif
		<li @if(session('vendormenu') == 'orderlist') class="active" @endif>
			<a href="">
				<i class="menu-icon fa fa-list-alt"></i>
				<span class="menu-text"> MY ORDERS LIST </span>
			</a>
			<b class="arrow"></b>
		</li>
		@if($logindata->isemployee == 0 && $logindata->verificationstatus == 1)
			<li @if(session('vendormenu') == 'addemployee') class="active" @endif>
				<a href="{{route('vendor.addemployee')}}">
					<i class="menu-icon fa fa-users"></i>
					<span class="menu-text"> CREATE EMPLOYEE </span>
				</a>
				<b class="arrow"></b>
			</li>
		@endif
		<li>
			<a href="">
				<i class="menu-icon fa fa-inr"></i>
				<span class="menu-text"> TRANSACTION </span>
			</a>
			<b class="arrow"></b>
		</li>

		<li>
			<a href="{{ route('vendorlogout') }}">
				<i class="menu-icon fa fa-power-off"></i>
				<span class="menu-text"> {{ __('menu.logout') }}</span>
			</a>
			<b class="arrow"></b>
		</li>

	</ul>
	<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
		<i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state"
			data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
	</div>
</div>