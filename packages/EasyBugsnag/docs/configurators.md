---eonx_docs---
title: 'Client configurators'
weight: 1002
---eonx_docs---

# Client configurators

The client factory allows you to set a collection of **client configurators**. Once the client has been instantiated,
the client factory will loop through the configurators, providing them the client instance to be configured.

A configurator is a PHP class implementing `EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface`. When used within
your favourite PHP framework, the configurators will be set on the factory for you so that any Bugsnag client will be
configured before being injected into your services. Each configurator must define a priority (an integer value) which
will be used to define the order of execution of the entire configurator collection.

For example, the following configurator sets the *release stage* attribute of the application data for Bugsnag:

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

## Default configurators

The package comes with a set of configurators that are enabled by default. You can disable the default configurators by
setting `use_default_configurators` to `false` in your configuration. See [Configuration](config.md).

The default configurators are:

- `BasicsConfigurator`: Sets basic information in the Bugsnag client:
  - The project root
  - The release stage
  - The strip path
- `RuntimeVersionConfigurator`: Sets the runtime and version in the *runtime versions* of the device data for Bugsnag:
  - For Symfony applications, sets the runtime to the value of the `runtime` configuration (`symfony` by default) and
    sets the version to the value of the `runtime_version` configuration (the Symfony runtime version).
  - For Laravel/Lumen applications, sets the runtime to either `lumen` or `laravel` as applicable and sets the version
    to the application version.
- `AwsEcsFargateConfigurator`: If the `aws_ecs_fargate.enabled` configuration is set to `true`, then the
  `AwsEcsFargateConfigurator` automatically resolves information about the AWS ECS Fargate task (`AvailabilityZone`,
  `Cluster`, `TaskARN` and `TaskDefinition`) and adds it as metadata to Bugsnag reports.

## Registering additional configurators

### Symfony

To register additional client configurators in Symfony, you simply register a new service implementing the
`EonX\EasyBugsnag\Interfaces\ClientConfiguratorInterface`. The EasyBugsnag package registers the interface for
auto-configuration by default, so you have nothing else to worry about.

### Laravel/Lumen

To register additional client configurators in Laravel/Lumen, you must explicitly tag them within the service container.

#### Example

The following example configurator adds the Laravel version to the *runtime versions* attribute of the device data for
Bugsnag.

Create a class extending `EonX\EasyBugsnag\Configurators\AbstractClientConfigurator` and add your version logic:

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

Register your client configurator as a service and tag it using the `EonX\EasyBugsnag\Bridge\BridgeConstantsInterface`
constant `TAG_CLIENT_CONFIGURATOR` provided for this purpose. This should be done in a service provider. For example:

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

        // Tag it as a client configurator
        $this->app->tag(RuntimeVersionConfigurator::class, [BridgeConstantsInterface::TAG_CLIENT_CONFIGURATOR]);
    }
}
```

Every Bugsnag client created by the client factory will now have the `laravel` runtime version within its device data.
