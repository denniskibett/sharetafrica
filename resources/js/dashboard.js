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
const PALETTE = ['#7C3AED', '#9CCB1F', '#0EA5E9', '#F59E0B', '#F43F5E', '#14B8A6'];

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
                colors: PALETTE, dataLabels: { enabled: false }, legend: { position: 'top', horizontalAlign: 'left', fontFamily: 'Outfit' },
                stroke: { curve: 'smooth', width: spec?.type === 'bar' ? 0 : 2 },
                plotOptions: { bar: { borderRadius: 4, columnWidth: '45%', borderRadiusApplication: 'end' }, pie: { donut: { size: '68%' } } },
                fill: spec?.type === 'area' ? { type: 'gradient', gradient: { opacityFrom: 0.35, opacityTo: 0 } } : {},
                xaxis: { axisBorder: { show: false }, axisTicks: { show: false } },
                yaxis: { labels: { formatter: (v) => (Number.isInteger(v) ? v.toLocaleString() : v) } },
                ...spec,
                ...t,
                chart: { height, fontFamily: 'Outfit', toolbar: { show: false }, zoom: { enabled: false }, ...t.chart, ...(spec?.chart ?? {}), type: spec?.type ?? 'bar' },
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
        open: false, title: '', message: '', tone: 'info', confirmText: 'OK', cancelText: 'Cancel', cancel: false, resolve: null,
        show(detail) {
            Object.assign(this, { title: '', message: '', tone: 'info', confirmText: 'OK', cancelText: 'Cancel', cancel: false }, detail);
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
    };
}
