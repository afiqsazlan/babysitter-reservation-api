<?php

namespace App\Http\Controllers\API;

use App\Actions\GenerateReferenceNumber;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Customer;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function store(
        StoreReservationRequest                 $request,
        GenerateReferenceNumber $generateReferenceNumber
    )
    {
        $customer = Customer::firstOrCreate(
            ['phone' => $request->customer_phone],
            ['name' => $request->customer_name]
        );

        $reservation = new Reservation([
            'customer_id' => $request->customer_id,
            'reference_number' => $generateReferenceNumber->execute(),
            'address' => $request->address,
            'start_at' => $request->start_at,
            'children' => $request->children
        ]);

        $customer->reservations()->save($reservation);

        return response()->json([], 201);
    }

    public function show($reservationNumber) {
        $reservation = Reservation::where('reference_number', $reservationNumber)->firstOrFail();
        return new ReservationResource($reservation);
    }
}
