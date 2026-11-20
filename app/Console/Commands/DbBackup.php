<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;

/**
 * php artisan db:backup — a compressed dump of the whole database (with the ledger's triggers) into DB_BACKUP_PATH
 * (live: a hidden folder in the site's home, outside the web folder and every release; default storage/app/backups),
 * readable only by the site user, keeping the last --keep days. Scheduled nightly; the password
 * goes through a private options file, never the command line (so `ps` never shows it).
 */
class DbBackup extends Command
{
    protected $signature = 'db:backup {--keep=14 : days of backups to keep}';

    protected $description = 'Dump the database to storage/app/backups and prune old dumps';

    public function handle(): int
    {
        $db = config('database.connections.'.config('database.default'));
        if (! in_array($db['driver'] ?? '', ['mysql', 'mariadb'], true)) {
            $this->warn('Only MySQL / MariaDB databases are backed up this way.');

            return self::SUCCESS;
        }

        $dir = rtrim(config('database.backup_path') ?: storage_path('app/backups'), '/');
        File::ensureDirectoryExists($dir, 0700);
        @chmod($dir, 0700);
        $file = $dir.'/'.$db['database'].'-'.now('Africa/Nairobi')->format('Ymd-His').'.sql.gz';
        $cnf = tempnam(sys_get_temp_dir(), 'dbb');
        file_put_contents($cnf, "[client]\nuser=\"{$db['username']}\"\npassword=\"".addcslashes((string) $db['password'], '"\\')."\"\nhost=\"{$db['host']}\"\nport={$db['port']}\n");
        chmod($cnf, 0600);

        try {
            // bash, not sh: PIPESTATUS reports mysqldump's own exit code through the pipe
            $dump = Process::timeout(1800)->run(['bash', '-c', sprintf('mysqldump --defaults-extra-file=%s --single-transaction --routines --triggers --no-tablespaces %s | gzip > %s; exit ${PIPESTATUS[0]}',
                escapeshellarg($cnf), escapeshellarg($db['database']), escapeshellarg($file))]);
        } finally {
            @unlink($cnf);
        }
        if (! $dump->successful() || ! is_file($file) || filesize($file) < 100) {
            @unlink($file);
            $this->error('Backup failed: '.trim($dump->errorOutput()));

            return self::FAILURE;
        }
        chmod($file, 0600);

        $cutoff = now()->subDays((int) $this->option('keep'))->getTimestamp();
        $pruned = collect(File::glob($dir.'/*.sql.gz'))->filter(fn ($f) => filemtime($f) < $cutoff)->each(fn ($f) => File::delete($f))->count();
        $this->info('Backed up to '.$file.' ('.round(filesize($file) / 1024).' KB); removed '.$pruned.' older.');

        return self::SUCCESS;
    }
}
