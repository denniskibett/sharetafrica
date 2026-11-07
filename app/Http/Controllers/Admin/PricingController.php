<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Staff › Pricing: the business tariff tables (docs/v2/INTEGRATION.md §7). Shown and edited in shillings and percent;
 * stored in cents and basis points. Saving never edits a version: it adds a new one with an effective date, so every
 * past quote stays explainable. Sharet's own companies are marked here (priced one volume tier up).
 */
class PricingController extends Controller
{
    public function index()
    {
        return view('admin.pricing.index', [
            'current' => Tariff::current(),
            'versions' => Tariff::orderByDesc('version')->limit(20)->get(),
            'companies' => Company::whereNotNull('verified_at')->orderBy('name')->get(['id', 'name', 'sharet_id', 'is_group_company']),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'effective_from' => ['required', 'date'],
            'excise_percent' => ['required', 'numeric', 'min:0', 'max:50'],
            'notes' => ['nullable', 'string', 'max:500'],
            'bands' => ['required', 'array'],
            'tiers' => ['required', 'array'],
        ]);

        $bands = [];
        foreach (array_diff(array_keys(Tariff::MOVEMENTS), ['sharet']) as $movement) {
            $rows = collect($data['bands'][$movement] ?? [])->filter(fn ($r) => filled($r['fixed'] ?? null) || filled($r['percent'] ?? null) || filled($r['up_to'] ?? null))
                ->map(fn ($r) => [
                    'up_to' => filled($r['up_to'] ?? null) ? $this->cents($r['up_to'], "bands.$movement") : null,
                    'fixed' => $this->cents($r['fixed'] ?? 0, "bands.$movement"),
                    'percent_bp' => (int) round(((float) ($r['percent'] ?? 0)) * 100),
                    'cap' => filled($r['cap'] ?? null) ? $this->cents($r['cap'], "bands.$movement") : null,
                ])->sortBy(fn ($r) => $r['up_to'] ?? PHP_INT_MAX)->values()->all();
            if (! $rows || end($rows)['up_to'] !== null) {
                throw ValidationException::withMessages(["bands.$movement" => Tariff::MOVEMENTS[$movement].': the last band must have no upper limit ("and above").']);
            }
            $bands[$movement] = $rows;
        }
        $bands['sharet'] = [['up_to' => null, 'fixed' => 0, 'percent_bp' => 0, 'cap' => null]];   // always free (owner's rule)

        $tiers = collect($data['tiers'])->filter(fn ($t) => filled($t['from'] ?? null))
            ->map(fn ($t) => ['from' => $this->cents($t['from'], 'tiers'), 'discount_bp' => (int) round(((float) ($t['discount'] ?? 0)) * 100)])
            ->sortBy('from')->values()->all();
        if (! $tiers || $tiers[0]['from'] !== 0) {
            throw ValidationException::withMessages(['tiers' => 'The first volume tier must start at KES 0.']);
        }

        $t = Tariff::create([
            'version' => (int) Tariff::max('version') + 1,
            'effective_from' => $data['effective_from'],
            'bands' => $bands, 'volume_tiers' => $tiers,
            'excise_bp' => (int) round($data['excise_percent'] * 100),
            'notes' => $data['notes'] ?? null,
            'created_by' => $request->user()->id,
        ]);

        return redirect()->route('admin.pricing.index')->with('status', "Version {$t->version} saved, in force from ".$t->effective_from->timezone('Africa/Nairobi')->format('j M Y, H:i').'.');
    }

    public function group(Request $request, Company $company)
    {
        $company->forceFill(['is_group_company' => ! $company->is_group_company])->save();

        return back()->with('status', $company->name.($company->is_group_company ? ' is now priced as a Sharet company (one tier up).' : ' is priced on its own volume again.'));
    }

    private function cents(mixed $kes, string $field): int
    {
        $v = str_replace([',', ' '], '', (string) $kes);
        if (! is_numeric($v) || (float) $v < 0) {
            throw ValidationException::withMessages([$field => 'Use amounts in shillings, like 1500 or 0.50.']);
        }

        return (int) round(((float) $v) * 100);
    }
}
