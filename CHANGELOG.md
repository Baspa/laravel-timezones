# Changelog

All notable changes to `laravel-timezones` will be documented in this file.

## v2.0.0 - 2026-11-13

### Breaking changes

* **Laravel 11 is no longer supported.** Laravel 11 is end-of-life and every 11.x release is now blocked by Packagist security advisories. Requires Laravel 12 or 13 and PHP 8.2+ (now tested up to PHP 8.5).
* **The facade resolves a fresh instance for every call.** Configuration such as `Timezones::excludeContinents([...])` or `Timezones::showCountry()` no longer leaks into later `Timezones::` calls. If you configured the facade once (e.g. in a service provider) and relied on that, chain the configuration where you call `toArray()`, or inject your own configured `Baspa\Timezones\Timezones` instance.
* **`htmlencode: false` now returns real plain text.** It no longer contains `&#160;`, and `toArrayWithContinents()` no longer adds an extra space: `(GMT/UTC + 01:00) Amsterdam`. HTML output is unchanged.
* **`showCountry()` uses short, localized names** when ext-intl is installed (`United Kingdom` instead of `United Kingdom of Great Britain and Northern Ireland`, `Niederlande` with a German app locale). Without intl it falls back to the ISO 3166 names. Country names are HTML-escaped in HTML output.
* **Unknown continent names now throw an `InvalidArgumentException`** in `excludeContinents()` and the new `onlyContinents()`. Names are matched case-insensitively, so `'europe'` now works.
* The protected methods `formatTimezoneWithContinent()` and `formatOffset()` are removed; `formatTimezone()` is the single formatter. This only matters if you extend the `Timezones` class.

### New

* `onlyContinents(['Europe', 'Asia'])` keeps only the given continents.
* `sortByOffset()` sorts by current UTC offset (UTC-11 first, UTC+14 last), also within each group.
* `offsetPrefix('UTC')` changes the `GMT/UTC` prefix; `offsetPrefix('')` leaves it out.
* `showCountry(locale: 'nl')` picks the language of the country names.
* README: validating submitted values with Laravel's `timezone` rule (use `timezone:all_with_bc` together with `includeGeneral()`, because of `GMT`).

### What's Changed

* chore: housekeeping (PHP 8.5 CI, CI on PRs, skeleton cleanup, README) by @Baspa in https://github.com/Baspa/laravel-timezones/pull/45
* fix: resolve a fresh instance for every facade call by @Baspa in https://github.com/Baspa/laravel-timezones/pull/41
* fix: plain-text output without HTML entities by @Baspa in https://github.com/Baspa/laravel-timezones/pull/44
* feat: add onlyContinents() and validate continent names by @Baspa in https://github.com/Baspa/laravel-timezones/pull/43
* feat: short, localized country names by @Baspa in https://github.com/Baspa/laravel-timezones/pull/46
* feat: add sortByOffset() by @Baspa in https://github.com/Baspa/laravel-timezones/pull/42
* feat: make the offset prefix configurable by @Baspa in https://github.com/Baspa/laravel-timezones/pull/47
* test: increase coverage from 51% to 99% by @Baspa in https://github.com/Baspa/laravel-timezones/pull/30
* GitHub Actions dependency bumps by @dependabot[bot] in #26, #27, #28, #29, #31, #32 and #33

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.5.0...v2.0.0

## v1.5.0 - 2026-06-09

### What's Changed

* Update laravel/boost requirement from ^1.8 to ^2.4 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/22
* Bump dependabot/fetch-metadata from 2.5.0 to 3.0.0 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/21
* Bump dependabot/fetch-metadata from 3.0.0 to 3.1.0 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/23
* Bump actions/checkout from 4 to 6 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/17
* Fix CI: support Laravel 12/13, drop EOL Laravel 10, add Codecov by @Baspa in https://github.com/Baspa/laravel-timezones/pull/24

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.4.0...v1.5.0

## v1.4.0 - 2026-03-24

### What's Changed

* Bump dependabot/fetch-metadata from 2.3.0 to 2.4.0 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/10
* Bump aglipanci/laravel-pint-action from 2.5 to 2.6 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/12
* Bump stefanzweifel/git-auto-commit-action from 5 to 6 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/11
* Bump stefanzweifel/git-auto-commit-action from 6 to 7 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/14
* feat: add `toArrayWithContinents` by @Baspa in https://github.com/Baspa/laravel-timezones/pull/15
* feat: show country name by @Baspa in https://github.com/Baspa/laravel-timezones/pull/16
* Bump dependabot/fetch-metadata from 2.4.0 to 2.5.0 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/18
* Bump ramsey/composer-install from 3 to 4 by @dependabot[bot] in https://github.com/Baspa/laravel-timezones/pull/19
* feat: add Laravel 13 support by @Baspa in https://github.com/Baspa/laravel-timezones/pull/20

### New Contributors

* @Baspa made their first contribution in https://github.com/Baspa/laravel-timezones/pull/15

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.3.0...v1.4.0

## v1.3.0 (Laravel 12 support) - 2025-02-26

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.2.0...v1.3.0

## v1.2.0 (Laravel 11 support) - 2024-12-14

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.1.1...v1.2.0

## v1.1.0 - 2024-03-07

**Full Changelog**: https://github.com/Baspa/laravel-timezones/compare/v1.0.2...v1.1.0
