<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class ReservationResource extends JsonResource
{

    public function toArray(Request $request): array
    {

        return [
            "customer_name" => $this->customer?->name,
            "customer_phone" => $this->customer?->phone,
            'start_at' => $this->reservationStartAt,
            "address" => $this->address,
            "children" => array_map(function ($child) {
                return [
                    'name' => $child['name'],
                    'date_of_birth' => Carbon::parse($child['date_of_birth'])->toDateString(),
                    'age' => $this->calculateAge($child['date_of_birth'])
                ];
            }, $this->children)
        ];
    }

    private function calculateAge($dateOfBirth)
    {
        $dateOfBirth = Carbon::parse($dateOfBirth);
        $age = $dateOfBirth->diffInYears(Carbon::now());

        if ($age >= 1) {
            return $age . Str::plural(' year', $age) . ' old';
        } else {
            $age = $dateOfBirth->diffInMonths(Carbon::now());
            return $age . Str::plural(' month', $age) . ' old';
        }
    }
}
