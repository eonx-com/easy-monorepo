---eonx_docs---
title: 'Retry strategies'
weight: 1004
---eonx_docs---

# Retry strategies

By default, the EasyWebhook package uses a **multiplier retry strategy** for retrying webhooks if sending fails. The
multiplier retry strategy can increase the delay between retries exponentially.

The multiplier retry strategy has the following properties:

- `$delayMilliseconds`: Delay between retries (in milliseconds). The default value is `1000`.
- `$multiplier`: Multiplier for the delay for each retry. The default value is `1.0`.
- `$maxDelayMilliseconds`: Maximum delay between retries (in milliseconds). There is no default value.

With the default values, there will be 1 second delay between each retry.

You can modify the multiplier retry strategy by setting its arguments when registering the
`EonX\EasyWebhook\Common\Strategy\WebhookRetryStrategyInterface` service.

For example, if you set `$delayMilliseconds` to `10000` and `$multiplier` to `2.0`, the retry delays will be:

- Retry 1: 10 second delay.
- Retry 2: 20 second delay (10000 * 2 = 20000).
- Retry 3: 40 second delay (20000 * 2 = 40000).

You can also create your own retry strategy by overriding the
`EonX\EasyWebhook\Common\Strategy\WebhookRetryStrategyInterface` service with your own implementation.
