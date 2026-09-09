<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\GuestController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class GuestControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_a_guest_with_ci(): void
    {
        $request = new Request([
            'ci' => '123123',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '77000000',
            'email' => 'juan@test.com',
            'invitations' => 2,
            'notes' => 'Amigo del colegio',
        ]);

        $controller = new GuestController();
        $response = $controller->store($request);

        $this->assertEquals(201, $response->getStatusCode());
        $this->assertDatabaseHas('guests', [
            'ci' => '123123',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
        ]);
    }

    public function test_it_returns_a_user_friendly_error_when_ci_already_exists(): void
    {
        $this->postJson('/api/guests', [
            'ci' => '123123',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'phone' => '77000000',
            'email' => 'juan@test.com',
            'invitations' => 2,
            'notes' => 'Amigo del colegio',
        ])->assertCreated();

        $response = $this->postJson('/api/guests', [
            'ci' => '123123',
            'first_name' => 'Ana',
            'last_name' => 'García',
            'phone' => '77111111',
            'email' => 'ana@test.com',
            'invitations' => 1,
            'notes' => 'Duplicado',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('message', 'La cédula ya está registrada.');

        $this->assertStringNotContainsString('SQLSTATE', $response->getContent());
        $this->assertStringNotContainsString('Duplicate entry', $response->getContent());
    }
}
