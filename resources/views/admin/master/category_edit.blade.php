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
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.updatecategory')}}
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.category',$data->categoryid)}}" method="post" enctype="multipart/form-data">
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
						{{__('labels.parentcategory')}}<label id="req">&nbsp;</label>
						<select class="chosen-select form-control" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.parentcategory')}}">
							<option value=""></option>
							@foreach ($category as $itm)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',$itm->categoryid))===$data->parentcategoryid ? 'selected' : '' }}>{{ strtoupper($itm->displayname) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.categoryname')}}<label id="req">*</label>
						<input type="text" class="form-control" name="category" id="category" value="{{old('category',$data->category)}}" placeholder="{{__('labels.categoryname')}}" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('category') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-7">
						{{__('labels.headingvalue')}}<label id="req">&nbsp;</label>
						<span id="charCounter1">0/300</span>
						<input type="text" class="form-control headvalue" name="headingvalue" id="headingvalue" value="{{old('headingvalue')}}" placeholder="{{__('labels.headingvalue')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" maxlength="300"/>
						
						<span class="text-danger">@error('headingvalue') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						{{__('labels.displayorder')}}<label id="req">*</label>
						<input type="number" class="form-control numbers" name="displayorder" id="displayorder" value="{{old('displayorder',$data->displayorder)}}" placeholder="DISPLAY ORDER" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('displayorder') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						{{__('labels.categorystatus')}}<label id="req">*</label>
						<select class="form-control" name="categorystatus" id="categorystatus" onKeyPress="return OnKeyPress(this, event)" required tabindex="{{$t++}}">
							<option value="1" {{ old('categorystatus',$data->categorystatus)=='1' ? 'selected' : '' }}>ACTIVE</option>
							<option value="2" {{ old('categorystatus',$data->categorystatus)=='2' ? 'selected' : '' }}>IN ACTIVE</option>							
						</select>
						<span class="text-danger">@error('categorystatus') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.metakeywords')}} (',' separated)<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="metakeywords" id="metakeywords" value="{{old('metakeywords',$data->metakeywords)}}" placeholder="META KEY WORDS" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('metakeywords') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-6">
						{{__('labels.metadescription')}} (',' separated)<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="metadescription" id="metadescription" value="{{old('metadescription',$data->metadescription)}}" placeholder="META DESCRIPTION" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('metadescription') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12">
						{{__('labels.description')}}<label id="req">&nbsp;</label>
						<textarea class="form-control" name="description" id="description" placeholder="{{__('labels.description')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{{old('description',$data->description)}}</textarea>

						<span class="text-danger">@error('description') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-4" alt="1">
						{{__('labels.categoryicon')}} <label id="req">(W=H, MAX : 500 KB)</label>
						<input type="file" class="form-control" name="categoryicon" id="categoryicon" accept="image/*" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('categoryicon') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-6" alt="2">
						{{__('labels.categorypage')}} <label id="req">(MAX : 500 KB)</label>
						<input type="file" class="form-control" name="categorypage" id="categorypage" accept="image/*" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
						<span class="text-danger">@error('categorypage') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn">{{__('common.update')}}</button>
					</div>
					<div class="col-sm-12"></div>
					<div class="col-sm-3">
						<div id="imagePreview1" style="text-align:center;">
						@if($data->categoryicon=='')
							<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
						@else
							<img src="{{ asset('storage/'.$data->categoryicon) }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
						@endif
						</div>					
					</div>
					<div class="col-sm-7">
						<div id="imagePreview2" style="text-align:center;">
						@if($data->categorypage=='')
							<img src="{{ asset('storage/uploads/images/defaultpage.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
						@else
							<img src="{{ asset('storage/'.$data->categorypage) }}" alt="Uploaded Image" style="width:100%; position:relative!important;">
						@endif
						</div>					
					</div>

					
				</div>		
			</form>
		</div>
	</div>
</div>

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
jQuery(function($) {	
	$('#categoryicon').ace_file_input({
		no_file:'No File ...',
		btn_choose:'{{__('labels.categoryicon')}}',
		btn_change:'Change',
		btn_name:'btnname',
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
				$('#categoryicon').ace_file_input('reset_input');
				$('#categoryicon').ace_file_input('reset_ui');
				$('#categoryicon').ace_file_input('reset_input_field');
			}
			else
			{
				$('#imagePreview1').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
			}
		  };
		};			
	});

	$('#categorypage').ace_file_input({
		no_file:'No File ...',
		btn_choose:'{{__('labels.categorypage')}}',
		btn_change:'Change',
		btn_name:'btnname',
		thumbnail:false //| true | large
	}).on('change', function() {
		var reader = new FileReader();
		reader.readAsDataURL(this.files[0]);
		reader.onload = function (e) {
		  var image = new Image();
		  image.src = e.target.result;
		  image.onload = function () {
			$('#imagePreview2').html('<img src="' + image.src + '" alt="Selected Image" style="position:relative; width:100%;">');
		  };
		};			
	});
	
	$('.remove').on('click', function() {		
		var alt = $(this).closest('div').attr('alt');
		if(alt==1)
		$('#imagePreview'+alt).html('<img src="{{ asset('storage/uploads/images/default.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
		else
		$('#imagePreview'+alt).html('<img src="{{ asset('storage/uploads/images/defaultpage.jpg') }}" alt="Uploaded Image" style="width:100%; position:relative!important;">');
	});	

	
});
</script>
@endsection