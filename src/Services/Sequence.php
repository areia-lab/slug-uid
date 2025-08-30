<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Sequence
{
    /**
     * Generate an incremental sequence value.
     */
    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null, ?bool $scoped = false, ?string $separator = '-'): string
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $column  = $model->sequence_column  ?? config('sluguid.sequence.column', 'sequence');
        $prefix  = $prefix ?? ($model->sequence_prefix ?? config('sluguid.sequence.prefix', 'ORD'));
        $padding = $padding ?? ($model->sequence_padding ?? config('sluguid.sequence.padding', 4));

        if (empty($column)) {
            throw new \Exception('Sequence column is not defined.');
        }

        $driver = DB::getDriverName();
        $table  = $model->getTable();

        $raw = null;

        switch ($driver) {
            case 'mysql':
                $raw = DB::table($table)
                    ->where($column, 'like', $prefix . '-%')
                    ->selectRaw("MAX(CAST(SUBSTRING($column, ? + 1) AS UNSIGNED)) as max_num", [strlen($prefix) + 1])
                    ->value('max_num');
                break;

            case 'pgsql':
                $raw = DB::table($table)
                    ->where($column, 'like', $prefix . '-%')
                    ->selectRaw("MAX(CAST(SUBSTRING($column FROM ? FOR 9999) AS INTEGER)) as max_num", [strlen($prefix) + 2])
                    ->value('max_num');
                break;

            case 'sqlite':
            default:
                // SQLite fallback: fetch all and compute max in PHP
                $raw = DB::table($table)
                    ->where($column, 'like', $prefix . '-%')
                    ->pluck($column)
                    ->map(fn($val) => (int) substr($val, strlen($prefix) + 1))
                    ->max();
                break;
        }

        $number = $raw ? (int) $raw : 0;

        $next = str_pad($number + 1, $padding, '0', STR_PAD_LEFT);

        $scoped = $model->sequence_scoped ?? config('sluguid.sequence.scoped', true);
        $separator = $scoped ? $model->sequence_separator ?? config('sluguid.sequence.separator', '-') : '';

        return $prefix . $separator . $next;
    }
}
