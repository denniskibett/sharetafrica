<?php

namespace App\Http\Controllers\Api\V1;

use App\Api\Webhooks;
use App\Http\Controllers\Controller;
use App\Models\ApiWebhook;
use App\Support\ApiProblem;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/** Webhook endpoints for the key's account and environment (docs/v2/INTEGRATION.md §8). The secret is shown once. */
class WebhooksController extends Controller
{
    public function index(Request $request)
    {
        $env = $request->attributes->get('api_key')->environment;

        return response()->json(['data' => ApiWebhook::where('user_id', $request->user()->id)->where('environment', $env)->latest()->get()
            ->map(fn (ApiWebhook $h) => $this->present($h))]);
    }

    public function store(Request $request)
    {
        $data = $request->validate(['url' => ['required', 'string', 'max:500'], 'events' => ['required', 'array', 'min:1'], 'events.*' => ['in:'.implode(',', ApiWebhook::EVENTS)]]);
        if (! Webhooks::urlAllowed($data['url'])) {
            return ApiProblem::make(422, 'invalid_webhook_url', 'Use a public HTTPS address', 'Webhook URLs must start with https:// and can\'t point at localhost or a private network.');
        }
        $secret = 'whsec_'.Str::random(40);
        $hook = ApiWebhook::create(['user_id' => $request->user()->id, 'environment' => $request->attributes->get('api_key')->environment,
            'url' => $data['url'], 'events' => array_values(array_unique($data['events'])), 'secret' => $secret, 'enabled' => true]);

        return response()->json($this->present($hook) + ['secret' => $secret, 'note' => 'Keep the secret: it signs every delivery and is not shown again.'], 201);
    }

    public function destroy(Request $request, int $id)
    {
        $hook = ApiWebhook::where('user_id', $request->user()->id)->whereKey($id)->first();
        if (! $hook) {
            return ApiProblem::make(404, 'webhook_not_found', 'No such webhook', 'Check the id.');
        }
        $hook->delete();

        return response()->noContent();
    }

    private function present(ApiWebhook $h): array
    {
        return ['id' => $h->id, 'url' => $h->url, 'events' => $h->events, 'environment' => $h->environment, 'enabled' => $h->enabled];
    }
}
