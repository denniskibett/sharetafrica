/* Sharet-family analytics (served by scharge, collected at POST /t/x, reported in Sales CRM > Growth).
   Include once in a public layout's <head>:
     <script defer src="https://scharge.sharet.africa/js/sharet-analytics.js" data-site="KEY"></script>
   KEY: sharet | breve | aiyotickets | eserviz | scharge | loans
   Conversions, before or after this script loads:
     <script>(window.sa=window.sa||[]).push(['conversion','signup'])</script>
   CTA clicks: <a data-sa-click="hero_get_started" ...>
   Privacy: no cookies, no IPs kept, nothing typed is read, no query strings sent (only utm_* tags);
   Do-Not-Track / Global Privacy Control turn it off. First touch (source, UTM, landing page) is kept
   in sessionStorage for this site only and sent with conversions. */
(function (w, d) {
  'use strict';
  var me = d.currentScript, queued = w.sa && w.sa.push ? w.sa : [];
  w.sa = { push: function () {} };
  if (!me || navigator.doNotTrack === '1' || w.doNotTrack === '1' || navigator.globalPrivacyControl) return;
  if (w.self !== w.top) return;
  var site = me.getAttribute('data-site');
  if (!/^[a-z]{2,20}$/.test(site || '')) return;
  var url = new URL(me.src).origin + '/t/x';

  function rnd() {
    var c = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789', k = '', r = w.crypto && crypto.getRandomValues ? crypto.getRandomValues(new Uint8Array(16)) : null;
    for (var i = 0; i < 16; i++) k += c[(r ? r[i] : Math.floor(Math.random() * 256)) % 62];
    return k;
  }
  function store(k, v) {
    try { if (v === undefined) return JSON.parse(sessionStorage.getItem(k) || 'null'); sessionStorage.setItem(k, JSON.stringify(v)); } catch (e) { return null; }
  }
  function send(p) {
    p.site = site; p.path = location.pathname; p.vw = w.innerWidth;
    var body = JSON.stringify(p);
    try {
      if (w.fetch) fetch(url, { method: 'POST', body: body, keepalive: true, mode: 'no-cors', credentials: 'omit', headers: { 'Content-Type': 'text/plain' } });
      else if (navigator.sendBeacon) navigator.sendBeacon(url, new Blob([body], { type: 'text/plain' }));
    } catch (e) { /* analytics must never break the page */ }
  }

  // First touch this session: a campaign link replaces a plain direct/referral touch, nothing else does.
  var q = new URLSearchParams(location.search), utm = { source: q.get('utm_source'), medium: q.get('utm_medium'), campaign: q.get('utm_campaign') };
  var ref = d.referrer && d.referrer.indexOf(location.origin) !== 0 ? d.referrer.split('?')[0] : '';
  var ftKey = 'sa_ft_' + site, ft = store(ftKey);
  if (!ft || (utm.source && !ft.campaign)) {
    ft = { source: utm.source, medium: utm.medium, campaign: utm.campaign, ref: ref, landing: location.pathname };
    store(ftKey, ft);
  }

  var key = rnd(), started = Date.now(), visibleMs = 0, shownAt = d.visibilityState === 'hidden' ? 0 : started, maxScroll = 0, left = false;
  send({ type: 'view', key: key, ref: ref, utm: utm });

  w.addEventListener('scroll', function () {
    var h = d.documentElement.scrollHeight - w.innerHeight;
    if (h > 0) maxScroll = Math.max(maxScroll, Math.min(100, Math.round(w.scrollY / h * 100)));
  }, { passive: true });
  function leave() {
    if (shownAt) { visibleMs += Date.now() - shownAt; shownAt = 0; }
    if (left) return;
    left = true;
    send({ type: 'leave', key: key, ms: visibleMs, scroll: maxScroll });
  }
  d.addEventListener('visibilitychange', function () {
    if (d.visibilityState === 'hidden') leave(); else shownAt = Date.now();
  });
  w.addEventListener('pagehide', leave);

  // Named CTAs and contact / outbound links (the number or address itself is never sent)
  d.addEventListener('click', function (e) {
    var t = e.target.closest ? e.target : null;
    if (!t) return;
    var cta = t.closest('[data-sa-click]');
    if (cta) send({ type: 'click', label: String(cta.getAttribute('data-sa-click')).slice(0, 120), ft: ft });
    var a = t.closest('a[href]');
    if (!a) return;
    var href = a.getAttribute('href') || '', name = /^tel:/i.test(href) ? 'phone_click' : /^mailto:/i.test(href) ? 'email_click'
      : /wa\.me|whatsapp\.com/i.test(href) ? 'whatsapp_click' : (a.host && a.host !== location.host && /^https?:/i.test(a.protocol)) ? 'outbound' : null;
    if (name) send({ type: 'event', name: name, label: name === 'outbound' ? a.host : null, ft: ft });
  }, { passive: true, capture: true });

  // Queue: ['conversion', name] | ['click', label]. The same conversion on the same page counts once per 30 minutes.
  function handle(cmd) {
    if (!cmd || !cmd.length) return;
    if (cmd[0] === 'conversion' && /^[a-z][a-z0-9_]{1,39}$/.test(cmd[1] || '')) {
      var seen = store('sa_cv_' + site) || {}, id = cmd[1] + location.pathname, now = Date.now();
      if (seen[id] && now - seen[id] < 1800000) return;
      seen[id] = now; store('sa_cv_' + site, seen);
      send({ type: 'conversion', name: cmd[1], ft: ft });
    } else if (cmd[0] === 'click' && cmd[1]) {
      send({ type: 'click', label: String(cmd[1]).slice(0, 120), ft: ft });
    }
  }
  w.sa = { push: function () { for (var i = 0; i < arguments.length; i++) handle(arguments[i]); } };
  for (var i = 0; i < queued.length; i++) handle(queued[i]);
})(window, document);
