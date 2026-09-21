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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.customerupdatetab')}}
				</a>
			</li>
		@endif

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.customer',$data->customerid)}}" method="post" enctype="multipart/form-data">
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
							{{__('labels.name')}}<label id="req">*</label>
							<input type="text" class="form-control" name="name" id="name" value="{{old('name',$data->name)}}" placeholder="{{__('labels.name')}}" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.mobilenumber')}}<label id="req">&nbsp; (Login Id) *</label>
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
							{{__('labels.statename')}}<label id="req">&nbsp;</label>
							@if($addstate->ispermitted==1)
							<span style="float:right;" class="mytheame-background">
								<i class="fa fa-plus-circle" onclick="AddState('{{ route('store.statename') }}')"></i>
							</span>
							@endif
							<select class="chosen-select form-control" name="stateid" id="stateid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetCities(this.value,'{{ route('cities.list') }}')">
								<option value=""></option>
								@foreach ($state as $itm)
								<option value="{{ $itm->stateid }}" {{ intval(old('stateid',$data->stateid))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
								@endforeach
							</select>

							<span class="text-danger">@error('stateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.cityname')}}<label id="req">&nbsp;</label>
							@if($addcity->ispermitted==1)
							<span style="float:right;" class="mytheame-background">
								<i class="fa fa-plus-circle" onclick="AddCity('{{ route('store.cityname') }}')"></i>
							</span>
							@endif
							@if(old('stateid',$data->stateid)=='')
							<select class="chosen-select form-control" name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="GetArea(this.value,'{{ route('areas.list') }}')" tabindex="{{$t++}}">
								<option value=""></option>
							</select>
							@else
							@php
							$city		=	DB::table('city_tbl')->where('stateid','=',old('stateid',$data->stateid))->orderby('cityname')->get();
							@endphp
							<select class="chosen-select form-control" name="cityid" id="cityid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="GetArea(this.value,'{{ route('areas.list') }}')" tabindex="{{$t++}}">
								<option value=""></option>
								@foreach ($city as $itm)
								<option value="{{ $itm->cityid }}" {{ intval(old('cityid',$data->cityid))===$itm->cityid ? 'selected' : '' }}>{{ strtoupper($itm->cityname) }}</option>
								@endforeach
							</select>
							@endif
							<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-4">
							{{__('labels.areaname')}}<label id="req">&nbsp;</label>
							@if($addarea->ispermitted==1)
							<span style="float:right;" class="mytheame-background">
								<i class="fa fa-plus-circle" onclick="AddArea('{{ route('store.areaname') }}')"></i>
							</span>
							@endif
							@if(old('cityid',$data->cityid)=='')
							<select class="chosen-select form-control" name="areaid" id="areaid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.areaname')}}" tabindex="{{$t++}}">
								<option value=""></option>
							</select>
							@else
							@php
							$area		=	DB::table('area_tbl')->where('cityid','=',old('cityid',$data->cityid))->orderby('areaname')->get();
							@endphp
							<select class="chosen-select form-control" name="areaid" id="areaid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.areaname')}}" tabindex="{{$t++}}">
								<option value=""></option>
								@foreach ($area as $itm)
								<option value="{{ $itm->areaid }}" {{ intval(old('areaid',$data->areaid))===$itm->areaid ? 'selected' : '' }}>{{ strtoupper($itm->areaname) }}</option>
								@endforeach
							</select>
							@endif
							<span class="text-danger">@error('areaid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>						
						<div class="col-sm-4">
							{{__('labels.postalcode')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{old('postalcode',$data->postalcode)}}" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

							<span class="text-danger">@error('postalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-8">
							{{__('labels.completeaddress')}}<label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="completeaddress" id="completeaddress" value="{{old('completeaddress',$data->completeaddress)}}" placeholder="{{__('labels.completeaddress')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('completeaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>	
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">LOGIN PASSWORD DETAIL</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-4">
						{{__('labels.loginpassword')}}<label id="req">*</label>
						<div class="password-container">
						<input type="password" class="form-control" name="loginpassword" id="loginpassword" value="{{old('loginpassword',$data->loginpassword)}}" placeholder="{{__('labels.loginpassword')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="toggle-password" onclick="togglePasswordVisibility()">
							<i class="fa fa-eye"></i>
						</span>
						</div>
						<span class="text-danger">@error('loginpassword') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>							
					</div>
						
					</div>
		
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-8">
						MAP ADDRESS LOCATION<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="locationaddress" id="locationaddress" value="{{old('locationaddress',$data->locationaddress)}}" placeholder="MAP LOCATION" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('locationaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						LATITUDE<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="latitude" readonly id="latitude" value="{{old('latitude',$data->latitude)}}" placeholder="0" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('latitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						LONGITUDE<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="longitude" readonly id="longitude" value="{{old('longitude',$data->longitude)}}" placeholder="0" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('longitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						<div id="map" style="width:100%; height:400px; position:relative;"></div>
					</div>
					<div class="col-sm-10"></div>
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
</script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDKLWkrg3sd51pWD3swPrFm-HLz4xOobQU&libraries=places&callback=initMap" async defer></script>
<script>
	var map;
	var marker;
	initMap();
	function initMap() {
		@if($data->latitude!='')
		var defaultLocation = { lat: {{$data->latitude}}, lng: {{$data->longitude}} }; // Default to New York City
		@else
		var defaultLocation = { lat: 21.2513844, lng: 81.6296413 }; // Default to New York City
		@endif
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
@endsection