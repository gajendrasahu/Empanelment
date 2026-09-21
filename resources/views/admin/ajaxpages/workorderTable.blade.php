@php 
$i = 1; 
$serial=0;
@endphp
@foreach ($data as $item)
@php
$serial = $serial+1;
@endphp

<tr class="eoi-row" id="item-{{ $serial }}">
	<td colspan="7" class="padding-2 eoi-card-cell" style="@if($serial%2!=0) background-color:white!important; @endif">
		<div class="eoi-card">
			<div class="eoi-header">
				<div class="eoi-main">
					<div class="eoi-number-line">
						<div class="eoi-sno">{{ $serial }}.</div>
						<div class="eoi-project">{{ $item->ordernumber }} | Order Date : {{ $item->orderdate != '0000-00-00' ? date('d-m-Y', strtotime($item->orderdate)) : '' }} | {{ $item->companyname }}

					@if($item->isExtended==1)
					<div class="expiry-badge bg-info"><i class="fa fa-mail-forward"> Extended</i></div>
					@endif
						
						</div>						
					</div>
					<div class="eoi-number mt-10" style="padding-left:18px!important;">
						@if($item->project_name) Project Name : {{$item->project_name}} | @endif
						@if($item->refrence) EoI Number : {{ $item->refrence }} | @endif Department / Project Manager : {{ $item->departmentname }}
					</div>
				</div>
		
			</div>
			<table style="border:none!important; width:100%!important;">
				<tr>
					<td style="padding:3px!important; border:none!important; text-align:center;">Resources Deployed</td>
					<td style="padding:3px!important; border:none!important; text-align:center;">Order Expiry Date</td>
					<td style="padding:3px!important; border:none!important; text-align:center;">Order Status</td>
					<td style="padding:3px!important; border:none!important; text-align:center;">Work Order Value</td>
				</tr>
				<tr>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
						@php
							$isComplete = ($item->resourcedeployed == $item->totalresources);
						@endphp						
						<span class="resource-badge {{ $isComplete ? 'complete' : 'pending' }}">
							{{ $item->resourcedeployed }}/{{ $item->totalresources }}
						</span>					
					</td>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
					@if($item->isCancelled==0)
						{{ $item->workorderduedate ? date('d-m-Y', strtotime($item->workorderduedate)) : '' }}
					@else
						-
					@endif
					</td>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
					@php
					
						$days = $item->days_to_expire;
						$months = intdiv(abs($days), 30);
						$remainingDays = abs($days) % 30;
					@endphp
					@if($item->isExtended==0)
					@if($item->isCancelled==0 && $item->isClosed==0)
					<div class="expiry-badge @if($days < 0) expired @elseif($days <= 60) warning @else active @endif">
						@if($days >= 0)
							@if($months > 0)
								{{ $months }} month{{ $months > 1 ? 's' : '' }}
								@if($remainingDays > 0)
									{{ $remainingDays }} day{{ $remainingDays > 1 ? 's' : '' }}
								@endif
							@else
								{{ $remainingDays }} day{{ $remainingDays > 1 ? 's' : '' }}
							@endif
							left
						@else
							Expired
							@if($months > 0)
								{{ $months }} month{{ $months > 1 ? 's' : '' }}
								@if($remainingDays > 0)
									{{ $remainingDays }} day{{ $remainingDays > 1 ? 's' : '' }}
								@endif
							@else
								{{ $remainingDays }} day{{ $remainingDays > 1 ? 's' : '' }}
							@endif
							ago
						@endif
					</div>
					@else
					@if($item->isCancelled!=0)
					<div class="expiry-badge expired active">Cancelled</div>
					@else
					<div class="expiry-badge expired active">Project Closed @if($item->closedOn) on {{date('d\-m\-Y',strtotime($item->closedOn))}}@endif</div>
					@endif
					@endif
					@else
					<div class="expiry-badge expired active">Extended</div>
					@endif
					
					</td>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
					{{ $item->workorderamount }}
					</td>
				</tr>
				<tr>
					<td colspan="4" style="border:none!important;">
				<div class="eoi-actions">
					@if($item->isExtended==0 && $item->isClosed==0 && $item->isCancelled==0)
					@permission('cancel.confirmation')
					<a href="{{ route('workorder.cancellation.form', Crypt::encrypt($item->orderid)) }}" class="eoi-btn action-a">
						<i class="fa fa-remove"></i> Cancel Order
					</a>
					@endpermission
					@endif
				
					@if($item->signedcopy)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($item->signedcopy))}}" class="eoi-btn action-a" target="_blank">
						<i class="fa fa-file"></i> Signed WO file
					</a>
					@endif
					@if($item->momfile)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($item->momfile))}}" class="eoi-btn action-a" target="_blank">
						<i class="fa fa-file"></i> MoM File
					</a>
					@endif
					@if($item->markingsheet)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($item->markingsheet))}}" class="eoi-btn action-a" target="_blank">
						<i class="fa fa-file"></i> Marking Sheet
					</a>
					@endif
					@if($item->isCancelled==0 && $item->isClosed==0)
					@permission('set.deploymentdate')
					<a href="{{ route('set.deploymentdate', Crypt::encrypt($item->orderid)) }}" class="eoi-btn action-a">
						<i class="fa fa-calendar"></i> Deployment
					</a>
					@endpermission
					@endif
					@if($item->isExtended==0 && $days<=200 && $item->isDeployed==1 && $item->isClosed==0)
					@permission('extend.workorder')
					<a href="{{ route('workorder.extension.form', Crypt::encrypt($item->orderid)) }}" class="eoi-btn action-a">
						<i class="fa fa-clock-o"></i> Extend
					</a>
					@endpermission
					@endif
					@if($item->closing_file)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($item->closing_file))}}" class="eoi-btn action-a" target="_blank">
						<i class="fa fa-file"></i> Closure File
					</a>
					@endif
					@if($item->cancellation_file)
					<a href="{{route('view.uploadedfile',Crypt::encrypt($item->cancellation_file))}}" class="eoi-btn action-a" target="_blank">
						<i class="fa fa-file"></i> Supporting Document File
					</a>
					@endif
					<!-- @if($months==0 && $remainingDays<=10) -->
					<!-- @endif -->
				</div>
					
					</td>
				</tr>
			</table>
		</div>
	</td>
</tr>
@endforeach
@if($data->count()==0)
<tr>
    <td class="padding-10" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
<td class="padding-10" style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif


<script>
const paginationLinks = document.querySelectorAll('.pagination a');
paginationLinks.forEach(link => {
	link.onclick = function(event) {
		event.preventDefault();
		const page 	= 	this.getAttribute('href').split('page=')[1];
		const url	= 	'{{ route('orderlist.html') }}';
		loadData(page, url);
	};
});
</script>
