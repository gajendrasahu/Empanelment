<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>WELCOME TO DASH BOARD</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-9ndCyUaIbzAi2FUVXJi0CjmCapSmO7SnpJef0486qhLnuZ2cdeRhO02iuK6FUUVM" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<div class="row">			

			<div class="col-md-4 col-md-offset-4" style="margin-top: 20px;">
				<h4>WELCOME TO DASHBOARD</h4>
				<hr>
			    @if(Session::has('success'))
			        <div class="alert alert-success"> {{ Session::get('success') }}</div>
			    @endif

				<table style="width:100%;">
						<thead>							
							<tr>
								<th>User Name</th>
								<th>Email</th>
								<th>Action</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>{{ $data->UserName}}</td>
								<td>{{ $data->EmailID}}</td>
								<td><a href="{{ route('logout')}}">Logout</a></td>
							</tr>
						</tbody>
						<tfoot>
							
						</tfoot>
				</table>
			</div>
		</div>
	</div>

</body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
</html>