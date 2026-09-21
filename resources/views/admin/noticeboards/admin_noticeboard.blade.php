@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
		
	@endphp
	<div class="main-content">
	<style>
.select2-results__group {
    background-color: #f0f0f0;
    color: #000;
    font-weight: bold;
    padding: 5px;
}	
	</style>
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable animated bounceInUp" style="padding:0px 0px!important;">
					
							<div class="tab-content no-border" style="min-height:100px; padding:0px 10px;">

								@if(in_array(2, Session::get('actions')) || in_array(3, Session::get('actions')) || in_array(12, Session::get('actions')))
									<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
										<div class="row" style="padding:0px;">
											<div class="col-xs-12">
												<form name="pagedata" id="pagedata" action="#" method="post"
													onsubmit="return false;">
													@csrf
													<div class="table-responsive">
														<table class="pd-8">
															<tbody class="tabledata">
																<tr>
																	<td class="center padding-8">--Search Record--
																	</td>
																</tr>
															</tbody>
														</table>
													</div>
												</form>
											</div>
										</div>
									</div>
								@endif


							</div>
						</div>



					</div><!-- /.col -->
				</div><!-- /.row -->
			</div><!-- /.page-content -->


		</div>
	</div>



<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>

<script>
	$(document).ready(function () {
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		// FIX: load table data on first page load
		loadData(1, '{{ route('adminnotice.html') }}');
	});
	function loadData(page, r1)
	{
		$.get("" + r1,
		{
		},
		function (data, status) {
			$(".tabledata").html(data);
		});
	}

setTimeout(function(){ window.location.reload();},120000);
</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection
