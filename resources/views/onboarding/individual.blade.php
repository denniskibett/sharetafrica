@extends('layouts.app')

@section('title', 'Wallet onboarding · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-10">
    <div class="mx-auto max-w-2xl">

        <div class="mb-8">
            <p class="text-xs uppercase tracking-wider text-gray-500">Step 2 of 2 · Individual</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">Create your wallet</h1>
            <p class="mt-2 text-sm text-gray-500">One more step and your Sharet wallet is ready. No fees to hold. Send across East Africa.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.individual.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Country</label>
                    <select name="country" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]">
                        <option value="KE">Kenya</option>
                        <option value="TZ">Tanzania</option>
                        <option value="UG">Uganda</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">What will you use Sharet for? (optional)</label>
                    <textarea name="use_case" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]">{{ old('use_case') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end">
                <button type="submit" class="inline-flex rounded-lg bg-[#D4FF3D] px-6 py-3 text-sm font-semibold text-gray-900 hover:brightness-95">
                    Create my wallet
                </button>
            </div>
        </form>

    </div>
</div>
@endsection