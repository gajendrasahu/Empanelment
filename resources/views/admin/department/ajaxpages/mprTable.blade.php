@php
$i=1;
@endphp
@foreach ($data as $item)
<tr class="" id="item-{{ $i }}">
	<td style="width:30px; text-align:center;" nowrap>{{ $i++ }}</td>
	<td class="v-top">@if($item->project_name) {{$item->project_name}}<br> @endif Order Date : {{date('d\-m\-Y',strtotime($item->orderdate))}}<br>{{ $item->ordernumber }}<br><b>{{ ucwords(strtolower($item->companyname)) }}</b></td>
	<td nowrap class="text-left v-top">
		Number : {{ $item->mpr_number }}<br>Date : {{ date('d\-m\-Y',strtotime($item->submission_date)) }}<br>Month : {{ date('F', mktime(0, 0, 0, $item->mpr_month, 1)) }}-{{ $item->mpr_year }}
		<div class="d-flex align-items-center" style="width:200px;">
		@if($item->mpr_file)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($item->mpr_file))}}" target="_blank" class="action-a">
				<span class="eoi-flag eoi-flag--info">
					<i class="fa fa-download"></i> MPR
				</span>
			</a>			
		@endif
		@if($item->attendance_file)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($item->attendance_file))}}" target="_blank" class="action-a">
				<span class="eoi-flag eoi-flag--info">
					<i class="fa fa-download"></i> Attendance
				</span>
			</a>			
		@endif
		@if($item->supporting_file)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($item->supporting_file))}}" target="_blank" class="action-a">
				<span class="eoi-flag eoi-flag--info">
					<i class="fa fa-download"></i> Other
				</span>
			</a>			
		@endif
		</div>
	</td>
	<td class="form-label format-indian text-center v-top" data-value="{{ $item->mpr_value ?? 0 }}"></td>
	<td class="form-label format-indian text-center v-top" data-value="{{ $item->mpr_approved_value ?? number_format(0,'2','.','') }}"></td>
	<td class="center v-top">
		<span class="btn btn-info gridbtn" onclick="viewMprDepartment('{{ route('view.mprdepartment')}}','{{Crypt::encrypt($item->mpr_id)}}')">
			View
		</span>
	</td>
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
<tr>
<td colspan="6" style="text-align:right;">
{{ $data->links('vendor.pagination.default') }}
</td>
</tr>
@endif

<script>
const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.onclick = function(event) {
                event.preventDefault();
                const page = this.getAttribute('href').split('page=')[1];
                const url = '{{ route('mpr.html') }}';
                loadData(page, url);
            };
        });
</script>

<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});
</script>	