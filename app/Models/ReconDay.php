<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/** One rail's reconciliation for one day: open → prepared (maker) → signed_off (a different checker). */
class ReconDay extends Model
{
    protected $guarded = ['id'];

    protected $casts = ['summary' => 'array', 'day' => 'date', 'last_run_at' => 'datetime', 'prepared_at' => 'datetime', 'signed_off_at' => 'datetime'];

    public function openBreaks(): int
    {
        return StatementLine::where('rail', $this->rail)->whereDate('day', $this->day)->whereIn('status', StatementLine::OPEN)->count();
    }

    public function present(): array
    {
        $s = $this->summary ?? [];
        $names = User::whereIn('id', array_filter([$this->prepared_by, $this->signed_off_by]))->pluck('name', 'id');

        return [
            'id' => $this->id,
            'day' => $this->day->format('D j M Y'),
            'date' => $this->day->toDateString(),
            'rail' => $this->rail,
            'lines' => $s['lines'] ?? 0,
            'matched' => ($s['matched'] ?? 0) + ($s['fixed'] ?? 0) + ($s['parked'] ?? 0),
            'breaks' => $this->openBreaks(),
            'not_on_statement' => $s['not_on_statement'] ?? 0,
            'money_in' => $s['in'] ?? '0.00',
            'money_out' => $s['out'] ?? '0.00',
            'status' => $this->status,
            'pill' => match ($this->status) {
                'signed_off' => ['label' => 'Signed off', 'tone' => 'success'],
                'prepared' => ['label' => 'Waiting for checker', 'tone' => 'warning'],
                default => ['label' => $this->openBreaks() ? 'Breaks open' : 'Ready to prepare', 'tone' => $this->openBreaks() ? 'error' : 'info'],
            },
            'prepared_by' => $names[$this->prepared_by] ?? null,
            'signed_off_by' => $names[$this->signed_off_by] ?? null,
            'last_run' => $this->last_run_at?->timezone('Africa/Nairobi')->format('j M, H:i'),
            'url' => route('ops.reconciliation.show', $this->id),
        ];
    }
}
