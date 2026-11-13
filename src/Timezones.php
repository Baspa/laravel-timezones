<?php

namespace Baspa\Timezones;

use Baspa\Timezones\Enums\HtmlEntity;
use DateTime;
use DateTimeZone;
use Exception;
use League\ISO3166\ISO3166;
use Locale;
use ResourceBundle;

class Timezones
{
    /** @var array<string, int> */
    protected $continents = [
        'Africa' => DateTimeZone::AFRICA,
        'America' => DateTimeZone::AMERICA,
        'Antarctica' => DateTimeZone::ANTARCTICA,
        'Arctic' => DateTimeZone::ARCTIC,
        'Asia' => DateTimeZone::ASIA,
        'Atlantic' => DateTimeZone::ATLANTIC,
        'Australia' => DateTimeZone::AUSTRALIA,
        'Europe' => DateTimeZone::EUROPE,
        'Indian' => DateTimeZone::INDIAN,
        'Pacific' => DateTimeZone::PACIFIC,
    ];

    /** @var list<string>|null */
    protected ?array $onlyContinents = null;

    /** @var list<string> */
    protected array $excludedContinents = [];

    /** @var array<string, string> */
    protected $timezones = [];

    /** @var bool */
    protected $showOffset = true;

    /** @var string */
    protected $offsetPrefix = 'GMT/UTC';

    /** @var bool */
    protected $includeGeneralTimezones = false;

    /** @var bool */
    protected $showCountry = false;

    /** @var string|null */
    protected $countryLocale = null;

    /**
     * Resolved once per list, so each list follows the current app locale.
     *
     * @var string|null
     */
    protected $listCountryLocale = null;

    /** @var array<string, array<string, string|null>> */
    protected $countryNames = [];

    /** @var array<string, string>|null */
    protected $isoCountryNames = null;

    public function includeGeneral(bool $include = true): self
    {
        $this->includeGeneralTimezones = $include;

        return $this;
    }

    public function showCountry(bool $show = true, ?string $locale = null): self
    {
        $this->showCountry = $show;

        if ($locale !== null) {
            $this->countryLocale = $locale;
        }

        return $this;
    }

    public function toArray(bool $grouped = false, bool $htmlencode = true): array
    {
        $this->listCountryLocale = null;

        if ($grouped) {
            return $this->toArrayGrouped($htmlencode);
        }

        return $this->toArrayUngrouped($htmlencode);
    }

    private function toArrayGrouped(bool $htmlencode = true): array
    {
        $list = [];

        foreach ($this->loadContinents() as $continent => $timezoneGroup) {
            $timezones = DateTimeZone::listIdentifiers(timezoneGroup: $timezoneGroup);

            foreach ($timezones as $timezone) {
                $list[$continent][$timezone] = $this->formatTimezone(
                    timezone: $timezone,
                    cutOffContinent: $continent,
                    htmlencode: $htmlencode
                );
            }
        }

        if ($this->includeGeneralTimezones) {
            $list['General']['UTC'] = 'UTC';
            $list['General']['GMT'] = 'GMT';
        }

        return $list;
    }

    private function toArrayUngrouped(bool $htmlencode = true, bool $withContinents = false): array
    {
        $list = [];

        foreach ($this->loadContinents() as $continent => $timezoneGroup) {
            $timezones = DateTimeZone::listIdentifiers(timezoneGroup: $timezoneGroup);

            foreach ($timezones as $timezone) {
                $list[$timezone] = $this->formatTimezone(
                    timezone: $timezone,
                    cutOffContinent: $withContinents ? null : $continent,
                    htmlencode: $htmlencode
                );
            }
        }

        if ($this->includeGeneralTimezones) {
            $list['UTC'] = 'UTC';
            $list['GMT'] = 'GMT';
        }

        return $list;
    }

    public function excludeContinents(array $continents): self
    {
        $this->excludedContinents = [
            ...$this->excludedContinents,
            ...$this->resolveContinentNames($continents),
        ];

        return $this;
    }

    public function onlyContinents(array $continents): self
    {
        if ($continents === []) {
            throw new \InvalidArgumentException('Pass at least one continent to onlyContinents().');
        }

        $this->onlyContinents = $this->resolveContinentNames($continents);

        return $this;
    }

    /**
     * @return list<string>
     */
    private function resolveContinentNames(array $names): array
    {
        $validNames = array_keys($this->continents);
        $validNamesByLowercase = array_combine(array_map(strtolower(...), $validNames), $validNames);
        $resolvedNames = [];

        foreach ($names as $name) {
            if (! is_string($name)) {
                throw new \InvalidArgumentException(sprintf(
                    'Continent names must be strings like "Europe", %s given.',
                    get_debug_type($name)
                ));
            }

            $resolvedNames[] = $validNamesByLowercase[strtolower($name)] ?? throw new \InvalidArgumentException(sprintf(
                'Unknown continent "%s". Valid continents are: %s.',
                $name,
                implode(', ', $validNames)
            ));
        }

        return $resolvedNames;
    }

    public function showOffset(bool $showOffset = true): self
    {
        $this->showOffset = $showOffset;

        return $this;
    }

    /** @return array<string, int> */
    protected function loadContinents(): array
    {
        $continents = $this->onlyContinents === null
            ? $this->continents
            : array_intersect_key($this->continents, array_flip($this->onlyContinents));

        return array_diff_key($continents, array_flip($this->excludedContinents));
    }

    protected function formatTimezone(string $timezone, ?string $cutOffContinent = null, bool $htmlencode = true): string
    {
        $displayedTimezone = empty($cutOffContinent) ?
            $timezone :
            substr(string: $timezone, offset: strlen((string) $cutOffContinent) + 1);

        $normalizedTimezone = $this->normalizeTimezone(timezone: $displayedTimezone);

        if ($this->showCountry) {
            $countryName = $this->getCountryName($timezone);
            if ($countryName) {
                $normalizedTimezone = $normalizedTimezone.' ('.$this->formatCountryName(countryName: $countryName, htmlencode: $htmlencode).')';
            }
        }

        if (! $this->showOffset) {
            return $normalizedTimezone;
        }

        $offset = $this->getOffset($timezone);
        $sign = substr($offset, 0, 1);
        $time = substr($offset, 1);

        if ($htmlencode) {
            $sign = $sign === '-' ? HtmlEntity::MINUS->value : HtmlEntity::PLUS->value;
        }

        $space = $this->formatSeparator(htmlencode: $htmlencode);

        return '('.$this->offsetPrefix.$space.$sign.$space.$time.')'.$space.$normalizedTimezone;
    }

    protected function normalizeTimezone(string $timezone): string
    {
        $search = ['St_', '/', '_'];
        $replace = ['St. ', ' / ', ' '];

        return str_replace(
            search: $search,
            replace: $replace,
            subject: $timezone
        );
    }

    protected function formatSeparator(bool $htmlencode = true): string
    {
        return $htmlencode ? HtmlEntity::WHITESPACE->value : ' ';
    }

    protected function getOffset(string $timezone): string
    {
        $time = new DateTime('', new DateTimeZone($timezone));

        return $time->format('P');
    }

    /**
     * Get timezones array with full continent/city names preserved
     *
     * @param  bool  $htmlencode  Whether to use HTML entities
     */
    public function toArrayWithContinents(bool $htmlencode = true): array
    {
        $this->listCountryLocale = null;

        return $this->toArrayUngrouped(htmlencode: $htmlencode, withContinents: true);
    }

    protected function getCountryName(string $timezone): ?string
    {
        try {
            $location = (new DateTimeZone($timezone))->getLocation();
        } catch (Exception) {
            return null;
        }

        if ($location === false) {
            return null;
        }

        $countryCode = $location['country_code'];
        $locale = $this->listCountryLocale ??= $this->resolveCountryLocale();

        return $this->countryNames[$locale][$countryCode]
            ??= $this->localizedCountryName($countryCode, $locale) ?? $this->isoCountryName($countryCode);
    }

    protected function formatCountryName(string $countryName, bool $htmlencode = true): string
    {
        return $htmlencode ? htmlspecialchars($countryName, ENT_QUOTES) : $countryName;
    }

    protected function resolveCountryLocale(): string
    {
        $locale = $this->countryLocale ?? $this->appLocale();

        if (! $this->intlIsAvailable()) {
            return $locale;
        }

        // For a language it has no data for, ICU falls back to the server's default locale instead of English.
        $languageIsKnown = in_array(Locale::getPrimaryLanguage($locale), ResourceBundle::getLocales('') ?: [], true);

        return $languageIsKnown ? $locale : 'en';
    }

    protected function appLocale(): string
    {
        $locale = function_exists('app') && app()->bound('config') ? config('app.locale') : null;

        return is_string($locale) && $locale !== '' ? $locale : 'en';
    }

    protected function intlIsAvailable(): bool
    {
        return class_exists(Locale::class);
    }

    protected function localizedCountryName(string $countryCode, string $locale): ?string
    {
        if (! $this->intlIsAvailable()) {
            return null;
        }

        $name = Locale::getDisplayRegion('-'.$countryCode, $locale);

        // ICU returns the code itself for a region it has no name for.
        return $name === false || $name === '' || $name === $countryCode ? null : $name;
    }

    protected function isoCountryName(string $countryCode): ?string
    {
        $this->isoCountryNames ??= array_column((new ISO3166)->all(), 'name', 'alpha2');

        return $this->isoCountryNames[$countryCode] ?? null;
    }
}
