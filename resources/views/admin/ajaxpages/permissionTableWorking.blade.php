@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}" style="background-color:#438EB9!important; color:white; font-weight:550;">
	<td style="border:1px solid #438EB9;">{{ $item->mastermenu }}</td>
</tr>
<tr>
	<td style="border:1px solid #438EB9;">
	@php
	$i=$i+1;
	@endphp
	@foreach ($item->actions as $action)
	<form name="frm_{{ $action->mappingid }}" id="frm_{{ $action->mappingid }}" action="{{ route('store.permission',$action->mappingid) }}" method="post">
	@csrf
		<label style="margin-left:5px; margin-right:20px; margin-right:15px; margin-top:10px; margin-bottom:10px; float:left;">
			<input type="checkbox" style="vertical-align:top;" @if($action->ispermitted==1) {{ 'checked' }} @endif onclick="$('#frm_{{$action->mappingid}}').submit();"> {{ $action->actionname}}
			<input type="hidden" name="uid_{{$action->mappingid}}" id="uid_{{$action->mappingid}}" value="{{ $userid }}">
		</label>
		<span class="text-danger">@error('mappingid') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
	</form>
    @endforeach	
	</td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@else
@endif

