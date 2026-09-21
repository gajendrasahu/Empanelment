<!DOCTYPE html>
<html>
<head>
    <title>Interview Schedule</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear Team,</b><br><br>
	
	This is to inform you that the online presentation and interview for the EoI vide Ref. No: [<b>{{$eoi->eoinumber}}</b>], [<b>{{$eoi->engagementname}}</b>] is scheduled as per the details below:
	<br><br>
	@php
		$date = \Carbon\Carbon::parse($participation->interviewdate);
	@endphp	
	<b>Date: {{ $date->format('jS F Y') }}</b><br>
	<b>Time: {{ $date->format('h:i A') }}</b><br><br>
	
	Meeting Link:<br>
	[<a href="{{$participation->interviewlink}}" target="_blank">{{$participation->interviewlink}}</a>]<br><br>
	
	All concerned officials and participants are requested to join the meeting as per the scheduled date and time.<br><br>
	
	@include('emails.partials.footer')
	</td>
</tr>

</table>


</body>
</html>