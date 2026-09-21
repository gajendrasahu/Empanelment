@php
$i=1;
$categoryid = 0;
@endphp
@foreach ($data as $item)
<tr class="bg-primary font-bold">
	<td colspan="5">{{$loop->iteration}}) {{$item->eoinumber}} | Release Date : {{date('d\-m\-Y',strtotime($item->releasedate))}}</td>
</tr>

@foreach($item->vendors as $vendor)
<tr class="myheadbg font-bold">
	<td colspan="5">Firm Name : {{$vendor->shortname}}</td>
</tr>
@if($item->categoryid==2)
	<tr class="myheadbg font-bold">
		<td class="width-50 text-center">S. No.</td>
		<td class="width-200">Position</td>
		<td>Sector</td>
		<td>Name</td>
		<td></td>
	</tr>
	@foreach($vendor->records as $record)
	<tr>
		<td class="width-30 text-center">{{$loop->iteration}}</td>
		<td class="width-200">{{$record->consultantposition}}</td>
		<td class="width-200">{{ucwords(strtolower($record->sectorname))}}</td>
		<td>{{$record->name}}</td>
		<td class="width-100 text-center">
			<a href="{{route('view.uploadedfile',Crypt::encrypt($record->resume))}}" target="_blank">
				<button type="button" class="btn btn-info gridbtn">
					<i class="fa fa-file-pdf-o"></i> Resume
				</button>
			</a>	
		</td>
	</tr>
	@endforeach
@endif
@if($item->categoryid==1)
	<tr class="myheadbg font-bold">
		<td class="width-50 text-center">S. No.</td>
		<td>Name</td>
		<td colspan="2">Experience Level</td>
		<td></td>
	</tr>
	@foreach($vendor->records as $record)
	<tr>
		<td class="width-50 text-center">{{$loop->iteration}}</td>
		<td>{{$record->name}}</td>
		<td colspan="2" class="width-200">{{$record->experiencelevel}}</td>
		<td class="width-100 text-center">
			<a href="{{route('view.uploadedfile',Crypt::encrypt($record->resume))}}" target="_blank">
				<button type="button" class="btn btn-info gridbtn">
					<i class="fa fa-file-pdf-o"></i> Resume
				</button>
			</a>	
		</td>

	</tr>
	@endforeach
@endif
@endforeach

@endforeach
@if($data->count()==0)
<tr>
    <td colspan="5" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
<td colspan="5" style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif


