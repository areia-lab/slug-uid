<?php

namespace AreiaLab\SlugUid\Traits;

use AreiaLab\SlugUid\Facades\SlugUid;
use Illuminate\Support\Str;

trait HasUid
{
    /**
     * Boot the HasUid trait to auto-generate UIDs.
     */
    protected static function bootHasUid(): void
    {
        // On create
        static::creating(function ($model) {
            $uidCol = $model->uid_column ?? config('sluguid.uid.column', 'uid');

            if ($uidCol && empty($model->{$uidCol})) {
                $model->{$uidCol} = SlugUid::uniqueUid(
                    get_class($model),
                    $model->getUidPrefix()
                );
            }
        });
    }

    /**
     * Get the UID prefix for this model.
     *
     * @return string
     */
    public function getUidPrefix(): string
    {
        return property_exists($this, 'uid_prefix')
            ? $this->uid_prefix
            : config('sluguid.uid.prefix', Str::slug(class_basename($this)));
    }
}
