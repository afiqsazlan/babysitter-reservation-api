<?php

namespace Tests\Feature\API;

use App\Actions\GenerateReferenceNumber;
use App\Models\Customer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CreateReservationTest extends TestCase
{

    private string $endpoint = 'api/reservations';

    public function test_guest_can_create_reservation(): void
    {

        $name = fake()->name;
        $phoneNumber = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->subHours(12)->toDateTimeString();
        $endAt = Carbon::now()->subHours(12)->toDateTimeString();
        $children = [
            ['name' => 'Ali', 'age_months' => 12],
            ['name' => 'Ali', 'age_months' => 25],
            ['name' => 'Ali', 'age_months' => 65]
        ];

        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => $name,
                "customer_phone" => $phoneNumber,
                "start_at" => $startAt,
                "end_at" => $endAt,
                "address" => $address,
                "children" => $children
            ]
        );

        $response->assertStatus(201);


        // Assert customer exist
        $this->assertDatabaseHas(
            'customers',
            [
                'name' => $name,
                'phone' => $phoneNumber
            ]
        );

        // Assert new reservation is created
        $this->assertDatabaseHas(
            'reservations',
            [
                "address" => $address,
                "start_at" => $startAt,
                "end_at" => $endAt,
                "children" => $this->castAsJson($children)
            ]
        );
    }


    public function test_customer_phone_is_required(): void
    {

        $name = fake()->name;
        $address = fake()->address;
        $startAt = Carbon::now()->subHours(12)->toDateTimeString();
        $endAt = Carbon::now()->subHours(12)->toDateTimeString();
        $children = [['name' => 'Ali', 'age_months' => 12]];


        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => $name,
                "customer_phone" => null,
                "start_at" => $startAt,
                "end_at" => $endAt,
                "address" => $address,
                "children" => $children
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('phone');
    }


}
