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
        return 'Search for available summer camps by age, category, borough, week, price, and schedule type. Returns up to 20 matching camps with key details. Use this to find camps that match a child\'s criteria.';
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
            ->limit(20)
            ->get()
            ->map(fn (Camp $camp) => [
                'id' => $camp->id,
                'name' => $camp->name,
                'facility_id' => $camp->facility_id,
                'facility_name' => $camp->facility->name,
                'borough' => $camp->facility->borough,
                'neighborhood' => $camp->facility->neighborhood,
                'category' => $camp->category,
                'age_range' => "{$camp->age_min}-{$camp->age_max}",
                'week_start' => $camp->week_start->toDateString(),
                'schedule_type' => $camp->schedule_type,
                'price' => $camp->formatted_price,
                'price_cents' => $camp->price_cents,
                'availability' => $camp->availability_status,
                'spots_remaining' => $camp->is_full ? 0 : $camp->spots_remaining,
                'waitlist_count' => $camp->waitlist_count,
                'lunch_provided' => $camp->lunch_provided,
            ]);

        if ($camps->isEmpty()) {
            return 'No camps found matching those criteria. Try broadening your search.';
        }

        return json_encode($camps->values()->toArray());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'age' => $schema->integer()->description('Child\'s age to filter camps that accept this age'),
            'category' => $schema->string()->description('Camp category: sports, arts, performing_arts, stem, nature, academic, general'),
            'borough' => $schema->string()->description('NYC borough: Manhattan, Brooklyn, Queens, Bronx, Staten Island'),
            'week_start' => $schema->string()->description('Monday date of the week in YYYY-MM-DD format, e.g. 2026-06-15'),
            'price_max_cents' => $schema->integer()->description('Maximum price in cents, e.g. 50000 for $500'),
            'schedule_type' => $schema->string()->description('Schedule: full_day, half_day_am, or half_day_pm'),
        ];
    }
}
