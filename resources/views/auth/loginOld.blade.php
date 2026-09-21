<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>{{ __('loginform.adminwindowtitle') }}</title>
  <link rel="shortcut icon" href="{{ asset('panel/assets/images/basic/logo.png') }}">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="{{ asset('panel/assets/css/bootstrap.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/font-awesome/4.5.0/css/font-awesome.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-skins.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/ace-rtl.min.css')}}" />
  <link rel="stylesheet" href="{{ asset('panel/assets/css/login.css')}}" />
</head>
<body>

<div class="container-fluid full-height">
  <!-- Header -->
<div class="row">
	<div class="col-xs-12 header text-center">
		<div style="display: inline-block; vertical-align: middle;">
			<img src="{{ asset('panel/assets/images/basic/logo.png') }}" alt="Login Image" style="height: 50px; vertical-align: middle;">
		</div>
		<div style="display: inline-block; vertical-align: middle; margin-left: 10px;">
			<span id="banner-text">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</span><br>
			<h2 class="primary-color" style="margin: 0; display: inline-block;">CHIPS Empanelment Portal</h2>
		</div>
	</div>
</div>

  <!-- Responsive Content Area -->
  <div class="row content-area">
    <!-- Image Column -->
    <div class="col-sm-6 col-xs-12 image-box text-center">
      <img src="{{ asset('panel/assets/images/basic/login.png') }}" alt="Login Image" style="width:70%;">
    </div>

    <!-- Login Form Column -->
    <div class="col-sm-6 col-xs-12 form-box">
      <div class="login-box">
		<span class="small-text text-left" style="display: block; margin-top:-20px; float:right;">
		  * Indicates mandatory fields
		</span>
	  
        <h3 class="text-left primary-color" style="margin-bottom:0px;">Welcome Back Login Now!</h3>
		<span class="small-text text-left" style="display: block; margin-bottom: 20px;">
		  Enter Id & Password to get access of your portal
		</span>
        <form method="POST" action="{{ route('login-user') }}">
		@csrf

		  <div class="row text-center" style="margin-bottom: 20px;">
		@if(Session::has('success'))
			<div class="alert alert-success text-left">{{ Session::get('success') }}</div>
		@endif
		@if(Session::has('fail'))
			<div class="alert alert-danger text-left"><i class="fa fa-warning"></i> {!! Session::get('fail') !!}</div>
		@endif

			<div class="col-xs-6 col-sm-3 p-0">
			  <label class="radio-button-label {{ old('usertype')==='DEPARTMENT' ? 'checked' : '' }}">
				<input type="radio" required name="usertype" value="DEPARTMENT" onchange="handleRadioChange(this)" {{ old('usertype')==='DEPARTMENT' ? 'checked' : '' }}>
				DEPARTMENT
			  </label>
			</div>
			
			<div class="col-xs-6 col-sm-3 p-0">
			  <label class="radio-button-label {{ old('usertype')==='ADMIN' ? 'checked' : '' }}">
				<input type="radio" required name="usertype" value="ADMIN" onchange="handleRadioChange(this)" {{ old('usertype')==='ADMIN' ? 'checked' : '' }}>
				ADMIN (CHiPS)
			  </label>
			</div>


			<div class="col-xs-6 col-sm-3 p-0">
			  <label class="radio-button-label {{ old('usertype')==='VENDOR' ? 'checked' : '' }}">
				<input type="radio" required name="usertype" value="VENDOR" onchange="handleRadioChange(this)" {{ old('usertype')==='VENDOR' ? 'checked' : '' }}>
				VENDOR
			  </label>
			</div>

		  </div>		


          <div class="form-group">
            <label for="username" class="label-text">User Id or User Code *</label>
            <input type="text" class="form-control" required name="username" id="username" value="{{old('username')}}" placeholder="User Id or User Code" autocomplete="off">
			<span class="text-danger">@error('username') {{ $message }} @enderror</span>
          </div>
          <div class="form-group">
            <label for="password" class="label-text">Password *</label>
            <input type="password" class="form-control" required name="userpassword" id="userpassword" placeholder="Password" autocomplete="off">
			<span class="text-danger">@error('userpassword') {{ $message }} @enderror</span>
          </div>
		  <a href="#" class="forgot-link">Forgot Password?</a>
          <div class="form-group">
            <button type="submit" class="btn btn-info no-hover primary-bgcolor" style="text-decoration: none !important;">
				<i class="fa fa-lock"></i> SUBMIT
			</button>
          </div>

          <div class="form-group">
            <label class="label-register"><a style="cursor:pointer;" onclick="registerUser()">Register Now</a></label>
			<input type="hidden" name="usrtype" id="usrtype">
			<label class="label-message">In case you don`t have ID and password then register now to get it.</label>
          </div>

		  
        </form>
      </div>
    </div>
  </div>

</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>

  function handleRadioChange(radio) {
    var labels = document.querySelectorAll('.radio-button-label');
    labels.forEach(function(label) {
      label.classList.remove('checked');
    });

    radio.closest('label').classList.add('checked');
	$("#usrtype").val(radio.value);
  }


function registerUser() {
    var usertype = document.getElementById("usrtype").value;
    if (usertype === '') {
        bootbox.alert('PLEASE SELECT <b>DEPARTMENT</b> | <b>VENDOR</b> TO CONTINUE WITH REGISTRATION.');
        return false;
    }

    $.ajax({
        url: '{{ route("registration") }}',
        type: 'GET',
        data: {
            _token: '{{ csrf_token() }}',
            usertype: usertype
        },
        success: function(response) {
            if (response.redirect_url) {
                window.location.href = response.redirect_url;
            } else {
                alert("No redirect URL returned.");
            }
        },
        error: function(err) {
            alert("Something went wrong.");
        }
    });
}

$(document).ready(function () {
    history.pushState(null, null, location.href);
    window.onpopstate = function () {
        history.pushState(null, null, location.href);
    };
});
</script>

<script src="{{ asset('panel/assets/js/bootbox.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>

</body>
</html>