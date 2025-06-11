document.addEventListener('DOMContentLoaded', function() {
    const admissionForm = document.getElementById('admissionForm');
    const classSelect = document.getElementById('classApplying');
    const recommendationField = document.getElementById('recommendationField');
    const reportField = document.getElementById('reportField');

    // Show/hide fields based on class selection
    classSelect.addEventListener('change', function() {
        if (this.value === 's1' || this.value === 's5') {
            recommendationField.style.display = 'block';
            reportField.style.display = 'none';
            document.getElementById('recommendationLetter').setAttribute('required', '');
            document.getElementById('schoolReport').removeAttribute('required');
        } else if (this.value === 's2' || this.value === 's3' || this.value === 's4' || this.value === 's6') {
            recommendationField.style.display = 'none';
            reportField.style.display = 'block';
            document.getElementById('schoolReport').setAttribute('required', '');
            document.getElementById('recommendationLetter').removeAttribute('required');
        } else {
            recommendationField.style.display = 'none';
            reportField.style.display = 'none';
            document.getElementById('recommendationLetter').removeAttribute('required');
            document.getElementById('schoolReport').removeAttribute('required');
        }
    });

// Replace your existing form submission handler with this
admissionForm.addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (!this.checkValidity()) {
        alert('Please fill in all required fields correctly.');
        return;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'Submitting...';

    // Create FormData object
    const formData = new FormData(this);

    // Submit via AJAX
    fetch('process_admission.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            this.reset();
            // Clear all previews
            document.querySelectorAll('.file-preview').forEach(el => {
                el.innerHTML = '';
            });
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('Network error: Please try again later.');
    })
    .finally(() => {
        submitBtn.disabled = false;
        submitBtn.textContent = 'Submit Application';
    });
});
// Add this to your existing admissions.js
function setupFilePreviews() {
    // Preview for student photo
    document.getElementById('studentPhoto').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewFile(file, 'photoPreview');
        }
    });

    // Preview for rules document
    document.getElementById('rulesDocument').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewFile(file, 'rulesPreview');
        }
    });

    // Preview for recommendation letter
    document.getElementById('recommendationLetter').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewFile(file, 'recommendationPreview');
        }
    });

    // Preview for school report
    document.getElementById('schoolReport').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewFile(file, 'reportPreview');
        }
    });

    // Preview for results slip
    document.getElementById('resultsSlip').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            previewFile(file, 'resultsPreview');
        }
    });
}

function previewFile(file, previewId) {
    const previewElement = document.getElementById(previewId);
    if (!previewElement) return;

    // Clear previous preview
    previewElement.innerHTML = '';
    
    // Check if file is PDF
    if (file.type === 'application/pdf') {
        const objectEl = document.createElement('object');
        objectEl.data = URL.createObjectURL(file);
        objectEl.type = 'application/pdf';
        objectEl.width = '100%';
        objectEl.height = '200px';
        previewElement.appendChild(objectEl);
    } 
    // Check if file is an image
    else if (file.type.match('image.*')) {
        const imgEl = document.createElement('img');
        imgEl.src = URL.createObjectURL(file);
        imgEl.alt = 'Preview';
        imgEl.style.maxWidth = '100%';
        imgEl.style.maxHeight = '200px';
        previewElement.appendChild(imgEl);
    } else {
        previewElement.textContent = `File: ${file.name}`;
    }
}

// Add this to your DOMContentLoaded event listener
setupFilePreviews();