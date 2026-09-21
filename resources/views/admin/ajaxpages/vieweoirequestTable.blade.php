@php
$i=1;


@endphp
<table class="mytable" border="1" style="text-transform: capitalize!important;">
	<tr class="myheadbg"><td class="td-padding" style="font-size:16px; font-weight:bold;" colspan="12">Draft Expression of Interest (EoI) REQUEST</td></tr>
	<tr>
		<td class="mytdleft" style="width:200px; padding:4px 5px!important;" nowrap>Department Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->departmentname)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Officer Name</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->name)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Designation</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->designation)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px;" nowrap>Contact Number</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ ucwords(strtolower($data->mobilenumber)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Email Id</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ strtolower($data->email) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Date of Submission</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ date('d\-m\-Y, h:i A',strtotime($data->creationdate)) }}</td>
	</tr>
	<tr>
		<td class="mytdleft td-padding" style="width:200px; padding:4px 5px!important;" nowrap>Approval Date</td>
		<td class="mytdleft td-padding" style="padding:4px 5px!important;" nowrap>{{ date('d\-m\-Y, h:i A',strtotime($data->actiondatetime)) }}</td>
	</tr>
</table>
<div class="table-responsive">
<table class="table table-bordered table-striped table-hover mytable" border="1">
	<tr class="myheadbg"><td class="mytdleftwhite" colspan="15">ADDED EXPRESSION OF INTEREST LIST</td></tr>
	<tr class="primary-bgcolor color-white font-12 font-bold">
		<td class="mytdleftwhite center" style="width:50px;">S.No.</td>
		<td class="mytdleftwhite" nowrap>Category</td>
		<td class="mytdleftwhite" nowrap>Role</td>
		<td class="mytdleftwhite" nowrap>Tier</td>
		<td class="mytdleftwhite" nowrap>Experience</td>
		<td class="mytdleftwhite" nowrap>Qualification</td>
		<td class="mytdleftwhite" nowrap>Numbers</td>
		<td class="mytdleftwhite" nowrap>Duration</td>
		<td class="mytdleftwhite" nowrap>Work Location</td>
		<td class="mytdleftwhite" nowrap>Start Date</td>
		<td class="mytdleftwhite" nowrap>End Date</td>
		<td class="mytdleftwhite" nowrap>Urgency</td>
		<td class="mytdleftwhite" nowrap>Budget</td>
	</tr>
	<tbody class="eoidata">
	@php $i=0; $total=0; @endphp
	@foreach($detail as $detail)
	@php $i=$i+1; @endphp
	<tr class="tr-20 font-12">
		<td class="v-top text-center">{{$i}}</td>
		<td class="v-top default-td">{{$data->jobcategory}}</td>
		@if($data->categoryid==2)
		<td class="v-top default-td">{{$detail->sectorname}}</td>
		<td class="v-top default-td">{{$detail->consultantposition}}</td>
		@else
		<td class="v-top default-td">{{$detail->role}}</td>
		@endif
		<td class="v-top default-td">{{$detail->tiername}}</td>
		@if($data->categoryid==1)
		<td class="v-top default-td">{{$detail->workexperience}} [L-{{$detail->experiencelevel}}]</td>
		@else
		<td class="v-top default-td">{{$detail->experience}}</td>
		@endif
		@if($data->categoryid==2)
		<td class="v-top default-td">{{$detail->engagement}}</td>
		@endif
		@if($data->categoryid==1)
		<td class="v-top default-td">{{$detail->qualification}}</td>
		@endif
		<td class="v-top default-td">{{$detail->requirednumber}}</td>
		<td class="v-top default-td">{{$detail->duration}}</td>
		<td class="v-top default-td">{{$detail->worklocation}}</td>
		<td class="v-top default-td">{{date('d\-m\-Y',strtotime($detail->startdate))}}</td>
		<td class="v-top default-td">{{date('d\-m\-Y',strtotime($detail->enddate))}}</td>
		<td class="v-top default-td">{{$detail->urgency}}</td>
		<td class="v-top text-right">{{$detail->budgetamount}}</td>
	</tr>
	@php $total=$budgetamount+$detail->budgetamount; @endphp
	@endforeach
	<tr>
		<td @if($data->categoryid==2) colspan="12" @else colspan="12" @endif style="vertical-align:top;" class="padding-0-10 font-18 font-bold text-right">All Requirement Total Budget</td>
		<td class="padding-0-10 font-18 font-bold text-right">{{$total}}</td>
	</tr>
	@if($i==1)
	<tr>
		<td colspan="15" class="center">--NO RECORD ADDED--</td>
	</tr>
	@endif
	</tbody>
	<tr>
		<td colspan="15" class="center">
			<button type="button" class="btn btn-info mygridbtn" onclick="Cls()" style="width:100px!important;">CLOSE</button>
		</td>
	</tr>
</table>
</div>
