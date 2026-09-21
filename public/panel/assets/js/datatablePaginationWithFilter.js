function deleteItem(rl,recordid,ind,asking,msg) {
    var ind =   ind-1;
	var flag=0;
    bootbox.confirm(asking,function(result){
        if(result)
        {
            $.ajax({
                url: '/delete/'+rl+'/' + encodeURIComponent(recordid),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if(response.fail=='')
                    {
                        $('#item-'+ind).css("display","none");
						$('#itemd-'+ind).css("display","none");

						bootbox.alert({
							message: msg,
							callback: function () {
								setTimeout(function () {
									$(".no-skin").css("padding-right", "");
								},500);
							}
						});
                    }
                    else
                    {
                        bootbox.alert(response.fail);
                    }
                },
                error: function (xhr) {
                    //console.log(xhr);
                }
            });        
        }
    });
}


function deleteItem1(rl,recordid,ind,asking,msg) {
    var ind =   ind-1;
	var flag=0;
    bootbox.confirm(asking,function(result){
        if(result)
        {
            $.ajax({
                url: '/delete/'+rl+'/' + encodeURIComponent(recordid),
                type: 'GET',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function (response) {
                    if(response.fail=='')
                    {
                        $('#items-'+ind).css("display","none");

						bootbox.alert({
							message: msg,
							callback: function () {
								setTimeout(function () {
									$(".no-skin").css("padding-right", "");
								},500);
							}
						});
                    }
                    else
                    {
                        bootbox.alert(response.fail);
                    }
                },
                error: function (xhr) {
                    //console.log(xhr);
                }
            });        
        }
    });
}

/*
document.getElementById('frm').onsubmit = function() {
	var submitBtn = document.getElementById('submitBtn');
	submitBtn.disabled = true;
	submitBtn.innerText = 'Submitting...';
};
*/
jQuery(function($) {


    $('.stname').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.distname').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.distcode').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter1').text(currentLength + '/' + maxLength);
    });

    $('.tiname').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.ctname').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter').text(currentLength + '/' + maxLength);
    });
    $('.catname').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter').text(currentLength + '/' + maxLength);
    });
    $('.headvalue').on('input', function() {
        var currentLength = $(this).val().length;
        var maxLength = $(this).attr('maxlength');
        
        // Update the character count (e.g., 5/50)
        $('#charCounter1').text(currentLength + '/' + maxLength);
    });
	
    $('.sername').on('input', function() {
        var currentLength 	= $(this).val().length;
        var maxLength 		= $(this).attr('maxlength');
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.payouttype').on('input', function() {
        var currentLength 	= $(this).val().length;
        var maxLength 		= $(this).attr('maxlength');
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.obje').on('input', function() {
        var currentLength 	= $(this).val().length;
        var maxLength 		= $(this).attr('maxlength');
        $('#charCounter').text(currentLength + '/' + maxLength);
    });

    $('.enquiry').on('input', function() {
        var currentLength 	= $(this).val().length;
        var maxLength 		= $(this).attr('maxlength');
        $('#charCounter').text(currentLength + '/' + maxLength);
    });


});



function formatIndianNumber(number) {

    number = number.toString();

    let decimal = '';
    if (number.includes('.')) {
        const parts = number.split('.');
        number = parts[0];
        decimal = '.' + parts[1].substring(0, 2); // Keep 2 decimal places
    }

    const lastThree = number.slice(-3);
    let rest = number.slice(0, -3);

    if (rest !== '') {
        rest = rest.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
        return rest + ',' + lastThree + decimal;
    } else {
        return lastThree + decimal;
    }
}


function generateWorkOrderNumber(requestid,r1,htmlid)
{
	var signedby	=	document.getElementById("signedby").value;
	var token 		= 	document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'requestid': requestid,'signedby':signedby },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).val(data.wonumber);
		},
		error: function(error) {
		}
	});

}


function convertToMMDDYYYY(dateStr) {
    if(!dateStr) return '';
    var parts = dateStr.split('-');
    if(parts.length !== 3) return '';
    var day = parts[0];
    var month = parts[1];
    var year = parts[2];
    return month+'/'+day+'/'+year;
}

$('.show-updates').on('click', function () {
    var docUrl = $(this).data('url');
    $(".modal-xl").css("width","60%");
    $(".modal-title").html("Update Detail");
    $('#updatedContent').html('Loading...');
    $('#updatedModal').modal('show');

    $.get(docUrl, function (data) {
        setTimeout(function() { $('#updatedContent').html(data.formhtml); },1000);
    }).fail(function () {
        $('#updatedContent').html('<p class="text-danger">Failed to load EoI.</p>');
    });
});

// Clear content when modal fully hidden
$('#updatedModal').on('hidden.bs.modal', function () {
    $('#updatedContent').html('');
});
