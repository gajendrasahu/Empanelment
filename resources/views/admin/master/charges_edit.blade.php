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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Update Tier Charge</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))		
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.charges',$data->priceid)}}" method="post" enctype="multipart/form-data">
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
						Category <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="categoryid" id="categoryid" autofocus required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Category" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',$data->categoryid))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						Tier <label id="req">*</label>
						<select class="chosen-select form-control" name="tierid" id="tierid" autofocus required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Tier" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($tier as $itm)
							<option value="{{ $itm->tierid }}" {{ intval(old('tierid',$data->tierid))===$itm->tierid ? 'selected' : '' }}>{{ strtoupper($itm->tiername) }}</option>
							@endforeach
						</select>
						

						<span class="text-danger">@error('tierid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2 operating" @if($data->categoryid==2) style="display:none;" @endif>
						Operating Margin <label class="om" id="req">@if($data->categoryid==1) * @endif</label>
						<input type="text" class="form-control numbers" name="operatingmargin" id="operatingmargin" value="{{old('operatingmargin',$data->operatingmargin)}}" placeholder="OM (In %)" @if($data->categoryid==1) required @endif autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('operatingmargin') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Tax (In %)<label id="req">*</label>
						<input type="text" class="form-control numbers" name="tax" id="tax" value="{{old('tax',$data->tax)}}" placeholder="TAX (In %)" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('tax') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Admin Charges (In %)<label id="req">*</label>
						<input type="text" class="form-control numbers" name="admincharge" id="admincharge" value="{{old('admincharge',$data->admincharge)}}" placeholder="(In %)" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('admincharge') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-10">&nbsp;</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">SUBMIT</button>
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
function SetOM(val)
{
	if(val==1)
	{
		$(".operating").css("display","");
		$(".om").html("*");
		$("#operatingmargin").prop("required","required");
	}
	else
	{
		$("#operatingmargin").prop("required","");
		$(".om").html("");
		$(".operating").css("display","none");
	}
}

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection