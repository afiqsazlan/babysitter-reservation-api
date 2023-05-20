<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class StoreReservationRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_name' => ['required'],
            'customer_phone' => ['required'],
            'address' => ['required', 'string'],

            'start_at' => [
                'required',
                'date',
                // Validate reservation is 6 hours after current time
                function ($attribute, $startAt, $fail) {
                    $startAt = Carbon::parse($startAt);
                    $currentDateTime = Carbon::now();

                    if ($startAt->diffInHours($currentDateTime) < 6) {
                        $fail("The reservation must be at least six hours from now.");
                    }
                },
                // Validate reservation is not more than 60 days from today
                function ($attribute, $startAt, $fail) {
                    $reservationDate = Carbon::parse($startAt);
                    $todayDate = Carbon::now();
                    $maxAllowedDays = 60;

                    if ($reservationDate->diffInDays($todayDate) + 1 > $maxAllowedDays) {
                        $fail("The reservation must be within {$maxAllowedDays} days from today.");
                    }
                },
            ],


            'children' => [
                'required',
                'array',
                'min:1',
                'max:4',
            ],

            'children.*.name' => ['required'],
            'children.*.date_of_birth' => [
                'required',
                'date',

                // Max age of child is below 13 years old
                function ($attribute, $dateOfBirth, $fail) {
                    $maxDate = Carbon::now()->subYears(13)->toDateString();

                    if ($dateOfBirth <= $maxDate) {
                        $fail("Only children below 13 years old are allowed");
                    }
                },

                // Min age of child is 1 month
                function ($attribute, $dateOfBirth, $fail) {
                    $minDate = Carbon::now()->subMonth()->toDateString();

                    if ($dateOfBirth >= $minDate) {
                        $fail("Only children above 1 month old are allowed");
                    }
                },
            ]
        ];
    }
}
