@php
$i=1;
$y=1;
@endphp
<form name="photos" id="photos" action="{{route('service.photos',$service->serviceid)}}" method="post" enctype="multipart/form-data">
<table class="mytable" border="1">
	<tr class="mybgtd">
		<td style="padding:3px; font-size:15px;" colspan="2">
			<b>{{$service->servicetitle}}</b>
			<span><label class="myheadbg" style="float:right; padding:0px 3px;" onclick="Close()"><i class="fa fa-remove"></i></label></span>
		</td>
	</tr>
	<tr>
		<td style="vertical-align:top; padding:0px 15px 0px 0px;">
			<input type="file" class="form-control" name="gallerypic" id="gallerypic" required accept="image/*"/>
			<span class="text-danger">@error('gallerypic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>			
			<button type="button" onclick="Close()" class="btn btn-info myfrmbtn uploadbtn" style="width:100px; float:right; margin-left:5px;">{{__('common.close')}}</button>
			<button type="submit" class="btn btn-info myfrmbtn uploadbtn" style="width:130px; float:right;">{{__('common.addimage')}}</button>			
			<br><br><br>

			<label class="uploadmsg" style="width:100%!important; text-align:center!important; display:none;">
				<i class="fa fa-thumbs-up text-success" style="font-size:60px;"></i><br>
				{{__('labels.uploadsuccess')}}
			</label>
		</td>
		<td style="width:250px;">
		<div id="imagePreviewG" style="text-align:center; z-index:-9;">
			<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
		</div>
		
		</td>
	</tr>
</table>
</form>
<table class="mytable" border="1">
	<tr class="mybgtd"><td style="padding:3px; font-size:15px;" colspan="2"><b>{{__('labels.existingphotos')}}</b></td></tr>
	<tr>
		<td colspan="2" style="padding:3px;" nowrap>
			<div style="width: 100%; max-height: 300px; overflow-y: scroll; display: flex; flex-wrap: wrap; padding-right: 10px;">
				@foreach($data as $img)
				<div id="item-{{ $y }}" style="margin-right: 5px; margin-bottom: 5px; border: 1px solid #ddd; text-align:center;">
					@php $y=$y+1; @endphp
					<a href="{{ asset('storage/'.$img->servicepic) }}" target="_blank">
						<img src="{{ asset('storage/'.$img->servicepic) }}" style="width: 200px; height: auto;">
					</a>
					<br>
					<i class="fa fa-remove" style="font-size:14px;" onclick="deleteItem('servicephotos','{{ Crypt::encrypt($img->recordid)}}',{{ $y }},'{{__('messages.confirmation')}}','{{__('messages.deleted')}}')"></i>
				</div>
				@php $y=$y+1; @endphp
				@endforeach
			</div>		
		</td>
	</tr>
</table>
<script>
	$('#gallerypic').ace_file_input({
		no_file:'No File ...',
		btn_choose:'{{__('labels.gallerypic')}}',
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
			if (width!='') 
			{
				$('#imagePreviewG').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
			}
		  };
		};			
	});
	$('.remove').on('click', function() {		
		$('#imagePreviewG').html('<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	

$(document).ready(function() {
    $('#photos').submit(function(e) {
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