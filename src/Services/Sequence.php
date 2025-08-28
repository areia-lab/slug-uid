<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;

class Sequence
{
    /**
     * Generate an incremental sequence value.
     */
    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
        if (empty($column)) {
            throw new \Exception('Sequence column is not defined.');
        }

        $prefix = $prefix ?? ($model->sequence_prefix ?? config('sluguid.sequence.prefix', 'ORD'));
        $padding = $padding ?? ($model->sequence_padding ?? config('sluguid.sequence.padding', 4));

        // Determine latest sequence safely for SQLite/MySQL/Postgres
        $latest = $model::select($column)
            ->whereNotNull($column)
            ->where($column, 'like', $prefix . '-%')
            ->orderByRaw("CAST(SUBSTR($column, LENGTH(?) + 1) AS INTEGER) DESC", [$prefix . '-'])
            ->value($column);

        $number = $latest ? (int) str_replace($prefix . '-', '', $latest) : 0;
        $next = str_pad($number + 1, $padding, '0', STR_PAD_LEFT);

        return $prefix . '-' . $next;
    }
}
