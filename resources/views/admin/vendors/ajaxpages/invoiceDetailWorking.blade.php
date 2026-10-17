<div class="invoice-modal-container">

	<div class="invoice-header">

        <div class="invoice-header-left">

            <div class="invoice-title">
                <i class="fa fa-file-text-o"></i>
                {{ $invoice->invoice_number }}
            </div>

            <div class="invoice-meta">
                <span>
                    <i class="fa fa-calendar"></i>
                    {{ date('d-m-Y', strtotime($invoice->invoice_date)) }}
                </span>

                <span class="invoice-divider">|</span>

                <span>
                    Gross Invoice Value:
                    <strong>INR
                        <span class="format-indian"
                              data-value="{{ number_format($invoice->net_invoice_value, 2, '.', '') }}">
                        </span>
                    </strong>
                </span>
            </div>

        </div>


        <div class="invoice-header-right">

            <span class="status-badge br-5 padding-5 {{ $invoice->finance_status == 'Pending' ? 'status-warning' : 'status-success' }}">
                <i class="fa fa-check-circle"></i>
                {{ $invoice->finance_status }}
            </span>


            <span class="status-badge br-5 padding-5 {{ $invoice->payment_status == 'Unpaid' ? 'status-warning' : 'status-success' }}">

                <i class="fa fa-file-text-o"></i>
                {{ $invoice->payment_status }}
            </span>
			
			@if($invoice->invoice_file)
            <a href="{{ route('view.uploadedfile', Crypt::encrypt($invoice->invoice_file)) }}" target="_blank">
				<span class="badge badge-warning br-5 padding-5">
					<i class="fa fa-eye"></i> View Invoice
				</span>
            </a>
				
			@endif
				
        </div>

    </div>
    @foreach($invoice->mprs as $orderId => $mprList)

        @foreach($mprList as $mpr)

            @php
                $totalCalculatedSalary = $mpr->attendance_summary->sum('calculated_salary');
                $totalNetSalary = $mpr->attendance_summary->sum('net_salary');
                $totalApprovedSalary = $mpr->attendance_summary->sum('approved_salary');
            @endphp
            <div class="mpr-card">

                {{-- MPR Header --}}
                <div class="mpr-header">
                    <div>
                        <div class="mpr-title">
                            <i class="fa fa-file-text"></i>
                            {{ $mpr->mpr_number }}
							@if($mpr->signed_mpr)
								<a href="{{ route('view.uploadedfile', Crypt::encrypt($mpr->signed_mpr)) }}" target="_blank" class="doc-btn">
									<i class="fa fa-file-pdf-o"></i> MPR
								</a>
							@endif
							@if($mpr->signed_attendance)
								<a href="{{ route('view.uploadedfile', Crypt::encrypt($mpr->signed_attendance)) }}" target="_blank" class="doc-btn">
									<i class="fa fa-file-pdf-o"></i> Attendance
								</a>
							@endif
							@if($mpr->signed_supporting)
								<a href="{{ route('view.uploadedfile', Crypt::encrypt($mpr->signed_supporting)) }}" target="_blank" class="doc-btn">
									<i class="fa fa-file-pdf-o"></i> Supporting Document
								</a>
							@endif
                        </div>
                        <div class="mpr-meta">
                            <span>
                                <i class="fa fa-calendar"></i>
                                {{ date('d-m-Y', strtotime($mpr->submission_date)) }}
                            </span>
                            <span>
                                <i class="fa fa-calendar"></i>
                                {{ date('F', mktime(0, 0, 0, $mpr->mpr_month, 1)) }}
                                -{{ $mpr->mpr_year }}
                            </span>
                        </div>

                    </div>


                    <div class="mpr-value-container">
                        <div class="value-box">
                            <span class="value-label">
                                MPR Value
                            </span>
                            <strong>
                                INR
                                <span class="format-indian"
                                      data-value="{{ $mpr->mpr_value }}">
                                </span>
                            </strong>
                        </div>


                        <div class="value-box approved-value">
                            <span class="value-label">
                                Approved Value
                            </span>
                            <strong>
                                INR
                                <span class="format-indian"
                                      data-value="{{ $mpr->mpr_approved_value }}">
                                </span>
                            </strong>
                        </div>
                    </div>
                </div>
                @if($mpr->attendance_summary->count() > 0)

                    <div class="attendance-section">
                        <div class="section-heading">
                            <div>
                                <i class="fa fa-users"></i>
                                Attendance & Salary Summary
                            </div>
                            <span>
                                {{ $mpr->attendance_summary->count() }}
                                Resource{{ $mpr->attendance_summary->count() > 1 ? 's' : '' }}
                            </span>
                        </div>


                        <div class="table-responsive attendance-table-wrapper">
                            <table class="attendance-table">
                                <thead>
                                    <tr>
                                        <th>Resource</th>
                                        <th class="text-center no_wrap">
                                            Documents
                                        </th>
                                        <th class="text-center no_wrap">
                                            Working Days
                                        </th>
                                        <th class="text-center">P</th>
                                        <th class="text-center">A</th>
                                        <th class="text-center">L</th>
                                        <th class="text-center">H</th>
                                        <th class="text-right no_wrap">
                                            Per Day Cost
                                        </th>
                                        <th class="text-right no_wrap">
                                            Actual Salary
                                        </th>
                                        <th class="text-right no_wrap">
                                            Calculated Salary
                                        </th>
                                        <th class="text-right no_wrap">
                                            Net Salary
                                        </th>
                                        <th class="text-right no_wrap">
                                            Deduction
                                        </th>
                                        <th class="text-right no_wrap">
                                            Approved Salary
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach($mpr->attendance_summary as $attendance)

                                        <tr>
                                            <td>
                                                <div class="resource-name">
                                                    {{ $attendance->resource_name }}
                                                </div>
                                                <div class="resource-mobile">
                                                    <i class="fa fa-phone"></i>
                                                    {{ $attendance->resource_mobilenumber }}
                                                </div>
                                            </td>
                                            <td class="text-center amount approved-salary">
											@if($attendance->mpr_file)
											<a href="{{route('view.uploadedfile',Crypt::encrypt($attendance->mpr_file))}}" target="_blank">
												<span class="badge badge-success br-5"><i class="fa fa-file-pdf-o"></i> MPR</span>
											</a>
											@endif
											@if($attendance->attendance_file)
											<a href="{{route('view.uploadedfile',Crypt::encrypt($attendance->attendance_file))}}" target="_blank">
												<span class="badge badge-success br-5"><i class="fa fa-file-pdf-o"></i> Attendance</span>
											</a>
											@endif
											@if($attendance->supporting_file)
											<a href="{{route('view.uploadedfile',Crypt::encrypt($attendance->supporting_file))}}" target="_blank">
												<span class="badge badge-success br-5"><i class="fa fa-file-pdf-o"></i></span>
											</a>
											@endif
                                            </td>

                                            <td class="text-center">
                                                {{ $attendance->total_working_days }}
                                            </td>
                                            <td class="text-center attendance-present">
                                                {{ $attendance->total_present }}
                                            </td>
                                            <td class="text-center attendance-absent">
                                                {{ $attendance->total_absent }}
                                            </td>
                                            <td class="text-center attendance-leave">
                                                {{ $attendance->total_leave }}
                                            </td>
                                            <td class="text-center attendance-holiday">
                                                {{ $attendance->total_holiday }}
                                            </td>
                                            <td class="text-right amount">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->per_day_cost }}">
                                                </span>
                                            </td>
                                            <td class="text-right amount">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->actual_salary }}">
                                                </span>
                                            </td>
                                            <td class="text-right amount">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->calculated_salary }}">
                                                </span>
                                            </td>
                                            <td class="text-right amount">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->net_salary }}">
                                                </span>
                                            </td>
                                            <td class="text-right amount deduction">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->deduction }}">
                                                </span>
                                            </td>
                                            <td class="text-right amount approved-salary">
                                                INR
                                                <span class="format-indian"
                                                      data-value="{{ $attendance->approved_salary }}">
                                                </span>
                                            </td>
                                        </tr>

                                    @endforeach

                                </tbody>

                                <tfoot>

                                    <tr class="font-16">

                                        <td colspan="9" class="total-label">
                                            TOTAL
                                        </td>


                                        <td class="text-right total-amount">

                                            INR
                                            <span class="format-indian"
                                                  data-value="{{ number_format($totalCalculatedSalary,'2','.','') }}">
                                            </span>

                                        </td>


                                        <td class="text-right total-amount">

                                            INR
                                            <span class="format-indian"
                                                  data-value="{{ number_format($totalNetSalary,'2','.','') }}">
                                            </span>

                                        </td>


                                        <td class="text-center">
                                            -
                                        </td>


                                        <td class="text-right total-amount approved-total">

                                            INR
                                            <span class="format-indian"
                                                  data-value="{{ number_format($totalApprovedSalary,'2','.','') }}">
                                            </span>

                                        </td>
                                    </tr>

                                </tfoot>

                            </table>
							
                        </div>
						
                    </div>
                @else

                    <div class="no-attendance">

                        <i class="fa fa-info-circle"></i>

                        No attendance summary available for this MPR.

                    </div>

                @endif

            </div>

        @endforeach

    @endforeach

</div>

<style>

:root {
    --brand-primary: #025964;
    --brand-secondary: #147E8B;
}


/* =========================================================
   INVOICE CONTAINER
========================================================= */

.invoice-modal-container {
    background: #f5f7fa;
    padding: 0;
    color: #333;
    font-size: 13px;
}


/* =========================================================
   INVOICE HEADER
========================================================= */

.invoice-header {
    background: var(--brand-primary);
    color: #fff;

    padding: 18px 20px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    border-radius: 5px 5px 0 0;
}


.invoice-title {
    font-size: 19px;
    font-weight: 600;
    margin-bottom: 7px;
}


.invoice-title i {
    margin-right: 7px;
}


.invoice-meta {
    font-size: 13px;
    opacity: .95;
}


.invoice-meta i {
    margin-right: 4px;
}


.invoice-divider {
    margin: 0 8px;
    opacity: .5;
}


.invoice-header-right {
    display: flex;
    gap: 7px;
    align-items: center;
    flex-wrap: wrap;
    justify-content: flex-end;
}


/* =========================================================
   STATUS BADGES
========================================================= */

.status-badge {
    display: inline-block;

    padding: 6px 10px;

    border-radius: 20px;

    font-size: 11px;
    font-weight: 600;

    white-space: nowrap;
}


.status-badge i {
    margin-right: 4px;
}


.status-warning {
    background: #ffc107;
    color: #222;
}


.status-success {
    background: #28a745;
    color: #fff;
}


/* =========================================================
   INVOICE DOCUMENT BAR
========================================================= */

.invoice-document-bar {
    background: #fff;

    border: 1px solid #e5e9ef;

    margin: 12px 15px;

    padding: 10px 14px;

    border-radius: 5px;

    display: flex;
    justify-content: space-between;
    align-items: center;
}


.invoice-document-bar > div {
    display: flex;
    align-items: center;
}


.pdf-icon {
    font-size: 25px;
    color: #dc3545;
    margin-right: 10px;
}


.invoice-document-bar small {
    display: block;
    color: #888;
    margin-top: 2px;
}


.document-btn {
    background: var(--brand-primary);
    color: #fff !important;

    padding: 6px 12px;

    border-radius: 4px;

    text-decoration: none !important;

    font-size: 12px;

    transition: background .2s ease;
}


.document-btn:hover {
    background: var(--brand-secondary);
    color: #fff !important;
}


/* =========================================================
   MPR CARD
========================================================= */

.mpr-card {
    background: #fff;

    margin: 15px;

    border: 1px solid #e1e6eb;

    border-radius: 6px;

    overflow: hidden;

    box-shadow: 0 2px 6px rgba(0,0,0,.04);
}


/* =========================================================
   MPR HEADER
========================================================= */

.mpr-header {
    background: #f8fafc;

    padding: 14px 16px;

    display: flex;
    justify-content: space-between;
    align-items: center;

    border-bottom: 1px solid #e5e9ef;
}


.mpr-title {
    font-size: 16px;
    font-weight: 600;

    color: var(--brand-primary);

    margin-bottom: 6px;
}


.mpr-title i {
    margin-right: 5px;
}


.mpr-meta {
    color: #777;

    display: flex;

    gap: 18px;

    font-size: 12px;
}


.mpr-meta i {
    margin-right: 4px;
}


/* =========================================================
   MPR VALUE BOXES
========================================================= */

.mpr-value-container {
    display: flex;
    gap: 8px;
}


.value-box {
    padding: 7px 11px;

    background: #fff;

    border: 1px solid #e1e6eb;

    border-radius: 5px;

    text-align: right;
}


.value-label {
    display: block;

    font-size: 10px;

    text-transform: uppercase;

    color: #888;

    margin-bottom: 2px;
}


.value-box strong {
    color: #333;

    font-size: 13px;
}


.approved-value {
    border-left: 3px solid #28a745;
}


.approved-value strong {
    color: #28a745;
}


/* =========================================================
   MPR DOCUMENTS
========================================================= */

.mpr-documents {
    padding: 9px 15px;

    border-bottom: 1px solid #eee;

    display: flex;

    align-items: center;

    gap: 7px;

    flex-wrap: wrap;
}


.documents-title {
    font-weight: 600;

    color: #666;

    margin-right: 4px;
}


.doc-btn {
    display: inline-block;

    padding: 4px 9px;

    border-radius: 4px;

    border: 1px solid #ddd;

    background: #fff;

    color: #555 !important;

    text-decoration: none !important;

    font-size: 11px;

    transition: all .2s ease;
}


.doc-btn i {
    color: #dc3545;

    margin-right: 4px;
}


.doc-btn:hover {
    border-color: var(--brand-primary);

    color: var(--brand-primary) !important;

    background: #f5fafb;
}


/* =========================================================
   ATTENDANCE SECTION
========================================================= */

.attendance-section {
    padding: 0;
}


.section-heading {
    padding: 11px 15px;

    display: flex;

    justify-content: space-between;

    align-items: center;

    background: #fff;

    border-bottom: 1px solid #eee;

    font-weight: 600;

    color: #444;
}


.section-heading i {
    color: var(--brand-primary);

    margin-right: 5px;
}


.section-heading span {
    font-size: 11px;

    font-weight: normal;

    color: #888;
}


/* =========================================================
   ATTENDANCE TABLE
========================================================= */

.attendance-table-wrapper {
    max-height: 430px;

    overflow: auto;
}


.attendance-table {
    width: 100%;

    border-collapse: separate;

    border-spacing: 0;

}


.attendance-table thead th {
    position: sticky;

    top: 0;

    z-index: 2;

    background: var(--brand-primary);

    color: #fff;

    padding: 9px 7px;

    border-right: 1px solid rgba(255,255,255,.15);

    white-space: nowrap;

    font-weight: 600;
}


.attendance-table tbody td {
    padding: 8px 7px;

    border-bottom: 1px solid #eee;

    border-right: 1px solid #f1f1f1;

    background: #fff;

    vertical-align: middle;
}


.attendance-table tbody tr:hover td {
    background: #eef8fa;
}


/* =========================================================
   RESOURCE DETAILS
========================================================= */

.resource-name {
    font-weight: 600;

    color: #333;
}


.resource-mobile {
    color: #999;

    font-size: 10px;

    margin-top: 2px;
}


.resource-mobile i {
    margin-right: 3px;
}


/* =========================================================
   ATTENDANCE STATUS
========================================================= */

.attendance-present {
    color: #28a745;

    font-weight: 600;
}


.attendance-absent {
    color: #dc3545;

    font-weight: 600;
}


.attendance-leave {
    color: #f0ad4e;

    font-weight: 600;
}


.attendance-holiday {
    color: var(--brand-secondary);

    font-weight: 600;
}


/* =========================================================
   AMOUNTS
========================================================= */

.amount {
    white-space: nowrap;

    font-weight: 500;
}


.deduction {
    color: #dc3545;
}


.approved-salary {
    color: #28a745;

    font-weight: 600;
}


/* =========================================================
   TABLE FOOTER
========================================================= */

.attendance-table tfoot td {
    background: #e8f3f5;

    padding: 10px 7px;

    border-top: 2px solid var(--brand-primary);

    font-weight: 600;
}


.total-label {
    color: var(--brand-primary);

    font-size: 12px;
}


.total-amount {
    color: #333;

    white-space: nowrap;
}


.approved-total {
    color: #28a745;
}


/* =========================================================
   NO ATTENDANCE
========================================================= */

.no-attendance {
    padding: 20px;

    text-align: center;

    color: #999;

    background: #fafafa;
}


.no-attendance i {
    margin-right: 5px;
}


/* =========================================================
   MOBILE
========================================================= */

@media(max-width: 767px) {

    .invoice-header {
        display: block;
    }


    .invoice-header-right {
        margin-top: 12px;

        justify-content: flex-start;
    }


    .invoice-meta {
        line-height: 24px;
    }


    .invoice-divider {
        display: none;
    }


    .mpr-header {
        display: block;
    }


    .mpr-value-container {
        margin-top: 12px;

        justify-content: flex-start;

        flex-wrap: wrap;
    }


    .value-box {
        min-width: 130px;
    }


    .invoice-document-bar {
        display: block;
    }


    .document-btn {
        display: inline-block;

        margin-top: 8px;
    }

}

</style>
<script>
document.querySelectorAll('.format-indian').forEach(function(el)
{
    el.style.fontWeight = 'bold';

    if (typeof formatIndianNumber === 'function') {
        el.innerText = formatIndianNumber(el.dataset.value);
    }
});

</script>

