<table class="mytable pd-8" border="1">
	<tr>
		<td colspan="4" nowrap><b>{{$order->ordernumber}} | Order Date : {{date('d\-m\-Y',strtotime($order->orderdate))}} | Work Order Due Date : {{date('d\-m\-Y',strtotime($order->workorderduedate))}}</b> </td>
		<td colspan="2" class="text-center"><b>Amount</b></td>
		<td colspan="3" class="text-center"><b>Documents Required for Invoice</b></td>
		<td></td>
	</tr>
	<tr class="myhead">
		<td class="text-center" style="width:25px;" nowrap>
			<input type="checkbox" name="chkAll" id="chkAll">
			<input type="hidden" name="orderid" id="orderid" value="{{$orderid}}">
			<input type="hidden" name="mpr_ids" id="mpr_ids">
			<input type="hidden" name="total_value" id="total_value">
		</td>
		<td nowrap><b>MPR Number</b></td>
		<td nowrap class="width-100"><b>Submission Date</b></td>
		<td nowrap class="width-100 center"><b>MPR Month</b></td>
		<td nowrap class="width-100 text-center"><b>Submitted</b></td>
		<td nowrap class="width-100 text-center"><b>Approved</b></td>
		<td nowrap class="text-center"><b>MPR</b></td>
		<td nowrap class="text-center"><b>Attendance</b></td>
		<td nowrap class="text-center"><b>Supporting</b></td>
		<td class="width-30"></td>
	</tr>
	@php
	$submitted	=	0;
	$approved	=	0;
	@endphp
	@foreach($mprs as $mpr)
	<tr class="form-label">
		<td class="center v-top">
			@if($mpr->signed_mpr && $mpr->signed_attendance)
			<input type="checkbox" name="mprids[]" value="{{$mpr->mpr_id}}" class="mpr_ids" data-value="{{ $mpr->mpr_approved_value }}">
			@else
			<i class="fa fa-info-circle" title="Please ensure all signed documents are attached."></i>
			@endif
		</td>
		<td class="v-top">{{ $mpr->mpr_number }}</td>
		<td class="v-top">{{ date('d\-m\-Y',strtotime($mpr->submission_date)) }}</td>
		<td class="center v-top">{{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}-{{ $mpr->mpr_year }}</td>
		<td class="form-label format-indian v-top text-center" data-value="{{ $mpr->mpr_value }}"></td>
		<td class="form-label format-indian v-top text-center" data-value="{{ $mpr->mpr_approved_value }}"></td>

		<td class="text-center" style="width:50px;">
			@if($mpr->signed_mpr)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_mpr))}}" target="_blank">
				<i class="fa fa-file-pdf-o"></i>
			</a>
			@else
				<i class="fa fa-remove"></i>
			@endif
		</td>
		<td class="text-center" style="width:50px;">
			@if($mpr->signed_attendance)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_attendance))}}" target="_blank">
				<i class="fa fa-file-pdf-o"></i>
			</a>
			@else
				<i class="fa fa-remove"></i>
			@endif
		</td>
		<td class="text-center" style="width:50px;">
			@if($mpr->signed_supporting)
			<a href="{{route('view.uploadedfile',Crypt::encrypt($mpr->signed_supporting))}}" target="_blank">
				<i class="fa fa-file-pdf-o"></i>
			</a>
			@else 
				<i class="fa fa-remove"></i>
			@endif
		</td>
		<td class="text-center" onclick="viewMprDetail('{{route('uploadmpr.document')}}','{{Crypt::encrypt($mpr->mpr_id)}}')">
			<i class="fa fa-angle-double-right icon-animated-bell"></i>
		</td>
	</tr>
	@php
	$submitted	=	$submitted+$mpr->mpr_value;
	$approved	=	$approved+$mpr->mpr_approved_value;
	@endphp
	@endforeach
	@if($mprs->count()==0)
	<tr><td class="center" colspan="11">--No Record Found--</td></tr>
	@else
	<tr>
		<td colspan="4" class="text-right"><b>Total</b></td>
		<td class="text-center format-indian font-bold" data-value="{{ number_format($submitted,'2','.','') }}"></td>
		<td class="text-center format-indian font-bold" data-value="{{ number_format($approved,'2','.','') }}"></td>
		<td colspan="4"></td>
	</tr>
	@endif
</table>

<script>
if ($('.mark_attendance').length) {
    $('.mark_attendance').hide();
}

@if($order->project_name)
$(".invoice-title").html(
    '<i class="fa fa-file-pdf-o"></i> Create an Invoice Against the Selected MPR(s) for Project: <b>{{$order->project_name}}</b>'
);
@else
$(".invoice-title").html(
    '<i class="fa fa-file-pdf-o"></i> Create an Invoice Against the selected MPR(s).'
);
@endif

$('.invoice-footer .selected-mprs-value').remove();
$('.invoice-footer .generateInvoice').remove();

function updateMPRData() {
    let ids = [];
    let total = 0;
    $('.mpr_ids:checked').each(function () {
        ids.push($(this).val());
        total += parseFloat($(this).data('value')) || 0;
    });
    $('input[name="mpr_ids"]').val(ids.join(','));
    $('input[name="total_value"]').val(total);
	$('.invoice-footer .selected-mprs-value').remove();
	
	$('.invoice-footer').append(
		'<span class="form-label font-14 font-bold selected-mprs-value format-indian" style="float:left;">(A) Selected MPRs Value =' + formatIndianNumber(total) + ', (B) GST @ 18% = '+formatIndianNumber(((total*18)/100))+', C= A+B = '+formatIndianNumber(total+((total*18)/100))+'</span>'
	);	
	
    $('#chkAll').prop('checked', $('.mpr_ids').length === $('.mpr_ids:checked').length);
}

$('#chkAll').on('change', function () {
    $('.mpr_ids').prop('checked', this.checked);
    updateMPRData();
});

// Individual checkboxes
$(document).on('change', '.mpr_ids', function () {
    updateMPRData();
});

$('.btn-close').on('click', function (e)
{
	$('#invoiceContent').html('');
	$('#invoiceHistory').modal('hide');
});

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

@if($mprs->count()!=0)
$(".invoice-footer").append(
  '<button type="button" class="btn btn-info generateInvoice gridbtn" style="width:150px;" onclick="makeInvoice(\'{{ route('make.vendorinvoice') }}\', \'{{ $orderid }}\')">' +
    '<i class="fa fa-save"></i> Prepare Invoice' +
  '</button>'
);
@endif
function makeInvoice(r1,orderid)
{
	var mpr_ids	=	document.getElementById("mpr_ids").value;
    $.ajax({
        url: r1,
        type: "GET",
        data: {
            orderid: orderid,
            mpr_ids: mpr_ids
        },
        success: function(data) {
            if (data.status == 400) {
                bootbox.alert(data.message);
            }
            else if (data.status == 500) {
                bootbox.alert(data.message);
            }
            else if (data.status == 200) {
				$("#invoicing").submit();
            }
        },
        error: function(xhr) {
            if (xhr.status === 422) {
                let errors = xhr.responseJSON.errors;
                let msg = "<ul>";
                $.each(errors, function(key, err) {
                    msg += "<li>" + err + "</li>";
                });
                msg += "</ul>";
                bootbox.alert(msg);
            }
			else
			{
                bootbox.alert("Unexpected error occurred. Status: " + xhr.status);
            }
        }
    });
}

function viewMprLog(r1,mprid)
{
	$.get(""+r1,
	{
		mprid:mprid,
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else if(data.status==500)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('.Tbldata').html(data.data);
		}
	});
}

</script>