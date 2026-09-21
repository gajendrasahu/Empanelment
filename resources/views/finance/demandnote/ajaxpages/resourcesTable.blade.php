@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp

@if($resources)
<table class="pd-5 width-full mytable" border="1">
	<tr class="bg-primary font-bold">
		<td colspan="6">EoI Number : {{$eoi->eoinumber}}, Release Date : {{date('d\-m\-Y',strtotime($eoi->releasedate))}}</td>
	</tr>
	<tr class="myheadbg font-bold">
		<td class="text-center"><input type="checkbox" class="order_checkbox" data-orderid="1" checked></td>
		<td class="text-center">S.No.</td>
		<td>Sector</td>
		<td>Position</td>
		<td>Start Date</td>	
		<td>End Date</td>	
	</tr>
	@foreach($resources as $resource)
	<tr>
		<td class="text-center">
			<input type="checkbox" name="resource_id[]" value="{{$resource->recordid}}" class="resource_checkbox" data-orderid="1" data-sectorid="{{$resource->sectorid}}" data-positionid="{{$resource->positionid}}" data-categoryid="{{$eoi->categoryid}}" data-tierid="{{$eoi->tierid}}" data-fromdate="{{date('d\-m\-Y',strtotime($fromDate))}}" data-todate="{{date('d\-m\-Y',strtotime($toDate))}}" checked>
		</td>
		<td class="text-center">{{$loop->iteration}}</td>
		<td>{{ucwords(strtolower($resource->sectorname))}}</td>
		<td>{{$resource->consultantposition}}</td>
		<td class="width-100" style="padding:0px!important;">
			<input type="text" name="fromDate[]" placeholder="dd-mm-YYYY" class="todays_dt_blank width-100" value="{{date('d\-m\-Y',strtotime($fromDate))}}">
		</td>
		<td class="width-100" style="padding:0px!important;">
			<input type="text" name="toDate[]" placeholder="dd-mm-YYYY" class="todays_dt_blank width-100" value="{{date('d\-m\-Y',strtotime($toDate))}}">
		</td>
	</tr>
	@endforeach
</table>
@else
<table class="pd-5 width-full mytable" border="1">		
	<tr class="bg-primary font-bold">
		<td colspan="8">EoI Number : {{$eoi->eoinumber}} | Date : {{date('d\-m\-Y',strtotime($eoi->releasedate))}}</td>
	</tr>
	@foreach($orders as $order)
	<tr class="myheadbg font-bold">
		<td colspan="8">Order Number : {{$order->ordernumber}} | Date : {{date('d\-m\-Y',strtotime($order->orderdate))}} | Due Date : {{date('d\-m\-Y',strtotime($order->workorderduedate))}}</td>
	</tr>
	<tr class="myheadbg font-bold">
		<td class="text-center"><input type="checkbox" class="order_checkbox" data-orderid="{{$order->orderid}}" checked></td>
		<td class="text-center">S.No.</td>
		<td>Sector</td>
		<td>Position</td>
		<td>Name</td>
		<td>Mobile Number</td>
		<td>Start Date</td>	
		<td>End Date</td>	
	</tr>
	@foreach($order->resources as $resource)
	<tr>
		<td class="text-center">
			<input type="checkbox" name="resource_id[]" value="{{$resource->deploymentid}}" class="resource_checkbox" data-orderid="{{$order->orderid}}" data-sectorid="{{$resource->sectorid}}" data-positionid="{{$resource->positionid}}" data-categoryid="{{$order->categoryid}}" data-tierid="{{$order->tierid}}" data-fromdate="{{date('d\-m\-Y',strtotime($fromDate))}}" data-todate="{{date('d\-m\-Y',strtotime($toDate))}}" checked>
		</td>
		<td class="text-center">{{$loop->iteration}}</td>
		<td>{{ucwords(strtolower($resource->sectorname))}}</td>
		<td>{{$resource->consultantposition}}</td>
		<td>{{$resource->name}}</td>
		<td>{{$resource->mobilenumber}}</td>
		<td class="width-100" style="padding:0px!important;">
			<input type="text" name="fromDate[]" placeholder="dd-mm-YYYY" class="todays_dt_blank width-100" value="{{date('d\-m\-Y',strtotime($fromDate))}}">
		</td>
		<td class="width-100" style="padding:0px!important;">
			<input type="text" name="toDate[]" placeholder="dd-mm-YYYY" class="todays_dt_blank width-100" value="{{date('d\-m\-Y',strtotime($toDate))}}">
		</td>
	</tr>
	@endforeach
	@endforeach
</table>
@endif
<div class="text-right mt-5">
	<button type="button" class="btn btn-info br-5 gridbtn width-100 clsBtn" class="close" data-dismiss="modal">
		<i class="fa fa-remove"></i> Close
	</button>
	
	<button type="button" class="btn btn-info br-5 gridbtn width-150 viewCalculationBtn">
		<i class="fa fa-calculator"></i> View Pricing
	</button>
	
	<button type="button" class="btn btn-info br-5 gridbtn width-100 submitDemandResourceBtn">
		<i class="fa fa-check-circle"></i> Set Value
	</button>
</div>
<script>

$(document).on('change', '.order_checkbox', function () {
	var orderid = $(this).data('orderid');
	$('.resource_checkbox[data-orderid="' + orderid + '"]').prop('checked', $(this).prop('checked'));
});

$(document).on('change', '.resource_checkbox', function () {
	var orderid = $(this).data('orderid');
	var total = $('.resource_checkbox[data-orderid="' + orderid + '"]').length;
	var selected = $('.resource_checkbox[data-orderid="' + orderid + '"]:checked').length;
	$('.order_checkbox[data-orderid="' + orderid + '"]').prop('checked', total > 0 && total == selected);
});


$(document)
    .off('click.DemandResource', '.submitDemandResourceBtn')
    .on('click.vDemandResource', '.submitDemandResourceBtn', function () {

	var resources = [];
	var valid = true;

	$('.resource_checkbox:checked').each(function () {

		var fromDate= 	$(this).closest('tr').find('input[name="fromDate[]"]').val();
		var toDate 	= 	$(this).closest('tr').find('input[name="toDate[]"]').val();

		if(fromDate == '' || toDate == '')
		{
			bootbox.alert('From Date and To Date are mandatory for every selected resource.');
			valid = false;
			return false;
		}

		resources.push({
			deploymentid: $(this).val(),
			orderid: $(this).data('orderid'),
			sectorid: $(this).data('sectorid'),
			positionid: $(this).data('positionid'),
			categoryid: $(this).data('categoryid'),
			tierid: $(this).data('tierid'),
			fromDate: fromDate,
			toDate: toDate
		});

	});

	if(!valid)
	{
		return;
	}

	if(resources.length==0)
	{
		bootbox.alert('Please select at least one resource.');
		return;
	}

	$.ajax({
		url: "{{ route('finance.demandnote.submitresources') }}",
		type: "POST",
		data: {
			requestid: $('#request_id').val(),
			resources: resources,
			_token: "{{ csrf_token() }}"
		},
		success: function (response) {

			if (response.status) {
				document.getElementById('advance_amount').value=response.grand_total;
				$('.clsBtn').trigger('click');
				calculateDemandAmounts();
			}
			else {
				bootbox.alert(response.message || 'Unable to submit resources.');
			}
		},
		error: function (xhr)
		{
			if(xhr.responseJSON && xhr.responseJSON.message)
			{
				bootbox.alert(xhr.responseJSON.message);
			}
			else
			{
				bootbox.alert('Something went wrong while submitting resources.');
			}
		},
		complete: function () {
			$('.submitDemandResourceBtn').prop('disabled', false);
		}
	});

});



$(document)
    .off('click.viewPricing', '.viewCalculationBtn')
    .on('click.viewPricing', '.viewCalculationBtn', function () {

    var resources = [];
    var valid = true;

    $('.resource_checkbox:checked').each(function () {

        var fromDate = $(this).closest('tr').find('input[name="fromDate[]"]').val();
        var toDate   = $(this).closest('tr').find('input[name="toDate[]"]').val();

        if (fromDate == '' || toDate == '')
		{
			
            bootbox.alert('From Date and To Date are mandatory for every selected resource.');
            valid = false;
            return false;
        }

        resources.push({
            deploymentid: $(this).val(),
            orderid: $(this).data('orderid'),
            sectorid: $(this).data('sectorid'),
            positionid: $(this).data('positionid'),
            categoryid: $(this).data('categoryid'),
            tierid: $(this).data('tierid'),
            fromDate: fromDate,
            toDate: toDate
        });

    });

    if(!valid)
	{
        console.log('Stopped because validation failed');
        return;
    }

    if (resources.length == 0)
	{
        bootbox.alert('Please select at least one resource.');
        return;
    }


	$.ajax({
		url: "{{ route('finance.demandnote.calculation') }}",
		type: "POST",

		data: {
			requestid: $('#request_id').val(),
			resources: resources,
			_token: "{{ csrf_token() }}"
		},
		beforeSend: function () {
			$('.viewCalculationBtn').prop('disabled', true);
		},
		success: function (response) {

			if (response.status) {

				bootbox.confirm({
					message: "Pricing calculation is ready. Do you want to download the PDF?",

					buttons: {
						confirm: {
							label: 'Download',
							className: 'btn-success'
						},
						cancel: {
							label: 'Cancel',
							className: 'btn-secondary'
						}
					},

					callback: function (result) {

						if (result) {

							var form = $('<form>', {
								method: 'POST',
								action: "{{ route('finance.demandnote.downloadcalculation') }}"
							});

							form.append(
								$('<input>', {
									type: 'hidden',
									name: '_token',
									value: "{{ csrf_token() }}"
								})
							);

							form.append(
								$('<input>', {
									type: 'hidden',
									name: 'requestid',
									value: $('#request_id').val()
								})
							);

							form.append(
								$('<input>', {
									type: 'hidden',
									name: 'resources',
									value: JSON.stringify(resources)
								})
							);

							$('body').append(form);

							form.submit();

							form.remove();
						}
					}
				});

			} else {

				bootbox.alert(
					response.message ||
					'Unable to generate pricing calculation.'
				);
			}
		},
		error: function (xhr) {

			if (xhr.responseJSON && xhr.responseJSON.message) {
				bootbox.alert(xhr.responseJSON.message);
			} else {
				bootbox.alert(
					'Something went wrong while generating pricing calculation.'
				);
			}
		},

		complete: function () {
			$('.viewCalculationBtn').prop('disabled', false);
		}
	});

});

</script>