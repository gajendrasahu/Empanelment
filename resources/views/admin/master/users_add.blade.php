@extends('admin.admin_master')
@section('admin')
@php($t=1)

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> ADD USERS</a></li>
			<li><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('users.html') }}')"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> 
			USERS LIST</a></li>

			</li>

		</ul>

	
<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.user')}}" method="post" enctype="multipart/form-data">
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
						PROFILE / USER NAME<label id="req">*</label>
						<input type="text" class="form-control" name="name" id="name" value="{{old('name')}}" placeholder="NAME" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required autofocus/>

						<span class="text-danger">@error('name') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						MOBILE NUMBER<label id="req">*</label>
						<input type="text" class="form-control" name="mobilenumber" id="mobilenumber" value="{{old('mobilenumber')}}" placeholder="MOBILE NUMBER" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required/>

						<span class="text-danger">@error('mobilenumber') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						EMAIL<label id="req">*</label>
						<input type="text" class="form-control" name="email" id="email" value="{{old('email')}}" placeholder="EMAIL" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required style="text-transform:lowercase;"/>

						<span class="text-danger">@error('email') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						PASSWORD<label id="req">*</label>
						<input type="password" class="form-control" name="password" id="password" value="{{old('password')}}" placeholder="PASSWORD" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required style="text-transform:none;"/>

						<span class="text-danger">@error('password') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						CONFIRM PASSWORD<label id="req">*</label>
						<input type="password" class="form-control" name="cndpassword" id="cndpassword" value="{{old('cndpassword')}}" placeholder="CONFIRM PASSWORD" autocomplete="off" tabindex="{{$t++}}" onKeyPress="return OnKeyPress(this, event)" required style="text-transform:none;"/>

						<span class="text-danger">@error('cndpassword') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						PROFILE IMAGE (SQUARE SIZE)<label id="req">&nbsp;</label>
						<input type="file" class="form-control" name="profilepic" id="profilepic" accept="image/*"/>						
						<span class="text-danger">@error('profilepic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-12"><div class="col-sm-12 myheadbg" style="padding:5px 5px;">IN CASE OF USER IS TO BE REGISTERED WITH ANY SERVICE ONLY</div></div>
					<div class="col-sm-12">&nbsp;</div>
					<div class="col-sm-3">
						CATEGORY<label id="req"></label>
						<select class="chosen-select form-control" name="categoryid" id="categoryid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="CATEGORY" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($servicecategory as $itm)
							<option value="{{ $itm->servicecategoryid }}" {{ intval(old('categoryid'))===$itm->servicecategoryid ? 'selected' : '' }}>{{ strtoupper($itm->servicecategory) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('categoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>

					<div class="col-sm-3">
						SUB CATEGORY<label id="req"></label>
						<select class="chosen-select form-control" name="subcategoryid" id="subcategoryid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="SUB CATEGORY" tabindex="{{$t++}}">
							<option value=""></option>
							@foreach ($servicesubcategory as $itm)
							<option value="{{ $itm->servicecategoryid }}" {{ intval(old('subcategoryid'))===$itm->servicecategoryid ? 'selected' : '' }}>{{ strtoupper($itm->servicecategory) }}</option>
							@endforeach
						</select>

						<span class="text-danger">@error('subcategoryid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }} </i> @enderror</span>
					</div>
					<div class="col-sm-3">
						SERVICE PROFILE IMAGE (SQUARE SIZE)<label id="req">&nbsp;</label>
						<input type="file" class="form-control" name="serviceprofilepic" id="serviceprofilepic" accept="image/*"/>						
						<span class="text-danger">@error('serviceprofilepic') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>		
					</div>
					<div class="col-sm-2">
						<button type="submit" class="btn btn-info myfrmbtn">CREATE USER PROFILE</button>
					</div>
				</div>		
			</form>
		</div>
	</div>
</div>

<div id="recordlist" class="tab-pane" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 1px;">
			<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
<table style="width:100%; margin:0 auto;">
	<tr class="myheadbg" style="height:35px;">
		<td style="text-align:left; padding:0px 5px;">

<select name="customeLength" id="customeLength" class="selectbx">
	<option value="10">10</option>
	<option value="25">25</option>
	<option value="50">50</option>
	<option value="100">100</option>
</select>

<input type="text" name="customSearch" id="customSearch" class="selectbx" placeholder="Search here.." autocomplete="off">

<select name="rectype" id="rectype" class="selectbx" onchange="loadData(1,'{{ route('users.html') }}')">
	<option value="1">ACTIVE</option>
	<option value="V">VERIFICATION PENDING</option>
	<option value="B">BLOCKED</option>
</select>

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
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>

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

	$('#serviceprofilepic').ace_file_input({
		no_file:'No File ...',
		btn_choose:'SERVICE PROFILE PIC',
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
				$('#serviceprofilepic').ace_file_input('reset_input');
				$('#serviceprofilepic').ace_file_input('reset_ui');
				$('#serviceprofilepic').ace_file_input('reset_input_field');
			}
		  };
		};			
	});



});

function loadData(page,rl) {
	var rectype		=	document.getElementById("rectype").value;
    $.ajax({
        url: ''+rl,
        type: 'GET',
        data: {rectype:rectype},
        success: function(response) {
            $(".tabledata").html(response);
$('#dynamic-table tbody tr').on('click', function() {
$(this).addClass("datatableselected").siblings().removeClass("datatableselected");
});
		   
		   var myTable = $('#dynamic-table').DataTable({
		        'columnDefs': [
		            { 'orderable': false, 'targets': [0,1,5,6,7] },
		            { 'bSearchable': false, 'targets' : [0,1,5,6,7] },
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
		            message: ' ',
		            title: 'USER LIST'
		          }       
		        ]
		    } );
		    myTable.buttons().container().appendTo( $('.tableTools-container') );
        },
    });
}


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

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection