{{-- Verification (KYC). Section 1 = Basic (M-Pesa grade). Section 2 = Full (bank grade). Stored encrypted. --}}
@php
    $select = 'h-11 w-full rounded-lg border border-gray-300 bg-transparent px-4 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white/90';
@endphp
<form @submit.prevent="submit('PUT', '{{ route('profile.verification') }}', data.kyc)" class="space-y-6">
  <x-ui.card title="Basic · M-Pesa grade" subtitle="The same details a mobile money agent asks for. Enough to send and receive across East Africa.">
    <x-slot:actions><span :class="sharet.pill(data.level.basic.complete ? 'success' : 'warning')" x-text="data.level.basic.complete ? 'Complete' : 'Needed'"></span></x-slot:actions>
    <div class="grid gap-5 md:grid-cols-3">
      <x-ui.field label="ID document" name="id_type" required>
        <select id="f-id_type" x-model="data.kyc.id_type" required class="{{ $select }}">
          <option value="">Choose</option><option value="national_id">National ID</option><option value="passport">Passport</option><option value="alien_id">Alien ID</option><option value="military_id">Military ID</option>
        </select>
      </x-ui.field>
      <x-ui.field label="ID number" name="id_number" model="data.kyc.id_number" required autocomplete="off" />
      <x-ui.field label="Date of birth" name="date_of_birth" type="date" model="data.kyc.date_of_birth" required autocomplete="bday" />
    </div>
    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">Your mobile number comes from Personal details: <span class="font-medium" x-text="data.user.phone || 'not added yet'"></span>.</p>
  </x-ui.card>

  <x-ui.card title="Full · bank grade" subtitle="What a bank asks when you open an account. Unlocks higher limits, trade payments and cards.">
    <x-slot:actions><span :class="sharet.pill(data.level.standard.complete ? 'success' : 'neutral')" x-text="data.level.standard.complete ? 'Complete' : 'Optional'"></span></x-slot:actions>
    <div class="space-y-6">
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
    <x-ui.button type="submit" icon="shield-check" x-bind:disabled="busy">Send for review</x-ui.button>
  </div>
</form>
