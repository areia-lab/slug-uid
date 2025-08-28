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
        $separator = config('sluguid.slug.separator', '-');
        $maxLength = config('sluguid.slug.max_length', 150);

        $slug = Str::slug(substr($value, 0, $maxLength), $separator);

        // If string passed, instantiate a new model
        if (is_string($model)) {
            $model = new $model;
        }

        $column = $model->slug_column ?? 'slug';
        $original = $slug;
        $i = 1;

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

        switch ($driver) {
            case 'sha1':
                return $prefix . substr(sha1(uniqid((string)mt_rand(), true)), 0, $length);
            case 'uuid4':
                return $prefix . Str::uuid()->toString();
            case 'nanoid':
                return $prefix . Str::random($length);
            default:
                return $prefix . uniqid();
        }
    }

    /**
     * Generate an incremental sequence value.
     *
     * @param \Illuminate\Database\Eloquent\Model|string $model
     */
    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        // Dynamically detect sequence column
        $column = $model->sequence_column
            ?? config('sluguid.sequence.column')
            ?? 'sequence';

        $prefix = $prefix ?? config('sluguid.sequence.prefix', 'ORD');
        $padding = $padding ?? config('sluguid.sequence.padding', 4);

        // Try to get the latest numeric sequence
        $latest = $model::select($column)
            ->where($column, 'like', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTR($column, LENGTH('$prefix-')+1) AS INTEGER) DESC")
            ->value($column);

        if ($latest) {
            $number = (int) str_replace($prefix . '-', '', $latest);
        } else {
            $number = 0;
        }

        $next = str_pad($number + 1, $padding, '0', STR_PAD_LEFT);

        return $prefix . '-' . $next;
    }

    /**
     * Automatically assign slug, uid, and sequence to model.
     */
    public function assign(Model $model): Model
    {
        $slugCol = $model->slug_column ?? 'slug';
        $uidCol  = $model->uid_column ?? 'uid';
        $seqCol  = $model->sequence_column ?? 'post_sequence';

        if (empty($model->{$slugCol})) {
            $model->{$slugCol} = $this->uniqueSlug($model, $this->slugFromModel($model));
        }

        if (empty($model->{$uidCol})) {
            $model->{$uidCol} = $this->uid();
        }

        if (empty($model->{$seqCol})) {
            $model->{$seqCol} = $this->sequence($model);
        }

        return $model;
    }
}
