@extends('admin.admin_master')
@section('admin')
@php
$t=1;
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
		@if(in_array(1,Session::get('actions')))
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> Update Remuneration</a></li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))		
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.remuneration',$data->remunerationid)}}" method="post" enctype="multipart/form-data">
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
				@csrf
				<div class="form-group">
					@if($data->categoryid==1)
					<div class="col-sm-3">
						Category <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="categoryid" id="categoryid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Category" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus onchange="GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')">
							@foreach ($category as $itm)
							@if($data->categoryid==$itm->categoryid)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',$data->categoryid))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
							@endif
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						Tier <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="tierid" id="tierid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Tier" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							@if(old('tierid',$data->categoryid)!=0)
							@php
								$tier	=	DB::table('pricing_tbl as a')
												->select('b.tierid','b.tiername')
												->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
												->where('a.categoryid','=',$data->categoryid)
												->orderby('b.tiername')
												->get();
							@endphp
							@endif
							@foreach ($tier as $itm)
							<option value="{{ $itm->tierid }}" {{ intval(old('tierid',$data->tierid))===$itm->tierid ? 'selected' : '' }}>{{ strtoupper($itm->tiername) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('tierid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Experience <label id="req">*</label>
						<select class="chosen-select form-control" name="experienceid" id="experienceid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Experience" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							@foreach ($experience as $itm)
							<option value="{{ $itm->experienceid }}" {{ intval(old('experienceid',$data->experienceid))===$itm->experienceid ? 'selected' : '' }}>{{ strtoupper($itm->workexperience) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('experienceid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					
					<div class="col-sm-2">
						Level <label id="req">*</label>
						<input type="text" class="form-control numbers" name="experiencelevel" id="experiencelevel" value="{{old('experiencelevel',$data->experiencelevel)}}" placeholder="LEVEL" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('experiencelevel') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Remuneration <label id="req">*</label>
						<input type="text" class="form-control numbers" name="remuneration" id="remuneration" value="{{old('remuneration',$data->remuneration)}}" placeholder="Remuneration" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('remuneration') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-10">&nbsp;</div>					
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">SUBMIT</button>
					</div>
					@endif
					@if($data->categoryid==2)
					<div class="col-sm-3">
						Category <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="categoryid" id="categoryid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Category" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus onchange="GetTiersWithId(this.value,'{{ route('tiers.list') }}','tierid')">
							@foreach ($category as $itm)
							@if($data->categoryid==$itm->categoryid)
							<option value="{{ $itm->categoryid }}" {{ intval(old('categoryid',$data->categoryid))===$itm->categoryid ? 'selected' : '' }}>{{ strtoupper($itm->jobcategory) }}</option>
							@endif
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						Tier <label id="req">*</label>
						<input type="hidden" name="form_token" id="form_token" value="{{$token}}">
						<select class="chosen-select form-control" name="tierid" id="tierid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Tier" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							@if(old('tierid',$data->categoryid)!=0)
							@php
								$tier	=	DB::table('pricing_tbl as a')
												->select('b.tierid','b.tiername')
												->leftJoin('tiermaster_tbl as b','b.tierid','=','a.tierid')
												->where('a.categoryid','=',$data->categoryid)
												->orderby('b.tiername')
												->get();
							@endphp
							@endif
							@foreach ($tier as $itm)
							<option value="{{ $itm->tierid }}" {{ intval(old('tierid',$data->tierid))===$itm->tierid ? 'selected' : '' }}>{{ strtoupper($itm->tiername) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('tierid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-6">
						Sector <label id="req">*</label>
						<select class="chosen-select form-control" name="sectorid" id="sectorid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Sector" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							@foreach ($sector as $itm)
							<option value="{{ $itm->sectorid }}" {{ intval(old('sectorid',$data->sectorid))===$itm->sectorid ? 'selected' : '' }}>{{ strtoupper($itm->sectorname) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('sectorid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-6">
						Position <label id="req">*</label>
						<select class="chosen-select form-control" name="positionid" id="positionid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="Position" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus>
							<option value=""></option>
							@foreach ($position as $itm)
							<option value="{{ $itm->positionid }}" {{ intval(old('positionid',$data->positionid))===$itm->positionid ? 'selected' : '' }}>{{ strtoupper($itm->consultantposition) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('positionid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						Remuneration <label id="req">*</label>
						<input type="text" class="form-control numbers" name="remuneration" id="remuneration" value="{{old('remuneration',$data->remuneration)}}" placeholder="Remuneration" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('remuneration') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">SUBMIT</button>
					</div>
					@endif
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
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection