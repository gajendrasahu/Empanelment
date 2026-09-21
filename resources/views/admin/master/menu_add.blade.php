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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{ __('menu.menu') }}
				</a>
			</li>
			<li><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('menu.html') }}')"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> MENU LIST</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.menu',0)}}" method="post" enctype="multipart/form-data">
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
							<option value="{{ $itm->menuid }}" {{ intval(old('menuid'))===$itm->menuid ? 'selected' : '' }}>{{ strtoupper(__($itm->mastermenu)) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('menuid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						ENTER MENU NAME<label id="req">*</label>
						<input type="text" class="form-control" name="mastermenu" id="mastermenu" value="{{old('mastermenu')}}" placeholder="MENU NAME" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('mastermenu') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						ENTER ICON VALUE<label id="req"></label>
						<input type="text" class="form-control" name="icon" id="icon" value="{{old('icon')}}" placeholder="FA FA-EYE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('icon') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-1">
						ORDER<label id="req">*</label>
						<input type="number" class="form-control numbers" name="displayorder" id="displayorder" value="{{old('displayorder',$displayorder)}}" placeholder="DISPLAY ORDER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('displayorder') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						ENTER URL VALUE<label id="req"></label>
						<input type="text" class="form-control" name="menuurl" id="menuurl" value="{{old('menuurl')}}" placeholder="MENU URL" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('menuurl') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-2">
						HAS SUB MENU ?<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="hassubmenu" id="hassubmenu" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU">
							<option value="0" @if(intval(old('hassubmenu'))=='0') 'selected' @endif>NO</option>
							<option value="1" @if(intval(old('hassubmenu'))=='1') 'selected' @endif>YES</option>
						</select>

						<span class="text-danger">@error('hassubmenu') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						KEEPING MENU ACTIVE VALUE<label id="req">*</label>
						<input type="text" class="form-control" name="activevalue" id="activevalue" value="{{old('activevalue')}}" placeholder="ACTIVE VALUE" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('activevalue') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						NEED TO PASS USER ID?<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="passuserid" id="passuserid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU">
							<option value="0" @if(intval(old('passuserid'))=='0') 'selected' @endif>NO</option>
							<option value="1" @if(intval(old('passuserid'))=='1') 'selected' @endif>YES</option>
						</select>

						<span class="text-danger">@error('passuserid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						MAP BUTTON ACTION<label id="req">*</label>
						<input type="hidden" name="actionids" id="actionids" value="{{ old('actionids')}}">
						<select class="form-control" name="actionid[]" id="actionid" multiple onchange="AddActions()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="--SELECT--">
							@foreach ($actions as $itm)
<option value="{{ $itm->actionid }}" @if(is_array(old('actionid')) && in_array($itm->actionid, old('actionid'))) selected @endif>{{ strtoupper($itm->actionname) }}</option>
							@endforeach
						
						</select>
						<span class="text-danger">@error('actionids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn">ADD MENU</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>


<div id="recordlist" class="tab-pane" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="12">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('menu.html') }}')">
							<option value="100">100</option>
							<option value="200">200</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="pid" id="pid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="PARENT MENU" onchange="loadData(1,'{{ route('menu.html') }}')">
							<option value=""></option>
							@foreach ($menus as $itm)
							<option value="{{ $itm->menuid }}">{{ __($itm->mastermenu) }}</option>
							@endforeach
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('menu.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.NO.</b></td>
					<td nowrap style=""><b>PARENT MENU</b></td>
					<td nowrap style=""><b>MENU NAME</b></td>
					<td nowrap style=""><b>ICON</b></td>
					<td nowrap style=""><b>ORDER</b></td>
					<td nowrap style=""><b>MENU URL</b></td>
					<td nowrap style=""><b>SUB MENU</b></td>
					<td nowrap style=""><b>ACTIVE VALUE</b></td>
					<td nowrap style="text-align:center;"><b>PASS ID</b></td>
					<td nowrap style=""><b>ACTIONS MAPPED</b></td>
					<td style="width:25px;"></td>
					<td style="width:25px;"></td>
				</tr>
				</thead>
				<tbody class="tabledata">
				</tbody>
				</table>
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


function loadData(page,r1)
{
	var parentid	=	document.getElementById("pid").value;
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		parentid:parentid
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").css("background-color","");
			$(".mytr").css("color","");
			$(this).css("background-color", "#438EB9");
			$(this).css("color","white");
		});
	});
}
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection