<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\System;
use App\Helpers\SystemHelper;
use App\Helpers\CountryHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    public function index()
    {
    $system = System::settings();
    $timezones = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
    
    // Get currencies from CountryHelper
    try {
        $currencies = CountryHelper::currencyOptions(false);
    } catch (\Exception $e) {
        // Fallback currencies
        $currencies = [
            'KES' => 'Kenyan Shilling (KSh)',
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'JPY' => 'Japanese Yen (¥)',
            'CAD' => 'Canadian Dollar (C$)',
        ];
    }

        // Get countries for location - use countryOptions() method
        try {
            $countries = CountryHelper::countryOptions(false);
        } catch (\Exception $e) {
            $countries = [
                'KE' => 'Kenya',
                'US' => 'United States',
                'GB' => 'United Kingdom',
                'CA' => 'Canada',
            ];
        }

        // Get all social media platforms from system settings
        $socialPlatforms = [
            'facebook' => ['color' => '#1877F2', 'icon' => 'ri-facebook-fill'],
            'twitter' => ['color' => '#1DA1F2', 'icon' => 'ri-twitter-fill'],
            'instagram' => ['color' => '#E4405F', 'icon' => 'ri-instagram-fill'],
            'linkedin' => ['color' => '#0A66C2', 'icon' => 'ri-linkedin-fill'],
            'youtube' => ['color' => '#FF0000', 'icon' => 'ri-youtube-fill'],
            'whatsapp' => ['color' => '#25D366', 'icon' => 'ri-whatsapp-fill'],
            'tiktok' => ['color' => '#000000', 'icon' => 'ri-tiktok-fill'],
            'telegram' => ['color' => '#26A5E4', 'icon' => 'ri-telegram-fill'],
            'github' => ['color' => '#181717', 'icon' => 'ri-github-fill'],
            'discord' => ['color' => '#5865F2', 'icon' => 'ri-discord-fill'],
            'slack' => ['color' => '#4A154B', 'icon' => 'ri-slack-fill'],
            'reddit' => ['color' => '#FF4500', 'icon' => 'ri-reddit-fill'],
            'pinterest' => ['color' => '#BD081C', 'icon' => 'ri-pinterest-fill'],
            'snapchat' => ['color' => '#FFFC00', 'icon' => 'ri-snapchat-fill'],
            'skype' => ['color' => '#00AFF0', 'icon' => 'ri-skype-fill'],
        ];

        return view('admin.system.simple', compact(
            'system', 
            'timezones', 
            'currencies',
            'countries',
            'socialPlatforms'
        ));
    }


    public function update(Request $request)
    {
        $system = System::settings();

        $request->validate([
            'name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'currency' => 'required|string|max:3',
            'currency_symbol' => 'required|string|max:10',
            'primary_color' => 'required|string|max:7',
            'secondary_color' => 'required|string|max:7',
            'pagination_limit' => 'required|integer|min:5|max:100',
            // Allow both image and SVG files
            'logo' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'logo_dark' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:2048',
            'logo_icon' => 'nullable|mimes:jpg,jpeg,png,gif,svg,webp|max:1024',
            'favicon' => 'nullable|mimes:jpg,jpeg,png,gif,svg,ico,webp|max:1024',
        ]);

        // Collect all normal inputs
        $data = $request->only([
            'name', 'slogan', 'timezone', 'date_format', 'time_format',
            'currency', 'currency_symbol', 'primary_color', 'secondary_color',
            'contact_email', 'contact_phone', 'address', 'meta_description',
            'meta_keywords', 'pagination_limit', 'custom_css', 'custom_js'
        ]);

        // Handle location data
        if ($request->has('location_country') || $request->has('location_city')) {
            $location = [
                'country' => $request->input('location_country', ''),
                'city' => $request->input('location_city', ''),
                'name' => $request->input('location_name', ''),
                'latitude' => $request->input('location_latitude', ''),
                'longitude' => $request->input('location_longitude', ''),
            ];
            $data['location'] = json_encode($location);
        }

        // Handle image uploads - CORRECTED PATH
        $imageFields = [
            'logo' => 'images/logo',
            'logo_dark' => 'images/logo',
            'logo_icon' => 'images/logo',
            'favicon' => 'images',
        ];

        foreach ($imageFields as $field => $folder) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                
                // Get original extension
                $extension = $file->getClientOriginalExtension();
                
                // Generate unique filename
                $filename = $field .'.' . $extension;
                
                // Define storage path
                $path = "public/{$folder}";
                
                // Ensure directory exists
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path, 0755, true);
                }
                
                // Delete old file if exists
                if ($system->$field && Storage::exists('public/' . $system->$field)) {
                    Storage::delete('public/' . $system->$field);
                }
                
                // Store new file
                $storedPath = $file->storeAs($folder, $filename, 'public');
                
                // Store relative path in database (images/logo/filename.ext)
                $data[$field] = $storedPath;
            }
        }
        $system->update($data);
        Cache::forget('system_settings');
        SystemHelper::clearCache();
        return back()->with('success', 'System settings updated successfully!');
    }   
    
    public function clearCache()
    {
        Cache::flush();

        
        SystemHelper::clearCache();
        return back()->with('success', 'Cache cleared successfully!');
    }

    public function toggleMaintenance()
    {
        $system = System::settings();
        $system->update([
            'maintenance_mode' => !$system->maintenance_mode
        ]);
        
        Cache::forget('system_settings');
        SystemHelper::clearCache();
        return back()->with('success', 'Maintenance mode updated!');
    }
}