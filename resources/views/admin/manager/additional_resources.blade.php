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
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">

<ul class="nav nav-tabs padding-0">
	
		<li class="active">
			<a data-toggle="tab" href="#home" class="form-label font-14">
				<i class="green ace-icon fa fa-users bigger-120 datalist" style="vertical-align:text-top;"></i>Add Additional Resources to EoI
			</a>
		</li>
	
</ul>

	
<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="#" method="post" enctype="multipart/form-data">
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
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $eoi->projecttitle }}
								</div>
								<div class="details-detail lh-25">
									<b>{{ $eoi->eoinumber }}</b> | <b>Release Date : {{ date('d\-m\-Y',strtotime($eoi->releasedate)) }}</b> | <b>Last Date for Submission of Proposals : {{ date('d\-m\-Y, h:i A',strtotime($eoi->deadlinedate)) }}</b><br>
								</div>
							</div>
						</div>
						
						
						@if(!$eoi->addition_resource_letter)
						<div class="ui-card-detail">
							<div>
								<div class="title-detail lh-20">
									<i class="fa fa-file-pdf-o font-14 mt-3"></i> Please Upload Approval Letter for Additional Resources
								</div>
								<div class="details-detail mt-20">
									<input type="file" name="addition_resource_letter" id="addition_resource_letter" accept="application/pdf">
									<input type="hidden" id="requestid" value="{{ Crypt::encrypt($eoi->requestid) }}">
								</div>
							</div>
						</div>
						@else
						<div class="ui-card-detail">
							<div style="position:relative; width:100%; height:30px;">
								<div class="title-detail lh-20">
									<i class="fa fa-file-pdf-o font-14 mt-3"></i> Uploaded Approval Letter for Additional Resources
								</div>
								<div class="details-detail" style="position:absolute; right:-25px; top:-5px;">
									<a href="{{route('view.uploadedfile',Crypt::encrypt($eoi->addition_resource_letter))}}" target="_blank">
										<button type="button" class="btn btn-info gridbtn width-100">
											<i class="fa fa-file-pdf-o"></i> View File
										</button>
									</a>
								</div>
							</div>
						</div>

						<div class="section-block-detail">
							<div class="table-responsive">
							@if($eoi->categoryid==2)
								<table class="mytable pd-8" border="1" style="text-transform:none!important">
									<tr class="myheadbg font-bold">
										<td colspan="5"><i class="fa fa-user"></i> Additional Resource Details</td>
									</tr>
									<tr>
										<td>
											<select name="sectorid" id="sectorid" class="select2" data-placeholder="Sector*" style="border-radius:0px!important;">
												<option value=""></option>
												@foreach($sectors as $sector)
												<option value="{{$sector->sectorid}}">{{$sector->sectorname}}
												@endforeach
											</select>
										</td>
										<td>
											<select name="positionid" id="positionid" class="select2" data-placeholder="Position*" style="border-radius:0px!important;">
												<option value=""></option>
												@foreach($positions as $position)
												<option value="{{$position->positionid}}" data-experience="{{ $position->experience }}">{{$position->consultantposition}}
												</option>
												@endforeach
											</select>
										</td>
										<td>
											<input type="text" name="experience" id="experience" class="form-control" placeholder="Experience*" readonly style="margin-top:-2px;">
										</td>
										<td>
											<select name="employmenttype" id="employmenttype" class="form-control" onKeyPress="return OnKeyPress(this, event)" onchange="PopulateDuration()" style="margin-top:-2px;">
												<option value="FULL TIME">FULL TIME</option>
												<option value="PART TIME">PART TIME</option>
											</select>
										</td>
										<td>
											<input type="hidden" name="projectduration" id="projectduration" value="{{$eoi->projectduration}}">
											<select class="form-control select2" name="duration"  id="duration" onchange="CalculateBudget()" onKeyPress="return OnKeyPress(this, event)" style="margin-top:-2px;" data-placeholder="Duration*">
													<option value=""></option>
													@php
													$d=1;
													@endphp
													@while($d<=$eoi->projectduration)
														<option value="{{$d}}" @if($eoi->projectduration==$d) selected @endif>{{$d}} Month</option>
													@php
													$d++;
													@endphp
													@endwhile
											</select>	
										</td>
									</tr>
									<tr>
										
										<td colspan="5" style="padding:0px!important;">
										<textarea name="qualification" id="qualification" class="form-control qualification tinymce" placeholder="Qualification*" title="" onKeyPress="return OnKeyPress(this, event)"></textarea>
										</td>
									</tr>
									<tr>
										
										<td colspan="5" style="padding:0px!important;">
										<textarea name="remark" id="remark" class="form-control remark tinymce" placeholder="Remark" title="" onKeyPress="return OnKeyPress(this, event)"></textarea>
										</td>
									</tr>
									<tr>
										<td colspan="5" class="text-right">
											<button type="button" class="btn btn-info gridbtn width-100" id="addResourceBtn">
												<i class="fa fa-user"></i> Add Resource
											</button>
										</td>
									</tr>
								</table>
							@endif
							</div>
						</div>


						<div class="section-block-detail">
							<div class="table-responsive">
								<table class="mytable pd-8" border="1" style="text-transform:none!important">
									<tr class="myheadbg font-bold">
										<td colspan="6"><i class="fa fa-users"></i> Additional Resource Details</td>
									</tr>
									@if($eoi->categoryid==2)
									<tr class="myheadbg font-bold">
										<td class="text-center" style="width:35px;">S.No.</td>
										<td>Sector</td>
										<td>Position</td>
										<td>Experience</td>
										<td>Deployment Type</td>
										<td>Duration</td>
									</tr>
									@else
									<tr class="myheadbg font-bold">
										<td class="center" style="width:35px;">S.No.</td>
										<td>Position</td>
										<td>Experience Level</td>
										<td>Experience</td>
										<td>Deployment Type</td>
										<td>Duration</td>
									</tr>
									@endif
									@if($eoi->categoryid==2)
										@if($eoi->resources->count()!=0)
										@foreach($eoi->resources as $resource)
										<tr>
											<td class="text-center">{{$loop->iteration}}</td>
											<td>{{$resource->sectorname}}</td>
											<td>{{$resource->consultantposition}}</td>
											<td>{{$resource->experience}}</td>
											<td>{{$resource->employmenttype}}</td>
											<td>{{$resource->duration}} Months</td>
										</tr>
										<tr>
											<td class="myheadbg" colspan="2"><b>Qualification</b></td>
											<td colspan="4">{!! $resource->qualification !!}</td>
										</tr>
										<tr>
											<td class="myheadbg" colspan="2"><b>Remark</b></td>
											<td colspan="4">{!! $resource->remark !!}</td>
										</tr>
										@endforeach
										@else
										<tr><td colspan="6" class="center">--No Record Found--</td></tr>
										@endif
										
									@else
									@endif
								</table>
							</div>
						</div>
						@endif

						
					</div>
				
					<div class="col-sm-12">&nbsp;</div>
					
				</div>		
			</form>
		</div>
	</div>
</div>



</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>

<script>
$('#addition_resource_letter').ace_file_input({
	no_file: 'No File ...',
	btn_choose: 'Approval Letter',
	btn_change: 'Change',
	btn_name: 'btnname',
	thumbnail: false //| true | large
})

$(document).ready(function () {

    $('#positionid').select2();

    $('#positionid').on('select2:select', function (e) {

        var experience = $(this).find('option:selected').attr('data-experience');

        $('#experience').val(experience || '');

    });

    // Clear experience when selection is removed
    $('#positionid').on('select2:clear', function () {
        $('#experience').val('');
    });

});

$('#addition_resource_letter').on('change', function () {

    var fileInput = this;

    if (this.files.length === 0) {
        return;
    }
	var file = this.files[0];

	if(file.type !== 'application/pdf')
	{
		bootbox.alert('Only PDF files are allowed.');
		$(this).val('');
		return;
	}
    bootbox.confirm({
        title: "Confirm Upload",
        message: "Are you sure you want to upload this additional resource requirement letter?<br><br>" +
             "<b>Once uploaded, this action cannot be reverted.</b><br><br>" +
             "<i class='fa fa-info-circle'></i> After successful upload, please add the required resource details individually as per the approved requirement.",
        buttons: {
            confirm: {
                label: 'Yes, Upload',
                className: 'btn-success'
            },
            cancel: {
                label: 'Cancel',
                className: 'btn-danger'
            }
        },
        callback: function (result)
		{
            if(result)
			{
                var formData	=	new FormData();
                formData.append('addition_resource_letter', fileInput.files[0]);
                formData.append('requestid', $('#requestid').val());
                formData.append('_token', $('meta[name="csrf-token"]').attr('content'));

                $.ajax({
                    url: "{{ route('upload.additional.resource.letter') }}",
                    type: "POST",
                    data: formData,
                    processData: false,
                    contentType: false,
					success: function (response) {

						if (response.status == 200) {

							bootbox.alert({
								title: "Success",
								message: response.message,
								callback: function () {
									window.location.href = response.redirect;
								}
							});

						} else {
							bootbox.alert(response.message);
						}
					},
                    error: function () {
                        bootbox.alert({
                            title: "Error",
                            message: "Failed to upload the letter.",
                        });

                        $(fileInput).val('');
                    }
                });
            }
			else
			{
                $(fileInput).val('');
            }
        }
    });

});

function PopulateDuration()
{
	var projectduration = 	document.getElementById("projectduration").value;
	var employmenttype	=	document.getElementById("employmenttype").value;
	var $duration 		= 	$('#duration');
	$duration.empty();
	if (!isNaN(projectduration))
	{
		if(employmenttype=='FULL TIME')
		{
			for (var i = projectduration; i <= projectduration; i++)
			{
				$duration.append('<option value="' + i + '">' + i + ' Months</option>');
			}
		}
		if(employmenttype=='PART TIME')
		{
			for (var i = 3; i <=projectduration-1; i++)
			{
				$duration.append('<option value="' + i + '">' + i + ' Months</option>');
			}
		}
	}				
}

tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    menubar: false,
    statusbar: false,
    min_height: 100,
    autoresize_min_height: 100,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,
    resize: true,
    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
    }
});


$('#addResourceBtn').on('click', function () {
	tinymce.triggerSave();
    var formData = {
        sectorid: $('#sectorid').val(),
        positionid: $('#positionid').val(),
        experience: $('#experience').val(),
        employmenttype: $('#employmenttype').val(),
        duration: $('#duration').val(),
        qualification: $('#qualification').val(),
        remark: $('#remark').val(),
        requestid: "{{ Crypt::encrypt($eoi->requestid) }}",
        _token: "{{ csrf_token() }}"
    };


    bootbox.confirm({
        title: "Confirm Add Resource",
        message: "Are you sure you want to add this resource?<br><br>" +
             "<b>Once added, this action cannot be reverted.</b>",
        buttons: {
            confirm: {
                label: 'Yes, Add',
                className: 'btn-success'
            },
            cancel: {
                label: 'Cancel',
                className: 'btn-danger'
            }
        },
        callback: function (result) {

            if (result) {

                $.ajax({
                    url: "{{ route('store.eoi.resource') }}",
                    type: "POST",
                    data: formData,

                    beforeSend: function () {
                        $('#addResourceBtn').prop('disabled', true);
                    },

                    success: function (response) {

                        if (response.status == 200) {

                            bootbox.alert({
                                title: "Success",
                                message: response.message,
                                callback: function () {
                                    location.reload();
                                }
                            });

                        } else {

                            bootbox.alert(response.message);

                        }
                    },

                    error: function (xhr) {

                        $('#addResourceBtn').prop('disabled', false);

                        if (xhr.status === 422) {

                            var errors = xhr.responseJSON.errors;
                            var message = '';

                            $.each(errors, function (key, value) {
                                message += value[0] + '<br>';
                            });

                            bootbox.alert({
                                title: "Validation Error",
                                message: message
                            });

                        } else {

                            bootbox.alert({
                                title: "Error",
                                message: "Something went wrong. Please try again."
                            });

                        }
                    },

                    complete: function () {
                        $('#addResourceBtn').prop('disabled', false);
                    }

                });

            }

        }
    });

});
</script>


<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection