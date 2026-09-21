<!-- ATTENDANCE RELATED -->
@php $t=20; @endphp
@if(count($attendance)>0)
<table class="mytable" border="1">
	<tr class="myheadbg">
		<td style="padding:3px 5px;" colspan="10">
			ATTENDANCE DETAIL FOR THE MONTH OF {{ strtoupper(date("F", mktime(0, 0, 0, $monthname, 10))) }} {{ $yearname}}
		</td>
	</tr>
	<tr class="myheadbg">
		<td style="padding:2px; text-align:center;">S.NO.</td>
		<td style="padding:2px; text-align:center;">DATE</td>
		<td style="padding:2px; text-align:center;">SCHEDULED IN</td>
		<td style="padding:2px; text-align:center;">CHECK IN</td>
		<td style="padding:2px; text-align:center;">SCHEDULED OUT</td>
		<td style="padding:2px; text-align:center;">CHECK OUT</td>
		<td style="padding:2px; text-align:center; width:175px;">CHECK IN LATE (IN MIN.)</td>
		<td style="padding:2px; text-align:center; width:175px;">CHECK OUT EARLY (IN MIN.)</td>
	</tr>
	@php $r=1; @endphp
	@foreach($attendance as $attend)
	<tr>
		<td style="padding:2px; text-align:center;">{{ $r++ }}</td>
		<td style="padding:2px; text-align:center;">{{ date('d\-m\-Y',strtotime($attend->attendancedate))}}</td>
		<td style="padding:2px; text-align:center;">{{ $attend->actualcheckin }}</td>
		<td style="padding:2px; text-align:center;">{{ $attend->timein}}</td>
		<td style="padding:2px; text-align:center;">{{ $attend->actualcheckout}}</td>
		<td style="padding:2px; text-align:center;">{{ $attend->timeout}}</td>
		<td style="padding:2px; text-align:center;">{{ abs($attend->infine)}}</td>
		<td style="padding:2px; text-align:center;">{{ abs($attend->outfine)}}</td>
	</tr>
	@endforeach
	<tr class="myheadbg">
		<td colspan="6" style="text-align:right; padding:2px 5px;">TOTAL</td>
		<td style="text-align:center;">{{ abs($totalinfine) }} MINUTES</td>
		<td style="text-align:center;">{{ abs($totaloutfine)}} MINUTES</td>
	</tr>
	<tr class="myheadbg" style="font-size:14px;">
		<td colspan="6" style="text-align:right; padding:2px 5px;"><b>TOTAL CHECK IN CHECK OUT FINE</b></td>
		<td style="text-align:center;" colspan="2"><b>{{ abs($totalinfine)+abs($totaloutfine) }} MINUTES</b></td>
	</tr>
</table>
@endif
<!-- ATTENDANCE RELATED CLOSED -->

<!--APPLICATION RELATED-->
@if(count($application)>0)
<table class="mytable" border="1" style="margin-top:15px;">
<tr class="myheadbg">
	<td style="padding:3px 5px;" colspan="6">
		LEAVE APPLICATION FOR THE MONTH OF {{ strtoupper(date("F", mktime(0, 0, 0, $monthname, 10))) }} {{ $yearname }}
	</td>
</tr>
<tr class="myheadbg">
	<td style="padding:2px; text-align:center; width:30px;">S.NO.</td>
	<td style="padding:2px; text-align:center; width:90px;">FROM DATE</td>
	<td style="padding:2px; text-align:center; width:90px;">TO DATE</td>
	<td style="padding:2px; text-align:left; width:150px;">APPROVED BY</td>
	<td style="padding:2px; text-align:left;">REMARK</td>
	<td style="padding:2px; text-align:center; width:90px;">DAYS</td>
</tr>
@php 
$r=1;
@endphp

@foreach($application as $app)
<tr>
	<td style="padding:2px; text-align:center;">{{ $r++ }}</td>
	<td style="padding:2px; text-align:center;">{{ date('d\-m\-Y',strtotime($app->frmdate)) }}</td>
	<td style="padding:2px; text-align:center;">{{ date('d\-m\-Y',strtotime($app->todate)) }}</td>
	<td style="padding:2px; text-align:left;">{{ $app->approvedby }}</td>
	<td style="padding:2px; text-align:left;">{{ $app->remark }}</td>
	<td style="padding:2px; text-align:center;">{{ $app->days }}</td>
</tr>
@endforeach	
<tr class="myheadbg" style="font-size:14px;">
	<td colspan="5" style="text-align:right; padding:2px 5px;"><b>TOTAL LEAVE DAYS</b></td>
	<td style="text-align:center;"><b>{{ $leavedays }}</b></td>
</tr>
</table>
@endif
<!--APPLICATION RELATED CLOSED-->

<!--ADVANCE RELATED-->
@if(($advances->advance-$advances->paid)>0)
<table class="mytable" border="1" style="width:350px!important; margin-top:15px; float:right; margin-bottom:15px;">
	<tr class="myheadbg">
		<td style="padding:3px 5px;" colspan="2">ADVANCE BALANCE DETAIL</td>
	</tr>
	<tr class="myheadbg">
		<td style="padding:2px; text-align:center; width:100px;">BALANCE AMOUNT</td>
		<td style="padding:2px; text-align:center; width:100px;">MONTHLY DEDUCTION</td>
	</tr>
	<tr>
		<td style="padding:2px; text-align:center;">{{ $advances->advance-$advances->paid}} INR</td>
		<td style="padding:2px; text-align:center;">{{ $data->monthlydeduction}} INR</td>
	</tr>
</table>
@endif

@if(($advances->advance-$advances->paid)<=$data->monthlydeduction)
@php $data->monthlydeduction = $advances->advance-$advances->paid; @endphp
@endif

</table>
<!--ADVANCE RELATED CLOSED-->

<!--NET SALARY DETAIL-->
<table class="mytable" border="1" style="font-size:12px; margin-top:15px;">
	<tr class="myheadbg">
		<td style="padding:3px 5px;" colspan="10">SALARY CALCULATION DETAIL FOR MONTH OF {{ strtoupper(date("F", mktime(0, 0, 0, $monthname, 10))) }} {{ $yearname }}
		</td>
	</tr>
	<tr class="myheadbg">
		<td style="padding:2px; text-align:left; width:100px;">BASIC SALARY</td>
		<td style="padding:2px; text-align:left; width:100px;">MONTH DAYS</td>
		<td style="padding:2px; text-align:left;" nowrap>WORKING DAYS</td>
		<td style="padding:2px; text-align:left;" nowrap>PER DAY SALARY</td>
		<td style="padding:2px; text-align:left;" nowrap>PER MIN. SALARY</td>
		<td style="padding:2px; text-align:left;" nowrap>ATTENDED DAYS</td>
		<td style="padding:2px; text-align:left;" nowrap>LEAVE DAYS</td>
		<td style="padding:2px; text-align:left;" nowrap>FINE MINUTES</td>
		<td style="padding:2px; text-align:left;" nowrap>FINE AMOUNT</td>
		<td style="padding:2px; text-align:left;" nowrap>MONTHLY DEDUCTION</td>
	</tr>
	<tr>
		<td style="padding:0px; text-align:left; width:100px;">
			<input type="hidden" name="totalmins" id="totalmins" value="{{ $data->totalmins }}">
			<input type="hidden" name="attend" id="attend" value="{{ count($attendance) }}">
			<input type="hidden" name="leave" id="leave" value="{{ $leavedays }}">
			<input type="hidden" name="balanceadvance" id="balanceadvance" value="{{ $advances->advance-$advances->paid }}">
			<input type="hidden" name="salarydeduction" id="salarydeduction" value="{{ $data->monthlydeduction }}">
			<input type="text" class="selectbx numbers" style="width:100%;" name="monthlysalary" id="monthlysalary" placeholder="0" value="{{ $data->basicsalary}}" readonly tabindex="{{ $t++ }}" onKeyPress="return OnKeyPress(this, event)">
		</td>				

		<td style="padding:0px; text-align:left; width:100px;">
			<input type="text" class="selectbx" style="width:100%;" name="monthdays" id="monthdays" placeholder="0" value="{{ $data->monthdays}}" readonly tabindex="{{ $t++ }}" onKeyPress="return OnKeyPress(this, event)">
		</td>				
		<td style="padding:0px; text-align:left;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="workingdays" id="workingdays" placeholder="0" value="{{old('workingdays',$data->workingdays)}}" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" required onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px; text-align:left;">
			<input type="text" class="selectbx numbers" style="width:100%;" required name="perdaysalary" id="perdaysalary" placeholder="0" readonly onKeyPress="return OnKeyPress(this, event)">
		</td>
		<td style="padding:0px; text-align:left;">
			<input type="text" class="selectbx numbers" style="width:100%;" required name="perminsalary" id="perminsalary" placeholder="0" readonly onKeyPress="return OnKeyPress(this, event)">
		</td>
		<td style="padding:0px; text-align:left;">
			<input type="text" class="selectbx numbers" style="width:100%;" required name="attendeddays" id="attendeddays" placeholder="0" value="{{count($attendance)}}" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" readonly onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px; text-align:left; width:100px;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="leavedays" id="leavedays" placeholder="0" value="{{ $leavedays }}" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" readonly onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px; text-align:left; width:100px;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="fineminutes" id="fineminutes" placeholder="0" value="{{ abs($totalinfine) +abs($totaloutfine) }}" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" readonly onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px; text-align:left; width:100px;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="fineamount" id="fineamount" placeholder="0" value="" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" readonly onKeyPress="return OnKeyPress(this, event)">
		</td>
		<td style="padding:0px; text-align:left; width:100px;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="monthlydeduction" id="monthlydeduction" placeholder="0" value="{{ abs($data->monthlydeduction) }}" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" readonly onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
	</tr>
</table>
<!--NET SALARY DETAIL CLOSED-->

<table class="mytable" border="1" style="width:650px!important; margin-top:15px; float:right;">
	<tr class="myheadbg">
		<td style="padding:3px 5px;" colspan="6">GROSS SALARY CALCULATION DETAIL FOR MONTH OF {{ strtoupper(date("F", mktime(0, 0, 0, $monthname, 10)))}} {{ $yearname }}
		</td>
	</tr>
	<tr class="myheadbg">
		<td style="text-align:center;">THIS MONTH SALARY</td>
		<td style="text-align:center;">TA</td>
		<td style="text-align:center;">DA</td>
		<td style="text-align:center;">HRA</td>
		<td style="text-align:center;">MA</td>
		<td style="text-align:center;">GROSS SALARY</td>
	</tr>
	<tr>
		<td style="padding:0px; text-align:left;">
			<input type="text" class="selectbx numbers" style="width:100%;" name="thismonthsalary" id="thismonthsalary" placeholder="0" onchange="CalculatePerDaySalary()" tabindex="{{ $t++ }}" onKeyPress="return OnKeyPress(this, event)" required readonly>
		</td>
		<td style="padding:0px;">
			<input type="text" class="selectbx numbers" name="ta" id="ta" value="{{$data->ta}}" placeholder="0" style="width:100px; text-align:center;" onchange="CalculatePerDaySalary()" tabindex="{{ $t++}}" onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" class="selectbx numbers" name="da" id="da" value="{{$data->da}}" placeholder="0" style="width:100px; text-align:center;" onchange="CalculatePerDaySalary()" tabindex="{{ $t++}}" onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" class="selectbx numbers" name="hra" id="hra" value="{{$data->hra}}" placeholder="0" style="width:100px; text-align:center;" onchange="CalculatePerDaySalary()" tabindex="{{ $t++}}" onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" class="selectbx numbers" name="ma" id="ma" value="{{$data->ma}}" placeholder="0" style="width:100px; text-align:center;" onchange="CalculatePerDaySalary()" tabindex="{{ $t++}}" onKeyPress="return OnKeyPress(this, event)" autocomplete="off">
		</td>
		<td style="padding:0px;">
			<input type="text" class="selectbx numbers" name="grosssalary" id="grosssalary" value="" placeholder="0" style="width:100px; text-align:center;" onchange="CalculatePerDaySalary()" tabindex="{{ $t++}}" onKeyPress="return OnKeyPress(this, event)" required readonly autocomplete="off">
		</td>
	</tr>
	<tr>
		<td style="padding:2px; 5px;">ENTER REMARK</td>
		<td colspan="5">
			<input type="text" class="selectbx" name="salaryremark" id="salaryremark" required placeholder="Enter salary remark here...." tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" style="width: 100%;" autocomplete="off">
		</td>
	</tr>
	
</table>

<div class="col-sm-row" style="padding:0px;">
<div class="col-sm-9"></div>
<div class="col-sm-2">
	<button type="submit" class="btn btn-info myfrmbtn sbt" tabindex="{{$t++}}">PAY SALARY</button>
</div>
<div class="col-sm-1" style="padding:0px;">
	<button type="button" class="btn btn-info myfrmbtn" tabindex="{{$t++}}" onclick="ReLoad()">RELOAD</button>
</div>
</div>
@if($data->workingdays!=0)
<script>
CalculatePerDaySalary();
setTimeout(function(){ $("#attendeddays").focus(); },500);
</script>
@endif