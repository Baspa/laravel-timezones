<?php

use Baspa\Timezones\Timezones;
use Illuminate\Support\Facades\Validator;

it('returns identifiers that pass Laravel\'s timezone rule', function (bool $includeGeneral, string $rule) {
    $identifiers = array_keys((new Timezones)->includeGeneral($includeGeneral)->toArray());

    $failing = array_filter(
        $identifiers,
        fn (string $identifier) => Validator::make(['timezone' => $identifier], ['timezone' => $rule])->fails(),
    );

    expect($failing)->toBe([]);
})->with([
    'default list with timezone' => [false, 'timezone'],
    'includeGeneral() with timezone:all_with_bc' => [true, 'timezone:all_with_bc'],
]);
