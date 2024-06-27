---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package allows you to create and configure [Monolog Loggers][2] in centralised and reusable way:

- Configure channels using PHP
- Control Handlers and Processors order
- Integration with popular frameworks (e.g. Laravel, Symfony)
- Discover Handlers and Processors automatically in your application

<br>

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-logging
```

<br>

### Usage

Here is a simple example on how to use the `LoggerFactoryInterface` to create loggers:

```php
// Instantiate the logger factory manually or use DI ...

$default = $loggerFactory->create(); // Calling create without arguments will create logger for default channel

$console = $loggerFactory->create('console'); // Create logger for console channel specifically
```

<br>

### Usage in Framework

The different bridge provided by this package will by default register the logger for your default channel in the
service container under the following service ids:

- `Psr\Log\LoggerInterface`
- `logger`

You can then use dependency injection anywhere you like!

Thanks to [Autowiring via setters][3] in Symfony, you can use `\EonX\EasyLogging\Logger\LoggerAwareTrait`
to simplify the injection of `Psr\Log\LoggerInterface`.

<br>

### Logger Configuration

The `LoggerFactoryInterface` allows you to set different collections of "config providers", each config can define:

- **channels:** if defined the config will be applied only to given channels, if `null` the config will be applied
  to all channels
- **priority:** define the order each config must be set on the logger instance, higher the priority later the config
  will be added to the logger instance

<br>

###### HandlerConfig

The `HandlerConfigInterface` allows you to configure `\Monolog\Handler\HandlerInterface` to be set loggers created by
the factory. Like other configs, it allows you to specify a list of channels this handler is for and, also a priority
to control when the handler must be executed.

To tell the logger factory about your `HandlerConfigInterface`, you must use a `HandlerConfigProviderInterface`. The
logger factory accepts a collection of providers via the `setHandlerConfigProviders()` method:

```php
use EonX\EasyLogging\Factory\LoggerFactory;

$handlerConfigProviders = [];

// Add your own handler config providers to $handlerConfigProviders ...

$loggerFactory = new LoggerFactory();

// Set your handler config providers on the logger factory
$loggerFactory->setHandlerConfigProviders($handlerConfigProviders);
```

<br>

Here is a simple example of a `HandlerConfigProviderInterface` to register a `StreamHandler`:

```php
namespace App\Logger;

use EonX\EasyLogging\Config\HandlerConfig;
use EonX\EasyLogging\Provider\HandlerConfigProviderInterface;
use Monolog\Handler\StreamHandler;

final class StreamHandlerConfigProvider implements HandlerConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Config\HandlerConfigInterface>
     */
    public function handlers(): iterable
    {
        /**
         * This method returns an iterable to make it easier to handle complex handler configs definition
         * But you can simply return an array if you want.
         */

        yield new HandlerConfig(new StreamHandler('php://stdout'));
    }
}
```

<br>

###### ProcessorConfig

The `ProcessorConfigInterface` allows you to configure `\Monolog\Processor\ProcessorInterface` to be set loggers created
by the factory. Like other configs, it allows you to specify a list of channels this handler is for and, also a priority
to control when the handler must be executed.

To tell the logger factory about your `ProcessorConfigInterface`, you must use a `ProcessorConfigProviderInterface`. The
logger factory accepts a collection of providers via the `setProcessorConfigProviders()` method:

```php
use EonX\EasyLogging\Factory\LoggerFactory;

$processorConfigProviders = [];

// Add your own processor config providers to $handlerConfigProviders ...

$loggerFactory = new LoggerFactory();

// Set your processor config providers on the logger factory
$loggerFactory->setProcessorConfigProviders($processorConfigProviders);
```

<br>

Here is a simple example of a `ProcessorConfigProviderInterface` to register a `TagProcessor`:

```php
namespace App\Logger;

use EonX\EasyLogging\Config\ProcessorConfig;
use EonX\EasyLogging\Provider\ProcessorConfigProviderInterface;
use Monolog\Processor\TagProcessor;

final class TagProcessorConfigProvider implements ProcessorConfigProviderInterface
{
    /**
     * @return iterable<\EonX\EasyLogging\Config\ProcessorConfigInterface>
     */
    public function processors(): iterable
    {
        /**
         * This method returns an iterable to make it easier to handle complex processor configs definition
         * But you can simply return an array if you want.
         */

        yield new ProcessorConfig(new TagProcessor(['tag-1', 'tag-2']));
    }
}
```

<br>

###### LoggerConfigurator

The `\Monolog\Logger` class has methods allowing you to configure it even more (e.g. using microseconds). To deal with
that, the logger factory accepts a collection of `LoggerConfiguratorInterface`.

To tell the logger factory about your `LoggerConfiguratorInterface`, you must call the `setLoggerConfigurators()` method:

```php
use EonX\EasyLogging\Factory\LoggerFactory;

$loggerConfigurators = [];

// Add your own logger configurators to $loggerConfigurators ...

$loggerFactory = new LoggerFactory();

// Set your logger configurators on the logger factory
$loggerFactory->setLoggerConfigurators($loggerConfigurators);
```

<br>

Here is a simple example of a `LoggerConfiguratorInterface` to use microseconds:

```php
namespace App\Logger;

use EonX\EasyLogging\Config\AbstractLoggingConfig;
use EonX\EasyLogging\Configurator\LoggerConfiguratorInterface;
use Monolog\Logger;

final class UseMicrosecondsLoggerConfigurator extends AbstractLoggingConfig implements LoggerConfiguratorInterface
{
    public function configure(Logger $logger) : void
    {
        $logger->useMicrosecondTimestamps(true);
    }
}
```

[1]: https://getcomposer.org/

[2]: https://github.com/Seldaek/monolog

[3]: https://symfony.com/doc/current/service_container/autowiring.html#autowiring-calls
