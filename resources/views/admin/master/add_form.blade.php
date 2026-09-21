@extends('admin.admin_master')
@section('admin')

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE PROFILE</a></li>

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px;">
			<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
				<div class="row">
<div class="row">
	<form name="frm" id="frm" action="" method="post" enctype="multipart/form-data">
		@if(Session::has('success'))
		<div class="col-sm-12">
			<div class="alert alert-success">{{ Session::get('success') }}</div>
		</div>
		@endif
		@if(Session::has('fail'))
		<div class="col-sm-12">
			<div class="alert alert-danger">{{ Session::get('fail') }}</div>
		</div>
		@endif
		@csrf
		<div class="form-group">
			<div class="col-sm-4" style="text-align:left;">
				<div id="imagePreview">
					<img src="" alt="Uploaded Image" style="width:300px;">
				</div>
			</div>

			<div class="col-sm-8">
				<div class="col-sm-4">
					PROFILE NAME<label id="req">*</label>
					<input type="text" class="form-control" name="name" id="name" value="" required placeholder="Name" autofocus autocomplete="off"/>

					<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ $message }} </i> @enderror</span>
				</div>
				<div class="col-sm-3">
					MOBILE NUMBER<label id="req">*</label>
					<input type="text" class="form-control" required name="mobilenumber" id="mobilenumber" value="" placeholder="Mobile Number" autocomplete="off"/>

					<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ $message }} </i> @enderror</span>
				</div>
				<div class="col-sm-5">
					EMAIL ADDRESS<label id="req">*</label>
					<input type="text" class="form-control" required name="email" id="email" value="" placeholder="EMAIL ADDRESS" autocomplete="off" style="text-transform:lowercase;" />

					<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ $message }} </i> @enderror</span>
				</div>
				<div class="col-sm-12">&nbsp;</div>
				<div class="col-sm-12">
					ADDRESS<label id="req">*</label>
					<input type="text" class="form-control" required name="address" id="address" value="" placeholder="ADDRESS" autocomplete="off"/>

					<span class="text-danger">@error('address') <i class="fa fa-hand-o-right"> {{ $message }} </i> @enderror</span>
				</div>
				<div class="col-sm-12">&nbsp;</div>
				<div class="col-sm-7">
					UPLOAD IMAGE (MAX : 2 MB)<label id="req">&nbsp;</label>
					<input type="file" class="form-control" name="profilepic" id="profilepic"/>

					<span class="text-danger">@error('profilepic') <i class="fa fa-hand-o-right"> {{ $message }} </i> @enderror</span>
				</div>
				<div class="col-sm-3" align="left">
					<button type="submit" class="btn btn-info myfrmbtn">UPDATE PROFILE</button>
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

<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
/*
$(document).ready(function() {
    $('#profilepic').ace_file_input({
        no_file: 'No File ...',
        btn_choose: 'Choose Image',
        btn_change: 'Change',
        thumbnail: 'small',
        allowExt: ['jpg', 'jpeg', 'png', 'gif'],
        before_remove: function() {
            return true; // Allow the removal
        }
    });

    $('#profilepic').on('change', function() {
        const fileInput = document.getElementById('profilepic');
        const preview = document.getElementById('imagePreview');
        const maxSize = 1024*1024; // 5MB
        const maxWidth = 200; // Maximum width in pixels
        const maxHeight = 50; // Maximum height in pixels
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];

            if (file.size > maxSize) {
                bootbox.alert("FILE SIZE NOT ALOWED");
                $('#profilepic').ace_file_input('reset_input'); // Reset the input
                return false;
            }

        const image = new Image();
        image.src = URL.createObjectURL(file);
        image.onload = function () {
            if (image.width > maxWidth || image.height > maxHeight) {
                bootbox.alert("FILE WIDTH HEIGHT DOES NOT MATCHED");
                $('#imagePreview').html("");
                $('#profilepic').ace_file_input('reset_input'); // Reset the input
                return false;
            }
            else
            {
            	preview.appendChild(image);
            }
        };


        }

    });
});
*/
$(document).ready(function() {
    setupAceFileInput('profilepic','imagePreview');
});

function setupAceFileInput(inputid,imgpreview)
{
	$('#'+inputid).ace_file_input({
        no_file: 'No File ...',
        btn_choose: 'Choose Image',
        btn_change: 'Change',
        thumbnail: 'small',
        allowExt: ['jpg', 'jpeg', 'png', 'gif'],
        before_remove: function() {
            return true; // Allow the removal
        }
    });

    $('#'+inputid).on('change', function() {
        const fileInput = document.getElementById(inputid);
        const preview = document.getElementById(imgpreview);
        const maxSize = 1024*1024; // 5MB
        const maxWidth = 200; // Maximum width in pixels
        const maxHeight = 50; // Maximum height in pixels
        
        if (fileInput.files.length > 0) {
            const file = fileInput.files[0];

            if (file.size > maxSize) {
                bootbox.alert("FILE SIZE NOT ALOWED");
                $('#profilepic').ace_file_input('reset_input'); // Reset the input
                return false;
            }

        const image = new Image();
        image.src = URL.createObjectURL(file);
        image.onload = function () {
            if (image.width > maxWidth || image.height > maxHeight) {
                bootbox.alert("FILE WIDTH HEIGHT DOES NOT MATCHED");
                $('#'+imgpreview).html("");
                $('#'+inputid).ace_file_input('reset_input'); // Reset the input
                return false;
            }
            else
            {
            	preview.appendChild(image);
            }
        };


        }

    });
}
</script>
@endsection