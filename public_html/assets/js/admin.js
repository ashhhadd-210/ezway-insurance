(function () {
  'use strict';

  // Whole table rows open the quote.
  document.querySelectorAll('tr[data-href]').forEach(function (tr) {
    tr.addEventListener('click', function (e) {
      if (e.target.closest('a, button, input')) return;
      window.location.href = tr.getAttribute('data-href');
    });
  });

  // Status changes save immediately.
  document.querySelectorAll('[data-autosubmit]').forEach(function (el) {
    el.addEventListener('change', function () { el.form.submit(); });
  });

  // Confirm destructive actions.
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!window.confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Reveal / hide license numbers.
  document.querySelectorAll('[data-reveal]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var box = btn.closest('[data-secret]');
      var plain = box.querySelector('[data-plain]'), masked = box.querySelector('[data-masked]');
      var show = plain.hidden;
      plain.hidden = !show;
      masked.hidden = show;
      btn.setAttribute('aria-label', show ? 'Hide license number' : 'Show license number');
      btn.querySelector('use').setAttribute('href', show ? '#i-eye-off' : '#i-eye');
    });
  });

  // Copy to clipboard.
  document.querySelectorAll('[data-copy]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var text = btn.getAttribute('data-copy');
      if (!text || !navigator.clipboard) return;
      navigator.clipboard.writeText(text).then(function () {
        btn.classList.add('is-done');
        btn.querySelector('use').setAttribute('href', '#i-check');
        setTimeout(function () {
          btn.classList.remove('is-done');
          btn.querySelector('use').setAttribute('href', '#i-doc');
        }, 1400);
      });
    });
  });

  // Show / hide password on the sign-in page.
  document.querySelectorAll('[data-pw]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var input = btn.parentElement.querySelector('input');
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.querySelector('use').setAttribute('href', show ? '#i-eye-off' : '#i-eye');
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });

  var printBtn = document.querySelector('[data-print]');
  if (printBtn) printBtn.addEventListener('click', function () { window.print(); });
})();
