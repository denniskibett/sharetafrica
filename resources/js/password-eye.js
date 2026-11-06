/**
 * Show / hide button on every password field (website and dashboard), including ones Alpine adds later.
 * Opt out with data-no-eye. The field goes back to hidden when its form is submitted.
 */
const EYE = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>';
const SHUT = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18"/><path d="M10.6 5.1A10.4 10.4 0 0 1 12 5c6.5 0 10 7 10 7a17 17 0 0 1-3.2 4.2M6.6 6.6A17.4 17.4 0 0 0 2 12s3.5 7 10 7a9.7 9.7 0 0 0 5.4-1.6"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/></svg>';

function enhance(input) {
    if (input.dataset.eye || input.hasAttribute('data-no-eye') || input.hasAttribute(':type') || input.hasAttribute('x-bind:type')) return;
    input.dataset.eye = '1';

    const wrap = document.createElement('span');
    wrap.style.cssText = 'position:relative;display:block;width:100%';
    input.parentNode.insertBefore(wrap, input);
    wrap.appendChild(input);
    input.style.paddingRight = '52px';
    if (!input.style.width) input.style.width = '100%';

    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'password-eye';
    btn.setAttribute('aria-label', 'Show password');
    btn.setAttribute('aria-pressed', 'false');
    btn.style.cssText = 'position:absolute;right:4px;top:50%;transform:translateY(-50%);width:44px;height:44px;display:flex;align-items:center;justify-content:center;border:0;background:transparent;color:inherit;opacity:.7;cursor:pointer;padding:0';
    btn.innerHTML = EYE;
    btn.addEventListener('click', () => {
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = show ? SHUT : EYE;
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        btn.setAttribute('aria-pressed', String(show));
        input.focus({ preventScroll: true });
    });
    wrap.appendChild(btn);
    input.form?.addEventListener('submit', () => { input.type = 'password'; }, { capture: true });
}

export function installPasswordEyes(root = document) {
    const scan = (node) => node.querySelectorAll?.('input[type=password]').forEach(enhance);
    const start = () => {
        scan(root);
        new MutationObserver((records) => records.forEach((r) => r.addedNodes.forEach((n) => {
            if (n.nodeType !== 1) return;
            n.matches('input[type=password]') ? enhance(n) : scan(n);
        }))).observe(root.body || root, { childList: true, subtree: true });
    };
    document.readyState === 'loading' ? document.addEventListener('DOMContentLoaded', start) : start();
}
