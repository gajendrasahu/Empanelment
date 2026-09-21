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
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.branchtab')}}
				</a>
			</li>
		@endif
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('branch.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> {{__('labels.branchlisttab')}}</a></li>
		@endif

		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.branch',0)}}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.basicdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.branchname')}}<label id="req">*</label>
						<input type="text" class="form-control" name="branchname" id="branchname" value="{{old('branchname')}}" placeholder="{{__('labels.branchname')}}" autofocus autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('branchname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.branchcode')}}<label id="req">*</label>
						<input type="text" class="form-control" name="branchcode" id="branchcode" value="{{old('branchcode')}}" placeholder="{{__('labels.branchcode')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('branchcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.branchcontact')}} <label id="req">*</label>
						<input type="text" class="form-control" name="contact1" id="contact1" value="{{old('contact1')}}" placeholder="{{__('labels.branchcontact')}}" required autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('contact1') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.alternetcontact')}} <label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="contact2" id="contact2" value="{{old('contact2')}}" placeholder="{{__('labels.alternetcontact')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('contact2') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.branchemail')}}<label id="req">*</label>
						<input type="text" class="form-control" name="branchemail" id="branchemail" value="{{old('branchemail')}}" placeholder="{{__('labels.branchemail')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('branchemail') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.branchpannumber')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="branchpannumber" id="branchpannumber" value="{{old('branchpannumber')}}" placeholder="{{__('labels.branchpannumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('branchpannumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.branchgstnumber')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="branchgstnumber" id="branchgstnumber" value="{{old('branchgstnumber')}}" placeholder="{{__('labels.branchgstnumber')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('branchgstnumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.addressdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.statename')}}<label id="req">*</label>
						@if($addstate->ispermitted==1)
						<span style="float:right;" class="mytheame-background">
							<i class="fa fa-plus-circle" onclick="AddState('{{ route('store.statename') }}')"></i>
						</span>
						@endif
						<select class="chosen-select form-control" name="stateid" id="stateid" autofocus required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="GetCities(this.value,'{{ route('cities.list') }}')">
							<option value=""></option>
							@foreach ($state as $itm)
							<option value="{{ $itm->stateid }}" {{ intval(old('stateid'))===$itm->stateid ? 'selected' : '' }}>{{ strtoupper($itm->statename) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('stateid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.cityname')}}<label id="req">*</label>
						@if($addcity->ispermitted==1)
						<span style="float:right;" class="mytheame-background">
							<i class="fa fa-plus-circle" onclick="AddCity('{{ route('store.cityname') }}')"></i>
						</span>
						@endif
						<select class="chosen-select form-control" name="cityid" id="cityid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
						</select>

						<span class="text-danger">@error('cityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.district')}}<label id="req">*</label>
						<input type="text" class="form-control" name="district" id="district" value="{{old('district')}}" placeholder="{{__('labels.district')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('district') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.postalcode')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="postalcode" id="postalcode" value="{{old('postalcode')}}" placeholder="{{__('labels.postalcode')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>

						<span class="text-danger">@error('postalcode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						{{__('labels.completeaddress')}}<label id="req">*</label>
						<input type="text" class="form-control" name="completeaddress" id="completeaddress" value="{{old('completeaddress')}}" placeholder="{{__('labels.completeaddress')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('completeaddress') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.bankdetail')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.bankname')}}<label id="req">*</label>
						<input type="text" class="form-control" name="bankname" id="bankname" value="{{old('bankname')}}" placeholder="{{__('labels.bankname')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('bankname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.bankaccount')}}<label id="req">*</label>
						<input type="text" class="form-control" name="bankaccount" id="bankaccount" value="{{old('bankaccount')}}" placeholder="{{__('labels.bankaccount')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('bankaccount') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.ifsccode')}}<label id="req">*</label>
						<input type="text" class="form-control" name="ifsccode" id="ifsccode" value="{{old('ifsccode')}}" placeholder="{{__('labels.ifsccode')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('ifsccode') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.bankbranch')}}<label id="req">&nbsp;</label>
						<input type="text" class="form-control" name="bankbranch" id="bankbranch" value="{{old('bankbranch')}}" placeholder="{{__('labels.bankbranch')}}" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>

						<span class="text-danger">@error('bankbranch') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px;">{{__('labels.operationinformation')}}</div></div>
					<div class="col-sm-12">&nbsp;</div>						
					<div class="col-sm-3">
						{{__('labels.operatingcity')}}<label id="req">*</label>
						<select class="chosen-select form-control" name="operatingcityid" id="operatingcityid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.operatingcity')}}" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($operatingcity as $itm)
							<option value="{{ $itm->cityid }}" {{ intval(old('operatingcityid'))===$itm->cityid ? 'selected' : '' }}>{{ strtoupper($itm->cityname) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('operatingcityid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.operatingdepartment')}}<label id="req">*</label>
						<input type="hidden" name="departmentids" id="departmentids" value="{{ old('departmentids')}}">
						<select class="form-control" name="departmentid[]" id="departmentid" multiple onchange="AddDepartments()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="None" required>
							@foreach ($departments as $itm)
<option value="{{ $itm->departmentid }}" @if(is_array(old('departmentid')) && in_array($itm->departmentid, old('departmentid'))) selected @endif>{{ strtoupper($itm->departmentname) }}</option>
							@endforeach
						
						</select>
						<span class="text-danger">@error('departmentids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						{{__('labels.operatingcategory')}}<label id="req">*</label>
						<input type="hidden" name="categoryids" id="categoryids" value="{{ old('categoryids')}}">
						<select class="form-control" name="categoryid[]" id="categoryid" multiple onchange="AddCategories()" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"  data-placeholder="None" required>
							@foreach ($categories as $itm)
<option value="{{ $itm->categoryid }}" @if(is_array(old('categoryid')) && in_array($itm->categoryid, old('categoryid'))) selected @endif>{{ strtoupper($itm->category) }}</option>
							@endforeach
						
						</select>
						<span class="text-danger">@error('categoryids') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">{{__('common.submit')}}</button>
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane @if(!in_array(1,Session::get('actions'))) in active @endif" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped table-hover" id="tablerecords">
				<thead>
				<tr>
					<td colspan="22">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('branch.html') }}')">
							<option value="15">15</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="stid" id="stid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.statename')}}" onchange="loadData(1,'{{ route('branch.html') }}')">
							<option value=""></option>
							@foreach ($state as $itm)
							<option value="{{ $itm->stateid }}">{{ strtoupper($itm->statename) }}</option>
							@endforeach
						</select>
						<select class="select2" name="ctid" id="ctid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.cityname')}}" onchange="loadData(1,'{{ route('branch.html') }}')">
							<option value=""></option>
							@foreach ($city as $itm)
							<option value="{{ $itm->cityid }}">{{ strtoupper($itm->cityname) }}</option>
							@endforeach
						</select>
						<select class="select2" name="opctid" id="opctid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.operatingcity')}}" onchange="loadData(1,'{{ route('branch.html') }}')">
							<option value=""></option>
							@foreach ($operatingcity as $itm)
							<option value="{{ $itm->cityid }}">{{ strtoupper($itm->cityname) }}</option>
							@endforeach
						</select>
						
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('branch.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
					</div>
					</td>
				</tr>
				<tr class="myhead">
					<td style="width:25px;" nowrap><b>{{__('labels.sno')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchname')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchcode')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchcontact')}}</b></td>
					<td nowrap style=""><b>{{__('labels.alternetcontact')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchemail')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchpannumber')}}</b></td>
					<td nowrap style=""><b>{{__('labels.branchgstnumber')}}</b></td>
					<td nowrap style=""><b>{{__('labels.statename')}}</b></td>
					<td nowrap style=""><b>{{__('labels.cityname')}}</b></td>
					<td nowrap style=""><b>{{__('labels.district')}}</b></td>
					<td nowrap style=""><b>{{__('labels.postalcode')}}</b></td>
					<td nowrap style=""><b>{{__('labels.completeaddress')}}</b></td>
					<td nowrap style=""><b>{{__('labels.bankname')}}</b></td>
					<td nowrap style=""><b>{{__('labels.bankaccount')}}</b></td>
					<td nowrap style=""><b>{{__('labels.ifsccode')}}</b></td>
					<td nowrap style=""><b>{{__('labels.bankbranch')}}</b></td>
					<td nowrap style=""><b>{{__('labels.operatingcity')}}</b></td>
					<td nowrap style=""><b>{{__('labels.operatingdepartment')}}</b></td>
					<td nowrap style=""><b>{{__('labels.operatingcategory')}}</b></td>
					<td style="width:25px;"></td>
					<td style="width:25px;"></td>
				</tr>
				</thead>
				<tbody class="tabledata">
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


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>




	
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/bootstrap-multiselect.min.js') }}"></script>
<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif

function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var stateid			=	document.getElementById("stid").value;
	var cityid			=	document.getElementById("ctid").value;
	var operatingcityid	=	document.getElementById("opctid").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		stateid:stateid,
		cityid:cityid,
		operatingcityid:operatingcityid
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

$('#departmentid').multiselect({
	 enableCaseInsensitiveFiltering:1,
	 enableClickableOptGroups:1,
	 enableHTML:1,
	 maxHeight:200,		 
	 buttonClass: 'form-control',
	 buttonWidth: '100%',
	 buttonHeight: '100%',
});	

$('#categoryid').multiselect({
	 enableCaseInsensitiveFiltering:1,
	 enableClickableOptGroups:1,
	 enableHTML:1,
	 maxHeight:200,		 
	 buttonClass: 'form-control',
	 buttonWidth: '100%',
	 buttonHeight: '100%',
});	

</script>
<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/multiselectfunctions.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection