<?php

namespace App\Concerns;

use App\Models\Restaurant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        static::creating(function ($model) {
            if (Auth::check() && ! $model->restaurant_id) {
                $user = Auth::user();
                if ($user->restaurant_id) {
                    $model->restaurant_id = $user->restaurant_id;
                }
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (Auth::check()) {
                $user = Auth::user();
                // If user is a tenant user (Manager/Staff), scope queries by their restaurant_id
                if ($user->restaurant_id && ! $user->hasRole('super_admin')) {
                    $builder->where($builder->getModel()->getTable() . '.restaurant_id', $user->restaurant_id);
                }
            }
        });
    }

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
