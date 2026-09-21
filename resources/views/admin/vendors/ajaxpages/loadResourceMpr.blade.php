
@php
$j=1;
@endphp
<table class="mytable pd-5 font-12" border="1">

    <tbody>

		@php
		$total_days	=	0;
		$count_days	=	0;
		$holi_day	=	0;
		$work_month	=	'';
		@endphp
        @foreach($attendance as $att)
		
		
		@if(($resource->lastdate==NULL && $att->work_date <= $order->workorderduedate) || ($att->work_date <= $resource->lastdate || $att->work_date <= $order->workorderduedate))
		
		@if($loop->first)
		@php
		$work_month	=	date('F', mktime(0, 0, 0, $att->work_month, 1));
		@endphp
		@endif
		@if($att->status!='NA')
			@if($vendor->categoryid==0)
            <tr class="form-label">
				<td class="center v-middle" nowrap>{{ $att->work_day }}</td>
				<td class="center v-middle" nowrap>{{ $att->day_name }}</td>
				<td class="center v-middle">
				@if($att->status!='NA')
				<input type="text" name="day_{{$att->work_day}}" class="cal_day" value="{{ $att->status }}" @if($att->status=='H') readonly @endif maxlength="1" autocomplete="off">
				@php
				$count_days	=	$count_days+1;
				@endphp
				@else
				<input type="text" name="{{$att->work_day}}" class="cal_day_default" value="" readonly maxlength="1" autocomplete="off">
				@endif
				
				@php
				$total_days	=	$total_days+1;
				@endphp
				</td>
				<td>
				@if($att->status!='NA')
				<input type="text" name="remark_{{$att->work_day}}" class="cal_remark" value="{{ $att->mpr_detail }}" autocomplete="off" placeholder="work detail" style="width:100%;">
				@else
				<input type="text" name="{{$att->work_day}}" class="cal_day_remark" value="" readonly autocomplete="off" placeholder="work detail" style="width:100%;">
				@endif		
				</td>
            </tr>
			@else
				@if($att->status!='NA')
					@php
						$count_days	=	$count_days+1;
					@endphp
				@endif
				@if(trim($att->status)=='H')
					@php
						$holi_day	=	$holi_day+1;
					@endphp
				@endif	
				
				@php
					$total_days	=	$total_days+1;
				@endphp
				
			@endif
		@endif
		@endif
        @endforeach
	
    </tbody>
</table>

<table style="width:100%;">
<tr>
<td style="width:60%;" class="v-top padding-10">
<table class="mytable pd-5 font-14" border="1">
	@if($vendor->categoryid==2)
	<tr>
		<td class="v-top form-label">Sector</td>
		<td class="v-top">{{ucwords(strtolower($resource->sectorname))}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Position</td>
		<td class="v-top">
			{{$resource->consultantposition}} {{$resource->experience}}
		</td>
	</tr>
	@endif
	@if($vendor->categoryid==1)
	<tr>
		<td class="v-top form-label">Role</td>
		<td class="v-top">{{$resource->role}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Experience Level</td>
		<td class="v-top"> {{$resource->awdexperience}} [Level - {{$resource->experiencelevel}}]</td>
	</tr>
	@endif
	<tr>
		<td class="v-top form-label">Resource Name</td>
		<td class="v-top">{{$resource->name}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Mobile Number</td>
		<td class="v-top">{{$resource->mobilenumber}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Email</td>
		<td class="v-top">{{$resource->email}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Joining Date</td>
		<td class="v-top">@if($resource->deployed_date){{date('d\-m\-Y',strtotime($resource->deployed_date))}}@endif</td>
	</tr>
	<tr>
		<td class="v-top form-label">Release Date</td>
		<td class="v-top">@if($resource->lastdate){{date('d\-m\-Y',strtotime($resource->lastdate))}}@endif</td>
	</tr>
	<tr>
		<td class="v-top form-label">Deployment Type</td>
		<td class="v-top">{{$resource->deploymenttype}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Man Month Rate</td>
		<td class="v-top">{{$resource->remuneration}} [Man Month Per Day Rate {{$salary=floor((str_replace(",","",$resource->remuneration)/$resource->total_days)*100)/100}}]</td>
	</tr>
	<tr>
		<td class="v-top form-label">Work Order Due Date</td>
		<td class="v-top">{{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Countable Days for This Month</td>
		<td class="v-top">{{$count_days}} Days</td>
	</tr>
	<!--
	<tr>
		<td class="v-top form-label">Tax ({{$resource->tax}})% (B)</td>
		<td class="v-top">{{$resource->taxvalue}}</td>
	</tr>
	<tr>
		<td class="v-top form-label">Total Cost (per month) (A+B)</td>
		<td class="v-top">{{$resource->grandtotal}}</td>
	</tr>
	-->
</table>
</td>
<td style="width:60%;" class="v-top padding-10">
<table class="mytable pd-5 font-14">
	<tr class="form-label">
		<td class="center">Month Days</td>
		<td class="center">Present</td>
		<td class="center">Absent</td>
		<td class="center">Leave</td>
		<td class="center">Holiday</td>
	</tr>
	<tr class="form-label">
		<td class="center">
			<input type="text" name="total_days" id="total_days" readonly value="{{$resource->total_days}}" class="width-50 center">
			<input type="hidden" name="count_days" id="count_days" readonly value="{{$count_days}}" class="width-50 center">
			<input type="hidden" name="per_day_salary" id="per_day_salary" value="{{$salary}}" class="width-50 center">
			<input type="hidden" name="tax" id="tax" value="{{$resource->tax}}" class="width-50 center">
		</td>
		<td class="center">
			<input type="text" name="presents" id="presents" value="{{$resource->total_present}}" placeholder="0" class="numbers width-50 center" autocomplete="off" onchange="CalculateSalary()">
		</td>
		<td class="center">
			<input type="text" name="absents" id="absents" value="{{$resource->total_absent}}" placeholder="0" class="numbers width-50 center" autocomplete="off" onchange="CalculateSalary()">
		</td>
		<td class="center">
			<input type="text" name="leaves" id="leaves" value="{{$resource->total_leave}}" placeholder="0" class="numbers width-50 center" autocomplete="off" onchange="CalculateSalary()">
		</td>
		<td class="center">
			<input type="text" name="holidays" id="holidays" value="{{$resource->total_holiday ?? $holi_day}}" placeholder="0" class="numbers width-50 center" autocomplete="off" readonly>
		</td>
	</tr>
</table>

<table class="mytable pd-5 mt-20 font-14">
	<tr class="form-label myheadbg">
		<td class="form-label" colspan="2"><b>Salary for the Month of {{$work_month}}</b></td>
	</tr>
	<tr>
		<td class="form-label">Engagement (%)</td>
		<td><input type="text" placeholder="0" class="numbers" name="engagement" value="100" id="engagement" style="width:100%" onchange="CalculateSalary()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$j++}}" @if($resource->deploymenttype=='FULL TIME') readonly @endif></td>
	</tr>
	<tr>
		<td class="form-label">Calculated Salary</td>
		<td><input type="text" placeholder="0" class="numbers" name="calculated_salary" id="calculated_salary" readonly style="width:100%" tabindex="{{$j++}}"></td>
	</tr>
	<tr>
		<td class="form-label">Attendance File @if($resource->attendance_file) <a href="{{route('view.uploadedfile',Crypt::encrypt($resource->attendance_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a> @endif</td>
		<td><input type="file" class="form-control attendancefile" name="attendancefile" id="attendancefile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" /></td>
	</tr>
	@if($vendor->categoryid!=2)
	<tr>
		<td class="form-label">MPR File  @if($resource->mpr_file) <a href="{{route('view.uploadedfile',Crypt::encrypt($resource->mpr_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a> @endif</td>
		<td><input type="file" class="form-control mprfile" name="mprfile" id="mprfile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" /></td>
	</tr>
	@endif
	<tr>
		<td class="form-label">Supporting Document  @if($resource->supporting_file) <a href="{{route('view.uploadedfile',Crypt::encrypt($resource->supporting_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a> @endif</td>
		<td><input type="file" class="form-control supportingfile" name="supportingfile" id="supportingfile" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" /></td>
	</tr>
	<tr>
		<td class="form-label v-top">Remark (if any)</td>
		<td><textarea class="form-control" name="mpr_remark" id="mpr_remark" placeholder="Remark.." onKeyPress="return OnKeyPress(this, event)">@if($resource->mpr_remark){{$resource->mpr_remark}}@endif</textarea></td>
	</tr>
</table>

</td>
</tr>
</table>
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


if ($('.backBtn').length) {
    $('.backBtn').hide();
}

var month	=	"{{ \Carbon\Carbon::create()->month($month)->format('F') }}";
var year	=	"{{$year}}";

@if($vendor->categoryid==1)
$(".modal-title").html(
    '<i class="fa fa-calendar"></i> Mark Attendance For '+month+'-'+year
);
@else
$(".modal-title").html(
    '<i class="fa fa-calendar"></i> Enter the working days details and upload supporting documents for ' + month +' - '+ year
);
@endif

$(".modal-footer").append(
  '<button type="button" class="btn btn-info goAheadBtn gridbtn" style="width:80px;" onclick="loadResources(\'{{ route('mpr.resources') }}\', \'{{ Crypt::encrypt($order->orderid) }}\', \'{{ $mprid }}\', \'{{ $month }}\', \'{{ $year }}\')">' +
    '<i class="fa fa-arrow-left"></i> Back' +
  '</button>'
);

$(".modal-footer").append(
  '<button type="button" class="btn btn-info goAheadBtn gridbtn submitAcn" style="width:90px;" onclick="storeMprAttendance(\'{{ route('store.mprattendance') }}\', \'{{ Crypt::encrypt($order->orderid) }}\', \'{{ $mprid }}\', \'{{ $month }}\', \'{{ $year }}\', \'{{ Crypt::encrypt($resource->deploymentid) }}\')">' +
    '<i class="fa fa-save"></i> Submit' +
  '</button>'
);

$(".modal-footer").append(
  '<span class="mark_attendance" style="float:left;"><span class="attend">P</span> Present <span class="attend">A</span> Absent <span class="attend">L</span> Leave <span class="attend">H</span> Holiday</span>'
);


const allowed = ['P', 'A', 'L', 'H'];

CalculateSalary();

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
	if(engagement>100)
	{
		document.getElementById('engagement').value=100;
		bootbox.alert('Engagement % cannot be more than 100%.');
		return false;
	}
	var	countable		=	p+l+h;
	var calculated		=	Math.round(countable*per_day_salary);
	
	calculated	=	Math.round((calculated*engagement)/100).toFixed(2);
	
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
		document.getElementById('calculated_salary').value=calculated;
		CalculateSalary();
		return false;
	}
	if(countable>0)
	{
		document.getElementById('calculated_salary').value=Math.round(calculated).toFixed(2);
		if(countable==total_days)
		{
			calculated	=	{{str_replace(",","",$resource->remuneration)}};
			calculated	=	Math.round((calculated*engagement)/100).toFixed(2);
			document.getElementById('calculated_salary').value=calculated;
		}
	}
}

const allInputs = Array.from(document.querySelectorAll('.cal_day, .cal_remark'));

document.querySelectorAll('.cal_day').forEach((input) => {

    input.addEventListener('input', () => {
        input.value = input.value.toUpperCase();

        if (!allowed.includes(input.value) && input.value !== '') {
            input.value = '';
            return;
        }

        if (input.value !== '') {
            // Extract work_day number from name="day_X"
            let workDay = input.name.replace("day_", "");

            // Move to remark for same day
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

document.querySelectorAll('.cal_remark').forEach((input) => {

    input.addEventListener('keydown', (e) => {
        if (e.key === "Enter") {
            e.preventDefault();

            // Find this remark in the combined list
            let idx = allInputs.indexOf(input);

            // Move to next cal_day or remark
            if (allInputs[idx + 1]) {
                allInputs[idx + 1].focus();
            }
        }
    });

});
function storeMprAttendance(rl,orderid,mprid,month,year,deploymentid)
{
	
	$(".submitAcn").prop("disabled","disabled");
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			let isValid = true;
			const allowedValues = ['A', 'P', 'L', 'H'];
			$('.cal_day').each(function() {
				const val = $.trim($(this).val().toUpperCase()); // normalize case

				if(val === '' || !allowedValues.includes(val))
				{
					isValid = false;
					$(this).css('border', '2px solid red'); // highlight invalid
				}
				else
				{
					$(this).css('border','');
				}
			});
			
			if (!isValid)
			{
				$(".submitAcn").prop("disabled","");
				bootbox.alert('Please enter valid attendance values (A, P, L, H) for all fields and work detail');
				return false;
			}
			var form = $('#storeattendance')[0];
			var formData = new FormData(form);
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			formData.append('orderid',orderid);
			formData.append('mprid',mprid);
			formData.append('month',month);
			formData.append('year',year);
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
						$(".goAheadBtn").prop("disabled","disabled");
						$('#paymentContent').html('<div style="width:100%; text-align:center;"><h2><i class="fa fa-thumbs-up"></i><br><br>Attendance detail stored successfully!</h2></div>');
						
						setTimeout(function(){
							$.get('{{route('mpr.resources')}}',
							{
								orderid:orderid,
								mprid:response.mprid,
								month:month,
								year:year,
							},
							function(data, status){
								if(data.status==400)
								{
									bootbox.alert(data.message);
								}
								else
								{
									$('#paymentContent').html(data.data);
								}
							});					
						},3000);
					}
					else
					{
						$(".submitAcn").prop("disabled","");
						bootbox.alert(response.message);
						return false;
					}	
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					$(".submitAcn").prop("disabled","");
					setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
				}
			});	
			
		}
		else
		{
			$(".submitAcn").prop("disabled","");
		}
	});
	
}

$('.mprfile').ace_file_input({
	no_file:'No File ...',
	btn_choose:'MPR File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

$('.attendancefile').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Attendance File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});
$('.supportingfile').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Supporting File (.pdf)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

</script>