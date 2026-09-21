@php
$i=1;
$t=0;
$grandtotal=0;
@endphp
<div class="table-responsive" style="margin-top:20px;">
<table class="table table-bordered table-striped font-13" border="1">
<tr class="myheadbg">
	<td colspan="11" class="padding-0 form-label" style="vertical-align:middle; line-height:30px; padding:0px 0px 0px 5px!important;">
		Resource Details
	</td>
</tr>
<tr class="myheadbg">
    <td class="padding-10 form-label center" nowrap style="width:30px;">S.No.</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Sector / Position / Role</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>
		Experience & Qualification <i class="fa fa-info-circle" title="As per empanelment"></i>
	</td>
	<td class="padding-10 form-label" style="width:130px;" nowrap>Deployment Type</td>
	<td class="padding-10 form-label" style="width:120px;" nowrap>Base Price</td>
	<td class="padding-10 form-label" style="width:80px;" nowrap>Duration</td>
	<td class="padding-10 form-label" style="width:80px;" nowrap>Total</td>
	<td class="padding-10 form-label" style="width:80px;" nowrap>With Tax</td>
	<td class="padding-10 form-label" style="width:80px;" nowrap>With Admin Charge</td>
	<td class="padding-10 form-label" style="" nowrap>Remark <i class="fa fa-info-circle" title="Additional qualification"></i></td>
	<td class="padding-10 form-label" style="width:30px;" nowrap></td>
</tr>
<tbody class="eoiresources">
@if($data)
	@php $r=0; @endphp
	@foreach($data as $rec)
	@php $r=$r+1; @endphp
	<tr class="mytr" id="item-{{ $r }}">
		<td class="mytd v-top center padding-10" nowrap>{{$r}}</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->sectorname))}}</td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->consultantposition}}<br>{{$rec->experience}}@if($rec->qualification!='')<br>{{$rec->qualification}} @endif</td>
		<td class="mytd v-top padding-10" nowrap>{{ucwords(strtolower($rec->employmenttype))}}</td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->remuneration }}"></td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->duration}} Months</td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->totalremuneration }}"></td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->withtax }}"></td>
		<td style="text-align:right;" class="mytd v-top padding-10 format-indian" nowrap data-value="{{ $rec->grandtotal }}"></td>
		<td class="mytd v-top padding-10" nowrap>{{$rec->remark}}</td>
		<td class="mytd v-top padding-10 center" nowrap><i class="fa fa-trash" onclick="deleteEois('deleteeois','{{ Crypt::encrypt($rec->recordid) }}',{{$r}},'{{ __('messages.confirmation') }}','{{ __('messages.deleted') }}')" title=""></i></td>
	</tr>
	@php
	$grandtotal	=	$grandtotal+$rec->grandtotal;
	@endphp
	@endforeach
@endif
@if($r==0)
@endif
	<tr>
		<td class="mytd v-top padding-10 form-label" style="text-align:right;" colspan="8">Grand Total</td>
		<td class="mytd v-top padding-10 form-label format-indian" style="text-align:right;" data-value="{{$grandtotal}}"></td>
		<td colspan="2">&nbsp;</td>
	</tr>

</tbody>
</table>
</div>

<div class="modal fade" id="resourceModal">
  <div class="modal-dialog modal-xl" style="width:50%;">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title form-label" style="float:left;">Add Resource</h5>
      </div>
      <div class="modal-body" id="resourceContent" style="background-color:white!important;">
		<div class="row padding-5" style="background-color:white!important;">
			<div class="col-sm-6 padding-5">
				<span class="form-label">Sector <label id="req">*</label></span>
				<select name="sectorid" id="sectorid" class="form-control sectorid" onchange="GetExperience(this.value,'{{ route('experiences.list') }}','remunerationid',document.getElementById('categoryid').value)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
					<option value="">--Sector--</option>
					@foreach($sectors as $sector)
					<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-sm-6 padding-5">
				<span class="form-label">Position <label id="req">*</label></span>
				<select name="remunerationid" id="remunerationid" class="form-control remunerationid" onchange="GetRemuneration(this.value,'{{ route('get.remuneration') }}','budget')" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
					<option value="">--Position--</option>
				</select>

			</div>
			<div class="col-sm-12">&nbsp;</div>
			<div class="col-sm-6 padding-5">
				<span class="form-label">Experience (As per empanelment)<label id="req">*</label></span>
				<input type="text" name="experience" id="experience" readonly class="form-control experience" placeholder="Experience" title="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</div>
			<div class="col-sm-6 padding-5">
				<span class="form-label">Qualification<label id="req">*</label></span>
				<input type="text" name="qualification" id="qualification" class="form-control qualification" placeholder="Qualification" title="" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			</div>
			<div class="col-sm-12">&nbsp;</div>
			<div class="col-sm-6 padding-5">
				<span class="form-label">Deployment Type <label id="req">*</label></span>
				<select name="employmenttype" id="employmenttype" class="form-control employmenttype" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="PopulateDuration()">
					<option value="FULL TIME">FULL TIME</option>
					<option value="PART TIME">PART TIME</option>
				</select>
			</div>
			<div class="col-sm-6 padding-5">
				<span class="form-label">Duration (in months)<label id="req">*</label></span>
				<select class="form-control" name="duration"  id="duration" onchange="CalculateBudget()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
						<option value="">--Duration--</option>
				</select>	
			</div>
			<div class="col-sm-12">&nbsp;</div>		
			<div class="col-sm-12 padding-5">
				<span class="form-label">Remark<label id="req">&nbsp;</label></span>
				<input type="text" name="remark" id="remark" class="form-control remark" placeholder="Additional qualification" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">

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

<script>
$(".addresourceBtn").css("display","");

$('.addToListBtn').on('click', function(e) {
	e.preventDefault();
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
				$("#qualification").val("");
				if(document.getElementById("categoryid").value==1)
				$("#remunerationid").val("");
				if(document.getElementById("categoryid").value==2)
				{
					$("#remunerationid").empty();
					$("#remunerationid").append("<option value=''>Position/Experience Level</option>");
				}
				$("#employmenttype").val("FULL TIME");
				$("#duration").val(document.getElementById("projectduration").value);
				$("#budget").val("");
				$("#remark").val("");
				$("#qualification").val("");
				$('.eoiresources').html(response.formhtml);
				$('.alert-msg').css("display","");
				$('.alert-msg').html("<i class='fa fa-thumbs-up'></i> Resource detail addedd successfully!");
				setTimeout(function() { $('.alert-msg').css("display","none"); $('.alert-msg').html(""); },3000);
				$("#projecttitle").prop("disabled","disabled");
				$("#projectduration").prop("disabled","disabled");
				$("#categoryid").prop("disabled","disabled");
				//$('#frm')[0].reset(); // Optional: Reset form
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

</script>





