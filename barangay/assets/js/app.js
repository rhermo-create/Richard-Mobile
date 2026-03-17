/* Basic app helpers: min-date, phone validation, file preview, submit state */
(function(){
  'use strict';

  // Set minimum date for claimDate inputs to today
  const claimEl = document.getElementById('claimDate');
  if (claimEl) {
    claimEl.min = new Date().toISOString().split('T')[0];
  }

  // Simple phone input validation helper (Philippine 11-digit mobile numbers)
  document.querySelectorAll('input[type="tel"]').forEach(function(input){
    input.addEventListener('input', function(){
      const raw = this.value.replace(/\s+/g,'');
      const ok = /^09\d{9}$/.test(raw);
      if(raw.length > 0 && !ok) this.setCustomValidity('Enter 11 digits starting with 09');
      else this.setCustomValidity('');
    });
  });

  // File input preview (lists filenames)
  document.querySelectorAll('input[type="file"]').forEach(function(fileInput){
    const preview = document.createElement('div');
    preview.className = 'file-preview';
    fileInput.parentNode.appendChild(preview);
    fileInput.addEventListener('change', function(){
      preview.innerHTML = '';
      Array.from(this.files).forEach(function(f){
        const item = document.createElement('div');
        item.textContent = f.name + (f.size ? ' (' + Math.round(f.size/1024) + ' KB)' : '');
        preview.appendChild(item);
      });
    });
  });

  // Prevent double submit: disable button + change label
  document.querySelectorAll('form').forEach(function(form){
    form.addEventListener('submit', function(){
      const btn = form.querySelector('button[type="submit"]');
      if(btn){
        btn.disabled = true;
        btn.setAttribute('aria-disabled','true');
        btn.dataset.orig = btn.innerHTML;
        btn.innerHTML = 'Submitting...';
      }
    });
  });

})();

/* End of app.js */
