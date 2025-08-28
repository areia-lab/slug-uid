<?php

namespace AreiaLab\SlugUid\Facades;

use Illuminate\Support\Facades\Facade;

class SlugUid extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'sluguid';
    }
}
