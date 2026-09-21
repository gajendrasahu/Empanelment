<!DOCTYPE html>
<html>
<head>
    <title>Publish</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	@if($usrType->isdepartment==1)
	<b>Dear {{$usrType->name}},</b><br><br>
	@else
	<b>Dear {{$usrType->name}},</b><br><br>
	@endif
	
	This is to inform you that the replies to the pre-bid queries, as provided by the concerned department, have been compiled.
	<br><br>
	CHiPS shall be publishing the same for circulation to all participating vendors.<br><br>
	
	This is for your kind information.<br><br>
	
@include('emails.partials.footer')	
	</td>
</tr>

</table>


</body>
</html>