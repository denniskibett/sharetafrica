<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\User;
use App\Support\Identity;
use Illuminate\Console\Command;

/**
 * Data check after the frontend audit (21 Nov 2026). Dry run by default; nothing about money is touched.
 *   php artisan sharet:audit-data            lists what it found
 *   php artisan sharet:audit-data --apply    also writes the fixes that are certain
 * Before --apply on live, back up `companies`: mysqldump … companies > /root/sharet-backups/companies-<date>.sql
 *
 * Certain (written with --apply): business phone numbers normalised to +254… (the same rule as people's numbers).
 * For a person (only listed): businesses with more than one member. Until 21 Nov, typing an existing business's name
 * at onboarding or on a website form joined that business, so staff should confirm each extra member belongs there.
 */
class SharetAuditData extends Command
{
    protected $signature = 'sharet:audit-data {--apply : Write the certain fixes}';

    protected $description = 'List data that needs a person and normalise business phone numbers (dry run unless --apply)';

    public function handle(): int
    {
        $apply = (bool) $this->option('apply');
        $this->info($apply ? 'Applying certain fixes.' : 'Dry run: nothing is written (add --apply).');

        $fixed = 0;
        Company::whereNotNull('phone')->where('phone', '!=', '')->orderBy('id')->each(function (Company $c) use ($apply, &$fixed) {
            $clean = Identity::normalisePhone($c->phone);
            if ($clean && $clean !== $c->phone && preg_match('/^\+\d{11,14}$/', $clean)) {
                $this->line("  phone  company #{$c->id} {$c->name}: {$c->phone} → {$clean}");
                $fixed++;
                $apply && $c->forceFill(['phone' => $clean])->saveQuietly();
            }
        });
        $this->info(($apply ? 'Normalised' : 'Would normalise')." $fixed business phone number(s).");

        $shared = User::where('is_business_wallet', false)->whereNotNull('company_id')
            ->selectRaw('company_id, count(*) n')->groupBy('company_id')->having('n', '>', 1)->pluck('n', 'company_id');
        $this->info("For a person: {$shared->count()} business(es) with more than one member (check each extra member belongs there).");
        foreach ($shared as $companyId => $n) {
            $c = Company::find($companyId);
            $this->line("  #{$companyId} {$c?->name} ({$n} members)");
            User::where('company_id', $companyId)->where('is_business_wallet', false)->orderBy('created_at')
                ->get(['id', 'name', 'email', 'created_at'])
                ->each(fn ($u) => $this->line("     user #{$u->id} {$u->name} <{$u->email}> joined {$u->created_at?->timezone('Africa/Nairobi')->format('j M Y H:i')}"));
        }

        return self::SUCCESS;
    }
}
