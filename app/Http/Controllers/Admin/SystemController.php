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
    /** System settings (platform admins). The page is a shell; values and option lists come as JSON. */
    public function index(Request $request)
    {
        if (! $request->wantsJson()) {
            return view('admin.system.index');
        }

        $system = System::settings();
        $file = fn ($path) => $path ? (str_starts_with($path, 'http') ? $path : asset('storage/'.$path)) : null;

        return response()->json([
            'values' => $system->only(['name', 'slogan', 'timezone', 'date_format', 'time_format', 'currency', 'currency_symbol', 'primary_color',
                'secondary_color', 'contact_email', 'contact_phone', 'address', 'meta_description', 'meta_keywords', 'pagination_limit', 'custom_css', 'custom_js']),
            'files' => ['logo' => $file($system->logo), 'logo_dark' => $file($system->logo_dark), 'logo_icon' => $file($system->logo_icon), 'favicon' => $file($system->favicon)],
            'maintenance' => (bool) $system->maintenance_mode,
            'timezones' => \DateTimeZone::listIdentifiers(\DateTimeZone::AFRICA),
            'currencies' => ['KES' => 'Kenyan shilling', 'UGX' => 'Ugandan shilling', 'TZS' => 'Tanzanian shilling', 'RWF' => 'Rwandan franc', 'USD' => 'US dollar', 'EUR' => 'Euro'],
        ]);
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

        return $request->wantsJson()
            ? response()->json(['message' => 'System settings saved.'])
            : back()->with('success', 'System settings updated successfully!');
    }   
    
    public function clearCache()
    {
        Cache::flush();

        
        SystemHelper::clearCache();

        return request()->wantsJson() ? response()->json(['message' => 'Cache cleared.']) : back()->with('success', 'Cache cleared successfully!');
    }

    public function toggleMaintenance()
    {
        $system = System::settings();
        $system->update([
            'maintenance_mode' => !$system->maintenance_mode
        ]);
        
        Cache::forget('system_settings');
        SystemHelper::clearCache();

        return request()->wantsJson()
            ? response()->json(['message' => $system->maintenance_mode ? 'Maintenance mode is on.' : 'Maintenance mode is off.', 'maintenance' => (bool) $system->maintenance_mode])
            : back()->with('success', 'Maintenance mode updated!');
    }
}