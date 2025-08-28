<?php

namespace AreiaLab\SlugUid\Traits;

use Illuminate\Support\Str;
use AreiaLab\SlugUid\Facades\SlugUid;
use Illuminate\Support\Facades\Schema;

trait HasSlugUid
{
    /**
     * Boot the trait on the model.
     */
    protected static function bootHasSlugUid(): void
    {
        static::creating(function ($model) {
            // Slug
            $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');

            if ($model->usesSlugUidColumn($slugCol) && empty($model->{$slugCol})) {
                $source = $model->title ?? $model->name ?? null;
                if (!empty($source)) {
                    $model->{$slugCol} = SlugUid::uniqueSlug(get_class($model), $source);
                }
            }

            // UID
            $uidCol = $model->uid_column ?? config('sluguid.uid.column', 'uid');

            if ($model->usesSlugUidColumn($uidCol) && empty($model->{$uidCol})) {
                $model->{$uidCol} = SlugUid::uniqueUid(
                    get_class($model),
                    $model->getUidPrefix() // custom prefix
                );
            }

            // Sequence
            $seqCol = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
            if ($model->usesSlugUidColumn($seqCol) && empty($model->{$seqCol})) {
                $prefix = $model->getSequencePrefix();
                $padding = $model->getSequencePadding();
                $model->{$seqCol} = SlugUid::sequence(get_class($model), $prefix, $padding);
            }
        });
    }

    /**
     * Check if the model has the given column in its fillable or database table.
     *
     * Works for MySQL, PostgreSQL, and SQLite.
     */
    protected function usesSlugUidColumn(string $column): bool
    {
        // 1️⃣ If column is explicitly fillable, assume it exists
        if (in_array($column, $this->fillable ?? [])) {
            return true;
        }

        // 2️⃣ If the model already has the attribute (from database), it's present
        if (array_key_exists($column, $this->getAttributes())) {
            return true;
        }

        // 3️⃣ Fall back to schema check if necessary (for new models without attributes)
        try {
            return Schema::hasColumn($this->getTable(), $column);
        } catch (\Exception $e) {
            return false; // Safely handle SQLite memory tables or other DB exceptions
        }
    }

    /**
     * Allow models to override UID prefix.
     */
    public function getUidPrefix(): string
    {
        return property_exists($this, 'uid_prefix')
            ? $this->uid_prefix
            : config('sluguid.uid.prefix', Str::slug(class_basename($this)));
    }

    /**
     * Allow models to override sequence prefix.
     */
    public function getSequencePrefix(): string
    {
        return property_exists($this, 'sequence_prefix')
            ? $this->sequence_prefix
            : config('sluguid.sequence.prefix', strtoupper(Str::substr(class_basename($this), 0, 3)));
    }

    /**
     * Allow models to override sequence padding.
     */
    public function getSequencePadding(): int
    {
        return property_exists($this, 'sequence_padding')
            ? $this->sequence_padding
            : config('sluguid.sequence.padding', 4);
    }
}
