@extends('admin.admin_master')
@section('admin')
	@php
		$t = 1;
	@endphp
	<div class="main-content">
		<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
						<!-- PAGE CONTENT BEGINS -->
						<div class="tabbable" style="padding:0px 0px!important;">
							<ul class="nav nav-tabs padding-0">
								@if(in_array(1, Session::get('actions')))
									<li class="active">
										<a data-toggle="tab" href="#home">
											<i class="green ace-icon fa fa-plus-circle"></i>
											<span style="margin-top:-3px;">Create Price List</span>
										</a>
									</li>
								@endif
							</ul>


							<div class="tab-content no-border"
								style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
								@if(in_array(1, Session::get('actions')))
									<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
										<div class="row">
											<div class="row">
												<form name="frm" id="frm" action="{{ route('store.ratelist', 0)}}" method="post"
													enctype="multipart/form-data">
													@if(Session::has('success'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-success">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																{{ Session::get('success') }}
															</div>
														</div>
													@endif
													@if(Session::has('fail'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																{{ Session::get('fail') }}
															</div>
														</div>
													@endif
													@if(Session::has('duplicate'))
														<div class="col-sm-12">
															<div class="alert alert-block alert-danger">
																<button type="button" class="close" data-dismiss="alert">
																	<i class="ace-icon fa fa-times"></i>
																</button>
																<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
															</div>
														</div>
													@endif
													<div class="col-sm-12">
													<span class="text-danger">@error('rateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror</span>
													</div>
													@csrf
<div class="form-group">
	<div class="col-sm-2">
		Start Date<label id="req">*</label>
		<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
		<input type="hidden" name="rateid" id="rateid" value="{{$rateId}}">
		<input type="text" class="form-control" name="startDate" id="startDate" value="{{old('startingDate',date('d\-m\-Y',strtotime($startDate)))}}" placeholder="dd-mm-YYYY" required readonly autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

		<span class="text-danger">@error('startingDate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror</span>
	</div>

	<div class="col-sm-2">
		End Date<label id="req">*</label>
		<input type="text" class="form-control todays_dt_blank" name="endDate" id="endDate" value="{{old('endDate',date('d\-m\-Y',strtotime($endDate)))}}" placeholder="dd-mm-YYYY" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

		<span class="text-danger">@error('endDate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror</span>
	</div>
	<div class="col-sm-4">
		Category <label id="req">*</label>
		<select class="chosen-select form-control" name="categoryid" id="categoryid" autofocus required	onKeyPress="return OnKeyPress(this, event)"
			data-placeholder="Category" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
			@foreach ($category as $itm)
				<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid')) === $itm->categoryid ? 'selected' : '' }}>
					{{ strtoupper($itm->jobcategory) }}
				</option>
			@endforeach
		</select>

		<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i>@enderror</span>
	</div>
	<div class="col-sm-2">
		Increment (in %)<label id="req">*</label>
		<input type="text" class="form-control numbers" name="incremented_by" id="incremented_by" value="{{old('incremented_by')}}" placeholder="5%" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" />

		<span class="text-danger">@error('incremented_by') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i>@enderror</span>
	</div>

	<div class="col-sm-2">
		<button type="submit" class="btn btn-info myfrmbtn mt-25 width-100" tabindex="{{$t++}}">SUBMIT</button>
	</div>
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
@endsection