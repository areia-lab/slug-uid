<?php

namespace AreiaLab\SlugUid\Traits;

use AreiaLab\SlugUid\Facades\SlugUid;

trait HasSlugUid
{
    protected static function bootHasSlugUid()
    {
        static::creating(function ($model) {
            SlugUid::assign($model);
        });

        static::updating(function ($model) {
            if (config('sluguid.slug.regen_on_update') ?? false) {
                $slugCol = $model->slug_column ?? 'slug';
                $model->{$slugCol} = SlugUid::uniqueSlug($model, SlugUid::slugFromModel($model));
            }
        });
    }
}
