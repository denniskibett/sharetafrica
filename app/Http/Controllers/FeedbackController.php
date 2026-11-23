<?php

namespace App\Http\Controllers;

use App\Support\Funnel;
use Illuminate\Http\Request;

/**
 * "How was that?": a 1–5 score and an optional note, asked once after a person's first payment out (website card on
 * the dashboard; app reads `ask_feedback` from /me). No score = dismissed, and it is not asked again.
 */
class FeedbackController extends Controller
{
    public function __invoke(Request $request)
    {
        $data = $request->validate([
            'score' => ['nullable', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);
        Funnel::feedback($request->user(), $data['score'] ?? null, $data['comment'] ?? null);

        return $request->expectsJson() ? response()->json(['ok' => true]) : back();
    }
}
