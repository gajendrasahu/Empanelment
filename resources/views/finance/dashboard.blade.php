@extends('admin.admin_master')
@section('admin')
<div class="main-content">

	<div class="main-content-inner">
		<div class="col-md-3 padding-10">
			<div class="card">
				<div class="card-body">
					<h5>Pending Vendor Invoices</h5>
					<h2>{{ $data['pendingVendorInvoices'] }}</h2>
				</div>
			</div>
		</div>
		<div class="col-md-3 padding-10">
			<div class="card">
				<div class="card-body">
					<h5>Pending Vendor Payments</h5>
					<h2>{{ $data['pendingVendorPayments'] }}</h2>
				</div>
			</div>
		</div>		
	</div>

</div>
@endsection
