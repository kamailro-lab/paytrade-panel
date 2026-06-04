<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabase extends Command
{
    protected $signature = 'backup:db {--keep=14 : Ile dni backupów zachować}';
    protected $description = 'Backup bazy MySQL przez mysqldump (gzip)';

    public function handle(): int
    {
        $database = config('database.connections.mysql.database');
        $filename = 'backup-' . $database . '-' . date('Y-m-d_His') . '.sql.gz';
        $localPath = storage_path('app/private/backups/' . $filename);

        if (!is_dir(dirname($localPath))) {
            mkdir(dirname($localPath), 0750, true);
        }

        $this->info("Backup → {$filename}");

        $cmd = sprintf(
            'mysqldump --no-tablespaces -h%s -P%s -u%s -p%s %s | gzip > %s',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password')),
            escapeshellarg($database),
            escapeshellarg($localPath)
        );

        $process = Process::fromShellCommandline($cmd, null, null, null, 600);
        $process->run();

        if (!$process->isSuccessful() || !file_exists($localPath)) {
            $this->error('mysqldump failed: ' . $process->getErrorOutput());
            return Command::FAILURE;
        }

        $size = round(filesize($localPath) / 1024, 1);
        $this->info("✓ Backup gotowy ({$size} KB)");

        $this->cleanOldBackups((int) $this->option('keep'));

        return Command::SUCCESS;
    }

    private function cleanOldBackups(int $keepDays): void
    {
        $dir = storage_path('app/private/backups');
        if (!is_dir($dir)) return;

        $cutoff = time() - ($keepDays * 86400);
        $deleted = 0;

        foreach (glob($dir . '/backup-*.sql.gz') as $file) {
            if (filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        if ($deleted > 0) {
            $this->info("Usunięto {$deleted} starych backupów (>{$keepDays} dni).");
        }
    }
}
