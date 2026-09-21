@php
$t=1;
@endphp
<tr class="myheadbg">
	<td class="padding-8 form-label" colspan="3">
		Required Documents (Uploaded)
	</td>
</tr>
@if(Session::has('uploaded'))
<tr class="uploaded">
	<td colspan="3">
		<div class="alert alert-block alert-success">
			<button type="button" class="close" data-dismiss="alert">
				<i class="ace-icon fa fa-times"></i>
			</button>
			{{ Session::pull('uploaded') }}
		</div>
	
	</td>
</tr>	
@endif

<tr>
	<td class="padding-8">
		<input type="text" name="authletter" required placeholder="" readonly autocomplete="off" class="full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="Authorization Letter*">
	</td>
	
	<td class="padding-8" style="width:250px;">
		<input type="file" class="form-control" required name="authorizationletter" id="authorizationletter" data-id="{{Crypt::encrypt($eoifiles->requestid)}}" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
	</td>
	<td class="padding-8 center" style="width:130px;">
		<a href="{{ asset('storage/'.$eoifiles->authorizationletter) }}" target="_blank">
		<button type="button" class="btn btn-info" style="width:100px;">
			<i class="fa fa-eye"></i> View File
		</button>

		</a>
	
	</td>
</tr>

<script>
jQuery(function($) {	

setTimeout(function() { 
	$(".uploaded").css("display","none"); 
	@if(Session::has('uploaded'))
	location.reload(); 
	@endif
},2000);

	$('#authorizationletter').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Update file (if required)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
		
});

	$('#authorizationletter').on('change', function (e) {
		const requestid = $(this).data('id');
		const authorizationletter = document.getElementById("authorizationletter").files[0];
		// Check if a file is selected
		if (authorizationletter)
		{
			// Show confirmation dialog
			if (confirm('Are you sure you want to upload this file?'))
			{
				let formData = new FormData();
				formData.append('_token', '{{ csrf_token() }}');
				formData.append('requestid', requestid);
				formData.append('authorizationletter', authorizationletter);
				$.ajax({
					url: '{{ route("update.pmeoifile") }}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if(response.status===200)
						{
							bootbox.alert(response.message);
							setTimeout(function() { 
								$(".eoifiles").html(response.formhtml);
								$(".no-skin").css("padding-right", "");
							},2000);					
						}
						else if(response.status===400)
						{
							bootbox.alert(response.message);
						}
						else
						{
							bootbox.alert('Something went wrong: ' + response.message);
						}
						$('#authorizationletter').ace_file_input('reset_input');
					},
					error: function(xhr) {
						$('#authorizationletter').ace_file_input('reset_input');
						if (xhr.responseJSON && xhr.responseJSON.errors) {
							var errors = xhr.responseJSON.errors;
							var allMessages = '';

							$.each(errors, function(field, messages) {
								$.each(messages, function(index, msg) {
									allMessages += msg + '<br>';
								});
							});
							bootbox.alert(allMessages);
						}			
						
					}
				});
				
			}
			else
			{
				// If not confirmed, reset the file input
				$('#authorizationletter').ace_file_input('reset_input');
			}
		}
		else
		{
			alert("Please select authorization letter");
			return false;
		}
	});

</script>