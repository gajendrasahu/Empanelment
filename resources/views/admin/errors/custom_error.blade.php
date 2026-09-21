@extends('admin.admin_master')
@section('admin')

<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content" style="padding:10px 15px!important;">


			<div class="error-container">
				<div class="well">
					<h1 class="grey lighter smaller">
						<span class="blue bigger-125">
							<i class="ace-icon fa fa-random"></i>
						</span>
						Something Went Wrong
					</h1>

					<hr />
					<div class="space"></div>
					@if(Session::has('fail'))
					<div class="alert alert-block alert-danger">
						<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{!! Session::get('fail') !!}
					</div>
					@endif

					<div>
						<h4 class="lighter smaller">please don’t hit the back button or refresh — this can interrupt the process or cause double payments.</h4>

					</div>

					<hr />
					<div class="space"></div>

				</div>
			</div>

			
		</div><!-- /.page-content -->	
	</div>
</div>
@endsection