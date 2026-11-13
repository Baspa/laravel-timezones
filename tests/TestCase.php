<?php

namespace Baspa\Timezones\Tests;

use Baspa\Timezones\TimezonesServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            TimezonesServiceProvider::class,
        ];
    }
}
