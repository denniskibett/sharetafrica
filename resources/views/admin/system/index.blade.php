@extends('layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-white">System Settings</h1>
                <p class="text-gray-600 dark:text-gray-300 mt-2">Manage your application configuration and preferences</p>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-3">
                <button onclick="toggleMaintenance()" 
                        class="inline-flex items-center px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ $system->maintenance_mode ? 'Disable Maintenance' : 'Enable Maintenance' }}
                </button>
                
                <a href="{{ route('system.clear-cache') }}" 
                   class="inline-flex items-center px-4 py-2.5 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear Cache
                </a>
                
                <a href="{{ route('system.backup') }}" 
                   class="inline-flex items-center px-4 py-2.5 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Backup Database
                </a>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
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

    @if($errors->any())
        <div class="mb-6 p-4 rounded-lg bg-red-50 border border-red-200 dark:bg-red-900/20 dark:border-red-800">
            <div class="flex items-center">
                <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                </svg>
                <span class="text-red-800 dark:text-red-300">Please fix the errors below.</span>
            </div>
        </div>
    @endif

    <!-- Debug Panel (Always visible for admin debugging) -->
    <div class="mb-6" id="debug-panel">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
            <div>
                <button type="button" onclick="toggleDebug()" 
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-gray-800 text-white hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    <span id="debug-toggle-text">Show Debug Info</span>
                </button>
                <button type="button" onclick="debugFormData()" 
                        class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors ml-2">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Test Form Data
                </button>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex gap-2">
                <a href="{{ route('system.clear-cache') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Clear Cache
                </a>
                <a href="{{ route('system.backup') }}" 
                   class="inline-flex items-center px-4 py-2 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
                    </svg>
                    Backup Database
                </a>
                <button onclick="toggleMaintenance()" 
                        class="inline-flex items-center px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-700 hover:bg-gray-50 dark:bg-gray-800 dark:border-gray-700 dark:text-gray-200 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    {{ $system->maintenance_mode ? 'Disable Maintenance' : 'Enable Maintenance' }}
                </button>
            </div>
        </div>
        
        <div id="debug-content" class="hidden">
            <!-- Current Values Debug -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
                <!-- Current System Values -->
                <div class="bg-gray-900 text-gray-300 rounded-lg overflow-hidden">
                    <div class="p-4 bg-gray-800 border-b border-gray-700">
                        <h4 class="font-medium text-white">Current System Values in Database</h4>
                    </div>
                    <div class="p-4">
                        <div class="mb-3">
                            <h5 class="text-sm font-medium text-gray-400 mb-2">General Settings:</h5>
                            <div class="grid grid-cols-2 gap-2 text-sm">
                                <div class="text-gray-500">Name:</div>
                                <div class="text-gray-300">{{ $system->name }}</div>
                                <div class="text-gray-500">Timezone:</div>
                                <div class="text-gray-300">{{ $system->timezone }}</div>
                                <div class="text-gray-500">Currency:</div>
                                <div class="text-gray-300">{{ $system->currency }} ({{ $system->currency_symbol }})</div>
                                <div class="text-gray-500">Pagination Limit:</div>
                                <div class="text-gray-300">{{ $system->pagination_limit }}</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <h5 class="text-sm font-medium text-gray-400 mb-2">Colors:</h5>
                            <div class="flex items-center gap-4">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-gray-700" style="background-color: {{ $system->primary_color }}"></div>
                                    <span class="text-sm text-gray-300">{{ $system->primary_color }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded border border-gray-700" style="background-color: {{ $system->secondary_color }}"></div>
                                    <span class="text-sm text-gray-300">{{ $system->secondary_color }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h5 class="text-sm font-medium text-gray-400 mb-2">Settings Array:</h5>
                            <pre class="text-xs overflow-auto max-h-48 bg-gray-800 p-3 rounded">{{ json_encode($system->settings, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>

                <!-- Current Form Values -->
                <div class="bg-gray-900 text-gray-300 rounded-lg overflow-hidden">
                    <div class="p-4 bg-gray-800 border-b border-gray-700">
                        <h4 class="font-medium text-white">Current Form Values</h4>
                    </div>
                    <pre class="p-4 overflow-auto text-sm max-h-96" id="current-values">Loading form values...</pre>
                </div>
            </div>

            <!-- Form Data Debug -->
            <div class="bg-gray-900 text-gray-300 rounded-lg overflow-hidden mb-4">
                <div class="p-4 bg-gray-800 border-b border-gray-700">
                    <h4 class="font-medium text-white">Form Submission Test Results</h4>
                </div>
                <div class="p-4">
                    <div id="form-debug-result" class="hidden">
                        <h5 class="text-sm font-medium text-gray-400 mb-2">Raw Form Data:</h5>
                        <pre class="text-xs overflow-auto bg-gray-800 p-3 rounded mb-3 max-h-48"></pre>
                    </div>
                    
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                        <div id="missing-fields" class="hidden bg-red-900/30 border border-red-800 rounded p-3">
                            <h5 class="font-medium text-red-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Missing Required Fields
                            </h5>
                            <div class="text-red-300 text-sm"></div>
                        </div>
                        
                        <div id="filled-fields" class="hidden bg-green-900/30 border border-green-800 rounded p-3">
                            <h5 class="font-medium text-green-300 mb-2 flex items-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Filled Required Fields
                            </h5>
                            <div class="text-green-300 text-sm"></div>
                        </div>
                    </div>
                    
                    <div id="all-fields" class="hidden mt-4">
                        <h5 class="text-sm font-medium text-gray-400 mb-2">All Form Fields:</h5>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                            <!-- Will be populated by JavaScript -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Session Debug Info -->
            @if(session('debug_info'))
            <div class="bg-gray-900 text-gray-300 rounded-lg overflow-hidden">
                <div class="p-4 bg-gray-800 border-b border-gray-700">
                    <h4 class="font-medium text-white">Last Update Debug Info</h4>
                </div>
                <pre class="p-4 overflow-auto text-sm max-h-64">{{ session('debug_info') }}</pre>
            </div>
            @endif
        </div>
    </div>

    <!-- Diagnostic Button (Temporary) -->
<div class="mb-4 p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
    <h4 class="font-medium text-yellow-800 dark:text-yellow-300 mb-2">Form Diagnostic</h4>
    <button onclick="diagnoseForm()" class="px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700">
        Diagnose Form Structure
    </button>
    <div id="diagnostic-result" class="mt-3 hidden p-3 bg-white dark:bg-gray-800 rounded border"></div>
</div>

<script>
function diagnoseForm() {
    const form = document.querySelector('form');
    if (!form) {
        alert('No form found on page!');
        return;
    }
    
    const inputs = form.querySelectorAll('input, select, textarea');
    let html = `<div class="mb-3 text-sm text-gray-600 dark:text-gray-400">Total elements: ${inputs.length}</div>`;
    
    const elementsByType = {};
    inputs.forEach(input => {
        const type = input.type || input.tagName.toLowerCase();
        if (!elementsByType[type]) elementsByType[type] = [];
        elementsByType[type].push(input);
    });
    
    html += '<div class="space-y-4">';
    
    Object.entries(elementsByType).forEach(([type, elements]) => {
        html += `<div><strong class="text-blue-600 dark:text-blue-400">${type.toUpperCase()}</strong> (${elements.length}):</div>`;
        html += '<div class="ml-4 space-y-1">';
        
        elements.forEach(input => {
            const hasName = input.name ? '✅' : '❌';
            const hasValue = input.value || input.checked ? '✅' : '❌';
            html += `<div class="text-xs">
                ${hasName} Name: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">${input.name || '(no name)'}</code>
                ${hasValue} Value: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">${input.type === 'checkbox' ? input.checked : input.value}</code>
                ID: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">${input.id || '(no id)'}</code>
            </div>`;
        });
        
        html += '</div>';
    });
    
    html += '</div>';
    
    document.getElementById('diagnostic-result').innerHTML = html;
    document.getElementById('diagnostic-result').classList.remove('hidden');
}
</script>

    <!-- Settings Container -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 dark:bg-gray-800 dark:border-gray-700">
        <!-- Tabs Navigation -->
        <div class="border-b border-gray-200 dark:border-gray-700">
            <nav class="flex flex-wrap -mb-px">
                <button type="button" onclick="switchTab('general')" data-tab="general" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    General
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
                <button type="button" onclick="switchTab('integrations')" data-tab="integrations" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Integrations
                </button>
                <button type="button" onclick="switchTab('backup')" data-tab="backup" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    Backup
                </button>
                <button type="button" onclick="switchTab('design')" data-tab="design" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                    </svg>
                    Design
                </button>
                <button type="button" onclick="switchTab('custom')" data-tab="custom" 
                        class="tab-button inline-flex items-center px-4 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Custom Code
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <form action="{{ route('system.update') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- General Tab -->
            <div id="general-tab-content" class="tab-content">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Application Info -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                                Application Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Application Name <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="name" value="{{ old('name', $system->name) }}" required
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Slogan
                                    </label>
                                    <input type="text" name="slogan" value="{{ old('slogan', $system->slogan) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Logo
                                    </label>
                                    <div class="flex items-center gap-4">
                                        @if($system->logo)
                                            <img src="{{ Storage::url($system->logo) }}" alt="Logo" class="h-12 w-auto rounded-lg">
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" name="logo" accept="image/*"
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-300">
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Recommended: 200x60px, PNG or SVG format</p>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Favicon
                                    </label>
                                    <div class="flex items-center gap-4">
                                        @if($system->favicon)
                                            <img src="{{ Storage::url($system->favicon) }}" alt="Favicon" class="h-8 w-8 rounded">
                                        @endif
                                        <div class="flex-1">
                                            <input type="file" name="favicon" accept="image/*"
                                                   class="w-full px-3 py-2 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-gray-100 file:text-gray-700 hover:file:bg-gray-200 dark:file:bg-gray-700 dark:file:text-gray-300">
                                        </div>
                                    </div>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Recommended: 32x32px, ICO or PNG format</p>
                                </div>
                            </div>
                        </div>

                        <!-- Contact Information -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                Contact Information
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Contact Email
                                    </label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email', $system->contact_email) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Contact Phone
                                    </label>
                                    <input type="text" name="contact_phone" value="{{ old('contact_phone', $system->contact_phone) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Address
                                    </label>
                                    <textarea name="address" rows="3"
                                              class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">{{ old('address', $system->address) }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- System Configuration -->
                    <div class="space-y-6">
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                System Configuration
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Timezone <span class="text-red-500">*</span>
                                    </label>
                                    <select name="timezone" required
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        @foreach($timezones as $tz)
                                            <option value="{{ $tz }}" {{ old('timezone', $system->timezone) == $tz ? 'selected' : '' }}>{{ $tz }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Date Format <span class="text-red-500">*</span>
                                        </label>
                                        <select name="date_format" required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                            <option value="Y-m-d" {{ old('date_format', $system->date_format) == 'Y-m-d' ? 'selected' : '' }}>YYYY-MM-DD</option>
                                            <option value="d/m/Y" {{ old('date_format', $system->date_format) == 'd/m/Y' ? 'selected' : '' }}>DD/MM/YYYY</option>
                                            <option value="m/d/Y" {{ old('date_format', $system->date_format) == 'm/d/Y' ? 'selected' : '' }}>MM/DD/YYYY</option>
                                            <option value="d M, Y" {{ old('date_format', $system->date_format) == 'd M, Y' ? 'selected' : '' }}>DD Mon, YYYY</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Time Format <span class="text-red-500">*</span>
                                        </label>
                                        <select name="time_format" required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                            <option value="H:i:s" {{ old('time_format', $system->time_format) == 'H:i:s' ? 'selected' : '' }}>24-hour (HH:MM:SS)</option>
                                            <option value="h:i:s A" {{ old('time_format', $system->time_format) == 'h:i:s A' ? 'selected' : '' }}>12-hour (HH:MM:SS AM/PM)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Currency <span class="text-red-500">*</span>
                                        </label>
                                        <select name="currency" required
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                            @foreach($currencies as $code => $name)
                                                <option value="{{ $code }}" {{ old('currency', $system->currency) == $code ? 'selected' : '' }}>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Currency Symbol <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $system->currency_symbol) }}" required
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Pagination Limit <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="pagination_limit" value="{{ old('pagination_limit', $system->pagination_limit) }}" min="5" max="100" required
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Number of items per page (5-100)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Social Media -->
                        <div class="bg-gray-50 dark:bg-gray-700/50 p-5 rounded-xl">
                            <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                                </svg>
                                Social Media
                            </h3>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Facebook URL
                                    </label>
                                    <input type="url" name="facebook_url" value="{{ old('facebook_url', $system->facebook_url) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="https://facebook.com/yourpage">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Twitter URL
                                    </label>
                                    <input type="url" name="twitter_url" value="{{ old('twitter_url', $system->twitter_url) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="https://twitter.com/yourprofile">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Instagram URL
                                    </label>
                                    <input type="url" name="instagram_url" value="{{ old('instagram_url', $system->instagram_url) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="https://instagram.com/yourprofile">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        LinkedIn URL
                                    </label>
                                    <input type="url" name="linkedin_url" value="{{ old('linkedin_url', $system->linkedin_url) }}"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                           placeholder="https://linkedin.com/company/yourcompany">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div id="notifications-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            Notification Preferences
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Email Notifications -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Email Notifications</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Receive important updates via email</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="email_notifications" value="1" 
                                           class="sr-only peer" 
                                           {{ old('email_notifications', $system->settings['notifications']['email_notifications'] ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- Push Notifications -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Push Notifications</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Receive browser notifications</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="push_notifications" value="1" 
                                           class="sr-only peer" 
                                           {{ old('push_notifications', $system->settings['notifications']['push_notifications'] ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- SMS Notifications -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">SMS Notifications</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Receive SMS alerts for critical updates</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="sms_notifications" value="1" 
                                           class="sr-only peer" 
                                           {{ old('sms_notifications', $system->settings['notifications']['sms_notifications'] ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <!-- Notification Sound -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Notification Sound</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Play sound for new notifications</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="notification_sound" value="1" 
                                           class="sr-only peer" 
                                           {{ old('notification_sound', $system->settings['notifications']['notification_sound'] ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div id="security-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            Security Settings
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Two-Factor Authentication -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Two-Factor Authentication</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Require 2FA for admin login</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="two_factor_auth" value="1" 
                                           class="sr-only peer" 
                                           {{ old('two_factor_auth', $system->settings['security']['two_factor_auth'] ?? false) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Max Login Attempts -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Max Login Attempts
                                    </label>
                                    <input type="number" name="login_attempts" 
                                           value="{{ old('login_attempts', $system->settings['security']['login_attempts'] ?? 5) }}" 
                                           min="1" max="10"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Failed attempts before lockout</p>
                                </div>

                                <!-- Session Timeout -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Session Timeout (minutes)
                                    </label>
                                    <input type="number" name="session_timeout" 
                                           value="{{ old('session_timeout', $system->settings['security']['session_timeout'] ?? 30) }}" 
                                           min="5" max="480"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Auto logout after inactivity</p>
                                </div>

                                <!-- Password Expiry -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Password Expiry (days)
                                    </label>
                                    <input type="number" name="password_expiry" 
                                           value="{{ old('password_expiry', $system->settings['security']['password_expiry'] ?? 90) }}" 
                                           min="1" max="365"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Days before password change required</p>
                                </div>

                                <!-- Maintenance Mode -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Maintenance Mode</h4>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Put site in maintenance mode</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="maintenance_mode" value="1" 
                                                   class="sr-only peer" 
                                                   {{ old('maintenance_mode', $system->maintenance_mode) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">When enabled, only admins can access the site.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Integrations Tab -->
            <div id="integrations-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Third-Party Integrations
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Analytics -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                                    </svg>
                                    Analytics
                                </h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Google Analytics ID
                                        </label>
                                        <input type="text" name="google_analytics" 
                                               value="{{ old('google_analytics', $system->settings['integrations']['google_analytics'] ?? '') }}"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                               placeholder="UA-XXXXXXXXX-X">
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Your Google Analytics tracking ID</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Google Maps API Key
                                        </label>
                                        <input type="password" name="google_maps_key" 
                                               value="{{ old('google_maps_key', $system->settings['integrations']['google_maps_key'] ?? '') }}"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors"
                                               placeholder="Enter your API key">
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">For Google Maps integration</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Email Configuration -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                    </svg>
                                    Email Configuration
                                </h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Mail Driver
                                        </label>
                                        <select name="mail_driver"
                                                class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                            <option value="smtp" {{ old('mail_driver', $system->settings['integrations']['mail_driver'] ?? 'smtp') == 'smtp' ? 'selected' : '' }}>SMTP</option>
                                            <option value="mailgun" {{ old('mail_driver', $system->settings['integrations']['mail_driver'] ?? '') == 'mailgun' ? 'selected' : '' }}>Mailgun</option>
                                            <option value="ses" {{ old('mail_driver', $system->settings['integrations']['mail_driver'] ?? '') == 'ses' ? 'selected' : '' }}>Amazon SES</option>
                                            <option value="sendmail" {{ old('mail_driver', $system->settings['integrations']['mail_driver'] ?? '') == 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        </select>
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Mail Host
                                            </label>
                                            <input type="text" name="mail_host" 
                                                   value="{{ old('mail_host', $system->settings['integrations']['mail_host'] ?? '') }}"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        </div>

                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                                Mail Port
                                            </label>
                                            <input type="text" name="mail_port" 
                                                   value="{{ old('mail_port', $system->settings['integrations']['mail_port'] ?? '587') }}"
                                                   class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Mail Username
                                        </label>
                                        <input type="text" name="mail_username" 
                                               value="{{ old('mail_username', $system->settings['integrations']['mail_username'] ?? '') }}"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Mail Password
                                        </label>
                                        <input type="password" name="mail_password" 
                                               value="{{ old('mail_password', $system->settings['integrations']['mail_password'] ?? '') }}"
                                               class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Backup Tab -->
            <div id="backup-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                            Backup Settings
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Auto Backup -->
                            <div class="flex items-center justify-between p-4 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0">
                                        <svg class="w-6 h-6 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-800 dark:text-white">Auto Backup</h4>
                                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Automatically backup database</p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" name="auto_backup" value="1" 
                                           class="sr-only peer" 
                                           {{ old('auto_backup', $system->settings['backup']['auto_backup'] ?? true) ? 'checked' : '' }}>
                                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                </label>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Backup Frequency -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Backup Frequency
                                    </label>
                                    <select name="backup_frequency"
                                            class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        <option value="daily" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? 'daily') == 'daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="weekly" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? '') == 'weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="monthly" {{ old('backup_frequency', $system->settings['backup']['backup_frequency'] ?? '') == 'monthly' ? 'selected' : '' }}>Monthly</option>
                                    </select>
                                </div>

                                <!-- Backup Retention -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Backup Retention (days)
                                    </label>
                                    <input type="number" name="backup_retention" 
                                           value="{{ old('backup_retention', $system->settings['backup']['backup_retention'] ?? 30) }}" 
                                           min="1" max="365"
                                           class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Days to keep backups</p>
                                </div>

                                <!-- Cloud Backup -->
                                <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-200 dark:border-gray-700">
                                    <div class="flex items-center justify-between mb-4">
                                        <div>
                                            <h4 class="text-sm font-medium text-gray-800 dark:text-white">Cloud Backup</h4>
                                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Backup to cloud storage</p>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" name="backup_to_cloud" value="1" 
                                                   class="sr-only peer" 
                                                   {{ old('backup_to_cloud', $system->settings['backup']['backup_to_cloud'] ?? false) ? 'checked' : '' }}>
                                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-2 peer-focus:ring-primary-500 dark:peer-focus:ring-primary-600 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary-600"></div>
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Store backups in cloud storage services</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Design Tab -->
            <div id="design-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                            </svg>
                            Design & Appearance
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Colors -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">Color Scheme</h4>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Primary Color -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Primary Color <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative">
                                                <input type="color" name="primary_color" 
                                                       value="{{ old('primary_color', $system->primary_color) }}"
                                                       class="w-12 h-12 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600">
                                            </div>
                                            <input type="text" name="primary_color" 
                                                   value="{{ old('primary_color', $system->primary_color) }}" required
                                                   class="flex-1 px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        </div>
                                        <div class="mt-3 flex items-center">
                                            <div class="w-8 h-8 rounded mr-2" style="background-color: {{ old('primary_color', $system->primary_color) }}"></div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Primary Color Preview</span>
                                        </div>
                                    </div>

                                    <!-- Secondary Color -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Secondary Color <span class="text-red-500">*</span>
                                        </label>
                                        <div class="flex items-center space-x-3">
                                            <div class="relative">
                                                <input type="color" name="secondary_color" 
                                                       value="{{ old('secondary_color', $system->secondary_color) }}"
                                                       class="w-12 h-12 rounded-lg cursor-pointer border border-gray-300 dark:border-gray-600">
                                            </div>
                                            <input type="text" name="secondary_color" 
                                                   value="{{ old('secondary_color', $system->secondary_color) }}" required
                                                   class="flex-1 px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">
                                        </div>
                                        <div class="mt-3 flex items-center">
                                            <div class="w-8 h-8 rounded mr-2" style="background-color: {{ old('secondary_color', $system->secondary_color) }}"></div>
                                            <span class="text-sm text-gray-600 dark:text-gray-400">Secondary Color Preview</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Meta Information -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4">SEO & Meta Information</h4>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Meta Description
                                        </label>
                                        <textarea name="meta_description" rows="3"
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">{{ old('meta_description', $system->meta_description) }}</textarea>
                                        <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            <span id="meta-desc-count">{{ strlen(old('meta_description', $system->meta_description)) }}</span>/160 characters
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                            Meta Keywords
                                        </label>
                                        <textarea name="meta_keywords" rows="3"
                                                  class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">{{ old('meta_keywords', $system->meta_keywords) }}</textarea>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Separate keywords with commas</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Custom Code Tab -->
            <div id="custom-tab-content" class="tab-content hidden">
                <div class="max-w-3xl mx-auto">
                    <div class="bg-gray-50 dark:bg-gray-700/50 p-6 rounded-xl">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-6 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                            </svg>
                            Custom Code
                        </h3>
                        
                        <div class="space-y-6">
                            <!-- Custom CSS -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                                    </svg>
                                    Custom CSS
                                </h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Add custom CSS styles
                                    </label>
                                    <textarea name="custom_css" rows="8"
                                              class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">{{ old('custom_css', $system->custom_css) }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This CSS will be added to the &lt;head&gt; section of every page.</p>
                                </div>
                            </div>

                            <!-- Custom JavaScript -->
                            <div class="bg-white dark:bg-gray-800 p-5 rounded-lg border border-gray-200 dark:border-gray-700">
                                <h4 class="text-md font-medium text-gray-800 dark:text-white mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-gray-600 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/>
                                    </svg>
                                    Custom JavaScript
                                </h4>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                        Add custom JavaScript
                                    </label>
                                    <textarea name="custom_js" rows="8"
                                              class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-white dark:bg-gray-800 dark:border-gray-600 text-gray-800 dark:text-gray-200 font-mono text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-colors">{{ old('custom_js', $system->custom_js) }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">This JavaScript will be added before the closing &lt;/body&gt; tag.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-700">
                <div class="flex flex-col sm:flex-row justify-end gap-3">
                    <button type="button" onclick="resetForm()" 
                            class="px-6 py-3 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                        Reset Changes
                    </button>
                    <button type="submit" 
                            class="px-6 py-3 rounded-lg bg-primary-600 text-white hover:bg-primary-700 transition-colors">
                        Save Settings
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js" integrity="sha512-GJrW9pX9Z1GDZCBoBPuiGNsq4CPGK/c/pX9nuSUXwxLBzME2YkdE+5EYXPLkZX31lrT7xvFeoJq6Dig0lzVE3ig==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    // Initialize Ace Editor for Custom CSS
    const cssEditor = ace.edit("css-editor");
    cssEditor.setTheme("ace/theme/monokai");
    cssEditor.session.setMode("ace/mode/css");
    cssEditor.setValue(`{{ old('custom_css', $system->custom_css) }}`, -1);
    cssEditor.session.on('change', function() {
        document.querySelector('textarea[name="custom_css"]').value = cssEditor.getValue();
    });

    // Initialize Ace Editor for Custom JS
    const jsEditor = ace.edit("js-editor");
    jsEditor.setTheme("ace/theme/monokai");
    jsEditor.session.setMode("ace/mode/javascript");
    jsEditor.setValue(`{{ old('custom_js', $system->custom_js) }}`, -1);
    jsEditor.session.on('change', function() {
        document.querySelector('textarea[name="custom_js"]').value = jsEditor.getValue();
    });
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
        // Debug functionality
    let debugVisible = false;

    function toggleDebug() {
        const debugContent = document.getElementById('debug-content');
        const toggleText = document.getElementById('debug-toggle-text');
        
        if (debugVisible) {
            debugContent.classList.add('hidden');
            toggleText.textContent = 'Show Debug Info';
        } else {
            debugContent.classList.remove('hidden');
            toggleText.textContent = 'Hide Debug Info';
            
            // Load current values
            loadCurrentValues();
        }
        debugVisible = !debugVisible;
    }

    function collectAllFormValues() {
        const form = document.querySelector('form');
        if (!form) {
            console.error('No form found on the page!');
            return {};
        }
        
        // Method 1: Use FormData for standard inputs
        const formData = new FormData(form);
        const formValues = {};
        
        // Convert FormData to object
        for (let [key, value] of formData.entries()) {
            // Skip method and token fields
            if (key === '_method' || key === '_token') continue;
            
            // Handle nested keys (settings[category][key])
            if (key.includes('[')) {
                const path = key.replace(/\]/g, '').split('[');
                let current = formValues;
                
                for (let i = 0; i < path.length - 1; i++) {
                    if (!current[path[i]]) {
                        current[path[i]] = {};
                    }
                    current = current[path[i]];
                }
                current[path[path.length - 1]] = value;
            } else {
                formValues[key] = value;
            }
        }
        
        // Method 2: Manually collect checkboxes (they might not be in FormData if not checked)
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (!checkbox.name) return;
            
            const value = checkbox.checked ? '1' : '0';
            
            // Handle nested checkbox names
            if (checkbox.name.includes('[')) {
                const path = checkbox.name.replace(/\]/g, '').split('[');
                let current = formValues;
                
                for (let i = 0; i < path.length - 1; i++) {
                    if (!current[path[i]]) {
                        current[path[i]] = {};
                    }
                    current = current[path[i]];
                }
                current[path[path.length - 1]] = value;
            } else {
                formValues[checkbox.name] = value;
            }
        });
        
        // Method 3: Collect all inputs directly (fallback)
        const allInputs = form.querySelectorAll('input, select, textarea');
        const directValues = {};
        
        allInputs.forEach(input => {
            if (!input.name || input.name === '_method' || input.name === '_token') return;
            
            let value;
            
            switch (input.type) {
                case 'checkbox':
                    value = input.checked ? '1' : '0';
                    break;
                case 'radio':
                    if (input.checked) value = input.value;
                    break;
                case 'file':
                    value = input.files.length > 0 ? 
                        `File: ${input.files[0].name} (${input.files[0].size} bytes)` : 
                        'No file selected';
                    break;
                case 'number':
                case 'range':
                    value = input.value || '0';
                    break;
                case 'color':
                    value = input.value || '#000000';
                    break;
                default:
                    value = input.value || '';
            }
            
            // Handle nested names
            if (input.name.includes('[')) {
                const path = input.name.replace(/\]/g, '').split('[');
                let current = directValues;
                
                for (let i = 0; i < path.length - 1; i++) {
                    if (!current[path[i]]) {
                        current[path[i]] = {};
                    }
                    current = current[path[i]];
                }
                current[path[path.length - 1]] = value;
            } else {
                directValues[input.name] = value;
            }
        });
        
        // Merge both collections (FormData + direct)
        const mergedValues = { ...directValues, ...formValues };
        
        console.log('Collected form values:', mergedValues);
        return mergedValues;
    }

    function loadCurrentValues() {
        try {
            const formValues = collectAllFormValues();
            const currentValues = document.getElementById('current-values');
            
            if (Object.keys(formValues).length === 0) {
                currentValues.textContent = 'No form values found. Check if form elements have "name" attributes.';
            } else {
                currentValues.textContent = JSON.stringify(formValues, null, 2);
            }
        } catch (error) {
            console.error('Error loading current values:', error);
            document.getElementById('current-values').textContent = 'Error loading form values: ' + error.message;
        }
    }

    async function debugFormData() {
        try {
            const formValues = collectAllFormValues();
            
            // Display raw form data
            const resultDiv = document.getElementById('form-debug-result');
            const pre = resultDiv.querySelector('pre');
            pre.textContent = JSON.stringify(formValues, null, 2);
            resultDiv.classList.remove('hidden');
            
            // Check for missing required fields
            const requiredFields = [
                'name', 'timezone', 'date_format', 'time_format', 
                'currency', 'currency_symbol', 'primary_color', 
                'secondary_color', 'pagination_limit'
            ];
            
            const missingFields = [];
            const filledFields = [];
            
            requiredFields.forEach(field => {
                const value = formValues[field];
                if (!value || value.toString().trim() === '' || value === '0') {
                    missingFields.push(field);
                } else {
                    filledFields.push({
                        field: field,
                        value: value
                    });
                }
            });
            
            // Display missing fields
            const missingDiv = document.getElementById('missing-fields');
            const filledDiv = document.getElementById('filled-fields');
            
            if (missingFields.length > 0) {
                missingDiv.classList.remove('hidden');
                missingDiv.querySelector('div').innerHTML = missingFields.map(f => 
                    `<div class="mb-1">
                        <span class="text-red-400 font-medium">${f}</span>: <span class="text-red-300">Empty or missing</span>
                    </div>`
                ).join('');
            } else {
                missingDiv.classList.add('hidden');
            }
            
            if (filledFields.length > 0) {
                filledDiv.classList.remove('hidden');
                filledDiv.querySelector('div').innerHTML = filledFields.map(f => 
                    `<div class="mb-1">
                        <span class="text-green-400 font-medium">${f.field}</span>: <span class="text-green-300">"${f.value}"</span>
                    </div>`
                ).join('');
            } else {
                filledDiv.classList.add('hidden');
            }
            
            // Display all fields in a nice grid
            const allFieldsDiv = document.getElementById('all-fields');
            const allFieldsContainer = allFieldsDiv.querySelector('.grid');
            allFieldsContainer.innerHTML = '';
            
            Object.entries(formValues).forEach(([key, value]) => {
                const fieldDiv = document.createElement('div');
                fieldDiv.className = 'bg-gray-800/50 rounded p-3';
                
                let displayValue = value;
                if (typeof value === 'object') {
                    displayValue = JSON.stringify(value);
                }
                if (displayValue === '') {
                    displayValue = '<span class="text-gray-500 italic">(empty)</span>';
                }
                
                fieldDiv.innerHTML = `
                    <div class="text-xs text-gray-400 font-medium mb-1">${key}</div>
                    <div class="text-sm text-gray-300">${displayValue}</div>
                `;
                allFieldsContainer.appendChild(fieldDiv);
            });
            
            allFieldsDiv.classList.remove('hidden');
            
            // Send to debug endpoint
            try {
                const response = await fetch('{{ route("system.debug") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    },
                    body: JSON.stringify(formValues)
                });
                
                if (response.ok) {
                    const debugData = await response.json();
                    console.log('Debug endpoint response:', debugData);
                    
                } else {
                    console.error('Debug request failed:', response.status);
                }
            } catch (error) {
                console.error('Debug request failed:', error);
            }
        } catch (error) {
            console.error('Error in debugFormData:', error);
            alert('Error collecting form data: ' + error.message);
        }
    }

    // Add a function to list all form elements for debugging
    function debugFormElements() {
        const form = document.querySelector('form');
        if (!form) {
            console.error('No form found!');
            return;
        }
        
        const inputs = form.querySelectorAll('input, select, textarea');
        console.log('Total form elements found:', inputs.length);
        
        inputs.forEach((input, index) => {
            console.log(`${index + 1}. Name: "${input.name}", Type: ${input.type}, Value: ${input.value}, ID: ${input.id}`);
        });
        
        // Also log checkboxes separately
        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        console.log('Checkboxes found:', checkboxes.length);
        checkboxes.forEach((cb, index) => {
            console.log(`Checkbox ${index + 1}: Name="${cb.name}", Checked=${cb.checked}`);
        });
    }

    // Initialize debug on page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('System Settings Debug Initialized');
        
        // Debug form elements on load
        debugFormElements();
        
        // Auto-show debug if URL has debug parameter
        if (window.location.search.includes('debug=true') || {{ config('app.debug') ? 'true' : 'false' }}) {
            toggleDebug();
        }
        
        // Monitor form changes for debugging
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', function() {
                console.log(`Field changed: ${this.name} = ${this.value}`);
                
                // Update debug display if visible
                if (debugVisible) {
                    loadCurrentValues();
                }
            });
            
            element.addEventListener('input', function() {
                if (debugVisible && (this.type === 'text' || this.type === 'textarea' || this.type === 'number')) {
                    loadCurrentValues();
                }
            });
        });
        
        // Initial load of form values
        loadCurrentValues();
    });

    function collectAllFormValues() {
        const form = document.querySelector('form');
        const allInputs = form.querySelectorAll('input, select, textarea');
        const formValues = {};
        
        allInputs.forEach(input => {
            if (!input.name) return;
            
            let value;
            
            switch (input.type) {
                case 'checkbox':
                    value = input.checked ? '1' : '0';
                    break;
                case 'radio':
                    if (input.checked) value = input.value;
                    break;
                case 'file':
                    value = input.files.length > 0 ? 
                        `File: ${input.files[0].name} (${input.files[0].size} bytes)` : 
                        'No file selected';
                    break;
                case 'number':
                case 'range':
                    value = input.value || '0';
                    break;
                case 'color':
                    value = input.value || '#000000';
                    break;
                default:
                    value = input.value || '';
            }
            
            // Handle nested names like settings[category][key]
            if (input.name.includes('[')) {
                const path = input.name.replace(/\]/g, '').split('[');
                let current = formValues;
                
                for (let i = 0; i < path.length - 1; i++) {
                    if (!current[path[i]]) {
                        current[path[i]] = {};
                    }
                    current = current[path[i]];
                }
                current[path[path.length - 1]] = value;
            } else {
                formValues[input.name] = value;
            }
        });
        
        return formValues;
    }

    function loadCurrentValues() {
        const formValues = collectAllFormValues();
        const currentValues = document.getElementById('current-values');
        currentValues.textContent = JSON.stringify(formValues, null, 2);
    }

    async function debugFormData() {
        const formValues = collectAllFormValues();
        
        // Display raw form data
        const resultDiv = document.getElementById('form-debug-result');
        const pre = resultDiv.querySelector('pre');
        pre.textContent = JSON.stringify(formValues, null, 2);
        resultDiv.classList.remove('hidden');
        
        // Check for missing required fields
        const requiredFields = [
            'name', 'timezone', 'date_format', 'time_format', 
            'currency', 'currency_symbol', 'primary_color', 
            'secondary_color', 'pagination_limit'
        ];
        
        const missingFields = [];
        const filledFields = [];
        
        requiredFields.forEach(field => {
            const value = formValues[field];
            if (!value || value.toString().trim() === '') {
                missingFields.push(field);
            } else {
                filledFields.push({
                    field: field,
                    value: value
                });
            }
        });
        
        // Display missing fields
        const missingDiv = document.getElementById('missing-fields');
        const filledDiv = document.getElementById('filled-fields');
        
        if (missingFields.length > 0) {
            missingDiv.classList.remove('hidden');
            missingDiv.querySelector('div').innerHTML = missingFields.map(f => 
                `<div class="mb-1">
                    <span class="text-red-400">${f}</span>: <span class="text-red-300">Empty or missing</span>
                </div>`
            ).join('');
        } else {
            missingDiv.classList.add('hidden');
        }
        
        if (filledFields.length > 0) {
            filledDiv.classList.remove('hidden');
            filledDiv.querySelector('div').innerHTML = filledFields.map(f => 
                `<div class="mb-1">
                    <span class="text-green-400">${f.field}</span>: <span class="text-green-300">"${f.value}"</span>
                </div>`
            ).join('');
        } else {
            filledDiv.classList.add('hidden');
        }
        
        // Display all fields in a nice grid
        const allFieldsDiv = document.getElementById('all-fields');
        const allFieldsContainer = allFieldsDiv.querySelector('.grid');
        allFieldsContainer.innerHTML = '';
        
        Object.entries(formValues).forEach(([key, value]) => {
            const fieldDiv = document.createElement('div');
            fieldDiv.className = 'bg-gray-800/50 rounded p-3';
            fieldDiv.innerHTML = `
                <div class="text-xs text-gray-400 font-medium mb-1">${key}</div>
                <div class="text-sm text-gray-300 truncate">${typeof value === 'object' ? JSON.stringify(value) : value}</div>
            `;
            allFieldsContainer.appendChild(fieldDiv);
        });
        
        allFieldsDiv.classList.remove('hidden');
        
        // Send to debug endpoint
        try {
            const response = await fetch('{{ route("system.debug") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(formValues)
            });
            
            if (response.ok) {
                const debugData = await response.json();
                console.log('Debug endpoint response:', debugData);
                
                // Update debug display with server response
                const serverDebug = document.createElement('div');
                serverDebug.className = 'mt-4 bg-blue-900/30 border border-blue-800 rounded p-3';
                serverDebug.innerHTML = `
                    <h5 class="font-medium text-blue-300 mb-2">Server Debug Response:</h5>
                    <pre class="text-xs overflow-auto">${JSON.stringify(debugData, null, 2)}</pre>
                `;
                
                resultDiv.appendChild(serverDebug);
            } else {
                console.error('Debug request failed:', response.status);
            }
        } catch (error) {
            console.error('Debug request failed:', error);
        }
    }

    // Initialize debug on page load
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-show debug if URL has debug parameter
        if (window.location.search.includes('debug=true') || {{ config('app.debug') ? 'true' : 'false' }}) {
            toggleDebug();
        }
        
        // Add debug parameter to form submission
        const form = document.querySelector('form');
        if (form) {
            const originalSubmit = form.submit;
            form.submit = function() {
                // Add debug parameter
                const debugInput = document.createElement('input');
                debugInput.type = 'hidden';
                debugInput.name = 'debug';
                debugInput.value = 'true';
                this.appendChild(debugInput);
                
                return originalSubmit.call(this);
            };
        }
        
        // Monitor form changes for debugging
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', function() {
                console.log(`Field changed: ${this.name} = ${this.value}`);
                
                // Update debug display if visible
                if (debugVisible) {
                    loadCurrentValues();
                }
            });
            
            element.addEventListener('input', function() {
                if (debugVisible && (this.type === 'text' || this.type === 'textarea' || this.type === 'number')) {
                    loadCurrentValues();
                }
            });
        });
        
        // Initial load of form values
        loadCurrentValues();
    });

    function loadCurrentValues() {
        const currentValues = document.getElementById('current-values');
        
        // Get form values
        const formData = new FormData(document.querySelector('form'));
        const formValues = {};
        
        for (let [key, value] of formData.entries()) {
            // Handle arrays (like settings fields)
            if (key.includes('[') && key.includes(']')) {
                // Handle nested keys
                const keys = key.replace(/\]/g, '').split('[');
                let current = formValues;
                
                for (let i = 0; i < keys.length - 1; i++) {
                    if (!current[keys[i]]) {
                        current[keys[i]] = {};
                    }
                    current = current[keys[i]];
                }
                current[keys[keys.length - 1]] = value;
            } else {
                formValues[key] = value;
            }
        }
        
        // Get checkbox values
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (checkbox.name) {
                if (checkbox.name.includes('[') && checkbox.name.includes(']')) {
                    const keys = checkbox.name.replace(/\]/g, '').split('[');
                    let current = formValues;
                    
                    for (let i = 0; i < keys.length - 1; i++) {
                        if (!current[keys[i]]) {
                            current[keys[i]] = {};
                        }
                        current = current[keys[i]];
                    }
                    current[keys[keys.length - 1]] = checkbox.checked ? '1' : '0';
                } else {
                    formValues[checkbox.name] = checkbox.checked ? '1' : '0';
                }
            }
        });
        
        currentValues.textContent = JSON.stringify(formValues, null, 2);
    }

    async function debugFormData() {
        const form = document.querySelector('form');
        const formData = new FormData(form);
        
        // Convert FormData to object for display
        const formObject = {};
        for (let [key, value] of formData.entries()) {
            // Handle file inputs
            if (value instanceof File) {
                formObject[key] = {
                    name: value.name,
                    size: value.size + ' bytes',
                    type: value.type
                };
            } else {
                // Handle nested keys
                if (key.includes('[') && key.includes(']')) {
                    const keys = key.replace(/\]/g, '').split('[');
                    let current = formObject;
                    
                    for (let i = 0; i < keys.length - 1; i++) {
                        if (!current[keys[i]]) {
                            current[keys[i]] = {};
                        }
                        current = current[keys[i]];
                    }
                    current[keys[keys.length - 1]] = value;
                } else {
                    formObject[key] = value;
                }
            }
        }
        
        // Get checkbox values
        document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
            if (checkbox.name) {
                if (checkbox.name.includes('[') && checkbox.name.includes(']')) {
                    const keys = checkbox.name.replace(/\]/g, '').split('[');
                    let current = formObject;
                    
                    for (let i = 0; i < keys.length - 1; i++) {
                        if (!current[keys[i]]) {
                            current[keys[i]] = {};
                        }
                        current = current[keys[i]];
                    }
                    current[keys[keys.length - 1]] = checkbox.checked ? '1' : '0';
                } else {
                    formObject[checkbox.name] = checkbox.checked ? '1' : '0';
                }
            }
        });
        
        // Display form data
        const resultDiv = document.getElementById('form-debug-result');
        const pre = resultDiv.querySelector('pre');
        pre.textContent = JSON.stringify(formObject, null, 2);
        resultDiv.classList.remove('hidden');
        
        // Check for missing required fields
        const requiredFields = [
            'name', 'timezone', 'date_format', 'time_format', 
            'currency', 'currency_symbol', 'primary_color', 
            'secondary_color', 'pagination_limit'
        ];
        
        const missingFields = [];
        const filledFields = [];
        
        requiredFields.forEach(field => {
            if (!formObject[field] || formObject[field] === '') {
                missingFields.push(field);
            } else {
                filledFields.push(`${field}: "${formObject[field]}"`);
            }
        });
        
        // Display missing fields
        const missingDiv = document.getElementById('missing-fields');
        const filledDiv = document.getElementById('filled-fields');
        
        if (missingFields.length > 0) {
            missingDiv.classList.remove('hidden');
            missingDiv.querySelector('div').textContent = missingFields.join(', ');
        } else {
            missingDiv.classList.add('hidden');
        }
        
        if (filledFields.length > 0) {
            filledDiv.classList.remove('hidden');
            filledDiv.querySelector('div').textContent = filledFields.join('\n');
        } else {
            filledDiv.classList.add('hidden');
        }
        
        // Send to debug endpoint
        try {
            const response = await fetch('{{ route("system.debug") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify(formObject)
            });
            
            const debugData = await response.json();
            console.log('Debug endpoint response:', debugData);
            
            // Update debug display with server response
            const serverDebug = document.createElement('div');
            serverDebug.className = 'mt-4 bg-blue-900/30 border border-blue-800 rounded p-3';
            serverDebug.innerHTML = `
                <h5 class="font-medium text-blue-300 mb-2">Server Debug Response:</h5>
                <pre class="text-sm overflow-auto">${JSON.stringify(debugData, null, 2)}</pre>
            `;
            
            resultDiv.appendChild(serverDebug);
        } catch (error) {
            console.error('Debug request failed:', error);
        }
    }

    // Initialize debug on page load if debug mode is active
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-show debug if URL has debug parameter
        if (window.location.search.includes('debug=true') || {{ config('app.debug') ? 'true' : 'false' }}) {
            toggleDebug();
        }
        
        // Add debug parameter to form submission
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function() {
                // Add debug parameter to form data
                const debugInput = document.createElement('input');
                debugInput.type = 'hidden';
                debugInput.name = 'debug';
                debugInput.value = 'true';
                this.appendChild(debugInput);
            });
        }
        
        // Monitor form changes for debugging
        document.querySelectorAll('input, select, textarea').forEach(element => {
            element.addEventListener('change', function() {
                console.log(`Field changed: ${this.name} = ${this.value}`);
                
                // Update debug display if visible
                if (debugVisible) {
                    loadCurrentValues();
                }
            });
        });
    });
</script>
<script>

    // Tab switching functionality
    function switchTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });

        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('border-primary-500', 'text-primary-600', 'dark:text-primary-400');
            button.classList.add('border-transparent', 'text-gray-500', 'dark:text-gray-400');
        });

        // Show selected tab content
        const selectedTab = document.getElementById(`${tabName}-tab-content`);
        if (selectedTab) {
            selectedTab.classList.remove('hidden');
        }

        // Activate selected tab button
        const selectedButton = document.querySelector(`[data-tab="${tabName}"]`);
        if (selectedButton) {
            selectedButton.classList.add('border-primary-500', 'text-primary-600', 'dark:text-primary-400');
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

    // Reset form
    function resetForm() {
        if (confirm('Are you sure you want to reset all changes? This cannot be undone.')) {
            document.querySelector('form').reset();
            // Reset color inputs to their original values
            document.querySelector('input[name="primary_color"]').value = '{{ $system->primary_color }}';
            document.querySelector('input[type="color"][name="primary_color"]').value = '{{ $system->primary_color }}';
            document.querySelector('input[name="secondary_color"]').value = '{{ $system->secondary_color }}';
            document.querySelector('input[type="color"][name="secondary_color"]').value = '{{ $system->secondary_color }}';
        }
    }

    // Meta description character counter
    document.addEventListener('DOMContentLoaded', function() {
        const metaDescTextarea = document.querySelector('textarea[name="meta_description"]');
        const metaDescCount = document.getElementById('meta-desc-count');
        
        if (metaDescTextarea && metaDescCount) {
            metaDescTextarea.addEventListener('input', function() {
                metaDescCount.textContent = this.value.length;
            });
        }

        // Initialize first tab based on URL hash or default to 'general'
        const hash = window.location.hash.substring(1);
        if (hash && ['general', 'notifications', 'security', 'integrations', 'backup', 'design', 'custom'].includes(hash)) {
            switchTab(hash);
        } else {
            switchTab('general');
        }

        // Sync color inputs
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
    });
</script>

<style>
    .tab-button {
        transition: all 0.2s ease-in-out;
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
</style>
@endsection