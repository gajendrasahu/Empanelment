<style>
.ace-file-input .ace-file-container .ace-file-name
{
	max-width:200%!important;
}
</style>
<form name="approvempr" id="approvempr" action="#" method="post" enctype="multipart/form-data">
@if($attendance->count()>0)
<table class="mytable pd-8" border="1">
	<tr class="myheadbg form-label">
		<td class="center width-30">Date</td>		
		<td class="center width-30">Day</td>
		<td class="center width-50">Status</td>
		<td class="width-300">Work Detail</td>
		<td class="width-300">Department Remark</td>
	</tr>
	@foreach($attendance as $att)
	<tr>
		<td class="center form-label">{{date('d',strtotime($att->work_date))}}</td>
		<td class="center form-label">{{date('D',strtotime($att->work_date))}}</td>
		<td class="center">
			<input type="text" name="day_{{$att->attendance_id}}" id="" value="{{$att->status}}" class="cal_day form-label" @if($att->status=='H') readonly @endif>
		</td>
		<td class="center">
			<input type="text" name="remark_{{$att->attendance_id}}" id="" value="{{$att->mpr_detail}}" class="form-label font-normal" style="width:100%;" placeholder="work detail" readonly>
		</td>
		<td class="center">
			<input type="text" name="update_{{$att->attendance_id}}" id="" value="{{$att->remarks}}" class="form-label font-normal" style="width:100%;" placeholder="Department remark (if any)">
		</td>
	</tr>
	@endforeach
</table>
@endif
<table class="mytable pd-5 font-14 mt-20">
	<tr>
		<td style="width:40%; text-align:left;" class="v-top">
			<table class="mytable pd-5 font-14">
				<tr class="form-label">
					<td class="center">Total Days</td>
					<td class="center">P</td>
					<td class="center">A</td>
					<td class="center">L</td>
					<td class="center">H</td>
				</tr>
				@if($vendor->categoryid==1)
				<tr class="form-label">
					<td class="center">
						<input type="text" name="total_days" id="total_days" readonly value="{{$summary->total_working_days}}" class="width-50 center">
						<input type="hidden" name="count_days" id="count_days" readonly value="{{$summary->count_days}}" class="width-50 center">
						<input type="hidden" name="per_day_salary" id="per_day_salary" value="{{$summary->per_day_cost}}" class="width-50 center">
						<input type="hidden" name="tax" id="tax" value="0" class="width-50 center">
					</td>
					<td class="center">
						<input type="text" name="presents" id="presents" readonly value="{{$summary->total_present}}" placeholder="0" class="width-50 center">
					</td>
					<td class="center">
						<input type="text" name="absents" id="absents" readonly value="{{$summary->total_absent}}" placeholder="0" class="width-50 center">
					</td>
					<td class="center">
						<input type="text" name="leaves" id="leaves" readonly value="{{$summary->total_leave}}" placeholder="0" class="width-50 center">
					</td>
					<td class="center">
						<input type="text" name="holidays" id="holidays" readonly value="{{$summary->total_holiday}}" placeholder="0" class="width-50 center">
					</td>
				</tr>
				@else
				<tr class="form-label">
					<td class="center">
						<input type="text" name="total_days" id="total_days" readonly value="{{$summary->total_working_days}}" class="width-50 center">
						<input type="hidden" name="count_days" id="count_days" readonly value="{{$summary->count_days}}" class="width-50 center">
						<input type="hidden" name="per_day_salary" id="per_day_salary" value="{{$summary->per_day_cost}}" class="width-50 center">
						<input type="hidden" name="tax" id="tax" value="0" class="width-50 center">
					</td>
					<td class="center">
						<input type="text" name="presents" id="presents" value="{{$summary->total_present}}" placeholder="0" class="width-50 center" onchange="CalculateSalary()" autocomplete="off">
					</td>
					<td class="center">
						<input type="text" name="absents" id="absents" value="{{$summary->total_absent}}" placeholder="0" class="width-50 center" onchange="CalculateSalary()" autocomplete="off">
					</td>
					<td class="center">
						<input type="text" name="leaves" id="leaves" value="{{$summary->total_leave}}" placeholder="0" class="width-50 center" onchange="CalculateSalary()" autocomplete="off">
					</td>
					<td class="center">
						<input type="text" name="holidays" id="holidays" value="{{$summary->total_holiday}}" placeholder="0" class="width-50 center" onchange="CalculateSalary()" autocomplete="off">
					</td>
				</tr>
				@endif
			</table>
			<table class="mytable pd-5 font-14 mt-20">
				<tr class="form-label myheadbg">
					<td class="form-label" colspan="2">Salary for the Month of {{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{$mpr->mpr_year}}</td>
				</tr>
				<tr>
					<td class="form-label">Engagement (%)</td>
					<td style="width:150px;"><input type="text" class="numbers" value="{{$summary->engagement}}" name="engagement" id="engagement" readonly style="width:100%;"></td>
				</tr>
				<tr>
					<td class="form-label">Submitted Salary</td>
					<td style="width:150px;"><input type="text" class="numbers" value="{{round($summary->net_salary)}}" name="net_salary" id="net_salary" readonly style="width:100%;"></td>
				</tr>
				<tr>
					<td class="form-label">Approved Salary</td>
					<td><input type="text" class="numbers" value="@if($summary->approved_salary){{$summary->approved_salary}}@else {{$summary->net_salary}} @endif" name="calculated_salary" id="calculated_salary" readonly style="width:100%;"></td>
				</tr>
			</table>
		</td>
		<td style="width:60%; text-align:right;" class="v-top">
			<table class="mytable pd-5 font-14" border="1" id="deductionTable">
				<tr class="form-label myheadbg">
					<td class="form-label" colspan="2" style="text-align:left!important;">Deduction Details (if any)</td>
				</tr>
				<tr>
					<td nowrap class="v-top" style="text-align:left!important;">Remark</td>
					<td>
					<textarea name="particular" id="particular" placeholder="Particular" autocomplete="off" cols="70" rows="5">{{$summary->remarks}}</textarea>
					</td>
				</tr>
				<tr>
					<td class="text-left">Attachment (if any) @if($summary->deduction_file) <a href="{{route('view.uploadedfile',Crypt::encrypt($summary->deduction_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a> @endif</td>
					<td style="text-align:left;">
						<input type="file" class="form-control deduction_file" name="deduction_file" id="deduction_file" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" style="text-align:left;" />
					</td>
				</tr>
				<tr>
					<td nowrap class="v-top" style="text-align:left!important;">Deduction Value</td>
					<td class="width-100 text-left">
						<input type="text" class="numbers width-100" name="deduction_value" id="deduction_value" placeholder="0.0" autocomplete="off" onchange="CalculateSalary()" value="{{$summary->deduction}}">
					</td>
				</tr>
			</table>
		</td>
	</tr>
</table>
</form>
<script>
$(".numbers").on("keypress", function(event) {
    var inputValue = event.key;
    var currentValue = $(this).val();
    
    // Allow "0" as a valid input unless it's already part of a larger number like "00"
    if ((currentValue === "" || currentValue === "0") && inputValue === "0") {
        event.preventDefault();
        return;
    }

    // Ensure only valid numeric input and allow a single decimal point
    var isValid = /^\d$/.test(inputValue) || (inputValue === '.' && currentValue.indexOf('.') === -1);
    
    if (!isValid) {
        event.preventDefault();
    }
});

$('.deduction_file').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Attachment (if any)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});


$(".modal-title").html(
    '<i class="fa fa-calendar"></i> Attendance & MPR Detail for {{$resource->name}} for the month of {{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{$mpr->mpr_year}}'
);

$(".modal-footer").html("");

$(".modal-footer").append(
  '<span class="mark_attendance" style="float:left;"><span class="attend">P</span> Present <span class="attend">A</span> Absent <span class="attend">L</span> Leave <span class="attend">H</span> Holiday</span>'
);

$(".modal-footer").append(
  '<button type="button" class="btn btn-info goAheadBtn gridbtn width-100" onclick="viewMprDepartment(\'{{ route('view.mprdepartment') }}\', \'{{ Crypt::encrypt($mpr->mpr_id) }}\')">' +
    '<i class="fa fa-arrow-left"></i> Back' +
  '</button>'
);



@if($mpr->mpr_status!='Verified')

/*	
$(".modal-footer").append(
  '<button type="button" class="btn btn-info goAheadBtn gridbtn width-100" onclick="updateMprAttendance(\'{{ route('update.mprattendance') }}\', \'{{ Crypt::encrypt($mpr->mpr_id) }}\', \'{{ Crypt::encrypt($summary->deployment_id) }}\')">' +
    '<i class="fa fa-refresh"></i> Update' +
  '</button>'
);
*/
@if($summary->status!='Approved')
$(".modal-footer").append(
  '<button type="button" class="btn btn-info goAheadBtn gridbtn width-100 aprBtn" onclick="approveResourceMpr(\'{{ route('approve.resourcempr') }}\', \'{{ Crypt::encrypt($summary->mpr_id) }}\', \'{{ Crypt::encrypt($summary->deployment_id) }}\')">' +
    '<i class="fa fa-save"></i> Approve' +
  '</button>'
);
@endif
@endif

$(".modal-footer").append('<button type="button" class="btn btn-info gridbtn btn-close width-100" data-bs-dismiss="modal"><i class="fa fa-remove"></i> Close</button>');


$('.btn-close').on('click', function (e)
{
	$('#mprContent').html('');
	$('#viewMpr').modal('hide');
});

const allowed = ['P', 'A', 'L', 'H'];
@if($vendor->categoryid==1)
updateCounts();
@else
CalculateSalary();
@endif

function updateCounts() {
    let counts = {P: 0, A: 0, L: 0, H: 0};
    
    document.querySelectorAll('.cal_day').forEach(input => {
        const val = input.value.toUpperCase();
        if (allowed.includes(val)) {
            counts[val]++;
        }
    });

    document.getElementById('presents').value = counts['P'];
    document.getElementById('absents').value = counts['A'];
    document.getElementById('leaves').value = counts['L'];
    document.getElementById('holidays').value = counts['H'];
	CalculateSalary();
}
function CalculateSalary()
{
	var p				=	Number(document.getElementById('presents').value);
	var a				=	Number(document.getElementById('absents').value);
	var l				=	Number(document.getElementById('leaves').value);
	var h				=	Number(document.getElementById('holidays').value);
	var tax				=	Number(document.getElementById('tax').value);
	var per_day_salary	=	Number(document.getElementById('per_day_salary').value);
	var engagement		=	Number(document.getElementById('engagement').value);
	var deduction_value	=	Number(document.getElementById('deduction_value').value);
	if(engagement>100)
	{
		document.getElementById('engagement').value=100;
		bootbox.alert('Engagement % cannot be more than 100%.');
		return false;
	}
	var	countable		=	p+l+h;
	var calculated		=	Math.round(countable*per_day_salary);
	calculated			=	Math.round((calculated*engagement)/100).toFixed(2);
	@if($vendor->categoryid==2)
		var total_days	=	Number(document.getElementById('total_days').value);
		var count_days	=	Number(document.getElementById('count_days').value);
		if((p+l+a+h)>count_days)
		{
			calculated	=	{{str_replace(",","",$resource->remuneration)}};
			calculated	=	Math.round((calculated*engagement)/100).toFixed(2);
			
			document.getElementById('presents').value="";
			document.getElementById('absents').value="";
			document.getElementById('leaves').value="";
			bootbox.alert("Total available days for the selected month is " + count_days + ". Please ensure that the sum of P, A, L, and H equals " + count_days + "");
			document.getElementById('calculated_salary').value=calculated-deduction_value;
			CalculateSalary();
			return false;
		}
		if(countable>0)
		{
			document.getElementById('calculated_salary').value=Math.round(calculated-deduction_value).toFixed(2);
			if(countable==total_days)
			{
				calculated	=	{{str_replace(",","",$resource->remuneration)}};
				calculated	=	Math.round((calculated*engagement)/100).toFixed(2);
				document.getElementById('calculated_salary').value=calculated-deduction_value;
			}
		}
	@else
		document.getElementById('calculated_salary').value=Math.round(calculated-deduction_value).toFixed(2);
	@endif
}


function approveResourceMpr(rl,mprid,deploymentid)
{
	$(".aprBtn").prop("disabled","disabled");
	
	let isValid = true;
	const allowedValues = ['A', 'P', 'L', 'H'];
    $('.cal_day').each(function() {
        const val = $.trim($(this).val().toUpperCase()); // normalize case

        if (val === '' || !allowedValues.includes(val)) {
            isValid = false;
            $(this).css('border', '2px solid red'); // highlight invalid
        } else {
            $(this).css('border', '');
        }
    });
	if (!isValid)
	{
		$(".aprBtn").prop("disabled","");
		bootbox.alert('Please enter valid attendance values (A, P, L, H) for all fields and work detail');
		return false;
	}
	
	var p					=	Number(document.getElementById('presents').value);
	var a					=	Number(document.getElementById('absents').value);
	var l					=	Number(document.getElementById('leaves').value);
	var h					=	Number(document.getElementById('holidays').value);
	var count_days			=	Number(document.getElementById('count_days').value);
	var net_salary			=	Number(document.getElementById('net_salary').value);
	var calculated_salary	=	Number(document.getElementById('calculated_salary').value);
	if(net_salary<calculated_salary)
	{
		$(".aprBtn").prop("disabled","");
		bootbox.alert("The approved salary cannot exceed the submitted salary. Please check and try again.");
		return false;
	}
	if((p+a+l+h)!=count_days)
	{
		$(".aprBtn").prop("disabled","");
		bootbox.alert("Total available days for the selected month is " + count_days + ". Please ensure that the sum of P, A, L, and H equals " + count_days + "");
		return false;
	}
	
	bootbox.confirm('Do you want to approve this attendance and MPR detail? Once approved, it cannot be reversed.',function(result){
		if(result)
		{
			var form = $('#approvempr')[0];
			var formData = new FormData(form);
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			formData.append('mprid',mprid);
			formData.append('deploymentid',deploymentid);
			$.ajax({
				url: ''+rl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status==200)
					{
						$('#mprContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>The submitted details have been approved successfully.</h2></div>');
						
						setTimeout(function(){
							$.get('{{route('view.mprdepartment')}}',
							{
								mprid:mprid,
							},
							function(data, status){
								if(data.status==400)
								{
									bootbox.alert(data.message);
								}
								else
								{
									$('#mprContent').html(data.data);
								}
							});					
						},3000);
					}
					else
					{
						$(".aprBtn").prop("disabled","");
					}
				},
				error: function(xhr) {
					$(".aprBtn").prop("disabled","");
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
				}
			});	
		}
	});
}


function updateMprAttendance(rl,mprid,deploymentid)
{
	let isValid = true;
	const allowedValues = ['A', 'P', 'L', 'H'];
    $('.cal_day').each(function() {
        const val = $.trim($(this).val().toUpperCase());

        if (val === '' || !allowedValues.includes(val)) {
            isValid = false;
            $(this).css('border','2px solid red');
        } else {
            $(this).css('border','');
        }
    });
	if (!isValid)
	{
		bootbox.alert('Please enter valid attendance values (A, P, L, H) for all fields and work detail');
		return false;
	}

	
	bootbox.confirm('Do you want to update this attendance and MPR detail?',function(result){
		if(result)
		{
			var form = $('#approvempr')[0];
			var formData = new FormData(form);
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			formData.append('mprid',mprid);
			formData.append('deploymentid',deploymentid);
			$.ajax({
				url: ''+rl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status==200)
					{
						$('#mprContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>Attendance and MPR details have been updated successfully. Please proceed to approve the attendance and MPR details.</h2></div>');
						
						setTimeout(function(){
							$.get('{{route('view.attendancemprdepartment')}}',
							{
								mprid:mprid,
								deploymentid:deploymentid
							},
							function(data, status){
								if(data.status==400)
								{
									bootbox.alert(data.message);
								}
								else if(data.status==500)
								{
									bootbox.alert(data.message);
								}
								else
								{
									$('#mprContent').html(data.data);
								}
							});
						},5000);

						
					}			
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
				}
			});	
		}
	});
}


document.querySelectorAll('.cal_day').forEach((input) => {

    input.addEventListener('input', () => {
        input.value = input.value.toUpperCase();

        if (!allowed.includes(input.value) && input.value !== '') {
            input.value = '';
            return;
        }

        if (input.value !== '') {
            let workDay = input.name.replace("day_", "");
            let remark = document.querySelector('input[name="remark_' + workDay + '"]');
            if (remark) remark.focus();
        }

        updateCounts();
    });

    input.addEventListener('keypress', (e) => {
        const key = e.key.toUpperCase();
        if (!allowed.includes(key)) e.preventDefault();
    });
});

</script>

<script>
function addRow() {
    const table = document.getElementById('deductionTable');
    const newRow = table.insertRow(-1);

    const cell1 = newRow.insertCell(0);
    cell1.innerHTML = '<input type="text" name="particular[]" placeholder="Particular" autocomplete="off" style="width:100%">';

    const cell2 = newRow.insertCell(1);
    cell2.innerHTML = '<input type="number" class="numbers width-100" name="deduction_on[]" placeholder="0.0" autocomplete="off">';

    const cell3 = newRow.insertCell(2);
    cell3.innerHTML = '<input type="number" class="numbers width-100" name="deduction_percentage[]" placeholder="in %" autocomplete="off">';

    const cell4 = newRow.insertCell(3);
    cell4.innerHTML = '<input type="number" class="numbers width-100" name="deduction_value[]" readonly placeholder="0.0" autocomplete="off">';

    const cell5 = newRow.insertCell(4);
    cell5.classList.add('center');
    cell5.innerHTML = '<button type="button" class="btn btn-info removeBtn">Remove</button>';
}

document.getElementById('addRowBtn').addEventListener('click', addRow);

document.getElementById('deductionTable').addEventListener('click', function(e) {
    if (e.target && e.target.classList.contains('removeBtn')) {

        const table = document.getElementById('deductionTable');

        const dataRows = Array.from(table.querySelectorAll('tr'))
            .filter(row => row.querySelector('input[name="particular[]"]'));

        if (dataRows.length > 1) {
            const row = e.target.closest('tr');
            row.remove();
        } else {
            bootbox.alert("At least one row must remain!");
        }
    }
});
</script>