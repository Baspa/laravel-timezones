<?php

namespace Baspa\Timezones\Commands;

use Illuminate\Console\Command;

class TimezonesCommand extends Command
{
    public $signature = 'laravel-timezones';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
