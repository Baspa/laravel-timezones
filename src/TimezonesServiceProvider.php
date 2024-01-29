<?php

namespace Baspa\Timezones;

use Baspa\Timezones\Commands\TimezonesCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TimezonesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-timezones');
    }
}
