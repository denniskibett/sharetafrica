@extends('layouts.app')

@section('title', 'Developer onboarding · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-10">
    <div class="mx-auto max-w-2xl">

        <div class="mb-8">
            <p class="text-xs uppercase tracking-wider text-gray-500">Step 2 of 2 · Developer</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">Build on Sharet</h1>
            <p class="mt-2 text-sm text-gray-500">Tell us what you're building. Sandbox access is usually granted within one working day.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.developer.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6">
            @csrf

            <div class="grid gap-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Company / project</label>
                    <input type="text" name="company" value="{{ old('company') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">What are you interested in?</label>
                    <select name="interest" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]">
                        <option value="sandbox">Sandbox — I want to test the API</option>
                        <option value="production_keys">Production keys — I'm ready to go live</option>
                        <option value="white_label">White-label WaaS — I want my own branded wallet</option>
                        <option value="licensing">Licensing — I want to discuss the full stack</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tell us what you're building</label>
                    <textarea name="use_case" rows="5" required
                              class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]">{{ old('use_case') }}</textarea>
                    @error('use_case')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex rounded-lg bg-[#D4FF3D] px-6 py-3 text-sm font-semibold text-gray-900 hover:brightness-95">
                    Finish onboarding
                </button>
            </div>
        </form>

    </div>
</div>
@endsection