<!DOCTYPE html>
<html lang="en">
@include('vendorpanel.body.header')
@inject('detect', 'Detection\MobileDetect')

<body class="no-skin" style="padding-right:0px!important;">
	<div id="navbar" class="navbar navbar-default ace-save-state">
		<button type="button" class="navbar-toggle menu-toggler pull-left" id="menu-toggler" data-target="#sidebar">
			<span class="sr-only">Toggle sidebar</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		</button>

		<div class="navbar-header pull-left">
			@if($detect->isMobile())
				<a href="{{ route('vendorpanel/dashboard') }}" style="font-size:22px;"
					class="navbar-brand">{{ __('common.vendorpanelname') }}</small></a>
			@else
				<a href="{{ route('vendorpanel/dashboard') }}"
					class="navbar-brand">{{ __('common.vendorpanelname') }}</small></a>
			@endif
		</div>

		<div class="navbar-container ace-save-state" id="navbar-container" @if($detect->isMobile()) style="float:right;"
		@endif>
			<div class="navbar-buttons navbar-header pull-right" role="navigation">
				<ul class="nav ace-nav">
					@if($logindata->verificationstatus == 0)
						<li>
							<a href="#" style="background-color:orange!important;">
								<i class="ace-icon fa fa-warning icon-animated-vertical"></i> UN VERIFIED

							</a>
						</li>
					@else
						<li>
							<a href="#" style="background-color:lightgreen!important;">
								<i class="ace-icon fa fa-check icon-animated-vertical"></i> VERIFIED

							</a>
						</li>
					@endif

					<li class="light-blue dropdown-modal">
						<a data-toggle="dropdown" href="#">
							@if($logindata->profilepic == '')
								<img class="nav-user-photo" src="{{ asset('panel/assets/images/basic/avtar1.png') }}"
									alt="" />
							@else
								<img class="nav-user-photo" src="{{ asset('storage/' . $logindata->profilepic) }}" alt="" />
							@endif
							{{ strtoupper(Session::get('vendorName')) }}
							<i class="ace-icon fa fa-caret-down"></i>
						</a>

						<ul
							class="user-menu dropdown-menu-right dropdown-menu dropdown-yellow dropdown-caret dropdown-close">

							<li>
								<a href="{{ route('edit.profile', Session::get('vendorId')) }}">
									<i class="ace-icon fa fa-user"></i>
									{{ __('common.profilemenu') }}
								</a>
							</li>
							<li>
								<a href="{{ route('edit.password', Session::get('vendorId')) }}">
									<i class="ace-icon fa fa-lock"></i>
									{{ __('common.changepassword') }}
								</a>
							</li>

							<li class="divider"></li>

							<li>
								<a href=" {{ route('vendorlogout') }}">
									<i class="ace-icon fa fa-power-off"></i>
									{{ __('common.logout') }}
								</a>
							</li>
						</ul>
					</li>
				</ul>
			</div>
		</div>
	</div>

	<div class="main-container ace-save-state" style="background-color:#F9FAF9!important;" id="main-container">

		@include('vendorpanel.body.vendormenu')

		@yield('vendorpanel')

		@include('vendorpanel.body.footer')

		<a href="#" id="btn-scroll-up" class="btn-scroll-up btn btn-sm btn-inverse">
			<i class="ace-icon fa fa-angle-double-up icon-only bigger-110"></i>
		</a>
	</div><!-- /.main-container -->

	<!-- basic scripts -->

	<link rel="stylesheet" href="{{ asset('panel/assets/css/flatpickr.min.css') }}">
	<script src="{{ asset('panel/assets/js/flatpickr.js') }}"></script>
	<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
	<script>
		const currentDate = new Date();
		const lastMonthDate = new Date(currentDate);
		lastMonthDate.setMonth(lastMonthDate.getMonth() - 1);

		flatpickr("#frmmonth", {
			defaultDate: new Date().setDate(1),
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
		});

		flatpickr("#dom", {
			dateFormat: "d-m-Y",

		});
		flatpickr("#visitdate", {
			dateFormat: "d-m-Y",
			enableTime: false,
		});
		flatpickr("#frmtime", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr("#totime", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: new Date().fp_incr(7),
			minDate: "today",
		});

		flatpickr("#appointfrmtime", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr("#appointtotime", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: new Date().fp_incr(7),
			minDate: "today",
		});

		flatpickr("#deliverydate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today",
		});
		flatpickr("#porderdate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr("#voucherdate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr("#pinvdate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr(".curdate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
			defaultDate: "today"
		});
		flatpickr(".blankdate", {
			dateFormat: "d-m-Y", // Set the desired date format: DD-MM-YYYY
		});

		flatpickr(".dttm", {
			enableTime: true,
			dateFormat: "d-m-Y h:i K",
			defaultDate: new Date(),
		});

	</script>

	<script>
		jQuery(function ($) {

			$('.chosen-select').chosen({ allow_single_deselect: true });

			$('.chosen-container').on('keydown', function (e) {
				if (e.which === 13) {
					e.preventDefault();
					return false;
				}
			});

			/*
				$('.select2').select2({				
					allowClear:true,
					width:'200px',
				});
			*/

			$(".select2").css('width', '200px').select2({
				allowClear: true
			}).on("select2:unselecting", function (e) {
				$(this).data('unselecting', true);
			}).on("select2:opening", function (e) {
				if ($(this).data('unselecting')) {
					$(this).removeData('unselecting');
					e.preventDefault();
				}
			});
			$(".select2").css('margin-top', '0px');

			$('#creditaccountid').on('select2:open', function () {
				$('.select2-dropdown--below').css('width', '300px'); // Adjust width as needed
			});

			$(".numbers").on("keypress", function (event) {
				var inputValue = event.key;
				var currentValue = $(this).val();
				if ((currentValue === "" || currentValue === "0") && inputValue === "0") {
					event.preventDefault();
					return;
				}
				var isValid = /^\d$/.test(inputValue) || (inputValue === '.' && $(this).val().indexOf('.') === -1);

				if (!isValid) {
					event.preventDefault();
				}
			});

			$(".completenumber").on("keypress", function (event) {
				var inputValue = event.key;
				var currentValue = $(this).val();
				if ((currentValue === "" || currentValue === "0") && inputValue === "0") {
					event.preventDefault();
					return;
				}
				var isValid = /^\d$/.test(inputValue);
				if (!isValid) {
					event.preventDefault();
				}
			});



		});

	</script>
	<script src="{{ asset('panel/assets/js/select22.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/chosen.jquery.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/bootstrap.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
	<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>


	<script src="{{ asset('panel/assets/js/bootbox.js') }}"></script>




</body>

</html>