<?php

namespace Tests\Feature\Console;

use App\Models\User;
use App\Notifications\InactiveClientReminderNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LoginReminderCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_reminder_command_queues_notifications_for_inactive_clients_only(): void
    {
        Queue::fake();

        Role::firstOrCreate(['name' => 'client']);

        $inactiveClient = User::factory()->create([
            'approved_at' => now()->subDays(40),
            'last_login_at' => now()->subDays(31),
        ]);
        $inactiveClient->assignRole('client');

        $activeClient = User::factory()->create([
            'approved_at' => now()->subDays(40),
            'last_login_at' => now()->subDays(5),
        ]);
        $activeClient->assignRole('client');

        $pendingClient = User::factory()->create([
            'approved_at' => null,
            'last_login_at' => now()->subDays(45),
        ]);
        $pendingClient->assignRole('client');

        $this->artisan('login:reminder')->assertSuccessful();

        Queue::assertPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($inactiveClient) {
            return $job->notification instanceof InactiveClientReminderNotification
                && $job->notifiables->contains(fn ($notifiable) => (int) $notifiable->id === (int) $inactiveClient->id);
        });

        Queue::assertNotPushed(SendQueuedNotifications::class, function (SendQueuedNotifications $job) use ($activeClient, $pendingClient) {
            if (!($job->notification instanceof InactiveClientReminderNotification)) {
                return false;
            }

            return $job->notifiables->contains(fn ($notifiable) => in_array((int) $notifiable->id, [$activeClient->id, $pendingClient->id], true));
        });
    }

    public function test_inactive_reminder_notification_implements_should_queue(): void
    {
        $this->assertContains(ShouldQueue::class, class_implements(InactiveClientReminderNotification::class));
    }
}
