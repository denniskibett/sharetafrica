<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class OnboardingController extends Controller
{
    /**
     * Entry point. If the user is still on the waiting list, show them a
     * "we'll be in touch" screen. If invited, push them to pick a lane.
     */
    public function welcome()
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->onboarding_status === 'waiting_list') {
            return view('onboarding.welcome', [
                'stage' => 'waiting',
                'user'  => $user,
            ]);
        }

        if ($user->onboarding_status === 'invited') {
            return redirect()->route('onboarding.choose_lane');
        }

        if (in_array($user->onboarding_status, ['in_progress', 'active'])) {
            // Already progressing — send them to their dashboard
            return redirect()->route('dashboard');
        }

        return view('onboarding.welcome', [
            'stage' => 'waiting',
            'user'  => $user,
        ]);
    }

    /**
     * Show the four-lane chooser.
     */
    public function chooseLane()
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->onboarding_status !== 'invited') {
            return redirect()->route('onboarding.welcome');
        }

        return view('onboarding.choose-lane', [
            'user' => $user,
            'lanes' => [
                'individual' => [
                    'title'   => 'Individual',
                    'tagline' => 'Send money across East Africa. The recipient never needs Sharet.',
                    'icon'    => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
                ],
                'merchant' => [
                    'title'   => 'Merchant',
                    'tagline' => 'Accept from any rail. Settle anywhere. Onboard in a day.',
                    'icon'    => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
                ],
                'business' => [
                    'title'   => 'Business',
                    'tagline' => 'Pay abroad. Get paid from abroad. Finance the gap. Settle local.',
                    'icon'    => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                ],
                'techie' => [
                    'title'   => 'Developer',
                    'tagline' => 'Build on the Sharet ledger. API, sandbox, webhooks, licensing.',
                    'icon'    => 'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',
                ],
            ],
        ]);
    }

    /**
     * Persist the chosen lane and redirect to the corresponding form.
     */
    public function storeLane(Request $request)
    {
        $data = $request->validate([
            'lane' => 'required|in:individual,merchant,business,techie',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'intent'            => $data['lane'],
            'onboarding_status' => 'in_progress',
        ]);

        return redirect()->route('onboarding.' . $data['lane']);
    }

    /*
    |--------------------------------------------------------------------------
    | Lane-specific forms
    |--------------------------------------------------------------------------
    */

    public function merchant()
    {
        return view('onboarding.merchant', ['user' => auth()->user()]);
    }

    public function storeMerchant(Request $request)
    {
        $data = $request->validate([
            'business_name' => 'required|string|max:190',
            'business_type' => 'required|string|max:60',
            'country'       => 'required|string|size:2',
            'city'          => 'nullable|string|max:120',
            'rails'         => 'nullable|string|max:255',
            'monthly_volume'=> 'nullable|string|max:60',
            'tax_id'        => 'nullable|string|max:60',
        ]);

        /** @var User $user */
        $user = auth()->user();

        // Create or link Company
        $company = Company::firstOrCreate(
            ['slug' => Str::slug($data['business_name'])],
            [
                'name'    => $data['business_name'],
                'type'    => 'merchant',
                'country' => $data['country'],
                'city'    => $data['city'] ?? null,
                'status'  => true,
            ]
        );

        $user->update([
            'company_id'        => $company->id,
            'tax_id'            => $data['tax_id'] ?? $user->tax_id,
            'country'           => $data['country'],
            'city'              => $data['city'] ?? $user->city,
            'onboarding_status' => 'active',
            'onboarded_at'      => now(),
        ]);

        $user->syncRoles(['merchant']);

        return redirect()->route('dashboard')
            ->with('success', 'Merchant onboarding complete. Welcome to Sharet.');
    }

    public function business()
    {
        return view('onboarding.business', ['user' => auth()->user()]);
    }

    public function storeBusiness(Request $request)
    {
        $data = $request->validate([
            'business_name'  => 'required|string|max:190',
            'registration_no'=> 'nullable|string|max:60',
            'tax_id'         => 'nullable|string|max:60',
            'country'        => 'required|string|size:2',
            'city'           => 'nullable|string|max:120',
            'corridors'      => 'nullable|array',
            'corridors.*'    => 'string|max:20',
            'monthly_volume' => 'nullable|string|max:60',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $company = Company::firstOrCreate(
            ['slug' => Str::slug($data['business_name'])],
            [
                'name'                => $data['business_name'],
                'type'                => 'business',
                'registration_number' => $data['registration_no'] ?? null,
                'tax_id'              => $data['tax_id'] ?? null,
                'country'             => $data['country'],
                'city'                => $data['city'] ?? null,
                'status'              => true,
            ]
        );

        $user->update([
            'company_id'        => $company->id,
            'country'           => $data['country'],
            'city'              => $data['city'] ?? $user->city,
            'onboarding_status' => 'active',
            'onboarded_at'      => now(),
        ]);

        $user->syncRoles(['business']);

        return redirect()->route('dashboard')
            ->with('success', 'Business onboarding complete. Welcome to Sharet.');
    }

    public function developer()
    {
        return view('onboarding.developer', ['user' => auth()->user()]);
    }

    public function storeDeveloper(Request $request)
    {
        $data = $request->validate([
            'company'  => 'nullable|string|max:190',
            'use_case' => 'required|string|max:2000',
            'interest' => 'required|in:sandbox,production_keys,white_label,licensing',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $user->developerApplications()->create([
            'interest'   => $data['interest'],
            'brief'      => $data['use_case'],
            'status'     => 'new',
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 500),
        ]);

        $user->update([
            'onboarding_status' => 'active',
            'onboarded_at'      => now(),
        ]);

        $user->syncRoles(['techie']);

        return redirect()->route('dashboard')
            ->with('success', 'Developer onboarding complete. Your sandbox request is under review.');
    }

    public function individual()
    {
        return view('onboarding.individual', ['user' => auth()->user()]);
    }

    public function storeIndividual(Request $request)
    {
        $data = $request->validate([
            'country' => 'required|string|size:2',
            'city'    => 'nullable|string|max:120',
            'use_case'=> 'nullable|string|max:1000',
        ]);

        /** @var User $user */
        $user = auth()->user();

        $user->update([
            'country'           => $data['country'],
            'city'              => $data['city'] ?? $user->city,
            'onboarding_status' => 'active',
            'onboarded_at'      => now(),
        ]);

        $user->syncRoles(['individual']);
        $user->getOrCreateWallet();

        return redirect()->route('dashboard')
            ->with('success', 'Your Sharet wallet is ready.');
    }
}