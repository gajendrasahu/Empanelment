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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> ADD APPLICATION USERS
				</a>
			</li>
		@endif
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('appuser.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> APPLICATION USERS LIST</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.appuser',0)}}" method="post" enctype="multipart/form-data">
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
								<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							</div>
						</div>
					</div>
					<div class="col-sm-9">
						<div class="row">
							<div class="col-sm-3">
								NAME<label id="req">*</label>
								<input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" placeholder="NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

								<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								MOBILE NUMBER<label id="req">*</label>
								<input type="number" class="form-control numbers" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber')}}" placeholder="MOBILE NUMBER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								EMAIL<label id="req">*</label>
								<input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" placeholder="EMAIL" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								GENDER<label id="req">*</label>
								<select class="form-control" name="gender" id="gender" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
									<option value="">--GENDER--</option>
									<option value="M" @if(old('gender')=='M') {{ 'selected' }} @endif>MALE</option>
									<option value="F" @if(old('gender')=='F') {{ 'selected' }} @endif>FEMALE</option>
									<option value="O" @if(old('gender')=='O') {{ 'selected' }} @endif>OTHER</option>
								</select>

								<span class="text-danger">@error('gender') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								FATHER'S NAME<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="fathername" id="fathername" value="{{old('fathername')}}" placeholder="FATHER NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('fathername') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DATE OF BIRTH<label id="req">&nbsp;</label>
								<input type="date" class="form-control" name="dob" id="dob" value="{{old('dob')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('dob') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DEPARTMENT<label id="req">*</label>
								<select class="chosen-select form-control" name="departmentid" id="departmentid" data-placeholder="DEPARTMENT NAME" required tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)">
									<option value=""></option>
									@foreach ($department as $itm)
									<option value="{{ $itm->departmentid }}" {{ intval(old('departmentid'))===$itm->departmentid ? 'selected' : '' }}>{{ strtoupper($itm->departmentname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('departmentid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DESIGNATION<label id="req">*</label>
								<select class="chosen-select form-control" name="designationid" id="designationid" data-placeholder="DESIGNATION NAME" required tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)">
									<option value=""></option>
									@foreach ($designation as $itm)
									<option value="{{ $itm->designationid }}" {{ intval(old('designationid'))===$itm->designationid ? 'selected' : '' }}>{{ strtoupper($itm->designationname) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('designationidid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								MONTHLY BASIC SALARY<label id="req">*</label>
								<input type="text" class="form-control numbers" name="basicsalary" id="basicsalary" required value="{{old('basicsalary')}}" placeholder="BASIC SALARY" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('basicsalary') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								TRAVEL ALLOWANCE (TA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="ta" id="ta" value="{{old('ta')}}" placeholder="TA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ta') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								DEARNESS ALLOWANCE (DA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="da" id="da" value="{{old('da')}}" placeholder="DA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('da') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								HOUSE RENT ALLOWANCE<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="hra" id="hra" value="{{old('hra')}}" placeholder="HRA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('hra') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								MEDICAL ALLOWANCE (MA)<label id="req">&nbsp;</label>
								<input type="text" class="form-control numbers" name="ma" id="ma" value="{{old('ma')}}" placeholder="MA" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ma') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>						
							<div class="col-sm-9">
								ADDRESS<label id="req">*</label>
								<input type="text" class="form-control" name="address" id="address" value="{{old('address')}}" placeholder="ADDRESS" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('address') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								STATE<label id="req">&nbsp;</label>
								@if($addstate->ispermitted==1)
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddState('{{ route('store.statename') }}')"></i>
								</span>
								@endif
								<select class="chosen-select form-control" name="stateid" id="stateid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="STATE NAME" onchange="GetCities(this.value,'{{ route('cities.list') }}')" tabindex="{{$t++}}">
									<option value=""></option>
									@foreach ($state as $itm)
									<option value="{{ $itm->stateid }}" {{ intval(old('stateid'))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
									@endforeach
								</select>

								<span class="text-danger">@error('stateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							
							<div class="col-sm-3">
								CITY<label id="req">&nbsp;</label>
								@if(intval($addcity->ispermitted)==1)
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddCity('{{ route('store.cityname') }}')"></i>
								</span>
								@endif
								<select class="chosen-select form-control" name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CITY NAME" onchange="GetArea(this.value,'{{ route('area.list') }}')" tabindex="{{$t++}}">
									<option value=""></option>
								</select>

								<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								AREA<label id="req">&nbsp;</label>
								@if($addarea->ispermitted==1)
								<span style="float:right;" class="mytheame-background">
									<i class="fa fa-plus-circle" onclick="AddArea('{{ route('store.areaname') }}')"></i>
								</span>
								@endif
								<select class="chosen-select form-control" name="areaid" id="areaid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="AREA NAME" tabindex="{{$t++}}">
									<option value=""></option>
								</select>

								<span class="text-danger">@error('areaid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3">
								PIN CODE<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="pincode" id="pincode" value="{{old('pincode')}}" placeholder="PIN CODE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('pincode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="row">
							<div class="col-sm-3">
								CHECK IN TIME<label id="req">*</label>
								<input type="time" class="form-control" name="checkintime" id="checkintime" value="{{old('checkintime',date('H:i'))}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('checkintime') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								CHECK OUT TIME<label id="req">*</label>
								<input type="time" class="form-control" name="checkouttime" id="checkouttime" value="{{old('checkouttime',date('H:i'))}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('checkouttime') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-6">
								REMARK<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="remark" id="remark" value="{{old('remark')}}" placeholder="REMARK" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
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
									<option value="{{ $itm->bankid }}" @if(old('bankid')==$itm->bankid) {{ 'selected' }} @endif>{{ strtoupper($itm->bankname) }}</option>
									@endforeach
								</select>								
								<span class="text-danger">@error('bankid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								BANK ACCOUNT NUMBER<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="accountnumber" id="accountnumber" value="{{old('accountnumber')}}" placeholder="ACCOUNT NUMBER" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('accountnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								IFSC CODE<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="ifsccode" id="ifsccode" value="{{old('ifsccode')}}" placeholder="IFSC CODE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('ifsccode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								BRANCH NAME<label id="req">&nbsp;</label>
								<input type="text" class="form-control" name="branchname" id="branchname" value="{{old('branchname')}}" placeholder="BRANCH NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('branchname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
						</div>
						<div class="row"><div class="col-sm-12">&nbsp;</div></div>
						<div class="col-sm-12 myheadbg" style="padding:3px 10px;">ACCOUNT LOGIN DETAIL</div>
						<div class="row"><div class="col-sm-12" style="padding:5px;"></div></div>
						<div class="row">
							<div class="col-sm-3">
								LOGIN USER NAME<label id="req">*</label>
								<div class="loginfield-container">
								<input type="text" class="form-control" name="username" id="username" value="{{old('username')}}" placeholder="LOGIN USER NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="toggle-password">
									<i class="fa fa-lock"></i>
								</span>
								</div>
								<span class="text-danger">@error('username') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								LOGIN PASSWORD<label id="req">*</label>
								<div class="password-container">
								<input type="password" class="form-control" name="password" id="password" value="{{old('password')}}" placeholder="LOGIN PASSWORD" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="toggle-password" onclick="togglePasswordVisibility()">
									<i class="fa fa-eye"></i>
								</span>
								</div>
								<span class="text-danger">@error('password') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
							</div>
							<div class="col-sm-3">
								<button type="submit" class="btn btn-info myfrmbtn">ADD APPLICATION USER</button>
							</div>
						</div>
					</div>
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
				<table class="table table-striped" id="tablerecords">
				<thead>
				<tr>
					<td colspan="2">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('appuser.html') }}')">
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="depid" id="depid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="DEPARTMENT NAME" onchange="loadData(1,'{{ route('appuser.html') }}')">
							<option value=""></option>
							@foreach ($department as $itm)
							<option value="{{ $itm->departmentid }}">{{ strtoupper($itm->departmentname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="desid" id="desid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="DESIGNATION NAME" onchange="loadData(1,'{{ route('appuser.html') }}')">
							<option value=""></option>
							@foreach ($designation as $itm)
							<option value="{{ $itm->designationid }}">{{ strtoupper($itm->designationname) }}</option>
							@endforeach
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('appuser.html') }}')" tabindex="<?php echo $t++;?>" />
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

function loadData(page,r1)
{
	var departmentid	=	document.getElementById("depid").value;
	var designationid	=	document.getElementById("desid").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	var pagesize	=	document.getElementById("pagesize").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		departmentid:departmentid,
		designationid:designationid,
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
				$('#imagePreview').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
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
	}
	else
	{
		passwordInput.attr('type', 'password');
	}
}

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection