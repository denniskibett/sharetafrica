{{-- Merchant onboarding (step 2 of 2), Sharet UI. Same fields and route as before. --}}
@extends('layouts.app')
@section('title', 'Merchant onboarding')

@section('content')
<div class="mx-auto max-w-xl py-2 md:py-8">
  @include('onboarding.partials.head', ['lane' => 'Merchant', 'title' => 'Tell us about your business', 'lede' => 'This takes a minute. Our team checks your details, then your merchant wallet goes live.'])

  <form method="POST" action="{{ route('onboarding.merchant.store') }}" class="mt-6 space-y-4 rounded-[24px] bg-app-panel p-5 md:p-6">
    @csrf
    <x-ui.form-input label="Business name" name="business_name" :value="old('business_name')" required autocomplete="organization" />
    <div class="grid gap-4 sm:grid-cols-2">
      <x-ui.form-input as="select" label="Business type" name="business_type" required>
        @foreach (['retail' => 'Retail', 'hospitality' => 'Hospitality', 'events' => 'Events', 'marketplace' => 'Marketplace', 'logistics' => 'Logistics', 'other' => 'Other'] as $value => $text)
          <option value="{{ $value }}" @selected(old('business_type') === $value)>{{ $text }}</option>
        @endforeach
      </x-ui.form-input>
      <x-ui.form-input as="select" label="Country" name="country" required>
        @include('onboarding.partials.countries')
      </x-ui.form-input>
      <x-ui.form-input label="City" name="city" :value="old('city')" autocomplete="address-level2" />
      <x-ui.form-input label="Tax ID (KRA PIN, TIN)" name="tax_id" :value="old('tax_id')" />
    </div>
    <x-ui.form-input label="Rails you accept today" name="rails" :value="old('rails')" placeholder="M-PESA · Airtel · Card" />
    <x-ui.form-input label="Monthly volume, roughly" name="monthly_volume" :value="old('monthly_volume')" placeholder="e.g. KES 500,000 – 2M" />
    @include('onboarding.partials.submit', ['text' => 'Finish onboarding'])
  </form>
</div>
@endsection
