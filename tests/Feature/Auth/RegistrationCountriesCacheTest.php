<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RegistrationCountriesCacheTest extends TestCase
{
    use RefreshDatabase;

    public function test_register_page_primes_countries_dropdown_cache(): void
    {
        Cache::forget('registration_countries_dropdown');

        $this->assertFalse(Cache::has('registration_countries_dropdown'));

        $response = $this
            ->withHeaders([
                'X-Inertia' => 'true',
                'X-Requested-With' => 'XMLHttpRequest',
            ])
            ->get('/register');

        $response
            ->assertOk()
            ->assertHeader('X-Inertia', 'true')
            ->assertJsonPath('component', 'Auth/Register');

        $this->assertTrue(Cache::has('registration_countries_dropdown'));

        $countries = Cache::get('registration_countries_dropdown');

        $this->assertIsArray($countries);
        $this->assertContains('Egypt', $countries);
        $this->assertContains('United States', $countries);
    }
}
