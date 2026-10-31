<?php

namespace App\Integrations\Simulator;

use App\Integrations\Contracts\PaymentRail;
use App\Integrations\RailResult;
use App\Models\RailRequest;
use Illuminate\Support\Str;

/**
 * A stand-in PSP for building and testing before Cashia and Daraja are integrated. It answers like a real one:
 * pending at first, then (after config rails.simulator.delay seconds) succeeded, failed or, for a while, unknown.
 * Outcome by the last four digits of the number: 0000 fails, 9999 stays unknown for the first status checks.
 * It moves no money and needs no credentials. Never enable it in production.
 */
class SimulatorRail implements PaymentRail
{
    public function name(): string
    {
        return 'simulator';
    }

    public function collect(RailRequest $request): RailResult
    {
        return RailResult::pending('SIM-'.strtoupper(Str::random(10)));
    }

    public function payout(RailRequest $request): RailResult
    {
        return RailResult::pending('SIM-'.strtoupper(Str::random(10)));
    }

    public function status(RailRequest $request): RailResult
    {
        $cfg = config('rails.simulator');
        if ($request->created_at->diffInSeconds(now()) < $cfg['delay']) {
            return RailResult::pending($request->provider_reference);
        }
        $digits = preg_replace('/\D/', '', $request->party);

        if (str_ends_with($digits, $cfg['fail_suffix'])) {
            return RailResult::failed($request->kind === 'collect' ? 'The customer cancelled the M-PESA prompt.' : 'The recipient account could not be credited.', $request->provider_reference);
        }
        if (str_ends_with($digits, $cfg['slow_suffix']) && $request->checks < $cfg['slow_checks']) {
            return RailResult::unknown('No answer from the provider yet.');
        }

        return RailResult::succeeded('S'.strtoupper(Str::random(9)), $request->provider_reference);
    }
}
