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

The package registers `Symfony\Component\Lock\LockFactory` as a service, configured with the package's store and
logger, so there is no need to create your own instance of the lock factory. You can simply inject it into your
services:

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

The same lock factory instance is used by `EonX\EasyLock\Common\Locker\LockerInterface` internally.

[1]: https://getcomposer.org/
[2]: https://symfony.com/doc/current/components/lock.html
[3]: https://github.com/symfony/symfony/blob/master/src/Symfony/Component/Lock/Store/StoreFactory.php
