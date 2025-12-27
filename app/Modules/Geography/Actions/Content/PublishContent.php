<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\Content;
use App\Modules\Geography\Events\ContentPublished;
use Carbon\Carbon;

class PublishContent
{
    public function execute(Content $content, ?Carbon $publishAt = null): Content
    {
        $content->update([
            'published_at' => $publishAt ?? now(),
        ]);

        event(new ContentPublished($content));

        return $content->fresh();
    }
}
