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
				<form name="frm" id="frm" action="{{ route('store.hiring',0)}}" data-url="{{ route('add.eoirecord')}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<br>
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
					<br>
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
					<br>
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@if ($errors->any())
				<div class="col-sm-12">
					<br>
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						<ul>
							@foreach ($errors->all() as $error)
								<li>{{ $error }}</li>
							@endforeach
						</ul>
				    </div>
				</div>
				@endif				
				@csrf

                <div class="multi-panel multi-panel-default">
                    <div class="multi-panel-heading">
                        <h3 class="multi-panel-title">Project and Resource Details</h3>
                    </div>
                    <div class="multi-panel-body">
                        <!-- Progress Bar -->
                        <div class="progress progress-striped">
                            <div class="progress-bar progress-bar-success active" role="progressbar" id="progressBar" style="width: 33%; vertical-align:middle; line-height:30px;">
                                Step 1 of 3
                            </div>
                        </div>
                        
                        <!-- Step Indicator -->
                        <ul class="multi-step-indicator">
                            <li class="active completed">
                                <span class="multi-step-number">1</span>
                                <span class="multi-step-title form-label">Project Details</span>
                            </li>
                            <li class="active">
                                <span class="multi-step-number">2</span>
                                <span class="multi-step-title form-label">Resources Requirement</span>
                            </li>
                            <li>
                                <span class="multi-step-number">3</span>
                                <span class="multi-step-title form-label">Upload Request Letter</span>
                            </li>
                        </ul>
                        
                        <!-- Step 1: Basic Information -->
                        <div class="multi-form-section" id="step1">
					
							<div class="col-sm-9 padding-5">
								<span class="form-label">Project Name <label id="req">*</label></span>
								<input type="text" class="form-control" name="projecttitle" id="projecttitle" value="{{old('projecttitle',session('projecttitle'))}}" placeholder="Project Name" autofocus required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
								<span class="text-danger">@error('projecttitle') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-3 padding-5">
								<span class="form-label">Project Duration <label id="req">(In months)*</label></span>
								<select class="form-control" name="projectduration" id="projectduration" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Project Duration" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" @if(Session::has('categoryid')) disabled @endif onchange="setDefaultDuration(this.value)">
									<option value="">--Project Duration--</option>
									@for($i=3;$i<=60; $i++)
										<option value="{{ $i }}" @if($i==session('projectduration')) selected @endif>{{ $i }} Months</option>
									@endfor
								</select>

								
								<span class="text-danger">@error('projectduration') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-12">&nbsp;</div>
							<div class="col-sm-12 padding-5">
								<span class="form-label">Project Objective <label id="req">*</label></span>
								<textarea class="form-control tinymce" name="projectobjective" required id="projectobjective" placeholder="Project objective" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!!old('projectobjective',$projectobjective)!!}</textarea>										

								<span class="text-danger">@error('projectobjective') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-12 padding-5">
<button type="button" class="multi-btn multi-btn-info frmSaveOnly" tabindex="{{$t++}}" id="" style="width:150px;">
<i class="fa fa-save"></i> Save Progress
</button>		
							</div>
							<div class="col-sm-12 padding-5">
								<span class="form-label">Scope of Work <label id="req">*</label></span> <a href="#" class="open-scope sample-link" data-url="{{ route('read.scopeofwork') }}">(Click here for sample data)</a>
								<textarea class="form-control tinymce" name="scopeofwork" required id="scopeofwork" placeholder="Enter scope of work detail" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{{old('scopeofwork',$scope)}}</textarea>									

								<span class="text-danger">@error('scopeofwork') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-12 padding-5">
								<button type="button" class="multi-btn multi-btn-info frmSaveOnly" tabindex="{{$t++}}" id="" style="width:150px;">
								<i class="fa fa-save"></i> Save Progress
								</button>		
							</div>

							<div class="col-sm-12 padding-5">
								<span class="form-label">Other Information (if any)<label id="req">&nbsp;</label></span> <a href="#" class="open-otherproject sample-link" data-url="{{ route('read.otherproject') }}">(Click here for sample data)</a>
								<textarea class="form-control tinymce" name="anyother" id="anyother" placeholder="Other information (if any)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{{old('anyother',$anyother)}}</textarea>									

								<span class="text-danger">@error('anyother') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-12">&nbsp;</div>

                        </div>
                        
                        <!-- Step 2: EoI Details -->
                        <div class="multi-form-section active" id="step2" style="height:300px;">
							<div class="col-sm-3 padding-5">
								<span class="form-label">Type of Empanelment <label id="req">*</label></span>
								<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
								<select class="form-control" name="categoryid" id="categoryid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Type of Empanelment" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
									@foreach ($category as $itm)
									@if($itm->categoryid==2)
									<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',session('categoryid')))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
									@endif
									@endforeach
									
								</select>
								<input type="hidden" name="tierid" id="tierid" value="1">
								<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
							</div>
							<div class="col-sm-9 mt-30 text-right">
							<button type="button" class="btn btn-info btn-sm add-resource-btn" onclick="AddResource()" tabindex="{{$t++}}" id="" >
								<i class="fa fa-plus"></i> Add Resource
							</button>		
							
							</div>
							<div class="col-sm-12 alert alert-block alert-danger no-radius font-14">
								If the order value exceeds ₹5 Crore, the Expression of Interest (EoI) shall be published only to Tier 1 empanelled companies.
							</div>
							<div class="col-sm-12 padding-5 displayform">
							
							</div>
                            
                            
                        </div>
                        
                        <!-- Step 3: Requirements -->
                        <div class="multi-form-section" id="step3" style="height:400px; max-height:400px; overflow:scroll;">
							<table class="table table-bordered table-striped table-hover resource-table upload-table">
								<tr class="myheadbg">
									<td class="padding-8 form-label" colspan="6" style="vertical-align:middle!important;">
										Upload Required Documents
									</td>
								</tr>
								<tr>
									<td class="padding-10 upload-input">
							<input type="text" name="authletter" required placeholder="" readonly autocomplete="off" class="form-control full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="Request letter from authorized signatory*">
									</td>
									<td class="padding-10 upload-file" >
							<input type="file" class="form-control attachment" required name="auth_letter" id="auth_letter" accept=".pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
							
							</table>

							<table class="mytable table table-bordered table-striped table-hover resource-table upload-table committeetable">
								<tr class="myheadbg">
									<td class="padding-8 form-label" colspan="6">
										Upload Other Documents (if any)
									</td>
								</tr>
								<tr>
									<td class="padding-10 upload-input">
							<input type="text" name="attachmenttitle[]" required placeholder="Attachment title" autocomplete="off" class="form-control full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
									</td>
									<td class="padding-10 upload-file" >
							<input type="file" class="form-control attachment" required name="attachmentfile[]" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
									<td class="padding-10 upload-actions" >
							<button type="button" class="btn btn-info addBtn" style="width:100px; padding:0px!important;">
											<i class="fa fa-plus"></i> ADD
										</button>
									</td>
								</tr>
							</table>
							<table class="table resource-table upload-confirm-table">
								<tr>
									<td class="padding-10 upload-confirm-cell">
		<label style="display:flex; align-items:flex-start; gap: 10px; font-size: 13px; line-height: 1.5;">
		  <input type="checkbox" name="declaration" id="declaration" tabindex="{{$t++}}">
		  <span class="form-label">
			The above information is declared to be true, accurate, and duly submitted. Recognizing its importance for the department’s project, I accept complete responsibility for its authenticity.
		  </span>
		</label>

		<label style="display:flex; align-items:flex-start; gap: 10px; font-size: 13px; line-height: 1.5;">
		  <input type="checkbox" name="declaration1" id="declaration1" tabindex="{{$t++}}">
		  <span class="form-label">
			I confirm adherence to CHiPS policies and acknowledge that the request shall be processed under the applicable internal guidelines.
		  </span>
		</label>
									

									</td>
								</tr>
							</table>
								
                        </div>
                        
                       
                        <!-- Navigation Buttons -->
						
                        <div class="multi-form-navigation" style="margin-top:50px;">
						


                            <button type="button" class="multi-btn multi-btn-default" id="prevBtn" onclick="changeStep(-1)"><i class="fa fa-angle-double-left"></i> Previous</button>
                            <button type="button" class="multi-btn multi-btn-primary" id="nextBtn" onclick="changeStep(1)">Next <i class="fa fa-angle-double-right"></i></button>
                            <button type="button" class="multi-btn multi-btn-success" id="submitBtn" style="display: none;">Submit EoI</button>
							
							<button type="button" class="multi-btn multi-btn-success" id="clrBtn">Clear All Data</button>
                        </div>
						
                    </div>
                </div>


				
				</form>
					
					
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>

<div class="modal fade" id="docModal" tabindex="-1">
  <div class="modal-dialog modal-xl" style="width:95%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Sample Data</h5>
        <!-- Proper close icon button -->
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal">Close</button>
      </div>
      <div class="modal-body" id="docContent">
        Loading...
      </div>
      <div class="modal-footer">
        <!-- Text button to close -->
        <button type="button" class="btn btn-close" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<div class="modal fade" id="editModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Update Resource Data</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
      <div class="modal-body" id="editContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="comparisionModal">
  <div class="modal-dialog modal-xl" style="width:95%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" style="float:left;">Resource Cost by Duration</h5>
        <button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
      <div class="modal-body" id="comparisionContent">
        Loading...
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-close" data-bs-dismiss="modal" onclick="ClsResource()">Close</button>
      </div>
    </div>
  </div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

function showPrice(rl)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			$('#comparisionModal').modal('show');
			$('#comparisionContent').html(response.formhtml);
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});
}


function editEois(rl,recordid)
{
	let formData = new FormData();
	formData.append('_token', '{{ csrf_token() }}');
	formData.append('recordid', recordid);
	$.ajax({
		url: '{{ route("update.resource_detail") }}',
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#editModal').modal('show');
				$('#editContent').html(response.formhtml);
			}
			else
			{
				alert(response.status);
				bootbox.alert('Something went: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});

}
function ClsResource()
{
	$('#editModal').modal('hide');
	$('#editContent').html("");
	$('#comparisionModal').modal('hide');
	$('#comparisionContent').html("");

}


function AddResource()
{
	$('#resourceModal').modal('show');
}




$(document).ready(function () {

/*	
	@if(!session('tierid'))
	GetTiersWithId(document.getElementById("categoryid").value,'{{ route('tiers.list') }}','tierid');
	@endif
*/

	
    $('.open-vendor').on('click', function (e) {
        e.preventDefault();
        var docUrl = $(this).data('url');
		$(".modal-xl").css("width","70%")
		$(".modal-title").html("Vendors List");
        $('#docContent').html('Loading...');
        $('#docModal').modal('show');

        $.get(docUrl, function (data) {
            $('#docContent').html(data);
        }).fail(function () {
            $('#docContent').html('<p class="text-danger">Failed to load document.</p>');
        });
    });

    $('.open-scope').on('click', function (e) {
        e.preventDefault();
        var docUrl = $(this).data('url');
		$(".modal-xl").css("width","95%")
		$(".modal-title").html("Sample Scope Of Work Content");
        $('#docContent').html('Loading...');
        $('#docModal').modal('show');

        $.get(docUrl, function (data) {
            $('#docContent').html(data);
        }).fail(function () {
            $('#docContent').html('<p class="text-danger">Failed to load document.</p>');
        });
    });

    $('.open-about').on('click', function (e) {
        e.preventDefault();
        var docUrl = $(this).data('url');
		$(".modal-xl").css("width","95%")
		$(".modal-title").html("Sample About Project Content");
        $('#docContent').html('Loading...');
        $('#docModal').modal('show');

        $.get(docUrl, function (data) {
            $('#docContent').html(data);
        }).fail(function () {
            $('#docContent').html('<p class="text-danger">Failed to load document.</p>');
        });
    });

    $('.open-otherproject').on('click', function (e) {
        e.preventDefault();
        var docUrl = $(this).data('url');
		$(".modal-xl").css("width","95%")
		$(".modal-title").html("Sample Other Project Requirement Content");
        $('#docContent').html('Loading...');
        $('#docModal').modal('show');

        $.get(docUrl, function (data) {
            $('#docContent').html(data);
        }).fail(function () {
            $('#docContent').html('<p class="text-danger">Failed to load document.</p>');
        });
    });

    $('.btn-close').on('click', function (e) {

        $('#docContent').html('');
        $('#docModal').modal('hide');
		$('#resourceModal').modal('hide');
    });

});

	



$(".con").css("display","none");
/*
@if(session('categoryid')==2)
	setTimeout(function() { GetForm('{{route("load-form")}}'); },2000);
@endif
*/
setTimeout(function() { GetForm('{{route("load-form")}}'); },2000);

function checkDeclarations() {
	const declaration = document.getElementById('declaration');
	const declaration1 = document.getElementById('declaration1');

	const bothChecked = declaration.checked && declaration1.checked;

	document.getElementById('submitBtn').disabled = !bothChecked;
}

$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
	
</script>
<script>
$(document).ready(function() {

	$('#clrBtn').on('click', function(e) {
		e.preventDefault();
		bootbox.confirm('Do you want to clear the EoI detail? Once cleared, this action cannot be reversed!',function(result){
			if(result)
			{
				var form = $('#frm')[0];
				var formData = new FormData(form);
				$.ajax({
					url: '{{route("clear.eois")}}',
					method: 'POST',
					data: formData,
					processData: false,
					contentType: false,
					success: function(response) {
						if(response.status === 200)
						{
							window.location.reload();
						}
						else
						{
							bootbox.alert('Something went wrong: ' + response.message);
						}
					},
					error: function(xhr) {
					}
				});
			}
		});
	});


});

jQuery(function($) {	

	$('#draftformat').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload draft format with seal & signature (Optional) max file size : 5 mb, PDF only',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#eoiformat').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload complete EoI format (Optional) max file size : 5 mb, PDF only',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
		
});


function deleteEois(rl,recordid,ind,asking,msg) {
    var ind =   ind-1;
	var flag=0;
    bootbox.confirm(asking,function(result){
        if(result)
        {
            $.ajax({
                url: '/delete/'+rl+'/' + encodeURIComponent(recordid),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if(response.fail=='')
                    {
						bootbox.alert({
							message: msg,
							callback: function () {
								setTimeout(function () {
									$(".no-skin").css("padding-right", "");
									GetForm('{{route("load-form")}}');
								},1000);
							}
						});
                    }
                    else
                    {
                        bootbox.alert(response.fail);
                    }
                },
                error: function (xhr) {
                    //console.log(xhr);
                }
            });        
        }
    });
}

$(document).ready(function() {

	$('.addBtn').on('click', function () {
		let newRow = `
		<tr class="datarow">
			<td class="padding-10 upload-input">
							<input type="text" name="attachmenttitle[]" required placeholder="Attachment title" autocomplete="off" class="form-control full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</td>
			<td class="padding-10 upload-file" >
							<input type="file" class="form-control attachment" required name="attachmentfile[]" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
			</td>
			<td class="padding-10 upload-actions" >
							<button type="button" class="btn btn-info removeRow"><i class="fa fa-trash"></i> Remove</button>
			</td>
		</tr>`;

		// Append the new row
		$('.committeetable').append(newRow);

		// Re-initialize ace_file_input on the newly added file input
		$('.committeetable .attachment').last().ace_file_input({
			no_file: 'No File ...',
			btn_choose: 'Attachment',
			btn_change: 'Change',
			btn_name: 'fourthimage',
			thumbnail: false
		});
	});

    $('.mytable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
    });		

	
});	

function setDefaultDuration(val)
{
	var $duration = $('#duration');
	$duration.empty();
    if (!isNaN(val)) {
      for (var i = 4; i <= val; i++) {
        $duration.append('<option value="' + i + '">' + i + ' Months</option>');
      }
    }	
	$("#duration").val(val);
}

</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multi-form.js') }}"></script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>

<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>


<script>
setTimeout(function(){
jQuery(function($) {

    // -----------------------------
    // TinyMCE editors configuration
    // -----------------------------
    const editors = [
        { selector: 'textarea.tinymce', minHeight: 180 },
        { selector: '#scopeofwork', height: 250 },
        { selector: '#anyother', height: 250 },
        { selector: '#projectobjective', height: 250 }
    ];

    editors.forEach(cfg => {
        tinymce.init({
            selector: cfg.selector,
            promotion: false,
            branding: false,
            height: cfg.height || cfg.minHeight,
            min_height: cfg.minHeight || 0,
            autoresize_min_height: cfg.minHeight || 0,
            autoresize_max_height: 700,
            autoresize_bottom_margin: 10,
            toolbar_mode: 'floating',
            plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
            toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
            menubar: false,
            statusbar: false,
            resize: true,
            setup: function(editor) {
                editor.on('init', function() {
                    editor.getBody().style.fontSize = '14px';
                    editor.getBody().style.lineHeight = '1.5';
                    const contentArea = editor.getContainer().querySelector('.tox-edit-area__iframe');
                    if (contentArea) contentArea.style.overflow = 'auto';
                });
                editor.on('change keyup', function() {
                    editor.save();
                });
            }
        });
    });

    // -----------------------------
    // Submit button validation
    // -----------------------------
    $('#submitBtn').on('click', function(e) {
        e.preventDefault();

        // Basic validations
        if ($('#auth_letter').get(0)?.files.length === 0) {
            bootbox.alert("Request letter from authorized signatory is mandatory.");
            return false;
        }

        if (!$('#declaration').is(':checked') || !$('#declaration1').is(':checked')) {
            bootbox.alert("<b>Please confirm your acknowledgment</b><br><br>You must confirm adherence to CHiPS policies and acceptance of internal guidelines by checking the checkboxes.");
            return false;
        }

        if ($('#tier_choice').val() === '') {
            bootbox.alert("Please select the Tier of firm to float the EoI.");
            return false;
        }

        // Attachment validations
        let attachmentError = false;
        $('tr').each(function() {
            const titleInput = $(this).find('input[name="attachmenttitle[]"]');
            const fileInput  = $(this).find('input[name="attachmentfile[]"]')[0];

            if (!titleInput.length || !fileInput) return true;

            const titleVal = titleInput.val().trim();
            const hasFile = fileInput.files?.length > 0;

            if (hasFile && titleVal === '') {
                bootbox.alert('Please enter the attachment title for the selected file.');
                titleInput.focus();
                attachmentError = true;
                return false;
            }

            if (!hasFile && titleVal !== '') {
                bootbox.alert('Please select a file for the entered attachment title.');
                $(fileInput).focus();
                attachmentError = true;
                return false;
            }
        });

        if (attachmentError) return false;

        // Final confirmation
        bootbox.confirm('Do you want to submit this EoI request?', function(result) {
            if (result) {
                const recs = parseInt($('#recs').val());
                if (recs >= 1) {
                    $('#frm')[0].submit();
                } else {
                    bootbox.alert("To proceed, please add at least one resource detail before submitting your request.");
                }
            }
        });
    });

    // -----------------------------
    // Print EoI
    // -----------------------------
    $('#frmPrint').on('click', function(e) {
        e.preventDefault();
        tinymce.triggerSave();

        const fields = {
            projectobjective: $('#projectobjective').val().trim(),
            aboutproject: $('#aboutproject').val().trim(),
            scopeofwork: $('#scopeofwork').val().trim(),
            anyother: $('#anyother').val().trim()
        };

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('print.eoi') }}";
        form.target = "_blank";

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        $.each(fields, (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
        form.remove();
    });

    // -----------------------------
    // Save only (Ajax)
    // -----------------------------
    $('.frmSaveOnly').on('click', function(e) {
        e.preventDefault();
        tinymce.triggerSave();
        const formData = new FormData($('#frm')[0]);

        $.ajax({
            url: '{{ route("saveonly.eoi") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status !== 200) {
                    bootbox.alert('Something went wrong: ' + response.message);
                }
            },
            error: function(xhr) {
                const errors = xhr.responseJSON?.errors;
                let message = '';
                if (errors) {
                    $.each(errors, (key, val) => {
                        message += '<i class="fa fa-hand-o-right"></i> ' + val + '<br>';
                    });
                } else {
                    message = 'An unexpected error occurred.';
                }
                bootbox.alert(message);
            }
        });
    });

    // -----------------------------
    // Next step save (Ajax)
    // -----------------------------
    $('#nextBtn').on('click', function(e) {
        e.preventDefault();
        tinymce.triggerSave();

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        formData.append('projectobjective', $('#projectobjective').val().trim());
        formData.append('scopeofwork', $('#scopeofwork').val().trim());
        formData.append('anyother', $('#anyother').val().trim());
        formData.append('projecttitle', $('#projecttitle').val().trim());
        formData.append('projectduration', $('#projectduration').val().trim());

        $.ajax({
            url: '{{ route("saveonly.eoi") }}',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.status !== 200) {
                    bootbox.alert('Something went wrong: ' + response.message);
                }
            },
            error: function(xhr) {
                if (xhr.responseJSON?.errors) {
                    let allMessages = '';
                    $.each(xhr.responseJSON.errors, (field, messages) => {
                        messages.forEach(msg => { allMessages += msg + '<br>'; });
                    });
                    bootbox.alert(allMessages);
                }
            }
        });
    });

});
 },2000);
</script>

@endsection