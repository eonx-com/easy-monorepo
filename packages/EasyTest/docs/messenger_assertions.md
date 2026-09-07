---eonx_docs---
title: Messenger Assertions
weight: 1002
---eonx_docs---

# Messenger Assertions

`EonX\EasyTest\Common\Trait\MessengerAssertionsTrait` lets a test consume Symfony Messenger messages the same way
`messenger:consume` does — through a real `Worker`, with retries running to exhaustion and delayed messages delivered
by moving a mock clock — and then assert what happened.

## Requirements

- The asserted transports must use an in-memory DSN in the test environment, registered via a factory that keeps its
  transports across service resets. A plain `in-memory://` transport is wiped by `messenger.listener.reset_services`
  in the middle of consuming, and the trait fails the test when it detects that. The package ships such a factory —
  register `\EonX\EasyTest\Messenger\Factory\InMemoryPersistentTransportFactory` in the test services (it tags itself
  as a `messenger.transport_factory` when autoconfiguration is on) and point the transports at
  `in-memory-persistent://`:

  ```php
  // config/packages/test/messenger.php
  'transports' => [
      'async' => [
          'dsn' => 'in-memory-persistent://',
          'retry_strategy' => [
              'jitter' => 0,
          ],
      ],
      'failed' => [
          'dsn' => 'in-memory-persistent://',
      ],
  ],
  ```
- Delayed messages (retries or an explicit `DelayStamp`) require a mocked clock. Add
  `\Symfony\Component\Clock\Test\ClockSensitiveTrait` to the test case and call `self::mockTime()` at the start of
  every test whose flow hits a delay: it swaps the global clock for a `MockClock` and automatically restores the real
  one after the test. Instead of sleeping, the trait moves that clock straight to the due time of the next delayed
  message, so a test with 10-minute retry delays still runs in milliseconds. The in-memory transports must be built
  on the same mocked clock: when the clock is not mocked, or the transports watch a different clock than the test
  moves, the trait fails with an explanation instead of really sleeping.

## Consume messages

```php
self::consumeAsyncMessages();
```

Consumes everything from the `async` transport until it is empty: handlers run, messages they dispatch are consumed
too, failed messages are retried according to the transport retry strategy until they succeed or run out of retries.
The test fails if any handler throws an exception, or if the transport could not be drained.

## Assert exceptions

### At-least-once shape (recommended)

Map each expected exception class to its code (or to `null` to accept any code):

```php
self::consumeAsyncMessages([
    ExternalApiCallFailedException::class => 42,
]);
```

Every listed exception must be thrown at least once, and nothing else may fail. How many times it was thrown is not
asserted — retry counts belong to the transport retry strategy configuration, not to the test.

A bare class entry is short for `[SomeException::class => 0]` — handy for non-domain exceptions, which carry no code:

```php
self::consumeAsyncMessages([ThirdPartyClientException::class]);

// several classes work too, each must be thrown at least once with code 0:
self::consumeAsyncMessages([ThirdPartyClientException::class, RuntimeException::class]);
```

This tolerates any number of retries, like every at-least-once expectation. Repeating a class in such a list changes
nothing — this shape does not assert counts, so `[SomeException::class, SomeException::class]` equals
`[SomeException::class]`; to assert how many times an exception was thrown, use the exact-list shape below.

### Exact-list shape

Pass a list of single-pair maps to assert the thrown exceptions one-to-one, count included:

```php
self::consumeAsyncMessages([
    [ExternalApiCallFailedException::class => 42],
    [ExternalApiCallFailedException::class => 42],
    [ExternalApiCallFailedException::class => 42],
    [ExternalApiCallFailedException::class => 42],
]);
```

Here the message must fail exactly four times (the first attempt plus three retries) — one more or one less fails the
test. Each delivery attempt counts as one thrown exception. The order is not asserted, only classes, codes and counts.
`null` accepts any code for that one occurrence.

Exact-list entries are single-pair maps only: a bare class inside the list would make the whole list the
at-least-once shape, so mixing the two kinds fails the test — write `[SomeException::class => 0]` inside an exact
list instead.

## Assert delays

`$expectedDelays` is the exact list of clock moves, in seconds, the consuming must perform to deliver delayed
messages — both explicit `DelayStamp` delays and retry delays:

```php
self::consumeAsyncMessages(
    [ExternalApiCallFailedException::class => 42],
    expectedDelays: [10, 1, 30, 60],
);
```

This test dispatches a message whose handler dispatches the next message with `new DelayStamp(10_000)` (the `10`), and
that message fails and is retried with the transport retry strategy delays of 1, 30 and 60 seconds. More moves, fewer
moves or different values fail the test, and the failure message shows the full actual list.

Notes:

- Retry delays are randomized by the retry strategy jitter — and Symfony's default is `jitter: 0.1`, which makes an
  exact delay assertion fail on almost every run. Set `retry_strategy: { jitter: 0 }` on the transports in the test
  environment before asserting delays.
- Messages available immediately do not move the clock and do not produce an entry — there are no `0` placeholders.
  An explicit `DelayStamp(0)` produces a `0` entry.
- The values are compared at millisecond precision, matching `DelayStamp`.
- `null` (the default) skips the check, `[]` asserts the clock never moved.
- Delays and exceptions are independent: a flow without failures can still assert its delays.

```php
self::consumeAsyncMessages(expectedDelays: [10]);
```

## Consume other transports

Transports are polled in the listed order, earlier ones first, like `messenger:consume async email_delivery`:

```php
self::consumeAsyncMessages(
    [SomeEmailException::class => 42],
    transportNames: ['async', 'email_delivery'],
);
```

A handler on one transport may dispatch messages routed to another listed transport and back — the worker keeps
consuming until every listed transport is empty. Exceptions and delays are asserted across all listed transports
together. Listing the failure transport (`failed`) fails the test — consuming it would re-handle dead messages;
assert its content instead, e.g. via `assertCountOfMessagesSentToFailedTransport()`.

## Everything together

Every parameter is independent — pass any subset. A test pinning the exact failures, the exact clock moves and a
custom transport list at once:

```php
// the flow below hits delays, so the clock must be mocked (see Requirements)
self::mockTime();

self::consumeAsyncMessages(
    [
        [ExternalApiCallFailedException::class => 42],
        [ExternalApiCallFailedException::class => 42],
        [ExternalApiCallFailedException::class => 42],
        [ExternalApiCallFailedException::class => 42],
    ],
    expectedDelays: [10, 1, 30, 60],
    transportNames: ['async', 'email_delivery'],
);
```

Reading: the message fails on all four of its delivery attempts with code 42; consuming moves the clock exactly four
times — by the deliberate 10-second `DelayStamp` of a scheduled message and then by the 1, 30 and 60-second retry
delays; both listed transports end up fully drained.

## Assert sent messages

```php
// exactly one UpdateOrderMessage with the given properties was sent to the async transport
self::assertMessageSentToAsyncTransport(UpdateOrderMessage::class, ['orderId' => $order->getId()]);

// counts, optionally filtered by message class
self::assertCountOfMessagesSentToAsyncTransport(3);
self::assertCountOfMessagesSentToFailedTransport(1, SendWebhookMessage::class);

// raw messages from any transport, optionally filtered by class
$messages = self::getMessagesSentToTransport('email_delivery', SendEmailMessage::class);
```

Sent messages survive consuming, so these assertions work both before and after `consumeAsyncMessages()`. A message
re-sent by a retry counts as sent once per attempt.

## Failure output

When an unexpected exception is thrown, the failure message contains the exception class, code, message and the full
stack trace, including the previous exceptions chain — no digging through the failure transport to find out what
actually broke.
