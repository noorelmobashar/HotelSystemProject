<?php

namespace Tests\Feature\Clients;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MyApprovedClientsViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_receptionist_can_view_only_their_approved_clients(): void
    {
        Role::firstOrCreate(['name' => 'receptionist']);
        Role::firstOrCreate(['name' => 'client']);

        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');

        $anotherReceptionist = User::factory()->create();
        $anotherReceptionist->assignRole('receptionist');

        $ownApprovedClient = User::factory()->create([
            'approved_at' => now()->subDay(),
            'approved_by' => $receptionist->id,
        ]);
        $ownApprovedClient->assignRole('client');

        $otherApprovedClient = User::factory()->create([
            'approved_at' => now()->subDay(),
            'approved_by' => $anotherReceptionist->id,
        ]);
        $otherApprovedClient->assignRole('client');

        $pendingClient = User::factory()->create([
            'approved_at' => null,
            'approved_by' => null,
        ]);
        $pendingClient->assignRole('client');

        $response = $this
            ->actingAs($receptionist)
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get(route('clients.my-approved'));

        $response
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Clients/MyApprovedClients');

        $clientIds = collect($response->json('props.clients.data'))->pluck('id')->all();

        $this->assertContains($ownApprovedClient->id, $clientIds);
        $this->assertNotContains($otherApprovedClient->id, $clientIds);
        $this->assertNotContains($pendingClient->id, $clientIds);
    }

    public function test_non_receptionist_cannot_access_my_approved_clients_view(): void
    {
        Role::firstOrCreate(['name' => 'client']);

        $client = User::factory()->create();
        $client->assignRole('client');

        $this
            ->actingAs($client)
            ->get(route('clients.my-approved'))
            ->assertForbidden();
    }
}
