<?php

namespace AreiaLab\SlugUid\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Uid
{
    /**
     * Generate a UID with configurable drivers and optional prefix.
     */
    public function uid(?string $prefix = null): string
    {
        $driver = config('sluguid.uid.driver', 'uniqid');
        $length = config('sluguid.uid.length', 13);
        $prefix = $prefix ?? config('sluguid.uid.prefix', '');

        $raw = match ($driver) {
            'sha1' => substr(sha1(uniqid((string)mt_rand(), true)), 0, $length),
            'uuid4' => Str::uuid()->toString(),
            'nanoid' => Str::random($length),
            default => uniqid(),
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
        if (empty($uidCol)) {
            return $this->uid($prefix);
        }

        $prefix = $prefix ?? ($model->uid_prefix ?? config('sluguid.uid.prefix', 'UID'));
        $uid = $this->uid($prefix);

        while ($model::where($uidCol, $uid)->exists()) {
            $uid = $this->uid($prefix);
        }

        return $uid;
    }
}
