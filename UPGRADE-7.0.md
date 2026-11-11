# Upgrade from 6.x to 7.0

This guide has two parts:

1. [Prepare on 6.x](#1-prepare-on-6x): changes you can make on the latest 6.x release. Test them before you switch.
2. [Upgrade to 7.0](#2-upgrade-to-70): changes that apply when you switch to 7.0.

Packages that are not listed have no changes other than the [requirements](#requirements). All `eonx-com/*`
packages in one app must use the same major version.

Config examples use `App::config()` in `config/packages/*.php` (namespace
`Symfony\Component\DependencyInjection\Loader\Configurator`).

<br>

## 1. Prepare on 6.x

### Dependencies

6.x already supports these versions. Upgrade to them first:

- [ ] PHP 8.4.
- [ ] Symfony 7.4.
- [ ] API Platform 4.4 or later (stop using API Platform 3).
- [ ] PHPUnit 12.5.
- [ ] `nesbot/carbon` 3.12 or later.
- [ ] Lumen apps: move to Laravel. 7.0 does not support Lumen.

### EasyLogging (Symfony)

- [ ] Install `symfony/monolog-bundle` (3.11.1 or later) and register `MonologBundle`.
- [ ] Set `'use_symfony_monolog_bundle' => true` in `easy_logging`. In 7.0 this is the only mode.
- [ ] Declare your Monolog handlers in `monolog.handlers`. See [EasyLogging](#easylogging) below.
- [ ] Replace services that implement `HandlerConfigProviderInterface` or `LoggerConfiguratorInterface` with Monolog
  config. In this mode Monolog loggers already ignore them. `easy_logging.logger` and direct
  `LoggerFactory::create()` calls still use them on 6.x.
- [ ] Inject `LoggerInterface` or `monolog.logger.<channel>`. Do not inject `LoggerFactoryInterface` or
  `easy_logging.logger`.
- [ ] Inject `monolog.logger.easy_http_client` instead of `easy_http_client.logger`.
- [ ] Inject `monolog.logger.security` instead of `easy_security.logger`.
- [ ] Stop using `EonX\EasyLogging\Logger\LoggerAwareTrait`. Use constructor injection.
- [ ] EasyDoctrine AWS RDS: remove `aws_rds.iam.logger` / `aws_rds.ssl.logger` if they point to a logger created with
  `LoggerFactory`. The bundle then uses `monolog.logger.easy_doctrine`.
- [ ] Bugsnag: list every channel to report in `bugsnag_handler_channels` (for example `['app', 'easy_doctrine']`).
  The default is `app` only.

How to move the old config to Monolog:

| 6.x (LoggerFactory) | Monolog config |
|---|---|
| `HandlerConfigProvider` with `channels([...])` | Handler with `'channels' => ['app', 'easy_http_client']` |
| `HandlerConfigProvider` with `exceptChannels([...])` | Handler with `'channels' => ['!app', '!easy_http_client']` |
| `ProcessorConfigProvider` | Register the processor as an autoconfigured service. MonologBundle tags every `ProcessorInterface`. |
| `LoggerConfigurator` that filters levels | Handler `'level'` |
| Custom formatter on a handler | Handler `'formatter'` (a service id, for example `easy_logging.formatter.json`) |

Tips:

- A channel without a handler loses its logs. Add a catch-all handler that excludes the channels you already route,
  for example `'channels' => ['!app', '!easy_http_client', '!easy_doctrine']` with `'level' => 'warning'`.
- MonologBundle adds its own `PsrLogMessageProcessor` to each handler. If you register `PsrLogMessageProcessor`
  yourself (for example with `$removeUsedContextFields`), set `'process_psr_3_messages' => false` on the handlers.
- Tests: replace `'stream_handler' => false` and the `LoggerStub` logger class parameter with `null` handlers in
  `config/packages/test/monolog.php`. Override each handler by name, and the Bugsnag handler by
  `easy_logging_bugsnag`. Keep `'level' => 'debug'` on test handlers: Monolog runs processors only if a handler
  accepts the record, so `MonologTrait` sees nothing below the handler level.

  ```php
  // config/packages/test/monolog.php
  return App::config([
      'monolog' => [
          'handlers' => [
              'app' => ['type' => 'null', 'level' => 'debug', 'formatter' => null],
              'easy_logging_bugsnag' => ['type' => 'null'],
          ],
      ],
  ]);
  ```

### EasyLogging (sensitive data sanitizer)

- [ ] Symfony: enable the sensitive data sanitizer: `'sensitive_data_sanitizer' => ['enabled' => true]` in
  `easy_logging`. It needs `EasyUtilsBundle`. In 7.0 it is enabled by default.
- [ ] Laravel: in 6.26.1 and earlier this option has no effect, because the provider does not add the sanitizer to
  the logger. Use a later 6.x release to test it, or check it after you switch to 7.0. It needs
  `EonX\EasyUtils\Laravel\EasyUtilsServiceProvider`.

### EasyDoctrine

- [ ] Replace `ObjectCopierFactory::create()` with `ObjectCopierFactory::createForDeletedEntity()`.
- [ ] Replace the `ObjectCopierInterface` service with `easy_doctrine.deleted_entity_copier`.
- [ ] If you create `DeferredEntityEventDispatcher` yourself, pass `deletedEntityCopier:` and remove `objectCopier:`.

### EasyBatch

- [ ] Update stored class names in the `class` column of `easy_batches` and `easy_batch_items` (and in your
  `batch_class` / `batch_item_class` config). 7.0 does not convert old names on read anymore:

  ```sql
  -- PostgreSQL, SQLite
  UPDATE easy_batches SET class = REPLACE(class, 'EonX\EasyBatch\Objects\', 'EonX\EasyBatch\Common\ValueObject\');
  UPDATE easy_batch_items SET class = REPLACE(class, 'EonX\EasyBatch\Objects\', 'EonX\EasyBatch\Common\ValueObject\');

  -- MySQL, MariaDB (a backslash in a string literal must be doubled)
  UPDATE easy_batches SET class = REPLACE(class, 'EonX\\EasyBatch\\Objects\\', 'EonX\\EasyBatch\\Common\\ValueObject\\');
  UPDATE easy_batch_items SET class = REPLACE(class, 'EonX\\EasyBatch\\Objects\\', 'EonX\\EasyBatch\\Common\\ValueObject\\');
  ```

### EasyBugsnag

- [ ] Symfony: if you need SQL query breadcrumbs, set `'doctrine_dbal' => true` in `easy_bugsnag`. In 7.0 they are
  disabled by default.

### EasyServerless

- [ ] Symfony: use `EonX\EasyServerless\Bundle\SqsHandler\SqsHandler` as the Lambda SQS handler. Stop using
  `ReportBusDriver`. Laravel is not affected.
- [ ] Remove the `easy_serverless.easy_error_handler` config key. It does nothing on 6.x.

### EasyUtils

- [ ] Stop using `JsonStringSanitizer`.
- [ ] Create `Number*Comparison` constraints with named arguments. Do not pass `options`.

### EasyWebhook

- [ ] Enable `'request_limits' => ['enabled' => true]` in `easy_webhook` (Laravel: the same in
  `config/easy-webhook.php`) and test it. In 7.0 it is enabled by default.

### EasyTest

- [ ] Replace `LoggerTrait` and `LoggerStub` with `EonX\EasyTest\Monolog\Trait\MonologTrait`. Replace
  `getLoggerService()->hasRecord([...], $level)` with `assertLoggerHas($level, $message, $context)`.
- [ ] `EasyErrorHandlerAssertionTrait`: replace `assertErrorCodeExistsInReportedErrors($code)` with
  `assertErrorHandlerReportedError($code)`. Remove `assertEmptyReportedErrors()` calls (the trait checks this after
  each test). Replace `getErrorHandlerReportedThrowables()` with `TraceableErrorHandlerStub::getAllReportedErrors()`.
- [ ] `MessengerAssertionsTrait`: replace `getMessagesSentToAsyncTransport()` / `getMessagesSentToFailedTransport()`
  with `getMessagesSentToTransport('async' | 'failed', ...)`.

### EasyErrorHandler

- [ ] Optional: override the `exceptions.*` texts in your translations now if you want to keep them in 7.0. See
  [EasyErrorHandler](#easyerrorhandler-1) below.

<br>

## 2. Upgrade to 7.0

### Requirements

| Dependency | 6.x | 7.0 |
|---|---|---|
| PHP | `^8.2` | `^8.4` |
| Symfony | `^7.2` | `^7.4 \|\| ^8.0` |
| PHPUnit | `^10.5 \|\| ^11.5 \|\| ^12.5` | `^12.5 \|\| ^13.0` |
| Laravel | 11 | 13 |
| Lumen | supported | **removed** |
| API Platform | `^3.4.17 \|\| ^4.3.17` | `^4.4 \|\| ^5.0.1` |
| Doctrine DBAL | `^3.8` | `^3.8 \|\| ^4.4` |
| Doctrine ORM | `^2.20` | `^2.20 \|\| ^3.6` |
| DoctrineBundle | `^2.12` | `^2.12 \|\| ^3.2` |
| `nesbot/carbon` | `^3.8.4` | `^3.12` |
| `symfony/monolog-bundle` | `^3.11.1` | `^3.11.1 \|\| ^4.0` |
| `bref/laravel-bridge` (EasyServerless, Laravel) | `^2.6 \|\| ^3.0` | `^3.0` |
| `eonx-com/*` | `^6.x` | `^7.0` |

- Lumen: all Lumen code is removed (for example `LumenHttpKernel` in EasyServerless, the Lumen branches in
  EasyPagination and EasyUtils). Laravel service providers now use `Illuminate\Foundation\Application`.

### EasyApiPlatform

- `api-platform/core` is not a dependency anymore. Require API Platform yourself: `api-platform/core` `^4.4`, or the
  split packages (`api-platform/symfony`, `api-platform/doctrine-orm`, ...) `^4.4 || ^5.0.1`.
- `symfony/property-info` is now required. Install `symfony/validator` to get validation error responses.
- `IriConverterTrait` is removed.
- Error response builders are removed:
  - `ApiPlatformMissingConstructorArgumentsExceptionErrorResponseBuilder`
  - `ApiPlatformNotNormalizableValueExceptionErrorResponseBuilder`
  - `ApiPlatformUnexpectedValueExceptionErrorResponseBuilder`
  - `ApiPlatformTypeErrorExceptionErrorResponseBuilder` (it was not deprecated in 6.x)

  `ValidationExceptionErrorResponseBuilder` now handles these errors through `collect_denormalization_errors`. Keep
  this option enabled (the bundle enables it).
- Error response builders are renamed. Update service ids, decorators and subclasses:

  | 6.x | 7.0 |
  |---|---|
  | `ApiPlatformValidationExceptionErrorResponseBuilder` | `ValidationExceptionErrorResponseBuilder` |
  | `ApiPlatformCustomSerializerExceptionErrorResponseBuilder` | `CustomSerializerExceptionErrorResponseBuilder` |
  | `ApiPlatformNotEncodableValueExceptionErrorResponseBuilder` | `NotEncodableValueExceptionErrorResponseBuilder` |
  | `AbstractApiPlatformExceptionErrorResponseBuilder` | `AbstractApiPlatformErrorResponseBuilder` |
  | `AbstractApiPlatformSerializerExceptionErrorResponseBuilder` | `AbstractDeserializationErrorResponseBuilder` |

- Builder priorities changed: Validation 99 → 90, CustomSerializer 94 → 80, NotEncodableValue 93 → 70. Check the
  priorities of your own builders.
- If you extend the abstract builders:
  - `$nameConverter` is now `?NameConverterInterface`.
  - `buildData()`, `normalizePropertyName()` and `setRequestStack()` are now in the base class. Remove your own copies.
  - `setIriConverter()` is only in `ValidationExceptionErrorResponseBuilder`.
  - `isThrowableFromApiPlatformSerializer()` is now private.
  - `buildViolationsForNotNormalizableValueException()` is removed.
- Violation keys use the `api_platform.name_converter` service (6.x: `serializer.name_converter.metadata_aware`).
  Keys can change if your API Platform and Symfony serializer name converters are different.
- Violation keys for operations with an input DTO use the DTO class, so `#[SerializedName]` on the DTO now applies.
- `violations` is always an object. NotEncodableValue errors and CustomSerializer errors without a property path
  change from `["message"]` to `{"": ["message"]}`. Update clients and tests.
- `AdvancedSearchFilter` does not support composite identifiers anymore.

### EasyApiToken

- `AwsCognitoJwtDriver` constructor arguments are renamed. Update named arguments and `->arg('$...')` in DI config:
  - `$jwkFetcher` → `$jwkProvider`
  - `$leeway` → `$jwtLeeway`
  - `$defaultJwkAlgo` → `$defaultJwtAlgorithm`

### EasyAsync

- `ReorderMessengerMiddlewareCompilerPass` priority changed from -11 to -30.

### EasyBatch

- `AbstractBatchObjectTransformer` does not convert old `EonX\EasyBatch\Objects\*` class names anymore. Update the
  stored data first, see [Prepare on 6.x](#easybatch).
- Compiler pass priorities changed: `AddMessengerMiddlewareToBusesCompilerPass` -10 → -20,
  `MakeMessengerReceiversPublicCompilerPass` -10 → 0.
- Batch item messages are signed with an HMAC of the `kernel.secret` parameter (or the new `signing_secret` option).
  No schema change: the signature is stored in the `message` column. Unsigned messages (stored before 7.0) are
  refused unless the new required `allow_unsigned_messages` option is `true`; the container does not build until you
  set it. If such batch items are left to process, set it to `true` before you deploy and to `false` once they are
  done (the roll-out in
  [Message signing](packages/EasyBatch/docs/symfony/install.md#message-signing) has a query to check it). If several
  applications create and process items of the same batch tables, set the same `signing_secret` in all of them
  before you deploy. 6.x cannot read signed messages: during a rolling deploy, 6.x workers fail on new items until a
  retry lands on an upgraded worker, and after a rollback to 6.x every signed item fails until 7.x is deployed again.
  An item that fails the check is not marked as failed: it keeps its status, its batch stays unfinished, and the
  message goes to the failure transport. Fix the config and run `messenger:failed:retry`.
- `MessageSerializer` requires a `MessageSignerInterface`. If you create it yourself, pass
  `new HmacMessageSigner(<secret>)`.
- `AbstractBatchObjectTransformer::instantiateForClass()` throws `BatchObjectInvalidException` when the stored
  `class` is not an `AbstractBatchObject`.

### EasyBugsnag

- New `DatabaseErrorScrubberClientConfigurator` (Symfony only, always on). In reports, the message of each Doctrine
  driver exception (and the exceptions below it) is replaced by `SQLSTATE[<state>] <class>`, also where other
  configurators copied it into the metadata (e.g. `error.message` from EasyErrorHandler). It runs after the other
  configurators (priority `PHP_INT_MAX - 1`), before the sensitive data sanitizer. To keep the full message, remove or
  decorate this service.
- `WorkerMessageReceivedListener` sanitizes the message and stamps dump with `SensitiveDataSanitizerInterface`.
- `doctrine_dbal` (SQL query breadcrumbs) is disabled by default. To keep the breadcrumbs, set
  `'doctrine_dbal' => true` in `easy_bugsnag`.
- SQL query breadcrumbs no longer contain the query parameter values. To add them, set
  `'doctrine_dbal' => ['log_query_values' => true]`. The values are sent as is: the sensitive data sanitizer cannot
  mask positional parameters.
- `QueryBreadcrumb::getQueryTypes()` (DBAL 4) returns the `ParameterType` case names (`string[]`) instead of
  `ParameterType` cases.

### EasyDoctrine

- `ObjectCopierFactory::create()` and the `ObjectCopierInterface` service are removed. Use
  `createForDeletedEntity()` and `easy_doctrine.deleted_entity_copier`.
- `DeferredEntityEventDispatcher::__construct()`: the `$objectCopier` argument is removed. `$deletedEntityCopier` is
  required and is now the second argument.
- `DeferredEntityEventDispatcherInterface::deferUpdate()`: argument `$object` is renamed to `$entity`.
- `EntityActionEventInterface` is removed. Use `AbstractEntityEvent` for type hints and `instanceof` checks.
- Entity events are dispatched twice: by class (as before) and by name `entity.<created|updated|deleted>.<FQCN>`.
  You can use the new attributes `#[AsEntityCreatedEventListener]`, `#[AsEntityUpdatedEventListener]` and
  `#[AsEntityDeletedEventListener]`.
- `FixPostgreSqlDefaultSchemaListener` is removed. If you registered it, remove the registration.
- The `easy_doctrine` Monolog channel is registered when `MonologBundle` is registered (6.x: only with
  `'use_symfony_monolog_bundle' => true`).

### EasyEncryption

- New option `default_hash_normalizations` (`lowercase`, `trim`), default `['lowercase']`. You can override it per
  field with `#[EncryptableField(hashNormalizations: [...])]`.
- `HmacSha512HashCalculator::calculate()` does not lowercase the value anymore. Lowercasing now comes from
  `default_hash_normalizations`. If you call `HashCalculatorInterface::calculate()` directly (for example to search
  by a value), the hash changes for mixed-case values. To keep 6.x hashes, call
  `EncryptableFieldHasherInterface::hashForField($entityClass, $property, $value)` (it applies the field
  normalizations), or call `mb_strtolower()` before `calculate()`.
- AWS CloudHSM: the default `lowercase` normalization now applies before hashing, so hashes of mixed-case values
  change. To keep 6.x hashes, set `'default_hash_normalizations' => []` in `easy_encryption`.
- HMAC secret:
  - Symfony: the secret is now the `easy_encryption.default_encryption_key` parameter (6.x: `APP_SECRET`). The
    default is still `%env(APP_SECRET)%`. If you changed `default_encryption_key`, all hashes change: set the
    `$secret` argument of `HashCalculatorInterface` to `%env(APP_SECRET)%`, or rehash your data.
  - Laravel: without AWS CloudHSM, the provider now registers `HmacSha512HashCalculator` with
    `easy-encryption.default_encryption_key` (default `env('APP_KEY')`). 6.x did not register it. If you bound your
    own `HashCalculatorInterface`, make sure your binding still wins, or rehash your data.
- If you create or implement these yourself:
  - `ObjectEncryptor` constructor: `$hashCalculator` argument → `$fieldHasher` (`EncryptableFieldHasherInterface`).
  - `EncryptableInterface::encrypt()` without `EncryptableTrait`: the hash callable is now
    `callable(string $entityClass, string $property, string $value)`.
  - `EncryptableMetadataInterface` has a new method `getHashNormalizationsForField()`.

### EasyErrorHandler

- Default texts now match the HTTP status phrases. Update tests and clients, or override the keys:

  | Key | 6.x | 7.0 |
  |---|---|---|
  | `exceptions.bad_request` | `Bad request.` | `Bad Request` |
  | `exceptions.conflict` | `Conflict.` | `Conflict` |
  | `exceptions.forbidden` | `Forbidden.` | `Forbidden` |
  | `exceptions.not_found` | `Not found.` | `Not Found` |
  | `exceptions.unauthorized` | `Unauthorized.` | `Unauthorized` |

- Translation keys are removed: `exceptions.entity_not_valid`, `violations.another_iri`,
  `violations.missing_constructor_argument`.
- New translation keys: `violations.item_not_found`, `violations.invalid_iri_value`. Translate them for other locales.
- `Translator::__construct()`: argument `$decorated` is renamed to `$translator`.

### EasyEventDispatcher

- `EventDispatcherInterface::dispatch()` has a new argument: `dispatch(object $event, ?string $eventName = null)`.
  Update your implementations and decorators.

### EasyHttpClient

- The `easy_http_client.logger` service is removed. The bundle uses `monolog.logger.easy_http_client`.

### EasyLock

- `Locker::__construct()` takes only `LockFactory $lockFactory`. The `$store` and `$logger` arguments are removed. If
  you create `Locker` yourself, create the `LockFactory` with the store and call `setLogger()` on it.
- `ReorderMessengerMiddlewareCompilerPass` priority changed from -9 to -10.

### EasyLogging

Symfony:

- `MonologBundle` is required. Without it, container compilation fails with a `LogicException`.
  `symfony/monolog-bundle` is not installed with EasyLogging (it is in `suggest`), so require it in your app.
- Options are removed: `use_symfony_monolog_bundle`, `default_channel`, `lazy_loggers`, `stream_handler`,
  `stream_handler_level`, `bugsnag_handler_level`, `bugsnag_handler_channels`. Remove them, or compilation fails with
  "Unrecognized option".
- The default stream handler is removed. **Without `monolog.handlers`, all logs are lost.** In 6.x it wrote all
  channels except `event` to `php://stdout` at `debug` level. Declare at least one handler:

  ```php
  // config/packages/monolog.php
  return App::config([
      'monolog' => [
          'handlers' => [
              'main' => [
                  'type' => 'stream',
                  'path' => 'php://stdout',
                  'level' => 'debug',
                  'channels' => ['!event'],
                  'formatter' => 'easy_logging.formatter.json', // optional
              ],
          ],
      ],
  ]);
  ```

- `LoggerFactoryInterface`, `easy_logging.logger` and the `logger` alias to it are removed. MonologBundle provides
  `logger` and `monolog.logger.<channel>`.
- Services that implement `HandlerConfigProviderInterface` or `LoggerConfiguratorInterface` now fail compilation.
  The same applies to `ProcessorConfigProviderInterface` services without the `monolog.processor` tag. Move handlers
  to `monolog.handlers`, and autoconfigure or tag processors.
- `DefaultStreamHandlerCompilerPass` and `ReplaceChannelsDefinitionCompilerPass` are removed.
- `bugsnag_handler` is now an object. `true` / `false` still work:

  ```php
  // config/packages/easy_logging.php
  return App::config([
      'easy_logging' => [
          'bugsnag_handler' => [
              'enabled' => true,
              'level' => 400, // was 'bugsnag_handler_level'
              'channels' => ['app'], // was 'bugsnag_handler_channels'
          ],
      ],
  ]);
  ```

  The handler is always added to Monolog for these channels. `enabled` and `channels` must be compile-time values:
  `%env()%` does not work for them (an env placeholder always counts as enabled).

Symfony and Laravel:

- The sensitive data sanitizer is enabled by default. It needs `EasyUtilsBundle` (Laravel:
  `EonX\EasyUtils\Laravel\EasyUtilsServiceProvider`). Without it you get a `LogicException` (Symfony: at compilation,
  Laravel: when the first logger is created). Register it, or disable the sanitizer:
  `'sensitive_data_sanitizer' => false` in `easy_logging` (Laravel: `'sensitive_data_sanitizer' => ['enabled' => false]`
  in `config/easy-logging.php`).
- `EonX\EasyLogging\Logger\LoggerAwareTrait` is removed.
- `BugsnagMonologHandler` has a new default formatter: `[%datetime%] %channel%.%level_name%: %message%`. Context and
  extra are only in the report metadata now. To get the old text, call `setFormatter()` on the handler. If you
  already set this format on 6.x, remove your handler service override.
- `BugsnagSeverityResolver` mapping changed. To keep the old mapping, implement `BugsnagSeverityResolverInterface`:

  | Monolog level | 6.x | 7.0 |
  |---|---|---|
  | Critical and higher | error | error |
  | Error | warning | error |
  | Warning | info | warning |
  | Notice and lower | info | info |

Laravel:

- `stream_handler` is now an array. `false` still works. **The old `stream_handler_level` key is ignored without an
  error**, so move it:

  ```php
  // config/easy-logging.php
  'stream_handler' => [
      'enabled' => true,
      'level' => 200, // was 'stream_handler_level'
  ],
  ```

### EasyPagination

- `PaginationFromRequestListener` priority changed from 10000 to 10010.

### EasySecurity

- Symfony: the `easy_security.logger` service is removed. The bundle uses `monolog.logger.security`.
- `FromRequestSecurityContextConfiguratorListener` priority changed from 9999 to 9990.
- Bugsnag metadata: the API token key `original` (raw token) is now `original_hash` (SHA-256 of the token). Update
  your Bugsnag filters.

### EasyServerless

- Symfony: `ReportBusDriver` and its alias `EonX\EasyServerless\EasyErrorHandler\BusDriver\ReportBusDriver` are
  removed. Use `EonX\EasyServerless\Bundle\SqsHandler\SqsHandler`. With the Bref SQS consumer, errors are not
  reported anymore. Laravel keeps `EonX\EasyServerless\Laravel\SqsHandlers\SqsHandler`.
- The `easy_serverless.easy_error_handler` config key is removed.
- `LumenHttpKernel` is removed.

### EasySwoole

- `SwooleWorkerIdProcessor` does not extend `AbstractSelfConfigProvidingProcessor` anymore. It is a Monolog processor
  (`#[AsMonologProcessor]`) and needs `MonologBundle`.
- `PdoClientPool` rolls back an open transaction before it returns a client to the pool, and logs a warning.
- Listener priorities changed: `ApplicationStateCheckListener` -10001 → -10020, `StaticPhpFileListener` 30000 → 30010.
- DBAL 4: `DbalConnection`, `DbalDriver`, `DbalStatement` and `CoroutineConnectionFactory` are the DBAL 4 classes. On
  DBAL 3 they are aliases of the `Dbal3*` classes. This only matters if you extend them.

### EasyTest

- The namespace `EonX\EasyTest\PHPUnit\` is renamed to `EonX\EasyTest\PhpUnit\`. Update `phpunit.xml`:

  ```xml
  <extensions>
      <bootstrap class="EonX\EasyTest\PhpUnit\Extension\EasyTestExtension"/>
  </extensions>
  ```

- `LoggerTrait` and `LoggerStub` are removed. Use `MonologTrait`.
- `EasyErrorHandlerAssertionTrait`: `assertEmptyReportedErrors()`, `assertErrorCodeExistsInReportedErrors()` and
  `getErrorHandlerReportedThrowables()` are removed. See [Prepare on 6.x](#easytest).
- `MessengerAssertionsTrait` is reworked. See [messenger_assertions.md](packages/EasyTest/docs/messenger_assertions.md).
  - New signature: `consumeAsyncMessages(array $expectedFailures = [], ?array $expectedDelays = null, array $transportNames = ['async'])`.
  - Removed: `consumeMessages()` (use `consumeAsyncMessages(transportNames: [...])`),
    `assertCountOfMessagesSentToTransport()`, `assertMessageSentToTransport()`,
    `assertMessageSentToFailedTransport()`, `getMessagesSentToAsyncTransport()`,
    `getMessagesSentToFailedTransport()`.
  - `$expectedFailures`: `[Exception::class => code]` means "fails at least once, nothing else fails".
    `[[Exception::class => code], ...]` means "exactly this list".
  - `$expectedDelays`: the exact clock moves between retries. `null` skips the check. For exact values set
    `'jitter' => 0` in the transport `retry_strategy`.
  - Test transports must use the `in-memory-persistent://` DSN. EasyTestBundle registers this transport factory:

    ```php
    // config/packages/test/messenger.php
    return App::config([
        'framework' => [
            'messenger' => [
                'transports' => [
                    'async' => [
                        'dsn' => 'in-memory-persistent://',
                        'retry_strategy' => ['jitter' => 0],
                    ],
                    'failed' => 'in-memory-persistent://',
                ],
            ],
        ],
    ]);
    ```


  ```php
  // 6.x
  self::consumeMessages([[ApiException::class => 42]], [1], ['async', 'email']);
  $failed = self::getMessagesSentToFailedTransport(Foo::class);

  // 7.0
  self::consumeAsyncMessages([[ApiException::class => 42]], [1], ['async', 'email']);
  $failed = self::getMessagesSentToTransport('failed', Foo::class);
  ```

- `ExceptionTrait`: `$isInsideSafeCall` is removed. `safeCall()` rethrows PHPUnit assertion failures.
  `assertThrownException()` has a new optional argument `$expectedMessage`.
- `EventDispatcherStub::getDispatchedEvents()` returns `array{event: object, eventName: ?string}[]`. Use
  `getDispatchedEvents()[$i]['event']`.
- `nesbot/carbon` below 3.12 is in `conflict`.
- New: `CarbonClock`, `QueuedEmailAssertionTrait`.

### EasyUtils

- `JsonStringSanitizer` is removed.
- `AbstractNumberComparison` (all `Number*Comparison` constraints): the `$options` argument is removed and the
  argument order changed. Use named arguments.
- Laravel: `easy-utils.string_trimmer.enabled` now adds `TrimStringsMiddleware` to the global HTTP middleware (6.x
  used the Lumen API).

### EasyWebhook

- `request_limits` is enabled by default (Symfony and Laravel). Defaults: 10 s idle timeout, 30 s max duration, 1 MiB
  max response. Change the `request_limits` options, or disable it:

  ```php
  // config/packages/easy_webhook.php
  return App::config([
      'easy_webhook' => [
          'request_limits' => [
              'enabled' => false,
          ],
      ],
  ]);
  ```
