@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)
<tr class="finance-payment-card-row">
    <td colspan="11" class="finance-payment-card-cell">

        <div class="finance-payment-card">

            {{-- Header --}}
            <div class="finance-payment-card-header">
                <div class="finance-payment-card-title">
                    <span class="finance-payment-number">
                        {{ $loop->iteration }}
                    </span>

                    <div>
                        <div class="department-name">
                            {{ $item->departmentname }}
                        </div>

                        <div class="receipt-info">
                            Receipt No:
                            <strong>{{ $item->receipt_no }}</strong>
                        </div>
                    </div>
                </div>

                <div class="finance-payment-card-date">
                    <span>Date</span>
                    <strong>
                        {{ date('d-m-Y', strtotime($item->receipt_date)) }}
                    </strong>
                </div>
            </div>


            {{-- Details --}}
            <div class="finance-payment-card-body">

                <div class="detail-section">

                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fa fa-file-text-o"></i>
                            Demand Note
                        </span>
                        <strong>{{ $item->demand_note_no }}</strong>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fa fa-calendar"></i>
                            Demand Date
                        </span>
                        <strong>
                            {{ date('d-m-Y', strtotime($item->demand_note_date)) }}
                        </strong>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fa fa-ticket"></i>
                            Voucher Number
                        </span>
                        <strong>{{ $item->voucher_no ?: '-' }}</strong>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fa fa-exchange"></i>
                            Transaction
                        </span>
                        <strong>{{ $item->transaction_no ?: '-' }}</strong>
                    </div>

                    <div class="detail-item">
                        <span class="detail-label">
                            <i class="fa fa-credit-card"></i>
                            Payment Mode
                        </span>
                        <span class="finance-payment-mode">
                            {{ $item->payment_mode }}
                        </span>
                    </div>

                </div>


                {{-- Amount Summary --}}
                <div class="amount-section">

                    <div class="amount-box">
                        <span>Gross Received</span>
                        <strong class="format-indian"
                               data-value="{{ $item->gross_received_amount }}">
                        </strong>
                    </div>

                    <div class="amount-box">
                        <span>TDS</span>
                        <strong class="format-indian"
                               data-value="{{ $item->tds_amount }}">
                        </strong>
                    </div>

                    <div class="amount-box">
                        <span>GST TDS</span>
                        <strong class="format-indian"
                               data-value="{{ $item->gst_tds_amount }}">
                        </strong>
                    </div>

                    <div class="amount-box">
                        <span>Other Deduction</span>
                        <strong class="format-indian"
                               data-value="{{ $item->other_deduction }}">
                        </strong>
                    </div>

                    <div class="amount-box net-amount">
                        <span>Net Received (in a/c)</span>
                        <strong class="format-indian"
                               data-value="{{ $item->net_received_amount }}">
                        </strong>
                    </div>

                </div>

            </div>


            {{-- Footer --}}
            <div class="finance-payment-card-footer">

                <div>
                    <span class="footer-label">Total Invoiced</span>
                    <strong class="format-indian" data-value="{{ $item->total_invoiced }}"></strong>
                </div>

                <div>
                    <span class="footer-label"></span>
                    <strong></strong>
                </div>

                <div class="card-actions">
                    @if($item->attachment)
					<a href="{{ route('view.uploadedfile', Crypt::encrypt($item->attachment)) }}" target="_blank" class="btn btn-sm btn-danger"
					   title="View Attachment">
						<i class="fa fa-file-pdf-o"></i>
						View Attachment
					</a>
                    @endif
					@if($item->status=='Active')
					@if($item->total_invoiced==0)
					<a href="{{ route('finance.departmentreceipt.edit', Crypt::encrypt($item->department_payment_id)) }}" class="btn btn-sm btn-info">
						<i class="fa fa-edit"></i>
						Edit
					</a>

					<a href="javascript:void(0);" class="btnCancelDepartmentPayment btn btn-sm btn-info" data-id="{{ Crypt::encrypt($item->department_payment_id) }}"
						 style="outline:none; text-decoration:none;" title="Cancel Department Payment">
						<i class="fa fa-ban"></i> Cancel
					</a>					
					@endif
					@endif
                    @if($item->status=='Cancelled')
					<a class="btn btn-sm btn-danger" title="Cancelled">
						<i class="fa fa-check-circle"></i>
						Cancelled
					</a>
                    @endif
					<a href="{{ route('finance.departmentreceipt.view',Crypt::encrypt($item->department_payment_id)) }}" class="btn btn-sm btn-info"
					   style="outline:none; text-decoration:none;" title="View Payment Receipt">
						<i class="fa fa-eye"></i> View
					</a>
					
                </div>

            </div>

        </div>

    </td>
</tr>
@endforeach
@if($data->count()==0)
<tr>
	<td colspan="10" style="text-align:center;">
		<br>
		<i class="fa fa-warning blue nodata"></i><br>
		{!! __('messages.sorry') !!}
	</td>
</tr>
@else
<tr>
	<td colspan="10" style="text-align:right;">
		{{ $data->links('vendor.pagination.default') }}
	</td>
</tr>
@endif

<script>
document.querySelectorAll('.format-indian').forEach(function(el)
{
	el.style.fontWeight = 'bold';
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>