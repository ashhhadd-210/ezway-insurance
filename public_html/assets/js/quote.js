/* Multi-step quote form (English & Spanish).
   Flows: auto     → Service → You → Address → Drivers (+) → Vehicles (+, VIN lookup) → Coverage → Review
          details  → Service → You → Address → Details (service questions) → Review
   Works as a pop-up on every page and inline on /quote. */
(function () {
  'use strict';

  var root = document.querySelector('[data-qw]');
  if (!root) return;

  /* ------------------------------------------------------------------ */
  /* Elements, text & constants                                          */
  /* ------------------------------------------------------------------ */
  var $ = function (sel, el) { return (el || root).querySelector(sel); };
  var $$ = function (sel, el) { return Array.prototype.slice.call((el || root).querySelectorAll(sel)); };

  var I18N = {};
  try { I18N = JSON.parse(document.getElementById('i18n').textContent); } catch (e) {}
  function T(key, vars) {
    var v = key.split('.').reduce(function (o, k) { return o && o[k]; }, I18N);
    v = v == null ? key : String(v);
    if (vars) Object.keys(vars).forEach(function (k) { v = v.replace('{' + k + '}', vars[k]); });
    return v;
  }

  var mode = root.getAttribute('data-mode');
  var LANG = root.getAttribute('data-lang') || 'en';
  var overlay = document.querySelector('[data-qoverlay]');
  var form = $('[data-qw-form]');
  var viewport = $('[data-qw-viewport]');
  var stepsEl = $('[data-qw-steps]');
  var bar = $('[data-qw-bar]');
  var stepLabel = $('[data-qw-steplabel]');
  var nextBtn = $('[data-next]');
  var nextLabel = $('[data-next-label]');
  var backBtn = $('[data-back]');
  var savedEl = $('[data-saved]');
  var doneEl = $('[data-qw-done]');
  var alertEl = $('[data-qw-alert]');
  var reviewEl = $('[data-review]');
  var liabilityEl = $('[data-liability]');
  var API = root.getAttribute('data-api');
  var MAX = { drivers: +root.getAttribute('data-max-drivers') || 8, vehicles: +root.getAttribute('data-max-vehicles') || 6 };
  var SERVICE_FLOWS = JSON.parse(root.getAttribute('data-flows') || '{}');
  var LIAB_DEFAULTS = JSON.parse(root.getAttribute('data-liability-defaults') || '{}');
  var LIAB_SETS = JSON.parse(liabilityEl.getAttribute('data-sets') || '{}');
  var FLOWS = {
    auto: ['service', 'you', 'address', 'drivers', 'vehicles', 'coverage', 'review'],
    details: ['service', 'you', 'address', 'details', 'review']
  };
  var STORE = 'qw-draft-v2';
  var VIN_RE = /^[A-HJ-NPR-Z0-9]{17}$/;
  var NAME_RE = /^[\p{L}][\p{L}\p{M} .'\-]{0,59}$/u;
  var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var toast = function (m) { if (window.Site) window.Site.toast(m); };

  var sections = {};
  $$('.qw-step').forEach(function (s) { sections[s.getAttribute('data-step')] = s; });

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
    service: radioMap('input[name="service"]'),
    state: optionMap(form.elements['address.state']),
    relationship: Object.assign({ self: T('policyholder') }, optionMap(tpl.drivers.content.querySelector('[data-f=relationship]'))),
    idType: radioMap('input[data-f=id_type]', tpl.drivers.content),
    idKind: optionMap(tpl.drivers.content.querySelector('[data-f=id_kind]')),
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
    return { first: '', last: '', dob: '', relationship: rel || '', id_type: 'us', id_kind: '', license_state: '', license_country: '', license_number: '' };
  }
  function newVehicle() {
    return { vin: '', year: '', make: '', model: '', trim: '', body: '', decoded: false, full_coverage: false, deductible: '500', lien: '', lienholder: '', roadside: false, rental: false };
  }
  function fresh() {
    return {
      service: '',
      applicant: { first: '', last: '', dob: '', phone: '', email: '', contact_pref: 'phone', company: '', business_type: '' },
      address: { zip: '', street: '', unit: '', city: '', state: '' },
      drivers: [newDriver('self')],
      vehicles: [newVehicle()],
      coverage: { liability: '', pip: false, um: false, start_date: '', currently_insured: '', current_carrier: '', comments: '' },
      details: {},
      consent: false,
      step: 'service',
      furthest: 0
    };
  }

  var state = load() || fresh();
  var cur = Math.max(0, flow().indexOf(state.step || 'service'));
  var furthest = Math.max(cur, state.furthest || 0);
  var busy = false;
  var lastFocus = null;
  var tsId = null;
  var synced = {};          // values copied from "You" into driver 1, so later edits keep them in step
  var autoAddr = {};        // city/state filled from the ZIP, so we can update them if the ZIP changes

  function flowName(svc) { return SERVICE_FLOWS[svc || state.service] || 'auto'; }
  function flow() { return FLOWS[flowName()]; }
  function stepId() { return flow()[cur]; }
  function isAuto() { return flowName() === 'auto'; }

  function load() {
    try {
      var raw = sessionStorage.getItem(STORE);
      if (!raw) return null;
      var d = JSON.parse(raw), base = fresh();
      ['applicant', 'address', 'coverage'].forEach(function (k) { base[k] = Object.assign(base[k], d[k] || {}); });
      if (Array.isArray(d.drivers) && d.drivers.length) base.drivers = d.drivers.slice(0, 8).map(function (x, i) { return Object.assign(newDriver(i ? '' : 'self'), x); });
      if (Array.isArray(d.vehicles) && d.vehicles.length) base.vehicles = d.vehicles.slice(0, 6).map(function (x) { return Object.assign(newVehicle(), x); });
      if (d.details && typeof d.details === 'object') base.details = d.details;
      if (SERVICE_FLOWS[d.service]) base.service = d.service;
      base.step = d.step || 'service';
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
        copy.step = stepId();
        copy.furthest = furthest;
        // Never keep ID numbers in the browser.
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
    if (p[0] === 'details') return (state.details[p[1]] || {})[p[2]];
    return state[p[0]] ? state[p[0]][p[1]] : undefined;
  }
  function setPath(path, val) {
    var p = path.split('.');
    if (p[0] === 'details') {
      state.details[p[1]] = state.details[p[1]] || {};
      state.details[p[1]][p[2]] = val;
    } else if (p.length === 2 && state[p[0]]) state[p[0]][p[1]] = val;
  }

  function hasData() {
    var a = state.applicant;
    return !!(state.service && (a.first || a.last || a.email || a.phone || state.address.street));
  }

  /* ------------------------------------------------------------------ */
  /* Service-dependent parts of the form                                 */
  /* ------------------------------------------------------------------ */
  function applyService() {
    var svc = state.service, fl = flowName();
    $$('[data-only], [data-flow], [data-not]').forEach(function (el) {
      var only = el.getAttribute('data-only'), fw = el.getAttribute('data-flow'), not = el.getAttribute('data-not');
      var show = (!only || only.split(' ').indexOf(svc) > -1) && (!fw || fw === fl) && (!not || not.split(' ').indexOf(svc) === -1);
      el.hidden = !show;
    });
    $$('[data-details]').forEach(function (el) { el.hidden = el.getAttribute('data-details') !== svc; });
    var streetOpt = $('[data-street-opt]');
    if (streetOpt) streetOpt.hidden = streetNeeded();
    if (isAuto()) renderLiability();
    renderStepper();
  }

  function streetNeeded() { return isAuto() || state.service === 'home'; }

  function renderLiability() {
    var set = LIAB_SETS[state.service] || LIAB_SETS.personal_auto || [];
    if (!set.some(function (o) { return o.val === state.coverage.liability; })) {
      state.coverage.liability = LIAB_DEFAULTS[state.service] || (set[0] && set[0].val) || '';
    }
    if (liabilityEl.getAttribute('data-for') === state.service) {
      $$('input', liabilityEl).forEach(function (r) { r.checked = r.value === state.coverage.liability; });
      return;
    }
    liabilityEl.setAttribute('data-for', state.service);
    liabilityEl.innerHTML = set.map(function (o) {
      return '<label class="opt"><input type="radio" name="coverage.liability" value="' + esc(o.val) + '"' + (o.val === state.coverage.liability ? ' checked' : '') + '>' +
        '<span class="opt-body"><span class="opt-radio" aria-hidden="true"></span><b>' + esc(o.label) + '</b><small>' + esc(o.desc) + '</small>' +
        (o.tag ? '<em class="opt-tag">' + esc(o.tag) + '</em>' : '') + '</span></label>';
    }).join('');
  }

  /* ------------------------------------------------------------------ */
  /* Static fields                                                       */
  /* ------------------------------------------------------------------ */
  function isStatic(el) { return el.name && !el.closest('[data-item]') && el.name !== 'website'; }

  function fillStatic() {
    $$('[name]', form).forEach(function (el) {
      if (!isStatic(el)) return;
      var v = el.name === 'consent' ? state.consent : el.name === 'service' ? state.service : getPath(el.name);
      if (el.type === 'checkbox') el.checked = Array.isArray(v) ? v.indexOf(el.value) > -1 : !!v;
      else if (el.type === 'radio') el.checked = el.value === v;
      else el.value = v == null ? '' : v;
    });
    syncCarrier();
    var today = new Date(), max = new Date(); max.setFullYear(max.getFullYear() + 1);
    var sd = form.elements['coverage.start_date'];
    sd.min = isoDate(today);
    sd.max = isoDate(max);
    $$('[data-vin-field]').forEach(function (el) { if (el.value.length === 17) detailVin(el); });
  }
  function isoDate(d) { return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0'); }

  function syncCarrier() {
    form.elements['coverage.current_carrier'].closest('.fld').hidden = state.coverage.currently_insured !== 'yes';
  }

  function valueOf(el) {
    if (el.type === 'checkbox' && el.name && el.name.indexOf('details.') === 0) {
      return $$('input[type=checkbox]', form).filter(function (c) { return c.name === el.name && c.checked; }).map(function (c) { return c.value; });
    }
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
    } else if (el.name === 'service') {
      chooseService(el.value, e.type === 'change');
    } else if (el.name === 'consent') {
      state.consent = el.checked;
      setErr(el, '');
    } else if (el.name && el.name.indexOf('.') > 0) {
      if (el.hasAttribute('data-vin-field')) {
        el.value = el.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 17);
        detailVin(el);
      }
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

  function chooseService(svc, advance) {
    if (!SERVICE_FLOWS[svc]) return;
    var changed = state.service !== svc;
    state.service = svc;
    setErr(form.elements.service[0], '');
    if (changed) {
      furthest = 0;
      applyService();
      updateChrome();
    }
    // Picking a card is the whole step, so move on automatically.
    if (advance && stepId() === 'service') setTimeout(function () { goTo(1, 1); }, reduce ? 0 : 220);
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
    add.disabled = state[kind].length >= MAX[kind];
    $('[data-count="' + kind + '"]').textContent = state[kind].length + ' ' + T('of') + ' ' + MAX[kind];
  }

  function cardOf(kind, i) { return listEl(kind).children[i]; }

  function refreshCard(kind, node, i) {
    var item = state[kind][i];
    var title = $('[data-title]', node), sub = $('[data-subtitle]', node), av = $('[data-avatar]', node);
    var rm = $('[data-remove]', node);
    if (kind === 'drivers') {
      var name = (item.first + ' ' + item.last).trim();
      title.textContent = name || T('driver') + ' ' + (i + 1);
      sub.textContent = i === 0 ? T('policyholder') : (LABELS.relationship[item.relationship] || T('additionalDriver'));
      var initials = ((item.first[0] || '') + (item.last[0] || '')).toUpperCase();
      if (initials) { av.textContent = initials; av.classList.add('is-filled'); }
      else { av.innerHTML = avatarIcon.drivers; av.classList.remove('is-filled'); }
      rm.hidden = i === 0;
      $('.only-extra', node).hidden = i === 0;
      $$('.show-us', node).forEach(function (x) { x.hidden = item.id_type !== 'us'; });
      $$('.show-foreign', node).forEach(function (x) { x.hidden = item.id_type !== 'foreign'; });
      $$('.show-other', node).forEach(function (x) { x.hidden = item.id_type !== 'other'; });
      $$('.hide-other', node).forEach(function (x) { x.hidden = item.id_type === 'other'; });
    } else {
      var t = [item.year, item.make, item.model].filter(Boolean).join(' ');
      title.textContent = item.decoded && t ? t : T('vehicle') + ' ' + (i + 1);
      sub.textContent = item.decoded ? ([item.trim, item.body].filter(Boolean).join(' · ') || T('vehicleIdentified')) : T('enterVin');
      av.classList.toggle('is-ok', !!item.decoded);
      rm.hidden = state.vehicles.length === 1;
      $('[data-full]', node).hidden = !item.full_coverage;
      $('.show-lien', node).hidden = item.lien !== 'yes';
      var cnt = $('[data-vincount]', node);
      cnt.textContent = item.vin.length + '/17';
      cnt.classList.toggle('is-full', item.vin.length === 17);
      renderVin($('[data-vinres]', node), item);
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
    if (el.type === 'radio') {
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
      var d = $('[data-full]', node);
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
    if (go) { goTo(flow().indexOf(go.getAttribute('data-goto')), -1); return; }
    var stepBtn = e.target.closest('[data-qw-steps] button');
    if (stepBtn) {
      var target = +stepBtn.getAttribute('data-s');
      if (target < cur) goTo(target, -1);
      else if (target > cur && target <= furthest) {
        for (var s = cur; s < target; s++) { if (!validate(flow()[s])) { if (s !== cur) goTo(s, 1); validate(flow()[s]); return; } }
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

  function vinHTML(s, item) {
    if (s === 'loading') return '<div class="vr vr-loading"><span class="btn-spin"></span>' + esc(T('vinLooking')) + '</div>';
    if (s === 'ok') {
      var t = [item.year, item.make, item.model].filter(Boolean).join(' ');
      var d = [item.trim, item.body].filter(Boolean).join(' · ');
      return '<div class="vr vr-ok"><span class="vr-ic">' + icon('check') + '</span><div><b>' + esc(t) + '</b>' + (d ? '<small>' + esc(d) + '</small>' : '') + '</div></div>';
    }
    if (s === 'notfound') return '<div class="vr vr-warn"><span class="vr-ic">' + icon('alert') + '</span><span>' + esc(T('vinNotFound')) + '</span></div>';
    if (s === 'unavailable') return '<div class="vr vr-warn"><span class="vr-ic">' + icon('info') + '</span><span>' + esc(T('vinBusy')) + '</span></div>';
    if (s === 'ioq') return '<div class="vr vr-warn"><span class="vr-ic">' + icon('info') + '</span><span>' + esc(T('vinIOQ')) + '</span></div>';
    return '';
  }

  function renderVin(box, item) {
    var s = item._status || (item.decoded ? 'ok' : '');
    if (box.getAttribute('data-s') !== s + '|' + item.vin) {
      box.innerHTML = vinHTML(s, item);
      box.setAttribute('data-s', s + '|' + item.vin);
    }
  }

  /** VIN field inside a service's questions (title & registration). */
  function detailVin(el) {
    var box = $('[data-vinres-field="' + el.name + '"]');
    if (!box) return;
    var vin = el.value;
    if (vin.length < 17) { box.innerHTML = /[IOQ]/.test(vin) ? vinHTML('ioq') : ''; return; }
    if (!VIN_RE.test(vin)) { box.innerHTML = vinHTML('ioq'); return; }
    box.innerHTML = vinHTML('loading');
    lookupVin(vin).then(function (res) {
      if (el.value !== vin) return;
      box.innerHTML = res && res.ok ? vinHTML('ok', res) : vinHTML(res && res.error === 'unavailable' ? 'unavailable' : 'notfound');
    });
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
    if (el.type === 'radio' || el.type === 'checkbox' && el.closest('.pick-grid, .svc-pick')) {
      var group = el.closest('.seg, .opt-grid, .pick-grid, .svc-pick');
      if (group) group.classList.toggle('is-bad', !!msg);
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
    var need = function (el, ok, msg) { if (!el) return; if (!ok) errs.push([el, msg]); else setErr(el, ''); };

    if (step === 'service') {
      need(F.service[0], !!SERVICE_FLOWS[state.service], T('err.service'));
    }
    if (step === 'you') {
      var a = state.applicant, dob = parseDate(a.dob);
      if (state.service === 'commercial_auto') need(F['applicant.company'], a.company.trim().length >= 2, T('err.company'));
      need(F['applicant.first'], NAME_RE.test(a.first.trim()), T('err.first'));
      need(F['applicant.last'], NAME_RE.test(a.last.trim()), T('err.last'));
      need(F['applicant.dob'], dob && age(dob) >= 16 && age(dob) <= 110, a.dob ? T('err.dobAge') : T('err.dob'));
      need(F['applicant.phone'], a.phone.replace(/\D/g, '').length === 10, T('err.phone'));
      need(F['applicant.email'], /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(a.email.trim()), T('err.email'));
    }
    if (step === 'address') {
      var ad = state.address;
      need(F['address.zip'], /^\d{5}$/.test(ad.zip), T('err.zip'));
      if (streetNeeded()) need(F['address.street'], ad.street.trim().length >= 3, T('err.street'));
      else setErr(F['address.street'], '');
      need(F['address.city'], ad.city.trim() !== '', T('err.city'));
      need(F['address.state'], ad.state !== '', T('err.state'));
    }
    if (step === 'drivers') {
      state.drivers.forEach(function (d, i) {
        var n = cardOf('drivers', i), q = function (f) { return n.querySelector('[data-f="' + f + '"]'); };
        var dob = parseDate(d.dob), num = d.license_number.replace(/\s+/g, '');
        need(q('first'), NAME_RE.test(d.first.trim()), T('err.firstD'));
        need(q('last'), NAME_RE.test(d.last.trim()), T('err.lastD'));
        need(q('dob'), dob && age(dob) >= 14 && age(dob) <= 110, d.dob ? T('err.dobBad') : T('err.dobD'));
        if (i > 0) need(q('relationship'), !!d.relationship, T('err.rel'));
        need(n.querySelector('[data-f=id_type]'), ['us', 'foreign', 'other'].indexOf(d.id_type) > -1, T('err.idType'));
        if (d.id_type === 'us') need(q('license_state'), !!d.license_state, T('err.licState'));
        if (d.id_type === 'foreign') need(q('license_country'), d.license_country.trim() !== '', T('err.licCountry'));
        if (d.id_type === 'other') need(q('id_kind'), !!d.id_kind, T('err.idKind'));
        if (d.id_type === 'other' && !num) setErr(q('license_number'), '');
        else need(q('license_number'), /^[A-Z0-9\-*]{4,25}$/.test(num), num ? T('err.licBad') : T('err.licNum'));
      });
    }
    if (step === 'vehicles') {
      var seen = {};
      state.vehicles.forEach(function (v, i) {
        var n = cardOf('vehicles', i), el = n.querySelector('[data-f=vin]');
        var msg = '';
        if (!v.vin) msg = T('err.vin');
        else if (v.vin.length !== 17) msg = T('err.vinLen', { n: v.vin.length });
        else if (!VIN_RE.test(v.vin)) msg = T('err.vinIOQ');
        else if (seen[v.vin]) msg = T('err.vinDup');
        seen[v.vin] = true;
        need(el, !msg, msg);
        if (v.full_coverage) need(n.querySelector('[data-f=lien]'), v.lien === 'yes' || v.lien === 'no', T('err.lien'));
      });
    }
    if (step === 'coverage') {
      var c = state.coverage;
      need($('input[name="coverage.liability"]', liabilityEl), !!c.liability, T('err.liability'));
      if (c.start_date) {
        var sd = new Date(c.start_date + 'T00:00:00'), today = new Date(); today.setHours(0, 0, 0, 0);
        var days = (sd - today) / 864e5;
        need(F['coverage.start_date'], days >= 0 && days <= 366, T('err.startDate'));
      }
    }
    if (step === 'details') {
      var block = $('[data-details="' + state.service + '"]');
      var fields = JSON.parse(block.getAttribute('data-fields') || '[]');
      var vals = state.details[state.service] || {};
      fields.forEach(function (f) {
        var name = 'details.' + state.service + '.' + f.key;
        var el = $$('[name="' + name + '"]', block)[0];
        var v = vals[f.key];
        if (f.type === 'radio' || f.type === 'select') need(el, f.optional || !!v, T('err.choose'));
        else if (f.type === 'checks') need(el, f.optional || (Array.isArray(v) && v.length > 0), T('err.chooseAny'));
        else if (f.type === 'vin') need(el, !v || VIN_RE.test(v), T('err.vinOpt'));
        else if (f.type === 'text') {
          var s = (v || '').trim();
          if (!s) need(el, f.optional, T('err.fill'));
          else need(el, !f.pattern || new RegExp(f.pattern).test(s), T('err.check'));
        }
      });
    }
    if (step === 'review') {
      need(F.consent, state.consent, T('err.consent'));
    }

    errs.forEach(function (x) { setErr(x[0], x[1]); });
    if (errs.length) {
      var first = errs[0][0];
      first.focus({ preventScroll: true });
      first.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
      var s = sections[step];
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
    var fl = flow();
    n = Math.max(0, Math.min(n, fl.length - 1));
    Object.keys(sections).forEach(function (k) { sections[k].hidden = true; sections[k].classList.remove('is-shake'); });
    cur = n;
    furthest = Math.max(furthest, cur);
    var id = fl[cur], s = sections[id];
    if (id === 'drivers') { syncDriverOne(); renderList('drivers'); }
    if (id === 'vehicles') renderList('vehicles');
    if (id === 'coverage') renderLiability();
    if (id === 'review') { buildReview(); setupTurnstile(); alertEl.hidden = true; }
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
      var h = $$('h3', s).filter(function (x) { return x.offsetParent !== null; })[0];
      if (h) { h.setAttribute('tabindex', '-1'); h.focus({ preventScroll: true }); }
    }
    save();
  }

  function renderStepper() {
    var fl = flow();
    stepsEl.style.setProperty('--n', fl.length);
    stepsEl.innerHTML = fl.map(function (id, i) {
      return '<li data-s="' + i + '"><button type="button" tabindex="-1" data-s="' + i + '" disabled><span class="qw-dot"><span>' + (i + 1) + '</span>' + icon('check') + '</span><span class="qw-sl">' + esc(T('steps.' + id)) + '</span></button></li>';
    }).join('');
  }

  function updateChrome() {
    var fl = flow();
    $$('li', stepsEl).forEach(function (li, i) {
      li.classList.toggle('is-current', i === cur);
      li.classList.toggle('is-done', i !== cur && i <= furthest);
      var b = li.querySelector('button');
      b.disabled = i > furthest || i === cur;
      if (i === cur) b.setAttribute('aria-current', 'step'); else b.removeAttribute('aria-current');
    });
    bar.style.width = ((cur + 1) / fl.length * 100) + '%';
    stepLabel.textContent = T('stepOf', { n: cur + 1, t: fl.length }) + ' · ' + T('steps.' + fl[cur]);
    backBtn.hidden = cur === 0;
    nextLabel.textContent = cur === fl.length - 1 ? T('send') : cur === fl.length - 2 ? T('reviewBtn') : T('continue');
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    if (busy) return;
    if (!validate(stepId())) return;
    if (cur < flow().length - 1) goTo(cur + 1, 1);
    else send();
  });
  backBtn.addEventListener('click', function () { if (cur > 0) goTo(cur - 1, -1); });

  /* ------------------------------------------------------------------ */
  /* Review                                                              */
  /* ------------------------------------------------------------------ */
  function section(title, ic, step, body) {
    return '<section class="rv-sec"><header class="rv-h">' + icon(ic) + '<h4>' + esc(title) + '</h4>' +
      '<button type="button" class="rv-edit" data-goto="' + step + '">' + icon('edit') + T('edit') + '</button></header>' +
      '<div class="rv-b">' + body + '</div></section>';
  }
  function item(b, lines, tags) {
    return '<div class="rv-item"><b>' + b + '</b>' + lines.filter(Boolean).map(function (l) { return '<span>' + l + '</span>'; }).join('') +
      (tags ? '<div class="rv-tags">' + tags + '</div>' : '') + '</div>';
  }
  function tag(t, on) { return '<em' + (on === false ? ' class="off"' : '') + '>' + esc(t) + '</em>'; }

  function detailRows() {
    var block = $('[data-details="' + state.service + '"]');
    var fields = JSON.parse(block.getAttribute('data-fields') || '[]');
    var vals = state.details[state.service] || {};
    var rows = [];
    fields.forEach(function (f) {
      var v = vals[f.key];
      if (v == null || v === '' || (Array.isArray(v) && !v.length)) return;
      var name = 'details.' + state.service + '.' + f.key;
      var wrap = $('[name="' + name + '"]', block).closest('[data-label]');
      var label = wrap ? wrap.getAttribute('data-label') : f.key;
      var text = v;
      if (f.type === 'radio' || f.type === 'checks') {
        text = (Array.isArray(v) ? v : [v]).map(function (x) {
          var inp = $('[name="' + name + '"][value="' + x + '"]', block);
          return inp ? inp.closest('label').textContent.trim() : x;
        }).join(', ');
      } else if (f.type === 'select') {
        var opt = $('[name="' + name + '"] option[value="' + v + '"]', block);
        text = opt ? opt.textContent : v;
      }
      rows.push([label, text]);
    });
    return rows;
  }

  function buildReview() {
    var a = state.applicant, ad = state.address;
    var html = section(T('service'), 'sparkle', 'service', item(esc(LABELS.service[state.service] || ''), []));
    html += section(T('aboutYou'), 'user', 'you', item(esc(a.first + ' ' + a.last), [
      a.company ? esc(T('company')) + ': ' + esc(a.company) + (a.business_type ? ' · ' + esc(a.business_type) : '') : '',
      esc(T('born')) + ' ' + esc(a.dob), esc(a.phone) + ' · ' + esc(a.email), esc(T('prefers')) + ': ' + esc(LABELS.contact[a.contact_pref] || '')
    ]));
    var cityLine = ad.city + ', ' + (LABELS.state[ad.state] || ad.state) + ' ' + ad.zip;
    html += section(T('address'), 'pin', 'address', ad.street
      ? item(esc(ad.street + (ad.unit ? ', ' + ad.unit : '')), [esc(cityLine)])
      : item(esc(cityLine), []));
    if (isAuto()) {
      html += section(T('drivers') + ' (' + state.drivers.length + ')', 'users', 'drivers', state.drivers.map(function (d, i) {
        var where = d.id_type === 'us' ? (LABELS.state[d.license_state] || d.license_state) : d.id_type === 'foreign' ? d.license_country : (LABELS.idKind[d.id_kind] || '');
        var lic = d.license_number.replace(/\s+/g, '');
        return item(esc(d.first + ' ' + d.last) + ' <span class="muted">· ' + esc(i === 0 ? T('you') : LABELS.relationship[d.relationship] || '') + '</span>', [
          esc(T('born')) + ' ' + esc(d.dob),
          [LABELS.idType[d.id_type], where, lic ? '••••' + lic.slice(-4) : ''].filter(Boolean).map(esc).join(' · ')
        ]);
      }).join(''));
      html += section(T('vehicles') + ' (' + state.vehicles.length + ')', 'car', 'vehicles', state.vehicles.map(function (v) {
        var t = v.decoded ? [v.year, v.make, v.model, v.trim].filter(Boolean).join(' ') : T('vinOnly');
        var tags = (v.full_coverage
          ? tag(T('fullCoverage') + (v.deductible && v.deductible !== 'unsure' ? ' · ' + (LABELS.deductible[v.deductible] || '') + ' ' + T('deductible') : '')) +
            (v.lien === 'yes' ? tag(T('financed') + (v.lienholder ? ': ' + v.lienholder : '')) : v.lien === 'no' ? tag(T('noLien')) : '')
          : tag(T('liabilityOnly'), false)) +
          tag(T('roadside'), v.roadside) + tag(T('rental'), v.rental);
        return item(esc(t), ['<span class="mono">VIN ' + esc(v.vin) + '</span>'], tags);
      }).join(''));
      var c = state.coverage, extra = [];
      var liab = (LIAB_SETS[state.service] || []).filter(function (o) { return o.val === c.liability; })[0];
      if (c.start_date) extra.push(esc(T('startDate')) + ': ' + esc(new Date(c.start_date + 'T00:00:00').toLocaleDateString(T('dateLocale'), { month: 'short', day: 'numeric', year: 'numeric' })));
      if (c.currently_insured) extra.push(esc(T('insured')) + ': ' + (c.currently_insured === 'yes' ? esc(T('yes')) + (c.current_carrier ? ' (' + esc(c.current_carrier) + ')' : '') : esc(T('no'))));
      if (c.comments) extra.push(esc(T('notes')) + ': ' + esc(c.comments));
      html += section(T('coverage'), 'shield', 'coverage', item(esc(T('liability')) + ': ' + esc(liab ? liab.label : ''), extra,
        tag(T('pip'), c.pip) + tag(T('um'), c.um)));
    } else {
      var rows = detailRows();
      html += section(T('details'), 'doc', 'details', rows.map(function (r) { return item(esc(r[0]), [esc(r[1])]); }).join(''));
    }
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
    var p = {
      service: state.service,
      lang: LANG,
      applicant: state.applicant,
      address: state.address,
      details: state.details[state.service] || {},
      consent: state.consent,
      token: root.getAttribute('data-token'),
      website: form.elements.website.value,
      turnstile: tsId !== null && window.turnstile ? window.turnstile.getResponse(tsId) : ''
    };
    if (isAuto()) {
      p.drivers = state.drivers;
      p.vehicles = state.vehicles.map(function (v) { var c = Object.assign({}, v); delete c._status; return c; });
      p.coverage = state.coverage;
    }
    return JSON.parse(JSON.stringify(p));
  }

  var STEP_OF = { service: 'service', applicant: 'you', address: 'address', drivers: 'drivers', vehicles: 'vehicles', coverage: 'coverage', details: 'details', consent: 'review' };

  function showServerErrors(errors) {
    var fl = flow(), first = 99;
    Object.keys(errors).forEach(function (path) {
      var idx = fl.indexOf(STEP_OF[path.split('.')[0]]);
      if (idx > -1) first = Math.min(first, idx);
    });
    if (first === 99) return;
    goTo(first, -1, { silent: true });
    Object.keys(errors).forEach(function (path) {
      var parts = path.split('.'), el = null;
      if (fl.indexOf(STEP_OF[parts[0]]) !== first) return;
      if (parts[0] === 'drivers' || parts[0] === 'vehicles') {
        var card = cardOf(parts[0], +parts[1]);
        el = card && card.querySelector('[data-f="' + parts[2] + '"]');
      } else if (parts[0] === 'details') {
        el = $('[name="details.' + state.service + '.' + parts[1] + '"]');
      } else if (parts[0] === 'consent') {
        el = form.elements.consent;
      } else if (parts[0] === 'service') {
        el = form.elements.service[0];
      } else if (parts[0] === 'coverage' && parts[1] === 'liability') {
        el = $('input[name="coverage.liability"]', liabilityEl);
      } else {
        el = form.elements[path];
        if (el && el.length && !el.tagName) el = el[0];
      }
      if (el) setErr(el, errors[path]);
    });
    toast(T('fixFields'));
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
      .then(function (r) { return r.json().catch(function () { return { ok: false, error: T('serverError') }; }); })
      .then(function (res) {
        if (res.ok) return showDone(res.ref);
        if (res.errors) return showServerErrors(res.errors);
        alertEl.textContent = res.error || T('serverError');
        alertEl.hidden = false;
        if (tsId !== null && window.turnstile) window.turnstile.reset(tsId);
      })
      .catch(function () {
        alertEl.textContent = T('offline');
        alertEl.hidden = false;
      })
      .then(function () { busy = false; nextBtn.classList.remove('is-loading'); });
  }

  function showDone(ref) {
    $('[data-qw-ref]').textContent = ref;
    $('[data-qw-first]').textContent = state.applicant.first;
    form.hidden = true;
    stepsEl.hidden = true;
    bar.parentElement.hidden = true;
    stepLabel.textContent = T('sent');
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
    stepsEl.hidden = false;
    bar.parentElement.hidden = false;
    $$('.is-bad', form).forEach(function (el) { el.classList.remove('is-bad'); });
    $$('.fld-e.is-on', form).forEach(function (el) { el.classList.remove('is-on'); el.textContent = ''; });
    fillStatic();
    applyService();
    goTo(0, -1, { silent: true });
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
    var colors = ['#3F55C8', '#FFB547', '#0D1640', '#8ED3EC', '#F29A2E', '#2B3990'];
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
    opts = opts || {};
    if (opts.zip && /^\d{5}$/.test(opts.zip)) {
      state.address.zip = opts.zip;
      form.elements['address.zip'].value = opts.zip;
      zipLookup(opts.zip);
    }
    if (opts.service && SERVICE_FLOWS[opts.service]) {
      if (state.service !== opts.service) {
        state.service = opts.service;
        furthest = 0;
        $$('input[name="service"]').forEach(function (r) { r.checked = r.value === opts.service; });
        applyService();
      }
      if (stepId() === 'service') cur = 1;
    }
  }

  function open(opts) {
    if (mode !== 'modal') {
      prefill(opts);
      goTo(cur, 1, { silent: true });
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
    if (cur > 1 && hasData() && !(opts && opts.service)) toast(T('welcomeBack'));
    setTimeout(function () {
      var first = sections[stepId()].querySelector('input:not([type=radio]):not([type=checkbox]):not([type=hidden]), select');
      if (first && first.offsetParent !== null && window.matchMedia('(pointer: fine)').matches) first.focus({ preventScroll: true });
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
    if (doneEl.hidden && hasData()) toast(T('progressSaved'));
  }

  window.addEventListener('popstate', function () {
    if (isOpen && location.hash !== '#quote') close(true);
    else if (!isOpen && location.hash === '#quote' && mode === 'modal') open();
  });

  // Any element with data-quote opens the form; data-quote="home" preselects a service.
  document.addEventListener('click', function (e) {
    var t = e.target.closest('[data-quote]');
    if (!t || e.defaultPrevented || e.metaKey || e.ctrlKey) return;
    e.preventDefault();
    open({ service: t.getAttribute('data-quote') || '' });
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
  applyService();
  Object.keys(sections).forEach(function (k) { sections[k].hidden = k !== stepId(); });
  if (stepId() === 'drivers') renderList('drivers');
  if (stepId() === 'vehicles') renderList('vehicles');
  if (stepId() === 'review') buildReview();
  updateChrome();
  // /quote?service=home or #quote links open with a service already chosen.
  var preset = new URLSearchParams(location.search).get('service');
  if (preset && SERVICE_FLOWS[preset]) prefill({ service: preset }), goTo(cur, 1, { silent: true });
  if (mode === 'modal' && location.hash === '#quote') open();

  window.QuoteWizard = { open: open, close: close };
})();
