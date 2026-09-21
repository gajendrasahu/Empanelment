function FetchOrderResource(format_id,orderid,departmentid,categoryid,sectorid,positionid)
{
	let formData = new FormData();
	formData.append('format_id',format_id);
	formData.append('orderid',orderid);
	formData.append('departmentid',departmentid);
	formData.append('categoryid',categoryid);
	formData.append('sectorid',sectorid);
	formData.append('positionid',positionid);
	$.ajax({
		url: resourceReportUrl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status===200)
			{
				$('#resourceModal').modal('show');
				$('.resourceContent').html(response.html);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});
}
function FetchResource(positionid,format_id,departmentid,vendorid,sectorid,orderid,categoryid)
{
	let formData = new FormData();
	formData.append('positionid',positionid);
	formData.append('format_id',format_id);
	formData.append('departmentid',departmentid);
	formData.append('vendorid',vendorid);
	formData.append('sectorid',sectorid);
	formData.append('orderid',orderid);
	formData.append('categoryid',categoryid);
	$.ajax({
		url: resourceReportUrl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#resourceModal').modal('show');
				$('.resourceContent').html(response.html);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});
}

function FetchTotalResource(format_id,departmentid,vendorid,sectorid,orderid,categoryid)
{
	let formData = new FormData();
	formData.append('format_id',format_id);
	formData.append('departmentid',departmentid);
	formData.append('vendorid',vendorid);
	formData.append('sectorid',sectorid);
	formData.append('orderid',orderid);
	formData.append('categoryid',categoryid);
	$.ajax({
		url: resourceReportUrl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#resourceModal').modal('show');
				$('.resourceContent').html(response.html);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});

}

function getVendors(categoryid)
{
    if(categoryid == '')
    {
        $('#vendorid').html('<option value="">Firm Name</option>');
        return;
    }
    $.ajax({
        url: vendorListUrl,
        type: "POST",
        data: {
            categoryid: categoryid,
        },
        success: function(response)
        {
			$('#vendorid').empty();
			$('#vendorid').append('<option value="">Firm Name</option>');

			$.each(response, function(index, item) {
				$('#vendorid').append(
					'<option value="'+item.vendorid+'">'+item.companyname+'</option>'
				);
			});

			$('#vendorid').trigger('change');
        }
    });
}

function FetchAwdResource(format_id,departmentid,vendorid,orderid,categoryid,experiencelevel)
{
	let formData = new FormData();
	formData.append('format_id',format_id);
	formData.append('departmentid',departmentid);
	formData.append('vendorid',vendorid);
	formData.append('orderid',orderid);
	formData.append('categoryid',categoryid);
	formData.append('experiencelevel',experiencelevel);
	$.ajax({
		url: resourceAwdReportUrl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if (response.status === 200)
			{
				$('#resourceModal').modal('show');
				$('.resourceContent').html(response.html);
			}
			else
			{
				bootbox.alert('Something went wrong: ' + response.message);
			}
		},
		error: function(xhr) {
			bootbox.alert('An error occurred while saving the data.');
		}
	});

}
