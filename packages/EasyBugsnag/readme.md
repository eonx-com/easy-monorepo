---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

# Introduction

This EasyBugsnag package provides a simple drop-in implementation of [Bugsnag][1] in your favourite PHP frameworks or
plain PHP app.

::: tip
The only configuration required for the package is to set the Bugsnag Integration API Key for your project. See
[Configuration](config.md) for more information.
:::

## Usage overview

Once installed in your PHP framework, this package will allow you to inject the Bugsnag client anywhere you like and
start notifying Bugsnag about your errors and exceptions.

For example:

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
        // Notify Bugsnag of your throwable
        $this->client->notifyException($throwable);
    }
}
```

### Client factory

The core functionality of the EasyBugsnag package is to create a Bugsnag client instance and make it available to your
application, so you can focus on notifying your errors/exceptions instead of the boilerplate Bugsnag setup. It uses a
**client factory** to do this. For more information, see [Client factory](client-factory.md).

### Configurators

The client factory allows you to set a collection of **client configurators**. Once the client has been instantiated,
the client factory will loop through the configurators, providing them the client instance to be configured. See
[Client configurators](configurators.md) for more information.

### Session tracking

Bugsnag can track the number of **sessions** that happen in your application, which enables Bugsnag to provide stability
scores for comparison across releases of your application. You can enable and configure session tracking for
EasyBugsnag. See [Session tracking](session-tracking.md) for more information.

### Worker information

For Symfony applications, you can include information about the worker as metadata in Bugsnag reports. See
[Worker information](worker-info.md) for more information.

### AWS information

You can include information about the AWS ECS Fargate task as metadata in Bugsnag reports. See
[AWS information](aws.md) for more information.

### SQL query logging

The EasyBugsnag package provides support for logging of SQL queries for Bugsnag. See [SQL query logging](sql-logging.md)
for more information.

[1]: https://docs.bugsnag.com/platforms/php/other/
