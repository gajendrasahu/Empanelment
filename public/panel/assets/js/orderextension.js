

function validateTopFields() {
    let vendor = $('#vendorid').val();
    let order_date = $('#order_date').val();
	let order_duedate = $('#order_duedate').val();

    if (!vendor || !order_date || !order_duedate) {
        bootbox.alert('Please select Firm, Work Order Date and Work Order Due Date first');
        return false;
    }
    return true;
}

function CheckDueDate(element) {

    let selectedDate 	=	element.value;
    let orderDueDate	=	$('#order_duedate').val();
    let orderDate 		=	$('#order_date').val();
	
    if(!selectedDate || !orderDueDate || !orderDate)
	{
		element.value = '';
        bootbox.alert("Please select order date and order due date.");
		return false;
    }

    function formatToNumber(dateStr)
	{
        let parts = dateStr.split('-');
        return parseInt(parts[2] + parts[1] + parts[0]); // yyyymmdd
    }

    let selected = formatToNumber(selectedDate);
    let dueDate = formatToNumber(orderDueDate);
    let order = formatToNumber(orderDate);

    if (selected > dueDate) {
        bootbox.alert('End Date cannot be greater than Order Due Date (' + orderDueDate + ')');
        element.value = '';
        return;
    }

    if (selected < order) {
        bootbox.alert('End Date cannot be before Order Date (' + orderDate + ')');
        element.value = '';
        return;
    }
}

function CheckStartDate(element) {

    let selectedDate = element.value;
    let orderDueDate = $('#order_duedate').val();
    let orderDate = $('#order_date').val();

    if (!selectedDate || !orderDueDate || !orderDate) {
        element.value = '';
        bootbox.alert("Please select order date and order due date.");
        return false;
    }

    function formatToNumber(dateStr) {
        let parts = dateStr.split('-');
        return parseInt(parts[2] + parts[1] + parts[0]); // yyyymmdd
    }

    let selected = formatToNumber(selectedDate);
    let dueDate = formatToNumber(orderDueDate);
    let order = formatToNumber(orderDate);

    if (selected > dueDate) {
        bootbox.alert('Start Date cannot be greater than Order Due Date (' + orderDueDate + ')');
        element.value = '';
        return;
    }

    if (selected < order) {
        bootbox.alert('Start Date cannot be before Order Date (' + orderDate + ')');
        element.value = '';
        return;
    }
}


$(document).on('change', '.mergedeployment', function () {

    let row = $(this).closest('tr');
	let order_date		=	$("#order_date").val();
	let order_duedate	=	$("#order_duedate").val();
    let startDate = row.find('.merge_startDate');
    let endDate = row.find('.merge_endDate');

    let deploymentType = row.find('.merge_deploymenttype');
    let experienceLevel = row.find('.merge_experiencelevel');

    if ($(this).is(':checked')) {
		
		startDate.val(order_date);
		endDate.val(order_duedate);
        startDate.prop('disabled', false).prop('readonly', false);
        endDate.prop('disabled', false).prop('readonly', false);

        deploymentType.prop('disabled', false);

        experienceLevel.prop('disabled', false);

    } else {

        startDate.val('').prop('readonly', true).prop('disabled', true);

        endDate.val('').prop('readonly', true).prop('disabled', true);

        deploymentType.prop('disabled', true);

        experienceLevel.prop('disabled', true);
    }
});