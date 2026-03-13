<?php

namespace Database\Seeders;

use App\Models\Camp;
use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class CampSeeder extends Seeder
{
    protected array $summerWeeks = [
        '2026-06-15', '2026-06-22', '2026-06-29',
        '2026-07-06', '2026-07-13', '2026-07-20', '2026-07-27',
        '2026-08-03', '2026-08-10', '2026-08-17',
    ];

    protected array $campTemplates = [
        'sports' => [
            'names' => ['Junior Soccer Stars', 'Basketball Bootcamp', 'Swim & Splash', 'Gymnastics Galaxy', 'Martial Arts Masters', 'All-Star Sports', 'Tennis Aces', 'Track & Field Fun'],
            'descriptions' => [
                'High-energy sports program with professional coaching. Kids build skills, teamwork, and confidence through drills, scrimmages, and fun competitions.',
                'An action-packed week of athletics designed to develop coordination, sportsmanship, and a love of movement. All skill levels welcome.',
                'From warm-up stretches to championship games, your child will stay active and engaged all day. Water and snacks provided.',
            ],
            'age_groups' => [[4, 6], [5, 8], [7, 10], [8, 12], [10, 14]],
            'price_range' => [35000, 75000], // $350-$750
        ],
        'arts' => [
            'names' => ['Little Picassos', 'Clay & Create', 'Mixed Media Makers', 'Watercolor Wonders', 'Sculpture Studio', 'Art Around the World'],
            'descriptions' => [
                'A colorful week of painting, drawing, sculpting, and mixed media projects. Each child creates a portfolio of work to take home.',
                'Explore famous artists and art movements while creating your own masterpieces. All materials included.',
                'Hands-on art camp where creativity flows freely. Kids experiment with clay, paint, textiles, and found objects.',
            ],
            'age_groups' => [[3, 5], [5, 8], [6, 10], [8, 12]],
            'price_range' => [30000, 60000],
        ],
        'performing_arts' => [
            'names' => ['Broadway Bound', 'Dance Fusion', 'Rock Band Camp', 'Improv & Comedy', 'Film Makers Club', 'Musical Theater Magic'],
            'descriptions' => [
                'Lights, camera, action! Kids rehearse and perform an original show by week\'s end. Acting, singing, and dancing in a supportive environment.',
                'From hip-hop to ballet, jazz to contemporary — dancers of all levels learn choreography and perform in a Friday showcase.',
                'Young performers build confidence and creativity through theater games, scene work, and musical numbers.',
            ],
            'age_groups' => [[5, 8], [7, 11], [8, 12], [10, 14]],
            'price_range' => [35000, 70000],
        ],
        'stem' => [
            'names' => ['Code Creators', 'Robotics Workshop', 'Science Explorers', 'Game Design Lab', 'Engineering Challenge', 'Digital Art & Animation'],
            'descriptions' => [
                'Build, code, and experiment! Hands-on STEM projects from building robots to launching rockets. No experience needed.',
                'Kids learn real programming concepts through game design and interactive projects. Each camper takes home their digital creation.',
                'A week of mind-blowing experiments, engineering challenges, and scientific discovery. Lab coats provided!',
            ],
            'age_groups' => [[6, 9], [7, 10], [8, 12], [10, 14], [11, 15]],
            'price_range' => [40000, 80000],
        ],
        'nature' => [
            'names' => ['Urban Nature Rangers', 'Garden Explorers', 'Eco Adventures', 'Wildlife Watchers', 'Farm to Table Kids'],
            'descriptions' => [
                'Discover the nature hidden in NYC! Park hikes, bird watching, gardening, and nature journaling. Sunscreen and curiosity required.',
                'Kids get their hands dirty planting, harvesting, and learning about urban ecology. Outdoor adventures rain or shine.',
                'An immersive outdoor experience — from pond exploration to tree identification, young naturalists will see the city in a new way.',
            ],
            'age_groups' => [[4, 7], [5, 8], [6, 10], [8, 12]],
            'price_range' => [25000, 55000],
        ],
        'academic' => [
            'names' => ['Reading Rockets', 'Math Wizards', 'Creative Writing Workshop', 'World Languages Fun', 'Chess Champions', 'Young Authors Club'],
            'descriptions' => [
                'Make learning fun! Engaging academic enrichment through games, projects, and collaborative activities. Perfect for keeping skills sharp over summer.',
                'Brain-boosting camp that combines critical thinking challenges with creative projects. Kids don\'t even realize they\'re learning!',
                'Interactive academic camp blending literacy, math games, and creative expression. Small groups ensure personalized attention.',
            ],
            'age_groups' => [[5, 7], [6, 9], [7, 10], [8, 12], [10, 14]],
            'price_range' => [25000, 55000],
        ],
        'general' => [
            'names' => ['Summer Fun Camp', 'Adventure Day Camp', 'Campers\' Choice', 'Best Week Ever', 'All-Around Camp'],
            'descriptions' => [
                'The classic day camp experience — sports, arts, games, field trips, and new friendships. A different theme every day!',
                'A little bit of everything! Swimming, crafts, team games, and special visitors make every day an adventure.',
                'Our signature multi-activity camp keeps kids engaged with a rotating schedule of sports, art, music, and outdoor play.',
            ],
            'age_groups' => [[3, 5], [4, 7], [5, 9], [6, 10], [8, 12], [10, 14]],
            'price_range' => [30000, 65000],
        ],
    ];

    protected array $facilityCategoryMap = [
        'NYC Kids Academy' => ['sports', 'arts', 'general', 'stem'],
        'East Side Explorers' => ['stem', 'nature'],
        'Chelsea Creative Arts' => ['arts'],
        'Tribeca Youth Center' => ['sports', 'performing_arts', 'general', 'academic'],
        'Midtown Movers Sports Club' => ['sports', 'general'],
        'Village Theater Kids' => ['performing_arts'],
        'Slope Sports & Arts' => ['sports', 'arts', 'nature', 'general'],
        'Williamsburg Innovation Lab' => ['stem', 'arts'],
        'DUMBO Discovery Camp' => ['nature', 'arts'],
        'Heights Academy' => ['academic', 'general'],
        'Cobble Hill Kids Club' => ['arts', 'general'],
        'Bushwick Beats & Moves' => ['performing_arts', 'arts'],
        'Astoria Adventure Camp' => ['general', 'sports', 'nature', 'arts'],
        'LIC Tech Camp' => ['stem'],
        'Forest Hills Family Center' => ['sports', 'general'],
        'Riverdale Nature Camp' => ['nature', 'academic'],
        'Pelham Bay Sports Complex' => ['sports', 'general', 'nature'],
        'St. George Creative Campus' => ['performing_arts', 'arts'],
    ];

    protected array $facilitySizeWeeks = [
        'small' => [4, 6],   // 4-6 weeks of camp
        'medium' => [7, 9],  // 7-9 weeks
        'large' => [10, 10], // all 10 weeks
    ];

    public function run(): void
    {
        $facilities = Facility::all();

        foreach ($facilities as $facility) {
            $categories = $this->facilityCategoryMap[$facility->name] ?? ['general'];
            $size = $this->getFacilitySize($facility, $categories);
            $weekRange = $this->facilitySizeWeeks[$size];
            $numWeeks = rand($weekRange[0], $weekRange[1]);

            // Pick which weeks this facility runs
            $activeWeeks = $this->pickActiveWeeks($numWeeks, $size);

            foreach ($activeWeeks as $weekStart) {
                $weekEnd = Carbon::parse($weekStart)->addDays(4)->toDateString();

                // Determine how many camps this week based on size
                $campsThisWeek = match ($size) {
                    'small' => 1,
                    'medium' => rand(1, 2),
                    'large' => rand(2, 4),
                };

                // Pick categories for this week
                $weekCategories = collect($categories)->shuffle()->take($campsThisWeek)->values();

                foreach ($weekCategories as $category) {
                    $this->createCamp($facility, $category, $weekStart, $weekEnd);
                }
            }
        }
    }

    protected function getFacilitySize(Facility $facility, array $categories): string
    {
        $count = count($categories);
        if ($count <= 1) {
            return 'small';
        }
        if ($count <= 2) {
            return 'medium';
        }
        return 'large';
    }

    protected function pickActiveWeeks(int $numWeeks, string $size): array
    {
        if ($numWeeks >= 10) {
            return $this->summerWeeks;
        }

        // For smaller facilities, pick contiguous or near-contiguous blocks
        if ($size === 'small') {
            $start = rand(0, count($this->summerWeeks) - $numWeeks);
            return array_slice($this->summerWeeks, $start, $numWeeks);
        }

        // Medium: mostly consecutive with maybe a gap
        $weeks = $this->summerWeeks;
        shuffle($weeks);
        $selected = array_slice($weeks, 0, $numWeeks);
        sort($selected);
        return $selected;
    }

    protected function createCamp(Facility $facility, string $category, string $weekStart, string $weekEnd): void
    {
        $template = $this->campTemplates[$category];
        $name = $template['names'][array_rand($template['names'])];
        $description = $template['descriptions'][array_rand($template['descriptions'])];
        $ageGroup = $template['age_groups'][array_rand($template['age_groups'])];

        // Schedule type
        $scheduleType = $this->pickScheduleType();
        [$startTime, $endTime] = $this->getScheduleTimes($scheduleType);

        // Price based on schedule and category
        $basePrice = rand($template['price_range'][0], $template['price_range'][1]);
        if ($scheduleType !== 'full_day') {
            $basePrice = (int) ($basePrice * 0.6);
        }
        // Round to nearest $25
        $priceCents = (int) (round($basePrice / 2500) * 2500);

        // Capacity and enrollment
        $capacity = $this->pickCapacity($category);
        [$enrolled, $waitlistCount] = $this->pickEnrollment($capacity);

        // Lunch: inherit from facility unless half-day
        $lunchProvided = $scheduleType === 'full_day' && $facility->lunch_provided;

        $weekLabel = Carbon::parse($weekStart)->format('M d');
        $slug = Str::slug("{$facility->slug}-{$name}-{$weekLabel}") . '-' . Str::random(4);

        Camp::create([
            'facility_id' => $facility->id,
            'name' => $name,
            'slug' => $slug,
            'category' => $category,
            'description' => $description,
            'age_min' => $ageGroup[0],
            'age_max' => $ageGroup[1],
            'week_start' => $weekStart,
            'week_end' => $weekEnd,
            'schedule_type' => $scheduleType,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'price_cents' => $priceCents,
            'capacity' => $capacity,
            'enrolled' => $enrolled,
            'waitlist_count' => $waitlistCount,
            'lunch_provided' => $lunchProvided,
            'image_url' => null,
        ]);
    }

    protected function pickScheduleType(): string
    {
        $roll = rand(1, 100);
        if ($roll <= 60) {
            return 'full_day';
        }
        if ($roll <= 80) {
            return 'half_day_am';
        }
        return 'half_day_pm';
    }

    protected function getScheduleTimes(string $scheduleType): array
    {
        return match ($scheduleType) {
            'full_day' => ['09:00', '15:00'],
            'half_day_am' => ['09:00', '12:00'],
            'half_day_pm' => ['13:00', '16:00'],
        };
    }

    protected function pickCapacity(string $category): int
    {
        // Smaller facilities and specialized camps have lower capacity
        return match ($category) {
            'arts', 'performing_arts' => rand(12, 20),
            'stem' => rand(10, 18),
            'nature' => rand(12, 22),
            'academic' => rand(10, 16),
            default => rand(18, 30),
        };
    }

    protected function pickEnrollment(int $capacity): array
    {
        $roll = rand(1, 100);

        if ($roll <= 60) {
            // Available: 40-85% full
            $enrolled = (int) ($capacity * (rand(40, 85) / 100));
            return [$enrolled, 0];
        }

        if ($roll <= 85) {
            // Almost full: capacity - 1 to 3 spots
            $enrolled = $capacity - rand(1, 3);
            return [max(0, $enrolled), 0];
        }

        // Full with waitlist
        $waitlist = rand(1, 8);
        return [$capacity, $waitlist];
    }
}
