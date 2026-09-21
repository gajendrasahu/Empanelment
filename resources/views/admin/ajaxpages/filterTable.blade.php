@php
$i=1;
@endphp
@foreach ($filtergroups as $item)
<tr class="myheadbg">
    <td class="bdleft" nowrap colspan="5">{{ $item->filtergroup }}</td>
</tr>
<tr class="myheadbg">
    <td style="width:30px; text-align:center;" nowrap>S.NO.</td>
	<td style="" nowrap>FILTER NAME</td>
	<td style="text-align:center; width:75px;" nowrap>DISPLAY ORDER</td>
	<td style="text-align:center; width:30px;"></td>
	<td style="text-align:center; width:30px;"></td>
</tr>
@php $r=0; @endphp
@foreach($item->filters as $filter)
@php $r=$r+1; $i=$i+1; @endphp
<tr class="mytr" id="item-{{ $i }}">
    <td style="text-align:center;" nowrap>{{ $r }}</td>
	<td nowrap>{{$filter->filtername}}</td>
	<td style="text-align:center;" nowrap>{{ $filter->displayorder }}</td>
    <td nowrap style="text-align:center; width:30px;">
	@if(in_array(2,Session::get('actions')))
		<a href="{{ route('edit.filter',$filter->filterid) }}" title="" class="myactionlink"><i class="fa fa-edit"></i></a></td>
	@endif
    <td nowrap style="text-align:center; width:30px;">
	@if(in_array(3,Session::get('actions')))
        <a title="" class="myactionlink" onclick="deleteItem('filter',{{ $filter->filterid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','FILTER NAME DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
	@endif
    </td>
</tr>
@endforeach
@endforeach
