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
<form name="forgotPasswordForm" id="forgotPasswordForm" method="POST" action="{{ route('update-password') }}">
	@csrf
    <div class="login-container">
        <div class="login-header">
            <div class="logo"><img src="{{ asset('panel/assets/images/basic/logo.png')}}" style="width:80px!important;"></div>
            <div class="org-name">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</div>
            <h1 class="portal-title">CHiPS Empanelment Portal</h1>
        </div>
		<div class="login-as">Create New Password</div>
		@if(Session::has('success'))
			<div class="alert alert-success text-left">{{ Session::get('success') }}</div>
		@endif
		@if(Session::has('fail'))
			<div class="alert alert-danger text-left"><i class="fa fa-warning"></i> {!! Session::get('fail') !!}</div>
		@endif

		<div class="form-group">
			<label class="form-label" for="userId">
				Password<span class="required">*</span>
			</label>
			<input type="hidden" name="token" value="{{ $token }}">
			<input type="hidden" name="email" value="{{ $email }}">
			<input type="password" class="form-input" required name="user_password" id="user_password" value="{{old('password')}}" placeholder="Enter your new password" autocomplete="off">
			<span class="text-danger">@error('password') {{ $message }} @enderror</span>
		</div>

		<div class="form-group">
			<label class="form-label" for="userId">
				Confirm Password<span class="required">*</span>
			</label>
			<input type="password" class="form-input" required name="user_password_confirmation" id="user_password_confirmation" value="{{old('user_password_confirmation')}}" placeholder="Confirm your new password" autocomplete="off">
			<span class="text-danger">@error('user_password_confirmation') {{ $message }} @enderror</span>
		</div>

		<button type="submit" class="submit-btn">Set New Password</button>
		<div class="form-row">
			<div class="checkbox-container"><a href="{{route('forgotpassword')}}">Re-send Link</a></div>
			<a href="{{route('loginpanel')}}">Back to Login Page</a>
		</div>
       
    </div>
</form>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
</body>
</html>