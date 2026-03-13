<?php

namespace App\Ai\Agents;

use App\Ai\Tools\CheckSiblingOverlap;
use App\Ai\Tools\GetFacilityDetails;
use App\Ai\Tools\SearchCamps;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Promptable;
use Stringable;

#[MaxSteps(20)]
#[MaxTokens(8192)]
#[Temperature(0.7)]
#[Timeout(120)]
class CampRecommender implements Agent, HasStructuredOutput, HasTools
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are CampFinder AI, a helpful and enthusiastic summer camp advisor for NYC families.

Given information about one or more children (ages, interests, location, budget, scheduling preferences), recommend the best camp options for each child across the 10 weeks of summer 2026 (June 15 - August 21).

SUMMER WEEKS:
- Week 1: Jun 15-19
- Week 2: Jun 22-26
- Week 3: Jun 29 - Jul 3
- Week 4: Jul 6-10
- Week 5: Jul 13-17
- Week 6: Jul 20-24
- Week 7: Jul 27-31
- Week 8: Aug 3-7
- Week 9: Aug 10-14
- Week 10: Aug 17-21

CAMP CATEGORIES: sports, arts, performing_arts, stem, nature, academic, general

PRIORITIZE:
1. Age-appropriate camps (child's age must be within the camp's age_min to age_max range)
2. Matching interests/categories to the child's stated preferences
3. Budget constraints (price per week)
4. Geographic preference (prefer camps in the stated borough/neighborhood when possible)
5. Sibling coordination — actively try to place siblings at the SAME FACILITY in the same week when possible. Use GetFacilityDetails to check what camps a facility offers across age ranges.
6. Availability — prefer camps with open spots. Still recommend waitlisted camps if they're a great fit, but note the waitlist status.
7. Variety — try to mix categories across weeks so the child gets diverse experiences, unless they specifically want one category.

PROCESS:
1. Parse the user's message to identify each child (name/label, age, interests) and constraints (budget, location, preferences)
2. For each child, search for camps matching their age and preferred categories
3. If multiple children, use GetFacilityDetails to find facilities with camps suitable for different ages in the same week
4. Use CheckSiblingOverlap to verify sibling placement opportunities
5. Build a full summer plan for each child
6. It's OK to leave some weeks empty if no great match exists — don't force bad recommendations
7. Provide a primary recommendation and one alternative per week when possible

If the user doesn't specify a name for a child, use "Child 1", "Child 2", etc.
If no budget is specified, aim for mid-range options ($400-600/week for full day).
If no location preference, start with Manhattan and Brooklyn options.
INSTRUCTIONS;
    }

    public function tools(): iterable
    {
        return [
            new SearchCamps,
            new GetFacilityDetails,
            new CheckSiblingOverlap,
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        $recommendationSchema = [
            'camp_id' => $schema->integer()->required(),
            'camp_name' => $schema->string()->required(),
            'facility_name' => $schema->string()->required(),
            'facility_id' => $schema->integer()->required(),
            'category' => $schema->string()->required(),
            'price_cents' => $schema->integer()->required(),
            'schedule_type' => $schema->string()->required(),
            'availability_status' => $schema->string()->required(),
            'spots_remaining' => $schema->integer()->required(),
            'waitlist_count' => $schema->integer()->required(),
            'lunch_provided' => $schema->boolean()->required(),
            'reason' => $schema->string()->required(),
        ];

        return [
            'children' => $schema->array()->items($schema->object([
                'name' => $schema->string()->required(),
                'age' => $schema->integer()->required(),
                'summary' => $schema->string()->required(),
                'weeks' => $schema->array()->items($schema->object([
                    'week_start' => $schema->string()->required(),
                    'week_label' => $schema->string()->required(),
                    'primary_recommendation' => $schema->object($recommendationSchema)->nullable(),
                    'alternative' => $schema->object($recommendationSchema)->nullable(),
                ]))->required(),
            ]))->required(),
            'sibling_overlaps' => $schema->array()->items($schema->object([
                'week_start' => $schema->string()->required(),
                'facility_name' => $schema->string()->required(),
                'children_names' => $schema->array()->items($schema->string())->required(),
            ]))->required(),
            'total_estimated_cost_cents' => $schema->integer()->required(),
            'notes' => $schema->string()->required(),
        ];
    }
}
