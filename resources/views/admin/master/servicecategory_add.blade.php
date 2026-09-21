@extends('admin.admin_master')
@section('admin')
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
				<a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> ADD SERVICE CATEGORY</a>
			</li>
		@endif
		@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
			<li @if(!in_array(1,Session::get('actions'))) class="active" @endif>
				<a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('servicecategory.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> SERVICE CATEGORY LIST</a>
			</li>
		@endif
		</ul>	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.servicecategory')}}" method="post" enctype="multipart/form-data">
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
						SERVICE NAME<label id="req">*</label>
						<select class="chosen-select form-control" name="serviceid" id="serviceid" autofocus onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SERVICE NAME">
							<option value=""></option>
							@foreach ($data as $itm)
							<option value="{{ $itm->serviceid }}" {{ intval(old('serviceid'))===$itm->serviceid ? 'selected' : '' }}>{{ strtoupper($itm->servicename) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('serviceid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-2">
						PARENT CATEGORY<label id="req"></label>
						<select class="chosen-select form-control" name="parentid" id="parentid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="PARENT CATEGORY">
							<option value=""></option>
						</select>

						<span class="text-danger">@error('parentid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						ENTER SERVICE CATEGORY<label id="req">*</label>
						<input type="text" class="form-control" name="servicecategory" id="servicecategory" value="{{old('servicecategory')}}" placeholder="SERVICE CATEGORY NAME" autocomplete="off"/>

						<span class="text-danger">@error('servicecategory') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						DISPLAY ORDER<label id="req">*</label>
						<input type="text" class="form-control numbers" name="displayorder" id="displayorder" value="{{old('displayorder',$displayorder)}}" placeholder="DISPLAY ORDER" autocomplete="off"/>

						<span class="text-danger">@error('displayorder') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-3">
						CATEGORY ICON (WIDTH=HEIGHT, MAX : 500 KB)<label id="req">&nbsp;</label>
						<input type="file" class="form-control" name="categoryicon" id="categoryicon" accept="image/*"/>
						<span class="text-danger">@error('categoryicon') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>

					<div class="col-sm-10"></div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn">ADD SERVICE CATEGORY</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif
@if(in_array(2,Session::get('actions')) || in_array(3,Session::get('actions')) || in_array(12,Session::get('actions')))
<div id="recordlist" class="tab-pane @if(!in_array(1,Session::get('actions'))) in active @endif" style="padding:0px 0px!important;">
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

<select class="select2" name="serid" id="serid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SERVICE NAME" onchange="loadData(1,'{{ route('servicecategory.html') }}')">
	<option value=""></option>
	@foreach ($data as $itm)
	<option value="{{ $itm->serviceid }}">{{ strtoupper($itm->servicename) }}</option>
	@endforeach
</select>

<select class="select2" name="pid" id="pid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="PARENT CATEGORY" onchange="loadData(1,'{{ route('servicecategory.html') }}')">
	<option value=""></option>
	@foreach ($parent as $itm)
	<option value="{{ $itm->servicecategoryid }}">{{ strtoupper($itm->servicecategory) }}</option>
	@endforeach
</select>
<select name="rtype" id="rtype" class="selectbx" onchange="loadData(1,'{{ route('servicecategory.html') }}')">
	<option value="ALL">ALL</option>
	<option value="PARENT">PARENT ONLY</option>
	<option value="CHILD">CHILD ONLY</option>
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
@endif



	
	</div>
</div>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
@if(!in_array(1,Session::get('actions')))
	setTimeout(function() { $('.datalist').trigger('click'); },1000);
@endif


$(document).ready(function() {	
	$("#serviceid").on("change", function() {
	  var selectedValue = $(this).val();
	  $("#parentid").empty().trigger("chosen:updated");
	  $("#parentid").prop("disabled", true).trigger("chosen:updated");
	  $.ajax({
	    url: '{{ route('servicecategorylist.html') }}',
	    type: 'GET',
	    data: { selectedValue: selectedValue },
	    dataType: 'json',
	    success: function(data) {
	      $("#parentid").append("<option value=''></option>");
	      $.each(data, function(index, option) {
	        $("#parentid").append("<option value='" + option.value + "'>" + option.label + "</option>");
	      });

	      $("#parentid").prop("disabled", false).trigger("chosen:updated");
	    },
	    error: function(error) {
	      console.error('Error fetching data:', error);
	    }
	  });
	});
});
jQuery(function($) {

	$('#categoryicon').ace_file_input({
		no_file:'No File ...',
		btn_choose:'CATEGORY ICON',
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
				$('#categoryicon').ace_file_input('reset_input');
				$('#categoryicon').ace_file_input('reset_ui');
				$('#categoryicon').ace_file_input('reset_input_field');
			}
		  };
		};			
	});
});

function loadData(page,rl) {
	var serid = document.getElementById("serid").value;
	var pid = document.getElementById("pid").value;
	var rtype = document.getElementById("rtype").value;
    $.ajax({
        url: ''+rl,
        type: 'GET',
        data: {serid:serid,pid:pid,rtype:rtype},
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