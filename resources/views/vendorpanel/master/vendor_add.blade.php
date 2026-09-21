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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.vendortab')}}
				</a>
			</li>
		@endif
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('vendor.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> {{__('labels.vendorlisttab')}}</a></li>
		@endif

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.vendor',0)}}" method="post" enctype="multipart/form-data">
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
								<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							</div>
						</div>
						
					</div>
					<div class="col-sm-9">
						<div class="col-sm-4">
							{{__('labels.name')}}<label id="req">*</label>
							<input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" placeholder="{{__('labels.name')}}" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.mobilenumber')}}<label id="req">*</label>
							<input type="text" class="form-control" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber')}}" placeholder="{{__('labels.mobilenumber')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.email')}} <label id="req">*</label>
							<input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" placeholder="{{__('labels.branchcontact')}}" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

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
								<option value="{{ $itm->stateid }}" {{ intval(old('stateid'))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
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
							@if(old('stateid')=='')
							<select class="chosen-select form-control" required name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="GetArea(this.value,'{{ route('area.list') }}')" tabindex="{{$t++}}">
								<option value=""></option>
							</select>
							@else
							@php
							$city		=	DB::table('city_tbl')->where('stateid','=',old('stateid'))->orderby('cityname')->get();
							@endphp
							<select class="chosen-select form-control" required name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="GetArea(this.value,'{{ route('area.list') }}')" tabindex="{{$t++}}">
								<option value=""></option>
								@foreach ($city as $itm)
								<option value="{{ $itm->cityid }}" {{ intval(old('cityid'))===$itm->cityid ? 'selected' : '' }}>{{ strtoupper($itm->cityname) }}</option>
								@endforeach
							</select>
							@endif
							<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.postalcode')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{old('postalcode')}}" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('postalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>
						<div class="col-sm-12">
							{{__('labels.completeaddress')}}<label id="req">*</label>
							<input type="text" class="form-control" name="completeaddress" id="completeaddress" value="{{old('completeaddress')}}" placeholder="{{__('labels.completeaddress')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('completeaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							{{__('labels.aadharnumber')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="aadharnumber" id="aadharnumber" value="{{old('aadharnumber')}}" placeholder="{{__('labels.aadharnumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('aadharnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.uploadaadhar')}} <label id="req">(MAX : 500 KB)</label>
							<input type="file" class="form-control" name="aadharfile" id="aadharfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('aadharfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.pannumber')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="pannumber" id="pannumber" value="{{old('pannumber')}}" placeholder="{{__('labels.pannumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('pannumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							{{__('labels.uploadpan')}} <label id="req">(MAX : 500 KB)</label>
							<input type="file" class="form-control" name="panfile" id="panfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							<span class="text-danger">@error('panfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.firmdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						{{__('labels.firmname')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmname" id="firmname" value="{{old('firmname')}}" placeholder="{{__('labels.firmname')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('firmname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.registrationnumber')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="registrationnumber" id="registrationnumber" value="{{old('registrationnumber')}}" placeholder="{{__('labels.registrationnumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('registrationnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.firmcontact')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmcontact" id="firmcontact" value="{{old('firmcontact')}}" placeholder="{{__('labels.firmcontact')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('firmcontact') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.email')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmemail" id="firmemail" value="{{old('firmemail')}}" placeholder="{{__('labels.email')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('firmemail') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						{{__('labels.statename')}}<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="firmstateid" id="firmstateid"  onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetFirmCities(this.value,'{{ route('cities.list') }}')">
							<option value=""></option>
							@foreach ($state as $itm)
							<option value="{{ $itm->stateid }}" {{ intval(old('firmstateid'))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('firmstateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.cityname')}}<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="firmcityid" id="firmcityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
						</select>

						<span class="text-danger">@error('firmcityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.postalcode')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmpostalcode" id="firmpostalcode" value="{{old('firmpostalcode')}}" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('firmpostalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						{{__('labels.completeaddress')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmaddress" id="firmaddress" value="{{old('firmaddress')}}" placeholder="{{__('labels.completeaddress')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('firmaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						{{__('labels.firmpan')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="firmpannumber" id="firmpannumber" value="{{old('firmpannumber')}}" placeholder="{{__('labels.firmpan')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('firmpannumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.firmpan')}} <label id="req">(MAX : 500 KB)</label>
						<input type="file" class="form-control" name="firmpanfile" id="firmpanfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('firmpanfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.gstnumber')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="gstnumber" id="gstnumber" value="{{old('gstnumber')}}" placeholder="{{__('labels.firmpan')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

						<span class="text-danger">@error('gstnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.gstfile')}} <label id="req">(MAX : 500 KB)</label>
						<input type="file" class="form-control" name="gstfile" id="gstfile" accept="image/*, application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('gstfile') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.bankdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.bankname')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="bankname" id="bankname" value="{{old('bankname')}}" placeholder="{{__('labels.bankname')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('bankname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.bankaccount')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="accountnumber" id="accountnumber" value="{{old('accountnumber')}}" placeholder="{{__('labels.bankaccount')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('accountnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.ifsccode')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="ifsccode" id="ifsccode" value="{{old('ifsccode')}}" placeholder="{{__('labels.ifsccode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('ifsccode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.bankbranch')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="bankbranch" id="bankbranch" value="{{old('bankbranch')}}" placeholder="{{__('labels.bankbranch')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('bankbranch') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.operationinformation')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.handlingbranch')}}<label id="req">*</label>
						<select class="chosen-select form-control" name="branchid" id="branchid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.handlingbranch')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($branches as $itm)
							<option value="{{ $itm->branchid }}" {{ intval(old('branchid'))===$itm->branchid ? 'selected' : '' }}>{{ strtoupper($itm->branchname) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('branchid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.operatingcategory')}}<label id="req">*</label>
						<input type="hidden" name="categoryids" id="categoryids" value="{{ old('categoryids')}}">
						<select class="form-control" name="categoryid[]" id="categoryid" multiple onchange="AddCategories()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="None" required>
							@foreach ($categories as $itm)
<option value="{{ $itm->categoryid }}" @if(is_array(old('categoryid')) && in_array($itm->categoryid, old('categoryid'))) selected @endif>{{ strtoupper($itm->category) }}</option>
							@endforeach
						
						</select>
						<span class="text-danger">@error('categoryids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.isselfemployeed')}}<label id="req">*</label>
						<select class="form-control" name="isselfemployeed" id="isselfemployeed" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="0" @if(old('isselfemployeed')=='0') {{ 'selected' }} @endif>NO</option>
							<option value="1" @if(old('isselfemployeed')=='1') {{ 'selected' }} @endif>YES</option>
						</select>

						<span class="text-danger">@error('isselfemployeed') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.logindetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.username')}}<label id="req">*</label>
						<div class="loginfield-container">
						<input type="text" class="form-control" name="loginid" id="loginid" value="{{old('loginid')}}" placeholder="{{__('labels.username')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="toggle-password">
							<i class="fa fa-lock"></i>
						</span>
						</div>
						<span class="text-danger">@error('loginid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.loginpassword')}}<label id="req">*</label>
						<div class="password-container">
						<input type="password" class="form-control" name="loginpassword" id="loginpassword" value="{{old('loginpassword')}}" placeholder="{{__('labels.loginpassword')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="toggle-password" onclick="togglePasswordVisibility()">
							<i class="fa fa-eye"></i>
						</span>
						</div>
						<span class="text-danger">@error('loginpassword') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>							
					</div>
					<div class="col-sm-3">
						{{__('labels.isactive')}}<label id="req">*</label>
						<select class="form-control" name="isactive" id="isactive" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="0" @if(old('isactive')=='0') {{ 'selected' }} @endif>NO</option>
							<option value="1" @if(old('isactive')=='1') {{ 'selected' }} @endif>YES</option>
						</select>

						<span class="text-danger">@error('isactive') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-8">
						VENDOR MAP LOCATION<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="locationaddress" id="locationaddress" value="{{old('locationaddress')}}" placeholder="MAP LOCATION" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('locationaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						LATITUDE<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="latitude" readonly id="latitude" value="{{old('latitude')}}" placeholder="0" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('latitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						LONGITUDE<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="longitude" readonly id="longitude" value="{{old('longitude')}}" placeholder="0" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('longitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						<div id="map" style="width:100%; height:400px; position:relative;"></div>
					</div>
					<div class="col-sm-10"></div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">{{__('common.submit')}}</button>
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
				<table class="table table-bordered table-striped" id="tablerecords">
				<thead>
				<tr>
					<td colspan="2">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('vendor.html') }}')">
							<option value="15">15</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="stid" id="stid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onchange="loadData(1,'{{ route('vendor.html') }}')" data-width="170">
							<option value=""></option>
							@foreach ($state as $itm)
							<option value="{{ $itm->stateid }}">{{ strtoupper($itm->statename) }}</option>
							@endforeach
						</select>
						<select class="select2" name="ctid" id="ctid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="loadData(1,'{{ route('vendor.html') }}')" data-width="170">
							<option value=""></option>
							@foreach ($city as $itm)
							<option value="{{ $itm->cityid }}">{{ strtoupper($itm->cityname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="brid" id="brid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.branchname')}}" onchange="loadData(1,'{{ route('vendor.html') }}')" data-width="170">
							<option value=""></option>
							@foreach ($branches as $itm)
							<option value="{{ $itm->branchid }}">{{ strtoupper($itm->branchname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="catid" id="catid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.categoryname')}}" onchange="loadData(1,'{{ route('vendor.html') }}')" data-width="170">
							<option value=""></option>
							@foreach ($categories as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->category) }}</option>
							@endforeach
						</select>
						<select class="select2" name="actstatus" id="actstatus" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.isactive')}}" onchange="loadData(1,'{{ route('vendor.html') }}')" data-width="170">
							<option value=""></option>
							<option value="0">IN ACTIVE</option>
							<option value="1">ACTIVE</option>
						</select>
						
					</div>
					</td>
				</tr>
				<tr>
					<td colspan="2">
					<div id="tablehead">
						
						<span class="input-icon" style="float:left;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('vendor.html') }}')" tabindex="<?php echo $t++;?>" />
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

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var stateid			=	document.getElementById("stid").value;
	var cityid			=	document.getElementById("ctid").value;
	var branchid		=	document.getElementById("brid").value;
	var categoryid		=	document.getElementById("catid").value;
	var isactive		=	document.getElementById("actstatus").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		stateid:stateid,
		cityid:cityid,
		branchid:branchid,
		categoryid:categoryid,
		isactive:isactive
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

$('#categoryid').multiselect({
	 enableCaseInsensitiveFiltering:1,
	 enableClickableOptGroups:1,
	 enableHTML:1,
	 maxHeight:200,		 
	 buttonClass: 'form-control',
	 buttonWidth: '100%',
	 buttonHeight: '100%',
});	

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
	

	$('#aadharfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD AADHAR',
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

	$('#firmpanfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD PAN CARD',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#gstfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'UPLOAD GST',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	
});
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDKLWkrg3sd51pWD3swPrFm-HLz4xOobQU&libraries=places&callback=initMap" async defer></script>
<script>
	var map;
	var marker;
	//initMap();
	function initMap() {
		var defaultLocation = { lat: 21.2513844, lng: 81.6296413 }; // Default to New York City
		map = new google.maps.Map(document.getElementById('map'), {
			center: defaultLocation,
			zoom: 12
		});

		marker = new google.maps.Marker({
			map: map,
			position: defaultLocation,
			draggable: true
		});

		var input = document.getElementById('locationaddress');
		var latInput = document.getElementById('latitude');
		var lngInput = document.getElementById('longitude');

		var autocomplete = new google.maps.places.Autocomplete(input);

		autocomplete.addListener('place_changed', function () {
			var place = autocomplete.getPlace();
			if (!place.geometry) {
				return;
			}

			var lat = place.geometry.location.lat();
			var lng = place.geometry.location.lng();

			latInput.value = lat;
			lngInput.value = lng;

			map.setCenter(place.geometry.location);
			marker.setPosition(place.geometry.location);
		});
		
		var geocoder = new google.maps.Geocoder();
		marker.addListener('dragend', function () {
			
			bootbox.confirm('ARE YOU SURE YOU WANT TO CHANGE THE LATITUDE AND LONGITUDE POSITION?',function(result){
				if(result)
				{
					var position = marker.getPosition();
					latInput.value = position.lat();
					lngInput.value = position.lng();
					/*
					geocoder.geocode({'location': position}, function(results, status) {
						if (status === 'OK' && results[0]) {
							// Set the address to the input field
							input.value = results[0].formatted_address;
						} else {
							alert('Geocoder failed due to: ' + status);
						}
					});					
					*/
				}
				else
				{
					var lat = parseFloat(latInput.value);
					var lng = parseFloat(lngInput.value);
					if(isNaN(lat) && isNaN(lng))
					{
						var lat = 21.2513844;
						var lng = 81.6296413;
						marker.setPosition({ lat: lat, lng: lng });
					}
					else
					{
						marker.setPosition({ lat: lat, lng: lng });
					}					
				}
			});
			/*
			var position = marker.getPosition();
			latInput.value = position.lat();
			lngInput.value = position.lng();
			*/
		});
	}
</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection