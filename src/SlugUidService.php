<?php

namespace AreiaLab\SlugUid;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class SlugUidService
{
    /**
     * Generate a slug from a plain string.
     */
    public function slug(string $value): string
    {
        $separator = config('sluguid.slug.separator', '-');
        $maxLength = config('sluguid.slug.max_length', 150);

        return Str::slug(substr($value, 0, $maxLength), $separator);
    }

    /**
     * Build a slug source string from model columns.
     */
    public function slugFromModel(Model $model): string
    {
        $cols = config('sluguid.slug.source_columns', ['title']);
        $str = '';

        foreach ($cols as $col) {
            if (!empty($model->{$col})) {
                $str .= $model->{$col} . ' ';
            }
        }

        return trim($str);
    }

    /**
     * Generate a unique slug for a given model.
     *
     * @param \Illuminate\Database\Eloquent\Model|string $model
     */
    public function uniqueSlug(Model|string $model, string $value): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $separator = config('sluguid.slug.separator', '-');
        $maxLength = config('sluguid.slug.max_length', 150);

        $slug   = Str::slug(substr($value, 0, $maxLength), $separator);
        $column = $model->slug_column ?? config('sluguid.slug.column', 'slug');

        $original = $slug;
        $i        = 1;

        while ($model::where($column, $slug)->exists()) {
            $slug = $original . $separator . $i++;
        }

        return $slug;
    }

    /**
     * Generate a UID with configurable drivers.
     */
    public function uid(?string $prefix = null): string
    {
        $driver = config('sluguid.uid.driver', 'uniqid');
        $length = config('sluguid.uid.length', 13);
        $prefix = $prefix ?? config('sluguid.uid.prefix', '');

        return match ($driver) {
            'sha1'   => $prefix . substr(sha1(uniqid((string)mt_rand(), true)), 0, $length),
            'uuid4'  => $prefix . Str::uuid()->toString(),
            'nanoid' => $prefix . Str::random($length),
            default  => $prefix . uniqid(),
        };
    }

    /**
     * Generate an incremental sequence value.
     *
     * Works with MySQL, SQLite, PostgreSQL.
     */
    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column  = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
        $prefix  = $prefix ?? config('sluguid.sequence.prefix', 'SEQ');
        $padding = $padding ?? config('sluguid.sequence.padding', 4);

        // Get latest sequence matching the prefix
        $latest = $model::where($column, 'like', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTR($column, LENGTH('$prefix-')+1) AS UNSIGNED) DESC")
            ->value($column);

        $lastNumber = $latest
            ? (int) str_replace($prefix . '-', '', $latest)
            : 0;

        $next = str_pad($lastNumber + 1, $padding, '0', STR_PAD_LEFT);

        return $prefix . '-' . $next;
    }

    /**
     * Automatically assign slug, uid, and sequence to model if not set.
     */
    public function assign(Model|string $model): Model
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');
        $uidCol  = $model->uid_column ?? config('sluguid.uid.column', 'uid');
        $seqCol  = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');

        // Slug
        if ($slugCol && empty($model->{$slugCol})) {
            $model->{$slugCol} = $this->uniqueSlug($model, $this->slugFromModel($model));
        }

        // UID
        if ($uidCol && empty($model->{$uidCol})) {
            $model->{$uidCol} = $this->uid();
        }

        // Sequence
        if ($seqCol && empty($model->{$seqCol})) {
            $model->{$seqCol} = $this->sequence($model);
        }

        return $model;
    }
}
