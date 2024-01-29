<?php

namespace Baspa\Timezones\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Baspa\Timezones\Timezones
 */
class Timezones extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Baspa\Timezones\Timezones::class;
    }
}
