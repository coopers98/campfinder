<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Facility extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'lunch_provided' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function camps(): HasMany
    {
        return $this->hasMany(Camp::class);
    }

    public function scopeInBorough($query, string $borough)
    {
        return $query->where('borough', $borough);
    }
}
