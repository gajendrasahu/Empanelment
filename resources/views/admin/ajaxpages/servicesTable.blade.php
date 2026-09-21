@php
$i=1;
@endphp
@foreach($data as $item)
<tr id="item-{{ $i }}">
    <td class="myheadbg" nowrap colspan="2">
		{{ $i++ }}) {{$item->servicetitle}}
		<span style="margin-right:10px; float:right;">
		@if(in_array(3,Session::get('actions')))
			<a title="" class="myactionlink" onclick="deleteItem('services','{{ Crypt::encrypt($item->serviceid)}}',{{ $i }},'{{__('messages.confirmation')}}','{{__('messages.deleted')}}')" title="">
				<i class="fa fa-trash" style="color:white!important;"></i>
			</a>
		@endif
		</span>
		<span style="margin-right:15px; float:right;">
		@if(in_array(2,Session::get('actions')))
			<a href="{{ route('edit.services',Crypt::encrypt($item->serviceid)) }}" title="" class="myactionlink">
				<i class="fa fa-edit" style="color:white!important;"></i>
			</a>
		@endif	
		</span>
	</td>
</tr>
<tr>
    <td nowrap style="width:100px;">
        <a href="{{ asset('storage/'.$item->servicepic) }}" target="_blank">
			<img src="{{ asset('storage/'.$item->servicepic) }}" alt="Uploaded Image" style="width:100px;">
		</a>
		<button type="button" class="btn btn-info myfrmbtn" style="float:left; width:120px;" onclick="ViewPhotos('{{ route('service.viewphotos') }}','{{ Crypt::encrypt($item->serviceid)}}')">{{__('common.viewphotos')}}</button>
		@if($item->optionrequired==1)
			<button type="button" class="btn btn-info myfrmbtn" style="float:left; width:120px;" onclick="AddOption('{{ route('service.options') }}','{{ Crypt::encrypt($item->serviceid)}}')">{{__('common.addoption')}}</button>

		@endif
		
    </td>
	<td>
		<table class="mytable" border="1">
			<tr>
				<td class="mybgtd" style="width:150px;">{{__('labels.categoryname')}}</td>
				<td colspan="7">{{$item->category}}</td>
			</tr>
			<tr>
				<td class="mybgtd" style="width:150px;">{{__('common.creationdate')}}</td>
				<td colspan="7">{{date('d\-m\-Y, h:i A',strtotime($item->creationdate))}}</td>
			</tr>
			<tr>
				<td class="mybgtd">{{__('labels.visitingcharge')}}</td>
				<td class="mybgtd">{{__('common.mrp')}}</td>
				<td class="mybgtd" style="width:150px;">{{__('labels.taxname')}}</td>
				<td class="mybgtd">{{__('labels.hsncode')}}</td>
				<td class="mybgtd">{{__('labels.timerequired')}}</td>
				<td class="mybgtd">{{__('labels.minqty')}}</td>
				<td class="mybgtd">{{__('labels.maxqty')}}</td>
				<td class="mybgtd center">{{__('labels.isactive')}}</td>

			</tr>
			<tr>				
				<td><i class="fa fa-inr"></i> {{$item->visitingcharge}}</td>
				<td><i class="fa fa-inr"></i> {{$item->mrp}}</td>
				<td>{{$item->taxname}}</td>
				<td>{{$item->hsncode}}</td>
				<td>{{$item->requiredtime}} MINUTES</td>
				<td>{{$item->minqty}}</td>
				<td>{{$item->maxqty}}</td>
				<td class="center">
					@if(in_array(23,Session::get('actions')))
					<input type="checkbox" @if($item->isactive==1) checked @elseif($item->isactive==0) unchecked @endif onclick="SetActiveInactive('{{ route('setactive.service') }}','{{ Crypt::encrypt($item->serviceid)}}',{{$i}})">
					<span id="{{$i}}" style="display:none;">
						<i class="fa fa-check-circle" style="color:green; font-size:18px;"></i> UPDATED
					</span>
				
					@endif
				</td>
			</tr>
			@if($item->needs!='')
			<tr>
				<td class="mybgtd" nowrap colspan="8"><i class="fa fa-arrow-down"></i> {{__('labels.need')}}</td>
			</tr>
			<tr>
				<td colspan="8" style="width:100%;">
				<div style="width:100%; min-height:100px; max-height:200px; overflow:auto!important;">
				{!! $item->needs !!}
				</div>
				</td>
			</tr>
			@endif
			@if($item->includes!='')
			<tr>
				<td class="mybgtd" nowrap colspan="8"><i class="fa fa-arrow-down"></i> {{__('labels.include')}}</td>
			</tr>
			<tr>
				<td colspan="8" style="width:100%;">
				<div style="width:100%; min-height:100px; max-height:200px; overflow:auto!important;">
				{!! $item->includes !!}
				</div>
				</td>
			</tr>
			@endif
			@if($item->excludes!='')
			<tr>
				<td class="mybgtd" nowrap colspan="8"><i class="fa fa-arrow-down"></i> {{__('labels.exclude')}}</td>
			</tr>
			<tr>
				<td colspan="8" style="width:100%;">
				<div style="width:100%; min-height:100px; max-height:200px; overflow:auto!important;">
				{!! $item->excludes !!}
				</div>
				</td>
			</tr>
			@endif
			@if($item->description!='')
			<tr>
				<td class="mybgtd" nowrap colspan="8"><i class="fa fa-arrow-down"></i> {{__('labels.description')}}</td>
			</tr>
			<tr>
				<td colspan="8" style="width:100%;">
				<div style="width:100%; min-height:100px; max-height:200px; overflow:auto!important;">
				{!! $item->description !!}
				</div>
				</td>
			</tr>
			@endif
		</table>
	</td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="8" style="text-align:center;">
        <br>
		@include('admin.body.actionmessage')
    </td>
</tr>
@else
<tr>
<td colspan="8" style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif

<script>
const paginationLinks = document.querySelectorAll('.pagination a');
paginationLinks.forEach(link => {
	link.onclick = function(event) {
		event.preventDefault(); // Prevent default link behavior
		const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
		const url = '{{ route('services.html') }}'; // URL generated by Laravel route
		loadData(page, url); // Call custom function with page number and URL
	};
});
</script>
