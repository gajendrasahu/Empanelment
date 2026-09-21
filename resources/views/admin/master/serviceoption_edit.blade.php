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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.updateserviceoptiontab')}}
				</a>
			</li>
		</ul>
		
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.options',$data->optionid)}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-4">
						<div class="col-sm-12">
							{{ __('labels.servicepic') }} <label id="req">&nbsp;</label>
							<input type="file" class="form-control" name="servicepic" id="servicepic" accept="image/*"/>
						</div>							
					
						<div class="col-sm-12">
							<div id="imagePreview1" style="text-align:center;">
							@if($data->displayimage=='')
								<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@else
								<img src="{{ asset('storage/'.$data->displayimage) }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
							@endif
							</div>					
						</div>
					</div>
					<div class="col-sm-8">
						<div class="col-sm-12">
							{{ __('labels.servicename') }} <label id="req">*</label>
							<input type="text" class="form-control sername1" name="servicename" id="servicename" value="{{old('servicename',$data->servicetitle)}}" placeholder="{{ __('labels.servicename') }}" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="250"/>
							
							<span class="text-danger">@error('servicename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>
						<div class="col-sm-12">
							{{ __('labels.shortdescription') }} <label id="req">&nbsp;</label>
							<input type="text" class="form-control" name="shortdescription" id="shortdescription" value="{{old('shortdescription',$data->description)}}" placeholder="{{ __('labels.shortdescription') }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="250"/>
							
							<span class="text-danger">@error('shortdescription') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-12">&nbsp;</div>
						<div class="col-sm-2">
							{{ __('common.mrp') }}<label id="req">*</label>
							<input type="text" class="form-control numbers" name="servicemrp" id="servicemrp" value="{{old('servicemrp',$data->mrp)}}" placeholder="{{ __('common.mrp') }}" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('servicemrp') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-3">
							{{ __('labels.requiredtime') }}<label id="req">(IN MIN)</label>
							<input type="text" class="form-control numbers" name="timerequired" id="timerequired" value="{{old('timerequired',$data->requiredtime)}}" placeholder="{{ __('labels.requiredtime') }}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

							<span class="text-danger">@error('timerequired') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
						</div>
						<div class="col-sm-2">
							<button type="submit" class="btn btn-info myfrmbtn">{{__('common.update')}}</button>
						</div>

					</div>
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
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>	
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
    tinymce.init({
        selector: '#description',
        promotion: false,
        branding: false,
        height: 350,
        autoresize_max_height: 490,
        toolbar_mode: 'floating',
        plugins: 'code advlist autolink lists charmap preview table searchreplace save',
        toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
        setup: function (editor) {
                // Add custom CSS to the editor content area
                editor.on('init', function () {
                    var contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) {
                        contentArea.style.overflow = 'auto';
                    }
                });
            }
    });
</script>

<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

jQuery(function($) {	
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
				$('#imagePreview1').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
			}
		  };
		};			
	});

	
	$('.remove').on('click', function() {		
		var alt = $(this).closest('div').attr('alt');
		$('#imagePreview'+alt).html('<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection