<div id="Tbldata" class="Tbldata">
<style>
.status-bds {
  position:absolute;
  right:5px;
  top:10px;
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  font-family: Arial, sans-serif;
}

.bds {
  display: inline-block;
  padding: 4px 10px;
  border-radius: 20px;
  font-size: 10px;
  font-weight: 600;
  color: #fff;
}

.bds-pending {
  background-color: #f59e0b;
}

.bds-active {
  background-color: #10b981;
}

.bds-extended {
  background-color: #3b82f6;
}

.bds-released {
  background-color: #ef4444;
}
.bds-success {
  background-color: green;
}

.bds-total {
  background-color: purple;
}

.bds-default {
  background-color:#eee;
  color:black;
}
</style>
<table class="mytable pd-8" border="1">
	<tr>
		<td colspan="12">
		<span>
			<span class="bds bds-active">P - Present</span>
			<span class="bds bds-extended">A - Absent</span>
			<span class="bds bds-released">L - Leave</span>
			<span class="bds bds-success">H - Holiday</span>
			<span class="bds bds-total">T.D. - Total Days (P+A+L+H)</span>
		</span>
		</td>
	</tr>
	<tr>
		<td colspan="12">
		@if($order->project_name) Project Name : {{$order->project_name}} | @endif Order Number : {{$order->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}}
		</td>
	</tr>
	<tr class="myheadbg form-label">
		<td class="center width-30 font-bold">S.No.</td>
		<td class="font-bold">Name</td>
		<td class="font-bold">@if($order->categoryid==1) Role @else Position @endif</td>
		<td class="center" nowrap><span class="bds bds-active">P</span></td>
		<td class="center" nowrap><span class="bds bds-extended">A</span></td>
		<td class="center" nowrap><span class="bds bds-released">L</span></td>
		<td class="center" nowrap><span class="bds bds-success">H</span></td>
		<td class="center" nowrap><span class="bds bds-total">T.D.</span></td>
		<td class="width-100 v-top font-bold" style="text-align:right;">Actual Salary</td>		
		<td style="text-align:right;" nowrap class="font-bold">Submitted</td>
		<td style="text-align:right;" nowrap class="width-120 font-bold">Approved</td>
		<td class="center font-bold" nowrap></td>
	</tr>
	@php
	$total			=	0;
	$totalSalary	=	0;
	$totalApproved	=	0;
	@endphp
	@foreach($data as $record)
	<tr>
		<td class="center v-top">{{$loop->iteration}}</td>
		<td class="v-top">
			<span class="form-label">{{$record->name}}</span>
			<div class="d-flex align-items-center" style="width:200px;">
				@if($record->mpr_file)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($record->mpr_file))}}" target="_blank" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-download"></i> MPR
						</span>
					</a>			
				@endif
				@if($record->attendance_file)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($record->attendance_file))}}" target="_blank" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-download"></i> Attendance
						</span>
					</a>			
				@endif
				@if($record->supporting_file)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($record->supporting_file))}}" target="_blank" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-download"></i> Other
						</span>
					</a>			
				@endif
			</div>
		</td>
		<td nowrap class="v-top">{{ucwords(strtolower($record->sectorname))}} - {{ucwords(strtolower($record->consultantposition))}}</td>
		<td class="center v-top">{{$record->total_present}}</td>
		<td class="center v-top">{{$record->total_absent}}</td>
		<td class="center v-top">{{$record->total_leave}}</td>
		<td class="center v-top">{{$record->total_holiday}}</td>
		<td class="center v-top">{{$record->total_present+$record->total_leave+$record->total_holiday}}</td>
		<td class="width-100 v-top format-indian font-bold form-label" style="text-align:right;" data-value="{{ number_format($record->remuneration,'2','.','') }}"></td>
		<td class="v-top format-indian form-label" style="text-align:right;" data-value="{{ number_format($record->net_salary,'2','.','') }}"></td>
		<td class="v-top format-indian form-label" style="text-align:right;" data-value="{{ number_format($record->approved_salary,'2','.','') }}"></td>
		<td class="center v-top">
		@if($record->status!='Approved')
		<span class="btn btn-info gridbtn" onclick="viewAttendanceMprDepartment('{{route('view.attendancemprdepartment')}}','{{Crypt::encrypt($record->mpr_id)}}','{{Crypt::encrypt($record->deployment_id)}}')">
			<i class="fa fa-calendar"></i> View
		</span>
		@endif
		@if($record->status=='Approved')
		<span class="btn btn-info gridbtn" onclick="viewAttendanceMprDepartment('{{route('view.attendancemprdepartment')}}','{{Crypt::encrypt($record->mpr_id)}}','{{Crypt::encrypt($record->deployment_id)}}')">
			<i class="fa fa-file-pdf-o"></i> Approved
		</span>		
		@endif
		
		</td>
	</tr>
	@php
	$total			=	$total+$record->remuneration;
	$totalSalary	=	$totalSalary+$record->net_salary;
	$totalApproved	=	$totalApproved+$record->approved_salary;
	@endphp
	@endforeach
	<tr class="font-14 font-bold">
		<td class="" colspan="8" style="text-align:right;">Total</td>
		<td class="width-100 format-indian font-bold" style="text-align:right;" data-value="{{ number_format($total,'2','.','') }}"></td>
		<td class="width-100 format-indian font-bold" style="text-align:right;" data-value="{{ number_format($totalSalary,'2','.','') }}"></td>
		<td class="width-100 format-indian font-bold" style="text-align:right;" data-value="{{ number_format($totalApproved,'2','.','') }}"></td>
		<td class=""></td>
	</tr>
</table>
</div>
<script>
$(".modal-title").html(
    '<i class="fa fa-calendar"></i> MPR Details – {{$mpr->mpr_number}} | {{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{ $mpr->mpr_year }} | {{$mpr->companyname}}'
);
$(".modal-footer").html('<button type="button" class="btn btn-info gridbtn btn-close" style="width:80px;" data-bs-dismiss="modal"><i class="fa fa-remove"></i> Close</button>');

$('.btn-close').on('click', function (e)
{
	$('#mprContent').html('');
	$('#viewMpr').modal('hide');
});

function viewAttendanceMprDepartment(r1,mprid,deploymentid)
{
	$.get(""+r1,
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
			$('.Tbldata').html(data.data);
		}
	});
}
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>