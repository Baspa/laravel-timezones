<?php

use Baspa\Timezones\Facades\Timezones;

it('resolves the facade to the timezones service', function () {
    expect(Timezones::getFacadeRoot())->toBeInstanceOf(Baspa\Timezones\Timezones::class);
});

it('can get timezones through the facade', function () {
    $list = Timezones::toArray();

    expect($list)->toBeArray()->toHaveKey('Europe/Amsterdam');
});

it('does not keep fluent configuration between facade calls', function () {
    Timezones::excludeContinents(['Europe'])->showOffset(false)->toArray();

    $list = Timezones::toArray(htmlencode: false);

    expect($list)->toHaveKey('Europe/Amsterdam')
        ->and($list['Europe/Amsterdam'])->toStartWith('(GMT/UTC');
});

it('does not apply an unchained facade setter to later calls', function () {
    Timezones::showCountry();

    expect(Timezones::toArray()['Europe/Amsterdam'])->not->toContain('Netherlands');
});

it('can chain fluent methods on the facade', function () {
    $list = Timezones::excludeContinents(['Africa'])->showOffset(false)->toArray();

    expect($list)->not->toHaveKey('Africa/Cairo')
        ->and($list['Europe/Amsterdam'])->toBe('Amsterdam');
});
