---eonx_docs---
title: Introduction
weight: 0
---eonx_docs---

The purpose of this package isn't to be used within a project by the application as there is no point in creating
another level of abstraction in that case BUT only to allow eonx-com packages to dispatch events without
having to think about the event dispatcher used by each of our projects.

### Require package (Composer)

The recommended way to install this package is to use [Composer][1]:

```bash
$ composer require eonx-com/easy-lock
```

<br>

### Usage

The Symfony Lock component has an excellent [documentation][2] and we recommend referring to it.

###### Connection

To work with this package you simply have to register the connection to use for the locks store as a service under
the `easy_lock.connection` id. This connection will be given to the [StoreFactory][3], so its value can be anything
supported by the Lock component.

###### Store

If defining the connection doesn't work for you, you can override the store instance within the service container under
the `easy_lock.store` id.

###### Lock factory

The package registers a `Symfony\Component\Lock\LockFactory` built on its own store and logger under the
`easy_lock.lock_factory` id, so there is no need to create your own instance of the lock factory. Wire it explicitly
where you need it:

```php
use Symfony\Component\Lock\LockFactory;

final readonly class MyService
{
    public function __construct(
        private LockFactory $lockFactory,
    ) {
    }

    public function doSomething(): void
    {
        $lock = $this->lockFactory->createLock('my-resource');

        // ...
    }
}
```

```php
// config/services.php
$services->set(MyService::class)
    ->arg('$lockFactory', service('easy_lock.lock_factory'));
```

The same lock factory instance is used by `EonX\EasyLock\Common\Locker\LockerInterface` internally.

Nothing is registered under the `Symfony\Component\Lock\LockFactory` class name on purpose. FrameworkBundle claims
that name as soon as `framework.lock` is enabled, which is the default once `symfony/lock` is installed, and its
default factory uses a **flock** store that does not lock across application instances. A plain `LockFactory`
type-hint therefore resolves to whatever the application configured, which is why the wiring above is explicit.

### Messenger middleware and busy locks

`EonX\EasyLock\Messenger\Middleware\ProcessWithLockMiddleware` handles messages consumed by a worker when the
message implements `EonX\EasyLock\Common\ValueObject\WithLockDataInterface` or the envelope has
`EonX\EasyLock\Messenger\Stamp\WithLockDataStamp`. The middleware acquires the lock before it calls the handler and
releases the lock after the handler returns.

When another process holds the lock, the result depends on the `retry` value of the lock data:

- `retry` is `false` (default): the handler is not called and the message is **removed from the transport**. The
  worker acknowledges the message as if it was handled. The middleware:
  - writes a log record to the `lock` channel with the message class, the lock resource, the lock TTL
    (`300` seconds when the lock data does not set it), the transport message ID and the transport name
  - adds `EonX\EasyLock\Messenger\Stamp\LockNotAcquiredStamp` to the envelope that the bus returns to the worker.
    Only worker-side code can see this stamp: the code that receives the result of the bus dispatch in the worker
    (for example a custom SQS handler) and listeners of `WorkerMessageHandledEvent`. The code that sent the message
    to the transport does not get this envelope
- `retry` is `true`: the middleware throws `EonX\EasyLock\Common\Exception\ShouldRetryException`. The retry
  strategy of the transport decides if the message is retried. No log record is written by the middleware.

Removing the message is correct only when the process that holds the lock does the same work, for example when the
transport delivers the same message two times. If different messages use the same lock resource, the work of the
removed message is lost.

The log level is `warning` by default because many applications ignore records below `warning` for channels other than
their own. You can change it:

```php
// config/packages/easy_lock.php
namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Psr\Log\LogLevel;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension('easy_lock', [
        'messenger' => [
            'middleware' => [
                'lock_not_acquired_log_level' => LogLevel::INFO,
            ],
        ],
    ]);
};
```

###### Lock TTL

The lock TTL is `300` seconds when the lock data does not set it. If a worker stops before it releases the lock (the
process is killed, the Lambda function times out), the lock stays until the TTL expires. Choose the TTL as follows:

- Make it longer than the longest time the handler can run. If the lock expires while the handler runs, another
  worker can handle the same message at the same time.
- Make it shorter than the time after which the transport delivers an unacknowledged message again: the visibility
  timeout for SQS, `redeliver_timeout` for the Doctrine transport. If the TTL is longer, the next delivery after a
  worker failure finds the old lock and the message is removed without being handled.

[1]: https://getcomposer.org/
[2]: https://symfony.com/doc/current/components/lock.html
[3]: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Lock/Store/StoreFactory.php
