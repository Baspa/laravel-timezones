<?php

use Baspa\Timezones\Timezones;

it('keeps only the given continents', function () {
    $list = (new Timezones)->onlyContinents(['Europe', 'Asia'])->toArray(grouped: true);

    expect(array_keys($list))->toBe(['Asia', 'Europe']);
});

it('keeps the original continent order regardless of the given order', function () {
    $list = (new Timezones)->onlyContinents(['Pacific', 'Africa', 'Europe'])->toArray(grouped: true);

    expect(array_keys($list))->toBe(['Africa', 'Europe', 'Pacific']);
});

it('keeps only the given continents in the flat array', function () {
    $list = (new Timezones)->onlyContinents(['Europe'])->toArray();

    expect($list)->toHaveKey('Europe/Amsterdam')
        ->and(array_filter(array_keys($list), fn (string $timezone) => ! str_starts_with($timezone, 'Europe/')))->toBe([]);
});

it('keeps only the given continents in the array with continents', function () {
    $list = (new Timezones)->onlyContinents(['Europe'])->toArrayWithContinents();

    expect($list)->toHaveKey('Europe/Amsterdam')
        ->and(array_filter(array_keys($list), fn (string $timezone) => ! str_starts_with($timezone, 'Europe/')))->toBe([]);
});

it('still adds the general timezones when keeping only some continents', function () {
    $timezones = (new Timezones)->onlyContinents(['Europe'])->includeGeneral();

    expect(array_keys($timezones->toArray(grouped: true)))->toBe(['Europe', 'General'])
        ->and($timezones->toArray())->toHaveKeys(['UTC', 'GMT'])
        ->and($timezones->toArrayWithContinents())->toHaveKeys(['UTC', 'GMT']);
});

it('matches continent names case-insensitively when keeping continents', function (string $name) {
    $list = (new Timezones)->onlyContinents([$name])->toArray(grouped: true);

    expect(array_keys($list))->toBe(['Europe']);
})->with(['europe', 'EUROPE', 'eUrOpE']);

it('matches continent names case-insensitively when excluding continents', function (string $name) {
    $list = (new Timezones)->excludeContinents([$name])->toArray(grouped: true);

    expect($list)->not->toHaveKey('Europe')
        ->and($list)->toHaveCount(9);
})->with(['europe', 'EUROPE', 'eUrOpE']);

it('throws for an unknown continent', function (string $method) {
    expect(fn () => (new Timezones)->{$method}(['Europe', 'Eurpoe']))
        ->toThrow(
            InvalidArgumentException::class,
            'Unknown continent "Eurpoe". Valid continents are: Africa, America, Antarctica, Arctic, Asia, Atlantic, Australia, Europe, Indian, Pacific.'
        );
})->with(['onlyContinents', 'excludeContinents']);

it('composes chained continent filters', function () {
    $list = (new Timezones)
        ->onlyContinents(['Europe', 'Asia'])
        ->excludeContinents(['asia'])
        ->toArray(grouped: true);

    expect(array_keys($list))->toBe(['Europe']);
});

it('accepts known continents that an earlier filter already removed', function () {
    $excludedTwice = (new Timezones)->excludeContinents(['Europe'])->excludeContinents(['Europe'])->toArray(grouped: true);
    $excludedAfterOnly = (new Timezones)->onlyContinents(['Europe'])->excludeContinents(['Asia'])->toArray(grouped: true);

    expect($excludedTwice)->not->toHaveKey('Europe')
        ->and(array_keys($excludedAfterOnly))->toBe(['Europe']);
});
