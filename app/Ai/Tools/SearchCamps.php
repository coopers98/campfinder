<?php

namespace App\Ai\Tools;

use App\Models\Camp;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchCamps implements Tool
{
    public function description(): Stringable|string
    {
        return 'Search for summer camps across ALL weeks at once. Returns up to 50 results sorted by week. Use broad searches (e.g. just age) to get an overview, then narrow down. Do NOT call this once per week — one call covers the whole summer.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = Camp::with('facility');

        if ($request['age'] ?? null) {
            $query->forAge((int) $request['age']);
        }

        if ($request['category'] ?? null) {
            $query->inCategory($request['category']);
        }

        if ($request['borough'] ?? null) {
            $query->inBorough($request['borough']);
        }

        if ($request['week_start'] ?? null) {
            $query->inWeek($request['week_start']);
        }

        if ($request['price_max_cents'] ?? null) {
            $query->where('price_cents', '<=', (int) $request['price_max_cents']);
        }

        if ($request['schedule_type'] ?? null) {
            $query->where('schedule_type', $request['schedule_type']);
        }

        $camps = $query->orderBy('week_start')
            ->limit(50)
            ->get()
            ->map(fn (Camp $camp) => [
                'id' => $camp->id,
                'name' => $camp->name,
                'fid' => $camp->facility_id,
                'fac' => $camp->facility->name,
                'boro' => $camp->facility->borough,
                'cat' => $camp->category,
                'ages' => "{$camp->age_min}-{$camp->age_max}",
                'wk' => $camp->week_start->format('m-d'),
                'sched' => $camp->schedule_type,
                'price' => $camp->price_cents,
                'avail' => $camp->availability_status,
                'spots' => $camp->is_full ? 0 : $camp->spots_remaining,
                'wl' => $camp->waitlist_count,
                'lunch' => $camp->lunch_provided,
            ]);

        if ($camps->isEmpty()) {
            return 'No camps found. Try broader criteria (remove borough or category filter).';
        }

        return json_encode($camps->values()->toArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'age' => $schema->integer()->description('Child age to filter by'),
            'category' => $schema->string()->description('Category: sports, arts, performing_arts, stem, nature, academic, general'),
            'borough' => $schema->string()->description('Borough: Manhattan, Brooklyn, Queens, Bronx, Staten Island'),
            'week_start' => $schema->string()->description('Specific week YYYY-MM-DD (optional — omit to search all weeks)'),
            'price_max_cents' => $schema->integer()->description('Max price in cents'),
            'schedule_type' => $schema->string()->description('full_day, half_day_am, or half_day_pm'),
        ];
    }
}
