@php
$i=1
@endphp
@foreach ($filters as $groupId => $filtersInGroup)
	@php
		$groupName = $filtersInGroup->first()->filtergroup;
	@endphp
	<tr class="myheadbg"><td style="padding:0px 5px;">{{ $groupName }}</td></tr>
		<tr>
			<td style="padding:0px 5px; height:30px; vertical-align:middle;">
			@foreach ($filtersInGroup as $filter)
				<span style="width:200px; float:left;">
					<input type="checkbox" style="vertical-align:text-top;" name="filters[]" value="{{ $filter->filterid }}" onclick="attachFilter('{{ route('attach.filter') }}',{{$filter->filterid}})" {{ ($filter->is_checked && $filter->isactive==1) ? 'checked' : '' }}>
					{{ $filter->filtername }}
				</span>
			@endforeach
			</td>
		</tr>
@endforeach	
