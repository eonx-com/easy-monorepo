---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

This package is made to ease dispatching notifications within EonX Notification service, and works with this service
ONLY.

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-notification
```

<br>

### Configuration

In order to send notifications using the EonX Notification service, you need to register a Provider within that service.
Once the Provider created, set the API URL the Provider is registered against and keep the Provider API key and external
ID handy as they will be required to send messages.

See the configuration section for your framework of choice. (coming soon...)

<br>

### CacheConfigFinder

This package will automatically send API requests to the EonX Notification service to fetch the configuration for your
ApiKey and Provider. This will ensure your application has the latest configuration, however, this configuration can
easily be cached as it doesn't change often.

This is why this package provides you with a `EonX\EasyNotification\Config\CacheConfigFinder` to decorate the default
config finder service and cache the found config.

The `EonX\EasyNotification\Config\CacheConfigFinder` implementation relies on the [Symfony Cache Component][2] so you
will need to make sure it is installed in your project.

See how to override the config caching within your framework of choice section.

<br>

### Usage

Once configured this package will fetch the required configuration from the EonX Notification service and register
a `EonX\EasyNotification\Client\NotificationClientInterface` service into your DI container. You can then inject
this client into your own classes and send notifications:

```php
// src/Listener/UserCreatedListener.php

namespace App\Listener;

use App\Entity\User;use EonX\EasyNotification\Client\NotificationClientInterface;use EonX\EasyNotification\Message\RealTimeMessage;use EonX\EasyNotification\Provider\ConfigProviderInterface;

final class UserCreatedListener
{
    /**
     * @var \EonX\EasyNotification\Provider\ConfigProviderInterface
     */
    private $configFinder;

    /**
     * @var \EonX\EasyNotification\Client\NotificationClientInterface
     */
    private $client;

    public function __construct(ConfigProviderInterface $configFinder, NotificationClientInterface $client)
    {
        $this->configFinder = $configFinder;
        $this->client = $client;
    }

    public function created(User $user): void
    {
        /**
         * In real use case, those values should come from configuration.
         *
         * For multi-tenancies application, it is a best practice to store those values against each tenancy.
         */
        $config = $this->configFinder->find('my-api-key', 'my-provider-external-id');

        /**
         * Topics are "channels" to send notifications to.
         * Every clients subscribing to any of the topics will then receive the notification.
         */
        $topics = [$user->getExternalId()]; // Good practice is to have 1 topic per user

        /**
         * Body is the notification contents each client will receive.
         * Its structure is completely up to the application but should negotiated with subscribers.
         */
        $body = [
            'title' => 'Welcome!',
            'body' => \sprintf('We are to have you onboard %s', $user->getUsername()),
        ];

        $this->client
             ->withConfig($config) // Set config for next send
             ->send(RealTimeMessage::create($body, $topics)); // Send real time message
    }
}
```

[1]: https://getcomposer.org/

[2]: https://symfony.com/doc/current/components/cache.html
