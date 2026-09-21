@php
$t=1;

@endphp
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
				<select name="sectorid" id="sectorid" class="form-control sectorid" onchange="GetManagerExperience(this.value,'{{ route('pmexperiences.list') }}','positionid',document.getElementById('categoryid').value)" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
					<option value="">--Sector--</option>
					@foreach($sectors as $sector)
					<option value="{{$sector->sectorid}}">{{$sector->sectorname}}</option>
					@endforeach
				</select>
			</div>
			<div class="col-sm-2 padding-5">
				<span class="form-label">Position <label id="req">*</label></span>
				<select name="positionid" id="positionid" class="form-control" onchange="GetPmRemuneration(this.value,'{{ route('get.pmremuneration') }}','budget')" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
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
				$('.tableData').html(response.tableData);
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



</script>





