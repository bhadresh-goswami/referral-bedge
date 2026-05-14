(() => {
  const uploadBox = document.getElementById('uploadBox');
  const fileInput = document.getElementById('resume_file');
  const fileLabel = document.getElementById('uploadFile');

  if (!uploadBox || !fileInput || !fileLabel) return;

  const allowed = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

  const showFile = (file) => {
    if (!file) return;
    const validType = allowed.includes(file.type) || /\.(pdf|doc|docx)$/i.test(file.name);
    fileLabel.textContent = validType ? file.name : 'Invalid file format. Use PDF, DOC, DOCX only.';
  };

  fileInput.addEventListener('change', (e) => showFile(e.target.files[0]));

  ['dragenter', 'dragover'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (e) => {
      e.preventDefault();
      uploadBox.classList.add('is-dragging');
    });
  });

  ['dragleave', 'drop'].forEach((eventName) => {
    uploadBox.addEventListener(eventName, (e) => {
      e.preventDefault();
      uploadBox.classList.remove('is-dragging');
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
})();
