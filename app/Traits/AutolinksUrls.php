<?php

namespace App\Traits;

trait AutolinksUrls
{
    /**
     * Get the body with plain text URLs converted to clickable links.
     */
    public function getFormattedBodyAttribute(): string
    {
        return $this->autolinkUrls($this->body ?? '');
    }

    /**
     * Convert plain text URLs to clickable links, skipping URLs already in anchor tags.
     */
    protected function autolinkUrls(string $text): string
    {
        if (empty($text)) {
            return $text;
        }

        // Pattern to match URLs not already inside an anchor tag
        // This uses a negative lookbehind to avoid matching href="..." URLs
        $urlPattern = '/(?<!href=["\'])(?<!src=["\'])(https?:\/\/[^\s<>"\']+)/i';

        // Split the text by existing anchor tags to avoid modifying them
        $parts = preg_split('/(<a\s[^>]*>.*?<\/a>)/is', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        $result = '';
        foreach ($parts as $part) {
            // If this part is an anchor tag, leave it alone
            if (preg_match('/^<a\s/i', $part)) {
                $result .= $part;
            } else {
                // Convert plain text URLs to links
                $result .= preg_replace_callback($urlPattern, function ($matches) {
                    $url = $matches[1];
                    // Clean up any trailing punctuation that's likely not part of the URL
                    $trailingPunct = '';
                    if (preg_match('/([.,;:!?\)]+)$/', $url, $punctMatch)) {
                        $trailingPunct = $punctMatch[1];
                        $url = substr($url, 0, -strlen($trailingPunct));
                    }
                    $escapedUrl = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
                    return '<a href="' . $escapedUrl . '" target="_blank" rel="noopener noreferrer">' . $escapedUrl . '</a>' . $trailingPunct;
                }, $part);
            }
        }

        return $result;
    }
}