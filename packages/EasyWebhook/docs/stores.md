---eonx_docs---
title: 'Stores'
weight: 1005
---eonx_docs---

# Stores

The EasyWebhook package allows you to store webhooks and webhook results within the persisting layer of your choice.
Different stores can be used for webhooks and webhook results.

You can implement your own stores, but the package comes with three store options out of the box: null store, array
store and Doctrine DBAL store.

To set the **webhook store**, set the `EonX\EasyWebhook\Common\Store\StoreInterface` service to be one of:

- `EonX\EasyWebhook\Common\Store\NullStore`: Webhooks are not stored. This is the default store option for webhooks.
- `EonX\EasyWebhook\Common\Store\ArrayStore`: Webhooks are stored in an array in memory. Note that the array store will not
  persist beyond the life of your application.
- `EonX\EasyWebhook\Doctrine\Store\DoctrineDbalStore`: Webhooks are stored in a database accessed through Doctrine DBAL. Provide
  a `Doctrine\DBAL\Connection` connection and an optional table name (the default table name is `easy_webhooks`).
- Your own webhook store implementation.

To set the **webhook results store**, set the `EonX\EasyWebhook\Common\Store\ResultStoreInterface` service to be
one of:

- `EonX\EasyWebhook\Common\Store\NullResultStore`: Webhook results are not stored. This is the default store option for
  webhook results.
- `EonX\EasyWebhook\Common\Store\ArrayResultStore`: Webhook results are stored in an array in memory. Note that the array
  store will not persist beyond the life of your application.
- `EonX\EasyWebhook\Doctrine\Store\DoctrineDbalResultStore`: Webhook results are stored in a database accessed through Doctrine
  DBAL. Provide a `Doctrine\DBAL\Connection` connection and an optional table name (the default table name is
  `easy_webhook_results`).
- Your own webhook results store implementation.

## DataCleaner

In case the webhooks and/or webhook results contain sensitive data, it is possible to remove it before persisting them.
To do so, create your own implementation of `EonX\EasyWebhook\Common\Cleaner\DataCleanerInterface` and replace the
existing service into your service container.

This interface defines a single method `cleanUpData(array $data): array`, it will receive the formatted data that you
can modify as desired and return it.

::: info
Because the data given to `cleanUpData` is formatted, any array will be represented as JSON string.
:::
