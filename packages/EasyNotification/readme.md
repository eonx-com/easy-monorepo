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

In order to send notifications to EonX Notification service, you need to create a Provider or reuse an existing one,
and add the required credentials into your application:

- `api_key`: The API key you can retrieve from the EonX Notification service
- `api_url`: The URL of the Eonx Notification service you're willing to use
- `provider`: Your Provider External ID from the EonX Notification service

<br>

### Usage

Once configured this package will fetch the required configuration from the EonX Notification service and register
a `EonX\EasyNotification\Interfaces\NotificationClientInterface` service into your DI container. You can then inject
this client into your own classes and send notifications:

```php
// src/Listener/UserCreatedListener.php

namespace App\Listener;

use App\Entity\User;
use EonX\EasyNotification\Interfaces\NotificationClientInterface;use EonX\EasyNotification\Messages\RealTimeMessage;

final class UserCreatedListener
{
    /**
     * @var \EonX\EasyNotification\Interfaces\NotificationClientInterface
     */
    private $client;

    public function __construct(NotificationClientInterface $client)
    {
        $this->client = $client;
    }

    public function created(User $user): void
    {
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

        $this->client->send(RealTimeMessage::create($body, $topics)); // Send real time message
    }
}
```

[1]: https://getcomposer.org/
