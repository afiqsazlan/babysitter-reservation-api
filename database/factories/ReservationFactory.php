<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reservation>
 */
class ReservationFactory extends Factory
{

    public function definition(): array
    {
        return [
            'reference_number' => $this->faker->unique()->randomNumber(),
            'address' => $this->faker->address,
            'start_at' => Carbon::now(),
            'children' => [
                ['name' => 'Ali', 'date_of_birth' => \Carbon\Carbon::now()->subYears(8)->toDateString()],
                ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(5)->toDateString()],
                ['name' => 'Ali', 'date_of_birth' => Carbon::now()->subYears(2)->toDateString()]
            ],
        ];
    }
}
