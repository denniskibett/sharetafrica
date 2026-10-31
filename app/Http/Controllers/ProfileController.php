<?php

namespace App\Http\Controllers;

use App\Support\People;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/**
 * Profile (overview) and Account settings. Pages are shells that read GET /profile/data.
 *
 * Account levels, from what the person has given us (users.kyc, encrypted):
 *   basic    M-Pesa grade: legal name, phone, ID document and date of birth
 *   standard bank grade: adds KRA PIN, address, occupation, income and source of funds, next of kin
 * Business accounts (bank-grade KYB) live in Business settings (CompanySettingsController).
 */
class ProfileController extends Controller
{
    public const BASIC = ['id_type', 'id_number', 'date_of_birth'];

    public const STANDARD = ['kra_pin', 'nationality', 'county', 'town', 'address', 'occupation', 'income_band', 'source_of_funds', 'next_of_kin_name', 'next_of_kin_phone'];

    public function show()
    {
        return view('profile.index');
    }

    public function edit()
    {
        return view('profile.edit');
    }

    public function getUserData(Request $request): JsonResponse
    {
        $user = $request->user();
        $kyc = $this->kyc($user);
        $done = fn (array $keys) => collect($keys)->filter(fn ($k) => filled($kyc[$k] ?? null))->count();
        $basicDone = filled($user->name) && filled($user->phone) && $done(self::BASIC) === count(self::BASIC);

        return response()->json([
            'user' => $user->only(['name', 'first_name', 'last_name', 'email', 'phone', 'bio', 'country', 'city']) + [
                'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'initials' => \App\Support\Table::person($user)['initials'],
                'lane' => People::lane($user->intent),
                'roles' => $user->getRoleNames()->map(fn ($r) => str_replace('_', ' ', $r)),
                'status' => People::status($user->onboarding_status),
                'email_verified' => (bool) $user->email_verified_at,
                'joined' => $user->created_at?->timezone('Africa/Nairobi')->format('j M Y'),
                'company' => $user->company?->name,
                'has_company' => in_array($user->intent, ['merchant', 'business'], true) || $user->company_id,
            ],
            'kyc' => $kyc,
            'level' => [
                'key' => $user->kyc_level ?? 'none',
                'label' => ['none' => 'Not verified', 'basic' => 'Basic · M-Pesa grade', 'standard' => 'Full · bank grade'][$user->kyc_level ?? 'none'] ?? 'Not verified',
                'basic' => ['done' => $done(self::BASIC) + (filled($user->phone) ? 1 : 0), 'of' => count(self::BASIC) + 1, 'complete' => $basicDone],
                'standard' => ['done' => $done(self::STANDARD), 'of' => count(self::STANDARD), 'complete' => $basicDone && $done(self::STANDARD) === count(self::STANDARD)],
                'submitted' => $user->kyc_submitted_at?->timezone('Africa/Nairobi')->format('j M Y'),
            ],
            'wallet' => ['balance' => $user->getWalletBalance()],
        ]);
    }

    /** Personal details and photo. JSON for the settings page; redirects for plain form posts. */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9 ]{9,15}$/', Rule::unique('users')->ignore($user->id)],
            'bio' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], ['phone.regex' => 'Use a phone number like +254 712 345 678.']);

        if ($request->hasFile('avatar')) {
            $user->avatar && Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('images/avatars', 'public');
        } else {
            unset($data['avatar']);
        }

        $user->fill($data);
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
        $user->save();

        return $request->expectsJson()
            ? response()->json(['message' => 'Your details are saved.', 'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null])
            : Redirect::route('profile.show')->with('status', 'profile-updated');
    }

    /** Verification details (M-Pesa grade, then bank grade). Stored encrypted; level set from what is complete. */
    public function updateVerification(Request $request): JsonResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'id_type' => 'required|in:national_id,passport,alien_id,military_id',
            'id_number' => 'required|string|max:30|regex:/^[A-Za-z0-9-]+$/',
            'date_of_birth' => 'required|date|before:-18 years|after:1900-01-01',
            'kra_pin' => ['nullable', 'string', 'regex:/^[AP][0-9]{9}[A-Z]$/i'],
            'nationality' => 'nullable|string|max:60',
            'county' => 'nullable|string|max:60',
            'town' => 'nullable|string|max:60',
            'address' => 'nullable|string|max:190',
            'occupation' => 'nullable|string|max:100',
            'employer' => 'nullable|string|max:120',
            'income_band' => 'nullable|in:under_50k,50k_150k,150k_500k,over_500k',
            'source_of_funds' => 'nullable|in:salary,business,savings,investments,family,other',
            'next_of_kin_name' => 'nullable|string|max:120',
            'next_of_kin_phone' => 'nullable|string|max:20',
            'next_of_kin_relationship' => 'nullable|string|max:40',
            'pep' => 'nullable|boolean',
        ], [
            'date_of_birth.before' => 'You need to be 18 or older to hold a Sharet account.',
            'kra_pin.regex' => 'A KRA PIN looks like A012345678Z.',
        ]);

        $data['kra_pin'] = isset($data['kra_pin']) ? strtoupper($data['kra_pin']) : null;
        $basic = filled($user->phone) && collect(self::BASIC)->every(fn ($k) => filled($data[$k] ?? null));
        $standard = $basic && collect(self::STANDARD)->every(fn ($k) => filled($data[$k] ?? null));

        $user->forceFill([
            'kyc' => encrypt($data),
            'kyc_level' => $standard ? 'standard' : ($basic ? 'basic' : 'none'),
            'kyc_submitted_at' => now(),
        ])->save();

        return response()->json(['message' => $standard
            ? 'Thanks. Your full (bank-grade) details are in for review.'
            : ($basic ? 'Thanks. Basic verification is in for review. Add the rest to unlock bank-grade services.' : 'Saved. Add your phone number in Personal details to finish basic verification.')]);
    }

    public function updatePassword(Request $request): JsonResponse
    {
        $data = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        $request->user()->update(['password' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password changed.']);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        $user = $request->user();
        $user->avatar && Storage::disk('public')->delete($user->avatar);
        $user->update(['avatar' => null]);

        return response()->json(['message' => 'Photo removed.']);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', ['password' => ['required', 'current_password']]);

        $user = $request->user();
        $user->avatar && Storage::disk('public')->delete($user->avatar);

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    protected function kyc($user): array
    {
        try {
            return $user->kyc ? (array) decrypt($user->kyc) : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
