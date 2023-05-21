<?php

namespace Tests\Feature\API;

use App\Actions\GenerateReferenceNumber;
use App\Http\Resources\ReservationResource;
use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class ShowReservationTest extends TestCase
{
    public function test_guest_can_view_reservation(): void
    {
//        $this->withoutExceptionHandling();

        $reservationNumber = (new GenerateReferenceNumber())->execute();

        // Create a customer
        $customer = Customer::factory()
            ->has(
                Reservation::factory()
                    ->count(1)
                    ->state(function (array $attributes, Customer $customer) use ($reservationNumber)  {
                        return [
                            'customer_id' => $customer->id,
                            'reference_number' => $reservationNumber,
                        ];
                    })
            )
            ->create();


        $response = $this->getJson('api/reservations/' . $reservationNumber);

        $response->assertStatus(200);

        $response->assertJson([
            'data' => new ReservationResource($customer->reservations()->latest()),
        ]);

    }
}
