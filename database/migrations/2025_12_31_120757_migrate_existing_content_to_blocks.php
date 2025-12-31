<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Converts existing guide content to the new block-based format:
     * - body field → text block
     * - list_items in metadata → list block
     * - address field split: URLs → website, non-URLs → address
     */
    public function up(): void
    {
        // Get all content records that have body or list_items in metadata
        $contents = DB::table('content')
            ->whereNotNull('body')
            ->orWhereNotNull('metadata')
            ->get();

        foreach ($contents as $content) {
            $blocks = [];
            $order = 0;
            $metadata = $content->metadata ? json_decode($content->metadata, true) : [];

            // Convert body to text block if not empty
            if (! empty($content->body) && trim(strip_tags($content->body)) !== '') {
                $blocks[] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'text',
                    'order' => $order++,
                    'data' => [
                        'content' => $content->body,
                    ],
                ];
            }

            // Convert list_items from metadata to list block
            if (! empty($metadata['list_items'])) {
                $listItems = [];
                $listSettings = $metadata['list_settings'] ?? [];

                foreach ($metadata['list_items'] as $item) {
                    $address = $item['address'] ?? '';
                    $website = '';

                    // Split address field: URLs go to website, non-URLs stay as address
                    if (! empty($address)) {
                        if ($this->isUrl($address)) {
                            $website = $address;
                            $address = '';
                        }
                    }

                    $listItems[] = [
                        'id' => $item['id'] ?? Str::uuid()->toString(),
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                        'image' => $item['image'] ?? null,
                        'address' => $address,
                        'website' => $website,
                        'rating' => $item['rating'] ?? null,
                    ];
                }

                $blocks[] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'list',
                    'order' => $order++,
                    'data' => [
                        'title' => $listSettings['title'] ?? '',
                        'ranked' => $listSettings['ranked'] ?? true,
                        'countdown' => $listSettings['countdown'] ?? false,
                        'items' => $listItems,
                    ],
                ];

                // Remove list_items and list_settings from metadata since they're now in blocks
                unset($metadata['list_items']);
                unset($metadata['list_settings']);
            }

            // Only update if we created any blocks
            if (! empty($blocks)) {
                DB::table('content')
                    ->where('id', $content->id)
                    ->update([
                        'blocks' => json_encode($blocks),
                        'metadata' => ! empty($metadata) ? json_encode($metadata) : null,
                    ]);
            }
        }

        // Also migrate guide_drafts
        $drafts = DB::table('guide_drafts')
            ->whereNotNull('body')
            ->orWhereNotNull('list_items')
            ->get();

        foreach ($drafts as $draft) {
            $blocks = [];
            $order = 0;

            // Convert body to text block if not empty
            if (! empty($draft->body) && trim(strip_tags($draft->body)) !== '') {
                $blocks[] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'text',
                    'order' => $order++,
                    'data' => [
                        'content' => $draft->body,
                    ],
                ];
            }

            // Convert list_items to list block
            $listItems = $draft->list_items ? json_decode($draft->list_items, true) : [];
            $listSettings = $draft->list_settings ? json_decode($draft->list_settings, true) : [];

            if (! empty($listItems)) {
                $convertedItems = [];

                foreach ($listItems as $item) {
                    $address = $item['address'] ?? '';
                    $website = '';

                    // Split address field: URLs go to website, non-URLs stay as address
                    if (! empty($address)) {
                        if ($this->isUrl($address)) {
                            $website = $address;
                            $address = '';
                        }
                    }

                    $convertedItems[] = [
                        'id' => $item['id'] ?? Str::uuid()->toString(),
                        'title' => $item['title'] ?? '',
                        'description' => $item['description'] ?? '',
                        'image' => $item['image'] ?? null,
                        'address' => $address,
                        'website' => $website,
                        'rating' => $item['rating'] ?? null,
                    ];
                }

                $blocks[] = [
                    'id' => Str::uuid()->toString(),
                    'type' => 'list',
                    'order' => $order++,
                    'data' => [
                        'title' => $listSettings['title'] ?? '',
                        'ranked' => $listSettings['ranked'] ?? true,
                        'countdown' => $listSettings['countdown'] ?? false,
                        'items' => $convertedItems,
                    ],
                ];
            }

            // Only update if we created any blocks
            if (! empty($blocks)) {
                DB::table('guide_drafts')
                    ->where('id', $draft->id)
                    ->update([
                        'blocks' => json_encode($blocks),
                    ]);
            }
        }
    }

    /**
     * Check if a string is a URL
     */
    private function isUrl(string $string): bool
    {
        return filter_var($string, FILTER_VALIDATE_URL) !== false
            || preg_match('/^(https?:\/\/|www\.)/i', $string);
    }

    /**
     * Reverse the migrations.
     *
     * This converts blocks back to body + list_items format.
     * Note: This is a best-effort reverse migration - some data structure
     * might not be perfectly reversible.
     */
    public function down(): void
    {
        // Reverse content table
        $contents = DB::table('content')
            ->whereNotNull('blocks')
            ->get();

        foreach ($contents as $content) {
            $blocks = json_decode($content->blocks, true) ?? [];
            $body = '';
            $listItems = [];
            $listSettings = [];
            $metadata = $content->metadata ? json_decode($content->metadata, true) : [];

            foreach ($blocks as $block) {
                if ($block['type'] === 'text') {
                    $body .= $block['data']['content'] ?? '';
                } elseif ($block['type'] === 'list') {
                    $listSettings = [
                        'title' => $block['data']['title'] ?? '',
                        'ranked' => $block['data']['ranked'] ?? true,
                        'countdown' => $block['data']['countdown'] ?? false,
                    ];

                    foreach ($block['data']['items'] ?? [] as $item) {
                        // Combine website and address back into address field
                        $address = ! empty($item['website']) ? $item['website'] : ($item['address'] ?? '');

                        $listItems[] = [
                            'id' => $item['id'] ?? Str::uuid()->toString(),
                            'title' => $item['title'] ?? '',
                            'description' => $item['description'] ?? '',
                            'image' => $item['image'] ?? null,
                            'address' => $address,
                            'rating' => $item['rating'] ?? null,
                        ];
                    }
                }
            }

            // Add list data back to metadata
            if (! empty($listItems)) {
                $metadata['list_items'] = $listItems;
                $metadata['list_settings'] = $listSettings;
            }

            DB::table('content')
                ->where('id', $content->id)
                ->update([
                    'body' => $body ?: $content->body,
                    'blocks' => null,
                    'metadata' => ! empty($metadata) ? json_encode($metadata) : null,
                ]);
        }

        // Reverse guide_drafts table
        $drafts = DB::table('guide_drafts')
            ->whereNotNull('blocks')
            ->get();

        foreach ($drafts as $draft) {
            $blocks = json_decode($draft->blocks, true) ?? [];
            $body = '';
            $listItems = [];
            $listSettings = [];

            foreach ($blocks as $block) {
                if ($block['type'] === 'text') {
                    $body .= $block['data']['content'] ?? '';
                } elseif ($block['type'] === 'list') {
                    $listSettings = [
                        'enabled' => true,
                        'title' => $block['data']['title'] ?? '',
                        'ranked' => $block['data']['ranked'] ?? true,
                        'countdown' => $block['data']['countdown'] ?? false,
                    ];

                    foreach ($block['data']['items'] ?? [] as $item) {
                        // Combine website and address back into address field
                        $address = ! empty($item['website']) ? $item['website'] : ($item['address'] ?? '');

                        $listItems[] = [
                            'id' => $item['id'] ?? Str::uuid()->toString(),
                            'title' => $item['title'] ?? '',
                            'description' => $item['description'] ?? '',
                            'image' => $item['image'] ?? null,
                            'address' => $address,
                            'rating' => $item['rating'] ?? null,
                        ];
                    }
                }
            }

            DB::table('guide_drafts')
                ->where('id', $draft->id)
                ->update([
                    'body' => $body ?: $draft->body,
                    'blocks' => null,
                    'list_items' => ! empty($listItems) ? json_encode($listItems) : null,
                    'list_settings' => ! empty($listSettings) ? json_encode($listSettings) : null,
                ]);
        }
    }
};
