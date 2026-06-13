<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ManagerRequest extends Model
{
    protected $fillable = [
        'name',
        'email',
        'restaurant_name',
        'restaurant_phone',
        'restaurant_address',
        'payment_amount',
        'payment_status',
        'status',
        'stripe_session_id',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
    ];
}
