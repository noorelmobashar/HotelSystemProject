<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\InactiveClientReminderNotification;
use Illuminate\Console\Command;

class LoginReminder extends Command
{
    protected $signature = 'login:reminder';

    protected $description = 'Send queued reminder notifications to inactive clients who have not logged in for 30+ days.';

    public function handle(): int
    {
        $threshold = now()->subDays(30);

        $inactiveClients = User::query()
            ->role('client')
            ->whereNotNull('approved_at')
            ->where(function ($query) use ($threshold) {
                $query
                    ->where('last_login_at', '<=', $threshold)
                    ->orWhere(function ($neverLoggedInQuery) use ($threshold) {
                        $neverLoggedInQuery
                            ->whereNull('last_login_at')
                            ->where('approved_at', '<=', $threshold);
                    });
            })
            ->get();

        foreach ($inactiveClients as $client) {
            $client->notify(new InactiveClientReminderNotification());
        }

        $this->info("Queued reminders for {$inactiveClients->count()} inactive client(s).");

        return self::SUCCESS;
    }
}
