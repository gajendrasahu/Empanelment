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
		<li class="active"><a data-toggle="tab" href="#recordlist"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> User Permission</a></li>
	</ul>

	<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
		<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
			<div class="row" style="padding:0px;">
				<div class="col-xs-12">
				<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
					<div class="table-responsive">
						<table class="table table-bordered">
						<tr class="bg-primary">
							<td>Name : {{$user->name}} | Email : {{$user->email}} | Mobile Number : {{$user->mobilenumber}}</td>
						</tr>
						<tr class="myheadbg">
							<td>Roles</td>
						</tr>
						<tr>
							<td>
							@foreach($roles as $role)
							<label class="me-4" style="display:inline-block; margin-right:25px; min-width:100px; padding:5px 0px;">
								<input type="checkbox" class="userrolepermission" value="{{ $role->roleid }}" {{ $role->isAvailable ? 'checked' : '' }} style="vertical-align:text-top;" data-roleid="{{ $role->roleid }}" data-user_id="{{ $user->userid }}"> {{ $role->role }}
							</label>							
							@endforeach
							</td>
						</tr>
						<tr class="bg-primary">
							<td style="position:relative!important;">Access Permissions
						<input type="hidden" name="user_id" id="user_id" value="{{$user->userid}}">
						<select name="menuid" id="menuid" onchange="loadData('{{ route('userpermission.html') }}')" style="position:absolute!important; right:5px; top:2px;">
							<option value="">--APPLICATION MENU--</option>
							@foreach($parents as $menu)
								@if($menu->mastermenu)
								<option value="{{ $menu->menuid }}">
									{{ strtoupper(__($menu->mastermenu)) }}
								</option>
								@endif
							@endforeach
						</select>						
							</td>
						</tr>
						</table>
					</div>
					<div class="table-responsive">
						<table class="table table-bordered table-striped tabledata pd-10" style="border:1px solid #eee; border-collapse:collapse;" border="1">
						</table>
					</div>
				</form>
				</div>
			</div>
		</div>
	</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
setTimeout(function(){ loadData('{{ route('userpermission.html') }}'); },2000);
function loadData(url)
{
    var menuid = $("#menuid").val();
	var userid = $("#user_id").val();

    var roleids = [];

    $(".role:checked").each(function () {
        roleids.push($(this).val());
    });

    roleids = roleids.join(",");
    var scrollPos = $(window).scrollTop();
	
	if(userid!='')
	{
		$.ajax({
			url: url,
			type: "GET",
			data: {
				roleids:roleids,
				menuid:menuid,
				userid:userid
			},
			success: function (response) {

				$(".tabledata").html(response);

				initializePermissionEvents();
			}
		});
	}
}

function initializePermissionEvents()
{
    $(".submenu").each(function()
	{
        var id = $(this).data("menuid");

        if($(this).is(":checked"))
        {
            $(".action-"+id).prop("disabled",false);
        }
        else
        {
            $(".action-"+id)
                .prop("disabled",true)
                .prop("checked",false);
        }

    });

    $(".submenu").off("change").on("change",function()
	{
        var id=$(this).data("menuid");

        if($(this).is(":checked"))
        {
            $(".action-"+id).prop("disabled",false);
        }
        else
        {
            $(".action-"+id)
                .prop("checked",false)
                .prop("disabled",true);
        }

    });

}

$(document).on("change", ".userpermission", function () {

    var checkbox = $(this);
	
    $.ajax({
        url: "/master/saveUserPermission",
        type: "POST",
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            userid:$("#user_id").val(),
            permissionid: checkbox.data("permissionid"),
            isactive: checkbox.is(":checked") ? 1 : 0
        },
        success: function (response) {
        },
        error: function (xhr) {
        }
    });

});

$(document).on("change", ".userrolepermission", function () {

    var checkbox = $(this);
	
    $.ajax({
        url: "/master/saveUserRolePermission",
        type: "POST",
        data: {
            _token:$('meta[name="csrf-token"]').attr('content'),
            userid: checkbox.data("user_id"),
			roleid: checkbox.data("roleid"),
            isactive: checkbox.is(":checked") ? 1 : 0
        },
        success: function (response) {
			setTimeout(function(){ loadData('{{ route('userpermission.html') }}'); },2000);
        },
        error: function (xhr) {
        }
    });

});

</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection