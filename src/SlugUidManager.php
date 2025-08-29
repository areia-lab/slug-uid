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
}
