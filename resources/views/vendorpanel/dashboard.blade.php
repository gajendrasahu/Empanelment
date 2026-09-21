
@extends('vendorpanel.vendor_master')
@section('vendorpanel')


<div class="main-content">
	<div class="main-content-inner">
		<div class="page-content" style="padding:0px 15px;">			 			
	    @if(Session::has('success'))
			<br>
	        <div class="alert alert-success"><i class="fa fa-smile-o"></i> {{ strtoupper(Session::get('success')); }} {{ strtoupper(Session::get('vendorName')); }}!</div>
	    @endif
		

<!--
<a href="{{ route('set-locale', ['locale' => 'en']) }}">English</a>
<a href="{{ route('set-locale', ['locale' => 'hn']) }}">Hindi</a>
-->

		</div>
	</div>
</div>

@endsection