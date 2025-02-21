<?php

namespace App\Actions\Threads;

use App\Models\Thread;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class FetchSeoTags
{
    public function __invoke(Thread $thread)
    {
        $threadForSeo = [
            'title' => $thread->title,
            'original_post' => $thread->body,
            'first_reply' => $thread->firstReply(),
            'last_reply' => $thread->lastReply()
        ];

        $encodedThread = json_encode($threadForSeo);

        try {
            $response = OpenAI::chat()->create([
                'model' => 'gpt-4o-mini',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are an SEO expert who always responds using JSON.'],
                    [
                        'role' => 'user',
                        'content' => <<<EOT
                        Analyze the content from this forum page and provide meta tags for improving seo.

                        {$encodedThread}

                        Expected response example:

                        {"meta_title": string, "meta_description": string, "meta_keywords": string}

                        EOT
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ])->choices[0]->message->content;

            $decoded = json_decode($response);

            $thread->update([
                'regenerate_meta' => false,
                'meta_title' => $decoded->meta_title,
                'meta_description' => $decoded->meta_description,
                'keywords' => $decoded->meta_keywords,
                'meta_generated_at' => Carbon::now()
            ]);
        } catch (\Exception $exception) {
            Log::error('There was an issue generating meta tags with OpenAI.', ['message' => $exception->getMessage()]);
        }

        return false;
    }
}
