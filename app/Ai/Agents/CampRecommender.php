<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\MaxTokens;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Attributes\Temperature;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-4o-mini')]
#[MaxTokens(16384)]
#[Temperature(0.3)]
#[Timeout(120)]
class CampRecommender implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
You are CampFinder AI, a helpful and enthusiastic summer camp advisor for NYC families.

You will receive a pre-built shortlist of the top camp candidates for each child for each week of summer 2026. Your job is to select the BEST option per week and provide a brief reason for each pick.

RULES:
1. Pick ONE primary recommendation per week per child from the candidates provided.
2. Pick ONE alternative per week (a different camp from the candidates) when possible.
3. Prefer camps with higher scores — they already factor in category match, location, availability, and price.
4. If siblings are mentioned, check the facility_overlaps data and prefer placing siblings at the same facility when a good option exists.
5. Vary categories across weeks for a diverse summer experience, unless the child has a single strong interest.
6. Note availability: "available" is best, "almost_full" means act fast, "waitlisted" means backup plan needed.
7. It's OK to leave a week empty (null) if no candidate scores well or all are waitlisted.
8. Write a short per-child summary of the overall plan.
9. Calculate total estimated cost from your primary picks.
10. Keep reasons concise — one sentence each.
INSTRUCTIONS;
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
                    'primary_recommendation' => $schema->object($recommendationSchema)->nullable()->required()->withoutAdditionalProperties(),
                    'alternative' => $schema->object($recommendationSchema)->nullable()->required()->withoutAdditionalProperties(),
                ])->withoutAdditionalProperties())->required(),
            ])->withoutAdditionalProperties())->required(),
            'sibling_overlaps' => $schema->array()->items($schema->object([
                'week_start' => $schema->string()->required(),
                'facility_name' => $schema->string()->required(),
                'children_names' => $schema->array()->items($schema->string())->required(),
            ])->withoutAdditionalProperties())->required(),
            'total_estimated_cost_cents' => $schema->integer()->required(),
            'notes' => $schema->string()->required(),
        ];
    }
}
