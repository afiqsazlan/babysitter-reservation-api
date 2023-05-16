<?php

namespace Tests\Feature\API;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_customer_can_create_reservation(): void
    {
        $reservationDetails = [
            ''
        ];

        $response = $this->postJson('api/reservations/create', $reservationDetails);

        $response->assertStatus(201);
    }
}
