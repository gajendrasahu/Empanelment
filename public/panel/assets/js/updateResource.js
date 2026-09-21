$(document).on('click', '.updateResource', function () {

    let btn = $(this);
    let row = btn.closest('tr');
    let table = btn.closest('table');

    btn.hide();

    let formData = {
        _token:$('meta[name="csrf-token"]').attr('content'),

        orderid: table.find('input[name="order_id"]').val(),
        vendorid: table.find('input[name="vendor_id"]').val(),

        deploymentid: row.find('input[name="deploymentid"]').val(),
        employee_code: row.find('input[name="employee_code"]').val(),
        name: row.find('input[name="name"]').val(),
        mobilenumber: row.find('input[name="mobilenumber"]').val(),
        email: row.find('input[name="email"]').val(),
        deployed_date: row.find('input[name="deployed_date"]').val(),

        levels: row.find('select[name="levels"]').val(),
        sectorid: row.find('input[name="sector_id"]').val(),
        positionid: row.find('input[name="position_id"]').val(),
    };

    $.ajax({
        url: resourceUpdateUrl,
        type: "POST",
        data: formData,
        success: function (response) {

            btn.show();

            if (response.status == 200) {
                bootbox.alert(response.message);
            } else {
                bootbox.alert(response.message);
            }
        },

        error: function (xhr) {

            btn.show();

            console.log(xhr);

            if(xhr.responseJSON && xhr.responseJSON.errors)
            {
                var errors = xhr.responseJSON.errors;
                var allMessages = '';

                $.each(errors, function(field, messages) {
                    $.each(messages, function(index, msg) {
                        allMessages += msg + '<br>';
                    });
                });

                bootbox.alert(allMessages);
            }
            else
            {
                bootbox.alert('Something went wrong');
            }
        }
    });

});