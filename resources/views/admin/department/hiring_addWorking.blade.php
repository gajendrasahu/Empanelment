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
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:text-top!important;"></i>RAISE RESOURCE REQUEST (EoI)</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.hiring',0)}}" data-url="{{ route('add.eoirecord')}}" method="post" enctype="multipart/form-data">
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
				@if ($errors->any())
				<div class="col-sm-12">
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
				<div class="form-group">
					<div class="col-sm-10">
						Project Name <label id="req">*</label>
						<input type="text" class="form-control" name="projecttitle" id="projecttitle" value="{{old('projecttitle',session('projecttitle'))}}" placeholder="Project Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" @if(Session::has('categoryid')) disabled @endif/>
						<span class="text-danger">@error('projecttitle') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Project Duration <label id="req">(In months)*</label>
						<select class="form-control" name="projectduration" id="projectduration" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Project Duration" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" @if(Session::has('categoryid')) disabled @endif onchange="setDefaultDuration(this.value)">
							<option value="">--Project Duration--</option>
							@for($i=1;$i<=60; $i++)
								<option value="{{ $i }}" @if($i==session('projectduration')) selected @endif>{{ $i }} Months</option>
							@endfor
						</select>

						
						<span class="text-danger">@error('projectduration') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						Project Objective <label id="req">*</label>
						<input type="text" class="form-control" name="projectobjective" id="projectobjective" value="{{old('projectobjective',session('projectobjective'))}}" placeholder="Project Objective" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" @if(Session::has('categoryid')) disabled @endif/>
						<span class="text-danger">@error('projectobjective') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						Type of Empanelment <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="form-control" name="categoryid" id="categoryid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Type of Empanelment" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus onchange="GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid');" @if(Session::has('categoryid')) disabled @endif>
							<option value="">--Type of Empanelment--</option>
							@foreach ($category as $itm)
							@if($itm->categoryid==2)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',session('categoryid')))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
							@endif
							@endforeach
							
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>					
					<div class="col-sm-2">
						<i class="fa fa-info-circle right open-vendor" data-url="{{route('show.vendorslist')}}" style="margin-top:0px; font-size:14px;"></i>
						Tier Reference <label id="req">*</label>
						
						<select class="form-control" name="tierid" id="tierid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Tier Reference" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetForm('{{route('load-form')}}')">
							<option value="">--Tier--</option>
							@if(Session::has('categoryid'))
							@php
							$categoryid = session('categoryid');

							$tierlist =DB::table('pricing_tbl as a')
										->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
										->select('b.tierid as value','b.tiername as label')
										->where('a.categoryid','=',$categoryid)
										->orderby('b.tiername')
										->get();        

							@endphp
							@foreach($tierlist as $tier)
							<option value="{{$tier->value}}" @if(old('tierid',session('tierid'))==$tier->value) selected @endif>{{$tier->label}}</option>
							@endforeach
							@endif
							
						</select>

						<span class="text-danger">@error('tierid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12 displayform">
<table class="mytable" border="1">
<thead>
<tr class="myheadbg">
    <td class="padding-5" colspan="9" style="width:150px;" nowrap>Resources Requirement</td>
</tr>
<tr class="myheadbg">
    <td class="padding-5" style="width:150px;" nowrap>Sector</td>
	<td class="padding-5" style="width:150px;" nowrap>Position</td>
	<td class="padding-5" style="width:100px;" nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
	<td class="padding-5" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-5" style="width:80px;" nowrap>Duration</td>
	<td class="padding-5" style="width:120px;" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i></td>
	<td class="padding-5" style="" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	<td class="padding-5" style="width:80px;" nowrap colspan="2"></td>
</tr>
</thead>
<tbody id="dataRows">
<tr id="entryRow">
	<td>
		<select name="sectorid" id="sectorid" class="selectbx width-100 sectorid" onchange="GetExperience(this.value,'{{ route('experiences.list') }}','remunerationid',document.getElementById('categoryid').value)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			<option value="">--Sector--</option>
		</select>
	</td>
	<td>
		<select name="remunerationid" id="remunerationid" class="selectbx width-100 remunerationid" onchange="GetRemuneration(this.value,'{{ route('get.remuneration') }}','budget')" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			<option value="">--Position--</option>
		</select>
	</td>
	<td><input type="text" name="experience" id="experience" readonly class="selectbx width-100 experience" placeholder="Experience" title="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"></td>
	<td>
		<select name="employmenttype" id="employmenttype" class="selectbx width-100 employmenttype" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			<option value="FULL TIME">FULL TIME</option>
			<option value="PART TIME">PART TIME</option>
		</select>
	</td>
	<td>
		<select class="selectbx width-100" name="duration"  id="duration" onchange="CalculateBudget()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
				<option value="">--Duration--</option>
		</select>	
	</td>
	<td>
		<input type="hidden" name="basebudget" id="basebudget">
		<input type="hidden" name="baseadmincharge" id="baseadmincharge">
		<input type="hidden" name="total" id="total">
		<input type="text" name="budget" id="budget" class="selectbx width-100 budget" placeholder="(Including tax)" readonly onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
	</td>
	<td>
		<input type="text" name="remark" id="remark" class="selectbx width-100 remark" placeholder="Additional qualification" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
	</td>
	<td colspan="2">
		<button type="button" class="btn btn-info selectbx width-100 addToListBtn" tabindex="{{$t++}}"><i class="fa fa-plus"></i> Add Resource</button>
	</td>
	
</tr>
</tbody>
</table><br>
<table class="mytable" border="1">
<tr class="myheadbg"><td colspan="9" class="padding-5">Resources Details</td></tr>
<tr class="myheadbg">
    <td class="padding-5 center" nowrap>S.No.</td>
	<td class="padding-5" style="width:130px;" nowrap>Sector</td>
	<td class="padding-5" style="width:130px;" nowrap>Position</td>
	<td class="padding-5" style="width:100px;" nowrap>Experience <i class="fa fa-info-circle" title="As per empanelment"></i></td>
	<td class="padding-5" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-5" style="width:80px;" nowrap>Duration</td>
	<td class="padding-5" style="width:120px;" nowrap>Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i></td>
	<td class="padding-5" style="" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	<td class="padding-5" style="width:80px;" nowrap></td>
</tr>
<tbody>
<tr>
	<td class="padding-5" colspan="9" style="text-align:right; font-size:16px;">
		<b>Grand Total : 0 INR</b>
	</td>
</tr>
</tbody>
</table>

					
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
<table class="mytable" border="1" style="margin-top:10px;">
	<tr class="myheadbg">
		<td colspan="2" class="mytdleftwhite" style="padding:4px!important;">About Project* <a href="#" class="open-about white" data-url="{{ route('read.aboutproject') }}">(Click Here For Sample Data)</a>
		</td>
	</tr>
	<tr>
		<td colspan="2">
<textarea class="form-control" name="aboutproject" required id="aboutproject" placeholder="Enter about project" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{!!old('aboutproject',$aboutproject)!!}</textarea>		
			
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;" class="padding-5">
			<button type="button" class="btn btn-info frmSaveOnly" tabindex="{{$t++}}" id="" style="width:150px;">
			SAVE PROGRESS
			</button>		
		</td>
	</tr>

	<tr class="myheadbg">
		<td colspan="2" class="mytdleftwhite" style="padding:4px!important;">Scope Of Work* <a href="#" class="open-scope white" data-url="{{ route('read.scopeofwork') }}">(Click Here For Sample Data)</a>
</td>
	</tr>	
	<tr>
		<td colspan="2">
<textarea class="form-control" name="scopeofwork" required id="scopeofwork" placeholder="Enter scope of work detail" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{{old('scopeofwork',$scope)}}</textarea>		
			
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;" class="padding-5">
			<button type="button" class="btn btn-info frmSaveOnly" tabindex="{{$t++}}" id="" style="width:150px;">
			SAVE PROGRESS
			</button>		
		</td>
	</tr>
	
	<tr class="myheadbg">
		<td colspan="2" class="mytdleftwhite" style="padding:4px!important;">Special Condition of Contract (if any) <a href="#" class="open-otherproject white" data-url="{{ route('read.otherproject') }}">(Click Here For Sample Data)</a></td>
	</tr>	
	<tr>
		<td colspan="2">
<textarea class="form-control" name="anyother" required id="anyother" placeholder="Special Condition of Contract (if any)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">{{old('anyother',$anyother)}}</textarea>		
			
		</td>
	</tr>
	<tr>
		<td colspan="2" style="text-align:right;" class="padding-5">
			<button type="button" class="btn btn-info frmSaveOnly" tabindex="{{$t++}}" id="" style="width:150px;">
			SAVE PROGRESS
			</button>		
		</td>
	</tr>
	
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
		<td colspan="2" class="no-padding">
	<table class="mytable committeetable" border="1" style="text-transform:none!important;">
		<tr class="myheadbg">
			<td class="padding-5" colspan="6">
				ADD MORE ATTACHMENTS
			</td>
		</tr>
		<tr class="myheadbg">
			<td class="padding-2">Attachment Title</td>
			<td class="padding-2">Attachment</td>
			<td class="padding-2" style="width:100px;">
				<button type="button" class="btn btn-info mygridbtn addBtn" style="width:100px;"><i class="fa fa-plus"></i> ADD</button>
			</td>
		</tr>
		<tr>
			<td class="padding-0">
				<input type="text" name="attachmenttitle[]" required placeholder="Attachment title" autocomplete="off" class="selectbx full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</td>
			<td class="padding-0" style="width:150px;">
				<label class="file-upload" nowrap style="width:170px;">
				  <i class="fa fa-upload"></i> Attach File (optional)
				  <input type="file" class="file-input" name="attachmentfile[]" accept=".pdf, .doc, .docx">
				</label>			
			</td>
			<td class="padding-0"></td>
		</tr>
	</table>
	<div class="trmsg myheadbg padding-5" style="display:none; width:100%; text-align:center;"></div>



		</td>
	</tr>
	<tr><td colspan="2">&nbsp;</td></tr>
	<tr>
	  <td class="text-left" colspan="2" style="padding: 10px; text-transform: none;">
		<label style="display:flex; align-items:flex-start; gap: 10px; font-size: 14px; line-height: 1.5;">
		  <input type="checkbox" name="declaration" id="declaration" style="width: 16px; height: 16px; margin-top:3px;" tabindex="{{$t++}}">
		  <span>
			I hereby confirm that all the information provided above is accurate and submitted by me. I understand that this requirement is essential for my department's project, and I take full responsibility for the authenticity of the details. I agree to comply with CHiPS policies and acknowledge that the request will be processed as per internal guidelines.
		  </span>
		</label>
	  </td>
	</tr>

	<tr>
		<td class="text-right" colspan="2">
			<button type="button" class="btn btn-info frmPrint" tabindex="{{$t++}}" id="frmPrint" disabled style="width:250px; float:left;">
			SAVE PROGRESS & PRINT
			</button>		
		
			<button type="button" class="btn btn-info" tabindex="{{$t++}}" id="frmSubmit" disabled style="width:175px;">SUBMIT (if completed)</button>
		</td>
	
	</tr>
</table>
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif

</div>
</div>


						
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

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

$(document).ready(function () {

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

    });

});

    tinymce.init({
        selector: '#scopeofwork',
        promotion: false,
        branding: false,
        height: 250,
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

    tinymce.init({
        selector: '#aboutproject',
        promotion: false,
        branding: false,
        height: 250,
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

    tinymce.init({
        selector: '#anyother',
        promotion: false,
        branding: false,
        height: 250,
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


$(".con").css("display","none");
@if(session('categoryid')==2)
	setTimeout(function() { GetForm('{{route("load-form")}}'); },2000);
@endif
/*
*/
document.getElementById('declaration').addEventListener('change', function () {
	document.getElementById('frmSubmit').disabled = !this.checked;
	document.getElementById('frmPrint').disabled = !this.checked;
});

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
		bootbox.confirm('DO YOU WANT TO CLEAR LIST?',function(result){
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

	$('#frmSubmit').on('click', function(e) {
		e.preventDefault();
		bootbox.confirm('DO YOU WANT TO SUBMIT THIS EoI REQUEST?',function(result){
			if(result)
			{
				var recs	=	document.getElementById("recs").value;
				if(recs>=1)
				document.forms['frm'].submit();
				else
				bootbox.alert("TO PROCEED, KINDLY ENSURE AT LEAST ONE REQUIREMENT IS ADDED BEFORE SUBMITTING YOUR EXPRESSION OF INTEREST (EoI).");
				
			}
		});
	});


	$('#frmPrint').on('click', function(e) {
		e.preventDefault();
		tinymce.triggerSave();

		const aboutproject  = document.getElementById('aboutproject').value.trim();
		const scopeofwork   = document.getElementById('scopeofwork').value.trim();
		const anyother      = document.getElementById('anyother').value.trim();

		// Create a form dynamically
		let form = document.createElement("form");
		form.method = "POST";
		form.action = "{{ route('print.eoi') }}";
		form.target = "_blank";

		// Add CSRF token
		let csrf = document.createElement("input");
		csrf.type = "hidden";
		csrf.name = "_token";
		csrf.value = "{{ csrf_token() }}";
		form.appendChild(csrf);

		// Add form fields
		const fields = {
			scope: scopeofwork,
			aboutproject: aboutproject,
			anyother: anyother
		};

		for (let key in fields) {
			let input = document.createElement("input");
			input.type = "hidden";
			input.name = key;
			input.value = fields[key];
			form.appendChild(input);
		}

		document.body.appendChild(form);
		form.submit();
		form.remove();
	});	


	$('.frmSaveOnly').on('click', function(e) {
		e.preventDefault();
		tinymce.triggerSave();

		const aboutproject  = document.getElementById('aboutproject').value.trim();
		const scopeofwork   = document.getElementById('scopeofwork').value.trim();
		const anyother      = document.getElementById('anyother').value.trim();

		let formData = new FormData();
		formData.append('_token', '{{ csrf_token() }}');
		formData.append('scope', scopeofwork);
		formData.append('aboutproject', aboutproject);
		formData.append('anyother', anyother);

		$.ajax({
			url: '{{ route("saveonly.eoi") }}',
			method: 'POST',
			data: formData,
			processData: false,
			contentType: false,
			success: function(response) {
				if (response.status === 200) {
					// You can customize what happens after a successful save
					bootbox.alert('Data saved successfully!');
				} else {
					bootbox.alert('Something went wrong: ' + response.message);
				}
			},
			error: function(xhr) {
				bootbox.alert('An error occurred while saving the data.');
			}
		});
	});

});

jQuery(function($) {	

	$('#draftformat').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Draft Format with Seal & Signature (Optional) max file size : 5 mb, PDF only',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#eoiformat').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Complete EoI Format (Optional) max file size : 5 mb, PDF only',
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
            <td class="padding-0">
                <input type="text" name="attachmenttitle[]" required placeholder="Attachment title" autocomplete="off" class="selectbx full-wdth" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
            </td>
            <td class="padding-0" style="width:100px;">
                <label class="file-upload" nowrap style="width:170px;">
                    <i class="fa fa-upload"></i> Attach File (optional)
                    <input type="file" class="file-input" name="attachmentfile[]" accept=".pdf, .doc, .docx">
                </label>			
            </td>
            <td class="padding-0">
                <button type="button" class="btn btn-info mygridbtn removeRow"><i class="fa fa-trash"></i> Remove</button>
            </td>
        </tr>`;
        
        $('.committeetable').append(newRow);
    });

    $('.mytable').on('click', '.removeRow', function () {
        $(this).closest('tr').remove();
    });		

	
});	
document.addEventListener('change', function (e) {
    // Match only single file input fields with class .file-input
    if (e.target && e.target.classList.contains('file-input')) {
        const input = e.target;
        const label = input.parentElement;

        if (input.files.length > 0) {
            const fileName = input.files[0].name;
            label.innerHTML = `<i class="fa fa-check"></i> 1 File Selected`;
            label.appendChild(input); // keep the input inside the label
        } else {
            label.innerHTML = '<i class="fa fa-upload"></i> Resume';
            label.appendChild(input);
        }
    }
});

function setDefaultDuration(val)
{
	var $duration = $('#duration');
	$duration.empty();
    if (!isNaN(val)) {
      for (var i = 1; i <= val; i++) {
        $duration.append('<option value="' + i + '">' + i + ' Months</option>');
      }
    }	
	$("#duration").val(val);
}



const preventSelect = (e) => {
  e.preventDefault();  // Prevent changing the option
};

$(document).ready(function() {
  const durationSelect = document.getElementById('duration');
  const employmentType = document.getElementById('employmenttype').value;

  // Initially set duration select box to readonly by default
  if (employmentType === 'FULL TIME') {
    durationSelect.addEventListener('mousedown', preventSelect);
  }
});

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection