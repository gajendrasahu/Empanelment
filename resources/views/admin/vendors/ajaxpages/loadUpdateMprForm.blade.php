<table class="mytable pd-8" border="1">
	<tr class="myheadbg">
		<td colspan="3"><b>Upload Merged Signed Documents for All Resources (Once uploaded, this action cannot be reversed.</b></td>
	</tr>
	<tr>
		<td class="width-200">Upload MPR</td>
		<td class="width-400">
			<input type="hidden" name="mpr_id" id="mpr_id" value="{{$mprs->mprid}}">
			<input type="hidden" name="_token" id="csrf_token" value="{{ csrf_token() }}">
			@if(!$mprs->signed_mpr)
			<input type="file" class="form-control signed_mpr" name="signed_mpr" id="signed_mpr" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" style="text-align:left;" />
			@else
				<i class="fa fa-check"></i> Uploaded
			@endif
		</td>
		<td>
			@if(!$mprs->signed_mpr)
			<button type="button" class="btn btn-info gridbtn width-100" onclick="uploadDocument('mpr')"><i class="fa fa-upload"></i> Upload</button>
			@else
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->signed_mpr))}}" target="_blank">
			<button type="button" class="btn btn-info gridbtn width-100">
				<i class="fa fa-download"></i> View
			</button>
			</a>
			@endif
		</td>
	</tr>
	<tr>
		<td>Upload Attendance</td>
		<td>
			@if(!$mprs->signed_attendance)
			<input type="file" class="form-control signed_attendance" name="signed_attendance" id="signed_attendance" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" style="text-align:left;" />
			@else
				<i class="fa fa-check"></i> Uploaded
			@endif
		</td>
		<td>
			@if(!$mprs->signed_attendance)
			<button type="button" class="btn btn-info gridbtn width-100" onclick="uploadDocument('attendance')"><i class="fa fa-upload"></i> Upload</button>
			@else
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->signed_attendance))}}" target="_blank">
			<button type="button" class="btn btn-info gridbtn width-100">
				<i class="fa fa-download"></i> View
			</button>
			</a>
			@endif
		</td>
	</tr>
	<tr>
		<td>Upload Supporting Documents</td>
		<td>
			@if(!$mprs->signed_supporting)
			<input type="file" class="form-control signed_supporting" name="signed_supporting" id="signed_supporting" accept="application/pdf" onKeyPress="return OnKeyPress(this, event)" style="text-align:left;" />
			@else
				<i class="fa fa-check"></i> Uploaded
			@endif
		</td>
		<td>
			@if(!$mprs->signed_supporting)
			<button type="button" class="btn btn-info gridbtn width-100" onclick="uploadDocument('supporting')"><i class="fa fa-upload"></i> Upload</button>
			@else
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->signed_supporting))}}" target="_blank">
			<button type="button" class="btn btn-info gridbtn width-100">
				<i class="fa fa-download"></i> View
			</button>
			</a>
			@endif
		</td>
	</tr>
</table>

<table class="mytable pd-8" border="1">
	<tr class="myheadbg">
		<td colspan="4"></td>
		<td colspan="2" class="text-center"><b>Amount</b></td>
		<td colspan="3" class="text-center"><b>Submitted Grouped Documents</b></td>
	</tr>
	<tr class="myheadbg">
		<td nowrap><b>Project Name</b></td>
		<td nowrap class="width-100"><b>Submission Date</b></td>
		<td nowrap class="width-100 center"><b>MPR Number</b></td>
		<td nowrap class="width-100 center"><b>MPR Month</b></td>
		<td nowrap class="width-100 text-center"><b>Submitted</b></td>
		<td nowrap class="width-100 text-center"><b>Approved</b></td>
		<td nowrap class="text-center width-30"><b>MPR</b></td>
		<td nowrap class="text-center width-30"><b>Attendance</b></td>
		<td nowrap class="text-center width-30"><b>Supporting</b></td>
	</tr>
	<tr>
		<td>@if($mprs->project_name) {{$mprs->project_name}} @else {{$mprs->engagementname}} @endif</td>
		<td class="text-center">{{date('d\-m\-Y',strtotime($mprs->submission_date))}}</td>
		<td class="text-center">{{$mprs->mpr_number}}</td>
		<td class="text-center">{{ date('F', mktime(0, 0, 0, $mprs->mpr_month, 1)) }}-{{$mprs->mpr_year}}</td>
		<td class="text-center">{{$mprs->mpr_value}}</td>
		<td class="text-center">{{$mprs->mpr_approved_value}}</td>
		<td class="text-center">
			@if($mprs->mpr_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->mpr_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
			@endif
		</td>
		<td class="text-center">
			@if($mprs->attendance_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->attendance_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
			@endif
		</td>
		<td class="text-center">
			@if($mprs->supporting_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($mprs->supporting_file))}}" target="_blank"><i class="fa fa-file-pdf-o"></i></a>
			@endif
		</td>
	</tr>
</table>
<table class="mytable pd-8" border="1">
	<tr class="myheadbg">
		<td colspan="11"><b>Attendance Summary For Resources (P - Present, A - Absent, L - Leave, H - Holiday)</b></td>
		<td colspan="3" class="text-center"><b>Documents</b></td>
		<td nowrap class="text-center"></td>
	</tr>
	<tr class="myheadbg">
		@if($mprs->categoryid==2)
		<td nowrap class="width-100"><b>Resource & Position</b></td>
		@else
		<td nowrap class="width-100"><b>Resource & Role</b></td>
		@endif
		<td nowrap class="width-100 text-center"><b>Joining Date</b></td>
		<td nowrap class="text-center"><b>P</b></td>
		<td nowrap class="text-center"><b>A</b></td>
		<td nowrap class="text-center"><b>L</b></td>
		<td nowrap class="text-center"><b>H</b></td>
		<td nowrap class="text-right"><b>Per Day Cost</b></td>
		<td nowrap class="text-right"><b>Actual Salary</b></td>
		<td nowrap class="text-right"><b>Submitted Salary</b></td>
		<td nowrap class="text-right"><b>Other Deduction</b></td>
		<td nowrap class="text-right"><b>Approved Salary</b></td>
		<td nowrap class="text-center"><b>MPR</b></td>
		<td nowrap class="text-center"><b>Att.</b></td>
		<td nowrap class="text-center"><b>Supp.</b></td>
		<td nowrap class="text-center"></td>
	</tr>
	@php
	$actual		=	0;
	$calculated	=	0;
	$deduction	=	0;
	$approved	=	0;
	$ind		=	0;
	@endphp
	@foreach($mprs->summary as $resource)
	@php
	$ind++;
	@endphp
	<tr>
		@if($mprs->categoryid==2)
		<td nowrap><b>{{$resource->name}}</b><br>{{ucwords(strtolower($resource->sectorname))}}<br>{{$resource->consultantposition}}</td>
		@else
		<td nowrap><b>{{$resource->name}}</b><br>{{$resource->role}}<br>Level-{{$resource->experiencelevel}}</td>
		@endif
		<td nowrap class="text-center">{{date('d\-m\-Y',strtotime($resource->deployed_date))}}</td>
		<td nowrap class="text-center">{{$resource->total_present}}</td>
		<td nowrap class="text-center">{{$resource->total_absent}}</td>
		<td nowrap class="text-center">{{$resource->total_leave}}</td>
		<td nowrap class="text-center">{{$resource->total_holiday}}</td>
		<td nowrap class="text-right">{{$resource->per_day_cost}}</td>
		<td nowrap class="text-right format-indian" data-value="{{number_format($resource->actual_salary,'2','.','')}}"></td>
		<td nowrap class="text-right format-indian" data-value="{{number_format($resource->calculated_salary,'2','.','')}}"></td>
		<td nowrap class="text-right format-indian" data-value="{{number_format($resource->deduction,'2','.','')}}"></td>
		<td nowrap class="text-right format-indian" data-value="{{number_format($resource->approved_salary,'2','.','')}}">
		</td>
		<td nowrap class="text-center">
			@if($resource->mpr_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($resource->mpr_file))}}" target="_blank">
					<i class="fa fa-file-pdf-o"></i>
				</a>
			@else
				<i class="fa fa-remove"></i> 
			@endif
		</td>
		<td nowrap class="text-center">
			@if($resource->attendance_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($resource->attendance_file))}}" target="_blank">
					<i class="fa fa-file-pdf-o"></i>
				</a> 
			@else 
				<i class="fa fa-remove"></i>
			@endif
		</td>
		<td nowrap class="text-center">
			@if($resource->supporting_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($resource->supporting_file))}}" target="_blank">
					<i class="fa fa-file-pdf-o"></i>
				</a>
			@else
				<i class="fa fa-remove"></i>
			@endif
		</td>
		<td nowrap class="text-center" @if($resource->isFlaged==1) onclick="viewMprUpdates('{{route('view.mprupdates',Crypt::encrypt($resource->summary_id))}}',{{$ind}})" @endif>
			<i class="fa fa-angle-double-down" ></i>
		</td>
	</tr>
	<tr class="trData{{$ind}} rowData" style="display:none;">
		<td colspan="14" class="tdData{{$ind}}" style="padding:0px!important;"></td>
	</tr>
	@php
		$actual		=	$actual+$resource->actual_salary;
		$calculated	=	$calculated+$resource->calculated_salary;
		$deduction	=	$deduction+($resource->deduction);
		$approved	=	$approved+$resource->approved_salary;
	@endphp
	@endforeach
	<tr>
		<td class="text-right" colspan="7"><b>Total</b></td>
		<td class="text-right format-indian" data-value="{{number_format($actual,'2','.','')}}"></td>
		<td class="text-right format-indian" data-value="{{number_format($calculated,'2','.','')}}"></td>
		<td class="text-right format-indian" data-value="{{number_format($deduction,'2','.','')}}"></td>
		<td class="text-right format-indian" data-value="{{number_format($approved,'2','.','')}}"></td>
		<td colspan="4"></td>
	</tr>
</table>
<script>
$(".invoice-title").html(
    '<i class="fa fa-calendar-check-o"></i> Attendance & MPR Details for Each Resource Under Work Order Number: <b>{{$mprs->ordernumber}}</b>'
);
$('.invoice-footer .selected-mprs-value').remove();
$('.invoice-footer .generateInvoice').remove();

$('.btn-close').on('click', function (e)
{
	$('#invoiceContent').html('');
	$('#invoiceHistory').modal('hide');
});

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

$(".invoice-footer").append(
  '<button type="button" class="btn btn-info generateInvoice gridbtn" style="width:100px;" onclick="loadPendingInvoices(\'{{ route('pending.invoices') }}\', \'{{ Crypt::encrypt($orderid) }}\')">' +
    '<i class="fa fa-angle-left"></i> Back' +
  '</button>'
);



function hideRow()
{
	$(".rowData").css("display","none");
}

function viewMprUpdates(r1,ind)
{
	$(".rowData").css("display","none");
	$.get(""+r1,
	{
		ind:ind
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
			$('.trData'+ind).css("display","");
			$('.tdData'+ind).html(data.data);

			document.querySelector('.trData' + ind).scrollIntoView({
				behavior: 'smooth',
				block: 'start'
			});
		}
	});
}
$('.signed_mpr').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Upload Signed MPR*',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

$('.signed_attendance').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Upload Signed Attendance*',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});

$('.signed_supporting').ace_file_input({
	no_file:'No File ...',
	btn_choose:'Upload Supporting Document (optional)',
	btn_change:'Change',
	btn_name:'fourthimage',
	thumbnail:false //| true | large
});


function uploadDocument(type)
{
    let inputId = '';
    let fieldName = '';

    switch(type)
    {
        case 'mpr':
            inputId = '#signed_mpr';
            fieldName = 'signed_mpr';
            break;

        case 'attendance':
            inputId = '#signed_attendance';
            fieldName = 'signed_attendance';
            break;

        case 'supporting':
            inputId = '#signed_supporting';
            fieldName = 'signed_supporting';
            break;
    }

    var file = $(inputId)[0].files[0];

    if(typeof file === 'undefined')
    {
        bootbox.alert('Please select a PDF file to upload.');
        return;
    }
    var formData = new FormData();
	formData.append('_token', $('#csrf_token').val());
	formData.append('mpr_id', $('#mpr_id').val());
    formData.append('upload_type', type);
    formData.append(fieldName, file);

    $.ajax({
        url: "{{ route('upload.singed.documents') }}",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response)
        {
            bootbox.alert(response.message);
			setTimeout(function() { viewMprDetail('{{route('uploadmpr.document')}}',response.mpr_id); } ,2000);
        },
		error: function(xhr)
		{
			if (xhr.status === 422)
			{
				var errors = xhr.responseJSON.errors;

				var firstError = '';
				$.each(errors, function(key, value) {
					firstError = value[0];
					return false; // break loop
				});

				bootbox.alert(firstError);
			}
			else
			{
				bootbox.alert('Something went wrong. Please try again.');
			}
		}
    });
}
</script>