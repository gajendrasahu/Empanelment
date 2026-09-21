@php
$i=1;
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
						<div class="eoi-project">{{ $item->ordernumber }} | Order Date : {{ $item->orderdate != '0000-00-00' ? date('d-m-Y', strtotime($item->orderdate)) : '' }} | {{ $item->companyname }}</div>						
					</div>
					<div class="eoi-number mt-10" style="padding-left:18px!important;">
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
					{{ $item->workorderduedate ? date('d-m-Y', strtotime($item->workorderduedate)) : '' }}
					</td>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
					@php
						$days = $item->days_to_expire;
						$months = intdiv(abs($days), 30); // assuming 30 days/month
						$remainingDays = abs($days) % 30;
					@endphp
					<div class="expiry-badge @if($days < 0) expired @elseif($days <= 60) warning @else active @endif">
					@if($item->isActiveOrder==1)
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
					@elseif($item->isExtended==1)
					Extended
					@endif
					</div>
					
					</td>
					<td style="padding:3px!important; border:none!important; font-weight:500; text-align:center;">
					{{ $item->workorderamount }}
					</td>
				</tr>
				<tr>
					<td colspan="4" style="border:none!important;">
				<div class="eoi-actions">


				@if($item->signedcopy)
				<a href="{{route('view.uploadedfile',Crypt::encrypt($item->signedcopy))}}" class="eoi-btn action-a" target="_blank">
					<i class="fa fa-file"></i> Signed WO file
				</a>
				@endif
				
				<a href="{{ route('view.deploymentdate',Crypt::encrypt($item->orderid)) }}" class="eoi-btn action-a">
						<i class="fa fa-user"></i> Deployment <span style="font-size:10px;" class="badge @if($item->isDeployed==0) badge-warning @else badge-success @endif">{{$item->resourcedeployed}}</span>
					</span>	
				</a>


				<a href="{{ route('manage.vendor',Crypt::encrypt($item->orderid)) }}" class="eoi-btn action-a">
					<i class="fa fa-book"></i> Manage
				</a>
				<!--
				<a onclick="loadMonths('{{route('mpr.months')}}','{{Crypt::encrypt($item->orderid)}}')" class="eoi-btn action-a" style="cursor:pointer;">
					<i class="ace-icon fa fa-calendar bigger-120"></i> Upload MPR & Attendance
				</a>
				-->
				
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
    <td class="padding-10" colspan="7" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
<td colspan="7" class="padding-10" style="text-align:right;">
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
                const url = '{{ route('orderlist.html') }}'; // URL generated by Laravel route
                loadData(page, url); // Call custom function with page number and URL
            };
        });
</script>

