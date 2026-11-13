<?php

use Baspa\Timezones\Timezones;

function currentUtcOffset(string $timezone): int
{
    return (new DateTimeZone($timezone))->getOffset(new DateTimeImmutable);
}

function expectSortedByOffset(array $identifiers): void
{
    $identifiers = array_values($identifiers);

    foreach (array_slice($identifiers, 1) as $index => $identifier) {
        $previous = $identifiers[$index];

        expect(currentUtcOffset($previous))->toBeLessThanOrEqual(currentUtcOffset($identifier));

        if (currentUtcOffset($previous) === currentUtcOffset($identifier)) {
            expect(strcmp($previous, $identifier))->toBeLessThan(0);
        }
    }
}

function expectAlphabetical(array $identifiers): void
{
    $sorted = $identifiers;
    sort($sorted);

    expect($identifiers)->toBe($sorted);
}

it('keeps the alphabetical order by default', function () {
    expectAlphabetical(array_keys((new Timezones)->toArray()));
    expectAlphabetical(array_keys((new Timezones)->toArrayWithContinents()));

    foreach ((new Timezones)->toArray(grouped: true) as $group) {
        expectAlphabetical(array_keys($group));
    }
});

it('keeps the alphabetical order when sorting by offset is disabled', function () {
    $list = (new Timezones)->sortByOffset()->sortByOffset(false)->toArray();

    expectAlphabetical(array_keys($list));
});

it('sorts the flat array by offset', function () {
    $list = (new Timezones)->sortByOffset()->toArray();

    expectSortedByOffset(array_keys($list));
    expect(currentUtcOffset((string) array_key_first($list)))->toBeLessThanOrEqual(-11 * 3600)
        ->and(array_key_last($list))->toBe('Pacific/Kiritimati');
});

it('only changes the order of the flat array', function () {
    $sorted = (new Timezones)->sortByOffset()->toArray();
    $default = (new Timezones)->toArray();

    ksort($sorted);
    ksort($default);

    expect($sorted)->toBe($default);
});

it('sorts by offset without html entities', function () {
    $list = (new Timezones)->sortByOffset()->toArray(htmlencode: false);

    expectSortedByOffset(array_keys($list));
});

it('sorts by offset when the offset is hidden', function () {
    $list = (new Timezones)->sortByOffset()->showOffset(false)->toArray();

    expectSortedByOffset(array_keys($list));
    expect(array_key_last($list))->toBe('Pacific/Kiritimati');
});

it('sorts general timezones as offset zero in the flat array', function () {
    $list = (new Timezones)->sortByOffset()->includeGeneral()->toArray();

    expectSortedByOffset(array_keys($list));
    expect($list)->toHaveKeys(['UTC', 'GMT'])
        ->and(array_key_last($list))->toBe('Pacific/Kiritimati');
});

it('sorts each group by offset and keeps the group order', function () {
    $list = (new Timezones)->sortByOffset()->includeGeneral()->toArray(grouped: true);
    $default = (new Timezones)->includeGeneral()->toArray(grouped: true);

    expect(array_keys($list))->toBe(array_keys($default))
        ->and(array_key_last($list))->toBe('General')
        ->and(array_key_last($list['Pacific']))->toBe('Pacific/Kiritimati');

    foreach ($list as $continent => $group) {
        expectSortedByOffset(array_keys($group));

        ksort($group);
        ksort($default[$continent]);
        expect($group)->toBe($default[$continent]);
    }
});

it('sorts the array with continents by offset', function () {
    $list = (new Timezones)->sortByOffset()->includeGeneral()->toArrayWithContinents();
    $default = (new Timezones)->includeGeneral()->toArrayWithContinents();

    expectSortedByOffset(array_keys($list));
    expect(array_key_last($list))->toBe('Pacific/Kiritimati');

    ksort($list);
    ksort($default);
    expect($list)->toBe($default);
});
