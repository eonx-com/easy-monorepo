---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

The purpose of this package is to ease running PHP applications within a serverless context by taking care of low level
and repetitive functionalities so things should "just work".

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-serverless
```

<br>

### Require dependencies

This package relies on [Bref][2] and its [Symfony Bridge][3] so make sure you do require them in your project:

```bash
$ composer require bref/bref
$ composer require bref/symfony-bridge
```

<br>

### SQS handler retries (Symfony Messenger)

`EonX\EasyServerless\Bundle\SqsHandler\SqsHandler` does not send failed messages back to the transport. When a
message fails, the handler keeps the same SQS message and changes its visibility timeout. SQS then delivers it again
after the delay. The handler counts attempts with the SQS `ApproximateReceiveCount` attribute.

Two settings control the retries:

- `$appMaxRetries` argument of the `SqsHandler` service (default `3`): the maximum number of **attempts** (executions)
  of a message, including the first one.
- The retry strategy of the transport (`retry_strategy` in the Messenger config): `max_retries` is the maximum number
  of **retries** after the first attempt. The delays come from this strategy.

The handler executes a message at most `min(appMaxRetries, max_retries + 1)` times. To use all the retries of the
strategy, set `appMaxRetries` to `max_retries + 1`.

When a failed attempt is the last one, the handler logs "SQS Record failed to process but will not be retried" and
reports the failure with `willRetry = false`. Then it puts the message back in the queue with a delay of 1 second
and does not execute it again. SQS moves the message to the dead letter queue (DLQ) when the receive count is more
than the `maxReceiveCount` of the redrive policy. Thus, set `maxReceiveCount` to `appMaxRetries + 1` or more. After
the last attempt, the handler only puts the message back in the queue on each receive.

The log record has this context:

- `attempt`: the number of the attempt (the SQS `ApproximateReceiveCount`).
- `app_max_retries`: the value of `appMaxRetries`.
- `retry_stop_reason`: why the message will not be retried. `retry_strategy` (the retry strategy does not allow one more
  retry, or the transport has no retry strategy), `app_max_retries` (the retry strategy allows one more retry, but
  the attempt is the last one allowed by `appMaxRetries`) or `unrecoverable`.

If the message fails with an exception that implements `UnrecoverableExceptionInterface`, the handler does not retry
it and does not put it back in the queue. SQS deletes the message, and it does not go to the DLQ.

Example: `max_retries: 3`, `delay: 10000`, `multiplier: 8`, `appMaxRetries: 4` and `maxReceiveCount: 5` give
4 attempts with delays of 10 s, 80 s and 640 s between them. Then the message goes to the DLQ.

<br>

### SQS handler retries (Laravel)

`EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler` uses the same approach. The number of attempts of a job is
the SQS `ApproximateReceiveCount`.

- `max_retries` of the queue connection (`appMaxRetries`, default `3`) is the maximum number of attempts of a job,
  including the first one.
- `$tries` of the job can only lower this limit. A value of `0` or more than `max_retries` gives `max_retries`.
  In this case, the handler logs a warning when the job fails on its last attempt.
- `$backoff` of the job (or `release($delay)` in the job) sets the delay before the next attempt. The minimum delay
  is 1 second.

On the last attempt, the worker fails the job (the `failed()` method of the job is called), and the handler logs
"SQS Record failed to process but will not be retried". Then it puts the message back in the queue so it can go to
the DLQ, as explained above. The handler does not put the message back in the queue when the job has `$tries = 1`,
or when the job failed before its last attempt (for example `$this->fail()` in the job, `FailOnException` or
`$maxExceptions`). SQS deletes these messages, and they do not go to the DLQ.

The log record has the same context as for Symfony. The values of `retry_stop_reason` are `job_max_tries` (the
`$tries` of the job), `app_max_retries` (`max_retries` of the queue connection), `job_failed` (the job failed before
its last attempt) and `unrecoverable` (`$tries = 1`).

[1]: https://getcomposer.org/
[2]: https://packagist.org/packages/bref/bref
[3]: https://packagist.org/packages/bref/symfony-bridge
