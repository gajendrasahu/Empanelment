	FamilyDetail('','','','');
	NoteDetail('');
	function ChangeValueStatus()
	{
		if($("#isundermonitoring").is(":checked"))
		{
			$("#monitoringvalue").prop("disabled","");
		}
		else
		{
			$("#monitoringvalue").prop("disabled","disabled");	
		}
	}

	function FamilyDetail(membername,gender,relation,dob) {
	    $.ajax({
	        url: '{{ route('doctorfamily.html')}}',
	        type: 'GET',	        
	        data: {'_token': '{{ csrf_token() }}',membername:membername,gender:gender,relation:relation,dob:dob},
	        success: function(response) {
	            $(".familydetail").html(response);
	            $("#membername0").focus();
	        },
	    });
	}
	function NoteDetail(docnote) {
	    $.ajax({
	        url: '{{ route('doctornote.html')}}',
	        type: 'GET',
	        data: {'_token': '{{ csrf_token() }}',docnote:docnote},
	        success: function(response) {
	            $(".notedetail").html(response);
	            $("#docnote0").focus();
	        },
	    });
	}

	function AddRecord()
	{
		var memname 	=	document.getElementById('membername0').value;
		var relation	=	document.getElementById('relation0').value;
		var gender		=	document.getElementById('gender0').value;
		var dob			=	document.getElementById('dob0').value;
		FamilyDetail(memname,gender,relation,dob);
	}
	function AddNote()
	{
		var docnote 	=	document.getElementById('docnote0').value;
		NoteDetail(docnote);
	}
	function DeleteNote(ind)
	{
	    $.ajax({
	        url: '{{ route('doctornotedelete.html')}}',
	        type: 'GET',
	        data: {'_token': '{{ csrf_token() }}',ind:ind},
	        success: function(response) {
	        	bootbox.alert("DATA DELETED SUCCESSFULLY");
		        $(".notedetail").html(response);
		        $("#docnote0").focus();
	        },
	        error: function(xhr, status, error) {
      		}
	    });

	}
	function DeleteFamily(ind)
	{
	    $.ajax({
	        url: '{{ route('doctorfamilydelete.html')}}',
	        type: 'GET',
	        data: {'_token': '{{ csrf_token() }}',ind:ind},
	        success: function(response) {
	        	bootbox.alert("DATA DELETED SUCCESSFULLY");
	            $(".familydetail").html(response);
	            $("#membername0").focus();
	        },
	        error: function(xhr, status, error) {

      		}
	    });

	}
	function UpdateNotes(skey,ind)
	{
		var docnote 	=	document.getElementById('docnote'+ind).value;
	    $.ajax({
	        url: '{{ route('doctornoteupdate.html')}}',
	        type: 'GET',
	        data: {'_token': '{{ csrf_token() }}',skey:skey,docnote:docnote},
	        success: function(response) {
	        	bootbox.alert("DATA UPDATED SUCCESSFULLY");
		        $(".notedetail").html(response);
		        $("#note0").focus();
	        },
	        error: function(xhr, status, error) {
      		}
	    });
	}
	function UpdateFamily(skey,ind)
	{
		var membername 	=	document.getElementById('membername'+ind).value;
		var gender 		=	document.getElementById('gender'+ind).value;
		var relation 	=	document.getElementById('relation'+ind).value;
		var dob 		=	document.getElementById('dob'+ind).value;
	    $.ajax({
	        url: '{{ route('doctorfamilyupdate.html')}}',
	        type: 'GET',
	        data: {'_token': '{{ csrf_token() }}',skey:skey,membername:membername,gender:gender,relation:relation,dob:dob},
	        success: function(response) {
	        	bootbox.alert("DATA UPDATED SUCCESSFULLY");
		        $(".familydetail").html(response);
		        $("#membername0").focus();
	        },
	        error: function(xhr, status, error) {
	        	alert(error);
      		}
	    });
	}

	setTimeout(function() { $("#accountname").focus(); },2000);
