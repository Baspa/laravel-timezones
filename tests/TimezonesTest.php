<?php

use Baspa\Timezones\Timezones;

function exposedTimezones(): Timezones
{
    return new class extends Timezones
    {
        public function exposedFormatTimezone(string $timezone, ?string $cutOffContinent = null, bool $htmlencode = true): string
        {
            return $this->formatTimezone($timezone, $cutOffContinent, $htmlencode);
        }

        public function exposedGetCountryName(string $timezone): ?string
        {
            return $this->getCountryName($timezone);
        }

        public function exposedGetOffset(string $timezone): string
        {
            return $this->getOffset($timezone);
        }

        public function exposedNormalizeTimezone(string $timezone): string
        {
            return $this->normalizeTimezone($timezone);
        }
    };
}

it('can get a grouped array of timezones', function () {
    $timezones = new Timezones;

    $this->assertIsArray($timezones->toArray(grouped: true));
});

it('can get a grouped array of timezones with html entities', function () {
    $timezones = new Timezones;

    $this->assertIsArray($timezones->toArray(grouped: true, htmlencode: true));
});

it('can get a grouped array of timezones without html entities', function () {
    $timezones = new Timezones;

    $this->assertIsArray($timezones->toArray(grouped: true, htmlencode: false));
});

it('can get a flat array of timezones', function () {
    $timezones = new Timezones;

    $this->assertIsArray($timezones->toArray(grouped: false));
});

it('groups timezones by continent', function () {
    $list = (new Timezones)->toArray(grouped: true);

    expect($list)->toHaveKeys(['Africa', 'America', 'Europe', 'Pacific'])
        ->and($list['Europe'])->toHaveKey('Europe/Amsterdam');
});

it('formats timezones with html entities by default', function () {
    $list = (new Timezones)->toArray();

    expect($list['Europe/Amsterdam'])
        ->toMatch('/^\(GMT\/UTC&#160;(&#43;|&#8722;)&#160;\d{2}:\d{2}\)&#160;Amsterdam$/');
});

it('formats timezones without html entities', function () {
    $list = (new Timezones)->toArray(grouped: false, htmlencode: false);

    expect($list['Europe/Amsterdam'])
        ->toMatch('/^\(GMT\/UTC [+-] \d{2}:\d{2}\) Amsterdam$/');
});

it('keeps the html encoded format for every output method', function () {
    $timezones = new Timezones;

    expect($timezones->toArray()['Asia/Tokyo'])->toBe('(GMT/UTC&#160;&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArray()['America/Phoenix'])->toBe('(GMT/UTC&#160;&#8722;&#160;07:00)&#160;Phoenix')
        ->and($timezones->toArray(grouped: true)['Asia']['Asia/Tokyo'])->toBe('(GMT/UTC&#160;&#43;&#160;09:00)&#160;Tokyo')
        ->and($timezones->toArrayWithContinents()['Asia/Tokyo'])->toBe('(GMT/UTC&#160;&#43;&#160;09:00)&#160;Asia / Tokyo')
        ->and($timezones->toArrayWithContinents()['America/Phoenix'])->toBe('(GMT/UTC&#160;&#8722;&#160;07:00)&#160;America / Phoenix');
});

it('formats plain text labels for every output method', function () {
    $timezones = new Timezones;

    expect($timezones->toArray(htmlencode: false)['Asia/Tokyo'])->toBe('(GMT/UTC + 09:00) Tokyo')
        ->and($timezones->toArray(htmlencode: false)['America/Phoenix'])->toBe('(GMT/UTC - 07:00) Phoenix')
        ->and($timezones->toArray(grouped: true, htmlencode: false)['Asia']['Asia/Tokyo'])->toBe('(GMT/UTC + 09:00) Tokyo')
        ->and($timezones->toArrayWithContinents(htmlencode: false)['Asia/Tokyo'])->toBe('(GMT/UTC + 09:00) Asia / Tokyo')
        ->and($timezones->toArrayWithContinents(htmlencode: false)['America/Phoenix'])->toBe('(GMT/UTC - 07:00) America / Phoenix');
});

it('returns no html entities in plain text mode', function (array $labels) {
    expect(implode(PHP_EOL, $labels))->not->toContain('&#');
})->with([
    'flat' => fn () => (new Timezones)->showCountry()->toArray(htmlencode: false),
    'grouped' => fn () => array_merge(...array_values((new Timezones)->showCountry()->toArray(grouped: true, htmlencode: false))),
    'with continents' => fn () => (new Timezones)->showCountry()->toArrayWithContinents(htmlencode: false),
]);

it('normalizes underscores and St_ prefixes in timezone names', function () {
    $list = (new Timezones)->showOffset(false)->toArray();

    expect($list['America/New_York'])->toBe('New York')
        ->and($list['America/St_Johns'])->toBe('St. Johns');
});

it('can exclude continents', function () {
    $timezones = new Timezones;

    $excludedTimezones = $timezones->excludeContinents(['Africa', 'America'])->toArray(grouped: true);

    $this->assertIsArray($excludedTimezones);
    $this->assertArrayNotHasKey('Africa', $excludedTimezones);
    $this->assertArrayNotHasKey('America', $excludedTimezones);
    $this->assertArrayHasKey('Europe', $excludedTimezones);
});

it('can include general timezones', function () {
    $timezones = new Timezones;

    $includedTimezones = $timezones->includeGeneral()->toArray(grouped: false);

    $this->assertIsArray($includedTimezones);
    $this->assertArrayHasKey('UTC', $includedTimezones);
    $this->assertArrayHasKey('GMT', $includedTimezones);
});

it('can include general timezones in a grouped array', function () {
    $list = (new Timezones)->includeGeneral()->toArray(grouped: true);

    expect($list)->toHaveKey('General')
        ->and($list['General'])->toBe(['UTC' => 'UTC', 'GMT' => 'GMT']);
});

it('does not include general timezones by default', function () {
    $list = (new Timezones)->toArray(grouped: true);

    expect($list)->not->toHaveKey('General');
});

it('can disable general timezones explicitly', function () {
    $list = (new Timezones)->includeGeneral(false)->toArray();

    expect($list)->not->toHaveKey('UTC')
        ->and($list)->not->toHaveKey('GMT');
});

it('can hide the offset', function () {
    $list = (new Timezones)->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam');
});

it('can re-enable the offset', function () {
    $list = (new Timezones)->showOffset(false)->showOffset()->toArray();

    expect($list['Europe/Amsterdam'])->toStartWith('(GMT/UTC');
});

it('can show the country name', function () {
    $list = (new Timezones)->showCountry()->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
});

it('can show the country name combined with the offset', function () {
    $list = (new Timezones)->showCountry()->toArray();

    expect($list['Europe/Amsterdam'])
        ->toMatch('/^\(GMT\/UTC&#160;(&#43;|&#8722;)&#160;\d{2}:\d{2}\)&#160;Amsterdam \(Netherlands\)$/');
});

it('can show the country name in a grouped array', function () {
    $list = (new Timezones)->showCountry()->showOffset(false)->toArray(grouped: true);

    expect($list['Europe']['Europe/Amsterdam'])->toBe('Amsterdam (Netherlands)');
});

it('can disable the country name explicitly', function () {
    $list = (new Timezones)->showCountry(false)->showOffset(false)->toArray();

    expect($list['Europe/Amsterdam'])->toBe('Amsterdam');
});

it('can get timezones with continents preserved', function () {
    $list = (new Timezones)->toArrayWithContinents();

    expect($list['Europe/Amsterdam'])
        ->toMatch('/^\(GMT\/UTC&#160;(&#43;|&#8722;)&#160;\d{2}:\d{2}\)&#160;Europe \/ Amsterdam$/');
});

it('can get timezones with continents without html entities', function () {
    $list = (new Timezones)->toArrayWithContinents(htmlencode: false);

    expect($list['Europe/Amsterdam'])
        ->toMatch('/^\(GMT\/UTC [+-] \d{2}:\d{2}\) Europe \/ Amsterdam$/');
});

it('can get timezones with continents without the offset', function () {
    $list = (new Timezones)->showOffset(false)->toArrayWithContinents();

    expect($list['Europe/Amsterdam'])->toBe('Europe / Amsterdam');
});

it('can get timezones with continents and country names', function () {
    $list = (new Timezones)->showCountry()->showOffset(false)->toArrayWithContinents();

    expect($list['Europe/Amsterdam'])->toBe('Europe / Amsterdam (Netherlands)')
        ->and($list['America/New_York'])->toBe('America / New York (United States of America)');
});

it('can get timezones with continents including general timezones', function () {
    $list = (new Timezones)->includeGeneral()->toArrayWithContinents();

    expect($list['UTC'])->toBe('UTC')
        ->and($list['GMT'])->toBe('GMT');
});

it('can exclude continents from the array with continents', function () {
    $list = (new Timezones)->excludeContinents(['Europe'])->toArrayWithContinents();

    expect($list)->not->toHaveKey('Europe/Amsterdam')
        ->and($list)->toHaveKey('America/New_York');
});

it('formats a timezone without a cut off continent', function () {
    $formatted = exposedTimezones()->exposedFormatTimezone(
        timezone: 'Europe/Amsterdam',
        cutOffContinent: null,
        htmlencode: false
    );

    expect($formatted)->toMatch('/^\(GMT\/UTC [+-] \d{2}:\d{2}\) Europe \/ Amsterdam$/');
});

it('gets the offset for a timezone', function () {
    expect(exposedTimezones()->exposedGetOffset('Europe/Amsterdam'))
        ->toMatch('/^[+-]\d{2}:\d{2}$/');
});

it('normalizes timezone names', function () {
    $timezones = exposedTimezones();

    expect($timezones->exposedNormalizeTimezone('America/St_Johns'))->toBe('America / St. Johns')
        ->and($timezones->exposedNormalizeTimezone('Antarctica/DumontDUrville'))->toBe('Antarctica / DumontDUrville');
});

it('gets the country name for a timezone', function () {
    expect(exposedTimezones()->exposedGetCountryName('Europe/Amsterdam'))->toBe('Netherlands');
});

it('returns null for a timezone without a country', function () {
    expect(exposedTimezones()->exposedGetCountryName('UTC'))->toBeNull();
});

it('returns null for an invalid timezone', function () {
    expect(exposedTimezones()->exposedGetCountryName('Invalid/Timezone'))->toBeNull();
});
