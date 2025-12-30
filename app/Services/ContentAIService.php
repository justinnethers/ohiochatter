<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class ContentAIService
{
    public function generateSummary(string $title, string $body, ?string $listTitle = null, array $listItems = []): string
    {
        $content = $this->buildContentForSummary($title, $body, $listTitle, $listItems);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4o-mini',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful assistant that writes concise, engaging summaries for local Ohio guides and reviews. Write in a friendly, informative tone. Keep summaries between 100-200 characters. Do not use quotes around the summary. Do not include phrases like "This guide" or "This review" - just describe what readers will find.',
                ],
                [
                    'role' => 'user',
                    'content' => "Write a brief summary for this guide:\n\n{$content}",
                ],
            ],
            'max_tokens' => 100,
            'temperature' => 0.7,
        ]);

        return trim($response->choices[0]->message->content);
    }

    protected function buildContentForSummary(string $title, string $body, ?string $listTitle, array $listItems): string
    {
        $content = "Title: {$title}\n\n";

        // Strip HTML and truncate body for context
        $plainBody = strip_tags($body);
        $truncatedBody = mb_substr($plainBody, 0, 1000);
        $content .= "Content: {$truncatedBody}\n\n";

        if ($listTitle || ! empty($listItems)) {
            $content .= "List Section";
            if ($listTitle) {
                $content .= " ({$listTitle})";
            }
            $content .= ":\n";

            foreach (array_slice($listItems, 0, 5) as $item) {
                $itemTitle = $item['title'] ?? 'Untitled';
                $content .= "- {$itemTitle}\n";
            }
        }

        return $content;
    }
}