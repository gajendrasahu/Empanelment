function checkAll(bx) {
  var cbs = document.getElementsByTagName('input');
  for(var i=0; i < cbs.length; i++) {
    if(cbs[i].type == 'checkbox') {
      cbs[i].checked = bx.checked;
    }
  }
}

function Delete() {
  var cbs = document.getElementsByTagName('input');
  var bcs	=	new Array();
  for(var i=0; i < cbs.length; i++) {
    if(cbs[i].type == 'checkbox') {
//      cbs[i].checked = bx.checked;
	if(cbs[i].checked==true)
	{
		if(cbs[i].value!="on")
		{
		bcs[i]=cbs[i].value;
//		alert("Found True"+cbs[i].value);		
		}
	}
    }
  }
  var a	=	confirm("Deleting record cannot be undone! Are you sure you want to do this?"); 
  if(a)
  {
	  document.forms.frm.ids.value=cleanArray(bcs);
	  document.forms.frm.submit();	
  }
}


function cleanArray(actual){
  var newArray = new Array();
  for(var i = 0; i<actual.length; i++){
      if (actual[i]){
        newArray.push(actual[i]);
    }
  }
  return newArray;
}	
