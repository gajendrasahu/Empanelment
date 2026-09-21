
let currentStep = 1;
const totalSteps = 3;

function showStep(step) {
	const steps = document.querySelectorAll('.multi-form-section');
	steps.forEach(s => s.classList.remove('active'));
	
	document.getElementById('step' + step).classList.add('active');
	
	const indicators = document.querySelectorAll('.multi-step-indicator li');
	indicators.forEach((indicator, index) => {
		indicator.classList.remove('active', 'completed');
		if (index + 1 < step) {
			indicator.classList.add('completed');
		} else if (index + 1 === step) {
			indicator.classList.add('active');
		}
	});
	
	const progress = (step / totalSteps) * 100;
	const progressBar = document.getElementById('progressBar');
	progressBar.style.width = progress + '%';
	progressBar.textContent = 'Step ' + step + ' of ' + totalSteps;
	
	
	
	//document.getElementById('frmPrint').style.display = (step === 1 || step === 2) ? 'none' : 'inline-block';

	document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
	document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-block';
	document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-block' : 'none';
}

function changeStep(direction) {
    var projectduration = document.getElementById("projectduration").value;
    var projecttitle = document.getElementById("projecttitle").value;

    if ((projectduration || projecttitle) == '') {
        bootbox.alert("Please enter project name and project duration.");
        return false;
    }
	
    const newStep = currentStep + direction;

    if (newStep >= 1 && newStep <= totalSteps) {

        // VALIDATE BEFORE MOVING
        if (newStep === 3) {
            let tier_choice = document.getElementById("tier_choice").value;
			let recCount = document.getElementById("recs").value;
            if (tier_choice === '') {
                bootbox.alert("Please select the Tier of Firms to float the EoI.");
                return; // STOP HERE, DO NOT MOVE STEP
            }
            if (recCount==='0') {
                bootbox.alert("To proceed, please ensure that at least one resource detail is added before submitting your request.");
                return; // STOP HERE, DO NOT MOVE STEP
            }			
        }

        // Now it's safe to update the step
        currentStep = newStep;
        showStep(currentStep);

        if (currentStep === totalSteps) {
            populateReview();
        }
    }
}

function populateReview() {
	const reviewContent = document.getElementById('reviewContent');
	const projectTitle = document.getElementById('projectTitle').value || 'Not specified';
	const department = document.getElementById('department').value || 'Not selected';
	const projectDescription = document.getElementById('projectDescription').value || 'Not provided';
	
	reviewContent.innerHTML = `
		<div class="multi-panel multi-panel-default">
			<div class="multi-panel-body">
				<h5>Project Information</h5>
				<p><strong>Title:</strong> ${projectTitle}</p>
				<p><strong>Department:</strong> ${department}</p>
				<p><strong>Description:</strong> ${projectDescription}</p>
			</div>
		</div>
	`;
}

function submitForm() {
	alert('EoI Form submitted successfully!');
}

showStep(currentStep);



