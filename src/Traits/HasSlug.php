<?php

namespace AreiaLab\SlugUid\Traits;

use AreiaLab\SlugUid\Facades\SlugUid;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        // On create
        static::creating(function ($model) {
            $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');

            if ($slugCol && empty($model->{$slugCol})) {
                $source = $model->slug_source ?? $model->title ?? $model->name ?? null;
                if (!empty($source)) {
                    $model->{$slugCol} = SlugUid::uniqueSlug(get_class($model), $source);
                }
            }
        });

        // On update (only if title/name changed)
        static::updating(function ($model) {
            $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');

            $source = $model->slug_source ?? $model->title ?? $model->name ?? null;

            if (!empty($source) && $model->isDirty(['title', 'name'])) {
                $model->{$slugCol} = SlugUid::uniqueSlug(get_class($model), $source, $model->getKey());
            }
        });
    }
}
