@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE MENU
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.menu',$data->menuid)}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@csrf
				<div class="form-group">
					<div class="col-sm-2">
						PARENT MENU<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="menuid" id="menuid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU">
							<option value=""></option>
							@foreach ($menus as $itm)
							<option value="{{ $itm->menuid }}" {{ intval(old('menuid',$data->parentid))===$itm->menuid ? 'selected' : '' }}>{{ strtoupper($itm->mastermenu) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('menuid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						ENTER MENU NAME<label id="req">*</label>
						<input type="text" class="form-control" name="mastermenu" id="mastermenu" value="{{old('mastermenu',$data->mastermenu)}}" placeholder="MENU NAME" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('mastermenu') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						ENTER ICON VALUE<label id="req"></label>
						<input type="text" class="form-control" name="icon" id="icon" value="{{old('icon',$data->icon)}}" placeholder="FA FA-EYE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('icon') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-1">
						ORDER<label id="req">*</label>
						<input type="number" class="form-control numbers" name="displayorder" id="displayorder" value="{{old('displayorder',$data->displayorder)}}" placeholder="DISPLAY ORDER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('displayorder') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						ENTER URL VALUE<label id="req"></label>
						<input type="text" class="form-control" name="menuurl" id="menuurl" value="{{old('menuurl',$data->menuurl)}}" placeholder="MENU URL" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('menuurl') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-2">
						HAS SUB MENU ?<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="hassubmenu" id="hassubmenu" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU">
							<option value="0" {{ intval(old('menuid',$data->hassubmenu))===0 ? 'selected' : '' }}>NO</option>
							<option value="1" {{ intval(old('menuid',$data->hassubmenu))===1 ? 'selected' : '' }}>YES</option>
						</select>

						<span class="text-danger">@error('hassubmenu') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						KEEPING MENU ACTIVE VALUE<label id="req">*</label>
						<input type="text" class="form-control" name="activevalue" id="activevalue" value="{{old('activevalue',$data->activevalue)}}" placeholder="ACTIVE VALUE" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('activevalue') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						NEED TO PASS USER ID?<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="passuserid" id="passuserid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU">
							<option value="0" {{ intval(old('menuid',$data->passuserid))===0 ? 'selected' : '' }}>NO</option>
							<option value="1" {{ intval(old('menuid',$data->passuserid))===1 ? 'selected' : '' }}>YES</option>
						</select>

						<span class="text-danger">@error('passuserid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						MENU ACTIONS<label id="req">*</label>
						<input type="hidden" name="actionids" id="actionids" value="{{ old('actionids',$data->actionids)}}">
						<select class="form-control" name="actionid[]" id="actionid" multiple onchange="AddActions()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="--SELECT--">
							@php
							$nds = explode(",",$data->actionids);
							@endphp
							@foreach ($actions as $itm)
<option value="{{ $itm->actionid }}" @if(is_array(old('actionid',$nds)) && in_array($itm->actionid,old('actionid',$nds))) selected @endif>{{ strtoupper($itm->actionname) }}</option>

							@endforeach
						
						</select>
						<span class="text-danger">@error('actionids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn">UPDATE MENU</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>

</div>
</div>


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>




	
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>	
<script>
$('#actionid').multiselect({
	 enableCaseInsensitiveFiltering:1,
	 enableClickableOptGroups:1,
	 enableHTML:1,
	 maxHeight:200,		 
	 buttonClass: 'form-control',
	 buttonWidth: '100%',
	 buttonHeight: '100%',
});	
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>

@endsection