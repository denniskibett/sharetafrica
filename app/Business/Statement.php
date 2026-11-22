<?php

namespace App\Business;

use App\Ledger\Money;
use App\Models\RampOrder;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/** A company wallet's statement for a month (Nairobi time): every payment in and out with both references, and the fees. */
final class Statement
{
    /** @return array{month: string, in: string, out: string, fees: string, lines: array} */
    public static function month(User $wallet, ?string $month = null): array
    {
        $start = self::start($month);
        $rows = self::rows($wallet, $start);
        $isIn = fn (RampOrder $o) => $o->recipient_user_id === $wallet->id || ($o->user_id === $wallet->id && $o->destination_type === 'wallet');

        return [
            'month' => $start->format('Y-m'), 'label' => $start->format('F Y'),
            'in' => Money::format((int) $rows->filter($isIn)->sum('amount')),
            'out' => Money::format((int) $rows->reject($isIn)->sum('total')),
            'fees' => Money::format((int) $rows->reject($isIn)->sum(fn ($o) => $o->fee + $o->excise + $o->rounding)),
            'lines' => $rows->map(fn (RampOrder $o) => [
                'at' => $o->created_at?->toIso8601String(), 'direction' => $isIn($o) ? 'in' : 'out',
                'description' => $isIn($o) ? 'From '.($o->user_id === $wallet->id ? ($o->payer_phone ?: 'M-PESA') : ($o->user?->name ?? 'Sharet')) : 'To '.$o->recipient(),
                'amount' => Money::format($isIn($o) ? $o->amount : $o->total), 'fee' => Money::format($isIn($o) ? 0 : $o->fee + $o->excise + $o->rounding),
                'note' => $o->note, 'status' => $o->status, 'sharet_reference' => $o->sharetReference(), 'network_reference' => $o->reference(),
            ])->values()->all(),
        ];
    }

    public static function csv(User $wallet, ?string $month = null): string
    {
        $s = self::month($wallet, $month);
        $out = fopen('php://temp', 'r+');
        fputcsv($out, ['date', 'direction', 'description', 'amount', 'fee', 'note', 'status', 'sharet_reference', 'network_reference']);
        foreach ($s['lines'] as $l) {
            fputcsv($out, [Carbon::parse($l['at'])->timezone('Africa/Nairobi')->format('Y-m-d H:i'), $l['direction'], $l['description'],
                str_replace(',', '', $l['amount']), str_replace(',', '', $l['fee']), $l['note'], $l['status'], $l['sharet_reference'], $l['network_reference']]);
        }
        rewind($out);

        return stream_get_contents($out);
    }

    private static function rows(User $wallet, Carbon $start): Collection
    {
        return RampOrder::where(fn ($q) => $q->where('user_id', $wallet->id)->orWhere('recipient_user_id', $wallet->id))
            ->whereNotIn('status', ['quoted', 'expired', 'reversed', 'rejected'])
            ->where('created_at', '>=', $start->copy()->utc())->where('created_at', '<', $start->copy()->addMonth()->utc())
            ->with('user')->orderBy('id')->get();
    }

    private static function start(?string $month): Carbon
    {
        $tz = 'Africa/Nairobi';
        if ($month && preg_match('/^\d{4}-\d{2}$/', $month)) {
            return Carbon::createFromFormat('Y-m-d H:i:s', $month.'-01 00:00:00', $tz);
        }

        return now($tz)->startOfMonth();
    }
}
