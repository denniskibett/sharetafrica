{{-- Business settings (tenant): bank-grade business account details. Data: CompanySettingsController@edit as JSON. --}}
@extends('layouts.app')
@section('title', 'Business settings')

@php $select = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90'; @endphp

@section('content')
<div x-data="{ d: { company: { directors: [], settlement: {} }, progress: {}, documents: [] }, loading: true, busy: false, errors: {}, docBusy: {}, docErr: {},
               async upload(type, file) { if (!file) return; this.docBusy = { ...this.docBusy, [type]: true }; this.docErr = { ...this.docErr, [type]: '' };
                 const body = new FormData(); body.append('file', file);
                 try { await sharet.send('POST', '{{ url('settings/business/documents') }}/' + type, body); await this.load() }
                 catch (e) { this.docErr = { ...this.docErr, [type]: Object.values(e.errors || {})[0]?.[0] || e.body?.message || 'That did not upload.' } }
                 this.docBusy = { ...this.docBusy, [type]: false } },
               async load() { this.d = await (await fetch('{{ route('settings.company') }}', { headers: { Accept: 'application/json' } })).json(); this.loading = false },
               async save() { this.busy = true; this.errors = {};
                 try { await sharet.send('PUT', '{{ route('settings.company.update') }}', this.d.company); await this.load() }
                 catch (e) { this.errors = Object.fromEntries(Object.entries(e.errors || {}).map(([k, v]) => [k, v[0]])) }
                 this.busy = false } }"
     x-init="load()">
  <x-ui.page-header title="Business settings" subtitle="The details a bank needs to open a business account. Complete them to accept and settle larger amounts.">
    @can('integrations.manage')<x-ui.button variant="secondary" icon="puzzle-piece" href="{{ route('integrations.index') }}">Integrations</x-ui.button>@endcan
    @canany(['developer.api_keys', 'merchant.accept'])<x-ui.button variant="secondary" icon="key" href="{{ route('api-keys.index') }}">API keys</x-ui.button>@endcanany
  </x-ui.page-header>

  <x-ui.stats :cols="3" class="mb-6">
    <x-ui.stat label="Business profile" value="d.progress.of ? Math.round(d.progress.done / d.progress.of * 100) + '% complete' : '—'" format="raw" icon="building-storefront" tone="lime" />
    <x-ui.stat label="Verification" value="d.verified ? 'Verified' : (d.review?.status === 'rejected' ? 'Needs changes' : (d.exists ? 'In review' : 'Not started'))" format="raw" icon="shield-check" tone="emerald" />
    <x-ui.stat label="Your verification" value="d.owner?.bank_grade ? 'Bank grade' : (d.owner?.personal ? 'Basic' : 'No account')" format="raw" icon="identification" tone="sky" />
  </x-ui.stats>

  <div x-show="!loading && !d.owner?.personal" x-cloak class="mb-6 flex flex-col gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100 sm:flex-row sm:items-center sm:justify-between">
    <p><strong>You need a verified personal account first.</strong> Only account holders can register a business, and you are listed as its first director.
      <span x-text="d.owner?.phone ? 'Verify your ID (photos and a quick selfie) to get one.' : 'Add your M-Pesa number to get one.'"></span></p>
    <x-ui.button x-show="!d.owner?.phone" href="{{ route('profile.edit') }}#personal">Add my M-Pesa number</x-ui.button>
    <x-ui.button x-show="d.owner?.phone" href="{{ route('profile.edit') }}#verification">Verify my ID</x-ui.button>
  </div>
  <div x-show="!loading && d.owner?.personal && !d.owner?.bank_grade" x-cloak class="mb-6 flex flex-col gap-3 rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm text-sky-900 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-100 sm:flex-row sm:items-center sm:justify-between">
    <p>Your business can't be fully verified until <strong>your own bank-grade verification</strong> is done (ID photos, selfies and signature).</p>
    <x-ui.button variant="secondary" href="{{ route('profile.edit') }}#verification">Finish my verification</x-ui.button>
  </div>

  <div x-show="!loading && !d.exists && d.owner?.personal" x-cloak class="mb-6 rounded-2xl border border-gray-200 bg-white p-5 text-sm text-gray-700 dark:border-gray-800 dark:bg-white/[0.03] dark:text-gray-300">
    <p><strong class="text-gray-900 dark:text-white">Start your business.</strong> Fill in its details and save: your business gets its own Sharet ID and you are its first director. Then upload its documents and our team verifies it. Your personal account stays as it is.</p>
  </div>

  <div x-show="!loading && d.account" x-cloak class="mb-6 flex flex-col gap-3 rounded-2xl bg-gray-900 p-5 text-sm text-white sm:flex-row sm:items-center sm:justify-between">
    <div>
      <p class="text-xs uppercase tracking-wide text-white/60">Business account</p>
      <p class="mt-1 text-base font-semibold"><span x-text="d.company.name"></span> · <code class="font-mono text-[#D4FF3D]" x-text="d.account?.sharet_id"></code> · <span x-text="'@' + d.account?.handle"></span></p>
      <p class="mt-1 text-white/70">Its own account, separate from your personal one. Payments, API keys and settlements run on it; live payments open once our team verifies the business.</p>
    </div>
    @canany(['developer.api_keys', 'merchant.accept'])<x-ui.button variant="secondary" icon="key" href="{{ route('api-keys.index') }}">Take payments</x-ui.button>@endcanany
  </div>

  <div x-show="!loading && !d.verified && d.review?.status === 'rejected'" x-cloak class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-5 text-sm text-red-900 dark:border-red-500/30 dark:bg-red-500/10 dark:text-red-100">
    <p><strong>Our team sent your business back.</strong> <span x-text="d.review?.reason"></span> Fix it below and save: we review it again.</p>
  </div>

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

    <x-ui.card title="Directors and owners" subtitle="You are listed first as the account holder. Add every other director and anyone who owns 10% or more.">
      <div class="space-y-4">
        <template x-for="(row, i) in d.company.directors" :key="i">
          <div class="grid items-end gap-3 rounded-xl border border-gray-200 p-4 dark:border-gray-800 md:grid-cols-[1.4fr_1fr_1fr_110px_auto]">
            <label class="text-sm"><span class="mb-1.5 flex items-center gap-2 font-medium text-gray-700 dark:text-gray-300">Full name <span x-show="row.self" :class="sharet.pill('dark')">You</span></span><input x-model="row.name" :readonly="row.self" class="{{ $select }} read-only:bg-gray-50 dark:read-only:bg-white/5"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">ID / passport</span><input x-model="row.id_number" :readonly="row.self" :placeholder="row.self ? 'From your verification' : ''" class="{{ $select }} read-only:bg-gray-50 dark:read-only:bg-white/5"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Role</span><input x-model="row.role" class="{{ $select }}" placeholder="Director"></label>
            <label class="text-sm"><span class="mb-1.5 block font-medium text-gray-700 dark:text-gray-300">Owns %</span><input type="number" min="0" max="100" x-model="row.shareholding" class="{{ $select }}"></label>
            <button type="button" x-show="!row.self" @click="d.company.directors.splice(i, 1)" class="h-11 rounded-lg px-3 text-gray-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-500/10" aria-label="Remove"><x-heroicon-o-trash class="h-5 w-5" /></button>
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

    <x-ui.card title="Documents" subtitle="PDF or a clear photo, up to 8 MB each. Stored privately: only you and our verification team can open them.">
      <p x-show="!d.exists" class="text-sm text-gray-500 dark:text-gray-400">Save your business details first, then add its documents here.</p>
      <ul x-show="d.exists" class="divide-y divide-gray-100 dark:divide-gray-800">
        <template x-for="doc in d.documents" :key="doc.type">
          <li class="flex flex-wrap items-center gap-3 py-3">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium text-gray-900 dark:text-white"><span x-text="doc.label"></span>
                <span x-show="!doc.required" class="ml-1 text-xs font-normal text-gray-500">optional</span></p>
              <p class="text-xs text-gray-500 dark:text-gray-400" x-text="doc.uploaded ? doc.name + ' · ' + doc.at : doc.hint"></p>
              <p x-show="docErr[doc.type]" x-text="docErr[doc.type]" class="mt-1 text-xs text-red-600 dark:text-red-400"></p>
            </div>
            <span :class="sharet.pill(doc.uploaded ? 'success' : (doc.required ? 'warning' : 'neutral'))" x-text="doc.uploaded ? 'Uploaded' : (doc.required ? 'Needed' : 'Optional')"></span>
            <a x-show="doc.url" :href="doc.url" target="_blank" rel="noopener" class="text-sm font-medium text-gray-700 underline dark:text-gray-300">View</a>
            <label class="inline-flex h-10 cursor-pointer items-center gap-2 rounded-full bg-gray-100 px-4 text-sm font-semibold text-gray-800 hover:bg-gray-200 dark:bg-white/[0.06] dark:text-gray-200"
                   :class="docBusy[doc.type] && 'pointer-events-none opacity-60'">
              <x-heroicon-o-arrow-up-tray class="h-4 w-4" /> <span x-text="docBusy[doc.type] ? 'Uploading…' : (doc.uploaded ? 'Replace' : 'Upload')"></span>
              <input type="file" accept="application/pdf,image/jpeg,image/png,image/webp" class="sr-only" @change="upload(doc.type, $event.target.files[0]); $event.target.value = ''">
            </label>
          </li>
        </template>
      </ul>
    </x-ui.card>

    <div class="flex justify-end"><x-ui.button type="submit" icon="check" x-bind:disabled="busy"><span x-text="d.exists ? 'Save business details' : 'Create my business'"></span></x-ui.button></div>
  </form>
</div>
@endsection
