@php
$i=1;
$t=0;
@endphp
<div class="table-responsive" style="margin-top:20px;">
<table class="table table-bordered table-striped font-13 table-hover resource-table">
<tr class="myheadbg">
	<td colspan="8" class="padding-10 form-label resource-table-title">
		Resource Details According to Tier-1 Pricing Structure
		<button type="button" class="btn btn-info btn-sm add-resource-btn" onclick="AddResource()" tabindex="{{$t++}}" id="" >
			<i class="fa fa-plus"></i> Add Resource
		</button>		
	</td>
</tr>
<tr class="myheadbg">
    <td class="padding-10 form-label center" nowrap style="width:30px;">S.No.</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Sector & Position</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>
		Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i>
	</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-10 form-label text-right" style="width:80px;" nowrap>Duration</td>
	<td class="padding-10 form-label text-right" style="width:120px;" nowrap>Rate</td>
	<td class="padding-10 form-label" style="" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualifications, depending on experience."></i></td>
	<td class="padding-10 form-label" style="width:30px;" nowrap></td>
</tr>
<tbody class="eoiresources">
@if($data)
	@php $r=0; @endphp
	@foreach($data as $rec)
	@php 
		$r=$r+1;
		$withtax	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing1->tax)/100)),'2','.','');
	@endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd v-top center padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}<br>{{$rec->consultantposition}}</td>
		<td class="mytd v-top padding-10">{{$rec->experience}}@if($rec->qualification!='')<br>{!!$rec->qualification!!} @endif</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->employmenttype))}}</td>
		<td class="mytd v-top padding-10 text-right" nowrap>{{$rec->duration}} Months</td>
		<td class="mytd v-top padding-10 text-right" nowrap>
			
			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($rec->baseprice,2,'.','') }}"
			   data-tax="{{ $pricing1->tax }}"
			   data-withtax="{{ $withtax }}"
			   data-duration="{{ $rec->duration }}"
			   data-total="{{ number_format($rec->budget,2,'.','') }}">
			</i>

			<span class="format-indian" data-value="{{ number_format($rec->budget,'2','.','') }}"></span>
		</td>
		
		<td class="mytd v-top padding-10">{!!$rec->remark!!}</td>
		<td class="mytd v-top padding-10 center" nowrap>
			<i class="fa fa-trash" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
			
			<i class="fa fa-edit" onclick="editEois('edit_eoi','{{ Crypt::encrypt($rec->recordid) }}')" style="margin-left:10px;"></i>
			
		</td>
	</tr>
	@php
	@endphp
	@endforeach
@endif
@if($r==0)
@endif
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>Cost of Resources (Incuding Tax)</b></td>
	<td class="padding-10"><b>{{$totalmanmonth}}</b></td>
	<td class="padding-10" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>CHiPS Admin Charge ({{$pricing1->admincharge}}%)</b></td>
	<td class="padding-10"><b>{{$adminchargetotal}}</b></td>
	<td class="padding-10" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>Grand Total</b></td>
	<td class="padding-10"><b>{{$grandtotal}}</b></td>
	<td class="padding-10" colspan="2">
		<input type="hidden" name="grandtotal" id="grandtotal" value="{{$grandtotal}}">
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
	</td>
</tr>

</tbody>
</table>
</div>

<div class="table-responsive" style="margin-top:20px;">
<table class="table table-bordered table-striped font-13 table-hover resource-table">
<tr class="myheadbg"><td colspan="8" class="padding-10 form-label resource-table-title">Resource Details According to Tier-2 Pricing Structure
</td></tr>

<tr class="myheadbg">
    <td class="padding-10 form-label center" nowrap style="width:30px;">S.No.</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Sector & Position</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i></td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-10 form-label text-right" style="width:80px;" nowrap>Duration</td>
	<td class="padding-10 form-label text-right" style="width:120px;" nowrap>Rate</td>
	<td class="padding-10 form-label" style="" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualification"></i></td>
	<td class="padding-10 form-label" style="width:30px;" nowrap></td>
</tr>
<tbody class="eoiresources1">
@if($data1)
	@php $r=0; @endphp
	@foreach($data1 as $rec)
	@php 
		$r=$r+1; 
		$withtax	=	number_format(($rec->baseprice+(($rec->baseprice*$pricing2->tax)/100)),'2','.','');
	@endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd v-top center padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}<br>{{$rec->consultantposition}}</td>
		<td class="mytd v-top padding-10">{{$rec->experience}}@if($rec->qualification!='')<br>{!!$rec->qualification!!} @endif</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->employmenttype))}}</td>
		<td class="mytd v-top padding-10 text-right" nowrap>{{$rec->duration}} Months</td>
		<td class="mytd v-top padding-10 text-right" nowrap>

			<i class="fa fa-info-circle info-icon"
			   data-toggle="tooltip"
			   data-baseprice="{{ number_format($rec->baseprice,2,'.','') }}"
			   data-tax="{{ $pricing2->tax }}"
			   data-withtax="{{ $withtax }}"
			   data-duration="{{ $rec->duration }}"
			   data-total="{{ number_format($rec->budget,2,'.','') }}">
			</i>
			
			<span class="format-indian" data-value="{{ number_format($rec->budget,'2','.','') }}"></span>
		</td>
		
		<td class="mytd v-top padding-10">{!!$rec->remark!!}</td>
		<td class="mytd v-top padding-10 center" nowrap>
		
		<i class="fa fa-trash" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i>
		
		<i class="fa fa-edit" onclick="editEois('edit_eoi','{{ Crypt::encrypt($rec->recordid) }}')" style="margin-left:10px;"></i>
		
		</td>
	</tr>
	@php
	@endphp
	@endforeach
@endif
@if($r==0)
@endif
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>Cost of Resources (Incuding Tax)</b></td>
	<td class="padding-10"><b>{{$totalmanmonth1}}</b></td>
	<td class="padding-10" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>CHiPS Admin Charge ({{$pricing2->admincharge}}%)</b></td>
	<td class="padding-10"><b>{{$adminchargetotal1}}</b></td>
	<td class="padding-10" colspan="2">&nbsp;</td>
</tr>
<tr>
	<td class="padding-10" style="text-align:right;" colspan="5"><b>Grand Total</b></td>
	<td class="padding-10"><b>{{$grandtotal1}}</b></td>
	<td class="padding-10" colspan="2">
		<input type="hidden" name="grandtotal" id="grandtotal" value="{{$grandtotal1}}">
		<input type="hidden" name="recs" id="recs" value="{{$r}}">
	</td>
</tr>
<tr>
	<td class="padding-10 form-label tier-choice-cell" colspan="8">
		<div class="tier-choice-wrap">
		<span class="tier-choice-label">Select Tier of Firms to Float EoI</span>
		<select class="form-control tier-choice-select" name="tier_choice" id="tier_choice">
			<option value="">--Select Tier--</option>
			<option value="1">Tier I</option>
			@if(round(str_replace(",","",$grandtotal1))<50000000)
			<option value="2">Tier II</option>
			<option value="3">Both</option>
			@endif
		</select>
	
		</div>
	</td>
</tr>

</tbody>

</table>
</div>



<div class="modal fade" id="resourceModal">
  <div class="modal-dialog modal-xl" style="width:90%; margin-top:0px;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;">Add Resource</h5>
      </div>
      <div class="modal-body" id="resourceContent" style="background-color:white!important;">
		<div class="row padding-5" style="background-color:white!important;">
			<div class="col-sm-3 padding-5">
				<span class="form-label">Sector <label id="req">*</label></span>
				<select name="sectorid" id="sectorid" class="form-control sectorid" onchange="GetExperience(this.value,'{{ route('experiences.list') }}','remunerationid',document.getElementById('categoryid').value)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
					<option value="">--Sector--</option>
					@foreach($sectors as $sector)
					<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-sm-2 padding-5">
				<span class="form-label">Position <label id="req">*</label></span>
				<select name="remunerationid" id="remunerationid" class="form-control remunerationid" onchange="GetRemuneration(this.value,'{{ route('get.remuneration') }}','budget')" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
					<option value="">--Position--</option>
				</select>

			</div>
			<div class="col-sm-3 padding-5">
				<span class="form-label">Experience (As per empanelment)<label id="req">*</label></span>
				<input type="text" name="experience" id="experience" readonly class="form-control experience" placeholder="Experience" title="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</div>
			<div class="col-sm-2 padding-5">
				<span class="form-label">Deployment Type <label id="req">*</label></span>
				<select name="employmenttype" id="employmenttype" class="form-control employmenttype" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="PopulateDuration()">
					<option value="FULL TIME">FULL TIME</option>
					<option value="PART TIME">PART TIME</option>
				</select>
			</div>
			<div class="col-sm-2 padding-5">
				<span class="form-label">Duration (in months)<label id="req">*</label></span>
				<select class="form-control" name="duration"  id="duration" onchange="CalculateBudget()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						<option value="">--Duration--</option>
				</select>	
			</div>
			<div class="col-sm-12">&nbsp;</div>		
			<div class="col-sm-12 padding-5">
				<span class="form-label">Qualification<label id="req">*</label></span>
				<textarea name="qualification" id="qualification" class="form-control qualification tinymce" placeholder="Qualification" title="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"></textarea>
			</div>
			<div class="col-sm-12">&nbsp;</div>		
			<!--			
			<div class="col-sm-6 padding-5">
				<span class="form-label">Man Month Rate (Including tax)<label id="req">*</label></span>
			</div>
			-->
			<div class="col-sm-12 padding-5">
				<span class="form-label">Remark<label id="req">&nbsp;</label></span>
				<textarea name="remark" id="remark" class="form-control remark tinymce" placeholder="Additional qualification" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"></textarea>

				<input type="hidden" name="basebudget" id="basebudget">
				<input type="hidden" name="baseadmincharge" id="baseadmincharge">
				<input type="hidden" name="total" id="total">
				<input type="hidden" name="budget" id="budget" class="form-control budget" placeholder="Man Month Rate (Including tax)" readonly onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">

			</div>
			
		</div>
		<div class="alert bg-success padding-5 alert-msg" style="display:none;"></div>
      </div>
      <div class="modal-footer">
        <!-- Text button to close -->
        <button type="button" class="multi-btn multi-btn-info btn btn-close" style="float:left;" data-bs-dismiss="modal">
			<i class="fa fa-remove"></i> Close
		</button>
		
		No. of resources :
		<select name="resource_qty" id="resource_qty" style="height:30px; margin-right:20px;">
			@for($i=1;$i<=15;$i++)
			<option value="{{$i}}">{{$i}}</option>
			@endfor
		</select>
		<button type="button" class="multi-btn multi-btn-info addToListBtn" tabindex="{{$t++}}">
			<i class="fa fa-plus"></i> Add Resource
		</button>
      </div>
    </div>
  </div>
</div>

<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>
$(this).tooltip({
  html: true,
  boundary: 'window',
  placement: 'auto'
});
$(function () {
  $('.info-icon').each(function () {

    let base   = $(this).data('baseprice');
    let tax    = $(this).data('tax');
    let withTx = $(this).data('withtax');
    let dur    = $(this).data('duration');
    let total  = $(this).data('total');

    let html =
      '<table>' +
        '<tr><td class="padding-5 text-left" nowrap>Base Price (A)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + base + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left" nowrap>Tax (B=A+(A*' + tax + '%))</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + withTx + '"></td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Duration (in months) (C)</td>' +
            '<td nowrap class="padding-5 text-left">' + dur + '</td></tr>' +

        '<tr><td nowrap class="padding-5 text-left">Total (D=B*C)</td>' +
            '<td nowrap class="padding-5 text-left format-indian" data-value="' + total + '"></td></tr>' +
      '</table>';

    $(this)
      .attr('data-original-title', html)
      .tooltip({ html: true, boundary: 'window', placement: 'auto' });
  });
});
</script>

</script>
<script>

tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    menubar: true,
    statusbar: false,
    min_height: 60,
    autoresize_min_height: 60,
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


$('.addToListBtn').on('click', function(e) {
	e.preventDefault();
	tinymce.triggerSave();
	var form = $('#frm')[0];
	var formData = new FormData(form);
	$.ajax({
		url: $('#frm').data('url'),
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status === 200)
			{
				$("#sectorid").val("");
				$("#positionid").val("");
				$("#experience").val("");
				tinymce.get('qualification')?.setContent('');
				if(document.getElementById("categoryid").value==1)
				$("#remunerationid").val("");
				if(document.getElementById("categoryid").value==2)
				{
					$("#remunerationid").empty();
					$("#remunerationid").append("<option value=''>--Position--</option>");
				}
				$("#employmenttype").val("FULL TIME");
				$("#duration").val(document.getElementById("projectduration").value);
				$("#budget").val("");
				tinymce.get('remark')?.setContent('');
				$("#qualification").val("");
				$('.eoiresources').html(response.formhtml);
				$('.eoiresources1').html(response.html1);
				$('.alert-msg').css("display","");
				$('.alert-msg').html("<i class='fa fa-thumbs-up'></i> Resource detail addedd successfully!");
				setTimeout(function() { $('.alert-msg').css("display","none"); $('.alert-msg').html(""); },3000);
				$("#projecttitle").prop("disabled","disabled");
				$("#projectduration").prop("disabled","disabled");
				$("#categoryid").prop("disabled","disabled");
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
		}
	});
});

function CalculateBudget()
{
	var duration		=	Number(document.getElementById("duration").value);
	var employmenttype	=	document.getElementById("employmenttype").value;
	var projectduration	=	Number(document.getElementById("projectduration").value);
	var basebudget		=	Number(document.getElementById("basebudget").value);
	if(duration>projectduration)
	{
		document.getElementById("duration").value=document.getElementById("projectduration").value;
		bootbox.alert("Employment duration cannot exceed the project duration.");
	}
	var budget	=	Number(document.getElementById("basebudget").value*duration);
	
	if (isNaN(duration) || duration==0) duration = 1;
	document.getElementById("budget").value=budget.toFixed(2);
}

@if(!Session('duration_month'))
{
	$("#duration").val(document.getElementById("projectduration").value);
}
@endif

function GetBudget(val)
{
	var duration	=	Number(document.getElementById("duration").value);
	var basebudget	=	Number(document.getElementById("basebudget").value);
	document.getElementById("budget").value=duration*basebudget

	const employmentType = document.getElementById('employmenttype').value;  // Get selected employment type
	const durationSelect = document.getElementById('duration');  // Get the duration select element

	// Make the duration select box readonly if "FULL TIME" is selected
	if (employmentType === 'FULL TIME')
	{
		document.getElementById('duration').value=document.getElementById('projectduration').value;
		document.getElementById("budget").value=document.getElementById("basebudget").value;
		CalculateBudget();
	}
	else if(employmentType==='PART TIME')
	{
		durationSelect.removeEventListener('mousedown', preventSelect);  // Remove the event listener to allow selection
	}
}


    $('.btn-close').on('click', function (e) {

		$('#resourceModal').modal('hide');
    });

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});


$(document).on('shown.bs.tooltip', '.info-icon', function () {
    let tooltipId = $(this).attr('aria-describedby');
    let tooltipEl = document.getElementById(tooltipId);

    if (!tooltipEl) return;

    tooltipEl.querySelectorAll('.format-indian').forEach(function (el) {
        el.innerText = formatIndianNumber(el.dataset.value);
    });
});


</script>





