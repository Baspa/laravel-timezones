<?php

namespace Baspa\Timezones\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array toArray(bool $grouped = false, bool $htmlencode = true)
 * @method static array toArrayWithContinents(bool $htmlencode = true)
 * @method static \Baspa\Timezones\Timezones excludeContinents(array<int, string> $continents)
 * @method static \Baspa\Timezones\Timezones onlyContinents(array<int, string> $continents)
 * @method static \Baspa\Timezones\Timezones includeGeneral(bool $include = true)
 * @method static \Baspa\Timezones\Timezones showOffset(bool $showOffset = true)
 * @method static \Baspa\Timezones\Timezones offsetPrefix(string $prefix = 'GMT/UTC')
 * @method static \Baspa\Timezones\Timezones showCountry(bool $show = true, ?string $locale = null)
 * @method static \Baspa\Timezones\Timezones sortByOffset(bool $sort = true)
 *
 * @see \Baspa\Timezones\Timezones
 */
class Timezones extends Facade
{
    // The fluent setters mutate the instance, so a cached one would leak configuration into later calls.
    protected static $cached = false;

    protected static function getFacadeAccessor()
    {
        return \Baspa\Timezones\Timezones::class;
    }
}
