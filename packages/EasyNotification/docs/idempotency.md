---eonx_docs---
title: Idempotency
weight: 4
---eonx_docs---

The EonX Notification service can deliver a push or real time message only once, even if your application sends it
more than once (for example, when a job that sends notifications is retried). To do this, set an idempotency key on
the message.

<br>

### Set the idempotency key

```php
use EonX\EasyNotification\Message\PushMessage;
use EonX\EasyNotification\Message\RealTimeMessage;

/** @var \EonX\EasyNotification\Client\NotificationClientInterface $client */

$idempotencyKey = \sprintf('order-%s-shipped', $order->getId());

// Push message: the key applies to the device
$client->send(PushMessage::create($device, $token, $body)->setIdempotencyKey($idempotencyKey));

// Real time message: the key applies to each topic
$client->send(RealTimeMessage::create($body, $topics)->setIdempotencyKey($idempotencyKey));
```

The client sends the key in the `idempotencyKey` header of the SQS message. Only push and real time messages support
the key.

<br>

### How the service uses the key

- The service keeps one message for each provider, message type, idempotency key and recipient. The recipient is the
  device for a push message and the topic for a real time message.
- Use the same key when you send the same notification again. Use a different key for a different notification.
- You can use one key to send the same notification to many devices or topics. Each device or topic gets the
  notification once.
- The first message wins. When the service gets a message with a known key, it uses the stored message, not the new
  one. If the bodies are different, the service logs a warning. The push token is not part of the key scope, so a new
  token does not cause a second notification.
- If the delivery of the first message failed, send the message with the same key again to retry the delivery. The
  retry uses the stored message: the service ignores the body and the push token of the new message.
- If the delivery failed because the push token is not valid, a retry with the same key and a new token fails again.
  To send the notification to the new token, use a new key.
- The delivery is at-least-once. If the service stops during the delivery, it can deliver the notification again.

::: warning
The key must not be empty and must not be longer than 255 characters. If it is, `setIdempotencyKey()` throws
`EonX\EasyNotification\Exception\InvalidIdempotencyKeyException`, because the service would drop the message.
:::

::: tip
Versions of the service without idempotency support ignore the key.
:::
