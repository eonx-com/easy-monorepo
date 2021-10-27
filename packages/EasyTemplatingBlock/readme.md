---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

# Introduction

The **EasyWebhook** package allows you to implement flexible, configurable webhook functionality in your projects. A
**webhook** is a mechanism for sending HTTP requests to URLs, typically with information about an event that has
occurred in your application.

Using the EasyWebhook package, you can:

- Configure webhooks
- Send webhooks as HTTP requests to URLs and retry them if they fail
- Send webhooks synchronously or asynchronously
- Receive webhook responses
- Persist webhooks and results into a store of your choice
- Dispatch events upon success or failure of webhooks

## Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-webhook
```

## Usage overview

The **[Webhook class](webhook.md)** defines the configuration for a webhook, e.g. the URL to send webhook HTTP requests.

The **[WebhookClient class](webhook-client.md)** triggers the processing of the stack of middleware that applies to the
webhook.

The **middleware stack** is the workhorse of the EasyWebhook package. Each middleware in the stack is responsible for a
distinct part of the webhook processing, including:

- Sending webhooks as HTTP requests.
- Getting the responses of the HTTP requests, using the **[WebhookResult class](webhook-result.md)** class to hold the
  responses.
- Retrying webhooks if necessary. The EasyWebhook package has a default retry strategy but you can implement your own.
  See [Retry strategies](retry-strategies.md) for more information.
- Persisting webhooks and their responses in stores. You can implement your own stores, but the EasyWebhook package
  comes with three store options out of the box: null store, array store and Doctrine DBAL store. See
  [Stores](stores.md) for more information.
- Dispatching events upon success or failure of webhooks. See [Events](events.md) for more information.

See [Middleware](middleware.md) for detailed information about middleware, including how to implement custom middleware.

By default, webhooks are sent asynchronously, but you can configure a webhook to be sent synchronously. See
[Sending synchronously](sync.md) for more information.

Webhooks can be configured to be sent after a particular date and time. You can run the
`easy-webhooks:send-due-webhooks` console command in a cron job to send due webhooks. See [Console](console.md) for more
information.

Global settings for the EasyWebhook package can be configured via a configuration file in your application. See
[Configuration](config.md) for more information.

[1]: https://getcomposer.org/
