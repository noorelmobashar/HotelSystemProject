<?php

namespace Tests\Feature\Console;

use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ArchiveOldReservationsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_archive_old_reservations_deactivates_and_soft_deletes_only_old_reservations(): void
    {
        Role::firstOrCreate(['name' => 'client']);

        $manager = User::factory()->create();

        $floor = Floor::query()->create([
            'name' => 'First Floor',
            'number' => 'F1',
            'created_by' => $manager->id,
        ]);

        $room = Room::query()->create([
            'number' => '101',
            'capacity' => 2,
            'price' => 500,
            'floor_id' => $floor->id,
            'created_by' => $manager->id,
        ]);

        $client = User::factory()->create();
        $client->assignRole('client');

        $oldActiveReservation = Reservation::query()->create([
            'client_id' => $client->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDays(10)->toDateString(),
            'check_out_date' => now()->subDay()->toDateString(),
            'accompany_number' => 1,
            'paid_price' => 1000,
            'is_active' => true,
        ]);

        $oldInactiveReservation = Reservation::query()->create([
            'client_id' => $client->id,
            'room_id' => $room->id,
            'check_in_date' => now()->subDays(8)->toDateString(),
            'check_out_date' => now()->subDays(2)->toDateString(),
            'accompany_number' => 1,
            'paid_price' => 900,
            'is_active' => false,
        ]);

        $currentReservation = Reservation::query()->create([
            'client_id' => $client->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDay()->toDateString(),
            'accompany_number' => 1,
            'paid_price' => 1200,
            'is_active' => true,
        ]);

        $this->artisan('reservations:archive-old')->assertSuccessful();

        $this->assertSoftDeleted('reservations', ['id' => $oldActiveReservation->id]);
        $this->assertSoftDeleted('reservations', ['id' => $oldInactiveReservation->id]);

        $this->assertFalse(
            Reservation::withTrashed()->findOrFail($oldActiveReservation->id)->is_active
        );

        $this->assertNotNull(
            Reservation::withTrashed()->findOrFail($oldInactiveReservation->id)->deleted_at
        );

        $this->assertNotNull($currentReservation->fresh());
        $this->assertNull($currentReservation->fresh()->deleted_at);
        $this->assertTrue((bool) $currentReservation->fresh()->is_active);
    }
}
