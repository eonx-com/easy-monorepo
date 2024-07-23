---eonx_docs---
title: Installation
weight: 1
---eonx_docs---

This document describes the steps to install this package into a [Laravel][1] and/or [Lumen][2] application.

# Require package (Composer)

Laravel uses [Composer][3] to manage its dependencies. You can require this package as following:

```bash
$ composer require eonx/easy-pipeline
```

<br>

# Service Provider

Once the package required, you must tell your application to use it. Laravel uses service providers to do so, if you are
not familiar with this concept make sure to have a look at the [documentation][4].

This package provides you with a service provider which will register your pipelines into the services container
automatically. Make sure to register it:

```php
// config/app.php

'providers' => [
    // Other Service Providers...

    \EonX\EasyPipeline\Laravel\EasyPipelineServiceProvider::class,
],
```

<br>

# Config

To allow this package to work with your own pipelines you must let it know about your pipelines and
middleware providers structure. To do so you will use the configuration file
`laravel/config/easy-pipeline.php`. Copy/Paste this file into your `config` folder and then
update it with your own pipelines list.

```php
return [
    'pipelines' => [
        'pipeline-1' => \App\Pipelines\Pipeline1MiddlewareProvider::class,
        'pipeline-2' => \App\Pipelines\Pipeline2MiddlewareProvider::class,
    ],
];
```

Pipelines list must be an associative array where the keys are the names of your pipelines
and the values the class of your middleware provider for each pipeline.

<br>

# Lumen Actions Required

To install this package in a Lumen application the procedures are a bit different.

<br>

## Register Service Provider

In a Lumen application you must explicitly tell the application to register the package's service provider as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->register(\EonX\EasyPipeline\Laravel\EasyPipelineServiceProvider::class);
```

<br>

## Add Config

In a Lumen application you must explicitly tell the application to add the package's config as following:

```php
// bootstrap/app.php

$app = new Laravel\Lumen\Application(\dirname(__DIR__));

// Other actions...

$app->configure('easy-pipeline');
```

<br>

# Usage

Prior to be able to use the pipelines in your application you will need create your middleware providers for each
of your pipeline, for more information please have a look at the [documentation](middleware_providers.md).

That's it you're all setup! You're now able to use your pipelines anywhere you want, using dependency
injection or service locator (we strongly recommend using the first one haha). To do so, you need to use the
`EonX\EasyPipeline\Factory\PipelineFactoryInterface` to create your pipelines and their middleware list.

```php
use EonX\EasyPipeline\Factory\PipelineFactoryInterface;

final class MyClass
{
    // Dependency Injection
    public function processMyPipeline(PipelineFactoryInterface $pipelineFactory, $input)
    {
        $pipeline = $pipelineFactory->create('my-pipeline'); // Will be your configured pipeline implementation

        $output = $pipeline->process($input); // Return the potentially modified version of $input

        $logs = $pipeline->getLogs(); // Return the logs from last process
    }

    // Service Locator
    public function processMyPipelineToo($input)
    {
        // Will be your configured repository implementation as well
        $pipeline = $app->make(\EonX\EasyPipeline\Factory\PipelineFactoryInterface::class)->create('my-pipeline');

        $output = $pipeline->process($input); // Return the potentially modified version of $input

        $logs = $pipeline->getLogs(); // Return the logs from last process
    }
}
```

[1]: https://laravel.com/

[2]: https://lumen.laravel.com/

[3]: https://getcomposer.org/

[4]: https://laravel.com/docs/10.x/providers
