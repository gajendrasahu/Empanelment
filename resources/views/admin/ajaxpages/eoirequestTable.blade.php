@php
$i = 1;
$now = \Carbon\Carbon::now();
@endphp
@foreach($data as $item)
<tr>
	<td class="center width-30">{{$loop->iteration}}</td>
	<td style="position:relative;">
		@if($item->eoinumber)<b>{{$item->eoinumber}}</b><br>@endif{{$item->departmentname}}@if($item->project_name)<br>{{$item->project_name}}@endif
		
		@if($item->iscancelled==0 && $item->isClosed==0)
		<div class="eoi-flags">
			@if($item->eoistatus==0)
				<a class="action-a">
				<span class="eoi-flag eoi-flag--info" class="action-a">
					<i class="fa fa-bell"></i> New Request
				</span>
				</a>
				@permission('prepare.draft')
				<a href="{{route('prepare.draft',Crypt::encrypt($item->requestid))}}" class="action-a">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-envelope"></i> Draft EoI
					</span>
				</a>
				@endpermission
			@endif
			@if($item->eoistatus==1)
				<a class="action-a">
				<span class="eoi-flag eoi-flag--info" class="action-a">
					<i class="fa fa-paper-plane"></i> Sent To Department
				</span>
				</a>
				@permission('eoipreview')
				<a href="{{route('eoipreview',Crypt::encrypt($item->requestid))}}" class="action-a" target="_blank">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-hand-o-right"></i> Preview
					</span>
				</a>
				@endpermission
			@if($item->isupdated==1)
				@permission('prepare.draft')
				<a href="{{route('prepare.draft',Crypt::encrypt($item->requestid))}}" class="action-a" target="_blank">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-refresh"></i> View Update
					</span>
				</a>
				@endpermission
			@endif			
			@endif
			@if($item->eoistatus==2)
				
				<span class="eoi-flag eoi-flag--success action-a" title="{{$item->departmentname}}" style="background-color:green!important; color:white;">
					<i class="fa fa-thumbs-up"></i> @if($item->ispm==0) Confirmed by Department @else Confirmed by Project Manager @endif
				</span>
				
				@permission('prepare.draft')
				<a href="{{route('prepare.draft',Crypt::encrypt($item->requestid))}}" class="action-a">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-edit"></i> Edit EoI
					</span>
				</a>
				@endpermission
				@permission('update.eoidates')
				<a href="{{route('update.eoidates',Crypt::encrypt($item->requestid))}}" class="action-a">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-calendar"></i> Update Date
					</span>
				</a>
				@endpermission
				@permission('float.eoi')
				<a href="{{route('prepare.float',Crypt::encrypt($item->requestid))}}" class="action-a">
					<span class="eoi-flag eoi-flag--info">
						<i class="fa fa-bullhorn"></i> Publish EoI
					</span>
				</a>
				@endpermission
			@endif
			@if($item->eoistatus==3)
				@if($item->isInterviewDone==0)
					<span class="eoi-flag eoi-flag--info action-a" onclick="ShowFirms('{{Crypt::encrypt($item->requestid)}}')" style="cursor:pointer;">
						<i class="fa fa-users"></i> Published
					</span>
					@if($item->isinprebid==1 && date('Y\-m\-d',strtotime($item->deadlinedate))>$now)
					@permission('prebidenquiry.list')
					<a href="{{route('prebidenquiry.list', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-question"></i> In Pre-Bid
						</span>
					</a>
					@endpermission
					@endif
					@if($item->eoistatus==3 && date('Y\-m\-d',strtotime($item->deadlinedate))<$now && $item->isParticipated==0)
					@permission('extend.eoidate')
					<a href="{{route('extend.eoidate', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info" style="background-color:red!important; color:white;">
							<i class="fa fa-calendar"></i> Extend Date (No Participants)
						</span>
					</a>
					@endpermission
					@endif			
					@if($item->isParticipated==1 && $now>date('Y\-m\-d',strtotime($item->deadlinedate)))
					@permission('view.pptresumes')
					<a href="{{route('view.pptresumes', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-users"></i> Vendors
						</span>
					</a>
					@endpermission
					@endif
				@else
					@if($item->isParticipated==1 && $now>date('Y\-m\-d',strtotime($item->deadlinedate)))
					@permission('view.pptresumes')
					<a href="{{route('view.pptresumes', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-users"></i> Vendors
						</span>
					</a>
					@endpermission
					@endif
				@endif
			@endif
			@if($item->eoistatus==4)
				@if($item->isordered==1)				
					<span class="eoi-flag eoi-flag--info action-a">
						<i class="fa fa-thumbs-up"></i> Order Issued
					</span>
					@permission('generate.workorder')
					<a href="{{route('generate.workorder', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-plus"></i> Add Order
						</span>
					</a>
					@endpermission
					@if($item->isClosed==0)
					@permission('close.project')
					<a href="{{route('close.project', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-close"></i> Close Project
						</span>
					</a>
					@endpermission
					@endif
				@else
					@if($item->isInterviewDone==0)
					@permission('upload.committee')
					<a href="{{route('prepare.committee', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-plus"></i> Add Committee Member
						</span>
					</a>
					@endpermission
					@endif
					@if($item->isInterviewDone==1)
						<span class="eoi-flag eoi-flag--info action-a">
							<i class="fa fa-calendar"></i> Interview Done <i class="fa fa-check"></i>
						</span>
					@endif
					@if($item->isMomReceived==1)
						<span class="eoi-flag eoi-flag--info action-a">
							<i class="fa fa-file-pdf-o"></i> MoM Received <i class="fa fa-check"></i>
						</span>
					@endif
					@if($item->isOrderFileReceived==1)
						<span class="eoi-flag eoi-flag--info action-a">
							<i class="fa fa-file-pdf-o"></i> Order Received <i class="fa fa-check"></i>
						</span>
					@endif
					@permission('view.pptresumes')
					<a href="{{route('view.pptresumes', Crypt::encrypt($item->requestid))}}" class="action-a">
						<span class="eoi-flag eoi-flag--info">
							<i class="fa fa-users"></i> Vendors
						</span>
					</a>
					@endpermission
				@endif
			@endif

			@permission('showprice.comparison')
			<a onclick="showPrice('{{ route('showprice.comparison',[Crypt::encrypt('No'),Crypt::encrypt($item->requestid)]) }}')" style="cursor:pointer;" class="action-a" >
				<span class="eoi-flag eoi-flag--success">
					<i class="fa fa-calculator"></i> Pricing
				</span>
			</a>
			@endpermission
			
		</div>
		@else
			@if($item->iscancelled==1)
			<br><b>Reason for Cancellation</b><br>{!!$item->cancellation_remark!!}
			<br>
			<span class="eoi-flag eoi-flag--danger action-a">
				<i class="fa fa-info-circle"></i> Cancelled
			</span>

			<span class="eoi-flag eoi-flag--danger action-a">
				<i class="fa fa-calendar"></i> Date : @if($item->canReqOn) {{date('d-m\-Y',strtotime($item->canReqOn))}} @endif
			</span>

			<a href="{{route('view.uploadedfile', Crypt::encrypt($item->cancellation_attachment))}}" target="_blank" class="action-a">
				<span class="eoi-flag eoi-flag--danger">
					<i class="fa fa-file-pdf-o"></i> View File
				</span>
			</a>
			@endif
			@if($item->isClosed==1)
			<br>
			<span class="eoi-flag eoi-flag--danger action-a">
				<i class="fa fa-close"></i> Project Closed
			</span>
			@if($item->closure_file)
			<a href="{{route('view.uploadedfile', Crypt::encrypt($item->closure_file))}}" target="_blank" class="action-a">
				<span class="eoi-flag eoi-flag--danger">
					<i class="fa fa-file-pdf-o"></i> Closure File
				</span>
			</a>
			@endif
			@endif
		@endif
		
	</td>
	<td class="center width-100 v-top">@if($item->releasedate){{date('d\-m\-Y',strtotime($item->releasedate))}} @else TBD @endif</td>
	<td class="center width-100" style="position:relative;">
		@if($item->prebidlastdate){{date('d\-m\-Y',strtotime($item->prebidlastdate))}} @else TBD @endif
	</td>
	<td class="center width-100">@if($item->deadlinedate){{str_replace(', 12:00 AM','',date('d\-m\-Y, h:i A',strtotime($item->deadlinedate)))}} @else TBD @endif</td>
	<td class="center width-30" @permission('more.eoidata') onclick="viewMoreData('{{route('more.eoidata')}}','{{Crypt::encrypt($item->requestid)}}')" @endpermission>
		@permission('more.eoidata') <i class="fa fa-angle-double-right"></i><i class="fa fa-angle-double-right"></i> @endpermission
	</td>
</tr>
@endforeach
@if($data->count()==0)
	<tr>
		<td class="padding-10" colspan="6" style="text-align:center;">
			<br>
			<i class="fa fa-warning blue nodata"></i><br>
			{!! __('messages.sorry') !!}
		</td>
	</tr>
@else
	<tr>
		<td colspan="6" style="text-align:right;">
			{{ $data->links('vendor.pagination.default') }}
		</td>
	</tr>
@endif

<script>
	const paginationLinks = document.querySelectorAll('.pagination a');
	paginationLinks.forEach(link => {
		link.onclick = function (event) {
			event.preventDefault(); // Prevent default link behavior
			const page = this.getAttribute('href').split('page=')[1]; // Extract page number from URL
			const url = '{{ route('eoirequest.html') }}'; // URL generated by Laravel route
			loadData(page, url); // Call custom function with page number and URL
		};
	});


$('.sendto-approval').on('click', function () {
	
    var docUrl = $(this).data('url');
    var params = {
    };
	
    $(".modal-xl").css("width", "90%");
    $('#approvalModal').modal('show');
	$('#approvalData').html("Please wait...");
    $.get(docUrl, params, function (data) {
        setTimeout(function () {
            $('#approvalData').html(data.approvalform);
        }, 2000);
    }).fail(function () {
        $('#approvalData').html('<p class="text-danger">Failed to load EoI.</p>');
    });
	
});
	
</script>
