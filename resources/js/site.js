/* Brivon — free HTML template. Minimal vanilla JS: mobile drawer + scroll-reveal. */
(function () {
  'use strict';

  // Mobile drawer
  const toggle = document.querySelector('.nav-toggle');
  const drawer = document.getElementById('mobile-drawer');
  const closeBtn = drawer && drawer.querySelector('.drawer-close');

  function openDrawer() {
    if (!drawer) return;
    drawer.classList.add('open');
    drawer.setAttribute('aria-hidden', 'false');
    if (toggle) toggle.setAttribute('aria-expanded', 'true');
    document.body.style.overflow = 'hidden';
  }
  function closeDrawer() {
    if (!drawer) return;
    drawer.classList.remove('open');
    drawer.setAttribute('aria-hidden', 'true');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }
  if (toggle) toggle.addEventListener('click', openDrawer);
  if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
  if (drawer) {
    drawer.querySelectorAll('a').forEach(function (a) {
      a.addEventListener('click', closeDrawer);
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && drawer && drawer.classList.contains('open')) closeDrawer();
  });

  // Reveal on intersect (respects prefers-reduced-motion)
  const reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (!reduced && 'IntersectionObserver' in window) {
    const els = document.querySelectorAll('.reveal');
    if (els.length) {
      els.forEach(function (el) {
        el.style.opacity = '0';
        el.style.transform = 'translateY(16px)';
        el.style.transition = 'opacity 600ms cubic-bezier(0.16, 1, 0.3, 1), transform 600ms cubic-bezier(0.16, 1, 0.3, 1)';
      });
      const io = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            io.unobserve(entry.target);
          }
        });
      }, { threshold: 0.18, rootMargin: '0px 0px -10% 0px' });
      els.forEach(function (el) { io.observe(el); });
    }
  }

  // Sticky header subtle shadow on scroll
  const header = document.querySelector('.site-header');
  if (header) {
    let last = 0;
    window.addEventListener('scroll', function () {
      const y = window.scrollY;
      if (y > 4 && last <= 4) header.style.boxShadow = '0 8px 24px -16px rgba(0,0,0,0.6)';
      if (y <= 4 && last > 4) header.style.boxShadow = 'none';
      last = y;
    }, { passive: true });
  }
})();

/*
 * First-party analytics (TrackController): page view, time on page + scroll depth on leave, and click
 * positions for the heatmap. No cookies; sent with sendBeacon so it never slows the page down.
 * Honours "Do Not Track" / Global Privacy Control.
 */
(function () {
  'use strict';
  if (navigator.doNotTrack === '1' || navigator.globalPrivacyControl) return;
  if (window.self !== window.top) return; // the heatmap preview loads pages in a frame: don't count those

  var url = '/t';
  var started = Date.now();
  var maxScroll = 0;
  var key = Array.from(crypto.getRandomValues(new Uint8Array(12)), function (b) { return 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'[b % 62]; }).join('').slice(0, 16);
  while (key.length < 16) key += 'x';

  function send(payload) {
    payload.path = location.pathname;
    payload.vw = window.innerWidth;
    var body = JSON.stringify(payload);
    if (navigator.sendBeacon) navigator.sendBeacon(url, new Blob([body], { type: 'application/json' }));
    else fetch(url, { method: 'POST', body: body, keepalive: true, headers: { 'Content-Type': 'application/json' } });
  }

  var q = new URLSearchParams(location.search);
  send({ type: 'view', key: key, ref: document.referrer, utm: { source: q.get('utm_source'), medium: q.get('utm_medium'), campaign: q.get('utm_campaign') } });

  window.addEventListener('scroll', function () {
    var h = document.documentElement.scrollHeight - window.innerHeight;
    if (h > 0) maxScroll = Math.max(maxScroll, Math.round(window.scrollY / h * 100));
  }, { passive: true });

  var left = false;
  function leave() {
    if (left) return;
    left = true;
    send({ type: 'leave', key: key, ms: Date.now() - started, scroll: maxScroll });
  }
  document.addEventListener('visibilitychange', function () { if (document.visibilityState === 'hidden') leave(); });
  window.addEventListener('pagehide', leave);

  document.addEventListener('click', function (e) {
    var doc = document.documentElement;
    var el = e.target.closest('a, button, [role="button"], input, label, summary') || e.target;
    var label = (el.getAttribute('aria-label') || el.innerText || el.value || el.alt || el.tagName || '').trim().slice(0, 120);
    send({
      type: 'click',
      x: Math.round(e.pageX / doc.scrollWidth * 1000),
      y: Math.round(e.pageY),
      ph: doc.scrollHeight,
      t: label,
    });
  }, { passive: true, capture: true });
})();

/*
 * Forms marked data-async-form send in the background: no page reload, the server's reply
 * appears in .form-status, and field errors show under each field. Without JS they post normally.
 */
(function () {
  'use strict';
  document.querySelectorAll('form[data-async-form]').forEach(function (form) {
    var status = form.querySelector('.form-status');
    form.addEventListener('submit', async function (e) {
      e.preventDefault();
      if (form.getAttribute('aria-busy') === 'true') return;
      form.setAttribute('aria-busy', 'true');
      form.querySelectorAll('.field-error').forEach(function (el) { el.remove(); });
      try {
        var res = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
        var body = await res.json().catch(function () { return {}; });
        if (res.ok) {
          show(body.message || 'Thank you.', false);
          form.querySelectorAll('input:not([type=hidden]):not([name=_token]), textarea').forEach(function (el) { el.value = ''; });
          form.querySelectorAll('select').forEach(function (el) { el.selectedIndex = 0; });
        } else if (res.status === 422 && body.errors) {
          Object.keys(body.errors).forEach(function (name) {
            var field = form.querySelector('[name="' + name + '"]');
            if (!field) return;
            var err = document.createElement('span');
            err.className = 'field-error';
            err.textContent = body.errors[name][0];
            field.insertAdjacentElement('afterend', err);
          });
          show('Please check the highlighted fields.', true);
        } else if (res.status === 429) {
          show('You have sent a few notes already. Please wait a few minutes and try again.', true);
        } else if (res.status === 419) {
          show('This page was open a long time. Reload it and send again.', true);
        } else {
          show('Something went wrong on our side. Please email hello@sharet.africa instead.', true);
        }
      } catch (err) {
        show('You seem to be offline. Check your connection and try again.', true);
      }
      form.removeAttribute('aria-busy');
    });

    function show(text, isError) {
      if (!status) return;
      status.hidden = false;
      status.textContent = text;
      status.classList.toggle('is-error', isError);
      status.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
  });
})();
