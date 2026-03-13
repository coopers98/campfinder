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
                'Gotham Goal Strikers', 'Brooklyn Ballers Academy', 'NYC Swim Lab',
                'Flips & Tumbles Gymnastics', 'Liberty Tennis Juniors', 'Urban Olympians',
                'City Track Blazers', 'Gridiron Jr. Flag League', 'Spike City Volleyball',
                'Sideline to Spotlight Soccer', 'The Dribble Factory', 'Basecamp Athletics',
                'Metro Lacrosse League', 'Harbor Swim School', 'Steel Bridge Runners',
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
                'Tiny Brushstrokes Studio', 'Clay Borough Pottery', 'The Mosaic Workshop',
                'Pigment & Ink Lab', 'Sidewalk Chalk Society', 'Little Frida\'s Art House',
                'The Loom Room', 'Splatter Zone Studio', 'Canvas & Cocoa Kids',
                'Papier-Mâché Planet', 'The Glazed Kiln', 'Stencil City Workshop',
                'Brooklyn Mural Makers', 'Color Theory Kids', 'Found Object Atelier',
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
                'Broadway Bootcamp Jr.', 'The Rhythm Hive', 'Reel Kids Film Academy',
                'Punchline Players Improv', 'Spotlight Stage Company', 'Beat Drop Music Lab',
                'The Hip-Hop Conservatory', 'Curtain Call Theater Camp', 'Vocal Spark Singers',
                'Trapeze & Tumble Circus Arts', 'The Playwrights\' Garage', 'Sketch Comedy Factory',
                'Mic Drop Music Camp', 'Pirouette Dance Intensive', 'Silver Screen Directors',
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
                'CodeCraft Academy', 'BotBuilder Workshop', 'The Hypothesis Lab',
                'Pixel Quest Game Studio', 'Bridge Builders Engineering', 'Neon Pixel Animation',
                'Redstone & Beyond (Minecraft)', 'Mission Control Rocketry', 'Molecule Kitchen Chemistry',
                'Neural Net Juniors', 'Circuit Breakers Electronics', 'PrintForge 3D Lab',
                'Drone Pilots Academy', 'Quantum Curious Science', 'Data Detectives Club',
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
                'Urban Wilderness Rangers', 'Concrete Jungle Gardeners', 'Tide Pool Explorers',
                'Monarch Migration Trackers', 'Rooftop Farm Camp', 'Muddyboots Nature School',
                'The Bug Observatory', 'Seedling Sprouts Garden Camp', 'Survivor Skills Outdoors',
                'Field Sketch Naturalists', 'Feathered Friends Birding', 'Watershed Watchers',
                'Forest Bathing Explorers', 'Wildflower Foragers', 'Compost Crew Kids',
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
                'Page Turners Book Lab', 'Number Ninjas Math Camp', 'The Story Forge Writers',
                'Polyglot Playground Languages', 'Checkmate Chess Intensive', 'Inkwell Young Authors',
                'The Podium Debate Club', 'Time Travelers History Camp', 'Between the Covers Book Club',
                'Enigma Puzzle Masters', 'Junior MasterChef Science', 'Cartography & Compass Club',
                'Brainwave Logic Games', 'Young Philosophers Circle', 'Mock Trial Academy',
            ],
            'descriptions' => [
                'Make learning fun! Engaging academic enrichment through games, projects, and collaborative activities. Perfect for keeping skills sharp over summer.',
                'Brain-boosting camp that combines critical thinking challenges with creative projects. Kids don\'t even realize they\'re learning!',
                'Interactive academic camp blending literacy, math games, and creative expression. Small groups ensure personalized attention.',
                'Summer slide? Not here. We keep brains buzzing with hands-on projects that feel like play but build real skills.',
            ],
        ],
        'martial_arts' => [
            'names' => [
                'Little Dragons Karate', 'Brooklyn Judo Club Jr.', 'Tiger Claw Kung Fu Camp',
                'Zen Warriors Aikido', 'The Dojo Project', 'Black Belt Bootcamp Kids',
                'Ninja Academy NYC', 'Iron Fist Taekwondo', 'Samurai Spirit Kendo',
                'Capoeira Rhythm & Flow', 'MMA Minis Training', 'Jiu-Jitsu Explorers',
                'Shaolin Kids Summer', 'Kickboxing Cubs', 'Warrior Way Self-Defense',
            ],
            'descriptions' => [
                'Discipline, respect, and confidence through martial arts training. Belt progressions, sparring basics, and character building all week.',
                'Kids learn self-defense, focus, and body awareness in a safe, structured environment. All levels from white belt to advanced.',
                'Traditional martial arts meets modern fitness. Your child will gain strength, flexibility, and inner calm through daily practice.',
                'More than kicks and punches — our instructors emphasize mindfulness, respect, and perseverance alongside physical technique.',
            ],
        ],
        'equestrian' => [
            'names' => [
                'Saddle Up Stables Camp', 'Bronx Pony Club', 'Trot & Canter Academy',
                'Urban Equestrians NYC', 'Gallop Park Riding Camp', 'Horseshoe Haven',
                'Bridle Path Juniors', 'Mane Attraction Riding School', 'Stirrup Cup Stables',
                'Prospect Park Pony Camp', 'Liberty Bell Riding Academy', 'The Young Jockeys',
                'Paddock & Pasture Camp', 'Blue Ribbon Equestrians', 'City Saddle School',
            ],
            'descriptions' => [
                'Learn to ride, groom, and care for horses at our urban equestrian center. Daily riding lessons, barn management, and horse bonding.',
                'From first pony rides to trotting and cantering, kids build confidence and connection with these gentle giants. All gear provided.',
                'Horsemanship camp covering riding fundamentals, stable care, tack cleaning, and equine anatomy. End-of-week mini show for parents.',
                'Whether your child has never touched a horse or already loves to ride, our experienced instructors meet them where they are.',
            ],
        ],
        'pets' => [
            'names' => [
                'Pawsitive Kids Animal Camp', 'Critter Academy NYC', 'The Puppy Whisperers',
                'Fur & Feathers Pet Science', 'Junior Vet Academy', 'Bark Park Animal Explorers',
                'Scales & Tails Reptile Camp', 'Whiskers & Wags Camp', 'Noah\'s Ark Animal Adventures',
                'Pet Chef Nutrition Lab', 'Shelter Heroes Volunteer Camp', 'The Bunny Barn',
                'Marine Paws Aquarium Camp', 'Petting Zoo Apprentices', 'Creature Feature Camp',
            ],
            'descriptions' => [
                'Hands-on animal interaction every day! Kids learn pet care, animal behavior, and veterinary basics with real animals.',
                'From puppies to parrots, hamsters to hermit crabs — campers meet, handle, and learn to care for a variety of animals.',
                'Future vets and animal lovers unite! Daily animal encounters, pet first aid basics, and behind-the-scenes shelter visits.',
                'An animal-obsessed kid\'s dream week. Training exercises, nutrition lessons, habitat building, and lots of furry cuddle time.',
            ],
        ],
        'general' => [
            'names' => [
                'Camp Kaleidoscope', 'The Great Summer Mashup', 'Basecamp Brooklyn',
                'Bright Days Adventure Camp', 'The Everything Camp', 'Camp Wildcard',
                'Ultimate Summer Sampler', 'Sunbeam Day Camp', 'Golden Hour Camp',
                'Rainbow Week Experience', 'Camp Trailblaze', 'The Backyard Explorers',
                'All-Star Summer Days', 'Camp Firefly', 'Wonder Week Day Camp',
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
        'Midtown Movers Sports Club'=> ['sports', 'martial_arts', 'general'],
        'Village Theater Kids'      => ['performing_arts', 'arts'],
        'Harlem Youth Arts'         => ['arts', 'performing_arts', 'general'],
        'Flatiron STEM Academy'     => ['stem', 'academic', 'arts'],
        // Brooklyn
        'Slope Sports & Arts'       => ['sports', 'arts', 'nature', 'general'],
        'Williamsburg Innovation Lab'=> ['stem', 'arts', 'academic'],
        'DUMBO Discovery Camp'      => ['nature', 'arts', 'pets'],
        'Heights Academy'           => ['academic', 'general', 'arts'],
        'Cobble Hill Kids Club'     => ['arts', 'general', 'performing_arts'],
        'Bushwick Beats & Moves'    => ['performing_arts', 'martial_arts', 'general'],
        'Bay Ridge Community Camp'  => ['sports', 'arts', 'general', 'academic'],
        'Crown Heights Creative'    => ['arts', 'performing_arts'],
        'Prospect Park Alliance Camp'=> ['nature', 'equestrian', 'general'],
        // Queens
        'Astoria Adventure Camp'    => ['general', 'sports', 'nature', 'pets'],
        'LIC Tech Camp'             => ['stem', 'academic'],
        'Forest Hills Family Center'=> ['sports', 'general', 'nature'],
        'Jackson Heights Global Camp'=> ['academic', 'martial_arts', 'general'],
        'Flushing Meadows Sports Academy' => ['sports', 'general', 'stem', 'martial_arts'],
        // Bronx
        'Riverdale Nature Camp'     => ['nature', 'equestrian', 'pets'],
        'Pelham Bay Sports Complex' => ['sports', 'general', 'nature'],
        'Van Cortlandt Riding Center' => ['equestrian', 'nature'],
        // Staten Island
        'St. George Creative Campus'=> ['performing_arts', 'arts', 'general'],
        'Staten Island Pet Ranch'   => ['pets', 'nature', 'equestrian'],
    ];

    protected array $facilitySize = [
        // Large
        'NYC Kids Academy'          => 'large',
        'Tribeca Youth Center'      => 'large',
        'Slope Sports & Arts'       => 'large',
        'Astoria Adventure Camp'    => 'large',
        'Pelham Bay Sports Complex' => 'large',
        'Bay Ridge Community Camp'  => 'large',
        'Flushing Meadows Sports Academy' => 'large',
        // Medium
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
        'Van Cortlandt Riding Center' => 'medium',
        'Staten Island Pet Ranch'   => 'medium',
        // Small
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
        'martial_arts'    => [30000, 65000],
        'equestrian'      => [50000, 95000],
        'pets'            => [35000, 70000],
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
        'martial_arts'    => [12, 20],
        'equestrian'      => [6, 12],
        'pets'            => [10, 16],
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
            return $this->summerWeeks;
        }

        if ($size === 'medium') {
            if (rand(1, 100) <= 50) {
                $skipIndex = rand(1, 100) <= 70 ? 2 : rand(0, 9);
                return array_values(array_filter($this->summerWeeks, fn ($k) => $k !== $skipIndex, ARRAY_FILTER_USE_KEY));
            }
            return $this->summerWeeks;
        }

        $skipIndex = rand(1, 100) <= 60 ? 2 : rand(0, 9);
        return array_values(array_filter($this->summerWeeks, fn ($k) => $k !== $skipIndex, ARRAY_FILTER_USE_KEY));
    }

    protected function createWeekCamps(Facility $facility, array $categories, string $size, string $weekStart, string $weekEnd): void
    {
        if ($size === 'large') {
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                $numBands = min(count($ageBandsForCategory), rand(2, 3));
                $selectedBands = array_slice($ageBandsForCategory, 0, $numBands);

                foreach ($selectedBands as $band) {
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, 'full_day');
                }

                if (rand(1, 100) <= 30 && $selectedBands[0][0] <= 5) {
                    $halfType = rand(0, 1) ? 'half_day_am' : 'half_day_pm';
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $selectedBands[0], $halfType);
                }
            }
        } elseif ($size === 'medium') {
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                $numBands = min(count($ageBandsForCategory), rand(1, 2));
                $selectedBands = array_slice($ageBandsForCategory, 0, $numBands);

                foreach ($selectedBands as $band) {
                    $scheduleType = $this->pickScheduleType(75);
                    $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, $scheduleType);
                }
            }
        } else {
            foreach ($categories as $category) {
                $ageBandsForCategory = $this->getAgeBandsForCategory($category);
                $band = $ageBandsForCategory[array_rand($ageBandsForCategory)];
                $scheduleType = $this->pickScheduleType(65);
                $this->createCamp($facility, $category, $weekStart, $weekEnd, $band, $scheduleType);

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
        return match ($category) {
            'sports' => [
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['preteen'],
                $this->ageBands['teen'],
            ],
            'arts' => [
                $this->ageBands['tiny'],
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['preteen'],
            ],
            'performing_arts' => [
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['teen'],
            ],
            'stem' => [
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['preteen'],
                $this->ageBands['teen'],
            ],
            'nature' => [
                $this->ageBands['tiny'],
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['preteen'],
            ],
            'academic' => [
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['teen'],
            ],
            'martial_arts' => [
                $this->ageBands['tiny'],
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['teen'],
            ],
            'equestrian' => [
                $this->ageBands['kid'],
                $this->ageBands['tween'],
                $this->ageBands['preteen'],
                $this->ageBands['teen'],
            ],
            'pets' => [
                $this->ageBands['tiny'],
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['tween'],
            ],
            'general' => [
                $this->ageBands['tiny'],
                $this->ageBands['young'],
                $this->ageBands['kid'],
                $this->ageBands['wide'],
                $this->ageBands['tween'],
                $this->ageBands['teen'],
            ],
        };
    }

    protected function createCamp(Facility $facility, string $category, string $weekStart, string $weekEnd, array $ageBand, string $scheduleType): void
    {
        $template = $this->campTemplates[$category];
        $name = $template['names'][array_rand($template['names'])];
        $description = $template['descriptions'][array_rand($template['descriptions'])];

        [$startTime, $endTime] = $this->getScheduleTimes($scheduleType);

        $priceRange = $this->categoryPriceRanges[$category];
        $basePrice = rand($priceRange[0], $priceRange[1]);
        if ($scheduleType !== 'full_day') {
            $basePrice = (int) ($basePrice * 0.6);
        }
        $priceCents = (int) (round($basePrice / 2500) * 2500);

        $capRange = $this->categoryCapacity[$category];
        $capacity = rand($capRange[0], $capRange[1]);
        [$enrolled, $waitlistCount] = $this->pickEnrollment($capacity);

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
            $enrolled = (int) ($capacity * (rand(30, 80) / 100));
            return [$enrolled, 0];
        }

        if ($roll <= 80) {
            $enrolled = $capacity - rand(1, 3);
            return [max(0, $enrolled), 0];
        }

        $waitlist = rand(1, 8);
        return [$capacity, $waitlist];
    }
}
