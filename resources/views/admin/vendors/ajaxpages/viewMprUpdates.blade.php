<table style="width:100%!important;">
<tr>
<td style="width:50%!important; padding:5px!important;">
<table class="mytable pd-8" border="1" style="width:100%!important;">
	<tr class="bg-primary">
		<td colspan="7">Recorded MPR & Attendance Updates for This Resource</td>
	</tr>
	<tr class="bg-primary">
		<td></td>
		<td class="text-center">Present</td>
		<td class="text-center">Absent</td>
		<td class="text-center">Leave</td>
		<td class="text-center">Holiday</td>
		<td class="text-center">Updated On</td>
	</tr>
	<tr>
		<td class="bg-primary" nowrap><i class="fa fa-angle-double-right"></i> <b>Submitted</b></td>
		<td class="text-center"><b>@if($summaryLog->total_present){{$summaryLog->total_present}}@endif</b></td>
		<td class="text-center"><b>@if($summaryLog->total_absent){{$summaryLog->total_absent}}@endif</b></td>
		<td class="text-center"><b>@if($summaryLog->total_leave){{$summaryLog->total_leave}}@endif</b></td>
		<td class="text-center"><b>@if($summaryLog->total_holiday){{$summaryLog->total_holiday}}@endif</b></td>
		<td class="text-center"></td>
	</tr>
	<tr>
		<td class="bg-primary" nowrap><i class="fa fa-angle-double-right"></i> <b>Updated</b></td>
		<td class="text-center"><b>@if($summary->total_present){{$summary->total_present}}@endif</b></td>
		<td class="text-center"><b>@if($summary->total_absent){{$summary->total_absent}}@endif</b></td>
		<td class="text-center"><b>@if($summary->total_leave){{$summary->total_leave}}@endif</b></td>
		<td class="text-center"><b>@if($summary->total_holiday){{$summary->total_holiday}}@endif</b></td>
		<td class="text-center">{{date('d\-m\-Y, h:i A',strtotime($summaryLog->updated_on))}}</td>
	</tr>
</table>
</td>
<td class="v-top" style="width:50%!important; padding:5px!important;">
<table class="mytable pd-8" border="1" style="width:100%!important;">
	<tr class="bg-primary">
		<td colspan="7" style="position:relative;">
			Other Deduction Detail for This Resource
			<a class="action-a" style="position:absolute; right:5px; top:3px;" onclick="hideRow()">
				<i class="fa fa-remove color-white"></i>
			</a>
		</td>
	</tr>
	<tr>
		<td class="bg-primary width-100" nowrap><i class="fa fa-angle-double-right"></i> <b>Deduction Value</b></td>
		<td class="text-left"><b>@if($summary->net_salary!=$summary->approved_salary){{$summary->net_salary-$summary->approved_salary}} INR @endif</b></td>
	</tr>
	<tr>
		<td class="bg-primary" nowrap><i class="fa fa-angle-double-right"></i> <b>Remark</b></td>
		<td class="text-left">@if($summaryLog->remark) {{$summaryLog->remark}} @else Not Available @endif</td>
	</tr>
	<tr>
		<td class="bg-primary" nowrap><i class="fa fa-angle-double-right"></i> <b>Attachment</b></td>
		<td class="text-left">
			@if($summaryLog->deduction_file)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($summaryLog->deduction_file))}}"><i class="fa fa-file-pdf-o"></i></a>
			@else
				Not Available
			@endif
		</td>
	</tr>
</table>
</td>
</tr>
</table>