
function GetVariationValues(variantid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'variantid': variantid },
		dataType: 'json',
		success: function(data)
		{
			$('#valueid').empty();
			$("#valueid").append("<option value=''>--VARIANT VALUE--</option>");
			$.each(data, function(index, option) {
			$("#valueid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		},
		error: function(error) {
		}
	});
}

function GetState(r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#stateid").empty().trigger("chosen:updated");
	$("#stateid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: {},
		dataType: 'json',
		success: function(data)
		{
			$("#stateid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#stateid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#stateid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}
function GetInclude(r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$('#includeid').empty();
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: {},
		dataType: 'json',
		success: function(data)
		{
			
			$("#includeid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#includeid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		},
		error: function(error) {
		}
	});
}


function GetCities(stateid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#cityid").empty().trigger("chosen:updated");
	$("#cityid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'stateid': stateid },
		dataType: 'json',
		success: function(data)
		{
			$("#cityid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#cityid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#cityid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}

function GetCitiesWithId(stateid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'stateid': stateid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}

function GetBranchVendors(branchid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'branchid': branchid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}


function GetObjectivesWithId(payouttypeid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'payouttypeid': payouttypeid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}


function GetFirmCities(stateid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#firmcityid").empty().trigger("chosen:updated");
	$("#firmcityid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'stateid': stateid },
		dataType: 'json',
		success: function(data)
		{
			$("#firmcityid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#firmcityid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#firmcityid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}



function GetArea(cityid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#areaid").empty().trigger("chosen:updated");
	$("#areaid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'cityid': cityid },
		dataType: 'json',
		success: function(data)
		{
			$("#areaid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#areaid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#areaid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}
function GetAreaName(cityid,r1)
{
	$.get(""+r1,
	{
		cityid:cityid
	},
	function(data, status){
		$(".areanames").html(data);
		
	});	
}
function GetAreaNames(cityid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#areaid").empty().trigger("chosen:updated");
	$("#areaid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'cityid': cityid },
		dataType: 'json',
		success: function(data)
		{
			$("#areaid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#areaid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#areaid").prop("disabled", false).trigger("chosen:updated");
		
		},
		error: function(error) {
		}
	});
	
	if(cityid!='')
	{
		$("#selectAll").prop("disabled","");
	}
	else
	{
		$("#selectAll").prop("checked",false);
		$("#selectAll").prop("disabled","disabled");
	}
	
}

function GetAreaNamesWithId(cityid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'cityid': cityid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		
		},
		error: function(error) {
		}
	});	
}

function GetDepartments(branchids,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#departmentid").empty().trigger("chosen:updated");
	$("#departmentid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'branchids': branchids },
		dataType: 'json',
		success: function(data)
		{
			$("#departmentid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#departmentid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#departmentid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}


function GetSubHead(headid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#subheadid").empty().trigger("chosen:updated");
	$("#subheadid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'headid': headid },
		dataType: 'json',
		success: function(data)
		{
			$("#subheadid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#subheadid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#subheadid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}

function AddState(r1)
{
	bootbox.prompt("PLEASE ENTER STATE NAME!", function(result)
	{ 
		if(result=="" || result==null)
		{
			result	=	"";
		}
		else
		{
			$.ajax({
				url: ''+r1,
				type: 'GET',
				data: {
					'statename': result
				},
				success: function(response) {
					if(response.error)
					{
						bootbox.alert("DUPLICATE STATE NAME");
						var statelist = '/master/list/state';
						GetState(''+statelist);
						
					}
					if(response.success)
					{
						bootbox.alert("STATE NAME ADDED SUCCESSFULLY");
						var statelist = '/master/list/state';
						GetState(''+statelist);
						setTimeout(function(){
							
						var selectBox = document.getElementById("stateid");
						var optionValue = response.lastid;
						for (var i = 0; i < selectBox.options.length; i++)
						{
							if(selectBox.options[i].value==optionValue)
							{
								selectBox.selectedIndex = i;
								break;
							}
						}
						$("#stateid").trigger("chosen:updated");
						},2000);
						setTimeout(function () {
							$(".no-skin").css("padding-right", "");
						},5000);
					}
				},
				error: function(xhr) {
					alert(xhr.message);
				}
			});			
		}
	});		
}

function AddCity(r1)
{
	var stateid	=	document.getElementById("stateid").value;
	if(stateid=='')
	{
		bootbox.alert("TO ADD NEW CITY. PLEASE SELECT STATE NAME FIRST THEN CLICK ON ADD CITY ICON!");	
	}
	else
	{
		bootbox.prompt("PLEASE ENTER CITY NAME!", function(result)
		{ 
			if(result=="" || result==null)
			{
				result	=	"";
			}
			else
			{
				$.ajax({
					url: ''+r1,
					type: 'GET',
					data: {
						'stateid':stateid,
						'cityname':result
					},
					success: function(response) {
						if(response.error)
						{
							bootbox.alert("DUPLICATE CITY NAME");
							var citylist = '/master/list/city';
							GetCities(stateid,citylist);
							
						}
						if(response.success)
						{
							bootbox.alert("CITY NAME ADDED SUCCESSFULLY");
							var citylist = '/master/list/city';
							GetCities(stateid,citylist);
							setTimeout(function(){
								
							var selectBox = document.getElementById("cityid");
							var optionValue = response.lastid;
							for (var i = 0; i < selectBox.options.length; i++)
							{
								if(selectBox.options[i].value==optionValue)
								{
									selectBox.selectedIndex = i;
									break;
								}
							}
							$("#cityid").trigger("chosen:updated");
							},2000);
							setTimeout(function () {
								$(".no-skin").css("padding-right", "");
							},5000);
						}
					},
					error: function(xhr) {
						alert(xhr.message);
					}
				});			
			}
		});		
	}
}

function AddArea(r1)
{
	var cityid	=	document.getElementById("cityid").value;
	if(cityid=='')
	{
		bootbox.alert("TO ADD NEW AREA. PLEASE SELECT CITY NAME FIRST THEN CLICK ON ADD AREA ICON!");	
	}
	else
	{
		bootbox.prompt("PLEASE ENTER AREA NAME!", function(result)
		{ 
			if(result=="" || result==null)
			{
				result	=	"";
			}
			else
			{
				$.ajax({
					url: ''+r1,
					type: 'GET',
					data: {
						'cityid':cityid,
						'areaname':result
					},
					success: function(response) {
						if(response.error)
						{
							bootbox.alert("DUPLICATE AREA NAME");
							var arealist = '/master/list/area';
							GetArea(cityid,arealist);
							
						}
						if(response.success)
						{
							bootbox.alert("AREA NAME ADDED SUCCESSFULLY");
							var arealist = '/master/list/area';
							GetArea(cityid,arealist);
							setTimeout(function(){
								
							var selectBox = document.getElementById("areaid");
							var optionValue = response.lastid;
							for (var i = 0; i < selectBox.options.length; i++)
							{
								if(selectBox.options[i].value==optionValue)
								{
									selectBox.selectedIndex = i;
									break;
								}
							}
							$("#areaid").trigger("chosen:updated");
							},2000);

							setTimeout(function () {
								$(".no-skin").css("padding-right", "");
							},5000);
							
						}
					},
					error: function(xhr) {
						alert(xhr.message);
					}
				});			
			}
		});		
	}
}
function AddInclude(r1)
{
	bootbox.prompt("PLEASE ENTER SERVICE INCLUDE VALUE!", function(result)
	{ 
		if(result=="" || result==null)
		{
			result	=	"";
		}
		else
		{
			$.ajax({
				url: ''+r1,
				type: 'GET',
				data: {
					'includevalue': result
				},
				success: function(response) {
					if(response.error)
					{
						bootbox.alert("DUPLICATE VALUE");
						var includelist = '/master/list/includes';
						GetInclude(''+includelist);
						
					}
					if(response.success)
					{
						bootbox.alert("INCLUDE VALUE ADDED SUCCESSFULLY");
						var includelist = '/master/list/includes';
						GetInclude(''+includelist);
						setTimeout(function(){
						$('#includeid').multiselect('refresh');
						var optionValue = response.lastid;
						$("#includeid").val([optionValue]);
						},3000);
						setTimeout(function () {
							$(".no-skin").css("padding-right", "");
						},5000);
					}
				},
				error: function(xhr) {
					alert(xhr.message);
				}
			});			
		}
	});		
}

function GetProduct(categoryid,r1)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#productid").empty().trigger("chosen:updated");
	$("#productid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#productid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#productid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#productid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}
function ApproveLeave(r1,recordid)
{
	if($('#chk'+recordid).is(':checked'))
	{
		bootbox.prompt("PLEASE ENTER APPROVAL REMARK!", function(result)
		{ 
			if(result=="" || result==null)
			{
				result	=	"";
			}
			else
			{
				$.ajax({
					url: ''+r1,
					type: 'GET',
					data: {
						'approvalremark':result,
						'recordid':recordid
					},
					success: function(response) {
						if(response.error)
						{						
						}
						if(response.success)
						{
							//bootbox.alert("LEAVE REQUEST APROVED SUCCESSFULLY!");
							bootbox.alert({
								message: 'LEAVE REQUEST APROVED SUCCESSFULLY!',
								callback: function () {
									ClickLoad();
									setTimeout(function () {
										$(".no-skin").css("padding-right", "");										
									},500);
								}
							});
							
						}
					},
					error: function(xhr) {
						alert(xhr.message);
					}
				});			
			}
		});		
	}
}

function GetEmployees(vendorid,r1)
{

	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#employeeid").empty().trigger("chosen:updated");
	$("#employeeid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'vendorid': vendorid },
		dataType: 'json',
		success: function(data)
		{
			$("#employeeid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#employeeid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#employeeid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
			alert("A");
		}
	});
}

function GetEmployeesWithCategory(vendorid,r1,categoryid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#employeeid").empty().trigger("chosen:updated");
	$("#employeeid").prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'vendorid': vendorid,'categoryid':categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#employeeid").append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#employeeid").append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#employeeid").prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
			alert("A");
		}
	});
}

function CheckForm()
{
	var flag	=	0;
	var projectimage=	$("#projectimage").val();
	var projectname	=	$("#projectname").val();
	var stateid		=	$("#stateid").val();
	var cityid		=	$("#cityid").val();
	var areaid		=	$("#areaid").val();
	var launchdate	=	$("#launchdate").val();
	
	
	if(projectimage=='')
	{
		flag++;
		bootbox.alert("PLEASE SELECT PROJECT IMAGE");
		return false;
	}
	if(projectname=='')
	{
		flag++;
		bootbox.alert("PLEASE ENTER PROJECT NAME");
		return false;
	}
}

function validateURL(url)
{
    const regex = /^(https?):\/\/[a-zA-Z0-9-]+\.[a-zA-Z]{2,6}([\/a-zA-Z0-9-]*)*$/;
    return regex.test(url);
}

function GetSubCategoryWithId(categoryid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		
		},
		error: function(error) {
		}
	});	
}

function GetTiersWithId(categoryid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			//$("#"+htmlid).append("<option value=''>--Tier--</option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
	$("#remunerationid").empty();
	$("#sectorid").val("");
	$("#remunerationid").append("<option value=''>Position/Experience Level</option>");
}

function GetTiersWithIdWithBlankOption(categoryid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''>--Tier--</option>");
			$.each(data, function(index, option) {
			$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
		});			
		$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
		},
		error: function(error) {
		}
	});
}


function GetTiersWithRole(categoryid,r1,htmlid,roleid)
{
	var inc	=	0;
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$("#"+htmlid).empty().trigger("chosen:updated");
	$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'categoryid': categoryid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).append("<option value=''></option>");
			$.each(data.tierlist, function(index, option) {
				$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
			});			
			$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");

			

			$.each(data.rolelist, function(index, option) {
				$("#"+roleid).append("<option value='" + option.value + "'>" + option.label + "</option>");
				inc=inc+1;
			});
			if(inc==0)
			{
			$("#"+roleid).empty();
			$("#"+roleid).append("<option value=''>Job Role</option>");
			}
		},
		error: function(error) {
		}
	});
}

function GetExperience(tierid,r1,htmlid,categoryid)
{
	if(categoryid==1)
	{
		var experienceid	=	document.getElementById("experienceid").value;
		var tierid			=	document.getElementById("tierid").value;
		var token 			= 	document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		$("#"+htmlid).empty().trigger("chosen:updated");
		$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
		$.ajax({
			url: ''+r1,
			type: 'GET',
			data: { 'tierid': tierid,'categoryid': categoryid,'experienceid':experienceid },
			dataType: 'json',
			success: function(data)
			{
				$("#"+htmlid).append("<option value=''>--Experience Level--</option>");
				$.each(data, function(index, option) {
				$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
			});			
			$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
			$("#basebudget").val("");
			$("#baseadmincharge").val("");
			$("#admincharge").val("");
			$("#total").val("");
			$("#budget").val("");
			PopulateDuration();

			
			},
			error: function(error) {
			}
		});
	}
	if(categoryid==2)
	{
		var tierid		=	document.getElementById("tierid").value;
		var sectorid	=	document.getElementById("sectorid").value;
		if(sectorid!='')
		{
			var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
			$("#"+htmlid).empty().trigger("chosen:updated");
			$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
			$.ajax({
				url: ''+r1,
				type: 'GET',
				data: { 'tierid': tierid,'categoryid': categoryid,'sectorid':sectorid },
				dataType: 'json',
				success: function(data)
				{
					$("#"+htmlid).append("<option value=''>--Position--</option>");
					$.each(data, function(index, option) {
					$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
				});			
				$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
				$("#basebudget").val("");
				$("#baseadmincharge").val("");
				$("#admincharge").val("");
				$("#total").val("");
				$("#budget").val("");
				PopulateDuration();
				},
				error: function(error) {
				}
			});
		}
	}
}

function GetManagerExperience(sectorid,r1,htmlid,categoryid)
{
	if(categoryid==2)
	{
		var sectorid	=	document.getElementById("sectorid").value;
		if(sectorid!='')
		{
			var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
			$("#"+htmlid).empty().trigger("chosen:updated");
			$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
			$.ajax({
				url: ''+r1,
				type: 'GET',
				data: {'categoryid': categoryid,'sectorid':sectorid },
				dataType: 'json',
				success: function(data)
				{
					$("#"+htmlid).append("<option value=''>--Position--</option>");
					$.each(data, function(index, option) {
					$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
				});			
				$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
				$("#basebudget").val("");
				$("#baseadmincharge").val("");
				$("#admincharge").val("");
				$("#total").val("");
				$("#budget").val("");
				PopulateDuration();
				},
				error: function(error) {
				}
			});
		}
	}
	
	if(categoryid==1)
	{
		var experienceid=	document.getElementById("experienceid").value;
		var token 		= document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		$("#"+htmlid).empty().trigger("chosen:updated");
		$("#"+htmlid).prop("disabled", true).trigger("chosen:updated");
		$.ajax({
			url: ''+r1,
			type: 'GET',
			data: {'categoryid': categoryid,'experienceid':experienceid },
			dataType: 'json',
			success: function(data)
			{
				$("#"+htmlid).append("<option value=''>--Experience Level--</option>");
				$.each(data, function(index, option) {
				$("#"+htmlid).append("<option value='" + option.value + "'>" + option.label + "</option>");
			});			
			$("#"+htmlid).prop("disabled", false).trigger("chosen:updated");
			$("#basebudget").val("");
			$("#baseadmincharge").val("");
			$("#admincharge").val("");
			$("#total").val("");
			$("#budget").val("");
			PopulateDuration();

			
			},
			error: function(error) {
			}
		});
	}
}

function PopulateDuration()
{
	
	var projectduration = 	document.getElementById("projectduration").value;
	var employmenttype	=	document.getElementById("employmenttype").value;
	var $duration 		= 	$('#duration');
	$duration.empty();
	if (!isNaN(projectduration))
	{
		if(employmenttype=='FULL TIME')
		{
			for (var i = projectduration; i <= projectduration; i++)
			{
				$duration.append('<option value="' + i + '">' + i + ' Months</option>');
			}
		}
		if(employmenttype=='PART TIME')
		{
			for (var i = 3; i <=projectduration-1; i++)
			{
				$duration.append('<option value="' + i + '">' + i + ' Months</option>');
			}
		}
	}				
	CalculateBudget();
}

function PopulateDurationUpdate()
{
    var projectduration = Number(document.getElementById("projectduration").value);
    var employmenttype  = document.getElementById("employment_type").value;
	
    var $duration       = $('#resource_duration');

    $duration.empty();

    if (isNaN(projectduration)) {
        return;
    }
	
    if (employmenttype === 'FULL TIME') {
        // Only one option
        $duration.append(
            '<option value="' + projectduration + '">' +
            projectduration + ' Months</option>'
        );
    }

    if (employmenttype === 'PART TIME') {
        for (var i = 3; i < projectduration; i++) {
            $duration.append(
                '<option value="' + i + '">' +
                i + ' Months</option>'
            );
        }
    }
}
function GetRemuneration(remunerationid,r1,htmlid)
{
	var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'remunerationid': remunerationid },
		dataType: 'json',
		success: function(data)
		{
			var projectduration	=	$("#projectduration").val();
			var duration		=	$("#duration").val();
			if(Number(duration)>Number(projectduration))
			{
				$("#duration").val(1);
			}
			else
			{
				$("#employmenttype").val('FULL TIME');
				$("#duration").val(projectduration);
			}
			$("#budget").val(data.budget);
			$("#basebudget").val(data.budget);
			$("#experience").val(data.experience);
			$('#experience').attr('title',data.experience);
			CalculateBudget();
		},
		error: function(error) {
		}
	});

}

function GetPmRemuneration(positionid,r1,htmlid)
{
	var categoryid	=	document.getElementById("categoryid").value;

	if(categoryid==2)
	{
		var sectorid	=	document.getElementById("sectorid").value;
		var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
		$.ajax({
			url: ''+r1,
			type: 'GET',
			data: { 'positionid': positionid,'sectorid':sectorid,'categoryid':categoryid },
			dataType: 'json',
			success: function(data)
			{
				var projectduration	=	$("#projectduration").val();
				var duration		=	$("#duration").val();
				if(Number(duration)>Number(projectduration))
				{
					$("#duration").val(1);
				}
				else
				{
					$("#employmenttype").val('FULL TIME');
					$("#duration").val(projectduration);
				}
				$("#budget").val(data.budget);
				$("#basebudget").val(data.budget);
				$("#experience").val(data.experience);
				$('#experience').attr('title',data.experience);
				CalculateBudget();
			},
			error: function(error) {
			}
		});
	}
}

function GetConsultantRemuneration(positionid,r1,htmlid)
{
	var categoryid	=	document.getElementById("categoryid").value;
	var tierid		=	document.getElementById("tierid").value;
	var sectorid	=	document.getElementById("sectorid").value;
	var token 		= 	document.querySelector('meta[name="csrf-token"]').getAttribute('content');
	$.ajax({
		url: ''+r1,
		type: 'GET',
		data: { 'positionid': positionid,'categoryid':categoryid,'tierid':tierid,'sectorid':sectorid },
		dataType: 'json',
		success: function(data)
		{
			$("#"+htmlid).val(data);
		},
		error: function(error) {
		}
	});

}

function GetPmUpdateForm(rl)
{
	var tierid		=	document.getElementById("tierid").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var requestid	=	document.getElementById("requestid").value;
	var form 		= 	$('#frm')[0];
	var formData 	= 	new FormData(form);
	formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
	formData.append('tierid',tierid);
	formData.append('categoryid',categoryid);
	formData.append('requestid',requestid);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
	   headers: {
			'Accept': 'application/json'
		},		
		success: function(response) {
			$(".displayform").html(response.formhtml);
		},
		error: function(xhr){
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}


function GetUpdateForm(rl)
{
	var tierid		=	document.getElementById("tierid").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var requestid	=	document.getElementById("requestid").value;
	var form 		= 	$('#frm')[0];
	var formData 	= 	new FormData(form);
	formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
	formData.append('tierid',tierid);
	formData.append('categoryid',categoryid);
	formData.append('requestid',requestid);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
	   headers: {
			'Accept': 'application/json'
		},		
		success: function(response) {
			$(".displayform").html(response.formhtml);
		},
		error: function(xhr){
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}

function GetForm(rl)
{
	var tierid		=	document.getElementById("tierid").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var form = $('#frm')[0];
	var formData = new FormData(form);
	formData.append('_token',$('meta[name="csrf-token"]').attr('content'));
	formData.append('tierid',tierid);
	formData.append('categoryid',categoryid);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
	   headers: {
			'Accept': 'application/json'
		},		
		success: function(response) {
			$(".displayform").html(response.formhtml);
		},
		error: function(xhr){
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}


function GetManagerForm(rl)
{
	var categoryid	=	document.getElementById("categoryid").value;
	var form = $('#frm')[0];
	var formData = new FormData(form);
	formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
	formData.append('categoryid',categoryid);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			$(".displayform").html(response.formhtml);
		},
		error: function(xhr){
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}

function GetOrderForm(rl)
{
	var tierid		=	document.getElementById("tierid").value;
	var categoryid	=	document.getElementById("categoryid").value;
	var projectduration	=	document.getElementById("projectduration").value;
	var form = $('#frm')[0];
	var formData = new FormData(form);
	formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
	formData.append('tierid',tierid);
	formData.append('categoryid',categoryid);
	formData.append('projectduration',projectduration);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
	   headers: {
			'Accept': 'application/json'
		},		
		success: function(response) {
			$(".displayform").html(response.formhtml);
		},
		error: function(xhr){
			document.getElementById("tierid").value="";
			document.getElementById("categoryid").value="";
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}

function MarkAsRead(rl,recordid)
{
	var form = $('#frm')[0];
	var formData = new FormData(form);
	formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
	formData.append('recordid',recordid);
	$.ajax({
		url: ''+rl,
		method: 'POST',
		data: formData,
		processData: false,
		contentType: false,
		success: function(response) {
			if(response.status==200)
			{
				bootbox.alert(response.message);
				setTimeout(function(){ location.reload(); },2000);
			}

			
		},
		error: function(xhr) {
			let errors = xhr.responseJSON?.errors;
			let message = '';
			$.each(errors, function(key, val) {
				message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
			});
			bootbox.alert(message);
			
			setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
		}
	});	
}

function RemoveResume(rl,resumeid)
{
	bootbox.confirm('Do you confirm this action?',function(result){
		if(result)
		{
			var form	=	$('#participate')[0];
			var formData= 	new FormData(form);
			
			formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
			formData.append('resumeid',resumeid);
			$.ajax({
				url: ''+rl,
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					if(response.status==200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ location.reload(); },2000);
					}
					else
					{
						bootbox.alert(response.message);
					}
				},
				error: function(xhr) {
					let errors = xhr.responseJSON?.errors;
					let message = '';
					$.each(errors, function(key, val) {
						message += '<i class="fa fa-hand-o-right"></i> '+ val + '<br>';
					});
					bootbox.alert(message);
					
					setTimeout(function() { $(".no-skin").css("padding-right", ""); }, 2000);
				}
			});
		}
	});
}


