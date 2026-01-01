<?php

namespace Database\Seeders;

use App\Models\ContentCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ContentCategorySeeder extends Seeder
{
    protected array $categories = [
        'Food & Drink' => [
            'description' => 'Culinary experiences across Ohio',
            'children' => ['Restaurants', 'Breweries', 'Bars', 'Cafes', 'Bakeries', 'Wineries', 'Distilleries', 'Food Trucks', 'Ice Cream'],
        ],
        'Outdoors & Nature' => [
            'description' => 'Natural attractions and outdoor activities',
            'children' => ['Hiking', 'Parks', 'Camping', 'Lakes', 'Scenic Drives', 'Fishing', 'Bike Trails', 'Kayaking', 'Waterfalls', 'Gardens', 'Beaches', 'Golf', 'Caves'],
        ],
        'Arts & Culture' => [
            'description' => 'Cultural attractions and artistic venues',
            'children' => ['Museums', 'Theaters', 'Galleries', 'Historic Sites', 'Architecture', 'Street Art', 'Live Music'],
        ],
        'Entertainment' => [
            'description' => 'Fun activities and entertainment venues',
            'children' => ['Sports', 'Concerts', 'Events', 'Nightlife', 'Amusement Parks', 'Casinos', 'Escape Rooms', 'Bowling', 'Arcades'],
        ],
        'Shopping' => [
            'description' => 'Retail destinations and markets',
            'children' => ['Antiques', 'Farmers Markets', 'Malls', 'Local Shops'],
        ],
        'Family' => [
            'description' => 'Family-friendly activities and destinations',
            'children' => ['Kid-Friendly', 'Playgrounds', 'Zoos', 'Aquariums', 'Farms'],
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding content categories...');

        $order = 1;
        $created = 0;
        $skipped = 0;

        foreach ($this->categories as $parentName => $data) {
            $parentSlug = Str::slug($parentName);

            // Create or find parent category
            $parent = ContentCategory::firstOrCreate(
                ['slug' => $parentSlug],
                [
                    'name' => $parentName,
                    'description' => $data['description'],
                    'meta_title' => "Ohio {$parentName} Guide",
                    'meta_description' => "Discover the best {$parentName} experiences across Ohio",
                    'display_order' => $order++,
                    'parent_id' => null,
                ]
            );

            if ($parent->wasRecentlyCreated) {
                $created++;
                $this->command->info("Created parent: {$parentName}");
            } else {
                $skipped++;
            }

            // Create children
            $childOrder = 1;
            foreach ($data['children'] as $childName) {
                $childSlug = Str::slug($childName);

                $child = ContentCategory::firstOrCreate(
                    ['slug' => $childSlug],
                    [
                        'name' => $childName,
                        'parent_id' => $parent->id,
                        'description' => "{$childName} guides and recommendations",
                        'meta_title' => "Ohio {$childName} Guide",
                        'meta_description' => "Find the best {$childName} in Ohio",
                        'display_order' => $childOrder++,
                    ]
                );

                if ($child->wasRecentlyCreated) {
                    $created++;
                } else {
                    // Ensure parent_id is set correctly for existing categories
                    if ($child->parent_id !== $parent->id) {
                        $child->update(['parent_id' => $parent->id]);
                        $this->command->info("Updated parent for: {$childName}");
                    }
                    $skipped++;
                }
            }
        }

        $this->command->info("Created {$created} categories, skipped {$skipped} existing.");
    }
}
