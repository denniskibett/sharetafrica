<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/**
 * Freeze my account (Me): stops every payment out of the wallet at once; money can still come in.
 *
 * Unfreezing needs the PIN and a fresh selfie, so someone who has the phone and the PIN still can't undo it. Sharet has
 * no automatic face matching, so the selfie waits for a person in Administration › Verifications (Unfreeze tab), who
 * compares it with the verification selfie and approves or rejects. The account stays frozen until then.
 */
class Freeze
{
    public static function assertOpen(User $user): void
    {
        if ($user->frozen_at) {
            throw ValidationException::withMessages(['amount' => 'Your account is frozen, so nothing can leave your wallet. Unfreeze it in Me with your PIN and a selfie.']);
        }
    }

    /** Freezing (again) cancels any unfreeze request still waiting: an older selfie must never undo a newer freeze. */
    public static function freeze(User $user): void
    {
        $user->forceFill(['frozen_at' => now(), 'unfreeze_requested_at' => null])->save();
    }

    /** The person asks to unfreeze: keep the selfie (private disk, with their KYC documents) and queue it for staff. */
    public static function requestUnfreeze(User $user, UploadedFile|string $selfie): void
    {
        $dir = "kyc/{$user->id}";
        if ($selfie instanceof UploadedFile) {
            $path = $selfie->storeAs($dir, 'unfreeze_selfie-'.now()->format('YmdHis').'.'.$selfie->extension(), 'local');
        } else {
            [$meta, $b64] = explode(',', $selfie, 2) + [1 => ''];
            $bytes = base64_decode($b64, true);
            if (! $bytes) {
                throw ValidationException::withMessages(['selfie' => 'That selfie did not come through. Take it again.']);
            }
            $path = "$dir/unfreeze_selfie-".now()->format('YmdHis').'.'.(str_contains($meta, 'png') ? 'png' : 'jpg');
            Storage::disk('local')->put($path, $bytes);
        }
        $kyc = Kyc::data($user);
        $old = $kyc['documents']['unfreeze_selfie']['path'] ?? null;
        $old && $old !== $path && Storage::disk('local')->delete($old);
        $kyc['documents']['unfreeze_selfie'] = ['path' => $path, 'uploaded_at' => now()->toIso8601String()];
        unset($kyc['unfreeze']);   // a new request clears the last rejection
        $user->forceFill(['kyc' => encrypt($kyc), 'unfreeze_requested_at' => now()])->save();
    }

    /** Staff decision on a waiting request. Approve unfreezes; reject keeps it frozen and tells the person why. */
    public static function decide(User $user, User $staff, string $decision, ?string $reason): void
    {
        $kyc = Kyc::data($user);
        $kyc['unfreeze'] = ['status' => $decision === 'approve' ? 'approved' : 'rejected', 'reason' => $reason,
            'staff_id' => $staff->id, 'at' => now()->toIso8601String()];
        $kyc['unfreeze_history'][] = $kyc['unfreeze'];
        $user->forceFill(['kyc' => encrypt($kyc), 'unfreeze_requested_at' => null]
            + ($decision === 'approve' ? ['frozen_at' => null] : []))->save();
    }

    /** For the app: null, or where the person's unfreeze stands. */
    public static function state(User $user): ?array
    {
        if (! $user->frozen_at) {
            return null;
        }
        if ($user->unfreeze_requested_at) {
            return ['status' => 'pending', 'text' => 'We are checking your selfie against your verification selfie. Your account unfreezes when a person at Sharet approves it, usually within an hour.'];
        }
        $last = Kyc::data($user)['unfreeze'] ?? null;

        return ($last['status'] ?? null) === 'rejected'
            ? ['status' => 'rejected', 'text' => 'We could not unfreeze your account: '.($last['reason'] ?: 'the selfie did not match. Take a new one in good light.')]
            : null;
    }
}
