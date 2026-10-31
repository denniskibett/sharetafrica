@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div x-data="{ pageName: 'System Settings' }">
    @include('partials.breadcrumb', ['pageName' => 'System Settings'])
</div>
    
<div class="container mx-auto px-4 py-6">
    <!-- Header with Actions -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">System Settings</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-2">Manage your application configuration</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-3">
                <button onclick="toggleMaintenance()" 
                        class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 transition rounded-lg border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ $system->maintenance_mode ? 'Disable Maintenance' : 'Enable Maintenance' }}
                </button>
                
                <a href="{{ route('system.clear-cache') }}" 
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-5 py-3.5 text-sm font-medium text-gray-700 shadow-theme-xs transition hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear Cache
                </a>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="mb-6 p-4 rounded-lg bg-green-50 border border-green-200 dark:bg-green-900/20 dark:border-green-800">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-600 dark:text-green-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-green-800 dark:text-green-300">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    <!-- Main Settings Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <div class="flex space-x-1 overflow-x-auto px-4">
                <button type="button" onclick="switchTab('basic')" data-tab="basic" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Basic Info
                </button>
                <button type="button" onclick="switchTab('system')" data-tab="system" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    System Config
                </button>
                <button type="button" onclick="switchTab('colors')" data-tab="colors" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Colors
                </button>
                <button type="button" onclick="switchTab('notifications')" data-tab="notifications" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    Notifications
                </button>
                <button type="button" onclick="switchTab('security')" data-tab="security" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    Security
                </button>
                <button type="button" onclick="switchTab('social')" data-tab="social" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Social Media
                </button>
                <button type="button" onclick="switchTab('pages')" data-tab="pages" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Website Pages
                </button>
                <button type="button" onclick="switchTab('company')" data-tab="company" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    Company Info
                </button>
                <button type="button" onclick="switchTab('other')" data-tab="other" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Other
                </button>
            </div>
        </div>

        <!-- Tab Content -->
        <form action="{{ route('system.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Basic Information Tab -->
            <div id="basic-tab" class="tab-content space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Application Name *
                        </label>
                        <input type="text" name="name" value="{{ old('name', $system->name) }}" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Slogan
                        </label>
                        <input type="text" name="slogan" value="{{ old('slogan', $system->slogan) }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                    </div>
                </div>

                <!-- Logo Uploads -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Main Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Main Logo (Light)
                        </label>
                        <div class="flex flex-col gap-2">
                            @if($system->logo)
                                <img src="{{ SystemHelper::logoUrl('light') }}" alt="Logo" class="h-12 w-auto rounded-lg bg-white p-2">
                            @endif
                            <input type="file" name="logo" accept="image/*"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                    </div>

                    <!-- Dark Logo -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Dark Logo
                        </label>
                        <div class="flex flex-col gap-2">
                            @if($system->logo_dark)
                                <img src="{{ SystemHelper::logoUrl('dark') }}" alt="Dark Logo" class="h-12 w-auto rounded-lg bg-gray-800 p-2">
                            @endif
                            <input type="file" name="logo_dark" accept="image/*"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                    </div>

                    <!-- Logo Icon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Logo Icon
                        </label>
                        <div class="flex flex-col gap-2">
                            @if($system->logo_icon)
                                <img src="{{ SystemHelper::logoUrl('icon') }}" alt="Logo Icon" class="h-10 w-10 rounded-lg">
                            @endif
                            <input type="file" name="logo_icon" accept="image/*"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                    </div>

                    <!-- Favicon -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Favicon
                        </label>
                        <div class="flex flex-col gap-2">
                            @if($system->favicon)
                                <img src="{{ SystemHelper::faviconUrl() }}" alt="Favicon" class="h-8 w-8 rounded">
                            @endif
                            <input type="file" name="favicon" accept="image/*"
                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200">
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">Contact Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Email
                            </label>
                            <input type="email" name="contact_email" value="{{ old('contact_email', $system->contact_email) }}"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Contact Phone
                            </label>
                            <input type="text" name="contact_phone" value="{{ old('contact_phone', $system->contact_phone) }}"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Address
                            </label>
                            <textarea name="address" rows="2"
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('address', $system->address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Meta Information -->
                <div class="mt-6">
                    <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">SEO Information</h4>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Description
                            </label>
                            <textarea name="meta_description" rows="2"
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('meta_description', $system->meta_description) }}</textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Meta Keywords
                            </label>
                            <textarea name="meta_keywords" rows="2"
                                      class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('meta_keywords', $system->meta_keywords) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- System Configuration Tab -->
            <div id="system-tab" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Timezone *
                        </label>
                        <select name="timezone" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            @foreach($timezones as $tz)
                                <option value="{{ $tz }}" {{ old('timezone', $system->timezone) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Date Format *
                        </label>
                        <select name="date_format" required
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            <option value="Y-m-d" {{ old('date_format', $system->date_format) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                            <option value="d/m/Y" {{ old('date_format', $system->date_format) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                            <option value="m/d/Y" {{ old('date_format', $system->date_format) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                            <option value="d-m-Y" {{ old('date_format', $system->date_format) == 'd-m-Y' ? 'selected' : '' }}>DD-MM-YYYY</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Time Format
                        </label>
                        <select name="time_format"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            <option value="H:i:s" {{ old('time_format', $system->time_format) == 'H:i:s' ? 'selected' : '' }}>24-hour (HH:MM:SS)</option>
                            <option value="h:i:s A" {{ old('time_format', $system->time_format) == 'h:i:s A' ? 'selected' : '' }}>12-hour (HH:MM:SS AM/PM)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Pagination Limit *
                        </label>
                        <input type="number" name="pagination_limit" value="{{ old('pagination_limit', $system->pagination_limit) }}" min="5" max="100" required
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                    </div>
                </div>

                <!-- Currency Settings -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">Currency Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Currency *
                            </label>
                            <select name="currency" required
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                @foreach($currencies as $code => $name)
                                    <option value="{{ $code }}" {{ old('currency', $system->currency) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Currency Symbol *
                            </label>
                            <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $system->currency_symbol) }}" required
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Symbol Position
                            </label>
                            <select name="currency_position"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                <option value="before" {{ old('currency_position', $system->settings['currency_position'] ?? 'before') == 'before' ? 'selected' : '' }}>Before amount ($100)</option>
                                <option value="after" {{ old('currency_position', $system->settings['currency_position'] ?? 'before') == 'after' ? 'selected' : '' }}>After amount (100$)</option>
                                <option value="before_space" {{ old('currency_position', $system->settings['currency_position'] ?? 'before') == 'before_space' ? 'selected' : '' }}>Before with space ($ 100)</option>
                                <option value="after_space" {{ old('currency_position', $system->settings['currency_position'] ?? 'before') == 'after_space' ? 'selected' : '' }}>After with space (100 $)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Location Settings -->
                <div>
                    <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">Location Settings</h4>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Country --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Country
                            </label>

                            <select name="location_country"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                <option value="">Select Country</option>

                                @foreach($countries as $code => $name)
                                    <option value="{{ $code }}"
                                        {{ old('location_country', $system->location->country ?? '') === $code ? 'selected' : '' }}>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- City --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                City
                            </label>
                            <input type="text"
                                name="location_city"
                                value="{{ old('location_city', $system->location->city ?? '') }}"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        {{-- Location Name --}}
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Location Name
                            </label>
                            <input type="text"
                                name="location_name"
                                value="{{ old('location_name', $system->location->name ?? '') }}"
                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        {{-- Coordinates --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Latitude
                                </label>
                                <input type="text"
                                    name="location_latitude"
                                    value="{{ old('location_latitude', $system->location->latitude ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Longitude
                                </label>
                                <input type="text"
                                    name="location_longitude"
                                    value="{{ old('location_longitude', $system->location->longitude ?? '') }}"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Colors Tab -->
            <div id="colors-tab" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Primary Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Primary Color *
                        </label>
                        <div class="flex items-center space-x-4">
                            <input type="color" name="primary_color" 
                                   value="{{ old('primary_color', $system->primary_color) }}" required
                                   class="w-16 h-16 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <input type="text" name="primary_color" 
                                       value="{{ old('primary_color', $system->primary_color) }}" required
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <div class="w-10 h-10 rounded mr-3" style="background-color: {{ old('primary_color', $system->primary_color) }}"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Primary Color Preview</span>
                        </div>
                    </div>

                    <!-- Secondary Color -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-4">
                            Secondary Color *
                        </label>
                        <div class="flex items-center space-x-4">
                            <input type="color" name="secondary_color" 
                                   value="{{ old('secondary_color', $system->secondary_color) }}" required
                                   class="w-16 h-16 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600">
                            <div class="flex-1">
                                <input type="text" name="secondary_color" 
                                       value="{{ old('secondary_color', $system->secondary_color) }}" required
                                       class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                            </div>
                        </div>
                        <div class="mt-4 flex items-center">
                            <div class="w-10 h-10 rounded mr-3" style="background-color: {{ old('secondary_color', $system->secondary_color) }}"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Secondary Color Preview</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div id="notifications-tab" class="tab-content hidden space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Email Notifications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Receive important updates via email</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="email_notifications" value="0">
                            <input type="checkbox" name="email_notifications" value="1" 
                                   class="sr-only peer" 
                                   {{ old('email_notifications', $system->settings['notifications']['email_notifications'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Push Notifications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Receive browser notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="push_notifications" value="0">
                            <input type="checkbox" name="push_notifications" value="1" 
                                   class="sr-only peer" 
                                   {{ old('push_notifications', $system->settings['notifications']['push_notifications'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">SMS Notifications</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Receive SMS notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="sms_notifications" value="0">
                            <input type="checkbox" name="sms_notifications" value="1" 
                                   class="sr-only peer" 
                                   {{ old('sms_notifications', $system->settings['notifications']['sms_notifications'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Notification Sound</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Play sound for new notifications</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="notification_sound" value="0">
                            <input type="checkbox" name="notification_sound" value="1" 
                                   class="sr-only peer" 
                                   {{ old('notification_sound', $system->settings['notifications']['notification_sound'] ?? true) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-tab" class="tab-content hidden space-y-6">
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                        <div>
                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Two-Factor Authentication</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Require 2FA for admin login</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="hidden" name="two_factor_auth" value="0">
                            <input type="checkbox" name="two_factor_auth" value="1" 
                                   class="sr-only peer" 
                                   {{ old('two_factor_auth', $system->settings['security']['two_factor_auth'] ?? false) ? 'checked' : '' }}>
                            <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Max Login Attempts
                            </label>
                            <input type="number" name="login_attempts" 
                                   value="{{ old('login_attempts', $system->settings['security']['login_attempts'] ?? 5) }}" min="1" max="10"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Session Timeout (minutes)
                            </label>
                            <input type="number" name="session_timeout" 
                                   value="{{ old('session_timeout', $system->settings['security']['session_timeout'] ?? 30) }}" min="5" max="480"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Password Expiry (days)
                            </label>
                            <input type="number" name="password_expiry" 
                                   value="{{ old('password_expiry', $system->settings['security']['password_expiry'] ?? 90) }}" min="30" max="365"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Social Media Tab -->
            <div id="social-tab" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    @php
                        $socialPlatforms = [
                            'facebook' => ['color' => '#1877F2', 'icon' => 'ri-facebook-fill'],
                            'twitter' => ['color' => '#1DA1F2', 'icon' => 'ri-twitter-fill'],
                            'instagram' => ['color' => '#E4405F', 'icon' => 'ri-instagram-fill'],
                            'linkedin' => ['color' => '#0A66C2', 'icon' => 'ri-linkedin-fill'],
                            'youtube' => ['color' => '#FF0000', 'icon' => 'ri-youtube-fill'],
                            'whatsapp' => ['color' => '#25D366', 'icon' => 'ri-whatsapp-fill'],
                        ];
                    @endphp
                    
                    @foreach($socialPlatforms as $platform => $defaults)
                        @php
                            $socialData = $system->social_media[$platform] ?? [
                                'enabled' => false,
                                'url' => '',
                                'icon' => $defaults['icon'],
                                'name' => ucfirst($platform),
                                'color' => $defaults['color'],
                                'order' => 99
                            ];
                        @endphp
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="social_{{ $platform }}_enabled" value="0">
                                        <input type="checkbox" name="social_{{ $platform }}_enabled" value="1" 
                                               class="sr-only peer" 
                                               {{ old("social_{$platform}_enabled", $socialData['enabled']) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                    </label>
                                    <div class="flex items-center gap-2">
                                        <div class="w-8 h-8 rounded flex items-center justify-center" style="background-color: {{ $socialData['color'] }}">
                                            <i class="{{ $socialData['icon'] }} text-white text-sm"></i>
                                        </div>
                                        <span class="text-sm font-medium text-gray-800 dark:text-white">
                                            {{ $socialData['name'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        URL
                                    </label>
                                    <input type="url" name="social_{{ $platform }}_url" 
                                           value="{{ old("social_{$platform}_url", $socialData['url']) }}"
                                           placeholder="https://{{ $platform }}.com/username"
                                           class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm">
                                </div>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Color
                                        </label>
                                        <input type="color" name="social_{{ $platform }}_color" 
                                               value="{{ old("social_{$platform}_color", $socialData['color']) }}"
                                               class="w-full h-8 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Order
                                        </label>
                                        <input type="number" name="social_{{ $platform }}_order" 
                                               value="{{ old("social_{$platform}_order", $socialData['order']) }}" min="1" max="99"
                                               class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Icon Class
                                    </label>
                                    <input type="text" name="social_{{ $platform }}_icon" 
                                           value="{{ old("social_{$platform}_icon", $socialData['icon']) }}"
                                           class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Website Pages Tab -->
            <div id="pages-tab" class="tab-content hidden space-y-6">
                <div class="space-y-4">
                    @php
                        $pages = ['home', 'about', 'services', 'contact', 'faq', 'privacy', 'terms'];
                    @endphp
                    
                    @foreach($pages as $page)
                        @php
                            $pageData = $system->website_pages[$page] ?? [
                                'enabled' => in_array($page, ['home', 'about', 'services', 'contact']),
                                'title' => ucfirst(str_replace('_', ' ', $page)),
                                'slug' => $page === 'home' ? '' : str_replace('_', '-', $page),
                                'content' => '', // <-- ADD THIS LINE
                                'meta_title' => '',
                                'meta_description' => '',
                                'meta_keywords' => '',
                                'show_in_menu' => in_array($page, ['home', 'about', 'services', 'contact', 'faq']),
                                'order' => array_search($page, $pages) + 1
                            ];
                        @endphp
                        <div class="p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="page_{{ $page }}_enabled" value="0">
                                        <input type="checkbox" name="page_{{ $page }}_enabled" value="1" 
                                            class="sr-only peer" 
                                            {{ old("page_{$page}_enabled", $pageData['enabled']) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                    </label>
                                    <span class="text-sm font-medium text-gray-800 dark:text-white">
                                        {{ $pageData['title'] }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="hidden" name="page_{{ $page }}_show_in_menu" value="0">
                                        <input type="checkbox" name="page_{{ $page }}_show_in_menu" value="1" 
                                            class="sr-only peer" 
                                            {{ old("page_{$page}_show_in_menu", $pageData['show_in_menu']) ? 'checked' : '' }}>
                                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                                    </label>
                                    <span class="text-xs text-gray-500 dark:text-gray-400">Show in Menu</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Title
                                        </label>
                                        <input type="text" name="page_{{ $page }}_title" 
                                            value="{{ old("page_{$page}_title", $pageData['title']) }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Slug
                                        </label>
                                        <input type="text" name="page_{{ $page }}_slug" 
                                            value="{{ old("page_{$page}_slug", $pageData['slug']) }}"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                            Order
                                        </label>
                                        <input type="number" name="page_{{ $page }}_order" 
                                            value="{{ old("page_{$page}_order", $pageData['order']) }}" min="1" max="99"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">
                                        Content
                                    </label>
                                    <textarea name="page_{{ $page }}_content" rows="3"
                                            class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 text-sm focus:ring-2 focus:ring-primary focus:border-transparent">{{ old("page_{$page}_content", $pageData['content'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Company Info Tab -->
            <div id="company-tab" class="tab-content hidden space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Company Website
                        </label>
                        <input type="url" name="company_website" 
                               value="{{ old('company_website', $system->settings['company']['website'] ?? '') }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Company Phone
                        </label>
                        <input type="tel" name="company_phone" 
                               value="{{ old('company_phone', $system->settings['company']['phone'] ?? '') }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Company Email
                        </label>
                        <input type="email" name="company_email" 
                               value="{{ old('company_email', $system->settings['company']['email'] ?? '') }}"
                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Company Address
                        </label>
                        <textarea name="company_address" rows="2"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('company_address', $system->settings['company']['address'] ?? '') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            About Company
                        </label>
                        <textarea name="company_about" rows="4"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('company_about', $system->settings['company']['about'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Mission Statement
                        </label>
                        <textarea name="company_mission" rows="3"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('company_mission', $system->settings['company']['mission'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Vision Statement
                        </label>
                        <textarea name="company_vision" rows="3"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('company_vision', $system->settings['company']['vision'] ?? '') }}</textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Core Values
                        </label>
                        <textarea name="company_values" rows="3"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">{{ old('company_values', $system->settings['company']['values'] ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Other Settings Tab -->
            <div id="other-tab" class="tab-content hidden space-y-6">
                <!-- Maintenance Mode -->
                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                    <div>
                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Maintenance Mode</h4>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Put site in maintenance mode</p>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="hidden" name="maintenance_mode" value="0">
                        <input type="checkbox" name="maintenance_mode" value="1" 
                               class="sr-only peer" 
                               {{ old('maintenance_mode', $system->maintenance_mode) ? 'checked' : '' }}>
                        <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                    </label>
                </div>

                <!-- Backup Settings -->
                <div class="space-y-4">
                    <h4 class="text-md font-medium text-gray-800 dark:text-white mb-2">Backup Settings</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white">Auto Backup</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Automatically backup database</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="auto_backup" value="0">
                                <input type="checkbox" name="auto_backup" value="1" 
                                       class="sr-only peer" 
                                       {{ old('auto_backup', $system->settings['backup']['auto_backup'] ?? true) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                            </label>
                        </div>

                        <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div>
                                <h4 class="text-sm font-medium text-gray-800 dark:text-white">Backup to Cloud</h4>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Store backups in cloud storage</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="backup_to_cloud" value="0">
                                <input type="checkbox" name="backup_to_cloud" value="1" 
                                       class="sr-only peer" 
                                       {{ old('backup_to_cloud', $system->settings['backup']['backup_to_cloud'] ?? false) ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-primary rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Backup Frequency
                            </label>
                            <select name="backup_frequency"
                                    class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                                <option value="daily" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                <option value="weekly" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? 'daily') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="monthly" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? 'daily') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Backup Retention (days)
                            </label>
                            <input type="number" name="backup_retention" 
                                   value="{{ old('backup_retention', $system->settings['backup']['backup_retention'] ?? 30) }}" min="1" max="365"
                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                        </div>
                    </div>
                </div>

                <!-- Google Analytics -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Google Analytics ID
                    </label>
                    <input type="text" name="google_analytics" 
                           value="{{ old('google_analytics', $system->settings['integrations']['google_analytics'] ?? '') }}"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                </div>

                <!-- Google Maps -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        Google Maps API Key
                    </label>
                    <input type="text" name="google_maps_key" 
                           value="{{ old('google_maps_key', $system->settings['integrations']['google_maps_key'] ?? '') }}"
                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200">
                </div>

                <!-- Custom CSS & JS -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Custom CSS
                        </label>
                        <textarea name="custom_css" rows="6"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 font-mono text-sm">{{ old('custom_css', $system->custom_css) }}</textarea>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Custom JavaScript
                        </label>
                        <textarea name="custom_js" rows="6"
                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 font-mono text-sm">{{ old('custom_js', $system->custom_js) }}</textarea>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-5">
                    <button type="reset" 
                            class="inline-flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 transition rounded-lg border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700">
                        <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Reset Changes
                    </button>
                    
                    <button type="submit" 
                            class="inline-flex items-center gap-2 rounded-lg border border-primary bg-primary px-5 py-3.5 text-sm font-medium text-white shadow-theme-xs transition hover:bg-primary/90 dark:bg-primary dark:border-primary dark:text-white dark:hover:bg-primary/90">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Tab switching functionality
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-primary', 'text-primary', 'dark:text-primary-400');
            button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        });

        // Show selected tab content
        const selectedTab = document.getElementById(`${tabName}-tab`);
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
        }

        // Activate selected tab button
        const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
        if (selectedButton) {
            selectedButton.classList.add('border-primary', 'text-primary', 'dark:text-primary-400');
            selectedButton.classList.remove('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        }

        // Update URL hash for bookmarking
        window.location.hash = tabName;
    }

    // Toggle maintenance mode
    function toggleMaintenance() {
        if (confirm('Are you sure you want to toggle maintenance mode?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("system.toggle-maintenance") }}';
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Sync color inputs
    function syncColorInputs() {
        const colorTextInputs = document.querySelectorAll('input[type="text"][name*="color"]');
        const colorInputs = document.querySelectorAll('input[type="color"]');
        
        colorInputs.forEach(input => {
            input.addEventListener('input', function() {
                const textInput = document.querySelector(`input[type="text"][name="${this.name}"]`);
                if (textInput) {
                    textInput.value = this.value;
                }
            });
        });
        
        colorTextInputs.forEach(input => {
            input.addEventListener('input', function() {
                const colorInput = document.querySelector(`input[type="color"][name="${this.name}"]`);
                if (colorInput && this.value.match(/^#[0-9A-F]{6}$/i)) {
                    colorInput.value = this.value;
                }
            });
        });
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize first tab based on URL hash or default to 'basic'
        const hash = window.location.hash.substring(1);
        const validTabs = ['basic', 'system', 'colors', 'notifications', 'security', 'social', 'pages', 'company', 'other'];
        if (hash && validTabs.includes(hash)) {
            switchTab(hash);
        } else {
            switchTab('basic');
        }

        // Sync color inputs
        syncColorInputs();

        // Auto-show tabs if errors exist
        const errorFields = document.querySelectorAll('[class*="text-red-600"]');
        if (errorFields.length > 0) {
            const errorField = errorFields[0];
            const input = errorField.closest('.tab-content')?.querySelector('input, select, textarea');
            if (input && input.closest('.tab-content')) {
                const tabId = input.closest('.tab-content').id.replace('-tab', '');
                switchTab(tabId);
            }
        }
    });
</script>

<style>
    .tab-button {
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
    }
    
    .tab-button:hover {
        border-bottom-color: rgba(59, 130, 246, 0.5);
    }
    
    .tab-content {
        animation: fadeIn 0.3s ease-in-out;
    }
    
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Tailadmin button styles */
    .shadow-theme-xs {
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
    }
</style>
@endsection