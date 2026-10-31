{{-- Click heatmap: the live page in a frame at the chosen device width, clicks drawn on a canvas above it. --}}
@extends('layouts.app')
@section('title', 'Click heatmap')

@section('content')
@php $widths = ['desktop' => 1280, 'tablet' => 820, 'mobile' => 390]; @endphp

<div x-data="heatmap(@js(route('admin.analytics.clicks')), @js(url('/')), @js($path), @js($device), {{ $days }}, @js($widths))" x-init="load()">
  <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
      <a href="{{ route('admin.analytics.index', ['days' => $days]) }}" class="text-sm text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white">← Website analytics</a>
      <h1 class="mt-1 text-2xl font-semibold text-gray-900 dark:text-white">Click heatmap</h1>
      <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Where people click on each page. Hotter colours mean more clicks. <span x-text="count + ' clicks shown'"></span></p>
    </div>
    <div class="flex flex-wrap gap-2">
      <select x-model="path" @change="load()" class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        @forelse ($paths as $p)
          <option value="{{ $p->path }}">{{ $p->path }} ({{ $p->clicks }})</option>
        @empty
          <option value="/">/ (no clicks yet)</option>
        @endforelse
      </select>
      <div class="flex rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-800 dark:bg-white/[0.03]">
        <template x-for="d in ['desktop', 'tablet', 'mobile']" :key="d">
          <button type="button" @click="device = d; load()" x-text="d.charAt(0).toUpperCase() + d.slice(1)" class="rounded-md px-3 py-1.5 text-sm font-medium"
                  :class="device === d ? 'bg-gray-900 text-white dark:bg-white dark:text-gray-900' : 'text-gray-600 dark:text-gray-400'"></button>
        </template>
      </div>
      <select onchange="location.search = '?days=' + this.value" class="h-10 rounded-lg border border-gray-300 bg-transparent px-3 text-sm text-gray-800 dark:border-gray-700 dark:bg-gray-900 dark:text-white/90">
        @foreach ($ranges as $value => $label)
          <option value="{{ $value }}" @selected($days === $value)>{{ $label }}</option>
        @endforeach
      </select>
    </div>
  </div>

  <div x-ref="stage" class="relative mx-auto overflow-hidden rounded-2xl border border-gray-200 bg-white dark:border-gray-800" :style="`max-width:${widths[device]}px`">
    <div class="relative origin-top-left" :style="`width:${widths[device]}px; transform: scale(${scale}); height:${frameH}px`">
      <iframe x-ref="frame" :src="site + path" class="block w-full border-0" :style="`height:${frameH}px`" @load="fit()" title="Page preview" tabindex="-1"></iframe>
      <canvas x-ref="canvas" class="pointer-events-none absolute left-0 top-0"></canvas>
    </div>
    <div :style="`height:${frameH * scale}px`" class="pointer-events-none absolute"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  function heatmap(endpoint, site, path, device, days, widths) {
    return {
      path, device, widths, site: site.replace(/\/$/, ''), points: [], count: 0, frameH: 900, scale: 1,
      async load() {
        const r = await fetch(`${endpoint}?path=${encodeURIComponent(this.path)}&device=${this.device}&days=${days}`, { headers: { Accept: 'application/json' } });
        const data = await r.json();
        this.points = data.points; this.count = data.count;
        this.$nextTick(() => this.fit());
      },
      fit() {
        const w = this.widths[this.device];
        this.scale = Math.min(1, this.$refs.stage.clientWidth / w);
        try { this.frameH = this.$refs.frame.contentDocument.documentElement.scrollHeight || 900; } catch (e) { this.frameH = 2000; }
        this.$refs.stage.style.height = (this.frameH * this.scale) + 'px';
        this.draw(w, this.frameH);
      },
      draw(w, h) {
        const c = this.$refs.canvas; c.width = w; c.height = h;
        const ctx = c.getContext('2d');
        // Intensity pass: soft black circles add up, then colourise by density
        const r = this.device === 'mobile' ? 22 : 30;
        this.points.forEach(([x, y]) => {
          const g = ctx.createRadialGradient(x * w, y * h, 0, x * w, y * h, r);
          g.addColorStop(0, 'rgba(0,0,0,0.25)'); g.addColorStop(1, 'rgba(0,0,0,0)');
          ctx.fillStyle = g; ctx.fillRect(x * w - r, y * h - r, r * 2, r * 2);
        });
        const img = ctx.getImageData(0, 0, w, h), d = img.data;
        for (let i = 0; i < d.length; i += 4) {
          const a = d[i + 3]; if (!a) continue;
          const t = Math.min(1, a / 200); // 0 cool → 1 hot
          d[i] = Math.round(255 * Math.min(1, t * 2)); d[i + 1] = Math.round(255 * (t < 0.5 ? t * 2 : 2 - t * 2)); d[i + 2] = Math.round(255 * Math.max(0, 1 - t * 2));
          d[i + 3] = Math.round(90 + 140 * t);
        }
        ctx.putImageData(img, 0, 0);
      },
    };
  }
</script>
@endpush
