<!DOCTYPE html>
<html>
<head>
    <title>Login Credential</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear {{$name}},</b><br><br>
	Welcome! Your account has been successfully created.<br><br>
	You can log in using the credentials below:<br><br>

	Login URL: <a href="{{$weblink}}" target="_blank">https://empl.cgstate.gov.in</a><br>
	Username/Email: {{$email}}<br>
	Temporary Password: {{$password}}<br><br>
	
	For security reasons, we strongly recommend that you log in as soon as possible and change your password.
	<br><br>

@include('emails.partials.footer')	
	</td>
</tr>

</table>


</body>
</html>