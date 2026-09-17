<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();
        $social = json_decode($user->social, true) ?? [];
        
        // Extract usernames from URLs for display
        $socialUsernames = [
            'facebook' => $this->extractUsernameFromUrl($social['facebook'] ?? ''),
            'twitter' => $this->extractUsernameFromUrl($social['twitter'] ?? ''),
            'linkedin' => $this->extractUsernameFromUrl($social['linkedin'] ?? '', 'linkedin.com/in/'),
            'instagram' => $this->extractUsernameFromUrl($social['instagram'] ?? ''),
        ];
        
        return view('profile.edit', [
            'user' => $user,
            'socialUsernames' => $socialUsernames,
        ]);
    }

    public function show(Request $request): View
    {
        $user = $request->user();
        $social = json_decode($user->social, true) ?? [];
        
        // Extract usernames from URLs for display
        $socialUsernames = [
            'facebook' => $this->extractUsernameFromUrl($social['facebook'] ?? ''),
            'twitter' => $this->extractUsernameFromUrl($social['twitter'] ?? ''),
            'linkedin' => $this->extractUsernameFromUrl($social['linkedin'] ?? '', 'linkedin.com/in/'),
            'instagram' => $this->extractUsernameFromUrl($social['instagram'] ?? ''),
        ];
        
        return view('profile.show', [
            'user' => $user,
            'socialUsernames' => $socialUsernames,
        ]);
    }

public function update(ProfileUpdateRequest $request): RedirectResponse|JsonResponse
{
    // Check if it's an AJAX request
    if ($request->expectsJson() || $request->ajax()) {
        try {
            $user = $request->user();
            
            // Validate the request with ALL fields
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => [
                    'required',
                    'string',
                    'email',
                    'max:255',
                    Rule::unique('users')->ignore($user->id),
                ],
                'phone' => 'nullable|string|max:20',
                'bio' => 'nullable|string|max:500',
                'country' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'tax_id' => 'nullable|string|max:50',
                'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
            ]);
            
            // Handle avatar upload - using same structure as logos
            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                // Get original extension
                $extension = $file->getClientOriginalExtension();
                
                // Generate unique filename
                $filename = 'avatar-' . time() . '.' . $extension;
                
                // Define storage path - using same pattern as logos
                $folder = 'images/avatars';
                
                // Ensure directory exists
                if (!Storage::disk('public')->exists($folder)) {
                    Storage::disk('public')->makeDirectory($folder, 0755, true);
                }
                
                // Delete old avatar if exists
                if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                    Storage::disk('public')->delete($user->avatar);
                }
                
                // Store new avatar in 'images/avatars' folder
                $path = $file->storeAs($folder, $filename, 'public');
                $validated['avatar'] = $path; // This will be 'images/avatars/avatar-1234567890.jpg'
            }
            
            // Handle social links
            $socialData = [];
            $socialPlatforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
            
            foreach ($socialPlatforms as $platform) {
                $value = $request->input("social.{$platform}");
                if (!empty($value)) {
                    // Remove any whitespace
                    $value = trim($value);
                    
                    // If it starts with @, it's a username
                    if (str_starts_with($value, '@')) {
                        $username = substr($value, 1);
                        $socialData[$platform] = $this->formatSocialLink($platform, $username);
                    } 
                    // If it's already a full URL, use it as is
                    elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                        $socialData[$platform] = $value;
                    }
                    // Otherwise, assume it's a username without @
                    else {
                        $socialData[$platform] = $this->formatSocialLink($platform, $value);
                    }
                }
            }
            
            // Only encode if we have social data
            if (!empty($socialData)) {
                $validated['social'] = json_encode($socialData);
            } else {
                $validated['social'] = null;
            }
            
            // Fill and save user data
            $user->fill($validated);
            
            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }
            
            $user->save();
            
            // Return success response with full data
            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
                'avatar_url' => $user->avatar ? asset('storage/' . $user->avatar) : asset('images/default-avatar.png'),
                'social' => json_decode($user->social, true) ?? []
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating profile: ' . $e->getMessage(),
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    // Original form submission handling (for non-AJAX requests)
    try {
        $user = $request->user();
        
        // Validate for non-AJAX requests
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'tax_id' => 'nullable|string|max:50',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);
        
        // Handle avatar upload for non-AJAX
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $filename = 'avatar-' . $extension;
            $folder = 'images/avatars';
            
            // Ensure directory exists
            if (!Storage::disk('public')->exists($folder)) {
                Storage::disk('public')->makeDirectory($folder, 0755, true);
            }
            
            // Delete old avatar
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }
            
            // Store new avatar
            $path = $file->storeAs($folder, $filename, 'public');
            $validated['avatar'] = $path;
        }
        
        // Handle social links for non-AJAX
        $socialData = [];
        $socialPlatforms = ['facebook', 'twitter', 'linkedin', 'instagram'];
        
        foreach ($socialPlatforms as $platform) {
            $value = $request->input("social.{$platform}");
            if (!empty($value)) {
                $value = trim($value);
                
                if (str_starts_with($value, '@')) {
                    $username = substr($value, 1);
                    $socialData[$platform] = $this->formatSocialLink($platform, $username);
                } elseif (filter_var($value, FILTER_VALIDATE_URL)) {
                    $socialData[$platform] = $value;
                } else {
                    $socialData[$platform] = $this->formatSocialLink($platform, $value);
                }
            }
        }
        
        if (!empty($socialData)) {
            $validated['social'] = json_encode($socialData);
        } else {
            $validated['social'] = null;
        }
        
        $user->fill($validated);
        
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        
        $user->save();
        
        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('success', 'Profile updated successfully!');
            
    } catch (\Illuminate\Validation\ValidationException $e) {
        return Redirect::route('profile.edit')
            ->withErrors($e->errors())
            ->withInput();
            
    } catch (\Exception $e) {
        return Redirect::route('profile.edit')
            ->with('error', 'Error updating profile: ' . $e->getMessage());
    }
}

    public function deleteAvatar(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if ($user->avatar) {
                // Delete from storage
                Storage::disk('public')->delete($user->avatar);
                $user->avatar = null;
                $user->save();
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Avatar deleted successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting avatar',
                'error' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Extract username from social media URL
     */
    private function extractUsernameFromUrl(?string $url, string $prefix = ''): string
    {
        if (empty($url)) {
            return '';
        }
        
        // If it's already a username (no http), return it
        if (!str_starts_with($url, 'http')) {
            return $url;
        }
        
        // Remove the base URL to get username
        $prefixes = [
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
            'http://www.instagram.com/',
        ];
        
        foreach ($prefixes as $p) {
            if (str_starts_with($url, $p)) {
                return substr($url, strlen($p));
            }
        }
        
        return $url; // Return as-is if no match
    }

    /**
     * Format social media link based on platform and username
     */
    private function formatSocialLink(string $platform, string $username): string
    {
        $platformUrls = [
            'facebook' => 'https://facebook.com/',
            'twitter' => 'https://twitter.com/',
            'linkedin' => 'https://linkedin.com/in/',
            'instagram' => 'https://instagram.com/',
        ];
        
        if (isset($platformUrls[$platform])) {
            // Clean the username (remove any @ symbols and trim)
            $cleanUsername = trim($username, '@');
            return $platformUrls[$platform] . $cleanUsername;
        }
        
        return $username;
    }

    public function updateAddress(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            $validated = $request->validate([
                'country' => 'nullable|string|max:100',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'tax_id' => 'nullable|string|max:50',
            ]);
            
            $user->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Address updated successfully',
                'user' => $user->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating address',
                'error' => $e->getMessage()
            ], 422);
        }
    }


    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete avatar if exists
        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    public function getData()
    {
        $user = auth()->user();
        
        return response()->json([
            'user' => $user->only(['name', 'email', 'phone', 'bio', 'country', 'city', 'state', 'postal_code', 'tax_id'])
        ]);
    }
    
}