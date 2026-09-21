@extends('admin.admin_master')
@section('admin')
@php($t=1)
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Update Firm Detail</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px;">
			<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
				<div class="row">
<div class="row">
	<form name="frm" id="frm" action="{{ route('update.firm',Crypt::encrypt($data->vendorid)) }}" method="post" enctype="multipart/form-data">
		@if(Session::has('success'))
		<div class="col-sm-12">
			<div class="alert alert-success">{{ Session::get('success') }}</div>
		</div>
		@endif
		@if(Session::has('fail'))
		<div class="col-sm-12">
			<div class="alert alert-danger">{{ Session::get('fail') }}</div>
		</div>
		@endif
		@csrf
				<div class="form-group">

					<div class="col-sm-6">
						Firm Name <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="text" class="form-control" name="companyname" id="companyname" value="{{old('companyname',$data->companyname)}}" placeholder="Firm Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('companyname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-3">
						Email <label id="req">(login id)*</label>
						<input type="text" class="form-control" name="email" id="email" value="{{old('email',$data->email)}}" placeholder="Email" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Mobile Number <label id="req">&nbsp;</label>
						<input type="number" class="form-control numbers" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber',$data->mobilenumber)}}" placeholder="Mobile Number" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						Firm Short Name <label id="req">*</label>
						<input type="text" class="form-control" name="shortname" id="shortname" value="{{old('shortname',$data->shortname)}}" placeholder="Short Name" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('shortname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						LOI Number <label id="req">*</label>
						<input type="text" class="form-control" required name="loinumber" id="loinumber" value="{{old('loinumber',$data->loinumber)}}" placeholder="LOI Number" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('loinumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Category <label id="req">*</label>
						<select class="form-control" name="categoryid" id="categoryid" placeholder="Tier" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="DisplaySectors()">
							<option value="">--Category--</option>
							@foreach($category as $cats)
							<option value="{{$cats->categoryid}}" @if($cats->categoryid==old('categoryid',$data->categoryid)) selected @endif>{{$cats->jobcategory}}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Tier <label id="req">*</label>
						<select class="form-control" name="tierid" id="tierid" required placeholder="Tier" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="">--Tier--</option>
							@foreach($tier as $tiers)
							<option value="{{$tiers->tierid}}" @if($tiers->tierid==old('tierid',$data->tierid)) selected @endif>{{$tiers->tiername}}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('tierid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						Office Address <label id="req">*</label>
						<input type="text" class="form-control" required name="address" id="address" value="{{old('address',$data->officelocation)}}" placeholder="Address" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('address') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-6">
					<div class="sectors">
					Applicable Sectors (Select all that apply)<br>
					<span class="text-danger">@error('sector') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span><br>
						@foreach($sectors as $sector)
<label class="font-12" style="display: inline-flex; align-items: center; gap: 6px; line-height:20px; vertical-align:middle; font-weight:normal;">
    <input type="checkbox" name="sector[]" value="{{ $sector->sectorid }}" style="margin-top:2px;" {{ in_array($sector->sectorid, old('sector', [])) ? 'checked' : '' }} @if($sector->isAvailable==1) checked @endif> {{ $sector->sectorname }}
</label>
<br>						
						@endforeach
					</div>
					</div>
					<div class="col-sm-6" align="right">
						<button type="submit" class="btn btn-info myfrmbtn width-100"  tabindex="{{$t++}}">Submit</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection