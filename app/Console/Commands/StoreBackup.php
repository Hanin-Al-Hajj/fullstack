<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;
use ZipArchive;

class StoreBackup extends Command
{
    protected $signature = 'store:backup {--keep=14 : how many backups to keep}';

    protected $description = 'Back up the database and the uploaded photos into storage/app/backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');
        File::ensureDirectoryExists($dir);

        $stamp = now()->format('Ymd-His');
        $sql = "{$dir}/db-{$stamp}.sql";
        $db = config('database.connections.'.config('database.default'));

        // The password goes in an environment variable so it never shows up in a process list.
        $process = new Process([
            env('MYSQLDUMP_PATH', 'mysqldump'),
            '--host='.$db['host'],
            '--port='.$db['port'],
            '--user='.$db['username'],
            '--single-transaction',
            '--routines',
            '--result-file='.$sql,
            $db['database'],
        ], null, ['MYSQL_PWD' => (string) ($db['password'] ?? '')]);
        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful() || ! File::exists($sql) || File::size($sql) === 0) {
            File::delete($sql);
            $this->error('The database backup failed. '.(trim($process->getErrorOutput()) ?: 'mysqldump was not found: set MYSQLDUMP_PATH in .env.'));

            return self::FAILURE;
        }

        if (class_exists(ZipArchive::class)) {
            $zipPath = "{$dir}/backup-{$stamp}.zip";
            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
                $zip->addFile($sql, "db-{$stamp}.sql");

                $uploads = storage_path('app/public');
                if (File::isDirectory($uploads)) {
                    foreach (File::allFiles($uploads) as $file) {
                        $zip->addFile($file->getPathname(), 'uploads/'.str_replace('\\', '/', $file->getRelativePathname()));
                    }
                }

                $zip->close();
                File::delete($sql);
                $this->info("Backup saved: {$zipPath}");
            }
        } else {
            $this->warn("PHP's zip extension is off, so only the database was saved: {$sql}");
        }

        $this->prune((int) $this->option('keep'));

        return self::SUCCESS;
    }

    private function prune(int $keep): void
    {
        $dir = storage_path('app/backups');

        foreach (['backup-*.zip', 'db-*.sql'] as $pattern) {
            $files = collect(File::glob("{$dir}/{$pattern}"))->sortDesc()->values();
            $files->slice($keep)->each(fn ($f) => File::delete($f));
        }
    }
}
