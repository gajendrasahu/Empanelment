@extends('admin.admin_master')
@section('admin')
@php
$t=1;
@endphp

<div class="main-content">
	<div class="main-content-inner">
			<div class="page-content" style="padding:10px 15px!important;">
				<div class="ace-settings-container" id="ace-settings-container"></div>
				<div class="row">
					<div class="col-xs-12">
					<!-- PAGE CONTENT BEGINS -->
<div class="tabbable" style="padding:0px 0px!important;">
		<ul class="nav nav-tabs padding-0">
		@if(in_array(1,Session::get('actions')))
			<li class="active">
				<a data-toggle="tab" href="#home">
					<i class="green ace-icon fa fa-plus-circle bigger-120" style="vertical-align:bottom;"></i> {{__('labels.updatetaxtab')}}
				</a>
			</li>
		@endif
		</ul>

	
		<div class="tab-content no-border" style="border:1px solid #ddd; min-height:100px; padding:0px 10px;">
@if(in_array(1,Session::get('actions')))
<div id="home" class="tab-pane in active" style="padding:0px 10px!important;">
	<div class="row">
		<div class="row">
			<form name="frm" id="frm" action="{{ route('update.tax',$data->taxid)}}" method="post" enctype="multipart/form-data">
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
				@if(Session::has('taxrequired'))
				<div class="col-sm-12">
				    <div class="alert alert-block alert-danger">
				    	<button type="button" class="close" data-dismiss="alert">
							<i class="ace-icon fa fa-times"></i>
						</button>
				    	<i class="fa fa-warning"></i> {{ Session::get('taxrequired') }}
				    </div>
				</div>
				@endif
				
				@csrf
				<div class="form-group">
				
					<div class="col-sm-2">
						{{__('labels.taxname')}}<label id="req">*</label>
						<input type="text" class="form-control" name="taxname" id="taxname" value="{{old('taxname',$data->taxname)}}" placeholder="{{__('labels.taxname')}}" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" autofocus/>
						<input type="hidden" name="tempname" value="{{$data->tempname}}">
						<span class="text-danger">@error('taxname') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						{{__('labels.taxtype')}}<label id="req">*</label>
						<select class="form-control" name="taxtype" id="taxtype" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}">
							<option value="SINGLE" @if(old('taxtype',$data->taxtype)=='SINGLE') {{'selected'}} @endif>SINGLE</option>
							<option value="MULTIPLE" @if(old('taxtype',$data->taxtype)=='MULTIPLE') {{'selected'}} @endif>MULTIPLE</option>
						</select>
						<span class="text-danger">@error('taxtype') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2">
						{{__('labels.taxrate')}}<label id="req">*</label>
						<input type="text" class="form-control numbers" name="taxrate" id="taxrate" value="@if($data->taxtype=='SINGLE') {{old('taxrate',$data->taxrate)}} @endif" placeholder="TAX RATE (%)" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" @if(old('taxtype',$data->taxtype)=='MULTIPLE') {{ 'disabled' }} @endif/>

						<span class="text-danger">@error('taxrate') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span>
					</div>
					<div class="col-sm-2" align="left">
						<button type="submit" class="btn btn-info myfrmbtn" tabindex="{{$t++}}">{{__('common.update')}}</button>
					</div>
				
				<div class="col-sm-12">&nbsp;</div>
@php
$loop=0;
if(count(old('taxnames', []))==0)
$loop = intval(count($rates))-1;
else
$loop = count(old('taxnames', []));
@endphp
<div class="col-sm-12" id="multiple_tax_fields" @if($loop==0 || $rates->count()==0) style="display: none;"  @endif>
<div class="row">
	<div class="col-sm-2">
		<button type="button" class="btn btn-info myfrmbtn add-row"><i class="fa fa-plus"></i> {{__('labels.addmore')}}</button>
	</div>
</div>

@for($i=0;$i<=$loop;$i++)

<div class="row">
	<div class="col-sm-2">
		{{__('labels.taxname')}}<label id="req">*</label>
		<input type="text" class="form-control tax-name" name="taxnames[]" placeholder="TAX NAME" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{ old('taxnames.'.$i,$rates[$i]->taxname) }}"/>
	</div>
	<div class="col-sm-2">
		{{__('labels.taxrate')}}<label id="req">*</label>
		<input type="text" class="form-control tax-rate" name="taxrates[]" placeholder="TAX RATE" autocomplete="off" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" value="{{ old('taxrates.'.$i,$rates[$i]->taxrate) }}"/>
	</div>
	<div class="col-sm-2">
		<button type="button" class="btn btn-info myfrmbtn delete-row">{{__('common.delete')}}</button>
	</div>
</div>
@endfor
</div>


					<div class="col-sm-12"><span class="text-danger">@error('taxrates') <i class="fa fa-hand-o-right"> {{ strtoupper($message) }}</i> @enderror</span></div>
				</div>		
			</form>
		</div>
	</div>
</div>
@endif
</div>
</div>


						<div class="hr hr32 hr-dotted"></div>
				</div><!-- /.col -->
			</div><!-- /.row -->
		</div><!-- /.page-content -->
	
		
	</div>
</div>




	
	</div>
</div>


<script src="{{ asset('panel/assets/js/jquery-2.1.4.min.js') }}"></script>
<script>
    jQuery(function($) {
        $('#taxtype').change(function() {
            if ($(this).val() == 'MULTIPLE') {
                $('#taxrate').prop('disabled', true);
                $('#multiple_tax_fields').show();
				$(".tax-name").prop('required','true');
				$(".tax-rate").prop('required','true');
            } else {
                $('#taxrate').prop('disabled', false);
                $('#multiple_tax_fields').hide();
				$(".tax-name").prop('required','');
				$(".tax-rate").prop('required','');
            }
        });

        $('.add-row').click(function() {
            var newRow = '<div class="row"><div class="col-sm-2">' +
                'TAX RATE(%)<label id="req">*</label>' +
                '<input type="text" class="form-control tax-name" name="taxnames[]" placeholder="TAX NAME" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>' +
                '</div>' +
                '<div class="col-sm-2">' +
                'TAX RATE(%)<label id="req">*</label>' +
                '<input type="text" class="form-control tax-rate" name="taxrates[]" placeholder="TAX RATE" autocomplete="off" required onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}"/>' +
                '</div>' +
				'<div class="col-sm-2">' +
                '<button type="button" class="btn btn-info myfrmbtn delete-row">DELETE</button>'+
				'</div></div>';

            $("#multiple_tax_fields").append(newRow);;
        });

        $(document).on('click', '.delete-row', function() {
            $(this).closest('.row').remove();// Remove the inputs and labels before the delete button
            $(this).remove(); // Remove the delete button itself
        });
    });
</script>

@endsection