<?php

namespace Baspa\Timezones;

use Baspa\Timezones\Enums\HtmlEntity;
use DateTime;
use DateTimeZone;
use Exception;
use League\ISO3166\ISO3166;
use Locale;

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

    /** @var array<string, array<string, string>> */
    protected $countryNamesByLocale = [];

    public function includeGeneral(bool $include = true): self
    {
        $this->includeGeneralTimezones = $include;

        return $this;
    }

    public function showCountry(bool $show = true, ?string $locale = null): self
    {
        $this->showCountry = $show;
        $this->countryLocale = $locale;

        return $this;
    }

    public function toArray(bool $grouped = false, bool $htmlencode = true): array
    {
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

    private function toArrayUngrouped(bool $htmlencode = true): array
    {
        $list = [];

        foreach ($this->loadContinents() as $continent => $timezoneGroup) {
            $timezones = DateTimeZone::listIdentifiers(timezoneGroup: $timezoneGroup);

            foreach ($timezones as $timezone) {
                $list[$timezone] = $this->formatTimezone(
                    timezone: $timezone,
                    cutOffContinent: $continent,
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
        $this->continents = array_diff_key(
            $this->continents,
            array_flip(array: $continents)
        );

        return $this;
    }

    public function showOffset(bool $showOffset = true): self
    {
        $this->showOffset = $showOffset;

        return $this;
    }

    protected function loadContinents(): array
    {
        return $this->continents;
    }

    protected function formatTimezone(string $timezone, ?string $cutOffContinent = null, bool $htmlencode = true): string
    {
        $displayedTimezone = empty($cutOffContinent) ?
            $timezone :
            substr(string: $timezone, offset: strlen((string) $cutOffContinent) + 1);

        $normalizedTimezone = $this->normalizeTimezone(timezone: $displayedTimezone);

        // Add country name if enabled
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

        // Format offset consistently with formatTimezoneWithContinent
        $search = ['-', '+'];
        $replace = $htmlencode ? [HtmlEntity::MINUS->value, HtmlEntity::PLUS->value] : ['-', '+'];
        $formattedSign = str_replace($search, $replace, substr($offset, 0, 1));
        $timeOffset = substr($offset, 1);

        $normalizedOffset = $htmlencode
            ? $formattedSign.HtmlEntity::WHITESPACE->value.$timeOffset
            : $formattedSign.' '.$timeOffset;

        $separator = $this->formatSeparator(htmlencode: $htmlencode);

        return '('.$this->offsetPrefix.HtmlEntity::WHITESPACE->value.$normalizedOffset.')'.$separator.$normalizedTimezone;
    }

    protected function formatOffset(string $offset, bool $htmlencode = true): string
    {
        $search = ['-', '+'];
        $replace = $htmlencode ? [HtmlEntity::MINUS->value, HtmlEntity::PLUS->value] : ['-', '+'];

        return str_replace(
            search: $search,
            replace: $replace,
            subject: $offset
        );
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
        $list = [];

        foreach ($this->loadContinents() as $continent => $timezoneGroup) {
            $timezones = DateTimeZone::listIdentifiers(timezoneGroup: $timezoneGroup);

            foreach ($timezones as $timezone) {
                $list[$timezone] = $this->formatTimezoneWithContinent(
                    timezone: $timezone,
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

    /**
     * Format timezone string with continent name preserved
     */
    protected function formatTimezoneWithContinent(string $timezone, bool $htmlencode = true): string
    {
        // Keep the full timezone identifier (e.g., Europe/Amsterdam)
        $normalizedTimezone = $this->normalizeTimezone(timezone: $timezone);

        // Add country name if enabled
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

        // Format offset with consistent width - pad the offset to ensure alignment
        $search = ['-', '+'];
        $replace = $htmlencode ? [HtmlEntity::MINUS->value, HtmlEntity::PLUS->value] : ['-', '+'];
        $formattedSign = str_replace($search, $replace, substr($offset, 0, 1));
        $timeOffset = substr($offset, 1); // Get the time part (e.g., "00:00" or "03:00")

        // Pad the entire offset string to a consistent width (sign + space + HH:MM = 7 chars visible)
        // Use str_pad to ensure the offset portion is always the same width
        $offsetString = $formattedSign.HtmlEntity::WHITESPACE->value.$timeOffset;

        // Build with consistent spacing - add extra padding after to ensure alignment
        $normalizedOffset = $htmlencode
            ? $offsetString
            : ' '.$formattedSign.' '.$timeOffset;

        $separator = $this->formatSeparator(htmlencode: $htmlencode);

        return '('.$this->offsetPrefix.HtmlEntity::WHITESPACE->value.$normalizedOffset.')'.$separator.$normalizedTimezone;
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

        return $this->countryNames(locale: $this->resolveCountryLocale())[$location['country_code']] ?? null;
    }

    protected function formatCountryName(string $countryName, bool $htmlencode = true): string
    {
        return $htmlencode ? htmlspecialchars($countryName, ENT_QUOTES) : $countryName;
    }

    protected function resolveCountryLocale(): string
    {
        if ($this->countryLocale !== null) {
            return $this->countryLocale;
        }

        if (function_exists('app') && app()->bound('config')) {
            return app()->getLocale();
        }

        return 'en';
    }

    /**
     * @return array<string, string>
     */
    protected function countryNames(string $locale): array
    {
        if (isset($this->countryNamesByLocale[$locale])) {
            return $this->countryNamesByLocale[$locale];
        }

        $names = array_column((new ISO3166)->all(), 'name', 'alpha2');

        if ($this->intlIsAvailable()) {
            foreach ($names as $countryCode => $name) {
                $names[$countryCode] = $this->localizedCountryName($countryCode, $locale) ?? $name;
            }
        }

        return $this->countryNamesByLocale[$locale] = $names;
    }

    protected function intlIsAvailable(): bool
    {
        return class_exists(Locale::class);
    }

    protected function localizedCountryName(string $countryCode, string $locale): ?string
    {
        $name = Locale::getDisplayRegion('-'.$countryCode, $locale);

        // ICU returns the code itself for a region it has no name for.
        return $name === false || $name === '' || $name === $countryCode ? null : $name;
    }
}
