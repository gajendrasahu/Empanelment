<table class="mytable pd-8" border="1">
	<tr class="myhead">
		<td class="" style="width:25px;" nowrap>
			<input type="checkbox" name="chkAll" id="chkAll">
			<input type="hidden" name="orderid" id="orderid" value="{{$orderid}}">
			<input type="hidden" name="mpr_ids" id="mpr_ids">
			<input type="hidden" name="total_value" id="total_value">
		</td>
		<td nowrap class="center width-100"><b>Submission Date</b></td>					
		<td nowrap class="width-100"><b>MPR Number</b></td>
		<td nowrap class=""><b>Order Number</b></td>
		<td nowrap class="width-100 center"><b>MPR Month</b></td>
		<td nowrap class="width-100" style="text-align:right;"><b>MPR Value</b></td>
		<td nowrap class="width-100" style="text-align:right;"><b>Approved Value</b></td>
	</tr>
	@foreach($mprs as $mpr)
	<tr class="form-label">
		<td class="center v-top">
			<input type="checkbox" name="mprids[]" value="{{$mpr->mpr_id}}" class="mpr_ids" data-value="{{ $mpr->mpr_approved_value }}">
		</td>
		<td class="center v-top">{{ date('d\-m\-Y',strtotime($mpr->submission_date)) }}</td>
		<td class="v-top">{{ $mpr->mpr_number }}<br>
		@if($mpr->mpr_value!=$mpr->mpr_approved_value)
		<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content" onclick="viewMprLog('{{route('view.mprlog')}}','{{Crypt::encrypt($mpr->mpr_id)}}')">
			<i class="fa fa-list"></i> View Log
		</span>		
		@endif
		</td>
		<td class="v-top">{{ $mpr->ordernumber }}</td>
		<td class="center v-top">{{ $mpr->mpr_month }}-{{ $mpr->mpr_year }}</td>
		<td class="form-label format-indian v-top" data-value="{{ $mpr->mpr_value }}" style="text-align:right;"></td>
		<td class="form-label format-indian v-top" data-value="{{ $mpr->mpr_approved_value }}" style="text-align:right;"></td>
	</tr>
	@endforeach
	@if($mprs->count()==0)
	<tr><td class="center" colspan="7">--No Record Found--</td></tr>
	@endif
</table>

<script>
$(".invoice-title").html(
    '<i class="fa fa-file-pdf-o"></i> Select MPRs and generate an invoice.'
);
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
  '<button type="button" class="btn btn-info generateInvoice" onclick="makeInvoice(\'{{ route('make.vendorinvoice') }}\', \'{{ $orderid }}\')">' +
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
            } else {
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