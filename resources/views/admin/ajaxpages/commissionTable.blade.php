@php
$i=1
@endphp
@foreach ($combinations as $comb)
<tr class="mytr">
	<td class="center">{{$i++}}</td>
	<td>{{$comb['tiername']}}</td>
	<td nowrap>{{$comb['categoryname']}}</td>
	<td style="padding:0px;">
		<input type="hidden" name="{{$comb['tierid']}}" id="{{$comb['tierid']}}" value="{{$comb['tierid']}}">
		<input type="hidden" name="{{$comb['categoryid']}}" id="{{$comb['categoryid']}}" value="{{$comb['categoryid']}}">
		<input type="text" name="{{$comb['tierid']}}{{$comb['categoryid']}}" id="{{$comb['tierid']}}{{$comb['categoryid']}}" class="select" value="{{$comb['commission']}}" placeholder="0" style="width:100%;">
	</td>
	<td style="padding:0px!important;">
		<button type="button" class="btn btn-info mygridbtn" style="width:100px; padding:4px; height:100%;" onclick="UpdateCommission('{{route('update.commission')}}',{{$comb['tierid']}},{{$comb['categoryid']}})">{{__('common.update')}} <i class="fa fa-check-circle" id="bt{{$comb['tierid']}}{{$comb['categoryid']}}" style="color:white; font-size:16px; display:none;"></i></button>
	</td>
	<td nowrap style=""></td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="6" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
@endif
