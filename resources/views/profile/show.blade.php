@extends('layouts.app')

@section('content')
<!-- Breadcrumb Start -->
<div x-data="{ pageName: 'Profile' }">
    @include('partials.breadcrumb', ['pageName' => 'Profile'])
</div>
<!-- Breadcrumb End -->

<div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200" 
     x-data="profileData()" 
     x-init="initProfile()">
    
    <!-- Success Message -->
    <div id="success-message" 
         class="hidden fixed top-20 right-5 z-50 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg transition-all duration-300"
         x-data="{ show: false }"
         x-show="show"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-2"
         @profile-updated.window="show = true; setTimeout(() => show = false, 3000)"
         @profile-updated.window="message = $event.detail.message">
        <div class="flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            <span x-text="message"></span>
        </div>
    </div>

    <!-- Profile Card -->
    <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 lg:p-6">
        <h3 class="mb-5 text-lg font-semibold lg:mb-7">Profile</h3>

        <!-- User Info Section -->
        <div class="mb-6 rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-center xl:justify-between">
                <div class="flex w-full flex-col items-center gap-6 xl:flex-row">
                    <div class="relative group">
                        <div class="h-20 w-20 overflow-hidden rounded-full border border-gray-200 dark:border-gray-700">
                            <img id="avatar-preview" 
                                 src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                                 alt="Profile Picture" 
                                 class="h-full w-full object-cover">
                        </div>
                        <button @click="openModal('avatarModal')"
                                class="absolute bottom-0 right-0 bg-blue-600 text-white p-1.5 rounded-full shadow-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                            <i class="fas fa-camera text-xs"></i>
                        </button>
                    </div>
                    <div class="order-3 xl:order-2">
                        <h4 class="mb-2 text-center text-lg font-semibold xl:text-left" x-text="user.name">{{ $user->name }}</h4>
                        <div class="flex flex-col items-center gap-1 text-center xl:flex-row xl:gap-3 xl:text-left">
                            <p class="text-sm text-gray-500 dark:text-gray-400" x-text="user.role || 'User'">{{ $user->role ?: 'User' }}</p>
                            <div class="hidden h-3.5 w-px bg-gray-300 dark:bg-gray-700 xl:block"></div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                <span x-text="user.city || ''"></span>
                                <span x-show="user.city && user.country">, </span>
                                <span x-text="user.country || 'No location set'"></span>
                            </p>
                        </div>
                    </div>
                    <div class="order-2 flex grow items-center gap-2 xl:order-3 xl:justify-end" id="social-links">
                        @php
                            $social = json_decode($user->social, true) ?? [];
                        @endphp
                        
                        @if(!empty($social['facebook']))
                        <a href="{{ $social['facebook'] }}" target="_blank" 
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                            </svg>
                        </a>
                        @endif
                        
                        @if(!empty($social['twitter']))
                        <a href="{{ $social['twitter'] }}" target="_blank" 
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-5 w-5 text-blue-400 dark:text-blue-300" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.213c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                            </svg>
                        </a>
                        @endif
                        
                        @if(!empty($social['linkedin']))
                        <a href="{{ $social['linkedin'] }}" target="_blank" 
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-5 w-5 text-blue-700 dark:text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                            </svg>
                        </a>
                        @endif
                        
                        @if(!empty($social['instagram']))
                        <a href="{{ $social['instagram'] }}" target="_blank" 
                        class="flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors">
                            <svg class="h-5 w-5 text-pink-600 dark:text-pink-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                            </svg>
                        </a>
                        @endif
                    </div>
                </div>
                <button @click="openModal('personalInfoModal')"
                        class="mt-4 flex w-full items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-4 py-3 text-sm font-medium text-gray-700 shadow-theme-xs hover:bg-gray-50 hover:text-gray-800 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700 lg:inline-flex lg:w-auto">
                    <i class="fas fa-edit"></i> Edit
                </button>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="mb-6 rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold">Personal Information</h4>
                        <button @click="openModal('personalInfoModal')"
                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Full Name</p>
                            <p class="text-sm font-medium" x-text="user.name || '{{ $user->name }}'">{{ $user->name }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Email address</p>
                            <p class="text-sm font-medium" x-text="user.email || '{{ $user->email }}'">{{ $user->email }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Phone</p>
                            <p class="text-sm font-medium" x-text="user.phone || '{{ $user->phone ?: 'Not set' }}'">{{ $user->phone ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Bio</p>
                            <p class="text-sm font-medium" x-text="user.bio || '{{ $user->bio ?: 'No bio yet' }}'">{{ $user->bio ?: 'No bio yet' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Address Section -->
        <div class="rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold">Address Information</h4>
                        <button @click="openModal('addressModal')"
                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Country</p>
                            <p class="text-sm font-medium" x-text="user.country || '{{ $user->country ?: 'Not set' }}'">{{ $user->country ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">City</p>
                            <p class="text-sm font-medium" x-text="user.city || '{{ $user->city ?: 'Not set' }}'">{{ $user->city ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">State/Province</p>
                            <p class="text-sm font-medium" x-text="user.state || '{{ $user->state ?: 'Not set' }}'">{{ $user->state ?: 'Not set' }}</p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Postal Code</p>
                            <p class="text-sm font-medium" x-text="user.postal_code || '{{ $user->postal_code ?: 'Not set' }}'">{{ $user->postal_code ?: 'Not set' }}</p>
                        </div>
                        <div class="lg:col-span-2">
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">TAX ID</p>
                            <p class="text-sm font-medium" x-text="user.tax_id || '{{ $user->tax_id ?: 'Not set' }}'">{{ $user->tax_id ?: 'Not set' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Social Media Section -->
        <div class="mt-6 rounded-2xl border border-gray-200 p-5 dark:border-gray-700 lg:p-6">
            <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
                <div class="w-full">
                    <div class="flex items-center justify-between mb-4">
                        <h4 class="text-lg font-semibold">Social Media Links</h4>
                        <button @click="openModal('socialModal')"
                                class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:gap-7 2xl:gap-x-32">
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Facebook</p>
                            <p class="text-sm font-medium" x-text="social.facebook ? '@' + social.facebook : 'Not set'">
                                @if(!empty($socialUsernames['facebook']))
                                    @{{ '@' . $socialUsernames['facebook'] }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Twitter</p>
                            <p class="text-sm font-medium" x-text="social.twitter ? '@' + social.twitter : 'Not set'">
                                @if(!empty($socialUsernames['twitter']))
                                    @{{ '@' . $socialUsernames['twitter'] }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">LinkedIn</p>
                            <p class="text-sm font-medium" x-text="social.linkedin ? '@' + social.linkedin : 'Not set'">
                                @if(!empty($socialUsernames['linkedin']))
                                    @{{ '@' . $socialUsernames['linkedin'] }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="mb-2 text-xs leading-normal text-gray-500 dark:text-gray-400">Instagram</p>
                            <p class="text-sm font-medium" x-text="social.instagram ? '@' + social.instagram : 'Not set'">
                                @if(!empty($socialUsernames['instagram']))
                                    @{{ '@' . $socialUsernames['instagram'] }}
                                @else
                                    Not set
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Overlay -->
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 transition-opacity duration-300"
         x-show="modalOpen"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click.self="closeModal"
         @keydown.escape.window="closeModal">
        
        <!-- Personal Information Modal -->
        <div class="w-full max-w-lg transform rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl transition-all duration-300"
             x-show="activeModal === 'personalInfoModal'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold">Edit Personal Information</h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="personalInfoForm" @submit.prevent="updatePersonalInfo" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div>
                    <label class="mb-2 block text-sm font-medium">Full Name *</label>
                    <input type="text" name="name" x-model="user.name" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900"
                           required>
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">Email *</label>
                    <input type="email" name="email" x-model="user.email" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900"
                           required>
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">Phone Number</label>
                    <input type="tel" name="phone" x-model="user.phone" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">Bio</label>
                    <textarea name="bio" x-model="user.bio" rows="3" 
                              class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900"
                              maxlength="500"></textarea>
                    <p class="mt-1 text-xs text-gray-500" x-text="`${user.bio?.length || 0}/500 characters`"></p>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModal" 
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :disabled="loading">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading">
                        <span x-show="!loading">Save Changes</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Address Modal -->
        <div class="w-full max-w-lg transform rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl transition-all duration-300"
             x-show="activeModal === 'addressModal'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold">Edit Address Information</h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="addressForm" @submit.prevent="updateAddress" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Country</label>
                        <input type="text" name="country" x-model="user.country" 
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium">City</label>
                        <input type="text" name="city" x-model="user.city" 
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                    </div>
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">State/Province</label>
                    <input type="text" name="state" x-model="user.state" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">Postal Code</label>
                    <input type="text" name="postal_code" x-model="user.postal_code" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                
                <div>
                    <label class="mb-2 block text-sm font-medium">TAX ID</label>
                    <input type="text" name="tax_id" x-model="user.tax_id" 
                           class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModal" 
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :disabled="loading">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading">
                        <span x-show="!loading">Save Changes</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Avatar Modal -->
        <div class="w-full max-w-md transform rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl transition-all duration-300"
             x-show="activeModal === 'avatarModal'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold">Update Profile Picture</h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="avatarForm" @submit.prevent="updateAvatar" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="text-center">
                    <div class="mx-auto mb-4 h-32 w-32 overflow-hidden rounded-full border-4 border-gray-200 dark:border-gray-700">
                        <img id="avatar-modal-preview" 
                             src="{{ $user->avatar ? asset('storage/' . $user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7F9CF5&background=EBF4FF' }}" 
                             alt="Profile Picture" 
                             class="h-full w-full object-cover">
                    </div>
                    
                    <div>
                        <input type="file" name="avatar" id="avatarInput" 
                               accept="image/*" 
                               @change="previewAvatar" 
                               class="hidden">
                        <label for="avatarInput" 
                               class="cursor-pointer rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-900 dark:hover:bg-gray-700">
                            Choose File
                        </label>
                        <p class="mt-2 text-xs text-gray-500" id="fileName">No file chosen</p>
                        <p class="mt-1 text-xs text-gray-500">Max file size: 2MB • Supported: JPG, PNG, GIF, WEBP</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModal" 
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :disabled="loading">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading">
                        <span x-show="!loading">Upload</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Uploading...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Social Media Modal -->
        <div class="w-full max-w-lg transform rounded-2xl bg-white dark:bg-gray-800 p-6 shadow-2xl transition-all duration-300"
             x-show="activeModal === 'socialModal'"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-4">
            <div class="mb-6 flex items-center justify-between">
                <h3 class="text-xl font-semibold">Edit Social Media Links</h3>
                <button @click="closeModal" class="text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="socialForm" @submit.prevent="updateSocial" class="space-y-4">
                @csrf
                @method('PUT')
                
                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-sm font-medium">Facebook</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">@</span>
                            <input type="text" name="social[facebook]" x-model="social.facebook" 
                                   placeholder="username"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter username without @ symbol or full URL</p>
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium">Twitter</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">@</span>
                            <input type="text" name="social[twitter]" x-model="social.twitter" 
                                   placeholder="username"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter username without @ symbol or full URL</p>
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium">LinkedIn</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">@</span>
                            <input type="text" name="social[linkedin]" x-model="social.linkedin" 
                                   placeholder="username"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter username without @ symbol or full URL</p>
                    </div>
                    
                    <div>
                        <label class="mb-2 block text-sm font-medium">Instagram</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">@</span>
                            <input type="text" name="social[instagram]" x-model="social.instagram" 
                                   placeholder="username"
                                   class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-700 dark:bg-gray-900">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Enter username without @ symbol or full URL</p>
                    </div>
                </div>
                
                <div class="flex justify-end gap-3 pt-4">
                    <button type="button" @click="closeModal" 
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-700"
                            :disabled="loading">
                        Cancel
                    </button>
                    <button type="submit" 
                            class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 focus:ring-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                            :disabled="loading">
                        <span x-show="!loading">Save Changes</span>
                        <span x-show="loading" class="flex items-center gap-2">
                            <i class="fas fa-spinner fa-spin"></i>
                            Saving...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function profileData() {
    return {
        modalOpen: false,
        activeModal: null,
        user: {
            name: '{{ $user->name }}',
            email: '{{ $user->email }}',
            phone: '{{ $user->phone ?? "" }}',
            bio: '{{ $user->bio ?? "" }}',
            country: '{{ $user->country ?? "" }}',
            city: '{{ $user->city ?? "" }}',
            state: '{{ $user->state ?? "" }}',
            postal_code: '{{ $user->postal_code ?? "" }}',
            tax_id: '{{ $user->tax_id ?? "" }}'
        },
        social: {
            facebook: '{{ isset($socialUsernames["facebook"]) ? $socialUsernames["facebook"] : "" }}',
            twitter: '{{ isset($socialUsernames["twitter"]) ? $socialUsernames["twitter"] : "" }}',
            linkedin: '{{ isset($socialUsernames["linkedin"]) ? $socialUsernames["linkedin"] : "" }}',
            instagram: '{{ isset($socialUsernames["instagram"]) ? $socialUsernames["instagram"] : "" }}'
        },
        loading: false,

        initProfile() {
            console.log('Profile initialized');
        },

        openModal(modalName) {
            this.activeModal = modalName;
            this.modalOpen = true;
            document.body.classList.add('overflow-hidden');
        },

        closeModal() {
            this.modalOpen = false;
            this.activeModal = null;
            setTimeout(() => {
                document.body.classList.remove('overflow-hidden');
            }, 300);
        },

        async updatePersonalInfo() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("profile.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        name: this.user.name,
                        email: this.user.email,
                        phone: this.user.phone,
                        bio: this.user.bio
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.closeModal();
                    this.showSuccess('Personal information updated successfully!');
                    
                    // Update UI
                    this.user.name = data.user.name;
                    this.user.email = data.user.email;
                    this.user.phone = data.user.phone;
                    this.user.bio = data.user.bio;
                } else {
                    throw new Error(data.message || data.error || 'Update failed');
                }
            } catch (error) {
                console.error('Update error:', error);
                alert('Error updating profile: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async updateAddress() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("profile.address.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        country: this.user.country,
                        city: this.user.city,
                        state: this.user.state,
                        postal_code: this.user.postal_code,
                        tax_id: this.user.tax_id
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.closeModal();
                    this.showSuccess('Address information updated successfully!');
                    
                    // Update UI
                    this.user.country = data.user.country;
                    this.user.city = data.user.city;
                    this.user.state = data.user.state;
                    this.user.postal_code = data.user.postal_code;
                    this.user.tax_id = data.user.tax_id;
                } else {
                    throw new Error(data.message || data.error || 'Update failed');
                }
            } catch (error) {
                console.error('Update error:', error);
                alert('Error updating address: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async updateSocial() {
            this.loading = true;
            
            try {
                const response = await fetch('{{ route("profile.update") }}', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        _method: 'PUT',
                        social: this.social
                    })
                });

                const data = await response.json();

                if (data.success) {
                    this.closeModal();
                    this.showSuccess('Social media links updated successfully!');
                    
                    // Parse and update social links from user data
                    if (data.user.social) {
                        const socialData = JSON.parse(data.user.social);
                        this.updateSocialLinksUI(socialData);
                    }
                } else {
                    throw new Error(data.message || data.error || 'Update failed');
                }
            } catch (error) {
                console.error('Update error:', error);
                alert('Error updating social links: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async updateAvatar() {
            this.loading = true;
            
            const formData = new FormData();
            const fileInput = document.getElementById('avatarInput');
            
            if (!fileInput.files[0]) {
                alert('Please select a file');
                this.loading = false;
                return;
            }
            
            formData.append('_method', 'PUT');
            formData.append('avatar', fileInput.files[0]);

            try {
                const response = await fetch('{{ route("profile.update") }}', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });

                const data = await response.json();

                if (data.success) {
                    // Update preview images
                    if (data.avatar_url) {
                        document.getElementById('avatar-preview').src = data.avatar_url;
                        document.getElementById('avatar-modal-preview').src = data.avatar_url;
                    }
                    
                    this.closeModal();
                    this.showSuccess('Profile picture updated successfully!');
                    
                    // Reset file input
                    fileInput.value = '';
                    document.getElementById('fileName').textContent = 'No file chosen';
                } else {
                    throw new Error(data.message || data.error || 'Upload failed');
                }
            } catch (error) {
                console.error('Avatar update error:', error);
                alert('Error updating avatar: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        previewAvatar(event) {
            const file = event.target.files[0];
            const fileNameElement = document.getElementById('fileName');
            
            if (file) {
                // Check file size (2MB = 2 * 1024 * 1024)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2MB');
                    event.target.value = '';
                    fileNameElement.textContent = 'No file chosen';
                    return;
                }
                
                fileNameElement.textContent = file.name;
                
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('avatar-modal-preview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        updateSocialLinksUI(socialData) {
            const socialContainer = document.getElementById('social-links');
            socialContainer.innerHTML = '';
            
            // Create social links based on data
            const socialIcons = {
                facebook: {
                    icon: '<svg class="h-5 w-5 text-blue-600 dark:text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
                    url: socialData.facebook
                },
                twitter: {
                    icon: '<svg class="h-5 w-5 text-blue-400 dark:text-blue-300" fill="currentColor" viewBox="0 0 24 24"><path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.213c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/></svg>',
                    url: socialData.twitter
                },
                linkedin: {
                    icon: '<svg class="h-5 w-5 text-blue-700 dark:text-blue-600" fill="currentColor" viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
                    url: socialData.linkedin
                },
                instagram: {
                    icon: '<svg class="h-5 w-5 text-pink-600 dark:text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>',
                    url: socialData.instagram
                }
            };
            
            Object.entries(socialData).forEach(([platform, url]) => {
                if (url && socialIcons[platform]) {
                    const link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.className = 'flex h-11 w-11 items-center justify-center rounded-full border border-gray-300 bg-white shadow-theme-xs hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:hover:bg-gray-700 transition-colors';
                    link.innerHTML = socialIcons[platform].icon;
                    socialContainer.appendChild(link);
                }
            });
            
            // Also update the social display text
            this.social.facebook = socialData.facebook ? this.extractUsername(socialData.facebook) : '';
            this.social.twitter = socialData.twitter ? this.extractUsername(socialData.twitter) : '';
            this.social.linkedin = socialData.linkedin ? this.extractUsername(socialData.linkedin, 'linkedin.com/in/') : '';
            this.social.instagram = socialData.instagram ? this.extractUsername(socialData.instagram) : '';
        },

        extractUsername(url, prefix = '') {
            if (!url) return '';
            if (!url.startsWith('http')) return url;
            
            const prefixes = [
                'https://facebook.com/',
                'https://www.facebook.com/',
                'https://twitter.com/',
                'https://www.twitter.com/',
                'https://linkedin.com/in/',
                'https://www.linkedin.com/in/',
                'https://instagram.com/',
                'https://www.instagram.com/',
            ];
            
            for (const p of prefixes) {
                if (url.startsWith(p)) {
                    return url.substring(p.length);
                }
            }
            
            return url;
        },

        showSuccess(message) {
            window.dispatchEvent(new CustomEvent('profile-updated', {
                detail: { message }
            }));
        }
    }
}
</script>
@endsection