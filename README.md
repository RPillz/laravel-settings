# Laravel app settings stored in the database

[![Latest Version on Packagist](https://img.shields.io/packagist/v/rpillz/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rpillz/laravel-settings)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/rpillz/laravel-settings/run-tests?label=tests)](https://github.com/rpillz/laravel-settings/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/rpillz/laravel-settings/Check%20&%20fix%20styling?label=code%20style)](https://github.com/rpillz/laravel-settings/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/rpillz/laravel-settings.svg?style=flat-square)](https://packagist.org/packages/rpillz/laravel-settings)

This package will set and get key-value settings from your Laravel app database. It was originally designed to work with a multi-tenant app, which is why the settings were stored in a database and not the standard config files.

There are probably other packages which do a similar thing. You should probably use one of those. ;)

## Installation

You can install the package via composer:

```bash
composer require rpillz/laravel-settings
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="settings-migrations"
php artisan migrate
```

You can publish the config file with:
```bash
php artisan vendor:publish --tag="settings-config"
```

This is the contents of the published config file:

```php
return [
    // these default settings will be used if there is nothing saved in the database using the same key.
    'defaults' => [

        'default-key' => 'Default Value',
        'is-this-true' => true,

    ]
];
```

## Usage

Primary usage is through a Facade.

```php

Settings::get('default-key'); // returns 'Default Value' from the config file.

Settings::set('default-key', 'My New Value'); // updates this setting in the database.

// Beware of cached values
Settings::get('default-key'); // will still return the original 'Default Value'.

// Get the latest value
Settings::fresh('default-key');
Settings::get('default-key', true); // passing a true with get() is the same as fresh()


```

You can add setting values with different type casts.

```php

Settings::set('default-key', 'My New Value', 'string'); // string is default. What goes in is what comes out.

Settings::set('default-key', 'My New Value', 'array'); // convert string into single array value

Settings::set('numbers', 'one, two, three', 'csv'); // convert csv string into array

Settings::set('is_active', 1, 'boolean'); // convert value into boolean

```

You can remove settings.

```php

Settings::forget('this-setting'); // temporarily nulls in the settings cache (for current page load only)

Settings::delete('this-setting'); // removes setting from the cache and the database.

```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Ryan Pilling](https://github.com/RPillz)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
