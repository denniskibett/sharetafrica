@extends('layouts.app')

@section('title', 'Merchant onboarding · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-10">
    <div class="mx-auto max-w-2xl">

        <div class="mb-8">
            <p class="text-xs uppercase tracking-wider text-gray-500">Step 2 of 2 · Merchant</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">Tell us about your business</h1>
            <p class="mt-2 text-sm text-gray-500">This takes a minute. Once submitted, our team verifies your details and your merchant wallet goes live.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.merchant.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Business name</label>
                    <input type="text" name="business_name" required value="{{ old('business_name') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                    @error('business_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Business type</label>
                    <select name="business_type" required class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]">
                        <option value="retail">Retail</option>
                        <option value="hospitality">Hospitality</option>
                        <option value="events">Events</option>
                        <option value="marketplace">Marketplace</option>
                        <option value="logistics">Logistics</option>
                        <option value="other">Other</option>
                    </select>
                </div>

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

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tax ID (KRA PIN, TIN)</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Rails you accept from today</label>
                    <input type="text" name="rails" value="{{ old('rails') }}" placeholder="M-PESA · MTN · Airtel · Card"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Approx. monthly volume</label>
                    <input type="text" name="monthly_volume" value="{{ old('monthly_volume') }}" placeholder="e.g. KES 500,000 – 2M"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
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