@extends('admin.admin_master')
@section('admin')
@php
$t=0;
@endphp
<div class="main-content">
<style>
#menu-list {
  list-style: none;
  margin: 0;
  padding: 10px;
  border:1px solid #eee;
}

#menu-list li {
  padding: 5px 10px;
  cursor: pointer;
}

.tree-parent-anchor::before {
  content: ""; /* Remove default triangle indicator */
}

.toggler {
  font-size: 12px;
  margin-left: 5px;
}

#menu-list li ul {
  display: none;
}

#menu-list li.open > .toggler {
  content: "-"; /* Minus icon for open menus */
}
</style>
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
			<li class="active"><a data-toggle="tab" href="#home"><i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> SET MENU</a></li>
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('store.tag',0)}}" method="post" enctype="multipart/form-data">
				@if(Session::has('success'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-success">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('success') }}
					</div>
				</div>
				@endif
				@if(Session::has('fail'))
				<div class="col-sm-12">
					<div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
						{{ Session::get('fail') }}
					</div>
				</div>
				@endif
				@if(Session::has('duplicate'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('duplicate') }}
				    </div>
				</div>
				@endif			
				@csrf
				<div class="form-group" style=" padding:0px 3px;">
<ul id="menu-list">
  <li style="border:1px dotted #eee;">
    <a href="#" class="tree-parent-anchor"><span class="toggler"><i class="fa fa-plus-square"></i></span> Menu 1</a>
    <ul>
      <li style="list-style:none; border:1px dotted #eee;">
        <a href="#" class="tree-parent-anchor"><span class="toggler"><i class="fa fa-plus-square"></i></span> Menu 1.2</a>
        <ul>
          <li style="list-style:none; border:1px dotted #eee;"><input type="checkbox"> Menu 1.2.1</li>
          <li style="list-style:none; border:1px dotted #eee;"><input type="checkbox"> Menu 1.2.2</li>
        </ul>
      </li>
    </ul>
  </li>
  <li style="border:1px dotted #eee;">Menu 2</li>
  <li style="border:1px dotted #eee;">Menu 3</li>
</ul>
				</div>		
			</form>
		</div>
	</div>
</div>

</div>
</div>


						
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>
<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
$(document).ready(function() {
  $("#menu-list .tree-parent-anchor").click(function() {
    $(this).siblings("ul").slideToggle();
    $(this).toggleClass("open"); // Toggle "open" class on parent item
    $(this).children(".toggler").html($(this).hasClass("open") ? '<i class="fa fa-minus-square"></i>' : '<i class="fa fa-plus-square"></i>');
  });
});

</script>
@verbatim
<script>
function loadData(page,r1)
{
	var pagesize	=	document.getElementById("pagesize").value;
	var pagesearch	=	document.getElementById("pagesearch").value;
	$.get(""+r1,
	{
		page:page,
		pagesize:pagesize,
		pagesearch:pagesearch,
	},
	function(data, status){
		$(".tabledata").html(data);
		$(".mytr").click(function(){
			$(".mytr").css("background-color","");
			$(".mytr").css("color","");
			$(this).css("background-color", "#438EB9");
			$(this).css("color","white");
		});
	});
}

</script>
@endverbatim
<script src="{{ asset('panel/assets/js/ace-elements.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/ace.min.js') }}"></script>
<script src="{{ asset('panel/assets/js/datatablePaginationWithFilter.js') }}"></script>
@endsection