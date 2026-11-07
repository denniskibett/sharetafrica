/**
 * Shared dashboard components. Pages never embed data in Blade: each page asks its controller for
 * JSON (same URL with Accept: application/json, or a /data route) and these components render it.
 *
 *  dataTable({ url, perPage })  search, filters, sorting, pagination over { data, meta } from Laravel's paginator
 *  chart({ url | options, type }) ApexCharts, loaded only on pages that draw a chart; follows light/dark
 *  sharet.confirm({...}) / sharet.alert({...})  the one centre alert modal for the whole dashboard
 *  sharet.panel(name, payload)  opens the right-hand slide-over with that name
 */

const query = (params) => new URLSearchParams(Object.entries(params).filter(([, v]) => v !== '' && v !== null && v !== undefined)).toString();

export async function getJson(url, params = {}) {
    const q = query(params);
    const res = await fetch(url + (q ? (url.includes('?') ? '&' : '?') + q : ''), {
        headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        credentials: 'same-origin',
    });
    if (!res.ok) throw new Error(`Request failed (${res.status})`);
    return res.json();
}

export function dataTable({ url, perPage = 10, filters = {}, sort = '' } = {}) {
    return {
        url, rows: [], meta: { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }, stats: {}, extra: {},
        search: '', perPage, page: 1, sort, filters: { ...filters }, loading: true, error: '', selected: [],
        init() {
            this.load();
            this.$watch('search', () => { clearTimeout(this._t); this._t = setTimeout(() => { this.page = 1; this.load(); }, 300); });
            this.$watch('perPage', () => { this.page = 1; this.load(); });
            this.$watch('filters', () => { this.page = 1; this.load(); }, { deep: true });
            window.addEventListener('table-reload', () => this.load());
        },
        async load() {
            this.loading = true; this.error = '';
            try {
                const body = await getJson(this.url, { search: this.search, per_page: this.perPage, page: this.page, sort: this.sort, ...this.filters });
                this.rows = body.data ?? [];
                this.meta = body.meta ?? this.meta;
                this.stats = body.stats ?? {};
                this.extra = body.extra ?? {};
                this.selected = [];
            } catch (e) { this.error = 'Could not load this list. Check your connection and try again.'; }
            this.loading = false;
        },
        go(p) { if (p >= 1 && p <= this.meta.last_page && p !== this.page) { this.page = p; this.load(); } },
        sortBy(key) { this.sort = this.sort === key ? '-' + key : key; this.load(); },
        sortIcon(key) { return this.sort === key ? '↑' : this.sort === '-' + key ? '↓' : ''; },
        /** Page buttons with gaps: 1 … 4 5 6 … 12 */
        get pages() {
            const last = this.meta.last_page, cur = this.meta.current_page, out = [];
            for (let p = 1; p <= last; p++) {
                if (p === 1 || p === last || Math.abs(p - cur) <= 1) out.push(p);
                else if (out[out.length - 1] !== '…') out.push('…');
            }
            return out;
        },
        get allChecked() { return this.rows.length > 0 && this.selected.length === this.rows.length; },
        toggleAll() { this.selected = this.allChecked ? [] : this.rows.map((r) => r.id); },
        remove(id) { this.rows = this.rows.filter((r) => r.id !== id); this.meta.total = Math.max(0, this.meta.total - 1); },
    };
}

// Status pills use general alert colours (never text on lime)
const TONES = {
    success: 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-400',
    warning: 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-400',
    error: 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
    info: 'bg-sky-50 text-sky-700 dark:bg-sky-500/15 dark:text-sky-400',
    neutral: 'bg-gray-100 text-gray-700 dark:bg-white/5 dark:text-gray-300',
    dark: 'bg-gray-900 text-[#D4FF3D] dark:bg-[#D4FF3D] dark:text-gray-900',
};
export const pill = (tone) => 'inline-flex shrink-0 items-center whitespace-nowrap rounded-full px-2.5 py-0.5 text-xs font-medium ' + (TONES[tone] ?? TONES.neutral);

let apex;
const isDark = () => document.documentElement.classList.contains('dark');
// Sharet UI colours: send blue, Sharet lime (deepened to read on white), money-in green, withdraw orange, pay violet, abroad teal
const PALETTE = ['#2F6BFF', '#8DB511', '#1F9D55', '#F2711C', '#7A4DFF', '#0EA5A4'];

export function chart({ url = null, options = null, params = {}, height = 300 } = {}) {
    return {
        empty: false, loading: true, instance: null, params: { ...params },
        async init() {
            apex ??= (await import('apexcharts')).default;
            await this.draw();
            this._obs = new MutationObserver(() => this.instance?.updateOptions(this.theme(), false, false));
            this._obs.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
            this.$watch('params', () => this.draw(), { deep: true });
        },
        destroy() { this._obs?.disconnect(); this.instance?.destroy(); },
        theme() {
            const dark = isDark(), text = dark ? '#9CA3AF' : '#6B7280';
            return {
                theme: { mode: dark ? 'dark' : 'light' },
                chart: { background: 'transparent', foreColor: text },
                grid: { borderColor: dark ? 'rgba(255,255,255,0.06)' : '#E5E7EB', strokeDashArray: 4 },
                tooltip: { theme: dark ? 'dark' : 'light' },
            };
        },
        async draw() {
            this.loading = true;
            const spec = url ? await getJson(url, this.params).catch(() => null) : options;
            this.loading = false;
            const series = spec?.series ?? [];
            this.empty = !series.length || series.every((s) => (typeof s === 'number' ? s === 0 : !(s.data ?? []).some((v) => Number(v?.y ?? v) > 0)));
            const t = this.theme();
            const opts = {
                colors: PALETTE, dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontFamily: 'Outfit Variable, Outfit' },
                stroke: { curve: 'smooth', width: spec?.type === 'bar' ? 0 : 2 },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%', borderRadiusApplication: 'end' }, pie: { donut: { size: '68%' } } },
                fill: spec?.type === 'area' ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0 } } : {},
                xaxis: { axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { formatter: (v) => (Number.isInteger(v) ? v.toLocaleString() : v) } },
                ...spec,
                ...t,
                chart: { height, fontFamily: 'Outfit Variable, Outfit', toolbar: { show: false }, zoom: { enabled: false }, ...t.chart, ...(spec?.chart ?? {}), type: spec?.type ?? 'bar' },
            };
            if (this.instance) { this.instance.destroy(); this.instance = null; }
            if (this.empty) return;
            this.instance = new apex(this.$refs.canvas, opts);
            this.instance.render();
        },
    };
}

/** One alert modal for the whole dashboard. confirm() resolves true/false; alert() resolves when closed. */
export function alertModal() {
    return {
        open: false, title: '', message: '', tone: 'info', details: [], confirmText: 'OK', cancelText: 'Cancel', cancel: false, resolve: null,
        show(detail) {
            Object.assign(this, { title: '', message: '', tone: 'info', details: [], confirmText: 'OK', cancelText: 'Cancel', cancel: false }, detail);
            this.resolve = detail.resolve; this.open = true;
            this.$nextTick(() => this.$refs.confirm?.focus());
        },
        close(result) { this.open = false; this.resolve?.(result); this.resolve = null; },
    };
}

export function installHelpers(sharet) {
    const ask = (detail) => new Promise((resolve) => window.dispatchEvent(new CustomEvent('alert-modal', { detail: { ...detail, resolve } })));
    sharet.confirm = (detail) => ask({ tone: 'warning', confirmText: 'Continue', cancel: true, ...detail });
    sharet.alert = (detail) => ask({ tone: 'info', confirmText: 'OK', cancel: false, ...(typeof detail === 'string' ? { message: detail } : detail) });
    sharet.panel = (name, payload = {}) => window.dispatchEvent(new CustomEvent('slide-over', { detail: { name, payload } }));
    sharet.pill = pill;
    sharet.money = (v, cur = 'KES') => `${cur} ${Number(v ?? 0).toLocaleString('en-KE', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
    sharet.number = (v) => Number(v ?? 0).toLocaleString('en-KE');
}

/** Profile pages: loads /profile/data once; forms post through sharet.send and keep errors per field. */
export function profile(url) {
    return {
        data: { user: {}, kyc: {}, level: { basic: {}, standard: {} }, wallet: {} }, loading: true, errors: {}, busy: false,
        async init() { await this.load(); },
        async load() { this.data = await getJson(url); this.loading = false; },
        async submit(method, target, body) {
            this.busy = true; this.errors = {};
            try { const r = await window.sharet.send(method, target, body); await this.load(); return r; }
            catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])); }
            finally { this.busy = false; }
        },
        percent(part) { return part?.of ? Math.round((part.done / part.of) * 100) : 0; },
        /** The three selfie photos shown as one card: done when all three are in. */
        selfieCard(center) {
            const poses = (this.data.documents || []).filter((x) => x.type.startsWith('selfie_'));
            return { ...center, type: 'selfie', label: 'Selfie', hint: 'One quick selfie: look straight, then turn left and right. Taken for you.',
                poses, uploaded: poses.length === 3 && poses.every((x) => x.uploaded), url: null, at: center.at };
        },
        docError: {},
        /** Upload one verification photo: a File (checked for clarity first) or a data: URL. */
        async uploadDoc(type, source, check = { minWidth: 1000, minHeight: 600 }) {
            this.docError = { ...this.docError, [type]: '' };
            if (source instanceof File) {
                const q = await checkPhoto(source, check).catch((e) => ({ ok: false, reason: e.message }));
                if (!q.ok) { this.docError = { ...this.docError, [type]: q.reason }; return false; }
                const f = new FormData(); f.append('file', source); source = f;
            } else { source = { image: source }; }
            try { await window.sharet.send('POST', `/profile/documents/${type}`, source); await this.load(); return true; }
            catch (e) { this.docError = { ...this.docError, [type]: Object.values(e.errors || {})[0]?.[0] || e.body?.message || 'Upload failed. Try again.' }; return false; }
        },
    };
}

/**
 * Verification photos. checkPhoto() rejects images that are too small, too dark/bright or blurry
 * (variance of a Laplacian on a downscaled greyscale copy), so ID pictures arrive clear and readable.
 * kycCamera() drives the selfie camera; signaturePad() the drawn signature. Both produce data: URLs.
 */
export async function checkPhoto(source, { minWidth = 1000, minHeight = 600 } = {}) {
    const img = await new Promise((resolve, reject) => {
        const i = new Image();
        i.onload = () => resolve(i);
        i.onerror = () => reject(new Error('That file is not a photo we can read.'));
        i.src = typeof source === 'string' ? source : URL.createObjectURL(source);
    });
    const w = img.naturalWidth, h = img.naturalHeight;
    if (Math.max(w, h) < minWidth || Math.min(w, h) < minHeight) {
        return { ok: false, reason: `Too small (${w}×${h}). Use your camera at full resolution so the text is readable.` };
    }
    const scale = 400 / Math.max(w, h), cw = Math.round(w * scale), ch = Math.round(h * scale);
    const c = document.createElement('canvas'); c.width = cw; c.height = ch;
    const ctx = c.getContext('2d', { willReadFrequently: true }); ctx.drawImage(img, 0, 0, cw, ch);
    const px = ctx.getImageData(0, 0, cw, ch).data, g = new Float32Array(cw * ch);
    let sum = 0;
    for (let i = 0; i < g.length; i++) { g[i] = 0.299 * px[i * 4] + 0.587 * px[i * 4 + 1] + 0.114 * px[i * 4 + 2]; sum += g[i]; }
    const mean = sum / g.length;
    if (mean < 55) return { ok: false, reason: 'Too dark. Move to better light and try again.' };
    if (mean > 225) return { ok: false, reason: 'Too bright or washed out. Avoid glare and direct flash.' };
    let n = 0, m = 0, m2 = 0;
    for (let y = 1; y < ch - 1; y++) for (let x = 1; x < cw - 1; x++) {
        const i = y * cw + x, l = 4 * g[i] - g[i - 1] - g[i + 1] - g[i - cw] - g[i + cw];
        n++; const d = l - m; m += d / n; m2 += d * (l - m);
    }
    const sharpness = m2 / n;
    if (sharpness < 40) return { ok: false, reason: 'Blurry. Hold the phone steady, tap to focus and try again.' };
    return { ok: true, width: w, height: h };
}

export function kycCamera() {
    return {
        stream: null, error: '', shot: null, facing: 'user',
        async start() {
            this.error = ''; this.shot = null;
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: this.facing, width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false });
                this.$refs.video.srcObject = this.stream; await this.$refs.video.play();
            } catch (e) { this.error = 'We could not open your camera. Allow camera access in your browser, or upload a photo instead.'; }
        },
        stop() { this.stream?.getTracks().forEach((t) => t.stop()); this.stream = null; },
        async capture() {
            const v = this.$refs.video, c = document.createElement('canvas');
            c.width = v.videoWidth; c.height = v.videoHeight;
            const ctx = c.getContext('2d'); ctx.translate(c.width, 0); ctx.scale(-1, 1); ctx.drawImage(v, 0, 0);
            const url = c.toDataURL('image/jpeg', 0.92);
            const check = await checkPhoto(url, { minWidth: 640, minHeight: 480 });
            if (!check.ok) { this.error = check.reason; return; }
            this.shot = url; this.stop();
        },
        destroy() { this.stop(); },
    };
}

/**
 * One guided selfie that yields the three photos staff need (straight, left, right). The camera watches the light
 * (dark, washed out, or lit from behind) and holds still; each pose is taken by itself once it has been good for about a
 * second, and the turns only count when the head has visibly moved from the straight shot. Each photo uploads in the
 * background while the next pose is being taken. `upload(type, dataUrl)` is the page's uploadDoc.
 */
export const SELFIE_STEPS = [
    { type: 'selfie_center', say: 'Look straight at the camera' },
    { type: 'selfie_left', say: 'Slowly turn your head to the left' },
    { type: 'selfie_right', say: 'Now slowly turn to the right' },
];

/** Light and a tiny greyscale thumbnail of the video frame: overall brightness, the face oval vs the edges, sharpness. */
function frameStats(video) {
    const w = 64, h = 48, c = frameStats.c || (frameStats.c = document.createElement('canvas'));
    c.width = w; c.height = h;
    const ctx = c.getContext('2d', { willReadFrequently: true }); ctx.drawImage(video, 0, 0, w, h);
    const px = ctx.getImageData(0, 0, w, h).data, g = new Float32Array(w * h);
    let all = 0, face = 0, nf = 0, edge = 0, ne = 0;
    for (let i = 0; i < g.length; i++) {
        g[i] = 0.299 * px[i * 4] + 0.587 * px[i * 4 + 1] + 0.114 * px[i * 4 + 2]; all += g[i];
        const x = (i % w) / w - 0.5, y = Math.floor(i / w) / h - 0.5;
        if ((x * x) / 0.08 + (y * y) / 0.14 < 1) { face += g[i]; nf++; } else if (Math.abs(x) > 0.38) { edge += g[i]; ne++; }
    }
    let lap = 0;
    for (let y = 1; y < h - 1; y++) for (let x = 1; x < w - 1; x++) { const i = y * w + x; lap += Math.abs(4 * g[i] - g[i - 1] - g[i + 1] - g[i - w] - g[i + w]); }
    return { mean: all / g.length, face: face / nf, edge: edge / ne, sharp: lap / ((w - 2) * (h - 2)), grey: g };
}

function frameDiff(a, b) {
    let d = 0;
    for (let i = 0; i < a.length; i++) d += Math.abs(a[i] - b[i]);
    return d / a.length;
}

export function kycSelfie(upload) {
    return {
        stream: null, error: '', step: 0, hint: '', good: 0, now: 0, shots: {}, sent: {}, timer: null, last: null, base: null, since: 0, done: false,
        get current() { return SELFIE_STEPS[this.step]; },
        async start() {
            this.stop(); Object.assign(this, { error: '', step: 0, good: 0, shots: {}, sent: {}, base: null, done: false, since: Date.now() });
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: { ideal: 1280 }, height: { ideal: 960 } }, audio: false });
                this.$refs.video.srcObject = this.stream; await this.$refs.video.play();
                this.timer = setInterval(() => this.tick(), 200);
            } catch (e) { this.error = 'We could not open your camera. Allow camera access in your browser, or upload photos instead.'; }
        },
        stop() { clearInterval(this.timer); this.timer = null; this.stream?.getTracks().forEach((t) => t.stop()); this.stream = null; },
        /** Every 200 ms: is the light right and the picture steady? Five good frames in a row (about a second) takes the photo. */
        tick() {
            const v = this.$refs.video;
            this.now = Date.now();
            if (!v?.videoWidth || this.done) return;
            const s = frameStats(v), moved = this.last ? frameDiff(s.grey, this.last) : 99;
            this.last = s.grey;
            let hint = '';
            if (s.mean < 60 || s.face < 55) hint = 'Too dark. Face a window or a lamp.';
            else if (s.mean > 215) hint = 'Too bright. Move out of direct light.';
            else if (s.edge - s.face > 45) hint = 'The light is behind you. Turn to face the light.';
            else if (s.sharp < 6) hint = 'Hold the phone steady.';
            else if (moved > 14) hint = 'Hold still…';
            else if (this.step > 0 && this.base && frameDiff(s.grey, this.base) < 9) hint = this.current.say.replace('Slowly ', '').replace('Now slowly', 'Turn') + ' a little more';
            this.hint = hint;
            this.good = hint ? 0 : this.good + 1;
            if (this.good >= 5) this.take(s);
        },
        /** Takes this pose's photo (mirrored like the preview), uploads it in the background and moves on. */
        take(stats = null) {
            const v = this.$refs.video, c = document.createElement('canvas');
            c.width = v.videoWidth; c.height = v.videoHeight;
            const ctx = c.getContext('2d'); ctx.translate(c.width, 0); ctx.scale(-1, 1); ctx.drawImage(v, 0, 0);
            const url = c.toDataURL('image/jpeg', 0.92), type = this.current.type;
            this.shots = { ...this.shots, [type]: url };
            if (this.step === 0) this.base = (stats || frameStats(v)).grey;
            this.sent = { ...this.sent, [type]: 'sending' };
            upload(type, url).then((ok) => { this.sent = { ...this.sent, [type]: ok ? 'done' : 'failed' }; });
            this.good = 0; this.since = Date.now();
            if (this.step < SELFIE_STEPS.length - 1) { this.step++; } else { this.done = true; this.stop(); }
        },
        /** After 12 seconds on one pose, let the person take it themselves. */
        get slow() { return !this.done && this.stream && this.now - this.since > 12000; },
        get failed() { return SELFIE_STEPS.filter((p) => this.sent[p.type] === 'failed').map((p) => p.type); },
        get finished() { return this.done && SELFIE_STEPS.every((p) => this.sent[p.type] === 'done'); },
        /** Re-sends any photo whose upload failed. */
        retry() { this.failed.forEach((t) => { this.sent = { ...this.sent, [t]: 'sending' }; upload(t, this.shots[t]).then((ok) => { this.sent = { ...this.sent, [t]: ok ? 'done' : 'failed' }; }); }); },
        destroy() { this.stop(); },
    };
}

export function signaturePad() {
    return {
        drawing: false, last: null, signed: false, ctx: null,
        init() {
            const c = this.$refs.pad, r = c.getBoundingClientRect(), d = window.devicePixelRatio || 1;
            c.width = r.width * d; c.height = r.height * d;
            this.ctx = c.getContext('2d'); this.ctx.scale(d, d);
            Object.assign(this.ctx, { lineWidth: 2.4, lineCap: 'round', lineJoin: 'round', strokeStyle: '#0A0A0C' });
        },
        pos(e) { const r = this.$refs.pad.getBoundingClientRect(); return { x: e.clientX - r.left, y: e.clientY - r.top }; },
        down(e) { this.drawing = true; this.last = this.pos(e); this.$refs.pad.setPointerCapture(e.pointerId); },
        move(e) { if (!this.drawing) return; const p = this.pos(e); this.ctx.beginPath(); this.ctx.moveTo(this.last.x, this.last.y); this.ctx.lineTo(p.x, p.y); this.ctx.stroke(); this.last = p; this.signed = true; },
        up() { this.drawing = false; },
        clear() { const c = this.$refs.pad; this.ctx.clearRect(0, 0, c.width, c.height); this.signed = false; },
        image() {
            // White background so the PNG reads on any viewer
            const c = this.$refs.pad, out = document.createElement('canvas'); out.width = c.width; out.height = c.height;
            const o = out.getContext('2d'); o.fillStyle = '#fff'; o.fillRect(0, 0, out.width, out.height); o.drawImage(c, 0, 0);
            return out.toDataURL('image/png');
        },
    };
}

/**
 * Paste an image (⌘V / Ctrl+V) into any image upload on the page. It goes to the upload you last
 * pointed at or clicked, or to the only one visible, and runs exactly like choosing a file
 * (same change handler, so the same size and clarity checks apply).
 */
export function installImagePaste() {
    let target = null;
    const isImageInput = (el) => el?.matches?.('input[type=file]') && (el.accept || 'image/').includes('image');
    const visible = (el) => !!(el.closest('label, div')?.getClientRects().length);
    const near = (e) => {
        const box = e.target.closest?.('label, [data-paste-zone], .rounded-xl, .rounded-2xl');
        const input = box?.querySelector?.('input[type=file]');
        if (isImageInput(input)) target = input;
    };
    document.addEventListener('pointerover', near, true);
    document.addEventListener('focusin', near, true);

    document.addEventListener('paste', (e) => {
        if (e.target.closest?.('[contenteditable=true], textarea, input:not([type=file])')) return;
        const file = [...(e.clipboardData?.items || [])].find((i) => i.kind === 'file' && i.type.startsWith('image/'))?.getAsFile();
        if (!file) return;
        const inputs = [...document.querySelectorAll('input[type=file]')].filter((i) => isImageInput(i) && visible(i));
        const input = target && document.contains(target) && visible(target) ? target : (inputs.length === 1 ? inputs[0] : null);
        if (!input) { window.sharet.toast('Point at the upload you want, then paste the image.', 'error'); return; }
        e.preventDefault();
        const named = new File([file], `pasted-${Date.now()}.${(file.type.split('/')[1] || 'png').replace('jpeg', 'jpg')}`, { type: file.type });
        const dt = new DataTransfer(); dt.items.add(named);
        input.files = dt.files;
        input.dispatchEvent(new Event('change', { bubbles: true }));
        window.sharet.toast('Image pasted.');
    });
}
