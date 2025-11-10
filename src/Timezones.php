<?php

namespace Baspa\Timezones;

use Baspa\Timezones\Enums\HtmlEntity;
use DateTime;
use DateTimeZone;
use League\ISO3166\ISO3166;

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

    public function includeGeneral(bool $include = true): self
    {
        $this->includeGeneralTimezones = $include;

        return $this;
    }

    public function showCountry(bool $show = true): self
    {
        $this->showCountry = $show;

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
                $normalizedTimezone = $normalizedTimezone.' ('.$countryName.')';
            }
        }

        if (! $this->showOffset) {
            return $normalizedTimezone;
        }

        $notmalizedOffset = $this->formatOffset(
            offset: $this->getOffset($timezone),
            htmlencode: $htmlencode
        );

        $separator = $this->formatSeparator(htmlencode: $htmlencode);

        return '('.$this->offsetPrefix.$notmalizedOffset.')'.$separator.$normalizedTimezone;
    }

    protected function formatOffset(string $offset, bool $htmlencode = true): string
    {
        $search = ['-', '+'];
        $replace = $htmlencode ? [' '.HtmlEntity::MINUS->value.' ', ' '.HtmlEntity::PLUS->value.' '] : [' - ', ' + '];

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
        return $htmlencode ? str_repeat(
            string: HtmlEntity::WHITESPACE->value,
            times: 5
        ) : ' ';
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
                $normalizedTimezone = $normalizedTimezone.' ('.$countryName.')';
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
            ? HtmlEntity::WHITESPACE->value.$offsetString
            : ' '.$formattedSign.' '.$timeOffset;

        $separator = $this->formatSeparator(htmlencode: $htmlencode);

        return '('.$this->offsetPrefix.$normalizedOffset.')'.$separator.$normalizedTimezone;
    }

    /**
     * Get the country name for a timezone using ISO 3166
     */
    protected function getCountryName(string $timezone): ?string
    {
        try {
            $tz = new DateTimeZone($timezone);
            $location = $tz->getLocation();
            $countryCode = $location['country_code'] ?? null;

            if (! $countryCode) {
                return null;
            }

            $iso3166 = new ISO3166();
            $country = $iso3166->alpha2($countryCode);

            return $country['name'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
