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
		@if(in_array(1,Session::get('actions')))
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Role Permission</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))		
<div id="home" class="tab-pane in active" style="padding:0px 14px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.commission')}}" method="post" enctype="multipart/form-data">
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
					<div class="table-responsive">			
						<table class="table table-bordered table-striped">
						<thead>
						<tr>
							<td colspan="20">
							<div id="tablehead">
								<select class="select2" name="roleid" id="roleid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Role" onchange="loadData(1,'{{ route('rolepermission.html') }}')">
									<option value=""></option>
									@foreach ($roles as $itm)
									<option value="{{ $itm->roleid }}">{{ $itm->role }}</option>
									@endforeach
								</select>
								<select class="select2" name="parentid" id="parentid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="MENU LIST" onchange="loadData(1,'{{ route('rolepermission.html') }}')">
									<option value=""></option>
									@foreach ($menus as $itm)
									<option value="{{ $itm->menuid }}">{{ __($itm->mastermenu) }}</option>
									@endforeach
								</select>
								<span class="input-icon" style="float:right;">
									<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('rolepermission.html') }}')" tabindex="<?php echo $t++;?>" />
									<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
								</span>
							</div>
							</td>
						</tr>
						</thead>
						<tbody class="tabledata">
						</tbody>
						</table>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>

$(".mytr").click(function(){
	$(".mytr").removeClass("selected-color").addClass("reset-color");
	$(this).removeClass("reset-color").addClass("selected-color");
});

function loadData(page,r1)
{
	var roleid		=	document.getElementById("roleid").value;
	var parentid	=	document.getElementById("parentid").value;
	if(roleid!='')
	{
		$.get(""+r1,
		{
			page:page,
			roleid:roleid,
			parentid:parentid
		},
		function(data, status){
			$(".tabledata").html(data);
			$(".mytr").click(function(){
				$(".mytr").css("background-color","");
				$(".mytr").css("color","");
				$(this).css("background-color", "skyblue");
				$(this).css("color","white");
			});
		});
	}
	else
	{
		$(".tabledata").html('<tr><td colspan="4" style="text-align:center;">--NO RECORD FOUND--</td></tr>');
	}
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection