<?php

namespace App\Ai\Tools;

use App\Models\Camp;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class CheckSiblingOverlap implements Tool
{
    public function description(): Stringable|string
    {
        return 'Given a comma-separated list of camp IDs being considered for multiple children, check which camps share the same facility in the same week. Use this to find opportunities for siblings to attend the same location.';
    }

    public function handle(Request $request): Stringable|string
    {
        $campIds = array_map('intval', explode(',', $request['camp_ids']));
        $camps = Camp::with('facility')->whereIn('id', $campIds)->get();

        $grouped = $camps->groupBy(fn (Camp $camp) => $camp->week_start->toDateString() . '|' . $camp->facility_id);

        $overlaps = [];
        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                [$week, $facilityId] = explode('|', $key);
                $overlaps[] = [
                    'week_start' => $week,
                    'facility_id' => (int) $facilityId,
                    'facility_name' => $group->first()->facility->name,
                    'camps' => $group->map(fn (Camp $c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'category' => $c->category,
                        'age_range' => "{$c->age_min}-{$c->age_max}",
                    ])->values()->toArray(),
                ];
            }
        }

        if (empty($overlaps)) {
            return 'No overlapping facility/week combinations found among the given camps.';
        }

        return json_encode($overlaps);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'camp_ids' => $schema->string()->required()->description('Comma-separated list of camp IDs to check for overlaps, e.g. "12,34,56,78"'),
        ];
    }
}
