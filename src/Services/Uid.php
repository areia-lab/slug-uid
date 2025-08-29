<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Uid
{
    /**
     * Generate a UID with configurable drivers and optional prefix.
     *
     * Supported drivers: 'uniqid', 'sha1', 'uuid4', 'nanoid'.
     *
     * @param string|null $prefix Optional prefix for the UID.
     * @return string Generated UID.
     */
    public function uid(?string $prefix = null): string
    {
        $driver = config('sluguid.uid.driver', 'uniqid');
        $length = config('sluguid.uid.length', 16);
        $prefix = $prefix ?? config('sluguid.uid.prefix', '');

        // Generate raw UID based on driver
        $rawUid = match ($driver) {
            'sha1'    => substr(sha1(uniqid((string) mt_rand(), true)), 0, $length),
            'uuid4'   => Str::uuid()->toString(),
            'nanoid'  => Str::random($length),
            default   => uniqid(),
        };

        return $prefix !== '' ? $prefix . '-' . $rawUid : $rawUid;
    }

    /**
     * Generate a UID unique for the given model.
     *
     * @param Model|string $model The model instance or class name.
     * @param string|null $prefix Optional prefix for the UID.
     * @return string Unique UID.
     */
    public function uniqueUid(Model|string $model, ?string $prefix = null): string
    {
        // Instantiate model if class string is passed
        $model = is_string($model) ? new $model : $model;

        $uidColumn = $model->uid_column ?? config('sluguid.uid.column', 'uid');
        if (empty($uidColumn)) {
            return $this->uid($prefix);
        }

        $prefix = $prefix ?? ($model->uid_prefix ?? config('sluguid.uid.prefix', 'UID'));

        // Generate UID until it's unique
        do {
            $uid = $this->uid($prefix);
        } while ($model->newQuery()->where($uidColumn, $uid)->exists());

        return $uid;
    }
}
