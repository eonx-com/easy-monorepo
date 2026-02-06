---eonx_docs---
title: 'WebhookResult class'
weight: 1002
---eonx_docs---

# WebhookResult class

The **WebhookResult** class contains the results of a webhook HTTP request.

## Properties

A WebhookResult object has the following properties:

- `$id`: Unique identifier for the webhook result, assigned when storing the result in a webhook result store.
- `$response`: The response from the webhook HTTP request, as a `Symfony\Contracts\HttpClient\ResponseInterface`.
- `$throwable`: The `Throwable` if the sending of the HTTP webhook request failed.
- `$webhook`: The Webhook object that the webhook result applies to.

## Methods

A WebhookResult object provides the following methods:

| Property     | Type      | Setter    | Getter           |
|--------------|-----------|-----------|------------------|
| `$id`        | string    | `setId()` | `getId()`        |
| `$response`  | Response  |           | `getResponse()`  |
| `$throwable` | Throwable |           | `getThrowable()` |
| `$webhook`   | Webhook   |           | `getWebhook()`   |

It also provides the `isSuccessful()` method, which returns a boolean depending on whether the sending of the webhook
was successful.
