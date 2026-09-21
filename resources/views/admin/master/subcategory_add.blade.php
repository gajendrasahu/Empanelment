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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> ADD SUB CATEGORY
				</a>
			</li>
			<li><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('subcategory.html') }}')"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> SUB CATEGORY LIST</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.subcategory',0)}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-2">
						PARENT CATEGORY<label id="req">*</label>
						<select class="chosen-select form-control" name="categoryid" id="categoryid" autofocus required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CATEGORY">
							<option value=""></option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid'))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->category) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						ENTER SUB CATEGORY<label id="req">*</label>
						<input type="text" class="form-control" name="subcategory" id="subcategory" value="{{old('subcategory')}}" placeholder="SUB CATEGORY NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('subcategory') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-1">
						ORDER<label id="req">*</label>
						<input type="number" class="form-control numbers" name="displayorder" id="displayorder" value="{{old('displayorder',$displayorder)}}" placeholder="DISPLAY ORDER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('displayorder') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						ICON<label id="req"> (WIDTH=HEIGHT, MAX : 500 KB)</label>
						<input type="file" class="form-control" name="subcategoryicon" id="subcategoryicon" accept="image/*" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('subcategoryicon') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-3">
						PAGE IMAGE<label id="req"> (1900x450 PX, MAX : 500 KB)</label>
						<input type="file" class="form-control" name="subcategorypage" id="subcategorypage" accept="image/*" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('subcategorypage') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-10" align="left"></div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn">ADD CATEGORY</button>
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
					<td colspan="9">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('subcategory.html') }}')">
							<option value="15">15</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="catid" id="catid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CATEGORY NAME" onchange="loadData(1,'{{ route('subcategory.html') }}')">
							<option value=""></option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->category) }}</option>
							@endforeach
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('subcategory.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>S.NO.</b></td>
					<td nowrap style=""><b>CATEGORY</b></td>
					<td nowrap style=""><b>SUB CATEGORY</b></td>
					<td nowrap style=""><b>DISPLAY ORDER</b></td>
					<td nowrap style=""><b>CREATED BY</b></td>
					<td nowrap style=""><b>CATEGORY ICON</b></td>
					<td nowrap style=""><b>CATEGORY PAGE</b></td>
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
function loadData(page,r1)
{
	var categoryid	=	document.getElementById("catid").value;
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid
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


jQuery(function($) {	
	$('#subcategoryicon').ace_file_input({
		no_file:'No File ...',
		btn_choose:'SUB CATEGORY ICON',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	})
	.on('change', function() {
		var reader = new FileReader();
		reader.readAsDataURL(this.files[0]);
		reader.onload = function (e) {
		  var image = new Image();
		  image.src = e.target.result;
		  image.onload = function () {
			var height = this.height;
			var width = this.width;
			if (width!=height) 
			{
				bootbox.alert("IMAGE WIDTH AND HEIGHT MUST BE SAME");
				$('#subcategoryicon').ace_file_input('reset_input');
				$('#subcategoryicon').ace_file_input('reset_ui');
				$('#subcategoryicon').ace_file_input('reset_input_field');
			}
		  };
		};			
	});

	$('#subcategorypage').ace_file_input({
		no_file:'No File ...',
		btn_choose:'PAGE IMAGE',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection