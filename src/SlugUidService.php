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
    public function slugFromModel(Model|string $model): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

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
        $length = config('sluguid.uid.length', 16);
        $prefix = $prefix ?? config('sluguid.uid.prefix', '');

        $raw = match ($driver) {
            'sha1'   => substr(sha1(uniqid((string)mt_rand(), true)), 0, $length),
            'uuid4'  => Str::uuid()->toString(),
            'nanoid' => Str::random($length),
            default  => uniqid(),
        };

        return $prefix ? $prefix . '-' . $raw : $raw;
    }

    /**
     * Generate a UID unique for the model.
     */
    public function uniqueUid(Model|string $model, ?string $prefix = null): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $uidCol = $model->uid_column ?? config('sluguid.uid.column', 'uid');
        $uid    = $this->uid($prefix);

        while ($model::where($uidCol, $uid)->exists()) {
            $uid = $this->uid($prefix);
        }

        return $uid;
    }

    /**
     * Generate an incremental sequence value.
     *
     * Respects "scoped" config → sequences are per prefix if true.
     */
    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column  = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
        $prefix  = $prefix ?? config('sluguid.sequence.prefix', 'SEQ');
        $padding = $padding ?? config('sluguid.sequence.padding', 4);
        $scoped  = config('sluguid.sequence.scoped', true);

        $query = $model::query();

        if ($scoped) {
            $query->where($column, 'like', $prefix . '-%');
        }

        $latest = $query->orderByRaw("CAST(SUBSTR($column, LENGTH('$prefix-')+1) AS UNSIGNED) DESC")
            ->value($column);

        $lastNumber = $latest
            ? (int) str_replace($prefix . '-', '', $latest)
            : 0;

        $next = str_pad($lastNumber + 1, $padding, '0', STR_PAD_LEFT);

        return $prefix . '-' . $next;
    }

    /**
     * Automatically assign slug, uid, and sequence to model if explicitly defined.
     */
    public function assign(Model|string $model): Model
    {
        if (is_string($model)) {
            $model = new $model;
        }

        // Slug
        $slugCol = $model->slug_column ?? config('sluguid.slug.column', 'slug');
        if ($slugCol && empty($model->{$slugCol})) {
            $model->{$slugCol} = $this->uniqueSlug($model, $this->slugFromModel($model));
        }

        // UID
        $uidCol = $model->uid_column ?? config('sluguid.uid.column', 'uid');
        if ($uidCol && empty($model->{$uidCol})) {
            $model->{$uidCol} = $this->uniqueUid($model);
        }

        // Sequence
        $seqCol = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
        if ($seqCol && empty($model->{$seqCol})) {
            $model->{$seqCol} = $this->sequence($model);
        }

        return $model;
    }
}
