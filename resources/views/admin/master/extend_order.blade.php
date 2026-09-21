@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp
<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14">
					<i class="green ace-icon fa fa-list-alt bigger-120 datalist"></i> Extend Work Order
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="extend" id="extend" action="{{route('update.deploymentdate',Crypt::encrypt($order->orderid))}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12" style="margin-top:20px!important;">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif
				@if($errors->any())
				<div class="col-sm-12" style="margin-top:20px!important;">
					<div class="alert alert-block alert-danger">
						<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						@foreach ($errors->all() as $error)
							<i class="fa fa-warning"></i> {{ $error }}<br>
						@endforeach
					</div>
				</div>
				@endif		
				
				@csrf
				<div class="form-group">
					<div class="content-detail">
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">fact_check</i> Order Number : {{ $order->ordernumber }}
								</div>
								<div class="details-detail">
								<b class="form-label">Order Date : </b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }}
								
								</div>
								
							</div>
							<a href="{{route('view.uploadedfile',Crypt::encrypt($order->signedcopy))}}" target="_blank" class="action-a">
								<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
									<i class="fa fa-hand-o-right icon-animated-bell "></i> Signed Work Order File
								</span>
							</a>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $order->project_name }}
								</div>
								<div class="details-detail lh-25">
									<b>Department / Project Manager : </b>{{ $order->departmentname }}<br>
									@if($order->refrence)EoI Number: <b>{{ $order->refrence }}</b> | Order Date : <b>{{ date('d\-m\-Y',strtotime($order->orderdate)) }}</b> | @endif Project Duration : {{ ucwords(strtolower($order->project_duration)) }} Months<br>
									
								</div>
							</div>
						</div>
						<div class="ui-card-detail">
							<div>
								<div class="title-detail">
									<i class="material-icons-outlined">topic</i> {{ $vendor->companyname }}
								</div>
								<div class="details-detail">
									<label class="form-label">Official Email :</label> {{$vendor->officialemail}}
								</div>
							</div>
						</div>


						
						<div class="section-block-detail">
							<div class="section-header-detail" style="position:relative;">
								<span class="material-icons-outlined">group_add</span>
								&nbsp;<h2 class="section-title-detail">Resource Details</h2>
								
								<select name="project_duration" id="project_duration" class="project_selection">
									<option value="">--Please Select Project Duration--</option>
									@for($i=1;$i<=60;$i++)
									<option value="{{$i}}">{{$i}} Month</option>
									@endfor
								</select>
								
							</div>
							<div class="table-responsive">
							@if($order->categoryid==2)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="center padding-10 font-bold width-100">
										<!--<input type="checkbox" class="allResource" style="vertical-align:text-top; margin-top:2px;">--> Extension
									</td>
									<td class="padding-10 font-bold">Sector</td>
									<td class="padding-10 font-bold">Position</td>
									<td class="padding-10 font-bold">Name</td>
									<td class="padding-10 font-bold text-right">Base Price</td>
									<td class="padding-10 font-bold text-right">Duration</td>
									<td class="padding-10 font-bold text-right">Total</td>
								</tr>
								@php
								$total		=	0;
								$taxtotal	=	0;
								$admintotal	=	0;
								$grandtotal	=	0;
								@endphp
								@foreach($resources as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10 center v-top">
										<input type="checkbox" class="resource" name="resources[]" value="{{$req->deploymentid}}">
									</td>
									<td class="padding-10 v-top">{{ucwords(strtolower($req->sectorname))}}</td>
									<td class="padding-10 v-top">{{$req->consultantposition}}</td>
									<td class="padding-10 v-top">{{$req->name}}</td>
									<td class="padding-10 v-top format-indian text-right remuneration" data-value="{{$req->remuneration}}"></td>
									<td class="padding-10 v-top text-right duration">
										<select name="duration[]" class="durations">
											<option value="">-Duration-</option>
										</select>
									</td>
									<td class="padding-10 v-top format-indian text-right row-total" data-value="0" nowrap></td>
								</tr>
								@endforeach
								@if($resources->count()!=0)
								<tr>
									<td class="padding-10 text-right" colspan="7"><b>Total</b></td>
									<td class="padding-10 format-indian text-right total" data-value="0"></td>
								</tr>
								<tr>
									<td class="padding-10 text-right" colspan="7"><b>Tax @ {{$order->tax}}</b></td>
									<td class="padding-10 format-indian text-right tax-total" data-value="0"></td>
								</tr>
								@if($order->admincharge!=0)
								<tr>
									<td class="padding-10 text-right" colspan="7"><b>Admin Charge @ {{$order->admincharge}}</b></td>
									<td class="padding-10 format-indian text-right admin-total" data-value="0"></td>
								</tr>
								@endif
								<tr>
									<td class="padding-10 text-right" colspan="7"><b>Grand Total</b></td>
									<td class="padding-10 format-indian text-right grand-total" data-value="0"></td>
								</tr>
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="8">
										<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
											<i class="fa fa-save"></i> Extend Work Order
										</button>
									</td>
								</tr>
								@endif
							</table>
							@endif
							@if($order->categoryid==1)
							<table class="mytable" border="1" style="text-transform: none!important;">
								<tr class="myheadbg">
									<td class="center padding-10 font-bold" style="width:30px;">S.No.</td>
									<td class="padding-10 font-bold">Position & Experience</td>
									<td class="padding-10 font-bold">Name</td>
									<td class="padding-10 font-bold">Mobile Number</td>
									<td class="padding-10 font-bold">Email</td>
									<td class="padding-10 font-bold">Work Order Issuance Date</td>
								</tr>
								@php
								$totalmanmonth		=	0;
								$adminchargetotal	=	0;
								$grandtotal			=	0;
								@endphp
								@foreach($resources as $index=>$req)
								<tr>
									<td class="padding-10 center v-top">{{$loop->iteration}}</td>
									<td class="padding-10">
									{{ucwords(strtolower($req->role))}}<br>Level-{{$req->experiencelevel}}<br>{{$req->experience}}
									</td>
									<td class="padding-10">
										<input type="hidden" name="deploymentid[]" value="{{Crypt::encrypt($req->deploymentid)}}">
										<input type="text" name="candidatename[]" value="{{old('candidatename.' . $index, $req->name)}}" class="form-control" placeholder="Name" autocomplete="off">
									</td>
									<td class="padding-10">
										<input type="text" name="mobilenumber[]" value="{{old('mobilenumber.' . $index, $req->mobilenumber)}}" class="form-control" placeholder="Mobile number" autocomplete="off">
									</td>
									<td class="padding-10">
										<input type="text" name="email[]" value="{{old('email.' . $index, $req->email)}}" class="form-control" placeholder="Email" autocomplete="off">
									</td>
									<td class="padding-10">
										@if(!old('deploymentdate.'.$index))
										<input type="text" name="deploymentdate[]" @if($req->deployment_date!='')value="{{date('d\-m\-Y',strtotime($req->deployment_date))}}"@endif class="form-control deploymentdate" placeholder="dd-mm-YYYY" autocomplete="off">
										@else
										<input type="text" name="deploymentdate[]" value="{{date('d\-m\-Y',strtotime(old('deploymentdate.'.$index)))}}" class="form-control deploymentdate" placeholder="dd-mm-YYYY" autocomplete="off">
										@endif
									</td>
								</tr>
								@endforeach
								@if($detail->count()!=0)
								<tr>
									<td class="padding-10" style="text-align:right;" colspan="6">
										<button type="button" class="btn btn-info" onclick="SetDeploymentDate()">
											<i class="fa fa-calendar"></i> Set Work Order Issuance Date
										</button>
									</td>
								</tr>
								@endif
							</table>

							@endif
							</div>
						</div>
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>

<script>
function SetDeploymentDate()
{
    let names 	=	document.getElementsByName("candidatename[]");
    let numbers =	document.getElementsByName("mobilenumber[]");
    let emails 	=	document.getElementsByName("email[]");
    let dates 	=	document.getElementsByName("deploymentdate[]");

    let atLeastOneFilled = false;

    for(let i = 0; i < names.length; i++)
	{
        //if(names[i].value.trim()!=="" && numbers[i].value.trim()!=="" && emails[i].value.trim()!=="" && dates[i].value.trim()!== "")
		if(dates[i].value.trim()!== "")
		{
            atLeastOneFilled = true;
            break;
        }
    }

    if (!atLeastOneFilled) {
        bootbox.alert("Please set at least one deployment date.");
        return;
    }
	finalSubmit();
}
function finalSubmit()
{
	bootbox.confirm('Do you want to submit the deployment date details?',function(result){
		if(result)
		{
			$("#setdeploymentdate").submit();
		}
	});
}

/*
$('.allResource').on('change', function () {
  $('.resource').prop('checked', this.checked);
});
*/
$('.resource').on('change', function () {
    if ($('.resource:checked').length === 0) {
        $('.allResource').prop('checked', false);
    }
});
</script>
<script>

@if($order->categoryid==2)
$(document).ready(function () {

    let TAX_PERCENT = parseFloat($('.tax-total').closest('tr').find('b').text().match(/\d+/)) || {{ $order->tax }};
    let ADMIN_PERCENT = {{ $order->admincharge ?? 0 }};

    $('.project_selection').on('change', function () {

        let months = parseInt($(this).val());
        let options = '<option value="">-Duration-</option>';

        if (!isNaN(months)) {
            for (let i = 1; i <= months; i++) {
                options += `<option value="${i}">${i} Month</option>`;
            }
        }

        $('.durations').html(options);
		//$('.resource').prop('checked', true);
		$('.durations').val(months);
        calculateTotals();
    });

    $(document).on('change', '.durations, .resource', function () {
        calculateTotals();
    });

    function calculateTotals() {

        let total = 0;

        $('.mytable tr').each(function () {

            let checkbox = $(this).find('.resource');

            if (!checkbox.length) return;

            let duration = parseInt($(this).find('.durations').val());
            let remuneration = parseFloat($(this).find('.remuneration').data('value')) || 0;
            let rowTotalCell = $(this).find('.row-total');

            if (!checkbox.is(':checked') || isNaN(duration)) {
                rowTotalCell.data('value', 0).text('0');
                return;
            }

            let rowTotal = duration * remuneration;
            total += rowTotal;

            rowTotalCell
                .data('value', rowTotal)
                .text(rowTotal.toLocaleString('en-IN'));
        });

        $('.total')
            .data('value', total)
            .text(total.toLocaleString('en-IN'));

        let taxTotal = (total * {{ $order->tax }}) / 100;
        $('.tax-total')
            .data('value', taxTotal)
            .text(taxTotal.toLocaleString('en-IN'));

		let adminBase	=	total + taxTotal;
        let adminTotal	=	(adminBase * {{ $order->admincharge ?? 0 }}) / 100;

        $('.admin-total')
            .data('value', adminTotal)
            .text(adminTotal.toLocaleString('en-IN'));

        let grandTotal	=	total + taxTotal + adminTotal;
        $('.grand-total')
            .data('value', grandTotal)
            .text(grandTotal.toLocaleString('en-IN'));
    }
});
@endif
</script>

<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
<script>

document.querySelectorAll('.format-indian').forEach(function(el) {
    el.innerText = formatIndianNumber(el.dataset.value);
});

</script>
@endsection