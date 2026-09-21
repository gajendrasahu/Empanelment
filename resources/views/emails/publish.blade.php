<!DOCTYPE html>
<html>
<head>
    <title>Publish</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear All,</b><br><br>
	
	This is to inform you about the EoI vide Ref. No: <b>{{$eoi->eoinumber}}</b> Dated {{date('d\-m\-Y',strtotime($eoi->releasedate))}}, <b>{{$eoi->projecttitle}}</b><br><br>
	
	The important deadlines for response are as follows:<br><br>
	
	<table width="60%" style="border:1px solid #777; border-collapse:collapse;" border="1">
		<tr>
			<td style="padding:8px;" nowrap>Release Date of EoI by CHiPS</td>
			<td style="padding:8px;" nowrap>{{date('d\-m\-Y',strtotime($eoi->releasedate))}}</td>
		</tr>
		<tr>
			<td style="padding:8px;" nowrap>Last Date of Pre-bid Query</td>
			<td style="padding:8px;" nowrap>{{date('d\-m\-Y',strtotime($eoi->prebidlastdate))}}</td>
		</tr>
		<tr>
			<td style="padding:8px;" nowrap>Deadline for submission of proposals</td>
			<td style="padding:8px;" nowrap>{{date('d\-m\-Y h:i A',strtotime($eoi->deadlinedate))}}</td>
		</tr>
		@if($eoi->interviewdate)
		<tr>
			<td style="padding:8px;" nowrap>Tentative Date of Presentation and Interview	</td>
			<td style="padding:8px;" nowrap>{{date('d\-m\-Y',strtotime($eoi->interviewdate))}}</td>
		</tr>
		@endif
	</table>
	<br><br>
	Kindly adhere to the above deadlines and revert accordingly<br><br>
	For more information, please visit the <a href="https://empl.cgstate.gov.in" target="_blank">https://empl.cgstate.gov.in</a> using your login credentials to view the detailed EoI and related documents.
	<br><br>

@include('emails.partials.footer')	
	</td>
</tr>

</table>


</body>
</html>