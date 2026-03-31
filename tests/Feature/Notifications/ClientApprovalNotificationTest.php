<?php

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\ClientApprovedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClientApprovalNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_approval_queues_welcome_notification(): void
    {
        Queue::fake();

        Role::firstOrCreate(['name' => 'receptionist']);
        Role::firstOrCreate(['name' => 'client']);

        $receptionist = User::factory()->create();
        $receptionist->assignRole('receptionist');

        $client = User::factory()->create([
            'approved_at' => null,
            'approved_by' => null,
        ]);
        $client->assignRole('client');

        $response = $this
            ->actingAs($receptionist)
            ->put(route('clients.approve', ['client' => $client->id]));

        $response->assertRedirect();

        $client->refresh();
        $this->assertNotNull($client->approved_at);
        $this->assertSame($receptionist->id, $client->approved_by);

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($client) {
            return $job->notification instanceof ClientApprovedNotification
                && $job->notifiables->contains(fn ($notifiable) => (int) $notifiable->id === (int) $client->id);
        });
    }

    public function test_client_approved_notification_implements_should_queue(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(ClientApprovedNotification::class));
    }
}
