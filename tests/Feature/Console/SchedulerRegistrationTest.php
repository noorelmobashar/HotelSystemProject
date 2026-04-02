<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SchedulerRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_reminder_command_is_scheduled_daily(): void
    {
        Artisan::call('schedule:list');

        $output = Artisan::output();

        $this->assertStringContainsString('0 0 * * *', $output);
        $this->assertStringContainsString('login:reminder', $output);
    }

    public function test_archive_old_reservations_command_is_scheduled_daily(): void
    {
        Artisan::call('schedule:list');

        $output = Artisan::output();

        $this->assertStringContainsString('0 0 * * *', $output);
        $this->assertStringContainsString('reservations:archive-old', $output);
    }
}
