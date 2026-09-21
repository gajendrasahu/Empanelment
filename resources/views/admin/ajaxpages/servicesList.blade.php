@php
$i=1;
$y=1;
@endphp
<form name="copyservicedata" id="copyservicedata" action="{{route('service.copydata',$serviceid)}}" method="post" enctype="multipart/form-data">
<table class="mytable" border="1" style="padding:5px;">
	<tr class="mybgtd">
		<td style="padding:3px; font-size:12px;">CHOSSE THE SOURCE SERVICE FOR DATA COPY</td>
		<td style="padding:3px; font-size:12px;">SELECT THE CONTENT TYPE TO COPY</td>
		<td style="padding:3px; font-size:12px;" colspan="2">
			<b></b>
			<span><label class="myheadbg" style="float:right; padding:0px 3px;" onclick="Close()"><i class="fa fa-remove"></i></label></span>
		</td>
	</tr>
	<tr>
		<td style="vertical-align:top; padding:0px 15px 0px 0px;">
			<select class="select2" name="sid" id="sid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SERVICE NAME" data-width="300">
				<option value=""></option>
				@foreach ($services as $itm)
				<option value="{{ $itm->serviceid }}">{{ strtoupper($itm->servicetitle) }}</option>
				@endforeach
			</select>
		
		
		<button type="submit" class="btn btn-info myfrmbtn uploadbtn" style="width:130px; float:right;">COPY</button>			
		</td>
	</tr>
</table>
</form>
<script>
$(document).ready(function() {
    $('#copyservicedata').submit(function(e) {
        e.preventDefault();
		$(".uploadbtn").prop("disabled","disabled");
        // Check if the file input is empty
        var fileInput = $('#gallerypic')[0].files;
        if (fileInput.length === 0) {
            bootbox.alert('Please select a photo to upload.');
            return;
        }

        var formData = new FormData(this);

        $.ajax({
            url: $(this).attr('action'),
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			},            
			success: function(response) {
                if(response.success) {		
					$(".uploadmsg").css("display","");
                    setTimeout(function() { $(".uploadmsg").css("display","none"); $(".uploadbtn").prop("disabled",""); ViewPhotos('{{ route('service.viewphotos') }}',response.serviceid); },3000);
                } else {
                    bootbox.alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                bootbox.alert('An error occurred: ' + error);
            }
        });
    });
});	
</script>