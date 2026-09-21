@php
$i=1;

@endphp
<!--
<tr style="background-color:#438EB9!important; color:white; font-weight:550;">
	<td style="border:1px solid #438EB9; text-align:center;" colspan="{{$records+1}}">{{ __($role) }}</td>
</tr>
-->
@foreach ($data as $item)
@php
$td=0;
@endphp
<tr style="background-color:#025964!important; color:white;">
	<td colspan="{{$records+1}}">{{ __($item->mastermenu) }}</td>
</tr>
<tr class="headbg">
	<td nowrap>Menu Name <i class="fa fa-angle-double-down"></i> Permissions <i class="fa fa-angle-double-right"></i></td>
	@php
	$maps = $item->mapping;
	@endphp
	@foreach($item->mapping as $mapping)
		<td class="center" nowrap>{{ $mapping->actionname }}</td>
		@php $td=$td+1; @endphp
	@endforeach
	@php
	while($td<$records)
	{
	@endphp
		<td style="border:1px solid #438EB9; width:100px;"></td>
	@php
	$td++;
	}
	@endphp
</tr>
<!-- SINGLE SUBMENU-->
@if(count($item->singlesubmenu)!=0)
@foreach($item->singlesubmenu as $singlesubmenu)
@php
$td=0;

@endphp
<tr>
	<td nowrap><i class="fa fa-angle-double-right"></i> {{ __($singlesubmenu->mastermenu) }}</td>
	@foreach($maps as $map)
		@php $f=0; @endphp
		@foreach($singlesubmenu->mapping as $mapping)
			@if($mapping->actionid==$map->actionid)
				<td nowrap class="center">
	<form name="frm_{{ $mapping->mappingid }}" id="frm_{{ $mapping->mappingid }}" action="{{ route('store.rolepermission',$mapping->mappingid) }}" method="post">
	@csrf
		<label>
			<input type="checkbox" style="vertical-align:middle;" @if($mapping->ispermitted==1) {{ 'checked' }} @endif onclick="$('#frm_{{$mapping->mappingid}}').submit();">
			<input type="hidden" name="uid_{{$mapping->mappingid}}" id="uid_{{$mapping->mappingid}}" value="{{ $roleid }}">
		</label>
		<span class="text-danger">@error('mappingid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
	</form>			
				</td>
				@php $td++; $f++; break; @endphp
			@endif
		@endforeach
		@if($f==0)
			<td nowrap></td>
			@php $td++; @endphp
		@endif		
	@endforeach
	@php
	while($td<$records)
	{
	@endphp
		<td style="border:1px solid #438EB9; width:100px;"></td>
	@php
	$td++;
	}
	@endphp
</tr>
@endforeach
@endif
<!-- SINGLE SUBMENU -->

<!-- SUBMENU-->
@foreach($item->submenu as $submenu)
@php
$td=0;

@endphp
<tr style="font-weight:550;">
	<td nowrap><i class="fa fa-angle-double-right"></i> {{ __($submenu->mastermenu) }}</td>
	@foreach($maps as $map)
		@php $f=0; @endphp
		@foreach($submenu->mapping as $mapping)
			@if($mapping->actionid==$map->actionid)
				<td nowrap class="center">
	<form name="frm_{{ $mapping->mappingid }}" id="frm_{{ $mapping->mappingid }}" action="{{ route('store.rolepermission',$mapping->mappingid) }}" method="post">
	@csrf
		<label>
			<input type="checkbox" style="vertical-align:middle;" @if($mapping->ispermitted==1) {{ 'checked' }} @endif onclick="$('#frm_{{$mapping->mappingid}}').submit();">
			<input type="hidden" name="uid_{{$mapping->mappingid}}" id="uid_{{$mapping->mappingid}}" value="{{ $roleid }}">
		</label>
		<span class="text-danger">@error('mappingid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
	</form>			
				</td>
				@php $td++; $f++; break; @endphp
			@endif
		@endforeach
		@if($f==0)
			<td nowrap></td>
			@php $td++; @endphp
		@endif		
	@endforeach
	@php
	while($td<$records)
	{
	@endphp
		<td style="border:1px solid #438EB9; width:100px;"></td>
	@php
	$td++;
	}
	@endphp
</tr>
@endforeach
<!-- SUBMENU -->
@endforeach

