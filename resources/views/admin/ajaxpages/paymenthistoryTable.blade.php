@php
$i=1;
@endphp
@foreach ($data as $item)
<tr class="" id="item-{{ $i }}">
    <td class="width-30" style="text-align:center;" nowrap>{{ $loop->iteration }}</td>
	<td class="center width-100" nowrap>{{date('d\-m\-Y',strtotime($item->payment_date))}}</td>
	<td nowrap class="width-250">{{ $item->companyname }}</td>
	<td nowrap class="width-150">{{ $item->payment_method }}</td>
	<td>
		{{ $item->transaction_number }}
		@if($item->payment_remark)
		<br>
		{{ $item->payment_remark }}
		@endif
	</td>
	<td nowrap class="text-right width-100 format-indian" data-value="{{ $item->paying_amount }}"></td>
</tr>
@endforeach
@if($data->count()==0)
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
                event.preventDefault(); // Prevent default link behavior
                const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
                const url = '{{ route('adminpaymenthistory.html') }}';
                loadData(page, url);
            };
        });

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>

