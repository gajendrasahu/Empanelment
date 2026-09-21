@extends('admin.admin_master')
@section('admin')
@php
$t=0;
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
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> UPDATE PROFILE</a></li>
		</ul>	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.profile',$data->profileid) }}" method="post" enctype="multipart/form-data">
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
					<div class="col-sm-2">
						CATEGORY<label id="req"></label>
						<select class="chosen-select form-control" name="categoryid" required id="categoryid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CATEGORY" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($servicecategory as $itm)
							<option value="{{ $itm->servicecategoryid }}" {{ intval(old('categoryid',$data->servicecategoryid))===$itm->servicecategoryid ? 'selected' : '' }}>{{ strtoupper($itm->servicecategory) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-2">
						SUB CATEGORY<label id="req"></label>
						<input type="hidden" name="oldcategoryid" id="oldcategoryid" value="{{$data->subcategoryid}}">
						<select class="chosen-select form-control" name="subcategoryid" id="subcategoryid" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SUB CATEGORY" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($servicesubcategory as $itm)
							<option value="{{ $itm->servicecategoryid }}" {{ intval(old('subcategoryid',$data->subcategoryid))===$itm->servicecategoryid ? 'selected' : '' }}>{{ strtoupper($itm->servicecategory) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('subcategoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						PROFILE NAME<label id="req">*</label>
						<input type="hidden" name="uid" id="uid" value="{{$data->userid}}">
						<input type="text" class="form-control" readonly name="profilename" id="profilename" value="{{old('fees',$data->profilename)}}" placeholder="PROFILE NAME" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required/>

						<span class="text-danger">@error('profilename') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>

					<div class="col-sm-2">
						FEES<label id="req">*</label>
						<input type="text" class="form-control numbers" name="fees" id="fees" value="{{old('fees',$data->fees)}}" placeholder="FEES" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required/>

						<span class="text-danger">@error('fees') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						EXPERIENCE<label id="req"></label>
						<select class="chosen-select form-control" name="experience" id="experience" required onKeyPress="return OnKeyPress(this, event)"  data-placeholder="EXPERIENCE" tabindex="{{$t++}}">
							@foreach ($experience as $itm)
							<option value="{{ $itm->experience }}" {{ intval(old('experience'))===$data->experience ? 'selected' : '' }}>{{ strtoupper($itm->experience) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('experience') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12">
						DESCRIPTION<label id="req">&nbsp;</label>
						<textarea class="form-control" name="description" id="description" placeholder="DESCRIPTION" autocomplete="off" rows="5" tabindex="{{$t++}}">{{old('description',$data->description)}}</textarea>
						<span class="text-danger">@error('description') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-12">&nbsp;</div>

					<div class="col-sm-4">
						PROFILE IMAGE (WIDTH=HEIGHT, MAX : 500 KB)<label id="req">&nbsp;</label>
						<input type="file" class="form-control" name="profilepic" id="profilepic" accept="image/*"/>						
						<span class="text-danger">@error('profilepic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn">UPDATE PROFILE</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>


<div id="recordlist" class="tab-pane" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px;">
			<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
<table style="width:100%; margin:0 auto;">
	<tr class="myheadbg" style="height:40px;">
		<td style="text-align:left; padding:0px 5px;">

<select name="customeLength" id="customeLength" class="selectbx">
	<option value="10">10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select>

<select class="select2" name="serid" id="serid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SERVICE NAME" onchange="loadData(1,'{{ route('profile.html') }}')">
	<option value=""></option>
	@foreach ($services as $itm)
	<option value="{{ $itm->serviceid }}">{{ strtoupper($itm->servicename) }}</option>
	@endforeach
</select>

<select class="select2" name="pid" id="pid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CATEGORY" onchange="loadData(1,'{{ route('profile.html') }}')">
	<option value=""></option>
	@foreach ($servicecategory as $itm)
	<option value="{{ $itm->servicecategoryid }}">{{ strtoupper($itm->servicecategory) }}</option>
	@endforeach
</select>
<select class="select2" name="scid" id="scid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SUB CATEGORY" onchange="loadData(1,'{{ route('profile.html') }}')">
	<option value=""></option>
	@foreach ($servicesubcategory as $itm)
	<option value="{{ $itm->servicecategoryid }}">{{ strtoupper($itm->servicecategory) }}</option>
	@endforeach
</select>



<input type="text" name="customSearch" id="customSearch" class="selectbx" placeholder="Search here.." autocomplete="off">

		</td>
	</tr>
</table>
<div class="tabledata">

</div>
			</form>
		</div>
	</div>
</div>



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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
$(document).ready(function() {	
	$("#categoryid").on("change", function() {
	  var selectedValue = $(this).val();
	  $("#subcategoryid").empty().trigger("chosen:updated");
	  $("#subcategoryid").prop("disabled", true).trigger("chosen:updated");
	  $.ajax({
	    url: '{{ route('servicesubcategorylist.html') }}',
	    type: 'GET',
	    data: { selectedValue: selectedValue },
	    dataType: 'json',
	    success: function(data) {
	      $("#subcategoryid").append("<option value=''></option>");
	      $.each(data, function(index, option) {
	        $("#subcategoryid").append("<option value='" + option.value + "'>" + option.label + "</option>");
	      });

	      $("#subcategoryid").prop("disabled", false).trigger("chosen:updated");
	    },
	    error: function(error) {
	      console.error('Error fetching data:', error);
	    }
	  });
	});

});
jQuery(function($) {

	$('#profilepic').ace_file_input({
		no_file:'No File ...',
		btn_choose:'PROFILE PIC',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	})
	.on('change', function() {
		var reader = new FileReader();
		reader.readAsDataURL(this.files[0]);
		reader.onload = function (e) {
		  var image = new Image();
		  image.src = e.target.result;
		  image.onload = function () {
			var height = this.height;
			var width = this.width;
			if (width!=height) 
			{
				bootbox.alert("IMAGE WIDTH AND HEIGHT MUST BE SAME");
				$('#profilepic').ace_file_input('reset_input');
				$('#profilepic').ace_file_input('reset_ui');
				$('#profilepic').ace_file_input('reset_input_field');
			}
		  };
		};			
	});
});
function loadData(page,rl) {
	var serid = document.getElementById("serid").value;
	var pid = document.getElementById("pid").value;
	var scid = document.getElementById("scid").value;
    $.ajax({
        url: ''+rl,
        type: 'GET',
        data: {serid:serid,pid:pid,scid:scid},
        success: function(response) {
            $(".tabledata").html(response);
			$('#dynamic-table tbody tr').on('click', function() {
			$(this).addClass("datatableselected").siblings().removeClass("datatableselected");
			});

		   var myTable = $('#dynamic-table').DataTable({
		        'columnDefs': [
		            { 'orderable': false, 'targets': [0,1,5,6,7,8,9] },
		            { 'bSearchable': false, 'targets' : [0,1,5,6,7,8,9] },
		        ],
		        'order': [],
		    });
			$('#dynamic-table_wrapper .row:first').hide();
		    $('#customSearch').on( 'keyup', function () {
		        myTable.search( this.value ).draw();
		    } );
		       
		    $('#customeLength').on('change', function () {
		        var customPageLength = parseInt($(this).val(), 10);
		        myTable.page.len(customPageLength).draw();
		    });
		    $.fn.dataTable.Buttons.defaults.dom.container.className = 'dt-buttons btn-overlap btn-group btn-overlap';

		    new $.fn.dataTable.Buttons( myTable, {
		        buttons: [
		          {
		            "extend": "print",
		            "text": "<i class='fa fa-print bigger-110 grey'></i>",
		            "className": "btn btn-white btn-primary btn-bold",
		            autoPrint: false,
		            message: 'CATEGORY LIST',
		          }       
		        ]
		    } );
		    myTable.buttons().container().appendTo( $('.tableTools-container') );
            
        },
    });
}
</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection