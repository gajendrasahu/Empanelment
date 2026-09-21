$(document).on('click', '.pagination a', function (e) {
    e.preventDefault();
    var url = $(this).attr('href');
    var search  =   document.getElementById("search").value;
    $.ajax({
        url: url,
        type: 'GET',
        data: { search:search },
        success: function(response) {
            $(".tabledata").html(response);
        },
    });
});


function loadData(page,rl) {
    var search  =   document.getElementById("search").value;
    $.ajax({
        url: ''+rl,
        type: 'GET',
        data: { page: page,search:search },
        success: function(response) {
            $(".tabledata").html(response);
        },
    });
}

function deleteItem(rl,recordid,ind,asking,msg) {
    var ind =   ind-1;
    if (confirm(asking)) {
        $.ajax({
            url: '/delete/'+rl+'/' + encodeURIComponent(recordid),
            type: 'GET',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function (response) {

                $('#item-'+ind).css("display","none");
                alert(msg);
            },
            error: function (xhr) {
                alert('An error occurred while deleting the item.');
            }
        });
    }
}
