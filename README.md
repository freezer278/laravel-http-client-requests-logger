# Package for fast setup for requests and responses logging for laravel http-client-based api clients

[![Latest Version on Packagist](https://img.shields.io/packagist/v/vmorozov/laravel-http-client-requests-logger.svg?style=flat-square)](https://packagist.org/packages/vmorozov/laravel-http-client-requests-logger)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/vmorozov/laravel-http-client-requests-logger/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/vmorozov/laravel-http-client-requests-logger/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/vmorozov/laravel-http-client-requests-logger/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/vmorozov/laravel-http-client-requests-logger/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/vmorozov/laravel-http-client-requests-logger.svg?style=flat-square)](https://packagist.org/packages/vmorozov/laravel-http-client-requests-logger)

This is where your description should go. Limit it to a paragraph or two. Consider adding a small example.

## Installation

You can install the package via composer:

```bash
composer require vmorozov/laravel-http-client-requests-logger
```

You can publish and run the migrations with:

```bash
php artisan vendor:publish --tag="laravel-http-client-requests-logger-migrations"
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --tag="laravel-http-client-requests-logger-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="laravel-http-client-requests-logger-views"
```

## Usage

```php
$laravelHttpClientRequestsLogger = new VMorozov\LaravelHttpClientRequestsLogger();
echo $laravelHttpClientRequestsLogger->echoPhrase('Hello, VMorozov!');
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Volodymyr Morozov](https://github.com/v.morozov)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
