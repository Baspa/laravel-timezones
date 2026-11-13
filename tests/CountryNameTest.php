<?php

use Baspa\Timezones\Timezones;
use Illuminate\Container\Container;

function timezonesWithoutIntl(): Timezones
{
    return new class extends Timezones
    {
        protected function intlIsAvailable(): bool
        {
            return false;
        }
    };
}

it('shows short country names', function () {
    $list = (new Timezones)->showCountry()->showOffset(false)->toArray();

    expect($list['Europe/London'])->toBe('London (United Kingdom)')
        ->and($list['America/La_Paz'])->toBe('La Paz (Bolivia)');
});

it('shows country names in the app locale', function () {
    app()->setLocale('nl');

    $list = (new Timezones)->showCountry()->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Nederland)')
        ->and($list['Europe/London'])->toBe('London (Verenigd Koninkrijk)');
});

it('shows country names in a given locale', function () {
    app()->setLocale('de');

    $list = (new Timezones)->showCountry(locale: 'nl')->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Nederland)');
});

it('follows the app locale when it changes between calls', function () {
    $timezones = (new Timezones)->showCountry()->showOffset(false);

    app()->setLocale('nl');
    $dutch = $timezones->toArray();

    app()->setLocale('en');
    $english = $timezones->toArray();

    expect($dutch['Europe/Amsterdam'])->toBe('Amsterdam (Nederland)')
        ->and($english['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
});

it('shows english country names outside a booted app', function () {
    Container::setInstance(new Container);

    try {
        $list = (new Timezones)->showCountry()->showOffset(false)->toArray();
    } finally {
        Container::setInstance($this->app);
    }

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
});

it('falls back to the official english names without intl', function () {
    $list = timezonesWithoutIntl()->showCountry(locale: 'nl')->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)')
        ->and($list['Europe/London'])->toBe('London (United Kingdom of Great Britain and Northern Ireland)');
});

it('does not use a region name that intl does not know', function () {
    $timezones = new class extends Timezones
    {
        public function exposedLocalizedCountryName(string $countryCode, string $locale): ?string
        {
            return $this->localizedCountryName($countryCode, $locale);
        }
    };

    expect($timezones->exposedLocalizedCountryName('QQ', 'en'))->toBeNull()
        ->and($timezones->exposedLocalizedCountryName('NL', 'en'))->toBe('Netherlands');
});

it('html-escapes country names in html output', function () {
    $timezones = (new Timezones)->showCountry()->showOffset(false);

    expect($timezones->toArray()['Europe/Sarajevo'])->toBe('Sarajevo (Bosnia &amp; Herzegovina)')
        ->and($timezones->toArrayWithContinents()['Europe/Sarajevo'])->toBe('Europe / Sarajevo (Bosnia &amp; Herzegovina)');
});

it('does not escape country names in plain output', function () {
    $timezones = (new Timezones)->showCountry()->showOffset(false);

    expect($timezones->toArray(htmlencode: false)['Europe/Sarajevo'])->toBe('Sarajevo (Bosnia & Herzegovina)')
        ->and($timezones->toArrayWithContinents(htmlencode: false)['Europe/Sarajevo'])->toBe('Europe / Sarajevo (Bosnia & Herzegovina)');
});
