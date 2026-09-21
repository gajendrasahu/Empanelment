<div class="pull-right tableTools-container" style="margin-top:-35px;"></div>

<div>
<table id="dynamic-table" class="table table-striped table-bordered">
<thead>
<tr>
    <th class="myrectd" nowrap><b>S.NO.</b></th>
    <th nowrap>POSTED BY</th>
    <th nowrap style="text-align:center;">DATE</th>
    <th nowrap>HEADING</th>
    <th nowrap>#TAG</th>
    <th nowrap>CONTENT</th>
    <th nowrap style="width:100px; text-align:center;">FILE</th>
    <th nowrap style="text-align:center;">LIKES</th>
    <th nowrap style="text-align:center;">DISLIKES</th>
    <th nowrap style="text-align:center;"></th>
</tr>
</thead>
<tbody>

@php
$i=1
@endphp
@foreach ($data as $item)
<tr id="item-{{ $i }}">
    <td style="width:30px;" class="myrectd" nowrap>{{ $i++ }}</td>
    <td nowrap>{{ $item->Name }}<br>{{ $item->MobileNumber }}</td>
    <td nowrap style="text-align:center;">{{ date('d\-m\-Y, h:i A',strtotime($item->creationdate)) }}</td>
    <td nowrap>{{ $item->heading }}</td>
    <td nowrap>{{ $item->hashtag }}</td>
    <td nowrap>{{ $item->content }}</td>
    <td nowrap style="width:100px; text-align:center; padding:0px;">
        @if($item->filename!='')
            @if (Str::endsWith($item->filename, ['.jpg', '.jpeg', '.png', '.gif', '.bmp']))
            <img src="{{ asset('storage/' . $item->filename) }}" alt="Image" style="width:100px;">
            @elseif (Str::endsWith($item->filename, ['.mp4', '.mov', '.avi', '.mkv']))
            <video controls width="100">
                <source src="{{ asset('storage/' . $item->filename) }}" type="video/mp4">
            </video>
            @endif
        @endif
    </td>
    <td nowrap style="text-align:center;"><label class="badge badge-info"> {{ $item->likes }}</label></td>
    <td nowrap style="text-align:center;"><label class="badge badge-info"> {{ $item->dislikes }}</label></td>
    <td nowrap style="text-align:center;"><i class="fa fa-trash" onclick="deleteItem('post',{{ $item->postid}},{{ $i }},'ARE YOU SURE YOU WANT TO DELETE THIS POST?','POST DELETED SUCCESSFULLY')"></i></td>
</tr>
@endforeach
@if($i==1)
<tr>
    <td colspan="5" style="text-align:center;">
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

<script>
function Mark(id)
{
    bootbox.confirm('DO YOU WANT TO VERIFY THIS USER RECORD!',function(result){
        if(result)
        {
            $.ajax({
                url: "{{ route('mark.save') }}", // Replace with your route
                type: "POST",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},                
                data: {id:id},
                success: function(response) {
                    if(response.result==='success')
                    {
                        setTimeout(function(){
                            loadData(1,'{{ route('feedback.list') }}')
                        },2000);                        
                        alert('FEEDBACK / SUGESSION MARKED AS VIEWED SUCCESSFULLY!');                        
                    }
                },
                error: function(xhr, status, error) {
                    alert(xhr.responseText);
                }
            });         
        }
    })
}
</script>