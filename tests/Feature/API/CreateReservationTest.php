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

        $this->withoutExceptionHandling();
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $reservationStartAt = Carbon::now()->addDays(4)->toDateTimeString();
        $children = [
            ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
            ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(5)->toDateString()],
            ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]
        ];

        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => $name,
                "customer_phone" => $phone,
                'start_at' => $reservationStartAt,
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
                'start_at' => $reservationStartAt,
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
        $children = [['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]];

        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => $name,
                "customer_phone" => null,
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
        $children = [['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]];


        $response = $this->postJson(
            $this->endpoint,
            [
                "customer_name" => null,
                "customer_phone" => $phone,
                "address" => $address,
                "children" => $children
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('customer_name');
    }

    public function test_reservation_must_start_six_hours_after_now(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $children = [['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]];

        // Reservation is after six hours from now
        $reservationStartAt = Carbon::now()->addHours(6)->toDateTimeString();

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "start_at" => $reservationStartAt,
            "address" => $address,
            "children" => $children
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('start_at');
    }

    public function test_reservation_must_be_within_sixty_days_from_now(): void
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $children = [['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]];

        // Reservation is within 60 days from now
        $reservationStartAt = Carbon::now()->addDays(61)->toDateTimeString();

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "address" => $address,
            "start_at" => $reservationStartAt,
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


        // Number of children is more than 4
        $children = [
            ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
            ['name' => 'Nurul', 'date_of_birth' => Carbon::now()->subYears(6)->toDateString()],
            ['name' => 'Muhammad', 'date_of_birth' => Carbon::now()->subYears(5)->toDateString()],
            ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(4)->toDateString()],
            ['name' => 'Siti', 'date_of_birth' => Carbon::now()->subYears(3)->toDateString()],
            ['name' => 'Hafiz', 'date_of_birth' => Carbon::now()->subYears(1)->toDateString()],
            ['name' => 'Aisyah', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]
        ];


        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
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


        $children = [];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children');
    }

    public function test_children_maximum_age_is_below_thirteen_years_old()
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;
        $startAt = Carbon::now()->addDays(40)->toDateString();
        $endAt = Carbon::now()->subHours(1)->toDateString();

        $children = [
            ['name' => 'Ahmad', 'date_of_birth' => Carbon::now()->subYears(13)->toDateString()],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.0.date_of_birth');
    }

    public function test_children_minimum_age_is_one_month_old()
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;


        $children = [
            ['name' => 'Ahmad', 'date_of_birth' => Carbon::now()->subDays()->toDateTimeString()],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.0.date_of_birth');
    }

    public function test_children_name_is_required()
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;


        $children = [
            ['name' => 'Ahmad', 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
            ['name' => null, 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.1.name');
    }

    public function test_children_date_of_birth_is_required()
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;
        $address = fake()->address;


        $children = [
            ['name' => 'Ahmad', 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
            ['name' => 'Lisa', 'date_of_birth' => null],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => $address,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('children.1.date_of_birth');
    }

    public function test_address_is_required()
    {
        $name = fake()->name;
        $phone = fake()->phoneNumber;

        $children = [
            ['name' => 'Ahmad', 'date_of_birth' => Carbon::now()->subYears(8)->toDateString()],
        ];

        $response = $this->postJson($this->endpoint, [
            "customer_name" => $name,
            "customer_phone" => $phone,
            "children" => $children,
            "address" => null,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('address');
    }

}
