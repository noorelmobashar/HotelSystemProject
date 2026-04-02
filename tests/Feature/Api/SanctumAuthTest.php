<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SanctumAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_and_use_sanctum_protected_endpoints(): void
    {
        $user = User::factory()->create([
            'email' => 'api-user@example.com',
            'password' => 'password',
        ]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'api-user@example.com',
            'password' => 'password',
            'device_name' => 'phpunit',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('message', 'Logged in successfully.')
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('user.id', $user->id)
            ->assertJsonStructure([
                'token',
                'user' => ['id', 'name', 'email', 'roles'],
            ]);

        $token = $loginResponse->json('token');

        $this->assertNotEmpty($token);
        $this->assertDatabaseCount('personal_access_tokens', 1);

        $this
            ->withToken($token)
            ->getJson('/api/user')
            ->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', 'api-user@example.com');

        $this
            ->withToken($token)
            ->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('message', 'Logged out successfully.');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_protected_endpoint_requires_sanctum_token(): void
    {
        $this
            ->getJson('/api/user')
            ->assertUnauthorized();
    }
}
