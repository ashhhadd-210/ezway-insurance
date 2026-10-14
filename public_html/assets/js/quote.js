/* Multi-step quote wizard.
   Steps: You → Address → Drivers (+) → Vehicles (+, VIN lookup) → Coverage → Review.
   Works as a pop-up on every page and inline on /quote. */
(function () {
  'use strict';

  var root = document.querySelector('[data-qw]');
  if (!root) return;

  /* ------------------------------------------------------------------ */
  /* Elements & constants                                                */
  /* ------------------------------------------------------------------ */
  var $ = function (sel, el) { return (el || root).querySelector(sel); };
  var $$ = function (sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); };

  var mode = root.getAttribute('data-mode');
  var overlay = document.querySelector('[data-qoverlay]');
  var form = $('[data-qw-form]');
  var viewport = $('[data-qw-viewport]');
  var steps = $$('.qw-step');
  var stepItems = $$('[data-qw-steps] li');
  var bar = $('[data-qw-bar]');
  var stepLabel = $('[data-qw-steplabel]');
  var nextBtn = $('[data-next]');
  var nextLabel = $('[data-next-label]');
  var backBtn = $('[data-back]');
  var savedEl = $('[data-saved]');
  var doneEl = $('[data-qw-done]');
  var alertEl = $('[data-qw-alert]');
  var reviewEl = $('[data-review]');
  var API = root.getAttribute('data-api');
  var MAX = { drivers: +root.getAttribute('data-max-drivers') || 8, vehicles: +root.getAttribute('data-max-vehicles') || 6 };
  var STORE = 'qw-draft-v1';
  var STEP_NAMES = ['About you', 'Address', 'Drivers', 'Vehicles', 'Coverage', 'Review'];
  var STEP_OF = { applicant: 0, address: 1, drivers: 2, vehicles: 3, coverage: 4, consent: 5 };
  var VIN_RE = /^[A-HJ-NPR-Z0-9]{17}$/;
  var NAME_RE = /^[\p{L}][\p{L}\p{M} .'\-]{0,59}$/u;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var toast = function (m) { if (window.Site) window.Site.toast(m); };

  var tpl = { drivers: document.getElementById('tpl-driver'), vehicles: document.getElementById('tpl-vehicle') };
  var avatarIcon = {
    drivers: tpl.drivers.content.querySelector('[data-avatar]').innerHTML,
    vehicles: tpl.vehicles.content.querySelector('[data-avatar]').innerHTML
  };

  // Human-readable labels, read from the markup so PHP stays the single source of truth.
  function optionMap(select) {
    var m = {};
    Array.prototype.forEach.call(select.options, function (o) { if (o.value) m[o.value] = o.textContent; });
    return m;
  }
  function radioMap(selector, scope) {
    var m = {};
    $$(selector, scope).forEach(function (r) {
      var lbl = r.closest('label');
      var b = lbl.querySelector('b');
      m[r.value] = (b || lbl).textContent.trim();
    });
    return m;
  }
  var LABELS = {
    state: optionMap(form.elements['address.state']),
    relationship: Object.assign({ self: 'Policyholder (you)' }, optionMap(tpl.drivers.content.querySelector('[data-f=relationship]'))),
    liability: radioMap('input[name="coverage.liability"]'),
    deductible: radioMap('input[data-f=deductible]', tpl.vehicles.content),
    contact: radioMap('input[name="applicant.contact_pref"]')
  };

  function icon(name) { return '<svg class="i" aria-hidden="true"><use href="#i-' + name + '"/></svg>'; }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }

  /* ------------------------------------------------------------------ */
  /* State                                                               */
  /* ------------------------------------------------------------------ */
  function newDriver(rel) {
    return { first: '', last: '', dob: '', relationship: rel || '', id_type: 'us', license_state: '', license_country: '', license_number: '' };
  }
  function newVehicle() {
    return { vin: '', year: '', make: '', model: '', trim: '', body: '', decoded: false, full_coverage: false, deductible: '500', roadside: false, rental: false };
  }
  function fresh() {
    return {
      applicant: { first: '', last: '', dob: '', phone: '', email: '', contact_pref: 'phone' },
      address: { zip: '', street: '', unit: '', city: '', state: '' },
      drivers: [newDriver('self')],
      vehicles: [newVehicle()],
      coverage: { liability: 'better', pip: false, um: false, start_date: '', currently_insured: '', current_carrier: '', comments: '' },
      consent: false,
      step: 0,
      furthest: 0
    };
  }

  var state = load() || fresh();
  var cur = Math.min(state.step || 0, 5);
  var furthest = Math.max(cur, state.furthest || 0);
  var busy = false;
  var lastFocus = null;
  var tsId = null;
  var synced = {};          // values copied from "You" into driver 1, so later edits keep them in step
  var autoAddr = {};        // city/state we filled from the ZIP, so we can update them if the ZIP changes

  function load() {
    try {
      var raw = sessionStorage.getItem(STORE);
      if (!raw) return null;
      var d = JSON.parse(raw), base = fresh();
      ['applicant', 'address', 'coverage'].forEach(function (k) { base[k] = Object.assign(base[k], d[k] || {}); });
      if (Array.isArray(d.drivers) && d.drivers.length) base.drivers = d.drivers.slice(0, 8).map(function (x, i) { return Object.assign(newDriver(i ? '' : 'self'), x); });
      if (Array.isArray(d.vehicles) && d.vehicles.length) base.vehicles = d.vehicles.slice(0, 6).map(function (x) { return Object.assign(newVehicle(), x); });
      base.step = d.step || 0;
      base.furthest = d.furthest || 0;
      return base;
    } catch (e) { return null; }
  }

  var saveTimer, savedTimer;
  function save(flash) {
    clearTimeout(saveTimer);
    saveTimer = setTimeout(function () {
      try {
        var copy = JSON.parse(JSON.stringify(state));
        copy.step = cur;
        copy.furthest = furthest;
        // Never keep license numbers in the browser.
        copy.drivers.forEach(function (d) { d.license_number = ''; });
        copy.vehicles.forEach(function (v) { delete v._status; });
        delete copy.consent;
        sessionStorage.setItem(STORE, JSON.stringify(copy));
        if (flash && savedEl) {
          savedEl.classList.add('is-on');
          clearTimeout(savedTimer);
          savedTimer = setTimeout(function () { savedEl.classList.remove('is-on'); }, 1400);
        }
      } catch (e) { /* storage unavailable: the form still works */ }
    }, 300);
  }
  function clearDraft() { try { sessionStorage.removeItem(STORE); } catch (e) {} }

  function getPath(path) {
    var p = path.split('.');
    return state[p[0]] ? state[p[0]][p[1]] : undefined;
  }
  function setPath(path, val) {
    var p = path.split('.');
    if (p.length === 2 && state[p[0]]) state[p[0]][p[1]] = val;
    else if (p.length === 1) state[p[0]] = val;
  }

  function hasData() {
    var a = state.applicant;
    return !!(a.first || a.last || a.email || a.phone || state.address.street || state.vehicles.some(function (v) { return v.vin; }));
  }

  /* ------------------------------------------------------------------ */
  /* Static fields (You, Address, Coverage, consent)                     */
  /* ------------------------------------------------------------------ */
  function fillStatic() {
    $$('[name]', form).forEach(function (el) {
      if (el.closest('[data-item]') || el.name === 'website') return;
      var v = el.name === 'consent' ? state.consent : getPath(el.name);
      if (el.type === 'checkbox') el.checked = !!v;
      else if (el.type === 'radio') el.checked = el.value === v;
      else el.value = v == null ? '' : v;
    });
    syncCarrier();
    var today = new Date(), max = new Date(); max.setFullYear(max.getFullYear() + 1);
    var sd = form.elements['coverage.start_date'];
    sd.min = isoDate(today);
    sd.max = isoDate(max);
  }
  function isoDate(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }

  function syncCarrier() {
    var f = form.elements['coverage.current_carrier'].closest('.fld');
    f.hidden = state.coverage.currently_insured !== 'yes';
  }

  function valueOf(el) {
    if (el.type === 'checkbox') return el.checked;
    return el.value;
  }

  form.addEventListener('input', onField);
  form.addEventListener('change', onField);

  function onField(e) {
    var el = e.target;
    if (!el.name && !el.getAttribute('data-f')) return;
    if (el.type === 'radio' && !el.checked) return;
    var item = el.closest('[data-item]');
    if (item) {
      listField(el, item, e.type);
    } else if (el.name === 'consent') {
      state.consent = el.checked;
      setErr(el, '');
    } else if (el.name && el.name.indexOf('.') > 0) {
      setPath(el.name, valueOf(el));
      setErr(el, '');
      if (el.name === 'coverage.currently_insured') syncCarrier();
      if (el.name === 'address.zip') {
        el.value = el.value.replace(/\D/g, '').slice(0, 5);
        state.address.zip = el.value;
        if (el.value.length === 5) zipLookup(el.value);
      }
      if (el.name === 'address.city') autoAddr.city = null;
      if (el.name === 'address.state') autoAddr.state = null;
    }
    save(e.type === 'change');
  }

  /* ZIP → city & state (free public service; silently skipped if unavailable). */
  var zipReq = 0;
  function zipLookup(zip) {
    var wrap = form.elements['address.zip'].closest('.fld-wrap');
    var id = ++zipReq;
    wrap.classList.add('is-loading');
    fetch('https://api.zippopotam.us/us/' + zip)
      .then(function (r) { return r.ok ? r.json() : null; })
      .then(function (d) {
        if (id !== zipReq || !d || !d.places || !d.places[0]) return;
        var p = d.places[0], city = p['place name'], st = p['state abbreviation'];
        var a = state.address;
        if (!a.city || a.city === autoAddr.city) { a.city = city; autoAddr.city = city; form.elements['address.city'].value = city; setErr(form.elements['address.city'], ''); }
        if (!a.state || a.state === autoAddr.state) { a.state = st; autoAddr.state = st; form.elements['address.state'].value = st; setErr(form.elements['address.state'], ''); }
        save();
      })
      .catch(function () {})
      .then(function () { if (id === zipReq) wrap.classList.remove('is-loading'); });
  }

  /* ------------------------------------------------------------------ */
  /* Drivers & vehicles                                                  */
  /* ------------------------------------------------------------------ */
  function listEl(kind) { return $('[data-list="' + kind + '"]'); }

  function renderList(kind, animateIndex) {
    var list = listEl(kind);
    list.innerHTML = '';
    state[kind].forEach(function (item, i) {
      var node = tpl[kind].content.firstElementChild.cloneNode(true);
      node.setAttribute('data-i', i);
      $$('input[type=radio]', node).forEach(function (r) { r.name = kind + '-' + i + '-' + r.getAttribute('data-f'); });
      $$('[data-f]', node).forEach(function (el) {
        var v = item[el.getAttribute('data-f')];
        if (el.type === 'checkbox') el.checked = !!v;
        else if (el.type === 'radio') el.checked = el.value === v;
        else el.value = v == null ? '' : v;
      });
      if (i === animateIndex && !reduce) node.classList.add('is-new');
      list.appendChild(node);
      refreshCard(kind, node, i);
    });
    var add = $('[data-add="' + kind + '"]');
    var count = $('[data-count="' + kind + '"]');
    add.disabled = state[kind].length >= MAX[kind];
    count.textContent = state[kind].length + ' of ' + MAX[kind];
  }

  function cardOf(kind, i) { return listEl(kind).children[i]; }

  function refreshCard(kind, node, i) {
    var item = state[kind][i];
    var title = $('[data-title]', node), sub = $('[data-subtitle]', node), av = $('[data-avatar]', node);
    var rm = $('[data-remove]', node);
    if (kind === 'drivers') {
      var name = (item.first + ' ' + item.last).trim();
      title.textContent = name || 'Driver ' + (i + 1);
      sub.textContent = i === 0 ? 'Policyholder (you)' : (LABELS.relationship[item.relationship] || 'Additional driver');
      var initials = ((item.first[0] || '') + (item.last[0] || '')).toUpperCase();
      if (initials) { av.textContent = initials; av.classList.add('is-filled'); }
      else { av.innerHTML = avatarIcon.drivers; av.classList.remove('is-filled'); }
      rm.hidden = i === 0;
      $('.only-extra', node).hidden = i === 0;
      $('.show-us', node).hidden = item.id_type !== 'us';
      $('.show-foreign', node).hidden = item.id_type !== 'foreign';
    } else {
      var t = [item.year, item.make, item.model].filter(Boolean).join(' ');
      title.textContent = item.decoded && t ? t : 'Vehicle ' + (i + 1);
      sub.textContent = item.decoded ? ([item.trim, item.body].filter(Boolean).join(' · ') || 'Vehicle identified') : 'Enter the VIN to identify it';
      av.classList.toggle('is-ok', !!item.decoded);
      rm.hidden = state.vehicles.length === 1;
      $('[data-deduct]', node).hidden = !item.full_coverage;
      var cnt = $('[data-vincount]', node);
      cnt.textContent = item.vin.length + '/17';
      cnt.classList.toggle('is-full', item.vin.length === 17);
      renderVin(node, item);
    }
  }

  function listField(el, node, evtType) {
    var kind = node.parentElement.getAttribute('data-list');
    var i = +node.getAttribute('data-i');
    var item = state[kind][i];
    var f = el.getAttribute('data-f');
    var val = valueOf(el);

    if (f === 'vin') {
      var pos = el.selectionStart;
      var clean = String(val).toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 17);
      if (clean !== el.value) { el.value = clean; try { el.setSelectionRange(pos, pos); } catch (x) {} }
      if (clean !== item.vin) {
        item.vin = clean;
        item.decoded = false;
        item.year = item.make = item.model = item.trim = item.body = '';
        item._status = /[IOQ]/.test(clean) ? 'ioq' : '';
        queueDecode(item);
      }
    } else if (f === 'license_number') {
      var up = String(val).toUpperCase();
      if (up !== el.value) el.value = up;
      item[f] = up;
    } else {
      item[f] = val;
    }
    setErr(el, '');
    if (f === 'id_type') {
      var seg = el.closest('.seg');
      if (seg) seg.classList.remove('is-bad');
    }
    if (f === 'full_coverage' && val && !item.deductible) {
      item.deductible = '500';
      var r = $('input[data-f=deductible][value="500"]', node);
      if (r) r.checked = true;
    }
    if (kind === 'drivers' && i === 0 && (f === 'first' || f === 'last' || f === 'dob')) synced[f] = null;
    refreshCard(kind, node, i);
    if (evtType === 'change' && f === 'full_coverage' && val && !reduce) {
      var d = $('[data-deduct]', node);
      d.style.animation = 'none'; void d.offsetWidth; d.style.animation = '';
    }
  }

  root.addEventListener('click', function (e) {
    var add = e.target.closest('[data-add]');
    if (add) {
      var kind = add.getAttribute('data-add');
      if (state[kind].length >= MAX[kind]) return;
      state[kind].push(kind === 'drivers' ? newDriver('') : newVehicle());
      var idx = state[kind].length - 1;
      renderList(kind, idx);
      var card = cardOf(kind, idx);
      var first = card.querySelector('input:not([type=radio]):not([type=checkbox])');
      if (first) first.focus({ preventScroll: true });
      card.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      save(true);
      return;
    }
    var rm = e.target.closest('[data-remove]');
    if (rm) {
      var node = rm.closest('[data-item]');
      var k = node.parentElement.getAttribute('data-list');
      var i = +node.getAttribute('data-i');
      var done = function () {
        state[k].splice(i, 1);
        renderList(k);
        var target = cardOf(k, Math.max(0, i - 1));
        if (target) target.querySelector('input').focus({ preventScroll: true });
        save(true);
      };
      if (reduce || !node.animate) return done();
      node.style.overflow = 'hidden';
      node.animate([
        { opacity: 1, transform: 'none', height: node.offsetHeight + 'px', marginBottom: '0px' },
        { opacity: 0, transform: 'scale(.96)', height: '0px', paddingTop: '0px', paddingBottom: '0px', marginBottom: '-16px' }
      ], { duration: 320, easing: 'cubic-bezier(.4,0,.2,1)' }).onfinish = done;
      return;
    }
    var go = e.target.closest('[data-goto]');
    if (go) { goTo(+go.getAttribute('data-goto'), -1); return; }
    var stepBtn = e.target.closest('[data-qw-steps] button');
    if (stepBtn) {
      var target = +stepBtn.parentElement.getAttribute('data-s');
      if (target < cur) goTo(target, -1);
      else if (target > cur && target <= furthest) {
        for (var s = cur; s < target; s++) { if (!validate(s)) { if (s !== cur) goTo(s, 1); validate(s); return; } }
        goTo(target, 1);
      }
    }
  });

  /* ------------------------------------------------------------------ */
  /* VIN lookup                                                          */
  /* ------------------------------------------------------------------ */
  var vinCache = {};

  function vinCheckDigitOk(vin) {
    var map = { A: 1, B: 2, C: 3, D: 4, E: 5, F: 6, G: 7, H: 8, J: 1, K: 2, L: 3, M: 4, N: 5, P: 7, R: 9, S: 2, T: 3, U: 4, V: 5, W: 6, X: 7, Y: 8, Z: 9 };
    var w = [8, 7, 6, 5, 4, 3, 2, 10, 0, 9, 8, 7, 6, 5, 4, 3, 2], sum = 0;
    for (var i = 0; i < 17; i++) { var c = vin[i]; sum += (/\d/.test(c) ? +c : map[c]) * w[i]; }
    var r = sum % 11;
    return vin[8] === (r === 10 ? 'X' : String(r));
  }

  function niceMake(m) {
    var up = ['BMW', 'GMC', 'MINI', 'MG', 'VW', 'BYD'];
    m = (m || '').trim();
    if (up.indexOf(m.toUpperCase()) > -1) return m.toUpperCase();
    return m.toLowerCase().replace(/(^|[\s-])\S/g, function (s) { return s.toUpperCase(); });
  }

  function fromNhtsa(json, vin) {
    var r = json && json.Results && json.Results[0];
    if (!r) return { ok: false, error: 'unavailable' };
    return {
      ok: !!(r.Make && r.ModelYear), vin: vin, year: r.ModelYear || '', make: niceMake(r.Make), model: (r.Model || '').trim(),
      trim: (r.Trim || '').trim(), body: (r.BodyClass || '').trim(), check_digit_ok: vinCheckDigitOk(vin)
    };
  }

  function lookupVin(vin) {
    if (vinCache[vin]) return Promise.resolve(vinCache[vin]);
    return fetch(API + 'vin.php?vin=' + vin, { headers: { Accept: 'application/json' } })
      .then(function (r) {
        if (r.status === 422) return { ok: false, error: 'invalid' };
        if (!r.ok) throw new Error('proxy');
        return r.json();
      })
      .catch(function () {
        // Our server couldn't reach the lookup service: ask NHTSA directly.
        return fetch('https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVinValues/' + vin + '?format=json')
          .then(function (r) { return r.json(); })
          .then(function (j) { return fromNhtsa(j, vin); })
          .catch(function () { return { ok: false, error: 'unavailable' }; });
      })
      .then(function (res) { if (res && res.ok) vinCache[vin] = res; return res; });
  }

  var vinTimers = new WeakMap();
  function queueDecode(item) {
    clearTimeout(vinTimers.get(item));
    if (item.vin.length !== 17) return;
    if (!VIN_RE.test(item.vin)) { item._status = 'ioq'; return; }
    item._status = 'loading';
    vinTimers.set(item, setTimeout(function () {
      var vin = item.vin;
      lookupVin(vin).then(function (res) {
        if (item.vin !== vin) return;
        if (res && res.ok) {
          item.year = res.year; item.make = res.make; item.model = res.model; item.trim = res.trim; item.body = res.body;
          item.decoded = true;
          item._status = 'ok';
        } else {
          item.decoded = false;
          item._status = res && res.error === 'unavailable' ? 'unavailable' : 'notfound';
        }
        var i = state.vehicles.indexOf(item);
        var node = i > -1 ? cardOf('vehicles', i) : null;
        if (node) refreshCard('vehicles', node, i);
        save();
      });
    }, 250));
  }

  function renderVin(node, item) {
    var box = $('[data-vinres]', node);
    var s = item._status || (item.decoded ? 'ok' : '');
    var html = '';
    if (s === 'loading') {
      html = '<div class="vr vr-loading"><span class="btn-spin"></span>Looking up your vehicle…</div>';
    } else if (s === 'ok') {
      var t = [item.year, item.make, item.model].filter(Boolean).join(' ');
      var d = [item.trim, item.body].filter(Boolean).join(' · ');
      html = '<div class="vr vr-ok"><span class="vr-ic">' + icon('check') + '</span><div><b>' + esc(t) + '</b>' + (d ? '<small>' + esc(d) + '</small>' : '') + '</div></div>';
    } else if (s === 'notfound') {
      html = '<div class="vr vr-warn"><span class="vr-ic">' + icon('alert') + '</span><span>We couldn\'t identify this VIN. Please double-check it. You can still continue and your agent will verify it.</span></div>';
    } else if (s === 'unavailable') {
      html = '<div class="vr vr-warn"><span class="vr-ic">' + icon('info') + '</span><span>Vehicle lookup is busy right now. You can continue and your agent will verify the VIN.</span></div>';
    } else if (s === 'ioq') {
      html = '<div class="vr vr-warn"><span class="vr-ic">' + icon('info') + '</span><span>VINs never contain the letters I, O or Q. These are usually the numbers 1 and 0.</span></div>';
    }
    if (box.getAttribute('data-s') !== s + '|' + item.vin) {
      box.innerHTML = html;
      box.setAttribute('data-s', s + '|' + item.vin);
    }
  }

  /* ------------------------------------------------------------------ */
  /* Validation                                                          */
  /* ------------------------------------------------------------------ */
  function parseDate(s) {
    var m = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(s || '');
    if (!m) return null;
    var d = new Date(+m[3], +m[1] - 1, +m[2]);
    return d.getMonth() === +m[1] - 1 && d.getDate() === +m[2] ? d : null;
  }
  function age(d) {
    var t = new Date(), a = t.getFullYear() - d.getFullYear();
    if (t.getMonth() < d.getMonth() || (t.getMonth() === d.getMonth() && t.getDate() < d.getDate())) a--;
    return a;
  }

  function errBox(el) {
    if (el.name === 'consent') return $('.consent-e');
    var wrap = el.closest('.fld, .seg-fld');
    return wrap ? wrap.querySelector('.fld-e') : null;
  }
  function setErr(el, msg) {
    if (!el) return;
    var box = errBox(el);
    if (el.type === 'radio') {
      var seg = el.closest('.seg, .opt-grid');
      if (seg) seg.classList.toggle('is-bad', !!msg);
    } else if (el.name === 'consent') {
      el.closest('.consent').classList.toggle('is-bad', !!msg);
    } else {
      el.classList.toggle('is-bad', !!msg);
    }
    if (msg) el.setAttribute('aria-invalid', 'true'); else el.removeAttribute('aria-invalid');
    if (box) { box.textContent = msg || ''; box.classList.toggle('is-on', !!msg); }
  }

  function validate(step) {
    var errs = [];
    var F = form.elements;
    var need = function (el, ok, msg) { if (!ok) errs.push([el, msg]); else setErr(el, ''); };

    if (step === 0) {
      var a = state.applicant, dob = parseDate(a.dob);
      need(F['applicant.first'], NAME_RE.test(a.first.trim()), 'Enter your first name.');
      need(F['applicant.last'], NAME_RE.test(a.last.trim()), 'Enter your last name.');
      need(F['applicant.dob'], dob && age(dob) >= 16 && age(dob) <= 110, a.dob ? 'Enter a real date as MM/DD/YYYY (you must be 16 or older).' : 'Enter your date of birth.');
      need(F['applicant.phone'], a.phone.replace(/\D/g, '').length === 10, 'Enter a 10-digit phone number.');
      need(F['applicant.email'], /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(a.email.trim()), 'Enter a valid email address.');
    }
    if (step === 1) {
      var ad = state.address;
      need(F['address.zip'], /^\d{5}$/.test(ad.zip), 'Enter a 5-digit ZIP code.');
      need(F['address.street'], ad.street.trim().length >= 3, 'Enter your street address.');
      need(F['address.city'], ad.city.trim() !== '', 'Enter your city.');
      need(F['address.state'], ad.state !== '', 'Choose your state.');
    }
    if (step === 2) {
      state.drivers.forEach(function (d, i) {
        var n = cardOf('drivers', i), q = function (f) { return n.querySelector('[data-f="' + f + '"]'); };
        var dob = parseDate(d.dob);
        need(q('first'), NAME_RE.test(d.first.trim()), 'Enter a first name.');
        need(q('last'), NAME_RE.test(d.last.trim()), 'Enter a last name.');
        need(q('dob'), dob && age(dob) >= 14 && age(dob) <= 110, d.dob ? 'Enter a real date as MM/DD/YYYY.' : 'Enter a date of birth.');
        if (i > 0) need(q('relationship'), !!d.relationship, 'Choose how this driver is related to you.');
        need(n.querySelector('[data-f=id_type]'), d.id_type === 'us' || d.id_type === 'foreign', 'Choose an ID type.');
        if (d.id_type === 'us') need(q('license_state'), !!d.license_state, 'Choose the state that issued the license.');
        if (d.id_type === 'foreign') need(q('license_country'), d.license_country.trim() !== '', 'Enter the country that issued the license.');
        need(q('license_number'), /^[A-Z0-9\-*]{4,25}$/.test(d.license_number.replace(/\s+/g, '')), d.license_number ? 'Use letters and numbers only (4–25 characters).' : 'Enter the license number.');
      });
    }
    if (step === 3) {
      var seen = {};
      state.vehicles.forEach(function (v, i) {
        var el = cardOf('vehicles', i).querySelector('[data-f=vin]');
        var msg = '';
        if (!v.vin) msg = 'Enter the VIN.';
        else if (v.vin.length !== 17) msg = 'A VIN has exactly 17 characters (you have ' + v.vin.length + ').';
        else if (!VIN_RE.test(v.vin)) msg = 'VINs never contain I, O or Q. Check for 1 and 0.';
        else if (seen[v.vin]) msg = 'This VIN is already listed above.';
        seen[v.vin] = true;
        need(el, !msg, msg);
      });
    }
    if (step === 4) {
      var c = state.coverage;
      need(F['coverage.liability'][0], !!c.liability, 'Choose a liability level.');
      if (c.start_date) {
        var sd = new Date(c.start_date + 'T00:00:00'), today = new Date(); today.setHours(0, 0, 0, 0);
        var days = (sd - today) / 864e5;
        need(F['coverage.start_date'], days >= 0 && days <= 366, 'Choose a date within the next 12 months.');
      }
    }
    if (step === 5) {
      need(F.consent, state.consent, 'Please check the box so we can contact you about your quote.');
    }

    errs.forEach(function (x) { setErr(x[0], x[1]); });
    if (errs.length) {
      var first = errs[0][0];
      first.focus({ preventScroll: true });
      first.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      var s = steps[step];
      s.classList.remove('is-shake'); void s.offsetWidth; s.classList.add('is-shake');
      return false;
    }
    return true;
  }

  /* ------------------------------------------------------------------ */
  /* Navigation                                                          */
  /* ------------------------------------------------------------------ */
  function syncDriverOne() {
    var d = state.drivers[0], a = state.applicant;
    ['first', 'last', 'dob'].forEach(function (f) {
      if (!d[f] || d[f] === synced[f]) { d[f] = a[f]; synced[f] = a[f]; }
    });
  }

  function goTo(n, dir, opts) {
    opts = opts || {};
    steps[cur].hidden = true;
    steps[cur].classList.remove('is-shake');
    cur = n;
    furthest = Math.max(furthest, cur);
    var s = steps[cur];
    if (cur === 2) { syncDriverOne(); renderList('drivers'); }
    if (cur === 3) renderList('vehicles');
    if (cur === 5) { buildReview(); setupTurnstile(); alertEl.hidden = true; }
    s.hidden = false;
    s.classList.toggle('is-back', dir < 0);
    if (!reduce) { s.style.animation = 'none'; void s.offsetWidth; s.style.animation = ''; }
    updateChrome();
    if (!opts.silent) {
      if (mode === 'modal') viewport.scrollTop = 0;
      else {
        var top = root.getBoundingClientRect().top + window.scrollY - 96;
        if (window.scrollY > top) window.scrollTo({ top: top, behavior: reduce ? 'auto' : 'smooth' });
      }
      var h = s.querySelector('h3');
      h.setAttribute('tabindex', '-1');
      h.focus({ preventScroll: true });
    }
    save();
  }

  function updateChrome() {
    stepItems.forEach(function (li, i) {
      li.classList.toggle('is-current', i === cur);
      li.classList.toggle('is-done', i !== cur && i <= furthest);
      var b = li.querySelector('button');
      b.disabled = i > furthest || i === cur;
      if (i === cur) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current');
    });
    bar.style.width = ((cur + 1) / steps.length * 100) + '%';
    stepLabel.textContent = 'Step ' + (cur + 1) + ' of ' + steps.length + ' · ' + STEP_NAMES[cur];
    backBtn.hidden = cur === 0;
    nextLabel.textContent = cur === 5 ? 'Send my request' : cur === 4 ? 'Review my quote' : 'Continue';
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (busy) return;
    if (!validate(cur)) return;
    if (cur < steps.length - 1) goTo(cur + 1, 1);
    else send();
  });
  backBtn.addEventListener('click', function () { if (cur > 0) goTo(cur - 1, -1); });

  /* ------------------------------------------------------------------ */
  /* Review                                                              */
  /* ------------------------------------------------------------------ */
  function section(title, ic, step, body) {
    return '<section class="rv-sec"><header class="rv-h">' + icon(ic) + '<h4>' + esc(title) + '</h4>' +
      '<button type="button" class="rv-edit" data-goto="' + step + '">' + icon('edit') + 'Edit</button></header>' +
      '<div class="rv-b">' + body + '</div></section>';
  }
  function item(b, lines, tags) {
    return '<div class="rv-item"><b>' + b + '</b>' + lines.filter(Boolean).map(function (l) { return '<span>' + l + '</span>'; }).join('') +
      (tags ? '<div class="rv-tags">' + tags + '</div>' : '') + '</div>';
  }
  function tag(t, on) { return '<em' + (on === false ? ' class="off"' : '') + '>' + esc(t) + '</em>'; }

  function buildReview() {
    var a = state.applicant, ad = state.address, c = state.coverage;
    var html = '';
    html += section('About you', 'user', 0, item(esc(a.first + ' ' + a.last), [
      'Born ' + esc(a.dob), esc(a.phone) + ' · ' + esc(a.email), 'Prefers: ' + esc(LABELS.contact[a.contact_pref] || '')
    ]));
    html += section('Address', 'pin', 1, item(esc(ad.street + (ad.unit ? ', ' + ad.unit : '')), [
      esc(ad.city + ', ' + (LABELS.state[ad.state] || ad.state) + ' ' + ad.zip)
    ]));
    html += section('Drivers (' + state.drivers.length + ')', 'users', 2, state.drivers.map(function (d, i) {
      var where = d.id_type === 'us' ? (LABELS.state[d.license_state] || d.license_state) : d.license_country;
      var lic = d.license_number.replace(/\s+/g, '');
      return item(esc(d.first + ' ' + d.last) + ' <span class="muted">· ' + esc(i === 0 ? 'You' : LABELS.relationship[d.relationship] || '') + '</span>', [
        'Born ' + esc(d.dob),
        esc(d.id_type === 'us' ? 'US license' : 'Foreign license') + ' · ' + esc(where) + ' · ' + esc('••••' + lic.slice(-4))
      ]);
    }).join(''));
    html += section('Vehicles (' + state.vehicles.length + ')', 'car', 3, state.vehicles.map(function (v) {
      var t = v.decoded ? [v.year, v.make, v.model, v.trim].filter(Boolean).join(' ') : 'Vehicle (VIN only)';
      var tags = (v.full_coverage
        ? tag('Full coverage' + (v.deductible && v.deductible !== 'unsure' ? ' · ' + (LABELS.deductible[v.deductible] || '') + ' deductible' : ''))
        : tag('Liability only', false)) +
        tag('Roadside', v.roadside) + tag('Rental', v.rental);
      return item(esc(t), ['<span class="mono">VIN ' + esc(v.vin) + '</span>'], tags);
    }).join(''));
    var extra = [];
    if (c.start_date) extra.push('Start date: ' + esc(new Date(c.start_date + 'T00:00:00').toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })));
    if (c.currently_insured) extra.push('Currently insured: ' + (c.currently_insured === 'yes' ? 'Yes' + (c.current_carrier ? ' (' + esc(c.current_carrier) + ')' : '') : 'No'));
    if (c.comments) extra.push('Notes: ' + esc(c.comments));
    html += section('Coverage', 'shield', 4, item('Liability: ' + esc(LABELS.liability[c.liability] || ''), extra,
      tag('Personal injury protection', c.pip) + tag('Uninsured motorist', c.um)));
    reviewEl.innerHTML = html;
  }

  /* ------------------------------------------------------------------ */
  /* Submit                                                              */
  /* ------------------------------------------------------------------ */
  function setupTurnstile() {
    var key = root.getAttribute('data-turnstile');
    if (!key || tsId !== null || !window.Site) return;
    window.Site.renderTurnstile($('[data-turnstile-box]'), key, function (id) { tsId = id; });
  }

  function payload() {
    var p = JSON.parse(JSON.stringify(state));
    p.vehicles.forEach(function (v) { delete v._status; });
    delete p.step; delete p.furthest;
    p.token = root.getAttribute('data-token');
    p.website = form.elements.website.value;
    p.turnstile = tsId !== null && window.turnstile ? window.turnstile.getResponse(tsId) : '';
    return p;
  }

  function showServerErrors(errors) {
    var firstStep = 99;
    Object.keys(errors).forEach(function (path) {
      var parts = path.split('.'), step = STEP_OF[parts[0]];
      if (step === undefined) return;
      firstStep = Math.min(firstStep, step);
    });
    if (firstStep === 99) return;
    goTo(firstStep, -1, { silent: true });
    Object.keys(errors).forEach(function (path) {
      var parts = path.split('.'), el = null;
      if (STEP_OF[parts[0]] !== firstStep) return;
      if (parts[0] === 'drivers' || parts[0] === 'vehicles') {
        var card = cardOf(parts[0], +parts[1]);
        el = card && card.querySelector('[data-f="' + parts[2] + '"]');
      } else if (parts[0] === 'consent') {
        el = form.elements.consent;
      } else {
        el = form.elements[path];
        if (el && el.length && !el.tagName) el = el[0];
      }
      if (el) setErr(el, errors[path]);
    });
    toast('Please fix the highlighted fields.');
  }

  function send() {
    busy = true;
    nextBtn.classList.add('is-loading');
    alertEl.hidden = true;
    fetch(API + 'quote.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload())
    })
      .then(function (r) { return r.json().catch(function () { return { ok: false, error: 'Unexpected server response.' }; }); })
      .then(function (res) {
        if (res.ok) return showDone(res.ref);
        if (res.errors) return showServerErrors(res.errors);
        alertEl.textContent = res.error || 'Something went wrong. Please try again.';
        alertEl.hidden = false;
        if (tsId !== null && window.turnstile) window.turnstile.reset(tsId);
      })
      .catch(function () {
        alertEl.textContent = 'We couldn\'t reach the server. Check your connection and try again. Your answers are saved.';
        alertEl.hidden = false;
      })
      .then(function () { busy = false; nextBtn.classList.remove('is-loading'); });
  }

  function showDone(ref) {
    $('[data-qw-ref]').textContent = ref;
    $('[data-qw-first]').textContent = state.applicant.first;
    form.hidden = true;
    $('[data-qw-steps]').hidden = true;
    bar.parentElement.hidden = true;
    stepLabel.textContent = 'Request sent';
    doneEl.hidden = false;
    doneEl.focus({ preventScroll: true });
    if (mode === 'page') window.scrollTo({ top: root.getBoundingClientRect().top + window.scrollY - 96, behavior: reduce ? 'auto' : 'smooth' });
    clearDraft();
    confetti($('[data-confetti]'));
  }

  function reset() {
    state = fresh();
    cur = 0; furthest = 0; synced = {}; autoAddr = {};
    form.hidden = false;
    doneEl.hidden = true;
    $('[data-qw-steps]').hidden = false;
    bar.parentElement.hidden = false;
    steps.forEach(function (s, i) { s.hidden = i !== 0; });
    $$('.is-bad', form).forEach(function (el) { el.classList.remove('is-bad'); });
    $$('.fld-e.is-on', form).forEach(function (el) { el.classList.remove('is-on'); el.textContent = ''; });
    fillStatic();
    updateChrome();
  }

  $('[data-qw-finish]').addEventListener('click', function () {
    if (mode === 'modal') { close(); setTimeout(reset, 300); }
    else { reset(); goTo(0, -1); }
  });

  /* Lightweight confetti burst for the success screen. */
  function confetti(canvas) {
    if (reduce || !canvas || !canvas.getContext) return;
    var ctx = canvas.getContext('2d'), dpr = window.devicePixelRatio || 1;
    var w = canvas.offsetWidth, h = canvas.offsetHeight;
    canvas.width = w * dpr; canvas.height = h * dpr; ctx.scale(dpr, dpr);
    var colors = ['#3F55C8', '#FFB547', '#0D1640', '#8ED3EC', '#F29A2E', '#7C6CF2'];
    var parts = [];
    for (var i = 0; i < 140; i++) {
      parts.push({
        x: w / 2, y: h * 0.22, r: 4 + Math.random() * 5,
        vx: (Math.random() - 0.5) * 14, vy: -Math.random() * 13 - 4,
        rot: Math.random() * 6, vr: (Math.random() - 0.5) * 0.4,
        c: colors[i % colors.length], shape: i % 3
      });
    }
    var start = performance.now();
    (function frame(now) {
      var t = now - start;
      ctx.clearRect(0, 0, w, h);
      parts.forEach(function (p) {
        p.vy += 0.38; p.vx *= 0.985; p.x += p.vx; p.y += p.vy; p.rot += p.vr;
        ctx.save();
        ctx.globalAlpha = Math.max(0, 1 - t / 2600);
        ctx.translate(p.x, p.y); ctx.rotate(p.rot); ctx.fillStyle = p.c;
        if (p.shape === 0) ctx.fillRect(-p.r, -p.r / 2, p.r * 2, p.r);
        else if (p.shape === 1) { ctx.beginPath(); ctx.arc(0, 0, p.r / 1.6, 0, 6.3); ctx.fill(); }
        else { ctx.beginPath(); ctx.moveTo(0, -p.r); ctx.lineTo(p.r, p.r); ctx.lineTo(-p.r, p.r); ctx.fill(); }
        ctx.restore();
      });
      if (t < 2700) requestAnimationFrame(frame); else ctx.clearRect(0, 0, w, h);
    })(start);
  }

  /* ------------------------------------------------------------------ */
  /* Opening & closing                                                   */
  /* ------------------------------------------------------------------ */
  var isOpen = false;

  function prefill(opts) {
    if (opts && opts.zip && /^\d{5}$/.test(opts.zip)) {
      state.address.zip = opts.zip;
      form.elements['address.zip'].value = opts.zip;
      zipLookup(opts.zip);
    }
  }

  function open(opts) {
    if (mode !== 'modal') {
      prefill(opts);
      root.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'start' });
      return;
    }
    if (isOpen) return;
    isOpen = true;
    lastFocus = document.activeElement;
    if (!doneEl.hidden) reset();
    prefill(opts);
    overlay.hidden = false;
    overlay.classList.remove('is-closing');
    document.body.classList.add('is-locked');
    if (location.hash !== '#quote') history.pushState({ qw: true }, '', '#quote');
    goTo(cur, 1, { silent: true });
    if (cur > 0 && hasData()) toast('Welcome back! We saved your progress.');
    setTimeout(function () {
      var first = steps[cur].querySelector('input:not([type=radio]):not([type=checkbox]), select');
      if (first && window.matchMedia('(pointer: fine)').matches) first.focus({ preventScroll: true });
      else root.querySelector('.qw-close').focus({ preventScroll: true });
    }, reduce ? 0 : 320);
  }

  function close(fromHistory) {
    if (!isOpen) return;
    if (!fromHistory && history.state && history.state.qw) { history.back(); return; }
    isOpen = false;
    if (location.hash === '#quote') history.replaceState(null, '', location.pathname + location.search);
    overlay.classList.add('is-closing');
    setTimeout(function () {
      overlay.hidden = true;
      overlay.classList.remove('is-closing');
      document.body.classList.remove('is-locked');
      if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }, reduce ? 0 : 240);
    if (doneEl.hidden && hasData()) toast('Your progress is saved. Pick up where you left off any time.');
  }

  window.addEventListener('popstate', function () {
    if (isOpen && location.hash !== '#quote') close(true);
    else if (!isOpen && location.hash === '#quote' && mode === 'modal') open();
  });

  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-quote]');
    if (!t || e.defaultPrevented || e.metaKey || e.ctrlKey) return;
    e.preventDefault();
    open();
  });

  if (mode === 'modal') {
    root.querySelector('[data-qclose]').addEventListener('click', function () { close(); });
    overlay.addEventListener('mousedown', function (e) { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function (e) {
      if (!isOpen) return;
      if (e.key === 'Escape') { e.preventDefault(); close(); return; }
      if (e.key === 'Tab') {
        // Keep keyboard focus inside the dialog.
        var f = $$('button:not([disabled]):not([hidden]), input:not([type=hidden]):not([tabindex="-1"]), select, textarea, a[href], [tabindex="0"]', overlay)
          .filter(function (el) { return el.offsetParent !== null; });
        if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });
  }

  /* ------------------------------------------------------------------ */
  /* Init                                                                */
  /* ------------------------------------------------------------------ */
  fillStatic();
  steps.forEach(function (s, i) { s.hidden = i !== cur; });
  if (cur === 2) renderList('drivers');
  if (cur === 3) renderList('vehicles');
  if (cur === 5) buildReview();
  updateChrome();
  if (mode === 'modal' && location.hash === '#quote') open();

  window.QuoteWizard = { open: open, close: close };
})();
