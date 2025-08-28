<?php

namespace AreiaLab\SlugUid\Traits;

use AreiaLab\SlugUid\Facades\SlugUid;
use Illuminate\Support\Str;

trait HasSequence
{
    protected static function bootHasSequence(): void
    {
        static::creating(function ($model) {
            $seqCol = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');

            if ($seqCol && empty($model->{$seqCol})) {
                $prefix  = $model->getSequencePrefix();
                $padding = $model->getSequencePadding();
                $model->{$seqCol} = SlugUid::sequence(get_class($model), $prefix, $padding);
            }
        });
    }

    public function getSequencePrefix(): string
    {
        return property_exists($this, 'sequence_prefix')
            ? $this->sequence_prefix
            : config('sluguid.sequence.prefix', strtoupper(Str::substr(class_basename($this), 0, 3)));
    }

    public function getSequencePadding(): int
    {
        return property_exists($this, 'sequence_padding')
            ? $this->sequence_padding
            : config('sluguid.sequence.padding', 4);
    }
}
