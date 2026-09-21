@php
$i=1;
$amount=0;
$tax=0;
$invoice=0;
$paid=0;
$balance=0;
@endphp
@foreach ($data as $item)
<tr class="" id="item-{{ $i }}">
    <td style="width:30px; text-align:center;" nowrap>{{ $loop->iteration }}</td>


<td style="text-align:left; width:400px;">
    <div style="display:flex; flex-direction:column; gap:0px; line-height:25px;">
        
        <div><b>{{ $item->companyname }}</b></div>
		@if($item->name)<p> <i class="fa fa-user"></i> Submitted By : <b>{{ $item->name }}</b> </p>@endif
		<p class="mt-10"><b>{{ $item->ordernumber }}</b></p>
		<p class="mt-10">{{ $item->project_name }}</p>
		
		

        @if($item->mpr_file!='')
        <a href="{{route('view.uploadedfile',Crypt::encrypt($item->mpr_file))}}" target="_blank" class="action-a" style="width:150px;">
            <span class="label label-white middle f-s-10 form-label span-content" style="width:100%; text-align:left;">
                <i class="fa fa-file-pdf-o"></i> Combined MPR File
            </span>
        </a>
        @endif

        @if($item->attendance_file!='')
        <a href="{{route('view.uploadedfile',Crypt::encrypt($item->attendance_file))}}" target="_blank" class="action-a" style="width:150px;">
            <span class="label label-white middle f-s-10 form-label span-content" style="width:100%; text-align:left;">
                <i class="fa fa-file-pdf-o"></i> Combined Attendance File
            </span>
        </a>
        @endif

        @if($item->supporting_file!='')
        <a href="{{route('view.uploadedfile',Crypt::encrypt($item->supporting_file))}}" target="_blank" class="action-a" style="width:150px;">
            <span class="label label-white middle f-s-10 form-label span-content" style="width:100%; text-align:left;">
                <i class="fa fa-file-pdf-o"></i> Supporting Document
            </span>
        </a>
        @endif

        @if($item->invoice_file!='')
        <a href="{{route('view.uploadedfile',Crypt::encrypt($item->invoice_file))}}" target="_blank" class="action-a" style="width:150px;">
            <span class="label label-white middle f-s-10 form-label span-content" style="width:100%; text-align:left;">
                <i class="fa fa-file-pdf-o"></i> Invoice File
            </span>
        </a>
        @endif
		
		<label style="display:flex; align-items:center; gap:6px;" class="mt-10">
			<input type="checkbox" onclick="markAsSeen(this,'{{route('deptmark.invoice')}}','{{Crypt::encrypt($item->recordid)}}')" name="marked[]" @if($item->isMarked) checked disabled @endif>
			Mark as seen
		</label>
    </div>
</td>

	<td class="center" nowrap style="padding:2px!important; vertical-align:top!important;">
		<table style="width:100%!important;">
			<tr>
				<td style="padding:5px!important; text-align:left;">Request Date</td>
				<td style="padding:5px!important; text-align:left;">{{date('d\-m\-Y',strtotime($item->request_date))}}</td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Invoice Date</td>
				<td style="padding:5px!important; text-align:left;">{{date('d\-m\-Y',strtotime($item->invoice_date))}}</td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Invoice Number</td>
				<td style="padding:5px!important; text-align:left;">{{ $item->invoice_number }}</td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Month</td>
				<td style="padding:5px!important; text-align:left;">{{ date('M-Y', strtotime($item->mpr_year . '-' . $item->mpr_month . '-01')) }}</td>
			</tr>
		</table>
	</td>
	<td class="center" style="padding:2px!important; vertical-align:top!important;">
		<table style="width:100%!important;">
			<tr>
				<td style="padding:5px!important; text-align:left;">Taxable Amount</td>
				<td style="padding:5px!important; text-align:left;" class="format-indian" data-value="{{ $item->amount_value }}"></td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Tax Value</td>
				<td style="padding:5px!important; text-align:left;" class="format-indian" data-value="{{ $item->tax_value }}"></td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Invoice Value</td>
				<td style="padding:5px!important; text-align:left;" class="format-indian" data-value="{{ $item->invoice_value }}"></td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Paid Value</td>
				<td style="padding:5px!important; text-align:left;" class="format-indian" data-value="@if($item->paid_value) {{ $item->paid_value }} @else 0.0 @endif"></td>
			</tr>
			<tr>
				<td style="padding:5px!important; text-align:left;">Balance Value</td>
				<td style="padding:5px!important; text-align:left;" class="format-indian" data-value="@if($item->balance_value) {{ $item->balance_value }} @else 0.0 @endif"></td>
			</tr>
			
		</table>
	</td>
</tr>
@php
$amount	=	$amount+$item->amount_value;
$tax	=	$tax+$item->tax_value;
$invoice=	$invoice+$item->invoice_value;
$paid	=	$paid+$item->paid_value;
$balance=	$balance+$item->balance_value;
@endphp
@endforeach
@if($data->count()==0)
<tr>
    <td colspan="4" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!! __('messages.sorry') !!}
    </td>
</tr>
@else
<tr>
	<td colspan="4" style="text-align:right;">
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
                const url = '{{ route('pminvoicehistory.html') }}';
                loadData(page, url);
            };
        });

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

function markAsSeen(el,url,recordid)
{
    if(el.checked)
	{
        el.disabled = true; // disable immediately

        $.get(url, {
            recordid: recordid,
        }, function(data, status){
            if (data.status!= 200) {
                el.disabled = false;
                el.checked = false;
            }
			else
			{
				bootbox.alert(data.message);
			}
        });
    }
}
</script>

