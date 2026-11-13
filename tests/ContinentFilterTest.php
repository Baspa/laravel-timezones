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

it('applies chained continent filters regardless of their order', function (Timezones $timezones) {
    expect(array_keys($timezones->toArray(grouped: true)))->toBe(['Europe']);
})->with([
    'only, then exclude' => fn () => (new Timezones)->onlyContinents(['Europe', 'Asia'])->excludeContinents(['asia']),
    'exclude, then only' => fn () => (new Timezones)->excludeContinents(['asia'])->onlyContinents(['Europe', 'Asia']),
]);

it('replaces the kept continents on a repeated onlyContinents call', function () {
    $list = (new Timezones)->onlyContinents(['Asia'])->onlyContinents(['Europe'])->toArray(grouped: true);

    expect(array_keys($list))->toBe(['Europe']);
});

it('adds up repeated excludeContinents calls', function () {
    $list = (new Timezones)->excludeContinents(['Asia'])->excludeContinents(['Europe'])->toArray(grouped: true);

    expect($list)->not->toHaveKeys(['Asia', 'Europe'])
        ->and($list)->toHaveCount(8);
});

it('accepts known continents that an earlier filter already removed', function () {
    $excludedTwice = (new Timezones)->excludeContinents(['Europe'])->excludeContinents(['Europe'])->toArray(grouped: true);
    $excludedAfterOnly = (new Timezones)->onlyContinents(['Europe'])->excludeContinents(['Asia'])->toArray(grouped: true);

    expect($excludedTwice)->not->toHaveKey('Europe')
        ->and(array_keys($excludedAfterOnly))->toBe(['Europe']);
});

it('throws for a continent name that is not a string', function (string $method, mixed $name) {
    expect(fn () => (new Timezones)->{$method}([$name]))
        ->toThrow(InvalidArgumentException::class, 'Continent names must be strings like "Europe"');
})->with(['onlyContinents', 'excludeContinents'])->with([
    'null' => [null],
    'DateTimeZone constant' => [DateTimeZone::EUROPE],
    'nested array' => [['Europe']],
]);

it('throws when onlyContinents gets no continents', function () {
    expect(fn () => (new Timezones)->onlyContinents([]))
        ->toThrow(InvalidArgumentException::class, 'at least one continent');
});

it('validates continent names against the continents of a subclass', function () {
    $timezones = new class extends Timezones
    {
        protected $continents = [
            'Asia' => DateTimeZone::ASIA,
            'Europe' => DateTimeZone::EUROPE,
        ];
    };

    expect(array_keys($timezones->onlyContinents(['europe'])->toArray(grouped: true)))->toBe(['Europe'])
        ->and(fn () => $timezones->excludeContinents(['Africa']))
        ->toThrow(InvalidArgumentException::class, 'Valid continents are: Asia, Europe.');
});
