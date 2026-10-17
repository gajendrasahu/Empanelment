@if($report_format=='1')
<table class="table table-bordered pd-10" id="tablerecords">
<thead>
<tr class="myheadbg">
	<td colspan="2"></td>
	<td colspan="4" class="finance-group-department text-center no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">Consultancy Service Fee</td>
	<td colspan="4" class="finance-group-department text-center no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">CHiPS Admin Charge</td>
	<td colspan="2" class="finance-group-department text-center no_wrap" style="background-color:#E5F1F8!important; color:#24536D!important;">Department Deductions</td>
	<td colspan="2" class="finance-group-invoice text-center no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">Invoice & Verification</td>
	<td colspan="3" class="finance-group-payment text-center no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">CHiPS Payment</td>
	<td colspan="3" class="finance-group-refund text-center no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">TDS & GST TDS Payable to Vendor</td>
</tr>
<tr class="myheadbg">
	<td>#</td>
	<td class="no_wrap">EoI / Project</td>
	<td class="no_wrap text-right" style="background-color:#E8EEF3!important; color:#000!important;">Opening</td>
	<td class="no_wrap text-right" style="background-color:#E8EEF3!important; color:#000!important;">Receipt</td>
	<td class="no_wrap text-right" style="background-color:#E8EEF3!important; color:#000!important;">Available</td>
	<td class="no_wrap text-right" style="background-color:#E8EEF3!important; color:#000!important;">Net Received</td>

	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Opening</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Receipt</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Available</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Net Received</td>

	<td class="no_wrap text-right" style="background-color:#E5F1F8!important; color:#24536D!important;">TDS</td>
	<td class="no_wrap text-right" style="background-color:#E5F1F8!important; color:#24536D!important;">GST-TDS</td>

	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Invoice Raised</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Finance Verified</td>

	<td class="no_wrap text-right" style="background-color:#E4F3EC!important; color:#285C47!important;">Consultancy Fee</td>
	<td class="no_wrap text-right" style="background-color:#E4F3EC!important; color:#285C47!important;">Admin Charges</td>
	<td class="no_wrap text-right" style="background-color:#E4F3EC!important; color:#285C47!important;">Paid to Vendor</td>

	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">TDS</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">GST-TDS</td>
	<td class="no_wrap text-right" style="background-color:#FFF1D6!important; color:#76500A!important;">Total Refundable</td>
</tr>
</thead>
<tbody>

@foreach($report as $row)
<tr>
	<td class="text-center">{{ $loop->iteration }}</td>

	<td class="">
		<strong>{{ $row->eoinumber }}</strong><br>
		<small>{{ $row->projecttitle }}</small><br>
		@if($row->ispm==0)
		Department : {{ $row->short_name ?? '-' }} <i class="fa fa-info-circle" title="{{$row->department_name}}"></i>
		@else
		Project Manager : {{ $row->department_name ?? '-' }}
		@endif
	</td>


	{{-- VENDOR FUNDING / CONSULTANCY --}}

	<td class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹ <span class="format-indian" data-value="{{ number_format($row->vendor_funding_opening_balance,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->vendor_funding_department_receipt,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->vendor_funding_total_available,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->consultancy_net_received,'2','.','') }}"></span>
	</td>

	{{-- SERVICE CHARGE / ADMIN --}}

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->service_charge_opening_balance,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->service_charge_department_receipt,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->service_charge_total_available,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->admin_net_received,'2','.','') }}"></span>
	</td>

	{{-- DEPARTMENT DEDUCTIONS --}}

	<td class="text-right no_wrap" style="background-color:#E5F1F8!important; color:#24536D!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->total_department_tds,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E5F1F8!important; color:#24536D!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->total_department_gst_tds,'2','.','') }}"></span>
	</td>

	{{-- INVOICE & VERIFICATION --}}

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->invoice_raised,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->finance_verified,'2','.','') }}"></span>
	</td>

	{{-- CHIPS PAYMENT --}}

	<td class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->consultancy_fee,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->admin_charge,'2','.','') }}"></span>
	</td>

	<td class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->paid_to_vendor,'2','.','') }}"></span>
	</td>

	{{-- REFUNDABLE --}}

	<td class="text-right finance-refund-value no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->consultancy_tds,'2','.','') }}"></span>
	</td>

	<td class="text-right finance-refund-value no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->consultancy_gst_tds,'2','.','') }}"></span>
	</td>

	<td class="text-right finance-refund-total no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹  <span class="format-indian" data-value="{{ number_format($row->refundable_to_vendor,'2','.','') }}"></span>
	</td>

</tr>

@endforeach
@if($report->count()==0)
<tr>
	<td colspan="22" class="text-center finance-empty-row">
		No financial records found.
	</td>
</tr>
@else
<tr>

	<th colspan="2" class="text-right">Total</th>

	{{-- VENDOR FUNDING --}}

	<th class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['vendor_funding_opening_balance'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['vendor_funding_department_receipt'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['vendor_funding_total_available'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E8EEF3!important; color:#000!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_net_received'],'2','.','') }}"></span>
	</th>

	{{-- SERVICE CHARGE --}}

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['service_charge_opening_balance'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['service_charge_department_receipt'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['service_charge_total_available'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_admin_net_received'],'2','.','') }}"></span>
	</th>

	{{-- DEPARTMENT DEDUCTIONS --}}

	<th class="text-right no_wrap" style="background-color:#E5F1F8!important; color:#24536D!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_total_tds'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E5F1F8!important; color:#24536D!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_total_gst_tds'],'2','.','') }}"></span>
	</th>

	{{-- INVOICE & VERIFICATION --}}

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['invoice_raised'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['finance_verified'],'2','.','') }}"></span>
	</th>

	{{-- CHIPS PAYMENT --}}

	<th class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['consultancy_fee'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['admin_charge'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#E4F3EC!important; color:#285C47!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['paid_to_vendor'],'2','.','') }}"></span>
	</th>

	{{-- REFUNDABLE --}}

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_tds'],'2','.','') }}"></span>
	</th>

	<th class="text-right no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['department_consultancy_gst_tds'],'2','.','') }}"></span>
	</th>

	<th class="text-right finance-refund-total no_wrap" style="background-color:#FFF1D6!important; color:#76500A!important;">
		₹ <span class="format-indian" data-value="{{ number_format($summary['refundable_to_vendor'],'2','.','') }}"></span>
	</th>

</tr>
@endif
</tbody>
</table>
@else

<div class="finance-project-list mt-10">

    @forelse($report as $row)

        {{-- =========================================================
             ONE PROJECT = ONE FULL WIDTH ROW
        ========================================================== --}}
        <div class="finance-project-card">

            {{-- =================================================
                 COLUMN 1 : PROJECT DETAILS
            ================================================== --}}
            <div class="finance-project-info">
                <div class="finance-project-number">#{{ $loop->iteration }}</div>
                <div class="finance-project-eoi no_wrap">{{ $row->eoinumber }}</div>
                <div class="finance-project-name">{{ $row->projecttitle }}</div>
                <div class="finance-project-owner">
					@if($row->ispm==0)
					<div class="finance-owner-label">
						<i class="fa fa-building-o"></i>
						Department
					</div>
					<div class="finance-owner-name">
						{{ $row->short_name ?? '-' }}
						@if(!empty($row->department_name))
							<i class="fa fa-info-circle finance-info-icon" title="{{ $row->department_name }}"></i>
						@endif
					</div>
					@else
					<div class="finance-owner-label">
						<i class="fa fa-user"></i>
						Project Manager
					</div>
					<div class="finance-owner-name">
						{{ $row->department_name ?? '-' }}
					</div>
					@endif
                </div>
            </div>


            {{-- =================================================
                 COLUMN 2 : PAYMENT DETAILS
            ================================================== --}}
            <div class="finance-project-financial">


                {{-- =================================================
                     CONSULTANCY SERVICE FEE
                ================================================== --}}
                @if($categoryid==2)

                    <div class="finance-financial-row">

                        <div class="finance-payment-group finance-section-consultancy">

                            <div class="finance-payment-group-title">
                                <i class="fa fa-briefcase"></i>
                                Consultancy Service Fee
                            </div>

                            <div class="finance-payment-values four-columns">

                                <div class="finance-project-value">
                                    <span>Opening</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->vendor_funding_opening_balance,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>Receipt</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->vendor_funding_department_receipt,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>Available</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->vendor_funding_total_available,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value finance-value-highlight">
                                    <span>Net Received</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->consultancy_net_received,'2','.','') }}"></span>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         CHIPS ADMIN CHARGE
                    ================================================== --}}
                    <div class="finance-financial-row">

                        <div class="finance-payment-group finance-section-admin">

                            <div class="finance-payment-group-title">
                                <i class="fa fa-cog"></i>
                                CHiPS Admin Charge
                            </div>

                            <div class="finance-payment-values four-columns">

                                <div class="finance-project-value">
                                    <span>Opening</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->service_charge_opening_balance,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>Receipt</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->service_charge_department_receipt,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>Available</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->service_charge_total_available,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value finance-value-highlight">
                                    <span>Net Received</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->admin_net_received,'2','.','') }}"></span>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- =================================================
                         DEPARTMENT DEDUCTIONS
                    ================================================== --}}
                    <div class="finance-financial-row">

                        <div class="finance-payment-group finance-section-deduction">

                            <div class="finance-payment-group-title">
                                <i class="fa fa-minus-circle"></i>
                                Department Deductions
                            </div>

                            <div class="finance-payment-values two-columns">

                                <div class="finance-project-value">
                                    <span>TDS</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->total_department_tds,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>GST-TDS</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->total_department_gst_tds,'2','.','') }}"></span>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>

                @endif


                {{-- =================================================
                     INVOICE & VERIFICATION
                ================================================== --}}
                <div class="finance-financial-row">

                    <div class="finance-payment-group finance-section-invoice">

                        <div class="finance-payment-group-title">
                            <i class="fa fa-file-text-o"></i>
                            Invoice & Verification
                        </div>

                        <div class="finance-payment-values two-columns">

                            <div class="finance-project-value">
                                <span>Invoice Raised</span>
                                <strong>
                                    ₹ <span class="format-indian" data-value="{{ number_format($row->invoice_raised,'2','.','') }}"></span>
                                </strong>
                            </div>

                            <div class="finance-project-value">
                                <span>Finance Verified</span>
                                <strong>
                                    ₹ <span class="format-indian" data-value="{{ number_format($row->finance_verified,'2','.','') }}"></span>
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     CHIPS PAYMENT
                ================================================== --}}
                <div class="finance-financial-row">

                    <div class="finance-payment-group finance-section-payment">

                        <div class="finance-payment-group-title">
                            <i class="fa fa-credit-card"></i>
                            CHiPS Payment
                        </div>

                        <div class="finance-payment-values three-columns">

                            @if($categoryid==2)

                                <div class="finance-project-value">
                                    <span>Consultancy Fee</span>
                                    <strong>
                                        ₹ <span class="format-indian" data-value="{{ number_format($row->consultancy_fee,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>Admin Charges</span>
                                    <strong>
                                        ₹ <span class="format-indian" data-value="{{ number_format($row->admin_charge,'2','.','') }}"></span>
                                    </strong>
                                </div>

                            @endif

                            <div class="finance-project-value finance-value-highlight">
                                <span>Paid to Vendor</span>
                                <strong>
                                    ₹ <span class="format-indian" data-value="{{ number_format($row->paid_to_vendor,'2','.','') }}"></span>
                                </strong>
                            </div>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                     REFUNDABLE TO VENDOR
                ================================================== --}}
                @if($categoryid==2)

                    <div class="finance-financial-row">

                        <div class="finance-payment-group finance-section-refund">

                            <div class="finance-payment-group-title">
                                <i class="fa fa-undo"></i>
                                TDS & GST TDS Payable to Vendor
                            </div>

                            <div class="finance-payment-values three-columns">

                                <div class="finance-project-value">
                                    <span>TDS</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->consultancy_tds,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value">
                                    <span>GST-TDS</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->consultancy_gst_tds,'2','.','') }}"></span>
                                    </strong>
                                </div>

                                <div class="finance-project-value finance-refund-total-card">
                                    <span>Total TDS + GST-TDS</span>
                                    <strong>
                                        ₹ <span class="format-indian"
                                            data-value="{{ number_format($row->refundable_to_vendor,'2','.','') }}"></span>
                                    </strong>
                                </div>

                            </div>

                        </div>

                    </div>

                @endif

            </div>

        </div>

    @empty

        <div class="finance-project-empty">

            <i class="fa fa-folder-open-o"></i>

            <strong>
                No financial records found.
            </strong>

            <span>
                There are no projects available for the selected criteria.
            </span>

        </div>

    @endforelse

</div>


@endif

<script>
document.querySelectorAll('.format-indian').forEach(function(el){
	el.style.fontWeight = 'bold';
	el.innerText = formatIndianNumber(el.dataset.value);
});
</script>