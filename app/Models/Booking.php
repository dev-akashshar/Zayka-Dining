<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'restaurant_id',
    'user_id',
    'table_id',
    'guest_count',
    'booking_date',
    'booking_time',
    'status',
    'payment_status',
    'payment_type',
    'payment_amount',
    'stripe_payment_intent_id',
    'notes'
])]
class Booking extends Model
{
    use HasFactory, BelongsToTenant;

    protected function casts(): array
    {
        return [
            'booking_date' => 'date',
            'payment_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }
}
