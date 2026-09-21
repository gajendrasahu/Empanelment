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
	
	We are pleased to inform you that your Expression of Interest (EOI) request [<b>{{$eoi->eoinumber}}</b>] has been successfully published to firms.
	<br><br>
	
	Thank you for your attention and cooperation.<br><br>
	
@include('emails.partials.footer')	
	</td>
</tr>

</table>


</body>
</html>