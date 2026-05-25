<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('env {action} {key?} {value?} {--no-clear}', function (string $action, string $key = null, string $value = null) {
    $action = strtolower($action);
    $clear = !$this->option('no-clear');

    $refreshCache = function () use ($clear) {
        if (!$clear) {
            return;
        }

        $this->call('config:clear');
        $this->call('cache:clear');
        $this->call('route:clear');
        $this->call('view:clear');
    };

    switch ($action) {
        case 'get':
            if (!$key) {
                return $this->error('Usage: php artisan env get KEY');
            }

            $result = env_get($key);

            if ($result === null) {
                return $this->warn("{$key} not found");
            }

            $this->line($result);
            break;

        case 'set':
            if (!$key || $value === null) {
                return $this->error('Usage: php artisan env set KEY VALUE');
            }

            if (!env_set($key, $value)) {
                return $this->error("Unable to write {$key} to .env");
            }

            $this->info("Set {$key}={$value}");
            $refreshCache();
            break;

        case 'delete':
            if (!$key) {
                return $this->error('Usage: php artisan env delete KEY');
            }

            if (env_delete($key)) {
                $this->info("Deleted {$key}");
                $refreshCache();
            } else {
                $this->warn("{$key} was not found");
            }
            break;

        case 'exists':
            if (!$key) {
                return $this->error('Usage: php artisan env exists KEY');
            }

            $this->line(env_exists($key) ? 'yes' : 'no');
            break;

        case 'list':
            $envPath = base_path('.env');

            if (!file_exists($envPath)) {
                return $this->error('.env file not found');
            }

            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $trimmed = trim($line);

                if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                    continue;
                }

                $this->line($trimmed);
            }
            break;

        default:
            $this->error('Unknown env action. Available actions: get, set, delete, exists, list');
            break;
    }
})->purpose('Manage .env variables from the CLI');

// Auto-expire pending_payment bookings every 15 minutes
Schedule::command('bookings:expire-holds')->everyFifteenMinutes();

// Check for expired IDs daily
Schedule::command('users:check-id-expiration')->dailyAt('00:01');

// Mark no-shows daily at 1:00 AM (checks if pickup date passed)
Schedule::command('bookings:check-no-shows')->dailyAt('01:00');

// Generate maintenance alerts daily
Schedule::command('maintenance:generate-tasks')->daily();
