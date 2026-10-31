{{-- Business settings (tenant): bank-grade business account details. Data: CompanySettingsController@edit as JSON. --}}
@extends('layouts.app')
@section('title', 'Business settings')

@php $select = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'; @endphp

@section('content')
<div x-data="{ d: { company: { directors: [], settlement: {} }, progress: {} }, loading: true, busy: false, errors: {},
               async load() { this.d = await (await fetch('{{ route('settings.company') }}', { headers: { Accept: 'application/json' } })).json(); this.loading = false },
               async save() { this.busy = true; this.errors = {};
                 try { await sharet.send('PUT', '{{ route('settings.company.update') }}', this.d.company); await this.load() }
                 catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])) }
                 this.busy = false } }"
     x-init="load()">
  <x-ui.page-header title="Business settings" subtitle="The details a bank needs to open a business account. Complete them to accept and settle larger amounts.">
    @if (Route::has('integrations.index'))<x-ui.button variant="secondary" icon="puzzle-piece" href="{{ route('integrations.index') }}">Integrations</x-ui.button>@endif
    @if (Route::has('api-keys.index'))<x-ui.button variant="secondary" icon="key" href="{{ route('api-keys.index') }}">API keys</x-ui.button>@endif
  </x-ui.page-header>

  <x-ui.stats :cols="3" class="mb-6">
    <x-ui.stat label="Business profile" value="d.progress.of ? Math.round(d.progress.done / d.progress.of * 100) + '% complete' : '—'" format="raw" icon="building-storefront" tone="lime" />
    <x-ui.stat label="Verification" value="d.verified ? 'Verified' : (d.exists ? 'In review' : 'Not started')" format="raw" icon="shield-check" tone="emerald" />
    <x-ui.stat label="Directors" value="(d.company.directors || []).filter(x => x.name).length" icon="users" tone="sky" />
  </x-ui.stats>

  <form x-show="!loading" x-cloak @submit.prevent="save()" class="space-y-6">
    <x-ui.card title="Business" subtitle="As registered with the Business Registration Service (BRS).">
      <div class="grid gap-5 md:grid-cols-2">
        <x-ui.field label="Trading name" name="name" model="d.company.name" required />
        <x-ui.field label="Registered (legal) name" name="legal_name" model="d.company.legal_name" />
        <x-ui.field label="Business type" name="type">
          <select id="f-type" x-model="d.company.type" class="{{ $select }}">
            <option value="">Choose</option><option value="sole_proprietor">Sole proprietor</option><option value="partnership">Partnership</option><option value="limited_company">Limited company</option><option value="ngo">NGO / non-profit</option><option value="other">Other</option>
          </select>
        </x-ui.field>
        <x-ui.field label="Registration number" name="registration_number" model="d.company.registration_number" placeholder="PVT-XXXXXXX or BN-XXXXXXX" />
        <x-ui.field label="Date registered" name="registered_on" type="date" model="d.company.registered_on" />
        <x-ui.field label="KRA PIN" name="tax_id" model="d.company.tax_id" placeholder="P051234567X" />
        <x-ui.field label="VAT number" name="vat_number" model="d.company.vat_number" help="If you are VAT registered." />
        <x-ui.field label="Annual turnover" name="annual_turnover">
          <select id="f-annual_turnover" x-model="d.company.annual_turnover" class="{{ $select }}">
            <option value="">Choose</option><option value="under_5m">Under KES 5M</option><option value="5m_50m">KES 5M – 50M</option><option value="50m_500m">KES 50M – 500M</option><option value="over_500m">Over KES 500M</option>
          </select>
        </x-ui.field>
        <x-ui.field label="What the business does" name="nature_of_business" model="d.company.nature_of_business" placeholder="Wholesale of electronics" class="md:col-span-2" />
      </div>
    </x-ui.card>

    <x-ui.card title="Contact and address">
      <div class="grid gap-5 md:grid-cols-2">
        <x-ui.field label="Business email" name="email" type="email" model="d.company.email" />
        <x-ui.field label="Business phone" name="phone" type="tel" model="d.company.phone" />
        <x-ui.field label="Website" name="website" type="url" model="d.company.website" placeholder="https://" />
        <x-ui.field label="Country" name="country" model="d.company.country" />
        <x-ui.field label="Town / city" name="city" model="d.company.city" />
        <x-ui.field label="Physical address" name="address" model="d.company.address" placeholder="Building, street, floor" />
      </div>
    </x-ui.card>

    <x-ui.card title="Directors and owners" subtitle="Everyone who owns 10% or more, and every director.">
      <div class="space-y-4">
        <template x-for="(row, i) in d.company.directors" :key="i">
          <div class="grid items-end gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-800 md:grid-cols-[1.4fr_1fr_1fr_110px_auto]">
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Full name</span><input x-model="row.name" class="{{ $select }}"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">ID / passport</span><input x-model="row.id_number" class="{{ $select }}"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Role</span><input x-model="row.role" class="{{ $select }}" placeholder="Director"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Owns %</span><input type="number" min="0" max="100" x-model="row.shareholding" class="{{ $select }}"></label>
            <button type="button" @click="d.company.directors.splice(i, 1)" class="h-11 rounded-lg px-3 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" aria-label="Remove"><x-heroicon-o-trash class="h-5 w-5" /></button>
          </div>
        </template>
        <x-ui.button variant="secondary" icon="plus" @click="d.company.directors.push({ name: '', id_number: '', role: 'Director', shareholding: '' })">Add person</x-ui.button>
      </div>
    </x-ui.card>

    <x-ui.card title="Settlement account" subtitle="Where we send your money when you settle out of Sharet.">
      <div class="mb-5 inline-flex rounded-lg bg-gray-100 p-0.5 dark:bg-gray-900">
        <button type="button" @click="d.company.settlement.method = 'bank'" :class="d.company.settlement.method !== 'mpesa' ? 'bg-white shadow-theme-xs dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-500'" class="rounded-md px-4 py-2 text-sm font-medium">Bank account</button>
        <button type="button" @click="d.company.settlement.method = 'mpesa'" :class="d.company.settlement.method === 'mpesa' ? 'bg-white shadow-theme-xs dark:bg-gray-800 text-gray-900 dark:text-white' : 'text-gray-500'" class="rounded-md px-4 py-2 text-sm font-medium">M-Pesa paybill / till</button>
      </div>
      <div x-show="d.company.settlement.method !== 'mpesa'" class="grid gap-5 md:grid-cols-2">
        <x-ui.field label="Bank" name="settlement.bank" model="d.company.settlement.bank" placeholder="Equity Bank" />
        <x-ui.field label="Branch" name="settlement.branch" model="d.company.settlement.branch" />
        <x-ui.field label="Account name" name="settlement.account_name" model="d.company.settlement.account_name" help="Must match the registered business name." />
        <x-ui.field label="Account number" name="settlement.account_number" model="d.company.settlement.account_number" autocomplete="off" />
      </div>
      <div x-show="d.company.settlement.method === 'mpesa'" class="grid gap-5 md:grid-cols-2">
        <x-ui.field label="Paybill or till number" name="settlement.paybill" model="d.company.settlement.paybill" />
      </div>
      <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Directors' ID numbers and account details are encrypted at rest.</p>
    </x-ui.card>

    <div class="flex justify-end"><x-ui.button type="submit" icon="check" x-bind:disabled="busy">Save business details</x-ui.button></div>
  </form>
</div>
@endsection
