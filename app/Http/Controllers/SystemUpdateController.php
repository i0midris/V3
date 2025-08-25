<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class SystemUpdateController extends Controller
{
    /**
     * Run the system update process.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function run(Request $request)
    {
        $branch = config('app.branch', 'main'); // Default to 'main' branch
        $status = null;
        $output = [];

        try {
            $this->backupDatabase();
             
            $this->infoLog("📦 Starting system update on branch: {$branch}");

            // Step 1: Stash any uncommitted changes (only tracked files)
            $this->infoLog('🔒 Stashing local tracked changes...');
            $this->execCommand('git stash', $output, $status);

            // Step 2: Fetch latest changes from remote
            $this->infoLog("⬇️ Fetching from origin/{$branch}...");
            $this->execCommand("git fetch origin {$branch}", $output, $status);

            // Step 3: Reset tracked files to match remote branch
            $this->infoLog("🔁 Resetting tracked files to origin/{$branch}...");
            $this->execCommand("git reset --hard origin/{$branch}", $output, $status);

            // Step 4: Run database migrations
            $this->infoLog('⚙️ Running migrations...');
            Artisan::call('migrate', ['--force' => true]);
            $this->infoLog(Artisan::output());

            // Step 5: Clear and re-cache Laravel config/routes/views
            $this->infoLog('🧹 Clearing & caching config, routes, and views...');
            $this->clearCaches();

            return $this->jsonResponse(
                'success',
                __('business.update_successful')
            );
        } catch (Exception $e) {
            Log::error('System update failed', ['error' => $e->getMessage()]);

            return $this->jsonResponse(
                'error',
                __('business.update_failed').': '.$e->getMessage()
            );
        }
    }

    /**
 * Backup the database using mysqldump.
 *
 * @throws \Exception
 */

 private function backupDatabase()
 {
     $this->infoLog('🛡️ Backing up database...');
 
     $backupPath = storage_path('backups');
     if (!file_exists($backupPath)) {
         mkdir($backupPath, 0755, true);
     }
 
     $timestamp = now()->format('Y-m-d_H-i-s');
     $backupFile = $backupPath . "/db_backup_{$timestamp}.sql";
 
     $dbName = escapeshellarg(config('database.connections.mysql.database'));
     $dbUser = escapeshellarg(config('database.connections.mysql.username'));
     $dbPass = config('database.connections.mysql.password');
     $dbHost = escapeshellarg(config('database.connections.mysql.host'));
 
     $passwordPart = $dbPass ? "-p" . escapeshellarg($dbPass) : '';
 
     // استخدم المسار الصحيح مع توجيه stderr إلى stdout
     $command = "/bin/mysqldump -h {$dbHost} -u{$dbUser} {$passwordPart} {$dbName} > \"{$backupFile}\" 2>&1";
 
     exec($command, $output, $status);
 
     if ($status !== 0) {
         Log::error('❌ Backup command failed', [
             'command' => $command,
             'output' => $output,
             'status' => $status,
         ]);
         throw new \Exception('❌ Database backup failed.');
     }
 
     $this->infoLog("✅ Backup saved: {$backupFile}");
 }
 


    /**
     * Show the "What's New" changelog from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function whatsNew()
    {
        try {
            $url = config('app.changelog_url', 'https://raw.githubusercontent.com/i0midris/changelog/main/changelog.md');

            $this->infoLog("📄 Fetching changelog from: {$url}");
            $markdown = @file_get_contents($url);

            if ($markdown === false) {
                return $this->htmlResponse(
                    '<p>'.__('business.changelog_unavailable').'</p>',
                    404
                );
            }

            $html = Str::markdown($markdown);

            return $this->htmlResponse($html);
        } catch (Exception $e) {
            Log::error('Failed to fetch changelog', ['error' => $e->getMessage()]);

            return $this->htmlResponse(
                '<p>'.__('business.changelog_error').': '.$e->getMessage().'</p>',
                500
            );
        }
    }

    /**
     * Execute a shell command and handle errors.
     *
     * @param  string  $command
     * @param  array  &$output
     * @param  int  &$status
     *
     * @throws Exception
     */
    private function execCommand($command, &$output, &$status)
    {
        exec($command, $output, $status);
        if ($status !== 0) {
            Log::error("❌ Command failed: {$command}", ['output' => $output]);
            throw new Exception("Command failed: {$command}");
        }

        Log::info("✅ Command succeeded: {$command}");
        Log::info(implode("\n", $output));
    }

    /**
     * Clear Laravel application caches.
     */
    private function clearCaches()
    {
        Artisan::call('config:cache');
        Artisan::call('route:cache');
        Artisan::call('view:clear');
        $this->infoLog('✅ Caches cleared successfully.');
    }

    /**
     * Log an informational message.
     *
     * @param  string  $message
     */
    private function infoLog($message)
    {
        Log::info($message);
    }

    /**
     * Return a JSON response.
     *
     * @param  string  $status
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    private function jsonResponse($status, $message)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
        ]);
    }

    /**
     * Return an HTML response.
     *
     * @param  string  $html
     * @param  int  $statusCode
     * @return \Illuminate\Http\Response
     */
    private function htmlResponse($html, $statusCode = 200)
    {
        return Response::make($html, $statusCode, ['Content-Type' => 'text/html']);
    }
}
