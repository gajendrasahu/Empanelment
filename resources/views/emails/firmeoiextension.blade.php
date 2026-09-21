<!DOCTYPE html>
<html>
<head>
    <title>EOI Submission Deadline Extension</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear Sir/Madam,</b><br><br>
	
	This is to inform you that the submission deadline for the Expression of Interest (EOI) titled [{{$eoi->eoinumber}}], invited by [{{$departmentName}}], has been extended.
	<br><br>
	The revised date and time are as follows:<br><br>
	
	Last Date for Submission of Proposals: {{date('d\-m\-Y h:i A',strtotime($eoi->deadlinedate))}}<br>
	@if($eoi->interviewdate)
	Tentative Date of Presentation and Interview: {{date('d\-m\-Y h:i A',strtotime($eoi->interviewdate))}}<br>
	@endif

	<br>
	Interested firms are requested to submit their proposals within the extended timeline.<br><br>
	For any clarification, you may contact the concerned department.<br><br>

	
@include('emails.partials.footer')
	</td>
</tr>

</table>


</body>
</html>