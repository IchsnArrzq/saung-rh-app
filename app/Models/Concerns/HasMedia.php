<?php

namespace App\Models\Concerns;

use App\Models\Media;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Adds a polymorphic media library (images + videos) to a model.
 */
trait HasMedia
{
    public function media(): MorphMany
    {
        return $this->morphMany(Media::class, 'mediable')
            ->orderByDesc('is_primary')
            ->orderBy('sort_order')
            ->orderBy('created_at');
    }

    public function images(): MorphMany
    {
        return $this->media()->where('type', 'image');
    }

    public function videos(): MorphMany
    {
        return $this->media()->where('type', 'video');
    }

    /**
     * The primary image (flagged first, otherwise the earliest image).
     */
    public function primaryImage(): ?Media
    {
        return $this->images->firstWhere('is_primary', true)
            ?? $this->images->first();
    }
}
