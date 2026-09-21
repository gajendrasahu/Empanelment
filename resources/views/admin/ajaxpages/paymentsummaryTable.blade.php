@php
$i=1;
$invoices	=	0;
$invoice	=	0;
$paid		=	0;
$balance	=	0;
@endphp
@if($summary_type=='FIRM')
<table class="mytable">
<tr class="myhead">
	<th class="center width-30 padding-15" nowrap><b>S.No.</b></th>
	<th nowrap class="width-200 padding-15"><b>Firm Name</b></th>
	<th nowrap class="center padding-15 width-100"><b>Total Invoices</b></th>
	<th nowrap class="text-right padding-15 width-100"><b>Invoice Value</b></th>
	<th nowrap class="text-right padding-15 width-100"><b>Paid Amount</b></th>
	<th nowrap class="text-right padding-15 width-100"><b>Balance</b></th>
	<th nowrap class="center width-150 padding-15"><b>Payment %</b></th>
</tr>

@foreach ($data as $item)
@if($item->total_invoices>0)
<tr class="" id="item-{{ $i }}">
    <td class="width-30" style="text-align:center;" nowrap>{{ $i }}</td>
	<td nowrap class="width-250">{{ $item->firm_name }}</td>
	<td nowrap class="center width-100">
		
		{{ $item->total_invoices }}
	</td>
	<td nowrap class="text-right width-100" style="position: relative;">
		<i class="fa fa-list" style="position:absolute; top:2px; right:2px;" onclick="viewInvoices({{$item->vendorid}},'Invoices')"></i>
		<span class="format-indian" data-value="{{ $item->invoice_value }}"></span>
	</td>
	<td nowrap class="text-right width-100" style="position: relative;">
		<i class="fa fa-list" style="position:absolute; top:2px; right:2px;" onclick="viewInvoices({{$item->vendorid}},'Paid')"></i>
		<span class="format-indian" data-value="{{ $item->paid_amount }}"></span>
	</td>
	<td nowrap class="text-right width-100" style="position: relative;">
		<i class="fa fa-list" style="position:absolute; top:2px; right:2px;" onclick="viewInvoices({{$item->vendorid}},'Balance')"></i>
		<span class="format-indian" data-value="{{ $item->balance }}"></span>
	</td>
	<td nowrap class="" style="width:200px; position: relative;">
		<div class="progress" style="height: 25px;">
			<div class="progress-bar progress-bar-striped progress-bar-animated
				@if($item->payment_percent === 100) bg-success
				@elseif($item->payment_percent === 0) bg-danger
				@else bg-warning
				@endif"
				role="progressbar" 
				style="width: {{ $item->payment_percent }}%">
			</div>
		</div>

         <span style="position: absolute; bottom:50%; left: 50%; transform: translate(-50%, 0); width: 100%; text-align: center; line-height: 25px; font-weight: bold; color: white; pointer-events: none;">{{ $item->payment_percent }}% ({{ $item->status }})</span>
		 
		 
		
	</td>
</tr>
@php
	$i=$i+1;
	$invoices	=	$invoices+$item->total_invoices;
	$invoice	=	$invoice+$item->invoice_value;
	$paid		=	$paid+$item->paid_amount;
	$balance	=	$balance+$item->balance;
@endphp
@endif
@endforeach
@if($data->count()==0)
<tr>
    <td colspan="7" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        {!!	__('messages.sorry') !!}
    </td>
</tr>
</table>
@endif
@endif
@if($summary_type=='PROJECT')
<table class="mytable">
@foreach($data as $vendorId => $projects)
    <tr>
        <td style="padding:15px!important;" colspan="5"><strong>{{ $projects->first()->companyname }}</strong></td>
        <tr>
            <td style="padding:15px!important;" nowrap class="center"><b>S.No.</b></td>
            <td style="padding:15px!important; width:450px;"><b>Project Name</b></td>
            <td style="padding:15px!important; text-align:right;" nowrap><b>Invoice Value</b></td>
            <td style="padding:15px!important; text-align:right;" nowrap><b>Paid Amount</b></td>
            <td style="padding:15px!important; text-align:right;" nowrap><b>Balance</b></td>
        </tr>
    </tr>

    @foreach($projects as $row)
        <tr>
            <td style="padding:15px!important;" class="center">{{$loop->iteration}}</td>
            <td style="padding:15px!important;">{{ $row->project_name }}</td>
            <td style="text-align:right; padding:15px!important; position:relative;">
				<i class="fa fa-list" style="position:absolute; top:2px; right:2px;"></i>
				<span class="format-indian" data-value="{{ $row->total_invoice }}"></span>
			</td>
            <td style="text-align:right; padding:15px!important; position:relative;">
				@if($row->total_paid)<i class="fa fa-list" style="position:absolute; top:2px; right:2px;"></i>@endif
				<span class="format-indian" data-value="{{ $row->total_paid }}"></span>			
			</td>
            <td style="text-align:right; padding:15px!important; position:relative;">
				@if($row->total_balance)<i class="fa fa-list" style="position:absolute; top:2px; right:2px;"></i>@endif
				<span class="format-indian" data-value="{{ $row->total_balance }}"></span>
			</td>
        </tr>
    @endforeach
@endforeach

</table>
@endif


<script>
document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>