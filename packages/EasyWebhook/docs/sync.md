---eonx_docs---
title: 'Sending synchronously'
weight: 1007
---eonx_docs---

# Sending synchronously

By default, the EasyWebhook package will send webhooks asynchronously if possible. This logic can be changed for all
webhooks at the configuration level by setting `easy-webhooks.send_async` to `false`.

It can also be changed for a specific webhook by setting the `$sendNow` property of a webhook via the `sendNow()` setter
method. See [Webhook class](webhook.md) for more information.

If webhooks are sent synchronously, then your application is responsible for retrying webhooks if necessary.
