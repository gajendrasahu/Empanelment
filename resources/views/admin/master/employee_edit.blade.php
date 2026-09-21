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
		@if(in_array(2,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.employeeupdatetab')}}
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.employee',$data->vendorid)}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.basicdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						<div class="col-sm-12">
							PROFILE IMAGE <label id="req">(W=H, MAX : 500 KB)</label>
							<input type="file" class="form-control" name="profilepic" id="profilepic" accept="image/*" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('profilepic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">
							<div id="imagePreview" style="text-align:center; margin-top:20px;">
							@if($data->profilepic=='')
								<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@else
								<img src="{{ asset('storage/'.$data->profilepic) }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@endif
							</div>
						</div>
						
					</div>
					<div class="col-sm-9">
						<div class="col-sm-4">
							{{__('labels.vendorname')}}<label id="req">*</label>
							<select class="chosen-select form-control" name="vendorid" id="vendorid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.vendorname')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetCities(this.value,'{{ route('cities.list') }}')">
								<option value=""></option>
								@foreach ($vendors as $itm)
								<option value="{{ $itm->vendorid }}" {{ intval(old('vendorid',$data->parentvendorid))===$itm->vendorid ? 'selected' : '' }}>{{ strtoupper($itm->name) }} {{ strtoupper($itm->middlename) }} {{ strtoupper($itm->lastname) }} [{{ $itm->mobilenumber }}]</option>@endforeach
							</select>

							<span class="text-danger">@error('vendorid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.employeename')}}<label id="req">*</label>
							<input type="text" class="form-control firstname" name="name" id="name" value="{{old('name',$data->name)}}" placeholder="{{__('labels.employeename')}}" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							MIDDLE NAME<label id="req">&nbsp;</label>
							<input type="text" class="form-control middlename" name="middlename" id="middlename" value="{{old('middlename',$data->middlename)}}" placeholder="MIDDLE NAME" autofocus autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('middlename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							LAST NAME<label id="req">*</label>
							<input type="text" class="form-control lastname" name="lastname" id="lastname" value="{{old('lastname',$data->lastname)}}" placeholder="LAST NAME" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('lastname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.mobilenumber')}}<label id="req">*</label>
							<input type="text" class="form-control" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber',$data->mobilenumber)}}" placeholder="{{__('labels.mobilenumber')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.email')}} <label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="email" id="email" value="{{old('email',$data->email)}}" placeholder="{{__('labels.email')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							{{__('labels.statename')}}<label id="req">*</label>
							@if($addstate->ispermitted==1)
							<span style="float:right;" class="mytheame-background">
								<i class="fa fa-plus-circle" onclick="AddState('{{ route('store.statename') }}')"></i>
							</span>
							@endif
							<select class="chosen-select form-control" name="stateid" id="stateid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetCities(this.value,'{{ route('cities.list') }}')">
								<option value=""></option>
								@foreach ($state as $itm)
								<option value="{{ $itm->stateid }}" {{ intval(old('stateid',$data->stateid))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
								@endforeach
							</select>

							<span class="text-danger">@error('stateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.cityname')}}<label id="req">*</label>
							@if($addcity->ispermitted==1)
							<span style="float:right;" class="mytheame-background">
								<i class="fa fa-plus-circle" onclick="AddCity('{{ route('store.cityname') }}')"></i>
							</span>
							@endif
							@if(old('stateid',$data->stateid)=='')
							<select class="chosen-select form-control" required name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" tabindex="{{$t++}}">
								<option value=""></option>
							</select>
							@else
							@php
							$city		=	DB::table('city_tbl')->where('stateid','=',old('stateid',$data->stateid))->orderby('cityname')->get();
							@endphp
							<select class="chosen-select form-control" required name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" tabindex="{{$t++}}">
								<option value=""></option>
								@foreach ($city as $itm)
								<option value="{{ $itm->cityid }}" {{ intval(old('cityid',$data->cityid))===$itm->cityid ? 'selected' : '' }}>{{ strtoupper($itm->cityname) }}</option>
								@endforeach
							</select>
							@endif
							<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.postalcode')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{old('postalcode',$data->postalcode)}}" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('postalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>
						<div class="col-sm-12">
							{{__('labels.completeaddress')}}<label id="req">*</label>
							<input type="text" class="form-control" name="completeaddress" id="completeaddress" value="{{old('completeaddress',$data->completeaddress)}}" placeholder="{{__('labels.completeaddress')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('completeaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							{{__('labels.aadharnumber')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="aadhaarnumber" id="aadhaarnumber" value="{{old('aadhaarnumber',$data->aadhaarnumber)}}" placeholder="{{__('labels.aadharnumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('aadhaarnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.pannumber')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="pannumber" id="pannumber" value="{{old('pannumber',$data->pannumber)}}" placeholder="{{__('labels.pannumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('pannumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							DRIVING LICENCE<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="drivinglicence" id="drivinglicence" value="{{old('drivinglicence',$data->drivinglicence)}}" placeholder="DRIVING LICENCE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('drivinglicence') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-2">
							FRONT <label id="req">(500 KB)</label>
							<input type="file" class="form-control" name="aadhaarfrontfile" id="aadhaarfrontfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('aadhaarfrontfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-2">
							BACK <label id="req">(500 KB)</label>
							<input type="file" class="form-control" name="aadhaarbackfile" id="aadhaarbackfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('aadhaarbackfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.uploadpan')}} <label id="req">(MAX : 500 KB)</label>
							<input type="file" class="form-control" name="panfile" id="panfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('panfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							LICENCE FILE <label id="req">(MAX : 500 KB)</label>
							<input type="file" class="form-control" name="licencefile" id="licencefile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('licencefile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">NOMINEE DETAIL</div></div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						NOMINEE NAME<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="nomineename" id="nomineename" value="{{old('nomineename',$data->nomineename)}}" placeholder="NOMINEE NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('nomineename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						RELATION<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="relation" id="relation" value="{{old('relation',$data->relation)}}" placeholder="RELATION" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('relation') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						NOMINEE DOB<label id="req">&nbsp;</label>
						<input type="date" class="form-control" name="nomineedob" id="nomineedob" value="{{old('nomineedob',$data->nomineedob)}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('nomineedob') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						AADHAAR / PAN <label id="req">(MAX : 500 KB)</label>
						<input type="file" class="form-control" name="documentfile" id="documentfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('documentfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">ACCOUNT STATUS</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.operatingcategory')}}<label id="req">*</label>
						<input type="hidden" name="categoryids" id="categoryids" value="{{ old('categoryids',$data->categoryids)}}">
						<select class="form-control" name="categoryid[]" id="categoryid" multiple onchange="AddCategories()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="None" required>
						
						@if(old('vendorid',$data->parentvendorid)!='')
							@php
								$oldItems 	= 	explode(',',old('categoryids',$data->employeecatids));
								$catid		=	DB::table('vendor_tbl')->where('vendorid',old('vendorid',$data->parentvendorid))->value('categoryids');
								$catids		=	explode(',',$catid);
								$categories	=	DB::table('category_tbl')->whereIn('categoryid',$catids)->get();
							@endphp
							@foreach($categories as $cat)
							<option value="{{$cat->categoryid}}" @if(in_array($cat->categoryid,$oldItems)) {{ 'selected' }} @endif>{{$cat->category}}</option>
							@endforeach
						@endif
						</select>
						<span class="text-danger">@error('categoryids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						VERIFICATION STATUS<label id="req">*</label>
						<select class="form-control" name="verificationstatus" id="verificationstatus" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="1" @if(old('verificationstatus',$data->verificationstatus)=='1') {{ 'selected' }} @endif>VERIFIED</option>

							<option value="0" @if(old('verificationstatus',$data->verificationstatus)=='0') {{ 'selected' }} @endif>UN VERIFIED</option>
						</select>

						<span class="text-danger">@error('verificationstatus') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.isactive')}}<label id="req">*</label>
						<select class="form-control" name="isactive" id="isactive" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="0" @if(old('isactive',$data->isactive)=='0') {{ 'selected' }} @endif>NO</option>
							<option value="1" @if(old('isactive',$data->isactive)=='1') {{ 'selected' }} @endif>YES</option>
						</select>

						<span class="text-danger">@error('isactive') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">{{__('common.update')}}</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>
<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif
function togglePasswordVisibility() {
	var passwordInput = $('#loginpassword');
	var toggleIcon = $('.toggle-password img');
	if(passwordInput.attr('type') === 'password')
	{
		passwordInput.attr('type', 'text');
	}
	else
	{
		passwordInput.attr('type', 'password');
	}
}

jQuery(function($) {	
	$('#profilepic').ace_file_input({
		no_file:'No File ...',
		btn_choose:'PROFILE IMAGE',
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
			$('#imagePreview').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
		  };
		};			
	});
	
	$('.remove').on('click', function() {
		$('#imagePreview').html('<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	
	

	$('#aadhaarfrontfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'AADHAR',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#aadhaarbackfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'AADHAR',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#panfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD PAN CARD',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#bankfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD FILE',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#licencefile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD FILE',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#documentfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD FILE',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});

$('#categoryid').multiselect({
	enableCaseInsensitiveFiltering: true,
	enableClickableOptGroups: true,
	enableHTML: true,
	maxHeight: 200,		 
	buttonClass: 'form-control',
	buttonWidth: '100%',
	buttonHeight: '100%',
	nonSelectedText: 'Select Category'
});

</script>
<script>
$(document).ready(function($) {

	window.getCategory = function(vendorid) {
		$.ajax({
			url: '{{ route("getvendorcategory") }}',
			type: 'GET',
			data: {
				_token: '{{ csrf_token() }}',
				vendorid: vendorid,
			},
			success: function(response) {
				var $dropdown = $('#categoryid');
				$dropdown.empty();

				if (response.status === 200) {
					var categoryIds = response.categoryIds;

					if (categoryIds.length > 0) {
						categoryIds.forEach(function(catid) {
							if (catid.categorystatus == 1) {
								$dropdown.append('<option value="' + catid.categoryid + '">' + catid.category + '</option>');
							}
						});
						
						$dropdown.multiselect('rebuild');
					}
				} else if (response.status === 400) {
					alert("ERROR");
				}
			},
			error: function(xhr, status, error) {
				console.error("AJAX Error:", status, error);
			}
		});
	};
});
</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection