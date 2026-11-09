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

- `$appMaxRetries` argument of the `SqsHandler` service (default `3`): the maximum number of **attempts** of a
  message, including the first one. This is a hard limit: the handler does not execute a message received more than
  `appMaxRetries` times.
- The retry strategy of the transport (`retry_strategy` in the Messenger config): it decides whether the handler asks
  SQS for a retry after a failure, and the delay. `max_retries` is the maximum number of **retries** after the first
  attempt.

The retry strategy stops asking for retries after `max_retries` retries, so a failing message usually runs
`min(appMaxRetries, max_retries + 1)` times. Only `appMaxRetries` is a hard limit: without partial batch failure,
when another record fails the Lambda invocation, SQS gives the whole batch back, and a message that the retry strategy
already stopped (or that succeeded) runs again while its receive count is within `appMaxRetries`. To use all the
retries of the strategy, set `appMaxRetries` to `max_retries + 1`.

When a failed attempt is the last one, the handler logs "SQS Record failed to process but will not be retried" and
dispatches `ServerlessWorkerMessageFailedEvent` with `willRetry = false`. Then:

- On the attempt that reaches `appMaxRetries`, the handler does not acknowledge the record, so SQS keeps the message.
  With partial batch failure, it puts the message back in the queue with a delay of 1 second. Without it, it throws an
  exception: the Lambda invocation fails and SQS gives the whole batch back. SQS moves the message to the dead letter
  queue (DLQ) when the receive count is more than the `maxReceiveCount` of the redrive policy. Thus, set
  `maxReceiveCount` to `appMaxRetries`: the message then goes to the DLQ after its last attempt in both modes. A higher
  value works only with partial batch failure (`ReportBatchItemFailures`): the handler puts the message back in the
  queue without executing it on each receive, until SQS moves it to the DLQ. Without partial batch failure, the
  handler acknowledges such a message, and SQS deletes it.
- When the retry strategy stops before `appMaxRetries`, the handler acknowledges the record. SQS deletes the message,
  and it does not go to the DLQ. SQS only counts receives, so on a next receive the handler could not tell whether the
  message was executed or only received (for example skipped below the Lambda time threshold). To send all failed
  messages to the DLQ, set `appMaxRetries` to `max_retries + 1`.

The receive count also grows when a message is received but not executed: skipped by the handler, given back with
the batch, or after a Lambda timeout. These receives count as attempts.

The log record has this context:

- `attempt`: the number of the attempt (the SQS `ApproximateReceiveCount`).
- `app_max_retries`: the value of `appMaxRetries`.
- `retry_stop_reason`: why the message will not be retried. `retry_strategy` (the retry strategy does not allow one more
  retry, or the transport has no retry strategy), `app_max_retries` (the retry strategy allows one more retry, but
  the attempt is the last one allowed by `appMaxRetries`) or `unrecoverable`.

If the message fails with an exception that implements `UnrecoverableExceptionInterface`, the handler does not retry
it and does not put it back in the queue. SQS deletes the message, and it does not go to the DLQ.

The handler dispatches `ServerlessWorkerMessageFailedEvent` for each failed attempt. It does not dispatch the Symfony
`WorkerMessageFailedEvent`, because the Messenger listeners of that event send the message again. The
`EasyErrorHandlerBundle` reports the failure from a listener of `ServerlessWorkerMessageFailedEvent`. Without this
bundle, the handler does not report the failure.

Example: `max_retries: 3`, `delay: 10000`, `multiplier: 8`, `appMaxRetries: 4` and `maxReceiveCount: 4` give
4 attempts with delays of 10 s, 80 s and 640 s between them. Then the message goes to the DLQ.

<br>

### SQS handler retries (Laravel)

`EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler` uses the same approach. The number of attempts of a job is
the SQS `ApproximateReceiveCount`.

- `max_retries` of the queue connection (`appMaxRetries`, default `3`) is the maximum number of attempts of a job,
  including the first one.
- `$tries` of the job can only lower this limit. A value of `0` or less (unlimited in Laravel), or more than
  `max_retries`, gives `max_retries`. In this case, the handler logs a warning when the job fails on its last attempt.
- The delay before the next attempt is the delay given to `release()`. When the job throws an exception, the worker
  releases it with the `$backoff` of the job for this attempt. When the job calls `$this->release($delay)` itself,
  this explicit delay is used, even if the job also has a `$backoff`. The delay is at least 1 second and at most
  42000 seconds: SQS limits it to 12 hours from when the message was received.

On the last attempt, the worker fails the job (the `failed()` method of the job is called), and the handler logs
"SQS Record failed to process but will not be retried". If the job calls `$this->release()` on its last attempt, the
handler fails it like the worker would do on the next attempt. Then:

- On the attempt that reaches `max_retries`, the handler does not acknowledge the record, as for Symfony: it puts the
  message back in the queue with partial batch failure, and throws an exception without it. Set `maxReceiveCount` to
  `max_retries` so the message goes to the DLQ after its last attempt.
- When the job reaches its own `$tries` before `max_retries`, the handler puts the message back in the queue with
  partial batch failure. When the message comes back, the handler puts it back again without running the job, until
  SQS moves it to the DLQ. Without partial batch failure, the handler acknowledges the record and logs a warning: SQS
  deletes the message, and it does not go to the DLQ.
- The handler does not put the message back in the queue when the job has `$tries = 1`, or when the job failed before
  its last attempt (for example `$this->fail()` in the job, `FailOnException` or `$maxExceptions`). SQS deletes these
  messages, and they do not go to the DLQ.

For FIFO queues, when the handler puts a message back in the queue, the next messages of the same message group in
the batch are put back in the queue too, to keep their order.

Without partial batch failure (`partial_batch_failure: false`), the handler also throws an exception for a retry: the
Lambda invocation fails, and SQS gives the whole batch back.

The log record has the same context as for Symfony. The values of `retry_stop_reason` are `job_max_tries` (the
`$tries` of the job), `app_max_retries` (`max_retries` of the queue connection), `job_failed` (the job failed before
its last attempt) and `unrecoverable` (`$tries = 1`).

[1]: https://getcomposer.org/
[2]: https://packagist.org/packages/bref/bref
[3]: https://packagist.org/packages/bref/symfony-bridge
