<?php

namespace Baspa\Timezones;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Baspa\Timezones\Commands\TimezonesCommand;

class TimezonesServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('laravel-timezones')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_laravel-timezones_table')
            ->hasCommand(TimezonesCommand::class);
    }
}
