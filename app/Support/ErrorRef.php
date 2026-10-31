<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * One short reference per request, shown on error pages and written to the log context,
 * so a person quoting "Ref K7Q2M9XD" can be matched to the log line.
 */
class ErrorRef
{
    protected static ?string $ref = null;

    public static function get(): string
    {
        return self::$ref ??= Str::upper(Str::random(8));
    }
}
