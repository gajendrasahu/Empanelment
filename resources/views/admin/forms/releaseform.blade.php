<form name="releaseform" id="releaseform" method="post" action="">
@csrf
<div class="table-responsive">
	<table class="table table-bordered padding-5" id="">
	<thead>
	<tr>
		<td>Profile Picture</td>
		<td>Resource Detail</td>
		<td>Release Detail</td>
	</tr>
	</thead>
	<body>
	<tr>
		<td class="center" style="width:20%; vertical-align:top!important;">
			@if($data->resource_pic)
			<img src="{{ asset('panel/assets/images/basic/defaut.jpg')}}" style="width:100%;">
			@else
			<img src="{{ asset('panel/assets/images/basic/defaut.jpg')}}" style="width:100%;">
			@endif
		</td>
		<td class="v-top" style="width:40%; vertical-align:top!important;">
			<table class="pd-10">
				<tr>
					<td>Employee Code</td>
					<td>: <b>{{$data->employee_code}}</b></td>
				</tr>
				<tr>
					<td>Name</td>
					<td>: <b>{{$data->name}}</b></td>
				</tr>
				<tr>
					<td>Mobile Number</td>
					<td>: <b>{{$data->mobilenumber}}</b></td>
				</tr>
				<tr>
					<td>Email</td>
					<td>: <b>{{$data->email}}</b></td>
				</tr>
				@if($data->role)
				<tr>
					<td>Role</td>
					<td>: <b>{{$data->role}}</b></td>
				</tr>
				@endif
				@if($data->sectorname)
				<tr>
					<td>Sector</td>
					<td>: <b>{{$data->sectorname}}</b></td>
				</tr>
				@endif
				@if($data->consultantposition)
				<tr>
					<td>Position</td>
					<td>: <b>{{$data->consultantposition}}</b></td>
				</tr>
				@endif
				@if($data->remuneration!=0)
				<tr>
					<td>Remuneration</td>
					<td>: <b><span class="format-indian" data-value="{{ number_format($data->remuneration,'2','.','') }}"></span> INR</b></td>
				</tr>
				@endif
				<tr>
					<td>Deployed Date</td>
					<td>: <b>{{date('d\-m\-Y',strtotime($data->deployed_date))}}</b></td>
				</tr>
			</table>
			 
		</td>
		<td class="v-top" style="width:40%; vertical-align:top!important;">
			<table class="pd-10 width-full">
				<tr>
					<td class="v-top">Attach File (if any)</td>
					<td>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<input type="hidden" name="user_id" id="user_id" value="{{Crypt::encrypt($data->userid)}}">
						<input type="file" class="attachment width-full" name="release_file" id="release_file" required>
					</td>
				</tr>
				<tr>
					<td class="v-top">Write some note (Optional)</td>
					<td class="v-top">
						<textarea name="release_remark" id="release_remark" rows="5" placeholder="Write some note" class="width-full"></textarea>
					</td>
				</tr>
				<tr>
					<td class="v-top">Last Working Day*</td>
					<td class="v-top">
						<input type="text" class="today_before" required placeholder="dd-mm-YYYY" name="lastdate" id="lastdate" required style="width:100%;">
					</td>
				</tr>
				<tr>
					<td class="v-top" colspan="2" style="text-align:right;">
						@permission('store.release')
						<button type="button" class="btn btn-info myfrmbtn releaseBtn width-100">Submit</button>
						@endpermission
					</td>
				</tr>
			</table>
		</td>
	</tr>
	</body>
	</table>

</form>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

jQuery(function($) {	

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'File',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

});


flatpickr(".today_before", {
    dateFormat: "d-m-Y",
    //minDate: "today",
    defaultDate: null,    // Keep blank by default
    allowInput: false
});

$('.releaseBtn').on('click', function(e) {
	e.preventDefault();
	var form 		=	$('#releaseform')[0];
	var formData 	= 	new FormData(form);
	$.ajax({
		url: '{{route("store.release")}}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status===200)
			{
				setTimeout(function() { 
					$('.datalist').trigger('click'); 
					$('#releaseData').html("");
					$('#releaseModal').modal('hide');
				},2000);
				bootbox.alert(response.message);
				return false;
			}
			else
			{
				bootbox.alert('<span style="color:red;">'+response.message+'</span>');
				return false;
			}
		},
		error: function(xhr) {
			let message = '';

			if (xhr.responseJSON)
			{
				if (xhr.responseJSON.errors)
				{
					$.each(xhr.responseJSON.errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> ' + val + '<br>';
					});
				}
				else if (xhr.responseJSON.message)
				{
					message = '<span style="color:red;">' + xhr.responseJSON.message + '</span>';
				}
			}
			else
			{
				message = 'Something went wrong. Please try again.';
			}

			bootbox.alert(message);
		}
	});
});

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
