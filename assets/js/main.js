document.addEventListener('DOMContentLoaded', function () {
  // Contact form validation (only on contact page)
  const form = document.getElementById('contactForm');
  if (form) {
    const nameInput = document.getElementById('name');
    const emailInput = document.getElementById('email');
    const messageInput = document.getElementById('message');

    function isValidEmail(value) {
      const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
      return re.test(String(value).trim());
    }

    function validateField(input, isValid, message = '') {
      if (!input) return;
      if (isValid) {
        input.setCustomValidity('');
        input.classList.remove('is-invalid');
      } else {
        input.setCustomValidity(message || 'Invalid field.');
        input.classList.add('is-invalid');
      }
    }

    function runValidations() {
      const nameVal = (nameInput?.value || '').trim();
      const emailVal = (emailInput?.value || '').trim();
      const messageVal = (messageInput?.value || '').trim();

      const nameOk = nameVal.length >= 2;
      validateField(nameInput, nameOk, 'Please enter your name (at least 2 characters).');

      const emailOk = isValidEmail(emailVal);
      validateField(emailInput, emailOk, 'Please enter a valid email.');

      const messageOk = messageVal.length >= 5;
      validateField(messageInput, messageOk, 'Please enter a message (at least 5 characters).');

      return nameOk && emailOk && messageOk;
    }

    [nameInput, emailInput, messageInput].forEach((el) => {
      if (!el) return;
      el.addEventListener('input', runValidations);
      el.addEventListener('blur', runValidations);
    });

    form.addEventListener('submit', function (e) {
      const ok = runValidations();
      if (!ok) {
        e.preventDefault();
        e.stopPropagation();
        const firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid && typeof firstInvalid.scrollIntoView === 'function') {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus({ preventScroll: true });
        }
      }
    });
  }

  // Global scroll-to-top button
  const btn = document.createElement('button');
  btn.type = 'button';
  btn.id = 'scrollTopBtn';
  btn.className = 'btn btn-primary scroll-top-btn';
  btn.setAttribute('aria-label', 'Scroll to top');
  btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true" focusable="false"><path d="M3.204 10.596a.75.75 0 0 0 1.06 0L8 6.86l3.736 3.736a.75.75 0 1 0 1.06-1.06L8.53 5.27a.75.75 0 0 0-1.06 0L3.204 9.536a.75.75 0 0 0 0 1.06z"/></svg>';
  document.body.appendChild(btn);

  function updateVisibility() {
    if (window.scrollY > 250) {
      btn.classList.add('show');
    } else {
      btn.classList.remove('show');
    }
  }

  window.addEventListener('scroll', updateVisibility, { passive: true });
  window.addEventListener('load', updateVisibility);

  btn.addEventListener('click', function () {
    try {
      window.scrollTo({ top: 0, behavior: 'smooth' });
    } catch (e) {
      // fallback
      document.documentElement.scrollTop = 0;
      document.body.scrollTop = 0;
    }
  });

  // Image preview for image input 
  const imageInput = document.getElementById('image');
  const imagePreview = document.getElementById('imagePreview');
  const imagePreviewHint = document.getElementById('imagePreviewHint');
  const removeCheckbox = document.getElementById('remove_image');
  const currentEventImg = document.querySelector('img[alt="Current Event Image"]');

  function hidePreview() {
    if (imagePreview) {
      imagePreview.src = '';
      imagePreview.style.display = 'none';
    }
    if (imagePreviewHint) {
      imagePreviewHint.style.display = '';
    }
  }

  function showPreviewFromFile(file) {
    if (!imagePreview || !file) return;
    const isImage = /^image\//i.test(file.type);
    if (!isImage) {
      hidePreview();
      return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
      imagePreview.src = e.target?.result || '';
      imagePreview.style.display = 'inline-block';
      if (imagePreviewHint) imagePreviewHint.style.display = 'none';
    };
    reader.readAsDataURL(file);
  }

  if (imageInput) {
    imageInput.addEventListener('change', function () {
      const file = imageInput.files && imageInput.files[0];
      if (file) {
        // uncheck remove if picking a new image
        if (removeCheckbox) removeCheckbox.checked = false;
        showPreviewFromFile(file);
      } else {
        hidePreview();
      }
    });
  }

  if (removeCheckbox) {
    removeCheckbox.addEventListener('change', function () {
      if (removeCheckbox.checked) {
        // Clear any selected file and hide preview
        if (imageInput) imageInput.value = '';
        hidePreview();
      }
    });
  }


  // Admin login/signup form validation 
  // admin/login.php
  const loginForm = document.getElementById('login-form');
  const signupForm = document.getElementById('signup-form');

  function setValidity(input, ok, message) {
    if (!input) return;
    const msgId = input.id ? input.id + '-error-top' : '';
    let msgEl = msgId ? document.getElementById(msgId) : null;

    if (ok) {
      input.setCustomValidity('');
      input.classList.remove('is-invalid');
      if (msgEl && msgEl.parentNode) msgEl.parentNode.removeChild(msgEl);
    } else {
      input.setCustomValidity(message || 'Invalid field');
      input.classList.add('is-invalid');
      if (!msgEl) {
        msgEl = document.createElement('div');
        if (msgId) msgEl.id = msgId;
        msgEl.className = 'text-danger small mb-1';
        // Insert the error message just before the input element 
        if (input.parentNode) {
          input.parentNode.insertBefore(msgEl, input);
        }
      }
      msgEl.textContent = message || 'Invalid field';
    }
  }

  function clearValidity(input) {
    if (!input) return;
    const msgId = input.id ? input.id + '-error-top' : '';
    const msgEl = msgId ? document.getElementById(msgId) : null;
    input.setCustomValidity('');
    input.classList.remove('is-invalid');
    if (msgEl && msgEl.parentNode) msgEl.parentNode.removeChild(msgEl);
  }

  
  function markTouched(input) {
    if (input) input.dataset.touched = '1';
  }

  function isTouched(input) {
    return !!(input && input.dataset && input.dataset.touched === '1');
  }

  function isValidEmail(value) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/i;
    return re.test(String(value || '').trim());
  }

  // Login form validation
  if (loginForm) {
    const loginUser = document.getElementById('login-username');
    const loginPass = document.getElementById('login-password');
    let loginSubmitAttempted = false;

    function validateLoginField(el) {
      if (!el) return true;
      const id = el.id;
      const u = (loginUser?.value || '').trim();
      const p = (loginPass?.value || '');
      const uOk = u.length >= 1;
      const pOk = p.length >= 1;
      if (el === loginUser) {
        if (loginSubmitAttempted || isTouched(el)) setValidity(el, uOk, 'Please enter your username.');
        else clearValidity(el);
        return uOk;
      }
      if (el === loginPass) {
        if (loginSubmitAttempted || isTouched(el)) setValidity(el, pOk, 'Please enter your password.');
        else clearValidity(el);
        return pOk;
      }
      return true;
    }

    function validateLogin() {
      const u = (loginUser?.value || '').trim();
      const p = (loginPass?.value || '');
      const uOk = u.length >= 1; 
      const pOk = p.length >= 1;
      const show = loginSubmitAttempted;
      if (show || isTouched(loginUser)) setValidity(loginUser, uOk, 'Please enter your username.'); else clearValidity(loginUser);
      if (show || isTouched(loginPass)) setValidity(loginPass, pOk, 'Please enter your password.'); else clearValidity(loginPass);
      return uOk && pOk;
    }

    [loginUser, loginPass].forEach((el) => {
      if (!el) return;
      el.addEventListener('input', function(){ validateLoginField(el); });
      el.addEventListener('blur', function(){ markTouched(el); validateLoginField(el); });
    });

    loginForm.addEventListener('submit', function (e) {
      loginSubmitAttempted = true;
      const ok = validateLogin();
      if (!ok) {
        e.preventDefault();
        e.stopPropagation();
        const firstInvalid = loginForm.querySelector('.is-invalid');
        if (firstInvalid && typeof firstInvalid.scrollIntoView === 'function') {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus({ preventScroll: true });
        }
      }
    });
  }

  // Signup form validation
  if (signupForm) {
    const suUser = document.getElementById('signup-username');
    const suEmail = document.getElementById('signup-email');
    const suPass = document.getElementById('signup-password');
    const suConfirm = document.getElementById('signup-confirm-password');
    let signupSubmitAttempted = false;

    function validateSignupField(el) {
      if (!el) return true;
      const u = (suUser?.value || '').trim();
      const e = (suEmail?.value || '').trim();
      const p = (suPass?.value || '');
      const c = (suConfirm?.value || '');
      const uOk = u.length >= 3;
      const eOk = isValidEmail(e);
      const pOk = p.length >= 5;
      const cOk = c === p && c.length > 0;

      if (el === suUser) {
        if (signupSubmitAttempted || isTouched(el)) setValidity(el, uOk, 'Username must be at least 3 characters.');
        else clearValidity(el);
        return uOk;
      }
      if (el === suEmail) {
        if (signupSubmitAttempted || isTouched(el)) setValidity(el, eOk, 'Please enter a valid email address.');
        else clearValidity(el);
        return eOk;
      }
      if (el === suPass) {
        if (signupSubmitAttempted || isTouched(el)) setValidity(el, pOk, 'Password must be at least 5 characters.');
        else clearValidity(el);
        if (isTouched(suConfirm)) validateSignupField(suConfirm);
        return pOk;
      }
      if (el === suConfirm) {
        if (signupSubmitAttempted || isTouched(el)) setValidity(el, cOk, 'Passwords do not match.');
        else clearValidity(el);
        return cOk;
      }
      return true;
    }

    function validateSignup() {
      const u = (suUser?.value || '').trim();
      const e = (suEmail?.value || '').trim();
      const p = (suPass?.value || '');
      const c = (suConfirm?.value || '');

      const uOk = u.length >= 3;
      const eOk = isValidEmail(e);
      const pOk = p.length >= 5;
      const cOk = c === p && c.length > 0;

      const show = signupSubmitAttempted;
      if (show || isTouched(suUser)) { setValidity(suUser, uOk, 'Username must be at least 3 characters.'); } else { clearValidity(suUser); }
      if (show || isTouched(suEmail)) { setValidity(suEmail, eOk, 'Please enter a valid email address.'); } else { clearValidity(suEmail); }
      if (show || isTouched(suPass)) { setValidity(suPass, pOk, 'Password must be at least 5 characters.'); } else { clearValidity(suPass); }
      if (show || isTouched(suConfirm)) { setValidity(suConfirm, cOk, 'Passwords do not match.'); } else { clearValidity(suConfirm); }

      return uOk && eOk && pOk && cOk;
    }

    [suUser, suEmail, suPass, suConfirm].forEach((el) => {
      if (!el) return;
      el.addEventListener('input', function(){ validateSignupField(el); });
      el.addEventListener('blur', function(){ markTouched(el); validateSignupField(el); });
    });

    signupForm.addEventListener('submit', function (e) {
      signupSubmitAttempted = true;
      const ok = validateSignup();
      if (!ok) {
        e.preventDefault();
        e.stopPropagation();
        const firstInvalid = signupForm.querySelector('.is-invalid');
        if (firstInvalid && typeof firstInvalid.scrollIntoView === 'function') {
          firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
          firstInvalid.focus({ preventScroll: true });
        }
      }
    });
  }
});
