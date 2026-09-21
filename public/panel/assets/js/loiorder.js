function validateTopFields() {
    let project_title = $('#project_title').val();
	let vendor = $('#vendorid').val();
    let rate = $('#rateid').val();
    let order_date = $('#order_date').val();
	let order_duedate = $('#order_duedate').val();
	let departmentid = $('#departmentid').val();

    if (!vendor || !rate || !order_date || !order_duedate || !departmentid || !project_title) {
        bootbox.alert('Please enter project title, select Firm, Rate List, Department / Manager, Work Order Date and Work Order Due Date first');
        return false;
    }
    return true;
}

function validateSelectedRows(categoryid)
{
    let isValid = true;
	if(categoryid==2)
	{
		$('.dynamic-resource-row').each(function () {

			let row = $(this);

			let sector 			= 	row.find('select[name="sectorids[]"]').val();
			let position 		= 	row.find('select[name="positionids[]"]').val();
			let deploymenttype 	= 	row.find('select[name="deploymenttypes[]"]').val();
			let startDate 		= 	row.find('.startDate').val().trim();
			let endDate 		= 	row.find('.endDate').val().trim();

			if(sector==='' || position === '' || deploymenttype === '' || startDate === '' || endDate === '')
			{

				bootbox.alert(
					'Sector, Position, Deployment Type, Start Date and End Date are mandatory for all rows.'
				);

				isValid = false;

				return false; // break loop
			}
		});
	}
	if(categoryid==1)
	{
		$('.dynamic-resource-row').each(function () {

			let row = $(this);

			let role = row.find('.role').val().trim();
			let experience = row.find('select[name="experiences[]"]').val();
			let resource_name = row.find('select[name="resource_names[]"]').val();
			let exp_level = row.find('select[name="exp_levels[]"]').val();
			let deploymenttype 	= 	row.find('select[name="deploymenttypes[]"]').val();
			let startDate = row.find('.startDate').val().trim();
			let endDate = row.find('.endDate').val().trim();

			if(role==='' || experience === '' || resource_name === '' || exp_level === '' || deploymenttype === '' || startDate === '' || endDate === '')
			{
				bootbox.alert(
					'Role, Experience, Name, Level, Deployment Type Start Date and End Date are mandatory for all rows.'
				);
				isValid = false;
				return false; // break loop
			}
		});		
	}
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