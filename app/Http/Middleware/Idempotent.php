<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Money requests from the app must carry "Idempotency-Key" (8–80 chars, the app makes one per payment and keeps it
 * until it has an answer). The first request runs; any retry with the same key gets the same answer back without
 * running again. The same key with a different body is refused (409), and a retry while the first is still running
 * is told to wait (409, retry_after). Refusals (4xx/5xx) are not remembered: nothing moved, so the app may retry.
 * The PIN is left out of the comparison, so correcting a wrong PIN on the same key works.
 */
class Idempotent
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = (string) $request->header('Idempotency-Key');
        if (strlen($key) < 8 || strlen($key) > 80) {
            return response()->json(['error' => 'idempotency_key_required', 'message' => 'Send an Idempotency-Key header (8–80 characters) with every payment.'], 428);
        }
        $user = $request->user();
        $route = (string) optional($request->route())->getName();
        $body = $request->except(['pin']);
        ksort($body);
        $hash = hash('sha256', $route.'|'.json_encode($body));

        try {
            $id = DB::table('mobile_requests')->insertGetId(['user_id' => $user->id, 'idem_key' => $key, 'route' => $route, 'body_hash' => $hash, 'created_at' => now(), 'updated_at' => now()]);
        } catch (UniqueConstraintViolationException) {
            $seen = DB::table('mobile_requests')->where('user_id', $user->id)->where('idem_key', $key)->first();
            if ($seen->body_hash !== $hash) {
                return response()->json(['error' => 'idempotency_key_reused', 'message' => 'That Idempotency-Key was used for a different request.'], 409);
            }
            if ($seen->status === null) {
                return response()->json(['error' => 'in_progress', 'message' => 'Still working on it. Check again in a moment.', 'retry_after' => 2], 409);
            }

            return (new JsonResponse(json_decode($seen->response, true), $seen->status))->header('Idempotent-Replay', 'true');
        }

        $response = $next($request);
        $status = $response->getStatusCode();
        if ($status >= 400) {
            // Refused (wrong PIN, not enough money, paused, server error): nothing moved, so forget the key and let the
            // app fix the problem and try again with it. Only results that happened are remembered and replayed.
            DB::table('mobile_requests')->where('id', $id)->delete();
        } else {
            DB::table('mobile_requests')->where('id', $id)->update(['status' => $status, 'response' => $response->getContent(), 'updated_at' => now()]);
        }

        return $response;
    }
}
