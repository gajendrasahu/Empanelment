@php
$i=1;
$t=0;
@endphp
<form name="resourcefrm" id="resourcefrm" action="" method="post" enctype="multipart/form-data">
@csrf
	<div class="row">
		<div class="col-sm-3">
			<input type="hidden" name="recordid" id="recordid" readonly value="{{$record->recordid}}">
			
			<span class="form-label">Sector <label id="req">*</label></span>
			<input type="hidden" name="projectduration" id="projectduration" value="{{$record->projectduration}}">
			<select name="sector_id" id="sector_id" class="form-control">
				@foreach($sectors as $sector)
				<option value="{{$sector->sectorid}}" @if($sector->sectorid==$record->sectorid) selected @endif>{{$sector->sectorname}}</option>
				@endforeach

			</select>
		</div>
		<div class="col-sm-3">
			<span class="form-label">Position <label id="req">*</label></span>
			<select name="position_id" id="position_id" class="form-control" onchange="setResourceExperience()">
				@foreach($positions as $position)
				<option value="{{$position->positionid}}" @if($position->positionid==$record->positionid) selected @endif data-value="{{$position->experience}}">{{$position->consultantposition}}</option>
				@endforeach

			</select>
		</div>

		<div class="col-sm-2">
			<span class="form-label">Experience <label id="req">*</label></span>
			<input type="text" name="resource_experience" id="resource_experience" class="form-control" readonly value="{{$record->experience}}">
		</div>

		<div class="col-sm-2">
			<span class="form-label">Deployment Type <label id="req">*</label></span>
			<select name="employment_type" id="employment_type" class="form-control" onKeyPress="return OnKeyPress(this, event)" tabindex="{{$t++}}" onchange="PopulateDurationUpdate()">
				<option value="FULL TIME" @if($record->employmenttype=='FULL TIME') selected @endif>FULL TIME</option>
				<option value="PART TIME" @if($record->employmenttype=='PART TIME') selected @endif>PART TIME</option>
			</select>
		</div>

		<div class="col-sm-2">
			<span class="form-label">Duration <label id="req">*</label></span>
			<select name="resource_duration" id="resource_duration" class="form-control">
				<option value="{{$record->duration}}">{{$record->duration}} Months</option>
			</select>
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">
			<span class="form-label">Qualification <label id="req">*</label></span>
			<textarea type="text" name="qualification" id="qualification" class="form-control tinymce">{{$record->qualification}}</textarea>
		</div>

		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-12">
			<span class="form-label">Remark <label id="req">*</label></span>
			<textarea type="text" name="remark" id="remark" class="form-control tinymce">{{$record->remark}}</textarea>
		</div>
		<div class="col-sm-12">&nbsp;</div>
		<div class="col-sm-10">&nbsp;</div>
		<div class="col-sm-2">
			<button type="button" class="btn btn-info myfrmbtn submitUpdateBtn"><i class="fa fa-save"></i> Update</button>
		</div>
	</div>
</form>



<script src="{{ asset('panel/assets/tinymce/tinymce.min.js') }}"></script>
<script>

function setResourceExperience()
{
    var experience = $('#position_id option:selected').data('value');
    $('#resource_experience').val(experience);
}


tinymce.init({
    selector: 'textarea.tinymce',
    promotion: false,
    branding: false,
    plugins: 'autoresize code advlist autolink lists charmap preview table searchreplace save',
    toolbar_mode: 'floating',
    toolbar: 'undo redo | formatselect | bold italic underline strikethrough | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | searchreplace',
    menubar: false,
    statusbar: false,
    min_height: 60,
    autoresize_min_height: 60,
    autoresize_max_height: 490,
    autoresize_bottom_margin: 10,

    resize: true,

    setup: function (editor) {
        editor.on('init', function () {
            editor.getBody().style.fontSize = '14px';
            editor.getBody().style.lineHeight = '1.5';
        });
        editor.on('change keyup', function () {
            editor.save(); // equivalent to triggerSave for this editor
        });		
		
    }
});

$('.submitUpdateBtn').on('click', function(e) {
    e.preventDefault();
	
	bootbox.confirm('Once you click OK, the data will be updated and cannot be reverted. Do you want to proceed?',function(result){
		if(result)
		{
			tinymce.triggerSave();
			$(".submitUpdateBtn").prop("disabled", true);

			let form = document.forms['resourcefrm'];
			let formData = new FormData(form);

			$.ajax({
				url: '{{ route("log.pmresourceupdate") }}',
				method: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				success: function(response) {
					$(".submitUpdateBtn").prop("disabled", false);

					if (response.status === 200)
					{
						bootbox.alert(response.message);
						setTimeout(function(){ GetManagerForm('{{route("load-pm-form")}}'); $('#editContent').html(""); $('#editModal').modal('hide'); },3000);
					}
					else if(response.status === 400)
					{
						bootbox.alert(response.message);
					}
					else 
					{
						bootbox.alert('Upload failed: ' + response.message);
					}
				},
				error: function(xhr) {
					$(".submitUpdateBtn").prop("disabled", false);

					if (xhr.responseJSON && xhr.responseJSON.errors) {
						let allMessages = '';
						$.each(xhr.responseJSON.errors, function(field, messages) {
							$.each(messages, function(index, msg) {
								allMessages += msg + '<br>';
							});
						});
						bootbox.alert(allMessages);
					}
				}
			});
			
		}
	});
});

</script>