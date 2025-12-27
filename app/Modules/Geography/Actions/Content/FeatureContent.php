<?php

namespace App\Modules\Geography\Actions\Content;

use App\Models\Content;
use App\Modules\Geography\Events\ContentFeatured;

class FeatureContent
{
    public function execute(Content $content, bool $featured = true): Content
    {
        $content->update(['featured' => $featured]);

        if ($featured) {
            event(new ContentFeatured($content));
        }

        return $content->fresh();
    }
}
