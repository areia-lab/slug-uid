<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
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
        $slug = Str::slug(substr($value, 0, $maxLength), $separator);

        $column = $model->slug_column ?? config('sluguid.slug.column', 'slug');
        if (empty($column)) {
            return $slug;
        }

        $original = $slug;
        $i = 1;

        while ($model::where($column, $slug)->exists()) {
            $slug = $original . $separator . $i++;
        }

        return $slug;
    }
}
