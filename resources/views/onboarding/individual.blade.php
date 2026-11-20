{{-- Individual onboarding (step 2 of 2), Sharet UI. Same fields and route as before. --}}
@extends('layouts.app')
@section('title', 'Create your wallet')

@section('content')
<div class="mx-auto max-w-xl py-2 md:py-8">
  @include('onboarding.partials.head', ['lane' => 'Individual', 'title' => 'Create your wallet', 'lede' => 'One more step and your Sharet wallet is ready. No fees to hold money.'])

  <form method="POST" action="{{ route('onboarding.individual.store') }}" class="mt-6 space-y-4 rounded-[24px] bg-app-panel p-5 md:p-6">
    @csrf
    <div class="grid gap-4 sm:grid-cols-2">
      <x-ui.form-input as="select" label="Country" name="country" required>
        @include('onboarding.partials.countries')
      </x-ui.form-input>
      <x-ui.form-input label="City" name="city" :value="old('city')" autocomplete="address-level2" placeholder="Nairobi" />
    </div>
    <x-ui.form-input as="textarea" label="What will you use Sharet for? (optional)" name="use_case" rows="3">{{ old('use_case') }}</x-ui.form-input>
    @include('onboarding.partials.submit', ['text' => 'Create my wallet'])
  </form>
</div>
@endsection
