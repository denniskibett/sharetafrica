{{-- Developer onboarding (step 2 of 2), Sharet UI. Same fields and route as before. --}}
@extends('layouts.app')
@section('title', 'Developer onboarding')

@section('content')
<div class="mx-auto max-w-xl py-2 md:py-8">
  @include('onboarding.partials.head', ['lane' => 'Developer', 'title' => 'Build on Sharet', 'lede' => 'Tell us what you are building. Sandbox access is usually granted within one working day.'])

  <form method="POST" action="{{ route('onboarding.developer.store') }}" class="mt-6 space-y-4 rounded-[24px] bg-app-panel p-5 md:p-6">
    @csrf
    <x-ui.form-input label="Company or project" name="company" :value="old('company')" autocomplete="organization" />
    <x-ui.form-input as="select" label="What are you interested in?" name="interest" required>
      @foreach (['sandbox' => 'Sandbox: I want to test the API', 'production_keys' => 'Production keys: I am ready to go live', 'white_label' => 'White-label wallet: my own branded wallet', 'licensing' => 'Licensing: discuss the full stack'] as $value => $text)
        <option value="{{ $value }}" @selected(old('interest') === $value)>{{ $text }}</option>
      @endforeach
    </x-ui.form-input>
    <x-ui.form-input as="textarea" label="Tell us what you're building" name="use_case" rows="5" required>{{ old('use_case') }}</x-ui.form-input>
    @include('onboarding.partials.submit', ['text' => 'Finish onboarding'])
  </form>
</div>
@endsection
