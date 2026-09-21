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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE APPLICATION USERS
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.appuser',$data->userid) }}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-3" style="text-align:left;">
						<div class="col-sm-12">
							PROFILE IMAGE <label id="req">(W=H, MAX : 500 KB)</label>
							<input type="file" class="form-control" name="profilepic" id="profilepic" accept="image/*" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('profilepic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">
							<div id="imagePreview" style="text-align:center; margin-top:20px;">
							@if($data->profilepic=='')
							<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@else
							<img src="{{ asset('storage/'.$data->profilepic) }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@endif
							</div>
						</div>
					</div>
					<div class="col-sm-9">
						<div class="row">
							<div class="col-sm-3">
								NAME<label id="req">*</label>
								<input type="text" class="form-control" name="name" id="name" value="{{old('name',$data->name)}}" placeholder="NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

								<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								MOBILE NUMBER<label id="req">*</label>
								<input type="number" class="form-control numbers" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber',$data->mobilenumber)}}" placeholder="MOBILE NUMBER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								EMAIL<label id="req">*</label>
								<input type="text" class="form-control" name="email" id="email" value="{{old('email',$data->email)}}" placeholder="EMAIL" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								GENDER<label id="req">*</label>
								<select class="form-control" name="gender" id="gender" required onKeyPress="return OnKeyPress(this, event)">
									<option value="">--GENDER--</option>
									<option value="M" @if(old('gender',$data->gender)=='M') {{ 'selected' }} @endif>MALE</option>
									<option value="F" @if(old('gender',$data->gender)=='F') {{ 'selected' }} @endif>FEMALE</option>
									<option value="O" @if(old('gender',$data->gender)=='O') {{ 'selected' }} @endif>OTHER</option>
								</select>

								<span class="text-danger">@error('gender') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								FATHER'S NAME<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="fathername" id="fathername" value="{{old('fathername',$data->fathername)}}" placeholder="FATHER NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('fathername') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DATE OF BIRTH<label id="req">&nbsp;</label>
								<input type="date" class="form-control" name="dob" id="dob" value="{{old('dob',$data->dob)}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('dob') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DEPARTMENT<label id="req">*</label>
								<select class="chosen-select form-control" name="departmentid" id="departmentid" data-placeholder="DEPARTMENT NAME" required tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)">
									<option value=""></option>
									@foreach ($department as $itm)
									<option value="{{ $itm->departmentid }}" {{ intval(old('departmentid',$data->departmentid))===$itm->departmentid ? 'selected' : '' }}>{{ strtoupper($itm->departmentname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('departmentid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DESIGNATION<label id="req">*</label>
								<select class="chosen-select form-control" name="designationid" id="designationid" data-placeholder="DESIGNATION NAME" required tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)">
									<option value=""></option>
									@foreach ($designation as $itm)
									<option value="{{ $itm->designationid }}" {{ intval(old('designationid',$data->designationid))===$itm->designationid ? 'selected' : '' }}>{{ strtoupper($itm->designationname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('designationidid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								MONTHLY BASIC SALARY<label id="req">*</label>
								<input type="text" class="form-control numbers" name="basicsalary" id="basicsalary" required value="{{old('basicsalary',$data->basicsalary)}}" placeholder="BASIC SALARY" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('basicsalary') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								TRAVEL ALLOWANCE (TA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="ta" id="ta" value="{{old('ta',$data->ta)}}" placeholder="TA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ta') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DEARNESS ALLOWANCE (DA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="da" id="da" value="{{old('da',$data->da)}}" placeholder="DA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('da') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								HOUSE RENT ALLOWANCE<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="hra" id="hra" value="{{old('hra',$data->hra)}}" placeholder="HRA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('hra') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>						
						<div class="row">
							<div class="col-sm-3">
								MEDICAL ALLOWANCE (MA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="ma" id="ma" value="{{old('ma',$data->ma)}}" placeholder="MA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ma') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>						
							<div class="col-sm-9">
								ADDRESS<label id="req">*</label>
								<input type="text" class="form-control" name="address" id="address" value="{{old('address',$data->address)}}" placeholder="ADDRESS" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('address') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								STATE<label id="req">&nbsp;</label>
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddState('{{ route('store.statename') }}')"></i>
								</span>
								<select class="chosen-select form-control" name="stateid" id="stateid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="STATE NAME" onchange="GetCities(this.value,'{{ route('cities.list') }}')">
									<option value=""></option>
									@foreach ($state as $itm)
									<option value="{{ $itm->stateid }}" {{ intval(old('stateid',$data->stateid))==$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('stateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							
							<div class="col-sm-3">
								CITY<label id="req">&nbsp;</label>
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddCity('{{ route('store.cityname') }}')"></i>
								</span>
								
								<select class="chosen-select form-control" name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CITY NAME" onchange="GetArea(this.value,'{{ route('area.list') }}')">
									<option value=""></option>
									@foreach ($city as $itm)
									<option value="{{ $itm->cityid }}" {{ intval(old('cityid',$data->cityid))==$itm->cityid ? 'selected' : '' }}>{{ strtoupper($itm->cityname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								AREA<label id="req">&nbsp;</label>
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddArea('{{ route('store.areaname') }}')"></i>
								</span>
								<select class="chosen-select form-control" name="areaid" id="areaid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="AREA NAME">
									<option value=""></option>
									@foreach ($area as $itm)
									<option value="{{ $itm->areaid }}" {{ intval(old('areaid',$data->areaid))==$itm->areaid ? 'selected' : '' }}>{{ strtoupper($itm->areaname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('areaid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								PIN CODE<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="pincode" id="pincode" value="{{old('pincode',$data->pincode)}}" placeholder="PIN CODE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('pincode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								CHECK IN TIME<label id="req">*</label>
								<input type="time" class="form-control" name="checkintime" id="checkintime" value="{{old('checkintime',date('H:i',strtotime($data->checkintime)))}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('checkintime') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								CHECK OUT TIME<label id="req">*</label>
								<input type="time" class="form-control" name="checkouttime" id="checkouttime" value="{{old('checkouttime',date('H:i',strtotime($data->checkouttime)))}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('checkouttime') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-6">
								REMARK<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="remark" id="remark" value="{{old('remark',$data->remark)}}" placeholder="REMARK" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('remark') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="col-sm-12 myheadbg" style="padding:3px 10px;">BANK ACCOUNT DETAIL</div>
						<div class="row"><div class="col-sm-12" style="padding:5px;"></div></div>
						<div class="row">
							<div class="col-sm-3">
								BANK NAME<label id="req">&nbsp;</label>
								<select class="form-control" name="bankid" id="bankid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="AREA NAME" tabindex="{{$t++}}">
									<option value="">--BANK NAME--</option>
									@foreach ($bank as $itm)
									<option value="{{ $itm->bankid }}" @if(old('bankid',$data->bankid)==$itm->bankid) {{ 'selected' }} @endif>{{ strtoupper($itm->bankname) }}</option>
									@endforeach
								</select>								
								<span class="text-danger">@error('bankid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								BANK ACCOUNT NUMBER<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="accountnumber" id="accountnumber" value="{{old('accountnumber',$data->accountnumber)}}" placeholder="ACCOUNT NUMBER" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('accountnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								IFSC CODE<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="ifsccode" id="ifsccode" value="{{old('ifsccode',$data->ifsccode)}}" placeholder="IFSC CODE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ifsccode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								BRANCH NAME<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="branchname" id="branchname" value="{{old('branchname',$data->branchname)}}" placeholder="BRANCH NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('branchname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="col-sm-12 myheadbg" style="padding:3px 10px;">ACCOUNT LOGIN DETAILS</div>
						<div class="row"><div class="col-sm-12" style="padding:5px;"></div></div>
						<div class="row">
							<div class="col-sm-3">
								LOGIN USER NAME<label id="req">*</label>
								<div class="loginfield-container">
								<input type="text" class="form-control" name="username" id="username" value="{{old('username',$data->username)}}" placeholder="LOGIN USER NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="toggle-password">
									<i class="fa fa-lock"></i>
								</span>
								</div>
								<span class="text-danger">@error('username') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								LOGIN PASSWORD<label id="req">*</label>
								<div class="password-container">
								<input type="password" class="form-control" name="password" id="password" value="{{old('password',$data->password)}}" placeholder="LOGIN PASSWORD" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="toggle-password" onclick="togglePasswordVisibility()">
									<i class="fa fa-eye"></i>
								</span>
								</div>
								<span class="text-danger">@error('password') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								<button type="submit" class="btn btn-info myfrmbtn">UPDATE USER</button>
							</div>
						</div>
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
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
			if (width!=height) 
			{
				bootbox.alert("IMAGE WIDTH AND HEIGHT MUST BE SAME");
				$('#profilepic').ace_file_input('reset_input');
				$('#profilepic').ace_file_input('reset_ui');
				$('#profilepic').ace_file_input('reset_input_field');
			}
			else
			{
				$('#imagePreview').html('<img src="'+image.src+'" alt="Selected Image" style="position:relative; width:100%;">');
			}
		  };
		};			
	});
	
	$('.remove').on('click', function() {
		$('#imagePreview').html('<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	
});

function togglePasswordVisibility() {
	var passwordInput = $('#password');
	var toggleIcon = $('.toggle-password img');

	if(passwordInput.attr('type') === 'password')
	{
		passwordInput.attr('type', 'text');
		toggleIcon.attr('src', 'eye-close.png');
	}
	else
	{
		passwordInput.attr('type', 'password');
		toggleIcon.attr('src', 'eye-open.png');
	}
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection