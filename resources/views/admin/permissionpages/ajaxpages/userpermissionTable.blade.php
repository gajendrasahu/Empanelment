@php
$i=1;
@endphp

@foreach($data as $parent)

    @foreach($parent->submenus as $submenu)

        <!-- First Row -->
        <tr style="border:1px solid #eee; border-collapse:collapse;">
            <td width="130px;">
                <strong>{{ __($parent->mastermenu) }}</strong>
            </td>

            <td>
                <label class="mb-0">
                    <input type="checkbox" class="submenu userpermission" data-menuid="{{ $submenu->permissionid }}" {{ $submenu->isAvailable ? 'checked' : '' }} style="vertical-align:text-top;" data-permissionid="{{ $submenu->permissionid }}" data-userid="{{ $userid }}"> <strong>{{ $submenu->permission_name }}</strong>
                </label>
            </td>
        </tr>
		
		@if($submenu->actions->count()>0)
        <!-- Second Row -->
        <tr>
            <td></td>
            <td style="vertical-align:top!important;">
                @foreach($submenu->actions as $action)
				<label class="me-4" style="display:inline-block; margin-right:25px; min-width:180px; padding:5px 0px;">
					<input type="checkbox" class="action userpermission action-{{ $submenu->permissionid }}" value="{{ $action->permissionid }}" {{ $action->isAvailable ? 'checked' : '' }} style="vertical-align:text-top;" data-permissionid="{{ $action->permissionid }}" data-userid="{{ $userid }}"> {{ $action->permission_name }}
				</label>
                @endforeach
            </td>
        </tr>
		@endif
    @endforeach

@endforeach

