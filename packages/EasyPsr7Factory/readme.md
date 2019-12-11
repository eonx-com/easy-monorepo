<div align="center">
    <h1>EonX - EasyPsr7Factory</h1>
    <p>Provides an easy way to create PSR7 Request/Response from Symfony Request/Response.</p>
</div>

---

# Documentation

## Installation

The recommended way to install this package is to use [Composer][1].

```bash
$ composer require eonx/psr7-factory
```

## How it works

You are working on a PHP application using the well known [Symfony HttpFoundation Component][2] and you want to
implement some logic which can be used in any other PHP application using Request/Response? This package is for you!

The EasyPsr7Factory will allow you to create a PSR-7 ServerRequestInterface implementation from a Symfony HttpFoundation
Request and then will also allow you to create a Symfony Response from a PSR-7 ResponseInterface.

## Usage

```php
use EonX\EasyPsr7Factory\EasyPsr7Factory;

 // Gives you a \Psr\Http\Message\ServerRequestInterface based on all values from the $symfonyRequest
$serverRequest = (new EasyPsr7Factory())->createRequest($symfonyRequest);

// Gives you a \Symfony\Component\HttpFoundation\Response based on all values from the $psr7Response
$symfonyResponse = (new EasyPsr7Factory())->createResponse($psr7Response);
```

## Laravel / Lumen

You like the idea and you're not using Symfony but [Laravel][3]/[Lumen][4] instead? Lucky you this is an easy use case :)
Laravel/Lumen Request/Response classes both extend the Symfony ones so this EasyPsr7Factory works for you too!


And just to make your day, it comes with a service provider allowing you to create requests and responses from anywhere
you want in your application :)

### Laravel
```php
// config/app.php

'providers' => [
    // Other Service Providers...
    
    \EonX\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider::class,
],
```

### Lumen

```php
// bootstrap/app.php

$app->register(\EonX\EasyPsr7Factory\Bridge\Laravel\EasyPsr7FactoryServiceProvider::class);
```

## Contributing

None of the existing implementations fit your needs? Don't hesitate to create an [Issue][5] about it 
or event a [Pull Request][6] to help us grow the package.

[1]: https://getcomposer.org/
[2]: https://symfony.com/doc/current/components/http_foundation.html
[3]: https://laravel.com/
[4]: https://lumen.laravel.com/
[5]: https://github.com/EonX/EonX/issues/new/choose
[6]: https://github.com/EonX/EonX/compare
