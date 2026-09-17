<!-- resources/views/profile/edit.blade.php -->
@extends('layouts.app')

@section('content')
    <div x-data="{ pageName: 'Edit Profile' }">
        @include('partials.breadcrumb', ['pageName' => 'Edit Profile'])
    </div>
    
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200 transition-colors duration-200" x-data="profileData()">
    

        <!-- Success Message -->
        <div id="success-message" class="hidden mb-4 p-4 rounded-lg bg-green-50 dark:bg-green-900/30 text-green-800 dark:text-green-200 border border-green-200 dark:border-green-800">
            Profile updated successfully!
        </div>

        <!-- Error Message -->
        <div id="error-message" class="hidden mb-4 p-4 rounded-lg bg-red-50 dark:bg-red-900/30 text-red-800 dark:text-red-200 border border-red-200 dark:border-red-800">
            Error updating profile. Please try again.
        </div>

        <!-- Profile Edit Form -->
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-800 lg:p-6">
            <h3 class="text-lg font-semibold mb-6">Edit Profile</h3>

            <form id="profile-form" method="POST" @submit.prevent="saveProfile" enctype="multipart/form-data">
                @csrf
                @method('PATCH')

                <!-- Profile Image -->
                <div class="mb-8">
                    <h5 class="mb-4 text-lg font-medium">Profile Image</h5>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <!-- Current Avatar -->
                        <div class="flex flex-col items-center">
                            <div class="relative">
                                <img :src="avatarPreview || '{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png') }}'" 
                                        alt="Profile Picture" 
                                        class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 dark:border-gray-700">
                                <div x-show="isUploading" class="absolute inset-0 bg-black/50 flex items-center justify-center rounded-full">
                                    <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-white"></div>
                                </div>
                            </div>
                            <div class="mt-3 flex gap-2">
                                <label for="avatar-upload" class="cursor-pointer px-4 py-2 bg-blue-600 text-white text-sm rounded-lg hover:bg-blue-700 transition-colors">
                                    Change Photo
                                </label>
                                <button type="button" @click="removeAvatar" x-show="hasAvatar" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                                    Remove
                                </button>
                            </div>
                            <input type="file" id="avatar-upload" name="avatar" accept="images/*" class="hidden" @change="handleAvatarUpload">
                            <div id="avatar-error" class="mt-1 text-sm text-red-600 hidden"></div>
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">JPG, PNG, GIF or WEBP. Max 2MB.</p>
                        </div>
                        
                        <!-- Upload Instructions -->
                        <div class="text-sm text-gray-600 dark:text-gray-400">
                            <p class="font-medium mb-2">Upload a clear photo of yourself</p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>Use a high-quality image (minimum 200x200 pixels)</li>
                                <li>Ensure your face is clearly visible</li>
                                <li>Square images work best</li>
                                <li>File size should not exceed 2MB</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Basic Information -->
                <div class="mb-8">
                    <h5 class="mb-4 text-lg font-medium">Basic Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium">Full Name</label>
                            <input type="text" name="name" x-model="formData.name" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="name-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Email Address</label>
                            <input type="email" name="email" x-model="formData.email" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="email-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Phone Number</label>
                            <input type="tel" name="phone" x-model="formData.phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="phone-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                    </div>
                    
                    <!-- Bio -->
                    <div class="mt-4">
                        <label class="block mb-2 text-sm font-medium">Bio</label>
                        <textarea name="bio" x-model="formData.bio" rows="4" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500"></textarea>
                        <div id="bio-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Tell us a little about yourself (max 500 characters)</p>
                    </div>
                </div>

                <!-- Social Media Links -->
                <div class="mb-8">
                    <h5 class="mb-4 text-lg font-medium">Social Media Links</h5>
                    <p class="mb-4 text-sm text-gray-500 dark:text-gray-400">
                        Enter your username (e.g., username) or full URL. Usernames will be automatically converted to links.
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium">Facebook</label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500">facebook.com/</span>
                                <input type="text" name="social[facebook]" x-model="formData.social.facebook" placeholder="username" class="w-full px-4 py-2.5 rounded-r-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div id="social-facebook-error" class="mt-1 text-sm text-red-600 hidden"></div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter username (e.g., johndoe) or full URL</p>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Twitter</label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500">twitter.com/</span>
                                <input type="text" name="social[twitter]" x-model="formData.social.twitter" placeholder="username" class="w-full px-4 py-2.5 rounded-r-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div id="social-twitter-error" class="mt-1 text-sm text-red-600 hidden"></div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter username (e.g., johndoe) or full URL</p>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">LinkedIn</label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500">linkedin.com/in/</span>
                                <input type="text" name="social[linkedin]" x-model="formData.social.linkedin" placeholder="username" class="w-full px-4 py-2.5 rounded-r-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div id="social-linkedin-error" class="mt-1 text-sm text-red-600 hidden"></div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter username (e.g., johndoe) or full URL</p>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Instagram</label>
                            <div class="flex items-center">
                                <span class="px-3 py-2.5 bg-gray-100 dark:bg-gray-700 border border-r-0 border-gray-300 dark:border-gray-600 rounded-l-lg text-gray-500">instagram.com/</span>
                                <input type="text" name="social[instagram]" x-model="formData.social.instagram" placeholder="username" class="w-full px-4 py-2.5 rounded-r-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            <div id="social-instagram-error" class="mt-1 text-sm text-red-600 hidden"></div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Enter username (e.g., johndoe) or full URL</p>
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-8">
                    <h5 class="mb-4 text-lg font-medium">Address Information</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block mb-2 text-sm font-medium">Country</label>
                            <input type="text" name="country" x-model="formData.country" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="country-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">City</label>
                            <input type="text" name="city" x-model="formData.city" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="city-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                        <div>
                            <label class="block mb-2 text-sm font-medium">Postal Code</label>
                            <input type="text" name="postal_code" x-model="formData.postal_code" class="w-full px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <div id="postal_code-error" class="mt-1 text-sm text-red-600 hidden"></div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block mb-2 text-sm font-medium">Tax ID</label>
                        <input type="text" name="tax_id" x-model="formData.tax_id" class="w-full md:w-1/2 px-4 py-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-900 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <div id="tax_id-error" class="mt-1 text-sm text-red-600 hidden"></div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6">
                    <a href="{{ route('profile.show') }}" class="px-5 py-2.5 rounded-lg border border-gray-300 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" :disabled="isSaving" class="px-5 py-2.5 rounded-lg bg-blue-600 text-white hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span x-text="isSaving ? 'Saving...' : 'Save Changes'"></span>
                    </button>
                </div>
            </form>
        </div>
    </div>

<script>
    // JavaScript version of the function
    function extractUsernameFromUrl(url, prefix = '') {
        if (!url) return '';
        // If it's already a username (no http), return it
        if (!url.startsWith('http')) return url;
        
        // Remove the base URL to get username
        const prefixes = [
            'https://facebook.com/',
            'https://www.facebook.com/',
            'https://twitter.com/',
            'https://www.twitter.com/',
            'https://linkedin.com/in/',
            'https://www.linkedin.com/in/',
            'https://instagram.com/',
            'https://www.instagram.com/',
            'http://facebook.com/',
            'http://www.facebook.com/',
            'http://twitter.com/',
            'http://www.twitter.com/',
            'http://linkedin.com/in/',
            'http://www.linkedin.com/in/',
            'http://instagram.com/',
            'http://www.instagram.com/'
        ];
        
        for (const p of prefixes) {
            if (url.startsWith(p)) {
                return url.substring(p.length);
            }
        }
        
        return url; // Return as-is if no match
    }

    function profileData() {
        return {
            formData: {
                name: '{{ addslashes($user->name) }}',
                email: '{{ addslashes($user->email) }}',
                phone: '{{ addslashes($user->phone) }}',
                bio: '{{ addslashes($user->bio) }}',
                country: '{{ addslashes($user->country) }}',
                city: '{{ addslashes($user->city) }}',
                postal_code: '{{ addslashes($user->postal_code) }}',
                tax_id: '{{ addslashes($user->tax_id) }}',
                social: {
                    facebook: '{{ $socialUsernames["facebook"] ?? "" }}',
                    twitter: '{{ $socialUsernames["twitter"] ?? "" }}',
                    linkedin: '{{ $socialUsernames["linkedin"] ?? "" }}',
                    instagram: '{{ $socialUsernames["instagram"] ?? "" }}',
                }
            },
            avatarFile: null,
            avatarPreview: null,
            isSaving: false,
            isUploading: false,
            hasAvatar: {{ $user->avatar ? 'true' : 'false' }},
            
            init() {
                // Initialize any additional setup if needed
                @if($errors->any())
                    this.showErrors(@json($errors->getMessages()));
                @endif
            },
            
            handleAvatarUpload(event) {
                const file = event.target.files[0];
                if (!file) return;
                
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    this.showError('File size must be less than 2MB');
                    event.target.value = '';
                    return;
                }
                
                // Validate file type
                const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                if (!validTypes.includes(file.type)) {
                    this.showError('Invalid file type. Please upload JPG, PNG, GIF, or WEBP.');
                    event.target.value = '';
                    return;
                }
                
                this.avatarFile = file;
                this.hasAvatar = true;
                
                // Create preview
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.avatarPreview = e.target.result;
                };
                reader.readAsDataURL(file);
            },
            
            async removeAvatar() {
                if (!confirm('Are you sure you want to remove your profile picture?')) {
                    return;
                }
                
                this.isUploading = true;
                try {
                    const response = await fetch('{{ route("profile.delete-avatar") }}', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.avatarPreview = null;
                        this.avatarFile = null;
                        this.hasAvatar = false;
                        this.showSuccess('Avatar removed successfully');
                    } else {
                        this.showError(data.message || 'Error removing avatar');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('An unexpected error occurred.');
                } finally {
                    this.isUploading = false;
                }
            },
            
            async saveProfile() {
                this.isSaving = true;
                this.clearMessages();
                this.clearErrors();
                
                try {
                    const formData = new FormData();
                    
                    // Add all form data
                    Object.keys(this.formData).forEach(key => {
                        if (key === 'social') {
                            // For social, we need to send each platform separately
                            Object.keys(this.formData.social).forEach(platform => {
                                formData.append(`social[${platform}]`, this.formData.social[platform]);
                            });
                        } else {
                            formData.append(key, this.formData[key]);
                        }
                    });
                    
                    // Add method override
                    formData.append('_method', 'PATCH');
                    
                    // Add avatar file if selected
                    if (this.avatarFile) {
                        formData.append('avatar', this.avatarFile);
                    }
                    
                    const response = await fetch('{{ route("profile.update") }}', {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    });
                    
                    const data = await response.json();
                    
                    if (data.success) {
                        this.showSuccess(data.message);
                        
                        // Update form data with fresh user data
                        if (data.user) {
                            this.updateFormData(data.user);
                        }
                        
                        // Update social data if returned
                        if (data.social) {
                            this.formData.social.facebook = extractUsernameFromUrl(data.social.facebook || '');
                            this.formData.social.twitter = extractUsernameFromUrl(data.social.twitter || '');
                            this.formData.social.linkedin = extractUsernameFromUrl(data.social.linkedin || '', 'linkedin.com/in/');
                            this.formData.social.instagram = extractUsernameFromUrl(data.social.instagram || '');
                        }
                        
                        // Update avatar preview if avatar was uploaded
                        if (data.avatar_url) {
                            this.avatarPreview = data.avatar_url;
                            this.hasAvatar = true;
                        }
                        
                        // Clear file input
                        this.avatarFile = null;
                        document.getElementById('avatar-upload').value = '';
                        
                    } else {
                        this.showError(data.message);
                        if (data.errors) {
                            this.showErrors(data.errors);
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    this.showError('An unexpected error occurred. Please try again.');
                } finally {
                    this.isSaving = false;
                }
            },
                        
            updateFormData(user) {
                // Update form data with user object
                this.formData.name = user.name;
                this.formData.email = user.email;
                this.formData.phone = user.phone;
                this.formData.bio = user.bio;
                this.formData.country = user.country;
                this.formData.city = user.city;
                this.formData.postal_code = user.postal_code;
                this.formData.tax_id = user.tax_id;
                
                // Parse social JSON and extract usernames
                if (user.social) {
                    const social = typeof user.social === 'string' ? JSON.parse(user.social) : user.social;
                    this.formData.social.facebook = extractUsernameFromUrl(social.facebook || '');
                    this.formData.social.twitter = extractUsernameFromUrl(social.twitter || '');
                    this.formData.social.linkedin = extractUsernameFromUrl(social.linkedin || '', 'linkedin.com/in/');
                    this.formData.social.instagram = extractUsernameFromUrl(social.instagram || '');
                }
            },
            
            showSuccess(message) {
                const successDiv = document.getElementById('success-message');
                successDiv.textContent = message || 'Profile updated successfully!';
                successDiv.classList.remove('hidden');
                
                // Hide success message after 5 seconds
                setTimeout(() => {
                    successDiv.classList.add('hidden');
                }, 5000);
            },
            
            showError(message) {
                const errorDiv = document.getElementById('error-message');
                errorDiv.textContent = message || 'Error updating profile. Please try again.';
                errorDiv.classList.remove('hidden');
            },
            
            showErrors(errors) {
                for (const [field, messages] of Object.entries(errors)) {
                    const errorDiv = document.getElementById(`${field.replace('.', '-')}-error`);
                    if (errorDiv) {
                        errorDiv.textContent = messages[0];
                        errorDiv.classList.remove('hidden');
                    }
                }
            },
            
            clearMessages() {
                document.getElementById('success-message').classList.add('hidden');
                document.getElementById('error-message').classList.add('hidden');
            },
            
            clearErrors() {
                // Clear all error messages
                document.querySelectorAll('[id$="-error"]').forEach(element => {
                    element.classList.add('hidden');
                    element.textContent = '';
                });
            }
        }
    }
</script>
@endsection