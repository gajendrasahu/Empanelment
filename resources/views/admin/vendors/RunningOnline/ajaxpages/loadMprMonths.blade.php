@php
$i=1;
$j=0;
@endphp
@foreach ($months as $month)
@php
$i	=	str_pad($i+1, 2, '0', STR_PAD_LEFT);
$j	=	$j+1;
@endphp

<span class="label label-white middle animated flipInX delay-{{$i}} f-s-20 form-label span-content" style="width:100px!important; height:90px; margin-right:20px!important; margin:0 auto; margin-bottom:15px; border-radius:0px 15px 0px 15px; cursor:pointer;" onclick="loadResources('{{route('mpr.resources')}}','{{$orderid}}',{{$month['mprid']}},{{$month['month']}},{{$month['year']}})">
	<i class="fa fa-calendar"></i>
	<div class="year">{{$month['year']}}</div>
	<div class="month">{{$month['month_name']}}</div>
	<div class="year">{{$month['label']}}</div>
</span>
@endforeach
@if($j==0)
<span style="width:100%; text-align:center!important;">
<div class="form-label font-16" style="line-height:30px;">
	<i class="fa fa-warning"></i>
	<div class="year">Sorry!</div>
	<div class="">You have submitted all your MPRs. There are currently no pending MPRs to submit to the department.</div>
</div>
</span>

@endif
<script>

$(".modal-title").html('<i class="fa fa-calendar"></i> Monthly Progress Report — Generate or Update MPRs');

if($('.backBtn').length)
{
    $('.backBtn').hide();
}

if($('.goAheadBtn').length)
{
    $('.goAheadBtn').hide();
}

if($('.mark_attendance').length)
{
    $('.mark_attendance').hide();
}

function loadResources(r1,orderid,mprid,month,year)
{
	$.get(""+r1,
	{
		orderid:orderid,
		mprid:mprid,
		month:month,
		year:year,
	},
	function(data, status){
		if(data.status==400)
		{
			bootbox.alert(data.message);
		}
		else
		{
			$('#paymentContent').html(data.data);
		}
	});
}

</script>