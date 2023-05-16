<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    protected $table = 'reservations';

    protected $fillable = [
        'customer_id',
        'reference_number',
        'address',
        'start_at',
        'end_at',
        'children'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'children' => 'json',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

}
