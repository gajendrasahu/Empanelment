@extends('vendorpanel.vendor_master')
@section('vendorpanel')
@php
$t=1;
@endphp
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#recordlist" onclick="loadData(1,'{{ route('openorder.html') }}')" class="datalist"><i class="green ace-icon fa fa-list bigger-120" style="vertical-align:bottom;"></i> {{__('labels.orderlisttab')}}</a></li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<form name="frm" id="frm" action="#" method="post">		
<div id="recordlist" class="tab-pane in active" style="padding:0px 0px!important;">
	<div class="row" style="padding:0px;">
		<div class="col-xs-12" style="padding:0px 2px;">
		<form name="pagedata" id="pagedata" action="#" method="post" onsubmit="return false;">
			<div class="table-responsive">			
				<table class="table table-bordered table-striped" id="tablerecords">
				<thead>
				<tr>
					<td colspan="2">
					<div id="tablehead">
						<select name="pagesize" id="pagesize" class="selectbx" onchange="loadData(1,'{{ route('openorder.html') }}')">
							<option value="50">50</option>
							<option value="100">100</option>
							<option value="300">300</option>
							<option value="500">500</option>														
						</select>
						<select class="select2" name="catid" id="catid" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.categoryname')}}" onchange="loadData(1,'{{ route('openorder.html') }}')" data-width="300">
							<option value=""></option>
							@foreach ($categories as $itm)
							<option value="{{ $itm->categoryid }}">{{ strtoupper($itm->category) }}</option>
							@endforeach
						</select>
						<select class="select2" name="orderstatus" id="orderstatus" onKeyPress="return OnKeyPress(this, event)"  data-placeholder="{{__('labels.orderstatus')}}" onchange="loadData(1,'{{ route('openorder.html') }}')" data-width="170">
							<option value=""></option>
							<option value="0">UN ASSIGNED</option>
							<option value="1">ASSIGNED</option>
							<option value="2">STARTED</option>
							<option value="3">COMPLETED</option>
							<option value="4">ON HOLD</option>
							<option value="-1">CANCELLED</option>
						</select>
						<span class="input-icon" style="float:right;">
							<input type="text" placeholder="Search ..." class="nav-search-input" id="pagesearch" name="pagesearch" autocomplete="off" onchange="loadData(1,'{{ route('openorder.html') }}')" tabindex="<?php echo $t++;?>" />
							<i class="ace-icon fa fa-search nav-search-icon" style="margin-top:-3px;"></i>
						</span>
						
					</div>
					</td>
				</tr>
				</thead>
				<tbody class="tabledata">
				</tbody>
				</table>
			</div>
		</form>
		</div>
	</div>
</div>
</form>
</div>
</div>


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>




	
	</div>
</div>

<div class="modal fade left" id="assignvendor" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content assignvendor" style="font-size:12px; margin:0 auto; margin-top:10px; width:70%!important;">
		</div>
	</div>
</div>

<div class="modal fade left" id="orderpricing" role="dialog" data-backdrop="static">
	<div class="modal-lg-dialog">
		<div class="modal-content orderpricing" style="font-size:12px; margin:0 auto; margin-top:10px; width:90%!important;">
		</div>
	</div>
</div>

<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
function ViewPricing(orderid,r1)
{
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		$("#orderpricing").modal("show");
		$(".orderpricing").html(data);
		$(".numbers").on("keypress", function(event) {
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
		
	});
}

function StoreOrderPrice(r1,orderid)
{
	$(".storepricebtn").prop("disabled","disabled");
	var particular	=	document.getElementById("particular").value;
	var amount		=	document.getElementById("amount").value;
	if(particular!='' && amount!=0 && amount!='')
	{
		$.get(""+r1,
		{
			orderid:orderid,
			particular:particular,
			amount:amount
		},
		function(data, status){
		if(data.status==200)
		{
			document.getElementById("particular").value="";
			document.getElementById("amount").value="";
			
			$("#storeprice").css("display","");
			$("#storeprice").addClass("bg-success");
			$(".storeprice").html(data.message);
			setTimeout(function(){
				$(".storeprice").html("");
				$("#storeprice").css("display","none");
				$(".storepricebtn").prop("disabled","");
				ViewPricing(data.orderid,'{{route("order.viewprice")}}');
			},5000);
		}
		});		
	}
	else
	{
		bootbox.alert("PLEASE ENTER PARTICULAR AND AMOUNT. BOTH ARE MANDATORY FIELDS.");
		return false;
	}
}
function AddAddress(r1,orderid)
{
	$(".addressbtn").prop("disabled","disabled");
	var stateid		=	document.getElementById("statid").value;
	var cityid		=	document.getElementById("ctyid").value;
	var postalcode	=	document.getElementById("postalcode").value;
	var address		=	document.getElementById("completeaddress").value;
	if(stateid=='' || cityid=='' || postalcode=='' || address=='')
	{
		$(".addressbtn").prop("disabled","");
		$("#storeaddress").css("display","");
		$("#storeaddress").addClass("bg-success");
		$(".storeaddress").html("ALL FIELDS ARE MANDATORY.");
		setTimeout(function(){
			$(".storeaddress").html("");
			$("#storeaddress").css("display","none");				
		},3000);
		
		return false;
	}
	$.get(""+r1,
	{
		orderid:orderid,
		stateid:stateid,
		cityid:cityid,
		postalcode:postalcode,
		address:address
	},
	function(data, status){
		if(data.status==200)
		{
			$('#statid').val(null).trigger('change');
			$('#ctyid').val(null).trigger('change');
			document.getElementById("postalcode").value="";
			document.getElementById("completeaddress").value="";
			
			$("#storeaddress").css("display","");
			$("#storeaddress").addClass("bg-success");
			$(".storeaddress").html(data.message);
			setTimeout(function(){
				$(".storeaddress").html("");
				$("#storeaddress").css("display","none");
				$(".addressbtn").prop("disabled","");
				ViewVendors('{{route("order.assignvendor")}}',data.orderid);
			},3000);
		}
	});	
}

function AssignVendor(r1,orderid)
{
	var addressid	=	$("input[name='addressid']:checked").val();
	var vendorid	=	document.getElementById("vendid").value;
	var employeeid	=	document.getElementById("employeeid").value;
	if(vendorid=='' || addressid=='')
	{
		bootbox.alert("PLEASE SELECT VENDOR NAME AND SERVICE ADDRESS DETAIL");
		return false;
	}
	$.get(""+r1,
	{
		orderid:orderid,
		vendorid:vendorid,
		employeeid:employeeid,
		addressid:addressid
	},
	function(data, status){
		if(data.status==200)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-success");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignvendor").html("");
				$("#assignvendor").modal("hide");
				loadData(1,'{{ route("order.html") }}');				
			},3000);
		}
		if(data.status==201)
		{
			$("#assignmsg").css("display","");
			$("#assignmsg").addClass("bg-warning");
			$(".assignmsg").html(data.message);
			setTimeout(function(){
				$(".assignmsg").html("");
				$("#assignmsg").css("display","none");
			},10000);
		}

	});
}
function ViewVendors(r1,orderid)
{
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		$("#assignvendor").modal("show");					
		$(".assignvendor").html(data);
		$(".select2").css('width','200px').select2({
			allowClear: true
		}).on("select2:unselecting", function (e) {
			$(this).data('unselecting', true);
		}).on("select2:opening", function (e) {
		if ($(this).data('unselecting')) {
			$(this).removeData('unselecting');
			e.preventDefault();
			}
		});
		$(".select2").css('margin-top','0px');
	});
}

function Close()
{

	$("#assignvendor").modal("hide");
	$(".orderpricing").html("");
	$("#orderpricing").modal("hide");
}


function togglePasswordVisibility() {
	var passwordInput = $('#loginpassword');
	var toggleIcon = $('.toggle-password img');
	if(passwordInput.attr('type') === 'password')
	{
		passwordInput.attr('type', 'text');
	}
	else
	{
		passwordInput.attr('type', 'password');
	}
}

setTimeout(function() { $('.datalist').trigger('click'); },1000);
function loadData(page,r1)
{
	var pagesize		=	document.getElementById("pagesize").value;
	var pagesearch		=	document.getElementById("pagesearch").value;
	var categoryid		=	document.getElementById("catid").value;
	var orderstatus		=	document.getElementById("orderstatus").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
		categoryid:categoryid,
		orderstatus:orderstatus
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").removeClass("selected-color").addClass("reset-color");
			$(this).removeClass("reset-color").addClass("selected-color");
		});
	});
}

function SetStartTime(orderid,r1,ind)
{
	$(".startbtn").prop("disabled","disabled");
	$.get(""+r1,
	{
		orderid:orderid,
	},
	function(data, status){
		if(data.status==200)
		{
			$("#startendmsg"+ind).css("display","");
			$("#startendmsg"+ind).addClass("bg-success");
			$(".startendmsg"+ind).html(data.message);
			setTimeout(function(){
				$(".startendmsg"+ind).html("");
				$("#startendmsg"+ind).css("display","");
				loadData(1,'{{ route("order.html") }}');				
			},3000);
		}
	});

}
function SetEndTime(orderid,r1,ind)
{
	bootbox.confirm('DO YOU WANT TO SUBMIT END TIME FOR THIS ORDER!',function(result){
		if(result)
		{
			$(".endbtn").prop("disabled","disabled");
			$.get(""+r1,
			{
				orderid:orderid,
			},
			function(data, status){
				if(data.status==200)
				{
					$("#startendmsg"+ind).css("display","");
					$("#startendmsg"+ind).addClass("bg-success");
					$(".startendmsg"+ind).html(data.message);
					setTimeout(function(){
						$(".startendmsg"+ind).html("");
						$("#startendmsg"+ind).css("display","");
						loadData(1,'{{ route("order.html") }}');				
					},3000);
				}
			});
		}
	});
}

</script>

<script src="{{ asset('panel/assets/js/master.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection