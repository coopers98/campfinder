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
            'names' => [
                'Junior Soccer Stars', 'Basketball Bootcamp', 'Swim & Splash',
                'Gymnastics Galaxy', 'Martial Arts Masters', 'All-Star Sports',
                'Tennis Aces', 'Track & Field Fun', 'Flag Football Frenzy',
                'Volleyball Camp', 'Multi-Sport Mania', 'Kickball & Beyond',
            ],
            'descriptions' => [
                'High-energy sports program with professional coaching. Kids build skills, teamwork, and confidence through drills, scrimmages, and fun competitions.',
                'An action-packed week of athletics designed to develop coordination, sportsmanship, and a love of movement. All skill levels welcome.',
                'From warm-up stretches to championship games, your child will stay active and engaged all day. Water and snacks provided.',
                'Our coaches create a positive, encouraging environment where every child improves. End-of-week mini tournament for families to watch!',
            ],
        ],
        'arts' => [
            'names' => [
                'Little Picassos', 'Clay & Create', 'Mixed Media Makers',
                'Watercolor Wonders', 'Sculpture Studio', 'Art Around the World',
                'Printmaking Workshop', 'Collage & Color', 'Drawing Intensive',
                'Fiber Arts & Weaving', 'Pottery Camp', 'Street Art Studio',
            ],
            'descriptions' => [
                'A colorful week of painting, drawing, sculpting, and mixed media projects. Each child creates a portfolio of work to take home.',
                'Explore famous artists and art movements while creating your own masterpieces. All materials included.',
                'Hands-on art camp where creativity flows freely. Kids experiment with clay, paint, textiles, and found objects.',
                'Every day is a new medium and a new adventure. From watercolors to wire sculpture, your child will discover their creative voice.',
            ],
        ],
        'performing_arts' => [
            'names' => [
                'Broadway Bound', 'Dance Fusion', 'Rock Band Camp',
                'Improv & Comedy', 'Film Makers Club', 'Musical Theater Magic',
                'Hip-Hop Crew', 'Stage & Screen', 'Singing Stars',
                'Circus Arts & Acrobatics', 'Playwriting Lab', 'Comedy Sketch Camp',
            ],
            'descriptions' => [
                'Lights, camera, action! Kids rehearse and perform an original show by week\'s end. Acting, singing, and dancing in a supportive environment.',
                'From hip-hop to ballet, jazz to contemporary — dancers of all levels learn choreography and perform in a Friday showcase.',
                'Young performers build confidence and creativity through theater games, scene work, and musical numbers.',
                'Whether your child dreams of the stage or just loves to perform, this camp nurtures creativity, teamwork, and self-expression.',
            ],
        ],
        'stem' => [
            'names' => [
                'Code Creators', 'Robotics Workshop', 'Science Explorers',
                'Game Design Lab', 'Engineering Challenge', 'Digital Art & Animation',
                'Minecraft Modding', 'Space & Rocketry Camp', 'Chemistry Kitchen',
                'AI & Machine Learning Jr.', 'Electronics Tinkerers', '3D Printing Lab',
            ],
            'descriptions' => [
                'Build, code, and experiment! Hands-on STEM projects from building robots to launching rockets. No experience needed.',
                'Kids learn real programming concepts through game design and interactive projects. Each camper takes home their digital creation.',
                'A week of mind-blowing experiments, engineering challenges, and scientific discovery. Lab coats provided!',
                'Future innovators welcome! We make science and technology accessible, fun, and deeply engaging for curious kids.',
            ],
        ],
        'nature' => [
            'names' => [
                'Urban Nature Rangers', 'Garden Explorers', 'Eco Adventures',
                'Wildlife Watchers', 'Farm to Table Kids', 'Park Naturalists',
                'Bug Safari', 'Green Thumbs Camp', 'Outdoor Survival Skills',
                'Nature Art & Journaling', 'Birding & Beyond', 'Water Explorers',
            ],
            'descriptions' => [
                'Discover the nature hidden in NYC! Park hikes, bird watching, gardening, and nature journaling. Sunscreen and curiosity required.',
                'Kids get their hands dirty planting, harvesting, and learning about urban ecology. Outdoor adventures rain or shine.',
                'An immersive outdoor experience — from pond exploration to tree identification, young naturalists will see the city in a new way.',
                'We believe every kid should know what dirt feels like. Nature play, garden science, and animal encounters all week long.',
            ],
        ],
        'academic' => [
            'names' => [
                'Reading Rockets', 'Math Wizards', 'Creative Writing Workshop',
                'World Languages Fun', 'Chess Champions', 'Young Authors Club',
                'Debate & Public Speaking', 'History Detectives', 'Book Club Camp',
                'Puzzle Masters', 'Science of Cooking', 'Map Makers & Explorers',
            ],
            'descriptions' => [
                'Make learning fun! Engaging academic enrichment through games, projects, and collaborative activities. Perfect for keeping skills sharp over summer.',
                'Brain-boosting camp that combines critical thinking challenges with creative projects. Kids don\'t even realize they\'re learning!',
                'Interactive academic camp blending literacy, math games, and creative expression. Small groups ensure personalized attention.',
                'Summer slide? Not here. We keep brains buzzing with hands-on projects that feel like play but build real skills.',
            ],
        ],
        'general' => [
            'names' => [
                'Summer Fun Camp', 'Adventure Day Camp', 'Campers\' Choice',
                'Best Week Ever', 'All-Around Camp', 'Explorer Camp',
                'Ultimate Day Camp', 'Camp Kaleidoscope', 'Sunshine Camp',
                'Rainbow Week', 'Camp All-Stars', 'Summer Spectacular',
            ],
            'descriptions' => [
                'The classic day camp experience — sports, arts, games, field trips, and new friendships. A different theme every day!',
                'A little bit of everything! Swimming, crafts, team games, and special visitors make every day an adventure.',
                'Our signature multi-activity camp keeps kids engaged with a rotating schedule of sports, art, music, and outdoor play.',
                'Can\'t pick just one thing? Neither can we. This camp samples the best of sports, arts, STEM, and nature every single day.',
            ],
        ],
    ];

    // Age bands that ensure sibling overlap potential
    protected array $ageBands = [
        'tiny'    => [3, 5],
        'young'   => [4, 7],
        'kid'     => [5, 9],
        'tween'   => [7, 11],
        'preteen' => [8, 12],
        'teen'    => [10, 14],
        'wide'    => [5, 12],
    ];

    protected array $facilityCategoryMap = [
        // Manhattan
        'NYC Kids Academy'          => ['sports', 'arts', 'general', 'stem'],
        'East Side Explorers'       => ['stem', 'nature', 'academic'],
        'Chelsea Creative Arts'     => ['arts', 'performing_arts'],
        'Tribeca Youth Center'      => ['sports', 'performing_arts', 'general', 'academic'],
        'Midtown Movers Sports Club'=> ['sports', 'general'],
        'Village Theater Kids'      => ['performing_arts', 'arts'],
        'Harlem Youth Arts'         => ['arts', 'performing_arts', 'general'],
        'Flatiron STEM Academy'     => ['stem', 'academic', 'arts'],
        // Brooklyn
        'Slope Sports & Arts'       => ['sports', 'arts', 'nature', 'general'],
        'Williamsburg Innovation Lab'=> ['stem', 'arts', 'academic'],
        'DUMBO Discovery Camp'      => ['nature', 'arts', 'general'],
        'Heights Academy'           => ['academic', 'general', 'arts'],
        'Cobble Hill Kids Club'     => ['arts', 'general', 'performing_arts'],
        'Bushwick Beats & Moves'    => ['performing_arts', 'arts', 'general'],
        'Bay Ridge Community Camp'  => ['sports', 'arts', 'general', 'academic'],
        'Crown Heights Creative'    => ['arts', 'performing_arts'],
        'Prospect Park Alliance Camp'=> ['nature', 'general', 'arts'],
        // Queens
        'Astoria Adventure Camp'    => ['general', 'sports', 'nature', 'arts'],
        'LIC Tech Camp'             => ['stem', 'academic'],
        'Forest Hills Family Center'=> ['sports', 'general', 'nature'],
        'Jackson Heights Global Camp'=> ['academic', 'arts', 'general'],
        'Flushing Meadows Sports Academy' => ['sports', 'general', 'stem', 'nature'],
        // Bronx
        'Riverdale Nature Camp'     => ['nature', 'academic', 'arts'],
        'Pelham Bay Sports Complex' => ['sports', 'general', 'nature'],
        // Staten Island
        'St. George Creative Campus'=> ['performing_arts', 'arts', 'general'],
    ];

    protected array $facilitySize = [
        // Large: true businesses, all 10 weeks, no breaks
        'NYC Kids Academy'          => 'large',
        'Tribeca Youth Center'      => 'large',
        'Slope Sports & Arts'       => 'large',
        'Astoria Adventure Camp'    => 'large',
        'Pelham Bay Sports Complex' => 'large',
        'Bay Ridge Community Camp'  => 'large',
        'Flushing Meadows Sports Academy' => 'large',
        // Medium: 9-10 weeks, maybe 1 week off
        'East Side Explorers'       => 'medium',
        'Midtown Movers Sports Club'=> 'medium',
        'Williamsburg Innovation Lab'=> 'medium',
        'Heights Academy'           => 'medium',
        'Bushwick Beats & Moves'    => 'medium',
        'Forest Hills Family Center'=> 'medium',
        'Riverdale Nature Camp'     => 'medium',
        'St. George Creative Campus'=> 'medium',
        'Harlem Youth Arts'         => 'medium',
        'Flatiron STEM Academy'     => 'medium',
        'Prospect Park Alliance Camp'=> 'medium',
        'Jackson Heights Global Camp'=> 'medium',
        // Small: owner-operated, 9 weeks with max 1 week off
        'Chelsea Creative Arts'     => 'small',
        'Village Theater Kids'      => 'small',
        'DUMBO Discovery Camp'      => 'small',
        'Cobble Hill Kids Club'     => 'small',
        'LIC Tech Camp'             => 'small',
        'Crown Heights Creative'    => 'small',
    ];

    // Price tiers by category (full day, in cents)
    protected array $categoryPriceRanges = [
        'sports'          => [35000, 70000],
        'arts'            => [30000, 60000],
        'performing_arts' => [35000, 65000],
        'stem'            => [40000, 80000],
        'nature'          => [25000, 55000],
        'academic'        => [28000, 55000],
        'general'         => [30000, 60000],
    ];

    // Capacity ranges by category
    protected array $categoryCapacity = [
        'sports'          => [18, 30],
        'arts'            => [12, 22],
        'performing_arts' => [12, 20],
        'stem'            => [10, 18],
        'nature'          => [14, 24],
        'academic'        => [10, 16],
        'general'         => [20, 35],
    ];

    public function run(): void
    {
        $facilities = Facility::all();

        foreach ($facilities as $facility) {
            $name = $facility->name;
            $categories = $this->facilityCategoryMap[$name] ?? ['general'];
            $size = $this->facilitySize[$name] ?? 'medium';
            $activeWeeks = $this->getActiveWeeks($size);

            foreach ($activeWeeks as $weekStart) {
                $weekEnd = Carbon::parse($weekStart)->addDays(4)->toDateString();
                $this->createWeekCamps($facility, $categories, $size, $weekStart, $weekEnd);
            }
        }
    }

    protected function getActiveWeeks(string $size): array
    {
        if ($size === 'large') {
            // True businesses: all 10 weeks, no exceptions
            return $this->summerWeeks;
        }

        if ($size === 'medium') {
            // 50% chance of taking 1 week off (often July 4th week)
            if (rand(1, 100) <= 50) {
                $skipIndex = rand(1, 100) <= 70 ? 2 : rand(0, 9); // 70% chance it's week 3 (Jun 29, July 4th week)
                return array_values(array_filter($this->summerWeeks, fn ($k) => $k !== $skipIndex, ARRAY_FILTER_USE_KEY));
            }
            return $this->summerWeeks;
        }

        // Small: owner-operated, always takes exactly 1 week off
        $skipIndex = rand(1, 100) <= 60 ? 2 : rand(0, 9); // 60% skip July 4th week
        return array_values(array_filter($this->summerWeeks, fn ($k) => $k !== $skipIndex, ARRAY_FILTER_USE_KEY));
    }

    protected function createWeekCamps(Facility $facility, array $categories, string $size, string $weekStart, string $weekEnd): void
    {
        // Determine how many camps this facility runs each week
        // Large: run ALL categories every week, with multiple age bands per category
        // Medium: run all categories, 1 age band each
        // Small: run all categories, 1 age band each

        if ($size === 'large') {
            // Every category every week, each with 2-3 age bands
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                // Large facilities offer 2-3 age bands per category
                $numBands = min(count($ageBandsForCategory), rand(2, 3));
                $selectedBands = array_slice($ageBandsForCategory, 0, $numBands);

                foreach ($selectedBands as $band) {
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, 'full_day');
                }

                // 30% chance of also offering a half-day option for the youngest band
                if (rand(1, 100) <= 30 && $selectedBands[0][0] <= 5) {
                    $halfType = rand(0, 1) ? 'half_day_am' : 'half_day_pm';
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $selectedBands[0], $halfType);
                }
            }
        } elseif ($size === 'medium') {
            // All categories every active week, 1-2 age bands each
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                $numBands = min(count($ageBandsForCategory), rand(1, 2));
                $selectedBands = array_slice($ageBandsForCategory, 0, $numBands);

                foreach ($selectedBands as $band) {
                    $scheduleType = $this->pickScheduleType(75); // 75% full day
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, $scheduleType);
                }
            }
        } else {
            // Small: all categories, 1 age band each, mix of schedules
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                $band = $ageBandsForCategory[array_rand($ageBandsForCategory)];
                $scheduleType = $this->pickScheduleType(65); // 65% full day
                $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, $scheduleType);

                // 40% chance small facilities also run a second age band for their primary category
                if ($category === $categories[0] && rand(1, 100) <= 40) {
                    $otherBands = array_filter($ageBandsForCategory, fn ($b) => $b !== $band);
                    if (!empty($otherBands)) {
                        $secondBand = $otherBands[array_rand($otherBands)];
                        $this->createCamp($facility, $category, $weekStart, $weekEnd, $secondBand, $scheduleType);
                    }
                }
            }
        }
    }

    protected function getAgeBandsForCategory(string $category): array
    {
        // Return appropriate age bands sorted youngest first
        return match ($category) {
            'sports' => [
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['tween'],    // 7-11
                $this->ageBands['preteen'],  // 8-12
                $this->ageBands['teen'],     // 10-14
            ],
            'arts' => [
                $this->ageBands['tiny'],     // 3-5
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['preteen'],  // 8-12
            ],
            'performing_arts' => [
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['tween'],    // 7-11
                $this->ageBands['teen'],     // 10-14
            ],
            'stem' => [
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['tween'],    // 7-11
                $this->ageBands['preteen'],  // 8-12
                $this->ageBands['teen'],     // 10-14
            ],
            'nature' => [
                $this->ageBands['tiny'],     // 3-5
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['preteen'],  // 8-12
            ],
            'academic' => [
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['tween'],    // 7-11
                $this->ageBands['teen'],     // 10-14
            ],
            'general' => [
                $this->ageBands['tiny'],     // 3-5
                $this->ageBands['young'],    // 4-7
                $this->ageBands['kid'],      // 5-9
                $this->ageBands['wide'],     // 5-12
                $this->ageBands['tween'],    // 7-11
                $this->ageBands['teen'],     // 10-14
            ],
        };
    }

    protected function createCamp(Facility $facility, string $category, string $weekStart, string $weekEnd, array $ageBand, string $scheduleType): void
    {
        $template = $this->campTemplates[$category];
        $name = $template['names'][array_rand($template['names'])];
        $description = $template['descriptions'][array_rand($template['descriptions'])];

        [$startTime, $endTime] = $this->getScheduleTimes($scheduleType);

        // Price
        $priceRange = $this->categoryPriceRanges[$category];
        $basePrice = rand($priceRange[0], $priceRange[1]);
        if ($scheduleType !== 'full_day') {
            $basePrice = (int) ($basePrice * 0.6);
        }
        $priceCents = (int) (round($basePrice / 2500) * 2500);

        // Capacity and enrollment
        $capRange = $this->categoryCapacity[$category];
        $capacity = rand($capRange[0], $capRange[1]);
        [$enrolled, $waitlistCount] = $this->pickEnrollment($capacity);

        // Lunch: full-day at facilities that provide it
        $lunchProvided = $scheduleType === 'full_day' && $facility->lunch_provided;

        $weekLabel = Carbon::parse($weekStart)->format('M-d');
        $ageLabel = $ageBand[0] . '-' . $ageBand[1];
        $slug = Str::slug("{$facility->slug}-{$name}-{$ageLabel}-{$weekLabel}") . '-' . Str::random(4);

        Camp::create([
            'facility_id' => $facility->id,
            'name' => $name,
            'slug' => $slug,
            'category' => $category,
            'description' => $description,
            'age_min' => $ageBand[0],
            'age_max' => $ageBand[1],
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

    protected function pickScheduleType(int $fullDayPercent = 70): string
    {
        $roll = rand(1, 100);
        if ($roll <= $fullDayPercent) {
            return 'full_day';
        }
        return rand(0, 1) ? 'half_day_am' : 'half_day_pm';
    }

    protected function getScheduleTimes(string $scheduleType): array
    {
        return match ($scheduleType) {
            'full_day' => ['09:00', '15:00'],
            'half_day_am' => ['09:00', '12:00'],
            'half_day_pm' => ['13:00', '16:00'],
        };
    }

    protected function pickEnrollment(int $capacity): array
    {
        $roll = rand(1, 100);

        if ($roll <= 55) {
            // Available: 30-80% full
            $enrolled = (int) ($capacity * (rand(30, 80) / 100));
            return [$enrolled, 0];
        }

        if ($roll <= 80) {
            // Almost full: 1-3 spots left
            $enrolled = $capacity - rand(1, 3);
            return [max(0, $enrolled), 0];
        }

        // Full with waitlist
        $waitlist = rand(1, 8);
        return [$capacity, $waitlist];
    }
}
