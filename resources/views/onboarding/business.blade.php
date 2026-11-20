{{-- Business (trade) onboarding (step 2 of 2), Sharet UI. Same fields and route as before. --}}
@extends('layouts.app')
@section('title', 'Business onboarding')

@section('content')
<div class="mx-auto max-w-xl py-2 md:py-8">
  @include('onboarding.partials.head', ['lane' => 'Business', 'title' => 'Trade account setup', 'lede' => 'Tell us what you trade and where. This opens the trade finance application.'])

  <form method="POST" action="{{ route('onboarding.business.store') }}" class="mt-6 space-y-4 rounded-[24px] bg-app-panel p-5 md:p-6">
    @csrf
    <x-ui.form-input label="Business name" name="business_name" :value="old('business_name')" required autocomplete="organization" />
    <div class="grid gap-4 sm:grid-cols-2">
      <x-ui.form-input label="Registration number" name="registration_no" :value="old('registration_no')" />
      <x-ui.form-input label="Tax ID" name="tax_id" :value="old('tax_id')" />
      <x-ui.form-input as="select" label="Country" name="country" required>
        @include('onboarding.partials.countries')
      </x-ui.form-input>
      <x-ui.form-input label="City" name="city" :value="old('city')" autocomplete="address-level2" />
    </div>
    <x-ui.form-input as="select" label="Main trade corridors" name="corridors[]" id="corridors" multiple size="6" hint="Hold Ctrl or Cmd to pick more than one.">
      @foreach (\App\Models\Corridor::grouped() as $group => $items)
        <optgroup label="{{ $group }}">
          @foreach ($items as $code => $label)
            <option value="{{ $code }}" @selected(in_array($code, (array) old('corridors', []), true))>{{ $label }}</option>
          @endforeach
        </optgroup>
      @endforeach
    </x-ui.form-input>
    <x-ui.form-input label="Monthly trade volume, roughly" name="monthly_volume" :value="old('monthly_volume')" placeholder="e.g. USD 50,000 – 200,000" />
    @include('onboarding.partials.submit', ['text' => 'Finish onboarding'])
  </form>
</div>
@endsection
