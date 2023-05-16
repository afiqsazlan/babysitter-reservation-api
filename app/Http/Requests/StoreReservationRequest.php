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

            'start_at' => [
                'required',
                'date',
                // Validate start time is more than 6 hours earlier than current time
                function ($attribute, $value, $fail) {
                    $startAt = Carbon::parse($value);
                    $currentDateTime = Carbon::now();

                    if ($startAt->diffInHours($currentDateTime) < 6) {
                        $fail("The start time must be at least six hours before the current time.");
                    }
                },
                // Validate start time is not more than 60 days from current time
                function ($attribute, $value, $fail) {
                    $startAt = Carbon::parse($value);
                    $currentDateTime = Carbon::now();
                    $maxAllowedDays = 60;

                    if ($startAt->diffInDays($currentDateTime) > $maxAllowedDays) {
                        $fail("The start date cannot be more than {$maxAllowedDays} days from now.");
                    }
                },
            ],


            'children' => [
                'required',
                'array',
                'min:1',
                'max:4'
//                // Validate maximum of four children per reservation
//                function ($attribute, $children, $fail) {
//                    $maxAllowedChildren = 4;
//                    if (count($children) > 4) {
//                        $fail("The number of children cannot be more than {$maxAllowedChildren}.");
//                    }
//                },
            ],


        ];
    }
}
