<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class SystemUpdate extends Command
{
    protected $signature = 'system:update';

    protected $description = 'Pull latest code, backup DB, run migrations, clear cache, and rollback if needed.';

    public function handle()
    {
        $this->info('🔄 Starting system update...');

        try {
            // Step 1: Backup the database
            $this->backupDatabase();

            // Step 2: Get current Git commit for potential rollback
            $oldHash = $this->getCurrentCommitHash();

            // Step 3: Pull latest code from Git (preserves untracked files)
            $this->pullLatestCode();

            // Step 4: Run migrations and clear Laravel caches
            $this->runMigrationsAndClearCaches();

            $this->info('✅ System update completed successfully!');

            return Command::SUCCESS;

        } catch (Exception $e) {
            $this->error('❌ Error during update: '.$e->getMessage());
            Log::error('System update failed', ['exception' => $e]);

            // Rollback both code and DB if needed
            $this->rollbackSystem($oldHash);

            return Command::FAILURE;
        }
    }

    /**
     * Backup the database using mysqldump.
     */
    private function backupDatabase()
    {
        $this->info('🛡️ Backing up database...');

        $backupPath = storage_path('backups');
        if (! file_exists($backupPath)) {
            mkdir($backupPath, 0755, true);
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupFile = $backupPath."/db_backup_{$timestamp}.sql";

        // DB credentials
        $dbName = config('database.connections.mysql.database');
        $dbUser = config('database.connections.mysql.username');
        $dbPass = config('database.connections.mysql.password');
        $dbHost = config('database.connections.mysql.host');

        $dumpCommand = "mysqldump -h {$dbHost} -u\"{$dbUser}\" ".
            ($dbPass ? "-p\"{$dbPass}\" " : '').
            "{$dbName} > \"{$backupFile}\"";

        exec($dumpCommand, $dumpOutput, $dumpStatus);

        if ($dumpStatus !== 0) {
            throw new Exception('Database backup failed.');
        }

        $this->info("✅ Backup saved: {$backupFile}");
    }

    /**
     * Get the current Git commit hash.
     */
    private function getCurrentCommitHash(): ?string
    {
        exec('git rev-parse HEAD', $currentCommitHash);

        return $currentCommitHash[0] ?? null;
    }

    /**
     * Pull latest code from Git (only tracked files will be overwritten).
     */
    private function pullLatestCode()
    {
        $branch = config('app.branch', 'main');
        $this->info("📥 Pulling latest code from '{$branch}'...");

        $gitCommand = "git reset --hard HEAD && git pull origin {$branch} --no-rebase --no-edit 2>&1";
        exec($gitCommand, $gitOutput, $gitStatus);

        $this->info(implode("\n", $gitOutput));

        if ($gitStatus !== 0) {
            throw new Exception('Git pull failed.');
        }
    }

    /**
     * Run Laravel migrations and clear caches.
     */
    private function runMigrationsAndClearCaches()
    {
        $this->info('⚙️ Running database migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        $this->info('🧹 Clearing and caching config, routes, and views...');
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:clear');
    }

    /**
     * Rollback the system (code & DB) if update fails.
     */
    private function rollbackSystem(?string $oldHash)
    {
        if ($oldHash) {
            $this->warn('🕑 Rolling back code to previous Git commit...');
            exec("git reset --hard {$oldHash}", $rollbackOutput);
            $this->warn(implode("\n", $rollbackOutput));
        }

        $this->warn('🔁 Rolling back last database migration batch...');
        Artisan::call('migrate:rollback', ['--step' => 1, '--force' => true]);
        $this->warn(Artisan::output());

        $this->info('✅ Rollback completed.');
    }
}
