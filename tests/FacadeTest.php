<?php

use Baspa\Timezones\Facades\Timezones;

it('resolves the facade to the timezones service', function () {
    expect(Timezones::getFacadeRoot())->toBeInstanceOf(Baspa\Timezones\Timezones::class);
});

it('can get timezones through the facade', function () {
    $list = Timezones::toArray();

    expect($list)->toBeArray()->toHaveKey('Europe/Amsterdam');
});
