<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class ArchiveOldReservations extends Command
{
    protected $signature = 'reservations:archive-old';

    protected $description = 'Set old reservations as inactive and soft delete them.';

    public function handle(): int
    {
        $thresholdDate = now()->toDateString();

        $oldReservations = Reservation::query()
            ->whereDate('check_out_date', '<', $thresholdDate);

        $deactivatedCount = (clone $oldReservations)
            ->where('is_active', true)
            ->update(['is_active' => false]);

        $softDeletedCount = (clone $oldReservations)->delete();

        $this->info("Deactivated {$deactivatedCount} old reservation(s).");
        $this->info("Soft deleted {$softDeletedCount} old reservation(s).");

        return self::SUCCESS;
    }
}
