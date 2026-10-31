import './bootstrap';

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';
import focus from '@alpinejs/focus';
import collapse from '@alpinejs/collapse';
import { dataTable, chart, alertModal, profile, kycCamera, signaturePad, checkPhoto, installHelpers } from './dashboard';

/**
 * Dashboard forms save in the background: sharet.send() returns the JSON reply, shows a toast,
 * and throws { status, errors } on validation errors so the form can show them inline.
 */
window.sharet = {
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
Alpine.data('dataTable', dataTable);
Alpine.data('chart', chart);
Alpine.data('alertModal', alertModal);
Alpine.data('profile', profile);
Alpine.data('kycCamera', kycCamera);
Alpine.data('signaturePad', signaturePad);
window.sharet.checkPhoto = checkPhoto;
Alpine.plugin(persist);
Alpine.plugin(focus);
Alpine.plugin(collapse);
window.Alpine = Alpine;
Alpine.start();
