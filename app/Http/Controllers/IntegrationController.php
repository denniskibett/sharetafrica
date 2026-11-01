<?php

namespace App\Http\Controllers;

use App\Mail\SharetMail;
use App\Models\Company;
use App\Models\Integration;
use App\Services\Integrations;
use App\Support\DashboardNav;
use App\Support\Outbox;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Provider settings from the dashboard. Admins work on the platform defaults or any company
 * (?company=platform|<id>); merchants and businesses only on their own company.
 * Secret fields are write-only: an empty secret keeps the saved one.
 */
class IntegrationController extends Controller
{
    public function index(Request $request)
    {
        $companyId = $this->companyId($request);
        $rows = Integration::where('company_id', $companyId)->get()->keyBy('provider');
        $isAdmin = $request->user()->hasAnyRole(DashboardNav::ADMIN);

        $providers = collect(config('integrations'))->map(fn ($p, $key) => $this->state($key, $rows->get($key)));
        if ($request->wantsJson()) {
            return response()->json(['providers' => $providers->values()]);
        }

        return view('integrations.index', [
            'scope' => $companyId ? Company::find($companyId) : null,
            'companies' => $isAdmin ? Company::orderBy('name')->get(['id', 'name']) : collect(),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function update(Request $request, string $provider)
    {
        $companyId = $this->companyId($request);
        $definition = $this->definition($provider);
        $row = Integration::firstOrNew(['company_id' => $companyId, 'provider' => $provider]);
        $saved = $row->credentials ?? [];
        $input = (array) $request->input('values', []);

        $values = [];
        $errors = [];
        foreach ($definition['fields'] as $key => $field) {
            $value = is_string($input[$key] ?? null) ? trim($input[$key]) : ($input[$key] ?? null);
            if (! empty($field['secret']) && blank($value)) {
                $value = $saved[$key] ?? null; // keep the stored secret
            }
            if (! empty($field['required']) && blank($value)) {
                $errors["values.$key"] = "{$field['label']} is required.";
            }
            if (($field['type'] ?? '') === 'select' && filled($value) && ! array_key_exists($value, $field['options'])) {
                $errors["values.$key"] = "Choose a valid {$field['label']}.";
            }
            if (($field['type'] ?? '') === 'email' && filled($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors["values.$key"] = "{$field['label']} must be an email address.";
            }
            if (($field['type'] ?? '') === 'url' && filled($value) && ! filter_var($value, FILTER_VALIDATE_URL)) {
                $errors["values.$key"] = "{$field['label']} must be a full web address.";
            }
            $values[$key] = $value === '' ? null : $value;
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $row->fill(['credentials' => $values, 'updated_by' => $request->user()->id]);
        if (! $row->exists) {
            $row->enabled = true;
        }
        $row->save();
        Integrations::exclusive($row);

        return response()->json(['message' => "{$definition['name']} saved.", 'provider' => $this->state($provider, $row), 'siblings' => $this->siblings($request, $provider)]);
    }

    public function toggle(Request $request, string $provider)
    {
        $row = $this->row($request, $provider);
        $row->update(['enabled' => ! $row->enabled, 'updated_by' => $request->user()->id]);
        Integrations::exclusive($row);
        $others = config("integrations.$provider.group") && $row->enabled ? ' The other M-Pesa connection is now off.' : '';

        return response()->json(['message' => ($row->enabled ? 'Turned on.' : 'Turned off.').$others, 'provider' => $this->state($provider, $row), 'siblings' => $this->siblings($request, $provider)]);
    }

    /** Fresh state of the other providers in the same group (so the page can flip their switches). */
    protected function siblings(Request $request, string $provider): array
    {
        $group = config("integrations.$provider.group");
        if (! $group) {
            return [];
        }
        $companyId = $this->companyId($request);

        return collect(config('integrations'))->filter(fn ($p, $k) => ($p['group'] ?? null) === $group && $k !== $provider)
            ->map(fn ($p, $k) => $this->state($k, Integration::where('company_id', $companyId)->where('provider', $k)->first()))->values()->all();
    }

    public function test(Request $request, string $provider)
    {
        $row = $this->row($request, $provider);
        [$ok, $message] = Integrations::test($row);

        return response()->json(['ok' => $ok, 'message' => $message, 'provider' => $this->state($provider, $row->fresh())], $ok ? 200 : 422);
    }

    /** Email (SMTP) only: send a real branded email through these settings to the signed-in person. */
    public function sendTestEmail(Request $request)
    {
        $row = $this->row($request, 'smtp');
        Integrations::useSmtp($row->credentials ?? []);

        $ok = Outbox::now($request->user()->email, SharetMail::test($request->user(), $row->credentials['host'] ?? 'SMTP'));
        $row->forceFill(['last_tested_at' => now(), 'last_test_ok' => $ok,
            'last_test_message' => $ok ? 'Test email sent to '.$request->user()->email.'.' : 'The mail server did not accept the test email. Check the log for details.'])->save();

        return response()->json([
            'message' => $row->last_test_message,
            'provider' => $this->state('smtp', $row->fresh()),
        ], $ok ? 200 : 422);
    }

    public function destroy(Request $request, string $provider)
    {
        $this->row($request, $provider)->delete();

        return response()->json(['message' => 'Removed. Saved credentials were deleted.', 'provider' => $this->state($provider, null)]);
    }

    /** What the page needs about one provider; secrets only as "saved". */
    protected function state(string $key, ?Integration $row): array
    {
        $d = $this->definition($key);

        return [
            'key' => $key,
            'name' => $d['name'],
            'category' => $d['category'],
            'description' => $d['description'],
            'color' => $d['color'],
            'logo' => isset($d['logo']) ? asset($d['logo']) : null,
            'group' => $d['group'] ?? null,
            'environment' => $d['environment'] ?? null,
            'fields' => collect($d['fields'])->map(fn ($f, $k) => $f + ['key' => $k, 'type' => $f['type'] ?? 'text'])->values(),
            'connected' => (bool) $row?->exists,
            'enabled' => (bool) $row?->enabled,
            'values' => $row?->publicValues() ?? [],
            'saved_secrets' => $row?->savedSecrets() ?? [],
            'last_tested_at' => $row?->last_tested_at?->diffForHumans(),
            'last_test_ok' => $row?->last_test_ok,
            'last_test_message' => $row?->last_test_message,
            'updated_at' => $row?->updated_at?->timezone('Africa/Nairobi')->format('j M Y, H:i'),
        ];
    }

    protected function definition(string $provider): array
    {
        return config("integrations.$provider") ?? abort(404);
    }

    protected function row(Request $request, string $provider): Integration
    {
        $this->definition($provider);

        return Integration::where('company_id', $this->companyId($request))->where('provider', $provider)->firstOrFail();
    }

    protected function companyId(Request $request): ?int
    {
        $user = $request->user();

        if ($user->hasAnyRole(DashboardNav::ADMIN)) {
            $company = $request->input('company');
            if (blank($company) || $company === 'platform') {
                return null;
            }

            return Company::findOrFail((int) $company)->id;
        }

        abort_unless($user->company_id, 403, 'Your account is not linked to a company yet.');

        return $user->company_id;
    }
}
