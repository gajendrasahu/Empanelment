let currentStep = 1; // Starting at step 2 as shown in the image
const totalSteps = 3;

function showStep(step) {
	// Hide all steps
	const steps = document.querySelectorAll('.multi-form-section');
	steps.forEach(s => s.classList.remove('active'));
	
	// Show current step
	document.getElementById('step' + step).classList.add('active');
	
	// Update step indicators
	const indicators = document.querySelectorAll('.multi-step-indicator li');
	indicators.forEach((indicator, index) => {
		indicator.classList.remove('active', 'completed');
		if (index + 1 < step) {
			indicator.classList.add('completed');
		} else if (index + 1 === step) {
			indicator.classList.add('active');
		}
	});
	
	// Update progress bar
	const progress = (step / totalSteps) * 100;
	const progressBar = document.getElementById('progressBar');
	progressBar.style.width = progress + '%';
	progressBar.textContent = 'Step ' + step + ' of ' + totalSteps;
	
	// Update navigation buttons
	
	document.getElementById('prevBtn').style.display = step === 1 ? 'none' : 'inline-block';
	document.getElementById('nextBtn').style.display = step === totalSteps ? 'none' : 'inline-block';
	document.getElementById('submitBtn').style.display = step === totalSteps ? 'inline-block' : 'none';
}

function changeStep(direction) {
	var projectduration	=	document.getElementById("projectduration").value;
	var projecttitle	=	document.getElementById("projecttitle").value;
	if((projectduration || projecttitle)=='')
	{
		bootbox.alert("Please enter project name and project duration.");
		return false;
	}
	const newStep = currentStep + direction;
	if (newStep >= 1 && newStep <= totalSteps) {
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
	// Here you would typically send the form data to a server
}

// Initialize the form
showStep(currentStep);
