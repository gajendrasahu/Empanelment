<div id="sidebar" class="sidebar responsive ace-save-state" style="background-color:#f9f9f9!important; border:1px solid #eee; border-bottom: 0!important;">
	<ul class="nav nav-list">
		@if(intval(session('issuper')) === 1)
			<li @if(session('adminmenu') === 'menus') class="active open" @endif style="border:none!important;">
				<a href="#" class="dropdown-toggle masters">
					<i class="menu-icon fa fa-bars"></i>
					<span class="menu-text">{{ __('menu.menu') }}</span>
					<b class="arrow fa fa-angle-down"></b>
				</a>
				<b class="arrow"></b>
				<ul class="submenu" style="border:none!important;">
					<li @if(session('adminsubmenu') === 'addaction') class="active" @endif>
						<a href="{{ route('add.action') }}">
							<i class="menu-icon fa fa-caret-right"></i>{{ __('menu.addaction') }}
						</a>
						<b class="arrow"></b>
					</li>
					<li @if(session('adminsubmenu') === 'addmenu') class="active" @endif>
						<a href="{{ route('add.menu') }}">
							<i class="menu-icon fa fa-caret-right"></i>{{ __('menu.addmenu') }}
						</a>
						<b class="arrow"></b>
					</li>
					<li @if(session('adminsubmenu') === 'addmenu') class="active" @endif>
						<a href="{{ route('sync.menupermission') }}">
							<i class="menu-icon fa fa-caret-right"></i> Sync Menu Permissions
						</a>
						<b class="arrow"></b>
					</li>
					<li @if(session('adminsubmenu') === 'addmenu') class="active" @endif>
						<a href="{{ route('add.actionpermission') }}">
							<i class="menu-icon fa fa-caret-right"></i> Add Action Permission
						</a>
						<b class="arrow"></b>
					</li>
					<li @if(session('adminsubmenu') === 'addmenu') class="active" @endif>
						<a href="{{ route('add.rolepermission') }}">
							<i class="menu-icon fa fa-caret-right"></i> Map Role Permissions
						</a>
						<b class="arrow"></b>
					</li>
				</ul>
			</li>
		@endif
		@foreach($menuItems as $menuItem)
			<li @if(session('adminmenu') === $menuItem->activevalue)
			class="active @if($menuItem->hassubmenu == 1) {{ 'open' }} @endif" @endif style="border:none!important;">
				@if($menuItem->passuserid == 0)
					<a @if($menuItem->menuurl != "") href="{{ route($menuItem->menuurl) }}" @else href="#" @endif
						@if($menuItem->hassubmenu == 1) class="dropdown-toggle masters" @endif>
				@else
						<a @if($menuItem->menuurl != "")
						href="{{ route($menuItem->menuurl, Crypt::encrypt(Session::get('userId'))) }}" @else href="#" @endif
							@if($menuItem->hassubmenu == 1) class="dropdown-toggle masters" @endif>
					@endif
						<i class="menu-icon {{ $menuItem->icon }}"></i>
						<span class="menu-text form-label"> {{ __($menuItem->mastermenu) }}</span>
						@if($menuItem->hassubmenu == 1)
							<b class="arrow fa fa-angle-down"></b>
						@endif
					</a>
					<b class="arrow"></b>
					@if(isset($menuItem->children) && count($menuItem->children) > 0)
						<ul class="submenu form-label">
							@foreach ($menuItem->children as $child)
								<li @if(session('adminsubmenu') === $child->activevalue) style="background-color:white;" class="active" @endif>
									<a href="{{ route($child->menuurl) }}">
										<i class="menu-icon fa fa-caret-right"></i>
										{{ __($child->mastermenu) }}
									</a>
									<b class="arrow"></b>
								</li>
							@endforeach
						</ul>
					@endif
			</li>
		@endforeach
<!--
<li style="border:none!important;">
	<a href="{{ route('logout') }}">
		<i class="menu-icon fa fa-power-off"></i>
		<span class="menu-text form-label"> {{ __('menu.logout') }}</span>
	</a>
	<b class="arrow"></b>
</li>
-->
	</ul>
	<div class="sidebar-toggle sidebar-collapse" id="sidebar-collapse">
		<i id="sidebar-toggle-icon" class="ace-icon fa fa-angle-double-left ace-save-state"
			data-icon1="ace-icon fa fa-angle-double-left" data-icon2="ace-icon fa fa-angle-double-right"></i>
	</div>
</div>
