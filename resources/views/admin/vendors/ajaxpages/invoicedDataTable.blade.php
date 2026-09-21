@foreach($data as $invoice)
<tr>
	<td class="bg-primary" colspan="10" style="color:white;">
	{{$loop->iteration}}) {{$invoice->invoice_number}} 
	| <i class="fa fa-calendar"></i> {{date('d\-m\-Y',strtotime($invoice->invoice_date))}}
	| Gross Invoice Value : INR <span class="format-indian" data-value="{{number_format($invoice->net_invoice_value,'2','.','')}}"></span>
	@if($invoice->invoice_file)
	|	<a href="{{route('view.uploadedfile',Crypt::encrypt($invoice->invoice_file))}}" target="_blank" style="outline:none!important;">
			<span class="badge badge-default br-5"><i class="fa fa-file-pdf-o" style="color:white!important;"></i> Invoice</span>
		</a>
	@endif

	<span class="badge @if($invoice->finance_status=='Pending') badge-warning @else badge-success @endif br-5" style="float:right!important;">Verification Status : {{$invoice->finance_status}}</span>
	<span class="badge @if($invoice->invoice_status=='Submitted') badge-warning @else badge-success @endif br-5" style="float:right!important; margin-right:5px;">Invoice Status : {{$invoice->invoice_status}}</span>
	
	</td>
</tr>
@foreach($invoice->mprs as $orderId => $mprList)
@foreach($mprList as $mpr)
<tr>
	<td class="myheadbg" colspan="10">
	{{$mpr->mpr_number}}
	| <i class="fa fa-calendar"></i> {{date('d\-m\-Y',strtotime($mpr->submission_date))}}
	| <i class="fa fa-calendar"></i> {{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{ $mpr->mpr_year }}
		@if($mpr->signed_mpr)
		<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_mpr))}}" target="_blank">
			| MPR <i class="fa fa-file-pdf-o"></i>
		</a>
		@endif
	
		@if($mpr->signed_attendance)
		<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_attendance))}}" target="_blank">
			| Attendance <i class="fa fa-file-pdf-o"></i>
		</a>
		@endif
	
		@if($mpr->signed_supporting)
		<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_supporting))}}" target="_blank">
			| Supporting Document <i class="fa fa-file-pdf-o"></i>
		</a>
		@endif

	<span class="badge badge-info br-5" style="float:right!important;">
		Approved Value : <span class="format-indian" data-value="{{$mpr->mpr_approved_value}}"></span>
	</span>
	<span class="badge badge-info br-5" style="float:right!important; margin-right:5px;">
		MPR Value : <span class="format-indian" data-value="{{$mpr->mpr_value}}"></span>
	</span>
	
	</td>
</tr>
@if($mpr->attendance_summary->count() > 0)

@php
    $totalCalculatedSalary = $mpr->attendance_summary->sum('calculated_salary');
    $totalNetSalary = $mpr->attendance_summary->sum('net_salary');
    $totalApprovedSalary = $mpr->attendance_summary->sum('approved_salary');
@endphp

<tr>
    <td colspan="10">
        <div class="table-responsive">
            <table class="table-bordered pd-5 mytable" border="1">
                <tr class="myheadbg">
                    <td>Resource</td>
                    <td class="no_wrap text-center">Working Days</td>
                    <td class="text-center">P</td>
                    <td class="text-center">A</td>
                    <td class="text-center">L</td>
                    <td class="text-center">H</td>
                    <td class="text-right">Per Day Cost</td>
                    <td class="text-right">Actual Salary</td>
                    <td class="text-right">Calculated Salary</td>
                    <td class="text-right">Net Salary</td>
                    <td class="text-right">Deduction</td>
                    <td class="text-right">Approved Salary</td>
                </tr>
				@foreach($mpr->attendance_summary as $attendance)
				<tr>
					<td class="no_wrap">
						{{ $attendance->resource_name }}<br>
						{{ $attendance->resource_mobilenumber }}
					</td>

					<td class="text-center">
						{{ $attendance->total_working_days }}
					</td>

					<td class="text-center">
						{{ $attendance->total_present }}
					</td>

					<td class="text-center">
						{{ $attendance->total_absent }}
					</td>

					<td class="text-center">
						{{ $attendance->total_leave }}
					</td>

					<td class="text-center">
						{{ $attendance->total_holiday }}
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->per_day_cost }}">
						</span>
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->actual_salary }}">
						</span>
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->calculated_salary }}">
						</span>
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->net_salary }}">
						</span>
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->deduction }}">
						</span>
					</td>

					<td class="text-right">
						<span class="format-indian"
							data-value="{{ $attendance->approved_salary }}">
						</span>
					</td>
				</tr>

				@endforeach

				{{-- TOTAL ROW --}}
				<tr class="myheadbg">
					<td colspan="8" class="text-right">
						<strong>Total</strong>
					</td>

					<td class="text-right">
						<strong>
							<span class="format-indian"
								data-value="{{ $totalCalculatedSalary }}">
							</span>
						</strong>
					</td>

					<td class="text-right">
						<strong>
							<span class="format-indian"
								data-value="{{ $totalNetSalary }}">
							</span>
						</strong>
					</td>

					<td class="text-center">
						-
					</td>

					<td class="text-right">
						<strong>
							<span class="format-indian"
								data-value="{{ $totalApprovedSalary }}">
							</span>
						</strong>
					</td>
				</tr>
            </table>
        </div>
    </td>
</tr>

@endif
@endforeach
@endforeach
@endforeach


<script>
document.querySelectorAll('.format-indian').forEach(function(el)
{
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});
</script>