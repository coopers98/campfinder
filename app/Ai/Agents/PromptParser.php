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
#[MaxTokens(1024)]
#[Temperature(0)]
#[Timeout(15)]
class PromptParser implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): Stringable|string
    {
        return <<<'INSTRUCTIONS'
Extract structured information from a parent's request about summer camps for their children.

Parse the free text and identify:
- Each child mentioned (name or label, age, interests/preferred categories)
- Location preference (borough or neighborhood)
- Budget per week per child (in cents, e.g. $400 = 40000)
- Schedule preference (full_day, half_day_am, half_day_pm, or any)
- Whether siblings should be at the same facility

CATEGORY MAPPING (map interests to these exact values):
- sports: soccer, basketball, swimming, gymnastics, tennis, athletic, sporty, baseball, lacrosse, volleyball
- arts: art, painting, drawing, sculpture, craft, creative, visual arts, pottery, ceramics
- performing_arts: theater, dance, music, acting, singing, drama, performance, broadway, film, improv
- stem: coding, robotics, science, technology, engineering, math, computers, programming, minecraft, 3d printing
- nature: outdoors, nature, garden, hiking, ecology, environmental, birds, plants, wilderness
- academic: reading, writing, math, language, chess, learning, tutoring, enrichment, debate, history
- martial_arts: karate, judo, taekwondo, kung fu, martial arts, self-defense, jiu-jitsu, kickboxing, mma, ninja
- equestrian: horses, horseback riding, equestrian, ponies, riding lessons, stables
- pets: animals, pets, dogs, cats, veterinary, vet, animal care, reptiles, zoo, farm animals
- general: everything, all-around, mix, variety, day camp, fun

If a child has no specific interest, use ["general"].
If no budget specified, use 50000 (i.e. $500).
If no borough specified, leave as empty string.
If no name given, use "Child 1", "Child 2", etc.
Default prefer_same_facility to true when multiple children are mentioned.
INSTRUCTIONS;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'children' => $schema->array()->items($schema->object([
                'name' => $schema->string()->required(),
                'age' => $schema->integer()->required(),
                'categories' => $schema->array()->items($schema->string())->required(),
            ])->withoutAdditionalProperties())->required(),
            'borough' => $schema->string()->required(),
            'budget_cents_per_week' => $schema->integer()->required(),
            'schedule_preference' => $schema->string()->required(),
            'prefer_same_facility' => $schema->boolean()->required(),
        ];
    }
}
