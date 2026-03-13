<?php

namespace Database\Seeders;

use App\Models\Facility;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class FacilitySeeder extends Seeder
{
    public function run(): void
    {
        $facilities = [
            // Manhattan (6)
            [
                'name' => 'NYC Kids Academy',
                'borough' => 'Manhattan',
                'neighborhood' => 'Upper West Side',
                'address' => '215 W 85th St, New York, NY 10024',
                'latitude' => 40.7870,
                'longitude' => -73.9754,
                'description' => 'A premier multi-activity camp offering diverse programs for children of all ages. Our spacious facility includes indoor gyms, art studios, and outdoor play areas.',
                'amenities' => ['indoor gym', 'art studio', 'outdoor playground', 'air conditioning', 'snack bar'],
                'lunch_provided' => true,
                'size' => 'large',
            ],
            [
                'name' => 'East Side Explorers',
                'borough' => 'Manhattan',
                'neighborhood' => 'Upper East Side',
                'address' => '401 E 76th St, New York, NY 10021',
                'latitude' => 40.7706,
                'longitude' => -73.9538,
                'description' => 'Hands-on STEM and nature exploration in the heart of the Upper East Side. We use Central Park as our outdoor classroom.',
                'amenities' => ['science lab', 'computer room', 'Central Park access', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'medium',
            ],
            [
                'name' => 'Chelsea Creative Arts',
                'borough' => 'Manhattan',
                'neighborhood' => 'Chelsea',
                'address' => '547 W 27th St, New York, NY 10001',
                'latitude' => 40.7510,
                'longitude' => -74.0018,
                'description' => 'A gallery-district arts camp where kids paint, sculpt, and perform in a real creative workspace. Small groups for personalized attention.',
                'amenities' => ['art gallery', 'pottery studio', 'performance space', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'small',
            ],
            [
                'name' => 'Tribeca Youth Center',
                'borough' => 'Manhattan',
                'neighborhood' => 'Tribeca',
                'address' => '105 Hudson St, New York, NY 10013',
                'latitude' => 40.7195,
                'longitude' => -74.0089,
                'description' => 'Downtown\'s favorite family center offering sports, arts, and academic enrichment camps all summer long.',
                'amenities' => ['swimming pool', 'basketball court', 'dance studio', 'library', 'cafeteria'],
                'lunch_provided' => true,
                'size' => 'large',
            ],
            [
                'name' => 'Midtown Movers Sports Club',
                'borough' => 'Manhattan',
                'neighborhood' => 'Midtown',
                'address' => '340 W 50th St, New York, NY 10019',
                'latitude' => 40.7631,
                'longitude' => -73.9889,
                'description' => 'High-energy sports camp with professional coaching. From soccer to gymnastics, we keep kids active and inspired.',
                'amenities' => ['full-size gym', 'turf field', 'rock climbing wall', 'locker rooms'],
                'lunch_provided' => true,
                'size' => 'medium',
            ],
            [
                'name' => 'Village Theater Kids',
                'borough' => 'Manhattan',
                'neighborhood' => 'East Village',
                'address' => '66 E 4th St, New York, NY 10003',
                'latitude' => 40.7264,
                'longitude' => -73.9908,
                'description' => 'Broadway-inspired performing arts camp in the East Village. Each session culminates in a showcase for family and friends.',
                'amenities' => ['black box theater', 'music room', 'costume shop', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'small',
            ],

            // Brooklyn (6)
            [
                'name' => 'Slope Sports & Arts',
                'borough' => 'Brooklyn',
                'neighborhood' => 'Park Slope',
                'address' => '220 5th Ave, Brooklyn, NY 11215',
                'latitude' => 40.6722,
                'longitude' => -73.9826,
                'description' => 'Park Slope\'s beloved community camp combining athletics, arts, and outdoor adventures in Prospect Park.',
                'amenities' => ['Prospect Park access', 'indoor gym', 'art room', 'outdoor field'],
                'lunch_provided' => true,
                'size' => 'large',
            ],
            [
                'name' => 'Williamsburg Innovation Lab',
                'borough' => 'Brooklyn',
                'neighborhood' => 'Williamsburg',
                'address' => '97 N 10th St, Brooklyn, NY 11249',
                'latitude' => 40.7193,
                'longitude' => -73.9575,
                'description' => 'A cutting-edge STEM camp where kids build robots, code games, and experiment with electronics in a maker space environment.',
                'amenities' => ['maker space', '3D printers', 'robotics lab', 'computer stations', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'medium',
            ],
            [
                'name' => 'DUMBO Discovery Camp',
                'borough' => 'Brooklyn',
                'neighborhood' => 'DUMBO',
                'address' => '45 Main St, Brooklyn, NY 11201',
                'latitude' => 40.7025,
                'longitude' => -73.9903,
                'description' => 'Waterfront camp combining nature exploration, art, and science with stunning Manhattan views. Small, nurturing groups.',
                'amenities' => ['waterfront access', 'art studio', 'outdoor garden', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'small',
            ],
            [
                'name' => 'Heights Academy',
                'borough' => 'Brooklyn',
                'neighborhood' => 'Brooklyn Heights',
                'address' => '185 Court St, Brooklyn, NY 11201',
                'latitude' => 40.6885,
                'longitude' => -73.9930,
                'description' => 'Well-rounded academic and enrichment camp with a focus on reading, math games, creative writing, and world languages.',
                'amenities' => ['classroom spaces', 'library', 'computer lab', 'playground', 'air conditioning'],
                'lunch_provided' => true,
                'size' => 'medium',
            ],
            [
                'name' => 'Cobble Hill Kids Club',
                'borough' => 'Brooklyn',
                'neighborhood' => 'Cobble Hill',
                'address' => '262 Smith St, Brooklyn, NY 11231',
                'latitude' => 40.6837,
                'longitude' => -73.9937,
                'description' => 'A cozy neighborhood camp where every child is known by name. Art, cooking, music, and outdoor play in a nurturing setting.',
                'amenities' => ['kitchen', 'music room', 'garden', 'air conditioning'],
                'lunch_provided' => true,
                'size' => 'small',
            ],
            [
                'name' => 'Bushwick Beats & Moves',
                'borough' => 'Brooklyn',
                'neighborhood' => 'Bushwick',
                'address' => '12 Jefferson St, Brooklyn, NY 11206',
                'latitude' => 40.7028,
                'longitude' => -73.9230,
                'description' => 'Dance, music production, and performing arts camp in the creative heart of Bushwick. Hip-hop, modern dance, and more.',
                'amenities' => ['dance studio', 'recording studio', 'performance space', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'medium',
            ],

            // Queens (3)
            [
                'name' => 'Astoria Adventure Camp',
                'borough' => 'Queens',
                'neighborhood' => 'Astoria',
                'address' => '22-12 31st St, Astoria, NY 11105',
                'latitude' => 40.7728,
                'longitude' => -73.9100,
                'description' => 'A vibrant multi-cultural camp celebrating the diversity of Astoria through food, art, sports, and language.',
                'amenities' => ['outdoor field', 'kitchen', 'art room', 'playground'],
                'lunch_provided' => true,
                'size' => 'large',
            ],
            [
                'name' => 'LIC Tech Camp',
                'borough' => 'Queens',
                'neighborhood' => 'Long Island City',
                'address' => '43-10 Crescent St, Long Island City, NY 11101',
                'latitude' => 40.7505,
                'longitude' => -73.9407,
                'description' => 'Future-focused STEM camp in the LIC tech corridor. Coding, game design, and digital art for the next generation.',
                'amenities' => ['computer lab', 'VR stations', 'green screen studio', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'small',
            ],
            [
                'name' => 'Forest Hills Family Center',
                'borough' => 'Queens',
                'neighborhood' => 'Forest Hills',
                'address' => '108-25 72nd Ave, Forest Hills, NY 11375',
                'latitude' => 40.7210,
                'longitude' => -73.8448,
                'description' => 'A full-service family center offering sports, arts, and nature camps with access to Forest Park trails.',
                'amenities' => ['swimming pool', 'tennis courts', 'Forest Park access', 'cafeteria', 'locker rooms'],
                'lunch_provided' => true,
                'size' => 'medium',
            ],

            // Bronx (2)
            [
                'name' => 'Riverdale Nature Camp',
                'borough' => 'Bronx',
                'neighborhood' => 'Riverdale',
                'address' => '5765 Riverdale Ave, Bronx, NY 10471',
                'latitude' => 40.9034,
                'longitude' => -73.9067,
                'description' => 'An outdoor-focused camp set in the green hills of Riverdale. Hiking, gardening, wildlife study, and environmental art.',
                'amenities' => ['nature trails', 'garden plots', 'outdoor amphitheater', 'picnic area'],
                'lunch_provided' => false,
                'size' => 'medium',
            ],
            [
                'name' => 'Pelham Bay Sports Complex',
                'borough' => 'Bronx',
                'neighborhood' => 'Pelham Bay',
                'address' => '1500 Shore Rd, Bronx, NY 10464',
                'latitude' => 40.8710,
                'longitude' => -73.8053,
                'description' => 'Sprawling sports campus with access to Pelham Bay Park. Full-day sports immersion from basketball to kayaking.',
                'amenities' => ['basketball courts', 'soccer field', 'kayak launch', 'beach access', 'locker rooms'],
                'lunch_provided' => true,
                'size' => 'large',
            ],

            // Staten Island (1)
            [
                'name' => 'St. George Creative Campus',
                'borough' => 'Staten Island',
                'neighborhood' => 'St. George',
                'address' => '15 Beach St, Staten Island, NY 10301',
                'latitude' => 40.6432,
                'longitude' => -74.0774,
                'description' => 'Staten Island\'s hub for creative kids. Art, theater, music, and digital media in a renovated waterfront building.',
                'amenities' => ['theater', 'art studios', 'music rooms', 'waterfront deck', 'air conditioning'],
                'lunch_provided' => false,
                'size' => 'medium',
            ],
        ];

        foreach ($facilities as $data) {
            $size = $data['size'];
            unset($data['size']);

            $facility = Facility::create(array_merge($data, [
                'slug' => Str::slug($data['name']),
                'image_url' => null,
            ]));

            // Store size as a temporary attribute for the camp seeder
            $facility->size = $size;
        }
    }
}
