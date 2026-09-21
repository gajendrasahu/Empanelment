<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Error Occurred | CHiPS</title>
  <link rel="shortcut icon" href="{{ asset('panel/assets/images/basic/logo.png') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('panel/assets/css/bootstrap.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/font-awesome/4.5.0/css/font-awesome.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-skins.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-rtl.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/newlogin.css')}}" />
</head>
<body>
<form name="loginForm" id="loginForm" method="POST" action="{{ route('login-user') }}">
@csrf

    <div class="login-container">
        <div class="login-header">
            <div class="logo"><img src="{{ asset('panel/assets/images/basic/logo.png')}}" style="width:80px!important;"></div>
            <div class="org-name">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</div>
            <h1 class="portal-title">CHiPS Empanelment Portal</h1>
        </div>
		<div class="login-as">Something went wrong</div>

		<p>Sorry, we couldn't find that page. Please check the URL or go back to the homepage.</p>
		<p>If you think this is a mistake, feel free to contact our support team for assistance.</p>

		<button type="button" class="submit-btn" onclick="history.back()">Back</button>
        
    </div>
</form>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
</body>
</html>