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

describe('with intl', function () {
    beforeEach(function () {
        if (! extension_loaded('intl')) {
            $this->markTestSkipped('Requires ext-intl.');
        }
    });

    it('shows short country names', function () {
        $timezones = (new Timezones)->showCountry()->showOffset(false);

        expect($timezones->toArray()['Europe/London'])->toBe('London (United Kingdom)')
            ->and($timezones->toArray()['America/La_Paz'])->toBe('La Paz (Bolivia)')
            ->and($timezones->toArrayWithContinents()['America/New_York'])->toBe('America / New York (United States)');
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

    it('keeps a given locale when showCountry is called again without one', function () {
        $list = (new Timezones)->showCountry(locale: 'nl')->showCountry()->showOffset(false)->toArray();

        expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Nederland)');
    });

    it('keeps the script of a locale ICU resolves itself', function () {
        $list = (new Timezones)->showCountry(locale: 'zh_TW')->showOffset(false)->toArray();

        expect($list['Europe/Amsterdam'])->toBe('Amsterdam (荷蘭)');
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

    it('uses english for a locale ICU has no data for', function () {
        $timezones = new class extends Timezones
        {
            public function exposedResolveCountryLocale(): string
            {
                return $this->resolveCountryLocale();
            }
        };

        $timezones->showCountry(locale: 'xx')->showOffset(false);

        expect($timezones->exposedResolveCountryLocale())->toBe('en')
            ->and($timezones->toArray()['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
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

    it('does not look up the official names when intl has a name', function () {
        $timezones = new class extends Timezones
        {
            public int $isoLookups = 0;

            protected function isoCountryName(string $countryCode): ?string
            {
                $this->isoLookups++;

                return parent::isoCountryName($countryCode);
            }
        };

        $timezones->showCountry()->toArray();

        expect($timezones->isoLookups)->toBe(0);
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

it('shows english country names when the app locale is not set', function () {
    config()->set('app.locale', null);

    $list = (new Timezones)->showCountry()->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
});

it('resolves the locale once per list', function () {
    $timezones = new class extends Timezones
    {
        public int $localeResolutions = 0;

        protected function resolveCountryLocale(): string
        {
            $this->localeResolutions++;

            return parent::resolveCountryLocale();
        }
    };

    $timezones->showCountry()->toArray();
    $timezones->toArray(grouped: true);
    $timezones->toArrayWithContinents();

    expect($timezones->localeResolutions)->toBe(3);
});

it('falls back to the official english names without intl', function () {
    $list = timezonesWithoutIntl()->showCountry(locale: 'nl')->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)')
        ->and($list['Europe/London'])->toBe('London (United Kingdom of Great Britain and Northern Ireland)');
});

it('has no country name for a timezone without a location', function () {
    $timezones = new class extends Timezones
    {
        public function exposedGetCountryName(string $timezone): ?string
        {
            return $this->getCountryName($timezone);
        }
    };

    expect($timezones->exposedGetCountryName('+01:00'))->toBeNull();
});
