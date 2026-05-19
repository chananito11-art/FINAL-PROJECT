<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Console\Command;

class CheckIdExpiration extends Command
{
    protected $signature = 'users:check-id-expiration';
    protected $description = 'Automatically unverify users whose ID expiration date has passed.';

    public function handle()
    {
        $expiredUsers = User::where('verification_status', 'verified')
            ->whereNotNull('id_expiration_date')
            ->where('id_expiration_date', '<', now()->toDateString())
            ->get();

        if ($expiredUsers->isEmpty()) {
            $this->info('No newly expired IDs found.');
            return 0;
        }

        foreach ($expiredUsers as $user) {
            $user->update(['verification_status' => 'expired']);
            
            ActivityLog::log(
                "User verification auto-expired: {$user->name} (ID expired on {$user->id_expiration_date->format('Y-m-d')})",
                User::class,
                $user->id
            );

            $this->line("  ✓ User #{$user->id} ({$user->name}) status set to expired.");
        }

        $this->info("Processed {$expiredUsers->count()} expired verification(s).");
        return 0;
    }
}
