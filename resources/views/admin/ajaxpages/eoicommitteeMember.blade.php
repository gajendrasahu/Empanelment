<div class="modal-header">
	<h5 class="modal-title" style="float:left;">{{$eoi->eoinumber}}</h5>
	<button type="button" class="btn btn-close" style="float:right;" data-bs-dismiss="modal" onclick="ClsList()">Close</button>
</div>
<div class="modal-body">
<form name="comMember" id="comMember" action="#" method="post" enctype="multipart/form-data">
	<table class="mytable pd-8" border="1">
		<tr class="myheadbg"><td colspan="8"><b>Committee Members</b></td></tr>
		<tr class="myheadbg">
			<td class="center width-40" nowrap><b>S. No.</b></td>
			<td><b>Name</b></td>
			<td><b>Mobile Number</b></td>
			<td><b>Email</b></td>
			<td><b>Designation</b></td>
			<td><b>Department</b></td>
			<td></td>
			<td></td>
		</tr>
		@foreach($data as $member)
		<tr>
			<td class="center">{{$loop->iteration}}</td>
			<td>
				<select name="memberid[]" class="form-control">
					@foreach($activeMembers as $active)
					<option value="{{$active->memberid}}" @if($active->memberid==$member->memberid) selected @endif>{{$active->name}}</option>
					@endforeach
				</select>
			</td>
			<td style="width:200px;">{{$member->mobilenumber}}</td>
			<td>{{$member->email}}</td>
			<td>{{$member->designation}}</td>
			<td>{{$member->department}}</td>
			<td style="width:120px;">
				<button type="button" class="btn btn-info gridbtn updateBtn" data-recordid="{{Crypt::encrypt($member->recordid)}}" data-requestid="{{Crypt::encrypt($eoi->requestid)}}" style="width:100px;"><i class="fa fa-refresh"></i> Update</button>
			</td>
			<td style="width:120px;">
				<button type="button" class="btn btn-info gridbtn removeBtn" data-recordid="{{Crypt::encrypt($member->recordid)}}" data-requestid="{{Crypt::encrypt($eoi->requestid)}}" style="width:100px;"><i class="fa fa-remove"></i> Remove</button>
			</td>
		</tr>
		@endforeach
		@if($data->count()==0)
		<tr><td colspan="8" class="center">--No committee members added.--</td></tr>
		@endif
		<tr>
			<td></td>
			<td>
				<select name="memid" id="memid" class="form-control">
					<option value="">--Select Name--</option>
					@foreach($moreMember as $more)
					<option value="{{$more->memberid}}">{{$more->name}}</option>
					@endforeach
				</select>
			</td>
			<td>
				<button type="button" class="btn btn-info gridbtn addMoreBtn" data-requestid="{{Crypt::encrypt($eoi->requestid)}}" style="width:180px;">
					<i class="fa fa-plus-circle"></i> Add Committee Member
				</button>
			</td>
			<td colspan="4"></td>
		</tr>
	</table>
</form>
</div>
<div class="modal-footer"></div>
