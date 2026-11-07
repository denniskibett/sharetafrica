<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

/** Errors for the public API as application/problem+json (RFC 7807), with a stable type and a plain-English detail. */
final class ApiProblem
{
    public static function make(int $status, string $type, string $title, string $detail, array $extra = []): JsonResponse
    {
        return response()->json(['type' => 'https://sharet.africa/docs/errors#'.$type, 'title' => $title, 'status' => $status, 'detail' => $detail] + $extra, $status)
            ->header('Content-Type', 'application/problem+json');
    }
}
