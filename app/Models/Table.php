<?php

namespace App\Models;

use App\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['restaurant_id', 'name', 'capacity', 'status'])]
class Table extends Model
{
    use HasFactory, BelongsToTenant;

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
