---eonx_docs---
title: Subscribe Info
weight: 1
---eonx_docs---

For clients to receive real_time messages, they need to subscribe to topics. These subscriptions are secure and require
signed JWTs provided by the EonX Notification service. Each JWT is issued for a restricted list of topics, so a client
can subscribe only to topics listed in its JWT.

Each client MUST request the generation of new subscribe info in order to be able to actually subscribe to topics.
Subscribe info is made of:

- **url**: The URL to subscribe to
- **topics**: The list of formatted topics to subscribe to
- **jwt**: The JWT to send as Bearer Token within the subscription request

<br>

### Provide Subscribe Info to clients

This package provides you with a `EonX\EasyNotification\Interfaces\SubscribeInfoFinderInterface` service making generating
the subscription info from the EonX Notification service easy. You can use dependency injection to use anywhere you like
in your code.

Here is an example how to generate subscription info within a Symfony Controller:

```php
// src/Http/Controller/SubscribeInfoController.php

namespace App\Http\Controller;

use EonX\EasyNotification\Provider\SubscribeInfoProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class SubscribeInfoController
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $providerExternalId;

    /**
     * @var \EonX\EasyNotification\Provider\SubscribeInfoProviderInterface
     */
    private $subscribeInfoFinder;

    public function __construct(
        string $notificationApiKey,
        string $notificationProvider,
        SubscribeInfoProviderInterface $subscribeInfoFinder
    ) {
        $this->apiKey = $notificationApiKey;
        $this->providerExternalId = $notificationProvider;
        $this->subscribeInfoFinder = $subscribeInfoFinder;
    }

    public function __invoke(Request $request)
    {
        // Please do better in your code! :)
        $body = \json_decode($request->getContent(), true);

        $subscribeInfo = $this->subscribeInfoFinder->provide(
            $this->apiKey,
            $this->providerExternalId,
            $body['topics'] // Get topics from request body
        );

        return new JsonResponse([
            'jwt' => $subscribeInfo->getJwt(),
            'topics' => $subscribeInfo->getTopics(),
            'url' => $subscribeInfo->getUrl(),
        ]);
    }
}
```
