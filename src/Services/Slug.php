<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Slug
{
    /**
     * Generate a slug from a plain string.
     * 
     * @param string $value
     * @return string
     */
    public function slug(string $value): string
    {
        $separator = config('sluguid.slug.separator', '-');
        $maxLength = config('sluguid.slug.max_length', 150);

        return Str::slug(Str::limit($value, $maxLength, ''), $separator);
    }

    /**
     * Build a slug source string from model columns.
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @return string
     */
    public function slugFromModel(Model|string $model): string
    {
        $model = is_string($model) ? new $model : $model;

        $columns = config('sluguid.slug.source_columns', ['title']);

        return collect($columns)
            ->map(fn($col) => $model->{$col} ?? null)
            ->filter()
            ->join(' ');
    }

    /**
     * Generate a unique slug for a given model.
     * 
     * @param \Illuminate\Database\Eloquent\Model|string $model
     * @param string $value
     * @param mixed $id
     * @return string
     */
    public function uniqueSlug(Model|string $model, string $value, ?int $id = null): string
    {
        $model = is_string($model) ? new $model : $model;

        $separator = config('sluguid.slug.separator', '-');
        $maxLength = config('sluguid.slug.max_length', 150);
        $column    = $model->slug_column ?? config('sluguid.slug.column', 'slug');

        if (empty($column)) {
            return Str::slug(Str::limit($value, $maxLength, ''), $separator);
        }

        $baseSlug = Str::slug(Str::limit($value, $maxLength, ''), $separator);
        $slug     = $baseSlug;
        $counter  = 1;

        while (
            $model->newQuery()
            ->where($column, $slug)
            ->when($id, fn($q) => $q->whereKeyNot($id)) // exclude current record
            ->exists()
        ) {
            $slug = $baseSlug . $separator . $counter++;
        }

        return $slug;
    }
}
