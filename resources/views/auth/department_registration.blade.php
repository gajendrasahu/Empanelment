@php $t=0; @endphp
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
  <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <div class="col-sm-6 col-xs-12 form-box" style="margin-top:-40px;">
      <div class="login-box">
		<span class="small-text text-left" style="display: block; margin-top:-20px; float:right;">
		  * Indicates mandatory fields
		</span>
		<h5 style="margin-bottom:0px;">
			<a href="{{route('loginpanel')}}" style="text-decoration:none!important;" class="no-hover"><i class="fa fa-arrow-left"></i> Back To Login</a>
		</h5>
        <h3 class="text-left primary-color" style="margin-bottom:0px; font-weight:bold;">Create Your Account - Join The Portal!</h3>
		<span class="small-text text-left" style="display: block; margin-bottom: 20px;">
		  Register as a department
		</span>
        <form name="registerdepartment" id="registerdepartment" method="POST">
		@csrf

		  <div class="row text-center" style="margin-bottom:20px; padding:0px 10px;">
			@if(Session::has('success'))
				<div class="alert alert-success text-left">{{ Session::get('success') }}</div>
			@endif
			@if(Session::has('fail'))
				<div class="alert alert-danger text-left"><i class="fa fa-warning"></i> {{ Session::get('fail') }}</div>
			@endif

			<div class="col-xs-6 col-sm-6 p-0">
			  <label class="radio-button-label {{ old('departmenttype')==='INSIDE' ? 'checked' : '' }}">
				<input type="radio" required name="departmenttype" value="INSIDE" onchange="handleRadioChange(this)" {{ old('departmenttype')==='INSIDE' ? 'checked' : '' }}>
				IN-SIDE DEPARTMENT
			  </label>
			</div>
			
			<div class="col-xs-6 col-sm-6 p-0">
			  <label class="radio-button-label {{ old('departmenttype')==='OUTSIDE' ? 'checked' : '' }}">
				<input type="radio" required name="departmenttype" value="OUTSIDE" onchange="handleRadioChange(this)" {{ old('departmenttype')==='OUTSIDE' ? 'checked' : '' }}>
				OUT-SIDE DEPARTMENT
			  </label>
			</div>
		  </div>		
		  <div class="row">
			<div class="col-sm-6">
				Department Name <label id="req">*</label>
				<input type="text" class="form-control" name="departmentname" id="departmentname" value="{{old('departmentname')}}" placeholder="Department Name"  autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
				
				<span class="text-danger">@error('departmentname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-6">
				Project / Division Name <label id="req">&nbsp;</label>
				<input type="text" class="form-control" name="projectname" id="projectname" value="{{old('projectname')}}" placeholder="Project / Division Name"  autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
				
				<span class="text-danger">@error('projectname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-12">&nbsp;</div>
			<div class="col-sm-6">
				Authorized Person Name <label id="req">*</label>
				<input type="text" class="form-control" name="personname" id="personname" value="{{old('personname')}}" placeholder="Authorized Person Name" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

				<span class="text-danger">@error('personname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-6">
				Designation <label id="req">*</label>
				<input type="text" class="form-control" name="designation" id="designation" value="{{old('designation')}}" placeholder="Designation" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

				<span class="text-danger">@error('designation') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-12">&nbsp;</div>

			<div class="col-sm-6">
				Mobile Number <i>(login id)</i><label id="req">*</label>
				<span class="input-icon" style="width:100%;">
					<input type="text" class="form-control" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber')}}" placeholder="Mobile Number" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
					<i class="ace-icon"><img src="{{asset('panel/assets/images/basic/india-flag.png')}}" style="width:16px; margin-top:-2px;"></i>
				</span>

				<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-6">
				Official Email Id <i>(login id)</i><label id="req">*</label>
				<span class="input-icon" style="width:100%;">
					<input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" placeholder="Official Email Id" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>
					<i class="fa fa-envelope ace-icon"></i>
				</span>

				<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-12">&nbsp;</div>
			<div class="col-sm-6">
				Create Password <label id="req">*</label>
				<input type="password" class="form-control" name="loginpassword" id="loginpassword" value="{{old('loginpassword')}}" placeholder="Create Password" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

				<span class="text-danger">@error('loginpassword') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			<div class="col-sm-6">
				Confirm Password <label id="req">*</label>
				<input type="password" class="form-control" name="confirmpassword" id="confirmpassword" value="{{old('confirmpassword')}}" placeholder="Confirm Password" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

				<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
			</div>
			
		</div>
          <div class="form-group">
		  
          </div>
          <div class="form-group">
		  <input type="hidden" name="depttype" id="depttype">
            <button type="button" class="btn btn-info no-hover primary-bgcolor" id="generateOtp" style="text-decoration: none !important; font-weight:bold;">
				Register Now <i class="fa fa-angle-double-right"></i>
			</button>
          </div>


<!-- MODEL -->
<div class="modal fade" id="otpverification" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content otp-modal-content">
      
      <div class="modal-header">
        <h5 class="modal-title w-100 text-center">OTP Verification</h5>
        
      </div>

      <div class="modal-body text-center">
        <div class="otp-container mx-auto">
          
          <!-- OTP Inputs -->
          <div class="otp-input-group">
            <input type="text" id="first" name="first" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="second" name="second" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="third" name="third" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="fourth" name="fourth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="fifth" name="fifth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
            <input type="text" id="sixth" name="sixth" class="form-control text-center otp-input" maxlength="1" onkeypress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autocomplete="off" />
          </div>

          <!-- Button -->
          <button type="button" class="btn btn-info otp-button" id="verifyOtp" tabindex="{{$t++}}">
            Verify OTP
          </button>

          <!-- Message -->
          <div class="msg text-danger mt-2">&nbsp;</div>

          <!-- Resend -->
          <p class="text-center mt-2 mb-0">
            <small>Didn't receive the code? <a href="#" id="resendOtp">Resend</a></small>
          </p>
        </div>
      </div>

    </div>
  </div>
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
	$("#depttype").val(radio.value);
  }


</script>
<script>
    $('#generateOtp').click(function (e) {
		$("#generateOtp").prop("disabled","disabled");
		$(".msg").html('&nbsp;');
        e.preventDefault();
        var formData = new FormData(document.getElementById('registerdepartment'));
        $.ajax({
            url: '{{route("generate_otp")}}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response)
			{
				if(response.status===200)
				{
					$("#otpverification").modal("show");
					setTimeout(function() { $("#first").focus(); },1000);
				}
				if(response.status===400)
				{
					bootbox.alert(response.message);
				}
            },
            error: function (xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					var errors = xhr.responseJSON.errors;
					var allMessages = '';

					$.each(errors, function(field, messages) {
						$.each(messages, function(index, msg) {
							allMessages += '<i class="fa fa-hand-o-right"></i> '+ msg + '<br>';
						});
					});
					bootbox.alert(allMessages);
					$("#generateOtp").prop("disabled","");
				}			
            }
        });
    });

    $('#verifyOtp').click(function (e) {
		$("#verifyOtp").prop("disabled","disabled");
        e.preventDefault();
		var allFilled = true;
		$('.otp-input').each(function() {
			if ($.trim($(this).val()) === '') {
				allFilled = false;
				return false;
			}
		});		
		if(!allFilled)
		{
			$("#verifyOtp").prop("disabled","");
			$("#generateOtp").prop("disabled","");
			$(".msg").html('Please enter OTP.');
			return false;
		}
        var formData = new FormData(document.getElementById('registerdepartment'));
        $.ajax({
            url: '{{route("verify_department_otp")}}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response)
			{
				if(response.redirect_url)
				{
					$("#verifyOtp").prop("disabled","disabled");
					$("#generateOtp").prop("disabled","disabled");
					$(".msg").html('<i class="fa fa-check-circle"></i> VERIFIED');
					setTimeout(function() { window.location.href = response.redirect_url; },2000);
				}
				if(response.status===400)
				{
					$("#verifyOtp").prop("disabled","");
					bootbox.alert(response.message);
				}
            },
            error: function (xhr) {
				if (xhr.responseJSON && xhr.responseJSON.errors) {
					var errors = xhr.responseJSON.errors;
					var allMessages = '';

					$.each(errors, function(field, messages) {
						$.each(messages, function(index, msg) {
							allMessages += msg + '<br>';
						});
					});
					$(".msg").html(allMessages);
					//bootbox.alert(allMessages);
					$("#verifyOtp").prop("disabled","");
					$("#generateOtp").prop("disabled","");
				}			
            }
        });
    });



$(document).ready(function () {
  var inputs = $('.otp-input');

  inputs.on('keyup', function (e) {
    var key = e.keyCode || e.which;
    var $this = $(this);

    if ($this.val().length === 1 && key !== 8 && key !== 37) {
      // Move to next input
      var index = inputs.index(this);
      if (index !== -1 && index < inputs.length - 1) {
        inputs.eq(index + 1).focus();
      }
    } else if ((key === 8 || key === 37)) {
      // Move to previous input
      var index = inputs.index(this);
      if (index > 0) {
        inputs.eq(index - 1).focus();
      }
    }
  });

  // Optional: handle right arrow key
  inputs.on('keydown', function (e) {
    var key = e.keyCode || e.which;
    if (key === 39) {
      var index = inputs.index(this);
      if (index < inputs.length - 1) {
        inputs.eq(index + 1).focus();
      }
    }
  });
});
</script>
<script src="{{ asset('panel/assets/js/forwarder.js') }} "></script>
<script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootbox.js') }}"></script>
</body>
</html>