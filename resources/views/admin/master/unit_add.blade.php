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
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> ADD MEASUREMENT UNIT
				</a>
			</li>
		@endif
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('unit.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> MEASUREMENT UNIT LIST</a></li>
		@endif

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.unit',0)}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-3">
						MEASUREMENT UNIT NAME<label id="req">*</label>
						<input type="text" class="form-control" name="unitname" id="unitname" value="{{old('unitname')}}" placeholder="MEASUREMENT UNIT" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('unitname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">ADD UNIT</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane @if(!in_array(1,Session::get('actions'))) in active @endif" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="4">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('unit.html') }}')">
							<option value="15">15</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('unit.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.NO.</b></td>
					<td nowrap style=""><b>MEASUREMENT UNIT NAME</b></td>
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
@endif
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

<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
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

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection