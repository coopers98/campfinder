<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Camp extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'lunch_provided' => 'boolean',
            'age_min' => 'integer',
            'age_max' => 'integer',
            'price_cents' => 'integer',
            'capacity' => 'integer',
            'enrolled' => 'integer',
            'waitlist_count' => 'integer',
        ];
    }

    public function facility(): BelongsTo
    {
        return $this->belongsTo(Facility::class);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->enrolled >= $this->capacity;
    }

    public function getSpotsRemainingAttribute(): int
    {
        return max(0, $this->capacity - $this->enrolled);
    }

    public function getAvailabilityStatusAttribute(): string
    {
        if ($this->enrolled >= $this->capacity) {
            return 'waitlist';
        }
        if ($this->spots_remaining <= 3) {
            return 'almost_full';
        }
        return 'available';
    }

    public function getFormattedPriceAttribute(): string
    {
        return '$' . number_format($this->price_cents / 100, 0);
    }

    public function scopeForAge($query, int $age)
    {
        return $query->where('age_min', '<=', $age)->where('age_max', '>=', $age);
    }

    public function scopeInCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeInWeek($query, string $weekStart)
    {
        return $query->whereDate('week_start', $weekStart);
    }

    public function scopeAvailable($query)
    {
        return $query->whereColumn('enrolled', '<', 'capacity');
    }

    public function scopeInBorough($query, string $borough)
    {
        return $query->whereHas('facility', fn ($q) => $q->where('borough', $borough));
    }
}
