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
        $phone = fake()->phoneNumber;
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
                "customer_phone" => $phone,
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
                'phone' => $phone
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
            ->assertJsonValidationErrors('customer_phone');
    }

    public function test_customer_name_is_required(): void
    {

        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->subHours(12)->toDateTimeString();
        $endAt = Carbon::now()->subHours(12)->toDateTimeString();
        $children = [['name' => 'Ali', 'age_months' => 12]];


        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => null,
                "customer_phone" => $phone,
                "start_at" => $startAt,
                "end_at" => $endAt,
                "address" => $address,
                "children" => $children
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('customer_name');
    }

    public function test_reservation_must_start_six_hours_before_now(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $children = [
            ['name' => 'Ali', 'age_months' => 12],
        ];

        // Start time is less than six hours before the current time
        $startAt = Carbon::now()->subHours(5)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "address" => $address,
            "children" => $children
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('start_at');
    }

    public function test_reservation_must_within_sixty_days_from_now(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $children = [
            ['name' => 'Ali', 'age_months' => 12],
        ];

        // Start date is more than 60 days from now
        $startAt = Carbon::now()->addDays(66)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "address" => $address,
            "children" => $children
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('start_at');
    }

    public function test_maximum_four_children_per_reservation(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        // Number of children is more than 4
        $children = [
            ['name' => 'Ahmad', 'age_months' => 12],
            ['name' => 'Nurul', 'age_months' => 15],
            ['name' => 'Muhammad', 'age_months' => 18],
            ['name' => 'Siti', 'age_months' => 21],
            ['name' => 'Hafiz', 'age_months' => 24],
            ['name' => 'Aisyah', 'age_months' => 27],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "address" => $address,
            "children" => $children
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children');
    }

    public function test_minimum_one_child_required(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();
        $children = [];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children');
    }

    public function test_children_maximum_age_is_below_thirteen_years_old() {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $children = [
            ['name' => 'Ahmad', 'age_months' => 180],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.0.age_months');
    }

    public function test_children_minimum_age_is_one_month_old() {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $children = [
            ['name' => 'Ahmad', 'age_months' => 0],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.0.age_months');
    }

    public function test_children_name_is_required() {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $children = [
            ['name' => 'Ahmad', 'age_months' => 180],
            ['name' => null, 'age_months' => 180],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.1.name');
    }

    public function test_children_age_in_months_is_required() {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateTimeString();
        $endAt = Carbon::now()->subHours(1)->toDateTimeString();

        $children = [
            ['name' => 'Ahmad', 'age_months' => 180],
            ['name' => 'Lisa', 'age_months' => null],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $startAt,
            "end_at" => $endAt,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.1.age_months');
    }




}
