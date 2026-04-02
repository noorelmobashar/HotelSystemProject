<?php

namespace Tests\Feature\Reservations;

use App\Models\Floor;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientsReservationsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_can_access_clients_reservations_view(): void
    {
        Role::firstOrCreate(['name' => 'receptionist']);
        Role::firstOrCreate(['name' => 'client']);

        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');

        $reservation = $this->createReservation();

        $this
            ->actingAs($receptionist)
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get(route('reservations.clients.index'))
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Reservations/ClientsReservations')
            ->assertJsonPath('props.reservations.data.0.id', $reservation->id);
    }

    public function test_client_cannot_access_clients_reservations_view(): void
    {
        Role::firstOrCreate(['name' => 'client']);

        $client = User::factory()->create();
        $client->assignRole('client');

        $this
            ->actingAs($client)
            ->get(route('reservations.clients.index'))
            ->assertForbidden();
    }

    private function createReservation(): Reservation
    {
        $creator = User::factory()->create();

        $floor = Floor::query()->create([
            'name' => 'Main Floor',
            'number' => 'F1',
            'created_by' => $creator->id,
        ]);

        $room = Room::query()->create([
            'number' => '201',
            'capacity' => 2,
            'price' => 450,
            'floor_id' => $floor->id,
            'created_by' => $creator->id,
        ]);

        $client = User::factory()->create();
        $client->assignRole('client');

        return Reservation::query()->create([
            'client_id' => $client->id,
            'room_id' => $room->id,
            'check_in_date' => now()->toDateString(),
            'check_out_date' => now()->addDays(2)->toDateString(),
            'accompany_number' => 1,
            'paid_price' => 900,
            'is_active' => true,
        ]);
    }
}
