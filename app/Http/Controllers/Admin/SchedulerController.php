<?php

namespace App\Http\Controllers\Admin;

use App\Console\Commands\SharetGoLive;
use App\Http\Controllers\Controller;
use App\Security\Lockdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Administration › Scheduled jobs: is the cron running, what runs when, and "Run now" for a fixed list of safe commands
 * (the scheduler itself, each scheduled job, and the go-live check). Running asks for the password again and is logged.
 * Never runs anything that isn't on the list below.
 */
class SchedulerController extends Controller
{
    /** command => [label, what it does] */
    public const COMMANDS = [
        'schedule:run' => ['Run the scheduler now', 'What the cron runs every minute: starts every job that is due.'],
        'ramp:resolve' => ['Resolve waiting payments', 'Asks M-PESA and banks about payments that are still waiting.'],
        'api:webhooks-retry' => ['Retry webhooks', 'Sends API webhook deliveries that are due again.'],
        'api:requests-expire' => ['Expire payment requests', 'Closes API payment requests that are past their time.'],
        'ledger:check' => ['Check the ledger', 'Confirms every journal balances and the hash chain is intact.'],
        'recon:run' => ['Reconcile now', 'Matches the ledger against M-PESA and bank statements.'],
        'db:backup' => ['Back up the database', 'Writes a compressed backup to the server (copy it off the server too).'],
        'kyc:reread' => ['Re-read uploaded IDs', 'Reads every uploaded ID photo again and fills name, ID number, birth date, county and town from it.'],
        'sharet:golive' => ['Go-live check', 'Lists what still blocks real money.'],
    ];

    public function index()
    {
        $beat = SharetGoLive::heartbeat();
        try {
            Artisan::call('schedule:list');
            $schedule = trim(Artisan::output());
        } catch (Throwable $e) {
            $schedule = 'Could not list the schedule: '.$e->getMessage();
        }

        return view('admin.scheduler.index', [
            'beat' => $beat,
            'running' => $beat && $beat->gt(now()->subMinutes(3)),
            'schedule' => $schedule,
            'commands' => self::COMMANDS,
        ]);
    }

    public function run(Request $request)
    {
        $data = $request->validate(['command' => ['required', 'in:'.implode(',', array_keys(self::COMMANDS))]]);
        @set_time_limit(300);
        $started = microtime(true);
        try {
            $code = Artisan::call($data['command']);
            $output = trim(Artisan::output());
        } catch (Throwable $e) {
            $code = 1;
            $output = $e->getMessage();
        }
        $seconds = round(microtime(true) - $started, 1);
        Lockdown::log('admin ran a scheduled command', ['command' => $data['command'], 'exit' => $code, 'seconds' => $seconds]);

        return redirect()->route('admin.scheduler.index')->with('ran', [
            'command' => $data['command'], 'label' => self::COMMANDS[$data['command']][0], 'ok' => $code === 0,
            'seconds' => $seconds, 'output' => $output !== '' ? $output : 'Done (no output).',
        ]);
    }
}
