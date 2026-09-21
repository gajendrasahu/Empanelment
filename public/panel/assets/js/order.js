$(document).ready(function () {

    // disable all fields initially
    $('.startDate, .endDate')
        .prop('disabled', true)
        .prop('readonly', true);

});

function validateTopFields() {
    let vendor = $('#vendorid').val();
    let rate = $('#rateid').val();
    let order_date = $('#order_date').val();
	let order_duedate = $('#order_duedate').val();

    if (!vendor || !rate || !order_date || !order_duedate) {
        bootbox.alert('Please select Firm, Rate List, Work Order Date and Work Order Due Date first');
        return false;
    }
    return true;
}

$('.chkAll').on('change', function () {

    if (!validateTopFields()) {
        $(this).prop('checked', false);
        return;
    }

    let isChecked = $(this).prop('checked');
    let order_date = $('#order_date').val();
	let order_duedate = $('#order_duedate').val();

    $('input[name="recordids[]"]').each(function () {

        let row = $(this).closest('tr');

        let startField = row.find('.startDate');
        let endField = row.find('.endDate');
		let resource_name = row.find('.resource_name');
		let role = row.find('.role');
		let exp_level = row.find('.exp_level');
		let deployment = row.find('.deploymenttype');

        $(this).prop('checked', isChecked);

        if (isChecked)
		{
            startField.prop('readonly', false).prop('disabled', false);
            endField.prop('readonly', false).prop('disabled', false);
			resource_name.prop('readonly', false).prop('disabled', false);
			role.prop('readonly', false).prop('disabled', false);
			exp_level.prop('readonly', false).prop('disabled', false);
			deployment.prop('readonly', false).prop('disabled', false);

            // optional: auto-fill start date
            startField.val(order_date);
			endField.val(order_duedate);

        }
		else
		{
            startField.val('').prop('readonly', true).prop('disabled', true).prop('placeholder', 'dd-mm-YYYY');

            endField.val('').prop('readonly', true).prop('disabled', true).prop('placeholder', 'dd-mm-YYYY');
				
			resource_name.val('').prop('readonly', true).prop('disabled', true)
                .prop('placeholder', 'Name');
				
			role.prop('readonly', true).prop('disabled', true)
                .prop('placeholder', 'Role');

			deployment.prop('readonly', true).prop('disabled', true);

        }

    });
});

$(document).on('change', 'input[name="recordids[]"]', function () {

    if ($(this).is(':checked') && !validateTopFields()) {
        $(this).prop('checked', false);
        $('.chkAll').prop('checked', false);
        return;
    }

    let row = $(this).closest('tr');

    let startField 		= 	row.find('.startDate');
    let endField 		= 	row.find('.endDate');
	let resource_name	=	row.find('.resource_name');
	let role			=	row.find('.role');
	let exp_level		=	row.find('.exp_level');
	let experience		=	row.find('.experience');
	let deployment		=	row.find('.deploymenttype');

    if ($(this).is(':checked')) {

        let order_date = $('#order_date').val();
		let order_duedate = $('#order_duedate').val();

        startField.prop('readonly', false).prop('disabled', false);
        endField.prop('readonly', false).prop('disabled', false);
		resource_name.prop('readonly', false).prop('disabled', false);
		role.prop('readonly', false).prop('disabled', false);
		exp_level.prop('readonly', false).prop('disabled', false);
		experience.prop('readonly', false).prop('disabled', false);
		deployment.prop('readonly', false).prop('disabled', false);

        // optional auto-fill
        startField.val(order_date);
		endField.val(order_duedate);

    } else {

        startField.val('').prop('readonly', true).prop('disabled', true);
        endField.val('').prop('readonly', true).prop('disabled', true);
		resource_name.val('').prop('readonly', true).prop('disabled', true);
		role.val('').prop('readonly', true).prop('disabled', true);


		let sector = row.find('select[name="sectorids[]"]');
		let position = row.find('select[name="positionids[]"]');
		let experience = row.find('select[name="experience[]"]');
		let exp_level = row.find('select[name="exp_level[]"]');
		let deployment = row.find('select[name="deploymenttypes[]"]');

		if (sector.length) {
			sector.val('');
		}

		if (position.length) {
			position.val('');
		}				
		if (experience.length) {
			experience.val('');
		}
		experience.val('').prop('readonly', true).prop('disabled', true);				
		exp_level.prop('readonly', true).prop('disabled', true);		
		deployment.prop('readonly', true).prop('disabled', true);		
    }
    let total = $('input[name="recordids[]"]').length;
    let checked = $('input[name="recordids[]"]:checked').length;

    $('.chkAll').prop('checked', total === checked);	
});



function validateSelectedRows() {

    let isValid = true;

    $('input[name="recordids[]"]:checked').each(function () {

        let row = $(this).closest('tr');

        let startDate = row.find('.startDate').val().trim();
        let endDate = row.find('.endDate').val().trim();

        if (startDate === '' || endDate === '') {

            bootbox.alert('Start Date and End Date are mandatory for selected resources.');

            isValid = false;

            return false; // break loop
        }
    });

    return isValid;
}

function CheckDueDate(element) {

    let selectedDate = element.value;
    let orderDueDate = $('#order_duedate').val();

    if (!selectedDate || !orderDueDate) {
        return;
    }

    function formatToNumber(dateStr) {

        let parts = dateStr.split('-');

        // yyyymmdd
        return parseInt(parts[2] + parts[1] + parts[0]);
    }

    let selected = formatToNumber(selectedDate);
    let dueDate = formatToNumber(orderDueDate);

    if (selected > dueDate) {

        bootbox.alert('End Date cannot be greater than Order Due Date (' + orderDueDate + ')');

        element.value = '';
    }
}