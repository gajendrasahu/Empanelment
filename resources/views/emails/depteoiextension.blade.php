<!DOCTYPE html>
<html>
<head>
    <title>EoI Date Extension</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear Sir/Madam,</b><br><br>
	
	This is to inform you that the submission deadline for the Expression of Interest (EOI) titled [{{$eoi->eoinumber}}] has been extended.
	<br><br>
	The revised date and time are as follows:<br><br>
	
	Last Date for Submission of Proposals: {{date('d\-m\-Y h:i A',strtotime($eoi->deadlinedate))}}<br>
	@if($eoi->interviewdate)
	Tentative Date of Presentation and Interview: {{date('d\-m\-Y h:i A',strtotime($eoi->interviewdate))}}<br>
	@endif

	<br>
	All empanelled firms have been notified accordingly.<br><br>
	Please feel free to contact us for any further assistance.<br><br>
	
	
@include('emails.partials.footer')
	</td>
</tr>

</table>


</body>
</html>