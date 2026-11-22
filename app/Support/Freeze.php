<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Validation\ValidationException;

/** Freeze my account (Me): stops every payment out of the wallet at once; money can still come in. Unfreeze needs the PIN. */
class Freeze
{
    public static function assertOpen(User $user): void
    {
        if ($user->frozen_at) {
            throw ValidationException::withMessages(['amount' => 'Your account is frozen, so nothing can leave your wallet. Unfreeze it in Me with your PIN.']);
        }
    }
}
