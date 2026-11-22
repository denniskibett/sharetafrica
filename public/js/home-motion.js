/* Home page motion: scroll reveal, count-up stats, the animated send card, and pausing motion that is off screen.
   Does nothing when the visitor prefers reduced motion; the page's final text is already in the HTML. */
(function () {
  var mq = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)');
  if (!('IntersectionObserver' in window) || (mq && mq.matches)) return;
  var root = document.querySelector('.ui.web');
  if (!root) return;
  document.documentElement.classList.add('hm-js');

  // scroll reveal: every section after the hero, plus the network panel
  var rev = [].slice.call(root.querySelectorAll(':scope > .wr')).slice(1).concat([].slice.call(root.querySelectorAll('.hm-net, .hm-rails, .foot')));
  var seen = new IntersectionObserver(function (es) {
    es.forEach(function (e) {
      if (!e.isIntersecting) return;
      e.target.classList.add('hm-on');
      seen.unobserve(e.target);
      [].forEach.call(e.target.querySelectorAll('[data-to]'), count);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.06 });
  rev.forEach(function (el) { el.classList.add('hm-r'); seen.observe(el); });

  // count up (or down) to the number already written in the page
  function count(el) {
    var to = +el.dataset.to, from = +(el.dataset.from || 0), t0 = 0, dur = 1400;
    function step(t) {
      t0 = t0 || t;
      var p = Math.min(1, (t - t0) / dur), e = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.round(from + (to - from) * e);
      if (p < 1) requestAnimationFrame(step);
    }
    el.textContent = from;
    requestAnimationFrame(step);
  }

  // sleep animations that are off screen (battery and cheap phones)
  var zz = new IntersectionObserver(function (es) {
    es.forEach(function (e) { e.target.classList.toggle('hm-zz', !e.isIntersecting); });
  });
  [].forEach.call(root.querySelectorAll('.hm-net, .hm-rails, .stage, .deck, .p3'), function (el) { zz.observe(el); });

  // the send card: type the amount, show the fee, send, done; loops while visible
  var card = root.querySelector('.hm-pay');
  if (!card) return;
  var amt = card.querySelector('[data-type]'), full = amt.dataset.type, digits = ['1', '15', '150', '1,500'];
  var timers = [], on = false;
  function at(ms, fn) { timers.push(setTimeout(fn, ms)); }
  function set(s) { card.setAttribute('data-s', s); }
  function run() {
    timers.forEach(clearTimeout); timers = [];
    set(0); amt.textContent = 'KES 0';
    digits.forEach(function (d, i) { at(700 + i * 380, function () { set(1); amt.textContent = 'KES ' + d; }); });
    at(2500, function () { amt.textContent = full; set(2); });
    at(4300, function () { set(3); });
    at(4700, function () { set(4); });
    at(7600, function () { if (on) run(); });
  }
  new IntersectionObserver(function (es) {
    var v = es[0].isIntersecting;
    if (v && !on) { on = true; run(); }
    if (!v && on) { on = false; timers.forEach(clearTimeout); timers = []; set(2); amt.textContent = full; }
  }, { threshold: 0.4 }).observe(card);
})();
