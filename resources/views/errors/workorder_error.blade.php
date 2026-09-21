@extends('admin.admin_master')
@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content" style="padding:10px 15px!important;">


			<div class="error-container">
				<div class="well">
					<h4 class="grey lighter smaller">
						<span class="blue bigger-125">
							<i class="ace-icon fa fa-random"></i>
						</span>
						Something Went Wrong
					</h4>

					<hr />
					<div class="space"></div>
					
					<div class="alert alert-block alert-danger">
						{{$message}}
					</div>
					
					<div>
						<h4 class="lighter smaller">
							please don’t hit the back button or refresh — this can interrupt the process or cause double transaction.
						</h4>
					</div>
					
					<hr />
					<div class="space"></div>

					<div class="center">
						<a href="{{ route('view.pptresumes',$requestid)}}" class="btn btn-primary">
							<i class="ace-icon fa fa-users"></i>
							Go Back to Participation Page
						</a>
					</div>
				</div>
			</div>

			
		</div><!-- /.page-content -->	
	</div>
</div>
@endsection