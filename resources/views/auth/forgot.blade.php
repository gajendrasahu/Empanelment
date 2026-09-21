<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>CHiPS Empanelment Portal</title>
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
<form name="forgotPasswordForm" id="forgotPasswordForm" method="POST" action="{{ route('forgot-password') }}">
	@csrf
    <div class="login-container">
        <div class="login-header">
            <div class="logo"><img src="{{ asset('panel/assets/images/basic/logo.png')}}" style="width:80px!important;"></div>
            <div class="org-name">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</div>
            <h1 class="portal-title">CHiPS Empanelment Portal</h1>
        </div>
		<div class="login-as">Reset Password</div>
		@if(Session::has('success'))
			<div class="alert alert-success text-left">{{ Session::get('success') }}</div>
		@endif
		@if(Session::has('fail'))
			<div class="alert alert-danger text-left"><i class="fa fa-warning"></i> {!! Session::get('fail') !!}</div>
		@endif

		<div class="form-group">
			<label class="form-label" for="userId">
				Email<span class="required">*</span>
			</label>
			<input type="email" class="form-input" required name="email" id="email" value="{{old('email')}}" placeholder="Enter your email address" autocomplete="off">
			<span class="text-danger">@error('username') {{ $message }} @enderror</span>
		</div>

		<button type="submit" class="submit-btn">Send Link</button>
		<div class="form-row">
			<div class="checkbox-container">We will send you a link to reset your password.</div>
			<a href="{{route('loginpanel')}}">Back to Login Page</a>
		</div>
        
    </div>
</form>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
</body>
</html>