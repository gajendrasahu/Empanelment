<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
	<th nowrap style="width:75px; text-align:center;">PROFILE PIC</th>
    <th nowrap>CATEGORY</th>
	<th nowrap>SUB CATEGORY</th>
    <th nowrap>PROFILE NAME</th>	
	<th nowrap>FEES</th>
	<th nowrap>DESCRIPTION</th>
	<th nowrap style="text-align:center;">JOINED ON</th>
    <th style="width:25px;"></th>
    <th style="width:25px;"></th>
</tr>
</thead>
<tbody>
@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td nowrap style="text-align:center; padding:0px;">
	@if($item->profilepic!='')
    <img src="{{ asset('storage/'.$item->profilepic) }}" alt="Profile Image" style="width:75px;">
	@else
	<img src="{{ asset('storage/uploads/images/avatar.png') }}" alt="Profile Image" style="width:75px;">
	@endif
    </td>	
    <td nowrap>{{ $item->parentcategory }}</td>
    <td nowrap>{{ $item->subcategory }}</td>    
	<td nowrap style="text-align:left;">{{ $item->Name }}<br>{{ $item->MobileNumber }}<br>{{ $item->Email }}</td>
	<td nowrap style="text-align:left;">{{ $item->fees }}</td>
	<td style="text-align:left;">{{ $item->description }}</td>
    <td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>

    <td nowrap style="text-align:center;"><a href="{{ route('edit.profile',$item->profileid) }}" title="" class="myactionlink"><i class="fa fa-edit"></i></a></td>
    <td nowrap style="text-align:center;">
	<a title="" class="myactionlink" onclick="deleteItem('profile',{{ $item->profileid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS RECORD?','PROFILE DETAIL DELETED SUCCESSFULLY')" title=""><i class="fa fa-trash"></i>
        </a>
    </td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="10" style="text-align:center;">
        <br>
        <i class="fa fa-warning blue nodata"></i><br>
        SORRY!<br>
        THE SYSTEM DID NOT FIND THE DATA YOU ARE LOOKING FOR.
    </td>
</tr>
@endif
</tbody>
</table>
</div>

