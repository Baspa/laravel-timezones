![Banner](/docs/banner.png)

# Laravel package to generate arrays of available timezones to be used in lists.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/baspa/laravel-timezones.svg?style=flat-square)](https://packagist.org/packages/baspa/laravel-timezones)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/baspa/laravel-timezones/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/baspa/laravel-timezones/actions?query=workflow%3Arun-tests+branch%3Amain)
[![Code Coverage](https://img.shields.io/codecov/c/github/baspa/laravel-timezones?style=flat-square)](https://codecov.io/gh/baspa/laravel-timezones)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/baspa/laravel-timezones/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/baspa/laravel-timezones/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/baspa/laravel-timezones.svg?style=flat-square)](https://packagist.org/packages/baspa/laravel-timezones)

The Laravel Timezones package is a convenient solution for Laravel developers who need to include a dropdown menu of timezones in their applications. This package simplifies the process of incorporating a timezone selection feature, saving developers valuable time and effort.

## Installation

You can install the package via composer:

```bash
composer require baspa/laravel-timezones
```

## Usage

### Timezones grouped by continent
```php
use Baspa\Timezones\Facades\Timezones;
// 
$groupedTimezones = Timezones::toArray(grouped: true);
```

### All timezones 
```php
use Baspa\Timezones\Facades\Timezones;
// 
$timezones = Timezones::toArray();
```

### Exclude continents
```php
use Baspa\Timezones\Facades\Timezones;
// 
$timezones = Timezones::excludeContinents(['Africa', 'America'])
    ->toArray();
```

### Show offset
```php
$timezones = Timezones::showOffset()->toArray();
// or
$timezones = Timezones::showOffset(showOffset: false)->toArray();
```

### Include general timezones
To include general timezones like GMT or UTC use the `includeGeneral` method.

```php
$timezones = Timezones::includeGeneral()->toArray();
```

### Show country names
To include country names alongside timezone labels use the `showCountry` method. With the [intl extension](https://www.php.net/manual/en/book.intl.php) installed, country names are short and in your app's locale (`app()->getLocale()`). Without intl, the package falls back to the official English names from [league/iso3166](https://github.com/thephpleague/iso3166), such as "United Kingdom of Great Britain and Northern Ireland".

```php
// Basic usage with country names
$timezones = Timezones::showCountry()->showOffset(false)->toArray();
// Output: "London (United Kingdom)"

// In a specific locale instead of the app locale
$timezones = Timezones::showCountry(locale: 'nl')->showOffset(false)->toArray();
// Output: "London (Verenigd Koninkrijk)"

// With full continent names preserved
$timezones = Timezones::showCountry()->showOffset(false)->toArrayWithContinents();
// Output: "Europe / London (United Kingdom)"

// Grouped by continent with country names
$timezones = Timezones::showCountry()->toArray(grouped: true);
```

Country names are HTML-escaped in the default HTML output (`Bosnia &amp; Herzegovina`) and left as-is with `htmlencode: false`.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Credits

- [Baspa](https://github.com/Baspa)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
