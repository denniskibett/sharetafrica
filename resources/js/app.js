import './bootstrap';

import Alpine from 'alpinejs';
import persist from '@alpinejs/persist';

/**
 * Dashboard forms save in the background: sharet.send() returns the JSON reply, shows a toast,
 * and throws { status, errors } on validation errors so the form can show them inline.
 */
window.sharet = {
    async send(method, url, data = null) {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const res = await fetch(url, {
            method,
            headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' },
            body: data ? JSON.stringify(data) : null,
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

Alpine.plugin(persist);
window.Alpine = Alpine;
Alpine.start();
