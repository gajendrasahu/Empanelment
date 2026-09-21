@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-input
{
	width:300px;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-building bigger-120" style="vertical-align:text-top;"></i>Work Order Creation
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
			<form name="workorderfrm" id="workorderfrm" action="{{ route('create.workorder') }}" method="post" enctype="multipart/form-data">
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
							@foreach ($errors->all() as $error)
								<i class="fa fa-warning"></i> {{ $error }}<br>
							@endforeach
						</div>
					</div>
				@endif		
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
					
						<div class="ui-card-detail">
							<div>
								<div class="title-detail lh-25">
									<i class="material-icons-outlined">topic</i> {{ ucwords(strtolower($vendor->companyname)) }} [{{$vendor->tiername}}]
								</div>
								<div class="details-detail">
									<b>Official Email</b> : {{$vendor->officialemail}}
								</div>
							</div>
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
							</div>
							@if($eoi->categoryid==2)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="padding-10 font-bold">Sector Name</td>
									<td class="padding-10 font-bold">Position</td>
									<td class="padding-10 font-bold">
										Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i>
									</td>
									<td class="padding-10 font-bold" style="width:120px;">
										Base Price <i class="fa fa-info-circle" title="Man Month Rate"></i>
									</td>
									<td class="padding-10 font-bold" style="width:120px;">Duration</td>
									<td class="padding-10 font-bold" style="width:120px;">
										Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i>
									</td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								@endphp
								@foreach($resumes as $index=>$req)
								<tr>
									<td class="padding-10 center">{{$loop->iteration}}</td>
									<td class="padding-10">{{ucwords(strtolower($req->sectorname))}}</td>
									<td class="padding-10">{{$req->consultantposition}}</td>
									<td class="padding-10">{{$req->experience}}<br>{!!$req->qualification!!}</td>
									<td class="padding-10 format-indian" data-value="{{$req->baseprice}}"></td>
									<td class="padding-10">{{$req->duration}} Months</td>
									<td class="padding-10 format-indian" data-value="{{ $req->budget }}"></td>
								</tr>
								@endforeach
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="6"><b>Cost of Resources (Incuding Tax)</b></td>
									<td class="padding-10"><b>{{$eoi->totalmanmonth}}</b></td>
								</tr>
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="6"><b>CHiPS Admin Charge ({{$eoi->admincharge}}%)</b></td>
									<td class="padding-10"><b>{{$eoi->adminchargetotal}}</b></td>
								</tr>
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="6"><b>Grand Total</b></td>
									<td class="padding-10"><b>{{$eoi->grandtotal}}</b></td>
								</tr>
								
							</table>
							@else
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="center padding-10 v-top" style="width:30px;">
										<input type="checkbox" class="allResource">
									</td>
									<td class="padding-10 font-bold" nowrap>Position</td>
									<td class="padding-10 font-bold" nowrap>Experience</td>
									<td class="padding-10 font-bold" nowrap>Name</td>
									<td class="padding-10 font-bold" nowrap>Level</td>
									<td class="padding-10 font-bold" nowrap>Qualification</td>
									<td class="padding-10 font-bold" nowrap>
										Base Price <i class="fa fa-info-circle" title="Man Month Rate"></i>
									</td>
									<td class="padding-10 font-bold" nowrap>Duration</td>
									<td class="padding-10 font-bold" nowrap>
										Rate <i class="fa fa-info-circle" title="Man Month Rate Including Tax"></i>
									</td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								@endphp
								@foreach($resumes as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10 center v-top">
										<input type="checkbox" class="resource" name="records[]" value="{{$req->recordid}}">
									</td>
									<td class="padding-10 v-top">{{$req->role}}</td>
									<td class="padding-10 v-top">{{$req->experience}}</td>
									<td class="padding-10">
										<input type="text" class="resource_name" name="name[{{$req->recordid}}]" placeholder="Name">
									</td>
									<td class="padding-10 v-top">
									<select name="exp_level[{{$req->recordid}}]">
									
										@foreach($levels as $level)
										@if($level->experiencelevel<=$req->experiencelevel)
										<option value="{{$level->experiencelevel}}" data-value="{{$level->remuneration}}-{{$req->duration}}-{{$pricing->operatingmargin}}-{{$pricing->tax}}" @if($level->experiencelevel==$req->experiencelevel) selected @endif>Level-{{$level->experiencelevel}}</option>
										@endif
										@endforeach
									</select>
									
									</td>
									<td class="padding-10 v-top">{!!$req->qualification!!}</td>
									<td class="padding-10 v-top format-indian" data-value="{{ $req->baseprice }}"></td>
									<td class="padding-10 v-top">{{$req->duration}} Months</td>
									<td class="padding-10 v-top format-indian incl-tax row-total" data-value="{{ $req->budget }}"></td>
								</tr>
								@endforeach
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="9"><b>Cost of Resources (Incuding Operating Margin+Tax)</b></td>
									<td class="padding-10"><b class="format-indian grand-total" data-value="{{str_replace(",","",$eoi->totalmanmonth)}}"></b></td>
								</tr>
								@if($eoi->admincharge!=0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="9"><b>CHiPS Admin Charge ({{$eoi->admincharge}}%)</b></td>
									<td class="padding-10"><b>{{$eoi->adminchargetotal}}</b></td>
								</tr>
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="9"><b>Grand Total</b></td>
									<td class="padding-10"><b>{{$eoi->grandtotal}}</b></td>
								</tr>
								@endif
							</table>
								
							@endif
						</div>
						<div class="section-block-detail">
							<div class="section-header-detail">
								<span class="material-icons-outlined">list</span>
								&nbsp;<h2 class="section-title-detail">Work Order Detail</h2>
							</div>
							<table class="mytable" border="1" style="text-transform: capitalize!important;">
								<tr>
									<td class="padding-10">MoM File*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="momfile" id="momfile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Marking Sheet*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="markingsheet" id="markingsheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Note Sheet*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="notesheet" id="notesheet" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								<tr>
									<td class="padding-10">Signed Order File*</td>
									<td class="padding-10">
										<input type="file" class="form-control" required name="signedcopy" id="signedcopy" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />
									</td>
								</tr>
								
								<tr>
									<td class="padding-10" style="width:200px;">LOI No.*</td>
									<td class="padding-10">
										<select name="loinumber" id="loinumber" class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
											<option value="{{$vendor->loinumber}}">{{$vendor->loinumber}}</option>
										</select>
									</td>
								</tr>
								<tr>
									<td class="padding-10">Subject*</td>
									<td class="padding-10">
										<input type="text" name="subject" id="subject" required placeholder="Subject" class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('subject')}}">
									</td>
								</tr>
								<tr>
									<td class="padding-10">Reference*</td>
									<td class="padding-10">
										<input type="text" name="refrence" id="refrence" required placeholder="Reference" class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('refrence',$eoi->eoinumber)}}">
										
										<input type="hidden" name="agreementdate" id="agreementdate" required class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important;" autocomplete="off" value="2025-07-01" readonly>
									</td>
								</tr>
								<tr>
									<td class="padding-10">Tender Number*</td>
									<td class="padding-10">
										<input type="text" name="tendernumber" id="tendernumber" required placeholder="Tender Number" class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="165081/CEO/CHiPS/Empanelment/Consultancy Firms/2025 Raipur, dated 20/02/2025" readonly>
									</td>
								</tr>
								<tr>
									<td class="padding-10" style="width:200px;">Project Type*</td>
									<td class="padding-10">
										<select name="project_type" id="project_type" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
											<option value="">--Project Type--</option>
											<option value="0">CHiPS</option>
											<option value="1">DEPARTMENTAL</option>
										</select>
										
									</td>
								</tr>
								
								<tr>
									<td class="padding-10" style="width:200px;">Signed By*</td>
									<td class="padding-10">
										<select name="signedby" id="signedby" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="generateWorkOrderNumber('{{Crypt::encrypt($eoi->requestid)}}','{{route('generate.wonumber')}}','ordernumber')">
											<option value="">--Signed By--</option>
											<option value="Chief Executive Officer (CEO)">Chief Executive Officer (CEO)</option>
											<option value="Jt. CEO (Project)">Jt. CEO (Project)</option>
											<option value="Jt. CEO(Finance)">Jt. CEO (Finance)</option>											
											<option value="Add. Chief Executive Officer">Add. Chief Executive Officer</option>
											<option value="Chief Operating Officer">Chief Operating Officer</option>
										</select>
										
									</td>
								</tr>
								<tr>
									<td class="padding-10" style="width:200px;">Order Date*</td>
									<td class="padding-10">
										<input type="text" name="orderdate" id="orderdate" required class="order_date" placeholder="dd-mm-YYYY" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:150px;" autocomplete="off" value="{{old('orderdate')}}">
										
									</td>
								</tr>
								<tr>
									<td class="padding-10" style="width:200px;">Order Prefix Number.*</td>
									<td class="padding-10">
										<input type="text" name="orderno" id="orderno" required placeholder="Order Prefix Number." class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:150px;" autocomplete="off" value="{{old('orderno')}}">
										
									</td>
								</tr>
								
								<tr>
									<td class="padding-10" style="width:200px;">Work Order Number.*</td>
									<td class="padding-10">
								<input type="hidden" name="vendorid" id="vendorid" value="{{old('vendorid',Crypt::encrypt($vendor->vendorid))}}">
								<input type="hidden" name="requestid" id="requestid" value="{{old('requestid',Crypt::encrypt($eoi->requestid))}}">
										<input type="text" name="ordernumber" id="ordernumber" required placeholder="Order No." class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('ordernumber')}}">
										
										
										<input type="hidden" name="copyto" id="copyto" required placeholder="Copy To" class="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;" autocomplete="off" value="{{old('copyto')}}" readonly>
									</td>
								</tr>
								
						@php

							$defaultTerms = <<<HTML
						<ol>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Payment Terms: As per Empanelment Agreement &amp; EoI</span></li>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Scope Of Work: As per EoI</span></li>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Duration of Work: As per EoI</span></li>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Qualification and Experience of Consultant: As per Empanelment Agreement &amp; EoI</span></li>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Other terms and conditions: As per Empanelment Agreement &amp; EoI &amp; Agreement Extension letter</span></li>
						<li><span style="font-size: 11.0pt; font-family: 'Times New Roman','serif'; mso-fareast-font-family: 'Times New Roman'; mso-font-kerning: 0pt; mso-ansi-language: EN-US; mso-fareast-language: EN-US; mso-bidi-language: AR-SA;">Rates will be as per tender No.-165081/CEO/CHiPS/Empanelment/ConsultancyFirm/2025 Raipur dated 20/02/2025 of consultancy firm.</span></li>
						</ol>
						HTML;

							$terms = old('termsandcondition') ?: ($defaultTerms ?: $defaultTerms);
						@endphp
								
								<tr>
									<td class="padding-10 v-top">Terms & conditions</td>
									<td class="padding-10">
										<textarea name="termsandcondition" id="termsandcondition" required placeholder="Terms & conditions" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" style="margin-top:-1px!important; width:100%;">{{$defaultTerms}}</textarea>

									</td>
								</tr>
								<tr>
									<td class="padding-10" colspan="2" style="border-top:none!important;">
										<a href="{{ route('view.pptresumes',Crypt::encrypt($eoi->requestid))}}">
										<button type="button" class="btn btn-info no-hover" style="width:150px; float:left;">
											<i class="fa fa-arrow-left"></i> Back
										</button>
										</a>
										<button type="button" class="btn btn-info generateorderBtn" style="width:auto; max-width:250px; float:right;" tabindex="{{$t++}}">
											<i class="fa fa-refresh"></i> Create Work Order
										</button>

									
									</td>
								</tr>
								
								<tr><td colspan="2" class="padding-10">&nbsp;</td></tr>
							</table>
							
						</div>
					</div>
				
				
<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
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


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


<script>

$(document).ready(function() {
  $('.allResource, .resource').prop('checked', true);
});

function SetOrderNo(val)
{
	
}

tinymce.init({
	selector: '#termsandcondition',
	promotion: false,
	branding: false,
	height: 250,
	elementpath: false,
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

$('.crtordbtn').on('click', function () {
	bootbox.confirm('Do you want to save this records?',function(result){
		if(result)
		{
			$("#crtordbtn").prop("disabled","disabled");
			let form = $('#workorderfrm')[0];
			let formData = new FormData(form);

			$.ajax({
				url: $('#workorderfrm').attr('action'),
				type: 'POST',
				data: formData,
				contentType: false,
				processData: false,
				headers: {
					'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
				},
				success: function (response) {
					if(response.status==200 || response.status==400)
					{
						bootbox.alert(response.message);
					}
					if(response.status==200)
					{
						$(".vieworderbtn").css("display","");
					}
				},
				error: function (xhr) {
					if (xhr.responseJSON && xhr.responseJSON.errors) {
						var errors = xhr.responseJSON.errors;
						var allMessages = '';

						$.each(errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});
						$("#submitBtn").prop("disabled","");
						bootbox.alert(allMessages);
					}			
				}
			});
		}
	});
});

$('.generateorderBtn').on('click', function () {
	
	@if($eoi->categoryid==1)
	let rec = 0;
	let flag = 0;
	document.querySelectorAll('tr').forEach(row => {
		const checkbox = row.querySelector('.resource');
		if (!checkbox || !checkbox.checked) return;

		rec++;

		const nameInput = row.querySelector('.resource_name');
		const nameValue = nameInput ? nameInput.value.trim() : '';

		if (nameValue === '') {
			flag++;
			return;
		}

		// letters + spaces + at most one dot
		const namePattern = /^[a-zA-Z ]+(\.[a-zA-Z ]+)?$/;

		if (!namePattern.test(nameValue)) {
			bootbox.alert("Names can only contain letters, spaces, and at most one dot.");
			flag++;
		}
	});
	if(rec === 0)
	{
		bootbox.alert("At least one resource must be selected.");
		return false;
	}

	if(flag > 0)
	{
		bootbox.alert("Name is required for all selected resources.");
		return false;
	}	
	@endif
	
	bootbox.confirm('Are you sure you want to create this work order? Once created, it cannot be undone.',function(result){
		if(result)
		{
            if ($('#paymentreceived').is(':checked'))
			{
                if (!$('#paymentfile').val()) {
                    bootbox.alert('If payment has been received, please upload the payment file before proceeding.');
                    return;
                }
            }
			if(!$('#momfile').val())
			{
				bootbox.alert('Please upload the mom file before proceeding.');
				return;				
			}
			if(!$('#markingsheet').val())
			{
				bootbox.alert('Please upload the marking sheet file before proceeding.');
				return;				
			}
			if(!$('#ordernumber').val())
			{
				bootbox.alert('Please enter order number before proceeding.');
				return;				
			}
			if(!$('#subject').val())
			{
				bootbox.alert('Please enter subject before proceeding.');
				return;				
			}
			if(!$('#orderno').val())
			{
				bootbox.alert('Please enter order number prefix value.');
				return;				
			}
			if(!$('#loinumber').val())
			{
				bootbox.alert('Please enter LOI number.');
				return;				
			}
			if(!$('#project_type').val())
			{
				bootbox.alert('Please select project type.');
				return;				
			}
			if(!$('#signedby').val())
			{
				bootbox.alert('Please select signed by name.');
				return;				
			}
			if(!$('#orderdate').val())
			{
				bootbox.alert('Please enter order date.');
				return;				
			}
			$("#workorderfrm").submit();
		}
	});
});




jQuery(function($) {	

	$('.attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Resume',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});


	$('#demandnotefile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Demand Note',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#paymentfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Payment File',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#momfile').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload MoM*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});

	$('#markingsheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Marking Sheet*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#notesheet').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Note Sheet*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
	$('#signedcopy').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Upload Order Copy*',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
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

</script>


@if($eoi->categoryid==1)

<script>
function calculateRows() {
  let grandTotal = 0;

  document.querySelectorAll('tr').forEach(row => {
    const checkbox = row.querySelector('.resource');
    const select   = row.querySelector('select[name^="exp_level["]');

    if (!checkbox || !select) return;

    const baseCell  = row.querySelector('.format-indian:not(.incl-tax)');
    const totalCell = row.querySelector('.row-total');

    // If unchecked → reset values
    if (!checkbox.checked) {
      if (baseCell) baseCell.dataset.value = '0.00';
      if (totalCell) totalCell.dataset.value = '0.00';
      return;
    }

    const option = select.selectedOptions[0];
    if (!option || !option.dataset.value) {
      if (baseCell) baseCell.dataset.value = '0.00';
      if (totalCell) totalCell.dataset.value = '0.00';
      return;
    }

    // remuneration-duration-margin-tax
    const [remuneration, duration, margin, tax] =
      option.dataset.value.split('-').map(Number);

    const A = remuneration * duration;
    const B = A + (A * margin / 100);
    const C = B + (B * tax / 100);

    if (baseCell) baseCell.dataset.value = A.toFixed(2);
    if (totalCell) totalCell.dataset.value = C.toFixed(2);

    grandTotal += C;
  });

  // Format all numbers
  document.querySelectorAll('.format-indian').forEach(el => {
    el.innerText = formatIndianNumber(el.dataset.value || '0.00');
  });

  // Update grand total
  $('.grand-total').text(formatIndianNumber(grandTotal.toFixed(2)));
}

// Change listeners
document.addEventListener('change', function (e) {
  if (
    e.target.matches('select[name^="exp_level["]') ||
    e.target.matches('.resource')
  ) {
    calculateRows();
  }
});

// Initial calculation
document.addEventListener('DOMContentLoaded', calculateRows);

// Select all resources
$('.allResource').on('change', function () {
  $('.resource').prop('checked', this.checked);
  calculateRows();
});
</script>
@endif
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

@endsection