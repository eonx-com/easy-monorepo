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

[1]: https://getcomposer.org/
[2]: https://symfony.com/doc/current/components/lock.html
[3]: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Lock/Store/StoreFactory.php
