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
      <img src="{{ asset('panel/assets/images/basic/login.png') }}" alt="Login Image" style="width:70%; vertical-align:top!important;">
    </div>

    <!-- Login Form Column -->
    <div class="col-sm-6 col-xs-12 form-box">
      <div class="login-box text-center">
  
        <h3 class="text-center primary-color font-bold" style="margin-bottom:0px;">Registration submitted Successfully!</h3>
		<span class="small-text text-center" style="display: block; margin-bottom: 20px;">
		  Thank you for registering. Your details have been received and are under review by the CHiPS Admin team.
		</span>
		<img class="text-center" src="{{asset('panel/assets/images/basic/tick_mark.png')}}" style="width:100px;">
		<br><br>
		<a href="{{route('loginpanel')}}"><button type="button" class="btn btn-info no-hover primary-bgcolor" style="text-decoration: none !important; width:150px; border-radius:5px;">
			Back To Login
		</button>
		</a>
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
        bootbox.alert('PLEASE SELECT <b>DEPARTMENT</b> | <b>IMPELLENT</b> | <b>VENDOR</b> TO CONTINUE WITH REGISTRATION.');
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

<script src="{{ asset('panel/assets/js/bootbox.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>

</body>
</html>