@extends('admin.admin_master')
@section('admin')
@php
$t=0;
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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i>NEW CUSTOMER ENTRY FORM</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.newcustomer')}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12">
						<div class="alert alert-block alert-success">
						PLEASE ENTER CUSTOMER BASIC DETAILS.
						</div>
					</div>
					<div class="col-sm-3">
						Mobile Number <label id="req">*</label>
						<input type="text" class="form-control" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber',$mobilenumber)}}" placeholder="MOBILE NUMBER" autofocus readonly autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						First Name <label id="req">*</label>
						<input type="text" class="form-control firstname" name="firstname" id="firstname" value="{{old('firstname')}}" placeholder="FIRST NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('firstname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Middle Name <label id="req">&nbsp;</label>
						<input type="text" class="form-control middlename" name="middlename" id="middlename" value="{{old('middlename')}}" placeholder="MIDDLE NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('middlename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Last Name <label id="req">*</label>
						<input type="text" class="form-control lastname" required name="lastname" id="lastname" value="{{old('lastname')}}" placeholder="LAST NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('lastname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						Email <label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" placeholder="EMAIL" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Postal Code <label id="req">*</label>
						<input type="text" class="form-control" required name="postalcode" id="postalcode" value="{{old('postalcode')}}" placeholder="POSTAL CODE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('postalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-6">
						Address <label id="req">*</label>
						<input type="text" class="form-control" name="completeaddress" id="completeaddress" value="{{old('completeaddress')}}" placeholder="COMPLETE ADDRESS" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('completeaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						Latitude <label id="req">*</label>
						<input type="text" class="form-control" required name="latitude" id="latitude" value="{{old('latitude')}}" placeholder="Latitude" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('latitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						Longitude <label id="req">*</label>
						<input type="text" class="form-control" required name="longitude" id="longitude" value="{{old('longitude')}}" placeholder="Longitude" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						
						<span class="text-danger">@error('longitude') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						<br>
						<button type="submit" class="btn btn-info" style="width:100%;">SAVE RECORD</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
					<table style="width:100%; border-collapse:collapse; border:1px solid #ddd; background-color:white;" border="1">
						<tr class="myheadbg"><td class="mytdleftwhite" style="font-size:14px;">GET LATITUDE AND LONGITUDE BY SEARCHING ADDRESS OR LANDMARK</td></tr>
						<tr>
							<td>
								<input type="text" class="form-control" name="locationaddress" id="locationaddress" value="{{old('locationaddress')}}" placeholder="MAP LOCATION" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
							</td>
						</tr>
						<tr>
							<td colspan="5">
								<div id="map" style="width:100%; height:400px; position:relative;"></div>
							</td>
						</tr>
					</table>

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


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>



<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDKLWkrg3sd51pWD3swPrFm-HLz4xOobQU&libraries=places&callback=initMap" async defer></script>

<script>
	var map;
	var marker;
	//initMap();
	function initMap() {
		var input = document.getElementById('locationaddress');
		if (!input) {
			console.error('Input element for location address not found.');
			return;
		}
		
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



<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection