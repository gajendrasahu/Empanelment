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
<form name="loginForm" id="loginForm" method="POST" action="{{ route('login-user') }}">
@csrf

    <div class="login-container">
        <div class="login-header">
            <div class="logo"><img src="{{ asset('panel/assets/images/basic/logo.png')}}" style="width:80px!important;"></div>
            <div class="org-name">Chhattisgarh Infotech Promotion Society | Government of Chhattisgarh</div>
            <h1 class="portal-title">CHiPS Empanelment Portal</h1>
        </div>
		<!--
        <div class="login-as">Login As</div>
		
        <div class="user-types">
            <div class="user-type @if(old('usertype')=='DEPARTMENT' || old('usertype')=='') active @endif" data-type="department">
				<i class="fa fa-check-circle department-check-box" style="font-size:16px; color:#025964; position:absolute; right:5px;"></i>
                <div class="user-type-icon"><i class="fa fa-building-o"></i></div>
                <div class="user-type-label">Department</div>
            </div>
            <div class="user-type @if(old('usertype')=='VENDOR') active @endif" data-type="vendor">
				<i class="fa fa-check-circle vendor-check-box" style="font-size:16px; color:#025964; display:none; position:absolute; right:5px;"></i>
                <div class="user-type-icon"><i class="fa fa-user"></i></div>
                <div class="user-type-label">Vendor</div>
            </div>
            <div class="user-type @if(old('usertype')=='ADMIN') active @endif" data-type="admin">
				<i class="fa fa-check-circle admin-check-box" style="font-size:16px; color:#025964; display:none; position:absolute; right:5px;"></i>
                <div class="user-type-icon"><i class="fa fa-cogs"></i></div>
                <div class="user-type-label">Admin</div>
            </div>
        </div>
		-->
		<div class="login-as">Enter credentials here</div>
		@if(Session::has('success'))
			<div class="alert alert-success text-left">{{ Session::get('success') }}</div>
		@endif
		@if(Session::has('fail'))
			<div class="alert alert-danger text-left"><i class="fa fa-warning"></i> {!! Session::get('fail') !!}</div>
		@endif

		<div class="form-group">
			<label class="form-label" for="userId">
				Email <span class="required">*</span>
			</label>
			<input type="hidden" name="usertype" id="usertype" value="{{old('usertype','DEPARTMENT')}}">
			<input type="text" class="form-input" required name="username" id="username" value="{{old('username')}}" placeholder="Enter your email address" autocomplete="off">
			<span class="text-danger">@error('username') {{ $message }} @enderror</span>
		</div>

		<div class="form-group">
			<label class="form-label" for="password">
				Password <span class="required">*</span>
			</label>
			<div class="password-container">
				<input type="password" class="form-input" required name="userpassword" id="userpassword" placeholder="Enter your password" autocomplete="off">
				<button type="button" class="password-toggle" onclick="togglePassword()">👁️</button>
				<span class="text-danger">@error('userpassword') {{ $message }} @enderror</span>
			</div>
		</div>


		<button type="submit" class="submit-btn">Login</button>
		<div class="form-row">
			<div class="checkbox-container"></div>
			<a href="{{route('forgotpassword')}}">Forgot password?</a>
		</div>
        
		<!--
        <div class="register-section">
            <div class="register-text"></div>
            <button type="button" class="register-btn" onclick="registerUser()">Register Now</button>
        </div>
		-->
    </div>
</form>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
	// Handle user type selection
	document.querySelectorAll('.user-type').forEach(type => {
		type.addEventListener('click', function() {
			// Remove active class from all types
			document.querySelectorAll('.user-type').forEach(t => t.classList.remove('active'));
			// Add active class to clicked type
			this.classList.add('active');
			const userType = this.getAttribute('data-type').toUpperCase();
			
			$(".department-check-box").css("display","none");
			$(".vendor-check-box").css("display","none");
			$(".admin-check-box").css("display","none");
			if(userType=='DEPARTMENT')
			{
				$(".department-check-box").css("display","");
				$(".department-check-box").prop("checked","checked");
			}
			if(userType=='VENDOR')
			{
				$(".vendor-check-box").css("display","");
				$(".vendor-check-box").prop("checked","checked");
			}
			if(userType=='ADMIN')
			{
				$(".admin-check-box").css("display","");
				$(".admin-check-box").prop("checked","checked");
			}
			document.getElementById("usertype").value=userType;
		});
	});

	// Toggle password visibility
	function togglePassword() {
		const passwordInput = document.getElementById('userpassword');
		const toggleBtn = document.querySelector('.password-toggle');
		
		if (passwordInput.type === 'password') {
			passwordInput.type = 'text';
			toggleBtn.textContent = '👁️';
		} else {
			passwordInput.type = 'password';
			toggleBtn.textContent = '👁️';
		}
	}

	// Handle registration
	function handleRegister() {
		alert('Redirecting to registration page...');
		// Here you would typically redirect to registration page
	}

	// Handle forgot password
	document.querySelector('.forgot-password').addEventListener('click', function(e) {
		e.preventDefault();
		alert('Redirecting to password recovery...');
		// Here you would typically redirect to password recovery page
	});
	


function registerUser() {
    var usertype = document.getElementById("usertype").value;
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
	
</script>
</body>
</html>