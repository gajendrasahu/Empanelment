<!DOCTYPE html>
<html>
<head>
    <title>Login Credentials</title>
</head>
<body style="margin:0; padding:0; font-family:Arial, sans-serif; color:#000000;">

<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">
<tr>
	<td style="text-align:left; height:300px; padding:10px;">
	<b>Dear {{$name}},</b><br>
	
	<p>Your account has been created successfully.</p>
	
	<p>You can now log in to the portal using the details below:</p>
	
	<p><b>Login Email:</b> {{ $email }}</p>
	<p><b>Password:</b> {{ $pass_word }}</p><br>
	
	<p>Login here: <a href="https://empl.cgstate.gov.in" target="_blank">https://empl.cgstate.gov.in</a></p>
	
	<p><b>Important:</b></p>
	<p>Please change your password after your first login for security purposes.</p>
	
<br>
	
Regards,<br>
Empanelment Team<br>
Chhattisgarh Infotech Promotion Society (CHiPS)<br>
SDC Building 2nd Floor, Civil Lines<br>
Raipur - 492001<br><br>
	
	

	</td>
</tr>

</table>


</body>
</html>