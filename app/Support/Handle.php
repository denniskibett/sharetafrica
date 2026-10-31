<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Str;

/** A user's @handle for wallet-to-wallet payments: made once from their name (jane.wanjiru, jane.wanjiru2…), then kept. */
final class Handle
{
    public static function for(User $user): string
    {
        if ($user->handle) {
            return $user->handle;
        }
        $base = Str::limit(trim(preg_replace('/[^a-z0-9]+/', '.', Str::ascii(strtolower($user->name ?: 'sharet'))), '.'), 24, '') ?: 'sharet';
        $handle = $base;
        for ($n = 2; User::where('handle', $handle)->exists(); $n++) {
            $handle = $base.$n;
        }
        $user->forceFill(['handle' => $handle])->save();

        return $handle;
    }

    /** "@Jane.Wanjiru " → the user, or null. */
    public static function find(string $raw): ?User
    {
        $handle = strtolower(ltrim(trim($raw), '@'));

        return $handle === '' ? null : User::where('handle', $handle)->first();
    }
}
