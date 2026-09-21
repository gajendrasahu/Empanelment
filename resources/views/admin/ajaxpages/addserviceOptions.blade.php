@php
$t=1;
@endphp
<div class="row" style="padding:0px 14px; font-size:14px;">
	<form name="options" id="options" action="{{route('store.options',$data->serviceid)}}" method="post" enctype="multipart/form-data">
		@csrf
		<div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.addserviceoption')}}</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">
			{{ __('labels.servicename') }} <label id="req">*</label>
			<span id="charCounter2">0/250</span>
			<input type="text" class="form-control sername1" name="servicename" id="servicename" value="{{old('servicename')}}" placeholder="{{ __('labels.servicename') }}" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="250"/>
			
			<span class="text-danger">@error('servicename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">
			{{ __('labels.shortdescription') }} <label id="req">&nbsp;</label>
			<input type="text" class="form-control" name="shortdescription" id="shortdescription" value="{{old('shortdescription')}}" placeholder="{{ __('labels.shortdescription') }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="250"/>
			
			<span class="text-danger">@error('shortdescription') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-2">
			{{ __('common.mrp') }}<label id="req">*</label>
			<input type="text" class="form-control numbers" name="servicemrp" id="servicemrp" value="{{old('servicemrp')}}" placeholder="{{ __('common.mrp') }}" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

			<span class="text-danger">@error('servicemrp') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
		</div>
		<div class="col-sm-3">
			{{ __('labels.requiredtime') }}<label id="req">(IN MIN)</label>
			<input type="text" class="form-control numbers" name="timerequired" id="timerequired" value="{{old('timerequired')}}" placeholder="{{ __('labels.requiredtime') }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

			<span class="text-danger">@error('timerequired') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
		</div>
		<div class="col-sm-3">
			{{ __('labels.servicepic') }} <label id="req">(Optional)</label>
			<input type="file" class="form-control" name="servicepic" id="servicepic" accept="image/*"/>
		</div>	
		<div class="col-sm-2">
			<button type="submit" class="btn btn-info myfrmbtn uploadbtn" style="width:100%;" tabindex="{{$t++}}">{{ __('common.submit') }}</button>
		</div>
		<div class="col-sm-2">
			<button type="button" onclick="Close()" class="btn btn-info myfrmbtn" style="width:100%; float:right; margin-left:5px;">{{__('common.close')}}</button>
		</div>
		<div class="col-sm-12"></div>
		<div class="col-sm-5"></div>
		<div class="col-sm-3">
			<div id="imagePreviewI" style="text-align:center; z-index:-9;">
				<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
			</div>
		</div>
		<div class="col-sm-2">
			<label class="uploadmsg" style="width:100%!important; text-align:center!important; display:none;">
				<i class="fa fa-thumbs-up text-success" style="font-size:60px;"></i><br>
				DATA STORED SUCCESSFULLY!
			</label>		
		</div>
		<div class="col-sm-12">&nbsp;</div>
		
	</form>
</div>
<div class="row" style="padding:0px 14px; font-size:14px;">
	<div class="col-xs-12" style="padding:0px 2px;">
		<div class="table-responsive">			
			<table class="table table-bordered table-striped" id="tablerecords">
			<thead>
			<tr class="myheadbg">
				<td class="myheadbg" colspan="6">OPTIONS LIST</td>
			</tr>
			<tr class="myheadbg">
				<td class="myheadbg center" style="width:30px;">{{__('labels.sno')}}</td>
				<td class="myheadbg">{{__('labels.servicename')}}</td>
				<td class="myheadbg" style="width:75px;">{{__('common.mrp')}}</td>
				<td class="myheadbg">{{__('labels.requiredtime')}} (In Minutes)</td>
				<td class="myheadbg" style="width:30px;"></td>
				<td class="myheadbg" style="width:30px;"></td>
			</tr>
			</thead>
			<tbody class="">
			@php $sr=0; @endphp
			@foreach($option as $opt)
			@php $sr=$sr+1; @endphp
			<tr id="items-{{ $sr }}">
				<td class="center">{{$sr}}</td>
				<td class="mytd" style="padding:0px;">{{$opt->servicetitle}}</td>
				<td class="mytd" style="padding:0px;">{{$opt->mrp}}</td>
				<td class="mytd" style="width:200px; padding:0px;">{{$opt->requiredtime}}</td>
				<td class="center" style="padding:0px;">
					<a href="{{ route('edit.serviceoption',Crypt::encrypt($opt->optionid)) }}" title="" class="myactionlink">
						<i class="fa fa-edit" style=""></i>
					</a>					
				</td>
				<td class="center" style="padding:0px;">
					<a title="" class="myactionlink" onclick="deleteItem1('options','{{ Crypt::encrypt($opt->optionid)}}',{{ $sr }},'{{__('messages.confirmation')}}','{{__('messages.deleted')}}')" title="">
							<i class="fa fa-trash" style=""></i>
					</a>
				</td>
			</tr>
			@endforeach
			</tbody>
			</table>
		</div>
	</div>
</div>
<script>

	$('#servicepic').ace_file_input({
		no_file:'No File ...',
		btn_choose:'{{__('labels.servicepic')}}',
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
				$('#imagePreviewI').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
			}
		  };
		};			
	});
	$('.remove').on('click', function() {		
		$('#imagePreviewI').html('<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	


$(document).ready(function() {
    $('#options').submit(function(e) {
        e.preventDefault();
		$(".uploadbtn").prop("disabled","disabled");
        var fileInput = $('#servicepic')[0].files;

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
                    setTimeout(function() {  $(".uploadmsg").css("display","none"); $(".uploadbtn").prop("disabled",""); Close(); },3000);
					setTimeout(function(){ AddOption('{{route('service.options')}}',response.serviceid); },5000);
                } else {
                    bootbox.alert('Error: ' + response);
                }
            },
            error: function(xhr, status, error) {
                bootbox.alert('An error occurred: ' + error);
            }
        });
    });
});	

</script>