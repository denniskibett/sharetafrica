<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Identity;
use App\Support\Kyc;
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
 * Account levels (App\Support\Kyc): basic = name + M-Pesa number; standard (bank grade) = full details,
 * clear ID photos, three selfies and a signature. Business accounts live in Business settings.
 */
class ProfileController extends Controller
{
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
        $kyc = Kyc::data($user);
        unset($kyc['documents'], $kyc['audit']);
        $progress = Kyc::progress($user);
        $level = Kyc::refreshLevel($user);

        return response()->json([
            'user' => $user->only(['name', 'first_name', 'last_name', 'email', 'phone', 'bio', 'country', 'city']) + [
                'avatar' => $user->avatar ? asset('storage/'.$user->avatar) : null,
                'pin_set' => \App\Support\TransactionPin::isSet($user),
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
            'level' => $progress + [
                'key' => $level,
                'label' => ['none' => 'Not verified', 'basic' => 'Verified', 'standard' => 'Bank grade'][$level],
                'submitted' => $user->kyc_submitted_at?->timezone('Africa/Nairobi')->format('j M Y'),
            ],
            'documents' => Kyc::documents($user, 'profile.document'),
            'wallet' => ['balance' => $user->getWalletBalance()],
        ]);
    }

    /** Personal details and photo. JSON for the settings page; redirects for plain form posts. */
    public function update(Request $request): RedirectResponse|JsonResponse
    {
        $user = $request->user();
        Identity::clean($request);
        $data = $request->validate([
            ...Identity::rules(phoneRequired: false, ignoreId: $user->id),
            'bio' => 'nullable|string|max:500',
            'country' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ], Identity::MESSAGES);

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
        Kyc::refreshLevel($user);

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
        $data['pep'] = (bool) ($data['pep'] ?? false);
        // Merge: keep the documents, what the ID photo said and the footprint log
        Kyc::save($user, $data + Kyc::data($user));
        \App\Support\Footprint::record($user, $request, 'kyc_details');
        $p = Kyc::progress($user->fresh())['standard'];

        return response()->json(['message' => $p['complete']
            ? 'Thanks. Your bank-grade details are in for review.'
            : "Details saved. {$p['documents'][0]} of {$p['documents'][1]} photos are in: add the rest below to finish bank-grade verification."]);
    }

    /** One verification photo: an uploaded image (ID) or a data: URL from the camera / signature pad. */
    public function uploadDocument(Request $request, string $type): JsonResponse
    {
        abort_unless(isset(Kyc::DOCUMENTS[$type]), 404);
        $kind = Kyc::DOCUMENTS[$type][2];

        if ($request->hasFile('file')) {
            $request->validate(['file' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240',
                $kind === 'signature' ? 'dimensions:min_width=300,min_height=100' : 'dimensions:min_width=800,min_height=600']],
                ['file.dimensions' => $kind === 'signature' ? 'That signature image is too small.' : 'That photo is too small to read. Use the camera at full resolution (at least 800 × 600).']);
            Kyc::storeDocument($request->user(), $type, $request->file('file'));
        } else {
            $data = $request->validate(['image' => ['required', 'string', 'max:14000000', 'regex:#^data:image/(jpeg|png);base64,[A-Za-z0-9+/=]+$#']])['image'];
            $size = @getimagesizefromstring(base64_decode(explode(',', $data, 2)[1]));
            $min = $kind === 'signature' ? [300, 100] : [640, 480];
            if (! $size || $size[0] < $min[0] || $size[1] < $min[1]) {
                return response()->json(['message' => 'That image is too small or unreadable. Try again.', 'errors' => ['image' => ['Image too small.']]], 422);
            }
            Kyc::storeDocument($request->user(), $type, $data);
        }

        \App\Support\Footprint::record($request->user()->fresh(), $request, 'kyc_'.$type);
        $user = $request->user()->fresh();
        $kyc = Kyc::data($user);
        $read = array_intersect_key($kyc['id_read'][$type] ?? [], array_flip(['full_name', 'id_number', 'date_of_birth', 'district', 'location', 'sub_location', 'district_of_birth']));
        $verified = Kyc::progress($user)['basic']['complete'];

        return response()->json([
            'message' => $verified ? 'You are verified. You can start sending and receiving.'
                : (Kyc::progress($user)['basic']['submitted'] && ! str_starts_with($type, 'selfie') ? 'Thanks. We are checking your ID; this usually takes less than a day.' : Kyc::DOCUMENTS[$type][0].' saved.'.($read ? ' We read your details from it.' : '')),
            'read' => $read, 'verified' => $verified,
        ]);
    }

    /** Streams a verification photo to its owner or to staff who manage people. */
    public function document(Request $request, User $user, string $type)
    {
        abort_unless($request->user()->is($user) || $request->user()->can('admin.users'), 403);
        $path = Kyc::data($user)['documents'][$type]['path'] ?? abort(404);

        return response()->file(Storage::disk('local')->path($path), ['Cache-Control' => 'private, max-age=300', 'X-Robots-Tag' => 'noindex']);
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
}
