@php
$i=1;
@endphp
<table class="table table-bordered">

@foreach($data as $parent)

    @foreach($parent->submenus as $submenu)

        <!-- First Row -->
        <tr class="table-primary">
            <td width="150px;">
                <strong>{{ __($parent->mastermenu) }}</strong>
            </td>

            <td>
                <label class="mb-0">
                    <input type="checkbox" class="submenu permission" data-menuid="{{ $submenu->permissionid }}" {{ $submenu->isAvailable ? 'checked' : '' }} style="vertical-align:text-top;" data-permissionid="{{ $submenu->permissionid }}" data-roleid="{{ $roleid }}"> <strong>{{ $submenu->permission_name }}</strong>
                </label>
            </td>
        </tr>
		
		@if($submenu->actions->count()>0)
        <!-- Second Row -->
        <tr>
            <td></td>
            <td>
                @foreach($submenu->actions as $action)
				<label class="me-4" style="display:inline-block; margin-right:25px; min-width:160px; padding:5px 0px;">
					<input type="checkbox" class="action permission action-{{ $submenu->permissionid }}" value="{{ $action->permissionid }}" {{ $action->isAvailable ? 'checked' : '' }} style="vertical-align:text-top;" data-permissionid="{{ $action->permissionid }}" data-roleid="{{ $roleid }}"> {{ $action->permission_name }}
				</label>
                @endforeach
            </td>
        </tr>
		@endif
    @endforeach

@endforeach

</table>
