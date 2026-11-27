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

<br>

### Datadog

When the [ddtrace extension][4] is loaded, PHP runtime information (version, SAPI, OS, memory limit, OPcache/JIT
status, extra version if set) is added as `php.*` tags on the root span of incoming requests, messages/jobs and
console commands, and `PHP_VERSION_ID` is added as the `php.version_id` metric.
Nothing happens when the extension is not loaded or no trace is active. Console commands and workers only have a root
span when `DD_TRACE_CLI_ENABLED=true` is set. OPcache/JIT status comes from `opcache_get_status()`, or from the ini
settings when `opcache.restrict_api` is set.

Disable it with `datadog.enabled: false` in the Symfony bundle config, or `easy-serverless.datadog.enabled` set to
`false` in Laravel/Lumen. In Laravel and Lumen, requests are covered by a global middleware registered first, so
unmatched routes and requests short-circuited by other global middleware (e.g. maintenance mode) are enriched as well.

<br>

### AWS Lambda SnapStart

With [SnapStart][5], Lambda initializes a published function version once, takes a snapshot of the execution
environment and restores the new execution environments from it. The package warms the application up before the
snapshot, so the restored environments skip this work, and makes each restored environment unique.

It is disabled by default. Once enabled, it is only active in a remote Lambda initialized by SnapStart
(`AWS_LAMBDA_INITIALIZATION_TYPE=snap-start`) with the Bref function runtime (the handler is resolved from the
container). The FPM and console runtimes are not supported.

With Bref, SnapStart needs a container image built from a Bref image: Lambda does not support SnapStart for the
OS-only runtimes, e.g. `provided.al2023` (ZIP) with the Bref layers. See the infrastructure checklist below.

```php
// config/packages/easy_serverless.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_serverless', [
        'snap_start' => [
            'enabled' => true,
        ],
    ]);
};
```

In Laravel/Lumen, set `'snap_start' => ['enabled' => true]` in `config/easy-serverless.php`.

If the application already subscribes its own SnapStart Bref subscriber, remove it before you enable `snap_start`,
otherwise the warm-up and the restore handling run twice. Move its steps into warmers and hooks, see below.

<br>

###### Lifecycle

1. Bref boots the application to resolve the handler, then dispatches `afterStartup`.
2. On `afterStartup`, the package boots Lumen if needed, creates the after restore hooks, runs the warmers in order,
   writes the marker file `/tmp/easy-serverless-snap-start-initialized` and logs "SnapStart warm-up completed".
3. Lambda takes the snapshot when the runtime asks for the first event.
4. On the first invocation after a restore, before the handler, the package calls `mt_srand()`, then the after restore
   hooks, and logs "SnapStart restore completed".
5. Bref starts a new PHP process after `BREF_LOOP_MAX` invocations or after a failed one. `/tmp` is part of the
   snapshot, so these processes find the marker and skip the warm-up and the after restore handling. They still boot
   Lumen on `afterStartup`: it would boot on the first request anyway.

**Set `BREF_LOOP_MAX` above 1.** With the default value `1`, Bref starts a new PHP process after each invocation, and
only the first invocation of each execution environment uses the warmed-up process. The application must then be
able to handle more than one invocation in the same process.

<br>

###### Default warmers

The classes are in the `EonX\EasyServerless` namespace, and the config keys are under `snap_start`.

| Warmer                                      | What it does                                                          | Symfony priority / condition       | Laravel/Lumen | Config key             |
|---------------------------------------------|-----------------------------------------------------------------------|------------------------------------|---------------|------------------------|
| `SnapStart\Warmer\ClassMapWarmer`           | Loads the classes of `namespaces` (default `App\`) from the class map | `300`                              | Tag order     | `warmers.class_map`    |
| `Doctrine\Warmer\EntityMetadataWarmer`      | Loads the metadata of all the entities of each entity manager         | `200`, with DoctrineBundle         | -             | -                      |
| `Routing\Warmer\RouteCollectionWarmer`      | Runs the route loaders (e.g. the EasyAdmin loader fills its caches)   | `100`                              | -             | -                      |
| Application warmers                         | -                                                                     | `0` by default                     | Tag order     | -                      |
| `Aws\Warmer\SymfonyHttpHandlerWarmer`       | Sends a synthetic HTTP request, see below                             | `-100`                             | -             | `warmers.http_request` |
| `Doctrine\Warmer\ConnectionsWarmer`         | Runs a query on each DBAL connection, then closes all of them         | `PHP_INT_MIN`, with DoctrineBundle | -             | -                      |
| `Laravel\Warmers\DatabaseConnectionsWarmer` | Closes the open database connections                                  | -                                  | Last          | -                      |

`ConnectionsWarmer` loads the driver and the platform of each connection. It also closes the connections opened by the
previous warmers: every restored environment would share them.

<br>

###### Custom warmers and hooks

Implement `EonX\EasyServerless\SnapStart\Warmer\SnapStartWarmerInterface` or
`EonX\EasyServerless\SnapStart\Hook\AfterRestoreHookInterface`. In Symfony, autoconfiguration tags them. Set the
order of a warmer with `#[AsTaggedItem(priority: ...)]`, higher first:

```php
#[AsTaggedItem(priority: 10)]
final readonly class CountryWarmer implements SnapStartWarmerInterface
{
    public function warm(): void
    {
        // Load what the first requests would load, e.g. read a cache
    }
}
```

In Laravel/Lumen, tag them in a service provider: `$this->app->tag([CountryWarmer::class], SnapStartTag::Warmer->value)`
for a warmer, `SnapStartTag::AfterRestoreHook->value` for a hook. Tag them in `register()` or `boot()`: the package
reads the tags on `afterStartup`.

Rules:

- A warmer must close the connections it opens (Redis, sockets, HTTP keep-alive): every restored environment would
  share them. The package only closes the Doctrine and Laravel database connections.
- Keep the priority of a Symfony warmer above `PHP_INT_MIN`, so `ConnectionsWarmer` runs after it.
- Keep hooks fast: they delay the first invocation of each restored environment.
- An exception never fails the initialization or an invocation. A warmer that throws is logged as a warning and the
  next warmers still run. A hook that throws is logged as an error.
- A warmer or a hook that cannot be created (e.g. its constructor throws) is logged as an error and skipped. The
  package creates each one separately, so the next ones still run, and `ConnectionsWarmer` and
  `DatabaseConnectionsWarmer` still close the connections.
- In Lumen, a boot that fails is logged as an error, and the warm-up still runs.

`EonX\EasyServerless\Uid\Hook\RegenerateUuidV6NodeHook` is opt-in. Use it when the application generates a `UuidV6`
during the initialization: Symfony keeps its random node in memory, so every restored environment would share it.
Register it with `$services->set(RegenerateUuidV6NodeHook::class);` (autoconfigured) in Symfony, or tag it with
`SnapStartTag::AfterRestoreHook->value` in Laravel/Lumen.

<br>

###### Laravel Octane

With `EonX\EasyServerless\Laravel\HttpHandlers\OctaneHttpHandler`, Bref boots one application to resolve the handler
and Octane boots a second one that handles the requests. The package warms both up, and runs the hooks and the
`DatabaseConnectionsWarmer` of both: the marker applies to the PHP process, not to the application. The class loading
and OPcache are shared, but the container services are not: list the services the requests use in the `octane.warm`
config, so they are resolved in the Octane application.

<br>

###### Synthetic request (Symfony)

`SymfonyHttpHandlerWarmer` sends `GET /easy-serverless/health-check` to the `SymfonyHttpHandler`, so the code and the
services of an HTTP request are loaded before the snapshot. Like an API Gateway request, it is sent as HTTPS
(`X-Forwarded-Proto: https`, `X-Forwarded-Port: 443`). It only runs in the functions whose handler is the
`SymfonyHttpHandler`. Laravel/Lumen have no synthetic request.

Import the routes of the package (they need `health.enabled`, enabled by default):

```php
// config/routes/easy_serverless.php
use EonX\EasyServerless\Bundle\Enum\BundleParam;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return static function (RoutingConfigurator $routes): void {
    $routes->import('.', BundleParam::RouteType->value);
};
```

Add a firewall with the pattern `^/easy-serverless/health-check$` and `'security' => false`, before the other
firewalls. When `framework.trusted_hosts` is set, set `snap_start.warmers.http_request.host` to a trusted host, e.g.
`env('DOMAIN_API')`. To send the request to another route, set `snap_start.warmers.http_request.path`.

A status code outside 2xx is logged as a warning: 404 when the routes are not imported, 401 for a firewall, 400 for an
untrusted host, 3xx for a redirect (e.g. the application forces HTTPS in another way), 500 for a failed health check.
Health checkers must not leave connections open, except the Doctrine ones. The kernel resets the services before the
first real request.

<br>

###### Class map

`ClassMapWarmer` only finds the application classes when the autoloader is optimized: run
`composer dump-autoload --optimize` (or `--classmap-authoritative`) when you build the application. Otherwise, it logs
a warning. A class that cannot be loaded (e.g. its parent class is only installed in dev) is skipped, but a class that
causes a fatal error stops the initialization: narrow `namespaces` or disable the warmer.

<br>

###### Uniqueness

All the restored environments start with the same memory, see [uniqueness with SnapStart][6]. After a restore, the
package calls `mt_srand()`, so `mt_rand()`, `rand()`, `shuffle()`, `array_rand()` and `str_shuffle()` give different
values in each environment. `random_int()`, `random_bytes()`, `Random\Randomizer` with the `Secure` engine, `UuidV4`,
`UuidV7` and `Ulid` are safe. The package does not fix `lcg_value()`, `uniqid('', true)` and the `Mt19937` engines
created during the initialization: do not use them for unique values.

<br>

###### Infrastructure checklist

SnapStart only applies to published versions. With SAM, publish a version and invoke it through an alias:

```yaml
Globals:
    Function:
        AutoPublishAlias: live
        SnapStart:
            ApplyOn: PublishedVersions
```

- Add `LABEL com.amazonaws.lambda.feature.snapstart="Allow"` to the Dockerfile: it is required, publishing a version
  fails without it.
- Do not disable OPcache: the scripts compiled during the initialization are part of the snapshot.
- With ddtrace, set `DD_TRACE_SECURE_RANDOM=true`, otherwise the restored environments can generate the same trace
  and span IDs.
- With SnapStart, Lambda gives the AWS credentials through `AWS_CONTAINER_CREDENTIALS_FULL_URI` and
  `AWS_CONTAINER_AUTHORIZATION_TOKEN`. aws/aws-sdk-php reads the token, but async-aws/core does not read it yet: patch
  async-aws/core (e.g. with cweagans/composer-patches) when the function uses any async-aws client, during the
  initialization or the invocations. For example `SecretsHelper` (used by `ServerlessSymfonyRuntime`), the
  `DynamoDbAdapter` of the package, the SQS client of the Symfony SQS handler, and the Messenger SQS, SNS and
  EventBridge transports.
- Secrets read during the initialization are part of the snapshot: publish a new version after a rotation.
- Use unbuffered log handlers (e.g. a stream to `php://stderr`): records buffered during the initialization are copied
  into every restored environment.
- Check the logs: "SnapStart warm-up completed" during the initialization, with the duration of each warmer, and
  "SnapStart restore completed" on the first invocation of each restored environment.

[1]: https://getcomposer.org/
[2]: https://packagist.org/packages/bref/bref
[3]: https://packagist.org/packages/bref/symfony-bridge
[4]: https://docs.datadoghq.com/tracing/trace_collection/automatic_instrumentation/dd_libraries/php/
[5]: https://docs.aws.amazon.com/lambda/latest/dg/snapstart.html
[6]: https://docs.aws.amazon.com/lambda/latest/dg/snapstart-uniqueness.html
