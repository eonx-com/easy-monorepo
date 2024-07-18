---eonx_docs---
title: Logging Config In Laravel
weight: 2001
---eonx_docs---

The Laravel integration of this package allows you to define your logging config providers and logger configurators anywhere
and use the container to register them to be used by the logger factory.

It requires to tag the different config providers and logger configurators as follows:

- **HandlerConfigProviderInterface:** `easy_logging.handler_config_provider`
- **ProcessorConfigProviderInterface:** `easy_logging.processor_config_provider`
- **LoggerConfiguratorInterface:** `easy_logging.logger_configurator`

To make this process easier, this package provides you with `\EonX\EasyLogging\Bundle\Enum\ConfigTag`.

Here is an example on how to register a `HandlerConfigProviderInterface` within a service provider:

```php
namespace App\Providers;

use App\Logger\StreamHandlerConfigProvider;
use EonX\EasyLogging\Bundle\Enum\ConfigTag;
use Illuminate\Support\ServiceProvider;

final class LoggingServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register HandlerConfigProvider as a service
        $this->app->singleton(StreamHandlerConfigProvider::class);

        // Tag StreamHandlerConfigProvider service as a HandlerConfigProvider
        $this->app->tag(StreamHandlerConfigProvider::class, [ConfigTag::HandlerConfigProvider->value]);
    }
}
```
