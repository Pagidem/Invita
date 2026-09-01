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
}
