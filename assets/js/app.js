(() => {
  const form = document.querySelector('.referral-form');
  const uploadBox = document.getElementById('uploadBox');
  const fileInput = document.getElementById('resume_file');
  const fileLabel = document.getElementById('uploadFile');
  const submitButton = form?.querySelector('.btn-submit');

  if (!form || !uploadBox || !fileInput || !fileLabel || !submitButton) return;

  const allowedMime = [
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
  ];
  const allowedExt = ['pdf', 'doc', 'docx'];
  const maxSize = 5 * 1024 * 1024;

  const showAlert = (icon, title, text) => {
    if (window.Swal) {
      window.Swal.fire({ icon, title, text, timer: icon === 'success' ? 2600 : undefined, showConfirmButton: icon !== 'success' });
      return;
    }

    window.alert(`${title}: ${text}`);
  };

  const getExtension = (name = '') => name.split('.').pop()?.toLowerCase() || '';

  const validateFile = (file) => {
    if (!file) return { valid: true };

    const ext = getExtension(file.name);
    const extOk = allowedExt.includes(ext);
    const mimeOk = allowedMime.includes(file.type) || file.type === '';

    if (!extOk || !mimeOk) {
      return { valid: false, message: 'Invalid file format. Use PDF, DOC, DOCX only.' };
    }

    if (file.size > maxSize) {
      return { valid: false, message: 'File exceeds 5MB limit.' };
    }

    return { valid: true };
  };

  const showFile = (file) => {
    const result = validateFile(file);
    if (!file) {
      fileLabel.textContent = 'No file selected';
      return true;
    }

    fileLabel.textContent = result.valid ? file.name : result.message;
    fileLabel.style.color = result.valid ? '' : '#ff7b7b';
    return result.valid;
  };

  fileInput.addEventListener('change', (e) => showFile(e.target.files[0]));

  ['dragenter', 'dragover'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (e) => {
      e.preventDefault();
      uploadBox.classList.add('is-dragging');
      uploadBox.style.transform = 'translateY(-2px)';
    });
  });

  ['dragleave', 'drop'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (e) => {
      e.preventDefault();
      uploadBox.classList.remove('is-dragging');
      uploadBox.style.transform = '';
    });
  });

  uploadBox.addEventListener('drop', (e) => {
    const file = e.dataTransfer.files[0];
    if (!file) return;

    const dt = new DataTransfer();
    dt.items.add(file);
    fileInput.files = dt.files;
    showFile(file);
  });

  const buildValidationErrors = () => {
    const errors = [];
    form.querySelectorAll('[required]').forEach((field) => {
      if (!field.value.trim()) {
        errors.push(`${field.name.replaceAll('_', ' ')} is required.`);
      }
    });

    const emailFields = ['your_email', 'referral_email'];
    emailFields.forEach((name) => {
      const field = form.querySelector(`[name="${name}"]`);
      if (field?.value && !/^\S+@\S+\.\S+$/.test(field.value)) {
        errors.push(`${name.replaceAll('_', ' ')} is invalid.`);
      }
    });

    const file = fileInput.files[0];
    const fileResult = validateFile(file);
    if (!fileResult.valid) {
      errors.push(fileResult.message);
    }

    return errors;
  };

  form.addEventListener('submit', async (event) => {
    event.preventDefault();

    const clientErrors = buildValidationErrors();
    if (clientErrors.length > 0) {
      showAlert('error', 'Validation Error', clientErrors[0]);
      return;
    }

    const originalBtnText = submitButton.textContent;
    submitButton.disabled = true;
    submitButton.textContent = 'Submitting...';
    form.classList.add('is-loading');
    form.style.opacity = '0.75';

    try {
      const formData = new FormData(form);
      const response = await fetch('/submit-referral', {
        method: 'POST',
        body: formData,
        headers: {
          Accept: 'application/json',
        },
      });

      const payload = await response.json();

      if (!response.ok || !payload.success) {
        const message = payload.message || Object.values(payload.errors || {}).join('\n') || 'Unable to submit referral.';
        showAlert('error', 'Submission Failed', message);
        return;
      }

      showAlert('success', 'Success!', payload.message || 'Referral submitted successfully.');
      form.reset();
      showFile(null);
      fileLabel.textContent = 'Thank you! Your referral was submitted.';
    } catch (error) {
      showAlert('error', 'Server Error', 'Unexpected error while submitting the form. Please try again.');
    } finally {
      submitButton.disabled = false;
      submitButton.textContent = originalBtnText;
      form.classList.remove('is-loading');
      form.style.opacity = '';
    }
  });
})();
