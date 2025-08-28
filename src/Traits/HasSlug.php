<?php

namespace AreiaLab\SlugUid\Traits;

use AreiaLab\SlugUid\Facades\SlugUid;

trait HasSlug
{
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');

            if ($slugCol && empty($model->{$slugCol})) {
                $source = $model->title ?? $model->name ?? null;
                if (!empty($source)) {
                    $model->{$slugCol} = SlugUid::uniqueSlug(get_class($model), $source);
                }
            }
        });
    }
}
