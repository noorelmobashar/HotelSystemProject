<?php

namespace Tests\Feature\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_reminder_command_is_scheduled_daily(): void
    {
        $events = app(Schedule::class)->events();

        $matched = collect($events)->contains(function ($event) {
            return str_contains((string) $event->command, 'login:reminder')
                && $event->expression === '0 0 * * *';
        });

        $this->assertTrue($matched, 'Expected login:reminder to be scheduled daily.');
    }
}
