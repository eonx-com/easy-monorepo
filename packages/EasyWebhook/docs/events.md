---eonx_docs---
title: 'Events'
weight: 1006
---eonx_docs---

# Events

The following events can be dispatched by the EasyWebhook package, allowing you to apply business logic depending on the
webhook response:

- `EonX\EasyWebhook\Common\Event\SuccessWebhookEvent`: Dispatched when sending of a webhook succeeded.
- `EonX\EasyWebhook\Common\Event\FailedWebhookEvent`: Dispatched when sending of a webhook failed and it is waiting to be
  retried.
- `EonX\EasyWebhook\Common\Event\FinalFailedWebhookEvent`: Dispatched when sending of a webhook failed and it cannot be
  retried.
