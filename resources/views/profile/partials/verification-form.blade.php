{{-- Verification. Verified (basic) = M-Pesa number + ID photos + selfies, details read from the ID.
     Bank grade = the details below + signature. Stored encrypted. --}}
@php
    $select = 'h-12 w-full px-4 ui-input rounded-2xl border-0 bg-gray-100 text-[15px] focus:outline-none focus:ring-2 focus:ring-gray-900/70 dark:bg-white/[0.06] dark:text-white/90';
@endphp
<x-ui.card title="Verified account" subtitle="Your M-Pesa number, your ID and three selfies. Then you can send and receive at basic limits." class="mb-6">
  <x-slot:actions><span :class="sharet.pill(data.level.basic.complete ? 'success' : 'warning')" x-text="data.level.basic.complete ? 'Verified' : 'Not verified yet'"></span></x-slot:actions>
  <dl class="grid gap-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
    <div><dt class="text-gray-500">M-Pesa number</dt><dd class="font-medium text-gray-900 dark:text-white" x-text="data.user.phone || 'Not added yet'"></dd></div>
    <div><dt class="text-gray-500">Name on ID</dt><dd class="font-medium text-gray-900 dark:text-white" x-text="data.kyc.full_name || 'Read from your ID'"></dd></div>
    <div><dt class="text-gray-500">ID number</dt><dd class="font-medium text-gray-900 dark:text-white" x-text="data.kyc.id_number || 'Read from your ID'"></dd></div>
    <div><dt class="text-gray-500">Date of birth</dt><dd class="font-medium text-gray-900 dark:text-white" x-text="data.kyc.date_of_birth || 'Read from your ID'"></dd></div>
  </dl>
  <button type="button" x-show="!data.user.phone" @click="tab = 'personal'" class="mt-4 text-sm font-medium underline">Add your M-Pesa number in Personal details</button>
</x-ui.card>

<div class="mb-6">@include('profile.partials.documents')</div>

<form @submit.prevent="submit('PUT', '{{ route('profile.verification') }}', data.kyc)" class="space-y-6">
  <x-ui.card title="Bank grade" subtitle="What a bank asks when you open an account. Needed for loans, cards, trade payments, higher limits and to register a business.">
    <x-slot:actions><span :class="sharet.pill(data.level.standard.complete ? 'success' : 'neutral')" x-text="data.level.standard.complete ? 'Complete' : (data.level.standard.fields?.[0] ?? 0) + ' of ' + (data.level.standard.fields?.[1] ?? 0) + ' details'"></span></x-slot:actions>
    <div class="space-y-6">
      <div class="grid gap-5 md:grid-cols-3">
        <x-ui.field label="ID document" name="id_type" required>
          <select id="f-id_type" x-model="data.kyc.id_type" required class="{{ $select }}">
            <option value="">Choose</option><option value="national_id">National ID</option><option value="passport">Passport</option><option value="alien_id">Alien ID</option><option value="military_id">Military ID</option>
          </select>
        </x-ui.field>
        <x-ui.field label="ID / passport number" name="id_number" model="data.kyc.id_number" required autocomplete="off" />
        <x-ui.field label="Date of birth" name="date_of_birth" type="date" model="data.kyc.date_of_birth" required autocomplete="bday" />
      </div>
      <div class="grid gap-5 md:grid-cols-2">
        <x-ui.field label="KRA PIN" name="kra_pin" model="data.kyc.kra_pin" placeholder="A012345678Z" class="uppercase" />
        <x-ui.field label="Nationality" name="nationality" model="data.kyc.nationality" placeholder="Kenyan" />
      </div>
      <fieldset>
        <legend class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Where you live</legend>
        <div class="grid gap-5 md:grid-cols-3">
          <x-ui.field label="County" name="county" model="data.kyc.county" placeholder="Nairobi" />
          <x-ui.field label="Town" name="town" model="data.kyc.town" placeholder="Westlands" />
          <x-ui.field label="Street / building" name="address" model="data.kyc.address" autocomplete="street-address" />
        </div>
      </fieldset>
      <fieldset>
        <legend class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Work and money</legend>
        <div class="grid gap-5 md:grid-cols-2">
          <x-ui.field label="Occupation" name="occupation" model="data.kyc.occupation" placeholder="Software engineer" />
          <x-ui.field label="Employer or business" name="employer" model="data.kyc.employer" />
          <x-ui.field label="Monthly income" name="income_band">
            <select id="f-income_band" x-model="data.kyc.income_band" class="{{ $select }}">
              <option value="">Choose</option><option value="under_50k">Under KES 50,000</option><option value="50k_150k">KES 50,000 – 150,000</option><option value="150k_500k">KES 150,000 – 500,000</option><option value="over_500k">Over KES 500,000</option>
            </select>
          </x-ui.field>
          <x-ui.field label="Main source of funds" name="source_of_funds">
            <select id="f-source_of_funds" x-model="data.kyc.source_of_funds" class="{{ $select }}">
              <option value="">Choose</option><option value="salary">Salary</option><option value="business">Business income</option><option value="savings">Savings</option><option value="investments">Investments</option><option value="family">Family support</option><option value="other">Other</option>
            </select>
          </x-ui.field>
        </div>
      </fieldset>
      <fieldset>
        <legend class="mb-3 text-sm font-semibold text-gray-800 dark:text-white/90">Next of kin</legend>
        <div class="grid gap-5 md:grid-cols-3">
          <x-ui.field label="Full name" name="next_of_kin_name" model="data.kyc.next_of_kin_name" />
          <x-ui.field label="Phone" name="next_of_kin_phone" type="tel" model="data.kyc.next_of_kin_phone" />
          <x-ui.field label="Relationship" name="next_of_kin_relationship" model="data.kyc.next_of_kin_relationship" placeholder="Sister" />
        </div>
      </fieldset>
      <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-300">
        <input type="checkbox" x-model="data.kyc.pep" class="mt-0.5 h-4 w-4 rounded border-gray-300 text-gray-900">
        <span>I hold, or have held, a prominent public role (or am closely related to someone who has). <span class="text-gray-500">Banks are required to ask this.</span></span>
      </label>
    </div>
  </x-ui.card>

  <div class="flex items-center justify-between gap-4">
    <p class="text-xs text-gray-500 dark:text-gray-400">Encrypted at rest and only seen by our compliance team.</p>
    <x-ui.button type="submit" icon="check" x-bind:disabled="busy">Save details</x-ui.button>
  </div>
</form>

