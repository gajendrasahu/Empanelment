@php
$i=1;
use Carbon\Carbon;
@endphp
<tr class="">
	<td colspan="7" class="pd-5 form-label font-14">
		Firms that have submitted a Pre-Bid Query | EoI Number: {{$eoi->eoinumber}} | Project Name: {{$eoi->projecttitle}}
	</td>
</tr>
<tr class="myhead">
	<td class="center" style="width:25px;" nowrap><b>S.No.</b></td>
	<td class="" nowrap style=""><b>Firm Name</b></td>
	<td class="" nowrap style=""><b>Raised by</b></td>
	<td class="" nowrap style=""><b>Contact Number</b></td>
	<td class="" nowrap style=""><b>Designation</b></td>
	<td class="center" nowrap style="width:120px;"><b>Date</b></td>
	<td class="" nowrap class="center" style="width:100px;"><b>Attachment</b></td>
</tr>

@foreach ($data as $item)
<tr class="mytr" id="item-{{ $i }}">
    <td class="" style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
	<td class="" nowrap nowrap>{{ ucwords(strtolower($item->companyname))}}</td>
	<td class="" nowrap nowrap>{{ ucwords(strtolower($item->contact_name))}}</td>
	<td class="" nowrap nowrap>{{ ucwords(strtolower($item->contact_number))}}</td>
	<td class="" nowrap nowrap>{{ ucwords(strtolower($item->contact_designation))}}</td>
	<td nowrap class="center">{{date('d\-m\-Y, h:i A',strtotime($item->raisedon))}}</td>
	<td nowrap class="">
	@if($item->attachment!='')
	<a href="{{ route('view.uploadedfile', Crypt::encrypt($item->attachment)) }}" target="_blank">
		<span class="label label-white middle f-s-10 form-label">
			<i class="ace-icon fa fa-file-pdf-o"></i> View Attachment
		</span>		
	</a>
	@endif
	</td>
</tr>
@endforeach

@if(Carbon::parse($eoi->prebidlastdate)->setTime(17, 30, 0)->lt(Carbon::now()))
<tr>
	<td colspan="7" class="center">
		<a class="animated bounceIn" href="{{route('download.queries',Crypt::encrypt($eoi->requestid))}}" style="text-decoration:none; float:left;">
			<span class="btn btn-info forwardBtn gridbtn">
			<i class="fa fa-download icon-animated-bell "></i> Download All Query Files
			</span>			
		</a>
		<a class="animated bounceIn" href="{{route('download.prebidqueries',Crypt::encrypt($eoi->requestid))}}" style="text-decoration:none; float:left; margin-left:5px;">
			<span class="btn btn-info forwardBtn gridbtn">
			<i class="fa fa-download icon-animated-bell "></i> Download Pre-Bid Query
			</span>			
		</a>
		@if($prebid->prebidstatus=='PENDING')
		<a class="animated bounceIn" onclick="viewAllQuery('{{Crypt::encrypt($eoi->requestid)}}')" style="text-decoration:none; float:right; margin-right:10px;">
			<span class="btn btn-info viewQueryBtn gridbtn">
			<i class="fa fa-hand-o-right"></i> View & Forward Queries</span>
		</a>
		@elseif($prebid->prebidstatus=='FORWARDED')
		<a class="animated bounceIn" onclick="viewAllQuery('{{Crypt::encrypt($eoi->requestid)}}')" style="text-decoration:none; float:right; margin-right:10px;">
			<span class="btn btn-info viewQueryBtn gridbtn">
			<i class="fa fa-hand-o-right"></i> View Queries</span>
		</a>
		@elseif($prebid->prebidstatus=='REPLIED')
		<a class="animated bounceIn" onclick="viewAllQuery('{{Crypt::encrypt($eoi->requestid)}}')" style="text-decoration:none; float:right; margin-right:10px;">
			<span class="btn btn-info viewQueryBtn gridbtn">
			<i class="fa fa-hand-o-right"></i> Publish Pre-Bid Response to Firms</span>
		</a>
		@elseif($prebid->prebidstatus=='BRODCASTED')
		<button type="button" class="btn btn-info" style="text-decoration:none; float:right; margin-right:10px;" disabled><i class="fa fa-thumbs-up"></i> Published</button>
		@endif
	</td>
</tr>
@else
<tr>
	<td colspan="7">		
		<span style="margin-top:5px;" class="font-14">
			<i class="fa fa-hand-o-right"></i> <b>The pre-bid query period is still active. Please wait until the last date has passed.</b>
		</span>

		<a class="animated bounceIn" onclick="viewAllQuery('{{Crypt::encrypt($eoi->requestid)}}')" style="text-decoration:none; float:right;">
			<span class="btn btn-info viewQueryBtn gridbtn">
			<i class="fa fa-hand-o-right"></i> View Queries</span>
		</a>
	</td>
</tr>
@endif

@if($i==1)
<tr>
    <td colspan="7" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@endif

@if($broadcasted)
<tr>
<td colspan="7">
	<table class="mytable" border="1">
		<tr class="myheadbg"><td colspan="5" class="pd-5 form-label font-14"><b>Published Responses / Corrigendum</b></td></tr>
		<tr class="myhead">
			<td class="center" style="width:25px;" nowrap><b>S.No.</b></td>
			<td class="center" nowrap style="width:140px;"><b>Published Date</b></td>
			<td class="" nowrap style=""><b>Content</b></td>
			<td class="" nowrap class="center" style="width:100px;"><b>Attachment</b></td>
			<td class="" nowrap class="center" style="width:100px;"></td>
		</tr>
		@foreach($broadcasted as $cast)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td class="center" nowrap>{{date('d\-m\-Y, h:i A',strtotime($cast->broadcastedon))}}</td>
			<td class="">{!!$cast->broadcastmessage!!}</td>
			<td nowrap class="">
			@if($cast->attachment!='')
			<a href="{{ route('view.uploadedfile', Crypt::encrypt($cast->attachment)) }}" target="_blank">
				<span class="label label-white middle animated flipInX f-s-10 form-label">
					<i class="ace-icon fa fa-file-pdf-o"></i> View File
				</span>		
			</a>
			@endif
			</td>
			<td>
			<button type="button" class="btn btn-info viewQueryBtn gridbtn" style="text-decoration:none; float:right;" onclick="viewAllQuery('{{Crypt::encrypt($eoi->requestid)}}')">
				<i class="fa fa-hand-o-right"></i> View
			</button>
			
			</td>
		</tr>
		@endforeach
		@if($broadcasted->count()==0)
		<tr><td colspan="5" class="center">--No Record Found--</td></tr>
		@endif
	</table>
@endif
</td>
</tr>