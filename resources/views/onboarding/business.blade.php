@extends('layouts.app')

@section('title', 'Business onboarding · Sharet Africa')

@section('content')
<div class="container-fluid px-4 py-10">
    <div class="mx-auto max-w-2xl">

        <div class="mb-8">
            <p class="text-xs uppercase tracking-wider text-gray-500">Step 2 of 2 · Business</p>
            <h1 class="mt-2 text-3xl font-semibold text-gray-900">Trade account setup</h1>
            <p class="mt-2 text-sm text-gray-500">Tell us what you trade and where. This opens the trade finance application.</p>
        </div>

        <form method="POST" action="{{ route('onboarding.business.store') }}" class="rounded-2xl border border-gray-200 bg-white p-6">
            @csrf

            <div class="grid gap-5 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Business name</label>
                    <input type="text" name="business_name" required value="{{ old('business_name') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                    @error('business_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Registration number</label>
                    <input type="text" name="registration_no" value="{{ old('registration_no') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Tax ID</label>
                    <input type="text" name="tax_id" value="{{ old('tax_id') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" />
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

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Main trade corridors</label>
                    <select name="corridors[]" multiple class="mt-1 block w-full rounded-md border-gray-300 focus:border-[#D4FF3D] focus:ring-[#D4FF3D]" size="6">
                        @foreach(\App\Models\Corridor::grouped() as $group => $items)
                            <optgroup label="{{ $group }}">
                                @foreach($items as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple.</p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Approx. monthly trade volume</label>
                    <input type="text" name="monthly_volume" value="{{ old('monthly_volume') }}" placeholder="e.g. USD 50,000 – 200,000"
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