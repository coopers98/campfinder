<?php

namespace App\Services;

use App\Models\Camp;
use Illuminate\Support\Collection;

class CampMatcher
{
    protected array $summerWeeks = [
        '2026-06-15', '2026-06-22', '2026-06-29',
        '2026-07-06', '2026-07-13', '2026-07-20', '2026-07-27',
        '2026-08-03', '2026-08-10', '2026-08-17',
    ];

    protected array $weekLabels = [
        '2026-06-15' => 'Week 1: Jun 15-19',
        '2026-06-22' => 'Week 2: Jun 22-26',
        '2026-06-29' => 'Week 3: Jun 29 - Jul 3',
        '2026-07-06' => 'Week 4: Jul 6-10',
        '2026-07-13' => 'Week 5: Jul 13-17',
        '2026-07-20' => 'Week 6: Jul 20-24',
        '2026-07-27' => 'Week 7: Jul 27-31',
        '2026-08-03' => 'Week 8: Aug 3-7',
        '2026-08-10' => 'Week 9: Aug 10-14',
        '2026-08-17' => 'Week 10: Aug 17-21',
    ];

    /**
     * Build a shortlist of top camp candidates for each child for each week.
     */
    public function buildShortlist(array $parsedCriteria): array
    {
        $children = $parsedCriteria['children'];
        $borough = $parsedCriteria['borough'] ?? '';
        $budget = $parsedCriteria['budget_cents_per_week'] ?? 50000;
        $schedulePref = $parsedCriteria['schedule_preference'] ?? 'any';
        $preferSameFacility = $parsedCriteria['prefer_same_facility'] ?? false;

        $result = [];

        foreach ($children as $child) {
            $childShortlist = [];

            foreach ($this->summerWeeks as $weekStart) {
                $candidates = $this->findCandidates(
                    age: $child['age'],
                    categories: $child['categories'],
                    borough: $borough,
                    budget: $budget,
                    schedulePref: $schedulePref,
                    weekStart: $weekStart,
                );

                $childShortlist[] = [
                    'week_start' => $weekStart,
                    'week_label' => $this->weekLabels[$weekStart],
                    'candidates' => $candidates->take(3)->values()->toArray(),
                ];
            }

            $result[] = [
                'name' => $child['name'],
                'age' => $child['age'],
                'categories' => $child['categories'],
                'weeks' => $childShortlist,
            ];
        }

        // If multiple children and prefer same facility, find shared facility opportunities
        $facilityOverlaps = [];
        if ($preferSameFacility && count($children) > 1) {
            $facilityOverlaps = $this->findFacilityOverlaps($children, $borough, $budget);
        }

        return [
            'children' => $result,
            'facility_overlaps' => $facilityOverlaps,
        ];
    }

    protected function findCandidates(int $age, array $categories, string $borough, int $budget, string $schedulePref, string $weekStart): Collection
    {
        $query = Camp::with('facility')
            ->forAge($age)
            ->inWeek($weekStart)
            ->where('price_cents', '<=', $budget);

        if ($schedulePref !== 'any') {
            $query->where('schedule_type', $schedulePref);
        }

        if ($borough) {
            // Prefer the borough but don't exclude others — we'll score them
            $camps = $query->get();
        } else {
            $camps = $query->get();
        }

        // Score and rank
        return $camps->map(function (Camp $camp) use ($categories, $borough) {
            $score = 0;

            // Category match: primary interest = 30pts, secondary = 20pts, general always gets 10pts
            if (in_array($camp->category, $categories)) {
                $score += 30;
                if ($camp->category === ($categories[0] ?? '')) {
                    $score += 10; // Bonus for primary interest
                }
            } elseif ($camp->category === 'general') {
                $score += 10;
            }

            // Borough match
            if ($borough && $camp->facility->borough === $borough) {
                $score += 20;
            }

            // Availability
            if ($camp->availability_status === 'available') {
                $score += 15;
            } elseif ($camp->availability_status === 'almost_full') {
                $score += 8;
            }
            // Waitlisted = 0 bonus

            // Full day preferred slightly
            if ($camp->schedule_type === 'full_day') {
                $score += 5;
            }

            // Lunch bonus
            if ($camp->lunch_provided) {
                $score += 3;
            }

            return [
                'id' => $camp->id,
                'name' => $camp->name,
                'facility_id' => $camp->facility_id,
                'facility_name' => $camp->facility->name,
                'borough' => $camp->facility->borough,
                'neighborhood' => $camp->facility->neighborhood,
                'category' => $camp->category,
                'ages' => "{$camp->age_min}-{$camp->age_max}",
                'schedule_type' => $camp->schedule_type,
                'price_cents' => $camp->price_cents,
                'availability_status' => $camp->availability_status,
                'spots_remaining' => $camp->is_full ? 0 : $camp->spots_remaining,
                'waitlist_count' => $camp->waitlist_count,
                'lunch_provided' => $camp->lunch_provided,
                'score' => $score,
            ];
        })->sortByDesc('score');
    }

    /**
     * Build the full recommendation plan from a shortlist (picks primary + alternative per week).
     */
    public function buildPlan(array $shortlist): array
    {
        $children = [];
        $totalCost = 0;

        foreach ($shortlist['children'] as $child) {
            $weeks = [];

            foreach ($child['weeks'] as $week) {
                $candidates = $week['candidates'];
                $primary = $candidates[0] ?? null;
                $alternative = $candidates[1] ?? null;

                if ($primary) {
                    $totalCost += $primary['price_cents'];
                }

                $weeks[] = [
                    'week_start' => $week['week_start'],
                    'week_label' => $week['week_label'],
                    'primary_recommendation' => $primary ? $this->formatRecommendation($primary) : null,
                    'alternative' => $alternative ? $this->formatRecommendation($alternative) : null,
                ];
            }

            $children[] = [
                'name' => $child['name'],
                'age' => $child['age'],
                'summary' => $this->generateChildSummary($child),
                'weeks' => $weeks,
            ];
        }

        // Build sibling overlaps in the expected format
        $siblingOverlaps = [];
        foreach ($shortlist['facility_overlaps'] as $overlap) {
            foreach ($overlap['shared_facilities'] as $facility) {
                $siblingOverlaps[] = [
                    'week_start' => $overlap['week_start'],
                    'facility_name' => $facility['name'],
                    'children_names' => array_map(fn ($c) => $c['name'], $shortlist['children']),
                ];
            }
        }

        return [
            'children' => $children,
            'sibling_overlaps' => $siblingOverlaps,
            'total_estimated_cost_cents' => $totalCost,
            'notes' => $this->generateNotes($children, $siblingOverlaps),
        ];
    }

    protected function formatRecommendation(array $candidate): array
    {
        return [
            'camp_id' => $candidate['id'],
            'camp_name' => $candidate['name'],
            'facility_name' => $candidate['facility_name'],
            'facility_id' => $candidate['facility_id'],
            'category' => $candidate['category'],
            'price_cents' => $candidate['price_cents'],
            'schedule_type' => $candidate['schedule_type'],
            'availability_status' => $candidate['availability_status'],
            'spots_remaining' => $candidate['spots_remaining'],
            'waitlist_count' => $candidate['waitlist_count'],
            'lunch_provided' => $candidate['lunch_provided'],
            'reason' => $this->generateReason($candidate),
        ];
    }

    protected function generateReason(array $candidate): string
    {
        $parts = [];

        if ($candidate['score'] >= 50) {
            $parts[] = 'Excellent match';
        } elseif ($candidate['score'] >= 35) {
            $parts[] = 'Strong match';
        } else {
            $parts[] = 'Good option';
        }

        $parts[] = strtolower(str_replace('_', ' ', $candidate['category'])) . ' camp';

        if ($candidate['availability_status'] === 'available') {
            $parts[] = 'spots available';
        } elseif ($candidate['availability_status'] === 'almost_full') {
            $parts[] = 'filling up fast';
        } else {
            $parts[] = 'waitlist only';
        }

        if ($candidate['lunch_provided']) {
            $parts[] = 'lunch included';
        }

        return ucfirst(implode(' — ', $parts)) . '.';
    }

    protected function generateChildSummary(array $child): string
    {
        $categories = collect($child['weeks'])
            ->pluck('candidates')
            ->flatten(1)
            ->take(10)
            ->pluck('category')
            ->countBy()
            ->sortDesc()
            ->keys()
            ->take(3)
            ->map(fn ($c) => str_replace('_', ' ', $c))
            ->join(', ');

        return "{$child['name']} (age {$child['age']}) has a summer plan focused on {$categories} camps in their preferred area.";
    }

    protected function generateNotes(array $children, array $siblingOverlaps): string
    {
        $notes = [];

        if (count($children) > 1 && count($siblingOverlaps) > 0) {
            $overlapWeeks = count(collect($siblingOverlaps)->pluck('week_start')->unique());
            $notes[] = "Siblings can attend the same facility in {$overlapWeeks} of 10 weeks.";
        }

        $notes[] = 'Recommendations are scored by category fit, location, availability, and amenities.';
        $notes[] = 'Book early for camps marked as "almost full."';

        return implode(' ', $notes);
    }

    /**
     * Find facilities that have camps for multiple age groups in the same week.
     */
    protected function findFacilityOverlaps(array $children, string $borough, int $budget): array
    {
        $overlaps = [];

        foreach ($this->summerWeeks as $weekStart) {
            // For each facility, check if it has age-appropriate camps for ALL children
            $facilityCoverage = [];

            foreach ($children as $childIdx => $child) {
                $query = Camp::with('facility')
                    ->forAge($child['age'])
                    ->inWeek($weekStart)
                    ->where('price_cents', '<=', $budget);

                if ($borough) {
                    $query->inBorough($borough);
                }

                $camps = $query->get();

                foreach ($camps as $camp) {
                    $fid = $camp->facility_id;
                    if (!isset($facilityCoverage[$fid])) {
                        $facilityCoverage[$fid] = [
                            'facility_name' => $camp->facility->name,
                            'borough' => $camp->facility->borough,
                            'children_covered' => [],
                        ];
                    }
                    $facilityCoverage[$fid]['children_covered'][$childIdx] = true;
                }
            }

            // Keep facilities that cover ALL children
            $sharedFacilities = array_filter($facilityCoverage, fn ($f) => count($f['children_covered']) === count($children));

            if (!empty($sharedFacilities)) {
                $overlaps[] = [
                    'week_start' => $weekStart,
                    'week_label' => $this->weekLabels[$weekStart],
                    'shared_facilities' => array_map(fn ($f) => [
                        'name' => $f['facility_name'],
                        'borough' => $f['borough'],
                    ], array_values($sharedFacilities)),
                ];
            }
        }

        return $overlaps;
    }
}
