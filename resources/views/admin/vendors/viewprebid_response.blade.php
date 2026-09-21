@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp

<div class="main-content">
<style>
.ace-file-container
{
	height:33px!important;
	line-height:33px!important;
	vertical-align:middle!important;
	padding:2px 5px!important;
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable animated fadeInUp delay-02" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active">
				<a data-toggle="tab" href="#home" class="form-label font-14" onclick="loadData('{{ route('prebid.querieshtml') }}')">
					<i class="green ace-icon fa fa-angle-double-right bigger-120 datalist" style="vertical-align:text-top;"></i>Official Reply to Pre-bid Query
				</a>
			</li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
					<br>
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
					<br>
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
					<br>
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@if ($errors->any())
					<br>
					<div class="col-sm-12">
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
									<i class="material-icons-outlined">topic</i> {{ $eoi->projecttitle }}
								</div>
								<div class="details-detail">
									EoI Number: <span class="form-label">{{$eoi->eoinumber}}</span>
									
								</div>
							</div>
						</div>


						@foreach($data as $rec)
						<div class="section-block-detail" style="position:relative;">
							
								Posted on : {{date('d\-m\-Y, h:i A',strtotime($rec->broadcastedon))}}</b><br><br>
								{!!$rec->broadcastmessage!!}
								@if($rec->attachment!='')
								<a href="{{ route('view.uploadedfile', Crypt::encrypt($rec->attachment)) }}" target="_blank" style="position:absolute; right:10px; top:10px;">
									<span class="label label-white middle animated flipInX delay-02 f-s-10 form-label span-content">
										<i class="fa fa-hand-o-right bigger-120 icon-animated-bell"></i> View Response / Corrigendum
									</span>
									
								</a>
								
								<br>
								@endif
							
						
						</div>
						@endforeach
						
						@if($queries->count()>0)
						<div class="section-block-detail" style="position:relative;">
	<a href="{{route('download.prebidresponse',Crypt::encrypt($eoi->requestid))}}" style="text-decoration:none; float:right; margin-top:-15px!important; margin-bottom:5px!important;">
		<span class="btn btn-info forwardBtn gridbtn">
			<i class="fa fa-download icon-animated-bell "></i> Download Pre-Bid Response
		</span>			
	</a>							

	<table class="mytable pd-8" border="1">

		<tr class="myheadbg">
			<th class="padding-8 center width-50">S.No.</th>
			<th class="padding-8" nowrap>Clause Reference</th>
			<th class="padding-8" nowrap>Clause Detail</th>
			<th class="padding-8" nowrap>Queries with Justification</th>
			<th class="padding-8" nowrap>Response</th>
		</tr>
		@foreach($queries as $query)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>{!!$query->clause_reference!!}</td>
			<td>{!!$query->clause_detail!!}</td>
			<td>{!!$query->queries_with_justification!!}</td>
			<td>{!!$query->answer!!}</td>
		</tr>
		@endforeach

	</table>
						</div>
						@endif
					</div>
				
					<div class="col-sm-12 tabledata">
<table class="" style="text-transform: none!important; width:100%;">
	<tr>
		<td class="" style="text-align:right;">
		</td>
	</tr>
</table>
						
					</div>
					<div class="col-sm-12">&nbsp;</div>
				</div>		
			</form>
		</div>
	</div>
</div>


</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script>

jQuery(function($) {	

	$('#attachment').ace_file_input({
		no_file:'No File ...',
		btn_choose:'Attachment (if any)',
		btn_change:'Change',
		btn_name:'fourthimage',
		thumbnail:false //| true | large
	});
});	

</script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection