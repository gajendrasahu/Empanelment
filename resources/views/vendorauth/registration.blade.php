<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>CUSTOME REGISTRATION</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<div class="row">			
			<div class="col-md-4 col-md-offset-4" style="margin-top: 20px;">
				<h4>REGISTRATION</h4>
				<hr>
<form method="post" action="{{ route('registration') }}">
	@if(Session::has('success'))
		<div class="alert alert-success">{{ Session::get('success') }}</div>
	@endif
	@if(Session::has('fail'))
		<div class="alert alert-danger">{{ Session::get('fail') }}</div>
	@endif
	@csrf
	<div class="form-group">
		<label for="name">User Name</label>
		<input type="text" class="form-control" placeholder="Enter user name" name="UserName" id="UserName" style="border-radius:0px;" value="{{ old('UserName')}}">
		<span class="text-danger">@error('UserName') {{ $message }} @enderror</span>
	</div>
	<div class="form-group">
		<label for="name" style="margin-top:10px;">Password</label>
		<input type="password" class="form-control" placeholder="Enter password" name="Password" id="Password" style="border-radius:0px;" value="{{ old('Password')}}">
		<span class="text-danger">@error('Password') {{ $message }} @enderror</span>
	</div>
	<div class="form-group">
		<label for="name" style="margin-top:10px;">Email</label>
		<input type="text" class="form-control" placeholder="Enter email" name="EmailID" id="EmailID" style="border-radius:0px;" value="{{ old('EmailID')}}">
		<span class="text-danger">@error('EmailID') {{ $message }} @enderror</span>
	</div>
	<div class="form-group">
		<label for="name" style="margin-top:10px;">Email Password</label>
		<input type="password" class="form-control" placeholder="Enter email password" name="EmailPW" id="EmailPW" style="border-radius:0px;" value="{{ old('EmailPW')}}">
		<span class="text-danger">@error('EmailPW') {{ $message }} @enderror</span>
	</div>
	<div class="form-group" style="text-align:right;">
		<br>
		<a href="{{ route('login') }}" style="float:left; vertical-align:bottom; line-height:60px; text-decoration:underline;">Already Registered !! Login Here.</a>
		<button type="submit" class="btn btn-primary" style="border-radius:0px;">Register</button>
	</div>
	
</form>
			</div>
		</div>
	</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>