<?php

namespace AreiaLab\SlugUid;

use AreiaLab\SlugUid\Services\Slug;
use AreiaLab\SlugUid\Services\Uid;
use AreiaLab\SlugUid\Services\Sequence;
use Illuminate\Database\Eloquent\Model;

class SlugUidManager
{
    protected ?Slug $slugService = null;
    protected ?Uid $uidService = null;
    protected ?Sequence $sequenceService = null;

    /**
     * Get the slug service instance.
     */
    protected function slugService(): Slug
    {
        return $this->slugService ??= new Slug();
    }

    /**
     * Get the UID service instance.
     */
    protected function uidService(): Uid
    {
        return $this->uidService ??= new Uid();
    }

    /**
     * Get the sequence service instance.
     */
    protected function sequenceService(): Sequence
    {
        return $this->sequenceService ??= new Sequence();
    }

    /** ---------------- Slug ---------------- **/

    public function slug(string $value): string
    {
        return $this->slugService()->slug($value);
    }

    public function slugFromModel(Model|string $model): string
    {
        return $this->slugService()->slugFromModel($model);
    }

    public function uniqueSlug(Model|string $model, string $value, ?int $id = null): string
    {
        return $this->slugService()->uniqueSlug($model, $value, $id);
    }

    /** ---------------- UID ---------------- **/

    public function uid(?string $prefix = null): string
    {
        return $this->uidService()->uid($prefix);
    }

    public function uniqueUid(Model|string $model, ?string $prefix = null): string
    {
        return $this->uidService()->uniqueUid($model, $prefix);
    }

    /** ---------------- Sequence ---------------- **/

    public function sequence(Model|string $model, ?string $prefix = null, ?int $padding = null): string
    {
        return $this->sequenceService()->sequence($model, $prefix, $padding);
    }

    /** ---------------- Assign All ---------------- **/

    public function assign(Model|string $model): Model
    {
        if (is_string($model)) {
            $model = new $model;
        }

        $this->assignSlug($model);
        $this->assignUid($model);
        $this->assignSequence($model);

        return $model;
    }

    /** ---------------- Helpers ---------------- **/

    protected function assignSlug(Model $model): void
    {
        $col = $model->slug_column ?? config('sluguid.slug.column', 'slug');
        if (empty($model->{$col})) {
            $source = $model->title ?? $model->name ?? null;
            if ($source) {
                $model->{$col} = $this->uniqueSlug($model, $source);
            }
        }
    }

    protected function assignUid(Model $model): void
    {
        $col = $model->uid_column ?? config('sluguid.uid.column', 'uid');
        if (empty($model->{$col})) {
            $prefix = $model->uid_prefix ?? null;
            $model->{$col} = $this->uniqueUid($model, $prefix);
        }
    }

    protected function assignSequence(Model $model): void
    {
        $col = $model->sequence_column ?? config('sluguid.sequence.column', 'sequence');
        if (empty($model->{$col})) {
            $prefix = $model->sequence_prefix ?? null;
            $padding = $model->sequence_padding ?? null;
            $model->{$col} = $this->sequence($model, $prefix, $padding);
        }
    }
}
