<?php

namespace Database\Seeders;

use App\Models\City;
use App\Models\ContentCategory;
use App\Models\GuideDraft;
use App\Models\User;
use Illuminate\Database\Seeder;

class GuideDraftSeeder extends Seeder
{
    protected array $drafts = [
        [
            'title' => 'Best Breweries in Cincinnati',
            'location_type' => 'city',
            'location_slug' => 'cincinnati',
            'categories' => ['breweries'],
        ],
        [
            'title' => 'Dankhouse Brewing Co',
            'location_type' => 'city',
            'location_slug' => 'newark',
            'categories' => ['breweries'],
        ],
        [
            'title' => 'Paradise Brewing',
            'location_type' => 'city',
            'location_slug' => 'cincinnati',
            'categories' => ['breweries'],
            'excerpt' => 'Anderson Township\'s inaugural brewery with a second taproom in a historic Williamsburg firehouse. Family-friendly, dog-friendly, with a tropical vibe.',
        ],
    ];

    public function run(): void
    {
        $this->command->info('Seeding guide drafts...');

        $user = User::where('username', 'justincredible')->first();

        if (! $user) {
            $this->command->error('User "justincredible" not found. Aborting.');

            return;
        }

        $created = 0;
        $skipped = 0;

        foreach ($this->drafts as $draftData) {
            // Resolve location
            $locatable = $this->resolveLocation($draftData['location_type'], $draftData['location_slug']);

            if (! $locatable) {
                $this->command->warn("Location not found: {$draftData['location_type']} - {$draftData['location_slug']}");
                $skipped++;

                continue;
            }

            // Resolve category IDs
            $categoryIds = $this->resolveCategoryIds($draftData['categories']);

            // Check if draft already exists
            $exists = GuideDraft::where('user_id', $user->id)
                ->where('title', $draftData['title'])
                ->where('locatable_type', get_class($locatable))
                ->where('locatable_id', $locatable->id)
                ->exists();

            if ($exists) {
                $this->command->info("Skipping existing draft: {$draftData['title']}");
                $skipped++;

                continue;
            }

            GuideDraft::create([
                'user_id' => $user->id,
                'title' => $draftData['title'],
                'excerpt' => $draftData['excerpt'] ?? null,
                'body' => $draftData['body'] ?? null,
                'locatable_type' => get_class($locatable),
                'locatable_id' => $locatable->id,
                'category_ids' => $categoryIds,
                'content_category_id' => $categoryIds[0] ?? null,
            ]);

            $this->command->info("Created draft: {$draftData['title']}");
            $created++;
        }

        $this->command->info("Created {$created} drafts, skipped {$skipped}.");
    }

    protected function resolveLocation(string $type, string $slug): ?object
    {
        return match ($type) {
            'city' => City::where('slug', $slug)->first(),
            'county' => \App\Models\County::where('slug', $slug)->first(),
            'region' => \App\Models\Region::where('slug', $slug)->first(),
            default => null,
        };
    }

    protected function resolveCategoryIds(array $categorySlugs): array
    {
        return ContentCategory::whereIn('slug', $categorySlugs)
            ->pluck('id')
            ->toArray();
    }
}