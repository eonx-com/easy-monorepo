---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package is a simple drop-in implementation of [Bugsnag][2] in your favourite PHP frameworks or plain PHP app.

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-bugsnag
```

<br>

### Usage

Once installed in your favourite PHP framework, this package will allow you to inject the Bugsnag Client anywhere you
like and start notifying your errors and exceptions:

```php
// src/Exception/Handler.php

namespace App\Exception;

use Bugsnag\Client;

final class ExceptionHandler
{
    /**
     * @var \Bugsnag\Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function report(\Throwable $throwable): void
    {
        // Notify bugsnag of your throwable
        $this->client->notifyException($throwable);
    }
}
```

<br>

### Client Factory

The core functionality of this package is to create a Bugsnag Client instance and make it available to your application,
so you can focus on notifying your errors/exceptions instead of the boilerplate setup. To do so, it uses a factory.

This factory implements `EonX\EasyBugsnag\Interfaces\ClientFactoryInterface` which is able to create the client from
just the api key you can find in your Bugsnag dashboard, handy! However, if needed you can set your own implementations
of the additional objects used by the Bugsnag Client such as:

- **HttpClient:** [Guzzle Client][3] used to send notifications to Bugsnag API
- **RequestResolver:** used to resolve the request information
- **ShutdownStrategy:** used to send bulk notifications while the application is shutting down

<br>

### Configurators

Additionally, the client factory allows you to set a collection of "configurators". Once the client instantiated, the
factory will loop through the configurators, providing them the client instance to be configured.

A configurator is a PHP class implementing `EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface`. When used within
your favourite PHP framework, the configurators will be set on the factory for you so any created client will be configured
before being injected into your services. Each configurator must define a priority, an integer value, which will be
used to define the order of execution of the entire collection.

Here is an example of a configurator to set the release stage:

```php
// src/Bugsnag/ReleaseStageConfigurator.php

namespace App\Bugsnag;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;

final class ReleaseStageConfigurator extends AbstractClientConfigurator
{
    public function configure(Client $bugsnag): void
    {
        $bugsnag->setReleaseStage('dev');
    }
}
```

[1]: https://getcomposer.org/
[2]: https://docs.bugsnag.com/platforms/php/other/
[3]: http://docs.guzzlephp.org/en/stable/
