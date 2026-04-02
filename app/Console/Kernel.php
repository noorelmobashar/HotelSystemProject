<?php

namespace App\Console;

use App\Console\Commands\ArchiveOldReservations;
use App\Console\Commands\LoginReminder;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        ArchiveOldReservations::class,
        LoginReminder::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(ArchiveOldReservations::class)->daily();
        $schedule->command(LoginReminder::class)->daily();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
