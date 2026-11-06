// Fonts ship with the app (no Google Fonts request): Outfit for numbers and staff pages, Inter Tight for customer text
import '@fontsource-variable/outfit';
import '@fontsource-variable/inter-tight';
import './bootstrap';

import Alpine from 'alpinejs';
import { installPasswordEyes } from './password-eye';
installPasswordEyes();
import persist from '@alpinejs/persist';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import intersect from '@alpinejs/intersect';
import { dataTable, chart, alertModal, profile, kycCamera, signaturePad, checkPhoto, installHelpers, installImagePaste } from './dashboard';

/**
 * Dashboard forms save in the background: sharet.send() returns the JSON reply, shows a toast,
 * and throws { status, errors } on validation errors so the form can show them inline.
 */
window.sharet = {
    /** Counts an amount like "KES 48,250.00" up from zero (skipped for reduced motion). set(text) receives each frame. */
    count(target, set, ms = 700) {
        const m = String(target ?? '').match(/^(\D*?)(\d[\d,]*(?:\.\d+)?)(.*)$/);
        if (!m || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return set(target);
        const end = parseFloat(m[2].replace(/,/g, '')), dec = (m[2].split('.')[1] || '').length, t0 = performance.now();
        const step = (t) => {
            const p = Math.min(1, (t - t0) / ms), e = 1 - Math.pow(1 - p, 3);
            set(m[1] + (end * e).toLocaleString('en-KE', { minimumFractionDigits: dec, maximumFractionDigits: dec }) + m[3]);
            if (p < 1) requestAnimationFrame(step);
        };
        requestAnimationFrame(step);
    },
    async send(method, url, data = null) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        // FormData (file uploads) goes as multipart; anything else as JSON
        const multipart = data instanceof FormData;
        const res = await fetch(url, {
            method,
            headers: { 'Accept': 'application/json', ...(multipart ? {} : { 'Content-Type': 'application/json' }), 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: multipart ? data : (data ? JSON.stringify(data) : null),
            credentials: 'same-origin',
        });
        const body = await res.json().catch(() => ({}));
        if (res.status === 419) {
            this.toast('Your session expired. Reload the page and try again.', 'error');
        } else if (body.message && (res.ok || res.status !== 422 || !body.errors)) {
            this.toast(body.message, res.ok ? 'success' : 'error');
        }
        if (!res.ok) throw { status: res.status, errors: body.errors || {}, body };
        return body;
    },
    toast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('toast', { detail: { message, type } }));
    },
    async copy(text) {
        try { await navigator.clipboard.writeText(text); this.toast('Copied.'); } catch { this.toast('Copy failed: select and copy by hand.', 'error'); }
    },
};

/**
 * <form data-ajax data-success="Saved."> submits in the background: toast on success, Laravel's
 * validation errors shown under each field, no page reload. Works with PATCH/PUT via _method.
 */
document.addEventListener('submit', async (e) => {
    const form = e.target.closest('form[data-ajax]');
    if (!form) return;
    e.preventDefault();
    const button = form.querySelector('[type="submit"]');
    button && (button.disabled = true);
    form.querySelectorAll('.ajax-error').forEach((el) => el.remove());
    try {
        const res = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        });
        if (res.ok) {
            sharet.toast(form.dataset.success || 'Saved.');
            form.querySelectorAll('input[type="password"]').forEach((el) => (el.value = ''));
        } else if (res.status === 422) {
            const { errors = {} } = await res.json();
            Object.entries(errors).forEach(([name, messages]) => {
                const field = form.querySelector(`[name="${name}"]`);
                const p = document.createElement('p');
                p.className = 'ajax-error mt-1 text-sm text-red-600 dark:text-red-400';
                p.textContent = messages[0];
                field ? field.insertAdjacentElement('afterend', p) : form.prepend(p);
            });
            sharet.toast('Please check the highlighted fields.', 'error');
        } else {
            sharet.toast(res.status === 419 ? 'Your session expired. Reload the page.' : 'Could not save. Please try again.', 'error');
        }
    } catch {
        sharet.toast('You seem to be offline. Try again.', 'error');
    }
    button && (button.disabled = false);
});

installHelpers(window.sharet);
installImagePaste();
Alpine.data('dataTable', dataTable);
Alpine.data('chart', chart);
Alpine.data('alertModal', alertModal);
Alpine.data('profile', profile);
Alpine.data('kycCamera', kycCamera);
Alpine.data('signaturePad', signaturePad);
window.sharet.checkPhoto = checkPhoto;
/** Draw a QR code for `text` on a canvas (the receive panel). The library loads only when a QR is shown. */
window.sharet.qr = async (canvas, text) => (await import('qrcode')).toCanvas(canvas, text, { width: 220, margin: 1, color: { dark: '#050507', light: '#FFFFFF' } });
Alpine.plugin(persist);
Alpine.plugin(focus);
Alpine.plugin(collapse);
Alpine.plugin(intersect);
window.Alpine = Alpine;
Alpine.start();
