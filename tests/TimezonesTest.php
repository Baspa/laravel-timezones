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
