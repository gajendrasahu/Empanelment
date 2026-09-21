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
			<li class="active"><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('actionpermission.html') }}')"><i class="green ace-icon fa fa-list bigger-120 datalist" style="vertical-align:bottom;"></i> Role Permission Mapping</a></li>
		</ul>

		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px;">
<div id="recordlist" class="tab-pane in active">
	<div class="row">
		<div class="col-xs-12">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
		@csrf
			<div class="table-responsive" style="margin-top:-10px;">			
				<table class="table table-bordered" id="tablerecords">
				<tr>
					<td>
						<select class="select2" data-width="200" name="roleid" id="roleid" data-placeholder="Role" onchange="loadData('{{ route('rolepermission.html') }}')">
							<option value=""></option>
							@foreach($roles as $role)
								<option value="{{ $role->roleid }}">
									{{ strtoupper($role->role) }}
								</option>
							@endforeach
						</select>						


						<select class="select2" data-width="200" name="menuid" id="menuid" data-placeholder="Master Menus" onchange="loadData('{{ route('rolepermission.html') }}')">
							<option value=""></option>
							@foreach($parents as $menu)
								@if($menu->mastermenu)
								<option value="{{ $menu->menuid }}">
									{{ strtoupper(__($menu->mastermenu)) }}
								</option>
								@endif
							@endforeach
						</select>						

						<select class="select2" data-width="200" name="apply_to" id="apply_to" data-placeholder="Apply To" onchange="loadData('{{ route('rolepermission.html') }}')">
							<option value="RO">Role Only</option>
							<option value="ROEU">Role + Existing Users</option>
						</select>						
					</td>
				</tr>
				<tr><td class="tabledata"  style="padding:0px!important;"></td></tr>
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

function loadData(url)
{
    var roleid 	=	$("#roleid").val();
    var menuid 	= 	$("#menuid").val();

    var scrollPos = $(window).scrollTop();
	
	if(roleid!='')
	{
		$.ajax({
			url: url,
			type: "GET",
			data: {
				roleid:roleid,
				menuid:menuid,
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
    // Initial state
    $(".submenu").each(function(){

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

    // Click event
    $(".submenu").off("change").on("change",function(){

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


$(document).on("change", ".permission", function () {

    var checkbox = $(this);
	
    $.ajax({
        url: "/master/saveRolePermission",
        type: "POST",
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            roleid: $("#roleid").val(),
			apply_to: $("#apply_to").val(),
            permissionid: checkbox.data("permissionid"),
            isactive: checkbox.is(":checked") ? 1 : 0
        },
        success: function (response) {
        },
        error: function (xhr) {
        }
    });

});

</script>

@endsection