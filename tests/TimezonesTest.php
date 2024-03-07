<?php

it('can get a grouped array of timezones', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $this->assertIsArray($timezones->toArray(grouped: true));
});

it('can get a grouped array of timezones with html entities', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $this->assertIsArray($timezones->toArray(grouped: true, htmlencode: true));
});

it('can get a grouped array of timezones without html entities', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $this->assertIsArray($timezones->toArray(grouped: true, htmlencode: false));
});

it('can get a flat array of timezones', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $this->assertIsArray($timezones->toArray(grouped: false));
});

it('can exclude continents', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $excludedTimezones = $timezones->excludeContinents(['Africa', 'America'])->toArray(grouped: false);

    $this->assertIsArray($excludedTimezones);
    $this->assertArrayNotHasKey('Africa', $excludedTimezones);
    $this->assertArrayNotHasKey('America', $excludedTimezones);
});

it('can include general timezones', function () {
    $timezones = new \Baspa\Timezones\Timezones();

    $includedTimezones = $timezones->includeGeneral()->toArray(grouped: false);

    $this->assertIsArray($includedTimezones);
    $this->assertArrayHasKey('UTC', $includedTimezones);
    $this->assertArrayHasKey('GMT', $includedTimezones);
});
