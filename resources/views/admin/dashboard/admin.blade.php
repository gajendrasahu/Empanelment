<div class="main-content">
	<div class="main-content-inner">
		<div class="col-sm-12 height-space"></div>
		<div class="col-sm-12">
			<div class="dashboard-header">
				<h1 class="welcome-title">Welcome, {{ucwords(strtolower(Session('userName')))}}
					<span class="engagement-select">
						<select name="firm_type" id="firm_type" class="shadcn-select"
							onchange=" loadData('{{ route('admindashboard.html') }}');">
							<option value="">All Engagements</option>
							<option value="1">AWD</option>
							<option value="2" selected>CSF</option>
						</select>
						<i class="ace-icon fa fa-angle-down"></i>
					</span>
				</h1>

				<p class="subtitle">
				Stay up to date with daily activities, reports, and announcements from CHiPS, Departments and Firms.
				</p>
			</div>
		</div>
		<div class="dashboardContent">
		</div>
	</div>
</div>
<script>
	//setTimeout(function() { loadData('{{ route('firmwiseproject.html') }}'); },1000);

	setTimeout(function () { loadData('{{ route('admindashboard.html') }}'); }, 1000);

	function loadData(r1) {

		var firm_type = document.getElementById("firm_type").value;
		$.get("" + r1,
			{
				firm_type: firm_type,
			},
			function (data, status) {
				$(".dashboardContent").html(data);
			});
	}

</script>
