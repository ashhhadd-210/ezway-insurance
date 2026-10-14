/* Site-wide interactions: header, menu, scroll reveals, counters, parallax,
   FAQ accordions, contact form and input masks. */
(function () {
  'use strict';

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var $ = function (sel, root) { return (root || document).querySelector(sel); };
  var $$ = function (sel, root) { return Array.prototype.slice.call((root || document).querySelectorAll(sel)); };

  /* ---------- Toast ---------- */
  var toastEl = $('[data-toast]'), toastTimer;
  function toast(msg) {
    if (!toastEl) return;
    toastEl.textContent = msg;
    toastEl.classList.add('is-on');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { toastEl.classList.remove('is-on'); }, 3200);
  }

  /* ---------- Input masks ---------- */
  function maskPhone(v) {
    var d = v.replace(/\D/g, '');
    if (d.length === 11 && d[0] === '1') d = d.slice(1);
    d = d.slice(0, 10);
    if (d.length < 4) return d.length ? '(' + d : '';
    if (d.length < 7) return '(' + d.slice(0, 3) + ') ' + d.slice(3);
    return '(' + d.slice(0, 3) + ') ' + d.slice(3, 6) + '-' + d.slice(6);
  }
  function maskDate(v, deleting) {
    var d = v.replace(/\D/g, '').slice(0, 8);
    var out = d.slice(0, 2);
    if (d.length >= 2 && (d.length > 2 || !deleting)) out += '/';
    out += d.slice(2, 4);
    if (d.length >= 4 && (d.length > 4 || !deleting)) out += '/';
    out += d.slice(4, 8);
    return out;
  }
  document.addEventListener('input', function (e) {
    var el = e.target, kind = el.getAttribute && el.getAttribute('data-mask');
    if (!kind) return;
    var deleting = (e.inputType || '').indexOf('delete') === 0;
    var next = kind === 'phone' ? (deleting && el.value.length < 2 ? el.value.replace(/\D/g, '') : maskPhone(el.value)) : maskDate(el.value, deleting);
    if (next !== el.value) el.value = next;
  }, true);

  /* ---------- Header ---------- */
  var nav = $('[data-nav]');
  var burger = $('[data-burger]');
  var drawer = $('[data-drawer]');
  function onScroll() {
    if (nav) nav.classList.toggle('is-scrolled', window.scrollY > 8);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  function setDrawer(open) {
    if (!drawer) return;
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    drawer.hidden = !open;
  }
  if (burger) {
    burger.addEventListener('click', function () { setDrawer(drawer.hidden); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !drawer.hidden) { setDrawer(false); burger.focus(); } });
    $$('a, button', drawer).forEach(function (a) { a.addEventListener('click', function () { setDrawer(false); }); });
    window.addEventListener('resize', function () { if (window.innerWidth > 900) setDrawer(false); });
  }

  /* ---------- Floating quote button ---------- */
  var fab = $('[data-fab]');
  if (fab) {
    setTimeout(function () { fab.classList.add('is-visible'); }, 700);
    // Step aside when the footer's own quote button is on screen so they don't overlap.
    var footer = $('.footer');
    if (footer && 'IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        fab.classList.toggle('is-hidden', entries[0].isIntersecting && window.innerWidth <= 620);
      }, { threshold: 0.35 }).observe(footer);
    }
  }

  /* ---------- Scroll reveal ---------- */
  var revealables = $$('.reveal, .ready-art, .timeline li');
  if ('IntersectionObserver' in window && !reduceMotion) {
    // Stagger siblings that enter together.
    revealables.forEach(function (el) {
      if (!el.classList.contains('reveal')) return;
      var sibs = Array.prototype.filter.call(el.parentElement.children, function (c) { return c.classList.contains('reveal'); });
      var i = sibs.indexOf(el);
      if (i > 0) el.style.setProperty('--delay', Math.min(i, 6) * 0.08 + 's');
    });
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 });
    revealables.forEach(function (el) { io.observe(el); });
  } else {
    revealables.forEach(function (el) { el.classList.add('is-in'); });
  }

  /* ---------- Count-up numbers ---------- */
  var counters = $$('[data-count]');
  function countUp(el) {
    var end = parseFloat(el.getAttribute('data-count')), start = performance.now(), dur = 1400;
    if (reduceMotion) { el.textContent = end; return; }
    (function tick(now) {
      var p = Math.min(1, (now - start) / dur), eased = 1 - Math.pow(1 - p, 4);
      el.textContent = Math.round(end * eased);
      if (p < 1) requestAnimationFrame(tick);
    })(start);
  }
  if ('IntersectionObserver' in window) {
    var cio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { countUp(en.target); cio.unobserve(en.target); } });
    }, { threshold: 0.6 });
    counters.forEach(function (c) { cio.observe(c); });
  } else {
    counters.forEach(function (c) { c.textContent = c.getAttribute('data-count'); });
  }

  /* ---------- Hero parallax ---------- */
  var art = $('[data-parallax]');
  if (art && !reduceMotion && window.matchMedia('(pointer: fine)').matches) {
    $$('[data-depth]', art).forEach(function (c) { c.style.setProperty('--d', c.getAttribute('data-depth')); });
    var hero = art.closest('section');
    var raf;
    hero.addEventListener('mousemove', function (e) {
      cancelAnimationFrame(raf);
      raf = requestAnimationFrame(function () {
        var r = hero.getBoundingClientRect();
        art.style.setProperty('--px', ((e.clientX - r.left) / r.width - 0.5).toFixed(3));
        art.style.setProperty('--py', ((e.clientY - r.top) / r.height - 0.5).toFixed(3));
      });
    });
    hero.addEventListener('mouseleave', function () { art.style.setProperty('--px', 0); art.style.setProperty('--py', 0); });
  }

  /* ---------- Card spotlight ---------- */
  $$('.cov-card').forEach(function (card) {
    card.addEventListener('pointermove', function (e) {
      var r = card.getBoundingClientRect();
      card.style.setProperty('--mx', (e.clientX - r.left) + 'px');
      card.style.setProperty('--my', (e.clientY - r.top) + 'px');
    });
  });

  /* ---------- Smooth FAQ accordions ---------- */
  $$('.faq details').forEach(function (d) {
    var summary = $('summary', d), body = $('div', d);
    summary.addEventListener('click', function (e) {
      if (reduceMotion || !body.animate) return;
      e.preventDefault();
      if (d.open) {
        var h = body.offsetHeight;
        body.animate([{ height: h + 'px', opacity: 1 }, { height: '0px', opacity: 0 }], { duration: 260, easing: 'ease-in' })
          .onfinish = function () { d.open = false; };
      } else {
        d.open = true;
        var full = body.offsetHeight;
        body.animate([{ height: '0px', opacity: 0 }, { height: full + 'px', opacity: 1 }], { duration: 320, easing: 'cubic-bezier(.2,.8,.2,1)' });
      }
    });
  });

  /* ---------- Coverage page: highlight the section in view ---------- */
  var covNav = $('[data-covnav]');
  if (covNav && 'IntersectionObserver' in window) {
    var links = $$('a', covNav);
    var map = {};
    links.forEach(function (a) { map[a.getAttribute('href').slice(1)] = a; });
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (!en.isIntersecting) return;
        links.forEach(function (a) { a.classList.remove('is-active'); });
        var a = map[en.target.id];
        if (a) {
          a.classList.add('is-active');
          covNav.firstElementChild.scrollTo({ left: a.offsetLeft - 24, behavior: reduceMotion ? 'auto' : 'smooth' });
        }
      });
    }, { rootMargin: '-45% 0px -50% 0px' });
    Object.keys(map).forEach(function (id) { var s = document.getElementById(id); if (s) sio.observe(s); });
  }

  /* ---------- Home: VIN typing demo ---------- */
  var mock = $('[data-mock]');
  if (mock) {
    var vinEl = $('[data-mock-vin]', mock), chips = $$('[data-mock-chip]', mock);
    var demoVin = '4T1G11AK5LU312845', playing = false;
    function play() {
      if (playing) return;
      playing = true;
      mock.classList.remove('is-decoded');
      chips.forEach(function (c, i) { c.classList.toggle('on', i === 0); });
      vinEl.textContent = '';
      var i = 0;
      (function type() {
        if (i <= demoVin.length) {
          vinEl.textContent = demoVin.slice(0, i++);
          setTimeout(type, reduceMotion ? 0 : 70 + Math.random() * 60);
        } else {
          setTimeout(function () { mock.classList.add('is-decoded'); }, 350);
          setTimeout(function () { chips[1].classList.add('on'); }, 1100);
          setTimeout(function () { chips[2].classList.add('on'); }, 1600);
          setTimeout(function () { playing = false; play(); }, 6000);
        }
      })();
    }
    if ('IntersectionObserver' in window) {
      var mio = new IntersectionObserver(function (en) { if (en[0].isIntersecting) { play(); mio.disconnect(); } }, { threshold: 0.4 });
      mio.observe(mock);
    } else { play(); }
  }

  /* ---------- Hero ZIP form opens the quote with the ZIP pre-filled ---------- */
  var zipForm = $('[data-zipform]');
  if (zipForm) {
    zipForm.addEventListener('submit', function (e) {
      e.preventDefault();
      var zip = zipForm.zip.value.trim();
      if (zip && !/^\d{5}$/.test(zip)) {
        zipForm.classList.remove('is-bad'); void zipForm.offsetWidth; zipForm.classList.add('is-bad');
        toast('Please enter a 5-digit ZIP code.');
        zipForm.zip.focus();
        return;
      }
      if (window.QuoteWizard) window.QuoteWizard.open({ zip: zip });
    });
  }

  /* ---------- Turnstile (optional) ---------- */
  function renderTurnstile(box, sitekey, cb) {
    if (!sitekey || !box) return;
    (function wait(n) {
      if (window.turnstile) { cb(window.turnstile.render(box, { sitekey: sitekey })); }
      else if (n < 50) setTimeout(function () { wait(n + 1); }, 200);
    })(0);
  }

  /* ---------- Contact form ---------- */
  var cform = $('[data-contact]');
  if (cform) {
    var tsId = null;
    renderTurnstile($('[data-turnstile-box]', cform), cform.getAttribute('data-turnstile'), function (id) { tsId = id; });

    var showErr = function (name, msg) {
      var el = cform.elements[name];
      if (!el) return;
      var box = el.closest('.fld').querySelector('.fld-e');
      el.classList.toggle('is-bad', !!msg);
      el.setAttribute('aria-invalid', msg ? 'true' : 'false');
      box.textContent = msg || '';
      box.classList.toggle('is-on', !!msg);
    };
    cform.addEventListener('input', function (e) { if (e.target.name) showErr(e.target.name, ''); });

    cform.addEventListener('submit', function (e) {
      e.preventDefault();
      var f = cform.elements, errs = {};
      if (f.name.value.trim().length < 2) errs.name = 'Enter your name.';
      if (!/^\S+@\S+\.\S{2,}$/.test(f.email.value.trim())) errs.email = 'Enter a valid email address.';
      if (f.phone.value && f.phone.value.replace(/\D/g, '').length !== 10) errs.phone = 'Enter a 10-digit phone number, or leave it blank.';
      if (f.message.value.trim().length < 10) errs.message = 'Tell us a little more (at least 10 characters).';
      ['name', 'email', 'phone', 'message'].forEach(function (n) { showErr(n, errs[n]); });
      var first = Object.keys(errs)[0];
      if (first) { f[first].focus(); return; }

      var btn = $('button[type=submit]', cform);
      btn.classList.add('is-loading'); btn.disabled = true;
      fetch(cform.getAttribute('data-api'), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          token: cform.getAttribute('data-token'),
          website: f.website.value,
          turnstile: tsId !== null && window.turnstile ? window.turnstile.getResponse(tsId) : '',
          name: f.name.value, email: f.email.value, phone: f.phone.value, topic: f.topic.value, message: f.message.value
        })
      }).then(function (r) { return r.json().catch(function () { return { ok: false }; }); })
        .then(function (res) {
          if (res.ok) {
            cform.classList.add('is-done');
            $('.form-done', cform).hidden = false;
            return;
          }
          if (res.errors) Object.keys(res.errors).forEach(function (n) { showErr(n, res.errors[n]); });
          toast(res.error || 'Something went wrong. Please try again.');
          if (tsId !== null && window.turnstile) window.turnstile.reset(tsId);
        })
        .catch(function () { toast('Network error. Please check your connection and try again.'); })
        .then(function () { btn.classList.remove('is-loading'); btn.disabled = false; });
    });
  }

  window.Site = { toast: toast, renderTurnstile: renderTurnstile, reduceMotion: reduceMotion };
})();
