# Upgrade from 6.x to 7.0

This guide has two parts:

1. [Prepare on 6.x](#1-prepare-on-6x): changes you can make now, on the latest 6.x release. Test them before you switch.
2. [Upgrade to 7.0](#2-upgrade-to-70): changes that apply when you switch to 7.0.

Packages that are not listed have no changes other than the [requirements](#requirements).

## Install a pre-release

7.0 pre-releases (`7.0.0-alpha.*`) are tagged from the `7.x` branch. To install one, allow unstable versions in your
`composer.json`:

```json
{
    "minimum-stability": "alpha",
    "prefer-stable": true
}
```

Then require the packages with `^7.0`. All `eonx-com/*` packages in one app must use the same major version.

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
- [ ] Set `easy_logging.use_symfony_monolog_bundle: true`. In 7.0 this is the only mode.
- [ ] Declare your Monolog handlers in `monolog.handlers`. See [EasyLogging](#easylogging) below.
- [ ] Replace services that implement `HandlerConfigProviderInterface` or `LoggerConfiguratorInterface` with Monolog
  config. In this mode 6.x already ignores them.
- [ ] Inject `LoggerInterface` or `monolog.logger.<channel>`. Do not inject `LoggerFactoryInterface` or
  `easy_logging.logger`.
- [ ] Inject `monolog.logger.easy_http_client` instead of `easy_http_client.logger`.
- [ ] Inject `monolog.logger.security` instead of `easy_security.logger`.
- [ ] Stop using `EonX\EasyLogging\Logger\LoggerAwareTrait`. Use constructor injection.

### EasyLogging (Symfony and Laravel)

- [ ] Enable the sensitive data sanitizer: `easy_logging.sensitive_data_sanitizer.enabled: true` (Laravel:
  `easy-logging.sensitive_data_sanitizer.enabled => true`). It needs `EasyUtilsBundle` (Laravel:
  `EonX\EasyUtils\Laravel\EasyUtilsServiceProvider`). In 7.0 it is enabled by default.

### EasyDoctrine

- [ ] Replace `ObjectCopierFactory::create()` with `ObjectCopierFactory::createForDeletedEntity()`.
- [ ] Replace the `ObjectCopierInterface` service with `easy_doctrine.deleted_entity_copier`.
- [ ] If you create `DeferredEntityEventDispatcher` yourself, pass `deletedEntityCopier:` and remove `objectCopier:`.

### EasyBatch

- [ ] Update stored class names in the `class` column of `easy_batches` and `easy_batch_items` (and in your
  `batch_class` / `batch_item_class` config). 7.0 does not convert old names on read anymore:

  ```sql
  UPDATE easy_batches SET class = REPLACE(class, 'EonX\\EasyBatch\\Objects\\', 'EonX\\EasyBatch\\Common\\ValueObject\\');
  UPDATE easy_batch_items SET class = REPLACE(class, 'EonX\\EasyBatch\\Objects\\', 'EonX\\EasyBatch\\Common\\ValueObject\\');
  ```

### EasyServerless

- [ ] Use `EonX\EasyServerless\Bundle\SqsHandler\SqsHandler` as the Lambda SQS handler. Stop using `ReportBusDriver`.
- [ ] Remove the `easy_serverless.easy_error_handler` config key. It does nothing on 6.x.

### EasyUtils

- [ ] Stop using `JsonStringSanitizer`.
- [ ] Create `Number*Comparison` constraints with named arguments. Do not pass `options`.

### EasyWebhook

- [ ] Enable `easy_webhook.request_limits.enabled: true` (Laravel: `easy-webhook.request_limits.enabled => true`)
  and test it. In 7.0 it is enabled by default.

### EasyTest

- [ ] Replace `LoggerTrait` and `LoggerStub` with `EonX\EasyTest\Monolog\Trait\MonologTrait`.
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
| API Platform | `^3.4.17 \|\| ^4.3.17` | `^4.4 \|\| ^5.0` |
| Doctrine DBAL | `^3.8` | `^3.8 \|\| ^4.4` |
| Doctrine ORM | `^2.20` | `^2.20 \|\| ^3.6` |
| DoctrineBundle | `^2.12` | `^2.12 \|\| ^3.2` |
| `nesbot/carbon` | `^3.8.4` | `^3.12` |
| `symfony/monolog-bundle` | `^3.11.1` | `^3.11.1 \|\| ^4.0` |
| `eonx-com/*` | `^6.x` | `^7.0` |

- Lumen: all Lumen code is removed (for example `LumenHttpKernel` in EasyServerless, the Lumen branches in
  EasyPagination and EasyUtils). Laravel service providers now use `Illuminate\Foundation\Application`.
- EasyServerless: `bref/laravel-bridge` must be `^3.0`.

### EasyApiPlatform

- `api-platform/core` is not a dependency anymore. Require API Platform yourself: `api-platform/core` `^4.4`, or the
  split packages (`api-platform/symfony`, `api-platform/doctrine-orm`, ...) `^4.4 || ^5.0`.
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

### EasyBugsnag

- New `DatabaseErrorScrubberClientConfigurator` (Symfony only, always on). In reports, the message of each Doctrine
  driver exception (and the exceptions below it) is replaced by `SQLSTATE[<state>] <class>`. To keep the full message,
  remove or decorate this service.
- `WorkerMessageReceivedListener` sanitizes the message and stamps dump with `SensitiveDataSanitizerInterface`.

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
  `use_symfony_monolog_bundle: true`).

### EasyEncryption

- New option `default_hash_normalizations` (`lowercase`, `trim`), default `['lowercase']`. You can override it per
  field with `#[EncryptableField(hashNormalizations: [...])]`.
- `HmacSha512HashCalculator::calculate()` does not lowercase the value anymore. Lowercasing now comes from
  `default_hash_normalizations`. If you call `HashCalculatorInterface` directly, use
  `EncryptableFieldHasherInterface::hashForField()` instead.
- AWS CloudHSM: the default `lowercase` normalization now applies before hashing, so hashes of mixed-case values
  change. To keep 6.x hashes, set `default_hash_normalizations: []`.
- The HMAC secret is now `easy_encryption.default_encryption_key` (6.x: `APP_SECRET`). The default is still
  `%env(APP_SECRET)%`. If you changed `default_encryption_key`, all hashes change: set the `$secret` argument of
  `HashCalculatorInterface` to `APP_SECRET`, or rehash your data.
- If you implement these interfaces yourself:
  - `ObjectEncryptor`: `$hashCalculator` argument → `$fieldHasher` (`EncryptableFieldHasherInterface`).
  - The hash callable passed to `EncryptableInterface::encrypt()` is now
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
- The default stream handler is removed. **Without `monolog.handlers`, all logs are lost.** Declare at least one
  handler:

  ```yaml
  # config/packages/monolog.yaml
  monolog:
      handlers:
          main:
              type: stream
              path: 'php://stderr'
              formatter: easy_logging.formatter.json
  ```

- `LoggerFactoryInterface`, `easy_logging.logger` and the `logger` alias to it are removed. MonologBundle provides
  `logger` and `monolog.logger.<channel>`.
- Services that implement `HandlerConfigProviderInterface` or `LoggerConfiguratorInterface` now fail compilation.
  The same applies to `ProcessorConfigProviderInterface` services without the `monolog.processor` tag. Move handlers
  to `monolog.handlers`, and autoconfigure or tag processors.
- `DefaultStreamHandlerCompilerPass` and `ReplaceChannelsDefinitionCompilerPass` are removed.
- `bugsnag_handler` is now an object. `true` / `false` still work:

  ```yaml
  easy_logging:
      bugsnag_handler:
          enabled: true
          level: 400        # was bugsnag_handler_level
          channels: [app]   # was bugsnag_handler_channels
  ```

  The handler is always added to Monolog for these channels. `enabled` and `channels` must be compile-time values:
  `%env()%` does not work for them (an env placeholder always counts as enabled).

Symfony and Laravel:

- The sensitive data sanitizer is enabled by default. It needs `EasyUtilsBundle` (Laravel:
  `EonX\EasyUtils\Laravel\EasyUtilsServiceProvider`). Without it you get a `LogicException` (Symfony: at compilation,
  Laravel: when the first logger is created). Register it, or disable the sanitizer:
  `easy_logging.sensitive_data_sanitizer: false` (Laravel: `easy-logging.sensitive_data_sanitizer.enabled => false`).
- `EonX\EasyLogging\Logger\LoggerAwareTrait` is removed.
- `BugsnagMonologHandler` has a new default formatter: `[%datetime%] %channel%.%level_name%: %message%`. Context and
  extra are only in the report metadata now. To get the old text, call `setFormatter()` on the handler.
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

- `ReportBusDriver` and its alias `EonX\EasyServerless\EasyErrorHandler\BusDriver\ReportBusDriver` are removed. Use
  `EonX\EasyServerless\Bundle\SqsHandler\SqsHandler`. With the Bref SQS consumer, errors are not reported anymore.
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
    `retry_strategy.jitter: 0`.
  - Test transports must use `dsn: 'in-memory-persistent://'`. EasyTestBundle registers this transport factory.

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
  max response. Change `easy_webhook.request_limits.*`, or disable it:

  ```yaml
  easy_webhook:
      request_limits:
          enabled: false
  ```
