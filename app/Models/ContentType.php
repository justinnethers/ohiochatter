<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentType extends Model
{
    protected $guarded = [];

    protected $casts = [
        'required_fields' => 'array',
        'optional_fields' => 'array'
    ];

    public function content()
    {
        return $this->hasMany(Content::class);
    }

    public function validateMetadata(array $metadata): bool
    {
        if (empty($this->required_fields)) {
            return true;
        }

        // Validate required fields exist
        foreach ($this->required_fields as $field) {
            if (!isset($metadata[$field])) {
                return false;
            }
        }

        // Ensure no undefined fields are present
        $allowedFields = array_merge($this->required_fields, $this->optional_fields ?? []);
        foreach (array_keys($metadata) as $field) {
            if (!in_array($field, $allowedFields)) {
                return false;
            }
        }

        return true;
    }

    public function getSchemaAttribute(): array
    {
        return match($this->slug) {
            'article' => [
                'required_fields' => ['title', 'body', 'excerpt'],
                'optional_fields' => ['featured_image', 'gallery', 'author_bio']
            ],
            'list' => [
                'required_fields' => ['title', 'introduction', 'items'],
                'optional_fields' => ['criteria', 'methodology', 'featured_image']
            ],
            'guide' => [
                'required_fields' => ['title', 'introduction', 'sections'],
                'optional_fields' => ['tips', 'warnings', 'related_links']
            ],
            default => [
                'required_fields' => ['title', 'body'],
                'optional_fields' => ['featured_image']
            ]
        };
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
