<?php

namespace App\Ai\Tools;

use App\Models\Camp;
use App\Models\Facility;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetFacilityDetails implements Tool
{
    public function description(): Stringable|string
    {
        return 'Get detailed information about a facility including its amenities, location, and all camps it offers. Use this to check what other camps are available at a facility where you are considering placing a child.';
    }

    public function handle(Request $request): Stringable|string
    {
        $facility = Facility::with('camps')->find((int) $request['facility_id']);

        if (!$facility) {
            return 'Facility not found.';
        }

        $camps = $facility->camps->map(fn (Camp $camp) => [
            'id' => $camp->id,
            'name' => $camp->name,
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

        return json_encode([
            'id' => $facility->id,
            'name' => $facility->name,
            'borough' => $facility->borough,
            'neighborhood' => $facility->neighborhood,
            'address' => $facility->address,
            'description' => $facility->description,
            'amenities' => $facility->amenities,
            'lunch_provided' => $facility->lunch_provided,
            'camps' => $camps->values()->toArray(),
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'facility_id' => $schema->integer()->required()->description('The ID of the facility to get details for'),
        ];
    }
}
