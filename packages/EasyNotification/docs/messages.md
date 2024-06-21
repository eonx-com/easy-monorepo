---eonx_docs---
title: Interact With Messages
weight: 2
---eonx_docs---

Once clients subscribe to topics, they will then receive messages in real time. However, it requires clients to have an
active subscription at the time the message is delivered.

So are all the message delivered when a client inactive lost!?

Lucky no! Every real time message are persisted within the EonX Notification service so clients can retrieve them at
later stage and interact with them. Let's see how you can allow clients to do so using the
`EonX\EasyNotification\Client\NotificationClientInterface` service in your code.

<br>

### Setting the Config on the NotificationClient

The `NotificationClientInterface` implementation requires the `EonX\EasyNotification\ValueObject\ConfigInterface` to
successfully interact with the EonX Notification service API.
You can get this `ConfigInterface` using the `EonX\EasyNotification\Interfaces\ConfigFinderInterface` service, and then
set it on the `NotificationClientInterace` using the `withConfig()` method:

```php
/** @var \EonX\EasyNotification\Provider\ConfigProviderInterface $configFinder */
/** @var \EonX\EasyNotification\Client\NotificationClientInterface $notificationClient */

$config = $configFinder->find('my-api-key', 'my-provider');

$notificationClient->withConfig($config);
```

::: warning
Once the config set on the NotificationClient, it will then be used for every following interactions
with the EonX Notification service API. In multi-tenancies application it can cause undesired behaviour, so be mindful
of how/when you set the Config on the NotificationClient.
:::

::: tip
Calling the `withConfig()` with `null` as argument will reset the NotificationClient enforcing to set a new Config
before using the NotificationClient again.
:::

<br>

All the following sections will be done assuming the `ConfigInterface` is already set
on the `NotificationClientInterace` service.

<br>

### Retrieve messages

The `NotificationClientInterace` allows you to retrieve message for specific topics. Optionally, you can pass an array
of options used by the HTTP client. The [Symfony HTTP Client][1] is used, checkout its documentation for the list of
available options.

```php
// src/Http/Controller/NotificationGetMessage.php

namespace App\Http\Controller;

use EonX\EasyNotification\Client\NotificationClientInterface;use Symfony\Component\HttpFoundation\JsonResponse;use Symfony\Component\HttpFoundation\Request;

final class NotificationGetMessage
{
    /**
     * @var \EonX\EasyNotification\Client\NotificationClientInterface
     */
    private $notificationClient;

    public function __construct(NotificationClientInterface $notificationClient)
    {
        $this->notificationClient = $notificationClient;
    }

    public function __invoke(Request $request)
    {
        // Please do better in your code! :)
        $body = \json_decode($request->getContent(), true);

        // Change how many messages to get per page
        $options = ['query' => ['perPage' => 100]];

        $response = $this->notificationClient->getMessages($body['topics'], $options);

        return new JsonResponse([
            'items' => $response['items'],
            'pagination' => $response['pagination'],
        ]);
    }
}
```

<br>

### Update messages status

The `NotificationClientInterace` allows you to update the status for specific messages. This is useful for the end user
to mark specific messages as read.

```php
// src/Http/Controller/NotificationUpdateMessage.php

namespace App\Http\Controller;

use EonX\EasyNotification\Client\NotificationClientInterface;use EonX\EasyNotification\Message\RealTimeMessage;use Symfony\Component\HttpFoundation\JsonResponse;use Symfony\Component\HttpFoundation\Request;

final class NotificationUpdateMessage
{
    /**
     * @var \EonX\EasyNotification\Client\NotificationClientInterface
     */
    private $notificationClient;

    public function __construct(NotificationClientInterface $notificationClient)
    {
        $this->notificationClient = $notificationClient;
    }

    public function __invoke(Request $request)
    {
        // Please do better in your code! :)
        $body = \json_decode($request->getContent(), true);

        // RealTimeMessage class provides you with constants for each available status
        $status = $body['status'] ?? RealTimeMessage::STATUS_READ;

        $this->notificationClient->updateMessagesStatus($body['messages'], $status);

        return new JsonResponse([], 204);
    }
}
```

<br>

### Delete messages

The `NotificationClientInterace` allows you to delete specific messages. This is useful for the end user
to remove messages from the list.

```php
// src/Http/Controller/NotificationDeleteMessage.php

namespace App\Http\Controller;

use EonX\EasyNotification\Client\NotificationClientInterface;use Symfony\Component\HttpFoundation\JsonResponse;

final class NotificationDeleteMessage
{
    /**
     * @var \EonX\EasyNotification\Client\NotificationClientInterface
     */
    private $notificationClient;

    public function __construct(NotificationClientInterface $notificationClient)
    {
        $this->notificationClient = $notificationClient;
    }

    public function __invoke(string $messageId)
    {
        $this->notificationClient->deleteMessage($messageId);

        return new JsonResponse([], 204);
    }
}
```

[1]: https://symfony.com/doc/current/http_client.html
