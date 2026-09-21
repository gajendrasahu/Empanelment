@extends('admin.admin_master')
@section('admin')
@php $t=1; @endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-list-alt bigger-115" style="vertical-align:bottom;"></i> POST LIST</a></li>

			</li>

		</ul>

	
<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">

<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 1px;">
			<form name="pagedata" id="pagedata" action="{{ route('send.notification')}}" method="post">

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

					<div class="col-sm-3">
						ENTER TITLE<label id="req">*</label>
						<input type="text" class="form-control" name="title" id="title" placeholder="TITLE" autofocus autocomplete="off" required style="text-transform:none!important;" value="{{old('title')}}" />

						<input type="hidden" name="userids" id="userids" value="" style="padding:0px;">

						<span class="text-danger">@error('title') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-8">
						NOTIFICATION<label id="req">*</label>
						<input type="text" class="form-control" name="notification" id="notification" placeholder="NOTIFICATION"  autocomplete="off" required style="text-transform:none!important;" value="{{old('notification')}}" />

						<span class="text-danger">@error('notification') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>

					</div>
					<div class="col-sm-1" align="left">
						<button type="submit" class="btn btn-info myfrmbtn">SEND</button>
					</div>
					
					
				</div>		

<table style="width:100%; margin:0 auto;">
	<tr class="myheadbg" style="height:35px;">
		<td style="text-align:left; padding:0px 5px;">
<select name="customeLength" id="customeLength" class="selectbx">
	<option value="50">50</option>
	<option value="100">100</option>
	<option value="300">300</option>
	<option value="500">500</option>
</select>

<input type="text" name="customSearch" id="customSearch" class="selectbx" placeholder="Search here.." autocomplete="off">

		</td>
	</tr>
</table>

<div class="tabledata">

<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
	<span class="text-danger">@error('userids') <label class="bg-danger" style="width:100%; padding:0px 10px;"><i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i></label> @enderror</span>
<thead>	
<tr>
    <th class="myrectd" nowrap><input type="checkbox" id="headerCheckbox"></th>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th class="myrectd" nowrap>&nbsp;</th>
    <th nowrap>USER NAME</th>
    <th nowrap>EMAIL</th>
    <th nowrap>MOBILE NUMBER</th>
</tr>
</thead>
<tbody>

@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap><input type="checkbox" class="rowCheckboxes" value="{{$item->UserID}}"></td>
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td style="width:30px;" class="myrectd" nowrap>
        @if($item->profilepic!='')
        <img src="{{ asset('storage/'.$item->profilepic) }}" alt="Profile Image" style="width:20px;">
        @else
        <img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Profile Image" style="width:20px;">
        @endif    	
    </td>
    <td nowrap>{{ strtoupper($item->Name) }}</td>
    <td nowrap>{{ $item->Email }}</td>
    <td nowrap>{{ $item->MobileNumber }}</td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="8" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        Sorry!<br>
        THE SYSTEM did not find the data you are looking for.
    </td>
</tr>
@endif
</tbody>
</table>
</div>


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

<script>
$(document).ready(function() {		
$('#dynamic-table tbody tr').on('click', function() {
$(this).addClass("datatableselected").siblings().removeClass("datatableselected");
});
		   
		   var myTable = $('#dynamic-table').DataTable({
		        'columnDefs': [
		            { 'orderable': false, 'targets': [0,1,2,4,5] },
		            { 'bSearchable': false, 'targets' : [0,1,2,4,5] },
		        ],
		        'order': [],
		    });
			$('#dynamic-table_wrapper .row:first').hide();

		    $('#customeLength').on('change', function () {
		        var customPageLength = parseInt($(this).val(),10);
		        myTable.page.len(customPageLength).draw();
		    });

		    $('#customSearch').on( 'keyup', function () {
		        myTable.search( this.value ).draw();
		    } );
		       



	var selectedValues = []; // To store the selected values

  // Handle the master checkbox
  $('#headerCheckbox').on('change', function () {
    var isChecked = $(this).prop('checked');
    // Iterate through each page
    for (var i = 0; i < myTable.page.info().pages; i++) {
      // Go to the page
      myTable.page(i).draw('page');
      // Check/uncheck all checkboxes on the current page
      myTable.$('input[type="checkbox"]').prop('checked', isChecked);
      // Collect selected values
      myTable.$('input[type="checkbox"]:checked').each(function () {
        selectedValues.push($(this).val());
      });
    }
    // Return to the first page
    myTable.page(0).draw('page');
    // Update the text field
    $('#userids').val(selectedValues.join(', '));
  });

  // Handle individual checkbox changes
  $('#dynamic-table').on('change', 'input[type="checkbox"]', function () {
    selectedValues = []; // Reset the array
    // Collect selected values
    myTable.$('input[type="checkbox"]:checked').each(function () {
      selectedValues.push($(this).val());
    });
    // Update the text field
    $('#userids').val(selectedValues.join(', '));
  });

/*
  const headerCheckbox = $('#headerCheckbox');
  const rowCheckboxes = $('.rowCheckboxes');
  const userids = $('#userids');

  headerCheckbox.click(function() {
    rowCheckboxes.prop('checked', headerCheckbox.prop('checked'));
    updateHiddenTextField();
  });
  rowCheckboxes.change(updateHiddenTextField);
  function updateHiddenTextField() {
    const checkedValues = rowCheckboxes
      .filter(':checked')
      .map(function() {
        return $(this).val();
      })
      .get()
      .join(',');
    userids.val(checkedValues);
  }
 */
});

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection