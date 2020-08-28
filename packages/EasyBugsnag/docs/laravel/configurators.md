---eonx_docs---
title: Client Configurators In Laravel
weight: 2001
---eonx_docs---

To register additional `ClientConfigurators` in Laravel/Lumen, you will need to explicitly tag them within the
service container.

Here is an example on how to add the Laravel version within the runtime versions.

###### Create Your ClientConfigurator

Create a class extending `EonX\EasyBugsnag\Configurators\AbstractClientConfigurator`, and add your logic:

```php
// app/Bugsnag/RuntimeVersionConfigurator.php

namespace App\Bugsnag;

use Bugsnag\Client;
use EonX\EasyBugsnag\Configurators\AbstractClientConfigurator;
use Illuminate\Contracts\Foundation\Application;

final class RuntimeVersionConfigurator extends AbstractClientConfigurator
{
    /**
     * @var \Illuminate\Contracts\Foundation\Application
     */
    private $app;

    public function __construct(Application $app, ?int $priority = null) 
    {
        $this->app = $app;

        parent::__construct($priority);
    }

    public function configure(Client $client): void
    {
        $client->getConfig()->mergeDeviceData([
            'runtimeVersions' => ['laravel' => $this->app->version()],
        ]);
    }
}
```

<br>

###### Register It As A Service And Tag It

Then register your `ClientConfigurator` as a service and tag it using the `EonX\EasyBugsnag\Bridge\BridgeConstantsInterface`
constant here for you. This should be done in a `ServiceProvider`:

```php
// app/Providers/BugsnagServiceProvider.php

use App\Bugsnag\RuntimeVersionConfigurator;
use EonX\EasyBugsnag\Bridge\BridgeConstantsInterface; 
use Illuminate\Support\ServiceProvider;

final class BugsnagServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register your configurator as a service
        $this->app->singleton(RuntimeVersionConfigurator::class, function (): RuntimeVersionConfigurator {
            return new RuntimeVersionConfigurator($this->app); 
        });

        // Tag it as a ClientConfigurator
        $this->app->tag(RuntimeVersionConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
    }
}
```

Done! From now on, every Bugsnag Client created by the factory will have the `laravel` runtime version within its 
device metadata.
