{{-- Role home: greeting + the role's tools from DashboardNav (same icons/colours as the sidebar).
     Expects $title (page heading), $groups (DashboardNav group titles to show), optional $intro,
     optional $walletOwner (User or Company with a bavix wallet) to show its balance. --}}
@php
    $tones = [
        'violet'  => 'bg-violet-100 text-violet-600 dark:bg-violet-500/15 dark:text-violet-300',
        'emerald' => 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/15 dark:text-emerald-300',
        'sky'     => 'bg-sky-100 text-sky-600 dark:bg-sky-500/15 dark:text-sky-300',
        'amber'   => 'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300',
        'indigo'  => 'bg-indigo-100 text-indigo-600 dark:bg-indigo-500/15 dark:text-indigo-300',
        'teal'    => 'bg-teal-100 text-teal-600 dark:bg-teal-500/15 dark:text-teal-300',
        'rose'    => 'bg-rose-100 text-rose-600 dark:bg-rose-500/15 dark:text-rose-300',
        'orange'  => 'bg-orange-100 text-orange-600 dark:bg-orange-500/15 dark:text-orange-300',
        'slate'   => 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-300',
    ];
    $first = \Illuminate\Support\Str::before(auth()->user()->name, ' ');
    $sections = collect($groups)->map(fn ($g) => \App\Support\DashboardNav::roleGroup(auth()->user(), $g))->filter();
@endphp

<div class="mb-6 flex flex-wrap items-end justify-between gap-3">
  <div>
    <p class="text-sm text-gray-500 dark:text-gray-400">Welcome back, {{ $first }}</p>
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ $title }}</h1>
    @isset($intro)<p class="mt-1 max-w-2xl text-sm text-gray-500 dark:text-gray-400">{{ $intro }}</p>@endisset
  </div>
</div>

@isset($walletOwner)
  @php $wallet = $walletOwner->wallet; @endphp
  <div class="mb-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    <div class="relative overflow-hidden rounded-2xl bg-gray-900 p-6 text-white dark:bg-white/[0.06]">
      <div class="absolute -right-6 -top-6 h-28 w-28 rounded-full bg-[#D4FF3D]/20"></div>
      <p class="text-sm text-white/70">{{ $walletOwner instanceof \App\Models\Company ? $walletOwner->name.' balance' : 'Wallet balance' }}</p>
      <p class="mt-2 text-3xl font-semibold tracking-tight">KES {{ number_format((float) $wallet->balanceFloat, 2) }}</p>
      <p class="mt-3 text-xs text-white/60">{{ $wallet->exists ? 'Updated '.$wallet->updated_at->diffForHumans() : 'No money has moved yet.' }}</p>
    </div>
  </div>
@endisset

@foreach ($sections as $section)
  <h2 class="mb-3 mt-8 text-xs font-semibold uppercase tracking-wider text-gray-400 first:mt-0 dark:text-gray-500">{{ $section['title'] }}</h2>
  <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
    @foreach ($section['items'] as $item)
      @php $tag = $item['ready'] ? 'a' : 'div'; @endphp
      <{{ $tag }} @if ($item['ready']) href="{{ $item['url'] }}" @endif
         @class([
           'group flex items-center gap-4 rounded-2xl border border-gray-200 bg-white p-5 transition dark:border-gray-800 dark:bg-white/[0.03]',
           'hover:-translate-y-0.5 hover:border-gray-300 hover:shadow-sm dark:hover:border-gray-700' => $item['ready'],
         ])>
        <span @class(['flex h-12 w-12 shrink-0 items-center justify-center rounded-xl', $tones[$item['tone']] ?? $tones['slate'], 'opacity-60' => ! $item['ready']])>
          <x-dynamic-component :component="'heroicon-o-'.$item['icon']" class="h-6 w-6" />
        </span>
        <span class="min-w-0 flex-1">
          <span class="block truncate font-medium text-gray-900 dark:text-white">{{ $item['label'] }}</span>
          <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item['ready'] ? 'Open' : 'Coming soon' }}</span>
        </span>
        @if ($item['ready'])
          <x-heroicon-o-arrow-right class="h-4 w-4 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-gray-500 dark:text-gray-600" />
        @endif
      </{{ $tag }}>
    @endforeach
  </div>
@endforeach
