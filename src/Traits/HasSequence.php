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
                $prefix    = $model->getSequencePrefix();
                $padding   = $model->getSequencePadding();
                $scoped    = $model->getSequenceScoped();
                $separator = $scoped ? $model->getSequenceSeparator() : '';

                $model->{$seqCol} = SlugUid::sequence(
                    get_class($model),
                    $prefix,
                    $padding,
                    $scoped,
                    $separator,
                );
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

    public function getSequenceScoped(): bool
    {
        return property_exists($this, 'sequence_scoped')
            ? $this->sequence_scoped
            : config('sluguid.sequence.scoped', true);
    }

    public function getSequenceSeparator(): string
    {
        return property_exists($this, 'sequence_separator')
            ? $this->sequence_separator
            : config('sluguid.sequence.separator', '-');
    }
}
