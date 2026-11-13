<?php

use Baspa\Timezones\Timezones;
use Illuminate\Support\Facades\Validator;

it('returns identifiers that pass Laravel\'s timezone rule', function (Closure $identifiers, bool $includeGeneral, string $rule) {
    $failing = array_filter(
        $identifiers((new Timezones)->includeGeneral($includeGeneral)),
        fn (string $identifier) => Validator::make(['timezone' => $identifier], ['timezone' => $rule])->fails(),
    );

    expect($failing)->toBe([]);
})->with([
    'toArray()' => [fn (Timezones $timezones) => array_keys($timezones->toArray())],
    'toArray(grouped: true)' => [fn (Timezones $timezones) => array_merge(...array_values(array_map(array_keys(...), $timezones->toArray(grouped: true))))],
    'toArrayWithContinents()' => [fn (Timezones $timezones) => array_keys($timezones->toArrayWithContinents())],
])->with([
    'default list with timezone' => [false, 'timezone'],
    'includeGeneral() with timezone:all_with_bc' => [true, 'timezone:all_with_bc'],
]);

it('rejects GMT with the default timezone rule', function () {
    expect(Validator::make(['timezone' => 'GMT'], ['timezone' => 'timezone'])->fails())->toBeTrue();
});
